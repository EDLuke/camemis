<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationGradebook.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/SpecialDBAccess.php";

class EvaluationGradebook extends AssessmentProperties {

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

        $data = $this->listSubjectsData();

        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {
                if ($value->SUBJECT_ID) {
                    $subjectId = $value->SUBJECT_ID;
                    $data[$i]["ASSIGNMENT"] = "---";
                    $i++;
                }
            }
        }

        return $data;
    }

    public function getStudentGradebookTerm() {

        $data = $this->listSubjectsData();

        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {
                if ($value->SUBJECT_ID) {
                    $subjectId = $value->SUBJECT_ID;
                    $data[$i]["ASSIGNMENT"] = "---";
                    $i++;
                }
            }
        }

        return $data;
    }

    public function getStudentGradebookYear() {

        $data = $this->listSubjectsData();

        if ($this->getListSubjects()) {
            $i = 0;
            foreach ($this->getListSubjects() as $value) {
                if ($value->SUBJECT_ID) {
                    $subjectId = $value->SUBJECT_ID;
                    $data[$i]["ASSIGNMENT"] = "---";
                    $i++;
                }
            }
        }

        return $data;
    }

}

?>