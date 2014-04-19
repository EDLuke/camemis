<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.09.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'clients/CamemisField.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/app_school/user/UserMemberDBAccess.php';
require_once 'models/app_school/user/UserActivityDBAccess.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class UserController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->DB_USER = UserMemberDBAccess::getInstance();
        $this->DB_USER_ACTIVITY = UserActivityDBAccess::getInstance();

        $this->objectId = null;
        $this->roleId = null;
        $this->objectData = array();
        $this->roll = null;

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_USER->getUserDataFromId($this->objectId);
        }

        $this->roleId = $this->_getParam('roleId');
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "SYSTEM_USER");
        $this->view->URL_ITEM = $this->UTILES->buildURL("user/showitem", array());
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "SYSTEM_USER");

        $this->view->objectId = $this->objectId;
        $memberObject = UserMemberDBAccess::findUserFromId($this->objectId);
        $this->view->roleId = $memberObject ? $memberObject->ROLE : null;
        $this->view->objectData = $this->objectData;

        if (isset($this->objectData["IS_PASSWORD"])) {

            $this->view->status_password_icon = $this->objectData["IS_PASSWORD"] ? "icon-accept" : "icon-error";
        }

        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;

        $superuser = isset($this->objectData["SUPERUSER"]) ? $this->objectData["SUPERUSER"] : 0;
        $status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $remove_status = isset($this->objectData["REMOVE_STATUS"]) ? $this->objectData["REMOVE_STATUS"] : 0;

        $this->view->edit_status = true;

        if ($superuser) {
            $this->view->remove_status = false;
            $this->view->edit_status = false;
        } elseif ($status) {
            $this->view->remove_status = false;
        } else {
            if ($remove_status) {
                $this->view->remove_status = false;
            } else {
                $this->view->remove_status = true;
            }
        }

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL("user/showitem", array('objectId' => $this->objectId));
        $this->view->SHOWITEM_STAFF = $this->UTILES->buildURL("staff/showitem", array());
    }

    public function useractivityAction() {
        
    }

    public function useronlineAction() {
        
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_USER->loadUserFromId($this->REQUEST->getPost('objectId'));
                break;

            case "allUsers":
                $jsondata = $this->DB_USER->allUsers($this->REQUEST->getPost());
                break;

            case "sessionExpired":
                $jsondata = $this->DB_USER->sessionExpired($this->REQUEST->getPost());
                break;

            case "searchUserActivity":
                $jsondata = $this->DB_USER_ACTIVITY->searchUserActivity($this->REQUEST->getPost());
                break;

            case "jsonUserOnline":
                $jsondata = UserActivityDBAccess::jsonUserOnline($this->REQUEST->getPost());
                break;

            case "jsonCountUsersOnline":
                $jsondata = UserActivityDBAccess::jsonCountUsersOnline($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_USER->updateUser($this->REQUEST->getPost());
                break;

            case "addObject":
                $jsondata = $this->DB_USER->addUser($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_USER->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_USER->releaseObject($this->REQUEST->getPost());
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