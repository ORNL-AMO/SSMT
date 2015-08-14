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
 * include needed classes
 */
require_once('Steam/Object.php');
require_once('Steam/ObjectDisplay.php');

/**
 * Models Steam Heat Loss
 * 
 * % Percent loss based on specific enthalpy using the triple point as the 0 baseline
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_HeatLoss {

    /**
     * Inlet Steam Properties
     * @var Steam_Object
     */
    public $inletSteam;   
    
    /**
     * Energy Loss Unit per Time
     * @var float
     */
    public $energyFlowLoss;
    
    /**
     * Energy Loss precent (%)
     * @var float
     */
    public $energyLossPercent;
    
    /**
     * Outlet Steam Properties
     * @var Steam_Object
     */
    public $outletSteam;

    /**
     * 
     * @param Steam_Object $steamObject
     * @param array() $parameters ['precent']
     */
    function __construct(Steam_Object $steamObject, $parameters = array()) {
        $this->inletSteam = $steamObject;
        if (isset($parameters['percent'])) $this->percentLoss ($parameters['percent']);        
    }

    /**
     * Sets % Percent Energy Loss and Calculates Outlet Properties
     * @param type $percent
     */
    function percentLoss($percent){
        $this->energyFlowLoss = $this->inletSteam->energyFlow * $percent;
        $this->energyLossPercent = $percent;
        $this->determineOutletSteam();
    }  

    /**
     * Determines Outlet Steam Based on energyLossPercent and Specific Enthalpy
     */
    function determineOutletSteam(){
        if ($this->inletSteam->specificEnthalpy>0){
            $this->outletSteam = new Steam_Object(array(
                'pressure' => $this->inletSteam->pressure,
                'specificEnthalpy' => ($this->inletSteam->specificEnthalpy * (1 -$this->energyLossPercent)),
                'massFlow' => $this->inletSteam->massFlow
                ));
        }else{
            $this->outletSteam = new Steam_Object();
        }
    }

    /**
     * Returns Heat Loss data table
     * @return string
     */
    function displayHeatLoss(){
        $mS = Steam_MeasurementSystem::getInstance();
        $translator = Zend_Registry::get('Zend_Translate');   
        $heatLoss = "<table class='data'>
           <tr><th>".$translator->_('Heat Loss')."</th><td>".number_format(100*$this->energyLossPercent,2)." ".$mS->label('%')."</td></tr>
           <tr><th>".$translator->_('Heat Loss')."</th><td>".$mS->displayEnergyflowLabeled($this->energyFlowLoss)."</td></tr>
        </table>";
        return $heatLoss;
    }
}
