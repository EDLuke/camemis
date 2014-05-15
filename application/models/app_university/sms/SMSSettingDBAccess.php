<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 6.08.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';

require_once setUserLoacalization();

class SMSSettingDBAccess {
    CONST T_ADMIN_CUSTOMER = "t_customer";
    CONST T_STAFF = "t_staff";
    CONST T_STUDENT = "t_student";
    CONST T_USER_SMS = "t_user_sms";
    public $data = array();

    static function getInstance() {
        static $me;

        if ($me == null) {
            $me = new SMSSettingDBAccess();
        }

        return $me;
    }

    public function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->ADMIN_DB_ACCESS = Zend_Registry::get('ADMIN_DB_ACCESS');

        $this->SELECT = $this->DB_ACCESS->select();
        $this->_TOSTRING = $this->SELECT->__toString();
        $this->DB_STAFF = StaffDBAccess::getInstance();
        $this->DB_SCHOOLYEAR = AcademicDateDBAccess::getInstance();
        $this->DB_STUDENT = StudentDBAccess::getInstance();
    }

    protected function getListUserSMS($params) {

        $type = isset($params["type"]) ? addText($params["type"]) : "";

        $SELECT_DATA_B = array(
            "LASTNAME AS LASTNAME"
            , "FIRSTNAME AS FIRSTNAME"
            , "CODE AS CODE"
            , "ID AS USER_ID"
        );
        
        $query = $this->SELECT;
        $this->SELECT->distinct();
        $this->SELECT->from(array('A' => self::T_USER_SMS), array('*'));
        $this->SELECT->joinLeft(array('B' => self::T_SATFF), 'A.USER_ID=B.ID', $SELECT_DATA_B);
        $this->SELECT->where('A.USER_TYPE = ?', "" . $type . "");
        $this->SELECT->order("A.SEND_DATE DESC");

        //echo $this->SELECT->__toString();
        $resultRows = $this->DB_ACCESS->fetchAll($query);

        return $resultRows;
    }

    public function jsonListUserSMS($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $result = $this->getListUserSMS($params);
        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->USER_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["SEND_DATE"] = getShowDateTime($value->SEND_DATE);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["SEND_BY"] = setShowText($value->SEND_BY);
                $data[$i]["USER"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
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

    public function getCurrentSchoolyear(){
        
        $SQL = "SELECT *  FROM t_academicdate";
        $SQL .= " WHERE STATUS=1 AND NOW()>=START AND NOW()<=END";
        $SQL .= " LIMIT 0,1";
        //echo $SQL;
        return $this->DB_ACCESS->fetchRow($SQL);
    }
    
    public function countSMSUsed(){
        
        $CURRENT_SCHOOLYEAR_OBJECT = $this->getCurrentSchoolyear();
        
        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM " . self::T_USER_SMS . "";
        $SQL .= " WHERE";
        $SQL .= " SEND_DATE >= '" . $CURRENT_SCHOOLYEAR_OBJECT->START . "'";
        $SQL .= " AND SEND_DATE <= '" . $CURRENT_SCHOOLYEAR_OBJECT->END . "'";
        $SQL .= " AND SMS_ID <>0";
        $SQL .= " AND SEND_DATE <>'0000-00-00'";
        //echo $SQL;
        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result?$result->C:0;

    }
    
    public function findSchoolProviderByUrl() {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM " . self::T_ADMIN_CUSTOMER . "";
        $SQL .= " WHERE";
        $SQL .= " URL = '" . Zend_Registry::get('SERVER_NAME') . "'";
        //echo $SQL;
        $result = $this->ADMIN_DB_ACCESS->fetchRow($SQL);

        return $result;
    }
    
    public function getCountSMSCredits() {

        $facette = $this->findSchoolProviderByUrl();
        $count = $facette->SMS_CREDITS;
        return $count?$count:0;
    }
    
    public function countSMSNotUsed() {

        $facette = $this->findSchoolProviderByUrl();
        $count = $facette->SMS_CREDITS - $this->countSMSUsed();
        return $count?$count:0;
    }

}

?>