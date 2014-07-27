<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

class AdminPage {

    protected $utiles = null;
    private $ext_version = null;
    public $stylename = "xtheme-blue";
    public $setLoadingMask = false;

    public function __construct()
    {

        $this->setExtJsVersion();
    }

    public function setExtJsVersion()
    {
        return $this->ext_version = "" . Zend_Registry::get('CAMEMIS_URL') . "/public/" . Zend_Registry::get('EXTJS_VERSION') . "";
    }

    static function getInstance()
    {
        static $me;
        if ($me == null)
        {
            $me = new AdminPage();
        }
        return $me;
    }

    public function showCamemisHeader()
    {

        $js = "";
        $js .= $this->pageHeader();
        if ($this->setLoadingMask)
            $js .= $this->loadingMask();
        $js .= $this->loadCSS();
        $js .= $this->loadExtjs();
        $js .= $this->loadPluginJS();
        $js .= $this->closeHead();
        $js .= $this->openPageBody();
        $js .= $this->openWinIFrame();
        $js .= $this->openWinXType();
        $js .= $this->gotoIFrameURL();
        $js .= $this->gotoExtype();
        $js .= $this->setActivePanel();
        $js .= $this->openWinMax();
        $js .= $this->openWinMaxXType();
        $js .= $this->loadCamemisExtendJS();

        return $js;
    }

    static function setRequestURI($params = false)
    {
        if ($params)
        {
            return "window.location='" . $_SERVER["REQUEST_URI"] . "&" . $params . "';";
        }
        else
        {
            return "window.location='" . $_SERVER["REQUEST_URI"] . "';";
        }
    }

    protected function pageHeader()
    {

        $js = "";
        $js .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
        $js .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
        $js .= "<head>";

        $js .= "<title>" . Zend_Registry::get('TITLE') . "</title>";

        return $js;
    }

    protected function loadCSS()
    {

        $js = "";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/ext-all.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/main.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/main-burmese.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/main-khmer.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/icons.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/spinner.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/css/Portal.css\" />";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        return $js;
    }

    protected function loadExtjs()
    {

        $js = "";
        $js .= "<script type=\"text/javascript\" src=\"" . $this->setExtJsVersion() . "/adapter/ext/ext-base.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . $this->setExtJsVersion() . "/ext-all.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script>";
        $js .= "var viewHeight = Ext.getBody().getViewSize().height;";
        $js .= "var viewWidth = Ext.getBody().getViewSize().width;";
        $js .= $this->percentHeight();
        $js .= $this->percentWidth();
        $js .= "</script>";

        $js .= "<script>";
        $js .= "function XMsg(title, format){";
        $js .= "if(!msgCt){";
        $js .= "msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);";
        $js .= "}";
        $js .= "msgCt.alignTo(document, 't-t');";
        $js .= "var s = String.format.apply(String, Array.prototype.slice.call(arguments, 1));";
        $js .= "var m = Ext.DomHelper.append(msgCt, {html:createBox(title, s)}, true);";
        $js .= "m.slideIn('t').pause(3).ghost('t', {remove:true});";
        $js .= "}";
        $js .= "</script>";

        return $js;
    }

    public function setExtDefaultGif()
    {

        $js = "";
        $js .= "Ext.SSL_SECURE_URL=\"" . $this->setExtJsVersion() . "/resources/images/default/s.gif\";";
        $js .= "Ext.BLANK_IMAGE_URL=\"" . $this->setExtJsVersion() . "/resources/images/default/s.gif\";";
        $js .= "Ext.QuickTips.init();";

        return print$js;
    }

    protected function loadPluginJS()
    {

        $js = "";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/Cookies.js\"></script>";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/IFrameComponent.js\"></script>";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/SearchField.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/Ext.ux.grid.CheckboxColumn.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/FileUploadField.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/ColorField.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/SpinnerField.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/Spinner.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/Portal.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/PortalColumn.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/Portlet.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/TextFieldRemoteVal.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/Ext.ux.GridPrinter.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/GroupSummary.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/Ext.ux.VrTabPanel.js\"></script>";
        if ($this->setLoadingMask)
            $js .= "<script type=\"text/javascript\">document.getElementById('loading-msg').innerHTML = 'Loading  Components...';</script> ";

        return $js;
    }

    protected function loadCamemisExtendJS()
    {

        //
    }

    protected function openPageBody()
    {
        return "<body>";
    }

    protected function closeHead()
    {
        return "</head>";
    }

    protected function percentWidth()
    {

        $js = "";
        $js .= "function percentWidth(value){";
        $js .= "var value;";
        $js .= "return Math.round((Ext.getBody().getViewSize().width*value)/100);";
        $js .= "}";

        return $js;
    }

    protected function percentHeight()
    {

        $js = "";
        $js .= "function percentHeight(value){";
        $js .= "var value;";
        $js .= "return Math.round((Ext.getBody().getViewSize().height*value)/100);";
        $js .= "}";

        return $js;
    }

    public function showCamemisFooter()
    {

        $js = "";
        $js .= $this->loadingIndicators();
        $js .= "</body>";
        $js .= "</html>";

        return $js;
    }

    public function openWinIFrame()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function openWinIFrame(winTitle, winURL, winWidth, winHeight){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",id: 'OPEN_WIN_IFRAME_ID'";
        $js .= ",maximizable: true";
        $js .= ",plain: true";
        $js .= ",modal: true";
        $js .= ",width: winWidth";
        $js .= ",height: winHeight";
        $js .= ",layout: 'fit'";
        $js .= ",items:[new Ext.ux.IFrameComponent({ id: 'OPEN_IFRAME', url: winURL})]";
        $js .= ",fbar: ['->',{";
        $js .= "text: 'Close'";
        $js .= ",iconCls: 'icon-cancel'";
        $js .= ",handler: function (){";
        $js .= "win.close();";
        $js .= "}";
        $js .= "}]";
        $js .= "});";
        $js .= "win.show();";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    public function openWinXType()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function openWinXType(winTitle, formXType, winWidth, winHeight){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",id: 'OPEN_WIN_XTYPE_ID'";
        $js .= ",maximizable: true";
        $js .= ",modal: true";
        $js .= ",plain: true";
        $js .= ",width: winWidth";
        $js .= ",height: winHeight";
        $js .= ",layout: 'fit'";
        $js .= ",items:[{xtype: formXType}]";
        $js .= ",fbar: ['->',{";
        $js .= "text: 'Close'";
        $js .= ",iconCls: 'icon-cancel'";
        $js .= ",handler: function (){";
        $js .= "win.close();";
        $js .= "}";
        $js .= "}]";
        $js .= "});";
        $js .= "win.show();";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    public function gotoIFrameURL()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function clickOpenPage(regionId, title, url){";
        $js .= "if (title){;";
        $js .= "var title = '<b>'+title+'</b>' ;";
        $js .= "}else{ ;";
        $js .= "var title = '';";
        $js .= "};";
        $js .= "var panel = new Ext.Panel({";
        $js .= "layout: 'fit'";
        $js .= ",title: title";
        $js .= ",border: false";
        $js .= ",items: [new Ext.ux.IFrameComponent({ id: 'GOTO_IFRAME_URL', url: url})]";
        $js .= "});";
        $js .= "var show = Ext.getCmp(regionId);";
        $js .= "show.add(panel);";
        $js .= "show.getLayout().setActiveItem(panel);";
        $js .= "show.doLayout();";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    public function gotoExtype()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function clickOpenExtype(regionId, title, setxtype){";
        $js .= "var panel = new Ext.Panel({";
        $js .= "layout: 'fit'";
        $js .= ",title: title";
        $js .= ",border: false";
        $js .= ",items: [{xtype: setxtype}]";
        $js .= "});";
        $js .= "var show = Ext.getCmp(regionId);";
        $js .= "show.add(panel);";
        $js .= "show.getLayout().setActiveItem(panel);";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    public function setActivePanel()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function setActivePanel(showRegionId, penelTitle, penelItems){";
        $js .= "var panel = new Ext.TabPanel({";
        $js .= "activeTab:0,";
        $js .= "enableTabScroll:true,";
        $js .= "border: false,";
        $js .= "items:[{";
        $js .= "layout: 'fit'";
        $js .= ",border:false";
        $js .= ",title: penelTitle";
        $js .= ",items: penelItems";
        $js .= "}]";
        $js .= "});";
        $js .= "var show = Ext.getCmp(showRegionId);";
        $js .= "show.add(panel);";
        $js .= "show.getLayout().setActiveItem(panel);";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    protected function openWinMax()
    {
        $js = "";
        $js .= "<script>";
        $js .= "function openWinMax(winTitle,winURL){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",id:'OPEN_WIN_MAX_ID'";
        $js .= ",maximizable:false";
        $js .= ",resizable:false";
        $js .= ",modal: false";
        $js .= ",layout: 'fit'";
        $js .= ",border: true";
        $js .= ",frame: true";
        $js .= ",items: [new Ext.ux.IFrameComponent({ id: 'GOTO_WINMAX', url: winURL})]";
        $js .= ",listeners: {";
        $js .= "show: function(win) {";
        $js .= "win.maximize();";
        $js .= "}";
        $js .= ",single: true";
        $js .= "}";
        $js .= ",fbar: ['->',{";
        $js .= "text: 'Close'";
        $js .= ",iconCls: 'icon-cancel'";
        $js .= ",handler: function (){";
        $js .= "win.close();";
        $js .= "}";
        $js .= "}]";
        $js .= "});";
        $js .= "win.show();";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    protected function openWinMaxXType()
    {
        $js = "";
        $js .= "<script>";
        $js .= "function openWinMaxXType(winTitle,xtype){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",maximizable:false";
        $js .= ",resizable:false";
        $js .= ",modal: false";
        $js .= ",layout: 'fit'";
        $js .= ",border: true";
        $js .= ",items: [{xtype: xtype}]";
        $js .= "});";
        $js .= "win.show();";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    protected function getAddTab()
    {
        $js = "";
        $js .= "function addTab(ID,TITLE,URL){";
        $js .= "var title = TITLE;";
        $js .= "var activetab = tabId(tabs, title);";
        $js .= "if (activetab){";
        $js .= "tabs.setActiveTab(activetab);";
        $js .= "}else{";
        $js .= "tabs.add({";
        $js .= "title: TITLE";
        $js .= ",closable:true";
        $js .= ",layout:'fit'";
        $js .= ",iconCls: 'icon-tabs'";
        $js .= ",items: [new Ext.ux.IFrameComponent({ id: ID, url: URL})]";
        $js .= "}).show();";
        $js .= "}";
        $js .= "}";

        return $js;
    }

    protected function getTabId()
    {
        $js = "";
        $js .= "function tabId(panel, title){";
        $js .= "for(var i = 0; i<panel.items.length; i++){";
        $js .= "if(panel.getItem(i).title == title){";
        $js .= "return panel.getItem(i).getId();break;";
        $js .= "}";
        $js .= "}";
        $js .= "return '';";
        $js .= "}";

        return $js;
    }

    public function setAddTab()
    {

        $js = "";
        $js .= $this->getAddTab();
        $js .= $this->getTabId();

        return print$js;
    }

    protected function loadingMask()
    {
        $js = "";
        $js .= "<style>";
        $js .= "#loading-mask{";
        $js .= "position:absolute;";
        $js .= "left:0;";
        $js .= "top:0;";
        $js .= "width:100%;";
        $js .= "height:100%;";
        $js .= "z-index:20000;";
        $js .= "text-align:center;";
        $js .= "color:#919191;";
        $js .= "background-color:white;";
        $js .= "}";
        $js .= "#loading{";
        $js .= "position:absolute;";
        $js .= "left:5%;";
        $js .= "top:5%;";
        $js .= "padding:2px;";
        $js .= "z-index:20001;";
        $js .= "text-align:center;";
        $js .= "color:#919191;";
        $js .= "height:auto;";
        $js .= "}";
        $js .= "#loading a {";
        $js .= "color:#919191;";
        $js .= "}";
        $js .= "#loading .loading-indicator{";
        $js .= "background:white;";
        $js .= "color:444;";
        $js .= "font:bold 13px Verdana,arial,helvetica;";
        $js .= "padding:10px;";
        $js .= "margin:0;";
        $js .= "color:#919191;";
        $js .= "height:auto;";
        $js .= "}";
        $js .= "#loading-msg {";
        $js .= "font: normal 10px arial,Verdana,sans-serif;";
        $js .= "color:#919191;";
        $js .= "}";
        $js .= "</style>";

        $js .= "<div id=\"loading-mask\" style=\"loading-mask\"></div>";
        $js .= "<div id=\"loading\">";
        $js .= "<div id=\"loading-ind\" class=\"loading-indicator\">";
        $js .= "<img id=\"loading-image\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/ajax-loader.gif\" width=\"80\" height=\"10\"/><br>";
        $js .= "Loading CAMEMIS DATA, please wait...<br>";
        $js .= "<span id=\"loading-msg\"></span>";
        $js .= "</div>";
        $js .= "</div>";

        return $js;
    }

    public function setCostumerCSS()
    {
        print"
    	Ext.util.CSS.swapStyleSheet('style','" . $this->ext_version . "/resources/css/" . $this->stylename . ".css');
    	";
    }

    public function setdefaultHeaders()
    {
        print"
    	Ext.Ajax.defaultHeaders = ({'Content-Type': 'application/json; charset=TIS-620;'});
    	";
    }

    protected function loadingIndicators()
    {
        $js = "";
        $js .= "
    	<script type=\"text/javascript\">";
        $js .= "var loading=document.getElementById(\"loading\");";
        $js .= "if(loading)document.body.removeChild(loading);";
        $js .= "var mask=document.getElementById(\"loading-mask\");";
        $js .= "if(mask)document.body.removeChild(mask);";
        $js .= "</script>";
        return $js;
    }

    static public function alertMSG($title, $msg_text)
    {

        $js = "";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'" . $title . "',";
        $js .= "msg: '" . $msg_text . "',";
        $js .= "buttons: Ext.MessageBox.OK,";
        $js .= "icon: Ext.MessageBox.INFO";
        $js .= "});";

        return $js;
    }

    public function buildURL($script, $parms = array())
    {

        $ret = $script;
        $ret.= "?camemisId=" . camemisId() . "";
        $ret.= "&pageId=" . addText(Zend_Registry::get('SESSIONID')) . "";
        $ret.= "&";

        if (is_array($parms))
        {

            while (list($key, $value) = each($parms))
            {
                if (!empty($value))
                {
                    if (is_array($value))
                    {
                        while (list($key1, $value1) = each($value))
                        {
                            $ret .= $key . "[" . $key1 . "]=" . $value1 . "&";
                        }
                    }
                    else
                    {
                        $ret .= "" . $key . "=" . $value . "&";
                    }
                }
            }
        }

        return "" . Zend_Registry::get('CAMEMIS_URL') . "/" . $ret;
    }

}

?>