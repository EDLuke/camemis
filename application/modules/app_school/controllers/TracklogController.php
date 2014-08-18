<?php

///////////////////////////////////////////////////////////
// @Chuy Thong Senior Software Developer
// Date: 08.08.2014
// Phnom Penh, Cambodia
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/TracklogDBAccess.php';

class TracklogController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->DB_TRACKLOG = TracklogDBAccess::getInstance();

        $this->objectId = null;

        $this->objectData = array();

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_TRACKLOG->getTracklogFromId($this->objectId);
        }
    }

    public function indexAction() {

    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "allTracklogs":
                $jsondata = $this->DB_TRACKLOG->allTracklogs($this->REQUEST->getPost());
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