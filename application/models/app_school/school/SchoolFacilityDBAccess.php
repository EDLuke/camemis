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

class SchoolFacilityDBAccess {

    public $data = array();

    static function getInstance() {
        static $me;

        if ($me == null) {
            $me = new SchoolFacilityDBAccess();
        }

        return $me;
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
            $data["CHOOSE_TYPE"] = $result->CHOOSE_TYPE;
            $data["TYPE"] = $result->TYPE;
            $data["NAME"] = setShowText($result->NAME);
            $data["SORTKEY"] = $result->SORTKEY;
            $data["FACILITY_TYPE"] = $result->TYPE;
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["COUNT"] = $result->COUNT;
        }

        return $data;
    }

    public static function findObjectFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_school_facility";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    public static function loadObject($Id) {

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

    public static function addObject($params) {

        $SAVEDATA = array();
        $SAVEDATA['NAME'] = addText($params["NAME"]);

        if ($params["parentId"] > 0) {
            $facette = self::findObjectFromId($params["parentId"]);
            $SAVEDATA['PARENT'] = (int) $params["parentId"];
            $SAVEDATA['OBJECT_TYPE'] = "ITEM";
            if ($facette) {
                $SAVEDATA['TYPE'] = $facette->TYPE;
                $SAVEDATA['CHOOSE_TYPE'] = 1;
            }
        } else {
            $SAVEDATA['PARENT'] = 0;
            if (isset($params["CHOOSE_TYPE"]))
                $SAVEDATA['CHOOSE_TYPE'] = addText($params["CHOOSE_TYPE"]);
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";
        }
        self::dbAccess()->insert('t_school_facility', $SAVEDATA);
        return array("success" => true);
    }

    public static function removeObject($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findObjectFromId($removeId);

        if ($facette) {
            if ($facette->OBJECT_TYPE == "FOLDER") {
                if ($facette->PARENT) {
                    self::sqlRemoveObject($facette->PARENT);
                }
            }
        }

        self::sqlRemoveObject($removeId);
        return array("success" => true);
    }

    public static function sqlRemoveObject($Id) {

        $SQL = "DELETE FROM t_school_facility";
        $SQL .= " WHERE";
        $SQL .= " ID='" . $Id . "'";
        self::dbAccess()->query($SQL);
    }

    public static function updateObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if ($objectId != "new") {
            $facette = self::findObjectFromId($objectId);

            $SQL = "UPDATE t_school_facility";
            $SQL .= " SET NAME='" . addText($params["NAME"]) . "'";
            if (isset($params["CHOOSE_TYPE"]))
                $SQL .= " ,CHOOSE_TYPE='" . addText($params["CHOOSE_TYPE"]) . "'";
            $SQL .= " ,SORTKEY='" . $params["SORTKEY"] . "'";

            if ($facette->PARENT > 0) {
                $parentObject = self::findObjectFromId($facette->PARENT);
                $SQL .= " ,TYPE='" . $parentObject->TYPE . "'";
            } else {
                if (isset($params["FACILITY_TYPE"]))
                    $SQL .= " ,TYPE='" . $params["FACILITY_TYPE"] . "'";
            }

            if (isset($params["COUNT"]))
                $SQL .= " ,COUNT='" . $params["COUNT"] . "'";
            if (isset($params["DESCRIPTION"]))
                $SQL .= " ,DESCRIPTION='" . addText($params["DESCRIPTION"]) . "'";
            $SQL .= " WHERE";
            $SQL .= " ID='" . addText($params["objectId"]) . "'";
            self::dbAccess()->query($SQL);
        }else {
            self::addObject($params);
        }

        return array("success" => true);
    }

    public static function sqlSchoolFacilty($node, $facilityType = false, $chooseType = false) {

        $SQL = "SELECT * FROM t_school_facility";
        $SQL .= " WHERE 1=1";
        if (!$node)
            $SQL .= " AND PARENT=0";
        else
            $SQL .= " AND PARENT=" . $node . "";

        if ($facilityType)
            $SQL .= " AND TYPE='" . $facilityType . "'";

        if ($chooseType)
            $SQL .= " AND CHOOSE_TYPE='" . $chooseType . "'";

        $SQL .= " ORDER BY SORTKEY ASC";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllFacilities($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $facilityType = isset($params["facilityType"]) ? $params["facilityType"] : '';

        $data = array();
        $result = self::sqlSchoolFacilty($node, $facilityType, false);

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                $data[$i]['id'] = $value->ID;

                switch ($value->OBJECT_TYPE) {
                    case "FOLDER":
                        $data[$i]['text'] = $value->NAME;
                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        break;
                    case "ITEM":

                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeText";
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        if ($value->TYPE == "SCHOOL") {
                            $data[$i]['text'] = $value->NAME . " (" . $value->COUNT . ")";
                        } else {
                            $data[$i]['text'] = $value->NAME;
                        }
                        break;
                }
                $i++;
            }
        }

        return $data;
    }

    public static function updateChildren($parentId) {

        $facette = self::findObjectFromId($parentId);

        $SQL = "SELECT * FROM t_school_facility";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND PARENT=" . $parentId . "";
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                $SQL = "UPDATE t_school_facility";
                $SQL .= " SET CHOOSE_TYPE='" . $facette->CHOOSE_TYPE . "'";
                $SQL .= ", TYPE='" . $facette->TYPE . "'";
                $SQL .= " WHERE";
                $SQL .= " PARENT='" . $value->PARENT . "'";
                self::dbAccess()->query($SQL);
            }
        }
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_facility", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>