<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_school/staff/StaffContractDBAccess.php';

class StaffcontractController extends Zend_Controller_Action {

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
        
        $this->DB_STAFF_CONTRACT = StaffContractDBAccess::getInstance();

        $this->objectId = null;
        $this->staffId = null;
        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

    }

    public function staffcontractmainAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("staff/index");            
    }
    
    public function showitemAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }
    
    public function staffshowitemAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("staff/showitem");    
    }
    
    public function chartreportAction() {

        $this->_helper->viewRenderer("staff/chartreport");
    }

    public function jsonloadAction() {
        switch ($this->REQUEST->getPost('cmd')) {
            case "jsonShowAllStaffContracts":
                $jsondata = $this->DB_STAFF_CONTRACT->jsonShowAllStaffContracts($this->REQUEST->getPost());
                break;
                
            case "jsonLoadStaffContract":
                $jsondata = $this->DB_STAFF_CONTRACT->jsonLoadStaffContract($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonShowAllMembers":
                $jsondata = $this->DB_STAFF_CONTRACT->jsonShowAllMembers($this->REQUEST->getPost());
                break;
                    
            case "jsonSearchStaffContract":
                $jsondata = $this->DB_STAFF_CONTRACT->jsonSearchStaffContract($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
             case "jsonSaveStaffContract":
                $jsondata = $this->DB_STAFF_CONTRACT->jsonSaveStaffContract($this->REQUEST->getPost());
                break;
                
             case "jsonRemoveStaffContract":
                $jsondata = $this->DB_STAFF_CONTRACT->jsonRemoveStaffContract($this->REQUEST->getPost("objectId"));
                break;
        }        
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>