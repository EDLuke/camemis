<?
    ///////////////////////////////////////////////////////////
    // @Chuy Thong Senior Software Developer
    // Date: 24.08.2012
    // 79Bz, Phnom Penh, Cambodia
    // 
    ///////////////////////////////////////////////////////////
    require_once("Zend/Loader.php");
    require_once 'include/Common.inc.php';
    switch (Zend_Registry::get('MODUL_API')) {
        case "dfe34ef0f0b812ea32d02866dbe9e3cb":
            require_once 'models/app_school/assignment/AssignmentTempDBAccess.php';
            require_once 'models/app_school/user/OrganizationDBAccess.php';
            require_once 'models/app_school/subject/SubjectDBAccess.php';
            break;
        case "dfe34ef0f0b812ea32d92866dbe9e3cb":
            require_once 'models/app_university/assignment/AssignmentTempDBAccess.php';
            require_once 'models/app_university/user/OrganizationDBAccess.php';
            require_once 'models/app_university/subject/SubjectDBAccess.php';
            break;
    }
    
    require_once 'clients/CamemisDynamicCombo.php';

    class CamemisControl {
        private $db = null;

        static function getInstance() {
            static $me;
            if ($me == null) 
                $me = new CamemisControl();
            return $me;
        }

        function __construct() {
            $this->db = Zend_Registry::get('ADMIN_DB_ACCESS');   
        }
        /*----------------------------------------------------------*/
        private static function getComboValues($object) {
            $SQL  = "SELECT `KEY` as ID, `VALUE` AS VALUE"; 
            $SQL .= " FROM t_combo_items WHERE OBJECT_NAME='". $object ."'";
            $SQL .= " ORDER BY ID";
            $result = self::getInstance()->db->fetchAll($SQL);
            $data = array();
            if ($result) {
                $i = 0;
                foreach ($result as $value) {
                    $data[$i++] = "['".$value->ID."','" . (defined($value->VALUE) ? constant($value->VALUE) : $value->VALUE) . "']";
                }
            }
            return "[" . implode(",", $data) . "]";   // [ [key1,value1],[key2,value2] ]
        }

        /*----------------------------------------------------------*/
        static function getCombo($object, $id, $fieldLabel, $store=false,$allowBlank=true,$readOnly=false) {

            $data = $store ? $store : self::getComboValues($object); 

            $js  = "";
            $js .= "xtype: 'combo'";
            $js .= ",id: '" . $id . "'";
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
            $js .= ",anchor: '95%'";
            $js .= ",mode: 'local'";
            $js .= ",editable:false";
            $js .= ",triggerAction: 'all'";

            if ($readOnly) {
                $js .= ",readOnly: true";
            }

            $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
            $js .= ",store: " . $data;
            $js .= ",name: '" . $object . "'";
            $js .= ",hiddenName: '" . $object . "'";        
            // $js .= ",width:" . ($width ? $width : 250);
            $js .= ",allowBlank: " . ($allowBlank ? "true" : "false");

            if ($fieldLabel == "")
                $js .= ",hideLabel: true";

            return $js;        
        }

        static function getDynCombo($object, $name, $module, $caption, $width, $cmd ) {
            $OBJ = new CamemisDynCombo($object, $module);
            $OBJ->objectTitle = $caption;
            $OBJ->allowBlank = "true";
            $OBJ->width = $width;
            $OBJ->varName = $name;
            $OBJ->setLoadParams("cmd: ". $cmd);
            //$OBJ->renderJS();
            return $OBJ;
        }

        static function Hidden($name, $value = false) {

            $js = "";
            $js .= "xtype: 'hidden'";
            $js .= ",id: '" . $name . "'";
            $js .= ",name: '" . $name . "'";
            if ($value)
                $js .= ",value: '" . $value . "'";

            return $js;
        }

        static function DateStartfield($fieldLabel, $daterange=false) {
            $js  = "";
            $js .= "xtype:'datefield'";
            $js .= ",id: 'START_DATE'";
            if ($daterange)
                $js .= ",vtype: 'daterange'";
            $js .= ",endDateField: 'END_DATE'";
            $js .= ",format: '" . setExtDatafieldFormat() . "'";
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
            $js .= ",name: 'START_DATE'";
            $js .= ",anchor: '95%'";
            $js .= ",allowBlank: true";
            $js .= ",value: '".showCurrentDBDate()."'";

            return $js;
        }
        
        static function DateEndfield($fieldLabel, $daterange=false) {
            $js  = "";
            $js .= "xtype:'datefield'";
            $js .= ",id: 'END_DATE'";
            if ($daterange)
                $js .= ",vtype: 'daterange'";
            $js .= ",startDateField: 'START_DATE'";
            $js .= ",format: '" . setExtDatafieldFormat() . "'";
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
            $js .= ",name: 'END_DATE'";
            $js .= ",anchor: '95%'";
            $js .= ",allowBlank: true";
            $js .= ",value: '".showCurrentDBDate()."'";

            return $js;
        }
        
        static function Datefield($name, $id, $fieldLabel, $allowBlank = false, $disabled = false) {
            $allowBlank = $allowBlank ? "false" : "true";
            $js = "";
            $js .= "xtype:'datefield'";
            $js .= ",id: '" . $id . "'";
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
            $js .= ",name: '" . $name . "'";
            if ($disabled) {
                $js .= ",readOnly : true";
            }
            $js .= ",format: '" . setExtDatafieldFormat() . "'";
            $js .= ",anchor: '95%'";
            $js .= ",allowBlank:" . $allowBlank . "";

            if ($fieldLabel == "")
                $js .= ",hideLabel: true";

            return $js;
        }

        static function Textfield($name, $fieldLabel, $allowBlank = false, $readOnly = false, $hidden = false) {
            $allowBlank = $allowBlank ? "false" : "true";
            $js = "";
            $js .= "xtype: 'textfield'";
            $js .= ",id: '" . $name . "_ID'";
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
            $js .= ",anchor: '95%'";
            $js .= ",name: '" . $name . "'";
            $js .= ",allowBlank: " . $allowBlank . "";

            if ($hidden) {
                $js .= ",hidden: true";
            }

            if ($fieldLabel == "")
                $js .= ",hideLabel: true";
            if ($readOnly)
                $js .= ",readOnly: true";

            return $js;
        }

        static function Trigger2($name, $fieldLabel, $onClick, $allowBlank = false) {
            $allowBlank = $allowBlank ? "false" : "true";
            $js = "id: '" . $name . "_ID',fieldLabel: '" . $fieldLabel . "',xtype: 'trigger',name: '" . $name . "',
            triggerClass: 'x-form-search-trigger',editable:false";
            $js .= ",anchor: '95%'";      
            $js .= "
            ,onTriggerClick: function() {
            " . $onClick . "
            } ";
            $js .= ",allowBlank: " . $allowBlank . "";
            $js .= ",hidden: false";

            return $js;
        }

        static function getOrganizationChartData()
        {
            $ORG_CHART = OrganizationDBAccess::getInstance();
            $result = $ORG_CHART->jsonTreeAllOrganizations(false);
            $data = array();
            foreach($result as $value) {
                $data[] = "['" . $value["id"] . "','" . $value['text'] . "']";    
            }
            return "[" . implode(",", $data) . "]";
        }

        static function getFeeCategoryData() {       
            $params["node"] = 1;
            $params["type"] = "SCHOOL";
            return BuildData::comboFeeCategory($params);           
        }


        static function getGradingSystemData($grading) {
            
            /*
            $params["start"] = 0;
            $params["limit"] = 50;
            $params["isActive"] = 1;
            $params["eduSystem"] = $grading;
            $jsondata = SpecialDBAccess::jsonSGradingSystem($params);
            $data = array();
            foreach ($jsondata["rows"] as $value) {
                $data[] = "['" . $value["ID"]. "','" . $value["DESCRIPTION"] . "']";
            }
            return "[" . implode(",", $data) . "]";
            */
        }
        
        
        static function getSubjectTraining($params=false) {
            $data = array();
            $OBJ_SUB = new SubjectDBAccess();
            $params['target'] = "TRAINING";
            $result = $OBJ_SUB->treeAllTrainingSubjects();
            $data[0] = "[0,'[---]']";
            $i = 0;
            if ($result)
                foreach ($result as $value) {
                    $data[$i + 1] = "['". $value['id'] ."','". $value['text'] ."']";
                    $i++;
                }
            return "[" . implode(",", $data) . "]";
        }

        static function getYesNoData() {
            return "[
            [0, '" . NO . "']
            ,[1, '" . YES . "']
            ]";       
        }

        static function getAgeData() {
            $store = "[['0', '[---]']";
            for ($i = 6; $i <= 30; $i++) {
                $store .= ",['" . $i . "', '" . $i . "']";
            }
            $store .= "]";
            return $store;
        }
    } 
?>