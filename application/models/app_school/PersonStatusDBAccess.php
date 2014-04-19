<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class PersonStatusDBAccess {

    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getObjectDataFromId($Id) {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result) {

            $data["ID"] = $result->ID;
            $data["SHORT"] = setShowText($result->SHORT);
            $data["COLOR"] = setShowText($result->COLOR);
            $data["OBJECT_TYPE"] = $result->OBJECT_TYPE;
            $data["NAME"] = setShowText($result->NAME);
            $data["DISPLAY_DATE"] = $result->DISPLAY_DATE;
            $data["DEACTIVATE_ACCOUNT"] = $result->DEACTIVATE_ACCOUNT ? true : false;
            $data["NO_DELETE"] = $result->NO_DELETE ? true : false;
        }

        return $data;
    }

    public static function findObjectFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_person_status";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadPersonStatus($Id) {

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

    public static function createOnlyItem($params) {

        $name = isset($params["name"]) ? addText($params["name"]) : "";
        $objectType = isset($params["objectType"]) ? $params["objectType"] : "";

        $SQL = "
            INSERT INTO t_person_status SET
            NAME = '" . addText($name) . "'
            ,OBJECT_TYPE = '" . addText($objectType) . "'
            ";
        self::dbAccess()->query($SQL);

        return array("success" => true);
    }

    public static function jsonTreeAllPersonStatus($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectType = isset($params["objectType"]) ? $params["objectType"] : "";

        $data = array();
        $result = self::getAllPersonStatus($objectType, $globalSearch);

        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = "(" . setShowText($value->SHORT) . ") " . setShowText($value->NAME);
                $data[$i]['iconCls'] = "icon-application_form_magnify";
                $data[$i]['cls'] = "nodeTextBlue";
                $data[$i]['leaf'] = true;

                $i++;
            }
        }
        return $data;
    }

    public static function getAllPersonStatus($objectType, $globalSearch) {

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_person_status";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        switch ($objectType) {
            case "STUDENT":
                $SQL .= " AND OBJECT_TYPE = 'STUDENT'";
                break;
            case "STAFF":
                $SQL .= " AND OBJECT_TYPE = 'STAFF'";
        }
        $SQL .= " ORDER BY NAME";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonSavePersonStatus($params) {

        $SAVEDATA = array();
        $WHERE = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findObjectFromId($objectId);

        if (isset($params["NAME"])) {
            $SAVEDATA['NAME'] = addText($params["NAME"]);
        }

        if (isset($params["objectType"]))
            $SAVEDATA['OBJECT_TYPE'] = addText($params["objectType"]);

        if (isset($params["DISPLAY_DATE"]))
            $SAVEDATA['DISPLAY_DATE'] = addText($params["DISPLAY_DATE"]);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        $SAVEDATA['DEACTIVATE_ACCOUNT'] = isset($params["DEACTIVATE_ACCOUNT"]) ? 1 : 0;

        if ($facette) {
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_person_status', $SAVEDATA, $WHERE);
        } else {
            self::dbAccess()->insert('t_person_status', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function jsonRemovePersonStatus($Id) {
        self::dbAccess()->delete('t_person_status', array("ID='" . $Id . "'"));
        return array(
            "success" => true
        );
    }

    public static function allPersonStatusComboData($objectType, $marching = false) {

        $data = array();
        $result = self::getAllPersonStatus($objectType, false);

        $data[0] = "[\"0\",\"[---]\"]";
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if ($marching) {
                    $data[$i + 1] = "[\"" . $value->ID . "|" . $value->DISPLAY_DATE . "\",\"" . addslashes($value->NAME) . "\"]";
                } else {
                    $data[$i + 1] = "[\"" . $value->ID . "\",\"" . addslashes($value->NAME) . "\"]";
                }

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

}

?>