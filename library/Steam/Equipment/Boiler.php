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
 * Models a Boiler using Steam Demand
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_Boiler {
   
    /**
     * Deaerator DA pressure
     * @var float 
     */
    public $daPressure;
    
    /**
     * Boiler Feedwater properties
     * @var Steam_Object
     */
    public $feedwater;
    
    /**
     * Boiler operating pressure
     * @var float 
     */
    public $boilerPressure;
    
    /**
     * Produced Steam properties
     * @var Steam_Object
     */
    public $outletSteam;
    
    /**
     * Blowdown water properties
     * @var Steam_Object 
     */
    public $blowdown;
    
    /**
     * Blowdown as a Percent (%) of Inlet mass flow
     * @var float
     */
    public $blowdownRate;
    
    /**
     * Required boiler energy
     * @var float
     */
    public $boilerEnergy;
    
    /**
     * Combustion efficiency (%) of the boiler
     * @var float
     */
    public $boilerEff;
    
    /**
     * Required boiler fuel energy
     * @var float
     */
    public $fuelEnergy;
    /**
     * List of any warnings
     * @var array()
     */
    var $warnings = array();  

    /**
     * Creates a boiler object using the 
     *      boilerPressure
     *      daPressure
     *      boilerEff
     *      outletMassFlow
     *      blowdownRate
     *      superheatTemp or outletSteam
     * 
     * @param array $boilerDetails includes:
     *      boilerPressure
     *      daPressure
     *      boilerEff
     *      outletMassFlow
     *      blowdownRate
     *      superheatTemp or outletSteam
     * @throws Zend_Exception if any are missing 
     */
    function __construct($boilerDetails) {        
        if (!array_key_exists('boilerPressure', $boilerDetails)) 
                throw new Zend_Exception("Boiler Details missing: boilerPressure");
        if (!array_key_exists('daPressure', $boilerDetails)) 
                throw new Zend_Exception("Boiler Details missing: daPressure");
        if (!array_key_exists('boilerEff', $boilerDetails)) 
                throw new Zend_Exception("Boiler Details missing: boilerEff");
        if (!array_key_exists('outletMassFlow', $boilerDetails)) 
                throw new Zend_Exception("Boiler Details missing: outletMassFlow");
        if (!array_key_exists('blowdownRate', $boilerDetails)) 
                throw new Zend_Exception("Boiler Details missing: blowdownRate");
        if (!array_key_exists('superheatTemp', $boilerDetails) 
                and !array_key_exists('outletSteam', $boilerDetails)) 
                throw new Zend_Exception("Boiler Details missing: superheatTemp or outletSteam");
        
        $this->boilerPressure = $boilerDetails['boilerPressure'];
        $this->daPressure = $boilerDetails['daPressure'];
        $this->boilerEff = $boilerDetails['boilerEff'];

        //Saturated Liquid
        $this->blowdown = new Steam_Object(array(
            'pressure' => $this->boilerPressure,
            'quality' => 0,
        ));

        if (isset($boilerDetails['superheatTemp'])){
            $saturatedOutlet = new Steam_Object(array(
                'pressure' => $this->boilerPressure,
                'quality' => 1,
            ));
            $this->outletSteam = new Steam_Object(array(
                'pressure' => $this->boilerPressure,
                'temperature' => ($this->blowdown->temperature + $boilerDetails['superheatTemp']),
            ));
            //Set oulet conditions as minimum saturated vapor
            if ($saturatedOutlet->specificEnthalpy>$this->outletSteam->specificEnthalpy) $this->outletSteam = $saturatedOutlet;
        }
        if (isset($boilerDetails['outletSteam'])){        
            $this->outletSteam = $boilerDetails['outletSteam'];
        }
        

        //Saturated Liquid
        $this->feedwater = new Steam_Object(array(
            'pressure' => $this->daPressure,
            'quality' => 0,
        ));

        $this->blowdownRate = $boilerDetails['blowdownRate'];
        $this->setOutletSteamMassFlow($boilerDetails['outletMassFlow']);
    }
    
    /**
     * Sets new steam output mass flow and adjusts inlet and blowdown mass flows
     * 
     * @param float $massflow 
     */
    function setOutletSteamMassFlow($massflow){
        $this->outletSteam->setMassFlow($massflow);
        $this->setBlowdownRate($this->blowdownRate);
    }

    /**
     * Sets Blowdown Rate and adjusts inlet mass flow to maintain steam output
     * 
     * @param float $blowdownRate 
     */
    function setBlowdownRate($blowdownRate){
        $this->blowdownRate = $blowdownRate;
        $this->feedwater->setMassFlow(
                $this->outletSteam->massFlow / (1 - $this->blowdownRate));
        $this->blowdown->setMassFlow(
            $this->feedwater->massFlow * $this->blowdownRate);  
        $this->calculateEnergyUse();
    }
    
    /**
     * Calculates Boiler and Fuel Energy Use 
     */
    function calculateEnergyUse(){
        $this->boilerEnergy = $this->outletSteam->energyFlow + $this->blowdown->energyFlow - $this->feedwater->energyFlow;
        $this->fuelEnergy = $this->boilerEnergy / $this->boilerEff;  
        $this->checkWarnings();
    }

    /**
     * Check Boiler Model for Warnings
     * @return int Warning Count
     */
    function checkWarnings(){        
        $this->warnings = array();
        if ($this->feedwater->massFlow>0){
            if ($this->outletSteam->phase<>'Gas' and $this->outletSteam->quality<>1){
                $this->warnings[] = "Outlet Steam Contains Condensate";
            }
            if ($this->fuelEnergy<0){
                $this->warnings[] = "Boiler Using Negative Energy";
            }
        }
        if ($this->feedwater->massFlow<0){
            $this->warnings[] = "Steam Flow Negative";
        }
        return count($this->warnings);
    }
    
    /**
     * Returns Table of Key Boiler Details
     * @return string
     */
    function displayBoilerDetails(){
        $mS = Steam_MeasurementSystem::getInstance();
        $translator = Zend_Registry::get('Zend_Translate');
        $details = "<table class='data'>
            <tr><th>".$translator->_('Blowdown Rate')."</th><td>".number_format(100*$this->blowdownRate,1)." ".$mS->label('%')."</td></tr>
            <tr><th>".$translator->_('Boiler Energy')."</th><td>".$mS->displayEnergyflowLabeled($this->boilerEnergy)."</td></tr>
            <tr><th>".$translator->_('Combustion Efficiency')."</th><td>".number_format(100*$this->boilerEff,1)." ".$mS->label('%')."</td></tr>
            <tr><th>".$translator->_('Fuel Energy')."</th><td>".$mS->displayEnergyflowLabeled($this->fuelEnergy)."</td></tr>
        </table>";
        return $details;
    }

    /**
     * Generates a table of all boiler properties
     * @param string $tableName
     * @return string HTML of Boiler Properties 
     */
    function displayBoilerProperties($tableName = NULL){

        $display = Steam_Support::displayWarnings($this);
        $steamDisplay = new Steam_ObjectDisplay(array(
            array($this->feedwater, 'Feedwater'),
            array($this->blowdown, 'Blowdown'),
            array($this->outletSteam, 'Steam'),
            ));
        $display .= $steamDisplay->displaySteamObjectTable($tableName, true);
        $display .= $this->displayBoilerDetails();
             $display .= "<table><tr><td>
         <form action='equipBoiler' method='Post'>
         <input type='hidden' name='daPressure' value='".round($steamDisplay->mS->localize($this->feedwater->pressure,'pressure'),2)."'>
         <input type='hidden' name='combustEff' value='".round($this->boilerEff*100,2)."'>
         <input type='hidden' name='blowdownRate' value='".round($this->blowdownRate*100,2)."'>
         <input type='hidden' name='SteamPressure' value='".round($steamDisplay->mS->localize($this->outletSteam->pressure,'pressure'),2)."'>
         <input type='hidden' name='SteamSecondParameter' value='SpecificEnthalpy'>
         <input type='hidden' name='SteamSpecificEnthalpy' value='".round($steamDisplay->mS->localize($this->outletSteam->specificEnthalpy,'specificEnthalpy'),2)."'>
         <input type='hidden' name='massFlow' value='".round($steamDisplay->mS->localize($this->outletSteam->massFlow,'massflow'),2)."'>
         
         <input type='submit' name='Enter' value='".$steamDisplay->translator->_('Copy to Boiler Calculator')."'>
         </form>
         </td><td style='color: red; padding-left: 10px;'>
         ".$steamDisplay->translator->_('*May include slight rounding errors.')."
         </td></tr></table>";
         
        return $display;
    }
}

