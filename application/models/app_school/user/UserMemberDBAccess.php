<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/SessionAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class UserMemberDBAccess {

    protected $data = array();
    protected $out = array();
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

    public function getUserDataFromId($Id) {

        $result = self::findUserFromId($Id);

        $data = array();

        if ($result) {

            if (!$result->LOGINNAME) {
                self::setMappingCode_LoginName($result->ID);
            }

            $data["ID"] = $result->ID;
            $data["LOGINNAME"] = setShowText($result->LOGINNAME);
            $data["FIRSTNAME"] = setShowText($result->FIRSTNAME);
            $data["LASTNAME"] = setShowText($result->LASTNAME);
            $data["STATUS"] = $result->STATUS;
            $data["ROLE"] = $result->ROLE;
            $data["ADDITIONAL_ROLE"] = $result->ADDITIONAL_ROLE;

            $data["NAME"] = setShowText($result->NAME);
            $data["EMAIL"] = setShowText($result->EMAIL);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);

            $data["ROLE"] = $result->ROLE;
            $data["USER_ROLE"] = $result->ROLE;
            $data["IS_PASSWORD"] = $result->PASSWORD;
            $data["SUPERUSER"] = $result->SUPERUSER;

            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);

            $data["REMOVE_STATUS"] = $this->checkRemoveUser($result->ID);
        }

        return $data;
    }

    public function getUserNameFromLID($LId) {

        $SQL = "SELECT CONCAT(LASTNAME,' ', FIRSTNAME) AS NAME ";
        $SQL .= " FROM t_members";
        $SQL .= " WHERE";
        $SQL .= " LID = '" . $LId . "'";

        $result = self::dbAccess()->fetchRow($SQL);
        if ($result) {
            return $result->NAME;
        } else {
            return "";
        }
    }

    public function getUserNameFromCode($Code) {

        $SQL = "SELECT CONCAT(LASTNAME,' ', FIRSTNAME) AS NAME ";
        $SQL .= " FROM t_members";
        $SQL .= " WHERE";
        $SQL .= " CODE = '" . $Code . "'";

        $result = self::dbAccess()->fetchRow($SQL);
        if ($result) {
            return $result->NAME;
        } else {
            return "";
        }
    }

    public static function findUserFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_members", array('*'));
        $SQL->where("ID = '" . $Id . "'");
        //echo $SQL->__toString();
        return self::dbAccess()->fetchRow($SQL);
    }

    public function allUsers($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $data = array();
        $entries = $this->getAllUsersQuery($params);
        $i = 0;
        if ($entries)
            foreach ($entries as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["PHONE"] = $value->PHONE;
                $data[$i]["EMAIL"] = $value->EMAIL;
                $data[$i]["LOGINNAME"] = $value->LOGINNAME;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["STATUS_NAME"] = getStatus($value->STATUS);

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

    public function getAllUsersQuery($params) {

        $searchRole = isset($params["searchRole"]) ? $params["searchRole"] : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.STATUS AS STATUS";
        $SQL .= " ,A.PHONE AS PHONE";
        $SQL .= " ,A.EMAIL AS EMAIL";
        $SQL .= " ,A.ROLE AS ROLE";
        $SQL .= " ,A.FIRSTNAME AS FIRSTNAME";
        $SQL .= " ,A.LASTNAME AS LASTNAME";
        $SQL .= " ,A.LOGINNAME AS LOGINNAME";
        $SQL .= " ,CONCAT('(',A.CODE,') ',A.LASTNAME,' ', A.FIRSTNAME) AS NAME";
        $SQL .= " ,A.DESCRIPTION AS DESCRIPTION";
        $SQL .= " FROM t_members AS A";
        $SQL .= " LEFT JOIN t_staff AS B ON B.ID = A.ID";
        $SQL .= " WHERE 1=1";

        if (!Zend_Registry::get('IS_SUPER_ADMIN')) {
            $SQL .= " AND ROLE<>1";
        }

        if ($searchRole) {
            $SQL .= " AND A.ROLE = '" . $searchRole . "'";
        }

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        $SQL .= " ORDER BY A.LASTNAME";

        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function loadUserFromId($Id) {

        $result = self::findUserFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getUserDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function updateUser($params) {

        $errors = Array();

        if (isset($params["LOGINNAME"])) {
            $SAVEDATA['LOGINNAME'] = addText($params["LOGINNAME"]);
            $SAVEDATA['CHANGED_LOGINNAME'] = 1;
        }

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["FIRSTNAME"]))
            $SAVEDATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);
        if (isset($params["LASTNAME"]))
            $SAVEDATA['LASTNAME'] = addText($params["LASTNAME"]);
        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["USER_ROLE"]))
            $SAVEDATA['ROLE'] = $params["USER_ROLE"];
        if (isset($params["ADDITIONAL_ROLE"]))
            $SAVEDATA['ADDITIONAL_ROLE'] = $params["ADDITIONAL_ROLE"];

        $password = isset($params["PASSWORD"]) ? addText($params["PASSWORD"]) : "";
        $password_repeat = isset($params["PASSWORD_REPEAT"]) ? addText($params["PASSWORD_REPEAT"]) : "";

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
                $SAVEDATA['CHANGE_PASSWORD'] = 1;
                $SAVEDATA['UMCPANL'] = 0;
                $SAVEDATA['CHANGE_PASSWORD_DATE'] = time();
                $SAVEDATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
            }
        }

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $params["objectId"]);
        self::dbAccess()->update('t_members', $SAVEDATA, $WHERE);

        $DB_STAFF = StaffDBAccess::getInstance();
        $STAFF_OBJECT = StaffDBAccess::findStaffFromId($params["objectId"]);

        if (isset($params["FIRSTNAME"]))
            $STAFF_DATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);
        if (isset($params["LASTNAME"]))
            $STAFF_DATA['LASTNAME'] = addText($params["LASTNAME"]);

        if ($STAFF_OBJECT) {
            $USER_OBJECT = self::findUserFromId($params["objectId"]);
            $STAFF_DATA['CODE'] = $USER_OBJECT->LOGINNAME;
            if (isset($params["USER_ROLE"]))
                $STAFF_DATA['TUTOR'] = $DB_STAFF->getTutorByRoleId($params["USER_ROLE"]);
            $STAFF_DATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $STAFF_DATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            $WHERE_STAFF = self::dbAccess()->quoteInto("ID = ?", $params["objectId"]);
            self::dbAccess()->update('t_staff', $STAFF_DATA, $WHERE_STAFF);
        } else {
            $USER_OBJECT = self::findUserFromId($params["objectId"]);
            $STAFF_DATA['ID'] = $params["objectId"];
            $STAFF_DATA['CODE'] = $USER_OBJECT->LOGINNAME;
            $STAFF_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $STAFF_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert('t_staff', $STAFF_DATA);
        }

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array(
                "success" => true
            );
        }
    }

    public function addUser($params) {

        $uniqueId = generateGuid();
        $SAVEDATA['ID'] = $uniqueId;

        $SAVEDATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);
        $SAVEDATA['LASTNAME'] = addText($params["LASTNAME"]);
        $SAVEDATA['LOGINNAME'] = createCode();
        $SAVEDATA['CODE'] = createCode();
        $SAVEDATA['LID'] = createCode();
        $SAVEDATA['ROLE'] = addText($params["roleId"]);

        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

        self::dbAccess()->insert('t_members', $SAVEDATA);

        $DB_STAFF = StaffDBAccess::getInstance();
        $USER_OBJECT = self::findUserFromId($uniqueId);

        if ($USER_OBJECT) {

            $STAFF_DATA['NAME'] = $USER_OBJECT->FIRSTNAME . ", " . $USER_OBJECT->LASTNAME;
            $STAFF_DATA['FIRSTNAME'] = $USER_OBJECT->FIRSTNAME;
            $STAFF_DATA['LASTNAME'] = $USER_OBJECT->LASTNAME;
            $STAFF_DATA['ID'] = $USER_OBJECT->ID;
            $STAFF_DATA['CODE'] = $USER_OBJECT->CODE;
            $STAFF_DATA['STAFF_SCHOOL_ID'] = $USER_OBJECT->CODE;
            $STAFF_DATA['TUTOR'] = $DB_STAFF->getTutorByRoleId($params["roleId"]);
            $STAFF_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $STAFF_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert('t_staff', $STAFF_DATA);
        }

        return array("success" => true);
    }

    public function sessionExpired() {

        $session = SessionAccess::getInstance();
        $status = $session->verifyTime(addText(Zend_Registry::get('SESSIONID')));

        return array("success" => true, "status" => $status);
    }

    public function releaseObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findUserFromId($objectId);

        if ($facette->PASSWORD) {
            $error = false;
        } else {
            $error = true;
        }

        $status = $facette->STATUS;

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_members";
        $SQL .= " SET";

        switch ($status) {
            case 0:
                $newStatus = 1;
                $SQL .= " STATUS=1";
                $SQL .= " ,ENABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,ENABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";

                $STAFF_DATA["STATUS"] = 1;

                break;
            case 1:
                $newStatus = 0;
                $SQL .= " STATUS=0";
                $SQL .= " ,DISABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,DISABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";

                $STAFF_DATA["STATUS"] = 0;

                break;
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $objectId . "'";

        self::dbAccess()->query($SQL);

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
        self::dbAccess()->update('t_staff', $STAFF_DATA, $WHERE);

        return array("success" => true, "status" => $newStatus);
    }

    public function removeObject($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $STAFF_DB = StaffDBAccess::getInstance();
        $check = $STAFF_DB->checkStaffAssignment($removeId);

        if (!$check) {
            self::dbAccess()->delete('t_staff', array("ID='" . $removeId . "'"));
            self::dbAccess()->delete('t_members', array("ID='" . $removeId . "'"));
            self::dbAccess()->delete('t_teacher_subject', array("TEACHER='" . $removeId . "'"));
            self::dbAccess()->delete('t_subject_teacher_class', array("TEACHER='" . $removeId . "'"));
            self::dbAccess()->delete('t_staff_campus', array("STAFF='" . $removeId . "'"));
            self::dbAccess()->delete('t_schedule', array("TEACHER_ID='" . $removeId . "'"));
            self::dbAccess()->delete('t_teacher_examination', array("STAFF_ID='" . $removeId . "'"));
            self::dbAccess()->delete('t_teaching_session', array("TEACHER_ID='" . $removeId . "'"));
            self::dbAccess()->delete('t_subject_teacher_training', array("TEACHER='" . $removeId . "'"));
        }

        return array(
            "success" => true
        );
    }

    public function checkRemoveUser($Id) {

        $DB_STAFF = StaffDBAccess::getInstance();
        return $DB_STAFF->checkStaffAssignment($Id);
    }

    public static function setMappingCode_LoginName($Id) {

        $facette = StaffDBAccess::findStaffFromId($Id);
        $user = self::findUserFromId($Id);

        if (!$user->CHANGED_LOGINNAME) {
            if (isset($facette->CODE)) {
                if (!$user->SUPERUSER) {
                    $SAVEDATA['LOGINNAME'] = $facette->CODE;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                    self::dbAccess()->update('t_members', $SAVEDATA, $WHERE);
                } else {
                    $SAVEDATA['CODE'] = $user->LOGINNAME;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                    self::dbAccess()->update('t_staff', $SAVEDATA, $WHERE);
                }
            }
        }
    }

}

?>