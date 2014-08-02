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
    
    public function getAttendanceType($personType,$type) {
        $data = AbsentTypeDBAccess::getAllAbsentType(array('objectType' => $personType, 'status' => 1,'type' => $type));
        return $data;
    }
    
    ///////////////////
    //Student Data
    ///////////////////
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
    
    public function groupAge(){
        $data = array();
        $studetnAge = $this->getStudentAge();
        foreach($studetnAge as $value){   
            $data[$value->AGE][]=$value->AGE;
        }
        
        return $data;
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
    
    //@Visal
    public function getCountStudentAdditionalInformation(){
        
        if($this->objectType){
            switch ($this->objectType) {
                case 'CAMPUS':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['dataType'] = $this->dataValue;
                    break;
                case 'GRADE':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['gradeId'] = $this->gradeId;
                    $params['dataType'] = $this->dataValue;
                    break;
                case 'CLASS':
                    $params['schoolyearId'] = $this->schoolyearId;
                    $params['campusId'] = $this->campusId;
                    $params['classId'] = $this->classId;
                    $params['dataType'] = $this->dataValue;
                    break;
            }
        }
        
        $stdClass = (object) $params;   
        $result = SQLStudentFilterReport::getCountStudentAdditional($stdClass);
        return $result?count($result):0;    
    }
    
    /*public function getcountStudentEDBackgroundDegreeOrMajor(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "type"  => $this->type
                , "objecttype"  => 'EDUBACK'
                , "camemisType"  => $this->camemisType
        );
        $result = SQLStudentFilterReport::getStudentInfo($stdClass);
        
        return count($result)?count($result):0;    
    }
    
    public function getcountRelationshipStudent(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "type"  => $this->type
                , "objecttype"  => 'PARINFO'
                , "camemisType"  => $this->camemisType
        );
        $result = SQLStudentFilterReport::getStudentInfo($stdClass);
        
        return count($result)?count($result):0;    
    }*/
    
    public function getCountDailyStudentAbsence(){
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "absentType"  => $this->absentType
        );
        $result = SQLStudentFilterReport::getStudentAttendanceType($stdClass);
        return count($result)?count($result):0;
    }
    
    public function getCountBlockStudentAbsence(){
        $count=0;
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "absentType"  => $this->absentType
        );
        $result = SQLStudentFilterReport::getStudentAttendanceType($stdClass);
        foreach($result as $value){
            $count += $value->COUNT_DATE;        
        }
        return $count;
    }
    //////////////////////
    ///Staff Data
    /////////////////////
    
    public function getCountDailyTeacherAbsence(){
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
        );
        $result=0;
        $teachers = SQLTeacherFilterReport::getAssignedTeacher($stdClass);
        if($teachers){
            foreach ($teachers as $value){
                $result += count(SQLTeacherFilterReport::sqlTeacherAttendance($value->TEACHER_ID,$value->SCHOOLYEAR_START,$value->SCHOOLYEAR_END,$this->absentType));    
            }
        }
        return $result;
    }
    
    public function getCountBlockTeacherAbsence(){
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
        );
        $result=0;
        $teachers = SQLTeacherFilterReport::getAssignedTeacher($stdClass);
        if($teachers){
            foreach($teachers as $value){
                $attendance=SQLTeacherFilterReport::sqlTeacherAttendance($value->TEACHER_ID,$value->SCHOOLYEAR_START,$value->SCHOOLYEAR_END,$this->absentType);
                if($attendance){
                    foreach($attendance as $valueattendance){
                        $result+=$valueattendance->COUNT_DATE;      
                    }
                }           
            }
        }
        return $result;
    }
    
    //@Visal
    public function getCountStaffActive(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "status"  => 1
        );
        $result = SQLTeacherFilterReport::getAssignedTeacher($stdClass);
        
        return $result?count($result):0;   
    }
    
    public function getCountStaffDeactive(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "status"  => 0
        );
        $result = SQLTeacherFilterReport::getAssignedTeacher($stdClass);
        
        return $result?count($result):0;   
    }
    
    
    //@Visal
    public function getStaffAge(){
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
        );
        $result = SQLTeacherFilterReport::getAgeStaffEnroll($stdClass); 
        return $result;  
    }
    
    //@Visal
    public function groupStaffAge(){
        $data = array();
        $staffAge = $this->getStaffAge();
        foreach($staffAge as $value){   
            $data[$value->AGE][]=$value->AGE;
        }
        
        return $data;
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
    
    public function getCountStaffAbsence(){
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "absentType"  => $this->absentType
        );
        $result = SQLStudentFilterReport::countStudentAttendanceType($stdClass);  
    }
    
    
    ///test new
    public function getcountPersonInfo(){
        
        $stdClass = (object) array(
                "schoolyearId" => $this->schoolyearId
                , "campusId" => $this->campusId
                , "gradeId" => $this->gradeId
                , "classId" => $this->classId
                , "type"  => $this->type
                , "objecttype"  => $this->objectType
                , "camemisType"  => $this->camemisType
        );
        switch(strtoupper($this->personType)){
            case'STUDENT':
                $result = SQLStudentFilterReport::getStudentInfo($stdClass);
                break;
            case'STAFF':
                $result = SQLTeacherFilterReport::getTeacherInfo($stdClass);
                break;   
        }
        
        return count($result)?count($result):0;    
    }
    ///
   
}

?>