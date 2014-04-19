<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/TextDBAccess.php';

class TranslationController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->DB_TRANSLATION = TextDBAccess::getInstance();

        $this->objectId = null;

        $this->objectData = array();

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_TRANSLATION->getTranslationDataFromId($this->objectId);
        }
    }

    public function indexAction() {

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('translation/showitem', array());
    }

    public function showitemAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_TRANSLATION->loadTranslationFromId($this->REQUEST->getPost('objectId'));
                break;

            case "allTranslations":
                $jsondata = $this->DB_TRANSLATION->allTranslations($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_TRANSLATION->updateTranslation($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllTranslations":
                $jsondata = $this->DB_TRANSLATION->jsonTreeAllTranslations($this->REQUEST->getPost());
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