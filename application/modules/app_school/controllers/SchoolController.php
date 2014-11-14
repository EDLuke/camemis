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
require_once 'models/app_school/school/SchoolDBAccess.php';
require_once 'models/app_school/UserCalendarDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/CAMEMISHelpDBAccess.php';

class SchoolController extends Zend_Controller_Action {

    public function init()
    {
        if (!UserAuth::identify())
        {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds'))
        {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->DB_SCHOOL = SchoolDBAccess::getInstance();
        $this->DB_USER_CALENDAR = UserCalendarDBAccess::getInstance();

        $this->objectId = Zend_Registry::get('SCHOOL_ID');
        $this->objectData = array();

        $this->textId = null;

        if ($this->_getParam('objectId'))
        {
            $this->objectId = $this->_getParam('objectId');
        }

        if ($this->_getParam('textId'))
        {
            $this->textId = $this->_getParam('textId');
        }

        $this->objectData = $this->DB_SCHOOL->getSchoolData($this->objectId);

        $this->DB_HELP = new CAMEMISHelpDBAccess();
    }

    public function indexAction()
    {
        //
    }

    public function showitemAction()
    {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->URL_TRANSLATION = $this->UTILES->buildURL('translation', array());
    }

    public function showmainAction()
    {
        $this->view->objectId = $this->objectId;
    }

    public function cardsettingAction()
    {

        $this->view->objectId = $this->objectId;
    }

    public function translationAction()
    {

        $this->view->URL_SHOWTEXT = $this->UTILES->buildURL('school/showtext', array());
    }

    public function showtextAction()
    {

        $this->view->textId = $this->textId;
    }

    public function calendarAction()
    {
        
    }

    public function jsonloadAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "loadObject":
                $jsondata = $this->DB_SCHOOL->jsonLoadSchool();
                break;

            case "loadText":
                $jsondata = $this->DB_SCHOOL->loadTextFromId($this->REQUEST->getPost('textId'));
                break;

            case "allGradingMethod":
                $jsondata = $this->DB_SCHOOL->allGradingMethod($this->REQUEST->getPost());
                break;

            case "globalSearch":
                $jsondata = $this->DB_SCHOOL->globalSearch($this->REQUEST->getPost());
                break;

            case "jsonManageUserCalendar":
                switch ($this->REQUEST->getPost("xaction"))
                {
                    case "read":
                        $jsondata = $this->DB_USER_CALENDAR->jsonLoadUserCalendar($this->REQUEST->getPost());
                        break;
                    case "create":
                        $jsondata = $this->DB_USER_CALENDAR->jsonAddUserCalendar($this->REQUEST->getPost());
                        break;
                    case "update":
                        $jsondata = $this->DB_USER_CALENDAR->jsonUpdateUserCalendar($this->REQUEST->getPost());
                        break;
                    case "destroy":
                        $jsondata = $this->DB_USER_CALENDAR->jsonDeleteUserCalendar($this->REQUEST->getPost());
                        break;
                }
                break;

            case "jsonListHelps":
                $jsondata = $this->DB_HELP->jsonAllHelps($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {
            /**
             * Update...
             */
            case "addItem":
                $jsondata = $this->DB_SCHOOL->addItem($this->REQUEST->getPost());
                break;

            case "updateObject":
                $jsondata = $this->DB_SCHOOL->updateSchool($this->REQUEST->getPost());
                break;
            ///////////////////////////////////////////////
            // Logout...
            ///////////////////////////////////////////////
            case "jsonLogout":
                $jsondata = $this->DB_SCHOOL->jsonLogout();
                break;

            case "jsonRemoveAllStudents":
                $jsondata = $this->DB_SCHOOL->jsonRemoveAllStudents();
                break;

            case "jsonRemoveAllStaffs":
                $jsondata = $this->DB_SCHOOL->jsonRemoveAllStaffs();
                break;

            case "sendTestSMS":
                $jsondata = $this->DB_SCHOOL->sendTestSMS($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonTreeAllTexts":
                $jsondata = $this->DB_SCHOOL->jsonTreeAllTexts($this->REQUEST->getPost());
                break;

            case "jsonTreeHelps":
                $jsondata = $this->DB_HELP->jsonTreeHelps($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function setJSON($jsondata)
    {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>