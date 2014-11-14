<?php

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/BuildData.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once setUserLoacalization();

class GuardianDBAccess {

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

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function findObjectFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from('t_guardian', array('*'));
        $SQL->where("ID = ?",$Id);

        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getObjectDataFromId($Id) {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["CODE"] = $result->CODE;
            $data["STATUS"] = $result->STATUS;
            $data["FIRSTNAME"] = setShowText($result->FIRSTNAME);
            $data["LASTNAME"] = setShowText($result->LASTNAME);
            $data["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
            $data["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
            $data["LOGINNAME"] = $result->LOGINNAME;
            $data["PASSWORD"] = "**********";
            $data["PASSWORD_REPEAT"] = "**********";
            $data["GENDER"] = $result->GENDER;
            $data["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);
            $data["ADDRESS"] = setShowText($result->ADDRESS);
            $data["PHONE"] = setShowText($result->PHONE);
            $data["EMAIL"] = setShowText($result->EMAIL);
            $data["UMCPANL"] = $result->UMCPANL ? 1 : 0;
            $data["UCNCP"] = $result->UCNCP ? 1 : 0;
        }

        return $data;
    }

    public static function mappingUserLoginName($Id) {
        $result = self::findObjectFromId($Id);
        $SAVEDATA["LOGINNAME"] = addText($result->CODE);
        $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
        self::dbAccess()->update("t_guardian", $SAVEDATA, $WHERE);
    }

    public static function jsonLoadGuardian($Id) {

        $result = self::findObjectFromId($Id);

        if (!$result->LOGINNAME)
            self::mappingUserLoginName($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getObjectDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function findlastId($index) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from('t_guardian', array('ID'));
        $SQL->where("GUARDIAN_INDEX='" . $index . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonSaveGuardian($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';

        $SAVEDATA = array();

        if ($objectId == "new") {
            $SAVEDATA["ID"] = generateGuid();
            $SAVEDATA['CODE'] = createCode();
        }

        if (isset($params["FIRSTNAME"]))
            $SAVEDATA["FIRSTNAME"] = addText($params["FIRSTNAME"]);

        if (isset($params["LASTNAME"]))
            $SAVEDATA["LASTNAME"] = addText($params["LASTNAME"]);

        if (isset($params["FIRSTNAME"]) && isset($params["LASTNAME"]))
            $SAVEDATA['NAME'] = addText($params["FIRSTNAME"]) . " " . addText($params["LASTNAME"]);

        if (isset($params["FIRSTNAME_LATIN"]))
            $SAVEDATA["FIRSTNAME_LATIN"] = addText($params["FIRSTNAME_LATIN"]);

        if (isset($params["LASTNAME_LATIN"]))
            $SAVEDATA["LASTNAME_LATIN"] = addText($params["LASTNAME_LATIN"]);

        $loginName = isset($params["LOGINNAME"]) ? addText($params["LOGINNAME"]) : "";
        if ($loginName != "") {
            $SAVEDATA['LOGINNAME'] = $loginName;
            //$SAVEDATA['CHANGED_LOGINNAME'] = 1;
        }

        $errors = Array();
        ////////////////////////////////////////////////////////////////////////
        //CHANGE PASSWORD...
        ////////////////////////////////////////////////////////////////////////
        $password = isset($params["PASSWORD"]) ? addText($params["PASSWORD"]) : "";
        $password_repeat = isset($params["PASSWORD_REPEAT"]) ? addText($params["PASSWORD_REPEAT"]) : "";
        $USERDATA['UMCPANL'] = isset($params["UMCPANL"]) ? 1 : 0;
        $USERDATA['UCNCP'] = isset($params["UCNCP"]) ? 1 : 0;
        if ($password != "" && $password_repeat != "") {
            if (strlen($password) < UserAuth::getMinPasswordLength()) {
                $errors['PASSWORD'] = PASSWORD_IS_TOO_SHORT;
            } else {
                if (UserAuth::isPasswordComplexityRequirements()) {
                    if (!preg_match("#[0-9]+#", $password)) {
                        $errors['PASSWORD'] = PASSWORD_MUST_INCLUDE_AT_LEAST_ONE_NUMBER;
                    }
                    if (!preg_match("#[a-zA-Z]+#", $password)) {
                        $errors['PASSWORD'] = PASSWORD_MUST_INCLUDE_AT_LEAST_ONE_LETTER;
                    }
                }
            }

            if ($password == $password_repeat) {
                $USERDATA['CHANGE_PASSWORD'] = 1;
                $USERDATA['UMCPANL'] = 0;
                $USERDATA['CHANGE_PASSWORD_DATE'] = time();
                $USERDATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
            }
        }

        ////////////////////////////////////////////////////////////////////////
        //CHANGE LOGINNAME...
        ////////////////////////////////////////////////////////////////////////

        if (isset($params["GENDER"]))
            $SAVEDATA['GENDER'] = addText($params["GENDER"]);

        if (isset($params["DATE_BIRTH"]))
            $SAVEDATA['DATE_BIRTH'] = setDate2DB($params["DATE_BIRTH"]);

        if (isset($params["ADDRESS"]))
            $SAVEDATA['ADDRESS'] = addText($params["ADDRESS"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["PHONE"]))
            $SAVEDATA['PHONE'] = addText($params["PHONE"]);

        $SAVEDATA['ROLE'] = 'GUARDIAN';

        if ($objectId == "new") {
            self::dbAccess()->insert('t_guardian', $SAVEDATA);
            $lastId = self::findlastId(self::dbAccess()->lastInsertId());
            $objectId = $lastId->ID;
        } else {
            $WHERE[] = "ID = '" . $objectId . "'";
            if (!$errors)
                self::dbAccess()->update('t_guardian', $SAVEDATA, $WHERE);
        }

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array(
                "success" => true
                , "objectId" => $objectId
            );
        }
    }

    public static function sqlSearchStudentGuardian($params) {

        $code = isset($params["code"]) ? addText($params["code"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_guardian'), array("*"));

        if ($code)
            $SQL->where("A.CODE LIKE '" . $code . "%'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '" . $lastname . "%'");

        if ($gender)
            $SQL->where("A.GENDER = " . $gender . "");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonSearchStudentGuardian($params,$isJson = true) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $data = array();
        $i = 0;

        $result = self::sqlSearchStudentGuardian($params);
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public function jsonRemoveGuardian($Id) {
        self::dbAccess()->delete('t_guardian', array("ID='" . $Id . "'"));
        self::dbAccess()->delete('t_student_guardian', array("GUARDIAN_ID='" . $Id . "'"));

        return array(
            "success" => true
        );
    }

    public static function sqlStudentGuardian($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_guardian'), array('GUARDIAN_ID'));
        $SQL->joinLeft(array('B' => 't_student'), 'A.STUDENT_ID=B.ID', array('*'));

        if ($Id)
            $SQL->where("A.GUARDIAN_ID='" . $Id . "'");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonLoadStudentGuardian($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $data = array();
        $i = 0;

        $result = self::sqlStudentGuardian($objectId);
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                ////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

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

    public static function jsonActionAddStudentToGuardian($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $selectedCount = 0;

        if ($selectionIds) {
            $selectedStudents = explode(",", $selectionIds);
            if ($selectedStudents) {
                foreach ($selectedStudents as $studentId) {
                    $SAVEDATA['GUARDIAN_ID'] = $objectId;
                    $SAVEDATA['STUDENT_ID'] = $studentId;

                    self::dbAccess()->insert('t_student_guardian', $SAVEDATA);
                    ++$selectedCount;
                }
            }
        }

        return array(
            "success" => true
            , 'selectedCount' => $selectedCount
        );
    }

    public static function jsonLoadActiveStudentsForGuardian($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $data = array();
        $i = 0;

        $result = StudentDBAccess::sqlAllActiveStudents($params);
        if ($result) {
            foreach ($result as $value) {

                $studentAssigned = self::checkAssignedStudentGuardian($value->ID, $objectId);
                if (!$studentAssigned) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

                    ////////////////////////////////////////////////////////////
                    //Status of student...
                    ////////////////////////////////////////////////////////////
                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
                    $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                    $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                    $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

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

    public static function checkAssignedStudentGuardian($studentId, $guardianId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_guardian", array("C" => "COUNT(*)"));
        $SQL->where("GUARDIAN_ID = '" . $guardianId . "'");
        $SQL->where("STUDENT_ID = ?",$studentId);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonRemoveStudentGuardian($params) {

        $guardianId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $studentId = isset($params["removeId"]) ? addText($params["removeId"]) : '';

        self::dbAccess()->delete('t_student_guardian', array("GUARDIAN_ID = '" . $guardianId . "'", "STUDENT_ID = '" . $studentId . "'"));

        return array(
            "success" => true
        );
    }

    public function releaseGuardian($params) {

        $SAVEDATA = array();
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findObjectFromId($objectId);
        $status = $facette->STATUS;

        switch ($status) {
            case 0:
                $newStatus = 1;
                $SAVEDATA ["STATUS"] = 1;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update('t_guardian', $SAVEDATA, $WHERE);
                break;
            case 1:
                $newStatus = 0;
                $SAVEDATA ["STATUS"] = 0;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update('t_guardian', $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true, "status" => $newStatus);
    }

    protected static function findLoginName($loginname) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_guardian", array('*'));
        $SQL->where("LOGINNAME='" . $loginname . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

}

?>
