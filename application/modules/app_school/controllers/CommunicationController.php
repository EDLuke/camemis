<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models//CamemisTypeDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/CommunicationDBAccess.php';
require_once 'models/app_school/finance/StudentPaymentSettingDBAccess.php';
require_once setUserLoacalization();

class CommunicationController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->DB_COMMUNICATION = CommunicationDBAccess::getInstance();
        $this->DB_CLASS = AcademicDBAccess::getInstance();
        $this->DB_CAMEMIS_TYPE = CamemisTypeDBAccess::getInstance();

        $this->objectId = null;

        $this->classId = null;

        $this->schoolyearId = null;

        $this->classObject = null;

        $this->objectData = array();

        $this->communicationObject = null;

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_COMMUNICATION->getCommunicationtDataFromId($this->objectId);
            $this->communicationObject = $this->DB_COMMUNICATION->findCommunicationFromId($this->objectId);
        }

        if ($this->_getParam('classId')) {
            $this->classId = $this->_getParam('classId');
            $this->classObject = AcademicDBAccess::findGradeFromId($this->classId);
            $this->schoolyearId = $this->classObject->SCHOOL_YEAR;
        }
    }

    public function indexAction() {

        $this->view->classId = $this->classId;

        $this->view->classObject = $this->classObject;

        $this->view->URL_MAIN_INBOX = $this->UTILES->buildURL(
                'communication/maininbox', array(
            "classId" => $this->classId
                )
        );

        $this->view->URL_MAIN_SEND = $this->UTILES->buildURL(
                'communication/mainsend', array(
            "classId" => $this->classId
                )
        );

        $this->view->URL_MAIN_DRAFTS = $this->UTILES->buildURL(
                'communication/maindrafts', array(
            "classId" => $this->classId
                )
        );
    }

    public function inboxAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;

        $this->view->URL_MAIL_REPLY = $this->UTILES->buildURL(
                'communication/addreply', array(
            "classId" => $this->classId
                )
        );
    }

    public function maininboxAction() {

        $this->view->schoolyearId = $this->schoolyearId;

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;

        $this->view->URL_MAIL_INBOX = $this->UTILES->buildURL(
                'communication/inbox', array(
            "classId" => $this->classId
                )
        );
    }

    public function mainsendAction() {

        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;

        $this->view->URL_MAIL_SEND = $this->UTILES->buildURL(
                'communication/send', array(
            "classId" => $this->classId
                )
        );
    }

    public function maindraftsAction() {

        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;

        $this->view->URL_MAIL_TO_STUDENTS = $this->UTILES->buildURL(
                'communication/tostudents', array(
            "classId" => $this->classId
                )
        );

        $this->view->URL_MAIL_TO_TEACHER = $this->UTILES->buildURL(
                'communication/toteacher', array(
            "classId" => $this->classId
                )
        );

        $this->view->URL_MAIL_TO_STAFF = $this->UTILES->buildURL(
                'communication/tostaff', array(
            "classId" => $this->classId
                )
        );

        $this->view->URL_MAIL_REPLY_DRAFTS = $this->UTILES->buildURL(
                'communication/reply', array(
            "classId" => $this->classId
                )
        );
    }

    public function addreplyAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->senderData = $this->DB_COMMUNICATION->getSenderData($this->objectId);
    }

    public function replyAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->senderData = $this->DB_COMMUNICATION->getSenderData($this->objectId);
    }

    public function toteacherAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
    }

    public function tostudentsAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
    }

    public function tostaffAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
    }

    public function sendAction() {

        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
    }
    
    //@sea peng 22.04.2013
    public function tostudentAction() {
        
        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
    }  
    //@end sea peng 22.04.2013

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonLoadCommunication($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadAllCommunications":
                $jsondata = $this->DB_COMMUNICATION->jsonLoadAllCommunications($this->REQUEST->getPost());
                break;

            case "jsonLoadAddReplyCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonLoadAddReplyCommunication($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadInboxCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonLoadInboxCommunication($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadReplyCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonLoadReplyCommunication($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonSaveCommunication($this->REQUEST->getPost());
                break;

            case "jsonSendCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonSaveCommunication($this->REQUEST->getPost());
                break;

            case "jsonRemoveCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonRemoveCommunication($this->REQUEST->getPost());
                break;

            case "jsonRemoveMyCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonRemoveMyCommunication($this->REQUEST->getPost('objectId'));
                break;
            //@veasna
            case "jsonRemoveRecipientCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonRemoveRecipientCommunication($this->REQUEST->getPost());
                break;
            
            case "jsonSaveAlertFeeCommunication":
                $jsondata = StudentPaymentSettingDBAccess::jsonSaveAlertFeeCommunication($this->REQUEST->getPost());
                break;
            //
            
            //@sea peng (single sava and send email, sms, message)
            case "jsonActionSingleCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonActionSingleCommunication($this->REQUEST->getPost());
                break;
            //@ end sea peng
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllDrafsCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonTreeAllDrafsCommunication($this->REQUEST->getPost());
                break;

            case "jsonTreeAllSendCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonTreeAllSendCommunication($this->REQUEST->getPost());
                break;

            case "jsonTreeAllInboxCommunication":
                $jsondata = $this->DB_COMMUNICATION->jsonTreeAllInboxCommunication($this->REQUEST->getPost());
                break;
            //@sea peng 07.05.2013
            case "jsonTreeAllCommunicationSubject":
                $jsondata = $this->DB_CAMEMIS_TYPE->jsonTreeAllCamemisType($this->REQUEST->getPost());
                break;   
            //@end sea peng 07.05.2013
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