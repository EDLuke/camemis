<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.07.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'clients/CamemisField.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/app_school/user/UserRoleDBAccess.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class UserroleController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->DB_USERROLE = UserRoleDBAccess::getInstance();

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->objectId = null;
        $this->parentId = null;
        $this->searchParent = null;
        $this->objectData = array();

        if ($this->_getParam('parentId')) {
            $this->parentId = $this->_getParam('parentId');
        }

        if ($this->_getParam('searchParent')) {
            $this->searchParent = $this->_getParam('searchParent');
        }

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_USERROLE->getUserRoleDataFromId($this->objectId);
        }
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "SYSTEM_USER_READ_RIGHT");

        $this->view->URL_USERROLE_ITEM = $this->UTILES->buildURL("userrole/showitem", array());
        $this->view->URL_USER_ITEM = $this->UTILES->buildURL("user/showitem", array());
    }

    public function addgroupAction() {
        $this->view->objectId = $this->objectId;
        $this->view->parentId = $this->parentId;
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "SYSTEM_USER_READ_RIGHT");

        $this->view->countUser = $this->DB_USERROLE->checkUserByRoleId($this->objectId);
        $this->view->objectData = $this->objectData;
        $this->view->objectId = $this->objectId;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $this->view->no_change_status = isset($this->objectData["NO_DELETE"]) ? $this->objectData["NO_DELETE"] : 0;
        $this->view->tutor = isset($this->objectData["TUTOR"]) ? $this->objectData["TUTOR"] : 0;
        if ($this->objectData["STATUS"]) {
            $this->view->remove_status = false;
        } else {

            $this->view->remove_status = $this->objectData["REMOVE_STATUS"];
        }

        $this->view->facette = UserRoleDBAccess::findUserRoleFromId($this->objectId);
    }

    public function rightsAction() {

        //UserAuth::actionPermint($this->_request, "SYSTEM_USER_READ_RIGHT");

        $this->view->objectId = $this->objectId;
        $this->view->searchParent = $this->searchParent;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_USERROLE->loadUserRoleFromId($this->REQUEST->getPost('objectId'));
                break;

            case "removeObject":
                $jsondata = $this->DB_USERROLE->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_USERROLE->releaseObject($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "treeAllUserrole":
                $jsondata = $this->DB_USERROLE->treeAllUserrole($this->REQUEST->getPost());
                break;
            case "jsonTreeAllRights":
                $jsondata = $this->DB_USERROLE->jsonTreeAllRights($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_USERROLE->updateObject($this->REQUEST->getPost());
                break;

            case "createObject":
                $jsondata = $this->DB_USERROLE->createObject($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_USERROLE->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_USERROLE->releaseObject($this->REQUEST->getPost());
                break;

            case "jsonActionUserRight":
                $jsondata = UserRoleDBAccess::jsonActionUserRight($this->REQUEST->getPost());
                break;

            case "jsonAddParent":
                $jsondata = $this->DB_USERROLE->createObject($this->REQUEST->getPost());
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