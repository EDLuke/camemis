<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 6.08.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/finance/ExpenseDBAccess.php';
require_once setUserLoacalization();

class StaffFeePaymentDBAccess extends ExpenseDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getCurrency() {

        return Zend_Registry::get('SCHOOL')->CURRENCY;
    }

    public static function jsonLoadStaffPayment($paymentId) {

        $facette = self::getStaffFeePaymentFromId($paymentId);

        if ($facette) {
            $data = array();

            $category = ExpenseCategoryDBAccess::findObjectFromId($facette->EXPENSE_CATEGORY);

            $data["PAYMENT_AMOUNT"] = displayNumberFormat($facette->AMOUNT);
            $data["START_DATE"] = getShowDate($facette->START_DATE);
            $data["END_DATE"] = getShowDate($facette->END_DATE);
            $data["TOTAL_SESSION"] = displayNumberFormat($facette->TOTAL_SESSION);
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["CHOOSE_EXPENSE_CATEGORY_NAME"] = $category ? $category->NAME : "---";
            $data["CHOOSE_EXPENSE_CATEGORY"] = $facette->EXPENSE_CATEGORY;

            $CHECK_DATA = explode(",", $facette->FINANCE_DESCRIPTION);

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

            $o = array(
                "success" => true
                , "data" => $data
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }

        return $o;
    }

    public static function removeStaffPayment($Id) {

        self::dbAccess()->delete('t_expense', array("GUID='" . $Id . "'"));

        return array(
            "success" => true
        );
    }

    public static function getStaffFeePaymentFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array('*'));
        $SQL->where("GUID = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonActionStaffPayment($params) {

        $RADIOBOX_DATA = array();
        $CHECKBOX_DATA = array();

        $paymentId = isset($params["paymentId"]) ? $params["paymentId"] : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $startdate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $enddate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $paymentType = isset($params["paymentType"]) ? addText($params["paymentType"]) : "";

        $errors = array();

        $CHECK = self::checkActionPayment(
                        $objectId
                        , $startdate
                        , $enddate
                        , $paymentType
        );

        if (isset($params["paymentType"]))
            $SAVEDATA["PAYMENT_TYPE"] = $params["paymentType"];

        if (isset($params["PAYMENT_AMOUNT"]))
            $SAVEDATA["AMOUNT"] = str2no($params["PAYMENT_AMOUNT"]);

        if (isset($params["START_DATE"]))
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);

        if (isset($params["END_DATE"]))
            $SAVEDATA["END_DATE"] = setDate2DB($params["END_DATE"]);

        if (isset($params["CHOOSE_EXPENSE_CATEGORY"]))
            $SAVEDATA["EXPENSE_CATEGORY"] = $params["CHOOSE_EXPENSE_CATEGORY"];

        if (isset($params["TOTAL_SESSION"]))
            $SAVEDATA["TOTAL_SESSION"] = $params["TOTAL_SESSION"];

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

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? addText($params["RADIOBOX_" . $value->ID . ""]) : "";

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

        $facette = self::getStaffFeePaymentFromId($paymentId);

        if (!$facette) {
            if ($CHECK) {
                $errors["START_DATE"] = USED;
                $errors["END_DATE"] = USED;
            }
        } else {

            if ($CHECK > 1) {
                $errors["START_DATE"] = USED;
                $errors["END_DATE"] = USED;
            }
        }

        if (!$facette) {

            switch ($params["paymentType"]) {
                case "PAYMENT_FOR_SALARY":
                    $SAVEDATA["NAME"] = '';
                    $SAVEDATA["TRANSACTION_NUMBER"] = self::getTNberSalary();
                    break;
                case "PAYMENT_FOR_TEACHING_SESSION":
                    $SAVEDATA["NAME"] = '';
                    $SAVEDATA["TRANSACTION_NUMBER"] = self::getTNTeachingSession();
                    break;
            }
            $SAVEDATA["GUID"] = generateGuid();
            $SAVEDATA["STAFF"] = $objectId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            if (!$errors) {
                if (str2no($params["PAYMENT_AMOUNT"]) > 0)
                    self::dbAccess()->insert('t_expense', $SAVEDATA);
            }
        } else {
            if (!$errors) {
                $WHERE = self::dbAccess()->quoteInto("GUID = ?", $paymentId);
                if (str2no($params["PAYMENT_AMOUNT"]) > 0)
                    self::dbAccess()->update('t_expense', $SAVEDATA, $WHERE);
            }
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
            );
        } else {
            return array(
                "success" => true
            );
        }
    }

    public static function jsonListStaffInvoices($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SELECTION_A = array(
            "GUID"
            , "TRANSACTION_NUMBER"
            , "AMOUNT"
            , "START_DATE"
            , "END_DATE"
            , "PAYMENT_TYPE"
            , "TOTAL_SESSION"
            , "DESCRIPTION"
            , "CREATED_DATE"
            , "CREATED_BY"
        );

        $SELECTION_B = array(
            "NAME AS EXPENSE_CATEGORY"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_expense'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_expense_category'), 'B.ID=A.EXPENSE_CATEGORY', $SELECTION_B);
        $SQL->where("A.STAFF = '" . $objectId . "'");
        $SQL->order('A.CREATED_DATE DESC');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->GUID;
                $data[$i]["TRANSACTION_NUMBER"] = displayNumberFormat($value->TRANSACTION_NUMBER);
                $data[$i]["AMOUNT"] = displayNumberFormat($value->AMOUNT) . " " . self::getCurrency();
                $data[$i]["PAYMENT_DATE"] = $value->START_DATE;
                $data[$i]["PAYMENT_DATE"] .= " -";
                $data[$i]["PAYMENT_DATE"] .= $value->END_DATE;

                switch ($value->PAYMENT_TYPE) {
                    case "PAYMENT_FOR_SALARY":
                        $data[$i]["TOTAL_SESSION"] = "---";
                        $data[$i]["TYPE"] = 1;
                        break;
                    case "PAYMENT_FOR_TEACHING_SESSION":
                        $data[$i]["TOTAL_SESSION"] = displayNumberFormat($value->TOTAL_SESSION);
                        $data[$i]["TYPE"] = 2;
                        break;
                }

                $data[$i]["PAYMENT_TYPE"] = defined($value->PAYMENT_TYPE) ? constant($value->PAYMENT_TYPE) : $value->PAYMENT_TYPE;
                $data[$i]["EXPENSE_CATEGORY"] = $value->EXPENSE_CATEGORY;
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;
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

    public static function searchStaffPayment($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $startDate = isset($params["startDate"]) ? addText($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? addText($params["endDate"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $paistatus = isset($params["paistatus"]) ? addText($params["paistatus"]) : "";
        $staffCode = isset($params["staffCode"]) ? addText($params["staffCode"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $number = isset($params["number"]) ? addText($params["number"]) : "";
        $paymentType = isset($params["paymentType"]) ? addText($params["paymentType"]) : "";

        $SELECTION_A = array(
            "ID AS STAFF_ID"
            , "STATUS"
            , "CODE"
            , "FIRSTNAME"
            , "LASTNAME"
            , "STAFF_SCHOOL_ID"
        );

        $SELECTION_B = array(
            "GUID AS GUID"
            , "TRANSACTION_NUMBER AS TRANSACTION_NUMBER"
            , "AMOUNT AS TRANSACTION_AMOUNT"
            , "PAYMENT_TYPE AS PAYMENT_TYPE"
            , "TOTAL_SESSION AS TOTAL_SESSION"
            , "START_DATE AS START_DATE"
            , "END_DATE AS END_DATE"
            , "CREATED_DATE AS CREATED_DATE"
            , "CREATED_BY AS CREATED_BY"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_staff'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_expense'), 'B.STAFF=A.ID', $SELECTION_B);

        $WHERE = "";
        if ($globalSearch) {

            $WHERE .= " ((A.NAME LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $WHERE .= " ) ";
        }

        if ($WHERE)
            $SQL->where($WHERE);

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '" . $lastname . "%'");

        if ($staffCode)
            $SQL->where("A.CODE LIKE '" . $staffCode . "%'");

        if ($number) {
            $ADD_WHERE = "";
            $ADD_WHERE .= " ((B.TRANSACTION_NUMBER LIKE 'SAL-" . $number . "%')";
            $ADD_WHERE .= " OR (B.TRANSACTION_NUMBER LIKE 'TEA-" . $number . "%')";
            $ADD_WHERE .= " ) ";
            $SQL->where($ADD_WHERE);
        }

        if ($paymentType)
            $SQL->where("B.PAYMENT_TYPE ='" . $paymentType . "'");

        if ($startDate && $endDate) {
            $SQL->where("B.START_DATE>='" . $startDate . "' AND B.END_DATE<='" . $endDate . "'");
        }

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STAFF_ID;
                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STAFF"] = "(" . $value->CODE . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STAFF"] = "(" . $value->CODE . ") " . setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                if ($value->TRANSACTION_NUMBER) {

                    $data[$i]["TRANSACTION_NUMBER"] = $value->TRANSACTION_NUMBER;
                    $data[$i]["SHOW"] = true;

                    switch ($value->PAYMENT_TYPE) {
                        case "PAYMENT_FOR_SALARY":
                            $data[$i]["SHOW_URL"] = "/finance/staffpaymentsalary/?objectId=" . $value->STAFF_ID . "&paymentId=" . $value->GUID;
                            break;
                        case "PAYMENT_FOR_TEACHING_SESSION":
                            $data[$i]["SHOW_URL"] = "/finance/staffpaymentsession/?objectId=" . $value->STAFF_ID . "&paymentId=" . $value->GUID;
                            break;
                    }

                    $data[$i]["PRINT_URL"] = "/finance/printfeestaff/?objectId=" . $value->STAFF_ID . "&paymentId=" . $value->GUID;

                    $data[$i]["TRANSACTION_AMOUNT"] = displayNumberFormat($value->TRANSACTION_AMOUNT) . " " . self::getCurrency();
                    $data[$i]["TYPE"] = constant($value->PAYMENT_TYPE);
                    $data[$i]["DATE"] = getShowDate($value->START_DATE) . "<BR>" . getShowDate($value->END_DATE);
                    $data[$i]["TOTAL_SESSION"] = displayNumberFormat($value->TOTAL_SESSION) ? $value->TOTAL_SESSION : "---";
                    $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                    $data[$i]["CREATED_DATE"] .= "<br>";
                    $data[$i]["CREATED_DATE"] .= $value->CREATED_BY;
                } else {

                    $data[$i]["SHOW"] = false;
                    $data[$i]["SHOW_URL"] = false;
                    $data[$i]["TRANSACTION_NUMBER"] = "?";
                    $data[$i]["PAYMENT"] = "new";
                    $data[$i]["TRANSACTION_AMOUNT"] = "?";
                    $data[$i]["TYPE"] = "?";
                    $data[$i]["DATE"] = "?";
                    $data[$i]["TOTAL_SESSION"] = "?";
                    $data[$i]["CREATED_DATE"] = "?";
                }

                ++$i;
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

    public static function checkActionPayment($staffId, $start, $stop, $type) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array("C" => "COUNT(*)"));
        $SQL->where("STAFF = '" . $staffId . "'");
        $SQL->where("('" . $start . "' BETWEEN START_DATE AND END_DATE) OR ('" . $stop . "' BETWEEN START_DATE AND END_DATE)");
        $SQL->where("PAYMENT_TYPE = '" . $type . "'");
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkPaymentForSalary($staffId, $startdate, $enddate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array("C" => "COUNT(*)"));
        $SQL->where("PAYMENT_TYPE = 'PAYMENT_FOR_SALARY'");
        $SQL->where("STAFF = '" . $staffId . "'");
        $SQL->where("('" . setDate2DB($startdate) . "' BETWEEN START_DATE AND END_DATE) OR ('" . setDate2DB($enddate) . "' BETWEEN START_DATE AND END_DATE)");
        $SQL->order('TRANSACTION_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkPaymentForTeachingSession($staffId, $startdate, $enddate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array("C" => "COUNT(*)"));
        $SQL->where("PAYMENT_TYPE = 'PAYMENT_FOR_TEACHING_SESSION'");
        $SQL->where("STAFF = '" . $staffId . "'");
        $SQL->where("('" . setDate2DB($startdate) . "' BETWEEN START_DATE AND END_DATE) OR ('" . setDate2DB($enddate) . "' BETWEEN START_DATE AND END_DATE)");
        $SQL->order('TRANSACTION_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getTNTeachingSession() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array("TRANSACTION_NUMBER"));
        $SQL->where("PAYMENT_TYPE = 'PAYMENT_FOR_TEACHING_SESSION'");
        $SQL->order('TRANSACTION_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        if ($result) {
            $cal = substr($result->TRANSACTION_NUMBER, 4) + 1;
            return "TEA-" . $cal;
        } else {
            return "TEA-100";
        }
    }

    public static function getTNberSalary() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_expense", array("TRANSACTION_NUMBER"));
        $SQL->where("PAYMENT_TYPE = 'PAYMENT_FOR_SALARY'");
        $SQL->where("TRANSACTION_NUMBER LIKE 'SAL-%'");
        $SQL->order('TRANSACTION_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        if ($result) {
            $cal = substr($result->TRANSACTION_NUMBER, 4) + 1;
            return "SAL-" . $cal;
        } else {
            return "SAL-100";
        }
    }

}

?>