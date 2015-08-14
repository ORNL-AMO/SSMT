<?php
/**
 * Steam Mesaurement System
 * 
 * Contains all units and associated conversions
 *
 * @package    Steam
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 * 
 */

/**
 * Contains all units and associated conversions
 * 
 * @category   Steam
 * @package    Steam
 */
class Steam_MeasurementSystem{

    /**
     * Returns set of units or all sets
     * @param string $type
     * @return array
     */
    public static function defaultUnits($type = false){
        $defaultUnits = array(
            'Imperial' => array(
                'pressure' => 'psig', 
                'vacuum' => 'psia', 
                'temperature' => 'F', 
                'specificEnthalpy' => 'btu/lbm', 
                'specificEntropy' => 'btu/lbm/R', 
                'massflow' => 'klb/hr', 
                'energyflow' => 'MMBtu/hr', 
                'energy' => 'MMBtu',
                'power' => 'kW',
                'electricity' => 'kWh',
                'volume' => 'gal',   
                'volumeflow' => 'gpm',    
                'density' => 'lb/ft3',
                'specificVolume' => 'ft3/lb',
            ),           
            'SI' => array(
                'pressure' => 'barg', 
                'vacuum' => 'bara', 
                'temperature' => 'C', 
                'specificEnthalpy' => 'kJ/kg', 
                'specificEntropy' => 'kJ/kg/K', 
                'massflow' => 't/hr', 
                'energyflow' => 'kW',
                'energy' => 'Nm3',
                'power' => 'kW',
                'electricity' => 'kWh',
                'volume' => 'l',   
                'volumeflow' => 'lpm',     
                'density' => 'g/m3',
                'specificVolume' => 'm3/kg',
            ),           
            'Chinese' => array(
                'pressure' => 'barg', 
                'vacuum' => 'bara', 
                'temperature' => 'C', 
                'specificEnthalpy' => 'kJ/kg', 
                'specificEntropy' => 'kJ/kg/K', 
                'massflow' => 't/hr', 
                'energyflow' => 'TCE/hr',
                'energy' => 'TCE',
                'power' => 'kW',
                'electricity' => 'kWh',
                'volume' => 'l',   
                'volumeflow' => 'lpm',     
                'density' => 'g/m3',
                'specificVolume' => 'm3/kg',
            ),                      
            'testSystem' => array(
                'pressure' => 'test:pressure', 
                'vacuum' => 'test:vacuum', 
                'temperature' => 'test:temperature', 
                'specificEnthalpy' => 'test:specificEnthalpy', 
                'specificEntropy' => 'test:specificEntropy', 
                'massflow' => 'test:massflow', 
                'energyflow' => 'test:energyflow',
                'energy' => 'test:energy',
                'power' => 'test:power',
                'electricity' => 'test:elec',
                'volume' => 'test:volume',  
                'volumeflow' => 'test:volumeflow',         
                'density' => 'test:density',
                'specificVolume' => 'm3/kg',
            )
        );
        unset($defaultUnits['testSystem']);
        if ($type){
            return $defaultUnits[$type];
        }
        return $defaultUnits;
    }
    
    /**
     * Instance of Class
     * @var Steam_MeasurementSystem 
     */
    private static $instance;
    
    /**
     * Returns Instance of Class (initalizing if necessary)
     * @return Steam_MeasurementSystem
     */
    public static function getInstance(){        
        if (!self::$instance){
            self::$instance = new Steam_MeasurementSystem();
        }
        return self::$instance;
    }
    
    /**
     * List of active conversions
     * @var array
     */
    public $conversions = array();
    
    /**
     * Set up Measurement Systems
     */
    public function __construct() {
        $this->preferences = new Zend_Session_Namespace('preferences');

        /*
         * key => shortCode
         * 0 => Full Name
         * 1 => Multiplier
         * 2 => Adjustment
         * 3 => Label
         * 4 => Standard Decimal Points
         */
        $this->masterConversionList  = array(
            'temperature' => array(
                'Temperature',
                array(
                    'C' => array('Celsius °C',      1,      273.15, '&#xb0;C',  1, '°C'),
                    'K' => array('Kelvin K',        1,      0,      'K',        1, 'K'),
                    'F' => array('Fahrenheit °F',   5/9,    459.67, '&#xb0;F',  1, '°F'),
                    'R' => array('Rankine °R',      5/9,    0,      '&#xb0;R',  1, '°R'),
                    ),
                ),           
            'temperaturediff' => array(
                'Temperature Difference',
                array(
                    'C' => array('Celsius °C',      1,      0,      '&#xb0;C',  1, '°C'),
                    'K' => array('Kelvin K',        1,      0,      'K',        1, 'K'),
                    'F' => array('Fahrenheit °F',   5/9,    0,      '&#xb0;F',  1, '°F'),
                    'R' => array('Rankine °R',      5/9,    0,      '&#xb0;R',  1, '°R'),
                    ),
                ),
            
          'pressure' => array(
                'Pressure',
                array(
                    'MPa' => array( 'MPa (absolute)',   1,              0,      'MPa',      4),
                    'kPa' => array( 'kPa (absolute)',   1/1000,         0,      'kPa',      0),
                    'kPa[g]' => array( 'kPa (gauge)',   1/1000,         101.325,'kPa[g]',  0),
                    'bara' => array('bar (absolute)',   1/10,           0,      'bara',     2),
                    'barg' => array('bar (gauge)',      1/10,           1.01325,'barg',     2),
                    'psia' => array('psi (absolute)',   .006894757293,  0,      'psia',     1),    
                    'psig' => array('psi (gauge)',      .006894757293,  14.696, 'psig',     1),
                    ),
                ), 
            
          'vacuum' => array(
                'Vacuum Pressure',
                array(
                    'MPa' => array( 'MPa (absolute)',   1,              0,      'MPa',      4),
                    'kPa' => array( 'kPa (absolute)',   1/1000,         0,      'kPa',      0),
                    'kPa[g]' => array( 'kPa (gauge)',   1/1000,         101.325,'kPa[g]',   0),
                    'bara' => array('bar (absolute)',   1/10,           0,      'bara',     2),
                    'barg' => array('bar (gauge)',      1/10,           1.01325,'barg',     2),
                    'psia' => array('psi (absolute)',   .006894757293,  0,      'psia',     1),    
                    'psig' => array('psi (gauge)',      .006894757293,  14.696, 'psig',     1),
                    ),
                ),                          
            'specificEnthalpy' => array(            
                'Specific Enthalpy',
                array(
                    'kJ/kg' => array('kJ/kg',       1,          0,  'kJ/kg',    1),
                    'btu/lbm' => array('btu/lbm',   2.325997,   0,  'btu/lbm',  1),       
                    ),
                ),                         
            'specificEntropy' => array(            
                'Specific Entropy',
                array(
                'kJ/kg/K' => array('kJ/kg/K',       1,      0,  'kJ/kg/K',  3),
                'btu/lbm/R' => array('btu/lbm/R',   4.1868, 0,  'btu/lbm/R',    3),
                    ),
                ),  
            'specificVolume' => array(            
                'Specific Volume',
                array(      
                    'm3/kg' => array('m³/kg',   1,              0,  'm&#179;/kg',   3),        
                    'm3/g' => array('m³/g',     1000,           0,  'm&#179;/g',    1),         
                    'ft3/lb' => array('ft³/lb', 1/16.01846353,  0,  'ft&#179;/lb',  3),  
                    ),
                ),   
            'massflow' => array(            
                'Mass Flow',
                array(      
                    'kg/hr' => array('kg/hr',   1,          0,          'kg/hr',    0),        
                    't/hr' => array('t/hr',     1000,       0,          't/hr', 2),   
                    'klb/hr' => array('klb/hr', 1/2.2046226213*1000, 0, 'klb/hr',   1),
                    ),
                ), 
            'density' => array(            
                'Density',
                array(       
                    'kg/m3' => array('kg/m³',   1,      0,  'kg/m&#179;',   1),        
                    'g/m3' => array('g/m³',     1/1000, 0,  'g/m&#179;',    0),      
                    'lb/ft3' => array('lb/ft³', 16.01846353,  0,  'lb/ft&#179;',  3),  
                    ),
                ), 
            'energyflow' => array(            
                'Energy Flow',
                array(              
                    'MJ/hr' => array(   'MJ/hr',    1,          0,  'MJ/hr',    0),         
                    'GJ/hr' => array(   'GJ/hr',    1000,       0,  'GJ/hr',    1),
                    'MMBtu/hr' => array('MMBtu/hr', 1055.056,   0,  'MMBtu/hr', 1),
                    'kW' => array(      'kW',       3600/1000,  0,  'kW',       1),     
                    'TCE/hr' => array( 'TCE/hr',      29307.6,     0,  'TCE/hr', 3),
                    ),
                ),  
            'energy' => array(            
                'Energy',
                array(              
                    'MJ' => array(  'MJ',       1,          0,  'MJ',       0),         
                    'GJ' => array(  'GJ',       1000,       0,  'GJ',       1),
                    'MMBtu' => array('MMBtu',   1055.056,   0,  'MMBtu',    1),            
                    'Nm3' => array( 'Nm³',      40.144,     0,  'Nm&#179;', 1),        
                    'TCE' => array( 'TCE',      29307.6,     0,  'TCE', 3),
                    ),
                ),   
            'power' => array(            
                'Power',
                array(              
                    'MJ/hr' => array('MJ/hr',   1,          0,    'MJ/hr',  1),   
                    'kJ/s' => array('kJ/s',     3600/1000,  0,  'kJ/s',     2),            
                    'kW' => array('kW',         3600/1000,  0,  'kW',       1),
                    'kJ/hr' => array('kJ/hr',   1000,       0,  'kJ/hr',    0), 
                    ),
                ),    
            'electricity' => array(            
                'Electricity',
                array(                    
                    'MJh' => array('MJh',   1,          0,  'MJh'),               
                    'kWh' => array('kWh',   3600/1000,  0,  'kWh'), 
                    ),
                ),    
            'volume' => array(            
                'Volume',
                array(                     
                    'l' => array(   'l',    1,          0,  'l',        0),        
                    'm3' => array(  'm³',   1000,       0,  'm&#179;',   3),  
                    'gal' => array( 'gal',  3.78541178, 0,  'gal',      0),
                    ),
                ),    
            'volumeflow' => array(            
                'Volume Flow',
                array(             
                    'lph' => array('lpm',   1,          0,  'lpm',      0),             
                    'lpm' => array('lpm',   1*60,          0,  'lpm',      0),        
                    'm3pm' => array('m³pm', 1000*60,       0,  'm&#179;pm',3),  
                    'gpm' => array('gpm',   3.78541178*60, 0,  'gpm',      1),
                    ),
                ),     
        );   
        
        $this->unitTypes = array();
        $this->conversions = array();
        foreach($this->masterConversionList as $key => $value) {
            $this->unitTypes[$key] = $value[0];
            $this->conversions[$key] = $value[1];
        }     
        $this->allUnitTypes = $this->unitTypes;
        unset($this->unitTypes['temperaturediff']);
               
        foreach($this->conversions as $type => $values){
            foreach($values as $key => $value){
                $this->labels[$type][$key] = $value[0];
                $this->units[$type][$key] = $value[3];
            }
        }
        $this->selected = array(
            'temperature' => 'C',
            'pressure' => 'psig',
            'vacuum' => 'psia',
            'specificEnthalpy' => 'btu/lbm',
            'specificEntropy' => 'kJ/kg/K',
            'massflow' => 'klb/hr',
            'energyflow' => 'kJ/hr',
            'energy' => 'GJ',
            'temperaturediff' => 'C',
            'power' => 'kW',
            'electricity' => 'kWh',
            'volume' => 'l',
            'volumeflow' => 'lph',
            'density' => 'kg/m3',
            'specificVolume' => 'm3/kg',
        );
        
        foreach($this->conversions as $type => $values) {
            if ( isset($this->preferences->$type) ) $this->selected[$type] = $this->preferences->$type;
        }
        if ( isset($this->preferences->temperature) ) $this->selected['temperaturediff'] = $this->preferences->temperature;

        //Set Standard Conversion Units
        $this->standard = array(
            'temperature' => 'K',
            'pressure' => 'MPa',
            'vacuum' => 'MPa',
            'specificEnthalpy' => 'kJ/kg',
            'specificEntropy' => 'kJ/kg/R',
            'massflow' => 'kg/hr',
            'energyflow' => 'kJ/hr',
            'energy' => 'kJ',
            'temperaturediff' => 'K',
            'power' => 'MJ/hr',
            'electricity' => 'MJh',
            'volume' => 'l',
            'volumeflow' => 'lpm',
            'density' => 'kg/m3',
            'specificVolume' => 'm3/kg',
        );
    }    
    
    /**
     * Converts a number for local units to system standard units
     * @param float $value
     * @param string $type Unit Type
     * @param string $selected Optional Specific Unit
     * @return float Standard Number
     */
    public function standardize($value, $type, $selected = false){        
        $returnUnitCost = false;
        if (substr($type, 0,9)=='unitcost.'){       
            $type = substr($type, 9);
            $returnUnitCost = true;
        }
        if (!$selected and $type<>'quality') $selected = $this->selected[$type];

        if ($type=='quality' or $selected == $this->standard[$type]) return $value;

        $multiply = $this->conversions[ $type ][ $selected ][1];
        $add = $this->conversions[ $type ][ $selected ][2];
        
        if ($returnUnitCost) return $value / $multiply;
        return ( $value + $add ) * $multiply;
    }
    
    /**
     * Converts a number from system standard units to local units
     * @param float $value
     * @param string $type Unit Type
     * @param string $selected Optional Specific Unit
     * @return float Local Number
     */
    public function localize($value, $type, $selected = false){        
        $returnUnitCost = false;
        if (substr($type, 0,9)=='unitcost.'){       
            $type = substr($type, 9);
            $returnUnitCost = true;
        }
        if (!$selected and $type<>'quality') $selected = $this->selected[$type];
        if ($type=='quality' or $selected == $this->standard[$type] or $type=='quality' ) return $value;
        $multiply = $this->conversions[ $type ][ $selected ][1];
        $add = $this->conversions[ $type ][ $selected ][2];
        
        if ($returnUnitCost) return $value * $multiply;
        return ( $value / $multiply ) - $add;        
    }
    
    /**
     * Converts a number from '$from' units to '$to' units
     * @param float $value
     * @param string $type Unit Type
     * @param string $from Specific Unit
     * @param string $to Specific Unit
     * @return float Local Number
     */
    public function convert($value, $type, $from, $to){
        if ($type=='quality' or $from == $to) return $value;               
        return $this->localize($this->standardize($value, $type, $from), $type, $to);
    }
    
    /**
     * Returns list of all unit types
     * @return array()
     */
    static public function unitTypes(){
        $tmp = Steam_MeasurementSystem::getInstance();
        return $tmp->unitTypes;
    }
    
    /**
     * Returns a formated label for the selected unit
     * @param string $unit Unit Type
     * @return string Formatted Label
     */
    public function label($unit){
        if ($unit == '%' or $unit=='hrs' or $unit=='tons') return "<span style=\"font-style: italic; color: grey;\">{$unit}</span>";
        return "<span style=\"font-style: italic; color: grey;\">".$this->conversions[$unit][$this->selected[$unit]][3]."</span>";
    }
    
    /**
     * Returns a small formatted label for the selected unit
     * @param string $unit Unit Type
     * @return string Formatted Label
     */
    public function miniLabel($unit){
        if ($unit == '%') return "<span style=\"font-size: .75em;\">%</span>";
        return "<span style=\"font-size: .75em;\">".$this->conversions[$unit][$this->selected[$unit]][3]."</span>";
    }
    
    /**
     * Returns a formatted currently selected unit name
     * @param string $unit
     * @return string
     */
    public function unitName($unit){
        $translator = Zend_Registry::get('Zend_Translate');    
        if ($unit == '%') return "<span style=\"font-style: italic; color: grey;\">".$translator->_('Percent')."</span>";
        if ($unit == 'hrs') return "<span style=\"font-style: italic; color: grey;\">".$translator->_('Hours')."</span>";
        return "<span style=\"font-style: italic; color: grey;\">".$translator->_($this->conversions[$unit][$this->selected[$unit]][0])."</span>";
    }
    
    /**
     * Returns a unit cost adjusted by selected unit
     * @param float $value
     * @param string $unit
     * @return float
     */
    public function unitCostAdjustment($value, $unit){    
        return $value / $this->localize(1, $unit);  
    }
    
    /**
     * Returns values with optional formatting and labeling
     * @param string $name function name
     * @param array $arguments passed arguements
     * @return string
     * @throws Exception
     */
    public function __call($name, $arguments) {        
        $labeled = false;
        $miniLabeled = false;
        $unit = null;
        $displayRaw = false;
        if (substr($name, 0,7)=='display') $displayRaw = 'display';
        if (substr($name, 0,3)=='raw') $displayRaw = 'raw';
        if ($displayRaw){
            $displayType = preg_replace('/^(display|raw)/', '', $name);

            preg_match_all('/[A-Z][^A-Z]*/',$displayType,$results);

            $result = $results[0];
            if ($result[0]=='Specific'){
                $result[0] = $result[0].$result[1];
                if (isset($result[2]) )$result[1] = $result[2];
            }
            foreach($this->allUnitTypes as $type => $fullname) if ( strcasecmp($type, $result[0])==0 ) $unit = $type;                
            if ( isset($result[1]) and $result[1]=='Labeled' ) $labeled = true;
            if ( isset($result[1]) and $result[1]=='Minl' ) $miniLabeled = true;
             
            $suffix='';
            $formatType = 'number_format';
            if ($displayRaw == 'raw') $formatType = 'round';
            if (!is_null($unit)){
                if ($labeled) $suffix = " ".$this->label($unit);
                if ($miniLabeled) $suffix = " ".$this->miniLabel($unit);
                $value = $this->localize($arguments[0], $unit);
                if (round($value, $this->conversions[$unit][$this->selected[$unit]][4])==0) {
                       $value = 0;
                       if ($unit=='massflow' or $unit=='energyflow') return '-'.$suffix;
                }
                return $formatType($value, $this->conversions[$unit][$this->selected[$unit]][4]).$suffix;                                     
            }
            if ($result[0] == 'Quality' ){                                
                return $formatType($arguments[0],2);
            }
        }
        
        if (substr($name, 0,3)=='min' or substr($name, 0,3)=='max' or substr($name, 0,4)=='crit' or substr($name, 0,4)=='cond'){
            $direction = 'min';
            $rounder = 'ceil_dec';
            if (substr($name, 0,3)=='max') {
                $direction = 'max';
                $rounder = 'floor_dec';
            }
            
            if (substr($name, 0,4)=='crit') {
                $direction = 'crit';
                $rounder = 'floor_dec';
            }
            
            if (substr($name, 0,4)=='cond') {
                $direction = 'cond';
                $rounder = 'floor_dec';
            }
            $displayType = preg_replace('/^(max|min)/', '', $name);         
            
            preg_match_all('/[A-Z][^A-Z]*/',$displayType,$results);
            $result = $results[0];
            
            if ( isset($result[0]) ){
                $unit = strtolower($result[0]);
                if ($result[0]=='Specific'){
                    $result[0] = $result[0].$result[1];
                    $unit = $unit.$result[1];
                    if (isset($result[2]) )$result[1] = $result[2];
                }

                if ($result[0]=='Pressure' and $direction=='min') $value = PRESSURE_MINIMUM;
                if ($result[0]=='Pressure' and $direction=='max') $value = PRESSURE_MAXIMUM;
                if ($result[0]=='Pressure' and $direction=='crit') $value = PRESSURE_CRITICAL_POINT;
                if ($result[0]=='Vacuum' and $direction=='min') $value = PRESSURE_MINIMUM;
                if ($result[0]=='Vacuum' and $direction=='cond') $value = .101325;
                    

                if ($result[0]=='Temperature' and $direction=='min') $value = TEMPERATURE_MINIMUM;
                if ($result[0]=='Temperature' and $direction=='max') $value = TEMPERATURE_MAXIMUM;
                if ($result[0]=='Temperature' and $direction=='crit') $value = TEMPERATURE_CRITICAL_POINT;

                if ($result[0]=='SpecificEnthalpy' and $direction=='min') $value = SPECIFIC_ENTHALPY_MINIMUM;
                if ($result[0]=='SpecificEnthalpy' and $direction=='max') $value = SPECIFIC_ENTHALPY_MAXIMUM;

                if ($result[0]=='SpecificEntropy' and $direction=='min') $value = SPECIFIC_ENTROPY_MINIMUM;
                if ($result[0]=='SpecificEntropy' and $direction=='max') $value = SPECIFIC_ENTROPY_MAXIMUM;
                
                if ($result[0]=='Massflow' and $direction=='min') $value = MASSFLOW_MINIMUM;
                if ($result[0]=='Massflow' and $direction=='max') $value = MASSFLOW_MAXIMUM;


                if (isset($result[1]) and $result[1]=='Labeled'){       
                    $suffix = " ".$this->label($unit);
                    return number_format($this->$rounder($this->localize($value, $unit),
                            $this->conversions[$unit][$this->selected[$unit]][4]),$this->conversions[$unit][$this->selected[$unit]][4]).$suffix;  
                }else{
                    return $this->$rounder($this->localize($value, $unit),
                            $this->conversions[$unit][$this->selected[$unit]][4]);
                }
            }
        }
            
        throw new Exception("Uncaught MeasurementSystem Function name: ".$name);
    }
     
    /**
     * Returns the floor of $number to the requested $precision
     * @param float $number
     * @param int $precision
     * @param string $separator
     * @return float
     */
    public function floor_dec($number,$precision,$separator='.')
    { 
        return floor( $number * pow(10, $precision) )/ pow(10, $precision);
    }
    
    /**
     * Returns the ceil of $number to the requested $precision
     * @param float $number
     * @param int $precision
     * @param string $separator
     * @return float
     */
    public function ceil_dec($number,$precision,$separator='.')
    { 
        return ceil( $number * pow(10, $precision) )/ pow(10, $precision);
    }
    
    /**
     * Returns excel number format string for selected unit type
     * @param string $unitType
     * @return string Number Format String
     */
    public function excelFormat($unitType = false){        
        $format = '#,##0';
        if ($unitType) {
            $points = $this->conversions[$unitType][$this->selected[$unitType]][4];
            if ($points>0){
                $format .= '.';
                for ($x=1;$x<=$points;$x++) $format .='0';
            }
        }
        return $format;
    }
    
    /**
     * Returns unit label for Excel Cell
     * @param string $unitType
     * @return string Unit Label
     */
    public function excelUnits($unitType){
        if ($unitType == 'temperature') return $this->conversions[$unitType][$this->selected[$unitType]][5];
        if ($unitType == 'specificVolume') return $this->conversions[$unitType][$this->selected[$unitType]][0];
        if ($unitType == 'density') return $this->conversions[$unitType][$this->selected[$unitType]][0];
        if ($unitType == 'volume' or $unitType == 'volumeflow') return $this->conversions[$unitType][$this->selected[$unitType]][0];
        return $this->conversions[$unitType][$this->selected[$unitType]][3];
    }
}