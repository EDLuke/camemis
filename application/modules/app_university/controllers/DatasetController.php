<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'Zend/Session.php';
require_once 'utiles/Utiles.php';
require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Controller/Action.php';
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/app_university/SessionAccess.php';
require_once 'models/app_university/CommunicationDBAccess.php';
require_once 'models/CAMEMISUploadDBAccess.php';
require_once 'models/CAMEMISYoutubeDBAccess.php';
require_once 'models/TextDBAccess.php';
require_once 'models/app_university/LocationDBAccess.php';
require_once 'models/app_university/BranchOfficeDBAccess.php';
require_once 'models/app_university/DescriptionDBAccess.php';
require_once 'models/app_university/LocationDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/school/SchoolFacilityDBAccess.php';
require_once 'models/app_university/finance/FinanceDescriptionDBAccess.php';
require_once 'models/app_university/finance/IncomeCategoryDBAccess.php';
require_once 'models/app_university/finance/ExpenseCategoryDBAccess.php';
require_once 'models/app_university/finance/TaxDBAccess.php';
require_once 'models/app_university/AbsentTypeDBAccess.php';
require_once 'models/app_university/ScholarshipDBAccess.php';
require_once 'models/app_university/PersonStatusDBAccess.php';
require_once 'models/CamemisTypeDBAccess.php';
require_once 'models/HealthSettingDBAccess.php';

require_once 'models/CAMEMISMySQLDump.php';

use CAMEMIS\CAMEMISMySQLDump\CAMEMISMySQLDump;

class DatasetController extends Zend_Controller_Action {

    public $dataforjson = null;
    protected $DB_ACADEMIC = null;

    public function init() {

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');
        //@veasna
        if ($this->_getParam('type'))
            $this->type = $this->_getParam('type');

        $this->DB_DESCRIPTION = DescriptionDBAccess::getInstance();
        $this->DB_LOCATION = LocationDBAccess::getInstance();
        $this->DB_BRANCH_OFFICE = BranchOfficeDBAccess::getInstance();
        $this->DB_UPLOAD = CAMEMISUploadDBAccess::getInstance();
        //@veasna
        $this->DB_SCHOLARSHIP = ScholarshipDBAccess::getInstance();
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
    }

    public function loginAction() {

        $DB_USER = UserDBAccess::getInstance();

        $DB_LOCALIZATION = TextDBAccess::getInstance();

        $SMG_TITLE_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("YOUR_AUTHENTICATION_HAS_FAILED");
        $SMG_TEXT_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("MSG_ERROR_LOGIN");

        $request = $this->getRequest();
        $LOGINNAME = addText($request->getPost("login"));
        $PASSWORD = addText($request->getPost("password"));
        $SYSTEM_LANGUAGE = addText($request->getPost("CHOOSE_LANGUAGE"));
        $tokenId = isset($_COOKIE['tokenId']) ? addText($_COOKIE['tokenId']) : '';

        $richtigerBenutzerName = $DB_USER->checkLoginOk($LOGINNAME);

        $isUserOnline = $DB_USER->checkCurrentUserOnline($LOGINNAME, $PASSWORD, $tokenId);

        switch ($SYSTEM_LANGUAGE) {
            case "VIETNAMESE":
                $SMG_TITLE = $SMG_TITLE_OBJECT->VIETNAMESE ? $SMG_TITLE_OBJECT->VIETNAMESE : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->VIETNAMESE ? $SMG_TEXT_OBJECT->VIETNAMESE : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "KHMER":
                $SMG_TITLE = $SMG_TITLE_OBJECT->KHMER ? $SMG_TITLE_OBJECT->KHMER : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->KHMER ? $SMG_TEXT_OBJECT->KHMER : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "THAI":
                $SMG_TITLE = $SMG_TITLE_OBJECT->THAI ? $SMG_TITLE_OBJECT->THAI : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->THAI ? $SMG_TEXT_OBJECT->THAI : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "GERMAN":
                $SMG_TITLE = $SMG_TITLE_OBJECT->GERMAN ? $SMG_TITLE_OBJECT->GERMAN : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->GERMAN ? $SMG_TEXT_OBJECT->GERMAN : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "LAO":
                $SMG_TITLE = $SMG_TITLE_OBJECT->LAO ? $SMG_TITLE_OBJECT->LAO : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->LAO ? $SMG_TEXT_OBJECT->LAO : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "BURMESE":
                $SMG_TITLE = $SMG_TITLE_OBJECT->BURMESE ? $SMG_TITLE_OBJECT->BURMESE : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->BURMESE ? $SMG_TEXT_OBJECT->BURMESE : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "INDONESIAN":
                $SMG_TITLE = $SMG_TITLE_OBJECT->INDONESIAN ? $SMG_TITLE_OBJECT->INDONESIAN : $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->INDONESIAN ? $SMG_TEXT_OBJECT->INDONESIAN : $SMG_TEXT_OBJECT->ENGLISH;
                break;
            case "ENGLISH":
                $SMG_TITLE = $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->ENGLISH;
                break;
            default:

                $SMG_TITLE = $SMG_TITLE_OBJECT->ENGLISH;
                $SMG_TEXT = $SMG_TEXT_OBJECT->ENGLISH;
                break;
        }

        if ($isUserOnline) {
            $json = "{'success':true, 'sessionId':'online'}";
        } else {
            $secureID = $DB_USER->Login($LOGINNAME, $PASSWORD);
            if ($secureID) {
                $json = "{'success':true, 'sessionId':'" . htmlentities(strip_tags($secureID)) . "', 'languageId': '" . htmlentities(strip_tags($SYSTEM_LANGUAGE)) . "'}";
            } else {
                $json = "{
                    'success':true
                    ,'sessionId':'failed'
                    ,'loginName':'" . $LOGINNAME . "'
                    ,'msgTitle':'" . $SMG_TITLE . "'
                    ,'msgText':'" . $SMG_TEXT . "'
                    ,'xxx': '" . $richtigerBenutzerName . "'
                }";
            }
        }

        if (isset($json)) {
            $this->getResponse()->setBody($json);
        }

        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
    }

    public function videoAction() {
        $this->view->objectId = $this->objectId;
    }

    public function dataviewAction() {
        $this->view->objectId = $this->_getParam('objectId');
        $this->view->object = $this->_getParam('object');
    }

    public function dataeditorAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->object = $this->_getParam('object');
    }

    public function attachmentAction() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function ckeditorAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function ckeditoradminAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function smalleditorAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function showcontentAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function showblobAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function displayfileAction() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
    }

    public function sendemailAction() {
        
    }

    public function linechartAction() {
        $this->view->setId = $this->_getParam('setId');
    }

    public function listAction() {
        $this->view->target = $this->target;
        switch (strtoupper($this->target)) {
            case "HEALTHSETTING":
                $this->_helper->viewRenderer("healthsetting/list");
                break;
            //@veasna    
            case "FORUMCATEGORIES":
                $this->view->typeForum = $this->type;
                $this->_helper->viewRenderer("forumcategories/list");
                break;
        }
    }

    public function showitemAction() {
        $this->view->target = $this->target;
        $this->view->objectId = $this->objectId;
        switch (strtoupper($this->target)) {
            case "HEALTHSETTING":
                $this->_helper->viewRenderer("healthsetting/show");
                break;
        }
    }

    public function addattachmentAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->object = $this->_getParam('object');
        $this->_helper->viewRenderer("upload/show");
    }

    public function uploadAction() {

        Zend_Registry::set('objectId', $this->_getParam('objectId'));
        Zend_Registry::set('object', $this->_getParam('object'));
        $this->_helper->viewRenderer("upload/action");
    }

    public function attachmentlistAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->object = $this->_getParam('object');
        $this->_helper->viewRenderer("upload/list");
    }

    public function imageAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->objectName = $this->_getParam('objectName');
    }

    public function settingAction() {
        $this->view->URL_DESCRIPTIOIN = $this->UTILES->buildURL('dataset/alldescription', array('personType' => $this->_getParam('personType')));
        $this->view->URL_DESCRIPTIOIN_TRAINING = $this->UTILES->buildURL('dataset/alldescription', array('personType' => "STUDENT_TRAINING"));
        $this->view->URL_LOCATION = $this->UTILES->buildURL('dataset/alllocation', array());
        $this->view->URL_ABSENT_TYPE = $this->UTILES->buildURL('dataset/allabsenttype', array());
    }

    public function allfacilitiesAction() {

        $this->view->URL_SHOW_FACILITY = $this->UTILES->buildURL('dataset/showfacility', array());
        $this->_helper->viewRenderer("facility/list");
    }

    public function alldescriptionAction() {

        $this->view->URL_SHOW_DESCRIPTION = $this->UTILES->buildURL('dataset/showdescription', array());
        $this->_helper->viewRenderer("description/list");
    }

    public function alllocationAction() {

        $this->view->URL_SHOW_LOCATION = $this->UTILES->buildURL('dataset/showlocation', array());
        $this->_helper->viewRenderer("location/list");
    }

    public function allbranchofficesAction() {

        $this->view->URL_SHOW_BRANCH_OFFICE = $this->UTILES->buildURL('dataset/showbranchoffice', array());
        $this->_helper->viewRenderer("branchoffice/list");
    }

    public function allpersonstatusAction() {
        $this->_helper->viewRenderer("personstatus/list");
    }

    //@veasna
    public function allscholarshiptypeAction() {
        $this->view->URL_SHOW_SCHOLARSHIP_TYPE = $this->UTILES->buildURL('dataset/showsholarshiptype', array());
        $this->_helper->viewRenderer("scholarshiptype/list");
    }

    public function showsholarshiptypeAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->parentId = $this->_getParam('parentId');
        //$this->view->target=$this->_getParam('target');
        $this->view->type = $this->_getParam('type');

        $this->_helper->viewRenderer("scholarshiptype/show");
    }

    public function scholarshipcontentAction() {
        $this->_helper->viewRenderer("scholarshiptype/showcontent");
    }

    //
    public function allabsenttypesAction() {
        $this->view->URL_SHOW_ABSENT_TYPE = $this->UTILES->buildURL('dataset/showabsenttype', array());
        $this->_helper->viewRenderer("absenttype/list");
    }

    public function showabsenttypeAction() {
        $this->view->objectId = $this->_getParam('objectId');
        $this->_helper->viewRenderer("absenttype/show");
    }

    public function showpersonstatusAction() {
        $this->view->objectId = $this->_getParam('objectId');
        $this->_helper->viewRenderer("personstatus/show");
    }

    public function showlocationAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->_helper->viewRenderer("location/show");
    }

    public function showbranchofficeAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->_helper->viewRenderer("branchoffice/show");
    }

    public function showdescriptionAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->facette = DescriptionDBAccess::findObjectFromId($this->_getParam('objectId'));
        $this->_helper->viewRenderer("description/show");
    }

    public function showfacilityAction() {

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->facette = SchoolFacilityDBAccess::findObjectFromId($this->_getParam('objectId'));
        $this->_helper->viewRenderer("facility/show");
    }

    public function authenticationAction() {
        
    }

    //@Sea Peng
    public function camemistypeAction() {
        $this->view->URL_SHOW_CAMEMIS_TYPE = $this->UTILES->buildURL('dataset/showcamemistype', array());
        $this->_helper->viewRenderer("camemistype/list");
    }

    public function showcamemistypeAction() {
        $this->view->parentId = $this->_getParam('parentId');
        $this->view->objectId = $this->_getParam('objectId');
        $this->view->type = $this->_getParam('type');
        $this->view->type = $this->_getParam('isParent');
        $this->_helper->viewRenderer("camemistype/show");
    }

    public function backupAction() {
        UserAuth::actionPermint($this->_request, "SCHOOL_SETTING");
    }

    public function remoteAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "jsonUserOnline":
                $DB_SECURE = SessionAccess::getInstance();
                $jsondata = $DB_SECURE->jsonUserOnline($this->REQUEST->getPost());
                break;
            case "jsonDeleteUserOnline":
                $DB_SECURE = SessionAccess::getInstance();
                $jsondata = $DB_SECURE->jsonDeleteUserOnline($this->REQUEST->getPost());
                break;
            case "jsonUserOnlineActivity":
                $DB_SECURE = SessionAccess::getInstance();
                $jsondata = $DB_SECURE->jsonUserOnlineActivity($this->REQUEST->getPost());
                break;
            case "actionLogoutWarning":
                $DB_SECURE = SessionAccess::getInstance();
                $jsondata = $DB_SECURE->actionLogoutWarning($this->REQUEST->getPost());
                break;
            case "jsonLoadNewMessage":
                $DB_COMMUNICATION = CommunicationDBAccess::getInstance();
                $jsondata = $DB_COMMUNICATION->jsonLoadNewMessage($this->REQUEST->getPost());
                break;

            case "sendmail":

                $empfaenger = $this->REQUEST->getPost('to');
                $betreff = $this->REQUEST->getPost('subject');
                $nachricht = $this->REQUEST->getPost('msg');
                $header = 'From: noreply@camemis.com' . "\r\n" .
                        'Reply-To: noreply@camemis.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
                mail($empfaenger, $betreff, $nachricht, $header);

                $jsondata = array(
                    "success" => true
                );
                break;
        }
        if (isset($jsondata))
            if (isset($jsondata))
                $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "treeAllLocations":
                $jsondata = LocationDBAccess::jsonTreeLocal($this->REQUEST->getPost());
                break;

            case "jsonTreeAllDescription":
                $jsondata = DescriptionDBAccess::jsonTreeAllDescription($this->REQUEST->getPost());
                break;

            case "jsonTreeAllFinancialDescription":
                $jsondata = FinanceDescriptionDBAccess::jsonTreeAllDescription($this->REQUEST->getPost());
                break;

            case "jsonTreeAllIncomeCategories":
                $jsondata = IncomeCategoryDBAccess::jsonTreeAllIncomeCategories($this->REQUEST->getPost());
                break;

            case "jsonTreeAllExpenseCategories":
                $jsondata = ExpenseCategoryDBAccess::jsonTreeAllExpenseCategories($this->REQUEST->getPost());
                break;

            case "jsonTreeAllTaxes":
                $jsondata = TaxDBAccess::jsonTreeAllTaxes($this->REQUEST->getPost());
                break;

            case "jsonTreeAllFacilities":
                $jsondata = SchoolFacilityDBAccess::jsonTreeAllFacilities($this->REQUEST->getPost());
                break;

            case "jsonTreeAllLocation":
                $jsondata = LocationDBAccess::jsonTreeAllLocation($this->REQUEST->getPost());
                break;

            case "jsonTreeAllBranchOffices":
                $jsondata = BranchOfficeDBAccess::jsonTreeAllBranchOffices($this->REQUEST->getPost());
                break;

            case "jsonTreeAllAbsentType":
                $jsondata = AbsentTypeDBAccess::jsonTreeAllAbsentType($this->REQUEST->getPost());
                break;

            //@veasna
            case "jsonTreeAllScholarship":
                $jsondata = $this->DB_SCHOLARSHIP->jsonTreeAllScholarship($this->REQUEST->getPost());
                break;
            //    
            case "jsonTreeAllPersonStatus":
                $jsondata = PersonStatusDBAccess::jsonTreeAllPersonStatus($this->REQUEST->getPost());
                break;

            //Sea Peng
            case "jsonTreeAllCamemisType":
                $jsondata = CamemisTypeDBAccess::jsonTreeAllCamemisType($this->REQUEST->getPost());
                break;

            case "jsonAllTreeHealthSetting":
                $jsondata = HealthSettingDBAccess::jsonAllTreeHealthSetting($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAttachmentList":
                $jsondata = CAMEMISUploadDBAccess::jsonAttachmentList($this->REQUEST->getPost());
                break;

            case "loadAllYoutube":
                $jsondata = CAMEMISYoutubeDBAccess::loadAllYoutube($this->REQUEST->getPost());
                break;

            case "jsonAllLocation":
                $jsondata = LocationDBAccess::jsonAllLocation($this->REQUEST->getPost('parentId'));
                break;

            case "jsonAllBranchOffices":
                $jsondata = BranchOfficeDBAccess::jsonAllBranchOffices($this->REQUEST->getPost('parentId'));
                break;

            case "jsonGrading":
                $jsondata = SpecialDBAccess::jsonGrading($this->REQUEST->getPost('edutype'));
                break;
            case "jsonSubject":
                $jsondata = SubjectDBAccess::jsonSubject($this->REQUEST->getPost('edutype'));
                break;

            case "loadAbsentType":
                $jsondata = AbsentTypeDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                break;

            //@veasna
            case "jsonLoadScholarship":
                $jsondata = ScholarshipDBAccess::jsonLoadScholarship($this->REQUEST->getPost('objectId'));
                break;

            case "jsonLoadPersonStatus":
                $jsondata = PersonStatusDBAccess::jsonLoadPersonStatus($this->REQUEST->getPost('objectId'));
                break;

            //Sea Peng
            case "loadCamemisType":
                $jsondata = CamemisTypeDBAccess::loadCamemisType($this->REQUEST->getPost('objectId'));
                break;
            
            //@Visal    
            case "jsonPunishment":
                $jsondata = CamemisTypeDBAccess::jsonPunishment($this->REQUEST->getPost());
                break;

            case "loadObject":
                switch ($this->REQUEST->getPost('type')) {
                    case "description":
                        $jsondata = DescriptionDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "finance":
                        $jsondata = FinanceDescriptionDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "income_category":
                        $jsondata = IncomeCategoryDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "expense_category":
                        $jsondata = ExpenseCategoryDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "facility":
                        $jsondata = SchoolFacilityDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "location":
                        $jsondata = LocationDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "branchoffice":
                        $jsondata = BranchOfficeDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "tax":
                        $jsondata = TaxDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                    case "personstatus":
                        $jsondata = PersonStatusDBAccess::loadObject($this->REQUEST->getPost('objectId'));
                        break;
                }
                break;

            //@Math Man
            case "jsonCheckLoginNameOrEmail":
                $jsondata = UserDBAccess::jsonCheckLoginNameOrEmail($this->REQUEST->getPost());
                break;

            case "jsonLoadHealthSetting":
                $jsondata = HealthSettingDBAccess::jsonLoadHealthSetting($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveHealthSetting":
                $jsondata = HealthSettingDBAccess::jsonSaveHealthSetting($this->REQUEST->getPost());
                break;

            case "removeSubHealthSetting":
                $jsondata = HealthSettingDBAccess::removeSubHealthSetting($this->REQUEST->getPost('removeId'));
                break;

            case "jsonRemoveHealthSetting":
                $jsondata = HealthSettingDBAccess::jsonRemoveHealthSetting($this->REQUEST->getPost('objectId'));
                break;

            case "jsonUploadFile":
                $jsondata = $this->DB_UPLOAD->jsonUploadFile($this->REQUEST->getPost());
                break;

            case "actionDeleteFile":
                $jsondata = $this->DB_UPLOAD->deleteFile($this->REQUEST->getPost('blobId'));
                break;

            case "actionYoutube":
                $jsondata = CAMEMISYoutubeDBAccess::actionYoutube($this->REQUEST->getPost());
                break;

            case "removeYoutube":
                $jsondata = CAMEMISYoutubeDBAccess::removeYoutube($this->REQUEST->getPost("removeId"));
                break;

            case "jsonSaveAbsentType":
                $jsondata = AbsentTypeDBAccess::jsonSaveAbsentType($this->REQUEST->getPost());
                break;

            case "releaseAbsentType":
                $jsondata = AbsentTypeDBAccess::jsonReleaseAbsentType($this->REQUEST->getPost());
                break;

            case "jsonRemoveAbsentType":
                $jsondata = AbsentTypeDBAccess::jsonRemoveAbsentType($this->REQUEST->getPost('objectId'));
                break;

            //@veasna
            case "jsonSaveSchoolarship":
                $jsondata = ScholarshipDBAccess::jsonSaveSchoolarship($this->REQUEST->getPost());
                break;

            case "jsonRemoveScholarship":
                $jsondata = ScholarshipDBAccess::jsonRemoveScholarship($this->REQUEST->getPost());
                break;
            //
            case "jsonSavePersonStatus":
                $jsondata = PersonStatusDBAccess::jsonSavePersonStatus($this->REQUEST->getPost());
                break;
            //            
            case "jsonRemovePersonStatus":
                $jsondata = PersonStatusDBAccess::jsonRemovePersonStatus($this->REQUEST->getPost('objectId'));
                break;

            //Sea Peng    
            case "jsonSaveCamemisType":
                $jsondata = CamemisTypeDBAccess::jsonSaveCamemisType($this->REQUEST->getPost());
                break;

            case "jsonRemoveCamemisType":
                $jsondata = CamemisTypeDBAccess::jsonRemoveCamemisType($this->REQUEST->getPost('objectId'));
                break;

            case "updateObject":
                switch ($this->REQUEST->getPost('type')) {
                    case "description":
                        $jsondata = DescriptionDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "income_category":
                        $jsondata = IncomeCategoryDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "expense_category":
                        $jsondata = ExpenseCategoryDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "finance":
                        $jsondata = FinanceDescriptionDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "facility":
                        $jsondata = SchoolFacilityDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "location":
                        $jsondata = LocationDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "branchoffice":
                        $jsondata = BranchOfficeDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                    case "tax":
                        $jsondata = TaxDBAccess::updateObject($this->REQUEST->getPost());
                        break;
                }
                break;

            case "jsonUserColumnSelection":
                $jsondata = Utiles::setGridColumnData($this->REQUEST->getPost("gridId"), $this->REQUEST->getPost("columdata"));
                break;

            case "addObject":
                switch ($this->REQUEST->getPost('type')) {
                    case "description":
                        $jsondata = DescriptionDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "finance":
                        $jsondata = FinanceDescriptionDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "income_category":
                        $jsondata = IncomeCategoryDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "expense_category":
                        $jsondata = ExpenseCategoryDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "facility":
                        $jsondata = SchoolFacilityDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "location":
                        $jsondata = LocationDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "branchoffice":
                        $jsondata = BranchOfficeDBAccess::addObject($this->REQUEST->getPost());
                        break;
                    case "tax":
                        $jsondata = TaxDBAccess::addObject($this->REQUEST->getPost());
                        break;
                }
                break;

            case "removeObject":
                switch ($this->REQUEST->getPost('type')) {
                    case "description":
                        $jsondata = DescriptionDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "finance":
                        $jsondata = FinanceDescriptionDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "income_category":
                        $jsondata = IncomeCategoryDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "expense_category":
                        $jsondata = ExpenseCategoryDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "facility":
                        $jsondata = SchoolFacilityDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "location":
                        $jsondata = LocationDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "branchoffice":
                        $jsondata = BranchOfficeDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                    case "tax":
                        $jsondata = TaxDBAccess::removeObject($this->REQUEST->getPost());
                        break;
                }
                break;
            ////////////////////////////////////////////////////////////////////
            //DATA BACK UP...
            ////////////////////////////////////////////////////////////////////
            case "actionBackUp":
                UserAuth::actionPermint($this->_request, "SCHOOL_SETTING");
                $jsondata = $this->actionBuckUp();
                break;
            case "actionDeleteBackUp":
                UserAuth::actionPermint($this->_request, "SCHOOL_SETTING");
                $jsondata = $this->actionDeleteBuckUp();
                break;
            ////////////////////////////////////////////////////////////////////
        }
        if (isset($jsondata))
            if (isset($jsondata))
                $this->setJSON($jsondata);
    }

    protected function actionDeleteBuckUp() {
        $filename = UserAuth::getUserPublicRoot() . "_dump.sql.gz";
        $myFile = "../public/users/" . UserAuth::getUserPublicRoot() . "/database/" . $filename . "";
        if (file_exists($myFile)) {
            unlink($myFile);
        }
        return array(
            "success" => true, "getFileName" => "---"
        );
    }

    protected function actionBuckUp() {

        $INCLUDE_TABLES = array();
        $EXCLUDE_TABLES = array();

        $dumpSettings = array(
            'include-tables' => $INCLUDE_TABLES,
            'exclude-tables' => $EXCLUDE_TABLES,
            'compress' => 'GZIP',
            'no-data' => false,
            'add-drop-database' => false,
            'add-drop-table' => false,
            'single-transaction' => true,
            'lock-tables' => false,
            'add-locks' => true,
            'extended-insert' => true,
            'disable-foreign-keys-check' => false
        );

        $CAMEMISMySQLDump = new CAMEMISMySQLDump(
                UserAuth::dbName()
                , UserAuth::dbUser()
                , UserAuth::dbPassword()
                , UserAuth::dbHost()
                , 'mysql'
                , $dumpSettings
        );

        if (UserAuth::getUserPublicRoot()) {
            $filename = UserAuth::getUserPublicRoot() . "_dump.sql";
            $CAMEMISMySQLDump->start("../public/users/" . UserAuth::getUserPublicRoot() . "/database/" . $filename . "");
            return array(
                "success" => true, "getFileName" => UserAuth::getFileBackUp()
            );
        }
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>