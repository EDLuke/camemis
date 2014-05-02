<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationStudentAssignment.php';
require_once 'models/assessment/SQLEvaluationStudentSubject.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/SpecialDBAccess.php";

class EvaluationSubjectAssessment extends AssessmentProperties {

    const NO_MONTH = false;
    const NO_YEAR = false;
    const NO_TERM = false;
    const NO_ASSIGNMENT = false;
    const NO_SECTION = false;
    const NO_SCHOOLYEAR_ID = false;
    const SCORE_NUMBER = 1;
    const SCORE_CHAR = 2;
    const INCLUDE_IN_MONTH = 1;
    const INCLUDE_IN_TERM = 2;
    const SCORE_TYPE_NUMBER = 1;
    const SCORE_TYPE_CHAR = 2;

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

    public function getListStudentSubjectAssignments() {

        $data = array();

        $object = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        $entries = SQLEvaluationStudentAssignment::getQueryStudentSubjectAssignments($object);

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

        if ($this->listClassStudents()) {

            $scoreList = $this->getScoreListSubjectMonthResult();

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $studentId = $value->ID;

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->averageMonthSubjectResult($studentId);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                        $data[$i]["AVERAGE"] = $AVERAGE;
                        $data[$i]["ASSESSMENT"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_NUMBER;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_NUMBER;
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_CHAR;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectMonthAssessment($studentId)->ASSESSMENT_ID;
                        break;
                }

                if ($this->getCurrentClassAssignments()) {
                    foreach ($this->getCurrentClassAssignments() as $v) {
                        $data[$i][$v->ASSIGNMENT_ID] = $this->getImplodeMonthSubjectAssignment($studentId, $v->ASSIGNMENT_ID);
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    // TERM CLASS SUBJECT RESULT...
    ////////////////////////////////////////////////////////////////////////////
    public function getSubjectTermResult() {
        $data = array();

        if ($this->listClassStudents()) {

            $scoreList = $this->getScoreListSubjectTermResult($this->term);

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $studentId = $value->ID;

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->calculatedAverageTermSubjectResult($studentId, $this->term);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                        $data[$i]["AVERAGE"] = $AVERAGE;
                        $data[$i]["MONTH_RESULT"] = $this->averageTermSubjectAssignmentByAllMonths($studentId);
                        $data[$i]["TERM_RESULT"] = $this->averageTermSubjectResult($studentId, $this->term);
                        $data[$i]["ASSESSMENT"] = $this->getSubjectTermAssessment($studentId, $this->term)->LETTER_GRADE_NUMBER;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectTermAssessment($studentId, $this->term)->LETTER_GRADE_NUMBER;
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectTermAssessment($studentId, $this->term)->LETTER_GRADE_CHAR;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectTermAssessment($studentId, $this->term)->ASSESSMENT_ID;
                        break;
                }

                if ($this->isDisplayMonthResult()) {
                    $data[$i]["ASSIGNMENT_MONTH"] = $this->getImplodeSubjectAssignmentByAllMonths($studentId);
                }

                $data[$i]["ASSIGNMENT_TERM"] = $this->getImplodeSubjectAssignmentByTerm($studentId);


                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    // YEAR CLASS SUBJECT RESULT...
    ////////////////////////////////////////////////////////////////////////////
    public function getSubjectYearResult() {
        $data = array();

        if ($this->listClassStudents()) {

            $scoreList = $this->getScoreListSubjectYearResult();

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value) {

                $studentId = $value->ID;

                switch ($this->getSubjectScoreType()) {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->calculatedAverageYearSubjectResult($studentId);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $AVERAGE);
                        $data[$i]["AVERAGE"] = $AVERAGE;

                        switch ($this->getTermNumber()) {
                            case 1:
                                $data[$i]["FIRST_TERM_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_TERM");
                                $data[$i]["SECOND_TERM_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_TERM");
                                $data[$i]["THIRD_TERM_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_TERM");
                                break;
                            case 2:
                                $data[$i]["FIRST_QUARTER_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_QUARTER");
                                $data[$i]["SECOND_QUARTER_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_QUARTER");
                                $data[$i]["THIRD_QUARTER_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_QUARTER");
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "FOURTH_QUARTER");
                                break;
                            default:
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_SEMESTER");
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_SEMESTER");
                                break;
                        }

                        $data[$i]["ASSESSMENT"] = $this->getSubjectYearAssessment($studentId)->LETTER_GRADE_NUMBER;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectYearAssessment($studentId)->LETTER_GRADE_NUMBER;
                        break;
                    case self::SCORE_CHAR:

                        switch ($this->getTermNumber()) {
                            case 1:
                                $data[$i]["FIRST_TERM_RESULT"] = $this->getSubjectTermAssessment($studentId, "FIRST_TERM")->LETTER_GRADE_CHAR;
                                $data[$i]["SECOND_TERM_RESULT"] = $this->getSubjectTermAssessment($studentId, "SECOND_TERM")->LETTER_GRADE_CHAR;
                                $data[$i]["THIRD_TERM_RESULT"] = $this->getSubjectTermAssessment($studentId, "THIRD_TERM")->LETTER_GRADE_CHAR;
                                break;
                            case 2:
                                $data[$i]["FIRST_QUARTER_RESULT"] = $this->getSubjectTermAssessment($studentId, "FIRST_QUARTER")->LETTER_GRADE_CHAR;
                                $data[$i]["SECOND_QUARTER_RESULT"] = $this->getSubjectTermAssessment($studentId, "SECOND_QUARTER")->LETTER_GRADE_CHAR;
                                $data[$i]["THIRD_QUARTER_RESULT"] = $this->getSubjectTermAssessment($studentId, "THIRD_QUARTER")->LETTER_GRADE_CHAR;
                                $data[$i]["FOURTH_QUARTER_RESULT"] = $this->getSubjectTermAssessment($studentId, "FOURTH_QUARTER")->LETTER_GRADE_CHAR;
                                break;
                            default:
                                $data[$i]["FIRST_SEMESTER_RESULT"] = $this->getSubjectTermAssessment($studentId, "FIRST_SEMESTER")->LETTER_GRADE_CHAR;
                                $data[$i]["SECOND_SEMESTER_RESULT"] = $this->getSubjectTermAssessment($studentId, "SECOND_SEMESTER")->LETTER_GRADE_CHAR;
                                break;
                        }
                        $data[$i]["ASSESSMENT"] = $this->getSubjectYearAssessment($studentId)->LETTER_GRADE_CHAR;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectYearAssessment($studentId)->ASSESSMENT_ID;
                        break;
                }

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function averageMonthSubjectResult($studentId) {

        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , $this->term
                        , $this->getMonth()
                        , $this->getYear()
                        , self::INCLUDE_IN_MONTH
        );
    }

    public function averageAllMonthsSubjectResult($studentId) {
        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , $this->term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , self::INCLUDE_IN_MONTH
        );
    }

    public function calculatedAverageTermSubjectResult($studentId, $term) {

        $TERM_RESULT = $this->averageTermSubjectResult($studentId, $term);

        if ($this->isDisplayMonthResult()) {
            $MONTH_RESULT = $this->averageAllMonthsSubjectResult($studentId);

            if ($MONTH_RESULT && !$TERM_RESULT) {
                $OUTPUT = $MONTH_RESULT;
            } elseif (!$MONTH_RESULT && $TERM_RESULT) {
                $OUTPUT = $TERM_RESULT;
            } elseif ($MONTH_RESULT && $TERM_RESULT) {
                $OUTPUT = ($MONTH_RESULT + $TERM_RESULT) / 2;
            } else {
                $OUTPUT = 0;
            }
        } else {
            $OUTPUT = $TERM_RESULT;
        }

        return displayRound($OUTPUT);
    }

    public function calculatedAverageYearSubjectResult($studentId) {

        switch ($this->getTermNumber()) {
            case 1:
                $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_TERM");
                $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_TERM");
                $THIRD = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_TERM");

                if ($FIRST && !$SECOND && !$THIRD) {
                    $OUTPUT = $FIRST;
                } elseif (!$FIRST && $SECOND && !$THIRD) {
                    $OUTPUT = $SECOND;
                } elseif (!$FIRST && !$SECOND && $THIRD) {
                    $OUTPUT = $THIRD;
                } elseif ($FIRST && $SECOND && $THIRD) {

                    $NUMERATOR = $this->getFirstTermCoeff() * $FIRST + $this->getSecondTermCoeff() * $SECOND + $this->getThirdTermCoeff() * $THIRD;
                    $DEVISOR = $this->getFirstTermCoeff() + $this->getSecondTermCoeff() + $this->getThirdTermCoeff();
                    $OUTPUT = ($NUMERATOR / $DEVISOR);
                } else {
                    $OUTPUT = 0;
                }
                break;
            case 2:
                $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_QUARTER");
                $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_QUARTER");
                $THIRD = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_QUARTER");
                $FOURTH = $this->calculatedAverageTermSubjectResult($studentId, "FOURTH_QUARTER");

                if ($FIRST && !$SECOND && !$THIRD && !$FOURTH) {
                    $OUTPUT = $FIRST;
                } elseif (!$FIRST && $SECOND && !$THIRD && !$FOURTH) {
                    $OUTPUT = $SECOND;
                } elseif (!$FIRST && !$SECOND && $THIRD && !$FOURTH) {
                    $OUTPUT = $THIRD;
                } elseif (!$FIRST && !$SECOND && !$THIRD && $FOURTH) {
                    $OUTPUT = $FOURTH;
                } elseif ($FIRST && $SECOND && $THIRD && $FOURTH) {

                    $NUMERATOR = $this->getFirstQuarterCoeff() * $FIRST + $this->getSecondQuarterCoeff() * $SECOND + $this->getThirdQuarterCoeff() * $THIRD + $this->getFourthQuarterCoeff() * $FOURTH;
                    $DEVISOR = $this->getFirstQuarterCoeff() + $this->getSecondQuarterCoeff() + $this->getThirdQuarterCoeff() + $this->getFourthQuarterCoeff();
                    $OUTPUT = ($NUMERATOR / $DEVISOR);
                } else {
                    $OUTPUT = 0;
                }
                break;
            default:
                $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_SEMESTER");
                $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_SEMESTER");

                if ($FIRST && !$SECOND) {
                    $OUTPUT = $FIRST;
                } elseif (!$FIRST && $SECOND) {
                    $OUTPUT = $SECOND;
                } elseif ($FIRST && $SECOND) {

                    $NUMERATOR = $this->getFirstSemesterCoeff() * $FIRST + $this->getSecondSemesterCoeff() * $SECOND;
                    $DEVISOR = $this->getFirstSemesterCoeff() + $this->getSecondSemesterCoeff();
                    $OUTPUT = ($NUMERATOR / $DEVISOR);
                } else {
                    $OUTPUT = 0;
                }

                break;
        }

        return displayRound($OUTPUT);
    }

    public function averageTermSubjectResult($studentId, $term) {
        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , $term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , self::INCLUDE_IN_TERM
        );
    }

    public function averageTermSubjectAssignmentByAllMonths($studentId) {
        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , $this->term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , self::INCLUDE_IN_MONTH
        );
    }

    public function getImplodeMonthSubjectAssignment($studentId, $assignmentId) {

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , $assignmentId
                        , self::NO_TERM
                        , $this->getMonth()
                        , $this->getYear()
                        , self::INCLUDE_IN_MONTH
        );
    }

    public function getImplodeSubjectAssignmentByAllMonths($studentId) {
        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , self::NO_ASSIGNMENT
                        , $this->term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , self::INCLUDE_IN_MONTH
        );
    }

    public function getImplodeSubjectAssignmentByTerm($studentId) {

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment(
                        $studentId
                        , $this->academicId
                        , $this->subjectId
                        , self::NO_ASSIGNMENT
                        , $this->term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , self::INCLUDE_IN_TERM
        );
    }

    protected function getScoreListSubjectMonthResult() {

        $data = Array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $studentId = $value->ID;
                $data[] = $this->averageMonthSubjectResult($studentId);
            }
        }
        return $data;
    }

    protected function getScoreListSubjectTermResult($term) {

        $data = Array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $studentId = $value->ID;
                $data[] = $this->calculatedAverageTermSubjectResult($studentId, $term);
            }
        }
        return $data;
    }

    public function getSubjectMonthAssessment($studentId) {

        $object = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => self::NO_TERM
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => "MONTH"
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($object);
    }

    protected function getScoreListSubjectYearResult() {

        $data = Array();
        if ($this->listClassStudents()) {
            foreach ($this->listClassStudents() as $value) {
                $studentId = $value->ID;
                $data[] = $this->calculatedAverageYearSubjectResult($studentId);
            }
        }
        return $data;
    }

    public function getSubjectTermAssessment($studentId, $term) {

        $object = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "SEMESTER"
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($object);
    }

    public function getSubjectYearAssessment($studentId) {

        $object = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "YEAR"
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($object);
    }

    public function actionStudentSubjectAssessment() {

        $object = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "assessmentId" => $this->actionValue
                    , "subjectValue" => $this->getSubjectValue()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "educationSystem" => $this->getEducationSystem()
        );

        return SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($object);
    }

    public function actionPublishSubjectAssessment() {

        $data = array(
            "academicId" => $this->academicId
            , "section" => $this->getSection()
            , "subjectId" => $this->subjectId
            , "scoreType" => $this->getSubjectScoreType()
            , "month" => $this->getMonth()
            , "year" => $this->getYear()
            , "term" => $this->term
            , "actionField" => "SUBJECT_VALUE"
            , "schoolyearId" => $this->getSchoolyearId()
            , "educationSystem" => $this->getEducationSystem()
        );

        switch ($this->getSection()) {
            case "MONTH":
                $result = $this->getSubjectMonthResult();
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                $result = $this->getSubjectTermResult();
                break;
            case "YEAR":
                $result = $this->getSubjectYearResult();
                break;
        }

        for ($i = 0; $i <= count($result); $i++) {

            $data["studentId"] = isset($result[$i]["ID"]) ? $result[$i]["ID"] : "";
            $data["actionRank"] = isset($result[$i]["RANK"]) ? $result[$i]["RANK"] : "";
            $data["assessmentId"] = isset($result[$i]["ASSESSMENT_ID"]) ? $result[$i]["ASSESSMENT_ID"] : "";

            switch ($this->getSubjectScoreType()) {
                case self::SCORE_NUMBER:
                    $data["subjectValue"] = isset($result[$i]["AVERAGE"]) ? $result[$i]["AVERAGE"] : "";
                    break;
                case self::SCORE_CHAR:
                    $data["subjectValue"] = isset($result[$i]["ASSESSMENT"]) ? $result[$i]["ASSESSMENT"] : "";
                    break;
            }

            SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation((object) $data);
        }
    }

    public function getSubjectValue() {

        $result = "";
        switch ($this->getSubjectScoreType()) {
            case self::SCORE_NUMBER:
                switch ($this->getSection()) {
                    case "MONTH":
                        $result = $this->averageMonthSubjectResult($this->studentId);
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        $result = $this->calculatedAverageTermSubjectResult($this->studentId, $this->term);
                        break;
                    case "YEAR":
                        $result = $this->calculatedAverageYearSubjectResult($this->studentId);
                        break;
                }
                break;
            case self::SCORE_CHAR:
                $gradingObject = SpecialDBAccess::findGradingSystemFromId($this->actionValue);
                $result = $gradingObject ? $gradingObject->LETTER_GRADE : "";
                break;
        }
        return $result;
    }

    public function actionTeacherScoreEnter() {
        $object = (object) array(
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
                    , "actionValue" => $this->actionValue
                    , "coeffValue" => $this->getAssignmentCoeff()
                    , "include_in_valuation" => $this->getAssignmentInCludeEvaluation()
                    , "educationSystem" => $this->getEducationSystem()
        );
        SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($object);
    }

    public function countTeacherScoreDate() {

        $object = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        return SQLEvaluationStudentAssignment::getCountTeacherScoreDate($object);
    }

    public function getListStudentsTeacherScoreEnter() {

        $object = (object) array(
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

                $object->studentId = $value->ID;
                $facette = SQLEvaluationStudentAssignment::getScoreSubjectAssignment($object);

                $data[$i]["SCORE"] = $facette ? $facette->POINTS : "";
                $data[$i]["TEACHER_COMMENTS"] = $facette ? $facette->TEACHER_COMMENTS : "";

                $i++;
            }
        }

        return $data;
    }

    public function actionDeleteAllStudentsTeacherScoreEnter() {

        $object = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
        );

        SQLEvaluationStudentAssignment::getActionDeleteAllStudentsTeacherScoreEnter();
    }

    public function actionDeleteOneStudentTeacherScoreEnter() {

        $object = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "assignmentId" => $this->assignmentId
                    , "date" => $this->date
                    , "studentId" => $this->studentId
        );
        SQLEvaluationStudentAssignment::getActionDeleteOneStudentTeacherScoreEnter($object);
    }

    public function actionDeleteSubjectScoreAssessment() {
        $object = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
        );

        SQLEvaluationStudentSubject::getActionDeleteSubjectScoreAssessment($object);
    }

}

?>