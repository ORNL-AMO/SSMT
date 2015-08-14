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
 * Universal Steam Calculator Data Transfer Object DTO.
 * Containns specific units associated with value for proper conversions
 * 
 * @package    Steam
 */
class Steam_DTO{
    
    var $data = array();
    
    public function __construct($type, $data, $specificUnits = false, $setUnits = false) {
        //if baseModel load default settings
        if ($type=='baseModel') $this->load('baseModel', $this->baseSetting(), $specificUnits, $setUnits);
        $this->load($type, $data, $specificUnits, $setUnits);
    }
    
    /**
     * Load Data 
     * @param type $type
     * @param type $data
     * @param type $specificUnits
     * @param type $setUnits
     */
    public function load($type, $data, $specificUnits = false, $setUnits = false){
        $mS = Steam_MeasurementSystem::getInstance();
        $typeUnits = $this->unitTypes($type);
        
        foreach($data as $key => $value){
            if (isset($typeUnits[$key]) and $typeUnits[$key]){
                if (substr($typeUnits[$key], 0,9)=='unitcost.'){
                    $selected = $mS->selected[substr($typeUnits[$key],9)];
                }else{
                    $selected = $mS->selected[$typeUnits[$key]];
                }
                if ($specificUnits) $selected = $specificUnits[$key];
                if ($setUnits){
                    if (substr($typeUnits[$key], 0,9)=='unitcost.'){
                        $selected = $setUnits[substr($typeUnits[$key],9)];
                    }else{                        
                        $selected = $setUnits[$typeUnits[$key]];
                    }                    
                }
                $this->data[$key] = array($value, $typeUnits[$key], $selected);
            }else{
                $this->data[$key] = array($value, false, false);
            }
        }        
    }
    
    /**
     * Return $data converted to local units
     * @return array
     */
    public function unload(){
        $mS = Steam_MeasurementSystem::getInstance();
        $data = array();
        foreach($this->data as $key => $values){
            if ($values[1]){
                if (substr($values[1], 0,9)=='unitcost.'){
                    $selected = $mS->selected[substr($values[1],9)];
                }else{
                    $selected = $mS->selected[$values[1]];
                }
                $data[$key] = $mS->convert($values[0], $values[1], $values[2], $selected);
                if ($values[2]<>$selected) $data[$key] = round($data[$key],6);
            }else{
                $data[$key] = $values[0];
            }            
        }
        return $data;        
    }
    
    /**
     * Return $data converted to standard units
     * @return array
     */
    public function unloadStandardized(){
        $mS = Steam_MeasurementSystem::getInstance();
        $data = array();
        foreach($this->data as $key => $values){
            if ($values[1]){
                $data[$key] = $mS->standardize($values[0], $values[1], $values[2]);                
            }else{
                $data[$key] = $values[0];
            }            
        }
        return $data;        
    }
    
    /**
     * Returns fieldNames and associated unitTypes based on $type
     * Includes:
                boiler
                heatloss
                flashtank
                prv
                header
                deaerator
                turbine
                baseModel
     * @param string $type
     * @return array
     */
    static public function unitTypes($type){
        switch($type) {
            case 'boiler':
                $typeUnits = array(
                    'SteamPressure' => 'pressure',
                    'SteamSecondParameter' => false,
                    'SteamTemperature' => 'temperature',
                    'SteamSpecificEnthalpy' => 'specificEnthalpy',
                    'SteamSpecificEntropy' => 'specificEntropy',
                    'SteamQuality' => false,
                    'massFlow' => 'massflow',
                    'daPressure' => 'pressure',
                    'blowdownRate' => false,
                    'combustEff' => false,                
                );
                break;
            case 'heatloss':
                $typeUnits = array(        
                    'Pressure' => 'pressure',
                    'SecondParameter' => false,
                    'Temperature' => 'temperature',
                    'SpecificEnthalpy' => 'specificEnthalpy',
                    'SpecificEntropy' => 'specificEntropy',
                    'Quality' => false,
                    'massFlow' => 'massflow',
                    'heatLossPercent' => false,
                );
                break;
            case 'flashtank':
                $typeUnits = array(        
                    'InletPressure' => 'pressure',
                    'InletSecondParameter' => false,
                    'InletTemperature' => 'temperature',
                    'InletSpecificEnthalpy' => 'specificEnthalpy',
                    'InletSpecificEntropy' => 'specificEntropy',
                    'InletQuality' => false,
                    'Quality' => false,
                    'massFlow' => 'massflow',                    
                    'tankPressure' => 'pressure',
                );
                break;
            case 'prv':
                $typeUnits = array(        
                    'InletPressure' => 'pressure',
                    'InletSecondParameter' => false,
                    'InletTemperature' => 'temperature',
                    'InletSpecificEnthalpy' => 'specificEnthalpy',
                    'InletSpecificEntropy' => 'specificEntropy',
                    'InletQuality' => false,
                    'FeedwaterPressure' => 'pressure',
                    'FeedwaterSecondParameter' => false,
                    'FeedwaterTemperature' => 'temperature',
                    'FeedwaterSpecificEnthalpy' => 'specificEnthalpy',
                    'FeedwaterSpecificEntropy' => 'specificEntropy',
                    'FeedwaterQuality' => false,
                    'massFlow' => 'massflow',                    
                    'outletPressure' => 'pressure',
                    'desuperTemp' => 'temperature',
                );
                break;
            case 'header':
                $typeUnits = array(        
                    'Inlet1Pressure' => 'pressure',
                    'Inlet1SecondParameter' => false,
                    'Inlet1Temperature' => 'temperature',
                    'Inlet1SpecificEnthalpy' => 'specificEnthalpy',
                    'Inlet1SpecificEntropy' => 'specificEntropy',
                    'Inlet1Quality' => false,
                    'massFlow' => 'massflow',
                    'Inlet2Pressure' => 'pressure',
                    'Inlet2SecondParameter' => false,
                    'Inlet2Temperature' => 'temperature',
                    'Inlet2SpecificEnthalpy' => 'specificEnthalpy',
                    'Inlet2SpecificEntropy' => 'specificEntropy',
                    'Inlet2Quality' => false,
                    'massFlow2' => 'massflow',
                );
                break;
            case 'deaerator':
                $typeUnits = array(        
                    'WaterPressure' => 'pressure',
                    'WaterSecondParameter' => false,
                    'WaterTemperature' => 'temperature',
                    'WaterSpecificEnthalpy' => 'specificEnthalpy',
                    'WaterSpecificEntropy' => 'specificEntropy',
                    'WaterQuality' => false,
                    'SteamPressure' => 'pressure',
                    'SteamSecondParameter' => false,
                    'SteamTemperature' => 'temperature',
                    'SteamSpecificEnthalpy' => 'specificEnthalpy',
                    'SteamSpecificEntropy' => 'specificEntropy',
                    'SteamQuality' => false,                  
                    'daPressure' => 'pressure',
                    'ventRate' => false,   
                    'feedwaterFlow' => 'massflow',  
                );
                break;    
            case 'turbine':
                $typeUnits = array(        
                    'InletPressure' => 'pressure',
                    'InletSecondParameter' => false,
                    'InletTemperature' => 'temperature',
                    'InletSpecificEnthalpy' => 'specificEnthalpy',
                    'InletSpecificEntropy' => 'specificEntropy',
                    'InletQuality' => false,
                    'massFlow' => 'massflow',
                    'powerOut' => 'power',
                    'OutletPressure' => 'pressure',
                    'OutletSecondParameter' => false,
                    'OutletTemperature' => 'temperature',
                    'OutletSpecificEnthalpy' => 'specificEnthalpy',
                    'OutletSpecificEntropy' => 'specificEntropy',
                    'OutletQuality' => false,
                );
                break;  
            case 'baseModel':
                $typeUnits = array(                   
                'sitePowerImport' => 'power',
                'sitePowerCost' => 'unitcost.electricity',
                'operatingHours' => false,
                'makeupWaterCost' => 'unitcost.volume',
                'makeupWaterTemp' => 'temperature',
                'fuelUnitCost' => 'unitcost.energy',                  
                'blowdownHeatX' => false,                   
                'blowdownHeatXTemp' => 'temperaturediff',
                    
                'headerCount' => false,
                'highPressure' => 'pressure',
                'mediumPressure' => 'pressure',
                'lowPressure' => 'pressure',
                'hpSteamUsage' => 'massflow',
                'mpSteamUsage' => 'massflow',
                'lpSteamUsage' => 'massflow',
                    
                'energyUsageHP' => 'energyflow',
                'energyUsageMP' => 'energyflow',
                'energyUsageLP' => 'energyflow',
                
                'boilerEff' => false,
                'blowdownRate' => false,
                'blowdownFlashLP' => false,
                'superheatTemp' => 'temperaturediff',
                'boilerTemp' => 'temperature',
                'daPressure' => 'pressure',
                'daVentRate' => false,
                
                'condReturnTemp' => 'temperature',
                'condReturnFlash' => false,
                'hpCondReturnRate' => false,
                'mpCondReturnRate' => false,
                'lpCondReturnRate' => false,
                'hpCondFlash' => false,
                'mpCondFlash' => false,
                                                    
                'heatLossMethod' => false,
                'hpSteamLeaks' => false,
                'mpSteamLeaks' => false,
                'lpSteamLeaks' => false,
                
                'hpSteamTrapLosses' => false,
                'mpSteamTrapLosses' => false,
                'lpSteamTrapLosses' => false,
                
                'hpHeatLossPercent' => false,
                'mpHeatLossPercent' => false,
                'lpHeatLossPercent' => false,
                
                'desuperHeatHpMp' => false,
                'desuperHeatHpMpTemp' => 'temperature',
                'desuperHeatMpLp' => false,
                'desuperHeatMpLpTemp' => 'temperature',
                                        
                'turbineHpMpOn' => false,
                'turbineHpLpOn' => false,
                'turbineMpLpOn' => false,
                'turbineCondOn' => false,
                'turbineHpMpMethod' => false,
                'turbineHpLpMethod' => false,
                'turbineMpLpMethod' => false,
                'turbineCondMethod' => false,
                'turbineHpMpIsoEff' => false,
                'turbineHpLpIsoEff' => false,
                'turbineMpLpIsoEff' => false,
                'turbineCondIsoEff' => false,
                'turbineHpMpGenEff' => false,
                'turbineHpLpGenEff' => false,
                'turbineMpLpGenEff' => false,
                'turbineCondGenEff' => false,
                'turbineCondOutletPressure' => 'vacuum',
                'turbineHpMpFixedFlow' => 'massflow',
                'turbineHpLpFixedFlow' => 'massflow',
                'turbineMpLpFixedFlow' => 'massflow',
                'turbineCondFixedFlow' => 'massflow',
                'turbineHpMpFixedPower' => 'power',
                'turbineHpLpFixedPower' => 'power',
                'turbineMpLpFixedPower' => 'power',
                'turbineCondFixedPower' => 'power',
                'turbineHpMpMinFlow' => 'massflow',
                'turbineHpLpMinFlow' => 'massflow',
                'turbineMpLpMinFlow' => 'massflow',
                'turbineCondMinFlow' => 'massflow',
                'turbineHpMpMaxFlow' => 'massflow',
                'turbineHpLpMaxFlow' => 'massflow',
                'turbineMpLpMaxFlow' => 'massflow',
                'turbineCondMaxFlow' => 'massflow',
                'turbineHpMpMinPower' => 'power',
                'turbineHpLpMinPower' => 'power',
                'turbineMpLpMinPower' => 'power',
                'turbineCondMinPower' => 'power',
                'turbineHpMpMaxPower' => 'power',
                'turbineHpLpMaxPower' => 'power',
                'turbineMpLpMaxPower' => 'power',
                'turbineCondMaxPower' => 'power',
                'setEnergyUsageHP' => 'energyflow',
                'setEnergyUsageMP' => 'energyflow',
                'setEnergyUsageLP' => 'energyflow',
                    );
                break;     
        }
        return $typeUnits;        
    }
    
    /**
     * Default settings for base model
     * @return array
     */
    public function baseSetting(){
        $modelData = array(

            //General
            'sitePowerImport' => 5000,
            'sitePowerCost' => .050, 
            'operatingHours' => 8000,
            'makeupWaterCost' => .0025,
            'makeupWaterTemp' => 50,
            'fuelUnitCost' => 5.78,
            
            'blowdownHeatX' => 'No',
            'blowdownHeatXTemp' => 20,

            //Boiler
            'boilerEff' => 85,
            'blowdownRate' => 2, 
            'blowdownFlashLP' => 'No',
            'superheatTemp' => 100,
            'boilerTemp' => 588.9,
            'fuelType' => 'natGas',
            'daVentRate' => .1,
            'daPressure' => 15,                                    
            'headerCount' => 3,
            'highPressure' => 600,
            'mediumPressure' => 150,
            'lowPressure' => 20,
            'hpSteamUsage' => 50,
            'mpSteamUsage' => 100,
            'lpSteamUsage' => 200,
            'hpCondReturnRate' => 50,
            'mpCondReturnRate' => 50,
            'lpCondReturnRate' => 50,
            'hpCondFlash' => 'No',
            'mpCondFlash' => 'No',
            'condReturnTemp' => 150,
            'condReturnFlash' => 'No',
            'heatLossMethod' => 'percent',
            'hpHeatLossFlow' => 0.1,
            'mpHeatLossFlow' => 0.1,
            'lpHeatLossFlow' => 0.1,
            'hpHeatLossPercent' => 0.1,
            'mpHeatLossPercent' => 0.1,
            'lpHeatLossPercent' => 0.1,
            'desuperHeatHpMp' => 'No',
            'desuperHeatHpMpTemp' => 370,
            'desuperHeatMpLp' => 'No',
            'desuperHeatMpLpTemp' => 270,
            'turbineHpMpOn' => 1,
            'turbineHpLpOn' => 1,
            'turbineMpLpOn' => 0,
            'turbineCondOn' => 0,
            'turbineHpMpIsoEff' => 65,
            'turbineHpLpIsoEff' => 65,
            'turbineMpLpIsoEff' => 65,
            'turbineCondIsoEff' => 65,
            'turbineHpMpGenEff' => 100,
            'turbineHpLpGenEff' => 100,
            'turbineMpLpGenEff' => 100,
            'turbineCondGenEff' => 100,
            'turbineHpMpMethod' => 'balanceHeader',
            'turbineHpLpMethod' => 'balanceHeader',
            'turbineMpLpMethod' => 'balanceHeader',
            'turbineCondMethod' => 'fixedFlow',
            'turbineHpMpFixedFlow' => 100,
            'turbineHpLpFixedFlow' => 100,
            'turbineMpLpFixedFlow' => 100,
            'turbineCondFixedFlow' => 100,
            'turbineHpMpMinFlow' => 50,
            'turbineHpLpMinFlow' => 50,
            'turbineMpLpMinFlow' => 50,
            'turbineCondMinFlow' => 50,
            'turbineHpMpMaxFlow' => 150,
            'turbineHpLpMaxFlow' => 150,
            'turbineMpLpMaxFlow' => 150,
            'turbineCondMaxFlow' => 150,
            'turbineHpMpFixedPower' => 2000,
            'turbineHpLpFixedPower' => 2000,
            'turbineMpLpFixedPower' => 2000,
            'turbineCondFixedPower' => 2000,
            'turbineHpMpMinPower' => 1500,
            'turbineHpLpMinPower' => 1500,
            'turbineMpLpMinPower' => 1500,
            'turbineCondMinPower' => 1500,
            'turbineHpMpMaxPower' => 2500,
            'turbineHpLpMaxPower' => 2500,
            'turbineMpLpMaxPower' => 2500,
            'turbineCondMaxPower' => 2500,
            'calcMarginalCosts' => 'No',
            
            'turbineCondOutletPressure' => 2,
            'hpSteamLeaks' => 0.0,
            'mpSteamLeaks' => 0.0,
            'lpSteamLeaks' => 0.0,

            'hpSteamTrapLosses' => 0,
            'mpSteamTrapLosses' => 0,
            'lpSteamTrapLosses' => 0,
            
        );
        return $modelData;
    }
}