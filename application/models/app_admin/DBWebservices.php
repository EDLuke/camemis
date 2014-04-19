<?php

/**
 * KAOM Vibolrith
 * Vikensoft UG
 * Am Stollhenn 18, 55120 Mainz
 * Germany 
 */
class DBWebservices {

    public $GuId = null;
    public $DB_DATABASE;
    public $schoolName;

    function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function getCAMEMISDatabases() {

        $SQL = "SHOW DATABASES";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                if (substr($value->Database, 0, 8) == "camemis_") {
                    $data[$value->Database] = $value->Database;
                } elseif (substr($value->Database, 0, 4) == "cam_") {
                    $data[$value->Database] = $value->Database;
                }
            }
        }

        return $data;
    }

    public static function checkUseTable($dbname, $talbename) {

        $SQL = "SHOW TABLES FROM " . $dbname . " LIKE '" . $talbename . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? true : false;
    }

    public static function checkUseTableColumn($dbName, $tableName, $columnName) {

        $SQL = "SHOW COLUMNS FROM $dbName.$tableName LIKE '" . $columnName . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? true : false;
    }

    public static function checkUseConst($dbName, $constName, $noDBName = false) {

        if ($noDBName) {
            $SQL = "SELECT COUNT(*) AS C FROM t_localization WHERE CONST='" . $constName . "'";
            $result = self::dbAccess()->fetchRow($SQL);
        } else {
            $SQL = "SELECT COUNT(*) AS C FROM $dbName.t_localization WHERE CONST='" . $constName . "'";
            $result = self::dbAccess()->fetchRow($SQL);
        }

        return $result ? $result->C : 0;
    }

    public static function getListLocalization() {

        $SQL = "SELECT DISTINCT * FROM t_localization";
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public static function jsonActionDBTable($params) {

        $dbAction = isset($params["dbAction"]) ? trim($params["dbAction"]) : "";
        $TABLE_NAME = isset($params["TABLE_NAME"]) ? trim($params["TABLE_NAME"]) : null;
        $SQL_STRING = isset($params["SQL"]) ? trim($params["SQL"]) : null;

        $entries = self::getCAMEMISDatabases();

        if ($entries) {
            foreach ($entries as $DB_NAME) {
                switch ($dbAction) {
                    case "addTable":
                        if (!self::checkUseTable($DB_NAME, $TABLE_NAME)) {
                            if ($DB_NAME != "camemis_admin") {

                                $DB_PARAMS = array(
                                    'host' => 'localhost',
                                    'username' => Zend_Registry::get('CHOOSE_DB_USER'),
                                    'password' => Zend_Registry::get('CHOOSE_DB_PWD'),
                                    'dbname' => $DB_NAME
                                );
                                $DB_ACCESS = Zend_Db::factory('PDO_MYSQL', $DB_PARAMS);
                                $DB_ACCESS->setFetchMode(Zend_Db::FETCH_OBJ);

                                $SQL = str_replace($TABLE_NAME, $DB_NAME . "." . $TABLE_NAME, $SQL_STRING);
                                $SQL = str_replace("`", "", $SQL);

                                $DB_ACCESS->query($SQL);
                            }
                        }
                        break;
                    case "updateTable":
                    case "deleteTable":
                        if (self::checkUseTable($DB_NAME, $TABLE_NAME)) {
                            if ($DB_NAME != "camemis_admin") {
                                $SQL = str_replace($TABLE_NAME, $DB_NAME . "." . $TABLE_NAME, $SQL_STRING);
                                $SQL = str_replace("`", "", $SQL);
                                self::dbAccess()->query($SQL);
                            }
                        }
                        break;
                }
            }
        }
        return array("success" => true);
    }

    public static function jsonActionDBTableColumn($params) {

        $dbAction = isset($params["dbAction"]) ? trim($params["dbAction"]) : "";

        $TABLE_NAME = isset($params["TABLE_NAME"]) ? trim($params["TABLE_NAME"]) : null;
        $COLUMN_NAME = isset($params["COLUMN_NAME"]) ? trim($params["COLUMN_NAME"]) : null;
        $SQL_STRING = isset($params["SQL"]) ? trim($params["SQL"]) : null;

        $entries = self::getCAMEMISDatabases();

        if ($entries) {

            foreach ($entries as $DB_NAME) {
                if (self::checkUseTable($DB_NAME, $TABLE_NAME)) {

                    switch ($dbAction) {
                        case "addColumn":
                            if (!self::checkUseTableColumn($DB_NAME, $TABLE_NAME, $COLUMN_NAME)) {
                                $SQL = str_replace($TABLE_NAME, $DB_NAME . "." . $TABLE_NAME, $SQL_STRING);
                                $SQL = str_replace("`", "", $SQL);
                                self::dbAccess()->query($SQL);
                            }
                            break;
                        case "updateColumn":
                            if (self::checkUseTableColumn($DB_NAME, $TABLE_NAME, $COLUMN_NAME)) {
                                $SQL = str_replace($TABLE_NAME, $DB_NAME . "." . $TABLE_NAME, $SQL_STRING);
                                $SQL = str_replace("`", "", $SQL);
                                self::dbAccess()->query($SQL);
                            }
                            break;
                        case "deleteColumn":
                            if (self::checkUseTableColumn($DB_NAME, $TABLE_NAME, $COLUMN_NAME)) {
                                $SQL = "ALTER TABLE $DB_NAME.$TABLE_NAME DROP $COLUMN_NAME";
                                self::dbAccess()->query($SQL);
                            }
                            break;
                    }
                }
            }
        }
        return array("success" => true);
    }

    public static function jsonDeleteLocalization($params) {
        $CONST = isset($params["CONST"]) ? trim($params["CONST"]) : null;
        $entries = self::getCAMEMISDatabases();

        if ($CONST) {
            self::dbAccess()->query("DELETE FROM t_localization WHERE CONST='" . $CONST . "'");
            if ($entries) {

                foreach ($entries as $DB_NAME) {
                    if (self::checkUseTable($DB_NAME, "t_localization")) {
                        if (self::checkUseConst($DB_NAME, $CONST)) {
                            $SQL = "DELETE FROM $DB_NAME.t_localization WHERE CONST='" . $CONST . "'";
                            self::dbAccess()->query($SQL);
                        }
                    }
                }
            }
        }

        return array("success" => true);
    }

    public static function jsonActionLocalization($params) {

        $CONST = isset($params["CONST"]) ? trim($params["CONST"]) : "";
        $LANGUAGE = isset($params["language"]) ? trim($params["language"]) : "";
        $POST_CONTENT = isset($params["CHOOSE_LANGUAGE"]) ? trim($params["CHOOSE_LANGUAGE"]) : "";

        if (self::checkUseConst('t_localization', $CONST, true)) {
            $adminSQL = "UPDATE t_localization SET";
            $adminSQL .= " $LANGUAGE='" . addslashes(stripslashes($POST_CONTENT)) . "'";
            $adminSQL .= " WHERE CONST='" . $CONST . "'";
            self::dbAccess()->query($adminSQL);
        } else {
            $adminSQL = "INSERT INTO t_localization SET";
            $adminSQL .= " ENGLISH='" . addslashes(stripslashes($POST_CONTENT)) . "'";
            $adminSQL .= " ,CONST='" . $CONST . "'";
            self::dbAccess()->query($adminSQL);
        }

        $entries = self::getCAMEMISDatabases();

        if ($entries) {
            foreach ($entries as $DB_NAME) {
                if (self::checkUseTable($DB_NAME, "t_localization")) {
                    if (self::checkUseConst($DB_NAME, $CONST)) {
                        $SQL = "UPDATE $DB_NAME.t_localization SET";
                        $SQL .= " $LANGUAGE='" . addslashes(stripslashes($POST_CONTENT)) . "'";
                        $SQL .= " WHERE CONST='" . $CONST . "'";
                        self::dbAccess()->query($SQL);
                    } else {
                        $SQL = "INSERT INTO $DB_NAME.t_localization SET";
                        $SQL .= " ENGLISH='" . addslashes(stripslashes($POST_CONTENT)) . "'";
                        $SQL .= " ,CONST='" . $CONST . "'";
                        self::dbAccess()->query($SQL);
                    }
                }
            }
        }
        return array("success" => true);
    }

    public function jsonAllLocalizations($params) {

        $start = $params["start"] ? $params["start"] : "0";
        $limit = $params["limit"] ? $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $language = isset($params["language"]) ? $params["language"] : "ENGLISH";
        $not_translated = isset($params["not_translated"]) ? $params["not_translated"] : false;

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_localization";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            /*
              if ($language != "ENGLISH") {
              $SQL .= " AND ((" . $language . " like '%" . $globalSearch . "%') OR (CONST like '%" . $globalSearch . "%') ";
              $SQL .= " ) ";
              }
             */
            $SQL .= " AND ((" . $language . " like '%" . $globalSearch . "%') OR (CONST like '%" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        if ($not_translated) {
            $SQL .= " AND " . $language . "='' OR " . $language . " IS NULL";
        } else {
            $SQL .= " AND ENGLISH !=''";
        }

        $SQL .= " ORDER BY CONST";
        //echo $SQL;
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CONST"] = $value->CONST;
                $data[$i]["ENGLISH"] = $value->ENGLISH;

                if ($value->$language) {
                    $data[$i]["CHOOSE_LANGUAGE"] = $value->$language;
                } else {
                    $data[$i]["CHOOSE_LANGUAGE"] = "?";
                }

                $i++;
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );

        return $dataforjson;
    }

    public static function updateExternalLanguage($language) {

        $SQL = "SELECT DISTINCT * FROM camemis_localization.t_localization";
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $adminSQL = "UPDATE t_localization SET";
                $adminSQL .= " " . $language . "='" . addslashes(stripslashes($value->$language)) . "'";
                $adminSQL .= " WHERE CONST='" . $value->CONST . "';";

                self::dbAccess()->query($adminSQL);
            }
        }
    }

    public static function jsonActionSQLStatements($params) {


        $SQL_STATEMENTS = isset($params["SQL_STATEMENTS"]) ? trim($params["SQL_STATEMENTS"]) : null;

        $entries = self::getCAMEMISDatabases();

        $CHECK_DATA = array(
            'camemis_admin' => 'camemis_admin'
            , 'cam_admin' => 'cam_admin'
            , 'camemis_efront1' => 'camemis_efront1'
            , 'camemis_moodle' => 'camemis_moodle'
        );

        if ($entries) {

            foreach ($entries as $DB_NAME) {
                //error_log($DB_NAME);
                if (!in_array($DB_NAME, $CHECK_DATA)) {

                    $DB_PARAMS = array(
                        'host' => 'localhost',
                        'username' => Zend_Registry::get('CHOOSE_DB_USER'),
                        'password' => Zend_Registry::get('CHOOSE_DB_PWD'),
                        'dbname' => $DB_NAME
                    );
                    $DB_ACCESS = Zend_Db::factory('PDO_MYSQL', $DB_PARAMS);
                    $DB_ACCESS->setFetchMode(Zend_Db::FETCH_OBJ);
                    $DB_ACCESS->query($SQL_STATEMENTS);
                }
            }
        }
        return array("success" => true);
    }

}