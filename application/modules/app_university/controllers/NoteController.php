<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/student/StudentNoteDBAccess.php';
require_once setUserLoacalization();

class NoteController extends Zend_Controller_Action {

    protected $OBJECT_DATA;
    protected $objectId;
    protected $roleAdmin = array("SYSTEM");

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }
        
        $this->classId = null;
        $this->studentId = null;

        if ($this->_getParam('academicId'))
            $this->academicId = $this->_getParam('academicId');

        if ($this->_getParam('objectId'))
            $this->studentId = $this->_getParam('objectId');
    }

    public function indexAction() {
        $this->view->academicId = $this->academicId;
        $this->view->studentId = $this->studentId;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadAllNotes":
                $jsondata = StudentNoteDBAccess::jsonLoadAllNotes($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAddNote":
                $jsondata = StudentNoteDBAccess::jsonAddNote($this->REQUEST->getPost());
                break;
            case "jsonDeleteNote":
                $jsondata = StudentNoteDBAccess::jsonDeleteNote($this->REQUEST->getPost('Id'));
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