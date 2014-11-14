<?php

///////////////////////////////////////////////////////////
// Vikensoft UG Partent Nr.....
// @Morng Thou
// 27.02.2011
// 03 Rue des Piblues Bailly Romainvilliers
// @VIK Modify....
// 24.05.2011
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentNoteDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function getAllNotesByStudent($studentId, $academicId, $teacherId) {

        switch (UserAuth::getUserType()) {
            case "STUDENT":
                $studentId = Zend_Registry::get('USER')->ID;
                break;
        }

        $SELECTION_A = array(
            "ID AS STUDENT_ID"
            , "CODE AS STUDENT_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
            , "SMS_SERVICES AS SMS_SERVICES"
            , "MOBIL_PHONE AS MOBIL_PHONE"
        );

        $SELECTION_B = array(
            "ID AS CONTENT_ID"
            , "CONTENT AS CONTENT"
            , "DATE_TIME AS DATE_TIME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_notes'), 'A.ID=B.STUDENT', $SELECTION_B);
        $SQL->where('B.STUDENT = ?', $studentId);
        $SQL->where('B.CLASS = ?', $academicId);

        switch (UserAuth::getUserType()) {
            case "TEACHER":
                $SQL->where('B.TEACHER = ?', $teacherId);
                break;
        }

        $SQL->order('B.DATE_TIME DESC');

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonLoadAllNotes($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $teacherId = Zend_Registry::get('USER')->ID;

        $result = self::getAllNotesByStudent(
                        $studentId
                        , $academicId
                        , $teacherId);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {

                $data[$key]["ID"] = $value->CONTENT_ID;
                $data[$key]["CONTENT"] = $value->CONTENT;
                $data[$key]["DATE_TIME"] = getShowDateTime($value->DATE_TIME);
            }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function jsonAddNote($params) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $content = isset($params["content"]) ? addText($params["content"]) : "";
        $teacherId = Zend_Registry::get('USER')->ID;

        self::addStudentNote(
                $studentId
                , $academicId
                , $content
                , $teacherId
        );

        return array(
            "success" => true
        );
    }

    public static function jsonDeleteNote($Id) {
        self::dbAccess()->delete("t_student_notes", " ID = '" . $Id . "'");
        return array(
            "success" => true
        );
    }

    public static function addStudentNote($studentId, $academicId, $content, $teacherId) {

        $SQL = "INSERT INTO";
        $SQL .= " t_student_notes";
        $SQL .= " SET STUDENT = '" . $studentId . "'";
        $SQL .= " ,CLASS = '" . $academicId . "'";
        $SQL .= " ,TEACHER = '" . $teacherId . "'";
        $SQL .= " ,CONTENT = '" . addText($content) . "'";
        $SQL .= " ,DATE_TIME = '" . getCurrentDBDateTime() . "'";
        return self::dbAccess()->query($SQL);
    }

}

?>