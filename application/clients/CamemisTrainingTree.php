<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith
// Date: 03.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'clients/CamemisField.php';
require_once "models/UserAuth.php";
require_once setUserLoacalization();

class CamemisTrainingTree {

    protected $objectName = null;
    protected $URL = "/";
    protected $object = null;
    public $objectTitle = '';
    public $backgroundColor = '#FFFFFF';
    public $objectWidth = 250;
    public $isTreeExpand = true;
    protected $baseParams = null;
    protected $saveParams = null;
    public $isOnClickContextMenu = true;
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
    public $saveUrl = null;

    public function __construct($object) {

        $this->object = $object;
        $this->utiles = Utiles::getInstance();
    }

    public function getObjectName() {

        return "TREE." . strtoupper($this->object);
    }

    public function getObjectId() {

        return strtoupper($this->object) . "_TREE_ID";
    }

    public function getObjectXType() {

        return "XTYPE_" . strtoupper($this->object) . "_TREE";
    }

    protected function setTreeNode() {
        return $this->isAsyncTreeNode ? $this->getAsyncTreeNode() : $this->getTreeLoader();
    }

    public function setAsyncTreeNode($value) {
        return $this->asyncTreeNodeData = $value;
    }

    public function addMenuItems($value) {
        $this->items[] = "{" . $value . "}";

        return $this->items;
    }

    public function addTBarItems($value) {
        $this->tbarItems[] = "'-',{" . $value . "}";

        return $this->tbarItems;
    }

    protected function setMenuItems() {
        return implode(",", $this->items);
    }

    protected function setTBarItems() {

        if ($this->tbarItems) {
            return implode(",", $this->tbarItems);
        }
    }

    public function setURL($value) {

        return $this->URL = $value;
    }

    public function setSaveUrl($value) {
        return $this->saveUrl = $value;
    }

    protected function setTreeExpand() {

        if ($this->isAsyncTreeNode) {

            $this->isTreeExpand = false;
            $this->isOnClickContextMenu = false;
        }

        return $this->isTreeExpand ? $this->getTreeExpand() : "";
    }

    public function setBaseParams($value) {

        if ($value != "")
            return $this->baseParams = $value;
    }

    public function setSaveParams($value) {

        if ($value != "")
            return $this->saveParams = "," . $value;
    }

    public function setAyncTreeNodeData($value) {

        return $this->asyncTreeNodeData = $value;
    }

    public function setWindowLocation($value) {

        if ($value) {
            return $this->windowlocation = "window.location='" . $value . "'";
        } else {
            return $this->windowlocation = "";
        }
    }

    protected function saveURL() {

        if ($this->saveUrl) {
            return "" . $this->saveUrl . "";
        } else {
            return "URL_JSONSAVE_" . strtoupper($this->object);
        }
    }

    public function renderJS() {

        $js = "";
        $js .= "Ext.namespace('TREE');";
        $js .= "" . $this->getObjectName() . " = Ext.extend(Ext.tree.TreePanel, {";

        $js .= "title: '" . $this->objectTitle . "'";
        $js .= ",id: '" . $this->getObjectId() . "'";
        $js .= ",width: " . $this->objectWidth . "";
        $js .= ",autoScroll: true";
        $js .= ",loader: new Ext.tree.TreeLoader()";
        $js .= ",rootVisible: false";
        $js .= ",bodyStyle: 'padding:10px; background-color: " . $this->backgroundColor . "'";
        $js .= ",border: false";
        $js .= ",initComponent: function(){
                Ext.apply(this, {
                tbar: [" . $this->setTBarItems() . "]
                    ," . $this->setListenersCheckchange() . "
                }
        )";
        $js .= "" . $this->setTreeNode() . "";
        $js .= "" . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);";
        $js .= "},onRender:function() {";
        $js .= "" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);";
        $js .= "var tbar = this.getTopToolbar();";
        $js .= "" . $this->onRightClickContextMenu() . "";
        $js .= "" . $this->setTreeExpand() . "";
        $js .= "}";
        $js .= ",onTBarRefresh: function(){";
        $js .= "this.root.reload();this.getRootNode().expand(true, false);";
        $js .= "}";
        $js .= ",onMenushow: function(node,e){";
        $js .= "node.select();";
        if (UserAuth::getACLValue("ACADEMIC_TRAINING_PROGRAMS_EDIT_RIGHT"))
            $js .= "" . $this->getExtMenu() . "";
        $js .= "}";
        $js .= "," . $this->onaddprogram() . "";
        $js .= "," . $this->onAddLevel() . "";
        $js .= "," . $this->onAddTerm() . "";
        $js .= "," . $this->onAddClass() . "";
        $js .= "," . $this->onExpand() . "";
        $js .= "," . $this->onCollapse() . "";
        $js .= "," . $this->onRemoveNode() . "";
        $js .= "});";

        $js .= "Ext.reg('" . $this->getObjectXType() . "', " . $this->getObjectName() . ");";

        return print compressMultiSpacesToSingleSpace($js);
    }

    protected function onaddprogram() {

        $js = "";
        $js .= "onaddprogram: function(){";
        $js .= "var actionNode = this.getSelectionModel().getSelectedNode();";
        $js .= "var clickId = 0;";
        $js .= "var url = '" . $this->utiles->buildURL('training/addprogram', array("objectId" => "new", "template" => "PROGRAM"), true) . "&parentId=' + clickId;";
        $js .= "clickOpenPage('center','" . PROGRAM . "', url);";
        $js .= "}";

        return $js;
    }

    protected function onAddLevel() {

        $js = "";
        $js .= "onAddLevel: function(){";
        $js .= "var actionNode = this.getSelectionModel().getSelectedNode();";
        $js .= "var clickId = actionNode.attributes.id;";
        $js .= "var url = '" . $this->utiles->buildURL('training/addprogram', array("objectId" => "new", "template" => "LEVEL"), true) . "&parentId=' + clickId;";
        $js .= "clickOpenPage('center','" . LEVEL . "', url);";
        $js .= "}";

        return $js;
    }

    public function onAddTerm() {

        $js = "";
        $js .= "onAddTerm: function(){";
        $js .= "var actionNode = this.getSelectionModel().getSelectedNode();";
        $js .= "var term_number = actionNode.attributes.term_number;";
        $js .= "var clickId = actionNode.attributes.id;";
        $js .= "var url = '" . $this->utiles->buildURL('training/addprogram', array("objectId" => "new", "template" => "TERM"), true) . "&parentId=' + clickId;";
        $js .= "clickOpenPage('center','" . TERM . "', url);";
        $js .= "}";

        return $js;
    }

    protected function onAddClass() {

        $js = "";
        $js .= "onAddClass: function(){";
        $js .= "var actionNode = this.getSelectionModel().getSelectedNode();";
        $js .= "var clickId = actionNode.attributes.id;";
        $js .= "var url = '" . $this->utiles->buildURL('training/addprogram', array("objectId" => "new", "template" => "CLASS"), true) . "&parentId=' + clickId + '&objecttype=CLASS';";
        $js .= "clickOpenPage('center','" . ADD_A_NEW_CLASS . "', url);";
        $js .= "}";

        return $js;
    }

    protected function onExpand() {

        $js = "";
        $js .= "onExpand: function(){";
        $js .= "this.getRootNode().expand(true, false);";
        $js .= "}";

        return $js;
    }

    protected function onCollapse() {

        $js = "";
        $js .= "onCollapse: function(){";
        $js .= "this.getRootNode().collapse(true, false);";
        $js .= "}";

        return $js;
    }

    protected function onRemoveNode() {

        $js = "";
        $js .= "onRemoveNode: function(){";
        $js .= "mytree = this; clickId = this.getSelectionModel().getSelectedNode().id;parentId = this.getSelectionModel().getSelectedNode().attributes.parentId;";
        $js .= "parent.Ext.MessageBox.confirm('" . CONFIRM . "', '" . DELETE_THIS_ITEM . "', showResult);";
        $js .= "function showResult(btn){";
        $js .= "if (btn == 'yes'){";
        $js .= "var connection = new Ext.data.Connection();";
        $js .= "connection.request({";

        $js .= "url: '" . $this->saveURL() . "'";

        $js .= ",scope:this";
        $js .= ",params: {";
        $js .= "cmd: 'removenode'";
        $js .= ",objectId: clickId";
        $js .= "}";
        $js .= ",method: 'POST'";
        $js .= ",success: function (result) {";
        $js .= "jsonData = Ext.util.JSON.decode(result.responseText);";
        $js .= "if (jsonData) {";
        $js .= "
            if(parentId){
                myNode = mytree.getNodeById(parentId);
                myNode.reload();
                myNode.expand(true, false);
            }else{
                window.location='" . $_SERVER["REQUEST_URI"] . "';
            }
        ";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";
        $js .= "};";
        $js .= "}";

        return $js;
    }

    protected function getTreeLoader() {
        $js = "";
        $js .= "
            this.loader = new Ext.tree.TreeLoader({
                dataUrl: '" . $this->URL . "'
                ,baseParams: {" . $this->baseParams . "}
            });

            this.root = new Ext.tree.AsyncTreeNode({
                draggable: false
                ,id: '0'
            })
            ";

        return $js;
    }

    protected function getAsyncTreeNode() {

        $js = "";
        $js .= "
        this.root = new Ext.tree.AsyncTreeNode({
            children: " . $this->asyncTreeNodeData . "
        })
        ";

        return $js;
    }

    protected function onRightClickContextMenu() {

        if ($this->isAsyncTreeNode) {

            $this->isOnClickContextMenu = false;
        }

        return $this->isOnClickContextMenu ? "this.on('contextmenu', this.onMenushow, this);" : "";
    }

    protected function getExtMenu() {

        $this->addMenuItems($this->getMenuOnTerm());
        $this->addMenuItems($this->getMenuOnAddGrade());
        $this->addMenuItems($this->getMenuOnAddClass());

        if ($this->isMenuOnExpand)
            $this->addMenuItems($this->getMenuOnExpand());
        if ($this->isMenuOnCollapse)
            $this->addMenuItems($this->getMenuOnCollapse());
        if ($this->isMenuOnRemove)
            $this->addMenuItems($this->getMenuOnRemove());

        $js = "";
        $js .= "
        if(!this.menu) {
            this.menu = new Ext.menu.Menu({
                id: 'concepts-ctx'
                ,items: [" . $this->setMenuItems() . "]
            });
        }
        " . $this->removeMenuItemsGet() . "
        " . $this->addFolderMenuItemsGet() . "
        " . $this->addprogramMenuItemsGet() . "
        " . $this->addLevelMenuItemsGet() . "
        " . $this->addTermMenuItemsGet() . "
        " . $this->addClassMenuItemsGet() . "
        " . $this->expandMenuItemsGet() . "
        " . $this->collapseMenuItemsGet() . "
        this.menu.showAt(e.getXY());
        e.stopEvent();
            ";

        return $js;
    }

    protected function getMenuonaddprogram() {

        $js = "";
        $js .= "id:'PROGRAM'";
        $js .= ",scope:this";
        $js .= ",handler: this.onaddprogram";
        $js .= ",iconCls:'icon-folder_add'";
        $js .= ",text:'<b>" . PROGRAM . "</b>'";

        return $js;
    }

    protected function getMenuOnAddGrade() {

        $js = "";
        $js .= "id:'LEVEL'";
        $js .= ",scope:this";
        $js .= ",handler: this.onAddLevel";
        $js .= ",iconCls:'icon-folder_add'";
        $js .= ",text:'<b>" . LEVEL . "</b>'";

        return $js;
    }

    protected function getMenuOnAddClass() {

        $js = "";
        $js .= "id:'CLASS'";
        $js .= ",scope:this";
        $js .= ",handler: this.onAddClass";
        $js .= ",diabled: true";
        $js .= ",iconCls:'icon-blackboard'";
        $js .= ",text:'<b>" . ADD_A_NEW_CLASS . "</b>'";

        return $js;
    }

    protected function getMenuOnAddFolder() {

        $js = "";
        $js .= "id:'folder'";
        $js .= ",scope:this";
        $js .= ",handler: this.onAddFolder";
        $js .= ",iconCls:'icon-folder_add'";
        $js .= ",text:'<b>" . ADD_A_NEW_FOLDER . "</b>'";

        return $js;
    }

    protected function getMenuOnExpand() {

        $js = "";
        $js .= "id:'expand'";
        $js .= ",scope:this";
        $js .= ",handler: this.onExpand";
        $js .= ",iconCls:'icon-expand-all'";
        $js .= ",text:'<b>" . EXPAND . "</b>'";

        return $js;
    }

    protected function getMenuOnCollapse() {

        $js = "";
        $js .= "id:'collapse'";
        $js .= ",scope:this";
        $js .= ",handler: this.onCollapse";
        $js .= ",iconCls:'icon-collapse-all'";
        $js .= ",text:'<b>" . COLLAPSE . "</b>'";

        return $js;
    }

    protected function getMenuOnRemove() {

        $js = "";
        $js .= "id:'remove'";
        $js .= ",scope:this";
        $js .= ",handler: this.onRemoveNode";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",text:'<b>" . REMOVE . "</b>'";

        return $js;
    }

    protected function getMenuOnTerm() {

        $js = "";
        $js .= "id:'TERM'";
        $js .= ",scope:this";
        $js .= ",handler: this.onAddTerm";
        $js .= ",iconCls:'icon-date_add'";
        $js .= ",text:'<b>" . TERM . "</b>'";

        return $js;
    }

    protected function removeMenuItemsGet() {

        $js = "this.menu.items.get('remove')[node.attributes.allowDelete ? '" . $this->removeStatus() . "' : 'disable']();";

        return $this->isMenuOnRemove ? $js : "";
    }

    protected function addprogramMenuItemsGet() {

        $js = "";

        $js .= "if (node.attributes.objecttype == 'PROGRAM'){";
        $js .= "this.menu.items.get('LEVEL')['" . $this->createStatus() . "']();";
        $js .= "this.menu.items.get('TERM')['disable']();";
        $js .= "this.menu.items.get('CLASS')['disable']();";
        $js .= "}";

        return $js;
    }

    protected function addTermMenuItemsGet() {

        $js = "";
        $js .= "if (node.attributes.objecttype == 'TERM'){";
        $js .= "this.menu.items.get('LEVEL')['disable']();";
        $js .= "this.menu.items.get('TERM')['disable']();";
        $js .= "this.menu.items.get('CLASS')['" . $this->createStatus() . "']();";

        $js .= "}";

        return $js;
    }

    protected function addFolderMenuItemsGet() {

        $js = "";
        $js .= "if (node.attributes.objecttype == 'FOLDER'){";
        $js .= "this.menu.items.get('TERM')['disable']();";
        $js .= "this.menu.items.get('LEVEL')['disable']();";
        $js .= "this.menu.items.get('CLASS')['disable']();";
        $js .= "}";

        return $js;
    }

    protected function addLevelMenuItemsGet() {

        $js = "";
        $js .= "if (node.attributes.objecttype == 'LEVEL'){";
        $js .= "this.menu.items.get('LEVEL')['disable']();";
        $js .= "this.menu.items.get('TERM')['" . $this->createStatus() . "']();";
        $js .= "this.menu.items.get('CLASS')['disable']();";

        $js .= "}";

        return $js;
    }

    protected function addClassMenuItemsGet() {

        $js = "";
        $js .= "if (node.attributes.objecttype == 'CLASS'){";
        $js .= "this.menu.items.get('TERM')['disable']();";
        $js .= "this.menu.items.get('LEVEL')['disable']();";
        $js .= "this.menu.items.get('CLASS')['disable']();";
        $js .= "}";

        return $js;
    }

    protected function expandMenuItemsGet() {

        $js = "this.menu.items.get('expand')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnExpand ? $js : "";
    }

    protected function collapseMenuItemsGet() {

        $js = "this.menu.items.get('collapse')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnCollapse ? $js : "";
    }

    protected function setListenersCheckchange() {
        $js = "
		
        listeners: {
            'checkchange': function(node, checked){
                if(checked){
                    node.getUI().addClass('complete');
                }else{
                    node.getUI().removeClass('complete');
                }
            }
        }
            ";

        return $js;
    }

    protected function getTreeExpand() {

        return "this.getRootNode().expand(true, false);";
    }

    public function ExtgetCmp() {
        return "Ext.getCmp('" . $this->getObjectId() . "')";
    }

    protected function callbackAddNode() {

        $js = "
        jsonData = Ext.util.JSON.decode(result.responseText);
        var tree = Ext.getCmp('" . $this->getObjectId() . "');
        actionNode.expand(false, false, function(n){
            n.appendChild(tree.loader.createNode({
                id: jsonData.id
                ,text: jsonData.text
                ,leaf: jsonData.leaf
                ,objecttype: jsonData.objecttype
                ,iconCls: jsonData.iconCls
                ,navtitle: jsonData.navtitle
                ,cls: jsonData.cls
                ,allowDelete: jsonData.allowDelete
                ,loaded:true
            }));
        }.createDelegate(tree));
        ";

        return $js;
    }

    protected function createStatus() {

        if (UserAuth::getACLValue("ACADEMIC_TRAINING_PROGRAMS_EDIT_RIGHT")) {

            $status = "enable";
        } else {
            $status = "diable";
        }

        return $status;
    }

    protected function removeStatus() {

        if (UserAuth::getACLValue("ACADEMIC_TRAINING_PROGRAMS_REMOVE_RIGHT")) {

            $status = "enable";
        } else {
            $status = "diable";
        }

        return $status;
    }

}

?>