<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/AbsentTypeDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/academic/AcademicDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/AcademicDateDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/training/TrainingDBAccess.php";
require_once "models/CamemisTypeDBAccess.php";
require_once "utiles/Utiles.php";
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentStatisticsDBAccess {

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
        
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public function getListGenderStatistics($params, $type)
    {
        $result = null;
        switch ($type)
        {
            case 1:
                $result = $this->getSqlAcadamicGenderStatistics($params);
                break;
            case 2:
                $result = $this->getSqlTrainingGenderStatistics($params);
                break;
        }

        $data = array();
        if ($result)
        {
            foreach ($result as $value)
            {
                $data[] = (array) $value;
            }
        }
        return $data;
    }

    public function getSqlTrainingGenderStatistics($params)
    {

        $studentstatusType = isset($params["studentstatusType"]) ? addText($params["studentstatusType"]) : "";
        $isStudentStatus = isset($params["isStudentStatus"]) ? addText($params["isStudentStatus"]) : "";
        $isStudentAbsence = isset($params["isStudentAbsence"]) ? addText($params["isStudentAbsence"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";    // Chuy Thong 19/08/2012
        $SQL = "";
        $SQL .= "SELECT DD.NAME,
        SUM( IF( A.GENDER = 1, 1, 0 ) ) AS MALE,
        SUM( IF( A.GENDER = 2, 1, 0 ) ) AS FEMALE,
        SUM( IF( A.GENDER <> 1 AND A.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
        COUNT( A.GENDER ) AS TOTAL
        FROM t_student AS A
        RIGHT JOIN t_student_training AS B ON A.ID=B.STUDENT
        LEFT JOIN t_training AS C ON C.ID = B.TRAINING ";
        $TRAINING_OBJECT = null;
        $WHERE_CLAUSE = "";
        if (!$trainingId)
            $SQL .= " INNER JOIN t_training AS DD ON C.PROGRAM = DD.ID";
        else
        {
            $TRAINING_OBJECT = TrainingDBAccess::findTrainingFromId($trainingId);
            switch ($TRAINING_OBJECT->OBJECT_TYPE)
            {
                case "PROGRAM":
                    $SQL .= " INNER JOIN t_training AS DD ON C.LEVEL = DD.ID";
                    $WHERE_CLAUSE = " AND C.PROGRAM=" . $trainingId;
                    break;
                case "LEVEL":
                    $SQL .= " INNER JOIN t_training AS DD ON C.ID = DD.ID";
                    $WHERE_CLAUSE = " AND C.LEVEL=" . $trainingId;
                    break;
                case "CLASS":
                    $SQL .= " INNER JOIN t_training AS DD ON C.ID = DD.ID";
                    $WHERE_CLAUSE = " AND C.ID=" . $trainingId;
                    break;
            }
        }

        if ($studentstatusType || $isStudentStatus)
            $SQL .= " RIGHT JOIN t_student_status AS D ON A.ID=D.STUDENT";

        if ($isStudentAbsence)
            $SQL .= " RIGHT JOIN t_student_attendance AS E ON A.ID=E.STUDENT_ID";

        $SQL .= " WHERE 1 = 1";

        if ($trainingId)
        {
            $SQL .= $WHERE_CLAUSE;
        }

        $SQL .= " GROUP  BY DD.NAME";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getSqlAcadamicGenderStatistics($params)
    {
        $campusId = isset($params['campusId']) ? addText($params["campusId"]) : '';
        $choosegrade = isset($params['choosegrade']) ? addText($params["choosegrade"]) : '';
        $schoolYearStart = isset($params['schoolYearStart']) ? addText($params["schoolYearStart"]) : '';
        $schoolYearEnd = isset($params['schoolYearEnd']) ? addText($params["schoolYearEnd"]) : '';
        $schoolyearId = isset($params['schoolyearId']) ? addText($params["schoolyearId"]) : '';
        $academicId = isset($params['academicId']) ? addText($params["academicId"]) : '';
        $studentstatusType = isset($params["studentstatusType"]) ? addText($params["studentstatusType"]) : "";
        $isStudentStatus = isset($params["isStudentStatus"]) ? addText($params["isStudentStatus"]) : "";
        $isStudentAbsence = isset($params["isStudentAbsence"]) ? addText($params["isStudentAbsence"]) : "";

        if (!$schoolyearId)
        {
            $currentSchoolyearObject = AcademicDateDBAccess::loadCurrentSchoolyear();
            if ($currentSchoolyearObject)
            {
                $schoolyearId = $currentSchoolyearObject->ID;
            }
        }

        $SQL = "";
        $SQL .= "SELECT C.NAME,
        SUM( IF( B.GENDER = 1, 1, 0 ) ) AS MALE,
        SUM( IF( B.GENDER = 2, 1, 0 ) ) AS FEMALE,
        SUM( IF( B.GENDER <> 1 AND B.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
        COUNT( B.GENDER ) AS TOTAL
        FROM t_student_schoolyear AS A
        LEFT JOIN t_student AS B ON A.STUDENT = B.ID";
        $SQL .= " LEFT JOIN t_grade AS C ON C.ID=" . (($choosegrade) ? "A.CLASS" : "A.GRADE");

        if ($studentstatusType || $isStudentStatus)
            $SQL .= " RIGHT JOIN t_student_status AS D ON B.ID=D.STUDENT";

        if ($isStudentAbsence)
            $SQL .= " RIGHT JOIN t_student_attendance AS E ON B.ID=E.STUDENT_ID";

        $SQL .= " WHERE 1 = 1";

        if ($campusId)
            $SQL .= " AND C.CAMPUS_ID=" . $campusId;

        if ($choosegrade)
            $SQL .= " AND C.OBJECT_TYPE = 'CLASS' AND C.ID=A.CLASS AND A.GRADE=" . $choosegrade;

        if ($schoolyearId)
            $SQL .= " AND A.SCHOOL_YEAR='" . $schoolyearId . "' ";

        if ($academicId)
            $SQL .= " AND A.CLASS='" . $academicId . "' ";

        if ($schoolYearStart || $schoolYearEnd)
        {
            $DB_ACADEMICDATE = AcademicDateDBAccess::getInstance();
            $schoolYearGroup = implode(",", $DB_ACADEMICDATE->getSchoolYearBetween($schoolYearStart, $schoolYearEnd));
            $SQL .= " AND A.SCHOOL_YEAR IN (" . $schoolYearGroup . ")";
        }

        $SQL .= " GROUP BY A.CAMPUS";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getSqlStudentAttendanceGenderStatistics($studentId, $classId, $absentType)
    {

        $SQL = "";
        $SQL .= "SELECT C.NAME,
        SUM( IF( A.GENDER = 1, 1, 0 ) ) AS MALE,
        SUM( IF( A.GENDER = 2, 1, 0 ) ) AS FEMALE,
        SUM( IF( A.GENDER <> 1 AND A.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
        COUNT( A.GENDER ) AS TOTAL
        FROM t_student AS A
        LEFT JOIN t_student_attendance AS B ON B.STUDENT_ID = A.ID";
        $SQL .= " LEFT JOIN t_grade AS C ON B.CLASS_ID = C.ID";
        $SQL .= " WHERE 1 = 1";

        if ($studentId)
            $SQL .= " AND B.STUDENT_ID='" . $studentId . "'";

        if ($classId)
            $SQL .= " AND B.CLASS_ID='" . $classId . "' ";

        if ($absentType)
        {
            $SQL .= " AND B.ABSENT_TYPE='" . $absentType . "' ";
        }

        $SQL .= " AND YEAR(B.END_DATE)='2013'";

        $SQL .= " GROUP  BY B.ABSENT_TYPE";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getSqlStudentAttendanceByDayType($day, $absentType, $studentId, $academicId, $target)
    {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_student_attendance";
        $SQL .= " WHERE 1 = 1";
        $SQL .= " AND WEEKOFYEAR(END_DATE)=WEEKOFYEAR(NOW())";

        if ($day)
            $SQL .= " AND WEEKDAY(END_DATE)='" . $day . "'";

        if ($absentType)
        {
            $SQL .= " AND ABSENT_TYPE='" . $absentType . "' ";
        }

        if ($studentId)
        {
            $SQL .= " AND STUDENT_ID='" . $studentId . "' ";
        }

        $facette = AcademicDBAccess::findGradeFromId($academicId);
        if ($facette)
        {
            if ($facette->EDUCATION_SYSTEM)
            {
                $SQL .= " AND CLASS_ID='" . $academicId . "' ";
            }
            else
            {
                $SQL .= " AND CLASS_ID='" . $academicId . "' ";
            }
        }

        switch ($target)
        {
            case 'general':
                $SQL .= " AND CLASS_ID<>0 ";
                break;
            case 'training':
                $SQL .= " AND TRAINING_ID<>0 ";
                break;
        }

        $SQL .= " GROUP BY ABSENT_TYPE";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //@Sea Peng 29.08.2013
    ////////////////////////////////////////////////////////////////////////////
    public static function getStudentAttendanceByDayType($absentType, $studentId, $academicId, $target)
    {
        $DAYS = array(
            '00' => MONDAY
            , '01' => TUESDAY
            , '02' => WEDNESDAY
            , '03' => THURSDAY
            , '04' => FRIDAY
            , '05' => SATURDAY
            , '06' => SUNDAY
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentAttendanceByDayType($day, $absentType, $studentId, $academicId, $target) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getStudentAttendanceByWeekType($absentType, $studentId, $academicId, $target)
    {
        $DAYS = array(
            '01' => "01", '02' => "02", '03' => "03", '04' => "04", '05' => "05", '06' => "06"
            , '07' => "07", '08' => "08", '09' => "09", '10' => "10", '11' => "11", '12' => "12"
            , '13' => "13", '14' => "14", '15' => "15", '16' => "16", '17' => "17", '18' => "18"
            , '19' => "19", '20' => "20", '21' => "21", '22' => "22", '23' => "23", '24' => "24"
            , '25' => "25", '26' => "26", '27' => "27", '28' => "28", '29' => "29", '30' => "30"
            , '31' => "31"
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentAttendanceByWeekType($day, $absentType, $studentId, $academicId, $target) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStudentAttendanceByWeekType($day, $absentType, $studentId, $academicId, $target)
    {
        //error_log($week);

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_student_attendance";
        $SQL .= " WHERE 1 = 1";
        $SQL .= " AND MONTH(END_DATE)=MONTH(NOW())";

        if ($day)
            $SQL .= " AND DAYOFMONTH(END_DATE)='" . $day . "'";

        if ($absentType)
        {
            $SQL .= " AND ABSENT_TYPE='" . $absentType . "' ";
        }

        if ($studentId)
        {
            $SQL .= " AND STUDENT_ID='" . $studentId . "' ";
        }

        $facette = AcademicDBAccess::findGradeFromId($academicId);
        if ($facette)
        {
            if ($facette->EDUCATION_SYSTEM)
            {
                $SQL .= " AND CLASS_ID='" . $academicId . "' ";
            }
            else
            {
                $SQL .= " AND CLASS_ID='" . $academicId . "' ";
            }
        }

        switch ($target)
        {
            case 'general':
                $SQL .= " AND CLASS_ID<>0 ";
                break;
            case 'training':
                $SQL .= " AND TRAINING_ID<>0 ";
                break;
        }

        $SQL .= " GROUP BY ABSENT_TYPE";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getSqlStudentAttendanceByMonthType($month, $year, $absentType, $studentId, $academicId, $target)
    {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_student_attendance";
        $SQL .= " WHERE 1 = 1";

        if ($month)
            $SQL .= " AND MONTH(END_DATE)='" . $month . "'";

        if ($year)
            $SQL .= " AND YEAR(END_DATE)='" . $year . "'";

        if ($absentType)
        {
            $SQL .= " AND ABSENT_TYPE='" . $absentType . "' ";
        }

        if ($studentId)
        {
            $SQL .= " AND STUDENT_ID='" . $studentId . "' ";
        }

        $facette = AcademicDBAccess::findGradeFromId($academicId);
        if ($facette)
        {
            if ($facette->EDUCATION_SYSTEM)
            {
                $SQL .= " AND CLASS_ID='" . $academicId . "' ";
            }
            else
            {
                $SQL .= " AND CLASS_ID='" . $academicId . "' ";
            }
        }

        switch ($target)
        {
            case 'general':
                $SQL .= " AND CLASS_ID<>0 ";
                break;
            case 'training':
                $SQL .= " AND TRAINING_ID<>0 ";
                break;
        }

        $SQL .= " GROUP BY ABSENT_TYPE";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentAttendanceByMonthType($absentType, $studentId, $academicId, $target)
    {
        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS)
        {
            $i = 0;
            foreach ($MONTHS as $key => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentAttendanceByMonthType($key, getCurrentYear(), $absentType, $studentId, $academicId, $target) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    ////////////////////////////////////////////////////////////////////////////
    //STUDENT DISCIPLINE
    ////////////////////////////////////////////////////////////////////////////
    ////@veasna
    public static function getCountStudentDiscipline($params){
        
        $disciplineType = isset($params['disciplineType'])? addText($params["disciplineType"]):'';
        $campusId = isset($params['campusId'])? addText($params["campusId"]):'';
        $gradeId = isset($params['gradeId'])? addText($params["gradeId"]):'';
        $schoolyearId = isset($params['schoolyearId'])? addText($params["schoolyearId"]):'';
        $MONTH = isset($params['MONTH'])? addText($params["MONTH"]):'';
        $YEAR = isset($params['YEAR'])? addText($params["YEAR"]):'';
        
        $SQL = self::dbAccess()->select();    
        $SQL->from(array("A"=>"t_discipline"), array("TOTAL" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'B.STUDENT=A.STUDENT_ID', array(''));
        if($disciplineType)
            $SQL->where("A.DISCIPLINE_TYPE = ?",$disciplineType);
        if($campusId)
            $SQL->where("B.CAMPUS = ?",$campusId);
        if($gradeId)
            $SQL->where("B.GRADE = ?",$gradeId);
        if($schoolyearId)
            $SQL->where("B.SCHOOL_YEAR = ?",$schoolyearId);
        if($MONTH)
            $SQL->where("MONTH(A.CREATED_DATE) = ?",$MONTH);
        if($YEAR)
            $SQL->where("YEAR(A.CREATED_DATE) = ?",$YEAR);
        $result = self::dbAccess()->fetchRow($SQL);
    
        return $result?$result->TOTAL:0;
    }
    
    public static function getDataStudentDiscipline($params)
    {
        $disciplineTypeEntries = CamemisTypeDBAccess::getCamemisType("DISCIPLINE_TYPE_STUDENT", false);
        $MONTHS = array(
                    '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
                    , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
                    , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
                    );
        $DATASET = "[";
        $i = 0;
       
        foreach ($disciplineTypeEntries as $disciplineObject)
        
        {
            $params['disciplineType']=$disciplineObject->ID;
            $RESULT = "[";
            $j = 0;
            foreach ($MONTHS as $key => $value)
            {
                $params['MONTH']=$key;
                $RESULT .=$j ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getCountStudentDiscipline($params) . "}";
                $j++;
            }
            
            $RESULT .= "]";
            $DATASET .=$i ? "," : "";
            $DATASET .= "{'key' : '" . $disciplineObject->NAME . "','values':" . $RESULT . "}";
            $i++;
        }
        $DATASET .= "]";

        return $DATASET;

    }
    ////
    
    
    
    public static function getDataSetStudentDiscipline($objectType, $studentId)
    {

        $disciplineTypeEntries = CamemisTypeDBAccess::getCamemisType("DISCIPLINE_TYPE_STUDENT", false);

        $DATASET = "[";
        switch ($objectType)
        {
            case "WEEKLY":
                if ($disciplineTypeEntries)
                {
                    $i = 0;
                    foreach ($disciplineTypeEntries as $value)
                    {
                        $VALUES = self::getStudentDisciplineByDayType($value->ID, $studentId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($disciplineTypeEntries)
                {
                    $i = 0;
                    foreach ($disciplineTypeEntries as $value)
                    {
                        $VALUES = self::getStudentDisciplineByWeekType($value->ID, $studentId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($disciplineTypeEntries)
                {
                    $i = 0;
                    foreach ($disciplineTypeEntries as $value)
                    {
                        $VALUES = self::getStudentDisciplineByMonthType($value->ID, $studentId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }

        $DATASET .= "]";

        return $DATASET;
    }

    public static function getStudentDisciplineByDayType($disciplineType, $studentId)
    {
        $DAYS = array(
            '00' => MONDAY
            , '01' => TUESDAY
            , '02' => WEDNESDAY
            , '03' => THURSDAY
            , '04' => FRIDAY
            , '05' => SATURDAY
            , '06' => SUNDAY
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlSudentDisciplineByDayType($day, $disciplineType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlSudentDisciplineByDayType($day, $disciplineType, $studentId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.DISCIPLINE_TYPE=B.ID', array());

        if ($day)
            $SQL->where("WEEKDAY(CREATED_DATE) = '" . $day . "'");

        if ($disciplineType)
            $SQL->where("DISCIPLINE_TYPE = '" . $disciplineType . "'");

        if ($studentId)
            $SQL->where("STUDENT_ID = ?",$studentId);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentDisciplineByWeekType($disciplineType, $studentId)
    {
        $DAYS = array(
            '01' => "01", '02' => "02", '03' => "03", '04' => "04", '05' => "05", '06' => "06"
            , '07' => "07", '08' => "08", '09' => "09", '10' => "10", '11' => "11", '12' => "12"
            , '13' => "13", '14' => "14", '15' => "15", '16' => "16", '17' => "17", '18' => "18"
            , '19' => "19", '20' => "20", '21' => "21", '22' => "22", '23' => "23", '24' => "24"
            , '25' => "25", '26' => "26", '27' => "27", '28' => "28", '29' => "29", '30' => "30"
            , '31' => "31"
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentDisciplineByWeekType($day, $disciplineType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStudentDisciplineByWeekType($day, $disciplineType, $studentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.DISCIPLINE_TYPE=B.ID', array());

        if ($day)
            $SQL->where("DAYOFMONTH(CREATED_DATE) = '" . $day . "'");

        if ($disciplineType)
            $SQL->where("DISCIPLINE_TYPE = '" . $disciplineType . "'");

        if ($studentId)
            $SQL->where("STUDENT_ID = ?",$studentId);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentDisciplineByMonthType($disciplineType, $studentId)
    {

        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS)
        {
            $i = 0;
            foreach ($MONTHS as $month => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentDisciplineByMonthType($month, getCurrentYear(), $disciplineType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStudentDisciplineByMonthType($month, $year, $disciplineType, $studentId)
    {
        $campusId = isset($params['campusId'])? addText($params["campusId"]):'';
        $gradeId = isset($params['gradeId'])? addText($params["gradeId"]):'';
        $schoolyearId = isset($params['schoolyearId'])? addText($params["schoolyearId"]):'';
        $month = isset($params['MONTH'])? addText($params["MONTH"]):'';
        $year = isset($params['YEAR'])? addText($params["YEAR"]):'';
        $disciplineType = isset($params['disciplineType'])? addText($params["disciplineType"]):'';
        $studentId = isset($params['studentId'])? addText($params["studentId"]):'';
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.DISCIPLINE_TYPE=B.ID', array(''));

        if ($month)
            $SQL->where("MONTH(CREATED_DATE) = '" . $month . "'");

        if ($year)
            $SQL->where("YEAR(CREATED_DATE) = '" . $year . "'");

        if ($disciplineType)
            $SQL->where("DISCIPLINE_TYPE = '" . $disciplineType . "'");

        if ($studentId)
            $SQL->where("STUDENT_ID = ?",$studentId);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //STUDENT ATTENDANCE...
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStudentAttendance($objectType, $studentId, $academicId, $target)
    {

        $absentTypeEntries = AbsentTypeDBAccess::allAbsentType('STUDENT', false);

        $DATASET = "[";
        switch ($objectType)
        {
            case "WEEKLY":
                if ($absentTypeEntries)
                {
                    $i = 0;
                    foreach ($absentTypeEntries as $value)
                    {
                        $VALUES = self::getStudentAttendanceByDayType($value->ID, $studentId, $academicId, $target);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','color':'$value->COLOR','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($absentTypeEntries)
                {
                    $i = 0;
                    foreach ($absentTypeEntries as $value)
                    {
                        $VALUES = self::getStudentAttendanceByWeekType($value->ID, $studentId, $academicId, $target);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','color':'$value->COLOR','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($absentTypeEntries)
                {
                    $i = 0;
                    foreach ($absentTypeEntries as $value)
                    {
                        $VALUES = self::getStudentAttendanceByMonthType($value->ID, $studentId, $academicId, $target);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','color':'$value->COLOR','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }

        $DATASET .= "]";

        return $DATASET;
    }
    ////@veasna
    public static function getCountStudentAttendance($params){
        
        $absenceType = isset($params['absenceType'])?$params['absenceType']:'';
        $campusId = isset($params['campusId'])? addText($params["campusId"]):'';
        $gradeId = isset($params['gradeId'])? addText($params["gradeId"]):'';
        $schoolyearId = isset($params['schoolyearId'])? addText($params["schoolyearId"]):'';
        $MONTH = isset($params['MONTH'])? addText($params["MONTH"]):'';
        $YEAR = isset($params['YEAR'])? addText($params["YEAR"]):'';
        
        $SQL = self::dbAccess()->select();    
        $SQL->from(array("A"=>"t_student_attendance"), array("TOTAL" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS_ID=B.ID', array(''));
        if($absenceType)
            $SQL->where("A.ABSENT_TYPE = ?",$absenceType);
        if($campusId)
            $SQL->where("B.CAMPUS_ID = ?",$campusId);
        if($gradeId)
            $SQL->where("B.GRADE_ID = ?",$gradeId);
        if($schoolyearId)
            $SQL->where("B.SCHOOL_YEAR = ?",$schoolyearId);
        if($MONTH)
            $SQL->where("MONTH(A.END_DATE) = ?",$MONTH);
        if($YEAR)
            $SQL->where("YEAR(A.END_DATE) = ?",$YEAR);
        $result = self::dbAccess()->fetchRow($SQL);
    
        return $result?$result->TOTAL:0;
    }
    
    public static function getDataAttendance($params)
    {
        $absentTypeEntries = AbsentTypeDBAccess::allAbsentType('STUDENT', false);
        $MONTHS = array(
                    '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
                    , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
                    , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
                    );
        $DATASET = "[";
        $i = 0;
       
        foreach ($absentTypeEntries as $absenceObject)
        
        {
            $params['absenceType']=$absenceObject->ID;
            $RESULT = "[";
            $j = 0;
            foreach ($MONTHS as $key => $value)
            {
                $params['MONTH']=$key;
                $RESULT .=$j ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getCountStudentAttendance($params) . "}";
                $j++;
            }
            
            $RESULT .= "]";
            $DATASET .=$i ? "," : "";
            $DATASET .= "{'key' : '" . $absenceObject->NAME . "','color':'$absenceObject->COLOR','values':" . $RESULT . "}";
            $i++;
        }
        $DATASET .= "]";

        return $DATASET;

    }
    ////
    
    ////////////////////////////////////////////////////////////////////////////
    //STUDENT ENROLLMENT TRADITIONAL....
    ////////////////////////////////////////////////////////////////////////////
    public static function getCountStudentBy_Schoolyear_Academic($objectType, $academicId, $schoolyearId)
    {

        $facette = AcademicDBAccess::findGradeFromId($academicId);

        if ($facette)
        {
            if ($facette->EDUCATION_SYSTEM)
            {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_student_schoolyear_subject", array("C" => "COUNT(*)"));
                $SQL->where("SCHOOLYEAR_ID = ?",$schoolyearId);

                switch ($objectType)
                {
                    case "CAMPUS":
                        $SQL->where("CAMPUS_ID = '" . $facette->ID . "'");
                        break;
                    case "SUBJECT":
                        $SQL->where("SUBJECT_ID = '" . $facette->ID . "'");
                        break;
                    case "GROUP":
                        $SQL->where("CLASS_ID = '" . $facette->ID . "'");
                        break;
                }
                $result = self::dbAccess()->fetchRow($SQL);
                return $result ? $result->C : 0;
            }
            else
            {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_student_schoolyear", array("C" => "COUNT(*)"));
                $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);

                switch ($objectType)
                {
                    case "CAMPUS":
                        $SQL->where("CAMPUS = '" . $facette->ID . "'");
                        break;
                    case "GRADE":
                        $SQL->where("GRADE = '" . $facette->ID . "'");
                        break;
                    case "CLASS":
                        $SQL->where("CLASS = '" . $facette->ID . "'");
                        break;
                }
                $SQL->group("SCHOOL_YEAR");
                $result = self::dbAccess()->fetchRow($SQL);
                return $result ? $result->C : 0;
            }
        }
    }

    public static function getDataSetStudentBy_Schoolyear_Academic($objectType, $academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array("*"));
        $SQL->order("START ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        $DATASET = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $schoolyearId = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{'x':'" . setShowText($value->NAME) . "','y':" . self::getCountStudentBy_Schoolyear_Academic($objectType, $academicId, $schoolyearId) . "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    public static function getDataSetStudentTraditionalSystem($objectType = false, $campusId = false, $gradeId = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));

        switch ($objectType)
        {
            case "CAMPUS":
                $SQL->where("OBJECT_TYPE = 'CAMPUS'");
                break;
            case "GRADE":
                $SQL->where("OBJECT_TYPE = 'GRADE'");
                $SQL->where("CAMPUS_ID = '" . $campusId . "'");
                break;
            case "CLASS":
                $SQL->where("OBJECT_TYPE = 'CLASS'");
                $SQL->where("GRADE_ID = '" . $gradeId . "'");
                break;
        }
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $DATASET = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $SHORT = $value->NAME ? setShowText($value->NAME) : ($i + 1) . ") " . SHORT . "?";
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $SHORT . "'";
                $DATASET .= ",'color':'" . getColorFromIndex($i + 2) . "'";
                $DATASET .= ",'values':" . self::getDataSetStudentBy_Schoolyear_Academic($objectType, $value->ID) . "";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    ////////////////////////////////////////////////////////////////////////////
    //Student Training...
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStudentTraining($objectType, $programId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array("*"));

        switch ($objectType)
        {
            case "PROGRAM":
                $SQL->where("OBJECT_TYPE = 'PROGRAM'");
                break;
            case "LEVEL":
                $SQL->where("OBJECT_TYPE = 'LEVEL'");
                $SQL->where("PROGRAM = '" . $programId . "'");
                break;
        }
        $SQL->order("SORTKEY ASC");
        $result = self::dbAccess()->fetchAll($SQL);

        $DATASET = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $SHORT = $value->NAME ? setShowText($value->NAME) : ($i + 1) . ") " . SHORT . "?";
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $SHORT . "'";
                $DATASET .= ",'color':'" . getColorFromIndex($i + 2) . "'";
                $DATASET .= ",'values':" . self::getDataSetStudentByProgramTerm($objectType, $value->ID) . "";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    public static function getDataSetStudentByProgramTerm($objectType, $programId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array("*"));

        switch ($objectType)
        {
            case "CAMUS":
                $SQL->where("OBJECT_TYPE = 'TERM'");
                break;
            case "LEVEL":
                $SQL->where("OBJECT_TYPE = 'TERM'");
                break;
        }

        $SQL->where("START_DATE<=NOW()");
        $SQL->where("END_DATE<=NOW()");
        $SQL->order("START_DATE ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        $DATASET = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{'x':'" . getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE) . "','y':" . self::getCountStudentProgramTerm($programId, $value->ID) . "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    public static function getCountStudentProgramTerm($programId, $termId)
    {
        $SQL = self::dbAccess()->select()
                ->from("t_student_training", array("C" => "COUNT(*)"))
                ->where("PROGRAM = '" . $programId . "'")
                ->where("TERM = '" . $termId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStudentCreditSystem($objectType = false, $campusId = false, $subjectId = false)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));

        switch ($objectType)
        {
            case "CAMPUS":
                $SQL->where("OBJECT_TYPE = 'CAMPUS'");
                $SQL->order("SORTKEY ASC");
                break;
            case "SUBJECT":
                $SQL->where("OBJECT_TYPE = 'GRADE'");
                $SQL->where("CAMPUS_ID = '" . $campusId . "'");
                $SQL->order("SORTKEY ASC");
                break;
            case "GROUP":
                $SQL->where("OBJECT_TYPE = 'CLASS'");
                $SQL->where("SUBJECT_ID = ?",$subjectId);
                $SQL->order("SORTKEY ASC");
                break;
        }
        $SQL->where("EDUCATION_SYSTEM = 1");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $DATASET = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $SHORT = $value->NAME ? setShowText($value->NAME) : ($i + 1) . ") " . SHORT . "?";
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $SHORT . "'";
                $DATASET .= ",'color':'" . getColorFromIndex($i + 2) . "'";
                $DATASET .= ",'values':" . self::getDataSetStudentBy_Schoolyear_Academic($objectType, $value->ID) . "";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    ////////////////////////////////////////////////////////////////////////////
    //STUDENT ADVISORY...
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStudentAdvisory($objectType, $studentId)
    {

        $advisoryTypeEntries = CamemisTypeDBAccess::getCamemisType('ADVISORY_TYPE', false);

        $DATASET = "[";
        switch ($objectType)
        {
            case "WEEKLY":
                if ($advisoryTypeEntries)
                {
                    $i = 0;
                    foreach ($advisoryTypeEntries as $value)
                    {
                        $VALUES = self::getStudentAdvisoryByDayType($value->ID, $studentId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($advisoryTypeEntries)
                {
                    $i = 0;
                    foreach ($advisoryTypeEntries as $value)
                    {
                        $VALUES = self::getStudentAdvisoryByWeekType($value->ID, $studentId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($advisoryTypeEntries)
                {
                    $i = 0;
                    foreach ($advisoryTypeEntries as $value)
                    {
                        $VALUES = self::getStudentAdvisoryByMonthType($value->ID, $studentId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }

        $DATASET .= "]";

        return $DATASET;
    }

    public static function getStudentAdvisoryByDayType($advisoryType, $studentId)
    {
        $DAYS = array(
            '00' => MONDAY
            , '01' => TUESDAY
            , '02' => WEDNESDAY
            , '03' => THURSDAY
            , '04' => FRIDAY
            , '05' => SATURDAY
            , '06' => SUNDAY
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentAdvisoryByDayType($day, $advisoryType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStudentAdvisoryByDayType($day, $advisoryType, $studentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_advisory'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_advisory'), 'A.ADVISORY_ID=B.ID', array());

        if ($day)
            $SQL->where("WEEKDAY(A.ADVISED_DATE) = '" . $day . "'");

        if ($advisoryType)
            $SQL->where("B.ADVISORY_TYPE = '" . $advisoryType . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentAdvisoryByWeekType($advisoryType, $studentId)
    {
        $DAYS = array(
            '01' => "01", '02' => "02", '03' => "03", '04' => "04", '05' => "05", '06' => "06"
            , '07' => "07", '08' => "08", '09' => "09", '10' => "10", '11' => "11", '12' => "12"
            , '13' => "13", '14' => "14", '15' => "15", '16' => "16", '17' => "17", '18' => "18"
            , '19' => "19", '20' => "20", '21' => "21", '22' => "22", '23' => "23", '24' => "24"
            , '25' => "25", '26' => "26", '27' => "27", '28' => "28", '29' => "29", '30' => "30"
            , '31' => "31"
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentAdvisoryByWeekType($day, $advisoryType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStudentAdvisoryByWeekType($day, $advisoryType, $studentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_advisory'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_advisory'), 'A.ADVISORY_ID=B.ID', array());

        if ($day)
            $SQL->where("DAYOFMONTH(A.ADVISED_DATE) = '" . $day . "'");

        if ($advisoryType)
            $SQL->where("A.ADVISORY_TYPE = '" . $advisoryType . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentAdvisoryByMonthType($advisoryType, $studentId)
    {

        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS)
        {
            $i = 0;
            foreach ($MONTHS as $month => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentAdvisoryByMonthType($month, getCurrentYear(), $advisoryType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStudentAdvisoryByMonthType($month, $year, $advisoryType, $studentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_advisory'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_advisory'), 'A.ADVISORY_ID=B.ID', array());

        if ($month)
            $SQL->where("MONTH(A.ADVISED_DATE) = '" . $month . "'");

        if ($year)
            $SQL->where("YEAR(A.ADVISED_DATE) = '" . $year . "'");

        if ($advisoryType)
            $SQL->where("A.ADVISORY_TYPE = '" . $advisoryType . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////@THORN Visal

    public static function getSqlStudentPreschoolByMonthType($month, $year, $objectType, $studentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_preschooltype'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.CAMEMIS_TYPE=B.ID', array());

        if ($month)
            $SQL->where("MONTH(CREATED_DATE) = '" . $month . "'");

        if ($year)
            $SQL->where("YEAR(CREATED_DATE) = '" . $year . "'");

        if ($objectType)
            $SQL->where("CAMEMIS_TYPE = '" . $objectType . "'");

        if ($studentId)
            $SQL->where("PRESTUDENT = '" . $studentId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentPreschoolByMonthType($objectType, $studentId)
    {

        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS)
        {
            $i = 0;
            foreach ($MONTHS as $month => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStudentPreschoolByMonthType($month, getCurrentYear(), $objectType, $studentId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getDataSetStudentPreschool($objectType, $studentId)
    {

        switch ($objectType)
        {
            case "APPLICATION_TYPE":
                $contractTypeEntries = CamemisTypeDBAccess::getCamemisType('APPLICATION_TYPE', false);
                break;
            case "TESTING_TYPE":
                $contractTypeEntries = CamemisTypeDBAccess::getCamemisType('TESTING_TYPE', false);
                break;
        }
        $DATASET = "[";
        if ($contractTypeEntries)
        {
            $i = 0;
            foreach ($contractTypeEntries as $value)
            {
                $VALUES = self::getStudentPreschoolByMonthType($value->ID, $studentId);
                $DATASET .=$i ? "," : "";
                $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                $i++;
            }
        }
        $DATASET .= "]";

        return $DATASET;
    }

    public static function getDataSetStudentTradiionalEnrollmentGender($objectType, $academicId)
    {

        $result = array(
            1 => MALE
            , 2 => FEMALE
            , 0 => "Unbekannt"
        );

        $DATASET = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $key => $value)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{'x':'" . $value . "','y':10}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    public static function getCoundGenderTraditionalEnrollment($genderIndex, $objectType, $academicId, $schoolyearId)
    {

        $SQL = "";
        $SQL .= "SELECT 
        SUM( IF( B.GENDER = 1, 1, 0 ) ) AS MALE,
        SUM( IF( B.GENDER = 2, 1, 0 ) ) AS FEMALE,
        SUM( IF( B.GENDER <> 1 AND B.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
        COUNT( B.GENDER ) AS TOTAL
        FROM t_student_schoolyear AS A
        LEFT JOIN t_student AS B ON A.STUDENT = B.ID";

        $SQL .= " WHERE 1 = 1";
        switch ($objectType)
        {
            case "CAMPUS":
                $SQL .= " AND A.GRADE=" . $academicId;
                break;
            case "GRADE":
                $SQL .= " AND A.CLASS=" . $academicId;
                break;
        }

        if ($schoolyearId)
            $SQL .= " AND A.SCHOOL_YEAR='" . $schoolyearId . "' ";
        //error_log($SQL);
        $facette = self::dbAccess()->fetchRow($SQL);

        $result = 0;
        switch ($genderIndex)
        {
            case "MALE":
                $result = $facette ? $facette->MALE : 0;
                break;
            case "FEMALE":
                $result = $facette ? $facette->FEMALE : 0;
                break;
            case "UNKNOWN":
                $result = $facette ? $facette->UNKNOWN : 0;
                break;
        }

        return $result;
    }

    public static function getDataSetStudentTradiionalEnrollment($objectType, $campusId, $gradeId, $schoolyearId)
    {

        $entries = array('MALE' => MALE, 'FEMALE' => FEMALE);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));

        switch ($objectType)
        {
            case "CAMPUS":
                $SQL->where("OBJECT_TYPE = 'GRADE'");
                $SQL->where("CAMPUS_ID = '" . $campusId . "'");
                break;
            case "GRADE":
                $SQL->where("OBJECT_TYPE = 'CLASS'");
                $SQL->where("GRADE_ID = '" . $gradeId . "'");
                $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);
                break;
        }
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key => $genderName)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $genderName . "'";
                $DATASET .= ",'color':'" . getColorFromIndex($i + 1) . "'";
                $DATASET .= ",'values':[";
                $j = 0;
                foreach ($result as $value)
                {
                    $count = self::getCoundGenderTraditionalEnrollment($key, $objectType, $value->ID, $schoolyearId);
                    $DATASET .= $j ? "," : "";
                    if ($count)
                    {
                        $DATASET .= "{'x':'" . setShowText($value->NAME) . "','y':" . self::getCoundGenderTraditionalEnrollment($key, $objectType, $value->ID, $schoolyearId) . "}";
                    }
                    else
                    {
                        $DATASET .= "{'x':'" . setShowText($value->NAME) . "','y':0}";
                    }

                    $j++;
                }
                $DATASET .= "]";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

}

?>
