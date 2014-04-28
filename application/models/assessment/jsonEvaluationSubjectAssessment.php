<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once 'models/assessment/EvaluationSubjectAssessment.php';

class jsonEvaluationSubjectAssessment extends EvaluationSubjectAssessment {

    public function __construct()
    {
        
    }

    public function setParams($params)
    {
        if (isset($params["start"]))
            $this->start = $params["start"];

        if (isset($params["limit"]))
            $this->limit = $params["limit"];

        if (isset($params["date"]))
            $this->date = $params["date"];

        if (isset($params["monthyear"]))
            $this->monthyear = $params["monthyear"];

        if (isset($params["score"]))
            $this->score = $params["score"];

        if (isset($params["term"]))
            $this->term = $params["term"];

        if (isset($params["classId"]))
            $this->classId = $params["classId"];

        if (isset($params["studentId"]))
            $this->studentId = $params["studentId"];

        if (isset($params["subjectId"]))
            $this->subjectId = $params["subjectId"];

        if (isset($params["assignmentId"]))
            $this->assignmentId = $params["assignmentId"];

        if (isset($params["globalSearch"]))
            $this->globalSearch = $params["globalSearch"];

        if (isset($params["id"]))
        {
            $this->studentId = $params["id"];
        }

        if (isset($params["field"]))
            $this->actionField = $params["field"];

        if (isset($params["newValue"]))
            $this->actionValue = $params["newValue"];

        if (isset($params["section"]))
            $this->section = $params["section"];
    }

    public function jsonListStudentSubjectAssignments($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getListStudentSubjectAssignments();

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function jsonActionStudentScoreSubjectAssignment($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);
        $this->actionStudentScoreSubjectAssignment();

        return array(
            "success" => true
        );
    }

    public function jsonListResultStudentsSubjectAssignment($params)
    {
        $this->setParams($params);
        print_r($this->getListResultStudentsSubjectAssignment());
    }

    public function jsonSubjectMonthResult($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getSubjectMonthResult();

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function jsonSubjectTermResult($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getSubjectTermResult();

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function jsonSubjectYearResult($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getSubjectYearResult();

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function jsonActionStudentSubjectAssessment($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);
        $this->actionStudentSubjectAssessment();

        return array(
            "success" => true
        );
    }

    public function jsonActionPublishSubjectAssessment($encrypParams)
    {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);
        $this->actionPublishSubjectAssessment();

        return array(
            "success" => true
        );
    }

}

?>