<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once 'models/assessment/EvaluationGradebook.php';

class jsonEvaluationGradebook extends EvaluationGradebook {

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

        if (isset($params["term"]))
            $this->term = $params["term"];

        if (isset($params["academicId"]))
            $this->academicId = $params["academicId"];

        if (isset($params["studentId"]))
            $this->studentId = $params["studentId"];

        if (isset($params["globalSearch"]))
            $this->globalSearch = $params["globalSearch"];

        if (isset($params["id"]))
            $this->studentId = $params["id"];

        if (isset($params["section"]))
            $this->section = $params["section"];
    }

    public function jsonStudentGradebookMonth($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getStudentGradebookMonth();

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

    public function jsonStudentGradebookTerm($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getStudentGradebookTerm();

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

    public function jsonStudentGradebookYear($encrypParams)
    {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getStudentGradebookYear();

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

}

?>