<?php
/**
 * Tests for Steam IAPWS
 *
 * @package    Tests
 * @subpackage Tests_IAPWS
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

require_once dirname(__FILE__) . '/../../../library/Steam/IAPWS.php';

/**
 * Test class for Steam_IAPWS.
 * 
 * @package    Tests
 * @subpackage    Tests_IAPWS
 */
class Steam_IAPWSTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Steam_IAPWS
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Steam_IAPWS;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    /**
     * Tests waterPropertiesPH and  waterPropertiesPS across the full range of temperatures and pressures
     */
    public function testSteamRange(){
        $pressureMin = Steam_IAPWS::PRESSURE_MIN;
        $pressureMax = Steam_IAPWS::PRESSURE_MAX;              
        $pressureInc = ($pressureMax-$pressureMin)/4;
        $temperatureMin = Steam_IAPWS::TEMPERATURE_MIN;
        $temperatureMax = Steam_IAPWS::TEMPERATURE_MAX;
        $temperatureInc = ($temperatureMax-$temperatureMin)/10;
        
        for( $pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc ){
            for( $temperature = $temperatureMin; $temperature <= $temperatureMax; $temperature += $temperatureInc ){                    
                $propertiesPT = $this->object->waterPropertiesPT($pressure, $temperature);
                $propertiesPH = $this->object->waterPropertiesPH($pressure, $propertiesPT['specificEnthalpy']);     
                $this->assertEquals( $temperature, $propertiesPH['temperature'], "specificEnthalpy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPH['region']}]", 1e-6 );      
                $propertiesPS = $this->object->waterPropertiesPS($pressure, $propertiesPT['specificEntropy']);
                $this->assertEquals( $temperature, $propertiesPS['temperature'], "specificEntropy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPS['region']}]", 1e-6 );       
            }            
        }        
    }
    
    /**
     * Tests waterPropertiesPH and  waterPropertiesPS focused on the Tp to Crit section of region 3
     */
    public function testSteamRangeRegion3Focus(){
        $pressureMin = Steam_IAPWS::PRESSURE_Tp-1;
        $pressureMax = Steam_IAPWS::PRESSURE_CRIT+1;              
        $pressureInc = ($pressureMax-$pressureMin)/4;
        $temperatureMin = Steam_IAPWS::TEMPERATURE_Tp-1;
        $temperatureMax = Steam_IAPWS::TEMPERATURE_CRIT+1;
        $temperatureInc = ($temperatureMax-$temperatureMin)/10;
        
        for( $pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc ){
            for( $temperature = $temperatureMin; $temperature <= $temperatureMax; $temperature += $temperatureInc ){                    
                $propertiesPT = $this->object->waterPropertiesPT($pressure, $temperature);
                $propertiesPH = $this->object->waterPropertiesPH($pressure, $propertiesPT['specificEnthalpy']);     
                $this->assertEquals( $temperature, $propertiesPH['temperature'], "specificEnthalpy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPH['region']}]", 1e-6 );      
                $propertiesPS = $this->object->waterPropertiesPS($pressure, $propertiesPT['specificEntropy']);
                $this->assertEquals( $temperature, $propertiesPS['temperature'], "specificEntropy [Pressure: {$pressure} | Temp: {$temperature} | Region: {$propertiesPS['region']}]", 1e-6 );       
            }            
        }        
    }
    
    /**
     * Tests $propertiesSatP, $propertiesSatT, waterPropertiesPH and waterPropertiesPS from Min to Tp Pressure
     */
    public function testSaturated(){
        $pressureMin = Steam_IAPWS::PRESSURE_MIN;
        $pressureMax = Steam_IAPWS::PRESSURE_Tp;              
        $pressureInc = ($pressureMax-$pressureMin)/50;
        
        for( $pressure = $pressureMin; $pressure <= $pressureMax; $pressure += $pressureInc ){            
            
            $propertiesSatP = $this->object->saturatedPropertiesByPressure($pressure);           
            $propertiesSatT = $this->object->saturatedPropertiesByTemperature($propertiesSatP['temperature']);
            $this->assertEquals( $pressure, $propertiesSatT['pressure'], "[Pressure: {$pressure} | Region: {$propertiesSatP['region']}]", 1e-6 );      
            
            $testSpecificEnthalpy = ($propertiesSatP['liquid']['specificEnthalpy']+$propertiesSatP['gas']['specificEnthalpy'])/2;
            $testSpecificEntropy = ($propertiesSatP['liquid']['specificEntropy']+$propertiesSatP['gas']['specificEntropy'])/2;
            
            $testPropertiesPH = $this->object->waterPropertiesPH($pressure, $testSpecificEnthalpy);
            $this->assertEquals( $pressure, $testPropertiesPH['pressure'], "[Pressure: {$pressure} | Region: {$testPropertiesPH['region']}]", 1e-6 ); 
            $this->assertEquals( $testSpecificEnthalpy, $testPropertiesPH['specificEnthalpy'], "[Pressure: {$pressure} | Region: {$testPropertiesPH['region']}]", 1e-6 ); 
            $this->assertEquals( $testSpecificEntropy, $testPropertiesPH['specificEntropy'], "[Pressure: {$pressure} | Region: {$testPropertiesPH['region']}]", 1e-6 ); 
            $this->assertEquals( .5, $testPropertiesPH['quality'], "[Pressure: {$pressure} | Region: {$testPropertiesPH['region']}]", 1e-6 );    
            
            $testPropertiesPS = $this->object->waterPropertiesPS($pressure, $testSpecificEntropy);
            $this->assertEquals( $pressure, $testPropertiesPS['pressure'], "[Pressure: {$pressure} | Region: {$testPropertiesPS['region']}]", 1e-6 ); 
            $this->assertEquals( $testSpecificEnthalpy, $testPropertiesPS['specificEnthalpy'], "[Pressure: {$pressure} | Region: {$testPropertiesPS['region']}]", 1e-6 ); 
            $this->assertEquals( $testSpecificEntropy, $testPropertiesPS['specificEntropy'], "[Pressure: {$pressure} | Region: {$testPropertiesPS['region']}]", 1e-6 ); 
            $this->assertEquals( .5, $testPropertiesPS['quality'], "[Pressure: {$pressure} | Region: {$testPropertiesPS['region']}]", 1e-6 );                                      
        }        
    }

    /**
     * @covers Steam_IAPWS::waterPropertiesPT
     */
    public function testWaterPropertiesPT() {
        //Region 1
        $tmp = $this->object->waterPropertiesPT(3, 300);       
        $this->assertEquals( .100215168e-2, $tmp['specificVolume'], 'specificVolume', 1e-12 );
        $this->assertEquals( .115331273e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-7);
        $this->assertEquals( .392294792, $tmp['specificEntropy'], 'specificEntropy', 1e-9);
        
        $tmp = $this->object->waterPropertiesPT(80, 300);       
        $this->assertEquals( .971180894e-3, $tmp['specificVolume'], 'specificVolume', 1e-13 );
        $this->assertEquals( .184142828e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6);
        $this->assertEquals( .368563852, $tmp['specificEntropy'], 'specificEntropy', 1e-9);
        
        $tmp = $this->object->waterPropertiesPT(3, 500);       
        $this->assertEquals( .120241800e-2, $tmp['specificVolume'], 'specificVolume', 1e-11 );
        $this->assertEquals( .975542239e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-7);
        $this->assertEquals( .258041912e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8); 
        
        //Region 2
        $tmp = $this->object->waterPropertiesPT(.0035, 300);
        $this->assertEquals( .394913866e2, $tmp['specificVolume'], 'specificVolume', 1e-7 );
        $this->assertEquals( .254991145e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6);
        $this->assertEquals( .852238967e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->waterPropertiesPT(.0035, 700);       
        $this->assertEquals( .923015898e2, $tmp['specificVolume'], 'specificVolume', 1e-7 );
        $this->assertEquals( .333568375e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
        $this->assertEquals( .101749996e2, $tmp['specificEntropy'], 'specificEntropy', 1e-7);
        
        $tmp = $this->object->waterPropertiesPT(30, 700);       
        $this->assertEquals( .542946619e-2, $tmp['specificVolume'], 'specificVolume', 1e-11);
        $this->assertEquals( .263149474e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
        $this->assertEquals( .517540298e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);   
        
        //Region 3
        $tmp = $this->object->waterPropertiesPT(.255837018e2, 650);       
        $this->assertEquals( 1/500, $tmp['specificVolume'], 'specificVolume', 1e-9 );
        $this->assertEquals( .186343019e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6);
        $this->assertEquals( .405427273e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->waterPropertiesPT(.222930643e2, 650);      
        $this->assertEquals( 1/200, $tmp['specificVolume'], 'specificVolume', 1e-9 );
        $this->assertEquals( .237512401e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-4);
        $this->assertEquals( .485438792e1, $tmp['specificEntropy'], 'specificEntropy', 1e-6);
        
        $tmp = $this->object->waterPropertiesPT(.783095639e2, 750);       
        $this->assertEquals( 1/500, $tmp['specificVolume'], 'specificVolume', 1e-9 );
        $this->assertEquals( .225868845e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-4);
        $this->assertEquals( .446971906e1, $tmp['specificEntropy'], 'specificEntropy', 1e-6);
        
    }

    /**
     * @covers Steam_IAPWS::regionSelect
     */
    public function testRegionSelect() {
        //Region 1
        $tmp = $this->object->waterPropertiesPT(3, 300);       
        $this->assertEquals( 1, $tmp['region'], 'Region');
        
        $tmp = $this->object->waterPropertiesPT(80, 300);       
        $this->assertEquals( 1, $tmp['region'], 'Region' );
        
        $tmp = $this->object->waterPropertiesPT(3, 500);       
        $this->assertEquals( 1, $tmp['region'], 'Region');
        
        //Region 2
        $tmp = $this->object->waterPropertiesPT(.0035, 300);
        $this->assertEquals( 2, $tmp['region'], 'Region' );
        
        $tmp = $this->object->waterPropertiesPT(.0035, 700);       
        $this->assertEquals( 2, $tmp['region'], 'Region' );
        
        $tmp = $this->object->waterPropertiesPT(30, 700);       
        $this->assertEquals( 2, $tmp['region'], 'Region' );  
        
        //Region 3
        $tmp = $this->object->waterPropertiesPT(.255837018e2, 650);       
        $this->assertEquals( 3, $tmp['region'], 'Region' );
        
        $tmp = $this->object->waterPropertiesPT(.222930643e2, 650);      
        $this->assertEquals( 3, $tmp['region'], 'Region' );
        
        $tmp = $this->object->waterPropertiesPT(.783095639e2, 750);       
        $this->assertEquals( 3, $tmp['region'], 'Region' );        
    }

    public function testRegion3() {
        $tmp = $this->object->region3Density(500, 650);       
        $results = $this->object->region3($tmp['pressure'],$tmp['temperature']);
        $this->assertEquals( 500, $results['density'] , 'Density', 1e-6 );
        $tmp = $this->object->region3Density(200, 650);       
        $results = $this->object->region3($tmp['pressure'],$tmp['temperature']);
        $this->assertEquals( 200, $results['density'], 'Density', 1e-5 );
        $tmp = $this->object->region3Density(500, 750);       
        $results = $this->object->region3($tmp['pressure'],$tmp['temperature']);
        $this->assertEquals( 500, $results['density'], 'Density', 1e-6 );       
    }

    /**
     * @covers Steam_IAPWS::saturatedPressure
     */
    public function testSaturatedPressure() {
        $this->assertEquals( 0.353658941e-2, $this->object->region4(300), '', 1e-11 );
        $this->assertEquals( 0.263889776e1, $this->object->region4(500), '', 1e-8 );
        $this->assertEquals( 0.123443146e2, $this->object->region4(600), '', 1e-7 );
    }

    /**
     * @covers Steam_IAPWS::saturatedTemperature
     */
    public function testSaturatedTemperature() {
        $this->assertEquals( 0.372755919e3, $this->object->backwardRegion4(.1), '', 1e-6 );
        $this->assertEquals( 0.453035632e3, $this->object->backwardRegion4(1), '', 1e-6 );
        $this->assertEquals( 0.584149488e3, $this->object->backwardRegion4(10), '', 1e-6 );
    }

    /**
     * @covers Steam_IAPWS::waterPropertiesPH
     */
    public function testWaterPropertiesPH() {
        //Region 1
        $tmp = $this->object->waterPropertiesPH(3, .115331273e3);  
        $this->assertEquals( 1, $tmp['region'], 'Region' );        
        $this->assertEquals( 300, $tmp['temperature'], 'temperature', 1e-7);
        $this->assertEquals( .100215168e-2, $tmp['specificVolume'], 'specificVolume', 1e-12 );        
        $this->assertEquals( .392294792, $tmp['specificEntropy'], 'specificEntropy', 1e-9);
        
        $tmp = $this->object->waterPropertiesPH(80, .184142828e3);  
        $this->assertEquals( 1, $tmp['region'], 'Region' );        
        $this->assertEquals( 300, $tmp['temperature'], 'temperature', 1e-7);     
        $this->assertEquals( .971180894e-3, $tmp['specificVolume'], 'specificVolume', 1e-13 );
        $this->assertEquals( .368563852, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->waterPropertiesPH(3, .975542239e3);  
        $this->assertEquals( 1, $tmp['region'], 'Region' );        
        $this->assertEquals( 500, $tmp['temperature'], 'temperature', 1e-7);     
        $this->assertEquals( .120241800e-2, $tmp['specificVolume'], 'specificVolume', 1e-11 );
        $this->assertEquals( .258041912e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8); 
        
        //Region 2
        $tmp = $this->object->waterPropertiesPH(.0035, .254991145e4);
        $this->assertEquals( '2a', $tmp['region'], 'Region' );        
        $this->assertEquals( 300, $tmp['temperature'], 'temperature', 1e-6);    
        $this->assertEquals( .394913866e2, $tmp['specificVolume'], 'specificVolume', 1e-7 );
        $this->assertEquals( .852238967e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->waterPropertiesPH(.0035, .333568375e4);   
        $this->assertEquals( '2a', $tmp['region'], 'Region' );        
        $this->assertEquals( 700, $tmp['temperature'], 'temperature', 1e-5);        
        $this->assertEquals( .923015898e2, $tmp['specificVolume'], 'specificVolume', 1e-6 );
        $this->assertEquals( .101749996e2, $tmp['specificEntropy'], 'specificEntropy', 1e-7);
        
        $tmp = $this->object->waterPropertiesPH(30, .263149474e4);  
        $this->assertEquals( '2c', $tmp['region'], 'Region' );        
        $this->assertEquals( 700, $tmp['temperature'], 'temperature', 1e-6);         
        $this->assertEquals( .542946619e-2, $tmp['specificVolume'], 'specificVolume', 1e-10);
        $this->assertEquals( .517540298e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);   
        
        //Region 3
        $tmp = $this->object->waterPropertiesPH(.255837018e2, .186343019e4); 
        $this->assertEquals( 3, $tmp['region'], 'Region' );        
        $this->assertEquals( 650, $tmp['temperature'], 'temperature', 1e-7);           
        $this->assertEquals( 1/500, $tmp['specificVolume'], 'specificVolume', 1e-10 );
        $this->assertEquals( .405427273e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->waterPropertiesPH(.222930643e2, .237512401e4); 
        $this->assertEquals( 3, $tmp['region'], 'Region' );           
        $this->assertEquals( 650, $tmp['temperature'], 'temperature', 1e-6);   
        $this->assertEquals( 1/200, $tmp['specificVolume'], 'specificVolume', 1e-10 );
        $this->assertEquals( .485438792e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
        
        $tmp = $this->object->waterPropertiesPH(.783095639e2, .225868845e4);   
        $this->assertEquals( 3, $tmp['region'], 'Region' );        
        $this->assertEquals( 750, $tmp['temperature'], 'temperature', 1e-6);     
        $this->assertEquals( 1/500, $tmp['specificVolume'], 'specificVolume', 1e-10 );
        $this->assertEquals( .446971906e1, $tmp['specificEntropy'], 'specificEntropy', 1e-8);
    }
    
    /**
     * @covers Steam_IAPWS::backwardPHregion1Exact
     */
    public function testBackwardPHregion1Exact() {
        $tmp = $this->object->region1(3, 391.798509);
        $this->assertEquals(391.798509 , $this->object->backwardPHregion1Exact($tmp['pressure'], $tmp['specificEnthalpy']), 'Temperature', 1e-12 );
        $tmp = $this->object->region1(3, 378.108626);
        $this->assertEquals(378.108626 , $this->object->backwardPHregion1Exact($tmp['pressure'], $tmp['specificEnthalpy']), 'Temperature', 1e-12 );
        $tmp = $this->object->region1(80, 611.041229);
        $this->assertEquals(611.041229 , $this->object->backwardPHregion1Exact($tmp['pressure'], $tmp['specificEnthalpy']), 'Temperature', 1e-11 );        
    }
    
    /**
     * @covers Steam_IAPWS::backwardPHregion3
     */
    public function testBackwardPHregion3() {        
        $tmp = $this->object->region3( .255837018e2, 650);
        $this->assertEquals(650 , $this->object->backwardPHregion3($tmp['pressure'], $tmp['specificEnthalpy']), 'Temperature', 1e-7 );
        $tmp = $this->object->region3( .222930643e2, 650);
        $this->assertEquals(650 , $this->object->backwardPHregion3($tmp['pressure'], $tmp['specificEnthalpy']), 'Temperature', 1e-6 );
        $tmp = $this->object->region3( .783095639e2, 750);
        $this->assertEquals(750 , $this->object->backwardPHregion3($tmp['pressure'], $tmp['specificEnthalpy']), 'Temperature', 1e-7 );        
    }

    /**
     * @covers Steam_IAPWS::waterPropertiesPS
     */
    public function testWaterPropertiesPS() {
        //Region 1
        $tmp = $this->object->waterPropertiesPS(3, .392294792);  
        $this->assertEquals( 1, $tmp['region'], 'Region' );        
        $this->assertEquals( 300, $tmp['temperature'], 'temperature', 1e-7);
        $this->assertEquals( .100215168e-2, $tmp['specificVolume'], 'specificVolume', 1e-12 );  
        $this->assertEquals( .115331273e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-7);     
        
        $tmp = $this->object->waterPropertiesPS(80, .368563852);  
        $this->assertEquals( 1, $tmp['region'], 'Region' );        
        $this->assertEquals( 300, $tmp['temperature'], 'temperature', 1e-7);     
        $this->assertEquals( .971180894e-3, $tmp['specificVolume'], 'specificVolume', 1e-13 );
        $this->assertEquals( .184142828e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6);
        
        $tmp = $this->object->waterPropertiesPS(3, .258041912e1);  
        $this->assertEquals( 1, $tmp['region'], 'Region' );        
        $this->assertEquals( 500, $tmp['temperature'], 'temperature', 1e-7);     
        $this->assertEquals( .120241800e-2, $tmp['specificVolume'], 'specificVolume', 1e-11 );
        $this->assertEquals( .975542239e3, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-6); 
        
        //Region 2
        $tmp = $this->object->waterPropertiesPS(.0035, .852238967e1);
        $this->assertEquals( '2a', $tmp['region'], 'Region' );        
        $this->assertEquals( 300, $tmp['temperature'], 'temperature', 1e-6);    
        $this->assertEquals( .394913866e2, $tmp['specificVolume'], 'specificVolume', 1e-7 );
        $this->assertEquals( .254991145e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
        
        $tmp = $this->object->waterPropertiesPS(.0035, .101749996e2);   
        $this->assertEquals( '2a', $tmp['region'], 'Region' );        
        $this->assertEquals( 700, $tmp['temperature'], 'temperature', 1e-5);        
        $this->assertEquals( .923015898e2, $tmp['specificVolume'], 'specificVolume', 1e-6 );
        $this->assertEquals( .333568375e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-4);
        
        $tmp = $this->object->waterPropertiesPS(30, .517540298e1);  
        $this->assertEquals( '2c', $tmp['region'], 'Region' );        
        $this->assertEquals( 700, $tmp['temperature'], 'temperature', 1e-6);         
        $this->assertEquals( .542946619e-2, $tmp['specificVolume'], 'specificVolume', 1e-11);
        $this->assertEquals( .263149474e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);   
        
        //Region 3
        $tmp = $this->object->waterPropertiesPS(.255837018e2, .405427273e1); 
        $this->assertEquals( 3, $tmp['region'], 'Region' );        
        $this->assertEquals( 650, $tmp['temperature'], 'temperature', 1e-6);           
        $this->assertEquals( 1/500, $tmp['specificVolume'], 'specificVolume', 1e-10 );
        $this->assertEquals( .186343019e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
        
        $tmp = $this->object->waterPropertiesPS(.222930643e2, .485438792e1); 
        $this->assertEquals( 3, $tmp['region'], 'Region' );           
        $this->assertEquals( 650, $tmp['temperature'], 'temperature', 1e-6);   
        $this->assertEquals( 1/200, $tmp['specificVolume'], 'specificVolume', 1e-10 );
        $this->assertEquals( .237512401e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);        
        
        $tmp = $this->object->waterPropertiesPS(.783095639e2, .446971906e1);   
        $this->assertEquals( 3, $tmp['region'], 'Region' );        
        $this->assertEquals( 750, $tmp['temperature'], 'temperature', 1e-6);     
        $this->assertEquals( 1/500, $tmp['specificVolume'], 'specificVolume', 1e-10 );
        $this->assertEquals( .225868845e4, $tmp['specificEnthalpy'], 'specificEnthalpy', 1e-5);
    }
    
    public function testBackwardPSregion1Exact() {
        $tmp = $this->object->region1(3, 391.798509);
        $this->assertEquals(391.798509 , $this->object->backwardPSregion1Exact($tmp['pressure'], $tmp['specificEntropy']), 'Temperature', 1e-12 );
        $tmp = $this->object->region1(3, 378.108626);
        $this->assertEquals(378.108626 , $this->object->backwardPSregion1Exact($tmp['pressure'], $tmp['specificEntropy']), 'Temperature', 1e-12 );
        $tmp = $this->object->region1(80, 611.041229);
        $this->assertEquals(611.041229 , $this->object->backwardPSregion1Exact($tmp['pressure'], $tmp['specificEntropy']), 'Temperature', 1e-11 );        
    }
    
    /**
     * @covers Steam_IAPWS::backwardPSregion3
     */
    public function testBackwardPSregion3() {        
        $tmp = $this->object->region3( .255837018e2, 650 );
        $this->assertEquals(650 , $this->object->backwardPSregion3($tmp['pressure'], $tmp['specificEntropy']), "Temperature [Pressure {$tmp['pressure']} | SpEntropy {$tmp['specificEntropy']}]", 1e-7 );
        $tmp = $this->object->region3( .222930643e2, 650 );
        $this->assertEquals(650 , $this->object->backwardPSregion3($tmp['pressure'], $tmp['specificEntropy']), "Temperature [Pressure {$tmp['pressure']} | SpEntropy {$tmp['specificEntropy']}]", 1e-6 );
        $tmp = $this->object->region3( .783095639e2, 750 );
        $this->assertEquals(750 , $this->object->backwardPSregion3($tmp['pressure'], $tmp['specificEntropy']), "Temperature [Pressure {$tmp['pressure']} | SpEntropy {$tmp['specificEntropy']}]", 1e-7 );        
    }

    /**
     * @covers Steam_IAPWS::rangeTemperatureByPressure
     */
    public function testRangeTemperatureByPressure() {
        $tmp = $this->object->rangeTemperatureByPressure(.1);
        $this->assertEquals(Steam_IAPWS::TEMPERATURE_MIN , $tmp['min'], 'Temperature Min', 1e-12 );
        $this->assertEquals(Steam_IAPWS::TEMPERATURE_MAX , $tmp['max'], 'Temperature Max', 1e-12 );
        
        $tmp = $this->object->rangeTemperatureByPressure(3);
        $this->assertEquals(Steam_IAPWS::TEMPERATURE_MIN , $tmp['min'], 'Temperature Min', 1e-12 );
        $this->assertEquals(Steam_IAPWS::TEMPERATURE_MAX , $tmp['max'], 'Temperature Max', 1e-12 );
        
        $tmp = $this->object->rangeTemperatureByPressure(15);
        $this->assertEquals(Steam_IAPWS::TEMPERATURE_MIN , $tmp['min'], 'Temperature Min', 1e-12 );
        $this->assertEquals(Steam_IAPWS::TEMPERATURE_MAX , $tmp['max'], 'Temperature Max', 1e-12 );
    }

    /**
     * @covers Steam_IAPWS::rangeSpecificEntropyByPressure
     */
    public function testRangeSpecificEntropyByPressure() {
        $tmp = $this->object->rangeSpecificEntropyByPressure(.1);
        $this->assertEquals(0 , $tmp['min'], 'Specific Entropy Min', 1e-2 );
        $this->assertEquals(9.57 , $tmp['max'], 'Specific Entropy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEntropyByPressure(3);
        $this->assertEquals(0 , $tmp['min'], 'Specific Entropy Min', 1e-2 );
        $this->assertEquals(7.99 , $tmp['max'], 'Specific Entropy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEntropyByPressure(15);
        $this->assertEquals(0 , $tmp['min'], 'Specific Entropy Min', 1e-2 );
        $this->assertEquals(7.20 , $tmp['max'], 'Specific Entropy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEntropyByPressure(50);
        $this->assertEquals(0 , $tmp['min'], 'Specific Entropy Min', 1e-2 );
        $this->assertEquals(6.52 , $tmp['max'], 'Specific Entropy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEntropyByPressure(100);
        $this->assertEquals(0 , $tmp['min'], 'Specific Entropy Min', 1e-2 );
        $this->assertEquals(6.04 , $tmp['max'], 'Specific Entropy Max', 1e-2 );
    }

    /**
     * @covers Steam_IAPWS::rangeSpecificEnthalpyByPressure
     */
    public function testRangeSpecificEnthalpyByPressure() {
        $tmp = $this->object->rangeSpecificEnthalpyByPressure(.1);
        $this->assertEquals(0 , $tmp['min'], 'Specific Enthalpy Min', 1e-1 );
        $this->assertEquals(4160.21 , $tmp['max'], 'Specific Enthalpy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEnthalpyByPressure(3);
        $this->assertEquals(3.00 , $tmp['min'], 'Specific Enthalpy Min', 1e-1 );
        $this->assertEquals(4147.03  , $tmp['max'], 'Specific Enthalpy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEnthalpyByPressure(15);
        $this->assertEquals(15.07, $tmp['min'], 'Specific Enthalpy Min', 1e-1 );
        $this->assertEquals(4091.33 , $tmp['max'], 'Specific Enthalpy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEnthalpyByPressure(50);
        $this->assertEquals(49.13 , $tmp['min'], 'Specific Enthalpy Min', 1e-1 );
        $this->assertEquals(3925.96 , $tmp['max'], 'Specific Enthalpy Max', 1e-2 );
        
        $tmp = $this->object->rangeSpecificEnthalpyByPressure(100);
        $this->assertEquals(95.39 , $tmp['min'], 'Specific Enthalpy Min', 1e-1 );
        $this->assertEquals(3715.19 , $tmp['max'], 'Specific Enthalpy Max', 1e-2 );
    }
    
    /**
     * @covers Steam_IAPWS::rangeByPressure
     */
    public function testRangeByPressure() {
        $tmp = $this->object->rangeByPressure(3, 'specificEnthalpy');
        $this->assertEquals(3.00 , $tmp['min'], 'Specific Enthalpy Min', 1e-1 );
        $this->assertEquals(4147.03  , $tmp['max'], 'Specific Enthalpy Max', 1e-2 );
                
        $tmp = $this->object->rangeByPressure(3, 'specificEntropy');
        $this->assertEquals(0 , $tmp['min'], 'Specific Entropy Min', 1e-2 );
        $this->assertEquals(7.99 , $tmp['max'], 'Specific Entropy Max', 1e-2 );
    }
}

?>
