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
 * Boiler Equipment Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_Boiler extends Steam_StdForm{
        
    /**
     * Generates Form
     */
    public function __construct() {
        parent::__construct();                            
        
        $daPressure = $this->createElement('text', 'daPressure')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true)
                ->addValidator($this->isFloat);   
        
        $combustEff = $this->createElement('text', 'combustEff')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('between', true, array('min' => COMBUSTION_EFF_MIN, 'max' => COMBUSTION_EFF_MAX, 'inclusive' => true));  
        
        $blowdownRate = $this->createElement('text', 'blowdownRate')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true)
                ->addValidator($this->isFloat, true)
                ->addValidator('between', true, array('min' => BLOWDOWN_RATE_MIN, 'max' => BLOWDOWN_RATE_MAX, 'inclusive' => true));;
                
        //Add Steam Fields
        $this->addSteamFields('Steam');    
        $this->fieldText = array(
            'Steam' => array(                
                'Pressure' => "Steam Pressure.<BR>Also used as Boiler and Blowdown Pressure.<br><span style='font-style: italic'>-Commonly between 0 and 300<br> and occasionally much greater.</span>",
                'Temperature' => "Temperature of the Steam.<br><span style='font-style: italic'>-If below boiler point, steam will not be generated.</span>",
                'SpecificEnthalpy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'SpecificEntropy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'Quality' => "Quality of Steam: Ratio of Gas/Vapor Mass to total Mass.<br><span style='font-style: italic'>-Most commonly set to 1 which means the steam is a Saturated Gas (100% Vapor).</span>",
            ));                
        
        $massFlow = $this->createElement('text', 'massFlow')
                ->setAttrib("style", "width: 60px;")
                ->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));
        
        $this->addElements = array(            
            $daPressure,
            $combustEff,
            $blowdownRate,            
            $massFlow,
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
        
        //Valid that DA Pressure is less than or equal to Steam Pressure and Crit Pressure
        if ($data['SteamPressure']<>'' and $data['SteamPressure']<>$data['daPressure']){
            if ($data['SteamPressure']>$this->mS->critPressure()){
                $this->getElement('daPressure')->addValidator('lessThan', true, array('max'=>$this->mS->critPressure()));
            }else{
                $this->getElement('daPressure')->addValidator('lessThan', true, array('max'=>$data['SteamPressure'], 'messages' => 'Cannot be greater than steam pressure.'));
            }
        }
        
        //Valid that DA Pressure is greater than Min Pressure
        if ($data['daPressure']<>$this->mS->minPressure())
            $this->getElement('daPressure')->addValidator('greaterThan', true, array('min' => $this->mS->minPressure()));
        
        return parent::isValid($data);
    }
    
}