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

    class FinanceDescriptionDBAccess {

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
                $data["CHOOSE_TYPE"] = $result->CHOOSE_TYPE;
                $data["LINK"] = $result->LINK;
                $data["NAME"] = setShowText($result->NAME);
            }

            return $data;
        }

        public static function findObjectFromId($Id) {

            $SQL = "SELECT DISTINCT *";
            $SQL .= " FROM t_finance_description";
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

            self::setUpdateAllChildren();

            return $o;
        }

        public static function addObject($params) {

            $SAVEDATA = array();
            $SAVEDATA['NAME'] = addText($params["NAME"]);

            if ($params["parentId"] > 0) {
                $SAVEDATA['PARENT'] =  addText($params["parentId"]);
                $SAVEDATA['OBJECT_TYPE'] = "ITEM";
                $facette = self::findObjectFromId($params["parentId"]);
                if ($facette) {
                    $SAVEDATA['CHOOSE_TYPE'] = $facette->CHOOSE_TYPE;
                }
            } else {

                $SAVEDATA['PARENT'] = 0;
                $SAVEDATA['CHOOSE_TYPE'] = 1;
                $SAVEDATA['OBJECT_TYPE'] = "FOLDER";

                if (isset($params["CHOOSE_TYPE"]))
                    $SAVEDATA['CHOOSE_TYPE'] = addText($params["CHOOSE_TYPE"]);

                if (isset($params["LINK"]))
                    $SAVEDATA['LINK'] = addText($params["LINK"]);
            }
            self::dbAccess()->insert('t_finance_description', $SAVEDATA);
            return array("success" => true);
        }
        
        public static function checkUsedFinanceDescription($Id){
            
            $check=0;
            $SQL = self::dbSelectAccess();
            $SQL->from('t_income', array("C" => "COUNT(*)"));
            $SQL->where("FINANCE_DESCRIPTION='".$Id."'");    
            $resultRows = self::dbAccess()->fetchRow($SQL);
            $checkIncome=$resultRows?$resultRows->C:'';
            
            $SQL = self::dbSelectAccess();
            $SQL->from('t_expense', array("C" => "COUNT(*)"));
            $SQL->where("FINANCE_DESCRIPTION like '".$Id."'");    
            $result= self::dbAccess()->fetchRow($SQL);
            $checkExpense= $result?$result->C:'';
            if($checkIncome || $checkExpense){
                $check=1;        
            }
            
            return $check;      
        }

        public static function removeObject($params) {

            $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

            $facette = self::findObjectFromId($removeId);

            if ($facette) {
                if ($facette->OBJECT_TYPE == "FOLDER") {
                    if ($facette->PARENT) {
                        self::sqlRemoveObject($facette->PARENT);
                    }
                }
            }

            self::sqlRemoveObject($removeId);
            return array("success" => true);
        }

        public static function sqlRemoveObject($Id) {
            self::dbAccess()->delete("t_finance_description", " ID='" . $Id . "'");
        }

        public static function updateObject($params) {

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

            if ($objectId != "new") {
                $data = array();
                $data['NAME'] = "'". addText($params["NAME"]) ."'";
                if (isset($params["CHOOSE_TYPE"]))
                    $data['CHOOSE_TYPE'] = "'". addText($params["CHOOSE_TYPE"]) ."'";
                if (isset($params["LINK"]))
                    $data['LINK'] = "'". $params["LINK"] ."'";
                self::dbAccess()->update("t_finance_description", $data, "ID='". addText($params["objectId"]) ."'");
                self::updateChildren($objectId);
            }else {
                self::addObject($params);
            }
            return array("success" => true);
        }

        public static function sqlDescription($node, $type = false, $filter = false) {

            $SQL = "SELECT * FROM t_finance_description";
            $SQL .= " WHERE 1=1";
            if (!$node)
                $SQL .= " AND PARENT=0";
            else
                $SQL .= " AND PARENT=" . $node . "";

            if ($type)
                $SQL .= " AND CHOOSE_TYPE='" . $type . "'";

            if ($filter) {
                switch ($filter) {
                    case 1:
                        $SQL .= " AND LINK IN (1, 3)";
                        break;
                    case 2:
                        $SQL .= " AND LINK IN (2, 3)";
                        break;
                }
            }

            $SQL .= " ORDER BY SORTKEY ASC";
            //error_log($SQL);
            return self::dbAccess()->fetchAll($SQL);
        }

        public static function jsonTreeAllDescription($params) {

            $node = isset($params["node"]) ? addText($params["node"]) : 0;
            $filter = isset($params["filter"]) ? addText($params["filter"]) : 0;

            $data = array();
            $result = self::sqlDescription($node, false, $filter);

            if ($result) {
                $i = 0;
                foreach ($result as $value) {

                    $data[$i]['text'] = $value->NAME;
                    $data[$i]['id'] = $value->ID;

                    switch ($value->OBJECT_TYPE) {
                        case "FOLDER":
                            $data[$i]['leaf'] = false;
                            $data[$i]['disabled'] = false;
                            $data[$i]['cls'] = "nodeTextBold";
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            self::updateChildren($value->ID);
                            break;
                        case "ITEM":
                            $data[$i]['leaf'] = true;
                            $data[$i]['cls'] = "nodeTextBlue";
                            $data[$i]['iconCls'] = "icon-application_form_magnify";
                            break;
                    }
                    $i++;
                }
            }

            return $data;
        }

        public static function setUpdateAllChildren() {

            $result = self::sqlDescription(false, false, false);
            if ($result) {
                foreach ($result as $value) {
                    switch ($value->OBJECT_TYPE) {
                        case "FOLDER":
                            self::updateChildren($value->ID);
                            break;
                    }
                }
            }
        }

        public static function updateChildren($parentId) {

            $facette = self::findObjectFromId($parentId);

            $SQL = "SELECT * FROM t_finance_description";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND PARENT=" . $parentId . "";
            $result = self::dbAccess()->fetchAll($SQL);

            if ($result) {
                foreach ($result as $value) {
                    $data = array();
                    $data['CHOOSE_TYPE'] = "'". $facette->CHOOSE_TYPE ."'";
                    $data['LINK'] = "'". $facette->LINK ."'";
                    self::dbAccess()->update("t_finance_description", $data, "PARENT='". $value->PARENT ."'");
                }
            }
        }

        public static function checkChild($Id) {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_finance_description", array("C" => "COUNT(*)"));
            $SQL->where("PARENT = ?",$Id);
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }

        public function allFinancialDescriptionComboData($parent, $type = false) {

            $data = array();
            $result = self::sqlDescription($parent, $type, false);

            $data[0] = "[\"0\",\"[---]\"]";
            $i = 0;
            if ($result)
                foreach ($result as $value) {

                    $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                    $i++;
            }

            return "[" . implode(",", $data) . "]";
        }

    }

?>