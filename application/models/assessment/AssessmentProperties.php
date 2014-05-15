<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

abstract class AssessmentProperties {

    public $datafield = array();

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function __get($name) {
        if (array_key_exists($name, $this->datafield)) {
            return $this->datafield[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->datafield[$name] = $value;
    }

    public function __isset($name) {
        return array_key_exists($name, $this->datafield);
    }

    public function __unset($name) {
        unset($this->datafield[$name]);
    }

    public function listClassStudents() {
        $studentsearch = new StudentSearchDBAccess();
        $studentsearch->globalSearch = $this->globalSearch;

        switch ($this->getEducationSystem()) {
            case 1:
                $studentsearch->creditSubjectId = $this->subjectId;
                $studentsearch->creditSchoolyearId = $this->getSchoolyearId();
                break;
            default:
                $studentsearch->classId = $this->academicId;
                $studentsearch->schoolyearId = $this->getSchoolyearId();
                break;
        }
        return $studentsearch->queryAllStudents();
    }

    public function getSubject() {
        return SubjectDBAccess::getAcademicSubject($this->subjectId, $this->academicId);
    }

    public function getSubjectScoreType() {
        return $this->getSubject()->SCORE_TYPE ? $this->getSubject()->SCORE_TYPE : 1;
    }

    public function getSubjectCoeff() {
        return $this->getSubject()->COEFF_VALUE ? $this->getSubject()->COEFF_VALUE : 1;
    }

    public function getSubjectScoreMax() {
        return $this->getSubject()->SCORE_MAX;
    }

    public function getAssignment() {
        return AssignmentDBAccess::findAssignmentFromId($this->assignmentId);
    }

    public function getAssignmentCoeff() {
        return $this->getAssignment()->COEFF_VALUE ? $this->getAssignment()->COEFF_VALUE : 1;
    }

    public function getAssignmentEvaluationType() {
        return $this->getAssignment()->EVALUATION_TYPE;
    }

    public function getAssignmentInCludeEvaluation() {
        return $this->getAssignment()->INCLUDE_IN_EVALUATION;
    }

    public function getCurrentClassAssignments() {
        return AssignmentDBAccess::getListAssignmentsToAcademic(
                        $this->academicId
                        , $this->subjectId
        );
    }

    public function getCurrentClass() {
        return AcademicDBAccess::findGradeFromId($this->academicId);
    }

    public function getTermNumber() {
        return AcademicDBAccess::findAcademicTerm($this->getSchoolyearId());
    }

    public function isDisplayMonthResult() {
        return $this->getCurrentClass()->DISPLAY_MONTH_RESULT;
    }

    public function isDisplayFirstResult() {
        return $this->getCurrentClass()->DISPLAY_FIRST_RESULT;
    }

    public function isDisplaySecondResult() {
        return $this->getCurrentClass()->DISPLAY_SECOND_RESULT;
    }

    public function isDisplayThirdResult() {
        return $this->getCurrentClass()->DISPLAY_THIRD_RESULT;
    }

    public function isDisplayFourthResult() {
        return $this->getCurrentClass()->DISPLAY_FOURTH_RESULT;
    }

    public function isDisplayYearResult() {
        return $this->getCurrentClass()->DISPLAY_YEAR_RESULT;
    }

    public function getEducationSystem() {
        return $this->getCurrentClass()->EDUCATION_SYSTEM;
    }

    public function getSchoolyearId() {
        return $this->getCurrentClass()->SCHOOL_YEAR;
    }

    public function getMonth() {
        if ($this->date) {
            return getMonthYearByDateStr($this->date)->MONTH * 1;
        }

        if ($this->monthyear) {
            return getMonthNumberFromMonthYear($this->monthyear) * 1;
        }
    }

    public function getYear() {
        if ($this->date) {
            return getMonthYearByDateStr($this->date)->YEAR;
        }

        if ($this->monthyear) {
            return getYearFromMonthYear($this->monthyear);
        }
    }

    public function getSection() {

        switch ($this->section) {
            case 1:
                return "MONTH";
            case 2:
                return "SEMESTER";
            case 3:
                return "YEAR";
            case 4:
                return "TERM";
            case 5:
                return "QUARTER";
        }
    }

    public function getListSubjects() {
        return GradeSubjectDBAccess::getListSubjectsToAcademic($this->academicId, $this->term);
    }

    public function getFirstSemesterCoeff() {
        return $this->getCurrentClass()->SEMESTER1_WEIGHTING ? $this->getCurrentClass()->SEMESTER1_WEIGHTING : 1;
    }

    public function getSecondSemesterCoeff() {
        return $this->getCurrentClass()->SEMESTER2_WEIGHTING ? $this->getCurrentClass()->SEMESTER2_WEIGHTING : 1;
    }

    public function getFirstTermCoeff() {
        return $this->getCurrentClass()->TERM1_WEIGHTING ? $this->getCurrentClass()->TERM1_WEIGHTING : 1;
    }

    public function getSecondTermCoeff() {
        return $this->getCurrentClass()->TERM2_WEIGHTING ? $this->getCurrentClass()->TERM2_WEIGHTING : 1;
    }

    public function getThirdTermCoeff() {
        return $this->getCurrentClass()->TERM3_WEIGHTING ? $this->getCurrentClass()->TERM3_WEIGHTING : 1;
    }

    public function getFirstQuarterCoeff() {
        return $this->getCurrentClass()->QUARTER1_WEIGHTING ? $this->getCurrentClass()->QUARTER1_WEIGHTING : 1;
    }

    public function getSecondQuarterCoeff() {
        return $this->getCurrentClass()->QUARTER2_WEIGHTING ? $this->getCurrentClass()->QUARTER2_WEIGHTING : 1;
    }

    public function getThirdQuarterCoeff() {
        return $this->getCurrentClass()->QUARTER3_WEIGHTING ? $this->getCurrentClass()->QUARTER3_WEIGHTING : 1;
    }

    public function getFourthQuarterCoeff() {
        return $this->getCurrentClass()->QUARTER4_WEIGHTING ? $this->getCurrentClass()->QUARTER4_WEIGHTING : 1;
    }

    public function getSettingYearResult() {
        return $this->getCurrentClass()->YEAR_RESULT;
    }

    public function getSettingYearTermResult() {
        return $this->getSubject()->AVERAGE_FROM_SEMESTER;
    }

    public function getSettingEvaluationType() {
        return $this->getCurrentClass()->EVALUATION_TYPE;
    }

    public function getSettingEvaluationOption() {
        return $this->getCurrentClass()->EVALUATION_OPTION;
    }

    public function displayRank($average, $scoreList) {
        return is_int($average) ? getScoreRank($scoreList, $average) : "---";
    }

    public function getSettingQualificationType() {
        return AcademicDBAccess::findGradeFromId($this->getCurrentClass()->CAMPUS_ID)->QUALIFICATION_TYPE;
    }

}

?>