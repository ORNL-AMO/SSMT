<?php
/**
 * Steam Calculators
 *
 * @package    View_Scripts Controller
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com> 
 */

class IndexController extends Zend_Controller_Action{

    public function init(){
        
        $this->_helper->_layout->setLayout('amo');
        $this->view->layout()->menu = 'baseMenu.phtml';
        $this->view->preferences = new Zend_Session_Namespace('preferences');      
                        
        global $CURRENCY_SYMBOL;
        $CURRENCY_SYMBOL = '$';
        if (isset($this->view->preferences->currencySymbol) ) $CURRENCY_SYMBOL = $this->view->preferences->currencySymbol;
        
        $this->view->translator = Zend_Registry::get('Zend_Translate');                
        
        if (!isset($this->view->preferences->language) ) {
            $language = 'en';
            $browserLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            if (substr( $browserLanguage, 0, 2)=='zh') $language = 'cn';
            $this->view->preferences->language = $language;            
        }
        $this->view->translator->setLocale($this->view->preferences->language);
        
        $this->view->printString = '?pv=1';
        $pv = $this->getRequest()->getParam('pv');
        if ($pv == 1) $this->_helper->_layout->setLayout('printable');
                
        if (!isset($this->view->preferences->temperature)) {            
            $systemUnitTypes = Steam_MeasurementSystem::unitTypes();
            $systemDefaults['Imperial'] = Steam_MeasurementSystem::defaultUnits('Imperial');
            $this->view->preferences->systemSelected = 'Imperial';
            foreach($systemUnitTypes as $type => $name) $this->view->preferences->$type =  $systemDefaults['Imperial'][$type];
        }
        $this->view->steamModelCommunicator = new Steam_Model_Communicator();
        $this->view->sMc = $this->view->steamModelCommunicator;
    }

    public function indexAction(){}
    
    public function aboutAction(){}

    public function glossaryAction(){}  

    public function resourcesAction(){}  


    //Property Calculators **************
    
    public function propsaturatedAction(){}

    public function propsteamAction(){}
    
    public function propertiesdownloadAction(){        
        $this->_helper->_layout->disableLayout();
        $steamPropertyList = new Zend_Session_Namespace('steamPropertyList');
        new Steam_Download_SteamProperties($steamPropertyList->steamProps, $steamPropertyList->satProps);
        $this->_helper->viewRenderer->setNoRender(true);
    }
    
    public function diagramtsAction(){
        $this->_helper->_layout->disableLayout();
    }    

    //Equipment Calculators **************
    
    public function equipboilerAction(){}

    public function equipheatlossAction(){}
   
    public function equipflashtankAction(){}
   
    public function equipprvAction(){}

    public function equipheaderAction(){}
    
    public function equipdeaeratorAction(){}

    public function equipturbineAction(){}
    
    //Steam System Modeler **************   
    
    public function overviewAction(){  
        if (isset($_POST['switchAB']) ){
            $this->view->sMc->adjustedToBase();
            $this->view->sMc->setStatus('The Adjusted Model is NOW the Base Model');
        }

        if (isset($_GET['example']) ){
            $example = new Steam_Model_Example($_GET['example']);
            if ($example->baseModelLoaded) $this->view->sMc->loadBaseModel($example->unloadBase());   
            if ($example->adjustedModelLoaded) $this->view->sMc->saveProjects($example->unloadAdjusted());         
            $this->view->sMc->setStatus($example->status);
        }

        if (isset($_GET['destroyModel']) and $_GET['destroyModel']=='now'){             
            $this->view->sMc->deleteEntireModel();    
            $this->view->sMc->setStatus('Model Cleared');
        }
        if (isset($_GET['destroyAdjModel']) and $_GET['destroyAdjModel']=='now'){ 
            $this->view->sMc->deleteAdjustedModel(); 
            $this->view->sMc->setStatus('Adjusted Model Cleared');
        }       
    }
    
    public function modelreloadAction(){        
        if (isset($_POST['reload']) ){
            new Steam_Model_Reload( $_POST['uploadModel'], $_POST['reloadType'] );
            $this->_redirect('overview');
        }
    }    
    
    public function basemodelAction(){}

    public function basediagramAction(){
        if (!$this->view->steamModelCommunicator->baseLoaded) $this->_forward('baseModel');
    }
    
    public function baseenergyAction(){
        if (!$this->view->steamModelCommunicator->baseLoaded) $this->_forward('baseModel');
    }
    
    public function basesankeyAction(){        
        if (!$this->view->steamModelCommunicator->baseLoaded) $this->_forward('baseModel');
        $this->_helper->_layout->disableLayout();                
        $tmp = new Steam_Model_Sankey($this->view->sMc->baseModel);
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function adjustedmodelAction(){
        if (!$this->view->steamModelCommunicator->baseLoaded) $this->_forward('baseModel');
    }
       
    public function adjusteddiagramAction(){
        if (!$this->view->steamModelCommunicator->adjustedLoaded) $this->_forward('adjustedModel');        
    }
    
    public function adjustedenergyAction(){
        if (!$this->view->steamModelCommunicator->adjustedLoaded) $this->_forward('adjustedModel');
    }
       
    public function adjustedsankeyAction(){
        if (!$this->view->steamModelCommunicator->adjustedLoaded) $this->_forward('adjustedModel');
        $this->_helper->_layout->disableLayout();                
        $tmp = new Steam_Model_Sankey($this->view->sMc->adjustedModels);
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function steambalanceAction(){
        if (!$this->view->steamModelCommunicator->baseLoaded) $this->_forward('baseModel');
    }
    
    public function modelcomparisonAction(){
        if (!$this->view->steamModelCommunicator->adjustedLoaded) $this->_forward('adjustedModel');
    }
    
    public function downloadmodelAction()
    {        
        $this->_helper->_layout->disableLayout();
        $activeModel = $this->view->steamModelCommunicator;
        $tmp = new Steam_Download_Model($activeModel->baseModel, $activeModel->adjustedModels, $activeModel->baseDTO );
        $this->_helper->viewRenderer->setNoRender(true);
    }    
    
    public function exportAction(){
        
    }
    
    public function downloadxmlAction()
    {        
        $this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $activeModel = $this->view->steamModelCommunicator;
        $tmp = new Steam_Download_OppTrackerXML($this->view->sMc);                               
    } 

    //Preferences
    
    public function preferencesAction()
    {       
        $prefForm = new Steam_Forms_Preferences();
        
        if (isset($_POST['Enter'])){
            if ($prefForm->isValid ($_POST)){
                
                if ( $prefForm->getValue('presistent')=='Yes' ){
                    Zend_Session::rememberMe(0);
                }else{
                    Zend_Session::forgetMe();
                }

                $this->view->preferences->language = $prefForm->getValue('language');
                $this->view->preferences->currencySymbol = $prefForm->getValue('currencySymbol');
                $this->view->preferences->presistent = $prefForm->getValue('presistent');
                $this->view->translator->setLocale($this->view->preferences->language);
                $this->view->preferences->systemSelected = $prefForm->getValue('systemSelected');

                $systemUnitTypes = Steam_MeasurementSystem::unitTypes();
                $systemDefaults = Steam_MeasurementSystem::defaultUnits();

                if ($this->view->preferences->systemSelected=='Custom'){
                    foreach($systemUnitTypes as $type => $name) $this->view->preferences->$type = $prefForm->getValue($type);
                }else{
                    foreach($systemUnitTypes as $type => $name) $this->view->preferences->$type =  $systemDefaults[$this->view->preferences->systemSelected][$type];    
                }

                $this->view->sMc->setStatus('Preferences Updated Successfully');            
            }
        }
        $this->view->prefForm = new Steam_Forms_Preferences();
    }
   
    //Autoload Languages
    
    public function cnAction()
    {
        $this->view->preferences->language = 'cn';
        $this->_redirect('/');
    }

    public function enAction()
    {
        $this->view->preferences->language = 'en';
        $this->_redirect('/');
    }

    public function ruAction()
    {
        $this->view->preferences->language = 'ru';
        $this->_redirect('/');
    }

    public function idAction()
    {
        $this->view->preferences->language = 'id';
        $this->_redirect('/');
    }
    
    public function tutorialsAction(){
        
    }

}

