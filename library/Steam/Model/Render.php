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
 * Renders Steam Model Diagram
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Render{
    var $unitSize = 25;
    
    /**
     *
     * @var Steam_Model_Constructor
     */
    var $steamModel;
    
    /**
     * Show Unused Equipment
     * @var bool
     */
    var $showUnused = false;
    
    var $dataPoints = array();
    var $equipmentLabels = array();
    var $equipmentWarnings = array();
    
    
    public function __construct($steamModel) {
        $this->translator = Zend_Registry::get('Zend_Translate');
        $this->mS = new Steam_MeasurementSystem();
        
        $this->steamModel = $steamModel;
        $this->hpHeaderLine = 125;
        $this->mpHeaderLine = 275;
        $this->lpHeaderLine = 425;
        $this->returnHeaderLine = 500;
        
        $this->loadPipes();                
        $this->loadEquipment();        
        $this->loadSteamPoints();
        
        $headerCount = $steamModel->headerCount;
         $this->headerCount = $headerCount;
        $this->loadHeaders($headerCount);
    
        $this->loadBoiler();
        
        $this->loadDA($headerCount);
        $this->loadBlowdown();
        
        if ($this->steamModel->turbineCondOn or $this->showUnused) $this->loadSteamTurbineCond();
        if ($headerCount==3 and ($this->steamModel->turbineHpMpOn or $this->showUnused)) $this->loadSteamTurbineHpMp();
        if ($headerCount>1 and ($this->steamModel->turbineHpLpOn or $this->showUnused)) $this->loadSteamTurbineHpLp();
        if ($headerCount==3 and ($this->steamModel->turbineMpLpOn or $this->showUnused)) $this->loadSteamTurbineMpLp();
        
        if ($headerCount==3) $this->loadPrvHpMp();
        if ($headerCount==3) $this->loadPrvMpLp();
        if ($headerCount==2) $this->loadPrvHpLp();
        
        if ($headerCount==3 and ($this->steamModel->hpCondFlash=='Yes' or $this->showUnused)) $this->loadFlashHpMp();
        if ($headerCount==3 and ($this->steamModel->mpCondFlash=='Yes' or $this->showUnused)) $this->loadFlashMpLp();
        if ($headerCount==2 and ($this->steamModel->mpCondFlash=='Yes' or $this->showUnused)) $this->loadFlashHpLp();
        
   
        $this->loadComponentDetails();
        $this->highlightPipes();
        $this->highlightEquipment();
        $this->equipmentPopupDetails();
        $this->highlightSteamPoints();
        //$this->displayGrid(); //Display Layout Grid
                        
        $this->displayHighlights();
        
        $this->displayPipes();
        $this->displayEquipment();
        
        ?>
<div id="diagramLegend" style=" position: absolute; top: 0px; left: 420px; z-index: 0; border: 1px solid black; background-color: #FAFAFA; padding: 4px;">
    <table>
        <tr><td style="vertical-align: middle;"><img src="images/pipes/PipePurpleHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px; "><span style="color: purple; font-weight: bold;"><?php echo $this->translator->_('Feedwater');?></span></td>
            <td style="vertical-align: middle;"><img src="images/pipes/PipeBrownHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px; margin-left: 10px;"><span style="color: brown; font-weight: bold;"><?php echo $this->translator->_('Vents/Blowdown');?></span></td></tr>
        
        <tr><td style="vertical-align: middle;"><img src="images/pipes/PipeBlueHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px; "><span style="color: blue; font-weight: bold;"><?php echo $this->translator->_('Condensate');?></span></td>
            <td style="vertical-align: middle;"><img src="images/pipes/PipeGreenHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px; margin-left: 10px; "><span style="color: Green; font-weight: bold;"><?php echo $this->translator->_('Return/Make-Up');?></span></td></tr>
        
        <tr><td style="vertical-align: middle;" colspan="2"><span style="font-weight: bold;  margin-left: 10px;"><?php echo $this->translator->_('Headers');?>:</span>
                <img src="images/pipes/PipeRedHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px;   margin-left: 5px;"><span style="color: red; font-weight: bold;"><?php echo $this->translator->_('HP');?></span>
                <?php if ($this->steamModel->headerCount==3){ ?><img src="images/pipes/PipeOrangeHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px;   margin-left: 5px;"><span style="color: orange; font-weight: bold;"><?php echo $this->translator->_('MP');?></span><?php } ?>
                <?php if ($this->steamModel->headerCount>1){ ?><img src="images/pipes/PipeYellowHoriz.gif" style="height: 16px; width: 10px; vertical-align: middle; padding: 3px;  margin-left: 5px;"><span style="color: #B8860B; font-weight: bold;"><?php echo $this->translator->_('LP');?></span></td></tr><?php } ?>
    </table>
</div>
        <div id="selectedSteamPoint" style="display: none; position: absolute; top: -30px; left: 400px; z-index: 10;">
            <table class="data" style="width: 350px; background-color: white;">
            <tr><td id="selectedTitle" colspan="2" style="font-weight: bold; background-color: #eeeeff;"></td>       
                    <th><?php echo $this->translator->_('Mass Flow')?></th><td id="selectedMassflow"></td>
                </tr>
                <tr>
                    <th style="width: 60px;"><?php echo $this->translator->_('Temperature')?></th><td id="selectedTemp"></td>
                    <th><?php echo $this->translator->_('Sp. Enthalpy')?></th><td id="selectedEnthalpy"></td>
                </tr>
                <tr>
                    <th><?php echo $this->translator->_('Pressure')?></th><td id="selectedPressure"></td>
                    <th><?php echo $this->translator->_('Sp. Entropy')?></th><td id="selectedEntropy"></td>
                </tr>
                <tr>
                    <th><?php echo $this->translator->_('Phase')?></th><td id="selectedPhase" colspan="2"></td>
                </tr>
                <tr>
                </tr>
            </table>
        </div>

        <div id="selectedComponent" style="display: none; position: absolute; top: -30px; left: 400px; z-index: 10;">
            <table class="data" style=" background-color: white; padding: 0px;">
            <tr><td id="selectedCompTitle"  style="font-weight: bold; background-color: #eeeeff; font-size: 1.25em;"></td>   
            </tr>
            <tr><td id="selectedCompText" style="padding: 0px;">Click for more details</td>   
            </tr>
            </table>
        </div>
        <?php
        echo implode('', $this->dataPoints);   
        echo implode('', $this->equipmentLabels);  
        echo implode('', $this->equipmentWarnings);   
        echo implode('', $this->steamPoints);   
        echo implode('', $this->equipmentPopups);

    }
          
    private function loadHeaders($headerCount){     
           
        if ($headerCount==1){
            $this->returnHeaderLine-=150;
            $this->lpHeaderLine = $this->mpHeaderLine;     
        }
                
        if ($headerCount==2){
            $this->returnHeaderLine-=150;
            $this->lpHeaderLine = $this->mpHeaderLine;            
        }
        
        $this->pipes[$component = 'HpHeader'][] = $this->connect(80,$this->hpHeaderLine,630,$this->hpHeaderLine, 'Red', $component);            
        $this->addSteamPoints('HP Process Steam', 600, $this->hpHeaderLine, 'Right', $this->steamModel->hpProcessSteam);  
        $this->addSteamPoints('Initial HP Condensate', 700, $this->hpHeaderLine, 'Right', $this->steamModel->hpCondInitial);
        $this->dataPoints[] = $this->addData($this->hpHeaderLine-36,615,$this->mS->displayMassflowMinl($this->steamModel->hpSteamUsage),'right');
        $this->dataPoints[] = $this->addData($this->hpHeaderLine-22,615,$this->mS->displayEnergyflowMinl($this->steamModel->energyUsageHP),'right');
        $this->dataPoints[] = $this->addData($this->hpHeaderLine+15,710,$this->mS->displayMassflowMinl($this->steamModel->hpCondInitial->massFlow),'right');
        $this->dataPoints[] = $this->addData($this->hpHeaderLine-19,92,$this->mS->displayPressureMinl($this->steamModel->highPressure),'left','darkred');
                
        $status = 'HpProcess';
        if ($this->steamModel->hpSteamUsage==0) $status='ProcessOff';
        $this->allEquipment[] =  $this->placeEquipment($status, $this->hpHeaderLine-40, 615, 70, 50, 'HP Steam Users');        
        $this->equipmentCode[count($this->allEquipment)-1] = 'hpProcess';  
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->hpHeaderLine-29).'px; left: 624px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 50px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; text-align: center;">'.$this->translator->_('Process Usage').'
            </div>';  
        
        if ($this->steamModel->hpHeader->finalHeaderSteam->phase<>'Gas'){
            $this->equipmentWarnings[] = 
            '<div id="pipeWarningHpHeader" style="position: absolute; top: '.($this->hpHeaderLine-16).'px; left: 150px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
        $this->pipes[$component = 'Cond'][] = $this->drawPipe(670,$this->hpHeaderLine, 725,$this->returnHeaderLine-8, 'hv', 'Blue', $component);
        
        if ($headerCount==3){
            
            $this->pipes[$component = 'MpHeader'][] = $this->connect(80,$this->mpHeaderLine,630,$this->mpHeaderLine, 'Orange', $component);
            
            $this->pipes[$component = 'Cond'][] = $this->connect(670,$this->mpHeaderLine,720,$this->mpHeaderLine, 'Blue', $component);     
            $this->pipes[$component = 'Cond'][] = $this->turn(725, $this->mpHeaderLine, 'Et', 'Blue', $component);
            $this->addSteamPoints('MP Process Steam', 600, $this->mpHeaderLine, 'Right', $this->steamModel->mpProcessSteam); 
             $this->addSteamPoints('Initial MP Condensate', 700, $this->mpHeaderLine, 'Right', $this->steamModel->mpCondInitial);
            $this->dataPoints[] = $this->addData($this->mpHeaderLine+17,560,$this->mS->displayMassflowMinl($this->steamModel->mpSteamUsage));
            $this->dataPoints[] = $this->addData($this->mpHeaderLine+33,560,$this->mS->displayEnergyflowMinl($this->steamModel->energyUsageMP));            
            $this->dataPoints[] = $this->addData($this->mpHeaderLine-19,92,$this->mS->displayPressureMinl($this->steamModel->mediumPressure),'left','#964B00');
            $this->dataPoints[] = $this->addData($this->mpHeaderLine+15,710,$this->mS->displayMassflowMinl($this->steamModel->mpCondInitial->massFlow),'right');
                        
            $status = 'MpProcess';
            if ($this->steamModel->mpSteamUsage==0) $status='ProcessOff';
            $this->allEquipment[] =  $this->placeEquipment($status, $this->mpHeaderLine-40, 615, 70, 50, 'MP Steam Users');
            $this->equipmentCode[count($this->allEquipment)-1] = 'mpProcess';  
            $this->equipmentLabels[] = 
            '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->mpHeaderLine-29).'px; left: 624px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 50px;
                opacity:.90; filter:alpha(opacity=90); padding: 1px; text-align: center;">'.$this->translator->_('Process Usage').'
                </div>';  
            
        if ($this->steamModel->mpHeader->finalHeaderSteam->phase<>'Gas'){
            $this->equipmentWarnings[] = 
            '<div id="pipeWarningMpHeader" style="position: absolute; top: '.($this->mpHeaderLine-16).'px; left: 150px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
        }
        if ($headerCount>1){            
            $this->pipes[$component = 'LpHeader'][] = $this->connect(80,$this->lpHeaderLine,630,$this->lpHeaderLine, 'Yellow', $component);
            $this->pipes[$component = 'Cond'][] = $this->connect(670,$this->lpHeaderLine,720,$this->lpHeaderLine, 'Blue', $component);     
            $this->pipes[$component = 'Cond'][] = $this->turn(725, $this->lpHeaderLine, 'Et', 'Blue', $component);
            
            if ($this->steamModel->lpSteamVent<>0 or $this->showUnused){
                $ventLine = 420;
                if ($headerCount==2) $ventLine = 320;
          
                $this->pipes[$component = 'LpHeader'][] = $this->turn($ventLine, $this->lpHeaderLine, 'St', 'Yellow', $component);                        
                $this->pipes[$component = 'Vents'][] = $this->drawPipe($ventLine,$this->lpHeaderLine-8, $ventLine+10,$this->lpHeaderLine-40, 'vh', 'Brown', $component);   
                $this->addSteamPoints('LP Vented Steam', $ventLine, $this->lpHeaderLine-25, 'Up', $this->steamModel->lpProcessSteam,'switch');            
                $this->dataPoints[] = $this->addData($this->lpHeaderLine-60,$ventLine-5,$this->mS->displayMassflowMinl($this->steamModel->lpSteamVent));
            }
            
            $this->addSteamPoints('LP Process Steam', 600, $this->lpHeaderLine, 'Right', $this->steamModel->lpProcessSteam); 
             $this->addSteamPoints('Initial LP Condensate', 700, $this->lpHeaderLine, 'Right', $this->steamModel->lpCondInitial);
            $this->dataPoints[] = $this->addData($this->lpHeaderLine+17,560,$this->mS->displayMassflowMinl($this->steamModel->lpSteamUsage));
            $this->dataPoints[] = $this->addData($this->lpHeaderLine+33,560,$this->mS->displayEnergyflowMinl($this->steamModel->energyUsageLP));
            $this->dataPoints[] = $this->addData($this->lpHeaderLine-19,92,$this->mS->displayPressureMinl($this->steamModel->lowPressure),'left','#B8860B');
            $this->dataPoints[] = $this->addData($this->lpHeaderLine+15,710,$this->mS->displayMassflowMinl($this->steamModel->lpCondInitial->massFlow),'right');
            
            $status = 'LpProcess';
            if ($this->steamModel->lpSteamUsage==0) $status='ProcessOff';
            $this->allEquipment[] =  $this->placeEquipment($status, $this->lpHeaderLine-40, 615, 70, 50, 'LP Steam Users');            
            $this->equipmentCode[count($this->allEquipment)-1] = 'lpProcess';  
            $this->equipmentLabels[] = 
            '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->lpHeaderLine-29).'px; left: 624px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 50px;
                opacity:.90; filter:alpha(opacity=90); padding: 1px; text-align: center;">'.$this->translator->_('Process Usage').'
                </div>';  
        if ($this->steamModel->lpHeader->finalHeaderSteam->phase<>'Gas'){
            $this->equipmentWarnings[] = 
            '<div id="pipeWarningLpHeader" style="position: absolute; top: '.($this->lpHeaderLine-16).'px; left: 150px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
        }
            
        $this->loadHeaderReturn();
    }
    
    private function loadHeaderReturn(){
        $centerLine = 725;
            $this->pipes[$component = 'Return'][] = $this->connect(245,$this->returnHeaderLine,635,$this->returnHeaderLine, 'Green', $component);
            $this->pipes[$component = 'Return'][] = $this->connect(245,$this->returnHeaderLine,245,$this->returnHeaderLine+100, 'Green', $component);

            $this->pipes[$component = 'Return'][] = $this->connect(115,$this->returnHeaderLine+40,115,$this->returnHeaderLine+100, 'Green', $component);
            $this->pipes[$component = 'Return'][] = $this->connect(115,$this->returnHeaderLine+100,245,$this->returnHeaderLine+100, 'Green', $component);
            $this->pipes[$component = 'Return'][] = $this->connect(245,$this->returnHeaderLine+75,455,$this->returnHeaderLine+75, 'Green', $component);
                                    
            $this->pipes[$component = 'Return'][] = $this->turn(245, $this->returnHeaderLine, 'Rdown', 'Green', $component);
  
        if ($this->steamModel->condReturnFlash=='Yes'){            
            $this->pipes[$component = 'Cond'][] = $this->connect(695,$this->returnHeaderLine+5,695,$this->returnHeaderLine+40, 'Blue', $component);
            $this->pipes[$component = 'Return'][] = $this->drawPipe(640,$this->returnHeaderLine,700,$this->returnHeaderLine+55, 'vh','Green', $component);             
            $this->pipes[$component = 'Return'][] = $this->turn(640, $this->returnHeaderLine, 'Ldown', 'Green', $component);
        }else{
            $this->pipes[$component = 'Return'][] = $this->connect(610,$this->returnHeaderLine,715,$this->returnHeaderLine, 'Green', $component);
            $this->addSteamPoints('Condensate Returned', 650, $this->returnHeaderLine, 'Left', $this->steamModel->condensate);
        }
            
            $this->pipes[$component = 'Return'][] = $this->turn(245, $this->returnHeaderLine+100, 'Lup', 'Green', $component);
            $this->pipes[$component = 'Return'][] = $this->turn(115, $this->returnHeaderLine+100, 'Rup', 'Green', $component);
            $this->pipes[$component = 'Return'][] = $this->turn(245, $this->returnHeaderLine+75, 'Wt', 'Green', $component);            
            $this->pipes[$component = 'Cond'][] = $this->turn(725, $this->returnHeaderLine, 'Lup', 'Blue', $component);

        $this->addSteamPoints('All Condensate', 725, $this->returnHeaderLine-50, 'Down', $this->steamModel->lpCondFinal,'switch');
        
        $this->dataPoints[] = $this->addData($this->returnHeaderLine-6,$centerLine-10,$this->mS->displayTemperatureMinl($this->steamModel->condReturnTemp),'right');
        
        if ($this->steamModel->condReturnFlash=='Yes'){    
            $this->dataPoints[] = $this->addData($this->returnHeaderLine+15,630,$this->mS->displayMassflowMinl($this->steamModel->condensate->massFlow),'right');
            $this->dataPoints[] = $this->addData($this->returnHeaderLine+15,765,$this->mS->displayMassflowMinl($this->steamModel->condReturnFlashTank->satGas->massFlow),'right');
            $this->pipes[$component = 'Vents'][] = $this->drawPipe($centerLine-30,$this->returnHeaderLine+55, $centerLine+30,$this->returnHeaderLine+30, 'hv', 'Brown', $component);   
            
            
            $this->addSteamPoints('Condensate Flash', $centerLine+30, $this->returnHeaderLine+37, 'Up', $this->steamModel->condReturnFlashTank->satGas);
            $this->addSteamPoints('Condensate Returned', 640, $this->returnHeaderLine+23, 'Up', $this->steamModel->condReturnFlashTank->satLiquid, 'switch');
        
            $this->allEquipment[] = $this->placeEquipment('FlashTk', $this->returnHeaderLine+30, $centerLine-55, 50, 50, 'Condensate Flash Tank', isset($this->steamModel->condReturnFlashTank)?$this->steamModel->condReturnFlashTank->displayFlashTank():'n/a');
            $this->equipmentCode[count($this->allEquipment)-1] = 'CondFlashTk';  
            $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->returnHeaderLine+65).'px; left: '.($centerLine-25).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('Flash Tank').'
            </div>'; 
            
        if (count($this->steamModel->condReturnFlashTank->warnings)>0){
            $this->equipmentWarnings[] = 
            '<div id="equipmentWarning'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->returnHeaderLine+60).'px; left: '.($centerLine-55).'px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
        }
        
    }
    
    private function loadBlowdown(){

        if ($this->steamModel->blowdownHeatX == 'Yes' or ($this->steamModel->blowdownFlashTank->inletSteam->massFlow>0 or $this->showUnused) ){       
            if ($this->steamModel->blowdownHeatX == 'Yes'){
                $this->pipes[$component = 'Blowdown'][] = $this->drawPipe(490,$this->lpHeaderLine+125,575,$this->lpHeaderLine+150,'vh', 'Brown', $component); 
                if ($this->steamModel->blowdownFlashTank->inletSteam->massFlow>0) $this->addSteamPoints('Boiler Blowdown Drain', 490, $this->lpHeaderLine+127, 'Up', $this->steamModel->blowdownFlashTank->satLiquid,'switch');           
            }else{
                $this->pipes[$component = 'Blowdown'][] = $this->drawPipe(490,$this->lpHeaderLine+185,575,$this->lpHeaderLine+150,'vh', 'Brown', $component); 
                $this->addSteamPoints('Boiler Blowdown Drain', 490, $this->lpHeaderLine+167, 'Down', $this->steamModel->blowdownFlashTank->satLiquid,'switch');   
                $this->dataPoints[] = $this->addData($this->lpHeaderLine+180,500,$this->mS->displayMassflowMinl($this->steamModel->blowdownFlashTank->satLiquid->massFlow));       
            }
            $this->addSteamPoints('Boiler Blowdown', 560, $this->lpHeaderLine+150, 'Left', $this->steamModel->boiler->blowdown);  
            $this->dataPoints[] = $this->addData($this->lpHeaderLine+144,580,$this->mS->displayMassflowMinl($this->steamModel->boiler->blowdown->massFlow));
        }
        
        if ($this->steamModel->blowdownHeatX == 'Yes'){
            $this->addSteamPoints('Make Up Water', 275, $this->lpHeaderLine+150, 'Left', $this->steamModel->makeupWaterHeated);
            
            $this->dataPoints[] = $this->addData($this->returnHeaderLine+50,256,$this->mS->displayTemperatureMinl($this->steamModel->makeupWaterHeated->temperature),'left');
            $this->pipes[$component = 'Blowdown'][] = $this->drawPipe(490,$this->lpHeaderLine+125,400,$this->lpHeaderLine+100,'hv', 'Brown', $component); 
            $this->pipes[$component = 'Blowdown'][] = $this->drawPipe(308,$this->lpHeaderLine+195,400,$this->lpHeaderLine+100,'vh', 'Brown', $component); 
            $this->addSteamPoints('Boiler Blowdown Drain', 308, $this->lpHeaderLine+180, 'Down', $this->steamModel->blowdownHeatExchanger->hotOutlet,'switch');   
            $this->dataPoints[] = $this->addData($this->lpHeaderLine+180,297,$this->mS->displayTemperatureMinl($this->steamModel->blowdownHeatExchanger->hotOutlet->temperature),'right');           
                        
            $this->allEquipment[] = $this->placeEquipment('MakeupWater', $this->lpHeaderLine+137,295, 25, 25, 'Blowdown Heat Exchanger', $this->steamModel->blowdownHeatExchanger->displayHeatXProperties());
            $this->equipmentCode[count($this->allEquipment)-1] = 'BlowdownHeatX';  
                    $this->equipmentLabels[] = 
                ''; 
        }
        
        //Condensate Return
        $this->addSteamPoints('Make Up Water', 375, $this->lpHeaderLine+150, 'Left', $this->steamModel->makeupWater);
        $this->addSteamPoints('Condensate and Feedwater', 163, $this->lpHeaderLine+175, 'Left', $this->steamModel->deaerator->daWaterFeed);
        $this->addSteamPoints('Condensate Returned', 275, $this->lpHeaderLine+75, 'Left', $this->steamModel->condensate);  
        
        $this->dataPoints[] = $this->addData($this->returnHeaderLine+100,460,$this->mS->displayTemperatureMinl($this->steamModel->makeupWater->temperature),'right');
        $this->dataPoints[] = $this->addData($this->returnHeaderLine+92,395,$this->mS->displayMassflowMinl($this->steamModel->makeupWater->massFlow),'right');
        $this->dataPoints[] = $this->addData($this->returnHeaderLine+49,395,$this->mS->displayVolumeflowMinl($this->steamModel->makeupWater->volumeFlow),'right');
        
            if ($this->headerCount>1 and ($this->steamModel->blowdownFlashTank->inletSteam->massFlow>0 or $this->showUnused) ) {
                
                $this->pipes[$component = 'LpHeader'][] = $this->drawPipe(525,$this->lpHeaderLine,525,$this->lpHeaderLine+150,'vh', 'Yellow', $component);

                $this->pipes[$component = 'LpHeader'][] = $this->turn(525,$this->lpHeaderLine, 'Nt', 'Yellow', $component);
                $this->addSteamPoints('Blowdown Flash', 525, $this->lpHeaderLine+25, 'Up', $this->steamModel->blowdownGasToLp);             
                $this->dataPoints[] = $this->addData($this->lpHeaderLine+20,506,$this->mS->displayMassflowMinl($this->steamModel->blowdownFlashTank->satGas->massFlow),'right');
                
                $this->allEquipment[] = $this->placeEquipment('FlashTk', $this->lpHeaderLine+125, 500, 50, 50, 'Blowdown Flash Tank', isset($this->steamModel->blowdownFlashTank)?$this->steamModel->blowdownFlashTank->displayFlashTank():'n/a');
                $this->equipmentCode[count($this->allEquipment)-1] = 'FlashTk';  
                    $this->equipmentLabels[] = 
                '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->lpHeaderLine+115).'px; left: '.(500+35).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
                    opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('Flash Tank').'
                    </div>'; 
            }
            $this->allEquipment[] = $this->placeEquipment('MakeupWater', $this->lpHeaderLine+125,400, 75, 50, 'Make-Up Water'); 
            $this->equipmentCode[count($this->allEquipment)-1] = 'MakeUpWater';  
                    $this->equipmentLabels[] = 
                '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->lpHeaderLine+136).'px; left: '.(407).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 61px;
                    opacity:.90; filter:alpha(opacity=90); padding: 1px; width:57px; text-align: center;">'.$this->translator->_('Make-Up Water').'
                    </div>'; 
        
    }
    
    private function loadBoiler(){
        $this->addSteamPoints('Boiler Feedwater', 60, 63, 'Right', $this->steamModel->boiler->feedwater);
        $this->addSteamPoints('Boiler Blowdown', 315, 82, 'Right', $this->steamModel->boiler->blowdown); 
        $this->addSteamPoints('Boiler Steam', 275, 33, 'Down', $this->steamModel->boiler->outletSteam);
        $this->pipes[$component = 'Feedwater'][] = $this->drawPipe(10,$this->hpHeaderLine, 130,63, 'vh', 'Purple', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->drawPipe(220,19, 275,$this->hpHeaderLine, 'hv', 'Red', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->turn(275, $this->hpHeaderLine, 'St', 'Red', $component);
        $this->pipes[$component = 'Blowdown'][] = $this->drawPipe(220,82, 325,82, 'hv', 'Brown', $component);
        
        $this->dataPoints[] = $this->addData(77,184,$this->mS->displayEnergyflowMinl($this->steamModel->boiler->fuelEnergy),'right');   
        $this->dataPoints[] = $this->addData(5,153,$this->mS->displayEnergyflowMinl($this->steamModel->boiler->fuelEnergy-$this->steamModel->boiler->boilerEnergy),'right');   
        $this->dataPoints[] = $this->addData(40,103,$this->mS->displayMassflowMinl($this->steamModel->boiler->feedwater->massFlow),'right');
        $this->dataPoints[] = $this->addData(0,227,$this->mS->displayMassflowMinl($this->steamModel->boiler->outletSteam->massFlow));      
        $this->dataPoints[] = $this->addData(27,285,$this->mS->displayTemperatureMinl($this->steamModel->boiler->outletSteam->temperature));
        $this->dataPoints[] = $this->addData(76,327,$this->mS->displayMassflowMinl($this->steamModel->boiler->blowdown->massFlow));
        
        $this->allEquipment[] = $this->placeEquipment('Boiler', 0, 125, 100, 100, 'Boiler', $this->steamModel->boiler->displayBoilerProperties());
        $this->equipmentCode[count($this->allEquipment)-1] = 'boiler';  
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 40px; left: 158px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 50px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; text-align: center;"><p style=" display: inline; padding-left: 2px;">'.$this->translator->_('Boiler').'</p>
            </div>';        
    }
    
    private function loadDA($headerCount){            //DA Tie-In
        $this->pipes[$component = 'Feedwater'][] = $this->drawPipe(10, $this->hpHeaderLine, 70, $this->returnHeaderLine+25, 'vh', 'Purple', $component);
        $this->pipes[$component = 'Vents'][] = $this->drawPipe(115, $this->returnHeaderLine-15, 123, $this->returnHeaderLine-50, 'vh', 'Brown', $component);
        
        if ($headerCount>1){
            $this->pipes[$component = 'LpHeader'][] = $this->drawPipe(145, $this->returnHeaderLine+25, 220, $this->lpHeaderLine, 'hv', 'Yellow', $component);
            $this->pipes[$component = 'LpHeader'][] = $this->turn(220,$this->lpHeaderLine, 'Nt', 'Yellow', $component);
        }else{
            $this->pipes[$component = 'HpHeader'][] = $this->drawPipe(125, $this->hpHeaderLine, 80, 270, 'vh', 'Red', $component);
            $this->pipes[$component = 'HpHeader'][] = $this->drawPipe(80, 270, 220, 275, 'vh', 'Red', $component);
            $this->pipes[$component = 'HpHeader'][] = $this->drawPipe(145, $this->returnHeaderLine+25, 220, 275, 'hv', 'Red', $component);
            $this->pipes[$component = 'HpHeader'][] = $this->turn(220, 275, 'Ldown', 'Red', $component);
        }
        $this->dataPoints[] = $this->addData($this->returnHeaderLine-57,127,$this->mS->displayMassflowMinl($this->steamModel->deaerator->daVentSteam->massFlow),'left');
        $this->dataPoints[] = $this->addData($this->returnHeaderLine-17,20,$this->mS->displayMassflowMinl($this->steamModel->deaerator->feedwater->massFlow),'left');
        $this->dataPoints[] = $this->addData($this->returnHeaderLine-20,203,$this->mS->displayMassflowMinl($this->steamModel->deaerator->daSteamFeed->massFlow),'right');
        $this->dataPoints[] = $this->addData($this->returnHeaderLine+75,130,$this->mS->displayMassflowMinl($this->steamModel->deaerator->daWaterFeed->massFlow),'left');
 
        $this->addSteamPoints('DA Steam Use', 220, $this->returnHeaderLine-12, 'Down', $this->steamModel->deaerator->daSteamFeed);    
        $this->addSteamPoints('Feedwater', 10, $this->returnHeaderLine+8, 'Up', $this->steamModel->deaerator->feedwater, 'switch'); 
        $this->addSteamPoints('DA Vented Steam', 115, $this->returnHeaderLine-32, 'Up', $this->steamModel->deaerator->daVentSteam, 'switch');
        
        $this->allEquipment[] = $this->placeEquipment('Da', $this->returnHeaderLine-35, 64, 100, 100, 'Deaerator',$this->steamModel->deaerator->displayDA());
        $this->equipmentCode[count($this->allEquipment)-1] = 'deaerator';  
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->returnHeaderLine+18).'px; left: 79px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 100px;
             opacity:1; filter:alpha(opacity=100); padding: 1px; width:68px; text-align: center;">'.$this->translator->_('Deaerator').'
            </div>';    
    }
    
    private function loadSteamTurbineCond(){
        $centerLine = 220;
        $turbine = $this->steamModel->turbineCondModel;
        $this->pipes[$component = 'HpHeader'][] = $this->connect($centerLine-10,$this->hpHeaderLine,$centerLine-10,160, 'Red', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->turn($centerLine-10, $this->hpHeaderLine, 'Nt', 'Red', $component);
        $this->addSteamPoints('HP to MP Turbine Inlet', $centerLine-10, 142, 'Down', $turbine->inletSteam);
        
        $this->pipes[$component = 'Return'][] = $this->connect($centerLine+25,240,$centerLine+25,$this->returnHeaderLine, 'Green', $component);
        $this->pipes[$component = 'Return'][] = $this->turn($centerLine+25, $this->returnHeaderLine, 'Wt', 'Green', $component);
        $this->addSteamPoints('HP to MP Turbine Outlet', $centerLine+25, 258, 'Down', $turbine->outletSteam, 'switch');           
                                
        $this->dataPoints[] = $this->addData(136,$centerLine+3,$this->mS->displayMassflowMinl($turbine->inletSteam->massFlow));
        $this->dataPoints[] = $this->addData(250,$centerLine+12,$this->mS->displayPowerMinl($turbine->powerOut),'right');                  
        $status = '';
        if ($turbine->inletSteam->massFlow==0) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('SteamTurbine'.$status, 150, $centerLine-50, 100, 100, 'HP to MP Turbine', $turbine->displayTurbine());
        $this->equipmentCode[count($this->allEquipment)-1] = 'turbineCond';  
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 175px; left: '.($centerLine+5).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('Condensing Steam Turbine').'
            </div>';
        if (count($turbine->warnings)>0){
            $this->equipmentWarnings[] = 
            '<div id="equipmentWarning'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 210px; left: '.($centerLine+10).'px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
    }
         
    private function loadSteamTurbineHpMp(){
        $centerLine = 350;
        $turbine = $this->steamModel->turbineHpMpModel;
        $this->pipes[$component = 'HpHeader'][] = $this->connect($centerLine-10,$this->hpHeaderLine,$centerLine-10,160, 'Red', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->turn($centerLine-10, $this->hpHeaderLine, 'Nt', 'Red', $component);
        $this->addSteamPoints('HP to MP Turbine Inlet', $centerLine-10, 142, 'Down',$turbine->inletSteam);
        
        $this->pipes[$component = 'MpHeader'][] = $this->connect($centerLine+25,240,$centerLine+25,$this->mpHeaderLine, 'Orange', $component);
        $this->pipes[$component = 'MpHeader'][] = $this->turn($centerLine+25, $this->mpHeaderLine, 'St', 'Orange', $component);
        $this->addSteamPoints('HP to MP Turbine Outlet', $centerLine+25, 258, 'Down', $turbine->outletSteam, 'switch');           
                        
        $this->dataPoints[] = $this->addData(136,$centerLine+3,$this->mS->displayMassflowMinl($turbine->inletSteam->massFlow));
        $this->dataPoints[] = $this->addData(250,$centerLine+12,$this->mS->displayPowerMinl($turbine->powerOut),'right');        
        $status = '';
        if ($turbine->inletSteam->massFlow==0) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('SteamTurbine'.$status, 150, $centerLine-50, 100, 100, 'HP to MP Turbine', $turbine->displayTurbine()); 
        $this->equipmentCode[count($this->allEquipment)-1] = 'turbineHpMp';
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 175px; left: '.($centerLine+5).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('HP to MP Steam Turbine').'
            </div>';    
        if (count($turbine->warnings)>0){
            $this->equipmentWarnings[] = 
            '<div id="equipmentWarning'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 210px; left: '.($centerLine+10).'px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
    }
    
    private function loadSteamTurbineHpLp(){
        $centerLine = 480;
        $turbine = $this->steamModel->turbineHpLpModel;
        $this->pipes[$component = 'HpHeader'][] = $this->connect($centerLine-10,$this->hpHeaderLine,$centerLine-10,160, 'Red', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->turn($centerLine-10, $this->hpHeaderLine, 'Nt', 'Red', $component);
        $this->addSteamPoints('HP to LP Turbine Inlet', $centerLine-10, 142, 'Down', $turbine->inletSteam);
        
        $this->pipes[$component = 'LpHeader'][] = $this->connect($centerLine+25,240,$centerLine+25,$this->lpHeaderLine, 'Yellow', $component);
        $this->pipes[$component = 'LpHeader'][] = $this->turn($centerLine+25, $this->lpHeaderLine, 'St', 'Yellow', $component);
        $this->addSteamPoints('HP to LP Turbine Outlet', $centerLine+25, 258, 'Down', $turbine->outletSteam, 'switch');           
                        
        $this->dataPoints[] = $this->addData(136,$centerLine+3,$this->mS->displayMassflowMinl($turbine->inletSteam->massFlow));
        $this->dataPoints[] = $this->addData(250,$centerLine+12,$this->mS->displayPowerMinl($turbine->powerOut),'right');                  
        
        $status = '';
        if ($turbine->inletSteam->massFlow==0) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('SteamTurbine'.$status, 150, $centerLine-50, 100, 100, 'HP to LP Turbine', $turbine->displayTurbine());
        $this->equipmentCode[count($this->allEquipment)-1] = 'turbineHpLp';
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 175px; left: '.($centerLine+5).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('HP to LP Steam Turbine').'
            </div>';    
        if (count($turbine->warnings)>0){
            $this->equipmentWarnings[] = 
            '<div id="equipmentWarning'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 210px; left: '.($centerLine+10).'px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
    }
    
    private function loadSteamTurbineMpLp(){
        $centerLine = 350;
        $turbine = $this->steamModel->turbineMpLpModel;
        $this->pipes[$component = 'MpHeader'][] = $this->connect($centerLine-10,$this->mpHeaderLine,$centerLine-10,310, 'Orange', $component);
        $this->pipes[$component = 'MpHeader'][] = $this->turn($centerLine-10, $this->mpHeaderLine, 'Nt', 'Orange', $component);
        $this->addSteamPoints('MP to LP Turbine Inlet', $centerLine-10, 292, 'Down', $turbine->inletSteam);
        
        $this->pipes[$component = 'LpHeader'][] = $this->connect($centerLine+25,390,$centerLine+25,$this->lpHeaderLine, 'Yellow', $component);
        $this->pipes[$component = 'LpHeader'][] = $this->turn($centerLine+25, $this->lpHeaderLine, 'St', 'Yellow', $component);
        $this->addSteamPoints('MP to LP Turbine Outlet', $centerLine+25, 408, 'Down', $turbine->outletSteam, 'switch');           
                        
        $this->dataPoints[] = $this->addData(286,$centerLine+3,$this->mS->displayMassflowMinl($turbine->inletSteam->massFlow));
        $this->dataPoints[] = $this->addData(400,$centerLine+12,$this->mS->displayPowerMinl($turbine->powerOut),'right');        
        
        $status = '';
        if ($turbine->inletSteam->massFlow==0) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('SteamTurbine'.$status, 300, $centerLine-50, 100, 100, 'MP to LP Turbine', $turbine->displayTurbine());  
        $this->equipmentCode[count($this->allEquipment)-1] = 'turbineMpLp';        
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 325px; left: '.($centerLine+5).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('MP to LP Steam Turbine').'
            </div>';    
        if (count($turbine->warnings)>0){
            $this->equipmentWarnings[] = 
            '<div id="equipmentWarning'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 360px; left: '.($centerLine+10).'px;">
            <img src="images/warnings.gif" alt="Warning">
            </div>
            ';    
                };
    }
    
    private function loadPrvHpMp(){
        $centerLine = 75;
        $prv = $this->steamModel->hpTmpPRV;
        if (isset($prv->desuperheatTemp) ){
            $this->pipes[$component = 'Feedwater'][] = $this->connect(10, 200, $centerLine-11, 200, 'Purple', $component);
            $this->pipes[$component = 'Feedwater'][] = $this->turn(10, 200, 'Wt', 'Purple', $component);
            $this->addSteamPoints('HP to MP PRV Feedwater', 34, 200, 'Right', $prv->desuperheatFluid); 
            $this->dataPoints[] = $this->addData(193,$centerLine-12,'+'.$this->mS->displayMassflowMinl($prv->desuperheatFluid->massFlow));
        }
        $this->pipes[$component = 'HpHeader'][] = $this->connect($centerLine,$this->hpHeaderLine,$centerLine,160, 'Red', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->turn($centerLine, $this->hpHeaderLine, 'Rdown', 'Red', $component);  
        $this->pipes[$component = 'MpHeader'][] = $this->connect($centerLine,235,$centerLine,$this->mpHeaderLine, 'Orange', $component);
            
        $this->addSteamPoints('HP to MP PRV Inlet', $centerLine, 142, 'Down', $prv->inletSteam);
        $this->addSteamPoints('HP to MP PRV Outlet', $centerLine, 258, 'Down', $prv->outletSteam);   
        
        if ($prv->outletSteam->massFlow>0.000001  ) $this->dataPoints[] = $this->addData(230,$centerLine+14,$this->mS->displayTemperatureMinl($prv->outletSteam->temperature));        
        $this->dataPoints[] = $this->addData(136,$centerLine+12,$this->mS->displayMassflowMinl($prv->inletSteam->massFlow));
        
        $status = '';
        if ($prv->inletSteam->massFlow==0 ) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('Prv'.$status, 149, $centerLine-16, 30, 100, 'HP to MP PRV', $prv->displayPRV());  
        $this->equipmentCode[count($this->allEquipment)-1] = 'prvHpMp';        
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 168px; left: '.($centerLine-15).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('PRV').'
            </div>'; 
    }
    
    private function loadPrvMpLp(){
        $centerLine = 75;
        $prv = $this->steamModel->mpTlpPRV;
        if (isset($prv->desuperheatTemp) ){
            $this->pipes[$component = 'Feedwater'][] = $this->connect(10, 350, $centerLine-11, 350, 'Purple', $component);
            $this->pipes[$component = 'Feedwater'][] = $this->turn(10, 350, 'Wt', 'Purple', $component);
            $this->addSteamPoints('MP to LP PRV Feedwater', 34, 350, 'Right', $prv->desuperheatFluid); 
            $this->dataPoints[] = $this->addData(343,$centerLine-12,'+'.$this->mS->displayMassflowMinl($prv->desuperheatFluid->massFlow));
        }
        $this->pipes[$component = 'MpHeader'][] = $this->connect($centerLine,$this->mpHeaderLine,$centerLine,310, 'Orange', $component);
        $this->pipes[$component = 'MpHeader'][] = $this->turn($centerLine, $this->mpHeaderLine, 'Wt', 'Orange', $component);  
        $this->pipes[$component = 'LpHeader'][] = $this->connect($centerLine,385,$centerLine,$this->lpHeaderLine, 'Yellow', $component);
        $this->pipes[$component = 'LpHeader'][] = $this->turn($centerLine, $this->lpHeaderLine, 'Rup', 'Yellow', $component);  
        
        $this->addSteamPoints('MP to LP PRV Inlet', $centerLine, 292, 'Down', $prv->inletSteam);
        $this->addSteamPoints('MP to LP PRV Outlet', $centerLine, 408, 'Down', $prv->outletSteam);   
         
        if ($prv->outletSteam->massFlow>0.000001 ) $this->dataPoints[] = $this->addData(380,$centerLine+14,$this->mS->displayTemperatureMinl($prv->outletSteam->temperature));        
        $this->dataPoints[] = $this->addData(286,$centerLine+12,$this->mS->displayMassflowMinl($prv->inletSteam->massFlow));
        
        $status = '';
        if ($prv->inletSteam->massFlow==0) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('Prv'.$status, 299, $centerLine-16, 30, 100, 'MP to LP PRV', $prv->displayPRV());    
        $this->equipmentCode[count($this->allEquipment)-1] = 'prvMpLp';      
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 318px; left: '.($centerLine-15).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('PRV').'
            </div>'; 
    }
    
    private function loadPrvHpLp(){
        $centerLine = 75;
        $prv = $this->steamModel->mpTlpPRV;
        if (isset($prv->desuperheatTemp) ){
            $this->pipes[$component = 'Feedwater'][] = $this->connect(10, 200, $centerLine-11, 200, 'Purple', $component);
            $this->pipes[$component = 'Feedwater'][] = $this->turn(10, 200, 'Wt', 'Purple', $component);
            $this->addSteamPoints('HP to LP PRV Feedwater', 34, 200, 'Right', $prv->desuperheatFluid); 
            $this->dataPoints[] = $this->addData(193,$centerLine-12,'+'.$this->mS->displayMassflowMinl($prv->desuperheatFluid->massFlow));
        }
        $this->pipes[$component = 'HpHeader'][] = $this->connect($centerLine,$this->hpHeaderLine,$centerLine,160, 'Red', $component);
        $this->pipes[$component = 'HpHeader'][] = $this->turn($centerLine, $this->hpHeaderLine, 'Rdown', 'Red', $component);  
        $this->pipes[$component = 'LpHeader'][] = $this->connect($centerLine,235,$centerLine,$this->lpHeaderLine, 'Yellow', $component);
        $this->pipes[$component = 'LpHeader'][] = $this->turn($centerLine, $this->lpHeaderLine, 'Rup', 'Yellow', $component);  
            
        $this->addSteamPoints('HP to LP PRV Inlet', $centerLine, 142, 'Down', $prv->inletSteam);
        $this->addSteamPoints('HP to LP PRV Outlet', $centerLine, 258, 'Down', $prv->outletSteam);   
        
        if ($prv->outletSteam->massFlow>0.000001  ) $this->dataPoints[] = $this->addData(230,$centerLine+14,$this->mS->displayTemperatureMinl($prv->outletSteam->temperature));        
        $this->dataPoints[] = $this->addData(136,$centerLine+12,$this->mS->displayMassflowMinl($prv->inletSteam->massFlow));
        
        $status = '';
        if ($prv->inletSteam->massFlow==0) $status='Off';
        $this->allEquipment[] = $this->placeEquipment('Prv'.$status, 149, $centerLine-16, 30, 100, 'HP to LP PRV', $prv->displayPRV());     
        $this->equipmentCode[count($this->allEquipment)-1] = 'prvHpLp';            
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: 168px; left: '.($centerLine-15).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('PRV').'
            </div>'; 
    }
    
    private function loadFlashHpMp(){
        $centerLine = 725;
        
        $this->pipes[$component = 'MpHeader'][] = $this->drawPipe($centerLine-10,200, 580,$this->mpHeaderLine, 'vh', 'Orange', $component);            
        $this->pipes[$component = 'MpHeader'][] = $this->turn(580, $this->mpHeaderLine, 'St', 'Orange', $component);

        $this->addSteamPoints('Remaining HP Condensate', $centerLine, 250, 'Down', $this->steamModel->hpCondFinal,'switch');
        $this->addSteamPoints('HP Condensate Flash to MP', 665, 200, 'Left', $this->steamModel->hpCondGasToMp); 
        $this->dataPoints[] = $this->addData(214,675,$this->mS->displayMassflowMinl($this->steamModel->hpCondGasToMp->massFlow),'right');
        $this->allEquipment[] = $this->placeEquipment('FlashTk', 175, $centerLine-25, 50, 50, 'HP Condensate Flash Tank', isset($this->steamModel->hpCondFlashTank)?$this->steamModel->hpCondFlashTank->displayFlashTank():'n/a' );
        $this->equipmentCode[count($this->allEquipment)-1] = 'flashHpMp';  
        $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->mpHeaderLine-88).'px; left: '.($centerLine+8).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('Flash Tank').'
            </div>'; 
           
    }
    
    private function loadFlashMpLp(){
        $centerLine = 725;
            $this->pipes[$component = 'LpHeader'][] = $this->drawPipe($centerLine,350, 580,$this->lpHeaderLine, 'vh', 'Yellow', $component);            
            $this->pipes[$component = 'LpHeader'][] = $this->turn(580, $this->lpHeaderLine, 'St', 'Yellow', $component);
            $this->pipes[$component = 'Cond'][] = $this->connect($centerLine,$this->lpHeaderLine-55,$centerLine,$this->lpHeaderLine+55, 'Blue', $component);
            $this->pipes[$component = 'Cond'][] = $this->connect($centerLine-55,$this->lpHeaderLine,$centerLine-5,$this->lpHeaderLine, 'Blue', $component);            
            $this->pipes[$component = 'Cond'][] = $this->turn($centerLine, $this->lpHeaderLine, 'Et', 'Blue', $component);
            //$this->pipes[$component = 'Vents'][] = $this->drawPipe($centerLine+10,350, $centerLine+30,342, 'hv', 'Brown', $component);   
            $this->addSteamPoints('MP Condensate Flash to LP', 664, 350, 'Left', $this->steamModel->mpCondGasToLp); 
            $this->addSteamPoints('HP & MP Condensate', 725, 400, 'Down', $this->steamModel->mpCondFinal,'switch');
            if (isset($this->steamModel->mpCondFlashTank)) $this->addSteamPoints('HP & MP Condensate', 725, 300, 'Down', $this->steamModel->mpCondFlashTank->inletSteam,'switch');
            
            $this->dataPoints[] = $this->addData(365,675,$this->mS->displayMassflowMinl($this->steamModel->mpCondGasToLp->massFlow),'right');
            $this->allEquipment[] = $this->placeEquipment('FlashTk', 325, $centerLine-25, 50, 50, 'MP Condensate Flash Tank', isset($this->steamModel->mpCondFlashTank)?$this->steamModel->mpCondFlashTank->displayFlashTank():'n/a');
            $this->equipmentCode[count($this->allEquipment)-1] = 'flashMpLp';  
            $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->lpHeaderLine-88).'px; left: '.($centerLine+8).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('Flash Tank').'
            </div>'; 
            
    }
    
    private function loadFlashHpLp(){
        $centerLine = 725;
            $this->pipes[$component = 'LpHeader'][] = $this->drawPipe($centerLine,200, 580,$this->lpHeaderLine, 'vh', 'Yellow', $component);            
            $this->pipes[$component = 'LpHeader'][] = $this->turn(580, $this->lpHeaderLine, 'St', 'Yellow', $component);
            $this->pipes[$component = 'Cond'][] = $this->connect($centerLine,$this->lpHeaderLine-55,$centerLine,$this->lpHeaderLine+55, 'Blue', $component);
            $this->pipes[$component = 'Cond'][] = $this->connect($centerLine-55,$this->lpHeaderLine,$centerLine-5,$this->lpHeaderLine, 'Blue', $component);            
            $this->pipes[$component = 'Cond'][] = $this->turn($centerLine, $this->lpHeaderLine, 'Et', 'Blue', $component);
            $this->addSteamPoints('Remaining HP Condensate', 725, 250, 'Down', $this->steamModel->mpCondFinal,'switch');
            $this->addSteamPoints('HP Condensate Flash to LP', 664, 200, 'Left', $this->steamModel->mpCondGasToLp);  
            $this->dataPoints[] = $this->addData(214,675,$this->mS->displayMassflowMinl($this->steamModel->mpCondGasToLp->massFlow),'right');
            $this->allEquipment[] = $this->placeEquipment('FlashTk', 175, $centerLine-25, 50, 50, 'HP Condensate Flash Tank', isset($this->steamModel->hpCondFlashTank)?$this->steamModel->hpCondFlashTank->displayFlashTank():'n/a' );
            $this->equipmentCode[count($this->allEquipment)-1] = 'flashHpLp';  
            $this->equipmentLabels[] = 
        '<div id="equipmentLabel'.(count($this->counterEquipment)-1).'" style="position: absolute; top: '.($this->lpHeaderLine-88).'px; left: '.($centerLine+8).'px; color: #0000FF; font-weight: bold; font-size: 1em; background-color: white; width: 90px;
             opacity:.90; filter:alpha(opacity=90); padding: 1px; width:60px">'.$this->translator->_('Flash Tank').'
            </div>'; 
        
    }
    
    private function displayGrid(){
        for($x=0;$x<30;$x++){
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
    
    private function equipmentPopupDetails(){
        $this->equipmentPopups = array();
        $this->equipmentPopupsJS = array();
        foreach($this->counterEquipment as $equipmentNum => $equipment){
            if ($this->equipmentDetails[$equipmentNum]['details']<>'n/a'){
                $this->equipmentPopupsJS[] = 
                "$( \"#equipmentDialog{$equipmentNum}\" ).dialog({
                    autoOpen: false,
                    modal: true,
                    minWidth: 525
                });

                $( \"#equipment{$equipmentNum}\" ).click(function() {
                    $( \"#equipmentDialog{$equipmentNum}\" ).dialog( \"open\" );
                    return false;
                });
                $( \"#equipmentLabel{$equipmentNum}\" ).click(function() {
                    $( \"#equipmentDialog{$equipmentNum}\" ).dialog( \"open\" );
                    return false;
                });
                $( \"#equipmentWarning{$equipmentNum}\" ).click(function() {
                    $( \"#equipmentDialog{$equipmentNum}\" ).dialog( \"open\" );
                    return false;
                });";
                $this->equipmentPopups[] = 
                "<div id=\"equipmentDialog{$equipmentNum}\" title=\"{$this->translator->_($this->equipmentDetails[$equipmentNum]['title'])}\" style='font-size: .875em;'>
                {$this->equipmentDetails[$equipmentNum]['details']}
                </div>";
            }
        }
    }
    
    private function loadSteamPoints(){
        $this->steamPoints = array();
        $this->steamPointsDetails = array();
    }
    
    private function addSteamPoints($name, $x, $y, $orientation, $steamDto, $switchSides = false){
        $offset = 12;
        if ($switchSides) $offset=-12;
        if ($orientation == 'Up' or $orientation == 'Down'){
            $width = 14;
            $height = 8;
            $widthA = 12;
            $heightA = 24;
            $topA = $y-$heightA/2;
            $leftA = $x-$widthA/2-$offset;
        }else{
            $width = 8;
            $height = 14;
            $widthA = 24;
            $heightA = 12;
            $topA = $y-$heightA/2+$offset;
            $leftA = $x-$widthA/2;
        }
        $top = $y-$height/2-1;
        $left = $x-$width/2-1;
        
        $backgroundColor = 'red';
        $opacity = .5;
        $extra = '';
        if ($steamDto instanceof Steam_Object){
            if ($steamDto->massFlow>=0.01){
                $backgroundColor = 'white';
            }else{
                $backgroundColor = 'silver';
                if ($width==14){
                    $extra = "background-image: url(images/XClosed.gif);";
                }else{
                    $extra = "background-image: url(images/XClosedUp.gif);";
                }
                $opacity = 1;
            }
        }
        $this->steamPoints[] = "<div id='steamPoint".(count($this->steamPoints))."' style=\"position: absolute; left: {$left}px; top: {$top}px;  height: {$height}px; width: {$width}px; border: 1px black solid;
                 background-color: {$backgroundColor}; opacity: {$opacity};
                 {$extra}
                 \">
             
        </div>

         <img src='images/arrows/Arrow{$orientation}.gif' style='position: absolute; left: ".$leftA."px; top: ".$topA."px;  opacity: ".(1.5-$opacity).";'>        
     
        ";
        $this->steamPointDetails[] = array(
            'Name' => $name,
            'SteamDto' => $steamDto,
            'Opacity' => $opacity,
        );
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
                $(\"#selectedTitle\").html('{$this->translator->_($details['Name'])}');                
                ";
                if ($details['SteamDto'] instanceof Steam_Object and $details['SteamDto']->massFlow>=0.01){
                    $temperature = $this->mS->displayTemperatureLabeled($details['SteamDto']->temperature);
                    $pressure = $this->mS->displayPressureLabeled($details['SteamDto']->pressure);
                    $massflow = $this->mS->displayMassflowLabeled($details['SteamDto']->massFlow);
                    $specificEnthalpy = $this->mS->displaySpecificEnthalpyLabeled($details['SteamDto']->specificEnthalpy);
                    $specificEntropy = $this->mS->displaySpecificEntropyLabeled($details['SteamDto']->specificEntropy);  
                    $phase = $this->translator->_($details['SteamDto']->phase);
                    if ($details['SteamDto']->phase=='Saturated') $phase .= ' / '.$this->translator->_('Quality').': '.number_format($details['SteamDto']->quality,2);
                }else{
                    $temperature = $this->translator->_('n/a');
                    $pressure = $this->translator->_('n/a');
                    $massflow = '<span style="color: red; font-weight: bold;">'.$this->translator->_('No Flow').'</span>';
                    $specificEnthalpy = $this->translator->_('n/a');
                    $specificEntropy = $this->translator->_('n/a');                                   
                    $phase = $this->translator->_('n/a');
                }
                                $this->hightlightedSteamPoints[$steamPointNum] .= 
                "$(\"#selectedTemp\").html('".$temperature."');
                $(\"#selectedPressure\").html('".$pressure."');
                $(\"#selectedMassflow\").html('".$massflow."');
                $(\"#selectedEnthalpy\").html('".$specificEnthalpy."');
                $(\"#selectedEntropy\").html('".$specificEntropy."');
                $(\"#selectedPhase\").html('".$phase."');";
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
        foreach($this->pipes as $component => $pieces) echo $this->highlight[$component];
        foreach($this->counterEquipment as $equipmentNum => $equipment) echo $this->highlightedEquipment[$equipmentNum];
        foreach($this->steamPointDetails as $steamPointNum => $details) echo $this->hightlightedSteamPoints[$steamPointNum];
        
        echo implode('', $this->equipmentPopupsJS);
        echo "
            });
            </script>";
    }
    
    private function loadPipes(){
        $pipes = array();        
        
        $this->counter['Feedwater'] = array();
        $this->counter['HpHeader'] = array();
        $this->counter['MpHeader'] = array();
        
        $this->counter['LpHeader'] = array();
        $this->counter['Cond'] = array();
        $this->counter['Vents'] = array();
        $this->counter['Return'] = array();
        $this->counter['Blowdown'] = array();
        
        $pipes[$component = 'Feedwater'] = array(); 
        $pipes[$component = 'HpHeader'] = array();        
        $pipes[$component = 'MpHeader'] = array();  
        
        $lpColor = 'Yellow';
        $lpLine = 425;
        $pipes[$component = 'LpHeader'] = array(
        );        
                
        $color = 'Blue';
        $line = 725;
        $pipes[$component = 'Cond'] = array(
        );
        $color = 'Brown';
        
        $pipes[$component = 'Blowdown'] = array(
        );
        
        $color = 'Brown';        
        $pipes[$component = 'Vents'] = array(
        );
        
        $returnColor = 'Green';        
        $pipes[$component = 'Return'] = array(
   
        );
         
        $this->pipes = $pipes;
    }
    
    private function highlightPipes(){
        $this->hightlight = array();        

        foreach($this->pipes as $component => $pieces){
            if (isset($this->componentDetails[$component])){
            $this->highlight[$component] = "

            $('img[id^=\"pipe{$component}\"]').mouseover(function(){ highlightPipe{$component}(); })
            $('img[id^=\"pipe{$component}\"]').mouseout(function(){ UNhighlightPipe{$component}(); })
            $('#pipeWarning{$component}').mouseover(function(){ highlightPipe{$component}(); })
            $('#pipeWarning{$component}').mouseout(function(){ UNhighlightPipe{$component}(); })

            function highlightPipe{$component}(){
                        $(\"#diagramLegend\").hide();
                $(\"#selectedComponent\").show();
                $(\"#selectedCompTitle\").html('".$this->componentDetails[$component]['title']."');
                $(\"#selectedCompText\").html('".$this->componentDetails[$component]['text']."');
            ";
            }else{
                
            $this->highlight[$component] = "

            $('img[id^=\"pipe{$component}\"]').mouseover(function(){ highlightPipe{$component}(); })
            $('img[id^=\"pipe{$component}\"]').mouseout(function(){ UNhighlightPipe{$component}(); })
            $('#pipeWarning{$component}').mouseover(function(){ highlightPipe{$component}(); })
            $('#pipeWarning{$component}').mouseout(function(){ UNhighlightPipe{$component}(); })

            function highlightPipe{$component}(){
                $(\"#diagramLegend\").hide();
                $(\"#selectedComponent\").show();
                $(\"#selectedCompTitle\").html('".$component."');
                $(\"#selectedCompText\").html('".$component."');
            ";
            }
                foreach($this->counter[$component] as $num => $image){
                    $this->highlight[$component] .= "$(\"#pipe{$component}{$num}\").attr(\"src\", \"{$image}HL.gif\");
                        "; }
            $this->highlight[$component] .= "}

            function UNhighlightPipe{$component}(){
                $(\"#diagramLegend\").show();
                $(\"#selectedComponent\").hide();
            ";
                foreach($this->counter[$component] as $num => $image){
                    $this->highlight[$component] .= "$(\"#pipe{$component}{$num}\").attr(\"src\", \"{$image}.gif\");
                        "; }
            $this->highlight[$component] .= "}
            ";
        }
    }
    
    private function displayPipes(){
        $pipeOrder = array(
            'Return',
            'Blowdown',
            'Feedwater',
            'LpHeader',
            'MpHeader',
            'HpHeader',
            'Cond',
            'Vents',
        );
        foreach ($pipeOrder as $component) echo implode("", $this->pipes[$component]);        
    }
    
    private function loadEquipment(){    
        $this->allEquipment = array();    
    }
    
    private function highlightEquipment(){
        $this->highlightedEquipment = array();
        foreach($this->counterEquipment as $equipmentNum => $equipment){
            $this->highlightedEquipment[$equipmentNum] = "

            $('#equipment{$equipmentNum}').mouseover(function(){ highlightEquipment{$equipmentNum}(); })
            $('#equipment{$equipmentNum}').mouseout(function(){ UNhighlightEquipment{$equipmentNum}(); })
            
            $('#equipmentLabel{$equipmentNum}').mouseover(function(){ highlightEquipment{$equipmentNum}(); })
            $('#equipmentLabel{$equipmentNum}').mouseout(function(){ UNhighlightEquipment{$equipmentNum}(); })
            
            $('#equipmentWarning{$equipmentNum}').mouseover(function(){ highlightEquipment{$equipmentNum}(); })
            $('#equipmentWarning{$equipmentNum}').mouseout(function(){ UNhighlightEquipment{$equipmentNum}(); })
            
            function highlightEquipment{$equipmentNum}(){";
            
            if (isset($this->equipmentCode[$equipmentNum]) and isset($this->componentDetails[$this->equipmentCode[$equipmentNum]]) ){                                
                $this->highlightedEquipment[$equipmentNum] .= "                    
                        $(\"#diagramLegend\").hide();
                        $(\"#selectedComponent\").show();
                        $(\"#selectedCompTitle\").html('".$this->translator->_($this->componentDetails[$this->equipmentCode[$equipmentNum]]['title'])."');
                        $(\"#selectedCompText\").html('".$this->componentDetails[$this->equipmentCode[$equipmentNum]]['text']."');
                            ";
            }else{
                $this->highlightedEquipment[$equipmentNum] .= "
                        $(\"#diagramLegend\").hide();
                        $(\"#selectedComponent\").show();
                        $(\"#selectedCompTitle\").html('".$equipmentNum."');
                        $(\"#selectedCompText\").html('');
                            ";
            }
            $this->highlightedEquipment[$equipmentNum] .= "
                $(\"#equipment{$equipmentNum}\").attr(\"src\", \"images/equipment/{$equipment}HL.gif\");
            }
            function UNhighlightEquipment{$equipmentNum}(){
                $(\"#diagramLegend\").show();
                $(\"#selectedComponent\").hide();
                $(\"#equipment{$equipmentNum}\").attr(\"src\", \"images/equipment/{$equipment}.gif\");
            }
                ";
            
        }        
    }
    
    private function displayEquipment(){        
        echo implode("", $this->allEquipment);
    }
        
    private function placeEquipment($name, $top, $left, $width = 100, $height = 100, $title = 'n/a', $details = 'n/a'){        
        $this->counterEquipment[] = $name;
  
        $this->equipmentDetails[ count($this->counterEquipment)-1 ] = array (
            'title' => $title,
            'details' => $details,
        );

        return '<img id="equipment'.(count($this->counterEquipment)-1).'" src="images/equipment/'.$name.'.gif" style="position: absolute; top:'.$top.'px; left: '.$left.'px; width:'.$width.'px; height:'.$height.'px;">';
    }
    
    private function connect($x1,$y1,$x2,$y2,$color = 'Blue',$component = 'Misc'){
        $colors = array(
            'Feedwater' => 'Purple',
        );
        if (isset($colors[$component])) $color = $colors[$component];
        if ($x1 == $x2){
            $image = "images/pipes/Pipe{$color}Vert";
            $height = abs( $y1-$y2 );
            $width = 16;
            $top = $y1;
            $left = $x1-8;
        }else{
            $image = "images/pipes/Pipe{$color}Horiz";
            $width = abs( $x1-$x2 );
            $height = 16;
            $top = $y1-8;
            $left = $x1;
        }
        $this->counter[$component][] = $image;
        return '<img id="pipe'.$component.(count($this->counter[$component])-1).'" src="'.$image.'.gif" style="position: absolute; top: '.$top.'px; left: '.$left.'px; width: '.$width.'px; height: '.$height.'px;">
            ';
    }
    
    
    public function drawPipe($left1,$top1,$left2,$top2,$direction, $color = 'Blue',$component = 'Misc'){                
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
            $image = "images/pipes/Pipe{$color}Horiz";
            $this->counter[$component][] = $image;
            $this->pipes[$component][] = "<img id=\"pipe".$component.(count($this->counter[$component])-1)."\" src=\"{$image}.gif\" style=\"position: absolute; top: ".($top-$diameter/2)."px; left: {$left}px; width: {$width}px; height: {$diameter}px;\">";
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
            $image = "images/pipes/Pipe{$color}Vert";
            $this->counter[$component][] = $image;
            $this->pipes[$component][] = "<img id=\"pipe".$component.(count($this->counter[$component])-1)."\" src=\"{$image}.gif\" style=\"position: absolute; top: {$top}px; left: ".($left-$diameter/2)."px; width: {$diameter}px; height: {$height}px;\">";
        }
        if ($top1<>$top2 and $left1<>$left2){
            $image = "images/pipes/Pipe{$color}{$angle}";
            $this->counter[$component][] = $image;
            $this->pipes[$component][] = "<img id=\"pipe".$component.(count($this->counter[$component])-1)."\" src=\"{$image}.gif\" style=\"position: absolute; top: ".($angleTop-8)."px; left: ".($angleLeft-8)."px;\">";
        }
            
        return $pipe;    
    }
    
    private function turn($x1,$y1,$turnType,$color = 'Blue',$component = 'Misc'){

        $image = 'images/pipes/Pipe'.$color.$turnType;
        $top = $y1-8;
        $left = $x1-8;
        
        $this->counter[$component][] = $image;
        return '<img id="pipe'.$component.(count($this->counter[$component])-1).'" src="'.$image.'.gif" style="position: absolute; top: '.$top.'px; left:'.$left.'px;">
            ';
    }
    
    
      private  function addData($top, $left, $data, $textAlign = 'left', $color='black'){
            if ($textAlign=='right') $left = 800-$left;
            $html = "<div style=\"position: absolute; top: {$top}px; {$textAlign}: {$left}px;  background-color: white; color: {$color};
             opacity:1; filter:alpha(opacity=80);\">{$data}</div>";
             return $html;
    }
        
    public function loadComponentDetails(){
        $this->componentDetails = array();
        
                $textHpHeader = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->highPressure)."</td>"
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpHeader->initialHeaderSteam->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Energy Loss %')."</th><td>".number_format($this->steamModel->hpHeader->heatLoss->energyLossPercent*100,2)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Loss')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->hpHeader->heatLoss->energyFlowLoss)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->hpHeader->finalHeaderSteam->temperature)."</td>";
                if ( $this->steamModel->hpHeader->finalHeaderSteam->phase=='Saturated' ){
                    $textHpHeader .= "<th style=\"color: red; font-weight: bold;\">".$this->translator->_('Saturated')."</th><td>".$this->mS->displayQualityLabeled($this->steamModel->hpHeader->finalHeaderSteam->quality)."</td>";
                }else{
                    if ($this->steamModel->hpHeader->finalHeaderSteam->phase=='Liquid'){
                        $textHpHeader .= "<th>".$this->translator->_('Phase')."</th><td style=\"color: red; font-weight: bold;\">".$this->translator->_($this->steamModel->hpHeader->finalHeaderSteam->phase)."</td>";
                    }else{
                        $textHpHeader .= "<th>".$this->translator->_('Phase')."</th><td>".$this->translator->_($this->steamModel->hpHeader->finalHeaderSteam->phase)."</td>";
                    }
                }
                $textHpHeader .= "</tr></table>";
                
        $textMpHeader = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->mediumPressure)."</td>"
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->mpHeader->initialHeaderSteam->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Energy Loss %')."</th><td>".number_format($this->steamModel->mpHeader->heatLoss->energyLossPercent*100,2)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Loss')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->mpHeader->heatLoss->energyFlowLoss)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->mpHeader->finalHeaderSteam->temperature)."</td>";
                if ( $this->steamModel->mpHeader->finalHeaderSteam->phase=='Saturated' ){
                    $textMpHeader .= "<th style=\"color: red; font-weight: bold;\">".$this->translator->_('Saturated')."</th><td>".$this->mS->displayQualityLabeled($this->steamModel->mpHeader->finalHeaderSteam->quality)."</td>";
                }else{
                    if ($this->steamModel->mpHeader->finalHeaderSteam->phase=='Liquid'){
                        $textMpHeader .= "<th>".$this->translator->_('Phase')."</th><td style=\"color: red; font-weight: bold;\">".$this->steamModel->mpHeader->finalHeaderSteam->phase."</td>";
                    }else{
                        $textMpHeader .= "<th>".$this->translator->_('Phase')."</th><td>".$this->steamModel->mpHeader->finalHeaderSteam->phase."</td>";
                    }
                }
                $textMpHeader .= "</tr></table>";
                
        $textLpHeader = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->lowPressure)."</td>"
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->lpHeader->initialHeaderSteam->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Energy Loss %')."</th><td>".number_format($this->steamModel->lpHeader->heatLoss->energyLossPercent*100,2)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Loss')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->lpHeader->heatLoss->energyFlowLoss)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->lpHeader->finalHeaderSteam->temperature)."</td>";
                if ( $this->steamModel->lpHeader->finalHeaderSteam->phase=='Saturated' ){
                    $textLpHeader .= "<th style=\"color: red; font-weight: bold;\">".$this->translator->_('Saturated')."</th><td>".$this->mS->displayQualityLabeled($this->steamModel->lpHeader->finalHeaderSteam->quality)."</td>";
                }else{
                    if ($this->steamModel->lpHeader->finalHeaderSteam->phase=='Liquid'){
                        $textLpHeader .= "<th>".$this->translator->_('Phase')."</th><td style=\"color: red; font-weight: bold;\">".$this->steamModel->lpHeader->finalHeaderSteam->phase."</td>";
                    }else{
                        $textLpHeader .= "<th>".$this->translator->_('Phase')."</th><td>".$this->steamModel->lpHeader->finalHeaderSteam->phase."</td>";
                    }
                }
                $textLpHeader .= "</tr></table>";
                
                             
        $textFeedwater = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->deaerator->feedwater->pressure)."</td>"
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->deaerator->feedwater->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Sp. Enthalpy')."</th><td>".$this->mS->displaySpecificEnthalpyLabeled($this->steamModel->deaerator->feedwater->specificEnthalpy)."</td>"
                        ."<th>".$this->translator->_('Sp. Entropy')."</th><td>".$this->mS->displaySpecificEntropyLabeled($this->steamModel->deaerator->feedwater->specificEntropy)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->deaerator->feedwater->temperature)."</td>";
                if ( $this->steamModel->deaerator->feedwater->phase=='Saturated' ){
                    $textFeedwater .= "<th>".$this->translator->_('Saturated')."</th><td>".$this->mS->displayQualityLabeled($this->steamModel->deaerator->feedwater->quality)."</td>";
                }else{
                    $textFeedwater .= "<th>".$this->translator->_('Phase')."</th><td>".$this->steamModel->deaerator->feedwater->phase."</td>";
                }
                $textFeedwater .= "</tr></table>";
        
        $textTurbineCond = "<table><tr>"
                        ."<th>".$this->translator->_('Isentropic Eff')."</th><td>".number_format($this->steamModel->turbineCondModel->isentropicEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Out')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->turbineCondModel->energyOut)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Generation Eff')."</th><td>".number_format($this->steamModel->turbineCondModel->generatorEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Power Out')."</th><td>".$this->mS->displayPowerLabeled($this->steamModel->turbineCondModel->powerOut)."</td>"
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Turbine for Details')."</span> ";
        if ( count($this->steamModel->turbineCondModel->warnings)>0 ) $textTurbineCond .= "<span style=\"color: red; font-weight: bold;\">[".$this->translator->_('Warnings')."]</span>";
        $textTurbineCond .= "</td></tr></table>";
        
        $textTurbineHpLp = "<table><tr>"
                        ."<th>".$this->translator->_('Isentropic Eff')."</th><td>".number_format($this->steamModel->turbineHpLpModel->isentropicEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Out')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->turbineHpLpModel->energyOut)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Generation Eff')."</th><td>".number_format($this->steamModel->turbineHpLpModel->generatorEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Power Out')."</th><td>".$this->mS->displayPowerLabeled($this->steamModel->turbineHpLpModel->powerOut)."</td>"
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Turbine for Details')."</span> ";
        if ( count($this->steamModel->turbineHpLpModel->warnings)>0 ) $textTurbineHpLp .= "<span style=\"color: red; font-weight: bold;\">[".$this->translator->_('Warnings')."]</span>";
        $textTurbineHpLp .= "</td></tr></table>";
        
        $textTurbineHpMp = "<table><tr>"
                        ."<th>".$this->translator->_('Isentropic Eff')."</th><td>".number_format($this->steamModel->turbineHpMpModel->isentropicEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Out')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->turbineHpMpModel->energyOut)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Generation Eff')."</th><td>".number_format($this->steamModel->turbineHpMpModel->generatorEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Power Out')."</th><td>".$this->mS->displayPowerLabeled($this->steamModel->turbineHpMpModel->powerOut)."</td>"
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Turbine for Details')."</span> ";
        if ( count($this->steamModel->turbineHpMpModel->warnings)>0 ) $textTurbineHpMp .= "<span style=\"color: red; font-weight: bold;\">[".$this->translator->_('Warnings')."]</span>";
        $textTurbineHpMp .= "</td></tr></table>";
        
        $textTurbineMpLp = "<table><tr>"
                        ."<th>".$this->translator->_('Isentropic Eff')."</th><td>".number_format($this->steamModel->turbineMpLpModel->isentropicEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Energy Out')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->turbineMpLpModel->energyOut)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Generation Eff')."</th><td>".number_format($this->steamModel->turbineMpLpModel->generatorEff*100,1)." ".$this->mS->label('%')."</td>"
                        ."<th>".$this->translator->_('Power Out')."</th><td>".$this->mS->displayPowerLabeled($this->steamModel->turbineMpLpModel->powerOut)."</td>"
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Turbine for Details')."</span> ";
        if ( count($this->steamModel->turbineMpLpModel->warnings)>0 ) $textTurbineMpLp .= "<span style=\"color: red; font-weight: bold;\">[".$this->translator->_('Warnings')."]</span>";
        $textTurbineMpLp .= "</td></tr></table>";
        
        
        $textHpProcess = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->highPressure)."</td>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->hpProcessSteam->temperature)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->hpProcessSteam->energyFlow)."</td>"  
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpSteamUsage)."</td>"
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Process Usage')."*</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->energyUsageHP)."</td>"
                        ."<td colspan=\"2\" style=\"font-style: italic;\">*".$this->translator->_('Only Includes Latent Heat')."</td>"
                    ."</tr></table>";
        $textMpProcess = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->mediumPressure)."</td>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->mpProcessSteam->temperature)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->mpProcessSteam->energyFlow)."</td>"  
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->mpSteamUsage)."</td>"
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Process Usage')."*</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->energyUsageMP)."</td>"
                        ."<td colspan=\"2\" style=\"font-style: italic;\">*".$this->translator->_('Only Includes Latent Heat')."</td>"
                    ."</tr></table>";
        $textLpProcess = "<table><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->lowPressure)."</td>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->lpProcessSteam->temperature)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->lpProcessSteam->energyFlow)."</td>"  
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->lpSteamUsage)."</td>"
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Process Usage')."*</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->energyUsageLP)."</td>"
                        ."<td colspan=\"2\" style=\"font-style: italic;\">*".$this->translator->_('Only Includes Latent Heat')."</td>"
                    ."</tr></table>";
                
        
        $textPrvHpMp = "<table><tr>" 
                        ."<th>".$this->translator->_('Outlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpTmpPRV->inletSteam->massFlow)."</td>"
                        ."<th>".$this->translator->_('Inlet')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->highPressure)."</td>"
                        
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Outlet Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->hpTmpPRV->outletSteam->temperature)."</td>"
                        ."<th>".$this->translator->_('Outlet')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->mediumPressure)."</td>"
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on PRV for Details')."</span> "
                    ."</tr></table>";
        $textPrvMpLp = "<table><tr>" 
                        ."<th>".$this->translator->_('Outlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->mpTlpPRV->inletSteam->massFlow)."</td>"
                        ."<th>".$this->translator->_('Inlet')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->mediumPressure)."</td>"
                        
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Outlet Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->mpTlpPRV->outletSteam->temperature)."</td>"
                        ."<th>".$this->translator->_('Outlet')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->lowPressure)."</td>"
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on PRV for Details')."</span> "
                    ."</tr></table>";
        
        
        $textBoiler = "<table><tr>"         
                        ."<th>".$this->translator->_('Fuel Energy')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->boiler->fuelEnergy)."</td>"         
                        ."<th>".$this->translator->_('Combustion Eff')."</th><td>".number_format($this->steamModel->boiler->boilerEff*100,1)." ".$this->mS->label('%')."</td>"  
                       
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Steam Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->boilerTemp)."</td>"  
                        ."<th>".$this->translator->_('Blowdown Rate')."</th><td>".number_format($this->steamModel->boiler->blowdownRate*100,1)." ".$this->mS->label('%')."</td>"                 
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Boiler for Details')."</span> "
                    ."</tr></table>";
        
        if (isset($this->steamModel->hpCondFlashTank) ){
            $textFlashHP = "<table><tr>"       
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->lowPressure)."</td>"  
                        ."<th>".$this->translator->_('Inlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpCondFlashTank->inletSteam->massFlow)."</td>"                                 
                       
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Gas Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpCondFlashTank->satGas->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Liquid Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpCondFlashTank->satLiquid->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Flash Tank for Details')."</span> "
                    ."</tr></table>";
                       // ."<th>Condensate Recovery</th><td>".number_format($this->steamModel->mpCondReturnRate,1)." ".$this->mS->label('%')."</td>"
        } else { $textFlashHP = "<table><tr><td>".$this->translator->_('Flash Tank Not Operational')."</td></tr></table>"; }
        if (isset($this->steamModel->hpCondFlashTank) ){
            $textFlashHP = "<table><tr>"       
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->mediumPressure)."</td>"  
                        ."<th>".$this->translator->_('Inlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpCondFlashTank->inletSteam->massFlow)."</td>"                                 
                       
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Gas Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpCondFlashTank->satGas->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Liquid Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->hpCondFlashTank->satLiquid->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Flash Tank for Details')."</span> "
                    ."</tr></table>";
                       // ."<th>Condensate Recovery</th><td>".number_format($this->steamModel->mpCondReturnRate,1)." ".$this->mS->label('%')."</td>"
        } else { $textFlashHP = "<table><tr><td>".$this->translator->_('Flash Tank Not Operational')."</td></tr></table>"; }
        if (isset($this->steamModel->mpCondFlashTank) ){
            $textFlashMP = "<table><tr>"       
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->lowPressure)."</td>"  
                        ."<th>".$this->translator->_('Inlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->mpCondFlashTank->inletSteam->massFlow)."</td>"                                 
                       
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Gas Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->mpCondFlashTank->satGas->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Liquid Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->mpCondFlashTank->satLiquid->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Flash Tank for Details')."</span> "
                    ."</tr></table>";
                       // ."<th>".$this->translator->_('Condensate Recovery</th><td>".number_format($this->steamModel->mpCondReturnRate,1)." ".$this->mS->label('%')."</td>"
        } else { $textFlashMP = "<table><tr><td>".$this->translator->_('Flash Tank Not Operational')."</td></tr></table>"; }
        
        if (isset($this->steamModel->blowdownFlashTank) ){
            $textBlowdownFlashLP = "<table><tr>"       
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->lowPressure)."</td>"  
                        ."<th>".$this->translator->_('Inlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->blowdownFlashTank->inletSteam->massFlow)."</td>"                                 
                       
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Gas Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->blowdownFlashTank->satGas->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Liquid Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->blowdownFlashTank->satLiquid->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Flash Tank for Details')."</span> "
                    ."</tr></table>";
        } else { $textBlowdownFlashLP = "<table><tr><td>".$this->translator->_('Flash Tank Not Operational')."</td></tr></table>"; }
        
        if (isset($this->steamModel->condReturnFlashTank) ){
            $textCondFlash = "<table><tr>"       
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->daPressure)."</td>"  
                        ."<th>".$this->translator->_('Inlet Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->condReturnFlashTank->inletSteam->massFlow)."</td>"                                 
                       
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Gas Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->condReturnFlashTank->satGas->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Liquid Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->condReturnFlashTank->satLiquid->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Flash Tank for Details')."</span> "
                    ."</tr></table>";
        } else { $textCondFlash = "<table><tr><td>".$this->translator->_('Flash Tank Not Operational')."</td></tr></table>"; }
        
        $textDeaerator = "<table><tr>"       
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->daPressure)."</td>"  
                        ."<th>".$this->translator->_('Vent Rate')."</th><td>".number_format($this->steamModel->daVentRate,1)." ".$this->mS->label('%')."</td>"                                                    
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Feedwater Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->deaerator->feedwater->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Vent Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->deaerator->daVentSteam->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Deaerator for Details')."</span> "
                    ."</tr></table>";
        
        $textBlowdown = "<table><tr>"  
                        ."<th>".$this->translator->_('Blowdown Rate')."</th><td>".number_format($this->steamModel->boiler->blowdownRate*100,1)." ".$this->mS->label('%')."</td>"   
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->boiler->blowdown->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->boiler->blowdown->temperature)."</td>"
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->boiler->blowdown->energyFlow)."</td>"  
                        
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Flash Blowdown')."</th><td>".$this->steamModel->blowdownFlashLP."</td>"
                    ."</tr></table>";
        
        $textMakeUpWater = "<table><tr>"  
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->makeupWaterTemp)."</td>"  
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->makeupWater->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->makeupWater->pressure)."</td>"   
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->makeupWater->energyFlow)."</td>"                          
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Volume Flow')."</th><td>".$this->mS->displayVolumeflowLabeled($this->steamModel->makeupWater->volumeFlow/60)."</td>"                        
                    ."</tr></table>";
        
        
        $textMakeCond = "<table><tr>"  
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->deaerator->daWaterFeed->temperature)."</td>"  
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->deaerator->daWaterFeed->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->deaerator->daWaterFeed->pressure)."</td>"   
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->deaerator->daWaterFeed->energyFlow)."</td>"                          
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Volume Flow')."</th><td>".$this->mS->displayVolumeflowLabeled($this->steamModel->deaerator->daWaterFeed->volumeFlow/60)."</td>"                        
                    ."</tr></table>";
        $textCond = "<table><tr>"  
                        ."<th>".$this->translator->_('Temperature')."</th><td>".$this->mS->displayTemperatureLabeled($this->steamModel->condensate->temperature)."</td>"  
                        ."<th>".$this->translator->_('Mass Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->condensate->massFlow)."</td>"
                    ."</tr><tr>"
                        ."<th>".$this->translator->_('Pressure')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->condensate->pressure)."</td>"   
                        ."<th>".$this->translator->_('Energy Flow')."</th><td>".$this->mS->displayEnergyflowLabeled($this->steamModel->condensate->energyFlow)."</td>"                          
                    ."</tr><tr>"                      
                        ."<th>".$this->translator->_('Volume Flow')."</th><td>".$this->mS->displayVolumeflowLabeled($this->steamModel->condensate->volumeFlow/60)."</td>"                        
                    ."</tr></table>";
        
        
        $textBlowdownHeatX = "<table><tr>"       
                        ."<th>".$this->translator->_('Approach Temperature')."</th><td>".$this->mS->displayPressureLabeled($this->steamModel->daPressure)."</td>"  
                        ."<th>".$this->translator->_('Vent Rate')."</th><td>".number_format($this->steamModel->daVentRate,1)." ".$this->mS->label('%')."</td>"                                                    
                    ."</tr><tr>"           
                        ."<th>".$this->translator->_('Feedwater Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->deaerator->feedwater->massFlow)."</td>"         
                        ."<th>".$this->translator->_('Vent Flow')."</th><td>".$this->mS->displayMassflowLabeled($this->steamModel->deaerator->daVentSteam->massFlow)."</td>"         
                    ."</tr><tr><td colspan=\"4\" ><span style=\"font-style: italic; font-weight: bold;\">".$this->translator->_('Click on Deaerator for Details')."</span> "
                    ."</tr></table>";
        
        $this->componentDetails = array(
            
            'boiler' => array(
                'title' => $this->translator->_('Boiler'),
                'text' => $textBoiler,  
                ),
            
            'deaerator' => array(
                'title' => $this->translator->_('Deaerator'),
                'text' => $textDeaerator,  
                ),
            
            'BlowdownHeatX' => array(
                'title' => $this->translator->_('Blowdown Heat Exchanger'),
                'text' => $textBlowdownHeatX,  
                ),           
            
            'MakeUpWater' => array(
                'title' => $this->translator->_('Make-Up Water'),
                'text' => $textMakeUpWater,  
                ),
            
            
            'flashHpMp' => array(
                'title' => $this->translator->_('HP Condensate Flash Tank'),
                'text' => $textFlashHP,  
                ),
            'flashMpLp' => array(
                'title' => $this->translator->_('MP Condensate Flash Tank'),
                'text' => $textFlashMP,  
                ),
            'flashHpLp' => array(
                'title' => $this->translator->_('HP Condensate Flash Tank'),
                'text' => $textFlashMP,  
                ),
            'FlashTk' => array(
                'title' => $this->translator->_('Blowdown Flash Tank'),
                'text' => $textBlowdownFlashLP,  
                ),
            'CondFlashTk' => array(
                'title' => $this->translator->_('Condensate Flash Tank'),
                'text' => $textCondFlash,  
                ),
            
            'HpHeader' => array(
                'title' => $this->translator->_('HP Header Details'),
                'text' => $textHpHeader,                
                ),            
            'MpHeader' => array(
                'title' => $this->translator->_('MP Header Details'),
                'text' => $textMpHeader,  
                ),            
            'LpHeader' => array(
                'title' => $this->translator->_('LP Header Details'),
                'text' => $textLpHeader,  
                ),
            'Feedwater' => array(
                'title' => $this->translator->_('Feedwater Details'),
                'text' => $textFeedwater,  
                ),
            'Return' => array(
                'title' => $this->translator->_('Make-Up Water & Condensate Details'),
                'text' => $textMakeCond,  
                ),
            'Cond' => array(
                'title' => $this->translator->_('Combined Returned Condensate Details'),
                'text' => $textCond,  
                ),
            'Vents' => array(
                'title' => $this->translator->_('Vented Steam Details'),
                'text' => $textFeedwater,  
                ),
            'Blowdown' => array(
                'title' => $this->translator->_('Boiler Blowdown'),
                'text' => $textBlowdown,  
                ),
            
            'turbineCond' => array(
                'title' => $this->translator->_('Condensing Turbine'),
                'text' => $textTurbineCond,  
                ),
            'turbineHpLp' => array(
                'title' => $this->translator->_('HP to LP Turbine'),
                'text' => $textTurbineHpLp,  
                ),
            'turbineHpMp' => array(
                'title' => $this->translator->_('HP to MP Turbine'),
                'text' => $textTurbineHpMp,  
                ),
            'turbineMpLp' => array(
                'title' => $this->translator->_('MP to LP Turbine'),
                'text' => $textTurbineMpLp,  
                ),
            
            'hpProcess' => array(
                'title' => $this->translator->_('HP Process Steam Usage'),
                'text' => $textHpProcess,  
                ),
            'mpProcess' => array(
                'title' => $this->translator->_('MP Process Steam Usage'),
                'text' => $textMpProcess,  
                ),
            'lpProcess' => array(
                'title' => $this->translator->_('LP Process Steam Usage'),
                'text' => $textLpProcess,  
                ),
            
            
            'prvHpMp' => array(
                'title' => $this->translator->_('HP to MP Pressure Reducing Valve'),
                'text' => $textPrvHpMp,  
                ),            
            'prvMpLp' => array(
                'title' => $this->translator->_('MP to LP Pressure Reducing Valve'),
                'text' => $textPrvMpLp,  
                ),
            'prvHpLp' => array(
                'title' => $this->translator->_('HP to LP Pressure Reducing Valve'),
                'text' => $textPrvMpLp,  
                ),
        );
    }    

}  