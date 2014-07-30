<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';

class CAMEMISHelpDBAccess {

    public $GuId = null;
    public $DB_DATABASE;

    function __construct($objectId = false)
    {

        $this->objectId = $objectId;
    }

    public static function dbAminAccess()
    {
        return Zend_Registry::get('ADMIN_DB_ACCESS');
    }

    public static function findHelp($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help", array("*"));

        if (is_numeric($Id))
        {
            $SQL->where("ID = ?", $Id);
        }
        else
        {
            $SQL->where("TEXT_KEY = '" . $Id . "'");
        }
        return self::dbAminAccess()->fetchRow($SQL);
    }

    public function findBlockIdByName($block)
    {

        $SQL = "SELECT ID";
        $SQL .= " FROM t_help";
        $SQL .= " WHERE";
        $SQL .= " NAME_EN = '" . $block . "'";
        //echo $SQL;
        $result = self::dbAminAccess()->fetchRow($SQL);
        return $result ? $result->ID : null;
    }

    public function checkChildren($parentId)
    {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_help";
        $SQL .= " WHERE";
        $SQL .= " PARENT = '" . $parentId . "'";
        //echo $SQL;
        $result = self::dbAminAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonLoadHelp($Id)
    {

        $facette = self::findHelp($Id);

        if ($facette)
        {

            $DATA["NAME_EN"] = $facette->NAME_EN;
            $DATA["NAME_VN"] = $facette->NAME_VN;
            $DATA["NAME_KH"] = $facette->NAME_KH;

            $DATA["YOUTUBE_KEY_EN"] = $facette->YOUTUBE_KEY_EN;
            $DATA["YOUTUBE_KEY_KH"] = $facette->YOUTUBE_KEY_KH;
            $DATA["YOUTUBE_KEY_VN"] = $facette->YOUTUBE_KEY_VN;
            $DATA["HELP_LINK_EN"] = $facette->HELP_LINK_EN;
            $DATA["HELP_LINK_VN"] = $facette->HELP_LINK_VN;
            $DATA["HELP_LINK_KH"] = $facette->HELP_LINK_KH;
        }

        $o = array(
            "success" => true
            , "data" => $DATA
        );
        return $o;
    }

    public static function treeUserHelps($params)
    {
        $node = isset($params["node"]) ? $params["node"] : "0";
        $key = isset($params["key"]) ? $params["key"] : "";

        $facette = self::findHelp($Id);

        $data = array();
//        $node = $params["node"];
//        $block = $params["block"];
//        $data = array();
//
//        $SQL = "SELECT * FROM t_help";
//        if ($node == 0)
//        {
//            $SQL .= " WHERE PARENT= " . $this->findBlockIdByName($block) . "";
//        }
//        else
//        {
//            $SQL .= " WHERE PARENT='" . $node . "'";
//        }
//
//        $SQL .= " ORDER BY NAME_EN";
//        $result = self::dbAminAccess()->fetchAll($SQL);
//
//        $i = 0;
//        foreach ($result as $value)
//        {
//
//            $data[$i]['text'] = $value->NAME_VN;
//            $data[$i]['id'] = "" . $value->ID . "";
//            $data[$i]['cls'] = "nodeFolderBold";
//            $data[$i]['treeType'] = $value->TREE_TYPE;
//
//            switch ($value->TREE_TYPE)
//            {
//                case "FOLDER":
//                    $data[$i]['leaf'] = false;
//                    $data[$i]['iconCls'] = "icon-folder_page";
//                    break;
//                case "ITEM":
//                    $data[$i]['leaf'] = true;
//                    $data[$i]['iconCls'] = "icon-page";
//                    break;
//            }
//
//            $i++;
//        }

        return $data;
    }

}

?>