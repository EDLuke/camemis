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

    public function setAcademicId($value) {
        return $this->academicId = $value;
    }

    public function setTerm($value) {
        return $this->term = $value;
    }

    public function setMonthYear($value) {
        return $this->monthyear = $value;
    }

    public function setSection($value) {
        return $this->section = $value;
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

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY MONTH ACADEMIC PERFORMANCES
    ////////////////////////////////////////////////////////////////////////////
    public function getListDisplayStudentsMonthAcademicPerformance() {

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
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $facette = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass);
                
                $data[$i]["RANK"] = $facette->RANK;
                $data[$i]["AVERAGE_TOTAL"] = $facette->TOTAL_RESULT;
                $data[$i]["ASSESSMENT_TOTAL"] = $facette->GRADING;

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
    ////////////////////////////////////////////////////////////////////////////
    
    public function getListStudentsMonthAcademicPerformance() {

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

            $scoreList = SQLAcademicPerformances::scoreListAcademicPerformance(
                            $this->listClassStudents()
                            , $stdClass
            );

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;

                $AVERAGE = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["AVERAGE"] = $AVERAGE ? $AVERAGE : "---";
                $data[$i]["ASSESSMENT"] = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass)->GRADING;

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

    public function getListStudentsTermAcademicPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $scoreList = SQLAcademicPerformances::scoreListAcademicPerformance(
                            $this->listClassStudents()
                            , $stdClass
            );

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $AVERAGE = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["AVERAGE"] = $AVERAGE ? $AVERAGE : "---";
                $data[$i]["ASSESSMENT"] = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass)->GRADING;

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY TERM ACADEMIC PERFORMANCES
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplayListStudentsTermAcademicPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $facette = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass);
                
                $data[$i]["RANK"] = $facette->RANK;
                $data[$i]["AVERAGE_TOTAL"] = $facette->TOTAL_RESULT;
                $data[$i]["ASSESSMENT_TOTAL"] = $facette->GRADING;

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

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY YEAR ACADEMIC PERFORMANCES
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplayListStudentsYearAcademicPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;

                $facette = SQLAcademicPerformances::getCallStudentAcademicPerformance($stdClass);

                $data[$i]["RANK"] = $facette->RANK;
                $data[$i]["AVERAGE_TOTAL"] = $facette->TOTAL_RESULT;
                $data[$i]["ASSESSMENT_TOTAL"] = $facette->GRADING;

                switch ($this->getTermNumber()) {
                    case 1:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_T1:

                                $data[$i]["FIRST_TERM_RESULT"] = $facette->FIRST_RESULT;
                                break;
                            case self::AVG_T2:

                                $data[$i]["SECOND_TERM_RESULT"] = $facette->SECOND_RESULT;
                                break;
                            case self::AVG_T3:

                                $data[$i]["THIRD_TERM_RESULT"] = $facette->THIRD_RESULT;
                                break;
                            default:

                                $data[$i]["FIRST_TERM_RESULT"] = $facette->FIRST_RESULT;
                                $data[$i]["SECOND_TERM_RESULT"] = $facette->SECOND_RESULT;
                                $data[$i]["THIRD_TERM_RESULT"] = $facette->THIRD_RESULT;
                                break;
                        }

                        break;
                    case 2:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_Q1:

                                $data[$i]["FIRST_QUARTER_RESULT"] = $facette->FIRST_RESULT;
                                break;
                            case self::AVG_Q2:

                                $data[$i]["SECOND_QUARTER_RESULT"] = $facette->SECOND_RESULT;
                                break;
                            case self::AVG_Q3:

                                $data[$i]["THIRD_QUARTER_RESULT"] = $facette->THIRD_RESULT;
                                break;
                            case self::AVG_Q4:

                                $data[$i]["FOURTH_QUARTER_RESULT"] = $facette->FOURTH_RESULT;
                                break;
                            default:
                                $data[$i]["FIRST_QUARTER_RESULT"] = $facette->FIRST_RESULT;
                                $data[$i]["SECOND_QUARTER_RESULT"] = $facette->SECOND_RESULT;
                                $data[$i]["THIRD_QUARTER_RESULT"] = $facette->THIRD_RESULT;
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $facette->FOURTH_RESULT;
                                break;
                        }

                        break;
                    default:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_S1:
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $facette->FIRST_RESULT;
                                break;
                            case self::AVG_S2:
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $facette->SECOND_RESULT;
                                break;
                            default:
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $facette->FIRST_RESULT;
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $facette->SECOND_RESULT;
                                break;
                        }
                        break;
                }

                $i++;
            }
        }

        return $data;
    }

    public function getListStudentsYearAcademicPerformance() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $scoreList = $this->getScoreListYearAcademicPerformance($stdClass);

            $i = 0;
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;

                $AVERAGE = $this->getAverageYearStudentAcademicPerformance($stdClass);
                $RANK = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["RANK"] = $RANK ? $RANK : "---";
                $data[$i]["AVERAGE"] = $AVERAGE ? $AVERAGE : "---";
                $data[$i]["ASSESSMENT"] = "----";

                switch ($this->getTermNumber()) {
                    case 1:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_T1:
                                $stdClass->term = "FIRST_TERM";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["FIRST_TERM_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            case self::AVG_T2:
                                $stdClass->term = "SECOND_TERM";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["SECOND_TERM_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            case self::AVG_T3:
                                $stdClass->term = "THIRD_TERM";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["THIRD_TERM_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            default:
                                $stdClass->term = "FIRST_TERM";
                                $FIRST = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $stdClass->term = "SECOND_TERM";
                                $SECOND = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $stdClass->term = "THIRD_TERM";
                                $THIRD = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $data[$i]["FIRST_TERM_RESULT"] = $FIRST ? $FIRST : "----";
                                $data[$i]["SECOND_TERM_RESULT"] = $SECOND ? $SECOND : "----";
                                $data[$i]["THIRD_TERM_RESULT"] = $THIRD ? $THIRD : "----";
                                break;
                        }

                        break;
                    case 2:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_Q1:
                                $stdClass->term = "FIRST_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["FIRST_QUARTER_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            case self::AVG_Q2:
                                $stdClass->term = "SECOND_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["SECOND_QUARTER_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            case self::AVG_Q3:
                                $stdClass->term = "THIRD_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["THIRD_QUARTER_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            case self::AVG_Q4:
                                $stdClass->term = "FOURTH_QUARTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            default:

                                $stdClass->term = "FIRST_QUARTER";
                                $FIRST = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $stdClass->term = "SECOND_QUARTER";
                                $SECOND = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $stdClass->term = "THIRD_QUARTER";
                                $THIRD = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $stdClass->term = "FOURTH_QUARTER";
                                $FOURTH = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                                $data[$i]["FIRST_QUARTER_RESULT"] = $FIRST ? $FIRST : "----";
                                $data[$i]["SECOND_QUARTER_RESULT"] = $SECOND ? $SECOND : "----";
                                $data[$i]["THIRD_QUARTER_RESULT"] = $THIRD ? $THIRD : "----";
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $FOURTH ? $FOURTH : "----";
                                break;
                        }

                        break;
                    default:

                        switch ($this->getSettingYearResult()) {
                            case self::AVG_S1:
                                $stdClass->term = "FIRST_SEMESTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            case self::AVG_S2:
                                $stdClass->term = "SECOND_SEMESTER";
                                $RESULT = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $RESULT ? $RESULT : "----";
                                break;
                            default:
                                $stdClass->term = "FIRST_SEMESTER";
                                $FIRST = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $FIRST ? $FIRST : "----";

                                $stdClass->term = "SECOND_SEMESTER";
                                $SECOND = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $SECOND ? $SECOND : "----";
                                break;
                        }
                        break;
                }

                $i++;
            }
        }

        return $data;
    }

    public function getAverageYearStudentAcademicPerformance($stdClass) {
        $output = "---";

        switch ($this->getTermNumber()) {
            case 1:

                switch ($this->getSettingYearResult()) {
                    case self::AVG_T1:
                        $stdClass->term = "FIRST_TERM";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    case self::AVG_T2:
                        $stdClass->term = "SECOND_TERM";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    case self::AVG_T3:
                        $stdClass->term = "THIRD_TERM";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    default:
                        $stdClass->term = "FIRST_TERM";
                        $FIRST = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        $stdClass->term = "SECOND_TERM";
                        $SECOND = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        $stdClass->term = "THIRD_TERM";
                        $THIRD = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        if (is_numeric($FIRST) && is_numeric($SECOND) && is_numeric($THIRD)) {
                            if ($FIRST && !$SECOND && !$THIRD) {
                                $output = $FIRST;
                            } elseif (!$FIRST && $SECOND && !$THIRD) {
                                $output = $SECOND;
                            } elseif (!$FIRST && !$SECOND && $THIRD) {
                                $output = $THIRD;
                            } elseif ($FIRST && $SECOND && $THIRD) {
                                $SUM_COEFF = $this->getFirstTermCoeff() + $this->getSecondTermCoeff() + $this->getThirdTermCoeff();
                                $SUM_VALUE = $FIRST * $this->getFirstTermCoeff() + $SECOND * $this->getSecondTermCoeff() + $THIRD * $this->getThirdTermCoeff();
                                $output = displayRound($SUM_VALUE / $SUM_COEFF);
                            }
                        }
                        break;
                }

                break;
            case 2:

                switch ($this->getSettingYearResult()) {
                    case self::AVG_Q1:
                        $stdClass->term = "FIRST_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    case self::AVG_Q2:
                        $stdClass->term = "SECOND_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    case self::AVG_Q3:
                        $stdClass->term = "THIRD_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    case self::AVG_Q4:
                        $stdClass->term = "FOURTH_QUARTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    default:

                        $stdClass->term = "FIRST_QUARTER";
                        $FIRST = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        $stdClass->term = "SECOND_QUARTER";
                        $SECOND = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        $stdClass->term = "THIRD_QUARTER";
                        $THIRD = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        $stdClass->term = "FOURTH_QUARTER";
                        $FOURTH = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        if (is_numeric($FIRST) && is_numeric($SECOND) && is_numeric($THIRD) && is_numeric($FOURTH)) {
                            if ($FIRST && !$SECOND && !$THIRD && !$FOURTH) {
                                $output = $FIRST;
                            } elseif (!$FIRST && $SECOND && !$THIRD && !$FOURTH) {
                                $output = $SECOND;
                            } elseif (!$FIRST && !$SECOND && $THIRD && !$FOURTH) {
                                $output = $THIRD;
                            } elseif (!$FIRST && !$SECOND && !$THIRD && $FOURTH) {
                                $output = $FOURTH;
                            } elseif ($FIRST && $SECOND && $THIRD && $FOURTH) {
                                $SUM_COEFF = $this->getFirstQuarterCoeff() + $this->getSecondQuarterCoeff() + $this->getThirdQuarterCoeff() + $this->getFourthQuarterCoeff();
                                $SUM_VALUE = $FIRST * $this->getFirstQuarterCoeff() + $SECOND * $this->getSecondQuarterCoeff() + $THIRD * $this->getThirdQuarterCoeff() + $FOURTH * $this->getFourthQuarterCoeff();
                                $output = displayRound($SUM_VALUE / $SUM_COEFF);
                            }
                        }

                        break;
                }

                break;
            default:

                switch ($this->getSettingYearResult()) {
                    case self::AVG_S1:
                        $stdClass->term = "FIRST_SEMESTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    case self::AVG_S2:
                        $stdClass->term = "SECOND_SEMESTER";
                        $output = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);
                        break;
                    default:
                        $stdClass->term = "FIRST_SEMESTER";
                        $FIRST = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        $stdClass->term = "SECOND_SEMESTER";
                        $SECOND = SQLAcademicPerformances::getSQLAverageStudentAcademicPerformance($stdClass);

                        if (is_numeric($FIRST) && is_numeric($SECOND)) {
                            if ($FIRST && !$SECOND) {
                                $output = $FIRST;
                            } elseif (!$FIRST && $SECOND) {
                                $output = $SECOND;
                            } elseif ($FIRST && $SECOND) {
                                $SUM_COEFF = $this->getFirstSemesterCoeff() + $this->getSecondSemesterCoeff();
                                $SUM_VALUE = $FIRST * $this->getFirstSemesterCoeff() + $SECOND * $this->getSecondSemesterCoeff();
                                $output = displayRound($SUM_VALUE / $SUM_COEFF);
                            }
                        }

                        break;
                }
                break;
        }

        return $output;
    }

    public function setActionStudentAcademicPerformance() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "studentId" => $this->studentId
                    , "assessmentId" => $this->actionValue
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "educationSystem" => $this->getEducationSystem()
                    , "schoolyearId" => $this->getSchoolyearId()
        );
        SQLAcademicPerformances::getActionStudentAcademicPerformance($stdClass);
    }

    public function actionDefaultStudentAcademicPerformance($type) {
        switch ($type) {
            case "MONTH":
                $entries = $this->getListStudentsMonthAcademicPerformance();
                break;
            case "TERM":
                $entries = $this->getListStudentsTermAcademicPerformance();
                break;
            case "YEAR":
                $entries = $this->getListStudentsYearAcademicPerformance();
                break;
        }

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "studentId" => $this->studentId
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "educationSystem" => $this->getEducationSystem()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {

                $stdClass->studentId = isset($entries[$i]["ID"]) ? $entries[$i]["ID"] : "";

                if ($stdClass->studentId) {

                    $rank = isset($entries[$i]["RANK"]) ? $entries[$i]["RANK"] : "";
                    $average = isset($entries[$i]["AVERAGE"]) ? $entries[$i]["AVERAGE"] : "";

                    if (is_numeric($average)) {
                        $stdClass->rank = $rank ? $rank : "---";
                    }

                    $stdClass->average = $average ? $average : "---";

                    switch ($this->getTermNumber()) {
                        case 1:
                            if (isset($entries[$i]["FIRST_TERM_RESULT"]))
                                $stdClass->firstResult = $entries[$i]["FIRST_TERM_RESULT"];
                            if (isset($entries[$i]["SECOND_TERM_RESULT"]))
                                $stdClass->secondResult = $entries[$i]["SECOND_TERM_RESULT"];
                            if (isset($entries[$i]["THIRD_TERM_RESULT"]))
                                $stdClass->thirdResult = $entries[$i]["THIRD_TERM_RESULT"];
                            break;
                        case 2:
                            if (isset($entries[$i]["FIRST_QUARTER_RESULT"]))
                                $stdClass->firstResult = $entries[$i]["FIRST_QUARTER_RESULT"];
                            if (isset($entries[$i]["SECOND_QUARTER_RESULT"]))
                                $stdClass->secondResult = $entries[$i]["SECOND_QUARTER_RESULT"];
                            if (isset($entries[$i]["THIRD_QUARTER_RESULT"]))
                                $stdClass->thirdResult = $entries[$i]["THIRD_QUARTER_RESULT"];
                            if (isset($entries[$i]["FOURTH_QUARTER_RESULT"]))
                                $stdClass->fourthResult = $entries[$i]["FOURTH_QUARTER_RESULT"];
                            break;
                        default:
                            if (isset($entries[$i]["FIRST_SEMESTER_RESULT"]))
                                $stdClass->firstResult = $entries[$i]["FIRST_SEMESTER_RESULT"];
                            if (isset($entries[$i]["SECOND_SEMESTER_RESULT"]))
                                $stdClass->secondResult = $entries[$i]["SECOND_SEMESTER_RESULT"];
                            break;
                    }
                    SQLAcademicPerformances::getActionStudentAcademicPerformance($stdClass);
                }
            }
        }
    }

    protected function getScoreListYearAcademicPerformance($stdClass) {

        $data = array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;
                $value = $this->getAverageYearStudentAcademicPerformance($stdClass);
                $data[] = $value;
            }
        }
        return $data;
    }

}

?>