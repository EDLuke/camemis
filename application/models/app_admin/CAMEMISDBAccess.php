<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/app_admin/AdminSessionAccess.php';

class CAMEMISDBAccess {

    public $GuId = null;
    public $DB_DATABASE;
    public $schoolName;

    function __construct($GuId=false) {

        $this->GuId = $GuId;

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
    }
    
    public function checkTableLocalization($database) {
        $SQL = "SHOW TABLES FROM cam_db1 LIKE '%" . $database . "%'";
        $result = $this->DB_ACCESS->fetchRow($SQL);
        return $result ? true : false;
    }

    public function getAllDatabases() {

        $SQL = "SHOW DATABASES";
        $result = $this->DB_ACCESS->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                $data[$value->Database] = $value->Database;
            }
        }

        return $data;
    }

    public static function getListLocalization() {

        $SQL = "SELECT * FROM t_localization";
        $result = Zend_Registry::get('DB_ACCESS')->fetchAll($SQL);

        return $result;
    }
}

?>