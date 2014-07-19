<?php
// drop this file in library/Zend/Db/Adapter
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

class Zend_Db_Adapter_CamemisPdoMysql extends Zend_Db_Adapter_Pdo_Mysql
{
    private function is_track_table($table) {
        return in_array($table, array("t_room"));
    }

    public function update($table, array $bind, $where = '')
    {
    	error_log("update $table");
        if ( $this->is_track_table($table) ) {
            $this->writeLogRecord($table, "UPDATE", $where, $bind);
        } 
		parent::update($table, $bind, $where);
    }

    public function delete($table, $where = '')
    {
    	error_log("delete $table");
        if ( $this->is_track_table($table) ) {
            $this->writeLogRecord($table, "DELETE", $where, null);
        }
    	parent::delete($table, $where);
    }
    // ==== helper ====
    function writeLogRecord($table, $operation, $where = '', $fields) {
        $values["ACCESSED_BY"] = UserAuth::userId();
        $values["TABLE_NAME" ] = $table;
        $values["OBJECT_ID"  ] = '';  // $objId;    // foreign key of table
        $values["ACCESS_TYPE"] = $operation;
        $data = "";
        if ("UPDATE" === $operation) {
            $data = $this->getLogUpdate($table, $where, $fields);
        } else if ("DELETE" === $operation) {
            $data = $this->getLogDelete($table, $where);
        }
        $values["FIELD_NOTE" ] = $data;
        error_log("By USER: ". $values["ACCESSED_BY"]);
        error_log("$operation: " . $data);
        // insert("t_log", $values);
    }

    function getLogUpdate($table, $where, $fields) {
        $field_str = implode(",", array_keys($fields));
        $stmt = "SELECT $field_str FROM $table WHERE TRUE AND $where";
        $data = array();
        foreach ($this->query($stmt) as $row) {
            foreach ($row as $key => $value) {
                if ($value != $fields[$key]) {  // record only field change
                    $data[] = "`$key: $value, $fields[$key]`";
                }
            }
        }
        return implode(",", $data);        
    }

    function getLogDelete($table, $where = '') {
        $stmt = "SELECT * FROM $table WHERE TRUE AND $where";
        $data = array();
        foreach ($this->query($stmt) as $row) {
            foreach ($row as $key => $value) {
                $data[] = "`$key: $value`";
            }
        }
        return implode(",", $data);        
    }
	
}