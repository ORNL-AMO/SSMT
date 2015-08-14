<?php
/**
 * Steam Calculators
 *
 * @package    View_Scripts Controller
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com> 
 */

/**
 * Steam_LanguageLoader
 *
 * @package    Steam
 */
class Steam_LanguageLoader {

    /**
     * Constants
     */
    const LangFilePath = '/../languages/';
    
    /**
     * Loaded Zend Translator
     * @var Zend_Translate object
     */
    var $translator = null;
    
    /**
     * Array of language translations for selected alternate language
     * @var array
     */
    var $langArray = array();
    
    /**
     * Array of master list of messages to translate
     * @var array
     */
    var $masterList = array();
    
    /**
     * Constructor
     *
     * $spec may be:
     * - string: name of element
     * - array: options with which to configure element
     * - Zend_Config: Zend_Config with options for configuring element
     *
     * @param  string|array|Zend_Config $spec
     * @param  array|Zend_Config $options
     * @return void
     * @throws Zend_Form_Exception if no element name after initialization
     */
    
    /**
     * Constructor
     * 
     * @param string $selectedLang Selected Language Code
     */
    function __construct($selectedLang = false) {
        //@TODO handle 'cn' to 'zh_CN' and similar options
        //@TODO handle no selected language
        $this->translator = Zend_Registry::get('Zend_Translate');
        if ($selectedLang) $this->setLang($selectedLang);
    }
    
    /**
     * Load Selected Language
     * @param string $selectedLang 
     */
    function setLang($selectedLang){
        $this->selectedLang = $selectedLang;
        $this->langArray = $this->translator->getMessages($selectedLang);
        $this->getMasterList();
    }
    
    /**
     * Draw Language Form
     */
    function drawLangForm(){
        echo "<table class='data'>";
        foreach($this->masterList as $en => $message){
            echo "<form method='POST' action=''><tr>";
            echo "<th style='width: 200px;'>".$message."</th>";
            $lang = null;
            if (isset($this->langArray[$message]) ) $lang = $this->langArray[$message];
            echo "<th style='width: 200px;'>".$lang."</th>";
            echo "<td>
                <input type='hidden' name='en' value='{$message}'>
                <input type='text' name='lang' value='".$lang."' style='width: 200px;'></td>";
            echo "<td><input type='submit' value='update'></td>";
            echo "</tr></form>";
        }
        echo "</table>";
    }
            
    /**
     * Update a Message Translation and Language File
     * 
     * If $messageLang is blank, entry in language file for $messageEN is cleared
     * Throws Error is $messageEN not in loaded master list
     * 
     * @param string $messageEN Regular Message
     * @param string $messageLang New Message Translation
     */
    function updateMessage($messageEN, $messageLang){        
        if ( isset($this->masterList[$messageEN]) ){
            if (isset($messageLang) and $messageLang<>'') {
                $this->langArray[$messageEN] = $messageLang;
            }else{
                unset($this->langArray[$messageEN]);
            }
        }else{
            throw new Zend_Exception("Message '".$messageEN."' not found in master list file.");
        }
        $this->updateLangFile();     
    }
            
    /**
     * Update ALL Message Translations and Language File
     * 
     * If a message is blank or not included, entry in language file for that message is cleared
     * Throws Error is a message is not in loaded master list
     * 
     * @param array $messageArray Updated Message Translations
     */
    function updateAllMessage($messageArray){        
        if ( empty ($messageArray) ) throw new Zend_Exception ("Translation Empty");
        foreach($messageArray as $en => $lang){
            if ( !isset($this->masterList[$en]) ) throw new Zend_Exception("Message '".$en."' not found in master list file.");            
        }
        $this->langArray = $messageArray;
        $this->updateLangFile();     
    }
    
    /**
     * Update selected language file with current translation array
     */
    function updateLangFile(){        
        //@TODO select more than 'cn'
        $fh = fopen(self::LangFilePath.'lang.cn', 'w');
        foreach($this->langArray as $en => $lang){
            $stringData = "\"".$en."\";\"".$lang."\"\n";
            fwrite($fh, $stringData);
        }        
    }
    
    /**
     * Loads Master List from master list file
     * @return array() Loaded Master List
     */
    function getMasterList(){
        $this->masterList = array();
        $masterListTmp = file( APPLICATION_PATH . self::LangFilePath.'masterList.en', FILE_IGNORE_NEW_LINES);
        foreach($masterListTmp as $message) $this->masterList[$message] = $message;
        return $this->masterList;        
    }
       
    /**
     * Overwrites Master List file with current master list array 
     *  - Loads Master List File
     *  - Adds Untranslated Messages from Untranslated Log
     *  - Removes Message from the Ignore List file
     *  - Writes to master list file
     * @return array() Updated Master List
     */
    function updateMasterList(){
        $masterList = $this->getMasterList();
        
        $masterListAdditionsTmp = file( APPLICATION_PATH . self::LangFilePath.'untranslated.log', FILE_IGNORE_NEW_LINES);
        foreach($masterListAdditionsTmp as $message) $masterList[$message] = $message;
        
        $ignoreListTmp = file( APPLICATION_PATH . self::LangFilePath.'ignoreList.en', FILE_IGNORE_NEW_LINES);
        foreach($ignoreListTmp as $message) unset($masterList[$message]);
        
        unset($masterList['']);
        asort($masterList);
        $this->masterList = $masterList;
        $fh = fopen( APPLICATION_PATH . self::LangFilePath.'masterList.en', 'w');
        foreach($this->masterList as $message) fwrite($fh, $message."\n");
        return $this->masterList;                       
    }
   
}