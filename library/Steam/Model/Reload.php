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
 * Reloads Steam Model From Uploaded Data
 * @package    Steam
 * @subpackage Steam_Model 
 */
class Steam_Model_Reload{
    
    /**
     * System units used in saved model
     * @var array
     */
    var $units;
    /**
     * Units to be used with model data
     * @var array
     */
    var $modelSelected;
    
    /**
     * Base Model Data
     * @var array
     */
    var $baseData;
    
    /**
     * Adjusted Model Data
     * @var array
     */
    var $adjustedData;
    
    /**
     * Steam_Model_Communicator
     * @var Steam_Model_Communicator
     */
    var $sMc;
    
    /**
     * Corrupt Upload Data Flag
     * @var boolean
     */
    var $corruptUpload;
        
    /**
     * Process Uploaded Data
     * @param type $uploadData
     * @param string $uploadType
     */
    public function __construct($uploadData, $uploadType = 'full'){
        $this->sMc = new Steam_Model_Communicator;
        
        $lines = explode("\n", $uploadData);
        $data = array();

        $cc = 0;
        foreach($lines as $row){
            $cells = explode("\t", $row);
            foreach($cells as $key => $value) $cells[$key] = trim($value);
            if ($cells[0]=='-------') { $cc++; continue;}
            if (isset($cells[1]) and isset($cells[0]) and $cells[0]<>''){
                $data[$cc][$cells[0]] = $cells[1];
            }
        }
        
        $this->units = $data[1];
        $this->baseData = $data[2];
        if (!isset($this->baseData['boilerTemp']) and isset($this->baseData['highPressure']) and isset($this->baseData['superheatTemp']) and isset($this->units['pressure'])){
            $iapws = new Steam_IAPWS();
            $mS = Steam_MeasurementSystem::getInstance();
            $this->baseData['boilerTemp'] = $mS->localize($iapws->saturatedTemperature($mS->standardize($this->baseData['highPressure'],'pressure',$this->units['pressure'])),'temperature',$this->units['temperature'])+$this->baseData['superheatTemp'];
        }
        if (isset($data[3]) ) $this->adjustedData = $data[3];
        
        $this->setMeauresmentSystem();
        $unitTypes = Steam_DTO::unitTypes('baseModel');
        
        //Create field specific unit list
        foreach($unitTypes as $key => $unitType){
            if ($unitType) {
                if (substr($unitType,0,9)<>'unitcost.'){
                    $this->convert[$key] = $this->modelSelected[$unitType];
                }else{
                    $this->convert[$key] = $this->modelSelected[substr($unitType,9)];
                }
            }else{
                $this->convert[$key] = false;
            }
        }
        
        $this->loadBaseModel();
        if (isset($data[3]) and $uploadType<>'base' and !$this->corruptUpload) $this->loadAdjustedModel();
        if (isset($data[3]) and $uploadType=='switch' and !$this->corruptUpload) $this->switchAM ();        
    }
    
    /**
     * Attempt to load Base Model
     */
    public function loadBaseModel(){        
        $this->baseDTO = new Steam_DTO('baseModel', $this->baseData, $this->convert);
        $baseForm = new Steam_Model_BaseForm();
        if ($baseForm->isValid($this->baseDTO->unload())){
            $this->sMc->loadBaseModel($this->baseDTO);
            $this->sMc->setStatus('Base Model Loaded');      
        }else{            
            $this->sMc->setStatus('Base Model Invalid. Manual re-entry required.',true);  
            $this->corruptUpload = true;
        }
    }
    
    /**
     * Attempt to load Adjusted Model
     */
    public function loadAdjustedModel(){
        $this->adjustedDTO = new Steam_DTO('baseModel', $this->adjustedData, $this->convert);
        $adjustedForm = new Steam_Model_ProjectsForm($this->sMc->baseModel);
        if ($adjustedForm->isValid($this->adjustedDTO->unload())){
            $this->sMc->saveProjects($this->adjustedDTO);        
            $this->sMc->setStatus('Base and Adjusted Model Loaded');
        }else{            
            $this->sMc->setStatus('Base Model Loaded / Adjusted Model Invalid. Manual re-entry required.',true);  
            $this->corruptUpload = true;
        }
    }
    
    /**
     * Switch Adjusted Model in for Base Model
     */
    public function switchAM(){
        $this->sMc->adjustedToBase();    
        $this->sMc->setStatus('Adjusted Model Loaded as Base Model');
    }
    
    /**
     * Set the field specific units
     */
    public function setMeauresmentSystem(){      
        $mS = Steam_MeasurementSystem::getInstance();
        
        $systemUnitTypes = Steam_MeasurementSystem::unitTypes(); 
        $systemDefaults = Steam_MeasurementSystem::defaultUnits();
        
        if ($this->units['systemSelected']=='Custom'){
            foreach($systemUnitTypes as $type => $name){
                $this->modelSelected[$type] = $this->units[$type];                
            }
        }else{
            foreach($systemUnitTypes as $type => $name){
                $this->modelSelected[$type] = $systemDefaults[$this->units['systemSelected']][$type];                
            }
        }
        $this->modelSelected['temperaturediff'] = $this->modelSelected['temperature'];
    }
}