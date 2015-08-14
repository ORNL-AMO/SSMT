<?php
/**
 * Steam Calculators
 *
 * @package    Steam
 * @subpackage Steam_Equipment
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Models a Water/Steam Heat Exchanger
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_HeatExchanger {
    
    /**
     * Creates a heat exchanger object using the 
     *      hotInlet        temp K
     *      coldInlet       temp K
     *      approachTemp    temp K
     * 
     * @param Steam_Object $hotInlet
     * @param Steam_Object $coldInlet
     * @param float $approachTemp
     */
    public function __construct($hotInlet, $coldInlet, $approachTemp = 20) {
        $this->hotInlet = $hotInlet;
        $this->coldInlet = $coldInlet;
        $maxTempDiff = $hotInlet->temperature - $coldInlet->temperature;
        if ($maxTempDiff < 0) ;
        if ($maxTempDiff < $approachTemp) ;
               
            $hotOutletTest = new Steam_Object(array(
                'pressure' => $hotInlet->pressure,
                'temperature' => ($coldInlet->temperature + $approachTemp),
                'massFlow' => $hotInlet->massFlow,
            ));
            $heatExchanged = $hotInlet->energyFlow - $hotOutletTest->energyFlow;
            $coldOutletTest = new Steam_Object(array(
                'pressure' => $coldInlet->pressure,
                'specificEnthalpy' => ($coldInlet->energyFlow + $heatExchanged)/$coldInlet->massFlow*1000,
                'massFlow' => $coldInlet->massFlow,
            ));
        
        if ( ($hotOutletTest->temperature-$coldInlet->temperature) > $approachTemp ) {
            $coldOutletTest = new Steam_Object(array(
                'pressure' => $coldInlet->pressure,
                'temperature' => ($hotInlet->temperature - $approachTemp),
                'massFlow' => $coldInlet->massFlow,
            ));
            $heatExchanged = $coldOutletTest->energyFlow - $coldInlet->energyFlow;
            $hotOutletTest = new Steam_Object(array(
                'pressure' => $hotInlet->pressure,
                'specificEnthalpy' => ($hotInlet->energyFlow-$heatExchanged) /$hotInlet->massFlow*1000,
                'massFlow' => $hotInlet->massFlow,
            ));
        }
        
        $this->hotOutlet = $hotOutletTest;
        $this->coldOutlet = $coldOutletTest;
        $this->heatExchanged = $heatExchanged;
    }

    /**
     * Check heat exchanger Model for Warnings
     * @return int Warning Count
     */
    function checkWarnings(){        
        $this->warnings = array();
        //No current warnings can be generated
        return count($this->warnings);
    }
    
    /**
     * Returns Table of Key Heat Exchanger Details
     * @return string
     */
    function displayHeatXDetails(){
        $mS = Steam_MeasurementSystem::getInstance();
        $translator = Zend_Registry::get('Zend_Translate');
        $details = "<table class='data'>
            <tr><th>".$translator->_('Hot Inlet / Cold Outlet Temperature Difference')."</th><td>".$mS->displayTemperaturediffLabeled($this->hotInlet->temperature-$this->coldOutlet->temperature)."</td></tr>            
            <tr><th>".$translator->_('Cold Inlet / Hot Outlet Temperature Difference')."</th><td>".$mS->displayTemperaturediffLabeled($this->hotOutlet->temperature-$this->coldInlet->temperature)."</td></tr>
            <tr><th>".$translator->_('Heat/Energy Exchanged')."</th><td>".$mS->displayEnergyflowLabeled($this->heatExchanged)."</td></tr>
            <tr><th>".$translator->_('Hot Inlet to Outlet Temperature Drop')."</th><td>".$mS->displayTemperaturediffLabeled($this->hotInlet->temperature-$this->hotOutlet->temperature)."</td></tr>    
            <tr><th>".$translator->_('Cold Inlet to Outlet Temperature Gain')."</th><td>".$mS->displayTemperaturediffLabeled($this->coldOutlet->temperature-$this->coldInlet->temperature)."</td></tr>    
        </table>";
        return $details;
    }

    /**
     * Generates a table of all heat exchanger properties
     * @param string $tableName
     * @return string HTML of heat exchanger Properties 
     */
    function displayHeatXProperties($tableName = NULL){

        $display = Steam_Support::displayWarnings($this);
        $steamDisplay = new Steam_ObjectDisplay(array(
            array($this->hotInlet, 'Hot Inlet'),
            array($this->coldOutlet, 'Cold Outlet'),
            array($this->hotOutlet, 'Hot Outlet'),
            array($this->coldInlet, 'Cold Inlet'),
            ));
        
        $display .= $steamDisplay->displaySteamObjectTable($tableName, true);
        $display .= $this->displayHeatXDetails();
        
        return $display;
    }
}