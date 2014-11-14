<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 25.10.2013
//
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class AssessmentStatisticsDBAccess {

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

    public static function getFacilityItemByDayType($Type) {
        $DAYS = array(
            '00' => MONDAY
            , '01' => TUESDAY
            , '02' => WEDNESDAY
            , '03' => THURSDAY
            , '04' => FRIDAY
            , '05' => SATURDAY
            , '06' => SUNDAY
        );
        $RESULT = "[";
        if ($DAYS) {
            $i = 0;
            foreach ($DAYS as $day => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlFacilityItemByDayType($day, $Type) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlFacilityItemByDayType($day, $Type) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_facility_user_item";
        $SQL .= " WHERE 1 = 1";

        if ($Type) {

            if ($Type == 'CHECK-OUT') {
                $SQL .= " AND ACTION_TYPE='1' ";
                if ($day)
                    $SQL .= " AND WEEKDAY(ISSUED_DATE)='" . $day . "'";
            }else if ($Type == 'CHECK-IN') {
                $SQL .= " AND ACTION_TYPE='2' ";
                if ($day)
                    $SQL .= " AND WEEKDAY(RECEIVED_DATE)='" . $day . "'";
            }
        }

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getFacilityItemByWeekType($Type) {
        $DAYS = array(
            '01' => "01"
            , '02' => "02"
            , '03' => "03"
            , '04' => "04"
            , '05' => "05"
            , '06' => "06"
            , '07' => "07"
            , '08' => "08"
            , '09' => "09"
            , '10' => "10"
            , '11' => "11"
            , '12' => "12"
            , '13' => "13"
            , '14' => "14"
            , '15' => "15"
            , '16' => "16"
            , '17' => "17"
            , '18' => "18"
            , '19' => "19"
            , '20' => "20"
            , '21' => "21"
            , '22' => "22"
            , '23' => "23"
            , '24' => "24"
            , '25' => "25"
            , '26' => "26"
            , '27' => "27"
            , '28' => "28"
            , '29' => "29"
            , '30' => "30"
            , '31' => "31"
        );
        $RESULT = "[";
        if ($DAYS) {
            $i = 0;
            foreach ($DAYS as $day => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlFacilityItemByWeekType($day, $Type) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlFacilityItemByWeekType($day, $Type) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_facility_user_item";
        $SQL .= " WHERE 1 = 1";

        if ($Type) {

            if ($Type == 'CHECK-OUT') {
                $SQL .= " AND ACTION_TYPE='1' ";
                if ($day)
                    $SQL .= " AND DAYOFMONTH(ISSUED_DATE)='" . $day . "'";
            }else if ($Type == 'CHECK-IN') {
                $SQL .= " AND ACTION_TYPE='2' ";
                if ($day)
                    $SQL .= " AND DAYOFMONTH(RECEIVED_DATE)='" . $day . "'";
            }
        }

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getFacilityItemByMonthType($Type) {
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
        $RESULT = "[";
        if ($MONTHS) {
            $i = 0;
            foreach ($MONTHS as $key => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlFacilityItemByMonthType($key, '2013', $Type) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlFacilityItemByMonthType($month, $year, $Type) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_facility_user_item";
        $SQL .= " WHERE 1 = 1";

        if ($Type) {

            if ($Type == 'CHECK-OUT') {
                $SQL .= " AND ACTION_TYPE='1' ";
                if ($month)
                    $SQL .= " AND MONTH(ISSUED_DATE)='" . $month . "'";

                if ($year)
                    $SQL .= " AND YEAR(ISSUED_DATE)='" . $year . "'";
            }elseif ($Type == 'CHECK-IN') {

                $SQL .= " AND ACTION_TYPE='2' ";

                if ($month)
                    $SQL .= " AND MONTH(ISSUED_DATE)='" . $month . "'";

                if ($year)
                    $SQL .= " AND YEAR(ISSUED_DATE)='" . $year . "'";
            }
        }

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>
