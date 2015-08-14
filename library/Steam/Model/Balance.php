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
 * Generates Steam Balances for components of the Steam Model 
 * 
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Balance{
         
    /**
     * List of Component Groups
     * @var array
     */
    var $components = array(
            'system' => 'System',
            'hpHeader' => 'HP Header',
            'mpHeader' => 'MP Header',
            'lpHeader' => 'LP Header',
            'condensateReturn' => 'Condensate Return',
            'feedwater' => 'Feedwater',
        );
        
    /**
     * List of Component Group Pieces
     * @var array
     */
    var $pieces = array(
            'system' => array(
                'boiler' => 'Boiler Energy',
                'boilerLoss' => 'Boiler Energy Losses',
                'condTurbine' => 'Cond Turbine',
                'condTurbineLoss' => 'Cond Turbine Losses',
                'hpmpTurbine' => 'HP tp MP Turbine',
                'hplpTurbine' => 'HP to LP Turbine',
                'mplpTurbine' => 'MP to LP Turbine',
                'hpHeatLoss' => 'HP Energy Losses',
                'hpProcessLoss' => 'HP Process Losses',
                'mpHeatLoss' => 'MP Energy Losses',
                'mpProcessLoss' => 'MP Condensate Losses',
                'lpHeatLoss' => 'LP Energy Losses',
                'lpProcessLoss' => 'LP Condensate Losses',   
                'lpVentedSteam' => 'LP Vented Steam',       
                'makeupWater' => 'Make Up Water',
                'blowdownDump' => 'Blowdown',
                'condensateFlash' => 'Condensate Flash',
                'condensateHeatLoss' => 'Condensate Heat Loss',
                'daVent' => 'Deaerator Steam Vent',
                ),
            'hpHeader' => array(
                'boiler' => 'Boiler Steam',
                'condTurbine' => 'Condensing Turbine Inlet',
                'hpmpTurbine' => 'HP to MP Turbine Inlet',
                'hplpTurbine' => 'HP to LP Turbine Inlet',
                'hpTmpPRV' => 'HP to MP PRV Inlet',
                'hpProcessSteam' => 'HP Processes',
                'heatLoss' => 'HP Energy Losses',
            ),
            'mpHeader' => array(
                'hpTmpPRV' => 'HP to MP PRV',
                'hpTmpPRVdesup' => 'Desuperheating Feedwater',
                'hpmpTurbine' => 'HP to MP Turbine Outlet',
                'hpCondGasToMp' => 'HP Cond Flashed to MP',
                'mpTlpPRV' => 'MP to LP PRV',
                'mplpTurbine' => 'MP to LP Turbine Inlet',
                'mpProcessSteam' => 'MP Processes',
                'heatLoss' => 'MP Energy Losses',
            ),
            'lpHeader' => array(
                'mpTlpPRV' => 'MP to LP PRV',
                'mpTlpPRVdesup' => 'Desuperheating Feedwater',
                'mplpTurbine' => 'MP to LP Turbine Outlet',
                'hplpTurbine' => 'HP to LP Turbine Outlet',
                'mpCondGasToLp' => 'MP Cond Flashed to LP',
                'blowdownGasToLp' => 'Blowdown Flashed to MP',
                'daSteam' => 'Deaerator Steam',        
                'lpProcessSteam' => 'LP Processes',
                'heatLoss' => 'LP Energy Losses',  
                'lpVentedSteam' => 'LP Vented Steam',     
            ),
            'condensateReturn' => array(
                'condensate' => 'Condensate Returned',
                'condensateFlash' => 'Condensate Flashed',
                'makeupWater' => 'Make Up Water',
                'condTurbine' => 'Condensing Turbine',
                'daFeed' => 'Deaerator Feed',

            ),
            'feedwater' => array(
                'daFeed' => 'Feedwater',
                'desuperLp' => "Desup Feedwater to LP",
                'desuperMp' => "Desup Feedwater to MP",
                'boiler' => "Boiler Feedwater",
            ),
        );
    
    /**
     * Complete Steam Balance stored in this array
     * @var array
     */
    var $sB = array();
    
    /**
     * Generates a Steam Balance for a Steam Model
     * @param Steam_Model_Constructor $steamModel
     */
    public function __construct($steamModel){
        $sM = $steamModel;
        
        $sB['system'] = array(
            'mF' => array (
                    'boiler' => 0,
                    'boilerLoss' => 0,
                    'condTurbine' => 0,
                    'condTurbineLoss' => 0,
                    'hpmpTurbine' => 0,
                    'hplpTurbine' => 0,
                    'mplpTurbine' => 0,
                    'hpHeatLoss' => 0,
                    'hpProcessLoss' => -($sM->hpProcessSteam->massFlow - $sM->hpCondInitial->massFlow),
                    'mpHeatLoss' => 0,
                    'mpProcessLoss' => -($sM->mpProcessSteam->massFlow - $sM->mpCondInitial->massFlow),
                    'lpHeatLoss' => 0,
                    'lpProcessLoss' => -($sM->lpProcessSteam->massFlow - $sM->lpCondInitial->massFlow),  
                    'lpVentedSteam' => -$sM->lpVentedSteam->massFlow,     
                    'makeupWater' => $sM->makeupWater->massFlow,
                    'blowdownDump' => -$sM->blowdownFlashLiquid->massFlow,
                    'condensateFlash' => -$sM->condReturnVent->massFlow,
                    'condensateHeatLoss' => 0,
                    'daVent' => -$sM->deaerator->daVentSteam->massFlow,       
                ),
            'eF' => array(
                    'boiler' => $sM->boiler->fuelEnergy,
                    'boilerLoss' => -($sM->boiler->fuelEnergy - $sM->boiler->boilerEnergy),
                    'condTurbine' => -$sM->turbineCondModel->energyOut,
                    'condTurbineLoss' => -($sM->turbineCondModel->outletSteam->energyFlow - $sM->turbineCondSteamCooled->energyFlow),
                    'hpmpTurbine' => -$sM->turbineHpMpModel->energyOut,
                    'hplpTurbine' => -$sM->turbineHpLpModel->energyOut,
                    'mplpTurbine' => -$sM->turbineMpLpModel->energyOut,
                    'hpHeatLoss' => -$sM->hpHeader->heatLoss->energyFlowLoss,
                    'hpProcessLoss' => -($sM->hpProcessSteam->energyFlow - $sM->hpCondInitial->energyFlow),
                    'mpHeatLoss' => -$sM->mpHeader->heatLoss->energyFlowLoss,
                    'mpProcessLoss' => -($sM->mpProcessSteam->energyFlow - $sM->mpCondInitial->energyFlow),
                    'lpHeatLoss' => -$sM->lpHeader->heatLoss->energyFlowLoss,
                    'lpProcessLoss' => -($sM->lpProcessSteam->energyFlow - $sM->lpCondInitial->energyFlow), 
                    'lpVentedSteam' => -$sM->lpVentedSteam->energyFlow,     
                    'makeupWater' => $sM->makeupWater->energyFlow,
                    'blowdownDump' => -$sM->blowdownFlashLiquid->energyFlow,
                    'condensateFlash' => -$sM->condReturnVent->energyFlow,
                    'condensateHeatLoss' => -( $sM->lpCondFinal->energyFlow - $sM->condensate->energyFlow ),
                    'daVent' => -$sM->deaerator->daVentSteam->energyFlow,  
            ));

        $sB['hpHeader'] = array(
            'mF' => array (
                'boiler' => $sM->boiler->outletSteam->massFlow,
                'condTurbine' => -$sM->turbineCondModel->inletSteam->massFlow,
                'hpmpTurbine' => -$sM->turbineHpMpModel->inletSteam->massFlow,
                'hplpTurbine' => -$sM->turbineHpLpModel->inletSteam->massFlow,
                'hpTmpPRV' => -$sM->hpTmpPRV->inletSteam->massFlow,
                'hpProcessSteam' => -$sM->hpProcessSteam->massFlow,
                'heatLoss' => 0,
                ),
            'eF' => array (
                'boiler' => $sM->boiler->outletSteam->energyFlow,
                'condTurbine' => -$sM->turbineCondModel->inletSteam->energyFlow,
                'hpmpTurbine' => -$sM->turbineHpMpModel->inletSteam->energyFlow,
                'hplpTurbine' => -$sM->turbineHpLpModel->inletSteam->energyFlow,
                'hpTmpPRV' => -$sM->hpTmpPRV->inletSteam->energyFlow,
                'hpProcessSteam' => -$sM->hpProcessSteam->energyFlow,
                'heatLoss' => -$sM->hpHeader->heatLoss->energyFlowLoss,
                ),
        );

        $sB['mpHeader'] = array(
            'mF' => array (
                'hpTmpPRV' => $sM->hpTmpPRV->inletSteam->massFlow,
                'hpTmpPRVdesup' => number_format(0,2),
                'hpmpTurbine' => $sM->turbineHpMpModel->outletSteam->massFlow,
                'hpCondGasToMp' => $sM->hpCondGasToMp->massFlow,
                'mpTlpPRV' => -$sM->mpTlpPRV->inletSteam->massFlow,
                'mplpTurbine' => -$sM->turbineMpLpModel->inletSteam->massFlow,
                'mpProcessSteam' => -$sM->mpProcessSteam->massFlow,
                'heatLoss' => 0,
                ),
            'eF' => array (
                'hpTmpPRV' => $sM->hpTmpPRV->inletSteam->energyFlow,
                'hpTmpPRVdesup' => number_format(0,2),
                'hpmpTurbine' => $sM->turbineHpMpModel->outletSteam->energyFlow,
                'hpCondGasToMp' => $sM->hpCondGasToMp->energyFlow,
                'mpTlpPRV' => -$sM->mpTlpPRV->inletSteam->energyFlow,
                'mplpTurbine' => -$sM->turbineMpLpModel->inletSteam->energyFlow,
                'mpProcessSteam' => -$sM->mpProcessSteam->energyFlow,
                'heatLoss' => -$sM->mpHeader->heatLoss->energyFlowLoss,
                ),
        );
        if (isset($sM->hpTmpPRV->desuperheatFluid->massFlow)){
            $sB['mpHeader']['mF']['hpTmpPRVdesup'] = $sM->hpTmpPRV->desuperheatFluid->massFlow;
            $sB['mpHeader']['eF']['hpTmpPRVdesup'] = $sM->hpTmpPRV->desuperheatFluid->energyFlow;
        }

        $sB['lpHeader'] = array(
            'mF' => array (
                'mpTlpPRV' => $sM->mpTlpPRV->inletSteam->massFlow,
                'mpTlpPRVdesup' => number_format(0,2),
                'mplpTurbine' => $sM->turbineMpLpModel->outletSteam->massFlow,
                'hplpTurbine' => $sM->turbineHpLpModel->outletSteam->massFlow,
                'mpCondGasToLp' => $sM->mpCondGasToLp->massFlow,
                'blowdownGasToLp' => $sM->blowdownGasToLp->massFlow,
                'daSteam' => -$sM->deaerator->daSteamFeed->massFlow,        
                'lpProcessSteam' => -$sM->lpProcessSteam->massFlow,  
                'heatLoss' => 0,      
                'lpVentedSteam' => -$sM->lpVentedSteam->massFlow,     
                ),
            'eF' => array (
                'mpTlpPRV' => $sM->mpTlpPRV->inletSteam->energyFlow,
                'mpTlpPRVdesup' => number_format(0,2),
                'mplpTurbine' => $sM->turbineMpLpModel->outletSteam->energyFlow,
                'hplpTurbine' => $sM->turbineHpLpModel->outletSteam->energyFlow,
                'mpCondGasToLp' => $sM->mpCondGasToLp->energyFlow,
                'blowdownGasToLp' => $sM->blowdownGasToLp->energyFlow,
                'daSteam' => -$sM->deaerator->daSteamFeed->energyFlow,        
                'lpProcessSteam' => -$sM->lpProcessSteam->energyFlow,
                'heatLoss' => -$sM->lpHeader->heatLoss->energyFlowLoss,
                'lpVentedSteam' => -$sM->lpVentedSteam->energyFlow,  
                ),
        );
        if (isset($sM->mpTlpPRV->desuperheatFluid->massFlow)){
            $sB['lpHeader']['mF']['mpTlpPRVdesup'] = $sM->mpTlpPRV->desuperheatFluid->massFlow;
            $sB['lpHeader']['eF']['mpTlpPRVdesup'] = $sM->mpTlpPRV->desuperheatFluid->energyFlow;
        }

        $sB['condensateReturn'] = array(
            'mF' => array (
                    'condensate' => $sM->condensateInitReturn->massFlow,
                    'condensateFlash' => -$sM->condReturnVent->massFlow,
                    'makeupWater' => $sM->makeupWater->massFlow,
                    'condTurbine' => $sM->turbineCondSteamCooled->massFlow,
                    'daFeed' => -$sM->deaerator->daWaterFeed->massFlow,
                ),
            'eF' => array (
                    'condensate' => $sM->condensateInitReturn->energyFlow,
                    'condensateFlash' => -$sM->condReturnVent->energyFlow,
                    'makeupWater' => $sM->makeupWater->energyFlow,
                    'condTurbine' => $sM->turbineCondSteamCooled->energyFlow,
                    'daFeed' => -$sM->deaerator->daWaterFeed->energyFlow,      
                ),
            );
        $sB['feedwater'] = array(
            'mF' => array (
                    'daFeed' => $sM->deaerator->feedwater->massFlow,
                    'desuperLp' => number_format(0,2),
                    'desuperMp' => number_format(0,2),
                    'boiler' => -$sM->boiler->feedwater->massFlow,
                ),
            'eF' => array (
                    'daFeed' => $sM->deaerator->feedwater->energyFlow,
                    'desuperLp' => number_format(0,2),
                    'desuperMp' => number_format(0,2),
                    'boiler' => -$sM->boiler->feedwater->energyFlow,             
            ),
        );
        if (isset($sM->mpTlpPRV->desuperheatFluid->massFlow)){
            $sB['feedwater']['mF']['desuperLp'] = -$sM->mpTlpPRV->desuperheatFluid->massFlow;
            $sB['feedwater']['eF']['desuperLp'] = -$sM->mpTlpPRV->desuperheatFluid->energyFlow;
        }

        if (isset($sM->hpTmpPRV->desuperheatFluid->massFlow)){
            $sB['feedwater']['mF']['desuperMp'] = -$sM->hpTmpPRV->desuperheatFluid->massFlow;
            $sB['feedwater']['eF']['desuperMp'] = -$sM->hpTmpPRV->desuperheatFluid->energyFlow;
        }
        $this->sB = $sB;    
    }
}