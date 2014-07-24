<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.02.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'include/Common.inc.php';
require_once 'Zend/Date.php';

class SessionAccess {

    protected $db = null;
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

    public static function dbAdminAccess() {
        return Zend_Registry::get('ADMIN_DB_ACCESS');
    }

    public static function dataSession($sessionId) {
        $SQL = "SELECT * FROM t_sessions WHERE ID = '" . $sessionId . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function dataSessionByUser($userId) {
        $SQL = "SELECT * FROM t_sessions WHERE MEMBERS_ID = '" . $userId . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function createSession($sessionid, $memberid, $loginUser, $userRole, $schoolId, $isSuperAdim) {
        $SQL = "";
        $SQL .= "INSERT INTO t_sessions";
        $SQL .= " SET";
        $SQL .= " ID = '" . $sessionid . "',";
        $SQL .= " MEMBERS_ID = '" . $memberid . "',";
        $SQL .= " LOGIN_USER = '" . $loginUser . "',";
        $SQL .= " USER_ROLE = '" . $userRole . "',";
        $SQL .= " LOGIN_DATE = '" . getCurrentDBDateTime() . "',";
        $SQL .= " ISSUPERADMIN = '" . $isSuperAdim . "',";
        if ($schoolId)
            $SQL .= " SCHOOL_ID = '" . $schoolId . "',";
        $SQL .= " TS_UPDATE = " . time() . "";

        self::dbAccess()->query($SQL);

        if ($isSuperAdim)
            $userRole = 1;

        if (!$isSuperAdim)
            $this->setLoginInfo($sessionid, $userRole);

        return;
    }

    public function resetTime($sessionId) {
        $SQL = "UPDATE t_sessions SET TS_UPDATE='" . time() . "' WHERE ID='" . $sessionId . "'";
        self::dbAccess()->query($SQL);
    }

    public function cleanUp() {

        $current = time();
        $still_valid = $current - CAMEMISConfigBasic::EXPIRE_TIME;

        self::dbAccess()->delete("t_sessions", " TS_UPDATE<'" . $still_valid . "'");
    }

    public function verifyTimeByUser($userId) {
        $current = time();

        $sessionObject = self::dataSessionByUser($userId);

        if ($sessionObject) {
            $ts_update = $sessionObject->TS_UPDATE;
            if ($ts_update + CAMEMISConfigBasic::EXPIRE_TIME < $current) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function verifyTime($sessionId) {
        $current = time();

        $data = self::dataSession($sessionId);

        if (isset($data->TS_UPDATE)) {
            $ts_update = $data->TS_UPDATE;

            if ($ts_update + CAMEMISConfigBasic::EXPIRE_TIME < $current)
                return false;
            else
                return true;
        }
    }

    protected function setLoginInfo($sessionId, $userRole) {

        $memberAccess = UserDBAccess::getInstance();
        $memberObject = $memberAccess->getMemberBySessionId($sessionId);

        $currentDate = getCurrentDBDateTime();

        $SAVE_DATA["ROLE"] = $userRole;
        $SAVE_DATA["IP"] = getenv("REMOTE_ADDR");
        $SAVE_DATA["LOGINNAME"] = $memberObject->LOGINNAME;
        $SAVE_DATA["DATE"] = $currentDate;
        self::dbAccess()->insert('t_logininfo', $SAVE_DATA);
    }

    public function checkCountSessions($memberId) {

        $SQL = "SELECT COUNT(*) AS C FROM t_sessions";
        $SQL .= " WHERE MEMBERS_ID = '" . $memberId . "' AND ISSUPERADMIN=0";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function isStudentLogin($sessionId) {

        $SQL = "SELECT COUNT(*) AS C FROM t_sessions";
        $SQL .= " WHERE ID = '" . $sessionId . "' AND USER_ROLE=4";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function actionLogoutWarning() {

        $SQL = "SELECT * FROM t_sessions";
        $SQL .= " WHERE MEMBERS_ID = '" . Zend_Registry::get('USERID') . "'";
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
            foreach ($result as $value) {

                if ($value->ID != addText(Zend_Registry::get('SESSIONID'))) {
                    $condition = array(
                        'ID = ? ' => $value->ID
                    );
                    self::dbAccess()->delete("t_sessions", $condition);
                }
            }
    }

    public function jsonUserOnline($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $current = time();
        $still_valid = $current - CAMEMISConfigBasic::EXPIRE_TIME;

        $SQL = "";
        $SQL .= " SELECT A.ID AS ID, LOGIN_USER AS LOGIN_USER, A.LOGIN_DATE AS LOGIN_DATE";
        $SQL .= " FROM t_sessions AS A";
        $SQL .= " WHERE A.ISSUPERADMIN=0";
        $SQL .= " AND A.TS_UPDATE>'" . $still_valid . "'";
        $SQL .= " GROUP BY A.MEMBERS_ID ORDER BY A.LOGIN_DATE DESC";

        //echo $SQL;
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                if ($value->LOGIN_USER) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["LOGIN_USER"] = setShowText($value->LOGIN_USER);
                    $data[$i]["LOGIN_DATE"] = getShowDateTime($value->LOGIN_DATE);
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

    public function jsonUserOnlineActivity($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $SQL = "";
        $SQL .= " SELECT
            A.ID AS ID
            ,A.LOGINNAME AS LOGINNAME
            ,A.DATE AS DATE
            ,A.DATE_END AS DATE_END
            ,A.IP AS IP
            ";
        $SQL .= " FROM t_logininfo AS A";
        $SQL .= " ORDER BY A.DATE DESC";

        //echo $SQL;

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                if ($value->LOGINNAME) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["LOGINNAME"] = setShowText($value->LOGINNAME);
                    $data[$i]["START_DATE"] = getShowDateTime($value->DATE);
                    if ($value->DATE_END) {
                        $data[$i]["END_DATE"] = getShowDateTime($value->DATE_END);
                    } else {
                        $data[$i]["END_DATE"] = "---";
                    }
                    $data[$i]["IP"] = setShowText($value->IP);
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

    public function jsonDeleteUserOnline($params) {
        $Id = isset($params["Id"]) ? addText($params["Id"]) : "0";
        if ($Id) {
            $condition = array(
                'ID = ? ' => $Id
            );
            self::dbAccess()->delete("t_sessions", $condition);
        }
        return array("success" => true);
    }

    public static function makeModulRegistry() {

        Zend_Registry::set('ZEND_REGISTRY', Zend_Registry::getInstance());
        Zend_Registry::set('APPLICATION_TYPE', CAMEMISConfigBasic::APPLICATION_TYPE);
        Zend_Registry::set('APPLICATION_DEMO', CAMEMISModulConfig::APPLICATION_DEMO);
        Zend_Registry::set('MULTI_SYSTEM_LANGUAGE', CAMEMISModulConfig::MULTI_SYSTEM_LANGUAGE);
    }

    public static function makeUserRegistry($MEMBER_OBJECT, $ACL_DATA, $USER_ROLE_OBJECT, $SESSION_OBJECT) {

        Zend_Registry::set('SESSIONID', $SESSION_OBJECT->ID);
        Zend_Registry::set('USER', $MEMBER_OBJECT);
        Zend_Registry::set('USERID', $MEMBER_OBJECT->ID);
        Zend_Registry::set('ROLE', $MEMBER_OBJECT->ROLE);
        Zend_Registry::set('LESSON_COUNT', Zend_Registry::get('SESSION_OBJECT')->checkCountSessions($MEMBER_OBJECT->ID));

        if ($SESSION_OBJECT->ISSUPERADMIN == 1) {
            Zend_Registry::set('IS_SUPER_ADMIN', 1);
        } else {
            Zend_Registry::set('IS_SUPER_ADMIN', 0);
        }
        self::makeGeneralUserEduRegistry($MEMBER_OBJECT, $ACL_DATA, $USER_ROLE_OBJECT, $SESSION_OBJECT);
    }

    public static function makeSchoolRegistry($schoolObject, $SESSION_OBJECT) {

        Zend_Registry::set('SCHOOL_ID', $schoolObject->ID);
        Zend_Registry::set('SCHOOL', $schoolObject);
        Zend_Registry::set('IS_PROVIDER', $SESSION_OBJECT->ISSUPERADMIN);
        Zend_Registry::set('URL_MAIN', "main?_sid=" . $SESSION_OBJECT->ID . "");
    }

    public static function makeGeneralUserEduRegistry($MEMBER_OBJECT, $ACL_DATA, $USER_ROLE_OBJECT, $SESSION_OBJECT) {

        Zend_Registry::set('SKIN', "BLUE");
        Zend_Registry::set('ADDITIONAL_ROLE', false);
        Zend_Registry::set('MODUL_NAME', CAMEMISModulConfig::ME_NAME);

        if (is_numeric($MEMBER_OBJECT->ROLE)) {
            switch ($MEMBER_OBJECT->ROLE) {
                case 1:
                    Zend_Registry::set('ADDITIONAL_ROLE', $MEMBER_OBJECT->ADDITIONAL_ROLE);
                    if ($SESSION_OBJECT->ISSUPERADMIN == 1) {
                        Zend_Registry::set('SUPER_ADMIN', true);
                    }
                    break;
                case 2:
                    Zend_Registry::set('USER_TYPE', "INSTRUCTOR");
                    Zend_Registry::set('INSTRUCTOR_ID', $MEMBER_OBJECT->ID);
                    Zend_Registry::set('TEACHER_ID', $MEMBER_OBJECT->ID);
                    break;
                case 3:
                    Zend_Registry::set('USER_TYPE', "TEACHER");
                    Zend_Registry::set('TEACHER_ID', $MEMBER_OBJECT->ID);
                    break;
            }
            Zend_Registry::set('ACL_DATA', $ACL_DATA);
            Zend_Registry::set('USER_ROLE', $USER_ROLE_OBJECT->NAME);
        } else {
            switch ($MEMBER_OBJECT->ROLE) {
                case "STUDENT":
                    Zend_Registry::set('SKIN', "BLUE");
                    Zend_Registry::set('ISDEMO', false);
                    Zend_Registry::set('LOGIN_USER', 1);
                    Zend_Registry::set('USER_TYPE', "STUDENT");
                    Zend_Registry::set('ACL_DATA', array());
                    break;
                case "GUARDIAN":
                    Zend_Registry::set('SKIN', "BLUE");
                    Zend_Registry::set('ISDEMO', false);
                    Zend_Registry::set('LOGIN_USER', 1);
                    Zend_Registry::set('USER_TYPE', "GUARDIAN");
                    Zend_Registry::set('ACL_DATA', array());
                    break;
            }
        }
    }

}

?>