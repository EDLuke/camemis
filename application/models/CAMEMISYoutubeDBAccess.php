<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';

class CAMEMISYoutubeDBAccess {

    function __construct() {
        
    }

    static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function findVideo($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_school_video', '*');
        $SQL->where("ID = ?",$Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function actionYoutube($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $chooseId = isset($params["id"]) ? addText($params["id"]) : "";

        $entries = self::sqlVideos($params);
        $count = count($entries) + 1;

        $SAVEDATA = array();

        if (!$field && !$chooseId) {
            if (isset($params["name"]))
                $SAVEDATA["NAME"] = addText($params["name"]);
            $SAVEDATA["OBJECT_ID"] = $objectId;
            $SAVEDATA["POSITION"] = $count;
            $SAVEDATA["SCHOOL_URL"] = Zend_Registry::get('SERVER_NAME');
            self::dbAccess()->insert("t_school_video", $SAVEDATA);
        }else {
            $SAVEDATA["" . $field . ""] = $newValue;
            $WHERE = array();
            $WHERE[] = self::dbAccess()->quoteInto('ID = ?', $chooseId);
            self::dbAccess()->update('t_school_video', $SAVEDATA, $WHERE);
        }
        return array(
            "success" => true
        );
    }

    public static function removeYoutube($Id) {

        if ($Id)
            self::dbAccess()->delete('t_school_video', array("ID='" . $Id . "'"));

        return array(
            "success" => true
        );
    }

    public static function sqlVideos($params) {

        $objectId = $params["objectId"] ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from('t_school_video', '*');
        if ($objectId) $SQL->where("OBJECT_ID = ?",$objectId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function loadAllYoutube($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = self::sqlVideos($params);

        $data = array();
        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["YOUTUBE"] = $value->YOUTUBE;
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

}

?>