<?php

////////////////////////////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////
require_once 'models/filter/StudentFilterProperties.php';

class StudentFilterData extends StudentFilterProperties {

    function __construct() {
        parent::__construct();
    }

    public function getFilterData() {

        $data = array();

        $stdClass = (object) array(
                    "schoolyearId" => $this->schoolyearId
                    , "objectType" => $this->objectType
                    , "personType" => $this->personType
                    , "status" => $this->status
                    , "gridType" => $this->gridType
        );

        $i = 0;

        $typeObject = $this->getCamemisType();
        $culumnObject = $this->getFirstCulumnData();
        if ($culumnObject) {
            foreach ($culumnObject as $firstCulum) {
                switch ($this->objectType) {
                    case 'CAMPUS':
                        $data[$i]["FIRST_CULUMN"] = $firstCulum->NAME;
                        $this->gradeId = $firstCulum->ID;
                        break;
                    case 'GRADE':
                        $data[$i]["FIRST_CULUMN"] = $firstCulum->NAME;
                        $this->classId = $firstCulum->ID;
                        break;
                    case 'CLASS':
                        $data[$i]["FIRST_CULUMN"] = StudentSearchDBAccess::getFullName($firstCulum->FIRSTNAME, $firstCulum->LASTNAME);
                        $this->studentId = $firstCulum->ID;
                        break;
                }
                $stdClass->campusId = $this->campusId;
                $stdClass->classId = $this->classId;
                $stdClass->gradeId = $this->gradeId;
                foreach ($typeObject as $value) {
                    if ($this->gridType) {
                        switch ($this->gridType) {
                            case 'STUDENT_ATTENDANCE_FILTER':
                                $stdClass->absentType = $value->ID;
                                $count = SQLStudentFilterReport::countStudentAttendanceType($stdClass);
                                $data[$i]['ATTENDANCE_' . $value->ID] = $count;
                                break;
                            case 'STUDENT_DISCIPLINE_FILTER':
                                $stdClass->disciplineType = $value->ID;
                                $count = SQLStudentFilterReport::countStudentDisciplineType($stdClass);
                                $data[$i]['DISCIPLINE_' . $value->ID] = $count;
                                break;
                            case 'STUDENT_ADVISORY_FILTER':
                                $stdClass->advisoryType = $value->ID;
                                $count = SQLStudentFilterReport::countStudentAdvisoryType($stdClass);
                                $data[$i]['ADVISORY_' . $value->ID] = $count;
                                break;
                        }
                    }
                }
                $i++;
            }
        }
        return $data;
    }

}

?>