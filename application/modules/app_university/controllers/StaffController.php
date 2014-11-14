<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/staff/StaffImportDBAccess.php';
require_once 'models/app_university/staff/StaffImportDBAccess.php';
require_once 'models/app_university/DescriptionDBAccess.php';

require_once setUserLoacalization();
require_once 'models/UserAuth.php';

class StaffController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::mainidentify()) {
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

        $this->DB_STAFF = StaffDBAccess::getInstance();
        $this->DB_STAFF_IMPORT = StaffImportDBAccess::getInstance();

        /**
         * stafffId...
         */
        $this->objectId = null;

        $this->taskId = null;

        $this->taskObject = null;

        $this->gradeId = null;

        $this->classId = null;
        
        $this->academicId = null;

        $this->schoolyearId = null;

        $this->subjectId = null;

        $this->target = null;

        $this->trainingId = null;

        $this->facette = null; //@Math Man

        $this->objectData = array();
        $this->OBJECT = null;
        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            if (!StaffDBAccess::findStaffFromId($this->objectId)) {
                $this->_request->setControllerName('error');
            }

            $this->objectData = $this->DB_STAFF->getSaffDataFromId($this->objectId);
            $this->OBJECT = StaffStatusDBAccess::findIdStaffStatus($this->objectId);
        }

        if ($this->_getParam('trainingId')) {
            $this->trainingId = $this->_getParam('trainingId');
        }

        if ($this->_getParam('taskId')) {

            $this->taskId = $this->_getParam('taskId');
            $this->taskObject = $this->DB_STAFF->findTaskFromId($this->taskId);

            $this->gradeId = $this->taskObject->GRADE_ID;
            $this->subjectId = $this->taskObject->SUBJECT_ID;
            $this->classId = $this->taskObject->CLASS_ID;
        } else {
            $this->subjectId = $this->_getParam('subjectId');
        }
        
        if($this->_getParam('academicId'))
           $this->academicId = $this->_getParam('academicId');

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        $this->facette = StaffImportDBAccess::checkAllImportInTemp(); //@Math Man
    }

    public function indexAction() {

        $this->view->SEARCH = $this->UTILES->buildURL('staff/search', false, true);
    }

    public function generaleducationAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/traditionalsystem/generaleducation");
    }

    public function staffstatusAction() {

        $this->view->facette = $this->OBJECT;
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->_helper->viewRenderer("personal/showstatus");
    }

    //@veasna
    public function crediteducationsystemAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemeducation");
    }

    public function crediteducationsystemdetailAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->schoolyearId = $this->schoolyearId;

        $this->_helper->viewRenderer("personal/creditsystem/creditsystemeducationdetail");   //@veasna
    }

    public function creditsystemeducationtabAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemeducationtab");   //@veasna
    }

    public function creditsystemstudentattendancesettingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId; /////@THORN Visal

        $this->_helper->viewRenderer("personal/creditsystem/creditsystemstudentattendancesetting");   //@veasna
    }

    public function creditsystemstudentsettingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemstudentsetting");   //@veasna
    }

    public function creditsystemeventsettingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemeventsetting");   //@veasna
    }

    public function creditsystemsubjectdetailAction() {
        $this->view->classId = $this->classId;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemsubjectdetail");   //@THORN Visal
    }

    public function creditsystemassessmentsettingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemassessmentsetting");   //@THORN Visal
    }

    public function creditsystemhomeworksettingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemhomeworksetting");   //@veasna
    }

    public function creditsystemsubjectteachingreportsettingAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->_helper->viewRenderer("personal/creditsystem/creditsystemsubjectteachingreportsetting");   //@veasna
    }

    //
    public function trainingeducationAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/trainingsystem/trainingeducation");
    }

    public function generaleducationdetailAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("personal/traditionalsystem/generaleducationdetail");
    }

    public function trainingeducationdetailAction() {

        $this->view->objectId = $this->objectId;
        $this->view->trainingId = $this->trainingId;
        $this->_helper->viewRenderer("personal/trainingsystem/trainingeducationdetail");
    }

    public function importxlsAction() {

        $this->view->URL_TEMPLATE_XLS = $this->UTILES->buildURL('staff/templatexls', array());
        $this->view->URL_STAFF_IMPORT = $this->UTILES->buildURL('staff/importxls', array());
        $this->view->facette = $this->facette; //@Math Man
        $this->_helper->viewRenderer("import/importxls");
    }

    public function templatexlsAction() {
        $this->_helper->viewRenderer("import/templatexls");
    }

    public function staffmonitorAction() {

        //UserAuth::actionPermint($this->_request, "STAFF_PERSONAL_INFORMATION_READ_RIGHT");
        $this->view->objectId = $this->objectId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("personal/main");
    }

    public function statusbystaffAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        //$this->view->studentObject = $this->studentObject;
        $this->_helper->viewRenderer("personal/statusbystaff");
    }

    public function administrationAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/administration");
    }

    public function personinfosAction() {

        $this->view->objectId = $this->objectId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("personal/personinfos");
    }

    public function searchAction() {
        $this->view->URL_SEARCH_RESULT = $this->UTILES->buildURL('staff/searchresult', array());
    }

    public function descriptionAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/description");
    }

    public function staffsessionAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("school/session");
    }

    public function teachersubjectAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }

    public function liststaffstatusAction() {
        
    }

    public function staffAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $this->view->staffObject = StaffDBAccess::findStaffFromId($this->objectId);
        $this->_helper->viewRenderer("personal/person");
    }

    public function settingstaffstatusAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/settingstaffstatus");
    }

    public function showitemAction() {

        $tutor = isset($this->objectData["TUTOR"]) ? $this->objectData["TUTOR"] : 0;

        $this->view->objectId = $this->objectId;

        if (UserAuth::getAddedUserRole()) {
            $this->_helper->viewRenderer("school/tutor/index");
        } else {
            if ($tutor) {
                $this->_helper->viewRenderer("school/tutor/index");
            } else {
                $this->_redirect("/staff/staff/?objectId=" . $this->objectId . "");
            }
        }
    }

    public function showclassmainAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;

        $this->_helper->viewRenderer("school/tutor/classmain");
    }

    public function showsubjectmainAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;

        $this->_helper->viewRenderer("school/tutor/subjectmain");
    }

    public function eventcalendarAction() {

        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->objectData = $this->objectData;
    }

    public function subjectteacherAction() {

        $this->view->objectId = $this->objectId;
        $this->view->gradeId = $this->gradeId;
        $this->view->subjectId = $this->subjectId;
        $this->view->classId = $this->classId;
        $this->view->objectData = $this->objectData;

        $this->view->URL_SCORE_SUBJECT = $this->UTILES->buildURL(
                'evaluation/scoresubject'
                , array(
            "classId" => $this->classId
            , "teacherId" => $this->objectId
            , "subjectId" => $this->subjectId
                )
        );

        $this->view->URL_FIRST_SCORE_SUBJECT = $this->UTILES->buildURL(
                'evaluation/firstscoresubject'
                , array(
            "classId" => $this->classId
            , "teacherId" => $this->objectId
            , "subjectId" => $this->subjectId
                )
        );

        $this->view->URL_SECOND_SCORE_SUBJECT = $this->UTILES->buildURL(
                'evaluation/secondscoresubject'
                , array(
            "classId" => $this->classId
            , "teacherId" => $this->objectId
            , "subjectId" => $this->subjectId
                )
        );

        $this->view->URL_STUDENT_ASSESSMENT = $this->UTILES->buildURL(
                'assessment/subjectassessment'
                , array(
            "classId" => $this->classId
            , "teacherId" => $this->objectId
            , "subjectId" => $this->subjectId
                )
        );

        $this->view->URL_FIRST_EXPORT_SUBJECT = $this->UTILES->buildURL(
                'evaluation/exportassignments'
                , array(
            "classId" => $this->classId
            , "teacherId" => $this->objectId
            , "subjectId" => $this->subjectId
            , "term" => "FIRST_SEMESTER"
                )
        );

        $this->view->URL_SECOND_EXPORT_SUBJECT = $this->UTILES->buildURL(
                'evaluation/exportassignments'
                , array(
            "classId" => $this->classId
            , "teacherId" => $this->objectId
            , "subjectId" => $this->subjectId
            , "term" => "SECOND_SEMESTER"
                )
        );

        $this->view->URL_GRADE_SUBJECT = $this->UTILES->buildURL(
                'subject/showgradesubject'
                , array(
            "classId" => $this->classId
            , "subjectId" => $this->subjectId
            , "objectId" => $this->objectId
                )
        );
    }

    public function registrationAction() {

        $this->view->URL_STAFF_REGISTRATION = $this->UTILES->buildURL('staff/registration', array());
    }

    public function schoolyearteacherscheduleAction() {

        $this->_helper->viewRenderer("school/schoolyearteacherschedule");
    }

    public function schoolyearteacherstaffAction() {

        $this->_helper->viewRenderer("school/schoolyearteacherstaff");
    }

    public function schoolyearteacherstudentAction() {

        $this->_helper->viewRenderer("school/schoolyearteacherstudent");
    }

    public function changepasswordAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("personal/password");
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_STAFF->loadStaffFromId($this->REQUEST->getPost('objectId'));
                break;

            case "searchStaff":
                $jsondata = $this->DB_STAFF->searchStaffs($this->REQUEST->getPost());
                break;

            case "jsonUnassignedTeachers":
                $jsondata = $this->DB_STAFF->jsonUnassignedTeachers($this->REQUEST->getPost());
                break;

            case "jsonAssignedTeachers":
                $jsondata = $this->DB_STAFF->jsonAssignedTeachers($this->REQUEST->getPost());
                break;

            case "teachersByClassANDSubject":
                $jsondata = $this->DB_STAFF->teachersByClassANDSubject($this->REQUEST->getPost());
                break;

            case "importStaffs":
                $jsondata = $this->DB_STAFF_IMPORT->importStaffs($this->REQUEST->getPost());
                break;

            case "jsonAssignedTeachersByClass":
                $jsondata = $this->DB_STAFF->jsonAssignedTeachersByClass($this->REQUEST->getPost());
                break;

            case "jsonCheckStaffSchoolID":
                $jsondata = $this->DB_STAFF->jsonCheckStaffSchoolID($this->REQUEST->getPost());
                break;

            case "jsonStaffCampus":
                $jsondata = $this->DB_STAFF->jsonStaffCampus($this->REQUEST->getPost());
                break;

            case "jsonAllInstructors":
                $jsondata = $this->DB_STAFF->jsonAllInstructors($this->REQUEST->getPost());
                break;

            case "jsonLoadTeacherSubjectClass":
                $jsondata = $this->DB_STAFF->jsonLoadTeacherSubjectClass($this->REQUEST->getPost());
                break;

            case "jsonAllActiveStaff":
                $jsondata = $this->DB_STAFF->jsonAllActiveStaff($this->REQUEST->getPost());
                break;

            case "jsonStaffQualification":
                $jsondata = StaffDBAccess::jsonStaffQualification($this->REQUEST->getPost('objectId'));
                break;

            case "jsonStaffExperience":
                $jsondata = StaffDBAccess::jsonStaffExperience($this->REQUEST->getPost());
                break;

            case "jsonStaffSkill":
                $jsondata = StaffDBAccess::jsonStaffSkill($this->REQUEST->getPost());
                break;

            case "loadStaffDescripton":
                $jsondata = $this->DB_STAFF->loadStaffDescripton($this->REQUEST->getPost('objectId'));
                break;

            case "jsonStaffTrainingPrograms":
                $jsondata = StaffDBAccess::jsonStaffTrainingPrograms($this->REQUEST->getPost('staffId'));
                break;

            case "jsonLoadLastStaffStatus":
                $jsondata = StaffStatusDBAccess::jsonLoadLastStaffStatus($this->REQUEST->getPost('objectId'));
                break;

            case "jsonListStaffStatus":
                $jsondata = StaffStatusDBAccess::jsonListStaffStatus($this->REQUEST->getPost());
                break;

            case "jsonSearchStaffStatus":
                $jsondata = StaffStatusDBAccess::jsonSearchStaffStatus($this->REQUEST->getPost());
                break;
            ///@veasna
            case "jsonCreditClassInAcademic":
                $jsondata = ScheduleDBAccess::jsonCreditClassInAcademic($this->REQUEST->getPost());
                break;

            case "jsonListPersonInfos":
                $jsondata = StaffDBAccess::jsonListPersonInfos($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_STAFF->updateStaff($this->REQUEST->getPost());
                break;
            case "releaseObject":
                $jsondata = $this->DB_STAFF->releaseObject($this->REQUEST->getPost());
                break;
            case "registrationRecord":
                $jsondata = $this->DB_STAFF->registrationRecord($this->REQUEST->getPost());
                break;
            ///////////////////////////////////////////////
            // jsonAddTeacherToSchoolYear
            ///////////////////////////////////////////////
            case "jsonAddTeacherToSchoolYear":
                $jsondata = $this->DB_STAFF->jsonAddTeacherToSchoolYear($this->REQUEST->getPost());
                break;
            ///////////////////////////////////////////////
            // jsonRemoveTeacherSchoolYear
            ///////////////////////////////////////////////
            case "jsonRemoveTeacherSchoolYear":
                $jsondata = $this->DB_STAFF->jsonRemoveTeacherSchoolYear($this->REQUEST->getPost());
                break;
            ///////////////////////////////////////////////
            // Remove: Staff from School
            ///////////////////////////////////////////////
            case "jsonRemoveStaffFromSchool":
                $jsondata = $this->DB_STAFF->jsonRemoveStaffFromSchool($this->REQUEST->getPost());
                break;
            case "jsonAddStaffToStaffDB":
                $jsondata = $this->DB_STAFF_IMPORT->jsonAddStaffToStaffDB($this->REQUEST->getPost());
                break;

            case "jsonRemoveStaffsFromImport":
                $jsondata = $this->DB_STAFF_IMPORT->jsonRemoveStaffsFromImport($this->REQUEST->getPost());
                break;

            case "actionsStaffCampus":
                $jsondata = $this->DB_STAFF->actionsStaffCampus($this->REQUEST->getPost());
                break;

            case "jsonActionChangeStaffImport":
                $jsondata = $this->DB_STAFF_IMPORT->jsonActionChangeStaffImport($this->REQUEST->getPost());
                break;

            case "jsonActionTeacherChange":
                $jsondata = $this->DB_STAFF->jsonActionTeacherChange($this->REQUEST->getPost());
                break;

            case "actionStaffDescription":
                $jsondata = StaffDBAccess::actionStaffDescription($this->REQUEST->getPost());
                break;

            case "actionStaffQualification":
                $jsondata = StaffDBAccess::actionStaffQualification($this->REQUEST->getPost());
                break;

            case "actionStaffExperience":
                $jsondata = StaffDBAccess::actionStaffExperience($this->REQUEST->getPost());
                break;

            case "actionStaffSkill":
                $jsondata = StaffDBAccess::actionStaffSkill($this->REQUEST->getPost());
                break;

            case "actionStaffProfil":
                $jsondata = StaffDBAccess::actionStaffProfil($this->REQUEST->getPost());
                break;

            case "jsonActionSaveLastStaffStatus":
                $jsondata = StaffStatusDBAccess::jsonActionSaveLastStaffStatus($this->REQUEST->getPost());
                break;

            case "actionPersonInfos":
                $jsondata = StaffDBAccess::actionPersonInfos($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonimportAction() {

        $this->DB_STAFF_IMPORT->importXLS($this->REQUEST->getPost());

        Zend_Loader::loadClass('Zend_Json');

        if (isset($jsondata))
            $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        if (isset($json))
            $this->getResponse()->setBody($json);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "assignedTeachersByGrade":
                $jsondata = $this->DB_STAFF->assignedTeachersByGrade($this->REQUEST->getPost());
                break;

            case "academicHistoryTree":
                $jsondata = $this->DB_STAFF->academicHistoryTree($this->REQUEST->getPost());
                break;

            case "treeAllTutors":
                $jsondata = $this->DB_STAFF->treeAllTutors($this->REQUEST->getPost());
                break;

            case "jsonTreeAllStaffDescription":
                $jsondata = DescriptionDBAccess::jsonTreeAllStaffDescription($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    protected function accessItems() {

        $js = "";

        switch ($this->view->ROLE) {
            case "ROLE_TEACHER":
            case "ROLE_INSTRUCTOR":
                $js .= "Ext.getCmp('RELEASE_ID').disable();";
                break;
            case "ROLE_ADMINISTRATOR":
                $js .= "Ext.getCmp('MY_ACCOUNT').disable();";
                break;
        }

        return $js;
    }

    public function classdetailsAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
    }

    public function schoolyeardetailsAction() {
        
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>