<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 26.06.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'clients/CamemisField.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/bulletin/BulletinDBAccess.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class BulletinController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->DB_BULLETIN = BulletinDBAccess::getInstance();

        $this->objectId = null;
        $this->OBJECT = null;
        $this->OBJECT_DATA = array();

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->OBJECT_DATA = BulletinDBAccess::getBulletinDataFromId($this->objectId);
            $this->OBJECT = BulletinDBAccess::findBulletinFromId($this->objectId);
        }
    }

    public function indexAction() {
        
    }

    public function showmainAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->OBJECT;
    }

    public function editbulletinAction() {

        $this->view->objectId = $this->objectId;

        if ($this->objectId != "new") {
            $this->view->facette = $this->OBJECT;
            $this->view->status = $this->OBJECT->STATUS ? 1 : 0;
            $this->view->remove_status = $this->OBJECT->STATUS ? false : true;
        } else {
            $this->view->status = false;
            $this->view->remove_status = false;
        }
    }

    public function profilebulletinAction() {
        
    }

    public function viewprofilebulletinAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->OBJECT;
    }

    public function showbulletinAction() {

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->OBJECT;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAllBulletins":
                $jsondata = BulletinDBAccess::jsonAllBulletins($this->REQUEST->getPost());
                break;
            case "searchBulletins":
                $jsondata = $this->DB_BULLETIN->searchBulletins($this->REQUEST->getPost());
                break;
            case "loadObject":
                $jsondata = BulletinDBAccess::loadBulletinFromId($this->REQUEST->getPost("objectId"));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveBulletin":
                $jsondata = BulletinDBAccess::jsonSaveBulletin($this->REQUEST->getPost());
                break;
            case "jsonAddObject":
                $jsondata = BulletinDBAccess::jsonAddBulletin($this->REQUEST->getPost());
                break;
            case "releaseObject":
                $jsondata = BulletinDBAccess::jsonReleaseBulletin($this->REQUEST->getPost());
                break;
            case "removeObject":
                $jsondata = BulletinDBAccess::jsonRemoveBulletin($this->REQUEST->getPost("objectId"));
                break;
            case "jsonActionAcademicToBulletin":
                $jsondata = BulletinDBAccess::jsonActionAcademicToBulletin($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "getAcademicsByBulletin":
                $jsondata = BulletinDBAccess::getAcademicsByBulletin($this->REQUEST->getPost());
                break;
            case "getTrainingsByBulletin":
                $jsondata = BulletinDBAccess::getTrainingsByBulletin($this->REQUEST->getPost());
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