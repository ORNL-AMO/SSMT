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
 * Models a Deaerator
 * 
 * @package    Steam
 * @subpackage Steam_Equipment
 */
class Steam_Equipment_Deaerator {

    /**
     * Vent Rate as a % of Feedwater Flow
     * @var float
     */
    public $ventRate;
    /**
     * DA Pressures MPa between pressure min and crit
     * @var flow
     */
    public $daPressure;
    /**
     * DA Water Feed Properties
     * @var Steam_Object
     */
    public $daWaterFeed;
    /**
     * DA Steam Feed Properties
     * @var Steam_Object
     */
    public $daSteamFeed;
    /**
     * DA Steam Vent Properties
     * @var Steam_Object
     */
    public $daVentSteam;
    /**
     * Feedwater Properties
     * @var Steam_Object;
     */
    public $feedwater;
    /**
     * Total DA Mass Flow
     * @var float
     */
    public $totalFlow;
    /**
     * Additional Energy Needed above Inlet Water
     * @var float
     */
    public $neededEnergyFlow;
    /**
     * Flag for DA failure
     * @var boolean
     */
    public $daNotFunctioning;
    /**
     * List of any warnings
     * @var array()
     */
    var $warnings = array();  
    
    /**
     * Creates a deaerator object using the 
     *      ventRate
     *      daPressure
     *      daWaterFeed
     *      daSteamFeed
     *      feedwaterFlow
     * 
     * @param array $properties includes:
     *      ventRate
     *      daPressure
     *      daWaterFeed
     *      daSteamFeed
     *      feedwaterFlow
     */
    function __construct($properties) {
        $this->ventRate = $properties['ventRate'];
        $this->daPressure = $properties['daPressure'];
        $this->daWaterFeed = $properties['daWaterFeed'];
        $this->daSteamFeed = $properties['daSteamFeed'];
        $this->feedwater = new Steam_Object(array(
           'pressure' => $this->daPressure,
           'quality' => 0,
           'massFlow' => $properties['feedwaterFlow'],
        ));

        $this->totalFlow = ($this->feedwater->massFlow * (1+$this->ventRate));
                
        $this->daVentSteam = new Steam_Object(array(
           'pressure' => $this->daPressure,
           'quality' => 1,
           'massFlow' => $this->feedwater->massFlow * $this->ventRate,
            ));               
        
        $energyOut = $this->daVentSteam->energyFlow + $this->feedwater->energyFlow;
        
        if ($this->daSteamFeed->specificEnthalpy <> $this->daWaterFeed->specificEnthalpy){
            $this->neededEnergyFlow = ($energyOut-$this->daWaterFeed->specificEnthalpy*$this->totalFlow/1000);
            $steamFeedFlow = ($energyOut-$this->daWaterFeed->specificEnthalpy*$this->totalFlow/1000)
                    /($this->daSteamFeed->specificEnthalpy-$this->daWaterFeed->specificEnthalpy)*1000;
        }else{
            // Steam and Water specific enthalpy equal
            $steamFeedFlow = 0;
        }        
        
        $this->daSteamFeed->setMassFlow($steamFeedFlow);
        $this->daWaterFeed->setMassFlow($this->totalFlow - $steamFeedFlow);     
        
        //Validate Correct Operation
        $energyIn = $this->daSteamFeed->energyFlow+$this->daWaterFeed->energyFlow;
        $energyOut = $this->feedwater->energyFlow+$this->daVentSteam->energyFlow;
        if ( ($energyIn-$energyOut)>1e-7 or $this->daSteamFeed->massFlow<0 or $this->daWaterFeed->massFlow<0 ){
            $this->daNotFunctioning = true;
            $this->daWaterFeed->setMassFlow($this->feedwater->massFlow);
            $this->feedwater = clone $this->daWaterFeed;
            $this->daVentSteam->setMassFlow(0);
            $this->daSteamFeed->setMassFlow(0);            
        }
    }

    /**
     * Check Deaerator Model for Warnings
     * @return int Warning Count
     */
    function checkWarnings(){        
        $this->warnings = array();
        if ($this->daNotFunctioning) $this->warnings[] = "Not Enough Energy to Operator Correctly";
        if ($this->daSteamFeed->massFlow<0) $this->warnings[] = "Negative Steam Flow";
        if ($this->daSteamFeed->massFlow==0) $this->warnings[] = "No Steam Flow";
        if ($this->daSteamFeed->specificEnthalpy==$this->daWaterFeed->specificEnthalpy) $this->warnings[] = "Steam and Water Specific Enthalpy Equal";
      
        if ($this->daSteamFeed->specificEnthalpy<$this->daWaterFeed->specificEnthalpy) $this->warnings[] = "Water Specific Enthalpy Greater Than Steam";
        
        if ($this->feedwater->specificEnthalpy>$this->daWaterFeed->specificEnthalpy
                and $this->feedwater->specificEnthalpy>$this->daSteamFeed->specificEnthalpy) $this->warnings[] = "Steam Specific Enthalpy too Low for Feedwater Requirements";
        return count($this->warnings);
    }
    
    /**
     * Returns Table of Deaerator Details
     * @return string
     */
    function displayDA(){
        $steamDisplay = new Steam_ObjectDisplay(array(
                array($this->daWaterFeed, 'Inlet Water'),
                array($this->daSteamFeed, 'Steam'),
                array($this->daVentSteam, 'Vent Steam'),
                array($this->feedwater, 'Feedwater'),
                ));
        $result = Steam_Support::displayWarnings($this);
        $result .= $steamDisplay->displaySteamObjectTable('',true);
        
        $result .= "<table><tr><td>
         <form action='equipDeaerator' method='Post'>
         <input type='hidden' name='daPressure' value='".round($steamDisplay->mS->localize($this->daPressure,'pressure'),2)."'>
         <input type='hidden' name='ventRate' value='".round($this->ventRate*100,2)."'>
         <input type='hidden' name='feedwaterFlow' value='".round($steamDisplay->mS->localize($this->feedwater->massFlow,'massflow'),2)."'>
         <input type='hidden' name='WaterPressure' value='".round($steamDisplay->mS->localize($this->daWaterFeed->pressure,'pressure'),2)."'>
         <input type='hidden' name='WaterSecondParameter' value='SpecificEnthalpy'>
         <input type='hidden' name='WaterSpecificEnthalpy' value='".round($steamDisplay->mS->localize($this->daWaterFeed->specificEnthalpy,'specificEnthalpy'),2)."'>
         <input type='hidden' name='SteamPressure' value='".round($steamDisplay->mS->localize($this->daSteamFeed->pressure,'pressure'),2)."'>
         <input type='hidden' name='SteamSecondParameter' value='SpecificEnthalpy'>
         <input type='hidden' name='SteamSpecificEnthalpy' value='".round($steamDisplay->mS->localize($this->daSteamFeed->specificEnthalpy,'specificEnthalpy'),2)."'>                  
         <input type='submit' name='Enter' value='".$steamDisplay->translator->_('Copy to Deaerator Calculator')."'>
        
         </form>
         </td><td style='color: red; padding-left: 10px;'>
         ".$steamDisplay->translator->_('*May include slight rounding errors.')."
         </td></tr></table>";
            
        return $result;
    }

}