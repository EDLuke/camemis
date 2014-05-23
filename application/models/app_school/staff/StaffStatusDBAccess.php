<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/PersonStatusDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StaffStatusDBAccess extends StaffDBAccess {

    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function jsonLoadLastStaffStatus($staffId)
    {

        return array(
            "success" => true
            , "data" => self::getCurrentStaffStatus($staffId)
        );
    }

    public static function findStaffStatus($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_status", array('*'));
        $SQL->where("ID = '" . $Id . "'");
        //echo $SQL->__toString();
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findIdStaffStatus($staffId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_status'));
        $SQL->joinLeft(array('B' => 't_person_status'), 'B.ID=A.STATUS_ID', array('B.NAME', 'B.SHORT'));
        $SQL->where("A.STAFF = '" . $staffId . "'");
        //error_log($SQL->__toString());       
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public static function removeStaffStatus($Id)
    {
        self::dbAccess()->delete('t_staff_status', array("ID='" . $Id . "'"));

        return array(
            "success" => true
            , "data" => array()
        );
    }

    public static function jsonActionSaveLastStaffStatus($params)
    {

        $statusId = isset($params["STAFF_STATUS"]) ? addText($params["STAFF_STATUS"]) : "";

        $SAVEDATA = array();

        $staffId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $chooseId = isset($params["chooseId"]) ? addText($params["chooseId"]) : "";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $singleDate = isset($params["SINGLE_START_DATE"]) ? setDate2DB($params["SINGLE_START_DATE"]) : "";
        $description = isset($params["DESCRIPTION"]) ? addText($params["DESCRIPTION"]) : "";

        $staffStatusObject = self::getSQLCurrentStaffStatus($staffId);

        if ($startDate && $endDate)
        {
            $CAL_POST_START_DATE = strtotime($startDate);
            $CAL_POST_END_DATE = strtotime($endDate);
            $POST_START_DATE = $startDate;
            $POST_END_DATE = $endDate;
        }

        if ($startDate && !$endDate)
        {
            $CAL_POST_START_DATE = $CAL_SINGLE_DATE;
            $CAL_POST_END_DATE = "";
            $POST_START_DATE = $singleDate;
            $POST_END_DATE = "";
        }

        $CAL_SINGLE_DATE = strtotime($singleDate);
        $CAL_CURRENT_START_DATE = strtotime($staffStatusObject->LAST_START_DATE);
        $CAL_CURRENT_END_DATE = strtotime($staffStatusObject->LAST_END_DATE);

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
                $SAVEDATA ["STAFF"] = addText($staffId);
                self::dbAccess()->insert('t_staff_status', $SAVEDATA);
                break;
            case "UPDATE":
                $WHERE[] = "ID = '" . $staffStatusObject->OBJECT_ID . "'";
                self::dbAccess()->update('t_staff_status', $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true);
    }

    public static function getSQLCurrentStaffStatus($staffId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff'), array(
            'A.STATUS AS STAFF_STATUS'
            , 'A.MODIFY_DATE AS STAFF_MODIFY_DATE'
            , 'A.CREATED_DATE AS STAFF_CREATED_DATE'
                )
        );
        $SQL->joinLeft(
                array('B' => 't_staff_status')
                , 'A.ID=B.STAFF', array(
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

        $SQL->where("A.ID = '" . $staffId . "'");
        $SQL->order("B.ID DESC");
        $SQL->limit("1,0");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getCurrentStaffStatus($staffId)
    {

        $result = self::getSQLCurrentStaffStatus($staffId);
        $data = array();

        if ($result)
        {
            if ($result->LAST_STATUS)
            {
                $data['SHORT'] = $result->SHORT;
                $data['NAME'] = $result->LAST_STATUS;
                $data['COLOR'] = $result->COLOR;
                $data['COLOR_FONT'] = getFontColor($result->COLOR);

                switch ($result->DISPLAY_DATE)
                {
                    case 1:
                        $data['DATE'] = getShowDate($result->LAST_START_DATE);
                        break;
                    case 2:
                        $data['DATE'] = getShowDate($result->LAST_START_DATE) . " - " . getShowDate($result->LAST_END_DATE);
                        break;
                }
            }
            else
            {
                $data['SHORT'] = $result->STAFF_STATUS ? ACTIVE : NO_ACTIVE;
                $data['NAME'] = $result->STAFF_STATUS ? ACTIVE : NO_ACTIVE;
                $data['COLOR'] = $result->STAFF_STATUS ? '#53aae0' : '#de5243';
                $data['COLOR_FONT'] = $result->STAFF_STATUS ? "#FFF" : "#FFF";
                if ($result->STAFF_MODIFY_DATE == "0000-00-00 00:00:00")
                {
                    $data['DATE'] = getShowDateTime($result->STAFF_CREATED_DATE);
                }
                else
                {
                    $data['DATE'] = getShowDateTime($result->STAFF_MODIFY_DATE);
                }
            }
        }

        return $data;
    }

    public static function getSqlStaffStatus($params)
    {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";
        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : "";
        $gender = isset($params["GENDER"]) ? addText($params["GENDER"]) : "";
        $code = isset($params["CODE"]) ? addText($params["CODE"]) : "";
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : "";
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : "";
        $staff_school_id = isset($params["STAFF_SCHOOL_ID"]) ? addText($params["STAFF_SCHOOL_ID"]) : "";
        $startDate = isset($params["START_DATE"]) ? addText($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? addText($params["END_DATE"]) : "";
        $staffstatusType = isset($params["STAFF_STATUS"]) ? addText($params["STAFF_STATUS"]) : "";
        $staffId = isset($params["staffId"]) ? addText($params["staffId"]) : "";

        $SELECTION_A = array(
            "ID AS STAFF_ID"
            , "CODE AS STAFF_CODE"
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
            , "STAFF_SCHOOL_ID AS STAFF_SCHOOL_ID"
        );

        $SELECTION_B = array(
            "ID AS STATUS_ID"
            , "START_DATE AS START_DATE"
            , "CONCAT(A.FIRSTNAME,' ',A.LASTNAME) AS STAFF"
            , "END_DATE AS END_DATE"
            , "DESCRIPTION AS DESCRIPTION"
            , "CREATED_DATE AS CREATED_DATE"
            , "CREATED_BY AS CREATED_BY"
            , "STAFF AS STAFF"
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
        $SQL->from(array('A' => 't_staff'), $SELECTION_A);
        $SQL->joinRight(array('B' => 't_staff_status'), 'A.ID=B.STAFF', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_person_status'), 'C.ID=B.STATUS_ID', $SELECTION_C);
        $SQL->where("1=1");

        if ($startDate && $endDate)
        {
            $SQL->where("B.START_DATE BETWEEN '" . setDate2DB($startDate) . "' AND '" . setDate2DB($endDate) . "'");
        }

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

        if ($staff_school_id)
            $SQL .= " AND A.STUDENT_SCHOOL_ID LIKE '" . strtoupper($staff_school_id) . "%' ";

        if ($gender)
            $SQL->where("A.GENDER='" . $gender . "'");
        if ($staffstatusType)
            $SQL->where("B.STATUS_ID='" . $staffstatusType . "'");
        if ($staffId)
            $SQL->where("B.STAFF='" . $staffId . "'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME like '" . $firstname . "%'");
        if ($lastname)
            $SQL->where("A.LASTNAME like '" . $lastname . "%'");

        $SQL->order("B.STATUS_ID ASC");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonListStaffStatus($params, $isJson = true)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getSqlStaffStatus($params);

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

                $data[$i]["ID"] = $value->STAFF_ID;
                $data[$i]["CODE_ID"] = $value->STAFF_CODE;
                $data[$i]["STAFF"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]['STATUS_KEY'] = $value->SHORT;
                $data[$i]['BG_COLOR'] = $value->COLOR;
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$i]["PHONE"] = $value->PHONE;
                $data[$i]["EMAIL"] = $value->EMAIL;
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);

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

    public static function jsonSearchStaffStatus($params, $isJson = true)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getSqlStaffStatus($params);

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
                $data[$i]["ID"] = $value->STAFF_ID;
                $data[$i]["CODE_ID"] = $value->STAFF_CODE;
                $data[$i]["STAFF"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]['STATUS_KEY'] = $value->SHORT;
                $data[$i]['BG_COLOR'] = $value->COLOR;
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$i]["PHONE"] = $value->PHONE;
                $data[$i]["EMAIL"] = $value->EMAIL;
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STAFF"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["STAFF"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

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

    public static function getSQLCurrentStaffContractStatus($staffId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_members'), array('A.STATUS AS STAFF_STATUS'));
        $SQL->joinLeft(
                array('B' => 't_staff_status')
                , 'A.ID=B.STAFF', array(
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

        $SQL->where("A.ID = '" . $staffId . "'");
        $SQL->order("B.ID DESC");
        $SQL->limit("1,0");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getCurrentStaffContractStatus($staffId)
    {

        $result = self::getSQLCurrentStaffContractStatus($staffId);
        $data = array();

        if ($result)
        {
            if ($result->LAST_STATUS)
            {
                $data['SHORT'] = $result->SHORT;
                $data['NAME'] = $result->LAST_STATUS;
                $data['COLOR'] = $result->COLOR;
                $data['COLOR_FONT'] = getFontColor($result->COLOR);
            }
            else
            {
                $data['SHORT'] = $result->STAFF_STATUS ? ACTIVE : NO_ACTIVE;
                $data['NAME'] = $result->STAFF_STATUS ? ACTIVE : NO_ACTIVE;
                $data['COLOR'] = $result->STAFF_STATUS ? '#53aae0' : '#de5243';
                $data['COLOR_FONT'] = $result->STAFF_STATUS ? "#FFF" : "#FFF";
            }
        }

        return $data;
    }

}

?>