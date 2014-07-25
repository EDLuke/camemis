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
        if ($this->getCurrentClass()->EDUCATION_SYSTEM) {
            return SubjectDBAccess::getAcademicSubject($this->subjectId, $this->getCurrentClass()->PARENT);
        } else {
            return SubjectDBAccess::getAcademicSubject($this->subjectId, $this->academicId);
        }
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

    public function getSubjectScoreMin() {
        return $this->getSubject()->SCORE_MIN;
    }

    public function getSubjectCreditHours() {
        return $this->getSubject()->NUMBER_CREDIT;
    }

    public function getSubjectScorePossible() {
        return $this->getSubject()->MAX_POSSIBLE_SCORE;
    }

    public function getAssignment() {
        return AssignmentDBAccess::findAssignmentFromId($this->assignmentId);
    }

    public function getAssignmentPointsPossible() {
        return $this->getAssignment()->POINTS_POSSIBLE ? $this->getAssignment()->POINTS_POSSIBLE : 100;
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

        if ($this->getCurrentClass()->EDUCATION_SYSTEM) {
            return AssignmentDBAccess::getListAssignmentsToAcademic(
                $this->getCurrentClass()->PARENT
                , $this->subjectId
            );
        } else {
            return AssignmentDBAccess::getListAssignmentsToAcademic(
                $this->academicId
                , $this->subjectId
            );
        }
    }

    public function getCurrentClass() {
        return AcademicDBAccess::findGradeFromId($this->academicId);
    }

    public function getCurrentSchoolyear() {
        return $result = AcademicDateDBAccess::findAcademicDateFromId($this->getSchoolyearId());
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

    public function getNameSectionByTerm() {

        $section = "";

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

        return $section;
    }

    public function getListSubjects() {

        if ($this->getCurrentClass()->EDUCATION_SYSTEM) {
            return GradeSubjectDBAccess::getListSubjectsFromAcademic($this->getCurrentClass()->PARENT, $this->term);
        } else {
            return GradeSubjectDBAccess::getListSubjectsFromAcademic($this->academicId, $this->term);
        }
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

    public function getSettingFormulaTermResult() {
        return $this->getCurrentClass()->FORMULA_TERM;
    }

    public function getSettingFormulaYearResult() {
        return $this->getCurrentClass()->FORMULA_TERM;
    }

    public function displayRank($average, $scoreList) {
        return getScoreRank($scoreList, $average);
    }

    public function getSettingQualificationType() {
        return AcademicDBAccess::findGradeFromId($this->getCurrentClass()->CAMPUS_ID)->QUALIFICATION_TYPE;
    }

    public function getTermByDateAcademicId() {
        return AcademicDBAccess::getNameOfSchoolTermByDate($this->date, $this->getCurrentClass()->ID);
    }

    public function getTermByMonthYear() {

        if ($this->monthyear) {
            return AcademicDBAccess::getTermByMonthYear($this->academicId, $this->monthyear);
        } elseif ($this->term) {
            return $this->term;
        }
    }

    public function getSettingFirstDivision() {
        return $this->getCurrentClass()->PERFORMANCE_FIRST_DIVISION_VALUE;
    }

    public function getSettingSecondDivision() {
        return $this->getCurrentClass()->PERFORMANCE_SECOND_DIVISION_VALUE;
    }

    public function getSettingThirdDivision() {
        return $this->getCurrentClass()->PERFORMANCE_THIRD_DIVISION_VALUE;
    }

    public function getSettingFourthDivision() {
        return $this->getCurrentClass()->PERFORMANCE_FOURTH_DIVISION_VALUE;
    }

    public function getAcademicTerm() {

        return AcademicDBAccess::getNameOfSchoolTermByDate($this->date, $this->academicId);
    }

    public static function displayAssignmentResult($type, $object) {

        $firstValue = $object ? $object->POINTS : "---";
        $secondValue = "---";
        if ($object) {
            if (isset($object->POINTS_POSSIBLE)) {
                $secondValue = $object ? $object->POINTS_POSSIBLE : "---";
            }
        }

        switch ($type) {
            case 0:
                return $firstValue;
            case 1:
                if (is_numeric($firstValue) && is_numeric($secondValue)) {
                    return $firstValue ? $firstValue . "/" . $secondValue : "";
                } else {
                    return "---";
                }
        }
    }

    public static function setGradingScale($academicObject) {

        $EDUCATION_TYPE = AcademicDBAccess::findGradeFromId($academicObject->CAMPUS_ID)->QUALIFICATION_TYPE;

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("EDUCATION_TYPE = '" . $EDUCATION_TYPE . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                if (strpos(trim($value->MARK), "<") !== false) {
                    $UPDATE = "UPDATE t_gradingsystem SET SCORE_MIN=0, SCORE_MAX='" . substr(trim($value->MARK), 1) . "' WHERE ID='" . $value->ID . "'";
                    self::dbAccess()->query($UPDATE);
                } else {
                    $explode = explode("-", trim($value->MARK));
                    $MIN = isset($explode[0]) ? $explode[0] : 0;
                    $MAX = isset($explode[1]) ? $explode[1] : 0;
                    $UPDATE = "UPDATE t_gradingsystem SET SCORE_MIN='" . $MIN . "', SCORE_MAX='" . $MAX . "' WHERE ID='" . $value->ID . "'";
                    self::dbAccess()->query($UPDATE);
                }
            }
        }
    }

}

?>