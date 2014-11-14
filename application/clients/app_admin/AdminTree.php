<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith
// Date: 24.07.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

class AdminTree {

    protected $objectName = null;
    protected $object = null;
    protected $modul = 'MODUL';
    protected $URL = "/";
    public $objectTitle = '';
    public $isTreeExpand = true;
    protected $baseParams = null;
    protected $saveParams = null;
    public $isOnClickContextMenu = false;
    public $isObjectCheckBox = false;
    public $isAsyncTreeNode = false;
    public $isMenuOnAddFolder = true;
    public $isMenuOnAddItem = true;
    public $isMenuOnRemove = true;
    public $isMenuOnExpand = true;
    public $isMenuOnCollapse = true;
    protected $asyncTreeNodeData = "[]";
    protected $menuItems = array();
    protected $tbarItems = array();
    protected $windowlocation = null;
    protected $onEmbeddedEvents = null;

    public function __construct($object, $modul)
    {

        $this->object = $object;

        $this->modul = $modul;
    }

    public function getObjectName()
    {

        if ($this->modul)
        {
            return "TREE." . strtoupper($this->object) . "_" . strtoupper($this->modul);
        }
        else
        {
            return "TREE." . strtoupper($this->object);
        }
    }

    public function getObjectId()
    {

        return strtoupper($this->getObjectName()) . "_ID";
    }

    public function getObjectXType()
    {
        return "XTYPE_" . strtoupper($this->getObjectName()) . "_TREE";
    }

    protected function setTreeNode()
    {
        return $this->isAsyncTreeNode ? $this->getAsyncTreeNode() : $this->getTreeLoader();
    }

    public function setAsyncTreeNode($value)
    {
        return $this->asyncTreeNodeData = $value;
    }

    public function addMenuItems($value)
    {
        $this->items[] = "{" . $value . "}";

        return $this->items;
    }

    public function addTBarItems($value)
    {

        $this->tbarItems[] = "'-',{" . $value . "}";
        return $this->tbarItems;
    }

    protected function setMenuItems()
    {
        return implode(",", $this->items);
    }

    protected function setTBarItems()
    {

        if ($this->tbarItems)
        {
            return implode(",", $this->tbarItems);
        }
    }

    protected function setTreeExpand()
    {

        if ($this->isAsyncTreeNode)
        {

            $this->isTreeExpand = false;
            $this->isOnClickContextMenu = false;
        }
        if ($this->isTreeExpand)
        {
            return $this->getTreeExpand();
        }
    }

    public function setBaseParams($value)
    {

        if ($value != "")
            return $this->baseParams = $value;
    }

    public function setSaveParams($value)
    {

        return $this->saveParams = "," . $value;
    }

    public function setAyncTreeNodeData($value)
    {

        return $this->asyncTreeNodeData = $value;
    }

    public function setWindowLocation($value)
    {

        if ($value)
        {
            return $this->windowlocation = "window.location='" . $value . "'";
        }
        else
        {
            return $this->windowlocation = "";
        }
    }

    public function setURL($value)
    {

        return $this->URL = $value;
    }

    public function setSaveUrl($value)
    {
        return $this->saveUrl = $value;
    }

    protected function saveURL()
    {

        if ($this->saveUrl)
        {
            return "'" . $this->saveUrl . "'";
        }
        else
        {
            return "URL_JSONSAVE_" . strtoupper($this->object);
        }
    }

    public function setOnEmbeddedEvents($value)
    {
        return $this->onEmbeddedEvents = $value;
    }

    public function renderJS()
    {

        $js = "";
        $js .= "Ext.namespace('TREE');";
        $js .= "" . $this->getObjectName() . " = Ext.extend(Ext.tree.TreePanel, {";
        $js .= "title: '" . $this->objectTitle . "'";
        $js .= ",id: '" . $this->getObjectId() . "'";
        $js .= ",autoWidth: true";
        $js .= ",autoScroll: true";
        $js .= ",loader: new Ext.tree.TreeLoader()";
        $js .= ",rootVisible: false";
        $js .= ",bodyStyle: 'padding:10px; background-color: #FFFFFF'";
        $js .= ",border: false";
        $js .= ",initComponent: function(){";
        $js .= "Ext.apply(this, {
            tbar: [" . $this->setTBarItems() . "]
            ," . $this->setListenersCheckchange() . "
            }
        )

        " . $this->setTreeNode() . "
        " . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);";
        $js .= "}";
        $js .= "," . $this->onRender() . "";
        $js .= "," . $this->onTBarRefresh() . "";
        $js .= "," . $this->onMenushow() . "";
        $js .= "," . $this->onTBarAddParentItem() . "";
        $js .= "," . $this->onAddFolder() . "";
        $js .= "," . $this->setOnTBarAddParentItem() . "";
        $js .= "," . $this->createOnlyItem() . "";
        $js .= "," . $this->onAddItem() . "";
        $js .= "," . $this->onExpand() . "";
        $js .= "," . $this->onCollapse() . "";
        $js .= "," . $this->onRemoveNode() . "";
        $js .= "," . $this->onSaveCheckItems() . "";
        $js .= "});";
        $js .= "Ext.reg('" . $this->getObjectXType() . "', " . $this->getObjectName() . ");";

        return print$js;
    }

    protected function onRender()
    {

        $js = "";
        $js .= "onRender:function() {";
        $js .= "" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);";
        $js .= "var tbar = this.getTopToolbar();";
        $js .= "" . $this->onRightClickContextMenu() . "";
        $js .= "" . $this->setTreeExpand() . "";
        $js .= "}";

        return $js;
    }

    protected function onTBarRefresh()
    {

        $js = "";
        $js .= "onTBarRefresh: function(){";
        $js .= "this.root.reload();this.getRootNode().expand(true, false);";
        $js .= "}";

        return $js;
    }

    protected function onMenushow()
    {

        $js = "";
        $js .= "onMenushow: function(node,e){";
        $js .= "node.select();";
        $js .= "" . $this->getExtMenu() . "";
        $js .= "}";

        return $js;
    }

    protected function onAddFolder()
    {

        $js = "";
        $js .= "onAddFolder: function(){";
        $js .= "clickId = this.getSelectionModel().getSelectedNode().attributes.id;";
        $js .= "parent.Ext.MessageBox.prompt('Add a new Folder', 'Please enter new Name', showResultText);";
        $js .= "function showResultText(btn,text){";

        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'addFolder'";
        $js .= ",parentId: clickId";
        $js .= ",name: text";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').root.reload();";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function setOnTBarAddParentItem()
    {

        $js = "";
        $js .= "onTBarAddParentItem: function(){";
        $js .= "parent.Ext.MessageBox.prompt('Add a new Item', 'Please enter new Name:', showResultText);";
        $js .= "function showResultText(btn,text){";
        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'addFolder'";
        $js .= ",parentId: 0";
        $js .= ",name: text";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').root.reload();";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function getTreeLoader()
    {

        $js = "";
        $js .= "this.loader = new Ext.tree.TreeLoader({";
        $js .= "dataUrl: '" . $this->URL . "'";
        $js .= ",baseParams: {" . $this->baseParams . "}";
        $js .= "});";

        $js .= "this.root = new Ext.tree.AsyncTreeNode({";
        $js .= "draggable: false";
        $js .= ",id: '0'";
        $js .= "})";

        return $js;
    }

    protected function getAsyncTreeNode()
    {

        $js = "";
        $js .= "this.root = new Ext.tree.AsyncTreeNode({";
        $js .= "children: " . $this->asyncTreeNodeData . "";
        $js .= "})";

        return $js;
    }

    protected function onRightClickContextMenu()
    {

        if ($this->isAsyncTreeNode)
        {

            $this->isOnClickContextMenu = false;
        }

        return $this->isOnClickContextMenu ? "this.on('contextmenu', this.onMenushow, this);" : "";
    }

    protected function getExtMenu()
    {

        if ($this->isMenuOnAddFolder)
            $this->addMenuItems($this->getMenuOnAddFolder());
        if ($this->isMenuOnAddItem)
            $this->addMenuItems($this->getMenuOnAddItem());
        if ($this->isMenuOnExpand)
            $this->addMenuItems($this->getMenuOnExpand());
        if ($this->isMenuOnCollapse)
            $this->addMenuItems($this->getMenuOnCollapse());
        if ($this->isMenuOnRemove)
            $this->addMenuItems($this->getMenuOnRemove());

        $js = "";
        $js .= "if(!this.menu) {";
        $js .= "this.menu = new Ext.menu.Menu({";
        $js .= "id: 'concepts-ctx'";
        $js .= ",items: [" . $this->setMenuItems() . "]";
        $js .= "});";
        $js .= "}";
        $js .= "" . $this->removeMenuItemsGet() . "";
        $js .= "" . $this->addFolderMenuItemsGet() . "";
        $js .= "" . $this->addItemMenuItemsGet() . "";
        $js .= "" . $this->expandMenuItemsGet() . "";
        $js .= "" . $this->collapseMenuItemsGet() . "";
        $js .= "this.menu.showAt(e.getXY());";
        $js .= "e.stopEvent();";

        return $js;
    }

    protected function getMenuOnAddFolder()
    {

        $js = "";
        $js .= "id:'ADDFOLDER'";
        $js .= ",scope:this";
        $js .= ",handler: this.onAddFolder";
        $js .= ",iconCls:'icon-folder_add'";
        $js .= ",text:'Add a new folder'";

        return $js;
    }

    protected function getMenuOnAddItem()
    {

        $js = "";
        $js .= "id:'ADDITEM'";
        $js .= ",scope:this";
        $js .= ",handler: this.onAddItem";
        $js .= ",iconCls:'icon-page_add'";
        $js .= ",text:'Add a new item'";

        return $js;
    }

    protected function getMenuOnExpand()
    {

        $js = "";
        $js .= "id:'EXPAND'";
        $js .= ",scope:this";
        $js .= ",handler: this.onExpand";
        $js .= ",iconCls:'icon-expand-all'";
        $js .= ",text:'Expand'";

        return $js;
    }

    protected function getMenuOnCollapse()
    {

        $js = "";
        $js .= "id:'COLLAPSE'";
        $js .= ",scope:this";
        $js .= ",handler: this.onCollapse";
        $js .= ",iconCls:'icon-collapse-all'";
        $js .= ",text:'Collapse'";

        return $js;
    }

    protected function getMenuOnRemove()
    {

        $js = "";
        $js .= "id:'REMOVE'";
        $js .= ",scope:this";
        $js .= ",handler: this.onRemoveNode";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",text: 'Remove'";

        return $js;
    }

    protected function removeMenuItemsGet()
    {

        $js = "this.menu.items.get('remove')[node.attributes.allowDelete ? 'enable' : 'disable']();";

        return $this->isMenuOnRemove ? $js : "";
    }

    protected function addFolderMenuItemsGet()
    {

        $js = "this.menu.items.get('addfolder')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnAddFolder ? $js : "";
    }

    protected function addItemMenuItemsGet()
    {

        $js = "this.menu.items.get('additem')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnAddItem ? $js : "";
    }

    protected function expandMenuItemsGet()
    {

        $js = "this.menu.items.get('expand')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnExpand ? $js : "";
    }

    protected function collapseMenuItemsGet()
    {

        $js = "this.menu.items.get('collapse')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnCollapse ? $js : "";
    }

    protected function setListenersCheckchange()
    {
        $js = "";
        $js .= "listeners: {";
        $js .= "'checkchange': function(node, checked){";
        $js .= "if(checked){";
        $js .= "node.getUI().addClass('complete');";
        $js .= "}else{";
        $js .= "node.getUI().removeClass('complete');";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function getTreeExpand()
    {

        return "this.getRootNode().expand(true, false);";
    }

    public function ExtgetCmp()
    {
        return "Ext.getCmp('" . $this->getObjectId() . "')";
    }

    protected function onTBarAddParentItem()
    {
        $js = "";
        $js .= "onTBarAddParentItem: function(){";
        $js .= "parent.Ext.MessageBox.prompt('Add a new Item', 'Please enter new Name:', showResultText);";
        $js .= "function showResultText(btn,text){";

        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'additem'";
        $js .= ",parentId: 0";
        $js .= ",name: text";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').root.reload();";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function onExpand()
    {

        $js = "";
        $js .= "onExpand: function(){";
        $js .= "this.getRootNode().expand(true, false);";
        $js .= "}";

        return $js;
    }

    protected function onCollapse()
    {

        $js = "";
        $js .= "onCollapse: function(){";
        $js .= "this.getRootNode().collapse(true, false);";
        $js .= "}";

        return $js;
    }

    protected function onSaveCheckItems()
    {

        $js = "";
        $js .= "onSaveCheckItems: function(){";

        $js .= "var chooseTexts = '', selNodes = this.getChecked();";
        $js .= "var chooseIds = '', selNodes = this.getChecked();";

        $js .= "Ext.each(selNodes, function(node){";
        $js .= "if(chooseTexts.length > 0){";
        $js .= "chooseTexts += ', ';";
        $js .= "chooseIds += ',';";
        $js .= "}";
        $js .= "chooseTexts += node.text;";
        $js .= "chooseIds += node.id;";
        $js .= "});";

        $js .= "Ext.Msg.show({";
        $js .= "title: 'Completed Tasks'";
        $js .= ",msg: chooseTexts.length > 0 ? chooseTexts : 'None'";
        $js .= ",icon: Ext.Msg.INFO";
        $js .= ",minWidth: 250";
        $js .= ",fn: function(btn){";
        $js .= "if (btn == 'ok'){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "checkItems: chooseIds";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "" . $this->onEmbeddedEvents . "";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= ",buttons: Ext.Msg.OK";
        $js .= "});";
        $js .= "}";

        return $js;
    }

    protected function onRemoveNode()
    {

        $js = "";
        $js .= "onRemoveNode: function(){";
        $js .= "clickId = this.getSelectionModel().getSelectedNode().attributes.id;";
        $js .= "parent.Ext.MessageBox.confirm('Confirmation', 'After activating this Item it will be visible to a...', showResult);";
        $js .= "function showResult(btn){";
        $js .= "if (btn == 'yes'){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'removenode'";
        $js .= ",objectId: clickId";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "" . $this->windowlocation . "";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "};";
        $js .= "}";

        return $js;
    }

    protected function onAddItem()
    {

        $js = "";
        $js .= "onAddItem: function(){";
        $js .= "clickId = this.getSelectionModel().getSelectedNode().attributes.id;";
        $js .= "parent.Ext.MessageBox.prompt('Add a new Item', 'Please enter new Name:', showResultText);";
        $js .= "function showResultText(btn,text){";
        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'additem'";
        $js .= ",parentId: clickId";
        $js .= ",name: text";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').root.reload();";
        #$js .= "Ext.getCmp('".$this->getObjectId()."').getRootNode().expand(true, false);";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function createOnlyItem()
    {

        $js = "";
        $js .= "createOnlyItem: function(){";
        $js .= "parent.Ext.MessageBox.prompt('Add a new Item', 'Please enter new Name:', showResultText);";
        $js .= "function showResultText(btn,text){";
        $js .= "if (btn == 'ok'){";
        $js .= "if (text !=''){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";
        $js .= "url: '" . $this->URL . "'";
        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'createOnlyItem'";
        $js .= ",name: text";
        $js .= "" . $this->saveParams . "";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "Ext.getCmp('" . $this->getObjectId() . "').root.reload();";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "}";
        $js .= "}";
        $js .= "}";

        return $js;
    }

}

?>