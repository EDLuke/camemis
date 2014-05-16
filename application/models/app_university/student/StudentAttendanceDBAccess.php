<?php

///////////////////////////////////////////////////////////
// Vikensoft UG Partent Nr.....
// @Morng Thou
// 27.02.2011
// 03 Rue des Piblues Bailly Romainvilliers
// @VIK Modify....
// 24.05.2011
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentAcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/training/TrainingDBAccess.php';
require_once 'models/app_university/SchooleventDBAccess.php';
require_once 'models/app_university/AbsentTypeDBAccess.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/sms/SendSMSDBAccess.php';
require_once 'models/app_university/schedule/DayScheduleDBAccess.php';
require_once setUserLoacalization();

class StudentAttendanceDBAccess extends StudentDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    static function getWeekDay() {
        $tab = array(
            0 => "SU"
            , 1 => "MO"
            , 2 => "TU"
            , 3 => "WE"
            , 4 => "TH"
            , 5 => "FR"
            , 6 => "SA"
        );
        return $tab;
    }

    public function getAttendanceFromId($Id) {
        $data = array();
        $facette = self::findAttendanceFromId($Id);

        if ($facette) {

            $data["ID"] = $facette->ID;
            $data["STUDENT_ID"] = $facette->STUDENT_ID;
            $data["STATUS"] = $facette->STATUS;
            $data["CLASS_ID"] = $facette->CLASS_ID;
            $data["ABSENT_TYPE"] = $facette->ABSENT_TYPE;
            $data["ACTION_TYPE"] = $facette->ACTION_TYPE;

            if ($facette->TRAINING_ID) {
                $TRAINING_OBJECT = TrainingDBAccess::findTrainingFromId($facette->TRAINING_ID);
                $data["CHOOSE_TRAINING_NAME"] = $TRAINING_OBJECT->NAME;
                $data["HIDDEN_TRAINING"] = $facette->TRAINING_ID;
            }

            $data["START_DATE"] = getShowDate($facette->START_DATE);
            $data["END_DATE"] = getShowDate($facette->END_DATE);

            $data["COUNT_DATE"] = $facette->COUNT_DATE;

            if ($facette->CAL_DATE) {
                $caldate = explode(",", $facette->CAL_DATE);

                if ($caldate) {
                    foreach ($caldate as $value) {
                        $data["COUNT_DATE"] .= "<br>" . getShowDate($value);
                    }
                }
            }

            $data["TERM"] = $facette->TERM;
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);

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

    public static function getScheduleFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_schedule";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findAttendanceFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_student_attendance";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getAllAttendancesQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $academicId= isset($params["classId"]) ? (int) $params["classId"] : "";
        $campusId = isset($params["campusId"]) ? addText($params["campusId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $term = isset($params["term"]) ? addText($params["term"]) : "";

        $gender = isset($params["GENDER"]) ? addText($params["GENDER"]) : "";
        $searchGrade = isset($params["CHOOSE_GRADE"]) ? addText($params["CHOOSE_GRADE"]) : "";
        $searchAbsencetype = isset($params["ABSENT_TYPE"]) ? addText($params["ABSENT_TYPE"]) : "";
        $searchSubjecttype = isset($params["subject_type"]) ? $params["subject_type"] : "";
        $code = isset($params["CODE"]) ? addText($params["CODE"]) : "";
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : "";
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : "";
        $studentschoolId = isset($params["STUDENT_SCHOOL_ID"]) ? addText($params["STUDENT_SCHOOL_ID"]) : "";
        $target = isset($params["TARGET"]) ? addText($params["TARGET"]) : "";
        $startDate = isset($params["START_DATE"]) ? $params["START_DATE"] : "";
        $endDate = isset($params["END_DATE"]) ? $params["END_DATE"] : "";

        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : "";

        if ($searchGrade) {

            $academicObject = AcademicDBAccess::findGradeFromId($searchGrade);

            switch ($academicObject->OBJECT_TYPE) {
                case "CAMPUS":
                    $campusId = $academicObject->ID;
                    break;
                case "GRADE":
                    $gradeId = $academicObject->ID;
                    break;
                case "SCHOOLYEAR":
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $gradeId = $academicObject->GRADE_ID;
                    break;
                case "CLASS":
                    $academicId= $academicObject->ID;
                    break;
            }

            ///@veasna
            if ($academicObject->EDUCATION_SYSTEM) {
                $target = "creditsystem";
            }
            ///
        }

        if ($academicId)
            $academicId= $academicId;

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.TRAINING_ID AS TRAINING_ID";
        $SQL .= " ,CONCAT(B.FIRSTNAME,' ', B.LASTNAME) AS STUDENT";
        $SQL .= " ,A.ABSENT_TYPE AS ABSENT_TYPE";
        $SQL .= " ,B.CODE AS CODE";
        $SQL .= " ,B.FIRSTNAME AS FIRSTNAME";
        $SQL .= " ,B.LASTNAME AS LASTNAME";
        $SQL .= " ,B.GENDER AS GENDER";
        $SQL .= " ,A.START_DATE AS START_DATE";
        $SQL .= " ,A.END_DATE AS END_DATE";
        $SQL .= " ,A.COUNT_DATE AS COUNT_DATE";
        $SQL .= " ,A.TERM AS TERM";
        $SQL .= " ,A.CREATED_BY AS CREATED_BY";
        $SQL .= " ,A.DESCRIPTION AS DESCRIPTION";
        $SQL .= " ,A.STATUS AS STATUS";
        $SQL .= " ,A.STUDENT_ID AS STUDENT_ID";
        $SQL .= " ,A.SUBJECT_ID AS SUBJECT_ID";

        //@soda
        $SQL .= " ,B.STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID";
        $SQL .= " ,B.LASTNAME_LATIN AS LASTNAME_LATIN";
        $SQL .= " ,B.FIRSTNAME_LATIN AS FIRSTNAME_LATIN";
        $SQL .= " ,B.PHONE AS PHONE";
        $SQL .= " ,B.EMAIL AS EMAIL";
        $SQL .= " ,B.DATE_BIRTH AS DATE_BIRTH";
        $SQL .= " ,A.CREATED_DATE AS CREATED_DATE";

        switch (strtoupper($target)) {
            case "GENERAL":
                $SQL .= " ,YEAR(B.DATE_BIRTH) AS BORN_YEAR";     // Chuy Thong 02/07/2012
                $SQL .= " ,E.NAME AS CLASS_NAME";
                $SQL .= " ,F.NAME AS SCHOOL_YEAR";
                break;
            case "TRAINING":
                $SQL .= " ,I.NAME AS TRAINING_NAME";
                $SQL .= " ,CONCAT(J.START_DATE,' ', J.END_DATE) AS TRAINING_TERM";
                $SQL .= " ,K.NAME AS TRAINING_LEVEL";
                break;
            ///@veasna
            case "CREDITSYSTEM":
                $SQL .= " ,YEAR(B.DATE_BIRTH) AS BORN_YEAR";     // Chuy Thong 02/07/2012
                $SQL .= " ,F.NAME AS SCHOOL_YEAR";
                break;
            ////
            default:
                $SQL .= " ,YEAR(B.DATE_BIRTH) AS BORN_YEAR";     // Chuy Thong 02/07/2012
                $SQL .= " ,E.NAME AS CLASS_NAME";
                $SQL .= " ,F.NAME AS SCHOOL_YEAR";

                $SQL .= " ,I.NAME AS TRAINING_NAME";
                $SQL .= " ,CONCAT(J.START_DATE,' ', J.END_DATE) AS TRAINING_TERM";
                $SQL .= " ,K.NAME AS TRAINING_LEVEL";
        }

        $SQL .= " FROM t_student_attendance AS A";
        $SQL .= " LEFT JOIN t_student AS B ON A.STUDENT_ID=B.ID";
        switch (strtoupper($target)) {
            case "GENERAL":
                $SQL .= " LEFT JOIN t_grade AS E ON E.ID=A.CLASS_ID";
                $SQL .= " LEFT JOIN t_academicdate AS F ON F.ID=E.SCHOOL_YEAR";
                $SQL .= " LEFT JOIN t_grade AS G ON G.ID=E.GRADE_ID";
                $SQL .= " LEFT JOIN t_grade AS H ON H.ID=E.CAMPUS_ID";
                break;
            case "TRAINING":
                $SQL .= " LEFT JOIN t_training AS I ON I.ID=A.TRAINING_ID";
                $SQL .= " LEFT JOIN t_training AS J ON J.ID=I.PARENT";
                $SQL .= " LEFT JOIN t_training AS K ON K.ID=I.LEVEL";
                break;
            ///@veasna    
            case "CREDITSYSTEM":
                $SQL .= " LEFT JOIN t_academicdate AS F ON F.ID=A.SCHOOLYEAR_ID";

                break;
            ////
            default:
                $SQL .= " LEFT JOIN t_grade AS E ON E.ID=A.CLASS_ID";
                $SQL .= " LEFT JOIN t_academicdate AS F ON F.ID=E.SCHOOL_YEAR";
                $SQL .= " LEFT JOIN t_grade AS G ON G.ID=E.GRADE_ID";
                $SQL .= " LEFT JOIN t_grade AS H ON H.ID=E.CAMPUS_ID";

                $SQL .= " LEFT JOIN t_training AS I ON I.ID=A.TRAINING_ID";
                $SQL .= " LEFT JOIN t_training AS J ON J.ID=I.PARENT";
                $SQL .= " LEFT JOIN t_training AS K ON K.ID=I.LEVEL";
        }

        $SQL .= " WHERE 1=1";

        if (strtoupper($target) != 'CREDITSYSTEM') { ///@veasna
            if ($campusId)
                $SQL .= " AND H.ID = " . $campusId . "";
            if ($gradeId)
                $SQL .= " AND G.ID = " . $gradeId . "";
        }
        if ($schoolyearId)
            $SQL .= " AND F.ID = '" . $schoolyearId . "'";
        if ($code)
            $SQL .= " AND B.CODE like '" . $code . "%'";
        if ($studentschoolId)
            $SQL .= " AND B.STUDENT_SCHOOL_ID like '" . $studentschoolId . "%'";
        if ($firstname)
            $SQL .= " AND B.FIRSTNAME like '" . $firstname . "%'";
        if ($lastname)
            $SQL .= " AND B.LASTNAME like '" . $lastname . "%'";
        if ($gender)
            $SQL .= " AND B.GENDER = " . $gender;
        if ($academicId)
            $SQL .= " AND A.CLASS_ID = " . $academicId. "";
        if ($studentId)
            $SQL .= " AND A.STUDENT_ID = '" . $studentId . "'";
        if ($term)
            $SQL .= " AND A.TERM = '" . $term . "'";
        if ($globalSearch) {
            $SQL .= " AND ((B.FIRSTNAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        if ($searchAbsencetype)
            $SQL .= " AND A.ABSENT_TYPE = " . $searchAbsencetype . "";

        if ($searchSubjecttype)
            $SQL .= " AND A.SUBJECT_ID = " . $searchSubjecttype . "";

        if ($startDate && $endDate) {
            $SQL .= " AND A.START_DATE >= '" . setDate2DB($startDate) . "' AND A.END_DATE <= '" . setDate2DB($endDate) . "'";
        }

        if ($actionType)
            $SQL .= " AND A.ACTION_TYPE = '" . $actionType . "'";

        switch (strtoupper($target)) {
            case "GENERAL":
                $SQL .= " AND A.CLASS_ID<>0";
                break;
            ///@veasna    
            case "CREDITSYSTEM":
                $SQL .= "";
                break;
            ////

            case "TRAINING":
                $SQL .= " AND A.TRAINING_ID<>0";
                break;
        }
        
        if ($globalSearch)
        {
            $SQL .= " AND ((B.FIRSTNAME like '%" . $globalSearch . "%')) ";
            $SQL .= " OR ((B.LASTNAME like '%" . $globalSearch . "%')) ";
            $SQL .= " OR ((B.CODE like '%" . $globalSearch . "%')) ";
            //$SQL .= " OR ((T.NAME like '%" . $globalSearch . "%')) ";
            //$SQL .= " OR ((E.NAME like '%" . $globalSearch . "%')) ";
            
        }

        //error_log($SQL);
        $SQL .= " ORDER BY B.FIRSTNAME";

        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonLoadStudentAttendance($objectId) {

        return array(
            "success" => true
            , "data" => $this->getAttendanceFromId($objectId)
        );
    }

    public function jsonSearchStudentAttendance($params, $isJson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->getAllAttendancesQuery($params);

        $data = array();

        $i = 0;

        $total['MALE'] = 0;
        $total['FEMALE'] = 0;
        $total['UNKNOWN'] = 0;

        $born_year = array();
        $born_year["UNKNOWN"] = 0;
        if ($result) {
            foreach ($result as $value) {
                $absentType = AbsentTypeDBAccess::findObjectFromId($value->ABSENT_TYPE);
                $subjecType = SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);
                if ($absentType) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                    $data[$i]["GENDER_NAME"] = getGenderName($value->GENDER);
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    //@soda
                    $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                    $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                    $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                    $data[$i]["DATE_BIRTH"] = $value->DATE_BIRTH;
                    $data[$i]["PHONE"] = $value->PHONE;
                    $data[$i]["EMAIL"] = $value->EMAIL;
                    $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;

                    //

                    if ($absentType) {
                        $data[$i]["ABSENT_TYPE"] = $absentType->NAME;
                    } else {
                        $data[$i]["ABSENT_TYPE"] = "No relationship";
                    }

                    $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                    $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                    $data[$i]["DATE"] = $data[$i]["START_DATE"] . " - " . $data[$i]["END_DATE"];
                    $data[$i]["COUNT_DATE"] = $value->COUNT_DATE;

                    switch ($value->TERM) {
                        case "FIRST_SEMESTER":
                            $data[$i]["TERM"] = FIRST_SEMESTER;
                            break;
                        case "SECOND_SEMESTER":
                            $data[$i]["TERM"] = SECOND_SEMESTER;
                            break;
                    }
                    if ($subjecType) {
                        $data[$i]["SUBJECT_NAME"] = $subjecType->NAME;
                    } else {
                        $data[$i]["SUBJECT_NAME"] = "?";
                    }
                    $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                    $data[$i]["CREATED_BY"] = $value->CREATED_BY;
                    $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                    $data[$i]["STATUS"] = $value->STATUS;

                    if (isset($value->CLASS_NAME)) {
                        $data[$i]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                        $data[$i]["SCHOOLYEAR_NAME"] = setShowText($value->SCHOOL_YEAR);
                    } else {
                        $data[$i]["CLASS_NAME"] = "";
                        $data[$i]["SCHOOLYEAR_NAME"] = "";
                    }

                    if (isset($value->TRAINING_NAME)) {
                        $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                        $data[$i]["TRAINING_TERM"] = setShowText($value->TRAINING_TERM);
                    } else {
                        $data[$i]["TRAINING_NAME"] = "";
                        $data[$i]["TRAINING_TERM"] = "";
                    }

                    $i++;

                    switch ($value->GENDER) {
                        case 1:
                            $total['MALE'] ++;
                            break;
                        case 2:
                            $total['FEMALE'] ++;
                            break;
                        default:
                            $total['UNKNOWN'] ++;
                    }

                    if (isset($value->BORN_YEAR)) {
                        if ($value->BORN_YEAR) {
                            if (isset($born_year[$value->BORN_YEAR]))
                                ++$born_year[$value->BORN_YEAR];
                            else
                                $born_year[$value->BORN_YEAR] = 1;
                        } else {
                            ++$born_year["UNKNOWN"];
                        }
                    }
                }
            }
        }

        $jsongender = jsonCountGender($total);
        $jsonbornyear = jsonBornYear($born_year);

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
                , "sexCount" => $jsongender
                , "bornYear" => $jsonbornyear
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    protected function attendanceCountByClass($academicId, $type, $subjectId) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_student_attendance";
        $SQL .= " WHERE 1=1";
        if ($type)
            $SQL .= " AND ABSENT_TYPE = $type";
        if ($subjectId)
            $SQL .= " AND SUBJECT = $subjectId";
        $SQL .= " AND CLASS = '" . $academicId. "'";
        $SQL .= " ORDER BY FIRSTNAME";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result->C;
    }

    public function jsonDeleteStudentAttendance($Id) {

        if ($Id) {
            self::dbAccess()->delete('t_student_attendance', array('ID = ? ' => $Id));
        }

        return array("success" => true);
    }

    public function jsonReleaseStudentAttendance($params) {

        $objectId = $params["objectId"] ? addText($params["objectId"]) : "0";
        $facette = self::findAttendanceFromId($objectId);
        $newStatus = 0;

        if ($facette) {
            $status = $facette->STATUS;

            $SQL = "";
            $SQL .= "UPDATE ";
            $SQL .= " t_student_attendance";
            $SQL .= " SET";

            switch ($status) {
                case 0:
                    $newStatus = 1;
                    $SQL .= " STATUS=1";
                    $SQL .= " ,SMS_SENT=1";
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
        }

        return array("success" => true, "status" => $newStatus);
    }

    public function getStudentAttendanceCount($studentId, $schoolyearId, $term = null, $type = null) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_attendance', array('SUM(COUNT_DATE) as C'));
        $SQL->where('STUDENT_ID = ?', $studentId);
        $SQL->where('STATUS = ?', 1);
        $SQL->where('SCHOOLYEAR_ID = ?', $schoolyearId);
        if ($term && $term <> 'YEAR')
            $SQL->where('TERM = ?', $term);
        if ($type)
            $SQL->where('ABSENT_TYPE = ?', $type);

        $result = self::dbAccess()->fetchRow($SQL);

        return isset($result->C) ? $result->C : "---";
    }

    public function getStudentEduWorkingDays($gradeId) {

        $DB_ACADEMIC = AcademicDBAccess::getInstance();

        $result = $DB_ACADEMIC->sqlGradeWorkingDays($gradeId);
        $data = array();
        if (isset($result))
            foreach ($result as $key => $value) {
                if ($value == 1)
                    $data[] = $key;
            }
        return $data;
    }

    public function getStudentTrainingWorkingDays($trainingId) {

        $selectColumns = array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU');
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from('t_training', $selectColumns);
        $SQL->where("ID = ?", $trainingId);
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchRow($SQL);

        $data = array();
        if (isset($result))
            foreach ($result as $key => $value) {
                if ($value == 1)
                    $data[] = $key;
            }
        return $data;
    }

    public function getSemesterTermByStartDateSchoolyear($start_date,$academicId,$schoolyear_id) {

        return AcademicDBAccess::getNameOfSchoolTermByDate($start_date, $academicId, $schoolyear_id);
    }

    public function dates_range($START_DATE, $END_DATE) {

        $START_DATE = strtotime($START_DATE);
        $END_DATE = strtotime($END_DATE);
        $DATE_RANGE = array();

        if ($START_DATE <= $END_DATE) {
            $DATE_RANGE[] = date('Y-m-d', $START_DATE);
            while ($START_DATE <> $END_DATE) {
                $START_DATE = mktime(0, 0, 0, date("m", $START_DATE), date("d", $START_DATE) + 1, date("Y", $START_DATE));
                $DATE_RANGE[] = date('Y-m-d', $START_DATE);
            }
        }
        return $DATE_RANGE;
    }

    public function checkFuturDate($date) {

        $date = strtotime($date);
        $today = strtotime(getCurrentDBDate());

        if ($date > $today) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkExistingAttendanceDate($studentId, $academicId, $trainingId, $startDate, $endDate) {

        $SQL_FIRST = "";
        $SQL_FIRST .= " SELECT *";
        $SQL_FIRST .= " FROM t_student_attendance";
        $SQL_FIRST .= " WHERE 1=1";
        $SQL_FIRST .= " AND (START_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "')";
        $SQL_FIRST .= " OR (END_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "')";
        $RESULT_FIRST = self::dbAccess()->fetchAll($SQL_FIRST);

        $SQL_SECOND = "";
        $SQL_SECOND .= " SELECT *";
        $SQL_SECOND .= " FROM t_student_attendance";
        $SQL_SECOND .= " WHERE 1=1";
        $SQL_SECOND .= " AND ('" . $startDate . "' BETWEEN START_DATE AND END_DATE)";
        $SQL_SECOND .= " OR ('" . $endDate . "' BETWEEN START_DATE AND END_DATE)";
        $RESULT_SECOND = self::dbAccess()->fetchAll($SQL_SECOND);

        $FIRST_DATA = array();
        if ($RESULT_FIRST) {
            foreach ($RESULT_FIRST as $key => $value) {

                if ($academicId) {
                    if ($value->STUDENT_ID == $studentId) {
                        if (!$value->TRAINING_ID)
                            $FIRST_DATA[$value->ID] = $value->ID;
                    }
                } elseif ($trainingId) {
                    if ($value->STUDENT_ID == $studentId) {
                        if ($value->TRAINING_ID)
                            $FIRST_DATA[$value->ID] = $value->ID;
                    }
                }
            }
        }

        $SECOND_DATA = array();
        if ($RESULT_SECOND) {
            foreach ($RESULT_SECOND as $key => $value) {
                if ($academicId) {
                    if ($value->STUDENT_ID == $studentId) {
                        if (!$value->TRAINING_ID)
                            $SECOND_DATA[$value->ID] = $value->ID;
                    }
                } elseif ($trainingId) {
                    if ($value->STUDENT_ID == $studentId) {
                        if ($value->TRAINING_ID)
                            $SECOND_DATA[$value->ID] = $value->ID;
                    }
                }
            }
        }

        $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;
        return $CHECK_DATA;
    }

    //07.04.2013
    public function jsonStudentDayClassEventsGeneral($Id, $date) {

        $data = array();
        $facette = self::findAttendanceFromId($Id);

        if ($facette && $date) {

            $schoolyearId = $facette->SCHOOLYEAR_ID;
            $academicId= $facette->CLASS_ID;
            $term = AcademicDBAccess::getNameOfSchoolTermByDate($date, $facette->CLASS_ID);

            if ($term != "TERM_ERROR") {
                $searchParams["academicId"] = $academicId;
                $searchParams["schoolyearId"] = $schoolyearId;
                $searchParams["shortday"] = getWEEKDAY($date);
                $searchParams["term"] = $term;
                $entries = ScheduleDBAccess::getSQLClassEvents($searchParams);

                if ($entries) {
                    $i = 0;
                    foreach ($entries as $value) {
                        $data[$i]["ID"] = $value->GUID;
                        $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                        $data[$i]["EVENT"] = $value->EVENT;
                        $data[$i]["CLASS_NAME"] = $value->CLASS_NAME;
                        $data[$i]["COLOR"] = $value->SUBJECT_COLOR;
                        $data[$i]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                        $data[$i]["TERM"] = displaySchoolTerm($value->TERM);

                        ++$i;
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

    ////////////////////////////////////////////////////////////////////////////
    //New...
    public function getCalculatedCountDate($start_date, $end_date, $academicId, $schoolyearId, $trainingId) {

        $DAY_SCHEDULE_DB = DayScheduleDBAccess::getInstance();

        $DAY_RANGES = $this->dates_range($start_date, $end_date);
        $count = 0;
        $tab = self::getWeekDay();

        $data_caldate = array();

        if ($academicId&& $schoolyearId) {

            $DB_EVENTS = SchooleventDBAccess::getInstance();
            $paramsSchoolEvent['schoolyearId'] = $schoolyearId;
            $paramsSchoolEvent['classId'] = 0;
            $paramsSchoolEvent['status'] = 1;
            $paramsSchoolEvent['dayoffschool'] = 1;
            $school_events = $DB_EVENTS->allSchoolevents($paramsSchoolEvent, false);

            $paramsClassEvent['classId'] = $academicId;
            $paramsClassEvent['dayoffschool'] = 1;
            $class_events = $DB_EVENTS->allSchoolevents($paramsClassEvent, false);

            $result = array_merge($school_events, $class_events);
            $events = array();
            if ($result)
                foreach ($result as $value) {
                    $ranges = $this->dates_range($value['START_DATE'], $value['END_DATE']);
                    $events = array_merge($events, $ranges);
                }

            $workingDays = $this->getStudentEduWorkingDays($academicId);

            if ($DAY_RANGES) {
                foreach ($DAY_RANGES as $value) {
                    $time = strtotime($value);
                    $date = getdate($time);
                    $day = $tab[$date['wday']];
                    if ($date && in_array($day, $workingDays) && !in_array($value, $events)) {
                        $data_caldate[] = $value;
                        $count++;
                    }
                }
            }
        } elseif ($trainingId) {

            $workingDays = $this->getStudentTrainingWorkingDays($trainingId);

            if ($DAY_RANGES) {
                foreach ($DAY_RANGES as $value) {
                    $time = strtotime($value);
                    $date = getdate($time);
                    $day = $tab[$date['wday']];
                    if ($date && in_array($day, $workingDays)) {
                        $data_caldate[] = $value;
                        $count++;
                    }
                }
            }
        }

        /**
         * Check date in schedule...
         */
        $RESULT_DATA = array();
        $RESULT_COUNT = 0;
        if ($data_caldate) {
            foreach ($data_caldate as $date) {

                if ($date) {
                    $searchParams["academicId"] = $academicId;
                    $searchParams["trainingId"] = $trainingId;
                    $searchParams["eventDay"] = $date;
                    $COUNT = count($DAY_SCHEDULE_DB->dayEventList($searchParams, false));
                    if ($COUNT) {
                        $RESULT_DATA[$date] = $date;
                        $RESULT_COUNT++;
                    }
                }
            }
        }

        $result['COUNT_DATE'] = $RESULT_COUNT;
        $result['CAL_DATE'] = implode(",", $RESULT_DATA);
        return $result;
    }

    ////////////////////////////////////////////////////////////////////////////
    //New...
    ////////////////////////////////////////////////////////////////////////////
    public function jsonActonStudentAttendance($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : "";
        $facette = self::findAttendanceFromId($objectId);

        if (!$facette) {
            $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
            $academicId = isset($params["academicId"]) ? (int) $params["academicId"] : "";
            $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
            $academicObject = AcademicDBAccess::findGradeFromId($academicId);
            $schoolyearId = $academicObject ? $academicObject->SCHOOL_YEAR : "";
        } else {
            $studentId = $facette->STUDENT_ID;
            $academicId= $facette->CLASS_ID;
            $trainingId = $facette->TRAINING_ID;
            $actionType = $facette->ACTION_TYPE;
            $schoolyearId = $facette->SCHOOLYEAR_ID;
        }

        $START_DATE = setDate2DB($params["START_DATE"]);
        $END_DATE = setDate2DB($params["END_DATE"]);

        if ($actionType)
            $SAVEDATA["ACTION_TYPE"] = $actionType;

        if (isset($params["ABSENT_TYPE"]))
            $SAVEDATA["ABSENT_TYPE"] = $params["ABSENT_TYPE"];

        $errors = array();
        $CHECK_ERROR_EXISTING_DATE = 0;
        $CHECK_ERROR_TERM = 0;

        $CHECK_EXISTING_DATE = $this->checkExistingAttendanceDate(
                $studentId
                , $academicId
                , $trainingId
                , $START_DATE
                , $END_DATE
        );

        $CHECK_COUNT_EXISTING_DATE = count($CHECK_EXISTING_DATE);

        if ($academicId&& $schoolyearId) {
            $SEMESTER_TERM = $this->getSemesterTermByStartDateSchoolyear($START_DATE,$academicId,$schoolyearId);
            $SAVEDATA['TERM'] = $SEMESTER_TERM;
            $CHECK_ERROR_TERM = $SEMESTER_TERM ? 0 : 1;
        } else {
            $CHECK_ERROR_TERM = 0;
        }

        $CALCULATED_COUNT_DATE_DATA = $this->getCalculatedCountDate(
                $START_DATE
                , $END_DATE
                , $academicId
                , $schoolyearId
                , $trainingId
        );

        $COUNT_DATE = $CALCULATED_COUNT_DATE_DATA["COUNT_DATE"];
        $SAVEDATA['COUNT_DATE'] = $CALCULATED_COUNT_DATE_DATA["COUNT_DATE"];
        $SAVEDATA['CAL_DATE'] = $CALCULATED_COUNT_DATE_DATA["CAL_DATE"];
        $SAVEDATA["START_DATE"] = $START_DATE;
        $SAVEDATA["END_DATE"] = $END_DATE;

        if ($CHECK_ERROR_TERM) {
            $errors["START_DATE"] = OUT_OF_ACADEMIC_DATE;
            $errors["END_DATE"] = OUT_OF_ACADEMIC_DATE;
        }

        if (!$facette) {

            if ($CHECK_COUNT_EXISTING_DATE) {
                $CHECK_ERROR_EXISTING_DATE = 1;
            }

            if ($academicId) {
                $SAVEDATA["CLASS_ID"] = $academicId;
                $SAVEDATA["SCHOOLYEAR_ID"] = $schoolyearId;
            }

            if ($trainingId)
                $SAVEDATA["TRAINING_ID"] = $trainingId;

            $SAVEDATA["STUDENT_ID"] = $studentId;

            if ($CHECK_ERROR_EXISTING_DATE) {
                $errors["START_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
                $errors["END_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
            }

            if (!$errors) {
                if (isset($params["target"])) {
                    $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                    $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                    if ($COUNT_DATE) {
                        self::dbAccess()->insert('t_student_attendance', $SAVEDATA);
                        $objectId = self::dbAccess()->lastInsertId();
                    }
                }
            }
        } else {

            if ($CHECK_COUNT_EXISTING_DATE == 1) {
                $CHECK_ERROR_EXISTING_DATE = 0;
            } elseif ($CHECK_COUNT_EXISTING_DATE == 0) {
                $CHECK_ERROR_EXISTING_DATE = 0;
            } else {
                $CHECK_ERROR_EXISTING_DATE = 1;
            }

            if ($CHECK_ERROR_EXISTING_DATE) {
                $errors["START_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
                $errors["END_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
            }

            $WHERE[] = "ID = '" . $objectId . "'";
            if (!$errors) {
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
                if ($COUNT_DATE)
                    self::dbAccess()->update('t_student_attendance', $SAVEDATA, $WHERE);
            }
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
                , "objectId" => $objectId
            );
        } else {
            return array(
                "success" => true
                , "errors" => $errors
                , "objectId" => $objectId
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //New...
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonStudentAttendanceBlock($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? (int) $params["academicId"] : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $classObject = AcademicDBAccess::findGradeFromId($academicId);
        $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);

        if ($classObject) {
            $entries = StudentDBAccess::queryStudentByClass($academicId, $classObject->SCHOOL_YEAR);
        } elseif ($trainingObject) {
            $entries = StudentTrainingDBAccess::sqlStudentTraining(false, $trainingId, false);
        }

        $data = array();
        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = $value->STUDENT_CODE;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                ////////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////////
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                ++$i;
            }
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

    ////////////////////////////////////////////////////////////////////////////
    //New...
    ////////////////////////////////////////////////////////////////////////////
    public static function checkExistStudentBlockAttendance($studentId, $academicId, $trainingId, $absentType, $absentDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_attendance", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $studentId . "'");

        if ($academicId)
            $SQL->where("CLASS_ID = '" . $academicId. "'");
        if ($trainingId)
            $SQL->where("TRAINING_ID = '" . $trainingId . "'");

        $SQL->where("ABSENT_TYPE = '" . $absentType . "'");
        $SQL->where("START_DATE <= '" . $absentDate . "' and END_DATE >= '" . $absentDate . "'");
        $SQL->where("ACTION_TYPE='2'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //New...
    ////////////////////////////////////////////////////////////////////////////
    public static function checkExistStudentDailyAttendance($studentId, $scheduleId, $absentType, $absentDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_attendance", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $studentId . "'");
        $SQL->where("SCHEDULE_ID = '" . $scheduleId . "'");
        if ($absentType)
            $SQL->where("ABSENT_TYPE = '" . $absentType . "'");
        $SQL->where("START_DATE <= '" . $absentDate . "' and END_DATE >= '" . $absentDate . "'");
        $SQL->where("ACTION_TYPE='1'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //@Sea Peng
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonStudentDailyAttendance($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? (int) $params["academicId"] : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : "";
        //error_log( $trainingId);
        $absentDate = isset($params["choosedate"]) ? setDate2DB($params["choosedate"]) : "";
        $classObject = AcademicDBAccess::findGradeFromId($academicId);
        $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);
        $scheduleObject = ScheduleDBAccess::findScheduleFromGuId($scheduleId);
        $isDisabled = 'disabled';

        if ($academicId) {
            $term = AcademicDBAccess::getNameOfSchoolTermByDate($absentDate, $academicId);
            if ($term != 'TERM_ERROR')
                $isDisabled = $scheduleObject ? "" : "disabled";
        }

        if ($trainingId) {
            $trainigTerm = TrainingDBAccess::checkTrainingClass($absentDate, $trainingId);
            if ($trainigTerm)
                $isDisabled = $scheduleObject ? "" : "disabled";
        }

        $ABSENT_TYPES = AbsentTypeDBAccess::allAbsentType('STUDENT');

        $data = array();

        if ($classObject) {
            $entries = StudentDBAccess::queryStudentByClass($academicId, $classObject->SCHOOL_YEAR);
        } elseif ($trainingObject) {
            $entries = StudentTrainingDBAccess::sqlStudentTraining(false, $trainingId, false);
        }

        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {

                $studentIndex = $value->STUDENT_INDEX;

                $studentId = $value->STUDENT_ID;

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = $value->STUDENT_CODE;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;

                ////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if ($ABSENT_TYPES) {
                    foreach ($ABSENT_TYPES as $abentType) {

                        $CHECK_STATUS_BLOCK = false;
                        $CHECK_STATUS_DAILY = false;

                        $newIndex = $studentIndex . "_" . $abentType->ID;

                        $data[$i]["" . $abentType->ID . "_COLOR"] = $abentType->COLOR;
                        $data[$i]["" . $abentType->ID . "_COLOR_FONT"] = getFontColor($abentType->COLOR);
                        $data[$i]["TYPE_" . $abentType->ID . ""] = "";
                        $data[$i]["TYPE_" . $abentType->ID . ""].= "<div style='padding :6px;'>";

                        if ($scheduleObject) {
                            /*
                              Check attendance by action block...
                             */
                            $CHECK_BLOCK = self::checkExistStudentBlockAttendance($studentId, $academicId, $trainingId, $abentType->ID, $absentDate);

                            if ($CHECK_BLOCK) {
                                $CHECK_STATUS_BLOCK = true;
                            } else {
                                /*
                                  Check attendance by action daily...
                                 */
                                $CHECK_STATUS = self::checkExistStudentDailyAttendance($studentId, $scheduleObject->ID, $abentType->ID, $absentDate);
                                $CHECK_STATUS_DAILY = $CHECK_STATUS ? "checked" : "";
                            }
                        }

                        if ($CHECK_STATUS_BLOCK) {
                            $CHECKED = "checked";
                        } elseif ($CHECK_STATUS_DAILY) {
                            $CHECKED = "checked";
                        } else {
                            $CHECKED = "";
                        }

                        $data[$i]["TYPE_" . $abentType->ID . ""] .= "<input onclick='functionActionDaily" . $newIndex . "()' " . $isDisabled . " " . $CHECKED . " type='checkbox' value='1' id='TYPE_" . $newIndex . "'>";
                        $data[$i]["TYPE_" . $abentType->ID . ""] .= "</div>";
                    }
                }
                ++$i;
            }
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

    public function jsonActionStudentDailyAttendance($params) {

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $absentType = isset($params["absentType"]) ? $params["absentType"] : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $academicId= isset($params["academicId"]) ? (int) $params["academicId"] : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $scheduleId = isset($params["scheduleId"]) ? addText($params["scheduleId"]) : "";
        $absentDate = isset($params["choosedate"]) ? setDate2DB($params["choosedate"]) : "";

        $SUCCESS_DATA = array();
        $SUCCESS_DATA['msg'] = ACTION_SUCCESSFULLY_SAVED;

        $scheduleObject = ScheduleDBAccess::findScheduleFromGuId($scheduleId);

        if ($scheduleObject) {

            $CHECK_BLOCK = self::checkExistStudentBlockAttendance(
                            $studentId
                            , $academicId
                            , $trainingId
                            , $absentType
                            , $absentDate);

            if (!$CHECK_BLOCK) {

                if ($newValue) {

                    $CHECK_EXIST = self::checkExistStudentDailyAttendance(
                                    $studentId
                                    , $scheduleObject->ID
                                    , false
                                    , $absentDate);

                    if ($CHECK_EXIST) {
                        $SUCCESS_DATA["error"] = true;
                        $SUCCESS_DATA['msg'] = ABSENCE_ALREADY_SET_THIS_DAY;
                        $SUCCESS_DATA["checked"] = $newValue ? false : true;
                    } else {
                        $SUCCESS_DATA["error"] = false;
                        $SUCCESS_DATA["checked"] = $newValue ? true : false;
                        self::addStudentDailyAttendance(
                                $studentId
                                , $absentDate
                                , $absentType
                                , $scheduleObject
                                , $academicId
                                , $trainingId);
                    }
                } else {
                    $WHERE = array();
                    $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
                    $WHERE[] = "START_DATE = '" . $absentDate . "'";
                    if ($academicId)
                        $WHERE[] = "CLASS_ID = '" . $academicId. "'";
                    if ($trainingId)
                        $WHERE[] = "TRAINING_ID = '" . $trainingId . "'";
                    $WHERE[] = "SCHEDULE_ID = '" . $scheduleObject->ID . "'";
                    self::dbAccess()->delete('t_student_attendance', $WHERE);
                }
            } else {
                $SUCCESS_DATA["error"] = true;
                $SUCCESS_DATA['msg'] = ABSENCE_ALREADY_SET_THIS_DAY;
                switch ($newValue) {
                    case 0:
                        $SUCCESS_DATA["checked"] = true;
                        break;
                    case 1:
                        $SUCCESS_DATA["checked"] = false;
                        break;
                }
            }
        }

        $SUCCESS_DATA["field"] = $field;
        $SUCCESS_DATA["success"] = true;
        $SUCCESS_DATA["objectId"] = self::dbAccess()->lastInsertId();

        return $SUCCESS_DATA;
    }

    public static function jsonUpdateStudentAttendanceDescription($params) {

        $Id = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = $params["DESCRIPTION"];

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);

        self::dbAccess()->update('t_student_attendance', $SAVEDATA, $WHERE);

        return array(
            "success" => true
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    public static function addStudentDailyAttendance($studentId, $absentDate, $absentType, $scheduleObject, $academicId, $trainingId) {

        $SAVEDATA["STUDENT_ID"] = $studentId;
        $SAVEDATA["ACTION_TYPE"] = 1;
        $SAVEDATA["ABSENT_TYPE"] = $absentType;
        $SAVEDATA["SCHEDULE_ID"] = $scheduleObject->ID;
        $SAVEDATA["SUBJECT_ID"] = $scheduleObject->SUBJECT_ID;
        $SAVEDATA["CLASS_ID"] = $academicId;
        $SAVEDATA["TRAINING_ID"] = $trainingId;
        $SAVEDATA["START_DATE"] = $absentDate;
        $SAVEDATA["END_DATE"] = $absentDate;
        $SAVEDATA["START_TIME"] = $scheduleObject->START_TIME;
        $SAVEDATA["END_TIME"] = $scheduleObject->END_TIME;
        $SAVEDATA["SCHOOLYEAR_ID"] = $scheduleObject->SCHOOLYEAR_ID;
        $SAVEDATA["TERM"] = $scheduleObject->TERM;
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        self::dbAccess()->insert('t_student_attendance', $SAVEDATA);
    }

    ////must find academicIdfor student in scheduleId ....
    public static function getClassStudentInSchedule($studentId, $scheduleId) {

        $SELECT_DATA = array(
            "B.SUBJECT_ID AS SUBJECT_ID"
            , "D.ID AS CLASS_ID"
            , "D.NAME AS CLASS_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_link_schedule_academic'), array());
        $SQL->joinLeft(array('B' => 't_schedule'), 'A.SCHEDULE_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_student_schoolyear_subject'), 'C.CLASS_ID=A.ACADEMIC_ID', array());
        $SQL->joinLeft(array('D' => 't_grade'), 'C.CLASS_ID=D.ID', $SELECT_DATA);
        $SQL->where("C.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.SCHEDULE_ID = '" . $scheduleId . "'");
        //error_log($SQL->__toString()); 
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    /////

    public function checkCreditStudentBetween2Date($studentId, $startDate, $endDate) {

        $SQL_FIRST = "";
        $SQL_FIRST .= " SELECT *";
        $SQL_FIRST .= " FROM t_student_attendance";
        $SQL_FIRST .= " WHERE 1=1";
        $SQL_FIRST .= " AND (START_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "')";
        $SQL_FIRST .= " OR (END_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "')";
        $RESULT_FIRST = self::dbAccess()->fetchAll($SQL_FIRST);

        $SQL_SECOND = "";
        $SQL_SECOND .= " SELECT *";
        $SQL_SECOND .= " FROM t_student_attendance";
        $SQL_SECOND .= " WHERE 1=1";
        $SQL_SECOND .= " AND ('" . $startDate . "' BETWEEN START_DATE AND END_DATE)";
        $SQL_SECOND .= " OR ('" . $endDate . "' BETWEEN START_DATE AND END_DATE)";
        $RESULT_SECOND = self::dbAccess()->fetchAll($SQL_SECOND);

        $FIRST_DATA = array();
        if ($RESULT_FIRST) {
            foreach ($RESULT_FIRST as $key => $value) {
                if ($value->STUDENT_ID == $studentId) {
                    $SECOND_DATA[$value->ID] = $value->ID;
                }
            }
        }

        $SECOND_DATA = array();
        if ($RESULT_SECOND) {
            foreach ($RESULT_SECOND as $key => $value) {
                if ($value->STUDENT_ID == $studentId) {
                    $SECOND_DATA[$value->ID] = $value->ID;
                }
            }
        }

        $CHECK_DATA = $FIRST_DATA + $SECOND_DATA;
        return $CHECK_DATA;
    }

    public function getCalculatedCountCreditStudent($startDate, $endDate, $studentId, $schoolyearId) {

        $DAY_SCHEDULE_DB = DayScheduleDBAccess::getInstance();

        $DAY_RANGES = $this->dates_range($startDate, $endDate);
        $count = 0;
        $tab = self::getWeekDay();

        $data_caldate = array();

        $DB_EVENTS = SchooleventDBAccess::getInstance();
        $paramsSchoolEvent['schoolyearId'] = $schoolyearId;
        $paramsSchoolEvent['classId'] = 0;
        $paramsSchoolEvent['status'] = 1;
        $paramsSchoolEvent['dayoffschool'] = 1;
        $school_events = $DB_EVENTS->allSchoolevents($paramsSchoolEvent, false);

        /* $paramsClassEvent['classId'] = $academicId;
          $paramsClassEvent['dayoffschool'] = 1;
          $class_events = $DB_EVENTS->allSchoolevents($paramsClassEvent, false); */

        $result = $school_events;
        $events = array();
        if ($result)
            foreach ($result as $value) {
                $ranges = $this->dates_range($value['START_DATE'], $value['END_DATE']);
                $events = array_merge($events, $ranges);
            }

        $workingDays = array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU');

        if ($DAY_RANGES) {
            foreach ($DAY_RANGES as $value) {
                $time = strtotime($value);
                $date = getdate($time);
                $day = $tab[$date['wday']];
                if ($date && in_array($day, $workingDays) && !in_array($value, $events)) {
                    $data_caldate[] = $value;
                    $count++;
                }
            }
        }

        /**
         * Check date in schedule...
         */
        $GROUPARR = array();
        $RESULT_DATA = array();
        $RESULT_COUNT = 0;
        if ($data_caldate) {
            foreach ($data_caldate as $date) {

                if ($date) {

                    $searchParams["studentId"] = $studentId;
                    $searchParams["schoolyearId"] = $schoolyearId;
                    $searchParams["eventDay"] = $date;
                    ///
                    $daySchedule = $DAY_SCHEDULE_DB->dayCreditStudentEventList($searchParams, false);
                    foreach ($daySchedule as $schedule) {
                        if (!in_array($schedule['GROUP_ID'], $GROUPARR)) {
                            $GROUPARR[] = $schedule['GROUP_ID'];
                        }
                    }
                    ///

                    $COUNT = count($daySchedule);
                    if ($COUNT) {
                        $RESULT_DATA[$date] = $date;
                        $RESULT_COUNT++;
                    }
                }
            }
        }

        $result['COUNT_DATE'] = $RESULT_COUNT;
        $result['GROUPIDS'] = implode(",", $GROUPARR);
        $result['CAL_DATE'] = implode(",", $RESULT_DATA);
        return $result;
    }

    public function jsonActonBlockCreditStudentAttendance($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : "";

        $facette = self::findAttendanceFromId($objectId);

        if (!$facette) {
            $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
            $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
            $creditAcademicId = StudentAcademicDBAccess::getStudentCurrentAcademicCreditSystem($studentId, $schoolyearId);
        } else {
            $studentId = $facette->STUDENT_ID;
            $actionType = $facette->ACTION_TYPE;
            $schoolyearId = $facette->SCHOOLYEAR_ID;
        }

        $START_DATE = setDate2DB($params["START_DATE"]);
        $END_DATE = setDate2DB($params["END_DATE"]);

        if ($actionType)
            $SAVEDATA["ACTION_TYPE"] = $actionType;

        if (isset($params["ABSENT_TYPE"]))
            $SAVEDATA["ABSENT_TYPE"] = $params["ABSENT_TYPE"];

        $errors = array();
        $CHECK_ERROR_EXISTING_DATE = 0;

        ///check student attendent during between date
        $CHECK_EXISTING_DATE = $this->checkCreditStudentBetween2Date($studentId, $START_DATE, $END_DATE);

        $CHECK_COUNT_EXISTING_DATE = count($CHECK_EXISTING_DATE);

        $SEMESTER_TERM = $this->getSemesterTermByStartDateSchoolyear($START_DATE, $schoolyearId);
        $SAVEDATA['TERM'] = $SEMESTER_TERM;
        $CHECK_ERROR_TERM = ($SEMESTER_TERM == "TERM_ERROR") ? 0 : 1;

        ///calulate
        $CALCULATED_COUNT_DATE_DATA = $this->getCalculatedCountCreditStudent(
                $START_DATE
                , $END_DATE
                , $studentId
                , $schoolyearId
        );


        $COUNT_DATE = $CALCULATED_COUNT_DATE_DATA["COUNT_DATE"];
        $SAVEDATA['COUNT_DATE'] = $CALCULATED_COUNT_DATE_DATA["COUNT_DATE"];
        $SAVEDATA['CAL_DATE'] = $CALCULATED_COUNT_DATE_DATA["CAL_DATE"];
        //  groupids
        if ($creditAcademicId) {
            $SAVEDATA['CREDIT_ACADEMIC_ID'] = $creditAcademicId->ID;
        }
        //$SAVEDATA['GROUP_IDS'] = $CALCULATED_COUNT_DATE_DATA["GROUPIDS"];
        //
        $SAVEDATA["START_DATE"] = $START_DATE;
        $SAVEDATA["END_DATE"] = $END_DATE;

        if ($CHECK_ERROR_TERM) {
            $errors["START_DATE"] = OUT_OF_ACADEMIC_DATE;
            $errors["END_DATE"] = OUT_OF_ACADEMIC_DATE;
        }

        if (!$facette) {

            if ($CHECK_COUNT_EXISTING_DATE) {
                $CHECK_ERROR_EXISTING_DATE = 1;
            }

            $SAVEDATA["SCHOOLYEAR_ID"] = $schoolyearId;
            $SAVEDATA["STUDENT_ID"] = $studentId;

            if ($CHECK_ERROR_EXISTING_DATE) {
                $errors["START_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
                $errors["END_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
            }

            if (!$errors) {
                if (isset($params["target"])) {
                    $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                    $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                    if ($COUNT_DATE) {
                        self::dbAccess()->insert('t_student_attendance', $SAVEDATA);
                        $objectId = self::dbAccess()->lastInsertId();
                    }
                }
            }
        } else {

            if ($CHECK_COUNT_EXISTING_DATE == 1) {
                $CHECK_ERROR_EXISTING_DATE = 0;
            } elseif ($CHECK_COUNT_EXISTING_DATE == 0) {
                $CHECK_ERROR_EXISTING_DATE = 0;
            } else {
                $CHECK_ERROR_EXISTING_DATE = 1;
            }

            if ($CHECK_ERROR_EXISTING_DATE) {
                $errors["START_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
                $errors["END_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
            }

            $WHERE[] = "ID = '" . $objectId . "'";
            if (!$errors) {
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
                if ($COUNT_DATE)
                    self::dbAccess()->update('t_student_attendance', $SAVEDATA, $WHERE);
            }
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
                , "objectId" => $objectId
            );
        } else {
            return array(
                "success" => true
                , "errors" => $errors
                , "objectId" => $objectId
            );
        }
    }

    public function jsonStudentAttendanceMonth($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->getAllAttendancesQuery($params);

        $data = array();

        $i = 0;

        if ($result) {
            foreach ($result as $value) {

                $absentType = AbsentTypeDBAccess::findObjectFromId($value->ABSENT_TYPE);
                $subjecType = SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);
                if ($absentType) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                    $data[$i]["GENDER_NAME"] = getGenderName($value->GENDER);
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }

                    if ($absentType) {
                        $data[$i]["ABSENT_TYPE"] = $absentType->NAME;
                    } else {
                        $data[$i]["ABSENT_TYPE"] = "No relationship";
                    }

                    $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                    $data[$i]["END_DATE"] = getShowDate($value->END_DATE);

                    $data[$i]["DATE"] = $data[$i]["START_DATE"] . " - " . $data[$i]["END_DATE"];

                    $data[$i]["COUNT_DATE"] = $value->COUNT_DATE;

                    if ($subjecType) {
                        $data[$i]["SUBJECT_NAME"] = $subjecType->NAME;
                    } else {
                        $data[$i]["SUBJECT_NAME"] = "?";
                    }
                    $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);

                    if (isset($value->CLASS_NAME)) {
                        $data[$i]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                        $data[$i]["SCHOOLYEAR_NAME"] = setShowText($value->SCHOOL_YEAR);
                    } else {
                        $data[$i]["CLASS_NAME"] = "";
                        $data[$i]["SCHOOLYEAR_NAME"] = "";
                    }

                    if (isset($value->TRAINING_NAME)) {
                        $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                        $data[$i]["TRAINING_TERM"] = setShowText($value->TRAINING_TERM);
                    } else {
                        $data[$i]["TRAINING_NAME"] = "";
                        $data[$i]["TRAINING_TERM"] = "";
                    }

                    $i++;
                }
            }
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

}

?>