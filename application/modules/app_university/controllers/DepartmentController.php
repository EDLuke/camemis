<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 22.12.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/DepartmentDBAccess.php';
require_once 'models/UserAuth.php';

class DepartmentController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        $this->DB_DEPARTMENT = DepartmentDBAccess::getInstance();

        $this->objectId = null;

        $this->parentId = null;

        $this->facette = null;

        if ($this->_getParam('parentId')) {
            $this->parentId = $this->_getParam('parentId');
        }

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->facette = DepartmentDBAccess::findDepartmentFromId($this->objectId);
        }
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('department/showitem', array());
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;

        if ($this->facette) {
            $this->view->parentId = $this->facette->PARENT;
        } else {
            $this->view->parentId = $this->parentId;
        }

        $this->view->parentObject = DepartmentDBAccess::findDepartmentFromId($this->parentId);
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadDepartment":
                //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
                $jsondata = $this->DB_DEPARTMENT->jsonLoadDepartment($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveDepartment":
                //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
                $jsondata = $this->DB_DEPARTMENT->jsonSaveDepartment($this->REQUEST->getPost());
                break;

            case "jsonRemoveDepartment":
                //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
                $jsondata = $this->DB_DEPARTMENT->jsonRemoveDepartment($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllDepartments":
                //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
                $jsondata = $this->DB_DEPARTMENT->jsonTreeAllDepartments($this->REQUEST->getPost());
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