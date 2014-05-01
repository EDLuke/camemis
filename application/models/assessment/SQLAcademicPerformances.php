<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/SQLAcademicPerformances.php';

class SQLAcademicPerformances {

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return false::dbAccess()->select();
    }

    public static function getSQLListStudentsMonthClassPerformance($object)
    {
        
    }

    public static function getSQLListStudentsTermClassPerformance($object)
    {
        
    }

    public static function getSQLListStudentsYearClassPerformance($object)
    {
        
    }

}

?>