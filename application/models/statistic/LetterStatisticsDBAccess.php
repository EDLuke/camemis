<?php

///////////////////////////////////////////////////////////
// @Math Man Web Application Developer
// Date: 04.03.2014
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once "models/CamemisTypeDBAccess.php";
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class LetterStatisticsDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    /**
     * Letter Dashboard
     * 
     * @param string $objectType
     * @param mixed $objectId
     */
    public static function getDataSetLetterDashboard($objectType, $objectId = false) {

        $letterTypeEntries = CamemisTypeDBAccess::getCamemisType("LETTER_TYPE", false);

        $DATASET = "[";
        switch ($objectType) {
            case "WEEKLY":
                if ($letterTypeEntries) {
                    $i = 0;
                    foreach ($letterTypeEntries as $value) {
                        $VALUES = self::getLetterDashoardByDayType($value->ID, $objectId);
                        $DATASET .= $i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($letterTypeEntries) {
                    $i = 0;
                    foreach ($letterTypeEntries as $value) {
                        $VALUES = self::getLetterDashoardByWeekType($value->ID, $objectId);
                        $DATASET .= $i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($letterTypeEntries) {
                    $i = 0;
                    foreach ($letterTypeEntries as $value) {
                        $VALUES = self::getLetterDashoardByMonthType($value->ID, $objectId);
                        $DATASET .= $i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }
        $DATASET .= "]";

        return $DATASET;
    }

    public static function getLetterDashoardByDayType($letterType, $objectId) {

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
                $RESULT .= $i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlLetterDashoardByDayType($day, $letterType, $objectId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlLetterDashoardByDayType($day, $letterType, $objectId) {

        $SQL = self::dbAccess()->select();
        /* if ($objectId) {
          $SQL->from(array("LP" => "t_letter_person"), array("C" => "COUNT(*)"));
          $SQL->joinLeft(array("L" => "t_letter"), "LP.LETTER_ID = L.ID", array());
          $SQL->where("LP.PERSON_ID = ?", "{$objectId}");
          } else { */
        $SQL->from(array("L" => "t_letter"), array("C" => "COUNT(*)"));
        //}

        if ($day)
            $SQL->where("WEEKDAY(CREATED_DATE) = ?", "{$day}");

        if ($letterType)
            $SQL->where("L.LETTER_TYPE = ?", $letterType);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getLetterDashoardByWeekType($letterType, $objectId) {
        $DAYS = array(
            '01' => "01", '02' => "02", '03' => "03", '04' => "04", '05' => "05", '06' => "06"
            , '07' => "07", '08' => "08", '09' => "09", '10' => "10", '11' => "11", '12' => "12"
            , '13' => "13", '14' => "14", '15' => "15", '16' => "16", '17' => "17", '18' => "18"
            , '19' => "19", '20' => "20", '21' => "21", '22' => "22", '23' => "23", '24' => "24"
            , '25' => "25", '26' => "26", '27' => "27", '28' => "28", '29' => "29", '30' => "30"
            , '31' => "31"
        );
        $RESULT = "[";
        if ($DAYS) {
            $i = 0;
            foreach ($DAYS as $day => $value) {
                $RESULT .= $i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlLetterDashoardByWeekType($day, $letterType, $objectId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlLetterDashoardByWeekType($day, $letterType, $objectId) {

        $SQL = self::dbAccess()->select();
        /* if ($objectId) {
          $SQL->from(array("LP" => "t_letter_person"), array("C" => "COUNT(*)"));
          $SQL->joinLeft(array("L" => "t_letter"), "LP.LETTER_ID = L.ID", array());
          $SQL->where("LP.PERSON_ID = ?", "{$objectId}");
          } else { */
        $SQL->from(array("L" => "t_letter"), array("C" => "COUNT(*)"));
        //}

        if ($day)
            $SQL->where("WEEKDAY(CREATED_DATE) = ?", "{$day}");

        if ($letterType)
            $SQL->where("L.LETTER_TYPE = ?", $letterType);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getLetterDashoardByMonthType($letterType, $objectId) {

        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS) {
            $i = 0;
            foreach ($MONTHS as $month => $value) {
                $RESULT .= $i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlLetterDashoardByMonthType($month, getCurrentYear(), $letterType, $objectId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlLetterDashoardByMonthType($month, $year, $letterType, $objectId) {

        $SQL = self::dbAccess()->select();
        /* if ($objectId) {
          $SQL->from(array("LP" => "t_letter_person"), array("C" => "COUNT(*)"));
          $SQL->joinLeft(array("L" => "t_letter"), "LP.LETTER_ID = L.ID", array());
          $SQL->where("LP.PERSON_ID = ?", "{$objectId}");
          } else { */
        $SQL->from(array("L" => "t_letter"), array("C" => "COUNT(*)"));
        //}

        if ($month)
            $SQL->where("MONTH(CREATED_DATE) = ?", "{$month}");

        if ($year)
            $SQL->where("YEAR(CREATED_DATE) = ?", "{$year}");

        if ($letterType)
            $SQL->where("L.LETTER_TYPE = ?", $letterType);

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}
