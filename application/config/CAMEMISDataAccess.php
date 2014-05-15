<?php

/**
 * @Kaom Vibolrith
 * 23.12.2008
 *
 */
require_once 'config/CAMEMISCustomerConfig.php';

class CAMEMISDataAccess {

    var $connection = null;
    var $dbName;
    var $dbUser;
    var $dbPassword;
    var $log = null;

    function CAMEMISDataAccess() {

        $this->dbName = CAMEMISCustomerConfig::getDBNameByDomain();
        $this->dbUser = CAMEMISCustomerConfig::getDBUserByDomain();
        $this->dbPassword = CAMEMISCustomerConfig::getDBPasswordByDomain();

        $this->log = new CAMEMISLog("CAMEMISDataAccess (" . $this->dbName . ")");
        $this->log->debug("trying to connect...");
        $this->connection = $this->getConnection();
    }

    /**
     * returns a database connection
     */
    function getConnection() {
        
        $con = mysql_connect("localhost", $this->dbUser, $this->dbPassword);
        
        if ($con){
            $ret = mysql_select_db($this->dbName, $con);
            return $con;
        }else{
            $con = null;
        }
        
        return $con;
    }

    function getConnectionClose() {
        $link = $this->getConnection();
        mysql_close($link);
    }

    /**
     * insert values into table
     * @param string $table tablename
     * @param array $values associative array with attr/value pairs
     */
    function insert($table, $values) {
        $stmt = "INSERT INTO $table ";

        $cols = "";
        $vals = "";

        $first = true;
        foreach ($values as $value) {
            if (!$first) {
                $cols .= ", ";
                $vals .= ", ";
            }
            $first = false;
            $cols .= key($values);
            $value = trim($value);

            if ($value === "NOW()") {
                $vals .= "NOW()";
            }
            else if ($value === "NULL") {
                $vals .= "NULL";
            }
            else {
                $vals .= "'$value'";
            }

            next($values);
        }

        $stmt .= "(" . $cols . ") VALUES (" . $vals . ")";

        #print $stmt;

        return $this->submit($stmt);
    }

    /**
     * Update a table row
     * @param string $table tablename
     * @param int $id id to update
     * @param array $values associative array with attr/value pairs
     * @param string $whereCol ="id" column name for $id
     */
    function update($table, $id, $values, $whereCol = "id") {
        $stmt = "UPDATE $table SET ";

        $first = true;

        foreach ($values as $value) {
            if (!$first) {
                $stmt .= ", ";
            }
            $first = false;

            if ($value === "NOW()") {
                $stmt .= key($values) . "=NOW()";
            }
            else if ($value === "NULL") {
                $stmt .= key($values) . "=NULL";
            }
            else {
                $stmt .= key($values) . "='" . $value . "'";
            }
            next($values);
        }

        $stmt .= " WHERE $whereCol = '" . $id . "'";
        #print $stmt;

        return $this->submit($stmt);
    }

    /**
     * Update a table row
     * @param string $table tablename
     * @param array $params WHERE to update
     * @param array $values associative array with attr/value pairs
     */
    function updateByParams($table, $params, $values) {
        $stmt = "UPDATE $table SET ";

        $first = true;

        foreach ($values as $value) {
            if (!$first) {
                $stmt .= ", ";
            }
            $first = false;

            if ($value === "NOW()") {
                $stmt .= key($values) . "=NOW()";
            }
            else if ($value === "NULL") {
                $stmt .= key($values) . "=NULL";
            }
            else {
                $stmt .= key($values) . "='" . $value . "'";
            }
            next($values);
        }

        $stmt .= " WHERE ";
        $bFirst = true;
        while (list($name, $value) = each($params)) {
            if ($bFirst)
                $bFirst = false;
            else
                $stmt .= "AND ";

            $stmt .= "$name = '$value' ";
        }

        //error_log($stmt."\n");
        return $this->submit($stmt);
    }

    /**
     * delete a table entry
     * @param string $table tablename
     * @param int $id primary key id
     */
    function delete($table, $id, $column = "id") {
        $stmt = "DELETE FROM $table WHERE 1=1 ";
        $stmt .= "AND $column = '" . $id . "'";
        return $this->submit($stmt);
    }

    function deleteByValues($table, $values) {
        $stmt = "DELETE FROM $table WHERE 1=1 ";
        while (list($name, $value) = each($values)) {
            $stmt .= "AND $name = '$value' ";
        }
        return $this->submit($stmt);
    }

    function deleteAll($table) {
        $stmt = "DELETE FROM $table WHERE 1=1'";
        return $this->submit($stmt);
    }

    function exists($table, $field, $value) {
        $stmt = "SELECT count(*) as C FROM $table WHERE 1=1 ";
        $stmt .= "AND $field='$value'";
        $result = $this->query($stmt);
        return $this->fetchObject($result);
    }

    function countByValues($table, $values) {
        $stmt = "SELECT count(*) as C FROM $table WHERE 1=1 ";
        while (list($name, $value) = each($values)) {
            $stmt .= "AND $name = '$value' ";
        }
        //error_log($stmt);
        $result = $this->query($stmt);
        return $this->fetchObject($result);
    }

    function getAll($table, $order = false) {
        $stmt = "SELECT DISTINCT * FROM $table WHERE 1=1 ";
        if ($order) {
            $stmt .= " ORDER BY $order";
        }
        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            $allRows = array();
            while ($row = $this->fetchObject($result)) {
                $allRows[] = $row;
            }
            return $allRows;
        }
    }

    function getAllWhereByValues($table, $values) {
        $stmt = "SELECT DISTINCT * FROM $table WHERE 1=1 ";
        
        $i = 0;
        while (list($name, $value) = each($values)) {
            $stmt .= "AND $name = '$value' ";
            $i++;
        }
        //error_log($stmt."\n");
        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            $allRows = array();
            while ($row = $this->fetchObject($result)) {
                $allRows[] = $row;
            }
            return $allRows;
        }
    }

    function getAllWhereByValuesArray($table, $values, &$columns = '') {
        $stmt = "SELECT DISTINCT * FROM $table WHERE 1=1 ";           

        $i = 0;
        while (list($name, $value) = each($values)) {
            $stmt .= "AND $name = '$value' ";
            $i++;
        }

        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            if ($columns <> '') {
                for ($i = 0; $i < mysql_num_fields($result); $i++) {
                    $columns[] = mysql_field_name($result, $i);
                }
            }
            $allRows = array();
            while ($row = mysql_fetch_array($result)) {
                $allRows[] = $row;
            }
            return $allRows;
        }
    }

    function getOneWhere($table, $values) {
        $stmt = "SELECT DISTINCT * FROM $table WHERE 1=1 "; 

        $i = 0;
        while (list($name, $value) = each($values)) {
            $stmt .= "AND $name = '$value' ";
            $i++;
        }
        
        //error_log($stmt."\n");
        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            return $this->fetchObject($result);
        }
    }

    function getScalarWhere($table, $field, $fieldwhere, $where) {
        $stmt = "SELECT $field AS RESULT_FIELD FROM $table WHERE 1=1 ";
        $stmt .= "AND $fieldwhere = '$where'";

        $result = $this->getBy($stmt);
        if (!$result) {
            return null;
        }
        else {
            return $result->RESULT_FIELD;
        }
    }

    function getAllWhere($table, $column, $value, $order = false) {
        $stmt = "SELECT DISTINCT * FROM $table WHERE 1=1 ";
        $stmt .= "WHERE $column = '$value' ";

        if ($order) {
            $stmt .= " ORDER BY $order";
        }
        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            $allRows = array();
            while ($row = $this->fetchObject($result)) {
                $allRows[] = $row;
            }
            return $allRows;
        }
    }

    /**
     * completly "self defined" (SDef) statement
     *
     * @param  str $stmt
     * @return arr
     */
    function getSDef($stmt) {
        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            $allRows = array();
            while ($row = $this->fetchObject($result)) {
                $allRows[] = $row;
            }
            return $allRows;
        }
    }

    function getBy($stmt) {
        $result = $this->query($stmt);
        if (!$result) {
            return null;
        }
        else {
            return $this->fetchObject($result);
        }
    }

    function query($stmt) {
        if (CAMEMISConfigBasic::LOGLEVEL_DATABASE) 
        {
            $this->log->database("query: " . $stmt);
        }
        $result = mysql_query($stmt, $this->connection);

        if (!isset($result) || !$result) {

        }

        if (CAMEMISConfigBasic::LOGLEVEL_DATABASE) {
            if ($result) $this->log->database("num_rows: " . mysql_num_rows($result));
        }
        if ($result) {
            if (mysql_num_rows($result) == 0) {
                return null;
            }
            else {
                return $result;
            }
        }
    }

    function submit($stmt) {
        if (CAMEMISConfigBasic::LOGLEVEL_DATABASE) {
            $this->log->database("update: " . $stmt);
        }

        if (!mysql_query($stmt, $this->connection)) {

        }

        if (CAMEMISConfigBasic::LOGLEVEL_DATABASE) {
            $this->log->database("affected_rows: " . mysql_affected_rows());
        }

        return mysql_insert_id($this->connection);
    }

    function fetchObject($result) {
        if ($result) {
            return mysql_fetch_object($result);
        }
        else {
            return null;
        }
    }

    function fetchUniqueResult($result) {
        if ($result) {
            return mysql_result($result, 0);
        }
        else {
            return null;
        }
    }

    function escapeString($string) {
        return mysql_real_escape_string($string);
    }
    
    function setUserActivity($values) {
        
        $stmt = "INSERT INTO ".Zend_Registry::get('T_USER_ACTIVITY')."";
        $stmt .= " SET SCHOOL_ID = '".Zend_Registry::get('SCHOOL_ID')."'";
        $stmt .= " ,USER ='".Zend_Registry::get('USER')->ID."'";
        $stmt .= " ,USER_CODE ='".Zend_Registry::get('USER')->CODE."'";
        $stmt .= " ,ACTIVITY_DATE ='".getCurrentDBDateTime()."'";
        
        while (list($name, $value) = each($values)) {
            $stmt .= ", $name = '$value' ";
        }
        
        return $this->submit($stmt);
    }
    
    function getStudentClassInfo($studentId, $classId){
        
        $sqlStudent = "SELECT CONCAT(LASTNAME,' ',FIRSTNAME) as NAME FROM  ".Zend_Registry::get('T_STUDENT')."";
        $sqlStudent .= " WHERE SCHOOL_ID = '".Zend_Registry::get('SCHOOL_ID')."' ";
        $sqlStudent .= " AND ID = '".$studentId."' ";
        $resultStudent = $this->query($sqlStudent);
        $student = $this->fetchObject($resultStudent);
        
        $sqlClass = "SELECT NAME FROM  ".Zend_Registry::get('T_GRADE')."";
        $sqlClass .= " WHERE 1=1' ";
        $sqlClass .= " AND ID = '".$classId."' ";
        $resultClass = $this->query($sqlClass);
        $class = $this->fetchObject($resultClass);
        
        $stmp = "";
        if ($student){
            $stmp = STUDENT.": ".isset($student->NAME)?$student->NAME:"---";
        }
        if ($class){
            $stmp .= ", ".GROUP.": ".$class->NAME;
        }
        
        return $stmp;
    }
}

?>