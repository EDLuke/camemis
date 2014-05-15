<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 07.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class CAMEMISConfig {

    static function setRegistryUserDB() {
        $params = array(
            'host' => "localhost",
            'username' => 'root',
            'password' => '',
            'dbname' => 'camemis_admin');

        $DB = Zend_Db::factory('PDO_MYSQL', $params);
        $DB->setFetchMode(Zend_Db::FETCH_OBJ);


        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_customer WHERE URL='" . $_SERVER['SERVER_NAME'] . "'";
	
        $result = $DB->fetchRow($SQL);

        if ($result) {

            Zend_Registry::set('CHOOSE_DB_NAME', $result->DB_NAME);
            Zend_Registry::set('CHOOSE_DB_HOST', 'localhost');
            Zend_Registry::set('CHOOSE_DB_USER', 'root');
            Zend_Registry::set('CHOOSE_DB_PWD', '');
            Zend_Registry::set('ADMIN_LANGUAGE', $result->SYSTEM_LANGUAGE);
            Zend_Registry::set('SYSTEM_LANGUAGE', $result->SYSTEM_LANGUAGE);
            Zend_Registry::set('SYSTEM_COUNTRY', $result->SYSTEM_COUNTRY);
            
            Zend_Registry::set('MODUL_API', $result->MODUL_API);

            $USER_DB_PARAMS = array(
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'dbname' => $result->DB_NAME
            );

            $ADMIN_DB_PARAMS = array(
                'host' => 'localhost',
                'username' => 'root',
                'password' => '',
                'dbname' => "camemis_admin"
            );

            $USER_DB_ZEND_CONFIG = Zend_Db::factory('PDO_MYSQL', $USER_DB_PARAMS);
            $USER_DB_ZEND_CONFIG->setFetchMode(Zend_Db::FETCH_OBJ);

            $ADMIN_DB_ZEND_CONFIG = Zend_Db::factory('PDO_MYSQL', $ADMIN_DB_PARAMS);
            $ADMIN_DB_ZEND_CONFIG->setFetchMode(Zend_Db::FETCH_OBJ);

            Zend_Registry::set('DB_ACCESS', $USER_DB_ZEND_CONFIG);
            Zend_Registry::set('ADMIN_DB_ACCESS', $ADMIN_DB_ZEND_CONFIG);

            return true;
        }
    }

    static function chooseDBUser() {
        return Zend_Registry::get('6E6B3F2E-NOK84-4CF8-922B-73D46B496E6B');
    }

    static function chooseDBPassword() {
        return Zend_Registry::get('6E6B3F2E-SOCHEATA84-4CF8-922B-73D46B496E6B');
    }

}

?>