<?php
/**
 * Steam Calculators
 *
 * @package    View_Scripts Controller
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com> 
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _initDoctype()
    {
        define("TOOL_VERSION", 'v0.9');
        define("TOOL_BUILD_DATE", '3/17/2015');
                
        define("PRESSURE_MINIMUM", .001);
        define("PRESSURE_MAXIMUM", 100);
        
        define("TEMPERATURE_MINIMUM", 273.15);        
        define("TEMPERATURE_MAXIMUM", 1073.15);
        
        define("TEMPERATURE_CRITICAL_POINT", 647.096);
        define("PRESSURE_CRITICAL_POINT", 22.064);
        
        define("TEMPERATURE_TRIPLE_POINT", 273.16);
        define("PRESSURE_TRIPLE_POINT", 0.0006117);
        
        define("SPECIFIC_ENTHALPY_MINIMUM", 50);
        define("SPECIFIC_ENTHALPY_MAXIMUM", 3700);
        
        define("SPECIFIC_ENTROPY_MINIMUM", 0);
        define("SPECIFIC_ENTROPY_MAXIMUM", 6.52);
                
        define("MASSFLOW_MINIMUM", 0);
        define("MASSFLOW_MAXIMUM", 4535923.7011291);
        
        define("HEATLOST_PERCENT_MIN", 0);
        define("HEATLOST_PERCENT_MAX", 10);
        
        define("BLOWDOWN_RATE_MIN", 0);
        define("BLOWDOWN_RATE_MAX", 25);
        
        define("COMBUSTION_EFF_MIN", 50);
        define("COMBUSTION_EFF_MAX", 100);
        
        define("ISOEFF_MIN", 20);
        define("ISOEFF_MAX", 100);
        
        define("GENEFF_MIN", 50);
        define("GENEFF_MAX", 100);
        
        define("DA_VENTRATE_MIN", 0);
        define("DA_VENTRATE_MAX", 10);

        define("MINIMUM_ERROR", 1e-10);        
        
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('HTML5');
        
        $view->addHelperPath('../library/Steam/Helper', 'Steam_Helper');

        $locale = new Zend_Locale('en_US');
        Zend_Registry::set('Zend_Locale', $locale);

        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace(array('Steam_', 'User_'));        
        
        $translate = new Zend_Translate(
            array(
                'adapter' => 'csv',
                'content' => APPLICATION_PATH . '/../languages/lang.en',
                'locale'  => 'en',
                'delimiter' => ';',
                )
                );
        $translate->addTranslation(
            array(
                'content' => APPLICATION_PATH . '/../languages/lang.de',
                'locale'  => 'gr'
                )
                );
        $translate->addTranslation(
            array(
                'content' => APPLICATION_PATH . '/../languages/lang.ru',
                'locale'  => 'ru'
                )
                );
        $translate->addTranslation(
            array(
                'content' => APPLICATION_PATH . '/../languages/lang.cn',
                'locale'  => 'cn'
                )
                );
        $translate->addTranslation(
            array(
                'content' => APPLICATION_PATH . '/../languages/lang.id',
                'locale'  => 'id'
                )
                );
        Zend_Registry::set('Zend_Translate', $translate);        
    }

    public function _initRoutes()
    {
        $front = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        $route = new Zend_Controller_Router_Route(':action',
                array(
                    'controller' => 'index',
                    'action' => ':action'
                ));
        $router->addRoute('index', $route);

        $route = new Zend_Controller_Router_Route('/',
                array(
                    'controller' => 'index',
                    'action' => 'index'
                ));
        $router->addRoute('indexindex', $route);
    }
}

