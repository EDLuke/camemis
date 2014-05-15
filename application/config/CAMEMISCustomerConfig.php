<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 26.04.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class CAMEMISCustomerConfig {

    static function getDBNameByDomain() {
        return Zend_Registry::get('CHOOSE_DB_NAME');
    }

    static function getDBPasswordByDomain() {
        return '';
    }

    static function getDBUserByDomain() {
        return 'root';
    }
}

?>