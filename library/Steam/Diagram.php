<?php
/**
 * Steam Calculators
 *
 * @package    Steam
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Generates Temperature vs Specific Entropy 
 * @package    Steam
 */
class Steam_Diagram{
    
    /**
     * Steam MeasurementSystem Object
     * @var Steam_MeasurementSystem
     */
    var $mS;
    
    /**
     * Steam IAPWS Object
     * $var Steam_IAPWS
     */
    var $sP;
    
    /**
     * Image Resouce Identifier
     * @var ImageResouceIdentifier
     */
    var $im;
    
    /**
     * Image Color Resouce Identifier
     * @var int
     */
    var $fullBlue;
    
    /**
     * Initlal Image Height
     * @var int
     */
    var $imageHeight = 500;
    /**
     * Initlal Image Width
     * @var int
     */
    var $imageWidth = 500;
    
    /**
     * Final Image Height
     * @var int
     */
    var $finalImageHeight = 500;
    /**
     * Final Image Width
     * @var int
     */
    var $finalImageWidth = 500;
    
    /**
     * Minimum Specific Entropy on Chart
     * @var float
     */
    var $minEntropy = 0;
    /**
     * Maximum Specific Entropy on Chart
     * @var float
     */
    var $maxEntropy = 10;
    
    /**
     * Minimum Temperature on Chart
     * @var float
     */
    var $minTemp = 273.15;
    /**
     * Maximum Specific Entropy on Chart
     * @var float
     */
    var $maxTemp = 1023.15;
       
    /**
     * Initialize Steam Diagram
     */
    public function __construct() {        
        $this->mS = new Steam_MeasurementSystem();
        $this->sP = new Steam_IAPWS();
    }
    
    /**
     * Initialize Image Canvas
     */
    function initImg(){        
        $this->im    = imagecreatetruecolor($this->imageWidth, $this->imageHeight);
        $this->fullBlue = imagecolorallocate($this->im, 0, 0, 255);
        $initialImage = imagecreatefromgif(APPLICATION_PATH.'/resources/images/diagrams/TSdiagram.gif');        
        imagecopyresampled($this->im, $initialImage, 0,0,0,0,500,500,500,500);        
    }
    
    /**
     * Render Image as a Gif
     */
    function drawImg(){
        $this->finalImageHeight = 200;
        $this->finalImageWidth  = 200;
        $imFinal    = imagecreatetruecolor($this->finalImageWidth, $this->finalImageHeight);
        imagecopyresampled($imFinal, $this->im, 0, 0, 0,0, $this->finalImageWidth, $this->finalImageHeight, $this->imageWidth, $this->imageHeight);       
        header("Content-type: image/gif");
        imagegif($imFinal);
        imagedestroy($imFinal);
        imagedestroy($this->im);          
    }
    
    /**
     * Draw an Temperature Enthalpy Image highlighting a steam point 
     * @param Steam_Object $steamObject
     */
    function highlightSteamTS($steamObject){        
        $this->initImg();
        
        $this->pressureLineTS($steamObject->pressure, $this->fullBlue);
        imagefilledarc($this->im, 
                $this->normalizeEntropy($steamObject->specificEntropy),
                $this->normalizeTemp($steamObject->temperature), 
                16, 16, 0, 360, $this->fullBlue, IMG_ARC_PIE);
                
        $this->drawImg();      
    }
    
    /**
     * Draw an Temperature Enthalpy Image highlighting saturated points
     * @param Steam_Object $steamObject
     * @param float $entropyLiquid kJ/kg/K
     * @param float $entropyGas kJ/kg/K
     */
    function highlightSaturatedLine($temperature, $entropyLiquid, $entropyGas){
        $this->initImg();
        
        imagefilledarc($this->im, 
                $this->normalizeEntropy($entropyLiquid),
                $this->normalizeTemp($temperature), 
                16, 16, 0, 360, $this->fullBlue, IMG_ARC_PIE);
        imagefilledarc($this->im, 
                $this->normalizeEntropy($entropyGas),
                $this->normalizeTemp($temperature), 
                16, 16, 0, 360, $this->fullBlue, IMG_ARC_PIE);
        imageline($this->im, 
                $this->normalizeEntropy($entropyLiquid),
                $this->normalizeTemp($temperature), 
                
                $this->normalizeEntropy($entropyGas),
                $this->normalizeTemp($temperature),
                $this->fullBlue);

        $this->drawImg();              
    }
    
    /**
     * Convert Temperature to Coordinate
     * @param float $temp K
     * @return float
     */
    function normalizeTemp($temp){
        return ($this->maxTemp-$temp)/($this->maxTemp-$this->minTemp)*$this->imageHeight;
    }
    
    /**
     * Convert Entropy to Coordinate
     * @param float $pressure kJ/kj/K
     * @return float
     */
    function normalizeEntropy($entropy){
        return $entropy /($this->maxEntropy-$this->minEntropy)*$this->imageHeight;
    }   
    
    /**
     * Draw a Constant Pressure line on image
     * @param float $pressure MPa
     * @param int $color
     */
    function pressureLineTS($pressure, $color){        
        if ($pressure<=PRESSURE_CRITICAL_POINT){
            $satProperties = $this->sP->saturatedPropertiesByPressure($pressure);        
            $satTemperature = $satProperties['liquid']['temperature'];
            $satLiq = $satProperties['liquid'];
            $satGas = $satProperties['gas'];
        }else{
            $satTemperature = $this->sP->boundaryByPressureRegion3to2($pressure);
            $satLiq = $this->sP->region3($pressure, $satTemperature);
            $satGas = $this->sP->region2($pressure, $satTemperature);
            
        }
        
        $lastPoint=false;
        //Liquid Section
        for( $temperature=$this->minTemp; $temperature<=$satTemperature; $temperature+=5){
            $propsPT = $this->sP->waterPropertiesPT( $pressure, $temperature  );            
            $newPoint = array(
                $this->normalizeEntropy($propsPT['specificEntropy']), 
                $this->normalizeTemp($temperature),
               );
            if ($lastPoint) imageline($this->im, $lastPoint[0], $lastPoint[1], $newPoint[0], $newPoint[1], $color);                
            $lastPoint = $newPoint;
        }
        
        //Saturated Section
        $newPoint = array(
                $this->normalizeEntropy($satLiq['specificEntropy']), 
                $this->normalizeTemp($satTemperature),
               );
        if ($lastPoint) imageline($this->im, $lastPoint[0], $lastPoint[1], $newPoint[0], $newPoint[1], $color);        
        imageline($this->im, 
                $this->normalizeEntropy($satLiq['specificEntropy']), 
                $this->normalizeTemp($satTemperature),
                $this->normalizeEntropy($satGas['specificEntropy']), 
                $this->normalizeTemp($satTemperature),
                $color);
        $lastPoint = array(
                $this->normalizeEntropy($satGas['specificEntropy']), 
                $this->normalizeTemp($satTemperature),
               );
        
        //Gas Section
        for($temperature=$satTemperature+.1;$temperature<=$this->maxTemp+10;$temperature+=10){
            $propsPT = $this->sP->waterPropertiesPT( $pressure, $temperature );
            $newPoint = array(
                $this->normalizeEntropy($propsPT['specificEntropy']), 
                $this->normalizeTemp($temperature),
               );
            imageline($this->im, $lastPoint[0], $lastPoint[1], $newPoint[0], $newPoint[1], $color);
            $lastPoint = $newPoint;
        }
    }    
}
?>