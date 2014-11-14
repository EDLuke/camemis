<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// @Hahn Stephen Senior Software Developer
// Date: 21.09.2010: Time: 02.18
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CamemisDynCombo {

    protected $object = null;
    public $objectTitle = null;
    public $width = null;
    public $varName = "NAME";
    protected $modul = null;
    protected $loadUrl = null;
    public $allowBlank = "true";
    public $hidden = "false";
    public $params = null;
    public $comboValue = null;
    protected $loadParams = null;
    public $readOnly = "false";

    public function __construct($object, $modul) {

        $this->object = $object;
        $this->modul = $modul;
    }

    public function getObjectName() {

        return strtoupper($this->object . "_" . $this->modul);
    }

    public function getObjectId() {

        return strtoupper($this->modul) . "_ID";
    }

    public function setLoadParams($value) {

        if ($value)
            return $this->loadParams = $value;
    }

    public function setLoadUrl($value) {
        return $this->loadUrl = $value;
    }

    protected function loadURL() {

        return "'" . $this->loadUrl . "'";
    }

    public function renderJS() {

        $js = "";
        $js .= "COMBO_" . $this->getObjectName() . " = Ext.extend(Ext.form.ComboBox, {";
        $js .= "id: '" . $this->getObjectId() . "'";
        $js .= ",fieldLabel: '" . $this->objectTitle . "'";
        $js .= ",readOnly: " . $this->readOnly . "";
        $js .= ",emptyText:'" . PLEASE_CHOOSE . "'";
        $js .= ",hidden: " . $this->hidden . "";

        $js .= ",initComponent: function(){";
        $js .= "Ext.apply(this, {";
        $js .= "mode: 'local'";
        $js .= ",triggerAction: 'all'";
        $js .= ",displayField: 'NAME'";
        $js .= ",valueField: 'ID'";
        if ($this->width) {
            $js .= ",width: $this->width";
        } else {
            $js .= ",width:230";
        }
        $js .= ",editable:false";
        $js .= ",name: '" . $this->varName . "'";
        $js .= ",hiddenName: '" . $this->varName . "'";
        $js .= ",allowBlank: " . $this->allowBlank . "";
        $js .= ",value : '" . $this->comboValue . "'";
        $js .= "});";
        $js .= "this.store = new Ext.data.Store({";
        $js .= "proxy: new Ext.data.HttpProxy({url: " . $this->loadURL() . ", timeout:6000, method: 'POST'})";
        $js .= ",baseParams:{";
        $js .= "" . $this->loadParams . "";
        $js .= "}";
        $js .= ",reader: new Ext.data.JsonReader({totalProperty: 'totalCount',root:'rows'}";
        $js .= ",[{name: 'ID'}, {name: 'NAME'}])";
        $js .= "});";
        $js .= "COMBO_" . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);";
        $js .= "},onRender:function() {";
        $js .= "COMBO_" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);";
        $js .= "this.store.reload();";
        $js .= "}";
        $js .= "});";
        $js .= "Ext.reg('" . $this->getObjectXType() . "', COMBO_" . $this->getObjectName() . ");
            ";
        return print$js;
    }

    public function getObjectXType() {

        return "XTYPE_" . strtoupper($this->getObjectName());
    }

}

?>