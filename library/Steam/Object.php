<?php
/**
 * Steam Calculators 
 * 
 * @package    Steam
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 * 
 */

/**
 * Include needed Object classes
 */
require_once 'IAPWS.php';

/**
 * Standard Steam Object
 * Determines and Contains all properties of an individual point of steam
 * 
 * @package    Steam
 */
class Steam_Object{

    /**
     * Temperature K
     * @var double
     */
    public $temperature = NULL;
    
    /**
     * Pressure MPa
     * @var double
     */
    public $pressure = NULL;
    
    /**
     * Quality of Steam
     * If Saturated, 0 to 1; Otherwise null
     * @var double
     */
    public $quality = NULL;
    
    /**
     * Massflow kg/hr
     * @var double 
     */
    public $massFlow = 0;
    
    /**
     * Volume flow m3/hr
     * @var double 
     */
    public $volumeFlow = 0;
    
    /**
     * Specific Enthalpy kJ/kg
     * @var double
     */
    public $specificEnthalpy = NULL;
    
    /**
     * Specific Entropy kJ/kg/R
     * @var double 
     */
    public $specificEntropy = NULL;
    
    /**
     * Specific Entropy m3/kg
     * @var double 
     */
    public $specificVolume = NULL;
    
    /**
     * Energy Flow kJ/hr
     * @var double
     */
    public $energyFlow = NULL;
    
    /**
     * Phase Liquid/Saturated/Gas
     * @var double 
     */
    public $phase = NULL;
    
    /**
     * Density kg/m3
     * @var double 
     */
    public $density = NULL;
    
    /**
     * IAPWS Steam Region
     * @var double 
     */
    public $region = NULL;

    /**
     * Determines Full Steam Properties based on $properties
     * 
     * If $properties is NULL, creates structured blank Steam_Object 
     * 
     * $properties must include "pressure" and 1 of the following:
     * temperature, quality, specificEnthalpy, or specificEntropy
     * 
     * $properties may also include "massFlow"
     * 
     * @param array() $properties 
     */
    public function __construct($properties = NULL) {        
        $this->iapws = new Steam_IAPWS();
        
        //Set Massflow if provided
        if (isset($properties['massFlow'])) $this->massFlow = $properties['massFlow'];
       
        //Determine Steam Properties
        if (isset($properties['temperature'])) $pressureAnd='temperature';
        if (isset($properties['quality'])) $pressureAnd='quality';
        if (isset($properties['specificEnthalpy'])) $pressureAnd='specificEnthalpy';
        if (isset($properties['specificEntropy'])) $pressureAnd='specificEntropy';
        
        if (isset($properties['pressure']) and isset($pressureAnd)) {
            switch ($pressureAnd) {
                case 'temperature':
                    $properties = $this->iapws->waterPropertiesPT($properties['pressure'],$properties['temperature']);                    
                    break;
                case 'quality':
                    $properties = $this->propertyQuality($properties['pressure'], $properties['quality']);
                    break;
                case 'specificEnthalpy':
                    $properties = $this->iapws->waterPropertiesPH($properties['pressure'], $properties['specificEnthalpy']);
                    break;
                case 'specificEntropy':
                    $properties = $this->iapws->waterPropertiesPS($properties['pressure'], $properties['specificEntropy']);
                    break;
            }
            $this->setProperties($properties);
        }        
    }

    /**
     * Determine Steam Properties based on Pressure and Quality
     * @param double $pressure MPa
     * @param double $quality (0-1)
     * @return array() Steam Properties
     */
    private function propertyQuality($pressure, $quality){
        $tmp = $this->iapws->saturatedPropertiesByPressure($pressure);
        $properties['temperature'] = $tmp['gas']['temperature'] * 1;
        $properties['pressure'] = $tmp['gas']['pressure'] * 1;
        $properties['specificEnthalpy'] = $tmp['gas']['specificEnthalpy'] * $quality + $tmp['liquid']['specificEnthalpy'] * (1-$quality);
        $properties['specificEntropy'] = $tmp['gas']['specificEntropy'] * $quality + $tmp['liquid']['specificEntropy'] * (1-$quality);
        $properties['specificVolume'] = $tmp['gas']['specificVolume'] * $quality + $tmp['liquid']['specificVolume'] * (1-$quality);
        $properties['density'] = 1/$properties['specificVolume'];
        $properties['quality'] = $quality;
        $properties['region'] = $tmp['region'];
        return $properties;
    }

    /**
     * Sets the Mass Flow and Energy flow of the Steam
     * @param double $massFlow kg/hr
     */
    public function setMassFlow($massFlow){       
        $this->massFlow = $massFlow*1;
        $this->energyFlow = $this->specificEnthalpy * $this->massFlow / 1000;        
        $this->volumeFlow = $this->specificVolume * $this->massFlow * 1000;
    }

    /**
     * Set Steam Properties as Object Variables
     * @param array() $properties  Steam Properties
     */
    private function setProperties($properties) {
        $this->temperature = $properties['temperature'];
        $this->pressure = $properties['pressure'];
        $this->specificEnthalpy = $properties['specificEnthalpy'];
        $this->specificEntropy = $properties['specificEntropy'];
        $this->specificVolume = $properties['specificVolume'];
        $this->quality = $properties['quality'];
        $this->density = null;
        if ($properties['specificVolume']<>0) $this->density = 1/$properties['specificVolume'];
        $this->region = 0;
        if (isset($properties['region']) ) $this->region = $properties['region'];
        $this->phase = 'Saturated';
        if (is_null($this->quality)){
            $this->phase = $properties['phase'];
        }
        $this->setMassFlow($this->massFlow);
    }  
}
