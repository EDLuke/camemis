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

    function __construct($objectId = false) {

        $this->objectId = $objectId;
        $this->DB_ACCESS = Zend_Registry::get('ADMIN_DB_ACCESS');
    }

    public function findHelp() {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_help";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $this->objectId . "'";
        //echo $SQL;
        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result;
    }

    public function findBlockIdByName($block) {

        $SQL = "SELECT ID";
        $SQL .= " FROM t_help";
        $SQL .= " WHERE";
        $SQL .= " NAME_EN = '" . $block . "'";
        //echo $SQL;
        $result = $this->DB_ACCESS->fetchRow($SQL);
        return $result ? $result->ID : null;
    }

    public function checkChildren($parentId) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_help";
        $SQL .= " WHERE";
        $SQL .= " PARENT = '" . $parentId . "'";
        //echo $SQL;
        $result = $this->DB_ACCESS->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonLoadHelp($Id) {

        $this->objectId = $Id;
        $facette = $this->findHelp();

        if ($facette) {

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

    public function jsonTreeHelps($params) {

        $node = $params["node"];
        $block = $params["block"];
        $data = array();

        $SQL = "SELECT * FROM t_help";
        if ($node == 0) {
            $SQL .= " WHERE PARENT= " . $this->findBlockIdByName($block) . "";
        } else {
            $SQL .= " WHERE PARENT='" . $node . "'";
        }

        $SQL .= " ORDER BY NAME_EN";
        $result = $this->DB_ACCESS->fetchAll($SQL);

        $i = 0;
        foreach ($result as $value) {

//            switch($chooseLanguage){
//                case "EN":
//                    $data[$i]['text'] = $value->NAME_EN;
//                    break;
//                case "KH":
//                    $data[$i]['text'] = $value->NAME_KH;
//                    break;
//                case "VN":
//                    $data[$i]['text'] = $value->NAME_VN;
//                    break;
//                default:
//                    $data[$i]['text'] = $value->NAME_VN;
//                    break;
//            }
            $data[$i]['text'] = $value->NAME_VN;
            $data[$i]['id'] = "" . $value->ID . "";
            $data[$i]['cls'] = "nodeFolderBold";
            $data[$i]['treeType'] = $value->TREE_TYPE;

            switch ($value->TREE_TYPE) {
                case "FOLDER":
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_page";
                    break;
                case "ITEM":
                    $data[$i]['leaf'] = true;
                    $data[$i]['iconCls'] = "icon-page";
                    break;
            }

            $i++;
        }

        return $data;
    }

}

?>