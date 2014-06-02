<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationStudentAssignment.php';
require_once 'models/assessment/SQLEvaluationStudentSubject.php';
require_once 'models/assessment/SQLEvaluationImport.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/SpecialDBAccess.php";

class EvaluationSubjectAssessment extends AssessmentProperties {

    CONST NO_MONTH = false;
    CONST NO_YEAR = false;
    CONST NO_TERM = false;
    CONST NO_ASSIGNMENT = false;
    CONST NO_SECTION = false;
    CONST NO_SCHOOLYEAR_ID = false;
    CONST SCORE_NUMBER = 1;
    CONST SCORE_CHAR = 2;
    CONST INCLUDE_IN_MONTH = 1;
    CONST INCLUDE_IN_TERM = 2;
    CONST SCORE_TYPE_NUMBER = 1;
    CONST SCORE_TYPE_CHAR = 2;

    /**
     * Evaluation type: (number, percent)
     */
    const EVALUATION_TYPE_COEFF = 0;
    const EVALUATION_TYPE_PERCENT = 1;

    /**
     * Formular for year result of subject
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
    CONST WITH_FORMAT = true;
    CONST WITHOUT_FORMAT = false;

    /**
     * Evaluation option
     */
    CONST EVALUATION_OF_ASSIGNMENT = 0;
    CONST EVALUATION_OF_SUBJECT = 1;

    function __construct() {
        parent::__construct();
    }

    public function setAcademicId($value) {
        return $this->academicId = $value;
    }

    public function setSubjectId($value) {
        return $this->subjectId = $value;
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

    public function setAssignmentId($value) {
        return $this->assignmentId = $value;
    }

    public function setDate($value) {
        return $this->date = $value;
    }

    public function listStudentsData() {

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

    public function getListStudentSubjectAssignments() {

        $data = array();

        $stdClass = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        $entries = SQLEvaluationStudentAssignment::getQueryStudentSubjectAssignments($stdClass);

        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["ASSIGNMENT"] = setShowText($value->ASSIGNMENT);
                $data[$i]["POINTS"] = $value->POINTS;
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["TEACHER_COMMENTS"] = setShowText($value->TEACHER_COMMENTS);

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    // MONTH CLASS SUBJECT RESULT...
    ////////////////////////////////////////////////////////////////////////////
    public function getSubjectMonthResult() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => $this->getSection()
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        if ($this->listClassStudents()) {

            $scoreList = $this->getScoreListSubjectMonthResult($stdClass);

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->averageMonthSubjectResult($stdClass, self::WITH_FORMAT);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                        $data[$i]["AVERAGE"] = $AVERAGE;
                        $data[$i]["AVERAGE_PERCENT"] = getPercent($AVERAGE, $this->getSubjectScoreMax());
                        break;
                }

                $data[$i]["ASSESSMENT_ID"] = $this->getSubjectMonthAssessment($stdClass)->ASSESSMENT_ID;

                $i++;
            }
        }

        return $data;
    }

    public function getSubjectTermResult() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "SEMESTER"
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        if ($this->listClassStudents()) {

            $scoreList = $this->getScoreListSubjectTermResult($stdClass);

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->calculatedAverageTermSubjectResult($stdClass, self::WITH_FORMAT);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                        $data[$i]["AVERAGE"] = $AVERAGE;
                        $data[$i]["AVERAGE_PERCENT"] = getPercent($AVERAGE, $this->getSubjectScoreMax());
                        $data[$i]["MONTH_RESULT"] = $this->averageTermSubjectAssignmentByAllMonths($stdClass, self::WITH_FORMAT);
                        $data[$i]["TERM_RESULT"] = $this->averageTermSubjectResult($stdClass, self::WITH_FORMAT);
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectTermAssessment($stdClass)->GRADING;
                        break;
                }

                if ($this->getSubjectTermAssessment($stdClass))
                    $data[$i]["ASSESSMENT_ID"] = $this->getSubjectTermAssessment($stdClass)->ASSESSMENT_ID;

                if ($this->isDisplayMonthResult()) {
                    if (!$this->getSettingEvaluationOption()) {
                        $stdClass->include_in_evaluation = self::INCLUDE_IN_MONTH;
                    }

                    $data[$i]["ASSIGNMENT_MONTH"] = $this->getImplodeSubjectAssignmentByAllMonths($stdClass);
                }

                $stdClass->include_in_evaluation = self::INCLUDE_IN_TERM;
                $data[$i]["ASSIGNMENT_TERM"] = $this->getImplodeSubjectAssignmentByTerm($stdClass);

                $i++;
            }
        }

        return $data;
    }

    public function getSubjectYearResult() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "YEAR"
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        if ($this->listClassStudents()) {

            $scoreList = $this->getScoreListSubjectYearResult($stdClass);

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;

                $AVERAGE = $this->calculatedAverageYearSubjectResult($stdClass);
                $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                $data[$i]["AVERAGE"] = $AVERAGE;
                $data[$i]["AVERAGE_PERCENT"] = getPercent($AVERAGE, $this->getSubjectScoreMax());

                switch ($this->getTermNumber()) {
                    case 1:
                        $stdClass->section = "TERM";
                        $stdClass->term = "FIRST_TERM";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["FIRST_TERM_RESULT"] = $FIRST->SUBJECT_VALUE;
                        $stdClass->term = "SECOND_TERM";
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["SECOND_TERM_RESULT"] = $SECOND->SUBJECT_VALUE;
                        $stdClass->term = "THIRD_TERM";
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["THIRD_TERM_RESULT"] = $THIRD->SUBJECT_VALUE;
                        break;
                    case 2:
                        $stdClass->section = "QUARTER";
                        $stdClass->term = "FIRST_QUARTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["FIRST_QUARTER_RESULT"] = $FIRST->SUBJECT_VALUE;
                        $stdClass->term = "SECOND_QUARTER";
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["SECOND_QUARTER_RESULT"] = $SECOND->SUBJECT_VALUE;
                        $stdClass->term = "THIRD_QUARTER";
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["FOURTH_QUARTER_RESULT"] = $THIRD->SUBJECT_VALUE;
                        $stdClass->term = "FOURTH_QUARTER";
                        $FOURTH = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["FOURTH_QUARTER_RESULT"] = $FOURTH->SUBJECT_VALUE;
                        break;
                    default:
                        $stdClass->section = "SEMESTER";
                        $stdClass->term = "FIRST_SEMESTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["FIRST_SEMESTER_RESULT"] = $FIRST->SUBJECT_VALUE;
                        $stdClass->term = "SECOND_SEMESTER";
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $data[$i]["SECOND_SEMESTER_RESULT"] = $SECOND->SUBJECT_VALUE;
                        break;
                }

                $data[$i]["ASSESSMENT_ID"] = $this->getSubjectYearAssessment($stdClass)->ASSESSMENT_ID;
                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY SUBJECT MONTH RESULT
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplaySubjectMonthResult() {

        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => $this->getSection()
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $data[$i]["RANK"] = $facette->RANK;
                        $data[$i]["AVERAGE"] = $facette->SUBJECT_VALUE;
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectMonthAssessment($stdClass)->GRADING;
                        break;
                }

                $data[$i]["ASSESSMENT"] = $facette->GRADING;
                $data[$i]["GPA"] = $facette->GPA ? $facette->GPA : "---";

                if ($this->getSettingEvaluationOption() == self::EVALUATION_OF_ASSIGNMENT) {
                    if ($this->getCurrentClassAssignments()) {
                        foreach ($this->getCurrentClassAssignments() as $v) {
                            $stdClass->assignmentId = $v->ASSIGNMENT_ID;
                            $data[$i][$v->ASSIGNMENT_ID] = $this->getImplodeMonthSubjectAssignment($stdClass);
                        }
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY SUBJECT TERM RESULT
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplaySubjectTermResult() {
        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => $this->getNameSectionByTerm()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);

                switch ($this->getSettingEvaluationOption()) {
                    case self::EVALUATION_OF_ASSIGNMENT:
                        switch ($this->getSubjectScoreType()) {
                            case self::SCORE_NUMBER:
                                $data[$i]["RANK"] = $facette->RANK;
                                $data[$i]["AVERAGE"] = $facette->SUBJECT_VALUE;
                                $data[$i]["MONTH_RESULT"] = $facette->MONTH_RESULT;
                                $data[$i]["TERM_RESULT"] = $facette->TERM_RESULT;
                                break;
                        }
                        $data[$i]["ASSIGNMENT_TERM"] = $facette->ASSIGNMENT_TERM;
                        break;
                    case self::EVALUATION_OF_SUBJECT:
                        switch ($this->getSubjectScoreType()) {
                            case self::SCORE_NUMBER:
                                $data[$i]["RANK"] = $facette->RANK;
                                $data[$i]["AVERAGE"] = $facette->SUBJECT_VALUE;
                                break;
                        }
                        break;
                }

                if ($this->isDisplayMonthResult()) {
                    $data[$i]["ASSIGNMENT_MONTH"] = $facette->ASSIGNMENT_MONTH;
                }

                $data[$i]["ASSESSMENT"] = $facette->GRADING ? $facette->GRADING : "---";
                $data[$i]["GPA"] = $facette->GPA ? $facette->GPA : "---";

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY SUBJECT YEAR RESULT
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplaySubjectYearResult() {
        $data = array();

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "YEAR"
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $data[$i]["RANK"] = $facette->RANK;
                        $data[$i]["AVERAGE"] = $facette->SUBJECT_VALUE;
                        break;
                }
                switch ($this->getTermNumber()) {
                    case 1:
                        $data[$i]["FIRST_TERM_RESULT"] = $facette->FIRST_RESULT;
                        $data[$i]["SECOND_TERM_RESULT"] = $facette->SECOND_RESULT;
                        $data[$i]["THIRD_TERM_RESULT"] = $facette->THIRD_RESULT;
                        break;
                    case 2:
                        $data[$i]["FIRST_QUARTER_RESULT"] = $facette->FIRST_RESULT;
                        $data[$i]["SECOND_QUARTER_RESULT"] = $facette->SECOND_RESULT;
                        $data[$i]["THIRD_QUARTER_RESULT"] = $facette->THIRD_RESULT;
                        $data[$i]["FOURTH_QUARTER_RESULT"] = $facette->FOURTH_RESULT;
                        break;
                    default:
                        $data[$i]["FIRST_SEMESTER_RESULT"] = $facette->FIRST_RESULT;
                        $data[$i]["SECOND_SEMESTER_RESULT"] = $facette->SECOND_RESULT;
                        break;
                }

                $data[$i]["ASSESSMENT"] = $facette->GRADING;
                $data[$i]["GPA"] = $facette->GPA ? $facette->GPA : "---";

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function calculatedAverageTermSubjectResult($stdClass, $withFormat = false) {

        $stdClass->include_in_evaluation = self::INCLUDE_IN_TERM;
        $TERM_RESULT = $this->averageTermSubjectResult($stdClass, false);

        if ($this->isDisplayMonthResult()) {

            $stdClass->include_in_evaluation = self::INCLUDE_IN_MONTH;
            $MONTH_RESULT = $this->averageAllMonthsSubjectResult($stdClass);

            if ($MONTH_RESULT && !$TERM_RESULT) {
                $result = $MONTH_RESULT;
            } elseif (!$MONTH_RESULT && $TERM_RESULT) {
                $result = $TERM_RESULT;
            } elseif ($MONTH_RESULT && $TERM_RESULT) {
                $result = ($MONTH_RESULT + $TERM_RESULT) / 2;
            } else {
                $result = 0;
            }
        } else {
            $result = $TERM_RESULT;
        }

        if ($withFormat) {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);

            if (!$COUNT) {
                $output = "---";
            } else {
                $output = displayRound($result);
            }
        } else {
            $output = $result;
        }

        return $output;
    }

    public function calculatedAverageYearSubjectResult($stdClass) {

        $result = 0;
        switch ($this->getTermNumber()) {
            case 1:
                $stdClass->section = "TERM";
                switch ($this->getSettingYearTermResult()) {
                    case self::AVG_T1:
                        $stdClass->term = "FIRST_TERM";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_T2:
                        $stdClass->term = "SECOND_TERM";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_T3:
                        $stdClass->term = "THIRD_TERM";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    default:
                        $stdClass->term = "FIRST_TERM";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : 0;

                        $stdClass->term = "SECOND_TERM";
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : 0;

                        $stdClass->term = "THIRD_TERM";
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $THIRD_VALUE = is_numeric($THIRD->SUBJECT_VALUE) ? $THIRD->SUBJECT_VALUE : 0;

                        if ($FIRST_VALUE && !$SECOND_VALUE && !$THIRD_VALUE) {
                            $result = $FIRST_VALUE;
                        } elseif (!$FIRST_VALUE && $SECOND_VALUE && !$THIRD_VALUE) {
                            $result = $SECOND_VALUE;
                        } elseif (!$FIRST_VALUE && !$SECOND_VALUE && $THIRD_VALUE) {
                            $result = $THIRD_VALUE;
                        } elseif ($FIRST_VALUE && $SECOND_VALUE && $THIRD_VALUE) {

                            $NUMERATOR = $this->getFirstTermCoeff() * $FIRST_VALUE + $this->getSecondTermCoeff() * $SECOND_VALUE + $this->getThirdTermCoeff() * $THIRD_VALUE;
                            $DEVISOR = $this->getFirstTermCoeff() + $this->getSecondTermCoeff() + $this->getThirdTermCoeff();
                            $result = ($NUMERATOR / $DEVISOR);
                        } else {
                            $result = 0;
                        }
                        break;
                }

                break;
            case 2:
                $stdClass->section = "QUARTER";
                switch ($this->getSettingYearTermResult()) {
                    case self::AVG_Q1:
                        $stdClass->term = "FIRST_QUARTER";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_Q2:
                        $stdClass->term = "SECOND_QUARTER";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_Q3:
                        $stdClass->term = "THIRD_QUARTER";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_Q4:
                        $stdClass->term = "FOURTH_QUARTER";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    default:
                        $stdClass->term = "FIRST_QUARTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : 0;

                        $stdClass->term = "SECOND_QUARTER";
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : 0;

                        $stdClass->term = "THIRD_QUARTER";
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $THIRD_VALUE = is_numeric($THIRD->SUBJECT_VALUE) ? $THIRD->SUBJECT_VALUE : 0;

                        $stdClass->term = "FOURTH_QUARTER";
                        $FOURTH = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $FOURTH_VALUE = is_numeric($FOURTH->SUBJECT_VALUE) ? $FOURTH->SUBJECT_VALUE : 0;

                        if ($FIRST_VALUE && !$SECOND_VALUE && !$THIRD_VALUE && !$FOURTH_VALUE) {
                            $result = $FIRST_VALUE;
                        } elseif (!$FIRST_VALUE && $SECOND_VALUE && !$THIRD_VALUE && !$FOURTH_VALUE) {
                            $result = $SECOND_VALUE;
                        } elseif (!$FIRST_VALUE && !$SECOND_VALUE && $THIRD_VALUE && !$FOURTH_VALUE) {
                            $result = $THIRD_VALUE;
                        } elseif (!$FIRST_VALUE && !$SECOND_VALUE && !$THIRD_VALUE && $FOURTH_VALUE) {
                            $result = $FOURTH_VALUE;
                        } elseif ($FIRST_VALUE && $SECOND_VALUE && $THIRD_VALUE && $FOURTH_VALUE) {

                            $NUMERATOR = $this->getFirstQuarterCoeff() * $FIRST_VALUE + $this->getSecondQuarterCoeff() * $SECOND_VALUE + $this->getThirdQuarterCoeff() * $THIRD_VALUE + $this->getFourthQuarterCoeff() * $FOURTH_VALUE;
                            $DEVISOR = $this->getFirstQuarterCoeff() + $this->getSecondQuarterCoeff() + $this->getThirdQuarterCoeff() + $this->getFourthQuarterCoeff();
                            $result = ($NUMERATOR / $DEVISOR);
                        } else {
                            $result = 0;
                        }
                        break;
                }

                break;
            default:
                $stdClass->section = "SEMESTER";
                switch ($this->getSettingYearTermResult()) {
                    case self::AVG_S1:
                        $stdClass->term = "FIRST_SEMESTER";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_S2:
                        $stdClass->term = "SECOND_SEMESTER";
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    default:

                        $stdClass->term = "FIRST_SEMESTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : 0;

                        $stdClass->term = "SECOND_SEMESTER";
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : 0;

                        if ($FIRST_VALUE && !$SECOND_VALUE) {
                            $result = $FIRST_VALUE;
                        } elseif (!$FIRST_VALUE && $SECOND_VALUE) {
                            $result = $SECOND_VALUE;
                        } elseif ($FIRST_VALUE && $SECOND_VALUE) {
                            $NUMERATOR = $this->getFirstSemesterCoeff() * $FIRST_VALUE + $this->getSecondSemesterCoeff() * $SECOND_VALUE;
                            $DEVISOR = $this->getFirstSemesterCoeff() + $this->getSecondSemesterCoeff();
                            $result = ($NUMERATOR / $DEVISOR);
                        } else {
                            $result = 0;
                        }

                        break;
                }
                break;
        }

        switch ($this->getSettingEvaluationOption()) {
            case self::EVALUATION_OF_ASSIGNMENT:
                $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignmentByYear($stdClass);
                break;
            case self::EVALUATION_OF_SUBJECT:
                $COUNT = SQLEvaluationStudentSubject::checkStudentSubjectEvaluation($stdClass);
                break;
        }

        if (!$COUNT) {
            $output = "---";
        } else {
            if ($result == 0) {
                $output = 0;
            } else {
                $output = displayRound($result);
            }
        }
        return $output;
    }

    public function averageMonthSubjectResult($stdClass, $withFormat = false) {

        $COUNT = "";
        $result = SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);

        if ($withFormat) {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);
            if (!$COUNT) {
                $output = "---";
            } else {
                $output = $result;
            }
        } else {
            $output = $result;
        }

        return $output;
    }

    public function averageAllMonthsSubjectResult($stdClass, $withFormat = false) {

        $COUNT = "";
        $result = SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);

        if ($withFormat) {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);
            if (!$COUNT) {
                $output = "---";
            } else {
                $output = $result;
            }
        } else {
            $output = $result;
        }

        return $output;
    }

    public function averageTermSubjectResult($stdClass, $withFormat = false) {

        $stdClass->include_in_evaluation = self::INCLUDE_IN_TERM;

        $result = SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);

        if ($withFormat) {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);

            if (!$COUNT) {
                $output = "---";
            } else {
                $output = $result;
            }
        } else {
            $output = $result;
        }

        return $output;
    }

    public function averageTermSubjectAssignmentByAllMonths($stdClass) {

        $stdClass->include_in_evaluation = self::INCLUDE_IN_MONTH;
        $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);
        $result = SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);

        if (!$COUNT) {
            $output = "---";
        } else {
            $output = $result;
        }

        return $output;
    }

    public function getImplodeMonthSubjectAssignment($stdClass) {

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass);
    }

    public function getImplodeSubjectAssignmentByAllMonths($stdClass) {

        if ($this->getSettingEvaluationOption()) {
            return SQLEvaluationStudentSubject::getImplodeQueryMonthSubject($stdClass);
        } else {
            $stdClass->assignmentId = self::NO_ASSIGNMENT;
            return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass);
        }
    }

    public function getImplodeSubjectAssignmentByTerm($stdClass) {
        $stdClass->assignmentId = self::NO_ASSIGNMENT;
        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass);
    }

    protected function getScoreListSubjectMonthResult($stdClass) {

        $data = array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;
                $data[] = $this->averageMonthSubjectResult($stdClass);
            }
        }
        return $data;
    }

    protected function getScoreListSubjectTermResult($stdClass) {

        $data = array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;
                $data[] = $this->calculatedAverageTermSubjectResult($stdClass);
            }
        }
        return $data;
    }

    public function getSubjectMonthAssessment($stdClass) {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    protected function getScoreListSubjectYearResult($stdClass) {

        $data = array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $stdClass->studentId = $value->ID;
                $data[] = $this->calculatedAverageYearSubjectResult($stdClass);
            }
        }
        return $data;
    }

    public function getSubjectTermAssessment($stdClass) {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    public function getSubjectYearAssessment($stdClass) {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    public function actionStudentSubjectAssessment() {

        $defaultObject = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->getTermByMonthYear()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "educationSystem" => $this->getEducationSystem()
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        switch ($this->actionField) {
            case "AVERAGE":
                $defaultObject->average = $this->newValue;
                break;
            case "RANK":
                $defaultObject->actionRank = $this->newValue;
                break;
            case "ASSESSMENT":

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_TYPE_CHAR:
                        $defaultObject->assessmentId = $this->comboValue;
                        $defaultObject->mappingValue = $this->newValue;
                        break;
                    case self::SCORE_TYPE_NUMBER:
                        $defaultObject->assessmentId = $this->comboValue;
                        if ($this->getSettingEvaluationOption() == self::EVALUATION_OF_ASSIGNMENT) {
                            if ($this->getSubjectValue($defaultObject))
                                $defaultObject->mappingValue = $this->getSubjectValue($defaultObject);
                        }
                        break;
                }

                break;
        }

        $stdClass = $defaultObject;

        return SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
    }

    public function getSubjectValue($stdClass) {

        $result = "";
        switch ($this->getSubjectScoreType()) {
            case self::SCORE_NUMBER:
                switch ($this->getSection()) {
                    case "MONTH":
                        $result = $this->averageMonthSubjectResult($stdClass);
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        $result = $this->calculatedAverageTermSubjectResult($stdClass);
                        break;
                    case "YEAR":
                        $result = $this->calculatedAverageYearSubjectResult($stdClass);
                        break;
                }
                break;
        }
        return $result;
    }

    public function actionTeacherScoreEnter() {
        $stdClass = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
                    , "actionField" => $this->actionField
                    , "actionValue" => $this->newValue
                    , "coeffValue" => $this->getAssignmentCoeff()
                    , "evaluationType" => $this->getSettingEvaluationType()
                    , "include_in_valuation" => $this->getAssignmentInCludeEvaluation()
                    , "educationSystem" => $this->getEducationSystem()
        );
        SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($stdClass);
    }

    public function countTeacherScoreDate() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        return SQLEvaluationStudentAssignment::getCountTeacherScoreDate($stdClass);
    }

    public function getListStudentsTeacherScoreEnter() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        $data = array();

        if ($this->listClassStudents()) {

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentAssignment::getScoreSubjectAssignment($stdClass);

                $data[$i]["SCORE"] = $facette ? $facette->POINTS : "";
                $data[$i]["TEACHER_COMMENTS"] = $facette ? $facette->TEACHER_COMMENTS : "";

                $i++;
            }
        }

        return $data;
    }

    public function actionDeleteAllStudentsTeacherScoreEnter() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        SQLEvaluationStudentAssignment::getActionDeleteAllStudentsTeacherScoreEnter($stdClass);
    }

    public function actionDeleteOneStudentTeacherScoreEnter() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "assignmentId" => $this->assignmentId
                    , "date" => $this->date
                    , "studentId" => $this->studentId
                    , "evaluationType" => $this->getSettingEvaluationType()
        );
        SQLEvaluationStudentAssignment::getActionDeleteOneStudentTeacherScoreEnter($stdClass);
    }

    public function actionDeleteSubjectScoreAssessment() {
        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
        );

        SQLEvaluationStudentSubject::getActionDeleteSubjectScoreAssessment($stdClass);
    }

    public function acitonSubjectAssignmentModifyScoreDate() {
        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "setId" => $this->setId
                    , "modify_date" => $this->modify_date
        );
        SQLEvaluationStudentAssignment::getAcitonSubjectAssignmentModifyScoreDate($stdClass);
    }

    public function actionContentTeacherScoreInputDate() {
        $stdClass = (object) array(
                    "setId" => $this->setId
                    , "content" => $this->content
        );
        SQLEvaluationStudentAssignment::getActionContentTeacherScoreInputDate($stdClass);
    }

    public function loadContentTeacherScoreInputDate() {
        $stdClass = (object) array(
                    "setId" => $this->setId
        );

        $facette = SQLEvaluationStudentAssignment::findScoreInputDate($stdClass);

        $data = array();

        if ($facette) {
            $data["NAME"] = setShowText($facette->NAME);
            $data["SHORT"] = setShowText($facette->SHORT);
            $data["CONTENT"] = setShowText($facette->CONTENT);
            $data["SCORE_INPUT_DATE"] = getShowDate($facette->SCORE_INPUT_DATE);
        }

        return $data;
    }

    public function actionScoreImport() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "scoreMax" => $this->getSubjectScoreMax()
                    , "scoreMin" => $this->getSubjectScoreMin()
                    , "scoreType" => $this->getSubjectScoreType()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "qualificationType" => $this->getSettingQualificationType()
                    , "listStudents" => $this->listClassStudents()
                    , "tmp_name" => $this->tmp_name
        );

        if ($this->getSettingEvaluationOption()) {

            $stdClass->term = $this->term;
            $stdClass->month = $this->getMonth();
            $stdClass->year = $this->getYear();

            if ($this->term) {
                $stdClass->section = $this->getNameSectionByTerm();
            }

            if ($stdClass->month && $stdClass->year) {
                $stdClass->section = "MONTH";
            }

            SQLEvaluationImport::importScoreSubject($stdClass);
        } else {
            $stdClass->assignmentId = $this->assignmentId;
            $stdClass->date = $this->date;
            $stdClass->month = $this->getMonth();
            $stdClass->year = $this->getYear();
            $stdClass->coeffValue = $this->getAssignmentCoeff();
            $stdClass->include_in_valuation = $this->getAssignmentInCludeEvaluation();
            $stdClass->educationSystem = $this->getEducationSystem();
            $stdClass->term = $this->getTermByDateAcademicId();
            $stdClass->actionField = "SCORE";
            SQLEvaluationImport::importScoreAssignment($stdClass);
        }
    }

    public function calculatedSubjectAssessment($stdClass) {
        
    }

    public function calculatedSubjectGPA($stdClass) {
        
    }

    public function actionCalculationSubjectEvaluation() {

        switch ($this->target) {
            case "MONTH":
                $entries = $this->getSubjectMonthResult();
                $section = "MONTH";
                break;
            case "TERM":
                $entries = $this->getSubjectTermResult();
                switch ($this->term) {
                    case "FIRST_SEMESTER":
                    case "SECOND_SEMESTER":
                        $section = "SEMESTER";
                        break;
                    case "FIRST_TERM":
                    case "SECOND_TERM":
                    case "THIRD_TERM":
                        $section = "TERM";
                        break;
                    case "FIRST_QUARTER":
                    case "SECOND_QUARTER":
                    case "THIRD_QUARTER":
                    case "FOURTH_QUARTER":
                        $section = "QUARTER";
                        break;
                }
                break;
            case "YEAR":
                $entries = $this->getSubjectYearResult();
                $section = "YEAR";
                break;
        }

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "educationSystem" => $this->getEducationSystem()
                    , "evaluationType" => $this->getSettingEvaluationType()
                    , "qualificationType" => $this->getSettingQualificationType()
        );

        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {

                $studentId = isset($entries[$i]["ID"]) ? $entries[$i]["ID"] : "";

                if ($studentId) {

                    $stdClass->actionRank = isset($entries[$i]["RANK"]) ? $entries[$i]["RANK"] : "";

                    switch ($this->getSubjectScoreType()) {
                        case self::SCORE_NUMBER:
                            $stdClass->assessmentId = isset($entries[$i]["ASSESSMENT_ID"]) ? $entries[$i]["ASSESSMENT_ID"] : "";
                            $stdClass->mappingValue = isset($entries[$i]["AVERAGE"]) ? $entries[$i]["AVERAGE"] : "";
                            $stdClass->averagePercent = isset($entries[$i]["AVERAGE_PERCENT"]) ? $entries[$i]["AVERAGE_PERCENT"] : "";
                            break;
                        case self::SCORE_CHAR:
                            switch ($this->target) {
                                case "MONTH":
                                case "TERM":
                                    $stdClass->assessmentId = isset($entries[$i]["ASSESSMENT_ID"]) ? $entries[$i]["ASSESSMENT_ID"] : "";
                                    $stdClass->mappingValue = isset($entries[$i]["ASSESSMENT"]) ? $entries[$i]["ASSESSMENT"] : "";
                                    break;
                            }
                            break;
                    }

                    $stdClass->studentId = $studentId;
                    $stdClass->section = $section;

                    switch ($this->target) {
                        case "TERM":
                            $stdClass->monthResult = isset($entries[$i]["MONTH_RESULT"]) ? $entries[$i]["MONTH_RESULT"] : "";
                            $stdClass->termResult = isset($entries[$i]["TERM_RESULT"]) ? $entries[$i]["TERM_RESULT"] : "";
                            $stdClass->monthAssignment = isset($entries[$i]["ASSIGNMENT_MONTH"]) ? $entries[$i]["ASSIGNMENT_MONTH"] : "";
                            $stdClass->termAssignment = isset($entries[$i]["ASSIGNMENT_TERM"]) ? $entries[$i]["ASSIGNMENT_TERM"] : "";
                            break;
                        case "YEAR":
                            switch ($this->getTermNumber()) {
                                case 1:
                                    $stdClass->firstResult = isset($entries[$i]["FIRST_TERM_RESULT"]) ? $entries[$i]["FIRST_TERM_RESULT"] : "";
                                    $stdClass->secondResult = isset($entries[$i]["SECOND_TERM_RESULT"]) ? $entries[$i]["SECOND_TERM_RESULT"] : "";
                                    $stdClass->thirdResult = isset($entries[$i]["THIRD_TERM_RESULT"]) ? $entries[$i]["THIRD_TERM_RESULT"] : "";
                                    break;
                                case 2:
                                    $stdClass->firstResult = isset($entries[$i]["FIRST_QUARTER_RESULT"]) ? $entries[$i]["FIRST_QUARTER_RESULT"] : "";
                                    $stdClass->secondResult = isset($entries[$i]["SECOND_QUARTER_RESULT"]) ? $entries[$i]["SECOND_QUARTER_RESULT"] : "";
                                    $stdClass->thirdResult = isset($entries[$i]["THIRD_QUARTER_RESULT"]) ? $entries[$i]["THIRD_QUARTER_RESULT"] : "";
                                    $stdClass->fourthResult = isset($entries[$i]["FOURTH_QUARTER_RESULT"]) ? $entries[$i]["FOURTH_QUARTER_RESULT"] : "";

                                    break;
                                default:
                                    $stdClass->firstResult = isset($entries[$i]["FIRST_SEMESTER_RESULT"]) ? $entries[$i]["FIRST_SEMESTER_RESULT"] : "";
                                    $stdClass->secondResult = isset($entries[$i]["SECOND_SEMESTER_RESULT"]) ? $entries[$i]["SECOND_SEMESTER_RESULT"] : "";
                                    break;
                            }
                            break;
                    }

                    SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
                }
            }
        }
    }

}

?>