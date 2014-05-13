<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once 'models/assessment/EvaluationSubjectAssessment.php';

class jsonEvaluationSubjectAssessment extends EvaluationSubjectAssessment {

    public function __construct() {
        
    }

    public function setParams($params) {
        if (isset($params["start"]))
            $this->start = $params["start"];

        if (isset($params["limit"]))
            $this->limit = $params["limit"];

        if (isset($params["date"]))
            $this->date = $params["date"];

        if (isset($params["monthyear"]))
            $this->monthyear = $params["monthyear"];

        if (isset($params["setId"]))
            $this->setId = $params["setId"];

        if (isset($params["term"]))
            $this->term = addText($params["term"]);

        if (isset($params["academicId"]))
            $this->academicId = $params["academicId"];

        if (isset($params["studentId"]))
            $this->studentId = $params["studentId"];

        if (isset($params["subjectId"]))
            $this->subjectId = $params["subjectId"];

        if (isset($params["assignmentId"]))
            $this->assignmentId = $params["assignmentId"];

        if (isset($params["globalSearch"]))
            $this->globalSearch = addText($params["globalSearch"]);

        if (isset($params["id"]))
            $this->studentId = $params["id"];

        if (isset($params["newValue"]))
            $this->actionValue = addText($params["newValue"]);

        if (isset($params["section"]))
            $this->section = $params["section"];
        
        if (isset($params["field"]))
            $this->actionField = $params["field"];

        if (isset($params["MODIFY_DATE"]))
            $this->modify_date = $params["MODIFY_DATE"];

        if (isset($params["CONTENT"]))
            $this->content = addText($params["CONTENT"]);
    }

    public function jsonListStudentSubjectAssignments($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getListStudentSubjectAssignments();

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

    public function jsonActionTeacherScoreEnter($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);
        $this->actionTeacherScoreEnter();

        return array(
            "success" => true
            , "SCHORE_DATE" => $this->countTeacherScoreDate()
        );
    }

    public function jsonSubjectMonthResult($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplaySubjectMonthResult();

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

    public function jsonSubjectTermResult($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplaySubjectTermResult();

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

    public function jsonSubjectYearResult($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplaySubjectYearResult();

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

    public function jsonActionStudentSubjectAssessment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);
        $this->actionStudentSubjectAssessment();

        return array("success" => true);
    }

    public function jsonActionPublishSubjectAssessment($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);
        $this->actionPublishSubjectAssessment();

        return array("success" => true);
    }

    public function jsonListStudentsTeacherScoreEnter($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getListStudentsTeacherScoreEnter();

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

    public function jsonActionDeleteOneStudentTeacherScoreEnter($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $this->actionDeleteOneStudentTeacherScoreEnter();

        return array("success" => true);
    }

    public function jsonActionDeleteAllStudentsTeacherScoreEnter($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $this->actionDeleteAllStudentsTeacherScoreEnter();

        return array("success" => true);
    }

    public function jsonActionDeleteSubjectScoreAssessment($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $this->actionDeleteSubjectScoreAssessment();

        return array("success" => true);
    }

    public function jsonAcitonSubjectAssignmentModifyScoreDate($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $this->acitonSubjectAssignmentModifyScoreDate();

        return array("success" => true);
    }

    public function jsonActionContentTeacherScoreInputDate($params) {
        $this->setParams($params);
        $this->actionContentTeacherScoreInputDate();

        return array("success" => true);
    }

    public function jsonLoadContentTeacherScoreInputDate($params) {
        $this->setParams($params);

        return Array(
            "success" => true
            , "data" => $this->loadContentTeacherScoreInputDate()
        );
    }

}

?>