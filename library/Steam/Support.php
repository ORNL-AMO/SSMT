<?php
/**
 * Steam Calculators
 * 
 * @package    Steam
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Contains Common Functions and Data for the Steam Calculators
 * @package    Steam
 */
class Steam_Support{    
      
    /**
     * Returns calculator descriptions
     * @param string $group
     * @param string $calculator
     * @return array
     */
    public static function descriptions($group = false, $calculator = false){
        $descriptions = array(
            'Properties' => array(
                'satProps' => array('Saturated Properties', 'Calculates saturated liquid and gas properties for a given pressure or temperature using the IAPWS Industrial Formulation 1997.', 'propSaturated'),
                'steamProps' => array('Individual Steam Properties', 'Calculates steam and liquid water properties given two properties using the IAPWS Industrial Formulation 1997.', 'propSteam'),
                ),
            'Equipment' => array(
                'boiler' => array('Boiler', 'Determines the amount of fuel energy required to produce steam with specified properties at a given flow rate using general boiler operational characteristics.', 'equipBoiler'),
                'heatloss' => array('Heat Loss', 'Calculates the energy (heat) loss and outlet steam properties given inlet steam conditions and a % heat loss.', 'equipHeatloss'),
                'flashtank' => array('Flash Tank', 'Determines the mass flows and properties of any resulting outlet gas and/or liquid for given inlet conditions.', 'equipFlashtank'),
                'prv' => array('PRV w/ Desuperheating', 'Calculates the properties of steam after a pressure drop with optional desuperheating.', 'equipPrv'),
                'header' => array('Header', 'Calculates the combined steam properties of multiple steam inlets.', 'equipHeader'),
                'deaerator' => array('Deaerator', 'Determines the required water and steam flows for a required feedwater mass flow.', 'equipDeaerator'),
                'steamTurbine' => array('Steam Turbine', 'Calculates the energy generated or steam outlet conditions for a steam turbine.', 'equipTurbine'),
                ),
            'Modeler' => array(
                'modeler' => array('Steam System Modeler', 'Creates a basic steam system model with up to 3 different pressure headers.', 'overview'),
            )
            );
        $result = $descriptions;
        if ($group and !$calculator) $result = $descriptions[$group];
        if ($group and $calculator) $result = $descriptions[$group][$calculator];
        return $result;
    }
    
    /**
     * Filters data based on header count to elimiate unused header data
     * @param array $properties
     * @return array $properties filtered
     */
    static public function headerAdjustment($properties){
        $filteredData = $properties;
        $highPressure = $properties['highPressure'];
        $headerCount = $properties['headerCount'];
        if ($headerCount<3){
            $filteredData['mediumPressure'] = $highPressure;
            $filteredData['mpSteamUsage'] = 0;
            $filteredData['hpCondFlash'] = 'No';
            $filteredData['mpHeatLossPercent'] = 0;
            $filteredData['desuperHeatMpLp'] = 'No';
            $filteredData['turbineHpMpOn'] = 0;
            $filteredData['turbineMpLpOn'] = 0;            
        }
        
        if ($headerCount<2){
            $filteredData['lowPressure'] = $highPressure;
            $filteredData['lpSteamUsage'] = 0;
            $filteredData['mpCondFlash'] = 'No';
            $filteredData['lpHeatLossPercent'] = 0;
            $filteredData['desuperHeatHpMp'] = 'No';
            $filteredData['turbineHpLpOn'] = 0;       
        }
        return $filteredData;
    }
        
    /**
     * Adds the approporiate navigation breadcrumbs based on the $url
     * @param string $url
     * @return string
     */
    public static function BreadCrumb($action, $base = false){
        $translator = Zend_Registry::get('Zend_Translate');
        
      
        if (!$base) $base = "<a href='./'>".$translator->_("Steam Calculators")."</a> &raquo; ";
        switch($action){
            case 'index':
                $crumb = $translator->_('Steam Calculators');
                break;
            case 'about':
                $crumb = $base.$translator->_("About");
                break;
            case 'glossary':
                $crumb = $base.$translator->_("Glossary");
                break;
            case 'preferences':
                $crumb = $base.$translator->_("Preferences");
                break;
            case 'resources':
                $crumb = $base.$translator->_("Steam Resources");
                break;
            case 'tutorials':
                $crumb = $base.$translator->_("Tutorials");
                break;
            case 'propSaturated':
                $crumb = $base.$translator->_("Saturated Properties Calculator");
                break;
            case 'propSteam':
                $crumb = $base.$translator->_("Individual Steam Properties Calculator");
                break;
            case 'equipBoiler':
                $crumb = $base.$translator->_("Boiler Calculator");
                break;
            case 'equipHeatloss':
                $crumb = $base.$translator->_("Heat Loss Calculator");
                break;
            case 'equipFlashtank':
                $crumb = $base.$translator->_("Flash Tank Calculator");
                break;
            case 'equipPrv':
                $crumb = $base.$translator->_("PRV Calculator");
                break;
            case 'equipHeader':
                $crumb = $base.$translator->_("Header Calculator");
                break;
            case 'equipDeaerator':
                $crumb = $base.$translator->_("Deaerator Calculator");
                break;
            case 'equipTurbine':
                $crumb = $base.$translator->_("Steam Turbine Calculator");
                break;
            case 'overview':
            case 'baseModel':
            case 'baseDiagram':
            case 'baseEnergy':
            case 'steamBalance':
            case 'adjustedModel':
            case 'adjustedDiagram':
            case 'adjustedEnergy':
            case 'modelComparison':   
            case 'modelReload':    
            case 'export':              
                $crumb = $base.$translator->_("Steam System Modeler");
                break;
            default:                                    
                $crumb = $base.$translator->_("not found");
                break;
        }
        return $crumb;
    }
    
    /**
     * Returns formatted warning, if any
     * @param type $equipmentObject
     * @return string warnings or blank
     */
    public static function displayWarnings($equipmentObject){
        $warnings = "";
        $translator = Zend_Registry::get('Zend_Translate');
        if ($equipmentObject->checkWarnings()>0){
            $warnings .= "<h1 style='color: red;'>".$translator->_('WARNING').":</h1><div style='background-color: #FDD; width: 400px; padding: 5px;'>";
            foreach($equipmentObject->warnings as $warning){
                $warnings .= "<span style='color: red; font-weight: bold; width: 400px;'>- ".$translator->_($warning)."</span><BR>";
            }
            $warnings .= "</div><br>";
        }
        return $warnings;
    }
    
    /**
     * Return value wrapped in color [red is negative, green if positive or 0]
     * @param float $value
     * @param string $displayValue
     * @return string
     */
    public static function colorValue($value, $displayValue){
        if (is_null($value)) return null;
        if ($value>0){
            return "<span style='color: red'>".$displayValue."</span>";
        }
        return "<span style='color: green'>".$displayValue."</span>";
    }
    
    /**
     * Return <table> cells highlight difference in values
     * @param float $valueA
     * @param float $valueB
     * @param string $displayType
     * @return string
     */
    public static function highlightDifference($valueA, $valueB, $displayType = 'Cost'){
        $mS = Steam_MeasurementSystem::getInstance();
        
        $difference = $valueB - $valueA;
        $diffPercent = NULL;
        if ($valueA<>0) $diffPercent = $difference / $valueA * 100;
        
        
        if ($displayType == 'Cost'){
            $displayValue = number_format($difference);
        }else{
            $displayFunction = 'display'.$displayType;
            $displayValue = $mS->$displayFunction($difference);
        }
        
        $result = '
            <td class="c">'.self::colorValue($difference,$displayValue).'</td>
            <td class="c">'.self::colorValue($diffPercent,number_format($diffPercent,1).'%').'</td>';
        
        return $result;  
    }
    
     /**
     * List of Steam Turbine Codes
     * @return array
     */    
    public static function steamTurbineCodes(){
        return array(
            'HpLp',
            'HpMp',
            'MpLp',
            'Cond',
            );
    }
}