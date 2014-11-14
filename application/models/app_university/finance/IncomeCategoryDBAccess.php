<?php
    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 6.08.2012
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once 'models/app_university/finance/TaxDBAccess.php';
    require_once setUserLoacalization();

    class IncomeCategoryDBAccess {

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

        public static function dbSelectAccess() {
            return self::dbAccess()->select();
        }

        public static function getObjectDataFromId($Id) {

            $result = self::findObjectFromId($Id);

            $data = array();
            if ($result) {

                $taxObject = TaxDBAccess::findObjectFromId($result->TAX);

                $data["ID"] = $result->ID;
                $data["NAME"] = setShowText($result->NAME);
                $data["ACCOUNT_NUMBER"] = setShowText($result->ACCOUNT_NUMBER);
                $data["SORTKEY"] = $result->SORTKEY;
                $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
                $data["HIDDEN_TAX"] = $result->TAX;

                if ($taxObject)
                    $data["CHOOSE_TAX_NAME"] = $taxObject->NAME;
            }

            return $data;
        }

        public static function findObjectFromId($Id) {

            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_income_category";
            $SQL .= " WHERE";
            $SQL .= " ID = '" . $Id . "'";
            $result = self::dbAccess()->fetchRow($SQL);

            return $result;
        }

        public static function loadObject($Id) {

            $result = self::findObjectFromId($Id);

            if ($result) {
                $o = array(
                    "success" => true
                    , "data" => self::getObjectDataFromId($Id)
                );
            } else {
                $o = array(
                    "success" => true
                    , "data" => array()
                );
            }
            return $o;
        }
        
        ///@veasna
        public static function findChild($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_income_category", array("*"));
            $SQL->where("PARENT = ?",$Id);
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);
            return $result ? $result: 0;
        }
        
        public static function checkUsedIncomeCat($Id){
           
            $check=0;
            $checkChilde=self::findChild($Id);
            if($checkChilde){
                foreach($checkChilde as $value){
                    
                    $SQL = self::dbSelectAccess();
                    $SQL->from('t_income', array("C" => "COUNT(*)"));
                    $SQL->where("INCOME_CATEGORY = '".$value->ID."'");    
                    $result= self::dbAccess()->fetchRow($SQL);
                    $checkIncome= $result?$result->C:'';
                    
                    $SQL = self::dbSelectAccess();
                    $SQL->from('t_fee', array("C" => "COUNT(*)"));
                    $SQL->where("INCOME_CATEGORY = '".$value->ID."'");    
                    $resultRow= self::dbAccess()->fetchRow($SQL);
                    $checkFee=$resultRow?$resultRow->C:'';
                    if($checkIncome || $checkFee){
                        $check=1;       
                        break; 
                    }           
                }            
            }else{
                
                $SQL = self::dbSelectAccess();
                $SQL->from('t_income', array("C" => "COUNT(*)"));
                $SQL->where("INCOME_CATEGORY = '".$Id."'");    
                $result= self::dbAccess()->fetchRow($SQL);
                $checkIncome= $result?$result->C:'';
                
                $SQL = self::dbSelectAccess();
                $SQL->from('t_fee', array("C" => "COUNT(*)"));
                $SQL->where("INCOME_CATEGORY = '".$Id."'");    
                $resultRow= self::dbAccess()->fetchRow($SQL);
                $checkFee=$resultRow?$resultRow->C:'';
                
                
                if($checkIncome || $checkFee){
                    $check=1;        
                }   
            }
            
            
            return $check;   
            
        }
        
        ///

        public static function removeObject($params) {

            $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
            $CHECK = self::checkChild($removeId);
            if ($CHECK) {
                self::sqlRemoveChilObject($removeId);
            }

            self::sqlRemoveObject($removeId);
            return array("success" => true);
        }

        public static function sqlRemoveChilObject($Id) {
            self::dbAccess()->delete("t_income_category", " PARENT='" . $Id . "'");
        }

        public static function sqlRemoveObject($Id) {
            self::dbAccess()->delete("t_income_category", " ID='" . $Id . "'");
        }

        public static function updateObject($params) {

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

            $SAVEDATA = array();

            $errors = array();
            $CHECK_USED_NUMBER = self::checkUsedAccountNumber(addText($params["ACCOUNT_NUMBER"]));

            if ($objectId == "new") {
                if ($CHECK_USED_NUMBER)
                    $errors["ACCOUNT_NUMBER"] = USED;
            }else {
                if ($CHECK_USED_NUMBER > 1) {
                    $errors["ACCOUNT_NUMBER"] = USED;
                }
            }

            if (isset($params["NAME"]))
                $SAVEDATA['NAME'] = addText($params["NAME"]);

            if (isset($params["ACCOUNT_NUMBER"]))
                $SAVEDATA['ACCOUNT_NUMBER'] = addText($params["ACCOUNT_NUMBER"]);

            if (isset($params["HIDDEN_TAX"]))
                $SAVEDATA['TAX'] = addText($params["HIDDEN_TAX"]);

            if (isset($params["SORTKEY"]))
                $SAVEDATA['SORTKEY'] =  addText($params["SORTKEY"]);

            if (isset($params["DESCRIPTION"]))
                $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

            if ($objectId == "new") {
                if (isset($params["parentId"]))
                    $SAVEDATA['PARENT'] = addText($params["parentId"]);

                if (!$errors) {
                    self::dbAccess()->insert('t_income_category', $SAVEDATA);
                    $objectId = self::dbAccess()->lastInsertId();
                }
            } else {
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                if (!$errors)
                    self::dbAccess()->update('t_income_category', $SAVEDATA, $WHERE);
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

        public static function sqlIncomeCategory($node) {

            $SQL = "SELECT * FROM t_income_category";
            $SQL .= " WHERE 1=1";
            if (!$node)
                $SQL .= " AND PARENT=0";
            else
                $SQL .= " AND PARENT=" . $node . "";

            $SQL .= " ORDER BY SORTKEY ASC";
            //error_log($SQL);
            return self::dbAccess()->fetchAll($SQL);
        }

        public static function jsonTreeAllIncomeCategories($params) {

            $node = isset($params["node"]) ? addText($params["node"]) : 0;

            $data = array();
            $result = self::sqlIncomeCategory($node);

            if ($result) {
                $i = 0;
                foreach ($result as $value) {

                    $data[$i]['id'] = $value->ID;
                    $data[$i]['parent'] = $value->ID;
                    if ($value->PARENT == 0) {
                        $data[$i]['text'] = "(".$value->ACCOUNT_NUMBER . ") " . $value->NAME;
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                    } else {

                        if (self::checkChild($value->ID)) {
                            $data[$i]['text'] = "(".$value->ACCOUNT_NUMBER . ") " . $value->NAME;
                            $data[$i]['leaf'] = false;
                            $data[$i]['cls'] = "nodeTextBold";
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                        } else {
                            $data[$i]['text'] = "(".$value->ACCOUNT_NUMBER . ") " . $value->NAME;
                            $data[$i]['leaf'] = true;
                            $data[$i]['cls'] = "nodeTextBlue";
                            $data[$i]['iconCls'] = "icon-plugin";
                        }
                    }
                    $i++;
                }
            }

            return $data;
        }

        public static function checkUsedAccountNumber($value) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_income_category", array("C" => "COUNT(*)"));
            $SQL->where("ACCOUNT_NUMBER = '" . trim($value) . "'");
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public static function checkChild($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_income_category", array("C" => "COUNT(*)"));
            $SQL->where("PARENT = ?",$Id);
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public function allIncomeCategoryComboData($parent, $type = false) {

            $data = array();
            $result = self::sqlIncomeCategory($parent, $type);

            $data[0] = "[\"0\",\"[---]\"]";
            $i = 0;
            if ($result)
                foreach ($result as $value) {

                    $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                    $i++;
            }

            return "[" . implode(",", $data) . "]";
        }

        public static function findTaxByCategory($Id) {

            $SQL = "";
            $SQL .= "SELECT B.*";
            $SQL .= " FROM t_income_category AS A";
            $SQL .= " LEFT JOIN t_tax AS B ON B.ID = A.TAX";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND A.ID= '" . $Id . "'";
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            if ($result) {
                switch ($result->FORMULAR) {
                    case 1:
                        return "+" . $result->PERCENT;
                        break;
                    case 2:
                        return "-" . $result->PERCENT;
                        break;
                }
            } else {
                return "";
            }
        }
        
        public static function findAllTaxByCategory($Id) {

            $SQL = "";
            $SQL .= "SELECT B.*";
            $SQL .= " FROM t_income_category AS A";
            $SQL .= " LEFT JOIN t_tax AS B ON B.ID = A.TAX";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND A.ID= '" . $Id . "'";
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            
            return $result; 
        }

    }

?>