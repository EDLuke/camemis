<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.02.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'include/Common.inc.php';
require_once 'Zend/Date.php';

define("T_USER", "t_user");
define("T_SESSIONS", "t_sessions");

class AdminSessionDBAccess {

    public $sessionId;

    function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
    }

    public function setSessionId($value) {

        $this->sessionId = $value;
    }

    public function getSession() {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM " . T_SESSIONS . "";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $this->sessionId . "'";
        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result;
    }

    public function createSession($newSessionId, $userId, $keyId) {

        $DATA = array(
            'ID' => $newSessionId
            , 'USER_ID' => $userId
            , 'TS_UPDATE' => time()
            , 'KEY' => $keyId
        );
        
        $this->DB_ACCESS->insert(T_SESSIONS, $DATA);

        return $newSessionId;
    }
    
    public function setSpecialKey($value){
        return $value;
    }

    public function resetTime() {

        $TABLE = T_SESSIONS;
        $RECORD = array(
            'TS_UPDATE' => time()
        );
        $ROW = $this->DB_ACCESS->quoteInto('ID =?', $this->sessionId);
        $rowsAffected = $this->DB_ACCESS->update($TABLE, $RECORD, $ROW);

        return $rowsAffected;
    }

    public function cleanUp() {

        $current = time();
        $still_valid = $current - CAMEMISConfigBasic::EXPIRE_TIME;
        $this->DB_ACCESS->delete(T_SESSIONS, "TS_UPDATE<'" . $still_valid . "'");
    }

    public function verifyTime() {

        $current = time();

        $facette = $this->getSession();

        if (isset($facette->TS_UPDATE)) {
            $ts_update = $facette->TS_UPDATE;

            if ($ts_update + CAMEMISConfigBasic::EXPIRE_TIME < $current)
                return false;
            else
                return true;
        }
    }

}

?>