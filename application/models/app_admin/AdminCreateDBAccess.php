<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.08.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/app_admin/AdminSessionAccess.php';

define("T_CUSTOMER", "t_customer");
define("T_DATABASE", "t_database");

class AdminCreateDBAccess {

    function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
    }

    public function creataSchoolyear($new_dbname, $selected_dbname) {
        $SQL = "
            INSERT INTO " . $new_dbname . ".t_academicdate SELECT * FROM " . $selected_dbname . ".t_academicdate;
        ";
        $this->DB_ACCESS->query($SQL);
    }

}

?>