<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.02.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffDBAccess.php";
require_once setUserLoacalization();

class StudentAssessmentDBAccess {

    public function __construct($studentId, $classId, $subjectId, $term, $assignmentId)
    {
        $this->studentId = $studentId;
        $this->classId = $classId;
        $this->subjectId = $subjectId;
        $this->term = $term;
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->datafield))
        {
            return $this->datafield[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->datafield[$name] = $value;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->datafield);
    }

    public function __unset($name)
    {
        unset($this->datafield[$name]);
    }

    public function getSubject()
    {
        return SubjectDBAccess::getAcademicSubject($this->subjectId, $this->classId);
    }

    public function getSubjectScoreType()
    {
        return $this->getSubject()->SCORE_TYPE;
    }

    public function getSubjectCoeff()
    {
        return $this->getSubject()->COEFF_VALUE;
    }

    public function getSubjectMaxScore()
    {
        return $this->getSubject()->SCORE_MAX;
    }

    public function getAssignment()
    {
        return AssignmentDBAccess::findAssignmentFromId($this->assignmentId);
    }

    public function getAssignmentCoeff()
    {
        return $this->getAssignment()->COEFF_VALUE;
    }

    public function getAssignmentEvaluationType()
    {
        return $this->getAssignment()->EVALUATION_TYPE;
    }

    public function getAssignmentInCludeEvaluation()
    {
        return $this->getAssignment()->INCLUDE_IN_EVALUATION;
    }

    public function getAcademic()
    {
        return AcademicDBAccess::findGradeFromId($this->classId);
    }

    public function getSchoolyearId()
    {
        return $this->getAcademic()->SCHOOL_YEAR;
    }

    public function getScoreActionMonth()
    {
        return getMonthYearByDateStr($this->date)->MONTH;
    }

    public function getScoreActionYear()
    {
        return getMonthYearByDateStr($this->date)->YEAR;
    }

    public function getScoreEnter($score)
    {

        $this->score = $score;
    }

    public function getClassId($classId)
    {
        $this->classId = $classId;
    }

    public function setSchoolyearId($schoolyearId)
    {
        $this->schoolyearId = $schoolyearId;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setSubjectId($subjectId)
    {
        $this->subjectId = $subjectId;
    }

    public function actionTeacherScoreEnter()
    {
        $this->classId;
    }

}

?>