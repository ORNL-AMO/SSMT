<?php
/**
 * Steam Calculators
 * 
 * @package    Steam
 * @subpackage Steam_Model
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Generates Sankey Energy Flow Chart for a given model
 * 
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Sankey{
    
    /**
     * Gap of Text
     * @var int 
     */
    var $textGap = 30;
    /**
     * Extra Arc Length
     * @var int 
     */
    var $extraArc = 20;
    /**
     * Extension Length
     * @var int
     */
    var $extensionLength = 500;
    /**
     * Heat In Width
     * @var int
     */
    const HEAT_IN_WIDTH = 150;
    /**
     * Useful Energy Width
     * @var int
     */
    var $usefulWidth = 50;
    /**
     * Clearance between waste and right side
     * $var int
     */
    var $wasteClearLength = 20;
    /**
     * Waste Turn Lenth
     * @var int
     */
    var $wasteTurnLength = 10;
    /**
     * Image Width
     * @var int
     */
    var $imageWidth = 700;
    /**
     * Image Height
     * @var int
     */
    var $imageHeight = 450;
    
    public function __construct($steamModel) {
        $totalFlows = count(self::displayedStreams($steamModel->headerCount));
        //Adjust Image Height to match flows
        $this->imageHeight -= (9-$totalFlows)*30;
        
        //Set working version 2 times large then final
        $originalZoom = 2;
        
        $steamtoUseful = imagecreatefromgif(APPLICATION_PATH.'/resources/images/steamtoUseful.gif');
        $this->textGap *= $originalZoom;
        $this->extraArc *= $originalZoom;
        $this->extensionLength *= $originalZoom;
        $this->heatInLength = $originalZoom * self::HEAT_IN_WIDTH;
        $this->usefulWidth *= $originalZoom;
        $this->wasteClearLength *= $originalZoom;
        $this->wasteTurnLength *= $originalZoom;
        $this->resultHeight *= $originalZoom;
        $canvasWidth = $this->imageWidth * $originalZoom;
        $canvasHeight = $this->imageHeight * $originalZoom;
        $this->resultEndLength = $this->heatInLength+$this->extraArc+$this->extensionLength;        
        
        $this->img = imagecreatetruecolor($canvasWidth, $canvasHeight);
        $this->finalImg = imagecreatetruecolor($this->imageWidth, $this->imageHeight);

        // allocate colors
        $this->white = imagecolorallocate($this->img, 255, 255, 255);
        $this->red   = imagecolorallocate($this->img, 255,   0,   0);
        $this->green = imagecolorallocate($this->img,   0, 255,   0);
        $this->blue  = imagecolorallocate($this->img,   0,   0, 255);
        $this->black = imagecolorallocate($this->img,   0,   0,   0);       
        
        $energyInArray = array(
            $steamModel->deaerator->daVentSteam->energyFlow,        
            $steamModel->lpHeader->heatLoss->energyFlowLoss+$steamModel->lpProcessSteam->energyFlow,
            $steamModel->turbineMpLpModel->energyOut,       
            $steamModel->mpHeader->heatLoss->energyFlowLoss+$steamModel->mpProcessSteam->energyFlow,
            $steamModel->turbineHpMpModel->energyOut,
            $steamModel->turbineHpLpModel->energyOut,
            $steamModel->turbineCondModel->energyOut,
            $steamModel->hpHeader->heatLoss->energyFlowLoss+$steamModel->hpProcessSteam->energyFlow,
            $steamModel->boiler->fuelEnergy*(1-$steamModel->boiler->boilerEff),
        );  
        
        $usefulArray = array(
            0,
            $steamModel->lpProcessSteam->energyFlow,
            $steamModel->turbineMpLpModel->powerOut,
            $steamModel->mpProcessSteam->energyFlow,
            $steamModel->turbineHpMpModel->powerOut,
            $steamModel->turbineHpLpModel->powerOut,
            $steamModel->turbineCondModel->powerOut,
            $steamModel->hpProcessSteam->energyFlow,
            0);
        
        $energyInArrayTotal = array_sum($energyInArray);
        $usefulTotal = array_sum($usefulArray);
        $this->flowPercent = array();
        foreach($energyInArray as $key => $value){
            $this->flowPercent[] = ceil($value/$energyInArrayTotal*$this->heatInLength);
            $this->usefulPercent[] = ceil($usefulArray[$key]/$energyInArrayTotal*$this->heatInLength);
        }

        imagefill($this->img, 0, 0, $this->white);
        
        $heatWidth = 0;
        foreach(array_reverse(self::displayedStreams($steamModel->headerCount),true) as $key => $title){
            $value = $this->flowPercent[$key];
            $heatWidth += $value;
            $extension = $totalFlows*$this->textGap+($this->heatInLength-$heatWidth)+$this->extraArc;
            $extensionTrim = $this->wasteClearLength+($value-$this->usefulPercent[$key]+$this->wasteTurnLength);

            imagefilledrectangle($this->img, 
                    0, ($totalFlows-1)*$this->textGap, //destination lefttop
                    $heatWidth, $totalFlows*$this->textGap, //destination rightbottom
                    $this->red);
            imagefilledrectangle($this->img, 
                    $this->heatInLength+$this->extraArc, $extension, 
                    $this->resultEndLength-$extensionTrim, $extension+$value, 
                    $this->red);
            imagecopyresampled($this->img, $steamtoUseful, 
                    $this->heatInLength+$this->extraArc, $extension, //destination topleft
                    0, 0, //source topleft
                    $this->extensionLength, $this->usefulPercent[$key], //destination widthheight
                    100, 10 ); //source widthheight
            imageline($this->img, 
                    $this->heatInLength+$this->extraArc, $extension+$value, 
                    $this->resultEndLength-$extensionTrim, $extension+$value , 
                    $this->black);
            $this->addArc( $totalFlows*$this->textGap, $value, $this->heatInLength-$heatWidth);
            $this->addArcOut($value, $this->usefulPercent[$key], $totalFlows, $heatWidth);
            $totalFlows--;
        }                
        
        imagefilledrectangle($this->img, 
                $this->resultEndLength,0,
                $this->resultEndLength+$this->heatInLength/10, 
                        (count($this->flowPercent)+1)*$this->textGap+$this->heatInLength, $this->red );
        imagefilledrectangle($this->img, 
                $this->resultEndLength,0,
                $this->resultEndLength+round($usefulTotal/$energyInArrayTotal*$this->heatInLength)/10, 
                        (count($this->flowPercent)+1)*$this->textGap+$this->heatInLength, $this->blue );
                        
        imagefilledrectangle($this->img, 
                $this->resultEndLength-$this->heatInLength+$this->heatInLength/10,0,
                $this->resultEndLength+$this->heatInLength/10, 
                        $this->resultHeight, $this->red );
        imagefilledrectangle($this->img, 
                $this->resultEndLength-$this->heatInLength+$this->heatInLength/10,0,
                $this->resultEndLength-$this->heatInLength+$this->heatInLength/10+round($usefulTotal/$energyInArrayTotal*$this->heatInLength), 
                        $this->resultHeight, $this->blue );
        
        imagefilledpolygon($this->img, array(
            $this->resultEndLength-$this->heatInLength+$this->heatInLength/10+round($usefulTotal/$energyInArrayTotal*$this->heatInLength),$this->resultHeight,
            $this->resultEndLength+$this->heatInLength/10,0,
            $this->resultEndLength+round($usefulTotal/$energyInArrayTotal*$this->heatInLength)/10,$this->textGap*.9,
            $this->resultEndLength+round($usefulTotal/$energyInArrayTotal*$this->heatInLength)/10,$this->textGap*.9,
        ), 4, $this->red);
        
        imagefilledpolygon($this->img, array(
            $this->resultEndLength-$this->heatInLength+$this->heatInLength/10,$this->resultHeight,
            $this->resultEndLength-$this->heatInLength+$this->heatInLength/10+round($usefulTotal/$energyInArrayTotal*$this->heatInLength),$this->resultHeight,
            $this->resultEndLength+round($usefulTotal/$energyInArrayTotal*$this->heatInLength)/10,$this->textGap*.9,
            $this->resultEndLength,$this->textGap,
        ), 4, $this->blue);
        
        imagecopyresampled($this->finalImg, $this->img, 0, 0, 0, 0, $this->imageWidth, $this->imageHeight, $canvasWidth, $canvasHeight);
        header("Content-type: image/png");
        imagepng($this->finalImg);

        // free memory
        imagedestroy($this->img);
        imagedestroy($this->finalImg);
    }
    
    /**
     * Draws Arc off of heat in
     * @param int $y position
     * @param int $width
     * @param int $remaingWidth
     */
    function addArc($y, $width, $remaingWidth){
        $diameter = ($width+$remaingWidth+$this->extraArc)*2;
        imagefilledarc($this->img, $this->heatInLength+$this->extraArc , $y, $diameter, $diameter, 90, 180, $this->red, IMG_ARC_PIE);
        imagefilledarc($this->img, $this->heatInLength+$this->extraArc , $y, $diameter-($width+1)*2, $diameter-($width+1)*2, 90, 180, $this->white, IMG_ARC_PIE);
        imagearc($this->img, $this->heatInLength+$this->extraArc , $y, $diameter, $diameter, 90, 180, $this->black);
    }
    
    /**
     * Draws Waste Arc
     * @param int $width
     * @param int $usefulPercent
     * @param int $count
     * @param int $heatwidth
     */
    function addArcOut($width, $usefulPercent, $count, $heatwidth){
        $diameter = (($width-$usefulPercent)+$this->wasteTurnLength)*2;
        $top = ($count)*$this->textGap+($this->heatInLength-$heatwidth)+$width+$this->wasteTurnLength+$this->extraArc;
        $left = $this->heatInLength+$this->extraArc+$this->extensionLength-$this->wasteClearLength-$diameter/2;

        imagefilledarc($this->img, 
                $left, $top, 
                $diameter, $diameter, 270, 360, $this->red, IMG_ARC_PIE);        
        imagefilledarc($this->img, 
                $left, $top, 
                $this->wasteTurnLength*2,$this->wasteTurnLength*2, 270, 360, $this->white, IMG_ARC_PIE);
        imagearc($this->img, $left, $top, 
                $this->wasteTurnLength*2,$this->wasteTurnLength*2, 270, 360, $this->black);      

    }
    
    /**
     * Adds Text for a Sankey Image
     * @param Steam_Model_Constructor $steamModel
     */
    public static function addSankeyText($steamModel){
        $mS = Steam_MeasurementSystem::getInstance();
        $translator = Zend_Registry::get('Zend_Translate');
       
        $energyInArray = array(
            $steamModel->deaerator->daVentSteam->energyFlow,        
            $steamModel->lpHeader->heatLoss->energyFlowLoss+$steamModel->lpProcessSteam->energyFlow,
            $steamModel->turbineMpLpModel->energyOut,       
            $steamModel->mpHeader->heatLoss->energyFlowLoss+$steamModel->mpProcessSteam->energyFlow,
            $steamModel->turbineHpMpModel->energyOut,
            $steamModel->turbineHpLpModel->energyOut,
            $steamModel->turbineCondModel->energyOut,
            $steamModel->hpHeader->heatLoss->energyFlowLoss+$steamModel->hpProcessSteam->energyFlow,
            $steamModel->boiler->fuelEnergy*(1-$steamModel->boiler->boilerEff),
        );  
        
        $usefulArray = array(
            0,
            $steamModel->lpProcessSteam->energyFlow,
            $steamModel->turbineMpLpModel->powerOut,
            $steamModel->mpProcessSteam->energyFlow,
            $steamModel->turbineHpMpModel->powerOut,
            $steamModel->turbineHpLpModel->powerOut,
            $steamModel->turbineCondModel->powerOut,
            $steamModel->hpProcessSteam->energyFlow,
            0);
        $heatInLength = self::HEAT_IN_WIDTH;
        $energyInArrayTotal = array_sum($energyInArray);
        $usefulTotal = array_sum($usefulArray);
        $flowPercent = array();
        foreach($energyInArray as $key => $value){
            $flowPercent[] = ceil($value/$energyInArrayTotal*$heatInLength);
            $usefulPercent[] = ceil($usefulArray[$key]/$energyInArrayTotal*$heatInLength);
        }
        
       
        $position = 30;
        $flowPercent = array_reverse($flowPercent, true);
        $energyInArray = array_reverse($energyInArray, true);
        $usefulArray = array_reverse($usefulArray, true);
        foreach(self::displayedStreams($steamModel->headerCount) as $key => $label){
            echo "<div style=\"position: absolute; top: {$position}px; left: 175px;\"><span style='font-weight: bold;'>".$translator->_($label)." ".$translator->_('Flow').":</span> 
                ".number_format($mS->localize($energyInArray[$key],'energyflow'),2)." ".$mS->selected['energyflow']."
            <span style='font-weight: bold;'>".$translator->_('Loss').":</span> ".number_format($mS->localize($energyInArray[$key]-$usefulArray[$key],'energyflow'),2)." ".$mS->selected['energyflow']."
            </div>";
            $position += (30+$flowPercent[$key]);
        }
    }
    
    /**
     * Returns a list of the display energy streams by header count
     * @param int $headerCount
     * @return array
     */
    public static function displayedStreams($headerCount){       
        $streams = array(
                3 => array(
                    8 => 'Boiler Losses',
                    7 => 'HP Header',
                    6 => 'Condensing Turbine',
                    5 => 'HP to LP Turbine',
                    4 => 'HP to MP Turbine',
                    3 => 'MP Header',
                    2 => 'MP to LP Turbine',
                    1 => 'LP Header',
                    0 => 'Deareator',
                ),
                2 => array(
                    8 => 'Boiler Losses',
                    7 => 'HP Header',
                    6 => 'Condensing Turbine',
                    5 => 'HP to LP Turbine',
                    1 => 'LP Header',
                    0 => 'Deareator',
                ),
                1 => array(
                    8 => 'Boiler Losses',
                    7 => 'HP Header',
                    6 => 'Condensing Turbine',
                    0 => 'Deareator',
                ),
            );
        return $streams[$headerCount];
    }
}