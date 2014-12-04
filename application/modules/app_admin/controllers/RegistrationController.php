<?php

require_once("Zend/Loader.php");
require_once 'models/app_admin/AdminCustomerDBAccess.php';

class RegistrationController extends Zend_Controller_Action {

    public function init() {
        
        $this->REQUEST = $this->getRequest();
    }

    public function indexAction() {
        
    }
    
    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "actionRegistration":
                $jsondata = AdminCustomerDBAccess::registrationSchool($this->REQUEST->getPost());
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