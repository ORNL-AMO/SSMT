<?php
/**
 * Steam Calculators 
 * 
 * Location of all equations from IAPWS IF97
 *
 * @package    Steam
 * @subpackage Steam_IAPWS
 * @version    beta
 * @author     Michael B Muller
 * <mbm@analyticalenergy.com>
 */

/**
 * Class implements equations from IAPWS IF97 
 * International Association for the Properties of Water and Steam's 
 * Thermodynamic Properties of Water and Steam Industrial Formulation, www.iapws.org
 * 
 * @package    Steam
 * @subpackage    Steam_IAPWS
 */
class Steam_IAPWS_Core{    

    /**
     * Returns the boundary Pressure associated with the given Temperature
     * Temperature Range 623.15 to 863.15 K
     * @param float $temperature K
     * @return float $pressure MPa
     */
    function boundaryByTemperatureRegion3to2($temperature){
	$constant['n']=array(
            NULL, 
            0.34805185628969E+03, 
            -0.11671859879975E+01, 
            0.10192970039326E-02,  
            0.57254459862746E+03, 
            0.13918839778870E+02);
	return $constant['n'][1]+$constant['n'][2]*$temperature+$constant['n'][3]*pow($temperature,2);
    }
    
    /**
     * Returns the boundary Temperature associated with the given Pressure
     * Temperature Range 16.5292 to 100 MPa
     * @param float $pressure MPa
     * @return float $temperature K
     */
    function boundaryByPressureRegion3to2($pressure){
        $constant['n']=array(NULL, 0.34805185628969E+03, -0.11671859879975E+01, 0.10192970039326E-02,  0.57254459862746E+03, 0.13918839778870E+02);
        return $constant['n'][4]+pow(($pressure-$constant['n'][5])/$constant['n'][3],.5);
    }
    
    /**
     * Returns Steam Properties based on the Region 1 equations
     * @param float $pressure MPa
     * @param float $temperature K
     * @return STEAM_PROPERTIES
     */
    function region1($pressure, $temperature){
	$constant['n']=array(NULL, 
            0.14632971213167,     -0.84548187169114,    -0.37563603672040e1,
            0.33855169168385e1,   -0.95791963387872,     0.15772038513228,
	   -0.16616417199501e-1,   0.81214629983568e-3,  0.28319080123804e-3,
	   -0.60706301565874e-3,  -0.18990068218419e-1, -0.32529748770505e-1,
	   -0.21841717175414e-1,  -0.52838357969930e-4, -0.47184321073267e-3,
	   -0.30001780793026e-3,   0.47661393906987e-4, -0.44141845330846e-5,
	   -0.72694996297594e-15, -0.31679644845054e-4, -0.28270797985312e-5,
	   -0.85205128120103e-9,  -0.22425281908000e-5, -0.65171222895601e-6,
	   -0.14341729937924e-12, -0.40516996860117e-6, -0.12734301741641e-8,
	   -0.17424871230634e-9,  -0.68762131295531e-18, 0.14478307828521e-19,
            0.26335781662795e-22, -0.11947622640071e-22, 0.18228094581404e-23,
	   -0.93537087292458e-25);
	$constant['J']=array(NULL, -2, -1, 0, 1, 2, 3, 4, 5, -9, -7, -1, 0, 1, 3, -3, 0, 1, 3, 17,
	  -4, 0, 6, -5, -2, 10, -8, -11, -6, -29, -31, -38, -39, -40, -41);
	$constant['I']=array(NULL, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 3, 3, 3,
	   4, 4, 4, 5, 8, 8, 21, 23, 29, 30, 31, 32);

	$reduced_pressure=$pressure/16.53;
	$inverse_reduced_temp=1386/$temperature;

	$gibbs=0;
	$gibbs_pi=0;
	$gibbs_pipi=0;
	$gibbs_t=0;
	$gibbs_TT=0;
	$gibbs_pit=0;
	
	for($i=1;$i<=34;$i++){
            $gibbs+=$constant['n'][$i]*pow((7.1-$reduced_pressure),$constant['I'][$i])*pow(($inverse_reduced_temp-1.222),$constant['J'][$i]);
            $gibbs_pi+=-$constant['n'][$i]*$constant['I'][$i]*pow((7.1-$reduced_pressure),$constant['I'][$i]-1)*pow(($inverse_reduced_temp-1.222),$constant['J'][$i]);

            $gibbs_t+=$constant['n'][$i]*pow((7.1-$reduced_pressure),$constant['I'][$i])*$constant['J'][$i]*pow(($inverse_reduced_temp-1.222),$constant['J'][$i]-1);
	}

	$constant_R=.461526;
	$region1['temperature'] = $temperature;
	$region1['pressure'] = $pressure;
	$region1['phase'] = 'Liquid';
	$region1['quality'] = null;
	$region1['specificVolume']=$reduced_pressure*$gibbs_pi*$temperature*$constant_R/$pressure/1000;
        $region1['density'] = 1 / $region1['specificVolume'];
	
	$region1['specificEnthalpy']=$inverse_reduced_temp*$gibbs_t*$temperature*$constant_R;
	$region1['specificEntropy']=($inverse_reduced_temp*$gibbs_t-$gibbs)*$constant_R;


	return $region1;
    }
    
    /**
     * Returns Temperature based on pressure and enthalpy for Region1
     * @param float $pressure MPa
     * @param float $specificEnthalpy kJ/kg
     * @return float $temperature K 
     */
    function backwardPHregion1($pressure, $enthalpy){
        $constant['I'] = array(
            0, 0, 0, 0, 0,
            0, 1, 1, 1, 1,
            1, 1, 1, 2, 2,
            3, 3, 4, 5, 6
        );
        $constant['J'] = array(
            0, 1, 2, 6, 22,
            32, 0, 1, 2, 3,
            4, 10, 32, 10, 32,
            10, 32, 32, 32, 32,
        );
        $constant['n'] = array(
            -0.23872489924521E+3,
            0.40421188637945E+3,
            0.11349746881718E+3,
            -0.58457616048039E+1,
            -0.15285482413140E-3,

            -0.10866707695377E-5,
            -0.13391744872602E+2,
            0.43211039183559E+2,
            -0.54010067170506E+2,
            0.30535892203916E+2,

            -0.65964749423638E+1,
            0.93965400878363E-2,
            0.11573647505340E-6,
            -0.25858641282073E-4,
            -0.40644363084799E-8,

            0.66456186191635E-7,
            0.80670734103027E-10,
            -0.93477771213947E-12,
            0.58265442020601E-14,
            -0.15020185953503E-16
        );

        $nu = $enthalpy / 2500;
        $temp = 0;
        for($i=0;$i<20;$i++){
        $temp += $constant['n'][$i]
            * pow($pressure, $constant['I'][$i])
            * pow(($nu+1), $constant['J'][$i]);
        }
        return $temp;        
    }

     /**
     * Returns Temperature based on pressure and entropy for Region1
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    function backwardPSregion1($pressure, $specificEntropy){
        $constants = array(
            1 => array(0, 0, 0.17478268058307E+3),
            2 => array(0, 1, 0.34806930892873E+2),
            3 => array(0, 2, 0.65292584978455E+1),
            4 => array(0, 3, 0.33039981775489),
            5 => array(0, 11, -0.19281382923196E-6),
            6 => array(0, 31, -0.24909197244573E-22),
            7 => array(1, 0, -0.26107636489332),
            8 => array(1, 1, 0.22592965981586),
            9 => array(1, 2, -0.64256463395226E-1),
            10 => array(1, 3, 0.78876289270526E-2),
            11 => array(1, 12, 0.35672110607366E-9),
            12 => array(1, 31, 0.17332496994895E-23),
            13 => array(2, 0, 0.56608900654837E-3),
            14 => array(2, 1, -0.32635483139717E-3),
            15 => array(2, 2, 0.44778286690632E-4),
            16 => array(2, 9, -0.51322156908507E-9),
            17 => array(2, 31, -0.42522657042207E-25),
            18 => array(3, 10, 0.26400441360689E-12),
            19 => array(3, 32, 0.78124600459723E-28),
            20 => array(4, 32, -0.30732199903668E-30),
            );

        $temp = 0;
        for($i=1;$i<=20;$i++){
        $temp += $constants[$i][2]
            * pow($pressure, $constants[$i][0])
            * pow(($specificEntropy+2), $constants[$i][1]);
        }
        return $temp;
    }

    /**
     * Returns Steam Properties based on the Region 2 equations
     * @param float $pressure MPa
     * @param float $temperature K
     * @return STEAM_PROPERTIES
     */
    function region2($pressure, $temperature){
        $constant['n'][0]=array( NULL, -0.96927686500217E+01,  0.10086655968018E+02, -0.56087911283020E-02,
            0.71452738081455E-01, -0.40710498223928E+00,  0.14240819171444E+01,
        -0.43839511319450E+01, -0.28408632460772E+00,  0.21268463753307E-01);
        $constant['J'][0]=array( NULL, 0, 1, -5, -4, -3, -2, -1, 2,  3);

        $constant['n'][1]=array( NULL, -0.17731742473213E-02,  -0.17834862292358E-01,  -0.45996013696365E-01,
        -0.57581259083432E-01,  -0.50325278727930E-01,  -0.33032641670203E-04,
        -0.18948987516315E-03,  -0.39392777243355E-02,  -0.43797295650573E-01,
        -0.26674547914087E-04,   0.20481737692309E-07,   0.43870667284435E-06,
        -0.32277677238570E-04,  -0.15033924542148E-02,  -0.40668253562649E-01,
        -0.78847309559367E-09,   0.12790717852285E-07,   0.48225372718507E-06,
            0.22922076337661E-05,  -0.16714766451061E-10,  -0.21171472321355E-02,
        -0.23895741934104E+02,  -0.59059564324270E-17,  -0.12621808899101E-05,
        -0.38946842435739E-01,   0.11256211360459E-10,  -0.82311340897998E+01,
            0.19809712802088E-07,   0.10406965210174E-18,  -0.10234747095929E-12,
        -0.10018179379511E-08,  -0.80882908646985E-10,   0.10693031879409E+00,
        -0.33662250574171E+00,   0.89185845355421E-24,   0.30629316876232E-12,
        -0.42002467698208E-05,  -0.59056029685639E-25,   0.37826947613457E-05,
        -0.12768608934681E-14,   0.73087610595061E-28,   0.55414715350778E-16,
        -0.94369707241210E-06);
        $constant['J'][1]=array( NULL, 0, 1, 2, 3, 6, 1, 2, 4, 7, 36, 0, 1, 3, 6, 35, 1, 2, 3, 7, 3, 16, 35, 0, 11,
        25, 8, 36, 13, 4, 10, 14, 29, 50, 57, 20, 35, 48, 21, 53, 39, 26, 40, 58 );
        $constant['I'][1]=array( NULL, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4, 4, 5, 6, 6, 6, 7, 7, 7,
        8, 8, 9, 10, 10, 10, 16, 16, 18, 20, 20, 20, 21, 22, 23, 24, 24, 24 );

	$reduced_pressure=$pressure;
	$inverse_reduced_temp=540/$temperature;

	$gibbs[0]=log($reduced_pressure);
	$gibbs_pi[0]=1/$reduced_pressure;
	$gibbs_pipi[0]=-1/pow($reduced_pressure,2);
	$gibbs_t[0]=0;
	$gibbs_TT[0]=0;
	$gibbs_pit[0]=0;

	for($i=1;$i<=9;$i++){
            $gibbs[0]+=
                    $constant['n'][0][$i]
                    *pow($inverse_reduced_temp,$constant['J'][0][$i]);
            $gibbs_t[0]+=
                    $constant['n'][0][$i]
                    *$constant['J'][0][$i]
                    *pow($inverse_reduced_temp,$constant['J'][0][$i]-1);
            $gibbs_TT[0]+=
                    $constant['n'][0][$i]
                    *$constant['J'][0][$i]
                    *($constant['J'][0][$i]-1)
                    *pow($inverse_reduced_temp,$constant['J'][0][$i]-2);
	}

	$gibbs[1]=0;
	$gibbs_pi[1]=0;
	$gibbs_pipi[1]=0;
	$gibbs_t[1]=0;
	$gibbs_TT[1]=0;
	$gibbs_pit[1]=0;
	//echo ($reduced_density)."::".log($reduced_density)."<BR>";
	for($i=1;$i<=43;$i++){
            $gibbs[1]+=
                    $constant['n'][1][$i]
                    *pow($reduced_pressure,$constant['I'][1][$i])
                    *pow(($inverse_reduced_temp-0.5),$constant['J'][1][$i]);
            $gibbs_pi[1]+=
                    $constant['n'][1][$i]
                    *$constant['I'][1][$i]
                    *pow($reduced_pressure,$constant['I'][1][$i]-1)
                    *pow(($inverse_reduced_temp-0.5),$constant['J'][1][$i]);
      
            $gibbs_t[1]+=
                    $constant['n'][1][$i]
                    *pow($reduced_pressure,$constant['I'][1][$i])
                    *$constant['J'][1][$i]
                    *pow(($inverse_reduced_temp-0.5),$constant['J'][1][$i]-1);

	}

	$constant_R=.461526;
	$region1['temperature'] = $temperature;
	$region1['pressure'] = $pressure;
	$region1['phase'] = 'Gas';
	$region1['quality'] = null;
	$region1['specificVolume']=$reduced_pressure*($gibbs_pi[0]+$gibbs_pi[1])*$temperature*$constant_R/$pressure/1000;
        $region1['density'] = 1/$region1['specificVolume'];
	//$region1['internalEnergy']=($inverse_reduced_temp*($gibbs_t[0]+$gibbs_t[1])-$reduced_pressure*($gibbs_pi[0]+$gibbs_pi[1]))*$temperature*$constant_R;
	$region1['specificEnthalpy']=$inverse_reduced_temp*($gibbs_t[0]+$gibbs_t[1])*$temperature*$constant_R;
	$region1['specificEntropy']=($inverse_reduced_temp*($gibbs_t[0]+$gibbs_t[1])-($gibbs[0]+$gibbs[1]))*$constant_R;        
	return $region1;
    }
    
    /**
     * Returns Temperature based on Pressure and Enthalpy for Region2a
     * @param float $pressure MPa
     * @param float $specificEnthapy kJ/kg
     * @return float $temperature K 
     */
    function backwardPHregion2a($pressure, $specificEnthapy){
        $constants = array(
            1 => array(0, 0, 0.10898952318288E+4),
            2 => array(0, 1, 0.84951654495535E+3),
            3 => array(0, 2, -0.10781748091826E+3),
            4 => array(0, 3, 0.33153654801263E+2),
            5 => array(0, 7, -0.74232016790248E+1),
            6 => array(0, 20, 0.11765048724356E+2),
            7 => array(1, 0, 0.18445749355790E+1),
            8 => array(1, 1, -0.41792700549624E+1),
            9 => array(1, 2, 0.62478196935812E+1),
            10 => array(1, 3, -0.17344563108114E+2),
            11 => array(1, 7, -0.20058176862096E+3),
            12 => array(1, 9, 0.27196065473796E+3),
            13 => array(1, 11, -0.45511318285818E+3),
            14 => array(1, 18, 0.30919688604755E+4),
            15 => array(1, 44, 0.25226640357872E+6),
            16 => array(2, 0, -0.61707422868339E-2),
            17 => array(2, 2, -0.31078046629583),
            18 => array(2, 7, 0.11670873077107E+2),
            19 => array(2, 36, 0.12812798404046E+9),
            20 => array(2, 38, -0.98554909623276E+9),
            21 => array(2, 40, 0.28224546973002E+10),
            22 => array(2, 42, -0.35948971410703E+10),
            23 => array(2, 44, 0.17227349913197E+10),
            24 => array(3, 24, -0.13551334240775E+5),
            25 => array(3, 44, 0.12848734664650E+8),
            26 => array(4, 12, 0.13865724283226E+1),
            27 => array(4, 32, 0.23598832556514E+6),
            28 => array(4, 44, -0.13105236545054E+8),
            29 => array(5, 32, 0.73999835474766E+4),
            30 => array(5, 36, -0.55196697030060E+6),
            31 => array(5, 42, 0.37154085996233E+7),
            32 => array(6, 34, 0.19127729239660E+5),
            33 => array(6, 44, -0.41535164835634E+6),
            34 => array(7, 28, -0.62459855192507E+2)
        );
        $temperature = 0;
        $nu = $specificEnthapy/2000;
        for($i=1;$i<=34;$i++){
            $temperature += $constants[$i][2]
                * pow($pressure, $constants[$i][0])
                * pow(($nu-2.1),$constants[$i][1]);
        }
        return $temperature;
    }

    /**
     * Returns Temperature based on Pressure and Enthalpy for Region2b
     * @param float $pressure MPa
     * @param float $specificEnthapy kJ/kg
     * @return float $temperature K 
     */
    function backwardPHregion2b($pressure, $specificEnthapy){
        $constants['I'] = array(
            0, 0, 0, 0, 0,
            0, 0, 0, 1, 1,
            1, 1, 1, 1, 1,
            1, 2, 2, 2, 2,
            3, 3, 3, 3, 4,
            4, 4, 4, 4, 4,
            5, 5, 5, 6, 7,
            7, 9, 9,
            );

        $constants['J'] = array(
            0, 1, 2, 12, 18,
            24, 28, 40, 0, 2,
            6, 12, 18, 24, 28,
            40, 2, 8, 18, 40,
            1, 2, 12, 24, 2,
            12, 18, 24, 28, 40,
            18, 24, 40, 28, 2,
            28, 1, 40,
            );

        $constants['n'] = array(
            0.14895041079516E+4,
            0.74307798314034E+3,
            -0.97708318797837E+2,
            0.24742464705674E+1,
            -0.63281320016026,
            0.11385952129658E+1,
            -0.47811863648625,
            0.85208123431544E-2,
            0.93747147377932,
            0.33593118604916E+1,
            0.33809355601454E+1,
            0.16844539671904,
            0.73875745236695,
            -0.47128737436186,
            0.15020273139707,
            -0.21764114219750E-2,
            -0.21810755324761E-1,
            -0.10829784403677,
            -0.46333324635812E-1,
            0.71280351959551E-4,
            0.11032831789999E-3,
            0.18955248387902E-3,
            0.30891541160537E-2,
            0.13555504554949E-2,
            0.28640237477456E-6,
            -0.10779857357512E-4,
            -0.76462712454814E-4,
            0.14052392818316E-4,
            -0.31083814331434E-4,
            -0.10302738212103E-5,
            0.28217281635040E-6,
            0.12704902271945E-5,
            0.73803353468292E-7,
            -0.11030139238909E-7,
            -0.81456365207833E-13,
            -0.25180545682962E-10,
            -0.17565233969407E-17,
            0.86934156344163E-14,
                    );
        $temperature = 0;
        $nu = $specificEnthapy/2000;
        for($i=0;$i<38;$i++){
            $temperature += $constants['n'][$i]
                * pow(($pressure-2), $constants['I'][$i])
                * pow(($nu-2.6),$constants['J'][$i]);
        }
        return $temperature;
    }

    /**
     * Returns Temperature based on Pressure and Enthalpy for Region2c
     * @param float $pressure MPa
     * @param float $specificEnthapy kJ/kg
     * @return float $temperature K 
     */
    function backwardPHregion2c($pressure, $specificEnthapy){
        $constants['I'] = array(
            -7, -7, -6, -6, -5,
            -5, -2, -2, -1, -1,
            0, 0, 1, 1, 2,
            6, 6, 6, 6, 6,
            6, 6, 6,
        );
        $constants['J'] = array(
            0, 4, 0, 2, 0,
            2, 0, 1, 0, 2,
            0, 1, 4, 8, 4,
            0, 1, 4, 10, 12,
            16, 20, 22,
        );
        $constants['n'] = array(
            -0.32368398555242E+13,
            0.73263350902181E+13,
            0.35825089945447E+12,
            -0.58340131851590E+12,
            -0.10783068217470E+11,
            0.20825544563171E+11,
            0.61074783564516E+6,
            0.85977722535580E+6,
            -0.25745723604170E+5,
            0.31081088422714E+5,
            0.12082315865936E+4,
            0.48219755109255E+3,
            0.37966001272486E+1,
            -0.10842984880077E+2,
            -0.45364172676660E-1,
            0.14559115658698E-12,
            0.11261597407230E-11,
            -0.17804982240686E-10,
            0.12324579690832E-6,
            -0.11606921130984E-5,
            0.27846367088554E-4,
            -0.59270038474176E-3,
            0.12918582991878E-2,
        );
        $temperature = 0;
        $nu = $specificEnthapy/2000;
        for($i=0;$i<23;$i++){
            $temperature += $constants['n'][$i]
                * pow(($pressure+25), $constants['I'][$i])
                * pow(($nu-1.8),$constants['J'][$i]);
        }
        return $temperature;
    }

    /**
     * Returns Temperature based on Pressure and Entropy for Region2a
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    function backwardPSregion2a($pressure, $specificEntropy){
        $constants = array(
            1 => array(-1.5, -24, -0.39235983861984E+6),
            2 => array(-1.5, -23, 0.51526573827270E+6),
            3 => array(-1.5, -19, 0.40482443161048E+5),
            4 => array(-1.5, -13, -0.32193790923902E+3),
            5 => array(-1.5, -11, 0.96961424218694E+2),
            6 => array(-1.5, -10, -0.22867846371773E+2),
            7 => array(-1.25, -19, -0.44942914124357E+6),
            8 => array(-1.25, -15, -0.50118336020166E+4),
            9 => array(-1.25, -6, 0.35684463560015),
            10 => array(-1.0, -26, 0.44235335848190E+5),
            11 => array(-1.0, -21, -0.13673388811708E+5),
            12 => array(-1.0, -17, 0.42163260207864E+6 ),
            13 => array(-1.0, -16, 0.22516925837475E+5 ),
            14 => array(-1.0, -9, 0.47442144865646E+3 ),
            15 => array(-1.0, -8, -0.14931130797647E+3 ),
            16 => array(-0.75, -15, -0.19781126320452E+6 ),
            17 => array(-0.75, -14, -0.23554399470760E+5 ),
            18 => array(-0.5, -26, -0.19070616302076E+5 ),
            19 => array(-0.5, -13, 0.55375669883164E+5 ),
            20 => array(-0.5, -9, 0.38293691437363E+4 ),
            21 => array(-0.5, -7, -0.60391860580567E+3 ),
            22 => array(-0.25, -27, 0.19363102620331E+4),
            23 => array(-0.25, -25, 0.42660643698610E+4 ),
            24 => array(-0.25, -11, -0.59780638872718E+4),
            25 => array(-0.25, -6, -0.70401463926862E+3),
            26 => array(0.25, 1, 0.33836784107553E+3),
            27 => array(0.25, 4, 0.20862786635187E+2),
            28 => array(0.25, 8, 0.33834172656196E-1),
            29 => array(0.25, 11, -0.43124428414893E-4),
            30 => array(0.5, 0, 0.16653791356412E+3),
            31 => array(0.5, 1, -0.13986292055898E+3),
            32 => array(0.5, 5, -0.78849547999872),
            33 => array(0.5, 6, 0.72132411753872E-1),
            34 => array(0.5, 10, -0.59754839398283E-2),
            35 => array(0.5, 14, -0.12141358953904E-4),
            36 => array(0.5, 16, 0.23227096733871E-6),
            37 => array(0.75, 0, -0.10538463566194E+2),
            38 => array(0.75, 4, 0.20718925496502E+1),
            39 => array(0.75, 9, -0.72193155260427E-1),
            40 => array(0.75, 17, 0.20749887081120E-6),
            41 => array(1.0, 7, -0.18340657911379E-1),
            42 => array(1.0, 18, 0.29036272348696E-6),
            43 => array(1.25, 3, 0.21037527893619),
            44 => array(1.25, 15, 0.25681239729999E-3),
            45 => array(1.5, 5, -0.12799002933781E-1),
            46 => array(1.5, 18, -0.82198102652018E-5),
        );

        $temperature = 0;
        for($i=1;$i<=46;$i++){
        $temperature += $constants[$i][2]
            * pow($pressure, $constants[$i][0])
            * pow(($specificEntropy/2-2), $constants[$i][1]);
        }
        return $temperature;
    }

    /**
     * Returns Temperature based on Pressure and Entropy for Region2b
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    function backwardPSregion2b($pressure, $specificEntropy){
        $constants = array(
        1 => array(-6, 0, 0.31687665083497E+6 ),
        2 => array(-6, 11, 0.20864175881858E+2 ),
        3 => array(-5, 0, -0.39859399803599E+6 ),
        4 => array(-5, 11, -0.21816058518877E+2 ),
        5 => array(-4, 0, 0.22369785194242E+6 ),

        6 => array(-4, 1, -0.27841703445817E+4 ),
        7 => array(-4, 11, 0.99207436071480E+1 ),
        8 => array(-3, 0, -0.75197512299157E+5 ),
        9 => array(-3, 1, 0.29708605951158E+4 ),
        10 => array(-3, 11, -0.34406878548526E+1 ),

        11 => array(-3, 12, 0.38815564249115 ),
        12 => array(-2, 0, 0.17511295085750E+5 ),
        13 => array(-2, 1, -0.14237112854449E+4 ),
        14 => array(-2, 6, 0.10943803364167E+1 ),
        15 => array(-2, 10, 0.89971619308495 ),

        16 => array(-1, 0, -0.33759740098958E+4 ),
        17 => array(-1, 1, 0.47162885818355E+3 ),
        18 => array(-1, 5, -0.19188241993679E+1 ),
        19 => array(-1, 8, 0.41078580492196 ),
        20 => array(-1, 9, -0.33465378172097 ),

        21 => array(0, 0, 0.13870034777505E+4 ),
        22 => array(0, 1, -0.40663326195838E+3 ),
        23 => array(0, 2, 0.41727347159610E+2),
        24 => array(0, 4, 0.21932549434532E+1),
        25 => array(0, 5, -0.10320050009077E+1),

        26 => array(0, 6, 0.35882943516703),
        27 => array(0, 9, 0.52511453726066E-2),
        28 => array(1, 0, 0.12838916450705E+2),
        29 => array(1, 1, -0.28642437219381E+1),
        30 => array(1, 2, 0.56912683664855),

        31 => array(1, 3, -0.99962954584931E-1),
        32 => array(1, 7, -0.32632037778459E-2),
        33 => array(1, 8, 0.23320922576723E-3),
        34 => array(2, 0, -0.15334809857450),
        35 => array(2, 1, 0.29072288239902E-1),

        36 => array(2, 5, 0.37534702741167E-3),
        37 => array(3, 0, 0.17296691702411E-2),
        38 => array(3, 1, -0.38556050844504E-3),
        39 => array(3, 3, -0.35017712292608E-4),
        40 => array(4, 0, -0.14566393631492E-4),

        41 => array(4, 1, 0.56420857267269E-5),
        42 => array(5, 0, 0.41286150074605E-7),
        43 => array(5, 1, -0.20684671118824E-7),
        44 => array(5, 2, 0.16409393674725E-8),
        );

        $temperature = 0;
        for($i=1;$i<=44;$i++){
        $temperature += $constants[$i][2]
            * pow($pressure, $constants[$i][0])
            * pow((10-$specificEntropy/0.7853), $constants[$i][1]);
        }
        return $temperature;
    }

    /**
     * Returns Temperature based on Pressure and Entropy for Region2c
     * @param float $pressure MPa
     * @param float $specificEntropy kJ/kg/K
     * @return float $temperature K 
     */
    function backwardPSregion2c($pressure, $specificEntropy){
        $constants = array(
        1 => array(-2, 0, 0.90968501005365E+3 ),
        2 => array(-2, 1, 0.24045667088420E+4 ),
        3 => array(-1, 0, -0.59162326387130E+3 ),
        4 => array(0, 0, 0.54145404128074E+3 ),
        5 => array(0, 1, -0.27098308411192E+3 ),
        6 => array(0, 2, 0.97976525097926E+3 ),
        7 => array(0, 3, -0.46966772959435E+3 ),
        8 => array(1, 0, 0.14399274604723E+2 ),
        9 => array(1, 1, -0.19104204230429E+2 ),
        10 => array(1, 3, 0.53299167111971E+1 ),
        11 => array(1, 4, -0.21252975375934E+2 ),
        12 => array(2, 0, -0.31147334413760 ),
        13 => array(2, 1, 0.60334840894623 ),
        14 => array(2, 2, -0.42764839702509E-1 ),
        15 => array(3, 0, 0.58185597255259E-2 ),
        16 => array(3, 1, -0.14597008284753E-1),
        17 => array(3, 5, 0.56631175631027E-2),
        18 => array(4, 0, -0.76155864584577E-4),
        19 => array(4, 1, 0.22440342919332E-3),
        20 => array(4, 4, -0.12561095013413E-4),
        21 => array(5, 0, 0.63323132660934E-6),
        22 => array(5, 1, -0.20541989675375E-5),
        23 => array(5, 2, 0.36405370390082E-7),
        24 => array(6, 0, -0.29759897789215E-8),
        25 => array(6, 1, 0.10136618529763E-7),
        26 => array(7, 0, 0.59925719692351E-11),
        27 => array(7, 1, -0.20677870105164E-10),
        28 => array(7, 3, -0.20874278181886E-10),
        29 => array(7, 4, 0.10162166825089E-9),
        30 => array(7, 5, -0.16429828281347E-9),
        );
        $temperature = 0;
        for($i=1;$i<=30;$i++){
        $temperature += $constants[$i][2]
            * pow($pressure, $constants[$i][0])
            * pow((2-$specificEntropy/2.9251), $constants[$i][1]);
        }
        return $temperature;
    }
    
    /**
     * Returns Steam Properties based on the Region 3 equations
     * @param float $density kg/m3
     * @param float $temperature K
     * @return STEAM_PROPERTIES
     */
    function region3Density($density, $temperature){
	$constant['n']=array(NULL, 
            0.10658070028513E+01, 
           -0.15732845290239E+02,  
            0.20944396974307E+02,
           -0.76867707878716E+01,  
            0.26185947787954E+01, 
            
           -0.28080781148620E+01,
	    0.12053369696517E+01, 
           -0.84566812812502E-02, 
           -0.12654315477714E+01,
	   -0.11524407806681E+01,  
            
            0.88521043984318E+00, 
           -0.64207765181607E+00,
	    0.38493460186671E+00, 
           -0.85214708824206E+00,  
            0.48972281541877E+01,
            
	   -0.30502617256965E+01,  
            0.39420536879154E-01,  
            0.12558408424308E+00,
	   -0.27999329698710E+00,  
            0.13899799569460E+01, 
            //Next 20
           -0.20189915023570E+01,
	   -0.82147637173963E-02, 
           -0.47596035734923E+00,  
            0.43984074473500E-01,
	   -0.44476435428739E+00,  
           
            0.90572070719733E+00,  
            0.70522450087967E+00,
	    0.10770512626332E+00, 
           -0.32913623258954E+00, 
           -0.50871062041158E+00,
	   
           -0.22175400873096E-01,  
            0.94260751665092E-01,  
            0.16436278447961E+00,
	   -0.13503372241348E-01, 
           -0.14834345352472E-01, 
            
            0.57922953628084E-03,
	    0.32308904703711E-02,  
            0.80964802996215E-04,
           -0.16557679795037E-03,
	   -0.44923899061815E-04 );

	$constant['J']=array(NULL, 
             0,  0,  1, 2,  7, 
            10, 12, 23, 2,  6, 
            15, 17,  0, 2,  6, 
             7, 22, 26, 0,  2, 
             4, 16, 26, 0,  2, 
             4, 26,  1, 3, 26, 
             0,  2, 26, 2, 26, 
             2, 26,  0, 1, 26);
	$constant['I']=array(NULL, 
            0, 0, 0, 0, 0, 
            0, 0, 0, 1, 1, 
            1, 1, 2, 2, 2, 
            2, 2, 2, 3, 3, 
            3, 3, 3, 4, 4, 
            4, 4, 5, 5, 5, 
            6, 6, 6, 7, 8, 
            9, 9, 10, 10, 11);

	$reduced_density=$density/322.000000;
	$inverse_reduced_temp=647.096/$temperature;

	$helmholtz=$constant['n'][1]*log($reduced_density);
	$helmholtz_s=$constant['n'][1]/$reduced_density;
	$helmholtz_SS=-$constant['n'][1]/pow($reduced_density,2);
	$helmholtz_t=0;
	$helmholtz_TT=0;
	$helmholtz_st=0;
	//echo ($reduced_density)."::".log($reduced_density)."<BR>";
	for($i=2;$i<=40;$i++){
		$helmholtz+=
			$constant['n'][$i]
			*pow($reduced_density,$constant['I'][$i])
			*pow($inverse_reduced_temp,$constant['J'][$i]);
		$helmholtz_s+=
			$constant['n'][$i]
			*$constant['I'][$i]
			*pow($reduced_density,$constant['I'][$i]-1)
			*pow($inverse_reduced_temp,$constant['J'][$i]);
		$helmholtz_t+=
			$constant['n'][$i]
			*pow($reduced_density,$constant['I'][$i])
			*$constant['J'][$i]
			*pow($inverse_reduced_temp,$constant['J'][$i]-1);                
	}
	
	$constant_R=.461526;
        $region3['temperature']= $temperature;
	$region3['pressure']=$reduced_density*$helmholtz_s*$density*$temperature*$constant_R/1000;
        $region3['density'] = $density;
        $region3['specificVolume'] = 1/$density;
	$region3['internalEnergy']=$inverse_reduced_temp*$helmholtz_t*$temperature*$constant_R;
	$region3['specificEnthalpy']=($inverse_reduced_temp*$helmholtz_t+$reduced_density*$helmholtz_s)*$temperature*$constant_R;
	$region3['specificEntropy']=($inverse_reduced_temp*$helmholtz_t-$helmholtz)*$constant_R;
        $region3['quality'] = NULL;
        $region3['phase'] = "";
        
	return $region3;        
    }       
    
    /**
     * Returns Saturated Pressure for given Temperature
     * Validity Range 273.15 K <= T <= 647.096
     * 
     * @param float $temperature K
     * @return float Pressure MPa 
     */
    function region4($temperature){
            $constant_n[1]=0.11670521452767E+04;
            $constant_n[2]=-0.72421316703206E+06;
            $constant_n[3]=-0.17073846940092E+02;
            $constant_n[4]=0.12020824702470E+05;
            $constant_n[5]=-0.32325550322333E+07;
            $constant_n[6]=0.14915108613530E+02;
            $constant_n[7]=-0.48232657361591E+04;
            $constant_n[8]=0.40511340542057E+06;
            $constant_n[9]=-0.23855557567849E+00;
            $constant_n[10]=0.65017534844798E+03;

            $equ_v=$temperature+$constant_n[9]/($temperature-$constant_n[10]);

            $equ_A=$equ_v*$equ_v+$constant_n[1]*$equ_v+$constant_n[2];
            $equ_B=$constant_n[3]*$equ_v*$equ_v+$constant_n[4]*$equ_v+$constant_n[5];
            $equ_C=$constant_n[6]*$equ_v*$equ_v+$constant_n[7]*$equ_v+$constant_n[8];

            $pressure=pow(2*$equ_C/(-$equ_B+sqrt(pow($equ_B,2)-4*$equ_A*$equ_C)),4);

            return $pressure;
    }

    /**
     * Returns Saturated Temperature for given Pressure   
     * sat_pressure
     * 
     * @param float Pressure MPa
     * @return float Temperature K 
     */
    function backwardRegion4($pressure){
            $constant_n[1]=0.11670521452767E+04;
            $constant_n[2]=-0.72421316703206E+06;
            $constant_n[3]=-0.17073846940092E+02;
            $constant_n[4]=0.12020824702470E+05;
            $constant_n[5]=-0.32325550322333E+07;
            $constant_n[6]=0.14915108613530E+02;
            $constant_n[7]=-0.48232657361591E+04;
            $constant_n[8]=0.40511340542057E+06;
            $constant_n[9]=-0.23855557567849E+00;
            $constant_n[10]=0.65017534844798E+03;

            $equ_SS=pow($pressure,.25);

            $equ_E=$equ_SS*$equ_SS+$constant_n[3]*$equ_SS+$constant_n[6];
            $equ_F=$constant_n[1]*$equ_SS*$equ_SS+$constant_n[4]*$equ_SS+$constant_n[7];
            $equ_G=$constant_n[2]*$equ_SS*$equ_SS+$constant_n[5]*$equ_SS+$constant_n[8];

            $equ_D=2*$equ_G/(-$equ_F-pow((pow($equ_F,2)-4*$equ_E*$equ_G),.5));

            $temperature=($constant_n[10]+$equ_D-pow((pow(($constant_n[10]+$equ_D),2)-4*($constant_n[9]+$constant_n[10]*$equ_D)),.5))/2;

            return $temperature;
    }
}
?>
