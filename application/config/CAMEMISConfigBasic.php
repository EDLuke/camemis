<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.07.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class CAMEMISConfigBasic {
    const MULTI_DOMAIN = true;

    const APPLICATION_TYPE = "SCHOOL";
    const CAMEMIS_URL = "";
    const EXTJS_VERSION = "ext-3.4.0";

    const EXPIRE_TIME = 3800;
    const LOG_FILE = "";
    const LOGLEVEL_DATABASE = true;
    const LOGLEVEL_DEBUG = true;
    const LOGLEVEL_INFO = true;
    const LOGLEVEL_WARN = true;
    const LOGLEVEL_ERROR = true;
    const DELETE_LOGFILE_ON_LOGIN = false;
    const SHOW_DEBUG_DIV = true;
    const EMAIL_ON_ERROR = false;
    const DIE_ON_ERROR = true;

    const IS_SEND_SMS = true;

    static function getCode($value) {

        switch ($value) {
            case "0b841617ffaf67b13f8b3c3cffe35639":
                $callback = "root";
                break;
            case "f62e9a8a5208b62fa5cc57c3e2d05b84":
                $callback = "localhost";
                break;
            case "5f09ca9317a700c4bc37ecddcd6879ef":
                $callback = "mon1ta911";
                break;
            case "93a57374384abd91b59b1dbab0f91e75":
                $callback = "sqladmin";
                break;
            case "FD5214F6-552D-4D0E-B1CF-9D23DC468-128":
                //$callback = "khmer1312";
                $callback = "";
                break;
            default:
                $callback = "93a57374384a";
                break;
        }

        return $callback;
    }
}

class CAMEMISUrlConfig {
    
    static function getCountryEducation() {
        return strtolower(Zend_Registry::get('SYSTEM_COUNTRY'));
    }
    
    static function getIncludeUrl($type, $file, $default=false) {
        
        $SYSTEM_COUNTRY = strtolower(Zend_Registry::get('SYSTEM_COUNTRY'));
        
        if ($default){
            $SYSTEM_COUNTRY = "default";
        }
        
        switch ($type) {
            case "EVALUATION":
                $test = "models/" . Zend_Registry::get('MODUL_API_PATH') . "/evaluation/" . $SYSTEM_COUNTRY . '/' . $file;
                return $test;
                break;
            case "REPORTING":
                return "models/" . Zend_Registry::get('MODUL_API_PATH') . "/reporting/" . $SYSTEM_COUNTRY . '/' . $file;
                break;
            default:
                break;
        }
    }
}

?>