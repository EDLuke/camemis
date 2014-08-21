<?php

// drop this file in library/Zend/Db/Adapter
require_once("Zend/Loader.php");
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';

class Zend_Db_Adapter_CamemisPdoMysql extends Zend_Db_Adapter_Pdo_Mysql {

    public function update($table, array $bind, $where = '') {
        // error_log("update $table");
        if ($this->is_log_table($table)) {
            $this->writeLogRecord($table, "UPDATE", $where, $bind);
        }
        parent::update($table, $bind, $where);
    }

    public function delete($table, $where = '') {
        error_log("delete $table");
        if ($this->is_log_table($table)) {
            $this->writeLogRecord($table, "DELETE", $where, null);
        }
        parent::delete($table, $where);
    }

    // ==== helper ====
    private function is_log_table($table) {
        $all_tables = array();
        $stmt = "SELECT TABLE_NAME FROM t_log_table";
        foreach ($this->query($stmt) as $row) {
            foreach ($row as $key => $value) {
                $all_tables[] = $value;
            }
        }
        return in_array($table, $all_tables);
    }

    private function get_primary_field($table) {
        $stmt = "SELECT FIELD_PRIMARY FROM t_log_table WHERE TABLE_NAME='" . $table . "'";
        foreach ($this->query($stmt) as $row) {
            foreach ($row as $key => $value) {
                return $value;
            }
        }
    }

    private function writeLogRecord($table, $operation, $where = '', $fields) {
        $values = array();
        $values['ACCESSED_BY'] = UserAuth::userId();
        $values['TABLE_NAME'] = $table;

        $values['ACCESS_TYPE'] = $operation;

        $values['ACCESSED_DATE'] = (new Zend_Date())->toString('YYYY-MM-dd HH:mm:ss');
        $data = "";
        $whereSQL = parent::_whereExpr($where);
        if ("UPDATE" === $operation) {
            $data_update = $this->getLogUpdate($table, $whereSQL, $fields);
            $data = $data_update['DATA'];
            $values['OBJECT_ID'] = $data_update['KEY'];  // $objId;    // foreign key of table
        } else if ("DELETE" === $operation) {
            $data_delete = $this->getLogDelete($table, $whereSQL);
            $data = $data_delete['DATA'];
            $values['OBJECT_ID'] = $data_delete['KEY'];
        }
        $values["FIELD_NOTE"] = $data;
        // error_log(print_r($values, true));
        parent::insert("t_log", $values);
    }

    private function getLogUpdate($table, $where, $fields) {
        $primary_field = $this->get_primary_field($table);
        $field_str = $primary_field . ", " . implode(",", array_keys($fields));
        $stmt = "SELECT $field_str FROM $table WHERE TRUE AND $where";
        $data = array();
        $primary_data = "";
        foreach ($this->query($stmt) as $row) {
            foreach ($row as $key => $value) {
                if ($key == $primary_field) {
                    $primary_data = $value;
                }
                if ($key != $primary_field && $value != $fields[$key]) {  // record only field change
                    $data[] = "\"$key\": { \"old\": \"$value\", \"new\": \"{$fields[$key]}\" }";
                }
            }
        }
        return array("KEY" => $primary_data, "DATA" => "{ " . implode(",", $data) . "}");
    }

    private function getLogDelete($table, $where = '') {
        $primary_field = $this->get_primary_field($table);
        $stmt = "SELECT * FROM $table WHERE TRUE AND $where";
        $data = array();
        $primary_data = "";
        foreach ($this->query($stmt) as $row) {
            foreach ($row as $key => $value) {
                if ($key == $primary_field) {
                    $primary_data = $value;
                }
                $data[] = "\"$key\": \"$value\"";
            }
        }
        return array("KEY" => $primary_data, "DATA" => "{ " . implode(", ", $data) . "}");
    }

}
