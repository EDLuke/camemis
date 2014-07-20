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

require_once setUserLoacalization();

class UserSMSDBAccess {

    CONST T_SATFF = "t_staff";
    CONST T_STUDENT = "t_student";
    CONST T_USER_SMS = "t_user_sms";

    private $dataforjson = null;
    public $data = array();

    static function getInstance() {
        static $me;

        if ($me == null) {
            $me = new UserSMSDBAccess();
        }

        return $me;
    }

    public function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->SELECT = $this->DB_ACCESS->select();
        $this->_TOSTRING = $this->SELECT->__toString();
        $this->DB_STAFF = StaffDBAccess::getInstance();
        $this->DB_STUDENT = StudentDBAccess::getInstance();
    }

    protected function getListUserSMS($params) {

        $target = isset($params["target"]) ? addText($params["target"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SELECT_DATA_B = array(
            "LASTNAME AS LASTNAME"
            , "FIRSTNAME AS FIRSTNAME"
            , "CODE AS CODE"
            , "ID AS USER_ID"
        );

        $SQL = $this->SELECT;
        $SQL->distinct();
        $SQL->from(array('A' => self::T_USER_SMS), array('*'));

        switch (strtoupper($target)) {
            case "STUDENT_INDIVIDUAL":
                $SQL->where("A.USER_TYPE = 'STUDENT'");
                $SQL->where("A.USER_ID = '".$studentId."'");
                $SQL->where("A.CONTENT<>''");
                break;
            default:
                $SQL->joinLeft(array('B' => self::T_SATFF), 'A.USER_ID=B.ID', $SELECT_DATA_B);
                $SQL->where('A.USER_TYPE = ?', "" . $type . "");
                break;
        }

        $SQL->order("A.SEND_DATE DESC");

        //echo $this->SELECT->__toString();
        $resultRows = $this->DB_ACCESS->fetchAll($SQL);

        return $resultRows;
    }

    public function jsonListUserSMS($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->getListUserSMS($params);
        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->USER_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["CONTENT"] = setShowText($value->CONTENT);
                $data[$i]["SEND_DATE"] = getShowDateTime($value->SEND_DATE);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["SEND_BY"] = setShowText($value->SEND_BY); 
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$i]["USER"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }else{
                    $data[$i]["USER"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );

        return $this->dataforjson;
    }

}

?>