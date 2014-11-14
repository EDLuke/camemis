<?php

////////////////////////////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");

class SQLScheduleDaySetting {

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getScheduleTrainingGroupDay($trainingId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule');
        $SQL->where('TRAINING_ID = ?', $trainingId);
        $SQL->group("START_DATE");
        $SQL->group("END_DATE");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

}

?>