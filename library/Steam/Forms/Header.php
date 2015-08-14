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
 * Header Equipment Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_Header extends Steam_StdForm{
                
    /**
     * Generates Form
     */
    public function __construct($options = null) {
        parent::__construct($options);
                                                        
        $headerCount = $this->createElement('select', 'headerCount')
            ->addMultiOptions(array(
                2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9
                    ));
        $this->addElement($headerCount);
        
        $headerPressure = $this->createElement('text', 'headerPressure')
                ->setAttrib("style", "width: 60px;")
                    ->setRequired('true')
                    ->addValidator($this->isFloat,true)                        
                    ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->maxPressure(), 'inclusive' => true));
        $this->addElement($headerPressure);
        
        for($x=1;$x<=9;$x++){ 
            $this->addSteamFields('Inlet'.$x);
            $massFlow = $this->createElement('text', 'massFlow'.$x)
                ->setAttrib("style", "width: 60px;");   
            $this->addElement($massFlow);
        }
        
    }
    
    /**
     * Determines if Form Data is Valid (true => valid, false => invalid)
     * @param array() $data Form Data
     * @return boolean Valid
     */
    public function isValid($data) {
        //Remove Commas        
        $data = str_replace(",", "", $data);
        
        
        //Only require steam fields for the selected number of headers
        $tmp = $this->steamNames;
        $this->steamNames = array();
        if (!isset($data['headerCount']) ) $data['headerCount']=2;
        for($x=1;$x<=$data['headerCount'];$x++){
            $this->getElement('massFlow'.$x)->setRequired('true')
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));
            $this->steamNames[] = 'Inlet'.$x;            
        }
        
        $valid = parent::isValid($data);
        $this->steamNames = $tmp;
        return $valid;
    }
    
    
}
