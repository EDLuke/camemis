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
    
    public static function getAssignedTeacher($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_schedule'), array('TEACHER_ID AS TEACHER_ID'));
        $SQL->joinLeft(array('B' => 't_staff'), 'B.ID=A.TEACHER_ID', array());
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=A.ACADEMIC_ID', array('SCHOOLYEAR_START','SCHOOLYEAR_END'));
        
        if(isset($stdClass->campusId))
        {
            $SQL->where("C.CAMPUS_ID = ?",$stdClass->campusId);    
        }
        
        if(isset($stdClass->gradeId))
        {
            $SQL->where("C.GRADE_ID = ?",$stdClass->gradeId);   
        }
        
        if (isset($stdClass->classId))
        {
            $SQL->where("A.ACADEMIC_ID = ?",$stdClass->classId);
        }
        
        if(isset($stdClass->schoolyearId))
        {
            $SQL->where("A.SCHOOLYEAR_ID = ?",$stdClass->schoolyearId);
        }
        if (isset($stdClass->gender))
            $SQL->where("B.GENDER = ?",$stdClass->gender);
        if (isset($stdClass->status))
            $SQL->where("B.STATUS = ?",$stdClass->status);
        if (isset($stdClass->religion))
            $SQL->where("B.RELIGION = ?",$stdClass->religion);
        if(isset($stdClass->ethnicity))
            $SQL->where("B.ETHNIC = ?",$stdClass->ethnicity);
        if(isset($stdClass->nationality))
            $SQL->where("B.NATIONALITY = ?",$stdClass->nationality);
        if (isset($stdClass->country_province))
            $SQL->where("B.COUNTRY_PROVINCE = ?",$stdClass->country_province);
        $SQL->group("A.TEACHER_ID");
        //error_log($SQL->__toString());
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result?$result:0;   
           
    }
    
    
    public static function SQLAssignedTeacher($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_staff'), array("*"));
        $SQL->joinLeft(array('B' => 't_schedule'), 'A.ID=B.TEACHER_ID', array());
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.ACADEMIC_ID', array("NAME AS CLASS","TITLE AS TITLE"));
        
        if(isset($stdClass->campusId))
        {
            $SQL->where("C.CAMPUS_ID = ?",$stdClass->campusId);    
        }
        
        if(isset($stdClass->gradeId))
        {
            $SQL->where("C.GRADE_ID = ?",$stdClass->gradeId);   
        }
        
        if (isset($stdClass->classId))
        {
            $SQL->where("B.ACADEMIC_ID = ?",$stdClass->classId);
        }
        
        if(isset($stdClass->schoolyearId))
        {
            $SQL->where("B.SCHOOLYEAR_ID = ?",$stdClass->schoolyearId);
        }
        
        $SQL->group("A.ID");
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result ? $result:0; 
           
    }
    
    
    public static function findObjectTgrade($Id)
    {
        return AcademicDBAccess::sqlGradeFromId($Id);
        
    }
    
    public static function sqlTeacherAttendance($teacherId,$schoolyearStart,$schoolyearEnd,$absenceType){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_staff_attendance'), array("*"));
        $SQL->where("A.STAFF_ID = ?",$teacherId);
        $SQL->where("A.ABSENT_TYPE = ?",$absenceType);
        if($schoolyearStart && $schoolyearEnd){
            $SQL->where("A.START_DATE >= ?",$schoolyearStart);   
            $SQL->where("A.END_DATE <= ?",$schoolyearEnd);  
        }
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }
    
    
    public static function checkTeacherAttendanceInLevelGrade($stdClass){
      
        $SQL = self::dbAccess()->select();    
        $SQL->from(array('A' =>'t_schedule'),  array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_grade'), 'A.ACADEMIC_ID=B.ID', array());
        if(isset($stdClass->teacherId))
        $SQL->where("A.TEACHER_ID = ?",$stdClass->teacherId); 
        if($stdClass->day)
        $SQL->where("A.SHORTDAY = ?",getWEEKDAY($stdClass->day));
        
        if(isset($stdClass->campusId))
        $SQL->where("B.CAMPUS_ID = ?",$stdClass->campusId);
        
        if(isset($stdClass->gradeId))
        $SQL->where("B.GRADE_ID = ?",$stdClass->gradeId);
        
        if(isset($stdClass->classId))
        $SQL->where("A.ACADEMIC_ID = ?",$stdClass->classId);
        
        if(isset($stdClass->start_time))
        $SQL->where("A.START_TIME <= ?",$stdClass->start_time);
        
        if(isset($stdClass->end_time))
        $SQL->Orwhere("A.END_TIME >= ?",$stdClass->end_time);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    
    }
   
    public static function getAgeStaffEnroll($stdClass){   
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_schedule'),     array('TEACHER_ID AS TEACHER_ID'));
        $SQL->joinLeft(array('B' => 't_staff'), 'B.ID=A.TEACHER_ID', array("DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(DATE_BIRTH)), '%Y')+0 AS AGE","ID AS STAFF_ID"));
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=A.ACADEMIC_ID', array());
         if(isset($stdClass->campusId))
        $SQL->where("C.CAMPUS_ID = ?",$stdClass->campusId);
        
        if(isset($stdClass->gradeId))
        $SQL->where("C.GRADE_ID = ?",$stdClass->gradeId);
        
        if(isset($stdClass->classId))
        $SQL->where("A.ACADEMIC_ID = ?",$stdClass->classId);
        
        if (isset($stdClass->schoolyearId))
            $SQL->where("A.SCHOOLYEAR_ID = ?",$stdClass->schoolyearId);

        //error_log($SQL->__toString());
        $SQL->group('TEACHER_ID');
        $SQL->order(array('AGE ASC'));
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;    
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
    
    public static function getTeacherInfo($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_person_infos'), array("*"));
        $SQL->joinRight(array('B' => 't_schedule'), 'A.USER_ID=B.TEACHER_ID', array());
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.ACADEMIC_ID', array());
        
        $SQL->where("A.USER_TYPE = ?","STAFF");    
        if ($stdClass->campusId)
            $SQL->where("C.CAMPUS_ID = ?",$stdClass->campusId);     
        if (isset($stdClass->gradeId)) 
            $SQL->where("B.GRADE_ID = ?",$stdClass->gradeId);
        if (isset($stdClass->classId))
            $SQL->where("B.ACADEMIC_ID = ?",$stdClass->classId);
        if (isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOLYEAR_ID = ?",$stdClass->schoolyearId);
        if($stdClass->objecttype)
            $SQL->where("A.OBJECT_TYPE = ?",strtoupper($stdClass->objecttype));   
        if($stdClass->type){
            switch(strtoupper($stdClass->type)){
                case'QUALIFICATION_DEGREE_TYPE':
                    $SQL->where("A.QUALIFICATION_DEGREE = ?",$stdClass->camemisType);
                    $SQL->group("A.USER_ID");
                    $SQL->group("A.QUALIFICATION_DEGREE");
                    break;
                case'MAJOR_TYPE':
                    $SQL->where("A.MAJOR = ?",$stdClass->camemisType);
                    $SQL->group("A.USER_ID");
                    $SQL->group("A.MAJOR");
                    break;
                case'RELATIONSHIP_TYPE':
                    $SQL->where("A.RELATIONSHIP = ?",$stdClass->camemisType);
                    $SQL->group("B.TEACHER_ID");
                    break;
                case'ORGANIZATION_TYPE':
                    $SQL->where("A.ORGANIZATION_TYPE = ?",$stdClass->camemisType);
                    $SQL->group("A.USER_ID");
                    $SQL->group("A.ORGANIZATION_TYPE");
                    break;
                case'EMERGENCY_CONTACT_TYPE':
                    $SQL->where("A.EMERGENCY_CONTACT = ?",$stdClass->camemisType);
                    $SQL->group("B.TEACHER_ID");
                    break;        
            }
        }  
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;    
    }
    
}

?>