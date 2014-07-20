<?php

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_school/GuardianDBAccess.php';

class GuardianController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::mainidentify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->DB_GUARDIAN = GuardianDBAccess::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->objectId = null;
        $this->classId = null;
        $this->studentId = null;
        $this->trainingId = null;
        $this->objectData = array();

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = GuardianDBAccess::getObjectDataFromId($this->objectId);
        }

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('studentId'))
            $this->studentId = $this->_getParam('studentId');

        if ($this->_getParam('trainingId'))
            $this->trainingId = $this->_getParam('trainingId');
    }

    public function indexAction() {
        
    }

    public function guardianshowitemAction() {
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $this->_helper->viewRenderer("showitem");
    }

    public function changepasswordAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("password");
    }

    public function studenttranditionalAction() {
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer("student/tranditional");
    }

    public function studenttrainingAction() {
        $this->view->objectId = $this->objectId;
        $this->view->trainingId = $this->trainingId;
        $this->view->studentId = $this->studentId;
        $this->_helper->viewRenderer("student/training");
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "jsonLoadActiveStudentsForGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonLoadActiveStudentsForGuardian($this->REQUEST->getPost());
                break;

            case "jsonSearchStudentGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonSearchStudentGuardian($this->REQUEST->getPost());
                break;

            case "jsonLoadGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonLoadGuardian($this->REQUEST->getPost("objectId"));
                break;

            case "jsonLoadStudentGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonLoadStudentGuardian($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "jsonSaveGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonSaveGuardian($this->REQUEST->getPost());
                break;

            case "jsonRemoveGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonRemoveGuardian($this->REQUEST->getPost("objectId"));
                break;

            case "jsonActionAddStudentToGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonActionAddStudentToGuardian($this->REQUEST->getPost());
                break;

            case "jsonRemoveStudentGuardian":
                $jsondata = $this->DB_GUARDIAN->jsonRemoveStudentGuardian($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_GUARDIAN->releaseGuardian($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        //
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