<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/UserAuth.php';

class AcademicdateController extends Zend_Controller_Action {

    protected $roleAdmin = array("SYSTEM");

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->DB_ACADEMIC_DATE = AcademicDateDBAccess::getInstance();

        $this->objectId = null;
        $this->onlyShow = null;
        $this->schoolyearObject = null;
        $this->objectData = array();

        if ($this->_getParam('onlyShow'))
            $this->onlyShow = $this->_getParam('onlyShow');
        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');

            if (!$this->DB_ACADEMIC_DATE->findAcademicDateFromId($this->objectId)) {
                $this->_request->setControllerName('error');
            }

            $this->schoolyearObject = $this->DB_ACADEMIC_DATE->findAcademicDateFromId($this->objectId);

            $this->objectData = $this->DB_ACADEMIC_DATE->getAcademicDatetDataFromId($this->objectId);
        }
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL("academicdate/showitem", array());
    }

    public function additemAction() {
        
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->onlyShow = $this->onlyShow;

        $this->view->dbSchoolyear = $this->DB_ACADEMIC_DATE;

        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->facette = $this->schoolyearObject;
        $this->view->isCurrentYear = isset($this->objectData["IS_CURRENT_YEAR"]) ? $this->objectData["IS_CURRENT_YEAR"] : "";
        $this->view->isPastYear = isset($this->objectData["IS_PAST_YEAR"]) ? $this->objectData["IS_PAST_YEAR"] : "";
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL("academicdate/showitem", array('objectId' => $this->objectId));
        $this->view->URL_WINDOW_LOCATION = $this->UTILES->buildURL("subject/showitem", array('objectId' => $this->objectId));

        $this->view->URL_SCHOOL_EVENT = $this->UTILES->buildURL("schoolevent", array('schoolyearId' => $this->objectId));
    }

    public function jsonloadAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_ACADEMIC_DATE->loadAcademicDateFromId($this->REQUEST->getPost('objectId'));
                break;

            case "allSchoolyears":
                $jsondata = $this->DB_ACADEMIC_DATE->allSchoolyears($this->REQUEST->getPost());
                break;

            case "allSchoolyearCombo":
                $jsondata = $this->DB_ACADEMIC_DATE->allSchoolyearCombo();
                break;

            case "selectBoxSchoolyearNowFuture":
                $jsondata = $this->DB_ACADEMIC_DATE->selectBoxSchoolyearNowFuture();
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_ACADEMIC_DATE->updateAcademicDate($this->REQUEST->getPost());
                break;

            case "additem":
                $jsondata = $this->DB_ACADEMIC_DATE->addAcademicDateItem($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_ACADEMIC_DATE->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_ACADEMIC_DATE->releaseObject($this->REQUEST->getPost());
                break;

            case "createOnlyItem":
                $jsondata = $this->DB_ACADEMIC_DATE->createOnlyItem($this->REQUEST->getPost());
                break;

            case "actionDateline":
                $jsondata = $this->DB_ACADEMIC_DATE->actionDateline($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllAcademicDate":
                //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
                $jsondata = $this->DB_ACADEMIC_DATE->jsonTreeAllAcademicDate($this->REQUEST->getPost());
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