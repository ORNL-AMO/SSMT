<?php
/**
 * Tests for Steam IAPWS Core
 *
 * @package    Tests
 * @subpackage Tests_IAPWS
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

require_once dirname(__FILE__) . '/../../../../library/Steam/IAPWS/Core.php';

/**
 * Test class for Steam_IAPWS_Core.
 * 
 * @package    Tests
 * @subpackage    Tests_IAPWS
 */
class Steam_IAPWS_CoreTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Steam_IAPWS_Core
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Steam_IAPWS_Core;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function testBoundaryByTemperatureRegion3to2() {
        $tmp = $this->object->boundaryByTemperatureRegion3to2(0.623150e3);       
        $this->assertEquals( 0.165291643e2, $tmp, 'Pressure', 1e-7 );
    }
    public function testBoundaryByPressureRegion3to2() {
        $tmp = $this->object->boundaryByPressureRegion3to2(0.165291643e2);       
        $this->assertEquals( 0.623150e3, $tmp, 'Temperature', 1e-6 );
    }


    public function testRegion1() {
        $tmp = $this->object->region1(3, 300);       
        $this->assertEquals( .100215168e-2, $tmp['specificVolume'], 'specificVolume', 1e-12 );
        $this->assertEquals( .115331273e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-7);
        $this->assertEquals( .392294792, $tmp['specificEntropy'], 'specificEntropy', 1e-9);
        
        $tmp = $this->object->region1(80, 300);       
        $this->assertEquals( .971180894e-3, $tmp['specificVolume'], 'specificVolume', 1e-13 );
        $this->assertEquals( .184142828e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6);
        $this->assertEquals( .368563852, $tmp['specificEntropy'], 'specificEntropy', 1e-9);
        
        $tmp = $this->object->region1(3, 500);       
        $this->assertEquals( .120241800e-2, $tmp['specificVolume'], 'specificVolume', 1e-11 );
        $this->assertEquals( .975542239e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-7);
        $this->assertEquals( .258041912e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);    
    }

    public function testBackwardPHregion1() {
        $this->assertEquals( 391.798509, $this->object->backwardPHregion1(3, 500), '', 1e-6 );
        $this->assertEquals(  378.108626, $this->object->backwardPHregion1(80, 500), '', 1e-6 );
        $this->assertEquals( 611.041229, $this->object->backwardPHregion1(80, 1500), '', 1e-6 );    
    }

    public function testBackwardPSregion1() {
        $this->assertEquals( 0.307842258e3, $this->object->backwardPSregion1(3, .5), '', .000001 );
        $this->assertEquals( 0.309979785e3, $this->object->backwardPSregion1(80, .5), '', .000001 );
        $this->assertEquals( 0.565899909e3, $this->object->backwardPSregion1(80, 3), '', .00001 );    
    }

    public function testRegion2() {
        $tmp = $this->object->region2(.0035, 300);
        $this->assertEquals( .394913866e2, $tmp['specificVolume'], 'specificVolume', 1e-7 );
        $this->assertEquals( .254991145e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6);
        $this->assertEquals( .852238967e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->region2(.0035, 700);       
        $this->assertEquals( .923015898e2, $tmp['specificVolume'], 'specificVolume', 1e-7 );
        $this->assertEquals( .333568375e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
        $this->assertEquals( .101749996e2, $tmp['specificEntropy'], 'specificEntropy', 1e-7);
        
        $tmp = $this->object->region2(30, 700);       
        $this->assertEquals( .542946619e-2, $tmp['specificVolume'], 'specificVolume', 1e-11);
        $this->assertEquals( .263149474e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
        $this->assertEquals( .517540298e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);     
    }

    public function testBackwardPHregion2a() {
        $this->assertEquals( 0.534433241e3, $this->object->backwardPHregion2a(0.001, 3000), '', 1e-6 );
        $this->assertEquals(  575.373370, $this->object->backwardPHregion2a(3, 3000), '', 1e-6 );
        $this->assertEquals( 1010.77577, $this->object->backwardPHregion2a(3, 4000), '', 1e-5 );     
    }

    public function testBackwardPHregion2b() {
        $this->assertEquals( .801299102e3, $this->object->backwardPHregion2b(5, 3500), '', 1e-6 );
        $this->assertEquals( .101531583e4, $this->object->backwardPHregion2b(5, 4000), '', 1e-5  );
        $this->assertEquals( .875279054e3, $this->object->backwardPHregion2b(25, 3500), '', 1e-6  );
    }

    public function testBackwardPHregion2c() {
        $this->assertEquals( 743.056411, $this->object->backwardPHregion2c(40, 2700), '', 1e-6 );
        $this->assertEquals( 791.137067, $this->object->backwardPHregion2c(60, 2700), '', 1e-6  );
        $this->assertEquals( 882.756860, $this->object->backwardPHregion2c(60, 3200), '', 1e-6  );
    }

    public function testBackwardPSregion2a() {
        $this->assertEquals( 0.399517097e3, $this->object->backwardPSregion2a(0.1, 7.5), '', 1e-6 );
        $this->assertEquals( 0.514127081e3, $this->object->backwardPSregion2a(0.1, 8), '', 1e-6 );
        $this->assertEquals( 0.103984917e4, $this->object->backwardPSregion2a(2.5, 8), '', 1e-5 );
    }

    public function testBackwardPSregion2b() {
        $this->assertEquals( 0.600484040e3, $this->object->backwardPSregion2b(8, 6), '', 1e-6 );
        $this->assertEquals( 0.106495556e4, $this->object->backwardPSregion2b(8, 7.5), '', 1e-5 );
        $this->assertEquals( 0.103801126e4, $this->object->backwardPSregion2b(90, 6), '', 1e-5 );
    }

    public function testBackwardPSregion2c() {
        $this->assertEquals( 0.697992849e3, $this->object->backwardPSregion2c(20, 5.75), '', 1e-6 );
        $this->assertEquals( 0.854011484e3, $this->object->backwardPSregion2c(80, 5.25), '', 1e-6 );
        $this->assertEquals( 0.949017998e3, $this->object->backwardPSregion2c(80, 5.75), '', 1e-6 );
    }
    
    public function testRegion3Density() {
        $tmp = $this->object->region3Density(500, 650);       
        $this->assertEquals( .255837018e2, $tmp['pressure'], 'pressure', 1e-7 );
        $this->assertEquals( .186343019e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-3);
        $this->assertEquals( .405427273e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->region3Density(200, 650);       
        $this->assertEquals( .222930643e2, $tmp['pressure'], 'pressure', 1e-7 );
        $this->assertEquals( .237512401e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-3);
        $this->assertEquals( .485438792e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->region3Density(500, 750);       
        $this->assertEquals( .783095639e2, $tmp['pressure'], 'pressure', 1e-7 );
        $this->assertEquals( .225868845e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-3);
        $this->assertEquals( .446971906e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
    }
    
    public function testRegion4() {
        $this->assertEquals( 0.353658941e-2, $this->object->region4(300), '', 1e-11 );
        $this->assertEquals( 0.263889776e1, $this->object->region4(500), '', 1e-8 );
        $this->assertEquals( 0.123443146e2, $this->object->region4(600), '', 1e-7 );
    }
    
    public function testBackwardRegion4() {
        $this->assertEquals( 0.372755919e3, $this->object->backwardRegion4(.1), '', 1e-6 );
        $this->assertEquals( 0.453035632e3, $this->object->backwardRegion4(1), '', 1e-6 );
        $this->assertEquals( 0.584149488e3, $this->object->backwardRegion4(10), '', 1e-6 );
    }
}

?>
