<?php

    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 29.11.2010
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////
    require_once("Zend/Loader.php");
    require_once 'include/Common.inc.php';
    require_once 'models/app_admin/AdminSessionAccess.php';

    define("T_CUSTOMER", "t_customer");
    define("T_DATABASE", "t_database");

    class AdminDatabaseDBAccess {

        public $GuId;

        function __construct() {

            $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        }

        public function findDatabaseFromCustomer($Id) {

            $sql = "SELECT DISTINCT *";
            $sql .= " FROM " . T_DATABASE . "";
            $sql .= " WHERE";
            $sql .= " CUSTOMER = '" . $Id . "'";
            //echo $sql;
            $result = $this->DB_ACCESS->fetchRow($sql);

            return $result;
        }

        public function allDatabasesQuery($params) {

            $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

            $sql = "";
            $sql .= " SELECT *";
            $sql .= " FROM " . T_DATABASE . " AS A";
            $sql .= " WHERE 1=1";

            if ($globalSearch) {
                $sql .= " AND ((A.DB_NAME like '" . $globalSearch . "%') ";
                $sql .= " ) ";
            }

            $sql .= " ORDER BY A.DB_NAME";
            //echo $sql;
            $result = $this->DB_ACCESS->fetchAll($sql);

            return $result;
        }

        public function jsonAllDatabases($params) {

            $start = $params["start"] ? (int) $params["start"] : "0";
            $limit = $params["limit"] ? (int) $params["limit"] : "50";

            $result = $this->allDatabasesQuery($params);

            $i = 0;
            $data = array();
            if ($result)
                foreach ($result as $value) {

                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["GUID"] = $value->GUID;
                    $data[$i]["DB_NAME"] = $value->DB_NAME;
                    $data[$i]["URL"] = $value->URL;
                    $data[$i]["MODUL_API"] = $value->MODUL_API;

                    if ($this->checkUsedDatabase($value->GUID)){

                        $data[$i]["DATABASE_STATUS"] = iconStatusDatabase(1);
                        $data[$i]["AVAILABLE"] = 1;
                    }else{
                        $data[$i]["DATABASE_STATUS"] = iconStatusDatabase(0);
                        $data[$i]["AVAILABLE"] = 0;
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

        public function checkUsedDatabase($GuId) {

            $sql = "SELECT DISTINCT *";
            $sql .= " FROM " . T_DATABASE . "";
            $sql .= " WHERE GUID='".$GuId."' AND CUSTOMER !=  ''";
            $result = $this->DB_ACCESS->fetchRow($sql);

            return $result;
        }
    }

?>