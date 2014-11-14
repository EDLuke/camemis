<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 25.10.2014
//
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/BuildData.php";
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentHealthStatisticsDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function getSqlStudentBMIByMonthType($month, $year, $Type) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_student_medical";
        $SQL .= " WHERE 1 = 1";

        if ($Type) {
            switch ($Type) {
                case 'UNDERWEIGHT':
                    if ($month)
                        $SQL .= " AND MONTH(MEDICAL_DATE) IN (" . $month . ")";
                    if ($year)
                        $SQL .= " AND YEAR(MEDICAL_DATE)='" . $year . "'";
                    $SQL .= " AND STATUS=1";
                    break;
                case 'NORMAL_WEIGHT':
                    if ($month)
                        $SQL .= " AND MONTH(MEDICAL_DATE) IN (" . $month . ")";
                    if ($year)
                        $SQL .= " AND YEAR(MEDICAL_DATE)='" . $year . "'";
                    $SQL .= " AND STATUS=2";
                    break;
                case 'OVER_WEIGHT':
                    if ($month)
                        $SQL .= " AND MONTH(MEDICAL_DATE) IN (" . $month . ")";
                    if ($year)
                        $SQL .= " AND YEAR(MEDICAL_DATE)='" . $year . "'";
                    $SQL .= " AND STATUS=3";
                    break;
                case 'OBESITY':
                    if ($month)
                        $SQL .= " AND MONTH(MEDICAL_DATE) IN (" . $month . ")";
                    if ($year)
                        $SQL .= " AND YEAR(MEDICAL_DATE)='" . $year . "'";
                    $SQL .= " AND STATUS=4";
                    break;
            }
        }
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentHealthBMIByMonthType($Type) {
        $MONTHS = array(
            '01' => JANUARY
            , '02' => FEBRUARY
            , '03' => MARCH
            , '04' => APRIL
            , '05' => MAY
            , '06' => JUNE
            , '07' => JULY
            , '08' => AUGUST
            , '09' => SEPTEMBER
            , '10' => OCTOBER
            , '11' => NOVEMBER
            , '12' => DECEMBER
        );

        $QUARTER = array(
            FIRST_QUARTER => '01,02,03'
            , SECOND_QUARTER => '04,05,06'
            , THIRD_QUARTER => '07,08,09'
            , FOUR_QUARTERS => '10,11,12'
        );

        $RESULT = "[";
        if ($QUARTER) {
            $i = 0;
            foreach ($QUARTER as $key => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $key . "'";
                $RESULT .= ",'y':" . self::getSqlStudentBMIByMonthType($value, date("Y"), $Type) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getCountStudentHealthType($Type, $date) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_student_medical_item AS A LEFT JOIN t_student_medical AS B ON A.MEDICAL_ID=B.ID ";
        $SQL .= " WHERE 1 = 1";

        if ($Type)
            $SQL .= " AND A.ITEM='" . $Type . "'";
        if ($date)
            $SQL .= " AND DATE(B.MEDICAL_DATE) = '" . $date . "'";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentHealthByType($Type) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_student_medical"), array("DATE(MEDICAL_DATE) AS SECTION_DATE"));
        $SQL->group("DATE(A.MEDICAL_DATE)");
        $SQL->order("DATE(A.MEDICAL_DATE) ASC");
        //error_log($SQL->__toString());
        $entries = self::dbAccess()->fetchAll($SQL);
        $RESULT = "[";
        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "[" . strtotime($value->SECTION_DATE) * 1000 . "";
                $RESULT .= "," . self::getCountStudentHealthType($Type, $value->SECTION_DATE) . "]";
                $i++;
            }
        }
        $RESULT .= "]";
        return $RESULT;
    }

    public static function getCountStudentHealthTypeByGender($quarter, $year, $gender, $type) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_student_medical AS A LEFT JOIN t_student AS B ON A.STUDENT_ID=B.ID ";
        $SQL .= " WHERE 1 = 1";

        if ($type)
            $SQL .= " AND A.OBJECT_TYPE='" . $type . "'";
        if ($quarter)
            $SQL .= " AND MONTH(A.MEDICAL_DATE) IN (" . $quarter . ")";
        if ($year)
            $SQL .= " AND YEAR(A.MEDICAL_DATE)='" . $year . "'";
        if ($gender) {
            switch ($gender) {
                case 'MALE':
                    $SQL .= " AND B.GENDER = '1'";
                    break;
                case 'FEMALE':
                    $SQL .= " AND B.GENDER = '2'";
                    break;
            }
        }
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentHealthByGender($gender, $type) {

        $QUARTER = array(
            FIRST_QUARTER => '01,02,03'
            , SECOND_QUARTER => '04,05,06'
            , THIRD_QUARTER => '07,08,09'
            , FOUR_QUARTERS => '10,11,12'
        );
        $RESULT = "[";
        if ($QUARTER) {
            $i = 0;
            foreach ($QUARTER as $key => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $key . "'";
                $RESULT .= ",'y':" . self::getCountStudentHealthTypeByGender($value, date("Y"), $gender, $type) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

}

?>
