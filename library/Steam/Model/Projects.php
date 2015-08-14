<?php
/**
 * Steam Calculators
 *
 * @package    Steam
 * @subpackage Steam_Model
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Generates the Projects Form Layout Components
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Projects{
        
    /**
     * Steam MeasurementSystem Object
     * @var Steam_MeasurementSystem
     */
    var $mS;
    /**
     * Zend_Translate
     * @var Zend_Translate
     */
    public $translator;       
    /**
     * Generated Base Model
     * @var Steam_Model_Constructor
     */
    var $baseModel;  
    /**
     * Total number of selected headers
     * @var int (1,2,or 3)
     */
    var $headerCount;
    /**
     * List of projects
     * @var array
     */
    var $projects;  
    /**
     * List of projects layout components
     * @var array
     */
    var $projectContent;     
    
    /**
     * ProjectsForm Object
     * @var Steam_Model_ProjectsForm 
     */
    var $projectForm;
    
    /**
     * Loads $baseModel data for reference and [optional] $submittedData into the form fields
     * @param type $baseModel
     * @param type $submittedData
     */
    public function __construct($baseModel, $submittedData = null) {
        $this->translator = Zend_Registry::get('Zend_Translate');      
        $this->baseModel = $baseModel;
        $this->mS = new Steam_MeasurementSystem();
        
        $this->projects = Steam_Model_Projects::listed($baseModel->headerCount); 
        $this->headerCount = $baseModel->headerCount;
        foreach($this->projects as $cat => $projects){
            foreach($projects[1] as $key => $value) {
                $this->projectContent[$key] = "Unavailable at this time";
            }
        }
        
        $this->projectForm = new Steam_Model_ProjectsForm($baseModel);
        if ($submittedData){
            $this->projectForm->isValid($submittedData);
        }
        
        $this->generateSubForms();
    }
    
    /**
     * Generates sub form components
     */
    public function generateSubForms(){      
        global $CURRENCY_SYMBOL; 
        
        $this->projectContent['Proj_operatingHours'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Operating Hours')."</th>
                <td class='pf_baseVal'>".  number_format($this->baseModel->operatingHours,0)." ".$this->mS->label('hrs')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Operating Hours')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->operatingHours->renderViewHelper()} ".$this->mS->label('hrs')."</td>";
                if ($errors =  $this->projectForm->operatingHours->renderErrors()) $this->projectContent['Proj_operatingHours'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_operatingHours'] .= "</tr>
        </table>
        ";
                
        $this->projectContent['Proj_makeupTemp'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Make-Up Water Temperature')."</th>
                <td class='pf_baseVal'>".$this->mS->displayTemperatureLabeled($this->baseModel->makeupWaterTemp)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Make-Up Water Temperature')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->makeupWaterTemp->renderViewHelper()} ".$this->mS->label('temperature')."</td>";
                if ($errors =  $this->projectForm->makeupWaterTemp->renderErrors()) $this->projectContent['Proj_makeupTemp'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_makeupTemp'] .= "</tr>
        </table>
        ";
                
        $this->projectContent['Proj_electricityUC'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Electricity Unit Cost')."</th>
                <td class='pf_baseVal'>".$CURRENCY_SYMBOL.' '.number_format($this->mS->localize($this->baseModel->sitePowerCost,'unitcost.electricity'),4)." / ".$this->mS->label('electricity')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Electricity Unit Cost')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->sitePowerCost->renderViewHelper()} ".$CURRENCY_SYMBOL.' / '.$this->mS->label('electricity')."</td>";
                if ($errors =  $this->projectForm->sitePowerCost->renderErrors()) $this->projectContent['Proj_electricityUC'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_electricityUC'] .= "</tr>
        </table>
        ";
                
        $this->projectContent['Proj_fuelUC'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Fuel Unit Cost')."</th>
                <td class='pf_baseVal'>".$CURRENCY_SYMBOL.' '.number_format($this->mS->localize($this->baseModel->fuelUnitCost,'unitcost.energy'),4)." / ".$this->mS->label('energy')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Fuel Unit Cost')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->fuelUnitCost->renderViewHelper()} ".$CURRENCY_SYMBOL.' / '.$this->mS->label('energy')."</td>";
                if ($errors =  $this->projectForm->fuelUnitCost->renderErrors()) $this->projectContent['Proj_fuelUC'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_fuelUC'] .= "</tr>
        </table>
        ";
                
        $this->projectContent['Proj_makeupwaterUC'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Make-Up Water Unit Cost')."</th>
                <td class='pf_baseVal'>".$CURRENCY_SYMBOL.' '.number_format($this->mS->localize($this->baseModel->makeupWaterCost,'unitcost.volume'),4)." / ".$this->mS->label('volume')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Make-Up Water Unit Cost')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->makeupWaterCost->renderViewHelper()} ".$CURRENCY_SYMBOL.' / '.$this->mS->label('volume')."</td>";
                if ($errors =  $this->projectForm->makeupWaterCost->renderErrors()) $this->projectContent['Proj_makeupwaterUC'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_makeupwaterUC'] .= "</tr>
        </table>
        ";
        
        
        
        $this->projectContent['Proj_steamDemand'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial HP Steam Usage')."</th>
                <td class='pf_baseVal'>".$this->mS->displayMassflowLabeled($this->baseModel->hpSteamUsage)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Steam Usage')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->hpSteamUsage->renderViewHelper()} ".$this->mS->label('massflow')."</td>";
                if ($errors =  $this->projectForm->hpSteamUsage->renderErrors()) $this->projectContent['Proj_steamDemand'] .= "<td>{$errors}</td>";
        if ($this->headerCount==3){
        $this->projectContent['Proj_steamDemand'] .= "</tr>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial MP Steam Usage')."</th>
                <td class='pf_baseVal'>".$this->mS->displayMassflowLabeled($this->baseModel->mpSteamUsage)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Steam Usage')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->mpSteamUsage->renderViewHelper()} ".$this->mS->label('massflow')."</td>";
                if ($errors =  $this->projectForm->mpSteamUsage->renderErrors()) $this->projectContent['Proj_steamDemand'] .= "<td>{$errors}</td>";
        }
        if ($this->headerCount>1){
        $this->projectContent['Proj_steamDemand'] .= "</tr>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial LP Steam Usage')."</th>
                <td class='pf_baseVal'>".$this->mS->displayMassflowLabeled($this->baseModel->lpSteamUsage)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Steam Usage')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->lpSteamUsage->renderViewHelper()} ".$this->mS->label('massflow')."</td>";
                if ($errors =  $this->projectForm->lpSteamUsage->renderErrors()) $this->projectContent['Proj_steamDemand'] .= "<td>{$errors}</td>";
        }
        $this->projectContent['Proj_steamDemand'] .= "</tr>
        </table>

        ";
        $this->projectContent['Proj_energyDemand'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial HP Energy Usage')."</th>
                <td class='pf_baseVal'>".$this->mS->displayEnergyflowLabeled($this->baseModel->energyUsageHP)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Energy Usage')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->setEnergyUsageHP->renderViewHelper()} ".$this->mS->label('energyflow')."</td>";
                if ($errors =  $this->projectForm->setEnergyUsageHP->renderErrors()) $this->projectContent['Proj_energyDemand'] .= "<td>{$errors}</td>";
        if ($this->headerCount==3){
        $this->projectContent['Proj_energyDemand'] .= "</tr>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial MP Energy Usage')."</th>
                <td class='pf_baseVal'>".$this->mS->displayEnergyflowLabeled($this->baseModel->energyUsageMP)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Energy Usage')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->setEnergyUsageMP->renderViewHelper()} ".$this->mS->label('energyflow')."</td>";
                if ($errors =  $this->projectForm->setEnergyUsageMP->renderErrors()) $this->projectContent['Proj_energyDemand'] .= "<td>{$errors}</td>";
        }
        if ($this->headerCount>1){
        $this->projectContent['Proj_energyDemand'] .= "</tr>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial LP Energy Usage')."</th>
                <td class='pf_baseVal'>".$this->mS->displayEnergyflowLabeled($this->baseModel->energyUsageLP)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Energy Usage')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->setEnergyUsageLP->renderViewHelper()} ".$this->mS->label('energyflow')."</td>";
                if ($errors =  $this->projectForm->setEnergyUsageLP->renderErrors()) $this->projectContent['Proj_energyDemand'] .= "<td>{$errors}</td>";
        }
        $this->projectContent['Proj_energyDemand'] .= "</tr>
        </table>

        ";
                
        $this->projectContent['Proj_boilerEff'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Boiler Combustion Efficiency')."</th>
                <td class='pf_baseVal'>".  number_format($this->baseModel->boilerEff,1)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Combustion Efficiency')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->boilerEff->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->boilerEff->renderErrors()) $this->projectContent['Proj_boilerEff'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_boilerEff'] .= "
            </tr>
        </table>
        ";
                
        $this->projectContent['Proj_fuelType'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Fuel Type').":</th>
                <td class='pf_baseVal'>". $this->translator->_(Steam_Fuel::fuelNames($this->baseModel->fuelType))."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Fuel Type')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->fuelType->renderViewHelper()}</td>";
                if ($errors =  $this->projectForm->fuelType->renderErrors()) $this->projectContent['Proj_fuelType'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_fuelType'] .= "</tr>
        </table>
        ";  
                
        $this->projectContent['Proj_blowdownRate'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Boiler Blowdown Rate')."</th>
                <td class='pf_baseVal'>".  number_format($this->baseModel->blowdownRate,1)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Blowdown Rate')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->blowdownRate->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->blowdownRate->renderErrors()) $this->projectContent['Proj_blowdownRate'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_blowdownRate'] .= "</tr>
        </table>
        ";
                
        $this->projectContent['Proj_blowdownFlashLP'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Flash Blowdown? Base').":</th>
                <td class='pf_baseVal'>".  $this->baseModel->blowdownFlashLP."</td>
                <th class='pf_projectNew'>".$this->translator->_('Adjusted')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->blowdownFlashLP->renderViewHelper()}</td>";
                if ($errors =  $this->projectForm->blowdownFlashLP->renderErrors()) $this->projectContent['Proj_blowdownFlashLP'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_blowdownFlashLP'] .= "</tr>
        </table>
        ";        
                
        $this->projectContent['Proj_blowdownHeatX'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Preheat Make-Up')."</th>
                <td class='pf_baseVal'>". $this->baseModel->blowdownHeatX ."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Preheat Make-Up')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->blowdownHeatX->renderViewHelper()}</td>";
                if ($errors =  $this->projectForm->blowdownHeatX->renderErrors()) $this->projectContent['Proj_blowdownHeatX'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_blowdownHeatX'] .= "</tr>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Approach Temperature')."</th>
                <td class='pf_baseVal'>".  $this->mS->displayTemperaturediffLabeled($this->baseModel->blowdownHeatXTemp)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Approach Temperature')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->blowdownHeatXTemp->renderViewHelper()} ".$this->mS->label('temperature')."</td>";
                if ($errors =  $this->projectForm->blowdownHeatXTemp->renderErrors()) $this->projectContent['Proj_blowdownHeatX'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_blowdownHeatX'] .= "</tr>
        </table>
        ";    
                
        $this->projectContent['Proj_steamGen'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Steam Temperature').":</th>
                <td class='pf_baseVal'>".  $this->mS->displayTemperatureLabeled($this->baseModel->boilerTemp)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Steam Temperature')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->boilerTemp->renderViewHelper()} ".$this->mS->label('temperature')."</td>";
                if ($errors =  $this->projectForm->boilerTemp->renderErrors()) $this->projectContent['Proj_steamGen'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_steamGen'] .= "</tr>
        </table>
        ";           
                
        $this->projectContent['Proj_daVentRate'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial DA Vent Rate')."</th>
                <td class='pf_baseVal'>".  number_format($this->baseModel->daVentRate,1)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW DA Vent Rate')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->daVentRate->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->daVentRate->renderErrors()) $this->projectContent['Proj_daVentRate'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_daVentRate'] .= "</tr>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial DA Pressure')."</th>
                <td class='pf_baseVal'>".  $this->mS->displayPressureLabeled($this->baseModel->daPressure,1)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW DA Pressure')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->daPressure->renderViewHelper()} ".$this->mS->label('pressure')."</td>";
                if ($errors =  $this->projectForm->daPressure->renderErrors()) $this->projectContent['Proj_daVentRate'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_daVentRate'] .= "</tr>
        </table>
        ";
                
                
        $this->projectContent['Proj_condTurbine'] = $this->turbineForm('Cond');
        $this->projectContent['Proj_hpMpTurbine'] = $this->turbineForm('HpMp');
        $this->projectContent['Proj_hpLpTurbine'] = $this->turbineForm('HpLp');
        $this->projectContent['Proj_mpLpTurbine'] = $this->turbineForm('MpLp');
        
        
        
        $this->projectContent['Proj_condRecovery'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial HP Condenstate Return')."</th>
                <td class='pf_baseVal'>".number_format($this->baseModel->hpCondReturnRate,1)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Condenstate Return')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->hpCondReturnRate->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->hpCondReturnRate->renderErrors()) $this->projectContent['Proj_condRecovery'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_condRecovery'] .= "</tr>";
        if ($this->headerCount==3){
        $this->projectContent['Proj_condRecovery'] .= "<tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial MP Condenstate Return')."</th>
                <td class='pf_baseVal'>".number_format($this->baseModel->mpCondReturnRate,1)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Condenstate Return')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->mpCondReturnRate->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->mpCondReturnRate->renderErrors()) $this->projectContent['Proj_condRecovery'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_condRecovery'] .= "</tr>";
        }
        if ($this->headerCount>1){
        $this->projectContent['Proj_condRecovery'] .= "<tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial LP Condenstate Return')."</th>
                <td class='pf_baseVal'>".number_format($this->baseModel->lpCondReturnRate,1)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Condenstate Return')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->lpCondReturnRate->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->lpCondReturnRate->renderErrors()) $this->projectContent['Proj_condRecovery'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_condRecovery'] .= "</tr>";
        }
        $this->projectContent['Proj_condRecovery'] .= "    
        </table>
        ";
                
        $this->projectContent['Proj_condFlashMP'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Flash Condensate to MP? Base').":</th>
                <td class='pf_baseVal'>".  $this->translator->_($this->baseModel->hpCondFlash)."</td>
                <th class='pf_projectNew'>".$this->translator->_('Adjusted')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->hpCondFlash->renderViewHelper()}</td>
            </tr>
        </table>
        ";
                
        $this->projectContent['Proj_condFlashLP'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Flash Condensate to LP? Base').":</th>
                <td class='pf_baseVal'>".  $this->translator->_($this->baseModel->mpCondFlash)."</td>
                <th class='pf_projectNew'>".$this->translator->_('Adjusted')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->mpCondFlash->renderViewHelper()}</td>
            </tr>
        </table>
        ";
                
        $this->projectContent['Proj_condReturnTemp'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Condensate Return Temperature').":</th>
                <td class='pf_baseVal'>".  $this->mS->displayTemperatureLabeled($this->baseModel->condReturnTemp)."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Condensate Return Temperature')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->condReturnTemp->renderViewHelper()} ".$this->mS->label('temperature')."</td>";
                if ($errors =  $this->projectForm->condReturnTemp->renderErrors()) $this->projectContent['Proj_condReturnTemp'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_condReturnTemp'] .= "</tr>
        </table>
        ";   
                
                
        $this->projectContent['Proj_heatLossPercent'] = "
        <table class='data' style='margin: 0px;'>
            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial HP Heat Loss')."</th>
                <td class='pf_baseVal'>".number_format($this->baseModel->hpHeatLossPercent,2)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Heat Loss')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->hpHeatLossPercent->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->hpHeatLossPercent->renderErrors()) $this->projectContent['Proj_heatLossPercent'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_heatLossPercent'] .= "</tr>";
              
        if ($this->headerCount==3){  
        $this->projectContent['Proj_heatLossPercent'] .= "<tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial MP Heat Loss')."</th>
                <td class='pf_baseVal'>".number_format($this->baseModel->mpHeatLossPercent,2)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Heat Loss')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->mpHeatLossPercent->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->mpHeatLossPercent->renderErrors()) $this->projectContent['Proj_heatLossPercent'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_heatLossPercent'] .= "</tr>";
        }
        if ($this->headerCount>1){  
        $this->projectContent['Proj_heatLossPercent'] .= "<tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial LP Heat Loss')."</th>
                <td class='pf_baseVal'>".number_format($this->baseModel->lpHeatLossPercent,2)." ".$this->mS->label('%')."</td>
                <th class='pf_projectNew'>".$this->translator->_('NEW Heat Loss')."*</th>
                <td class='pf_adjustedVal'>{$this->projectForm->lpHeatLossPercent->renderViewHelper()} ".$this->mS->label('%')."</td>";
                if ($errors =  $this->projectForm->lpHeatLossPercent->renderErrors()) $this->projectContent['Proj_heatLossPercent'] .= "<td>{$errors}</td>";
        $this->projectContent['Proj_heatLossPercent'] .= "</tr>";
        }
        $this->projectContent['Proj_heatLossPercent'] .= "</table>
        ";
    }
    
    /**
     * Returns the form layout components for a single turbine
     * @param string $turbineType
     * @return string
     */
    private function turbineForm($turbineType){
        
        $turbineStatus = array(0 => 'Off', 1 => 'On');
        $turbineMethods = array(
            'balanceHeader' => 'Balance Header',
            'fixedFlow' => 'Steam Flow',
            'flowRange' => 'Flow Range',
            'fixedPower' => 'Power Generation',
            'powerRange' => 'Power Range',
            );
        
        $turbineOn = 'turbine'.$turbineType.'On';
        $turbineIsoEff = 'turbine'.$turbineType.'IsoEff';
        $turbineGenEff = 'turbine'.$turbineType.'GenEff';
        $turbineMethod = 'turbine'.$turbineType.'Method';
        $turbineFixedFlow = 'turbine'.$turbineType.'FixedFlow';
        $turbineMinFlow = 'turbine'.$turbineType.'MinFlow';
        $turbineMaxFlow = 'turbine'.$turbineType.'MaxFlow';
        
        $turbineFixedPower = 'turbine'.$turbineType.'FixedPower';
        $turbineMinPower = 'turbine'.$turbineType.'MinPower';
        $turbineMaxPower = 'turbine'.$turbineType.'MaxPower';
        
        $turbineForm = "
        <table class='data' style='margin: 0px;'>

            <tr>
                <th class='pf_projectOld'>".$this->translator->_('Initial Turbine Status')."</th>
                <td class='pf_baseVal'>".$this->translator->_($turbineStatus[$this->baseModel->$turbineOn])."</td>
                <th class='pf_projectNew'>".$this->translator->_('Adjusted Status')."*</th>
                <td class='pf_adjustedVal' >{$this->projectForm->$turbineOn->renderViewHelper()} <span style='margin-bottom: 41px;'>".$this->translator->_('On/Off')."</span></td>
            </tr>
            <tr>";
                if ($this->baseModel->$turbineOn==1){
                    $turbineForm .= "                
                        <th class='pf_projectOld'>".$this->translator->_('Isentropic Efficiency')."</th>
                        <td class='pf_baseVal'>".number_format($this->baseModel->$turbineIsoEff,1)." ".$this->mS->label('%')."</td>";
                }else{
                    $turbineForm .= "<td id ='turbine{$turbineType}DetailsSpacer' colspan=2 rowspan=4 style='padding:0px; margin:0px; font-size: 0px;'>blank</td>";
                }            
                
                $turbineForm .= "                
                <td id ='turbine{$turbineType}Details' colspan=2 rowspan=5 style='padding:0px;'>
                    <table class='data' style='margin:0px; font-size: 1em; border: 1px;'>
                        <tr>
                            <th class='pf_projectNew'>".$this->translator->_('Isentropic Efficiency')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineIsoEff->renderViewHelper()} ".$this->mS->label('%')."</td>";
                        if ($errors =  $this->projectForm->$turbineIsoEff->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "</tr>
                        <tr>
                            <th class='pf_projectNew'>".$this->translator->_('Generation Efficiency')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineGenEff->renderViewHelper()} ".$this->mS->label('%')."</td>";
                        if ($errors =  $this->projectForm->$turbineGenEff->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        
                        
                        if ($turbineType=='Cond'){
                            $turbineForm .= "                        
                                </tr><tr>
                                    <th class='pf_projectNew'>".$this->translator->_('Condenser Pressure')."*</th>
                                    <td class='pf_adjustedVal'>".$this->projectForm->turbineCondOutletPressure->renderViewHelper()." ".$this->mS->label('vacuum')."</td>";
                            if ($errors =  $this->projectForm->turbineCondOutletPressure->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        }
                        
                        $turbineForm .= "</tr>
                        <tr>                        
                            <th class='pf_projectNew' colspan=2 style='text-align: center;'>".$this->translator->_('Operation')."* {$this->projectForm->$turbineMethod->renderViewHelper()}</th>            
                        </tr>
                        <tr id='sf_turbine{$turbineType}FixedFlow'>
                            <th class='pf_projectNew'>".$this->translator->_('Fixed Flow')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineFixedFlow->renderViewHelper()} ".$this->mS->label('massflow')."</td>";
                        if ($errors =  $this->projectForm->$turbineFixedFlow->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "
                        </tr>
                        <tr id='sf_turbine{$turbineType}MinFlow'>
                            <th class='pf_projectNew'>".$this->translator->_('Minimum Flow')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineMinFlow->renderViewHelper()} ".$this->mS->label('massflow')."</td>";
                        if ($errors =  $this->projectForm->$turbineMinFlow->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "
                        </tr>
                        <tr id='sf_turbine{$turbineType}MaxFlow'>
                            <th class='pf_projectNew'>".$this->translator->_('Maximum Flow')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineMaxFlow->renderViewHelper()} ".$this->mS->label('massflow')."</td>";
                        if ($errors =  $this->projectForm->$turbineMaxFlow->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "
                        </tr>
                        <tr id='sf_turbine{$turbineType}FixedPower'>
                            <th class='pf_projectNew'>".$this->translator->_('Fixed Power')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineFixedPower->renderViewHelper()} ".$this->mS->label('power')."</td>";
                        if ($errors =  $this->projectForm->$turbineFixedPower->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "
                        </tr>
                        <tr id='sf_turbine{$turbineType}MinPower'>
                            <th class='pf_projectNew'>".$this->translator->_('Minimum Power')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineMinPower->renderViewHelper()} ".$this->mS->label('power')."</td>";
                        if ($errors =  $this->projectForm->$turbineMinPower->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "
                        </tr>
                        <tr id='sf_turbine{$turbineType}MaxPower'>
                            <th class='pf_projectNew'>".$this->translator->_('Maximum Power')."*</th>
                            <td class='pf_adjustedVal'>{$this->projectForm->$turbineMaxPower->renderViewHelper()} ".$this->mS->label('power')."</td>";
                        if ($errors =  $this->projectForm->$turbineMaxPower->renderErrors()) $turbineForm .= "<td>{$errors}</td>";
                        $turbineForm .= "
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>";
 
                if ($this->baseModel->$turbineOn==1){
                    $turbineForm .= "
                        <th class='pf_projectOld'>".$this->translator->_('Generation Efficiency')."</th>
                        <td class='pf_baseVal'>".number_format($this->baseModel->$turbineGenEff,1)." ".$this->mS->label('%')."</td>
                    </tr>";
                    
                    if ($turbineType=='Cond'){      
                    $turbineForm .= "                    
                    <tr>
                        <th class='pf_projectOld'>".$this->translator->_('Condenser Pressure')."*</th>
                        <td class='pf_baseVal'>".$this->mS->displayVacuumLabeled($this->baseModel->turbineCondOutletPressure)."</td>
                    </tr>";
                    }
                    $turbineForm .= "
                    <tr>
                        <th class='pf_projectOld'>".$this->translator->_('Operation')."</th>
                        <td class='pf_baseVal'>".$this->translator->_($turbineMethods[$this->baseModel->$turbineMethod])."</td>";
                }
                $turbineForm .= "
            </tr>
            <tr>";
                if ($this->baseModel->$turbineOn==1){
                    switch($this->baseModel->$turbineMethod){
                        case 'fixedFlow':
                            $turbineForm .= "
                            <th class='pf_projectOld'>".$this->translator->_('Fixed Flow')."</th>
                            <td class='pf_baseVal'>".$this->mS->displayMassflowLabeled($this->baseModel->$turbineFixedFlow)."</td>
                                ";
                            break;
                        case 'flowRange':
                            $turbineForm .= "
                            <th class='pf_projectOld'>".$this->translator->_('Minimum Flow')."</th>
                            <td class='pf_baseVal'>".$this->mS->displayMassflowLabeled($this->baseModel->$turbineMinFlow)."</td>
                                ";
                            break;
                        case 'fixedPower':
                            $turbineForm .= "
                            <th class='pf_projectOld'>".$this->translator->_('Fixed Power')."</th>
                            <td class='pf_baseVal'>".$this->mS->displayPowerLabeled($this->baseModel->$turbineFixedPower)."</td>
                                ";
                            break;
                        case 'powerRange':
                            $turbineForm .= "
                            <th class='pf_projectOld'>".$this->translator->_('Minimum Power')."</th>
                            <td class='pf_baseVal'>".$this->mS->displayPowerLabeled($this->baseModel->$turbineMinPower)."</td>
                                ";
                            break;
                        default:
                            $turbineForm .= "<td id ='turbine{$turbineType}DetailsSpacer' colspan='2' rowspan='2' style='padding:0px; margin:0px; font-size: 0px;'>asdf1</td>";
                            break;
                    }
                }
                $turbineForm .= "
            </tr>
            <tr>";
                
                if ($this->baseModel->$turbineOn==1){
                switch($this->baseModel->$turbineMethod){       
                    case 'flowRange':
                        $turbineForm .= "
                        <th class='pf_projectOld'>".$this->translator->_('Maximum Flow')."</th>
                        <td class='pf_baseVal'>".$this->mS->displayMassflowLabeled($this->baseModel->$turbineMaxFlow)."</td>
                            ";
                        break;    
                    case 'powerRange':
                        $turbineForm .= "
                        <th class='pf_projectOld'>".$this->translator->_('Maximum Power')."</th>
                        <td class='pf_baseVal'>".$this->mS->displayPowerLabeled($this->baseModel->$turbineMaxPower)."</td>
                            ";
                        break; 
                    case 'fixedFlow':
                    case 'fixedPower':
                        $turbineForm .= "<td id ='turbine{$turbineType}DetailsSpacer' colspan='2' rowspan='1' style='padding:0px; margin:0px; font-size: 0px;'>asdf2</td>";
                        break;
                    default:
                        break;
                }
                }
                $turbineForm .= "

            </tr>
   
        </table>
        ";
        return $turbineForm;
    }
    
    /**
     * Lists all projects based on header count
     * @param int $headerCount
     * @return array
     */
    static public function listed($headerCount = 3){         
         $fullList = array(
            'Cat_General' => array(
                'Adjust General Operation',
                array(
                    'Proj_operatingHours' => "Modify Operating Hours",
                    'Proj_makeupTemp' => "Modify Make-Up Water Temperature",
                )),
            'Cat_Unitcost' => array(
                'Adjust Unit Costs',
                array(
                    'Proj_electricityUC' => "Modify Electricity Unit Cost",
                    'Proj_fuelUC' => "Modify Fuel Unit Cost",
                    'Proj_makeupwaterUC' => "Modify Make-Up Unit Cost",
                )),
            'Cat_Demand' => array(
                'Adjust Steam Demand (only 1 may be selected)',
                array(
                    'Proj_steamDemand' => "Modify Process Steam Demand/Usage",
                    'Proj_energyDemand' => "Modify Process Energy Demand",
                )),
            'Cat_Boiler' => array(
                'Adjust Boiler Operation',
                array(
                    'Proj_boilerEff' => "Change Boiler Combustion Efficiency",
                    'Proj_fuelType' => "Change Fuel Type",
                    'Proj_blowdownRate' => "Change Boiler Blowdown Rate",
                    'Proj_blowdownFlashLP' => "Blowdown Flash to LP",
                    'Proj_blowdownHeatX' => "Preheat Make-Up Water with Blowdown",
                    'Proj_steamGen' => "Change Steam Generation Conditions",
                    'Proj_daVentRate' => "Change DA Operating Conditions",
                    
                )),
            'Cat_Turbine' => array(
                'Adjust Steam Turbine Operation',
                array(
                    'Proj_condTurbine' => "Modify HP to Condensing Steam Turbine",
                    'Proj_hpLpTurbine' => "Modify HP to LP Steam Turbine",
                    'Proj_hpMpTurbine' => "Modify HP to MP Steam Turbine",
                    'Proj_mpLpTurbine' => "Modify MP to LP Steam Turbine",
                )),
            'Cat_Condensate' => array(
                'Adjust Condensate Handling',
                array(
                    'Proj_condRecovery' => "Condensate Recovery",
                    'Proj_condFlashMP' => "Condensate Flash to MP",
                    'Proj_condFlashLP' => "Condensate Flash to LP",
                    'Proj_condReturnTemp' => "Modify Condensate Return Temperature",
                )),
            'Cat_Other' => array(
                'Adjust Insulation / Heat Loss',
                array(
                    'Proj_heatLossPercent' => "Adjust Heat Loss Percentage",
                )),
        );
        if ($headerCount<3){
            unset($fullList['Cat_Turbine'][1]['Proj_hpMpTurbine']);
            unset($fullList['Cat_Turbine'][1]['Proj_mpLpTurbine']);
            unset($fullList['Cat_Condensate'][1]['Proj_condFlashMP']);
        }
        if ($headerCount<2){
            unset($fullList['Cat_Turbine'][1]['Proj_hpLpTurbine']);
            unset($fullList['Cat_Condensate'][1]['Proj_condFlashLP']);
            unset($fullList['Cat_Boiler'][1]['Proj_blowdownFlashLP']);
        }
        return $fullList;
    }    
}