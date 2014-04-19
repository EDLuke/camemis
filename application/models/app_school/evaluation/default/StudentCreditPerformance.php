<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.10.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentCreditPerformance extends StudentSubjectAssessment {

    private static $instance = null;
    //
    public $data = array();

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getStudentSubjectPerformance($section = false) {

        $SELECTION_A = Array('SUBJECT_VALUE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION AS ASSESSMENT', 'LETTER_GRADE');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = '" . $this->studentId . "'");
        $SQL->where("A.CLASS_ID = '" . $this->creditClassId . "'");
        $SQL->where("A.EDUCATION_SYSTEM = 1");

        if ($section) {
            $SQL->where("A.TERM = '" . $section . "'");
            $SQL->where("A.SECTION = 'SEMESTER'");
        } else {
            switch ($this->section) {
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
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //Use...
    public function jsonLoadStudentCreditAllSubjectAssessment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? $params["start"] : 0;
        $limit = isset($params["limit"]) ? $params["limit"] : 50;

        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $this->studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";

        if ($this->monthyear) {
            $this->month = getMonthNumberFromMonthYear($this->monthyear);
            $this->year = getYearFromMonthYear($this->monthyear);
        }

        $listOfSubjects = $this->getListSubjectsStudentCredit();

        $data = Array();

        if ($listOfSubjects) {
            if ($listOfSubjects) {
                $i = 0;
                foreach ($listOfSubjects as $value) {

                    $this->subjectId = $value->SUBJECT_ID;
                    $this->creditClassId = $value->GROUP_ID;
                    $subjectPerformance = $this->getStudentSubjectPerformance();

                    $data[$i]["SHORT"] = $value->SUBJECT_SHORT;
                    $data[$i]["NAME"] = $value->SUBJECT_NAME;

                    $data[$i]["SUBJECT_VALUE"] = $subjectPerformance ? $subjectPerformance->SUBJECT_VALUE : "---";
                    $data[$i]["ASSESSMENT"] = $subjectPerformance ? $subjectPerformance->ASSESSMENT : "---";
                    $data[$i]["RANK"] = $subjectPerformance ? $subjectPerformance->RANK : "---";


                    $i++;
                }
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    protected function getListSubjectsStudentCredit() {

        $SELECTION_A = Array(
            'CLASS_ID AS GROUP_ID'
        );

        $SELECTION_B = Array(
            'ID AS SUBJECT_ID'
            , 'SHORT AS SUBJECT_SHORT'
            , 'NAME AS SUBJECT_NAME'
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_schoolyear_subject"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = '" . $this->studentId . "'");
        $SQL->where("A.SCHOOLYEAR_ID = '" . $this->schoolyearId . "'");
        //error_log($this->schoolyearId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

}

?>