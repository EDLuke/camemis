<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 17.10.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/SessionAccess.php';
require_once setUserLoacalization();

class UserActivityDBAccess {

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

    protected function getSQLUserActivity($params) {

        $userId = isset($params["userId"]) ? addText($params["userId"]) : "";
        $start_date = isset($params["start_date"]) ? addText($params["start_date"]) : "";
        $end_date = isset($params["end_date"]) ? addText($params["end_date"]) : "";

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " CONCAT(B.LASTNAME, ' ', B.FIRSTNAME) AS USER_NAME";
        $SQL .= " ,A.CODE AS CODE";
        $SQL .= " ,A.ACTIVITY_DATE AS ACTIVITY_DATE";
        $SQL .= " ,A.CONTENT AS CONTENT";
        $SQL .= " FROM t_user_activity AS A";
        $SQL .= " LEFT JOIN t_staff AS B ON B.ID = A.USER";
        $SQL .= " WHERE 1=1";

        if ($userId) {
            $SQL .= " AND A.USER = '" . $userId . "'";
        }

        if ($start_date) {
            $SQL .= " AND A.ACTIVITY_DATE >= '" . $start_date . "'";
        }

        if ($end_date) {
            $SQL .= " AND A.ACTIVITY_DATE <= '" . $end_date . "'";
        }

        if ($start_date && $end_date) {
            $SQL .= " AND '" . $start_date . "'<=A.ACTIVITY_DATE<='" . $end_date . "'";
        }
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function searchUserActivity($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $result = $this->getSQLUserActivity($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["USER"] = $value->USER_NAME . " (" . $value->CODE . ")";
                $data[$i]["ACTIVITY_DATE"] = "<b>" . getShowDateTime($value->ACTIVITY_DATE) . "</b>";
                $data[$i]["CONTENT"] = $value->CONTENT;

                $i++;
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $this->out = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
        return $this->out;
    }

    public static function jsonUserOnline($params) {

        $DB_SESSION = SessionAccess::getInstance();

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $firstName = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $code = isset($params["code"]) ? addText($params["code"]) : "";
        $startdt = isset($params["startdt"]) ? addText($params["startdt"]) : "";
        $enddt = isset($params["enddt"]) ? addText($params["enddt"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";

        switch ($type) {
            case "search":
                $onlyUserOnline = false;
                $SQL = "SELECT C.CODE AS CODE, C.ID AS ID, CONCAT(C.LASTNAME, ' ', C.FIRSTNAME) as USER_LOGIN , A.DATE AS ACTIVITY_DATE, B.NAME AS USER_ROLE FROM t_logininfo AS A";
                $SQL .= " LEFT JOIN t_memberrole AS B ON B.ID = A.ROLE";
                $SQL .= " LEFT JOIN t_members AS C ON C.LOGINNAME = A.LOGINNAME";
                $SQL .= " WHERE 1=1";

                if ($firstName)
                    $SQL .= " AND C.FIRSTNAME LIKE '" . $firstName . "%'";

                if ($lastname)
                    $SQL .= " AND C.LASTNAME LIKE '" . $lastname . "%'";

                if ($code)
                    $SQL .= " AND C.CODE LIKE '" . strtoupper($code) . "%' ";

                if ($startdt && $enddt)
                    $SQL .= " AND A.DATE >= '" . $startdt . "' AND A.DATE <= '" . $enddt . "'";

                $SQL .= " ORDER BY A.DATE DESC";

                //error_log($SQL);
                $result = self::dbAccess()->fetchAll($SQL);
                break;
            case "live":
                $onlyUserOnline = true;
                $SQL = "SELECT A.MEMBERS_ID AS ID, CONCAT(B.LASTNAME, ' ', B.FIRSTNAME) AS USER_LOGIN, A.LOGIN_DATE AS ACTIVITY_DATE, C.NAME AS USER_ROLE FROM t_sessions AS A";
                $SQL .= " LEFT JOIN t_members AS B ON A.MEMBERS_ID = B.ID";
                $SQL .= " LEFT JOIN t_memberrole AS C ON A.USER_ROLE = C.ID";
                $SQL .= " WHERE 1=1 AND A.ISSUPERADMIN=0";
                //error_log($SQL);
                $result = self::dbAccess()->fetchAll($SQL);
                break;
        }

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {
                if ($value->USER_LOGIN) {

                    if ($onlyUserOnline) {
                        if ($DB_SESSION->verifyTimeByUser($value->ID)) {
                            $data[$i]["ID"] = $value->ID;
                            $data[$i]["USER_LOGIN"] = iconOk() . " " . setShowText($value->USER_LOGIN);
                            $data[$i]["ACTIVITY_DATE"] = getShowDateTime($value->ACTIVITY_DATE);
                            $data[$i]["USER_ROLE"] = $value->USER_ROLE;
                            $i++;
                        }
                    } else {
                        $data[$i]["ID"] = $value->ID;
                        $data[$i]["USER_LOGIN"] = "(" . $value->CODE . ") " . setShowText($value->USER_LOGIN);
                        $data[$i]["ACTIVITY_DATE"] = getShowDateTime($value->ACTIVITY_DATE);
                        $data[$i]["USER_ROLE"] = $value->USER_ROLE;
                        $i++;
                    }
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

    public static function jsonCountUsersOnline() {

        $DB_SESSION = SessionAccess::getInstance();

        $SQL = "SELECT MEMBERS_ID AS ID FROM t_sessions";
        $SQL .= " WHERE 1=1 AND ISSUPERADMIN=0";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                if ($DB_SESSION->verifyTimeByUser($value->ID)) {
                    $data[] = $value->ID;
                } else {
                    self::dbAccess()->delete("t_sessions", array("MEMBERS_ID ='" . $value->ID . "'"));
                }
            }
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($data)
        );
    }

}

?>