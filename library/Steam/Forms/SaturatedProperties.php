<?php
/**
 * Steam Calculators 
 *
 * @package    Steam
 * @subpackage Steam_Forms
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Saturated Properties Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_SaturatedProperties extends Steam_StdForm{
    
    /**
     * Generates form Fields
     * @param array() $options
     */
    public function __construct($options = null) {
        parent::__construct($options);

        $using = $this->createElement('select', 'using')
                ->addMultiOptions(array('pressure' => 'Pressure', 'temperature' => 'Temperature'))
                ->setLabel('Using: ');

        $pressure = $this->createElement('text', 'Pressure')
                        ->setAttrib("style", "width: 60px;");

        $temperature = $this->createElement('text', 'Temperature')
                        ->setAttrib("style", "width: 60px;");

        $submit = $this->createElement('submit', 'Enter');

        $this->addElements(array(
            $using,
            $pressure,
            $temperature,
            $submit
            ));
    }
    
    /**
     * Adds Validation Tests and determines if form is valid
     * @param array() $data Form Data
     * @return boolean true if valid
     */
    public function isValid($data) {    
        //Remove Commas        
        $data = str_replace(",", "", $data);
            
        $mS = Steam_MeasurementSystem::getInstance();        
        switch ($data['using']){
            case 'temperature':
                $this->getElement('Temperature')->setRequired('true')                      
                ->addValidator('between', true, array('min' => $mS->minTemperature(), 'max' => $mS->critTemperature(), 'inclusive' => true));
                break;
            case 'pressure':
                $this->getElement('Pressure')->setRequired('true')                    
                ->addValidator('between', true, array('min' => $mS->minPressure(), 'max' => $mS->critPressure(), 'inclusive' => true));
                break;
        }
        $formValid = parent::isValid($data);
        return $formValid;        
    }
}
