<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.02.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/schedule/ScheduleDBAccess.php';
require_once 'models/app_university/schedule/TeacherScheduleDBAccess.php';
require_once setUserLoacalization();

class TeachingSessionDBAccess extends ScheduleDBAccess {

    public $dataforjson = null;
    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function findTeachingSession($Id) {

        $scheduleObject = self::findScheduleFromGuId($Id);
        $teachingsessionObject = self::getTeachingSessionFromId($Id);

        $facette = null;

        if ($scheduleObject) {
            $facette = $scheduleObject;
        } elseif ($teachingsessionObject) {
            $facette = $teachingsessionObject;
        }

        return $facette;
    }

    public function jsonLoadTeachingSession($Id) {

        $facette = self::findTeachingSession($Id);
        $data = array();

        if ($facette) {

            $teachingsessionObject = self::getTeachingSessionFromId($Id);

            if ($teachingsessionObject) {

                switch ($teachingsessionObject->ACTION_TYPE) {
                    case "SUBSTITUTE":
                        $teacherObject = StaffDBAccess::findStaffFromId($teachingsessionObject->SUBSTITUTE_TEACHER);
                        $roomObject = RoomDBAccess::findRoomFromId($teachingsessionObject->SUBSTITUTE_ROOM);
                        break;
                    case "EXTRASESSION":
                    case "DAYOFFSCHOOL":
                        $data["CHOOSE_DATE"] = getShowDate($teachingsessionObject->TEACHING_DATE);
                        $teacherObject = StaffDBAccess::findStaffFromId($facette->TEACHER_ID);
                        $roomObject = RoomDBAccess::findRoomFromId($facette->ROOM_ID);
                        break;
                }
            } else {
                $teacherObject = StaffDBAccess::findStaffFromId($facette->TEACHER_ID);
                $roomObject = RoomDBAccess::findRoomFromId($facette->ROOM_ID);
            }

            if ($teacherObject) {
                $data["TEACHER_HIDDEN"] = $teacherObject->ID;
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data["TEACHER_NAME"] = $teacherObject->LASTNAME . " " . $teacherObject->FIRSTNAME;
                } else {
                    $data["TEACHER_NAME"] = $teacherObject->FIRSTNAME . " " . $teacherObject->LASTNAME;
                }
            }

            if ($roomObject) {
                $data["ROOM_NAME"] = $roomObject->NAME;
                $data["ROOM_HIDDEN"] = $roomObject->ID;
            }
            
            $data["SUBJECT_ID"] = $facette->SUBJECT_ID;
            $data["EVENT"] = $facette->EVENT;
            $data["SCHEDULE_TYPE"] = $facette->SUBJECT_ID;
            $data["SCHEDULE_TYPE"] = $facette->SCHEDULE_TYPE;
            $data["EVENT"] = $facette->EVENT;
            $data["START_TIME"] = secondToHour($facette->START_TIME);
            $data["END_TIME"] = secondToHour($facette->END_TIME);
            $data["DESCRIPTION"] = $facette->DESCRIPTION;
        }

        $o = array(
            "success" => true
            , "data" => $data
        );
        return $o;
    }

    ////////////////////////////////////////////////////////////////////////////
    //VIK: 03.01.2013
    ////////////////////////////////////////////////////////////////////////////
    public function jsonActionTeachingSession($params) {

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : '';
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $choosedate = isset($params["choosedate"]) ? $params["choosedate"] : '';
        $teacherId = isset($params["TEACHER_HIDDEN"]) ? addText($params["TEACHER_HIDDEN"]) : '';
        $roomId = isset($params["ROOM_HIDDEN"]) ? addText($params["ROOM_HIDDEN"]) : '';
        $type = isset($params["type"]) ? addText($params["type"]) : '';

        if ($type) {

            switch (strtoupper($type)) {
                case "SUBSTITUTE":
                    $scheduleObject = self::findScheduleFromGuId($scheduleId);
                    self::dbAccess()->delete("t_teaching_session", array('GUID = ? ' => $scheduleId));
                    if ($scheduleObject) {
                        $SAVEDATA["GUID"] = $scheduleId;
                        $SAVEDATA["TEACHER_ID"] = $scheduleObject->TEACHER_ID;
                        $SAVEDATA["TEACHING_DATE"] = $choosedate;
                        $SAVEDATA["ROOM_ID"] = $roomId;

                        if (isset($params["DESCRIPTION"]))
                            $SAVEDATA["EDUCATION_SYSTEM"] = addText($params["DESCRIPTION"]);

                        $SAVEDATA["SCHEDULE_TYPE"] = $scheduleObject->SCHEDULE_TYPE;
                        $SAVEDATA["START_TIME"] = $scheduleObject->START_TIME;
                        $SAVEDATA["END_TIME"] = $scheduleObject->END_TIME;
                        $SAVEDATA["SHORTDAY"] = $scheduleObject->SHORTDAY;
                        $SAVEDATA["EVENT"] = $scheduleObject->EVENT;
                        $SAVEDATA["SUBJECT_ID"] = $scheduleObject->SUBJECT_ID;
                        $SAVEDATA["ACADEMIC_ID"] = $scheduleObject->ACADEMIC_ID;
                        $SAVEDATA["TRAINING_ID"] = $scheduleObject->TRAINING_ID;
                        $SAVEDATA["EDUCATION_SYSTEM"] = $scheduleObject->EDUCATION_SYSTEM;
                        $SAVEDATA["ACTION_TYPE"] = "SUBSTITUTE";

                        if ($roomId == $scheduleObject->ROOM_ID) {
                            if ($teacherId != $scheduleObject->TEACHER_ID) {
                                $SAVEDATA["SUBSTITUTED_TEACHER"] = $scheduleObject->TEACHER_ID;
                                $SAVEDATA["SUBSTITUTE_TEACHER"] = $teacherId;
                            }
                        }

                        if ($teacherId == $scheduleObject->TEACHER_ID) {
                            if ($roomId != $scheduleObject->ROOM_ID) {
                                $SAVEDATA["SUBSTITUTED_ROOM"] = $scheduleObject->ROOM_ID;
                                $SAVEDATA["SUBSTITUTE_ROOM"] = $roomId;
                            }
                        }

                        self::dbAccess()->insert('t_teaching_session', $SAVEDATA);
                    }
                    break;
                case "DAYOFFSCHOOL":
                    $scheduleObject = self::findScheduleFromGuId($scheduleId);
                    self::dbAccess()->delete("t_teaching_session", array('GUID = ? ' => $scheduleId));
                    $SAVEDATA["GUID"] = $scheduleId;
                    $SAVEDATA["TEACHER_ID"] = $scheduleObject->TEACHER_ID;
                    $SAVEDATA["TEACHING_DATE"] = $choosedate;
                    $SAVEDATA["ROOM_ID"] = $scheduleObject->ROOM_ID;

                    if (isset($params["DESCRIPTION"]))
                        $SAVEDATA["EDUCATION_SYSTEM"] = addText($params["DESCRIPTION"]);
                    $SAVEDATA["SCHEDULE_TYPE"] = $scheduleObject->SCHEDULE_TYPE;
                    $SAVEDATA["START_TIME"] = $scheduleObject->START_TIME;
                    $SAVEDATA["END_TIME"] = $scheduleObject->END_TIME;
                    $SAVEDATA["SHORTDAY"] = $scheduleObject->SHORTDAY;
                    $SAVEDATA["EVENT"] = $scheduleObject->EVENT;
                    $SAVEDATA["SUBJECT_ID"] = $scheduleObject->SUBJECT_ID;
                    $SAVEDATA["ACADEMIC_ID"] = $scheduleObject->ACADEMIC_ID;
                    $SAVEDATA["TRAINING_ID"] = $scheduleObject->TRAINING_ID;
                    $SAVEDATA["EDUCATION_SYSTEM"] = $scheduleObject->EDUCATION_SYSTEM;
                    $SAVEDATA["ACTION_TYPE"] = "DAYOFFSCHOOL";
                    self::dbAccess()->insert('t_teaching_session', $SAVEDATA);
                    break;
            }
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function jsonListExtraTeachingSession($params, $isJson = true) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";
        ///@veasna
        $groupId = isset($params["groupId"]) ? $params["groupId"] : '';
        if ($groupId) {
            $extraSessionGroupArr = array();
            $extraSessionGroup = self::findLinkedScheduleAcademicByAcademicId($groupId);
            foreach ($extraSessionGroup as $extraSession) {
                $extraSessionGroupArr[] = $extraSession->TEACHING_SESSION_ID;
            }
        }
        ///

        switch (strtoupper($target)) {
            case "GENERAL":
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $SELECT_DATA = array(
                        "A.GUID AS GUID"
                        , "A.ID AS EXTRA_TEACHING_SESSION_ID"
                        , "A.ACADEMIC_ID AS ACADEMIC_ID"
                        , "A.SUBJECT_ID AS SUBJECT_ID"
                        , "A.TEACHER_ID AS TEACHER_ID"
                        , "A.ROOM_ID AS ROOM_ID"
                        , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                        , "A.START_TIME AS START_TIME"
                        , "A.END_TIME AS END_TIME"
                        , "A.EVENT AS EVENT"
                        , "A.TERM AS TERM"
                        , "A.SHORTDAY AS SHORTDAY"
                        , "B.NAME AS SUBJECT_NAME"
                        , "B.COLOR AS SUBJECT_COLOR"
                        , "CONCAT(C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
                        , "D.NAME AS ROOM"
                        //, "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM"
                        , "E.NAME AS CLASS_NAME"
                    );
                } else {
                    $SELECT_DATA = array(
                        "A.GUID AS GUID"
                        , "A.ID AS EXTRA_TEACHING_SESSION_ID"
                        , "A.ACADEMIC_ID AS ACADEMIC_ID"
                        , "A.SUBJECT_ID AS SUBJECT_ID"
                        , "A.TEACHER_ID AS TEACHER_ID"
                        , "A.ROOM_ID AS ROOM_ID"
                        , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                        , "A.START_TIME AS START_TIME"
                        , "A.END_TIME AS END_TIME"
                        , "A.EVENT AS EVENT"
                        , "A.TERM AS TERM"
                        , "A.SHORTDAY AS SHORTDAY"
                        , "B.NAME AS SUBJECT_NAME"
                        , "B.COLOR AS SUBJECT_COLOR"
                        , "CONCAT(C.FIRSTNAME,' ',C.LASTNAME) AS TEACHER"
                        , "D.NAME AS ROOM"
                        //, "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM"
                        , "E.NAME AS CLASS_NAME"
                    );
                }
                break;
            case "TRAINING":
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $SELECT_DATA = array(
                        "A.GUID AS GUID"
                        , "A.ID AS EXTRA_TEACHING_SESSION_ID"
                        , "A.TRAINING_ID AS TRAINING_ID"
                        , "A.SUBJECT_ID AS SUBJECT_ID"
                        , "A.TEACHER_ID AS TEACHER_ID"
                        , "A.ROOM_ID AS ROOM_ID"
                        , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                        , "A.SHORTDAY AS SHORTDAY"
                        , "A.START_TIME AS START_TIME"
                        , "A.END_TIME AS END_TIME"
                        , "A.EVENT AS EVENT"
                        , "A.TERM AS TERM"
                        , "B.NAME AS SUBJECT_NAME"
                        , "B.COLOR AS SUBJECT_COLOR"
                        , "CONCAT(C.CODE,'<br>',C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
                        , "D.NAME AS ROOM"
                        , "E.NAME AS TRAINING_NAME"
                    );
                } else {
                    $SELECT_DATA = array(
                        "A.GUID AS GUID"
                        , "A.ID AS EXTRA_TEACHING_SESSION_ID"
                        , "A.TRAINING_ID AS TRAINING_ID"
                        , "A.SUBJECT_ID AS SUBJECT_ID"
                        , "A.TEACHER_ID AS TEACHER_ID"
                        , "A.ROOM_ID AS ROOM_ID"
                        , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                        , "A.SHORTDAY AS SHORTDAY"
                        , "A.START_TIME AS START_TIME"
                        , "A.END_TIME AS END_TIME"
                        , "A.EVENT AS EVENT"
                        , "A.TERM AS TERM"
                        , "B.NAME AS SUBJECT_NAME"
                        , "B.COLOR AS SUBJECT_COLOR"
                        , "CONCAT(C.CODE,'<br>',C.FIRSTNAME,' ',C.LASTNAME) AS TEACHER"
                        , "D.NAME AS ROOM"
                        , "E.NAME AS TRAINING_NAME"
                    );
                }
                break;
            default:
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $SELECT_DATA = array(
                        "A.GUID AS GUID"
                        , "A.ID AS EXTRA_TEACHING_SESSION_ID"
                        , "A.ACADEMIC_ID AS ACADEMIC_ID"
                        , "A.SUBJECT_ID AS SUBJECT_ID"
                        , "A.TEACHER_ID AS TEACHER_ID"
                        , "A.ROOM_ID AS ROOM_ID"
                        , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                        , "A.START_TIME AS START_TIME"
                        , "A.END_TIME AS END_TIME"
                        , "A.EVENT AS EVENT"
                        , "A.TERM AS TERM"
                        , "A.SHORTDAY AS SHORTDAY"
                        , "B.NAME AS SUBJECT_NAME"
                        , "B.COLOR AS SUBJECT_COLOR"
                        , "CONCAT(C.CODE,'<br>',C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
                        , "D.NAME AS ROOM"
                        , "E.NAME AS CLASS_NAME"
                    );
                } else {
                    $SELECT_DATA = array(
                        "A.GUID AS GUID"
                        , "A.ID AS EXTRA_TEACHING_SESSION_ID"
                        , "A.ACADEMIC_ID AS ACADEMIC_ID"
                        , "A.SUBJECT_ID AS SUBJECT_ID"
                        , "A.TEACHER_ID AS TEACHER_ID"
                        , "A.ROOM_ID AS ROOM_ID"
                        , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                        , "A.START_TIME AS START_TIME"
                        , "A.END_TIME AS END_TIME"
                        , "A.EVENT AS EVENT"
                        , "A.TERM AS TERM"
                        , "A.SHORTDAY AS SHORTDAY"
                        , "B.NAME AS SUBJECT_NAME"
                        , "B.COLOR AS SUBJECT_COLOR"
                        , "CONCAT(C.CODE,'<br>',C.FIRSTNAME,' ',C.LASTNAME) AS TEACHER"
                        , "D.NAME AS ROOM"
                        , "E.NAME AS CLASS_NAME"
                    );
                }
                break;
        }

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_teaching_session"), $SELECT_DATA);
        $SQL->joinLeft(array('B' => "t_subject"), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => "t_staff"), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => "t_room"), 'A.ROOM_ID=D.ID', array());

        switch (strtoupper($target)) {
            case "GENERAL":
                $SQL->joinLeft(array('E' => self::TABLE_GRADE), 'A.ACADEMIC_ID=E.ID', array());
                if ($academicId)
                    $SQL->where("A.ACADEMIC_ID IN (" . $academicId . ")");
                break;
            case "TRAINING":
                $SQL->joinLeft(array('E' => 't_training'), 'A.TRAINING_ID=E.ID', array());
                $SQL->joinLeft(array('F' => 't_training'), 'E.PARENT=F.ID', array());

                if ($trainingId) {
                    $SQL->where("A.TRAINING_ID='" . $trainingId . "'");
                } else {
                    $SQL->where("NOW()<=F.END_DATE");
                }
                break;
        }

        $SQL->where("ACTION_TYPE='EXTRASESSION'");

        if ($teacherId)
            $SQL->where('A.TEACHER_ID = ?', $teacherId);

        $SQL->order('A.START_TIME');
        $SQL->group("A.START_TIME");
        $SQL->group("A.END_TIME");

        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);
        $data = array();

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {
                if ($groupId) { //$veasna    
                    if (!in_array($value->EXTRA_TEACHING_SESSION_ID, $extraSessionGroupArr))
                        continue;
                }
                $data[$i]["ID"] = $value->GUID;

                switch ($value->SCHEDULE_TYPE) {
                    case 1:
                        $data[$i]["EVENT"] = setShowText($value->SUBJECT_NAME);
                        $data[$i]["COLOR"] = $value->SUBJECT_COLOR;
                        $data[$i]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                        break;
                    case 2:
                        $data[$i]["EVENT"] = EVENT;
                        $data[$i]["EVENT"] .= "<br>";
                        $data[$i]["EVENT"] .= setShowText($value->EVENT);
                        $data[$i]["COLOR"] = "#FFF";
                        break;
                }

                $data[$i]["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                $data[$i]["START_DATE"] = secondToHour($value->START_TIME);
                $data[$i]["END_DATE"] = secondToHour($value->END_TIME);
                $data[$i]["TEACHER"] = setShowText($value->TEACHER);
                $data[$i]["ROOM"] = setShowText($value->ROOM);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        //@soda
        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
        //
    }

    ////@veasna
    public function jsonListCreditStudentExtraTeachingSession($params, $isJson = true) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $facette = GradeSubjectDBAccess::getStudentCreditSubject($studentId, $schoolyearId);
        $studentGroupArr = array();
        $studentGroup = '';
        if ($facette) {
            foreach ($facette as $studentInSubject) {
                if ($studentInSubject->CLASS_ID) {
                    $studentGroupArr[] = $studentInSubject->CLASS_ID;
                }
            }
            $studentGroup = implode(',', $studentGroupArr);
        }

        $SELECT_DATA = array(
            "A.GUID AS GUID"
            , "A.ID AS EXTRA_TEACHING_SESSION_ID"
            , "A.ACADEMIC_ID AS ACADEMIC_ID"
            , "A.SUBJECT_ID AS SUBJECT_ID"
            , "A.TEACHER_ID AS TEACHER_ID"
            , "A.ROOM_ID AS ROOM_ID"
            , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
            , "A.START_TIME AS START_TIME"
            , "A.END_TIME AS END_TIME"
            , "A.EVENT AS EVENT"
            , "A.TERM AS TERM"
            , "A.SHORTDAY AS SHORTDAY"
            , "B.NAME AS SUBJECT_NAME"
            , "B.COLOR AS SUBJECT_COLOR"
            , "CONCAT(C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
            , "D.NAME AS ROOM"
                //, "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_teaching_session"), $SELECT_DATA);
        $SQL->joinLeft(array('B' => "t_subject"), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => "t_staff"), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => "t_room"), 'A.ROOM_ID=D.ID', array());
        $SQL->joinLeft(array('E' => "t_student_schoolyear_subject"), 'A.SUBJECT_ID=E.SUBJECT_ID', array());

        $SQL->where("E.STUDENT_ID = ?", $studentId);
        $SQL->where("E.SCHOOLYEAR_ID = ?", $schoolyearId);

        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);
        $data = array();

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {
                $inCheck = self::checkCreditExtraSessionInGroup($value->EXTRA_TEACHING_SESSION_ID, $studentGroup);
                if (!$inCheck) {
                    continue;
                }
                $data[$i]["ID"] = $value->GUID;

                switch ($value->SCHEDULE_TYPE) {
                    case 1:
                        $data[$i]["EVENT"] = setShowText($value->SUBJECT_NAME);
                        $data[$i]["COLOR"] = $value->SUBJECT_COLOR;
                        $data[$i]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                        break;
                    case 2:
                        $data[$i]["EVENT"] = EVENT;
                        $data[$i]["EVENT"] .= "<br>";
                        $data[$i]["EVENT"] .= setShowText($value->EVENT);
                        $data[$i]["COLOR"] = "#FFF";
                        break;
                }

                $data[$i]["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                $data[$i]["START_DATE"] = secondToHour($value->START_TIME);
                $data[$i]["END_DATE"] = secondToHour($value->END_TIME);
                $data[$i]["TEACHER"] = setShowText($value->TEACHER);
                $data[$i]["ROOM"] = setShowText($value->ROOM);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        //@soda
        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
        //
    }

    ///
    public function jsonActionExtraTeachingSession($params) {

        $ERRORS = array();

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : false;

        $choosedate = isset($params["CHOOSE_DATE"]) ? $params["CHOOSE_DATE"] : false;
        $startTime = isset($params["START_TIME"]) ? addText($params["START_TIME"]) : false;
        $endTime = isset($params["END_TIME"]) ? addText($params["END_TIME"]) : false;
        $scheduleType = isset($params["SCHEDULE_TYPE"]) ? addText($params["SCHEDULE_TYPE"]) : false;
        $eventName = isset($params["EVENT"]) ? addText($params["EVENT"]) : false;
        $subjectId = isset($params["SUBJECT"]) ? addText($params["SUBJECT"]) : false;
        $roomId = isset($params["ROOM_HIDDEN"]) ? addText($params["ROOM_HIDDEN"]) : false;
        $teacherId = isset($params["TEACHER_HIDDEN"]) ? addText($params["TEACHER_HIDDEN"]) : false;
        $description = isset($params["DESCRIPTION"]) ? $params["DESCRIPTION"] : false;
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : false;
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : false;

        if ($choosedate) {
            $SAVEDATA["TEACHING_DATE"] = setDate2DB($choosedate);
            $SAVEDATA["SHORTDAY"] = getWEEKDAY(setDate2DB($choosedate));
        }

        if ($startTime)
            $SAVEDATA["START_TIME"] = timeStrToSecond($startTime);

        if ($endTime)
            $SAVEDATA["END_TIME"] = timeStrToSecond($endTime);

        if ($scheduleType)
            $SAVEDATA["SCHEDULE_TYPE"] = addText($scheduleType);

        if ($eventName)
            $SAVEDATA["EVENT"] = addText($eventName);

        if ($subjectId)
            $SAVEDATA["SUBJECT_ID"] = addText($subjectId);

        if ($roomId)
            $SAVEDATA["ROOM_ID"] = addText($roomId);

        if ($teacherId)
            $SAVEDATA["TEACHER_ID"] = addText($teacherId);

        if ($description)
            $SAVEDATA["DESCRIPTION"] = addText($description);

        if ($trainingId) {
            $SAVEDATA["ACADEMIC_TYPE"] = 'TRAINING';
            $SAVEDATA["TRAINING_ID"] = addText($trainingId);
        } elseif ($academicId) {
            $SAVEDATA["ACADEMIC_TYPE"] = 'GENERAL';
            $SAVEDATA["ACADEMIC_ID"] = addText($academicId);
        }

        $facette = self::getTeachingSessionFromId($scheduleId);

        if ($facette) {
            //Update...
            //$ERROR_TIME_HAS_BEEN_USED = false;
            $WHERE = array(
                'GUID = ?' => $scheduleId
            );
            self::dbAccess()->update('t_teaching_session', $SAVEDATA, $WHERE);
        } else {
            //Insert....
            $SAVEDATA["GUID"] = $scheduleId;
            $SAVEDATA["ACTION_TYPE"] = 'EXTRASESSION';
            self::dbAccess()->insert('t_teaching_session', $SAVEDATA);
        }

        if ($ERRORS) {
            return array(
                "success" => false
                , "errors" => $ERRORS
                , "objectId" => $scheduleId
            );
        } else {
            return array(
                "success" => true
                , "errors" => $ERRORS
                , "objectId" => $scheduleId
            );
        }
    }

    public function jsonDeleteTeachingSession($Id) {
        self::dbAccess()->delete('t_teaching_session', array("GUID='" . $Id . "'"));
        return array(
            "success" => true
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    //@Sea Peng
    ////////////////////////////////////////////////////////////////////////////
    public static function getTeachingSession($teacherId, $day) {

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
            , "C.NAME AS ROOM"
            , "CONCAT(C.NAME,'<br>',C.BUILDING,'<br>',C.FLOOR) AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_teaching_session'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_room'), 'A.ROOM_ID=C.ID', array());
        $SQL->where("TEACHER_ID='" . $teacherId . "'");
        $SQL->where("TEACHING_DATE='" . $day . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    public static function sqlTeachingSession($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";
        $academicType = isset($params["academicType"]) ? $params["academicType"] : "GENERAL";

        $SELECTION_A = array(
            "STAFF_INDEX"
            , "ID AS STAFF_ID"
            , "CODE AS STAFF_CODE"
            , "FIRSTNAME"
            , "LASTNAME"
            , "FIRSTNAME_LATIN"
            , "LASTNAME_LATIN"
            , "STAFF_SCHOOL_ID"
            , "GENDER"
            , "DATE_BIRTH"
            , "EMAIL"
            , "PHONE"
            , "CREATED_DATE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_staff'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_schedule'), 'A.ID=B.TEACHER_ID', array());
        $SQL->where("B.START_DATE<='" . $endDate . "'");
        $SQL->where("B.END_DATE>='" . $startDate . "'");

        switch (strtoupper($academicType)) {
            case "GENERAL":
                $SQL->where("B.ACADEMIC_ID<>0");
                $SQL->where("B.EDUCATION_SYSTEM=0");
                break;
            case "CREDIT":
                $SQL->where("B.ACADEMIC_ID<>0");
                $SQL->where("B.EDUCATION_SYSTEM=1");
                break;
            case "TRAINING":
                $SQL->where("B.TRAINING_ID<>0");
                break;
        }

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        $SQL->group("B.TEACHER_ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountTeacherSession($teacherId, $startDate, $endDate, $academicType) {
        $days = getDatesBetween2Dates($startDate, $endDate);
        $data = array();
        $countSession = 0;
        foreach ($days as $day) {
            $shortday = getWEEKDAY($day);
            if ($shortday) {
                $entries = TeacherScheduleDBAccess::getShortSQLTeacherEvent($teacherId, $startDate, $endDate, $shortday, $academicType);
                if ($entries) {
                    foreach ($entries as $value) {
                        $countSession++;    
                    }
                }
            }
        }

        return $countSession;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountEventDayOffSchool($startdate, $enddate) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_schoolevent", array('COUNT(*) AS C'));
        $SQL->where("DAY_OFF_SCHOOL='1'");
        $SQL->where("START_DATE<='" . $enddate . "'");
        $SQL->where("END_DATE>='" . $startdate . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountTeacherAttendance($teacherId, $startdate, $enddate) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array('COUNT(*) AS C'));
        $SQL->where("STAFF_ID='" . $teacherId . "'");
        $SQL->where("('" . $startdate . "' BETWEEN START_DATE AND END_DATE) OR ('" . $enddate . "' BETWEEN START_DATE AND END_DATE)");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountTeacherExtraSession($teacherId, $startdate, $enddate) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_teaching_session", array('COUNT(*) AS C'));
        $SQL->where("TEACHER_ID='" . $teacherId . "'");
        $SQL->where("ACTION_TYPE='EXTRASESSION'");
        $SQL->where("TEACHING_DATE BETWEEN '" . $startdate . "' AND '" . $enddate . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountTeacherSubstitute($teacherId, $startdate, $enddate) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_teaching_session", array('COUNT(*) AS C'));
        $SQL->where("SUBSTITUTE_TEACHER='" . $teacherId . "'");
        $SQL->where("ACTION_TYPE='SUBSTITUTE'");
        $SQL->where("TEACHING_DATE BETWEEN '" . $startdate . "' AND '" . $enddate . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountTeacherSubstituted($teacherId, $startdate, $enddate) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_teaching_session", array('COUNT(*) AS C'));
        $SQL->where("SUBSTITUTED_TEACHER='" . $teacherId . "'");
        $SQL->where("ACTION_TYPE='SUBSTITUTE'");
        $SQL->where("TEACHING_DATE BETWEEN '" . $startdate . "' AND '" . $enddate . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountSessionCanceled($teacherId, $startdate, $enddate) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_teaching_session", array('COUNT(*) AS C'));
        $SQL->where("TEACHER_ID='" . $teacherId . "'");
        $SQL->where("ACTION_TYPE='DAYOFFSCHOOL'");
        $SQL->where("TEACHING_DATE BETWEEN '" . $startdate . "' AND '" . $enddate . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Kaom 01.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonListTeachingSession($params, $isJson = true) {

        set_time_limit(3600);
        ini_set('memory_limit', '128M');

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "100";
        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";
        $academicType = isset($params["type"]) ? addText($params["type"]) : "GENERAL";

        $data = array();

        $result = self::sqlTeachingSession($params);
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $COUNT_EVENT_DAY_OFF_SCHOOL = self::getCountEventDayOffSchool($startDate, $endDate);
                $COUNT_EXTRA_SESSION = self::getCountTeacherExtraSession($value->STAFF_ID, $startDate, $endDate);
                $COUNT_SUBSTITUTE = self::getCountTeacherSubstitute($value->STAFF_ID, $startDate, $endDate);
                $COUNT_SUBSTITUTED = self::getCountTeacherSubstituted($value->STAFF_ID, $startDate, $endDate);
                $COUNT_ATTENDANCE = self::getCountTeacherAttendance($value->STAFF_ID, $startDate, $endDate);
                $COUNT_TEACHING_SESSION = self::getCountTeacherSession($value->STAFF_ID, $startDate, $endDate, $academicType);
                $COUNT_CANCELED_SESSION = self::getCountSessionCanceled($value->STAFF_ID, $startDate, $endDate, $academicType);

                $data[$i]["STAFF_ID"] = $value->STAFF_ID;
                $data[$i]["STAFF_SCHOOL_ID"] = setShowText($value->STAFF_SCHOOL_ID);
                $data[$i]["STAFF_CODE"] = $value->STAFF_CODE;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = $value->GENDER;
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["FULLNAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]["NUMBER_SESSION"] = $COUNT_TEACHING_SESSION;
                $data[$i]["NUMBER_ABSENCE"] = $COUNT_ATTENDANCE;
                $data[$i]["NUMBER_EXTRA_SESSION"] = $COUNT_EXTRA_SESSION;
                $data[$i]["CANCEL"] = $COUNT_CANCELED_SESSION;

                if ($COUNT_SUBSTITUTED > 0) {
                    $CALCULATED_SUBSTITUTE = "-" . $COUNT_SUBSTITUTED;
                    $TOTAL = ($COUNT_TEACHING_SESSION + $COUNT_EXTRA_SESSION - $COUNT_SUBSTITUTED) - ($COUNT_ATTENDANCE + $COUNT_EVENT_DAY_OFF_SCHOOL + $COUNT_CANCELED_SESSION);
                } else {
                    $CALCULATED_SUBSTITUTE = ($COUNT_SUBSTITUTE > 0) ? "+" . $COUNT_SUBSTITUTE : $COUNT_SUBSTITUTE;
                    $TOTAL = ($COUNT_TEACHING_SESSION + $COUNT_EXTRA_SESSION + $COUNT_SUBSTITUTE) - ($COUNT_ATTENDANCE + $COUNT_EVENT_DAY_OFF_SCHOOL + $COUNT_CANCELED_SESSION);
                }

                $data[$i]["SUBSTITUTE"] = $CALCULATED_SUBSTITUTE;
                $data[$i]["TOTAL"] = $TOTAL;

                $i++;
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

    ////////////////////////////////////////////////////////////////////////////

    public static function getTeachingSessionFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_teaching_session', '*');
        $SQL->where("GUID='" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getDetailSubstitute($object) {

        $teacherObject = StaffDBAccess::findStaffFromId($object->SUBSTITUTE_TEACHER);
        $roomObject = RoomDBAccess::findRoomFromId($object->SUBSTITUTE_ROOM);

        $html = "";
        if ($teacherObject) {
            $html .= $teacherObject->LASTNAME . " " . $teacherObject->FIRSTNAME;
        }

        if ($roomObject) {
            $html .= "<br>";
            $html .= $roomObject->NAME;
        }

        return $html;
    }

    //@veasna
    public static function getTeacherSessionCreditSystem($teacherId, $startDate, $endDate, $subjectCreditId, $term) {

        $days = getDatesBetween2Dates($startDate, $endDate);
        $countTeacherSession = 0;

        if ($days) {
            $CHECK_DAYS = array();
            foreach ($days as $day) {
                $CHECK_DAY = TeacherScheduleDBAccess::checkDay($day);
                if (!$CHECK_DAY) {
                    $CHECK_DAYS[$day] = $day;
                }
            }

            if ($CHECK_DAYS) {
                foreach ($CHECK_DAYS as $day) {
                    $shortday = getWEEKDAY($day);
                    $teacherScedule = self::countTeacherScheduleSemester($teacherId, $shortday, $subjectCreditId, $term);
                    if ($teacherScedule) {
                        $countTeacherSession = $countTeacherSession + $teacherScedule;
                    }
                }
            }
        }

        return $countTeacherSession;
    }

    public static function countTeacherScheduleSemester($teacherId, $shortday, $subjectCreditId, $term) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_schedule'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_link_schedule_academic'), 'A.ID=B.SCHEDULE_ID');
        $SQL->where("B.ACADEMIC_ID = '" . $subjectCreditId . "'");
        $SQL->where("A.TEACHER_ID = '" . $teacherId . "'");
        $SQL->where("A.SHORTDAY = '" . $shortday . "'");
        $SQL->where("A.TERM = '" . $term . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///
}

?>