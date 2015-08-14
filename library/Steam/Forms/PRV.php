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
 * PRV Equipment Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_PRV extends Steam_StdForm{
                
    /**
     * Generates Form
     */
    public function __construct($options = null) {
        parent::__construct($options);
        $this->addSteamFields('Inlet');
        $this->addSteamFields('Feedwater');
                        
        $this->fieldText = array(
            'Inlet' => array(                
                'Pressure' => "Inlet Pressure.<br><span style='font-style: italic'>-Commonly between 0 and 300<br> and occasionally much greater.</span>",
                'Temperature' => "Inlet Temperature.<br><span style='font-style: italic'>-If below boiler point, inlet will be a liquid.</span>",
                'SpecificEnthalpy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'SpecificEntropy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'Quality' => "Inlet Quality: Ratio of Gas/Vapor Mass to total Mass.",
            ));
        $this->fieldText = array(
            'Feedwater' => array(                
                'Pressure' => "Feedwater Pressure.",
                'Temperature' => "Feedwater Temperature.",
                'SpecificEnthalpy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'SpecificEntropy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'Quality' => "Feedwater Quality: Ratio of Gas/Vapor Mass to total Mass.",
            ));
        
        $massFlow = $this->createElement('text', 'massFlow')
                ->setAttrib("style", "width: 60px;")
                ->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));        
        
        $outputPressure = $this->createElement('text', 'outletPressure')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true);                        
        
        $desuperTemp = $this->createElement('text', 'desuperTemp')
                ->setAttrib("style", "width: 60px;")                
                ->setRequired(true);        
        
        $desuperheating = $this->createElement('select', 'desuperheating')
            ->addMultiOptions(array(
                'Yes'=>'Desuperheating','No'=>'Not Desuperheating',
                    ))
                ->setValue('No');
        
        $this->addElements = array(
            $massFlow,
            $outputPressure,
            $desuperTemp,
            $desuperheating,
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
        
        //Validate that Outlet Pressure is less than Inlet Pressure
        $this->getElement('outletPressure')->addValidator('lessThan', true, array('max' => $data['InletPressure'], 'messages' => 'Must be less than Inlet Pressure'));
        
        //Only require Desuperheating fields if desuperheating
        $tmp = $this->steamNames;
        if (isset($data['desuperheating']) and $data['desuperheating']=='No'){            
            $this->steamNames = array();
            $this->steamNames[] = 'Inlet';    
            $this->getElement('desuperTemp')->setRequired(false);        
        }else{
            $this->getElement('desuperTemp')
                                ->addValidator($this->isFloat,true)    
                            ->addValidator('between', true, array('min' => $this->mS->minTemperature(), 'max' => $this->mS->maxTemperature(), 'inclusive' => true));
        }
        
        $valid = parent::isValid($data);
        $this->steamNames = $tmp;
        return $valid;
    }
    
    
}
