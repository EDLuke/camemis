<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.12.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/student/StudentSearchDBAccess.php';
require_once 'models/app_university/student/StudentImportDBAccess.php';
require_once 'models/app_university/student/StudentEnrollmentDBAccess.php';
require_once 'models/app_university/student/StudentAcademicDBAccess.php';
require_once 'models/app_university/student/StudentPreRequisiteCourseDBAccess.php'; //@veasna
require_once 'models/app_university/student/StudentStatusDBAccess.php';
require_once 'models/app_university/finance/StudentFeeDBAccess.php';
require_once 'models/app_university/DescriptionDBAccess.php';
require_once 'models/app_university/ScholarshipDBAccess.php';
require_once 'models/UserAuth.php';

class StudentController extends Zend_Controller_Action {

    public function init()
    {
        if (!UserAuth::mainidentify())
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

        $this->DB_STUDENT = StudentDBAccess::getInstance();
        $this->DB_STUDENT_IMPORT = StudentImportDBAccess::getInstance();
        $this->DB_STUDENT_ENROLLMENT = StudentEnrollmentDBAccess::getInstance();
        $this->DB_STUDENT_SEARCH = new StudentSearchDBAccess();
        $this->DB_STUDENT_ACADEMIC = StudentAcademicDBAccess::getInstance();

        $this->studentObject = null;
        $this->objectId = null;
        $this->studentId = null;
        $this->subjectId = null;
        $this->gradeId = null;
        $this->academicId = null;
        $this->target = null;
        $this->schoolyearId = null;
        $this->trainingId = null;
        $this->studentstatusType = null;
        $this->facette = null; //@Math Man
        $this->campusId = null;
        $this->noSudentComboLsit = null;

        $this->objectData = array();
        $this->OBJECT = null;
        if ($this->_getParam('objectId'))
        {

            $this->objectId = $this->_getParam('objectId');
            if (!StudentDBAccess::findStudentFromId($this->objectId))
            {
                $this->_request->setControllerName('error');
            }
            $this->objectData = $this->DB_STUDENT->getStudentDataFromId($this->objectId);
            $this->studentObject = StudentDBAccess::findStudentFromId($this->objectId);
            $this->OBJECT = StudentStatusDBAccess::findIdStudentStatus($this->objectId);
        }

        if ($this->_getParam('noSudentComboLsit'))
        {
            $this->noSudentComboLsit = $this->_getParam('noSudentComboLsit');
        }

        if ($this->_getParam('studentId'))
        {
            $this->studentId = $this->_getParam('studentId');
        }

        if ($this->_getParam('subjectId'))
        {
            $this->subjectId = $this->_getParam('subjectId');
        }

        if ($this->_getParam('schoolyearId'))
        {
            $this->schoolyearId = $this->_getParam('schoolyearId');
        }

        if ($this->_getParam('gradeId'))
        {
            $this->gradeId = $this->_getParam('gradeId');
        }

        if ($this->_getParam('academicId'))
        {
            $this->academicId = $this->_getParam('academicId');
        }

        if ($this->_getParam('target'))
        {
            $this->target = $this->_getParam('target');
        }

        if ($this->_getParam('trainingId'))
        {
            $this->trainingId = $this->_getParam('trainingId');
        }

        if ($this->_getParam('studentstatusType'))
        {
            $this->studentstatusType = $this->_getParam('studentstatusType');
        }

        if ($this->_getParam('campusId'))
        {
            $this->campusId = $this->_getParam('campusId');
        }

        $this->facette = StudentImportDBAccess::checkAllImportInTemp(); //@Math Man
    }

    public function indexAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
    }

    public function searchAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->URL_SEARCH_RESULT = $this->UTILES->buildURL('student/searchresult', array());
    }

    public function searchchartreportAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->_helper->viewRenderer("chartreport");
    }

    public function changepasswordAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/password");
    }

    public function classtransferAction()
    {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->academicId = $this->academicId;

        $this->_helper->viewRenderer("person/classtransfer");
    }

    public function settingstudentstatusAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_STATUS");
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->studentObject = $this->studentObject;
        $this->view->studentstatusType = $this->studentstatusType;
        $this->_helper->viewRenderer("person/settingstudentstatus");
    }

    public function studentlongtextAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;

        $this->_helper->viewRenderer("person/longtext");
    }

    public function deletestudentAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
    }

    public function studentacademictraditionalAction()
    {

        $this->view->objectId = $this->objectId;
        $this->view->academicId = $this->academicId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->_helper->viewRenderer("person/traditionalsystem/studentacademic");
    }

    ////////////////////////////////////////////////////////////////////////////
    public function medicalinfoAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/medicalinfo");
    }

    public function studentgeneraleducationAction()
    {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/generaleducation");
    }

    public function studenttrainingprogramsAction()
    {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/trainingprograms");
    }

    public function parentinfoAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/parentinfo");
    }

    public function prerequirementsAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/prerequirements");
    }

    public function descriptionAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/description");
    }

    public function studentmonitorAction()
    {

        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION_READ_RIGHT");

        $this->view->target = $this->target;
        $this->view->academicId = $this->academicId;
        $this->view->objectId = $this->objectId;
        $this->view->studentstatusType = $this->studentstatusType;
        $this->view->noSudentComboLsit = $this->noSudentComboLsit;
        $this->_helper->viewRenderer("person/monitor");
    }

    public function statusbystudentAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_STATUS");
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->studentObject = $this->studentObject;
        $this->_helper->viewRenderer("person/statusbystudent");
    }

    public function studentstatusAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_STATUS");
        $this->view->facette = $this->OBJECT;
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->studentObject = $this->studentObject;
        $this->_helper->viewRenderer("person/showstatus");
    }

    public function showitemAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION");
        $this->_redirect("/student/student/?objectId=" . $this->objectId . "");
    }

    public function personinfosAction()
    {

        $this->view->objectId = $this->objectId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("person/personinfos");
    }

    public function studentAction()
    {

        UserAuth::actionPermint($this->_request, "STUDENT_PERSONAL_INFORMATION_READ_RIGHT");

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->studentObject = $this->studentObject;
        $this->view->academicId = $this->academicId;
        $this->view->studentstatusType = $this->studentstatusType;

        if ($this->studentObject->CHANGE_PASSWORD == 0)
        {
            $this->view->HIDDEN_MSG_CHANGE_PASSWORD = "false";
        }
        else
        {
            $this->view->HIDDEN_MSG_CHANGE_PASSWORD = "true";
        }

        $this->view->status = isset($this->objectData["STATUS"]) ? $this->objectData["STATUS"] : 0;
        $this->view->status_password_icon = $this->objectData["IS_PASSWORD"] ? "icon-accept" : "icon-error";

        $this->_helper->viewRenderer("person/person");
    }

    public function statusAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_STATUS");
        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->studentObject = $this->studentObject;

        $this->_helper->viewRenderer("person/status");
    }

    public function liststudentstatusAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_STATUS");
    }

    public function importxlsAction()
    {

        $this->view->URL_TEMPLATE_XLS = $this->UTILES->buildURL('student/templatexls', array("target" => $this->target));
        $this->view->URL_STUDENT_IMPORT = $this->UTILES->buildURL('student/importxls', array("target" => $this->target));
        $this->view->facette = $this->facette; //@Math Man

        switch (strtoupper($this->target))
        {
            case "GENERAL":
                $this->_helper->viewRenderer("import/general/importxls");
                break;
            case "TRAINING":
                $this->_helper->viewRenderer("import/training/importxls");
                break;
        }
    }

    public function templatexlsAction()
    {
        $this->view->target = $this->target;
        switch (strtoupper($this->target))
        {
            case "GENERAL":
                $this->_helper->viewRenderer("import/general/templatexls");
                break;
            case "TRAINING":
                $this->_helper->viewRenderer("import/training/templatexls");
                break;
        }
    }

    public function currentgradeAction()
    {

        $this->view->objectId = $this->objectId;
        $this->view->academicId = $this->academicId;
        $this->view->URL_STUDENT_CHART = $this->UTILES->buildURL('student/chart', array());

        $this->DB_CLASS = AcademicDBAccess::getInstance();
        $classObject = AcademicDBAccess::findGradeFromId($this->academicId);
        $this->view->term_number = $classObject->TERM_NUMBER;
    }

    public function detailsassessmentAction()
    {

        $this->DB_CLASS = AcademicDBAccess::getInstance();
        $classObject = AcademicDBAccess::findGradeFromId($this->academicId);

        $this->view->objectId = $this->objectId;
        $this->view->term_number = $classObject->TERM_NUMBER;
        $this->view->academicId = $this->academicId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectData = $this->objectData;
    }

    public function enrollmentrecordAction()
    {

        $this->view->objectId = $this->objectId;
    }

    public function registrationAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_REGISTRATION_WIZARD");
        $this->view->gradeId = $this->gradeId;

        $this->db_grade = AcademicDBAccess::getInstance();
        $gradeObject = $this->db_grade->getGradeDataFromId($this->gradeId);

        if ($gradeObject)
        {

            $this->view->gradeObject = $gradeObject;
        }
        else
        {
            $this->view->gradeObject = null;
        }

        $this->view->URL_STUDENT_REGISTRATION = $this->UTILES->buildURL(
                'student/registration', array(
            "gradeId" => $this->gradeId
                )
        );
    }

    public function searchstudentscholarshipAction()
    {
        UserAuth::actionPermint($this->_request, "SCHOLARSHIP");
        $this->view->URL_SEARCH_STUDENT_SCHOLARSHIP_RESULT = $this->UTILES->buildURL('student/searchstudentscholarshipresult', array());
    }

    public function studentscholarshipAction()
    {
        UserAuth::actionPermint($this->_request, "SCHOLARSHIP");
    }

    ////////////////////////////////////////////////////////////////////////////
    //Credit System
    public function creditstudentdashboardAction()
    {
        $this->view->objectId = $this->objectId;
        $this->view->academicId = $this->academicId;
        $this->view->campusId = $this->campusId;
        $this->_helper->viewRenderer("person/creditsystem/dashboard");
    }

    public function creditstudentscheduleAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/schedule");
    }

    public function creditstudentschooleventAction()
    {
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->URL_SCHOOL_EVENT = $this->UTILES->buildURL("schoolevent", array(
            'schoolyearId' => $this->schoolyearId
            , 'target' => true
                )
        );
        $this->_helper->viewRenderer("person/creditsystem/schoolevent");
    }

    public function creditstudentsubjectAction()
    {
        $this->view->objectId = $this->objectId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->_helper->viewRenderer("person/creditsystem/subject");
    }

    public function creditstudenthomeworkAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/homework");
    }

    public function creditstudentassessmentAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/assessment");
    }

    public function creditstudentattendanceAction()
    {
        $this->view->objectId = $this->objectId;
        $this->view->academicId = $this->academicId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->trainingId = $this->trainingId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("person/creditsystem/attendance");
    }

    public function creditstudentdisciplineAction()
    {
        $this->view->objectId = $this->objectId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("person/creditsystem/discipline");
    }

    public function creditstudentlistAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/studentlist");
    }

    public function creditteacherlistAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/teacherlist");
    }

    public function studentsubjectcreditmainAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/subjectmain");
    }

    public function studentcampuscreditmainAction()
    {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("person/creditsystem/campusmain");
    }

    ////////////////////////////////////////////////////////////////////////////

    public function traditionalstudentattendanceAction()
    {
        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("person/traditionalsystem/attendance");
    }

    public function trainingstudentattendanceAction()
    {
        UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->trainingId = $this->trainingId;
        $this->_helper->viewRenderer("person/trainingsystem/attendance");
    }

    public function jsonloadAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "loadObject":
                $jsondata = $this->DB_STUDENT->loadStudentFromId($this->REQUEST->getPost('objectId'));
                break;

            case "enrollStudentsSubjectTerm":
                $jsondata = $this->DB_STUDENT_SEARCH->enrollStudentsSubjectTerm($this->REQUEST->getPost());
                break;
            
            case "searchStudent":
            case "allStudentsByGrade":
                $jsondata = $this->DB_STUDENT_SEARCH->searchStudents($this->REQUEST->getPost());
                break;

            case "academicHistories":
                $jsondata = $this->DB_STUDENT_ENROLLMENT->academicHistories($this->REQUEST->getPost());
                break;

            case "jsonUnassignedStudents":
                $jsondata = $this->DB_STUDENT->jsonUnassignedStudents($this->REQUEST->getPost());
                break;

            case "jsonAssignedStudents":
                $jsondata = $this->DB_STUDENT->jsonAssignedStudents($this->REQUEST->getPost());
                break;

            case "jsonEnrolledStudentBySubject":
                $jsondata = StudentAcademicDBAccess::jsonEnrolledStudentBySubject($this->REQUEST->getPost());
                break;

            case "jsonUnenrolledStudentSubject":
                $jsondata = StudentAcademicDBAccess::jsonUnenrolledStudentSubject($this->REQUEST->getPost());
                break;

            case "jsonUnassignedStudentsByClass":
                $jsondata = $this->DB_STUDENT->jsonUnassignedStudentsByClass($this->REQUEST->getPost());
                break;

            case "jsonListStudentsByClass":
                $jsondata = StudentAcademicDBAccess::jsonListStudentsByClass($this->REQUEST->getPost());
                break;

            //@veasna
            case "jsonListStudentsByTeacherClass":
                $jsondata = StudentAcademicDBAccess::jsonListStudentsByTeacherClass($this->REQUEST->getPost());
                break;

            case "jsonListStudentsSubjectCreditStatus":
                $jsondata = StudentPreRequisiteCourseDBAccess::jsonListStudentsSubjectCreditStatus($this->REQUEST->getPost());
                break;

            case "jsonListStudentsByStudentClass":
                $jsondata = StudentAcademicDBAccess::jsonListStudentsByStudentClass($this->REQUEST->getPost());
                break;
            //

            case "loadActionStudentSchoolYear":
                $jsondata = $this->DB_STUDENT->loadActionStudentSchoolYear($this->REQUEST->getPost());
                break;

            case "importStudents":
                $jsondata = $this->DB_STUDENT_IMPORT->importStudents($this->REQUEST->getPost());
                break;

            case "jsonCheckStudentSchoolID":
                $jsondata = $this->DB_STUDENT->jsonCheckStudentSchoolID($this->REQUEST->getPost());
                break;

            case "enrolledStudentByNextYear":
                $jsondata = $this->DB_STUDENT->enrolledStudentByNextYear($this->REQUEST->getPost());
                break;

            case "jsonLoadStudentStatus":
                $jsondata = StudentStatusDBAccess::jsonLoadStudentStatus($this->REQUEST->getPost('statusId'));
                break;

            case "jsonListStudentStatus":
                $jsondata = StudentStatusDBAccess::jsonListStudentStatus($this->REQUEST->getPost());
                break;
            case "jsonSearchStudentStatus":
                $jsondata = StudentStatusDBAccess::jsonSearchStudentStatus($this->REQUEST->getPost());
                break;

            case "jsonListPersonInfos":
                $jsondata = StudentDBAccess::jsonListPersonInfos($this->REQUEST->getPost());
                break;

            case "jsonStudentMedical":
                $jsondata = StudentDBAccess::jsonStudentMedical($this->REQUEST->getPost());
                break;

            case "jsonStudentPrerequirements":
                $jsondata = StudentDBAccess::jsonStudentPrerequirements($this->REQUEST->getPost());
                break;

            case "loadStudentDescripton":
                $jsondata = $this->DB_STUDENT->loadStudentDescripton($this->REQUEST->getPost('objectId'));
                break;

            case "jsonStatusByStudent":
                $jsondata = StudentStatusDBAccess::jsonStatusByStudent($this->REQUEST->getPost());
                break;

            case "jsonListStudentScholarship":
                $jsondata = StudentAcademicDBAccess::jsonListStudentScholarship($this->REQUEST->getPost());
                break;

            case "jsonStudentAcademicTraditional":
                $jsondata = $this->DB_STUDENT_ACADEMIC->jsonStudentAcademicTraditional($this->REQUEST->getPost());
                break;

            case "jsonLoadLastStudentStatus":
                $jsondata = StudentStatusDBAccess::jsonLoadLastStudentStatus($this->REQUEST->getPost('objectId'));
                break;

            case "checkStudentEducationSystem":
                $jsondata = StudentDBAccess::checkStudentEducationSystem($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "registrationRecord":
                $jsondata = $this->DB_STUDENT_ENROLLMENT->registrationRecord($this->REQUEST->getPost());
                break;

            case "updateObject":
                $jsondata = $this->DB_STUDENT->updateStudent($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_STUDENT->releaseStudent($this->REQUEST->getPost());
                break;

            case "jsonAddEnrollStudentSchoolyear":
                $jsondata = $this->DB_STUDENT->jsonAddEnrollStudentSchoolyear($this->REQUEST->getPost());
                break;

            case "jsonAddEnrollStudentClass":
                $jsondata = $this->DB_STUDENT->jsonAddEnrollStudentClass($this->REQUEST->getPost());
                break;

            case "jsonRemoveEnrolledStudentSchoolyear":
                $jsondata = $this->DB_STUDENT->jsonRemoveEnrolledStudentSchoolyear($this->REQUEST->getPost());
                break;

            case "jsonRemoveEnrolledStudentClass":
                $jsondata = $this->DB_STUDENT->jsonRemoveEnrolledStudentClass($this->REQUEST->getPost());
                break;

            case "actionStudentSchoolYear":
                $jsondata = $this->DB_STUDENT->actionStudentSchoolYear($this->REQUEST->getPost());
                break;

            case "jsonRemoveStudentFromSchool":
                $jsondata = $this->DB_STUDENT->jsonRemoveStudentFromSchool($this->REQUEST->getPost());
                break;

            case "jsonAddStudentToStudentDB":
                $jsondata = $this->DB_STUDENT_IMPORT->jsonAddStudentToStudentDB($this->REQUEST->getPost());
                break;

            case "jsonAddTrainingToStudentDB":
                $jsondata = $this->DB_STUDENT_IMPORT->jsonAddTrainingToStudentDB($this->REQUEST->getPost());
                break;

            case "jsonRemoveStudentsFromImport":
                $jsondata = $this->DB_STUDENT_IMPORT->jsonRemoveStudentsFromImport($this->REQUEST->getPost());
                break;

            case "actionEnrolledStudentByNextYear":
                $jsondata = $this->DB_STUDENT->actionEnrolledStudentByNextYear($this->REQUEST->getPost());
                break;

            case "actionGeneratePassword":
                $jsondata = $this->DB_STUDENT->actionGeneratePassword($this->REQUEST->getPost());
                break;

            case "jsonActionChangeStudentImport":
                $jsondata = $this->DB_STUDENT_IMPORT->jsonActionChangeStudentImport($this->REQUEST->getPost());
                break;

            case "jsonActionStudentClassTransfer":
                $jsondata = $this->DB_STUDENT->jsonActionStudentClassTransfer($this->REQUEST->getPost());
                break;

            case "jsonActionStudentAcademicTraditional":
                $jsondata = StudentAcademicDBAccess::jsonActionStudentAcademicTraditional($this->REQUEST->getPost());
                break;

            case "actionRemoveSMSRegistration":
                $jsondata = $this->DB_STUDENT->actionRemoveSMSRegistration($this->REQUEST->getPost('objectId'));
                break;

            case "actionStudentSchoolYearSorting":
                $jsondata = $this->DB_STUDENT->actionStudentSchoolYearSorting($this->REQUEST->getPost());
                break;

            case "jsonActionSaveLastStudentStatus":
                $jsondata = StudentStatusDBAccess::jsonActionSaveLastStudentStatus($this->REQUEST->getPost());
                break;

            case "removeStudentStatus":
                $jsondata = StudentStatusDBAccess::removeStudentStatus($this->REQUEST->getPost('statusId'));
                break;

            case "actionStudentPrerequirements":
                $jsondata = StudentDBAccess::actionStudentPrerequirements($this->REQUEST->getPost());
                break;

            case "actionPersonInfos":
                $jsondata = StudentDBAccess::actionPersonInfos($this->REQUEST->getPost());
                break;

            case "actionStudentDescription":
                $jsondata = StudentDBAccess::actionStudentDescription($this->REQUEST->getPost());
                break;

            case "jsonAddEnrollStudentSubject":
                $jsondata = StudentAcademicDBAccess::jsonAddEnrollStudentSubject($this->REQUEST->getPost());
                break;

            case "jsonRemoveEnrolledStudentSubject":
                $jsondata = StudentAcademicDBAccess::jsonRemoveEnrolledStudentSubject($this->REQUEST->getPost());
                break;

            case "actionStudent2ClassSectionTraditional":
                $jsondata = StudentDBAccess::actionStudent2ClassSectionTraditional($this->REQUEST->getPost());
                break;

            case "jsonSetCurrentStudentAcademic":
                $jsondata = StudentDBAccess::setCurrentStudentAcademic(false, true);
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonimportAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {
            case "importXLS":
                $jsondata = $this->DB_STUDENT_IMPORT->importXLS($this->REQUEST->getPost());
                break;
            case "importStudentExamScoreXLS":
                $jsondata = $this->DB_STUDENT_IMPORT->importStudentExamScoreXLS($this->REQUEST->getPost());
                break;
        }

        Zend_Loader::loadClass('Zend_Json');

        if (isset($jsondata))
            $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        if (isset($json))
            $this->getResponse()->setBody($json);
    }

    public function jsontreeAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {
            case "academicHistoryTree":
                $jsondata = $this->DB_STUDENT_ENROLLMENT->academicHistoryTree($this->REQUEST->getPost());
                break;

            case "jsonTreeStudentsByClass":
                $jsondata = StudentAcademicDBAccess::jsonTreeStudentsByClass($this->REQUEST->getPost("Id"));
                break;

            case "jsonTreeAllStudentDescription":
                $jsondata = DescriptionDBAccess::jsonTreeAllStudentDescription($this->REQUEST->getPost());
                break;

            case "jsonTreeFeesByStudent":
                $jsondata = StudentFeeDBAccess::jsonTreeFeesByStudent($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function exportAction()
    {
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("export/index");
    }

    //////////////////////////////////////////////////////

    public function setJSON($jsondata)
    {

        Zend_Loader::loadClass('Zend_Json');

        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>