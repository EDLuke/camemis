<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.07.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/SpecialDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/AcademicAdditionalDBAccess.php'; //@Sea Peng

class AcademicsettingController extends Zend_Controller_Action {

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

        $this->objectId = null;
        $this->target = null;

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');
    }

    public function indexAction() {
        //
    }

    public function logAction() {

        switch ($this->_getParam('type')) {
            case "academic":
                $this->_helper->viewRenderer("log/academic/show");
                break;
        }
    }

    public function traditionalsystemtabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function creditsystemtabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function educationsystemtabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function staffattendancetabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function trainingeducationtabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function financetabsAction() {

        UserAuth::actionPermint($this->_request, "FINANCIAL_MANAGEMENT");
    }

    public function systemusertabsAction() {
        UserAuth::actionPermint($this->_request, "SYSTEM_USER");
    }

    public function smstabsAction() {

        UserAuth::actionPermint($this->_request, "SMS_MANAGEMENT");
    }

    public function schoolsettingtabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function examinationtabsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
    }

    public function studentimporttabsAction() {

        UserAuth::actionPermint($this->_request, "STUDENT_MODUL_IMPORT_FROM_XLS_FILE");
    }

    public function studentdisciplinetabsAction() {
        $this->view->target = $this->target;
        UserAuth::actionPermint($this->_request, "STUDENT_DISCIPLINE");
    }

    public function studentgeneraldisciplinetabsAction() {
        $this->view->target = $this->target;
        UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
    }

    public function studentattendancetabsAction() {
        $this->view->target = $this->target;
        UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
    }

    public function studentgeneralattendancetabsAction() {
        $this->view->target = $this->target;
        UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
    }

    public function studenttrainingattendancetabsAction() {
        $this->view->target = $this->target;
        UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
    }

    public function gradingsystemAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->_helper->viewRenderer("gradingsystem/main");
    }

    public function showgradingsystemAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->_helper->viewRenderer("gradingsystem/show");
    }

    public function gradingsystemlistAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOW_RELIGION = $this->UTILES->buildURL('academicsetting/showreligion', array());

        $this->_helper->viewRenderer("gradingsystem/list");
    }

    public function allassignmentsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOW_ASSIGNMENT_TEMP = $this->UTILES->buildURL('assignment/showtemp', array());
    }

    public function alltrainingassignmentsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOW_ASSIGNMENT_TEMP = $this->UTILES->buildURL('assignment/showtemp', array());
    }

    public function allgeneralassignmentsAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOW_ASSIGNMENT_TEMP = $this->UTILES->buildURL('assignment/showtemp', array());
    }

    public function additionalinformationAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->URL_SHOW_ADDITIONAL_INFORMATION = $this->UTILES->buildURL('academicsetting/showadditionalinformation', array());
        $this->_helper->viewRenderer("additionalinformation/list");
    }

    public function showadditionalinformationAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("additionalinformation/show");
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadLogAcademic":
                $jsondata = SpecialDBAccess::jsonLoadLogAcademic($this->REQUEST->getPost());
                break;
            case "jsonListGradingSystems":
                $jsondata = SpecialDBAccess::jsonListGradingSystems($this->REQUEST->getPost());
                break;
            case "jsonLoadGradingSystem":
                $jsondata = SpecialDBAccess::jsonLoadGradingSystem($this->REQUEST->getPost('objectId'));
                break;
            case "jsonLoadAdditionalInformation":
                $jsondata = AcademicAdditionalDBAccess::jsonLoadAcademicAdditional($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonActionGradingSystem":
                $jsondata = SpecialDBAccess::jsonActionGradingSystem($this->REQUEST->getPost());
                break;

            case "jsonActonRemove":
                $jsondata = SpecialDBAccess::jsonActonRemove($this->REQUEST->getPost('objectId'));
                break;

            case "jsonSaveAcademicAdditional":
                $jsondata = AcademicAdditionalDBAccess::jsonSaveAcademicAdditional($this->REQUEST->getPost());
                break;

            case "jsonRemoveAcademicAdditional":
                $jsondata = AcademicAdditionalDBAccess::jsonRemoveAcademicAdditional($this->REQUEST->getPost('objectId'));
                break;

            case "jsonAcademicToAdditionalInformation":
                $jsondata = AcademicAdditionalDBAccess::jsonAcademicToAdditionalInformation($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAllTreeAdditionalInformation":
                $jsondata = AcademicAdditionalDBAccess::jsonAllTreeAdditionalInformation($this->REQUEST->getPost());
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