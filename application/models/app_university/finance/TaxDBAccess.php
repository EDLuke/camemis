<?php
    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 6.08.2012
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once setUserLoacalization();

    class TaxDBAccess {

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

                $data["ID"] = $result->ID;
                $data["NAME"] = displayNumberFormat($result->NAME);
                $data["NUMBER"] = setShowText($result->NUMBER);
                $data["PERCENT"] = setShowText($result->PERCENT);
                $data["SORTKEY"] = $result->SORTKEY;
                $data["FORMULAR"] = $result->FORMULAR;
                $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            }

            return $data;
        }

        public static function findObjectFromId($Id) {

            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_tax";
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
        public static function checkUsedTax($Id){

            $check=0; 
            
            $SQL = self::dbSelectAccess();
            $SQL->from('t_income_category', array("C" => "COUNT(*)"));
            $SQL->where("TAX='".$Id."'");    
            $resultRows = self::dbAccess()->fetchRow($SQL);
            $checkIncomeCat=$resultRows?$resultRows->C:'';

            $SQL = self::dbSelectAccess();
            $SQL->from('t_expense_category', array("C" => "COUNT(*)"));
            $SQL->where("TAX ='".$Id."'");    
            $result= self::dbAccess()->fetchRow($SQL);
            $checkExpenseCat= $result?$result->C:'';
            if($checkIncomeCat || $checkExpenseCat){
                $check=1;        
            }

            return $check;      
        }
        //
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

            $SQL = "DELETE FROM t_tax";
            $SQL .= " WHERE";
            $SQL .= " PARENT='" . $Id . "'";
            self::dbAccess()->query($SQL);
        }

        public static function sqlRemoveObject($Id) {

            $SQL = "DELETE FROM t_tax";
            $SQL .= " WHERE";
            $SQL .= " ID='" . $Id . "'";
            self::dbAccess()->query($SQL);
        }

        public static function updateObject($params) {

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

            $SAVEDATA = array();

            $errors = array();
            $CHECK_USED_NUMBER = self::checkUsedAccountNumber(addText($params["NUMBER"]));

            if ($objectId == "new") {
                if ($CHECK_USED_NUMBER)
                    $errors["NUMBER"] = USED;
            }else {
                if ($CHECK_USED_NUMBER > 1) {
                    $errors["NUMBER"] = USED;
                }
            }

            if (isset($params["NAME"]))
                $SAVEDATA['NAME'] = addText($params["NAME"]);

            if (isset($params["NUMBER"]))
                $SAVEDATA['NUMBER'] = addText($params["NUMBER"]);

            if (isset($params["PERCENT"]))
                $SAVEDATA['PERCENT'] = addText($params["PERCENT"]);

            if (isset($params["FORMULAR"]))
                $SAVEDATA['FORMULAR'] = addText($params["FORMULAR"]);

            if (isset($params["SORTKEY"]))
                $SAVEDATA['SORTKEY'] = addText($params["SORTKEY"]);

            if (isset($params["DESCRIPTION"]))
                $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

            if ($objectId == "new") {
                if (isset($params["parentId"]))
                    $SAVEDATA['PARENT'] = addText($params["parentId"]);
                self::dbAccess()->insert('t_tax', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
            } else {

                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update('t_tax', $SAVEDATA, $WHERE);
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

        public static function sqlTax($node) {

            $SQL = "SELECT * FROM t_tax";
            $SQL .= " WHERE 1=1";
            if (!$node)
                $SQL .= " AND PARENT=0";
            else
                $SQL .= " AND PARENT=" . $node . "";

            $SQL .= " ORDER BY SORTKEY ASC";
            //error_log($SQL);
            return self::dbAccess()->fetchAll($SQL);
        }

        public static function jsonTreeAllTaxes($params) {

            $node = isset($params["node"]) ? addText($params["node"]) : 0;

            $data = array();
            $result = self::sqlTax($node);

            if ($result) {
                $i = 0;
                foreach ($result as $value) {

                    if ($value->NUMBER) {
                        $data[$i]['text'] = "(" . displayNumberFormat($value->NUMBER) . ") " . $value->NAME;
                    } else {
                        $data[$i]['text'] = $value->NAME;
                    }

                    if ($value->PERCENT) {
                        switch ($value->FORMULAR) {
                            case 1:
                                $data[$i]['text'] = "(" . displayNumberFormat($value->NUMBER) . ") " .$value->NAME . " (+ " . $value->PERCENT . "%)";
                                break;
                            case 2:
                                $data[$i]['text'] = "(" . displayNumberFormat($value->NUMBER) . ") " .$value->NAME . " (- " . $value->PERCENT . "%)";
                                break;
                        }
                    }

                    $data[$i]['id'] = $value->ID;
                    $data[$i]['parent'] = $value->ID;
                    if ($value->PARENT == 0) {
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                    } else {

                        if (self::checkChild($value->ID)) {
                            $data[$i]['leaf'] = false;
                            $data[$i]['cls'] = "nodeTextBold";
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                        } else {
                            $data[$i]['leaf'] = true;
                            $data[$i]['cls'] = "nodeText";
                            $data[$i]['iconCls'] = "icon-plugin";
                        }
                    }
                    $i++;
                }
            }

            return $data;
        }

        public static function checkChild($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_tax", array("C" => "COUNT(*)"));
            $SQL->where("PARENT = '" . $Id . "'");
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public function allTaxComboData($parent, $type = false) {

            $data = array();
            $result = self::sqlTax($parent, $type);

            $data[0] = "[\"0\",\"[---]\"]";
            $i = 0;
            if ($result)
                foreach ($result as $value) {

                    $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                    $i++;
            }

            return "[" . implode(",", $data) . "]";
        }

        public static function checkUsedAccountNumber($value) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_tax", array("C" => "COUNT(*)"));
            $SQL->where("NUMBER = '" . trim($value) . "'");
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

    }

?>