<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/subject/SubjectTeacherDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/room/RoomDBAccess.php';
require_once 'models/app_university/academic/AcademicLevelDBAccess.php';
require_once 'models/training/TrainingDBAccess.php';
require_once setUserLoacalization();

class ScheduleDBAccess {

    public $dataforjson = null;
    public $data = array();

    CONST TABLE_SCHEDULE = "t_schedule";
    CONST TABLE_STAFF = "t_staff";
    CONST TABLE_GRADE = "t_grade";
    CONST TABLE_ROOM = "t_room";
    CONST TABLE_SUBJECT = "t_subject";
    CONST TABLE_TEACHER_SUBJECT = "t_teacher_subject";
    CONST TABLE_STUDENT_ASSIGNMENT = "t_student_assignment";
    CONST TABLE_SUBJECT_TEACHER_CLASS = "t_subject_teacher_class";
    CONST TABLE_SUBJECT_TEACHER_TRAINING = "t_subject_teacher_training";

    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {

        $this->DB_SCHOOLYEAR = AcademicDateDBAccess::getInstance();
        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();
        $this->DB_ROOM = RoomDBAccess::getInstance();
        $this->DB_SUBJECT = SubjectDBAccess::getInstance();
        $this->DB_STAFF = StaffDBAccess::getInstance();
        $this->DB_TRAINING = TrainingDBAccess::getInstance();
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelect()
    {
        return self::dbAccess()->select();
    }

    public static function findScheduleFromGuId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule', '*');

        if (is_numeric($Id))
        {
            $SQL->where('ID = ?', $Id);
        }
        else
        {
            $SQL->where('GUID = ?', $Id);
        }

        //echo $SQL->__toString();
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getScheduleDataFromGuId($Id)
    {

        $data = array();
        $result = self::findScheduleFromGuId($Id);

        if ($result)
        {

            $ROOM_OBJECT = $this->DB_ROOM->findRoomFromId($result->ROOM_ID);
            $SUBJECT_OBJECT = SubjectDBAccess::findSubjectFromId($result->SUBJECT_ID);
            $TEACHER_OBJECT = StaffDBAccess::findStaffFromId($result->TEACHER_ID);

            $data["STATUS"] = $result->STATUS;
            $data["EXTRA_START_DATE"] = showSeconds2Date($result->EXTRA_START_DATE);
            $data["EXTRA_END_DATE"] = showSeconds2Date($result->EXTRA_END_DATE);
            $data["SCHEDULE_TYPE"] = $result->SCHEDULE_TYPE;
            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);
            $data["START_TIME"] = secondToHour($result->START_TIME);
            $data["END_TIME"] = secondToHour($result->END_TIME);


            if ($result->SUBJECT_ID)
            {

                if ($SUBJECT_OBJECT)
                {
                    $data["EVENT"] = $SUBJECT_OBJECT->NAME;
                }
                else
                {
                    $data["EVENT"] = "?";
                }
            }
            else
            {
                $data["EVENT"] = setShowText($result->EVENT);
            }

            $data["SUBJECT_ID"] = $result->SUBJECT_ID;
            $data["TEACHER_ID"] = $result->TEACHER_ID;
            $data["ROOM_ID"] = $result->ROOM_ID;
            $data["ROOM_NAME"] = isset($ROOM_OBJECT->NAME) ? $ROOM_OBJECT->NAME : "---";

            if ($TEACHER_OBJECT)
            {
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data["TEACHER_NAME"] = $TEACHER_OBJECT->LASTNAME . " " . $TEACHER_OBJECT->FIRSTNAME;
                }
                else
                {
                    $data["TEACHER_NAME"] = $TEACHER_OBJECT->FIRSTNAME . " " . $TEACHER_OBJECT->LASTNAME;
                }
                $data["TEACHER_NAME"] .= " (" . $TEACHER_OBJECT->CODE . ")";
            }
            else
            {
                $data["TEACHER_NAME"] = "---";
            }

            $data["SHARED_SCHEDULE"] = $result->SHARED_SCHEDULE;
            $data["SHORTDAY_NAME"] = getShortdayName($result->SHORTDAY);

            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);
            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }

    public function loadClassEvent($Id)
    {

        $result = self::findScheduleFromGuId($Id);

        if ($result)
        {
            $o = array(
                "success" => true
                , "data" => $this->getScheduleDataFromGuId($Id)
            );
        }
        else
        {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function saveClassEvent($params)
    {

        $SAVEDATA = array();

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : false;
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : false;
        $shortDay = isset($params["shortday"]) ? addText($params["shortday"]) : false;
        $term = isset($params["term"]) ? addText($params["term"]) : false;
        $startTime = isset($params["START_TIME"]) ? addText($params["START_TIME"]) : false;
        $endTime = isset($params["END_TIME"]) ? addText($params["END_TIME"]) : false;
        $scheduleType = isset($params["SCHEDULE_TYPE"]) ? addText($params["SCHEDULE_TYPE"]) : false;
        $eventName = isset($params["EVENT"]) ? addText($params["EVENT"]) : false;
        $subjectId = isset($params["SUBJECT"]) ? addText($params["SUBJECT"]) : false;
        $roomId = isset($params["ROOM_HIDDEN"]) ? addText($params["ROOM_HIDDEN"]) : false;
        $teacherId = isset($params["TEACHER_HIDDEN"]) ? addText($params["TEACHER_HIDDEN"]) : false;
        $shared = isset($params["SHARED_SCHEDULE"]) ? addText($params["SHARED_SCHEDULE"]) : false;

        $startDate = isset($params["START_DATE"]) ? addText($params["START_DATE"]) : false;
        $endDate = isset($params["END_DATE"]) ? addText($params["END_DATE"]) : false;

        $target = isset($params["target"]) ? addText($params["target"]) : false;
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : false;

        $facette = self::findScheduleFromGuId($scheduleId);

        $errors = array();

        if ($facette)
        {
            $academicId = $facette->ACADEMIC_ID;
            $shortDay = $facette->SHORTDAY;
            $term = $facette->TERM;
        }

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $_START_TIME = timeStrToSecond($startTime);
        $_END_TIME = timeStrToSecond($endTime);

        $ERROR_START_TIME_IS_INCORRECT = false;
        $ERROR_END_TIME_IS_INCORRECT = false;
        $ERROR_START_TIME_END_TIME = false;
        $ERROR_TIME_HAS_BEEN_USED = false;
        $ERROR_TEACHER_NAME = false;
        $ERROR_ROOM_NAME = false;
        $ERROR_SUBJECT = false;
        $ERROR_EVENT = false;
        $ERROR_START_DATE_END_DATE_TERM = false;

        if (!$_START_TIME)
            $ERROR_START_TIME_IS_INCORRECT = true;
        if (!$_END_TIME)
            $ERROR_START_TIME_IS_INCORRECT = true;

        if ($_START_TIME > $_END_TIME)
        {
            $ERROR_START_TIME_END_TIME = true;
        }

        if ($academicId && !$trainingId)
        {
            $ERROR_TIME_HAS_BEEN_USED = $this->checkStartTimeANDEndTime(
                    $_START_TIME
                    , $_END_TIME
                    , $shortDay
                    , $academicId
                    , $term
                    , $scheduleId
                    , $subjectId
                    , $shared
            );
        }

        if (!$academicId && $trainingId)
        {
            $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);
            if ($startDate && $endDate)
            {
                $checkInTerm = TrainingDBAccess::checkTrainingStartDateEnddate($startDate, $endDate, $trainingId);
                if (!$checkInTerm)
                {
                    $ERROR_START_DATE_END_DATE_TERM = true;
                }
            }
            $ERROR_TIME_HAS_BEEN_USED = $this->checkStartTimeANDEndTimeTraining(
                    $_START_TIME
                    , $_END_TIME
                    , $shortDay
                    , $trainingId
                    , $scheduleId
                    , $startDate
                    , $endDate
            );
        }

        $SAVEDATA["START_TIME"] = timeStrToSecond($startTime);
        $SAVEDATA["END_TIME"] = timeStrToSecond($endTime);
        $SAVEDATA["SUBJECT_ID"] = $subjectId;
        $SAVEDATA["ROOM_ID"] = $roomId;
        $SAVEDATA["EVENT"] = $eventName;
        if ($startDate && $endDate)
        {
            $SAVEDATA["START_DATE"] = setDate2DB($startDate);
            $SAVEDATA["END_DATE"] = setDate2DB($endDate);
        }
        if ($trainingId)
        {//@veasna
            if ($startDate && $endDate)
            {
                $SAVEDATA["START_DATE"] = setDate2DB($startDate);
                $SAVEDATA["END_DATE"] = setDate2DB($endDate);
            }
            else
            {
                $SAVEDATA["START_DATE"] = $trainingObject->START_DATE;
                $SAVEDATA["END_DATE"] = $trainingObject->END_DATE;
            }
        }

        $SAVEDATA["SHARED_SCHEDULE"] = $shared;

        if (isset($params["EXTRA_START_DATE"]))
            $SAVEDATA['EXTRA_START_DATE'] = strtotime(setDate2DB($params["EXTRA_START_DATE"]));

        if (isset($params["EXTRA_END_DATE"]))
            $SAVEDATA['EXTRA_END_DATE'] = strtotime(setDate2DB($params["EXTRA_END_DATE"]));

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if ($facette)
        {
            switch ($scheduleType)
            {
                case 1:

                    $SAVEDATA["TEACHER_ID"] = $teacherId;

                    if (isset($facette->SUBJECT))
                    {
                        if (!$teacherId)
                            $ERROR_TEACHER_NAME = true;
                        if (!$roomId)
                            $ERROR_ROOM_NAME = true;
                    }

                    if (!$subjectId)
                        $ERROR_SUBJECT = true;
                    break;
                case 2:
                    if (!$eventName)
                        $ERROR_EVENT = true;
                    break;
            }

            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            if ($ERROR_TIME_HAS_BEEN_USED)
            {
                $errors["START_TIME"] = true;
                $errors["END_TIME"] = true;
            }
            if ($ERROR_START_TIME_IS_INCORRECT)
            {
                $errors["START_TIME"] = true;
            }
            if ($ERROR_END_TIME_IS_INCORRECT)
            {
                $errors["END_TIME"] = true;
            }
            if ($ERROR_TEACHER_NAME)
            {
                $errors["TEACHER_NAME"] = true;
            }
            if ($ERROR_ROOM_NAME)
            {
                $errors["ROOM_NAME"] = true;
            }
            if ($ERROR_SUBJECT)
            {
                $errors["SUBJECT"] = true;
            }
            if ($ERROR_EVENT)
            {
                $errors["EVENT"] = true;
            }

            ///////////////////////////// Update....
            if (!$errors)
            {

                if (timeStrToSecond($startTime) && timeStrToSecond($endTime))
                {
                    $WHERE = self::dbAccess()->quoteInto("GUID = ?", $scheduleId);
                    self::dbAccess()->update('t_schedule', $SAVEDATA, $WHERE);
                }

                if (!$facette->TRAINING_ID)
                {
                    SubjectTeacherDBAccess::addSubjectTeacherClassTerm(
                            $subjectId
                            , $teacherId
                            , $academicId
                            , $term
                    );
                }
                else
                {
                    SubjectTeacherDBAccess::addSubjectTeacherTraining(
                            $subjectId
                            , $teacherId
                            , $facette->TRAINING_ID
                    );
                }
            }
        }
        else
        {
            switch ($scheduleType)
            {
                case 1:
                    if (!$subjectId)
                        $ERROR_SUBJECT = true;
                    break;
                case 2:
                    if (!$eventName)
                        $ERROR_EVENT = true;
                    break;
            }

            $SAVEDATA["CODE"] = createCode();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA["GUID"] = $scheduleId;
            $SAVEDATA["ACADEMIC_ID"] = $academicId;
            $SAVEDATA["SHORTDAY"] = $shortDay;
            $SAVEDATA["TRAINING_ID"] = $trainingId;
            if ($trainingId)
            {//@veasna
                if ($startDate)
                    $SAVEDATA["START_DATE"] = setDate2DB($startDate);
                if ($endDate)
                    $SAVEDATA["END_DATE"] = setDate2DB($endDate);
            }

            if (isset($academicObject))
            {
                if (!$trainingId)
                {
                    $termDateObject = AcademicDBAccess::getDateBySchoolTerm($academicObject->ID, $term);
                    $SAVEDATA["START_DATE"] = $termDateObject->START_DATE;
                    $SAVEDATA["END_DATE"] = $termDateObject->END_DATE;
                    $SAVEDATA["SCHOOLYEAR_ID"] = $academicObject->SCHOOL_YEAR;
                    $SAVEDATA["EDUCATION_SYSTEM"] = $academicObject->EDUCATION_SYSTEM;
                    $SAVEDATA["GRADE_ID"] = $academicObject->GRADE_ID;
                }
            }

            $SAVEDATA["TERM"] = $term;
            $SAVEDATA["SCHEDULE_TYPE"] = $scheduleType;

            if ($ERROR_TIME_HAS_BEEN_USED)
            {
                $errors["START_TIME"] = true;
                $errors["END_TIME"] = true;
            }
            if ($ERROR_START_DATE_END_DATE_TERM)
            {
                $errors["START_DATE"] = true;
                $errors["END_DATE"] = true;
            }

            if ($ERROR_START_TIME_IS_INCORRECT)
            {
                $errors["START_TIME"] = true;
            }
            if ($ERROR_END_TIME_IS_INCORRECT)
            {
                $errors["END_TIME"] = true;
            }
            if ($ERROR_TEACHER_NAME)
            {
                $errors["TEACHER_NAME"] = true;
            }
            if ($ERROR_ROOM_NAME)
            {
                $errors["ROOM_NAME"] = true;
            }
            if ($ERROR_SUBJECT)
            {
                $errors["SUBJECT"] = true;
            }
            if ($ERROR_EVENT)
            {
                $errors["EVENT"] = true;
            }

            if (!$errors)
            {
                self::dbAccess()->insert('t_schedule', $SAVEDATA);
            }
        }

        if ($ERROR_START_TIME_END_TIME)
        {

            $errors["START_TIME"] = ERROR;
            $errors["END_TIME"] = ERROR;
        }

        if ($ERROR_TIME_HAS_BEEN_USED)
        {
            $errors["START_TIME"] = TIME_HAS_BEEN_USED;
            $errors["END_TIME"] = TIME_HAS_BEEN_USED;
        }
        if ($ERROR_START_DATE_END_DATE_TERM)
        {
            $errors["START_DATE"] = ERROR;
            $errors["END_DATE"] = ERROR;
        }

        if ($ERROR_START_TIME_IS_INCORRECT)
        {
            $errors["START_TIME"] = TIME_IS_INCORRECT;
        }
        if ($ERROR_END_TIME_IS_INCORRECT)
        {
            $errors["END_TIME"] = TIME_IS_INCORRECT;
        }
        if ($ERROR_TEACHER_NAME)
        {
            $errors["TEACHER_NAME"] = THIS_FIELD_IS_REQIRED;
        }
        if ($ERROR_ROOM_NAME)
        {
            $errors["ROOM_NAME"] = THIS_FIELD_IS_REQIRED;
        }
        if ($ERROR_SUBJECT)
        {
            $errors["SUBJECT"] = THIS_FIELD_IS_REQIRED;
        }
        if ($ERROR_EVENT)
        {
            $errors["EVENT"] = THIS_FIELD_IS_REQIRED;
        }

        self::updateSharedSchedule(self::findScheduleFromGuId($scheduleId));

        if ($errors)
        {
            return array(
                "success" => false
                , "errors" => $errors
            );
        }
        else
        {
            return array(
                "success" => true
                , "errors" => $errors
            );
        }
    }

    public static function loadSQLClassEvents($startTime, $endTime, $shortDay, $chooseId, $term, $single = true, $type = false, $teacherId = false, $startDate = false, $endDate = false)
    {

        switch (strtoupper($type))
        {
            case "GENERAL":
                $params["academicId"] = $chooseId;
                $params["target"] = "GENERAL";
                break;
            case "TRAINING":
                $params["trainingId"] = $chooseId;
                $params["target"] = "TRAINING";
                if ($startDate)
                    $params["startDate"] = $startDate;
                if ($endDate)
                    $params["endDate"] = $endDate;
                break;
            case "CREDIT_SCHOOLYEAR":
                $params["academicId"] = '';
                $params["target"] = "GENERAL";
                break;
        }

        $params["starttime"] = $startTime;
        $params["endtime"] = $endTime;
        $params["shortday"] = $shortDay;
        $params["term"] = $term;
        $params["single"] = $single;
        if ($teacherId)
            $params["teacherId"] = $teacherId;
        return self::getSQLClassEvents($params);
    }

    /**
     * Get groupId/classId of each student as credit education systems
     * 
     * @author Math Man 05.02.2014
     * @param mixed $params
     */
    public static function getGroupsByStudentId($params)
    {

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from("t_student_schoolyear_subject", array("CLASS_ID"));

        if ($studentId)
            $SQL->where("STUDENT_ID = ?", $studentId);

        if ($schoolyearId)
            $SQL->where("SCHOOLYEAR_ID = ?", $schoolyearId);

        $SQL->order("CLASS_ID");
        //error_log($SQL); exit();
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getSQLClassEvents($params)
    {

        $single = isset($params["single"]) ? $params["single"] : "";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $term = isset($params["term"]) ? addText($params["term"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $shortday = isset($params["shortday"]) ? addText($params["shortday"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $roomId = isset($params["roomId"]) ? (int) $params["roomId"] : "";
        $status = isset($params["status"]) ? addText($params["status"]) : "";

        $starttime = isset($params["starttime"]) ? addText($params["starttime"]) : "";
        $endtime = isset($params["endtime"]) ? addText($params["endtime"]) : "";

        $startDate = isset($params["startDate"]) ? $params["startDate"] : ""; //@veasna
        $endDate = isset($params["endDate"]) ? $params["endDate"] : ""; //@veasna
        $checkDay = isset($params["checkDay"]) ? $params["checkDay"] : ""; //@veasna

        $target = isset($params["target"]) ? addText($params["target"]) : "GENERAL";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : ''; //@new... veasna
        $groupIds = isset($params["groupIds"]) ? addText($params["groupIds"]) : ''; //@Man

        if ($academicId && !$trainingId)
        {
            $SELECT_DATA = array(
                "A.GUID AS GUID"
                , "A.GRADE_ID AS GRADE_ID"
                , "A.EXTRA_START_DATE AS EXTRA_START_DATE"
                , "A.EXTRA_END_DATE AS EXTRA_END_DATE"
                , "A.EDUCATION_SYSTEM AS EDUCATION_SYSTEM"
                , "A.SHARED_SCHEDULE AS SHARED_SCHEDULE"
                , "A.SHARED_FROM AS SHARED_FROM"
                , "A.ID AS SCHEDULE_ID"
                , "A.TRAINING_ID AS TRAINING_ID"
                , "A.ACADEMIC_ID AS ACADEMIC_ID"
                , "A.SUBJECT_ID AS SUBJECT_ID"
                , "A.TEACHER_ID AS TEACHER_ID"
                , "A.ROOM_ID AS ROOM_ID"
                , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                , "A.STATUS AS SCHEDULE_STATUS"
                , "A.CODE AS SCHEDULE_CODE"
                , "A.START_TIME AS START_TIME"
                , "A.END_TIME AS END_TIME"
                , "A.EVENT AS EVENT"
                , "A.TERM AS TERM"
                , "A.SHORTDAY AS SHORTDAY"
                , "A.DESCRIPTION AS DESCRIPTION"
                , "B.NAME AS SUBJECT_NAME"
                , "B.SHORT AS SUBJECT_SHORT"
                , "B.COLOR AS SUBJECT_COLOR"
                , "CONCAT(C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
                , "D.SHORT AS ROOM_SHORT"
                , "D.NAME AS ROOM"
                , "E.NAME AS CLASS_NAME"
            );
        }

        if (!$academicId && $trainingId)
        {
            $SELECT_DATA = array(
                "A.GUID AS GUID"
                , "A.ID AS SCHEDULE_ID"
                , "A.TRAINING_ID AS TRAINING_ID"
                , "A.SUBJECT_ID AS SUBJECT_ID"
                , "A.TEACHER_ID AS TEACHER_ID"
                , "A.ROOM_ID AS ROOM_ID"
                , "A.SHARED_SCHEDULE AS SHARED_SCHEDULE"
                , "A.SHARED_FROM AS SHARED_FROM"
                , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
                , "A.STATUS AS SCHEDULE_STATUS"
                , "A.SHORTDAY AS SHORTDAY"
                , "A.CODE AS SCHEDULE_CODE"
                , "A.START_TIME AS START_TIME"
                , "A.END_TIME AS END_TIME"
                , "A.EVENT AS EVENT"
                , "A.TERM AS TERM"
                , "A.DESCRIPTION AS DESCRIPTION"
                , "B.NAME AS SUBJECT_NAME"
                , "B.SHORT AS SUBJECT_SHORT"
                , "B.COLOR AS SUBJECT_COLOR"
                , "CONCAT(C.CODE,'<br>',C.LASTNAME,' ',C.FIRSTNAME) AS TEACHER"
                , "D.SHORT AS ROOM_SHORT"
                , "D.NAME AS ROOM"
                , "E.NAME AS TRAINING_NAME"
            );
        }

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_schedule"), $SELECT_DATA);
        $SQL->joinLeft(array('B' => self::TABLE_SUBJECT), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => self::TABLE_STAFF), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => self::TABLE_ROOM), 'A.ROOM_ID=D.ID', array());

        if ($studentId)
            $SQL->joinLeft(array('F' => "t_student_schoolyear_subject"), 'A.SUBJECT_ID=F.SUBJECT_ID', array());

        if ($academicId && !$trainingId)
        {
            $academicObject = AcademicDBAccess::findGradeFromId($academicId);
            $SQL->joinLeft(array('E' => self::TABLE_GRADE), 'A.ACADEMIC_ID=E.ID', array());

            if ($academicId)
                $SQL->where('A.ACADEMIC_ID = ?', $academicObject->ID);
            if ($schoolyearId)
                $SQL->where('A.SCHOOLYEAR_ID = ?', $schoolyearId);

            if (!$teacherId)
            {
                if ($term)
                    $SQL->where('A.TERM = ?', $term);
            }
        }

        if (!$academicId && $trainingId)
        {
            $SQL->joinLeft(array('E' => 't_training'), 'A.TRAINING_ID=E.ID', array());
            $SQL->joinLeft(array('F' => 't_training'), 'E.PARENT=F.ID', array());

            if ($trainingId)
            {
                $SQL->where("A.TRAINING_ID='" . $trainingId . "'");
                if ($startDate)
                {
                    $SQL->where("A.START_DATE='" . $startDate . "'");
                }
                if ($endDate)
                {
                    $SQL->where("A.END_DATE='" . $endDate . "'");
                }
                if ($checkDay)
                {
                    $SQL->where("A.START_DATE >='" . $checkDay . "' AND A.END_DATE >='" . $checkDay . "'");
                }
            }
            else
            {
                $SQL->where("NOW()<=F.EXTRA_END_DATE");
            }
        }

        if ($shortday)
            $SQL->where('A.SHORTDAY = ?', $shortday);

        if ($teacherId)
            $SQL->where('A.TEACHER_ID = ?', $teacherId);

        if ($roomId)
            $SQL->where('A.ROOM_ID = ?', $roomId);

        if ($subjectId)
            $SQL->where('A.SUBJECT_ID = ?', $subjectId);

        if ($status)
            $SQL->where("A.STATUS=" . $status . "");

        if ($starttime)
            $SQL->where("A.START_TIME=" . $starttime . "");

        if ($endtime)
            $SQL->where("A.END_TIME=" . $endtime . "");

        if ($groupIds) //@Man
            $SQL->where("A.GROUP_IDS IN (?)", $groupIds);

        $SQL->group("A.START_TIME");
        $SQL->group("A.END_TIME");
        $SQL->order('A.START_TIME');

        //error_log($SQL->__toString());
        if ($single)
        {

            $resultRows = self::dbAccess()->fetchRow($SQL);
        }
        else
        {
            $resultRows = self::dbAccess()->fetchAll($SQL);
        }

        return $resultRows;
    }

    public function loadClassEvents($params, $isJson = true)
    {

        $data = array();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        ///@veasna
        $checkAcademicId = '';

        if ($academicId)
        {
            //@veasna
            $type = "GENERAL";
            $academicObject = AcademicDBAccess::findGradeFromId($academicId);
            $params["schoolyearId"] = $academicObject->SCHOOL_YEAR;
            if ($academicObject->EDUCATION_SYSTEM)
            {
                switch ($academicObject->OBJECT_TYPE)
                {
                    case "SUBJECT":
                        $params["academicId"] = $academicObject->ID;
                        $checkAcademicId = '';
                        break;
                    case "CLASS":
                        $params["academicId"] = $academicObject->PARENT;
                        $checkAcademicId = $academicObject->ID;
                        break;
                    case "SCHOOLYEAR": //@Man 05.02.2014
                        $params["academicId"] = '';
                        $type = "CREDIT_SCHOOLYEAR";
                        $groupIds = array();
                        $result = self::getGroupsByStudentId($params);
                        if ($result)
                            foreach ($result as $v)
                                $groupIds[] = $v->CLASS_ID;
                        $params['groupIds'] = $groupIds;
                        break;
                }
            } else
            {
                $params["academicId"] = $academicObject->ID;
            }
            $chooseId = "ACADEMIC_ID";
        }
        elseif ($trainingId)
        {
            $chooseId = "TRAINING_ID";
            $type = "TRAINING";
        }

        ////////////////////////////////////////////////////////////////////////
        //@veasna check link credit education system link
        ////////////////////////////////////////////////////////////////////////
        $LINKED_SCHEDULE_CREDIT_DATA = array();
        if ($checkAcademicId)
        {
            $listLinkedScheduleCredit = self::findLinkedScheduleAcademicByAcademicId($checkAcademicId);
            foreach ($listLinkedScheduleCredit as $schedule)
            {
                $LINKED_SCHEDULE_CREDIT_DATA[] = $schedule->SCHEDULE_ID;
            }
        }
        //////////////////////////

        $DISPLAY_SUBJECT = Zend_Registry::get('SCHOOL')->SUBJECT_DISPLAY ? "SUBJECT_SHORT" : "SUBJECT_NAME";

        $resultRows = self::getSQLClassEvents($params);

        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

                $data[$i]["ID"] = $value->GUID;
                if (self::getCountAssignedGroup($value->SCHEDULE_ID))
                {
                    $data[$i]["HEIGHT"] = 110;
                }
                else
                {
                    $data[$i]["HEIGHT"] = 85;
                }
                $data[$i]["STATUS"] = $value->SCHEDULE_STATUS;
                $data[$i]["CODE"] = setShowText($value->SCHEDULE_CODE);
                $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                $data[$i]["EXTRA_START_DATE"] = secondToHour($value->START_TIME);
                $data[$i]["EXTRA_END_DATE"] = secondToHour($value->END_TIME);
                if ($i > 0)
                    if ($data[$i]["TIME"] == $data[$i - 1]["TIME"])
                    {
                        $i--;
                    }
                $MO_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "MO", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["MO_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($MO_OBJECT);
                $data[$i]["MO"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["MO_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["MO_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["MO_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["MO_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["MO_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["MO_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $TU_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "TU", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["TU_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($TU_OBJECT);
                $data[$i]["TU"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TU_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TU_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TU_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TU_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TU_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TU_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $WE_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "WE", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["WE_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($WE_OBJECT);
                $data[$i]["WE"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["WE_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["WE_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["WE_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["WE_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["WE_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["WE_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $TH_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "TH", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["TH_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($TH_OBJECT);
                $data[$i]["TH"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TH_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TH_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TH_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TH_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TH_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["TH_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $FR_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "FR", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["FR_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($FR_OBJECT);
                $data[$i]["FR"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["FR_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["FR_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["FR_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["FR_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["FR_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["FR_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $SA_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "SA", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["SA_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($SA_OBJECT);
                $data[$i]["SA"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SA_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SA_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SA_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SA_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SA_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SA_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $SU_OBJECT = self::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "SU", $value->$chooseId, $value->TERM, true, $type);

                $data[$i]["SU_GROUP"] = ($trainingId) ? "" : self::checkDoubleGroupEvent($SU_OBJECT);
                $data[$i]["SU"] = self::displayEvent("DAY_EVENT", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SU_GUID"] = self::displayEvent("DAY_GUID", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SU_EVENT"] = self::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SU_COLOR"] = self::displayEvent("DAY_COLOR", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SU_COLOR_FONT"] = self::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SU_DESCRIPTION"] = self::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                $data[$i]["SU_DESCRIPTION_EX"] = self::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        //@soda
        if ($isJson)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        }
        //
    }

    public function deleteDayClassEvent($Id)
    {

        $facette = self::findScheduleFromGuId($Id);
        if ($facette)
        {
            self::dbAccess()->delete('t_schedule', array('GUID = ? ' => $Id));
            if (!$facette->SHARED_FROM)
            {
                self::dbAccess()->delete('t_link_schedule_academic', array(
                    'SCHEDULE_ID = ? ' => $facette->SHARED_FROM)
                );
            }
            else
            {
                self::dbAccess()->delete('t_link_schedule_academic', array(
                    'SCHEDULE_ID = ? ' => $facette->SHARED_FROM
                    , 'ACADEMIC_ID = ? ' => $facette->ACADEMIC_ID)
                );
            }

            self::deleteTecherFromAcademic($facette);
        }
    }

    public function jsonDeleteDayClassEvent($Id)
    {

        $this->deleteDayClassEvent($Id);

        return array(
            "success" => true
        );
    }

    public function findMaxTime($shortday, $term, $academicId)
    {

        $SQL = "SELECT DISTINCT END_TIME AS TIME";
        $SQL .= " FROM t_schedule";
        $SQL .= " WHERE";
        $SQL .= " ACADEMIC_ID = '" . $academicId . "'";
        $SQL .= " AND SHORTDAY = '" . $shortday . "'";
        $SQL .= " AND TERM = '" . $term . "'";
        $SQL .= " ORDER BY END_TIME DESC";
        $SQL .= " LIMIT 0,1";

        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? secondToHour($result->TIME) : "06:00";
    }

    public function releaseClassEvent($params)
    {

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : false;
        $facette = self::findScheduleFromGuId($scheduleId);
        $newStatus = 0;
        switch ($facette->STATUS)
        {
            case 0:
                $newStatus = 1;
                $SAVEDATA["STATUS"] = 1;
                $SAVEDATA["ENABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA["ENABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("GUID = ?", $scheduleId);
                self::dbAccess()->update('t_schedule', $SAVEDATA, $WHERE);
                break;
            case 1:
                $newStatus = 0;
                $SAVEDATA["STATUS"] = 0;
                $SAVEDATA["DISABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA["DISABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("GUID = ?", $scheduleId);
                self::dbAccess()->update('t_schedule', $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true, "status" => $newStatus);
    }

    protected function checkUseRooms($scheduleObject)
    {

        $CHECK_DATA = array();

        if ($scheduleObject->TRAINING_ID)
        {
            $objectTraining = TrainingDBAccess::findTrainingFromId($scheduleObject->TRAINING_ID);

            if ($objectTraining)
            {

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => 't_schedule'), array("*"));
                $SQL->where("START_TIME <=" . $scheduleObject->START_TIME . " AND END_TIME >=" . $scheduleObject->END_TIME . "");
                $SQL->where("TERM= '" . $objectTraining->TERM . "'");
                $SQL->where("SHORTDAY= '" . $scheduleObject->SHORTDAY . "'");
                if ($scheduleObject->SUBJECT_ID)
                {
                    $SQL->where("SUBJECT_ID= '" . $scheduleObject->SUBJECT_ID . "'");
                }
                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchAll($SQL);
                foreach ($result as $value)
                {
                    if ($scheduleObject->END_TIME == $value->END_TIME)
                    {
                        $CHECK_DATA[$value->ROOM_ID] = $value->ROOM_ID;
                    }
                }
            }
        }
        else
        {

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_schedule'), array("*"));
            $SQL->where("START_TIME <=" . $scheduleObject->START_TIME . " AND END_TIME >=" . $scheduleObject->END_TIME . "");
            $SQL->where("TERM= '" . $scheduleObject->TERM . "'");
            $SQL->where("SHORTDAY= '" . $scheduleObject->SHORTDAY . "'");
            $SQL->where("SCHOOLYEAR_ID= '" . $scheduleObject->SCHOOLYEAR_ID . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
            foreach ($result as $value)
            {
                if ($scheduleObject->START_TIME == $value->START_TIME)
                {
                    if (!$scheduleObject->SHARED_SCHEDULE && !$value->SHARED_SCHEDULE)
                    {
                        if ($scheduleObject->END_TIME == $value->END_TIME)
                        {
                            $CHECK_DATA[$value->ROOM_ID] = $value->ROOM_ID;
                        }
                    }
                }
            }
        }

        return $CHECK_DATA;
    }

    //@veasna
    public function availableGridRoom($params)
    {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : '';
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $facette = self::findScheduleFromGuId($scheduleId);

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => self::TABLE_ROOM), array("*"));
        if ($globalSearch)
        {
            $SQL->where("A.NAME LIKE '" . $globalSearch . "%'");
            $SQL->Orwhere("A.SHORT LIKE '" . $globalSearch . "%'");
        }
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $CHECK_DATA = $this->checkUseRooms($facette);

        $data = array();
        $i = 0;

        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["SHORT"] = setShowText($value->SHORT);
                $data[$i]["MAX_COUNT"] = $value->MAX_COUNT ? $value->MAX_COUNT : "---";
                $data[$i]["ROOM_SIZE"] = $value->ROOM_SIZE ? $value->ROOM_SIZE : "---";
                $data[$i]["ROOM"] = setShowText($value->NAME);
                $data[$i]["BUILDING"] = setShowText($value->BUILDING);
                $data[$i]["FLOOR"] = setShowText($value->FLOOR);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                $data[$i]["STATUS"] = $value->STATUS;

                if (!in_array($value->ID, $CHECK_DATA))
                {
                    $data[$i]['AVAILABLE_STATUS'] = iconYESNO(1);
                    $data[$i]['AVAILABLE'] = 1;
                }
                else
                {
                    $data[$i]['AVAILABLE_STATUS'] = iconYESNO(0);
                    $data[$i]['AVAILABLE'] = 0;
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    //

    public function availableRoom($params)
    {

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : false;
        $parentId = isset($params["node"]) ? addText($params["node"]) : 0;

        $facette = self::findScheduleFromGuId($scheduleId);

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => self::TABLE_ROOM), array("*"));
        $SQL->where("A.PARENT = ?", $parentId);
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $CHECK_DATA = $this->checkUseRooms($facette);

        $data = array();
        $i = 0;

        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

                if (RoomDBAccess::checkChild($value->ID))
                {

                    if ($value->SHORT)
                    {
                        $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME);
                    }
                    else
                    {
                        $data[$i]['text'] = stripslashes($value->NAME);
                    }
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                }
                else
                {

                    if ($shared)
                    {
                        if ($value->SHORT)
                        {
                            $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME);
                        }
                        else
                        {
                            $data[$i]['text'] = stripslashes($value->NAME);
                        }
                        $data[$i]['id'] = "" . $value->ID . "";
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['leaf'] = true;
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                    }
                    else
                    {
                        if (!in_array($value->ID, $CHECK_DATA))
                        {
                            if ($value->SHORT)
                            {
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME);
                            }
                            else
                            {
                                $data[$i]['text'] = stripslashes($value->NAME);
                            }
                            $data[$i]['id'] = "" . $value->ID . "";
                            $data[$i]['cls'] = "nodeTextBlue";
                            $data[$i]['leaf'] = true;
                            $data[$i]['iconCls'] = "icon-application_form_magnify";
                        }
                        else
                        {
                            if ($value->SHORT)
                            {
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME);
                            }
                            else
                            {
                                $data[$i]['text'] = stripslashes($value->NAME);
                            }
                            $data[$i]['id'] = "" . $value->ID . "";
                            $data[$i]['cls'] = "nodeTextBlue";
                            $data[$i]['isUsed'] = "1"; //@veasna
                            $data[$i]['leaf'] = true;
                            $data[$i]['iconCls'] = "icon-cancel";  //@veasna
                        }
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    protected function checkUsedTeachers($scheduleObject)
    {

        ///@veasna
        $CHECK_DATA = array();

        if ($scheduleObject->TRAINING_ID)
        {
            $objectTraining = TrainingDBAccess::findTrainingFromId($scheduleObject->TRAINING_ID);

            if ($objectTraining)
            {

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => 't_schedule'), array("*"));
                $SQL->where("START_TIME <=" . $scheduleObject->START_TIME . " AND END_TIME >=" . $scheduleObject->END_TIME . "");
                $SQL->where("TERM= '" . $objectTraining->TERM . "'");
                $SQL->where("SHORTDAY= '" . $scheduleObject->SHORTDAY . "'");
                if ($scheduleObject->SUBJECT_ID)
                {
                    $SQL->where("SUBJECT_ID= '" . $scheduleObject->SUBJECT_ID . "'");
                }
                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchAll($SQL);
                foreach ($result as $value)
                {
                    if ($scheduleObject->END_TIME == $value->END_TIME)
                    {
                        $CHECK_DATA[$value->TEACHER_ID] = $value->TEACHER_ID;
                    }
                }
            }
        }
        else
        {

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_schedule'), array("*"));
            $SQL->where("START_TIME <=" . $scheduleObject->START_TIME . " AND END_TIME >=" . $scheduleObject->END_TIME . "");
            $SQL->where("TERM= '" . $scheduleObject->TERM . "'");
            $SQL->where("SHORTDAY= '" . $scheduleObject->SHORTDAY . "'");
            $SQL->where("SCHOOLYEAR_ID= '" . $scheduleObject->SCHOOLYEAR_ID . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
            foreach ($result as $value)
            {
                if ($scheduleObject->START_TIME == $value->START_TIME)
                {
                    if (!$scheduleObject->SHARED_SCHEDULE && !$value->SHARED_SCHEDULE)
                    {
                        if ($scheduleObject->END_TIME == $value->END_TIME)
                        {
                            $CHECK_DATA[$value->TEACHER_ID] = $value->TEACHER_ID;
                        }
                    }
                }
            }
        }

        return $CHECK_DATA;
    }

    protected function checkAssignedSubjectTeacherClass($subjectId, $teacherId, $academicId, $term)
    {

        $SQL = "SELECT DISTINCT COUNT(*) AS C";
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE 1=1";
        if ($subjectId)
            $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        if ($teacherId)
            $SQL .= " AND TEACHER = '" . $teacherId . "'";
        if ($academicId)
            $SQL .= " AND ACADEMIC = '" . $academicId . "'";
        if ($term)
            $SQL .= " AND GRADINGTERM = '" . $term . "'";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkAssignedSubjectTeacherTraining($subjectId, $teacherId, $trainingId)
    {

        $SQL = "SELECT DISTINCT COUNT(*) AS C";
        $SQL .= " FROM t_subject_teacher_training";
        $SQL .= " WHERE 1=1";
        if ($subjectId)
            $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        if ($teacherId)
            $SQL .= " AND TEACHER = '" . $teacherId . "'";
        if ($trainingId)
            $SQL .= " AND TRAINING = '" . $trainingId . "'";

        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function availableTeacher($params)
    {

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : false;
        $facette = self::findScheduleFromGuId($scheduleId);
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SELECT_DATA = array(
            "A.ID AS ID"
            , "A.CODE AS CODE"
            , "A.FIRSTNAME AS FIRSTNAME"
            , "A.LASTNAME AS LASTNAME"
            , "C.NAME AS SUBJECT_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => self::TABLE_STAFF), $SELECT_DATA);
        $SQL->joinRight(array('B' => self::TABLE_TEACHER_SUBJECT), 'A.ID=B.TEACHER', array());
        $SQL->joinRight(array('C' => self::TABLE_SUBJECT), 'B.SUBJECT=C.ID', array());

        if ($facette->SUBJECT_ID)
            $SQL->where("C.ID ='" . $facette->SUBJECT_ID . "'");

        if ($globalSearch)
        {
            $SEARCH = " ((A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        //error_log($SQL->__toString());

        $resultRows = self::dbAccess()->fetchAll($SQL);

        $CHECK_USED_TEACHERS = $this->checkUsedTeachers($facette);

        $data = array();
        $i = 0;

        if ($resultRows)
            foreach ($resultRows as $value)
            {

                if ($value->ID)
                {

                    if ($facette->SHARED_SCHEDULE)
                    {
                        $status = 1;
                        $AVAILABLE = true;
                    }
                    else
                    {
                        if (in_array($value->ID, $CHECK_USED_TEACHERS))
                        {
                            if ($facette->TEACHER_ID == $value->ID)
                            {
                                $status = 1;
                                $AVAILABLE = true;
                            }
                            else
                            {
                                $status = 3;
                                $AVAILABLE = false;
                            }
                        }
                        else
                        {
                            $status = 2;
                            $AVAILABLE = true;
                        }
                    }

                    $data[$i]["AVAILABLE"] = $AVAILABLE;
                    $data[$i]["AVAILABLE_STATUS"] = iconTeacherInClassSchedule($status);

                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    if (!SchoolDBAccess::displayPersonNameInGrid())
                    {
                        $data[$i]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                    }
                    else
                    {
                        $data[$i]["FULL_NAME"] = $value->FIRSTNAME . " " . $value->LASTNAME;
                    }
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;

                    $i++;
                }
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function checkCountEvents($subjectId, $academicId, $term)
    {
        return self::checkSubjectInSchedule($subjectId, $academicId, $term);
    }

    public function deleteOldSubject($params)
    {

        $SAVEDATA = array();
        $Id = $params["scheduleId"] ? addText($params["scheduleId"]) : false;
        $subjectId = $params["subjectId"] ? addText($params["subjectId"]) : false;
        $academicId = $params["academicId"] ? addText($params["academicId"]) : false;
        $term = $params["term"] ? addText($params["term"]) : false;

        $facette = self::findScheduleFromGuId($Id);

        if ($facette)
        {
            if ($subjectId != $facette->SUBJECT_ID)
            {
                $WHERE = array();
                $SAVEDATA["TEACHER_ID"] = "";
                $WHERE[] = self::dbAccess()->quoteInto("SUBJECT_ID = ?", $subjectId);
                $WHERE[] = self::dbAccess()->quoteInto("ACADEMIC_ID = ?", $academicId);
                $WHERE[] = self::dbAccess()->quoteInto("TERM = ?", $term);
                self::dbAccess()->update('t_schedule', $SAVEDATA, $WHERE);
            }
        }
    }

    public function jsonDeleteAllClassEventByDay($params)
    {

        $shortday = $params["shortday"] ? addText($params["shortday"]) : false;
        $academicId = $params["academicId"] ? addText($params["academicId"]) : false;
        $term = $params["term"] ? addText($params["term"]) : false;

        $entries = self::getSQLClassEvents($params);
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $this->deleteDayClassEvent($value->GUID);
            }
        }

        $condition = array(
            'SHORTDAY = ? ' => $shortday
            , 'ACADEMIC_ID = ? ' => $academicId
            , 'TERM = ? ' => $term
        );

        self::dbAccess()->delete('t_schedule', $condition);

        return array(
            "success" => true
        );
    }

    public static function checkSubjectInSchedule($subjectId, $academicId, $term)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(self::TABLE_SCHEDULE, 'COUNT(*) AS C');
        $SQL->where("SUBJECT_ID='" . $subjectId . "'");
        $SQL->where("ACADEMIC_ID='" . $academicId . "'");
        if ($term)
            $SQL->where("TERM='" . $term . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function checkStartTimeANDEndTime($starttime, $endtime, $shortday, $academicId, $term, $scheduleId, $subjectId, $shared)
    {

        $facette = self::findScheduleFromGuId($scheduleId);

        $ERROR_TIME_HAS_BEEN_USED = false;

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_schedule'), array("*"));
        $SQL->where("START_TIME <=" . $starttime . " AND END_TIME >=" . $endtime . "");
        $SQL->where("TERM= '" . $term . "'");
        $SQL->where("SHORTDAY= '" . $shortday . "'");
        $SQL->where("ACADEMIC_ID= '" . $academicId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        if (!$facette)
        {
            if ($result)
            {
                switch ($shared)
                {
                    case 1:
                        if ($result->SUBJECT_ID == $subjectId)
                        {
                            if ($result->START_TIME != $starttime && $result->END_TIME != $endtime)
                            {
                                $ERROR_TIME_HAS_BEEN_USED = true;
                            }
                        }
                        else
                        {

                            if ($result->START_TIME == $starttime && $result->END_TIME == $endtime)
                            {
                                $ERROR_TIME_HAS_BEEN_USED = false;
                            }
                            else
                            {
                                $ERROR_TIME_HAS_BEEN_USED = true;
                            }
                        }
                        break;
                    default:
                        $ERROR_TIME_HAS_BEEN_USED = true;
                        break;
                }
            }
        }

        return $ERROR_TIME_HAS_BEEN_USED;
    }

    public function checkStartTimeANDEndTimeTraining($starttime, $endtime, $shortday, $trainingId, $scheduleId = false, $startDate = false, $endDate = false)
    {

        $facette = self::findScheduleFromGuId($scheduleId);
        $traininObject = TrainingDBAccess::findTrainingFromId($trainingId); //@veasna
        $ERROR_TIME_HAS_BEEN_USED = false;

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_schedule'), array("*"));
        $SQL->where("START_TIME <=" . $starttime . " AND END_TIME >=" . $endtime . "");
        //$SQL->where("TERM= '" . $term . "'");
        $SQL->where("SHORTDAY= '" . $shortday . "'");
        $SQL->where("TRAINING_ID= '" . $trainingId . "'");
        if ($traininObject->SCHEDULE_SETTING)
        {
            $SQL->where("START_DATE <='" . setDate2DB($startDate) . "' AND END_DATE >='" . setDate2DB($endDate) . "'");
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        if (!$facette)
        {
            if ($result)
            {
                switch ($shared)
                {
                    case 1:
                        if ($result->SUBJECT_ID == $subjectId)
                        {
                            if ($result->START_TIME != $starttime && $result->END_TIME != $endtime)
                            {
                                $ERROR_TIME_HAS_BEEN_USED = true;
                            }
                        }
                        else
                        {

                            if ($result->START_TIME == $starttime && $result->END_TIME == $endtime)
                            {
                                $ERROR_TIME_HAS_BEEN_USED = false;
                            }
                            else
                            {
                                $ERROR_TIME_HAS_BEEN_USED = true;
                            }
                        }
                        break;
                    default:
                        $ERROR_TIME_HAS_BEEN_USED = true;
                        break;
                }
            }
        }

        return $ERROR_TIME_HAS_BEEN_USED;

        /* $ERROR_TIME_HAS_BEEN_USED = false;

          $SQLFirst = "";
          $SQLFirst .= " SELECT *";
          $SQLFirst .= " FROM " . self::TABLE_SCHEDULE . "";
          $SQLFirst .= " WHERE 1=1";
          $SQLFirst .= " AND (START_TIME BETWEEN '" . $strttime . "' AND '" . $endtime . "')";
          $SQLFirst .= " OR (END_TIME BETWEEN '" . $strttime . "' AND '" . $endtime . "')";
          $resultFirst = self::dbAccess()->fetchAll($SQLFirst);

          $SQLSecond = "";
          $SQLSecond .= " SELECT *";
          $SQLSecond .= " FROM " . self::TABLE_SCHEDULE . "";
          $SQLSecond .= " WHERE 1=1";
          $SQLSecond .= " AND ('" . $strttime . "' BETWEEN START_TIME AND END_TIME)";
          $SQLSecond .= " OR ('" . $strttime . "' BETWEEN START_TIME AND END_TIME)";
          $resultSecond = self::dbAccess()->fetchAll($SQLSecond);

          $FIRST_DATA = array();
          if ($resultFirst) {
          foreach ($resultFirst as $key => $value) {
          if ($value->TRAINING_ID == $trainingId) {
          if ($value->SHORTDAY == $shortday) {
          $FIRST_DATA[$value->GUID] = $value->GUID;
          }
          }
          }
          }

          $SECOND_DATA = array();
          if ($resultSecond) {
          foreach ($resultSecond as $key => $value) {
          if ($value->TRAINING_ID == $trainingId) {
          if ($value->SHORTDAY == $shortday) {
          $SECOND_DATA[$value->GUID] = $value->GUID;
          }
          }
          }
          }

          $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;

          $CHECK_COUNT = count($CHECK_DATA);
          if ($CHECK_COUNT == 1) {
          if (isset($scheduleId)) {
          if (!in_array($scheduleId, $CHECK_DATA)) {
          $ERROR_TIME_HAS_BEEN_USED = true;
          }
          }
          } elseif ($CHECK_COUNT >= 2) {
          $ERROR_TIME_HAS_BEEN_USED = true;
          }

          return $ERROR_TIME_HAS_BEEN_USED;
         */
    }

    public function getSubjectsInSchedule($teacherId, $subjectId, $academicId)
    {

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM " . self::TABLE_SCHEDULE . "";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND TEACHER_ID='" . $teacherId . "'";
        $SQL .= " AND SUBJECT_ID='" . $subjectId . "'";
        $SQL .= " AND ACADEMIC_ID='" . $academicId . "'";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
        {
            foreach ($result as $value)
            {
                $data[$value->SUBJECT_ID] = $value->SUBJECT_ID;
            }
        }

        return $data;
    }

    public function jsonTeacherSchedule($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : false;

        $params["status"] = 1;
        $resultRows = self::getSQLClassEvents($params);

        $data = array();
        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

                $CHECK_SUBJECT_TEACHER_DATA = $this->getSubjectsInSchedule($teacherId, $value->SUBJECT_ID, $value->ACADEMIC_ID);

                if (in_array($value->SUBJECT_ID, $CHECK_SUBJECT_TEACHER_DATA))
                {

                    $data[$i]["ID"] = $value->GUID;
                    $data[$i]["STATUS"] = $value->SCHEDULE_STATUS;
                    $data[$i]["CODE"] = setShowText($value->SCHEDULE_CODE);

                    switch ($value->SCHEDULE_TYPE)
                    {
                        case 1:
                            $data[$i]["EVENT"] = setShowText($value->SUBJECT_NAME);
                            $data[$i]["COLOR"] = $value->SUBJECT_COLOR;
                            $data[$i]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                            break;
                        case 2:
                            $data[$i]["EVENT"] = setShowText($value->EVENT);
                            $data[$i]["COLOR"] = "#FFF";
                            break;
                    }

                    $data[$i]["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                    $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                    $data[$i]["EXTRA_START_DATE"] = secondToHour($value->START_TIME);
                    $data[$i]["EXTRA_END_DATE"] = secondToHour($value->END_TIME);
                    $data[$i]["GRADE_CLASS"] = setShowText($value->CLASS_NAME);
                    $data[$i]["ROOM"] = setShowText($value->ROOM);

                    $i++;
                }
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    protected function setTeacherWorkingSubjectInAllDays($in_all_days, $teacherId, $subjectId, $academicId, $term)
    {

        $params["academicId"] = $academicId;
        $params["subjectId"] = $subjectId;
        $params["term"] = $term;
        $entries = self::getSQLClassEvents($params);

        $TEACHER_SUBJECTS = $this->getTeacherSubjects($teacherId);

        if (in_array($subjectId, $TEACHER_SUBJECTS))
        {
            if ($entries)
            {
                foreach ($entries as $value)
                {
                    if ($subjectId == $value->SUBJECT_ID)
                    {
                        if ($in_all_days && $teacherId && $subjectId && $academicId && $term)
                        {
                            $SAVEDATA["TEACHER_ID"] = $teacherId;
                            $WHERE = self::dbAccess()->quoteInto("GUID = ?", $value->GUID);
                            self::dbAccess()->update(self::TABLE_SCHEDULE, $SAVEDATA, $WHERE);
                        }
                    }
                }
            }
        }
    }

    protected function getTeacherSubjects($teacherId)
    {

        $SQL = "SELECT * FROM t_teacher_subject";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND TEACHER='" . $teacherId . "'";
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {
                $data[$value->SUBJECT] = $value->SUBJECT;
            }
        }

        return $data;
    }

    public function checkAssignedTeacherInSchedule($params)
    {

        $teacherId = isset($params["checkId"]) ? addText($params["checkId"]) : false;
        $target = isset($params["target"]) ? addText($params["target"]) : false;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : '';

        $searchParams["teacherId"] = $teacherId;
        $searchParams["target"] = $target;
        $searchParams["schoolyearId"] = $schoolyearId;
        $resultRows = self::getSQLClassEvents($searchParams);

        $data = array();

        if ($resultRows)
        {
            foreach ($resultRows as $key => $value)
            {

                if ($value->ACADEMIC_ID)
                {
                    $data[$key]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                    $data[$key]["ID"] = $value->GUID;
                    $data[$key]["DAY"] = getShortdayName($value->SHORTDAY);
                    $data[$key]["COLOR"] = $value->SUBJECT_COLOR;
                    $data[$key]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                    $data[$key]["TEACHING_INFORMATION"] = "<b>" . getShortdayName($value->SHORTDAY) . "</b>";
                    $data[$key]["TEACHING_INFORMATION"] .= "<br>";
                    $data[$key]["TEACHING_INFORMATION"] .= "<b>" . $value->CLASS_NAME . "</b>";
                    $data[$key]["TEACHING_INFORMATION"] .= "<br>";
                    $data[$key]["TEACHING_INFORMATION"] .= $value->SUBJECT_NAME;
                    $data[$key]["TEACHING_INFORMATION"] .= "<br>";
                    $data[$key]["TEACHING_INFORMATION"] .= displaySchoolTerm($value->TERM);
                }
                elseif ($value->TRAINING_ID)
                {
                    $data[$key]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                    $data[$key]["ID"] = $value->ID;
                    $data[$key]["DAY"] = getShortdayName($value->SHORTDAY);
                    $data[$key]["COLOR"] = $value->SUBJECT_COLOR;
                    $data[$key]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                    $data[$key]["TEACHING_INFORMATION"] .= "<br>";
                    $data[$key]["TEACHING_INFORMATION"] .= $value->SUBJECT_NAME;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function checkAssignedRoomInSchedule($params)
    {

        $roomId = isset($params["checkId"]) ? addText($params["checkId"]) : false;
        $target = isset($params["target"]) ? addText($params["target"]) : false;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : '';

        $searchParams["target"] = $target;
        $searchParams["roomId"] = $roomId;
        $searchParams["schoolyearId"] = $schoolyearId;
        $resultRows = self::getSQLClassEvents($searchParams);

        $data = array();

        if ($resultRows)
        {
            foreach ($resultRows as $key => $value)
            {

                $data[$key]["ID"] = $value->ROOM_ID;
                $data[$key]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);

                if ($value->ACADEMIC_ID)
                {
                    $data[$key]["DAY"] = getShortdayName($value->SHORTDAY);
                    $data[$key]["COLOR"] = $value->SUBJECT_COLOR;
                    $data[$key]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                    $data[$key]["TEACHING_INFORMATION"] = $value->CLASS_NAME;
                    $data[$key]["TEACHING_INFORMATION"] .= "<br>";
                    $data[$key]["TEACHING_INFORMATION"] .= displaySchoolTerm($value->TERM);
                }
                elseif ($value->TRAINING_ID)
                {
                    $data[$key]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                    $data[$key]["ID"] = $value->ID;
                    $data[$key]["DAY"] = getShortdayName($value->SHORTDAY);
                    $data[$key]["COLOR"] = $value->SUBJECT_COLOR;
                    $data[$key]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                    $data[$key]["TEACHING_INFORMATION"] .= "<br>";
                    $data[$key]["TEACHING_INFORMATION"] .= $value->SUBJECT_NAME;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function setSharedSchedule($academicId, $scheduleObject, $checked)
    {

        if ($academicId && is_object($scheduleObject))
        {

            $CONDITION_A = array(
                'TERM = ? ' => $scheduleObject->TERM
                , 'START_TIME = ? ' => $scheduleObject->START_TIME
                , 'END_TIME = ? ' => $scheduleObject->END_TIME
                , 'SHORTDAY = ? ' => $scheduleObject->SHORTDAY
                , 'ACADEMIC_ID = ? ' => $academicId
            );
            if ($checked == false)
            {
                self::dbAccess()->delete('t_schedule', $CONDITION_A);
            }
            $SAVEDATA["CODE"] = createCode();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA["GUID"] = generateGuid();
            $SAVEDATA["SHORTDAY"] = $scheduleObject->SHORTDAY;
            $SAVEDATA["SHARED_FROM"] = $scheduleObject->ID;
            $SAVEDATA["GRADE_ID"] = $scheduleObject->GRADE_ID;
            $SAVEDATA["SHARED_SCHEDULE"] = $scheduleObject->SHARED_SCHEDULE;
            $SAVEDATA["SCHOOLYEAR_ID"] = $scheduleObject->SCHOOLYEAR_ID;
            $SAVEDATA["TERM"] = $scheduleObject->TERM;
            $SAVEDATA["ACADEMIC_ID"] = $academicId;
            $SAVEDATA["ROOM_ID"] = $scheduleObject->ROOM_ID;
            $SAVEDATA["SUBJECT_ID"] = $scheduleObject->SUBJECT_ID;
            $SAVEDATA["TEACHER_ID"] = $scheduleObject->TEACHER_ID;
            $SAVEDATA["EVENT"] = $scheduleObject->EVENT;
            $SAVEDATA["STATUS"] = $scheduleObject->STATUS;
            $SAVEDATA["START_TIME"] = $scheduleObject->START_TIME;
            $SAVEDATA["END_TIME"] = $scheduleObject->END_TIME;
            $SAVEDATA["SCHEDULE_TYPE"] = $scheduleObject->SCHEDULE_TYPE;
            $SAVEDATA["DESCRIPTION"] = $scheduleObject->DESCRIPTION;
            if ($checked)
            {
                $checkSchedule = self::getSQLClassEvents(array('academicId' => $academicId, 'subjectId' => $scheduleObject->SUBJECT_ID, 'starttime' => $scheduleObject->START_TIME, 'endtime' => $scheduleObject->END_TIME, 'single' => true));
                if (!$checkSchedule)
                    self::dbAccess()->insert('t_schedule', $SAVEDATA);
            }
            $CONDITION_B = array(
                'SCHEDULE_ID = ? ' => $scheduleObject->ID
                , 'ACADEMIC_ID = ? ' => $academicId
            );
            if ($checked == false)
            {
                self::dbAccess()->delete('t_link_schedule_academic', $CONDITION_B);
            }
            $LINK_SAVEDATA["SCHEDULE_ID"] = $scheduleObject->ID;
            $LINK_SAVEDATA["ACADEMIC_ID"] = $academicId;
            if ($checked)
                self::dbAccess()->insert('t_link_schedule_academic', $LINK_SAVEDATA);
        }
    }

    public function linkedScheduleAcademic($Id, $type)
    {
        if ($type == 'EXTRA_SESSION')
        {
            $facette = TeachingSessionDBAccess::getTeachingSessionFromId($Id);
        }
        else
        {
            $facette = self::findScheduleFromGuId($Id);
        }

        $data = Array();

        if ($facette)
        {
            $academicObject = AcademicDBAccess::findGradeFromId($facette->ACADEMIC_ID);

            if ($academicObject)
            {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_grade", array('*'));
                $SQL->where("PARENT='" . $academicObject->ID . "'");
                $SQL->where("OBJECT_TYPE='CLASS'");
                $result = self::dbAccess()->fetchAll($SQL);

                $i = 0;
                if ($result)
                {
                    foreach ($result as $value)
                    {
                        $data[$i]['ID'] = $value->ID;
                        $data[$i]['NAME'] = $value->NAME;
                        $data[$i]['APPLY'] = self::checkLinkedScheduleAcademic($facette->ID, $value->ID, $type);
                        $i++;
                    }
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function actionLinkSchedule2Academic($scheduleId, $academicId, $isValue, $target = false)
    {

        $facette = self::findScheduleFromGuId($scheduleId);
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($facette && $academicObject)
        {
            $CONDITION = array(
                'SCHEDULE_ID = ? ' => $facette->ID
                , 'ACADEMIC_ID = ? ' => $academicId
            );

            self::dbAccess()->delete('t_link_schedule_academic', $CONDITION);

            if ($isValue)
            {
                $SAVEDATA["SCHEDULE_ID"] = $facette->ID;
                $SAVEDATA["ACADEMIC_ID"] = $academicId;
                $SAVEDATA["PARENT_ACADEMIC_ID"] = $academicObject->PARENT;
                if ($target)
                {
                    $SAVEDATA["TARGET"] = 1;
                }
                self::dbAccess()->insert('t_link_schedule_academic', $SAVEDATA);
            }
            //@veasna
            ///find parent schedule if have no action if not insert
            $parentSchedule = self::getSQLClassEvents(array('academicId' => $academicObject->PARENT, 'subjectId' => $facette->SUBJECT_ID, 'starttime' => $facette->START_TIME, 'endtime' => $facette->END_TIME, 'single' => true));
            if (!$parentSchedule)
            {
                $SAVEDATA = array();
                $SAVEDATA["CODE"] = createCode();
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                $SAVEDATA["GUID"] = generateGuid();
                $SAVEDATA["SHORTDAY"] = $facette->SHORTDAY;
                $SAVEDATA["SHARED_FROM"] = $facette->ID;
                $SAVEDATA["GRADE_ID"] = $facette->GRADE_ID;
                $SAVEDATA["SHARED_SCHEDULE"] = $facette->SHARED_SCHEDULE;
                $SAVEDATA["SCHOOLYEAR_ID"] = $facette->SCHOOLYEAR_ID;
                $SAVEDATA["TERM"] = $facette->TERM;
                $SAVEDATA["ACADEMIC_ID"] = $academicObject->PARENT;
                $SAVEDATA["ROOM_ID"] = $facette->ROOM_ID;
                $SAVEDATA["SUBJECT_ID"] = $facette->SUBJECT_ID;
                $SAVEDATA["TEACHER_ID"] = $facette->TEACHER_ID;
                $SAVEDATA["EVENT"] = $facette->EVENT;
                $SAVEDATA["STATUS"] = $facette->STATUS;
                $SAVEDATA["START_TIME"] = $facette->START_TIME;
                $SAVEDATA["END_TIME"] = $facette->END_TIME;
                $SAVEDATA["SCHEDULE_TYPE"] = $facette->SCHEDULE_TYPE;
                $SAVEDATA["DESCRIPTION"] = $facette->DESCRIPTION;
                if ($isValue)
                {
                    self::dbAccess()->insert('t_schedule', $SAVEDATA);
                }
            }
            $chekLinkParent = self::findLinkedScheduleAcademic(array('scheduleId' => $facette->ID, 'academicId' => $academicObject->PARENT));
            if (!$chekLinkParent)
            {
                if ($isValue)
                {
                    if ($academicObject->PARENT != $facette->ACADEMIC_ID)
                        self::dbAccess()->insert('t_link_schedule_academic', array('SCHEDULE_ID' => $facette->ID, 'ACADEMIC_ID' => $academicObject->PARENT));
                }
            }
            //
            $scheduleLinkAcademic = self::findLinkedScheduleAcademicByScheduleId($facette->ID);
            $GROUP_ID_ARR = array();
            if ($scheduleLinkAcademic)
            {
                foreach ($scheduleLinkAcademic as $value)
                {
                    $GROUP_ID_ARR[] = $value->ACADEMIC_ID;
                }
            }
            $SAVE_GROUP_IDS["GROUP_IDS"] = implode(",", $GROUP_ID_ARR);
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $facette->ID);
            self::dbAccess()->update('t_schedule', $SAVE_GROUP_IDS, $WHERE);
            // 
        }
    }

    public function jsonActionLinkSchedule2Academic($params)
    {

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : "";
        $academicId = isset($params["id"]) ? addText($params["id"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        self::actionLinkSchedule2Academic($scheduleId, $academicId, $newValue, false);
        return array(
            "success" => true
        );
    }

    public static function checkLinkedScheduleAcademic($scheduleId, $academicId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_link_schedule_academic", array("C" => "COUNT(*)"));
        $SQL->where("SCHEDULE_ID = '" . $scheduleId . "'");
        $SQL->where("ACADEMIC_ID = ?", $academicId);
        $result = self::dbAccess()->fetchRow($SQL);

        if ($result)
        {
            return $result->C ? 1 : 0;
        }
        else
        {
            return 0;
        }
    }

    //@veasna
    public static function findLinkedScheduleAcademicByScheduleId($scheduleId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_link_schedule_academic'), array("ACADEMIC_ID AS ACADEMIC_ID"));
        $SQL->joinLeft(array('B' => 't_grade'), 'B.ID=A.ACADEMIC_ID', array("*"));
        $SQL->where("A.SCHEDULE_ID = '" . $scheduleId . "'");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());
        return $result;
    }

    public static function findLinkedScheduleAcademicByAcademicId($academicId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_link_schedule_academic', array('*'));
        $SQL->where("ACADEMIC_ID= '" . $academicId . "'");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());
        return $result;
    }

    public static function sqlTeachersCredit($params)
    {

        $objectId = isset($params['objectId']) ? addText($params['objectId']) : '';
        //$parentId = isset($params['parentId']) ? $params['parentId'] : '';

        $SELECTION_C = array(
            "ID AS STAFF_ID"
            , "CODE AS CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS  LASTNAME"
            , "GENDER AS GENDER"
            , "PHONE AS PHONE"
            , "EMAIL AS EMAIL"
        );

        $SELECTION_B = array(
            "GUID AS GUID"
            , "ID AS SCHEDULE_ID"
            , "START_TIME AS START_TIME"
            , "END_TIME AS END_TIME"
            , "SCHOOLYEAR_ID AS SCHOOLYEAR_ID"
            , "TERM AS TERM"
            , "SHORTDAY AS SHORTDAY"
            , "TERM AS TERM"
        );

        $SELECTION_D = array(
            "GUID AS GROUP_GUID"
            , "ID AS GROUP_ID"
            , "PARENT AS ACADEMIC_ID"
            , "NAME AS GROUP_NAME"
        );

        $SELECTION_E = array(
            "GUID AS SUBJECT_TGRADE_GUID"
            , "ID AS SUBJECT_TGRADE_ID"
            , "NAME AS SUBJECT_TGRADE"
        );

        $academicObject = AcademicDBAccess::findGradeFromId($objectId);

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_link_schedule_academic'));
        $SQL->joinLeft(array('B' => 't_schedule'), 'B.ID=A.SCHEDULE_ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_staff'), 'B.TEACHER_ID=C.ID', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_grade'), 'D.ID=A.ACADEMIC_ID', $SELECTION_D);
        if ($academicObject)
        {
            switch ($academicObject->OBJECT_TYPE)
            {
                case 'CLASS':
                    $SQL->where("B.ACADEMIC_ID='" . $academicObject->PARENT . "'");
                    $SQL->where("A.ACADEMIC_ID='" . $academicObject->ID . "'");
                    break;
                case 'SUBJECT':
                    $SQL->where("B.ACADEMIC_ID='" . $academicObject->ID . "'");
                    break;
                case 'SCHOOLYEAR':
                    $SQL->joinLeft(array('E' => 't_grade'), 'E.ID=B.ACADEMIC_ID', $SELECTION_E);
                    $SQL->where("E.PARENT='" . $academicObject->ID . "'");
                    $SQL->group(array("GUID"));
                    break;
            }
        }

        $SQL->order("C.ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function jsonListTeacherCredit($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($objectId);
        $result = self::sqlTeachersCredit($params);
        $data = array();
        $creditGroupArr = array();
        $teacher = '';
        $i = 0;
        if ($result)
        {
            foreach ($result as $value)
            {
                if ($value->STAFF_ID)
                {
                    if ($teacher == $value->STAFF_ID)
                    {
                        if (in_array($value->GROUP_ID, $creditGroupArr))
                            continue;
                    }else
                    {
                        $creditGroupArr = array();
                    }
                    $teacher = $value->STAFF_ID;
                    $data[$i]["ID"] = $teacher; //@THORN Visal
                    $data[$i]["TEACHER_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME . " (" . $value->CODE . ")";
                    $data[$i]["PHONE"] = $value->PHONE;
                    $data[$i]["EMAIL"] = $value->EMAIL;
                    if ($academicObject)
                    {
                        switch ($academicObject->OBJECT_TYPE)
                        {
                            case 'CLASS':
                            case 'SUBJECT':
                                $data[$i]["GROUP_NAME"] = $value->GROUP_NAME;
                                $data[$i]["GROUP_ID"] = $value->GROUP_ID; //@THORN Visal
                                break;
                            case 'SCHOOLYEAR':
                                $data[$i]["SUBJECT"] = $value->SUBJECT_TGRADE;
                                break;
                        }
                    }
                    switch (Zend_Registry::get('SCHOOL')->SCHOOL_TERM)
                    {
                        case 1:
                            $data[$i]["FIRST_TERM"] = ($value->TERM == "FIRST_TERM") ? iconYESNO(true) : iconYESNO(false);
                            $data[$i]["SECOND_TERM"] = ($value->TERM == "SECOND_TERM") ? iconYESNO(true) : iconYESNO(false);
                            $data[$i]["THIRD_TERM"] = ($value->TERM == "THIRD_TERM") ? iconYESNO(true) : iconYESNO(false);
                            break;
                        case 2:
                            $data[$i]["FIRST_QUARTER"] = ($value->TERM == "FIRST_QUARTER") ? iconYESNO(true) : iconYESNO(false);
                            $data[$i]["SECOND_QUARTER"] = ($value->TERM == "SECOND_QUARTER") ? iconYESNO(true) : iconYESNO(false);
                            $data[$i]["THIRD_QUARTER"] = ($value->TERM == "THIRD_QUARTER") ? iconYESNO(true) : iconYESNO(false);
                            $data[$i]["FOURTH_QUARTER"] = ($value->TERM == "FOURTH_QUARTER") ? iconYESNO(true) : iconYESNO(false);
                            break;
                        default:
                            $data[$i]["FIRST_SEMESTER"] = ($value->TERM == "FIRST_SEMESTER") ? iconYESNO(true) : iconYESNO(false);
                            $data[$i]["SECOND_SEMESTER"] = ($value->TERM == "SECOND_SEMESTER") ? iconYESNO(true) : iconYESNO(false);
                            break;
                    }
                    $creditGroupArr[] = $value->GROUP_ID;
                    $i++;
                }
            }
        }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function countTeacherScheduleFirstSemester($teacherId, $academicId, $child = false)
    {

        $SQL = self::dbAccess()->select();
        if ($child)
        {
            $SQL->from(array('A' => 't_schedule'), array("C" => "COUNT(*)"));
            $SQL->joinLeft(array('B' => 't_link_schedule_academic'), 'A.ID=B.SCHEDULE_ID');
            $SQL->where("A.TEACHER_ID = '" . $teacherId . "'");
            $SQL->where("B.ACADEMIC_ID = '" . $academicId . "'");
            $SQL->where("A.TERM = 'FIRST_SEMESTER'");
        }
        else
        {
            $SQL->from("t_schedule", array("C" => "COUNT(*)"));
            $SQL->where("TEACHER_ID = ?", $teacherId);
            $SQL->where("ACADEMIC_ID = ?", $academicId);
            $SQL->where("TERM = 'FIRST_SEMESTER'");
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function countTeacherScheduleSecondSemester($teacherId, $academicId, $child)
    {

        $SQL = self::dbAccess()->select();
        if ($child)
        {
            $SQL->from(array('A' => 't_schedule'), array("C" => "COUNT(*)"));
            $SQL->joinLeft(array('B' => 't_link_schedule_academic'), 'A.ID=B.SCHEDULE_ID');
            $SQL->where("A.TEACHER_ID = '" . $teacherId . "'");
            $SQL->where("B.ACADEMIC_ID = '" . $academicId . "'");
            $SQL->where("A.TERM = 'SECOND_SEMESTER'");
        }
        else
        {
            $SQL->from("t_schedule", array("C" => "COUNT(*)"));
            $SQL->where("TEACHER_ID = ?", $teacherId);
            $SQL->where("ACADEMIC_ID = ?", $academicId);
            $SQL->where("TERM = 'SECOND_SEMESTER'");
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkCreditScheduleInGroup($scheduleId, $group)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_link_schedule_academic', 'COUNT(*) AS C');
        $SQL->where("SCHEDULE_ID = '" . $scheduleId . "'");
        $SQL->where("ACADEMIC_ID IN (" . $group . ")");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public static function checkCreditExtraSessionInGroup($scheduleId, $group)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_link_schedule_academic', 'COUNT(*) AS C');
        $SQL->where("TEACHING_SESSION_ID = '" . $scheduleId . "'");
        $SQL->where("ACADEMIC_ID IN (" . $group . ")");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function loadCreditStudentSubjectEvents($params, $isJson = true)
    {

        $data = array();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $facette = GradeSubjectDBAccess::getStudentCreditSubject($studentId, $schoolyearId);
        $studentGroupArr = array();
        $studentGroup = '';
        if ($facette)
        {
            foreach ($facette as $studentInSubject)
            {
                if ($studentInSubject->CLASS_ID)
                {
                    $studentGroupArr[] = $studentInSubject->CLASS_ID;
                }
            }
            $studentGroup = implode(',', $studentGroupArr);
        }
        //error_log($studentGroup);

        $resultRows = self::getSQLCreditSubjectEvent($params);

        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {


                $data[$i]["ID"] = $value->GUID;
                $data[$i]["STATUS"] = $value->SCHEDULE_STATUS;
                $data[$i]["CODE"] = setShowText($value->SCHEDULE_CODE);
                $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                $data[$i]["EXTRA_START_DATE"] = secondToHour($value->START_TIME);
                $data[$i]["EXTRA_END_DATE"] = secondToHour($value->END_TIME);

                $params["starttime"] = $value->START_TIME;
                $params["endtime"] = $value->END_TIME;
                $params['single'] = true;
                $shortdayInWeek = array("MO", "TU", "WE", "TH", "FR", "SA", "SU");

                foreach ($shortdayInWeek as $shortday)
                {
                    $params["shortday"] = $shortday;
                    $DAY_OBJECT = self::getSQLCreditSubjectEvent($params);
                    $data[$i][$shortday] = "";
                    if ($DAY_OBJECT)
                    {
                        $inCheck = self::checkCreditScheduleInGroup($DAY_OBJECT->SCHEDULE_ID, $studentGroup);
                        if (!$inCheck)
                        {
                            continue;
                        }

                        $data[$i][$shortday . "_EVENT"] = setShowText($DAY_OBJECT->SUBJECT_NAME);
                        $data[$i][$shortday . "_GUID"] = $DAY_OBJECT->GUID;

                        $data[$i][$shortday] .= setShowText($DAY_OBJECT->SUBJECT_NAME);
                        $data[$i][$shortday] .= "<br>";

                        $data[$i][$shortday] .= $DAY_OBJECT->TEACHER;
                        $data[$i][$shortday] .= "<br>";

                        $data[$i][$shortday] .= setShowText($DAY_OBJECT->ROOM);
                        $data[$i][$shortday] .= "<br>";

                        $data[$i][$shortday . "_COLOR"] = $DAY_OBJECT->SUBJECT_COLOR;
                        $data[$i][$shortday . "_COLOR_FONT"] = getFontColor($DAY_OBJECT->SUBJECT_COLOR);
                        $data[$i][$shortday . "_DESCRIPTION_EX"] = setShowText($DAY_OBJECT->SUBJECT_NAME) . "<br>" . $DAY_OBJECT->TEACHER . "<br>" . setShowText($DAY_OBJECT->ROOM) . "<br>" . $DAY_OBJECT->DESCRIPTION;

                        $data[$i][$shortday . "_DESCRIPTION"] = $DAY_OBJECT->DESCRIPTION;
                    }
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        //@soda
        if ($isJson)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        }
    }

    public static function jsonTreeSharedSchedule2Academic($params)
    {

        $node = isset($params["node"]) ? addText($params["node"]) : "0";
        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : "0";
        $scheduleObject = self::findScheduleFromGuId($scheduleId);

        $data = array();

        if ($scheduleObject)
        {

            $facette = AcademicDBAccess::findGradeFromId($scheduleObject->ACADEMIC_ID);

            if ($node == 0)
            {
                $parentId = $facette->PARENT;
            }
            else
            {
                $parentId = $node;
            }

            $SQL = self::dbAccess()->select();
            $SQL->from("t_grade", array("*"));
            $SQL->where("PARENT = ?", $parentId);
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);

            $i = 0;
            if ($result)
            {
                foreach ($result as $value)
                {
                    /* if ($scheduleObject->ACADEMIC_ID != $value->ID)
                      { */
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = setShowText($value->NAME);
                    $data[$i]['iconCls'] = "icon-shape_square_link";
                    $data[$i]['cls'] = "nodeText";
                    $data[$i]['checked'] = self::checkSharedScheduleAcademic($scheduleId, $value->ID);
                    if ($value->OBJECT_TYPE == 'SUBCLASS')
                    {
                        $data[$i]['leaf'] = true;
                    }
                    else
                    {
                        $data[$i]['leaf'] = false;
                        if ($value->ID == $scheduleObject->ACADEMIC_ID)
                        {
                            $data[$i]['disabled'] = true;
                            $data[$i]['checked'] = true;
                        }
                    }


                    $i++;
                    //}
                }
            }
        }

        return $data;
    }

    public static function actionSharingSchedule2Academic($params)
    {

        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : "0";
        $selectedId = isset($params["selectedId"]) ? addText($params["selectedId"]) : "";
        $checked = false;
        if (isset($params["checked"]))
        {
            if ($params["checked"] == "true")
            {
                $checked = true;
            }
        }
        $scheduleObject = self::findScheduleFromGuId($scheduleId);
        if ($scheduleObject)
        {
            if ($selectedId)
            {
                $academicObject = AcademicDBAccess::findGradeFromId($selectedId);
                switch ($academicObject->OBJECT_TYPE)
                {
                    case "CLASS":
                        self::setSharedSchedule($selectedId, $scheduleObject, $checked);
                        break;
                    case "SUBCLASS":
                        self::actionLinkSchedule2Academic($scheduleId, $selectedId, $checked, 1);
                        break;
                }
            }
        }
        return array(
            "success" => true
        );
    }

    public static function displayGroupSubjectEvent($scheduleObject)
    {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_subject'), array('NAME AS SUBJECT_NAME', 'ID AS SUBJECT_ID'));
        $SQL->joinLeft(array('B' => 't_schedule'), 'A.ID=B.SUBJECT_ID', array('GUID AS SCHEDULE_GUID'));
        $SQL->where("B.ACADEMIC_ID = '" . $scheduleObject->ACADEMIC_ID . "'");
        $SQL->where("B.START_TIME = '" . $scheduleObject->START_TIME . "'");
        $SQL->where("B.END_TIME = '" . $scheduleObject->END_TIME . "'");
        $SQL->where("B.TERM = '" . $scheduleObject->TERM . "'");
        $SQL->where("B.SHORTDAY = '" . $scheduleObject->SHORTDAY . "'");
        $SQL->order("A.NAME DESC");
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $DAY_EVENT = "";
        if ($result)
        {
            $j = 0;
            foreach ($result as $value)
            {
                if ($j != 0)
                    $DAY_EVENT .="<br>";
                $DAY_EVENT .= setShowText($value->SUBJECT_NAME);
                ++$j;
            }
        }

        return $DAY_EVENT;
    }

    public static function checkSharedScheduleAcademic($scheduleId, $academicId)
    {
        $CHECK = self::checkLinkedScheduleAcademic($scheduleId, $academicId);
        return $CHECK ? true : false;
    }

    //@veasna
    public static function getCreditClassInAcademicSubject($params)
    {

        $academicId = isset($params['academicId']) ? addText($params["academicId"]) : '';
        $schoolyearId = isset($params['schoolyearId']) ? addText($params["schoolyearId"]) : '';
        $teacherId = isset($params['teacherId']) ? $params['teacherId'] : '';

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_grade'), array('*'));
        $SQL->joinLeft(array('B' => 't_link_schedule_academic'), 'A.ID=B.ACADEMIC_ID', array());
        $SQL->joinLeft(array('C' => 't_schedule'), 'C.ID=B.SCHEDULE_ID', array());
        if ($teacherId)
            $SQL->where("C.TEACHER_ID = '" . $teacherId . "'");
        if ($academicId)
            $SQL->where("C.ACADEMIC_ID = '" . $academicId . "'");
        if ($schoolyearId)
            $SQL->where("A.SCHOOL_YEAR = ?", $schoolyearId);

        $SQL->where("A.EDUCATION_SYSTEM = '1'");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonCreditClassInAcademic($params)
    {

        $result = self::getCreditClassInAcademicSubject($params);
        $data = array();
        $i = 0;

        if ($result)
        {
            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = setShowText($value->NAME);

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    ///
    public static function findLinkedScheduleAcademic($params)
    {
        $scheduleId = isset($params['scheduleId']) ? $params['scheduleId'] : '';
        $isParent = isset($params['isParent']) ? $params['isParent'] : '';
        $isChildrens = isset($params['isChildrens']) ? $params['isChildrens'] : '';
        $parentId = isset($params['parentId']) ? $params['parentId'] : '';
        $academicId = isset($params['academicId']) ? $params['academicId'] : '';

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_link_schedule_academic'), array("ACADEMIC_ID AS ACADEMIC_ID", "SCHEDULE_ID"));
        $SQL->joinLeft(array('B' => 't_grade'), 'B.ID=A.ACADEMIC_ID', array("*"));
        if ($scheduleId)
            $SQL->where("A.SCHEDULE_ID = '" . $scheduleId . "'");
        if ($isParent)
            $SQL->where("A.PARENT_ACADEMIC_ID = ?", 0);
        if ($isChildrens)
            $SQL->where("A.PARENT_ACADEMIC_ID <> 0");
        if ($parentId)
            $SQL->where("A.PARENT_ACADEMIC_ID = ?", $parentId);
        if ($academicId)
        {
            $SQL->where("A.ACADEMIC_ID = ?", $academicId);
        }
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());
        return $result;
    }

    public static function displayShearedWith($scheduleObject)
    {

        if ($scheduleObject->SHARED_FROM)
        {
            $ownerSchedule = self::findScheduleFromGuId($scheduleObject->SHARED_FROM);
            if ($ownerSchedule->ACADEMIC_ID)
            {
                $ownerScheduleClass = AcademicDBAccess::findGradeFromId($ownerSchedule->ACADEMIC_ID);
                $ownerScheduleSubClass = self::findLinkedScheduleAcademic(array('scheduleId' => $ownerSchedule->ID, 'parentId' => $ownerSchedule->ACADEMIC_ID));
            }
        }
        else
        {
            $ownerScheduleClass = AcademicDBAccess::findGradeFromId($scheduleObject->ACADEMIC_ID);
            $ownerScheduleSubClass = self::findLinkedScheduleAcademic(array('scheduleId' => $scheduleObject->SCHEDULE_ID, 'parentId' => $scheduleObject->ACADEMIC_ID));
        }

        $disPlay = "<div style=\"padding:5px;\"><h2 style=\"font-size:12px;\">This Schedule shared With:</h2>";
        $disPlay .="<span style=\"color:#15428b; font-weight:bold; padding-left:10px; margin-top:10px;\">" . $ownerScheduleClass->NAME;

        $i = 0;
        if ($ownerScheduleSubClass)
        {
            $disPlay .="(";
            foreach ($ownerScheduleSubClass as $value)
            {
                $disPlay .= $i ? ',' : '';
                $disPlay .= $value->NAME;
                $i++;
            }
            $disPlay .= ")";
        }
        $disPlay .= "</span>";
        /////Sheared
        if ($scheduleObject->SHARED_FROM)
        {
            $linkShearedSchedule = self::findLinkedScheduleAcademic(array('scheduleId' => $ownerSchedule->ID, 'isParent' => true));
        }
        else
        {
            $linkShearedSchedule = self::findLinkedScheduleAcademic(array('scheduleId' => $scheduleObject->SCHEDULE_ID, 'isParent' => true));
        }

        if ($linkShearedSchedule)
        {
            foreach ($linkShearedSchedule as $value)
            {
                $disPlay .= "<br/><span style=\"padding-left:10px;\">";
                $disPlay .= $value->NAME;
                $ShearSubClass = self::findLinkedScheduleAcademic(array('scheduleId' => $value->SCHEDULE_ID, 'parentId' => $value->ID));
                if ($ShearSubClass)
                {
                    $disPlay .= "(";
                    $i = 0;
                    foreach ($ShearSubClass as $subSchedule)
                    {
                        $disPlay .= $i ? ',' : '';
                        $disPlay .= $subSchedule->NAME;
                    }
                    $disPlay .= ")";
                }
                $disPlay .= "</span>";
            }
        }
        $disPlay .="<br/></div>";

        return $disPlay;
    }

    ///
    public static function displayEvent($type, $checkAcademicId, $EVENT_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT)
    {

        $DAY_EVENT = "";
        $DAY_NAME_EVENT = "";
        $DAY_COLOR = "";
        $DAY_COLOR_FONT = "";
        $DAY_GUID = "";
        $DAY_DESCRIPTION = "";
        $DAY_DESCRIPTION_EX = "";

        $DISPLAY_ROOM = Zend_Registry::get('SCHOOL')->ROOM_DISPLAY ? "ROOM_SHORT" : "ROOM";

        if ($EVENT_OBJECT)
        {

            if ($EVENT_OBJECT->SHARED_SCHEDULE)
            {
                if (self::checkAcaemicGroup($EVENT_OBJECT->ACADEMIC_ID))
                {
                    $DAY_EVENT .= self::displayGroupSubjectEvent($EVENT_OBJECT);
                    $DAY_EVENT .= "<br>";
                    $DAY_EVENT .= $EVENT_OBJECT->TEACHER;
                    $DAY_EVENT .= "<br>";
                    $DAY_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_ROOM);
                    $DAY_EVENT .= "<br>";
                    //$DAY_EVENT .= self::displayShearedWith($EVENT_OBJECT);
                    $DAY_EVENT .= "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/24/paperclip_add.png' ext:qtip='" . self::displayShearedWith($EVENT_OBJECT) . "'>";

                    $DAY_NAME_EVENT .= "";
                    $DAY_COLOR .= "#FFF";
                    $DAY_COLOR_FONT .= "#555";
                    $DAY_GUID .= $EVENT_OBJECT->GUID;
                    //$DAY_DESCRIPTION .= self::displayAcademicGroup($EVENT_OBJECT);
                    $DAY_DESCRIPTION .= self::displayShearedWith($EVENT_OBJECT);
                    $DAY_DESCRIPTION_EX .= "";
                }
                else
                {
                    $DAY_NAME_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_SUBJECT);
                    $DAY_GUID .= $EVENT_OBJECT->GUID;
                    switch ($EVENT_OBJECT->SCHEDULE_TYPE)
                    {
                        case 1:
                            $DAY_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_SUBJECT);
                            $DAY_EVENT .= "<br>";

                            $groupObject = self::findLinkedScheduleAcademicByScheduleId($EVENT_OBJECT->SCHEDULE_ID);
                            if ($groupObject)
                            {
                                $j = 0;
                                foreach ($groupObject as $group)
                                {
                                    if ($j != 0)
                                        $DAY_EVENT .=", ";
                                    $DAY_EVENT .= setShowText($group->NAME);
                                    ++$j;
                                }

                                $DAY_EVENT .= "<br>";
                            }
                            //
                            $DAY_EVENT .= $EVENT_OBJECT->TEACHER;
                            $DAY_EVENT .= "<br>";

                            $DAY_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_ROOM);
                            $DAY_EVENT .= "<br>";
                            $DAY_COLOR .= $EVENT_OBJECT->SUBJECT_COLOR;
                            $DAY_COLOR_FONT .= getFontColor($EVENT_OBJECT->SUBJECT_COLOR);
                            $DAY_DESCRIPTION_EX .= setShowText($EVENT_OBJECT->$DISPLAY_SUBJECT);
                            $DAY_DESCRIPTION_EX .= "<br>";
                            $DAY_DESCRIPTION_EX .= $EVENT_OBJECT->TEACHER;
                            $DAY_DESCRIPTION_EX .= "<br>";
                            $DAY_DESCRIPTION_EX .= setShowText($EVENT_OBJECT->SHORT);
                            $DAY_DESCRIPTION_EX .= setShowText($EVENT_OBJECT->$DISPLAY_ROOM);
                            $DAY_DESCRIPTION_EX .= "<br>";
                            $DAY_DESCRIPTION_EX .= $EVENT_OBJECT->DESCRIPTION;
                            break;
                        case 2:
                            $DAY_EVENT .= EVENT;
                            $DAY_EVENT .= "<br>";
                            $DAY_EVENT .= setShowText($EVENT_OBJECT->EVENT);
                            $DAY_COLOR .= "#FFF";
                            $DAY_COLOR_FONT .= "#555";
                            break;
                    }

                    $DAY_DESCRIPTION .= $EVENT_OBJECT->DESCRIPTION;
                }
            } else
            {
                if ($checkAcademicId && !in_array($EVENT_OBJECT->SCHEDULE_ID, $LINKED_SCHEDULE_CREDIT_DATA))
                {
                    $DAY_EVENT .= "";
                }
                else
                {
                    $DAY_NAME_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_SUBJECT);
                    $DAY_GUID .= $EVENT_OBJECT->GUID;
                    switch ($EVENT_OBJECT->SCHEDULE_TYPE)
                    {
                        case 1:
                            //@THORN Visal
                            /* switch (UserAuth::getUserType()) {
                              case "SUPERADMIN":
                              case "ADMIN":
                              case "STUDENT": */
                            $DAY_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_SUBJECT);
                            $DAY_EVENT .= "<br>";
                            // break;
                            // }

                            $groupObject = self::findLinkedScheduleAcademicByScheduleId($EVENT_OBJECT->SCHEDULE_ID);
                            if ($groupObject)
                            {
                                $j = 0;
                                foreach ($groupObject as $group)
                                {
                                    if ($j != 0)
                                        $DAY_EVENT .=", ";
                                    $DAY_EVENT .= setShowText($group->NAME);
                                    ++$j;
                                }

                                $DAY_EVENT .= "<br>";
                            }
                            //@THORN Visal
                            switch (UserAuth::getUserType())
                            {
                                case "SUPERADMIN":
                                case "ADMIN":
                                case "STUDENT":
                                case "INSTRUCTOR":
                                    $DAY_EVENT .= $EVENT_OBJECT->TEACHER;
                                    $DAY_EVENT .= "<br>";
                                    break;
                            }

                            $DAY_EVENT .= setShowText($EVENT_OBJECT->$DISPLAY_ROOM);
                            $DAY_EVENT .= "<br>";
                            $DAY_COLOR .= $EVENT_OBJECT->SUBJECT_COLOR;
                            $DAY_COLOR_FONT .= getFontColor($EVENT_OBJECT->SUBJECT_COLOR);
                            $DAY_DESCRIPTION_EX .= setShowText($EVENT_OBJECT->$DISPLAY_SUBJECT);
                            $DAY_DESCRIPTION_EX .= "<br>";
                            $DAY_DESCRIPTION_EX .= $EVENT_OBJECT->TEACHER;
                            $DAY_DESCRIPTION_EX .= "<br>";
                            $DAY_DESCRIPTION_EX .= setShowText($EVENT_OBJECT->$DISPLAY_ROOM);
                            $DAY_DESCRIPTION_EX .= "<br>";
                            $DAY_DESCRIPTION_EX .= $EVENT_OBJECT->DESCRIPTION;
                            break;
                        case 2:
                            $DAY_EVENT .= EVENT;
                            $DAY_EVENT .= "<br>";
                            $DAY_EVENT .= setShowText($EVENT_OBJECT->EVENT);
                            $DAY_COLOR .= "#FFF";
                            $DAY_COLOR_FONT .= "#555";
                            break;
                    }

                    $DAY_DESCRIPTION .= $EVENT_OBJECT->DESCRIPTION;
                }
            }
        }

        switch ($type)
        {
            case "DAY_EVENT":return $DAY_EVENT;
            case "DAY_NAME_EVENT":return $DAY_NAME_EVENT;
            case "DAY_COLOR_FONT":return $DAY_COLOR_FONT;
            case "DAY_COLOR":return $DAY_COLOR;
            case "DAY_GUID":return $DAY_GUID;
            case "DAY_DESCRIPTION":return $DAY_DESCRIPTION;
            case "DAY_DESCRIPTION_EX":return $DAY_DESCRIPTION_EX;
        }
    }

    public static function checkDoubleGroupEvent($scheduleObject)
    {

        if ($scheduleObject)
        {

            $SQL = self::dbAccess()->select();
            $SQL->distinct();
            $SQL->from(array('A' => 't_schedule'), array('COUNT(*) AS C'));
            $SQL->joinLeft(array('B' => 't_grade'), 'A.ACADEMIC_ID=B.ID', array());
            $SQL->where("A.ACADEMIC_ID = '" . $scheduleObject->ACADEMIC_ID . "'");
            $SQL->where("A.START_TIME = '" . $scheduleObject->START_TIME . "'");
            $SQL->where("A.END_TIME = '" . $scheduleObject->END_TIME . "'");
            $SQL->where("A.TERM = '" . $scheduleObject->TERM . "'");
            $SQL->where("A.SHORTDAY = '" . $scheduleObject->SHORTDAY . "'");
            $SQL->where("B.USE_OF_GROUPS = 1");
            $result = self::dbAccess()->fetchRow($SQL);
            //error_log($SQL);
            return $result ? $result->C : 0;
        }
        else
        {
            return 0;
        }
    }

    public static function checkAcaemicGroup($academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', 'COUNT(*) AS C');
        $SQL->where("PARENT = '" . $academicId . "'");
        $SQL->where("OBJECT_TYPE = 'SUBCLASS'");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        return $result ? $result->C : 0;
    }

    public static function getCountAssignedGroup($scheduleId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_link_schedule_academic', 'COUNT(*) AS C');
        $SQL->where("SCHEDULE_ID = '" . $scheduleId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        return $result ? $result->C : 0;
    }

    public static function displayAcademicGroup($scheduleObject)
    {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_grade'), array('NAME'));
        $SQL->joinLeft(array('B' => 't_link_schedule_academic'), 'A.ID=B.ACADEMIC_ID', array());
        $SQL->where("B.PARENT_ACADEMIC_ID = '" . $scheduleObject->ACADEMIC_ID . "'");
        $SQL->where("B.SCHEDULE_ID = '" . $scheduleObject->SCHEDULE_ID . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $GROUP_NAME = "";
        if ($result)
        {
            $j = 0;
            foreach ($result as $value)
            {
                if ($j != 0)
                    $GROUP_NAME .="<br>";
                $GROUP_NAME .= setShowText($value->NAME);
                ++$j;
            }
        }

        return $GROUP_NAME;
    }

    public static function checkRoomSharedSchedule($scheduleObject)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule', '*');
        $SQL->where("START_TIME = '" . $scheduleObject->START_TIME . "'");
        $SQL->where("END_TIME = '" . $scheduleObject->END_TIME . "'");
        $SQL->where("SUBJECT_ID = '" . $scheduleObject->SUBJECT_ID . "'");
        $SQL->where("SHORTDAY = '" . $scheduleObject->SHORTDAY . "'");
        $SQL->where("TERM = '" . $scheduleObject->TERM . "'");
        if ($scheduleObject->SCHOOLYEAR_ID)
        {
            $SQL->where("SCHOOLYEAR_ID = '" . $scheduleObject->SCHOOLYEAR_ID . "'");
        }
        $SQL->limitPage(0, 1);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ROOM_ID : null;
    }

    public static function deleteTecherFromAcademic($scheduleObject)
    {

        if ($scheduleObject)
        {

            if ($scheduleObject->ACADEMIC_ID)
            {

                $SQL = self::dbAccess()->select();
                $SQL->from('t_subject_teacher_class', 'COUNT(*) AS C');
                $SQL->where("TEACHER = '" . $scheduleObject->TEACHER_ID . "'");
                $SQL->where("ACADEMIC = '" . $scheduleObject->ACADEMIC_ID . "'");
                $SQL->where("SUBJECT = '" . $scheduleObject->SUBJECT_ID . "'");
                $SQL->where("SCHOOLYEAR = '" . $scheduleObject->SCHOOLYEAR_ID . "'");
                $SQL->where("GRADINGTERM = '" . $scheduleObject->TERM . "'");
                $result = self::dbAccess()->fetchRow($SQL);
                //error_log($SQL);
                $count = $result ? $result->C : 0;
                if ($count == 1)
                {
                    self::dbAccess()->delete('t_subject_teacher_class', array(
                        'TEACHER = ? ' => $scheduleObject->TEACHER_ID
                        , 'ACADEMIC = ? ' => $scheduleObject->ACADEMIC_ID
                        , 'SUBJECT = ? ' => $scheduleObject->SUBJECT_ID
                        , 'SCHOOLYEAR = ? ' => $scheduleObject->SCHOOLYEAR_ID
                        , 'GRADINGTERM = ? ' => $scheduleObject->TERM
                    ));
                }
            }

            if ($scheduleObject->TRAINING_ID)
            {
                $SQL = self::dbAccess()->select();
                $SQL->from('t_subject_teacher_training', 'COUNT(*) AS C');
                $SQL->where("TEACHER = '" . $scheduleObject->TEACHER_ID . "'");
                $SQL->where("TRAINING = '" . $scheduleObject->TRAINING_ID . "'");
                $SQL->where("SUBJECT = '" . $scheduleObject->SUBJECT_ID . "'");
                $SQL->where("TERM = '" . $scheduleObject->TERM . "'");
                $result = self::dbAccess()->fetchRow($SQL);
                //error_log($SQL);
                $count = $result ? $result->C : 0;
                if ($count == 1)
                {
                    self::dbAccess()->delete('t_subject_teacher_training', array(
                        'TEACHER = ? ' => $scheduleObject->TEACHER_ID
                        , 'TRAINING = ? ' => $scheduleObject->TRAINING_ID
                        , 'SUBJECT = ? ' => $scheduleObject->SUBJECT_ID
                        , 'TERM = ? ' => $scheduleObject->TERM
                    ));
                }
            }
        }
    }

    public static function updateSharedSchedule($fromObject)
    {

        if ($fromObject)
        {
            if ($fromObject->START_TIME && $fromObject->END_TIME)
            {
                $WHERE = self::dbAccess()->quoteInto("SHARED_FROM = ?", $fromObject->ID);
                $SAVEDATA["START_TIME"] = $fromObject->START_TIME;
                $SAVEDATA["END_TIME"] = $fromObject->END_TIME;
                $SAVEDATA["TEACHER_ID"] = $fromObject->TEACHER_ID;
                $SAVEDATA["ROOM_ID"] = $fromObject->ROOM_ID;
                self::dbAccess()->update('t_schedule', $SAVEDATA, $WHERE);
            }
        }
    }

}

?>