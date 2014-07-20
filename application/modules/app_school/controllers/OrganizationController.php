<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 26.02.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'clients/CamemisField.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/app_school/user/OrganizationDBAccess.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class OrganizationController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->DB_ORGANIZATION = OrganizationDBAccess::getInstance();

        $this->objectId = null;
        $this->roleId = null;
        $this->objectData = array();
        $this->roll = null;

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_ORGANIZATION->getOrganizationDataFromId($this->objectId);
        }

        $this->roleId = $this->_getParam('roleId');
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL("organization/showitem", array());
    }

    public function showitemAction() {
#
        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $remove_status = isset($this->objectData["REMOVE_STATUS"]) ? $this->objectData["REMOVE_STATUS"] : 0;

        $this->view->edit_status = true;

        if ($status) {
            $this->view->remove_status = false;
        } else {
            if ($remove_status) {
                $this->view->remove_status = false;
            } else {
                $this->view->remove_status = true;
            }
        }

        $this->view->SHOWITEM_STAFF = $this->UTILES->buildURL("staff/showitem", array());
    }

    public function jsonloadAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_ORGANIZATION->loadOrganizationFromId($this->REQUEST->getPost('objectId'));
                break;

            case "allOrganizations":
                $jsondata = $this->DB_ORGANIZATION->allOrganizations($this->REQUEST->getPost());
                break;

            case "assignedUserOrganization":
                $jsondata = $this->DB_ORGANIZATION->assignedUserOrganization($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_ORGANIZATION->updateOrganization($this->REQUEST->getPost());
                break;

            case "addObject":
                $jsondata = $this->DB_ORGANIZATION->addOrganization($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_ORGANIZATION->removeOrganization($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_ORGANIZATION->releaseOrganization($this->REQUEST->getPost());
                break;

            case "actionUserOrganization":
                $jsondata = $this->DB_ORGANIZATION->actionUserOrganization($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllOrganizations":
                $jsondata = $this->DB_ORGANIZATION->jsonTreeAllOrganizations($this->REQUEST->getPost());
                break;
        }
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