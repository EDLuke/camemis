<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 10.10.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class AdminField {

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
        $js .= ",emptyText: 'Please choose...'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:300";
        $js .= ",name: '" . $name . "'";
        $js .= ",readOnly: " . $readOnly . "";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function Trigger($name, $fieldLabel, $onClick, $allowBlank = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "id: '" . $name . "',fieldLabel: '" . $fieldLabel . "',xtype: 'trigger',name: '" . $name . "',
                triggerClass: 'x-form-search-trigger',editable:false,
                anchor: '95%',
                onTriggerClick: function() {
                    " . $onClick . "
                } ";
        $js .= ",allowBlank:" . $allowBlank . "";
        return $js;
    }

    static function Displayfield($name, $fieldLabel, $value = false) {

        $js = "";
        $js .= "xtype: 'displayfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:300";
        $js .= ",name: '" . $name . "'";
        $js .= ",value: '" . $value . "'";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function Loginname($name, $fieldLabel) {

        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",readOnly: true";
        $js .= ",width:300";
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
        $js .= ",width:300";
        $js .= ",allowBlank:" . $allowBlank . "";
        $js .= ",name: '" . $name . "'";
        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        return $js;
    }

    static function EMailfield($name, $fieldLabel, $readOnly = false, $allowBlank = false) {

        $readOnly = $readOnly ? "true" : "false";
        if ($allowBlank) {
            $emptyText = 'This field is required';
            $allowBlank = "false";
        } else {
            $emptyText = "";
            $allowBlank = "true";
        }
        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: '" . $emptyText . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:300";
        $js .= ",name: '" . $name . "'";
        $js .= ",readOnly: " . $readOnly . "";
        $js .= ",allowBlank:" . $allowBlank . "";
        $js .= ",regex: /^([\w\-\'\-]+)(\.[\w-\'\-]+)*@([\w\-]+\.){1,5}([A-Za-z]){2,4}$/";

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
        $js .= ",width:300";
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

    static function Textfield($id, $name, $fieldLabel, $allowBlank = false, $readOnly = false, $value = false) {

        if ($allowBlank) {
            $emptyText = 'This field is required';
            $allowBlank = "false";
        } else {
            $emptyText = "";
            $allowBlank = "true";
        }

        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $id . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:300";
        $js .= ",emptyText: '" . $emptyText . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",allowBlank: " . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";
        if ($readOnly)
            $js .= ",readOnly: true";
        if ($value)
            $js .= ",value: '" . $value . "'";
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

    static function Combo($name, $fieldLabel, $store, $readOnly = false) {
        $readOnly = $readOnly ? "true" : "false";
        $store = $store ? $store : "[]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",lazyRender: true";
        $js .= ",resizable: true";
        $js .= ",editable:false";
        $js .= ",emptyText: 'Please choose...'";
        $js .= ",store: " . $store . "";
        $js .= ",name: '" . $name . "'";
        $js .= ",hiddenName: '" . $name . "'";
        $js .= ",readOnly: " . $readOnly . "";
        $js .= ",width:300";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function Textarea($name, $fieldLabel, $height, $readOnly = false, $allowBlank = false, $value = false) {

        $js = "";
        $js .= "xtype: 'textarea'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",emptyText: ''";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:300";
        $js .= ",name: '" . $name . "'";
        if ($readOnly)
            $js .= ",readOnly: true";
        if ($allowBlank)
            $js .= ",allowBlank: false";
        $js .= ",height: " . $height . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        if ($value)
            $js .= ",value: '" . $value . "'";

        return $js;
    }

    static function Datefield($name, $fieldLabel, $allowBlank = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype:'datefield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",format: '" . setExtDatafieldFormat() . "'";
        $js .= ",width:300";
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
        $js .= ",width:300";
        $js .= ",value: '" . getShowDate($Date) . "'";
        $js .= ",allowBlank:" . $allowBlank . "";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function HTML($name, $width = false, $height = false, $readOnly = false) {

        $readOnly = $readOnly ? "true" : "false";

        $js = "
        xtype:'htmleditor'
        ,layout: 'form'
        ,id: '" . $name . "_ID'
        ,name: '" . $name . "'
        ,hideLabel: true
        ,listeners: {
            'initialize': function(f){
                this.setReadOnly(" . $readOnly . ");
            }
         }
        ,enableColors: false
        ,enableAlignments: false
        ,enableFont: false
        ,enableLinks: false
        ,enableSourceEdit: false
        ";

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

    static function Numberfield($id, $name, $fieldLabel, $allowBlank = true, $value = false, $readOnly = false) {
        $allowBlank = $allowBlank ? "false" : "true";
        $js = "";
        $js .= "xtype: 'numberfield'";
        $js .= ",id: '" . $id . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",allowBlank: " . $allowBlank . "";
        if ($value)
            $js .= ",value: " . $value . "";
        $js .= ",decimalPrecision : 3";
        $js .= ",width:300";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        if ($readOnly)
            $js .= ",readOnly: " . $readOnly . "";

        return $js;
    }

    static function Checkbox($id, $name, $fieldLabel, $value = false, $checked = false) {
        $js = "";
        $js .= "fieldLabel: ''";
        $js .= ",xtype: 'checkbox'";
        $js .= ",id: '" . $id . "'";
        $js .= ",boxLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",hideLabel: true";

        if ($value)
            $js .= ",value: '" . $value . "'";
        if ($checked)
            $js .= ",checked: true";

        return $js;
    }

    static function Radio($id, $name, $fieldLabel, $value, $checked = false) {
        $js = "";
        $js .= "fieldLabel: ''";
        $js .= ",xtype: 'radio'";
        $js .= ",id: '" . $id . "'";
        $js .= ",boxLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",inputValue: '" . $value . "'";
        $js .= ",hideLabel: true";

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
        $js .= ",width:300";
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

    static function Uploadfield($name, $fieldLabel, $readOnly = false, $allowBlank = false) {
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
        $js .= ",emptyText: 'Please choose...'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",name: '" . $name . "'";
        $js .= ",buttonText: ''";
        $js .= ",buttonCfg: {";
        $js .= "iconCls: 'icon-image_add'";
        $js .= "}";
        $js .= ",width:300";

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

    static function Timefield($name, $fieldLabel) {

        $js = "";
        $js .= "xtype: 'textfield'";
        $js .= ",id: '" . $name . "_ID'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",width:300";
        $js .= ",name: '" . $name . "'";
        $js .= ",emptyText: 'HH:MM'";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboCAMEMISModulAPI($fieldLabel) {

        $store = "[
            ['SCHOOL', 'School'],
            ['UNIVERSITY', 'University'],
            ['KINDERGARTEN', 'Kindergarten']
        ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'MODUL_API'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: 'Please choose'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'MODUL_API'";
        $js .= ",value: 'SCHOOL'";
        $js .= ",hiddenName: 'MODUL_API'";
        $js .= ",width:300";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboSchulTemplate($fieldLabel) {

        $store = "[
            ['1', 'Primary School']
            ,['2', 'Secondary School']
            ,['3', 'High School']
            ,['4', 'Primary School+Secondary School']
            ,['5', 'Secondary School+High School']
            ,['6', 'Prymary School+Secondary School+High School']
        ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'SYSTEM_TEMPLATE'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: 'Please choose'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'SYSTEM_TEMPLATE'";
        $js .= ",hiddenName: 'SYSTEM_TEMPLATE'";
        $js .= ",width:300";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboEducationType($fieldLabel) {

        $store = "[
            ['SCHOOL', 'School'],
            ['UNIVERSITY', 'University'],
            ['KINDERGARTEN', 'Kindergarten']
        ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'EDUCATION_TYPE'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: 'Please choose'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'EDUCATION_TYPE'";
        $js .= ",value: 'SCHOOL'";
        $js .= ",hiddenName: 'EDUCATION_TYPE'";
        $js .= ",width:300";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboObjectType($fieldLabel) {

        $store = "[
            ['FOLDER', 'Folder'],
            ['ITEM', 'ITEM']
        ]";

        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'OBJECT_TYPE'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: 'Please choose'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'OBJECT_TYPE'";
        $js .= ",value: 'FOLDER'";
        $js .= ",hiddenName: 'OBJECT_TYPE'";
        $js .= ",width:300";
        $js .= ",allowBlank:false";

        if ($fieldLabel == "")
            $js .= ",hideLabel: true";

        return $js;
    }

    static function ComboCountry($fieldLabel) {

        $store = "[
            ['KH', 'Cambodia'],
            ['ID', 'Indonesia'],
            ['LA', 'Laos'],
            ['MM', 'Myanmar'],
            ['MY', 'Malaysia'],
            ['PH', 'Philippines'],
            ['TH', 'Thailand']
        ]";
        /*
          $store = "[

          ['AF','Afghanistan']
          ['AX','Åland Islands'],
          ['AL','Albania'],
          ['DZ','Algeria'],
          ['AS','American Samoa'],
          ['AD','Andorra'],
          ['AO','Angola'],
          ['AI','Anguilla'],
          ['AQ','Antarctica'],
          ['AG','Antigua and Barbuda'],
          ['AR','Argentina'],
          ['AM','Armenia'],
          ['AW','Aruba'],
          ['AU','Australia'],
          ['AT','Austria'],
          ['AZ','Azerbaijan'],
          ['BS','Bahamas'],
          ['BH','Bahrain'],
          ['BD','Bangladesh'],
          ['BB','Barbados'],
          ['BY','Belarus'],
          ['BE','Belgium'],
          ['BZ','Belize'],
          ['BJ','Benin'],
          ['BM','Bermuda'],
          ['BT','Bhutan'],
          ['BO','Bolivia'],
          ['BA','Bosnia and Herzegovina'],
          ['BW','Botswana'],
          ['BV','Bouvet Island'],
          ['BR','Brazil'],
          ['IO','British Indian Ocean Territory'],
          ['BN','Brunei Darussalam'],
          ['BG','Bulgaria'],
          ['BF','Burkina Faso'],
          ['BI','Burundi'],
          ['KH','Cambodia'],
          ['CM','Cameroon'],
          ['CA','Canada'],
          ['CV','Cape Verde'],
          ['KY','Cayman Islands'],
          ['CF','Central African Republic'],
          ['TD','Chad'],
          ['CL','Chile'],
          ['CN','China'],
          ['CX','Christmas Island'],
          ['CC','Cocos (Keeling) Islands'],
          ['CO','Colombia'],
          ['KM','Comoros'],
          ['CG','Congo'],
          ['CD','The Democratic Republic of The Congo'],
          ['CK','Cook Islands'],
          ['CR','Costa Rica'],
          ['HR','Croatia'],
          ['CU','Cuba'],
          ['CY','Cyprus'],
          ['CZ','Czech Republic'],
          ['DK','Denmark'],
          ['DJ','Djibouti'],
          ['DM','Dominica'],
          ['DO','Dominican Republic'],
          ['EC','Ecuador'],
          ['EG','Egypt'],
          ['SV','El Salvador'],
          ['GQ','Equatorial Guinea'],
          ['ER','Eritrea'],
          ['EE','Estonia'],
          ['ET','Ethiopia'],
          ['FK','Falkland Islands (Malvinas)'],
          ['FO','Faroe Islands'],
          ['FJ','Fiji'],
          ['FI','Finland'],
          ['FR','France'],
          ['GF','French Guiana'],
          ['PF','French Polynesia'],
          ['TF','French Southern Territories'],
          ['GA','Gabon'],
          ['GM','Gambia'],
          ['GE','Georgia'],
          ['DE','Germany'],
          ['GH','Ghana'],
          ['GI','Gibraltar'],
          ['GR','Greece'],
          ['GL','Greenland'],
          ['GD','Grenada'],
          ['GP','Guadeloupe'],
          ['GU','Guam'],
          ['GT','Guatemala'],
          ['GG','Guernsey'],
          ['GN','Guinea'],
          ['GW','Guinea-bissau'],
          ['GY','Guyana'],
          ['HT','Haiti'],
          ['HM','Heard Island and Mcdonald Islands'],
          ['VA','Holy See (Vatican City State)'],
          ['HN','Honduras'],
          ['HK','Hong Kong'],
          ['HU','Hungary'],
          ['IS','Iceland'],
          ['IN','India'],
          ['ID','Indonesia'],
          ['IR','Islamic Republic of Iran'],
          ['IQ','Iraq'],
          ['IE','Ireland'],
          ['IM','Isle of Man'],
          ['IL','Israel'],
          ['IT','Italy'],
          ['JM','Jamaica'],
          ['JP','Japan'],
          ['JE','Jersey'],
          ['JO','Jordan'],
          ['KZ','Kazakhstan'],
          ['KE','Kenya'],
          ['KI','Kiribati'],
          ['KP','Democratic People\'s Republic of Korea'],
          ['KR','Republic of Korea'],
          ['KW','Kuwait'],
          ['KG','Kyrgyzstan'],
          ['LA','Lao People\'s Democratic Republic'],
          ['LV','Latvia'],
          ['LB','Lebanon'],
          ['LS','Lesotho'],
          ['LR','Liberia'],
          ['LY','Libyan Arab Jamahiriya'],
          ['LI','Liechtenstein'],
          ['LT','Lithuania'],
          ['LU','Luxembourg'],
          ['MO','Macao'],
          ['MK','Macedonia,' ],
          ['MG','Madagascar'],
          ['MW','Malawi'],
          ['MY','Malaysia'],
          ['MV','Maldives'],
          ['ML','Mali'],
          ['MT','Malta'],
          ['MH','Marshall Islands'],
          ['MQ','Martinique'],
          ['MR','Mauritania'],
          ['MU','Mauritius'],
          ['YT','Mayotte'],
          ['MX','Mexico'],
          ['FM','Micronesia'],
          ['MD','Moldova'],
          ['MC','Monaco'],
          ['MN','Mongolia'],
          ['ME','Montenegro'],
          ['MS','Montserrat'],
          ['MA','Morocco'],
          ['MZ','Mozambique'],
          ['MM','Myanmar'],
          ['NA','Namibia'],
          ['NR','Nauru'],
          ['NP','Nepal'],
          ['NL','Netherlands'],
          ['AN','Netherlands Antilles'],
          ['NC','New Caledonia'],
          ['NZ','New Zealand'],
          ['NI','Nicaragua'],
          ['NE','Niger'],
          ['NG','Nigeria'],
          ['NU','Niue'],
          ['NF','Norfolk Island'],
          ['MP','Northern Mariana Islands'],
          ['NO','Norway'],
          ['OM','Oman'],
          ['PK','Pakistan'],
          ['PW','Palau'],
          ['PS','Palestinian Territory'],
          ['PA','Panama'],
          ['PG','Papua New Guinea'],
          ['PY','Paraguay'],
          ['PE','Peru'],
          ['PH','Philippines'],
          ['PN','Pitcairn'],
          ['PL','Poland'],
          ['PT','Portugal'],
          ['PR','Puerto Rico'],
          ['QA','Qatar'],
          ['RE','Reunion'],
          ['RO','Romania'],
          ['RU','Russian Federation'],
          ['RW','Rwanda'],
          ['SH','Saint Helena'],
          ['KN','Saint Kitts and Nevis'],
          ['LC','Saint Lucia'],
          ['PM','Saint Pierre and Miquelon'],
          ['VC','Saint Vincent and The Grenadines'],
          ['WS','Samoa'],
          ['SM','San Marino'],
          ['ST','Sao Tome and Principe'],
          ['SA','Saudi Arabia'],
          ['SN','Senegal'],
          ['RS','Serbia'],
          ['SC','Seychelles'],
          ['SL','Sierra Leone'],
          ['SG','Singapore'],
          ['SK','Slovakia'],
          ['SI','Slovenia'],
          ['SB','Solomon Islands'],
          ['SO','Somalia'],
          ['ZA','South Africa'],
          ['GS','South Georgia and The South Sandwich Islands'],
          ['ES','Spain'],
          ['LK','Sri Lanka'],
          ['SD','Sudan'],
          ['SR','Suriname'],
          ['SJ','Svalbard and Jan Mayen'],
          ['SZ','Swaziland'],
          ['SE','Sweden'],
          ['CH','Switzerland'],
          ['SY','Syrian Arab Republic'],
          ['TW','Taiwan'],
          ['TJ','Tajikistan'],
          ['TZ','Tanzania'],
          ['TH','Thailand'],
          ['TL','Timor-leste'],
          ['TG','Togo'],
          ['TK','Tokelau'],
          ['TO','Tonga'],
          ['TT','Trinidad and Tobago'],
          ['TN','Tunisia'],
          ['TR','Turkey'],
          ['TM','Turkmenistan'],
          ['TC','Turks and Caicos Islands'],
          ['TV','Tuvalu'],
          ['UG','Uganda'],
          ['UA','Ukraine'],
          ['AE','United Arab Emirates'],
          ['GB','United Kingdom'],
          ['US','United States'],
          ['UM','United States Minor Outlying Islands'],
          ['UY','Uruguay'],
          ['UZ','Uzbekistan'],
          ['VU','Vanuatu'],
          ['VE','Venezuela'],
          ['VN','Viet Nam'],
          ['VG','Virgin Islands'],
          ['VI','Virgin Islands'],
          ['WF','Wallis and Futuna'],
          ['EH','Western Sahara'],
          ['YE','Yemen'],
          ['ZM','Zambia'],
          ['ZW','Zimbabwe']
          ]";
         */
        $js = "";
        $js .= "xtype: 'combo'";
        $js .= ",id: 'COUNTRY'";
        $js .= ",fieldLabel: '" . $fieldLabel . "'";
        $js .= ",mode: 'local'";
        $js .= ",editable:false";
        $js .= ",triggerAction: 'all'";
        $js .= ",emptyText: 'Please choose'";
        $js .= ",store: " . $store . "";
        $js .= ",name: 'COUNTRY'";
        $js .= ",hiddenName: 'COUNTRY'";
        $js .= ",width:300";
        $js .= ",allowBlank:false";

        return $js;
    }

}

?>