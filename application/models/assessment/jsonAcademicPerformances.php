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
            $this->start = (int) $params["start"];

        if (isset($params["limit"]))
            $this->limit = (int) $params["limit"];

        if (isset($params["date"]))
            $this->date = addText($params["date"]);

        if (isset($params["monthyear"]))
            $this->monthyear = addText($params["monthyear"]);

        if (isset($params["term"]))
            $this->term = addText($params["term"]);

        if (isset($params["academicId"]))
            $this->academicId = (int) $params["academicId"];

        if (isset($params["studentId"]))
            $this->studentId = addText($params["studentId"]);

        if (isset($params["globalSearch"]))
            $this->globalSearch = addText($params["globalSearch"]);

        if (isset($params["id"])) {
            $this->studentId = addText($params["id"]);
        }

        if (isset($params["field"]))
            $this->actionField = addText($params["field"]);

        if (isset($params["newValue"])) {
            $this->newValue = addText($params["newValue"]);
        }

        if (isset($params["section"]))
            $this->section = addText($params["section"]);

        if (isset($params["comboValue"])) {
            $this->comboValue = addText($params["comboValue"]);
        }
        
        if (isset($params["target"])) {
            $this->target = addText($params["target"]);
        }
    }

    public function jsonListAcademicPerformanceFromMonth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplayListAcademicPerformanceByMonth();

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

    public function jsonListAcademicPerformanceFromTerm($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplayListAcademicPerformanceByTerm();

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

    public function jsonListAcademicPerformanceFromYear($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $this->setParams($params);

        $data = $this->getDisplayListAcademicPerformanceByYear();

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