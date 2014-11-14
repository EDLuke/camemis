<?php

///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 06.06.2013
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/DescriptionDBAccess.php';
require_once 'models/app_school/extraclass/ExtraClassDBAccess.php';
require_once 'models/app_school/extraclass/StudentExtraClassDBAccess.php';
require_once 'models/UserAuth.php';

class ExtraclassController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }
        
        $this->DB_EXTRACLASS = ExtraClassDBAccess::getInstance();

        $this->objectId = null;
        $this->target = null;
        $this->objectData = array();
        $this->facette = null;
        $this->template = null;

        $this->studentId = $this->_getParam('studentId');
        $this->teacherId = $this->_getParam('teacherId');
        $this->template = $this->_getParam('template');

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_EXTRACLASS->getExtraclassDataFromId($this->objectId);
            $this->facette = ExtraClassDBAccess::findExtraClassFromId($this->objectId);
        }

        $this->target = $this->_getParam('target');
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
        $this->view->URL_PROGRAM = $this->UTILES->buildURL('extraclass/program', array());
        $this->view->URL_LEVEL = $this->UTILES->buildURL('extraclass/level', array());
        $this->view->URL_TERM = $this->UTILES->buildURL('extraclass/term', array());
        $this->view->URL_CLASS = $this->UTILES->buildURL('extraclass/class', array());
    }

    public function programAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }

    public function addprogramAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
        $this->view->objectId = $this->objectId;
        $this->view->template = $this->template;

        if ($this->objectId != 'new') {
            switch ($this->template) {
                case "PROGRAM":
                    $this->_redirect("/extraclass/program/?objectId=" . $this->objectId . "");
                    break;

                case "LEVEL":
                    $this->_redirect("/extraclass/level/?objectId=" . $this->objectId . "");
                    break;

                case "TERM":
                    $this->_redirect("/extraclass/term/?objectId=" . $this->objectId . "");
                    break;

                case "CLASS":
                    $this->_redirect("/extraclass/class/?objectId=" . $this->objectId . "");
                    break;
            }
        }
    }

    public function levelAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;
    }

    public function termAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;
    }

    public function classAction() {

        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->facette;
    }

    public function showitemAction() {
        //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->teacherId = $this->teacherId;
        $this->view->objectData = $this->objectData;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadObject":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_EXTRACLASS->jsonLoadObject($this->REQUEST->getPost('objectId'));
                break;
            case "jsonTeacherExtraClass":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_EXTRACLASS->jsonTeacherExtraClass($this->REQUEST->getPost());
                break;
            case "jsonListExtraClass":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_EXTRACLASS->jsonListExtraClass($this->REQUEST->getPost());
                break;

            case "jsonStudentExtraClass":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentExtraClassDBAccess::jsonStudentExtraClass($this->REQUEST->getPost());
                break;
            case "jsonListStudentInSchool":
                $jsondata = StudentExtraClassDBAccess::jsonListStudentInSchool($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonActionAddExtraClass":
            case "jsonSaveObject":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = ExtraClassDBAccess::jsonSaveExtraClass($this->REQUEST->getPost());
                break;
            case "removenode":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_EXTRACLASS->jsonRemoveExtraClass($this->REQUEST->getPost('objectId'));
                break;
            case "jsonReleaseObject":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = $this->DB_EXTRACLASS->jsonReleaseExtraClass($this->REQUEST->getPost('objectId'));
                break;

            case "actionStudentToExtraClass":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentExtraClassDBAccess::actionStudentToExtraClass($this->REQUEST->getPost());
                break;

            case "actionRemoveStudentExtraClass":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = StudentExtraClassDBAccess::actionRemoveStudentExtraClass($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllExtraClass":
                //UserAuth::actionPermint($this->_request, "TRAINING_PROGRAMS");
                $jsondata = ExtraClassDBAccess::jsonTreeAllExtraClass($this->REQUEST->getPost());
                break;
            case "jsonTreeAllCommunicationSubject":
                $jsondata = $this->DB_COMMUNICATION->jsonTreeAllCommunicationSubject($this->REQUEST->getPost());
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