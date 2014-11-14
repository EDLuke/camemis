<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/app_admin/AdminSessionAccess.php';
require_once 'models/app_admin/AdminDatabaseDBAccess.php';
require_once 'models/app_admin/AdminLocalDBAccess.php';

class AdminCustomerDBAccess {

    public $GuId = null;
    public $DB_DATABASE;
    public $schoolName;

    function __construct($GuId = false) {

        $this->GuId = $GuId;
        $this->DB_DATABASE = new AdminDatabaseDBAccess();
        $this->DB_LOCAL = new AdminLocalDBAccess();
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSchoolAccess() {
        return Zend_Registry::get('SCHOOL_DB_ACCESS');
    }

    protected static function checkSchoolDB() {

        return Zend_Registry::get('IS_SCHOOL_DB_ACCESS');
    }

    protected static function schoolURL() {

        return Zend_Registry::get('CUSTOMER')->URL;
    }

    public function findCustomer() {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM " . T_CUSTOMER . "";
        $SQL .= " WHERE";
        $SQL .= " GUID = '" . $this->GuId . "'";
        //echo $SQL;
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    public function jsonLoadCustomer($Id) {

        $this->GuId = $Id;

        $facette = $this->findCustomer();

        $DATA = array("ISDEMO" => 1);

        if ($facette) {

            $LOCAL_OBJECT = $this->DB_LOCAL->findLocalFromId($facette->LOCAL);

            $DATA["SCHOOL_NAME"] = $facette->SCHOOL_NAME;
            $DATA["SCHOOL_CODE"] = $facette->SCHOOL_CODE;
            $DATA["CONTACT_PERSON"] = $facette->CONTACT_PERSON;
            $DATA["CONTACT_EMAIL"] = $facette->CONTACT_EMAIL;
            $DATA["CONTACT_PHONE"] = $facette->CONTACT_PHONE;

            $DATA["URL"] = $facette->URL;
            $DATA["DB_NAME"] = $facette->DB_NAME;

            $DATA["SMS_CREDITS"] = $facette->SMS_CREDITS ? $facette->SMS_CREDITS : 10000;
            $DATA["SMS_CREDITS_USED"] = $this->countSMSUsed();
            $DATA["SMS_NOT_USED"] = $this->countSMSNotUsed();

            $DATA["SCHOOL_LOGIN"] = $this->getLoginName();

            $DATA["SUPPORT_PERSON"] = $facette->SUPPORT_PERSON;
            $DATA["SUPPORT_EMAIL"] = $facette->SUPPORT_EMAIL;
            $DATA["SUPPORT_PHONE"] = $facette->SUPPORT_PHONE;
            $DATA["HIDDEN_LOCAL"] = $facette->LOCAL;
            $DATA["CHOOSE_LOCAL_NAME"] = isset($LOCAL_OBJECT) ? $LOCAL_OBJECT->NAME : "---";

            $DATA["ADDRESS"] = $facette->ADDRESS;
            $DATA["DESCRIPTION"] = $facette->DESCRIPTION;
            $DATA["ISDEMO"] = $facette->ISDEMO;

            $DATA["SCHOOL_WEBSITE"] = $facette->SCHOOL_WEBSITE;

            $DATA["startdt"] = ($facette->START_DATE != '0000-00-00') ? $facette->START_DATE : "";
            $DATA["enddt"] = ($facette->END_DATE != '0000-00-00') ? $facette->END_DATE : "";

            $DATA["SORTKEY"] = $facette->SORTKEY;
            $DATA["SYSTEM_TEMPLATE"] = $facette->SYSTEM_TEMPLATE;

            $DATA["EDUCATION_TYPE"] = $facette->EDUCATION_TYPE;
        }

        $o = array(
            "success" => true
            , "data" => $DATA
        );
        return $o;
    }

    public function allCustomersQuery($params) {

        $local = isset($params["local"]) ? $params["local"] : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $systemTemp = isset($params["systemTemp"]) ? $params["systemTemp"] : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM " . T_CUSTOMER . " AS A";
        $SQL .= " WHERE 1=1";

        if ($local) {
            $SQL .= " AND A.LOCAL='" . $local . "'";
        } else {
            $SQL .= " AND A.LOCAL='xxxxxxxxx'";
        }

        if ($globalSearch) {
            $SQL .= " AND ((A.SCHOOL_NAME like '%" . $globalSearch . "%') OR (A.SCHOOL_CODE like '%" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        if ($systemTemp) {
            switch ($systemTemp) {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $SQL .= " AND A.SYSTEM_TEMPLATE=$systemTemp";
                    break;
            }
        }

        $SQL .= " AND A.ACTIVE=1";
        $SQL .= " AND A.MODUL_API <>'ADMIN'";
        $SQL .= " ORDER BY A.SORTKEY";

        //echo $SQL;
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public function jsonAllCustomers($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = $this->allCustomersQuery($params);

        $i = 0;
        $data = array();
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["GUID"] = $value->GUID;
                $data[$i]["SCHOOL_CODE"] = "<B>" . $value->SCHOOL_CODE . "</B>";
                $data[$i]["SCHOOL_LOGIN"] = $value->SCHOOL_LOGIN;
                $data[$i]["SCHOOL_NAME"] = $value->SCHOOL_NAME;
                $data[$i]["SCHOOL_URL"] = "<B>" . $value->URL . "</B>";
                $data[$i]["CONTACT_EMAIL"] = $value->CONTACT_EMAIL;
                $data[$i]["CONTACT_PHONE"] = $value->CONTACT_PHONE;
                $data[$i]["ADDRESS"] = $value->ADDRESS;
                $data[$i]["SMS_CREDITS"] = $value->SMS_CREDITS;
                $data[$i]["SUPPORT_PERSON"] = $value->SUPPORT_PERSON;
                $data[$i]["SUPPORT_EMAIL"] = $value->SUPPORT_EMAIL;
                $data[$i]["SUPPORT_PHONE"] = $value->SUPPORT_PHONE;
                $data[$i]["EDUCATION_TYPE"] = $value->EDUCATION_TYPE;
                $data[$i]["SYSTEM_TEMPLATE"] = $value->SYSTEM_TEMPLATE;

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

        return $dataforjson;
    }

    public function jsonSaveCustomer($params) {

        $GuId = isset($params["GuId"]) ? $params["GuId"] : "0";
        $this->GuId = $GuId;
        $facette = $this->findCustomer();

        if ($facette) {
            $this->updateCustomer($params);
        }

        return array(
            "success" => true
        );
    }

    public function updateCustomer($params) {

        $SAVEDATA = array();

        $GuId = isset($params["GuId"]) ? $params["GuId"] : "0";
        $ISDEMO = isset($params["ISDEMO"]) ? $params["ISDEMO"] : "0";

        $SAVEDATA['CONTACT_EMAIL'] = $params["CONTACT_EMAIL"];
        $SAVEDATA['CONTACT_PHONE'] = $params["CONTACT_PHONE"];
        $SAVEDATA['CONTACT_PERSON'] = $params["CONTACT_PERSON"];

        if (isset($params["SMS_CREDITS"]))
            $SAVEDATA['SMS_CREDITS'] = $params["SMS_CREDITS"];
        if (isset($params["URL"]))
            $SAVEDATA['URL'] = $params["URL"];

        if (isset($params["HIDDEN_LOCAL"]))
            $SAVEDATA['LOCAL'] = $params["HIDDEN_LOCAL"];

        if (isset($params["SCHOOL_WEBSITE"]))
            $SAVEDATA['SCHOOL_WEBSITE'] = $params["SCHOOL_WEBSITE"];

        if (isset($params["DB_NAME"]))
            $SAVEDATA['DB_NAME'] = $params["DB_NAME"];
        if (isset($params["SYSTEM_TEMPLATE"]))
            $SAVEDATA['SYSTEM_TEMPLATE'] = $params["SYSTEM_TEMPLATE"];

        if (isset($params["SCHOOL_LOGIN"])) {
            $SAVEDATA['SCHOOL_LOGIN'] = $params["SCHOOL_LOGIN"];
            $this->setSuperUserLogin($params["SCHOOL_LOGIN"]);
        }

        if (isset($params["SCHOOL_NAME"])) {
            $SAVEDATA['SCHOOL_NAME'] = $params["SCHOOL_NAME"];
            $this->setSchoolName($params["SCHOOL_NAME"]);
        }

        $SAVEDATA['SCHOOL_CODE'] = $params["SCHOOL_CODE"];
        $SAVEDATA['CUSTOMER_CODE'] = $params["SCHOOL_CODE"];
        $SAVEDATA['ADDRESS'] = $params["ADDRESS"];
        $SAVEDATA['START_DATE'] = $params["startdt"];
        $SAVEDATA['END_DATE'] = $params["enddt"];
        $SAVEDATA['DESCRIPTION'] = $params["DESCRIPTION"];
        $SAVEDATA['SORTKEY'] = $params["SORTKEY"];
        $SAVEDATA['ISDEMO'] = $ISDEMO;
        $SAVEDATA['SUPPORT_EMAIL'] = $params["SUPPORT_EMAIL"];
        $SAVEDATA['SUPPORT_PHONE'] = $params["SUPPORT_PHONE"];
        $SAVEDATA['SUPPORT_PERSON'] = $params["SUPPORT_PERSON"];

        $WHERE = self::dbAccess()->quoteInto("GUID = ?", $GuId);
        self::dbAccess()->update('t_customer', $SAVEDATA, $WHERE);

        return array(
            "success" => true
        );
    }

    public function findSchoolyearByCurrentDate() {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT ID FROM t_academicdate";
            $SQL .= " WHERE STATUS=1 AND NOW()>=START AND NOW()<=END";
            $SQL .= " LIMIT 0,1";
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result ? $result->ID : false;
        } else {
            return false;
        }
    }

    public function getEnrolledStudents($schoolyearId) {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT COUNT(*) AS C FROM t_student_schoolyear";
            $SQL .= " WHERE SCHOOL_YEAR='" . $schoolyearId . "'";
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    public function getEnrolledStudentsMale($schoolyearId) {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT COUNT(*) AS C FROM t_student_schoolyear";
            $SQL .= " WHERE SCHOOL_YEAR='" . $schoolyearId . "'";
            $SQL .= " AND GENDER=1";
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    public function getEnrolledStudentsFemale($schoolyearId) {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT COUNT(*) AS C FROM t_student_schoolyear";
            $SQL .= " WHERE SCHOOL_YEAR='" . $schoolyearId . "'";
            $SQL .= " AND GENDER=2";
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    public function jsonEnrolledStudents($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $data = array();

        if (self::checkSchoolDB()) {
            $SQL = "";
            $SQL .= " SELECT *";
            $SQL .= " FROM t_academicdate";
            //echo $SQL;
            $resultRows = self::dbSchoolAccess()->fetchAll($SQL);
            if ($resultRows) {

                $i = 0;
                foreach ($resultRows as $value) {

                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["NAME"] = $value->NAME;
                    $data[$i]["YEAR"] = $value->NAME;
                    $data[$i]["TOTAL"] = $this->getEnrolledStudents($value->ID);
                    $data[$i]["TOTAL_MALE"] = $this->getEnrolledStudentsMale($value->ID);
                    $data[$i]["TOTAL_FEMALE"] = $this->getEnrolledStudentsFemale($value->ID);

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

    public function getSchoolId() {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_school";
            $SQL .= " LIMIT 0,1";
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result ? $result->ID : null;
        } else {
            return null;
        }
    }

    public function getLoginName() {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_members";
            $SQL .= " WHERE ROLE=1 AND SUPERUSER = '1' LIMIT 0,1";
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result ? $result->LOGINNAME : "Error";
        } else {
            return "Error";
        }
    }

    public function setSuperUserLogin($value) {

        if (self::checkSchoolDB()) {
            $SAVEDATA['LOGINNAME'] = $value;
            $WHERE[] = "ROLE = '1'";
            $WHERE[] = "SUPERUSER = '1'";
            self::dbSchoolAccess()->update('t_members', $SAVEDATA, $WHERE);
        }
    }

    public function setSchoolName($value) {

        if (self::checkSchoolDB()) {
            $SAVEDATA['NAME'] = $value;
            $WHERE[] = "ID = '" . $this->getSchoolId() . "'";
            self::dbSchoolAccess()->update('t_school', $SAVEDATA, $WHERE);
        }
    }

    public function getCurrentSchoolyear() {
        if (self::checkSchoolDB()) {
            $SQL = "SELECT *  FROM t_academicdate";
            $SQL .= " WHERE STATUS=1 AND NOW()>=START AND NOW()<=END";
            $SQL .= " LIMIT 0,1";
            //echo $SQL;
            $result = self::dbSchoolAccess()->fetchRow($SQL);
            return $result;
        }
    }

    public function countSMSUsed() {

        if (self::checkSchoolDB()) {

            $CURRENT_SCHOOLYEAR_OBJECT = $this->getCurrentSchoolyear();

            $SQL = "SELECT COUNT(*) AS C";
            $SQL .= " FROM t_user_sms";
            $SQL .= " WHERE";
            $SQL .= " SEND_DATE >= '" . $CURRENT_SCHOOLYEAR_OBJECT->START . "'";
            $SQL .= " AND SEND_DATE <= '" . $CURRENT_SCHOOLYEAR_OBJECT->END . "'";
            $SQL .= " AND SMS_ID <>0";
            $SQL .= " AND SEND_DATE <>'0000-00-00'";
            //echo $SQL;
            $result = self::dbSchoolAccess()->fetchRow($SQL);

            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    public function countSMSNotUsed() {
        if (self::checkSchoolDB()) {
            $facette = $this->findCustomer();
            $count = $facette->SMS_CREDITS - $this->countSMSUsed();
            return $count ? $count : 0;
        } else {
            return 0;
        }
    }

    public function countCAMEMISVisitorsLastWeek() {

        if (self::checkSchoolDB()) {
            $SQL = "SELECT COUNT(*) AS C";
            $SQL .= " FROM t_logininfo";
            $SQL .= " WHERE";
            $SQL .= " DATE< DATE_ADD(CURDATE( ),INTERVAL -7 DAY)";
            //echo $SQL;
            $result = self::dbSchoolAccess()->fetchRow($SQL);

            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    public function jsonTreeAllCustomers($params) {

        $result = $this->allCustomersQuery($params);

        $i = 0;
        $data = array();
        if ($result)
            foreach ($result as $value) {

                $data[$i]['id'] = "" . $value->GUID . "";
                $data[$i]['text'] = stripslashes($value->URL);
                $data[$i]['iconCls'] = "icon-school";
                $data[$i]['cls'] = "nodeTextBlue";
                $data[$i]['leaf'] = true;
                $i++;
            }
        return $data;
    }

    public static function checkSchoolLogin($date) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_schoollogin";
        $SQL .= " WHERE";
        $SQL .= " URL = '" . self::schoolURL() . "'";
        $SQL .= " AND LOGING_DATE = '" . $date . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function setSchoolLogin() {

        $SAVEDATA = array();

        if (self::checkSchoolDB()) {

            $SQL = "SELECT *";
            $SQL .= " FROM t_logininfo";
            $resultRows = self::dbSchoolAccess()->fetchAll($SQL);

            if ($resultRows) {
                foreach ($resultRows as $value) {

                    if (!self::checkSchoolLogin($value->DATE)) {
                        $SAVEDATA["LOGING_DATE"] = $value->DATE;
                        $SAVEDATA["LOGINNAME"] = $value->LOGINNAME;
                        $SAVEDATA["IP"] = $value->IP;
                        $SAVEDATA["URL"] = self::schoolURL();
                        self::dbAccess()->insert('t_schoollogin', $SAVEDATA);
                    }
                }
            }
        }
    }

    public static function registrationSchool($params) {
        
        $SAVEDATA = array();
        $SAVEDATA["SCHOOL_NAME"] = $params["SCHOOL_NAME"];
        $SAVEDATA["CONTACT_PERSON"] = $params["LASTNAME"] . " " . $params["FIRSTNAME"];
        $SAVEDATA["CONTACT_PHONE"] = $params["CONTACT_PHONE"];
        $SAVEDATA["CONTACT_EMAIL"] = $params["CONTACT_EMAIL"];
        $SAVEDATA["SYSTEM_TEMPLATE"] = $params["SYSTEM_TEMPLATE"];
        $SAVEDATA["GUID"] = generateGuid();
        $SAVEDATA["CUSTOMER"] = generateGuid();
        $SAVEDATA["SCHOOL_CODE"] = createCode();
        $SAVEDATA["CUSTOMER_CODE"] = createCode();
        
        switch ($params["COUNTRY"]) {
            case "MM":
                $SAVEDATA["SYSTEM_COUNTRY"] = "BURMESE";
                $SAVEDATA["SYSTEM_LANGUAGE"] = "BURMESE";
                break;
            case "KH":
                $SAVEDATA["SYSTEM_COUNTRY"] = "KHMER";
                $SAVEDATA["SYSTEM_LANGUAGE"] = "KHMER";
                break;
            case "ID":
                $SAVEDATA["SYSTEM_COUNTRY"] = "INDONESIAN";
                $SAVEDATA["SYSTEM_LANGUAGE"] = "INDONESIAN";
                break;
            case "LA":
                $SAVEDATA["SYSTEM_COUNTRY"] = "LAO";
                $SAVEDATA["SYSTEM_LANGUAGE"] = "LAO";
                break;
            case "PH":
                $SAVEDATA["SYSTEM_COUNTRY"] = "FILIPINO";
                $SAVEDATA["SYSTEM_LANGUAGE"] = "FILIPINO";
                break;
            case "TH":
                $SAVEDATA["SYSTEM_COUNTRY"] = "THAI";
                $SAVEDATA["SYSTEM_LANGUAGE"] = "THAI";
                break;
        }

        $SAVEDATA["ADDRESS"] = $params["ADDRESS"];
        self::dbAccess()->insert('t_customer', $SAVEDATA);
    }

}

?>