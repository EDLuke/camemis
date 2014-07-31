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

class DescriptionDBAccess {

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
            $data["SORTKEY"] = $result->SORTKEY;//@veasna
        }

        return $data;
    }

    public static function findObjectFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_personal_description";
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

        if (isset($params["personType"]))
            $SAVEDATA['PERSON'] = $params["personType"];
        if (isset($params["SORTKEY"]))//@veasna
            $SAVEDATA['SORTKEY'] = $params["SORTKEY"];

        if ($params["parentId"] > 0) {
            $SAVEDATA['PARENT'] =  addText($params["parentId"]);
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
        self::dbAccess()->insert('t_personal_description', $SAVEDATA);
        return array("success" => true);
    }

    public static function removeObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findObjectFromId($objectId);

        if ($facette) {
            self::dbAccess()->delete('t_personal_description', array("ID='" . $objectId . "'"));

            if ($facette->OBJECT_TYPE == 'FOLDER') {
                self::dbAccess()->delete('t_personal_description', array("PARENT='" . $objectId . "'"));
            }
        }

        return array("success" => true);
    }

    public static function updateObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if ($objectId != "new") {
            $data         = array();
            $data['NAME'] = "'". addText($params["NAME"]) ."'";
            if (isset($params["CHOOSE_TYPE"]))
                $data['CHOOSE_TYPE'] = "'". addText($params["CHOOSE_TYPE"]) ."'";
            if (isset($params["SORTKEY"]))//@veasna
                $data['SORTKEY']     = "'". $params["SORTKEY"] ."'";
            self::dbAccess()->update("t_personal_description", $data, "ID='". addText($params["objectId"]) ."'");
        }else {
            self::addObject($params);
        }

        return array("success" => true);
    }
    public static function sqlPersonalDescription($personType) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_personal_description', array('*'));
        $SQL->where("OBJECT_TYPE='ITEM'");
        $SQL->where("PERSON = '" . $personType . "'"); 
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    public static function sqlDescription($node, $personType, $type = false) {

        $SQL = "SELECT * FROM t_personal_description";
        $SQL .= " WHERE 1=1";

        if (!$node) {
            $SQL .= " AND PARENT=0";
        } else {
            $SQL .= " AND PARENT='" . $node . "'";
        }

        $SQL .= " AND PERSON='" . $personType . "'";

        if ($type)
            $SQL .= " AND CHOOSE_TYPE='" . $type . "'";
        $SQL .= " ORDER BY SORTKEY ASC";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllDescription($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $personType = isset($params["personType"]) ? $params["personType"] : 0;

        $data = array();
        $result = self::sqlDescription($node, $personType);

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                $data[$i]['id'] = $value->ID;

                switch ($value->OBJECT_TYPE) {
                    case "FOLDER":
                        switch ($value->CHOOSE_TYPE) {
                            case 1:
                                $data[$i]['text'] = $value->NAME . " (Checkbox)";
                                break;
                            case 2:
                                $data[$i]['text'] = $value->NAME . " (Radiobox)";
                                break;
                            case 3:
                                $data[$i]['text'] = $value->NAME . " (Inputfield)";
                                break;
                            case 4:
                                $data[$i]['text'] = $value->NAME . " (Textarea)";
                                break;
                            case 5:
                                $data[$i]['text'] = $value->NAME . " (Text)";
                                break;
                        }
                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        self::updateChildren($value->ID);
                        break;
                    case "ITEM":
                        $data[$i]['text'] = $value->NAME;
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";
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
        
        $SQL = "SELECT * FROM t_personal_description";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND PARENT='" . $parentId . "'";
        $result = self::dbAccess()->fetchAll($SQL);

       if ($result) {
            foreach ($result as $value) {
                self::dbAccess()->update("t_personal_description", array('CHOOSE_TYPE' => $facette->CHOOSE_TYPE), "ID=". $value->ID);
            }
        }
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_personal_description", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function allPersonalDescriptionComboData($parent, $personType, $type = false) {

        $data = array();
        $result = self::sqlDescription($parent, $personType, $type);

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