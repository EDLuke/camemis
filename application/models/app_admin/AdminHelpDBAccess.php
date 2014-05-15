<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/app_admin/AdminSessionAccess.php';
require_once 'models/app_admin/AdminDatabaseDBAccess.php';

class AdminHelpDBAccess {

    public $objectId = null;
    public $DB_DATABASE;

    function __construct() {
        $this->DB_DATABASE = new AdminDatabaseDBAccess();
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public function __get($name) {
        if (Array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function __isset($name) {
        return Array_key_exists($name, $this->data);
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }

    public static function findHelp($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_help", array("*"));

        if (is_numeric($Id)) {
            $SQL->where("ID = '" . $Id . "'");
        } else {
            $SQL->where("TEXT_KEY = '" . $Id . "'");
        }

        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonLoadHelp($Id) {

        $facette = self::findHelp($Id);

        if ($facette) {

            $DATA["NAME_ENGLISH"] = $facette->NAME_ENGLISH;
            $DATA["NAME_KHMER"] = $facette->NAME_KHMER ? $facette->NAME_KHMER : $facette->NAME_ENGLISH;
            $DATA["NAME_VIETNAMESE"] = $facette->NAME_VIETNAMESE ? $facette->NAME_VIETNAMESE : $facette->NAME_ENGLISH;

            $DATA["YOUTUBE_KEY_ENGLISH"] = $facette->YOUTUBE_KEY_ENGLISH;
            $DATA["YOUTUBE_KEY_KHMER"] = $facette->YOUTUBE_KEY_KHMER ? $facette->YOUTUBE_KEY_KHMER : $facette->YOUTUBE_KEY_ENGLISH;
            $DATA["YOUTUBE_KEY_VIETNAMESE"] = $facette->YOUTUBE_KEY_VIETNAMESE ? $facette->YOUTUBE_KEY_VIETNAMESE : $facette->YOUTUBE_KEY_ENGLISH;

            $DATA["TEXT_KEY"] = $facette->TEXT_KEY;
            $DATA["CONTENT_ENGLISH"] = $facette->CONTENT_ENGLISH;
            $DATA["CONTENT_KHMER"] = $facette->CONTENT_KHMER;
            $DATA["CONTENT_VIETNAMESE"] = $facette->CONTENT_VIETNAMESE;
        }

        $o = array(
            "success" => true
            , "data" => $DATA
        );
        return $o;
    }

    public function addFolder($params) {
        if (isset($params["name"]))
            $SAVE_DATA['NAME_ENGLISH'] = addText($params["name"]);
        self::dbAccess()->insert('t_help', $SAVE_DATA);
        return array(
            "success" => true
        );
    }

    public function jsonSaveHelp($params) {

        $Id = isset($params["objectId"]) ? addText($params["objectId"]) : "0";

        if (isset($params["NAME_ENGLISH"]))
            $SAVE_DATA["NAME_ENGLISH"] = addText($params["NAME_ENGLISH"]);

        if (isset($params["NAME_KHMER"]))
            $SAVE_DATA["NAME_KHMER"] = addText($params["NAME_KHMER"]);

        if (isset($params["NAME_VIETNAMESE"]))
            $SAVE_DATA["NAME_VIETNAMESE"] = addText($params["NAME_VIETNAMESE"]);

        if (isset($params["YOUTUBE_KEY_ENGLISH"]))
            $SAVE_DATA["YOUTUBE_KEY_ENGLISH"] = addText($params["YOUTUBE_KEY_ENGLISH"]);

        if (isset($params["YOUTUBE_KEY_KHMER"]))
            $SAVE_DATA["YOUTUBE_KEY_KHMER"] = addText($params["YOUTUBE_KEY_KHMER"]);

        if (isset($params["YOUTUBE_KEY_VIETNAMESE"]))
            $SAVE_DATA["YOUTUBE_KEY_VIETNAMESE"] = addText($params["YOUTUBE_KEY_VIETNAMESE"]);

        if (isset($params["TEXT_KEY"]))
            $SAVE_DATA["TEXT_KEY"] = addText($params["TEXT_KEY"]);

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
        self::dbAccess()->update('t_help', $SAVE_DATA, $WHERE);

        return array(
            "success" => true
        );
    }

    public static function actionSaveContent($params) {

        $Id = isset($params["objectId"]) ? addText($params["objectId"]) : "0";
        $SAVE_DATA = Array();
        
        if (isset($params["CONTENT_ENGLISH"]))
            $SAVE_DATA["CONTENT_ENGLISH"] = addText($params["CONTENT_ENGLISH"]);

        if (isset($params["CONTENT_KHMER"]))
            $SAVE_DATA["CONTENT_KHMER"] = addText($params["CONTENT_KHMER"]);

        if (isset($params["CONTENT_VIETNAMESE"]))
            $SAVE_DATA["CONTENT_VIETNAMESE"] = addText($params["CONTENT_VIETNAMESE"]);

        $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
        self::dbAccess()->update('t_help', $SAVE_DATA, $WHERE);
    }

    public function jsonTreeHelps($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $data = array();

        $SQL = "SELECT * FROM t_help";
        $SQL .= " ORDER BY NAME_ENGLISH";
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        foreach ($result as $value) {

            $data[$i]['text'] = $value->NAME_ENGLISH;
            $data[$i]['id'] = "" . $value->ID . "";
            $data[$i]['cls'] = "nodeText";
            $data[$i]['iconCls'] = "icon-application_form_magnify";
            $data[$i]['leaf'] = true;
            $i++;
        }

        return $data;
    }

    public function jsonRemoveHelp($Id) {

        $condition = array(
            'ID = ? ' => $Id
        );
        self::dbAccess()->delete('t_help', $condition);

        return array(
            "success" => true
        );
    }

}

?>