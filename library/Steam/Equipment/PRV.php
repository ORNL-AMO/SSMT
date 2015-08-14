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
 * Models a Pressure Reducing Valve PRV with optional Desuperheating
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_PRV {

    /**
     * Inlet Steam Properties
     * @var Steam_Object
     */
    var $inletSteam;
    /**
     * Desuperheating Fluid Properties
     * @var Steam_Object
     */
    var $desuperheatFluid;
    /**
     * Outlet Steam Properties
     * @var Steam_Object
     */
    var $outletSteam;
    
    /**
     * Desuperheating Temperature K
     * @var float
     */
    var $desuperheatTemp;
    
    /**
     * Mass flow of desuperheating fluid kg/hr
     * $var float
     */
    var $desuperFluidFlow = 0;
    
    /**
     * List of any warnings
     * @var array()
     */
    public $warnings = array();
    
    /**
     * Generates PRV Model
     * @param Steam_Object $steamObject
     * @param float $outletPressure MPa
     * @param Steam_Object $desuperheatFluid
     * @param float $desuperheatTemp K
     */
    function __construct(Steam_Object $steamObject, $outletPressure, Steam_Object $desuperheatFluid = NULL, $desuperheatTemp = NULL) {
        $this->inletSteam = $steamObject;
        $this->desuperheatFluid = $desuperheatFluid;
        if ($this->desuperheatFluid instanceof Steam_Object) $this->desuperheatFluid->setMassFlow(0);
        $this->desuperheatTemp = $desuperheatTemp;

        if ($desuperheatTemp and $this->inletSteam->massFlow>0){
            //Desuperheating
            $this->outletSteam = new Steam_Object(array(
                'pressure' => $outletPressure,
                'temperature' => $this->desuperheatTemp,
                ));
            
            $this->SAToutletSteam = new Steam_Object(array(
                'pressure' => $outletPressure,
                'quality' => 1,
                ));
            
            $e1 = $this->outletSteam->specificEnthalpy;
            $e2 = $this->desuperheatFluid->specificEnthalpy;
            $e3 = $this->inletSteam->specificEnthalpy;

            $m3 = $this->inletSteam->massFlow;

            $m1 = $m3 * ($e3-$e2) / ($e1-$e2);
            $m2 = $m1 - $m3;
            if ($m1>0 and $m2>0 
                    and $this->outletSteam->specificEnthalpy >= $this->SAToutletSteam->specificEnthalpy //Assume Outlet is steam if desuperheating
                    ){
                $this->outletSteam->setMassFlow($m1);
                $this->desuperheatFluid->setMassFlow($m2);
                $this->desuperFluidFlow = $m2;
            }else{
                if ($m1<0 and $m2<0) $this->warnings[] = 'Outlet specific enthalpy associated with set desuperheating temperature is less than the cooling fluid specific enthalpy. Desuperheating canceled.';
                if ($m1>0 and $m2<0) $this->warnings[] = 'Outlet specific enthalpy associated with set desuperheating temperature is greater than the inlet specific enthalpy. Desuperheating canceled.';
                if ($this->outletSteam->specificEnthalpy<$this->SAToutletSteam->specificEnthalpy) $this->warnings[] = 'Outlet specific enthalpy associated with set desuperheating temperature is less than specific enthalpy for saturated steam. Desuperheating canceled.';
                $this->outletSteam = new Steam_Object(array(
                    'pressure' => $outletPressure,
                    'specificEnthalpy' => $this->inletSteam->specificEnthalpy,
                    'massFlow' => $this->inletSteam->massFlow
                ));
            }
        }else{
            //No Desuperheating
            $this->outletSteam = new Steam_Object(array(
                'pressure' => $outletPressure,
                'specificEnthalpy' => $this->inletSteam->specificEnthalpy,
                'massFlow' => $this->inletSteam->massFlow
                ));
        }
    }    

    /**
     * Check Boiler Model for Warnings
     * @return int Warning Count
     */
    function checkWarnings(){        
        return count($this->warnings);
    }

    /**
     * Generates a table of all PRV properties
     * @param string $tableName
     * @return string HTML of PRV Properties 
     */
    function displayPRV($tableName = NULL){
        $prvDisplay = Steam_Support::displayWarnings($this);
        if ($this->desuperheatTemp){
            $steamDisplay = new Steam_ObjectDisplay(array(
               array($this->inletSteam, 'Steam In'),
               array($this->desuperheatFluid, 'Feedwater'),
               array($this->outletSteam, 'Steam Out'),
            ));
        }else{
            $steamDisplay = new Steam_ObjectDisplay(array(
               array($this->inletSteam, 'Steam In'),
               array($this->outletSteam, 'Steam Out'),
            ));
        }
        
        $prvDisplay .= $steamDisplay->displaySteamObjectTable($tableName, true);
        
        $prvDisplay .= "<table><tr><td>
         <form action='equipPrv' method='Post'>
         <input type='hidden' name='InletPressure' value='".round($steamDisplay->mS->localize($this->inletSteam->pressure,'pressure'),2)."'>
         <input type='hidden' name='InletSecondParameter' value='SpecificEnthalpy'>
         <input type='hidden' name='InletSpecificEnthalpy' value='".round($steamDisplay->mS->localize($this->inletSteam->specificEnthalpy,'specificEnthalpy'),2)."'>
         <input type='hidden' name='massFlow' value='".round($steamDisplay->mS->localize($this->inletSteam->massFlow,'massflow'),2)."'>
         <input type='hidden' name='outletPressure' value='".round($steamDisplay->mS->localize($this->outletSteam->pressure,'pressure'),2)."'>
             ";
        if (isset($this->desuperheatTemp)){
            $prvDisplay .= "
            <input type='hidden' name='desuperheating' value='Yes'>
            <input type='hidden' name='FeedwaterPressure' value='".round($steamDisplay->mS->localize($this->desuperheatFluid->pressure,'pressure'),2)."'>
            <input type='hidden' name='FeedwaterSecondParameter' value='SpecificEnthalpy'>
            <input type='hidden' name='FeedwaterSpecificEnthalpy' value='".round($steamDisplay->mS->localize($this->desuperheatFluid->specificEnthalpy,'specificEnthalpy'),2)."'>
            <input type='hidden' name='desuperTemp' value='".round($steamDisplay->mS->localize($this->desuperheatTemp,'temperature'),2)."'>
                    ";
        }else{
            $prvDisplay .= "<input type='hidden' name='desuperheating' value='No'>";
        }
         
        $prvDisplay .= "
            <input type='submit' name='Enter' value='".$steamDisplay->translator->_('Copy to PRV Calculator')."'>
            </form>
            </td><td style='color: red; padding-left: 10px;'>
            ".$steamDisplay->translator->_('*May include slight rounding errors.')."
            </td></tr></table>";
        
        return $prvDisplay;
    }
}