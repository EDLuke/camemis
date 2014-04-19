<?php

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/school/SchoolDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'models/app_school/room/RoomDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDBAccess.php';
require_once 'models/app_school/schedule/TeachingSessionDBAccess.php';
require_once 'models/app_school/training/TrainingDBAccess.php';
require_once setUserLoacalization();

class TeacherScheduleDBAccess extends ScheduleDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new TeacherScheduleDBAccess();
        }
        return $me;
    }

    public static function getCurrentSchoolyearId() {

        if (AcademicDateDBAccess::findSchoolyearByCurrentDate()) {
            $YEAR_OBJECT = AcademicDateDBAccess::findSchoolyearByCurrentDate();
            return $YEAR_OBJECT->ID;
        } else {
            return "";
        }
    }

    public static function getCurrentTerm($eventDay) {

        $term = AcademicDBAccess::getNameOfSchoolTermByDate(
                $eventDay
                , self::getCurrentSchoolyearId()
        );

        return $term;
    }

    public function getSelectedCurrentTime($academicId) {

        $eventDay = getCurrentDBDate();
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        $term = AcademicDBAccess::getNameOfSchoolTermByDate($eventDay, $academicObject->ID);
        $query = self::dbAccess()->select();
        $query->from("t_schedule", '*');
        $query->where("END_TIME >= " . convertTimeInSecond(getCurrentDBDateTime() . ""));
        $query->where("SCHOOLYEAR_ID= '" . $this->getCurrentSchoolyearId() . "'");
        $query->where("TERM = '" . $term . "'");

        //error_log($query->__toString());
        $result = self::dbAccess()->fetchRow($query);

        $out = null;
        if ($result) {

            $classObject = AcademicDBAccess::findGradeFromId($result->ACADEMIC_ID);

            switch ($academicObject->OBJECT_TYPE) {
                case "CAMPUS":
                    if ($classObject->CAMPUS_ID == $academicId) {
                        $out = $result;
                    }
                    break;
                case "GRADE":
                    if ($classObject->GRADE_ID == $academicId) {
                        $out = $result;
                    }
                    break;
            }
        }

        return $out;
    }

    public function campusCurrentTime($academicId) {

        $result = $this->getSelectedCurrentTime($academicId);
        return $result ? secondToHour($result->START_TIME) . " - " . secondToHour($result->END_TIME) : "";
    }

    public function getSqlCampusSchedule($params, $groupBy = false) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $currentTerm = isset($params["currentTerm"]) ? addText($params["currentTerm"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        $eventDay = isset($params["eventDay"]) ? setFormatDate($params["eventDay"]) : getCurrentDBDate();
        $shortday = getWEEKDAY($eventDay);
        $termByChooseDay = AcademicDBAccess::getNameOfSchoolTermByDate($eventDay, $academicObject->ID);

        $SELECT_A = array(
            "GUID AS GUID"
            , "START_TIME AS START_TIME"
            , "END_TIME AS END_TIME"
            , "TEACHER_ID AS TEACHER_ID"
            , "ROOM_ID AS ROOM_ID"
        );

        $SELECT_C = array(
            "CODE AS TEACHER_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
        );

        $SELECT_D = array(
            "NAME AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_schedule'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_staff'), 'A.TEACHER_ID=C.ID', $SELECT_C);
        $SQL->joinLeft(array('D' => 't_room'), 'A.ROOM_ID=D.ID', $SELECT_D);
        $SQL->joinLeft(array('E' => 't_grade'), 'A.ACADEMIC_ID=E.ID', array());

        if ($currentTerm) {
            $SQL->where("A.TERM='" . $currentTerm . "'");
        } else {
            $SQL->where('A.TERM = ?', $termByChooseDay);
            $SQL->where('A.SHORTDAY = ?', $shortday);
        }

        $SQL->where('A.SCHOOLYEAR_ID = ?', $this->getCurrentSchoolyearId());

        if ($academicObject) {
            switch ($academicObject->OBJECT_TYPE) {
                case "CAMPUS":
                    $SQL->where('E.CAMPUS_ID = ?', $academicId);
                    break;
                case "GRADE":
                    $SQL->where('E.GRADE_ID = ?', $academicId);
                    break;
                case "CLASS":
                    $SQL->where('E.ACADEMIC_ID = ?', $academicId);
                    break;
            }
        }

        $SQL->order('A.START_TIME');

        if ($groupBy)
            $SQL->group($groupBy);

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function campusrRoomList($params) {

        $data = array();

        $result = $this->getSqlCampusSchedule($params);

        $DATA_TEMP = Array();
        if ($result) {
            foreach ($result as $key => $value) {

                $name = $value->ROOM;
                $DATA_TEMP[$value->ROOM_ID] = $name;
            }
        }

        $i = 0;
        $data[0]["ID"] = "";
        $data[0]["NAME"] = "[---]";
        foreach ($DATA_TEMP as $key => $value) {
            $data[$i + 1]["ID"] = $key;
            $data[$i + 1]["NAME"] = $value;
            $i++;
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function campusTeacherList($params) {

        $data = array();

        $result = $this->getSqlCampusSchedule($params);

        $DATA_TEMP = Array();
        if ($result) {
            foreach ($result as $key => $value) {

                $name = "(" . $value->TEACHER_CODE . ") " . $value->LASTNAME . " " . $value->FIRSTNAME;
                $DATA_TEMP[$value->TEACHER_ID] = $name;
            }
        }

        $i = 0;
        $data[0]["ID"] = "";
        $data[0]["NAME"] = "[---]";
        foreach ($DATA_TEMP as $key => $value) {
            $data[$i + 1]["ID"] = $key;
            $data[$i + 1]["NAME"] = $value;
            $i++;
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function campusTimeList($params) {

        $data = array();

        $result = $this->getSqlCampusSchedule($params);

        $DATA_TEMP = Array();
        if ($result) {
            foreach ($result as $key => $value) {
                $time = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                if (!array_key_exists($time, $DATA_TEMP)) {
                    $DATA_TEMP[$time] = $time;
                }
            }
        }

        $i = 0;
        $data[0]["ID"] = "";
        $data[0]["NAME"] = "[---]";
        foreach ($DATA_TEMP as $key => $value) {
            $data[$i + 1]["ID"] = $key;
            $data[$i + 1]["NAME"] = $value;
            $i++;
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function campusEventList($params) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "GENERAL";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $roomId = isset($params["roomId"]) ? addText($params["roomId"]) : "";
        $currentTerm = isset($params["currentTerm"]) ? addText($params["currentTerm"]) : "";
        $selectedDay = isset($params["selectedDay"]) ? addText($params["selectedDay"]) : "";
        $searchTeacherCode = isset($params["searchTeacherCode"]) ? addText($params["searchTeacherCode"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $eventDay = isset($params["eventDay"]) ? setFormatDate($params["eventDay"]) : getCurrentDBDate();
        $shortday = getWEEKDAY($eventDay);

        $forTime = isset($params["forTime"]) ? $params["forTime"] : "";
        $forTimeStart = 0;
        $forTimeEnd = 0;

        if ($forTime != "") {
            $forTimeStart = convertTimeInSecond(setFormatTime($forTime));
            $forTimeEnd = convertTimeInSecond(setFormatTime($forTime, false));
        }

        $termBychooseDay = AcademicDBAccess::getNameOfSchoolTermByDate($eventDay, $academicObject->ID);

        $SELECT_DATA = array(
            "A.GUID AS GUID"
            , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
            , "A.STATUS AS SCHEDULE_STATUS"
            , "A.ROOM_ID AS ROOM_ID"
            , "A.TEACHER_ID AS TEACHER_ID"
            , "A.SHORTDAY AS SHORTDAY"
            , "A.CODE AS SCHEDULE_CODE"
            , "A.START_TIME AS START_TIME"
            , "A.END_TIME AS END_TIME"
            , "A.EVENT AS EVENT"
            , "A.TERM AS TERM"
            , "B.NAME AS SUBJECT_NAME"
            , "B.COLOR AS SUBJECT_COLOR"
            , "CONCAT(C.CODE,'<br>',C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
            , "D.NAME AS ROOM"
            , "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_schedule'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_staff'), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => 't_room'), 'A.ROOM_ID=D.ID', array());

        if (strtoupper($target) == "GENERAL") {
            $SQL->joinLeft(array('E' => 't_grade'), 'A.ACADEMIC_ID=E.ID', array("NAME AS CLASS_NAME"));
        }

        if (strtoupper($target) == "TRAINING") {

            $TRAINING_OBEJECT = TrainingDBAccess::findTrainingFromId($trainingId);

            if ($TRAINING_OBEJECT) {
                switch ($TRAINING_OBEJECT->OBJECT_TYPE) {
                    case "PROGRAM":
                        $SQL->joinLeft(array('F' => 't_training'), 'A.TRAINING_ID=F.ID', array("NAME AS TRAINING_NAME"));
                        $SQL->joinLeft(array('G' => 't_training'), 'F.PARENT=F.ID', array("START_DATE AS TRAINING_START_DATE", "END_DATE AS TRAINING_END_DATE"));
                        $SQL->joinLeft(array('H' => 't_training'), 'F.LEVEL=H.ID', array("NAME AS TRAINING_LEVEL"));
                        $SQL->joinLeft(array('I' => 't_training'), 'F.PROGRAM=I.ID', array("NAME AS TRAINING_PROGRAMM"));
                        $SQL->where("I.ID='" . $TRAINING_OBEJECT->ID . "'");
                        break;
                    case "LEVEL":
                        $SQL->joinLeft(array('F' => 't_training'), 'A.TRAINING_ID=F.ID', array("NAME AS TRAINING_NAME"));
                        $SQL->joinLeft(array('G' => 't_training'), 'F.PARENT=F.ID', array("START_DATE AS TRAINING_START_DATE", "END_DATE AS TRAINING_END_DATE"));
                        $SQL->joinLeft(array('H' => 't_training'), 'F.LEVEL=H.ID', array("NAME AS TRAINING_LEVEL"));
                        $SQL->where("H.ID='" . $TRAINING_OBEJECT->ID . "'");
                        break;
                }
            }
        }

        if ($forTimeStart && $forTimeEnd) {
            $SQL->where('A.START_TIME = ?', $forTimeStart);
            $SQL->where('A.END_TIME = ?', $forTimeEnd);
        }

        if (strtoupper($target) == "GENERAL") {

            if ($currentTerm) {
                $SQL->where("A.TERM='" . $currentTerm . "'");
            } else {
                $SQL->where("A.TERM = '" . $termBychooseDay . "'");
                $SQL->where("A.SHORTDAY = '" . $shortday . "'");
            }
            $SQL->where("A.SCHOOLYEAR_ID = '" . $this->getCurrentSchoolyearId() . "'");
            $SQL->where("E.OBJECT_TYPE = 'CLASS'");

            if ($academicObject) {
                switch ($academicObject->OBJECT_TYPE) {
                    case "CAMPUS":
                        $SQL->where('E.CAMPUS_ID = ?', $academicId);
                        break;
                    case "GRADE":
                        $SQL->where('E.GRADE_ID = ?', $academicId);
                        break;
                }
            }
        }

        if ($selectedDay)
            $SQL->where("A.SHORTDAY = '" . $selectedDay . "'");

        if ($searchTeacherCode)
            $SQL->where("C.CODE LIKE ?", '%' . $searchTeacherCode . '%');

        if ($teacherId)
            $SQL->where("A.TEACHER_ID = '" . $teacherId . "'");
        if ($roomId)
            $SQL->where("A.ROOM_ID = '" . $roomId . "'");

        $SQL->order('A.START_TIME');
        $SQL->group('A.CODE');

        //echo ($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = Array();

        $i = 0;
        if ($result) {
            foreach ($result as $key => $value) {

                $data[$key]["CODE"] = $value->SCHEDULE_CODE;
                $data[$key]["DAY"] = getShortdayName($value->SHORTDAY);

                if (strtoupper($target) == "TRAINING") {
                    $data[$key]["CLASS"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                    $data[$key]["CLASS"] .= "<BR>" . setShowText($value->TRAINING_NAME);
                    $data[$key]["CLASS"] .= "<BR>" . setShowText($value->SUBJECT_NAME);
                    $data[$key]["COLOR"] = $value->SUBJECT_COLOR;
                    $data[$key]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);

                    if ($TRAINING_OBEJECT) {

                        switch ($TRAINING_OBEJECT->OBJECT_TYPE) {

                            case "PROGRAM":
                                $data[$key]["EVENT"] = setShowText($value->TRAINING_PROGRAMM);
                                $data[$key]["EVENT"] .= "<BR>" . setShowText($value->TRAINING_LEVEL);
                                break;
                            case "LEVEL":
                                $data[$key]["EVENT"] = setShowText($value->TRAINING_LEVEL);
                                break;
                        }
                    }
                } else {
                    $data[$key]["CLASS"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                    $data[$key]["CLASS"] .= "<BR>" . setShowText($value->CLASS_NAME);
                    switch ($value->SCHEDULE_TYPE) {
                        case 1:
                            $data[$key]["EVENT"] = setShowText($value->SUBJECT_NAME);
                            $data[$key]["EVENT"] .= "<BR>" . displaySchoolTerm($value->TERM);
                            $data[$key]["COLOR"] = $value->SUBJECT_COLOR;
                            $data[$key]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                            break;
                        case 2:
                            $data[$key]["EVENT"] = setShowText($value->EVENT);
                            $data[$key]["EVENT"] .= "<BR>" . displaySchoolTerm($value->TERM);
                            $data[$key]["COLOR"] = "#CCCCCC";
                            break;
                    }
                }

                $data[$i]["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                $data[$i]["START_DATE"] = secondToHour($value->START_TIME);
                $data[$i]["END_DATE"] = secondToHour($value->END_TIME);
                $data[$i]["TEACHER"] = setShowText($value->TEACHER);
                $data[$i]["ROOM"] = setShowText($value->ROOM);

                $data[$i]["ROOM_ID"] = $value->ROOM_ID;
                $data[$i]["TEACHER_ID"] = $value->TEACHER_ID;

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function checkDay($day) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_schoolevent";
        $SQL .= " WHERE";
        $SQL .= " DAY_OFF_SCHOOL=1";
        $SQL .= " AND '" . $day . "' BETWEEN START_DATE and END_DATE";
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //@Sea Peng 20.08.2013
    ////////////////////////////////////////////////////////////////////////////
    public static function getSchoolyearIdByDate($date) {

        $YEAR_OBJECT = AcademicDateDBAccess::findSchoolyearByDate($date);
        if ($YEAR_OBJECT) {
            return $YEAR_OBJECT->ID;
        } else {
            return "";
        }
    }

    public static function getTermByDate($date) {

        $term = AcademicDBAccess::getNameOfSchoolTermByDate($date, self::getSchoolyearIdByDate($date));

        return $term;
    }

    public static function checkTeacherSession($teacherId, $day) {

        $checkday = self::checkDay($day);
        if (!$checkday) {
            //$term = self::getTermByDate($day);
            $shortday = getWEEKDAY($day);

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_schedule'), array("C" => "COUNT(*)"));

            if ($shortday)
                $SQL->where("A.SHORTDAY = '" . $shortday . "'");
            if ($teacherId)
                $SQL->where("A.TEACHER_ID = '" . $teacherId . "'");
            /*
              if ($term)
              $SQL->where("A.TERM = '" . $term . "'");
             */
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //VIK 01.01.214
    ////////////////////////////////////////////////////////////////////////////
    public static function getSQLTeacherEvent($teacherId, $startDate, $endDate, $shortDay, $academicType) {

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
            , "D.NAME AS ROOM"
            , "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_schedule'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_staff'), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => 't_room'), 'A.ROOM_ID=D.ID', array());
        $SQL->joinLeft(array('E' => 't_grade'), 'A.ACADEMIC_ID=E.ID', array("NAME AS ACADEMIC_NAME"));

        if ($shortDay)
            $SQL->where("A.SHORTDAY = '" . $shortDay . "'");

        if ($teacherId)
            $SQL->where("A.TEACHER_ID = '" . $teacherId . "'");

        $SQL->where("A.START_DATE<='" . $endDate . "'");
        $SQL->where("A.END_DATE>='" . $startDate . "'");

        switch (strtoupper($academicType)) {
            case "GENERAL":
                $SQL->where("A.ACADEMIC_ID<>0");
                $SQL->where("A.EDUCATION_SYSTEM=0");
                break;
            case "CREDIT":
                $SQL->where("A.ACADEMIC_ID<>0");
                $SQL->where("A.EDUCATION_SYSTEM=1");
                break;
            case "TRAINING":
                $SQL->where("A.TRAINING_ID<>0");
                break;
        }
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////
    //VIK 01.01.214
    ////////////////////////////////////////////////////////////////////////////
    public static function getShortSQLTeacherEvent($teacherId, $startDate, $endDate, $shortDay, $academicType) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("*"));
        if ($shortDay)
            $SQL->where("SHORTDAY = '" . $shortDay . "'");

        if ($teacherId)
            $SQL->where("TEACHER_ID = '" . $teacherId . "'");

        $SQL->where("START_DATE<='" . $endDate . "'");
        $SQL->where("END_DATE>='" . $startDate . "'");

        switch (strtoupper($academicType)) {
            case "GENERAL":
                $SQL->where("ACADEMIC_ID<>0");
                $SQL->where("EDUCATION_SYSTEM=0");
                break;
            case "CREDIT":
                $SQL->where("ACADEMIC_ID<>0");
                $SQL->where("EDUCATION_SYSTEM=1");
                break;
            case "TRAINING":
                $SQL->where("TRAINING_ID<>0");
                break;
        }
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getCurrentTermNew($eventDay, $schoolyearId) {
        return AcademicDBAccess::getNameOfSchoolTermByDate($eventDay, $schoolyearId);
    }

    ////////////////////////////////////////////////////////////////////////////
    //VIK 01.01.214
    ////////////////////////////////////////////////////////////////////////////
    public function searchTeachingSession($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "100";
        $startDate = isset($params["startdt"]) ? substr($params["startdt"], 0, 10) : "";
        $endDate = isset($params["enddt"]) ? substr($params["enddt"], 0, 10) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $academicType = isset($params["type"]) ? addText($params["type"]) : "GENERAL";

        $days = getDatesBetween2Dates($startDate, $endDate);
        $data = array();
        $i = 0;
        foreach ($days as $day) {
            $shortday = getWEEKDAY($day);
            if ($shortday) {
                $entries = self::getSQLTeacherEvent($teacherId, $startDate, $endDate, $shortday, $academicType);
                if ($entries) {
                    foreach ($entries as $value) {
                        $data[$i]["ID"] = $value->GUID;
                        $data[$i]["TEACHING_STATUS"] = "---";
                        $data[$i]["EVENT"] = $value->EVENT;
                        $data[$i]["ROOM"] = $value->ROOM;
                        $data[$i]["SESSION_DATE"] = getShortdayName($shortday);
                        $data[$i]["SESSION_DATE"] .= "<br>";
                        $data[$i]["SESSION_DATE"] .= secondToHour($value->START_TIME) . " " . secondToHour($value->END_TIME);
                        $data[$i]["SESSION_DATE"] .= "<br>";
                        $data[$i]["SESSION_DATE"] .= getShowDate($day);
                        $data[$i]["COLOR"] = $value->SUBJECT_COLOR;
                        $data[$i]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                        $data[$i]["CLASS"] = $value->ACADEMIC_NAME;
                    }
                }
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
            , "rows" => $a
        );
    }

    public static function getStaffAttendanceByDate($staffId, $date) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_attendance'), array('ACTION_TYPE'));
        $SQL->joinLeft(array('B' => 't_absent_type'), 'A.ABSENT_TYPE=B.ID', array('NAME AS ABSENCE_TYPE'));

        $SQL->where("A.STAFF_ID = '" . $staffId . "'");
        $SQL->where("'" . $date . "' BETWEEN A.START_DATE AND A.END_DATE");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////

    public static function getTeacherSessionStatus($teacherId, $checkDate) {

        $firstSQL = "SELECT *";
        $firstSQL .= " FROM t_staff_attendance";
        $firstSQL .= " WHERE";
        $firstSQL .= " STAFF_ID='" . $teacherId . "'";
        $firstSQL .= " AND '" . $checkDate . "' BETWEEN START_DATE and END_DATE";
        $firstResult = self::dbAccess()->fetchRow($firstSQL);

        $secondSQL = "SELECT *";
        $secondSQL .= " FROM t_teaching_session";
        $secondSQL .= " WHERE";
        $secondSQL .= " TEACHER_ID='" . $teacherId . "'";
        $secondSQL .= " AND TEACHING_DATE='" . $checkDate . "'";
        $secondResult = self::dbAccess()->fetchRow($secondSQL);

        $status = "---";
        if ($firstResult && !$secondResult) {

            switch ($firstResult->ABSENT_TYPE) {
                case 1:
                    $status = EXCUSED_TARDY;
                    break;
                case 2:
                    $status = EXCUSED_ABSENCE;
                    break;
                case 3:
                    $status = UNEXCUSED_ABSENCE;
                    break;
            }
        } elseif (!$firstResult && $secondResult) {
            $status = TEACHING;
        } else {
            $status = "---";
        }

        return $status;
    }

}

?>