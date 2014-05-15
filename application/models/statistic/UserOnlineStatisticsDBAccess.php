<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 04.07.2010 
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class UserOnlineStatisticsDBAccess {

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

    public static function getCountUserOnline($roleId, $date) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_logininfo"), array("C" => "COUNT(*)"));
        $SQL->joinLeft(array('B' => 't_memberrole'), 'A.ROLE=B.ID', array());
        $SQL->where("A.ROLE = '" . $roleId . "'");
        $SQL->where("DATE(A.DATE) = '" . $date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getDatasetDateUserCountByRoleId($roleId) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_logininfo"), array("DATE(DATE) AS SECTION_DATE", "DATE"));
        $SQL->joinLeft(array('B' => 't_memberrole'), 'A.ROLE=B.ID', array());
        $SQL->group("DATE(A.DATE)");
        $SQL->order("DATE(A.DATE) ASC");
        //error_log($SQL->__toString());
        $entries = self::dbAccess()->fetchAll($SQL);

        $RESULT = "[";
        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {
                $RESULT .=$i ? "," : "";
                $RESULT .= "[" . strtotime($value->SECTION_DATE) * 1000 . "";
                $RESULT .= "," . self::getCountUserOnline($roleId, $value->SECTION_DATE) . "]";
                $i++;
            }
        }
        $RESULT .= "]";

        return $RESULT;
    }

    ////////////////////////////////////////////////////////////////////////////
    //DATASET USER ONLINE...
    ////////////////////////////////////////////////////////////////////////////
    public static function getDataSetUserOnline() {

        $result = UserRoleDBAccess::allUserRole();

        $DATASET = "[";
        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $VALUES = self::getDatasetDateUserCountByRoleId($value->ID);
                $DATASET .=$i ? "," : "";
                $DATASET .= "{'key' : '" . setShowText($value->NAME) . "','values':" . $VALUES . "}";
                $i++;
            }
        }

        $DATASET .= "]";

        return $DATASET;
    }

}

?>
