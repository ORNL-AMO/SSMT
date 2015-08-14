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
 * Models FlashTank
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_FlashTank {

    /**
     * Flash Tank Pressure
     * @var float MPa
     */
    var $tankPressure;   
    /**
     * Inlet Fluid
     * @var Steam_Object
     */
    var $inletSteam;        
    /**
     * Liquid leaving the flash tank (Saturated or SubCooled)
     * @var Steam_Object
     */
    var $satLiquid;
    /**
     * Steam leaving the flash tank (Saturated or Superheated)
     * @var Steam_Object
     */
    var $satGas;
    /**
     * List of any warnings
     * @var array()
     */
    var $warnings = array();  
    
    /**
     * Generates Flash Tank Model
     * @param Steam_Object $steamObject
     * @param float $pressure MPa
     * @throws Exception
     */
    function __construct($steamObject, $pressure) {
        $this->tankPressure = $pressure;
        
        if (!$steamObject instanceof Steam_Object) throw new Exception('$steamObject not a Steam Object');
        if ($steamObject->pressure < $pressure)  throw new Exception('Flash tank pressure Higher than Steam');

        $this->inletSteam = $steamObject;
        $inletSteamReducedPressure = new Steam_Equipment_PRV($this->inletSteam, $pressure);
        $entranceSteam = $inletSteamReducedPressure->outletSteam;
        
        $this->satLiquid = new Steam_Object(array(
                'pressure' => $pressure,
                'quality' => 0,
                ));

        $this->satGas = new Steam_Object(array(
                'pressure' => $pressure,
                'quality' => 1,
                ));

        switch ($entranceSteam->phase){
            case 'Liquid':
                //No Flash
                $this->satLiquid = $entranceSteam;
                break;
            case 'Gas':
                //All Flash or Vapor Inlet
                $this->satGas = $entranceSteam;
                break;
            default:
                $m1 = $this->inletSteam->massFlow;

                $e1 = $this->inletSteam->specificEnthalpy;
                $e2 = $this->satGas->specificEnthalpy;
                $e3 = $this->satLiquid->specificEnthalpy;

                $m3 = $m1 * ($e1-$e2) / ($e3-$e2);
                $m2 = $m1 - $m3;

                $this->satGas->setMassFlow($m2);
                $this->satLiquid->setMassFlow($m3);
                break;
        }
    }

    /**
     * Check Boiler Model for Warnings
     * @return int Warning Count
     */
    function checkWarnings(){        
        $this->warnings = array();
        if ($this->satGas->massFlow==0 and $this->satLiquid->massFlow<>0) $this->warnings[] = "No Steam Flashing";
        if ($this->inletSteam->phase=='Gas') $this->warnings[] = "Inlet is Super Heated Steam";
            
        return count($this->warnings);
    }

    /**
     * Returns a table of all flashtank properties
     * @param string $tableName
     * @return string HTML of FlashTank Properties 
     */
    function displayFlashTank(){
        $steamDisplay = new Steam_ObjectDisplay(array(
           array($this->inletSteam, 'Inlet'),
           array($this->satGas, 'Steam Out'),
            array($this->satLiquid, 'Liquid Out'),
        ));
        $flashDisplay = Steam_Support::displayWarnings($this);
        $flashDisplay .= $steamDisplay->displaySteamObjectTable('',true);
        
        $flashDisplay .= "<table><tr><td>
         <form action='equipFlashtank' method='Post'>
         <input type='hidden' name='InletPressure' value='".round($steamDisplay->mS->localize($this->inletSteam->pressure,'pressure'),2)."'>
         <input type='hidden' name='InletSecondParameter' value='SpecificEnthalpy'>
         <input type='hidden' name='InletSpecificEnthalpy' value='".round($steamDisplay->mS->localize($this->inletSteam->specificEnthalpy,'specificEnthalpy'),2)."'>
         <input type='hidden' name='massFlow' value='".round($steamDisplay->mS->localize($this->inletSteam->massFlow,'massflow'),2)."'>
         <input type='hidden' name='tankPressure' value='".round($steamDisplay->mS->localize($this->tankPressure,'pressure'),2)."'>       
         <input type='submit' name='Enter' value='".$steamDisplay->translator->_('Copy to Flash Tank Calculator')."'>
         </form>
         </form>
         </td><td style='color: red; padding-left: 10px;'>
         ".$steamDisplay->translator->_('*May include slight rounding errors.')."
         </td></tr></table>";
        
        return $flashDisplay;
    }
}