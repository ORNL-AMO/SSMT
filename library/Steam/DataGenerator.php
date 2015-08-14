<?php
/**
 * Steam DataGenerator
 * 
 * Contains all Example Data Generators for the Property and Individual Equipment Calculators
 *
 * @package    Steam
 * @subpackage Steam
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Contains all Example Data Generators for the Property and Individual Equipment Calculators
 * 
 * @package    Steam
 * @subpackage Steam
 */
class Steam_DataGenerator{        
    
    /**
     * Returns an Array of Examples for a given $form
     * @param string $form
     * @return array|boolean
     */
    public static function examples($form){
        $examples = array(   
            'satProps' => array(
                'Pressure' => "Random Pressure",
                'Temperature' => "Random Temperature",
            ),                   
            'steamProps' => array(
                    'Any' => "Random",
                    'Liquid' => "Sub-Cooled Liquid",
                    'SatLiquid' => "Saturated Liquid",
                    'Mixture' => "Saturated Mixture",
                    'SatGas' => "Saturated Gas",
                    'Gas' => "Super-Heated Gas",
            ),       
            'boiler' => array(
                'satSteam' => "Saturated Steam",
                'superSteam' => "Super-heated Steam",
            ),        
            'heatloss' => array(
                'Any' => "Any",
                'Liquid' => "Liquid",
                'Gas' => "Gas",
            ),            
            'flashtank' => array(
                'SatLiquid' => "Saturated Liquid",
                'SatMixture' => "Saturated Mixture",
            ),           
            'prv' => array(
                'randomNoD' => "Random - No Desuperheating",
                'randomD' => "Random - With Desuperheating",
            ),         
            'header' => array(
                'Any' => "Random Inlets",
            ),
            'deaerator' => array(
                'random' => "Random",
            ),
            'turbine' => array(
                'Outlet' => "Solve for Outlet Properties",
                'IsoEff' => "Solve for Isentropic Efficiency",
            ),
            
        );
        if (isset($examples[$form]) ) return $examples[$form];
        return false;
    }
    
    /**
     * Generates a random pressure in given range
     * @param float $minPressure $units
     * @param float $maxPressure $units
     * @param string $units pressure
     * @return float Pressure localUnits
     */
    public static function randomPressure($minPressure, $maxPressure, $units){
        $mS = Steam_MeasurementSystem::getInstance();
        $randomPressure = 
                ( $mS->standardize($maxPressure,'pressure',$units) - $mS->standardize($minPressure,'pressure',$units) )
                    *rand(1,1000)/1000 + $mS->standardize($minPressure,'pressure',$units);
        return $mS->rawPressure($randomPressure);
    }
    
    /**
     * Generates a random Massfow in given range
     * @param float $minFlow $units
     * @param float $maxFlow $units
     * @param string $units massflow
     * @return float Massflow localUnits
     */
    public static function randomMassFlow($minFlow, $maxFlow, $units){
        $mS = Steam_MeasurementSystem::getInstance();
        $randomMassflow = 
                ( $mS->standardize($maxFlow,'massflow',$units) - $mS->standardize($minFlow,'massflow',$units) )
                    *rand(1,1000)/1000 + $mS->standardize($minFlow,'massflow',$units) ;
        return $mS->rawMassflow($randomMassflow);
    }
    
    /**
     * Generates a random Temperature for liquid water at $pressure
     * @param float $pressure localUnits
     * @return float Temperature localUnits
     */
    public static function liquidTemp($pressure){       
        $mS = Steam_MeasurementSystem::getInstance();
        $iapws = new Steam_IAPWS();
        $maxTemperature = $iapws->saturatedTemperature( $mS->standardize($pressure,'pressure') )*.99;
        $range = $maxTemperature-TEMPERATURE_MINIMUM;
        $temperature = rand(100,1000)/1000*$range + TEMPERATURE_MINIMUM ;
        return $mS->rawTemperature($temperature);
    }
    
    /**
     * Generates a random Temperature for superheated steam at $pressure
     * @param float $pressure localUnits
     * @return float Temperature localUnits
     */
    public static function gasTemp($pressure){        
        $mS = Steam_MeasurementSystem::getInstance();
        $iapws = new Steam_IAPWS();
        $minTemperature = $iapws->saturatedTemperature( $mS->standardize($pressure,'pressure') )*1.01;
        $range = (TEMPERATURE_MAXIMUM-$minTemperature);
        $temperature = rand(1,900)/1000*$range +$minTemperature;
        return $mS->rawTemperature($temperature);  
    }
    
    /**
     * Generates a random temperature between the entered points
     * @param float $minTemperature MPa
     * @param float $maxTemperature MPa
     * @return float
     */
    public static function anyTemp($minTemperature=273.15, $maxTemperature=623.15){
        $mS = Steam_MeasurementSystem::getInstance();
        $temperature = ($maxTemperature-$minTemperature)*rand(10,990)/1000 +$minTemperature;
        return $mS->rawTemperature($temperature);
    }
           
    /**
     * Generates Example Data for Boiler Form
     * @param string $type 
     * @return array() 
     */ 
    public static function boiler($type){
        $mS = Steam_MeasurementSystem::getInstance();
        
        $data['daPressure'] = self::randomPressure(0, 40, 'psig');
        $data['combustEff'] = rand(600,950)/10;        
        $data['blowdownRate'] = rand(10,100)/10;
               
        $data['SteamPressure'] = self::randomPressure( $mS->standardize($data['daPressure'],'pressure'), $mS->standardize(1000,'pressure','psig'), 'MPa');
        switch ($type) {
            case 'satSteam':
                $data['SteamSecondParameter'] = 'Quality';
                $data['SteamQuality'] = 1;                                                
                break;
            default:
            case 'superSteam':
                $data['SteamSecondParameter'] = 'Temperature';
                $data['SteamTemperature'] = self::gasTemp($data['SteamPressure']);
                break;
        } 
        $data['massFlow'] = self::randomMassFlow(10,100,'klb/hr');
        
        $data['Enter'] = 1;
        
        return $data;        
    }
    
    /**
     * Generates Example Data for Heatloss Form
     * @param string $type 
     * @return array() 
     */
    public static function heatloss($type){                
        $data['InletPressure'] = self::randomPressure(0, 1000, 'psig');
        $data['InletSecondParameter'] = 'Temperature';            
        switch ($type) {
            case 'Liquid':
                $data['InletTemperature'] = self::liquidTemp($data['InletPressure']);
                break;
            case 'Gas':
                $data['InletTemperature'] = self::gasTemp($data['InletPressure']);            
                break;
            case 'Any':        
                $data['InletTemperature'] = self::anyTemp();
            default:
                break;
        }
        $data['massFlow'] = self::randomMassFlow(10,100,'klb/hr');               
        $data['heatLossPercent'] = round( rand(10, 1000)/100 ,2);        
        
        $data['Enter'] = 1;
        return $data;
    }
    
    /**
     * Generates Example Data for Flash Tank Form
     * @param string $type 
     * @return array() 
     */
    public static function flashTank($type){   
        $mS = Steam_MeasurementSystem::getInstance();
        
        $data['InletPressure'] = self::randomPressure(0, 1000, 'psig');
        $data['InletSecondParameter'] = 'Quality';
        switch ($type) {
            case 'SatLiquid':                
                $data['InletQuality'] = 0;
                break;
            default:
            case 'SatMixture':
                $data['InletQuality'] = rand(1,99)/100;
                break;
        }
        $data['massFlow'] = self::randomMassFlow(10,100,'klb/hr');   
        $data['tankPressure'] = self::randomPressure( $mS->standardize(0,'pressure','psig'), $mS->standardize($data['InletPressure'],'pressure')*.95, 'MPa');      
        
        $data['Enter'] = 1;
        return $data;
    }
        
    /**
     * Generates Example Data for PRV Form
     * @param string $type 
     * @return array() 
     */
    public static function prv($type){
        $mS = Steam_MeasurementSystem::getInstance();
        
        $data['InletPressure'] = self::randomPressure(0, 1000, 'psig');
        $data['InletSecondParameter'] = 'Temperature';
        $data['InletTemperature'] = self::gasTemp($data['InletPressure']);
        $data['massFlow'] = self::randomMassFlow(10,100,'klb/hr'); 
        $data['outletPressure'] = self::randomPressure( $mS->standardize(0,'pressure','psig'), $mS->standardize($data['InletPressure'],'pressure')*.95, 'MPa');  
        
        switch ($type) {
            case 'randomNoD':
                $data['desuperheating'] = 'No';
                break;
            case 'randomD':
            default:
                $iapws = new Steam_IAPWS();  
                $data['desuperheating'] = 'Yes';              
                $data['FeedwaterPressure'] = self::randomPressure(0, 100, 'psig');
                $data['FeedwaterSecondParameter'] = 'Quality';
                $data['FeedwaterQuality'] = 0;
                $feedwaterTemperature = $iapws->saturatedTemperature($mS->standardize($data['FeedwaterPressure'], 'pressure'));
               
                $data['desuperTemp'] = $mS->rawTemperature( 
                        ($mS->standardize($data['InletTemperature'],'temperature')-$feedwaterTemperature)*rand(20,80)/100+$feedwaterTemperature
                        );
                break;
        }        
        $data['Enter'] = 1;
        
        return $data;        
    }    
        
    /**
     * Generates Example Data for Header Form
     * @param string $type 
     * @return array() 
     */
    public static function header($type){
        $mS = Steam_MeasurementSystem::getInstance();
        
        $data['headerCount'] = rand(2,9);
        
        $data['headerPressure'] = self::randomPressure(0, 400, 'psig');
        for($x=1;$x<=$data['headerCount'];$x++){         
            $data['Inlet'.$x.'Pressure'] = self::randomPressure( $mS->standardize($data['headerPressure'],'pressure'), $mS->standardize(600,'pressure','psig'), 'MPa');
            $data['Inlet'.$x.'SecondParameter'] = 'Temperature';
            $data['Inlet'.$x.'Temperature'] = self::anyTemp();
            $data['massFlow'.$x] = self::randomPressure(0, 100, 'psig');
        }    
        $data['Enter'] = 1;
        return $data;        
    }
        
    /**
     * Generates Example Data for Deaerator Form
     * @param string $type 
     * @return array() 
     */
    public static function deaerator($type){ 
        //Only 1 Type "Random" for Deaerator
        $mS = Steam_MeasurementSystem::getInstance();
                
        $data['daPressure'] = self::randomPressure(0,40,'psig');
        $data['ventRate'] = round(rand(1,10)/10,1);
        $data['feedwaterFlow'] =  self::randomMassFlow(10,100,'klb/hr');
        
        $data['SteamPressure'] = self::randomPressure( $mS->standardize($data['daPressure'],'pressure'), $mS->standardize(150,'pressure','psig'), 'MPa');
        $data['SteamSecondParameter'] = 'Temperature';
        $data['SteamTemperature'] = self::gasTemp($data['SteamPressure']);
        
        $data['WaterPressure'] = self::randomPressure(0,10,'psig');      
        $data['WaterSecondParameter'] = 'Temperature';
        $data['WaterTemperature'] = self::liquidTemp($data['WaterPressure']);
                
        $data['Enter'] = 1;
        
        return $data;                
    } 
        
    /**
     * Generates Example Data for Steam Turbine Form
     * @param string $type 
     * @return array() 
     */
    public static function steamTurbine($type){    
        $mS = Steam_MeasurementSystem::getInstance();    
        $data['InletPressure'] = self::randomPressure(10, 1000, 'psig');
        $data['InletSecondParameter'] = 'Temperature';
        $data['InletTemperature'] = self::gasTemp($data['InletPressure']); 
        $data['massFlow'] =  self::randomMassFlow(10,100,'klb/hr');
        $data['turbineMethod'] =  'massFlow';
        $data['genEff'] = round(rand(800,990)/10,1);
        $data['OutletPressure'] = self::randomPressure( $mS->standardize(0,'pressure','psig'), $mS->standardize($data['InletPressure'],'pressure')*.95, 'MPa');   
        $data['isentropicEff'] = round(rand(400,900)/10,1);    
        
        switch ($type) {
            case 'IsoEff':
                $data['solveFor'] =  'isoEff';
                $turbine = new Steam_Equipment_Turbine(array(
                        'inletSteam' => new Steam_Object(array(
                                'pressure' => $mS->standardize($data['InletPressure'], 'pressure'),
                                'temperature' => $mS->standardize($data['InletTemperature'], 'temperature'),
                                )),
                        'outletPressure' => $mS->standardize($data['OutletPressure'],'pressure'),
                        'isentropicEff' => $data['isentropicEff']/100,
                        'generatorEff' => $data['genEff']/100,
                        ));
                $data['OutletSecondParameter'] = 'Temperature'; 
                $data['OutletTemperature'] = $mS->rawTemperature($turbine->outletSteam->temperature);
                break;
            case 'Outlet':
            default:
                $data['solveFor'] =  'outlet';                          
                break;
        }        
        
        $data['Enter'] = 1;
        
        return $data;                
    }
                
    /**
     * Generates Example Data for Steam Properties Form
     * @param string $type 
     * @return array() 
     */
    public static function steamCalc($type = 'Any'){
        $mS = Steam_MeasurementSystem::getInstance();  
        
        $data['Pressure'] = self::randomPressure(0, 2000, 'psig');
                        
        switch($type){
            case 'Liquid':                        
                $data['SecondParameter'] = 'Temperature';
                $data['Temperature'] = self::liquidTemp($data['Pressure']);
                break;
            case 'Gas': 
                $data['SecondParameter'] = 'Temperature';
                $data['Temperature'] = self::gasTemp($data['Pressure']);           
                break;
            case 'SatLiquid':
                $data['SecondParameter'] = 'Quality';
                $data['Quality'] = 0;
                break;
            case 'SatGas':
                $data['SecondParameter'] = 'Quality';
                $data['Quality'] = 1;
                break;
            case 'Mixture':
                $data['SecondParameter'] = 'Quality';
                $data['Quality'] = rand(1, 99)/100;
                break;
            default:
            case 'Any':
                $steamMin = new Steam_Object(array(
                    'pressure' => $mS->standardize($data['Pressure'], 'pressure'),
                    'temperature' => TEMPERATURE_MINIMUM+10,
                    ));
                $steamMax = new Steam_Object(array(
                    'pressure' => $mS->standardize($data['Pressure'], 'pressure'),
                    'temperature' => TEMPERATURE_MAXIMUM-10,
                    ));
                $data['SecondParameter'] = 'SpecificEnthalpy';
                $specificEnthalpy = ($steamMax->specificEnthalpy-$steamMin->specificEnthalpy)*rand(1,999)/1000+$steamMin->specificEnthalpy;
                $data['SpecificEnthalpy'] = $mS->rawSpecificEnthalpy($specificEnthalpy);
                break;
            
        }
        
        $data['Enter'] = 1;

        return $data;
    }
                
    /**
     * Generates Example Data for Saturated Properties Form
     * @param string $type 
     * @return array() 
     */
    public static function satCalc($type = 'Pressure'){
        $mS = Steam_MeasurementSystem::getInstance();                 
        
        switch($type){
            case 'Temperature': 
                $data['using'] = 'temperature';                  
                $data['Temperature'] = self::anyTemp(TEMPERATURE_MINIMUM, TEMPERATURE_CRITICAL_POINT);        
                break;
            default:
            case 'Pressure':      
                $data['using'] = 'pressure';                  
                $data['Pressure'] = self::randomPressure(PRESSURE_MINIMUM, PRESSURE_CRITICAL_POINT, 'MPa');
                break;            
        }
        
        $data['Enter'] = 1;

        return $data;
    }
}