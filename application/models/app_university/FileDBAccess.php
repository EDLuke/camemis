<?php

///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 10.07.2013
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/user/UserRoleDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/CAMEMISUploadDBAccess.php';
require_once setUserLoacalization();

class FileDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new FileDBAccess();
        }
        return $me;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function findFileFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('t_file'));
        $SQL->where("ID='" . $Id . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getFileDataFromId($Id) {

        $data = array();
        $result = self::findFileFromId($Id);

        if ($result) {
            $data["ID"] = $result->ID;
            $data["GUID"] = $result->GUID;
            $data["NAME"] = setShowText($result->NAME);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }

    public static function findFileFromGuId($GuId) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('t_file'));
        $SQL->where("GUID='" . $GuId . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getFileDataFromGuId($GuId) {

        $data = array();
        $result = self::findFileFromGuId($GuId);

        if ($result) {
            $data["ID"] = $result->ID;
            $data["GUID"] = $result->GUID;
            $data["NAME"] = setShowText($result->NAME);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }

    public static function findGuidFromlastId($lastId) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('t_file'));
        $SQL->where("ID='" . $lastId . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonLoadFile($GuId) {

        $result = self::findFileFromGuId($GuId);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getFileDataFromGuId($GuId)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function getAllFilesQuery($params) {

        $parentId = isset($params["parentId"]) ? $params["parentId"] : '';
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : '';
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : '';
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : '';

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_file'));
        switch (UserAuth::getUserType()) {
            case "STUDENT":
                $SQL->joinRight(array('B' => 't_file_user'), 'A.GUID=B.FILE', array('B.FILE', 'B.ACADEMIC_ID'));
                $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'B.ACADEMIC_ID=C.SCHOOL_YEAR AND B.GRADE=C.GRADE', array('C.STUDENT'));
                if ($studentId) {
                    $SQL->where("C.STUDENT='" . $studentId . "'");
                }
                break;

            case "TEACHER":
            case "INSTRUCTOR":
                $SQL->joinRight(array('B' => 't_file_user'), 'A.GUID=B.FILE', array('B.FILE', 'B.USER_ROLE_ID'));
                $SQL->joinLeft(array('C' => 't_members'), 'B.USER_ROLE_ID=C.ROLE', array('C.ID AS MEMBER_ID'));
                if ($teacherId) {
                    $SQL->where("C.ID='" . $teacherId . "'");
                }
                break;

            case "SUPERADMIN":
            case "ADMIN":
                if ($parentId) {
                    $SQL->where("A.PARENT='" . $parentId . "'");
                } else {
                    $SQL->where("A.PARENT=0");
                }
                break;
        }

        if ($globalSearch) {
            $SQL->where("A.NAME LIKE '" . $globalSearch . "%'");
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonTreeAllFiles($params) {

        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : '';
        $result = self::getAllFilesQuery($params);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if ($value->NAME) {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['guid'] = "" . $value->GUID . "";
                    $data[$i]['parentId'] = "" . $value->PARENT . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['uploadFile'] = self::checkUploadFile($value->GUID);
                    $data[$i]['fileRecipient'] = self::checkFileRecipient($value->GUID);

                    if (!self::checkChild($value->GUID)) {
                        $data[$i]['leaf'] = true;
                        $data[$i]['iconCls'] = "icon-attach";
                    } else {
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                    }

                    $i++;
                }
            }

        return $data;
    }

    public function jsonSaveFile($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? $params["parentId"] : '';

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if ($parentId)
            $SAVEDATA['IS_CHILD'] = 1;

        if ($objectId == "new") {
            $uniqueId = generateGuid();
            $SAVEDATA['GUID'] = $uniqueId;
            $SAVEDATA['PARENT'] = $parentId;
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            self::dbAccess()->insert('t_file', $SAVEDATA);

            $lastId = self::dbAccess()->lastInsertId();
            $result = self::findGuidFromlastId($lastId);
            if ($result) {
                $objectId = $result->GUID;
            }
        } else {
            $WHERE = self::dbAccess()->quoteInto("GUID = ?", $objectId);
            self::dbAccess()->update('t_file', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function jsonRemoveFile($GuId) {

        self::dbAccess()->delete('t_file', array("GUID='" . $GuId . "'"));

        self::dbAccess()->delete('t_file_user', array("FILE='" . $GuId . "'"));

        CAMEMISUploadDBAccess::deleteFileFromObjectId($GuId, false, false);

        return array("success" => true);
    }

    public static function checkChild($GuId) {
        $facette = self::findFileFromGuId($GuId);
        $parentId = $facette ? $facette->ID : 0;
        if ($parentId) {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_file", array("C" => "COUNT(*)"));
            $SQL->where("PARENT = '" . $parentId . "'");
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    public static function checkUploadFile($GuId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_upload", array("C" => "COUNT(*)"));
        $SQL->where("OBJECT_ID = '" . $GuId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkFileRecipient($GuId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_file_user", array("C" => "COUNT(*)"));
        $SQL->where("FILE = '" . $GuId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getAcademicsByFile($params) {

        $DATA_FOR_JSON = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $result = AcademicLevelDBAccess::getSQLAllAcademics($params);

        if ($result)
            foreach ($result as $value) {

                $data['id'] = "" . $value->ID . "";
                $data['guid'] = "" . $value->GUID . "";
                $data['parentId'] = "" . $value->PARENT . "";
                $data['objectType'] = $value->OBJECT_TYPE;
                $data['schoolyearId'] = $value->SCHOOL_YEAR;

                switch ($value->OBJECT_TYPE) {

                    case "CAMPUS":
                        $data['leaf'] = false;
                        $data['text'] = setShowText($value->NAME);
                        $data['cls'] = "nodeFolderBold";
                        $data['iconCls'] = "icon-bricks";
                        break;
                    case "GRADE":
                        $data['leaf'] = false;
                        $data['text'] = setShowText($value->NAME);
                        $data['cls'] = "nodeTextBoldBlue";
                        $data['iconCls'] = "icon-folder_magnify";
                        break;
                    case "SCHOOLYEAR":
                        $data['leaf'] = true;
                        $data['text'] = setShowText($value->NAME);
                        $data['cls'] = "nodeTextBlue";
                        $data['iconCls'] = "icon-group_link";
                        $data['checked'] = self::checkFileAcademic($objectId, $value->ID);
                        break;
                }

                $DATA_FOR_JSON[] = $data;
            }

        return $DATA_FOR_JSON;
    }

    public static function checkFileAcademic($objectId, $Id) {

        $academicObject = AcademicDBAccess::findGradeFromId($Id);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_file_user", array("C" => "COUNT(*)"));
        $SQL->where("GRADE = '" . $academicObject->GRADE_ID . "'");
        $SQL->where("ACADEMIC_ID = '" . $academicObject->SCHOOL_YEAR . "'");
        $SQL->where("FILE = '" . $objectId . "'");

        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            return $result->C ? true : false;
        } else {
            return false;
        }
    }

    public static function jsonActionAcademicToFile($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $checked = isset($params["checked"]) ? $params["checked"] : "";
        $academicId = isset($params["academic"]) ? $params["academic"] : "";
        $userroleId = isset($params["userroleId"]) ? $params["userroleId"] : "";

        if ($checked == "true") {
            if ($academicId) {
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
                $SAVEDATA["ACADEMIC_ID"] = $academicObject->SCHOOL_YEAR;
            }

            if ($userroleId) {
                $SAVEDATA["USER_ROLE_ID"] = $userroleId;
            }

            $SAVEDATA["FILE"] = $objectId;
            self::dbAccess()->insert("t_file_user", $SAVEDATA);

            $msg = RECORD_WAS_ADDED;
        } else {
            if ($academicId) {
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                self::dbAccess()->delete("t_file_user", array("GRADE='" . $academicObject->GRADE_ID . "'", "ACADEMIC_ID='" . $academicObject->SCHOOL_YEAR . "'", "FILE='" . $objectId . "'"));
            }

            if ($userroleId) {
                self::dbAccess()->delete("t_file_user", array("USER_ROLE_ID='" . $userroleId . "'", "FILE='" . $objectId . "'"));
            }

            $msg = MSG_RECORD_NOT_CHANGED_DELETED;
        }

        return array("success" => true, "msg" => $msg);
    }

    public function treeAllStaffs($params) {

        $parent = $params["node"];
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $result = $this->getAllStaffsQuery($params);

        $data = array();

        if ($parent == 0) {

            $i = 0;
            if ($result)
                foreach ($result as $i => $value) {

                    $data[$i]['id'] = $value->ID;
                    $data[$i]['cls'] = "nodeTextBoldBlue";
                    $data[$i]['text'] = stripslashes($value->NAME);

                    switch ($value->ID) {
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                            $data[$i]['iconCls'] = "icon-user1_lock";
                            break;
                        default:
                            $data[$i]['iconCls'] = "icon-user1_monitor";
                            break;
                    }

                    $data[$i]['checked'] = self::checkFileStaff($objectId, $value->ID);
                    $i++;
                }
        }

        return $data;
    }

    public function getAllStaffsQuery() {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('t_memberrole'));
        //error_log($SQL);

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkFileStaff($objectId, $Id) {

        $userRoleObject = UserRoleDBAccess::findUserRoleFromId($Id);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_file_user", array("C" => "COUNT(*)"));
        $SQL->where("USER_ROLE_ID = '" . $userRoleObject->ID . "'");
        $SQL->where("FILE = '" . $objectId . "'");

        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            return $result->C ? true : false;
        } else {
            return false;
        }
    }

}

?>