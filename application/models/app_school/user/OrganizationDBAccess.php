<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 27.02.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/SessionAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class OrganizationDBAccess {
    const T_ORGANIZATION = "t_organization";
    const T_USER_ORGANIZATION = "t_user_organization";
    const T_STAFF = "t_staff";

    protected $data = array();
    protected $out = array();

    public function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->SELECT = $this->DB_ACCESS->select();
        $this->_TOSTRING = $this->SELECT->__toString();
    }

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getOrganizationDataFromId($Id) {

        $result = $this->findOrganizationFromId($Id);
        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["STATUS"] = $result->STATUS;
            $data["NAME"] = setShowText($result->NAME);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }

    public function findOrganizationFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM " . self::T_ORGANIZATION . "";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return $this->DB_ACCESS->fetchRow($SQL);
    }

    public function allOrganizations($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $entries = $this->getAllOrganizationQuery($params);
        $data = array();
        $i = 0;
        if ($entries)
            foreach ($entries as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["STATUS"] = $value->STATUS;

                $i++;
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

    public function getAllOrganizationsQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.STATUS AS STATUS";
        $SQL .= " ,A.NAME AS NAME";
        $SQL .= " FROM " . self::T_ORGANIZATION . " AS A";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        $SQL .= " ORDER BY A.NAME";
        //echo $SQL;
        return $this->DB_ACCESS->fetchAll($SQL);
    }

    public function loadOrganizationFromId($Id) {

        $result = $this->findOrganizationFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getOrganizationDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function updateOrganization($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $SAVEDATA['NAME'] = addText($params["NAME"]);
        $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        $ROW = $this->DB_ACCESS->quoteInto('ID =?', $objectId);
        $this->DB_ACCESS->update(self::T_ORGANIZATION, $SAVEDATA, $ROW);
        return array("success" => true);
    }

    public function addOrganization($params) {

        $name = isset($params["name"]) ? addText($params["name"]) : 0;

        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
            $SAVEDATA['STATUS'] = 1;
        }
        $SAVEDATA['NAME'] = addText($params["name"]);


        if ($name)
            $this->DB_ACCESS->insert(self::T_ORGANIZATION, $SAVEDATA);
        
        return array("success" => true);
    }

    public function jsonTreeAllOrganizations($params) {

        $result = $this->getAllOrganizationsQuery($params);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = stripslashes($value->NAME);

                switch ($value->STATUS) {
                    case 0:
                        $data[$i]['iconCls'] = "icon-red";
                        break;
                    case 1:
                        $data[$i]['iconCls'] = "icon-green";
                        break;
                }

                $data[$i]['cls'] = "nodeTextBlue";
                $data[$i]['leaf'] = true;

                $i++;
            }

        return $data;
    }

    public function releaseOrganization($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = $this->findOrganizationFromId($objectId);

        $status = $facette->STATUS;

        $data = array();
        switch ($status) {
            case 0:
                $newStatus = 1;
                $data['STATUS'] = 1;
                $STAFF_DATA["STATUS"] = 1;

                break;
            case 1:
                $newStatus = 0;
                $data['STATUS'] = 0;
                $STAFF_DATA["STATUS"] = 0;

                break;
        }

        $this->DB_ACCESS->update(self::T_ORGANIZATION, $data, "ID='". $objectId ."'");

        return array("success" => true, "status" => $newStatus);
    }

    public function removeOrganization($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $check = $this->checkUserOrganization($removeId);

        if (!$check) {

            $this->deleteOrganizationFromId($removeId);
        }

        return array(
            "success" => true
        );
    }

    public function checkUserOrganization($Id) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM " . self::T_USER_ORGANIZATION . "";
        $SQL .= " WHERE";
        $SQL .= " ORGANIZATION_ID = '" . $Id . "'";

        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function loadUserOrganization($Id, $userId) {

        $SQL = "SELECT *";
        $SQL .= " FROM " . self::T_USER_ORGANIZATION . "";
        $SQL .= " WHERE";
        $SQL .= " ORGANIZATION_ID = '" . $Id . "'";
        $SQL .= " AND USER_ID = '" . $userId . "'";
        //echo $SQL;
        return $this->DB_ACCESS->fetchRow($SQL);
    }

    public function deleteOrganizationFromId($Id) {
        $this->DB_ACCESS->delete(self::T_ORGANIZATION, "ID = '" . $Id . "'");
    }

    public function assignedUserOrganization($params) {

        $userId = isset($params["userId"]) ? addText($params["userId"]) : 0;

        $SQL = "";
        $SQL .= " SELECT A.ID AS ID";
        $SQL .= " ,A.NAME AS NAME";
        $SQL .= " FROM " . self::T_ORGANIZATION . " AS A";
        $SQL .= " WHERE 1=1";

        //echo $SQL;
        $result = $this->DB_ACCESS->fetchAll($SQL);
        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $facette = $this->loadUserOrganization($value->ID, $userId);

                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;

                if ($facette) {
                    $data[$i]["ASSIGNED"] = 1;
                    $data[$i]["POSITION"] = $facette->POSITION;
                } else {
                    $data[$i]["ASSIGNED"] = 0;
                    $data[$i]["POSITION"] = "---";
                }

                $i++;
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function actionUserOrganization($params) {

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $organizationId = isset($params["id"]) ? addText($params["id"]) : 0;
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : 0;
        $userId = isset($params["userId"]) ? addText($params["userId"]) : "";

        switch ($field) {
            case "POSITION":
                $position = $newValue;
                $assigned = 1;
                break;
            default:
                $position = "---";
                $assigned = $newValue;
                break;
        }


        $whereSQL  = " 1=1 AND ORGANIZATION_ID='" . $organizationId . "'";
        $whereSQL .= " AND USER_ID='" . $userId . "'";

        $this->DB_ACCESS->delete(self::T_USER_ORGANIZATION, $whereSQL);

        $SAVEDATA['USER_ID'] = $userId;
        $SAVEDATA['ORGANIZATION_ID'] = $organizationId;
        $SAVEDATA['POSITION'] = addText($position);

        if ($assigned)
            $this->DB_ACCESS->insert(self::T_USER_ORGANIZATION, $SAVEDATA);

        $data["ASSIGNED"] = $assigned;
        $data["POSITION"] = $position;
        
        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function allOrganizationsComboData() {

        $result = $this->getAllOrganizationsQuery(false);

        $data[0] = "[\"0\",\"[---]\"]";
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }
}

?>