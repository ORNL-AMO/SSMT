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
 * Steam Turbine Equipment Calculator Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_Turbine extends Steam_StdForm{
                
    /**
     * Generates Form
     */
    public function __construct($options = null) {
        parent::__construct($options);
        $mS = Steam_MeasurementSystem::getInstance();
        $this->addSteamFields('Inlet');
        $this->addSteamFields('Outlet');
       
        $this->fieldText = array(
            'Inlet' => array(                
                'Pressure' => "Inlet Pressure.<br><span style='font-style: italic'>-Commonly between 0 and 300<br> and occasionally much greater.</span>",
                'Temperature' => "Inlet Temperature.<br><span style='font-style: italic'>-If below boiler point, inlet will be a liquid.</span>",
                'SpecificEnthalpy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'SpecificEntropy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'Quality' => "Inlet Quality: Ratio of Gas/Vapor Mass to total Mass.",
            ),
            'Outlet' => array(                
                'Pressure' => "Outlet Pressure.<br><span style='font-style: italic; color: red;'>MUST also be less than the inlet pressure.</span>",
                'Temperature' => "Inlet Temperature.<br><span style='font-style: italic'>-If below boiler point, inlet will be a liquid.</span>",
                'SpecificEnthalpy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'SpecificEntropy' => "<span style='font-style: italic'>-Range further limited by associated pressure.</span>",
                'Quality' => "Inlet Quality: Ratio of Gas/Vapor Mass to total Mass.",
            ));
        $solveFor = $this->createElement('select', 'solveFor')
                ->addMultiOptions(array('outlet' => 'Outlet Properties', 'isoEff' => 'Isentropic Efficiency'))
                ->setLabel('Solve For: ');

        $turbineMethod = $this->createElement('select', 'turbineMethod')
                ->addMultiOptions(array('massFlow' => 'Mass Flow', 'powerOut' => 'Power Out'))
                ->setLabel('Select Turbine Property');

        $massFlow = $this->createElement('text', 'massFlow')
                        ->setAttrib("style", "width: 60px;")
                ->setLabel('Mass Flow ('.$mS->selected['massflow'].')');
        
        $powerOut = $this->createElement('text', 'powerOut')
                        ->setAttrib("style", "width: 60px;")
                ->setLabel('Power Out ('.$mS->selected['power'].')');
        
        $isentropicEff = $this->createElement('text', 'isentropicEff')
                        ->setAttrib("style", "width: 60px;")
                ->setLabel('Isentropic Efficiency (%)');        
        
        $genEff = $this->createElement('text', 'genEff')
                        ->setAttrib("style", "width: 60px;")
                ->setRequired('true')
                    ->addValidator($this->isFloat)
                    ->addValidator('Between', true, array('min' => GENEFF_MIN, 'max' => GENEFF_MAX))
                ->setLabel('Generator Efficiency (%)');



        $submit = $this->createElement('submit', 'Enter');

        $this->addElements(array(
            $solveFor,
            $turbineMethod,
            $massFlow,
            $powerOut,
            $isentropicEff,
            $genEff,
            $submit
            ));
    }
    
    /**
     * Determines if Form Data is Valid (true => valid, false => invalid)
     * @param array() $data Form Data
     * @return boolean Valid
     */
    public function isValid($data) {
        //Remove Commas        
        $data = str_replace(",", "", $data);
        
        //Validate Massflow if selected
        if ($data['turbineMethod']=='massFlow'){
            $this->getElement('massFlow')->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('between', true, array('min' => $this->mS->minMassflow(), 'max' => $this->mS->maxMassflow(), 'inclusive' => false));
        }
        //Validate Power if selected
        if ($data['turbineMethod']=='powerOut'){
            $this->getElement('powerOut')->setRequired(true)
                ->addValidator($this->isFloat)
                ->addValidator('greaterThan', true, array('min'=>0));
        }
                
        //Validate Outlet Fields only if solving for Isentropic Efficiency
        $tmp = $this->steamNames;
        if ($data['solveFor']=='outlet'){
            $this->getElement('isentropicEff')->setRequired('true')
                ->addValidator($this->isFloat)
                ->addValidator('Between', true, array('min' => ISOEFF_MIN, 'max' => ISOEFF_MAX));

            $this->steamNames = array('Inlet');
            $this->getElement('OutletPressure')->setRequired('true')
                ->addValidator($this->isFloat,true)                        
                ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->maxPressure(), 'inclusive' => true));        
        }
                 
        //Validate that Outlet Pressure is less than Inlet Pressure
        if ($data['InletPressure']) $this->getElement('OutletPressure')->addValidator('lessThan',true, array('max' => $data['InletPressure'], 'messages' => 'Must be less than inlet pressure'));
        
        $valid = parent::isValid($data);
        $this->steamNames = $tmp;
        return $valid;        
    }
}