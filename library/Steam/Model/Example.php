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
 * Loads Examples Steam Models
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Example{
    
    /**
     * Example Units
     * @var array
     */
    var $units = array(
        'temperature' => 'F',
        'temperaturediff' => 'F',
        'pressure' => 'psig',
        'vacuum' => 'psia',
        'power' => 'kW',
        'electricity' => 'kWh',
        'volume' => 'gal',
        'energy' => 'MMBtu',
        'massflow' => 'klb/hr',                        
    );
    
    /**
     * Base Model Loaded Flag
     * @var boolean
     */
    var $baseModelLoaded = true;
    
    /**
     * Adjusted Model Loaded Flag
     * @var boolean
     */
    var $adjustedModelLoaded = false;
    
    /**
     * Status of Example load
     * @var string 
     */
    var $status;
    
    /**
     * Base Model Data DTO
     * @var Steam_DTO
     */
    var $modelData;
    
    /**
     * Adjusted Model Data DTO
     * @var Steam_DTO
     */
    var $adjustedData;
                
    /**
     * Loads selected $example if found
     * @param string $example
     */
    public function __construct($example) {
        $examples = explode('.', $example);
        $this->baseModelLoaded = true;    
        $this->adjustedModelLoaded = false;            
        
        //Load Base Model Data
        switch($examples[0]){
            case 'SSAT1':
                $this->status = "Example: SSAT Default 1 Header Model - Loaded Successfully";
                $exampleSpecificData = self::SSAT1();  
                break;
            case 'SSAT2':
                $this->status = "Example: SSAT Default 2 Header Model - Loaded Successfully";
                $exampleSpecificData = self::SSAT2();  
                break;
            case 'SSAT3':
                $this->status = "Example: SSAT Default 3 Header Model - Loaded Successfully";
                $exampleSpecificData = self::SSAT3();  
                break;
            case 'random':
                $this->status = "Example: RANDOM Model - Loaded Successfully";
                $exampleSpecificData = self::baseModel('random');  
                break;
            case 'randomLimit':
                $this->status = "Example: RANDOM Model - Loaded Successfully";
                $exampleSpecificData = self::randomLimit();  
                break;
            default:
                $this->status = "<span style='color: red;'>Example: {$example} not found</span>";
                $this->baseModelLoaded = false;
                break;
        }
        if ($this->baseModelLoaded){
            $this->modelData = new Steam_DTO('baseModel', $exampleSpecificData, false, $this->units);

            //Load Adjusted Model Data
            if (isset($examples[1])){
                switch($examples[1]){
                    case 'noTurbines':
                        $this->status = "Example: SSAT3 with no Turbines- Loaded Successfully";
                        $exampleSpecificData = array(
                            'Cat_Turbine' => 1,
                            'Proj_hpLpTurbine' => 1,
                            'Proj_hpMpTurbine' => 1,
                            'Proj_mpLpTurbine' => 1,
                            'Proj_condTurbine' => 1,
                            'turbineHpMpOn' => 0,
                            'turbineHpLpOn' => 0,
                            'turbineMpLpOn' => 0,
                            'turbineCondOn' => 0,
                            );
                        $this->adjustedModelLoaded = true;  
                        break;
                    case 'flashCond':
                        $this->status = "Example: SSAT3 with Flash Steam - Loaded Successfully";
                        $exampleSpecificData = array(
                            'Cat_Boiler' => 1,	
                            'Proj_blowdownFlashLP' => 1,
                            'blowdownFlashLP' => 'Yes',
                            'Cat_Condensate' => 1,
                            'Proj_condFlashMP' => 1,
                            'Proj_condFlashLP' => 1,
                            'hpCondFlash' => 'Yes',
                            'mpCondFlash' => 'Yes',
                            );
                        $this->adjustedModelLoaded = true;  
                        break;
                    case 'halfSteam':
                        $this->status = "Example: Adjusted Half Steam - Loaded Successfully";
                        $exampleSpecificData = array(
                            'Cat_Demand' => 1,	
                            'Proj_steamDemand' => 1,
                            'hpSteamUsage' => 25,
                            'mpSteamUsage' => 50,
                            'lpSteamUsage' => 100,
                            );  
                        $this->adjustedModelLoaded = true;  
                        break;
                    default:
                        break;
                }

                if ($this->adjustedModelLoaded) {
                    $this->adjustedData = clone $this->modelData;
                    $this->adjustedData->load('baseModel', $this->adjustedNone(), false, $this->units);
                    $this->adjustedData->load('baseModel', $exampleSpecificData, false, $this->units);
                }
            }
        }        
    }
    
    /*
     * Return Base Model DTO
     */
    public function unloadBase(){        
        return $this->modelData;
    }
    
    /*
     * Return Adjusted Model DTO
     */
    public function unloadAdjusted(){
        return $this->adjustedData;
    }
    
    /*
     * Set all adjustments/projects to off
     */
    public function adjustedNone(){
        $adjustedNone = array(
            'Cat_General' => 0,
            'Cat_Unitcost' => 0,
            'Cat_Demand' => 0,
            'Proj_steamDemand' => 0,
            'Proj_energyDemand' => 0,
            'Cat_Boiler' => 0,
            'Proj_boilerEff' => 0,
            'Proj_blowdownRate' => 0,
            'Proj_blowdownFlashLP' => 0,
            'Proj_steamGen' => 0,
            'Proj_heatXblowdown' => 0,
            'Proj_daVentRate' => 0,
            'Cat_Turbine' => 0,
            'Proj_hpLpTurbine' => 0,
            'Proj_hpMpTurbine' => 0,
            'Proj_mpLpTurbine' => 0,
            'Proj_condTurbine' => 0,
            'Cat_Condensate' => 0,
            'Proj_condRecovery' => 0,
            'Proj_condFlashMP' => 0,
            'Proj_condFlashLP' => 0,
            'Proj_heatXcond' => 0,
            'Proj_heatLossPercent' => 0,
            'Cat_Other' => 0,
            'Proj_insulation' => 0,
                    );
        return $adjustedNone;
    }
            
    /**
     * Generates Example Data for Modeler
     * @param string $type 
     * @return array() 
     */ 
    public static function baseModel($type){
        $maxPressure = 800;
        $mS = Steam_MeasurementSystem::getInstance();
        
        $yesNo = array(0 => 'Yes', 1 => 'No');
        
        $data['boilerEff'] = rand(75,87);        
        $data['blowdownRate'] = rand(1,5);
        $data['blowdownFlashLP'] = $yesNo[rand(0,1)];
        $data['superheatTemp'] = rand(0,300);     
        
        $data['daVentRate'] = rand(1,5)/10;    

        $data['sitePowerImport'] = rand(-10000,10000);
        $data['sitePowerCost'] = 0.05;
        $data['operatingHours'] = rand(4000,8760);
        $data['makeupWaterCost'] = .0025;
        $data['makeupWaterTemp'] = rand(40,120);
        $data['fuelUnitCost'] = 5.78;
        
                
        $data['headerCount'] = rand(1, 3);
        $data['highPressure'] = rand(22, $maxPressure);
        $data['mediumPressure'] = rand(21, $data['highPressure']-1);
        $data['lowPressure'] = rand(20, $data['mediumPressure']-1);
        
            $precision = $mS->masterConversionList['temperature'][1][$mS->selected['temperature']][4];
            $iapws = new Steam_IAPWS();
            $minTemp = $mS->ceil_dec($mS->localize($iapws->saturatedTemperature($mS->standardize($data['highPressure'],'pressure')),'temperature'),$precision);
        $data['boilerTemp'] = $minTemp+rand(0,300);
                   
        $data['daPressure'] = rand(0, 15);
        
        $data['hpSteamUsage'] = rand(0, 300);
        $data['mpSteamUsage'] = rand(0, 300);
        $data['lpSteamUsage'] = rand(0, 300);
        $data['hpCondReturnRate'] = rand(40, 90);
        $data['mpCondReturnRate'] = rand(40, 90);
        $data['lpCondReturnRate'] = rand(40, 90);

        $data['hpCondFlash'] = $yesNo[rand(0,1)];
        $data['mpCondFlash'] = $yesNo[rand(0,1)];
        
        $data['condReturnTemp'] = 150;
        
        $data['hpHeatLossPercent'] = rand(HEATLOST_PERCENT_MIN*10,40)/10;   
        $data['mpHeatLossPercent'] = rand(HEATLOST_PERCENT_MIN*10,40)/10;   
        $data['lpHeatLossPercent'] = rand(HEATLOST_PERCENT_MIN*10,40)/10;   

        $data['desuperHeatHpMp'] = $yesNo[rand(0,1)];
        $data['desuperHeatHpMpTemp'] = rand(300,800);
        $data['desuperHeatMpLp'] = $yesNo[rand(0,1)];
        $data['desuperHeatMpLpTemp'] = rand(300,800);
        
        $data['turbineHpMpOn'] = rand(0,1);
        $data['turbineHpLpOn'] = rand(0,1);
        $data['turbineMpLpOn'] = rand(0,1);
        $data['turbineCondOn'] = rand(0,1);
        
        $data['turbineHpMpIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        $data['turbineHpLpIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        $data['turbineMpLpIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        $data['turbineCondIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        
        $data['turbineCondOutletPressure'] = rand(2,14);
        
        $data['turbineHpMpGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        $data['turbineHpLpGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        $data['turbineMpLpGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        $data['turbineCondGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        
        $turbineMethods = array(
            0 => 'balanceHeader',
            1 => 'fixedFlow',
            2 => 'fixedPower',
            3 => 'flowRange',
            4 => 'powerRange',
            );
        $data['turbineHpMpMethod'] = $turbineMethods[rand(0,4)];
        $data['turbineHpLpMethod'] = $turbineMethods[rand(0,4)];
        $data['turbineMpLpMethod'] = $turbineMethods[rand(0,4)];
        $turbineMethods = array(
            0 => 'fixedFlow',
            1 => 'fixedPower',
            );
        $data['turbineCondMethod'] = $turbineMethods[rand(0,1)];
        
        $data['turbineHpMpFixedFlow'] = rand(1,300);
        $data['turbineHpLpFixedFlow'] = rand(1,300);
        $data['turbineMpLpFixedFlow'] = rand(1,300);
        $data['turbineCondFixedFlow'] = rand(1,300);
        
        $data['turbineHpMpMaxFlow'] = rand(1,300);
        $data['turbineHpLpMaxFlow'] = rand(1,300);
        $data['turbineMpLpMaxFlow'] = rand(1,300);
        $data['turbineCondMaxFlow'] = rand(1,300);
        
        $data['turbineHpMpMinFlow'] = rand(0,$data['turbineHpMpMaxFlow']-1);
        $data['turbineHpLpMinFlow'] = rand(0,$data['turbineHpLpMaxFlow']-1);
        $data['turbineMpLpMinFlow'] = rand(0,$data['turbineMpLpMaxFlow']-1);
        $data['turbineCondMinFlow'] = rand(0,$data['turbineCondMaxFlow']-1);
        
        $data['turbineHpMpFixedPower'] = rand(1,300);
        $data['turbineHpLpFixedPower'] = rand(1,300);
        $data['turbineMpLpFixedPower'] = rand(1,300);
        $data['turbineCondFixedPower'] = rand(1,300);
        
        $data['turbineHpMpMaxPower'] = rand(1,300);
        $data['turbineHpLpMaxPower'] = rand(1,300);
        $data['turbineMpLpMaxPower'] = rand(1,300);
        $data['turbineCondMaxPower'] = rand(1,300);
        
        $data['turbineHpMpMinPower'] = rand(0,$data['turbineHpMpMaxPower']-1);
        $data['turbineHpLpMinPower'] = rand(0,$data['turbineHpLpMaxPower']-1);
        $data['turbineMpLpMinPower'] = rand(0,$data['turbineMpLpMaxPower']-1);
        $data['turbineCondMinPower'] = rand(0,$data['turbineCondMaxPower']-1);
    
        $data['Enter'] = 1;
        
        return $data;        
    }
            
    /**
     * Generates Example Data for Modeler using the Maximum Range
     * @return array() 
     */ 
    public static function baseModelMaximumRange(){
        $maxPressure = 2382;
        $mS = Steam_MeasurementSystem::getInstance();
        
        $yesNo = array(0 => 'Yes', 1 => 'No');
        
        $data['boilerEff'] = rand(COMBUSTION_EFF_MIN,COMBUSTION_EFF_MAX);        
        $data['blowdownRate'] = rand(BLOWDOWN_RATE_MIN,BLOWDOWN_RATE_MAX);
        $data['blowdownFlashLP'] = $yesNo[rand(0,1)];
        $data['superheatTemp'] = rand(0,300);     
        $data['daVentRate'] = rand(DA_VENTRATE_MIN,DA_VENTRATE_MAX);    

        $data['sitePowerImport'] = rand(-10000,10000);
        $data['sitePowerCost'] = 0.05;
        $data['operatingHours'] = rand(1,8760);
        $data['makeupWaterCost'] = .0025;
        $data['makeupWaterTemp'] = rand(40,120);
        $data['fuelUnitCost'] = 5.78;
        
                
        $data['headerCount'] = rand(3, 3);
        $data['highPressure'] = rand(2, $maxPressure);
        $data['mediumPressure'] = rand(1, $data['highPressure']-1);
        $data['lowPressure'] = rand(0, $data['mediumPressure']-1);
                   
        $data['daPressure'] = rand(-14, $data['lowPressure']);
        
        $data['hpSteamUsage'] = rand(0, 10000);
        $data['mpSteamUsage'] = rand(0, 10000);
        $data['lpSteamUsage'] = rand(0, 10000);
        $data['hpCondReturnRate'] = rand(0, 100);
        $data['mpCondReturnRate'] = rand(0, 100);
        $data['lpCondReturnRate'] = rand(0, 100);

        $data['hpCondFlash'] = $yesNo[rand(0,1)];
        $data['mpCondFlash'] = $yesNo[rand(0,1)];
        
        $data['condReturnTemp'] = 150;
        
        $data['hpHeatLossPercent'] = rand(HEATLOST_PERCENT_MIN*10,HEATLOST_PERCENT_MAX*10)/10;   
        $data['mpHeatLossPercent'] = rand(HEATLOST_PERCENT_MIN*10,HEATLOST_PERCENT_MAX*10)/10;   
        $data['lpHeatLossPercent'] = rand(HEATLOST_PERCENT_MIN*10,HEATLOST_PERCENT_MAX*10)/10;   

        $data['desuperHeatHpMp'] = $yesNo[rand(0,1)];
        $data['desuperHeatHpMpTemp'] = rand(300,800);
        $data['desuperHeatMpLp'] = $yesNo[rand(0,1)];
        $data['desuperHeatMpLpTemp'] = rand(300,800);
        
        $bob = array(
            0 => array(1,0,0),
            1 => array(1,1,0),
            2 => array(1,1,1),
            3 => array(1,0,1),
            4 => array(0,0,1),
            5 => array(0,1,1),
            6 => array(0,1,0),
        );
        $turbs = $bob[rand(0,6)];
        
        $data['turbineHpMpOn'] = $turbs[0]; //rand(0,1);
        $data['turbineHpLpOn'] = $turbs[1]; //rand(0,1);
        $data['turbineMpLpOn'] = $turbs[2]; //rand(0,1);
        $data['turbineCondOn'] = rand(0,1);
        
        $data['turbineHpMpIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        $data['turbineHpLpIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        $data['turbineMpLpIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        $data['turbineCondIsoEff'] = rand(ISOEFF_MIN,ISOEFF_MAX);
        
        $data['turbineCondOutletPressure'] = rand(2,14);
        
        $data['turbineHpMpGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        $data['turbineHpLpGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        $data['turbineMpLpGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        $data['turbineCondGenEff'] = rand(GENEFF_MIN,GENEFF_MAX);
        
        $turbineMethods = array(
            0 => 'balanceHeader',
            1 => 'fixedFlow',
            2 => 'fixedPower',
            3 => 'flowRange',
            4 => 'powerRange',
            );
        $data['turbineHpMpMethod'] = $turbineMethods[rand(0,4)];
        $data['turbineHpLpMethod'] = $turbineMethods[rand(0,4)];
        $data['turbineMpLpMethod'] = $turbineMethods[rand(0,4)];
        $turbineMethods = array(
            0 => 'fixedFlow',
            1 => 'fixedPower',
            );
        $data['turbineCondMethod'] = $turbineMethods[rand(0,1)];
        
        $data['turbineHpMpFixedFlow'] = rand(1,9999);
        $data['turbineHpLpFixedFlow'] = rand(1,9999);
        $data['turbineMpLpFixedFlow'] = rand(1,9999);
        $data['turbineCondFixedFlow'] = rand(1,9999);
        
        $data['turbineHpMpMaxFlow'] = rand(1,9999);
        $data['turbineHpLpMaxFlow'] = rand(1,9999);
        $data['turbineMpLpMaxFlow'] = rand(1,9999);
        $data['turbineCondMaxFlow'] = rand(1,9999);
        
        $data['turbineHpMpMinFlow'] = rand(0,$data['turbineHpMpMaxFlow']-1);
        $data['turbineHpLpMinFlow'] = rand(0,$data['turbineHpLpMaxFlow']-1);
        $data['turbineMpLpMinFlow'] = rand(0,$data['turbineMpLpMaxFlow']-1);
        $data['turbineCondMinFlow'] = rand(0,$data['turbineCondMaxFlow']-1);
        
        $data['turbineHpMpFixedPower'] = rand(1,9999);
        $data['turbineHpLpFixedPower'] = rand(1,9999);
        $data['turbineMpLpFixedPower'] = rand(1,9999);
        $data['turbineCondFixedPower'] = rand(1,9999);
        
        $data['turbineHpMpMaxPower'] = rand(1,9999);
        $data['turbineHpLpMaxPower'] = rand(1,9999);
        $data['turbineMpLpMaxPower'] = rand(1,9999);
        $data['turbineCondMaxPower'] = rand(1,9999);
        
        $data['turbineHpMpMinPower'] = rand(0,$data['turbineHpMpMaxPower']-1);
        $data['turbineHpLpMinPower'] = rand(0,$data['turbineHpLpMaxPower']-1);
        $data['turbineMpLpMinPower'] = rand(0,$data['turbineMpLpMaxPower']-1);
        $data['turbineCondMinPower'] = rand(0,$data['turbineCondMaxPower']-1);
    
        $data['Enter'] = 1;
        
        return $data;        
    }
            
    /**
     * Generates Example Data for Modeler
     * @param string $type 
     * @return array() 
     */ 
    public static function randomLimit(){
        $maxPressure = 2382;
        $mS = Steam_MeasurementSystem::getInstance();
        
        $yesNo = array(0 => 'Yes', 1 => 'No');
        
        $range = array(0 => COMBUSTION_EFF_MIN, 1 => COMBUSTION_EFF_MAX);
        $data['boilerEff'] = $range[rand(0,1)];
        $range = array(0 => BLOWDOWN_RATE_MIN, 1 => BLOWDOWN_RATE_MAX);
        $data['blowdownRate'] = $range[rand(0,1)];
        $data['blowdownFlashLP'] = $yesNo[rand(0,1)];
        $range = array(0 => 0, 1 => 300);
        $data['superheatTemp'] = $range[rand(0,1)];
        $range = array(0 => DA_VENTRATE_MIN, 1 => DA_VENTRATE_MAX);
        $data['daVentRate'] = $range[rand(0,1)];

        $data['sitePowerImport'] = rand(-10000,10000);
        $data['sitePowerCost'] = 0.05;
        $data['operatingHours'] = rand(1,8760);
        $data['makeupWaterCost'] = .0025;
        $range = array(0 => 40, 1 => 120);
        $data['makeupWaterTemp'] = $range[rand(0,1)];
        $data['fuelUnitCost'] = 5.78;
        
                
        $data['headerCount'] = rand(3, 3);
        $data['highPressure'] = rand(2, $maxPressure);
        $data['mediumPressure'] = rand(1, $data['highPressure']-1);
        $data['lowPressure'] = rand(0, $data['mediumPressure']-1);
                   
        $data['daPressure'] = rand(-14, $data['lowPressure']);
        
        $data['hpSteamUsage'] = rand(0, 10000);
        $data['mpSteamUsage'] = rand(0, 10000);
        $data['lpSteamUsage'] = rand(0, 10000);
        $data['hpCondReturnRate'] = rand(0, 100);
        $data['mpCondReturnRate'] = rand(0, 100);
        $data['lpCondReturnRate'] = rand(0, 100);

        $data['hpCondFlash'] = $yesNo[rand(0,1)];
        $data['mpCondFlash'] = $yesNo[rand(0,1)];
        
        $data['condReturnTemp'] = 150;
        
        $range = array(0 => HEATLOST_PERCENT_MIN, 1 => HEATLOST_PERCENT_MAX);
        $data['hpHeatLossPercent'] = $range[rand(0,1)];
        $range = array(0 => HEATLOST_PERCENT_MIN, 1 => HEATLOST_PERCENT_MAX);
        $data['mpHeatLossPercent'] = $range[rand(0,1)];
        $range = array(0 => HEATLOST_PERCENT_MIN, 1 => HEATLOST_PERCENT_MAX);
        $data['lpHeatLossPercent'] = $range[rand(0,1)];

        $data['desuperHeatHpMp'] = $yesNo[rand(1,1)];
        $data['desuperHeatHpMpTemp'] = 370;
        $data['desuperHeatMpLp'] = $yesNo[rand(0,1)];
        $data['desuperHeatMpLpTemp'] = 270;
        
        $bob = array(
            0 => array(1,0,0),
            1 => array(1,1,0),
            2 => array(1,1,1),
            3 => array(1,0,1),
            4 => array(0,0,1),
            5 => array(0,1,1),
            6 => array(0,1,0),
        );
        $turbs = $bob[rand(0,6)];
        
        $data['turbineHpMpOn'] = $turbs[0]; //rand(0,1);
        $data['turbineHpLpOn'] = $turbs[1]; //rand(0,1);
        $data['turbineMpLpOn'] = $turbs[2]; //rand(0,1);
        $data['turbineCondOn'] = rand(0,1);
        
        $range = array(0 => ISOEFF_MIN, 1 => ISOEFF_MAX);
        $data['turbineHpMpIsoEff'] = $range[rand(0,1)];
        $range = array(0 => ISOEFF_MIN, 1 => ISOEFF_MAX);
        $data['turbineHpLpIsoEff'] = $range[rand(0,1)];
        $range = array(0 => ISOEFF_MIN, 1 => ISOEFF_MAX);
        $data['turbineMpLpIsoEff'] = $range[rand(0,1)];
        $range = array(0 => ISOEFF_MIN, 1 => ISOEFF_MAX);
        $data['turbineCondIsoEff'] = $range[rand(0,1)];
        
        $data['turbineCondOutletPressure'] = rand(2,14);
        
        $range = array(0 => GENEFF_MIN, 1 => GENEFF_MAX);
        $data['turbineHpMpGenEff'] = $range[rand(0,1)];
        $range = array(0 => GENEFF_MIN, 1 => GENEFF_MAX);
        $data['turbineHpLpGenEff'] = $range[rand(0,1)];
        $range = array(0 => GENEFF_MIN, 1 => GENEFF_MAX);
        $data['turbineMpLpGenEff'] = $range[rand(0,1)];
        $range = array(0 => GENEFF_MIN, 1 => GENEFF_MAX);
        $data['turbineCondGenEff'] = $range[rand(0,1)];
        
        $turbineMethods = array(
            0 => 'balanceHeader',
            1 => 'fixedFlow',
            2 => 'fixedPower',
            3 => 'flowRange',
            4 => 'powerRange',
            );
        $data['turbineHpMpMethod'] = $turbineMethods[rand(0,4)];
        $data['turbineHpLpMethod'] = $turbineMethods[rand(0,4)];
        $data['turbineMpLpMethod'] = $turbineMethods[rand(0,4)];
        $turbineMethods = array(
            0 => 'fixedFlow',
            1 => 'fixedPower',
            );
        $data['turbineCondMethod'] = $turbineMethods[rand(0,1)];
        
        $data['turbineHpMpFixedFlow'] = rand(1,9999);
        $data['turbineHpLpFixedFlow'] = rand(1,9999);
        $data['turbineMpLpFixedFlow'] = rand(1,9999);
        $data['turbineCondFixedFlow'] = rand(1,9999);
        
        $data['turbineHpMpMaxFlow'] = rand(1,9999);
        $data['turbineHpLpMaxFlow'] = rand(1,9999);
        $data['turbineMpLpMaxFlow'] = rand(1,9999);
        $data['turbineCondMaxFlow'] = rand(1,9999);
        
        $data['turbineHpMpMinFlow'] = rand(0,$data['turbineHpMpMaxFlow']-1);
        $data['turbineHpLpMinFlow'] = rand(0,$data['turbineHpLpMaxFlow']-1);
        $data['turbineMpLpMinFlow'] = rand(0,$data['turbineMpLpMaxFlow']-1);
        $data['turbineCondMinFlow'] = rand(0,$data['turbineCondMaxFlow']-1);
        
        $data['turbineHpMpFixedPower'] = rand(1,9999);
        $data['turbineHpLpFixedPower'] = rand(1,9999);
        $data['turbineMpLpFixedPower'] = rand(1,9999);
        $data['turbineCondFixedPower'] = rand(1,9999);
        
        $data['turbineHpMpMaxPower'] = rand(1,9999);
        $data['turbineHpLpMaxPower'] = rand(1,9999);
        $data['turbineMpLpMaxPower'] = rand(1,9999);
        $data['turbineCondMaxPower'] = rand(1,9999);
        
        $data['turbineHpMpMinPower'] = rand(0,$data['turbineHpMpMaxPower']-1);
        $data['turbineHpLpMinPower'] = rand(0,$data['turbineHpLpMaxPower']-1);
        $data['turbineMpLpMinPower'] = rand(0,$data['turbineMpLpMaxPower']-1);
        $data['turbineCondMinPower'] = rand(0,$data['turbineCondMaxPower']-1);
    
        $data['Enter'] = 1;
        
        return $data;        
    }
    
    /**
     * SSAT halfSteam Adjustment
     * @return array
     */
    static public function halfSteam() {
        $modelData = array(    
            'hpSteamUsage' => 25,
            'mpSteamUsage' => 50,
            'lpSteamUsage' => 100,
            );
        return $modelData;        
    }
    
    /**
     * SSAT3 Base Model Data
     * @return array
     */
    static public function SSAT3() {
        $modelData = array();
        return $modelData;        
    }
    
    /**
     * SSAT2 Base Model Data
     * @return array
     */
    static public function SSAT2() {
        $modelData = array(
            'headerCount' => 2,
            'highPressure' => 600,
            'mediumPressure' => 20,
            'lowPressure' => 20,
            'hpSteamUsage' => 50,
            'mpSteamUsage' => 0,
            'lpSteamUsage' => 200,
            'condReturnTemp' => 150,
            'heatLossMethod' => 'percent',
            'mpHeatLossFlow' => 0,
            'mpHeatLossPercent' => 0,
            'turbineHpMpOn' => 0,
            'turbineHpLpOn' => 1,
            'turbineCondOn' => 0,
            'turbineHpLpIsoEff' => 65,
            'turbineCondIsoEff' => 65,
            'turbineHpLpMethod' => 'balanceHeader',
            'turbineCondMethod' => 'fixedFlow',
            'turbineCondFixedFlow' => 100,
            'calcMarginalCosts' => 'No',
            );
        return $modelData;        
    }
    
    /**
     * SSAT1 Base Model Data
     * @return array
     */
    static public function SSAT1() {
        $modelData = array(
            'headerCount' => 1,
            'highPressure' => 150,
            'mediumPressure' => 150,
            'lowPressure' => 150,
            'hpSteamUsage' => 50,
            'mpSteamUsage' => 0,
            'lpSteamUsage' => 0,
            'boilerTemp' => 465.9,
            'condReturnTemp' => 150,
            'heatLossMethod' => 'percent',
            'mpHeatLossFlow' => 0,
            'mpHeatLossPercent' => 0,
            'turbineHpMpOn' => 0,
            'turbineHpLpOn' => 0,
            'turbineCondOn' => 0,
            'calcMarginalCosts' => 'No',
            );
        return $modelData;        
    }
}