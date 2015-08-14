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
 * Fuel Names and CO2 Emission Coefficients
 * 
 * @package    Steam
 */

class Steam_Fuel {    
    
    /**
     * Provides all fuel details in array
     * Coefficients are in kg/MMBtu as per EPA estimates
     * @return array Fuel Details
     */
    static function fuelDetails(){
        return $fuels = array(
    
            'natGas' =>    array('Natural Gas',            53.06),
            'coalAnt' =>   array('Coal (Anthracite)',      103.69),
            'coalBit' =>   array('Coal (Bituminous)',      93.28),
            'coalSub' =>   array('Coal (Sub-bituminous)',  97.17),
            'coalLig' =>   array('Coal (Lignite)',         97.72),
            'heavyOil'=>   array('Heavy Fuel Oil',         78.80),
            'tires'   =>   array('Tires',                  85.97),
            
        );
    }
    
    /**
     * Calculates CO2 Emissions based on a fuel code and amount of energy
     * @param string $code fuel code
     * @param float $energy kJ
     * @return float metric tons
     */
    static public function co2Emissions($code, $energy){
        $fuels = self::fuelDetails();
        // 1055056 is used to convert from MMBtu to kJ
        return ( $fuels[$code][1]/1055056 ) * $energy;
    }
    
    /**
     * Returns fuel Name for a given code
     * @param string $code
     * @return string Fuel Name
     */
    static function fuelNames($code = false){
        
        $return = array();
        foreach(self::fuelDetails() as $key => $values){
            $return[$key] = $values[0];
        }
        if ($code) return $return[$code];
        return $return;
    }
    
    /**
     * Returns Coefficients or complete array of Coefficients
     * @param string $code fuel code
     * @return float or array of Coefficients
     */
    static function fuelCoeff($code = false){
        $return = array();
        foreach(self::fuelDetails() as $key => $values){
            $return[$key] = $values[1];
        }
        if ($code) return $return[$code];
        return $return;
    }

}