<?php
////////////////////////////////////////////////////////////////////////////////
// @sor veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/student_filter/SQLStudentFilterReport.php';
require_once setUserLoacalization();

abstract class StudentFilterProperties {

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
    
    public function getCamemisType(){
        if($this->gridType){
            switch($this->gridType){
                case 'STUDENT_ATTENDANCE_FILTER':
                    $stdClass['personType']="STUDENT";
                    $stdClass['status']=1;
                    $typeObject = SQLStudentFilterReport::getAttendanceType((object)$stdClass);
                    break;
                case 'STUDENT_DISCIPLINE_FILTER':
                    $typeObject = SQLStudentFilterReport::getDisciplineType();
                    break;
                case 'STUDENT_ADVISORY_FILTER':
                    $typeObject = SQLStudentFilterReport::getAdvisoryType();
                    break;    
            }
        }
        return $typeObject;
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

}

?>