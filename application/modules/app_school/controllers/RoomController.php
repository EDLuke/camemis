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
require_once 'models/app_school/room/RoomDBAccess.php';
require_once 'models/app_school/room/RoomDescriptionDBAccess.php';
require_once 'models/app_school/room/RoomSessionDBAccess.php';

class RoomController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->DB_ROOM = RoomDBAccess::getInstance();
        
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->objectId = null;
        $this->facette = null;
        $this->roomName = null;
        $this->parentId = 0;
        $this->objectData = array();

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_ROOM->getRoomDataFromId($this->objectId);
            $this->facette = RoomDBAccess::findRoomFromId($this->objectId);
        }

        if ($this->_getParam('parentId')) {
            $this->parentId = $this->_getParam('parentId');
        }
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('room/showitem', array());
    }
    
    public function roomcreateAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->_helper->viewRenderer("/allrooms");
    }

    public function roomdescriptionAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->_helper->viewRenderer("/description/list");
    }

    public function showroomdescriptionAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->facette = RoomDescriptionDBAccess::findObjectFromId($this->_getParam('objectId'));
        $this->_helper->viewRenderer("description/show");
    }
    
    public function listroomsessionAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->_helper->viewRenderer("session/list");
    }
    
    public function summaryroomsessionAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->_helper->viewRenderer("session/summary");
    }
    
    public function showroomsessionAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("session/showitem");
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->facette = $this->facette;
        $this->view->objectId = $this->objectId;
        $this->view->parentId = $this->parentId;
        $this->view->objectData = $this->objectData;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;

        if ($this->facette) {
            if ($this->facette->STATUS) {
                $this->view->remove_status = false;
            } else {
                if ($this->objectData["REMOVE_STATUS"]) {
                    $this->view->remove_status = false;
                } else {
                    $this->view->remove_status = true;
                }
            }
            $this->view->roomName = setShowText($this->facette->NAME);
            
        } else {
            $this->view->remove_status = true;
            $this->view->remove_status = true;
        }
    }

    public function jsonloadAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_ROOM->loadObject($this->REQUEST->getPost('objectId'));
                break;

            case "allRooms":
                $jsondata = $this->DB_ROOM->allRooms($this->REQUEST->getPost());
                break;

            case "loadRoomDescription":
                $jsondata = RoomDescriptionDBAccess::loadRoomDescription($this->REQUEST->getPost('objectId'));
                break;
                
            case "jsonListRoomSession":
                $jsondata = RoomSessionDBAccess::jsonListRoomSession($this->REQUEST->getPost());
                break;
                
            case "jsonSumRoomSession":
                $jsondata = RoomSessionDBAccess::jsonSumRoomSession($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "addObject":
                $jsondata = $this->DB_ROOM->createOnlyItem($this->REQUEST->getPost());
                break;

            case "actionSaveRoom":
                $jsondata = $this->DB_ROOM->actionSaveRoom($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_ROOM->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_ROOM->releaseObject($this->REQUEST->getPost());
                break;

            case "saveRoomDescription":
                $jsondata = RoomDescriptionDBAccess::saveRoomDescription($this->REQUEST->getPost());
                break;

            case "removeRoomDescription":
                $jsondata = RoomDescriptionDBAccess::removeRoomDescription($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllRooms":
                $jsondata = $this->DB_ROOM->jsonTreeAllRooms($this->REQUEST->getPost());
                break;

            case "jsonTreeAllRoomDescription":
                $jsondata = RoomDescriptionDBAccess::jsonTreeAllRoomDescription($this->REQUEST->getPost());
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