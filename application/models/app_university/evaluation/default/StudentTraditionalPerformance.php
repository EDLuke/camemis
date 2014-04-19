<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.10.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/evaluation/default/StudentSubjectAssessment.php';
require_once setUserLoacalization();

class StudentTraditionalPerformance extends StudentSubjectAssessment {

    private static $instance = null;
    //
    public $data = array();

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function jsonListStudentsMonthClassPerformance($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;
        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";

        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->classObject = $this->getClassObject();

        $this->month = getMonthNumberFromMonthYear($this->monthyear);
        $this->year = getYearFromMonthYear($this->monthyear);

//        parent::$this->month = $this->month;
//        parent::$this->year = $this->year;
//        parent::$this->academicId = $this->academicId;
//        parent::$this->section = $this->section;

        return $this->traditionalMonthResultClassPerformance();
    }

    public function jsonListStudentsSemesterClassPerformance($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";
        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;
        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $this->classObject = $this->getClassObject();

//        parent::$this->term = $this->term;
//        parent::$this->academicId = $this->academicId;
//        parent::$this->section = $this->section;

        return $this->traditionalSemesterResultClassPerformance();
    }

    public function jsonListStudentsYearClassPerformance($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->section = isset($params["section"]) ? $params["section"] : 0;
        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;
        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $this->classObject = $this->getClassObject();

//        parent::$this->academicId = $this->academicId;
//        parent::$this->section = $this->section;

        return $this->traditionalYearResultClassPerformance();
    }

    public function traditionalMonthResultClassPerformance() {

        $data = array();

        $entries = $this->listStudentsByClass();
        $scoreList = $this->scoreListClassPerformance();

        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $studentAssessment = $this->getStudentPerformance();
                if ($studentAssessment) {
                    if (isset($studentAssessment->DESCRIPTION)) {
                        $data[$i]["ASSESSMENT_TOTAL"] = $studentAssessment->DESCRIPTION;
                    }
                }

                $AVERAGE_TOTAL = $this->getAvgClassPerformance(
                        $value->STUDENT_ID
                        , false);

                $data[$i]["AVERAGE_TOTAL"] = $AVERAGE_TOTAL;
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE_TOTAL);

                if ($this->listSubjects()) {
                    foreach ($this->listSubjects() as $subjectObject) {
                        $SUBJECT_ASSESSMENT = $this->getStudentSubjectAssessment(
                                $value->STUDENT_ID
                                , $subjectObject->SUBJECT_ID
                                , false);
                        $data[$i][$subjectObject->SUBJECT_ID] = $SUBJECT_ASSESSMENT ? $SUBJECT_ASSESSMENT->SUBJECT_VALUE : "---";
                    }
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function traditionalSemesterResultClassPerformance() {

        $data = array();

        $entries = $this->listStudentsByClass();
        $scoreList = $this->scoreListClassPerformance();

        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $studentAssessment = $this->getStudentPerformance();
                if ($studentAssessment) {
                    if (isset($studentAssessment->DESCRIPTION)) {
                        $data[$i]["ASSESSMENT_TOTAL"] = $studentAssessment->DESCRIPTION;
                    }
                }

                $AVERAGE_TOTAL = $this->getAvgClassPerformance(
                        $value->STUDENT_ID
                        , false);

                $data[$i]["AVERAGE_TOTAL"] = $AVERAGE_TOTAL;
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE_TOTAL);

                if ($this->listSubjects()) {
                    foreach ($this->listSubjects() as $subjectObject) {
                        $SUBJECT_ASSESSMENT = $this->getStudentSubjectAssessment(
                                $value->STUDENT_ID
                                , $subjectObject->SUBJECT_ID
                                , false);
                        $data[$i][$subjectObject->SUBJECT_ID] = $SUBJECT_ASSESSMENT ? $SUBJECT_ASSESSMENT->SUBJECT_VALUE : "---";
                    }
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function traditionalYearResultClassPerformance() {
        $data = array();

        $entries = $this->listStudentsByClass();
        $scoreList = $this->scoreListYearClassPerformance();

        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = $value->STUDENT_CODE;

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $studentAssessment = $this->getStudentPerformance();
                if ($studentAssessment) {
                    if (isset($studentAssessment->DESCRIPTION)) {
                        $data[$i]["ASSESSMENT_YEAR"] = $studentAssessment->DESCRIPTION;
                    }
                }

                $AVERAGE_TOTAL = $this->getAvgYearClassPerformance($value->STUDENT_ID);

                $data[$i]["AVERAGE_TOTAL"] = $AVERAGE_TOTAL;
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE_TOTAL);

                $data[$i]["FIRST_SEMESTER_RESULT"] = $this->getAvgClassPerformance(
                        $value->STUDENT_ID
                        , "FIRST_SEMESTER");

                $data[$i]["SECOND_SEMESTER_RESULT"] = $this->getAvgClassPerformance(
                        $value->STUDENT_ID
                        , "SECOND_SEMESTER");

                $FS_ASSESSMENT = $this->getStudentPerformance("FIRST_SEMESTER");
                if ($FS_ASSESSMENT) {
                    if (isset($FS_ASSESSMENT->DESCRIPTION)) {
                        $data[$i]["ASSESSMENT_FIRST_SEMESTER"] = $FS_ASSESSMENT->DESCRIPTION;
                    }
                }

                $SS_ASSESSMENT = $this->getStudentPerformance("SECOND_SEMESTER");
                if ($FS_ASSESSMENT) {
                    if (isset($SS_ASSESSMENT->DESCRIPTION)) {
                        $data[$i]["ASSESSMENT_SECOND_SEMESTER"] = $SS_ASSESSMENT->DESCRIPTION;
                    }
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    protected function getSQLClassPerformance($studentId, $term = false) {
        $SELECTION_A = Array(
            'SUBJECT_VALUE'
            , 'SUBJECT_ID'
            , 'RANK'
        );

        $SELECTION_B = Array(
            'INCLUDE_IN_EVALUATION'
            , 'SCORE_TYPE'
            , 'COEFF_VALUE'
            , 'FORMULA_TYPE'
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_subject_assessment"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_grade_subject'), 'A.SUBJECT_ID=B.SUBJECT', $SELECTION_B);

        if ($studentId) {
            $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        }

        if ($this->academicId) {
            $SQL->where("A.CLASS_ID = '" . $this->academicId . "'");
        }

        if ($term) {
            $SQL->where("TERM = '" . strtoupper($term) . "'");
            $SQL->where("SECTION = 'SEMESTER'");
        } else {
            switch ($this->section) {
                case 1:
                    $SQL->where("MONTH = '" . $this->month . "'");
                    $SQL->where("YEAR = '" . $this->year . "'");
                    $SQL->where("SECTION = 'MONTH'");
                    break;
                case 2:
                    $SQL->where("TERM = '" . strtoupper($this->term) . "'");
                    $SQL->where("SECTION = 'SEMESTER'");
                    break;
            }
        }
        $SQL->group("A.SUBJECT_ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function getAvgClassPerformance($studentId, $term) {

        $entries = $this->getSQLClassPerformance($studentId, $term);

        $sumAvg = "";
        $sumCoeff = "";
        $result = "---";

        if ($entries) {
            foreach ($entries as $value) {
                if ($value->SCORE_TYPE == 1) {
                    if (is_numeric($value->SUBJECT_VALUE)) {
                        if ($value->COEFF_VALUE) {
                            $sumAvg += $value->SUBJECT_VALUE * $value->COEFF_VALUE;
                            $sumCoeff += $value->COEFF_VALUE;
                        }
                    }
                }
            }
        }
        if (is_numeric($sumAvg) && is_numeric($sumCoeff)) {
            $result = setRound($sumAvg / $sumCoeff);
        }

        return $result;
    }

    protected function scoreListClassPerformance() {

        $data = Array();
        $entries = $this->listStudentsByClass();
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $this->getAvgClassPerformance($value->STUDENT_ID, false);
            }
        }
        return $data;
    }

    protected function scoreListYearClassPerformance() {

        $data = Array();
        $entries = $this->listStudentsByClass();
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $this->getAvgYearClassPerformance($value->STUDENT_ID);
            }
        }
        return $data;
    }

    protected function getAvgYearClassPerformance($studentId) {
        $result = "---";
        switch (AcademicDBAccess::findAcademicTerm($this->academicId)) {
            case 1:
                break;
            case 2:
                break;
            default:
                $FIRST_SEMESTER = $this->getAvgClassPerformance($studentId, 'FIRST_SEMESTER');
                $SECOND_SEMESTER = $this->getAvgClassPerformance($studentId, 'SECOND_SEMESTER');
                $COEFF_FS = $this->classObject->SEMESTER1_WEIGHTING ? $this->classObject->SEMESTER1_WEIGHTING : 1;
                $COEFF_SS = $this->classObject->SEMESTER2_WEIGHTING ? $this->classObject->SEMESTER2_WEIGHTING : 1;
                switch ($this->classObject->YEAR_RESULT) {
                    case 1:
                        $result = is_numeric($FIRST_SEMESTER) ? $FIRST_SEMESTER : "---";
                        break;
                    case 2:
                        $result = is_numeric($SECOND_SEMESTER) ? $SECOND_SEMESTER : "---";
                        break;
                    default:
                        if (is_numeric($FIRST_SEMESTER) && is_numeric($SECOND_SEMESTER)) {
                            $COEFF = $COEFF_FS + $COEFF_SS;
                            if ($COEFF)
                                $result = setRound(($FIRST_SEMESTER * $COEFF_FS + $SECOND_SEMESTER * $COEFF_SS) / $COEFF);
                        } elseif (is_numeric($FIRST_SEMESTER) && !is_numeric($SECOND_SEMESTER)) {
                            $result = $FIRST_SEMESTER;
                        } elseif (!is_numeric($FIRST_SEMESTER) && is_numeric($SECOND_SEMESTER)) {
                            $result = $SECOND_SEMESTER;
                        }

                        break;
                }
                break;
        }

        return $result;
    }

    public function getStudentPerformance($section = false) {

        $SELECTION_A = Array('LEARNING_VALUE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');
        $SELECTION_C = Array('DESCRIPTION AS BEHAVIOR_DES', 'NAME AS BEHAVIOR_NAME', 'ID AS BEHAVIOR_ID');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_learning_performance"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_camemis_type'), 'A.BEHAVIOR_ID=C.ID', $SELECTION_C);
        $SQL->where("A.STUDENT_ID = '" . $this->studentId . "'");
        $SQL->where("A.CLASS_ID = '" . $this->academicId . "'");

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

    public function jsonActionStudentClassPerformance($encrypParams, $noJson = false) {

        if ($noJson) {
            $params = $encrypParams;
        } else {
            $params = Utiles::setPostDecrypteParams($encrypParams);
        }

        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $comment = isset($params["comment"]) ? addText($params["comment"]) : "";

        if (isset($params["id"])) {
            $this->studentId = $params["id"];
        }

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";
        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";

        $this->classObject = $this->getClassObject();

        $this->month = getMonthNumberFromMonthYear($this->monthyear);
        $this->year = getYearFromMonthYear($this->monthyear);

        $assessmentId = "";
        switch ($field) {
            case "ASSESSMENT_YEAR":
            case "ASSESSMENT_TOTAL":
            case "ASSESSMENT":
                $assessmentId = $newValue;
                break;
        }

        $facette = $this->getStudentPerformance();

        switch ($this->section) {
            case 1:
                $scoreList = $this->scoreListClassPerformance();
                $AVERAGE = $this->getAvgClassPerformance(
                        $this->studentId
                        , false);
                $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);
                $UPDATE_DATA["LEARNING_VALUE"] = $AVERAGE;
                $UPDATE_DATA["RANK"] = $RANK;
                $WHERE[] = "SECTION = 'MONTH'";
                $WHERE[] = "MONTH = '" . $this->month . "'";
                $WHERE[] = "YEAR = '" . $this->year . "'";

                $INSERT_DATA["LEARNING_VALUE"] = $AVERAGE;
                $INSERT_DATA["RANK"] = $RANK;
                $INSERT_DATA["MONTH"] = $this->month;
                $INSERT_DATA["YEAR"] = $this->year;
                $INSERT_DATA["SECTION"] = "MONTH";
                break;
            case 2:
                $scoreList = $this->scoreListClassPerformance();
                $AVERAGE = $this->getAvgClassPerformance(
                        $this->studentId
                        , false);
                $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);
                $UPDATE_DATA["LEARNING_VALUE"] = $AVERAGE;
                $UPDATE_DATA["RANK"] = $RANK;
                $WHERE[] = "SECTION = 'SEMESTER'";
                $WHERE[] = "TERM = '" . $this->term . "'";

                $INSERT_DATA["LEARNING_VALUE"] = $AVERAGE;
                $INSERT_DATA["RANK"] = $RANK;
                $INSERT_DATA["SECTION"] = "SEMESTER";
                $INSERT_DATA["TERM"] = $this->term;
                break;
            case 3:
                $scoreList = $this->scoreListYearClassPerformance();
                $AVERAGE = $this->getAvgYearClassPerformance($this->studentId);
                $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);
                $UPDATE_DATA["LEARNING_VALUE"] = $AVERAGE;
                $UPDATE_DATA["RANK"] = $RANK;
                $WHERE[] = "SECTION = 'YEAR'";

                $INSERT_DATA["LEARNING_VALUE"] = $AVERAGE;
                $INSERT_DATA["RANK"] = $RANK;
                $INSERT_DATA["SECTION"] = "YEAR";
                break;
        }

        if ($facette) {
            if ($assessmentId)
                $UPDATE_DATA["ASSESSMENT_ID"] = $assessmentId;

            if ($comment)
                $UPDATE_DATA["TEACHER_COMMENT"] = $comment;

            if (isset($params["CAMEMIS_TYPE"]))
                $UPDATE_DATA["BEHAVIOR_ID"] = $params["CAMEMIS_TYPE"];

            $WHERE[] = "STUDENT_ID = '" . $this->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $this->academicId . "'";

            self::dbAccess()->update('t_student_learning_performance', $UPDATE_DATA, $WHERE);
        } else {

            $INSERT_DATA["STUDENT_ID"] = $this->studentId;
            $INSERT_DATA["CLASS_ID"] = $this->academicId;

            if ($assessmentId)
                $INSERT_DATA["ASSESSMENT_ID"] = $assessmentId;

            if ($comment)
                $INSERT_DATA["TEACHER_COMMENT"] = $comment;

            if (isset($params["CAMEMIS_TYPE"]))
                $INSERT_DATA["BEHAVIOR_ID"] = $params["CAMEMIS_TYPE"];


            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";

            self::dbAccess()->insert("t_student_learning_performance", $INSERT_DATA);
        }

        if (!$noJson) {
            return array(
                "success" => true
            );
        }
    }

    public static function getStudentYearAssessmentResult($studentId, $academicId) {

        $SELECTION_A = Array('LEARNING_VALUE AS AVERAGE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION AS ASSESSMENT', 'LETTER_GRADE');
        $SELECTION_C = Array('DESCRIPTION AS BEHAVIOR_DES', 'ID AS BEHAVIOR_ID', 'NAME AS BEHAVIOR_NAME');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_learning_performance"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_camemis_type'), 'A.BEHAVIOR_ID=B.ID', $SELECTION_C);
        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.CLASS_ID = '" . $academicId . "'");
        $SQL->where("A.SECTION = 'YEAR'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getStudentTermAssessmentResult($studentId, $academicId, $term) {

        $SELECTION_A = Array('LEARNING_VALUE AS AVERAGE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION AS ASSESSMENT', 'LETTER_GRADE');
        $SELECTION_C = Array('DESCRIPTION AS BEHAVIOR_DES', 'ID AS BEHAVIOR_ID', 'NAME AS BEHAVIOR_NAME');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_learning_performance"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_camemis_type'), 'A.BEHAVIOR_ID=B.ID', $SELECTION_C);
        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.CLASS_ID = '" . $academicId . "'");
        $SQL->where("A.TERM = '" . $term . "'");
        $SQL->where("A.SECTION = 'SEMESTER'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonLoadStudentAllSubjectAssessment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? $params["start"] : 0;
        $limit = isset($params["limit"]) ? $params["limit"] : 50;

        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";

        $this->classObject = $this->getClassObject();

        if ($this->monthyear) {
            $this->month = getMonthNumberFromMonthYear($this->monthyear);
            $this->year = getYearFromMonthYear($this->monthyear);
        }

        parent::$this->term = $this->term;
        parent::$this->academicId = $this->academicId;
        parent::$this->section = $this->section;

        $listOfSubjects = $this->listSubjects();

        $data = Array();

        if ($listOfSubjects) {
            if ($listOfSubjects) {
                $i = 0;
                foreach ($listOfSubjects as $value) {

                    $data[$i]["SHORT"] = $value->SUBJECT_SHORT;
                    $data[$i]["NAME"] = $value->SUBJECT_NAME;

                    switch ($this->section) {
                        case 1:
                            $facette = $this->getStudentSubjectAssessment(
                                    $this->studentId
                                    , $value->SUBJECT_ID
                                    , false);
                            break;
                        case 2:
                            $facette = $this->getStudentSubjectYearSemesterAssessment(
                                    $this->studentId
                                    , $value->SUBJECT_ID
                                    , $this->term);
                            break;
                        case 3:
                            $facette = $this->getStudentSubjectAssessment(
                                    $this->studentId
                                    , $value->SUBJECT_ID
                                    , false);
                            break;
                    }

                    $data[$i]["SUBJECT_VALUE"] = $facette ? $facette->SUBJECT_VALUE : "---";
                    $data[$i]["ASSESSMENT"] = $facette ? setShowText($facette->LETTER_GRADE) : "---";
                    $data[$i]["RANK"] = $facette ? setShowText($facette->RANK) : "---";
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

    public function jsonLoadStudentClassPerformance($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->monthyear = isset($params["monthyear"]) ? $params["monthyear"] : "";
        $this->academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $this->studentId = isset($params["id"]) ? addText($params["id"]) : "";

        $this->section = isset($params["section"]) ? $params["section"] : "";
        $this->term = isset($params["term"]) ? $params["term"] : "";

        $this->classObject = $this->getClassObject();

        if ($this->monthyear) {
            $this->month = getMonthNumberFromMonthYear($this->monthyear);
            $this->year = getYearFromMonthYear($this->monthyear);
        }

        $data = Array();
        $facette = $this->getStudentPerformance();

        if ($facette) {
            $data["comment"] = setShowText($facette->TEACHER_COMMENT);
            $data["LEARNING_VALUE"] = setShowText($facette->LEARNING_VALUE);
            $data["LEARNING_VALUE"] = setShowText($facette->LEARNING_VALUE);
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["RANK"] = setShowText($facette->RANK);
            $data["LETTER_GRADE"] = setShowText($facette->LETTER_GRADE);
            $data["behaviorId"] = setShowText($facette->BEHAVIOR_ID);
            $data["CHOOSE_BEHAVIOR_NAME"] = setShowText($facette->BEHAVIOR_NAME);
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

}

?>