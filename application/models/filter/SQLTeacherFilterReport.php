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
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=A.ACADEMIC_ID', array());
        
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
    
    public static function countTeacherAttendanceTypeByTeacherId($Id){
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_staff_attendance'), array("C" => "COUNT(*)"));
        $SQL->where("A.STAFF_ID=?",$Id);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;   
    }
    
    
    public static function findObjectTgrade($Id)
    {
        return AcademicDBAccess::sqlGradeFromId($Id);
        
    }
    
    public static function sqlTeacherAttendance($teacherId,$objectTgrade,$absenceType){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_staff_attendance'), array("*"));
        $SQL->where("A.STAFF_ID = ?",$teacherId);
        $SQL->where("A.ABSENT_TYPE = ?",$absenceType);
        if($objectTgrade){
            $SQL->where("A.START_DATE >= ?",$objectTgrade->SCHOOLYEAR_START);   
            $SQL->where("A.END_DATE <= ?",$objectTgrade->SCHOOLYEAR_END);  
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
   
    
    public static function countTeacherAttendanceType($stdClass){
        
        $count=0;
        switch ($stdClass->objectType) {
            case 'CAMPUS':
                $objectTgrade = self::findObjectTgrade($stdClass->gradeId);
                break;
            case 'GRADE':
                $objectTgrade = self::findObjectTgrade($stdClass->classId);
                break;
        }
        
        if(isset($stdClass->personId))
        {
            
            $count=$count+self::countTeacherAttendanceTypeByTeacherId($stdClass->teacherId);       
        }else{//
            $teacherObject = self::SQLAssignedTeacher($stdClass);
            if($teacherObject)
            {
                foreach($teacherObject as $value){
                    $TeacherAttendance = self::sqlTeacherAttendance($value->ID,$objectTgrade,$stdClass->absentType);
                    if($TeacherAttendance) {
                        foreach($TeacherAttendance as $attendanceObject){
                            $stdClass->teacherId = $value->ID;
                            if($attendanceObject->ACTION_TYPE==2){
                                $days = explode(",",$attendanceObject->CAL_DATE);
                                foreach($days as $day){
                                    $stdClass->day=$day;
                                    $check = self::checkTeacherAttendanceInLevelGrade($stdClass);
                                    if($check)
                                        $count = $count+1;  
                                    //error_log($count);       
                                }
                                             
                            }else{////check more...............
                                $stdClass->day="";
                                $stdClass->start_time=$attendanceObject->START_TIME;
                                $stdClass->end_time=$attendanceObject->END_TIME;
                                $check = self::checkTeacherAttendanceInLevelGrade($stdClass);
                                if($check)
                                    $count = $count+1;  
                                //error_log("daily:".$count);   
                            }       
                        }    
                    }                 
                }
            }    
        }
        
        /*$SQL = self::dbAccess()->select();
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
        $result = self::dbAccess()->fetchRow($SQL);*/
        return $count;   
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