<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once ("Zend/Loader.php");
require_once 'clients/CamemisPage.php';
require_once 'include/Common.inc.php';
require_once "models/UserAuth.php";
require_once setUserLoacalization();

class CamemisBar {

    public $data = array();

    public function __construct() {

        //
    }

    static function setTBARShorInfo($text) {

        return "['->',{text: '" . INFO_SHORT_HEADER . "', iconCls:'icon-information',tooltip:'" . addslashes($text) . "'}]";
    }

    static function tbarShorInfo($id, $text) {

        $js = "";
        $js .= "text: '" . INFO_SHORT_HEADER . "'";
        $js .= ",id: '" . $id . "'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-information'";
        $js .= ",tooltip:'" . addslashes($text) . "'";

        return $js;
    }

    static function tbarLoad($disabled = false) {

        $js = "";
        $js .= "text: '" . LOAD . "'";
        $js .= ",id: 'LOAD_ID'";
        $js .= ",disabled: false";
        if ($disabled) {
            $js .= ",disabled: true";
        }
        $js .= ",iconCls:'icon-reload'";
        $js .= ",formBind:true";
        $js .= ",scope:this";
        $js .= ",handler:this.onLoad";

        return $js;
    }

    static function tbarFormCreatePDF($url = false) {

        $js = "";
        $js .= "id: 'CREATE_PDF_ID'";
        $js .= ",disabled: true";
        $js .= ",text: '" . CREATE_PDF_FILE . "'";
        $js .= ",iconCls:'icon-page_white_acrobat'";
        $js .= ",handler: function(){";
        $js .= "window.location='" . $url . "';";
        $js .= "}";

        return $js;
    }

    static function tbarFormRefresh($Id) {

        $js = "";
        $js .= "id: '" . $Id . "'";
        $js .= ",text: '<b>" . REFRESH . "</b>'";
        $js .= ",iconCls:'icon-reload'";
        $js .= ",handler: function(){";
        $js .= camemisPage::setRequestURI();
        $js .= "}";

        return $js;
    }

    static function tbarFormEasyRemoveObject($disabled = false) {

        $js = "";

        $js .= "id: 'EASY_REMOVE_ID'";
        $js .= ",text: '" . REMOVE . "'";
        if ($disabled) {
            $js .= ",disabled: true";
        }
        $js .= ",iconCls:'icon-delete'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectRemove";

        return $js;
    }

    static function tbarFormEasyReleaseObject($hidden = false) {

        $js = "";

        $js .= "id: 'EASY_RELEASE_ID'";
        $js .= ",text: '" . ACTIVE . "'";
        if ($hidden) {
            $js .= ",disabled: true";
        }
        $js .= ",iconCls:'icon-green'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectReleaseOn";

        return $js;
    }

    static function tbarFormRemoveObject($isremove) {

        $js = "";
        $js .= "id: 'FORM_REMOVE_ID'";
        $js .= ",text: '" . REMOVE . "'";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectRemove";

        if ($isremove)
            $js .= ",disabled: false";
        else
            $js .= ",disabled: true";
        return $js;
    }

    static function tbarCreateUserRole($handler) {

        $js = "";
        $js .= "text: ''";
        $js .= ",id: 'CREATE_USER_ROLE_ID'";
        $js .= ",tooltip:'" . CREATE . " " . USER_ROLE . "'";
        $js .= ",iconCls:'icon-group_add'";
        $js .= ",formBind:true";
        $js .= ",scope:this";
        $js .= ",handler: " . $handler . "";

        return $js;
    }

    static function tbarCreateUser($handler) {

        $js = "";
        $js .= "text: '" . CREATE_USER . "'";
        $js .= ",id: 'CREATE_USER_ID'";
        $js .= ",iconCls:'icon-application_form_add'";
        $js .= ",formBind:true";
        $js .= ",scope:this";
        $js .= ",handler: " . $handler . "";

        return $js;
    }

    static function tbarCreate($handler, $Id = false) {

        $js = "";
        $js .= "text: '" . ADD_ENTRY . "'";
        if ($Id) {
            $js .= ",id: 'ADD_ENTRY_ID_" . $Id . "'";
        } else {
            $js .= ",id: 'ADD_ENTRY_ID'";
        }
        $js .= ",iconCls:'icon-application_form_add'";
        $js .= ",formBind:true";
        $js .= ",scope:this";
        $js .= ",tooltip:'" . ADD_ENTRY . "'";
        $js .= ",handler: " . $handler . "";
        return $js;
    }

    static function tbarGridCreateItem($id, $text, $handler) {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: '" . $id . "'";
        $js .= ",text: '" . $text . "'";
        $js .= ",iconCls:'icon-application_form_add'";
        $js .= ",scope:this";
        if ($handler)
            $js .= ",handler: " . $handler . "";
        else
            $js .= ",handler: this.onAddItem";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridEvent($id, $text, $icon, $handler) {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: '" . $id . "'";
        $js .= ",text: '" . $text . "'";
        $js .= ",iconCls:'" . $icon . "'";
        $js .= ",scope:this";
        $js .= ",handler: " . $handler . "";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridRemove($handler) {

        $js = "";

        $js .= "tbar.add([{";
        $js .= "id: 'REMOVE_ID'";
        $js .= ",text: '" . REMOVE . "'";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",scope:this";
        $js .= ",handler: " . $handler . "";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridCreatePDF() {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: 'CREATE_PDF_ID'";
        $js .= ",hidden: true";
        $js .= ",text: '" . CREATE_PDF_FILE . "'";
        $js .= ",tooltip:'" . CREATE_PDF_FILE . "'";
        $js .= ",iconCls:'icon-page_white_acrobat'";
        $js .= ",scope:this";
        $js .= ",handler: function(){";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'Info...',";
        $js .= "msg: 'This functionality is not present in demo mode',";
        $js .= "buttons: Ext.MessageBox.OK,";
        $js .= " icon: Ext.MessageBox.INFO";
        $js .= "});";
        $js .= "}";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridExportCSV() {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: 'EXPORT_CSV_ID'";
        $js .= ",hidden: true";
        $js .= ",text: '" . EXPORT_XLS_FILE . "'";
        $js .= ",iconCls:'icon-page_white_excel'";
        $js .= ",scope:this";
        $js .= ",handler: function(){";
        $js .= "Ext.MessageBox.show({";
        $js .= "title:'Info...',";
        $js .= "msg: 'This functionality is not present in demo mode',";
        $js .= "buttons: Ext.MessageBox.OK,";
        $js .= "icon: Ext.MessageBox.INFO";
        $js .= "});";
        $js .= "}";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridAdd($handler) {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: 'ADD_ID'";
        $js .= ",text: '" . ADD_ENTRY . "'";
        $js .= ",iconCls:'icon-application_form_add'";
        $js .= ",scope:this";
        $js .= ",handler: " . $handler . "";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridStudentReg($handler) {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: 'ADD_ID'";
        $js .= ",text: '" . STUDENT_REGISTRATION_WIZARD . "'";
        $js .= ",iconCls:'icon-application_form_add'";
        $js .= ",scope:this";
        $js .= ",handler: " . $handler . "";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridEnrollment() {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: 'ADD_APPLY_ID'";
        $js .= ",text: '" . SAVE . "'";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSelection";
        $js .= "}]);";

        return $js;
    }

    static function tbarGridSelection() {

        $js = "";
        $js .= "tbar.add([{";
        $js .= "id: 'ADD_APPLY_ID'";
        $js .= ",text: '" . SAVE . "'";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSelection";
        $js .= "}]);";

        return $js;
    }

    static function tbarEnrollmentRecord() {

        $js = "";
        $js .= "text: '" . ENROLLMENT_RECORD . "'";
        $js .= ",id: 'ENROLLMENT_RECORD_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-wand'";
        $js .= ",scope:this";
        $js .= ",tooltip:'" . ENROLLMENT_RECORD . "'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarSingleRemove() {

        $js = "";
        $js .= "id: 'SINGLE_REMOVE_ID'";
        $js .= ",text: '" . REMOVE . "'";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",scope:this";

        $js .= ",handler: this.onSetObjectRemove";
        return $js;
    }

    static function tbarSingleReply() {

        $js = "";
        $js .= "text:'" . REPLY . "'";
        $js .= ",id: 'SINGLE_REPLY_ID'";
        $js .= ",tooltip:'" . REPLY . "'";
        $js .= ",iconCls:'icon-comment_add'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectReply";

        return $js;
    }

    static function tbarSingleSend() {

        $js = "";
        $js .= "text:'" . SEND . "'";
        $js .= ",id: 'SINGLE_SEND_ID'";
        $js .= ",tooltip:'" . SEND . "'";
        $js .= ",iconCls:'icon-email_go'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectSend";

        return $js;
    }

    static function tbarSingleSendSave() {

        $js = "";
        $js .= "text: '" . SEND . "'";
        $js .= ",id: 'SINGLE_SAVE_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-email_go'";
        $js .= ",scope:this";
        $js .= ",tooltip:'" . SEND . "'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarSingleSave() {

        $js = "";
        $js .= "text: '" . SAVE . "'";
        $js .= ",id: 'SINGLE_SAVE_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",tooltip:'" . SAVE . "'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarSimpleSave() {

        $js = "";
        $js .= "text: '" . SAVE . "'";
        $js .= ",id: 'SIMPLE_SAVE_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",tooltip:'" . SAVE . "'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarSave() {

        $js = "";
        $js .= "text: '" . SAVE . "'";
        $js .= ",id: 'SAVE_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",tooltip:'" . SAVE . "'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarReset($disabled = false) {

        $js = "";
        $js .= "text:'" . RESET . "'";
        $js .= ",tooltip:'" . RESET . "'";
        $js .= ",id: 'RESET_ID'";
        $js .= ",disabled: false";
        if ($disabled) {
            $js .= ",disabled: true";
        }
        $js .= ",iconCls:'icon-arrow_undo'";
        $js .= ",scope:this";
        $js .= ",handler: this.onReset";

        return $js;
    }

    static function tbarSetRelease($status = false) {

        $text = $status ? DISABLE : ENABLE;
        $icon = $status ? "icon-red" : "icon-greenn";
        $handler = $status ? "this.onSetObjectReleaseOff" : "this.onSetObjectReleaseOn";

        $js = "";
        $js .= "text:'" . $text . "'";
        $js .= ",tooltip:'" . $text . "'";
        $js .= ",id: 'RELEASE_ID'";
        $js .= ",iconCls:'" . $icon . "'";
        $js .= ",scope:this";
        $js .= ",disabled: true";
        $js .= ",handler: " . $handler . "";

        return $js;
    }

    static function tbarSetSend() {

        $js = "";
        $js .= "text:'" . SEND . "'";
        $js .= ",id: 'SEND_ID'";
        $js .= ",tooltip:'" . SEND . "'";
        $js .= ",iconCls:'icon-email_go'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectSend";

        return $js;
    }

    static function tbarTreeRefresh($Id = false) {

        $js = "";
        if ($Id) {
            $js .= "id: 'REFRESH_ID_" . $Id . "'";
        } else {
            $js .= "id: 'REFRESH_ID'";
        }
        $js .= ",tooltip:'<b>" . REFRESH . "</b>'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-reload'";
        $js .= ",handler: this.onTBarRefresh";

        return $js;
    }

    static function tbarTreeCollapse($Id = false) {

        $js = "";

        if ($Id) {
            $js .= "id: 'TBAR_COLLAPSE_ID_" . $Id . "'";
        } else {
            $js .= "id: 'TBAR_COLLAPSE_ID'";
        }
        $js .= ",tooltip:'" . COLLAPSE . "'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-collapse-all'";
        $js .= ",handler: this.onCollapse";

        return $js;
    }

    static function tbarTreeExpand($Id = false) {

        $js = "";
        if ($Id) {
            $js .= "id: 'TBAR_EXPAND_ID_" . $Id . "'";
        } else {
            $js .= "id: 'TBAR_EXPAND_ID'";
        }

        $js .= ",tooltip:'" . EXPAND . "'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-expand-all'";
        $js .= ",handler: this.onExpand";

        return $js;
    }

    static function tbarTreeAddParentFolder() {

        $js = "";
        $js .= "id: 'ADD_PARENT_FOLDER_ID'";
        $js .= ",text:'" . FOLDER . "'";
        $js .= ",tooltip:'" . FOLDER . "'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-folder_add'";
        $js .= ",handler: this.onTBarAddParentFolder";

        return $js;
    }

    static function tbarTreeAddParentItem() {

        $js = "";
        $js .= "id: 'ADD_PARENT_ITEM_ID'";
        $js .= ",tooltip:'" . ITEM . "'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-page_add'";
        $js .= ",handler: this.onTBarAddParentItem";

        return $js;
    }

    static function tbarTreeRemoveNode() {

        $js = "";
        $js .= "id: 'REMOVE_NODE_ID'";
        $js .= ",tooltip:'" . REMOVE . "'";
        $js .= ",scope:this";
        $js .= ",disabled:true";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",handler: this.onRemoveNode";

        return $js;
    }

    static function tbarTreeAddSubject() {

        $js = "";
        $js .= "id: 'ADD_SUBJECT_ITEM_ID'";
        $js .= ",text: '" . SUBJECT . "'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-folder_add'";
        $js .= ",handler: this.onAddSubject";

        return $js;
    }

    static function saveButtonHide() {

        $js = "Ext.getCmp('SAVE_ID')?Ext.getCmp('SAVE_ID').disable():'';";

        return $js;
    }

    static function saveButtonShow() {

        $js = "Ext.getCmp('SAVE_ID')?Ext.getCmp('SAVE_ID').enable():'';";

        return $js;
    }

    static function releaseSetText($text) {

        $js = "Ext.getCmp('RELEASE_ID')?Ext.getCmp('RELEASE_ID').setText('" . $text . "'):'';";

        return $js;
    }

    static function sendSetText($text) {
        $js = "Ext.getCmp('SEND_ID')?Ext.getCmp('SEND_ID').setText('" . $text . "'):'';";
        return $js;
    }

    static function releaseSetIcon($icon) {

        $js = "Ext.getCmp('RELEASE_ID')?Ext.getCmp('RELEASE_ID').setIconClass('" . $icon . "'):'';";

        return $js;
    }

    static function sendSetIcon($icon) {
        $js = "Ext.getCmp('SEND_ID')?Ext.getCmp('SEND_ID').setIconClass('" . $icon . "'):'';";

        return $js;
    }

    static function tbarReleaseStatus() {

        $js = "";
        $js = "
        if (action.result.data.STATUS == 1){
        " . CamemisBar::saveButtonHide() . "
        " . CamemisBar::releaseSetText(DISABLE) . "
        " . CamemisBar::releaseSetIcon("icon-red") . "
        }else{
        " . CamemisBar::saveButtonShow() . "
        " . CamemisBar::releaseSetText(ENABLE) . "
        " . CamemisBar::releaseSetIcon("icon-green") . "
        }
        ";
        return $js;
    }

}

?>