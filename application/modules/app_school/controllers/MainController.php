<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Updated : 23.01.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'Zend/Loader.php';
require_once 'Zend/Http/Cookie.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/app_school/school/SchoolDBAccess.php';
require_once 'models/app_school/user/UserMemberDBAccess.php';
require_once 'models/UserAuth.php';

class MainController extends Zend_Controller_Action {

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

        $this->DB_USER = UserMemberDBAccess::getInstance();
        $this->SCHOOL = SchoolDBAccess::getSchool();
    }

    ////////////////////////////////////////////////////////////////////////////
    //DASHBOARD....
    ////////////////////////////////////////////////////////////////////////////
    public function userdashboardAction() {
        $this->_helper->viewRenderer("dashboard/user/index");
    }

    public function dashboardenrolledstudentAction() {
        $this->_helper->viewRenderer("dashboard/user/enrolledstudent");
    }

    public function dashboardstudentattendanceAction() {
        $this->_helper->viewRenderer("dashboard/user/studentattendance");
    }

    public function dashboardstaffattendanceAction() {
        $this->_helper->viewRenderer("dashboard/user/staffattendance");
    }

    public function dashboardstaffdisciplineAction() {
        $this->_helper->viewRenderer("dashboard/user/staffdiscipline");
    }

    public function dashboardstudentdisciplineAction() {
        $this->_helper->viewRenderer("dashboard/user/studentdiscipline");
    }

    public function studentdashboardAction() {
        $this->_helper->viewRenderer("dashboard/student/index");
    }

    public function dashboardstudentassessmentAction() {
        $this->_helper->viewRenderer("dashboard/student/assessment");
    }

    public function studentdashboardnewsAction() {
        $this->_helper->viewRenderer("dashboard/student/news");
    }

    public function teacherdashboardnewsAction() {
        $this->_helper->viewRenderer("dashboard/teacher/news");
    }

    public function dashboardfacilityAction() {
        $this->_helper->viewRenderer("dashboard/user/facility");
    }

    public function dashboardstaffcontractAction() {
        $this->_helper->viewRenderer("dashboard/user/staffcontract");
    }

    ////////////////////////////////////////////////////////////////////////////

    public function permissionAction() {
        
    }

    public function mycalendarAction() {
        
    }

    public function indexAction() {

        $this->setMainIndexApplication();
        $this->view->SCHOOL = $this->SCHOOL;

        //error_log(UserAuth::getUserType());
        switch (UserAuth::getUserType()) {
            case "STUDENT":
                $this->_helper->viewRenderer('student');
                break;
            case "GUARDIAN":
                $this->_helper->viewRenderer('guardian');
                break;
            case "TEACHER":
            case "INSTRUCTOR":
                $this->_helper->viewRenderer('teacher');
                break;
            default:
                if (UserAuth::getAddedUserRole()) {
                    $this->_helper->viewRenderer('teacher');
                }
                break;
        }
    }

    public function showstudentmainAction() {
        switch (UserAuth::getUserType()) {
            case "ADMIN":
            case "SUPERADMIN":
            case "GUARDIAN":
                $this->_helper->viewRenderer('student');
                break;
        }
    }

    public function homeAction() {

        $this->view->SCHOOL = $this->SCHOOL;

        if (Zend_Registry::get('ADDITIONAL_ROLE')) {
            $this->view->URL_USER_LOGIN = $this->UTILES->buildURL('staff/tutor', array("objectId" => Zend_Registry::get('USERID')));
        } else {
            $this->view->URL_USER_LOGIN = $this->UTILES->buildURL('staff/showitem', array("objectId" => Zend_Registry::get('USERID')));
        }

        $this->view->URL_TEST_CHART = $this->UTILES->buildURL('main/chart', array());
        $this->view->URL_HOME_CHART = $this->UTILES->buildURL('school/homechart', array());
        $this->view->URL_VIDEO = $this->UTILES->buildURL('school/video', array());
    }

    public function ldapAction() {
        
    }

    public function chatAction() {
        
    }

    public function verticalbarchartAction() {
        
    }

    public function piechartAction() {
        
    }

    private function studentModul() {

        $STUDENT_SEARCH = "{
            text: '" . STUDENT_SEARCH . "'
            ,iconCls:'icon-zoom'
            ,handler: function(){
                addTab('STUDENT_SEARCH','" . STUDENT_SEARCH . "','" . $this->UTILES->buildURL('student/search', array()) . "');
            }
        } ";

        $STUDENT_REGISTRATION_WIZARD = "{
            text: '" . STUDENT_REGISTRATION_WIZARD . "'
            ,iconCls:'icon-user_add'
            ,handler: function(){
                addTab('STUDENT_REGISTRATION_WIZARD','" . STUDENT_REGISTRATION_WIZARD . "','" . $this->UTILES->buildURL('student/registration', array()) . "');
            }
        } ";

        $STUDENT_ATTENDANCE = "{
            text: '" . STUDENT_ATTENDANCE . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('STUDENT_ATTENDANCE','" . STUDENT_ATTENDANCE . "','/academicsetting/studentattendancetabs/?key=" . camemisId() . "');
            }
        } ";

        $STUDENT_DISCIPLINE = "{
            text: '" . STUDENT_DISCIPLINE . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('STUDENT_DISCIPLINE','" . STUDENT_DISCIPLINE . "','/discipline/?camIds=" . $this->urlEncryp->encryptedGet('personType=student') . "');
            }
        } ";

        $STUDENT_STATUS = "{
            text: '" . STUDENT_STATUS . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('LIST_OF_STUDENT_STATUS','" . STUDENT_STATUS . "','" . $this->UTILES->buildURL('student/liststudentstatus', array()) . "');
            }
        } ";

        $SCHOLARSHIP = "{
            text: '" . SCHOLARSHIP . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('SCHOLARSHIP','" . SCHOLARSHIP . "','" . $this->UTILES->buildURL('student/searchstudentscholarship', array()) . "');
            }
        } ";

        $PRE_SCHOOL = "{
            text: '" . PRE_SCHOOL . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('PRE_SCHOOL','" . PRE_SCHOOL . "','" . $this->UTILES->buildURL('studentpreschool/studentpreschoolmain') . "');
            }
        } ";

        $STUDENT_SETTING = "{
            text: '" . STUDENT_SETTING . "'
            ,iconCls:'icon-wrench_orange'
            ,handler: function(){
                addTab('SCHOOL_SETTINGS','" . STUDENT_SETTING . "','/dataset/setting/?camIds=" . $this->urlEncryp->encryptedGet('personType=STUDENT') . "');
            }
        } ";

        $STUDENT_HEALTH = "{
            text: '" . STUDENT_HEALTH . "'
            ,iconCls:'icon-wrench_orange'
            ,menu:[{
                text:'" . HEALTH_INFORMATION . "'
                ,iconCls:'icon-brick'
                ,handler: function(){
                    addTab('HEALTH_INFORMATION','" . STUDENT_MODUL . " &raquo; " . HEALTH_INFORMATION . "','" . $this->UTILES->buildURL('health', array()) . "');
                }
            },{
                text:'" . HEALTH_SETTING . "'
                ,iconCls:'icon-brick'
                ,handler: function(){
                    addTab('HEALTH_INFORMATION','" . STUDENT_MODUL . " &raquo; " . HEALTH_SETTING . "','/dataset/list/?camIds=" . $this->urlEncryp->encryptedGet("target=HealthSetting") . "');
                }
            }]
        } ";

        $STUDENT_ADVISORY = "{
            text: '" . STUDENT_ADVISORY . "'
            ,iconCls:'icon-bell'
            ,handler: function(){
                addTab('STUDENT_ADVISORY','" . STUDENT_ADVISORY . "','/advisory/studentadvisorymain/?key=" . camemisId() . "');
            }
        } ";

        $GUARDIAN = "{
            text: '" . GUARDIAN . "'
            ,iconCls:'icon-group_add'
            ,handler: function(){
                addTab('GUARDIAN','" . GUARDIAN . "','/guardian/?key=" . camemisId() . "');    
            }
        } ";

        $ALUMNI_MANAGEMENT = "{
            text: '" . ALUMNI_MANAGEMENT . "'
            ,iconCls:'icon-wrench_orange'
            ,menu:[{
                text: '" . EVENT . "'
                ,disabled:true
                ,iconCls:'icon-brick'
            },{
                text: '" . FORUM . "'
                ,iconCls:'icon-brick'
                ,handler: function(){
                    addTab('FORUM','" . FORUM . "','/forum/?key=" . camemisId() . "&camIds=" . $this->urlEncryp->encryptedGet("target=alumni") . "');    
                }
            },{
                text: '" . MAILING_LIST . "'
                ,disabled:true
                ,iconCls:'icon-brick'
            },{
                text: '" . SETTING . "'
                ,disabled:true
                ,iconCls:'icon-brick'
            }]
        } ";

        $CHOOSE_STUDENT_MODUL = array();

        if (UserAuth::getACLValue("STUDENT_SEARCH"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_SEARCH . "";

        if (UserAuth::getACLValue("STUDENT_REGISTRATION_WIZARD"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_REGISTRATION_WIZARD . "";

        $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_HEALTH . "";

        if (UserAuth::getACLValue("STUDENT_ATTENDANCE"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_ATTENDANCE . "";

        if (UserAuth::getACLValue("STUDENT_DISCIPLINE"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_DISCIPLINE . "";

        if (UserAuth::getACLValue("STUDENT_STATUS"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_STATUS . "";

        if (UserAuth::getACLValue("STUDENT_ADVISORY"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_ADVISORY . "";

        if (UserAuth::getACLValue("SCHOLARSHIP"))
            $CHOOSE_STUDENT_MODUL[] = "" . $SCHOLARSHIP . "";

        $CHOOSE_STUDENT_MODUL[] = "" . $PRE_SCHOOL . "";

        if (UserAuth::getACLValue("GUARDIAN"))
            $CHOOSE_STUDENT_MODUL[] = "" . $GUARDIAN . "";

        if (UserAuth::getACLValue("STUDENT_SETTING"))
            $CHOOSE_STUDENT_MODUL[] = "" . $STUDENT_SETTING . "";

        $TBAR_STUDENT_MODUL = implode(",", $CHOOSE_STUDENT_MODUL);

        $js = "
        text: '" . STUDENT_MODUL . "'
        ,iconAlign: 'left'
        ,iconCls:'icon-bricks'
        ,menu: [" . $TBAR_STUDENT_MODUL . "]
        ";

        return $js = $CHOOSE_STUDENT_MODUL ? $js : "";
    }

    private function staffModul() {

        $STAFF_SEARCH = "{
            text: '" . STAFF_SEARCH . "'
            ,iconCls:'icon-zoom'
            ,handler: function(){
                addTab('STAFF_SEARCH','" . STAFF_SEARCH . "','" . $this->UTILES->buildURL('staff/search', array()) . "');
            }
        } ";

        $STAFF_REGISTRATION_WIZARD = "{
            text: '" . STAFF_REGISTRATION_WIZARD . "'
            ,iconCls:'icon-user_add'
            ,handler: function(){
                addTab('STAFF_REGISTRATION_WIZARD','" . STAFF_REGISTRATION_WIZARD . "','" . $this->UTILES->buildURL('staff/registration', array()) . "');
            }
        } ";

        $STAFF_ATTENDANCE = "{
            text: '" . STAFF_ATTENDANCE . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('STAFF_ATTENDANCE','" . STAFF_ATTENDANCE . "','/academicsetting/staffattendancetabs/');
            }
        } ";

        $STAFF_DISCIPLINE = "{
            text: '" . STAFF_DISCIPLINE . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('STAFF_DISCIPLINE','" . STAFF_DISCIPLINE . "','/discipline/?camIds=" . $this->urlEncryp->encryptedGet('personType=staff') . "');
            }
        } ";

        $TEACHING_SESSION = "{
            text: '" . TEACHING_SESSION . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('TEACHING_REPORT','" . TEACHING_SESSION . "','" . $this->UTILES->buildURL('schedule/listteachingsession', array()) . "');
            }
        } ";

        $IMPORT_FROM_XLS_FILE = "{
            text: '" . IMPORT_FROM_XLS_FILE . "'
            ,iconCls:'icon-database_save'
            ,handler: function(){
                addTab('IMPORT_FROM_XLS_FILE_GENERAL','" . IMPORT_FROM_XLS_FILE . " (" . STAFF . ")','" . $this->UTILES->buildURL('staff/importxls', array()) . "');
            }
        } ";

        $STAFF_STATUS = "{
            text: '" . STAFF_STATUS . "'
            ,iconCls:'icon-report_edit'
            ,handler: function(){
                addTab('LIST_OF_STAFF_STATUS','" . STAFF_STATUS . "','" . $this->UTILES->buildURL('staff/liststaffstatus', array()) . "');
            }
        } ";

        $STAFF_CONTRACT = "{
            text: '" . STAFF_CONTRACT . "'
            ,iconCls:'icon-book_red'
            ,handler: function(){
                addTab('STAFF_CONTRACT','" . STAFF_CONTRACT . "','" . $this->UTILES->buildURL('staffcontract/staffcontractmain') . "');
            }
        } ";

        $STAFF_SETTING = "{
            text: '" . STAFF_SETTING . "'
            ,iconCls:'icon-wrench_orange'
            ,handler: function(){
                addTab('STAFF_SETTING','" . STAFF_SETTING . "','/dataset/setting/?camIds=" . $this->urlEncryp->encryptedGet('personType=STAFF') . "');
            }
        } ";

        $CHOOSE_MODUL = array();

        if (UserAuth::getACLValue("STAFF_SEARCH"))
            $CHOOSE_MODUL[] = "" . $STAFF_SEARCH . "";

        if (UserAuth::getACLValue("STAFF_REGISTRATION_WIZARD"))
            $CHOOSE_MODUL[] = "" . $STAFF_REGISTRATION_WIZARD . "";

        if (UserAuth::getACLValue("STAFF_ATTENDANCE"))
            $CHOOSE_MODUL[] = "" . $STAFF_ATTENDANCE . "";

        if (UserAuth::getACLValue("STAFF_DISCIPLINE"))
            $CHOOSE_MODUL[] = "" . $STAFF_DISCIPLINE . "";

        if (UserAuth::getACLValue("TEACHING_SESSION"))
            $CHOOSE_MODUL[] = "" . $TEACHING_SESSION . "";

        if (UserAuth::getACLValue("IMPORT_FROM_XLS_FILE"))
            $CHOOSE_MODUL[] = "" . $IMPORT_FROM_XLS_FILE . "";

        if (UserAuth::getACLValue("STAFF_CONTRACT"))
            $CHOOSE_MODUL[] = "" . $STAFF_CONTRACT . "";

        if (UserAuth::getACLValue("STAFF_STATUS"))
            $CHOOSE_MODUL[] = "" . $STAFF_STATUS . "";

        if (UserAuth::getACLValue("STAFF_SETTING"))
            $CHOOSE_MODUL[] = "" . $STAFF_SETTING . "";

        $CHOOSE_TOOLBAR_ITEMS = implode(",", $CHOOSE_MODUL);

        $js = "
        text: '" . STAFF_MODUL . "'
        ,iconAlign: 'left'
        ,iconCls:'icon-bricks'
        ,menu: [" . $CHOOSE_TOOLBAR_ITEMS . "]
        ";

        return $CHOOSE_MODUL ? $js : "";
    }

    private function academicModul() {

        ////////////////////////////////////////////////////////////////////////
        //EXAM
        ////////////////////////////////////////////////////////////////////////

        $EXAMINATION_MANAGEMENT = "{
            text: '" . EXAMINATION_MANAGEMENT . "'
            ,iconCls:'icon-clipboard_add'
            ,handler: function(){
                addTab('ENROLLMENT_EXAMINATION','" . ACADEMIC_MODUL . " &raquo; " . EXAMINATION_MANAGEMENT . "','/academicsetting/examinationtabs/');
            }
        } ";

        ////////////////////////////////////////////////////////////////////////
        //ELEARNING...
        $ELEARNING_MANAGEMENT = "{
            text: 'CAMEMIS for Online Course'
            ,iconCls:'icon-arrow_in'
            ,handler: function(){
                addTab('ENROLLMENT_EXAMINATION','" . ACADEMIC_MODUL . " &raquo; CAMEMIS for Online Course','/elearning/');
            }
        } ";

        ////////////////////////////////////////////////////////////////////////
        //CLASS_TRANSFER...
        $CLASS_TRANSFER = "{
            text: '" . CLASS_TRANSFER . "'
            ,iconCls:'icon-graduate'
            ,handler: function(){
                addTab('ENROLLMENT_EXAMINATION','" . ACADEMIC_MODUL . " &raquo; " . CLASS_TRANSFER . "','/enrollment/');
            }
        } ";

        ////////////////////////////////////////////////////////////////////////
        ///ACADEMIC_PERFORMANCES
        ////////////////////////////////////////////////////////////////////////

        $CHOOSE_ACADEMIC_PERFORMANCES_ITEMS = array();

        ////////////////////////////////////////////////////////////////////////
        //ACADEMIC_MODUL
        ////////////////////////////////////////////////////////////////////////

        if (UserAuth::getACLValue("SCORES_MANAGEMENT")) {
            $HIDDEN_SCORES_MANAGEMENT = 'false';
        } else {
            $HIDDEN_SCORES_MANAGEMENT = 'true';
        }

        if (UserAuth::getACLValue("STUDENT_ATTENDANCE")) {
            $HIDDEN_STUDENT_ATTENDANCE = 'false';
        } else {
            $HIDDEN_STUDENT_ATTENDANCE = 'true';
        }

        $EXTRA_CLASS_MANAGEMENT = "{
            text: '" . EXTRA_CLASS_MANAGEMENT . "'
            ,iconCls:'icon-user_home'
            ,handler: function(){
                addTab('EXTRA_CLASS_MANAGEMENT','" . ACADEMIC_MODUL . " &raquo; " . EXTRA_CLASS_MANAGEMENT . "','" . $this->UTILES->buildURL('extraclass', array()) . "');
            }
        } ";

        $CLUB_MANAGEMENT = "{
            text: '" . CLUB_MANAGEMENT . "'
            ,iconCls:'icon-user_home'
            ,handler: function(){
                addTab('CLUB_MANAGEMENT','" . ACADEMIC_MODUL . " &raquo; " . CLUB_MANAGEMENT . "','" . $this->UTILES->buildURL('club', array()) . "');
            }
        } ";

        //
        $TRAINING_PROGRAMS = "{
            text: '" . TRAINING_PROGRAMS . "'
            ,iconCls:'icon-bricks'
            ,menu:[{
                text: '" . SETTING . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('TRAINING_PROGRAMS_SETTING','" . TRAINING_PROGRAMS . " (" . SETTING . ") ','/training/');
                }
            },{
                text: '" . STUDENT_IMPORT_FROM_EXCEL_FILE . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('TES_STUDENT_IMPORT_FROM_EXCEL_FILE','" . TRAINING_PROGRAMS . " (" . STUDENT_IMPORT_FROM_EXCEL_FILE . ") ','/student/importxls/?target=training');
                }
            },{
                text: '" . SCORES_MANAGEMENT . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('SCORES_MANAGEMENT_STUDENT_IMPORT_FROM_EXCEL_FILE','" . TRAINING_PROGRAMS . " (" . SCORES_MANAGEMENT . ") ','/training/scoremanagement/');
                }
            }]
        } ";

        $SCHOOL_DOCUMENTATION = "{
            text: '" . SCHOOL_DOCUMENTATION . "'
            ,iconCls:'icon-page_attach'
            ,handler: function(){
                addTab('SCHOOL_DOCUMENTATION','" . SCHOOL_DOCUMENTATION . "','" . $this->UTILES->buildURL('file', array()) . "');
            }
        } ";

        $BULLETIN_BOARD = "{
            text: '" . BULLETIN_BOARD . "'
            ,iconCls:'icon-bell_add'
            ,handler: function(){
                addTab('TRAINING_PROGRAMS','" . ACADEMIC_MODUL . " &raquo; " . BULLETIN_BOARD . "','" . $this->UTILES->buildURL('bulletin', array()) . "');
            }
        } ";

        $ACADEMIC_SETTING = "{
            text: '" . ACADEMIC_SETTING . "'
            ,iconCls:'icon-wrench_orange'
            ,handler:function(){
                addTab('ACADEMIC_SETTING','" . ACADEMIC_SETTING . "','/academicsetting/');
            }
        }
        ";

        $TRADITIONAL_EDUCATION_SYSTEM = "{
            text: '" . TRADITIONAL_EDUCATION_SYSTEM . "'
            ,iconCls:'icon-bricks'
            ,menu:[{
                text: '" . SETTING . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('TES_SETTING','" . TRADITIONAL_EDUCATION_SYSTEM . " (" . SETTING . ") ','/academic/');
                }
            },{
                text: '" . STUDENT_IMPORT_FROM_EXCEL_FILE . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('TES_STUDENT_IMPORT_FROM_EXCEL_FILE','" . TRADITIONAL_EDUCATION_SYSTEM . " (" . STUDENT_IMPORT_FROM_EXCEL_FILE . ") ','/student/importxls/?target=general');
                }
            },{
                text: '" . SCORES_MANAGEMENT . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('TES_SCORES_MANAGEMENT','" . TRADITIONAL_EDUCATION_SYSTEM . " (" . SCORES_MANAGEMENT . ") ','/academic/scoremanagement/');
                }
            },{
                text: '" . REPORT_AND_STATISTIC . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('TES_STUDENT_IMPORT_FROM_EXCEL_FILE','" . TRADITIONAL_EDUCATION_SYSTEM . " (" . REPORT_AND_STATISTIC . ") ','/academic/traditionalsystemsearch/');
                }
            }]
        }";

        $CREDIT_EDUCATION_SYSTEM = "{
            text: '" . CREDIT_EDUCATION_SYSTEM . "'
            ,iconCls:'icon-bricks'
            ,menu:[{
                text: '" . SETTING . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('CES_SETTING','" . CREDIT_EDUCATION_SYSTEM . " (" . SETTING . ") ','/academic/creditsystemmain/');
                }
            },{
                text: '" . STUDENT_IMPORT_FROM_EXCEL_FILE . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('CES_STUDENT_IMPORT_FROM_EXCEL_FILE','" . CREDIT_EDUCATION_SYSTEM . " (" . STUDENT_IMPORT_FROM_EXCEL_FILE . ") ','/student/importxls/?target=general&educationSystem=1');
                }
            },{
                text: '" . SCORES_MANAGEMENT . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('CES_SCORES_MANAGEMENT','" . CREDIT_EDUCATION_SYSTEM . " (" . SCORES_MANAGEMENT . ") ','/academic/creditsystemscoremanagement/');
                }
            },{
                text: '" . REPORT_AND_STATISTIC . "'
                ,iconCls:'icon-brick_magnify'
                ,handler: function(){
                    addTab('CES_STUDENT_IMPORT_FROM_EXCEL_FILE','" . CREDIT_EDUCATION_SYSTEM . " (" . REPORT_AND_STATISTIC . ") ','/academic/creditsystemsearch/');
                }
            }]
        }";

        $CHOOSE_ACADEMIC_MODUL_ITEMS = array();

        if (UserAuth::displayTraditionalEducationSystem())
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = $TRADITIONAL_EDUCATION_SYSTEM;

        if (UserAuth::displayCreditEducationSystem())
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = $CREDIT_EDUCATION_SYSTEM;

        if (Zend_Registry::get('SCHOOL')->TRAINING_PROGRAMS) {
            if (UserAuth::getACLValue("ACADEMIC_TRAINING_PROGRAMS"))
                $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $TRAINING_PROGRAMS . "";
        }

        if (Zend_Registry::get('SCHOOL')->AVAILABLE_ELEARNING == 1) {
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $ELEARNING_MANAGEMENT . "";
        }

        if (UserAuth::displayRoleGeneralEducation()) {
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $CLASS_TRANSFER . "";
        }

        if (UserAuth::displayRoleGeneralEducation()) {
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $EXAMINATION_MANAGEMENT . "";
        }

        if (Zend_Registry::get('SCHOOL')->GENERAL_EDUCATION) {
            if (UserAuth::getACLValue("ACADEMIC_GENERAL_EDUCATION")) {
                $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $EXTRA_CLASS_MANAGEMENT . "";
            }
        }

        if (UserAuth::getACLValue("BULLETIN_BOARD"))
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $BULLETIN_BOARD . "";

        if (UserAuth::getACLValue("ACADEMIC_SETTING")) {

            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $SCHOOL_DOCUMENTATION . "";
            $CHOOSE_ACADEMIC_MODUL_ITEMS[] = "" . $ACADEMIC_SETTING . "";
        }

        $TBAR_ACADEMIC_MODUL_ITEMS = implode(",", $CHOOSE_ACADEMIC_MODUL_ITEMS);

        $js = "
        text: '" . ACADEMIC_MODUL . "'
        ,iconAlign: 'left'
        ,iconCls:'icon-bricks'
        ,menu: [" . $TBAR_ACADEMIC_MODUL_ITEMS . "]
        ";

        return $CHOOSE_ACADEMIC_MODUL_ITEMS ? $js : "";
    }

    private function administrationModul() {

        if (Zend_Registry::get('SCHOOL')->MULTI_BRANCH_OFFICE) {
            $DISABLED_BRANCH_OFFICE = "false";
        } else {
            $DISABLED_BRANCH_OFFICE = "true";
        }

        ////////////////////////////////////////////////////////////////////////
        //ADMINISTRATION
        ////////////////////////////////////////////////////////////////////////
        $SMS_MANAGEMENT = "{
            text: '" . SMS_MANAGEMENT . "'
            ,iconCls:'icon-phone'
            ,handler:function(){
                addTab('SMS_MANAGEMENT','" . ADMINISTRATION . " &raquo; " . SMS_MANAGEMENT . "','/academicsetting/smstabs/');
            }
        } ";

        $FINANCIAL_MANAGEMENT = "{
            text: '" . FINANCIAL_MANAGEMENT . "'
            ,iconCls:'icon-cashier'
            ,handler: function(){
                addTab('FINANCIAL_MANAGEMENT','" . ADMINISTRATION . " &raquo; " . FINANCIAL_MANAGEMENT . "','/academicsetting/financetabs/');
            }
        } ";

        $FACILITY_MANAGEMENT = "{
            text: '" . FACILITY_MANAGEMENT . "'
            ,iconCls:'icon-computer'
            ,handler: function(){
                addTab('FACILITY_MANAGEMENT','" . ADMINISTRATION . " &raquo; " . FACILITY_MANAGEMENT . "','" . $this->UTILES->buildURL('facility', array()) . "');
            }
        } ";

        $EVALUATION_MANAGEMENT = "{
            text: '" . EVALUATION_MANAGEMENT . "'
            ,iconCls:'icon-clipboard_add'
            ,handler: function(){
                addTab('EVALUATION_MANAGEMENT','" . EVALUATION_MANAGEMENT . "','/camemisevaluation/evaluationmanagementmain/?key=" . camemisId() . "');
            }
        } ";

        $LETTER_MANAGEMENT = "{
            text: '" . LETTER_MANAGEMENT . "'
            ,iconCls:'icon-certificate'
            ,handler: function(){
                addTab('LETTER_MANAGEMENT','" . LETTER_MANAGEMENT . "','/letter/?key=" . camemisId() . "');
            }
        } ";

        $SYSTEM_USER = "{
            text: '" . SYSTEM_USER . "'
            ,iconCls:'icon-group_add'
            ,handler: function(){
                addTab('SYSTEM_USER','" . SYSTEM_USER . "','/academicsetting/systemusertabs/');
            }
        } ";

        $SCHOOL_SETTINGS = "{
            text: '" . SCHOOL_SETTINGS . "'
            ,iconCls:'icon-wrench_orange'
            ,handler: function(){
                addTab('SCHOOL_SETTINGS','" . ADMINISTRATION . " &raquo; " . SCHOOL_SETTINGS . "','/academicsetting/schoolsettingtabs/');
            }
        } ";

        $CHOOSE_ADMINISTRATION_ITEMS = array();
        if (UserAuth::getACLValue("SMS_MANAGEMENT"))
            $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $SMS_MANAGEMENT . "";

        if (UserAuth::getACLValue("CASH_MANAGEMENT"))
            $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $FINANCIAL_MANAGEMENT . "";

        if (UserAuth::getACLValue("FACILITY_MANAGEMENT"))
            $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $FACILITY_MANAGEMENT . "";
        
        //if (UserAuth::getACLValue("EVALUATION_MANAGEMENT"))
        $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $EVALUATION_MANAGEMENT . "";
        
        if (UserAuth::getACLValue("LETTER_MANAGEMENT"))
            $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $LETTER_MANAGEMENT . "";

        if (UserAuth::getACLValue("SYSTEM_USER"))
            $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $SYSTEM_USER . "";

        if (UserAuth::getACLValue("SCHOOL_SETTING"))
            $CHOOSE_ADMINISTRATION_ITEMS[] = "" . $SCHOOL_SETTINGS . "";

        $TBAR_ADMINISTRATION_ITEMS = implode(",", $CHOOSE_ADMINISTRATION_ITEMS);

        $js = "
        text: '" . ADMINISTRATION . "'
        ,iconAlign: 'left'
        ,iconCls:'icon-bricks'
        ,menu: [" . $TBAR_ADMINISTRATION_ITEMS . "]
        ";

        return $CHOOSE_ADMINISTRATION_ITEMS ? $js : "";
    }

    private function setToolbar() {

        $CHOOSE_MODUL = array();

        if (UserAuth::getACLValue("STUDENT_MODUL"))
            $CHOOSE_MODUL[] = "'-',{" . $this->studentModul() . "}";
        if (UserAuth::getACLValue("STAFF_MODUL"))
            $CHOOSE_MODUL[] = "'-',{" . $this->staffModul() . "}";
        if (UserAuth::getACLValue("ACADEMIC_MODUL"))
            $CHOOSE_MODUL[] = "'-',{" . $this->academicModul() . "}";
        if (UserAuth::getACLValue("ADMINISTRATION_MODUL"))
            $CHOOSE_MODUL[] = "'-',{" . $this->administrationModul() . "}";

        $CHOOSE_TOOLBAR_ITEMS = implode(",", $CHOOSE_MODUL);

        $js = $CHOOSE_TOOLBAR_ITEMS;

        $SCHOOL_NAME = Zend_Registry::get('SCHOOL')->NAME ? Zend_Registry::get('SCHOOL')->NAME : "";
        $SCHOOL_URL = Zend_Registry::get('SCHOOL')->WEBSITE ? Zend_Registry::get('SCHOOL')->WEBSITE : "";

        if ($CHOOSE_MODUL) {
            $js .= ",'->',{
                text: '" . $SCHOOL_NAME . "'
                ,iconCls: 'icon-application_home'
                ,handler:function(){window.open('" . $SCHOOL_URL . "');}
            },'-',{
                text: '" . LOGOUT . "'
                ,iconAlign: 'left'
                ,iconCls: 'icon-door_out'
                ,tooltip: '" . LOGOUT . "...'
                ,handler: function(){
                    Ext.MessageBox.confirm('" . CONFIRM . "', '" . MSG_LOGOFF . "', showResult);
                }
            }";
        } else {
            $js .= "'->',{
                text: '" . $SCHOOL_NAME . "'
                ,iconCls: 'icon-application_home'
                ,handler:function(){window.open('" . $SCHOOL_URL . "');}
            },'-',{
                text: '" . LOGOUT . "'
                ,iconAlign: 'left'
                ,iconCls: 'icon-door_out'
                ,tooltip: '" . LOGOUT . "...'
                ,handler: function(){
                    Ext.MessageBox.confirm('" . CONFIRM . "', '" . MSG_LOGOFF . "', showResult);
                }
            }";
        }

        return $js;
    }

    private function userToolBar() {

        $SCHOOL_NAME = Zend_Registry::get('SCHOOL')->NAME ? Zend_Registry::get('SCHOOL')->NAME : "";
        $SCHOOL_URL = Zend_Registry::get('SCHOOL')->WEBSITE ? Zend_Registry::get('SCHOOL')->WEBSITE : "";

        switch (UserAuth::getUserType()) {
            case "GUARDIAN":
            case "STUDENT":
                $js = "'->',{
                    text: '" . $SCHOOL_NAME . "'
                    ,iconCls: 'icon-application_home'
                    ,handler:function(){window.open('" . $SCHOOL_URL . "');}
                },'-',{
                    text: '" . LOGOUT . "'
                    ,iconAlign: 'left'
                    ,iconCls: 'icon-door_out'
                    ,tooltip: '" . LOGOUT . "...'
                    ,handler: function(){
                        Ext.MessageBox.confirm('" . CONFIRM . "', '" . MSG_LOGOFF . "', showResult);
                    }
                }
                ";
                break;
            default:
                $js = $this->setToolbar();
                break;
        }

        return $js;
    }

    private function setMainIndexApplication() {
        $this->view->TOOLBAR = $this->userToolBar();
    }

    ////////////////////////////////////////////////////////////////////////////
    //Model Dashborad....
    ////////////////////////////////////////////////////////////////////////////
    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    protected static function setPositionDashboardItem($newposition, $const) {

        $currentObject = self::getObjectByConst($const);
        $nextObject = self::getNextObject($newposition);
        if ($currentObject && $nextObject) {
            self::dbAccess()->query("UPDATE t_user_dashboard SET POSITION='" . $currentObject->POSITION . "' WHERE ID='" . $nextObject->ID . "'");
            self::dbAccess()->query("UPDATE t_user_dashboard SET POSITION='" . $newposition . "' WHERE ID='" . $currentObject->ID . "'");
        }

        return array("success" => true);
    }

    protected static function actionRemovePanel($Id) {
        self::dbAccess()->query("DELETE FROM t_user_dashboard WHERE CONST='" . $Id . "' AND USER_ID='" . Zend_Registry::get('USER')->ID . "'");
        return array("success" => true);
    }

    protected static function getObjectByConst($const) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_dashboard", array("*"));
        $SQL->where("CONST = '" . $const . "'");
        $SQL->where("USER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function getNextObject($position) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_dashboard", array("*"));
        $SQL->where("POSITION = '" . $position . "'");
        $SQL->where("USER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function jsonTreeUnassignedDashboardItems() {

        $entries = Utiles::getDashboardItems();

        $USED_DATA = array();
        if (Utiles::getUserDashboardItems()) {
            foreach (Utiles::getUserDashboardItems() as $value) {
                $USED_DATA[] = $value->CONST;
            }
        }

        $data = array();
        if ($entries) {
            $i = 0;
            foreach ($entries as $key => $value) {
                if (!in_array($key, $USED_DATA)) {
                    $text = defined(trim($key)) ? constant(trim($key)) : trim($key);
                    $data[$i]['id'] = "" . $key . "";
                    $data[$i]['text'] = $text;
                    $data[$i]['iconCls'] = "icon-chart_bar_link";
                    $data[$i]['cls'] = "nodeText";
                    $data[$i]['checked'] = false;
                    $data[$i]['leaf'] = true;
                    $i++;
                }
            }
        }

        return $data;
    }

    protected static function actionAddUserDashboardItem($Id) {

        switch (UserAuth::getUserType()) {
            case "SUPERADMIN":
                $userType = "SUPERADMIN";
                break;
            case "ADMIN":
                $userType = "ADMIN";
                break;
            case "STUDENT":
                $userType = "STUDENT";
                break;
            case "TEACHER":
                $userType = "TEACHER";
                break;
            case "INSTRUCTOR":
                $userType = "INSTRUCTOR";
                break;
        }

        $SQL = "SELECT * FROM t_user_dashboard ORDER BY POSITION DESC LIMIT 0 ,1";
        $facette = self::dbAccess()->fetchRow($SQL);

        if ($facette) {
            $data = Utiles::getDashboardItems();
            if (isset($data[$Id])) {
                $SAVEDATA["USER_ID"] = Zend_Registry::get('USER')->ID;
                $SAVEDATA["POSITION"] = $facette->POSITION + 1;
                $SAVEDATA["CONST"] = $Id;
                $SAVEDATA["URL"] = $data[$Id];
                $SAVEDATA["USER_TYPE"] = $userType;
                self::dbAccess()->insert('t_user_dashboard', $SAVEDATA);
            }
        }

        return array("success" => true);
    }

    ////////////////////////////////////////////////////////////////////////////
    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "setPositionDashboardItem":
                $jsondata = self::setPositionDashboardItem($this->REQUEST->getPost('newposition'), $this->REQUEST->getPost('clickId'));
                break;
            case "actionRemovePanel":
                $jsondata = self::actionRemovePanel($this->REQUEST->getPost('panelId'));
                break;
            case "actionAddUserDashboardItem":
                $jsondata = self::actionAddUserDashboardItem($this->REQUEST->getPost('panelId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeUnassignedDashboardItems":
                $jsondata = self::jsonTreeUnassignedDashboardItems();
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