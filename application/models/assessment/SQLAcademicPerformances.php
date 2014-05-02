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

    public static function getAverageMonthStudentClassPerformance($object)
    {
        
    }

    public static function getSQLAverageStudentClassPerformance($object)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_subject_assessment'), array("SUM(A.SUBJECT_VALUE*B.COEFF_VALUE) AS SUM_VALUE"));
        $SQL->joinLeft(array('B' => "t_grade_subject"), 'A.SUBJECT_ID=B.SUBJECT', array("SUM(B.COEFF_VALUE) AS SUM_COEFF"));
        $SQL->where('A.CLASS_ID = ?', $object->academicId);
        $SQL->where('A.STUDENT_ID = ?', $object->studentId);
        $SQL->where('B.SCORE_TYPE = ?', 1);

        switch ($object->section)
        {
            case "MONTH":
                if ($object->month)
                    $SQL->where("A.MONTH = '" . $object->month . "'");

                if ($object->year)
                    $SQL->where("A.YEAR = '" . $object->year . "'");
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                if ($object->term)
                    $SQL->where("A.TERM = '" . $object->term . "'");
                break;
            case "YEAR":
                if ($object->year)
                    $SQL->where("A.YEAR = '" . $object->year . "'");
                break;
        }

        $SQL->group("A.SUBJECT_ID");
        $result = self::dbAccess()->fetchRow($SQL);
    }

}

?>