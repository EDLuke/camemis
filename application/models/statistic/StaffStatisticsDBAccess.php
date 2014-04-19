<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/BuildData.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/AbsentTypeDBAccess.php";
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StaffStatisticsDBAccess {

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

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getSqlStaffRoleGenderStatistics($roleId) {

        $SQL = "";
        $SQL .= "SELECT 
                        SUM( IF( A.GENDER = 1, 1, 0 ) ) AS MALE, 
                        SUM( IF( A.GENDER = 2, 1, 0 ) ) AS FEMALE, 
                        SUM( IF( A.GENDER <> 1 AND A.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
                        COUNT( A.GENDER ) AS TOTAL
                   FROM t_staff AS A 
              LEFT JOIN t_members AS B ON B.ID = A.ID";
        $SQL .= " LEFT JOIN t_memberrole AS C ON C.ID = B.ROLE";
        $SQL .= " WHERE B.ROLE='" . $roleId . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getSqlStaffAttendanceGenderStatistics($staffId, $absentType) {

        $SQL = "";
        $SQL .= "SELECT 
            SUM( IF( A.GENDER = 1, 1, 0 ) ) AS MALE, 
            SUM( IF( A.GENDER = 2, 1, 0 ) ) AS FEMALE, 
            SUM( IF( A.GENDER <> 1 AND A.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
            COUNT( A.GENDER ) AS TOTAL
        FROM t_staff AS A 
        LEFT JOIN t_staff_attendance AS B ON B.STAFF_ID = A.ID";

        $SQL .= " WHERE 1 = 1";

        if ($staffId)
            $SQL .= " AND B.STAFF_ID='" . $staffId . "'";

        if ($absentType) {
            $SQL .= " AND B.ABSENT_TYPE='" . $absentType . "' ";
        }

        $SQL .= " AND YEAR(B.END_DATE)='2013'";

        $SQL .= " GROUP  BY B.ABSENT_TYPE";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getSqlStaffAttendanceByMonthType($month, $absentType, $staffId) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_staff_attendance";
        $SQL .= " WHERE 1 = 1";

        $SQL .= " AND YEAR(END_DATE)=YEAR(NOW())";

        if ($month)
            $SQL .= " AND MONTH(END_DATE)='" . $month . "'";

        if ($absentType) {
            $SQL .= " AND ABSENT_TYPE='" . $absentType . "' ";
        }

        if ($staffId) {
            $SQL .= " AND STAFF_ID='" . $staffId . "' ";
        }

        $SQL .= " GROUP BY ABSENT_TYPE";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStaffAttendanceByMonthType($absentType, $staffId) {
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
            foreach ($MONTHS as $month => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStaffAttendanceByMonthType($month, $absentType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    ///////////////////////////////////////////////////////////////////////////////////
    //@Sea Peng 29.08.2013
    ///////////////////////////////////////////////////////////////////////////////////
    public static function getStaffAttendanceByDayType($absentType, $staffId) {
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
                $RESULT .= ",'y':" . self::getSqlStaffAttendanceByDayType($day, $absentType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffAttendanceByDayType($day, $absentType, $staffId) {

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_staff_attendance";
        $SQL .= " WHERE 1 = 1";

        $SQL .= " AND WEEKOFYEAR(END_DATE)=WEEKOFYEAR(NOW())";

        if ($day)
            $SQL .= " AND WEEKDAY(END_DATE)='" . $day . "'";

        if ($absentType) {
            $SQL .= " AND ABSENT_TYPE='" . $absentType . "' ";
        }

        if ($staffId) {
            $SQL .= " AND STAFF_ID='" . $staffId . "' ";
        }

        $SQL .= " GROUP BY ABSENT_TYPE";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStaffAttendanceByWeekType($absentType, $staffId) {
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
                $RESULT .= ",'y':" . self::getSqlStaffAttendanceByWeekType($day, $absentType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffAttendanceByWeekType($day, $absentType, $staffId) {
        //error_log($week);

        $SQL = "";
        $SQL .= "SELECT COUNT(*) AS C FROM t_staff_attendance";
        $SQL .= " WHERE 1 = 1";

        $SQL .= " AND MONTH(END_DATE)=MONTH(NOW())";

        if ($day)
            $SQL .= " AND DAYOFMONTH(END_DATE)='" . $day . "'";

        if ($absentType) {
            $SQL .= " AND ABSENT_TYPE='" . $absentType . "' ";
        }

        if ($staffId) {
            $SQL .= " AND STAFF_ID='" . $staffId . "' ";
        }

        $SQL .= " GROUP BY ABSENT_TYPE";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //STAFF...
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStaff() {
        $entries = Array(0 => UNKNOWN, 1 => MALE, 2 => FEMALE);
        $DATASET = "[";
        if ($entries) {
            $i = 0;
            foreach ($entries as $index => $value) {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value . "'";
                $DATASET .= ",'color':'" . getColorFromIndex($i) . "'";
                $DATASET .= ",'values':" . self::getDataSetStaffByRole_Gender($index) . "";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    public static function getDataSetStaffByRole_Gender($gender) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_memberrole", array("*"));
        $SQL->where("PARENT = 0");
        $SQL->where("ID<>4");
        //error_log($SQL->__toString()); 
        $result = self::dbAccess()->fetchAll($SQL);
        $DATASET = "[";
        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $SHORT = $value->SHORT ? setShowText($value->SHORT) : ($i + 1) . ") " . SHORT . "?";
                $DATASET .= $i ? "," : "";
                $DATASET .= "{'x':'" . $SHORT . "','y':" . self::getCountStaffRole_Gender($gender, $value->ID) . "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;
    }

    public static function getCountStaffRole_Gender($gender, $roleId) {
        $SQL = "";
        $SQL .= "SELECT B.ID, 
            SUM( IF( A.GENDER = 1, 1, 0 ) ) AS MALE, 
            SUM( IF( A.GENDER = 2, 1, 0 ) ) AS FEMALE, 
            SUM( IF( A.GENDER <> 1 AND A.GENDER <> 2, 1, 0 ) ) AS UNKNOWN,
            COUNT( A.GENDER ) AS TOTAL
        FROM t_staff AS A 
        LEFT JOIN t_members AS B ON B.ID = A.ID";
        $SQL .= " WHERE 1 = 1";
        $SQL .= " AND A.GENDER='" . $gender . "'";
        $SQL .= " AND B.ROLE='" . $roleId . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->TOTAL : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    //STAFF ATTENDANCE..
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStaffAttendance($objectType, $staffId) {
        $absentTypeEntries = AbsentTypeDBAccess::allAbsentType('STAFF', false);
        $DATASET = "[";
        switch ($objectType) {
            case "WEEKLY":
                if ($absentTypeEntries) {
                    $i = 0;
                    foreach ($absentTypeEntries as $value) {
                        $VALUES = self::getStaffAttendanceByDayType($value->ID, $staffId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','color':'$value->COLOR','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($absentTypeEntries) {
                    $i = 0;
                    foreach ($absentTypeEntries as $value) {
                        $VALUES = self::getStaffAttendanceByWeekType($value->ID, $staffId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','color':'$value->COLOR','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($absentTypeEntries) {
                    $i = 0;
                    foreach ($absentTypeEntries as $value) {
                        $VALUES = self::getStaffAttendanceByMonthType($value->ID, $staffId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','color':'$value->COLOR','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }

        $DATASET .= "]";

        return $DATASET;
    }

    ////////////////////////////////////////////////////////////////////////////
    //STAFF CONTRACT...
    ////////////////////////////////////////////////////////////////////////////

    public static function getDataSetStaffContract($objectType, $subjectId) {

        $contractTypeEntries = CamemisTypeDBAccess::getCamemisType('CONTRACT_TYPE', false);

        $DATASET = "[";
        switch ($objectType) {
            case "WEEKLY":
                if ($contractTypeEntries) {
                    $i = 0;
                    foreach ($contractTypeEntries as $value) {
                        $VALUES = self::getStaffContractByDayType($value->ID, $subjectId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($contractTypeEntries) {
                    $i = 0;
                    foreach ($contractTypeEntries as $value) {
                        $VALUES = self::getStaffContractByWeekType($value->ID, $subjectId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($contractTypeEntries) {
                    $i = 0;
                    foreach ($contractTypeEntries as $value) {
                        $VALUES = self::getStaffContractByMonthType($value->ID, $subjectId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }

        $DATASET .= "]";

        return $DATASET;
    }

    public static function getStaffContractByDayType($contractType, $staffId) {
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
                $RESULT .= ",'y':" . self::getSqlStaffContractByDayType($day, $contractType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffContractByDayType($day, $contractType, $staffId) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_contract'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.CONTRACT_TYPE=B.ID', array());


        if ($day)
            $SQL->where("WEEKDAY(CREATED_DATE) = '" . $day . "'");

        if ($contractType)
            $SQL->where("CONTRACT_TYPE = '" . $contractType . "'");

        if ($staffId)
            $SQL->where("STAFF_ID = '" . $staffId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStaffContractByWeekType($contractType, $staffId) {
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
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStaffContractByWeekType($day, $contractType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffContractByWeekType($day, $contractType, $staffId) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_contract'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.CONTRACT_TYPE=B.ID', array());

        if ($day)
            $SQL->where("DAYOFMONTH(CREATED_DATE) = '" . $day . "'");

        if ($contractType)
            $SQL->where("CONTRACT_TYPE = '" . $contractType . "'");

        if ($staffId)
            $SQL->where("STAFF_ID = '" . $staffId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStaffContractByMonthType($contractType, $subjectId) {

        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS) {
            $i = 0;
            foreach ($MONTHS as $month => $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStaffContractByMonthType($month, getCurrentYear(), $contractType, $subjectId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffContractByMonthType($month, $year, $contractType, $staffId) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_contract'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.CONTRACT_TYPE=B.ID', array());

        if ($month)
            $SQL->where("MONTH(CREATED_DATE) = '" . $month . "'");

        if ($year)
            $SQL->where("YEAR(CREATED_DATE) = '" . $year . "'");

        if ($contractType)
            $SQL->where("CONTRACT_TYPE = '" . $contractType . "'");

        if ($staffId)
            $SQL->where("STAFF_ID = '" . $staffId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //DISCIPLINE....
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetStaffDiscipline($objectType, $staffId)
    {

        $disciplineTypeEntries = CamemisTypeDBAccess::getCamemisType("DISCIPLINE_TYPE_STAFF", false);

        $DATASET = "[";
        switch ($objectType)
        {
            case "WEEKLY":
                if ($disciplineTypeEntries)
                {
                    $i = 0;
                    foreach ($disciplineTypeEntries as $value)
                    {
                        $VALUES = self::getStaffDisciplineByDayType($value->ID, $staffId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "MONTHLY":
                if ($disciplineTypeEntries)
                {
                    $i = 0;
                    foreach ($disciplineTypeEntries as $value)
                    {
                        $VALUES = self::getStaffDisciplineByWeekType($value->ID, $staffId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
            case "YEARLY":
                if ($disciplineTypeEntries)
                {
                    $i = 0;
                    foreach ($disciplineTypeEntries as $value)
                    {
                        $VALUES = self::getStaffDisciplineByMonthType($value->ID, $staffId);
                        $DATASET .=$i ? "," : "";
                        $DATASET .= "{'key' : '" . $value->NAME . "','values':" . $VALUES . "}";
                        $i++;
                    }
                }
                break;
        }

        $DATASET .= "]";

        return $DATASET;
    }

    public static function getStaffDisciplineByDayType($disciplineType, $staffId)
    {
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
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlSudentDisciplineByDayType($day, $disciplineType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlSudentDisciplineByDayType($day, $disciplineType, $staffId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.DISCIPLINE_TYPE=B.ID', array());

        if ($day)
            $SQL->where("WEEKDAY(CREATED_DATE) = '" . $day . "'");

        if ($disciplineType)
            $SQL->where("DISCIPLINE_TYPE = '" . $disciplineType . "'");

        if ($staffId)
            $SQL->where("STAFF_ID = '" . $staffId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStaffDisciplineByWeekType($disciplineType, $staffId)
    {
        $DAYS = array(
            '01' => "01", '02' => "02", '03' => "03", '04' => "04", '05' => "05", '06' => "06"
            , '07' => "07", '08' => "08", '09' => "09", '10' => "10", '11' => "11", '12' => "12"
            , '13' => "13", '14' => "14", '15' => "15", '16' => "16", '17' => "17", '18' => "18"
            , '19' => "19", '20' => "20", '21' => "21", '22' => "22", '23' => "23", '24' => "24"
            , '25' => "25", '26' => "26", '27' => "27", '28' => "28", '29' => "29", '30' => "30"
            , '31' => "31"
        );
        $RESULT = "[";
        if ($DAYS)
        {
            $i = 0;
            foreach ($DAYS as $day => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStaffDisciplineByWeekType($day, $disciplineType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffDisciplineByWeekType($day, $disciplineType, $staffId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.DISCIPLINE_TYPE=B.ID', array());
        
        if ($day)
            $SQL->where("DAYOFMONTH(CREATED_DATE) = '" . $day . "'");

        if ($disciplineType)
            $SQL->where("DISCIPLINE_TYPE = '" . $disciplineType . "'");

        if ($staffId)
            $SQL->where("STAFF_ID = '" . $staffId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStaffDisciplineByMonthType($disciplineType, $staffId)
    {

        $MONTHS = array(
            '01' => JANUARY, '02' => FEBRUARY, '03' => MARCH, '04' => APRIL
            , '05' => MAY, '06' => JUNE, '07' => JULY, '08' => AUGUST
            , '09' => SEPTEMBER, '10' => OCTOBER, '11' => NOVEMBER, '12' => DECEMBER
        );
        $RESULT = "[";
        if ($MONTHS)
        {
            $i = 0;
            foreach ($MONTHS as $month => $value)
            {
                $RESULT .=$i ? "," : "";
                $RESULT .= "{'x':'" . $value . "'";
                $RESULT .= ",'y':" . self::getSqlStaffDisciplineByMonthType($month, getCurrentYear(), $disciplineType, $staffId) . "}";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    public static function getSqlStaffDisciplineByMonthType($month, $year, $disciplineType, $staffId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_discipline'), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_camemis_type'), 'A.DISCIPLINE_TYPE=B.ID', array());

        if ($month)
            $SQL->where("MONTH(CREATED_DATE) = '" . $month . "'");

        if ($year)
            $SQL->where("YEAR(CREATED_DATE) = '" . $year . "'");

        if ($disciplineType)
            $SQL->where("DISCIPLINE_TYPE = '" . $disciplineType . "'");

        if ($staffId)
            $SQL->where("STAFF_ID = '" . $staffId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
}

?>
