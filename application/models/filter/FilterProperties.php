<?php
////////////////////////////////////////////////////////////////////////////////
// @sor veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/filter/SQLStudentFilterReport.php';
require_once 'models/filter/SQLTeacherFilterReport.php';
require_once setUserLoacalization();

abstract class FilterProperties {

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

    public static function getCamemisType($camemisType){
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        $SQL->where("OBJECT_TYPE =?",$camemisType);
        $SQL->where("PARENT <> 0");
        $SQL->order("ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;        
    }

    public function getFirstCulumnData() {
        $data = array();
        if ($this->objectType) {
            switch ($this->objectType) {
                case 'CAMPUS':
                    $academicObject = new AcademicDBAccess();
                    $data = $academicObject->searchGrade(array('campusId' => $this->campusId));
                    break;
                case 'GRADE':
                    $academicObject = new AcademicDBAccess();
                    $data = $academicObject->searchClass(array('gradeId' => $this->gradeId, 'schoolyearId' => $this->schoolyearId));
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
    
    public function getObjectGradeData(){
        $data = '';
        if ($this->objectType) {
            switch ($this->objectType) {
                case 'CAMPUS':
                    $data = AcademicDBAccess::findCampusSchoolyear($this->campusId, $this->schoolyearId);
                    break;
                case 'GRADE':
                    $data = AcademicDBAccess::findGradeSchoolyear($this->gradeId, $this->schoolyearId);
                    break;
                case 'CLASS':
                    $data = AcademicDBAccess::sqlGradeFromId($this->classId);
                    break;
            }
        }
        return $data;    
    }
    
    

}

?>