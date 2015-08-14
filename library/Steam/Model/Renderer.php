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
 * Renders Diagram Components
 * @TODO Merge with Steam_Model_Renderer
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Renderer{
    
    public function renderModel($steamModel) {
        $this->steamModel = $steamModel;
        $this->translator = Zend_Registry::get('Zend_Translate');
        $this->mS = new Steam_MeasurementSystem();
        //$this->displayGrid();
        echo $this->displaySteamDetails(0,450);
        //$this->loadBoiler();
        $this->addSteamPoint(100, 63, 'Right.Top','Boiler Feedwater', $this->steamModel->boiler->feedwater);
        $this->addSteamPoint(34, 200, 'Right.Top','HP to MP PRV Feedwater', $this->steamModel->hpTmpPRV->desuperheatFluid); 
        $this->addSteamPoint(34, 350, 'Right.Top', 'HP to MP PRV Feedwater', $this->steamModel->mpTlpPRV->desuperheatFluid); 
        $this->addSteamPoint(34, 525, 'Left.Bottom','Feedwater',  $this->steamModel->deaerator->feedwater, 'switch'); 
        
        $this->highlightSteamPoints();
        $this->displayHighlights();
        echo implode('', $this->steamPoints);  
        
        //$this->displayGrid();
    
    }
    
    private function displaySteamDetails($top, $left){
    $steamDetails = '<table class="data" style="position: absolute; top: '.$top.'px; left: '.$left.'px; width: 350px;">
                        <tr>            
                            <td colspan="2" style="background-color: #eeeeff" id="selectedTitle" ><h2 style="margin: 0px;"></h2></td>
                            <th>'.$this->translator->_('Mass Flow').'</th><td></td>
                        </tr>
                        <tr>
                            <th>'.$this->translator->_('Pressure').'</th><td></td>
                            <th>'.$this->translator->_('Sp. Enthalpy').'</th><td></td>
                        </tr>
                        <tr>
                            <th>'.$this->translator->_('Temperature').'</th><td></td>
                            <th>'.$this->translator->_('Sp. Entropy').'</th><td></td>
                        </tr>  
                        <tr>
                            <th>'.$this->translator->_('Phase').'</th><td></td>        
                        </tr>
                    </table>';
        return $steamDetails;
    }
    
    private function displayGrid(){
        for($x=0;$x<32;$x++){
            for($y=0;$y<30;$y++){
                $top = $y*25;
                $left = $x*25;
                if ( (ceil($x/4) == $x/4) or (ceil($y/4) == $y/4) ){
                    echo "<div style='position: absolute; top: {$top}px; left: {$left}px; width: 24px; height: 24px; border: 1px solid grey; background-color: #DDD'></div>";
                }else{
                    echo "<div style='position: absolute; top: {$top}px; left: {$left}px; width: 24px; height: 24px; border: 1px solid grey'></div>";
                }
            }
            
        }
        
    }
    public function drawEquipment($top, $left, $type){
        $this->translator = Zend_Registry::get('Zend_Translate');  
        $equipment = "
            
        <div style=\"position: absolute; top: ".($top-50)."px; left: ".($left-50)."px; width: 100px; height: 100px;\" >
        <img src='images/equipment/Boiler.gif' alt='Boiler'>
            <div style=\"position: relative; top: -62px; left: 32px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 50px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; text-align: center;\"><p style=\" display: inline; padding-left: 2px;\">{$this->translator->_('Boiler')}</p>
            </div>
        </div>
            ";
        
        
        
        return $equipment;
    }
    
    public function drawMultiJoint($top, $left, $type,$color = 'Blue'){
        
        $joint = "<img src='images/pipes/Pipe{$color}{$type}.gif'  style=\"position: absolute; top: ".($top-8)."px; left: ".($left-8)."px; width: 16px; height: 16px;\" alt='pipe'>";
        return $joint;
    }
    
    
    public function drawPipe($top1,$left1,$top2,$left2,$direction, $color = 'blue'){                
        $pipe = '';
        $top = 0;
        $diameter=16;        
        $colors = array(
            'Yellow', 'Red', 'Brown', 'Purple', 'Orange', 'Green',            
        );
        if ($left2<$left1) list($left2,$top2,$left1,$top1) = array($left1,$top1,$left2,$top2);
          
        //Northeast Quad
        if ($top1>$top2){
            if ($direction=='hv') {
                $angle = 'Lup';
                $angleTop = $top1;
                $angleLeft = $left2;
            }
            if ($direction=='vh') {
                $angle = 'Rdown';
                $angleTop = $top2;
                $angleLeft = $left1;
            }                
        } 
        //Southeast Quad
        if ($top1<$top2 ){
            if ($direction=='vh') {
                $angle = 'Rup';
                $angleTop = $top2;
                $angleLeft = $left1;
            }
            if ($direction=='hv') {
                $angle = 'Ldown';
                $angleTop = $top1;
                $angleLeft = $left2;
            }
        }         
        
        //Vertical Line
        if ($left1<>$left2){
            $left = $left1;
            $width = $left2-$left1;
            if ($left2<$left1) {                
                $left = $left2;
                $width = $left1-$left2;
            }
            $top=$top2;
            if ($direction{0}=='h') $top = $top1;
            
            $pipe .= "<img src=\"images/pipes/Pipe{$color}Horiz.gif\" style=\"position: absolute; top: ".($top-$diameter/2)."px; left: {$left}px; width: {$width}px; height: {$diameter}px;\" alt='pipe'>";
        }
        
        //Horizantal Line
        if ($top1<>$top2){
            $top = $top1;
            $height = $top2-$top1;
            if ($top2<$top1) {                
                $top = $top2;
                $height = $top1-$top2;
            }
            $left=$left2;
            if ($direction{0}=='v') $left = $left1;            
            $pipe .= "<img src=\"images/pipes/Pipe{$color}Vert.gif\" style=\"position: absolute; top: {$top}px; left: ".($left-$diameter/2)."px; width: {$diameter}px; height: {$height}px;\" alt='pipe'>";
        }
        
        if ($top1<>$top2 and $left1<>$left2)
            $pipe .= "<img src=\"images/pipes/Pipe{$color}$angle.gif\" style=\"position: absolute; top: ".($angleTop-8)."px; left: ".($angleLeft-8)."px;\" alt='pipe'>";
            
        return $pipe;
    //<img src=\"images/pipes/PipeBlueRup.gif\" style=\"position: absolute; top: 298px; left: 25px;">
        
    }
    public function addSteamPoint($y,$x,$direction,$steamTitle=false,$steamObject){
        $backgroundColor = 'white';
        $opacity = .5;
        $extra = '';
        $this->steamPoints[] = $this->drawSteamPoint($x, $y, $direction, $backgroundColor, $opacity, $extra);
        $this->steamPointDetails[] = array(
            'Name' => $steamTitle,
            'SteamDto' => $steamObject,
            'Opacity' => $opacity,
        );
    }
    
    public function drawSteamPoint($y,$x,$direction, $backgroundColor = 'white', $opacity = .5, $extra = ''){
        $directions = explode('.', $direction);
        
        $boxVert = array(
            'width' => 14,
            'height' => 8,
        );
        $boxHoriz = array(
            'width' => 8,
            'height' => 14,
        );
        if ($directions[0]=='Up' or $directions[0]=='Down'){
            $box = $boxVert;
        }else{
            $box = $boxHoriz;
        }
        $xAdjust = -7;
        $yAdjust = -7;
        if ($directions[1]=='Left') $xAdjust = -10;
        if ($directions[1]=='Right') $xAdjust = 14;
        
        if ($directions[1]=='Top') $yAdjust = -10;
        if ($directions[1]=='Bottom') $yAdjust = 14;
        
        $steamPoint = 
        "<div style=\"position: absolute; left: ".($x-$box['width']/2-1)."px; top: ".($y-$box['height']/2-1)."px;  height: {$box['height']}px; width: {$box['width']}px; border: 1px black solid;\">
            <div  style=\"height: {$box['height']}px; width: {$box['width']}px;background-color: silver; opacity: .5;\"></div>
        </div>
        <img src='images/arrows/Arrow{$directions[0]}.gif' style='position: absolute; left: ".($x+$xAdjust-$box['width']/2-1)."px; top: ".($y+$yAdjust-$box['height']/2-1)."px;  opacity: 1;' alt='Arrow'>";
        
        return $steamPoint;
    }   
    
    private function highlightSteamPoints(){
        $this->hightlightedSteamPoints = array();
        foreach($this->steamPointDetails as $steamPointNum => $details){
            $this->hightlightedSteamPoints[$steamPointNum] = "

            $('#steamPoint{$steamPointNum}').mouseover(function(){ highlightSteamPoint{$steamPointNum}(); })
            $('#steamPoint{$steamPointNum}').mouseout(function(){ UNhighlightSteamPoint{$steamPointNum}(); })

            function highlightSteamPoint{$steamPointNum}(){
                $(\"#steamPoint{$steamPointNum}\").css(\"border\", \"1px solid blue\");
                $(\"#steamPoint{$steamPointNum}\").css(\"opacity\", \"1\");
                $(\"#selectedSteamPoint\").show();
                $(\"#selectedTitle\").html('{$details['Name']}');                
                ";
                if ($details['SteamDto'] instanceof Steam_Object and $details['SteamDto']->massFlow>=0.01){
                    $temperature = number_format($this->mS->localize($details['SteamDto']->temperature,'temperature'),2).' '.$this->mS->selected['temperature'];
                    $pressure = number_format($this->mS->localize($details['SteamDto']->pressure,'pressure'),2).' '.$this->mS->selected['pressure'];
                    $massflow = number_format($this->mS->localize($details['SteamDto']->massFlow,'massflow'),2).' '.$this->mS->selected['massflow'];
                    $specificEnthalpy = number_format($this->mS->localize($details['SteamDto']->specificEnthalpy,'specificEnthalpy'),2);
                    $specificEntropy = number_format($this->mS->localize($details['SteamDto']->specificEntropy,'specificEntropy'),2);
                }else{
                    $temperature = 'n/a';
                    $pressure = 'n/a';
                    $massflow = '<span style="color: red; font-weight: bold;">No Flow</span>';
                    $specificEnthalpy = 'n/a';
                    $specificEntropy = 'n/a';                                   
                }
                                $this->hightlightedSteamPoints[$steamPointNum] .= 
                "$(\"#selectedTemp\").html('".$temperature."');
                $(\"#selectedPressure\").html('".$pressure."');
                $(\"#selectedMassflow\").html('".$massflow."');
                $(\"#selectedEnthalpy\").html('".$specificEnthalpy."');
                $(\"#selectedEntropy\").html('".$specificEntropy."');";
                $this->hightlightedSteamPoints[$steamPointNum] .= "
            }
            function UNhighlightSteamPoint{$steamPointNum}(){
                $(\"#selectedSteamPoint\").hide();
                $(\"#steamPoint{$steamPointNum}\").css(\"border\", \"1px solid black\");
                $(\"#steamPoint{$steamPointNum}\").css(\"opacity\", \"{$details['Opacity']}\");
                
            }
                ";                                
        }
    }
    
    private function displayHighlights(){
                echo "<script>           
        $(document).ready(function() {
        $('#modelMaster').show();
        ";
        //foreach($this->pipes as $component => $pieces) echo $this->highlight[$component];
        //foreach($this->counterEquipment as $equipmentNum => $equipment) echo $this->highlightedEquipment[$equipmentNum];
        foreach($this->steamPointDetails as $steamPointNum => $details) echo $this->hightlightedSteamPoints[$steamPointNum];
        
        //echo implode('', $this->equipmentPopupsJS);
        echo "
            });
            </script>";
    }
    
}