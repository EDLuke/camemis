<?php

require_once 'config/CAMEMISCustomerConfig.php';

class ZendDBConfig {

    static function DBConfig() {
        // Local....
        $params = array('host' => CAMEMISConfig::DB_HOST,
            'username' => CAMEMISCustomerConfig::getDBUserByDomain(),
            'password' => CAMEMISCustomerConfig::getDBPasswordByDomain(),
            'dbname' => CAMEMISCustomerConfig::getDBNameByDomain());
        return $params;
    }

}

?>