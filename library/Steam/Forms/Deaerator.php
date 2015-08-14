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
 * Deaerator Equipment Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_Deaerator extends Steam_StdForm{
        
    /**
     * Generates Form
     */
    public function __construct() {
        parent::__construct();
                
        $daPressure = $this->createElement('text', 'daPressure')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired('true')
                ->addValidator($this->isFloat,true)                        
                ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->critPressure(), 'inclusive' => true));                        
        
        $this->addSteamFields('Water');          
        $this->addSteamFields('Steam');
                             
        $ventRate = $this->createElement('text', 'ventRate')
                ->setAttrib("style", "width: 60px;")
                ->setRequired(true)
                ->addValidator($this->isFloat,true)
                ->addValidator('between', true, array('min' => DA_VENTRATE_MIN, 'max' => DA_VENTRATE_MAX, 'inclusive' => true));
        
        $feedwaterFlow = $this->createElement('text', 'feedwaterFlow')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));
        
        $this->addElements = array(
            $daPressure,           
            $ventRate,
            $feedwaterFlow,
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
        
        return parent::isValid($data);
    }  
}