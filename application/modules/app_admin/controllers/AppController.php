<?php

require_once 'Zend/Acl.php';
require_once 'models/app_admin/AdminHelpDBAccess.php';
require_once 'models/app_admin/AdminUserAuth.php';
require_once 'models/app_admin/AdminAppDBAccess.php';

class AppController extends Zend_Controller_Action {

    public function init() {

        if (!AdminUserAuth::identify()) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->objectId = null;
        $this->HELP_OBJECT = null;

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

    }

    public function indexAction() {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->_helper->viewRenderer("userright/index");
    }

    public function showdetailAction() {
        
        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("userright/show");
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadAppRight":
                $jsondata = AdminAppDBAccess::jsonLoadAppRight($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveAppRight":
                $jsondata = AdminAppDBAccess::jsonSaveAppRight($this->REQUEST->getPost());
                break;
            
            case "jsonDeleteAppRight":
                $jsondata = AdminAppDBAccess::jsonDeleteAppRight($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllAppRights":
                $jsondata = AdminAppDBAccess::jsonTreeAllAppRights($this->REQUEST->getPost());
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