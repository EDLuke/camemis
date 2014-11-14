<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 8.05.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentSearchDBAccess.php';
require_once 'models/app_university/assignment/AssignmentDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/sms/SMSDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SendSMSDBAccess extends SMSDBAccess {

    protected $data = array();
    protected $out = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getSMSSchoolName() {

        if (Zend_Registry::get('SCHOOL')->SMS_SCHOOL_NAME) {
            return strip_tags(Zend_Registry::get('SCHOOL')->SMS_SCHOOL_NAME) . " ";
        } else {
            return "";
        }
    }

    public static function curlSendSMS($callnumbers, $content) {
        $result = SchoolDBAccess::getSchool();
        $sym="";
        if($result->SHORT){
            $sym="-";
        }
        $CURLOPT_POSTFIELDS = "";
        $CURLOPT_POSTFIELDS .= "username=swi-srapid";
        $CURLOPT_POSTFIELDS .= "&password=1234567";
        $CURLOPT_POSTFIELDS .= "&type=0";
        $CURLOPT_POSTFIELDS .= "&dlr=0";
        $CURLOPT_POSTFIELDS .= "&destination=" . $callnumbers . "";
        $CURLOPT_POSTFIELDS .= "&source=CAMEMIS".$sym.$result->SHORT;
        $CURLOPT_POSTFIELDS .= "&message=" . $content . "";

        $handle = curl_init();
        curl_setopt_array(
            $handle, array(
                CURLOPT_URL => "http://sms.wicam.com.kh:8080/bulksms/bulksms"
                , CURLOPT_POST => true
                , CURLOPT_POSTFIELDS => $CURLOPT_POSTFIELDS
                , CURLOPT_RETURNTRANSFER => true
            )
        );

        $response = curl_exec($handle);
        curl_close($handle);

        if (substr($response, 0, 4) == 1701) {
            $status = 1;
        } else {
            $status = 0;
        }
        
        return $status;
    }

    public function jsonSendSMSToAllPersons($params) {

        $sendTo = isset($params["sendTo"]) ? $params["sendTo"] : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        switch ($sendTo) {
            case "STUDENT":
                $entries = $this->assignedStudentsSMSVervices($params);
                break;
            case "STAFF":
                $entries = $this->assignedStaffsSMSVervices($params);
                break;
        }

        $facette = $this->findSMSFromId($objectId);

        $count = 0;
        if ($facette) {
            $CONTENT = $facette->CONTENT;
            if ($entries) {
                foreach ($entries as $value) {
                    self::curlSendSMS($value->MOBIL_PHONE, $CONTENT);
                    $count++;
                }
            }
        }

        return array(
            "success" => true
            , "sendCount" => $count
        );
    }

    public function jsonSendSMSToSingleStudent($params) {

        $data = array();

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function jsonSendSMSScoreToSingleStudent($params) {
        //$params
        $data = array();

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function jsonActionSendScoreSMSToAllStudents() {

        //
    }

    public function jsonSMSStudentSubjectAverage() {

        //
    }

    public function setSendInfo($sendCount) {

        if ($sendCount == 0 || $sendCount == 1) {
            return $sendCount . " SMS " . WAS_INFORMED;
        } else {
            return $sendCount . " SMS " . HAVE_BEEN_INFORMED;
        }
    }

    public function jsonSendStudentAbsence($params, $isJson = true) {
        
    }

    public function sendSMS() {
        
    }

    public function setSendDateBySMSId($Id, $userType) {

        $SAVEDATA = array();

        $SAVEDATA["SEND_DATE"] = getCurrentDBDateTime();
        $SAVEDATA["EVENT_TYPE"] = $userType;
        $SAVEDATA["SEND_BY"] = Zend_Registry::get('USER')->CODE;
        $WHERE = self::dbAccess()->quoteInto("SMS_ID = ?", $Id);
        self::dbAccess()->update('t_user_sms', $SAVEDATA, $WHERE);
    }

    public function setUserIntoUserSMS($userId, $userType, $eventType, $content = false) {

        $SAVEDATA = array();

        $SAVEDATA["USER_ID"] = $userId;
        $SAVEDATA["SEND_DATE"] = getCurrentDBDateTime();
        $SAVEDATA["USER_TYPE"] = $userType;
        $SAVEDATA["EVENT_TYPE"] = $eventType;
        $SAVEDATA["SEND_BY"] = Zend_Registry::get('USER')->CODE;
        if ($content) {
            $SAVEDATA["CONTENT"] = $content;
        }
        self::dbAccess()->insert('t_user_sms', $SAVEDATA);
    }

}

?>