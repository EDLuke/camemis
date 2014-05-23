<?php
////////////////////////////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////
require_once 'models/student_filter/StudentFilterData.php';

class jsonStudentFilterReport extends StudentFilterData {

    public function __construct() {
        
    }

    public function setParams($params) {
        if (isset($params["start"]))
            $this->start = (int) $params["start"];

        if (isset($params["limit"]))
            $this->limit = (int) $params["limit"];

        if (isset($params["campusId"]))
            $this->campusId = addText($params["campusId"]);
        
        if (isset($params["classId"]))
            $this->classId = addText($params["classId"]);
        
        if (isset($params["gradeId"]))
            $this->gradeId = addText($params["gradeId"]);
        
        if (isset($params["schoolyearId"]))
            $this->schoolyearId = addText($params["schoolyearId"]);
            
        if (isset($params["objectType"]))
            $this->objectType = addText($params["objectType"]);
        
        if (isset($params["personType"]))
            $this->personType = addText($params["personType"]);
            
        if (isset($params["status"]))
            $this->status = addText($params["status"]);
            
        if (isset($params["gridType"]))
            $this->gridType = addText($params["gridType"]);
    }

    public function getGridData($params) {
        
        $this->setParams($params);
        $data = $this->getFilterData();

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
}

?>