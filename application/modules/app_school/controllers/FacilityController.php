<?php

///////////////////////////////////////////////////////////
//@sor veasna
//Date: 31.08.2013
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_school/facility/FacilityDBAccess.php';
require_once 'models/app_school/facility/FacilityUserDBAccess.php';
require_once 'models/app_school/facility/FieldSettingDBAccess.php';

class FacilityController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }
        
        $this->DB_FACILITY = FacilityDBAccess::getInstance();

        $this->objectId = null;

        $this->parentId = null;

        $this->facette = null; 

        if ($this->_getParam('parentId')) {
            $this->parentId = $this->_getParam('parentId');
        }

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
        }
    }

    public function indexAction() {

        $this->view->URL_MANAGE_TYPES = $this->UTILES->buildURL('facility/managetype', array());
        $this->view->URL_MANAGE_ITEMS = $this->UTILES->buildURL('facility/manageitem', array());
    }
    
    public function mainAction() {
        
    }

    public function dashboardAction() {
        
    }
    public function facilityitemtabsAction(){   
         $this->_helper->viewRenderer("items/facilityitemtabs");   
    }
    public function searchitemsAction(){
        $this->_helper->viewRenderer("items/search");   
    }
    public function managetypeAction() {

        $this->view->URL_SHOW_MANAGE_TYPE = $this->UTILES->buildURL('facility/showmanagetype', array());
        $this->_helper->viewRenderer("types/list");
    }

    public function showmanagetypeAction() {

        $this->view->objectId = $this->objectId;
        $this->view->parentId = $this->parentId;
        $this->view->facette = $result = FacilityDBAccess::findFacilityType($this->objectId);
        $this->_helper->viewRenderer("types/show");
    }

    public function manageitemAction() {
        $this->view->URL_SHOW_MANAGE_ITEM = $this->UTILES->buildURL('facility/showmanageitem', array());
        $this->view->URL_SHOW_SUB_MANAGE_ITEM = $this->UTILES->buildURL('facility/subshowmanageitem', array());
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("items/list");
    }

    public function showmanageitemAction() {
        $this->view->objectId = $this->objectId;
        $this->view->parentId = $this->parentId;
        $this->view->facette = FacilityDBAccess::findFacilityItem($this->objectId);
        $this->_helper->viewRenderer("items/show");
    }

    public function subshowmanageitemAction() {
        $this->view->objectId = $this->objectId;

        $parentObject = FacilityDBAccess::findFacilityItem($this->parentId);
        $facette = FacilityDBAccess::findFacilityItem($this->objectId);

        if ($facette) {
            $parentObject = FacilityDBAccess::findFacilityItem($facette->PARENT);
            $this->view->parentId = $parentObject->ID;
        } else {
            $this->view->parentId = $parentObject->ID;
        }
        $this->view->parentObject = $parentObject;
        $this->view->facette = $facette;
        $this->_helper->viewRenderer("items/subshow");
    }

    public function barcodeAction() {

        $this->view->code = $this->_getParam('code') ? $this->_getParam('code') : '';
        $this->_helper->viewRenderer("items/barcode");
    }

    public function checkinmainAction() {

        $this->_helper->viewRenderer("checkin/list");
    }

    public function showcheckinAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facUserId = $this->parentId;
        $this->view->show = $this->_getParam('show') ? $this->_getParam('show') : '';
        if ($this->parentId) {
            $facette = FacilityUserDBAccess::findFacilityUserById($this->parentId);
            $this->view->facette = $facette;
        }
        $this->_helper->viewRenderer("checkin/show");
    }

    public function showsubcheckinAction() {

        $this->view->objectId = $this->objectId;
        if ($this->objectId != 'new') {
            $facette = FacilityUserDBAccess::getUserItemFacility($this->objectId);
        } else {
            $facette = '';
        }
        $this->view->facette = $facette;
        $this->view->facUserId = $this->parentId;
        $this->view->target_user = $this->_getParam('target_user') ? $this->_getParam('target_user') : '';
        $this->_helper->viewRenderer("checkin/subshow");
    }

    public function showcheckoutAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = FacilityUserDBAccess::getAssignedUserFacility($this->objectId);
        $this->_helper->viewRenderer("checkout/show");
    }

    public function checkoutmainAction() {

        $this->_helper->viewRenderer("checkout/list");
    }

    public function listcheckoutAction() {

        $this->_helper->viewRenderer("checkin/listcheckout");
    }

    public function subshowcheckoutitemAction() {
        $this->view->objectId = $this->objectId;
        if ($this->objectId != 'new') {
            $facette = FacilityUserDBAccess::getUserItemFacility($this->objectId);
        } else {
            $facette = '';
        }
        $this->view->facette = $facette;
        $this->view->facUserId = $this->parentId;
        $this->_helper->viewRenderer("checkout/subshow");
    }

    ////////////////////////////////////////////////////////////////////////////
    //Field setting...
    ////////////////////////////////////////////////////////////////////////////
    public function fieldsettingAction() {
        $this->_helper->viewRenderer("fieldsetting/list");
    }

    public function showfieldsettingAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("fieldsetting/show");
    }

    public function templatexlsAction() {
        $this->_helper->viewRenderer("items/templatexls");
    }

    ////////////////////////////////////////////////////////////////////////////

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadFacilityType":
                $jsondata = FacilityDBAccess::jsonLoadFacilityType($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadFacilityItem":
                $jsondata = FacilityDBAccess::jsonLoadFacilityItem($this->REQUEST->getPost('objectId'));
                break;

            case "jsonSearchFacilityItem":
                $jsondata = FacilityDBAccess::jsonSearchFacilityItem($this->REQUEST->getPost());
                break;

            case "jsonCheckBarcodeID":
                $jsondata = FacilityDBAccess::jsonCheckBarcodeID($this->REQUEST->getPost('barcodeId'));
                break;
            //@veasna    
            case "jsonLoadAvailableFacilityItem":
                $jsondata = FacilityDBAccess::jsonLoadAvailableFacilityItem($this->REQUEST->getPost());
                break;

            case "jsonLoadFacilityUser":
                $jsondata = FacilityUserDBAccess::jsonLoadFacilityUser($this->REQUEST->getPost('objectId'));
                break;
            
            case "jsonListAssignedItem":
                $jsondata = FacilityUserDBAccess::jsonListAssignedItem($this->REQUEST->getPost());
                break;
            //veasna    
            case "jsonAllCheckOutItems":
                $jsondata = FacilityUserDBAccess::jsonAllCheckOutItems($this->REQUEST->getPost());
                break;

            case "jsonLoadFacilityUserItem":
                $jsondata = FacilityUserDBAccess::jsonLoadFacilityUserItem($this->REQUEST->getPost('objectId'));
                break;

            case "jsonAllNotReturnItems":
                $jsondata = FacilityUserDBAccess::jsonAllNotReturnItems($this->REQUEST->getPost());
                break;

            ////////////////////////////////////////////////////////////////////
            //Field setting
            ////////////////////////////////////////////////////////////////////
            case "jsonLoadFieldSetting":
                $jsondata = FieldSettingDBAccess::jsonLoadFieldSetting($this->REQUEST->getPost('objectId'));
                break;
            
            case "jsonSearchFacility":
                $jsondata = FacilityDBAccess::jsonSearchFacility($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveFacilityType":
                $jsondata = FacilityDBAccess::jsonSaveFacilityType($this->REQUEST->getPost());
                break;

            case "jsonSaveFacilityItem":
                $jsondata = FacilityDBAccess::jsonSaveFacilityItem($this->REQUEST->getPost());
                break;

            case "deleteFacilityType":
                $jsondata = FacilityDBAccess::deleteFacilityType($this->REQUEST->getPost('objectId'));
                break;

            case "deleteFacilityUser":
                $jsondata = FacilityUserDBAccess::deleteFacilityUser($this->REQUEST->getPost('objectId'));
                break;

            case "deleteFacilityItem":
                $jsondata = FacilityDBAccess::deleteFacilityItem($this->REQUEST->getPost('objectId'));
                break;

            ///
            case "deleteUserItemFacility":
                $jsondata = FacilityUserDBAccess::deleteUserItemFacility($this->REQUEST->getPost('objectId'));
                break;
            ///      

            case "jsonSaveFacilityUser":
                $jsondata = FacilityUserDBAccess::jsonSaveFacilityUser($this->REQUEST->getPost());
                break;

            case "jsonSaveFacilityUserItems":
                $jsondata = FacilityUserDBAccess::jsonSaveFacilityUserItems($this->REQUEST->getPost());
                break;

            case "jsonSaveCheckIn":
                $jsondata = FacilityUserDBAccess::jsonSaveCheckIn($this->REQUEST->getPost());
                break;

            case "jsonAllTreeFieldSetting":
                $jsondata = FacilityUserDBAccess::jsonAllTreeFieldSetting($this->REQUEST->getPost());
                break;

            ////////////////////////////////////////////////////////////////////
            //Field setting
            ////////////////////////////////////////////////////////////////////
            case "jsonSaveFieldSetting":
                $jsondata = FieldSettingDBAccess::jsonSaveFieldSetting($this->REQUEST->getPost());
                break;

            case "actionFieldSetting2Category":
                $jsondata = FacilityDBAccess::actionFieldSetting2Category($this->REQUEST->getPost());
                break;

            case "jsonRemoveFieldSetting":
                $jsondata = FieldSettingDBAccess::jsonRemoveFieldSetting($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllFacilityType":
                $jsondata = FacilityDBAccess::jsonTreeAllFacilityType($this->REQUEST->getPost());
                break;

            case "jsonTreeAllFacilityItem":
                $jsondata = FacilityDBAccess::jsonTreeAllFacilityItem($this->REQUEST->getPost());
                break;

            ////////////////////////////////////////////////////////////////////
            //Field setting
            ////////////////////////////////////////////////////////////////////
            case "jsonAllTreeFieldSetting":
                $jsondata = FieldSettingDBAccess::jsonAllTreeFieldSetting($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonimportAction() {

        FacilityDBAccess::importXLS($this->REQUEST->getPost());
        Zend_Loader::loadClass('Zend_Json');

        if (isset($jsondata))
            $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        if (isset($json))
            $this->getResponse()->setBody($json);
    }
    
    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>