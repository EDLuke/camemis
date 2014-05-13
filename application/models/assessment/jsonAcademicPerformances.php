<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once 'models/assessment/AcademicPerformances.php';

class jsonAcademicPerformances extends AcademicPerformances {

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

        if (isset($params["term"]))
            $this->term = $params["term"];

        if (isset($params["academicId"]))
            $this->academicId = $params["academicId"];

        if (isset($params["studentId"]))
            $this->studentId = $params["studentId"];

        if (isset($params["globalSearch"]))
            $this->globalSearch = $params["globalSearch"];

        if (isset($params["id"])) {
            $this->studentId = $params["id"];
        }

        if (isset($params["field"]))
            $this->actionField = $params["field"];

        if (isset($params["newValue"]))
            $this->actionValue = $params["newValue"];

        if (isset($params["section"]))
            $this->section = $params["section"];
    }

    public function jsonListStudentsMonthAcademicPerformance($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getListDisplayStudentsMonthAcademicPerformance();

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

    public function jsonListStudentsTermAcademicPerformance($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplayListStudentsTermAcademicPerformance();

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

    public function jsonListStudentsYearAcademicPerformance($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplayListStudentsYearAcademicPerformance();

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

    public function jsonActionStudentAcademicPerformance($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $this->setActionStudentAcademicPerformance();

        return array(
            "success" => true
        );
    }

}

?>