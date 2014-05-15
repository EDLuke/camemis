<?

require_once 'config/CAMEMISCustomerConfig.php';

class ZendDBConfig {

    static function DBConfig() {
        // Local....
        $params = array('host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'dbname' => 'camemis_admin');
        return $params;
    }

}

?>