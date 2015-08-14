<?php
/**
 * Steam Calculators
 *
 * @package    Steam
 * @subpackage Steam_Forms
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Preferences Form
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_Forms_Preferences extends Zend_Form{
    
    /**
     * Construct Form
     * @param array $options
     */
    public function __construct($options = null) {
        parent::__construct($options);
        $mS = Steam_MeasurementSystem::getInstance();
        $preferences = new Zend_Session_Namespace('preferences');   
        
        $language = $this->createElement('select', 'language')
                ->setDisableTranslator(true)
                ->setLabel('Language')
                ->addMultiOptions(array(
                    'en' => 'English',
                    'cn' => '中文',
                    'ru' => 'Russian',
                    ))
                ->setValue($preferences->language);
        
        $currencySymbol = $this->createElement('select', 'currencySymbol')
                ->setDisableTranslator(true)
                ->setLabel('Currency Symbol')
                ->addMultiOptions(array(
                    '$' => '$',
                    '¥' => '¥',
                    '€' => '€',
                    '£' => '£',
                    'KZT' => 'KZT',                    
                    ))
                ->setValue($preferences->currencySymbol);

        $systemSelected = $this->createElement('select', 'systemSelected')
                ->setLabel('Units')
                ->addMultiOptions(array(
                    'Imperial' => 'Imperial',
                    'SI' => 'SI',
                    'Chinese' => 'Chinese',
                    'Custom' => 'Custom',
                    ))
                ->setValue($preferences->systemSelected);
        
        $presistent = $this->createElement('select', 'presistent')
                ->setLabel('Presistent')
                ->addMultiOptions(array(
                    'No' => 'No',
                    'Yes' => 'Yes',
                    ))
                ->setValue($preferences->presistent);;
        
        $unitFields = array(
            $language,
            $currencySymbol,
            $systemSelected,
            $presistent,
        );
        
        foreach(Steam_MeasurementSystem::unitTypes() as $type => $name){        
            $unitFields[$type] = $this->createElement('select', $type)
                ->setLabel($name)
                ->addMultiOptions($mS->labels[$type])
                ->setValue($preferences->$type);
        }
        
        $unitFields['Enter'] = $this->createElement('submit', 'Enter')
                ->setLabel('UPDATE PREFERENCES')
                ->setAttrib('style', 'margin: 5px; font-weight: bold; font-size: 1.2em; color: blue;');
        $this->addElements($unitFields);
                
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table', 'class' => 'data')),
            'Form',
        ));
    }
}