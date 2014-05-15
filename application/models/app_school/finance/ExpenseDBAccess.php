<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 6.08.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/finance/ExpenseCategoryDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once setUserLoacalization();

class ExpenseDBAccess {

    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        
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

    public static function getExpenseDataFromId($Id) {

        $result = self::findExpenseFromId($Id);

        $data = array();
        if ($result) {
            $category = ExpenseCategoryDBAccess::findObjectFromId($result->EXPENSE_CATEGORY);
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["TRANSACTION_NUMBER"] = setShowText($result->TRANSACTION_NUMBER);
            $data["PAYMENT_AMOUNT"] = displayNumberFormat($result->AMOUNT);
            $data["TRANSACTION_TYPE"] = $result->TRANSACTION_TYPE;
            $data["CHOOSE_EXPENSE_CATEGORY"] = $result->EXPENSE_CATEGORY;

            if ($category)
                $data["CHOOSE_EXPENSE_CATEGORY_NAME"] = $category->NAME . " (" . $category->ACCOUNT_NUMBER . ")";
            $data["EXPENSE_STATUS"] = $result->EXPENSE_STATUS;
            $data["CREATED_DATE"] = $result->CREATED_DATE;
            $data["CREATED_BY"] = $result->CREATED_BY;
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);

            if ($result->STAFF) {
                $staffObject = StaffDBAccess::findStaffFromId($result->STAFF);
                $data["STAFF"] = $staffObject->LASTNAME . " " . $staffObject->FIRSTNAME . " (" . $staffObject->CODE . ")";
                $data["PAYMENT_TYPE"] = constant($result->PAYMENT_TYPE);
                $data["DATE"] = getShowDate($result->START_DATE) . " - " . getShowDate($result->END_DATE);
            }

            if ($result->STUDENT) {
                $studentObject = StudentDBAccess::findStudentFromId($result->STUDENT);
                $data["STUDENT"] = $studentObject->LASTNAME . " " . $studentObject->FIRSTNAME . " (" . $studentObject->CODE . ")";
                $data["DATE"] = getShowDate($result->START_DATE) . " - " . getShowDate($result->END_DATE);
            }

            $CHECK_DATA = explode(",", $result->FINANCE_DESCRIPTION);

            $SQL = self::dbAccess()->select();
            $SQL->from("t_finance_description", array('*'));
            $result = self::dbAccess()->fetchAll($SQL);

            if ($result) {
                foreach ($result as $value) {

                    $descriptionObject = FinanceDescriptionDBAccess::findObjectFromId($value->ID);
                    switch ($value->CHOOSE_TYPE) {
                        case 1:
                            if (in_array($value->ID, $CHECK_DATA)) {
                                $data["CHECKBOX_" . $value->ID] = true;
                            } else {
                                $data["CHECKBOX_" . $value->ID] = false;
                            }

                            break;
                        case 2:
                            if (in_array($value->ID, $CHECK_DATA)) {
                                $data["RADIOBOX_" . $descriptionObject->PARENT] = $value->ID;
                            }
                            break;
                    }
                }
            }
        }

        return $data;
    }

    public static function findExpenseFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_expense";
        $SQL .= " WHERE";
        if (is_numeric($Id)) {
            $SQL .= " ID = '" . $Id . "'";
        } else {
            $SQL .= " GUID = '" . $Id . "'";
        }
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    public static function loadExpense($Id) {

        $result = self::findExpenseFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getExpenseDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function addExpense($params) {

        $SAVEDATA = array();
        $SAVEDATA['NAME'] = addText($params["name"]);

        if ($params["parentId"] > 0) {
            $SAVEDATA['PARENT'] = $params["parentId"];
        } else {
            $SAVEDATA['PARENT'] = 0;
        }
        self::dbAccess()->insert('t_expense', $SAVEDATA);
        return array("success" => true, "text" => addText($params["name"]));
    }

    public static function removeExpense($removeId) {

        self::sqlRemoveExpense($removeId);
        return array("success" => true);
    }

    public static function sqlRemoveExpense($Id) {

        $SQL = "DELETE FROM t_expense";
        $SQL .= " WHERE";
        $SQL .= " GUID = '" . $Id . "'";
        //error_log($SQL);
        self::dbAccess()->query($SQL);
    }

    public static function saveExpense($params) {

        $SAVEDATA = array();
        $RADIOBOX_DATA = array();
        $CHECKBOX_DATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $facette = self::findExpenseFromId($objectId);

        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);

        if (isset($params["PAYMENT_AMOUNT"]))
            $SAVEDATA["AMOUNT"] = str2no($params["PAYMENT_AMOUNT"]);

        if (isset($params["TRANSACTION_TYPE"]))
            $SAVEDATA["TRANSACTION_TYPE"] = addText($params["TRANSACTION_TYPE"]);

        if (isset($params["CHOOSE_EXPENSE_CATEGORY"]))
            $SAVEDATA["EXPENSE_CATEGORY"] = addText($params["CHOOSE_EXPENSE_CATEGORY"]);

        if (isset($params["EXPENSE_STATUS"]))
            $SAVEDATA["EXPENSE_STATUS"] = addText($params["EXPENSE_STATUS"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        ////////////////////////////////////////////////////////////////////////
        //Description....
        ////////////////////////////////////////////////////////////////////////
        $SQL = self::dbAccess()->select();
        $SQL->from("t_finance_description", array('*'));
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());

        if ($result) {
            foreach ($result as $value) {

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? $params["CHECKBOX_" . $value->ID . ""] : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? $params["RADIOBOX_" . $value->ID . ""] : "";

                if ($RADIOBOX) {
                    $RADIOBOX_DATA[$RADIOBOX] = $RADIOBOX;
                }

                if ($CHECKBOX == "on") {
                    $CHECKBOX_DATA[$value->ID] = $value->ID;
                }
            }
        }

        $CHOOSE_DATA = $CHECKBOX_DATA + $RADIOBOX_DATA;
        $SAVEDATA["FINANCE_DESCRIPTION"] = implode(",", $CHOOSE_DATA);
        ////////////////////////////////////////////////////////////////////////

        if ($facette) {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_expense', $SAVEDATA, $WHERE);
        } else {

            $SAVEDATA["GUID"] = generateGuid();
            $SAVEDATA['TRANSACTION_NUMBER'] = self::getTNExpense();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert('t_expense', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public static function sqlSearchExpense($params) {

        $transactionNumber = isset($params["transactionNumber"]) ? addText($params["transactionNumber"]) : "";
        $startDate = isset($params["startDate"]) ? addText($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? addText($params["endDate"]) : "";
        $categoryId = isset($params["categoryId"]) ? $params["categoryId"] : "";
        $financeDescription = isset($params["financeDescription"]) ? addText($params["financeDescription"]) : "";
        $name = isset($params["name"]) ? addText($params["name"]) : "";

        $SELECTION_A = array(
            "ID AS ID"
            , "GUID AS GUID"
            , "NAME AS TRANSACTION_NAME"
            , "TRANSACTION_NUMBER AS TRANSACTION_NUMBER"
            , "AMOUNT AS TRANSACTION_AMOUNT"
            , "TRANSACTION_TYPE AS TRANSACTION_TYPE"
            , "EXPENSE_CATEGORY AS TRANSACTION_CATEGORY"
            , "CREATED_DATE AS TRANSACTION_DATE"
            , "EXPENSE_STATUS AS EXPENSE_STATUS"
            , "CREATED_BY AS CREATED_BY"
        );

        $SELECTION_B = array(
            "NAME AS TRANSACTION_CATEGORY"
            , "ACCOUNT_NUMBER AS ACCOUNT_NUMBER"
        );

        $SELECTION_C = array(
            "NAME AS TAX_NAME"
            , "PERCENT AS TAX_PERCENT"
            , "FORMULAR AS TAX_FORMULAR"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_expense'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_expense_category'), 'B.ID=A.EXPENSE_CATEGORY', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_tax'), 'C.ID=B.TAX', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_staff'), 'D.ID=A.STAFF', array("CODE AS STAFF_CODE", "LASTNAME AS LASTNAME", "FIRSTNAME AS FIRSTNAME"));

        if ($categoryId)
            $SQL->where("A.EXPENSE_CATEGORY='" . $categoryId . "'");

        if ($startDate && $endDate) {
            $SQL->where("DATE(A.CREATED_DATE) BETWEEN '" . $startDate . "' AND '" . $endDate . "'");
        }

        if ($transactionNumber) {
            $SQL->where("A.TRANSACTION_NUMBER LIKE '%" . strtoupper($transactionNumber) . "%'");
        }

        if ($financeDescription)
            $SQL->where("A.FINANCE_DESCRIPTION LIKE '%" . $financeDescription . "%'");
            
        if($name)
            $SQL->where("A.NAME LIKE '%" . $name . "%'"); 

        $SQL->order('A.CREATED_DATE DESC');

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonSearchExpense($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $result = self::sqlSearchExpense($params);
        $i = 0;

        $data = array();
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->GUID;
                $data[$i]["TRANSACTION_NAME"] = displayNumberFormat($value->TRANSACTION_NUMBER);
                $data[$i]["TRANSACTION_NAME"] .= "<br>";
                $data[$i]["TRANSACTION_NAME"] .= $value->TRANSACTION_NAME;

                if ($value->STAFF_CODE) {
                    $data[$i]["TRANSACTION_NAME"] .= "(" . $value->STAFF_CODE . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }

                if ($value->ACCOUNT_NUMBER) {
                    $data[$i]["TRANSACTION_CATEGORY"] = setShowText($value->ACCOUNT_NUMBER);
                } else {
                    $data[$i]["TRANSACTION_CATEGORY"] = "---";
                }

                $data[$i]["TRANSACTION_AMOUNT"] = displayNumberFormat($value->TRANSACTION_AMOUNT) . " " . self::getCurrency();
                $data[$i]["PAYMENT_PAID"] = displayNumberFormat($value->TRANSACTION_AMOUNT) . " " . self::getCurrency();

                if ($value->TAX_PERCENT && $value->TAX_FORMULAR) {
                    switch ($value->TAX_FORMULAR) {
                        case 1:
                            $AMOUNT_TAX = calculateTax($value->TRANSACTION_AMOUNT, $value->TAX_PERCENT) . " " . self::getCurrency();
                            $TOTAL = displayNumberFormat($value->TRANSACTION_AMOUNT) . " " . self::getCurrency();
                            $PAYMENT_PAID = $TOTAL - $AMOUNT_TAX;
                            $data[$i]["TAX_CATEGORY"] = $value->TAX_NAME;
                            $data[$i]["TAX_CATEGORY"] .= "<br>";
                            $data[$i]["TAX_CATEGORY"] .= " (+" . displayNumberFormat($value->TAX_PERCENT) . "%)";
                            $data[$i]["AMOUNT_TAX"] = $AMOUNT_TAX;
                            $data[$i]["PAYMENT_PAID"] = $PAYMENT_PAID ? $PAYMENT_PAID . " " . self::getCurrency() : "---";
                            break;
                        case 2:
                            $AMOUNT_TAX = calculateTax($value->TRANSACTION_AMOUNT, $value->TAX_PERCENT) . " " . self::getCurrency();
                            $TOTAL = displayNumberFormat($value->TRANSACTION_AMOUNT) . " " . self::getCurrency();
                            $PAYMENT_PAID = $TOTAL - $AMOUNT_TAX;
                            $data[$i]["TAX_CATEGORY"] = $value->TAX_NAME;
                            $data[$i]["TAX_CATEGORY"] .= "<br>";
                            $data[$i]["TAX_CATEGORY"] .= " (-" . displayNumberFormat($value->TAX_PERCENT) . "%)";
                            $data[$i]["AMOUNT_TAX"] = $AMOUNT_TAX;
                            $data[$i]["TOTAL_AMOUNT"] = calculateTaxMinus($value->TRANSACTION_AMOUNT, $value->TAX_PERCENT) . " " . self::getCurrency();
                            $data[$i]["PAYMENT_PAID"] = $PAYMENT_PAID ? $PAYMENT_PAID . " " . self::getCurrency() : "---";
                            break;
                    }
                } else {
                    $data[$i]["TAX_CATEGORY"] = setShowText($value->TAX_NAME);
                    $data[$i]["AMOUNT_TAX"] = "---";
                }

                $data[$i]["TRANSACTION_DATE"] = setShowText($value->TRANSACTION_DATE);
                $data[$i]["TRANSACTION_DATE"] .= "<br>";
                $data[$i]["TRANSACTION_DATE"] .= setShowText($value->CREATED_BY);

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

    public static function getTNExpense($type = false) {

        $_type = $type ? $type : "EXP";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array("TRANSACTION_NUMBER"));
        $SQL->where("TRANSACTION_NUMBER LIKE '" . $_type . "-%'");
        //$SQL->where("TRANSACTION_NUMBER LIKE '" . $_type . "-%'");
        $SQL->order('TRANSACTION_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            $cal = substr($result->TRANSACTION_NUMBER, 4) + 1;
            return "" . $_type . "-" . $cal;
        } else {
            return "" . $_type . "-100";
        }
    }

}

?>