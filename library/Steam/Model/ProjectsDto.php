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
 * Project Data Transfer Object
 * Using the base model as the preset values, the ProjectsDTO overwrites any data associated with a selected project creating a set of adjusted model data
 * @package    Steam
 * @subpackage Steam_Model
 */

class Steam_Model_ProjectsDto extends Steam_DTO{

    /**
     * Steam MeasurementSystem Object
     * @var Steam_MeasurementSystem
     */
    var $mS;
    /**
     * ProjectsDto Object
     * @var Steam_Model_ProjectsDto
     */
    var $projectFormData;
      
    /**
     * Generated Base Model
     * @var Steam_Model_Constructor
     */
    var $baseModel; 
    
    public function __construct($baseModelDTO, $baseModel, $projectFormData) {
        $this->mS = Steam_MeasurementSystem::getInstance();
        parent::__construct('baseModel', $baseModelDTO->unload() );
        $this->projectFormData = $projectFormData->unload();
        
        $checkBoxes = array();
        foreach(Steam_Model_Projects::listed($baseModel->headerCount) as $key => $values){           
            if (isset($this->projectFormData[$key])){
                $checkBoxes[$key] = $this->projectFormData[$key];
            }else{
                $checkBoxes[$key] = false;
                $this->projectFormData[$key] = false;
            }
            foreach($values[1] as $projectKey => $value)    
                if (isset($this->projectFormData[$projectKey])) {
                    $checkBoxes[$projectKey] = $this->projectFormData[$projectKey];
                }else{
                    $checkBoxes[$projectKey] = false;
                    $this->projectFormData[$projectKey] = false;
                }
        }     
        $this->load ('baseModel',  $checkBoxes );
        
        $this->baseModel = $baseModel;
        
        if ($this->projectFormData['Proj_operatingHours']) $this->load ('baseModel', $this->projOperatingHours() );
        if ($this->projectFormData['Proj_makeupTemp']) $this->load ('baseModel', $this->projMakeupTemp() );
        if ($this->projectFormData['Proj_electricityUC']) $this->load ('baseModel', $this->projElectrictyUC() );
        if ($this->projectFormData['Proj_fuelUC']) $this->load ('baseModel', $this->projFuelUC() );
        if ($this->projectFormData['Proj_makeupwaterUC']) $this->load ('baseModel', $this->projMakeupwaterUC() );

        if ($this->projectFormData['Proj_steamDemand'] or $this->projectFormData['Proj_energyDemand']  ){
            if ( $this->projectFormData['Proj_steamDemand'] ) $this->load ('baseModel',  $this->projSteamDemand() );
            if ( $this->projectFormData['Proj_energyDemand'] ) $this->load ('baseModel',  $this->projEnergyDemand() );
        }else{
            $this->load ('baseModel',  $this->fixedEnergyUsage() );
        }
        if ($this->projectFormData['Proj_boilerEff']) $this->load ('baseModel', $this->projBoilerEff() );
        if ($this->projectFormData['Proj_fuelType']) $this->load ('baseModel', $this->projFuelType() );
        if ($this->projectFormData['Proj_blowdownRate']) $this->load ('baseModel', $this->projBlowdownRate() );
        if ($baseModel->headerCount>1 and $this->projectFormData['Proj_blowdownFlashLP']) $this->load ('baseModel', $this->projBlowdownFlashLP() );
        if ($this->projectFormData['Proj_blowdownHeatX']) $this->load ('baseModel', $this->projBlowdownHeatX() );
        if ($this->projectFormData['Proj_steamGen']) $this->load ('baseModel', $this->projSteamGen() );
        if ($this->projectFormData['Proj_daVentRate']) $this->load ('baseModel', $this->projDaVentRate() );
        
        if ($baseModel->headerCount>2 and $this->projectFormData['Proj_hpMpTurbine']) $this->load ('baseModel', $this->projTurbine('HpMp') );
        if ($baseModel->headerCount>1 and $this->projectFormData['Proj_hpLpTurbine']) $this->load ('baseModel', $this->projTurbine('HpLp') );
        if ($this->projectFormData['Proj_condTurbine']) $this->load ('baseModel', $this->projTurbine('Cond') );
        if ($baseModel->headerCount>2 and $this->projectFormData['Proj_mpLpTurbine']) $this->load ('baseModel', $this->projTurbine('MpLp') );
                
        if ($this->projectFormData['Proj_condRecovery']) $this->load ('baseModel', $this->projCondRecovery() );
        if ($baseModel->headerCount>2 and $this->projectFormData['Proj_condFlashMP']) $this->load ('baseModel', $this->projCondFlashMP() );
        if ($baseModel->headerCount>1 and $this->projectFormData['Proj_condFlashLP']) $this->load ('baseModel', $this->projCondFlashLP() );
        if ($this->projectFormData['Proj_condReturnTemp']) $this->load ('baseModel', $this->projCondReturnTemp() );
                
        if ($this->projectFormData['Proj_heatLossPercent']) $this->load ('baseModel', $this->projHeatLossPercent() );
        
        
    }

    /**
     * New Operating Hours
     * @return array
     */
    public function projOperatingHours(){
        $adjustments = array(
            'operatingHours' => $this->projectFormData['operatingHours'],
            );        
        return $adjustments;
    }
    
    /**
     * New Make Up Water Temp
     * @return array
     */
    public function projMakeupTemp(){
        $adjustments = array(
            'makeupWaterTemp' => $this->projectFormData['makeupWaterTemp'],
            );        
        return $adjustments;
    }
    
    /**
     * New Electricity Unit Cost 
     * @return array
     */
    public function projElectrictyUC(){
        $adjustments = array(
            'sitePowerCost' => $this->projectFormData['sitePowerCost'],
            );        
        return $adjustments;
    }
    
    /**
     * New Fuel Unit Cost 
     * @return array
     */
    public function projFuelUC(){
        $adjustments = array(
            'fuelUnitCost' => $this->projectFormData['fuelUnitCost'],
            );        
        return $adjustments;
    }
    
    /**
     * New Make-Up Water Unit Cost 
     * @return array
     */
    public function projMakeupwaterUC(){
        $adjustments = array(
            'makeupWaterCost' => $this->projectFormData['makeupWaterCost'],
            );        
        return $adjustments;
    }
    
    /**
     * Energy Usage Fixed by Default unless other demand project selected
     * @return array
     */
    private function fixedEnergyUsage(){
        $adjustments = array(
            'energyUsageFixed' => true,            
            'setEnergyUsageHP' => $this->mS->localize($this->baseModel->energyUsageHP,'energyflow'),
            'setEnergyUsageMP' => $this->mS->localize($this->baseModel->energyUsageMP,'energyflow'),
            'setEnergyUsageLP' => $this->mS->localize($this->baseModel->energyUsageLP,'energyflow'),
            );
        return $adjustments;
    }
    
    /**
     * New Steam Demands
     * @return array
     */
    private function projSteamDemand(){
        $adjustments = array(
            'hpSteamUsage' => $this->projectFormData['hpSteamUsage'],
            'mpSteamUsage' => $this->projectFormData['mpSteamUsage'],
            'lpSteamUsage' => $this->projectFormData['lpSteamUsage'],
            );        
        return $adjustments;
    }

    /**
     * New Energy Demands
     * @return array
     */
    private function projEnergyDemand(){        
        $adjustments = array(
            'energyUsageFixed' => true,            
            'setEnergyUsageHP' => $this->projectFormData['setEnergyUsageHP'],
            'setEnergyUsageMP' => $this->projectFormData['setEnergyUsageMP'],
            'setEnergyUsageLP' => $this->projectFormData['setEnergyUsageLP'],
            );        
        return $adjustments;
    }

    /**
     * New Boiler Efficiency
     * @return array
     */
    public function projBoilerEff(){
        $adjustments = array(
            'boilerEff' => $this->projectFormData['boilerEff'],
            );        
        return $adjustments;
    }

    /**
     * New Fuel Type
     * @return array
     */
    public function projFuelType(){
        $adjustments = array(
            'fuelType' => $this->projectFormData['fuelType'],
            );        
        return $adjustments;
    }
    
    /**
     * New Blowdown Rate
     * @return array
     */
    public function projBlowdownRate(){
        $adjustments = array(
            'blowdownRate' => $this->projectFormData['blowdownRate'],
            );        
        return $adjustments;
    }

    /**
     * New DA Vent Rate
     * @return array
     */
    public function projDaVentRate(){
        $adjustments = array(
            'daVentRate' => $this->projectFormData['daVentRate'],
            'daPressure' => $this->projectFormData['daPressure'],
            );
        
        return $adjustments;
    }

    /**
     * New Blowdown Flash Setting
     * @return array
     */
    public function projBlowdownFlashLP(){
        $adjustments = array(
            'blowdownFlashLP' => $this->projectFormData['blowdownFlashLP'],
            );        
        return $adjustments;
    } 

    /**
     * New Boiler Temperature
     * @return array
     */
    public function projSteamGen(){
        $adjustments = array(
            'boilerTemp' => $this->projectFormData['boilerTemp'],
            );        
        return $adjustments;
    } 

    /**
     * New Blowdown Heat Exchanger Setting
     * @return array
     */
    public function projBlowdownHeatX(){
        $adjustments = array(
            'blowdownHeatX' => $this->projectFormData['blowdownHeatX'],
            'blowdownHeatXTemp' => $this->projectFormData['blowdownHeatXTemp'],
            );        
        return $adjustments;
    }         

    /**
     * New Steam Turbine Settings
     * @return array
     */
    public function projTurbine($turbineType){
        $adjustments = array(
            'turbine'.$turbineType.'On' => $this->projectFormData['turbine'.$turbineType.'On'],
            'turbine'.$turbineType.'Method' => $this->projectFormData['turbine'.$turbineType.'Method'],
            'turbine'.$turbineType.'IsoEff' => $this->projectFormData['turbine'.$turbineType.'IsoEff'],
            'turbine'.$turbineType.'GenEff' => $this->projectFormData['turbine'.$turbineType.'GenEff'],
            'turbine'.$turbineType.'FixedFlow' => $this->projectFormData['turbine'.$turbineType.'FixedFlow'],
            'turbine'.$turbineType.'MinFlow' => $this->projectFormData['turbine'.$turbineType.'MinFlow'],
            'turbine'.$turbineType.'MaxFlow' => $this->projectFormData['turbine'.$turbineType.'MaxFlow'],
            'turbine'.$turbineType.'FixedPower' => $this->projectFormData['turbine'.$turbineType.'FixedPower'],
            'turbine'.$turbineType.'MinPower' => $this->projectFormData['turbine'.$turbineType.'MinPower'],
            'turbine'.$turbineType.'MaxPower' => $this->projectFormData['turbine'.$turbineType.'MaxPower'],
            );    
        if ($turbineType=='Cond') $adjustments['turbineCondOutletPressure'] = $this->projectFormData['turbineCondOutletPressure'];
        return $adjustments;
    }
    
    /**
     * New Condensate Recovery Rates
     * @return array
     */
    private function projCondRecovery(){
        $adjustments = array(
            'hpCondReturnRate' => $this->projectFormData['hpCondReturnRate'],
            'mpCondReturnRate' => $this->projectFormData['mpCondReturnRate'],
            'lpCondReturnRate' => $this->projectFormData['lpCondReturnRate'],
            );     
        return $adjustments;
    }
    
    /**
     * New HPtMP Condensate Flash Setting
     * @return array
     */
    private function projCondFlashMP(){
        $adjustments = array(
            'hpCondFlash' => $this->projectFormData['hpCondFlash'],
            );        
        return $adjustments;
    }
    
    /**
     * New MPtLP Condensate Flash Setting
     * @return array
     */
    private function projCondFlashLP(){
        $adjustments = array(
            'mpCondFlash' => $this->projectFormData['mpCondFlash'],
            );        
        return $adjustments;
    }

    /**
     * New Condensate Return Temp
     * @return array
     */
    public function projCondReturnTemp(){
        $adjustments = array(
            'condReturnTemp' => $this->projectFormData['condReturnTemp'],
            );        
        return $adjustments;
    }
    
    /**
     * New HeatLoss Percents
     * @return array
     */
    private function projHeatLossPercent(){
        $adjustments = array(
            'hpHeatLossPercent' => $this->projectFormData['hpHeatLossPercent'],
            'mpHeatLossPercent' => $this->projectFormData['mpHeatLossPercent'],
            'lpHeatLossPercent' => $this->projectFormData['lpHeatLossPercent'],
            );        
        return $adjustments;
    }
    
    /**
     * Return HTML formatted list of Active Projects
     * @return string
     */
    public function displayActiveProjects(){
        $translator = Zend_Registry::get('Zend_Translate');      
        $cats = array();
        $projects = array();
        $projectSelected = $this->projectFormData;
        
        foreach(Steam_Model_Projects::listed($this->baseModel->headerCount) as $key => $values){
            if ($projectSelected[$key]) $cats[$key] = $values[0];
            foreach($values[1] as $projectKey => $value)  if ($projectSelected[$projectKey])   $projects[$key][$projectKey] = $value;
        }
        $activeProjects = "
        <h2>".$translator->_('Adjusted Model: Active Projects')."</h2>
        <table class='data'>";
        foreach($cats as $key => $value){
            $activeProjects .= "<tr><th>".$translator->_($value)."</th></tr><tr><td><ul>";
            foreach($projects[$key] as $projectKey => $value) $activeProjects .= "<li>".$translator->_($value)."</li>";
            $activeProjects .= "</ul></td></tr>";
        }
        $activeProjects .= '</table>';
        return $activeProjects;
    }
}