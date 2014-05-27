<?php

////////////////////////////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////
require_once 'models/filter/FilterProperties.php';

class FilterData extends FilterProperties {

    function __construct() {
        parent::__construct();
    }
    
    public static function getFullName($firstname, $lastname) {
        if (!SchoolDBAccess::displayPersonNameInGrid()) {
            return setShowText($lastname) . " " . setShowText($firstname);
        } else {
            return setShowText($firstname) . " " . setShowText($lastname);
        }
    }
    
    public function listAssignedTeacher(){
        $data = array();
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
        );
        $result = SQLTeacherFilterReport::SQLAssignedTeacher($stdClass);
        $i = 0;
        if($result)
        {
            foreach($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = setShowText($value->CODE);  
                $data[$i]["STAFF_SCHOOL_ID"] = setShowText($value->STAFF_SCHOOL_ID);
                $data[$i]["FULL_NAME"] = self::getFullName($value->FIRSTNAME, $value->LASTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

                $i++;        
            }
        }
        return $data;    
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
                        $stdClass->gradeId = $firstCulum->ID;
                        break;
                    case 'GRADE':
                        $data[$i]["FIRST_CULUMN"] = $firstCulum->NAME;
                        $stdClass->classId = $firstCulum->ID;
                        break;
                    case 'CLASS':
                        $data[$i]["FIRST_CULUMN"] = self::getFullName($firstCulum->FIRSTNAME, $firstCulum->LASTNAME);
                        $stdClass->personId = $firstCulum->ID;
                        break;
                }
                $stdClass->campusId = $this->campusId;
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
                            case 'TEACHER_ATTENDANCE_FILTER':
                                $stdClass->absentType = $value->ID;
                                $count = SQLTeacherFilterReport::countTeacherAttendanceType($stdClass);
                                $data[$i]['ATTENDANCE_'.$value->ID] = $count;
                                break;
                            case 'TEACHER_DISCIPLINE_FILTER':
                                $stdClass->disciplineType = $value->ID;
                                $count = SQLTeacherFilterReport::countTeacherDisciplineType($stdClass);
                                $data[$i]['DISCIPLINE_'.$value->ID] = $count;
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