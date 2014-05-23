<?php

//////////////////////////////////////////////////////////////////////////
//@Sea Peng
//Date: 22.11.2013
//////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once setUserLoacalization();

class StudentAdvisoryDBAccess {

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

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function findObjectFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_advisory'), array('*'));
        $SQL->where("A.ID = ?",$Id);
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getObjectDataFromId($Id) {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["ADVISORY_TYPE"] = setShowText($result->ADVISORY_TYPE);
            $data["NAME"] = setShowText($result->NAME);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }

    public static function jsonLoadAdvisory($Id) {

        $result = self::findObjectFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getObjectDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function sqlSearchStudentAdvisory($params) {

        $startDate = isset($params["START_DATE"]) ? addText($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? addText($params["END_DATE"]) : "";

        $code = isset($params["CODE"]) ? addText($params["CODE"]) : "";
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : "";
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : "";

        $name = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $advisoryId = isset($params["ADVISORY_TYPE"]) ? addText($params["ADVISORY_TYPE"]) : "";
        
        $campusId = isset($params["campusId"]) ? addText($params["campusId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_advisory'), array("*", "CONCAT(A.NAME,' ( Ref:',A.ID,')') AS ADVISORY_NAME"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.ADVISORY_TYPE=B.ID', array('NAME AS ADVISORY_TYPE'));
        $SQL->joinLeft(array('C' => 't_members'), 'A.ADVISED_BY=C.ID', array('FIRSTNAME AS STAFF_FIRSTNAME', 'LASTNAME AS STAFF_LASTNAME'));

        $SQL->joinLeft(array('D' => 't_student_advisory'), 'A.ID=D.ADVISORY_ID', array('STUDENT_ID'));
        $SQL->joinLeft(array('E' => 't_student'), 'D.STUDENT_ID=E.ID', array('FIRSTNAME AS STUDENT_FIRSTNAME', 'LASTNAME AS STUDENT_LASTNAME'));

        if ($startDate and $endDate)
            $SQL->where("A.ADVISED_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'");

        if ($code)
            $SQL->where("E.CODE LIKE '" . $code . "%'");

        if ($firstname)
            $SQL->where("E.FIRSTNAME LIKE '" . $firstname . "%'");

        if ($lastname)
            $SQL->where("E.LASTNAME LIKE '" . $lastname . "%'");

        if ($name)
            $SQL->where("A.NAME = " . $name . "");

        if ($advisoryId)
            $SQL->where("A.ADVISORY_TYPE = " . $advisoryId . "");
            
        if($campusId && $schoolyearId){
            $objectSchoolyear = AcademicDBAccess::findCampusSchoolyear($campusId, $schoolyearId);
            if($objectSchoolyear){
                $SQL->where("A.ADVISED_DATE >= '" . $objectSchoolyear->SCHOOLYEAR_START . "'");
                $SQL->where("A.ADVISED_DATE <= '" . $objectSchoolyear->SCHOOLYEAR_END . "'");
            }
        }

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonSearchStudentAdvisory($params, $isJson = true) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $data = array();
        $i = 0;

        $result = self::sqlSearchStudentAdvisory($params);
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["ADVISORY_NAME"] = $value->ADVISORY_NAME;
                $data[$i]["ADVISORY_TYPE"] = $value->ADVISORY_TYPE;
                $data[$i]["ADVISED_DATE"] = getShowDate($value->ADVISED_DATE);

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["ADVISED_BY"] = setShowText($value->STAFF_LASTNAME) . " " . setShowText($value->STAFF_FIRSTNAME);
                    $data[$i]["STUDENT"] = setShowText($value->STUDENT_LASTNAME) . " " . setShowText($value->STUDENT_FIRSTNAME);
                } else {
                    $data[$i]["ADVISED_BY"] = setShowText($value->STAFF_FIRSTNAME) . " " . setShowText($value->STAFF_LASTNAME);
                    $data[$i]["STUDENT"] = setShowText($value->STUDENT_FIRSTNAME) . " " . setShowText($value->STUDENT_LASTNAME);
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
       
        $dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
        
         if ($isJson)
        {
            return $dataforjson;
        }
        else
        {
            return $data;
        }
    }

    public static function sqlStudentAdvisory($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_advisory'), array('*'));
        $SQL->joinLeft(array('B' => 't_student'), 'A.STUDENT_ID=B.ID', array('*'));

        $SQL->where("A.ADVISORY_ID='" . $objectId . "'");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonLoadStudentAdvisory($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $data = array();
        $i = 0;

        $result = self::sqlStudentAdvisory($params);
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                ////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

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
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function jsonSaveAdvisory($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';

        $SAVEDATA = array();

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);

        if (isset($params["ADVISORY_TYPE"]))
            $SAVEDATA["ADVISORY_TYPE"] = addText($params["ADVISORY_TYPE"]);

        if ($objectId == "new") {
            $SAVEDATA['ADVISED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['ADVISED_BY'] = Zend_Registry::get('USER')->ID;
            self::dbAccess()->insert('t_advisory', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_advisory', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function jsonRemoveAdvisory($Id) {
        self::dbAccess()->delete('t_advisory', array("ID='" . $Id . "'"));
        self::dbAccess()->delete('t_student_advisory', array("ADVISORY_ID='" . $Id . "'"));

        return array(
            "success" => true
        );
    }

    public static function jsonActionAddStudentToAdvisory($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $selectedCount = 0;

        if ($selectionIds) {
            $selectedStudents = explode(",", $selectionIds);
            if ($selectedStudents) {
                foreach ($selectedStudents as $studentId) {
                    $SAVEDATA['ADVISORY_ID'] = $objectId;
                    $SAVEDATA['STUDENT_ID'] = $studentId;

                    self::dbAccess()->insert('t_student_advisory', $SAVEDATA);
                    ++$selectedCount;
                }
            }
        }

        return array(
            "success" => true
            , 'selectedCount' => $selectedCount
        );
    }

    public function jsonRemoveStudentAdvisory($params) {

        $advisoryId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $studentId = isset($params["removeId"]) ? addText($params["removeId"]) : '';

        self::dbAccess()->delete('t_student_advisory', array("ADVISORY_ID = " . $advisoryId . "", "STUDENT_ID = '" . $studentId . "'"));

        return array(
            "success" => true
        );
    }

    public static function jsonLoadAllActiveStudents($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $data = array();
        $i = 0;

        $result = StudentDBAccess::sqlAllActiveStudents($params);
        if ($result) {
            foreach ($result as $value) {

                $studentAssigned = self::checkAssignedStudentAdvisory($value->ID, $objectId);
                if (!$studentAssigned) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

                    ////////////////////////////////////////////////////////////
                    //Status of student...
                    ////////////////////////////////////////////////////////////
                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
                    $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                    $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                    $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

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

    public static function checkAssignedStudentAdvisory($studentId, $advisoryId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_advisory", array("C" => "COUNT(*)"));
        $SQL->where("ADVISORY_ID = '" . $advisoryId . "'");
        $SQL->where("STUDENT_ID = ?",$studentId);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>
