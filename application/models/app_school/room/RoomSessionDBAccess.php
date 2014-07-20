<?php

///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 30.10.2013
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDBAccess.php';
require_once 'models/app_school/schedule/TeacherScheduleDBAccess.php';
require_once setUserLoacalization();

class RoomSessionDBAccess extends ScheduleDBAccess {

    public $dataforjson = null;
    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function countRoomExtraSession($roomId = false, $startDate, $endDate) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_teaching_session'), array("C" => "COUNT(*)"));
        if ($roomId)
            $SQL->where("ROOM_ID = ?", $roomId);

        $SQL->where("TEACHING_DATE BETWEEN  '" . setDate2DB($startDate) . "'  AND '" . setDate2DB($endDate) . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getRoomExtraSession($roomId = false, $day, $search = false) {

        $SELECT_DATA = array(
            "A.ID AS SCHEDULE_ID"
            , "A.GUID AS GUID"
            , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
            , "A.ROOM_ID AS ROOM_ID"
            , "A.ACADEMIC_ID AS ACADEMIC_ID"
            , "A.TRAINING_ID AS TRAINING_ID"
            , "A.SHORTDAY AS SHORTDAY"
            , "A.START_TIME AS START_TIME"
            , "A.END_TIME AS END_TIME"
            , "A.EVENT AS EVENT"
            , "A.TERM AS TERM"
            , "B.ID AS SUBJECT_ID"
            , "B.NAME AS SUBJECT_NAME"
            , "B.COLOR AS SUBJECT_COLOR"
            , "CONCAT(C.NAME,'(',C.SHORT,')') AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_teaching_session'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_room'), 'A.ROOM_ID=C.ID', array());
        $SQL->where("TEACHING_DATE='" . $day . "'");
        $SQL->where("A.ROOM_ID <> 0");

        if ($roomId)
            $SQL->where("ROOM_ID = ?", $roomId);

        if ($search) {
            $SEARCH = "";
            $SEARCH .= " ((C.NAME LIKE '" . $search . "%')";
            $SEARCH .= " OR (C.CODE LIKE '" . $search . "%')";
            $SEARCH .= " OR (C.SHORT LIKE '" . $search . "%')";
            $SEARCH .= " OR (C.ROOM_SIZE LIKE '" . $search . "%')";
            $SEARCH .= " OR (C.BUILDING LIKE '" . $search . "%')";
            $SEARCH .= " OR (C.MAX_COUNT LIKE '" . $search . "%')";
            $SEARCH .= " OR (B.NAME LIKE '" . $search . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getRoomSession($roomId = false, $shortday, $term, $type, $schoolyearId, $search = false) {

        $SELECT_DATA = array(
            "A.ID AS SCHEDULE_ID"
            , "A.GUID AS GUID"
            , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
            , "A.STATUS AS SCHEDULE_STATUS"
            , "A.ROOM_ID AS ROOM_ID"
            , "A.ACADEMIC_ID AS ACADEMIC_ID"
            , "A.TRAINING_ID AS TRAINING_ID"
            , "A.SHORTDAY AS SHORTDAY"
            , "A.CODE AS SCHEDULE_CODE"
            , "A.START_TIME AS START_TIME"
            , "A.END_TIME AS END_TIME"
            , "A.EVENT AS EVENT"
            , "A.TERM AS TERM"
            , "B.ID AS SUBJECT_ID"
            , "B.NAME AS SUBJECT_NAME"
            , "B.COLOR AS SUBJECT_COLOR"
            , "B.COLOR AS SUBJECT_COLOR"
            , "CONCAT(D.NAME,'(',D.SHORT,')') AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_schedule'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_staff'), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => 't_room'), 'A.ROOM_ID=D.ID', array());
        $SQL->where("A.ROOM_ID <> 0");

        if ($shortday)
            $SQL->where("A.SHORTDAY = ?", $shortday);

        if ($roomId)
            $SQL->where("A.ROOM_ID = '" . $roomId . "'");

        if ($type == "GENERAL") {
            $SQL->where("A.ACADEMIC_ID<>0");
            if ($term) {
                switch ($term) {
                    case "FIRST_SEMESTER": $SQL->where("A.TERM = 'FIRST_SEMESTER'");
                        break;
                    case "SECOND_SEMESTER": $SQL->where("A.TERM = 'SECOND_SEMESTER'");
                        break;
                }
            }
            if ($schoolyearId)
                $SQL->where("A.SCHOOLYEAR_ID = '" . $schoolyearId . "'");
        }

        if ($type == "TRAINING") {
            $SQL->where("A.TRAINING_ID<>0");
        }

        if ($search) {
            $SEARCH = "";
            $SEARCH .= " ((D.NAME LIKE '" . $search . "%')";
            $SEARCH .= " OR (D.CODE LIKE '" . $search . "%')";
            $SEARCH .= " OR (D.SHORT LIKE '" . $search . "%')";
            $SEARCH .= " OR (D.ROOM_SIZE LIKE '" . $search . "%')";
            $SEARCH .= " OR (D.BUILDING LIKE '" . $search . "%')";
            $SEARCH .= " OR (D.MAX_COUNT LIKE '" . $search . "%')";
            $SEARCH .= " OR (B.NAME LIKE '" . $search . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        $SQL->order('START_TIME');
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonListRoomSession($params, $isJson = true) {

        $searchCount = isset($params["searchCount"]) ? $params["searchCount"] : "";
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";
        $startDate = isset($params["startDate"]) ? substr($params["startDate"], 0, 10) : "";
        $endDate = isset($params["endDate"]) ? substr($params["endDate"], 0, 10) : "";
        $academicType = isset($params["type"]) ? addText($params["type"]) : "GENERAL";
        $search = isset($params["query"]) ? addText($params["query"]) : "";

        $days = getDatesBetween2Dates($startDate, $endDate);
        $data = array();
        $i = self::countRoomExtraSession(false, $startDate, $endDate);
        $j = 0;

        $term = "";
        $result = self::sqlRoomSession($params);

        $COUNT_SESSION_DATA = Array();

        if ($result) {
            $roomIndex = 0;
            foreach ($result as $value) {
                $countSession = 0;
                $countExtraSession = 0;
                if ($days) {

                    foreach ($days as $day) {

                        $schoolyearId = teacherScheduleDBAccess::getSchoolyearIdByDate($day);
                        if ($schoolyearId)
                            $term = teacherScheduleDBAccess::getCurrentTermNew($day, $schoolyearId);

                        $shortday = getWEEKDAY($day);
                        ////////////////////////////////////////////////////////////////////
                        //EXTRA TEACHING SESSION
                        ///////////////////////////////////////////////////////////////////
                        $EXTRA_ROOM_OBJECT = self::getRoomExtraSession($value->ROOM_ID, $day, $search);

                        if ($EXTRA_ROOM_OBJECT) {
                            foreach ($EXTRA_ROOM_OBJECT as $extraTeachingSession) {
                                $data[$j]["ID"] = $extraTeachingSession->GUID;
                                $data[$j]["ROOM"] = setShowText($extraTeachingSession->ROOM);
                                $data[$j]["SESSION_DATE"] = getShowDate($day);
                                $data[$j]["COLOR"] = $extraTeachingSession->SUBJECT_COLOR;
                                $data[$j]["COLOR_FONT"] = getFontColor($extraTeachingSession->SUBJECT_COLOR);

                                $subjectObject = SubjectDBAccess::findSubjectFromId($extraTeachingSession->SUBJECT_ID);

                                if ($subjectObject)
                                    $data[$j]["EVENT"] = setShowText($subjectObject->NAME);

                                $academicObject = AcademicDBAccess::findGradeFromId($extraTeachingSession->ACADEMIC_ID);

                                if ($academicObject) {
                                    $data[$j]["CLASS"] = $academicObject->NAME;
                                    $data[$j]["TIME"] = secondToHour($extraTeachingSession->START_TIME) . " - " . secondToHour($extraTeachingSession->END_TIME);
                                    $data[$j]["TARGET"] = "GENERAL";
                                    $data[$j]["CLASS_NAME"] = $academicObject->NAME;
                                    $data[$j]["TEACHING_STATUS"] = EXTRA_TEACHING_SESSION;
                                }

                                $countExtraSession++;

                                $j++;
                            }
                        }

                        ////////////////////////////////////////////////////////
                        //ROOM TEACHING SESSION
                        ////////////////////////////////////////////////////////
                        $facette = self::getRoomSession($value->ROOM_ID, $shortday, $term, $academicType, $schoolyearId, $search);
                        if ($facette) {
                            foreach ($facette as $facette) {
                                $data[$i]["ID"] = $facette->GUID;
                                $data[$i]["CHOOSE_DATE"] = $day;

                                if ($facette->ACADEMIC_ID || $facette->TRAINING_ID) {

                                    $data[$i]["ACADEMIC_ID"] = $facette->ACADEMIC_ID;
                                    $data[$i]["SUBJECT_ID"] = $facette->SUBJECT_ID;
                                    $data[$i]["SHORTDAY"] = $facette->SHORTDAY;

                                    $data[$i]["SESSION_STATUS"] = "Absence, Extra Session...";
                                    $data[$i]["SESSION_DATE"] = getShowDate($day);
                                    $data[$i]["CODE"] = $facette->SCHEDULE_CODE;
                                    $data[$i]["DAY"] = getShortdayName($facette->SHORTDAY);
                                    $data[$i]["DAY"] .= "<br>";
                                    $data[$i]["DAY"] .= getShowDate($day);
                                    $data[$i]["SCHEDULE_TYPE"] = $facette->SCHEDULE_TYPE;

                                    switch ($facette->SCHEDULE_TYPE) {
                                        case 1:
                                            $data[$i]["EVENT"] = setShowText($facette->SUBJECT_NAME);
                                            $data[$i]["COLOR"] = $facette->SUBJECT_COLOR;
                                            $data[$i]["COLOR_FONT"] = getFontColor($facette->SUBJECT_COLOR);
                                            $data[$i]["EVENT_NAME"] = setShowText($facette->SUBJECT_NAME);
                                            break;
                                        case 2:
                                            $data[$i]["EVENT"] = setShowText($facette->EVENT);
                                            $data[$i]["COLOR"] = "#CCCCCC";
                                            $data[$i]["COLOR_FONT"] = getFontColor($facette->SUBJECT_COLOR);
                                            $data[$i]["EVENT_NAME"] = setShowText($facette->EVENT);
                                            break;
                                    }

                                    $START_TIME = secondToHour($facette->START_TIME);
                                    $END_TIME = secondToHour($facette->END_TIME);
                                    $data[$i]["TIME"] = $START_TIME . " - " . $END_TIME;

                                    $data[$i]["START_TIME"] = $START_TIME;
                                    $data[$i]["END_TIME"] = $END_TIME;
                                    $data[$i]["ROOM"] = setShowText($facette->ROOM);


                                    if ($facette->ACADEMIC_ID) {

                                        $academicObject = AcademicDBAccess::findGradeFromId($facette->ACADEMIC_ID);
                                        $data[$i]["TERM"] = displaySchoolTerm($facette->TERM);

                                        if ($academicObject) {
                                            $data[$i]["CLASS"] = $academicObject->NAME;
                                            $data[$i]["TIME"] = secondToHour($facette->START_TIME) . " - " . secondToHour($facette->END_TIME);
                                            $data[$i]["TARGET"] = "GENERAL";
                                            $data[$i]["CLASS_NAME"] = $academicObject->NAME;
                                        }
                                    } elseif ($facette->TRAINING_ID) {

                                        $data[$i]["TARGET"] = "TRAINING";
                                        $TRAINING_OBEJECT = TrainingDBAccess::findTrainingFromId($facette->TRAINING_ID);

                                        if ($TRAINING_OBEJECT) {
                                            $data[$i]["CLASS"] = $TRAINING_OBEJECT->NAME . " (" . TRAINING_PROGRAMS . ")";
                                            $data[$i]["CLASS"] .= "<br>";
                                            $data[$i]["CLASS"] .= secondToHour($facette->START_TIME) . " - " . secondToHour($facette->END_TIME);
                                            $data[$i]["CLASS_NAME"] = $TRAINING_OBEJECT->NAME . " (" . TRAINING_PROGRAMS . ")";
                                        }
                                    }

                                    $countSession++;
                                    $i++;
                                }
                            }
                        }
                    }
                }

                $COUNT_SESSION_DATA[$roomIndex]["COUNT"] = $countSession + $countExtraSession;
                $COUNT_SESSION_DATA[$roomIndex]["NAME"] = setShowText($value->ROOM_NAME);

                $roomIndex++;
            }
        }

        if ($searchCount) {
            return $COUNT_SESSION_DATA;
        } else {
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
                return $a;
            }
        }
    }

    //VIK: 01.01.2014
    public static function sqlRoomSession($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";
        $schoolyearId = teacherScheduleDBAccess::getSchoolyearIdByDate($startDate);

        $SELECTION_A = array(
            "ID AS ROOM_ID"
            , "CODE AS ROOM_CODE"
            , "NAME AS ROOM_NAME"
            , "ROOM_SIZE"
            , "BUILDING"
            , "MAX_COUNT"
            , "CREATED_DATE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_room'), $SELECTION_A);
        $SQL->joinRight(array('B' => 't_schedule'), 'A.ID=B.ROOM_ID', array());
        $SQL->where("B.ROOM_ID <> 0");
        $SQL->where("B.START_DATE<='" . $startDate . "'");
        $SQL->where("B.END_DATE>='" . $endDate . "'");

        if ($schoolyearId)
            $SQL->where("B.SCHOOLYEAR_ID = '" . $schoolyearId . "'");

        if ($globalSearch) {
            $SEARCH = "";
            $SEARCH .= " ((A.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.SHORT LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.ROOM_SIZE LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.BUILDING LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.MAX_COUNT LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

}

?>