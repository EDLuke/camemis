<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.02.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffDBAccess.php"; //@Man
require_once setUserLoacalization();

class DisciplineDBAccess extends StudentDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDisciplineFromId($Id, $personType = false) {

        $result = $this->findDisciplineFromId($Id);
        if ($personType == "staff") //@Man
            $DISCIPLINE_TYPE = "DISCIPLINE_TYPE_STAFF";
        else
            $DISCIPLINE_TYPE = "DISCIPLINE_TYPE_STUDENT";

        $data = array();

        if ($result) {

            $data["ID"] = $result->ID;
            $data["STATUS"] = $result->STATUS;
            $data["INFRACTION_DATE"] = getShowDate($result->INFRACTION_DATE);
            $data[$DISCIPLINE_TYPE] = $result->DISCIPLINE_TYPE;
            $data["PUNISHMENT_TYPE"] = $result->PUNISHMENT_TYPE;
            $data["INFRACTION_DATE"] = getShowDate($result->INFRACTION_DATE);
            $data["COMMENT"] = $result->COMMENT;

            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
        }

        return $data;
    }

    public function findDisciplineFromId($Id) {

        $SQL = self::dbAccess()->select()
                ->from("t_discipline", array('*'))
                ->where("ID = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonShowAllStudents($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $personType = isset($params["personType"]) ? $params["personType"] : ""; //@Man

        $data = array();
        $i = 0;

        if ($personType == "STAFF") {
            $DB_STAFF = StaffDBAccess::getInstance();
            $result = $DB_STAFF->queryAllStaffs($params);
            $chooseId = "STAFF_ID";
        } else {
            $result = StudentSearchDBAccess::queryAllStudents($params);
            $chooseId = "STUDENT_ID";
        }

        if ($result) {
            foreach ($result as $value) {

                $data[$i]['ID'] = $value->ID;
                $data[$i]['CODE'] = $value->CODE;

                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["STATUS"] = $value->STATUS;
                if ($personType == "staff")
                    $STATUS_DATA = StaffStatusDBAccess::getCurrentStaffStatus($value->$chooseId);
                else
                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->$chooseId);
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

    public function getAllDisciplinesQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $searchType = isset($params["searchType"]) ? $params["searchType"] : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_discipline AS A";
        $SQL .= " WHERE 1=1";
        if ($searchType)
            $SQL .= " AND TYPE = '" . $searchType . "'";

        if ($globalSearch) {
            $SQL .= " AND ((A.SUBJECT like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        $SQL .= " ORDER BY A.SUBJECT";
        return self::dbAccess()->fetchAll($SQL);
    }

    public function loadDiscipline($params) {

        $Id = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $personType = isset($params["personType"]) ? addText($params["personType"]) : ""; //@Man

        $result = $this->getDisciplineFromId($Id, $personType);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $result
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function actionDiscipline($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $infractionDate = isset($params["INFRACTION_DATE"]) ? setDate2DB($params["INFRACTION_DATE"]) : "";
        $punishmentType = isset($params["PUNISHMENT_TYPE"]) ? $params["PUNISHMENT_TYPE"] : "";
        $comment = isset($params["COMMENT"]) ? $params["COMMENT"] : "";
        $personType = isset($params["personType"]) ? $params["personType"] : ""; //@Man
        switch ($personType) {
            case "staff":
                $chooseId = "STAFF_ID";
                $disciplineType = isset($params["DISCIPLINE_TYPE_STAFF"]) ? $params["DISCIPLINE_TYPE_STAFF"] : "";
                break;
            default:
                $chooseId = "STUDENT_ID";
                $disciplineType = isset($params["DISCIPLINE_TYPE_STUDENT"]) ? $params["DISCIPLINE_TYPE_STUDENT"] : "";
                break;
        }

        $CHECK_FUTUR_DATE = $this->checkFuturDate($infractionDate);

        $errors = array();

        if ($objectId != "new") {

            $SQL = "UPDATE t_discipline SET";

            if ($studentId)
                $SQL .= " {$chooseId} = '" . addText($studentId) . "',";

            if ($comment)
                $SQL .= " COMMENT = '" . addText($comment) . "',";

            if ($disciplineType)
                $SQL .= " DISCIPLINE_TYPE = '" . addText($disciplineType) . "',";

            if ($punishmentType)
                $SQL .= " PUNISHMENT_TYPE = '" . addText($punishmentType) . "',";

            if ($infractionDate)
                $SQL .= " INFRACTION_DATE = '" . $infractionDate . "',";

            $SQL .= " MODIFY_DATE = '" . getCurrentDBDateTime() . "',";
            $SQL .= " MODIFY_BY = '" . Zend_Registry::get('USER')->CODE . "'";
            $SQL .= " WHERE";
            $SQL .= " ID = '" . $objectId . "'";

            if (!$CHECK_FUTUR_DATE)
                self::dbAccess()->query($SQL);
        } else {
            $SAVEDATA[$chooseId] = addText($studentId);

            if ($infractionDate)
                $SAVEDATA['INFRACTION_DATE'] = $infractionDate;

            if ($comment)
                $SAVEDATA['COMMENT'] = addText($comment);

            if ($disciplineType)
                $SAVEDATA['DISCIPLINE_TYPE'] = addText($disciplineType);

            if ($punishmentType)
                $SAVEDATA['PUNISHMENT_TYPE'] = addText($punishmentType);

            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            if (!$CHECK_FUTUR_DATE) {
                self::dbAccess()->insert('t_discipline', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
            }
        }

        if ($CHECK_FUTUR_DATE) {
            $errors["INFRACTION_DATE"] = CHECK_DATE_PAST;
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
                , "objectId" => $objectId
            );
        } else {
            return array(
                "success" => true
                , "errors" => $errors
                , "objectId" => $objectId
            );
        }
    }

    public function findPunishmentFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array('*'));
        if (is_numeric($Id)) {
            $SQL->where("ID = ?", $Id);
        }
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getAllDisciplineQuery($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : '';        //@veasna  
        $academictype = isset($params["academictype"]) ? addText($params["academictype"]) : '';
        $code = isset($params["CODE"]) ? addText($params["CODE"]) : '';
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : '';
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : '';
        $gender = isset($params["GENDER"]) ? addText($params["GENDER"]) : '';
        $startDate = isset($params["START_DATE"]) ? addText($params["START_DATE"]) : '';
        $endDate = isset($params["END_DATE"]) ? addText($params["END_DATE"]) : '';
        $campusId = isset($params["campusId"]) ? addText($params["campusId"]) : '';
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : '';
        $gradeId = isset($params["gradeId"]) ? addText($params["gradeId"]) : '';
        $classId = isset($params["classId"]) ? (int) $params["classId"] : '';
        //@Math Man 11.02.2014
        $personType = isset($params["personType"]) ? addText($params["personType"]) : '';

        switch (strtoupper($personType)) {
            case "STAFF":
                $SCHOOL_ID = "STAFF_SCHOOL_ID";
                $table = "t_staff";
                $DISCIPLINE_TYPE = "DISCIPLINE_TYPE_STAFF";
                $studentId = isset($params["STAFF_ID"]) ? addText($params["STAFF_ID"]) : '';
                $disciplineType = isset($params["DISCIPLINE_TYPE_STAFF"]) ? addText($params["DISCIPLINE_TYPE_STAFF"]) : '';
                $studentschoolId = isset($params["STAFF_SCHOOL_ID"]) ? addText($params["STAFF_SCHOOL_ID"]) : '';
                $chooseId = "STAFF_ID";
                break;
            default:
                $SCHOOL_ID = "STUDENT_SCHOOL_ID";
                $table = "t_student";
                $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : '';
                $studentschoolId = isset($params["STUDENT_SCHOOL_ID"]) ? addText($params["STUDENT_SCHOOL_ID"]) : '';
                $disciplineType = isset($params["DISCIPLINE_TYPE_STUDENT"]) ? addText($params["DISCIPLINE_TYPE_STUDENT"]) : '';
                $DISCIPLINE_TYPE = "DISCIPLINE_TYPE_STUDENT";
                $chooseId = "STUDENT_ID";
                break;
        }
        ////////////////////////

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.STATUS AS STATUS";
        $SQL .= " ,A.PUNISHMENT_TYPE AS PUNISHMENT_TYPE";
        $SQL .= " ,CONCAT(B.FIRSTNAME,' ', B.LASTNAME) AS STUDENT";
        $SQL .= " ,B.FIRSTNAME AS FIRSTNAME";
        $SQL .= " ,B.LASTNAME AS LASTNAME";
        $SQL .= " ,B.ID AS STUDENT_ID";
        $SQL .= " ,B.CODE AS CODE";
        $SQL .= " ,B." . $SCHOOL_ID . " AS STUDENT_SCHOOL_ID";
        $SQL .= " ,B.LASTNAME_LATIN AS LASTNAME_LATIN";
        $SQL .= " ,B.FIRSTNAME_LATIN AS FIRSTNAME_LATIN";
        $SQL .= " ,B.PHONE AS PHONE";
        $SQL .= " ,B.EMAIL AS EMAIL";
        $SQL .= " ,B.GENDER AS GENDER";
        $SQL .= " ,B.DATE_BIRTH AS DATE_BIRTH";
        $SQL .= " ,B.CREATED_DATE AS CREATED_DATE";
        $SQL .= " ,A.INFRACTION_DATE AS INFRACTION_DATE";
        $SQL .= " ,A.CREATED_BY AS CREATED_BY ";
        $SQL .= " ,A.COMMENT AS COMMENT";
        $SQL .= " ,C.NAME AS DISCIPLINE_TYPE";
        $SQL .= " ,C.ID AS DISCIPLINE_TYPE_ID";
        $SQL .= " FROM t_discipline AS A";
        $SQL .= " LEFT JOIN " . $table . " AS B ON A." . $chooseId . "=B.ID";
        $SQL .= " LEFT JOIN t_camemis_type AS C ON C.ID=A.DISCIPLINE_TYPE";
        $SQL .= " LEFT JOIN t_student_schoolyear AS D ON D.STUDENT=A.STUDENT_ID";

        if ($campusId || $gradeId || $schoolyearId) { ////@veasna
            $SQL .= " LEFT JOIN t_student_schoolyear AS D ON D.STUDENT=A.STUDENT_ID ";
        }

        $SQL .= " WHERE 1=1";

        $SQL .= " AND C.OBJECT_TYPE = '" . $DISCIPLINE_TYPE . "'";

        if (UserAuth::getUserType() == "STUDENT")
            $SQL .= " AND A.STATUS = '1'";
        if ($code)
            $SQL .= " AND B.CODE like '" . $code . "%'";
        if ($studentschoolId)
            $SQL .= " AND B." . $SCHOOL_ID . " like '" . $studentschoolId . "%'";
        if ($firstname)
            $SQL .= " AND B.FIRSTNAME like '" . $firstname . "%'";
        if ($lastname)
            $SQL .= " AND B.LASTNAME like '" . $lastname . "%'";
        if ($gender)
            $SQL .= " AND B.GENDER = '" . $gender . "'";
        if ($academictype)
            $SQL .= " AND E.ACADEMIC_TYPE = '" . $academictype . "'";
        if ($studentId)
            $SQL .= " AND A." . $chooseId . " = '" . $studentId . "'";
        if ($classId)
            $SQL .= " AND D.CLASS = '" . $classId . "'";
        if ($disciplineType)
            $SQL .= " AND A.DISCIPLINE_TYPE = " . $disciplineType . "";

        if ($startDate && $endDate) {
            $SQL .= " AND A.INFRACTION_DATE >= '" . setDate2DB($startDate) . "' AND A.INFRACTION_DATE <= '" . setDate2DB($endDate) . "'";
        }

        ////@veasna
        if ($campusId || $gradeId || $schoolyearId) {
            if ($campusId) {
                $SQL .= " AND D.CAMPUS = '" . $campusId . "'";
            }
            if ($gradeId) {
                $SQL .= " AND D.GRADE = '" . $gradeId . "'";
            }
            if ($schoolyearId) {
                $SQL .= " AND D.SCHOOL_YEAR = '" . $schoolyearId . "'";
            }
        }
        ////
        if ($globalSearch) {
            $SQL .= " AND ((B.NAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (B.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }
        if ($target)
            $SQL .= " AND A.ACADEMIC_TYPE = '" . $target . "'";

        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY B." . $SCHOOL_ID . " DESC";
                break;
            case "1":
                $SQL .= " ORDER BY B.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY B.FIRSTNAME DESC";
                break;
        }

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonListByDicipline($params, $isJson = true) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $personType = isset($params["personType"]) ? addText($params["personType"]) : ''; //@Man

        $result = $this->getAllDisciplineQuery($params);

        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $punishmentType = self::findPunishmentFromId($value->PUNISHMENT_TYPE);

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                switch (strtoupper($personType)) {
                    case "STUDENT":
                        $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                        $data[$i]["STUDENT"] = $value->STUDENT;
                        $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;



                        $data[$i]["CURRENT_SCHOOLYEAR"] = StudentSearchDBAccess::getCurrentAcademic($value->STUDENT_ID)->CURRENT_SCHOOLYEAR;
                        $data[$i]["CURRENT_ACADEMIC"] = StudentSearchDBAccess::getCurrentAcademic($value->STUDENT_ID)->CURRENT_ACADEMIC;
                        $data[$i]["CURRENT_COURSE"] = StudentSearchDBAccess::getCurrentTraining($value->STUDENT_ID);
                        break;
                }

                $data[$i]["INFRACTION_DATE"] = getShowDate($value->INFRACTION_DATE);
                $data[$i]["COMMENT"] = setShowText($value->COMMENT);
                $data[$i]["DISCIPLINE_TYPE_ID"] = $value->DISCIPLINE_TYPE_ID;
                $data[$i]["DISCIPLINE_TYPE"] = setShowText($value->DISCIPLINE_TYPE);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = $value->DATE_BIRTH;
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;

                if ($punishmentType) {
                    $data[$i]["PUNISHMENT_TYPE"] = $punishmentType->NAME;
                }

                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["COMMENT"] = setShowText($value->COMMENT);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }


        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public function releaseDiscipline($Id) {

        $facette = $this->findDisciplineFromId($Id);

        $status = $facette->STATUS;

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_discipline";
        $SQL .= " SET";

        switch ($status) {
            case 0:
                $newStatus = 1;
                $SQL .= " STATUS=1";
                $SQL .= " ,ENABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,ENABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
            case 1:
                $newStatus = 0;
                $SQL .= " STATUS=0";
                $SQL .= " ,DISABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,DISABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $Id . "'";
        self::dbAccess()->query($SQL);

        return array("success" => true, "status" => $newStatus);
    }

    public function removeDiscipline($Id) {

        if ($Id) {
            $SQL = "
            DELETE FROM t_discipline
            WHERE 1=1
            AND ID = '" . $Id . "'
            ";
            self::dbAccess()->query($SQL);
        }

        return array("success" => true);
    }

    protected function checkExistingDisciplineDate($studentId, $infractionDate) {

        $SQL = self::dbAccess()->select()
                ->from("t_discipline", array("C" => "COUNT(*)"))
                ->where("STUDENT_ID = '" . $studentId . "'")
                ->where("INFRACTION_DATE = '" . $infractionDate . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function checkFuturDate($date) {

        $date = strtotime($date);
        $today = strtotime(getCurrentDBDate());

        if ($date > $today) {
            return true;
        } else {
            return false;
        }
    }

}

?>