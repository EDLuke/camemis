<?php

///////////////////////////////////////////////////////////
// @sor veasna
// Date: 12.06.2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/finance/IncomeDBAccess.php';
require_once 'models/app_school/finance/IncomeCategoryDBAccess.php';
require_once 'models/app_school/finance/StudentFeeDBAccess.php';

require_once setUserLoacalization();

class FeeOptionDBAccess {

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

    public static function jsonSaveFeeOption($param) {

        $feeId = isset($param["feeId"]) ? $param["feeId"] : '';
        $NAME = isset($param["NAME"]) ? $param["NAME"] : '';
        $AMOUNT = isset($param["AMOUNT"]) ? $param["AMOUNT"] : '';
        $TOTAL = isset($param["TOTAL"]) ? $param["TOTAL"] : '';
        $START_DATE = isset($param["START_DATE"]) ? $param["START_DATE"] : '';
        $END_DATE = isset($param["END_DATE"]) ? $param["END_DATE"] : '';
        $OPTION = isset($param["OPTION"]) ? $param["OPTION"] : '';
        $objectId = isset($param["objectId"]) ? $param["objectId"] : '';

        $SAVEDATA = array();
        if ($objectId) {
            $SAVEDATA['NAME'] = addText($NAME);
            $SAVEDATA['AMOUNT'] = addText($AMOUNT);
            $SAVEDATA['TOTAL'] = addText($TOTAL);
            $SAVEDATA['START_DATE'] = addText($START_DATE);
            $SAVEDATA['END_DATE'] = addText($END_DATE);

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_fee_option', $SAVEDATA, $WHERE);
        } else {
            $SAVEDATA['FEE_ID'] = $feeId;
            $SAVEDATA['NAME'] = $NAME;
            $SAVEDATA['AMOUNT'] = $AMOUNT;
            $SAVEDATA['TOTAL'] = $TOTAL;
            $SAVEDATA['START_DATE'] = $START_DATE;
            $SAVEDATA['END_DATE'] = $END_DATE;
            $SAVEDATA['N_OPTION'] = $OPTION;


            self::dbAccess()->insert('t_fee_option', $SAVEDATA);
        }
        return array("success" => true);
    }

    public static function jsonActionSaveFeeOptions($params) {

        $feeId = isset($params["feeId"]) ? $params["feeId"] : '';
        $nOption = isset($params["nOption"]) ? $params["nOption"] : '';

        $index = array("FIRST", "SECOND", "THIRD", "FOURTH", "FIFTH", "SIXTH", "SEVEN", "EIGHTH", "NINTH", "TENTH");

        for ($i = 1; $i <= $nOption; $i++) {
            $param = array();
            $param["feeId"] = $feeId;
            $param["NAME"] = isset($params[$index[$i - 1] . "_DES"]) ? addText($params[$index[$i - 1] . "_DES"]) : "";
            $param["AMOUNT"] = isset($params[$index[$i - 1] . "_AMOUNT"]) ? ($params[$index[$i - 1] . "_AMOUNT"]) : "";
            $param["TOTAL"] = isset($params[$index[$i - 1] . "_TOTAL"]) ? ($params[$index[$i - 1] . "_TOTAL"]) : "";
            $param["START_DATE"] = isset($params[$index[$i - 1] . "_START_DATE"]) ? setDate2DB($params[$index[$i - 1] . "_START_DATE"]) : "";
            $param["END_DATE"] = isset($params[$index[$i - 1] . "_END_DATE"]) ? setDate2DB($params[$index[$i - 1] . "_END_DATE"]) : "";
            $param["OPTION"] = $i;
            $param["objectId"] = isset($params[$index[$i - 1] . "_ID"]) ? ($params[$index[$i - 1] . "_ID"]) : "";

            self::jsonSaveFeeOption($param);
        }

        return array("success" => true);
    }

    public static function getSQLFeeOptionByFeeId($Id) {

        $SQL = self::dbAccess()->select();

        $SQL->from('t_fee_option');
        $SQL->where("FEE_ID = ?",$Id);

        $SQL->order('N_OPTION ASC');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public static function jsonLoadAllFeeOptions($params) {

        $noption = isset($params['nOption']) ? $params['nOption'] : '';
        $feeId = isset($params["feeId"]) ? $params["feeId"] : '';
        $index = array("FIRST", "SECOND", "THIRD", "FOURTH", "FIFTH", "SIXTH", "SEVEN", "EIGHTH", "NINTH", "TENTH");

        $data = array();
        $result = self::getSQLFeeOptionByFeeId($feeId);
        if ($result) {
            foreach ($result as $value) {

                $data[$index[$value->N_OPTION - 1] . "_ID"] = $value->ID;
                $data[$index[$value->N_OPTION - 1] . "_DES"] = $value->NAME;
                $data[$index[$value->N_OPTION - 1] . "_AMOUNT"] = displayNumberFormat($value->AMOUNT);
                $data[$index[$value->N_OPTION - 1] . "_TOTAL"] = displayNumberFormat($value->TOTAL);
                $data[$index[$value->N_OPTION - 1] . "_START_DATE"] = $value->START_DATE;
                $data[$index[$value->N_OPTION - 1] . "_END_DATE"] = $value->END_DATE;
            }
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function getFeeOptionById($Id) {

        $SELECTION_A = array(
            "ID"
            , "FEE_ID"
            , "NAME"
            , "AMOUNT"
            , "TOTAL"
            , "START_DATE"
            , "END_DATE"
            , "N_OPTION"
            , "DATEDIFF(END_DATE,NOW()) AS DiffDate"
        );
        $SQL = self::dbAccess()->select();

        $SQL->from('t_fee_option', $SELECTION_A);
        $SQL->where('ID=' . $Id);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function findFeeOptionByFeeIdNoption($feeId, $nOption) {

        $SQL = "SELECT DATEDIFF(END_DATE,NOW()) AS DiffDate, NAME AS TITLE, AMOUNT AS AMOUNT, TOTAL AS TOTAL, START_DATE AS START_DATE,END_DATE AS END_DATE";
        $SQL.=" FROM t_fee_option";
        $SQL.=" WHERE FEE_ID=" . $feeId;
        $SQL.=" AND N_OPTION=" . $nOption;
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function getFristFeeOptionByFeeId($feeId) {

        $SELECTION_A = array(
            "ID"
            , "FEE_ID"
            , "NAME"
            , "AMOUNT"
            , "TOTAL"
            , "START_DATE"
            , "END_DATE"
            , "N_OPTION"
            , "DATEDIFF(END_DATE,NOW()) AS DiffDate"
        );
        $SQL = self::dbAccess()->select();

        $SQL->from('t_fee_option', $SELECTION_A);
        $SQL->where("FEE_ID = ?",$feeId);
        $SQL->where('N_OPTION=1');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

}

?>