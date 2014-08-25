<?php
///////////////////////////////////////////////////////////
// @Chuy Thong Senior Software Developer
// Date: 08.08.2014
// Phnom Penh, Cambodia
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffDBAccess.php";

class LogDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new LogDBAccess();
        }
        return $me;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function getLogs($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $endDate   = isset($params["END_DATE"])   ? setDate2DB($params["END_DATE"])   : "";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " ID, ACCESSED_BY, ACCESSED_DATE, TABLE_NAME, ACCESS_TYPE, OBJECT_ID, FIELD_NOTE";
        $SQL .= " FROM t_log";
        $SQL .= " WHERE 1=1";

        if ($startDate && $endDate)
        {
            $SQL .= " AND (DATE(ACCESSED_DATE) >= '" . $startDate . "' AND DATE(ACCESSED_DATE) <= '" . $endDate . "')";
        }

        if ($globalSearch) {
            // $SQL .= " AND ((ENGLISH LIKE '%" . $globalSearch . "%')";
            // $SQL .= " ) ";
        }

        $SQL .= " ORDER BY ACCESSED_DATE DESC";

        $resultCount = self::dbAccess()->fetchAll($SQL);
        $SQL .= " LIMIT " . $start . " , " . $limit . "";
        $resultEntries = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($resultEntries)
            while (list($key, $row) = each($resultEntries)) {
                $user  = StaffDBAccess::findStaffFromId($row->ACCESSED_BY);
                $note  = "";
                $dummy = unserialize($row->FIELD_NOTE);
                if ("UPDATE" == $row->ACCESS_TYPE) {
                    foreach ($dummy as $value) {
                        foreach ($value as $key => $val) {
                            $note .= constant($key) ."= {". $val["OLD"] ." => ". $val["NEW"] ."} ";
                        }
                    }
                } else {
                    foreach ($dummy as $value) {
                        foreach ($value as $key => $val) {
                            $note .= constant($key) ."= {". $val ."} ";
                        }
                    }                    
                }
                $data[$i]["ID"]            = $row->ID;
                $data[$i]["ACCESSED_BY"]   = $user->NAME;
                $data[$i]["ACCESSED_DATE"] = $row->ACCESSED_DATE;
                $data[$i]["TABLE_NAME"]    = constant($this->getTableConstant( $row->TABLE_NAME ));
                $data[$i]["ACCESS_TYPE"]   = $row->ACCESS_TYPE;
                $data[$i]["OBJECT_ID"]     = $row->OBJECT_ID;
                $data[$i]["FIELD_NOTE"]    = $note;

                $i++;
            }
        // error_log(print_r($data, true));
        return array(
            "success" => true
            , "totalCount" => sizeof($resultCount)
            , "rows" => $data
        );
    }

    private function getFieldConstant($field_in_table) {   // table_name.field_name
        $SQL = self::dbAccess()->select();
        $SQL->from("t_log_field_name", array('DESC_CONST'));
        $SQL->where("TABLE_FIELD_NAME='" . $field_in_table . "'");
        $data = self::dbAccess()->fetchRow($SQL);
        if ($data)
            return $data->DESC_CONST;
        return 'N/A';        
    }

    private function getTableConstant($tbl) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_log_table", array('DESC_CONST'));
        $SQL->where("TABLE_NAME='" . $tbl . "'");
        $data = self::dbAccess()->fetchRow($SQL);
        if ($data)
            return $data->DESC_CONST;
        return 'N/A';
    }

}

?>