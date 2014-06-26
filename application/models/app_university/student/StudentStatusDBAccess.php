<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentAcademicDBAccess.php';
require_once 'models/app_university/student/StudentStatusDBAccess.php';
require_once 'models/app_university/student/StudentSearchDBAccess.php';
require_once 'models/app_university/PersonStatusDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/training/TrainingDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentStatusDBAccess extends StudentDBAccess {

    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function jsonLoadStudentStatus($Id)
    {

        $facette = self::findStudentStatus($Id);

        $data = array();

        if ($facette)
        {
            $data["ID"] = $facette->ID;
            $data["STATUS"] = $facette->STATUS;
            $data["STUDENT_STATUS"] = $facette->STUDENT_STATUS;
            $data["DESCRIPTION"] = $facette->DESCRIPTION;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function jsonLoadLastStudentStatus($studentId)
    {

        return array(
            "success" => true
            , "data" => self::getCurrentStudentStatus($studentId)
        );
    }

    // Thong
    public static function jsonStudentStatus()
    {

        //
    }

    public static function findStudentStatus($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_status", array('*'));
        $SQL->where("STUDENT = '" . $Id . "'");
        //echo $SQL->__toString();
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findIdStudentStatus($studentId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_status'));
        $SQL->joinLeft(array('B' => 't_person_status'), 'B.ID=A.STATUS_ID', array('B.NAME', 'B.SHORT'));
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        //error_log($SQL->__toString());       
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public static function findStatusByStudent($studentId, $status)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_status", array('*'));
        $SQL->where("STUDENT = ?",$studentId);
        $SQL->where("'" . getCurrentDBDate() . "' BETWEEN START_DATE AND END_DATE");
        if ($status)
            $SQL->where("STUDENT_STATUS = '" . $status . "'");
        $SQL->order("LEVEL DESC");
        //echo $SQL->__toString();
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function checkFuturDate($date)
    {

        $date = strtotime($date);
        $today = strtotime(getCurrentDBDate());

        if ($date > $today)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function removeStudentStatus($Id)
    {
        self::dbAccess()->delete('t_student_status', array("ID='" . $Id . "'"));

        return array(
            "success" => true
            , "data" => array()
        );
    }

    public static function jsonStatusByStudent()
    {
        
    }

    public static function updateStudentStatus($studentId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'));
        $SQL->joinLeft(array('B' => 't_student_status'), 'A.ID=B.STUDENT', array('B.STATUS'));

        $SQL->where("A.ID = '" . $studentId . "'");
        //error_log($SQL->__toString());       
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public static function jsonActionSaveLastStudentStatus($params)
    {

        $statusId = isset($params["STUDENT_STATUS"]) ? addText($params["STUDENT_STATUS"]) : "";

        $SAVEDATA = array();

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $singleDate = isset($params["SINGLE_START_DATE"]) ? setDate2DB($params["SINGLE_START_DATE"]) : "";
        $description = isset($params["DESCRIPTION"]) ? addText($params["DESCRIPTION"]) : "";

        $studentStatusObject = self::getSQLCurrentStudentStatus($studentId);

        if ($startDate && $endDate)
        {
            $CAL_POST_START_DATE = strtotime($startDate);
            $CAL_POST_END_DATE = strtotime($endDate);
            $POST_START_DATE = $startDate;
            $POST_END_DATE = $endDate;
        }

        if ($singleDate)
        {
            $CAL_POST_START_DATE = $CAL_SINGLE_DATE;
            $CAL_POST_END_DATE = "";
            $POST_START_DATE = $singleDate;
            $POST_END_DATE = "";
        }

        $CAL_SINGLE_DATE = strtotime($singleDate);
        $CAL_CURRENT_START_DATE = strtotime($studentStatusObject->LAST_START_DATE);
        $CAL_CURRENT_END_DATE = strtotime($studentStatusObject->LAST_END_DATE);

        if (!$CAL_CURRENT_START_DATE && !$CAL_CURRENT_END_DATE)
        {
            $ACTION = "INSERT";
        }
        elseif ($CAL_CURRENT_START_DATE && $CAL_CURRENT_END_DATE)
        {
            if ($CAL_POST_START_DATE > $CAL_CURRENT_START_DATE && $CAL_POST_START_DATE < $CAL_CURRENT_END_DATE)
            {
                $ACTION = "UPDATE";
            }
            else
            {
                $ACTION = "INSERT";
            }
        }
        elseif ($CAL_CURRENT_START_DATE && !$CAL_CURRENT_END_DATE)
        {
            if ($CAL_POST_START_DATE < $CAL_CURRENT_START_DATE)
            {
                $ACTION = "UPDATE";
            }
            else
            {
                $ACTION = "INSERT";
            }
        }
        else
        {
            $ACTION = "INSERT";
        }

        $SAVEDATA ["DESCRIPTION"] = addText($description);
        $SAVEDATA ["START_DATE"] = $POST_START_DATE;
        $SAVEDATA ["END_DATE"] = $POST_END_DATE;
        $SAVEDATA ["STATUS_ID"] = $statusId;

        switch ($ACTION)
        {
            case "INSERT":
                $SAVEDATA ["CREATED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA ["CREATED_BY"] = Zend_Registry::get('USER')->CODE;
                $SAVEDATA ["STUDENT"] = addText($studentId);
                self::dbAccess()->insert('t_student_status', $SAVEDATA);
                break;
            case "UPDATE":
                $WHERE[] = "ID = '" . $studentStatusObject->OBJECT_ID . "'";
                self::dbAccess()->update('t_student_status', $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true);
    }

    public static function getSQLCurrentStudentStatus($studentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), array(
            'A.STATUS AS STUDENT_STATUS'
            , 'A.ID AS ID'
            , 'A.MODIFY_DATE AS STUDENT_MODIFY_DATE'
            , 'A.CREATED_DATE AS STUDENT_CREATED_DATE'
                )
        );
        $SQL->joinLeft(
                array('B' => 't_student_status')
                , 'A.ID=B.STUDENT', array(
            'STATUS_ID AS STATUS_ID'
            , 'B.ID AS OBJECT_ID'
            , 'B.START_DATE AS LAST_START_DATE'
            , 'B.END_DATE AS LAST_END_DATE'
            , 'B.DESCRIPTION AS LAST_DESCRIPTION'
                )
        );
        $SQL->joinLeft(
                array('C' => 't_person_status')
                , 'C.ID=B.STATUS_ID', array(
            'C.SHORT AS SHORT'
            , 'C.NAME AS LAST_STATUS'
            , 'C.COLOR AS COLOR'
            , 'C.DISPLAY_DATE AS DISPLAY_DATE'
            , 'C.DEACTIVATE_ACCOUNT AS DEACTIVATE_ACCOUNT'
                )
        );

        $SQL->where("A.ID = '" . $studentId . "'");
        $SQL->order("B.ID DESC");
        $SQL->limit("1,0");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getCurrentStudentStatus($studentId)
    {

        $result = self::getSQLCurrentStudentStatus($studentId);
        $data = array();

        if ($result)
        {
            if ($result->LAST_STATUS)
            {
                $data['SHORT'] = $result->SHORT;
                $data['NAME'] = $result->LAST_STATUS;
                $data['COLOR'] = $result->COLOR;
                $data['COLOR_FONT'] = getFontColor($result->COLOR);
                $DATE_FROM = DATE_FROM . ": " . getShowDate($result->LAST_START_DATE);
                $DATE_TO = DATE_TO . ": " . getShowDate($result->LAST_END_DATE);
                switch ($result->DISPLAY_DATE)
                {
                    case 1:
                        $data['DATE'] = $DATE_FROM;
                        break;
                    case 2:
                        $data['DATE'] = $DATE_FROM . " - " . $DATE_TO;
                        break;
                    default:
                        $data['DATE'] = $DATE_FROM;
                        break;
                }
            }
            else
            {
                $data['SHORT'] = $result->STUDENT_STATUS ? ACTIVE : NO_ACTIVE;
                $data['NAME'] = $result->STUDENT_STATUS ? ACTIVE : NO_ACTIVE;
                $data['COLOR'] = $result->STUDENT_STATUS ? '#53aae0' : '#de5243';
                $data['COLOR_FONT'] = $result->STUDENT_STATUS ? "#FFF" : "#FFF";
                if ($result->STUDENT_MODIFY_DATE == "0000-00-00 00:00:00")
                {
                    $data['DATE'] = getShowDateTime($result->STUDENT_CREATED_DATE);
                }
                else
                {
                    $data['DATE'] = getShowDateTime($result->STUDENT_MODIFY_DATE);
                }
            }
        }

        return $data;
    }

    public static function getSqlStudentStatus($params)
    {

        $studentSchoolId = isset($params["STUDENT_SCHOOL_ID"]) ? addText($params["STUDENT_SCHOOL_ID"]) : "";
        $code = isset($params["CODE"]) ? addText($params["CODE"]) : "";
        $startDate = isset($params["START_DATE"]) ? substr($params["START_DATE"], 0, 10) : "";
        $endDate = isset($params["END_DATE"]) ? substr($params["END_DATE"], 0, 10) : "";
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : "";
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : "";
        $studentstatusType = isset($params["STUDENT_STATUS"]) ? addText($params["STUDENT_STATUS"]) : "";
        $gender = isset($params["GENDER"]) ? addText($params["GENDER"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SELECTION_A = array(
            "ID AS STUDENT_ID"
            , "CODE AS STUDENT_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
            , "EMAIL AS EMAIL"
            , "PHONE AS PHONE"
            , "DATE_BIRTH AS DATE_BIRTH"
            , "YEAR(DATE_BIRTH) AS BORN_YEAR"
            , "MOBIL_PHONE AS MOBIL_PHONE"
            , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
        );

        $SELECTION_B = array(
            "ID AS STATUS_ID"
            , "START_DATE AS START_DATE"
            , "CONCAT(A.FIRSTNAME,' ',A.LASTNAME) AS STUDENT"
            , "END_DATE AS END_DATE"
            , "DESCRIPTION AS DESCRIPTION"
            , "CREATED_DATE AS CREATED_DATE"
            , "CREATED_BY AS CREATED_BY"
            , ""
        );

        $SELECTION_C = array(
            "ID AS ID"
            , "SHORT AS SHORT"
            , "NAME AS LAST_STATUS"
            , "COLOR AS COLOR"
            , "DISPLAY_DATE AS DISPLAY_DATE"
            , "DEACTIVATE_ACCOUNT AS DEACTIVATE_ACCOUNT"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECTION_A);
        $SQL->joinRight(array('B' => 't_student_status'), 'A.ID=B.STUDENT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_person_status'), 'C.ID=B.STATUS_ID', $SELECTION_C);

        $SQL->where("1=1");

        if ($startDate && $endDate)
        {
            $SQL->where("B.START_DATE BETWEEN '" . setDate2DB($startDate) . "' AND '" . setDate2DB($endDate) . "'");
        }


        if ($gender)
            $SQL->where("A.GENDER='" . $gender . "'");
        if ($studentstatusType)
            $SQL->where("B.STATUS_ID='" . $studentstatusType . "'");
        if ($studentId)
            $SQL->where("B.STUDENT='" . $studentId . "'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME like '" . $firstname . "%'");
        if ($lastname)
            $SQL->where("A.LASTNAME like '" . $lastname . "%'");
            
        if ($globalSearch)
        {

            $SQL .= " AND ((A.NAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }

        if ($code)
            $SQL .= " AND A.CODE LIKE '" . strtoupper($code) . "%' ";

        if ($studentSchoolId)
            $SQL .= " AND A.STUDENT_SCHOOL_ID LIKE '" . strtoupper($studentSchoolId) . "%' ";

        $SQL .= "ORDER BY B.STATUS_ID DESC";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonListStudentStatus($params, $isJson = true)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getSqlStudentStatus($params);

        $data = array();

        $total['MALE'] = 0;
        $total['FEMALE'] = 0;
        $total['UNKNOWN'] = 0;

        $born_year = array();
        $born_year["UNKNOWN"] = 0;

        $i = 0;
        if ($result)
        {
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE_ID"] = $value->STUDENT_CODE;
                $data[$i]["STUDENT"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]['STATUS_KEY'] = $value->SHORT;
                $data[$i]['BG_COLOR'] = $value->COLOR;
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;

                $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$i]["PHONE"] = $value->PHONE;
                $data[$i]["EMAIL"] = $value->EMAIL;
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);

                $data[$i]["CURRENT_SCHOOLYEAR"] = StudentSearchDBAccess::getCurrentAcademic($value->STUDENT_ID)->CURRENT_SCHOOLYEAR;
                $data[$i]["CURRENT_ACADEMIC"] = StudentSearchDBAccess::getCurrentAcademic($value->STUDENT_ID)->CURRENT_ACADEMIC;
                $data[$i]["CURRENT_COURSE"] = StudentSearchDBAccess::getCurrentTraining($value->STUDENT_ID);

                $i++;
                switch ($value->GENDER)
                {
                    case 1:
                        $total['MALE'] ++;
                        break;
                    case 2:
                        $total['FEMALE'] ++;
                        break;
                    default:
                        $total['UNKNOWN'] ++;
                }

                if ($value->BORN_YEAR)
                {
                    if (isset($born_year[$value->BORN_YEAR]))
                        ++$born_year[$value->BORN_YEAR];
                    else
                        $born_year[$value->BORN_YEAR] = 1;
                } else
                {
                    ++$born_year["UNKNOWN"];
                }
            }
        }

        $jsongender = jsonCountGender($total);
        $jsonbornyear = jsonBornYear($born_year);

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "sexCount" => $jsongender
                , "bornYear" => $jsonbornyear
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        }
    }

    public static function jsonSearchStudentStatus($params, $isJson = true)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getSqlStudentStatus($params);

        $data = array();

        $i = 0;

        $total['MALE'] = 0;
        $total['FEMALE'] = 0;
        $total['UNKNOWN'] = 0;

        $born_year = array();
        $born_year["UNKNOWN"] = 0;
        if ($result)
        {
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE_ID"] = $value->STUDENT_CODE;
                $data[$i]["STUDENT"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]['STATUS_KEY'] = $value->SHORT;
                $data[$i]['BG_COLOR'] = $value->COLOR;
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;

                $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$i]["PHONE"] = $value->PHONE;
                $data[$i]["EMAIL"] = $value->EMAIL;
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["STUDENT_STATUS"] = setShowText($value->STATUS_ID);

                $data[$i]["CURRENT_SCHOOLYEAR"] = StudentSearchDBAccess::getCurrentAcademic($value->STUDENT_ID)->CURRENT_SCHOOLYEAR;
                $data[$i]["CURRENT_ACADEMIC"] = StudentSearchDBAccess::getCurrentAcademic($value->STUDENT_ID)->CURRENT_ACADEMIC;
                $data[$i]["CURRENT_COURSE"] = StudentSearchDBAccess::getCurrentTraining($value->STUDENT_ID);

                $i++;

                switch ($value->GENDER)
                {
                    case 1:
                        $total['MALE'] ++;
                        break;
                    case 2:
                        $total['FEMALE'] ++;
                        break;
                    default:
                        $total['UNKNOWN'] ++;
                }

                if (isset($value->BORN_YEAR))
                {
                    if ($value->BORN_YEAR)
                    {
                        if (isset($born_year[$value->BORN_YEAR]))
                            ++$born_year[$value->BORN_YEAR];
                        else
                            $born_year[$value->BORN_YEAR] = 1;
                    } else
                    {
                        ++$born_year["UNKNOWN"];
                    }
                }
            }
        }

        $jsongender = jsonCountGender($total);
        $jsonbornyear = jsonBornYear($born_year);

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "sexCount" => $jsongender
                , "bornYear" => $jsonbornyear
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        }
    }

}

?>