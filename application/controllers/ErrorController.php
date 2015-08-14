<?php
/**
 * Steam Calculators
 *
 * @package    View_Scripts Controller
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com> 
 */

class ErrorController extends Zend_Controller_Action
{
    public function init(){  
        
        $this->_helper->_layout->setLayout('amo');
        $this->view->layout()->menu = 'baseMenu.phtml';
        
        $this->view->preferences = new Zend_Session_Namespace('preferences');        
                
        $this->view->translator = Zend_Registry::get('Zend_Translate');
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
    
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        
        $this->view->request   = $errors->request;
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}