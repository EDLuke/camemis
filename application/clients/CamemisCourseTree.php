<?
///////////////////////////////////////////////////////////
// @Kaom Vibolrith
// Date: 24.07.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'clients/CamemisField.php';
require_once setUserLoacalization();

class CamemisCourseTree {

    protected $objectName = null;
    protected $object = null;
    public $objectTitle = '';
    public $objectWidth = 250;
    public $isTreeExpand = true;
    protected $baseParams = null;
    protected $saveParams = null;
    public $isOnClickContextMenu = true;
    public $isAsyncTreeNode = false;
    public $isMenuOnAddFolder = true;
    public $isMenuOnRemove = true;
    public $isMenuOnExpand = true;
    public $isMenuOnCollapse = true;
    protected $asyncTreeNodeData = "[]";
    protected $menuItems = array();
    protected $tbarItems = array();
    protected $windowlocation = null;
    
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
        return $this->isAsyncTreeNode?$this->getAsyncTreeNode():$this->getTreeLoader();
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
    
    protected function setTreeExpand() {

        if ($this->isAsyncTreeNode) {

            $this->isTreeExpand = false;
            $this->isOnClickContextMenu = false;
        }

        return $this->isTreeExpand?$this->getTreeExpand():"";
    }
    
    public function setBaseParams($value) {

        if ($value != "") return $this->baseParams = $value;
    }
    
    public function setSaveParams($value) {

        if ($value != "") return $this->saveParams = "," . $value;
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
    
    public function renderJS() {

        $js = "";
        $js .= "Ext.namespace('TREE');";
        $js .= "
				" . $this->getObjectName() . " = Ext.extend(Ext.tree.TreePanel, {
					
					title: '" . $this->objectTitle . "'
					,id: '" . $this->getObjectId() . "'
					,width: " . $this->objectWidth . "
					,autoScroll: true
					,loader: new Ext.tree.TreeLoader()
					,rootVisible: false
					,bodyStyle: 'padding:10px; background-color: #FFFFFF'
					,border: false
					,initComponent: function(){
		            	Ext.apply(this, {
		            		tbar: [" . $this->setTBarItems() . "]
							," . $this->setListenersCheckchange() . "
		            	}
		            )
		            
		            " . $this->setTreeNode() . "
		            
		            " . $this->getObjectName() . ".superclass.initComponent.apply(this, arguments);
		            
		        },onRender:function() {
		        	
		        	" . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);
		        	
		        	var tbar = this.getTopToolbar();
		        	
		        	" . $this->onRightClickContextMenu() . "
		        	
					" . $this->setTreeExpand() . "
					
		        }
		        ,onTBarRefresh: function(){
		        	
		        	this.root.reload();
		        	
		        }
		        ,onMenushow: function(node,e){
		        	
		        	node.select();
		        	
		        	" . $this->getExtMenu() . "
		        }
		        ,onAddCourse: function(){
		        	
		        	clickId = this.getSelectionModel().getSelectedNode().attributes.id;
		        	
					parent.Ext.MessageBox.prompt('Add a new term', 'Please enter new name:', showResultText);
					
					function showResultText(btn,text){
						
						if (btn == 'ok'){
							if (text !=''){
								var connection = new Ext.data.Connection();
								connection.request({
									url: URL_JSONSAVE_" . strtoupper($this->object) . "
									,scope:this
									,params: {
										cmd: 'addCourse'
										,objecttype: 'COURSE'
										,parentId: clickId
										,name: text
										" . $this->saveParams . "
									}
									,method: 'POST'
									,success: function (result) {
										jsonData = Ext.util.JSON.decode(result.responseText);
										if (jsonData) {
											Ext.getCmp('" . $this->getObjectId() . "').root.reload();
											Ext.getCmp('" . $this->getObjectId() . "').getRootNode().expand(true, false);
										}
									}
								});
							}
						}
					}
		        }
		        ,onAddSubject: function(){
		        	
					var addsubjectWin = new Ext.Window({
	                   title: '" . PLEASE_SELECT_A_SUBJECT . "'
						,modal: true
						,width: 400
						,height: 180
						,bodyStyle: 'background:#f7f7f7; padding:15px'
						,items:[{
	                    	xtype: 'form'
	                    	,id: 'ADD_SUBJECT_FORM'
	                    	,labelAlign: 'top'
	                    	,buttonAlign: 'center'
	                    	,bodyStyle: 'background:#f7f7f7; padding:15px'
	                    	,items:[{
	                    		" . CamemisField::ComboSubjcts() . "
	                    	}]
	                    	,buttons:[{
	                    		text: '" . CANCEL . "'
	                    		,handler: function() {
	                    			addsubjectWin.close();
	                    		}
	                    	},{
	                    		text: '" . SAVE . "'
	                    		,handler: function(){
	                    			var subjectId = Ext.getCmp('ADD_SUBJECT_FORM').getForm().findField('SUBJECT').getValue();
		                    		if (subjectId){
			                    		var connection = new Ext.data.Connection();
										connection.request({
											url: URL_JSONSAVE_" . strtoupper($this->object) . "
											,scope:this
											,params: {
												cmd: 'addGradeSubject'
												,objecttype: 'SUBJECT'
												,parentId: 0
												,subjectId: subjectId
												" . $this->saveParams . "
											}
											,method: 'POST'
											,success: function (result) {
												jsonData = Ext.util.JSON.decode(result.responseText);
												if (jsonData) {
													Ext.getCmp('" . $this->getObjectId() . "').root.reload();
													Ext.getCmp('" . $this->getObjectId() . "').getRootNode().expand(true, false);
												}
											}
										});
		                    			addsubjectWin.close();
		                    		}
		                    	}
	                    	}]
	                    }]
	               });
	                addsubjectWin.show();
		        }
		        
		        ,onAddTerm: function(){
		        	clickId = this.getSelectionModel().getSelectedNode().attributes.id;
					var addtermWin = new Ext.Window({
	                   title: '" . PLEASE_SELECT_A_TERM . "'
						,modal: true
						,width: 400
						,height: 180
						,bodyStyle: 'background:#f7f7f7; padding:15px'
						,items:[{
	                    	xtype: 'form'
	                    	,id: 'ADD_TERM_FORM'
	                    	,labelAlign: 'top'
	                    	,buttonAlign: 'center'
	                    	,bodyStyle: 'background:#f7f7f7; padding:15px'
	                    	,buttons:[{
	                    		text: '" . CANCEL . "'
	                    		,handler: function() {
	                    			addtermWin.close();
	                    		}
	                    	},{
	                    		text: '" . SAVE . "'
	                    		,handler: function(){
	                    			var termId = Ext.getCmp('ADD_TERM_FORM').getForm().findField('TERM').getValue();
		                    		if (termId){
			                    		var connection = new Ext.data.Connection();
										connection.request({
											url: URL_JSONSAVE_" . strtoupper($this->object) . "
											,scope:this
											,params: {
												cmd: 'addGradeSubject'
												,objecttype: 'TERM'
												,parentId: clickId
												,termIdex: termId
												" . $this->saveParams . "
											}
											,method: 'POST'
											,success: function (result) {
												jsonData = Ext.util.JSON.decode(result.responseText);
												if (jsonData) {
													Ext.getCmp('" . $this->getObjectId() . "').root.reload();
													Ext.getCmp('" . $this->getObjectId() . "').getRootNode().expand(true, false);
												}
											}
										});
		                    			addtermWin.close();
		                    		}
		                    	}
	                    	}]
	                    }]
	               });
	                addtermWin.show();
		        }
		        
		        ,onExpand: function(){
		        	this.getRootNode().expand(true, false);
		        }
		        ,onCollapse: function(){
		        	this.getRootNode().collapse(true, false);
		        }
		        ,onRemoveNode: function(){
		        	
		        	clickId = this.getSelectionModel().getSelectedNode().attributes.id;
		        	
		        	parent.Ext.MessageBox.confirm('Confirm', 'Are you sure you want to do that?', showResult);
					function showResult(btn){
						if (btn == 'yes'){
							var connection = new Ext.data.Connection();
							connection.request({
								url: URL_JSONSAVE_" . strtoupper($this->object) . "
								,scope:this
								,params: {
									cmd: 'removenode'
									,objectId: clickId
								}
								,method: 'POST'
								,success: function (result) {
									jsonData = Ext.util.JSON.decode(result.responseText);
									if (jsonData) {
										" . $this->windowlocation . "
									}
								}
							});
						}
					};
		        }
		    });
		    
		    Ext.reg('" . $this->getObjectXType() . "', " . $this->getObjectName() . ");
		";

        return print$js;
    }
    
    protected function getTreeLoader() {

        $js = "";
        $js .= "
			this.loader = new Ext.tree.TreeLoader({
				
				dataUrl: URL_JSONTREE_" . strtoupper($this->object) . "
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

        return $this->isOnClickContextMenu?"this.on('contextmenu', this.onMenushow, this);":"";
    }
    
    protected function getExtMenu() {

        $this->addMenuItems($this->getMenuOnAddTerm());

        $this->addMenuItems($this->getMenuOnAddCourse());

        if ($this->isMenuOnExpand) $this->addMenuItems($this->getMenuOnExpand());
        if ($this->isMenuOnCollapse) $this->addMenuItems($this->getMenuOnCollapse());
        if ($this->isMenuOnRemove) $this->addMenuItems($this->getMenuOnRemove());

        $js = "";
        $js .= "
			if(!this.menu) {
				this.menu = new Ext.menu.Menu({
					id: 'concepts-ctx'
					,items: [" . $this->setMenuItems() . "]
				});
			}
			
			" . $this->removeMenuItemsGet() . "
			
			" . $this->addSubjectMenuItemsGet() . "
			
			" . $this->addTermMenuItemsGet() . "
			
			" . $this->addCourseMenuItemsGet() . "
			
			" . $this->expandMenuItemsGet() . "
			
			" . $this->collapseMenuItemsGet() . "
			
			this.menu.showAt(e.getXY());
			
			e.stopEvent();
		";

        return $js;
    }
    
    protected function getMenuOnExpand() {
        $js = "";
        $js .= "
			id:'expand'
			,scope:this
			,handler: this.onExpand
			,iconCls:'icon-expand-all'
			,text:'<b>Expand</b>'
		";

        return $js;
    }
    
    protected function getMenuOnCollapse() {
        $js = "";
        $js .= "
			id:'collapse'
			,scope:this
			,handler: this.onCollapse
			,iconCls:'icon-collapse-all'
			,text:'<b>Collapse</b>'
		";

        return $js;
    }
    
    protected function getMenuOnRemove() {
        $js = "";
        $js .= "
			id:'remove'
			,scope:this
			,handler: this.onRemoveNode
			,iconCls:'icon-delete'
			,text:'<b>Remove</b>'
		";

        return $js;
    }
    
    protected function getMenuOnAddTerm() {
        $js = "";
        $js .= "
			id:'term'
			,scope:this
			,handler: this.onAddTerm
			,iconCls:'icon-date_add'
			,text:'<b>Add a new term</b>'
		";

        return $js;
    }
    
    protected function getMenuOnAddCourse() {
        $js = "";
        $js .= "
			id:'course'
			,scope:this
			,handler: this.onAddCourse
			,iconCls:'icon-page_add'
			,text:'<b>Add a new course</b>'
		";

        return $js;
    }
    
    protected function removeMenuItemsGet() {

        $js = "this.menu.items.get('remove')[node.attributes.allowDelete ? 'enable' : 'disable']();";

        return $this->isMenuOnRemove?$js:"";
    }
    
    protected function addSubjectMenuItemsGet() {

        $js = "
		if (node.attributes.objecttype == 'SUBJECT'){
			this.menu.items.get('term')['enable']();
			this.menu.items.get('course')['disable']();
		}
		";

        return $js;
    }
    
    protected function addTermMenuItemsGet() {

        $js = "
		if (node.attributes.objecttype == 'TERM'){
			this.menu.items.get('term')['disable']();
			this.menu.items.get('course')['enable']();
		}
		";

        return $js;
    }
    
    protected function addCourseMenuItemsGet() {

        $js = "
		if (node.attributes.objecttype == 'COURSE'){
			this.menu.items.get('term')['disable']();
			this.menu.items.get('course')['disable']();
		}
		";

        return $js;
    }
    
    protected function expandMenuItemsGet() {

        $js = "this.menu.items.get('expand')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnExpand?$js:"";
    }
    
    protected function collapseMenuItemsGet() {

        $js = "this.menu.items.get('collapse')[(node.attributes.type == 0) ? 'enable' : 'disable']();";

        return $this->isMenuOnCollapse?$js:"";
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
}

?>