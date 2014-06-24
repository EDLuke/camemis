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
    
    public function getCountStudentFemale(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "gender"  => 2
        );
        $result = SQLStudentFilterReport::getCountStudent($stdClass);
        
        return $result;    
    }
    
    public function getCountStudentMale(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "gender"  => 1
        );
        $result = SQLStudentFilterReport::getCountStudent($stdClass);
        
        return $result;    
    }
    
    public function getTotalStudents(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
        );
        $result = SQLStudentFilterReport::getCountStudent($stdClass);
        
        return $result;   
    }
    
    public function getCountStudentActive(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "status"  => 1
        );
        $result = SQLStudentFilterReport::getCountStudent($stdClass);
        
        return $result;    
    }
    
    public function getCountStudentDeactive(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "status"  => 0
        );
        $result = SQLStudentFilterReport::getCountStudent($stdClass);
        
        return $result;    
    }
    
    public function getStudentAge(){
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
        );
        $result = SQLStudentFilterReport::getAgeStudentEnroll($stdClass);  
        return $result;  
    }
    
    public function groupAge(){
        $data = array();
        $studetnAge = $this->getStudentAge();
        foreach($studetnAge as $value){   
            $data[$value->AGE][]=$value->AGE;
        }
        
        return $data;
    }
    
    public function getCountStudentPersonalInformation(){
        if($this->objectType){
            switch ($this->objectType) {
                case 'CAMPUS':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params[$this->dataType] = $this->dataValue;
                    break;
                case 'GRADE':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['gradeId'] = $this->gradeId;
                    $params[$this->dataType] = $this->dataValue;
                    break;
                case 'CLASS':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['classId'] = $this->classId;
                    $params[$this->dataType] = $this->dataValue;
                    break;
            }
        }
        
        $stdClass = (object) $params;   
        $result = SQLStudentFilterReport::getCountStudent($stdClass);
        return $result;  
    }
    
    public function getCountStaffPersonalInformation(){
        
        if($this->objectType){
            switch ($this->objectType) {
                case 'CAMPUS':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params[$this->dataType] = $this->dataValue;
                    break;
                case 'GRADE':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['gradeId'] = $this->gradeId;
                    $params[$this->dataType] = $this->dataValue;
                    break;
                case 'CLASS':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['classId'] = $this->classId;
                    $params[$this->dataType] = $this->dataValue;
                    break;
            }
        }
        
        $stdClass = (object) $params;   
        $result = SQLTeacherFilterReport::getAssignedTeacher($stdClass);
        return $result?count($result):0;    
    }
    
}

?>