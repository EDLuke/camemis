<?php

///////////////////////////////////////////////////////////
// @sor veasna
// 16/05/2014
//////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/' . Zend_Registry::get('MODUL_API_PATH') . '/student/StudentAttendanceDBAccess.php';

class SQLStudentFilterReport {

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getStudentAttendanceType($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_attendance'), array("*"));
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS_ID=B.ID', array());
        
        if (isset($stdClass->campusId)) {
            $SQL->where("B.CAMPUS_ID = '" . $stdClass->campusId . "'");
        }

        if (isset($stdClass->gradeId)) {
            $SQL->where("B.GRADE_ID = '" . $stdClass->gradeId . "'");
        }

        if (isset($stdClass->personId))
            $SQL->where("A.STUDENT_ID = '" . $stdClass->personId . "'");

        if (isset($stdClass->classId))
            $SQL->where("A.CLASS_ID = '" . $stdClass->classId . "'");

        if (isset($stdClass->absentType))
            $SQL->where("A.ABSENT_TYPE = '" . $stdClass->absentType . "'");

        if (isset($stdClass->schoolyearId))
            $SQL->where("A.SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function countStudentDisciplineType($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.STUDENT_ID=B.STUDENT', array());

        if (isset($stdClass->campusId)) {
            $SQL->where("B.CAMPUS = ?",$stdClass->campusId);
        }

        if (isset($stdClass->gradeId)) {
            $SQL->where("B.GRADE = ?",$stdClass->gradeId);
        }

        if (isset($stdClass->personId))
            $SQL->where("A.STUDENT_ID = ?",$stdClass->personId);

        if (isset($stdClass->classId))
            $SQL->where("B.CLASS = ?",$stdClass->classId);

        if (isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOL_YEAR =?",$stdClass->schoolyearId);

        if (isset($stdClass->disciplineType))
            $SQL->where("A.DISCIPLINE_TYPE =?",$stdClass->disciplineType);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function countStudentAdvisoryType($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_advisory'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_advisory'), 'A.ADVISORY_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'A.STUDENT_ID=C.STUDENT', array());

        if (isset($stdClass->campusId)) {
            $SQL->where("C.CAMPUS = ?",$stdClass->campusId);
        }

        if (isset($stdClass->gradeId)) {
            $SQL->where("C.GRADE = ?",$stdClass->gradeId);
        }

        if (isset($stdClass->personId))
            $SQL->where("A.STUDENT_ID = ?",$stdClass->personId);

        if (isset($stdClass->classId))
            $SQL->where("C.CLASS = ?",$stdClass->classId);

        if (isset($stdClass->schoolyearId))
            $SQL->where("C.SCHOOL_YEAR = ?",$stdClass->schoolyearId);

        if (isset($stdClass->advisoryType))
            $SQL->where("B.ADVISORY_TYPE = ?",$stdClass->advisoryType);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getCountStudent($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_student'), 'A.STUDENT=B.ID', array());
        if ($stdClass->campusId)
            $SQL->where("A.CAMPUS = ?",$stdClass->campusId);     
        if (isset($stdClass->gradeId)) 
            $SQL->where("A.GRADE = ?",$stdClass->gradeId);
        if (isset($stdClass->classId))
            $SQL->where("A.CLASS = ?",$stdClass->classId);
        if (isset($stdClass->schoolyearId))
            $SQL->where("A.SCHOOL_YEAR = ?",$stdClass->schoolyearId);  
        if (isset($stdClass->gender))
            $SQL->where("B.GENDER = ?",$stdClass->gender);
        if(isset($stdClass->status))
            $SQL->where("A.STATUS = ?",$stdClass->status);
        if(isset($stdClass->religion))
            $SQL->where("B.RELIGION = ?",$stdClass->religion);
        if(isset($stdClass->SMS))
            $SQL->where("B.SMS_SERVICES = ?",$stdClass->SMS);
        if(isset($stdClass->nationality))
            $SQL->where("B.NATIONALITY = ?",$stdClass->nationality);
        if(isset($stdClass->ethnicity))
            $SQL->where("B.ETHNIC = ?",$stdClass->ethnicity);     
        if (isset($stdClass->country_province))
            $SQL->where("B.COUNTRY_PROVINCE = ?",$stdClass->country_province);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result?$result->C:0;    
    }
    
    /*public static function getStudentInfo($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_person_infos'), array("*"));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.USER_ID=B.STUDENT', array());
        
        $SQL->where("A.USER_TYPE = ?",'STUDENT'); 
           
        if ($stdClass->campusId)
            $SQL->where("B.CAMPUS = ?",$stdClass->campusId);     
        if (isset($stdClass->gradeId)) 
            $SQL->where("B.GRADE = ?",$stdClass->gradeId);
        if (isset($stdClass->classId))
            $SQL->where("B.CLASS = ?",$stdClass->classId);
        if (isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOL_YEAR = ?",$stdClass->schoolyearId);
        if($stdClass->objecttype)
            $SQL->where("A.OBJECT_TYPE = ?",strtoupper($stdClass->objecttype));   
        if($stdClass->type){
            switch(strtoupper($stdClass->type)){
                case'QUALIFICATION_DEGREE':
                    $SQL->where("A.QUALIFICATION_DEGREE = ?",$stdClass->camemisType);
                    $SQL->group("A.USER_ID");
                    $SQL->group("A.QUALIFICATION_DEGREE");
                    break;
                case'MAJOR':
                    $SQL->where("A.MAJOR = ?",$stdClass->camemisType);
                    $SQL->group("A.USER_ID");
                    $SQL->group("A.MAJOR");
                    break;
                case'RELATIONSHIP':
                    $SQL->where("A.RELATIONSHIP = ?",$stdClass->camemisType);
                    break;
                        
            }
        }  
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;    
    }*/
    
    ////test new
    public static function getStudentInfo($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_person_infos'), array("*"));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.USER_ID=B.STUDENT', array());
        
        $SQL->where("A.USER_TYPE = ?","STUDENT");    
        if ($stdClass->campusId)
            $SQL->where("B.CAMPUS = ?",$stdClass->campusId);     
        if (isset($stdClass->gradeId)) 
            $SQL->where("B.GRADE = ?",$stdClass->gradeId);
        if (isset($stdClass->classId))
            $SQL->where("B.CLASS = ?",$stdClass->classId);
        if (isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOL_YEAR = ?",$stdClass->schoolyearId);
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
                    break;
                case'EMERGENCY_CONTACT_TYPE':
                    $SQL->where("A.EMERGENCY_CONTACT = ?",$stdClass->camemisType);
                    break;
                        
            }
        }  
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;    
    }
    //
    
    //@Visal
    public static function getCountStudentAdditional($stdClass){
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' =>'t_person_description_item'), array('*'));
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'B.STUDENT=A.PERSON_ID', array());
        if ($stdClass->campusId)
            $SQL->where("B.CAMPUS = ?",$stdClass->campusId);     
        if (isset($stdClass->gradeId)) 
            $SQL->where("B.GRADE = ?",$stdClass->gradeId);
        if (isset($stdClass->classId))
            $SQL->where("B.CLASS = ?",$stdClass->classId);
        if (isset($stdClass->schoolyearId))
            $SQL->where("B.SCHOOL_YEAR = ?",$stdClass->schoolyearId);
        if (isset($stdClass->dataType))
            $SQL->where("A.ITEM = ?",$stdClass->dataType);
            //$SQL->group("A.ITEM");
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result?$result:0;
    }
    
    
    public static function getAgeStudentEnroll($stdClass){
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear'), array());
        $SQL->joinLeft(array('B' => 't_student'), 'A.STUDENT=B.ID', array("DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(DATE_BIRTH)), '%Y')+0 AS AGE","ID AS STUDENT_ID"));
        if (isset($stdClass->campusId))
            $SQL->where("A.CAMPUS = ?",$stdClass->campusId);     
        if (isset($stdClass->gradeId)) 
            $SQL->where("A.GRADE = ?",$stdClass->gradeId);
        if (isset($stdClass->classId))
            $SQL->where("A.CLASS = ?",$stdClass->classId);
        if (isset($stdClass->schoolyearId))
            $SQL->where("A.SCHOOL_YEAR = ?",$stdClass->schoolyearId);
       
        //error_log($SQL->__toString());
        //$SQL->group('AGE');
        $SQL->order(array('AGE ASC'));
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;    
    }
    
    public static function getAllLocation(){
        
        $SQL = self::dbAccess()->select();
        $SQL->from("t_location", array("*"));
        $SQL->where("PARENT=?",0);
        $SQL->order("NAME");
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }
    
    public static function getAllAdditional(){
        
        $SQL = self::dbAccess()->select();
        $SQL->from("t_personal_description", array("*"));
        $SQL->where("PARENT=?",0);
        $SQL->where("PERSON=?","STUDENT");
        $SQL->order("NAME");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL);
        return $result;
    }
    
    public static function getAllAdditionalChild($parentID){
        
        $SQL = self::dbAccess()->select();
        $SQL->from("t_personal_description", array("*"));
        $SQL->where("PARENT=?",$parentID);
        $SQL->where("PERSON=?","STUDENT");
        $SQL->order("NAME");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL);
        return $result;
    }
    
    public static function getStudentStatus($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_status'), array("*"));
        $SQL->join(array('B' => 't_student_schoolyear'), 'A.STUDENT=B.STUDENT', array());
        if (isset($stdClass->objectGrade))
        {
            $SQL->where("A.START_DATE >= '" . $stdClass->objectGrade->SCHOOLYEAR_START . "' AND A.END_DATE <= '" . $stdClass->objectGrade->SCHOOLYEAR_END . "' OR A.START_DATE >= '" . $stdClass->objectGrade->SCHOOLYEAR_START . "' AND A.END_DATE >= '" . $stdClass->objectGrade->SCHOOLYEAR_END . "'");
            
        }
        if (isset($stdClass->campusId)) {
            $SQL->where("B.CAMPUS = '" . $stdClass->campusId . "'");
        }

        if (isset($stdClass->gradeId)) {
            $SQL->where("B.GRADE = '" . $stdClass->gradeId . "'");
        }
        
        if (isset($stdClass->classId))
            $SQL->where("B.CLASS = '" . $stdClass->classId . "'");

        if (isset($stdClass->statusType))
            $SQL->where("A.STATUS_ID = '" . $stdClass->statusType . "'");
        $SQL->group("A.STUDENT");
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }
    
    public static function getStudentRgistration($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear'), array("*"));
        $SQL->join(array('B' => 't_student'), 'A.STUDENT=B.ID', array());
        
        if (isset($stdClass->campusId)) {
            $SQL->where("A.CAMPUS = '" . $stdClass->campusId . "'");
        }

        if (isset($stdClass->gradeId)) {
            $SQL->where("A.GRADE = '" . $stdClass->gradeId . "'");
        }
        
        if (isset($stdClass->gender)) {
            $SQL->where("B.GENDER = '" . $stdClass->gender . "'");
        }
        
        if (isset($stdClass->classId))
            $SQL->where("A.CLASS = '" . $stdClass->classId . "'");
        if(isset($stdClass->month)){
            $SQL->where("MONTH(A.CREATED_DATE) = '" . getMonthNumberFromMonthYear($stdClass->month) . "'"); 
            $SQL->where("YEAR(A.CREATED_DATE) = '" . getYearFromMonthYear($stdClass->month) . "'");   
        }
        
        $SQL->group("A.STUDENT");
        error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }
}

?>