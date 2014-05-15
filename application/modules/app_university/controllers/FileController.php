<?php
///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 15.07.2013
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/FileDBAccess.php';
require_once 'models/UserAuth.php';

class FileController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }
        
        $this->DB_FILE = FileDBAccess::getInstance();

        $this->objectId = null;
        $this->studentId = null;
        $this->teacherId = null;
        $this->parentId = null;
        $this->facette = null;

        if ($this->_getParam('parentId')) {
            $this->parentId = $this->_getParam('parentId');
        }

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->facette = FileDBAccess::findFileFromGuId($this->objectId);
        }
        
        if ($this->_getParam('studentId'))
            $this->studentId = $this->_getParam('studentId');
            
        if ($this->_getParam('teacherId'))
            $this->teacherId = $this->_getParam('teacherId');
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->studentId = $this->studentId;
        $this->view->teacherId = $this->teacherId;
        
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('file/showitem', array());
        $this->view->URL_EDIT_FILE = $this->UTILES->buildURL('file/editfile', array());
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        
         if ($this->objectId != 'new') {
             $checkChild = FileDBAccess::checkChild($this->objectId);

             if(!$checkChild)
                $this->_redirect("/file/editfile/?objectId=" . $this->objectId . "");   
         }

        if ($this->facette) {
            $this->view->parentId = $this->facette->PARENT;
        } else {
            $this->view->parentId = $this->parentId;
        }

        $this->view->parentObject = FileDBAccess::findFileFromGuId($this->parentId);
    }
    
    public function showitemlistAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
    }
    
    public function editfileAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;

        if ($this->facette) {
            $this->view->parentId = $this->facette->PARENT;
        } else {
            $this->view->parentId = $this->parentId;
        }

        $this->view->parentObject = FileDBAccess::findFileFromGuId($this->parentId);
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadFile":
                //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
                $jsondata = $this->DB_FILE->jsonLoadFile($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveFile":
                $jsondata = $this->DB_FILE->jsonSaveFile($this->REQUEST->getPost());
                break;
            case "jsonRemoveFile":
                $jsondata = $this->DB_FILE->jsonRemoveFile($this->REQUEST->getPost('objectId'));
                break;
            case "jsonActionAcademicToFile":
                $jsondata = FileDBAccess::jsonActionAcademicToFile($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllFiles":
                $jsondata = $this->DB_FILE->jsonTreeAllFiles($this->REQUEST->getPost());
                break;
                
            case "getAcademicsByFile":
                $jsondata = $this->DB_FILE->getAcademicsByFile($this->REQUEST->getPost());
                break;
            
            case "treeAllStaffs":
                $jsondata = $this->DB_FILE->treeAllStaffs($this->REQUEST->getPost());
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