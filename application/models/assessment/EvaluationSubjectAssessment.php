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
    CONST INCLUDE_IN_TERM = "2,3";
    CONST INCLUDE_IN_MONTH_TERM = "1,2,3";
    CONST SCORE_TYPE_NUMBER = 1;
    CONST SCORE_TYPE_CHAR = 2;

    /**
     * Evaluation type: (number, percent)
     */
    const EVALUATION_TYPE_COEFF = 0;
    const EVALUATION_TYPE_PERCENTAGE = 1;

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

    function __construct()
    {
        parent::__construct();
    }

    public function setAcademicId($value)
    {
        return $this->academicId = $value;
    }

    public function setSubjectId($value)
    {
        return $this->subjectId = $value;
    }

    public function setTerm($value)
    {
        return $this->term = $value;
    }

    public function setMonthYear($value)
    {
        return $this->monthyear = $value;
    }

    public function setSection($value)
    {
        return $this->section = $value;
    }

    public function setAssignmentId($value)
    {
        return $this->assignmentId = $value;
    }

    public function setDate($value)
    {
        return $this->date = $value;
    }

    public function listStudentsData()
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
    public function getListSubjectResultsByMonth()
    {

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

        if ($this->listClassStudents())
        {

            $scoreList = $this->getScoreListTotalSubjectResultsByMonth($stdClass);

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;

                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $TOTAL_RESULT = $this->getTotalSubjectResultsByMonth($stdClass, self::WITH_FORMAT);
                        $data[$i]["RANK"] = getScoreRank($scoreList, $TOTAL_RESULT);
                        $data[$i]["DISPLAY_TOTAL"] = $TOTAL_RESULT;

                        switch ($this->getSettingEvaluationType())
                        {
                            case self::EVALUATION_TYPE_COEFF:
                                $PERCENTAGE_VALUE = getPercent($TOTAL_RESULT, $this->getSubjectScoreMax());
                                break;
                            case self::EVALUATION_TYPE_PERCENTAGE:
                                $PERCENTAGE_VALUE = $TOTAL_RESULT;
                                break;
                        }

                        $data[$i]["ASSESSMENT_ID"] = AssessmentConfig::calculateGradingScale($PERCENTAGE_VALUE, $this->getSettingQualificationType());

                        break;
                }
                $i++;
            }
        }

        return $data;
    }

    public function getListSubjectResultsByTerm()
    {

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

        if ($this->listClassStudents())
        {

            $scoreList = $this->getScoreListTotalSubjectResultsByTerm($stdClass);

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;
                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $TOTAL_RESULT = $this->getTotalSubjectResultsByTerm($stdClass, self::WITH_FORMAT);

                        $data[$i]["RANK"] = getScoreRank($scoreList, $TOTAL_RESULT);
                        $data[$i]["TOTAL_RESULT"] = $TOTAL_RESULT;
                        $data[$i]["TOTAL_MONTH_RESULT"] = $this->getTotalSubjectResultsByMonth($stdClass, self::WITH_FORMAT);
                        $data[$i]["TOTAL_TERM_RESULT"] = $this->getSubjectResultsByTerm($stdClass, self::INCLUDE_IN_TERM, self::WITH_FORMAT);

                        switch ($this->getSettingEvaluationType())
                        {
                            case self::EVALUATION_TYPE_COEFF:
                                $PERCENTAGE_VALUE = getPercent($TOTAL_RESULT, $this->getSubjectScoreMax());
                                break;
                            case self::EVALUATION_TYPE_PERCENTAGE:
                                $PERCENTAGE_VALUE = $TOTAL_RESULT;
                                break;
                        }

                        $data[$i]["ASSESSMENT_ID"] = AssessmentConfig::calculateGradingScale($PERCENTAGE_VALUE, $this->getSettingQualificationType());

                        break;
                }

                $i++;
            }
        }

        return $data;
    }

    public function getListSubjectResultsByYear()
    {

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

        if ($this->listClassStudents())
        {

            $scoreList = $this->getScoreListTotalSubjectResultsByYear($stdClass);

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;

                $TOTAL_RESULT = $this->getTotalSubjectResultsByYear($stdClass);
                $data[$i]["RANK"] = getScoreRank($scoreList, $TOTAL_RESULT);
                $data[$i]["TOTAL_RESULT"] = $TOTAL_RESULT;

                switch ($this->getTermNumber())
                {
                    case 1:
                        $stdClass->section = "TERM";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_TERM");
                        $data[$i]["FIRST_TERM_RESULT"] = $FIRST->SUBJECT_VALUE;
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_TERM");
                        $data[$i]["SECOND_TERM_RESULT"] = $SECOND->SUBJECT_VALUE;
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_TERM");
                        $data[$i]["THIRD_TERM_RESULT"] = $THIRD->SUBJECT_VALUE;
                        break;
                    case 2:
                        $stdClass->section = "QUARTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_QUARTER");
                        $data[$i]["FIRST_QUARTER_RESULT"] = $FIRST->SUBJECT_VALUE;
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_QUARTER");
                        $data[$i]["SECOND_QUARTER_RESULT"] = $SECOND->SUBJECT_VALUE;
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_QUARTER");
                        $data[$i]["FOURTH_QUARTER_RESULT"] = $THIRD->SUBJECT_VALUE;
                        $FOURTH = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FOURTH_QUARTER");
                        $data[$i]["FOURTH_QUARTER_RESULT"] = $FOURTH->SUBJECT_VALUE;
                        break;
                    default:
                        $stdClass->section = "SEMESTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_SEMESTER");
                        $data[$i]["FIRST_SEMESTER_RESULT"] = $FIRST->SUBJECT_VALUE;
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_SEMESTER");
                        $data[$i]["SECOND_SEMESTER_RESULT"] = $SECOND->SUBJECT_VALUE;
                        break;
                }

                switch ($this->getSettingEvaluationType())
                {
                    case self::EVALUATION_TYPE_COEFF:
                        $PERCENTAGE_VALUE = getPercent($TOTAL_RESULT, $this->getSubjectScoreMax());
                        break;
                    case self::EVALUATION_TYPE_PERCENTAGE:
                        $PERCENTAGE_VALUE = $TOTAL_RESULT;
                        break;
                }

                $data[$i]["ASSESSMENT_ID"] = AssessmentConfig::calculateGradingScale($PERCENTAGE_VALUE, $this->getSettingQualificationType());

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY SUBJECT MONTH RESULT
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplaySubjectResultsByMonth()
    {

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

        $listAssignments = AssessmentConfig::getListAssignmentScoreDate($this->academicId, $this->subjectId, false, $this->monthyear, false);

        if ($this->listClassStudents())
        {

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $data[$i]["RANK"] = $facette->RANK;
                        $data[$i]["DISPLAY_TOTAL"] = $facette->SUBJECT_VALUE;
                        $data[$i]["GRADE_POINTS"] = $facette->GRADE_POINTS;
                        break;
                    case self::SCORE_CHAR:
                        $data[$i]["ASSESSMENT"] = $this->getTotalSubjectAssessmentByMonth($stdClass)->GRADING;
                        break;
                }

                $data[$i]["ASSESSMENT"] = $facette->GRADING;
                if ($listAssignments)
                {
                    foreach ($listAssignments as $object)
                    {
                        $stdClass->assignmentId = $object->ID;
                        $stdClass->date = $object->SCORE_INPUT_DATE;
                        $scoreObject = SQLEvaluationStudentAssignment::getScoreSubjectAssignment($stdClass);
                        $data[$i]["A_" . $object->OBJECT_ID . ""] = $this->displayAssignmentResult($stdClass->evaluationType, $scoreObject);
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
    public function getDisplaySubjectResultsByTerm()
    {
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

        $listAssignments = AssessmentConfig::getListAssignmentScoreDate($this->academicId, $this->subjectId, $this->term, false, false);

        if ($this->listClassStudents())
        {

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);

                switch ($this->getSettingEvaluationOption())
                {
                    case self::EVALUATION_OF_ASSIGNMENT:
                        switch ($this->getSubjectScoreType())
                        {
                            case self::SCORE_NUMBER:
                                $data[$i]["RANK"] = $facette->RANK;
                                $data[$i]["GRADE_POINTS"] = $facette->GRADE_POINTS;
                                $data[$i]["TOTAL_RESULT"] = $facette->SUBJECT_VALUE;
                                $data[$i]["DISPLAY_TOTAL"] = $facette->SUBJECT_VALUE;
                                $data[$i]["DISPLAY_REPEAT"] = $facette->SUBJECT_VALUE_REPEAT ? $facette->SUBJECT_VALUE_REPEAT : "---";
                                break;
                        }
                        $data[$i]["ASSIGNMENT_TERM"] = $facette->ASSIGNMENT_TERM;
                        break;
                    case self::EVALUATION_OF_SUBJECT:
                        switch ($this->getSubjectScoreType())
                        {
                            case self::SCORE_NUMBER:
                                $data[$i]["RANK"] = $facette->RANK;
                                $data[$i]["DISPLAY_TOTAL"] = $facette->SUBJECT_VALUE;
                                $data[$i]["DISPLAY_REPEAT"] = $facette->SUBJECT_VALUE_REPEAT ? $facette->SUBJECT_VALUE_REPEAT : "---";
                                break;
                        }
                        break;
                }

                if ($listAssignments)
                {
                    foreach ($listAssignments as $object)
                    {
                        $stdClass->assignmentId = $object->ID;
                        $stdClass->date = $object->SCORE_INPUT_DATE;
                        $scoreObject = SQLEvaluationStudentAssignment::getScoreSubjectAssignment($stdClass);
                        $data[$i]["A_" . $object->OBJECT_ID . ""] = $this->displayAssignmentResult($stdClass->evaluationType, $scoreObject);
                    }
                }

                if (!$this->getSettingEvaluationOption())
                {
                    $data[$i]["ASSIGNMENT_MONTH"] = $facette->ASSIGNMENT_MONTH;
                }

                $data[$i]["ASSESSMENT"] = $facette->GRADING ? $facette->GRADING : "---";

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DISPLAY SUBJECT YEAR RESULT
    ////////////////////////////////////////////////////////////////////////////
    public function getDisplaySubjectResultsByYear()
    {
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

        if ($this->listClassStudents())
        {

            $data = $this->listStudentsData();
            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {

                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);

                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_NUMBER:
                        $data[$i]["RANK"] = $facette->RANK;
                        $data[$i]["GRADE_POINTS"] = $facette->GRADE_POINTS;
                        $data[$i]["DISPLAY_TOTAL"] = $facette->SUBJECT_VALUE;
                        break;
                }
                switch ($this->getTermNumber())
                {
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

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getTotalSubjectResultsByTerm($stdClass, $withFormat = false)
    {

        $result = 0;
        $OUTPUT = SQLEvaluationStudentAssignment::calculatedSubjectResults($stdClass, self::INCLUDE_IN_MONTH_TERM);

        if ($withFormat)
        {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);
            if (!$COUNT)
            {
                $result = "---";
            }
            else
            {
                $result = displayRound($OUTPUT);
            }
        }
        else
        {
            $result = $OUTPUT;
        }

        return $result;
    }

    public function getTotalSubjectResultsByYear($stdClass)
    {

        $result = 0;
        switch ($this->getTermNumber())
        {
            case 1:
                $stdClass->section = "TERM";
                switch ($this->getSettingYearTermResult())
                {
                    case self::AVG_T1:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_TERM");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_T2:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_TERM");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_T3:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_TERM");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    default:
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_TERM");
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : 0;
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_TERM");
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : 0;
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_TERM");
                        $THIRD_VALUE = is_numeric($THIRD->SUBJECT_VALUE) ? $THIRD->SUBJECT_VALUE : 0;

                        if ($FIRST_VALUE && !$SECOND_VALUE && !$THIRD_VALUE)
                        {
                            $result = $FIRST_VALUE;
                        }
                        elseif (!$FIRST_VALUE && $SECOND_VALUE && !$THIRD_VALUE)
                        {
                            $result = $SECOND_VALUE;
                        }
                        elseif (!$FIRST_VALUE && !$SECOND_VALUE && $THIRD_VALUE)
                        {
                            $result = $THIRD_VALUE;
                        }
                        elseif ($FIRST_VALUE && $SECOND_VALUE && $THIRD_VALUE)
                        {

                            $NUMERATOR = $this->getFirstTermCoeff() * $FIRST_VALUE + $this->getSecondTermCoeff() * $SECOND_VALUE + $this->getThirdTermCoeff() * $THIRD_VALUE;
                            $DEVISOR = $this->getFirstTermCoeff() + $this->getSecondTermCoeff() + $this->getThirdTermCoeff();
                            $result = ($NUMERATOR / $DEVISOR);
                        }
                        else
                        {
                            $result = 0;
                        }
                        break;
                }

                break;
            case 2:
                $stdClass->section = "QUARTER";
                switch ($this->getSettingYearTermResult())
                {
                    case self::AVG_Q1:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_QUARTER");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_Q2:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_QUARTER");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_Q3:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_QUARTER");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_Q4:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FOURTH_QUARTER");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    default:
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_QUARTER");
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : 0;
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_QUARTER");
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : 0;
                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_QUARTER");
                        $THIRD_VALUE = is_numeric($THIRD->SUBJECT_VALUE) ? $THIRD->SUBJECT_VALUE : 0;
                        $FOURTH = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FOURTH_QUARTER");
                        $FOURTH_VALUE = is_numeric($FOURTH->SUBJECT_VALUE) ? $FOURTH->SUBJECT_VALUE : 0;

                        if ($FIRST_VALUE && !$SECOND_VALUE && !$THIRD_VALUE && !$FOURTH_VALUE)
                        {
                            $result = $FIRST_VALUE;
                        }
                        elseif (!$FIRST_VALUE && $SECOND_VALUE && !$THIRD_VALUE && !$FOURTH_VALUE)
                        {
                            $result = $SECOND_VALUE;
                        }
                        elseif (!$FIRST_VALUE && !$SECOND_VALUE && $THIRD_VALUE && !$FOURTH_VALUE)
                        {
                            $result = $THIRD_VALUE;
                        }
                        elseif (!$FIRST_VALUE && !$SECOND_VALUE && !$THIRD_VALUE && $FOURTH_VALUE)
                        {
                            $result = $FOURTH_VALUE;
                        }
                        elseif ($FIRST_VALUE && $SECOND_VALUE && $THIRD_VALUE && $FOURTH_VALUE)
                        {

                            $NUMERATOR = $this->getFirstQuarterCoeff() * $FIRST_VALUE + $this->getSecondQuarterCoeff() * $SECOND_VALUE + $this->getThirdQuarterCoeff() * $THIRD_VALUE + $this->getFourthQuarterCoeff() * $FOURTH_VALUE;
                            $DEVISOR = $this->getFirstQuarterCoeff() + $this->getSecondQuarterCoeff() + $this->getThirdQuarterCoeff() + $this->getFourthQuarterCoeff();
                            $result = ($NUMERATOR / $DEVISOR);
                        }
                        else
                        {
                            $result = 0;
                        }
                        break;
                }

                break;
            default:
                $stdClass->section = "SEMESTER";
                switch ($this->getSettingYearTermResult())
                {
                    case self::AVG_S1:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_SEMESTER");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    case self::AVG_S2:
                        $object = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_SEMESTER");
                        $result = is_numeric($object->SUBJECT_VALUE) ? $object->SUBJECT_VALUE : 0;
                        break;
                    default:
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_SEMESTER");
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : 0;
                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_SEMESTER");
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : 0;

                        if ($FIRST_VALUE && !$SECOND_VALUE)
                        {
                            $result = $FIRST_VALUE;
                        }
                        elseif (!$FIRST_VALUE && $SECOND_VALUE)
                        {
                            $result = $SECOND_VALUE;
                        }
                        elseif ($FIRST_VALUE && $SECOND_VALUE)
                        {
                            $NUMERATOR = $this->getFirstSemesterCoeff() * $FIRST_VALUE + $this->getSecondSemesterCoeff() * $SECOND_VALUE;
                            $DEVISOR = $this->getFirstSemesterCoeff() + $this->getSecondSemesterCoeff();
                            $result = ($NUMERATOR / $DEVISOR);
                        }
                        else
                        {
                            $result = 0;
                        }

                        break;
                }
                break;
        }

        switch ($this->getSettingEvaluationOption())
        {
            case self::EVALUATION_OF_ASSIGNMENT:
                $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignmentByYear($stdClass);
                break;
            case self::EVALUATION_OF_SUBJECT:
                $COUNT = SQLEvaluationStudentSubject::checkStudentSubjectEvaluation($stdClass);
                break;
        }

        if (!$COUNT)
        {
            $OUTPUT = "---";
        }
        else
        {
            if ($result == 0)
            {
                $OUTPUT = 0;
            }
            else
            {
                $OUTPUT = displayRound($result);
            }
        }
        return $OUTPUT;
    }

    public function getTotalSubjectResultsByMonth($stdClass, $withFormat = false)
    {

        $COUNT = "";
        $result = SQLEvaluationStudentAssignment::calculatedSubjectResults($stdClass, false);

        if ($withFormat)
        {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass);
            if (!$COUNT)
            {
                $OUTPUT = "---";
            }
            else
            {
                $OUTPUT = $result;
            }
        }
        else
        {
            $OUTPUT = $result;
        }

        return $OUTPUT;
    }

    public function getSubjectResultsForAllMonths($stdClass, $include, $withFormat = false)
    {

        $COUNT = "";
        $result = SQLEvaluationStudentAssignment::calculatedSubjectResults($stdClass, $include);

        if ($withFormat)
        {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass, $include);
            if (!$COUNT)
            {
                $OUTPUT = "---";
            }
            else
            {
                $OUTPUT = $result;
            }
        }
        else
        {
            $OUTPUT = $result;
        }

        return $OUTPUT;
    }

    public function getSubjectResultsByTerm($stdClass, $include, $withFormat = false)
    {

        $result = SQLEvaluationStudentAssignment::calculatedSubjectResults($stdClass, $include);

        if ($withFormat)
        {
            $COUNT = SQLEvaluationStudentAssignment::checkExistStudentSubjectAssignment($stdClass, $include);

            if (!$COUNT)
            {
                $OUTPUT = "---";
            }
            else
            {
                $OUTPUT = $result;
            }
        }
        else
        {
            $OUTPUT = $result;
        }

        return $OUTPUT;
    }

    public function getImplodeMonthSubjectAssignment($stdClass, $include)
    {

        return SQLEvaluationStudentAssignment::getImplodeQuerySubjectAssignment($stdClass, $include);
    }

    protected function getScoreListTotalSubjectResultsByMonth($stdClass)
    {

        $data = array();
        if ($this->listClassStudents())
        {
            foreach ($this->listClassStudents() as $value)
            {
                $stdClass->studentId = $value->ID;
                $data[] = $this->getTotalSubjectResultsByMonth($stdClass);
            }
        }
        return $data;
    }

    protected function getScoreListTotalSubjectResultsByTerm($stdClass)
    {

        $data = array();
        if ($this->listClassStudents())
        {
            foreach ($this->listClassStudents() as $value)
            {
                $stdClass->studentId = $value->ID;
                $data[] = $this->getTotalSubjectResultsByTerm($stdClass);
            }
        }
        return $data;
    }

    public function getTotalSubjectAssessmentByMonth($stdClass)
    {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    protected function getScoreListTotalSubjectResultsByYear($stdClass)
    {

        $data = array();
        if ($this->listClassStudents())
        {
            foreach ($this->listClassStudents() as $value)
            {
                $stdClass->studentId = $value->ID;
                $data[] = $this->getTotalSubjectResultsByYear($stdClass);
            }
        }
        return $data;
    }

    public function getTotalSubjectAssessmentByTerm($stdClass)
    {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    public function getTotalSubjectAssessmentByYear($stdClass)
    {

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    ////////////////////////////////////////////////////////////////////////////
    //ACTION EDITING...
    ////////////////////////////////////////////////////////////////////////////
    public function actionStudentSubjectAssessment()
    {

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

        switch ($this->actionField)
        {
            case "DISPLAY_TOTAL":
                $defaultObject->average = $this->newValue;
                $defaultObject->oldValue = $this->oldValue;
                $defaultObject->assessmentId = AssessmentConfig::calculateGradingScale($this->newValue, $this->getSettingQualificationType());
                break;
            case "RANK":
                $defaultObject->actionRank = $this->newValue;
                break;
            case "ASSESSMENT":

                switch ($this->getSubjectScoreType())
                {
                    case self::SCORE_TYPE_CHAR:
                        $defaultObject->assessmentId = $this->comboValue;
                        $defaultObject->mappingValue = $this->newValue;
                        break;
                    case self::SCORE_TYPE_NUMBER:
                        $defaultObject->assessmentId = $this->comboValue;
                        if ($this->getSettingEvaluationOption() == self::EVALUATION_OF_ASSIGNMENT)
                        {
                            if ($this->getSubjectValue($defaultObject))
                                $defaultObject->mappingValue = $this->getSubjectValue($defaultObject);
                        }
                        break;
                }

                break;
        }

        $stdClass = $defaultObject;
        SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);

        return SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass);
    }

    public function getSubjectValue($stdClass)
    {

        $result = "";
        switch ($this->getSubjectScoreType())
        {
            case self::SCORE_NUMBER:
                switch ($this->getSection())
                {
                    case "MONTH":
                        $result = $this->getTotalSubjectResultsByMonth($stdClass);
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        $result = $this->getTotalSubjectResultsByTerm($stdClass);
                        break;
                    case "YEAR":
                        $result = $this->getTotalSubjectResultsByYear($stdClass);
                        break;
                }
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
                    , "actionValue" => $this->newValue
                    , "coeffValue" => $this->getAssignmentCoeff()
                    , "pointsPossible" => $this->getAssignmentPointsPossible()
                    , "evaluationType" => $this->getSettingEvaluationType()
                    , "include_in_valuation" => $this->getAssignmentInCludeEvaluation()
                    , "educationSystem" => $this->getEducationSystem()
        );
        SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($stdClass);
        $facette = SQLEvaluationStudentAssignment::getScoreSubjectAssignment($stdClass);

        return $facette;
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

                if ($facette)
                {
                    if ($facette->POINTS_REPEAT)
                    {
                        $data[$i]["SCORE"] = $facette->POINTS_REPEAT;
                        $data[$i]["SCORE_REPEAT"] = $facette->POINTS;
                    }
                    else
                    {
                        $data[$i]["SCORE"] = $facette ? $facette->POINTS : "---";
                        $data[$i]["SCORE_REPEAT"] = "---";
                    }
                    $data[$i]["TEACHER_COMMENTS"] = $facette ? $facette->TEACHER_COMMENTS : "";
                }
                else
                {
                    $data[$i]["SCORE"] = "---";
                    $data[$i]["SCORE_REPEAT"] = "---";
                }

                switch ($this->getSubjectScoreType())
                {
                    case 1:
                        $data[$i]["POINTS_POSSIBLE"] = $this->getAssignmentPointsPossible();
                        break;
                    case 2:
                        $data[$i]["POINTS_POSSIBLE"] = $this->getSubjectScorePossible();
                        break;
                }

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
                    , "term" => $this->term
                    , "date" => $this->date
                    , "assignmentId" => $this->assignmentId
        );

        SQLEvaluationStudentAssignment::getActionDeleteAllStudentsTeacherScoreEnter($stdClass);
    }

    public function actionDeleteOneStudentTeacherScoreEnter()
    {

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

    public function actionScoreImport()
    {

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

        if ($this->getSettingEvaluationOption())
        {

            $stdClass->term = $this->term;
            $stdClass->month = $this->getMonth();
            $stdClass->year = $this->getYear();

            if ($this->term)
            {
                $stdClass->section = $this->getNameSectionByTerm();
            }

            if ($stdClass->month && $stdClass->year)
            {
                $stdClass->section = "MONTH";
            }

            SQLEvaluationImport::importScoreSubject($stdClass);
        }
        else
        {

            if ($this->assignmentId)
            {
                $stdClass->assignmentId = $this->assignmentId;
                $stdClass->date = $this->date;
                $stdClass->month = $this->getMonth();
                $stdClass->year = $this->getYear();
                $stdClass->coeffValue = $this->getAssignmentCoeff();
                $stdClass->include_in_valuation = $this->getAssignmentInCludeEvaluation();
                $stdClass->term = $this->getTermByDateAcademicId();
            }

            $stdClass->educationSystem = $this->getEducationSystem();
            $stdClass->actionField = "SCORE";
            SQLEvaluationImport::importScoreAssignment($stdClass);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //CLICK ACTION CALCULATION...
    ////////////////////////////////////////////////////////////////////////////
    public function actionCalculationSubjectEvaluation()
    {

        switch ($this->target)
        {
            case "MONTH":
                $entries = $this->getListSubjectResultsByMonth();
                $section = "MONTH";
                break;
            case "TERM":
                $entries = $this->getListSubjectResultsByTerm();
                switch ($this->term)
                {
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
                $entries = $this->getListSubjectResultsByYear();
                $section = "YEAR";
                break;
        }

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "section" => $this->getSection()
                    , "creditHours" => $this->getSubjectCreditHours()
                    , "subjectId" => $this->subjectId
                    , "scoreType" => $this->getSubjectScoreType()
                    , "coeffValue" => $this->getSubjectCoeff()
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "term" => $this->term
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "educationSystem" => $this->getEducationSystem()
                    , "evaluationType" => $this->getSettingEvaluationType()
                    , "qualificationType" => $this->getSettingQualificationType()
        );

        if ($entries)
        {
            for ($i = 0; $i <= count($entries); $i++)
            {

                $studentId = isset($entries[$i]["ID"]) ? $entries[$i]["ID"] : "";

                if ($studentId)
                {

                    $stdClass->actionRank = isset($entries[$i]["RANK"]) ? $entries[$i]["RANK"] : "";

                    switch ($this->getSubjectScoreType())
                    {
                        case self::SCORE_NUMBER:
                            $stdClass->assessmentId = isset($entries[$i]["ASSESSMENT_ID"]) ? $entries[$i]["ASSESSMENT_ID"] : "";
                            $stdClass->mappingValue = isset($entries[$i]["TOTAL_RESULT"]) ? $entries[$i]["TOTAL_RESULT"] : "";
                            break;
                        case self::SCORE_CHAR:
                            switch ($this->target)
                            {
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

                    switch ($this->target)
                    {
                        case "TERM":
                            $stdClass->monthResult = isset($entries[$i]["TOTAL_MONTH_RESULT"]) ? $entries[$i]["TOTAL_MONTH_RESULT"] : "";
                            $stdClass->termResult = isset($entries[$i]["TOTAL_TERM_RESULT"]) ? $entries[$i]["TOTAL_TERM_RESULT"] : "";
                            $stdClass->monthAssignment = isset($entries[$i]["ASSIGNMENT_MONTH"]) ? $entries[$i]["ASSIGNMENT_MONTH"] : "";
                            $stdClass->termAssignment = isset($entries[$i]["ASSIGNMENT_TERM"]) ? $entries[$i]["ASSIGNMENT_TERM"] : "";
                            break;
                        case "YEAR":
                            switch ($this->getTermNumber())
                            {
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

    public function getDisplayCreditSubjectStatus()
    {
        if ($this->listClassStudents())
        {

            $stdClass = (object) array(
                        "academicId" => $this->academicId
                        , "subjectId" => $this->subjectId
                        , "schoolyearId" => $this->getSchoolyearId()
            );

            $data = $this->listStudentsData();

            $i = 0;
            foreach ($this->listClassStudents() as $value)
            {
                $stdClass->studentId = $value->ID;

                $data[$i]["NUMBER_CREDIT"] = $this->getSubjectCreditHours();

                switch ($this->getTermNumber())
                {
                    case 1:
                        $stdClass->section = "TERM";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_TERM");
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : "---";
                        $data[$i]["FIRST_LEARNING_RESULT"] = $FIRST_VALUE;
                        $data[$i]["FIRST_CREDIT_STATUS"] = "---";

                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_TERM");
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : "---";
                        $data[$i]["SECOND_LEARNING_RESULT"] = $SECOND_VALUE;
                        $data[$i]["SECOND_CREDIT_STATUS"] = "---";

                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_TERM");
                        $THIRD_VALUE = is_numeric($THIRD->SUBJECT_VALUE) ? $THIRD->SUBJECT_VALUE : "---";
                        $data[$i]["THIRD_LEARNING_RESULT"] = $THIRD_VALUE;
                        $data[$i]["THIRD_CREDIT_STATUS"] = "---";
                        break;
                    case 1:
                        $stdClass->section = "QUARTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_QUARTER");
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : "---";
                        $data[$i]["FIRST_LEARNING_RESULT"] = $FIRST_VALUE;
                        $data[$i]["FIRST_CREDIT_STATUS"] = "---";

                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_QUARTER");
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : "---";
                        $data[$i]["SECOND_LEARNING_RESULT"] = $SECOND_VALUE;
                        $data[$i]["SECOND_CREDIT_STATUS"] = "---";

                        $THIRD = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "THIRD_QUARTER");
                        $THIRD_VALUE = is_numeric($THIRD->SUBJECT_VALUE) ? $THIRD->SUBJECT_VALUE : "---";
                        $data[$i]["THIRD_LEARNING_RESULT"] = $THIRD_VALUE;
                        $data[$i]["THIRD_CREDIT_STATUS"] = "---";

                        $FOURTH = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FOURTH_QUARTER");
                        $FOURTH_VALUE = is_numeric($FOURTH->SUBJECT_VALUE) ? $FOURTH->SUBJECT_VALUE : "---";
                        $data[$i]["FOURTH_LEARNING_RESULT"] = $FOURTH_VALUE;
                        $data[$i]["FOURTH_CREDIT_STATUS"] = "---";
                        break;
                    default:
                        $stdClass->section = "SEMESTER";
                        $FIRST = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "FIRST_SEMESTER");
                        $FIRST_VALUE = is_numeric($FIRST->SUBJECT_VALUE) ? $FIRST->SUBJECT_VALUE : "---";
                        $data[$i]["FIRST_LEARNING_RESULT"] = $FIRST_VALUE;
                        $data[$i]["FIRST_CREDIT_STATUS"] = "---";

                        $SECOND = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, "SECOND_SEMESTER");
                        $SECOND_VALUE = is_numeric($SECOND->SUBJECT_VALUE) ? $SECOND->SUBJECT_VALUE : "---";
                        $data[$i]["SECOND_LEARNING_RESULT"] = $SECOND_VALUE;
                        $data[$i]["SECOND_CREDIT_STATUS"] = "---";
                        break;
                }

                $data[$i]["YEAR_LEARNING_RESULT"] = "---";
                $data[$i]["YEAR_CREDIT_STATUS"] = "---";

                $i++;
            }
        }

        return $data;
    }

}

?>