<?php

///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 16.06.2013
//////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/extraclass/ExtraClassDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';

require_once setUserLoacalization();

class StudentExtraClassDBAccess extends ExtraClassDBAccess {

    static function getInstance() {

        return new StudentExtraClassDBAccess();
    }

    public static function findStudentExtraClassFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_extraclass", array('*'));
        $SQL->where("ID = ?",$Id);
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function findStudentExtraClassByStudentId($studentId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_extraclass", array('*'));
        $SQL->where("STUDENT = ?",$studentId);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function sqlStudentExtraClass($globalSearch, $extraClassId, $studentId) {

        //$EXTRACLASS_OBJECT = self::findExtraClassFromId($extraClassId);

        $SELECT_A = array(
            'ID'
            , 'EXTRACLASS'
        );

        $SELECT_B = array(
            'ID AS STUDENT_ID'
            , 'CODE'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'GENDER'
        );

        $SELECT_C = array(
            'CLASS'
        );
        
        $SELECT_D = array(
            'NAME AS CLASS_NAME'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_extraclass'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student'), 'A.STUDENT=B.ID', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'B.ID=C.STUDENT', $SELECT_C);
        $SQL->joinLeft(array('D' => 't_grade'), 'C.CLASS=D.ID', $SELECT_D);
        $SQL->where('A.EXTRACLASS = ?', $extraClassId);

        if ($globalSearch) {
            $SEARCH = " ((B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        if ($studentId)
            $SQL->where("B.STUDENT='" . $studentId . "'");

        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY B.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY B.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY B.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL);

        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public static function jsonStudentExtraClass($params, $isJson = true) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $extraClassId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        //$facette = self::findExtraClasslFromId($extraClassId);
        /*if ($facette) {
            $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
            $SAVEDATA["TERM"] = $facette->TERM;
            $SAVEDATA["LEVEL"] = $facette->LEVEL;
            $WHERE = self::dbAccess()->quoteInto("EXTRACLASS = ?", $extraClassId);
            self::dbAccess()->update('t_student_extraclass', $SAVEDATA, $WHERE);
        }*/

        $resultRows = self::sqlStudentExtraClass($globalSearch, $extraClassId, $studentId);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["CLASS_NAME"] = setShowText($value->CLASS_NAME);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson == true) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public static function actionRemoveStudentExtraClass($params) {
        $id = isset($params["id"]) ? addText($params["id"]) : "0";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "0";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";

        if ($field == "CHECKED") {
            if ($newValue) {
                if ($objectId)
                    self::dbAccess()->delete('t_student_extraclass', array("ID=" . $id . ""));
            }
        }

        return array(
            "success" => true
        );
    }

    public static function findStudentFromId($Id) {
        $SQL = self::dbAccess()->select()
            ->from("t_student", array('*'))
            ->where("ID = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function jsonListStudentInSchool($params) {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $extraClassId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SELECT_A = array(
            'CODE'
            , 'ID AS STUDENT_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'GENDER'
            , 'DATE_BIRTH'
        );

        $SELECT_B = array(
            'GRADE'
            , 'CLASS'
        );

        $SELECT_C = array(
            'NAME AS CLASS_NAME'
        );


        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.ID=B.STUDENT', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_grade'), 'B.CLASS=C.ID', $SELECT_C);

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY A.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY A.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY A.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                if (!self::checkStudentInExtraClassEducation($value->STUDENT_ID, $extraClassId)) {

                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                    $data[$i]["CURRENT_CLASS"] = setShowText($value->CLASS_NAME);

                    $i++;
                }
            }
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

    public static function checkStudentInExtraClassEducation($studentId, $extraClassId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_extraclass", array("C" => "COUNT(*)"));
        if ($studentId)
            $SQL->where("STUDENT = ?",$studentId);
        if ($extraClassId)
            $SQL->where("EXTRACLASS = '" . $extraClassId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function actionStudentToExtraClass($params) {

        $SAVEDATA = array();

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $extraClassId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";

        $classObject = ExtraClassDBAccess::findExtraClassFromId($extraClassId);
        $LEVEL_OBJECT = ExtraClassDBAccess::findExtraClassFromId($classObject->PARENT);
        $TERM_OBJECT = ExtraClassDBAccess::findExtraClassFromId($LEVEL_OBJECT->PARENT);
        $PROGRAM_OBJECT = ExtraClassDBAccess::findExtraClassFromId($TERM_OBJECT->PARENT);

        if ($field == "ASSIGNED") {
            if ($newValue) {
                $SAVEDATA["STUDENT"] = $studentId;
                $SAVEDATA["EXTRACLASS"] = $extraClassId;
                $SAVEDATA["PROGRAM"] = $PROGRAM_OBJECT->ID;
                $SAVEDATA["TERM"] = $TERM_OBJECT->ID;
                $SAVEDATA["LEVEL"] = $LEVEL_OBJECT->ID;

                //if ($STUDENT_COUNT <= $classObject->MAX_STUDENTS) {
                self::dbAccess()->insert('t_student_extraclass', $SAVEDATA);
                //}
            }
        }

        $o = array(
            "success" => true
        );
        return $o;
    }

}

?>