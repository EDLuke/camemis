<?
///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once "models/".Zend_Registry::get('MODUL_API_PATH')."/BuildData.php";
require_once setUserLoacalization();

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 14.08.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
Class CamemisTreeCombo {

    public $data = array();
    private $modul = null;
    private $submodul = null;
    
    public function __construct($modul, $submodul) {

        $this->modul = $modul;
        $this->submodul = $submodul;
    }
    
    public function renderJS() {
        $js = "
		
		Ext.TreeCombo = Ext.extend(Ext.form.ComboBox, {
			fieldLabel: 'Grade'
			,anchor: '100%'
			,forceSelection:true
			,initList: function() {
				this.list = new Ext.tree.TreePanel({
					root: new Ext.tree.AsyncTreeNode({
					})
					,loader: new Ext.tree.TreeLoader({
						dataUrl: URL_JSONTREE_PROGRAMS
						,baseParams: {
							cmd: 'allPrograms'
						}
					})
					,floating: true
					,autoHeight: true
					,listeners: {
						click: this.onNodeClick,
						scope: this
					},
					alignTo: function(el, pos) {
						this.setPagePosition(this.el.getAlignToXY(el, pos));
					}
				});
			}
			,expand: function() {
				if (!this.list.rendered) {
					this.list.render(document.body);
					this.list.setWidth(this.el.getWidth());
					this.innerList = this.list.body;
					this.list.hide();
				}
				this.el.focus();
				Ext.TreeCombo.superclass.expand.apply(this, arguments);
			}
			,doQuery: function(q, forceAll) {
				this.expand();
			}
		    ,collapseIf : function(e){
		    	if(!e.within(this.wrap) && !e.within(this.list.el)){
		            this.collapse();
		        }
		    }
			,onNodeClick: function(node, e) {
				 if(node.isLeaf()){
				 	this.setRawValue(node.attributes.text);
				 	this.hiddenField.value = node.id;
				 }
				this.collapse();
			}
		});
		Ext.reg('treecombo', Ext.TreeCombo);
		
		";

        return print$js;
    }
}

?>