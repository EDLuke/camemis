<?php
class CAMEMISLog {
    public $name;

    function CAMEMISLog($name) {
        $this->name = $name;
    }
    
    function database($text) {
        if(CAMEMISConfigBasic::LOGLEVEL_DATABASE) {
            //$this->log("DB   ", $text);
        }
    }

    function debug($text) {
        if(CAMEMISConfigBasic::LOGLEVEL_DEBUG) {
            //$this->log("DEBUG", $text);
        }
    }

    function info($text) {
        if(CAMEMISConfigBasic::LOGLEVEL_INFO) {
            //$this->log("INFO ", $text);
        }
    }

    function warn($text) {
        if(CAMEMISConfigBasic::LOGLEVEL_WARN) {
            //$this->log("* WARN *", $text);
        }
    }

    function error($text) {
        if(CAMEMISConfigBasic::LOGLEVEL_ERROR) {
            //$this->log("* ERROR *", $text);
        }
        if(CAMEMISConfigBasic::EMAIL_ON_ERROR) {
            //error_log("ERROR ".date("ymdHi")." - ".$this->name."\n".$text, 1, CAMEMISConfigBasic::EMAIL_ON_ERROR);
        }
        if(CAMEMISConfigBasic::DIE_ON_ERROR) {
            die("<b>ERROR in ".$this->name.":</b><br/>".nl2br($text));
        }
    }

    function staticDebug($text) {
        $errorText = "[DEBUG] ".date("ymd H:i:s")." STATIC - ".$text."\n";
        //error_log($errorText, 3, CAMEMISConfigBasic::LOG_FILE);
    }

    function log($level, $text) {
        //$errorText = "[".$level."] ".date("ymd H:i:s")." ".$this->name." - ".$text."\n";
        $errorText = "[".$level."]";

        //error_log($errorText, 3, CAMEMISConfigBasic::LOG_FILE);
    }

    function separator($count = 1) {
        for($i=0; $i<$count; $i++) {
            //error_log("\n", 3, CAMEMISConfigBasic::LOG_FILE);
        }
    }

    function deleteLogfile() {
        //$this->info("*** DELETING LOGFILE ***");
        //unlink(CAMEMISConfigBasic::LOG_FILE);
    }
}
?>