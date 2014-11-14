<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 14.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CamemisViewport {

    protected $modul = null;
    public $coding = true;
    public $viewportItems = null;
    public $eastItems = null;
    public $westItems = null;
    public $centerItems = null;
    public $centerItemsIframe = false;
    public $eastWidth = 250;
    public $eastColor ="#f9f9f9";
    public $westColor ="#f9f9f9";
    public $westWidth = 250;
    public $collapsible = "true";
    public $viewportTbarItems = null;
    //Normal, Border....
    public $viewportLayout = "Normal";
    //WestCenter,CenterEast, WestCenterEast...
    public $layoutBorderType = "WestCenter";

    public function __construct() {

        $this->utiles = Utiles::getInstance();
    }

    public function addViewportTbarItem($item) {
        $this->viewportTbarItems[] = "{" . $item . "}";
    }

    protected function setViewportTbarItems() {
        return $this->viewportTbarItems ? "" . implode(",", $this->viewportTbarItems) : "";
    }

    public function addItem($item) {
        $this->viewportItems[] = "{" . $item . "}";
    }

    public function addBorderItem($item) {
        $this->viewportBorderItems[] = "{" . $item . "}";
    }

    public function addWestItem($item) {
        $this->westItems[] = "{" . $item . "}";
    }

    public function addEastItem($item) {
        $this->eastItems[] = "{" . $item . "}";
    }
    
    public function addCenterItem($item) {
        
        if ($this->centerItemsIframe){
            $this->centerItems[] = $item;
        }else{
            $this->centerItems[] = "{" . $item . "}";
        }
    }

    protected function setViewportItems() {
        return $this->viewportItems ? "" . implode(",", $this->viewportItems) : "";
    }

    protected function setWestItems() {
        return $this->westItems ? "" . implode(",", $this->westItems) : "";
    }

    protected function setEastItems() {
        return $this->eastItems ? "" . implode(",", $this->eastItems) : "";
    }

    protected function setCenterItems() {
        return $this->centerItems ? "" . implode(",", $this->centerItems) : "";
    }

    protected function layoutWestCenterEast() {
        $js = "";
        $js .= "{";
        $js .= "region:'west'";
        $js .= ",id:'WEST_CONTENT'";
        $js .= ",margins:'3 0 3 3'";
        $js .= ",cmargins:'3 3 3 3'";
        $js .= ",bodyStyle: 'padding:0px; background-color: ".$this->westColor."'";
        $js .= ",width:" . $this->westWidth . "";
        $js .= ",minSize:" . $this->westWidth . "";
        $js .= ",maxSize:" . $this->westWidth . "";
        $js .= ",layout:'card'";
        $js .= ",activeItem: 0";

        if ($this->westItems) {
            $js .= ",items:[" . $this->setWestItems() . "]";
        } else {
            $js .= ",items: [{layout:'fit',bodyStyle:'padding:15px',border:false,html:'<b>Please add west content...</b>'}]";
        }

        $js .= "},{";
        $js .= "collapsible: false";
        $js .= ",id:'CENTER_CONTENT'";
        $js .= ",region:'center'";
        $js .= ",margins:'3 0 3 0'";
        $js .= ",layout:'card'";
        $js .= ",activeItem: 0";

        if ($this->centerItems) {
            $js .= ",items:[" . $this->setCenterItems() . "]";
        } else {
            $js .= ",items: [{layout:'fit',bodyStyle:'padding:15px',border:false,html:'<b>Please add center content...</b>'}]";
        }

        $js .= "},{";
        $js .= "region:'east'";
        $js .= ",id:'EAST_CONTENT'";
        $js .= ",margins:'3 3 3 0'";
        $js .= ",cmargins:'3 3 3 3'";
        $js .= ",width:" . $this->eastWidth . "";
        $js .= ",minSize:" . $this->eastWidth . "";
        $js .= ",maxSize:" . $this->eastWidth . "";
        $js .= ",layout:'card'";
        $js .= ",activeItem: 0";

        if ($this->eastItems) {
            $js .= ",items:[" . $this->setEastItems() . "]";
        } else {
            $js .= ",items: [{layout:'fit',bodyStyle:'padding:15px',border:false,html:'<b>Please add east content...</b>'}]";
        }

        $js .= "}";

        return $js;
    }

    protected function layoutWestConter() {
        $js = "";
        $js .= "{";
        $js .= "region:'west'";
        $js .= ",id:'WEST_CONTENT'";
        $js .= ",margins:'3 0 3 3'";
        $js .= ",cmargins:'3 3 3 3'";
        $js .= ",bodyStyle: 'padding:0px; background-color: ".$this->westColor."'";
        $js .= ",width:" . $this->westWidth . "";
        $js .= ",minSize:" . $this->westWidth . "";
        $js .= ",maxSize:" . $this->westWidth . "";
        $js .= ",layout:'card'";
        $js .= ",activeItem: 0";
        if ($this->westItems) {
            $js .= ",items:[" . $this->setWestItems() . "]";
        } else {
            $js .= ",items: [{layout:'fit',bodyStyle:'padding:15px',border:false,html:'<b>Please add west content...</b>'}]";
        }
        $js .= "},{";
        $js .= "collapsible: false";
        $js .= ",id:'CENTER_CONTENT'";
        $js .= ",region:'center'";
        $js .= ",margins:'3 3 3 0'";
        $js .= ",layout:'card'";
        $js .= ",activeItem: 0";

        if ($this->centerItems) {
            $js .= ",items:[" . $this->setCenterItems() . "]";
        } else {
            $js .= ",items: [{layout:'fit',bodyStyle:'padding:15px',border:false,html:'<b>Please add center content...</b>'}]";
        }

        $js .= "}";

        return $js;
    }

    protected function layoutCenterEast() {
        $js = "";
        $js .= " {";
        $js .= "region:'east'";
        $js .= ",id:'EAST_CONTENT'";
        $js .= ",margins:'3 3 3 0'";
        $js .= ",cmargins:'3 3 3 3'";
        $js .= ",bodyStyle: 'padding:0px; background-color: ".$this->eastColor."'";
        $js .= ",width:" . $this->eastWidth . "";
        $js .= ",minSize:" . $this->eastWidth . "";
        $js .= ",maxSize:" . $this->eastWidth . "";
        $js .= ",layout:'card'";
        $js .= ",activeItem: 0";

        if ($this->eastItems) {
            $js .= ",items:[" . $this->setEastItems() . "]";
        } else {
            $js .= ",items: [{layout:'fit',bodyStyle:'padding:15px',border:false,html:'<b>Please add east content...</b>'}]";
        }

        $js .= "},{";
        $js .= "collapsible: false";
        $js .= ",id:'CENTER_CONTENT'";
        $js .= ",region:'center'";
        $js .= ",margins:'3 0 3 3'";
        $js .= ",layout:'card'";
        $js .= ",activeItem:0";
        $js .= ",items:[" . $this->setCenterItems() . "]";
        $js .= "}
        ";

        return $js;
    }

    public function renderJS() {

        $js = "";

        switch ($this->viewportLayout) {
            case "Normal":
                $js .= "";
                $js .= "viewport = new Ext.Viewport({";
                $js .= "layout: 'fit'";
                $js .= ",border: false";
                $js .= ",items:[" . $this->setViewportItems() . "]";
                if ($this->viewportTbarItems)
                    $js .= ",tbar:[" . $this->setViewportTbarItems() . "]";
                $js .= "});";
                break;
            case "Border":
                $js .="";
                $js .= "viewport = new Ext.Viewport({";
                $js .= "layout:'fit'";
                $js .= ",border:false";

                $js .= ",items:[{";
                $js .= "layout:'border'";
                if ($this->viewportTbarItems)
                    $js .= ",tbar:[" . $this->setViewportTbarItems() . "]";
                $js .= ",border:false";
                $js .= ",defaults:{";
                $js .= "collapsible: " . $this->collapsible . "";
                $js .= ",split:true";
                $js .= "}";
                switch ($this->layoutBorderType) {
                    case "WestCenter":
                        $js .= ",items:[" . $this->layoutWestConter() . "]";
                        break;
                    case "CenterEast":
                        $js .= ",items:[" . $this->layoutCenterEast() . "]";
                        break;
                    case "WestCenterEast":
                        $js .= ",items:[" . $this->layoutWestCenterEast() . "]";
                        break;
                }
                $js .= "}]";
                $js .= "});";
                break;
        }

        if ($this->coding) {
            $js = str_replace(array("\r\n", "\r"), "", $js);
            return print trim($js);
        } else {
            return print$js;
        }
    }

}

?>