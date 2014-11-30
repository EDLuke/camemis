<?php

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/HealthSettingDBAccess.php';
require_once setUserLoacalization();

class StudentHealthDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getListEyeDataInfo() {
        $data = array(
            1 => array("METRE" => "6/60", "FOOT" => "20/200", "DECIMAL" => "0.10", "LOGMAR" => "1.00"),
            2 => array("METRE" => "6/48", "FOOT" => "20/160", "DECIMAL" => "0.125", "LOGMAR" => "0.90"),
            3 => array("METRE" => "6/38", "FOOT" => "20/125", "DECIMAL" => "0.16", "LOGMAR" => "0.80"),
            4 => array("METRE" => "6/30", "FOOT" => "20/100", "DECIMAL" => "0.20", "LOGMAR" => "0.70"),
            5 => array("METRE" => "6/24", "FOOT" => "20/80", "DECIMAL" => "0.25", "LOGMAR" => "0.60"),
            6 => array("METRE" => "6/19", "FOOT" => "20/63", "DECIMAL" => "0.32", "LOGMAR" => "0.50"),
            7 => array("METRE" => "6/15", "FOOT" => "20/50", "DECIMAL" => "0.40", "LOGMAR" => "0.40"),
            8 => array("METRE" => "6/12", "FOOT" => "20/40", "DECIMAL" => "0.50", "LOGMAR" => "0.30"),
            9 => array("METRE" => "6/9.5", "FOOT" => "20/32", "DECIMAL" => "0.63", "LOGMAR" => "0.20"),
            10 => array("METRE" => "6/7.5", "FOOT" => "20/25", "DECIMAL" => "0.80", "LOGMAR" => "0.10"),
            11 => array("METRE" => "6/6.0", "FOOT" => "20/20", "DECIMAL" => "1.00", "LOGMAR" => "0.00"),
            12 => array("METRE" => "6/4.8", "FOOT" => "20/16", "DECIMAL" => "1.25", "LOGMAR" => "-0.10"),
            13 => array("METRE" => "6/3.8", "FOOT" => "20/12.5", "DECIMAL" => "1.60", "LOGMAR" => "-0.20"),
            15 => array("METRE" => "6/3.0", "FOOT" => "20/10", "DECIMAL" => "2.00", "LOGMAR" => "-0.30")
        );

        return $data;
    }

	//@Luke
	//Data is from http://www.cdc.gov/growthcharts/2000growthchart-us.pdf
	//returns L, M, S
	private static function getBMIMeanStd($age, $gender){
	    if($gender == "Male"){
			if($age<24){return array(-2.01118107, 16.57502768, 0.080592465);}
			else if($age<24.5){return array(-1.982373595, 16.54777487, 0.080127429);}
			else if($age<25.5){return array(-1.924100169, 16.49442763, 0.079233994);}
			else if($age<26.5){return array(-1.86549793, 16.44259552, 0.078389356);}
			else if($age<27.5){return array(-1.807261899, 16.3922434, 0.077593501);}
			else if($age<28.5){return array(-1.750118905, 16.34333654, 0.076846462);}
			else if($age<29.5){return array(-1.69481584, 16.29584097, 0.076148308);}
			else if($age<30.5){return array(-1.642106779, 16.24972371, 0.075499126);}
			else if($age<31.5){return array(-1.592744414, 16.20495268, 0.074898994);}
			else if($age<32.5){return array(-1.547442391, 16.16149871, 0.074347997);}
			else if($age<33.5){return array(-1.506902601, 16.11933258, 0.073846139);}
			else if($age<34.5){return array(-1.471770047, 16.07842758, 0.07339337);}
			else if($age<35.5){return array(-1.442628957, 16.03875896, 0.072989551);}
			else if($age<36.5){return array(-1.419991255, 16.00030401, 0.072634432);}
			else if($age<37.5){return array(-1.404277619, 15.96304277, 0.072327649);}
			else if($age<38.5){return array(-1.39586317, 15.92695418, 0.07206864);}
			else if($age<39.5){return array(-1.394935252, 15.89202582, 0.071856805);}
			else if($age<40.5){return array(-1.401671596, 15.85824093, 0.071691278);}
			else if($age<41.5){return array(-1.416100312, 15.82558822, 0.071571093);}
			else if($age<42.5){return array(-1.438164899, 15.79405728, 0.071495113);}
			else if($age<43.5){return array(-1.467669032, 15.76364255, 0.071462106);}
			else if($age<44.5){return array(-1.504376347, 15.73433668, 0.071470646);}
			else if($age<45.5){return array(-1.547942838, 15.70613566, 0.071519218);}
			else if($age<46.5){return array(-1.597896397, 15.67904062, 0.071606277);}
			else if($age<47.5){return array(-1.653732283, 15.65305192, 0.071730167);}
			else if($age<48.5){return array(-1.714869347, 15.62817269, 0.071889214);}
			else if($age<49.5){return array(-1.780673181, 15.604408, 0.072081737);}
			else if($age<50.5){return array(-1.850468473, 15.58176458, 0.072306081);}
			else if($age<51.5){return array(-1.923551865, 15.56025067, 0.072560637);}
			else if($age<52.5){return array(-1.999220429, 15.5398746, 0.07284384);}
			else if($age<53.5){return array(-2.076707178, 15.52064993, 0.073154324);}
			else if($age<54.5){return array(-2.155348017, 15.50258427, 0.073490667);}
			else if($age<55.5){return array(-2.234438552, 15.48568973, 0.073851672);}
			else if($age<56.5){return array(-2.313321723, 15.46997718, 0.074236235);}
			else if($age<57.5){return array(-2.391381273, 15.45545692, 0.074643374);}
			else if($age<58.5){return array(-2.468032491, 15.44213961, 0.075072264);}
			else if($age<59.5){return array(-2.542781541, 15.43003207, 0.075522104);}
			else if($age<60.5){return array(-2.61516595, 15.41914163, 0.07599225);}
			else if($age<61.5){return array(-2.684789516, 15.40947356, 0.076482128);}
			else if($age<62.5){return array(-2.751316949, 15.40103139, 0.076991232);}
			else if($age<63.5){return array(-2.81445945, 15.39381785, 0.077519149);}
			else if($age<64.5){return array(-2.87402476, 15.38783094, 0.07806539);}
			else if($age<65.5){return array(-2.92984048, 15.38306945, 0.078629592);}
			else if($age<66.5){return array(-2.981796828, 15.37952958, 0.079211369);}
			else if($age<67.5){return array(-3.029831343, 15.37720582, 0.079810334);}
			else if($age<68.5){return array(-3.073924224, 15.37609107, 0.080426086);}
			else if($age<69.5){return array(-3.114093476, 15.37617677, 0.081058206);}
			else if($age<70.5){return array(-3.15039004, 15.37745304, 0.081706249);}
			else if($age<71.5){return array(-3.182893018, 15.37990886, 0.082369741);}
			else if($age<72.5){return array(-3.21170511, 15.38353217, 0.083048178);}
			else if($age<73.5){return array(-3.23694834, 15.38831005, 0.083741021);}
			else if($age<74.5){return array(-3.25876011, 15.39422883, 0.0844477);}
			else if($age<75.5){return array(-3.277281546, 15.40127496, 0.085167651);}
			else if($age<76.5){return array(-3.292683774, 15.40943252, 0.085900184);}
			else if($age<77.5){return array(-3.305124073, 15.41868691, 0.086644667);}
			else if($age<78.5){return array(-3.314768951, 15.42902273, 0.087400421);}
			else if($age<79.5){return array(-3.321785992, 15.44042439, 0.088166744);}
			else if($age<80.5){return array(-3.326345795, 15.45287581, 0.088942897);}
			else if($age<81.5){return array(-3.328602731, 15.46636218, 0.089728202);}
			else if($age<82.5){return array(-3.328725277, 15.48086704, 0.090521875);}
			else if($age<83.5){return array(-3.32687018, 15.49637465, 0.091323162);}
			else if($age<84.5){return array(-3.323188896, 15.51286936, 0.092131305);}
			else if($age<85.5){return array(-3.317827016, 15.53033563, 0.092945544);}
			else if($age<86.5){return array(-3.310923871, 15.54875807, 0.093765118);}
			else if($age<87.5){return array(-3.302612272, 15.56812143, 0.09458927);}
			else if($age<88.5){return array(-3.293018361, 15.58841065, 0.095417247);}
			else if($age<89.5){return array(-3.282260813, 15.60961101, 0.096248301);}
			else if($age<90.5){return array(-3.270454609, 15.63170735, 0.097081694);}
			else if($age<91.5){return array(-3.257703616, 15.65468563, 0.097916698);}
			else if($age<92.5){return array(-3.244108214, 15.67853139, 0.098752593);}
			else if($age<93.5){return array(-3.229761713, 15.70323052, 0.099588675);}
			else if($age<94.5){return array(-3.214751287, 15.72876911, 0.100424251);}
			else if($age<95.5){return array(-3.199158184, 15.75513347, 0.101258643);}
			else if($age<96.5){return array(-3.18305795, 15.78231007, 0.102091189);}
			else if($age<97.5){return array(-3.166520664, 15.8102856, 0.102921245);}
			else if($age<98.5){return array(-3.1496103, 15.83904708, 0.103748189);}
			else if($age<99.5){return array(-3.132389637, 15.86858123, 0.104571386);}
			else if($age<100.5){return array(-3.114911153, 15.89887562, 0.105390269);}
			else if($age<101.5){return array(-3.097226399, 15.92991765, 0.106204258);}
			else if($age<102.5){return array(-3.079383079, 15.96169481, 0.107012788);}
			else if($age<103.5){return array(-3.061423765, 15.99419489, 0.107815327);}
			else if($age<104.5){return array(-3.043386071, 16.02740607, 0.108611374);}
			else if($age<105.5){return array(-3.025310003, 16.0613159, 0.109400388);}
			else if($age<106.5){return array(-3.007225737, 16.09591292, 0.110181915);}
			else if($age<107.5){return array(-2.989164598, 16.13118532, 0.110955478);}
			else if($age<108.5){return array(-2.971148225, 16.16712234, 0.111720691);}
			else if($age<109.5){return array(-2.953208047, 16.20371168, 0.112477059);}
			else if($age<110.5){return array(-2.935363951, 16.24094239, 0.1132242);}
			else if($age<111.5){return array(-2.917635157, 16.27880346, 0.113961734);}
			else if($age<112.5){return array(-2.900039803, 16.31728385, 0.114689291);}
			else if($age<113.5){return array(-2.882593796, 16.35637267, 0.115406523);}
			else if($age<114.5){return array(-2.865311266, 16.39605916, 0.116113097);}
			else if($age<115.5){return array(-2.848204697, 16.43633265, 0.116808702);}
			else if($age<116.5){return array(-2.831285052, 16.47718256, 0.117493042);}
			else if($age<117.5){return array(-2.81456189, 16.51859843, 0.11816584);}
			else if($age<118.5){return array(-2.79804347, 16.56056987, 0.118826835);}
			else if($age<119.5){return array(-2.781736856, 16.60308661, 0.119475785);}
			else if($age<120.5){return array(-2.765648008, 16.64613844, 0.120112464);}
			else if($age<121.5){return array(-2.749782197, 16.68971518, 0.120736656);}
			else if($age<122.5){return array(-2.734142443, 16.73380695, 0.121348181);}
			else if($age<123.5){return array(-2.718732873, 16.77840363, 0.121946849);}
			else if($age<124.5){return array(-2.703555506, 16.82349538, 0.122532501);}
			else if($age<125.5){return array(-2.688611957, 16.86907238, 0.123104991);}
			else if($age<126.5){return array(-2.673903164, 16.91512487, 0.123664186);}
			else if($age<127.5){return array(-2.659429443, 16.96164317, 0.124209969);}
			else if($age<128.5){return array(-2.645190534, 17.00861766, 0.124742239);}
			else if($age<129.5){return array(-2.631185649, 17.05603879, 0.125260905);}
			else if($age<130.5){return array(-2.617413511, 17.10389705, 0.125765895);}
			else if($age<131.5){return array(-2.603872392, 17.15218302, 0.126257147);}
			else if($age<132.5){return array(-2.590560148, 17.20088732, 0.126734613);}
			else if($age<133.5){return array(-2.577474253, 17.25000062, 0.12719826);}
			else if($age<134.5){return array(-2.564611831, 17.29951367, 0.127648067);}
			else if($age<135.5){return array(-2.551969684, 17.34941726, 0.128084023);}
			else if($age<136.5){return array(-2.539539972, 17.39970308, 0.128506192);}
			else if($age<137.5){return array(-2.527325681, 17.45036072, 0.128914497);}
			else if($age<138.5){return array(-2.515320235, 17.50138161, 0.129309001);}
			else if($age<139.5){return array(-2.503519447, 17.55275674, 0.129689741);}
			else if($age<140.5){return array(-2.491918934, 17.60447714, 0.130056765);}
			else if($age<141.5){return array(-2.480514136, 17.6565339, 0.130410133);}
			else if($age<142.5){return array(-2.469300331, 17.70891811, 0.130749913);}
			else if($age<143.5){return array(-2.458272656, 17.76162094, 0.131076187);}
			else if($age<144.5){return array(-2.447426113, 17.81463359, 0.131389042);}
			else if($age<145.5){return array(-2.436755595, 17.86794729, 0.131688579);}
			else if($age<146.5){return array(-2.426255887, 17.92155332, 0.131974905);}
			else if($age<147.5){return array(-2.415921689, 17.97544299, 0.132248138);}
			else if($age<148.5){return array(-2.405747619, 18.02960765, 0.132508403);}
			else if($age<149.5){return array(-2.395728233, 18.08403868, 0.132755834);}
			else if($age<150.5){return array(-2.385858029, 18.1387275, 0.132990575);}
			else if($age<151.5){return array(-2.376131459, 18.19366555, 0.133212776);}
			else if($age<152.5){return array(-2.366542942, 18.24884431, 0.133422595);}
			else if($age<153.5){return array(-2.357086871, 18.3042553, 0.133620197);}
			else if($age<154.5){return array(-2.347757625, 18.35989003, 0.133805756);}
			else if($age<155.5){return array(-2.338549576, 18.41574009, 0.133979452);}
			else if($age<156.5){return array(-2.3294571, 18.47179706, 0.13414147);}
			else if($age<157.5){return array(-2.320474586, 18.52805255, 0.134292005);}
			else if($age<158.5){return array(-2.311596446, 18.5844982, 0.134431256);}
			else if($age<159.5){return array(-2.302817124, 18.64112567, 0.134559427);}
			else if($age<160.5){return array(-2.294131107, 18.69792663, 0.134676731);}
			else if($age<161.5){return array(-2.285532933, 18.75489278, 0.134783385);}
			else if($age<162.5){return array(-2.277017201, 18.81201584, 0.134879611);}
			else if($age<163.5){return array(-2.268578584, 18.86928753, 0.134965637);}
			else if($age<164.5){return array(-2.260211837, 18.92669959, 0.135041695);}
			else if($age<165.5){return array(-2.251911809, 18.98424378, 0.135108024);}
			else if($age<166.5){return array(-2.243673453, 19.04191185, 0.135164867);}
			else if($age<167.5){return array(-2.235491842, 19.09969557, 0.135212469);}
			else if($age<168.5){return array(-2.227362173, 19.15758672, 0.135251083);}
			else if($age<169.5){return array(-2.21927979, 19.21557707, 0.135280963);}
			else if($age<170.5){return array(-2.211240187, 19.27365839, 0.135302371);}
			else if($age<171.5){return array(-2.203239029, 19.33182247, 0.135315568);}
			else if($age<172.5){return array(-2.195272161, 19.39006106, 0.135320824);}
			else if($age<173.5){return array(-2.187335625, 19.44836594, 0.135318407);}
			else if($age<174.5){return array(-2.179425674, 19.50672885, 0.135308594);}
			else if($age<175.5){return array(-2.171538789, 19.56514153, 0.135291662);}
			else if($age<176.5){return array(-2.163671689, 19.62359571, 0.135267891);}
			else if($age<177.5){return array(-2.155821357, 19.6820831, 0.135237567);}
			else if($age<178.5){return array(-2.147985046, 19.74059538, 0.135200976);}
			else if($age<179.5){return array(-2.140160305, 19.7991242, 0.135158409);}
			else if($age<180.5){return array(-2.132344989, 19.85766121, 0.135110159);}
			else if($age<181.5){return array(-2.124537282, 19.916198, 0.135056522);}
			else if($age<182.5){return array(-2.116735712, 19.97472615, 0.134997797);}
			else if($age<183.5){return array(-2.108939167, 20.03323719, 0.134934285);}
			else if($age<184.5){return array(-2.10114692, 20.09172262, 0.134866291);}
			else if($age<185.5){return array(-2.093358637, 20.15017387, 0.134794121);}
			else if($age<186.5){return array(-2.085574403, 20.20858236, 0.134718085);}
			else if($age<187.5){return array(-2.077794735, 20.26693944, 0.134638494);}
			else if($age<188.5){return array(-2.070020599, 20.32523642, 0.134555663);}
			else if($age<189.5){return array(-2.062253431, 20.38346455, 0.13446991);}
			else if($age<190.5){return array(-2.054495145, 20.44161501, 0.134381553);}
			else if($age<191.5){return array(-2.046748156, 20.49967894, 0.134290916);}
			else if($age<192.5){return array(-2.039015385, 20.5576474, 0.134198323);}
			else if($age<193.5){return array(-2.031300282, 20.6155114, 0.134104101);}
			else if($age<194.5){return array(-2.023606828, 20.67326189, 0.134008581);}
			else if($age<195.5){return array(-2.015942013, 20.73088905, 0.133912066);}
			else if($age<196.5){return array(-2.008305745, 20.7883851, 0.133814954);}
			else if($age<197.5){return array(-2.000706389, 20.84574003, 0.133717552);}
			else if($age<198.5){return array(-1.993150137, 20.90294449, 0.1336202);}
			else if($age<199.5){return array(-1.985643741, 20.95998909, 0.133523244);}
			else if($age<200.5){return array(-1.97819451, 21.01686433, 0.133427032);}
			else if($age<201.5){return array(-1.970810308, 21.07356067, 0.133331914);}
			else if($age<202.5){return array(-1.96349954, 21.1300685, 0.133238245);}
			else if($age<203.5){return array(-1.956271141, 21.18637813, 0.133146383);}
			else if($age<204.5){return array(-1.949134561, 21.24247982, 0.13305669);}
			else if($age<205.5){return array(-1.942099744, 21.29836376, 0.132969531);}
			else if($age<206.5){return array(-1.935177101, 21.35402009, 0.132885274);}
			else if($age<207.5){return array(-1.92837748, 21.40943891, 0.132804292);}
			else if($age<208.5){return array(-1.921712136, 21.46461026, 0.132726962);}
			else if($age<209.5){return array(-1.915192685, 21.51952414, 0.132653664);}
			else if($age<210.5){return array(-1.908831065, 21.57417053, 0.132584784);}
			else if($age<211.5){return array(-1.902639482, 21.62853937, 0.132520711);}
			else if($age<212.5){return array(-1.896630358, 21.68262062, 0.132461838);}
			else if($age<213.5){return array(-1.890816268, 21.73640419, 0.132408563);}
			else if($age<214.5){return array(-1.885209876, 21.78988003, 0.132361289);}
			else if($age<215.5){return array(-1.879823505, 21.84303819, 0.132320427);}
			else if($age<216.5){return array(-1.874670324, 21.8958685, 0.132286382);}
			else if($age<217.5){return array(-1.869760299, 21.94836168, 0.1322596);}
			else if($age<218.5){return array(-1.865113245, 22.00050569, 0.132240418);}
			else if($age<219.5){return array(-1.860734944, 22.05229242, 0.13222933);}
			else if($age<220.5){return array(-1.85663384, 22.10371305, 0.132226801);}
			else if($age<221.5){return array(-1.852827186, 22.15475603, 0.132233201);}
			else if($age<222.5){return array(-1.849323204, 22.20541249, 0.132248993);}
			else if($age<223.5){return array(-1.846131607, 22.255673, 0.132274625);}
			else if($age<224.5){return array(-1.843261294, 22.30552831, 0.132310549);}
			else if($age<225.5){return array(-1.840720248, 22.3549693, 0.132357221);}
			else if($age<226.5){return array(-1.83851544, 22.40398706, 0.132415103);}
			else if($age<227.5){return array(-1.83665586, 22.45257182, 0.132484631);}
			else if($age<228.5){return array(-1.835138046, 22.50071778, 0.132566359);}
			else if($age<229.5){return array(-1.833972004, 22.54841437, 0.132660699);}
			else if($age<230.5){return array(-1.833157751, 22.59565422, 0.132768153);}
			else if($age<231.5){return array(-1.83269562, 22.64242956, 0.132889211);}
			else if($age<232.5){return array(-1.832584342, 22.68873292, 0.133024368);}
			else if($age<233.5){return array(-1.832820974, 22.73455713, 0.133174129);}
			else if($age<234.5){return array(-1.833400825, 22.7798953, 0.133338999);}
			else if($age<235.5){return array(-1.834317405, 22.82474087, 0.133519496);}
			else if($age<236.5){return array(-1.83555752, 22.86908912, 0.133716192);}
			else if($age<237.5){return array(-1.837119466, 22.91293151, 0.133929525);}
			else if($age<238.5){return array(-1.838987063, 22.95626373, 0.134160073);}
			else if($age<239.5){return array(-1.841146139, 22.99908062, 0.134408381);}
			else if($age<240){return array(-1.84233016, 23.02029424, 0.134539365);}
			else if($age<240.5){return array(-1.843580575, 23.04137734, 0.134675001);}
			}else{
			if($age<24){return array(-0.98660853, 16.42339664, 0.085451785);}
			else if($age<24.5){return array(-1.024496827, 16.38804056, 0.085025838);}
			else if($age<25.5){return array(-1.102698353, 16.3189719, 0.084214052);}
			else if($age<26.5){return array(-1.18396635, 16.25207985, 0.083455124);}
			else if($age<27.5){return array(-1.268071036, 16.18734669, 0.082748284);}
			else if($age<28.5){return array(-1.354751525, 16.12475448, 0.082092737);}
			else if($age<29.5){return array(-1.443689692, 16.06428762, 0.081487717);}
			else if($age<30.5){return array(-1.53454192, 16.00593001, 0.080932448);}
			else if($age<31.5){return array(-1.626928093, 15.94966631, 0.080426175);}
			else if($age<32.5){return array(-1.720434829, 15.89548197, 0.079968176);}
			else if($age<33.5){return array(-1.814635262, 15.84336179, 0.079557735);}
			else if($age<34.5){return array(-1.909076262, 15.79329146, 0.079194187);}
			else if($age<35.5){return array(-2.003296102, 15.7452564, 0.078876895);}
			else if($age<36.5){return array(-2.096828937, 15.69924188, 0.078605255);}
			else if($age<37.5){return array(-2.189211877, 15.65523282, 0.078378696);}
			else if($age<38.5){return array(-2.279991982, 15.61321371, 0.078196674);}
			else if($age<39.5){return array(-2.368732949, 15.57316843, 0.078058667);}
			else if($age<40.5){return array(-2.455021314, 15.53508019, 0.077964169);}
			else if($age<41.5){return array(-2.538471972, 15.49893145, 0.077912684);}
			else if($age<42.5){return array(-2.618732901, 15.46470384, 0.077903716);}
			else if($age<43.5){return array(-2.695488973, 15.43237817, 0.077936763);}
			else if($age<44.5){return array(-2.768464816, 15.40193436, 0.078011309);}
			else if($age<45.5){return array(-2.837426693, 15.37335154, 0.078126817);}
			else if($age<46.5){return array(-2.902178205, 15.34660842, 0.078282739);}
			else if($age<47.5){return array(-2.962580386, 15.32168181, 0.078478449);}
			else if($age<48.5){return array(-3.018521987, 15.29854897, 0.078713325);}
			else if($age<49.5){return array(-3.069936555, 15.27718618, 0.078986694);}
			else if($age<50.5){return array(-3.116795864, 15.2575692, 0.079297841);}
			else if($age<51.5){return array(-3.159107331, 15.23967338, 0.079646006);}
			else if($age<52.5){return array(-3.196911083, 15.22347371, 0.080030389);}
			else if($age<53.5){return array(-3.230276759, 15.20894491, 0.080450145);}
			else if($age<54.5){return array(-3.259300182, 15.19606152, 0.080904391);}
			else if($age<55.5){return array(-3.284099963, 15.18479799, 0.081392203);}
			else if($age<56.5){return array(-3.30481415, 15.17512871, 0.081912623);}
			else if($age<57.5){return array(-3.321596954, 15.16702811, 0.082464661);}
			else if($age<58.5){return array(-3.334615646, 15.16047068, 0.083047295);}
			else if($age<59.5){return array(-3.344047622, 15.15543107, 0.083659478);}
			else if($age<60.5){return array(-3.35007771, 15.15188405, 0.084300139);}
			else if($age<61.5){return array(-3.352893805, 15.14980479, 0.0849682);}
			else if($age<62.5){return array(-3.352691376, 15.14916825, 0.085662539);}
			else if($age<63.5){return array(-3.34966438, 15.14994984, 0.086382035);}
			else if($age<64.5){return array(-3.343998803, 15.15212585, 0.087125591);}
			else if($age<65.5){return array(-3.335889574, 15.15567186, 0.087892047);}
			else if($age<66.5){return array(-3.325522491, 15.16056419, 0.088680264);}
			else if($age<67.5){return array(-3.31307846, 15.16677947, 0.089489106);}
			else if($age<68.5){return array(-3.298732648, 15.17429464, 0.090317434);}
			else if($age<69.5){return array(-3.282653831, 15.18308694, 0.091164117);}
			else if($age<70.5){return array(-3.265003896, 15.1931339, 0.092028028);}
			else if($age<71.5){return array(-3.245937506, 15.20441335, 0.092908048);}
			else if($age<72.5){return array(-3.225606516, 15.21690296, 0.093803033);}
			else if($age<73.5){return array(-3.204146115, 15.2305815, 0.094711916);}
			else if($age<74.5){return array(-3.181690237, 15.24542745, 0.095633595);}
			else if($age<75.5){return array(-3.158363475, 15.26141966, 0.096566992);}
			else if($age<76.5){return array(-3.134282833, 15.27853728, 0.097511046);}
			else if($age<77.5){return array(-3.109557879, 15.29675967, 0.09846471);}
			else if($age<78.5){return array(-3.084290931, 15.31606644, 0.099426955);}
			else if($age<79.5){return array(-3.058577292, 15.33643745, 0.100396769);}
			else if($age<80.5){return array(-3.032505499, 15.35785274, 0.101373159);}
			else if($age<81.5){return array(-3.0061576, 15.38029261, 0.10235515);}
			else if($age<82.5){return array(-2.979609448, 15.40373754, 0.103341788);}
			else if($age<83.5){return array(-2.952930993, 15.42816819, 0.104332139);}
			else if($age<84.5){return array(-2.926186592, 15.45356545, 0.105325289);}
			else if($age<85.5){return array(-2.899435307, 15.47991037, 0.106320346);}
			else if($age<86.5){return array(-2.872731211, 15.50718419, 0.10731644);}
			else if($age<87.5){return array(-2.846123683, 15.53536829, 0.108312721);}
			else if($age<88.5){return array(-2.819657704, 15.56444426, 0.109308364);}
			else if($age<89.5){return array(-2.793374145, 15.5943938, 0.110302563);}
			else if($age<90.5){return array(-2.767310047, 15.6251988, 0.111294537);}
			else if($age<91.5){return array(-2.741498897, 15.65684126, 0.112283526);}
			else if($age<92.5){return array(-2.715970894, 15.68930333, 0.113268793);}
			else if($age<93.5){return array(-2.690753197, 15.7225673, 0.114249622);}
			else if($age<94.5){return array(-2.665870146, 15.75661555, 0.115225321);}
			else if($age<95.5){return array(-2.641343436, 15.79143062, 0.116195218);}
			else if($age<96.5){return array(-2.617192204, 15.82699517, 0.117158667);}
			else if($age<97.5){return array(-2.593430614, 15.86329241, 0.118115073);}
			else if($age<98.5){return array(-2.570076037, 15.90030484, 0.119063807);}
			else if($age<99.5){return array(-2.547141473, 15.93801545, 0.12000429);}
			else if($age<100.5){return array(-2.524635245, 15.97640787, 0.120935994);}
			else if($age<101.5){return array(-2.502569666, 16.01546483, 0.121858355);}
			else if($age<102.5){return array(-2.48095189, 16.05516984, 0.12277087);}
			else if($age<103.5){return array(-2.459785573, 16.09550688, 0.123673085);}
			else if($age<104.5){return array(-2.439080117, 16.13645881, 0.124564484);}
			else if($age<105.5){return array(-2.418838304, 16.17800955, 0.125444639);}
			else if($age<106.5){return array(-2.399063683, 16.22014281, 0.126313121);}
			else if($age<107.5){return array(-2.379756861, 16.26284277, 0.127169545);}
			else if($age<108.5){return array(-2.360920527, 16.30609316, 0.128013515);}
			else if($age<109.5){return array(-2.342557728, 16.34987759, 0.128844639);}
			else if($age<110.5){return array(-2.324663326, 16.39418118, 0.129662637);}
			else if($age<111.5){return array(-2.307240716, 16.43898741, 0.130467138);}
			else if($age<112.5){return array(-2.290287663, 16.48428082, 0.131257852);}
			else if($age<113.5){return array(-2.273803847, 16.53004554, 0.132034479);}
			else if($age<114.5){return array(-2.257782149, 16.57626713, 0.132796819);}
			else if($age<115.5){return array(-2.242227723, 16.62292864, 0.133544525);}
			else if($age<116.5){return array(-2.227132805, 16.67001572, 0.134277436);}
			else if($age<117.5){return array(-2.212495585, 16.71751288, 0.134995324);}
			else if($age<118.5){return array(-2.19831275, 16.76540496, 0.135697996);}
			else if($age<119.5){return array(-2.184580762, 16.81367689, 0.136385276);}
			else if($age<120.5){return array(-2.171295888, 16.86231366, 0.137057004);}
			else if($age<121.5){return array(-2.158454232, 16.91130036, 0.137713039);}
			else if($age<122.5){return array(-2.146051754, 16.96062216, 0.138353254);}
			else if($age<123.5){return array(-2.134084303, 17.0102643, 0.138977537);}
			else if($age<124.5){return array(-2.122547629, 17.06021213, 0.139585795);}
			else if($age<125.5){return array(-2.111437411, 17.11045106, 0.140177947);}
			else if($age<126.5){return array(-2.100749266, 17.16096656, 0.140753927);}
			else if($age<127.5){return array(-2.090478774, 17.21174424, 0.141313686);}
			else if($age<128.5){return array(-2.080621484, 17.26276973, 0.141857186);}
			else if($age<129.5){return array(-2.071172932, 17.31402878, 0.142384404);}
			else if($age<130.5){return array(-2.062128649, 17.3655072, 0.142895332);}
			else if($age<131.5){return array(-2.053484173, 17.4171909, 0.143389972);}
			else if($age<132.5){return array(-2.045235058, 17.46906585, 0.143868341);}
			else if($age<133.5){return array(-2.03737688, 17.52111811, 0.144330469);}
			else if($age<134.5){return array(-2.029906684, 17.57333347, 0.144776372);}
			else if($age<135.5){return array(-2.022817914, 17.62569869, 0.145206138);}
			else if($age<136.5){return array(-2.016107084, 17.67819987, 0.145619819);}
			else if($age<137.5){return array(-2.009769905, 17.7308234, 0.146017491);}
			else if($age<138.5){return array(-2.003802134, 17.78355575, 0.146399239);}
			else if($age<139.5){return array(-1.998199572, 17.83638347, 0.146765161);}
			else if($age<140.5){return array(-1.992958064, 17.88929321, 0.147115364);}
			else if($age<141.5){return array(-1.988073505, 17.94227168, 0.147449967);}
			else if($age<142.5){return array(-1.983541835, 17.9953057, 0.147769097);}
			else if($age<143.5){return array(-1.979359041, 18.04838216, 0.148072891);}
			else if($age<144.5){return array(-1.975521156, 18.10148804, 0.148361495);}
			else if($age<145.5){return array(-1.972024258, 18.15461039, 0.148635067);}
			else if($age<146.5){return array(-1.968864465, 18.20773639, 0.148893769);}
			else if($age<147.5){return array(-1.966037938, 18.26085325, 0.149137776);}
			else if($age<148.5){return array(-1.963540872, 18.31394832, 0.14936727);}
			else if($age<149.5){return array(-1.961369499, 18.36700902, 0.149582439);}
			else if($age<150.5){return array(-1.959520079, 18.42002284, 0.149783482);}
			else if($age<151.5){return array(-1.9579889, 18.47297739, 0.149970604);}
			else if($age<152.5){return array(-1.956772271, 18.52586035, 0.15014402);}
			else if($age<153.5){return array(-1.95586652, 18.57865951, 0.15030395);}
			else if($age<154.5){return array(-1.955267984, 18.63136275, 0.150450621);}
			else if($age<155.5){return array(-1.954973011, 18.68395801, 0.15058427);}
			else if($age<156.5){return array(-1.954977947, 18.73643338, 0.150705138);}
			else if($age<157.5){return array(-1.955279136, 18.788777, 0.150813475);}
			else if($age<158.5){return array(-1.955872909, 18.84097713, 0.150909535);}
			else if($age<159.5){return array(-1.956755579, 18.89302212, 0.150993582);}
			else if($age<160.5){return array(-1.957923436, 18.94490041, 0.151065883);}
			else if($age<161.5){return array(-1.959372737, 18.99660055, 0.151126714);}
			else if($age<162.5){return array(-1.9610997, 19.04811118, 0.151176355);}
			else if($age<163.5){return array(-1.963100496, 19.09942105, 0.151215094);}
			else if($age<164.5){return array(-1.96537124, 19.15051899, 0.151243223);}
			else if($age<165.5){return array(-1.967907983, 19.20139397, 0.151261042);}
			else if($age<166.5){return array(-1.970706706, 19.25203503, 0.151268855);}
			else if($age<167.5){return array(-1.973763307, 19.30243131, 0.151266974);}
			else if($age<168.5){return array(-1.977073595, 19.35257209, 0.151255713);}
			else if($age<169.5){return array(-1.980633277, 19.40244671, 0.151235395);}
			else if($age<170.5){return array(-1.984437954, 19.45204465, 0.151206347);}
			else if($age<171.5){return array(-1.988483106, 19.50135548, 0.151168902);}
			else if($age<172.5){return array(-1.992764085, 19.55036888, 0.151123398);}
			else if($age<173.5){return array(-1.997276103, 19.59907464, 0.15107018);}
			else if($age<174.5){return array(-2.002014224, 19.64746266, 0.151009595);}
			else if($age<175.5){return array(-2.00697335, 19.69552294, 0.150942);}
			else if($age<176.5){return array(-2.012148213, 19.7432456, 0.150867753);}
			else if($age<177.5){return array(-2.017533363, 19.79062086, 0.150787221);}
			else if($age<178.5){return array(-2.023123159, 19.83763907, 0.150700774);}
			else if($age<179.5){return array(-2.028911755, 19.88429066, 0.150608788);}
			else if($age<180.5){return array(-2.034893091, 19.9305662, 0.150511645);}
			else if($age<181.5){return array(-2.041060881, 19.97645636, 0.150409731);}
			else if($age<182.5){return array(-2.047408604, 20.02195192, 0.15030344);}
			else if($age<183.5){return array(-2.05392949, 20.06704377, 0.150193169);}
			else if($age<184.5){return array(-2.060616513, 20.11172291, 0.150079322);}
			else if($age<185.5){return array(-2.067462375, 20.15598047, 0.149962308);}
			else if($age<186.5){return array(-2.074459502, 20.19980767, 0.14984254);}
			else if($age<187.5){return array(-2.081600029, 20.24319586, 0.149720441);}
			else if($age<188.5){return array(-2.088875793, 20.28613648, 0.149596434);}
			else if($age<189.5){return array(-2.096278323, 20.32862109, 0.149470953);}
			else if($age<190.5){return array(-2.103798828, 20.37064138, 0.149344433);}
			else if($age<191.5){return array(-2.111428194, 20.41218911, 0.149217319);}
			else if($age<192.5){return array(-2.119156972, 20.45325617, 0.14909006);}
			else if($age<193.5){return array(-2.126975375, 20.49383457, 0.14896311);}
			else if($age<194.5){return array(-2.134873266, 20.5339164, 0.148836931);}
			else if($age<195.5){return array(-2.142840157, 20.57349387, 0.148711989);}
			else if($age<196.5){return array(-2.150865204, 20.61255929, 0.148588757);}
			else if($age<197.5){return array(-2.158937201, 20.65110506, 0.148467715);}
			else if($age<198.5){return array(-2.167044578, 20.6891237, 0.148349348);}
			else if($age<199.5){return array(-2.175176987, 20.72660728, 0.14823412);}
			else if($age<200.5){return array(-2.183317362, 20.76355011, 0.148122614);}
			else if($age<201.5){return array(-2.191457792, 20.79994337, 0.148015249);}
			else if($age<202.5){return array(-2.199583649, 20.83578051, 0.147912564);}
			else if($age<203.5){return array(-2.207681525, 20.87105449, 0.147815078);}
			else if($age<204.5){return array(-2.215737645, 20.90575839, 0.147723315);}
			else if($age<205.5){return array(-2.223739902, 20.93988477, 0.147637768);}
			else if($age<206.5){return array(-2.231667995, 20.97342858, 0.147559083);}
			else if($age<207.5){return array(-2.239511942, 21.00638171, 0.147487716);}
			else if($age<208.5){return array(-2.247257081, 21.0387374, 0.14742421);}
			else if($age<209.5){return array(-2.254885145, 21.07048996, 0.147369174);}
			else if($age<210.5){return array(-2.26238209, 21.10163241, 0.147323144);}
			else if($age<211.5){return array(-2.269731517, 21.13215845, 0.147286698);}
			else if($age<212.5){return array(-2.276917229, 21.16206171, 0.147260415);}
			else if($age<213.5){return array(-2.283925442, 21.1913351, 0.147244828);}
			else if($age<214.5){return array(-2.290731442, 21.21997472, 0.147240683);}
			else if($age<215.5){return array(-2.29732427, 21.24797262, 0.147248467);}
			else if($age<216.5){return array(-2.303687802, 21.27532239, 0.14726877);}
			else if($age<217.5){return array(-2.309799971, 21.30201933, 0.147302299);}
			else if($age<218.5){return array(-2.315651874, 21.32805489, 0.147349514);}
			else if($age<219.5){return array(-2.32121731, 21.35342563, 0.147411215);}
			else if($age<220.5){return array(-2.326481911, 21.37812462, 0.147487979);}
			else if($age<221.5){return array(-2.331428139, 21.40214589, 0.147580453);}
			else if($age<222.5){return array(-2.336038473, 21.42548351, 0.147689289);}
			else if($age<223.5){return array(-2.34029545, 21.44813156, 0.14781515);}
			else if($age<224.5){return array(-2.344181703, 21.47008412, 0.147958706);}
			else if($age<225.5){return array(-2.34768, 21.49133529, 0.148120633);}
			else if($age<226.5){return array(-2.350773286, 21.51187918, 0.148301619);}
			else if($age<227.5){return array(-2.353444725, 21.53170989, 0.148502355);}
			else if($age<228.5){return array(-2.355677743, 21.55082155, 0.148723546);}
			else if($age<229.5){return array(-2.35745607, 21.56920824, 0.148965902);}
			else if($age<230.5){return array(-2.358763788, 21.58686406, 0.149230142);}
			else if($age<231.5){return array(-2.359585369, 21.60378309, 0.149516994);}
			else if($age<232.5){return array(-2.359905726, 21.61995939, 0.149827195);}
			else if($age<233.5){return array(-2.359710258, 21.635387, 0.150161492);}
			else if($age<234.5){return array(-2.358980464, 21.65006126, 0.150520734);}
			else if($age<235.5){return array(-2.357714508, 21.6639727, 0.150905439);}
			else if($age<236.5){return array(-2.355892424, 21.67711736, 0.151316531);}
			else if($age<237.5){return array(-2.353501353, 21.68948935, 0.151754808);}
			else if($age<238.5){return array(-2.350528726, 21.70108288, 0.152221086);}
			else if($age<239.5){return array(-2.346962247, 21.71189225, 0.152716206);}
			else if($age<240){return array(-2.34495843, 21.71699934, 0.152974718);}
			else if($age<240.5){return array(-2.342796948, 21.72190973, 0.153240872);}
			}

	    
	}
	
    public static function getEyeData($index) {

        $result = self::getListEyeDataInfo();
        $facette = isset($result[$index]) ? (object) $result[$index] : "";

        $output = "";
        if ($facette) {
            $output = "&bull; " . "Metre: " . $facette->METRE;
            $output .= "<br>&bull; " . "Foot: " . $facette->FOOT;
            $output .= "<br>&bull; " . "Decimal: " . $facette->DECIMAL;
            $output .= "<br>&bull; " . "LogMAR: " . $facette->LOGMAR;
        }

        return $output;
    }

    public static function listHealthValuesOfEye($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";

        $facette = self::findStudentHealth($setId, $studentId, false);

        $data = array();
        $i = 0;
        if (self::getListEyeDataInfo()) {
            foreach (self::getListEyeDataInfo() as $key => $value) {
                $data[$i]["ID"] = $key;
                if ($facette)
                    $data[$i]["EYE_LEFT"] = ($key == $facette->EYE_LEFT) ? 1 : 0;
                if ($facette)
                    $data[$i]["EYE_RIGHT"] = ($key == $facette->EYE_RIGHT) ? 1 : 0;
                $data[$i]["FOOT"] = $value["FOOT"];
                $data[$i]["METRE"] = $value["METRE"];
                $data[$i]["DECIMAL"] = $value["DECIMAL"];
                $data[$i]["LOGMAR"] = $value["LOGMAR"];
                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function createStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $target = isset($params["target"]) ? addText($params["target"]) : "DYNAMIC";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";

        $SAVEDATA['OBJECT_TYPE'] = $target;

        if (isset($params["DESCRIPTION"])) {
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);
        }

        if (isset($params["NEXT_VISIT_TIME"])) {
            $SAVEDATA['NEXT_VISIT_TIME'] = timeStrToSecond($params["NEXT_VISIT_TIME"]);
        }

        if (isset($params["WEIGHT"])) {
            $SAVEDATA['WEIGHT'] = addText($params["WEIGHT"]);
        }

        if (isset($params["HEIGHT"])) {
            $SAVEDATA['HEIGHT'] = addText($params["HEIGHT"]);
        }

        if (isset($params["PULSE"])) {
            $SAVEDATA['PULSE'] = addText($params["PULSE"]);
        }

        if (isset($params["BLOOD_PRESSURE_SYSTOLIC"])) {
            $SAVEDATA['BLOOD_PRESSURE_SYSTOLIC'] = addText($params["BLOOD_PRESSURE_SYSTOLIC"]);
        }

        if (isset($params["BLOOD_PRESSURE_DIASTOLIC"])) {
            $SAVEDATA['BLOOD_PRESSURE_DIASTOLIC'] = addText($params["BLOOD_PRESSURE_DIASTOLIC"]);
        }

        if (isset($params["TEMPERATURE"])) {
            $SAVEDATA['TEMPERATURE'] = addText($params["TEMPERATURE"]);
        }
        	
   		if (isset($params["BMI_Z_SCORE"])) {
            $SAVEDATA['BMI_Z_SCORE'] = addText($params["BMI_Z_SCORE"]);
        }

        if (isset($params["WT_Z_SCORE"])) {
            $SAVEDATA['WT_Z_SCORE'] = addText($params["WT_Z_SCORE"]);
        }

        if (isset($params["HT_Z_SCORE"])) {
            $SAVEDATA['HT_Z_SCORE'] = addText($params["HT_Z_SCORE"]);
        }

        if (isset($params["LOCATION"])) {
            $SAVEDATA['LOCATION'] = addText($params["LOCATION"]);
        }

        if (isset($params["OTHER"])) {
            $SAVEDATA['OTHER'] = addText($params["OTHER"]);
        }

        if (isset($params["FULL_NAME"])) {
            $SAVEDATA['DOCTOR_NAME'] = addText($params["FULL_NAME"]);
        }

        if (isset($params["NEXT_VISIT"])) {
            if ($params["NEXT_VISIT"])
                $SAVEDATA['NEXT_VISIT'] = setDate2DB($params["NEXT_VISIT"]);
        }

        if (isset($params["MEDICAL_DATE"])) {
            $SAVEDATA['MEDICAL_DATE'] = setDate2DB($params["MEDICAL_DATE"]);
        }

        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        //error_log($SQL);
        $entries = self::dbAccess()->fetchAll($SQL);

        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();

        if ($entries) {
            foreach ($entries as $value) {
                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? addText($params["RADIOBOX_" . $value->ID . ""]) : "";
                if ($CHECKBOX) {
                    $CHECKBOX_DATA[$value->ID] = $CHECKBOX;
                }
                if ($RADIOBOX) {
                    $RADIOBOX_DATA[$value->ID] = $RADIOBOX;
                }
            }
        }

        if ($CHECKBOX_DATA) {
            $SAVEDATA['CHECKBOX_DATA'] = implode(",", $CHECKBOX_DATA);
        }

        if ($RADIOBOX_DATA) {
            $SAVEDATA['RADIOBOX_DATA'] = implode(",", $RADIOBOX_DATA);
        }

        $SAVEDATA['DATA_ITEMS'] = implode(",", $RADIOBOX_DATA + $CHECKBOX_DATA);

        if ($setId == "new") {
            $SAVEDATA['MEDICAL_SETTING_ID'] = $settingId;
            $SAVEDATA['STUDENT_ID'] = $objectId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_student_medical', $SAVEDATA);
            $setId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE[] = "ID = '" . $setId . "'";
            self::dbAccess()->update('t_student_medical', $SAVEDATA, $WHERE);
        }

        $facette = self::findStudentHealth($setId, $objectId, false);

        if ($facette) {
            self::calculationBMI($facette->ID);
			self::calculationZScore($facette->ID); //@Luke
        }
        return array(
            "success" => true
            , "setId" => $setId
        );
    }

    public static function deleteStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";

        self::dbAccess()->delete('t_student_medical', array(
            "ID='" . $setId . "'"
            , "STUDENT_ID='" . $objectId . "'"
            , "MEDICAL_SETTING_ID='" . $settingId . "'"
        ));

        return array(
            "success" => true
        );
    }

    public static function findStudentHealth($Id, $studentId = false, $settingId = false) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_medical", array('*'));
        $SQL->where("ID = ?", $Id);
        if ($settingId)
            $SQL->where("MEDICAL_SETTING_ID='" . $settingId . "'");
        if ($studentId)
            $SQL->where("STUDENT_ID='" . $studentId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }
	
	//@Luke
    //could be modified to retrieve other information from student data
    public static function findStudentAgeGender($Id){
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student", array('*'));
        $SQL->where("ID = ?", $Id);
        error_log($SQL->__toString());
	
		$facette = self::dbAccess()->fetchRow($SQL);
        $now = new DateTime();
        $birth = new DateTime($facette->DATE_BIRTH);
        $age = $birth->diff($now);
        $ageDecimal = intval($age->days) / 365.5 / 12;
        return array((string)($facette->GENDER), $ageDecimal);
    }

    //@veasna
    public static function sqlStudentHealth($params) {

        $codeId = isset($params["codeId"]) ? addText($params["codeId"]) : "";
        $studentSchoolCode = isset($params["studentSchoolCode"]) ? addText($params["studentSchoolCode"]) : "";
        $firstName = isset($params["firstName"]) ? addText($params["firstName"]) : "";
        $lastName = isset($params["lastName"]) ? addText($params["lastName"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $bmiStatus = isset($params["bmiStatus"]) ? addText($params["bmiStatus"]) : "";
        $start = isset($params["start"]) ? $params["start"] : "";
        $end = isset($params["end"]) ? $params["end"] : "";
        $nextVisit = isset($params["nextVisit"]) ? $params["nextVisit"] : "";

        $health_type = isset($params["health_type"]) ? $params["health_type"] : "";

        $SELECTION_C = array(
            "ID AS STUDENT_ID"
            , "CODE AS CODE"
            , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
            , "ACADEMIC_TYPE AS ACADEMIC_TYPE"
            , "CONCAT(LASTNAME,', ',FIRSTNAME) AS NAME"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "GENDER AS GENDER"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_medical'), array('*'));
        $SQL->joinLeft(array('B' => 't_health_setting'), 'B.ID=A.MEDICAL_SETTING_ID', array());
        $SQL->joinLeft(array('C' => 't_student'), 'C.ID=A.STUDENT_ID', $SELECTION_C);

        if ($health_type)
            $SQL->where("B.OBJECT_INDEX = '" . $health_type . "'");
        if ($start && $end)
            $SQL->where("A.MEDICAL_DATE >='" . $start . "' AND A.MEDICAL_DATE <='" . $end . "'");
        if ($nextVisit)
            $SQL->where("A.START_DATE <= '" . $nextVisit . "' AND A.END_DATE >= '" . $nextVisit . "'");
        if ($bmiStatus)
            $SQL->where("A.STATUS = '" . $bmiStatus . "'");
        if ($codeId)
            $SQL->where("C.CODE LIKE '" . $codeId . "%'");
        if ($studentSchoolCode)
            $SQL->where("C.STUDENT_SCHOOL_ID LIKE '" . $studentSchoolCode . "%'");
        if ($firstName)
            $SQL->where("C.FIRSTNAME LIKE '" . $firstName . "%'");
        if ($lastName)
            $SQL->where("C.LASTNAME LIKE '" . $lastName . "%'");
        if ($gender)
            $SQL->where("C.GENDER ='" . $gender . "'");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function searchStudentHealth($encrypParams, $isJson = true) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";
        $health_type = isset($params["health_type"]) ? $params["health_type"] : "";

        $result = self::sqlStudentHealth($params);
        $data = array();
        $i = 0;
        foreach ($result as $value) {

            $data[$i]["MEDICAL_DATE"] = getShowDate($value->MEDICAL_DATE);
            $data[$i]["CODE"] = $value->CODE;
            $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
            $data[$i]["ID"] = $value->ID;
            $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;

            if (!SchoolDBAccess::displayPersonNameInGrid()) {
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
            } else {
                $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
            }

            switch ($health_type) {
                
                case "MEDICAL_VISIT":
                    if (getShowDate($value->NEXT_VISIT) != "---") {
                        $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT) . " " . secondToHour($value->NEXT_VISIT_TIME);
                    } else {
                        $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT);
                    }

                    $data[$i]["FULL_NAME"] = setShowText($value->DOCTOR_NAME);
                    $data[$i]["VISITED_BY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_BY");
                    $data[$i]["REASON"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_REASON");
                    $data[$i]["LOCATION"] = setShowText($value->LOCATION);
                    $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                    $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                    $data[$i]["PULSE"] = setShowText($value->PULSE);
                    $data[$i]["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($value->BLOOD_PRESSURE_SYSTOLIC);
                    $data[$i]["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($value->BLOOD_PRESSURE_DIASTOLIC);
                    $data[$i]["TEMPERATURE"] = setShowText($value->TEMPERATURE);
                    break;

                case "DENTAL":
                    $data[$i]["EXAM_TYPE"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_EXAM_TYPE");
                    $data[$i]["FLUORIDE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_FLUORIDE_TREATMENT");
                    $data[$i]["X_RAYS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_X_RAYS");
                    $data[$i]["DENTAL_CARIES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_CARIES");
                    $data[$i]["TOOTH_NUMBER"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_TOOTH_NUMBER");
                    break;

                case "INJURY":
                    $data[$i]["LOCATION"] = setShowText($value->LOCATION);
                    $data[$i]["KIND_OF_INJURY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "KIND_OF_INJURY");
                    break;
             
                case "VACCINATION":
                    $data[$i]["TYPES_OF_VACCINES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "TYPES_OF_VACCINES");
                    break;

                case "VISION":
                    $data[$i]["OTHER"] = setShowText($value->OTHER);
                    $data[$i]["EYE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_TREATMENT");
                    $data[$i]["EYE_CHART"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_CHART");
                    $data[$i]["VALUES_OF_LEFT_EYE"] = self::getEyeData($value->EYE_LEFT);
                    $data[$i]["VALUES_OF_RIGHT_EYE"] = self::getEyeData($value->EYE_RIGHT);
                    break;

                case "VITAMIN":
                    $data[$i]["VND"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING");
                    $data[$i]["DP"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING_PILL");
                    $data[$i]["MMS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_MMS");
                    break;

                case "BMI":
                    $data[$i]["BMI"] = setShowText($value->BMI);
                    $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                    $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                    $data[$i]["STATUS"] = self::showBMIStatus($value->STATUS);
                    $data[$i]["BMI_Z_SCORE"] = setshowText($value->BMI_Z_SCORE);
                    $data[$i]["WT_Z_SCORE"] = setshowText($value->WT_Z_SCORE);
                    $data[$i]["HT_Z_SCORE"] = setshowText($value->HT_Z_SCORE);
                    break;

                case "GROWTH_CHART":
                    $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                    $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                    $data[$i]["PULSE"] = setShowText($value->PULSE);
                    $data[$i]["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($value->BLOOD_PRESSURE_SYSTOLIC);
                    $data[$i]["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($value->BLOOD_PRESSURE_DIASTOLIC);
                    $data[$i]["TEMPERATURE"] = setShowText($value->TEMPERATURE);
                    break;
            }

            $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
            $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

            $i++;
        }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );

        if ($isJson) {
            return $dataforjson;
        } else {
            return $data;
        }
    }

    public static function loadStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";
        $result = self::findStudentHealth($setId, $objectId, $settingId);

        $data = array();

        if ($result) {
            $data["MEDICAL_DATE"] = getShowDate($result->MEDICAL_DATE);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["LOCATION"] = setShowText($result->LOCATION);
            $data["FULL_NAME"] = setShowText($result->DOCTOR_NAME);
            $data["DOCTOR_COMMENT"] = setShowText($result->DOCTOR_COMMENT);
            $data["OTHER"] = setShowText($result->OTHER);
            $data["WEIGHT"] = setShowText($result->WEIGHT);
            $data["HEIGHT"] = setShowText($result->HEIGHT);
            $data["PULSE"] = setShowText($result->PULSE);
            $data["NEXT_VISIT"] = getShowDate($result->NEXT_VISIT);
            $data["NEXT_VISIT_TIME"] = secondToHour($result->NEXT_VISIT_TIME);
            $data["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($result->BLOOD_PRESSURE_SYSTOLIC);
			$data["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($result->BLOOD_PRESSURE_DIASTOLIC);
			$data["TEMPERATURE"] = setShowText($result->TEMPERATURE);
			$data["BMI_Z_SCORE"] = setShowText($result->BMI_Z_SCORE);
			$data["WT_Z_SCORE"] = setShowText($result->WT_Z_SCORE);
			$data["HT_Z_SCORE"] = setShowText($result->HT_Z_SCORE);

            $LIST_CHECKBOX = explode(",", $result->CHECKBOX_DATA);
            if ($LIST_CHECKBOX) {
                foreach ($LIST_CHECKBOX as $value) {
                    $data["CHECKBOX_" . $value] = true;
                }
            }
            $LIST_RADIOBOX = explode(",", $result->RADIOBOX_DATA);
            if ($LIST_RADIOBOX) {
                foreach ($LIST_RADIOBOX as $value) {
                    $setting = HealthSettingDBAccess::findHealthSettingFromId($value);
                    $data["RADIOBOX_" . $setting->PARENT] = $value;
                }
            }
        }

        $o = array(
            "success" => true
            , "data" => $data
        );
        return $o;
    }

    public static function listStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";

        $data = array();
        $i = 0;

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_medical", array('*'));

        switch ($target) {
            case "BMI":
            case "GROWTH_CHART":
            case "MEDICAL_VISIT":
                $SQL->where("OBJECT_TYPE='" . $target . "'");
                break;
            default:
                if ($settingId)
                    $SQL->where("MEDICAL_SETTING_ID='" . $settingId . "'");
                break;
        }

        if ($objectId)
            $SQL->where("STUDENT_ID='" . $objectId . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["MEDICAL_DATE"] = getShowDate($value->MEDICAL_DATE);
                $data[$i]["DOCTOR_COMMENT"] = setShowText($value->DOCTOR_COMMENT);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

                switch ($target) {
                    case "MEDICAL_VISIT":
                        if (getShowDate($value->NEXT_VISIT) != "---") {
                            $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT) . " " . secondToHour($value->NEXT_VISIT_TIME);
                        } else {
                            $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT);
                        }
                        $data[$i]["FULL_NAME"] = setShowText($value->DOCTOR_NAME);
                        $data[$i]["VISITED_BY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_BY");
                        $data[$i]["REASON"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_REASON");
                        $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                        $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                        $data[$i]["PULSE"] = setShowText($value->PULSE);
                        $data[$i]["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($value->BLOOD_PRESSURE_SYSTOLIC);
                        $data[$i]["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($value->BLOOD_PRESSURE_DIASTOLIC);
                        $data[$i]["TEMPERATURE"] = setShowText($value->TEMPERATURE);
                        $data[$i]["LOCATION"] = setShowText($value->LOCATION);
                        break;
                    case "BMI":
                        $data[$i]["BMI"] = setShowText($value->BMI);
                        $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                        $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                        $data[$i]["STATUS"] = self::showBMIStatus($value->STATUS);
                        $data[$i]["BMI_Z_SCORE"] = setShowText($value->BMI_Z_SCORE);
                        $data[$i]["WT_Z_SCORE"] = setShowText($value->WT_Z_SCORE);
                        $data[$i]["HT_Z_SCORE"] = setShowText($value->HT_Z_SCORE);
                        break;
                    case "DENTAL":
                        $data[$i]["EXAM_TYPE"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_EXAM_TYPE");
                        $data[$i]["FLUORIDE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_FLUORIDE_TREATMENT");
                        $data[$i]["X_RAYS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_X_RAYS");
                        $data[$i]["DENTAL_CARIES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_CARIES");
                        $data[$i]["TOOTH_NUMBER"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_TOOTH_NUMBER");
                        break;
                    case "INJURY":
                        $data[$i]["KIND_OF_INJURY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "KIND_OF_INJURY");
                        break;
                    case "VACCINATION":
                        $data[$i]["TYPES_OF_VACCINES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "TYPES_OF_VACCINES");
                        break;
                    case "VISION":
                        $data[$i]["OTHER"] = setShowText($value->OTHER);
                        $data[$i]["EYE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_TREATMENT");
                        $data[$i]["EYE_CHART"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_CHART");
                        $data[$i]["VALUES_OF_LEFT_EYE"] = self::getEyeData($value->EYE_LEFT);
                        $data[$i]["VALUES_OF_RIGHT_EYE"] = self::getEyeData($value->EYE_RIGHT);
                        break;
                    case "VITAMIN":
                        $data[$i]["VND"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING");
                        $data[$i]["DP"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING_PILL");
                        $data[$i]["MMS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_MMS");
                        break;
                    case "GROWTH_CHART":
                        $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                        $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                        $data[$i]["PULSE"] = setShowText($value->PULSE);
                        $data[$i]["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($value->BLOOD_PRESSURE_SYSTOLIC);
                        $data[$i]["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($value->BLOOD_PRESSURE_DIASTOLIC);
                        $data[$i]["TEMPERATURE"] = setShowText($value->TEMPERATURE);
                        break;
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function calculationBMI($Id) {

        $facette = self::findStudentHealth($Id, false, false);

        /**
         * Example: Weight = 68 kg, Height = 165 cm (1.65 m)
          Calculation: 68 ÷ (1.65)2 = 24.98
         */
        /*
         * Example: Weight = 150 lbs, Height = 5'5" (65")
          Calculation: [150 ÷ (65)2] x 703 = 24.96
         */
        $value = "";

        if ($facette) {
            switch (HealthSettingDBAccess::unitBMI()) {
                case 1:
                    if (is_numeric($facette->HEIGHT) && is_numeric($facette->WEIGHT)) {
                        $value = round($facette->WEIGHT / pow($facette->HEIGHT / 100, 2), 2);
                    }
                    break;
                case 2:
                    if (is_numeric($facette->HEIGHT) && is_numeric($facette->WEIGHT)) {
                        $value = round((($facette->WEIGHT) / pow($facette->HEIGHT * 2.54, 2)) * 703, 2);
                    }
                    break;
            }
            if ($value) {
                $data = array();
                $data['BMI']   = "$value";
                $data['STATUS']= "'". self::calculationBMIStatus($value) ."'";
                self::dbAccess()->update("t_student_medical", $data, "ID='". $facette->ID ."'");
            }
        }
    }
	
	public static function calculationZScore($Id) {
		$facette = self::findStudentHealth($Id, false, false);

        /**
         * z = ((score / M) ** L - 1) / (S * L)
         */
		$studentId = $facette->STUDENT_ID;
		$ageGender = self::findStudentAgeGender($studentId);
		$age = $ageGender[1];
		$gender = $ageGender[0];
		$score = "";
		switch (HealthSettingDBAccess::unitBMI()) {
                case 1: //metric?
                    if (is_numeric($facette->HEIGHT) && is_numeric($facette->WEIGHT)) {
                        $score = round($facette->WEIGHT / pow($facette->HEIGHT / 100, 2), 2);
                    }
                    break;
                case 2: //imperial?
                    if (is_numeric($facette->HEIGHT) && is_numeric($facette->WEIGHT)) {
                        $score = round((($facette->WEIGHT) / pow($facette->HEIGHT * 2.54, 2)) * 703, 2);
                    }
                    break;
            }
		$meanStd = self::getBMIMeanStd($age, $gender);		
		
		$l = $meanStd[0];
		$m  = $meanStd[1];
		$s = $meanStd[2];
		$value = round (((($BMI / $M) ** $L) - 1) / ($S * $L), 2);
        if ($value) {
            $data = array();
            $data['BMI_Z_SCORE']   = "$value";
            self::dbAccess()->update("t_student_medical", $data, "ID='". $facette->ID ."'");
        }
    }

    public static function calculationBMIStatus($value) {
        /**
         * Underweight = <18.5
          Normal weight = 18.5–24.9
          Overweight = 25–29.9
          Obesity = BMI of 30 or greater
         */
        $result = "";
        if ($value) {
            if ($value <= 18.49) {
                $result = 1;
            } elseif ($value >= 18.50 && $value <= 24.99) {
                $result = 2;
            } elseif ($value >= 25.00 && $value <= 29.99) {
                $result = 3;
            } elseif ($value >= 30.00) {
                $result = 4;
            }
        }

        return $result;
    }

    public static function showBMIStatus($value) {
        switch ($value) {
            case 1:
                $result = "Underweight";
                break;
            case 2:
                $result = "Normal weight";
                break;
            case 3:
                $result = "Overweight";
                break;
            case 4:
                $result = "Obesity";
                break;
            default:
                $result = "---";
                break;
        }

        return $result;
    }

    public static function getHealthSetting($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        $SQL->where("ID = ?", $Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getStudentHealthSetting($dataItems, $objectIndex) {

        $data = array();
        if ($dataItems) {
            $CHECK_DATA = explode(",", $dataItems);
            $entries = HealthSettingDBAccess::getListObjectIndizes($objectIndex);
            if ($entries) {
                foreach ($entries as $value) {
                    if (in_array($value->ID, $CHECK_DATA)) {
                        $data[] = "&bull; " . setShowText($value->NAME);
                    }
                }
            }
        }

        return implode("<br>", $data);
    }

    public static function actionStudentHealthEyeInfo($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $rowValue = isset($params["id"]) ? addText($params["id"]) : "";

        $WHERE[] = "ID = '" . $setId . "'";
        switch ($field) {
            case "EYE_LEFT":
                $SAVEDATA["EYE_LEFT"] = addText($rowValue);
                break;
            case "EYE_RIGHT":
                $SAVEDATA["EYE_RIGHT"] = addText($rowValue);
                break;
        }
        self::dbAccess()->update('t_student_medical', $SAVEDATA, $WHERE);

        return array(
            "success" => true
        );
    }

}

?>