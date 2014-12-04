<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 24.07.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CamemisWindow {

    protected $modul = null;
    public $isImageView = false;
    public $objectTitle = 'Default Title';
    public $contentType = "HTML";
    public $objectWidth = 650;
    public $objectHeight = 550;
    public $objectXType = null;
    private $contentIFRAME = "http://www.google.de";
    private $contentHTML = "Hallo World....";
    private $contentITEMS = null;

    public function __construct($object) {

        $this->object = $object;

        $this->utiles = Utiles::getInstance();
    }

    public function setContentITEMS($value) {
        return $this->contentITEMS = $value;
    }

    public function setContentHTML($value) {
        return $this->contentHTML = $value;
    }

    public function setContentIFRAME($value) {
        return $this->contentIFRAME = $value;
    }

    public function getObjectName() {
        return "WIN." . strtoupper($this->object);
    }

    public function getObjectId() {
        return strtoupper($this->object) . "_WIN_ID";
    }

    public function getObjectXType() {
        return "XTYPE_" . strtoupper($this->object) . "_WIN";
    }

    function jsAdd($id, $vnam, $grid) {
        $js = "
    xtype:'textfield'
    ,id: '" . $id . "'
    ,width: 150
    ,emptyText: '" . PLEASE_ENTER_NEW_NAME . "'
    ,listeners:{  
        scope:this 
        ,specialkey: function(f,e){  
            if(e.getKey()==e.ENTER){
                Ext.Ajax.request({
                    url: '/subject/jsonsave/'
                    ,method: 'POST'
                    ,params: {cmd: 'actionLessonplan'," . $vnam . ":Ext.getCmp('" . $id . "').getValue()}
                    ,success: function(response, options) {Ext.getCmp('" . $grid->getObjectId() . "').store.load();}
                    ,failure: function(response, options) {}
                });
            }  
        }  
    }  
    ";
        return $js;
    }

    public function renderJS() {

        $js = "";
        $js .= "Ext.namespace('WIN');";
        $js .= "
        " . $this->getObjectName() . " = Ext.extend(Ext.Window, {
            id: 'KAOM'
            ,initObject: function() {
                " . $this->bodyCfg() . "
                " . $this->getObjectName() . ".superclass.initObject.apply(this, arguments);
            }
            ,onRender: function() {
                " . $this->getObjectName() . ".superclass.onRender.apply(this, arguments);
                " . $this->onImageLoad() . "
            }
            ,onImageLoad: function() {
                var h = this.getFrameHeight(),
                w = this.getFrameWidth();
                this.setSize(this.body.dom.offsetWidth + w, this.body.dom.offsetHeight + h);
                    if (Ext.isIE) {
                    this.center();
                }
            }
            ,setSrc: function(src) {
                this.body.on('load', this.onImageLoad, this, {single: true});
                this.body.dom.style.width = this.body.dom.style.width = 'auto';
                this.body.dom.src = src;
            }
            ,initEvents: function() {
                " . $this->getObjectName() . ".superclass.initEvents.apply(this, arguments);
                if (this.resizer) {
                    this.resizer.preserveRatio = true;
                }
            }
        });
        ";

        $js .= $this->setWindow();

        return print$js;
    }

    protected function bodyCfg() {
        $js = "
        this.bodyCfg = {
            tag: 'img',
            src: this.src
        };
        ";
        return $this->isImageView ? $js : "";
    }

    protected function onImageLoad() {
        $js = "this.body.on('load', this.onImageLoad, this, {single: true});";
        return $this->isImageView ? $js : "";
    }

    public function getImageView($title) {
        /**
         * http://www.extjs.com/forum/showthread.php?t=26455
         */
        $js = "";
        $js .= "
        ImageView = new " . $this->getObjectName() . "({
            title: '" . $title . "'
            ,src: 'http://cdn.media.cyclingnews.com//2009/07/25/2/pic50838s_600.jpg'
            ,hideAction: 'close'
        }).show();
        ";

        return $js;
    }

    protected function setObjectItems() {

        switch ($this->contentType) {
            case "IFRAME":
                $js = "[new Ext.ux.IFrameComponent({ id: 'IFRAME_ID', url: '" . $this->contentIFRAME . "'})]";
                break;
            case "XTYPE":
                break;
            case "HTML":
                $js = "[{";
                $js .= "xtype:'box'";
                $js .= ",anchor:''";
                $js .= ",isFormField:true";
                $js .= ",autoEl:{";
                $js .= "tag:'div', children:[{";
                $js .= "tag:'div'";
                $js .= ",style:'margin:0 0 4px 0'";
                $js .= ",bodyStyle:'padding: 15px;'";
                $js .= ",html: '" . htmlspecialchars($this->contentHTML) . "'";
                $js .= "}]";
                $js .= "}";
                $js .= "}]";
                break;
            case "ITEMS":
                $js = $this->contentITEMS;
                break;
            default:
                $js = "[new Ext.ux.IFrameComponent({ id: 'IFRAME_ID', url: '" . $this->contentIFRAME . "'})]";
                break;
        }
        return $js;
    }

    public function openWindow() {
        $js = "
            jsOpenWindow();
        ";
        return $js;
    }

    protected function setWindow() {
        $js = "
    function jsOpenWindow(){
        win = new Ext.Window({
            title: '" . $this->objectTitle . "'
            ,id: '" . $this->getObjectId() . "'
            ,width: " . $this->objectWidth . "
            ,height: " . $this->objectHeight . "
            ,modal: true
            ,layout: 'fit'
            ,items: " . $this->setObjectItems() . "
            ,buttons:[{
                text: '" . CLOSE . "'
                ,iconCls:'icon-close'
                ,handler: function(){
                    win.close();
                }
            }]
        });
        win.show();
    };";

        return $js;
    }

    public function ExtgetCmp() {
        return "Ext.getCmp('" . $this->getObjectId() . "')";
    }
    
    public function Test(){
        $js = "
        myGrid.getColumnModel().setHidden(1, false);
        myGrid.getColumnModel().setHidden(3, false);
        myGrid.getColumnModel().setHidden(4, false);    
        ";
    }

}

?>