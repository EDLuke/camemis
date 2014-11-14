<?php

///////////////////////////////////////////////////////////
//@Chung veng Web Developer
//Date: 22.06.2013
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once setUserLoacalization();

class ClubDBAccess {

    CONST TABLE_CLUB = "t_club";
    CONST TABLE_CLUBEVENT = "t_clubevent";

    public $DB_ACCESS = null;

    public function __construct() {
        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->SELECT = $this->DB_ACCESS->select();
        $this->_TOSTRING = $this->SELECT->__toString();
    }

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new ClubDBAccess();
        }
        return $me;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public function getFileDataFromId($Id) {
        $data = array();
        $result = self::findClubFromId($Id);

        if ($result) {
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["TEACHER_ID"] = setShowText($result->TEACHER_ID);
            $data["TEACHER_NAME"] = $result->LASTNAME . " " . $result->FIRSTNAME;
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["REGISTRATION"] = $result->REGISTRATION ? true : false;
            $data["STATUS"] = $result->STATUS;
        }

        return $data;
    }

    public static function findClubFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_club'));
        $SQL->joinLeft(array('B' => 't_staff'), 'A.TEACHER_ID=B.ID', array('B.FIRSTNAME', 'B.LASTNAME'));

        $SQL->where("A.ID = ?",$Id);
        //error_log($SQL->__toString());       
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public function jsonReleaseClub($Id) {
        $SAVEDATA = array();
        $facette = self::findClubFromId($Id);
        $newStatus = 0;
        switch ($facette->STATUS) {
            case 0:
                $newStatus = 1;
                $SAVEDATA["STATUS"] = 1;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                if (isset($params["TEACHER_ID"]))
                    $SAVEDATA["TEACHER_ID"] = addText($params["TEACHER_ID"]);
                self::dbAccess()->update(self::TABLE_CLUB, $SAVEDATA, $WHERE);
                break;
            case 1:
                $newStatus = 0;
                $SAVEDATA["STATUS"] = 0;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                if (isset($params["TEACHER_ID"]))
                    $SAVEDATA["TEACHER_ID"] = addText($params["TEACHER_ID"]);
                self::dbAccess()->update(self::TABLE_CLUB, $SAVEDATA, $WHERE);
                break;
        }
        return array("success" => true, "status" => $newStatus);
    }

    public function jsonLoadClub($Id) {
        $result = self::findClubFromId($Id);
        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getFileDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function getAllClubsQuery($params) {
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_club";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        if ($parentId) {
            $SQL .= " AND PARENT='" . $parentId . "'";
        } else {
            $SQL .= " AND PARENT='0'";
        }

        $SQL .= " ORDER BY NAME";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getComboDataFile() {
        $result = self::getAllClubsQuery(false);
        $data = array();
        $data[0] = "[0,'[---]']";
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";
                $i++;
            }
        return "[" . implode(",", $data) . "]";
    }

    public function jsonTreeAllClubs($params) {
        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "";
        $result = self::getAllClubsQuery($params);
        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if (self::checkChild($value->ID)) {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['disabled'] = false;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                } else {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['cls'] = "nodeTextBlue";
                    $data[$i]['iconCls'] = "icon-application_form_magnify_form_magnify";
                    $data[$i]['leaf'] = true;
                }
                $i++;
            }

        return $data;
    }

    public function jsonSaveClub($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "0";
        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        $SAVEDATA['REGISTRATION'] = isset($params["REGISTRATION"]) ? 1 : 0;

        if ($objectId == "new") {
            $SAVEDATA["STATUS"] = 0;
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_club', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            if (isset($params["TEACHER_ID"]))
                $SAVEDATA["TEACHER_ID"] = addText($params["TEACHER_ID"]);
            $SAVEDATA['REGISTRATION'] = isset($params["REGISTRATION"]) ? 1 : 0;
            self::dbAccess()->update('t_club', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function jsonRemoveClub($Id) {
        self::dbAccess()->delete("t_club", "ID = '" . $Id . "'");

        if (self::checkChild($Id)) {
            self::dbAccess()->delete("t_club", "PARENT = '" . $Id . "'");
        }
        return array("success" => true);
    }

    public function jsonTeacherClub($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $SELECT_DATA = array(
            "A.ID AS ID"
            , "A.CODE AS CODE"
            , "A.FIRSTNAME AS FIRSTNAME"
            , "A.LASTNAME AS LASTNAME"
            , "A.GENDER AS GENDER"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff'), $SELECT_DATA);

        $SQL->group("ID");
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $status = "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user_add.png' border='0'>";

                $data[$i]["ID"] = $value->ID;
                $data[$i]["STATUS"] = $status;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["GENDER"] = $value->GENDER;

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

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_club", array("C" => "COUNT(*)"));
        if ($Id)
            $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    /// new event calendar ////

    public function createOnlyItem($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $SAVEDATA['NAME'] = addText($params["name"]);
        $SAVEDATA["CLUB"] = isset($objectId) ? addText($objectId) : "";
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        self::dbAccess()->insert('t_clubevent', $SAVEDATA);

        return array("success" => true);
    }

    public function allClubevents($params, $isJson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getAllClubeventsQuery($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CLUB"] = $value->CLUB;
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["START_HOUR"] = $value->START_HOUR;
                $data[$i]["END_HOUR"] = $value->END_HOUR;
                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["REMARK"] = $value->REMARK;

                if (isset($value->STATUS)) {
                    $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                    $data[$i]["STATUS"] = $value->STATUS;
                }

                $i++;
            }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
        if ($isJson == true)
            return $this->dataforjson;
        else
            return $data;
    }

    public static function getAllClubeventsQuery($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $status = isset($params["status"]) ? addText($params["status"]) : "";
        $SQL = "";
        $SQL .= " SELECT A.ID AS ID
            ,A.NAME AS NAME
            ,A.CLUB AS CLUB
            ,A.STATUS AS STATUS
            ,A.START_DATE AS START_DATE
            ,A.END_DATE AS END_DATE
            ,A.START_HOUR AS START_HOUR
            ,A.END_HOUR AS END_HOUR
            ,A.REMARK AS REMARK
            ";
        $SQL .= " FROM t_clubevent AS A";
        $SQL .= " WHERE 1=1";

        if ($objectId) {
            $SQL .= " AND A.CLUB = '" . $objectId . "'";
        }

        if ($status) {
            $SQL .= " AND A.STATUS = '" . $status . "'";
        }

        //echo $SQL;
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    //
    public function getClubeventDataFromId($Id) {

        $result = $this->findClubeventFromId($Id);

        $data = array();
        if ($result) {

            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);
            $data["REMARK"] = setShowText($result->REMARK);
            $data["STATUS"] = $result->STATUS;
            $data["START_HOUR"] = $result->START_HOUR;
            $data["END_HOUR"] = $result->END_HOUR;
            //$data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
        }

        return $data;
    }

    public static function findClubeventFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_clubevent";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";

        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadObject($Id) {

        $result = $this->findClubeventFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getClubeventDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    //
    public function jsonSaveEvent($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SAVEDATA['NAME'] = addText($params["NAME"]);
        $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
        $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]);
        $SAVEDATA['REMARK'] = addText($params["REMARK"]);
        if (isset($params["START_HOUR"]))
            $SAVEDATA['START_HOUR'] = addText($params["START_HOUR"]);
        if (isset($params["END_HOUR"]))
            $SAVEDATA['END_HOUR'] = addText($params["END_HOUR"]);

        //check date errors
        $CHECK_ERROR_START_DATE = timeDifference(getCurrentDBDate(), setDate2DB($params["START_DATE"]));
        $CHECK_ERROR_END_DATE = timeDifference(getCurrentDBDate(), setDate2DB($params["END_DATE"]));
        $CHECK_ERROR_START_END_DATE = timeDifference(setDate2DB($params["START_DATE"]), setDate2DB($params["END_DATE"]));

        if ($CHECK_ERROR_START_DATE) {
            $errors["START_DATE"] = CHECK_DATE_PAST;
        } elseif ($CHECK_ERROR_END_DATE) {
            $errors["END_DATE"] = CHECK_DATE_PAST;
        } elseif ($CHECK_ERROR_START_DATE && $CHECK_ERROR_END_DATE) {
            $errors["START_DATE"] = CHECK_DATE_PAST;
            $errors["END_DATE"] = CHECK_DATE_PAST;
        } elseif ($CHECK_ERROR_START_END_DATE) {
            $errors["START_DATE"] = ERROR;
            $errors["END_DATE"] = ERROR;
        } else {
            $errors = array();
        }

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
        if (!$errors)
            self::dbAccess()->update('t_clubevent', $SAVEDATA, $WHERE);

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array("success" => true, "errors" => $errors, "objectId" => $objectId);
        }
    }

    /// remove events 

    public function removeObject($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        self::dbAccess()->delete("t_clubevent", "ID = '" . $removeId . "'");
        
        return array("success" => true);
    }

    /// release events ///
    public function releaseObjectEvent($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = $this->findClubeventFromId($objectId);
        $status = $facette->STATUS;

        $data = array();
        switch ($status) {
            case 0:
                $newStatus = 1;
                $data['STATUS'] = 1;
                break;
            case 1:
                $newStatus = 0;
                $data['STATUS'] = 0;
                break;
        }
        self::dbAccess()->update("t_clubevent", $data, "ID='". $objectId ."'");

        return array("success" => true, "status" => $newStatus);
    }

}

?>