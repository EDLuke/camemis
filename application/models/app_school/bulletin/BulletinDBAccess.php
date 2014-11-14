<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 8.05.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/training/TrainingDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/CAMEMISUploadDBAccess.php';
require_once setUserLoacalization();

class BulletinDBAccess {

    public $name;
    public $data = array();
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

    public static function getBulletinDataFromId($Id) {

        $facette = self::findBulletinFromId($Id);
        $data = array();
        if ($facette) {

            $data["ID"] = $facette->ID;
            $data["GUID"] = $facette->GUID;
            $data["CODE"] = $facette->CODE;
            $data["STATUS"] = $facette->STATUS;

            $data["EDUCATION_TYPE"] = $facette->EDUCATION_TYPE;

            $RECIPIENTS = explode(",", $facette->RECIPIENT);

            if ($RECIPIENTS) {
                foreach ($RECIPIENTS as $value) {
                    switch ($value) {
                        case "STUDENT":
                            $data["STUDENT"] = true;
                            break;
                        case "TEACHER":
                            $data["TEACHER"] = true;
                            break;
                        case "STAFF":
                            $data["STAFF"] = true;
                            break;
                        case "GUARDIAN":
                            $data["GUARDIAN"] = true;
                            break;
                    }
                }
            }

            $data["START_DATE"] = getShowDate($facette->START_DATE);
            $data["END_DATE"] = getShowDate($facette->END_DATE);

            $data["SUBJECT_BULLETIN"] = setShowText($facette->SUBJECT_BULLETIN);
            $data["CONTENT"] = setShowText($facette->CONTENT);
            $data["POSTED_DATE"] = getShowDateTime($facette->POSTED_DATE);
            $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($facette->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($facette->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($facette->DISABLED_DATE);
            $data["CREATED_BY"] = setShowText($facette->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($facette->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($facette->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($facette->DISABLED_BY);
        }

        return $data;
    }

    public static function loadBulletinFromId($Id) {

        $o = array(
            "success" => true
            , "data" => self::getBulletinDataFromId($Id)
        );
        return $o;
    }

    public static function findAcademicByTeacherId($teacherId, $type) {//@veasna
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_schedule"), array("ACADEMIC_ID AS ACADEMIC_ID", "TRAINING_ID"));
        switch ($type) {
            case 1:
                $SQL->joinLeft(array('B' => 't_grade'), 'A.ACADEMIC_ID=B.ID', array('*'));
                $SQL->where("B.EDUCATION_SYSTEM ='0'");
                break;
            case 2:
                break;
            case 3:
                $SQL->joinLeft(array('B' => 't_grade'), 'A.ACADEMIC_ID=B.ID', array('*'));
                $SQL->where("B.EDUCATION_SYSTEM ='1'");
                break;
        }
        $SQL->where("A.TEACHER_ID ='" . $teacherId . "'");
        //error_log("sql:".$SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $data = array();
        if ($result) {
            switch ($type) {
                case 1:
                    foreach ($result as $value) {
                        if (!in_array($value->PARENT, $data)) {
                            $data[] = $value->PARENT;
                        }
                    }
                    break;
                case 2:
                    foreach ($result as $value) {
                        if (!in_array($value->TRAINING_ID, $data)) {
                            $data[] = $value->TRAINING_ID;
                        }
                    }
                    break;
                case 3:
                    foreach ($result as $value) {
                        if (!in_array($value->PARENT, $data)) {
                            $data[] = $value->PARENT;
                        }
                        if (!in_array($value->ACADEMIC_ID, $data)) {
                            $data[] = $value->ACADEMIC_ID;
                        }
                    }
                    break;
            }
        }
        return $data;
    }

    public static function findAcademicByStudentId($studentId, $type) {//@veasna
        $SQL = self::dbAccess()->select();
        switch ($type) {
            case 1:  ///traditional
                $SQL->from(array('A' => "t_student_schoolyear"), array("*"));
                $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS=B.ID', array('PARENT'));
                $SQL->where("A.STUDENT = ?",$studentId);
                break;
            case 2: //training
                $SQL->from(array('A' => "t_student_training"), array("*"));
                $SQL->where("A.STUDENT = ?",$studentId);
                break;
            case 3: //credit
                $SQL->from(array('A' => "t_student_schoolyear_subject"), array("*"));
                $SQL->joinLeft(array('B' => 't_grade'), 'A.CREDIT_ACADEMIC_ID=B.ID', array('PARENT'));
                $SQL->where("A.STUDENT_ID = ?",$studentId);
                break;
        }

        //error_log("sql:".$SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $data = array();
        if ($result) {
            switch ($type) {
                case 1:
                    foreach ($result as $value) {
                        if (!in_array($value->PARENT, $data)) {
                            $data[] = $value->PARENT;
                        }
                    }
                    break;
                case 2:
                    foreach ($result as $value) {
                        if (!in_array($value->TERM, $data)) {
                            $data[] = $value->TERM;
                        }
                    }
                    break;
                case 3:
                    foreach ($result as $value) {
                        if (!in_array($value->PARENT, $data)) {
                            $data[] = $value->PARENT;
                        }
                        if (!in_array($value->CREDIT_ACADEMIC_ID, $data)) {
                            $data[] = $value->CREDIT_ACADEMIC_ID;
                        }
                    }
                    break;
            }
        }
        return $data;
    }

    public static function sqlUserBulletin($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('B' => "t_bulletin"), array('*'));  //@veasna
        $SQL->joinLeft(array('A' => 't_bulletin_academic'), 'A.BULLETIN=B.ID', array('ACADEMIC AS ACADEMIC'));

        switch (UserAuth::getUserType()) {
            case "INSTRUCTOR":
            case "TEACHER":
                $data = self::findAcademicByTeacherId(UserAuth::userId(), $type);
                $academicIds = ($data) ? implode(",", $data) : 'NULL';
                switch ($type) {
                    case 1:
                    case 3:
                        $SQL->where("A.ACADEMIC IN (" . $academicIds . ")");
                        break;
                    case 2:
                        $SQL->where("A.TRAINING_TERM IN (" . $academicIds . ")");
                        break;
                }

                $SQL->where("B.RECIPIENT LIKE '%TEACHER%'");
                break;
            case "STUDENT":
                $data = self::findAcademicByStudentId(UserAuth::userId(), $type);
                $academicIds = ($data) ? implode(",", $data) : 'NULL';
                switch ($type) {
                    case 1:
                    case 3:
                        $SQL->where("A.ACADEMIC IN (" . $academicIds . ")");
                        break;
                    case 2:
                        $SQL->where("A.TRAINING_TERM IN (" . $academicIds . ")");
                        break;
                }

                $SQL->where("B.RECIPIENT LIKE '%STUDENT%'");
                break;
            case "GUARDIAN":
                $SQL->where("B.RECIPIENT LIKE '%GUARDIAN%'");
                break;
            case "STAFF":
            case "ADMIN":
            case "SUPERADMIN":
                $SQL->where("B.RECIPIENT LIKE '%STAFF%'");
                break;
        }
        switch ($type) {
            case 1:
                $SQL->where("B.EDUCATION_TYPE ='GENERAL'");
                break;
            case 2:
                $SQL->where("B.EDUCATION_TYPE ='TRAINING'");
                break;
            case 3: //@veasna
                $SQL->where("B.EDUCATION_TYPE ='CREDIT'");
                break;
        }
        if ($globalSearch)
            $SQL->where("B.SUBJECT_BULLETIN LIKE '%" . $globalSearch . "%'");
        $SQL->order('B.POSTED_DATE DESC');
        $SQL->where("B.STATUS = '1'");
        $SQL->group("B.ID");
        //error_log("sql:".$SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function jsonAllBulletins($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $data = array();
        $i = 0;
        ///@veasna
        if (UserAuth::displayTraditionalEducationSystem()) {
            $params["type"] = 1;  //traditional
            $result1 = self::sqlUserBulletin($params);
            if ($result1) {
                foreach ($result1 as $value) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["SUBJECT_BULLETIN"] = setShowText($value->SUBJECT_BULLETIN);
                    $data[$i]["CONTENT"] = setShowText($value->CONTENT);
                    $data[$i]["POSTED_DATE"] = getShowDateTime($value->POSTED_DATE);
                    $data[$i]["EDUCATION_TYPE"] = TRADITIONAL_EDUCATION_SYSTEM;

                    $i++;
                }
            }
        }
        if (UserAuth::displayRoleTrainingEducation()) {
            $params["type"] = 2;  //training
            $result2 = self::sqlUserBulletin($params);
            if ($result2) {
                foreach ($result2 as $value) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["SUBJECT_BULLETIN"] = setShowText($value->SUBJECT_BULLETIN);
                    $data[$i]["CONTENT"] = setShowText($value->CONTENT);
                    $data[$i]["POSTED_DATE"] = getShowDateTime($value->POSTED_DATE);
                    $data[$i]["EDUCATION_TYPE"] = TRAINING_PROGRAMS;

                    $i++;
                }
            }
        }
        if (UserAuth::displayCreditEducationSystem()) {
            $params["type"] = 3; //credit
            $result3 = self::sqlUserBulletin($params);
            if ($result3) {
                foreach ($result3 as $value) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["SUBJECT_BULLETIN"] = setShowText($value->SUBJECT_BULLETIN);
                    $data[$i]["CONTENT"] = setShowText($value->CONTENT);
                    $data[$i]["POSTED_DATE"] = getShowDateTime($value->POSTED_DATE);
                    $data[$i]["EDUCATION_TYPE"] = CREDIT_EDUCATION_SYSTEM;

                    $i++;
                }
            }
        }
        ////
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

    public static function jsonRemoveBulletin($Id) {

        $facette = self::findBulletinFromId($Id);
        CAMEMISUploadDBAccess::deleteAllFiles($facette->GUID);
        self::dbAccess()->delete('t_bulletin', array("ID='" . $Id . "'"));
        return array(
            "success" => true
        );
    }

    public static function jsonAddBulletin($params) {

        $SAVEDATA = array();

        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $name = isset($params["name"]) ? addText($params["name"]) : "";

        $count = self::getCount();

        if (!$count) {
            $SAVEDATA['ID'] = 10;
        }

        if (isset($params["START_DATE"]))
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);

        if (isset($params["END_DATE"]))
            $SAVEDATA["END_DATE"] = setDate2DB($params["END_DATE"]);

        if (isset($params["EDUCATION_TYPE"]))
            $SAVEDATA['EDUCATION_TYPE'] = addText($params["EDUCATION_TYPE"]);

        $SAVEDATA['SUBJECT_BULLETIN'] = addText($name);
        $SAVEDATA['POSTED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        $SAVEDATA['CODE'] = createCode();
        $SAVEDATA['GUID'] = generateGuid();

        if ($name != "" && $parentId)
            self::dbAccess()->insert('t_bulletin', $SAVEDATA);

        return array("success" => true);
    }

    public static function jsonSaveBulletin($params) {

        $SAVEDATA = array();
        $WHERE = array();
        $RECIPIENTS = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $facette = self::findBulletinFromId($objectId);

        if (isset($params["START_DATE"]))
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);

        if (isset($params["END_DATE"]))
            $SAVEDATA["END_DATE"] = setDate2DB($params["END_DATE"]);

        if (isset($params["SUBJECT_BULLETIN"]))
            $SAVEDATA['SUBJECT_BULLETIN'] = addText($params["SUBJECT_BULLETIN"]);

        if (isset($params["EDUCATION_TYPE"]))
            $SAVEDATA['EDUCATION_TYPE'] = addText($params["EDUCATION_TYPE"]);

        if (isset($params["CONTENT"]))
            $SAVEDATA['CONTENT'] = addText($params["CONTENT"]);

        if (isset($params["STUDENT"]))
            $RECIPIENTS['STUDENT'] = "STUDENT";
        if (isset($params["TEACHER"])) {
            $RECIPIENTS['TEACHER'] = "TEACHER";
        }
        if (isset($params["STAFF"]))
            $RECIPIENTS['STAFF'] = "STAFF";
        if (isset($params["GUARDIAN"]))
            $RECIPIENTS['GUARDIAN'] = "GUARDIAN";

        if ($RECIPIENTS)
            $SAVEDATA["RECIPIENT"] = implode(",", $RECIPIENTS);

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        if ($facette) {
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_bulletin', $SAVEDATA, $WHERE);
        } else {
            $SAVEDATA['POSTED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA['CODE'] = createCode();
            $SAVEDATA['GUID'] = generateGuid();

            self::dbAccess()->insert('t_bulletin', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function jsonReleaseBulletin($params) {

        $objectId = $params["objectId"] ? addText($params["objectId"]) : "0";
        $facette = self::findBulletinFromId($objectId);
        $status = $facette->STATUS;

        $data = array();
        switch ($status) {
            case 0:
                $newStatus = 1;
                $data['STATUS']      = 1;
                $data['POSTED_DATE'] = "'". getCurrentDBDateTime() ."'";
                $data['ENABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                $data['ENABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                break;
            case 1:
                $newStatus = 0;
                $data['STATUS']       = 0;
                $data['DISABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                $data['DISABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                break;
        }
        self::dbAccess()->update("t_bulletin", $data, "ID='". $objectId ."'");
        return array("success" => true, "status" => $newStatus);
    }

    public static function findBulletinFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_bulletin", array('*'));
        if ($Id)
            $SQL->where("ID = ?", $Id);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function getCount() {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_bulletin', 'COUNT(*) AS C');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function searchBulletins($params, $isJson = true) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->queryAllBulletins($params, " A.ID", " A.CONTENT");
        $i = 0;

        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["POSTED_DATE"] = setShowText($value->POSTED_DATE);
                $data[$i]["SUBJECT_BULLETIN"] = $value->SUBJECT_BULLETIN;
                $data[$i]["CONTENT"] = $value->CONTENT;
                $data[$i]["RECIPIENT"] = $value->RECIPIENT;
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                if ($value->EDUCATION_TYPE) {
                    switch ($value->EDUCATION_TYPE) {
                        case "GENERAL":
                            $data[$i]["CREATED_DATE"] .= " " . TRADITIONAL_EDUCATION_SYSTEM;
                            break;
                        case "CREDIT":
                            $data[$i]["CREATED_DATE"] .= " " . CREDIT_EDUCATION_SYSTEM;
                            break;
                        case "TRAINING":
                            $data[$i]["CREATED_DATE"] .= " " . TRAINING_PROGRAMS;
                            break;
                    }
                }
                $data[$i]["CREATED_DATE"] .= "<br>";
                $data[$i]["CREATED_DATE"] .= $value->CREATED_BY;


                $i++;
            }
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
        if ($isJson) {
            return $dataforjson;
        } else {
            return $data;
        }
    }

    public function queryAllBulletins($params) {

        $subjectBulletin = isset($params["subject_bulletin"]) ? $params["subject_bulletin"] : "";
        $choose_status = isset($params["choose_status"]) ? $params["choose_status"] : "";
        $startDate = isset($params["startDate"]) ? addText($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? addText($params["endDate"]) : "";
        $recipient = isset($params["recipient"]) ? $params["recipient"] : "";

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.STATUS AS STATUS";
        $SQL .= " ,A.POSTED_DATE AS POSTED_DATE";
        $SQL .= " ,A.SUBJECT_BULLETIN AS SUBJECT_BULLETIN";
        $SQL .= " ,A.RECIPIENT AS RECIPIENT";
        $SQL .= " ,A.CONTENT AS CONTENT";
        $SQL .= " ,A.CREATED_DATE AS CREATED_DATE";
        $SQL .= " ,A.CREATED_BY AS CREATED_BY";
        $SQL .= " ,A.EDUCATION_TYPE AS EDUCATION_TYPE";
        $SQL .= " FROM t_bulletin AS A";

        $SQL .= " WHERE 1=1";

        if ($subjectBulletin) {
            $SQL .= " AND SUBJECT_BULLETIN LIKE '%" . $subjectBulletin . "%'";
        }

        if ($recipient != "") {
            $SQL .= " AND RECIPIENT LIKE '%" . $recipient . "%'";
        }

        if ($choose_status != "") {

            $SQL .= " AND STATUS='" . $choose_status . "'";
        }

        if ($startDate && $endDate) {
            $SQL .= " AND START_DATE >= '" . $startDate . "' AND START_DATE <= '" . $endDate . "' ";
        }
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public static function getAcademicsByBulletin($params) {

        $DATA_FOR_JSON = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $educationSystem = isset($params["educationSystem"]) ? addText($params["educationSystem"]) : 0;

        $result = AcademicLevelDBAccess::getSQLAllAcademics($params);

        if ($result)
            foreach ($result as $value) {

                $data['id'] = "" . $value->ID . "";
                $data['parentId'] = "" . $value->PARENT . "";
                $data['objectType'] = $value->OBJECT_TYPE;
                $data['schoolyearId'] = $value->SCHOOL_YEAR;
                if ($educationSystem) {
                    switch ($value->OBJECT_TYPE) {

                        case "CAMPUS":
                            $data['leaf'] = false;
                            $data['text'] = setShowText($value->NAME);
                            $data['cls'] = "nodeCampus";
                            $data['iconCls'] = "icon-bricks";
                            break;
                        case "SCHOOLYEAR":
                            $data['leaf'] = false;
                            $data['text'] = setShowText($value->NAME);
                            $data['cls'] = "nodeGrade";
                            $data['iconCls'] = "icon-date";
                            $data['checked'] = self::checkBulletinAcademic($objectId, $value->ID);
                            break;
                        case "SUBJECT":
                            $data['leaf'] = true;
                            $subjectObject = SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);

                            if ($subjectObject) {
                                $data['title'] = $subjectObject->NAME;
                                if ($subjectObject->SHORT) {
                                    $data['text'] = "($subjectObject->SHORT) " . $subjectObject->NAME;
                                } else {
                                    $data['text'] = $subjectObject->NAME;
                                }
                            } else {
                                $data['text'] = "?";
                            }
                            //$data['text'] = setShowText($value->NAME);
                            $data['cls'] = "nodeFolderBold";
                            $data['iconCls'] = "icon-folder_star";
                            $data['checked'] = self::checkBulletinAcademic($objectId, $value->ID);
                            break;
                    }
                } else {
                    switch ($value->OBJECT_TYPE) {

                        case "CAMPUS":
                            $data['leaf'] = false;
                            $data['text'] = setShowText($value->NAME);
                            $data['cls'] = "nodeCampus";
                            $data['iconCls'] = "icon-bricks";
                            break;
                        case "GRADE":
                            $data['leaf'] = false;
                            $data['text'] = setShowText($value->NAME);
                            $data['cls'] = "nodeGrade";
                            $data['iconCls'] = "icon-folder_magnify";
                            break;
                        case "SCHOOLYEAR":
                            $data['leaf'] = true;
                            $data['text'] = setShowText($value->NAME);
                            $data['cls'] = "nodeTextBoldBlue";
                            $data['iconCls'] = "icon-date_edit";
                            $data['checked'] = self::checkBulletinAcademic($objectId, $value->ID);
                            break;
                    }
                }

                $DATA_FOR_JSON[] = $data;
            }

        return $DATA_FOR_JSON;
    }

    public static function getTrainingsByBulletin($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        if ($node == 0) {
            $resultRows = TrainingDBAccess::allTrainingprograms(false);
        } else {
            $resultRows = TrainingDBAccess::allTrainingprograms($node, false);
        }

        $data = array();
        $i = 0;
        if ($resultRows)
            foreach ($resultRows as $value) {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = stripslashes($value->NAME);
                $data[$i]['leaf'] = false;
                $data[$i]['cls'] = "nodeTextBoldBlue";

                switch ($value->OBJECT_TYPE) {
                    case "PROGRAM":
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-bricks";

                        break;

                    case "LEVEL":
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-folder_magnify";

                        break;

                    case "TERM":
                        $data[$i]['leaf'] = true;
                        $data[$i]['text'] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                        if ($value->STATUS == 1) {
                            $data[$i]['iconCls'] = "icon-date";
                        } else {
                            $data[$i]['iconCls'] = "icon-date_edit";
                        }
                        $data[$i]['checked'] = self::checkBulletinAcademic($objectId, $value->ID);
                        break;
                }
                $i++;
            }

        return $data;
    }

    public static function checkBulletinAcademic($objectId, $Id) {

        $facette = self::findBulletinFromId($objectId);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_bulletin_academic", array("C" => "COUNT(*)"));

        switch ($facette->EDUCATION_TYPE) {
            case "GENERAL":
                $academicObject = AcademicDBAccess::findGradeFromId($Id);
                $SQL->where("ACADEMIC = '" . $academicObject->ID . "'");
                $SQL->where("SCHOOLYEAR = '" . $academicObject->SCHOOL_YEAR . "'");
                break;
            case "CREDIT": //@veasna
                $academicObject = AcademicDBAccess::findGradeFromId($Id);
                $SQL->where("ACADEMIC = '" . $academicObject->ID . "'");
                $SQL->where("SCHOOLYEAR = '" . $academicObject->SCHOOL_YEAR . "'");
                break;
            case "TRAINING":
                $SQL->where("TRAINING_TERM = '" . $Id . "'");
                break;
        }

        $SQL->where("BULLETIN = '" . $objectId . "'");

        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            return $result->C ? true : false;
        } else {
            return false;
        }
    }

    public static function jsonActionAcademicToBulletin($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $checked = isset($params["checked"]) ? addText($params["checked"]) : "";
        $academicId = isset($params["academic"]) ? (int) $params["academic"] : "";

        $facette = self::findBulletinFromId($objectId);

        if ($facette->STATUS) {
            $msg = MSG_RECORD_NOT_CHANGED_DELETED;
        } else {
            if ($checked == "true") {

                switch ($facette->EDUCATION_TYPE) {
                    case "GENERAL":
                        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                        self::dbAccess()->delete("t_bulletin_academic", array("ACADEMIC='" . $academicObject->ID . "'", "SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'", "BULLETIN='" . $objectId . "'"));
                        $SAVEDATA["ACADEMIC"] = $academicObject->ID;
                        $SAVEDATA["SCHOOLYEAR"] = $academicObject->SCHOOL_YEAR;
                        $SAVEDATA["BULLETIN"] = $objectId;
                        self::dbAccess()->insert("t_bulletin_academic", $SAVEDATA);
                        break;
                    case "CREDIT": //@veasna
                        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                        self::dbAccess()->delete("t_bulletin_academic", array("ACADEMIC='" . $academicObject->ID . "'", "SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'", "BULLETIN='" . $objectId . "'"));
                        $SAVEDATA["ACADEMIC"] = $academicObject->ID;
                        $SAVEDATA["SCHOOLYEAR"] = $academicObject->SCHOOL_YEAR;
                        $SAVEDATA["BULLETIN"] = $objectId;
                        self::dbAccess()->insert("t_bulletin_academic", $SAVEDATA);
                        break;
                    case "TRAINING":
                        self::dbAccess()->delete("t_bulletin_academic", array("TRAINING_TERM='" . $academicId . "'", "BULLETIN='" . $objectId . "'"));
                        $SAVEDATA["TRAINING_TERM"] = $academicId;
                        $SAVEDATA["BULLETIN"] = $objectId;
                        self::dbAccess()->insert("t_bulletin_academic", $SAVEDATA);
                        break;
                }
                $msg = RECORD_WAS_ADDED;
            } else {
                switch ($facette->EDUCATION_TYPE) {
                    case "GENERAL":
                        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                        self::dbAccess()->delete("t_bulletin_academic", array("ACADEMIC='" . $academicObject->GRADE_ID . "'", "SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'", "BULLETIN='" . $objectId . "'"));
                        break;
                    case "CREDIT": //@veasna
                        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                        self::dbAccess()->delete("t_bulletin_academic", array("ACADEMIC='" . $academicObject->ID . "'", "SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'", "BULLETIN='" . $objectId . "'"));
                        break;
                    case "TRAINING":
                        self::dbAccess()->delete("t_bulletin_academic", array("TRAINING_TERM='" . $academicId . "'", "BULLETIN='" . $objectId . "'"));
                        break;
                }
                $msg = MSG_RECORD_NOT_CHANGED_DELETED;
            }
        }

        return array("success" => true, "msg" => $msg);
    }

}

?>