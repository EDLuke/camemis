<?php
    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 6.08.2012
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once 'models/app_school/finance/FeeDBAccess.php';
    require_once 'models/app_school/finance/IncomeCategoryDBAccess.php';
    require_once setUserLoacalization();

    class IncomeDBAccess {

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

        public static function dbAccess() {
            return Zend_Registry::get('DB_ACCESS');
        }

        public static function getCurrency() {

            return Zend_Registry::get('SCHOOL')->CURRENCY;
        }

        public static function dbSelectAccess() {
            return self::dbAccess()->select();
        }

        public static function getIncomeDataFromId($Id) {

            $result = self::findIncomeFromId($Id);

            $data = array();
            if ($result) {

                $category = IncomeCategoryDBAccess::findObjectFromId($result->INCOME_CATEGORY);

                $data["ID"] = $result->ID;
                $data["NAME"] = setShowText($result->NAME);
                $data["TRANSACTION_NUMBER"] = setShowText($result->TRANSACTION_NUMBER);

                if ($result->FEE) {
                    $data["PAYMENT_AMOUNT"] = displayNumberFormat(self::getTotalPaidByFee($result->FEE));
                } else {
                    $data["PAYMENT_AMOUNT"] = displayNumberFormat($result->AMOUNT);
                }

                $data["TRANSACTION_TYPE"] = $result->TRANSACTION_TYPE;
                $data["CHOOSE_INCOME_CATEGORY"] = $result->INCOME_CATEGORY;

                if ($category)
                    $data["CHOOSE_INCOME_CATEGORY_NAME"] = $category->NAME . " (" . $category->ACCOUNT_NUMBER . ")";

                if ($result->STUDENT) {
                    $studentObject = StudentDBAccess::findStudentFromId($result->STUDENT);
                    $data["STUDENT"] = "(".$studentObject->CODE.") ".$studentObject->LASTNAME . " " . $studentObject->FIRSTNAME ;
                }
                
                //
                $data["FEES"]= displayNumberFormat($result->AMOUNT);
                $data["TOTAL_AMOUNT"]= displayNumberFormat($result->INCOME_AMOUNT);
                $data["TAX"] = displayNumberFormat($result->AMOUNT_TAX);
                $data["DISCOUNT"] = displayNumberFormat($result->DISCOUNT); 
                $data["SCHOLARSHIP"]= displayNumberFormat($result->SCHOLARSHIP_AMOUNT);
                //

                $data["INCOME_STATUS"] = $result->INCOME_STATUS;
                $data["CREATED_DATE"] = $result->CREATED_DATE;
                $data["CREATED_BY"] = $result->CREATED_BY;
                $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);

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

        public static function findIncomeFromId($Id) {

            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_income";
            $SQL .= " WHERE";

            if (is_numeric($Id)) {
                $SQL .= " ID = '" . $Id . "'";
            } else {
                $SQL .= " GUID = '" . $Id . "'";
            }

            $result = self::dbAccess()->fetchRow($SQL);

            return $result;
        }

        public static function findIncomeFromFeeId($Id) {

            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_income";
            $SQL .= " WHERE";
            $SQL .= " FEE = '" . $Id . "'";
            $result = self::dbAccess()->fetchRow($SQL);

            return $result;
        }

        public static function loadIncome($Id) {

            $result = self::findIncomeFromId($Id);

            if ($result) {
                $o = array(
                    "success" => true
                    , "data" => self::getIncomeDataFromId($Id)
                );
            } else {
                $o = array(
                    "success" => true
                    , "data" => array()
                );
            }
            return $o;
        }

        public static function removeIncome($removeId) {

            //$removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
            self::sqlRemoveIncome($removeId);
            return array("success" => true);
        }

        public static function sqlRemoveIncome($Id) {

            $SQL = "DELETE FROM t_income";
            $SQL .= " WHERE";
            $SQL .= " GUID='" . $Id . "'";
            self::dbAccess()->query($SQL);
        }

        public static function saveIncome($params) {

            $SAVEDATA = array();

            $RADIOBOX_DATA = array();
            $CHECKBOX_DATA = array();

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
            $facette = self::findIncomeFromId($objectId);

            if (isset($params["NAME"]))
                $SAVEDATA["NAME"] = addText($params["NAME"]);

            if (isset($params["PAYMENT_AMOUNT"])){
                $SAVEDATA["AMOUNT"] = str2no($params["PAYMENT_AMOUNT"]);
                
            } 

            if (isset($params["TRANSACTION_TYPE"]))
                $SAVEDATA["TRANSACTION_TYPE"] = addText($params["TRANSACTION_TYPE"]);

            if (isset($params["CHOOSE_INCOME_CATEGORY"])){
                $SAVEDATA["INCOME_CATEGORY"] = addText($params["CHOOSE_INCOME_CATEGORY"]);
            
            }
            
            //@veasna
                $Incomcat=isset($params["CHOOSE_INCOME_CATEGORY"])?$params["CHOOSE_INCOME_CATEGORY"]:'';
                if($Incomcat){
                    $INCOME_AMOUNT=isset($params["PAYMENT_AMOUNT"])?$params["PAYMENT_AMOUNT"]:0;
                    $incomeTaxObject=IncomeCategoryDBAccess::findAllTaxByCategory($Incomcat);
                    $AMOUNT_TAX=($INCOME_AMOUNT * $incomeTaxObject->PERCENT)/100;
                   
                    if($incomeTaxObject->FORMULAR=='1'){
                        $INCOME_AMOUNT=$INCOME_AMOUNT+$AMOUNT_TAX;
                    }
                    
                    $SAVEDATA["INCOME_AMOUNT"] =$INCOME_AMOUNT;
                    $SAVEDATA["FORMULAR_TAX"] =$incomeTaxObject->FORMULAR;
                    $SAVEDATA["AMOUNT_TAX"] =$AMOUNT_TAX;
                
                }
                
            //
            

            if (isset($params["INCOME_STATUS"]))
                $SAVEDATA["INCOME_STATUS"] = addText($params["INCOME_STATUS"]);

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

            if ($facette) {
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $facette->ID);
                self::dbAccess()->update('t_income', $SAVEDATA, $WHERE);
            } else {

                $SAVEDATA["GUID"] = generateGuid();
                $SAVEDATA['TRANSACTION_NUMBER'] = self::getTNIncome();
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                self::dbAccess()->insert('t_income', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
            }

            return array(
                "success" => true
                , "objectId" => $objectId
            );
        }

        public static function sqlSearchIncome($params) {

            $name = isset($params["name"]) ? addText($params["name"]) : "";
            $transactionNumber = isset($params["transactionNumber"]) ? addText($params["transactionNumber"]) : "";
            $startDate = isset($params["startDate"]) ? $params["startDate"] : "";
            $endDate = isset($params["endDate"]) ? $params["endDate"] : "";
            $categoryId = isset($params["categoryId"]) ? $params["categoryId"] : "";
            $financeDescription = isset($params["financeDescription"]) ? addText($params["financeDescription"]) : "";

            $SELECTION_A = array(
                "ID AS ID"
                , "GUID AS GUID"
                , "FEE AS FEE"
                , "NAME AS TRANSACTION_NAME"
                , "TRANSACTION_NUMBER AS TRANSACTION_NUMBER"
                , "INCOME_AMOUNT AS INCOME_AMOUNT"
                , "AMOUNT AS TRANSACTION_AMOUNT"
                , "SCHOLARSHIP_PERCENT AS SCHOLARSHIP_PERCENT"
                , "SCHOLARSHIP_AMOUNT AS SCHOLARSHIP_AMOUNT"
                , "DISCOUNT AS DISCOUNT_AMOUNT"
                , "DISCOUNT_PERCENT AS DISCOUNT_PERCENT"
                , "AMOUNT_TAX AS AMOUNT_TAX"
                , "TRANSACTION_TYPE AS TRANSACTION_TYPE"
                , "INCOME_CATEGORY AS TRANSACTION_CATEGORY"
                , "CREATED_DATE AS TRANSACTION_DATE"
                , "INCOME_STATUS AS INCOME_STATUS"
                , "CREATED_BY AS CREATED_BY"
            );

            $SELECTION_B = array(
                "NAME AS TRANSACTION_CATEGORY"
                , "ACCOUNT_NUMBER AS ACCOUNT_NUMBER"
            );

            $SELECTION_C = array(
                "NAME AS FEE_NAME"
            );

            $SELECTION_D = array(
                "NAME AS TAX_NAME"
                , "PERCENT AS TAX_PERCENT"
                , "FORMULAR AS TAX_FORMULAR"
            );

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_income'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_income_category'), 'B.ID=A.INCOME_CATEGORY', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_fee'), 'C.ID=A.FEE', $SELECTION_C);
            $SQL->joinLeft(array('D' => 't_tax'), 'B.TAX=D.ID', $SELECTION_D);

            if ($categoryId) {
                $categoryObject = IncomeCategoryDBAccess::findObjectFromId($categoryId);
                if ($categoryObject->PARENT == 0) {
                    $searchStr = substr($categoryObject->ACCOUNT_NUMBER, 0, 2);
                } else {
                    $searchStr = substr($categoryObject->ACCOUNT_NUMBER, 0, 3);
                }
                $SQL->where('B.TRANSACTION_NUMBER LIKE ?', $searchStr . '%');
            }

            if ($startDate && $endDate) {
                $SQL->where("DATE(A.CREATED_DATE) BETWEEN '" . $startDate . "' AND '" . $endDate . "'");
            }

            if ($name)
                $SQL->where('A.NAME LIKE ?', $name . '%');

            if ($transactionNumber) {
                $SQL->orWhere('A.TRANSACTION_NUMBER LIKE ?', "INC-" . $transactionNumber . '%');
                $SQL->orWhere('A.TRANSACTION_NUMBER LIKE ?', "STD-" . $transactionNumber . '%');
                $SQL->orWhere('A.TRANSACTION_NUMBER LIKE ?', "TRA-" . $transactionNumber . '%');
            }

            if ($financeDescription)
                $SQL->where("A.FINANCE_DESCRIPTION LIKE '%" . $financeDescription . "%'");

            $SQL->order('A.CREATED_DATE DESC');

            //error_log($SQL->__toString());
            return self::dbAccess()->fetchAll($SQL);
        }

        public static function getTotalPaidByFee($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_income", array("C" => "SUM(AMOUNT) AS SUM_AMOUNT"));
            $SQL->where("FEE = '" . $Id . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->SUM_AMOUNT : 0;
        }

        public static function checkIncomeByFee($feeId) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_income", array("C" => "COUNT(*)"));
            $SQL->where("FEE = '" . $feeId . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public static function jsonSearchIncome($params) {

            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

            $result = self::sqlSearchIncome($params);
            $i = 0;

            $data = array();
            if ($result) {
                foreach ($result as $value) {

                    $data[$i]["ID"] = $value->GUID;

                    $data[$i]["TRANSACTION_NAME"] = displayNumberFormat($value->TRANSACTION_NUMBER);
                    $data[$i]["TRANSACTION_NAME"] .= "<br>";
                    $data[$i]["TRANSACTION_NAME"] .= $value->TRANSACTION_NAME;
                    $data[$i]["TRANSACTION_CATEGORY"] = displayNumberFormat($value->ACCOUNT_NUMBER);
                    $data[$i]["TRANSACTION_AMOUNT"] = displayNumberFormat($value->INCOME_AMOUNT)." ". self::getCurrency()."(".FEES.": ".displayNumberFormat($value->TRANSACTION_AMOUNT) . " " . self::getCurrency().")";
                    $data[$i]["SCHOLARSHIP_AMOUNT"] = displayNumberFormat($value->SCHOLARSHIP_AMOUNT)." ". self::getCurrency();
                    $data[$i]["SCHOLARSHIP_PERCENT"] = $value->SCHOLARSHIP_PERCENT." ". self::getCurrency();
                    $data[$i]["DISCOUNT_AMOUNT"] = displayNumberFormat($value->DISCOUNT_AMOUNT)." ".self::getCurrency();
                    $data[$i]["DISCOUNT_PERCENT"] = $value->DISCOUNT_PERCENT." ". self::getCurrency();
                    $data[$i]["TOTAL_AMOUNT"] = displayNumberFormat($value->INCOME_AMOUNT)." ". self::getCurrency();
                    $data[$i]["AMOUNT_TAX"] = displayNumberFormat($value->AMOUNT_TAX)." ". self::getCurrency();
                    $school_income=$value->INCOME_AMOUNT-$value->AMOUNT_TAX;
                    $data[$i]["SCHOOL_INCOME"] = displayNumberFormat($school_income)." ". self::getCurrency();
                    if ($value->TAX_PERCENT && $value->TAX_FORMULAR) {
                        switch ($value->TAX_FORMULAR) {
                            case 1:
                                
                                $data[$i]["TAX_CATEGORY"] = $value->TAX_NAME;
                                $data[$i]["TAX_CATEGORY"] .= "<br>";
                                $data[$i]["TAX_CATEGORY"] .= " (+" . displayNumberFormat($value->TAX_PERCENT) . "%)";

                                break;
                            case 2:

                                $data[$i]["TAX_CATEGORY"] = $value->TAX_NAME;
                                $data[$i]["TAX_CATEGORY"] .= "<br>";
                                $data[$i]["TAX_CATEGORY"] .= " (-" . displayNumberFormat($value->TAX_PERCENT) . "%)";
                                
                                break;
                        }
                    } else {
                        $data[$i]["TAX_CATEGORY"] = setShowText($value->TAX_NAME);
                       
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

        public static function getTNIncome() {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_income", array("TRANSACTION_NUMBER"));
            $SQL->where("TRANSACTION_NUMBER LIKE 'INC-%'");
            $SQL->order('TRANSACTION_NUMBER DESC');
            $SQL->limit(1);
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            if ($result) {
                $cal = substr($result->TRANSACTION_NUMBER, 4) + 1;
                return "INC-" . $cal;
            } else {
                return "INC-100";
            }
        }
        
        public static function checkIncomeFeeGrade($fee,$grade){
            $SQL = self::dbAccess()->select();     
            $SQL->from("t_income", array("C" => "COUNT(*)"));
            $SQL->where("FEE = '".$fee."'");
            $SQL->where("GRADE = '".$grade."'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

    }

?>