<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SubjectStudentDBAccess extends SubjectDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new SubjectStudentDBAccess();
        }
        return $me;
    }

    public static function checkStudentBySubject($subjectId, $studentId, $classId, $term, $type) {

        $SQL = "SELECT DISTINCT COUNT(*) AS C";
        $SQL .= " FROM t_student_subject";
        $SQL .= " WHERE 1=1";
        if ($subjectId)
            $SQL .= " AND SUBJECT_ID = '" . $subjectId . "'";
        if ($studentId)
            $SQL .= " AND STUDENT_ID = '" . $studentId . "'";
        if ($classId)
            $SQL .= " AND CLASS_ID = '" . $classId . "'";
        if ($term)
            $SQL .= " AND TERM = '" . $term . "'";
        if ($type)
            $SQL .= " AND TYPE = '" . $type . "'";

        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonListStudentsBySubject($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";

        $result = StudentAcademicDBAccess::getQueryStudentEnrollment($classId, $globalSearch, false);

        $data = array();

        if ($result) {

            $i = 0;
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["CODE"] = $value->STUDENT_CODE;

                $CHECK_FIRST_SEMESTER = self::checkStudentBySubject(
                                $subjectId
                                , $value->STUDENT_ID
                                , $classId
                                , 'FIRST_SEMESTER'
                                , $type
                );
                
                $CHECK_SECOND_SEMESTER = self::checkStudentBySubject(
                                $subjectId
                                , $value->STUDENT_ID
                                , $classId
                                , 'SECOND_SEMESTER'
                                , $type
                );
                
                $data[$i]["FIRST_SEMESTER"] = $CHECK_FIRST_SEMESTER?1:0;
                $data[$i]["SECOND_SEMESTER"] = $CHECK_SECOND_SEMESTER?1:0;
                
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => $data
            , "rows" => $a
        );
    }

    public static function actionStudentSubject($params) {

        $SAVEDATA = array();

        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $term = isset($params["field"]) ? addText($params["field"]) : "";
        $classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $condition = array(
            'CLASS_ID = ? ' => $classId
            , 'STUDENT_ID = ? ' => $studentId
            , 'SUBJECT_ID = ? ' => $subjectId
            , 'TYPE = ? ' => $type
            , 'TERM = ? ' => $term
        );
        self::dbAccess()->delete('t_student_subject', $condition);

        if ($newValue) {
            $facette = AcademicDBAccess::findGradeFromId($classId);
            $SAVEDATA["CLASS_ID"] = $classId;
            $SAVEDATA["SUBJECT_ID"] = $subjectId;
            $SAVEDATA["STUDENT_ID"] = $studentId;
            $SAVEDATA["TERM"] = $term;
            $SAVEDATA["TYPE"] = $type;
            $SAVEDATA["CAMPUS_ID"] = $facette->CAMPUS_ID;
            $SAVEDATA["GRADE_ID"] = $facette->GRADE_ID;
            self::dbAccess()->insert('t_student_subject', $SAVEDATA);
        }

        return array(
            "success" => true
        );
    }

}

?>