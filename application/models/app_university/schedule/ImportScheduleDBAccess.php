<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 11.10.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once ('excel/excel_reader2.php');
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/room/RoomDBAccess.php';
require_once 'models/app_university/schedule/ScheduleDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class ImportScheduleDBAccess extends ScheduleDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {

        parent::__construct();
    }

    public function setImportSheets($sheets, $academicId, $term, $shortday, $schoolyearId) {

        $SAVEDATA = array();

        $roomId = "";
        $subjectId = "";
        $teacherId = "";

        for ($iCol = 1; $iCol <= $sheets['numCols']; $iCol++) {
            $field = isset($sheets['cells'][1][$iCol]) ? $sheets['cells'][1][$iCol] : "";
            switch (trim($field)) {
                case "TIME":
                    $Col_TIME = $iCol;
                    break;
                case "EVENT_TYPE":
                    $Col_EVENT_TYPE = $iCol;
                    break;
                case "SUBJECT_SHORT_OR_EVENT":
                    $Col_SUBJECT_SHORT = $iCol;
                    break;
                case "ROOM_SHORT":
                    $Col_ROOM_SHORT = $iCol;
                    break;
                case "TEACHER_CODE":
                    $Col_TEACHER_CODE = $iCol;
                    break;
            }
        }

        for ($i = 1; $i <= $sheets['numRows']; $i++) {

            $scheduleTime = isset($sheets['cells'][$i + 1][$Col_TIME]) ? $sheets['cells'][$i + 1][$Col_TIME] : "";
            $eventType = isset($sheets['cells'][$i + 1][$Col_EVENT_TYPE]) ? $sheets['cells'][$i + 1][$Col_EVENT_TYPE] : "";
            $event = isset($sheets['cells'][$i + 1][$Col_SUBJECT_SHORT]) ? $sheets['cells'][$i + 1][$Col_SUBJECT_SHORT] : "";
            $roomShort = isset($sheets['cells'][$i + 1][$Col_ROOM_SHORT]) ? $sheets['cells'][$i + 1][$Col_ROOM_SHORT] : "";
            $teacherCode = isset($sheets['cells'][$i + 1][$Col_TEACHER_CODE]) ? $sheets['cells'][$i + 1][$Col_TEACHER_CODE] : "";

            if ($roomShort)
                $roomId = $this->findRoomId(strtoupper(trim($roomShort)));

            if ($teacherCode) {
                $teacherObject = self::findTeacherByCode(strtoupper(trim($teacherCode)));
                if ($teacherObject) {
                    $teacherId = $teacherObject->ID;
                }
            }

            if ($scheduleTime) {
                $TIME_STR = explode("-", trim($scheduleTime));
                $startTime = isset($TIME_STR[0]) ? timeStrToSecond(trim($TIME_STR[0])) : 0;
                $endTime = isset($TIME_STR[1]) ? timeStrToSecond(trim($TIME_STR[1])) : 0;
            }

            //error_log("TeacherCode: ".$teacherId." RoomShort: ".$roomId." EventType: ".$eventType. " Event: ".$subjectId. " Term: ".$term." StartTime: ".$startTime." EndTime: ".$endTime." academicId: ".$academicId." ShortDay: ".$shortday);

            if ($scheduleTime) {

                if ($startTime && $endTime) {

                    $ERROR_TIME_HAS_BEEN_USED = $this->checkStartTimeANDEndTime(
                            $startTime
                            , $endTime
                            , $shortday
                            , $academicId
                            , $term
                            , false
                    );

                    if (!$ERROR_TIME_HAS_BEEN_USED) {

                        $SAVEDATA["CODE"] = createCode();
                        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                        $SAVEDATA["GUID"] = generateGuid();
                        $SAVEDATA["SHORTDAY"] = $shortday;
                        $SAVEDATA["SCHOOLYEAR_ID"] = $schoolyearId;
                        $SAVEDATA["TERM"] = $term;
                        $SAVEDATA["ACADEMIC_ID"] = $academicId;

                        if ($event != "") {
                            if ($roomId) {
                                $SAVEDATA["ROOM_ID"] = $roomId;
                            }
                        } else {
                            $SAVEDATA["ROOM_ID"] = "";
                        }

                        if ($eventType) {

                            switch ($eventType) {
                                case 1:
                                    if ($event && $academicId && $teacherCode) {
                                        $SAVEDATA["EVENT"] = "";
                                        $SAVEDATA["SCHEDULE_TYPE"] = 1;
                                        $SAVEDATA["TEACHER_ID"] = $teacherId;
                                        if ($event)
                                            $subjectId = $this->findSubjectId(strtoupper(trim($event)), $teacherId);
                                        $SAVEDATA["SUBJECT_ID"] = $subjectId;
                                    }
                                    break;
                                case 2:
                                    if ($event && $academicId) {
                                        $SAVEDATA["EVENT"] = $event;
                                        $SAVEDATA["SCHEDULE_TYPE"] = 2;
                                        $SAVEDATA["SUBJECT_ID"] = "";
                                        $SAVEDATA["TEACHER_ID"] = "";
                                    }
                                    break;
                            }

                            $SAVEDATA["STATUS"] = 1;
                            $SAVEDATA["START_TIME"] = $startTime;
                            $SAVEDATA["END_TIME"] = $endTime;

                            if ($subjectId && $academicId && $teacherId && $term) {
                                self::dbAccess()->insert(self::TABLE_SCHEDULE, $SAVEDATA);
                                $scheduleId = self::dbAccess()->lastInsertId();
                                if ($scheduleId) {
                                    self::addSubjectTeacherClass($subjectId, $teacherId, $academicId, $term);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function importXLS($params) {

        $_FILES['xlsfile']['type'];

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : false;
        $term = isset($params["term"]) ? addText($params["term"]) : false;

        $classObject = AcademicDBAccess::findGradeFromId($academicId);

        $schoolyearId = $classObject->SCHOOL_YEAR;

        $firstCondition = array(
            "ACADEMIC_ID = '" . $academicId . "'"
            , "SCHOOLYEAR_ID = '" . $schoolyearId . "'"
            , "TERM = '" . $term . "'"
        );

        $secondCondition = array(
            "ACADEMIC = '" . $academicId . "'"
            , "SCHOOLYEAR = '" . $schoolyearId . "'"
            , "GRADINGTERM = '" . $term . "'"
        );

        self::dbAccess()->delete(self::TABLE_SCHEDULE, $firstCondition);
        self::dbAccess()->delete('t_subject_teacher_class', $secondCondition);

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($_FILES["xlsfile"]['tmp_name']);

        if ($classObject->MO)
            $MO_SHEETS = $xls->sheets[0];

        if ($classObject->TU)
            $TU_SHEETS = $xls->sheets[1];

        if ($classObject->WE)
            $WE_SHEETS = $xls->sheets[2];

        if ($classObject->TH)
            $TH_SHEETS = $xls->sheets[3];

        if ($classObject->FR)
            $FR_SHEETS = $xls->sheets[4];

        if ($classObject->SA)
            $SA_SHEETS = $xls->sheets[5];

        if ($classObject->SU)
            $SU_SHEETS = $xls->sheets[6];

        if ($classObject->MO)
            $this->setImportSheets($MO_SHEETS, $academicId, $term, "MO", $schoolyearId);

        if ($classObject->TU)
            $this->setImportSheets($TU_SHEETS, $academicId, $term, "TU", $schoolyearId);

        if ($classObject->WE)
            $this->setImportSheets($WE_SHEETS, $academicId, $term, "WE", $schoolyearId);

        if ($classObject->TH)
            $this->setImportSheets($TH_SHEETS, $academicId, $term, "TH", $schoolyearId);

        if ($classObject->FR)
            $this->setImportSheets($FR_SHEETS, $academicId, $term, "FR", $schoolyearId);

        if ($classObject->SA)
            $this->setImportSheets($SA_SHEETS, $academicId, $term, "SA", $schoolyearId);

        if ($classObject->SU)
            $this->setImportSheets($SU_SHEETS, $academicId, $term, "SU", $schoolyearId);

        return array(
            "success" => true
        );
    }

    public function checkTeacherInSchedule($starttime, $endtime, $shortday, $term) {

        $SQLFirst = "";
        $SQLFirst .= " SELECT *";
        $SQLFirst .= " FROM " . self::TABLE_SCHEDULE . "";
        $SQLFirst .= " WHERE 1=1";
        $SQLFirst .= " AND ('" . $starttime . "' BETWEEN START_TIME AND END_TIME)";
        $SQLFirst .= " OR ('" . $endtime . "' BETWEEN START_TIME AND END_TIME)";
        //error_log($SQLFirst);
        $resultRowsFirst = self::dbAccess()->fetchAll($SQLFirst);

        $SQLSecond = "";
        $SQLSecond .= " SELECT *";
        $SQLSecond .= " FROM " . self::TABLE_SCHEDULE . "";
        $SQLSecond .= " WHERE 1=1";
        $SQLSecond .= " AND (START_TIME BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
        $SQLSecond .= " OR (END_TIME BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
        //error_log($SQLSecond);
        $resultRowsSecond = self::dbAccess()->fetchAll($SQLSecond);

        $FIRST_DATA = array();
        if ($resultRowsFirst) {
            foreach ($resultRowsFirst as $key => $value) {
                if ($value->SHORTDAY == $shortday) {
                    if ($value->TERM == $term) {
                        if ($value->TEACHER_ID)
                            $FIRST_DATA[$value->TEACHER_ID] = $value->TEACHER_ID;
                    }
                }
            }
        }

        $SECOND_DATA = array();
        if ($resultRowsSecond) {
            foreach ($resultRowsSecond as $key => $value) {
                if ($value->SHORTDAY == $shortday) {
                    if ($value->TERM == $term) {
                        if ($value->TEACHER_ID)
                            $SECOND_DATA[$value->TEACHER_ID] = $value->TEACHER_ID;
                    }
                }
            }
        }

        $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;

        return $CHECK_DATA;
    }

    protected function checkImportRoom($starttime, $endtime, $shortday, $term) {

        $SQLFirst = "";
        $SQLFirst .= " SELECT *";
        $SQLFirst .= " FROM " . self::TABLE_SCHEDULE . "";
        $SQLFirst .= " WHERE 1=1";
        $SQLFirst .= " AND ('" . $starttime . "' BETWEEN START_TIME AND END_TIME)";
        $SQLFirst .= " OR ('" . $starttime . "' BETWEEN START_TIME AND END_TIME)";
        //error_log($SQLFirst);
        $resultRowsFirst = self::dbAccess()->fetchAll($SQLFirst);

        $SQLSecond = "";
        $SQLSecond .= " SELECT *";
        $SQLSecond .= " FROM " . self::TABLE_SCHEDULE . "";
        $SQLSecond .= " WHERE 1=1";
        $SQLSecond .= " AND (START_TIME BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
        $SQLSecond .= " OR (END_TIME BETWEEN '" . $starttime . "' AND '" . $endtime . "')";
        //error_log($SQLSecond);
        $resultRowsSecond = self::dbAccess()->fetchAll($SQLSecond);

        $FIRST_DATA = array();
        if ($resultRowsFirst) {
            foreach ($resultRowsFirst as $key => $value) {
                if ($value->SHORTDAY == $shortday) {
                    if ($value->TERM == $term) {
                        if ($value->ROOM_ID)
                            $FIRST_DATA[$value->ROOM_ID] = $value->ROOM_ID;
                    }
                }
            }
        }

        $SECOND_DATA = array();
        if ($resultRowsSecond) {
            foreach ($resultRowsSecond as $key => $value) {
                if ($value->SHORTDAY == $shortday) {
                    if ($value->TERM == $term) {
                        if ($value->ROOM_ID)
                            $SECOND_DATA[$value->ROOM_ID] = $value->ROOM_ID;
                    }
                }
            }
        }

        $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;

        return $CHECK_DATA;
    }

    public function checkImportTeacher($starttime, $endtime, $teacherId, $subjectId, $shortday, $term) {

        $CHECK_VALUE = false;

        $query = self::dbAccess()->select();
        $query->from(self::TABLE_TEACHER_SUBJECT, 'COUNT(*) AS C');
        $query->where("TEACHER = '" . $teacherId . "'");
        $query->where("SUBJECT = '" . $subjectId . "'");
        //echo $query->__toString();
        $result = self::dbAccess()->fetchRow($query);
        $TEACHER_SUBJECT_COUNT = $result ? $result->C : 0;

        $TEACHER_IN_SCHEDULE_DATA = $this->checkTeacherInSchedule(
                $starttime
                , $endtime
                , $shortday
                , $term
        );

        if ($TEACHER_SUBJECT_COUNT) {

            if (!in_array($teacherId, $TEACHER_IN_SCHEDULE_DATA)) {
                $CHECK_VALUE = false;
            } else {
                $CHECK_VALUE = true;
            }
        } else {
            $CHECK_VALUE = true;
        }

        return $CHECK_VALUE;
    }

    public function findSubjectId($short, $teacherId) {

        $query = self::dbAccess()->select();
        $query->from(array('A' => 't_subject'), array("ID AS SUBJECT_ID"));
        $query->joinLeft(array('B' => 't_teacher_subject'), 'A.ID=B.SUBJECT', array());
        $query->where("A.SHORT = '" . $short . "'");
        $query->where("B.TEACHER = '" . $teacherId . "'");
        $query->group('A.ID');
        //error_log($query->__toString()."\n");
        $result = self::dbAccess()->fetchRow($query);
        return $result ? $result->SUBJECT_ID : false;
    }

    public function findRoomId($code) {

        $query = self::dbAccess()->select();
        $query->from('t_room');
        $query->where("SHORT = '" . $code . "'");
        $stmt = self::dbAccess()->query($query);
        $result = $stmt->fetch();
        return $result ? $result->ID : null;
    }

    public static function findTeacherByCode($codeId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_staff', '*');
        $SQL->where('CODE = ?', $codeId);
        //echo $SQL->__toString();
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function checkSubjectTeacherClassTerm($subjectId, $teacherId, $academicId, $schoolyearId, $term) {

        $SQL = self::dbAccess()->select()
                ->from("t_subject_teacher_class", array("C" => "COUNT(*)"))
                ->where("SUBJECT = '" . $subjectId . "'")
                ->where("TEACHER = '" . $teacherId . "'")
                ->where("SCHOOLYEAR = '" . $schoolyearId . "'")
                ->where("ACADEMIC = '" . $academicId . "'")
                ->where("GRADINGTERM = '" . $term . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function addSubjectTeacherClass($subjectId, $teacherId, $academicId, $term) {

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $CHECK = self::checkSubjectTeacherClassTerm(
                        $subjectId
                        , $teacherId
                        , $academicId
                        , $academicObject->SCHOOL_YEAR
                        , $term
        );

        $SQL = "INSERT INTO t_subject_teacher_class";
        $SQL .= " SET";
        $SQL .= " GRADINGTERM='" . $term . "'";
        $SQL .= " ,CAMPUS='" . $academicObject->CAMPUS_ID . "'";
        $SQL .= " ,SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'";
        $SQL .= " ,SUBJECT='" . $subjectId . "'";
        $SQL .= " ,ACADEMIC='" . $academicObject->ID . "'";
        $SQL .= " ,GRADE='" . $academicObject->GRADE_ID . "'";
        $SQL .= " ,TEACHER='" . $teacherId . "'";
        if (!$CHECK) self::dbAccess()->query($SQL);
    }

}

?>