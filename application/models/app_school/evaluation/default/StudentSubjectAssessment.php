<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.10.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/evaluation/default/StudentAssignmentDBAccess.php';
require_once setUserLoacalization();

class StudentSubjectAssessment extends StudentAssignmentDBAccess {

    private static $instance = null;
    //
    public $data = Array();

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function jsonSubjectMonthResult($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;

        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $this->scoreType = $this->classSubject ? $this->classSubject->SCORE_TYPE : "";

        $this->month = getMonthNumberFromMonthYear($this->monthyear);
        $this->year = getYearFromMonthYear($this->monthyear);
        $this->section = 1;

//        parent::$this->section = $this->section;
//        parent::$this->month = $this->month;
//        parent::$this->year = $this->year;
//        parent::$this->classId = $this->classId;
//        parent::$this->subjectId = $this->subjectId;
//        parent::$this->classObject = $this->classObject;
//        parent::$this->classSubject = $this->classSubject;

        return $this->getstudentsSubjectMonthResult();
    }

    public function jsonSubjectSemesterResult($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->section = 2;
        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;

        $this->classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $this->scoreType = $this->classSubject ? $this->classSubject->SCORE_TYPE : "";

//        parent::$this->section = $this->section;
//        parent::$this->term = $this->term;
//        parent::$this->classId = $this->classId;
//        parent::$this->subjectId = $this->subjectId;
//        parent::$this->classSubject = $this->classSubject;

        return $this->getStudentsSubjectSemesterResult();
    }

    public function jsonSubjectYearResult($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->section = 3;
        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;

        $this->classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $this->scoreType = $this->classSubject ? $this->classSubject->SCORE_TYPE : "";

//        parent::$this->section = $this->section;
//        parent::$this->classId = $this->classId;
//        parent::$this->subjectId = $this->subjectId;
//        parent::$this->classSubject = $this->classSubject;

        return $this->getStudentslSubjectYearResult();
    }

    /*     * *******************************************************************
     * Grid: Class subject month result...
     * ********************************************************************** */

    public function getstudentsSubjectMonthResult()
    {

        ini_set('memory_limit', '50M');

        $data = Array();

        $entries = $this->listStudentsByClass();
        $scoreList = $this->scoreListSubjectByMonth();

        if ($this->month && $this->year)
        {
            if ($entries)
            {
                $i = 0;
                foreach ($entries as $value)
                {

                    $this->studentId = $value->STUDENT_ID;
                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                    $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                    $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                    $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                    if (!SchoolDBAccess::displayPersonNameInGrid())
                    {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    }
                    else
                    {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }

                    ////////////////////////////////////////////////////////////
                    //Show Assessment...
                    $data[$i]["ASSESSMENT"] = $this->studentAssessmentMonthSubject($value->STUDENT_ID, $this->subjectId);
                    //Show Average...
                    $AVERAGE = $this->studentAvgMonthSubject($value->STUDENT_ID, $this->subjectId);
                    $data[$i]["AVERAGE"] = $AVERAGE;

                    ////////////////////////////////////////////////////////////
                    //Show Rank
                    $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE);
                    ////////////////////////////////////////////////////////////
                    //Show assignment score implode..
                    if ($this->listAssignmentsByClass())
                    {
                        foreach ($this->listAssignmentsByClass() as $assignment)
                        {
                            $data[$i][$assignment->ASSIGNMENT_ID] = $this->getImplodeStudentAssignment(
                                    $value->STUDENT_ID
                                    , $this->subjectId
                                    , false
                                    , $assignment->ASSIGNMENT_ID
                            );
                        }
                    }
                    ////////////////////////////////////////////////////////////

                    $i++;
                }
            }
        }

        $a = Array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return Array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    /*     * *******************************************************************
     * Grid: Class subject term result...
     * ********************************************************************** */

    public function getStudentsSubjectSemesterResult()
    {

        ini_set('memory_limit', '50M');

        $data = Array();
        $entries = $this->listStudentsByClass();
        $scoreList = $this->scoreListSubjectBySemester();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                ////////////////////////////////////////////////////////////////
                //Show Average...
                $AVERAGE = $this->studentTotalAvgSemesterSubject(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , $this->term);

                $data[$i]["AVERAGE"] = $AVERAGE;
                ////////////////////////////////////////////////////////////////
                //Show Rank
                $data[$i]["RANK"] = $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE);
                ////////////////////////////////////////////////////////////////
                //Show assignment score implode..
                if ($this->listAssignmentsByClass())
                {
                    foreach ($this->listAssignmentsByClass() as $assignment)
                    {
                        $data[$i][$assignment->ASSIGNMENT_ID] = $this->getImplodeStudentAssignment(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , $this->term
                                , $assignment->ASSIGNMENT_ID);
                    }
                }

                $data[$i]["ASSESSMENT"] = $this->studentTotalAssessmentSubjectSemester(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , $this->term);

                $data[$i]["MONTHLY_RESULT"] = $this->studentAvgMonthSubjectInSemester(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , $this->term);

                switch (strtoupper($this->term))
                {
                    case "FIRST_SEMESTER":
                        $data[$i]["FIRST_SEMESTER_RESULT"] = $this->studentAvgSemesterSubjectInSemester(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , $this->term);
                        break;
                    case "SECOND_SEMESTER":
                        $data[$i]["SECOND_SEMESTER_RESULT"] = $this->studentAvgSemesterSubjectInSemester(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , $this->term);
                        break;
                }

                //Ok...
                $data[$i]["ASSIGNMENT_MONTH"] = $this->implodeAssignmentMonthBySemester(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , $this->term);

                $data[$i]["ASSIGNMENT_SEMESTER"] = $this->implodeAssignmentSemesterBySemester(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , $this->term);

                $i++;
            }
        }

        $a = Array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return Array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    /*     * *******************************************************************
     * Grid: Class subject year result...
     * ********************************************************************** */

    public function getStudentslSubjectYearResult()
    {

        ini_set('memory_limit', '50M');
        $data = Array();
        $entries = $this->listStudentsByClass();
        $scoreList = $this->scoreListSubjectByYear();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                switch ($this->scoreType)
                {
                    case 1:
                        $AVG_FS = $this->studentTotalAvgSemesterSubject(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , 'FIRST_SEMESTER'
                        );

                        $AVG_SS = $this->studentTotalAvgSemesterSubject(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , 'SECOND_SEMESTER'
                        );

                        $data[$i]["FIRST_SEMESTER_RESULT"] = $AVG_FS;
                        $data[$i]["SECOND_SEMESTER_RESULT"] = $AVG_SS;
                        break;
                    case 2:

                        $ASSESSMENT_FS = $this->getStudentSubjectYearSemesterAssessment(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , 'FIRST_SEMESTER');

                        $ASSESSMENT_SS = $this->getStudentSubjectYearSemesterAssessment(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , 'SECOND_SEMESTER');

                        $data[$i]["FIRST_SEMESTER_RESULT"] = $ASSESSMENT_FS ? $ASSESSMENT_FS->LETTER_GRADE : "---";
                        $data[$i]["SECOND_SEMESTER_RESULT"] = $ASSESSMENT_SS ? $ASSESSMENT_SS->LETTER_GRADE : "---";

                        break;
                }

                ////////////////////////////////////////////////////////////////
                //Show Average...
                $AVERAGE = $this->studentAvgSubjectByYear(
                        $value->STUDENT_ID
                        , $this->subjectId
                );
                $data[$i]["AVERAGE"] = $AVERAGE;

                $studentAssessment = $this->getStudentSubjectAssessment($value->STUDENT_ID, $this->subjectId);
                if ($studentAssessment)
                {

                    switch ($this->scoreType)
                    {
                        case 1:
                            if (isset($studentAssessment->DESCRIPTION))
                            {
                                $data[$i]["ASSESSMENT"] = $studentAssessment->DESCRIPTION;
                            }
                            break;
                        case 2:
                            if (isset($studentAssessment->LETTER_GRADE))
                            {
                                $data[$i]["ASSESSMENT"] = $studentAssessment->LETTER_GRADE;
                            }
                            break;
                    }
                }

                ////////////////////////////////////////////////////////////////
                //Show Rank
                $data[$i]["RANK"] = $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE);
                ////////////////////////////////////////////////////////////////
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $i++;
            }
        }

        $a = Array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return Array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    /*     * *******************************************************************
     * Action Student Subject Assessment...
     * ********************************************************************** */

    public function jsonActionStudentSubjectAssessment($encrypParams, $noJson = false)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";
        $fieldValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $comment = isset($params["comment"]) ? $params["comment"] : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $this->month = getMonthNumberFromMonthYear($this->monthyear);
        $this->year = getYearFromMonthYear($this->monthyear);

        $assessmentId = "";

        if ($field == "ASSESSMENT")
        {
            $this->studentId = isset($params["id"]) ? addText($params["id"]) : "";
            $assessmentId = $fieldValue;
        }

        if ($comment)
        {
            $this->studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        }

        switch ($this->section)
        {
            case 1:
                $this->setStudentMonthSubjectAssessment(
                        $this->studentId
                        , $this->subjectId
                        , $comment
                        , $assessmentId
                        , $this->scoreListSubjectByMonth()
                );
                break;
            case 2:
                $this->setStudentSemesterSubjectAssessment(
                        $this->studentId
                        , $this->subjectId
                        , $comment
                        , $assessmentId
                        , $this->scoreListSubjectBySemester());
                break;
            case 3:
                $this->setStudentYearSubjectAssessment(
                        $this->studentId
                        , $this->subjectId
                        , $comment
                        , $assessmentId
                        , $this->scoreListSubjectByYear());
                break;
        }

        if (!$noJson)
        {
            return Array(
                "success" => true
            );
        }
    }

    /*     * *******************************************************************
     * Load: Student Assessment...
     * ********************************************************************** */

    public function jsonListStudentsSubjectAssessment($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";
        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $data = Array();

        $this->month = getMonthNumberFromMonthYear($this->monthyear);
        $this->year = getYearFromMonthYear($this->monthyear);
        $facette = $this->getStudentSubjectAssessment($this->studentId, $this->subjectId);

        if ($facette)
        {
            $data["comment"] = setShowText($facette->TEACHER_COMMENT);
            $data["subjectValue"] = $facette ? $facette->SUBJECT_VALUE : "---";
            $data["rank"] = $facette ? $facette->RANK : "---";
        }

        return Array(
            "success" => true
            , "data" => $data
        );
    }

    protected function studentAssessmentMonthSubject($studentId, $subjectId)
    {

        $result = "---";
        $CHECK = $this->getCountScoreEnterByStudent($studentId, $subjectId, false, false);
        if ($CHECK)
        {
            $studentAssessment = $this->getStudentSubjectAssessment($studentId, $subjectId);
            if ($studentAssessment)
            {
                if (isset($studentAssessment->DESCRIPTION))
                {

                    switch ($this->scoreType)
                    {
                        case 1:
                            $result = $studentAssessment->DESCRIPTION;
                            break;
                        case 2:
                            $result = $studentAssessment->LETTER_GRADE;
                            break;
                    }
                }
            }
        }

        return $result;
    }

    protected function studentAvgMonthSubject($studentId, $subjectId)
    {

        $CHECK = $this->getCountScoreEnterByStudent($studentId, $subjectId, false, false);

        if ($CHECK)
        {
            return $this->studentAvgAllAssignmentsBySubject(
                            $studentId
                            , $subjectId
                            , false
                            , false
                            , false
            );
        }
        else
        {
            return "---";
        }
    }

    protected function studentAvgMonthSubjectInSemester($studentId, $subjectId, $term)
    {

        $CHECK = $this->getCountScoreEnterByStudent($studentId, $subjectId, $term, 1);
        if ($CHECK)
        {
            return $this->studentAvgAllAssignmentsBySubject(
                            $studentId
                            , $subjectId
                            , $term
                            , false
                            , 1
            );
        }
        else
        {
            return "---";
        }
    }

    protected function studentTotalAssessmentSubjectSemester($studentId, $subjectId, $term)
    {

        $result = "---";

        switch ($this->scoreType)
        {
            case 1:
                $CHECK = $this->getCountScoreEnterByStudent($studentId, $subjectId, $term, false);
                if ($CHECK)
                {
                    $studentAssessment = $this->getStudentSubjectAssessment($studentId, $subjectId, "ASSESSMENT");
                    if ($studentAssessment)
                    {
                        if (isset($studentAssessment->DESCRIPTION))
                        {
                            $result = $studentAssessment->DESCRIPTION;
                        }
                    }
                }
                break;
            case 2:
                $studentAssessment = $this->getStudentSubjectAssessment($studentId, $subjectId, "ASSESSMENT");
                if ($studentAssessment)
                {
                    if (isset($studentAssessment->LETTER_GRADE))
                    {
                        $result = $studentAssessment->LETTER_GRADE;
                    }
                }
                break;
        }

        return $result;
    }

    protected function studentAvgSemesterSubjectInSemester($studentId, $subjectId, $term)
    {

        $CHECK = $this->getCountScoreEnterByStudent($studentId, $subjectId, $term, 2);
        if ($CHECK)
        {
            return $this->studentAvgAllAssignmentsBySubject(
                            $studentId
                            , $subjectId
                            , $term
                            , false
                            , 2
            );
        }
        else
        {
            return "---";
        }
    }

    protected function studentTotalAvgSemesterSubject($studentId, $subjectId, $term)
    {

        $CHECK = $this->getCountScoreEnterByStudent($studentId, $subjectId, $term, false);
        if ($CHECK)
        {
            return $this->studentAvgAllAssignmentsBySubject(
                            $studentId
                            , $subjectId
                            , $term
                            , false
                            , false
            );
        }
        else
        {
            return "---";
        }
    }

    protected function studentAvgSubjectByYear($studentId, $subjectId)
    {

        $result = "";
        $AVG_FIRST_SEMSTER = "";
        $AVG_SECOND_SEMSTER = "";

        $firstCoeff = $this->classObject->SEMESTER1_WEIGHTING ? $this->classObject->SEMESTER1_WEIGHTING : 1;
        $secondCoeff = $this->classObject->SEMESTER2_WEIGHTING ? $this->classObject->SEMESTER2_WEIGHTING : 1;

        $firstSemester = $this->studentTotalAvgSemesterSubject(
                $studentId
                , $subjectId
                , "FIRST_SEMESTER"
        );

        if (is_numeric($firstSemester))
        {
            $AVG_FIRST_SEMSTER = $firstSemester * $firstCoeff;
        }

        $secondSemester = $this->studentTotalAvgSemesterSubject(
                $studentId
                , $subjectId
                , "SECOND_SEMESTER"
        );

        if (is_numeric($secondSemester))
        {
            $AVG_SECOND_SEMSTER = $secondSemester * $secondCoeff;
        }

        switch ($this->classObject->YEAR_RESULT)
        {
            case 1:
                if (is_numeric($AVG_FIRST_SEMSTER))
                {
                    $result = setRound(($AVG_FIRST_SEMSTER) / ($firstCoeff));
                }
                break;
            case 2:
                if (is_numeric($AVG_SECOND_SEMSTER))
                {
                    $result = setRound(($AVG_SECOND_SEMSTER) / ($secondCoeff));
                }
                break;
            default:
                if (is_numeric($AVG_FIRST_SEMSTER) && is_numeric($AVG_SECOND_SEMSTER))
                {
                    $result = setRound(($AVG_FIRST_SEMSTER + $AVG_SECOND_SEMSTER) / ($firstCoeff + $secondCoeff));
                }
                elseif (is_numeric($AVG_FIRST_SEMSTER) && !is_numeric($AVG_SECOND_SEMSTER))
                {
                    $result = setRound(($AVG_FIRST_SEMSTER) / ($firstCoeff));
                }
                elseif (!is_numeric($AVG_FIRST_SEMSTER) && is_numeric($AVG_SECOND_SEMSTER))
                {
                    $result = setRound(($AVG_SECOND_SEMSTER) / ($secondCoeff));
                }
                break;
        }

        return $result;
    }

    protected function scoreListSubjectByMonth()
    {

        $data = Array();
        $entries = $this->listStudentsByClass();

//        parent::$this->month = $this->month;
//        parent::$this->year = $this->year;
//        parent::$this->subjectId = $this->subjectId;

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $this->studentAvgAllAssignmentsBySubject(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , false
                        , false
                );
            }
        }
        return $data;
    }

    protected function scoreListSubjectBySemester()
    {

        $data = Array();

//        parent::$this->term = $this->term;
//        parent::$this->subjectId = $this->subjectId;

        $entries = $this->listStudentsByClass();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $this->studentTotalAvgSemesterSubject(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , $this->term
                );
            }
        }
        return $data;
    }

    protected function scoreListSubjectByYear()
    {

        $data = Array();
        $entries = $this->listStudentsByClass();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $this->studentAvgSubjectByYear($value->STUDENT_ID, $this->subjectId);
            }
        }
        return $data;
    }

    protected function implodeAssignmentMonthBySemester($studentId, $subjectId, $term)
    {

        $entries = $this->getSQLStudentAssignment($studentId, $subjectId, $term, false, 1);
        $data = Array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $value->POINTS;
            }
        }
        return $data ? implode('|', $data) : "---";
    }

    protected function implodeAssignmentSemesterBySemester($studentId, $subjectId, $term)
    {

        $entries = $this->getSQLStudentAssignment($studentId, $subjectId, $term, false, 2);
        $data = Array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $value->POINTS;
            }
        }
        return $data ? implode('|', $data) : "---";
    }

    public function getStudentSubjectYearSemesterAssessment($studentId, $subjectId, $term)
    {

        $SELECTION_A = Array('SUBJECT_VALUE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("A.CLASS_ID = '" . $this->classId . "'");
        $SQL->where("A.SECTION = 'SEMESTER'");
        $SQL->where("A.TERM = '" . $term . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public function getStudentSubjectAssessment($studentId, $subjectId, $actionType = false)
    {

        $SELECTION_A = Array('SUBJECT_VALUE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("A.CLASS_ID = '" . $this->classId . "'");

        switch ($this->section)
        {
            case 1:
                $SQL->where("A.MONTH = '" . $this->month . "'");
                $SQL->where("A.YEAR = '" . $this->year . "'");
                $SQL->where("A.SECTION = 'MONTH'");
                break;
            case 2:
                $SQL->where("A.TERM = '" . $this->term . "'");
                $SQL->where("A.SECTION = 'SEMESTER'");
                break;
            case 3:
                $SQL->where("A.SECTION = 'YEAR'");

                break;
        }

        if (!$actionType)
        {
            $SQL->where("A.ACTION_TYPE = 'ASSESSMENT'");
        }
        else
        {
            $SQL->where("A.ACTION_TYPE = '" . $actionType . "'");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function setStudentMonthSubjectAssessment($studentId, $subjectId, $comment, $assessmentId, $scoreList)
    {

        $facette = $this->getStudentSubjectAssessment($studentId, $subjectId);
        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);

        $AVERAGE = $this->studentAvgMonthSubject($studentId, $subjectId);
        $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);

        if ($facette)
        {

            switch ($subjectObject->SCORE_TYPE)
            {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE))
                    {
                        $UPDATE_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId)
                    {
                        $UPDATE_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId)
            {
                $UPDATE_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $UPDATE_DATA["RANK"] = $RANK;
            if ($comment)
                $UPDATE_DATA["TEACHER_COMMENT"] = addText($comment);

            $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $this->classId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $subjectId . "'";
            $WHERE[] = "SECTION = 'MONTH'";
            $WHERE[] = "ACTION_TYPE = 'ASSESSMENT'";
            $WHERE[] = "MONTH = '" . $this->month . "'";
            $WHERE[] = "YEAR = '" . $this->year . "'";
            self::dbAccess()->update('t_student_subject_assessment', $UPDATE_DATA, $WHERE);
        } else
        {
            $INSERT_DATA["STUDENT_ID"] = $studentId;
            $INSERT_DATA["SUBJECT_ID"] = $subjectId;
            $INSERT_DATA["CLASS_ID"] = $this->classId;

            switch ($subjectObject->SCORE_TYPE)
            {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE))
                    {
                        $INSERT_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId)
                    {
                        $INSERT_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId)
            {
                $INSERT_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $INSERT_DATA["RANK"] = $RANK;
            if ($comment)
                $INSERT_DATA["TEACHER_COMMENT"] = addText($comment);

            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
            $INSERT_DATA["SECTION"] = "MONTH";
            $INSERT_DATA["MONTH"] = $this->month;
            $INSERT_DATA["YEAR"] = $this->year;

            if ($this->classObject)
            {
                $INSERT_DATA["SCHOOLYEAR_ID"] = $this->classObject->SCHOOL_YEAR;
                $INSERT_DATA["EDUCATION_SYSTEM"] = $this->classObject->EDUCATION_SYSTEM;
            }

            self::dbAccess()->insert("t_student_subject_assessment", $INSERT_DATA);
        }
    }

    protected function setStudentSemesterSubjectAssessment($studentId, $subjectId, $comment, $assessmentId, $scoreList)
    {

        $facette = $this->getStudentSubjectAssessment($studentId, $subjectId);
        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);

        $AVERAGE = $this->studentTotalAvgSemesterSubject(
                $studentId
                , $subjectId
                , $this->term);

        $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);

        if ($facette)
        {

            switch ($subjectObject->SCORE_TYPE)
            {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE))
                    {
                        $UPDATE_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId)
                    {
                        $UPDATE_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId)
            {
                $UPDATE_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $UPDATE_DATA["RANK"] = $RANK;
            if ($comment)
                $UPDATE_DATA["TEACHER_COMMENT"] = addText($comment);
            $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $this->classId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $subjectId . "'";
            $WHERE[] = "SECTION = 'SEMESTER'";
            $WHERE[] = "ACTION_TYPE = 'ASSESSMENT'";
            $WHERE[] = "TERM = '" . $this->term . "'";
            self::dbAccess()->update('t_student_subject_assessment', $UPDATE_DATA, $WHERE);
        } else
        {

            $INSERT_DATA["STUDENT_ID"] = $studentId;
            $INSERT_DATA["SUBJECT_ID"] = $subjectId;
            $INSERT_DATA["CLASS_ID"] = $this->classId;

            switch ($subjectObject->SCORE_TYPE)
            {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE))
                    {
                        $INSERT_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId)
                    {
                        $INSERT_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId)
            {
                $INSERT_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $INSERT_DATA["RANK"] = $RANK;
            if ($comment)
                $INSERT_DATA["TEACHER_COMMENT"] = addText($comment);

            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
            $INSERT_DATA["SECTION"] = "SEMESTER";
            $INSERT_DATA["TERM"] = $this->term;
            if ($this->classObject)
            {
                $INSERT_DATA["SCHOOLYEAR_ID"] = $this->classObject->SCHOOL_YEAR;
                $INSERT_DATA["EDUCATION_SYSTEM"] = $this->classObject->EDUCATION_SYSTEM;
            }

            self::dbAccess()->insert("t_student_subject_assessment", $INSERT_DATA);
        }
    }

    protected function setStudentYearSubjectAssessment($studentId, $subjectId, $comment, $assessmentId, $scoreList)
    {

        $facette = $this->getStudentSubjectAssessment($studentId, $subjectId);
        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);

        $AVERAGE = $this->studentAvgSubjectByYear(
                $studentId
                , $subjectId
        );

        $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);

        if ($facette)
        {

            switch ($subjectObject->SCORE_TYPE)
            {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE))
                    {
                        $UPDATE_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId)
                    {
                        $UPDATE_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId)
            {
                $UPDATE_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $UPDATE_DATA["RANK"] = $RANK;
            if ($comment)
                $UPDATE_DATA["TEACHER_COMMENT"] = addText($comment);

            $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $this->classId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $subjectId . "'";
            $WHERE[] = "SECTION = 'YEAR'";
            $WHERE[] = "ACTION_TYPE = 'ASSESSMENT'";
            self::dbAccess()->update('t_student_subject_assessment', $UPDATE_DATA, $WHERE);
        } else
        {

            $INSERT_DATA["STUDENT_ID"] = $studentId;
            $INSERT_DATA["SUBJECT_ID"] = $subjectId;
            $INSERT_DATA["CLASS_ID"] = $this->classId;

            switch ($subjectObject->SCORE_TYPE)
            {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE))
                    {
                        $INSERT_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId)
                    {
                        $INSERT_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId)
            {
                $INSERT_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $INSERT_DATA["RANK"] = $RANK;
            if ($comment)
                $INSERT_DATA["TEACHER_COMMENT"] = addText($comment);

            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
            $INSERT_DATA["SECTION"] = "YEAR";
            if ($this->classObject)
            {
                $INSERT_DATA["SCHOOLYEAR_ID"] = $this->classObject->SCHOOL_YEAR;
                $INSERT_DATA["EDUCATION_SYSTEM"] = $this->classObject->EDUCATION_SYSTEM;
            }
            self::dbAccess()->insert("t_student_subject_assessment", $INSERT_DATA);
        }
    }

    public function jsonSetSubjectAssessment($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";
        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        parent::$this->term = $this->term;
        parent::$this->monthyear = $this->monthyear;
        parent::$this->classObject = $this->classObject;
        parent::$this->classId = $this->classId;
        parent::$this->subjectId = $this->subjectId;

        if ($this->monthyear)
        {

            parent::$this->month = getMonthNumberFromMonthYear($this->monthyear);
            parent::$this->year = getYearFromMonthYear($this->monthyear);
        }

        switch ($this->section)
        {
            case 1:
                $this->scoreList = $this->scoreListSubjectByMonth();
                break;
            case 2:
                $this->scoreList = $this->scoreListSubjectBySemester();
                break;
            case 3:
                $this->scoreList = $this->scoreListSubjectByYear();
                break;
        }

        if ($this->listStudentsByClass())
        {
            foreach ($this->listStudentsByClass() as $studentObject)
            {

                switch ($this->section)
                {
                    case 1:
                        if ($this->getCountScoreEnterByStudent($studentObject->STUDENT_ID, $this->subjectId, false, false))
                            $this->setStudentMonthSubjectAssessment(
                                    $studentObject->STUDENT_ID
                                    , $this->subjectId
                                    , false
                                    , false
                                    , $this->scoreList);
                        break;
                    case 2:
                        if ($this->getCountScoreEnterByStudent($studentObject->STUDENT_ID, $this->subjectId, $this->term, false))
                            $this->setStudentSemesterSubjectAssessment(
                                    $studentObject->STUDENT_ID
                                    , $this->subjectId
                                    , false
                                    , false
                                    , $this->scoreList);
                        break;
                    case 3:
                        if ($this->getCountScoreEnterByStudent($studentObject->STUDENT_ID, $this->subjectId, false, false))
                            $this->setStudentYearSubjectAssessment(
                                    $studentObject->STUDENT_ID
                                    , $this->subjectId
                                    , false
                                    , false
                                    , $this->scoreList);
                        break;
                }
            }
        }
        return array(
            "success" => true
        );
    }

}

?>