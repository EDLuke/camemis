<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 10.04.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
//

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class TextDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new TextDBAccess();
        }
        return $me;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function selectedLanguage() {

        return Zend_Registry::get('SYSTEM_LANGUAGE');
    }

    public function getTranslationDataFromId($Id) {

        $field = Zend_Registry::get('SYSTEM_LANGUAGE');

        $data = array();

        $result = $this->findTranslationFromId($Id);
        if ($result) {
            $data["ID"] = $result->ID;
            $data["CONST"] = $result->CONST;
            $data["ENGLISH"] = stripslashes($result->ENGLISH);
            $data["" . $field . ""] = stripslashes($result->$field);
        }
        return $data;
    }

    public static function getMyText($const) {

        $CHOOSE_FIELD = Zend_Registry::get('SYSTEM_LANGUAGE') ? Zend_Registry::get('SYSTEM_LANGUAGE') : "ENGLISH";

        $SQL = "SELECT DISTINCT " . $CHOOSE_FIELD . " AS TEXT";
        $SQL .= " FROM t_localization";
        $SQL .= " WHERE";
        $SQL .= " CONST = '" . $const . "'";
        $SQL .= " AND IS_LONGTEXT = '1'";

        $result = self::dbAccess()->fetchRow($SQL);

        return isset($result) ? $result->TEXT : "---";
    }

    public function findTranslationFromId($Id) {
        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_localization";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadTranslationFromId($Id) {

        $result = $this->findTranslationFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getTranslationDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function allTranslations($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $not_translated = isset($params["not_translated"]) ? $params["not_translated"] : "";
        $show_all = isset($params["show_all"]) ? $params["show_all"] : "";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $field = Zend_Registry::get('SYSTEM_LANGUAGE');

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " ID, CONST, ENGLISH, " . $field . "";
        $SQL .= " FROM t_localization";
        $SQL .= " WHERE 1=1";

        if ($not_translated == "true") {
            $SQL .= " AND " . $field . "='' OR " . $field . " IS NULL";
        } elseif ($show_all == "true") {
            $SQL .= " AND ENGLISH !=''";
        } else {
            $SQL .= " AND ENGLISH !=''";
        }

        if ($globalSearch) {

            $SQL .= " AND ((ENGLISH LIKE '%" . $globalSearch . "%')";
            $SQL .= " OR (" . $field . " LIKE '%" . $globalSearch . "%')";
            $SQL .= " ) ";
        }

        $SQL .= " ORDER BY ENGLISH";
        //$SQL .= " ORDER BY CONST";
        //error_log($SQL);

        $resultCount = self::dbAccess()->fetchAll($SQL);
        $SQL .= " LIMIT " . $start . " , " . $limit . "";
        $resultEntries = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($resultEntries)
            while (list($key, $row) = each($resultEntries)) {

                $data[$i]["ID"] = $row->ID;
                $data[$i]["CONST"] = $row->CONST;
                $data[$i]["ENGLISH"] = $row->ENGLISH;
                $data[$i]["" . $field . ""] = $row->$field . "";

                $i++;
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($resultCount)
            , "rows" => $data
        );
    }

    public function updateTranslation($params) {

        $field = Zend_Registry::get('SYSTEM_LANGUAGE');

        if (isset($params["CONST"]))
            $SAVEDATA['CONST'] = trim(addText($params["CONST"]));
        if (isset($params["ENGLISH"]))
            $SAVEDATA['ENGLISH'] = trim(addText($params["ENGLISH"]));
        if (isset($params["" . $field . ""]))
            $SAVEDATA["" . $field . ""] = trim(addText($params["" . $field . ""]));

        $SAVEDATA['CHANGED'] = 1;
        $WHERE = self::dbAccess()->quoteInto("ID = ?", $params["objectId"]);
        self::dbAccess()->update('t_localization', $SAVEDATA, $WHERE);

        return array("success" => true);
    }

    public function addTranslation($params) {

        $SAVEDATA['ENGLISH'] = $params["ENGLISH"];
        self::dbAccess()->insert('t_localization', $SAVEDATA);
        return array("success" => true);
    }

    public function allTexts() {

        $CHOOSE_FIELD = Zend_Registry::get('SYSTEM_LANGUAGE') ? Zend_Registry::get('SYSTEM_LANGUAGE') : "ENGLISH";
        $SQL = "";
        $SQL .= " SELECT DISTINCT ID, CONST, ENGLISH, " . $CHOOSE_FIELD . "";
        $SQL .= " FROM t_localization";
        $SQL .= " WHERE IS_LONGTEXT=0";
        $SQL .= " GROUP BY CONST";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function usedContLocalization() {

        $SQL = "";
        $SQL .= " SELECT DISTINCT CONST";
        $SQL .= " FROM t_localization";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                $data[$value->CONST] = $value->CONST;
            }
        }

        return $data;
    }

    public function findLocalizationByConst($cost) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_localization";
        $SQL .= " WHERE";
        $SQL .= " CONST = '" . $cost . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

}

?>