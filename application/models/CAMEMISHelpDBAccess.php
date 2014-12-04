<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';

class CAMEMISHelpDBAccess {

    function __construct()
    {
        
    }

    public static function dbAminAccess()
    {
        return Zend_Registry::get('ADMIN_DB_ACCESS');
    }

    public static function findHelp($Id)
    {

        $SQL = self::dbAminAccess()->select();
        $SQL->from("t_help", array("*"));

        if (is_numeric($Id))
        {
            $SQL->where("ID = ?", "" . $Id . "");
        }
        else
        {
            $SQL->where("TEXT_KEY = '" . $Id . "'");
        }
        return self::dbAminAccess()->fetchRow($SQL);
    }

    public static function getSQLHelp($params)
    {
        $Id = isset($params["parent"]) ? $params["parent"] : 0;
        $SQL = self::dbAminAccess()->select();
        $SQL->from("t_help", array("*"));
        if (self::checkChild($Id))
        {
            $SQL->where("PARENT = ?", $Id);
        }
        else
        {
            $SQL->where("ID = ?", $Id);
        }
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        return self::dbAminAccess()->fetchAll($SQL);
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

    public static function checkChild($Id)
    {

        $SQL = self::dbAminAccess()->select();
        $SQL->from("t_help", array("C" => "COUNT(*)"));
        if ($Id)
            $SQL->where("PARENT = ?", $Id);
        //error_log($SQL);
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

    public static function findFirsKeytHelpContent($key)
    {

        $facette = self::findHelp($key);
        $newKey = "";
        if ($facette)
        {
            $SQL = self::dbAminAccess()->select();
            $SQL->from("t_help", array("*"));
            $SQL->where("ID = ?", $facette->ID);
            $SQL->order("SORTKEY ASC");
            $SQL->limitPage(0, 1);
            $result = self::dbAminAccess()->fetchRow($SQL);
            if ($result)
            {
                $newKey = $result->TEXT_KEY;
            }
        }

        return $newKey;
    }

    public static function treeUserHelps($params)
    {
        $node = isset($params["node"]) ? $params["node"] : "0";
        $key = isset($params["key"]) ? $params["key"] : "";
        $SQL = self::dbAminAccess()->select();
        $SQL->from("t_help", array("*"));

        $facette = self::findHelp($key);
        if ($node == 0)
        {
            $SQL->where("ID = ?", $facette->ID);
        }
        else
        {
            $SQL->where("PARENT = ?", $node);
        }
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        $entries = self::dbAminAccess()->fetchAll($SQL);

        $i = 0;
        if ($entries)
            foreach ($entries as $value)
            {
                if (self::checkChild($value->ID))
                {

                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME_ENGLISH);
                    $data[$i]['iconCls'] = "icon-book";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                }
                else
                {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME_ENGLISH);
                    $data[$i]['leaf'] = true;
                    $data[$i]['iconCls'] = "icon-book_open";
                    $data[$i]['cls'] = "nodeTextBlue";
                }

                $i++;
            }

        return $data;
    }

}

?>