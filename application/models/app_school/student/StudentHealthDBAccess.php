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
	//returns L, M, S of weight
	private static function getWtLMS($age, $gender){
		if($gender == "Male"){
			if($age<24){return array(-0.20615245, 12.6707633, 0.108125811);}
			else if($age<24.5){return array(-0.216501213, 12.74154396, 0.108166006);}
			else if($age<25.5){return array(-0.239790488, 12.88102276, 0.108274706);}
			else if($age<26.5){return array(-0.266315853, 13.01842382, 0.108421025);}
			else if($age<27.5){return array(-0.295754969, 13.1544966, 0.10860477);}
			else if($age<28.5){return array(-0.327729368, 13.28989667, 0.108825681);}
			else if($age<29.5){return array(-0.361817468, 13.42519408, 0.109083424);}
			else if($age<30.5){return array(-0.397568087, 13.56088113, 0.109377581);}
			else if($age<31.5){return array(-0.434520252, 13.69737858, 0.109707646);}
			else if($age<32.5){return array(-0.472188756, 13.83504622, 0.110073084);}
			else if($age<33.5){return array(-0.510116627, 13.97418299, 0.110473254);}
			else if($age<34.5){return array(-0.547885579, 14.1150324, 0.1109074);}
			else if($age<35.5){return array(-0.58507011, 14.25779618, 0.111374787);}
			else if($age<36.5){return array(-0.621319726, 14.40262749, 0.111874514);}
			else if($age<37.5){return array(-0.656295986, 14.54964614, 0.112405687);}
			else if($age<38.5){return array(-0.689735029, 14.69893326, 0.112967254);}
			else if($age<39.5){return array(-0.721410388, 14.85054151, 0.11355811);}
			else if($age<40.5){return array(-0.751175223, 15.00449143, 0.114176956);}
			else if($age<41.5){return array(-0.778904279, 15.16078454, 0.114822482);}
			else if($age<42.5){return array(-0.804515498, 15.31940246, 0.115493292);}
			else if($age<43.5){return array(-0.828003255, 15.48030313, 0.116187777);}
			else if($age<44.5){return array(-0.849380372, 15.64343309, 0.116904306);}
			else if($age<45.5){return array(-0.86869965, 15.80872535, 0.117641148);}
			else if($age<46.5){return array(-0.886033992, 15.97610456, 0.118396541);}
			else if($age<47.5){return array(-0.901507878, 16.14548194, 0.119168555);}
			else if($age<48.5){return array(-0.915241589, 16.31676727, 0.11995532);}
			else if($age<49.5){return array(-0.927377772, 16.4898646, 0.120754916);}
			else if($age<50.5){return array(-0.938069819, 16.66467529, 0.121565421);}
			else if($age<51.5){return array(-0.94747794, 16.84109948, 0.122384927);}
			else if($age<52.5){return array(-0.955765694, 17.01903746, 0.123211562);}
			else if($age<53.5){return array(-0.963096972, 17.1983908, 0.124043503);}
			else if($age<54.5){return array(-0.969633434, 17.37906341, 0.124878992);}
			else if($age<55.5){return array(-0.975532355, 17.56096245, 0.125716348);}
			else if($age<56.5){return array(-0.980937915, 17.74400082, 0.126554022);}
			else if($age<57.5){return array(-0.986006518, 17.92809121, 0.127390453);}
			else if($age<58.5){return array(-0.99086694, 18.11315625, 0.128224294);}
			else if($age<59.5){return array(-0.995644402, 18.29912286, 0.129054277);}
			else if($age<60.5){return array(-1.000453886, 18.48592413, 0.129879257);}
			else if($age<61.5){return array(-1.005399668, 18.67349965, 0.130698212);}
			else if($age<62.5){return array(-1.010575003, 18.86179576, 0.131510245);}
			else if($age<63.5){return array(-1.016061941, 19.05076579, 0.132314586);}
			else if($age<64.5){return array(-1.021931241, 19.24037019, 0.133110593);}
			else if($age<65.5){return array(-1.028242376, 19.43057662, 0.133897752);}
			else if($age<66.5){return array(-1.035043608, 19.62136007, 0.134675673);}
			else if($age<67.5){return array(-1.042372125, 19.8127028, 0.13544409);}
			else if($age<68.5){return array(-1.050254232, 20.0045944, 0.13620286);}
			else if($age<69.5){return array(-1.058705595, 20.19703171, 0.136951959);}
			else if($age<70.5){return array(-1.067731529, 20.39001872, 0.137691478);}
			else if($age<71.5){return array(-1.077321193, 20.58356862, 0.138421673);}
			else if($age<72.5){return array(-1.087471249, 20.77769565, 0.139142773);}
			else if($age<73.5){return array(-1.098152984, 20.97242631, 0.139855242);}
			else if($age<74.5){return array(-1.10933408, 21.16779192, 0.140559605);}
			else if($age<75.5){return array(-1.120974043, 21.36383013, 0.141256489);}
			else if($age<76.5){return array(-1.133024799, 21.56058467, 0.141946613);}
			else if($age<77.5){return array(-1.145431351, 21.75810506, 0.142630785);}
			else if($age<78.5){return array(-1.158132499, 21.95644627, 0.143309898);}
			else if($age<79.5){return array(-1.171061612, 22.15566842, 0.143984924);}
			else if($age<80.5){return array(-1.184141975, 22.35583862, 0.144656953);}
			else if($age<81.5){return array(-1.197307185, 22.55702268, 0.145327009);}
			else if($age<82.5){return array(-1.210475099, 22.75929558, 0.145996289);}
			else if($age<83.5){return array(-1.223565263, 22.9627344, 0.146666);}
			else if($age<84.5){return array(-1.236497304, 23.16741888, 0.147337375);}
			else if($age<85.5){return array(-1.249186293, 23.37343341, 0.148011715);}
			else if($age<86.5){return array(-1.261555446, 23.58086145, 0.148690256);}
			else if($age<87.5){return array(-1.273523619, 23.78979096, 0.149374297);}
			else if($age<88.5){return array(-1.285013783, 24.00031064, 0.150065107);}
			else if($age<89.5){return array(-1.295952066, 24.21251028, 0.150763933);}
			else if($age<90.5){return array(-1.306268473, 24.42648043, 0.151471982);}
			else if($age<91.5){return array(-1.31589753, 24.642312, 0.152190413);}
			else if($age<92.5){return array(-1.324778843, 24.86009596, 0.152920322);}
			else if($age<93.5){return array(-1.332857581, 25.07992303, 0.153662731);}
			else if($age<94.5){return array(-1.340080195, 25.30188584, 0.154418635);}
			else if($age<95.5){return array(-1.346412105, 25.52606977, 0.155188768);}
			else if($age<96.5){return array(-1.351813296, 25.75256528, 0.155973912);}
			else if($age<97.5){return array(-1.356253969, 25.9814599, 0.156774684);}
			else if($age<98.5){return array(-1.359710858, 26.2128399, 0.157591579);}
			else if($age<99.5){return array(-1.362167159, 26.44679027, 0.158424964);}
			else if($age<100.5){return array(-1.363612378, 26.68339457, 0.159275071);}
			else if($age<101.5){return array(-1.364042106, 26.92273494, 0.160141995);}
			else if($age<102.5){return array(-1.363457829, 27.16489199, 0.161025689);}
			else if($age<103.5){return array(-1.361865669, 27.40994539, 0.161925976);}
			else if($age<104.5){return array(-1.35928261, 27.65796978, 0.162842452);}
			else if($age<105.5){return array(-1.355720571, 27.90904433, 0.163774719);}
			else if($age<106.5){return array(-1.351202536, 28.16324264, 0.164722138);}
			else if($age<107.5){return array(-1.345754408, 28.42063744, 0.165683945);}
			else if($age<108.5){return array(-1.339405453, 28.68130005, 0.166659247);}
			else if($age<109.5){return array(-1.332188093, 28.94530029, 0.167647017);}
			else if($age<110.5){return array(-1.324137479, 29.21270645, 0.168646104);}
			else if($age<111.5){return array(-1.315291073, 29.48358527, 0.169655235);}
			else if($age<112.5){return array(-1.30568824, 29.75800198, 0.170673022);}
			else if($age<113.5){return array(-1.295369867, 30.03602021, 0.17169797);}
			else if($age<114.5){return array(-1.284374967, 30.31770417, 0.17272854);}
			else if($age<115.5){return array(-1.272750864, 30.60311107, 0.173762961);}
			else if($age<116.5){return array(-1.260539193, 30.89230072, 0.174799493);}
			else if($age<117.5){return array(-1.247783611, 31.18532984, 0.175836284);}
			else if($age<118.5){return array(-1.234527763, 31.48225315, 0.176871417);}
			else if($age<119.5){return array(-1.220815047, 31.78312329, 0.177902912);}
			else if($age<120.5){return array(-1.206688407, 32.08799062, 0.17892874);}
			else if($age<121.5){return array(-1.19219015, 32.39690313, 0.17994683);}
			else if($age<122.5){return array(-1.177361786, 32.7099062, 0.180955078);}
			else if($age<123.5){return array(-1.162243894, 33.02704244, 0.181951361);}
			else if($age<124.5){return array(-1.146876007, 33.34835148, 0.182933537);}
			else if($age<125.5){return array(-1.131296524, 33.67386973, 0.183899465);}
			else if($age<126.5){return array(-1.115542634, 34.00363017, 0.184847006);}
			else if($age<127.5){return array(-1.099650267, 34.33766207, 0.185774041);}
			else if($age<128.5){return array(-1.083654055, 34.67599076, 0.18667847);}
			else if($age<129.5){return array(-1.067587314, 35.01863732, 0.187558229);}
			else if($age<130.5){return array(-1.051482972, 35.36561737, 0.18841128);}
			else if($age<131.5){return array(-1.035367321, 35.71694723, 0.189235738);}
			else if($age<132.5){return array(-1.019277299, 36.07262569, 0.190029545);}
			else if($age<133.5){return array(-1.003235326, 36.43265996, 0.190790973);}
			else if($age<134.5){return array(-0.987269866, 36.79704392, 0.191518224);}
			else if($age<135.5){return array(-0.971406609, 37.1657671, 0.192209619);}
			else if($age<136.5){return array(-0.955670107, 37.53881268, 0.192863569);}
			else if($age<137.5){return array(-0.940083834, 37.91615721, 0.193478582);}
			else if($age<138.5){return array(-0.924670244, 38.2977703, 0.194053274);}
			else if($age<139.5){return array(-0.909450843, 38.6836143, 0.194586368);}
			else if($age<140.5){return array(-0.894446258, 39.07364401, 0.195076705);}
			else if($age<141.5){return array(-0.879676305, 39.46780643, 0.195523246);}
			else if($age<142.5){return array(-0.865160071, 39.86604044, 0.195925079);}
			else if($age<143.5){return array(-0.850915987, 40.26827652, 0.196281418);}
			else if($age<144.5){return array(-0.836961905, 40.67443658, 0.196591612);}
			else if($age<145.5){return array(-0.823315176, 41.08443363, 0.19685514);}
			else if($age<146.5){return array(-0.809992726, 41.49817164, 0.19707162);}
			else if($age<147.5){return array(-0.797011132, 41.91554528, 0.197240806);}
			else if($age<148.5){return array(-0.784386693, 42.33643978, 0.197362591);}
			else if($age<149.5){return array(-0.772135506, 42.76073078, 0.197437004);}
			else if($age<150.5){return array(-0.760273528, 43.18828419, 0.19746421);}
			else if($age<151.5){return array(-0.748815968, 43.61895703, 0.197444522);}
			else if($age<152.5){return array(-0.737780398, 44.0525931, 0.197378345);}
			else if($age<153.5){return array(-0.727181568, 44.48903027, 0.197266263);}
			else if($age<154.5){return array(-0.717035494, 44.92809483, 0.197108968);}
			else if($age<155.5){return array(-0.707358338, 45.36960315, 0.196907274);}
			else if($age<156.5){return array(-0.698166437, 45.81336172, 0.196662115);}
			else if($age<157.5){return array(-0.689476327, 46.25916729, 0.196374538);}
			else if($age<158.5){return array(-0.68130475, 46.70680701, 0.196045701);}
			else if($age<159.5){return array(-0.673668658, 47.15605863, 0.195676862);}
			else if($age<160.5){return array(-0.666585194, 47.60669074, 0.19526938);}
			else if($age<161.5){return array(-0.660069969, 48.05846572, 0.19482473);}
			else if($age<162.5){return array(-0.654142602, 48.51113138, 0.19434441);}
			else if($age<163.5){return array(-0.648819666, 48.96443224, 0.193830046);}
			else if($age<164.5){return array(-0.644118611, 49.41810374, 0.193283319);}
			else if($age<165.5){return array(-0.640056805, 49.87187409, 0.192705974);}
			else if($age<166.5){return array(-0.636651424, 50.32546478, 0.192099812);}
			else if($age<167.5){return array(-0.633919328, 50.77859121, 0.191466681);}
			else if($age<168.5){return array(-0.631876912, 51.23096332, 0.190808471);}
			else if($age<169.5){return array(-0.63053994, 51.68228625, 0.190127105);}
			else if($age<170.5){return array(-0.629923353, 52.13226113, 0.18942453);}
			else if($age<171.5){return array(-0.630041066, 52.58058583, 0.188702714);}
			else if($age<172.5){return array(-0.630905733, 53.02695588, 0.187963636);}
			else if($age<173.5){return array(-0.632528509, 53.47106525, 0.187209281);}
			else if($age<174.5){return array(-0.634918779, 53.91260737, 0.18644163);}
			else if($age<175.5){return array(-0.638083884, 54.35127608, 0.185662657);}
			else if($age<176.5){return array(-0.642028835, 54.78676659, 0.184874323);}
			else if($age<177.5){return array(-0.646756013, 55.21877657, 0.184078567);}
			else if($age<178.5){return array(-0.652262297, 55.64701131, 0.183277339);}
			else if($age<179.5){return array(-0.658551638, 56.07116407, 0.182472427);}
			else if($age<180.5){return array(-0.665609025, 56.49095862, 0.181665781);}
			else if($age<181.5){return array(-0.673425951, 56.90610886, 0.18085918);}
			else if($age<182.5){return array(-0.681987284, 57.31634059, 0.180054395);}
			else if($age<183.5){return array(-0.691273614, 57.72138846, 0.179253153);}
			else if($age<184.5){return array(-0.701261055, 58.12099696, 0.178457127);}
			else if($age<185.5){return array(-0.711921092, 58.51492143, 0.177667942);}
			else if($age<186.5){return array(-0.723218488, 58.90293208, 0.176887192);}
			else if($age<187.5){return array(-0.735121189, 59.28479948, 0.176116307);}
			else if($age<188.5){return array(-0.747580416, 59.66032626, 0.175356814);}
			else if($age<189.5){return array(-0.760550666, 60.02931704, 0.174610071);}
			else if($age<190.5){return array(-0.773984558, 60.39158721, 0.173877336);}
			else if($age<191.5){return array(-0.787817728, 60.74698785, 0.173159953);}
			else if($age<192.5){return array(-0.801993069, 61.09536847, 0.172459052);}
			else if($age<193.5){return array(-0.816446409, 61.43660077, 0.171775726);}
			else if($age<194.5){return array(-0.831110299, 61.77057372, 0.171110986);}
			else if($age<195.5){return array(-0.845914498, 62.09719399, 0.170465756);}
			else if($age<196.5){return array(-0.860786514, 62.41638628, 0.169840869);}
			else if($age<197.5){return array(-0.875652181, 62.72809362, 0.169237063);}
			else if($age<198.5){return array(-0.890436283, 63.03227756, 0.168654971);}
			else if($age<199.5){return array(-0.905063185, 63.32891841, 0.168095124);}
			else if($age<200.5){return array(-0.91945749, 63.61801537, 0.16755794);}
			else if($age<201.5){return array(-0.933544683, 63.89958662, 0.167043722);}
			else if($age<202.5){return array(-0.947251765, 64.17366943, 0.166552654);}
			else if($age<203.5){return array(-0.960507855, 64.44032016, 0.166084798);}
			else if($age<204.5){return array(-0.973244762, 64.69961427, 0.16564009);}
			else if($age<205.5){return array(-0.985397502, 64.95164625, 0.165218341);}
			else if($age<206.5){return array(-0.996904762, 65.1965295, 0.164819236);}
			else if($age<207.5){return array(-1.007705555, 65.43440186, 0.16444238);}
			else if($age<208.5){return array(-1.017756047, 65.66540015, 0.164087103);}
			else if($age<209.5){return array(-1.027002713, 65.88970117, 0.163752791);}
			else if($age<210.5){return array(-1.035402243, 66.10749114, 0.163438661);}
			else if($age<211.5){return array(-1.042916356, 66.31897311, 0.163143825);}
			else if($age<212.5){return array(-1.049511871, 66.52436618, 0.162867311);}
			else if($age<213.5){return array(-1.055160732, 66.72390443, 0.162608072);}
			else if($age<214.5){return array(-1.059840019, 66.91783563, 0.162365006);}
			else if($age<215.5){return array(-1.063531973, 67.10641956, 0.162136973);}
			else if($age<216.5){return array(-1.066224038, 67.28992603, 0.161922819);}
			else if($age<217.5){return array(-1.067908908, 67.46863255, 0.161721398);}
			else if($age<218.5){return array(-1.068589885, 67.64281378, 0.16153153);}
			else if($age<219.5){return array(-1.068261146, 67.8127675, 0.161352313);}
			else if($age<220.5){return array(-1.066933756, 67.97877331, 0.161182785);}
			else if($age<221.5){return array(-1.064620976, 68.14111022, 0.161022184);}
			else if($age<222.5){return array(-1.061341755, 68.30004741, 0.160869943);}
			else if($age<223.5){return array(-1.057116957, 68.4558454, 0.160725793);}
			else if($age<224.5){return array(-1.051988979, 68.60872174, 0.160589574);}
			else if($age<225.5){return array(-1.04599033, 68.75889263, 0.1604617);}
			else if($age<226.5){return array(-1.039168248, 68.90653028, 0.160342924);}
			else if($age<227.5){return array(-1.031579574, 69.05176427, 0.160234478);}
			else if($age<228.5){return array(-1.023291946, 69.19467288, 0.160138158);}
			else if($age<229.5){return array(-1.014385118, 69.33527376, 0.160056393);}
			else if($age<230.5){return array(-1.004952366, 69.47351373, 0.159992344);}
			else if($age<231.5){return array(-0.995101924, 69.60925782, 0.159949989);}
			else if($age<232.5){return array(-0.984958307, 69.74227758, 0.159934231);}
			else if($age<233.5){return array(-0.974663325, 69.87223885, 0.159951004);}
			else if($age<234.5){return array(-0.964376555, 69.99868896, 0.160007394);}
			else if($age<235.5){return array(-0.954274945, 70.12104381, 0.160111769);}
			else if($age<236.5){return array(-0.944551187, 70.23857482, 0.160273918);}
			else if($age<237.5){return array(-0.935410427, 70.35039626, 0.160505203);}
			else if($age<238.5){return array(-0.927059784, 70.45546105, 0.160818788);}
			else if($age<239.5){return array(-0.919718461, 70.55252127, 0.161229617);}
			else if($age<240){return array(-0.91648762, 70.59761453, 0.161476792);}
	}else{
			if($age<24){return array(-0.73533951, 12.05503983, 0.107399495);}
			else if($age<24.5){return array(-0.75220657, 12.13455523, 0.107740345);}
			else if($age<25.5){return array(-0.78423366, 12.2910249, 0.10847701);}
			else if($age<26.5){return array(-0.81409582, 12.44469258, 0.109280828);}
			else if($age<27.5){return array(-0.841935504, 12.59622335, 0.110144488);}
			else if($age<28.5){return array(-0.867889398, 12.74620911, 0.111060815);}
			else if($age<29.5){return array(-0.892102647, 12.89517218, 0.112022759);}
			else if($age<30.5){return array(-0.914718817, 13.04357164, 0.113023467);}
			else if($age<31.5){return array(-0.935876584, 13.19180874, 0.114056328);}
			else if($age<32.5){return array(-0.955723447, 13.34022934, 0.115114953);}
			else if($age<33.5){return array(-0.974383363, 13.48913319, 0.116193327);}
			else if($age<34.5){return array(-0.991980756, 13.63877446, 0.11728575);}
			else if($age<35.5){return array(-1.008640742, 13.78936547, 0.118386848);}
			else if($age<36.5){return array(-1.024471278, 13.94108332, 0.119491669);}
			else if($age<37.5){return array(-1.039573604, 14.09407175, 0.120595658);}
			else if($age<38.5){return array(-1.054039479, 14.24844498, 0.121694676);}
			else if($age<39.5){return array(-1.067946784, 14.40429169, 0.12278503);}
			else if($age<40.5){return array(-1.081374153, 14.56167529, 0.1238634);}
			else if($age<41.5){return array(-1.094381409, 14.72064045, 0.124926943);}
			else if($age<42.5){return array(-1.107021613, 14.88121352, 0.125973221);}
			else if($age<43.5){return array(-1.119338692, 15.04340553, 0.127000212);}
			else if($age<44.5){return array(-1.131367831, 15.20721443, 0.128006292);}
			else if($age<45.5){return array(-1.143135936, 15.37262729, 0.128990225);}
			else if($age<46.5){return array(-1.15466215, 15.53962221, 0.129951143);}
			else if($age<47.5){return array(-1.165958392, 15.70817017, 0.130888527);}
			else if($age<48.5){return array(-1.177029925, 15.87823668, 0.131802186);}
			else if($age<49.5){return array(-1.187871001, 16.04978452, 0.132692269);}
			else if($age<50.5){return array(-1.198484073, 16.2227706, 0.133559108);}
			else if($age<51.5){return array(-1.208853947, 16.39715363, 0.134403386);}
			else if($age<52.5){return array(-1.218965087, 16.57289122, 0.13522599);}
			else if($age<53.5){return array(-1.228798212, 16.74994187, 0.136028014);}
			else if($age<54.5){return array(-1.238330855, 16.92826587, 0.136810739);}
			else if($age<55.5){return array(-1.247537914, 17.10782615, 0.137575606);}
			else if($age<56.5){return array(-1.256392179, 17.28858894, 0.138324193);}
			else if($age<57.5){return array(-1.264864846, 17.47052444, 0.139058192);}
			else if($age<58.5){return array(-1.272926011, 17.65360733, 0.139779387);}
			else if($age<59.5){return array(-1.28054514, 17.83781722, 0.140489635);}
			else if($age<60.5){return array(-1.287691525, 18.02313904, 0.141190842);}
			else if($age<61.5){return array(-1.294332076, 18.20956418, 0.141884974);}
			else if($age<62.5){return array(-1.300441561, 18.3970876, 0.142573939);}
			else if($age<63.5){return array(-1.305989011, 18.58571243, 0.143259709);}
			else if($age<64.5){return array(-1.310946941, 18.77544728, 0.143944216);}
			else if($age<65.5){return array(-1.315289534, 18.966307, 0.144629359);}
			else if($age<66.5){return array(-1.318992925, 19.15831267, 0.14531699);}
			else if($age<67.5){return array(-1.322035315, 19.35149163, 0.146008903);}
			else if($age<68.5){return array(-1.324398133, 19.54587708, 0.146706813);}
			else if($age<69.5){return array(-1.326064539, 19.74150854, 0.147412363);}
			else if($age<70.5){return array(-1.327020415, 19.93843145, 0.148127109);}
			else if($age<71.5){return array(-1.327256387, 20.13669623, 0.148852482);}
			else if($age<72.5){return array(-1.326763834, 20.33635961, 0.149589838);}
			else if($age<73.5){return array(-1.325538668, 20.53748298, 0.1503404);}
			else if($age<74.5){return array(-1.323579654, 20.74013277, 0.151105277);}
			else if($age<75.5){return array(-1.320888012, 20.94438028, 0.151885464);}
			else if($age<76.5){return array(-1.317468695, 21.15030093, 0.152681819);}
			else if($age<77.5){return array(-1.313331446, 21.35797332, 0.15349505);}
			else if($age<78.5){return array(-1.308487081, 21.56748045, 0.154325756);}
			else if($age<79.5){return array(-1.302948173, 21.77890902, 0.155174414);}
			else if($age<80.5){return array(-1.296733913, 21.99234686, 0.15604132);}
			else if($age<81.5){return array(-1.289863329, 22.20788541, 0.156926667);}
			else if($age<82.5){return array(-1.282358762, 22.4256177, 0.157830504);}
			else if($age<83.5){return array(-1.274244931, 22.64563824, 0.158752743);}
			else if($age<84.5){return array(-1.265548787, 22.86804258, 0.159693163);}
			else if($age<85.5){return array(-1.256299378, 23.09292679, 0.16065141);}
			else if($age<86.5){return array(-1.24653066, 23.32038549, 0.161626956);}
			else if($age<87.5){return array(-1.236266832, 23.55051871, 0.162619308);}
			else if($age<88.5){return array(-1.225551344, 23.78341652, 0.1636276);}
			else if($age<89.5){return array(-1.214410914, 24.01917703, 0.1646511);}
			else if($age<90.5){return array(-1.202884389, 24.25789074, 0.165688808);}
			else if($age<91.5){return array(-1.191007906, 24.49964778, 0.166739662);}
			else if($age<92.5){return array(-1.178818621, 24.74453536, 0.167802495);}
			else if($age<93.5){return array(-1.166354376, 24.99263735, 0.168876037);}
			else if($age<94.5){return array(-1.153653688, 25.24403371, 0.169958922);}
			else if($age<95.5){return array(-1.140751404, 25.49880264, 0.171049756);}
			else if($age<96.5){return array(-1.127684095, 25.7570168, 0.172147043);}
			else if($age<97.5){return array(-1.114490244, 26.01874261, 0.173249185);}
			else if($age<98.5){return array(-1.101204848, 26.28404312, 0.174354569);}
			else if($age<99.5){return array(-1.087863413, 26.55297507, 0.175461512);}
			else if($age<100.5){return array(-1.074500927, 26.82558904, 0.176568284);}
			else if($age<101.5){return array(-1.061151213, 27.1019295, 0.177673124);}
			else if($age<102.5){return array(-1.047847141, 27.38203422, 0.178774242);}
			else if($age<103.5){return array(-1.034620551, 27.66593402, 0.179869829);}
			else if($age<104.5){return array(-1.021502197, 27.9536524, 0.180958063);}
			else if($age<105.5){return array(-1.008521695, 28.24520531, 0.182037118);}
			else if($age<106.5){return array(-0.995707494, 28.54060085, 0.183105172);}
			else if($age<107.5){return array(-0.983086844, 28.83983907, 0.18416041);}
			else if($age<108.5){return array(-0.970685789, 29.14291171, 0.185201039);}
			else if($age<109.5){return array(-0.958529157, 29.44980208, 0.186225287);}
			else if($age<110.5){return array(-0.946640568, 29.76048479, 0.187231416);}
			else if($age<111.5){return array(-0.935042447, 30.0749257, 0.188217723);}
			else if($age<112.5){return array(-0.923756041, 30.39308176, 0.18918255);}
			else if($age<113.5){return array(-0.912801445, 30.71490093, 0.190124286);}
			else if($age<114.5){return array(-0.902197638, 31.0403221, 0.191041375);}
			else if($age<115.5){return array(-0.891962513, 31.36927506, 0.191932319);}
			else if($age<116.5){return array(-0.882112919, 31.7016805, 0.192795682);}
			else if($age<117.5){return array(-0.872664706, 32.03744999, 0.193630095);}
			else if($age<118.5){return array(-0.863632768, 32.37648607, 0.19443426);}
			else if($age<119.5){return array(-0.855031092, 32.71868225, 0.195206948);}
			else if($age<120.5){return array(-0.846872805, 33.06392318, 0.195947008);}
			else if($age<121.5){return array(-0.839170224, 33.4120847, 0.196653365);}
			else if($age<122.5){return array(-0.831934903, 33.76303402, 0.197325023);}
			else if($age<123.5){return array(-0.825177688, 34.1166299, 0.197961065);}
			else if($age<124.5){return array(-0.818908758, 34.47272283, 0.198560655);}
			else if($age<125.5){return array(-0.813137675, 34.83115524, 0.199123037);}
			else if($age<126.5){return array(-0.807873433, 35.19176177, 0.199647538);}
			else if($age<127.5){return array(-0.803122613, 35.55437176, 0.200133598);}
			else if($age<128.5){return array(-0.79889771, 35.91879976, 0.200580618);}
			else if($age<129.5){return array(-0.795203499, 36.28486194, 0.200988216);}
			else if($age<130.5){return array(-0.792047959, 36.65236365, 0.201356017);}
			else if($age<131.5){return array(-0.789435274, 37.02110818, 0.201683791);}
			else if($age<132.5){return array(-0.787374433, 37.39088668, 0.201971282);}
			else if($age<133.5){return array(-0.785870695, 37.76148905, 0.202218375);}
			else if($age<134.5){return array(-0.784929893, 38.1326991, 0.202425006);}
			else if($age<135.5){return array(-0.784557605, 38.50429603, 0.202591183);}
			else if($age<136.5){return array(-0.78475917, 38.87605489, 0.20271698);}
			else if($age<137.5){return array(-0.785539703, 39.24774707, 0.202802535);}
			else if($age<138.5){return array(-0.786904102, 39.61914076, 0.202848049);}
			else if($age<139.5){return array(-0.788858208, 39.98999994, 0.202853758);}
			else if($age<140.5){return array(-0.791403051, 40.36009244, 0.202820053);}
			else if($age<141.5){return array(-0.794546352, 40.72917544, 0.202747236);}
			else if($age<142.5){return array(-0.79829102, 41.09701099, 0.202635758);}
			else if($age<143.5){return array(-0.802640891, 41.46335907, 0.202486098);}
			else if($age<144.5){return array(-0.807599577, 41.82797963, 0.202298783);}
			else if($age<145.5){return array(-0.813170461, 42.19063313, 0.202074385);}
			else if($age<146.5){return array(-0.819356692, 42.55108107, 0.201813521);}
			else if($age<147.5){return array(-0.826161176, 42.90908653, 0.201516851);}
			else if($age<148.5){return array(-0.833586038, 43.2644155, 0.201185082);}
			else if($age<149.5){return array(-0.841634949, 43.61683402, 0.200818928);}
			else if($age<150.5){return array(-0.850307441, 43.9661169, 0.200419208);}
			else if($age<151.5){return array(-0.859607525, 44.31203579, 0.199986681);}
			else if($age<152.5){return array(-0.869534339, 44.65437319, 0.199522233);}
			else if($age<153.5){return array(-0.880088651, 44.99291356, 0.199026736);}
			else if($age<154.5){return array(-0.891270585, 45.32744704, 0.198501096);}
			else if($age<155.5){return array(-0.903079458, 45.65777013, 0.197946255);}
			else if($age<156.5){return array(-0.915513542, 45.98368656, 0.197363191);}
			else if($age<157.5){return array(-0.928569454, 46.30500858, 0.196752931);}
			else if($age<158.5){return array(-0.942245864, 46.62155183, 0.196116472);}
			else if($age<159.5){return array(-0.956537923, 46.93314404, 0.19545489);}
			else if($age<160.5){return array(-0.971440492, 47.23962058, 0.194769279);}
			else if($age<161.5){return array(-0.986947308, 47.54082604, 0.194060758);}
			else if($age<162.5){return array(-1.003050887, 47.83661466, 0.193330477);}
			else if($age<163.5){return array(-1.019742425, 48.12685082, 0.192579614);}
			else if($age<164.5){return array(-1.037011698, 48.41140938, 0.191809374);}
			else if($age<165.5){return array(-1.054846957, 48.69017613, 0.191020995);}
			else if($age<166.5){return array(-1.073234825, 48.9630481, 0.190215739);}
			else if($age<167.5){return array(-1.092160195, 49.22993391, 0.189394901);}
			else if($age<168.5){return array(-1.111606122, 49.49075409, 0.188559804);}
			else if($age<169.5){return array(-1.131553723, 49.74544132, 0.187711798);}
			else if($age<170.5){return array(-1.151982079, 49.99394068, 0.186852266);}
			else if($age<171.5){return array(-1.172868141, 50.23620985, 0.185982617);}
			else if($age<172.5){return array(-1.19418462, 50.47222213, 0.185104331);}
			else if($age<173.5){return array(-1.215907492, 50.70195581, 0.184218803);}
			else if($age<174.5){return array(-1.238005268, 50.92540942, 0.183327556);}
			else if($age<175.5){return array(-1.260445591, 51.14259229, 0.182432113);}
			else if($age<176.5){return array(-1.283193626, 51.3535268, 0.181534018);}
			else if($age<177.5){return array(-1.306212032, 51.55824831, 0.180634839);}
			else if($age<178.5){return array(-1.329460945, 51.75680513, 0.179736168);}
			else if($age<179.5){return array(-1.35289798, 51.94925841, 0.178839614);}
			else if($age<180.5){return array(-1.376478254, 52.13568193, 0.177946804);}
			else if($age<181.5){return array(-1.400154426, 52.31616197, 0.177059379);}
			else if($age<182.5){return array(-1.423876772, 52.49079703, 0.17617899);}
			else if($age<183.5){return array(-1.447593267, 52.65969757, 0.175307296);}
			else if($age<184.5){return array(-1.471249702, 52.82298572, 0.174445958);}
			else if($age<185.5){return array(-1.494789826, 52.9807949, 0.173596636);}
			else if($age<186.5){return array(-1.518155513, 53.13326946, 0.172760982);}
			else if($age<187.5){return array(-1.541286949, 53.28056425, 0.17194064);}
			else if($age<188.5){return array(-1.564122852, 53.42284417, 0.171137232);}
			else if($age<189.5){return array(-1.586600712, 53.5602837, 0.170352363);}
			else if($age<190.5){return array(-1.608657054, 53.69306637, 0.169587605);}
			else if($age<191.5){return array(-1.630227728, 53.82138422, 0.168844497);}
			else if($age<192.5){return array(-1.651248208, 53.94543725, 0.168124538);}
			else if($age<193.5){return array(-1.67165392, 54.06543278, 0.167429179);}
			else if($age<194.5){return array(-1.691380583, 54.18158486, 0.166759816);}
			else if($age<195.5){return array(-1.710364557, 54.29411356, 0.166117788);}
			else if($age<196.5){return array(-1.728543207, 54.40324431, 0.165504365);}
			else if($age<197.5){return array(-1.745855274, 54.50920717, 0.164920747);}
			else if($age<198.5){return array(-1.762241248, 54.61223603, 0.164368054);}
			else if($age<199.5){return array(-1.777643747, 54.71256787, 0.16384732);}
			else if($age<200.5){return array(-1.792007891, 54.81044184, 0.163359491);}
			else if($age<201.5){return array(-1.805281675, 54.90609842, 0.162905415);}
			else if($age<202.5){return array(-1.817416335, 54.99977846, 0.162485839);}
			else if($age<203.5){return array(-1.828366707, 55.09172217, 0.162101402);}
			else if($age<204.5){return array(-1.838091576, 55.18216811, 0.161752634);}
			else if($age<205.5){return array(-1.846554015, 55.271352, 0.161439944);}
			else if($age<206.5){return array(-1.853721704, 55.35950558, 0.161163623);}
			else if($age<207.5){return array(-1.859567242, 55.44685531, 0.160923833);}
			else if($age<208.5){return array(-1.864068443, 55.53362107, 0.160720609);}
			else if($age<209.5){return array(-1.86720861, 55.62001464, 0.16055385);}
			else if($age<210.5){return array(-1.8689768, 55.70623826, 0.160423319);}
			else if($age<211.5){return array(-1.869371157, 55.79247939, 0.160328578);}
			else if($age<212.5){return array(-1.868386498, 55.87892356, 0.160269232);}
			else if($age<213.5){return array(-1.866033924, 55.96573022, 0.160244549);}
			else if($age<214.5){return array(-1.862327775, 56.05304601, 0.160253714);}
			else if($age<215.5){return array(-1.857289195, 56.14099882, 0.160295765);}
			else if($age<216.5){return array(-1.850946286, 56.22969564, 0.16036959);}
			else if($age<217.5){return array(-1.84333425, 56.3192203, 0.16047393);}
			else if($age<218.5){return array(-1.834495505, 56.40963105, 0.160607377);}
			else if($age<219.5){return array(-1.824479785, 56.50095811, 0.16076838);}
			else if($age<220.5){return array(-1.813344222, 56.59320107, 0.160955249);}
			else if($age<221.5){return array(-1.801153404, 56.68632619, 0.161166157);}
			else if($age<222.5){return array(-1.787979408, 56.78026364, 0.161399151);}
			else if($age<223.5){return array(-1.773901816, 56.87490465, 0.161652158);}
			else if($age<224.5){return array(-1.759007704, 56.97009856, 0.161922998);}
			else if($age<225.5){return array(-1.743391606, 57.06564989, 0.162209399);}
			else if($age<226.5){return array(-1.72715546, 57.16131528, 0.162509006);}
			else if($age<227.5){return array(-1.710410733, 57.25679821, 0.162819353);}
			else if($age<228.5){return array(-1.693267093, 57.35175792, 0.163138124);}
			else if($age<229.5){return array(-1.67585442, 57.44578172, 0.163462715);}
			else if($age<230.5){return array(-1.658302847, 57.53840429, 0.163790683);}
			else if($age<231.5){return array(-1.640747464, 57.62910094, 0.164119574);}
			else if($age<232.5){return array(-1.623332891, 57.7172758, 0.164446997);}
			else if($age<233.5){return array(-1.606209374, 57.80226553, 0.164770638);}
			else if($age<234.5){return array(-1.589533346, 57.88333502, 0.165088289);}
			else if($age<235.5){return array(-1.573467222, 57.95967458, 0.165397881);}
			else if($age<236.5){return array(-1.558179166, 58.0303973, 0.165697507);}
			else if($age<237.5){return array(-1.543846192, 58.09453209, 0.165985386);}
			else if($age<238.5){return array(-1.530642461, 58.15103575, 0.166260109);}
			else if($age<239.5){return array(-1.518754013, 58.1987714, 0.16652037);}
			else if($age<240){return array(-1.51336185, 58.21897289, 0.166644749);}
		}
	}
	
	//@Luke
	//returns L, M, S of height
	private static function getHtLMS($age, $gender){
		if($gender == "Male"){
			if($age<24){return array(0.941523967, 86.45220101, 0.040321528);}
			else if($age<24.5){return array(1.00720807, 86.86160934, 0.040395626);}
			else if($age<25.5){return array(0.837251351, 87.65247282, 0.040577525);}
			else if($age<26.5){return array(0.681492975, 88.42326434, 0.040723122);}
			else if($age<27.5){return array(0.538779654, 89.17549228, 0.040833194);}
			else if($age<28.5){return array(0.407697153, 89.91040853, 0.040909059);}
			else if($age<29.5){return array(0.286762453, 90.62907762, 0.040952433);}
			else if($age<30.5){return array(0.174489485, 91.33242379, 0.04096533);}
			else if($age<31.5){return array(0.069444521, 92.02127167, 0.040949976);}
			else if($age<32.5){return array(-0.029720564, 92.69637946, 0.040908737);}
			else if($age<33.5){return array(-0.124251789, 93.35846546, 0.040844062);}
			else if($age<34.5){return array(-0.215288396, 94.00822923, 0.040758431);}
			else if($age<35.5){return array(-0.30385434, 94.64636981, 0.040654312);}
			else if($age<36.5){return array(-0.390918369, 95.27359106, 0.04053412);}
			else if($age<37.5){return array(-0.254801167, 95.91474929, 0.040572876);}
			else if($age<38.5){return array(-0.125654535, 96.54734328, 0.04061691);}
			else if($age<39.5){return array(-0.00316735, 97.17191309, 0.040666414);}
			else if($age<40.5){return array(0.11291221, 97.78897727, 0.040721467);}
			else if($age<41.5){return array(0.222754969, 98.3990283, 0.040782045);}
			else if($age<42.5){return array(0.326530126, 99.00254338, 0.040848042);}
			else if($age<43.5){return array(0.42436156, 99.599977, 0.040919281);}
			else if($age<44.5){return array(0.516353108, 100.191764, 0.040995524);}
			else if($age<45.5){return array(0.602595306, 100.7783198, 0.041076485);}
			else if($age<46.5){return array(0.683170764, 101.3600411, 0.041161838);}
			else if($age<47.5){return array(0.758158406, 101.9373058, 0.041251224);}
			else if($age<48.5){return array(0.827636736, 102.5104735, 0.041344257);}
			else if($age<49.5){return array(0.891686306, 103.0798852, 0.041440534);}
			else if($age<50.5){return array(0.95039153, 103.645864, 0.041539635);}
			else if($age<51.5){return array(1.003830006, 104.208713, 0.041641136);}
			else if($age<52.5){return array(1.05213569, 104.7687256, 0.041744602);}
			else if($age<53.5){return array(1.0953669, 105.3261638, 0.041849607);}
			else if($age<54.5){return array(1.133652119, 105.8812823, 0.041955723);}
			else if($age<55.5){return array(1.167104213, 106.4343146, 0.042062532);}
			else if($age<56.5){return array(1.195845353, 106.9854769, 0.042169628);}
			else if($age<57.5){return array(1.220004233, 107.534968, 0.042276619);}
			else if($age<58.5){return array(1.239715856, 108.0829695, 0.042383129);}
			else if($age<59.5){return array(1.255121285, 108.6296457, 0.042488804);}
			else if($age<60.5){return array(1.266367398, 109.1751441, 0.042593311);}
			else if($age<61.5){return array(1.273606657, 109.7195954, 0.042696342);}
			else if($age<62.5){return array(1.276996893, 110.2631136, 0.042797615);}
			else if($age<63.5){return array(1.276701119, 110.8057967, 0.042896877);}
			else if($age<64.5){return array(1.272887366, 111.3477265, 0.042993904);}
			else if($age<65.5){return array(1.265728536, 111.8889694, 0.043088503);}
			else if($age<66.5){return array(1.255402281, 112.4295761, 0.043180513);}
			else if($age<67.5){return array(1.242090871, 112.9695827, 0.043269806);}
			else if($age<68.5){return array(1.225981067, 113.5090108, 0.043356287);}
			else if($age<69.5){return array(1.207263978, 114.0478678, 0.043439893);}
			else if($age<70.5){return array(1.186140222, 114.5861486, 0.043520597);}
			else if($age<71.5){return array(1.162796198, 115.1238315, 0.043598407);}
			else if($age<72.5){return array(1.137442868, 115.6608862, 0.043673359);}
			else if($age<73.5){return array(1.110286487, 116.1972691, 0.043745523);}
			else if($age<74.5){return array(1.081536236, 116.732925, 0.043815003);}
			else if($age<75.5){return array(1.05140374, 117.2677879, 0.043881929);}
			else if($age<76.5){return array(1.020102497, 117.8017819, 0.043946461);}
			else if($age<77.5){return array(0.987847213, 118.3348215, 0.044008785);}
			else if($age<78.5){return array(0.954853043, 118.8668123, 0.044069112);}
			else if($age<79.5){return array(0.921334742, 119.397652, 0.044127675);}
			else if($age<80.5){return array(0.887505723, 119.9272309, 0.044184725);}
			else if($age<81.5){return array(0.85357703, 120.455433, 0.044240532);}
			else if($age<82.5){return array(0.819756239, 120.9821362, 0.044295379);}
			else if($age<83.5){return array(0.786246296, 121.5072136, 0.044349559);}
			else if($age<84.5){return array(0.753244292, 122.0305342, 0.044403374);}
			else if($age<85.5){return array(0.720940222, 122.5519634, 0.04445713);}
			else if($age<86.5){return array(0.689515708, 123.0713645, 0.044511135);}
			else if($age<87.5){return array(0.659142731, 123.588599, 0.044565693);}
			else if($age<88.5){return array(0.629997853, 124.1035312, 0.044621104);}
			else if($age<89.5){return array(0.602203984, 124.6160161, 0.044677662);}
			else if($age<90.5){return array(0.575908038, 125.1259182, 0.044735646);}
			else if($age<91.5){return array(0.55123134, 125.6331012, 0.044795322);}
			else if($age<92.5){return array(0.528279901, 126.1374319, 0.044856941);}
			else if($age<93.5){return array(0.507143576, 126.6387804, 0.04492073);}
			else if($age<94.5){return array(0.487895344, 127.1370217, 0.044986899);}
			else if($age<95.5){return array(0.470590753, 127.6320362, 0.045055632);}
			else if($age<96.5){return array(0.455267507, 128.1237104, 0.045127088);}
			else if($age<97.5){return array(0.441945241, 128.6119383, 0.045201399);}
			else if($age<98.5){return array(0.430625458, 129.096622, 0.045278671);}
			else if($age<99.5){return array(0.421291648, 129.5776723, 0.045358979);}
			else if($age<100.5){return array(0.413909588, 130.0550101, 0.045442372);}
			else if($age<101.5){return array(0.408427813, 130.5285669, 0.045528869);}
			else if($age<102.5){return array(0.404778262, 130.9982857, 0.045618459);}
			else if($age<103.5){return array(0.402877077, 131.4641218, 0.045711105);}
			else if($age<104.5){return array(0.402625561, 131.9260439, 0.045806742);}
			else if($age<105.5){return array(0.40391127, 132.3840348, 0.045905281);}
			else if($age<106.5){return array(0.406609232, 132.838092, 0.046006604);}
			else if($age<107.5){return array(0.410583274, 133.2882291, 0.046110573);}
			else if($age<108.5){return array(0.415687443, 133.7344759, 0.046217028);}
			else if($age<109.5){return array(0.421767514, 134.1768801, 0.04632579);}
			else if($age<110.5){return array(0.428662551, 134.6155076, 0.046436662);}
			else if($age<111.5){return array(0.436206531, 135.0504433, 0.04654943);}
			else if($age<112.5){return array(0.44423, 135.4817925, 0.046663871);}
			else if($age<113.5){return array(0.45256176, 135.9096813, 0.046779748);}
			else if($age<114.5){return array(0.461030578, 136.3342577, 0.046896817);}
			else if($age<115.5){return array(0.469466904, 136.7556923, 0.047014827);}
			else if($age<116.5){return array(0.477704608, 137.1741794, 0.047133525);}
			else if($age<117.5){return array(0.48558272, 137.5899378, 0.047252654);}
			else if($age<118.5){return array(0.492947182, 138.0032114, 0.047371961);}
			else if($age<119.5){return array(0.499652617, 138.4142703, 0.047491194);}
			else if($age<120.5){return array(0.505564115, 138.8234114, 0.047610108);}
			else if($age<121.5){return array(0.510559047, 139.2309592, 0.047728463);}
			else if($age<122.5){return array(0.514528903, 139.6372663, 0.04784603);}
			else if($age<123.5){return array(0.517381177, 140.042714, 0.047962592);}
			else if($age<124.5){return array(0.519041285, 140.4477127, 0.048077942);}
			else if($age<125.5){return array(0.519454524, 140.8527022, 0.048191889);}
			else if($age<126.5){return array(0.518588072, 141.2581515, 0.048304259);}
			else if($age<127.5){return array(0.516433004, 141.6645592, 0.048414893);}
			else if($age<128.5){return array(0.513006312, 142.072452, 0.048523648);}
			else if($age<129.5){return array(0.508352901, 142.4823852, 0.048630402);}
			else if($age<130.5){return array(0.502547502, 142.8949403, 0.04873505);}
			else if($age<131.5){return array(0.495696454, 143.3107241, 0.048837504);}
			else if($age<132.5){return array(0.487939275, 143.7303663, 0.048937694);}
			else if($age<133.5){return array(0.479449924, 144.1545167, 0.049035564);}
			else if($age<134.5){return array(0.470437652, 144.5838414, 0.049131073);}
			else if($age<135.5){return array(0.461147305, 145.0190192, 0.049224189);}
			else if($age<136.5){return array(0.451858946, 145.4607359, 0.049314887);}
			else if($age<137.5){return array(0.442886661, 145.9096784, 0.049403145);}
			else if($age<138.5){return array(0.434576385, 146.3665278, 0.049488934);}
			else if($age<139.5){return array(0.427302633, 146.8319513, 0.049572216);}
			else if($age<140.5){return array(0.421464027, 147.3065929, 0.049652935);}
			else if($age<141.5){return array(0.417477538, 147.7910635, 0.049731004);}
			else if($age<142.5){return array(0.415771438, 148.2859294, 0.0498063);}
			else if($age<143.5){return array(0.416777012, 148.7917006, 0.04987865);}
			else if($age<144.5){return array(0.420919142, 149.3088178, 0.049947823);}
			else if($age<145.5){return array(0.428606007, 149.8376391, 0.050013518);}
			else if($age<146.5){return array(0.440218167, 150.3784267, 0.050075353);}
			else if($age<147.5){return array(0.456097443, 150.9313331, 0.050132858);}
			else if($age<148.5){return array(0.476536014, 151.4963887, 0.050185471);}
			else if($age<149.5){return array(0.501766234, 152.0734897, 0.050232532);}
			else if($age<150.5){return array(0.531951655, 152.6623878, 0.050273285);}
			else if($age<151.5){return array(0.567179725, 153.2626819, 0.050306885);}
			else if($age<152.5){return array(0.607456565, 153.8738124, 0.050332406);}
			else if($age<153.5){return array(0.652704121, 154.495058, 0.05034886);}
			else if($age<154.5){return array(0.702759868, 155.1255365, 0.050355216);}
			else if($age<155.5){return array(0.757379106, 155.7642086, 0.050350423);}
			else if($age<156.5){return array(0.816239713, 156.4098858, 0.050333444);}
			else if($age<157.5){return array(0.878947416, 157.0612415, 0.050303283);}
			else if($age<158.5){return array(0.945053486, 157.7168289, 0.050259018);}
			else if($age<159.5){return array(1.014046108, 158.3750929, 0.050199837);}
			else if($age<160.5){return array(1.085383319, 159.034399, 0.050125062);}
			else if($age<161.5){return array(1.158487278, 159.6930501, 0.05003418);}
			else if($age<162.5){return array(1.232768816, 160.3493168, 0.049926861);}
			else if($age<163.5){return array(1.307628899, 161.0014586, 0.049802977);}
			else if($age<164.5){return array(1.382473225, 161.6477515, 0.04966261);}
			else if($age<165.5){return array(1.456720479, 162.2865119, 0.049506051);}
			else if($age<166.5){return array(1.529810247, 162.9161202, 0.049333801);}
			else if($age<167.5){return array(1.601219573, 163.535045, 0.049146553);}
			else if($age<168.5){return array(1.670433444, 164.1418486, 0.04894519);}
			else if($age<169.5){return array(1.736995571, 164.7352199, 0.048730749);}
			else if($age<170.5){return array(1.800483802, 165.3139755, 0.048504404);}
			else if($age<171.5){return array(1.860518777, 165.8770715, 0.048267442);}
			else if($age<172.5){return array(1.916765525, 166.4236087, 0.04802123);}
			else if($age<173.5){return array(1.968934444, 166.9528354, 0.047767192);}
			else if($age<174.5){return array(2.016781776, 167.4641466, 0.047506783);}
			else if($age<175.5){return array(2.060109658, 167.9570814, 0.047241456);}
			else if($age<176.5){return array(2.098765817, 168.4313175, 0.04697265);}
			else if($age<177.5){return array(2.132642948, 168.8866644, 0.046701759);}
			else if($age<178.5){return array(2.16167779, 169.3230548, 0.046430122);}
			else if($age<179.5){return array(2.185849904, 169.7405351, 0.046159004);}
			else if($age<180.5){return array(2.205180153, 170.139255, 0.045889585);}
			else if($age<181.5){return array(2.219728869, 170.5194567, 0.045622955);}
			else if($age<182.5){return array(2.2295937, 170.881464, 0.045360101);}
			else if($age<183.5){return array(2.234907144, 171.2256717, 0.045101913);}
			else if($age<184.5){return array(2.235833767, 171.5525345, 0.044849174);}
			else if($age<185.5){return array(2.232567138, 171.8625576, 0.044602566);}
			else if($age<186.5){return array(2.2253265, 172.1562865, 0.044362674);}
			else if($age<187.5){return array(2.214353232, 172.4342983, 0.044129985);}
			else if($age<188.5){return array(2.199905902, 172.6971935, 0.043904897);}
			else if($age<189.5){return array(2.182262864, 172.9455898, 0.043687723);}
			else if($age<190.5){return array(2.161704969, 173.180112, 0.043478698);}
			else if($age<191.5){return array(2.138524662, 173.4013896, 0.043277987);}
			else if($age<192.5){return array(2.113023423, 173.6100518, 0.043085685);}
			else if($age<193.5){return array(2.085490286, 173.8067179, 0.042901835);}
			else if($age<194.5){return array(2.0562195, 173.9919998, 0.042726424);}
			else if($age<195.5){return array(2.025496648, 174.1664951, 0.042559396);}
			else if($age<196.5){return array(1.993598182, 174.3307855, 0.042400652);}
			else if($age<197.5){return array(1.960789092, 174.4854344, 0.042250063);}
			else if($age<198.5){return array(1.927320937, 174.6309856, 0.042107465);}
			else if($age<199.5){return array(1.89343024, 174.7679617, 0.041972676);}
			else if($age<200.5){return array(1.859337259, 174.8968634, 0.041845488);}
			else if($age<201.5){return array(1.825245107, 175.0181691, 0.041725679);}
			else if($age<202.5){return array(1.791339209, 175.1323345, 0.041613015);}
			else if($age<203.5){return array(1.757787065, 175.2397926, 0.041507249);}
			else if($age<204.5){return array(1.724738292, 175.340954, 0.041408129);}
			else if($age<205.5){return array(1.692324905, 175.4362071, 0.041315398);}
			else if($age<206.5){return array(1.660661815, 175.5259191, 0.041228796);}
			else if($age<207.5){return array(1.629847495, 175.6104358, 0.04114806);}
			else if($age<208.5){return array(1.599964788, 175.690083, 0.041072931);}
			else if($age<209.5){return array(1.571081817, 175.7651671, 0.04100315);}
			else if($age<210.5){return array(1.543252982, 175.8359757, 0.040938463);}
			else if($age<211.5){return array(1.516519998, 175.9027788, 0.040878617);}
			else if($age<212.5){return array(1.490912963, 175.9658293, 0.040823368);}
			else if($age<213.5){return array(1.466451429, 176.0253641, 0.040772475);}
			else if($age<214.5){return array(1.44314546, 176.081605, 0.040725706);}
			else if($age<215.5){return array(1.420996665, 176.1347593, 0.040682834);}
			else if($age<216.5){return array(1.399999187, 176.1850208, 0.04064364);}
			else if($age<217.5){return array(1.380140651, 176.2325707, 0.040607913);}
			else if($age<218.5){return array(1.361403047, 176.2775781, 0.040575448);}
			else if($age<219.5){return array(1.343763564, 176.3202008, 0.040546051);}
			else if($age<220.5){return array(1.327195355, 176.3605864, 0.040519532);}
			else if($age<221.5){return array(1.311668242, 176.3988725, 0.040495713);}
			else if($age<222.5){return array(1.297149359, 176.4351874, 0.040474421);}
			else if($age<223.5){return array(1.283603728, 176.469651, 0.040455493);}
			else if($age<224.5){return array(1.270994782, 176.5023751, 0.040438773);}
			else if($age<225.5){return array(1.25928483, 176.533464, 0.040424111);}
			else if($age<226.5){return array(1.248435461, 176.5630153, 0.040411366);}
			else if($age<227.5){return array(1.23840791, 176.5911197, 0.040400405);}
			else if($age<228.5){return array(1.229163362, 176.6178621, 0.040391101);}
			else if($age<229.5){return array(1.220663228, 176.6433219, 0.040383334);}
			else if($age<230.5){return array(1.212869374, 176.6675729, 0.04037699);}
			else if($age<231.5){return array(1.20574431, 176.6906844, 0.040371962);}
			else if($age<232.5){return array(1.199251356, 176.712721, 0.040368149);}
			else if($age<233.5){return array(1.19335477, 176.733743, 0.040365456);}
			else if($age<234.5){return array(1.188019859, 176.753807, 0.040363795);}
			else if($age<235.5){return array(1.183213059, 176.7729657, 0.04036308);}
			else if($age<236.5){return array(1.178901998, 176.7912687, 0.040363233);}
			else if($age<237.5){return array(1.175055543, 176.8087622, 0.040364179);}
			else if($age<238.5){return array(1.171643828, 176.8254895, 0.04036585);}
			else if($age<239.5){return array(1.16863827, 176.8414914, 0.04036818);}
			else if($age<240){return array(1.167279219, 176.8492322, 0.040369574);}
		}else{
			 if($age<24){return array(1.07244896, 84.97555512, 0.040791394);}
			else if($age<24.5){return array(1.051272912, 85.3973169, 0.040859727);}
			else if($age<25.5){return array(1.041951175, 86.29026318, 0.041142161);}
			else if($age<26.5){return array(1.012592236, 87.15714182, 0.041349399);}
			else if($age<27.5){return array(0.970541909, 87.9960184, 0.041500428);}
			else if($age<28.5){return array(0.921129988, 88.8055115, 0.041610508);}
			else if($age<29.5){return array(0.868221392, 89.58476689, 0.041691761);}
			else if($age<30.5){return array(0.81454413, 90.33341722, 0.04175368);}
			else if($age<31.5){return array(0.761957977, 91.0515436, 0.041803562);}
			else if($age<32.5){return array(0.711660228, 91.7396352, 0.041846882);}
			else if($age<33.5){return array(0.664323379, 92.39854429, 0.041887626);}
			else if($age<34.5){return array(0.620285102, 93.02945392, 0.041928568);}
			else if($age<35.5){return array(0.57955631, 93.63382278, 0.041971514);}
			else if($age<36.5){return array(0.54198094, 94.21335709, 0.042017509);}
			else if($age<37.5){return array(0.511429832, 94.79643239, 0.042104522);}
			else if($age<38.5){return array(0.482799937, 95.37391918, 0.042199507);}
			else if($age<39.5){return array(0.455521041, 95.94692677, 0.042300333);}
			else if($age<40.5){return array(0.429150288, 96.51644912, 0.042405225);}
			else if($age<41.5){return array(0.403351725, 97.08337211, 0.042512706);}
			else if($age<42.5){return array(0.377878239, 97.6484807, 0.042621565);}
			else if($age<43.5){return array(0.352555862, 98.21246579, 0.042730809);}
			else if($age<44.5){return array(0.327270297, 98.77593069, 0.042839638);}
			else if($age<45.5){return array(0.301955463, 99.33939735, 0.042947412);}
			else if($age<46.5){return array(0.276583851, 99.9033122, 0.043053626);}
			else if($age<47.5){return array(0.251158446, 100.4680516, 0.043157889);}
			else if($age<48.5){return array(0.225705996, 101.033927, 0.043259907);}
			else if($age<49.5){return array(0.20027145, 101.6011898, 0.043359463);}
			else if($age<50.5){return array(0.174913356, 102.1700358, 0.043456406);}
			else if($age<51.5){return array(0.149700081, 102.7406094, 0.043550638);}
			else if($age<52.5){return array(0.12470671, 103.3130077, 0.043642107);}
			else if($age<53.5){return array(0.100012514, 103.8872839, 0.043730791);}
			else if($age<54.5){return array(0.075698881, 104.4634511, 0.043816701);}
			else if($age<55.5){return array(0.051847635, 105.0414853, 0.043899867);}
			else if($age<56.5){return array(0.02853967, 105.6213287, 0.043980337);}
			else if($age<57.5){return array(0.005853853, 106.2028921, 0.044058171);}
			else if($age<58.5){return array(-0.016133871, 106.7860583, 0.04413344);}
			else if($age<59.5){return array(-0.037351181, 107.3706841, 0.044206218);}
			else if($age<60.5){return array(-0.057729947, 107.9566031, 0.044276588);}
			else if($age<61.5){return array(-0.077206672, 108.5436278, 0.044344632);}
			else if($age<62.5){return array(-0.09572283, 109.1315521, 0.044410436);}
			else if($age<63.5){return array(-0.113225128, 109.7201531, 0.044474084);}
			else if($age<64.5){return array(-0.129665689, 110.3091934, 0.044535662);}
			else if($age<65.5){return array(-0.145002179, 110.8984228, 0.044595254);}
			else if($age<66.5){return array(-0.159197885, 111.4875806, 0.044652942);}
			else if($age<67.5){return array(-0.172221748, 112.0763967, 0.044708809);}
			else if($age<68.5){return array(-0.184048358, 112.6645943, 0.044762936);}
			else if($age<69.5){return array(-0.194660215, 113.2518902, 0.044815402);}
			else if($age<70.5){return array(-0.204030559, 113.8380006, 0.044866288);}
			else if($age<71.5){return array(-0.212174408, 114.4226317, 0.044915672);}
			else if($age<72.5){return array(-0.219069129, 115.0054978, 0.044963636);}
			else if($age<73.5){return array(-0.224722166, 115.5863089, 0.045010259);}
			else if($age<74.5){return array(-0.229140412, 116.1647782, 0.045055624);}
			else if($age<75.5){return array(-0.232335686, 116.7406221, 0.045099817);}
			else if($age<76.5){return array(-0.234324563, 117.3135622, 0.045142924);}
			else if($age<77.5){return array(-0.235128195, 117.8833259, 0.045185036);}
			else if($age<78.5){return array(-0.234772114, 118.4496481, 0.045226249);}
			else if($age<79.5){return array(-0.233286033, 119.0122722, 0.045266662);}
			else if($age<80.5){return array(-0.230703633, 119.5709513, 0.045306383);}
			else if($age<81.5){return array(-0.227062344, 120.1254495, 0.045345524);}
			else if($age<82.5){return array(-0.222403111, 120.6755427, 0.045384203);}
			else if($age<83.5){return array(-0.216770161, 121.22102, 0.045422551);}
			else if($age<84.5){return array(-0.210210748, 121.7616844, 0.045460702);}
			else if($age<85.5){return array(-0.202774891, 122.2973542, 0.045498803);}
			else if($age<86.5){return array(-0.194515104, 122.827864, 0.045537012);}
			else if($age<87.5){return array(-0.185486099, 123.3530652, 0.045575495);}
			else if($age<88.5){return array(-0.175744476, 123.8728276, 0.045614432);}
			else if($age<89.5){return array(-0.165348396, 124.38704, 0.045654016);}
			else if($age<90.5){return array(-0.15435722, 124.8956114, 0.04569445);}
			else if($age<91.5){return array(-0.142831123, 125.398472, 0.045735953);}
			else if($age<92.5){return array(-0.130830669, 125.895574, 0.045778759);}
			else if($age<93.5){return array(-0.118416354, 126.3868929, 0.045823114);}
			else if($age<94.5){return array(-0.105648092, 126.8724284, 0.04586928);}
			else if($age<95.5){return array(-0.092584657, 127.3522056, 0.045917535);}
			else if($age<96.5){return array(-0.079283065, 127.8262759, 0.045968169);}
			else if($age<97.5){return array(-0.065797888, 128.2947187, 0.04602149);}
			else if($age<98.5){return array(-0.0521805, 128.757642, 0.046077818);}
			else if($age<99.5){return array(-0.03847825, 129.2151839, 0.046137487);}
			else if($age<100.5){return array(-0.024733545, 129.6675143, 0.046200842);}
			else if($age<101.5){return array(-0.010982868, 130.1148354, 0.04626824);}
			else if($age<102.5){return array(0.002744306, 130.5573839, 0.046340046);}
			else if($age<103.5){return array(0.016426655, 130.995432, 0.046416629);}
			else if($age<104.5){return array(0.030052231, 131.4292887, 0.046498361);}
			else if($age<105.5){return array(0.043619747, 131.8593015, 0.046585611);}
			else if($age<106.5){return array(0.05713988, 132.2858574, 0.046678741);}
			else if($age<107.5){return array(0.070636605, 132.7093845, 0.046778099);}
			else if($age<108.5){return array(0.08414848, 133.1303527, 0.04688401);}
			else if($age<109.5){return array(0.097729873, 133.5492749, 0.046996769);}
			else if($age<110.5){return array(0.111452039, 133.9667073, 0.047116633);}
			else if($age<111.5){return array(0.125404005, 134.3832499, 0.047243801);}
			else if($age<112.5){return array(0.13969316, 134.7995463, 0.047378413);}
			else if($age<113.5){return array(0.154445482, 135.2162826, 0.047520521);}
			else if($age<114.5){return array(0.169805275, 135.634186, 0.047670085);}
			else if($age<115.5){return array(0.185934346, 136.0540223, 0.047826946);}
			else if($age<116.5){return array(0.203010488, 136.4765925, 0.04799081);}
			else if($age<117.5){return array(0.2212252, 136.9027281, 0.048161228);}
			else if($age<118.5){return array(0.240780542, 137.3332846, 0.04833757);}
			else if($age<119.5){return array(0.261885086, 137.7691339, 0.048519011);}
			else if($age<120.5){return array(0.284748919, 138.2111552, 0.048704503);}
			else if($age<121.5){return array(0.309577733, 138.6602228, 0.048892759);}
			else if($age<122.5){return array(0.336566048, 139.1171933, 0.049082239);}
			else if($age<123.5){return array(0.365889711, 139.5828898, 0.049271137);}
			else if($age<124.5){return array(0.397699038, 140.0580848, 0.049457371);}
			else if($age<125.5){return array(0.432104409, 140.5434787, 0.049638596);}
			else if($age<126.5){return array(0.46917993, 141.0396832, 0.049812203);}
			else if($age<127.5){return array(0.508943272, 141.5471945, 0.049975355);}
			else if($age<128.5){return array(0.551354277, 142.0663731, 0.050125012);}
			else if($age<129.5){return array(0.596307363, 142.59742, 0.050257992);}
			else if($age<130.5){return array(0.643626542, 143.1403553, 0.050371024);}
			else if($age<131.5){return array(0.693062173, 143.6949981, 0.050460835);}
			else if($age<132.5){return array(0.744289752, 144.2609497, 0.050524236);}
			else if($age<133.5){return array(0.79691098, 144.8375809, 0.050558224);}
			else if($age<134.5){return array(0.85045728, 145.4240246, 0.050560083);}
			else if($age<135.5){return array(0.904395871, 146.0191748, 0.050527494);}
			else if($age<136.5){return array(0.958138449, 146.621692, 0.050458634);}
			else if($age<137.5){return array(1.011054559, 147.2300177, 0.050352269);}
			else if($age<138.5){return array(1.062474568, 147.8423918, 0.050207825);}
			else if($age<139.5){return array(1.111727029, 148.4568879, 0.050025434);}
			else if($age<140.5){return array(1.158135105, 149.0714413, 0.049805967);}
			else if($age<141.5){return array(1.201050821, 149.6838943, 0.049551023);}
			else if($age<142.5){return array(1.239852328, 150.2920328, 0.049262895);}
			else if($age<143.5){return array(1.274006058, 150.8936469, 0.048944504);}
			else if($age<144.5){return array(1.303044695, 151.4865636, 0.048599314);}
			else if($age<145.5){return array(1.326605954, 152.0686985, 0.048231224);}
			else if($age<146.5){return array(1.344443447, 152.6380955, 0.047844442);}
			else if($age<147.5){return array(1.356437773, 153.1929631, 0.047443362);}
			else if($age<148.5){return array(1.362602695, 153.7317031, 0.04703243);}
			else if($age<149.5){return array(1.363085725, 154.2529332, 0.046616026);}
			else if($age<150.5){return array(1.358162799, 154.755501, 0.046198356);}
			else if($age<151.5){return array(1.348227142, 155.2384904, 0.04578335);}
			else if($age<152.5){return array(1.333772923, 155.7012216, 0.045374597);}
			else if($age<153.5){return array(1.315374704, 156.1432438, 0.044975281);}
			else if($age<154.5){return array(1.293664024, 156.564323, 0.044588148);}
			else if($age<155.5){return array(1.269304678, 156.9644258, 0.044215488);}
			else if($age<156.5){return array(1.242968236, 157.3436995, 0.043859135);}
			else if($age<157.5){return array(1.21531127, 157.7024507, 0.04352048);}
			else if($age<158.5){return array(1.186955477, 158.0411233, 0.043200497);}
			else if($age<159.5){return array(1.158471522, 158.3602756, 0.042899776);}
			else if($age<160.5){return array(1.130367088, 158.6605588, 0.042618565);}
			else if($age<161.5){return array(1.103079209, 158.9426964, 0.042356812);}
			else if($age<162.5){return array(1.076970655, 159.2074654, 0.042114211);}
			else if($age<163.5){return array(1.052329922, 159.455679, 0.041890247);}
			else if($age<164.5){return array(1.029374161, 159.688172, 0.04168424);}
			else if($age<165.5){return array(1.008254396, 159.9057871, 0.041495379);}
			else if($age<166.5){return array(0.989062282, 160.1093647, 0.041322765);}
			else if($age<167.5){return array(0.971837799, 160.299733, 0.041165437);}
			else if($age<168.5){return array(0.95657215, 160.4776996, 0.041022401);}
			else if($age<169.5){return array(0.94324228, 160.6440526, 0.040892651);}
			else if($age<170.5){return array(0.931767062, 160.7995428, 0.040775193);}
			else if($age<171.5){return array(0.922058291, 160.9448916, 0.040669052);}
			else if($age<172.5){return array(0.914012643, 161.0807857, 0.040573288);}
			else if($age<173.5){return array(0.907516917, 161.2078755, 0.040487005);}
			else if($age<174.5){return array(0.902452436, 161.3267744, 0.040409354);}
			else if($age<175.5){return array(0.898698641, 161.4380593, 0.040339537);}
			else if($age<176.5){return array(0.896143482, 161.5422726, 0.040276811);}
			else if($age<177.5){return array(0.894659668, 161.639917, 0.040220488);}
			else if($age<178.5){return array(0.89413892, 161.7314645, 0.040169932);}
			else if($age<179.5){return array(0.894475371, 161.8173534, 0.040124562);}
			else if($age<180.5){return array(0.895569834, 161.8979913, 0.040083845);}
			else if($age<181.5){return array(0.897330209, 161.9737558, 0.040047295);}
			else if($age<182.5){return array(0.899671635, 162.0449969, 0.040014473);}
			else if($age<183.5){return array(0.902516442, 162.1120386, 0.03998498);}
			else if($age<184.5){return array(0.905793969, 162.17518, 0.039958458);}
			else if($age<185.5){return array(0.909440266, 162.2346979, 0.039934584);}
			else if($age<186.5){return array(0.913397733, 162.2908474, 0.039913066);}
			else if($age<187.5){return array(0.91761471, 162.343864, 0.039893644);}
			else if($age<188.5){return array(0.922045055, 162.3939652, 0.039876087);}
			else if($age<189.5){return array(0.926647697, 162.4413513, 0.039860185);}
			else if($age<190.5){return array(0.931386217, 162.4862071, 0.039845754);}
			else if($age<191.5){return array(0.93622842, 162.5287029, 0.039832629);}
			else if($age<192.5){return array(0.941145943, 162.5689958, 0.039820663);}
			else if($age<193.5){return array(0.94611388, 162.6072309, 0.039809725);}
			else if($age<194.5){return array(0.95111043, 162.6435418, 0.0397997);}
			else if($age<195.5){return array(0.956116576, 162.6780519, 0.039790485);}
			else if($age<196.5){return array(0.961115792, 162.7108751, 0.039781991);}
			else if($age<197.5){return array(0.966093766, 162.7421168, 0.039774136);}
			else if($age<198.5){return array(0.971038162, 162.7718741, 0.03976685);}
			else if($age<199.5){return array(0.975938391, 162.8002371, 0.03976007);}
			else if($age<200.5){return array(0.980785418, 162.8272889, 0.039753741);}
			else if($age<201.5){return array(0.985571579, 162.8531067, 0.039747815);}
			else if($age<202.5){return array(0.99029042, 162.8777619, 0.039742249);}
			else if($age<203.5){return array(0.994936555, 162.9013208, 0.039737004);}
			else if($age<204.5){return array(0.999505539, 162.9238449, 0.039732048);}
			else if($age<205.5){return array(1.003993753, 162.9453912, 0.039727352);}
			else if($age<206.5){return array(1.0083983, 162.9660131, 0.03972289);}
			else if($age<207.5){return array(1.012716921, 162.9857599, 0.03971864);}
			else if($age<208.5){return array(1.016947912, 163.0046776, 0.039714581);}
			else if($age<209.5){return array(1.021090055, 163.0228094, 0.039710697);}
			else if($age<210.5){return array(1.025142554, 163.0401953, 0.039706971);}
			else if($age<211.5){return array(1.029104983, 163.0568727, 0.039703391);}
			else if($age<212.5){return array(1.032977233, 163.0728768, 0.039699945);}
			else if($age<213.5){return array(1.036759475, 163.0882404, 0.039696623);}
			else if($age<214.5){return array(1.040452117, 163.1029943, 0.039693415);}
			else if($age<215.5){return array(1.044055774, 163.1171673, 0.039690313);}
			else if($age<216.5){return array(1.047571238, 163.1307866, 0.039687311);}
			else if($age<217.5){return array(1.050999451, 163.1438776, 0.039684402);}
			else if($age<218.5){return array(1.054341482, 163.1564644, 0.039681581);}
			else if($age<219.5){return array(1.057598512, 163.1685697, 0.039678842);}
			else if($age<220.5){return array(1.060771808, 163.1802146, 0.039676182);}
			else if($age<221.5){return array(1.063862715, 163.1914194, 0.039673596);}
			else if($age<222.5){return array(1.066872639, 163.202203, 0.039671082);}
			else if($age<223.5){return array(1.069803036, 163.2125835, 0.039668635);}
			else if($age<224.5){return array(1.072655401, 163.2225779, 0.039666254);}
			else if($age<225.5){return array(1.075431258, 163.2322024, 0.039663936);}
			else if($age<226.5){return array(1.078132156, 163.2414722, 0.039661679);}
			else if($age<227.5){return array(1.080759655, 163.2504019, 0.039659481);}
			else if($age<228.5){return array(1.083315329, 163.2590052, 0.039657339);}
			else if($age<229.5){return array(1.085800751, 163.2672954, 0.039655252);}
			else if($age<230.5){return array(1.088217496, 163.2752848, 0.039653218);}
			else if($age<231.5){return array(1.090567133, 163.2829854, 0.039651237);}
			else if($age<232.5){return array(1.092851222, 163.2904086, 0.039649306);}
			else if($age<233.5){return array(1.095071313, 163.297565, 0.039647424);}
			else if($age<234.5){return array(1.097228939, 163.304465, 0.039645591);}
			else if($age<235.5){return array(1.099325619, 163.3111185, 0.039643804);}
			else if($age<236.5){return array(1.101362852, 163.3175349, 0.039642063);}
			else if($age<237.5){return array(1.103342119, 163.3237231, 0.039640367);}
			else if($age<238.5){return array(1.105264876, 163.3296918, 0.039638715);}
			else if($age<239.5){return array(1.107132561, 163.3354491, 0.039637105);}
			else if($age<240){return array(1.108046193, 163.338251, 0.039636316);}
		}
	}
	
	//@Luke
	//returns L, M, S of BMI
	private static function getBmiLMS($age, $gender){
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
        $mdate = new DateTime($facette->MEDICAL_DATE);
        $birth = new DateTime($facette->DATE_BIRTH);
        $aid = $mdate->diff($birth)->format('%a days');
        $ageDecimal = (($aid / 365.5) * 12);
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
                case "Z_SCORES";
                	$data[$i]["BMI_Z_SCORE"] = setshowText($value->BMI_Z_SCORE);
                    $data[$i]["WT_Z_SCORE"] = setshowText($value->WT_Z_SCORE);
                    $data[$i]["HT_Z_SCORE"] = setshowText($value->HT_Z_SCORE);
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
                    case "Z_SCORES":
                        $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                        $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                        $data[$i]["BMI_Z_SCORE"] = setShowText($value->BMI_Z_SCORE);
                        $data[$i]["WT_Z_SCORE"] =  setShowText($value->WT_Z_SCORE);
                        $data[$i]["HT_Z_SCORE"] =  setShowText($value->HT_Z_SCORE);
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
		
		//BMI zscore
		$meanStd = self::getBmiLMS($age, $gender);		
		
		$L = $meanStd[0];
		$M  = $meanStd[1];
		$S = $meanStd[2];
		$value = round (((($score / $M) ** $L) - 1) / ($S * $L), 2);
        if ($value) {
            $data = array();
            $data['BMI_Z_SCORE']   = "$value";
            self::dbAccess()->update("t_student_medical", $data, "ID='". $facette->ID ."'");
        }
		
		//Weight zscore
		$meanStd = self::getWtLMS($age, $gender);	
		$L = $meanStd[0];
		$M  = $meanStd[1];
		$S = $meanStd[2];
		$value = round (((($facette->WEIGHT / $M) ** $L) - 1) / ($S * $L), 2);
        if ($value) {
            $data = array();
            $data['WT_Z_SCORE']   = "$value";
            self::dbAccess()->update("t_student_medical", $data, "ID='". $facette->ID ."'");
        }
		
		//Height zscore
		$meanStd = self::getHtLMS($age, $gender);
		$L = $meanStd[0];
		$M  = $meanStd[1];
		$S = $meanStd[2];
		$value = round (((($facette->HEIGHT / $M) ** $L) - 1) / ($S * $L), 2);
        if ($value) {
            $data = array();
            $data['HT_Z_SCORE']   = "$value";
            self::dbAccess()->update("t_student_medical", $data, "ID='". $facette->ID ."'");
        }
    }

    public static function calculationBMIStatus($value) {
        /**
          Underweight = <18.5
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