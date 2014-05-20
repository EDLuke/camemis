<?php
///////////////////////////////////////////////////////////
// @sor veasna
// 16/05/2014
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/student/StudentAttendanceDBAccess.php';
require_once setUserLoacalization();

class StudentFilterReportDBAccess {

    public $datafield = array();
    
    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function __get($name) {
        if (array_key_exists($name, $this->datafield)) {
            return $this->datafield[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->datafield[$name] = $value;
    }

    public function __isset($name) {
        return array_key_exists($name, $this->datafield);
    }

    public function __unset($name) {
        unset($this->datafield[$name]);
    }
    
    public function countStudentAttendanceType(){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_student_attendance'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS_ID=B.ID', array());
        
        if($this->campusId){
            $SQL->where("B.CAMPUS_ID = '" . $this->campusId . "'");    
        }
        
        if($this->gradeId){
            $SQL->where("B.GRADE_ID = '" . $this->gradeId . "'");   
        }
        
        if($this->studentId)
            $SQL->where("A.STUDENT_ID = '" . $this->studentId . "'");

        if ($this->classId)
            $SQL->where("A.CLASS_ID = '" . $this->classId . "'");
        
        if($this->absentType)
            $SQL->where("A.ABSENT_TYPE = '" . $this->absentType . "'");
        
        if($this->schoolyearId)
            $SQL->where("A.SCHOOLYEAR_ID = '" . $this->schoolyearId . "'");
        
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    }
    
    public function countStudentDisciplineType(){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.STUDENT_ID=B.STUDENT', array());
        
        if($this->campusId){
            $SQL->where("B.CAMPUS = '" . $this->campusId . "'");    
        }
        
        if($this->gradeId){
            $SQL->where("B.GRADE = '" . $this->gradeId . "'");   
        }
        
        if($this->studentId)
            $SQL->where("A.STUDENT_ID = '" . $this->studentId . "'");

        if ($this->classId)
            $SQL->where("B.CLASS = '" . $this->classId . "'");
        
        if($this->schoolyearId)
            $SQL->where("B.SCHOOL_YEAR = '" . $this->schoolyearId . "'");
        
        if($this->disciplineType)
            $SQL->where("A.DISCIPLINE_TYPE = '" . $this->disciplineType . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    }
    
    public function countStudentAdvisoryType(){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_student_advisory'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_advisory'), 'A.ADVISORY_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'A.STUDENT_ID=C.STUDENT', array());
        
        if($this->campusId){
            $SQL->where("C.CAMPUS = '" . $this->campusId . "'");    
        }
        
        if($this->gradeId){
            $SQL->where("C.GRADE = '" . $this->gradeId . "'");   
        }
        
        if($this->studentId)
            $SQL->where("A.STUDENT_ID = '" . $this->studentId . "'");

        if ($this->classId)
            $SQL->where("C.CLASS = '" . $this->classId . "'");
        
        if($this->schoolyearId)
            $SQL->where("C.SCHOOL_YEAR = '" . $this->schoolyearId . "'");
        
        if($this->advisoryType)
            $SQL->where("B.ADVISORY_TYPE = '" . $this->advisoryType . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    }
    
    
    public function getAttendanceType(){
        $data = AbsentTypeDBAccess::getAllAbsentType(array('objectType'=>$this->personType,'status'=>$this->status)); 
        return $data;       
    }
    
    public function getDisciplineType(){
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        $SQL->where("OBJECT_TYPE = 'DISCIPLINE_TYPE_STUDENT'");
        $SQL->where("PARENT <> 0");
        $SQL->order("ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);   
        return $result;     
    }
    
    public function getAdvisoryType(){
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        $SQL->where("OBJECT_TYPE = 'ADVISORY_TYPE'");
        $SQL->where("PARENT <> 0");
        $SQL->order("ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);   
        return $result;     
    }
    
    public function getFirstCulumnData(){
        $data = array();
        if($this->objectType){
            switch($this->objectType){
                case 'CAMPUS':
                    $academicObject = new AcademicDBAccess();
                    $data = $academicObject->searchGrade(array('campusId'=>$this->campusId)); 
                    break;
                case 'GRADE':
                    $academicObject = new AcademicDBAccess();
                    $data = $academicObject->searchClass(array('gradeId'=>$this->gradeId,'schoolyearId'=>$this->schoolyearId));
                    break;
                case 'CLASS':
                    $studentEnroll = new StudentSearchDBAccess();
                    $studentEnroll->classId = $this->classId;
                    $data = $studentEnroll->queryAllStudents();
                    break;     
            }
        }
        return $data;   
    }
    
    public function getGridData($params, $isJson = true){
        
        $this->start = isset($params["start"]) ? (int) $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $this->campusId = isset($params["campusId"]) ? $params["campusId"] : "";
        $this->classId = isset($params["classId"]) ? $params["classId"] : "";
        $this->gradeId = isset($params["gradeId"]) ? $params["gradeId"] : "";
        $this->schoolyearId = isset($params["schoolyearId"]) ? $params["schoolyearId"] : "";
        $this->objectType = isset($params["objectType"]) ? $params["objectType"] : "";
        $this->personType = isset($params["personType"]) ? $params["personType"] : "";//student or staff
        $this->status = isset($params["status"]) ? $params["status"] : "";
        $this->gridType = isset($params["gridType"]) ? $params["gridType"] : "";
        $this->isJson = $isJson;
        $data = array();
        $i = 0;
        if($this->gridType){
            switch($this->gridType){
                case 'STUDENT_ATTENDANCE_FILTER':
                    $typeObject = $this->getAttendanceType();
                    break;
                case 'STUDENT_DISCIPLINE_FILTER':
                    $typeObject = $this->getDisciplineType();
                    break;
                case 'STUDENT_ADVISORY_FILTER':
                    $typeObject = $this->getAdvisoryType();
                    break;    
            }
        }
        
        $culumnObject = $this->getFirstCulumnData();
        if($culumnObject){
            foreach($culumnObject as $firstCulum){
                switch($this->objectType){
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
                
                foreach($typeObject as $value){
                    if($this->gridType){
                        switch($this->gridType){
                            case 'STUDENT_ATTENDANCE_FILTER':
                                $this->absentType = $value->ID;
                                $count = $this->countStudentAttendanceType();
                                $data[$i]['ATTENDANCE_'.$value->ID] = $count;
                                break;
                            case 'STUDENT_DISCIPLINE_FILTER':
                                $this->disciplineType = $value->ID;
                                $count = $this->countStudentDisciplineType();
                                $data[$i]['DISCIPLINE_'.$value->ID] = $count;
                                break;
                            case 'STUDENT_ADVISORY_FILTER':
                                $this->advisoryType = $value->ID;
                                $count = $this->countStudentAdvisoryType();
                                $data[$i]['ADVISORY_'.$value->ID] = $count;
                                break;    
                        }
                    }
                }  
                $i++;            
            }
        }
        
        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($this->isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }   
    }
 
}

?>