<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 10.10.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/BuildData.php";
require_once 'utiles/Utiles.php';
require_once setUserLoacalization();

class CamemisField {

    public $data = array();

    public function __construct() {

        //
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

    static function Colorfield($name, $fieldLabel, $readOnly = false) {

        $readOnly = $readOnly ? "true" : "false";
        $js = "";
        $js .= "xtype: 'colorfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:250";
        $js .= ",name: '" . $name . "'";
        $js .= ",readOnly: " . $readOnly . "";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function Trigger2($name, $fieldLabel, $onClick, $allowBlank = false, $width = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "id: '" . $name . "_ID',fieldLabel: '" . $fieldLabel . "',xtype: 'trigger',name: '" . $name . "',
            triggerClass: 'x-form-search-trigger',editable:false";

        if ($width) {
            $js .= ",width:" . $width;
        } else {
            $js .= ",anchor: '95%'";
        }

        $js .= "
            ,onTriggerClick: function() {
            " . $onClick . "
            } ";
        $js .= ",allowBlank: " . $allowBlank . "";
        $js .= ",hidden: false";

        return $js;
    }

    static function Trigger($name, $fieldLabel, $onClick, $allowBlank = false, $hidden = false, $readOnly = false, $width = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $hidden = $hidden ? "true" : "false";
        $js = "id: '" . $name . "_ID',fieldLabel: '" . $fieldLabel . "',xtype: 'trigger',name: '" . $name . "',";
        $js .="triggerClass: 'x-form-search-trigger',editable:false,";

        if ($width) {
            $js .="width:$width,";
        } else {
            $js .="width:250,";
        }

        $js .="onTriggerClick: function() {";
        $js .="" . $onClick . "";
        $js .="} ";
        $js .= ",allowBlank:" . $allowBlank . "";
        $js .= ",hidden:" . $hidden . "";
        if ($readOnly) {
            $js .= ",readOnly: true";
        }

        return $js;
    }

    static function Displayfield($name, $fieldLabel, $value = false, $hidden = false, $width = false) {

        $js = "";
        $js .= "xtype: 'displayfield'";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";

        if ($width) {
            $js .= ",width: '" . $width . "'";
        } else {
            $js .= ",width:250";
        }

        $js .= ",name: '" . $name . "'";
        $js .= ",value: '" . $value . "'";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        if ($hidden)
            $js .= ",hidden: true";

        return $js;
    }

    static function Loginname($name, $fieldLabel) {

        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",readOnly: true";
        $js .= ",width:250";
        $js .= ",name: '" . $name . "'";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function Password($name, $fieldLabel, $allowBlank = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",inputType: 'password'";
        $js .= ",width:250";
        $js .= ",allowBlank:" . $allowBlank . "";
        $js .= ",name: '" . $name . "'";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function EMailfield($name, $fieldLabel, $readOnly = false, $allowBlank = false) {

        $readOnly = $readOnly ? "true" : "false";

        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: 'user@example.com'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:250";
        $js .= ",name: '" . $name . "'";
        $js .= ",readOnly: " . $readOnly . "";

        if ($allowBlank) {
            $js .= ",allowBlank:false";
            $js .= ",regex: /^([\w\-\'\-]+)(\.[\w-\'\-]+)*@([\w\-]+\.){1,5}([A-Za-z]){2,4}$/";
        }

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function TextTimefield($name, $fieldLabel, $allowBlank = false, $value = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:250";
        $js .= ",name: '" . $name . "'";
        $js .= ",emptyText: 'HH:MM'";
        if ($value)
            $js .= ",value: '" . $value . "'";
        $js .= ",regex: /^[0-9]{2}:[0-9]{2}$/";
        $js .= ",allowBlank: " . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function Textfield($id, $name, $fieldLabel, $allowBlank = false, $readOnly = false, $hidden = false, $width = false, $value = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype: 'textfield'";
        //$js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",id: '" . $id . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        if ($width) {
            $js .= ",width:$width";
        } else {
            $js .= ",width:250";
        }

        $js .= ",name: '" . $name . "'";
        $js .= ",allowBlank: " . $allowBlank . "";

        if ($hidden) {
            $js .= ",hidden: true";
        }

        if ($value) {
            $js .= ",value: '" . $value . "'";
        }

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        if ($readOnly)
            $js .= ",readOnly: true";

        return $js;
    }

    static function Hiddenfield($name, $value) {
        $js = "";
        $js .= "xtype: 'hidden'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",name: '" . $name . "'";
        if ($value)
            $js .= ", value:'" . $value . "'";
        return $js;
    }

    static function Combo($name, $fieldLabel, $store, $readOnly = false, $value = false, $width = false, $hidden = false, $allowBlank = false) {

        if (!$store)
            $store = "[]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",lazyRender: true";
        $js .= ",resizable: true";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";

        if ($readOnly) {
            $js .= ",readOnly: true";
        } else {
            $js .= ",readOnly: false";
        }

        if ($hidden) {
            $js .= ",hidden: true";
        } else {
            $js .= ",hidden: false";
        }

        if ($value) {
            $js .= ",value: '" . $value . "'";
        }

        if ($width) {
            $js .= ",width: " . $width . "";
        } else {
            $js .= ",width:250";
        }

        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function Textarea($name, $fieldLabel, $height, $readOnly = false, $allowBlank = false, $hidden = false, $width = false) {

        $js = "";
        $js .= "xtype: 'textarea'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: ''";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        $js .= ",name: '" . $name . "'";

        if ($readOnly)
            $js .= ",readOnly: true";
        if ($allowBlank)
            $js .= ",allowBlank: false";
        $js .= ",height: " . $height . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        if ($readOnly)
            $js .= ",readOnly: true";

        if ($hidden) {
            $js .= ",hidden: true";
        }

        return $js;
    }

    static function ComboCampusBind($name, $fieldLabel, $bindToId, $hidden = false, $disabled = false) {
        $store = BuildData::comboCampus();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly: true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",displayField:'" . $name . "'";
        $js .= ",valueField:'" . $name . "_ID'";
        $js .= ",store: new Ext.data.SimpleStore({
            fields:['" . $name . "_ID', '" . $name . "']
            ,data:" . $store . "})";
        $js .= ",name: '" . $name . "'";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",hiddenName: '" . $name . "'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";
        $js .= ",listeners:{select:{fn:function(combo, value) {
            var comboGrade = Ext.getCmp('" . $bindToId . "');
            comboGrade.clearValue();
            comboGrade.store.filter('" . $name . "_ID', combo.getValue());
            }}
        }";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboFeeCategory($id, $name, $fieldLabel, $width = false) {
        $params["node"] = 1;
        $params["type"] = "SCHOOL";
        $store = BuildData::comboFeeCategory($params);

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $id . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboGradeBind($name, $fieldLabel, $bindFromId, $hidden = false, $disabled = false) {
        $store = BuildData::comboGradeByCampusId();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly: true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",displayField:'" . $name . "'";
        $js .= ",valueField:'" . $name . "_ID'";
        $js .= ",store: new Ext.data.SimpleStore({
            fields:['" . $name . "_ID', '" . $bindFromId . "', '" . $name . "']
            ,data:" . $store . "})";
        $js .= ",name: '" . $name . "'";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",hiddenName: '" . $name . "'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";
        $js .= ",lastQuery:''";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboYear($name, $fieldLabel, $startYear, $endYear, $hidden, $width = false) {

        if (!$startYear)
            $startYear = 2000;
        if (!$endYear)
            $endYear = date('Y') + 1;

        $store = "[[0, '[---]']";
        for ($i = $startYear; $i <= $endYear; $i++) {
            $store .= ",[" . $i . ", '" . $i . "']";
        }
        $store .= "]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",hiddenName: '" . $name . "'";

        if ($width) {
            $js .= ",width: " . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboAge($name, $fieldLabel, $hidden = false, $disabled = false, $width = false) {
        $store = "[[0, '[---]']";
        for ($i = 6; $i <= 30; $i++) {
            $store .= ",[" . $i . ", '" . $i . "']";
        }
        $store .= "]";
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly: true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",hiddenName: '" . $name . "'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboGender($fieldLabel = false, $hidden = false, $disabled = false, $width = false) {
        $store = "[[0, '[---]'],[2, '" . FEMALE . "'],[1, '" . MALE . "']]";
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'GENDER_ID'";
        $js .= ",fieldLabel: '" . GENDER . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly: true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'GENDER'";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",hiddenName: 'GENDER'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboUserType($fieldLabel = false, $hidden = false, $disabled = false, $width = false) {
        $store = "[[0, '[---]'],['STUDENT', '" . STUDENT . "'],['TEACHER', '" . TEACHER . "'],['STAFF', '" . STAFF . "']]";
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'RECIPIENT'";
        $js .= ",fieldLabel: '" . RECIPIENT . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly: true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'RECIPIENT'";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",hiddenName: 'RECIPIENT'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboYesNO($id, $name, $fieldLabel, $allowBlank = false, $disabled = false, $width = false) {

        $allowBlank = $allowBlank ? "false" : "true";
        $store = "[
            [0, '" . NO . "']
            ,[1, '" . YES . "']
            ]";
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $id . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";

        if ($disabled) {
            $js .= ",readOnly: true";
        }

        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        $js .= ",allowBlank: " . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboReleaseStatus($search = false, $hidden = false, $noNullValue = false, $width = false) {

        if ($search) {
            $store = "[
                ['---', '[---]'],
                ['ACTIVE', '" . ENABLED . "'],
                ['INACTIVE', '" . DISABLED . "']
                ]";
        } else {
            $store = "[
                [1, '" . DISABLED . "'],
                [0, '[---]']
                ]";
            if ($noNullValue) {
                $store = "[
                    ['---', '[---]'],
                    [1, '" . ENABLED . "'],
                    ['0', '" . DISABLED . "']
                    ]";
            }
        }

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'STATUS_ID'";
        $js .= ",fieldLabel: '" . STATUS . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'STATUS'";

        if ($hidden) {
            $js .= ",hidden:true";
        }

        if ($search)
            $js .= ",value: '---'";

        $js .= ",hiddenName: 'STATUS'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }


        return $js;
    }

    static function ComboLastSchoolyears($fieldLabel, $allowBlank = false) {

        $allowBlank = $allowBlank ? "false" : "true";

        $store = BuildData::comboLastSchoolyearData();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'LAST_SCHOOLYEAR_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'LAST_SCHOOLYEAR'";
        $js .= ",hiddenName: 'LAST_SCHOOLYEAR'";
        $js .= ",width:250";

        $js .= ",allowBlank:" . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSchoolYear($id, $name, $fieldLabel, $width = false) {
        $store = BuildData::comboAcademicData();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $id . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function Datefield($name, $fieldLabel, $allowBlank = false, $disabled = false, $width = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype:'datefield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        if ($disabled) {
            $js .= ",readOnly : true";
        }
        $js .= ",format: '" . setExtDatafieldFormat() . "'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:" . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function DatefieldDefaultToday($name, $fieldLabel, $allowBlank = false) {
        $Date = new Zend_Date();
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype:'datefield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",format: '" . setExtDatafieldFormat() . "'";
        $js .= ",width:250";
        $js .= ",value: '" . getShowDate($Date) . "'";
        $js .= ",allowBlank:" . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function StartDatefieldRange($name, $fieldLabel, $endDateField, $allowBlank = false, $setTodayValue = false, $readOnly = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype:'datefield'";
        $js .= ",id: '" . $name . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",format: '" . setExtDatafieldFormat() . "'";
        $js .= ",width:250";
        $js .= ",vtype: 'daterange'";
        if ($endDateField)
            $js .= ",endDateField:'" . $endDateField . "'";
        $js .= ",allowBlank:" . $allowBlank . "";
        if ($readOnly)
            $js .= ",readOnly:" . $readOnly . "";

        if ($setTodayValue) {
            $Date = new Zend_Date();
            $js .= ",value: '" . getShowDate($Date) . "'";
        }
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function EndDatefieldRange($name, $fieldLabel, $startDateField, $allowBlank = false, $readOnly = false, $disable = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype:'datefield'";
        $js .= ",id: '" . $name . "'";
        $js .= ",name: '" . $name . "'";
        if ($disable)
            $js .= ",disabled: 'true'";

        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",format: '" . setExtDatafieldFormat() . "'";
        $js .= ",width:250";
        $js .= ",vtype: 'daterange'";
        $js .= ",startDateField:'" . $startDateField . "'";
        $js .= ",allowBlank:" . $allowBlank . "";
        if ($readOnly)
            $js .= ",readOnly:" . $readOnly . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSubjectType($fieldLabel = false, $allowBlank = false, $readOnly = false) {

        $allowBlank = $allowBlank ? "false" : "true";

        $store = BuildData::comboDataSubjectType();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SUBJECT_TYPE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",editable:false";
        if ($readOnly) {
            $js .= ",readOnly: true";
        }
        $js .= ",name: 'SUBJECT_TYPE'";
        $js .= ",hiddenName: 'SUBJECT_TYPE'";
        $js .= ",width:250";
        $js .= ",allowBlank:" . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboJobType($fieldLabel, $allowBlank = true, $isRegistration = false, $width = false) {
        $allowBlank = $allowBlank ? "false" : "true";

        if ($isRegistration) {
            $store = "[
                [2, '" . PART_TIME_JOB . "'],
                [1, '" . FULL_TIME_JOB . "']
                ]";
        } else {
            $store = "[
                [0, '[---]'],
                [2, '" . PART_TIME_JOB . "'],
                [1, '" . FULL_TIME_JOB . "']
                ]";
        }

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'JOB_TYPE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'JOB_TYPE'";
        $js .= ",hiddenName: 'JOB_TYPE'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        $js .= ",allowBlank: " . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboUserRole($fieldLabel, $allowBlank = false, $hidden = false, $width = false) {

        $allowBlank = $allowBlank ? "false" : "true";

        $store = BuildData::comboDataUserRole(false, true);

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'USER_ROLE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",name: 'USER_ROLE'";
        $js .= ",hiddenName: 'USER_ROLE'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        $js .= ",allowBlank: " . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function HTML($name, $height = false, $readOnly = false) {

        $_readOnly = $readOnly ? "true" : "false";

        $js = "";
        $js .= "xtype:'htmleditor'";
        $js .= ",layout: 'form'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",name: '" . $name . "'";
        $js .= ",hideLabel: true";

        if ($readOnly) {
            $js .= ",listeners: {";
            $js .= "'initialize': function(f){";
            $js .= "this.setReadOnly(" . $_readOnly . ");";
            $js .= "}";
            $js .= "}";
        }

        $js .= ",enableColors: false";
        $js .= ",enableAlignments: false";
        $js .= ",enableFont: false";
        $js .= ",enableLinks: false";
        $js .= ",enableSourceEdit: false";

        if ($height)
            $js .=",height: " . $height . "";
        else
            $js .=",height: 300";

        $js .=",anchor: '100% -53'";
        return $js;
    }

    static function HTMLAdvantage($name, $width = false, $height = false, $readOnly = false) {

        $_readOnly = $readOnly ? "true" : "false";

        $js = "";
        $js .= "xtype:'htmleditor'";
        $js .= ",layout: 'form'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",name: '" . $name . "'";
        $js .= ",hideLabel: true";

        if ($readOnly) {
            $js .= ",listeners: {";
            $js .= "'initialize': function(f){";
            $js .= "this.setReadOnly(" . $_readOnly . ");";
            $js .= "}";
            $js .= "}";
        }

        $js .= ",enableColors: false";
        $js .= ",enableAlignments: true";
        $js .= ",enableFont: false";
        $js .= ",enableLinks: false";
        $js .= ",enableSourceEdit: false";

        if ($width)
            $js .=",width: " . $width . "";
        else
            $js .=",width: 530";
        if ($height)
            $js .=",height: " . $height . "";
        else
            $js .=",height: 250";

        return $js;
    }

    static function Numberfield($id, $name, $fieldLabel, $allowBlank = true, $value = false, $readOnly = false, $hidden = false, $emptyText = false, $width = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype: 'numberfield'";
        $js .= ",id: '" . $id . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",allowBlank: " . $allowBlank . "";
        if ($emptyText)
            $js .= ",emptyText: '" . $emptyText . "'";
        if ($value)
            $js .= ",value: " . $value . "";
        $js .= ",decimalPrecision : 3";
        if ($width) {
            $js .= ",width:$width";
        } else {
            $js .= ",width:250";
        }


        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        if ($readOnly)
            $js .= ",readOnly: " . $readOnly . "";

        if ($hidden)
            $js .= ",hidden: true";

        return $js;
    }

    static function Checkbox($id, $name, $fieldLabel, $value = false, $checked = false, $disabled = false, $hiden = false) {
        $js = "";
        $js .= "fieldLabel: ''";
        $js .= ",xtype: 'checkbox'";
        $js .= ",id: '" . $id . "'";
        $js .= ",boxLabel: '" . $fieldLabel . "'";
        if ($hiden)
            $js .= ",hidden:true";
        $js .= ",name: '" . $name . "'";
        $js .= ",hideLabel: true";
        if ($disabled) {
            $js .= ",disabled: true";
        }
        if ($value)
            $js .= ",value: '" . $value . "'";
        if ($checked)
            $js .= ",checked: true";

        return $js;
    }

    static function Radio($id, $name, $fieldLabel, $value, $checked = false, $disabled = false) {
        $js = "";
        $js .= "fieldLabel: ''";
        $js .= ",xtype: 'radio'";
        $js .= ",id: '" . $id . "'";
        $js .= ",boxLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",inputValue: '" . $value . "'";
        $js .= ",hideLabel: true";
        if ($disabled) {
            $js .= ",disabled: true";
        }
        if ($checked)
            $js .= ",checked: true";

        return $js;
    }

    static function Spinnerfield($name, $fieldLabel, $allowBlank = false, $value = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $minValue = $value ? 1 : 0;

        $js = "";
        $js .= "xtype: 'spinnerfield'";
        $js .= ",bodyStyle: 'padding:5px 5px 0'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",minValue: " . $minValue . "";
        $js .= ",maxValue: 100";
        $js .= ",width:250";
        $js .= "//,allowDecimals: true";
        $js .= "//,decimalPrecision: 1";
        $js .= "//,incrementValue: 0.4";
        $js .= "//,alternateIncrementValue: 2.1";
        $js .= ",allowBlank: " . $allowBlank . "";
        $js .= ",accelerate: true";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function ComboDuration($fieldLabel, $value = false, $readOnly = false) {
        $store = "[
            [1, '" . ONE_YEAR . "'],
            [2, '" . TWO_YEAR . "'],
            [3, '" . THREE_YEAR . "'],
            [4, '" . FOUR_YEAR . "'],
            [5, '" . FIVE_YEAR . "'],
            [6, '" . SIX_YEAR . "'],
            [7, '" . SEVEN_YEAR . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'DURATION_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        if ($readOnly)
            $js .= ",readOnly: true";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'DURATION'";
        $js .= ",hiddenName: 'DURATION'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";

        if ($value)
            $js .= ",value: '" . $value . "'";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboMarried($fieldLabel, $width = false) {
        $store = "[
            [1, '" . SINGLE . "'],
            [2, '" . MARRIED . "'],
            [3, '" . DIVORCED . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'MARRIED_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'MARRIED'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'MARRIED'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSendStatus($fieldLabel, $width = false) {
        $store = "[
            [1, '" . ALL_TEXT . "'],
            [2, '" . SENT . "'],
            [3, '" . NOT_SENT . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SENT_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SENT'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'SENT'";

        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSkin($fieldLabel) {
        $store = "[
            ['BLUE', '" . CAMEMIS_SKINS_BLUE . "']
            ,['GRAY', '" . CAMEMIS_SKINS_GRAY . "']
            ,['UBUNTU', '" . CAMEMIS_SKINS_UBUNTU . "']
            ,['SLATE', '" . CAMEMIS_SKINS_SLATE . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SKIN_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SKIN'";
        $js .= ",hiddenName: 'SKIN'";
        $js .= ",width:250";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    ///////////////////////////////////////////////////////
    // Subjects by Grade...
    ///////////////////////////////////////////////////////
    static function ComboSubjectByGrade($id, $name, $fieldLabel, $allowBlank, $hidden = false, $width = false) {
        $store = BuildData::comboDataSubjectsByGrade();
        $allowBlank = $allowBlank ? "false" : "true";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $id . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE_SUBJECT . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        $js .= ",allowBlank:" . $allowBlank . "";

        if ($hidden) {
            $js .= ",readOnly: true";
        }
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSubjectByTraining($id, $name, $fieldLabel, $allowBlank, $hidden = false) {

        $store = BuildData::comboDataSubjectsByTraining();
        $allowBlank = $allowBlank ? "false" : "true";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $id . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",editable:false";
        $js .= ",emptyText: '" . PLEASE_CHOOSE_SUBJECT . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        $js .= ",width:250";
        $js .= ",allowBlank:" . $allowBlank . "";

        if ($hidden) {
            $js .= ",hidden: true";
        }
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboRoom($id, $name, $fieldLabel, $allowBlank, $width = false) {
        $store = BuildData::comboDataRoom();
        $allowBlank = $allowBlank ? "false" : "true";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $id . "'";
        $js .= ",editable:false";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE_ROOM . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        $js .= ",allowBlank:" . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function UploadExcelField($name, $fieldLabel, $readOnly = false, $allowBlank = false) {
        $readOnly = $readOnly ? "true" : "false";
        if ($allowBlank) {
            $emptyText = "";
            $allowBlank = "false";
        } else {
            $emptyText = "";
            $allowBlank = "true";
        }

        $allowBlank = $allowBlank ? "false" : "true";

        $js = "";
        $js .= "xtype: 'fileuploadfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",buttonText: ''";
        $js .= ",buttonCfg: {";
        $js .= "iconCls: 'icon-page_white_excel'";
        $js .= "}";
        $js .= ",width:250";

        return $js;
    }

    static function Uploadfield($name, $fieldLabel) {

        $js = "";
        $js .= "xtype: 'fileuploadfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";

        if ($fieldLabel) {
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
        } else {
            $js .= ",hideLabel: true";
        }

        $js .= ",name: '" . $name . "'";
        $js .= ",buttonText: ''";
        $js .= ",buttonCfg: {";
        $js .= "iconCls: 'icon-image_add'";
        $js .= "}";
        $js .= ",allowBlank: false,width:250";

        return $js;
    }

    static function Box($Id, $fieldLabel, $value) {
        $js = "";
        $js .= "xtype:'box'";
        $js .= ",id: '" . $Id . "'";
        $js .= ",anchor:''";
        $js .= ",isFormField:true";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",autoEl:{";
        $js .= "tag:'div', children:[{";
        $js .= "tag:'div'";
        $js .= ",style:'margin:0 0 4px 0'";
        $js .= ",html: '" . htmlspecialchars($value) . "'";
        $js .= "}]";
        $js .= "}";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboCampus($fieldLabel, $allowBlank = true) {

        $allowBlank = $allowBlank ? "false" : "true";

        $store = BuildData::comboCampus();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'CAMPUS_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'CAMPUS'";
        $js .= ",hiddenName: 'CAMPUS'";
        $js .= ",width:250";
        $js .= ",allowBlank: false";
        $js .= ",allowBlank:" . $allowBlank . "";

        return $js;
    }

    static function ComboTrainingprograms($fieldLabel, $allowBlank = true) {

        $allowBlank = $allowBlank ? "false" : "true";

        $store = BuildData::comboTrainingprograms();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'PROGRAM_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PROGRAM'";
        $js .= ",hiddenName: 'PROGRAM'";
        $js .= ",width:250";
        $js .= ",allowBlank: false";
        $js .= ",allowBlank:" . $allowBlank . "";

        return $js;
    }

    static function ComboGrade($name, $fieldLabel, $allowBlank = false, $hidden = false, $width = false) {

        $allowBlank = $allowBlank ? "false" : "true";
        $hidden = $hidden ? "true" : "false";

        $store = BuildData::comboGrade();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";

        $js .= ",allowBlank:" . $allowBlank . "";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        $js .= ",hidden:" . $hidden . "";

        return $js;
    }

    static function Timefield($name, $fieldLabel) {

        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:250";
        $js .= ",name: '" . $name . "'";
        $js .= ",emptyText: 'HH:MM'";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function checkBoxSubject() {
        $checkBoxs = BuildData::checkboxDataSubjects();
        $js = $checkBoxs;
        return $js;
    }

    static function scheduleType($fieldLabel, $readOnly = false) {
        $store = "[
            [0, '[---]']
            ,[1, '" . TEACHING_EVENT . "']
            ,[2, '" . EVENT . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SCHEDULE_TYPE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SCHEDULE_TYPE'";
        $js .= ",hiddenName: 'SCHEDULE_TYPE'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";

        if ($readOnly) {
            $js .= ",readOnly: " . $readOnly . "";
        }

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function CampusSuperBox($fieldLabel, $value = false) {

        $store = BuildData::superboxCampus();

        $js = "";
        $js .= "xtype: 'superboxselect'";
        $js .= ",id: 'SUPERBOX_CAMPUS_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: new Ext.data.SimpleStore({fields: ['id', 'name'],data: " . $store . "})";
        $js .= ",displayField: 'name'";
        $js .= ",valueField: 'id'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";
        $js .= ",classField: 'cls'";
        $js .= ",styleField: 'style'";
        $js .= ",extraItemCls: 'x-flag'";
        $js .= ",extraItemStyle: 'border-width:2px'";
        $js .= ",stackItems: true";

        if ($value)
            $js .= ",value: '" . $value . "'";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboInfractionType($fieldLabel, $hidden = false, $width = false) {

        $store = BuildData::comboDataInfractionType();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'INFRACTION_TYPE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'INFRACTION_TYPE'";
        $js .= ",hiddenName: 'INFRACTION_TYPE'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";

        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboPriority($fieldLabel) {

        $store = "[
            [1, '" . NORMAL . "'],
            [2, '" . IMPORTANT . "']
            //[3, '" . WITH_CONFIRMATION . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'PRIORITY_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PRIORITY'";
        $js .= ",value: 1";
        $js .= ",hiddenName: 'PRIORITY'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSystemLanguage($fieldLabel, $hidden = false) {
        $store = "[
            ['ENGLISH', 'English'],
            ['BURMESE', '" . BURMESE . "'],
            //['CHINESE_TRADITIONAL', '" . CHINESE_TRADITIONAL . "'],
            ['CHINESE_SIMPLIFIED', '" . CHINESE_SIMPLIFIED . "'],
            ['FILIPINO', '" . FILIPINO . "'],
            ['FRANCE', '" . FRANCE . "'],
            ['GERMAN', '" . GERMAN . "'],
            ['KHMER', '" . KHMER . "'],
            ['INDONESIAN', '" . INDONESIAN . "'],
            ['LAO', '" . LAO . "'],
            //['SPANISH', '" . SPANISH . "'],
            ['THAI', '" . THAI . "'],
            ['VIETNAMESE', '" . VIETNAMESE . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'LANGUAGE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";

        if ($hidden) {
            $js .= ",hidden: true";
        }

        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SYSTEM_LANGUAGE'";
        $js .= ",hiddenName: 'SYSTEM_LANGUAGE'";
        $js .= ",width:250";

        //$js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function comboTwoTerms($allowBlank = false, $hidden = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $hidden = $hidden ? "true" : "false";
        $store = "[
            ['', '[---]']
            ,['FIRST_SEMESTER', '" . FIRST_SEMESTER . "']
            ,['SECOND_SEMESTER', '" . SECOND_SEMESTER . "']

            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'TERM_ID'";
        $js .= ",fieldLabel: '" . TERM . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'TERM'";
        $js .= ",hiddenName: 'TERM'";
        $js .= ",width:250";
        $js .= ",allowBlank:" . $allowBlank . "";
        $js .= ",hidden:" . $hidden . "";

        return $js;
    }

    static function comboReligion($disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllReligion();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'RELIGION'";
        $js .= ",fieldLabel: '" . RELIGION . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }
        if ($hidden)
            $js .= ",hidden: true";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'RELIGION'";
        $js .= ",hiddenName: 'RELIGION'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        return $js;
    }

    static function comboTutors($allowBlank = false, $width = false) {

        $allowBlank = $allowBlank ? "false" : "true";
        $store = BuildData::comboDataAllTeacher();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'TEACHER_ID'";
        $js .= ",fieldLabel: '" . TEACHER . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'TEACHER'";
        $js .= ",hiddenName: 'TEACHER'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        $js .= ",allowBlank: " . $allowBlank . "";

        return $js;
    }

    static function comboNationality($disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllNationality();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'NATIONALITY'";
        $js .= ",fieldLabel: '" . NATIONALITY . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }

        if ($hidden) {
            $js .= ",hidden:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'NATIONALITY'";
        $js .= ",hiddenName: 'NATIONALITY'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        return $js;
    }
    ////
    static function comboMajor($disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllMajor();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'MAJOR'";
        $js .= ",fieldLabel: '" . MAJOR . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }

        if ($hidden) {
            $js .= ",hidden:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'MAJOR'";
        $js .= ",hiddenName: 'MAJOR'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        return $js;
    }
    
     static function comboQualitycationDegree($disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllQualitycationDegree();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'QUALIFICATION_DEGREE'";
        $js .= ",fieldLabel: '" . QUALIFICATION_DEGREE . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }

        if ($hidden) {
            $js .= ",hidden:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'QUALIFICATION_DEGREE'";
        $js .= ",hiddenName: 'QUALIFICATION_DEGREE'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        return $js;
    }
    ////

    static function comboEthnic($disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllEthnic();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'ETHNIC'";
        $js .= ",fieldLabel: '" . ETHNIC_GROUPS . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }

        if ($hidden) {
            $js .= ",hidden:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ETHNIC'";
        $js .= ",hiddenName: 'ETHNIC'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        return $js;
    }

    static function comboOrganization($hidden = false, $width = false) {

        $store = BuildData::comboDataAllOrganization();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'ORGANIZATION'";
        $js .= ",fieldLabel: '" . ORGANIZATION_CHART . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";

        if ($hidden) {
            $js .= ",hidden: true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ORGANIZATION'";
        $js .= ",hiddenName: 'ORGANIZATION'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        return $js;
    }

    static function comboUserAcivity() {

        $store = "[
            ['','[---]']
            ,['CREATE','" . setICONV(CREATE) . "']
            ,['UPDATE','" . setICONV(UPDATE) . "']
            ,['REMOVE','" . setICONV(REMOVE) . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'COMBO_USER_ACTIVITY'";
        $js .= ",fieldLabel: '" . ACTIVITY . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ACTIVITY'";
        $js .= ",hiddenName: 'ACTIVITY'";
        $js .= ",width:250";
        $js .= ",allowBlank: false";

        return $js;
    }

    static function ComboSMSPriority($type, $width = false) {

        switch ($type) {
            case "SEARCH_STUDENT":
                $store = "[
                    ['', '" . ALL_TEXT . "']
                    ,[0, '" . INFORMATION . "']
                    ,[1, '" . IMPORTANT . "']
                    ,[2, '" . URGENT . "']
                    ,[3, 'SOS']
                    ]";
                break;
            case "SEARCH_STAFF":
                $store = "[
                    ['', '" . ALL_TEXT . "']
                    ,[0, '" . INFORMATION . "']
                    ,[1, '" . IMPORTANT . "']
                    ,[2, '" . URGENT . "']
                    ]";
                break;
            case "STUDENT":
                $store = "[
                    [0, '" . INFORMATION . "']
                    ,[1, '" . IMPORTANT . "']
                    ,[2, '" . URGENT . "']
                    ,[3, 'SOS']
                    ]";
                break;
            case "STAFF":
                $store = "[
                    [0, '" . INFORMATION . "']
                    ,[1, '" . IMPORTANT . "']
                    ,[2, '" . URGENT . "']
                    ]";
                break;
        }

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . PRIORITY . "'";
        $js .= ",id: 'ID_SMS_PRIORITY'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",value: 0";
        $js .= ",name: 'PRIORITY'";
        $js .= ",hiddenName: 'PRIORITY'";
        if ($width)
            $js .= ",width:" . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        return $js;
    }

    static function ComboCurrency() {
        $store = "[
            [0, '[---]']
            ,['KHR', '" . CAMBODIA_RIEL . "']
            ,['IDR', '" . INDONESIA_RUPIAH . "']
            ,['EUR', '" . EURO_MEMBER_CONTRIES . "']
            ,['LAK', '" . LAOS_KIP . "']
            ,['MYR', '" . MALAYSIA_RINGGIT . "']
            ,['PHP', '" . PHILIPPINES_PESO . "']
            ,['THB', '" . THAILAND_BATH . "']
            ,['USD', '" . UNITED_STATES_DOLLAR . "']
            ,['VND', '" . VIETN_NAM_DONG . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . CURRENCY . "'";
        $js .= ",id: 'CURRENCY_ID'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'CURRENCY'";
        $js .= ",hiddenName: 'CURRENCY'";
        $js .= ",width:250";
        $js .= ",allowBlank:true";

        return $js;
    }

    static function ComboPaymentMethod($width = false) {
        $store = "[
            ['CASH_IN_HAND', '" . CASH_IN_HAND . "']
            ,['CREDIT_CARD_BANK_CARD', '" . CREDIT_CARD_BANK_CARD . "']
            ,['CHEQUE', '" . CHEQUE . "']
            ,['EFT', '" . EFT . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . PAYMENT_METHOD . "'";
        $js .= ",id: 'PAYMENT_METHOD_ID'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PAYMENT_METHOD'";
        $js .= ",hiddenName: 'PAYMENT_METHOD'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:false";

        return $js;
    }

    static function ComboAmountOption($width = false, $readOnly = false) {
        $store = "[
            ['1', '" . FIRST_OPTION . "']
            ,['2', '" . SECOND_OPTION . "']
            ,['3', '" . THIRD_OPTION . "']
            ,['4', '" . FOURTH_OPTION . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . AMOUNT_OPTION . "'";
        $js .= ",id: 'AMOUNT_OPTION'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        if ($readOnly)
            $js .= ",readOnly: true";
        $js .= ",name: 'AMOUNT_OPTION'";
        $js .= ",hiddenName: 'AMOUNT_OPTION'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:false";

        return $js;
    }

    static function ComboFeeBalanceType($width = false) {
        $store = "[
            ['1', '" . COMPLETELY . "']
            ,['2', '" . HALF_PAY . "']
            ,['3', '" . PARTIALLY . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . PAID_STATUS . "'";
        $js .= ",id: 'FEE_BALANCETYPE_ID'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'FEE_BALANCETYPE'";
        $js .= ",hiddenName: 'FEE_BALANCETYPE'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:false";

        return $js;
    }

    static function ComboPaymentOption($index, $value = false, $readOnly = false) {

        switch ($index) {
            case 1:
                $store = "[
                    ['1', '" . FIRST_OPTION . "']
                    ]";
                break;
            case 2:
                $store = "[
                    ['1', '" . FIRST_OPTION . "']
                    ,['2', '" . SECOND_OPTION . "']
                    ]";
                break;
            case 3:
                $store = "[
                    ['1', '" . FIRST_OPTION . "']
                    ,['2', '" . SECOND_OPTION . "']
                    ,['3', '" . THIRD_OPTION . "']
                    ]";
                break;
            case 4:
                $store = "[
                    ['1', '" . FIRST_OPTION . "']
                    ,['2', '" . SECOND_OPTION . "']
                    ,['3', '" . THIRD_OPTION . "']
                    ,['4', '" . FOURTH_OPTION . "']
                    ]";
                break;
        }

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . PAYMENT_OPTION . "'";
        $js .= ",id: 'PAYMENT_OPTION'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PAYMENT_OPTION'";
        $js .= ",hiddenName: 'PAYMENT_OPTION'";

        if ($value)
            $js .= ",value: '" . $value . "'";
        if ($readOnly)
            $js .= ",readOnly: true";

        $js .= ",width:250";
        $js .= ",allowBlank:false";

        return $js;
    }

    static function ComboAcademicType($fieldLabel, $value = false, $width = false) {

        $store = "[
            [0, '[---]'],
            [1, '" . FULL_TIME_ACADEMIC . "'],
            [2, '" . PART_TIME_ACADEMIC . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'ACADEMIC_TYPE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ACADEMIC_TYPE'";
        $js .= ",hiddenName: 'ACADEMIC_TYPE'";
        if ($width)
            $js .= ",width:" . $width;
        else
            $js .= ",width:250";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        if ($value == "clear") {
            $js .= "";
        } else {
            $js .= ",value: 2";
        }

        return $js;
    }

    static function ComboAddUserRole($fieldLabel, $width = false) {
        $store = "[
            [0, '[---]']
            ,[1, '" . INSTRUCTOR . "']
            ,[2, '" . TEACHER . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'ADDITIONAL_ROLE_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ADDITIONAL_ROLE'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'ADDITIONAL_ROLE'";
        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSendSMSIntervall($allowBlank = false) {
        $store = "[
            ['0', '" . NO . "']
            ,['1', '" . YES . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: 'SMS - " . SEND . "'";
        $js .= ",id: 'SMS_SEND'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SMS_SEND'";
        $js .= ",hiddenName: 'SMS_SEND'";
        $js .= ",width:250";
        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }

        return $js;
    }

    static function ComboDay($hideLabel = false) {
        $store = "[
            ['0', '[---]']
            ,['MO', '" . MONDAY . "']
            ,['TU', '" . TUESDAY . "']
            ,['WE', '" . WEDNESDAY . "']
            ,['TH', '" . THURSDAY . "']
            ,['FR', '" . FRIDAY . "']
            ,['SA', '" . SATURDAY . "']
            ,['SU', '" . SUNDAY . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'DAY_ID'";
        $js .= ",fieldLabel: '" . DAY . "'";
        if ($hideLabel)
            $js .= ",hideLabel: true";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SHORTDAY'";
        $js .= ",hiddenName: 'SHORTDAY'";
        $js .= ",width:250";

        return $js;
    }

    static function ComboBulletinType() {

        $store = "[['0', '[---]']]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'BULLETIN_TYPE'";
        $js .= ",fieldLabel: '" . TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",editable:false";
        $js .= ",readOnly: true";
        $js .= ",name: 'BULLETIN_TYPE'";
        $js .= ",hiddenName: 'BULLETIN_TYPE'";
        $js .= ",width:250";

        return $js;
    }

    static function comboMathematicalOperation($value = false) {

        $store = "[
            ['0', '[---]']
            ,['1', '" . MULIPLICATION . "']
            ,['2', '" . ADDITION . "']
            ,['3', '" . SUBSTRACTION . "']
            ,['4', '" . DISTRIBUTION . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'FORMULA'";
        $js .= ",fieldLabel: '" . MATHEMATICAL_OPERATION . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        if ($value)
            $js .= ",value: '" . $value . "'";
        $js .= ",editable:false";
        $js .= ",name: 'FORMULA_TYPE'";
        $js .= ",hiddenName: 'FORMULA_TYPE'";
        $js .= ",width:250";

        return $js;
    }

    static function comboScoreType($value = false, $readOnly = false) {

        $store = "[
            ['1', '" . SCORE_ON_NUMBER . "']
            ,['2', '" . SCORE_ON_ALPHABET . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SCORE_TYPE'";
        $js .= ",fieldLabel: '" . SCORE_TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",editable:false";
        if ($readOnly)
            $js .= ",readOnly: true";
        if ($value)
            $js .= ",value: '" . $value . "'";
        $js .= ",name: 'SCORE_TYPE'";
        $js .= ",hiddenName: 'SCORE_TYPE'";
        $js .= ",width:250";

        return $js;
    }

    static function ComboFacilityType() {

        $store = "[
            ['', '[---]']
            ,['ROOM', '" . ROOM_FACILITIES . "']
            ,['SCHOOL', '" . SCHOOL_FACILITIES . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'FACILITY_TYPE'";
        $js .= ",fieldLabel: '" . TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",editable:false";
        $js .= ",name: 'FACILITY_TYPE'";
        $js .= ",hiddenName: 'FACILITY_TYPE'";
        $js .= ",width:250";

        return $js;
    }

    static function comboPersonalDescription($fieldLabel, $parent, $personType, $type = false, $disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllPersonalDescription($parent, $personType, $type);

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $fieldLabel . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }

        if ($hidden) {
            $js .= ",hidden:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $fieldLabel . "'";
        $js .= ",hiddenName: '" . $fieldLabel . "'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        return $js;
    }

    static function ComboAcademicSystemTypeHightEducation($value = false, $width = false, $readOnly = false) {

        $PENEL_ITEMS = Array();
        $GENERAL_EDUCATION = "['GENERAL', '" . HIGHT_EDUCATION . "']";
        if (UserAuth::displayRoleGeneralEducation())
            $PENEL_ITEMS[] = $GENERAL_EDUCATION;

        $TRAINING_PROGRAMS = "['TRAINING', '" . TRAINING_PROGRAMS . "']";
        if (UserAuth::displayRoleTrainingEducation())
            $PENEL_ITEMS[] = $TRAINING_PROGRAMS;

        $CHOOSE_ITEMS = "[" . implode(',', $PENEL_ITEMS) . "]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'EDUCATION_TYPE_ID'";
        $js .= ",fieldLabel: '" . EDUCATION_TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $CHOOSE_ITEMS . "";
        $js .= ",name: 'EDUCATION_TYPE'";

        if ($value) {
            $js .= ",value: '" . $value . "'";
        } else {
            $js .= ",value: '[---]'";
        }

        if ($readOnly) {
            $js .= ",readOnly:true";
        }
        $js .= ",hiddenName: 'EDUCATION_TYPE'";

        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:false";

        return $js;
    }

    static function ComboAcademicSystemType($value = false, $width = false, $readOnly = false) {

        $PENEL_ITEMS = Array();
        $GENERAL_EDUCATION = "['GENERAL', '" . TRADITIONAL_EDUCATION_SYSTEM . "']";
        $CREDIT_EDUCATION = "['CREDIT','" . CREDIT_EDUCATION_SYSTEM . "']";   //@veasna
        if (UserAuth::displayRoleGeneralEducation()) {  //@veasna
            if (UserAuth::displayTraditionalEducationSystem()) //@veasna
                $PENEL_ITEMS[] = $GENERAL_EDUCATION;      //@veasna
            if (UserAuth::displayCreditEducationSystem())   //@veasna
                $PENEL_ITEMS[] = $CREDIT_EDUCATION;   //@veasna
        }

        $TRAINING_PROGRAMS = "['TRAINING', '" . TRAINING_PROGRAMS . "']";
        if (UserAuth::displayRoleTrainingEducation())
            $PENEL_ITEMS[] = $TRAINING_PROGRAMS;

        $CHOOSE_ITEMS = "[" . implode(',', $PENEL_ITEMS) . "]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'EDUCATION_TYPE_ID'";
        $js .= ",fieldLabel: '" . EDUCATION_TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $CHOOSE_ITEMS . "";
        $js .= ",name: 'EDUCATION_TYPE'";

        if ($value) {
            $js .= ",value: '" . $value . "'";
        } else {
            $js .= ",value: '[---]'";
        }

        if ($readOnly) {
            $js .= ",readOnly:true";
        }
        $js .= ",hiddenName: 'EDUCATION_TYPE'";

        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:false";

        return $js;
    }

    static function ComboFormularTax($fieldLabel, $width = false) {
        $store = "[
            [1, '" . ADDITION . "']
            ,[2, '" . SUBSTRACTION . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'FORMULAR_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'FORMULAR'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'FORMULAR'";
        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboPaymentStatus($width = false) {
        $store = "[
            ['', '[---]']
            ,['PAID', '" . PAID . "']
            ,['PARTLY_PAID', '" . PARTLY_PAID . "']
            ,['NOT_YET_PAID', '" . NOT_YET_PAID . "']
            ,['PRE_PAY', '" . PRE_PAY . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'PAYMENT_STATUS'";
        $js .= ",fieldLabel: '" . PAYMENT_STATUS . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PAYMENT_STATUS'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'PAYMENT_STATUS'";
        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        return $js;
    }

    static function ComboSalary($width = false) {
        $store = "[
            ['', '[---]']
            ,['PAYMENT_FOR_SALARY', '" . PAYMENT_FOR_SALARY . "']
            ,['PAYMENT_FOR_TEACHING_SESSION', '" . PAYMENT_FOR_TEACHING_SESSION . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'PAMENT_TYPE'";
        $js .= ",fieldLabel: '" . TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PAYMENT_TYPE'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'PAYMENT_TYPE'";
        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        return $js;
    }

    static function ComboAttendanceIsUsed($width = false, $readOnly = false) {
        $store = "[
            ['0', '" . NO . "']
            ,['1', '" . YES . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'ATTENDANCE_IS_USED'";
        $js .= ",fieldLabel: 'ATTENDANCE_IS_USED'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ATTENDANCE_IS_USED'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'ATTENDANCE_IS_USED'";
        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        if ($readOnly) {
            $js .= ",readOnly:true";
        }

        return $js;
    }

    static function ComboQualificationType($allowBlank, $width = false, $readOnly = false) {

        $store = BuildData::comboDataQualificationType();

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'QUALIFICATION_TYPE'";
        $js .= ",fieldLabel: '" . QUALIFICATION_TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'QUALIFICATION_TYPE'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'QUALIFICATION_TYPE'";

        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        if ($readOnly) {
            $js .= ",readOnly:true";
        }
        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }
        return $js;
    }

    static function ComboEducationSystem($width = false, $readOnly = false, $value = false) {

        $store = "[
            ['0', '" . TRADITIONAL . "']
            ,['1', '" . CREDIT . "']
            ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'EDUCATION_SYSTEM'";
        $js .= ",fieldLabel: '" . EDUCATION_SYSTEM . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'EDUCATION_SYSTEM'";
        $js .= ",editable:false";
        $js .= ",hiddenName: 'EDUCATION_SYSTEM'";
        if ($width)
            $js .= ",width: " . $width . "";
        else
            $js .= ",width:250";
        $js .= ",allowBlank:true";

        if ($readOnly) {
            $js .= ",readOnly:true";
        }

        if ($value == 0) {
            $js .= ",value:'0'";
        } else {
            $js .= ",value:'" . $value . "'";
        }

        return $js;
    }

    static function comboAbsentTypes($objectType, $readOnly = false, $width = false, $allowBlank = false) {

        $store = BuildData::comboAbsentType($objectType);

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'ABSENT_TYPE'";
        $js .= ",fieldLabel: '" . ATTENDANCE_TYPE . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";

        if ($readOnly) {
            $js .= ",readOnly:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'ABSENT_TYPE'";
        $js .= ",hiddenName: 'ABSENT_TYPE'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }

        return $js;
    }

    //@THORN Visal
    static function comboAcademicClasses($academicId, $schoolyearId, $teacherId, $label, $readOnly = false, $width = true, $allowBlank = false) {

        $store = BuildData::comboAcademicClasses(array('academicId' => $academicId, 'schoolyearId' => $schoolyearId, 'teacherId' => $teacherId));

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $label . "'";
        $js .= ",fieldLabel: '" . $label . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";

        if ($readOnly) {
            $js .= ",readOnly:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $label . "'";
        $js .= ",hiddenName: '" . $label . "'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }

        return $js;
    }

    //@Sea Peng
    static function comboCamemisTypes($objectType, $label, $readOnly = false, $width = false, $allowBlank = false) {

        $store = BuildData::comboCamemisType($objectType);

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $objectType . "'";
        $js .= ",fieldLabel: '" . $label . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";

        if ($readOnly) {
            $js .= ",readOnly:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $objectType . "'";
        $js .= ",hiddenName: '" . $objectType . "'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }

        return $js;
    }

    static function ComboPunishmentType() {

        $store = BuildData::comboCamemisType('PUNISHMENT_TYPE');
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",fieldLabel: '" . PUNISHMENT_TYPE . "'";
        $js .= ",id: 'TYPE'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'PUNISHMENT_TYPE'";
        $js .= ",hiddenName: 'PUNISHMENT_TYPE'";
        $js .= ",width:250";
        $js .= ",allowBlank:false";
        if (UserAuth::getUserType() == "STUDENT") //@Math Man 23.01.2014
            $js .= ",readOnly:true";
        return $js;
    }

    //

    static function comboTimezone($width = false, $allowBlank = false) {

        $data = array(
            '(UTC-11:00)-1' => 'Pacific/Midway (UTC-11:00)',
            '(UTC-11:00)-2' => 'Pacific/Samoa (UTC-11:00)',
            '(UTC-10:00)-3' => 'Pacific/Honolulu (UTC-10:00)',
            '(UTC-09:00)-4' => 'US/Alaska (UTC-09:00)',
            '(UTC-08:00)-5' => 'America/Los Angeles (UTC-08:00)',
            '(UTC-08:00)-6' => 'America/Tijuana (UTC-08:00)',
            '(UTC-07:00)-7' => 'US/Arizona (UTC-07:00)',
            '(UTC-07:00)-8' => 'America/Chihuahua (UTC-07:00)',
            '(UTC-07:00)-9' => 'America/La Paz (UTC-07:00)',
            '(UTC-07:00)-10' => 'America/Mazatlan (UTC-07:00)',
            '(UTC-07:00)-11' => 'US/Mountain Time (US &amp; Canada) (UTC-07:00)',
            '(UTC-06:00)-12' => 'America/Central America (UTC-06:00)',
            '(UTC-06:00)-13' => 'US/Central Time (US &amp; Canada) (UTC-06:00)',
            '(UTC-06:00)-14' => 'America/Guadalajara (UTC-06:00)',
            '(UTC-06:00)-15' => 'America/Mexico City (UTC-06:00)',
            '(UTC-06:00)-16' => 'America/Monterrey (UTC-06:00)',
            '(UTC-06:00)-17' => 'Canada/Saskatchewan (UTC-06:00)',
            '(UTC-05:00)-18' => 'America/Bogota (UTC-05:00)',
            '(UTC-05:00)-19' => 'US/Eastern Time (US &amp; Canada) (UTC-05:00)',
            '(UTC-05:00)-20' => 'US/EastIndiana (East)Indiana (UTC-05:00)',
            '(UTC-05:00)-21' => 'America/Lima (UTC-05:00)',
            '(UTC-05:00)-22' => 'America/Quito (UTC-05:00)',
            '(UTC-04:00)-23' => 'Canada/Atlantic (UTC-04:00)',
            '(UTC-04:30)-24' => 'America/Caracas (UTC-04:30)',
            '(UTC-04:00)-25' => 'America/La Paz (UTC-04:00)',
            '(UTC-04:00)-26' => 'America/Santiago (UTC-04:00)',
            '(UTC-03:30)-27' => 'Canada/Newfoundland (UTC-03:30)',
            '(UTC-03:00)-28' => 'America/Brasilia (UTC-03:00)',
            '(UTC-03:00)-29' => 'America/Argentina/Buenos Aires (UTC-03:00)',
            '(UTC-03:00)-30' => 'America/Argentina/Georgetown (UTC-03:00)',
            '(UTC-03:00)-31' => 'America/Greenland (UTC-03:00)',
            '(UTC-02:00)-32' => 'America/Mid-Atlantic (UTC-02:00)',
            '(UTC-01:00)-33' => 'Atlantic/Azores (UTC-01:00)',
            '(UTC-01:00)-34' => 'Atlantic/Cape Verde Is. (UTC-01:00)',
            '(UTC+00:00)-35' => 'Africa/Casablanca (UTC+00:00)',
            '(UTC+00:00)-36' => 'Europe/Edinburgh (UTC+00:00)',
            '(UTC+00:00)-37' => 'Etc/Greenwich Mean Time : Dublin (UTC+00:00)',
            '(UTC+00:00)-38' => 'Europe/Lisbon (UTC+00:00)',
            '(UTC+00:00)-39' => 'Europe/London (UTC+00:00)',
            '(UTC+00:00)-40' => 'Africa/Monrovia (UTC+00:00)',
            '(UTC+00:00)-41' => 'UTC (UTC+00:00)',
            '(UTC+01:00)-42' => 'Europe/Amsterdam (UTC+01:00)',
            '(UTC+01:00)-43' => 'Europe/Belgrade (UTC+01:00)',
            '(UTC+01:00)-44' => 'Europe/Berlin (UTC+01:00)',
            '(UTC+01:00)-45' => 'Europe/Bern (UTC+01:00)',
            '(UTC+01:00)-46' => 'Europe/Bratislava (UTC+01:00)',
            '(UTC+01:00)-47' => 'Europe/Brussels (UTC+01:00)',
            '(UTC+01:00)-48' => 'Europe/Budapest (UTC+01:00)',
            '(UTC+01:00)-49' => 'Europe/Copenhagen (UTC+01:00)',
            '(UTC+01:00)-50' => 'Europe/Ljubljana (UTC+01:00)',
            '(UTC+01:00)-51' => 'Europe/Madrid (UTC+01:00)',
            '(UTC+01:00)-52' => 'Europe/Paris (UTC+01:00)',
            '(UTC+01:00)-53' => 'Europe/Prague (UTC+01:00)',
            '(UTC+01:00)-54' => 'Europe/Rome (UTC+01:00)',
            '(UTC+01:00)-55' => 'Europe/Sarajevo (UTC+01:00)',
            '(UTC+01:00)-56' => 'Europe/Skopje (UTC+01:00)',
            '(UTC+01:00)-57' => 'Europe/Stockholm (UTC+01:00)',
            '(UTC+01:00)-58' => 'Europe/Vienna (UTC+01:00)',
            '(UTC+01:00)-59' => 'Europe/Warsaw (UTC+01:00)',
            '(UTC+01:00)-60' => 'Africa/West Central Africa (UTC+01:00)',
            '(UTC+01:00)-60' => 'Europe/Zagreb (UTC+01:00)',
            '(UTC+02:00)-61' => 'Europe/Athens (UTC+02:00)',
            '(UTC+02:00)-62' => 'Europe/Bucharest (UTC+02:00)',
            '(UTC+02:00)-63' => 'Africa/Cairo (UTC+02:00)',
            '(UTC+02:00)-64' => 'Africa/Harare (UTC+02:00)',
            '(UTC+02:00)-65' => 'Europe/Helsinki (UTC+02:00)',
            '(UTC+02:00)-66' => 'Europe/Istanbul (UTC+02:00)',
            '(UTC+02:00)-67' => 'Asia/Jerusalem (UTC+02:00)',
            '(UTC+02:00)-68' => 'Europe/Kyiv (UTC+02:00)',
            '(UTC+02:00)-69' => 'Africa/Pretoria (UTC+02:00)',
            '(UTC+02:00)-70' => 'Europe/Riga (UTC+02:00)',
            '(UTC+02:00)-71' => 'Europe/Sofia (UTC+02:00)',
            '(UTC+02:00)-72' => 'Europe/Tallinn (UTC+02:00)',
            '(UTC+02:00)-73' => 'Europe/Vilnius (UTC+02:00)',
            '(UTC+03:00)-74' => 'Asia/Baghdad (UTC+03:00)',
            '(UTC+03:00)-75' => 'Asia/Kuwait (UTC+03:00)',
            '(UTC+03:00)-76' => 'Europe/Minsk (UTC+03:00)',
            '(UTC+03:00)-77' => 'Africa/Nairobi (UTC+03:00)',
            '(UTC+03:00)-78' => 'Asia/Riyadh (UTC+03:00)',
            '(UTC+03:00)-79' => 'Europe/Volgograd (UTC+03:00)',
            '(UTC+03:30)-80' => 'Asia/Tehran (UTC+03:30)',
            '(UTC+04:00)-81' => 'Asia/Abu Dhabi (UTC+04:00)',
            '(UTC+04:00)-82' => 'Asia/Baku (UTC+04:00)',
            '(UTC+04:00)-83' => 'Europe/Moscow (UTC+04:00)',
            '(UTC+04:00)-84' => 'Asia/Muscat (UTC+04:00)',
            '(UTC+04:00)-85' => 'Europe/St. Petersburg (UTC+04:00)',
            '(UTC+04:00)-86' => 'Asia/Tbilisi (UTC+04:00)',
            '(UTC+04:00)-87' => 'Asia/Yerevan (UTC+04:00)',
            '(UTC+04:30)-88' => 'Asia/Kabul (UTC+04:30)',
            '(UTC+05:00)-89' => 'Asia/Islamabad (UTC+05:00)',
            '(UTC+05:00)-90' => 'Asia/Karachi (UTC+05:00)',
            '(UTC+05:00)-91' => 'Asia/Tashkent (UTC+05:00)',
            '(UTC+05:30)-92' => 'Asia/Chennai (UTC+05:30)',
            '(UTC+05:30)-93' => 'Asia/Kolkata (UTC+05:30)',
            '(UTC+05:30)-94' => 'Asia/Mumbai (UTC+05:30)',
            '(UTC+05:30)-95' => 'Asia/New Delhi (UTC+05:30)',
            '(UTC+05:30)-96' => 'Asia/Sri Jayawardenepura (UTC+05:30)',
            '(UTC+05:45)-97' => 'Asia/Katmandu (UTC+05:45)',
            '(UTC+06:00)-98' => 'Asia/Almaty (UTC+06:00)',
            '(UTC+06:00)-99' => 'Asia/Astana (UTC+06:00)',
            '(UTC+06:00)-100' => 'Asia/Dhaka (UTC+06:00)',
            '(UTC+06:00)-101' => 'Asia/Ekaterinburg (UTC+06:00)',
            '(UTC+06:30)-102' => 'Asia/Rangoon (UTC+06:30)',
            '(UTC+07:00)-103' => 'Asia/Bangkok (UTC+07:00)',
            '(UTC+07:00)-104' => 'Asia/Hanoi (UTC+07:00)',
            '(UTC+07:00)-105' => 'Asia/Jakarta (UTC+07:00',
            '(UTC+07:00)-106' => 'Asia/Novosibirsk (UTC+07:00)',
            '(UTC+08:00)-107' => 'Asia/Beijing (UTC+08:00)',
            '(UTC+08:00)-108' => 'Asia/Chongqing (UTC+08:00)',
            '(UTC+08:00)-109' => 'Asia/Hong Kong (UTC+08:00)',
            '(UTC+08:00)-110' => 'Asia/Krasnoyarsk (UTC+08:00)',
            '(UTC+08:00)-111' => 'Asia/Kuala_Lumpur (UTC+08:00)',
            '(UTC+08:00)-112' => 'Australia/Perth (UTC+08:00)',
            '(UTC+08:00)-113' => 'Asia/Singapore (UTC+08:00)',
            '(UTC+08:00)-114' => 'Asia/Taipei (UTC+08:00)',
            '(UTC+08:00)-115' => 'Asia/Ulaan Bataar (UTC+08:00)',
            '(UTC+08:00)-116' => 'Asia/Urumqi (UTC+08:00)',
            '(UTC+09:00)-117' => 'Asia/Irkutsk (UTC+09:00)',
            '(UTC+09:00)-118' => 'Asia/Osaka (UTC+09:00)',
            '(UTC+09:00)-119' => 'Asia/Sapporo (UTC+09:00)',
            '(UTC+09:00)-120' => 'Asia/Seoul (UTC+09:00)',
            '(UTC+09:00)-121' => 'Asia/Tokyo (UTC+09:00)',
            '(UTC+09:30)-222' => 'Australia/Adelaide (UTC+09:30)',
            '(UTC+09:30)-123' => 'Australia/Darwin (UTC+09:30)',
            '(UTC+10:00)-124' => 'Australia/Brisbane (UTC+10:00)',
            '(UTC+10:00)-125' => 'Australia/Canberra (UTC+10:00)',
            '(UTC+10:00)-126' => 'Pacific/Guam (UTC+10:00)',
            '(UTC+10:00)-127' => 'Australia/Hobart (UTC+10:00)',
            '(UTC+10:00)-128' => 'Australia/Melbourne (UTC+10:00)',
            '(UTC+10:00)-129' => 'Pacific/Port Moresby (UTC+10:00)',
            '(UTC+10:00)-130' => 'Australia/Sydney (UTC+10:00)',
            '(UTC+10:00)-131' => 'Asia/Yakutsk (UTC+10:00)',
            '(UTC+11:00)-132' => 'Asia/Vladivostok (UTC+11:00)',
            '(UTC+12:00)-133' => 'Pacific/Auckland (UTC+12:00)',
            '(UTC+12:00)-134' => 'Pacific/Fiji (UTC+12:00)',
            '(UTC+12:00)-135' => 'Pacific/International Date Line West (UTC+12:00)',
            '(UTC+12:00)-136' => 'Asia/Kamchatka (UTC+12:00)',
            '(UTC+12:00)-137' => 'Asia/Magadan (UTC+12:00)',
            '(UTC+12:00)-139' => 'Pacific/Marshall Is. (UTC+12:00)',
            '(UTC+12:00)-140' => 'Asia/New Caledonia (UTC+12:00)',
            '(UTC+12:00)-141' => 'Asia/Solomon Is. (UTC+12:00)',
            '(UTC+12:00)-142' => 'Pacific/Wellington (UTC+12:00)',
            '(UTC+13:00)-143' => 'Pacific/Pacific/Tongatapu (UTC+13:00)'
        );

        asort($data);

        foreach ($data as $key => $value) {
            $result[] = "['" . $key . "', '" . $value . "']";
        }

        $store = "[" . implode(",", $result) . "]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SCHOOL_TIMEZONE'";
        $js .= ",fieldLabel: '" . SCHOOL_TIMEZONE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SCHOOL_TIMEZONE'";
        $js .= ",hiddenName: 'SCHOOL_TIMEZONE'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }

        if ($allowBlank) {
            $js .= ",allowBlank:false";
        } else {
            $js .= ",allowBlank:true";
        }

        return $js;
    }

    static function ComboSchoolTerm($fieldLabel, $readOnly = false, $width = false) {
        $store = "[[0, '2xSemester'],[1, '3xTerm'],[2, '4xQuarter']]";
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'TERM_NUMBER_ID'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($fieldLabel) {
            $js .= ",fieldLabel: '" . $fieldLabel . "'";
        } else {
            $js .= ",fieldLabel: '" . TERM_NUMBER . "'";
        }
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        if ($readOnly) {
            $js .= ",readOnly:true";
        }
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . ", value:0";
        $js .= ",name: 'TERM_NUMBER'";
        $js .= ",hiddenName: 'TERM_NUMBER'";
        return $js;
    }

    static function comboEyeChart($disabled = false, $hidden = false, $width = false) {

        $store = BuildData::comboDataAllEyeChart();
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'EYECHART_TYPE'";
        $js .= ",fieldLabel: 'Chart Type'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        if ($disabled) {
            $js .= ",readOnly:true";
        }

        if ($hidden) {
            $js .= ",hidden:true";
        }

        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: '" . PLEASE_CHOOSE . "'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'EYECHART_TYPE'";
        $js .= ",hiddenName: 'EYECHART_TYPE'";
        if ($width) {
            $js .= ",width:" . $width . "";
        } else {
            $js .= ",width:250";
        }
        $js .= ",allowBlank:true";

        return $js;
    }

}

?>
