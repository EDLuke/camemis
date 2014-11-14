<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 26.02.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'clients/CamemisField.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/sms/SMSDBAccess.php';
require_once 'models/app_school/sms/SMSSettingDBAccess.php';
require_once 'models/app_school/sms/SendSMSDBAccess.php';
require_once 'models/app_school/sms/UserSMSDBAccess.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class SmsController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify() || !UserAuth::getACLValue("SMS_MANAGEMENT")) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->DB_SMS = SMSDBAccess::getInstance();

        $this->DB_SEND_SMS = SendSMSDBAccess::getInstance();

        $this->DB_USER_SMS = UserSMSDBAccess::getInstance();

        $this->DB_SMS_SETTING = SMSSettingDBAccess::getInstance();

        $this->objectId = null;
        $this->roleId = null;
        $this->OBJECT_DATA = array();
        $this->roll = null;
        $this->facette = null;

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->OBJECT_DATA = $this->DB_SMS->getSMSDataFromId($this->objectId);
            $this->facette = SMSDBAccess::findSMSFromId($this->objectId);
        }
    }

    public function indexAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
    }

    public function tostudentAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL("sms/showitem", array());
    }

    public function tostaffAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL("sms/showitem", array());
    }

    public function settingsAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->countSMSCredits = $this->DB_SMS_SETTING->getCountSMSCredits();
        $this->view->countSMSUsed = $this->DB_SMS_SETTING->countSMSUsed();
        $this->view->countSMSNotUsed = $this->DB_SMS_SETTING->countSMSNotUsed();
    }

    public function feesAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
    }

    public function registrationAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
    }

    public function usersmsAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
    }

    public function studentsbyclassAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->OBJECT_DATA = $this->OBJECT_DATA;
        $this->view->priority = isset($this->OBJECT_DATA["PRIORITY"]) ? $this->OBJECT_DATA["PRIORITY"] : "";
        $this->view->objectId = $this->objectId;
    }

    public function searchstudentsAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->OBJECT_DATA = $this->OBJECT_DATA;
        $this->view->priority = isset($this->OBJECT_DATA["PRIORITY"]) ? $this->OBJECT_DATA["PRIORITY"] : "";
        $this->view->objectId = $this->objectId;
    }

    public function searchstaffsAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->OBJECT_DATA = $this->OBJECT_DATA;
        $this->view->priority = isset($this->OBJECT_DATA["PRIORITY"]) ? $this->OBJECT_DATA["PRIORITY"] : "";
        $this->view->objectId = $this->objectId;
    }

    public function showitemAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");

        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
    }

    public function chartsentAction() {
        //UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
        $this->view->OBJECT_DATA = $this->OBJECT_DATA;
        $this->view->objectId = $this->objectId;
        $this->view->countSMSCredits = $this->DB_SMS_SETTING->getCountSMSCredits();
        $this->view->countSMSUsed = $this->DB_SMS_SETTING->countSMSUsed();
        $this->view->countSMSNotUsed = $this->DB_SMS_SETTING->countSMSNotUsed();
    }

    public function tostudentindividualAction() {
        
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAllSMS":
                $jsondata = $this->DB_SMS->jsonAllSMS($this->REQUEST->getPost());
                break;

            case "jsonLoadSMS":
                $jsondata = $this->DB_SMS->jsonLoadSMS($this->REQUEST->getPost('objectId'));
                break;

            case "jsonUnassignedStudentsSMS":
                $jsondata = $this->DB_SMS->jsonUnassignedStudentsSMS($this->REQUEST->getPost());
                break;

            case "jsonAssignedStudentsSMSServices":
                $jsondata = $this->DB_SMS->jsonAssignedStudentsSMSServices($this->REQUEST->getPost());
                break;

            case "jsonUnassignedStaffsSMS":
                $jsondata = $this->DB_SMS->jsonUnassignedStaffsSMS($this->REQUEST->getPost());
                break;

            case "jsonAssignedStaffsSMSServices":
                $jsondata = $this->DB_SMS->jsonAssignedStaffsSMSServices($this->REQUEST->getPost());
                break;

            case "jsonSearchSMS":
                $jsondata = $this->DB_SMS->jsonAllSMS($this->REQUEST->getPost());
                break;

            case "jsonLoadLogSMS":
                $jsondata = $this->DB_SMS->jsonLoadLogSMS($this->REQUEST->getPost());
                break;

            case "jsonLoadUserSMS":
                $jsondata = $this->DB_USER_SMS->jsonListUserSMS($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "actionStudentSMSRegistration":
                $jsondata = $this->DB_SMS->actionStudentSMSRegistration($this->REQUEST->getPost());
                break;

            case "jsonActionSaveSMSContent":
                $jsondata = $this->DB_SMS->jsonActionSaveSMSContent($this->REQUEST->getPost());
                break;

            case "jsonRemoveSMS":
                $jsondata = $this->DB_SMS->jsonRemoveSMS($this->REQUEST->getPost());
                break;

            case "actionAddStudentsToSMSSevices":
                $jsondata = $this->DB_SMS->actionAddStudentsToSMSSevices($this->REQUEST->getPost());
                break;

            case "actionAddStaffsToSMSSevices":
                $jsondata = $this->DB_SMS->actionAddStaffsToSMSSevices($this->REQUEST->getPost());
                break;

            case "jsonActionRemoveStudentFromSMSServices":
            case "jsonActionRemoveStaffFromSMSServices":
                $jsondata = $this->DB_SMS->jsonActionRemoveUserFromSMSServices($this->REQUEST->getPost());
                break;

            case "jsonRemoveAllStudentsFromSMSServices":
            case "jsonRemoveAllStaffsFromSMSServices":
                $jsondata = $this->DB_SMS->jsonRemoveAllUsersFromSMSServices($this->REQUEST->getPost());
                break;

            case "jsonCopySMS":
                $jsondata = $this->DB_SMS->jsonCopySMS($this->REQUEST->getPost());
                break;

            case "chooseAllPersons":
                $jsondata = $this->DB_SMS->chooseAllPersons($this->REQUEST->getPost());
                break;

            case "jsonSendSMSToSingleStudent":
                $jsondata = $this->DB_SEND_SMS->jsonSendSMSToSingleStudent($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsendAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSendSMSToAllPersons":
                $jsondata = $this->DB_SEND_SMS->jsonSendSMSToAllPersons($this->REQUEST->getPost());
                break;

            case "jsonSendSMSScoreToSingleStudent":
                $jsondata = $this->DB_SEND_SMS->jsonSendSMSScoreToSingleStudent($this->REQUEST->getPost());
                break;

            case "jsonActionSendScoreSMSToAllStudents":
                $jsondata = $this->DB_SEND_SMS->jsonActionSendScoreSMSToAllStudents($this->REQUEST->getPost());
                break;

            case "jsonSendStudentAbsence":
                $jsondata = $this->DB_SEND_SMS->jsonSendStudentAbsence($this->REQUEST->getPost());
                break;

            case "jsonSMSStudentSubjectAverage":
                $jsondata = $this->DB_SEND_SMS->jsonSMSStudentSubjectAverage($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

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