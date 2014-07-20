<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/assignment/AssignmentDBAccess.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SchooleventDBAccess {

    private $dataforjson = null;
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

    public function getSchooleventDataFromId($Id) {

        $result = $this->findSchooleventFromId($Id);

        $data = array();
        if ($result) {

            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);
            $data["STATUS"] = $result->STATUS;
            $data["REMARK"] = setShowText($result->REMARK);
            $data["LOCATION"] = setShowText($result->LOCATION);
            $data["LOCATION"] = setShowText($result->LOCATION);
            $data["TEACHER_ID"] = $result->TEACHER_ID;
            $data["CLASS_ID"] = $result->CLASS_ID;
            $data["Classes"] = $result->CLASS_ID; //THORN Visal
            $data["SUBJECT_ID"] = $result->SUBJECT_ID;
            $data["ASSIGNMENT_ID"] = $result->ASSIGNMENT_ID;
            $data["DAY_OFF_SCHOOL"] = $result->DAY_OFF_SCHOOL ? true : false;
            if (UserAuth::getUserType() == "STUDENT")
                $data["DAY_OFF_SCHOOL"] = $result->DAY_OFF_SCHOOL ? "Yes" : "No";
            $data["START_HOUR"] = $result->START_HOUR;
            $data["END_HOUR"] = $result->END_HOUR;
            $data["EVENT_TYPE"] = $result->EVENT_TYPE;
            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);
            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
        }

        return $data;
    }

    public function findSchooleventFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_schoolevent";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";

        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadObject($Id) {

        $result = $this->findSchooleventFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getSchooleventDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function getAllSchooleventsQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $eventType = isset($params["eventType"]) ? addText($params["eventType"]) : "SCHOOL";
        $status = isset($params["status"]) ? addText($params["status"]) : "";
        $dayoffschool = isset($params["dayoffschool"]) ? addText($params["dayoffschool"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";

        $SQL = "";
        $SQL .= " SELECT A.ID AS ID
        ,A.NAME AS EVENT_NAME
        ,A.DAY_OFF_SCHOOL AS DAY_OFF_SCHOOL
        ,A.CLASS_ID AS CLASS_ID
        ,A.STATUS AS STATUS
        ,A.START_DATE AS START_DATE
        ,A.END_DATE AS END_DATE
        ,A.START_HOUR AS START_HOUR
        ,A.END_HOUR AS END_HOUR
        ,A.EVENT_TYPE AS EVENT_TYPE
        ,A.REMARK AS REMARK
        ,A.LOCATION AS LOCATION
        ,A.LOCATION AS LOCATION
        ,C.NAME AS SUBJECT_NAME
        ,D.NAME AS GRADE_NAME
        ";
        $SQL .= " FROM t_schoolevent AS A";
        $SQL .= " LEFT JOIN t_assignment AS B ON B.ID = A.ASSIGNMENT_ID";
        $SQL .= " LEFT JOIN t_subject AS C ON C.ID = A.SUBJECT_ID";
        $SQL .= " LEFT JOIN t_grade AS D ON D.ID = A.CLASS_ID";
        $SQL .= " WHERE 1=1";

        if ($schoolyearId) {
            $SQL .= " AND A.SCHOOL_YEAR = '" . $schoolyearId . "'";
        }

        if ($subjectId) {
            $SQL .= " AND A.SUBJECT_ID = '" . $subjectId . "'";
        }

        if ($teacherId) {
            $SQL .= " AND A.TEACHER_ID = '" . $teacherId . "'";
        }

        //if ($classId) {
        //$SQL .= " AND A.CLASS_ID = '" . $classId . "'";
        //}

        if ($status) {
            $SQL .= " AND A.STATUS = '" . $status . "'";
        }

        if ($dayoffschool) {
            $SQL .= " AND A.DAY_OFF_SCHOOL = '" . $dayoffschool . "'";
        }
        if ($target == 'general') {
            $SQL .= " AND A.ISTRAINING = 0";
        }
        if ($target == 'training') {
            $SQL .= " AND A.ISTRAINING = 1";
        }

        switch ($eventType) {
            case 1:
                $SQL .= " AND A.EVENT_TYPE = '" . $eventType . "'";
                break;
            case 2:
                $SQL .= " AND A.EVENT_TYPE = '" . $eventType . "'";
                break;
        }

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        $SQL .= " ORDER BY A.CREATED_DATE DESC";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    ///////////////// THORN Visal
    public function getTeacherAllSchoolEvents($params) {

        $schoolparam['eventType'] = 1;
        $schoolparam['schoolyearId'] = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $resultschool = self::getAllSchooleventsQuery($schoolparam);

        return $resultschool;
    }

    public function getTeacherAllClassEvents($params) {

        $params['eventType'] = 2;
        $resultclass = self::getAllSchooleventsQuery($params);

        return $resultclass;
    }
    
    public function allSchoolevents($params, $isJson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getAllSchooleventsQuery($params);

        $data = array();

        $i = 0;
        
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CLASS"] = $value->CLASS_ID;

                if ($value->SUBJECT_NAME) {
                    $data[$i]["EVENT_NAME"] = setShowText($value->EVENT_NAME) . " (" . $value->SUBJECT_NAME . ")";
                } else {
                    $data[$i]["EVENT_NAME"] = setShowText($value->EVENT_NAME);
                }

                $date = getShowDate($value->START_DATE);
                $date .= " - " . getShowDate($value->END_DATE);

                $data[$i]["DATE"] = $date;
                $time = $value->START_HOUR;
                $time .= " - " . $value->END_HOUR;

                $data[$i]["TIME"] = $time;
                $data[$i]["REMARK"] = setShowText($value->REMARK);
                $data[$i]["LOCATION"] = setShowText($value->LOCATION);

                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["START_HOUR"] = $value->START_HOUR;
                $data[$i]["END_HOUR"] = $value->END_HOUR;

                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                $data[$i]["STATUS"] = $value->STATUS;
                //////// THORN Visal
                $eventType = $value->EVENT_TYPE;
                if ($eventType == '1') {
                    $data[$i]["EVENT_TYPE"] = "School Event";
                } else {
                    $data[$i]["EVENT_TYPE"] = $value->GRADE_NAME . " (Class Event)";
                }
                ////////
                if (isset($value->STATUS)) {
                    $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                    $data[$i]["STATUS"] = $value->STATUS;
                }

                $i++;
            }
        }


//        //////////THORN Visal
//        switch (UserAuth::getUserType()) {
//            case "TEACHER":
//            case "INSTRUCTOR":
//                $resultschool = self::getTeacherAllSchoolEvents($params);
//                $resultclass = self::getTeacherAllClassEvents($params);
//
//                $data = array();
//
//                $i = 0;
//
//                if ($resultschool)
//                    foreach ($resultschool as $value) {
//
//                        $data[$i]["ID"] = $value->ID;
//                        $data[$i]["CLASS"] = $value->CLASS_ID;
//
//                        if ($value->SUBJECT_NAME) {
//                            $data[$i]["EVENT_NAME"] = setShowText($value->EVENT_NAME) . " (" . $value->SUBJECT_NAME . ")";
//                        } else {
//                            $data[$i]["EVENT_NAME"] = setShowText($value->EVENT_NAME);
//                        }
//
//                        $date = getShowDate($value->START_DATE);
//                        $date .= " - " . getShowDate($value->END_DATE);
//
//                        $data[$i]["DATE"] = $date;
//
//                        $time = $value->START_HOUR;
//                        $time .= " - " . $value->END_HOUR;
//
//                        $data[$i]["TIME"] = $time;
//
//                        $data[$i]["REMARK"] = setShowText($value->REMARK);
//                        $data[$i]["LOCATION"] = setShowText($value->LOCATION);
//
//                        $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
//                        $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
//                        $data[$i]["START_HOUR"] = $value->START_HOUR;
//                        $data[$i]["END_HOUR"] = $value->END_HOUR;
//
//                        $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
//                        $data[$i]["STATUS"] = $value->STATUS;
//                        //////// THORN Visal
//                        $classEvent = $value->GRADE_NAME;
//                        if (!$classEvent) {
//                            $data[$i]["EVENT_TYPE"] = "School Event";
//                        } else {
//                            $data[$i]["EVENT_TYPE"] = $value->GRADE_NAME . " (Class Event)";
//                        }
//                        ////////
//                        if (isset($value->STATUS)) {
//                            $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
//                            $data[$i]["STATUS"] = $value->STATUS;
//                        }
//
//                        $i++;
//                    }
//                $j = count($resultschool);
//                if ($resultclass)
//                    foreach ($resultclass as $value) {
//
//                        $data[$j]["ID"] = $value->ID;
//                        $data[$j]["CLASS"] = $value->CLASS_ID;
//
//                        if ($value->SUBJECT_NAME) {
//                            $data[$j]["EVENT_NAME"] = setShowText($value->EVENT_NAME) . " (" . $value->SUBJECT_NAME . ")";
//                        } else {
//                            $data[$j]["EVENT_NAME"] = setShowText($value->EVENT_NAME);
//                        }
//
//                        $date = getShowDate($value->START_DATE);
//                        $date .= " - " . getShowDate($value->END_DATE);
//
//                        $data[$j]["DATE"] = $date;
//
//                        $time = $value->START_HOUR;
//                        $time .= " - " . $value->END_HOUR;
//
//                        $data[$j]["TIME"] = $time;
//
//                        $data[$j]["REMARK"] = setShowText($value->REMARK);
//                        $data[$j]["LOCATION"] = setShowText($value->LOCATION);
//
//                        $data[$j]["START_DATE"] = getShowDate($value->START_DATE);
//                        $data[$j]["END_DATE"] = getShowDate($value->END_DATE);
//                        $data[$j]["START_HOUR"] = $value->START_HOUR;
//                        $data[$j]["END_HOUR"] = $value->END_HOUR;
//
//                        $data[$j]["STATUS_KEY"] = iconStatus($value->STATUS);
//                        $data[$j]["STATUS"] = $value->STATUS;
//                        //////// THORN Visal
//                        $classEvent = $value->GRADE_NAME;
//                        if (!$classEvent) {
//                            $data[$j]["EVENT_TYPE"] = "School Event";
//                        } else {
//                            $data[$j]["EVENT_TYPE"] = $value->GRADE_NAME . " (Class Event)";
//                        }
//                        ////////
//                        if (isset($value->STATUS)) {
//                            $data[$j]["STATUS_KEY"] = iconStatus($value->STATUS);
//                            $data[$j]["STATUS"] = $value->STATUS;
//                        }
//
//                        $j++;
//                    }
//
//                break;
//            default:
//                $result = self::getAllSchooleventsQuery($params);
//
//                $data = array();
//
//                $i = 0;
//                if (isset($data) && $data)
//                    unset($data);
//
//                if ($result)
//                    foreach ($result as $value) {
//
//                        $data[$i]["ID"] = $value->ID;
//                        $data[$i]["CLASS"] = $value->CLASS_ID;
//
//                        if ($value->SUBJECT_NAME) {
//                            $data[$i]["EVENT_NAME"] = setShowText($value->EVENT_NAME) . " (" . $value->SUBJECT_NAME . ")";
//                        } else {
//                            $data[$i]["EVENT_NAME"] = setShowText($value->EVENT_NAME);
//                        }
//
//                        $date = getShowDate($value->START_DATE);
//                        $date .= " - " . getShowDate($value->END_DATE);
//
//                        $data[$i]["DATE"] = $date;
//
//                        $time = $value->START_HOUR;
//                        $time .= " - " . $value->END_HOUR;
//
//                        $data[$i]["TIME"] = $time;
//
//                        $data[$i]["REMARK"] = setShowText($value->REMARK);
//                        $data[$i]["LOCATION"] = setShowText($value->LOCATION);
//
//                        $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
//                        $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
//                        $data[$i]["START_HOUR"] = $value->START_HOUR;
//                        $data[$i]["END_HOUR"] = $value->END_HOUR;
//
//                        $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
//                        $data[$i]["STATUS"] = $value->STATUS;
//                        //////// THORN Visal
//                        $eventType = $value->EVENT_TYPE;
//                        if ($eventType == '1') {
//                            $data[$i]["EVENT_TYPE"] = "School Event";
//                        } else {
//                            $data[$i]["EVENT_TYPE"] = $value->GRADE_NAME . " (Class Event)";
//                        }
//                        ////////
//                        if (isset($value->STATUS)) {
//                            $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
//                            $data[$i]["STATUS"] = $value->STATUS;
//                        }
//
//                        $i++;
//                    }
//                break;
//        }

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
        if ($isJson == true)
            return $this->dataforjson;
        else
            return $data;

        //////////////////////
    }

    //////////////////////////////
    public function createOnlyItem($params) {

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $SAVEDATA['NAME'] = addText($params["name"]);

        switch (UserAuth::getUserType()) {
            case "INSTRUCTOR":
            case "TEACHER":
                $classObject = AcademicDBAccess::findGradeFromId($classId);
                $SAVEDATA['TEACHER_ID'] = Zend_Registry::get('USER')->ID;
                $SAVEDATA['SCHOOL_YEAR'] = $classObject->SCHOOL_YEAR;
                $SAVEDATA['CLASS_ID'] = $classId;
                $SAVEDATA['EVENT_TYPE'] = 2;
                break;
            default:
                $SAVEDATA['SCHOOL_YEAR'] = $schoolyearId;
                $SAVEDATA['EVENT_TYPE'] = 1;
                break;
        }

        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        self::dbAccess()->insert('t_schoolevent', $SAVEDATA);

        return array("success" => true);
    }

    public function jsonSaveEvent($params) {

        $errors = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $classId = isset($params["Classes"]) ? $params["Classes"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $SAVEDATA['NAME'] = addText($params["NAME"]);
        $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
        $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]);

        if (isset($params["START_HOUR"]))
            $SAVEDATA['START_HOUR'] = addText($params["START_HOUR"]);
        if (isset($params["END_HOUR"]))
            $SAVEDATA['END_HOUR'] = addText($params["END_HOUR"]);
        //@THORN Visal    
        if (isset($params["Classes"]))
            $SAVEDATA["CLASS_ID"] = addText($params["Classes"]);
        /////
        if (isset($params["REMARK"]))
            $SAVEDATA['REMARK'] = addText($params["REMARK"]);

        if (isset($params["LOCATION"]))
            $SAVEDATA['LOCATION'] = addText($params["LOCATION"]);

        $SAVEDATA['DAY_OFF_SCHOOL'] = isset($params["DAY_OFF_SCHOOL"]) ? 1 : 0;

        //check date errors
        $CHECK_ERROR_START_DATE = timeDifference(getCurrentDBDate(), setDate2DB($params["START_DATE"]));
        $CHECK_ERROR_END_DATE = timeDifference(getCurrentDBDate(), setDate2DB($params["END_DATE"]));
        $CHECK_ERROR_START_END_DATE = timeDifference(setDate2DB($params["START_DATE"]), setDate2DB($params["END_DATE"]));

        //        if ($CHECK_ERROR_START_DATE) {
        //            $errors["START_DATE"] = CHECK_DATE_PAST;
        //        } elseif ($CHECK_ERROR_END_DATE) {
        //            $errors["END_DATE"] = CHECK_DATE_PAST;
        //        } elseif ($CHECK_ERROR_START_DATE && $CHECK_ERROR_END_DATE) {
        //            $errors["START_DATE"] = CHECK_DATE_PAST;
        //            $errors["END_DATE"] = CHECK_DATE_PAST;
        //        } elseif ($CHECK_ERROR_START_END_DATE) {
        //            $errors["START_DATE"] = ERROR;
        //            $errors["END_DATE"] = ERROR;
        //        } else {
        //            $errors = array();
        //        }

        if ($objectId == "new") {

            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            $SAVEDATA["SCHOOL_YEAR"] = $schoolyearId;
            $SAVEDATA["SUBJECT_ID"] = $subjectId;
            $SAVEDATA["CLASS_ID"] = $classId;
            $SAVEDATA["EVENT_TYPE"] = 2;
            $SAVEDATA['TEACHER_ID'] = Zend_Registry::get('USER')->ID;
            $SAVEDATA['ISTRAINING'] = $target == 'training' ? 1 : 0;

            if (!$errors)
                self::dbAccess()->insert('t_schoolevent', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            if (!$errors)
                self::dbAccess()->update('t_schoolevent', $SAVEDATA, $WHERE);
        }

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array("success" => true, "errors" => $errors, "objectId" => $objectId);
        }
    }

    public function jsonActionClassEvent($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $id = isset($params["id"]) ? addText($params["id"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";

        $data = array();

        if ($objectId == "new" || $id == "") {

            $classObject = AcademicDBAccess::findGradeFromId($classId);
            $SAVEDATA['NAME'] = addText($params["name"]);
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            $SAVEDATA["SCHOOL_YEAR"] = $classObject->SCHOOL_YEAR;
            $SAVEDATA["CLASS_ID"] = $classId;
            $SAVEDATA["EVENT_TYPE"] = 2;
            $SAVEDATA['TEACHER_ID'] = Zend_Registry::get('USER')->ID;
            $SAVEDATA['ISTRAINING'] = $target == 'training' ? 1 : 0;
            self::dbAccess()->insert('t_schoolevent', $SAVEDATA);
        } else {

            switch ($field) {
                case "EVENT_NAME":
                    $SAVEDATA['NAME'] = addText($params["newValue"]);
                    break;
                case "START_DATE":
                    $SAVEDATA['START_DATE'] = substr($params["newValue"], 0, 10);
                    break;
                case "END_DATE":
                    $SAVEDATA['END_DATE'] = substr($params["newValue"], 0, 10);
                    break;
                case "START_HOUR":
                    $SAVEDATA['START_HOUR'] = addText($params["newValue"]);
                    break;
                case "END_HOUR":
                    $SAVEDATA['END_HOUR'] = addText($params["newValue"]);
                    break;
                case "REMARK":
                    $SAVEDATA['REMARK'] = addText($params["newValue"]);
                    break;
            }

            $SAVEDATA['DAY_OFF_SCHOOL'] = isset($params["DAY_OFF_SCHOOL"]) ? 1 : 0;
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $params["id"]);
            self::dbAccess()->update('t_schoolevent', $SAVEDATA, $WHERE);

            $facette = $this->findSchooleventFromId($params["id"]);

            $data["NAME"] = setShowText($facette->NAME);
            $data["START_DATE"] = getShowDate($facette->START_DATE);
            $data["END_DATE"] = getShowDate($facette->END_DATE);
            $data["REMARK"] = setShowText($facette->REMARK);
            $data["LOCATION"] = setShowText($facette->LOCATION);
            $data["START_HOUR"] = $facette->START_HOUR;
            $data["END_HOUR"] = $facette->END_HOUR;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function releaseObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = $this->findSchooleventFromId($objectId);
        $status = $facette->STATUS;

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_schoolevent";
        $SQL .= " SET";

        switch ($status) {
            case 0:
                $newStatus = 1;
                $SQL .= " STATUS=1";
                $SQL .= " ,ENABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,ENABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
            case 1:
                $newStatus = 0;
                $SQL .= " STATUS=0";
                $SQL .= " ,DISABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,DISABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $objectId . "'";

        self::dbAccess()->query($SQL);

        return array("success" => true, "status" => $newStatus);
    }

    public function removeObject($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = "DELETE FROM t_schoolevent";
        $SQL .= " WHERE";
        $SQL .= " ID='" . $removeId . "'";
        self::dbAccess()->query($SQL);

        return array("success" => true);
    }

    public function jsonLoadTestSchedule($params) {

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $facette = $this->findEvent(
                $assignmentId
                , $subjectId
                , $classId
                , $schoolyearId
        );

        $data = array();

        $data["NAME"] = $facette ? $facette->NAME : '---';
        $data["START_DATE"] = $facette ? getShowDate($facette->START_DATE) : "";
        $data["END_DATE"] = $facette ? getShowDate($facette->END_DATE) : "";
        $data["START_HOUR"] = $facette ? $facette->START_HOUR : '00:00';
        $data["END_HOUR"] = $facette ? $facette->END_HOUR : '00:00';
        $data["REMARK"] = $facette ? $facette->REMARK : '---';

        $o = array(
            "success" => true
            , "data" => $data
        );

        return $o;
    }

    protected function checkAssignmentEvent($assignmentId, $subjectId, $classId, $schoolyearId) {

        $SQL = "SELECT COUNT(*) AS C FROM t_schoolevent";
        $SQL .= " WHERE";
        $SQL .= " ASSIGNMENT_ID='" . $assignmentId . "'";
        $SQL .= " AND SUBJECT_ID='" . $subjectId . "'";
        $SQL .= " AND CLASS_ID='" . $classId . "'";
        $SQL .= " AND SCHOOL_YEAR='" . $schoolyearId . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonTestSchedule($params) {

        $assignmentId = str_replace('CAMEMIS_', '', $params["assignmentId"]);
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $DB_GRADE = AcademicDBAccess::getInstance();

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = $DB_GRADE->searchGrade($params);
        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $facette = $this->findEvent($assignmentId, $subjectId, $value->ID, $schoolyearId);

                $data[$i]["CLASS_NAME"] = $value->NAME;
                $data[$i]["ID"] = $value->ID;

                if ($facette) {

                    $date = getShowDate($facette->START_DATE);
                    $date .= " - " . getShowDate($facette->END_DATE);

                    $time = $facette->START_HOUR;
                    $time .= " - " . $facette->END_HOUR;

                    $data[$i]["DATE"] = $date;
                    $data[$i]["TIME"] = $time;

                    $data[$i]["REMARK"] = $facette->REMARK;
                } else {
                    $data[$i]["DATE"] = "---";
                    $data[$i]["TIME"] = "---";
                    $data[$i]["REMARK"] = "---";
                }

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

    public function jsonActionTestSchedule($params) {

        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";

        $assignmentObject = AssignmentDBAccess::findAssignmentFromId($assignmentId);

        $CHECK = $this->checkAssignmentEvent(
                $assignmentId
                , $subjectId
                , $classId
                , $schoolyearId
        );

        $teacherId = $this->findTeacherInSubjectTeacherClass(
                $subjectId
                , $assignmentObject->GRADINGTERM
                , $classId
                , $schoolyearId)
        ;

        $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
        $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]);
        $SAVEDATA['START_HOUR'] = addText($params["START_HOUR"]);
        $SAVEDATA['END_HOUR'] = addText($params["END_HOUR"]);
        $SAVEDATA['REMARK'] = addText($params["REMARK"]);

        if (!$CHECK) {

            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            $SAVEDATA["ASSIGNMENT_ID"] = $assignmentId;
            $SAVEDATA["SCHOOL_YEAR"] = $schoolyearId;
            $SAVEDATA["SUBJECT_ID"] = $subjectId;
            $SAVEDATA["CLASS_ID"] = $classId;
            $SAVEDATA["TEACHER_ID"] = $teacherId;
            $SAVEDATA["NAME"] = $assignmentObject->NAME;
            $SAVEDATA["EVENT_TYPE"] = 3;
            self::dbAccess()->insert('t_schoolevent', $SAVEDATA);
        } else {

            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA["TEACHER_ID"] = $teacherId;
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA['CLASS_ID'] = $classId;

            $WHERE[] = "SCHOOL_YEAR = '" . $schoolyearId . "'";
            $WHERE[] = "ASSIGNMENT_ID = '" . $assignmentId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $subjectId . "'";
            $WHERE[] = "CLASS_ID = '" . $classId . "'";

            self::dbAccess()->update('t_schoolevent', $SAVEDATA, $WHERE);
        }

        return array("success" => true);
    }

    protected function findTeacherInSubjectTeacherClass($subjectId, $term, $classId, $schoolyearId) {

        $SQL = "SELECT * ";
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        $SQL .= " AND ACADEMIC = '" . $classId . "'";
        $SQL .= " AND SCHOOLYEAR = '" . $schoolyearId . "'";
        $SQL .= " AND GRADINGTERM = '" . $term . "'";

        $SQL .= " LIMIT 0,1";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->TEACHER : "";
    }

    protected function findEvent($assignmentId, $subjectId, $classId, $schoolyearId) {

        $values["ASSIGNMENT_ID"] = $assignmentId;
        $values["SUBJECT_ID"] = $subjectId;
        $values["CLASS_ID"] = $classId;
        $values["SCHOOL_YEAR"] = $schoolyearId;

        return $this->getOneWhere('t_schoolevent', $values);
    }

    public static function getEventDaysByCurrentSchoolyear($dayoffschool = false) {

        $schoolyearObject = AcademicDateDBAccess::findSchoolyearByCurrentDate();

        $ranges = array();
        $events = array();

        if ($schoolyearObject) {
            $params['schoolyearId'] = $schoolyearObject->ID;
            $params['status'] = 1;
            if ($dayoffschool) {
                $params['dayoffschool'] = 1;
            }
            $schoolEvents = self::getAllSchooleventsQuery($params);
            $events = array();
            if ($schoolEvents) {
                foreach ($schoolEvents as $value) {
                    $ranges = getDatesBetween2Dates($value->START_DATE, $value->END_DATE);
                    $events = array_merge($events, $ranges);
                }
            }
        }

        return $events;
    }

}

?>