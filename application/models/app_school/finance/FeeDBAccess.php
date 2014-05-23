<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 6.08.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/finance/IncomeDBAccess.php';
require_once 'models/app_school/finance/IncomeCategoryDBAccess.php';
require_once 'models/app_school/finance/StudentFeeDBAccess.php';
require_once 'models/app_school/finance/FeeOptionDBAccess.php';

require_once setUserLoacalization();

class FeeDBAccess {

    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {

        $this->DB_SCHOOLYEAR = AcademicDateDBAccess::getInstance();
        $this->DB_STUDENT = StudentDBAccess::getInstance();
    }

    public static function getCurrency() {

        return Zend_Registry::get('SCHOOL')->CURRENCY;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getFeeDataFromId($Id) {

        $data = array();
        $result = self::findFeeFromId($Id);

        if ($result) {

            $CATEGORY_OBJECT = IncomeCategoryDBAccess::findObjectFromId($result->INCOME_CATEGORY);

            $data["CODE"] = $result->CODE;
            $data["ID"] = $result->ID;
            $data["STATUS"] = $result->STATUS;
            $data["NAME"] = setShowText($result->NAME);
            $data["SCHOOLYEAR"] = $result->SCHOOLYEAR;

            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);

            $data["HIDDEN_INCOME_CATEGORY"] = $result->INCOME_CATEGORY;
            $data["CHOOSE_INCOME_CATEGORY_NAME"] = $CATEGORY_OBJECT->NAME . " (" . $CATEGORY_OBJECT->ACCOUNT_NUMBER . ")";
            $data["AMOUNT_OPTION"] = displayNumberFormat($result->AMOUNT_OPTION);
            $data["STUDENT_SERVICES"] = $result->STUDENT_SERVICES ? true : false;
            $data["DISCOUNT"] = displayNumberFormat($result->DISCOUNT);

            $data["FIRST_AMOUNT"] = displayNumberFormat($result->FIRST_AMOUNT);
            $data["SECOND_AMOUNT"] = displayNumberFormat($result->SECOND_AMOUNT);
            $data["THIRD_AMOUNT"] = displayNumberFormat($result->THIRD_AMOUNT);
            $data["FOURTH_AMOUNT"] = displayNumberFormat($result->FOURTH_AMOUNT);

            $data["FIRST_TOTAL"] = displayNumberFormat($result->FIRST_TOTAL);
            $data["SECOND_TOTAL"] = displayNumberFormat($result->SECOND_TOTAL);
            $data["THIRD_TOTAL"] = displayNumberFormat($result->THIRD_TOTAL);
            $data["FOURTH_TOTAL"] = displayNumberFormat($result->FOURTH_TOTAL);

            $data["SCHOLARSHIP_A"] = $result->SCHOLARSHIP_A;
            $data["SCHOLARSHIP_B"] = $result->SCHOLARSHIP_B;
            $data["SCHOLARSHIP_C"] = $result->SCHOLARSHIP_C;
            $data["SCHOLARSHIP_D"] = $result->SCHOLARSHIP_D;

            $data["FIRST_DES"] = setShowText($result->FIRST_DES);
            $data["SECOND_DES"] = setShowText($result->SECOND_DES);
            $data["THIRD_DES"] = setShowText($result->THIRD_DES);
            $data["FOURTH_DES"] = setShowText($result->FOURTH_DES);

            $data["REMINDER"] = $result->REMINDER;
            $data["REMINDER_TEXT"] = setShowText($result->REMINDER_TEXT);

            $data["FIRST_DUE_DATE_A"] = getShowDate($result->FIRST_DUE_DATE_A);

            $data["SECOND_DUE_DATE_A"] = getShowDate($result->SECOND_DUE_DATE_A);
            $data["SECOND_DUE_DATE_B"] = getShowDate($result->SECOND_DUE_DATE_B);

            $data["SECOND_DUE_DATE_A"] = getShowDate($result->SECOND_DUE_DATE_A);
            $data["SECOND_DUE_DATE_B"] = getShowDate($result->SECOND_DUE_DATE_B);

            $data["THIRD_DUE_DATE_A"] = getShowDate($result->THIRD_DUE_DATE_A);
            $data["THIRD_DUE_DATE_B"] = getShowDate($result->THIRD_DUE_DATE_B);
            $data["THIRD_DUE_DATE_C"] = getShowDate($result->THIRD_DUE_DATE_C);

            $data["FOURTH_DUE_DATE_A"] = getShowDate($result->FOURTH_DUE_DATE_A);
            $data["FOURTH_DUE_DATE_B"] = getShowDate($result->FOURTH_DUE_DATE_B);
            $data["FOURTH_DUE_DATE_C"] = getShowDate($result->FOURTH_DUE_DATE_C);
            $data["FOURTH_DUE_DATE_D"] = getShowDate($result->FOURTH_DUE_DATE_D);

            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }

    public static function jsonLoadFee($Id) {

        $result = self::findFeeFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getFeeDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function findFeeFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_fee";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    //@veasna
    public static function findFeeTaxFromId($Id) {

        $SQL = "SELECT `A`.*, `B`.`NAME` AS `TRANSACTION_CATEGORY`, `B`.`ACCOUNT_NUMBER`, `D`.`NAME` AS `TAX_NAME`, `D`.`PERCENT` AS `TAX_PERCENT`, `D`.`FORMULAR` AS `TAX_FORMULAR`";
        $SQL .= " FROM `t_fee` AS `A`";
        $SQL .= " LEFT JOIN `t_income_category` AS `B` ON B.ID=A.INCOME_CATEGORY";
        $SQL .= " LEFT JOIN `t_tax` AS `D` ON B.TAX=D.ID";
        $SQL .= " WHERE";
        $SQL .= " `A`.ID = '" . $Id . "'";
        $SQL .= " LIMIT 1";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    //
    public static function checkGradeTrainingInFee($Id, $courseId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_fee_training", array("C" => "COUNT(*)"));
        $SQL->where("FEE = '" . $Id . "'");
        $SQL->where("TRAINING = '" . $courseId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkGradeGeneralInFee($Id, $gradeId, $schoolyearId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_fee_general", array("C" => "COUNT(*)"));
        $SQL->where("FEE = '" . $Id . "'");
        $SQL->where("GRADE = '" . $gradeId . "'");
        $SQL->where("SCHOOLYEAR = ?",$schoolyearId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function jsonTreeGradeGeneralByFee($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findFeeFromId($objectId);

        $SELECTION_A = array(
            "ID"
            , "NAME"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_grade'), $SELECTION_A);

        if (!$node) {
            $SQL->where("A.PARENT = 0");
        } else {

            $SQL->where("A.PARENT = '" . $node . "'");
            $SQL->where('A.OBJECT_TYPE = ?', "GRADE");
        }
        $SQL->order("A.SORTKEY ASC");
        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($resultRows) {

            $i = 0;
            foreach ($resultRows as $value) {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = "" . $value->NAME . "";

                if ($node == 0) {
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_explore";
                    $data[$i]['cls'] = "nodeTextBoldBlue";
                } else {
                    $data[$i]['leaf'] = true;
                    $data[$i]['iconCls'] = "icon-tag_orange";
                    $data[$i]['cls'] = "nodeTextBlue";
                    if (self::checkGradeGeneralInFee($objectId, $value->ID, $facette->SCHOOLYEAR)) {
                        $data[$i]['checked'] = true;
                    } else {
                        $data[$i]['checked'] = false;
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    public static function jsonTreeGradeTrainingByFee($params) {
        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $SELECTION_A = array(
            "ID"
            , "NAME"
            , "START_DATE"
            , "END_DATE"
            , "OBJECT_TYPE"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_training'), $SELECTION_A);

        if (!$node) {
            $SQL->where('A.OBJECT_TYPE = ?', "PROGRAM");
        } else {
            $SQL->where("A.PARENT = '" . $node . "'");
        }
        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($resultRows) {

            $i = 0;
            foreach ($resultRows as $value) {

                $data[$i]['id'] = "" . $value->ID . "";

                if ($node == 0) {
                    $data[$i]['leaf'] = false;
                    $data[$i]['text'] = "" . $value->NAME . "";
                    $data[$i]['iconCls'] = "icon-folder_explore";
                    $data[$i]['cls'] = "nodeTextBoldBlue";
                } else {

                    switch ($value->OBJECT_TYPE) {
                        case "LEVEL":
                            $data[$i]['text'] = "" . $value->NAME . "";
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-folder_explore";
                            $data[$i]['cls'] = "nodeTextBoldBlue";
                            break;
                        case "TERM":
                            $data[$i]['leaf'] = true;
                            $data[$i]['text'] = "" . getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE) . "";
                            $data[$i]['iconCls'] = "icon-tag_orange";
                            $data[$i]['cls'] = "nodeTextBlue";
                            if (self::checkGradeTrainingInFee($objectId, $value->ID)) {
                                $data[$i]['checked'] = true;
                            } else {
                                $data[$i]['checked'] = false;
                            }
                            break;
                    }
                }

                $i++;
            }
        }

        return $data;
    }

    public static function jsonSaveFee($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";

        $facette = self::findFeeFromId($objectId);

        if (isset($params["START_DATE"]) && isset($params["END_DATE"])) {
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);
            $SAVEDATA["END_DATE"] = setDate2DB($params["END_DATE"]);
        }

        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if (isset($params["SCHOOLYEAR"]))
            $SAVEDATA["SCHOOLYEAR"] = $params["SCHOOLYEAR"];

        if (isset($params["HIDDEN_INCOME_CATEGORY"]))
            $SAVEDATA["INCOME_CATEGORY"] = $params["HIDDEN_INCOME_CATEGORY"];

        if (isset($params["type"]))
            $SAVEDATA["TYPE"] = $params["type"];

        if (isset($params["AMOUNT_OPTION"]))
            $SAVEDATA["AMOUNT_OPTION"] = $params["AMOUNT_OPTION"];

        if (isset($params["services"])) {
            $SAVEDATA["STUDENT_SERVICES"] = $params["services"];
            if (isset($params["FIRST_AMOUNT"])) {
                $SAVEDATA["FIRST_AMOUNT"] = str2no($params["FIRST_AMOUNT"]);
                $SAVEDATA["FIRST_TOTAL"] = str2no($params["FIRST_AMOUNT"]);
            }
        }

        $SAVEDATA["TYPE"] = $type;

        if ($facette) {
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_fee', $SAVEDATA, $WHERE);
        } else {
            $SAVEDATA["CODE"] = self::getFeeCode();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_fee', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public static function jsonReleaseFee($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $facette = self::findFeeFromId($objectId);
        $newStatus = 0;

        if ($facette) {
            switch ($facette->STATUS) {
                case 0:
                    $newStatus = 1;
                    $SAVEDATA["STATUS"] = 1;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update('t_fee', $SAVEDATA, $WHERE);
                    break;
                case 1:
                    $newStatus = 0;
                    $SAVEDATA["STATUS"] = 0;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update('t_fee', $SAVEDATA, $WHERE);
                    break;
            }
        }

        return array("success" => true, "status" => $newStatus);
    }

    public static function removeFee($Id) {

        self::dbAccess()->delete('t_fee', array("ID = '" . $Id . "'"));
        self::dbAccess()->delete('t_fee_option', array("FEE_ID = '" . $Id . "'"));
        self::dbAccess()->delete('t_income', array("FEE = '" . $Id . "'"));
        self::dbAccess()->delete('t_fee_training', array("FEE = '" . $Id . "'"));
        self::dbAccess()->delete('t_fee_general', array("FEE = '" . $Id . "'"));
        self::dbAccess()->delete('t_student_fee', array("FEE = '" . $Id . "'"));

        return array("success" => true);
    }

    protected static function deleteGradeGeneralFromFee($objectId, $gradeId, $schoolyearId) {

        //error_log("Fee: ".$objectId." Grade: ".$gradeId. " Schoolyear: ".$schoolyearId);     
        self::dbAccess()->delete('t_fee_general', array("FEE='" . $objectId . "'", "GRADE='" . $gradeId . "'", "SCHOOLYEAR='" . $schoolyearId . "'"));
        self::dbAccess()->delete('t_student_fee', array("FEE='" . $objectId . "'", "GRADE='" . $gradeId . "'", "SCHOOLYEAR='" . $schoolyearId . "'"));
        self::dbAccess()->delete('t_income', array("FEE='" . $objectId . "'", "GRADE='" . $gradeId . "'"));
    }

    protected static function deleteGradeTrainingFromFee($objectId, $gradeId) {

        self::dbAccess()->delete('t_fee_training', array("FEE='" . $objectId . "'", "TRAINING='" . $gradeId . "'"));
        self::dbAccess()->delete('t_student_fee', array("FEE='" . $objectId . "'", "TRAINING='" . $gradeId . "'"));
        self::dbAccess()->delete('t_income', array("FEE='" . $objectId . "'", "TRAINING='" . $gradeId . "'"));
    }

    protected static function addGradeGeneralToFee($objectId, $gradeId, $schoolyearId) {

        $SAVEDATA = array(
            'FEE' => $objectId
            , 'GRADE' => $gradeId
            , 'SCHOOLYEAR' => $schoolyearId
        );
        self::dbAccess()->insert('t_fee_general', $SAVEDATA);
    }

    protected static function addGradeTrainingToFee($objectId, $gradeId) {

        $SAVEDATA = array(
            'FEE' => $objectId
            , 'TRAINING' => $gradeId
        );
        self::dbAccess()->insert('t_fee_training', $SAVEDATA);
    }

    ///////////////////////////////////////////////////
    // Grade Schoolyear into Fee...
    // Student into Fee...
    ///////////////////////////////////////////////////

    public static function jsonActionGradeGeneralToFee($params) {

        $checked = $params["checked"];
        $objectId = $params["objectId"];
        $gradeId = $params["gradeId"];

        $facette = self::findFeeFromId($objectId);

        //error_log($facette->SCHOOLYEAR);

        if ($facette) {

            if (!$facette->STATUS) {

                if ($checked == "false") {
                    //Delete all...

                    $checkFeeIncome = IncomeDBAccess::checkIncomeFeeGrade($objectId, $gradeId);

                    if (!$checkFeeIncome) {
                        self::deleteGradeGeneralFromFee($objectId, $gradeId, $facette->SCHOOLYEAR);
                        $msg = RECORD_WAS_REMOVED;
                    } else {
                        $msg = MSG_RECORD_NOT_CHANGED_DELETED;
                    }
                } elseif ($checked == "true") {

                    $msg = RECORD_WAS_ADDED;

                    self::addGradeGeneralToFee(
                            $objectId
                            , $gradeId
                            , $facette->SCHOOLYEAR
                    );
                    //Set all...  
                    StudentFeeDBAccess::setStudents2GeneralFee($objectId, $gradeId, $facette->SCHOOLYEAR);
                }
            } else {
                $msg = MSG_RECORD_NOT_CHANGED_DELETED;
            }
        } else {
            $msg = MSG_RECORD_NOT_CHANGED_DELETED;
        }

        return array("success" => true, "msg" => $msg);
    }

    public static function jsonActionGradeTrainingToFee($params) {

        $checked = $params["checked"];
        $objectId = $params["objectId"];
        $trainingId = $params["gradeId"];

        $facette = self::findFeeFromId($objectId);

        if ($facette) {

            if (!$facette->STATUS) {
                if ($checked == "false") {

                    self::deleteGradeTrainingFromFee(
                            $objectId
                            , $trainingId
                    );
                    $msg = RECORD_WAS_REMOVED;
                } elseif ($checked == "true") {

                    self::addGradeTrainingToFee(
                            $objectId
                            , $trainingId
                    );
                    //Set all...
                    StudentFeeDBAccess::setStudents2TrainingFee($objectId, $trainingId);
                    $msg = RECORD_WAS_ADDED;
                }
            } else {
                $msg = MSG_RECORD_NOT_CHANGED_DELETED;
            }
        } else {
            $msg = MSG_RECORD_NOT_CHANGED_DELETED;
        }

        return array("success" => true, "msg" => $msg);
    }

    public function getFeesByGradeSchoolyear($gradeId, $schoolyearId, $status) {

        $SELECT_FEE_DATA = array(
            "NAME"
            , "ID"
            , "CODE"
            , "TYPE"
            , "FIRST_AMOUNT"
            , "SECOND_AMOUNT"
            , "THIRD_AMOUNT"
            , "FOURTH_AMOUNT"
            , "FIRST_DES"
            , "SECOND_DES"
            , "THIRD_DES"
            , "FOURTH_DES"
            , "CREATED_DATE"
            , "CREATED_BY"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_fee'), $SELECT_FEE_DATA);
        $SQL->joinLeft(array('B' => 't_fee_general'), 'A.ID=B.FEE', array());
        $SQL->joinLeft(array('C' => 't_income_category'), 'A.INCOME_CATEGORY=C.ID', array("NAME AS CATEGORY_NAME"));
        $SQL->where('B.GRADE = ?', "" . $gradeId . "");
        $SQL->where('B.SCHOOLYEAR= ?', "" . $schoolyearId . "");
        if ($status)
            $SQL->where("A.STATUS=1");
        $SQL->order('A.NAME');

        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);

        return $resultRows;
    }

    protected function getGradesByFee($Id) {

        $SQL = self::dbSelectAccess()
                ->distinct()
                ->from(array('A' => 't_fee_general'), array('*'))
                ->where('A.FEE = ?', "" . $Id . "");
        //echo $SQL->__toString();
        $resultRows = self::dbAccess()->fetchAll($SQL);

        return $resultRows;
    }

    public static function sqlSearchFee($params) {

        $name = isset($params["name"]) ? addText($params["name"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $isService = isset($params["isService"]) ? addText($params["isService"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $code = isset($params["code"]) ? addText($params["code"]) : "";

        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";

        $SELECTION_A = array(
            "ID AS ID"
            , "CODE AS CODE"
            , "NAME AS NAME"
            , "AMOUNT_OPTION AS AMOUNT_OPTION"
            , "FIRST_TOTAL AS FIRST_TOTAL"
            , "SECOND_TOTAL AS SECOND_TOTAL"
            , "THIRD_TOTAL AS THIRD_TOTAL"
            , "FOURTH_TOTAL AS FOURTH_TOTAL"
            , "SCHOLARSHIP_A AS SCHOLARSHIP_A"
            , "SCHOLARSHIP_B AS SCHOLARSHIP_B"
            , "SCHOLARSHIP_C AS SCHOLARSHIP_C"
            , "SCHOLARSHIP_D AS SCHOLARSHIP_D"
            , "START_DATE"
            , "END_DATE"
            , "CREATED_DATE"
            , "CREATED_BY"
        );

        $SELECTION_B = array(
            "NAME AS INCOME_CATEGORY"
            , "ACCOUNT_NUMBER AS ACCOUNT_NUMBER"
        );

        $SELECTION_C = array(
            "NAME AS SCHOOLYEAR_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_fee'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_income_category'), 'B.ID=A.INCOME_CATEGORY', $SELECTION_B);

        if ($code)
            $SQL->where("A.CODE LIKE 'FEE-" . $code . "%'");

        if ($isService) {
            $SQL->where("A.STUDENT_SERVICES='1'");
            if ($startDate && $endDate)
                $SQL->where("A.START_DATE>='" . $startDate . "' AND A.END_DATE<='" . $endDate . "'");
        }else {
            $SQL->where("A.STUDENT_SERVICES='0'");
        }

        switch ($type) {
            case "COURSE":
                $SQL->joinLeft(array('D' => 't_fee_training'), 'A.ID=D.FEE', array());
                if ($trainingId)
                    $SQL->where("D.TRAINING='" . $trainingId . "'");
                $SQL->where("A.TYPE='COURSE'");
                break;
            case "GENERAL_EDU":
                $SQL->joinLeft(array('C' => 't_academicdate'), 'C.ID=A.SCHOOLYEAR', $SELECTION_C);
                $SQL->joinLeft(array('D' => 't_fee_general'), 'A.ID=D.FEE', array());
                if ($schoolyearId)
                    $SQL->where("A.SCHOOLYEAR='" . $schoolyearId . "'");
                if ($gradeId)
                    $SQL->where("D.GRADE='" . $gradeId . "'");
                $SQL->where("A.TYPE='GENERAL_EDU'");
                break;
        }

        if ($name)
            $SQL->where("A.NAME LIKE '" . $name . "%'");

        $SQL->order('A.CREATED_DATE DESC');
        //error_log($type." ##### ".$SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonSearchFee($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::sqlSearchFee($params);
        $i = 0;

        $data = array();
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;

                /*
                  if(isset($value->SCHOOLYEAR_NAME)){
                  $data[$i]["NAME"] = "(".setShowText($value->CODE).") ".setShowText($value->NAME)." (".$value->SCHOOLYEAR_NAME.")";
                  }else{
                  $data[$i]["NAME"] = "(".setShowText($value->CODE).") ".setShowText($value->NAME);
                  }
                 */
                $data[$i]["NAME"] = "(" . setShowText($value->CODE) . ") " . setShowText($value->NAME);
                $data[$i]["AMOUNT_OPTION"] = setShowText($value->AMOUNT_OPTION);
                $data[$i]["INCOME_CATEGORY"] = $value->ACCOUNT_NUMBER;
                $firstOptionFee = FeeOptionDBAccess::getFristFeeOptionByFeeId($value->ID); //$veasna
                $data[$i]["FIRST_OPTION_AMOUNT"] = $firstOptionFee ? displayNumberFormat($firstOptionFee->TOTAL) . " " . self::getCurrency() : '---'; //$veasna
                $data[$i]["AMOUNT"] = displayNumberFormat($value->FIRST_TOTAL) . " " . self::getCurrency();

                $data[$i]["SCHOLARSHIP_A"] = $value->SCHOLARSHIP_A . "%";
                $data[$i]["SCHOLARSHIP_B"] = $value->SCHOLARSHIP_B . "%";
                $data[$i]["SCHOLARSHIP_C"] = $value->SCHOLARSHIP_C . "%";
                $data[$i]["SCHOLARSHIP_D"] = $value->SCHOLARSHIP_D . "%";

                $data[$i]["DATE"] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);

                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["CREATED_DATE"] .= "<br>";
                $data[$i]["CREATED_DATE"] .= $value->CREATED_BY;

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function getFeeCode() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_fee", array("CODE"));
        $SQL->order('CODE DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        if ($result) {
            $cal = substr($result->CODE, 4) + 1;
            return "FEE-" . $cal;
        } else {
            return "FEE-100";
        }
    }

}

?>