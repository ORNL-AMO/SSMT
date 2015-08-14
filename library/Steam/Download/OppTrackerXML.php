<?php
/**
 * Steam Calculators
 * 
 * @package    Steam
 * @subpackage Steam_Download
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Generates XML of projects as recommendations for the Project Opportunity Tracker
 * @package    Steam
 * @subpackage Steam_Download
 */
class Steam_Download_OppTrackerXML{
    
    /**
     * Generates XML of projects as recommendations for the Project Opportunity Tracker  
     * @param Steam_Model_Communicator $sMc
     */
    public function __construct($sMc) {
        $mS = Steam_MeasurementSystem::getInstance();                
        $this->sMc = $sMc;               
        $projectList = Steam_Model_Projects::listed($this->sMc->baseModel->headerCount);
        
        //Determine Active Projects
        $cats = array();
        $projects = array();
        $allProjects = array();
        $projectCat = array();
        $projectSelected = $this->sMc->adjustedDTO->projectFormData;
        foreach($projectList as $key => $values){
            if ($projectSelected[$key]) $cats[$key] = $values[0];
            foreach($values[1] as $projectKey => $value){
                if ($projectSelected[$projectKey]){   
                    $projects[$key][$projectKey] = $value;
                    $allProjects[$projectKey] = $value;
                    $projectCat[$projectKey] = $values[0];
                }
            }
        }
                
        $masterFormValues = $this->sMc->adjustedDTO->unload(); //$this->sMc->projectFormValues;        
        $totalAdjustedOperatingCosts = $this->sMc->baseModel->totalOperatingCosts;
        $totalAdjustedEnergyUsage = $this->sMc->baseModel->totalEnergyUsage;

        //Turn All Projects OFf
        $projectFormValues = $masterFormValues;
        foreach($allProjects as $projectKey => $projectTitle){
            $projectFormValues[$projectKey] = 0;
        }
        $masterFormValues = $projectFormValues;

        //Evaluate Projects Individually
        $projectResults = array();
        foreach($allProjects as $projectKey => $projectTitle){
            $projectFormValues = $masterFormValues;
            $projectFormValues[$projectKey] = 1;
            
            $adjustedDTO = new Steam_Model_ProjectsDto($this->sMc->baseDTO, $this->sMc->baseModel, new Steam_DTO('baseModel', $projectFormValues));    
            $adjustedModels = new Steam_Model_Constructor($adjustedDTO->unloadStandardized(), $this->sMc->baseModel->powerGenerated);;
            $adjustedDTO->load('baseModel', array('sitePowerImport' => $this->sMc->baseModel->sitePowerImport+$this->sMc->baseModel->powerGenerated-$adjustedModels->powerGenerated), array('sitePowerImport'=>'MJ/hr'));              

            $costSavings = round($totalAdjustedOperatingCosts - $adjustedModels->totalOperatingCosts);
            $energySavings = $mS->rawEnergy($totalAdjustedEnergyUsage - $adjustedModels->totalEnergyUsage);    

            $projectResults[$projectKey] = array(
                'Action_Category' => 'Steam Generation',
                'Action_Title' => $projectCat[$projectKey],
                'Action_Description' => $projectTitle." (Savings based on individual project implementation.)",
                'ProjectedCostSavings' => $costSavings,
                'ProjectedSavings' => $energySavings,
            );
        }
        
        //Generate XML of results
        $xmlWriter = new XMLWriter();
        
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        
        $xmlWriter->setIndentString("    ");
        $xmlWriter->startDocument("1.0", "UTF-8");     

        $xmlWriter->startElement("Recommendations");   
        foreach($projectResults as $key => $values){           
                $xmlWriter->startElement("Recommendation");      
                    $xmlWriter->writeElement('Plant_Action_ID', '');
                    $xmlWriter->writeElement('Action_ID', '');
                    $xmlWriter->writeElement('Action_Title', $values['Action_Title']);
                    $xmlWriter->writeElement('Action_Description', $values['Action_Description']);
                    $xmlWriter->writeElement('Action_Category', $values['Action_Category']);
                    $xmlWriter->writeElement('SourceToolID', '18');
                    $xmlWriter->writeElement('Notes', '');
                    $xmlWriter->writeElement('StatusID', '');
                    $xmlWriter->writeElement('ProjectedCost', '');
                    $xmlWriter->writeElement('ProjectedSavings', $values['ProjectedSavings']);
                    $xmlWriter->writeElement('ProjectedROI', '');
                    $xmlWriter->writeElement('Implemented', 'False');
                    $xmlWriter->writeElement('ImplementationNotes', '');
                    $xmlWriter->writeElement('Priority', '');
                    $xmlWriter->writeElement('CustomCategory', '');
                    $xmlWriter->writeElement('Plant_ID', '');
                    $xmlWriter->writeElement('Deleted_Date', '');
                    $xmlWriter->writeElement('ProjectedCostSavings', $values['ProjectedCostSavings']);
                    $xmlWriter->writeElement('ProjectedCO2', '');
                    $xmlWriter->writeElement('SourceText', '');
                $xmlWriter->endElement();
            }
        $xmlWriter->endElement();

        $xmlWriter->endDocument(); 
        header('Content-type: text/xml');
        header('Content-Disposition: attachment;filename=SteamOppTracker.xml');
        header('Cache-Control: max-age=0');

        echo $xmlWriter->outputMemory();
    }
}