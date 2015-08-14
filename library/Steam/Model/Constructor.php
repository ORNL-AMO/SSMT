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
 * Constructs Steam Model
 * 
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Constructor{
    
    //Entered Values
    var $mS;
    var $headerCount;
    
    //Initialize Key Derived Public Variables
    public $sitePowerImport;
    public $sitePowerCost;
    public $operatingHours;
    public $makeupWaterCost;
    public $makeupWaterTemp;
    public $highPressure;
    public $mediumPressure;
    public $lowPressure;
    public $hpSteamUsage;
    public $mpSteamUsage;
    public $lpSteamUsage;
    public $boilerEff;
    public $blowdownRate;
    public $blowdownFlashLP;
    public $superheatTemp;
    public $hpheatLossPercent;
    public $mpheatLossPercent;
    public $lpheatLossPercent;
    public $feedwaterTemp;
    public $siteTotalPowerCost;
    public $steamProduction;
    
    //$count the number of iterations to convergence on a solution
    public $count = array(
        'iterations' => 0
    );
    public $adjustments = array();
    
    /**
     * The Tolerance required for a solution to be found
     */
    const Tolerance = .0001; //kg/hr;

    /**
     * @var Steam_Equipment_Header
     */
    var $hpHeader;
    /**
     * @var Steam_Equipment_Header
     */
    var $mpHeader;
    /**
     * @var Steam_Equipment_Header
     */
    var $lpHeader;
    /**
     * @var Steam_Object
     */
    var $hpCondInitial;
    /**
     * @var Steam_Object
     */
    var $mpCondInitial;
    /**
     * @var Steam_Object
     */
    var $lpCondInitial;
    /**
     * @var Steam_Object
     */
    var $condensate;
    /**
     * @var Steam_Object
     */
    var $turbineCondSteamCooled;
    /**    
     * @var Steam_Equipment_Boiler
     */
    var $boiler;
    /**
     * @var Steam_Object
     */
    var $blowdown;
    /**
     * @var Steam_Equipment_FlashTank
     */
    var $blowdownFlashTank;
    /**
     * Is Cond Turbine On?
     * @var bool
     */
    var $turbineCondOn;   
    /**    
     * @var Steam_Equipment_Turbine
     */
    var $turbineCondModel; 
    /**    
     * @var Steam_Equipment_Turbine
     */
    var $turbineHpMpModel;  
    /**
     * @var Steam_Equipment_Turbine
     */
    var $turbineHpLpModel;
    /**
     * @var Steam_Equipment_PRV
     */
    var $hpTmpPRV;
    /**
     * @var Steam_Equipment_PRV
     */
    var $mpTlpPRV;
    /**
     * @var Steam_Equipment_Turbine
     */
    var $turbineMpLpModel;
    
    //Setup interation timestamp arrays
    var $runStartTime = array();
    var $runEndTime = array();
    
    /**
     * @var Steam_Object
     */
    var $makeupWater;
    /**
     * @var float
     */
    var $forcedExcessSteamMptoLP = 0;
    /**
     * Additional Steam Flow
     * @var float
     */
    var $additionalSteamFlow;
    /**
     * @var array
     */
    var $warnings = array();
        
    /**
     * HP Marginal Steam Cost
     * @var float
     */
    var $marginalCostHP = null;
    /**
     * MP Marginal Steam Cost
     * @var float
     */
    var $marginalCostMP = null;
    /**
     * LP Marginal Steam Cost
     * @var float
     */
    var $marginalCostLP = null;
    /**
     * Has marginal steam cost been calculated
     * @var boolean
     */
    var $marginalCostCalculated = false;
    
    function __construct($properties, $initialGenerated = -999999.999) {
        $this->mS = new Steam_MeasurementSystem();

        $this->startTime = microtime(true);
    
        $this->initialGenerated = $initialGenerated;
                
        //Clean & Filter Properties for selected headerCount
        $properties = Steam_Support::headerAdjustment($properties);
        foreach($properties as $type => $value){
            $this->$type = $value;
        }
        //Fix Header Energy Usage if set
        if (isset($properties['energyUsageFixed']) and $properties['energyUsageFixed']){
            $this->energyUsageFixed = $properties['energyUsageFixed'];            
            $this->setEnergyUsageHP = $properties['setEnergyUsageHP'];
            $this->setEnergyUsageMP = $properties['setEnergyUsageMP'];
            $this->setEnergyUsageLP = $properties['setEnergyUsageLP'];
        }else{
            $this->energyUsageFixed = false;
        }
                       
        //Run model
        $this->initializeSteamProperties();                                      
        $this->additionalSteamFlow = $this->iterateModel();        
        
        $this->finalizeModel();
        $this->checkWarnings();
        
        $this->endTime = microtime(true);
    
        //Calculate energy usage
        $this->energyUsageHP = $this->hpSteamUsage*($this->hpHeader->remainingSteam->specificEnthalpy - $this->satLiqEnthalpyHP)/1000;
        $this->energyUsageMP = $this->mpSteamUsage*($this->mpHeader->remainingSteam->specificEnthalpy - $this->satLiqEnthalpyMP)/1000;
        $this->energyUsageLP = $this->lpSteamUsage*($this->lpHeader->remainingSteam->specificEnthalpy - $this->satLiqEnthalpyLP)/1000;        
    }
    
    /**
     * Iterate model until proper steam flow adjustment is found
     * @return float $additionalSteamFlow
     */
    private function iterateModel(){        
        //Determine initial adjustment
        $additionalSteamFlow = $this->steamToDA;
        if ( $additionalSteamFlow==0 ) $additionalSteamFlow = 1;        
        if ($this->additionalSteamFlow) $additionalSteamFlow = $this->additionalSteamFlow;

        $this->debug = array();
        $adjustment = $this->convergeAdjustment($additionalSteamFlow, .01);
        
        $cc=0;
        $this->debug = array();
        while ( abs($adjustment)>1e-5 and $cc++<50){                        
            $this->debug[$cc] = array();
            $adjustment = $this->convergeAdjustment($additionalSteamFlow);

            switch ($cc){
                case 1:
                    $y1= $additionalSteamFlow;
                    $x1 = $adjustment;
                    break;
                case 2:
                    $y2= $additionalSteamFlow;
                    $x2 = $adjustment;
                    break;
                default:                            
                    //Set New Test Point
                    $yNew = $additionalSteamFlow;
                    $xNew = $adjustment;

                    //Select Closest Old Test Point
                    $y1diff = abs($y1-$yNew);
                    $y2diff = abs($y2-$yNew);
                    if ($y1diff<$y2diff){
                        $y2 = $yNew;
                        $x2 = $xNew;
                    }else{
                        $y2 = $y1;
                        $x2 = $x1;    
                        $y1 = $yNew;
                        $x1 = $xNew;
                    }
                    break;
            }

            //User Linear Interpolation to determine new adjustment
            if ( isset($y1) and isset($y2) ){
                if ($x2==$x1){    
                    $adjustment = $this->convergeAdjustment($additionalSteamFlow+=$adjustment);
                    break;
                }
                $slope = ($y2-$y1)/($x2-$x1);
                $yIntercept = $y2-$x2*$slope;
                if (($cc>10 and $cc%5==0) or (isset($lastSlope) and ($slope ==0 or $lastSlope/$slope<0) ) ){
                    $additionalSteamFlow += $adjustment;    
                }else{
                    $additionalSteamFlow = $yIntercept;      
                }
                $lastSlope = $slope;
            }else{
                $additionalSteamFlow += $adjustment;
            }
            if (is_nan($adjustment) ) break;
        }
        if ($this->checkWarnings()>0) $adjustment = $this->convergeAdjustment($additionalSteamFlow);
        $this->totalRuns = $cc;
        return $additionalSteamFlow;
    }

    /**
     * Calculate Marginal Steam Costs
     */
    public function calculateMarginalCosts(){
        $setting_energyUsageFixed = $this->energyUsageFixed;
        $this->energyUsageFixed = false;
        
        $setting_initialGenerated = $this->initialGenerated;
        $this->initialGenerated = -999999.999;      
        $this->finalizeModel(); 
        
        $steamAdjustment = 100;
        $totalOC = $this->totalOperatingCosts/$this->operatingHours;        
        $powerGenOC = $this->powerGenerated*$this->sitePowerCost;

        $this->hpSteamUsage+= $steamAdjustment;
        $this->iterateModel();
        $this->finalizeModel();
        $totalOCintHP = $this->totalOperatingCosts/$this->operatingHours;        
        $powerGenOCintHP = $this->powerGenerated*$this->sitePowerCost;   
        $this->hpSteamUsage-= $steamAdjustment;
        $this->marginalCostHP = (( ($totalOCintHP-$totalOC))+($powerGenOC-$powerGenOCintHP))/$steamAdjustment ;
        
        if ($this->headerCount>2){
            $this->mpSteamUsage+= $steamAdjustment;
            $this->iterateModel();
            $this->finalizeModel();
            $totalOCintMP = $this->totalOperatingCosts/$this->operatingHours;        
            $powerGenOCintMP = $this->powerGenerated*$this->sitePowerCost;
            $this->mpSteamUsage-= $steamAdjustment;
            $this->marginalCostMP = (( ($totalOCintMP-$totalOC))+($powerGenOC-$powerGenOCintMP))/$steamAdjustment;
        }
        if ($this->headerCount>1){
            $this->lpSteamUsage+= $steamAdjustment;
            $this->iterateModel();
            $this->finalizeModel();
            $totalOCintLP = $this->totalOperatingCosts/$this->operatingHours;        
            $powerGenOCintLP = $this->powerGenerated*$this->sitePowerCost;
            $this->lpSteamUsage-= $steamAdjustment;
            $this->marginalCostLP = (( ($totalOCintLP-$totalOC))+($powerGenOC-$powerGenOCintLP))/$steamAdjustment;
        }        
        
        $this->energyUsageFixed = $setting_energyUsageFixed;   
        $this->initialGenerated = $setting_initialGenerated;
        
        $this->iterateModel();
        $this->finalizeModel();
        $this->marginalCostCalculated = true;
    }
    
    /**
     * runModel until $adjustment value returned converges below $req value
     * @param float $additionalSteamFlow 
     * @param float $req Required Value
     * @return float $adjustment
     */
    private function convergeAdjustment($additionalSteamFlow, $req = .5){
        $adjustment = 1;    
        $adjustmentLast = 0;
        $cc = 0;
        while(  $adjustment<>0 //Break on no adjustment                
                and abs(($adjustment-$adjustmentLast)/$adjustment)>$req and $cc++<15 
                and abs($adjustment)<>abs($adjustmentLast) //Break on adjustment Flipping
                ){
            $this->debug[count($this->debug)][$cc] = $cc;
            $adjustmentLast = $adjustment;
            $adjustment = $this->runModel($additionalSteamFlow);                
            if (is_nan($adjustment) ) break;
        }
        return $adjustment;
    }
    
    /**
     * Clean up and finalize model data
     */
    private function finalizeModel(){
        if (abs($this->hpTmpPRV->inletSteam->massFlow)<1e-2) $this->hpTmpPRV->inletSteam->setMassFlow (0);
        if (abs($this->mpTlpPRV->inletSteam->massFlow)<1e-2) $this->mpTlpPRV->inletSteam->setMassFlow (0);
        
        
        if (abs($this->turbineHpLpModel->massFlow)<1e-4) $this->turbineHpLpModel->setMassFlow (0);
        if (abs($this->turbineHpMpModel->massFlow)<1e-4) $this->turbineHpMpModel->setMassFlow (0);
        if (abs($this->turbineMpLpModel->massFlow)<1e-4) $this->turbineMpLpModel->setMassFlow (0);
                
        $this->powerGenerated = $this->turbineCondModel->powerOut
                +$this->turbineHpMpModel->powerOut
                +$this->turbineHpLpModel->powerOut
                +$this->turbineMpLpModel->powerOut;
        
        $this->increasedGeneration = 0;
        if ($this->initialGenerated<>-999999.999) {           
            $this->increasedGeneration = $this->powerGenerated-$this->initialGenerated;
        }
        
        $this->newSitePowerImport = $this->sitePowerImport-$this->increasedGeneration;
        
        $this->boilerTotalFuelCost = $this->boiler->fuelEnergy * $this->operatingHours*$this->fuelUnitCost;
        $this->makeupWaterTotalCost = 
                $this->makeupWaterCost
                *$this->makeupWater->volumeFlow
                *$this->operatingHours;
        $this->totalOperatingCosts =
                    $this->sitePowerCost*$this->newSitePowerImport*$this->operatingHours
                    +$this->boilerTotalFuelCost
                    +$this->makeupWaterTotalCost;        
        
        $this->totalEnergyUsage = ($this->newSitePowerImport*3.1+$this->boiler->fuelEnergy) * $this->operatingHours;
        
        $this->co2Emissions = Steam_Fuel::co2Emissions($this->fuelType, $this->boiler->fuelEnergy*$this->operatingHours);
    }
    
    /**
     * Display model build details for developement and debug only
     */
    public function displayModelBuildDetails(){
       var_dump($this->endTime-$this->startTime);
        
        echo "<table class='data'>
            <tr>
                <th>int</th>
                <th>runtime</th>
                <th>runAdjusted1 [raw]</th>
                <th>runAdjusted1</th>
                <th>runAdjusted2</th>
                <th>runNeeded</th>
                <th>prvSteamRequirement</th>
                <th>daSteamDifference</th>
            </tr>
            ";
        foreach($this->runStartTime as $key => $value){
            echo "<tr><td>".$key."</td>";
            echo "<td>".($this->runEndTime[$key]-$value)."</td>";
            echo "<td>".($this->runAdjusted1[$key])."</td>";
            echo "<td>".($this->mS->localize($this->runAdjusted1[$key],'massflow'))."</td>";
            echo "<td>".($this->mS->localize($this->runAdjusted2[$key],'massflow'))."</td>";
            echo "<td>".($this->mS->localize($this->runNEEDED[$key],'massflow'))."</td>";
            echo "<td>".($this->mS->localize($this->runDetails['prvSteamRequirement'][$key],'massflow'))."</td>";
            echo "<td>".($this->mS->localize($this->runDetails['daSteamDifference'][$key],'massflow'))."</td>";
        }
        echo "</table>";
        var_dump($this->hpSteamUsage);        
    }

    /**
     * Check Boiler Model for Warnings
     * @return int Warning Count
     */
    function checkWarnings(){        
        $this->warnings = array();

        //Check Steam Balance
        $steamBalance = new Steam_Model_Balance($this);
        foreach($steamBalance->sB as $key => $component){
            $energyFlow = array_sum($component['eF']);
            $massFlow = array_sum($component['mF']);
            
            if (abs($energyFlow)>10) $this->warnings[] = "ERROR Energy Flow for {$steamBalance->components[$key]} NOT BALANCED [".$this->mS->displayMassflow($energyFlow)."]";
            if (abs($massFlow)>1e-1) $this->warnings[] = "ERROR Mass Flow for {$steamBalance->components[$key]} NOT BALANCED [".$this->mS->localize($massFlow,'massflow')."]";
        }
        if ($this->hpTmpPRV->inletSteam->massFlow > 1e-3
                and $this->mpTlpPRV->inletSteam->massFlow > 1e-3
                and $this->lpSteamVent > 1e-3) $this->warnings[] = "Steam Vent open from PRVs through boiler [".$this->mS->localize($this->lpSteamVent,'massflow')."]";
        
        
        //Check Negative Flow
        if ($this->hpTmpPRV->inletSteam->massFlow<0) $this->warnings[] = "HP to MP PRV Steam Flow Negative [".$this->mS->localize($this->hpTmpPRV->inletSteam->massFlow,'massflow')."]";
        if ($this->mpTlpPRV->inletSteam->massFlow<0) $this->warnings[] = "MP to LP PRV Steam Flow Negative [".$this->mS->localize($this->mpTlpPRV->inletSteam->massFlow,'massflow')."]";        
        if ($this->makeupWater->massFlow<0) $this->warnings[] = "Make Up Water Mass Flow Negative [".$this->mS->localize($this->makeupWater->massFlow,'massflow')."]";
        
        if ($this->turbineCondModel->inletSteam->massFlow<0) $this->warnings[] = "Cond Steam Negative [".$this->mS->localize($this->turbineCondModel->inletSteam->massFlow,'massflow')."]";
        if ($this->turbineHpLpModel->inletSteam->massFlow<0) $this->warnings[] = "HpLp Steam Negative [".$this->mS->localize($this->turbineHpLpModel->inletSteam->massFlow,'massflow')."]";
        if ($this->turbineHpMpModel->inletSteam->massFlow<0) $this->warnings[] = "HpMp Steam Negative [".$this->mS->localize($this->turbineHpMpModel->inletSteam->massFlow,'massflow')."]";
        if ($this->turbineMpLpModel->inletSteam->massFlow<0) $this->warnings[] = "MpLp Steam Negative [".$this->mS->localize($this->turbineMpLpModel->inletSteam->massFlow,'massflow')."]";        
        
        if ($this->turbineCondOn<>1 and $this->turbineCondModel->massFlow<>0) $this->warnings[] = "Cond Steam Turbine has Mass flow when it is off.";
        if ($this->turbineHpLpOn<>1 and $this->turbineHpLpModel->massFlow<>0) $this->warnings[] = "HpLp Steam Turbine has Mass flow when it is off.";
        if ($this->turbineHpMpOn<>1 and $this->turbineHpMpModel->massFlow<>0) $this->warnings[] = "HpMp Steam Turbine has Mass flow when it is off.";
        if ($this->turbineMpLpOn<>1 and $this->turbineMpLpModel->massFlow<>0) $this->warnings[] = "MpLp Steam Turbine has Mass flow when it is off.";
        
        if ($this->turbineHpLpOn){
            $turbineHpLpRequiredFlow = 0;
            $turbineHpLpMaxFlow = false;
            $turbineHpLpBalancing = false;
            if ($this->turbineHpLpMethod == 'fixedFlow'){
                $turbineHpLpRequiredFlow = $this->turbineHpLpFixedFlow;
                $turbineHpLpMaxFlow = $this->turbineHpLpFixedFlow;
                if ($this->turbineHpLpModel->massFlow<>$this->turbineHpLpFixedFlow) $this->warnings[] = "HpLp Steam Turbine 'fixed flow' not fixed.";                
            }
            if ($this->turbineHpLpMethod == 'fixedPower'){
                $turbineHpLpRequiredFlow = $this->turbineHpLpModel->massFlow;
                $turbineHpLpMaxFlow = $this->turbineHpLpModel->massFlow;
                if (abs($this->turbineHpLpModel->powerOut-$this->turbineHpLpFixedPower)>1e-10) $this->warnings[] = "HpLp Steam Turbine 'fixed power' not fixed.";                
            }
            if ($this->turbineHpLpMethod == 'flowRange'){
                $turbineHpLpRequiredFlow = $this->turbineHpLpModel->minFlow;
                $turbineHpLpMaxFlow = $this->turbineHpLpModel->maxFlow;
                if ($this->turbineHpLpModel->minFlow>$this->turbineHpLpModel->massFlow 
                    or $this->turbineHpLpModel->maxFlow<$this->turbineHpLpModel->massFlow) $this->warnings[] = "HpLp Steam Turbine 'flow range' not in range.";
            }
            if ($this->turbineHpLpMethod == 'powerRange'){                
                $turbineHpLpRequiredFlow = $this->turbineHpLpModel->minFlow;
                $turbineHpLpMaxFlow = $this->turbineHpLpModel->maxFlow;
                if ($this->turbineHpLpMinPower>$this->turbineHpLpModel->powerOut+1e-10
                    or $this->turbineHpLpMaxPower<$this->turbineHpLpModel->powerOut-1e-10) $this->warnings[] = "HpLp Steam Turbine 'power range' not in range.";
            }
        }
        
        if ($this->turbineHpMpOn){
            $turbineHpMpRequiredFlow = 0;
            $turbineHpMpMaxFlow = false;
            $turbineHpMpBalancing = false;
            if ($this->turbineHpMpMethod == 'fixedFlow'){
                $turbineHpMpRequiredFlow = $this->turbineHpMpFixedFlow;
                $turbineHpMpMaxFlow = $this->turbineHpMpFixedFlow;
                if ($this->turbineHpMpModel->massFlow<>$this->turbineHpMpFixedFlow) $this->warnings[] = "HpMp Steam Turbine 'fixed flow' not fixed.";                
            }
            if ($this->turbineHpMpMethod == 'fixedPower'){
                $turbineHpMpRequiredFlow = $this->turbineHpMpModel->massFlow;
                $turbineHpMpMaxFlow = $this->turbineHpMpModel->massFlow;
                if (abs($this->turbineHpMpModel->powerOut-$this->turbineHpMpFixedPower)>1e-10) $this->warnings[] = "HpMp Steam Turbine 'fixed power' not fixed.";                
            }
            if ($this->turbineHpMpMethod == 'flowRange'){
                $turbineHpMpRequiredFlow = $this->turbineHpMpModel->minFlow;
                $turbineHpMpMaxFlow = $this->turbineHpMpModel->maxFlow;
                if ($this->turbineHpMpModel->minFlow>$this->turbineHpMpModel->massFlow 
                    or $this->turbineHpMpModel->maxFlow<$this->turbineHpMpModel->massFlow) $this->warnings[] = "HpMp Steam Turbine 'flow range' not in range.";
            }
            if ($this->turbineHpMpMethod == 'powerRange'){                
                $turbineHpMpRequiredFlow = $this->turbineHpMpModel->minFlow;
                $turbineHpMpMaxFlow = $this->turbineHpMpModel->maxFlow;
                if ($this->turbineHpMpMinPower>$this->turbineHpMpModel->powerOut+1e-10
                    or $this->turbineHpMpMaxPower<$this->turbineHpMpModel->powerOut-1e-10) $this->warnings[] = "HpMp Steam Turbine 'power range' not in range.";
            }
            if ( (!$turbineHpMpMaxFlow or $this->turbineHpMpModel->massFlow<$turbineHpMpMaxFlow) and $this->hpTmpPRV->inletSteam->massFlow>0) $this->warnings[] = "HpMp Steam Turbine Open yet HpMp PRV has flow";
        }
        
        if ($this->turbineMpLpOn){
            $turbineMpLpRequiredFlow = 0;
            $turbineMpLpMaxFlow = false;
            $turbineMpLpBalancing = false;
            if ($this->turbineMpLpMethod == 'fixedFlow'){
                $turbineMpLpRequiredFlow = $this->turbineMpLpFixedFlow;
                $turbineMpLpMaxFlow = $this->turbineMpLpFixedFlow;
                if ($this->turbineMpLpModel->massFlow<>$this->turbineMpLpFixedFlow) $this->warnings[] = "MpLp Steam Turbine 'fixed flow' not fixed.";                
            }
            if ($this->turbineMpLpMethod == 'fixedPower'){
                $turbineMpLpRequiredFlow = $this->turbineMpLpModel->massFlow;
                $turbineMpLpMaxFlow = $this->turbineMpLpModel->massFlow;
                if (abs($this->turbineMpLpModel->powerOut-$this->turbineMpLpFixedPower)>1e-10) $this->warnings[] = "MpLp Steam Turbine 'fixed power' not fixed.";                
            }
            if ($this->turbineMpLpMethod == 'flowRange'){
                $turbineMpLpRequiredFlow = $this->turbineMpLpModel->minFlow;
                $turbineMpLpMaxFlow = $this->turbineMpLpModel->maxFlow;
                if ($this->turbineMpLpModel->minFlow>$this->turbineMpLpModel->massFlow 
                    or $this->turbineMpLpModel->maxFlow<$this->turbineMpLpModel->massFlow) $this->warnings[] = "MpLp Steam Turbine 'flow range' not in range.";
            }
            if ($this->turbineMpLpMethod == 'powerRange'){                
                $turbineMpLpRequiredFlow = $this->turbineMpLpModel->minFlow;
                $turbineMpLpMaxFlow = $this->turbineMpLpModel->maxFlow;
                if ($this->turbineMpLpMinPower>$this->turbineMpLpModel->powerOut+1e-10
                    or $this->turbineMpLpMaxPower<$this->turbineMpLpModel->powerOut-1e-10) $this->warnings[] = "MpLp Steam Turbine 'power range' not in range.";
            }
            if ( (!$turbineMpLpMaxFlow or $this->turbineMpLpModel->massFlow<$turbineMpLpMaxFlow) and $this->mpTlpPRV->inletSteam->massFlow>0) $this->warnings[] = "MpLp Steam Turbine Open yet MpLp PRV has flow";
        }
        
        return count($this->warnings);
    }

    /**
     * Sets initial values of Steam Properties
     */
    function initializeSteamProperties(){
        $this->initialSteamUsageGuess = ($this->hpSteamUsage + $this->mpSteamUsage + $this->lpSteamUsage)*1.2+1;
        $this->steamToDA = $this->initialSteamUsageGuess * .1;
        $this->lpSteamVent = 0;
        $this->lpPRVneed = 0;

        $this->mpSteamNeed = $this->mpSteamUsage;
        $this->lpSteamNeed = $this->steamToDA + $this->lpSteamUsage;
        
        //Model Boiler Operation
        //Steam Production Set at 1 for initial model
        $this->boiler = new Steam_Equipment_Boiler(array(
            'boilerPressure' => $this->highPressure,
            'blowdownRate' => ($this->blowdownRate/100),
            'outletMassFlow' => 1,
            'outletSteam' => new Steam_Object(array(
                    'pressure' => $this->highPressure,
                    'temperature' => $this->boilerTemp,
                    )),
            'boilerEff' => ($this->boilerEff/100),
            'daPressure' => $this->daPressure,
        ));
        
        //Set Blowdown condition
        $this->blowdown = new Steam_Object(array(
            'pressure' => $this->highPressure,
            'quality' => 0,
            ));
        $this->blowdownFlashLiquid = $this->blowdown;
        
        //Set Blowdown Flash to off
        $this->blowdownGasToLp = new Steam_Object();
        $this->blowdownFlashTank = new Steam_Equipment_FlashTank(new Steam_Object(array('pressure' => $this->highPressure, 'quality'=>0)), $this->lowPressure);
        
        //Set Header Condensate Specific Enthalpy
            //HP Condensate = Blowdown Properties
        $this->hpCondInitial = clone $this->blowdown;
        $this->satLiqEnthalpyHP = $this->hpCondInitial->specificEnthalpy;
        $this->mpCondInitial = new Steam_Object(array(
            'pressure' => $this->mediumPressure,
            'quality' => 0
            ));
        
        $this->satLiqEnthalpyMP = $this->mpCondInitial->specificEnthalpy;
        $this->lpCondInitial = new Steam_Object(array(
            'pressure' => $this->lowPressure,
            'quality' => 0
            ));
        
        $this->satLiqEnthalpyLP = $this->lpCondInitial->specificEnthalpy;
        
        $this->condensateInitReturn = new Steam_Object(array(
            'pressure' => $this->lowPressure,
            'temperature' => $this->condReturnTemp,
            'massFlow' => $this->hpSteamUsage*$this->hpCondReturnRate+$this->mpSteamUsage*$this->mpCondReturnRate+$this->lpSteamUsage*$this->lpCondReturnRate,
        ));   
        
        if ($this->condReturnFlash=='Yes'){
            $this->condReturnFlashTank = new Steam_Equipment_FlashTank($this->condensateInitReturn, $this->daPressure);
            $this->condReturnVent = $this->condReturnFlashTank->satGas;
            $this->condensate = $this->condReturnFlashTank->satLiquid;
        }else{
            $this->condensate = clone $this->condensateInitReturn;
            $this->condReturnVent = clone $this->condensateInitReturn;
            $this->condReturnVent->setMassFlow(0);
        }
        
        //Set Condensate Flash to 0
        $this->hpCondGasToMp = new Steam_Object();
        $this->mpCondGasToLp = new Steam_Object();
        
        //Initialize Turbines assuming 0 massflow      
            //Determine HP Header Condition
        $this->hpHeader = new Steam_Equipment_Header($this->highPressure, $this->boiler->outletSteam);
        $this->hpHeader->setHeatLoss(array('percent' => $this->hpHeatLossPercent/100));
        $hpSteam = $this->hpHeader->finalHeaderSteam;
            //MP header initially set as saturated vapor
        $mpSteam = new Steam_Object(array('pressure' => $this->mediumPressure, 'quality' => 1));
        
        $this->turbineCondModel = new Steam_Equipment_Turbine(array(
            'inletSteam' => clone $hpSteam,
            'outletPressure' => $this->turbineCondOutletPressure,
            'isentropicEff' => $this->turbineCondIsoEff/100,
            'generatorEff' => $this->turbineCondGenEff/100,
        ));
        $this->turbineCondSteamCooled = new Steam_Object(array(
           'pressure' => $this->turbineCondOutletPressure,
            'quality' => 0,
        ));        
        //Condensing Turbine
        if ($this->turbineCondOn){
            if ($this->turbineCondMethod == 'fixedFlow') $this->turbineCondModel->setMassFlow($this->turbineCondFixedFlow);            
            if ($this->turbineCondMethod == 'fixedPower') $this->turbineCondModel->setPowerOut($this->turbineCondFixedPower);                
        }
        
        $this->turbineHpLpModel = new Steam_Equipment_Turbine(array(
            'inletSteam' => clone $hpSteam,
            'outletPressure' => $this->lowPressure,
            'isentropicEff' => $this->turbineHpLpIsoEff/100,
            'generatorEff' => $this->turbineHpLpGenEff/100,
        ));
        $this->turbineHpMpModel = new Steam_Equipment_Turbine(array(
            'inletSteam' => clone $hpSteam,
            'outletPressure' => $this->mediumPressure,
            'isentropicEff' => $this->turbineHpMpIsoEff/100,
            'generatorEff' => $this->turbineHpMpGenEff/100,
        ));
        $this->turbineMpLpModel = new Steam_Equipment_Turbine(array(
            'inletSteam' => $mpSteam,
            'outletPressure' => $this->lowPressure,
            'isentropicEff' => $this->turbineMpLpIsoEff/100,
            'generatorEff' => $this->turbineMpLpGenEff/100,
        ));
        $this->initializeTurbines();
               
        $this->siteTotalPowerCost = $this->sitePowerImport * $this->operatingHours * $this->sitePowerCost;

        $this->makeupWater = new Steam_Object(array(
            'temperature' => $this->makeupWaterTemp,
            'pressure' => 0.101325, //Atmospheric
            ));
        $this->feedwater = new Steam_Object(array(
            'pressure' => $this->daPressure,
            'quality' => 0,
            ));
        
        $this->balanceTurbines();
    }
    
    function initializeTurbines(){
        //Set Turbine Ranges        
        if ($this->turbineHpMpOn){  
            switch ($this->turbineHpMpMethod) {
                case 'balanceHeader':
                    break;
                case 'fixedFlow':
                    break;
                case 'fixedPower':
                    $this->turbineHpMpModel->setPowerOut($this->turbineHpMpFixedPower);
                    break;
                case 'flowRange':
                    $this->turbineHpMpModel->setFlowRange ($this->turbineHpMpMinFlow, $this->turbineHpMpMaxFlow);
                    break;
                case 'powerRange':
                    $this->turbineHpMpModel->setPowerRange ($this->turbineHpMpMinPower, $this->turbineHpMpMaxPower);
                    break;
            }  
        }
        
        if ($this->turbineHpLpOn){  
            switch ($this->turbineHpLpMethod) {
                case 'balanceHeader':
                    break;
                case 'fixedFlow':
                    break;
                case 'fixedPower':
                    $this->turbineHpLpModel->setPowerOut($this->turbineHpLpFixedPower);
                    break;
                case 'flowRange':
                    $this->turbineHpLpModel->setFlowRange ($this->turbineHpLpMinFlow, $this->turbineHpLpMaxFlow);
                    break;
                case 'powerRange':
                    $this->turbineHpLpModel->setPowerRange ($this->turbineHpLpMinPower, $this->turbineHpLpMaxPower);
                    break;
            }  
        }
        $this->balanceTurbines();        
    }
    
    /**
     * Balance Turbine Operation
     * 
     * Required to Handle flow and power ranges
     */
    function balanceTurbines() {
        $turbineHpMpFlow = 0;        
        $turbineHpLpFlow = 0;
        $turbineMpLpFlow = 0;
        
        //Balance MP
        $mpSteamNeed = $this->mpSteamNeed;
        $mpSteamRemaining = $this->mpSteamNeed;
        if ($this->turbineHpMpOn){  
            //Set minimum flow
            switch ($this->turbineHpMpMethod) {
                case 'balanceHeader':
                    $turbineHpMpFlow = 0;
                    break;
                case 'fixedFlow':
                    $turbineHpMpFlow = $this->turbineHpMpFixedFlow;
                    break;
                case 'fixedPower':
                    $turbineHpMpFlow = $this->turbineHpMpModel->massFlow;
                    break;
                case 'flowRange':
                    $turbineHpMpFlow = $this->turbineHpMpModel->minFlow;
                    break;
                case 'powerRange':
                    $turbineHpMpFlow = $this->turbineHpMpModel->minFlow;                
                    break;
            }           
            $mpSteamRemaining = $this->mpSteamNeed - ($turbineHpMpFlow);
            if ($mpSteamRemaining<0) $mpSteamRemaining = 0;

            //HpMp Handle Variable Load        
            if ($this->turbineHpMpMethod == 'flowRange' or $this->turbineHpMpMethod == 'powerRange' or $this->turbineHpMpMethod == 'balanceHeader' ){
                if ($mpSteamRemaining <= ($this->turbineHpMpModel->maxFlow-$this->turbineHpMpModel->minFlow) or $this->turbineHpMpMethod == 'balanceHeader'){
                    $turbineHpMpFlow = $mpSteamRemaining + $this->turbineHpMpModel->minFlow;
                    $mpSteamRemaining = 0;
                }else{
                    $turbineHpMpFlow = $this->turbineHpMpModel->maxFlow;
                    $mpSteamRemaining -= ($this->turbineHpMpModel->maxFlow - $this->turbineHpMpModel->minFlow);
                }
            }
        }
        $this->turbineHpMpModel->setMassFlow($turbineHpMpFlow);
                
        //Balance LP
        if ($this->turbineHpLpOn){  
            //Set minimum flow
            switch ($this->turbineHpLpMethod) {
                case 'balanceHeader':
                    $turbineHpLpFlow = 0;
                    break;
                case 'fixedFlow':
                    $turbineHpLpFlow = $this->turbineHpLpFixedFlow;
                    break;
                case 'fixedPower':
                    $turbineHpLpFlow = $this->turbineHpLpModel->massFlow;
                    break;
                case 'flowRange':
                    $turbineHpLpFlow = $this->turbineHpLpModel->minFlow;
                    break;
                case 'powerRange':
                    $turbineHpLpFlow = $this->turbineHpLpModel->minFlow;                
                    break;
            }      
        }

        if ($this->turbineMpLpOn){
            //Set minimum flow
            switch ($this->turbineMpLpMethod) {
                case 'balanceHeader':
                    $turbineMpLpFlow = 0;
                    break;
                case 'fixedFlow':
                    $turbineMpLpFlow = $this->turbineMpLpFixedFlow;
                    break;
                case 'fixedPower':
                    $this->turbineMpLpModel->setPowerOut($this->turbineMpLpFixedPower);
                    $turbineMpLpFlow = $this->turbineMpLpModel->massFlow;
                    break;
                case 'flowRange':
                    $this->turbineMpLpModel->setFlowRange ($this->turbineMpLpMinFlow, $this->turbineMpLpMaxFlow);
                    $turbineMpLpFlow = $this->turbineMpLpModel->minFlow;
                    break;
                case 'powerRange':
                    $this->turbineMpLpModel->setPowerRange ($this->turbineMpLpMinPower, $this->turbineMpLpMaxPower);
                    $turbineMpLpFlow = $this->turbineMpLpModel->minFlow;
                    break;
            }  
        }        
        
        $lpSteamRemaining = $this->lpSteamNeed - ($turbineHpLpFlow+$turbineMpLpFlow) ;
        if (isset($this->mpTlpPRV->desuperFluidFlow)) $lpSteamRemaining-= $this->mpTlpPRV->desuperFluidFlow;
        
        $lpSteamRemaining -= $this->forcedExcessSteamMptoLP;
        if ($lpSteamRemaining < 0) $lpSteamRemaining = 0;

        //HpLp Handle Variable Load
        
        if ($this->turbineHpLpOn){
            if ($this->turbineHpLpMethod == 'flowRange' or $this->turbineHpLpMethod == 'powerRange' or $this->turbineHpLpMethod == 'balanceHeader' ){
                if ($lpSteamRemaining <= ($this->turbineHpLpModel->maxFlow-$this->turbineHpLpModel->minFlow) or $this->turbineHpLpMethod == 'balanceHeader'){
                    $turbineHpLpFlow = $lpSteamRemaining + $this->turbineHpLpModel->minFlow;
                    $lpSteamRemaining = 0;
                }else{
                    $turbineHpLpFlow = $this->turbineHpLpModel->maxFlow;
                    $lpSteamRemaining -= ($this->turbineHpLpModel->maxFlow - $this->turbineHpLpModel->minFlow);
                }
            }
        }
        $lpSteamRemaining += $this->forcedExcessSteamMptoLP;
        if ($lpSteamRemaining<$this->forcedExcessSteamMptoLP) $lpSteamRemaining = $this->forcedExcessSteamMptoLP;
        //MpLp Handle Variable Load
        if ($this->turbineMpLpOn){
            if ($this->turbineMpLpMethod == 'flowRange' or $this->turbineMpLpMethod == 'powerRange' or $this->turbineMpLpMethod == 'balanceHeader' ){
                if ($lpSteamRemaining <= ($this->turbineMpLpModel->maxFlow-$this->turbineMpLpModel->minFlow) or $this->turbineMpLpMethod == 'balanceHeader'){
                    $turbineMpLpFlow = $lpSteamRemaining + $this->turbineMpLpModel->minFlow;
                    $lpSteamRemaining = 0;
                }else{
                    $turbineMpLpFlow = $this->turbineMpLpModel->maxFlow;
                    $lpSteamRemaining -= ($this->turbineMpLpModel->maxFlow - $this->turbineMpLpModel->minFlow);
                }
            }
        }

        $this->turbineHpLpModel->setMassFlow($turbineHpLpFlow);        
        $this->turbineMpLpModel->setMassFlow($turbineMpLpFlow);
        
                
    }
    
    /**
     * Runs through the model once, starting with boiler steam production
     * @param float $additionalSteamFlow
     * @param float $steamProduction
     * @return type
     */
    function runModel($additionalSteamFlow, $steamProduction = false){
        $this->runStartTime[] = microtime(true);
        $this->runAdjusted1[] = $additionalSteamFlow;
        
        //Step 1a: Estimate Steam Production
        if ($steamProduction){
            $this->steamProduction = $steamProduction;
        }else{
            $this->steamProduction = 
                ($this->hpSteamUsage
                + $this->mpSteamUsage
                + $this->lpSteamUsage) 
                + $additionalSteamFlow
                + $this->turbineCondModel->massFlow;
        }        
        
        //Step 1b: Adjust Model Boiler Steam Production and Feedwater & Blowdown Massflows
        $this->boiler->setOutletSteamMassFlow($this->steamProduction);                
        $this->feedwater->setMassFlow($this->boiler->feedwater->massFlow);        
        $this->blowdown->setMassFlow($this->boiler->blowdown->massFlow);
        
        //Step 1c: Model Blowdown Flash if set
        if ($this->blowdownFlashLP=='Yes'){
            $this->blowdownFlashTank = new Steam_Equipment_FlashTank($this->blowdown, $this->lowPressure);            
            $this->blowdownGasToLp = $this->blowdownFlashTank->satGas;
            $this->blowdownFlashLiquid = $this->blowdownFlashTank->satLiquid;   
        }
        
    /**********************************************
     * HP High Pressure Header
     * Step 2a: Model HP Header
     */
        $this->hpHeader = new Steam_Equipment_Header($this->highPressure, $this->boiler->outletSteam);
        $this->hpHeader->setHeatLoss(array('percent' => $this->hpHeatLossPercent/100));           
             
        //Step 2b: Remove HP Process Steam Usages
        if ($this->energyUsageFixed) $this->hpSteamUsage = $this->setEnergyUsageHP*1000/($this->hpHeader->remainingSteam->specificEnthalpy-$this->satLiqEnthalpyHP);
        $this->hpHeader->useSteam($this->hpSteamUsage);
        
        //Step 2c: Evaluate Turbine Steam Usage
        $this->balanceTurbines();                        
        $this->hpHeader->useSteam($this->turbineCondModel->inletSteam->massFlow);
        $this->hpHeader->useSteam($this->turbineHpLpModel->outletSteam->massFlow);
        
        //if ($this->turbineHpMpOn and $this->turbineHpMpMethod=='balanceHeader') $this->turbineHpMpModel->setMassFlow ($this->hpHeader->remainingSteam->massFlow);
        $this->hpHeader->useSteam($this->turbineHpMpModel->outletSteam->massFlow);
        
        $this->hpProcessSteam = clone $this->hpHeader->remainingSteam;
        $this->hpProcessSteam->setMassFlow($this->hpSteamUsage);
        
        //Step 2d: Process Condensate
        $this->hpCondInitial->setMassFlow($this->hpSteamUsage * $this->hpCondReturnRate/100) ;
        $this->hpCondFinal = $this->hpCondInitial;

        if ($this->hpCondFlash=='Yes'){
            $this->hpCondFlashTank = new Steam_Equipment_FlashTank($this->hpCondFinal, $this->mediumPressure);
            $this->hpCondGasToMp = $this->hpCondFlashTank->satGas;
            $this->hpCondFinal = $this->hpCondFlashTank->satLiquid;
        }

        //Step 2e: Hp to Mp Pressure Reducing Value
        if ($this->desuperHeatHpMp == 'Yes'){
            $this->hpTmpPRV = new Steam_Equipment_PRV($this->hpHeader->remainingSteam, $this->mediumPressure,
                    clone $this->feedwater, $this->desuperHeatHpMpTemp);
        }else{
            $this->hpTmpPRV = new Steam_Equipment_PRV($this->hpHeader->remainingSteam, $this->mediumPressure);
        }
        
    /******************************************
     * MP Medium Pressure Header
     */
        //Step 3a: Set up MP Header
        $this->mpHeader = new Steam_Equipment_Header($this->mediumPressure, 
                array($this->hpTmpPRV->outletSteam,
                    $this->hpCondGasToMp,
                    $this->turbineHpMpModel->outletSteam,));
        $this->mpHeader->setHeatLoss(array('percent' => $this->mpHeatLossPercent/100));        
                
        //Step 3b: Remove MP Process Steam Usages
        if ($this->energyUsageFixed) $this->mpSteamUsage = $this->setEnergyUsageMP*1000/($this->mpHeader->remainingSteam->specificEnthalpy-$this->satLiqEnthalpyMP);
        $this->mpHeader->useSteam($this->mpSteamUsage);
       
        //Step 3c: Evaluate Turbine Steam Usage
        $this->turbineMpLpModel->setInletSteam(clone $this->mpHeader->remainingSteam);
        $this->balanceTurbines();                        
        $this->mpHeader->useSteam($this->turbineMpLpModel->inletSteam->massFlow);
        
        $this->mpSteamNeed = $this->mpSteamUsage
                + $this->turbineMpLpModel->inletSteam->massFlow
                - $this->hpCondGasToMp->massFlow;
        
        if (isset($this->mpTlpPRV->inletSteam->massFlow)) $this->mpSteamNeed+=$this->mpTlpPRV->inletSteam->massFlow;        
        $this->mpProcessSteam = clone $this->mpHeader->remainingSteam;
        $this->mpProcessSteam->setMassFlow($this->mpSteamUsage);
        
        //Step 4d: Process Condensate
        $this->mpCondInitial->setMassFlow($this->mpSteamUsage * $this->mpCondReturnRate/100) ;
        
        $mixHpMp = new Steam_Equipment_Header($this->mediumPressure, array($this->hpCondFinal, $this->mpCondInitial));
        $mpCondMixed = $mixHpMp->finalHeaderSteam;
        $this->mpCondFinal = $mpCondMixed;

        if ($this->mpCondFlash=='Yes'){
            $this->mpCondFlashTank = new Steam_Equipment_FlashTank($this->mpCondFinal, $this->lowPressure);
            $this->mpCondGasToLp = $this->mpCondFlashTank->satGas;
            $this->mpCondFinal = $this->mpCondFlashTank->satLiquid;
        }

        //Step 3e: Mp to Lp Pressure Reducing Value
        if ($this->desuperHeatMpLp == 'Yes'){
            $this->mpTlpPRV = new Steam_Equipment_PRV($this->mpHeader->remainingSteam, $this->lowPressure,
                    clone $this->feedwater, $this->desuperHeatMpLpTemp);
        }else{
            $this->mpTlpPRV = new Steam_Equipment_PRV($this->mpHeader->remainingSteam, $this->lowPressure);
        }

        /***********************************************
         * LP Low Pressure Header
         */
        //Step 4a: Set up LP Header
        $this->lpHeader = new Steam_Equipment_Header($this->lowPressure, 
                array(
                    $this->mpTlpPRV->outletSteam,
                    $this->mpCondGasToLp,
                    $this->blowdownGasToLp,
                    $this->turbineHpLpModel->outletSteam,
                    $this->turbineMpLpModel->outletSteam,));
        $this->lpHeader->setHeatLoss(array('percent' => $this->lpHeatLossPercent/100));        
        $this->lpHeader->useSteam($this->lpSteamVent );
        
        if (is_nan($this->lpHeader->remainingSteam->specificEnthalpy) ){
            var_dump($this->lpHeader);
            exit;
        }
       
        //Step 4b: Remove LP Process Steam Usages
        if ($this->energyUsageFixed) $this->lpSteamUsage = $this->setEnergyUsageLP*1000/($this->lpHeader->remainingSteam->specificEnthalpy-$this->satLiqEnthalpyLP);
        $this->lpHeader->useSteam($this->lpSteamUsage);
        
        $this->lpProcessSteam = clone $this->lpHeader->finalHeaderSteam;
        $this->lpProcessSteam->setMassFlow($this->lpSteamUsage);
        
        //Step 4c: Process Condensate
        $this->lpCondInitial->setMassFlow($this->lpSteamUsage * $this->lpCondReturnRate/100) ;
        
        $mixHpMpLp = new Steam_Equipment_Header($this->lowPressure, array($this->mpCondFinal, $this->lpCondInitial));
        $lpCondMixed = $mixHpMpLp->finalHeaderSteam;
        
        $this->lpCondFinal = $lpCondMixed;
        
        $this->condensateInitReturn->setMassFlow($this->lpCondFinal->massFlow);
        if ($this->condReturnFlash=='Yes'){
            $this->condReturnFlashTank = new Steam_Equipment_FlashTank($this->condensateInitReturn, $this->daPressure);
            $this->condReturnVent = $this->condReturnFlashTank->satGas;
            $this->condensate = $this->condReturnFlashTank->satLiquid;
        }else{
            $this->condensate->setMassFlow($this->lpCondFinal->massFlow);
        }        

        $this->turbineCondSteamCooled->setMassFlow($this->turbineCondModel->outletSteam->massFlow);
        
        /***********************************************
         * Determine Makeup Water Requirement
         */
        $this->makeupWaterFlow =
                ($this->feedwater->massFlow+$this->hpTmpPRV->desuperFluidFlow+$this->mpTlpPRV->desuperFluidFlow) * (1+$this->daVentRate/100)
                -$this->condensate->massFlow
                -$this->lpHeader->remainingSteam->massFlow
                -$this->turbineCondSteamCooled->massFlow;        
        $this->makeupWater->setMassFlow($this->makeupWaterFlow);
        
        $this->makeupWaterHeated = clone $this->makeupWater;
        if ($this->blowdownHeatX == 'Yes') {
            $blowdownInlet = $this->blowdown; 
            if ($this->blowdownFlashLP=='Yes'){
                $blowdownInlet = $this->blowdownFlashTank->satLiquid; 
            }
            $this->blowdownHeatExchanger = new Steam_Equipment_HeatExchanger($blowdownInlet, $this->makeupWater, $this->blowdownHeatXTemp);
            //echo $this->blowdownHeatExchanger->displayHeatXProperties();
            $this->makeupWaterHeated = $this->blowdownHeatExchanger->coldOutlet;
        }

        /***********************************************
         * Model Deaerator DA
         */
        $this->daWaterFeed = new Steam_Equipment_Header($this->daPressure, array(
            $this->makeupWaterHeated,
            $this->condensate,
            $this->turbineCondSteamCooled,
        ));
        $this->steamToDA = $this->lpHeader->remainingSteam->massFlow;
        $this->deaerator = new Steam_Equipment_Deaerator(array(
            'ventRate' => $this->daVentRate/100,
            'daPressure' => $this->daPressure,
            'daWaterFeed' => $this->daWaterFeed->finalHeaderSteam,
            'daSteamFeed' => $this->lpHeader->remainingSteam,
            'feedwaterFlow' => $this->feedwater->massFlow+$this->hpTmpPRV->desuperFluidFlow+$this->mpTlpPRV->desuperFluidFlow,
                ));               
        
        
        /*************************
         * Calculate Forced Excess Steam, if Positive: Open Vent
         */
        $forcedExcessSteamMP = $this->hpCondGasToMp->massFlow - $this->mpSteamUsage;
 
        $forcedExcessSteamLP = $this->mpCondGasToLp->massFlow 
                + $this->blowdownGasToLp->massFlow 
                - $this->deaerator->daSteamFeed->massFlow
                - $this->lpSteamUsage;
        
        if ($this->turbineHpMpOn){
            if ($this->turbineHpMpMethod=='fixedFlow' or $this->turbineHpMpMethod=='fixedPower') $forcedExcessSteamMP += $this->turbineHpMpModel->massFlow;
            if ($this->turbineHpMpMethod=='flowRange' or $this->turbineHpMpMethod=='powerRange') $forcedExcessSteamMP += $this->turbineHpMpModel->minFlow;
        }
        
        if ($this->turbineMpLpOn){
            if ($this->turbineMpLpMethod=='fixedFlow' or $this->turbineMpLpMethod=='fixedPower') {
                $forcedExcessSteamMP -= $this->turbineMpLpModel->massFlow;
                $forcedExcessSteamLP += $this->turbineMpLpModel->massFlow;
            }
            if ($this->turbineMpLpMethod=='flowRange' or $this->turbineMpLpMethod=='powerRange') {
                $forcedExcessSteamMP -= $this->turbineMpLpModel->minFlow;                
                $forcedExcessSteamLP += $this->turbineMpLpModel->minFlow;
            }
        }
        
        if ($this->turbineHpLpOn){
            if ($this->turbineHpLpMethod=='fixedFlow' or $this->turbineHpLpMethod=='fixedPower') $forcedExcessSteamLP += $this->turbineHpLpModel->massFlow;            
            if ($this->turbineHpLpMethod=='flowRange' or $this->turbineHpLpMethod=='powerRange') $forcedExcessSteamLP += $this->turbineHpLpModel->minFlow;            
        }
        
        if ($forcedExcessSteamMP>0) {
            $forcedSteamThroughTurbine = 0;
            $forcedSteamThroughPRV = $forcedExcessSteamMP;
            if ($this->turbineMpLpOn and $this->turbineMpLpMethod=='balanceHeader'){
                $forcedSteamThroughTurbine = $forcedExcessSteamMP;                
                $forcedSteamThroughPRV = 0;
            }
            if ($this->turbineMpLpOn and ($this->turbineMpLpMethod=='flowRange' or $this->turbineMpLpMethod=='powerRange') ){
                
                $forcePRVmPlPremaining = $forcedExcessSteamMP - $this->turbineMpLpModel->maxFlow + $this->turbineMpLpModel->minFlow;
                if ($forcePRVmPlPremaining>0){
                    $forcedSteamThroughTurbine = $this->turbineMpLpModel->maxFlow - $this->turbineMpLpModel->minFlow;
                    $forcedSteamThroughPRV = $forcedExcessSteamMP - $forcedSteamThroughTurbine;
                }else{
                    $forcedSteamThroughTurbine = $forcedExcessSteamMP;
                    $forcedSteamThroughPRV = 0;
                }
            }
            if ($this->turbineMpLpOn and ($this->turbineMpLpMethod=='fixedFlow' or $this->turbineMpLpMethod=='fixedPower') ) {
                $forcedSteamThroughTurbine = 0;
                $forcedSteamThroughPRV = $forcedExcessSteamMP;
            }
            if ($forcedSteamThroughPRV > 0 and $this->desuperHeatMpLp == 'Yes'){
                $forcePRVmPlPSteam = clone $this->mpHeader->remainingSteam;
                $forcePRVmPlPSteam->setMassFlow($forcedSteamThroughPRV);
                $forcedmpTlpPRV = new Steam_Equipment_PRV($forcePRVmPlPSteam, $this->lowPressure,
                        clone $this->feedwater, $this->desuperHeatMpLpTemp);
                $forcedSteamThroughPRV = $forcedmpTlpPRV->outletSteam->massFlow;
            }
            
            //@TODO Unused
            $this->forcedExcessSteamMptoLP = $forcedExcessSteamMP; //$forcedSteamThroughTurbine + $forcedSteamThroughPRV;

            $forcedExcessSteamLP += ($forcedSteamThroughTurbine + $forcedSteamThroughPRV);
        }

        
        $this->lpSteamVent = 0;
        if ( $forcedExcessSteamLP>0 ) $this->lpSteamVent = $forcedExcessSteamLP;
        
        
        $this->lpSteamNeed = 
                $this->lpSteamUsage 
                + $this->deaerator->daSteamFeed->massFlow
                - $this->mpCondGasToLp->massFlow 
                - $this->blowdownGasToLp->massFlow;
        
        if ($forcedExcessSteamMP>$this->lpSteamNeed) {
            $this->lpSteamNeed = $forcedExcessSteamMP;
        }

        $this->balanceTurbines();        
        $this->lpPRVneed = $this->lpSteamNeed
                -$this->turbineHpLpModel->massFlow
                -$this->turbineMpLpModel->massFlow;
        
        $remainingSteam = $this->mpTlpPRV->inletSteam->massFlow-$this->lpPRVneed;      
        
        $this->lpBalance = 
                $this->lpSteamUsage 
                + $this->deaerator->daSteamFeed->massFlow
                - $this->mpCondGasToLp->massFlow 
                - $this->blowdownGasToLp->massFlow
                - $this->turbineHpLpModel->massFlow
                - $this->turbineMpLpModel->massFlow
                - $this->mpTlpPRV->outletSteam->massFlow
                + $this->lpSteamVent;                
        
        $this->runAdjusted2[] = $this->lpSteamVent; //$lpNeedAdjustment;
        $this->runNEEDED[] = $this->deaerator->daSteamFeed->massFlow-$this->steamToDA;
        $this->runEndTime[] = microtime(true);              
        
        $daSteamDifference = $this->deaerator->daSteamFeed->massFlow-$this->steamToDA;
        
        $steamRequirements = array(
            'hmPRV' => $this->hpTmpPRV->outletSteam->massFlow,
            'mlPRV' => $this->mpTlpPRV->outletSteam->massFlow,
            'vent' => $this->lpSteamVent,
        );
        
        if ($this->turbineHpLpOn){
            if ( $this->turbineHpLpMethod=='balanceHeader') $steamRequirements['mlPRV'] = $this->turbineHpLpModel->massFlow;
            if ( $this->turbineHpLpMethod=='powerRange' or $this->turbineHpLpMethod=='flowRange' ){
                if ( $this->turbineHpLpModel->massFlow<$this->turbineHpLpModel->maxFlow ){
                    $steamRequirements['mlPRV'] = $this->turbineHpLpModel->massFlow;
                }else{
                    
                }
                
            }
        }
        if ($this->turbineHpMpOn and $this->turbineHpMpMethod=='balanceHeader') $steamRequirements['hmPRV'] = $this->turbineHpMpModel->massFlow;
        if ($this->turbineMpLpOn and $this->turbineMpLpMethod=='balanceHeader') $steamRequirements['mlPRV'] = $this->turbineMpLpModel->massFlow;
        
        $extraFlow = min($steamRequirements);
        
        $prvSteamRequirement =0;    
        
        if ($this->lpBalance<0) $daSteamDifference += $this->lpBalance;
        $this->lpVentedSteam = clone $this->lpHeader->finalHeaderSteam;
        $this->lpVentedSteam->setMassflow($this->lpSteamVent);          
        
        $this->runDetails['prvSteamRequirement'][] = $prvSteamRequirement;        
        $this->runDetails['daSteamDifference'][] = $daSteamDifference;
        if ($prvSteamRequirement>0){
            if ($daSteamDifference>0 and $daSteamDifference>$prvSteamRequirement) return $daSteamDifference;
            return $prvSteamRequirement;
        }else{
            return $daSteamDifference;
            
        }
    }
}