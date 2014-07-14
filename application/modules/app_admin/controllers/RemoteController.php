<?php

require_once 'models/app_admin/AdminUserDBAccess.php';
require_once 'models/app_admin/AdminSessionAccess.php';

class RemoteController extends Zend_Controller_Action {

    public function init() {

        $this->REQUEST = $this->getRequest();
    }

    public function indexAction() {

        $USER_ACCESS = new AdminUserDBAccess(false, false);

        $LOGINNAME = $this->REQUEST->getPost("login");
        $PASSWORD = $this->REQUEST->getPost("password");

        $session_id = $USER_ACCESS->Login($LOGINNAME, $PASSWORD);

        if ($session_id) {

            $json = "{'success':true, 'sessionId':'$session_id'}";
        } else {
            $json = "{'success':true, 'sessionId':'failed'}";
        }
        if (isset($json)) {
            $this->getResponse()->setBody($json);
        }

        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
    }

    public function cronjobAction() {
        $key = $this->_getParam('key');
        switch ($key) {

            //OPTIMIZE_TABLE
            case "bfda03fce66ddcbf39915a9c8d4661f6":
                require_once 'models/app_admin/DBWebservices.php';
                DBWebservices::actionOptimizeTable();
                break;
            // BACKUP
            case "606bc133522c0d9ca3b40eeae65705e3":
                require_once 'models/app_admin/DBWebservices.php';
                DBWebservices::actionCustomerBackUp();
                break;
        }
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');

        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }
}

?>