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

    public static function countStudentAttendanceType($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_attendance'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS_ID=B.ID', array());

        if (isset($stdClass->campusId)) {
            $SQL->where("B.CAMPUS_ID = '" . $stdClass->campusId . "'");
        }

        if (isset($stdClass->gradeId)) {
            $SQL->where("B.GRADE_ID = '" . $stdClass->gradeId . "'");
        }

        if (isset($stdClass->personId))
            $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");

        if (isset($stdClass->classId))
            $SQL->where("A.CLASS_ID = '" . $stdClass->classId . "'");

        if (isset($stdClass->absentType))
            $SQL->where("A.ABSENT_TYPE = '" . $stdClass->absentType . "'");

        if (isset($stdClass->schoolyearId))
            $SQL->where("A.SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
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
            $SQL->where("A.STUDENT_ID = ?",$stdClass->studentId);

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
            $SQL->where("A.STUDENT_ID = ?",$stdClass->studentId);

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

    public static function getAttendanceType($stdClass) {
        $data = AbsentTypeDBAccess::getAllAbsentType(array('objectType' => $stdClass->personType, 'status' => $stdClass->status));
        return $data;
    }

    public static function getDisciplineType() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        $SQL->where("OBJECT_TYPE =?","DISCIPLINE_TYPE_STUDENT");
        $SQL->where("PARENT <> 0");
        $SQL->order("ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function getAdvisoryType() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        $SQL->where("OBJECT_TYPE =?","ADVISORY_TYPE");
        $SQL->where("PARENT <> 0");
        $SQL->order("ID ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

}

?>