<?php
///////////////////////////////////////////////////////////
// @Math Man Web Application Developer
// Date: 17.02.2014
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/LetterDBAccess.php';

class LetterController extends Zend_Controller_Action {

    private $REQUEST = null;
    private $UTILES = null;
    private $DB_LETTER = null;
    private $objectId = null;
    private $facette = null;
    private $typeLetter = null;

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

        $this->DB_LETTER = LetterDBAccess::getInstance();

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->facette = LetterDBAccess::findLetterFromId($this->objectId);
        }
        
        if ($this->_getParam('typeLetter')) {
            $this->typeLetter = $this->_getParam('typeLetter');
            //$this->facette = LetterDBAccess::findLetterFromId($this->objectId);
        }
    }

    public function indexAction() {
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('letter/showitem', array());
    }

    public function chartreportAction() {
    }

    public function searchresultAction () {
    }

    public function showitemAction () {
        $this->view->objectId = $this->objectId;
        $this->view->typeLetter = $this->typeLetter;
        $this->view->facette = $this->facette;
        $this->view->status = $this->facette ? $this->facette->STATUS : 0;
        if ($this->objectId != "new") {
            if ($this->facette->STATUS == 0) {
                $this->view->remove_status = true;
            } else {
                $this->view->remove_status = false;
            }
        }else {
            $this->view->remove_status = false;
        }
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "showListByLetter":
                $jsondata = $this->DB_LETTER->showListByLetter($this->REQUEST->getPost());
                break;

            case "showAllStudentsOrStaffs":
                $jsondata = $this->DB_LETTER->showAllStudentsOrStaffs($this->REQUEST->getPost());
                break;

            case "loadLetter":
                $jsondata = $this->DB_LETTER->loadLetter($this->REQUEST->getPost("objectId"));
                break;

            case "loadPersonLetter":
                $jsondata = $this->DB_LETTER->loadPersonLetter($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);

    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "actionLetter":
                $jsondata = $this->DB_LETTER->actionLetter($this->REQUEST->getPost());
                break;

            case "releaseLetter":
                $jsondata = $this->DB_LETTER->releaseLetter($this->REQUEST->getPost("objectId"));
                break;

            case "removeLetter":
                $jsondata = $this->DB_LETTER->removeLetter($this->REQUEST->getPost("objectId"));
                break;

            case "addPersonToLetter":
                $jsondata = $this->DB_LETTER->addPersonToLetter($this->REQUEST->getPost());
                break;

            case "removePersonLetter":
                $jsondata = $this->DB_LETTER->removePersonLetter($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);

    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

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