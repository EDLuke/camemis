<?php

require_once 'Zend/Acl.php';
require_once 'models/app_admin/AdminUserDBAccess.php';
require_once 'models/app_admin/AdminSessionAccess.php';
require_once 'models/app_admin/AdminCustomerDBAccess.php';
require_once 'models/app_admin/AdminDatabaseDBAccess.php';
require_once 'clients/app_admin/AdminPage.php';
require_once 'models/app_admin/AdminUserAuth.php';

class CustomerController extends Zend_Controller_Action {

    public function init() {

        if (!AdminUserAuth::identify()) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->GuId = null;
        $this->CUSTOMER_OBJECT = null;

        if ($this->_getParam('GuId'))
            $this->GuId = $this->_getParam('GuId');

        $this->CUSTOMER_ACCESS = new AdminCustomerDBAccess();
        $this->CUSTOMER_ACCESS->GuId = $this->GuId;

        $this->CUSTOMER_OBJECT = $this->CUSTOMER_ACCESS->findCustomer();
        $this->DATABASE_ACCESS = new AdminDatabaseDBAccess();

        if ($this->GuId) {
            $params = array(
                'host' => "localhost",
                'username' => CAMEMISConfigBasic::getCode($this->CUSTOMER_OBJECT->DB_USER),
                'password' => CAMEMISConfigBasic::getCode($this->CUSTOMER_OBJECT->DB_PWD),
                'dbname' => $this->CUSTOMER_OBJECT->DB_NAME
            );

            $SCHOOL_DB_CONFIG = Zend_Db::factory('PDO_MYSQL', $params);
            $SCHOOL_DB_CONFIG->setFetchMode(Zend_Db::FETCH_OBJ);

            if ($this->CUSTOMER_OBJECT->DB_NAME) {
                
                Zend_Registry::set('IS_SCHOOL_DB_ACCESS', true);
                Zend_Registry::set('SCHOOL_DB_ACCESS', $SCHOOL_DB_CONFIG);
                Zend_Registry::set('CUSTOMER', $this->CUSTOMER_OBJECT);
                
                AdminCustomerDBAccess::setSchoolLogin();
                
            } else {
                Zend_Registry::set('IS_SCHOOL_DB_ACCESS', false);
            }
        }
    }

    public function indexAction() {
        
    }

    public function showcustomerAction() {

        $this->view->GuId = $this->GuId;
        $this->view->customerObject = $this->CUSTOMER_OBJECT;
    }

    public function showdetailAction() {

        $this->view->GuId = $this->GuId;
    }

    public function visitorsAction() {

        $this->view->GuId = $this->GuId;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAllCustomers":
                $jsondata = $this->CUSTOMER_ACCESS->jsonAllCustomers($this->REQUEST->getPost());
                break;

            case "jsonLoadCustomer":
                $jsondata = $this->CUSTOMER_ACCESS->jsonLoadCustomer($this->REQUEST->getPost('GuId'));
                break;

            case "jsonAllDatabases":
                $jsondata = $this->DATABASE_ACCESS->jsonAllDatabases($this->REQUEST->getPost());
                break;

            case "jsonEnrolledStudents":
                $jsondata = $this->CUSTOMER_ACCESS->jsonEnrolledStudents($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveCustomer":
                $jsondata = $this->CUSTOMER_ACCESS->jsonSaveCustomer($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllCustomers":
                $jsondata = $this->CUSTOMER_ACCESS->jsonTreeAllCustomers($this->REQUEST->getPost());

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