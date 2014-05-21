<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';

class ReportDBAccess {

    function __construct() {
        
    }

    static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    private static $instance = null;

    public static function treeReportTraditionalAcademic($params) {

        $strNode = isset($params["node"]) ? addText($params["node"]) : 0;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;

        if (strpos($strNode, "SCHOOLYEAR") !== false) {
            $selectionType = "SCHOOLYEAR";
            $selectctionId = substr($strNode, 11);
        } elseif (strpos($strNode, "CAMPUS") !== false) {
            $selectionType = "CAMPUS";
            $selectctionId = substr($strNode, 7);
            $explode = explode("_", $selectctionId);
            $campusId = isset($explode[0]) ? $explode[0] : "";
            $schoolyearId = isset($explode[1]) ? $explode[1] : "";
        } elseif (strpos($strNode, "GRADE") !== false) {
            $selectionType = "GRADE";
            $selectctionId = substr($strNode, 6);
            $explode = explode("_", $selectctionId);
            $gradeId = isset($explode[0]) ? $explode[0] : "";
            $campusId = isset($explode[1]) ? $explode[1] : "";
            $schoolyearId = isset($explode[2]) ? $explode[2] : "";
        } else {
            $selectionType = "";
            $selectctionId = "";
        }

        $data = array();

        if (!$selectionType) {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_academicdate", array('*'));
            $SQL->where("STATUS = 1");
            if ($schoolyearId) {
                $SQL->where("ID = '" . $schoolyearId . "'");
            }
            $SQL->order("START ASC");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);

            $i = 0;
            if ($result) {
                foreach ($result as $value) {
                    $data[$i]['id'] = "SCHOOLYEAR_" . $value->ID;
                    $data[$i]['text'] = setShowText($value->NAME);
                    $data[$i]['leaf'] = false;
                    $data[$i]['isClick'] = false;
                    $data[$i]['objectId'] = $value->ID;
                    $data[$i]['iconCls'] = "icon-date_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                    $i++;
                }
            }
        } else {
            $SELECTION_A = array(
                "ID"
                , "NAME"
            );
            switch ($selectionType) {
                case "SCHOOLYEAR":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_grade'), $SELECTION_A);
                    $SQL->joinLeft(array('B' => 't_grade'), 'A.ID=B.CAMPUS_ID', array());
                    $SQL->where("B.SCHOOL_YEAR = '" . $selectctionId . "'");
                    $SQL->order("A.SORTKEY");
                    $SQL->group("A.ID");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchAll($SQL);

                    $i = 0;
                    if ($result) {
                        foreach ($result as $value) {
                            $data[$i]['id'] = "CAMPUS_" . $value->ID . "_" . $selectctionId;
                            $data[$i]['text'] = setShowText($value->NAME);
                            $data[$i]['isClick'] = true;
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-bricks";
                            $data[$i]['cls'] = "nodeTextBoldBlue";
                            $i++;
                        }
                    }
                    break;
                case "CAMPUS":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_grade'), $SELECTION_A);
                    $SQL->where("A.PARENT = '" . $campusId . "'");
                    $SQL->order("A.SORTKEY");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchAll($SQL);

                    $i = 0;
                    if ($result) {
                        foreach ($result as $value) {
                            $data[$i]['id'] = "GRADE_" . $value->ID . "_" . $campusId . "_" . $schoolyearId;
                            $data[$i]['text'] = setShowText($value->NAME);
                            $data[$i]['leaf'] = false;
                            $data[$i]['isClick'] = true;
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            $data[$i]['cls'] = "nodeTextBold";
                            $i++;
                        }
                    }
                    break;
                case "GRADE":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_grade'), $SELECTION_A);
                    $SQL->where("A.GRADE_ID = '" . $gradeId . "'");
                    $SQL->where("A.SCHOOL_YEAR = '" . $schoolyearId . "'");
                    $SQL->where("A.OBJECT_TYPE = 'CLASS'");
                    $SQL->order("A.SORTKEY");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchAll($SQL);

                    $i = 0;
                    if ($result) {
                        foreach ($result as $value) {
                            $data[$i]['id'] = $value->ID;
                            $data[$i]['text'] = setShowText($value->NAME);
                            $data[$i]['leaf'] = true;
                            $data[$i]['isClick'] = true;
                            $data[$i]['objectId'] = $value->ID;
                            $data[$i]['iconCls'] = "icon-blackboard";
                            $data[$i]['cls'] = "nodeTextBlue";
                            $i++;
                        }
                    }
                    break;
            }
        }

        return $data;
    }

    public static function treeReportCreditAcademic($params) {
        
    }

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

}

?>