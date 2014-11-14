<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationGradebook.php';
require_once 'models/assessment/SQLEvaluationStudentAssignment.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/SpecialDBAccess.php";

class EvaluationGradebook extends AssessmentProperties {

    CONST SCORE_TYPE_NUMBER = 1;
    CONST SCORE_TYPE_CHAR = 2;

    function __construct() {
        parent::__construct();
    }

    protected function listSubjectsData() {

        $data = array();

        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {

                if ($value->SUBJECT_ID) {
                    $subjectId = $value->SUBJECT_ID;
                    $data[$i]["SUBJECT_SHORT"] = $value->SUBJECT_SHORT;
                    $data[$i]["SUBJECT_NAME"] = $value->SUBJECT_NAME;

                    $i++;
                }
            }
        }

        return $data;
    }

    public function getStudentGradebookMonth() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "studentId" => $this->studentId
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
                    , "include_in_evaluation" => 1
        );

        $data = $this->listSubjectsData();


        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {
                if ($value->SUBJECT_ID) {
                    $this->subjectId = $value->SUBJECT_ID;
                    $stdClass->subjectId = $value->SUBJECT_ID;
                    $data[$i]["ASSIGNMENT"] = $this->getImplodeStudentAssignments($stdClass);
                    $data[$i]["SUBJECT_VALUE"] = $this->getSubjectValue($stdClass);
                    $data[$i]["RANK"] = $this->getSubjectRank($stdClass);
                    $data[$i]["ASSESSMENT"] = $this->getSubjectAssessment($stdClass);

                    $i++;
                }
            }
        }

        return $data;
    }

    public function getStudentGradebookTerm() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "studentId" => $this->studentId
                    , "term" => $this->term
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        $data = $this->listSubjectsData();

        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {
                if ($value->SUBJECT_ID) {
                    $this->subjectId = $value->SUBJECT_ID;
                    $stdClass->subjectId = $value->SUBJECT_ID;
                    $data[$i]["SUBJECT_VALUE"] = $this->getSubjectValue($stdClass);
                    $data[$i]["RANK"] = $this->getSubjectRank($stdClass);
                    $data[$i]["ASSESSMENT"] = $this->getSubjectAssessment($stdClass);

                    $i++;
                }
            }
        }

        return $data;
    }

    public function getStudentGradebookYear() {

        $stdClass = (object) array(
                    "academicId" => $this->academicId
                    , "studentId" => $this->studentId
                    , "section" => $this->getSection()
                    , "schoolyearId" => $this->getSchoolyearId()
        );

        $data = $this->listSubjectsData();

        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {
                if ($value->SUBJECT_ID) {
                    $this->subjectId = $value->SUBJECT_ID;
                    $stdClass->subjectId = $value->SUBJECT_ID;
                    $data[$i]["SUBJECT_VALUE"] = $this->getSubjectValue($stdClass);
                    $data[$i]["RANK"] = $this->getSubjectRank($stdClass);
                    $data[$i]["ASSESSMENT"] = $this->getSubjectAssessment($stdClass);

                    $i++;
                }
            }
        }

        return $data;
    }

    public function getImplodeStudentAssignments($stdClass) {
        $entries = SQLEvaluationStudentAssignment::getQueryStudentSubjectAssignments($stdClass);

        $data = array();

        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $value->POINTS;
            }
        }

        return $data ? implode("|", $data) : "---";
    }

    public function getSubjectValue($stdClass) {

        $facette = SQLEvaluationStudentSubject::getPropertiesStudentSE($stdClass);
        return $facette->SUBJECT_VALUE;
    }

    public function getSubjectRank($stdClass) {
        $facette = SQLEvaluationStudentSubject::getPropertiesStudentSE($stdClass);
        return $facette->RANK;
    }

    public function getSubjectAssessment($stdClass) {
        $facette = SQLEvaluationStudentSubject::getPropertiesStudentSE($stdClass);

        switch ($this->getSubjectScoreType()) {
            case self::SCORE_TYPE_NUMBER:
                return $facette->GRADING ? $facette->GRADING : "---";
            case self::SCORE_TYPE_CHAR:
                return $facette->GRADING ? $facette->GRADING : "---";
        }
    }

}

?>