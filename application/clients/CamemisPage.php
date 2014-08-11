<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'clients/CamemisField.php';
require_once 'localization/ExtjsLocalization.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CamemisPage {

    protected $utiles = null;
    private $ext_version = null;
    public $stylename = "xtheme-blue";
    public $setLoadingMask = false;
    public $bgColor = "";

    public function __construct()
    {

        $this->utiles = Utiles::getInstance();

        $this->setExtJsVersion();

        //$registry = Zend_Registry::getInstance();
        //print_r($registry);
        switch (Zend_Registry::get('SKIN'))
        {
            case "GRAY":
                $this->stylename = "xtheme-gray";
                break;
            case "SLATE":
                $this->stylename = "xtheme-slate/css/xtheme-slate";
                break;
            case "UBUNTU":
                $this->stylename = "xtheme-human/css/xtheme-human";
                break;
            default:
                $this->stylename = "xtheme-blue";
                break;
        }
        //exit;
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
            $me = new CamemisPage();
        }
        return $me;
    }

    public function showCamemisHeader()
    {

        $js = "";
        $js .= $this->pageHeader();
        if ($this->setLoadingMask)
            $js .= $this->loadingMask();

        $js .= $this->setJavaScript();
        $js .= $this->loadExtjs();
        $js .= self::loadExtLocalization();
        $js .= $this->loadPluginJS();
        $js .= $this->closeHead();
        $js .= $this->openPageBody();
        $js .= $this->openEasyWindow();
        $js .= $this->openWinIFrame();
        $js .= $this->openFixeIFrame();
        $js .= $this->openWinXType();
        $js .= $this->clickOpenIFrame();
        $js .= $this->gotoIFrameURL();
        $js .= $this->gotoExtype();
        $js .= $this->setActivePanel();
        $js .= $this->openWinMax();
        $js .= $this->openWinMaxXType();
        $js .= $this->loadCamemisExtendJS();

        $js .= $this->loadCSS();

        return $js;
    }

    protected function setJavaScript()
    {

        $js = "";
        $js .= "<script>";
        $js .= "";
        $js .= "var msgCt;";
        $js .= "function createBox(t, s){";
        $js .= "return ['<div class=\"msg\">',";
        $js .= "'<div class=\"x-box-tl\"><div class=\"x-box-tr\"><div class=\"x-box-tc\"></div></div></div>',";
        $js .= "'<div class=\"x-box-ml\"><div class=\"x-box-mr\"><div class=\"x-box-mc\"><h3>', t, '</h3>', s, '</div></div></div>',";
        $js .= "'<div class=\"x-box-bl\"><div class=\"x-box-br\"><div class=\"x-box-bc\"></div></div></div>',";
        $js .= "'</div>'].join('');";
        $js .= "}";

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

        return trim(preg_replace(array('/\r/', '/\n/'), '', $js));
    }

    protected function pageHeader()
    {

        $js = "";
        $js .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
        $js .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
        $js .= "<head>";

        switch (Zend_Registry::get('SYSTEM_LANGUAGE'))
        {

            case "KHMER":
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            case "VIETNAMESE":
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            case "THAI":
                //$js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=TIS-620\"/>";
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            case "LAO":
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            case "CHINESE_TRADITIONAL":
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            case "CHINESE_SIMPLIFIED":
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            case "SPANISH":
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
            default:
                $js .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>";
                break;
        }

        $js .= "<title>" . Zend_Registry::get('TITLE') . "</title>";
        return trim(preg_replace(array('/\r/', '/\n/'), '', $js));
    }

    public static function userBgColor()
    {
        $bgColor = "";
        switch (UserAuth::getUserType())
        {
            case "STUDENT":
            case "GUARDIAN":
                //$bgColor = "#e6e6e6";
                //$bgColor = "#c3d5ed";
                $bgColor = "#dee7f6";
                break;
            case "TEACHER":
            case "INSTRUCTOR":
                //$bgColor = "#e6e6e6";
                //$bgColor = "#c3d5ed";
                $bgColor = "#dee7f6";
                break;
            default:
                //$bgColor = "#c3d5ed";
                $bgColor = "#dee7f6";
                break;
        }

        return $bgColor;
    }

    public static function userFormBgColor()
    {
        $bgColor = "";
        switch (UserAuth::getUserType())
        {
            case "STUDENT":
            case "GUARDIAN":
                //$bgColor = "#e6e6e6";
                $bgColor = "#c3d5ed";
                break;
            case "TEACHER":
            case "INSTRUCTOR":
                //$bgColor = "#e6e6e6";
                $bgColor = "#d1ddef";
                break;
            default:
                $bgColor = "#d1ddef";
                break;
        }

        return $bgColor;
    }

    protected function loadCSS()
    {

        $keyAPI = Zend_Registry::get('MODUL_API');

        $js = "";
        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/ext-all-css-camemis.php?key=" . $keyAPI . "\" />";

        switch (UserAuth::getUserType())
        {
            case "STUDENT":
            case "GUARDIAN":
                //$js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/xtheme-gray.css\" />";
                $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/xtheme-blue.css\" />";
                break;
            case "TEACHER":
            case "INSTRUCTOR":
                //$js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/xtheme-gray.css\" />";
                $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/xtheme-blue.css\" />";
                break;
            default:
                $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this->setExtJsVersion() . "/resources/css/xtheme-blue.css\" />";
                break;
        }

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/icons.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/gradebook.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/spinner.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/css/Portal.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/ColumnHeaderGroup.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/Calendar.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/filetype.css?key=" . $keyAPI . "\" />";

        $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/css/" . $this->mainCSS() . ".css?key=" . $keyAPI . "\" />";

        return trim(preg_replace(array('/\r/', '/\n/'), '', $js));
    }

    protected function loadExtjs()
    {

        $keyAPI = Zend_Registry::get('MODUL_API');

        $js = "";

        $js .= "<script type=\"text/javascript\" src=\"" . $this->setExtJsVersion() . "/ext-camemis.php?key=" . $keyAPI . "\"></script>";

        $js .= "<script>";
        $js .= "var viewHeight = Ext.getBody().getViewSize().height;";
        $js .= "var viewWidth = Ext.getBody().getViewSize().width;";
        $js .= $this->percentHeight();
        $js .= $this->percentWidth();
        $js .= "</script>";

        return trim(preg_replace(array('/\r/', '/\n/'), '', $js));
    }

    public static function loadExtLocalization()
    {

        $js = "<script>";
        $js .= ExtjsLocalization::render();
        $js .= "</script>";

        return trim(preg_replace(array('/\r/', '/\n/'), '', $js));
    }

    public function setExtDefaultGif()
    {

        $js = "";
        $js .= "Ext.QuickTips.init();";

        return print$js;
    }

    protected function loadPluginJS()
    {

        $keyAPI = Zend_Registry::get('MODUL_API');

        $js = "";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/Ext.ux.FileUploader.js?key=" . $keyAPI . "\"></script>";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/ux/Ext.ux.UploadPanel.js?key=" . $keyAPI . "\"></script>";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/FilterRow.js?key=" . $keyAPI . "\"></script>";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/HttpProvider.js?key=" . $keyAPI . "\"></script>";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/GroupSummary.js?key=" . $keyAPI . "\"></script>";
        $js .= "<script type=\"text/javascript\" src=\"" . Zend_Registry::get('CAMEMIS_URL') . "/public/plugin/GroupSummary.js?key=" . $keyAPI . "\"></script>";
        return $js;
    }

    protected function loadCamemisExtendJS()
    {

        //
    }

    protected function openPageBody()
    {

        if ($this->bgColor)
        {
            return "<body bgcolor='" . $this->bgColor . "'>";
        }
        else
        {
            return "<body>";
        }
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
        #$js .= ",closeAction:'hide'";
        $js .= ",plain: true";
        $js .= ",modal: true";
        $js .= ",width: winWidth";
        $js .= ",height: winHeight";
        $js .= ",layout: 'fit'";
        $js .= ",items:[new Ext.ux.IFrameComponent({ id: 'OPEN_IFRAME', url: winURL})]";

        $js .= ",fbar: ['->',{";
        $js .= "text: '" . CLOSE . "'";
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

    public function openFixeIFrame()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function openFixeIFrame(winTitle, winURL, winWidth, winHeight){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",id: 'OPEN_WIN_IFRAME_ID'";
        $js .= ",maximizable: true";
        #$js .= ",closeAction:'hide'";
        $js .= ",plain: true";
        $js .= ",modal: true";
        $js .= ",width: winWidth";
        $js .= ",height: winHeight";
        $js .= ",layout: 'fit'";
        $js .= ",items:[new Ext.ux.IFrameComponent({ id: 'OPEN_IFRAME', url: winURL})]";
        $js .= "});";
        $js .= "win.show();";
        $js .= "};";
        $js .= "</script>";

        return $js;
    }

    public function openEasyWindow()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function openEasyWindow(winTitle, url, winWidth, winHeight){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",maximizable: false";
        $js .= ",modal: true";
        $js .= ",plain: false,closable:false";
        $js .= ",width: winWidth";
        $js .= ",height: winHeight";
        $js .= ",layout: 'fit'";
        $js .= ",items: [new Ext.ux.IFrameComponent({ id: 'EASY_IFRAME_URL', url: url})]";
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
        $js .= "function openWinXType(winId, winTitle, formXType, winWidth, winHeight){";
        $js .= "var win = new Ext.Window({";
        $js .= "title: '<b>'+winTitle+'</b>'";
        $js .= ",id: winId";
        $js .= ",maximizable: true";
        $js .= ",modal: true";
        #$js .= ",closeAction:'hide'";
        $js .= ",plain: true";
        $js .= ",width: winWidth";
        $js .= ",height: winHeight";
        $js .= ",layout: 'fit'";
        $js .= ",items:[{xtype: formXType}]";
        $js .= ",fbar: ['->',{";
        $js .= "text: '" . CLOSE . "'";
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

    public function clickOpenIFrame()
    {

        $js = "";
        $js .= "<script>";
        $js .= "function clickOpenIFrame(regionId, title, url){";
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
        $js .= "show.removeAll();";
        $js .= "show.add(panel);";
        $js .= "show.getLayout().setActiveItem(panel);";
        $js .= "show.doLayout();";
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
        $js .= ",items: [new Ext.ux.IFrameComponent({ id: regionId + '_ID', url: url})]";
        $js .= "});";
        $js .= "var show = Ext.getCmp(regionId);";
        $js .= "show.add(panel);";
        $js .= "show.getLayout().setActiveItem(panel);";
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
        $js .= "show.removeAll();";
        $js .= "show.add(panel);";
        $js .= "show.getLayout().setActiveItem(panel);";
        $js .= "show.doLayout();";
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
        $js .= "text: '" . CLOSE . "'";
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
        $js .= "if (Ext.getCmp('ACTIVED_' + ID)) tabs.remove(Ext.getCmp('ACTIVED_' + ID));var title = TITLE;";
        $js .= "var activetab = tabId(tabs, title);";
        $js .= "if (activetab){";
        $js .= "tabs.setActiveTab(activetab);";
        $js .= "}else{";
        $js .= "tabs.add({";
        $js .= "title: TITLE";
        $js .= ",id:'ACTIVED_'+ID";
        $js .= ",closable:true";
        $js .= ",layout:'fit'";
        $js .= "/*,iconCls: 'icon-tabs'*/";
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
        // eliminate the loading indicators
        $js .= "var loading=document.getElementById(\"loading\");";
        $js .= "if(loading)document.body.removeChild(loading);";
        // eliminate the loading mask so application shows
        $js .= "var mask=document.getElementById(\"loading-mask\");";
        $js .= "if(mask)document.body.removeChild(mask);";
        $js .= "</script>";
        return $js;
    }

    public static function setExternalUrl($value)
    {
        $js = "";
        $js .= "
      <script type=\"text/javascript\">";
        $js .= $value;
        $js .= "</script>";
        return $js;
    }

    static public function alertPleaseSelect($title = false, $msg = false)
    {

        $js = "";
        $js .= "Ext.MessageBox.show({";
        if ($title)
        {
            $js .= "title:'" . $title . "',";
        }
        else
        {
            $js .= "title:'" . WARNING . "',";
        }
        if ($msg)
        {
            $js .= "msg:'" . $msg . "',";
        }
        else
        {
            $js .= "msg:'" . PLEASE_SELECT . "',";
        }
        $js .= "buttons: Ext.MessageBox.OK,";
        $js .= "icon: Ext.MessageBox.INFO";
        $js .= "});";

        return $js;
    }

    static public function alertMSG($title, $msg_text, $icon = false)
    {

        $js = "";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'" . $title . "',";
        $js .= "msg: '" . $msg_text . "',";
        switch ($icon)
        {
            case 1:
                $js .= "icon: Ext.MessageBox.ERROR";
                break;
            case 2:
                $js .= "icon: Ext.MessageBox.WARNING";
                break;
            case 3:
                $js .= "icon: Ext.MessageBox.QUESTION";
                break;
            default:
                $js .= "icon: Ext.MessageBox.INFO";
                break;
        }
        $js .= ",buttons: Ext.MessageBox.OK,";
        $js .= "});";

        return $js;
    }

    static public function chartStyle()
    {

        switch (Zend_Registry::get('SYSTEM_LANGUAGE'))
        {
            case "KHMER":
                $size = 11;
                break;
            default:
                $size = 9;
                break;
        }
        $js = "";
        $js .= "

        {
        padding: 10,
        animationEnabled: true,
        font: {
        name: 'Verdana',
        color: 0x444444,
        size: " . $size . "
        },
        dataTip: {
        padding: 5,
        border: {
        color: 0x99bbe8,
        size:1
        },
        background: {
        color: 0xDAE7F6,
        alpha: .9
        },
        font: {
        name: 'Verdana',
        color: 0x15428B,
        size: " . $size . ",
        bold: true
        }
        },
        xAxis: {
        color: 0x69aBc8,
        majorTicks: {color: 0x69aBc8, length: 4},
        minorTicks: {color: 0x69aBc8, length: 2},
        majorGridLines: {size: 1, color: 0xeeeeee}
        },
        yAxis: {
        color: 0x69aBc8
        ,majorTicks: {color: 0x69aBc8, length: 4}
        ,minorTicks: {color: 0x69aBc8, length: 2}
        ,majorGridLines: {size: 1, color: 0xdfe8f6}
        }
        }
        ";
        return $js;
    }

    protected function mainCSS()
    {

        switch (Zend_Registry::get('SYSTEM_LANGUAGE'))
        {
            case "KHMER":
                $mainCSS = "main-khmer";
                break;
            case "THAI":
                $mainCSS = "main-thai";
                break;
            case "BURMESE":
                $mainCSS = "main-burmese";
                break;
            default:
                $mainCSS = "main";
                break;
        }
        return $mainCSS;
    }

    static public function setMainquoteRefresh($jsEvent, $timeSecond)
    {

        $js = "
        var mainquoterefresh = {
        run: function(){
        " . $jsEvent . ";
        },
        interval: " . $timeSecond . " //1 second
        }
        Ext.TaskMgr.start(mainquoterefresh);
        ";
        if (CAMEMISModulConfig::AUTO_ROMOTE)
            echo $js;
    }

    //
    static public function setNoLogoutMessage()
    {

        $js = "";
        if (!Zend_Registry::get('APPLICATION_DEMO'))
        {

            if (Zend_Registry::get('LESSON_COUNT') > 1)
            {
                $js .= "window.parent.Ext.MessageBox.show({";
                $js .= "title:'" . WARNING . "'";
                $js .= ",width: 350";
                $js .= ",msg: '" . NO_LOGOUT_MESSAGE . "'";
                $js .= ",buttons: Ext.MessageBox.OK";
                $js .= ",icon: Ext.MessageBox.WARNING";
                $js .= ",fn: function(btn, text){
            if (btn == 'ok'){
            Ext.Ajax.request({
            url:'/dataset/remote/'
            ,method: 'POST'
            ,params: {cmd: 'actionLogoutWarning'}
            ,success: function(response, options) {}
            ,failure: function(response, options) {}
            });
            }
            }";
                $js .= ",icon: Ext.MessageBox.INFO";
                $js .= "});";
            }
        }
        echo $js;
    }

    static function ExtformVTypes()
    {

        $js = "
      Ext.apply(Ext.form.VTypes, {
      daterange : function(val, field) {
      var date = field.parseDate(val);

      if(!date){
      return false;
      }
      if (field.startDateField && (!this.dateRangeMax || (date.getTime() != this.dateRangeMax.getTime()))) {
      var start = Ext.getCmp(field.startDateField);
      start.setMaxValue(date);
      start.validate();
      this.dateRangeMax = date;
      }
      else if (field.endDateField && (!this.dateRangeMin || (date.getTime() != this.dateRangeMin.getTime()))) {
      var end = Ext.getCmp(field.endDateField);
      end.setMinValue(date);
      end.validate();
      this.dateRangeMin = date;
      }
      /*
      * Always return true since we're only using this vtype to set the
      * min/max allowed values (these are tested for after the vtype test)
      */
      return true;
      },

      password : function(val, field) {
      if (field.initialPassField) {
      var pwd = Ext.getCmp(field.initialPassField);
      return (val == pwd.getValue());
      }
      return true;
      },

      passwordText : 'Passwords do not match'
      });
      ";

        echo $js;
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

    static function isSSL()
    {
        if ($_SERVER['HTTPS'] != "on")
        {
            return "https";
        }
        else
        {
            return "http";
        }
    }

    public function getShowLog($data = array())
    {

        $extjs = "
        [
        {
        extype: 'panel'
        ,iconCls:'icon-about'
        ,title: '" . CREATED . "'
        ,collapsible: true
        ,collapsed: false
        ,boder: false
        ,autoHeight: true
        ,layout: 'form'
        ,labelWidth: 160
        ,bodyStyle: 'background:#E0E7F7; padding:15px'
        ,style: 'padding-bottom: 10px'
        ,items:[{
        " . CamemisField::Displayfield("CREATED_DATE", CREATED_DATE, "" . isset($data["CREATED_DATE"]) ? $data["CREATED_DATE"] : "---" . "") . "
        },{
        " . CamemisField::Displayfield("CREATED_BY", CREATED_BY, "" . isset($data["CREATED_BY"]) ? $data["CREATED_BY"] : "---" . "") . "
        }]
        },{
        extype: 'panel'
        ,iconCls:'icon-about'
        ,title: '" . MODIFIED . "'
        ,collapsible: true
        ,collapsed: false
        ,boder: false
        ,autoHeight: true
        ,layout: 'form'
        ,labelWidth: 160
        ,bodyStyle: 'background:#E0E7F7; padding:15px'
        ,style: 'padding-bottom: 10px'
        ,items:[{
        " . CamemisField::Displayfield("MODIFY_DATE", MODIFY_DATE, "" . isset($data["MODIFY_DATE"]) ? $data["MODIFY_DATE"] : "---" . "") . "
        },{
        " . CamemisField::Displayfield("MODIFY_BY", MODIFY_BY, "" . isset($data["MODIFY_BY"]) ? $data["MODIFY_BY"] : "---" . "") . "
        }]
        },{
        extype: 'panel'
        ,iconCls:'icon-about'
        ,title: '" . ENABLED . "'
        ,collapsible: true
        ,collapsed: true
        ,boder: false
        ,autoHeight: true
        ,layout: 'form'
        ,labelWidth: 160
        ,bodyStyle: 'background:#E0E7F7; padding:15px'
        ,style: 'padding-bottom: 10px'
        ,items:[{
        " . CamemisField::Displayfield("ENABLED_DATE", ENABLED_DATE, "" . isset($data["ENABLED_DATE"]) ? $data["ENABLED_DATE)"] : "---" . "") . "
        },{
        " . CamemisField::Displayfield("ENABLED_BY", ENABLED_BY, "" . isset($data["ENABLED_BY"]) ? $data["ENABLED_BY"] : "---" . "") . "
        }]
        },{
        extype: 'panel'
        ,iconCls:'icon-about'
        ,title: '" . DISABLED . "'
        ,collapsible: true
        ,collapsed: true
        ,boder: false
        ,autoHeight: true
        ,layout: 'form'
        ,labelWidth: 160
        ,bodyStyle: 'background:#E0E7F7; padding:15px'
        ,style: 'padding-bottom: 10px'
        ,items:[{
        " . CamemisField::Displayfield("DISABLED_DATE", DISABLED_DATE, "" . isset($data["DISABLED_DATE"]) ? $data["DISABLED_DATE"] : "---" . "") . "
        },{
        " . CamemisField::Displayfield("DISABLED_BY", DISABLED_BY, "" . isset($data["DISABLED_BY"]) ? $data["DISABLED_BY"] : "---" . "") . "
        }]
        }
        ]
        ";

        return $extjs;
    }

    public static function showBlob()
    {
        $js = "";
        $js .= "function showBlob(showFile){";
        $js .= "window.location='/dataset/showblob/?blobId=' + showFile;";
        $js .= "}";

        print $js;
    }

    public static function deleteBlob()
    {
        $js = "";
        $js .= "function deleteBlob(blobId){";
        $js .= "Ext.Ajax.request({";
        $js .= "url: '/dataset/jsonsave/'";
        $js .= ",method: 'POST'";
        $js .= ",params:{";
        $js .= "cmd: 'actionDeleteFile'";
        $js .= ",blobId: blobId";
        $js .= "}";
        $js .= ",success: function(response, options) {";
        $js .= "" . self::setRequestURI() . "";
        $js .= "}";
        $js .= "})";
        $js .= "}";
        print $js;
    }

    public static function uploadBlob()
    {
        $js = "";
        $js .= "function uploadBlob(uploadform, showfilemessage, id, area, academic){";
        $js .= "if(Ext.getCmp(uploadform).getForm().isValid()){ Ext.Msg.progress('Upload...', 'waiting...', 'progressing');";
        $js .= "var count = 0;";
        $js .= "var interval = window.setInterval(function() {";
        $js .= "count = count + 0.04;";
        $js .= "Ext.Msg.updateProgress(count);";
        $js .= "if(count >= 1) {";
        $js .= "window.clearInterval(interval);";
        $js .= "Ext.Msg.hide();";
        $js .= "}";
        $js .= "}, 100);";
        $js .= "}";

        $js .= "if(Ext.getCmp(uploadform).getForm().isValid()){Ext.Ajax.request({";
        $js .= "url: '/dataset/jsonsave/'";
        $js .= ",isUpload: true";
        $js .= ",headers: {'Content-type':'multipart/form-data'}";
        $js .= ",method: 'POST'";
        $js .= ",params:{";
        $js .= "cmd: 'jsonUploadFile'";
        $js .= ",objectId: id";
        $js .= ",area: area";
        $js .= ",class: academic";
        $js .= "}";
        $js .= ",form: Ext.getCmp(uploadform).getForm().getEl().dom";
        $js .= ",success: function(response, options) {";

        $js .= "Ext.getCmp('myWin').close();";
        $js .= "" . camemisPage::setRequestURI() . "";
        $js .= "}";
        $js .= "})";
        $js .= "}";
        $js .= "}";
        print $js;
    }

    public static function ckeditorLanguage()
    {
        switch (Zend_Registry::get('SYSTEM_LANGUAGE'))
        {

            case "KHMER":
                $lang = 'km';
                break;
            case "VIETNAMESE":
                $lang = 'vi';
                break;
            case "THAI":
                $lang = 'th';
                break;
            case "LAO":
                $lang = 'en';
                break;
            case "BURMESE":
                $lang = 'en';
                break;
            default:
                $lang = 'en';
                break;
        }

        return $lang;
    }

    public static function setEmbeddedHelp($key)
    {
        return "openWinIFrame('" . CAMEMIS_HELP . "', '/help/?key=" . $key . "',percentWidth(75), percentHeight(85));";
    }

}

?>