<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 24.07.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CamemisGrid {

    public $comboReplaceValue = false;
    public $forceFit = "true";
    public $loadType = "jsonload";
    public $loadUrl = null;
    public $saveUrl = null;
    public $treeUrl = null;
    public $clicksToEdit = 1;
    public $loadMask = true;
    public $setUserColumn = false;
    public $isGridEditing = false;
    protected $object = null;
    protected $modul = "MODUL";
    public $isPagingToolbar = true;
    public $removeID = 'ID';
    public $removeNAME = 'NAME';
    public $pageSize = "100";
    protected $objectName = "objectName";
    public $isCheckboxSelect = false;
    public $objectTitle = null;
    public $objectBorder = "false";
    protected $objectWidth = null;
    public $isObjectDefaultOnLoad = true;
    public $isGroupingView = false;
    public $isFilterRow = false;
    public $isQuickySearch = false;
    public $groupField = "";
    public $tbarItems = null;
    public $baseParams = null;
    protected $column = null;
    protected $readfields = array();
    protected $columns = array();
    protected $saveParams = null;
    protected $aftereditLoadParams = null;
    protected $removeParams = null;
    protected $selectionParams = null;
    protected $removeSelectionParams = null;
    protected $cellRenderers = null;
    public $isBufferView = false;
    protected $selectionEmbeddedEvents = null;
    protected $removeEmbeddedEvents = null;
    protected $editEmbeddedEvents = null;
    protected $aftereditCallback = null;
    protected $aftereditEvent = "";
    //@veasna
    public $isDataPreview = false;
    protected $applyRowClass = null;

    public function __construct($object, $modul) {

        $this->object = $object;
        $this->modul = $modul;
        $this->utiles = Utiles::getInstance();
    }

    public function getObjectId() {

        return strtoupper($this->object) . "_" . strtoupper($this->modul) . "_ID";
    }

    public function getObjectName() {
        return "GRID." . strtoupper($this->object) . "_" . strtoupper($this->modul);
    }

    public function getObjectXType() {
        return "XTYPE_" . strtoupper($this->getObjectName()) . "_GRID";
    }

    public function addColumn($column) {
        $this->columns[] = "{" . $column . "}";
    }

    protected function loadURL() {
        return "'" . $this->loadUrl . "'";
    }

    protected function setColumns() {
        return $this->columns ? "," . implode(",", $this->columns) : "";
    }

    public function addReadField($readfield) {
        $this->readfields[] = "{" . $readfield . "}";
    }

    protected function setReadFields() {
        return $this->readfields ? "," . implode(",", $this->readfields) : "";
    }

    protected function setStore() {
        if ($this->isGroupingView) {
            return $this->getGroupingStore();
        } else {
            return $this->getNormalStore();
        }
    }

    public function addTBarItems($value) {
        $this->tbarItems[] = $value;
        return $this->tbarItems;
    }

    protected function setTBarItems() {
        if ($this->tbarItems) {
            return implode(";", $this->tbarItems);
        }
    }

    public function setSaveParams($value) {
        if ($value)
            return $this->saveParams = "," . $value;
    }

    public function setRemoveParams($value) {
        if ($value)
            return $this->removeParams = "," . $value;
    }

    public function setSelectionParams($value) {
        if ($value)
            return $this->selectionParams = "," . $value;
    }

    public function setRemoveSelectionParams($value) {
        if ($value)
            return $this->removeSelectionParams = "," . $value;
    }

    public function addCellRenderer($cell) {
        $this->cellRenderers[] = $cell;
    }

    public function setAftereditCallback($value) {

        return $this->aftereditCallback = $value;
    }

    public function setAftereditEvent($value) {

        return $this->aftereditEvent = $value;
    }

    public function setAftereditLoadParams($value) {

        return $this->aftereditLoadParams = $value ? "," . $value : "";
    }

    protected function setCellRenderers() {
        return $this->cellRenderers ? implode(";", $this->cellRenderers) : "";
    }

    protected function setExtGridType() {
        if ($this->isGridEditing) {
            $js = "Ext.grid.EditorGridPanel";
        } else {
            $js = "Ext.grid.GridPanel";
        }
        return $js;
    }

    public function setSelectionEmbeddedEvents($value) {
        return $this->selectionEmbeddedEvents = $value;
    }

    public function setRemoveEmbeddedEvents($value) {
        return $this->removeEmbeddedEvents = $value;
    }

    public function setEditEmbeddedEvents($value) {
        return $this->editEmbeddedEvents = $value;
    }

    public function setObjectWidth($value) {

        return $this->objectWidth = $value;
    }

    public function setLoadUrl($value) {
        return $this->loadUrl = $value;
    }

    public function setSaveUrl($value) {
        return $this->saveUrl = $value;
    }

    public function addTreeUrl($value) {
        return $this->treeUrl = $value;
    }

    public function renderJS() {

        $js = "";
        $js .= "var replaceValue;";
        $js .= "Ext.ns('Extensive.grid');";
        $js .= $this->setCellRenderers();
        $js .= "Ext.namespace('GRID');";
        if ($this->isCheckboxSelect)
            $js .= "var CheckboxSelect = new Ext.grid.CheckboxSelectionModel({});var replaceValue;";
        $js .= "" . $this->getObjectName() . " = Ext.extend(" . $this->setExtGridType() . ", {";
        $js .= "id: '" . $this->getObjectId() . "'";
        $js .= ",title: '" . $this->objectTitle . "'";
        $js .= ",border: " . $this->objectBorder . "";
        $js .= ",autoScroll: true";
        $js .= ",height: percentHeight(95)";
        $js .= "" . $this->objectWidth() . "";
        $js .= ",initComponent:function() {";
        $js .= "Ext.apply(this, {";
        $js .= "" . $this->ExtToolbar() . "";
        $js .= "columns: [";
        $js .= "" . $this->ExtgridRowNumberer() . "";
        if ($this->isCheckboxSelect)
            $js .= "" . $this->CheckboxSelect() . "";
        $js .= "" . $this->setColumns() . "";
        $js .= "]";

        /**
         * 16.04.2014 KAOM Vibolrith
         */
        if ($this->isFilterRow) {
            $js .= ",plugins:[new Ext.ux.grid.FilterRow({refilterOnStoreUpdate: true})]";
            $js .= ",stripeRows: true";
        }

        /**
         * 18.04.2014 KAOM Vibolrith
         */
        if ($this->isGroupingView) {
            $js .= ",plugins: [new Ext.ux.grid.HybridSummary()]";
        }

        $js .= "" . $this->setSM() . "";
        $js .= " ,viewConfig:{forceFit:" . $this->forceFit . "";

        //@veasna
        if ($this->isDataPreview) {
            $js .=" ,enableRowBody:true";
            $js .=" ,showPreview:true";
            $js .=" ,getRowClass : " . $this->applyRowClass . "";
        }
        //
        $js .= " }";
        $js .= "});";
        $js .= "" . $this->setStore() . "";
        $js .= "" . $this->setBufferView() . "";

        /**
         * 16.04.2014 KAOM Vibolrith
         */
        if ($this->isPagingToolbar)
            $js .= "" . $this->PagingToolbar() . "";

        $js .= "" . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);";
        $js .= "}";
        $js .= ",columnLines: true";
        if ($this->loadMask)
            $js .= ",loadMask: new Ext.LoadMask(Ext.getBody(), {msg:'<b>CAMEMIS " . LOADING . "</b>', msgCls:'x-mask-loading-camemis'})";
        $js .= ",trackMouseOver: true";
        $js .= ",buttonAlign:'center'";
        $js .= "" . $this->setClicksToEdit() . "";
        $js .= "" . $this->setListeners() . "";
        $js .= ",onRender:function() {";
        $js .= "" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);";
        $js .= "" . $this->getTopToolbar() . "";
        $js .= "" . $this->setTBarItems() . "";
        $js .= "" . $this->setQuickySearch() . "";
        $js .= "" . $this->setObjectDefaultOnLoad() . "";
        $js .= "}";
        $js .= "," . $this->onSelection() . "";
        $js .= "," . $this->onRemoveSelection() . "";
        $js .= "," . $this->onRemove() . "";
        $js .= "," . $this->onAddItem() . "";
        $js .= " }";
        $js .= "});";

        $js .= "Ext.reg('" . $this->getObjectXType() . "', " . $this->getObjectName() . ");";

        if ($this->setUserColumn) {
            $this->setUserGridColumns();
        }

        print compressMultiSpacesToSingleSpace($js);
    }

    protected function getGroupingStore() {
        $js = "";
        $js .= "this.store = new Ext.data.GroupingStore({";
        $js .= "proxy: new Ext.data.HttpProxy({url: " . $this->loadURL() . ", method: 'POST'})";
        $js .= ",baseParams:{" . $this->baseParams . "}";
        $js .= ",reader:new Ext.data.JsonReader({";
        $js .= "id:'NAME'";
        $js .= ",totalProperty:'totalCount'";
        $js .= ",root:'rows'";
        $js .= ",fields:[";
        $js .= "{name: 'ID'}";
        $js .= "" . $this->setReadFields() . "";
        $js .= "]";
        $js .= "})";
        $js .= ",sortInfo:{field: '" . $this->groupField . "', direction: 'ASC'}";
        $js .= ",groupField: '" . $this->groupField . "'";
        $js .= "});";
        $js .= "this.view = new Ext.grid.GroupingView({";
        $js .= "forceFit:" . $this->forceFit . "";
        $js .= ",showGroupName: false";
        $js .= ",enableNoGroups: false";
        $js .= ",enableGroupingMenu: false";
        $js .= ",hideGroupedColumn: true";
        $js .= "});";

        return $js;
    }

    protected function getNormalStore() {
        $js = "";
        $js .= "this.store = new Ext.data.Store({";
        $js .= "proxy: new Ext.data.HttpProxy({url: " . $this->loadURL() . ", method: 'POST'})";
        $js .= ",baseParams:{" . $this->baseParams . "}";
        $js .= ",reader:new Ext.data.JsonReader({";
        $js .= "id:'NAME'";
        $js .= ",totalProperty:'totalCount'";
        $js .= ",root:'rows'";
        $js .= ",fields:[";
        $js .= "{name: 'ID'}";
        $js .= "" . $this->setReadFields() . "";
        $js .= "]";
        $js .= "})";
        if ($this->isGroupingView) {
            $js .= ",remoteSort: true";
            $js .= ",remoteGroup: true";
        }
        $js .= "});";

        return $js;
    }

    public function ExtgetCmp() {
        return "Ext.getCmp('" . $this->getObjectId() . "')";
    }

    protected function setObjectDefaultOnLoad() {
        $js = "";
        if ($this->isObjectDefaultOnLoad) {
            $js .= "
            this.store.load.defer(20,this.store,[{" . $this->baseParams . "}]);
            Ext.get(document.body).unmask();
            ";
        }
        return $js;
    }

    //@veasna
    public function dataPreviewField($field, $className) {

        $js = "function(record, rowIndex, p, store) { ";
        $js .= "if (this.showPreview) { ";
        $js .= "p.body = '<div >";
        $js .= "<p class=\"" . $className . "\">' + record.data." . $field . " + '</p>";
        $js .= "</div>';";
        $js .= "return 'x-grid3-row-expanded';";
        $js .= "}";
        $js .= "return 'x-grid3-row-collapsed';";
        $js .= "}";

        $this->applyRowClass = $js;
    }

    public function tbarSummary($value = false) {

        $js = "tbar.add(['-',{";
        $js .= "text:'<b>" . SUMMARY . "</b>'";
        $js .= ",pressed: true";
        $js .= ",enableToggle:true";
        $js .= ",iconCls: 'icon-application_view_list'";
        $js .= ",toggleHandler: function(btn, pressed){";
        $js .= "var view = Ext.getCmp('" . $this->getObjectId() . "').getView();";
        $js .= "view.showPreview = pressed;";
        $js .= "view.refresh();";
        $js .= "}";
        $js .= "}]);";
        if ($value)
            $this->addTBarItems($js);
    }

    //
    protected function setQuickySearch() {
        $js = "";
        $js .= "tbar.add('-');";
        $js .= "tbar.add('<b>" . QUICKY_SEARCH . ":</b> ', ' ',";
        $js .= "new Ext.app.SearchField({";
        $js .= "store: this.store";
        $js .= ",width:150";
        $js .= "})";
        $js .= ");";

        return $this->isQuickySearch ? $js : "";
    }

    protected function getTopToolbar() {
        $c = sizeof($this->tbarItems);
        $js = "";
        if ($c || $this->isQuickySearch) {
            $js .= "var tbar = this.getTopToolbar();";
        }
        return $js;
    }

    protected function ExtToolbar() {
        $c = sizeof($this->tbarItems);
        $js = "";
        if ($c || $this->isQuickySearch) {
            $js .= "tbar: new Ext.Toolbar(),";
        }
        return $js;
    }

    protected function CheckboxSelect() {
        if ($this->isCheckboxSelect) {
            return "CheckboxSelect";
        }
    }

    protected function setSM() {
        if ($this->isCheckboxSelect) {
            return ",sm: CheckboxSelect";
        }
    }

    protected function ExtgridRowNumberer() {
        if (!$this->isCheckboxSelect) {
            return "new Ext.grid.RowNumberer()";
        }
    }

    protected function setBufferView() {
        return "";
    }

    public function setListeners($value = false) {

        $js = "";

        if ($value) {
            $js .= ",listeners: {" . $value . "}";
            //error_log($value);
        } else {
            if ($this->isGridEditing) {
                $js .= ",listeners: {";

                $js .= "afteredit: function (e){";

                $js .= "var objectId = e.record.get('ID');";
                $js .= "if (e.field == 'DELETE'){";

                /**
                 * KAOM Vibolrith
                 * 19.05.2014
                 * Editing Grid Delete and message box
                 */
                $js .= "Ext.MessageBox.show({";
                $js .= "title: '" . ACTION_STATUS . "'";
                $js .= ",msg: '" . DELETE_THIS_ITEM . "'";
                $js .= ",width:250";
                $js .= ",buttons: Ext.MessageBox.YESNOCANCEL";
                $js .= ",icon: Ext.MessageBox.QUESTION";
                $js .= ",fn: function(btn){";
                $js .= "if (btn == 'yes'){";
                    
                    $js .= "Ext.Ajax.request({";
                        $js .= "url: '" . $this->saveUrl . "'";
                        $js .= ",method: 'POST'";
                        $js .= ",params: {";
                            $js .= "id: objectId";
                            $js .= ",field:'DELETE'";
                            $js .= ",newValue:1";
                            $js .= "" . $this->saveParams . "";
                        $js .= "}";
                        $js .= ",success: function (result, request ) {";
                            $js .= "Ext.getCmp('" . $this->getObjectId() . "').store.reload();";
                    $js .= "}";
                $js .= "});";
                
                $js .= "}";
                $js .= "}";
                $js .= "});";

                $js .= "}else{";

                $js .= "var connection = new Ext.data.Connection();";
                $js .= "connection.request({";

                $js .= "url: '" . $this->saveUrl . "'";

                $js .= ",scope:this";
                $js .= ",params: {";
                $js .= "id: objectId";
                $js .= ",field: e.field";
                $js .= ",newValue: e.value";

                $js .= "" . $this->saveParams . "";
                $js .= "}";
                $js .= ",method: 'POST'";
                $js .= ",success: function (respon, opt) {";
                $js .= "jsonData = Ext.util.JSON.decode(respon.responseText);";
                $js .= "if (jsonData) {";
                $js .= "" . $this->aftereditCallback . "";
                if ($this->aftereditEvent)
                    $js .= "" . $this->aftereditEvent . "";
                if ($this->editEmbeddedEvents) {
                    $js .= "" . $this->editEmbeddedEvents . "";
                }
                $js .= "this.getStore().commitChanges();";
                $js .= "}";
                $js .= "}";
                $js .= ",failure: function (respon, opt) {";
                $js .= "if (e) e.reject();";
                $js .= "}";
                $js .= "});";
                $js .= "}";
                $js .= "}";
                $js .= "}";
            }
        }
        return $js;
    }

    ///////////////////////////////////////////////////////
    // PagingToolbar...
    ///////////////////////////////////////////////////////
    protected function PagingToolbar() {
        if ($this->isPagingToolbar) {
            $js = "";
            $js .= "this.bbar = new Ext.PagingToolbar({";
            $js .= "store:this.store";
            $js .= ",displayInfo:true";
            $js .= ",pageSize: " . $this->pageSize . "";
            $js .= "});";
        } else {
            $js = "";
        }
        return $js;
    }

    protected function setClicksToEdit() {

        if ($this->isGridEditing)
            return $js = ",clicksToEdit: $this->clicksToEdit";
    }

    protected function getSelectionModel() {
        if ($this->isGridEditing) {
            $js = "";
            $js .= "var rc = this.getSelectionModel().getSelectedCell();";
            $js .= "if (rc){";
            $js .= "var record = this.getStore().getAt(rc[0]);";
            $js .= "if (record){";
            $js .= "var recordId = record.data." . $this->removeID . ";";
            $js .= "var recordName = record.data." . $this->removeNAME . ";";
            $js .= "}";
            $js .= "}";
        } else {
            $js = "";
            $js .= "var sm = this.getSelectionModel();";
            $js .= "var record = sm.getSelected();";
            $js .= "if (record){";
            $js .= "var recordId = record.data." . $this->removeID . ";";
            $js .= "var recordName = record.data." . $this->removeNAME . ";";
            $js .= "}";
        }

        return $js;
    }

    protected function actionSelection() {
        $js = "";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";

        $js .= "url: '" . $this->saveUrl . "'";

        $js .= ",params: {";
        $js .= "selectionIds: selids";
        $js .= "" . $this->selectionParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "if (jsonData.selectedCount > 0){";
        $js .= "Ext.MessageBox.alert(";
        $js .= "'" . ACTION_STATUS . "'";
        $js .= ",jsonData.selectedCount + ' " . MSG_ACTION_ADDED_ITEM . "'";
        $js .= ",function(btn){";
        $js .= "if (btn == 'ok'){";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').store.reload();";
        $js .= "" . $this->selectionEmbeddedEvents . "";
        $js .= "}";
        $js .= "}";
        $js .= ");";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "});";

        return $js;
    }

    protected function actionRemoveSelection() {
        $js = "";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->saveUrl . "'";
        $js .= ",params: {";
        $js .= "selectionIds: selids";
        $js .= "" . $this->removeSelectionParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "if (jsonData.selectedCount > 0){";
        $js .= "Ext.MessageBox.alert(";
        $js .= "'" . ACTION_STATUS . "'";
        $js .= ",jsonData.selectedCount + ' " . MSG_ACTION_REMOVED_ITEM . "'";
        $js .= ",function(btn){";
        $js .= "if (btn == 'ok'){";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').store.reload();";
        $js .= "" . $this->selectionEmbeddedEvents . "";
        $js .= "}";
        $js .= "}";
        $js .= ");";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "});";

        return $js;
    }

    protected function objectWidth() {

        $js = "";
        if ($this->objectWidth) {
            $js = ",width: " . $this->objectWidth . "";
        }
        return $js;
    }

    ///////////////////////////////////////////////////////
    // OnSelection...
    ///////////////////////////////////////////////////////
    protected function onSelection() {

        $js = "";
        $js .= "onSelection: function (){";
        $js .= "var selids = '';";
        $js .= "var seltexts = '';";
        $js .= "var sels = Ext.getCmp('" . $this->getObjectId() . "').getSelectionModel().getSelections();";
        $js .= "for( var i = 0; i < sels.length; i++ ) {";
        $js .= "if (i >0) selids += ',';";
        $js .= "selids += sels[i].get('ID');";
        $js .= "if (i >0) seltexts += ',';";
        $js .= "seltexts += sels[i].get('NAME');";
        $js .= "}";
        $js .= "if (sels.length == 0){";
        $js .= "Ext.MessageBox.alert('" . WARNING . "', '" . PLEASE_CHOOSE . "');";
        $js .= "}else{";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'" . APPLY . "'";
        $js .= ",msg: '" . MSG_ACCEPT_CHOICE . "'";
        $js .= ",buttons: Ext.MessageBox.YESNO";
        $js .= ",fn: function(btn){";
        $js .= "if (btn=='yes'){";
        $js .= "" . $this->actionSelection() . "";
        $js .= "}";
        $js .= "}";
        $js .= ",icon: Ext.MessageBox.QUESTION";
        $js .= "});";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    ///////////////////////////////////////////////////////
    // OnRemoveSelection...
    ///////////////////////////////////////////////////////
    protected function onRemoveSelection() {

        $js = "";
        $js .= "onRemoveSelection: function (){";
        $js .= "var selids = '';";
        $js .= "var seltexts = '';";
        $js .= "var sels = Ext.getCmp('" . $this->getObjectId() . "').getSelectionModel().getSelections();";
        $js .= "for( var i = 0; i < sels.length; i++ ) {";
        $js .= "if (i >0) selids += ',';";
        $js .= "selids += sels[i].get('ID');";
        $js .= "if (i >0) seltexts += ',';";
        $js .= "seltexts += sels[i].get('NAME');";
        $js .= "}";
        $js .= "if (sels.length == 0){";
        $js .= "Ext.MessageBox.alert('" . WARNING . "', '" . PLEASE_CHOOSE . "');";
        $js .= "}else{";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'" . APPLY . "'";
        $js .= ",msg: '" . MSG_ACCEPT_CHOICE . "'";
        $js .= ",buttons: Ext.MessageBox.YESNO";
        $js .= ",fn: function(btn){";
        $js .= "if (btn=='yes'){";
        $js .= "" . $this->actionRemoveSelection() . "";
        $js .= "}";
        $js .= "}";
        $js .= ",icon: Ext.MessageBox.QUESTION";
        $js .= "});";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    ///////////////////////////////////////////////////////
    // onRemove...
    ///////////////////////////////////////////////////////
    protected function onRemove() {
        $js = "";
        $js .= "onRemove: function (){";
        $js .= "" . $this->getSelectionModel() . "";
        $js .= "if (recordId){";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'" . REMOVE . "?'";
        $js .= ",msg: '" . REMOVE . " ' + recordName + '?'";
        $js .= ",buttons: Ext.MessageBox.YESNO";
        $js .= ",icon: Ext.MessageBox.QUESTION";
        $js .= ",fn: function (btn) {";
        $js .= "if (btn == 'yes'){";
        $js .= "Ext.Ajax.request({";

        $js .= "url: '" . $this->saveUrl . "'";

        $js .= ",method: 'POST'";
        $js .= ",params: {";
        $js .= "removeId: recordId";
        $js .= "" . $this->removeParams . "";
        $js .= "}";
        $js .= ",success: function (result, request ) {";
        $js .= "" . $this->removeEmbeddedEvents . "";

        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    ///////////////////////////////////////////////////////
    // onAddItem...
    ///////////////////////////////////////////////////////
    protected function onAddItem() {

        $js = "";
        $js .= "onAddItem: function (){";
        $js .= "parent.Ext.MessageBox.prompt('" . ADD_ENTRY . "', '" . PLEASE_ENTER_NEW_NAME . ":', showResultText);";
        $js .= "function showResultText(btn,text){";
        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";

        $js .= "url: '" . $this->saveUrl . "'";

        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "name: text";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "var grid = Ext.getCmp('" . $this->getObjectId() . "');";
        $js .= "grid.store.reload();";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function findUserColunmName($str) {
        $str1 = substr(strrchr($str, "dataIndex:"), 4);
        $str2 = strpos($str1, "}");
        $str3 = substr($str1, 0, $str2);
        return substr(trim($str3), 1, -1);
    }

    public function setUserGridColumns() {

        $data = array();

        $CHECK_STR = "";

        foreach ($this->columns as $value) {
            $CHECK_STR = trim(str_replace(",filter:", "", $this->findUserColunmName($value)));

            if (strpos($CHECK_STR, "eturn") !== false) {
                $CHECK_STR = "";
            }

            if (strripos($CHECK_STR, "'") !== false) {
                $CHECK_STR = substr($CHECK_STR, 0, -1);
            }

            if ($CHECK_STR) {
                $data[] = $CHECK_STR;
            }
        }
        Utiles::setGridColumnData(strtoupper($this->getObjectId()), false, implode(",", $data));
    }

}

?>