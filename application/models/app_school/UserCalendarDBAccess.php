<?php

///////////////////////////////////////////////////////////
// @Thou Morng Sothearung Software Developer
// Date: 11.02.2011
// 03 Rue des Pibleus, Bailly Romainvilliers, France
// VIKENSOFT
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/SchooleventDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/student/StudentAttendanceDBAccess.php';
require_once 'models/app_school/AbsentTypeDBAccess.php';
require_once 'models/app_school/examination/ExaminationDBAccess.php';

require_once 'models/app_school/club/ClubDBAccess.php';
require_once setUserLoacalization();

class UserCalendarDBAccess {

    const T_USER_CALENDAR = "t_user_calendar";

    public $DB_ACCESS = null;
    public $_TOSTRING = null;
    public $SELECT = null;

    public function __construct() {
        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->SELECT = $this->DB_ACCESS->select();
        $this->_TOSTRING = $this->SELECT->__toString();
    }

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getCalendarById($id) {
        $this->SELECT->from(self::T_USER_CALENDAR);
        $this->SELECT->where("ID = ?", $id);

        $stmt = $this->DB_ACCESS->query($this->SELECT);
        return $stmt->fetch();
    }

    public function getUserCalendarByUserId($userId) {
        $this->SELECT = $this->DB_ACCESS->select();
        $this->SELECT->from(self::T_USER_CALENDAR);
        $this->SELECT->where("USER_ID = ?", $userId);

        //$SQL = $this->SELECT->__toString();
        $stmt = $this->DB_ACCESS->query($this->SELECT);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function jsonLoadUserCalendar() {

        switch (UserAuth::getUserType()) {

            case "INSTRUCTOR":
            case "TEACHER":
                $evt_params['classId'] = 0;
                $evt_params['user_type'] = 1;
                $evt_params['teacherId'] = Zend_Registry::get('USERID');
                $school_events = $this->jsonLoadSchoolEvent($evt_params);
                $club_events = $this->jsonLoadClub($evt_params);
                break;
            case "STUDENT" :
                $DB_STUDENT = StudentDBAccess::getInstance();
                $USER = $DB_STUDENT->getStudentDataFromId(Zend_Registry::get('USERID'));

                $evt_params['classId'] = 0;
                $evt_params['user_type'] = 2;
                $evt_params['studentId'] = Zend_Registry::get('USERID');
//                $evt_params['current_classId'] = $USER['CURRENT_CLASS_ID'];
//                $evt_params['current_gradeId'] = $USER['CURRENT_GRADE_ID'];
                $exam_events = $this->jsonLoadStudentExams($evt_params);
                $school_events = $this->jsonLoadSchoolEvent($evt_params);
                $student_attendance = $this->jsonLoadStudentAttendance($evt_params);
                $club_events = $this->jsonLoadClub($evt_params);

                break;
            case "SUPERADMIN":
            case "ADMIN":
            default:
                $evt_params['type'] = 1;
                $evt_params['classId'] = 0;
                $evt_params['user_type'] = 0;
                $exam_events = $this->jsonLoadExaminations($evt_params);
                $school_events = $this->jsonLoadSchoolEvent($evt_params);
                $club_events = $this->jsonLoadClub($evt_params);
                break;
        }

        $entries = $this->getUserCalendarByUserId(Zend_Registry::get("USERID"));
        $data = array();
        $i = 0;
        if ($entries) {
            foreach ($entries as $value) {

                $data[$i]["id"] = $value->ID;
                $data[$i]["cid"] = $value->TYPE;
                $data[$i]["title"] = $value->TITLE;
                $data[$i]["start"] = $value->START_DATE . "T" . $value->START_HOUR;
                $data[$i]["end"] = $value->END_DATE . "T" . $value->END_HOUR;
                $data[$i]["notes"] = $value->NOTES;
                $data[$i]["rem"] = $value->REMINDER;
                $data[$i]["ad"] = $value->ALL_DAY;
                $data[$i]["loc"] = $value->LOCATION;
                $data[$i]["n"] = 0;

                $i++;
            }
        }

        $data = isset($school_events) ? array_merge($data, $school_events) : $data;
        #$data = isset($exam_events) ? array_merge($data, $exam_events) : $data;
        #$data = isset($student_attendance) ? array_merge($data, $student_attendance) : $data;
        #$data = isset($club_events) ? array_merge($data, $club_events) : $data;

        return array(
            "success" => true,
            "data" => $data,
            "totalCount" => count($data)
        );
    }

    // club event in calendar /////
    public function jsonLoadclub($params) {

        $params["status"] = 1;
        $entries = ClubDBAccess::getAllClubeventsQuery($params);

        $data = array();
        $i = 0;
        if ($entries) {
            foreach ($entries as $value) {
                $data[$i]["id"] = $value->ID;
                $data[$i]["cid"] = 6;
                $START_HOUR = $value->START_HOUR ? $value->START_HOUR . ':00' : "00:00:00";
                $END_HOUR = $value->END_HOUR ? $value->END_HOUR . ':00' : "00:00:00";

                $data[$i]["title"] = $value->NAME;
                $data[$i]["start"] = $value->START_DATE . "T" . $START_HOUR;
                $data[$i]["end"] = $value->END_DATE . "T" . $END_HOUR;
                $data[$i]["notes"] = "";
                $data[$i]["rem"] = "";
                if ($START_HOUR == "00:00:00" && $END_HOUR == "00:00:00")
                    $data[$i]["ad"] = 1;
                else
                    $data[$i]["ad"] = 0;
                $data[$i]["loc"] = "";
                $data[$i]["n"] = 0;


                $i++;
            }
        }

        return $data;
    }

    //
    public function jsonLoadSchoolEvent($params) {

        $entries = $this->sqlSchoolEvent($params);

        $data = array();
        $i = 0;
        if ($entries) {
            foreach ($entries as $value) {
                $data[$i]["id"] = "SC_" . $value->ID;
                if ($value->CLASS_ID == 0)
                    $data[$i]["cid"] = 2;
                elseif ($value->CLASS_ID != 0 && $value->TEACHER_ID)
                    $data[$i]["cid"] = 3;

                $START_HOUR = $value->START_HOUR ? $value->START_HOUR . ':00' : "00:00:00";
                $END_HOUR = $value->END_HOUR ? $value->END_HOUR . ':00' : "00:00:00";

                $data[$i]["title"] = $value->NAME;
                $data[$i]["start"] = $value->START_DATE . "T" . $START_HOUR;
                $data[$i]["end"] = $value->END_DATE . "T" . $END_HOUR;
                $data[$i]["notes"] = "";
                $data[$i]["rem"] = "";
                if ($START_HOUR == "00:00:00" && $END_HOUR == "00:00:00")
                    $data[$i]["ad"] = 1;
                else
                    $data[$i]["ad"] = 0;
                $data[$i]["loc"] = "";
                $data[$i]["n"] = 0;

                $i++;
            }
        }

        return $data;
    }

    public function jsonLoadStudentAttendance($params) {

        $DB_STUDENT_ATTENDANCE = StudentAttendanceDBAccess::getInstance();
        $entries = $DB_STUDENT_ATTENDANCE->getAllAttendancesQuery($params);

        $data = array();
        $i = 0;
        if (is_array($entries)) {
            foreach ($entries as $value) {

                $absentType = AbsentTypeDBAccess::findObjectFromId($value->ABSENT_TYPE);

                $data[$i]["id"] = "SA_" . $value->ID;
                $data[$i]["cid"] = 5;

                if ($absentType) {
                    $data[$i]["title"] = $absentType->NAME;
                }

                $data[$i]["start"] = $value->START_DATE;
                $data[$i]["end"] = $value->END_DATE;
                $data[$i]["notes"] = "";
                $data[$i]["rem"] = "";
                $data[$i]["ad"] = 1;
                $data[$i]["loc"] = "";
                $data[$i]["n"] = 0;

                $i++;
            }
        }

        return $data;
    }

    public function jsonLoadStudentExams($params) {
        $DB_EXAM = ExaminationDBAccess::getInstance();
        $entries = $DB_EXAM->getStudentAllExam($params);

        $data = array();
        $i = 0;
        if (is_array($entries)) {
            foreach ($entries as $value) {
                $data[$i]["id"] = "SE_" . $value->ID;
                $data[$i]["cid"] = 4;
                $data[$i]["title"] = $value->SUBJECT_NAME;
                $data[$i]["room"] = $value->ROOM_NAME;
                $data[$i]["start"] = $value->START_DATE . "T" . $value->START_TIME;
                $data[$i]["end"] = $value->START_DATE . "T" . $value->END_TIME;
                $data[$i]["notes"] = "";
                $data[$i]["rem"] = "";
                $data[$i]["ad"] = 0;
                $data[$i]["loc"] = "";
                $data[$i]["n"] = 0;

                $i++;
            }
        }

        return $data;
    }

    public function jsonLoadExaminations($params) {

        $params["status"] = 1;
        $entries = ExaminationDBAccess::getSQLExamination($params);

        $data = array();
        $i = 0;
        if ($entries) {
            foreach ($entries as $value) {
                $data[$i]["id"] = "SE_" . $value->EXM_ID;
                $data[$i]["cid"] = 4;
                $data[$i]["title"] = $value->SUBJECT_NAME;
                $data[$i]["grade"] = $value->GRADE_NAME;
                $data[$i]["start"] = $value->START_DATE . "T" . $value->START_TIME;
                $data[$i]["end"] = $value->START_DATE . "T" . $value->END_TIME;
                $data[$i]["ad"] = 0;
                $data[$i]["n"] = 0;

                $i++;
            }
        }

        return $data;
    }

    public function jsonAddUserCalendar($params) {
        $entries = isset($params["data"]) ? json_decode($params["data"]) : "";
        if ($entries) {
            if (!is_array($entries))
                $entries = array($entries);

            $i = 0;
            foreach ($entries as $key => $value) {
                $id = isset($value->id) ? $value->id : "";
                if (!$this->getCalendarById($id)) {
                    $data = array();
                    $data[$i]["ID"] = null;
                    $data[$i]["TYPE"] = isset($value->cid) ? addText($value->cid) : "";
                    list($data[$i]["START_DATE"], $data[$i]["START_HOUR"]) = isset($value->start) ? $this->splitDateTime($value->start) : "";
                    list($data[$i]["END_DATE"], $data[$i]["END_HOUR"]) = isset($value->end) ? $this->splitDateTime($value->end) : "";

                    $data[$i]["ALL_DAY"] = isset($value->ad) ? addText($value->ad) : "";
                    $data[$i]["TITLE"] = isset($value->title) ? addText($value->title) : "";
                    $data[$i]["LOCATION"] = isset($value->loc) ? addText($value->loc) : "";
                    $data[$i]["NOTES"] = isset($value->notes) ? addText($value->notes) : "";
                    $data[$i]["URL"] = isset($value->url) ? addText($value->url) : "";
                    $data[$i]["REMINDER"] = isset($value->rem) ? addText($value->rem) : "";

                    $data[$i]["ID"] = $this->addUserCalendar($data[$i]);
                }
            }
        }
        if ($data) {
            $a = array();
            $i = 0;
            foreach ($data as $key => $value) {
                $a[$i]["id"] = $data[$i]["ID"];
                $a[$i]["cid"] = $data[$i]["TYPE"];
                $a[$i]["start"] = $data[$i]["START_DATE"] . "T" . $data[$i]["START_HOUR"];
                $a[$i]["end"] = $data[$i]["END_DATE"] . "T" . $data[$i]["END_HOUR"];
                $a[$i]["ad"] = $data[$i]["ALL_DAY"];
                $a[$i]["title"] = $data[$i]["TITLE"];
                $a[$i]["notes"] = $data[$i]["NOTES"];
                $a[$i]["url"] = $data[$i]["URL"];
                $a[$i]["rem"] = $data[$i]["REMINDER"];
                //$a[$i]["n"] = 0;
                $i++;
            }
        }

        return array(
            "success" => true,
            "data" => $a
        );
    }

    public function jsonUpdateUserCalendar($params) {
        $entries = isset($params["data"]) ? json_decode($params["data"]) : "";
        if ($entries) {
            if (!is_array($entries))
                $entries = array($entries);

            foreach ($entries as $value) {
                $id = isset($value->id) ? $value->id : "";

                if ($this->getCalendarById($id)) {
                    $data = array();

                    if (isset($value->cid))
                        $data["TYPE"] = isset($value->cid) ? addText($value->cid) : "";
                    if (isset($value->start))
                        list($data["START_DATE"], $data["START_HOUR"]) = isset($value->start) ? $this->splitDateTime($value->start) : "";
                    if (isset($value->end))
                        list($data["END_DATE"], $data["END_HOUR"]) = isset($value->end) ? $this->splitDateTime($value->end) : "";

                    if (isset($value->ad))
                        $data["ALL_DAY"] = isset($value->ad) ? addText($value->ad) : "";
                    if (isset($value->title))
                        $data["TITLE"] = isset($value->title) ? addText($value->title) : "";
                    if (isset($value->loc))
                        $data["LOCATION"] = isset($value->loc) ? addText($value->loc) : "";
                    if (isset($value->notes))
                        $data["NOTES"] = isset($value->notes) ? addText($value->notes) : "";
                    if (isset($value->url))
                        $data["URL"] = isset($value->url) ? addText($value->url) : "";
                    if (isset($value->rem))
                        $data["REMINDER"] = isset($value->rem) ? addText($value->rem) : "";

                    $this->updateUserCalendar($data, $id);
                }
            }
        }

        return array("success" => true);
    }

    public function jsonDeleteUserCalendar($params) {
        $GuId = isset($params["data"]) ? json_decode($params["data"]) : "";
        if ($GuId) {
            if ($this->getCalendarById($GuId)) {

                $this->deleteUserCalendar($GuId);
            }
        }

        return array("success" => true);
    }

    public function sqlSchoolEvent($params) {
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $current_classId = isset($params["current_classId"]) ? addText($params["current_classId"]) : "";

        $this->SELECT = $this->DB_ACCESS->select();
        $this->SELECT->from('t_schoolevent');

        if ($classId || $classId == 0 || $current_classId) {
            if ($classId || $classId == 0)
                $this->SELECT->where('CLASS_ID = ?', $classId);
            if ($current_classId)
                $this->SELECT->orWhere('CLASS_ID = ?', $current_classId);
        }
        if ($teacherId) {
            $this->SELECT->orWhere('TEACHER_ID = ?', $teacherId);
        }
        if ($schoolyearId)
            $this->SELECT->where('SCHOOL_YEAR = ?', $schoolyearId);

        $this->SELECT->where('STATUS = ?', 1);

        //$SQL = $this->SELECT->__toString();
        $stmt = $this->DB_ACCESS->query($this->SELECT);

        return $stmt->fetchAll();
    }

    public function sqlSubjectGradeByTeacherId($params) {
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $this->SELECT = $this->DB_ACCESS->select();
        $this->SELECT->from('t_subject_teacher_class');

        if ($teacherId)
            $this->SELECT->where('TEACHER_ID = ?', $teacherId);
        if ($schoolyearId)
            $this->SELECT->where('SCHOOL_YEAR = ?', $schoolyearId);

        //$SQL = $this->SELECT->__toString();
        $stmt = $this->DB_ACCESS->query($this->SELECT);

        return $stmt->fetchAll();
    }

    public function addUserCalendar($params) {

        $params["USER_ID"] = Zend_Registry::get("USERID");
        $result = $this->DB_ACCESS->insert("t_user_calendar", $params);
        $id = $this->DB_ACCESS->lastInsertId();

        return $result ? $id : false;
    }

    public function updateUserCalendar($params, $id) {

        $where = $this->DB_ACCESS->quoteInto("ID = ?", $id);
        $result = $this->DB_ACCESS->update(self::T_USER_CALENDAR, $params, $where);

        return $result ? $result : false;
    }

    public function deleteUserCalendar($id) {

        $where = $this->DB_ACCESS->quoteInto("ID = ?", $id);
        $result = $this->DB_ACCESS->delete(self::T_USER_CALENDAR, $where);

        return $result ? $result : false;
    }

    public function splitDateTime($dateTime) {

        if ($dateTime) {
            $dateTimeTab = explode("T", $dateTime);
            $date = $dateTimeTab[0];
            $time = $dateTimeTab[1];
        }
        if ($date && $time)
            return array($date, $time);
        else {
            return false;
        }
    }

}

?>
