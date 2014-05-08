<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationStudentSubject.php';
require_once 'models/assessment/SQLAcademicPerformances.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/SpecialDBAccess.php";

class AcademicPerformances extends AssessmentProperties {

    CONST NO_MONTH = false;
    CONST NO_YEAR = false;
    CONST NO_TERM = false;
    CONST NO_ASSIGNMENT = false;
    CONST NO_SECTION = false;
    CONST NO_SCHOOLYEAR_ID = false;
    CONST SCORE_NUMBER = 1;
    CONST SCORE_CHAR = 2;
    CONST SCORE_TYPE_NUMBER = 1;
    CONST SCORE_TYPE_CHAR = 2;

    /**
     * Formular for year result of performance
     */
    CONST AVG_S1 = 1;
    CONST AVG_S2 = 2;
    CONST AVG_T1 = 1;
    CONST AVG_T2 = 2;
    CONST AVG_T3 = 3;
    CONST AVG_Q1 = 1;
    CONST AVG_Q2 = 2;
    CONST AVG_Q3 = 3;
    CONST AVG_Q4 = 4;

    function __construct() {
        parent::__construct();
    }

    protected function listStudentsData() {

        $data = array();

        if ($this->listClassStudents()) {
            $i = 0;
            foreach ($this->listClassStudents() as $value) {
                $studentId = $value->ID;

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($studentId);
                $data[$i]["ID"] = $studentId;
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["STUDENT"] = getFullName($value->FIRSTNAME, $value->LASTNAME);
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;

                $i++;
            }
        }

        return $data;
    }

    public function getListStudentsMonthClassPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $scoreList = SQLAcademicPerformances::scoreListClassPerformance(
                            $this->listClassStudents()
                            , $stdClass
            );

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;

                $AVERAGE = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["AVERAGE_TOTAL"] = $AVERAGE ? $AVERAGE : "---";
                $data[$i]["ASSESSMENT_TOTAL"] = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass)->GRADING;

                if ($this->getListSubjects()) {
                    foreach ($this->getListSubjects() as $v) {
                        if ($v->SUBJECT_ID) {
                            $stdClass->subjectId = $v->SUBJECT_ID;
                            $data[$i][$v->SUBJECT_ID] = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass)->SUBJECT_VALUE;
                        }
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    public function getListStudentsTermClassPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $scoreList = SQLAcademicPerformances::scoreListClassPerformance(
                            $this->listClassStudents()
                            , $stdClass
            );

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;

                $AVERAGE = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["AVERAGE_TOTAL"] = $AVERAGE ? $AVERAGE : "---";
                $data[$i]["ASSESSMENT_TOTAL"] = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass)->GRADING;

                if ($this->getListSubjects()) {
                    foreach ($this->getListSubjects() as $v) {
                        if ($v->SUBJECT_ID) {
                            $stdClass->subjectId = $v->SUBJECT_ID;
                            $data[$i][$v->SUBJECT_ID] = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass)->SUBJECT_VALUE;
                        }
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    public function getListStudentsYearClassPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $scoreList = SQLAcademicPerformances::scoreListClassPerformance(
                            $this->listClassStudents()
                            , $stdClass
            );

            $i = 0;
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;

                $AVERAGE = $this->getAverageYearStudentClassPerformance($stdClass);
                $data[$i]["RANK"] = $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["AVERAGE_TOTAL"] = $AVERAGE ? $AVERAGE : "---";
                $data[$i]["ASSESSMENT_TOTAL"] = "----";

                switch ($this->getTermNumber()) {
                    case 1:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_T1:
                                $stdClass->term = "FIRST_TERM";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["FIRST_TERM_RESULT"] = $RESULT;
                                break;
                            case self::AVG_T2:
                                $stdClass->term = "SECOND_TERM";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["SECOND_TERM_RESULT"] = $RESULT;
                                break;
                            case self::AVG_T3:
                                $stdClass->term = "THIRD_TERM";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["THIRD_TERM_RESULT"] = $RESULT;
                                break;
                            default:
                                $stdClass->term = "FIRST_TERM";
                                $FIRST = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $stdClass->term = "SECOND_TERM";
                                $SECOND = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $stdClass->term = "THIRD_TERM";
                                $THIRD = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $data[$i]["FIRST_TERM_RESULT"] = $FIRST;
                                $data[$i]["SECOND_TERM_RESULT"] = $SECOND;
                                $data[$i]["THIRD_TERM_RESULT"] = $THIRD;
                                break;
                        }

                        break;
                    case 2:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_Q1:
                                $stdClass->term = "FIRST_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["FIRST_QUARTER_RESULT"] = $RESULT;
                                break;
                            case self::AVG_Q2:
                                $stdClass->term = "SECOND_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["SECOND_QUARTER_RESULT"] = $RESULT;
                                break;
                            case self::AVG_Q3:
                                $stdClass->term = "THIRD_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["THIRD_QUARTER_RESULT"] = $RESULT;
                                break;
                            case self::AVG_Q4:
                                $stdClass->term = "FOURTH_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $RESULT;
                                break;
                            default:

                                $stdClass->term = "FIRST_QUARTER";
                                $FIRST = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $stdClass->term = "SECOND_QUARTER";
                                $SECOND = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $stdClass->term = "THIRD_QUARTER";
                                $THIRD = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $stdClass->term = "FOURTH_QUARTER";
                                $FOURTH = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                                $data[$i]["FIRST_QUARTER_RESULT"] = $FIRST;
                                $data[$i]["SECOND_QUARTER_RESULT"] = $SECOND;
                                $data[$i]["THIRD_QUARTER_RESULT"] = $THIRD;
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $FOURTH;
                                break;
                        }

                        break;
                    default:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_S1:
                                $stdClass->term = "FIRST_SEMESTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $RESULT;
                                break;
                            case self::AVG_S2:
                                $stdClass->term = "SECOND_SEMESTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $RESULT;
                                break;
                            default:
                                $stdClass->term = "FIRST_SEMESTER";
                                $FIRST = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $FIRST;

                                $stdClass->term = "SECOND_SEMESTER";
                                $SECOND = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $SECOND;
                                break;
                        }
                        break;
                }

                $i++;
            }
        }

        return $data;
    }

    public function getAverageYearStudentClassPerformance($stdClass) {
        $output = "---";

        switch ($this->getTermNumber()) {
            case 1:

                switch ($this->getSettingYearResult()) {
                    case self::AVG_T1:
                        $stdClass->term = "FIRST_TERM";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    case self::AVG_T2:
                        $stdClass->term = "SECOND_TERM";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    case self::AVG_T3:
                        $stdClass->term = "THIRD_TERM";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    default:
                        $stdClass->term = "FIRST_TERM";
                        $FIRST = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        $stdClass->term = "SECOND_TERM";
                        $SECOND = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        $stdClass->term = "THIRD_TERM";
                        $THIRD = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        if ($FIRST && !$SECOND && !$THIRD) {
                            $output = $FIRST;
                        }

                        if (!$FIRST && $SECOND && !$THIRD) {
                            $output = $SECOND;
                        }

                        if (!$FIRST && !$SECOND && $THIRD) {
                            $output = $THIRD;
                        }

                        if ($FIRST && $SECOND && $THIRD) {
                            $SUM_COEFF = $this->getFirstTermCoeff() + $this->getSecondTermCoeff() + $this->getThirdTermCoeff();
                            $SUM_VALUE = $FIRST * $this->getFirstTermCoeff() + $SECOND * $this->getSecondTermCoeff() + $THIRD * $this->getThirdTermCoeff();
                            $output = displayRound($SUM_VALUE / $SUM_COEFF);
                        }

                        break;
                }

                break;
            case 2:

                switch ($this->getSettingYearResult()) {
                    case self::AVG_Q1:
                        $stdClass->term = "FIRST_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    case self::AVG_Q2:
                        $stdClass->term = "SECOND_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    case self::AVG_Q3:
                        $stdClass->term = "THIRD_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    case self::AVG_Q4:
                        $stdClass->term = "FOURTH_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    default:

                        $stdClass->term = "FIRST_QUARTER";
                        $FIRST = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        $stdClass->term = "SECOND_QUARTER";
                        $SECOND = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        $stdClass->term = "THIRD_QUARTER";
                        $THIRD = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        $stdClass->term = "FOURTH_QUARTER";
                        $FOURTH = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        if ($FIRST && !$SECOND && !$THIRD && !$FOURTH) {
                            $output = $FIRST;
                        }

                        if (!$FIRST && $SECOND && !$THIRD && !$FOURTH) {
                            $output = $SECOND;
                        }

                        if (!$FIRST && !$SECOND && $THIRD && !$FOURTH) {
                            $output = $THIRD;
                        }

                        if (!$FIRST && !$SECOND && !$THIRD && $FOURTH) {
                            $output = $FOURTH;
                        }

                        if ($FIRST && $SECOND && $THIRD && $FOURTH) {
                            $SUM_COEFF = $this->getFirstQuarterCoeff() + $this->getSecondQuarterCoeff() + $this->getThirdQuarterCoeff() + $this->getFourthQuarterCoeff();
                            $SUM_VALUE = $FIRST * $this->getFirstQuarterCoeff() + $SECOND * $this->getSecondQuarterCoeff() + $THIRD * $this->getThirdQuarterCoeff() + $FOURTH * $this->getFourthQuarterCoeff();
                            $output = displayRound($SUM_VALUE / $SUM_COEFF);
                        }

                        break;
                }

                break;
            default:

                switch ($this->getSettingYearResult()) {
                    case self::AVG_S1:
                        $stdClass->term = "FIRST_SEMESTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    case self::AVG_S2:
                        $stdClass->term = "SECOND_SEMESTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                        break;
                    default:
                        $stdClass->term = "FIRST_SEMESTER";
                        $FIRST = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        $stdClass->term = "SECOND_SEMESTER";
                        $SECOND = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);

                        if ($FIRST && !$SECOND) {
                            $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                            $output = $RESULT;
                        }
                        if (!$FIRST && $SECOND) {
                            $RESULT = SQLAcademicPerformances::getSQLAverageStudentClassPerformance($stdClass);
                            $output = $RESULT;
                        }

                        if ($FIRST && $SECOND) {
                            $SUM_COEFF = $this->getFirstSemesterCoeff() + $this->getSecondSemesterCoeff();
                            $SUM_VALUE = $FIRST * $this->getFirstSemesterCoeff() + $SECOND * $this->getSecondSemesterCoeff();
                            $output = displayRound($SUM_VALUE / $SUM_COEFF);
                        }

                        break;
                }
                break;
        }

        return $output;
    }

    public function setActionStudentClassPerformance() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "studentId" => $this->studentId
                    , "actionField" => $this->actionField
                    , "actionValue" => $this->actionValue
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "educationSystem" => $this->getEducationSystem()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "listStudents" => $this->listClassStudents()
        );

        SQLAcademicPerformances::getActionStudentClassPerformance($stdClass);
    }

}

?>