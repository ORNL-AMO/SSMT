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
 * Displays loaded steam properties in a table 
 * @package    Steam
 */
class Steam_ObjectDisplay {

    /**
     * Array of all loaded Steam_Objects
     * @var array()
     */
    public $steamObjects = array();
    /**
     * Array of labels associated with $steamObjects
     * @var array()
     */
    public $steamObjectLabels = array();
    
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
     * Initiate Steam_ObjectDisplay and load any passed Steam_Objects
     * @param Steam_Object|array() $steamObject
     * @param string $label
     */
    function __construct($steamObject, $label = null) {
        $this->mS = new Steam_MeasurementSystem();
        $this->translator = Zend_Registry::get('Zend_Translate');
            if (is_array($steamObject)){
                foreach($steamObject as $values){
                    if ($values instanceof Steam_Object){
                        $this->addSteamObject($values);
                    }else{
                        $this->addSteamObject($values[0], $values[1]);
                    }
                }
            }elseif ($steamObject instanceof Steam_Object){
                $this->addSteamObject($steamObject, $label);
            }
    }

    /**
     * Add Steam_Object and optional $label
     * @param Steam_Object $steamObject
     * @param type $label
     * @throws Exception If $steamObject not instance of Steam_Object
     */
    function addSteamObject($steamObject, $label = null){
        if (!$steamObject instanceof Steam_Object) throw new Exception('$steamObject not a Steam Object');        
        
        $this->steamObjects[] = $steamObject;
        $this->steamObjectLabels[] = $label;
    }

    /**
     * Generates and Returns HTML Steam Object Table with Vertical Layout
     * @param string $tableName 
     * @param boolean $displayFlows If true flows displayed     
     * @return string Steam Object Table
     */
    function displaySteamObjectTable($tableName = Null, $displayFlows = false ){
        $mS = $this->mS;
        
        $display = "
        <table class='data' style=' margin-right: auto;'>
            <tr>
                <th style='font-size: 1.2em;'>{$tableName}</th>";
                foreach($this->steamObjectLabels as $label){
                    $display .= "<th>".$this->translator->_($label)."</th>";
                }
            $display .= "
                <th style='font-style: italic; color: grey;'>{$this->translator->_('Units')}</th>
            </tr>
            <tr>
                <th>{$this->translator->_('Pressure')}</th>";
                foreach($this->steamObjects as $values){
                    $display .= "<td>".$mS->displayPressure($values->pressure)."</td>";
                } 
            $display .= "
                <td>{$mS->label('pressure')}</td>
            </tr>
            <tr>
                <th>{$this->translator->_('Temperature')}</th>";
                foreach($this->steamObjects as $values){
                    $display .= "<td>".$mS->displayTemperature($values->temperature)."</td>";
                }
            $display .= "
                <td>{$mS->label('temperature')}</td>
            </tr>
            <tr>
                <th>{$this->translator->_('Specific Enthalpy')}</th>";
                foreach($this->steamObjects as $values){
                    $display .= "<td>".$mS->displaySpecificEnthalpy($values->specificEnthalpy)."</td>";
                }
            $display .= "
                <td>{$mS->label('specificEnthalpy')}</td>
            </tr>
            <tr>
                <th>{$this->translator->_('Specific Entropy')}</th>";
                foreach($this->steamObjects as $values){
                    $display .= "<td>".$mS->displaySpecificEntropy($values->specificEntropy)."</td>";
                }
            $display .= "
                <td>{$mS->label('specificEntropy')}</td>
            </tr>
            <tr>
                <th>{$this->translator->_('Phase / Quality')}</th>";
                foreach($this->steamObjects as $values){
                    if (!is_null($values->quality)){
                        $display .= "<td>".number_format($values->quality,2)."</td>";
                    }else{
                        $display .= "<td>".$this->translator->_($values->phase)."</td>";
                    }
                } 
            $display .= "<td></td></tr>
            <tr>
                <th>{$this->translator->_('Specific Volume')}</th>";
                foreach($this->steamObjects as $values){
                    $display .= "<td>".$mS->displaySpecificVolume($values->specificVolume)."</td>";
                }
            $display .= "
                <td>{$mS->label('specificVolume')}</td>
            </tr>";
            
            //Display flows if set
            if ($displayFlows){
                $display .= "<tr>
                    <th>{$this->translator->_('Mass Flow')} </th>";
                    foreach($this->steamObjects as $values) $display .= "<td>".$mS->displayMassflow($values->massFlow)."</td>";
                $display .= "        
                    <td>{$mS->label('massflow')}</td>    
                </tr>
                <tr>
                    <th>{$this->translator->_('Energy Flow')}</th>";
                    foreach($this->steamObjects as $values) $display .= "<td>".$mS->displayEnergyflow($values->energyFlow)."</td>";
                    $display .= "        
                    <td>{$mS->label('energyflow')}</td>    
                </tr>";
            }
        $display .= "</table>";
        
        return $display;
    }

    /**
     * Generates and Returns HTML Steam Object Table with Horizontal/Flat Layout
     * @return string Steam Object Table
     */
    function displaySteamObjectTableFlat(){
        $mS = $this->mS;
        
        $display = "
        <table class='data' style=' margin-right: auto;'>
            <tr>
                <th></th>
                <th>{$this->translator->_('Pressure')}</th>
                <th>{$this->translator->_('Temperature')}</th>
                <th>{$this->translator->_('Specific Enthalpy')}</th>
                <th>{$this->translator->_('Specific Entropy')}</th>
                <th>{$this->translator->_('Phase / Quality')}</th>
                <th>{$this->translator->_('Specific Volume')}</th>
            </tr>
            <tr>
                <th></th>
                <td style='font-style: italic; color: grey; text-align: center;'>{$mS->label('pressure')}</td>
                <td style='font-style: italic; color: grey; text-align: center;'>{$mS->label('temperature')}</td>
                <td style='font-style: italic; color: grey; text-align: center;'>{$mS->label('specificEnthalpy')}</td>
                <td style='font-style: italic; color: grey; text-align: center;'>{$mS->label('specificEntropy')}</td>
                <td style='font-style: italic; color: grey; text-align: center;'></td>
                <td style='font-style: italic; color: grey; text-align: center;'>{$mS->label('specificVolume')}</td>                
            </tr>
            ";
            foreach($this->steamObjects as $key => $values){                
                $display .= "<tr>
                    <th>{$this->steamObjectLabels[$key]}</th>
                        <td style='text-align: center;'>".$mS->displayPressure($values->pressure)."</td>
                        <td style='text-align: center;'>".$mS->displayTemperature($values->temperature)."</td>
                        <td style='text-align: center;'>".$mS->displaySpecificEnthalpy($values->specificEnthalpy)."</td>
                        <td style='text-align: center;'>".$mS->displaySpecificEntropy($values->specificEntropy)."</td>";
                        if (!is_null($values->quality)){
                            $display .= "<td style='text-align: center;'>".number_format($values->quality,2)."</td>";
                        }else{
                            $display .= "<td style='text-align: center;'>".$this->translator->_($values->phase)."</td>";
                        }
                        $display .= "<td style='text-align: center;'>".$mS->displaySpecificVolume($values->specificVolume)."</td>
                    </tr>";                
            }       
        $display .= "</table>";         
        
        return $display;
    }
}
