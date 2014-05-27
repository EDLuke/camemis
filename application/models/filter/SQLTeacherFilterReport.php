<?php
///////////////////////////////////////////////////////////
// @sor veasna
// 16/05/2014
//////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/' . Zend_Registry::get('MODUL_API_PATH') . '/student/StudentAttendanceDBAccess.php';

class SQLTeacherFilterReport {
    
    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }
    
    
    public static function SQLAssignedTeacher($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_staff'), array("*"));
        $SQL->joinLeft(array('B' => 't_schedule'), 'A.ID=B.TEACHER_ID', array());
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.ACADEMIC_ID', array("NAME AS CLASS","TITLE AS TITLE"));
        
        if($stdClass->campusId)
        {
            $SQL->where("C.CAMPUS_ID = ?",$stdClass->campusId);    
        }
        
        if($stdClass->gradeId)
        {
            $SQL->where("C.GRADE_ID = ?",$stdClass->gradeId);   
        }
        
        if ($stdClass->classId)
        {
            $SQL->where("B.ACADEMIC_ID = ?",$stdClass->classId);
        }
        
        if($stdClass->schoolyearId)
        {
            $SQL->where("B.SCHOOLYEAR_ID = ?",$stdClass->schoolyearId);
        }
        
        $SQL->group("A.ID");
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result ? $result:0; 
           
    }
    
    public static function countTeacherAttendanceType($stdClass){//check
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_staff_attendance'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_schedule'), 'A.STAFF_ID=B.TEACHER_ID', array());
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.ACADEMIC_ID', array());
        
        if(isset($stdClass->campusId)){
            $SQL->where("C.CAMPUS_ID = ?",$stdClass->campusId);    
        }
        
        if(isset($stdClass->gradeId)){
            $SQL->where("C.GRADE_ID = ?",$stdClass->gradeId);   
        }
        
        if(isset($stdClass->teacherId))
            $SQL->where("A.STAFF_ID = ?",$stdClass->teacherId);

        if (isset($stdClass->classId))
            $SQL->where("B.ACADEMIC_ID = ?",$stdClass->classId);
        
        if(isset($stdClass->absentType))
            $SQL->where("A.ABSENT_TYPE = ?",$stdClass->absentType);
        
        if(isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOLYEAR_ID = ?",$stdClass->schoolyearId);
        
        
        $SQL->group("A.ID");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    }
    
    public static function countTeacherDisciplineType($stdClass){//check
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.STUDENT_ID=B.STUDENT', array());
        
        if(isset($stdClass->campusId)){
            $SQL->where("B.CAMPUS = ?",$stdClass->campusId);    
        }
        
        if(isset($stdClass->gradeId)){
            $SQL->where("B.GRADE = ?",$stdClass->gradeId);   
        }
        
        if(isset($stdClass->studentId))
            $SQL->where("A.STUDENT_ID = ?",$stdClass->studentId);

        if (isset($stdClass->classId))
            $SQL->where("B.CLASS = ?",$stdClass->classId);
        
        if(isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOL_YEAR = ?",$stdClass->schoolyearId);
        
        if(isset($stdClass->disciplineType))
            $SQL->where("A.DISCIPLINE_TYPE = ?",$stdClass->disciplineType);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    }
    
    public static function getAttendanceType($stdClass){
        $data = AbsentTypeDBAccess::getAllAbsentType(array('objectType'=>$stdClass->personType,'status'=>$stdClass->status)); 
        return $data;       
    }
    
    public static function getDisciplineType(){
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        $SQL->where("OBJECT_TYPE =?","DISCIPLINE_TYPE_STAFF");
        $SQL->where("PARENT <> 0");
        $SQL->order("ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);   
        return $result;     
    }
    
}

?>