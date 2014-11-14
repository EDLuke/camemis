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
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SMSDBAccess {

    protected $data = array();
    protected $out = array();
    public $sms_content = null;
    public $sms_url;
    public $sms_phone = null;
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

    public static function dbAminAccess() {
        return Zend_Registry::get('ADMIN_DB_ACCESS');
    }

    public function getSMSDataFromId($Id) {

        $result = self::findSMSFromId($Id);
        $data = array();
        if ($result) {

            $data["ID"] = $result->ID;
            $data["PRIORITY"] = $result->PRIORITY;
            $data["SENT"] = $result->SENT;
            $data["CONTENT"] = setShowText($result->CONTENT);
            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["CHARTS"] = strlen(utf8_decode($result->CONTENT));
            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
        }

        return $data;
    }

    public static function findSMSFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_sms", array('*'));
        $SQL->where("ID = ?",$Id);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonLoadSMS($Id) {

        $result = self::findSMSFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getSMSDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    protected function getAllSMSQuery($params) {

        $content = isset($params["content"]) ? addText($params["content"]) : "";
        $priority = isset($params["priority"]) ? $params["priority"] : "";
        $sent = isset($params["sent"]) ? $params["sent"] : "";
        $sendTo = isset($params["sendTo"]) ? $params["sendTo"] : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_sms";
        $SQL .= " WHERE 1=1";

        if ($content) {
            $SQL .= " AND ((CONTENT like '" . $content . "%') ";
            $SQL .= " ) ";
        }

        switch ($priority) {
            case 1:
                $SQL .= " AND PRIORITY=0";
                break;
            case 2:
                $SQL .= " AND PRIORITY=1";
                break;
            case 3:
                $SQL .= " AND PRIORITY=2";
                break;
            case 4:
                $SQL .= " AND PRIORITY=3";
                break;
            case 5:
                $SQL .= " AND PRIORITY=4";
                break;
        }

        switch ($sent) {
            case 2:
                $SQL .= " AND SENT=1";
                break;
            case 3:
                $SQL .= " AND SENT=0";
                break;
        }

        switch (UserAuth::getUserType()) {
            case "INSTRUCTOR":
            case "TEACHER":
                $SQL .= " AND STAFF_ID = '" . Zend_Registry::get('USER')->ID . "'";
                break;
        }

        $SQL .= " AND SEND_TO = '" . $sendTo . "'";

        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonAllSMS($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = $this->getAllSMSQuery($params);
        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $count = $this->countSendStudentSMSServices($value->ID);

                $data[$i]["ID"] = $value->ID;
                $data[$i]["PRIORITY"] = $value->PRIORITY;
                $data[$i]["CONTENT"] = setShowText($value->CONTENT);
                $data[$i]['PERSONS'] = $count;
                $data[$i]['PRIORITY_ICON'] = getSMSPriorityIcon($value->PRIORITY);
                $data[$i]['PRIORITY_NAME'] = getSMSPriorityName($value->PRIORITY);
                $i++;
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

    public function actionStudentSMSRegistration($params) {

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $sms_services = isset($params["SMS_SERVICES"]) ? $params["SMS_SERVICES"] : "0";
        $mobil_phone = isset($params["FORM_MOBIL_PHONE"]) ? $params["FORM_MOBIL_PHONE"] : "";
        $country_code = isset($params["FORM_PHONE_COUNTRY_CODE"]) ? $params["FORM_PHONE_COUNTRY_CODE"] : "";

        $SAVEDATA["PHONE_COUNTRY_CODE"] = addText($country_code);
        $SAVEDATA["SMS_SERVICES"] = addText($sms_services);
        $SAVEDATA["MOBIL_PHONE"] = addText($mobil_phone);

        $errors["FORM_MOBIL_PHONE"] = MSG_ENTER_ONLY_NUMBERS;

        $CHECK_ERROR = true;

        if (is_numeric($mobil_phone)) {
            $CHECK_ERROR = false;
        } else {
            $CHECK_ERROR = true;
        }

        if (!$CHECK_ERROR) {
            if ($studentId) {
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $studentId);
                self::dbAccess()->update('t_student', $SAVEDATA, $WHERE);
            }
        }

        if ($CHECK_ERROR) {

            return array(
                "success" => false
                , "errors" => $errors
            );
        } else {
            return array(
                "success" => true
            );
        }
    }

    public static function updateSMS($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $smsContent = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";

        if (isset($params["sendTo"]))
            $SAVEDATA['SEND_TO'] = $params["sendTo"];

        if (isset($params["PRIORITY"]))
            $SAVEDATA['PRIORITY'] = $params["PRIORITY"];

        if (isset($params["academicId"]))
            $SAVEDATA['CLASS_ID'] = $params["academicId"];

        $SAVEDATA["CONTENT"] = addText($smsContent);

        switch (UserAuth::getUserType()) {
            case "INSTRUCTOR":
            case "TEACHER":
                $SAVEDATA['STAFF_ID'] = Zend_Registry::get('USER')->ID;
                break;
        }

        if ($objectId == "new") {
            $SAVEDATA['CODE'] = createCode();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_sms', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {

            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $params["objectId"]);
            self::dbAccess()->update('t_sms', $SAVEDATA, $WHERE);
        }

        return $objectId;
    }

    public function jsonActionSaveSMSContent($params) {

        $objectId = self::updateSMS($params);

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function jsonRemoveSMS($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $fistCond = array('ID = ? ' => $removeId);
        self::dbAccess()->delete('t_sms', $fistCond);

        $secondCond = array('SMS_ID = ? ' => $removeId);
        self::dbAccess()->delete('t_user_sms', $secondCond);

        return array("success" => true);
    }

    public function jsonUnassignedStudentsSMS($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $params["status"] = 1;
        $ALL_STUDENTS = StudentSearchDBAccess::queryAllStudents($params);
        $STUDENTS_SMS = $this->listStudentsSMSServices($params["objectId"]);

        $i = 0;
        $data = array();

        if ($ALL_STUDENTS) {
            while (list($key, $row) = each($ALL_STUDENTS)) {

                if (!in_array($row->ID, $STUDENTS_SMS)) {

                    $data[$i]["ID"] = $row->ID;
                    $data[$i]["FIRSTNAME"] = setShowText($row->FIRSTNAME);
                    $data[$i]["LASTNAME"] = setShowText($row->LASTNAME);
                    $data[$i]["CODE"] = $row->CODE;
                    $data[$i]["DATE_BIRTH"] = getShowDate($row->DATE_BIRTH);
                    $data[$i]["GENDER"] = getGenderName($row->GENDER);
                    $data[$i]["ICON_SMS_SERVICES"] = iconSMSServices($row->SMS_SERVICES);
                    $data[$i]["MOBIL_PHONE"] = setShowText($row->MOBIL_PHONE);

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

    public function jsonUnassignedStaffsSMS($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $params["status"] = 1;
        $DB_STAFF = StaffDBAccess::getInstance();
        $ALL_STAFFS = $DB_STAFF->queryAllStaffs($params);
        $STAFF_SMS = $this->listStaffsSMSServices($params["objectId"]);

        $i = 0;
        $data = array();

        if ($ALL_STAFFS) {
            while (list($key, $row) = each($ALL_STAFFS)) {

                if (!in_array($row->ID, $STAFF_SMS)) {

                    $data[$i]["ID"] = $row->ID;
                    $data[$i]["FIRSTNAME"] = setShowText($row->FIRSTNAME);
                    $data[$i]["LASTNAME"] = setShowText($row->LASTNAME);
                    $data[$i]["FULL_NAME"] = setShowText($row->LASTNAME) . " " . setShowText($row->FIRSTNAME);
                    $data[$i]["CODE"] = $row->CODE;
                    $data[$i]["MOBIL_PHONE"] = $row->MOBIL_PHONE;
                    $data[$i]["USER_ROLE"] = setShowText($row->USER_ROLE);

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

    public function listStudentsSMSServices($objectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_sms", array('*'));
        $SQL->where("USER_TYPE = 'STUDENT'");
        $SQL->where("SMS_ID = '" . $objectId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($result) {
            foreach ($result as $value) {
                $data[$value->USER_ID] = $value->USER_ID;
            }
        }

        return $data;
    }

    public function listStaffsSMSServices($objectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_sms", array('*'));
        $SQL->where("USER_TYPE = 'STAFF'");
        $SQL->where("SMS_ID = '" . $objectId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($result) {
            foreach ($result as $value) {
                $data[$value->USER_ID] = $value->USER_ID;
            }
        }

        return $data;
    }

    public function actionAddStudentsToSMSSevices($params) {

        $selectionIds = $params["selectionIds"];
        $objectId = $params["objectId"];

        if ($selectionIds != "") {
            $selectedStudents = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($selectedStudents)
                foreach ($selectedStudents as $studentId) {

                    $SAVEDATA["SMS_ID"] = $objectId;
                    $SAVEDATA["USER_ID"] = $studentId;
                    $SAVEDATA["USER_TYPE"] = "STUDENT";

                    $CHECK = $this->checkAssignedUserSMSServises(
                            $studentId
                            , $objectId
                    );

                    if (!$CHECK) {
                        self::dbAccess()->insert('t_user_sms', $SAVEDATA);
                        $selectedCount++;
                    }
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public function actionAddStaffsToSMSSevices($params) {

        $selectionIds = $params["selectionIds"];
        $objectId = $params["objectId"];

        if ($selectionIds != "") {
            $selectedStaffs = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($selectedStaffs)
                foreach ($selectedStaffs as $staffId) {

                    $SAVEDATA["SMS_ID"] = $objectId;
                    $SAVEDATA["USER_ID"] = $staffId;
                    $SAVEDATA["USER_TYPE"] = "STAFF";

                    $CHECK = $this->checkAssignedUserSMSServises(
                            $staffId
                            , $objectId
                    );

                    if (!$CHECK) {
                        self::dbAccess()->insert('t_user_sms', $SAVEDATA);
                        $selectedCount++;
                    }
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public function assignedStudentsSMSVervices($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";

        $SQL = "SELECT B.ID AS USER_ID, B.FIRSTNAME AS FIRSTNAME, B.LASTNAME AS LASTNAME";
        $SQL .= ", B.CODE AS CODE";
        $SQL .= ", B.GENDER AS GENDER";
        $SQL .= ", B.STATUS AS STATUS";
        $SQL .= ", B.DATE_BIRTH AS DATE_BIRTH";
        $SQL .= ", B.SMS_SERVICES AS SMS_SERVICES";
        $SQL .= ", B.MOBIL_PHONE AS MOBIL_PHONE";
        $SQL .= ", B.PHONE_COUNTRY_CODE AS PHONE_COUNTRY_CODE";
        $SQL .= " FROM t_user_sms AS A";
        $SQL .= " LEFT JOIN t_student AS B ON B.ID = A.USER_ID";
        $SQL .= " WHERE";
        $SQL .= " A.SMS_ID = '" . $objectId . "'";

        if ($globalSearch) {

            $SQL .= " AND ((B.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY B.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY B.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY B.FIRSTNAME DESC";
                break;
        }
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function assignedStaffsSMSVervices($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";

        $SQL = "SELECT B.ID AS USER_ID, B.FIRSTNAME AS FIRSTNAME, B.LASTNAME AS LASTNAME";
        $SQL .= ", B.CODE AS CODE";
        $SQL .= ", B.GENDER AS GENDER";
        $SQL .= ", B.STATUS AS STATUS";
        $SQL .= ", B.MOBIL_PHONE AS MOBIL_PHONE";
        $SQL .= ", D.NAME AS USER_ROLE";
        $SQL .= " FROM t_user_sms AS A";
        $SQL .= " LEFT JOIN t_staff AS B ON B.ID = A.USER_ID";
        $SQL .= " LEFT JOIN t_members AS C ON C.ID = B.ID";
        $SQL .= " LEFT JOIN t_memberrole AS D ON D.ID = C.ROLE";
        $SQL .= " WHERE";
        $SQL .= " A.SMS_ID = '" . $objectId . "'";

        if ($globalSearch) {

            $SQL .= " AND ((B.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }

        //echo $SQL;

        return self::dbAccess()->fetchAll($SQL);
    }

    public function countSendStudentSMSServices($objectId) {

        $SQL = self::dbAccess()->select()
                ->from("t_user_sms", array("C" => "COUNT(*)"))
                ->where("SMS_ID = '" . $objectId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonAssignedStudentsSMSServices($params, $isjson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->assignedStudentsSMSVervices($params);

        $i = 0;
        $data = array();
        if ($result) {

            while (list($key, $row) = each($result)) {

                $data[$i]["ID"] = $row->USER_ID;
                $data[$i]["FIRSTNAME"] = setShowText($row->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($row->LASTNAME);
                $data[$i]["MOBIL_PHONE"] = $row->MOBIL_PHONE;
                $data[$i]["CODE"] = $row->CODE;
                $data[$i]["DATE_BIRTH"] = getShowDate($row->DATE_BIRTH);
                $data[$i]["GENDER"] = getGenderName($row->GENDER);
                $data[$i]["ICON_SMS_SERVICES"] = iconSMSServices($row->SMS_SERVICES);
                $data[$i]["SMS_SERVICES"] = $row->SMS_SERVICES;
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isjson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public function jsonAssignedStaffsSMSServices($params, $isjson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->assignedStaffsSMSVervices($params);

        $i = 0;
        $data = array();
        if ($result) {

            while (list($key, $row) = each($result)) {

                $data[$i]["ID"] = $row->USER_ID;
                $data[$i]["FIRSTNAME"] = setShowText($row->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($row->LASTNAME);
                $data[$i]["MOBIL_PHONE"] = $row->MOBIL_PHONE;
                $data[$i]["CODE"] = $row->CODE;
                $data[$i]["USER_ROLE"] = $row->USER_ROLE;

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isjson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public function jsonActionRemoveUserFromSMSServices($params) {

        $studentId = isset($params["id"]) ? addText($params["id"]) : "0";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "0";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";

        if ($field == "CHECKED") {
            if ($newValue) {
                if ($objectId)
                    self::dbAccess()->delete('t_user_sms', array("USER_ID='" . $studentId . "'", "SMS_ID='" . $objectId . "'"));
            }
        }

        return array(
            "success" => true
        );
    }

    public function jsonRemoveAllUsersFromSMSServices($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        if ($objectId)
            self::dbAccess()->delete('t_user_sms', array("SMS_ID='" . $objectId . "'"));

        return array(
            "success" => true
        );
    }

    public function jsonCopySMS($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $facette = self::findSMSFromId($objectId);

        $SAVEDATA["CONTENT"] = COPY . " : " . $facette->CONTENT;
        $SAVEDATA["PRIORITY"] = $facette->PRIORITY;
        $SAVEDATA["CLASS_ID"] = $facette->CLASS_ID;
        $SAVEDATA["STAFF_ID"] = $facette->STAFF_ID;

        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

        self::dbAccess()->insert('t_sms', $SAVEDATA);
        $newSMSId = self::dbAccess()->lastInsertId();

        $entries = $this->assignedStudentsSMSVervices($objectId);

        if ($newSMSId && $entries) {

            foreach ($entries as $value) {

                $SAVEDATA_STUDENT["SMS_ID"] = $newSMSId;
                $SAVEDATA_STUDENT["USER_ID"] = $value->USER_ID;
                $SAVEDATA_STUDENT["USER_TYPE"] = $value->USER_TYPE;
                self::dbAccess()->insert('t_user_sms', $SAVEDATA_STUDENT);
            }
        }

        return array(
            "success" => true
            , "newObjectId" => $newSMSId
        );
    }

    public function jsonLoadLogSMS($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "0";

        $SQL = "SELECT *";
        $SQL .= " FROM t_logsms";
        $SQL .= " WHERE";
        $SQL .= " GRADE_ID = '" . $gradeId . "'";
        $SQL .= " ORDER BY SENT_ON DESC";
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result) {

            while (list($key, $row) = each($result)) {

                $data[$i]["ID"] = $row->ID;
                $data[$i]["SMS_COUNT"] = $row->SMS_COUNT;
                $data[$i]["SENT_BY"] = $row->SENT_BY;
                $data[$i]["TERM"] = $row->TERM;
                $data[$i]["SENT_ON"] = getShowDateTime($row->SENT_ON);

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

    protected function checkAssignedUserSMSServises($userId, $smsId) {

        $SQL = self::dbAccess()->select()
                ->from("t_user_sms", array("C" => "COUNT(*)"))
                ->where("SMS_ID = '" . $smsId . "'")
                ->where("USER_ID = '" . $userId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function chooseAllPersons($params) {

        $registered = isset($params["registered"]) ? $params["registered"] : 1;
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $schoolyearObject = AcademicDateDBAccess::loadCurrentSchoolyear();

        if ($registered) {
            $searchParams["searchSMSServices"] = 1;
        } else {
            $searchParams["searchSMSServices"] = 0;
        }

        $searchParams["status"] = 1;
        $searchParams["schoolyearId"] = $schoolyearObject->ID;
        if ($academicId)
            $searchParams["academicId"] = $academicId;
        $ALL_STUDENTS = StudentSearchDBAccess::queryAllStudents($searchParams);

        if ($ALL_STUDENTS) {
            foreach ($ALL_STUDENTS as $value) {
                if ($value->MOBIL_PHONE) {
                    if ($objectId) {
                        $CHECK = $this->checkAssignedUserSMSServises($value->STUDENT_ID, $objectId);
                        if (!$CHECK) {
                            $SAVEDATA["USER_ID"] = $value->STUDENT_ID;
                            $SAVEDATA['SMS_ID'] = $objectId;
                            $SAVEDATA['USER_TYPE'] = "STUDENT";
                            $SAVEDATA["EVENT_TYPE"] = "EVENT";
                            self::dbAccess()->insert('t_user_sms', $SAVEDATA);
                        }
                    }
                }
            }
        }
    }

    public static function jsonLoadSMSSubscription($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_sms_subscription", array('*'));
        $SQL->where("ID = ?",$Id);
        $result = self::dbAminAccess()->fetchRow($SQL);

        if ($result) {

            $data["SUBSCRIPTION_COUNT"] = $result->SUBSCRIPTION_COUNT;
            $data["ACTIVE"] = $result->ACTIVE ? true : false;
            $data["ACTIVED_DATE"] = getShowDateTime($result->ACTIVED_DATE);

            $o = array(
                "success" => true
                , "data" => $data
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function jsonActionSMSSubscription($params) {

        $subscriptionId = isset($params["subscriptionId"]) ? $params["subscriptionId"] : "";
        $subscriptionCount = isset($params["SUBSCRIPTION_COUNT"]) ? $params["SUBSCRIPTION_COUNT"] : "";

        $SAVEDATA["SUBSCRIPTION_COUNT"] = addText($subscriptionCount);
        $SAVEDATA['SERVER_NAME'] = Zend_Registry::get('SERVER_NAME');
        $SAVEDATA['ACTIVE'] = isset($params["ACTIVE"]) ? 1 : 0;

        if (isset($params["ACTIVE"])) {
            $SAVEDATA['ACTIVED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['ACTIVED_BY'] = Zend_Registry::get('USER')->CODE;
        }

        if ($subscriptionId == "new") {
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAminAccess()->insert('t_sms_subscription', $SAVEDATA);
            $subscriptionId = self::dbAminAccess()->lastInsertId();
        } else {

            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            $WHERE = self::dbAminAccess()->quoteInto("ID = ?", $subscriptionId);
            self::dbAminAccess()->update('t_sms_subscription', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
            , "subscriptionId" => $subscriptionId
        );
    }

    public static function jsonListSubscriptons($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_sms_subscription", array('*'));
        $SQL->where("SERVER_NAME = '" . Zend_Registry::get('SERVER_NAME') . "'");
        $result = self::dbAminAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result) {

            while (list($key, $row) = each($result)) {

                $data[$i]["ID"] = $row->ID;
                $data[$i]["PRICING"] = "---";
                $data[$i]["SUBSCRIPTION_COUNT"] = $row->SUBSCRIPTION_COUNT;
                $data[$i]["SUBSCRIPTION_DATE"] = getShowDateTime($row->CREATED_DATE);

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

    public static function getSMSCountryCode() {
        switch (Zend_Registry::get('SYSTEM_COUNTRY')) {
            case "KH":
                return "855";
                break;
            case "VN":
                return "84";
                break;
            default:
                return "?";
                break;
        }
    }

}

?>