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
 * Flash Tank Equipment Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_FlashTank extends Steam_StdForm{
                
    /**
     * Generates Form
     */
    public function __construct() {
        parent::__construct();
        $this->addSteamFields('Inlet');
        $this->getElement('InletSecondParameter')->setValue('Quality');        
        $this->fieldText = array(
            'Inlet' => array(                
                'Pressure' => "Inlet Pressure.<br><span style='font-style: italic'>-Commonly between 0 and 300<br> and occasionally much greater.</span>",
                'Temperature' => "Inlet Temperature.<br><span style='font-style: italic'>-If below boiler point, inlet will be a liquid.</span>",
                'SpecificEnthalpy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'SpecificEntropy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'Quality' => "Inlet Quality: Ratio of Gas/Vapor Mass to total Mass.",
            ));
        
        $massFlow = $this->createElement('text', 'massFlow')
                ->setAttrib("style", "width: 60px;")
                ->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));        
        
        $tankPressure = $this->createElement('text', 'tankPressure')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true)
                ->addValidator($this->isFloat)
                    ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->critPressure(), 'inclusive' => true));        
        
        $this->addElements = array(
            $massFlow,
            $tankPressure,
        );
    }
    
    /**
     * Determines if Form Data is Valid (true => valid, false => invalid)
     * @param array() $data Form Data
     * @return boolean Valid
     */    
    public function isValid($data) {
        //Remove Commas        
        $data = str_replace(",", "", $data);
        
        
        //Valid that Tank Pressure is less than Inlet Pressure
        $this->getElement('tankPressure')->addValidator('lessThan', true, array('max' => $data['InletPressure'], 'messages' => 'Must be less than Inlet Pressure'));
        
        return parent::isValid($data);
    }
    
    
}