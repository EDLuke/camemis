<?php
///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 15.07.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class CAMEMISZendAccess {

    private $db_access = null;
    
    function __construct() {

        $this->db_access = Zend_Registry::get('DB_ACCESS');

    }
}

?>