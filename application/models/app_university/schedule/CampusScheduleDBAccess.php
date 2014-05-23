<?php
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/school/SchoolDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/room/RoomDBAccess.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/schedule/ScheduleDBAccess.php';
require_once 'models/app_university/training/TrainingDBAccess.php';
require_once setUserLoacalization();

class CampusScheduleDBAccess extends ScheduleDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new CampusScheduleDBAccess();
        }
        return $me;
    }

    public function getCurrentSchoolyearId() {

        if(AcademicDateDBAccess::findSchoolyearByCurrentDate()){
            $YEAR_OBJECT = AcademicDateDBAccess::findSchoolyearByCurrentDate();
            return $YEAR_OBJECT->ID;
        }else{
            return "";
        }
    }
    
    public function getSchoolyearName($schoolyearId) {
        $SQL  = "SELECT NAME ";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE ID='" . $schoolyearId . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->NAME : "";   
    }

    public function getCurrentTerm($eventDay) {

        $term = AcademicDBAccess::getNameOfSchoolTermByDate(
                $eventDay
                , $this->getCurrentSchoolyearId()
        );

        return $term;
    }

    public function getSelectedCurrentTime($academicId) {

        $eventDay = getCurrentDBDate();
        $term = $this->getCurrentTerm($eventDay);

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

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
        $termByChooseDay = $this->getCurrentTerm($eventDay);

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
        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
        return $this->dataforjson;
    }

    public function campusTeacherList($params) {

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
        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
        return $this->dataforjson;
    }

    public function campusTimeList($params) {

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
        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
        return $this->dataforjson;
    }

    public function campusEventList($params , $isJson = true) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "GENERAL";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $roomId = isset($params["roomId"]) ? (int) $params["roomId"] : "";
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

        $termBychooseDay = $this->getCurrentTerm($eventDay);
        if(!SchoolDBAccess::displayPersonNameInGrid()){
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
        }else{
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
                , "CONCAT(C.CODE,'<br>',C.FIRSTNAME,' ',C.LASTNAME) AS TEACHER"
                , "D.NAME AS ROOM"
                , "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM"
            );
        }
        
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
                $SQL->where("A.SHORTDAY = ?",$shortday);
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
                    case "SCHOOLYEAR":
                        $SQL->where('E.GRADE_ID = ?', $academicObject->GRADE_ID);
                        $SQL->where('E.SCHOOL_YEAR = ?', $academicObject->SCHOOL_YEAR);
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

        //error_log($SQL->__toString());
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
                            
                            $data[$key]["EVENT"] = EVENT;
                            $data[$key]["EVENT"] .= "<br>";
                            $data[$key]["EVENT"] .= setShowText($value->EVENT);
                            $data[$key]["COLOR"] = "#FFF";
                            
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

        //@soda
        if($isJson) {
            $this->dataforjson = array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $data
            );
            return $this->dataforjson;
        } else {
            return $data;
        }
        //

    }
}

?>