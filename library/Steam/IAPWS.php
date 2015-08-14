<?php
/**
 * Steam Calculators 
 *
 * Location of IAPWS support functions
 * 
 * @package    Steam
 * @subpackage Steam_IAPWS
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * include needed classes
 */
require_once('IAPWS/Core.php');

/**
 * Extends IAPWS Core Equations
 * 
 * @package    Steam
 * @subpackage Steam_IAPWS
 */
class Steam_IAPWS extends Steam_IAPWS_Core{

    /**
     * Maximum Pressure of Water MPa
     */ 
    const PRESSURE_MIN = 0.01;
    
    /**
     * Maximum Temperature of Water K
     */ 
    const TEMPERATURE_MIN = 273.15;      
    
    /**
     * Pressure of Water where ALL regions meet MPa
     */
    const PRESSURE_Tp = 16.5291643;
    
    /**
     * Temperature of Water where ALL regions meet K
     */
    const TEMPERATURE_Tp = 623.15;
    
    /**
     * Critical Pressure of Water MPa
     */
    const PRESSURE_CRIT = 22.064;
    
    /**
     * Critical Temperature of Water K
     */
    const TEMPERATURE_CRIT = 647.096;
    
    /**
     * Maximum Pressure of Water MPa
     */
    const PRESSURE_MAX = 100;
    
    /**
     * Maximum Temperature of Water MPa
     */
    const TEMPERATURE_MAX = 1073.15;
    
    /**
     * Maximum Temperature of Water for Region 3 MPa
     */
    const TEMPERATURE_REGION3_MAX = 863.15;
    
    /**
     * Returns Steam Properties based on $pressure and $temperature
     * @param double $pressure MPa
     * @param double $temperature K
     * @return array() SteamProperties
     */
    function waterPropertiesPT($pressure, $temperature){
	$region=$this->regionSelect($pressure, $temperature);
	switch($region){
            case 1:
                $properties = $this->region1($pressure, $temperature);
                $properties['phase'] = "Liquid";
            break;
            case 2:
                $properties = $this->region2($pressure, $temperature);
                $properties['phase'] = "Gas";
            break;
            case 3:
                $properties = $this->region3($pressure, $temperature);
                $properties['phase'] = "Liquid";
            break;
	}
        $properties['region'] = $region;
        $properties['temperature'] = $temperature;
        $properties['quality'] =  NULL;
	return $properties;
    }
    
    /**
     * Returns the IAPWS region based on $pressure and $temperature
     * @param float $pressure MPa
     * @param float $temperature K
     * @return int Region
     */
    function regionSelect($pressure, $temperature){
        $region=0;
        //Determine Boundary
        if ($temperature>=self::TEMPERATURE_Tp){
            $boundaryPressure = $this->boundaryByTemperatureRegion3to2($temperature);
        }else{
            $boundaryPressure = $this->saturatedPressure($temperature);
        }
               
	if ($temperature>=self::TEMPERATURE_MIN and $temperature<=self::TEMPERATURE_Tp){
            if ($pressure<=self::PRESSURE_MAX and $pressure>=$boundaryPressure){
                $region=1; //Liquid
            }
            if ($pressure>0 and $pressure<=$boundaryPressure){
                $region=2; //Gas
            }
	}
	if ($temperature>=self::TEMPERATURE_Tp and $temperature<=self::TEMPERATURE_REGION3_MAX){
            if ($pressure>0 and $pressure<=$boundaryPressure){
                $region=2; //Gas
            }
            if ($pressure<=self::PRESSURE_MAX and $pressure>$boundaryPressure){
                $region=3; //Liquid
            }
	}        
	if ($temperature>self::TEMPERATURE_REGION3_MAX and $temperature<=self::TEMPERATURE_MAX) $region=2; //Gas
	return $region;
    }
    
    /**
     * Determines the Density in Region 3 based on $pressure and $temperature
     * @param float $pressure MPa
     * @param float $temperature K
     * @return float Density kg/m3
     */
    function region3($pressure, $temperature){
                
        $boundary13Properties = $this->region1($pressure, self::TEMPERATURE_Tp);        
        $region3propA = $this->region3Density( $densityA = $boundary13Properties['density'], $temperature);
        $testPressureA = $region3propA['pressure'];
        
        $boundary23Properties = $this->region2($pressure, $this->boundaryByPressureRegion3to2($pressure));        
        $region3propB = $this->region3Density( $densityB = $boundary23Properties['density'], $temperature);
        $testPressureB = $region3propB['pressure'];
        
        //Base Goal Seek
        for($x=1;$x<5;$x++){
            $densityNew = ($densityA+$densityB)/2;
            $region3propNew = $this->region3Density($densityNew, $temperature);
            $pressureNew = $region3propNew['pressure'];
            if ($pressure>$pressureNew){
                $densityB = $densityNew;
                $testPressureB = $pressureNew;
            }else{                    
                $densityA = $densityNew;
                $testPressureA = $pressureNew;
            }
        }
        
        //Uses Linear Interpolation
        $counter = 0;
	while(abs($pressureNew-$pressure)>1e-10 and $counter++<50 and $testPressureA<>$testPressureB ){      
            $densityNew = $pressure * ($densityA-$densityB)/($testPressureA-$testPressureB) + $densityA - $testPressureA * ($densityA-$densityB) / ($testPressureA-$testPressureB); 
            $region3propNew = $this->region3Density($densityNew, $temperature);
            $pressureNew=$region3propNew['pressure'];
                $densityB = $densityA;
                $densityA = $densityNew;
                $testPressureB = $testPressureA;
                $testPressureA = $pressureNew;
	}        
	return $region3propNew;
    }

    /**
     * Returns the saturated pressure based on temperature
     * @param double $temperature K
     * @return double pressure MPa
     */   
    function saturatedPressure($temperature){
        if($temperature<=self::TEMPERATURE_CRIT) return $this->region4($temperature);
    }

    /**
     * Returns the saturated temperature based on pressure
     * @param double $pressure MPa
     * @return double temperature K
     */
    public function saturatedTemperature($pressure){
        if($pressure<=self::PRESSURE_CRIT) return $this->backwardRegion4($pressure);
    }
    
    /**
     * Provides saturated liquid and gas properties for a given pressure
     * @param double $pressure MPa
     * @return array() 
     */
    public function saturatedPropertiesByPressure($pressure){
        $temperature = $this->saturatedTemperature($pressure);
        $properties['gas'] = $this->region2($pressure, $temperature);
        $properties['gas']['region'] = 2;
        $properties['gas']['quality'] = 1;
	if ($temperature>=self::TEMPERATURE_MIN and $temperature<=self::TEMPERATURE_Tp){
            $properties['liquid'] = $this->region1($pressure, $temperature);
            $properties['liquid']['quality'] = 0;
            $properties['liquid']['region'] = 1;
	}
	if ($temperature>self::TEMPERATURE_Tp and $temperature<=self::TEMPERATURE_CRIT){
            $properties['liquid'] = $this->region3($pressure, $temperature);
            $properties['liquid']['quality'] = 0;
            $properties['liquid']['region'] = 3;
        }     
        $properties['temperature'] = $temperature;
        $properties['pressure'] = $pressure;    
        $properties['gas']['temperature'] = $temperature;
        $properties['gas']['pressure'] = $pressure;
        $properties['liquid']['temperature'] = $temperature;
        $properties['liquid']['pressure'] = $pressure;      
        $properties['region'] = $properties['liquid']['region'] .'&'. $properties['gas']['region'];        
	return $properties;
    }    
               
    /**
     * Provides saturated liquid and gas properties for a given temperature
     * @param double $temperature K
     * @return array() 
     */
    public function saturatedPropertiesByTemperature($temperature){
        return $this->saturatedPropertiesByPressure( $this->saturatedPressure($temperature) );        
    }
           
    /**
     * Returns Steam Properties based on $pressure and $specificEnthalpy
     * @param double $pressure MPa
     * @param double $specificEnthalpy kJ/kg
     * @return array() SteamProperties
     */
    public function waterPropertiesPH($pressure, $specificEnthalpy){
        if ($pressure<self::PRESSURE_CRIT){
            $pressureSatProps = $this->saturatedPropertiesByPressure($pressure);
            $specificEnthalpyLimit = $pressureSatProps['liquid']['specificEnthalpy'];
        }            
        if ($pressure>self::PRESSURE_Tp){
            $boundaryTemperature = $this->boundaryByPressureRegion3to2($pressure);
            $boundaryProps = $this->region2($pressure, $boundaryTemperature);
            $specificEnthalpyLimit = $boundaryProps['specificEnthalpy'];            
        }
        if ( $specificEnthalpy < $specificEnthalpyLimit ){
            if ($pressure>self::PRESSURE_Tp) $region13boundary = $this->waterPropertiesPT($pressure, self::TEMPERATURE_Tp);
            if ($pressure<=self::PRESSURE_Tp or $specificEnthalpy<$region13boundary['specificEnthalpy'] ){
                $temperature = $this->backwardPHregion1Exact($pressure, $specificEnthalpy);
                $testProps = $this->region1($pressure, $temperature);
                $testProps['region'] = '1';
            }else{
                $temperature = $this->backwardPHregion3($pressure, $specificEnthalpy);
                $testProps = $this->region3($pressure, $temperature);
                $testProps['region'] = 3;
            }
            return $testProps;        
        }
                
        if ($pressure<self::PRESSURE_CRIT and $specificEnthalpy>=$pressureSatProps['liquid']['specificEnthalpy'] and $specificEnthalpy<=$pressureSatProps['gas']['specificEnthalpy']){
            $quality = ($specificEnthalpy-$pressureSatProps['liquid']['specificEnthalpy'])
                /($pressureSatProps['gas']['specificEnthalpy']-$pressureSatProps['liquid']['specificEnthalpy']);
            $testProps =  array(
                'temperature' => $pressureSatProps['gas']['temperature'],
                'pressure' => $pressure,
                'specificEnthalpy' => $specificEnthalpy,
                'specificEntropy' => ($pressureSatProps['gas']['specificEntropy']-$pressureSatProps['liquid']['specificEntropy'])*$quality+$pressureSatProps['liquid']['specificEntropy'],
                'quality' => $quality,
                'specificVolume' => ($pressureSatProps['gas']['specificVolume']-$pressureSatProps['liquid']['specificVolume'])*$quality+$pressureSatProps['liquid']['specificVolume'],
                'region' => 4,
                );
            return $testProps;
        }
        
        if ($pressure<=4){
            $temperature = $this->backwardPHregion2aExact($pressure, $specificEnthalpy);
            $region = '2a';
        }else{
            $constants = array(
                1 => 0.90584278514723E+3,
                2 => -0.67955786399241,
                3 => 0.12809002730136E-3,
                );
            $pressureLine = $constants[1]
                + $constants[2] * $specificEnthalpy
                + $constants[3] * pow($specificEnthalpy,2);
            if ($pressureLine>$pressure){
                $temperature = $this->backwardPHregion2bExact($pressure, $specificEnthalpy);
                $region = '2b';
            }else{
                $temperature = $this->backwardPHregion2cExact($pressure, $specificEnthalpy);                   
                $region = '2c';
            }
        }
        $testProps = $this->region2($pressure, $temperature);
        $testProps['region'] = $region;
        return $testProps;
     
    }
    
    /**
     * Returns a more accurate Temperature than backwardPHregion1
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K 
     */
    public function backwardPHregion1Exact($pressure, $specificEnthalpy){
        return $this->backwardExact('region1', 'specificEnthalpy', 'backwardPHregion1', $pressure, $specificEnthalpy);
    }
    
    /**
     * Returns a more accurate Temperature than backwardPHregion2a
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K 
     */
    public function backwardPHregion2aExact($pressure, $specificEnthalpy){
        return $this->backwardExact('region2', 'specificEnthalpy', 'backwardPHregion2a', $pressure, $specificEnthalpy);
    }
    
    /**
     * Returns a more accurate Temperature than backwardPHregion2b
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K 
     */
    public function backwardPHregion2bExact($pressure, $specificEnthalpy){
        return $this->backwardExact('region2', 'specificEnthalpy', 'backwardPHregion2b', $pressure, $specificEnthalpy);
    }
    
    /**
     * Returns a more accurate Temperature than backwardPHregion2c
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K 
     */
    public function backwardPHregion2cExact($pressure, $specificEnthalpy){
        return $this->backwardExact('region2', 'specificEnthalpy', 'backwardPHregion2c', $pressure, $specificEnthalpy);
    }
    
    /**
     * Uses linear interpolation to goal seek Region3 using pressure and enthalpy
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K 
     */
    public function backwardPHregion3($pressure, $specificEnthalpy){
        return $this->backwardRegion3Exact($pressure, $specificEnthalpy, 'specificEnthalpy');
    }
    
    /**
     * Returns Steam Properties based on $pressure and $specificEntropy
     * @param double $pressure MPa
     * @param double $specificEntropy kJ/kg/K
     * @return array() SteamProperties
     */
    public function waterPropertiesPS($pressure, $specificEntropy){
        if ($pressure<self::PRESSURE_CRIT){
            $pressureSatProps = $this->saturatedPropertiesByPressure($pressure);
            $specificEntropyLimit = $pressureSatProps['liquid']['specificEntropy'];
        }
            
        if ($pressure>self::PRESSURE_Tp){
            $boundaryTemperature = $this->boundaryByPressureRegion3to2($pressure);
            $boundaryProps = $this->region2($pressure, $boundaryTemperature);
            $specificEntropyLimit = $boundaryProps['specificEntropy'];            
        }
        if ( $specificEntropy < $specificEntropyLimit ){
            if ($pressure>self::PRESSURE_Tp) $region13boundary = $this->waterPropertiesPT($pressure, self::TEMPERATURE_Tp);
            if ($pressure<=self::PRESSURE_Tp or $specificEntropy<$region13boundary['specificEntropy'] ){
                $temperature = $this->backwardPSregion1Exact($pressure, $specificEntropy);
                $testProps = $this->region1($pressure, $temperature);
                $testProps['region'] = '1';
            }else{
                $temperature = $this->backwardPSregion3($pressure, $specificEntropy);
                $testProps = $this->region3($pressure, $temperature);
                $testProps['region'] = 3;
            }
            return $testProps;        
        }
        
        if ($pressure<self::PRESSURE_CRIT and $specificEntropy>=$pressureSatProps['liquid']['specificEntropy'] and $specificEntropy<=$pressureSatProps['gas']['specificEntropy']){
            $quality = ($specificEntropy-$pressureSatProps['liquid']['specificEntropy'])
            /($pressureSatProps['gas']['specificEntropy']-$pressureSatProps['liquid']['specificEntropy']);

            $testProps = array(
                'temperature' => $pressureSatProps['gas']['temperature'],
                'pressure' => $pressure,
                'specificEntropy' => $specificEntropy,
                'specificEnthalpy' => ($pressureSatProps['gas']['specificEnthalpy']-$pressureSatProps['liquid']['specificEnthalpy'])*$quality+$pressureSatProps['liquid']['specificEnthalpy'],
                'quality' => $quality,
                'specificVolume' => ($pressureSatProps['gas']['specificVolume']-$pressureSatProps['liquid']['specificVolume'])*$quality+$pressureSatProps['liquid']['specificVolume'],
                'region' => 4,
                );
            return $testProps;            
        }
        
        if ($pressure<=4){
            $temperature = $this->backwardPSregion2aExact($pressure, $specificEntropy);
            $region = '2a';
        }else{
            if ($specificEntropy>=5.85){
                $temperature = $this->backwardPSregion2bExact($pressure, $specificEntropy);
                $region = '2b';
            }else{
                $temperature = $this->backwardPSregion2cExact($pressure, $specificEntropy);
                $region = '2c';
            }
        }
        $testProps = $this->region2($pressure, $temperature);
        $testProps['region'] = $region;
        return $testProps;
    }
    
    /**
     * Returns a more accurate Temperature than backwardPSregion1
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    public function backwardPSregion1Exact($pressure, $specificEntropy){
        return $this->backwardExact('region1', 'specificEntropy', 'backwardPSregion1', $pressure, $specificEntropy);       
    }
    
    /**
     * Returns a more accurate Temperature than backwardPSregion2a
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    public function backwardPSregion2aExact($pressure, $specificEntropy){
        return $this->backwardExact('region2', 'specificEntropy', 'backwardPSregion2a', $pressure, $specificEntropy);       
    }
    
    /**
     * Returns a more accurate Temperature than backwardPSregion2b
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    public function backwardPSregion2bExact($pressure, $specificEntropy){
        return $this->backwardExact('region2', 'specificEntropy', 'backwardPSregion2b', $pressure, $specificEntropy);       
    }
    
    /**
     * Returns a more accurate Temperature than backwardPSregion2c
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    public function backwardPSregion2cExact($pressure, $specificEntropy){
        return $this->backwardExact('region2', 'specificEntropy', 'backwardPSregion2c', $pressure, $specificEntropy);       
    }
    
    /**
     * Uses linear interpolation to goal seek Region3 using pressure and entropy
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg
     * @return float $temperature K 
     */
    public function backwardPSregion3($pressure, $specificEntropy){
        return $this->backwardRegion3Exact($pressure, $specificEntropy, 'specificEntropy');
    }
    
    /**
     * Uses linear extrapolation for estimate equation to determine much more accurate $temperature
     * @param string $region ['region1','region2']
     * @param string $backwardUnitType ['specificEthalpy' or 'specificEntropy']
     * @param string $backwardRegionFunction  ['region1','region2a', etc]
     * @param float $pressure MPa
     * @param float $var2 [specificEthalpy or specificEntropy]
     * @return float Temperature K
     */
    public function backwardExact($region, $backwardUnitType, $backwardRegionFunction, $pressure, $var2){
        $pointA = $this->generatePoint($region, $backwardUnitType, $pressure, $this->$backwardRegionFunction($pressure, $var2) );
        $pointB = $this->generatePoint($region, $backwardUnitType, $pressure, $this->$backwardRegionFunction($pressure, $pointA[0]) );               
        $temperature = $this->linearTestPoint($var2, $pointA, $pointB);
      
        $pointA = $this->generatePoint($region, $backwardUnitType, $pressure, $temperature );
        $temperature = $this->linearTestPoint($var2, $pointA, $pointB);       
        return $temperature;
    }
    
    /**
     * Specifically for Region3. Uses linear interpolation for estimate equation to determine much more accurate $temperature
     * @param type $pressure MPa
     * @param type $unit [specificEthalpy or specificEntropy]
     * @param type $unitType ['specificEthalpy' or 'specificEntropy']
     * @return type
     */
    public function backwardRegion3Exact($pressure, $unit, $unitType ){
        $temperature = self::TEMPERATURE_Tp;
        $pointA = $this->generatePoint('region1', $unitType, $pressure, $temperature  );
        $pointB = $this->generatePoint('region2', $unitType, $pressure, $this->boundaryByPressureRegion3to2($pressure) );
        $temperatureB = $this->linearTestPoint($unit, $pointA, $pointB);                    
        $counter = 0;
        while( abs($temperature-$temperatureB)>1e-6 and $counter++<15){
            $pointA = $pointB;
            $pointB = $this->generatePoint('region3', $unitType, $pressure, $temperatureB  );
            $temperature = $temperatureB;
            $temperatureB = $this->linearTestPoint($unit, $pointA, $pointB);      
        }        
        return $temperatureB;
    }
    
    /**
     * Generates a Data Point for a given function
     * @param string $function
     * @param string $key
     * @param float $var1
     * @param float $var2
     * @return array()
     */
    public function generatePoint($function, $key, $var1, $var2){
        $result = $this->$function($var1, $var2);
        $point = array($result[$key],$var2);
        return $point;
    }
    
    /**
     * Uses linear extrapolation to determine location of $X relative to both points
     * @param float $X
     * @param array() $point1
     * @param array() $point2
     * @return float Y
     */
    public function linearTestPoint($X, $point1, $point2){
        $slope = 0;
        if ($point1[0]-$point2[0] <> 0) $slope = ($point1[1]-$point2[1])/($point1[0]-$point2[0]);
        $yIntercept = $point1[1] - $slope*$point1[0];
        return $X*$slope+$yIntercept;
    }
    
    /**
     * Returns the minimum and maximum acceptable values for Temperature based on a given pressure
     * @param double $pressure 
     * @return array ('min', 'max') K
     */
    public function rangeTemperatureByPressure($pressure){
        return array('min' => self::TEMPERATURE_MIN,'max' => self::TEMPERATURE_MAX);
    }  
    
    /**
     * Returns the minimum and maximum acceptable values for Entropy based on a given pressure
     * @param double $pressure MPa 
     * @return array ('min', 'max') kJ/kg/K
     */
    public function rangeSpecificEntropyByPressure($pressure){
        return $this->rangeByPressure($pressure, 'specificEntropy');        
    }   
    
    /**
     * Returns the minimum and maximum acceptable values for Enthalpy based on a given pressure
     * @param double $pressure MPa
     * @return array ('min', 'max') kJ/kg
     */
    public function rangeSpecificEnthalpyByPressure($pressure){
        return $this->rangeByPressure($pressure, 'specificEnthalpy');
    }
    
    /**
     * Returns the minimum and maximum acceptable values for "$type" based on a given pressure
     * @param double $pressure MPa 
     * @return array ('min', 'max')
     */
    public function rangeByPressure($pressure, $type){
        $min = $this->waterPropertiesPT($pressure, self::TEMPERATURE_MIN);
        $max = $this->waterPropertiesPT($pressure, self::TEMPERATURE_MAX);
        $result = array(
            'min' => $min[$type],
            'max' => $max[$type],
        );
        return $result;
    } 
}
?>
