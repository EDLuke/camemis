<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 27.11.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/subject/GradeSubjectDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/room/RoomDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentSearchDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class ExaminationDBAccess {

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

    public static function findExamFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_examination";
        $SQL .= " WHERE";

        if (is_numeric($Id)) {
            $SQL .= " ID = '" . $Id . "'";
        } else {
            $SQL .= " GUID = '" . $Id . "'";
        }

        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findExamParentFromId($childId) {

        $childObject = self::findExamFromId($childId);
        return $childObject ? self::findExamFromId($childObject->PARENT) : false;
    }

    public static function loadExamination($Id) {

        $facette = self::findExamFromId($Id);

        $data = array();

        if ($facette) {

            $assignmentObject = AssignmentDBAccess::findAssignmentFromId($facette->ASSIGNMENT_ID);
            $subjectObject = SubjectDBAccess::findSubjectFromId($facette->SUBJECT_ID);
            $roomObject = RoomDBAccess::findRoomFromId($facette->ROOM_ID);

            $data["NAME"] = setShowText($facette->NAME);
            $data["CHOOSE_ASSIGNMENT_NAME"] = $assignmentObject ? $assignmentObject->NAME : "---";
            $data["CHOOSE_SUBJECT_NAME"] = $subjectObject ? $subjectObject->NAME : "---";
            $data["CHOOSE_ROOM_NAME"] = $roomObject ? $roomObject->NAME : "---";
            $data["CHOOSE_SUBJECT"] = $facette->SUBJECT_ID;
            $data["TERM"] = $facette->TERM;
            $data["CHOOSE_ASSIGNMENT"] = $facette->ASSIGNMENT_ID;
            $data["START_DATE"] = getShowDate($facette->START_DATE);
            $data["START_TIME"] = secondToHour($facette->START_TIME);
            $data["END_TIME"] = secondToHour($facette->END_TIME);
            $data["DESCRIPTION"] = $facette->DESCRIPTION;
            $data["COUNT"] = $facette->COUNT;
            $data["STATUS"] = $facette->STATUS;
            //@veasna
            $data["ENROLL_FULL_SCORE"] = displayNumberFormat($facette->ENROLL_FULL_SCORE);
            $data["ENROLL_EXAM_EXPECTED_SCORE"] = displayNumberFormat($facette->ENROLL_EXAM_EXPECTED_SCORE);
            //

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

    public static function getSQLExamination($params) {

        $status = isset($params["status"]) ? addText($params["status"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $startDate = isset($params["start"]) ? setDate2DB($params["start"]) : "";
        $endDate = isset($params["end"]) ? setDate2DB($params["end"]) : "";

        $SELECTION_A = array(
            "ID AS EXM_ID"
            , "GUID"
            , "COUNT"
            , "START_DATE"
            , "START_TIME"
            , "END_TIME"
            , "OBJECT_TYPE"
            , "SCHOOLYEAR_ID AS SCHOOLYEAR_ID"
            , "STATUS"
        );

        $SELECTION_B = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_C = array(
            "ID AS ROOM_ID"
            , "NAME AS ROOM_NAME"
        );

        $SELECTION_D = array(
            "ID AS GRADE_ID"
            , "NAME AS GRADE_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_examination"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_room'), 'A.ROOM_ID=C.ID', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_grade'), 'A.GRADE_ID=D.ID', $SELECTION_D);

        if ($startDate && $endDate) {
            $SQL->where("('" . $startDate . "' BETWEEN A.START_DATE AND A.END_DATE) OR ('" . $endDate . "' BETWEEN A.START_DATE AND A.END_DATE)");
        }

        if ($type) {
            $SQL->where('A.EXAM_TYPE = ?', $type);
        }

        if ($schoolyearId)
            $SQL->where('A.SCHOOLYEAR_ID = ?', $schoolyearId);

        if ($gradeId)
            $SQL->where('A.GRADE_ID = ?', $gradeId);

        if ($parentId) {
            $SQL->where("A.PARENT = ?",$parentId);
        } else {
            $SQL->where('A.PARENT = 0');
        }

        if ($status) {
            $SQL->where('A.STATUS = 1');
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllExaminations($params) {

        $data = array();
        $node = isset($params["node"]) ? addText($params["node"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";

        $showExam = false;

        if (preg_match("/^SCHOOLYEAR_/", $node)) {
            $showExam = true;
            $node = str_replace('SCHOOLYEAR_', '', $params["node"]);
            $academicObject = AcademicDBAccess::findGradeFromId($node);
            $searchParams["parentId"] = false;
            $searchParams["type"] = $type;
            $searchParams["gradeId"] = $academicObject->GRADE_ID;
            $searchParams["schoolyearId"] = $academicObject->SCHOOL_YEAR;
            $result = self::getSQLExamination($searchParams);
        } elseif (preg_match("/^EXAMINATION_/", $node)) {
            $showExam = true;
            $node = str_replace('EXAMINATION_', '', $params["node"]);
            $searchParams["parentId"] = $node;
            $searchParams["type"] = $type;
            $searchParams["schoolyearId"] = false;
            $result = self::getSQLExamination($searchParams);
        } else {

            $academicObject = AcademicDBAccess::findGradeFromId($node);

            $SELECTION_A = array(
                "ID"
                , "CAMPUS_ID"
                , "GRADE_ID"
                , "NAME"
                , "SCHOOL_YEAR"
                , "OBJECT_TYPE"
                , "STATUS"
            );

            $SELECTION_B = array(
                "NAME AS SCHOOLYEAR_NAME"
            );

            $SQL = self::dbAccess()->select();
            $SQL->distinct();
            $SQL->from(array('A' => "t_grade"), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_academicdate'), 'A.SCHOOL_YEAR=B.ID', $SELECTION_B);
            $SQL->where('A.PARENT = ?', $node);

            if ($type == 5) {
                if ($academicObject) {
                    if ($academicObject->OBJECT_TYPE == 'CAMPUS') {
                        $SQL->where('A.END_OF_GRADE = 1');
                    }
                }
            }

            $SQL->where("A.OBJECT_TYPE IN ('CAMPUS', 'GRADE', 'SCHOOLYEAR')");

            $SQL->order("A.SORTKEY ASC");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
        }

        $schoolyearObject = AcademicDateDBAccess::getInstance();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                if (!$showExam) {
                    $isCurrentYear = $schoolyearObject->isCurrentSchoolyear($value->SCHOOL_YEAR);

                    $data[$i]['objectType'] = $value->OBJECT_TYPE;
                    $data[$i]['schoolyearId'] = $value->SCHOOL_YEAR;

                    switch ($value->OBJECT_TYPE) {

                        case "CAMPUS":
                            $data[$i]['id'] = $value->ID;
                            $data[$i]['text'] = setShowText($value->NAME);
                            $data[$i]['cls'] = "nodeCampus";
                            $data[$i]['iconCls'] = "icon-bricks";
                            $data[$i]['bulletinCampusId'] = $value->CAMPUS_ID;
                            break;
                        case "GRADE":
                            $data[$i]['id'] = $value->ID;
                            $data[$i]['text'] = setShowText($value->NAME);
                            $data[$i]['cls'] = "nodeGrade";
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            $data[$i]['campusId'] = $value->CAMPUS_ID;
                            break;
                        case "SCHOOLYEAR":

                            $data[$i]['id'] = "SCHOOLYEAR_" . $value->ID;

                            $gradeObject = AcademicDBAccess::findGradeFromId($value->GRADE_ID);

                            $data[$i]['text'] = setShowText($value->NAME);

                            if ($gradeObject) {
                                $data[$i]['title'] = setShowText($gradeObject->NAME) . " (" . setShowText($value->NAME) . ") ";
                            } else {
                                $data[$i]['title'] = setShowText($value->NAME);
                            }

                            $data[$i]['academicId'] = $value->ID;
                            $data[$i]['campusId'] = $value->CAMPUS_ID;
                            $data[$i]['gradeId'] = $value->GRADE_ID;
                            $data[$i]['schoolyearId'] = $value->SCHOOL_YEAR;

                            if ($isCurrentYear) {
                                $data[$i]['cls'] = "nodeTextBoldBlue";
                            } else {
                                $data[$i]['cls'] = "nodeTextRedBold";
                            }

                            if ($value->STATUS == 1) {
                                $data[$i]['iconCls'] = "icon-date";
                            } else {
                                $data[$i]['iconCls'] = "icon-date_edit";
                            }

                            $data[$i]['leaf'] = false;
                            break;
                    }
                } else {

                    $data[$i]['id'] = "EXAMINATION_" . $value->EXM_ID;
                    $data[$i]['objectId'] = $value->GUID;

                    switch ($value->OBJECT_TYPE) {
                        case "FOLDER":
                            $data[$i]['text'] = $value->START_DATE . " " . setShowText($value->SUBJECT_NAME) . " (" . secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME) . ")";
                            $data[$i]['subjectId'] = $value->SUBJECT_ID;
                            $data[$i]['parentId'] = $value->EXM_ID;
                            $data[$i]['schoolyear_Id'] = $value->SCHOOLYEAR_ID;

                            $data[$i]['startDate'] = $value->START_DATE;
                            $data[$i]['startTime'] = $value->START_TIME;
                            $data[$i]['endTime'] = $value->END_TIME;

                            $data[$i]['iconCls'] = "icon-flag_red";
                            $data[$i]['leaf'] = false;
                            $data[$i]['cls'] = "nodeTextBlue";
                            break;
                        case "ITEM":
                            $data[$i]['text'] = ROOM . ": " . setShowText($value->ROOM_NAME) . " (" . $value->COUNT . ")";
                            $data[$i]['leaf'] = true;
                            $data[$i]['roomId'] = $value->ROOM_ID;
                            $data[$i]['iconCls'] = "icon-flag_blue";
                            $data[$i]['cls'] = "nodeTextBlue";
                            break;
                    }
                }
                $i++;
            }
        }
        return $data;
    }

    /*
      public function getExaminationByTreeType($params) {


      print_r($params);
      $SQL = "";
      $SQL .= " SELECT
      A.ID as ID,
      A.START_DATE as START_DATE,
      A.START_TIME as START_TIME,
      A.END_TIME as END_TIME,
      B.NAME as SUBJECT_NAME,
      C.NAME as GRADE_NAME,
      D.NAME as ROOM_NAME";

      $SQL .= " FROM t_examination AS A";
      $SQL .= " LEFT JOIN t_subject AS B ON A.SUBJECT_ID = B.ID";
      $SQL .= " LEFT JOIN t_grade AS C ON A.GRADE_ID = C.ID";
      $SQL .= " LEFT JOIN t_room AS D ON A.ROOM_ID = D.ID";
      $SQL .= " WHERE 1=1";
      $SQL .= " GROUP BY A.SUBJECT_ID";
      $result = self::dbAccess()->fetchAll($SQL);

      return $result;
      }
     */

    public static function checkUsedExamSubject($gradeId, $schoolyearId, $testType) {

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_examination";
        $SQL .= " WHERE GRADE_ID = '" . $gradeId . "'";
        $SQL .= " AND SCHOOLYEAR_ID = '" . $schoolyearId . "'";
        $SQL .= " AND EXAM_TYPE = '" . $testType . "'";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                if ($value->SUBJECT_ID) {
                    $data[$value->SUBJECT_ID] = $value->SUBJECT_ID;
                }
            }
        }

        return $data;
    }

    public static function checkExamSubjectBetweenDateTime($parentId) {

        $facette = self::findExamFromId($parentId);

        if ($facette) {
            $SQLFirst = "";
            $SQLFirst .= " SELECT *";
            $SQLFirst .= " FROM t_examination";
            $SQLFirst .= " WHERE 1=1";
            $SQLFirst .= " AND ('" . $facette->START_TIME . "' BETWEEN START_TIME AND END_TIME)";
            $SQLFirst .= " OR ('" . $facette->END_TIME . "' BETWEEN START_TIME AND END_TIME)";
            $resultFirst = self::dbAccess()->fetchAll($SQLFirst);

            $SQLSecond = "";
            $SQLSecond .= " SELECT *";
            $SQLSecond .= " FROM t_examination";
            $SQLSecond .= " WHERE 1=1";
            $SQLSecond .= " AND (START_TIME BETWEEN '" . $facette->START_TIME . "' AND '" . $facette->END_TIME . "')";
            $SQLSecond .= " OR (END_TIME BETWEEN '" . $facette->START_TIME . "' AND '" . $facette->END_TIME . "')";
            $resultSecond = self::dbAccess()->fetchAll($SQLSecond);

            $FIRST_DATA = array();
            if ($resultFirst) {
                foreach ($resultFirst as $key => $value) {
                    if ($value->TREE_TYPE == 'FOLDER') {
                        if ($value->SCHOOLYEAR_ID == $facette->SCHOOLYEAR_ID) {
                            if ($value->START_DATE == $facette->START_DATE) {
                                if ($value->SUBJECT_ID)
                                    $FIRST_DATA[$value->SUBJECT_ID] = $value->SUBJECT_ID;
                            }
                        }
                    }
                }
            }

            $SECOND_DATA = array();
            if ($resultSecond) {
                foreach ($resultSecond as $key => $value) {
                    if ($value->TREE_TYPE == 'FOLDER') {
                        if ($value->SCHOOLYEAR_ID == $facette->SCHOOLYEAR_ID) {
                            if ($value->START_DATE == $facette->START_DATE) {
                                if ($value->SUBJECT_ID)
                                    $SECOND_DATA[$value->SUBJECT_ID] = $value->SUBJECT_ID;
                            }
                        }
                    }
                }
            }

            $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;
        }else {
            $CHECK_DATA = array();
        }

        return $CHECK_DATA;
    }

    public static function jsonActionSaveExamination($params) {

        $error = false;
        $errors = array();

        $assignmentId = isset($params["CHOOSE_ASSIGNMENT"]) ? addText($params["CHOOSE_ASSIGNMENT"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $subjectId = isset($params["CHOOSE_SUBJECT"]) ? addText($params["CHOOSE_SUBJECT"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $startTime = isset($params["START_TIME"]) ? timeStrToSecond($params["START_TIME"]) : "";
        $endTime = isset($params["END_TIME"]) ? timeStrToSecond($params["END_TIME"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : ""; //@veasna

        $facette = self::findExamFromId($objectId);

        $CHECK_DATE = checkFuturDate($startDate);

        $SAVEDATA['START_DATE'] = $startDate;
        $SAVEDATA['START_TIME'] = $startTime;
        $SAVEDATA['END_TIME'] = $endTime;

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);
        //@veasna
        if (isset($params["ENROLL_FULL_SCORE"]))
            $SAVEDATA['ENROLL_FULL_SCORE'] = $params["ENROLL_FULL_SCORE"];

        if (isset($params["ENROLL_EXAM_EXPECTED_SCORE"]))
            $SAVEDATA['ENROLL_EXAM_EXPECTED_SCORE'] = $params["ENROLL_EXAM_EXPECTED_SCORE"];
        //

        if (isset($params["COUNT"]))
            $SAVEDATA['COUNT'] = addText($params["COUNT"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["TERM"]))
            $SAVEDATA['TERM'] = addText($params["TERM"]);

        if ($objectId == "new") {

            $SAVEDATA['GUID'] = generateGuid();
            $SAVEDATA['EXAM_TYPE'] = $type;
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";
            $SAVEDATA['SCHOOLYEAR_ID'] = $schoolyearId;
            $SAVEDATA['GRADE_ID'] = $gradeId;
            $SAVEDATA['SUBJECT_ID'] = $subjectId;
            $SAVEDATA['ASSIGNMENT_ID'] = $assignmentId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            if (!$error) {
                self::dbAccess()->insert('t_examination', $SAVEDATA);
                $newId = self::dbAccess()->lastInsertId();
                $facette = self::findExamFromId($newId);
                $objectId = $facette->GUID;
                if ($academicId) {
                    StudentExaminationDBAccess::jsonActionAllStudentsTmpToExamination($objectId, $academicId);
                }
            }
        } else {
            $WHERE[] = "GUID = '" . $objectId . "'";
            self::dbAccess()->update('t_examination', $SAVEDATA, $WHERE);
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
            );
        } else {
            return array(
                "success" => true
                , "errors" => $errors
                , "objectId" => $objectId
            );
        }
    }

    public static function jsonActionReleaseExam($objectId) {

        $facette = self::findExamFromId($objectId);
        $status = $facette->STATUS;

        $data = array();
        switch ($status) {
            case 0:
                $newStatus = 1;
                $data['STATUS']      = 1;
                $data['ENABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                $data['ENABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                break;
            case 1:
                $newStatus = 0;
                $data['STATUS']       =0;
                $data['DISABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                $data['DISABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                break;
        }
        if ($facette)
            self::dbAccess()->update("t_examination", $data, "GUID='". $objectId ."'");
        return array("success" => true, "status" => $newStatus);
    }

    public static function jsonActionSaveRoom($params) {

        $error = null;
        $errors = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $roomId = isset($params["CHOOSE_ROOM"]) ? $params["CHOOSE_ROOM"] : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";

        $facette = self::findExamFromId($parentId);

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["COUNT"]))
            $SAVEDATA['COUNT'] = addText($params["COUNT"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if ($roomId)
            $SAVEDATA['ROOM_ID'] = $roomId;

        if ($objectId == "new") {
            if ($facette) {
                $SAVEDATA['GUID'] = generateGuid();
                $SAVEDATA['PARENT'] = $facette->ID;
                $SAVEDATA['EXAM_TYPE'] = $facette->EXAM_TYPE;
                $SAVEDATA['START_DATE'] = $facette->START_DATE;
                $SAVEDATA['START_TIME'] = $facette->START_TIME;
                $SAVEDATA['END_TIME'] = $facette->END_TIME;
                $SAVEDATA['TERM'] = $facette->TERM;
                $SAVEDATA['OBJECT_TYPE'] = "ITEM";
                $SAVEDATA['SCHOOLYEAR_ID'] = $facette->SCHOOLYEAR_ID;
                $SAVEDATA['GRADE_ID'] = $facette->GRADE_ID;
                $SAVEDATA['SUBJECT_ID'] = $facette->SUBJECT_ID;
                $SAVEDATA['ASSIGNMENT_ID'] = $facette->ASSIGNMENT_ID;
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                if (!$error) {
                    self::dbAccess()->insert('t_examination', $SAVEDATA);
                    $newId = self::dbAccess()->lastInsertId();
                    $facette = self::findExamFromId($newId);
                    $objectId = $facette->GUID;
                }
            }
        } else {
            $WHERE[] = "GUID = '" . $objectId . "'";
            self::dbAccess()->update('t_examination', $SAVEDATA, $WHERE);
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
            );
        } else {
            return array(
                "success" => true
                , "errors" => $errors
                , "objectId" => $objectId
            );
        }
    }

    public static function jsonAllExamSubjects($params) {

        $DB_GRADE_SUBJECT = GradeSubjectDBAccess::getInstance();
        $result = $DB_GRADE_SUBJECT->sqlAssignedSubjectsByGrade($params);

        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $CHECK_SUBJECT = self::checkUsedExamSubject($gradeId, $schoolyearId, 1);

        $data = array();
        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                if (!in_array($value->SUBJECT_ID, $CHECK_SUBJECT)) {
                    $data[$i]["ID"] = $value->SUBJECT_ID;
                    $data[$i]["SUBJECT_NAME"] = $value->NAME;
                    $i++;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function checkUsedRoom($schoolyear_Id, $date, $startTime, $endTime) {

        $SQL_FIRST = "";
        $SQL_FIRST .= " SELECT *";
        $SQL_FIRST .= " FROM t_examination";
        $SQL_FIRST .= " WHERE ROOM_ID <> 0";
        $SQL_FIRST .= " AND START_DATE = '" . $date . "'";
        $SQL_FIRST .= " AND SCHOOLYEAR_ID = '" . $schoolyear_Id . "'";
        $SQL_FIRST .= " AND (('" . $startTime . "' BETWEEN START_TIME AND END_TIME)";
        $SQL_FIRST .= " OR ('" . $endTime . "' BETWEEN START_TIME AND END_TIME))";

        $FIRST_DATA = array();
        $result_first = self::dbAccess()->fetchAll($SQL_FIRST);
        foreach ($result_first as $value) {
            if ($value->ROOM_ID) {
                $FIRST_DATA[$value->ROOM_ID] = $value->ROOM_ID;
            }
        }

        $SQL_SECOND = "";
        $SQL_SECOND .= " SELECT *";
        $SQL_SECOND .= " FROM t_examination";
        $SQL_SECOND .= " WHERE ROOM_ID <> 0";
        $SQL_SECOND .= " AND START_DATE = '" . $date . "'";
        $SQL_SECOND .= " AND SCHOOLYEAR_ID = '" . $schoolyear_Id . "'";
        $SQL_SECOND .= " AND ((START_TIME BETWEEN '" . $startTime . "' AND '" . $endTime . "')";
        $SQL_SECOND .= " OR (END_TIME BETWEEN '" . $startTime . "' AND '" . $endTime . "'))";

        $SECOND_DATA = array();
        $result_second = self::dbAccess()->fetchAll($SQL_SECOND);
        foreach ($result_second as $v) {
            if ($v->ROOM_ID) {
                $SECOND_DATA[$v->ROOM_ID] = $v->ROOM_ID;
            }
        }

        $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;

        return $CHECK_DATA;
    }

    public static function jsonAllExamRooms($params) {

        $schoolyear_Id = isset($params["schoolyear_Id"]) ? addText($params["schoolyear_Id"]) : "";
        $startDate = isset($params["startDate"]) ? addText($params["startDate"]) : "";
        $startTime = isset($params["startTime"]) ? addText($params["startTime"]) : "";
        $endTime = isset($params["endTime"]) ? addText($params["endTime"]) : "";

        $data = array();

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from("t_room", array('ID AS ROOM_ID', 'NAME AS ROOM_NAME'));
        $result = self::dbAccess()->fetchAll($SQL);

        $CHECK_ROOM = self::checkUsedRoom($schoolyear_Id, $startDate, $startTime, $endTime);

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                if (!in_array($value->ROOM_ID, $CHECK_ROOM)) {
                    $data[$i]["ID"] = $value->ROOM_ID;
                    $data[$i]["ROOM_NAME"] = $value->ROOM_NAME;
                    $i++;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function removeExamination($Id) {

        $facette = self::findExamFromId($Id);

        if ($facette) {
            switch ($facette->OBJECT_TYPE) {
                case "FOLDER":
                    self::dbAccess()->delete('t_examination', array("PARENT='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_examination', array("EXAM_ID='" . $Id . "'"));
                    self::dbAccess()->delete('t_teacher_examination', array("EXAM_ID='" . $Id . "'")); //@veasna
                    self::dbAccess()->delete('t_examination', array("GUID ='" . $Id . "'"));
                    break;
                case "ITEM":
                    $where = array();
                    $where[] = "EXAM_ID='". $Id ."'";
                    $where[] = "ROOM_ID='". $facette->ROOM_ID ."'";
                    self::dbAccess()->update("t_student_examination", array('ROOM_ID' => '0'), $where);
                    self::dbAccess()->delete('t_examination', array("GUID ='" . $Id . "'"));
                    break;
            }
        }

        return array("success" => true);
    }

    public static function removeRoom($Id) {

        $facette = self::findExamFromId($Id);
        if ($facette) {
            $parentFacette = self::findExamFromId($facette->PARENT);
            $where = array();
            $where[] = "EXAM_ID='". $parentFacette->GUID ."'";
            $where[] = "ROOM_ID='". $facette->ROOM_ID ."'";
            self::dbAccess()->update("t_student_examination", array('ROOM_ID' => '0'), $where);

            $where = array();
            $where[] = "EXAM_ID='". $parentFacette->GUID ."'";
            $where[] = "ROOM_ID='". $facette->ROOM_ID ."'";
            self::dbAccess()->update("t_teacher_examination", array('ROOM_ID' => '0'), $where);

            self::dbAccess()->delete('t_examination', array("GUID ='" . $Id . "'"));
        }

        return array("success" => true);
    }

    public static function getStudentAllExam($params) {

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $SQL = "";
        $SQL .= " SELECT 
                A.ID as ID,
                A.EXAM_CODE as EXAM_CODE,
                B.START_DATE as START_DATE,
                B.START_TIME as START_TIME,
                B.END_TIME as END_TIME,
                C.NAME as SUBJECT_NAME,
                D.NAME as ROOM_NAME";
        $SQL .= " FROM t_student_examination AS A";
        $SQL .= " LEFT JOIN t_examination AS B ON B.GUID = A.EXAM_ID";
        $SQL .= " LEFT JOIN t_subject AS C ON B.SUBJECT_ID = C.ID";
        $SQL .= " LEFT JOIN t_room AS D ON B.ROOM_ID = D.ID";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.STATUS = '1'";
        $SQL .= " AND A.STUDENT_ID = '" . $studentId . "'";
        if ($schoolyearId)
            $SQL .= " AND B.SCHOOL_YEAR = '" . $schoolyearId . "'";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findAcademicById($academicId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', array('*'));
        $SQL->where("ID = ?",$academicId);
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function loadMainExam($Id) {

        $result = self::findAcademicById($Id);
        $data = array();
        if ($result) {

            $data['ENROLL_EXAM_NAME'] = $result->ENROLL_EXAM_NAME;
            $data['ENROLL_FULL_SCORE'] = displayNumberFormat($result->ENROLL_FULL_SCORE);
            $data['ENROLL_EXAM_EXPECTED_SCORE'] = displayNumberFormat($result->ENROLL_EXAM_EXPECTED_SCORE);
            $data['ENROLL_TOTAL_STUDENTS'] = displayNumberFormat($result->ENROLL_TOTAL_STUDENTS);
            $data['ENROLL_STUDENTS'] = $result->ENROLL_STUDENTS;
            $data['ENROLL_SCORE_EDITABLE'] = ($result->ENROLL_SCORE_EDITABLE == 1) ? 'on' : ''; ///check 
            $data['ENROLL_EXAM_DES'] = $result->ENROLL_EXAM_DES;
        }

        return array("success" => true, "data" => $data);
    }

    public static function jsonActionSaveMainExam($params) {

        $academicId = isset($params['academicId']) ? addText($params["academicId"]) : '';
        $ENROLL_EXAM_NAME = isset($params['ENROLL_EXAM_NAME']) ? addText($params["ENROLL_EXAM_NAME"]) : '';
        $ENROLL_FULL_SCORE = isset($params['ENROLL_FULL_SCORE']) ? addText($params["ENROLL_FULL_SCORE"]) : '0';
        $ENROLL_EXAM_EXPECTED_SCORE = isset($params['ENROLL_EXAM_EXPECTED_SCORE']) ? addText($params["ENROLL_EXAM_EXPECTED_SCORE"]) : '0';
        $ENROLL_TOTAL_STUDENTS = isset($params['ENROLL_TOTAL_STUDENTS']) ? addText($params["ENROLL_TOTAL_STUDENTS"]) : '0';
        $ENROLL_STUDENTS = isset($params['ENROLL_STUDENTS']) ? addText($params["ENROLL_STUDENTS"]) : '0';
        $ENROLL_SCORE_EDITABLE = isset($params['ENROLL_SCORE_EDITABLE']) ? addText($params["ENROLL_SCORE_EDITABLE"]) : '';
        $ENROLL_EXAM_DES = isset($params['ENROLL_EXAM_DES']) ? addText($params["ENROLL_EXAM_DES"]) : '';
        $objectId = isset($params['HIDDEN_OBJECT_ID']) ? addText($params["HIDDEN_OBJECT_ID"]) : '';
        $editAble = 0;

        if ($ENROLL_SCORE_EDITABLE == 'on') {

            $editAble = 1;
        } else {

            $editAble = 0;
        }

        $SAVEDATA = array();

        if ($academicId) {
            $SAVEDATA['ENROLL_EXAM_NAME'] = $ENROLL_EXAM_NAME;
            $SAVEDATA['ENROLL_FULL_SCORE'] = $ENROLL_FULL_SCORE;
            $SAVEDATA['ENROLL_EXAM_EXPECTED_SCORE'] = $ENROLL_EXAM_EXPECTED_SCORE;
            $SAVEDATA['ENROLL_TOTAL_STUDENTS'] = $ENROLL_TOTAL_STUDENTS;
            $SAVEDATA['ENROLL_STUDENTS'] = $ENROLL_STUDENTS;
            $SAVEDATA['ENROLL_SCORE_EDITABLE'] = $editAble;
            $SAVEDATA['ENROLL_EXAM_DES'] = $ENROLL_EXAM_DES;

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $academicId);
            self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
        }
        return array("success" => true, 'Id' => $academicId);
    }

}

?>