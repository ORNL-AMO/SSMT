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
 * Models a Boiler using Steam Demand
 * 
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_BaseForm extends Steam_StdForm{
    
    /**
     * Steam_MeasurementSystem
     * @var Steam_MeasurementSystem
     */
    var $mS;

    /**
     * Construct Form
     * @param array $options
     */
    function __construct($options = null){
        parent::__construct($options);
        $mS = Steam_MeasurementSystem::getInstance();
        $this->mS = $mS;
        
        $this->addBoilerDetails();
        $this->addGeneralDetails();
        $this->addHeaderDetails();
        
        $desuperHeatHpMp = $this->createElement('select', 'desuperHeatHpMp')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');
        $desuperHeatHpMpTemp = $this->createElement('text', 'desuperHeatHpMpTemp')
                ->setValue($this->mS->rawTemperature(460.927))
                ->setAttrib('style', 'width: 60px;');
        $desuperHeatMpLp = $this->createElement('select', 'desuperHeatMpLp')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');
        $desuperHeatMpLpTemp = $this->createElement('text', 'desuperHeatMpLpTemp')
                ->setValue($this->mS->rawTemperature(405.3722))
                ->setAttrib('style', 'width: 60px;');
        $this->addElements(array(
            $desuperHeatHpMp,
            $desuperHeatHpMpTemp,
            $desuperHeatMpLp,
            $desuperHeatMpLpTemp,
        ));

        $turbineHpMpON = $this->createElement('Checkbox', 'turbineHpMpOn')
                ->setValue(false);
        $turbineHpLpON = $this->createElement('Checkbox', 'turbineHpLpOn')
                ->setValue(false);
        $turbineMpLpON = $this->createElement('Checkbox', 'turbineMpLpOn')
                ->setValue(false);
        $turbineCondON = $this->createElement('Checkbox', 'turbineCondOn')
                ->setValue(false);

        $turbineMethods = array(
            'balanceHeader' => 'Balance Header',
            'fixedFlow' => 'Steam Flow',
            'flowRange' => 'Flow Range',
            'fixedPower' => 'Power Generation',
            'powerRange' => 'Power Range',
            );
        $turbineHpMpMethod = $this->createElement('select', 'turbineHpMpMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue('balanceHeader');
        $turbineHpLpMethod = $this->createElement('select', 'turbineHpLpMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue('balanceHeader');
        $turbineMpLpMethod = $this->createElement('select', 'turbineMpLpMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue('balanceHeader');

        $turbineMethods = array(
            'fixedFlow' => 'Steam Flow',
            'fixedPower' => 'Power Generation',
            );
        $turbineCondMethod = $this->createElement('select', 'turbineCondMethod')
                ->addMultiOptions($turbineMethods)
                ->setValue('balanceHeader');

        $turbineHpMpIsoEff = $this->createElement('text', 'turbineHpMpIsoEff')
                ->setValue(65)
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpIsoEff = $this->createElement('text', 'turbineHpLpIsoEff')
                ->setValue(65)
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpIsoEff = $this->createElement('text', 'turbineMpLpIsoEff')
                ->setValue(65)
                ->setAttrib('style', 'width: 60px;');
        $turbineCondIsoEff = $this->createElement('text', 'turbineCondIsoEff')
                ->setValue(65)
                ->setAttrib('style', 'width: 60px;');

        
        $turbineHpMpGenEff = $this->createElement('text', 'turbineHpMpGenEff')
                ->setValue(98)
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpGenEff = $this->createElement('text', 'turbineHpLpGenEff')
                ->setValue(98)
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpGenEff = $this->createElement('text', 'turbineMpLpGenEff')
                ->setValue(98)
                ->setAttrib('style', 'width: 60px;');
        $turbineCondGenEff = $this->createElement('text', 'turbineCondGenEff')
                ->setValue(98)
                ->setAttrib('style', 'width: 60px;');

        
        $turbineCondOutletPressure = $this->createElement('text', 'turbineCondOutletPressure')
                ->setValue($this->mS->rawVacuum(5))
                ->setAttrib('style', 'width: 60px;');
        
        $turbineHpMpFixedFlow = $this->createElement('text', 'turbineHpMpFixedFlow')                
                ->setValue($this->mS->rawMassflow(45359.237))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpFixedFlow = $this->createElement('text', 'turbineHpLpFixedFlow')
                ->setValue($this->mS->rawMassflow(45359.237))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpFixedFlow = $this->createElement('text', 'turbineMpLpFixedFlow')
                ->setValue($this->mS->rawMassflow(45359.237))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondFixedFlow = $this->createElement('text', 'turbineCondFixedFlow')
                ->setValue($this->mS->rawMassflow(45359.237))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpFixedPower = $this->createElement('text', 'turbineHpMpFixedPower')
                ->setValue($this->mS->rawPower(7200))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpFixedPower = $this->createElement('text', 'turbineHpLpFixedPower')
                ->setValue($this->mS->rawPower(7200))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpFixedPower = $this->createElement('text', 'turbineMpLpFixedPower')
                ->setValue($this->mS->rawPower(7200))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondFixedPower = $this->createElement('text', 'turbineCondFixedPower')
                ->setValue($this->mS->rawPower(7200))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMinFlow = $this->createElement('text', 'turbineHpMpMinFlow')
                ->setValue($this->mS->rawMassflow(22679.6185))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMinFlow = $this->createElement('text', 'turbineHpLpMinFlow')
                ->setValue($this->mS->rawMassflow(22679.6185))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMinFlow = $this->createElement('text', 'turbineMpLpMinFlow')
                ->setValue($this->mS->rawMassflow(22679.6185))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMinFlow = $this->createElement('text', 'turbineCondMinFlow')
                ->setValue($this->mS->rawMassflow(22679.6185))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMaxFlow = $this->createElement('text', 'turbineHpMpMaxFlow')
                ->setValue($this->mS->rawMassflow(68038.8555))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMaxFlow = $this->createElement('text', 'turbineHpLpMaxFlow')
                ->setValue($this->mS->rawMassflow(68038.8555))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMaxFlow = $this->createElement('text', 'turbineMpLpMaxFlow')
                ->setValue($this->mS->rawMassflow(68038.8555))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMaxFlow = $this->createElement('text', 'turbineCondMaxFlow')
                ->setValue($this->mS->rawMassflow(68038.8555))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMinPower = $this->createElement('text', 'turbineHpMpMinPower')
                ->setValue($this->mS->rawPower(5400))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMinPower = $this->createElement('text', 'turbineHpLpMinPower')
                ->setValue($this->mS->rawPower(5400))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMinPower = $this->createElement('text', 'turbineMpLpMinPower')
                ->setValue($this->mS->rawPower(5400))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMinPower = $this->createElement('text', 'turbineCondMinPower')
                ->setValue($this->mS->rawPower(5400))
                ->setAttrib('style', 'width: 60px;');

        $turbineHpMpMaxPower = $this->createElement('text', 'turbineHpMpMaxPower')
                ->setValue($this->mS->rawPower(9000))
                ->setAttrib('style', 'width: 60px;');
        $turbineHpLpMaxPower = $this->createElement('text', 'turbineHpLpMaxPower')
                ->setValue($this->mS->rawPower(9000))
                ->setAttrib('style', 'width: 60px;');
        $turbineMpLpMaxPower = $this->createElement('text', 'turbineMpLpMaxPower')
                ->setValue($this->mS->rawPower(9000))
                ->setAttrib('style', 'width: 60px;');
        $turbineCondMaxPower = $this->createElement('text', 'turbineCondMaxPower')
                ->setValue($this->mS->rawPower(9000))
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

        $submit = $this->createElement('submit', 'Enter')
                ->setLabel('GENERATE BASE MODEL')
                ->setAttrib('style', 'margin: 5px; font-weight: bold; font-size: 1.2em; color: blue;');
        $this->addElements(array(
            $submit,
        ));
        
    }
    
    /**
     * Add Boiler Related Form Elements
     */
    private function addBoilerDetails(){     
        $minTemp = $this->mS->standardize(0,'temperaturediff','F');   
        $maxTemp = $this->mS->standardize(300,'temperaturediff','F');   
      
        $boilerEff = $this->createElement('text', 'boilerEff')
                ->setValue(85)
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => COMBUSTION_EFF_MIN, 'max' => COMBUSTION_EFF_MAX, 'inclusive' => true));        
        $fuelType = $this->createElement('select', 'fuelType')
                ->addMultiOptions(Steam_Fuel::fuelNames())
                ->setValue('natGas');        
        $blowdownRate = $this->createElement('text', 'blowdownRate')
                ->setValue(2)
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => BLOWDOWN_RATE_MIN, 'max' => BLOWDOWN_RATE_MAX, 'inclusive' => true));
        $blowdownFlashLP = $this->createElement('select', 'blowdownFlashLP')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No')
                ->setAttrib('style', 'width: 60px;');
        $blowdownHeatX = $this->createElement('select', 'blowdownHeatX')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');
        $blowdownHeatXTemp = $this->createElement('text', 'blowdownHeatXTemp')
                ->setValue($this->mS->rawTemperaturediff(10))
                ->setAttrib('style', 'width: 60px;');
        $superheatTemp = $this->createElement('text', 'superheatTemp')
                ->setValue(0)
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => $this->mS->localize($minTemp,'temperaturediff'), 'max' => $this->mS->localize($maxTemp,'temperaturediff'), 'inclusive' => true));
        
        $boilerTemp = $this->createElement('text', 'boilerTemp')            
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('lessThan', true, array('max' => $this->mS->maxTemperature()));
        
        $daVentRate = $this->createElement('text', 'daVentRate')
                ->setValue(.1)
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => DA_VENTRATE_MIN, 'max' => DA_VENTRATE_MAX, 'inclusive' => true));
        $daPressure = $this->createElement('text', 'daPressure')                
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->critPressure(), 'inclusive' => true));

        $this->addElements(array(
            $boilerEff,
            $fuelType,
            $blowdownRate,
            $blowdownFlashLP,
            $blowdownHeatX,
            $blowdownHeatXTemp,
            $boilerTemp,
            $daVentRate,
            $daPressure,
        ));
    }
    
    /**
     * Add General Detail Form Elements
     */
    private function addGeneralDetails(){
        $sitePowerImport = $this->createElement('text', 'sitePowerImport')
            ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true);
        $sitePowerCost = $this->createElement('text', 'sitePowerCost')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0));
        $operatingHours = $this->createElement('text', 'operatingHours')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 1, 'max' => 8760, 'inclusive' => true));
        $makeupWaterCost = $this->createElement('text', 'makeupWaterCost')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0));
        
        $minTemp = $this->mS->standardize(39.9999999999999,'temperature','F');   
        $maxTemp = $this->mS->standardize(160.000000000001,'temperature','F');  
        $makeupWaterTemp = $this->createElement('text', 'makeupWaterTemp')
                ->setValue( $this->mS->localize(283.15, 'temperature') )
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => $this->mS->localize($minTemp,'temperature'), 'max' => $this->mS->localize($maxTemp,'temperature'), 'inclusive' => true));

        $fuelUnitCost = $this->createElement('text', 'fuelUnitCost')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0));

        $this->addElements(array(
            $sitePowerImport,
            $sitePowerCost,
            $operatingHours,
            $makeupWaterCost,
            $makeupWaterTemp,
            $fuelUnitCost,
        ));
    }
    
    /**
     * Add Header detail form Elements
     */
    private function addHeaderDetails(){        
        $headerCount = $this->createElement('select', 'headerCount')
                ->addMultiOptions(array('1' => '1 - Header', '2' => '2 - Header', '3' => '3 - Header'))
                ->setValue('3');
        
        $highPressure = $this->createElement('text', 'highPressure')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->critPressure(), 'inclusive' => true));
        $mediumPressure = $this->createElement('text', 'mediumPressure')
                ->setAttrib('style', 'width: 60px;');
        $lowPressure = $this->createElement('text', 'lowPressure')
                ->setAttrib('style', 'width: 60px;');
        $hpSteamUsage = $this->createElement('text', 'hpSteamUsage')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true);
        $mpSteamUsage = $this->createElement('text', 'mpSteamUsage')
                ->setAttrib('style', 'width: 60px;');
        $lpSteamUsage = $this->createElement('text', 'lpSteamUsage')
                ->setAttrib('style', 'width: 60px;');

        $this->addElements(array(
            $headerCount,
            $highPressure,
            $mediumPressure,
            $lowPressure,
            $hpSteamUsage,
            $mpSteamUsage,
            $lpSteamUsage,
        ));                
        
        $condReturnTemp = $this->createElement('text', 'condReturnTemp')
                ->setValue(round($this->mS->localize(338.705556, 'temperature')))
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true);
        
        $condReturnFlash = $this->createElement('select', 'condReturnFlash')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');
        $hpCondReturnRate  = $this->createElement('text', 'hpCondReturnRate')
                ->setAttrib('style', 'width: 60px;')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 0, 'max' => 100, 'inclusive' => true));
        $mpCondReturnRate  = $this->createElement('text', 'mpCondReturnRate')
                ->setAttrib('style', 'width: 60px;');
        $lpCondReturnRate  = $this->createElement('text', 'lpCondReturnRate')
                ->setAttrib('style', 'width: 60px;');
        $hpCondFlash = $this->createElement('select', 'hpCondFlash')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');
        $mpCondFlash = $this->createElement('select', 'mpCondFlash')
                ->addMultiOptions(array('No' => 'No', 'Yes' => 'Yes'))
                ->setValue('No');

        $this->addElements(array(
            $condReturnTemp,
            $condReturnFlash,
            $hpCondReturnRate,
            $mpCondReturnRate,
            $lpCondReturnRate,
            $hpCondFlash,
            $mpCondFlash,
        ));
        
        $hpHeatLossPercent = $this->createElement('text', 'hpHeatLossPercent')
                ->setValue(.1)
                ->setAttrib('style', 'width: 60px;');
        $mpHeatLossPercent= $this->createElement('text', 'mpHeatLossPercent')
                ->setValue(.1)
                ->setAttrib('style', 'width: 60px;');
        $lpHeatLossPercent = $this->createElement('text', 'lpHeatLossPercent')
                ->setValue(.1)
                ->setAttrib('style', 'width: 60px;');

        $this->addElements(array(
            $hpHeatLossPercent,
            $mpHeatLossPercent,
            $lpHeatLossPercent,
            ));
    }
        
    /**
     * Determines if Form Data is Valid (true => valid, false => invalid)
     * @param array() $data Form Data
     * @return boolean Valid
     */
    public function isValid($data) {
        //Remove Commas        
        $data = str_replace(",", "", $data);
        
        $data['heatLossMethod']='percent';
            
        $precision = $this->mS->masterConversionList['temperature'][1][$this->mS->selected['temperature']][4];
        $iapws = new Steam_IAPWS();
        $maxTemp = $this->mS->ceil_dec($this->mS->localize($iapws->saturatedTemperature($this->mS->standardize($data['daPressure'], 'pressure')),'temperature'),$precision);             
        
        if ($data['condReturnTemp']>=$maxTemp or $data['condReturnTemp']<=$this->mS->minTemperature()){
            $this->getElement('condReturnTemp')->addValidator('between', true, array('min' => $this->mS->minTemperature(), 'max' => $maxTemp, 'inclusive' => false));
        }
  
                
            $precision = $this->mS->masterConversionList['temperature'][1][$this->mS->selected['temperature']][4];
            $iapws = new Steam_IAPWS();
            $minTemp = $this->mS->ceil_dec($this->mS->localize($iapws->saturatedTemperature($this->mS->standardize($data['highPressure'],'pressure')),'temperature'),$precision);
            if ($data['boilerTemp']<$minTemp){
                $this->getElement('boilerTemp')
                    ->addValidator('greaterThan', true, array('min' => $minTemp, 'messages' => '%value% is below the boiling temperature [%min%] for boiler pressure.'));
            }
        
        
        if ($data['headerCount']<3){
            $data['turbineHpMpOn'] = null;
            $data['turbineMpLpOn'] = null;
            $data['desuperHeatHpMp']=='No';
            if ($data['mpCondReturnRate']=='') $data['mpCondReturnRate'] = 0;
            if ($data['lpCondReturnRate']=='') $data['lpCondReturnRate'] = 0;
        }
        if ($data['headerCount']<2){
            $data['turbineHpLpOn'] = null;
            $data['desuperHeatMpLp']=='No';
        }
        
        if ($data['headerCount']==3){
            if($data['desuperHeatHpMp']=='Yes'){                
                $this->getElement('desuperHeatHpMpTemp')
                    ->setRequired(true)
                    ->addValidator($this->isFloat,true)
                    ->addValidator('between', true, array('min' => $this->mS->minTemperature(), 'max' => $this->mS->maxTemperature(), 'inclusive' => true));
            }
        }
        if ($data['headerCount']>=2){
            if($data['desuperHeatMpLp']=='Yes'){                
                $this->getElement('desuperHeatMpLpTemp')
                    ->setRequired(true)
                    ->addValidator($this->isFloat,true)
                    ->addValidator('between', true, array('min' => $this->mS->minTemperature(), 'max' => $this->mS->maxTemperature(), 'inclusive' => true));     
            }
        }

        
        if($data['blowdownHeatX']=='Yes'){                
            $this->getElement('blowdownHeatXTemp')
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('greaterThan', true, array('min' => 0, 'max' => $this->mS->maxTemperature(), 'inclusive' => true));
        }
        
        foreach(Steam_Support::steamTurbineCodes() as $turbine){
            if ($data['turbine'.$turbine.'On']==1){
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
                            ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));                    
                        break;
                    case 'flowRange':
                        $this->getElement('turbine'.$turbine.'MinFlow')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $data['turbine'.$turbine.'MaxFlow']));                    
                        
                        $this->getElement('turbine'.$turbine.'MaxFlow')->setRequired('true')
                            ->addValidator($this->isFloat)
                            ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));
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
        
        $this->getElement('hpSteamUsage')->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => true));
                        
            $this->getElement('hpHeatLossPercent')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => HEATLOST_PERCENT_MIN, 'max' => HEATLOST_PERCENT_MAX, 'inclusive' => true));
            
        
        if ($data['headerCount']==3){
            $tmp = $this->getElement('mediumPressure')->setRequired(true)
                ->addValidator($this->isFloat,true);
                   if (isset($data['highPressure'])) $tmp->addValidator('lessThan', true, array('max' => $data['highPressure'], 'messages' => 'Must be less than High Pressure.'));
                   $tmp->addValidator('greaterThan', true, array('min' => $this->mS->minPressure()));
            $tmp = $this->getElement('lowPressure')->setRequired(true)
                ->addValidator($this->isFloat,true);
                   if (isset($data['mediumPressure'])) $tmp->addValidator('lessThan', true, array('max' => $data['mediumPressure'], 'messages' => 'Must be less than Medium Pressure.'));
                   $tmp->addValidator('greaterThan', true, array('min' => $this->mS->minPressure()));                   
            if (isset($data['lowPressure'])) $this->getElement('daPressure')->addValidator('lessThan', true, array('max' => $data['lowPressure'], 'messages' => 'Must be below lowest steam header pressure.'));
            
            
            $this->getElement('mpSteamUsage')->setRequired(true)
                ->addValidator($this->isFloat,true);
            $this->getElement('mpSteamUsage')->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => true));         
                        
            $this->getElement('mpCondReturnRate')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 0, 'max' => 100, 'inclusive' => true));
                                   
            $this->getElement('mpHeatLossPercent')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => HEATLOST_PERCENT_MIN, 'max' => HEATLOST_PERCENT_MAX, 'inclusive' => true));           
        }
        
        if ($data['headerCount']==2){
            
            $tmp = $this->getElement('lowPressure')->setRequired(true)
                ->addValidator($this->isFloat,true);
                   if (isset($data['highPressure'])) $tmp->addValidator('lessThan', true, array('max' => $data['highPressure'], 'messages' => 'Must be less than High Pressure.'));
                   $tmp->addValidator('greaterThan', true, array('min' => $this->mS->minPressure()));                   
            if (isset($data['lowPressure'])) $this->getElement('daPressure')->addValidator('lessThan', true, array('max' => $data['lowPressure'], 'messages' => 'Must be below lowest steam header pressure.'));
                        
        }
        
        if ($data['headerCount']>=2){
            
            $this->getElement('lpSteamUsage')->setRequired(true)
                ->addValidator($this->isFloat,true);
            
            $this->getElement('lpSteamUsage')->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => true));  
            
            $this->getElement('lpCondReturnRate')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => 0, 'max' => 100, 'inclusive' => true));
                      
            $this->getElement('lpHeatLossPercent')->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => HEATLOST_PERCENT_MIN, 'max' => HEATLOST_PERCENT_MAX, 'inclusive' => true));          
        }
        
        if ($data['headerCount']==1){                           
            if (isset($data['highPressure'])) $this->getElement('daPressure')->addValidator('lessThan', true, array('max' => $data['highPressure'], 'messages' => 'Must be below lowest steam header pressure.'));            
            
        }                
        return parent::isValid($data);
    }
}