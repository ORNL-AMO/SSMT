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
 * Generates the Projects Form Object
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_ProjectsForm extends Steam_StdForm{
    
    /**
     * Total number of selected headers
     * @var int (1,2,or 3)
     */
    var $headerCount;
    
    /**
     * Base Model
     * @var Steam_Model_Constructor $baseModel
     */
    var $baseModel;
    
    /**
     * Construct Form
     * @param Steam_Model_Constructor $baseModel
     */
    public function __construct($baseModel) {
        
        parent::__construct();
        $this->headerCount = $baseModel->headerCount;
        $this->baseModel = $baseModel;
        $mS = Steam_MeasurementSystem::getInstance();
        
        //Create a checkbox for each category and project
        $projectList = Steam_Model_Projects::listed();
        $projectCheckBoxes = array();
        foreach($projectList as $cat => $projects){
                $projectCheckBoxes[$cat] = $this->createElement('checkbox', $cat)
                    ->setAttrib('style','margin: 0px; vertical-align: middle;');                
            foreach($projects[1] as $id => $title){
                $projectCheckBoxes[$id] = $this->createElement('checkbox', $id)
                    ->setAttrib('style','margin: 0px; vertical-align: bottom;');                
            }
        }
        $this->addElements($projectCheckBoxes);
        
        //Proj_operatingHours
        $operatingHours = $this->createElement('text', 'operatingHours')
                ->setAttrib('style', 'width: 60px;');
        
        //Proj_makeupTemp
        $makeupWaterTemp = $this->createElement('text', 'makeupWaterTemp')
                ->setValue( $this->mS->localize(283.15, 'temperature') )
                ->setAttrib('style', 'width: 60px;');
        
        //Proj_electricityUC
        $sitePowerCost = $this->createElement('text', 'sitePowerCost')
                ->setAttrib('style', 'width: 60px;');                        

        //Proj_fuelUC
        $fuelUnitCost = $this->createElement('text', 'fuelUnitCost')
                ->setAttrib('style', 'width: 60px;');
        
        //Proj_makeupwaterUC
        $makeupWaterCost = $this->createElement('text', 'makeupWaterCost')
                ->setAttrib('style', 'width: 60px;');

        $this->addElements(array(
            $sitePowerCost,
            $operatingHours,
            $makeupWaterCost,
            $makeupWaterTemp,
            $fuelUnitCost,
        ));
        
        
        //Steam Demand Proj_steamDemand
        $hpSteamUsage = $this->createElement('text', 'hpSteamUsage')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawMassflow($baseModel->hpSteamUsage) );
        $mpSteamUsage = $this->createElement('text', 'mpSteamUsage')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawMassflow($baseModel->mpSteamUsage) );
        $lpSteamUsage = $this->createElement('text', 'lpSteamUsage')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawMassflow($baseModel->lpSteamUsage) );
        $this->addElements(array(
            $hpSteamUsage,
            $mpSteamUsage,
            $lpSteamUsage,
        ));
        
        //Energy Demand Proj_energyDemand
        $energyUsageHP = $this->createElement('text', 'setEnergyUsageHP')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawEnergyflow($baseModel->energyUsageHP) );
        $energyUsageMP = $this->createElement('text', 'setEnergyUsageMP')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawEnergyflow($baseModel->energyUsageMP) );
        $energyUsageLP = $this->createElement('text', 'setEnergyUsageLP')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawEnergyflow($baseModel->energyUsageLP) );
        $this->addElements(array(
            $energyUsageHP,
            $energyUsageMP,
            $energyUsageLP,
        ));
                
        //Proj_boilerEff
        $boilerEff = $this->createElement('text', 'boilerEff')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->boilerEff,1) );
        $this->addElements(array(
            $boilerEff,
        ));
        
        //Proj_blowdownRate
        $blowdownRate = $this->createElement('text', 'blowdownRate')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->blowdownRate,1) );
        $this->addElements(array(
            $blowdownRate,
        ));
        
        $fuelType = $this->createElement('select', 'fuelType')
            ->addMultiOptions(Steam_Fuel::fuelNames())
            ->setValue($baseModel->fuelType);    
        $this->addElements(array(
            $fuelType,
        ));
        
        //Proj_blowdownFlashLP
        $blowdownFlashLP = $this->createElement('select', 'blowdownFlashLP')
        ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))        
            ->setValue( $baseModel->blowdownFlashLP );
        
        $this->addElements(array(
            $blowdownFlashLP,
        ));
                
        //Proj_blowdownHeatX
        $blowdownHeatX = $this->createElement('select', 'blowdownHeatX')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');
        $blowdownHeatXTemp = $this->createElement('text', 'blowdownHeatXTemp')
                ->setValue($this->mS->rawTemperaturediff($baseModel->blowdownHeatXTemp))
                ->setAttrib('style', 'width: 60px;');       
        $this->addElements(array(
            $blowdownHeatX,
            $blowdownHeatXTemp,
        ));
        
        //Proj_steamGen        
        $boilerTemp = $this->createElement('text', 'boilerTemp')            
                ->setAttrib('style', 'width: 60px;');
        $this->addElements(array(
            $boilerTemp,
        ));
        
        //Proj_daVentRate
        $daVentRate = $this->createElement('text', 'daVentRate')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->daVentRate,1) );
        $daPressure = $this->createElement('text', 'daPressure')
                ->setAttrib('style', 'width: 50px;')
                ->setValue($mS->rawPressure($baseModel->daPressure) );
        $this->addElements(array(
            $daVentRate,
            $daPressure,
        ));
               
        //Proj_hpLpTurbine
        //Proj_hpMpTurbine
        //Proj_mpLpTurbine     
        //Proj_condTurbine                            
        $turbineHpMpON = $this->createElement('Checkbox', 'turbineHpMpOn')
                ->setValue($baseModel->turbineHpMpOn);
        $turbineHpLpON = $this->createElement('Checkbox', 'turbineHpLpOn')
                ->setValue($baseModel->turbineHpLpOn);
        $turbineMpLpON = $this->createElement('Checkbox', 'turbineMpLpOn')
                ->setValue($baseModel->turbineMpLpOn);
        $turbineCondON = $this->createElement('Checkbox', 'turbineCondOn')
                ->setValue($baseModel->turbineCondOn);

        $turbineMethods = array(
            'balanceHeader' => 'Balance Header',
            'fixedFlow' => 'Steam Flow',
            'flowRange' => 'Flow Range',
            'fixedPower' => 'Power Generation',
            'powerRange' => 'Power Range',
            );
        $turbineHpMpMethod = $this->createElement('select', 'turbineHpMpMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue($baseModel->turbineHpMpMethod);
        $turbineHpLpMethod = $this->createElement('select', 'turbineHpLpMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue($baseModel->turbineHpLpMethod);
        $turbineMpLpMethod = $this->createElement('select', 'turbineMpLpMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue($baseModel->turbineMpLpMethod);

        $turbineMethods = array(
            'fixedFlow' => 'Steam Flow',
            'fixedPower' => 'Power Generation',
            );
        $turbineCondMethod = $this->createElement('select', 'turbineCondMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue($baseModel->turbineCondMethod);

        $turbineHpMpIsoEff = $this->createElement('text', 'turbineHpMpIsoEff')                
                ->setValue($baseModel->turbineHpMpIsoEff)
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpIsoEff = $this->createElement('text', 'turbineHpLpIsoEff')
                ->setValue($baseModel->turbineHpLpIsoEff)
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpIsoEff = $this->createElement('text', 'turbineMpLpIsoEff')
                ->setValue($baseModel->turbineMpLpIsoEff)
                ->setAttrib('style', 'width: 60px;');
        $turbineCondIsoEff = $this->createElement('text', 'turbineCondIsoEff')
                ->setValue($baseModel->turbineCondIsoEff)
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpGenEff = $this->createElement('text', 'turbineHpMpGenEff')                
                ->setValue($baseModel->turbineHpMpGenEff)
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpGenEff = $this->createElement('text', 'turbineHpLpGenEff')
                ->setValue($baseModel->turbineHpLpGenEff)
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpGenEff = $this->createElement('text', 'turbineMpLpGenEff')
                ->setValue($baseModel->turbineMpLpGenEff)
                ->setAttrib('style', 'width: 60px;');
        $turbineCondGenEff = $this->createElement('text', 'turbineCondGenEff')
                ->setValue($baseModel->turbineCondGenEff)
                ->setAttrib('style', 'width: 60px;');
       
        $turbineCondOutletPressure = $this->createElement('text', 'turbineCondOutletPressure')
                ->setValue($this->mS->rawVacuum(5))
                ->setAttrib('style', 'width: 60px;');
        
        $turbineHpMpFixedFlow = $this->createElement('text', 'turbineHpMpFixedFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineHpMpFixedFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpFixedFlow = $this->createElement('text', 'turbineHpLpFixedFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineHpLpFixedFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpFixedFlow = $this->createElement('text', 'turbineMpLpFixedFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineMpLpFixedFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondFixedFlow = $this->createElement('text', 'turbineCondFixedFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineCondFixedFlow))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpFixedPower = $this->createElement('text', 'turbineHpMpFixedPower')
                ->setValue($mS->rawPower($baseModel->turbineHpMpFixedPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpFixedPower = $this->createElement('text', 'turbineHpLpFixedPower')
                ->setValue($mS->rawPower($baseModel->turbineHpLpFixedPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpFixedPower = $this->createElement('text', 'turbineMpLpFixedPower')
                ->setValue($mS->rawPower($baseModel->turbineMpLpFixedPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondFixedPower = $this->createElement('text', 'turbineCondFixedPower')
                ->setValue($mS->rawPower($baseModel->turbineCondFixedPower))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMinFlow = $this->createElement('text', 'turbineHpMpMinFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineHpMpMinFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMinFlow = $this->createElement('text', 'turbineHpLpMinFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineHpLpMinFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMinFlow = $this->createElement('text', 'turbineMpLpMinFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineMpLpMinFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMinFlow = $this->createElement('text', 'turbineCondMinFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineCondMinFlow))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMaxFlow = $this->createElement('text', 'turbineHpMpMaxFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineHpMpMaxFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMaxFlow = $this->createElement('text', 'turbineHpLpMaxFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineHpLpMaxFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMaxFlow = $this->createElement('text', 'turbineMpLpMaxFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineMpLpMaxFlow))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMaxFlow = $this->createElement('text', 'turbineCondMaxFlow')
                ->setValue($mS->rawMassflow($baseModel->turbineCondMaxFlow))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMinPower = $this->createElement('text', 'turbineHpMpMinPower')
                ->setValue($mS->rawPower($baseModel->turbineHpMpMinPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMinPower = $this->createElement('text', 'turbineHpLpMinPower')
                ->setValue($mS->rawPower($baseModel->turbineHpLpMinPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMinPower = $this->createElement('text', 'turbineMpLpMinPower')
                ->setValue($mS->rawPower($baseModel->turbineMpLpMinPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMinPower = $this->createElement('text', 'turbineCondMinPower')
                ->setValue($mS->rawPower($baseModel->turbineCondMinPower))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMaxPower = $this->createElement('text', 'turbineHpMpMaxPower')
                ->setValue($mS->rawPower($baseModel->turbineHpMpMaxPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMaxPower = $this->createElement('text', 'turbineHpLpMaxPower')
                ->setValue($mS->rawPower($baseModel->turbineHpLpMaxPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMaxPower = $this->createElement('text', 'turbineMpLpMaxPower')
                ->setValue($mS->rawPower($baseModel->turbineMpLpMaxPower))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMaxPower = $this->createElement('text', 'turbineCondMaxPower')
                ->setValue($mS->rawPower($baseModel->turbineCondMaxPower))
                ->setAttrib('style', 'width: 60px;');


        $this->addElements(array(
            $turbineHpMpON,
            $turbineHpLpON,
            $turbineMpLpON,
            $turbineCondON,

            $turbineHpMpMethod,
            $turbineHpLpMethod,
            $turbineMpLpMethod,
            $turbineCondMethod,

            $turbineHpMpIsoEff,
            $turbineHpLpIsoEff,
            $turbineMpLpIsoEff,
            $turbineCondIsoEff,

            $turbineHpMpGenEff,
            $turbineHpLpGenEff,
            $turbineMpLpGenEff,
            $turbineCondGenEff,
            
            $turbineCondOutletPressure,
            
            $turbineHpMpFixedFlow,
            $turbineHpLpFixedFlow,
            $turbineMpLpFixedFlow,
            $turbineCondFixedFlow,

            $turbineHpMpFixedPower,
            $turbineHpLpFixedPower,
            $turbineMpLpFixedPower,
            $turbineCondFixedPower,

            $turbineHpMpMinFlow,
            $turbineHpLpMinFlow,
            $turbineMpLpMinFlow,
            $turbineCondMinFlow,

            $turbineHpMpMaxFlow,
            $turbineHpLpMaxFlow,
            $turbineMpLpMaxFlow,
            $turbineCondMaxFlow,

            $turbineHpMpMinPower,
            $turbineHpLpMinPower,
            $turbineMpLpMinPower,
            $turbineCondMinPower,

            $turbineHpMpMaxPower,
            $turbineHpLpMaxPower,
            $turbineMpLpMaxPower,
            $turbineCondMaxPower,
        ));
        
        //Proj_condRecovery
        $hpCondReturnRate = $this->createElement('text', 'hpCondReturnRate')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->hpCondReturnRate,1) );
        $mpCondReturnRate = $this->createElement('text', 'mpCondReturnRate')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->mpCondReturnRate,1) );
        $lpCondReturnRate = $this->createElement('text', 'lpCondReturnRate')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->lpCondReturnRate,1) );
        $this->addElements(array(
            $hpCondReturnRate,
            $mpCondReturnRate,
            $lpCondReturnRate,
        ));
        
        //Proj_condFlashMP
        $hpCondFlash = $this->createElement('select', 'hpCondFlash')
        ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))        
            ->setValue( $baseModel->hpCondFlash );
        
        $this->addElements(array(
            $hpCondFlash,
        ));
        
        //Proj_condFlashLP
        $mpCondFlash = $this->createElement('select', 'mpCondFlash')
        ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))        
            ->setValue( $baseModel->mpCondFlash );
        
        $this->addElements(array(
            $mpCondFlash,
        ));
        
        //Proj_condReturnTemp        
        $condReturnTemp = $this->createElement('text', 'condReturnTemp')            
                ->setAttrib('style', 'width: 60px;');
        $this->addElements(array(
            $condReturnTemp,
        ));
        
        //Proj_heatLossPercent
        $hpHeatLossPercent = $this->createElement('text', 'hpHeatLossPercent')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->hpHeatLossPercent,1) );
        $mpHeatLossPercent = $this->createElement('text', 'mpHeatLossPercent')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->mpHeatLossPercent,1) );
        $lpHeatLossPercent = $this->createElement('text', 'lpHeatLossPercent')
                ->setAttrib('style', 'width: 50px;')
                ->setValue(number_format($baseModel->lpHeatLossPercent,1) );
        $this->addElements(array(
            $hpHeatLossPercent,
            $mpHeatLossPercent,
            $lpHeatLossPercent,
        ));
        
        $submit = $this->createElement('submit', 'Enter')
                ->setLabel('GENERATE ADJUSTED MODEL')
                ->setAttrib('style', 'margin: 5px; font-weight: bold; font-size: 1.2em; color: blue;');
        $this->addElements(array(
            $submit,
        ));
    }

    /**
     * Determines if Form Data is Valid (true => valid, false => invalid)
     * Add validates based on selected projects and header count
     * @param array $data Form Data
     * @return boolean Valid
     */
    public function isValid($data) {
        //Remove Commas        
        $data = str_replace(",", "", $data);
        
        
        
        
        //Proj_operatingHours
        if (isset($data['Proj_operatingHours']) and $data['Proj_operatingHours']){
            $this->getElement('operatingHours')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 1, 'max' => 8760, 'inclusive' => true));
        }
        
        //Proj_makeupTemp
        if (isset($data['Proj_makeupTemp']) and $data['Proj_makeupTemp']){
            $minTemp = $this->mS->standardize(39.9999999999999,'temperature','F');   
            $maxTemp = $this->mS->standardize(160.000000000001,'temperature','F');  
            $this->getElement('makeupWaterTemp')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => $this->mS->localize($minTemp,'temperature'), 'max' => $this->mS->localize($maxTemp,'temperature'), 'inclusive' => true));
        }
        
        //Proj_electricityUC
        if (isset($data['Proj_electricityUC']) and $data['Proj_electricityUC']){
            $this->getElement('sitePowerCost')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0));  
        }                   

        //Proj_fuelUC
        if (isset($data['Proj_fuelUC']) and $data['Proj_fuelUC']){
            $this->getElement('fuelUnitCost')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0));  
        }
        
        //Proj_makeupwaterUC
        if (isset($data['Proj_makeupwaterUC']) and $data['Proj_makeupwaterUC']){
            $this->getElement('makeupWaterCost')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0));  
        }                
        
        if ($data['Proj_steamDemand']){
            $this->getElement('hpSteamUsage')->setRequired(true)
                ->addValidator($this->isFloat,true)                    
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => true));
            
            if ($this->headerCount==3){
                $this->getElement('mpSteamUsage')->setRequired(true)
                    ->addValidator($this->isFloat,true)                        
                    ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => true));
            }
            if ($this->headerCount>1){
                $this->getElement('lpSteamUsage')->setRequired(true)
                    ->addValidator($this->isFloat,true)
                    ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => true));                
            }
                 
        }
        
        if ($data['Proj_energyDemand']){
            $tmp = $this->getElement('setEnergyUsageHP')->setRequired(true)
                ->addValidator($this->isFloat,true);
            if (!$data['setEnergyUsageHP']==0) $tmp->addValidator('greaterThan', true, array('min' => 0, 'messages' => 'Must be greater than or equal to 0.'));
            if ($this->headerCount==3){
                $this->getElement('setEnergyUsageMP')->setRequired(true)
                    ->addValidator($this->isFloat,true);
                if (!$data['setEnergyUsageMP']==0) $this->getElement('setEnergyUsageMP')->addValidator('greaterThan', true, array('min' => 0, 'messages' => 'Must be greater than or equal to 0.'));                   
            }
            if ($this->headerCount>1){
                $this->getElement('setEnergyUsageLP')->setRequired(true)
                    ->addValidator($this->isFloat,true);
                if (!$data['setEnergyUsageLP']==0) $this->getElement('setEnergyUsageLP')->addValidator('greaterThan', true, array('min' => 0, 'messages' => 'Must be greater than or equal to 0.'));
            } 
        }
        
        if ($data['Proj_boilerEff']){        
            $this->getElement('boilerEff')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => COMBUSTION_EFF_MIN, 'max' => COMBUSTION_EFF_MAX, 'inclusive' => true));
        }
        if ($data['Proj_blowdownRate']){
            $this->getElement('blowdownRate')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => BLOWDOWN_RATE_MIN, 'max' => BLOWDOWN_RATE_MAX, 'inclusive' => true));        
        }
        if ($this->headerCount>1 and $data['Proj_blowdownFlashLP']){        
        }
        if ($data['Proj_steamGen']){          
            $precision = $this->mS->masterConversionList['temperature'][1][$this->mS->selected['temperature']][4];
            $iapws = new Steam_IAPWS();
            $minTemp = $this->mS->ceil_dec($this->mS->localize($iapws->saturatedTemperature($this->baseModel->highPressure),'temperature'),$precision);
 
            $this->getElement('boilerTemp')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('lessThan', true, array('max' => $this->mS->maxTemperature()));
            if ($data['boilerTemp']<$minTemp){
                $this->getElement('boilerTemp')->addValidator('greaterThan', true, array('min' => $minTemp, 'messages' => '%value% is below the boiling temperature [%min%] for boiler pressure.'));
            }
            
        }
        if ($data['Proj_daVentRate']){        
            $this->getElement('daVentRate')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => DA_VENTRATE_MIN, 'max' => DA_VENTRATE_MAX, 'inclusive' => true));        
            $this->getElement('daPressure')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->critPressure(), 'inclusive' => true));        
        }
        
        $turbines = array(
            'HpLp',
            'HpMp',
            'MpLp',
            'Cond',
        );
        foreach($turbines as $turbine){
            if (  ( ($turbine=='HpLp' and $this->headerCount>1 and $data['Proj_hpLpTurbine'])
                    or ($turbine=='HpMp' and $this->headerCount==3 and $data['Proj_hpMpTurbine'])
                    or ($turbine=='MpLp' and $this->headerCount==3 and $data['Proj_mpLpTurbine'])
                    or ($turbine=='Cond' and $data['Proj_condTurbine']) )                     
                    and $data['turbine'.$turbine.'On']==1){
                
                $this->getElement('turbine'.$turbine.'IsoEff')->setRequired('true')
                    ->addValidator($this->isFloat)
                    ->addValidator('Between', true, array('min' => ISOEFF_MIN, 'max' => ISOEFF_MAX));                
                $this->getElement('turbine'.$turbine.'GenEff')->setRequired('true')
                    ->addValidator($this->isFloat)
                    ->addValidator('Between', true, array('min' => GENEFF_MIN, 'max' => GENEFF_MAX));
                
                if ($turbine=='Cond'){
                    $this->getElement('turbineCondOutletPressure')->setRequired(true)
                    ->addValidator($this->isFloat,true)
                    ->addValidator('between', true, array('min' => $this->mS->minVacuum(), 'max' => $this->mS->condVacuum(), 'inclusive' => true));
                }
                
                switch($data['turbine'.$turbine.'Method']){
                    case 'fixedFlow':
                        $this->getElement('turbine'.$turbine.'FixedFlow')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('greaterThan', true, array('min' => 0));                    
                        break;
                    case 'flowRange':
                        $this->getElement('turbine'.$turbine.'MinFlow')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('between', true, array('min' => 0, 'max' => $data['turbine'.$turbine.'MaxFlow']));                    
                        
                        $this->getElement('turbine'.$turbine.'MaxFlow')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('greaterThan', true, array('min' => 0));                    
                        break;

                    case 'fixedPower':
                        $this->getElement('turbine'.$turbine.'FixedPower')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('greaterThan', true, array('min' => 0));                    
                        break;
                    case 'powerRange':
                        $this->getElement('turbine'.$turbine.'MinPower')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('between', true, array('min' => 0, 'max' => $data['turbine'.$turbine.'MaxPower']));                    
                        
                        $this->getElement('turbine'.$turbine.'MaxPower')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('greaterThan', true, array('min' => 0));                    
                        break;

                    case 'balanceHeader':
                        break;
                }
            }
        }
        
        if ($data['Proj_condRecovery']){
            $this->getElement('hpCondReturnRate')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 0, 'max' => 100, 'inclusive' => true));
            if ($this->headerCount==3){
                $this->getElement('mpCondReturnRate')->setRequired(true)
                    ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 0, 'max' => 100, 'inclusive' => true));
            }
            if ($this->headerCount>1){
                $this->getElement('lpCondReturnRate')->setRequired(true)
                    ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 0, 'max' => 100, 'inclusive' => true));
            }
        }
        if ($this->headerCount==3 and $data['Proj_condFlashMP']){
        
        }
        if ($this->headerCount>1 and $data['Proj_condFlashLP']){
        
        }
        if ($data['Proj_condReturnTemp']){          
            $precision = $this->mS->masterConversionList['temperature'][1][$this->mS->selected['temperature']][4];
            $iapws = new Steam_IAPWS();
            $maxTemp = $this->mS->ceil_dec($this->mS->localize($iapws->saturatedTemperature($this->baseModel->daPressure),'temperature'),$precision);             
            $this->getElement('condReturnTemp')->setRequired(true)
                ->addValidator($this->isFloat,true);
                if ($data['condReturnTemp']>=$maxTemp or $data['condReturnTemp']<=$this->mS->minTemperature()){
                    $this->getElement('condReturnTemp')->addValidator('between', true, array('min' => $this->mS->minTemperature(), 'max' => $maxTemp, 'inclusive' => false));
                }
            
        }
        
        if ($data['Proj_heatLossPercent']){
            $this->getElement('hpHeatLossPercent')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => HEATLOST_PERCENT_MIN, 'max' => HEATLOST_PERCENT_MAX, 'inclusive' => true));
            if ($this->headerCount==3){
                $this->getElement('mpHeatLossPercent')->setRequired(true)
                    ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => HEATLOST_PERCENT_MIN, 'max' => HEATLOST_PERCENT_MAX, 'inclusive' => true));
            }
            if ($this->headerCount>1){
                $this->getElement('lpHeatLossPercent')->setRequired(true)
                    ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => HEATLOST_PERCENT_MIN, 'max' => HEATLOST_PERCENT_MAX, 'inclusive' => true));
            }        
        }
        return parent::isValid($data);
    }
}