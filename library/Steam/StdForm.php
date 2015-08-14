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
 * Standard Base Form for all Steam Calculator Forms
 * 
 * @package    Steam
 * @subpackage Steam_Forms
 */
class Steam_StdForm extends Zend_Form{
    
    /**
     * Field Decorations
     * @var array
     */
    public $fieldDecorations = array();
    
    /**
     * Float Validator with custom message
     * @var \Zend_Validate_Float
     */
    public $isFloat;
    
    
    /**
     * Steam MeasurementSystem Object
     * @var Steam_MeasurementSystem
     */
    var $mS;
    /**
     * Zend_Translate
     * @var Zend_Translate
     */
    public $translator;
    
    /**
     * Array of Field Text for loaded steam fields
     * @var array
     */
    public $fieldText;
    /**
     * Array of Names of Steam Field Sets
     * @var array 
     */
    public $steamNames = array();
    
    /**
     * Construct Std Form
     * @param array $options
     */
    public function __construct($options = null) {
        $this->translator = Zend_Registry::get('Zend_Translate');       
        $this->mS = Steam_MeasurementSystem::getInstance();
        
        $this->isFloat = self::isFloat();       
        
        $this->fieldDecorations = array(
            'ViewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
            array('Label', array('tag' => 'th')),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        );
        
        $this->setAction('?');
        parent::__construct($options);
       
        $submit = $this->createElement('submit', 'Enter')
            ->setDecorators(array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => '"2"', 'style' => 'text-align: center;')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ));

        $this->addElements(array(
            $submit
        ));
        
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table', 'class' => 'data')),
            'Form',
        ));
        
    }
    
    /**
     * Create Steam_Objects for steam fields matching $name
     * @param string $name
     * @return \Steam_Object
     */
    public function getSteamObject($name = ''){
        $data = $this->getValues();
        $secondParameter = ($data[$name.'SecondParameter']) ;
        $secondParameter{0} = strtolower($secondParameter{0});
        $steamObject = new Steam_Object(array(
            $secondParameter => $this->mS->standardize( $data[$name.($data[$name.'SecondParameter'])], $secondParameter ),
            'pressure' => $this->mS->standardize( $data[$name.'Pressure'], 'pressure' ),
            ));
        return $steamObject;
    }
    
    /**
     * Returns string of HTML for steam fields matching $name
     * @param $string $name
     * @return string
     * @throws Exception if $name not found
     */
    public function displaySteamFields($name = ''){        
        if ( array_search($name, $this->steamNames, true)===false ) throw new Exception("'".$name."' not found");

        $pressure = $name.'Pressure';
        $secondParameter = $name.'SecondParameter';
        $temperature = $name.'Temperature';
        $specificEnthalpy = $name.'SpecificEnthalpy';
        $specificEntropy = $name.'SpecificEntropy';
        $quality = $name.'Quality';
        
        $result = '
        <tr id="'.$name.'PressureRow">
            <th style="width: 140px;">'.$this->translator->_('Pressure').'*</th>
            <td style="width: 120px;">'.$this->$pressure->renderViewhelper().' '.$this->mS->label('pressure').'</td>';
            if ($errors = $this->$pressure->renderErrors()) $result .= "<td>{$errors}</td>";
        $result .= "</tr>
        <tr id='{$name}SecondRow'>
            <th>{$this->$secondParameter->renderViewhelper()} *</th>
            <td>
            <span id=\"{$temperature}Row\">{$this->$temperature->renderViewhelper()} {$this->mS->label('temperature')}</span>
            <span id=\"{$specificEnthalpy}Row\">{$this->$specificEnthalpy->renderViewhelper()} {$this->mS->label('specificEnthalpy')}</span>
            <span id=\"{$specificEntropy}Row\">{$this->$specificEntropy->renderViewhelper()} {$this->mS->label('specificEntropy')}</span>
            <span id=\"{$quality}Row\">{$this->$quality->renderViewhelper()}</span>
            </td>";
            
            if ($errors = $this->$secondParameter->renderErrors()) $result .= "<td>{$errors}</td>";
            if ($errors = $this->$temperature->renderErrors()) $result .= "<td>{$errors}</td>";
            if ($errors = $this->$specificEnthalpy->renderErrors()) $result .= "<td>{$errors}</td>";
            if ($errors = $this->$specificEntropy->renderErrors()) $result .= "<td>{$errors}</td>";
            if ($errors = $this->$quality->renderErrors()) $result .= "<td>{$errors}</td>";
        $result .= "</tr>";
        return $result;
    }
    
    /**
     * Returns JavaScript Code required for steam property fields
     * @return string JavaScript Code
     */
    public function jqueryAdd(){        
        $fieldText = $this->fieldText;
        $result = "";
        foreach($this->steamNames as $name){        
            $result .= "
            {$name}showPicked();
            $('#{$name}SecondParameter').change(function(){
                {$name}showPicked();
            });
            function {$name}showPicked(){

                $('#{$name}TemperatureRow').hide();
                $('#{$name}SpecificEnthalpyRow').hide();
                $('#{$name}SpecificEntropyRow').hide();
                $('#{$name}QualityRow').hide();
                $('#' + '{$name}' + $('#{$name}SecondParameter').val() + 'Row').show();
            }
            ";

            $result .= self::hintDetails(array(
                    'type' => 'maxPressure',
                    'fieldID' => $name."Pressure",
                    'title' => $name." Pressure",
                    'text' => isset($fieldText[$name]['Pressure'])?$fieldText[$name]['Pressure']:"",
                ));         
            $result .= self::hintDetails(array(
                    'type' => 'maxTemperature',
                    'fieldID' => $name."Temperature",
                    'title' => $name." Temperature",
                    'text' => isset($fieldText[$name]['Temperature'])?$fieldText[$name]['Temperature']:"",
                ));               
            $result .= self::hintDetails(array(
                    'type' => 'specificEnthalpy',
                    'fieldID' => $name."SpecificEnthalpy",
                    'title' => $name." Specific Enthalpy",
                    'text' => isset($fieldText[$name]['SpecificEnthalpy'])?$fieldText[$name]['SpecificEnthalpy']:"",
                ));                            
            $result .= self::hintDetails(array(
                    'type' => 'specificEntropy',
                    'fieldID' => $name."SpecificEntropy",
                    'title' => $name." Specific Entropy",
                    'text' => isset($fieldText[$name]['SpecificEntropy'])?$fieldText[$name]['SpecificEntropy']:"",
                ));         
            $result .= self::hintDetails(array(
                    'type' => 'quality',
                    'fieldID' => $name."Quality",
                    'title' => $name." Quality",
                    'text' => isset($fieldText[$name]['Quality'])?$fieldText[$name]['Quality']:"",
                ));                                                               
        }
        
        $result .= "  
            
            $('#fieldDetails').hide();
                
            function highlightRange(field, min, max, exclusive){                    
                if (exclusive==false){
                    if ($(field).val()<min || $(field).val()==''){ $('#fieldDetailsMin').attr('style', 'color: red;'); }
                    if ($(field).val()>=min && $(field).val()!=''){ $('#fieldDetailsMin').attr('style', 'color: blue;'); }
                    if (max!=-999999 && $(field).val()>max){ $('#fieldDetailsMax').attr('style', 'color: red;'); }
                    if (max==-999999 || $(field).val()<=max){ $('#fieldDetailsMax').attr('style', 'color: blue;'); }
                    if ($(field).val()>=min && $(field).val()!='' && ($(field).val()<max || max==-999999 || true) ){ $('#fieldDetailsRange').html(\"<span style='color: blue; text-align: center;'>".$this->translator->_('Acceptable')."</span>\"); }
                    if ($(field).val()<min || $(field).val()=='' || ($(field).val()>max && max!=-999999) ){ $('#fieldDetailsRange').html(\"<span style='color: red; text-align: center;'>".$this->translator->_('Out of Acceptable Range')."</span>\"); }
                }else{
                    if ($(field).val()<=min || $(field).val()==''){ $('#fieldDetailsMin').attr('style', 'color: red;'); }
                    if ($(field).val()>min && $(field).val()!=''){ $('#fieldDetailsMin').attr('style', 'color: blue;'); }
                    if (max!=-999999 && $(field).val()>=max){ $('#fieldDetailsMax').attr('style', 'color: red;'); }
                    if (max==-999999 || $(field).val()<max){ $('#fieldDetailsMax').attr('style', 'color: blue;'); }
                    if ($(field).val()>min && $(field).val()!='' && ($(field).val()<max || max==-999999) ){ $('#fieldDetailsRange').html(\"<span style='color: blue; text-align: center;'>".$this->translator->_('Acceptable')."</span>\"); }
                    if ($(field).val()<=min || $(field).val()=='' || ($(field).val()>=max && max!=-999999) ){ $('#fieldDetailsRange').html(\"<span style='color: red; text-align: center;'>".$this->translator->_('Out of Acceptable Range')."</span>\"); }                    
                }                        
            }  
        ";
        return $result;           
    }
    
    /**
     * Add a Set of Steam Form Fields using $name
     * @param string $name
     */
    public function addSteamFields($name = ''){
        $this->steamNames[] = $name;
        $key = $name.'Pressure';
        $field[$key] = $this->createElement('text', $key)
            ->setLabel($this->translator->_('Pressure').' ('.$this->mS->label('pressure').')')
            ->setDecorators($this->fieldDecorations)
                ->setAttrib("style", "width: 60px;");
        $key = $name.'SecondParameter';
        $field[$key] = $this->createElement('select', $key)
            ->addMultiOptions(array(
                'Temperature' => 'Temperature',
                'SpecificEnthalpy' => 'Specific Enthalpy',
                'SpecificEntropy' => 'Specific Entropy',
                'Quality' => 'Saturated Quality',
                    ))
            ->setLabel('Second Parameter')
            ->setValue('temperature')
            ->setDecorators($this->fieldDecorations);
          
        $key = $name.'Temperature';
        $field[$key] = $this->createElement('text', $key)
            ->setLabel($this->translator->_('Temperature').' ('.$this->mS->label('temperature').')')
            ->setDecorators($this->fieldDecorations)
                ->setAttrib("style", "width: 60px;");        
        
        $key = $name.'SpecificEnthalpy';
        $field[$key] = $this->createElement('text', $key)
            ->setLabel($this->translator->_('Specific Enthalpy').' ('.$this->mS->label('specificEnthalpy').')')
            ->setDecorators($this->fieldDecorations)
                ->setAttrib("style", "width: 60px;");        
        
        $key = $name.'SpecificEntropy';
        $field[$key] = $this->createElement('text', $key)
            ->setLabel($this->translator->_('Specific Entropy').' ('.$this->mS->label('specificEntropy').')')
            ->setDecorators($this->fieldDecorations)
                ->setAttrib("style", "width: 60px;");
             
        $key = $name.'Quality';
        $field[$key] = $this->createElement('text', $key)
            ->setLabel($this->translator->_('Quality').' (0 to 1)')
            ->setDecorators($this->fieldDecorations)
                ->setAttrib("style", "width: 60px;");
        
        $this->addElements($field);
    }
    
    /**
     * Determines if Form Data is Valid (true => valid, false => invalid)
     * @param array() $data Form Data
     * @return boolean Valid
     */
    public function isValid($data) {        
        $mS = $this->mS;
        
        foreach($this->steamNames as $name){ 
            $this->getElement($name.'Pressure')->setRequired('true')
                ->addValidator($this->isFloat,true)                        
                ->addValidator('between', true, array('min' => $this->mS->minPressure(), 'max' => $this->mS->maxPressure(), 'inclusive' => true));
            
            if (isset($data[$name.'SecondParameter']) and $data[$name.'SecondParameter']=='Quality' and $data[$name.'Pressure']>$this->mS->critPressure()) {
                $this->getElement($name.'Pressure')->addValidator ('lessThan', true, array('max' => $this->mS->critPressure(), 'messages' => 'Pressure cannot be in supercritical region (>%max%) when Quality is selected.'));
            }
            
            if (isset($data[$name.'SecondParameter'])){
                switch ($data[$name.'SecondParameter']){
                    case 'Temperature':
                        $this->getElement($name.'Temperature')->setRequired('true')
                            ->addValidator($this->isFloat,true)    
                        ->addValidator('between', true, array('min' => $mS->minTemperature(), 'max' => $mS->maxTemperature(), 'inclusive' => true));
                        break;
                    case 'SpecificEnthalpy':
                        $this->getElement($name.'SpecificEnthalpy')->setRequired('true')   
                            ->addValidator($this->isFloat,true)                     
                        ->addValidator('between', true, array('min' => $mS->minSpecificEnthalpy(), 'max' => $mS->maxSpecificEnthalpy(), 'inclusive' => true));
                        break;
                    case 'SpecificEntropy':
                        $this->getElement($name.'SpecificEntropy')->setRequired('true')
                            ->addValidator($this->isFloat,true)    
                        ->addValidator('between', true, array('min' => $mS->minSpecificEntropy(), 'max' => $mS->maxSpecificEntropy(), 'inclusive' => true));
                        break;
                    case 'Quality':
                        $this->getElement($name.'Quality')->setRequired('true')
                            ->addValidator($this->isFloat,true)    
                            ->addValidator('Between', true, array('min' => 0, 'max' => 1));
                        break;
                }
            }else{                    
                $this->getElement($name.'Temperature')->setRequired('true')                    
                ->addValidator('between', true, array('min' => $mS->minTemperature(), 'max' => $mS->maxTemperature(), 'inclusive' => true));
            }
        }        
        return parent::isValid($data);  
    }
    
    /**
     * Returns array of function names specific to $secondParameter
     * @param string $secondParameter
     * @return array 
     */
    public static function secondParameterDetails($secondParameter){
        $secondParameterDetails = array(
            'Temperature' => array(
                0 => 'Temperature',
                1 => 'temperature',
                2 => 'displayTemperatureLabeled',
                3 => 'rawTemperature',
            ),
            'SpecificEnthalpy' => array(
                0 => 'Specific Enthalpy',
                1 => 'specificEnthalpy',
                2 => 'displaySpecificEnthalpyLabeled',
                3 => 'rawSpecificEnthalpy',
            ),
            'SpecificEntropy' => array(
                0 => 'Specific Entropy',
                1 => 'specificEntropy',
                2 => 'displaySpecificEntropyLabeled',
                3 => 'rawSpecificEntropy',
            ),
            'Quality' => array(
                0 => 'Quality',
                1 => 'quality',
                2 => 'displayQualityLabeled',
                3 => 'rawQuality',
            ),
        );
        return $secondParameterDetails[$secondParameter];
    }
    
    /**
     * Returns link with values for the Steam Properties Calculator
     * @param Steam_Object $steamObject
     * @param string $secondParameter
     * @return string Link
     */
    public static function propLink($steamObject, $secondParameter){            
        $mS = Steam_MeasurementSystem::getInstance();    
        $secParaDets = Steam_StdForm::secondParameterDetails($secondParameter);
        $link = "propSteam?Pressure={$mS->rawPressure($steamObject->pressure)}"
        ."&amp;{$secondParameter}={$mS->$secParaDets[3]($steamObject->$secParaDets[1])}"
        ."&amp;SecondParameter={$secondParameter}";
        return $link;
    }
    
    /**
     * Float Validator with custom message
     * @return \Zend_Validate_Float
     */
    public static function isFloat(){        
        $isFloat = new Zend_Validate_Float();
        $isFloat->setMessage("'%value%'  is not a valid number.", 'notFloat');
        return $isFloat;
    }
    
    /**
     * Generates JavaScript for form field hints
     * 
     * Includes:
     *      maxPressure
            critPressure
            maxTemperature
            critTemperature
            combustEff
            massflow
            massflowInclusive
            blowdown
            heatloss
            temperature
            isoeff
            geneff
            ventRate
            power
            quality
            specificEnthalpy
            specificEntropy
            condensateReturn
     * 
     * @param array() $content Hint Contents
     * @return String JavaScript Code
     */
    public static function hintDetails($content){
        $translator = Zend_Registry::get('Zend_Translate'); 
        if ( isset($content['type']) ) $content = array_merge (self::hintArray ($content['type']), $content);
        
        $exclusive = 'false';
        if (isset($content['exclusive']) ) $exclusive = $content['exclusive'];
        
        $details = "
            $('#{$content['fieldID']}').focus(function(){     
            
                position = $('#{$content['fieldID']}').position();
            
                $('#fieldDetailsTextRow').hide();
                $('#fieldDetailsMain').css('top',position.top-50);
                $('#fieldDetailsShadow').css('top',position.top-44);
                $('#fieldDetailsMain').css('left',position.left+140);
                $('#fieldDetailsShadow').css('left',position.left+146);
                $('#fieldDetails').show(); 
                $('#fieldDetailsTitle').html('".$translator->_(trim($content['title']))."'); 
                $('#fieldDetailsUnit').html('".$translator->_('Unit').": ".$translator->_($content['unit'])."'); ";
                if (isset($content['text']) and $content['text']<>''){
                $details .= "
                    $('#fieldDetailsTextRow').show();
                    $('#fieldDetailsText').html(\"".$translator->_($content['text'])."\"); ";
                        }
                $details .= "
                $('#fieldDetailsMin').html('{$content['minLabeled']}'); 
                $('#fieldDetailsMax').html('".$translator->_($content['maxLabeled'])."'); 
                highlightRange('#{$content['fieldID']}', {$content['min']}, {$content['max']}, {$exclusive});
                $('#{$content['fieldID']}').keyup(function(){ 
                    highlightRange('#{$content['fieldID']}', {$content['min']}, {$content['max']}, {$exclusive});
                });
                $('#fieldDetailsShadow').height($('#fieldDetailsMain').height()+5); 
            }); 
            $('#{$content['fieldID']}').blur(function(){ $('#fieldDetails').hide(); });                    
                    ";
        return $details;
    }
    
    static public function hintArray($type){
        $mS = Steam_MeasurementSystem::getInstance();
        $details = array();
        switch($type){
            case 'maxPressure':
                $details = array(
                    'unit'  => $mS->unitName('pressure'),                    
                    'min' => $mS->minPressure(),
                    'max' => $mS->maxPressure(),
                    'minLabeled' => $mS->minPressureLabeled(),
                    'maxLabeled' => $mS->maxPressureLabeled(),                    
                );
                break;
            case 'critPressure':
                $details = array(
                    'unit'  => $mS->unitName('pressure'),                    
                    'min' => $mS->minPressure(),
                    'max' => $mS->critPressure(),
                    'minLabeled' => $mS->minPressureLabeled(),
                    'maxLabeled' => $mS->critPressureLabeled(),                    
                );
                break;
            case 'condPressure':
                $details = array(
                    'unit'  => $mS->unitName('vacuum'),                    
                    'min' => $mS->minVacuum(),
                    'max' => $mS->condVacuum(),
                    'minLabeled' => $mS->minVacuumLabeled(),
                    'maxLabeled' => $mS->condVacuumLabeled(),                    
                );
                break;
            case 'maxTemperature':
                $details = array(
                    'unit'  => $mS->unitName('temperature'),            
                        'min' => $mS->minTemperature(),
                        'max' => $mS->maxTemperature(),
                        'minLabeled' => $mS->minTemperatureLabeled(),
                        'maxLabeled' => $mS->maxTemperatureLabeled(),                
                );
                break;
            case 'critTemperature':
                $details = array(
                    'unit'  => $mS->unitName('temperature'),             
                    'min' => $mS->minTemperature(),
                    'max' => $mS->critTemperature(),
                    'minLabeled' => $mS->minTemperatureLabeled(),
                    'maxLabeled' => $mS->critTemperatureLabeled(),                 
                );
                break;
            case 'combustEff':
                $details = array(
                    'title' => "Combustion Efficiency",
                    'unit'  => $mS->label('%'),
                    'text' => "Percent of fuel energy added to the feedwater.<BR><span style='font-style: italic;'>-Generally 75% to 85%",
                    'min' => COMBUSTION_EFF_MIN,
                    'max' => COMBUSTION_EFF_MAX,
                    'minLabeled' => number_format(COMBUSTION_EFF_MIN,1).' '.$mS->label('%'),
                    'maxLabeled' => number_format(COMBUSTION_EFF_MAX,1).' '.$mS->label('%'),
                    );
                break;
            case 'massflow':
                $details = array(
                    'unit'  => $mS->unitName('massflow'),
                    'min' => $mS->minMassflow(),
                    'max' => $mS->maxMassflow(),
                    'minLabeled' => '> '.$mS->minMassflowLabeled(),
                    'maxLabeled' => '< '.$mS->maxMassflowLabeled(),
                    'exclusive' => 'true',
                    );
                break;           
            case 'massflowInclusive':
                $details = array(
                    'unit'  => $mS->unitName('massflow'),
                    'min' => $mS->minMassflow(),
                    'max' => $mS->maxMassflow(),
                    'minLabeled' => $mS->minMassflowLabeled(),
                    'maxLabeled' => $mS->maxMassflowLabeled(),
                    'exclusive' => 'false',
                    );
                break;
            case 'blowdown':
                $details = array(                    
                    'title' => "Blowdown Rate",
                    'unit'  => $mS->label('%'),
                    'text' => "Percent of feedwater massflow leaving the boiler as blowdown.<BR><span style='font-style: italic;'>-Generally 1% to 5%",
                    'min' => BLOWDOWN_RATE_MIN,
                    'max' => BLOWDOWN_RATE_MAX,
                    'minLabeled' => number_format(BLOWDOWN_RATE_MIN,1).' '.$mS->label('%'),
                    'maxLabeled' => number_format(BLOWDOWN_RATE_MAX,1).' '.$mS->label('%'),
                );
                break;
            case 'heatloss':
                $details = array(                    
                    'unit'  => $mS->label('%'),
                    'min' => HEATLOST_PERCENT_MIN,
                    'max' => HEATLOST_PERCENT_MAX,
                    'minLabeled' => number_format(HEATLOST_PERCENT_MIN,1).' '.$mS->label('%'),
                    'maxLabeled' => number_format(HEATLOST_PERCENT_MAX,1).' '.$mS->label('%'),
                );                
                break;
            case 'temperature':
                $details = array(                    
                    'unit'  => $mS->label('temperature'),                        
                    'min' => $mS->minTemperature(),
                    'max' => $mS->maxTemperature(),
                    'minLabeled' => $mS->minTemperatureLabeled(),
                    'maxLabeled' => $mS->maxTemperatureLabeled(),
                );                
                break;
            case 'isoeff':
                $details = array(                
                    'unit'  => $mS->label('%'),
                    'min' => ISOEFF_MIN,
                    'max' => ISOEFF_MAX,
                    'minLabeled' => number_format(ISOEFF_MIN,1).' '.$mS->label('%'),
                    'maxLabeled' => number_format(ISOEFF_MAX,1).' '.$mS->label('%'),
                );
                break;
            case 'geneff':
                $details = array(                   
                    'unit'  => $mS->label('%'),
                    'min' => GENEFF_MIN,
                    'max' => GENEFF_MAX,
                    'minLabeled' => number_format(GENEFF_MIN,1).' '.$mS->label('%'),
                    'maxLabeled' => number_format(GENEFF_MAX,1).' '.$mS->label('%'),
                );
                break;
            case 'ventRate':
                $details = array(                   
                    'unit'  => $mS->label('%'),
                    'min' => DA_VENTRATE_MIN,
                    'max' => DA_VENTRATE_MAX,
                    'minLabeled' => number_format(DA_VENTRATE_MIN,1).' '.$mS->label('%'),
                    'maxLabeled' => number_format(DA_VENTRATE_MAX,1).' '.$mS->label('%'),
                );
                break;
            case 'power':
                $details = array(      
                    'unit'  => $mS->unitName('power'),
                    'min' => MINIMUM_ERROR,
                    'max' => -999999,
                    'minLabeled' => '> 0 '.$mS->label('power'),
                    'maxLabeled' => 'none',             
                );
                break;
            case 'quality':
                $details = array(                 
                    'unit'  => 'none',
                    'min' => 0,
                    'max' => 1,
                    'minLabeled' => 0,
                    'maxLabeled' => 1,
                );
                break;            
            case 'specificEnthalpy':
                $details = array(              
                    'unit'  => $mS->unitName('specificEnthalpy'),
                    'min' => $mS->minSpecificEnthalpy(),
                    'max' => $mS->maxSpecificEnthalpy(),
                    'minLabeled' => $mS->minSpecificEnthalpyLabeled(),
                    'maxLabeled' => $mS->maxSpecificEnthalpyLabeled(),
                );
                break;
            case 'specificEntropy':
                $details = array(               
                    'unit'  => $mS->unitName('specificEntropy'),
                    'min' => $mS->minSpecificEntropy(),
                    'max' => $mS->maxSpecificEntropy(),
                    'minLabeled' => $mS->minSpecificEntropyLabeled(),
                    'maxLabeled' => $mS->maxSpecificEntropyLabeled(),
                );
                break;
            case 'condensateReturn':
                $details = array(                
                    'unit'  => $mS->label('%'),
                    'min' => 0,
                    'max' => 100,
                    'minLabeled' => '0.0 '.$mS->label('%'),
                    'maxLabeled' => '100.0 '.$mS->label('%'),
                );
                break;
        }
            
        return $details;
    }    
}