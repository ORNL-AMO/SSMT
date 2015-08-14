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
 * Models Steam Header
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_Header {

    /**
     * Header Pressure
     * @var float MPa
     */
    public $headerPressure;
    /**
     * Array of Steam_Objects
     * @var array
     */
    public $inletSteamStreams = array();
    
    /**
     * Intial Combination of Inlet Steam at Header Pressure
     * @var Steam_Object
     */
    public $initialHeaderSteam;

    /**
     * Steam Remaining after steam removed
     * @var Steam_Object
     */
    public $remainingSteam;

    /**
     * Header Steam after any head loss
     * @var Steam_Object
     */
    public $finalHeaderSteam;
    /**
     * List of any warnings
     * @var array()
     */
    var $warnings = array();  
    
    /**
     * Creates Header Object
     * @param float $pressure MPa
     * @param Steam_Object $inletSteamStreams May be array of Steam_Objects
     */
    function __construct($pressure, $inletSteamStreams) {
        $this->headerPressure = $pressure;
        if (is_array($inletSteamStreams)){
            $this->inletSteamStreams = $inletSteamStreams;
        }else{
            $this->inletSteamStreams[] = $inletSteamStreams;
        }
        $this->combineInletStreams();
    }
    
    /**
     * Combines Inlet Steam Streams at header pressure
     */
    function combineInletStreams(){
        $energyFlow = 0;
        $massFlow = 0;
        $posEnergyFlow = 0;
        $posMassFlow = 0;
        foreach($this->inletSteamStreams as $stream){
            $energyFlow += $stream->energyFlow;
            $massFlow += $stream->massFlow;
            if ($stream->massFlow>0){
                $posEnergyFlow += $stream->energyFlow;
                $posMassFlow += $stream->massFlow;
            }else{                
                $posEnergyFlow -= $stream->energyFlow;
                $posMassFlow -= $stream->massFlow;
            }
        }
        $specificEnthalpy = 0;
        if ($posMassFlow){
            $specificEnthalpy = $posEnergyFlow*1000/$posMassFlow;
        }
        if ($specificEnthalpy<0) $specificEnthalpy = 0;
        
        
        $this->initialHeaderSteam = new Steam_Object(array(
           'pressure' => $this->headerPressure,
           'specificEnthalpy' => $specificEnthalpy,
           'massFlow' => $massFlow,
        ));
        //To Handle Header Model Runs
        if ($this->initialHeaderSteam->temperature<273.15){
            $this->initialHeaderSteam = new Steam_Object(array(
               'pressure' => $this->headerPressure,
               'temperature' => 273.15,
               'massFlow' => $massFlow,
            ));
        }
        $this->finalHeaderSteam = $this->initialHeaderSteam;
        $this->remainingSteam = $this->initialHeaderSteam;
    }
    
    /**
     * Uses $parameters to create a HeatLoss object and determine final/remaining header steam conditions after heat loss
     * @param array $parameters
     */
    function setHeatLoss($parameters){
        $this->heatLoss = new Steam_Equipment_HeatLoss($this->initialHeaderSteam, $parameters);
        $this->finalHeaderSteam = $this->heatLoss->outletSteam;
        $this->remainingSteam = $this->heatLoss->outletSteam;
    }

    /**
     * Subtracts $massflow from remaining header steam
     * @param float $massFlow kg/hr
     */
    function useSteam($massFlow){
        $this->remainingSteam->setMassFlow($this->remainingSteam->massFlow-$massFlow);
    }

    /**
     * Subtracts mass flow equal to a given $energyFlow from remaining header steam
     * @param float $massFlow kg/hr
     */
    function useEnergy($energyFlow){
        $massFlow = $energyFlow*1000/$this->remainingSteam->specificEnthalpy;
        $this->useSteam($massFlow);
        return $massFlow;
    }

    /**
     * Returns Header data table
     * @return string
     */
    function displayHeader($tableName = NULL){
        $display = new Steam_ObjectDisplay($this->inletSteamStreams);
        $display->addSteamObject($this->initialHeaderSteam, 'Header Before Heat Loss');
        $display->addSteamObject($this->finalHeaderSteam, 'Header After Heat Loss');
        return $display->displaySteamObjectTable($tableName, true);
    }
}