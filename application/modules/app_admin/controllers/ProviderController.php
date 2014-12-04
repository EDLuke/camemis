<?php

require_once 'models/app_admin/AdminUserDBAccess.php';
require_once 'models/app_admin/AdminSessionAccess.php';
require_once 'models/app_admin/AdminCustomerDBAccess.php';
require_once 'clients/app_admin/AdminPage.php';
require_once 'models/app_admin/AdminUserAuth.php';
require_once 'models/app_admin/MyConfig.php';

class ProviderController extends Zend_Controller_Action {

    public function init() {

        if (!AdminUserAuth::identify() || !MyConfig::getACLValue("PROVIDER")) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->PAGE = new AdminPage();
    }

    public function indexAction() {
        
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLogout":
                $jsondata = array("success" => true);

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