<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////scoremonitorAction
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/academic/AcademicLevelDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/subject/GradeSubjectDBAccess.php';
require_once 'models/app_university/AcademicAdditionalDBAccess.php'; //@Sea Peng
require_once 'models/app_university/student/StudentCreditInformationDBAccess.php'; //@Sor Veasna

class AcademicController extends Zend_Controller_Action {

    protected $OBJECT_DATA;
    protected $objectId;
    protected $roleAdmin = array("SYSTEM");

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

        $this->DB_GRADE = AcademicDBAccess::getInstance();
        $this->DB_ACADEMIC_LEVEL = AcademicLevelDBAccess::getInstance();
        $this->DB_SUBJECT = SubjectDBAccess::getInstance();

        $this->parentId = null;
        $this->facette = null;
        $this->objectId = null;
        $this->schoolyearId = null;
        $this->objectData = array();
        $this->campusId = null;
        $this->gradeId = null;
        $this->academicId = null;
        $this->subjectId = null;
        $this->teacherId = null;
        $this->sourceModul = null;
        $this->isCurrentYear = null;
        $this->studentSubjectId = null; //@veasna

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');

        if ($this->_getParam('parentId'))
            $this->parentId = $this->_getParam('parentId');

        if ($this->_getParam('gradeId'))
            $this->gradeId = $this->_getParam('gradeId');

        if ($this->_getParam('academicId'))
            $this->academicId = $this->_getParam('academicId');

        if ($this->_getParam('campusId'))
            $this->campusId = $this->_getParam('campusId');

        if ($this->_getParam('subjectId'))
            $this->subjectId = $this->_getParam('subjectId');

        if ($this->_getParam('teacherId'))
            $this->teacherId = $this->_getParam('teacherId');

        if ($this->_getParam('sourceModul'))
            $this->sourceModul = $this->_getParam('sourceModul');

        if ($this->_getParam('studentSubjectId')) //@veasna
            $this->studentSubjectId = $this->_getParam('studentSubjectId');

        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_GRADE->getGradeDataFromId($this->objectId);
            $this->facette = AcademicDBAccess::findGradeFromId($this->objectId);

            if ($this->facette) {

                switch ($this->facette->OBJECT_TYPE) {
                    case "CAMPUS":

                        $this->campusId = $this->facette->ID;
                        Zend_Registry::set('OBJECT_CAMPUS', AcademicDBAccess::findGradeFromId($this->objectId));

                        break;
                    case "GRADE":

                        $this->gradeId = $this->facette->ID;
                        $this->campusId = $this->facette->CAMPUS_ID;
                        Zend_Registry::set('OBJECT_GRADE', AcademicDBAccess::findGradeFromId($this->objectId));

                        break;
                    case "SCHOOLYEAR":

                        $this->gradeId = $this->facette->GRADE_ID;
                        $this->campusId = $this->facette->CAMPUS_ID;
                        $facette = AcademicDBAccess::findGradeFromId($this->objectId);
                        $this->schoolyearId = $facette->SCHOOL_YEAR;
                        $schoolyear = AcademicDateDBAccess::getInstance();
                        $this->isCurrentYear = $schoolyear->isCurrentSchoolyear($facette->SCHOOL_YEAR);
                        Zend_Registry::set('OBJECT_SCHOOLYEAR', AcademicDBAccess::findGradeFromId($this->objectId));

                        break;
                    case "CLASS":
                    case "SUBJECT":
                    case "SUBCLASS":

                        $this->academicId = $this->facette->ID;
                        $this->campusId = $this->facette->CAMPUS_ID;
                        $this->gradeId = $this->facette->GRADE_ID;
                        $this->objectData = $this->DB_GRADE->getGradeDataFromId($this->academicId);
                        //$this->isCurrentYear = isset($this->objectData["IS_CURRENT_YEAR"]) ? $this->objectData["IS_CURRENT_YEAR"] : false;
                        Zend_Registry::set('OBJECT_CLASS', AcademicDBAccess::findGradeFromId($this->objectId));

                        break;
                }
            }
        }
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");

        $this->view->URL_WINDOW_LOCATION = $this->UTILES->buildURL("academic", array());
        $this->view->URL_FOLDER = $this->UTILES->buildURL("academic/editfolder", array());
        $this->view->URL_EDIT_CAMPUS = $this->UTILES->buildURL("academic/editcampus", array());
        $this->view->URL_EDIT_GRADE = $this->UTILES->buildURL("academic/editgrade", array());
        $this->view->URL_EDIT_SCHOOLYEAR = $this->UTILES->buildURL("academic/editschoolyear", array());
        $this->view->URL_EDIT_CLASS = $this->UTILES->buildURL("academic/editclass", array());
    }

    //@veasna
    public function creditinformationdashbaordAction() {

        $this->_helper->viewRenderer("creditsystem/crediteinformation/dashbaord");
    }

    public function creditinformationAction() {

        $this->_helper->viewRenderer("creditsystem/crediteinformation/creditlist");
    }

    public function showcreditinformationAction() {
        $this->view->studentSubjectId = $this->studentSubjectId;
        $this->view->studentSubjectObject = StudentCreditInformationDBAccess::getStudentSchoolYearSubjectById($this->studentSubjectId);
        $this->_helper->viewRenderer("creditsystem/crediteinformation/showcreditinformation");
    }

    public function schoolyearscheduleAction() {
        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $facette = AcademicDBAccess::findGradeFromId($this->academicId);
        if ($facette->EDUCATION_SYSTEM) {
            $this->_helper->viewRenderer("creditsystem/schedule");
        } else {
            $this->_helper->viewRenderer("traditionalsystem/schedule");
        }
    }

    public function creditassessmenttabsAction() {
        $this->_helper->viewRenderer("creditsystem/creditassessmenttabs");
    }

    public function addparentfolderAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
    }

    public function addcampusAction() {
        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
    }

    public function addgradeAction() {
        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
    }

    public function addclassAction() {
        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = AcademicDBAccess::findGradeFromId($this->parentId);
        $this->view->parentId = $this->parentId;
    }

    public function addsubjectAction() {
        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = AcademicDBAccess::findGradeFromId($this->parentId);
        $this->view->parentId = $this->parentId;
    }

    public function addgroupAction() {
        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = AcademicDBAccess::findGradeFromId($this->parentId);
        $this->view->parentId = $this->parentId;
    }

    public function addschoolyearAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->parentId = $this->parentId;
        Zend_Registry::set('TERM_NUMBER', $this->_getParam('term_number'));
    }

    public function listsubjectsAction() {

        $this->view->objectId = $this->objectId;
    }

    public function editcampusAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->view->status = isset($this->facette) ? $this->facette->STATUS : 0;
        $this->view->URL_GRADE_REPORTING = $this->UTILES->buildURL("reporting", array("campusId" => $this->objectId, "target" => "CAMPUS"));
        $this->view->URL_CAMPUS_SCHEDULE = $this->UTILES->buildURL("schedule/campusschedule", array("academicId" => $this->objectId));
    }

    public function editgradeAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->facette = $this->facette;
        $this->view->status = isset($this->facette) ? $this->facette->STATUS : 0;
        $this->view->URL_PERIOD_SCHEDULE = $this->UTILES->buildURL(
                'period'
                , array(
            "gradeId" => $this->objectId,
            "sourceModul" => 'GRADE'
        ));

        $this->view->URL_CAMPUS_SCHEDULE = $this->UTILES->buildURL("schedule/campusschedule", array("academicId" => $this->objectId));
    }

    public function evaluationformulaAction() {
        $this->view->objectId = $this->objectId;
    }

    public function datesettingAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("default/datesetting");
    }

    public function editschoolyearAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->facette = $this->facette;

        $this->_helper->viewRenderer("default/schoolyear");
    }

    public function editstageAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->facette = $this->facette;
    }

    public function enrollmentbyyearAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = $this->facette;
        $this->view->URL_STUDENT_REGISTRATION = $this->UTILES->buildURL('student/registration', array());
        $this->view->URL_SHOWITEM_STUDENT = $this->UTILES->buildURL("student/showitem", array());

        $this->view->classesComboData = $this->DB_GRADE->classesComboData($this->objectId);
    }

    public function enrollmentbysubjectAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = $this->facette;
    }

    public function enrollmentbysubjectgroupAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = $this->facette;
    }

    public function teacherscoreAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
    }

    public function studentexemptionsubjectAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
    }

    public function studentselectedsubjectAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
    }

    public function editclassAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = $this->facette;

        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
    }

    public function editsubjectAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = $this->facette;
        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
    }

    public function editgroupAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->facette = $this->facette;
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->status = isset($this->facette) ? $this->facette->STATUS : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function groupAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");

        $this->view->facette = $this->facette;

        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;
        $this->view->campusId = $this->campusId;
        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;

        $this->view->URL_SCHEDULE = $this->UTILES->buildURL(
                'schedule/byclass'
                , array(
            "academicId" => $this->academicId,
            "sourceModul" => 'GRADE'
                )
        );

        $this->view->URL_STUDENTS_CLASS = $this->UTILES->buildURL(
                "academic/studentsbyclass", array("objectId" => $this->objectId)
        );

        $this->view->URL_TEACHER_BY_CLASS = $this->UTILES->buildURL(
                'academic/teachersbyclass'
                , array(
            "schoolyearId" => $this->schoolyearId,
            "objectId" => $this->objectId
                )
        );

        $this->view->URL_CLASS_ATTENDANCE = $this->UTILES->buildURL("attendance/byclass", array("academicId" => $this->objectId));

        $this->view->URL_CLASS_DISCIPLINE = $this->UTILES->buildURL(
                "discipline/byclass"
                , array(
            "academicId" => $this->objectId
                )
        );

        $this->view->URL_ACADEMIC_PERFORMANCES = $this->UTILES->buildURL(
                "evaluation/classperformances"
                , array(
            "academicId" => $this->objectId
            , "schoolyearId" => $this->facette->SCHOOL_YEAR
                )
        );

        $this->view->URL_GRADE_REPORTING = $this->UTILES->buildURL(
                "reporting"
                , array(
            "gradeId" => $this->objectId,
            "target" => "CLASS"
                )
        );

        $this->view->URL_CLASS_SUBJECT = $this->UTILES->buildURL(
                'subject/byclass'
                , array(
            "academicId" => $this->objectId
                )
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    public function gradesubjectsAction() {

        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->view->teacherId = $this->teacherId;
        $this->view->facette = $this->facette;
        $this->view->objectId = $this->objectId;

        if ($this->facette)
            if ($this->facette->EDUCATION_SYSTEM) {
                GradeSubjectDBAccess:: setAutoSubject2GradeYearCreditSystem($this->objectId);
            }

        $this->view->URL_SHOW_SUBJECT_GRADE = $this->UTILES->buildURL(
                'subject/showgradesubject'
                , array()
        );

        $this->view->URL_ASSIGNMENT_SHOWITEM = $this->UTILES->buildURL(
                'assignment/showitem'
                , array()
        );
        $this->view->URL_ASSIGNMENT = $this->UTILES->buildURL('assignment', array());
    }

    public function additionalinformationAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->objectId = $this->objectId;
    }

    ///////////////////////////////////////////////////////
    // Students by Class
    ///////////////////////////////////////////////////////
    public function studentsbyclassAction() {

        $USER_TYPE = UserAuth::getUserType();
        switch (Zend_Registry::get('ADDITIONAL_ROLE')) {
            case 1:
                $USER_TYPE = "INSTRUCTOR";
                break;
            case 2:
                $USER_TYPE = "TEACHER";
                break;
        }

        switch ($USER_TYPE) {
            case "SUPERADMIN":
            case "ADMIN":
                $this->_redirect("/academic/studentlistadmin/?objectId=" . $this->objectId . "");
                break;
            case "TEACHER":
            case "INSTRUCTOR":
                $this->_redirect("/academic/studentlistteacher/?objectId=" . $this->objectId . "");
                break;
            case "STUDENT":
                $this->_redirect("/academic/studentlist/?objectId=" . $this->objectId . "");
                break;
        }
    }

    public function scoremanagementAction() {

        $this->_helper->viewRenderer("traditionalsystem/scoremanagement");
    }

    public function scoremonitorAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("traditionalsystem/scoremonitor");
    }

    public function studentlistadminAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->objectId = $this->objectId;

        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;
        $this->view->URL_SHOW_STUDENT = $this->UTILES->buildURL("student/showitem", array());
        $this->view->URL_EXPORT_ROSTER = $this->UTILES->buildURL(
                'student/exportroster'
                , array(
            'target' => "CLASS",
            'academicId' => $this->objectId,
            'schoolyearId' => $this->facette->SCHOOL_YEAR
                )
        );

        $this->view->URL_STUDENT_INTO_NEW_CLASS = $this->UTILES->buildURL(
                'academic/intonewclass'
                , array(
            'target' => "CLASS"
            , 'objectId' => $this->objectId
            , 'schoolyearId' => $this->facette->SCHOOL_YEAR
                )
        );

        $this->view->URL_STUDENT_CLASS_TRANSFER = $this->UTILES->buildURL(
                'student/classtransfer'
                , array(
            'academicId' => $this->facette->ID
            , 'gradeId' => $this->facette->GRADE_ID
            , 'schoolyearId' => $this->facette->SCHOOL_YEAR
                )
        );

        $this->view->SHOW_STUDENT = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'DETAIL')
        );

        $this->view->SHOW_PARENT_INFO = $this->UTILES->buildURL(
                'student/studentlongtext'
                , array('target' => 'PARENT_INFO')
        );

        $this->view->SHOW_MEDICAL_INFO = $this->UTILES->buildURL(
                'student/studentlongtext'
                , array('target' => 'MEDICAL_INFO')
        );

        $this->view->SHOW_BACKGROUND_INFO = $this->UTILES->buildURL(
                'student/studentlongtext'
                , array('target' => 'BACKGROUND_INFO')
        );

        $this->view->SHOW_CHANGE_PASSWORD = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'CHANGE_PASSWORD')
        );

        $this->view->SHOW_SMS_SERVICES = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'SMS_SERVICES')
        );

        $this->view->STUDENT_NOTES = $this->UTILES->buildURL("note"
                , array('academicId' => $this->objectId)
        );

        $this->view->STUDENT_TRANSFER_ASSESSMENT = $this->UTILES->buildURL("evaluation/scoretransfer"
                , array('academicId' => $this->objectId)
        );

        $this->view->REMOVE_FROM_CLASS = $this->UTILES->buildURL('student/deletestudent', array(
            "academicId" => $this->facette->ID,
            "schoolyearId" => $this->facette->SCHOOL_YEAR)
        );

        switch ($this->facette->EDUCATION_SYSTEM) {
            case 1:
                $this->view->URL_GRADEBOOK = $this->UTILES->buildURL('evaluation/gradebookcredit', array(
                    "academicId" => $this->facette->ID,
                    "gradeId" => $this->facette->GRADE_ID,
                    "schoolyearId" => $this->facette->SCHOOL_YEAR)
                );
                break;
            default:
                $this->view->URL_GRADEBOOK = $this->UTILES->buildURL('evaluation/gradebooktraditional', array(
                    "academicId" => $this->facette->ID,
                    "gradeId" => $this->facette->GRADE_ID,
                    "schoolyearId" => $this->facette->SCHOOL_YEAR)
                );
                break;
        }
    }

    public function studentlistteacherAction() {

        $this->view->facette = $this->facette;
        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;

        $this->view->URL_EXPORT_ROSTER = $this->UTILES->buildURL(
                'student/exportroster'
                , array('target' => "CLASS", 'academicId' => $this->objectId, 'schoolyearId' => $this->facette->SCHOOL_YEAR)
        );

        $this->view->SHOW_STUDENT = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'DETAIL')
        );

        $this->view->SHOW_PARENT_INFO = $this->UTILES->buildURL(
                'student/studentlongtext'
                , array('target' => 'PARENT_INFO', 'academicId' => $this->objectId)
        );

        $this->view->SHOW_MEDICAL_INFO = $this->UTILES->buildURL(
                'student/studentlongtext'
                , array('target' => 'MEDICAL_INFO', 'academicId' => $this->objectId)
        );

        $this->view->SHOW_BACKGROUND_INFO = $this->UTILES->buildURL(
                'student/studentlongtext'
                , array('target' => 'BACKGROUND_INFO', 'academicId' => $this->objectId)
        );

        $this->view->SHOW_CHANGE_PASSWORD = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'CHANGE_PASSWORD')
        );

        $this->view->SHOW_SMS_SERVICES = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'SMS_SERVICES')
        );

        $this->view->SHOW_SORTKEY = $this->UTILES->buildURL(
                'student/student'
                , array('target' => 'SORTKEY', 'academicId' => $this->objectId)
        );

        $this->view->URL_ADD_ABSENCE = $this->UTILES->buildURL("attendance/showitem"
                , array('academicId' => $this->objectId)
        );

        $this->view->URL_ADD_DISCIPLINE = $this->UTILES->buildURL("discipline/showitem"
                , array('academicId' => $this->objectId)
        );

        $this->view->STUDENT_TRANSFER_ASSESSMENT = $this->UTILES->buildURL("evaluation/scoretransfer"
                , array('academicId' => $this->objectId)
        );

        $this->view->STUDENT_NOTES = $this->UTILES->buildURL("note"
                , array('academicId' => $this->objectId)
        );

        $this->view->SMS_INDIVIDUAL = $this->UTILES->buildURL("sms/tostudentindividual"
                , array('academicId' => $this->objectId)
        );

        $this->view->SHOW_CLASS_REPRESENTATIVE = $this->UTILES->buildURL(
                'student/presentative'
                , array('academicId' => $this->objectId)
        );

        switch ($this->facette->EDUCATION_SYSTEM) {
            case 1:
                $this->view->URL_GRADEBOOK = $this->UTILES->buildURL('evaluation/gradebookcredit', array(
                    "academicId" => $this->facette->ID,
                    "gradeId" => $this->facette->GRADE_ID,
                    "schoolyearId" => $this->facette->SCHOOL_YEAR)
                );
                break;
            default:
                $this->view->URL_GRADEBOOK = $this->UTILES->buildURL('evaluation/gradebooktraditional', array(
                    "academicId" => $this->facette->ID,
                    "gradeId" => $this->facette->GRADE_ID,
                    "schoolyearId" => $this->facette->SCHOOL_YEAR)
                );
                break;
        }
    }

    public function studentlistAction() {

        if ($this->academicId) {
            $this->objectId = $this->academicId;
            $this->view->facette = AcademicDBAccess::findGradeFromId($this->objectId);
        } elseif ($this->objectId) {
            $this->view->objectId = $this->objectId;
            $this->view->facette = $this->facette;
        }

        $this->view->objectId = $this->objectId;
    }

    //////////////////////////////////////////////////////
    //@soda
    //////////////////////////////////////////////////////

    public function exportAction() {
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("export/index");
    }

    ///////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////
    // Teachers by Grade and School Year...
    ///////////////////////////////////////////////////////
    public function teachersbyyearAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");

        $this->view->objectId = $this->objectId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->gradeSubjects = $this->DB_ACADEMIC_LEVEL->subjectsByGradeArray();
        $this->view->EXPORT = $this->UTILES->buildURL('academic/exportteacherbyclass', array());
    }

    public function listsubjectAction() {
        $this->view->objectId = $this->objectId;
        $this->view->gradeId = $this->gradeId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->teacherId = $this->teacherId;
        $this->view->academicId = $this->academicId;
    }

    public function permissionscoreAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("traditionalsystem/permissionscore");
    }

    ///////////////////////////////////////////////////////
    // Teachers by Class
    ///////////////////////////////////////////////////////
    public function teachersbyclassAction() {

        $this->view->objectId = $this->objectId;
    }

    public function teacherclassmainAction() {
        $this->view->objectId = $this->objectId;
    }

    public function teacherchangeAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");

        $this->view->objectId = $this->objectId;
        $this->view->gradeId = $this->gradeId;
        $this->view->schoolyearId = $this->schoolyearId;
    }

    ///////////////////////////////////////////////////////
    // Teachers...
    ///////////////////////////////////////////////////////
    public function subjectteachersAction() {

        //UserAuth::actionPermint($this->_request, "GENERAL_EDUCATION");
        $this->view->isCurrentYear = $this->isCurrentYear;
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
    }

    public function traditionalsystemsearchAction() {
        $this->_helper->viewRenderer("traditionalsystem/search");
    }

    public function creditsystemmainAction() {
        $this->_helper->viewRenderer("creditsystem/index");
    }

    public function creditsystemsearchAction() {
        $this->_helper->viewRenderer("creditsystem/search");
    }

    public function creditsystemsettingAction() {
        $this->_helper->viewRenderer("creditsystem/setting");
    }

    public function creditsystemscoremanagementAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("creditsystem/scoremanagement");
    }

    public function creditenrolledstudentAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("creditsystem/enrolledstudent");
    }

    public function creditsubjectassignmentAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("creditsystem/subjectassignment");
    }

    public function creditassignedteacherAction() {
        $this->view->parentId = $this->parentId;
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("creditsystem/assignedteacher");
    }

    public function creditprerequisiteAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("creditsystem/prerequisite");
    }

    public function studentsubclassAction() {
        $this->view->objectId = $this->objectId;
        $this->view->facette = AcademicDBAccess::findGradeFromId($this->objectId);
        $this->_helper->viewRenderer("traditionalsystem/studentsubclass");
    }

    ////////////////////////////////////////////////////////////////////////////
    // STUDENT FILTER...
    ////////////////////////////////////////////////////////////////////////////
    public function studentfilterAction() {
        $this->_helper->viewRenderer("filter/studentfilterviewport");
        $this->view->gridType = "STUDENT_FILTER";
    }

    public function studentattendancefilterAction() {
        $this->_helper->viewRenderer("filter/studentfilterviewport");
        $this->view->gridType = "STUDENT_ATTENDANCE_FILTER";
    }

    public function studentdisciplinefilterAction() {
        $this->_helper->viewRenderer("filter/studentfilterviewport");
        $this->view->gridType = "STUDENT_DISCIPLINE_FILTER";
    }

    public function studentadvisoryfilterAction() {
        $this->_helper->viewRenderer("filter/studentfilterviewport");
        $this->view->gridType = "STUDENT_ADVISORY_FILTER";
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_GRADE->loadGradeFromId($this->REQUEST->getPost('objectId'));
                break;
            case "subjectsByGrade":
                $jsondata = $this->DB_ACADEMIC_LEVEL->subjectsByGrade($this->REQUEST->getPost());
                break;
            case "subjectsByClass":
                $jsondata = $this->DB_SUBJECT->jsonSubjectsByClass($this->REQUEST->getPost());
                break;
            case "actionSubjectTeacherClass":
                $jsondata = $this->DB_ACADEMIC_LEVEL->actionSubjectTeacherClass($this->REQUEST->getPost());
                break;
            case "jsonSubjectTeacherClass":
                $jsondata = $this->DB_ACADEMIC_LEVEL->jsonSubjectTeacherClass($this->REQUEST->getPost());
                break;
            case "gradesByCampus":
                $jsondata = $this->DB_ACADEMIC_LEVEL->gradesByCampus($this->REQUEST->getPost());
                break;
            case "jsonAssignedTeachers":
                $jsondata = $this->DB_ACADEMIC_LEVEL->jsonAssignedTeachers($this->REQUEST->getPost());
                break;
            case "jsonSearchGrade":
                $jsondata = $this->DB_GRADE->jsonSearchGrade($this->REQUEST->getPost());
                break;
            case "jsonEnrollmentType":
                $jsondata = $this->DB_GRADE->jsonEnrollmentType($this->REQUEST->getPost());
                break;
            case "jsonLoadScoreDeadLine":
                $jsondata = $this->DB_GRADE->jsonLoadScoreDeadLine($this->REQUEST->getPost());
                break;
            case "jsonCheckTeacherScoreEnter":
                $jsondata = $this->DB_GRADE->jsonCheckTeacherScoreEnter($this->REQUEST->getPost());
                break;
            case "jsonInstructorsByClass":
                $jsondata = AcademicDBAccess::jsonInstructorsByClass($this->REQUEST->getPost());
                break;
            case "jsonListSubClass":
                $jsondata = AcademicDBAccess::jsonListSubClass($this->REQUEST->getPost());
                break;
            //@veasna
            case "jsonListCreditStudentInformation":
                $jsondata = StudentCreditInformationDBAccess::jsonListCreditStudentInformation($this->REQUEST->getPost());
                break;
            case "jsonStudentCreditStatus":
                $jsondata = StudentCreditInformationDBAccess::jsonStudentCreditStatus($this->REQUEST->getPost('studentSubjectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "updateObject":
                $jsondata = $this->DB_GRADE->updateGrade($this->REQUEST->getPost());
                break;

            case "addFolder":
            case "addCampus":
            case "addGrade":
            case "addSchoolYear":
            case "addSubject":
            case "addGroup":
            case "addClass":
                $jsondata = $this->DB_ACADEMIC_LEVEL->addNode($this->REQUEST->getPost());
                break;
            case "addSchoolYear":
                $jsondata = $this->DB_ACADEMIC_LEVEL->addNode($this->REQUEST->getPost());
                break;

            case "setSubjectTeacherClass":
                $jsondata = $this->DB_ACADEMIC_LEVEL->setSubjectTeacherClass($this->REQUEST->getPost());
                break;

            case "actionScoreDuration":
                $jsondata = $this->DB_ACADEMIC_LEVEL->actionScoreDuration($this->REQUEST->getPost());
                break;

            case "jsonActionScoreModification":
                $jsondata = $this->DB_ACADEMIC_LEVEL->jsonActionScoreModification($this->REQUEST->getPost("objectId"), $this->REQUEST->getPost("type"));
                break;

            case "removenode":
                $jsondata = $this->DB_GRADE->removeNodeAndChildren($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_GRADE->releaseObject($this->REQUEST->getPost());
                break;

            case "jsonAddEnrollStudentSubject":
                $jsondata = StudentAcademicDBAccess::jsonAddEnrollStudentSubject($this->REQUEST->getPost());
                break;

            case "actionClassInstructor":
                $jsondata = AcademicDBAccess::actionClassInstructor($this->REQUEST->getPost());
                break;

            case "jsonActionExtraSemesterDate":
                $jsondata = AcademicDBAccess::jsonActionExtraSemesterDate($this->REQUEST->getPost());
                break;

            case "saveSchoolyearDateSetting":
                $jsondata = AcademicDBAccess::saveSchoolyearDateSetting($this->REQUEST->getPost());
                break;

            case "jsonAdditionalInformationToAcademic":
                $jsondata = AcademicAdditionalDBAccess::jsonAdditionalInformationToAcademic($this->REQUEST->getPost());
                break;

            case "actionStaffPermissionScore":
                $jsondata = AcademicDBAccess::actionStaffPermissionScore($this->REQUEST->getPost());
                break;

            case "addSubClass":
                $jsondata = AcademicDBAccess::addSubClass($this->REQUEST->getPost());
                break;

            case "deleteSubClass":
                $jsondata = AcademicDBAccess::deleteSubClass($this->REQUEST->getPost("removeId"));
                break;
            //@veasna
            case "changeCrditeStudentInformation":
                $jsondata = StudentCreditInformationDBAccess::changeCrditeStudentInformation($this->REQUEST->getPost());
                break;
            case "changeStudentSubjectCreditInfo":
                $jsondata = StudentCreditInformationDBAccess::changeStudentSubjectCreditInfo($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "getTreeTraditionalEducationSystem":
                $jsondata = $this->DB_ACADEMIC_LEVEL->getTreeTraditionalEducationSystem($this->REQUEST->getPost());
                break;

            case "getTreeCreditEducationSystem":
                $jsondata = $this->DB_ACADEMIC_LEVEL->getTreeCreditEducationSystem($this->REQUEST->getPost());
                break;

            case "teachersByGrade":
                $jsondata = $this->DB_ACADEMIC_LEVEL->teachersByGrade($this->REQUEST->getPost());
                break;

            case "subjectsByGrade":
                $jsondata = $this->DB_ACADEMIC_LEVEL->subjectsByGrade($this->REQUEST->getPost());
                break;

            case "jsonTreeAllAcademicGradeSchoolyear":
                $jsondata = AcademicLevelDBAccess::jsonTreeAllAcademicGradeSchoolyear($this->REQUEST->getPost());
                break;

            case "jsonTreeClassBySubject":
                $jsondata = AcademicLevelDBAccess::jsonTreeClassBySubject($this->REQUEST->getPost());
                break;

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