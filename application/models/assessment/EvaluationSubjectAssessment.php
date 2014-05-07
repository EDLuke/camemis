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

    /**
     * Evaluation type: (number, percent)
     */
    const EVALUATION_TYPE_COEFF = 0;
    const EVALUATION_TYPE_PERCENT = 1;

    /**
     * Formular for year result of subject
     */
    const AVG_S1 = 1;
    const AVG_S2 = 2;
    const AVG_T1 = 1;
    const AVG_T2 = 2;
    const AVG_T3 = 3;
    const AVG_Q1 = 1;
    const AVG_Q2 = 2;
    const AVG_Q3 = 3;
    const AVG_Q4 = 4;

    function __construct()
    {
        parent::__construct();
    }

    protected function listStudentsData()
    {

        $data = array();

        if ($this->listClassStudents())
        {
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {
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

    public function getListStudentSubjectAssignments()
    {

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

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
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
    public function getSubjectMonthResult()
    {

        $data = array();

        if ($this->listClassStudents())
        {

            $scoreList = $this->getScoreListSubjectMonthResult();

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $studentId = $value->ID;

                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->averageMonthSubjectResult($studentId);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $this->displayRank_AND_Average($studentId, $AVERAGE));
                        $data[$i]["AVERAGE"] = $this->displayRank_AND_Average($studentId, $AVERAGE);
                        $data[$i]["ASSESSMENT"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_NUMBER;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_NUMBER;
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getSubjectMonthAssessment($studentId)->LETTER_GRADE_CHAR;
                        $data[$i]["ASSESSMENT_ID"] = $this->getSubjectMonthAssessment($studentId)->ASSESSMENT_ID;
                        break;
                }

                if ($this->getCurrentClassAssignments())
                {
                    foreach ($this->getCurrentClassAssignments() as $v)
                    {
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
    public function getSubjectTermResult()
    {
        $data = array();

        if ($this->listClassStudents())
        {

            $scoreList = $this->getScoreListSubjectTermResult($this->term);

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $studentId = $value->ID;

                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->calculatedAverageTermSubjectResult($studentId, $this->term);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $this->displayRank_AND_Average($studentId, $AVERAGE));
                        $data[$i]["AVERAGE"] = $this->displayRank_AND_Average($studentId, $AVERAGE);
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

                if ($this->isDisplayMonthResult())
                {
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
    public function getSubjectYearResult()
    {
        $data = array();

        if ($this->listClassStudents())
        {

            $scoreList = $this->getScoreListSubjectYearResult();

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $studentId = $value->ID;

                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $AVERAGE = $this->calculatedAverageYearSubjectResult($studentId);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $this->displayRank_AND_Average($studentId, $AVERAGE));
                        $data[$i]["AVERAGE"] = $this->displayRank_AND_Average($studentId, $AVERAGE);

                        switch ($this->getTermNumber())
                        {
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

                        switch ($this->getTermNumber())
                        {
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
    public function averageMonthSubjectResult($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);
    }

    public function averageAllMonthsSubjectResult($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);
    }

    public function calculatedAverageTermSubjectResult($studentId, $term)
    {

        $TERM_RESULT = $this->averageTermSubjectResult($studentId, $term);

        if ($this->isDisplayMonthResult())
        {
            $MONTH_RESULT = $this->averageAllMonthsSubjectResult($studentId);

            if ($MONTH_RESULT && !$TERM_RESULT)
            {
                $OUTPUT = $MONTH_RESULT;
            }
            elseif (!$MONTH_RESULT && $TERM_RESULT)
            {
                $OUTPUT = $TERM_RESULT;
            }
            elseif ($MONTH_RESULT && $TERM_RESULT)
            {
                $OUTPUT = ($MONTH_RESULT + $TERM_RESULT) / 2;
            }
            else
            {
                $OUTPUT = 0;
            }
        }
        else
        {
            $OUTPUT = $TERM_RESULT;
        }

        return displayRound($OUTPUT);
    }

    public function calculatedAverageYearSubjectResult($studentId)
    {

        switch ($this->getTermNumber())
        {
            case 1:

                switch ($this->getSettingYearTermResult())
                {
                    case self::AVG_T1:
                        $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_TERM");
                        $OUTPUT = $FIRST ? $FIRST : 0;
                        break;
                    case self::AVG_T2:
                        $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_TERM");
                        $OUTPUT = $SECOND ? $SECOND : 0;
                        break;
                    case self::AVG_T3:
                        $THIRD = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_TERM");
                        $OUTPUT = $THIRD ? $THIRD : 0;
                        break;
                    default:
                        $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_TERM");
                        $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_TERM");
                        $THIRD = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_TERM");

                        if ($FIRST && !$SECOND && !$THIRD)
                        {
                            $OUTPUT = $FIRST;
                        }
                        elseif (!$FIRST && $SECOND && !$THIRD)
                        {
                            $OUTPUT = $SECOND;
                        }
                        elseif (!$FIRST && !$SECOND && $THIRD)
                        {
                            $OUTPUT = $THIRD;
                        }
                        elseif ($FIRST && $SECOND && $THIRD)
                        {

                            $NUMERATOR = $this->getFirstTermCoeff() * $FIRST + $this->getSecondTermCoeff() * $SECOND + $this->getThirdTermCoeff() * $THIRD;
                            $DEVISOR = $this->getFirstTermCoeff() + $this->getSecondTermCoeff() + $this->getThirdTermCoeff();
                            $OUTPUT = ($NUMERATOR / $DEVISOR);
                        }
                        else
                        {
                            $OUTPUT = 0;
                        }
                        break;
                }

                break;
            case 2:

                switch ($this->getSettingYearTermResult())
                {
                    case self::AVG_Q1:
                        $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_QUARTER");
                        $OUTPUT = $FIRST ? $FIRST : 0;
                        break;
                    case self::AVG_Q2:
                        $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_QUARTER");
                        $OUTPUT = $SECOND ? $SECOND : 0;
                        break;
                    case self::AVG_Q3:
                        $THIRD = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_QUARTER");
                        $OUTPUT = $THIRD ? $THIRD : 0;
                        break;
                    case self::AVG_Q4:
                        $FOURTH = $this->calculatedAverageTermSubjectResult($studentId, "FOURTH_QUARTER");
                        $OUTPUT = $FOURTH ? $FOURTH : 0;
                        break;
                    default:
                        $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_QUARTER");
                        $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_QUARTER");
                        $THIRD = $this->calculatedAverageTermSubjectResult($studentId, "THIRD_QUARTER");
                        $FOURTH = $this->calculatedAverageTermSubjectResult($studentId, "FOURTH_QUARTER");

                        if ($FIRST && !$SECOND && !$THIRD && !$FOURTH)
                        {
                            $OUTPUT = $FIRST;
                        }
                        elseif (!$FIRST && $SECOND && !$THIRD && !$FOURTH)
                        {
                            $OUTPUT = $SECOND;
                        }
                        elseif (!$FIRST && !$SECOND && $THIRD && !$FOURTH)
                        {
                            $OUTPUT = $THIRD;
                        }
                        elseif (!$FIRST && !$SECOND && !$THIRD && $FOURTH)
                        {
                            $OUTPUT = $FOURTH;
                        }
                        elseif ($FIRST && $SECOND && $THIRD && $FOURTH)
                        {

                            $NUMERATOR = $this->getFirstQuarterCoeff() * $FIRST + $this->getSecondQuarterCoeff() * $SECOND + $this->getThirdQuarterCoeff() * $THIRD + $this->getFourthQuarterCoeff() * $FOURTH;
                            $DEVISOR = $this->getFirstQuarterCoeff() + $this->getSecondQuarterCoeff() + $this->getThirdQuarterCoeff() + $this->getFourthQuarterCoeff();
                            $OUTPUT = ($NUMERATOR / $DEVISOR);
                        }
                        else
                        {
                            $OUTPUT = 0;
                        }
                        break;
                }

                break;
            default:

                switch ($this->getSettingYearTermResult())
                {
                    case self::AVG_S1:
                        $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_SEMESTER");
                        $OUTPUT = $FIRST ? $FIRST : 0;
                        break;
                    case self::AVG_S2:
                        $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_SEMESTER");
                        $OUTPUT = $SECOND ? $SECOND : 0;
                        break;
                    default:
                        $FIRST = $this->calculatedAverageTermSubjectResult($studentId, "FIRST_SEMESTER");
                        $SECOND = $this->calculatedAverageTermSubjectResult($studentId, "SECOND_SEMESTER");

                        if ($FIRST && !$SECOND)
                        {
                            $OUTPUT = $FIRST;
                        }
                        elseif (!$FIRST && $SECOND)
                        {
                            $OUTPUT = $SECOND;
                        }
                        elseif ($FIRST && $SECOND)
                        {

                            $NUMERATOR = $this->getFirstSemesterCoeff() * $FIRST + $this->getSecondSemesterCoeff() * $SECOND;
                            $DEVISOR = $this->getFirstSemesterCoeff() + $this->getSecondSemesterCoeff();
                            $OUTPUT = ($NUMERATOR / $DEVISOR);
                        }
                        else
                        {
                            $OUTPUT = 0;
                        }
                        break;
                }
                break;
        }

        return displayRound($OUTPUT);
    }

    public function averageTermSubjectResult($studentId, $term)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_TERM
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);
    }

    public function averageTermSubjectAssignmentByAllMonths($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
                    , "evaluationType" => $this->getSettingEvaluationType()
        );

        return SQLEvaluationStudentAssignment::calculatedAverageSubjectResult($stdClass);
    }

    public function getImplodeMonthSubjectAssignment($studentId, $assignmentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "assignmentId" => $assignmentId
                    , "term" => self::NO_TERM
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
        );

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass);
    }

    public function getImplodeSubjectAssignmentByAllMonths($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "assignmentId" => self::NO_ASSIGNMENT
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_MONTH
        );

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass);
    }

    public function getImplodeSubjectAssignmentByTerm($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "assignmentId" => self::NO_ASSIGNMENT
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "in_clude_in_evaluation" => self::INCLUDE_IN_TERM
        );

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass);
    }

    protected function getScoreListSubjectMonthResult()
    {

        $data = array();
        if ($this->listClassStudents())
        {
            foreach ($this->listClassStudents() as $value)
            {
                $studentId = $value->ID;
                $data[] = $this->averageMonthSubjectResult($studentId);
            }
        }
        return $data;
    }

    protected function getScoreListSubjectTermResult($term)
    {

        $data = array();
        if ($this->listClassStudents())
        {
            foreach ($this->listClassStudents() as $value)
            {
                $studentId = $value->ID;
                $data[] = $this->calculatedAverageTermSubjectResult($studentId, $term);
            }
        }
        return $data;
    }

    public function getSubjectMonthAssessment($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => self::NO_TERM
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => "MONTH"
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    protected function getScoreListSubjectYearResult()
    {

        $data = array();
        if ($this->listClassStudents())
        {
            foreach ($this->listClassStudents() as $value)
            {
                $studentId = $value->ID;
                $data[] = $this->calculatedAverageYearSubjectResult($studentId);
            }
        }
        return $data;
    }

    public function getSubjectTermAssessment($studentId, $term)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "SEMESTER"
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    public function getSubjectYearAssessment($studentId)
    {

        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => self::NO_MONTH
                    , "year" => self::NO_YEAR
                    , "section" => "YEAR"
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    public function actionStudentSubjectAssessment()
    {

        $stdClass = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "assessmentId" => $this->actionValue
                    , "actionValue" => $this->getSubjectValue()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "educationSystem" => $this->getEducationSystem()
        );

        return SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
    }

    public function actionPublishSubjectAssessment()
    {

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

        switch ($this->getSection())
        {
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

        for ($i = 0; $i <= count($result); $i++)
        {

            $data["studentId"] = isset($result[$i]["ID"]) ? $result[$i]["ID"] : "";
            $data["actionRank"] = isset($result[$i]["RANK"]) ? $result[$i]["RANK"] : "";
            $data["assessmentId"] = isset($result[$i]["ASSESSMENT_ID"]) ? $result[$i]["ASSESSMENT_ID"] : "";

            switch ($this->getSubjectScoreType())
            {
                case self::SCORE_NUMBER:
                    $data["actionValue"] = isset($result[$i]["AVERAGE"]) ? $result[$i]["AVERAGE"] : "";
                    break;
                case self::SCORE_CHAR:
                    $data["actionValue"] = isset($result[$i]["ASSESSMENT"]) ? $result[$i]["ASSESSMENT"] : "";
                    break;
            }

            SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation((object) $data);
        }
    }

    public function getSubjectValue()
    {

        $result = "";
        switch ($this->getSubjectScoreType())
        {
            case self::SCORE_NUMBER:
                switch ($this->getSection())
                {
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

    public function actionTeacherScoreEnter()
    {
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
                    , "actionValue" => $this->actionValue
                    , "coeffValue" => $this->getAssignmentCoeff()
                    , "include_in_valuation" => $this->getAssignmentInCludeEvaluation()
                    , "educationSystem" => $this->getEducationSystem()
        );
        SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($stdClass);
    }

    public function countTeacherScoreDate()
    {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        return SQLEvaluationStudentAssignment::getCountTeacherScoreDate($stdClass);
    }

    public function getListStudentsTeacherScoreEnter()
    {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        $data = array();

        if ($this->listClassStudents())
        {

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentAssignment::getScoreSubjectAssignment($stdClass);

                $data[$i]["SCORE"] = $facette ? $facette->POINTS : "";
                $data[$i]["TEACHER_COMMENTS"] = $facette ? $facette->TEACHER_COMMENTS : "";

                $i++;
            }
        }

        return $data;
    }

    public function actionDeleteAllStudentsTeacherScoreEnter()
    {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
        );

        SQLEvaluationStudentAssignment::getActionDeleteAllStudentsTeacherScoreEnter();
    }

    public function actionDeleteOneStudentTeacherScoreEnter()
    {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "assignmentId" => $this->assignmentId
                    , "date" => $this->date
                    , "studentId" => $this->studentId
        );
        SQLEvaluationStudentAssignment::getActionDeleteOneStudentTeacherScoreEnter($stdClass);
    }

    public function actionDeleteSubjectScoreAssessment()
    {
        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
        );

        SQLEvaluationStudentSubject::getActionDeleteSubjectScoreAssessment($stdClass);
    }

    public function acitonSubjectAssignmentModifyScoreDate()
    {
        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "setId" => $this->setId
                    , "modify_date" => $this->modify_date
        );
        SQLEvaluationStudentAssignment::getAcitonSubjectAssignmentModifyScoreDate($stdClass);
    }

    public function actionContentTeacherScoreInputDate()
    {
        $stdClass = (object) array(
                    "setId" => $this->setId
                    , "content" => $this->content
        );
        SQLEvaluationStudentAssignment::getActionContentTeacherScoreInputDate($stdClass);
    }

    public function loadContentTeacherScoreInputDate()
    {
        $stdClass = (object) array(
                    "setId" => $this->setId
        );

        $facette = SQLEvaluationStudentAssignment::findScoreInputDate($stdClass);

        $data = array();

        if ($facette)
        {
            $data["NAME"] = setShowText($facette->NAME);
            $data["SHORT"] = setShowText($facette->SHORT);
            $data["CONTENT"] = setShowText($facette->CONTENT);
            $data["SCORE_INPUT_DATE"] = getShowDate($facette->SCORE_INPUT_DATE);
        }

        return $data;
    }

    public function displayRank_AND_Average($studentId, $average)
    {
        $stdClass = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $this->academicId
                    , "subjectId" => $this->subjectId
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
        );

        $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);

        if (!$average && $COUNT)
        {
            return 0;
        }
        elseif (!$average && !$COUNT)
        {
            return "---";
        }
        else
        {
            return $average;
        }
    }

}

?>