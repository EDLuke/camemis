<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 24.07.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class AdminForm {

    public $objectTitle = "";
    //public $tbarAlign = "'->',";
    protected $baseURL =  "/";
    public $tbarAlign = "";
    public $objectBoder = 'false';
    public $objectFrame = 'false';
    public $bodyStyle = "padding:10px";
    public $isKeys = false;
    public $releaseError = false;
    public $msgError = "Warning";
    public $isObjectSend = false;
    public $isObjectReply = false;
    public $labelAlign = "top";
    public $labelWidth = 120;
    protected $modul = null;
    protected $numItems = null;
    protected $items = array();
    protected $tbaritems = array();
    protected $loadparams = null;
    public $isObjectDefaultOnLoad = true;
    protected $saveparams = null;
    protected $releaseparams = null;
    protected $sendparams = null;
    protected $Replyparams = null;
    protected $removeparams = null;
    public $isWindowlocation = true;
    public $parentReload = "";
    public $treeReloadId = null;
    protected $submodul = null;
    protected $onEmbeddedEvents = null;
    protected $onEmbeddedReleaseEvents = null;
    protected $onEmbeddedSendEvents = null;
    protected $onEmbeddedReplyEvents = null;
    protected $onEmbeddedRemoveEvents = null;
    protected $callback = null;
    public $isEmbeddedGrid = false;
    protected $embeddedGridId = null;

    public function __construct($modul, $submodul = false) {
        $this->modul = $modul;
        $this->submodul = $submodul;
    }

    public function getObjectName() {
        if ($this->submodul) {
            return strtoupper("FORM." . strtoupper($this->modul) . "_" . $this->submodul);
        } else {
            return strtoupper("FORM." . strtoupper($this->modul));
        }
    }

    public function getObjectId() {
        if ($this->submodul) {
            return strtoupper($this->modul . "_ID" . "_" . $this->submodul);
        } else {
            return strtoupper($this->modul . "_ID");
        }
    }

    public function getObjectXType() {
        if ($this->submodul) {
            return strtoupper($this->modul . "_XTYPE" . "_" . $this->submodul);
        } else {
            return strtoupper($this->modul . "_XTYPE");
        }
    }

    public function addObjectItems($value) {
        $this->items[] = "{" . $value . "}";
        return $this->items;
    }

    public function addTbarItems($value) {
        $this->tbaritems[] = "{" . $value . "}";
        return $this->tbaritems;
    }

    protected function setObjectItems() {
        return implode(",", $this->items);
    }

    protected function setTBarItems() {
        return implode(",", $this->tbaritems);
    }

    public function setSaveParams($value) {
        return $this->saveparams = $value;
    }

    public function setCallback($value) {
        return $this->callback = $value;
    }

    public function setLoadParams($value) {
        return $this->loadparams = $value;
    }

    public function setReleaseParams($value) {
        return $this->releaseparams = $value;
    }

    public function setSendParams($value) {
        return $this->sendparams = $value;
    }

    public function setReplyParams($value) {
        return $this->Replyparams = $value;
    }

    public function setRemoveParams($value) {
        return $this->removeparams = $value;
    }

    public function setOnEmbeddedEvents($value) {
        return $this->onEmbeddedEvents = $value;
    }

    public function setOnEmbeddedReleaseEvents($value) {
        return $this->onEmbeddedReleaseEvents = $value;
    }

    public function setOnEmbeddedSendEvents($value) {
        return $this->onEmbeddedSendEvents = $value;
    }

    public function setOnEmbeddedReplyEvents($value) {
        return $this->onEmbeddedReplyEvents = $value;
    }

    public function setOnEmbeddedRemoveEvents($value) {
        return $this->onEmbeddedRemoveEvents = $value;
    }

    public function setBodyStyle($value) {
        return $this->bodyStyle = $value;
    }

    public function setSearchGridParams($value) {
        return $this->searchGridParams = $value;
    }

    public function setEmbeddedGridId($value) {
        return $this->embeddedGridId = $value;
    }

    public function setURL($value) {

        return $this->baseURL = $value;
    }
    
    public function renderJS() {
        $js = "";
        $js .= "Ext.namespace('FORM');";
        $js .= "" . $this->getObjectName() . " = Ext.extend(Ext.form.FormPanel, {";
        $js .= "id: '" . $this->getObjectId() . "'";
        $js .= ",border: " . $this->objectBoder . "";
        $js .= ",title: '" . $this->objectTitle . "'";
        $js .= ",labelAlign: '" . $this->labelAlign . "'";
        $js .= ",labelWidth: " . $this->labelWidth . "";
        $js .= ",buttonAlign: 'right'";
        $js .= ",bodyStyle: '" . $this->bodyStyle . "'";
        $js .= "," . $this->initComponent() . "";
        $js .= "," . $this->onRender() . "";
        $js .= "," . $this->onLoad() . "";
        $js .= "," . $this->onReset() . "";
        $js .= "," . $this->onSubmit() . "";
        $js .= "," . $this->onSuccess() . "";
        $js .= "," . $this->onFailure() . "";
        $js .= "," . $this->showError() . "";
        $js .= "," . $this->onEmbeddedEvents() . "";

        if ($this->isObjectSend) {
            $js .= "," . $this->onSetObjectSend() . "";
            $js .= "," . $this->onActionSetSend() . "";
        } elseif ($this->isObjectReply) {
            $js .= "," . $this->onSetObjectReply() . "";
            $js .= "," . $this->onActionSetReply() . "";
        } else {
            $js .= "," . $this->onSetObjectReleaseOn() . "";
            $js .= "," . $this->onSetObjectReleaseOff() . "";
            $js .= "," . $this->onActionSetRelease() . "";
        }

        $js .= "," . $this->onSetObjectRemove() . "";

        $js .= "," . $this->onActionSetRemove() . "";
        $js .= "});";

        $js .= "Ext.reg('" . $this->getObjectXType() . "', " . $this->getObjectName() . ");";

        return print$js;
    }

    protected function initComponent() {
        $js = "";
        $js .= "initComponent: function(){";
        $js .= "Ext.apply(this, {";
        $js .= "height: percentHeight(100),autoScroll: true,items:[" . $this->setObjectItems() . "]";
        $js .= ",keys: {" . $this->onKeys() . "}";
        $js .= ",tbar: [" . $this->tbarAlign . " " . $this->setTBarItems() . "]";
        $js .= "});";
        $js .= "" . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);";
        $js .= "}";

        return $js;
    }

    protected function onRender() {
        $js = "";
        $js .= "onRender:function() {";
        $js .= "" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);";
        $js .= "this.getForm().waitMsgTarget = this.getEl();";
        $js .= "" . $this->setObjectDefaultOnLoad() . "";
        $js .= "}";

        return $js;
    }

    protected function onLoad() {
        $js = "";
        $js .= "onLoad:function(){";
        $js .= "this.load({";
        $js .= "url: '".$this->baseURL."jsonload/'";
        $js .= ",waitMsg:'Loading...'";
        $js .= ",params:{" . $this->loadparams . "}";
        $js .= ",success: function(form, action) {";
        /*
          $js .= "".$this->buttonsReleaseStatus()."";
         */
        $js .= "}";
        $js .= "});";
        $js .= "}";

        return $js;
    }

    protected function onReset() {
        $js = "";
        $js .= "onReset:function(){";
        $js .= "this.getForm().reset();";
        $js .= "}";
        return $js;
    }

    protected function onSubmit() {
        $js = "";
        $js .= "onSubmit:function(){";

        $js .="var selids = '';";
        $js .= "var isValid = this.getForm().isValid();";

        if ($this->isEmbeddedGrid) {
            $js .="
                var sels = Ext.getCmp('" . $this->embeddedGridId . "').getSelectionModel().getSelections();
                for( var i = 0; i < sels.length; i++ ) {
                    if (i >0) selids += ',';
                    selids += sels[i].get('ID');
                }
                if (isValid == false){
                    " . $this->msgSubmitError() . "
                }else if (selids =='') {
                    " . $this->msgSubmitGridSeletionError() . "
                }else{
                    " . $this->getFormSubmit() . "
                }
                ";
        } else {
            $js .="
                if (isValid == false){
                    " . $this->msgSubmitError() . "
                }else{
                    " . $this->getFormSubmit() . "
                }
                ";
        }
        $js .= "}";

        return $js;
    }

    protected function setKeys() {
        $js = "";
        if ($this->onEmbeddedEvents != "") {
            $js .= "this.onEmbeddedEvents";
        } else {
            $js .= "this.onSubmit";
        }
        return $js;
    }

    protected function onSuccess() {
        $js = "";
        $js .= "onSuccess:function(form, action) {";
        $js .= "" . $this->onEmbeddedEvents . "";
        $js .= "" . $this->callback . "";
        $js .= "}";
        return $js;
    }

    protected function onFailure() {
        $js = "";
        $js .= "onFailure:function(form, action) {";
        //$js .= "this.showError(action.result.error || action.response.responseText);";
        $js .= "}";
        return $js;
    }

    protected function showError() {
        $js = "";
        $js .= "showError:function(msg, title) {";
        $js .= "title = title || 'Error';";
        $js .= "Ext.Msg.show({";
        $js .= "title:title";
        $js .= ",msg:msg";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.ERROR";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";
        $js .= "}";

        return $js;
    }

    protected function onEmbeddedEvents() {
        $js = "";
        $js .= "onEmbeddedEvents: function() {";
        $js .= "" . $this->onEmbeddedEvents . "";
        $js .= "}";
        return $js;
    }

    protected function onSetObjectReleaseOn() {
        $js = "";
        $js .= "onSetObjectReleaseOn: function () {";
        $js .= "var isValid = this.getForm().isValid();";
        $js .= "if (isValid == false){";
        $js .= "Ext.Msg.show({";
        $js .= "title:'<b>The data cannot be saved!</b>'";
        $js .= ",msg:'Check all the input-fields that are marked red.'";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.WARNING";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";
        $js .= "}else{";
        $js .= "Ext.MessageBox.confirm('<b>Confirmation!</b>', 'After activating this Item it will be visible to a...', this.onActionSetRelease);";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onSetObjectReleaseOff() {
        $js = "";
        $js .= "onSetObjectReleaseOff: function () {";
        $js .= "var isValid = this.getForm().isValid();";
        $js .= "if (isValid == false){";
        $js .= "Ext.Msg.show({";
        $js .= "title:'<b>The data cannot be saved!</b>'";
        $js .= ",msg:'Check all the input-fields that are marked red.'";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.WARNING";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";
        $js .= "}else{";
        $js .= "Ext.MessageBox.confirm('<b>Confirmation!</b>', 'After deactivating this Item it will not be visibl...', this.onActionSetRelease);";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onSetObjectSend() {
        $js = "";
        $js .= "onSetObjectSend: function () {";
        $js .="var selids = '';";
        $js .= "var isValid = this.getForm().isValid();";

        if ($this->isEmbeddedGrid) {
            $js .="
                var sels = Ext.getCmp('" . $this->embeddedGridId . "').getSelectionModel().getSelections();
                for( var i = 0; i < sels.length; i++ ) {
                    if (i >0) selids += ',';
                    selids += sels[i].get('ID');
                }
                if (isValid == false){
                    " . $this->msgSubmitError() . "
                }else if (selids =='') {
                    " . $this->msgSubmitGridSeletionError() . "
                }else{
                    Ext.MessageBox.confirm('<b>Confirmation!</b>', 'Do you really want to send this message ?', this.onActionSetSend);
                }
                ";
        } else {
            $js .="
                if (isValid == false){
                    " . $this->msgSubmitError() . "
                }else{
                    Ext.MessageBox.confirm('<b>Confirmation!</b>', 'Do you really want to send this message ?', this.onActionSetSend);
                }
                ";
        }
        $js .= "}";

        return $js;
    }

    protected function onSetObjectReply() {
        $js = "";
        $js .= "onSetObjectReply: function () {";
        $js .= "var isValid = this.getForm().isValid();";
        $js .= "if (isValid == false){";
        $js .= "Ext.Msg.show({";
        $js .= "title:'<b>The data cannot be saved!</b>'";
        $js .= ",msg:'Check all the input-fields that are marked red.'";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.WARNING";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";
        $js .= "}else{";
        $js .= "Ext.MessageBox.confirm('<b>Confirmation!</b>', 'Do you really want to reply this message ?', this.onActionSetReply);";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onSetObjectRemove() {
        $js = "";
        $js .= "onSetObjectRemove: function () {";
        $js .= "Ext.MessageBox.confirm('<b>Confirmation!</b>', 'Do you really want to delete this item ?', this.onActionSetRemove);";
        $js .= "}";

        return $js;
    }

    protected function onActionSetRelease() {
        $js = "";
        $js .= "onActionSetRelease: function (btn) {";
        $js .= "if (btn == 'yes'){";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').getForm().submit({";
        $js .= "url: '".$this->baseURL."jsonsave/'";
        $js .= ",scope:this";
        $js .= ",params:{" . $this->saveparams . "}";
        $js .= "});";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '".$this->baseURL."jsonsave/'";
        $js .= ",scope:this";
        $js .= ",params:{" . $this->releaseparams . "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        if ($this->releaseError) {
            $js .= "if (jsonData.error == true){";
            $js .= "" . $this->msgShowError() . "";
            $js .= "}else{";
            $js .= "" . $this->onEmbeddedReleaseEvents . "";
            $js .= "}";
        } else {
            $js .= "" . $this->onEmbeddedReleaseEvents . "";
        }
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onActionSetSend() {
        $js = "";
        $js .= "onActionSetSend: function (btn) {";
        $js .= "if (btn == 'yes'){";
        $js .="var selids = '';";
        if ($this->isEmbeddedGrid) {
            $js .="
                var sels = Ext.getCmp('" . $this->embeddedGridId . "').getSelectionModel().getSelections();

                for( var i = 0; i < sels.length; i++ ) {
                    if (i >0) selids += ',';
                    selids += sels[i].get('ID');
                }
                ";
        }

        $js .= "Ext.getCmp('" . $this->getObjectId() . "').getForm().submit({";
        $js .= "url: '".$this->baseURL."'jsonsave/";
        $js .= ",scope:this";

        if ($this->isEmbeddedGrid) {
            $js .= ",params:{" . $this->sendparams . ", selids: selids}";
        } else {
            $js .= ",params:{" . $this->sendparams . "}";
        }

        $js .= "});";
        $js .= "" . $this->onEmbeddedSendEvents . "";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onActionSetReply() {
        $js = "";
        $js .= "onActionSetReply: function (btn) {";
        $js .= "if (btn == 'yes'){";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').getForm().submit({";
        $js .= "url: '".$this->baseURL."jsonsave/'";
        $js .= ",scope:this";
        $js .= ",params:{" . $this->Replyparams . "}";
        $js .= "});";
        $js .= "" . $this->onEmbeddedReplyEvents . "";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onActionSetRemove() {
        $js = "";
        $js .= "onActionSetRemove: function (btn) {";
        $js .= "if (btn == 'yes'){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '".$this->baseURL."jsonsave/'";
        $js .= ",scope:this";
        $js .= ",params:{" . $this->removeparams . "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "" . $this->onEmbeddedRemoveEvents . "";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function setObjectDefaultOnLoad() {

        $js = "
        this.load({
            url: '".$this->baseURL."jsonload/'
            //,waitMsg:'Loading...'
            ,params:{" . $this->loadparams . "}
            ,success: function(form, action) {
                " . $this->buttonsReleaseStatus() . "
            }
        });
        ";

        return $this->isObjectDefaultOnLoad ? $js : "";
    }

    protected function onKeys() {
        $js = "";
        $js .= "
        key: [13]
        ,fn: " . $this->setKeys() . "
        ,scope:this
        ";

        return $this->isKeys ? $js : "";
    }

    public function ExtgetCmp() {
        return "Ext.getCmp('" . $this->getObjectId() . "')";
    }

    protected function releaseButtonHide() {
        $js = "Ext.getCmp('RELEASE_ID')?Ext.getCmp('RELEASE_ID').hide():'';";
        return $js;
    }

    protected function releaseButtonShow() {
        $js = "Ext.getCmp('RELEASE_ID')?Ext.getCmp('RELEASE_ID').show():'';";
        return $js;
    }

    protected function saveButtonHide() {
        $js = "Ext.getCmp('SAVE_ID')?Ext.getCmp('SAVE_ID').hide():'';";
        return $js;
    }

    protected function saveButtonShow() {
        $js = "Ext.getCmp('SAVE_ID')?Ext.getCmp('SAVE_ID').show():'';";
        return $js;
    }

    protected function releaseSetText($text) {
        $js = "Ext.getCmp('RELEASE_ID')?Ext.getCmp('RELEASE_ID').setText('" . $text . "'):'';";
        return $js;
    }

    protected function releaseSetIcon($icon) {
        $js = "Ext.getCmp('RELEASE_ID')?Ext.getCmp('RELEASE_ID').setIconClass('" . $icon . "'):'';";
        return $js;
    }

    protected function buttonsReleaseStatus() {
        $js = "
        if (action.result.data.STATUS == 1){
            " . $this->saveButtonHide() . "
            " . $this->releaseButtonShow() . "
            " . $this->releaseSetText('Deactivate to Edit') . "
            " . $this->releaseSetIcon("icon-red") . "
        }else{
            " . $this->saveButtonShow() . "
            " . $this->releaseButtonShow() . "
            " . $this->releaseSetText('Activate') . "
            " . $this->releaseSetIcon("icon-green") . "
        }
        ";
        return $js;
    }

    protected function msgShowError() {
        $js = "";
        $js .= "Ext.Msg.show({";
        $js .= "title:'Warning'";
        $js .= ",msg:'" . $this->msgError . "'";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.ERROR";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";

        return $js;
    }

    protected function msgSubmitError() {

        $js = "Ext.Msg.show({";
        $js .= "title:'<b>The data cannot be saved!</b>'";
        $js .= ",msg:'Check all the input-fields that are marked red.'";
        $js .= ",width:250";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.WARNING";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";

        return $js;
    }

    protected function msgSubmitGridSeletionError() {

        $js = "Ext.Msg.show({";
        $js .= "title:'<b>The data cannot be saved!</b>'";
        $js .= ",msg:'Please choose...'";
        $js .= ",width:250";
        $js .= ",modal:true";
        $js .= ",icon:Ext.Msg.WARNING";
        $js .= ",buttons:Ext.Msg.OK";
        $js .= "});";

        return $js;
    }

    protected function getFormSubmit() {

        $js = "this.getForm().submit({";
        $js .= "url: '".$this->baseURL."jsonsave/'";
        $js .= ",scope:this";
        $js .= ",success:this.onSuccess";
        $js .= ",failure:this.onFailure";
        $js .= ",waitMsg:'Saving...'";

        if ($this->isEmbeddedGrid) {
            $js .= ",params:{" . $this->saveparams . ", selids: selids}";
        } else {
            $js .= ",params:{" . $this->saveparams . "}";
        }
        $js .= "});";

        return $js;
    }

}

?>