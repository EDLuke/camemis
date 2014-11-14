<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 30.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class AdminGrid {

    public $forceFit = "true";
    public $loadType = "jsonload";
    protected $URL =  "/";
    public $loadMask = false;
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

    public function __construct($object, $modul) {

        $this->object = $object;
        $this->modul = $modul;
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

    public function setURL($value) {

        return $this->URL = $value;
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

    public function renderJS() {

        $js = "";
        if ($this->isGroupingView) {
            $js .= "var summary = new Ext.ux.grid.HybridSummary();";
        }
        $js .= "Ext.ns('Extensive.grid');";
        $js .= $this->setCellRenderers();
        $js .= "Ext.namespace('GRID');";
        $js .= "var CheckboxSelect = new Ext.grid.CheckboxSelectionModel({});";
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
        $js .= "" . $this->CheckboxSelect() . "";
        $js .= "" . $this->setColumns() . "";
        $js .= "]";

        if ($this->isGroupingView) {
            $js .= ",plugins: summary";
        }

        $js .= "" . $this->setSM() . "";
        $js .= " ,viewConfig:{forceFit:" . $this->forceFit . "}";
        $js .= "});";
        $js .= "" . $this->setStore() . "";
        $js .= "" . $this->setBufferView() . "";
        if ($this->isPagingToolbar)
            $js .= "" . $this->PagingToolbar() . "";
        $js .= "" . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);";
        $js .= "}";
        $js .= ",columnLines: true";
        if ($this->loadMask)
            $js .= ",loadMask: new Ext.LoadMask(Ext.getBody(), {msg:'CAMEMIS Loading...'})";
        $js .= ",trackMouseOver: true";
        $js .= ",buttonAlign:'center'";
        $js .= "" . $this->setClicksToEdit() . "";
        $js .= "" . $this->setListeners() . "";
        $js .= ",onRender:function() {";
        $js .= "" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);";
        $js .= "" . $this->getTopToolbar() . "";
        $js .= "" . $this->setQuickySearch() . "";
        $js .= "" . $this->setTBarItems() . "";
        $js .= "" . $this->setObjectDefaultOnLoad() . "";
        $js .= "}";
        $js .= "," . $this->onSelection() . "";
        $js .= "," . $this->onRemoveSelection() . "";
        $js .= "," . $this->onRemove() . "";
        $js .= "," . $this->onAddItem() . "";
        $js .= " }";
        $js .= "});";

        $js .= "Ext.reg('" . $this->getObjectXType() . "', " . $this->getObjectName() . ");";

        return print$js;
    }

    protected function getGroupingStore() {
        $js = "";
        $js .= "this.store = new Ext.data.GroupingStore({";
        $js .= "proxy: new Ext.data.HttpProxy({url: '" . $this->URL . "', method: 'POST'})";
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
        $js .= "proxy: new Ext.data.HttpProxy({url: '" . $this->URL . "', method: 'POST'})";
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

    protected function setQuickySearch() {
        $js = "";
        $js .= "tbar.add('-');";
        $js .= "tbar.add('<b>Quicky Search:</b> ', ' ',";
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
        if ($this->isBufferView) {
            $js = "
            this.view = new Ext.ux.grid.BufferView({
                // custom row height
                rowHeight: 110,
                // render rows as they come into viewable area.
                scrollDelay: true
            });
            ";
        } else {
            $js = "";
        }
        return $js;
    }

    protected function setListeners() {
        $js = "";
        if ($this->isGridEditing) {
            $js .= ",listeners: {";

            $js .= "afteredit: function (e){";

            $js .= "var objectId = e.record.get('ID');";
            $js .= "if (e.field != 'STATUS' && e.record.get('STATUS') == 1){";

            $js .= "Ext.MessageBox.show({";
            $js .= "title: 'Action status'";
            $js .= ",msg: 'The data cannot be saved!'";
            $js .= ",width:250";
            $js .= ",buttons: Ext.MessageBox.OK";
            $js .= ",icon: Ext.MessageBox.WARNING";
            $js .= "
            ,fn: function(btn){
                if (btn == 'ok'){
                    Ext.getCmp('" . $this->getObjectId() . "').store.reload();
                }
            }
            ";
            $js .= "});";

            $js .= "}else{";

            $js .= "var connection = new Ext.data.Connection();";
            $js .= "connection.request({";
            $js .= "url: '" . $this->URL . "'";
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
            $js .= "" . $this->editEmbeddedEvents . "";
            $js .= "this.getStore().commitChanges();";
            $js .= "}";
            $js .= "}";
            $js .= ",failure: function (respon, opt) {";
            $js .= "e.reject();";
            $js .= "}";
            $js .= "});";
            $js .= "}";
            $js .= "}";
            $js .= "}";
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
            return $js = ",clicksToEdit: 1";
    }

    protected function getSelectionModel() {
        if ($this->isGridEditing) {
            $js = "";
            $js .= "var rc = this.getSelectionModel().getSelectedCell();";
            $js .= "if (rc){";
            $js .= "var record = this.getStore().getAt(rc[0]);";
            $js .= "if (record){";
            $js .= "if(record.data.STATUS == 1){";
            $js .= "Ext.MessageBox.show({";
            $js .= "title: 'Remove'";
            $js .= ",msg: record.data." . $this->removeNAME . " + '<br>cannot be removed, because it is active !'";
            $js .= ",width:250";
            $js .= ",buttons: Ext.MessageBox.OK";
            $js .= ",icon: Ext.MessageBox.WARNING";
            $js .= "});";
            $js .= "}else{";
            $js .= "var recordId = record.data." . $this->removeID . ";";
            $js .= "var recordName = record.data." . $this->removeNAME . ";";
            $js .= "}";
            $js .= "}";
            $js .= "}";
        } else {
            $js = "";
            $js .= "var sm = this.getSelectionModel();";
            $js .= "var record = sm.getSelected();";
            $js .= "if (record){";
            $js .= "if(record.data.STATUS == 1){";
            $js .= "Ext.MessageBox.show({";
            $js .= "title: 'Remove'";
            $js .= ",msg: record.data." . $this->removeNAME . " + '<br>cannot be removed, because it is active !'";
            $js .= ",width:250";
            $js .= ",buttons: Ext.MessageBox.OK";
            $js .= ",icon: Ext.MessageBox.WARNING";
            $js .= "});";
            $js .= "}else{";
            $js .= "var recordId = record.data." . $this->removeID . ";";
            $js .= "var recordName = record.data." . $this->removeNAME . ";";
            $js .= "}";
            $js .= "}";
        }

        return $js;
    }

    protected function actionSelection() {
        $js = "";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
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
        $js .= "'Action status'";
        $js .= ",jsonData.selectedCount + ' Item(s) have been added successfully.'";
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
        $js .= "url: '" . $this->URL . "'";
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
        $js .= "'Action status'";
        $js .= ",jsonData.selectedCount + ' Item(s) have been removed successfully.'";
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
            $js = "
	    		,width: " . $this->objectWidth . "
	    	";
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
        $js .= "Ext.MessageBox.alert('Warning', 'Please choose...');";
        $js .= "}else{";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'Apply'";
        $js .= ",msg: 'Do you want to accept the choice?'";
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
        $js .= "Ext.MessageBox.alert('Warning', 'Please choose...');";
        $js .= "}else{";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'Apply'";
        $js .= ",msg: 'Do you want to accept the choice?'";
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
        $js .= "title:'Remove?'";
        $js .= ",msg: 'Remove ' + recordName + '?'";
        $js .= ",buttons: Ext.MessageBox.YESNO";
        $js .= ",icon: Ext.MessageBox.QUESTION";
        $js .= ",fn: function (btn) {";
        $js .= "if (btn == 'yes'){";
        $js .= "Ext.Ajax.request({";
        $js .= "url: URL_JSONSAVE_" . strtoupper($this->object) . "";
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
        $js .= "parent.Ext.MessageBox.prompt('Add', 'Please enter new Name:', showResultText);";
        $js .= "function showResultText(btn,text){";
        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: URL_JSONSAVE_" . strtoupper($this->object) . "";
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
}

?>