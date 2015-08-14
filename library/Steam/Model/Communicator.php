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
 * Handles (loads/saves/deletes) Model Session data (base/adjusted)
 * 
 * @package    Steam
 * @subpackage Steam_Model
 */
class Steam_Model_Communicator{
    /**
     * Base Loaded Flag
     * @var boolean
     */    
    var $baseLoaded = false;
    /**
     * Base Model Data Transfer Object
     * @var Steam_DTO
     */
    var $baseDTO;
    /**
     * Generated Base Model
     * @var Steam_Model_Constructor
     */
    var $baseModel;
    /**
     * Adjusted Loaded Flag
     * @var boolean
     */    
    var $adjustedLoaded = false;
    /**
     * Adjusted Model Data Transfer Object
     * @var Steam_DTO
     */
    var $adjustedDTO = array();
    /**
     * Generated Adjusted Model
     * @var Steam_Model_Constructor
     */
    var $adjustedModels = array();    
    
    /**
     * Session Saved Data Container
     * @var Zend_Session_Namespace
     */
    var $savedSteamModel = null;
    
    /**
     * Loads any saved models
     */
    public function __construct() {
        $this->savedSteamModel = new Zend_Session_Namespace('Steam_Model');
                
        if ($this->savedSteamModel->baseLoaded){
            
            $this->baseLoaded = $this->savedSteamModel->baseLoaded;
            $this->baseDTO = $this->savedSteamModel->baseDTO;       
            $this->baseModel = $this->savedSteamModel->baseModel; 
            
            if ($this->savedSteamModel->adjustedLoaded){                                                     
                $this->projectFormValues = $this->savedSteamModel->projectFormValues;
                $this->adjustedLoaded = $this->savedSteamModel->adjustedLoaded;
                $this->adjustedDTO = $this->savedSteamModel->adjustedDTO;
                $this->adjustedModels = $this->savedSteamModel->adjustedModels;
            }
        }  
    }
    
    /**
     * Load Adjusted Model as Base Model
     */
    public function adjustedToBase(){
        if ($this->savedSteamModel->adjustedLoaded ){
            $this->loadBaseModel($this->savedSteamModel->adjustedDTO);
            $this->deleteAdjustedModel();
        }
    }
    
    /**
     * Save Adjusted Model
     * @param Steam_DTO $projectsDTO
     */    
    public function saveProjects($projectFormValues){                                         
        $this->projectFormValues = $projectFormValues;
        $adjustedDTO = new Steam_Model_ProjectsDto($this->baseDTO, $this->baseModel, $projectFormValues);
        $this->adjustedModels = new Steam_Model_Constructor($adjustedDTO->unloadStandardized(), $this->baseModel->powerGenerated);
        
        $adjustedDTO->load('baseModel', array('sitePowerImport' => $this->baseModel->sitePowerImport+$this->baseModel->powerGenerated-$this->adjustedModels->powerGenerated), array('sitePowerImport'=>'MJ/hr'));              

        $this->adjustedDTO = $adjustedDTO;
        $this->savedSteamModel->projectFormValues = $projectFormValues;
        $this->savedSteamModel->adjustedDTO = $this->adjustedDTO;
        $this->savedSteamModel->adjustedModels = $this->adjustedModels;
        $this->adjustedLoaded = true; //count($this->adjustedModels);
        $this->savedSteamModel->adjustedLoaded = $this->adjustedLoaded;   
    }
    
    /**
     * Load Base Mode
     * @param Steam_DTO $baseDTO
     */
    public function loadBaseModel($baseDTO){          
        
        $this->baseDTO = $baseDTO;
        $this->baseModel = new Steam_Model_Constructor($this->baseDTO->unloadStandardized());        
        
        if ($this->baseModel->deaerator->daNotFunctioning){
            $this->setStatus('ERROR: Model cannot converge:<BR><span style="font-size: .5em;">
                Feedwater specific ethalpy is too high and low pressure header steam cannot provide enough heat.<br>
                Reduce Deaerator pressure or increase low pressure header specific enthalpy.</span>',true);
            if ($this->baseLoaded){
                $this->baseDTO = $this->savedSteamModel->baseDTO;
                $this->baseModel = $this->savedSteamModel->baseModel;                
            }
        }else{
        
            $this->setStatus('Base Model Generated Successfully');     
            $this->baseLoaded = true;
            
            $this->savedSteamModel->baseLoaded = true;                
            $this->savedSteamModel->baseDTO = $this->baseDTO;
            $this->savedSteamModel->baseModel = $this->baseModel;                

            //Reload Adjusted Model if loaded with base model adjustments
            if ($this->adjustedLoaded) $this->saveProjects($this->adjustedDTO);
        }
    }    
    
    /**
     * Delete Base and Adjusted Models
     */
    public function deleteEntireModel(){
        $this->baseLoaded = false;
        unset($this->baseDTO);
        unset($this->baseModel);
        $this->savedSteamModel->baseLoaded = false;
        unset($this->savedSteamModel->baseDTO);
        unset($this->savedSteamModel->baseModel);      
               $this->deleteAdjustedModel();
    }
    
    /**
     * Delete Adjusted Model
     */
    public function deleteAdjustedModel(){
        $this->adjustedLoaded = false;
        unset($this->adjustedDTO);
        unset($this->adjustedModels);
        $this->savedSteamModel->adjustedLoaded = false;
        unset($this->savedSteamModel->adjustedDTO);
        unset($this->savedSteamModel->adjustedModels);
    }
    
    /**
     * Set status string
     * @param string $status
     * @param boolean $alert
     */
    public function setStatus($status,$alert = false){
        $translator = Zend_Registry::get('Zend_Translate');    
        if (!$alert) $this->savedSteamModel->status = "<BR><span style='display: block; border: 2px green solid; padding: 6px; color: green;  background-color: #EEEEEE; font-size: 2em;'>".$translator->_($status)."</span><BR>";;
        if ($alert) $this->savedSteamModel->status = "<BR><span style='display: block; border: 2px red solid; padding: 6px; color: red;  background-color: #EEEEEE; font-size: 2em;'>".$translator->_($status)."</span><BR>";;
    }
    
    /**
     * Display any set status strings
     * @return string Status
     */
    public function status(){
        $status = $this->savedSteamModel->status;
        unset($this->savedSteamModel->status);
        return $status;
    }
}
