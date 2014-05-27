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
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/student/StudentAttendanceDBAccess.php';
require_once 'models/app_school/staff/StaffAttendanceDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/AbsentTypeDBAccess.php';
require_once 'models/filter/jsonFilterReport.php';

class AttendanceController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->DB_STUDENT_ATTENDANCE = StudentAttendanceDBAccess::getInstance();
        $this->DB_STAFF_ATTENDANCE = StaffAttendanceDBAccess::getInstance();

        $this->target = null;
        $this->object = null;
        $this->objectId = null;
        $this->classId = null;
        $this->trainingId = null;
        $this->studentId = null;
        $this->staffId = null;
        $this->subjectId = null;
        $this->facette = null;
        $this->actionType = null;
        $this->absentType = null;
        $this->startDate = null;
        $this->endDate = null;
        $this->choosedate = null;
        $this->schoolyearId = null;

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');

        if ($this->_getParam('startDate'))
            $this->startDate = $this->_getParam('startDate');

        if ($this->_getParam('endDate'))
            $this->endDate = $this->_getParam('endDate');

        if ($this->_getParam('choosedate'))
            $this->choosedate = $this->_getParam('choosedate');

        if ($this->_getParam('studentId'))
            $this->studentId = $this->_getParam('studentId');

        if ($this->_getParam('staffId'))
            $this->staffId = $this->_getParam('staffId');

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('trainingId'))
            $this->trainingId = $this->_getParam('trainingId');

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        if ($this->_getParam('object'))
            $this->objectId = $this->_getParam('object');

        if ($this->_getParam('actionType'))
            $this->actionType = $this->_getParam('actionType');

        if ($this->_getParam('absentType'))
            $this->absentType = $this->_getParam('absentType');
    }

    public function studentsearchmainAction() {
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("student/index");
    }

    public function settingAction() {
        
    }

    public function ofstudentsAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->URL_CREATE_ABSENCE = $this->UTILES->buildURL('attendance/addbystudent', array());
        $this->_helper->viewRenderer("student/index");
    }

    public function ofstaffsAction() {

        //UserAuth::actionPermint($this->_request, "STAFF_ATTENDANCE");
        $this->_helper->viewRenderer("staff/index");
    }

    public function ofstaffscurrentAction() {

        //UserAuth::actionPermint($this->_request, "STAFF_ATTENDANCE");
        $this->_helper->viewRenderer("staff/current");
    }

    public function showAction() {
        
    }

    /**
     * 05.06.2013
     * attendance/studentclassadmin (Student Login)
     */
    public function studentclassadminAction() {

        $this->view->target = $this->target;
        switch (strtoupper($this->target)) {
            case "GENERAL":
                $this->_helper->viewRenderer("student/admingeneral");
                break;
            case "CREDIT":
                $this->_helper->viewRenderer("student/creditsystem/admincredit");
                break;
            case "TRAINING":
                $this->_helper->viewRenderer("student/admintraining");
                break;
        }
    }

    /**
     * 05.06.2013
     * attendance/studentpersonmain (Student Login)
     */
    public function studentpersonmainAction() {

        $this->view->studentId = $this->studentId;
        $this->view->classId = $this->classId;
        $this->view->trainingId = $this->trainingId;
        $this->view->target = $this->target;

        $this->_helper->viewRenderer("student/person/main");
    }

    /**
     * 05.06.2013
     */
    public function studentclassmainAction() {

        $this->view->classId = $this->classId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("student/class/main");
    }

    /**
     * 05.06.2013
     */
    public function studentclassdailyAction() {
        $this->view->classId = $this->classId;
        $this->view->staffId = $this->staffId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("student/class/daily");
    }

    /**
     * 05.06.2013
     */
    public function studentclassblockAction() {
        $this->view->classId = $this->classId;
        $this->view->staffId = $this->staffId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("student/class/block");
    }

    //@THORN Visal    
    public function studentclassreportAction() {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->target = $this->target;
        $classObject = AcademicDBAccess::findGradeFromId($this->classId);
        $this->view->schoolyearId = $classObject->SCHOOL_YEAR;
        $this->_helper->viewRenderer("student/class/report");
    }

    //@Sea Peng
    public function studentdialydescriptionAction() {
        $this->view->objectId = $this->objectId;
        $this->view->choosedate = $this->choosedate;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("student/class/description");
    }

    /**
     * 05.06.2013
     */
    public function studentpersonblockAction() {

        $this->view->studentId = $this->studentId;
        $this->view->classId = $this->classId;
        $this->view->trainingId = $this->trainingId;
        $this->view->target = $this->target;

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('attendance/showbystudent', array());
        $this->view->URL_NEW_SHOWITEM = $this->UTILES->buildURL('attendance/showbystudent'
                , array(
            'objectId' => 'new'
            , 'studentId' => $this->studentId
            , 'target' => $this->target
            , 'classId' => $this->classId
            , 'trainingId' => $this->trainingId
                )
        );

        $this->_helper->viewRenderer("/student/person/block");
    }

    //for student credit system @veasna
    public function studentcreditsystemattendancetabsAction() {
        $this->view->target = $this->target;
        UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->_helper->viewRenderer("student/creditsystem/studentcreditsystemattendancetabs");
    }

    public function studentcreditblockAction() {

        $this->view->target = $this->target;
        $currenSchoolyearObject = AcademicDateDBAccess::loadCurrentSchoolyear();
        /* if($currenSchoolyearObject)
          $gradeSchoolyearObject = AcademicDBAccess::findCreditGradeSchoolyear($currenSchoolyearObject->ID); */

        $this->view->schoolyearId = $currenSchoolyearObject ? $currenSchoolyearObject->ID : '';

        $this->_helper->viewRenderer("student/creditsystem/block");
    }

    public function personcreditblockAction() {

        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('attendance/addattendancecreditstudent', array());
        $this->view->URL_NEW_SHOWITEM = $this->UTILES->buildURL('attendance/addattendancecreditstudent'
                , array(
            'objectId' => 'new'
            , 'studentId' => $this->studentId
            , 'schoolyearId' => $this->schoolyearId
                )
        );

        $this->_helper->viewRenderer("/student/creditsystem/personblock");
    }

    public function addattendancecreditstudentAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->facette = $this->DB_STUDENT_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;

        if ($this->facette) {
            $this->_helper->viewRenderer("student/creditsystem/show");
        } else {
            $this->_helper->viewRenderer("student/creditsystem/add");
        }
    }

    //

    public function exportexcelAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->target = $this->target;
    }

    public function showbystudentAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->classId = $this->classId;
        $this->view->trainingId = $this->trainingId;
        $this->view->studentId = $this->studentId;
        $this->view->target = $this->target;
        $this->facette = $this->DB_STUDENT_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;

        if ($this->facette) {
            $this->_helper->viewRenderer("student/show");
        } else {
            $this->_helper->viewRenderer("student/add");
        }
    }

    public function studentmoreeventsAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STUDENT_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("student/moreevents");
    }

    public function showitemsAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->target = $this->target;
        $this->view->actionType = $this->actionType;
        $this->_helper->viewRenderer("staff/showitems");
    }

    public function searchstaffabsenceAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("staff/search");
    }

    public function checktimebystaffAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->staffId = $this->staffId;
        $this->view->absentType = $this->absentType;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STAFF_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;

        $this->_helper->viewRenderer("staff/dailyattendancetabs");
    }

    public function settimeAction() {
        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->staffId = $this->staffId;
        $this->view->absentType = $this->absentType;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STAFF_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;

        $this->_helper->viewRenderer("staff/settime");
    }

    public function teachingsessionAction() {
        //UserAuth::actionPermint($this->_request, "STUDENT_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->staffId = $this->staffId;
        $this->view->absentType = $this->absentType;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STAFF_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;

        $this->_helper->viewRenderer("staff/teachingsession");
    }

    public function showbystaffAction() {

        //UserAuth::actionPermint($this->_request, "STAFF_ATTENDANCE");
        $this->view->actionType = $this->actionType;
        $this->view->objectId = $this->objectId;
        $this->view->staffId = $this->staffId;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STAFF_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;

        if ($this->objectId == "new") {
            $this->_helper->viewRenderer("staff/add");
        } else {

            $userObject = UserMemberDBAccess::findUserFromId($this->staffId);

            if ($userObject) {
                switch ($userObject->ROLE) {
                    case 2:
                    case 3:
                        $this->_helper->viewRenderer("staff/showwithschedule");
                        break;
                    default:
                        $this->_helper->viewRenderer("staff/showwithoutschedule");
                        break;
                }
            }
        }
    }

    public function staffchartreportAction() {

        $this->_helper->viewRenderer("staff/chartreport");
    }

    public function studentchartreportAction() {
        $this->view->studentId = $this->studentId;
        $this->view->classId = $this->classId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("student/chartreport");
    }

    public function staffbydailyAction() {
        $this->view->staffId = $this->staffId;
        $this->view->target = $this->target;
        $this->view->object = $this->object;
        $this->view->choosedate = $this->choosedate;
        $this->_helper->viewRenderer("staff/daily");
    }

    public function staffbyblockAction() {
        $this->view->staffId = $this->staffId;
        $this->view->target = $this->target;
        $this->_helper->viewRenderer("staff/block");
    }

    public function teachersingleeventsAction() {

        //UserAuth::actionPermint($this->_request, "STAFF_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->staffId = $this->staffId;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STAFF_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("staff/singleevents");
    }

    public function teachermoreeventsAction() {

        //UserAuth::actionPermint($this->_request, "STAFF_ATTENDANCE");
        $this->view->objectId = $this->objectId;
        $this->view->staffId = $this->staffId;
        $this->view->choosedate = $this->choosedate;
        $this->facette = $this->DB_STAFF_ATTENDANCE->findAttendanceFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("staff/moreevents");
    }

    public function bystaffAction() {

        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("staff/bystaff");
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            //////@THORN Visal
            case "jsonShowAllStudentAbsence":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonSearchStudentAttendance($this->REQUEST->getPost());
                break;
            //////////////////     
            case "jsonLoadStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonLoadStudentAttendance($this->REQUEST->getPost('objectId'));
                break;

            case "loadAttendanceChartBySingleClass":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->loadAttendanceChartBySingleClass($this->REQUEST->getPost());
                break;

            case "jsonSearchStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonSearchStudentAttendance($this->REQUEST->getPost());
                break;

            case "jsonLoadStaffAttendance":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonLoadStaffAttendance($this->REQUEST->getPost('objectId'));
                break;

            case "jsonSearchStaffAttendance":
                $jsondata = StaffAttendanceDBAccess::jsonSearchStaffAttendance($this->REQUEST->getPost());
                break;

            case "jsonStudentDayClassEventsGeneral":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonStudentDayClassEventsGeneral($this->REQUEST->getPost('objectId'), $this->REQUEST->getPost('choosedate'));
                break;

            case "jsonTeacherDayClassEvents":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonTeacherDayClassEvents($this->REQUEST->getPost());
                break;
            /**
             * 01.06.2013
             */
            case "jsonStudentDailyAttendance":
                $jsondata = StudentAttendanceDBAccess::jsonStudentDailyAttendance($this->REQUEST->getPost());
                break;
            /**
             * 02.06.2013
             */
            case "jsonStudentAttendanceBlock":
                $jsondata = StudentAttendanceDBAccess::jsonStudentAttendanceBlock($this->REQUEST->getPost());
                break;

            case "jsonStudentAttendanceMonth":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonStudentAttendanceMonth($this->REQUEST->getPost());
                break;
            /*
             * Sea Peng 09.08.2013
             */
            case "jsonStaffDailyAttendance":
                $jsondata = StaffAttendanceDBAccess::jsonStaffDailyAttendance($this->REQUEST->getPost());
                break;
            case "jsonStaffBlockAttendance":
                $jsondata = StaffAttendanceDBAccess::jsonStaffBlockAttendance($this->REQUEST->getPost());
                break;
            ////@veasna
            case "getStudentAttendanceData":
                $objectStudentAttendance = new jsonFilterReport();
                $jsondata = $objectStudentAttendance->getGridData($this->REQUEST->getPost());
                break;
            case "getTeacherAttendanceData":
                $objectTeacherAttendance = new jsonFilterReport();
                $jsondata = $objectTeacherAttendance->getGridData($this->REQUEST->getPost());
                break;
            ///
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllAbsentType":
                $jsondata = AbsentTypeDBAccess::jsonTreeAllAbsentType($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonActonStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonActonStudentAttendance($this->REQUEST->getPost());
                break;

            case "jsonActionStudentAttendanceSubject":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonActionStudentAttendanceSubject($this->REQUEST->getPost());
                break;

            case "jsonDeleteStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonDeleteStudentAttendance($this->REQUEST->getPost("objectId"));
                break;

            case "jsonReleaseStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonReleaseStudentAttendance($this->REQUEST->getPost());
                break;

            //@Sea Peng
            case "jsonUpdateStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonUpdateStudentAttendanceDescription($this->REQUEST->getPost());
                break;

            case "jsonActonStaffAttendance":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonActonStaffAttendance($this->REQUEST->getPost());
                break;

            case "jsonDeleteStaffAttendance":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonDeleteStaffAttendance($this->REQUEST->getPost('objectId'));
                break;

            case "jsonActionTeacherAttendanceSubject":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonActionTeacherAttendanceSubject($this->REQUEST->getPost());
                break;

            case "jsonReleaseStaffAttendance":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonReleaseStaffAttendance($this->REQUEST->getPost());
                break;

            case "jsonActionStudentDailyAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonActionStudentDailyAttendance($this->REQUEST->getPost());
                break;
            /*
             * Sea Peng 09.08.2013
             */
            case "jsonActionStaffDailyAttendance":
                $jsondata = $this->DB_STAFF_ATTENDANCE->jsonActionStaffDailyAttendance($this->REQUEST->getPost());
                break;

            ///@veasna
            case "jsonActonBlockCreditStudentAttendance":
                $jsondata = $this->DB_STUDENT_ATTENDANCE->jsonActonBlockCreditStudentAttendance($this->REQUEST->getPost());
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