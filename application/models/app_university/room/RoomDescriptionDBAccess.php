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

class RoomDescriptionDBAccess {

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
            $data["CHOOSE_TYPE"] = $result->CHOOSE_TYPE;
            $data["NAME"] = setShowText($result->NAME);
        }

        return $data;
    }

    public static function findObjectFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_room_description";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    public static function loadRoomDescription($Id) {

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

    public static function removeRoomDescription($params) {

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

        $SQL = "DELETE FROM t_room_description";
        $SQL .= " WHERE";
        $SQL .= " ID='" . $Id . "'";
        self::dbAccess()->query($SQL);
    }

    public static function addObject($params) {

        $SAVEDATA = array();
        $SAVEDATA['NAME'] = addText($params["NAME"]);

        if ($params["parentId"] > 0) {
            $SAVEDATA['PARENT'] = $params["parentId"];
            $SAVEDATA['OBJECT_TYPE'] = "ITEM";
            $facette = self::findObjectFromId($params["parentId"]);
            if ($facette) {
                $SAVEDATA['CHOOSE_TYPE'] = $facette->CHOOSE_TYPE;
            }
        } else {
            $SAVEDATA['PARENT'] = 0;
            if (isset($params["CHOOSE_TYPE"]))
                $SAVEDATA['CHOOSE_TYPE'] = addText($params["CHOOSE_TYPE"]);
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";
        }
        self::dbAccess()->insert('t_room_description', $SAVEDATA);
        return array("success" => true);
    }

    public static function saveRoomDescription($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if ($objectId != "new") {
            $SQL = "UPDATE t_room_description";
            $SQL .= " SET NAME='" . addText($params["NAME"]) . "'";
            if (isset($params["CHOOSE_TYPE"]))
                $SQL .= " ,CHOOSE_TYPE='" . $params["CHOOSE_TYPE"] . "'";
            $SQL .= " WHERE";
            $SQL .= " ID='" . $params["objectId"] . "'";
            self::dbAccess()->query($SQL);
        }else {
            self::addObject($params);
        }

        return array("success" => true);
    }

    public static function sqlDescription($node, $type = false) {

        $SQL = "SELECT * FROM t_room_description";
        $SQL .= " WHERE 1=1";
        if (!$node)
            $SQL .= " AND PARENT=0";
        else
            $SQL .= " AND PARENT=" . $node . "";

        if ($type)
            $SQL .= " AND CHOOSE_TYPE='" . $type . "'";
        $SQL .= " ORDER BY SORTKEY ASC";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllRoomDescription($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        $data = array();
        $result = self::sqlDescription($node);

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                $data[$i]['text'] = $value->NAME;
                $data[$i]['id'] = $value->ID;

                switch ($value->OBJECT_TYPE) {
                    case "FOLDER":
                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        self::updateChildren($value->ID);
                        break;
                    case "ITEM":
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeText";
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        break;
                }
                $i++;
            }
        }

        return $data;
    }

    public static function updateChildren($parentId) {

        $facette = self::findObjectFromId($parentId);

        $SQL = "SELECT * FROM t_room_description";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND PARENT=" . $parentId . "";
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                $SQL = "UPDATE t_room_description";
                $SQL .= " SET CHOOSE_TYPE='" . $facette->CHOOSE_TYPE . "'";
                $SQL .= " WHERE";
                $SQL .= " ID='" . $value->ID . "'";
                self::dbAccess()->query($SQL);
            }
        }
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_room_description", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>