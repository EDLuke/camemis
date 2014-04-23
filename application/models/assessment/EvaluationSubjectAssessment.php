<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationStudentAssignment.php';
require_once 'models/assessment/SQLEvaluationStudentSubject.php';

class EvaluationSubjectAssessment extends AssessmentProperties {

    const NO_MONTH = false;
    const NO_YEAR = false;
    const NO_TERM = false;
    const NO_ASSIGNMENT = false;
    const NO_SECTION = false;
    const SCORE_NUMBER = 1;
    const SCORE_CHAR = 2;
    const INCLUDE_IN_MONTH = 1;
    const INCLUDE_IN_TERM = 2;

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
                $data[$i]["STUDENT_ID"] = $studentId;
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                $data[$i]["CODE"] = $value->CODE;
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
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_CHAR;
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
                        $data[$i]["ASSESSMENT"] = $this->getSubjectTermAssessment($studentId)->LETTER_GRADE_NUMBER;
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectTermAssessment($studentId)->LETTER_GRADE_CHAR;
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
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectYearAssessment($studentId)->LETTER_GRADE_CHAR;
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
                        , $this->classId
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
                        , $this->classId
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
                        , $this->classId
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
                        , $this->classId
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
                        , $this->classId
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
                        , $this->classId
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
                        , $this->classId
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

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation(
                        $studentId
                        , $this->classId
                        , $this->subjectId
                        , self::NO_TERM
                        , $this->getMonth()
                        , $this->getYear()
                        , self::NO_SECTION
        );
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

    public function getSubjectTermAssessment($studentId) {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation(
                        $studentId
                        , $this->classId
                        , $this->subjectId
                        , $this->term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , self::NO_SECTION
        );
    }

    public function getSubjectYearAssessment($studentId) {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation(
                        $studentId
                        , $this->classId
                        , $this->subjectId
                        , $this->term
                        , self::NO_MONTH
                        , self::NO_YEAR
                        , "YEAR"
        );
    }

}

?>