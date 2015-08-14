<?php
/**
 * Tests for Steam Object
 *
 * @package    Tests
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

require_once dirname(__FILE__) . '/../../../library/Steam/Object.php';

/**
 * Test class for Steam_Object.
 * 
 * @package    Tests
 */
class Steam_ObjectTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Steam_Object
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Steam_Object;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function testSteamObject() {
        //Temperature
        $tmp = new Steam_Object(array(
            'pressure' => 3, 
            'temperature' => 300
            ));
        $this->assertEquals(1, $tmp->region, 'Region');
        $this->assertEquals(.115331273e3, $tmp->specificEnthalpy, 'specificEnthalpy', 1e-7);
        $this->assertEquals(.100215168e-2, $tmp->specificVolume, 'specificVolume', 1e-12);
        $this->assertEquals(.392294792, $tmp->specificEntropy, 'specificEntropy', 1e-9);

        //Specific Enthalpy
        $tmp = new Steam_Object(array(
            'pressure' => 3, 
            'specificEnthalpy' => .115331273e3
            ));
        $this->assertEquals(1, $tmp->region, 'Region');
        $this->assertEquals(300, $tmp->temperature, 'temperature', 1e-7);
        $this->assertEquals(.100215168e-2, $tmp->specificVolume, 'specificVolume', 1e-12);
        $this->assertEquals(.392294792, $tmp->specificEntropy, 'specificEntropy', 1e-9);
        
        //Specific Entropy
        $tmp = new Steam_Object(array(
            'pressure' => 3, 
            'specificEntropy' => .392294792
            ));
        $this->assertEquals(1, $tmp->region, 'Region');
        $this->assertEquals(300, $tmp->temperature, 'temperature', 1e-7);
        $this->assertEquals(.100215168e-2, $tmp->specificVolume, 'specificVolume', 1e-12);
        $this->assertEquals(.115331273e3, $tmp->specificEnthalpy, 'specificEnthalpy', 1e-7);
        
        
        //Quality
        $tmp = new Steam_Object(array(
            'pressure' => 0.353658941e-2, 
            'quality' => 0
            ));        
        $this->assertEquals('1&2', $tmp->region, 'Region');
        $this->assertEquals(300, $tmp->temperature, 'temperature', 1e-7);
        $tmp = new Steam_Object(array(
            'pressure' => 0.353658941e-2, 
            'quality' => 1
            ));        
        $this->assertEquals('1&2', $tmp->region, 'Region');
        $this->assertEquals(300, $tmp->temperature, 'temperature', 1e-7);
        
        
        
    }

    /**
     * @covers Steam_Object::setMassFlow
     */
    public function testSetMassFlow() {
        $tmp = new Steam_Object(array(
            'pressure' => 0.353658941e-2, 
            'quality' => 1
            ));  
        $tmp->setMassFlow(100);
        $this->assertEquals(100, $tmp->massFlow, 'Mass Flow');
    }

}

?>
