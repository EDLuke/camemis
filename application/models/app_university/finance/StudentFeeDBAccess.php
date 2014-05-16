<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 6.08.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentSearchDBAccess.php';
require_once 'models/app_university/finance/ExpenseDBAccess.php';
require_once 'models/app_university/finance/FeeDBAccess.php';
require_once 'models/app_university/training/TrainingDBAccess.php';
require_once 'models/app_university/ScholarshipDBAccess.php'; //@veasna 
require_once 'models/app_university/finance/FeeOptionDBAccess.php'; //@veasna 
require_once 'models/app_university/finance/IncomeCategoryDBAccess.php'; //@veasna 
require_once setUserLoacalization();

class StudentFeeDBAccess extends FeeDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function jsonLoadStudentPayment($studentId, $feeId, $paymentId) {

        if ($paymentId) {
            $facette = self::getStudentPaymentFromId($paymentId);
        } else {
            $facette = self::getLastStudentPayment($studentId, $feeId);
        }

        if ($facette) {
            $data = array();

            $data["AMOUNT"] = displayNumberFormat($facette->AMOUNT);

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

    public static function deleteStudentFromFee($student, $feeId) {
        $WHERE = array();
        $WHERE[] = "FEE = '" . $feeId . "'";
        $WHERE[] = "STUDENT = '" . $student . "'";
        self::dbAccess()->delete('t_student_fee', $WHERE);
    }

    public static function checkStudentInFee($studentId, $feeId) {

        $facette = self::getStudentFee($studentId, $feeId);
        return $facette ? true : false;
    }

    public static function getStudentFee($studentId, $feeId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_fee", array('*'));
        $SQL->where("STUDENT = '" . $studentId . "'");
        $SQL->where("FEE = '" . $feeId . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getStudentPaymentFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array('*'));
        $SQL->where("GUID = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getLastStudentPayment($studentId, $feeId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array('*'));
        $SQL->where("STUDENT = '" . $studentId . "'");
        $SQL->where("FEE = '" . $feeId . "'");
        $SQL->order('CREATED_DATE DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getStudentOpenBalance($studentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_fee", array("C" => "SUM(AMOUNT_OWED)"));
        $SQL->where("STUDENT = '" . $studentId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentTotalAmount($studentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_fee", array("C" => "SUM(AMOUNT_PAID)"));
        $SQL->where("STUDENT = '" . $studentId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getStudentAmountPaidByTraining($studentId, $trainingId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array("*"));
        $SQL->where("STUDENT = '" . $studentId . "'");
        $SQL->where("TRAINING = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //bezahlte Summen....
    public static function getStudentAmountByFee($studentId, $feeId, $trainingId, $paymentRemove = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array("*"));
        $SQL->where("STUDENT = '" . $studentId . "'");

        if ($feeId)
            $SQL->where("FEE = '" . $feeId . "'");
        if ($trainingId)
            $SQL->where("TRAINING = '" . $trainingId . "'");

        if ($paymentRemove){
            $SQL->where("PAYMENT_REMOVE = '1'");
        }else{
            $SQL->where("PAYMENT_REMOVE = '0'");    
        }

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function getStudentAmountPaidByFee($studentId, $feeId, $trainingId, $paymentRemove = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array("SUM" => "SUM(AMOUNT)"));
        $SQL->where("STUDENT = '" . $studentId . "'");

        if ($feeId)
            $SQL->where("FEE = '" . $feeId . "'");
        if ($trainingId)
            $SQL->where("TRAINING = '" . $trainingId . "'");

        if ($paymentRemove)
            $SQL->where("PAYMENT_REMOVE = '1'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->SUM : 0;
    }

    public static function checkStudentFeePayment($studentId, $feeId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT = '" . $studentId . "'");
        $SQL->where("FEE = '" . $feeId . "'");
        $SQL->where("PAYMENT_REMOVE = '0'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function setStudentFeePayment($params) {

        $RADIOBOX_DATA = array();
        $CHECKBOX_DATA = array();

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $chooseOption = isset($params["CHOOSE_OPTION"]) ? $params["CHOOSE_OPTION"] : "";
        $feeId = isset($params["feeId"]) ? $params["feeId"] : "";

        //@veasna
        $taxFormular = isset($params["taxFormular"]) ? $params["taxFormular"] : "";
        $taxAmount = isset($params["HIDDEN_TAX_AMOUNT"]) ? $params["HIDDEN_TAX_AMOUNT"] : "";
        //
        $amount = isset($params["HIDDEN_FEE_AMOUNT"]) ? str2no($params["HIDDEN_FEE_AMOUNT"]) : "";
        $income_amount = isset($params["HIDDEN_TOTAL_PAY"]) ? $params["HIDDEN_TOTAL_PAY"] : "";
        $scholarshipPercent = isset($params["SCHOLARSHIP_PERCENT"]) ? $params["SCHOLARSHIP_PERCENT"] : "0";
        $scholarshipAmount = isset($params["HIDDEN_SCHOLARSHIP_AMOUNT"]) ? $params["HIDDEN_SCHOLARSHIP_AMOUNT"] : "0";
        $discount_amount = isset($params["HIDDEN_DISCOUNT_AMOUNT"]) ? $params["HIDDEN_DISCOUNT_AMOUNT"] : "0";

        /////scholarship
        $SAVEDATA["AMOUNT"] = addText($amount);
        $SAVEDATA["INCOME_AMOUNT"] = addText($income_amount);
        /////

        if (!$trainingId) {
            $studentAcademicObject = StudentAcademicDBAccess::getCurrentStudentAcademic($studentId);
            $SAVEDATA["GRADE"] = $studentAcademicObject->GRADE_ID;
        } else {
            $STUDENT_TRAINING = self::getStudentFeeTraining($studentId, $feeId);
            $SAVEDATA["TRAINING"] = $STUDENT_TRAINING->TRAINING_ID;
            //$isScholarship = $STUDENT_TRAINING ? $STUDENT_TRAINING->SCHOLARSHIP : false;
        }

        $feeObject = self::findFeeFromId($feeId);
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
        //$SAVEDATA["AMOUNT"] = $amount;

        if ($feeObject)
            $SAVEDATA["INCOME_CATEGORY"] = $feeObject->INCOME_CATEGORY;

        $CHECK = self::checkStudentFeePayment($studentId, $feeId);

        $SAVEDATA["SCHOLARSHIP_AMOUNT"] = $scholarshipAmount;
        $SAVEDATA["SCHOLARSHIP_PERCENT"] = $scholarshipPercent;
        $SAVEDATA["GUID"] = generateGuid();
        $SAVEDATA["TRANSACTION_NUMBER"] = self::getTNIncome();
        $SAVEDATA["STUDENT"] = $studentId;
        $SAVEDATA["FEE"] = $feeId;
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        //@veasna
        $SAVEDATA['AMOUNT_TAX'] = $taxAmount;
        $SAVEDATA['FORMULAR_TAX'] = $taxFormular;
        $SAVEDATA['DISCOUNT'] = $discount_amount;
        $SAVEDATA['DISCOUNT_PERCENT'] = isset($params["DISCOUNT"]) ? $params["DISCOUNT"] : "0";
        //error_log($SAVEDATA['AMOUNT_TAX']);

        if ($studentId) {
            $studentObject = StudentDBAccess::findStudentFromId($studentId);
            $SAVEDATA["NAME"] = $studentObject->LASTNAME . " " . $studentObject->FIRSTNAME . " (" . $studentObject->CODE . ")";
        }

        if (!$CHECK) {
            if ($amount > 0) {

                self::dbAccess()->insert('t_income', $SAVEDATA);
            }
        } else {
            if ($CHECK < $chooseOption) {
                if ($amount > 0) {
                    self::dbAccess()->insert('t_income', $SAVEDATA);
                }
            }
        }

        self::setUpdateStudentFee($studentId, $feeId, $income_amount, $scholarshipAmount, $scholarshipPercent, $taxFormular, $taxAmount, $discount_amount);
    }

    ///////////////////////////////////////////////////
    //Wichtig....
    ///////////////////////////////////////////////////
    public static function setUpdateStudentFee($studentId, $feeId, $paidAmount, $scholarshipAmount, $scholarshipPercent, $taxFormular, $taxAmount) {

        $FEE_OBJECT = self::findFeeFromId($feeId);
        $STUDENT_FEE = self::getStudentFee($studentId, $feeId);

        $AMOUNT_PAID = $paidAmount;

        if ($STUDENT_FEE && $FEE_OBJECT) {

            $objectAmount = self::getStudentAmountByFee($studentId, $feeId, false); ///check
            $AMOUNT_PAID = 0;
            $AMOUNT_TAX = 0;
            $AMOUNT_DISCOUNT = 0;
            foreach ($objectAmount as $values) {
                $AMOUNT_PAID +=$values->INCOME_AMOUNT;
                $AMOUNT_TAX +=$values->AMOUNT_TAX;
                $AMOUNT_DISCOUNT +=$values->DISCOUNT;
            }

            if ($scholarshipPercent) {

                if ($scholarshipPercent == 100) {
                    $AMOUNT_OWED = 0;
                    $AMOUNT_PAID = 0;
                } else {
                    // $feeOptionObject=FeeOptionDBAccess::getFristFeeOptionByFeeId($STUDENT_FEE->ID); //@veasna
                    if ($taxFormular == 1) {
                        $AMOUNT_OWED = $STUDENT_FEE->TOTAL_AMOUNT - (($scholarshipAmount + $paidAmount) - $taxAmount);
                    } else {
                        $AMOUNT_OWED = $STUDENT_FEE->TOTAL_AMOUNT - ($scholarshipAmount + $paidAmount);
                    }
                }
            } else {
                if ($taxFormular == 1) {
                    //error_log($AMOUNT_PAID.','.$AMOUNT_TAX.','.$AMOUNT_DISCOUNT.','.$STUDENT_FEE->TOTAL_AMOUNT);
                    $AMOUNT_OWED = $STUDENT_FEE->TOTAL_AMOUNT - (($AMOUNT_PAID + $AMOUNT_DISCOUNT) - $AMOUNT_TAX);
                } else {
                    $AMOUNT_OWED = $STUDENT_FEE->TOTAL_AMOUNT - ($AMOUNT_PAID + $AMOUNT_DISCOUNT);
                }
            }
        }

        //error_log("Bezahlt: ".$paidAmount." Wegen Scholarship:".$scholarshipAmount. " Gesamt: ".$FEE_OBJECT->FIRST_TOTAL. " Rest: ".$AMOUNT_OWED);

        $SAVEDATA['ACTION_PAYMENT'] = 1;
        $SAVEDATA['SCHOLARSHIP_AMOUNT'] = $scholarshipAmount;
        $SAVEDATA['SCHOLARSHIP_PERCENT'] = $scholarshipPercent;
        $SAVEDATA['AMOUNT_PAID'] = $AMOUNT_PAID;
        $SAVEDATA['AMOUNT_OWED'] = $AMOUNT_OWED;
        $WHERE[] = "FEE = '" . $feeId . "'";
        $WHERE[] = "STUDENT = '" . $studentId . "'";
        self::dbAccess()->update('t_student_fee', $SAVEDATA, $WHERE);

        ////////////////////////////////////////////////////////////////////////
        self::changePaymentStatus($studentId, $feeId);
        ////////////////////////////////////////////////////////////////////////
    }

    public static function setStudentPaymentOption($params) {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $feeId = isset($params["feeId"]) ? $params["feeId"] : "";
        $chooseOption = isset($params["CHOOSE_OPTION"]) ? $params["CHOOSE_OPTION"] : "";

        $WHERE = array();

        if ($chooseOption) {
            $facette = FeeOptionDBAccess::getFeeOptionById($chooseOption);
            $SAVEDATA['TOTAL_AMOUNT'] = $facette->TOTAL;
            $SAVEDATA['CHOOSE_OPTION'] = $chooseOption;
        } else {
            $facette = self::findFeeFromId($feeId);

            $SAVEDATA['TOTAL_AMOUNT'] = $facette->FIRST_TOTAL;
        }

        $WHERE[] = "FEE = '" . $feeId . "'";
        $WHERE[] = "STUDENT = '" . $studentId . "'";
        self::dbAccess()->update('t_student_fee', $SAVEDATA, $WHERE);
    }

    public static function jsonActionStudentPayment($params) {

        $payment_type = isset($params['PAYMENT']) ? $params['PAYMENT'] : '';
        $objectId = isset($params['objectId']) ? addText($params['objectId']) : '';
        $isTraing = isset($params['training']) ? $params['training'] : false;
        if ($payment_type) {
            switch ($payment_type) {
                case "NORMAL":
                    self::setStudentPaymentOption($params);
                    self::setStudentFeePayment($params);
                    break;
                case "PRE_PAYMENT":
                    $objectSave = StudentFeePrepaidDBAccess::jsonSaveStudentFeePrePayment($objectId, $isTraing);
                    $objectId = $objectSave['objectId'];
                    break;
                default:
                    self::setStudentPaymentOption($params);
                    self::setStudentFeePayment($params);
                    break;
            }
        } else {
            self::setStudentPaymentOption($params);
            self::setStudentFeePayment($params);
        }

        return array(
            "success" => true
            , "status" => isset($params["HIDDEN_TOTAL_PAY"]) ? true : false
            , "payment_type" => $payment_type
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    //List....
    public static function jsonListStudentInvoicesGeneral($params) {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $services = isset($params["services"]) ? $params["services"] : "0";

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_fee'), array("FEE", "CHOOSE_OPTION", "AMOUNT_PAID", "AMOUNT_OWED", "SCHOLARSHIP_AMOUNT", "SCHOLARSHIP_INDEX"));
        $SQL->joinLeft(array('B' => 't_fee'), 'B.ID=A.FEE', array("NAME AS FEE_NAME"));
        $SQL->where("B.TYPE = 'GENERAL_EDU'");
        $SQL->where("A.STUDENT = '" . $studentId . "'");

        if ($services) {
            $SQL->where("B.STUDENT_SERVICES = '1'");
            $SQL->where("A.CHOOSE_SERVICE = '2'");
        } else {
            $SQL->where("B.STUDENT_SERVICES = '0'");
            $SQL->where("A.CHOOSE_SERVICE = '1'");
        }

        $SQL->order('B.CREATED_DATE DESC');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $facette = self::findFeeFromId($value->FEE);

                if ($facette) {

                    $data[$i]["ID"] = $value->FEE;

                    $data[$i]["INVOICE_NUMBER"] = $value->FEE_NAME . " (" . setShowText($facette->CODE) . ")";

                    switch ($value->CHOOSE_OPTION) {
                        case 1:$data[$i]["CHOOSE_OPTION"] = FIRST_OPTION;
                            break;
                        case 2:$data[$i]["CHOOSE_OPTION"] = SECOND_OPTION;
                            break;
                        case 3:$data[$i]["CHOOSE_OPTION"] = THIRD_OPTION;
                            break;
                        case 4:$data[$i]["CHOOSE_OPTION"] = FOURTH_OPTION;
                            break;
                        default:$data[$i]["CHOOSE_OPTION"] = FIRST_OPTION;
                            break;
                    }

                    $data[$i]["CLICK_ADD"] = $value->AMOUNT_OWED ? true : false;
                    $data[$i]["AMOUNT_OWED"] = displayNumberFormat($value->AMOUNT_OWED) . " " . self::getCurrency();
                    $data[$i]["AMOUNT"] = displayNumberFormat($value->AMOUNT_PAID) . " " . self::getCurrency();

                    if ($value->SCHOLARSHIP_AMOUNT) {
                        $data[$i]["SCHOLARSHIP"] = displayNumberFormat($value->SCHOLARSHIP_AMOUNT) . " " . self::getCurrency() . " (" . $value->SCHOLARSHIP_INDEX . "%)";
                    } else {
                        $data[$i]["SCHOLARSHIP"] = "---";
                    }

                    $i++;
                }
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

    public static function getSQLStudentPayments($studentId, $trainingId, $feeId, $paymentRemove) {

        $SELECTION_A = array(
            "GUID AS GUID"
            , "FEE AS FEE_ID"
            , "TRAINING AS TRAINING_ID"
            , "AMOUNT AS AMOUNT"
            , "INCOME_AMOUNT AS INCOME_AMOUNT"
            , "INCOME_CATEGORY AS INCOME_CATEGORY"
            , "TRANSACTION_NUMBER AS TRANSACTION_NUMBER"
            , "DISCOUNT_PERCENT AS DISCOUNT_PERCENT"
            , "DISCOUNT AS DISCOUNT"
            , "AMOUNT_TAX AS AMOUNT_TAX"
            , "SCHOLARSHIP_PERCENT AS SCHOLARSHIP_PERCENT"
            , "SCHOLARSHIP_AMOUNT AS SCHOLARSHIP_AMOUNT"
            , "CREATED_DATE AS CREATED_DATE"
            , "CREATED_BY AS CREATED_BY"
        );

        $SELECTION_B = array(
            "NAME AS FEE_NAME"
        );

        $SELECTION_C = array(
            "NAME AS TRAINING_NAME"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_income'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_fee'), 'B.ID=A.FEE', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.TRAINING', $SELECTION_C);
        if ($studentId)
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        if ($feeId)
            $SQL->where("A.FEE = '" . $feeId . "'");
        if ($trainingId)
            $SQL->where("A.TRAINING = '" . $trainingId . "'");
        if ($paymentRemove) {
            $SQL->where("A.PAYMENT_REMOVE = '1'");
        } else {
            $SQL->where("A.PAYMENT_REMOVE = '0'");
        }
        $SQL->order('A.CREATED_DATE ASC');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonListStudentPayments($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $feeId = isset($params["feeId"]) ? $params["feeId"] : "";

        $result = self::getSQLStudentPayments($studentId, $trainingId, $feeId, false);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->GUID;

                if ($value->FEE_ID) {
                    $TOTAL = displayNumberFormat(self::getStudentAmountPaidByFee($studentId, $value->FEE_ID, false)) . " " . self::getCurrency();
                    $data[$i]["FEE_NAME"] = "<b>" . $value->FEE_NAME . " (" . $TOTAL . ")</b>";
                    $data[$i]["ACADEMIC"] = HIGHER_EDUCATION;
                }

                if ($value->TRAINING_ID) {
                    $TOTAL = displayNumberFormat(self::getStudentAmountPaidByFee($studentId, false, $value->TRAINING_ID)) . " " . self::getCurrency();
                    $data[$i]["FEE_NAME"] = "<b>" . $value->TRAINING_NAME . " (" . $TOTAL . ")</b>";
                    $data[$i]["ACADEMIC"] = HIGHER_EDUCATION;
                }

                $data[$i]["TRANSACTION_NUMBER"] = displayNumberFormat($value->TRANSACTION_NUMBER);
                $data[$i]["AMOUNT"] = displayNumberFormat($value->AMOUNT) . " " . self::getCurrency();
                $data[$i]["INCOME_AMOUNT"]= displayNumberFormat($value->INCOME_AMOUNT) . " " . self::getCurrency();
                
                $scholarship='';
                if($value->SCHOLARSHIP_PERCENT){
                $scholarship=", ".SCHOLARSHIP." ".displayNumberFormat($value->SCHOLARSHIP_PERCENT)."%: ".displayNumberFormat($value->SCHOLARSHIP_AMOUNT). " " . self::getCurrency();    
                }
                $taxPercent=IncomeCategoryDBAccess::findTaxByCategory($value->INCOME_CATEGORY);
                $data[$i]["STATUS"]= TOTAL.": ".displayNumberFormat($value->INCOME_AMOUNT) . " " . self::getCurrency()."<BR/>".AMOUNT.": ".displayNumberFormat($value->AMOUNT) . " " . self::getCurrency()
                                    ." (".DISCOUNT." ".displayNumberFormat($value->DISCOUNT_PERCENT)."%: ".displayNumberFormat($value->DISCOUNT). " " . self::getCurrency().", "
                                    .TAX." ".$taxPercent."%: ".displayNumberFormat($value->AMOUNT_TAX). " " . self::getCurrency().$scholarship.")";
                $data[$i]["CREATED_DATE"] = $value->CREATED_DATE;
                $data[$i]["CREATED_DATE"] .= '<br>';
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

    public static function jsonTreeFeesByStudent($params) {

        $node = $params["node"];
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbSelectAccess();
        $SQL->distinct();

        if (!$node) {

            $SQL->from(array('A' => 't_grade'), array());
            $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.SCHOOL_YEAR=B.SCHOOL_YEAR', array("SCHOOL_YEAR AS SCHOOLYEAR_ID", "GRADE AS GRADE_ID"));
            $SQL->joinLeft(array('C' => 't_grade'), 'B.GRADE=C.ID', array("NAME AS GRADE_NAME"));
            $SQL->where("B.STUDENT = '" . $studentId . "'");
            //error_log($SQL->__toString());
        } else {

            $explode = explode("_", $node);

            $gradeId = $explode[0];
            $schoolyearId = $explode[1];

            $SQL->from(array('A' => 't_fee'), array("ID AS FEE_ID", "NAME AS FEE_NAME"));
            $SQL->joinLeft(array('B' => 't_fee_general'), 'A.ID=B.FEE', array());
            $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'B.SCHOOLYEAR=C.SCHOOL_YEAR', array());
            $SQL->where("C.STUDENT = '" . $studentId . "'");
            $SQL->where("B.GRADE = '" . $gradeId . "'");
            $SQL->where("B.SCHOOLYEAR = '" . $schoolyearId . "'");
            //error_log($SQL->__toString());
        }

        $resultRows = self::dbAccess()->fetchAll($SQL);
        $data = array();
        if ($resultRows) {

            $i = 0;
            foreach ($resultRows as $value) {

                if (!$node) {
                    $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($value->SCHOOLYEAR_ID);
                    $data[$i]['id'] = $value->GRADE_ID . "_" . $value->SCHOOLYEAR_ID;
                    $data[$i]['text'] = $value->GRADE_NAME . " (" . $schoolyearObject->NAME . ")";
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                } else {
                    $data[$i]['id'] = "" . $value->FEE_ID . "";
                    $data[$i]['text'] = $value->FEE_NAME;
                    $data[$i]['iconCls'] = "icon-plugin";
                    $data[$i]['cls'] = "nodeTextBlue";
                    $data[$i]['leaf'] = true;
                }

                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////
    //TRAINING....
    ////////////////////////////////////////////////////////////////////////////
    public static function checkPaidFeeTraining($studentId, $trainingId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT = '" . $studentId . "'");
        $SQL->where("TRAINING = '" . $trainingId . "'");
        $SQL->where("PAID = '1'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    //Add Student Training Payment...
    public static function jsonActionStudentPaymentTraining($params) {

        $SAVE_INCOME_DATA = array();
        $SAVE_TRAINING_DATA = array();

        $RADIOBOX_DATA = array();
        $CHECKBOX_DATA = array();

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $amount = isset($params["PAYMENT_AMOUNT"]) ? str2no($params["PAYMENT_AMOUNT"]) : "";

        $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);
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

        if ($studentId) {
            $studentObject = StudentDBAccess::findStudentFromId($studentId);
            $SAVE_INCOME_DATA["NAME"] = $studentObject->LASTNAME . " " . $studentObject->FIRSTNAME . " (" . $studentObject->CODE . ")";
        }

        $CHECK = self::getStudentAmountPaidByTraining($studentId, $trainingId);

        if (!$CHECK) {

            if ($amount) {
                $SAVE_INCOME_DATA["FINANCE_DESCRIPTION"] = implode(",", $CHOOSE_DATA);
                $SAVE_INCOME_DATA["AMOUNT"] = $amount;

                if ($trainingObject)
                    $SAVE_INCOME_DATA["INCOME_CATEGORY"] = $trainingObject->BALANCE_CATEGORY;

                $SAVE_INCOME_DATA["GUID"] = generateGuid();
                $SAVE_INCOME_DATA["TRANSACTION_NUMBER"] = self::getTNIncome("TRA");
                $SAVE_INCOME_DATA["STUDENT"] = $studentId;
                $SAVE_INCOME_DATA["TRAINING"] = $trainingId;
                $SAVE_INCOME_DATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVE_INCOME_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                if ($amount > 0)
                    self::dbAccess()->insert('t_income', $SAVE_INCOME_DATA);

                $WHERE = array();
                $SAVE_TRAINING_DATA["AMOUNT_PAID"] = $amount;
                $SAVE_TRAINING_DATA["AMOUNT_OWED"] = "0";
                $SAVE_TRAINING_DATA["PAID"] = "1";
                $SAVE_TRAINING_DATA['PAYMENT_DATE'] = getCurrentDBDateTime();
                $SAVE_TRAINING_DATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVE_TRAINING_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                $WHERE[] = "STUDENT = '" . $studentId . "'";
                $WHERE[] = "TRAINING = '" . $trainingId . "'";
                self::dbAccess()->update('t_student_training', $SAVE_TRAINING_DATA, $WHERE);
            }
        }

        return array(
            "success" => true
        );
    }

    public static function jsonListStudentInvoicesTraining($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $services = isset($params["services"]) ? $params["services"] : "";

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_fee'), array("FEE", "CHOOSE_OPTION", "AMOUNT_PAID", "AMOUNT_OWED", "SCHOLARSHIP_AMOUNT", "SCHOLARSHIP_INDEX"));
        $SQL->joinLeft(array('B' => 't_fee'), 'B.ID=A.FEE', array("NAME AS FEE_NAME"));
        $SQL->joinLeft(array('C' => 't_fee_training'), 'B.ID=A.FEE', array("ID AS TRAINING_ID"));
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=C.TRAINING', array("START_DATE", "END_DATE"));
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=D.PROGRAM', array("NAME AS PROGRAM"));
        $SQL->joinLeft(array('F' => 't_training'), 'F.ID=D.LEVEL', array("NAME AS LEVEL"));
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        $SQL->where("B.TYPE = 'COURSE'");

        if ($services) {
            $SQL->where("B.STUDENT_SERVICES = '1'");
        } else {
            $SQL->where("B.STUDENT_SERVICES = '0'");
        }

        $SQL->group("A.ID");
        $SQL->order('B.CREATED_DATE DESC');

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->FEE;
                $data[$i]["INVOICE_NUMBER"] = $value->FEE_NAME;
                $data[$i]["PROGRAM"] = $value->PROGRAM;
                $data[$i]["LEVEL"] = $value->LEVEL;

                $data[$i]["TERM"] = getShowDate($value->START_DATE);
                $data[$i]["TERM"] .= " - ";
                $data[$i]["TERM"] .= getShowDate($value->END_DATE);
                $data[$i]["CLICK_ADD"] = 1;

                if ($value->AMOUNT_PAID) {
                    $data[$i]["AMOUNT_OWED"] = "0 " . self::getCurrency();
                    $data[$i]["AMOUNT"] = displayNumberFormat($value->AMOUNT_PAID) . " " . self::getCurrency();
                    $data[$i]["AMOUNT_PAID"] = displayNumberFormat($value->AMOUNT_PAID - $value->SCHOLARSHIP_AMOUNT) . " " . self::getCurrency();
                    $data[$i]["SCHOLARSHIP_AMOUNT"] = displayNumberFormat($value->SCHOLARSHIP_AMOUNT) . " " . self::getCurrency();
                } else {
                    $data[$i]["PAYMENT_DATE"] = "---";
                    $data[$i]["AMOUNT_OWED"] = displayNumberFormat($value->AMOUNT_OWED) . " " . self::getCurrency();
                    $data[$i]["AMOUNT"] = "0 " . self::getCurrency();
                    $data[$i]["AMOUNT_PAID"] = "---";
                    $data[$i]["SCHOLARSHIP_AMOUNT"] = "---";
                }

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

    public static function getTotalSumFeeByStudent($studentId, $schoolyearId, $servives, $training) {

        $SELECTION_A = array(
            "SUM(A.AMOUNT_OWED) AS TOTAL_AMOUNT_OWED"
            , "SUM(A.AMOUNT_PAID) AS TOTAL_AMOUNT_PAID"
        );

        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_fee'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_fee'), 'B.ID=A.FEE', array());
        $SQL->where("A.STUDENT = '" . $studentId . "'");

        if ($servives) {
            $SQL->where("A.CHOOSE_SERVICE = '2'");
            $SQL->where("B.STUDENT_SERVICES = '2'");
        } else {
            $SQL->where("A.CHOOSE_SERVICE = '0'");
            $SQL->where("B.STUDENT_SERVICES = '0'");
        }

        if ($schoolyearId)
            $SQL->where("B.SCHOOLYEAR  = '" . $schoolyearId . "'");

        if ($training)
            $SQL->where("A.TRAINING <>'0'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getSQLSearchCasshStudent($params) {

        $studentCode = isset($params["studentCode"]) ? $params["studentCode"] : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $feeCode = isset($params["feeCode"]) ? $params["feeCode"] : "";
        $feeName = isset($params["feeName"]) ? $params["feeName"] : "";
        $paymentStatus = isset($params["paymentStatus"]) ? $params["paymentStatus"] : "";
        $searchService = isset($params["searchService"]) ? $params["searchService"] : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $SELECTION_A = array(
            "CHOOSE_SERVICE"
            , "TOTAL_AMOUNT"
            , "AMOUNT_PAID"
            , "AMOUNT_OWED"
            , "PAID_STATUS"
            , "CHOOSE_OPTION"
            , "SCHOLARSHIP"
            , "SCHOLARSHIP_AMOUNT"
            , "SCHOLARSHIP_INDEX"
            , "GRADE"
            , "SCHOOLYEAR AS SCHOOLYEAR"
            , "TRAINING"
            , "ACTION_PAYMENT"
        );

        $SELECTION_B = array(
            "CODE AS FEE_CODE"
            , "ID AS FEE_ID"
            , "NAME AS FEE_NAME"
            , "TYPE AS FEE_TYPE"
            , "AMOUNT_OPTION AS AMOUNT_OPTION"
            , "FIRST_TOTAL AS TRANSACTION_AMOUNT"
            , "STUDENT_SERVICES AS STUDENT_SERVICES"
        );

        $SELECTION_C = array(
            "ID AS STUDENT_ID"
            , "CODE AS STUDENT_CODE"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "GENDER AS GENDER"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_fee'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_fee'), 'B.ID=A.FEE', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_student'), 'C.ID=A.STUDENT', $SELECTION_C);

        $SQL->where("B.STATUS ='1'");

        if ($firstname)
            $SQL->where("C.FIRSTNAME LIKE '" . $firstname . "%'");

        if ($lastname)
            $SQL->where("C.LASTNAME LIKE '" . $lastname . "%'");

        if ($studentCode)
            $SQL->where("C.CODE LIKE '" . $studentCode . "%'");

        if ($feeCode)
            $SQL->where("B.CODE LIKE 'FEE-" . $feeCode . "%'");

        if ($feeName)
            $SQL->where("B.NAME LIKE '" . $feeName . "%'");

        if ($paymentStatus)
            $SQL->where("A.PAID_STATUS = '" . $paymentStatus . "'");

        if ($studentId && $searchService) {
            $SQL->where("B.STUDENT_SERVICES='1'");
            $SQL->where("A.STUDENT ='" . $studentId . "'");
        } else {
            $SQL->where("A.CHOOSE_SERVICE IN (1,2)");
        }

        if ($studentId && $gradeId && $schoolyearId) {
            $SQL->where("A.STUDENT ='" . $studentId . "'");
            $SQL->where("A.GRADE ='" . $gradeId . "'");
            $SQL->where("A.SCHOOLYEAR ='" . $schoolyearId . "'");
        }

        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());

        foreach ($result as $value) {

            if (!$value->ACTION_PAYMENT) {
                self::dbAccess()->query("DELETE FROM t_student_fee WHERE STUDENT='" . $value->STUDENT_ID . "' AND FEE='" . $value->FEE_ID . "'");
                self::deleteStudentFromFeeANDIncome(
                        $value->STUDENT_ID
                        , $value->FEE_ID
                        , false
                        , true
                        , true
                );
            }

            self::addStudent2Fee(
                    $value->STUDENT_ID
                    , $value->FEE_ID
                    , $value->GRADE
                    , $value->SCHOOLYEAR
                    , $value->TRAINING
                    , $value->CHOOSE_SERVICE
            );
        }

        return $result;
    }

    //
    public static function getSQLCheckCasshStudent($params) {
        $studentCode = isset($params["studentCode"]) ? $params["studentCode"] : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $feeCode = isset($params["feeCode"]) ? $params["feeCode"] : "";
        $feeName = isset($params["feeName"]) ? $params["feeName"] : "";
        $paymentStatus = isset($params["paymentStatus"]) ? $params["paymentStatus"] : "";
        $scholarship = isset($params["scholarship"]) ? $params["scholarship"] : "";
        $searchService = isset($params["searchService"]) ? $params["searchService"] : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $result = self::getSQLSearchCasshStudent($params);
        $data = array();
        //@veasna new check for serach prepay
        if ($paymentStatus) {
            $params["paymentStatus"] = '';
            $records = self::getSQLSearchCasshStudent($params);
            switch ($paymentStatus) {
                case "PAID":
                    foreach ($records as $find) {
                        $CHECK = StudentFeePrepaidDBAccess::checkStudentPrePayPaid($find->STUDENT_ID);
                        if ($CHECK['CHECK']) {
                            $result[] = $find;
                        }
                    }
                    break;
                case "NOT_YET_PAID":
                    $TMP_DATA = array();
                    foreach ($result as $find) {
                        $CHECK = StudentFeePrepaidDBAccess::checkStudentPrePayPaid($find->STUDENT_ID);
                        if (!$CHECK['CHECK']) {
                            $TMP_DATA[] = $find;
                        }
                    }
                    $result = $TMP_DATA;
                    break;
                case "PRE_PAY":
                    foreach ($records as $find) {
                        $CHECK = StudentFeePrepaidDBAccess::checkStudentPrePayPaid($find->STUDENT_ID);
                        if ($CHECK['PRE_PAY']) {
                            $result[] = $find;
                        }
                    }
                    break;
            }
        }
        //

        if ($scholarship) {
            foreach ($result as $values) {
                $facette = ScholarshipDBAccess::getStudentScholarship($values->STUDENT_ID, $scholarship, false);
                if ($facette) {
                    $values->SCHOLARSHIP = $facette->PARENT_NAME . "(" . $facette->NAME . ")";
                    $data[] = $values;
                }
            }
        } else {

            foreach ($result as $values) {

                $facette = ScholarshipDBAccess::getStudentScholarship($values->STUDENT_ID, false, $values->SCHOOLYEAR);
                if ($facette) {
                    $values->SCHOLARSHIP = $facette->PARENT_NAME . " (" . $facette->NAME . ")";
                    $data[] = $values;
                } else {
                    $values->SCHOLARSHIP = '---';
                    $data[] = $values;
                }
            }
        }


        return $data;
    }

    //
    //new
    public static function findPaidAmounGeneral($studentId, $feeId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_income', array('*'));
        $SQL->where("STUDENT ='" . $studentId . "'");
        $SQL->where("FEE ='" . $feeId . "'");
        $SQL->where("PAYMENT_REMOVE ='0'");
        //error_log($SQL->__toString());    
        $result = self::dbAccess()->fetchAll($SQL);
        $total = 0;
        if ($result) {
            foreach ($result as $values) {
                $total+=$values->INCOME_AMOUNT;
            }
        }

        return $total;
    }

    //

    public static function searchCashStudent($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        //$result = self::getSQLSearchCasshStudent($params);
        $result = self::getSQLCheckCasshStudent($params);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                if ($value->FEE_TYPE == 'COURSE') {
                    $training = true;
                } else {
                    $training = false;
                }
                $studentPrePay = StudentFeePrepaidDBAccess::findStudentPrePayment($value->STUDENT_ID, $training);

                if ($studentPrePay && !$value->STUDENT_SERVICES) {

                    $data[$i]["STUDENT_SERVICES"] = $value->STUDENT_SERVICES;
                    $data[$i]["SCHOLARSHIP"] = "---";
                    //veasna
                    
                    $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                    $data[$i]["STUDENT_CODE"] = $value->STUDENT_CODE;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["FEE_CODE"] = $value->FEE_CODE;
                    //
                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["FEE_ID"] = $value->FEE_ID;
                    $data[$i]["FEE_NAME"] = "(" . $value->FEE_CODE . ") " . setShowText($value->FEE_NAME) . " (PRE PAID)";
                    $data[$i]["FEE_PRE_PAY"] = "1";
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = "(" . $value->STUDENT_CODE . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = "(" . $value->STUDENT_CODE . ") " . setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["TRANSACTION_AMOUNT"] = $value->TOTAL_AMOUNT ? displayNumberFormat($value->TOTAL_AMOUNT) . " " . self::getCurrency() : "0 " . self::getCurrency();

                    $CHECK = StudentFeePrepaidDBAccess::checkIncomePrePayment($value->STUDENT_ID, $studentPrePay->FEE_PREPAYMENT_ID);
                    $data[$i]["AMOUNT_PAID"] = $CHECK ? displayNumberFormat($CHECK->INCOME_AMOUNT) . " " . self::getCurrency() : "0 " . self::getCurrency();

                    $data[$i]["AMOUNT_OWED"] = $CHECK ? "0 " . self::getCurrency() : displayNumberFormat($value->TOTAL_AMOUNT) . " " . self::getCurrency();

                    switch ($value->FEE_TYPE) {
                        case "GENERAL_EDU":
                            $data[$i]["FEE_TYPE"] = HIGHER_EDUCATION;

                            if ($value->STUDENT_SERVICES) {
                                $data[$i]["URL"] = 3;
                            } else {
                                $data[$i]["URL"] = 1;
                            }

                            break;
                        case "COURSE":
                            $data[$i]["FEE_TYPE"] = TRAINING_PROGRAMS;
                            if ($value->STUDENT_SERVICES) {
                                $data[$i]["URL"] = 3;
                            } else {
                                $data[$i]["URL"] = 2;
                            }
                            break;
                        default:
                            $data[$i]["FEE_TYPE"] = "---";
                            $data[$i]["URL"] = 0;
                            break;
                    }

                    
                    $data[$i]["PRINT_URL"] = "/finance/printfee?objectId=" . $value->STUDENT_ID . "&fee=" . $value->FEE_ID . "&feeprepay=" . $studentPrePay->FEE_PREPAYMENT_ID . "";
                    
                } else {

                    if (!$value->STUDENT_SERVICES) {

                        $data[$i]["SCHOLARSHIP"] = $value->SCHOLARSHIP;

                        if ($value->AMOUNT_OPTION >= 1) {
                            $CHOOSE_OPTION = " (" . $value->CHOOSE_OPTION . ")";
                        } else {
                            $CHOOSE_OPTION = "";
                        }
                    } else {

                        $CHOOSE_OPTION = "";
                        $data[$i]["SCHOLARSHIP"] = "---";
                    }

                    $data[$i]["STUDENT_SERVICES"] = $value->STUDENT_SERVICES;
                    $data[$i]["FEE_PRE_PAY"] = "NORMAL";
                    //veasna
                    $data[$i]["FEE_PRE_PAY"] = "0";
                    $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                    $data[$i]["STUDENT_CODE"] = $value->STUDENT_CODE;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["FEE_CODE"] = $value->FEE_CODE;
                    //

                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["FEE_ID"] = $value->FEE_ID;
                    $data[$i]["FEE_NAME"] = "(" . $value->FEE_CODE . ") " . setShowText($value->FEE_NAME);
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = "(" . $value->STUDENT_CODE . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = "(" . $value->STUDENT_CODE . ") " . setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    // $data[$i]["TRANSACTION_AMOUNT"] = $value->TOTAL_AMOUNT ? getNumberFormat($value->TOTAL_AMOUNT) . " " . self::getCurrency() : "0 " . self::getCurrency();
                    if ($value->CHOOSE_OPTION >= 1) {
                        $feeOptionObject = FeeOptionDBAccess::getFeeOptionById($value->CHOOSE_OPTION);
                        $CHOOSE_OPTION = " (" . $feeOptionObject->N_OPTION . ")";
                        $data[$i]["TRANSACTION_AMOUNT"] = $feeOptionObject->TOTAL ? displayNumberFormat($feeOptionObject->TOTAL) . " " . self::getCurrency() : "0 " . self::getCurrency();
                    } else {

                        $feeOptionObject = FeeOptionDBAccess::getFristFeeOptionByFeeId($value->FEE_ID);
                        $CHOOSE_OPTION = "---";
                        //$data[$i]["TRANSACTION_AMOUNT"] = $feeOptionObject->TOTAL ? displayNumberFormat($feeOptionObject->TOTAL) . " " . self::getCurrency() : "0 " . self::getCurrency();
                        $data[$i]["TRANSACTION_AMOUNT"] = $value->TOTAL_AMOUNT ? displayNumberFormat($value->TOTAL_AMOUNT) . " " . self::getCurrency() : "0 " . self::getCurrency();
                    }

                    //$data[$i]["AMOUNT_PAID"] = $value->AMOUNT_PAID ? getNumberFormat($value->AMOUNT_PAID) . " " . self::getCurrency() : "0 " . self::getCurrency();
                    $data[$i]["AMOUNT_PAID"] = (self::findPaidAmounGeneral($value->STUDENT_ID, $value->FEE_ID)) ? displayNumberFormat(self::findPaidAmounGeneral($value->STUDENT_ID, $value->FEE_ID)) . " " . self::getCurrency() : "0 " . self::getCurrency();
                    $data[$i]["AMOUNT_PAID"] .= $CHOOSE_OPTION;

                    $data[$i]["AMOUNT_OWED"] = $value->AMOUNT_OWED ? displayNumberFormat($value->AMOUNT_OWED) . " " . self::getCurrency() : "0 " . self::getCurrency();

                    switch ($value->FEE_TYPE) {
                        case "GENERAL_EDU":
                            $data[$i]["FEE_TYPE"] = HIGHER_EDUCATION;

                            if ($value->STUDENT_SERVICES) {
                                $data[$i]["URL"] = 3;
                            } else {
                                $data[$i]["URL"] = 1;
                            }

                            break;
                        case "COURSE":
                            $data[$i]["FEE_TYPE"] = TRAINING_PROGRAMS;
                            if ($value->STUDENT_SERVICES) {
                                $data[$i]["URL"] = 3;
                            } else {
                                $data[$i]["URL"] = 2;
                            }
                            break;
                        default:
                            $data[$i]["FEE_TYPE"] = "---";
                            $data[$i]["URL"] = 0;
                            break;
                    }

                    $data[$i]["PRINT_URL"] = "/finance/printfee?objectId=" . $value->STUDENT_ID . "&fee=" . $value->FEE_ID . "";

                    if ($value->ACTION_PAYMENT) {
                        $data[$i]["DELETE_URL"] = "/finance/deletepayments?objectId=" . $value->STUDENT_ID . "&fee=" . $value->FEE_ID . "";
                    } else {
                        $data[$i]["DELETE_URL"] = "";
                    }
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

    public static function actionStudentFeeServices($params) {

        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "0";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";
        $feeId = isset($params["id"]) ? addText($params["id"]) : "0";
        $facette = self::getStudentFee($studentId, $feeId);

        //error_log($studentId." Fee: ".$feeId);
        if ($facette) {
            $SQL = "UPDATE t_student_fee";
            $SQL .= " SET";
            if ($newValue) {
                $SQL .= " CHOOSE_SERVICE = '2'";
            } else {
                if ($facette->AMOUNT_PAID) {
                    $SQL .= " CHOOSE_SERVICE = '2'";
                } else {
                    $SQL .= " CHOOSE_SERVICE = '0'";
                }
            }
            $SQL .= " WHERE";
            $SQL .= " STUDENT= '" . $studentId . "'";
            $SQL .= " AND FEE= '" . $feeId . "'";
            self::dbAccess()->query($SQL);
        }

        return array(
            "success" => true
        );
    }

    public static function jsonListStudentFeeServices($params) {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";

        $searchParams["studentId"] = $studentId;
        $searchParams["searchService"] = 1;
        $result = self::getSQLSearchCasshStudent($searchParams);

        $data = array();

        $i = 0;
        foreach ($result as $value) {

            $data[$i]["ID"] = $value->FEE_ID;

            if ($value->CHOOSE_SERVICE == 2) {
                $data[$i]["ASSIGNED"] = 1;
            } else {
                $data[$i]["ASSIGNED"] = 0;
            }

            $data[$i]["NAME"] = "(" . $value->FEE_CODE . ") " . $value->FEE_NAME;
            $data[$i]["AMOUNT_PAID"] = displayNumberFormat($value->AMOUNT_PAID) . " " . self::getCurrency();
            $data[$i]["AMOUNT_OWED"] = displayNumberFormat($value->AMOUNT_OWED) . " " . self::getCurrency();
            $data[$i]["PAID_STATUS"] = defined($value->PAID_STATUS) ? constant($value->PAID_STATUS) : "---";
            ++$i;
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function getStudentFeeTraining($studentId, $feeId) {
        $SQL = self::dbSelectAccess();
        $SQL->distinct();
        $SQL->from(array('A' => 't_fee'), array("ID", "NAME", "FIRST_AMOUNT AS AMOUNT"));
        $SQL->joinLeft(array('B' => 't_student_fee'), 'A.ID=B.FEE', array());
        $SQL->joinLeft(array('C' => 't_student_training'), 'C.TERM=B.TRAINING', array("SCHOLARSHIP", "TRAINING AS TRAINING_ID"));
        $SQL->where("A.ID = '" . $feeId . "'");
        $SQL->where("C.STUDENT = '" . $studentId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function addStudent2Fee($studentId, $feeId, $gradeId, $schoolyearId, $trainingId, $chooseService = false) {

        $feeObject = self::findFeeFromId($feeId);
        $firstOptionFee = FeeOptionDBAccess::getFristFeeOptionByFeeId($feeId); //@veasna

        if (!$trainingId && $gradeId && $schoolyearId) {
            $studentObject = StudentAcademicDBAccess::getCurrentStudentAcademic($studentId);
        } elseif ($trainingId && !$gradeId && !$schoolyearId) {
            $studentObject = StudentTrainingDBAccess::getStudentTraining($studentId, $trainingId, "TERM");
        }

        if (isset($feeObject) && isset($studentObject)) {

            $STUDENT_FEE = self::getStudentFee($studentId, $feeId);

            $SQL = "INSERT INTO t_student_fee SET";
            $SQL .= " PAID_STATUS='NOT_YET_PAID'";

            if ($gradeId)
                $SQL .= ",GRADE='" . $gradeId . "'";
            if ($schoolyearId)
                $SQL .= ",SCHOOLYEAR='" . $schoolyearId . "'";
            if ($trainingId)
                $SQL .= ",TRAINING='" . $trainingId . "'";

            if ($chooseService) {
                $SQL .= ",CHOOSE_SERVICE='" . $chooseService . "'";
            } else {
                if ($feeObject->STUDENT_SERVICES) {
                    $SQL .= ",CHOOSE_SERVICE='0'";
                } else {
                    $SQL .= ",CHOOSE_SERVICE='1'";
                }
            }

            //@veasna

             if ($firstOptionFee) {
                $SQL .= ",TOTAL_AMOUNT='" . $firstOptionFee->AMOUNT . "'";
                $SQL .= ",AMOUNT_OWED='" . $firstOptionFee->AMOUNT . "'";
            } else {
                $SQL .= ",TOTAL_AMOUNT='" . $feeObject->FIRST_TOTAL . "'";
                $SQL .= ",AMOUNT_OWED='" . $feeObject->FIRST_TOTAL . "'";
            }
            $SQL .= ",STUDENT='" . $studentId . "'";
            $SQL .= ",FEE='" . $feeId . "'";

            //error_log($SQL);
            if (!$STUDENT_FEE)
                self::dbAccess()->query($SQL);
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Set all students to general fee...
    ////////////////////////////////////////////////////////////////////////////
    public static function setStudents2GeneralFee($feeId, $gradeId, $schoolyearId) {

        $gradeSchoolyearObject = AcademicDBAccess::findGradeSchoolyear($gradeId, $schoolyearId);
        if ($gradeSchoolyearObject) {
            $entries = StudentAcademicDBAccess::getQueryStudentEnrollment($gradeSchoolyearObject->ID, false);
            if ($entries) {
                foreach ($entries as $value) {
                    self::addStudent2Fee($value->STUDENT_ID, $feeId, $gradeId, $schoolyearId, false);
                }
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    // Set all students to training fee...
    ////////////////////////////////////////////////////////////////////////////
    public static function setStudents2TrainingFee($feeId, $trainingId) {

        $entries = StudentTrainingDBAccess::sqlStudentTraining(false, $trainingId, false);

        if ($entries) {
            foreach ($entries as $value) {
                self::addStudent2Fee($value->STUDENT_ID, $feeId, false, false, $trainingId);
            }
        }
    }

    public static function changePaymentStatus($studentId, $feeId) {

        $feeObject = self::findFeeFromId($feeId);
        $facette = self::getStudentFee($studentId, $feeId);

        $USER_OWED = 0;
        $PAID_STATUS = 'NOT_YET_PAID';

        if ($facette && $feeObject) {

            $AMOUNT_PAID = $facette->AMOUNT_PAID + $facette->SCHOLARSHIP_AMOUNT;

            if ($facette->AMOUNT_PAID == 0) {
                if ($facette->SCHOLARSHIP_AMOUNT == $facette->TOTAL_AMOUNT) {
                    $PAID_STATUS = 'PAID';
                } else {
                    $PAID_STATUS = 'NOT_YET_PAID';
                }
                if ($facette->SCHOLARSHIP_AMOUNT) {
                    $USER_FEE_OWED = $facette->TOTAL_AMOUNT - $facette->SCHOLARSHIP_AMOUNT;
                } else {
                    $USER_FEE_OWED = $facette->TOTAL_AMOUNT;
                }
            } elseif ($facette->TOTAL_AMOUNT > $AMOUNT_PAID) {
                if ($facette->AMOUNT_OWED == 0) {
                    $PAID_STATUS = 'PAID';
                } else {
                    $PAID_STATUS = 'PARTLY_PAID';
                    $USER_FEE_OWED = $facette->TOTAL_AMOUNT - $AMOUNT_PAID;
                }
            } elseif ($facette->TOTAL_AMOUNT == $AMOUNT_PAID) {
                $PAID_STATUS = 'PAID';
                $USER_FEE_OWED = '0';
            } elseif ($facette->TOTAL_AMOUNT <= $facette->SCHOLARSHIP_AMOUNT + $facette->AMOUNT_PAID) {
                $PAID_STATUS = 'PAID';
            }

            //error_log($facette->SCHOLARSHIP_AMOUNT. " #### ".$facette->TOTAL_AMOUNT );

            $SQL = "";
            $SQL .= "UPDATE t_student_fee";
            $SQL .= " SET";
            $SQL .= " PAID_STATUS ='" . $PAID_STATUS . "'";

            if ($USER_OWED)
                $SQL .= ",AMOUNT_OWED ='" . $USER_OWED . "'";

            $SQL .= " WHERE STUDENT='" . $studentId . "' AND FEE='" . $feeId . "' AND ACTION_PAYMENT='1'";
            //error_log($SQL);
            self::dbAccess()->query($SQL);
        }
    }

    public static function setStudentFeesByChangeScholarship($studentId, $gradeId, $schoolyearId, $trainingId) {

        if ($gradeId && $schoolyearId && !$trainingId) {
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_fee'), array("ID AS FEE_ID"));
            $SQL->joinLeft(array('B' => 't_fee_general'), 'A.ID=B.FEE', array());
            $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'B.SCHOOLYEAR=C.SCHOOL_YEAR', array());
            $SQL->where("C.STUDENT = '" . $studentId . "'");
            $SQL->where("B.GRADE = '" . $gradeId . "'");
            $SQL->where("B.SCHOOLYEAR = '" . $schoolyearId . "'");
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchAll($SQL);
            if ($resultRows) {
                foreach ($resultRows as $value) {
                    self::deleteStudentFromFeeANDIncome($studentId, $value->FEE_ID, true, true);
                    self::addStudent2Fee($studentId, $value->FEE_ID, $gradeId, $schoolyearId, false);
                }
            }
        }

        if (!$gradeId && !$schoolyearId && $trainingId) {
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_fee'), array("ID AS FEE_ID"));
            $SQL->joinLeft(array('B' => 't_student_fee'), 'A.ID=B.FEE', array());
            $SQL->joinLeft(array('C' => 't_student_training'), 'C.TERM=B.TRAINING', array());
            $SQL->where("C.STUDENT = '" . $studentId . "'");
            $SQL->where("C.TRAINING = '" . $trainingId . "'");
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchAll($SQL);
            if ($resultRows) {
                foreach ($resultRows as $value) {
                    self::deleteStudentFromFeeANDIncome($studentId, $value->FEE_ID, true, true);
                    self::setStudents2TrainingFee($value->FEE_ID, $trainingId);
                }
            }
        }
    }

    public static function deleteStudentFromFeeANDIncome($studentId, $feeId, $deleteFee = false, $deleteIncome = false, $paymentRemove = false) {

        $DE_FEE = "";
        $DE_FEE .= "DELETE FROM t_student_fee";
        $DE_FEE .= " WHERE";
        $DE_FEE .= " STUDENT='" . $studentId . "'";
        $DE_FEE .= " AND FEE='" . $feeId . "'";
        if ($deleteFee)
            self::dbAccess()->query($DE_FEE);

        $DEL_INCOME = "";
        $DEL_INCOME .= "DELETE FROM t_income";
        $DEL_INCOME .= " WHERE";
        $DEL_INCOME .= " STUDENT='" . $studentId . "'";
        if ($paymentRemove) {
            $DEL_INCOME .= " AND PAYMENT_REMOVE='0'";
        }
        $DEL_INCOME .= " AND FEE='" . $feeId . "'";
        if ($deleteIncome)
            self::dbAccess()->query($DEL_INCOME);
    }

    public static function removeStudentPayment($params) {

        $paymentId = isset($params["id"]) ? addText($params["id"]) : "0";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";
        $feeId = isset($params["feeId"]) ? $params["feeId"] : "0";
        //@veasna
        $type=isset($params["type"])?strtoupper($params["type"]):"REFUND";
        //

        $PAYMENT_OBJECT = IncomeDBAccess::findIncomeFromId($paymentId);

        if ($PAYMENT_OBJECT) {

            $feeObject = self::findFeeFromId($feeId);
            $facette = self::getStudentFee($studentId, $feeId);
            $studentObject = StudentDBAccess::findStudentFromId($studentId);

            if ($facette && $studentObject && $feeObject) {
                 if($type=="REFUND"){
                ////////////////////////////////////////////////////////////////
                //Add into t_expense...
                ////////////////////////////////////////////////////////////////
                    $SAVEDATA["GUID"] = generateGuid();
                    $SAVEDATA["TRANSACTION_NUMBER"] = ExpenseDBAccess::getTNExpense("REF");
                    $SAVEDATA["STUDENT"] = $studentId;
                    $SAVEDATA["FEE"] = $feeId;
                    $SAVEDATA["PAYMENT_TYPE"] = 'REFUND';
                    $SAVEDATA["NAME"] = "(" . $studentObject->CODE . ") ";
                    $SAVEDATA["NAME"] .= $studentObject->LASTNAME . " " . $studentObject->FIRSTNAME;

                    /*if ($PAYMENT_OBJECT->SCHOLARSHIP_INDEX == 100) {
                        $SAVEDATA["AMOUNT"] = "0";
                    } else {
                        $SAVEDATA["AMOUNT"] = displayNumberFormat($PAYMENT_OBJECT->AMOUNT) ? $PAYMENT_OBJECT->AMOUNT : "0";
                    } */
                    $SAVEDATA["AMOUNT"] = displayNumberFormat($PAYMENT_OBJECT->INCOME_AMOUNT) ? $PAYMENT_OBJECT->INCOME_AMOUNT : "0";
                    $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                    $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                    self::dbAccess()->insert('t_expense', $SAVEDATA);
                ////////////////////////////////////////////////////////////////
                 }
                $UPDATE_STUDENT_FEE = "UPDATE t_student_fee";
                $UPDATE_STUDENT_FEE .= " SET";

                if ($feeObject->STUDENT_SERVICES) {
                    $UPDATE_STUDENT_FEE .= " CHOOSE_SERVICE = '0'";
                } else {
                    $UPDATE_STUDENT_FEE .= " CHOOSE_SERVICE = '1'";
                }
                
                $TOTAL_AMOUNT = $facette->TOTAL_AMOUNT;
                $DELETE_AMOUNT = $PAYMENT_OBJECT->INCOME_AMOUNT;
               
                if ($facette->AMOUNT_PAID > $DELETE_AMOUNT) {

                    $CALL_AMOUNT_PAID = $facette->AMOUNT_PAID - $DELETE_AMOUNT;
                    $CALL_AMOUNT_OWED = $facette->AMOUNT_OWED + $PAYMENT_OBJECT->AMOUNT;
                    
                    if ($CALL_AMOUNT_PAID == 0) {
                        $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '0'";
                        $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'NOT_YET_PAID'";
                        $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '0'";
                        $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $PAYMENT_OBJECT->AMOUNT . "'";
                    } else {
                        $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '1'";
                        $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'PARTLY_PAID'";
                        $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '" . $CALL_AMOUNT_PAID . "'";
                        $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $CALL_AMOUNT_OWED . "'";
                    }
                } else {
                    $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '0'";
                    $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'NOT_YET_PAID'";
                    $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '0'";
                    $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $TOTAL_AMOUNT . "'";
                }

                /*$TOTAL_AMOUNT = $facette->TOTAL_AMOUNT;
                $SCHOLARSHIP_AMOUNT = $facette->SCHOLARSHIP_AMOUNT;
                $DELETE_AMOUNT = $PAYMENT_OBJECT->AMOUNT;

                if ($SCHOLARSHIP_AMOUNT) {
                    $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '0'";
                    $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'NOT_YET_PAID'";
                    $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '0'";
                    $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $TOTAL_AMOUNT . "'";
                } else {
                    if ($facette->AMOUNT_PAID > $DELETE_AMOUNT) {

                        $CALL_AMOUNT_PAID = $facette->AMOUNT_PAID - $DELETE_AMOUNT;
                        $CALL_AMOUNT_OWED = $TOTAL_AMOUNT - $CALL_AMOUNT_PAID;

                        //error_log("Call paid: ".$CALL_AMOUNT_PAID." Call owed: ".$CALL_AMOUNT_OWED);

                        if ($CALL_AMOUNT_PAID == 0) {
                            $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '0'";
                            $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'NOT_YET_PAID'";
                            $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '0'";
                            $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $TOTAL_AMOUNT . "'";
                        } else {
                            $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '1'";
                            $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'PARTLY_PAID'";
                            $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '" . $CALL_AMOUNT_PAID . "'";
                            $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $CALL_AMOUNT_OWED . "'";
                        }
                    } else {
                        $UPDATE_STUDENT_FEE .= " ,ACTION_PAYMENT = '0'";
                        $UPDATE_STUDENT_FEE .= " ,PAID_STATUS = 'NOT_YET_PAID'";
                        $UPDATE_STUDENT_FEE .= " ,AMOUNT_PAID = '0'";
                        $UPDATE_STUDENT_FEE .= " ,AMOUNT_OWED = '" . $TOTAL_AMOUNT . "'";
                    }
                } */


                $UPDATE_STUDENT_FEE .= " WHERE";
                $UPDATE_STUDENT_FEE .= " STUDENT= '" . $studentId . "'";
                $UPDATE_STUDENT_FEE .= " AND FEE= '" . $feeId . "'";
                //error_log($UPDATE_STUDENT_FEE);
                self::dbAccess()->query($UPDATE_STUDENT_FEE);

                ///////////////////////////////////////
                if($type=="REFUND"){
                    $UPDATE_INCOME = "UPDATE t_income";
                    $UPDATE_INCOME .= " SET";
                    $UPDATE_INCOME .= " PAYMENT_REMOVE = '1'";
                    $UPDATE_INCOME .= " WHERE";
                    $UPDATE_INCOME .= " GUID= '" . $paymentId . "'";
                    self::dbAccess()->query($UPDATE_INCOME);
                }elseif($type=="DELETE"){
                    self::dbAccess()->delete('t_income', array("GUID='" . $paymentId . "'"));    
                }
            }
        }

        return array(
            "success" => true
        );
    }

    public static function getTNIncome($type = false) {

        $type = $type ? $type : "STD";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_income", array("TRANSACTION_NUMBER"));
        $SQL->where("TRANSACTION_NUMBER LIKE '" . $type . "-%'");
        $SQL->order('TRANSACTION_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            $cal = substr($result->TRANSACTION_NUMBER, 4) + 1;
            return "" . $type . "-" . $cal;
        } else {
            return "" . $type . "-100";
        }
    }

}

?>