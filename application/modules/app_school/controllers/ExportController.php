<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/UserAuth.php';
require_once 'models/export/StudentExportDBAccess.php';
require_once 'models/export/StaffExportDBAccess.php';
require_once 'models/export/ScheduleExportDBAccess.php';
require_once 'models/export/StudentAttendanceExportDBAccess.php';
require_once 'models/export/StudentPreschoolExportDBAccess.php';
require_once 'models/export/StudentDisciplineExportDBAccess.php';//@veasna
require_once 'models/export/StudentStatusExportDBAccess.php';//@Visal
require_once 'models/export/StudentAdvisoryExportDBAccess.php';//@Visal
require_once 'models/export/TraditionalFilterExportDBAccess.php';//@Visal
require_once 'models/export/RoomExportDBAccess.php';//@CHHE Vathana
require_once 'models/export/TrainingAssessmentExportDBAccess.php';//@CHHE Vathana
require_once 'models/export/StudentPersonalInfoExportDBAccess.php';
require_once 'models/export/StaffPersonalInfoExportDBAccess.php';

class ExportController extends Zend_Controller_Action {

    public function init()
    {

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->STUDENT_EXCEL = new StudentExportDBAccess($this->_getParam('objectId'));
        $this->STUDENT_PRESCHOOL_EXCEL = new StudentPreschoolExportDBAccess($this->_getParam('objectId'));//@Visal
        $this->STAFF_EXCEL = new StaffExportDBAccess($this->_getParam('objectId'));
        $this->STUDENT_ATTENDANCE_EXCEL = new StudentAttendanceExportDBAccess($this->_getParam('gridId'));
        $this->STUDENT_STATUS_EXCEL = new StudentStatusExportDBAccess($this->_getParam('objectId'));//@Visal
        $this->STUDENT_ADVISORY_EXCEL = new StudentAdvisoryExportDBAccess($this->_getParam('objectId'));//@Visal
        $this->STUDENT_DISCIPLINE_EXCEL = new StudentDisciplineExportDBAccess($this->_getParam('objectId'));//@veasna
        $this->TRADITION_FILTER_EXCEL = new TraditionalFilterExportDBAccess($this->_getParam('objectId'));//@Visal
        $this->ROOM_EXCEL = new RoomExportDBAccess($this->_getParam('objectId'));//@CHHE Vathana
        $this->STUDENT_TRAINING_ASSESSMENT_EXCEL = new TrainingAssessmentExportDBAccess($this->_getParam('objectId'));//@veasna
        $this->STUDENT_PERSONAL_EXCEL = new StudentPersonalInfoExportDBAccess($this->_getParam('objectId'));
        $this->STAFF_PERSONAL_EXCEL = new StaffPersonalInfoExportDBAccess($this->_getParam('objectId'));

        $this->SCHEDULE_EXCEL = new ScheduleExportDBAccess(
                $this->_getParam('academicId')
                , $this->_getParam('trainingId')
        );
    }

    //@veasna
    public function examshowcolumnAction()
    {

        $this->view->objectType = $this->_getParam('objectType') ? $this->_getParam('objectType') : '';
        $this->view->enrollExamType = $this->_getParam('enrollExamType') ? $this->_getParam('enrollExamType') : '';
        $this->view->campus = $this->_getParam('campus') ? $this->_getParam('campus') : '';
        $this->view->gender = $this->_getParam('gender') ? $this->_getParam('gender') : '';
        $this->view->examResult = $this->_getParam('examResult') ? $this->_getParam('examResult') : '';
        $this->view->type = $this->_getParam('type') ? $this->_getParam('type') : '';
        $this->view->bnt = $this->_getParam('bnt') ? $this->_getParam('bnt') : '';

        $this->_helper->viewRenderer("exam/showcolumn");
    }

    public function examAction()
    {

        switch ($this->_getParam('type'))
        {
            case "1":
                //SEMESTER_TEST

                break;
            case "2":
                //REPEAT_TEST

                break;
            case "3":
                //OLYMPIA_TEST

                break;
            case "4":
                //QUALITY_TEST

                break;
            case "5":
                //STATE EXAM

                break;
            case "6":
                //ENROLLMENT

                $this->_helper->viewRenderer("exam/exportenrollexam");
                break;
        }
    }

    public function openstudentlistAction()
    {
        
    }
    
    public function openstudentpreschoollistAction()
    {
        
    }

    public function openstafflistAction()
    {
        
    }

    public function opendayscheduleAction()
    {
        
    }

    public function openweekscheduleAction()
    {
        
    }

    public function openstudentattendancelistAction()
    {
        
    }

    public function openstaffattendancelistAction()
    {
        
    }
    //@veasna
    public function openstudentdisciplinelistAction()
    {
     
    }
    public function openstudentrainingassessmentlistAction()
    {
     
    }
    //@Visal
    public function openstudentstatuslistAction()
    {
         
    }
    
    public function openstudentadvisoryAction()
    {
         
    }
    //@CHHE Vathana
    public function openenrolledstudentyearlistAction()
    {
         
    }
    public function openroomlistAction()
    {
         
    }
    public function openstudentpersonalinfolistAction()
    {
         
    }
    public function openstaffpersonalinfolistAction()
    {
         
    }
    //End...
    public function jsonexcelAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {
            case "studentSearch":
            case "searchStudent":
                $jsondata = $this->STUDENT_EXCEL->studentSearch($this->REQUEST->getPost());
                break;
                
            //@CHHE Vathana
            case "enrolledStudentYearSearch":
                $jsondata = $this->STUDENT_EXCEL->enrolledStudentYearSearch($this->REQUEST->getPost());
                break;
            case "allRooms":
                $jsondata = $this->ROOM_EXCEL->allRooms($this->REQUEST->getPost());
                break;
            //End...
            case "jsonSearchStudentPreschool":
                $jsondata = $this->STUDENT_PRESCHOOL_EXCEL->jsonSearchStudentPreschool($this->REQUEST->getPost());
                break;
            case "staffSearch":
                $jsondata = $this->STAFF_EXCEL->staffSearch($this->REQUEST->getPost());
                break;
            case "loadClassEvents":
                $jsondata = $this->SCHEDULE_EXCEL->loadClassEvents($this->REQUEST->getPost());
                break;
            case "dayEventList":
                $jsondata = $this->SCHEDULE_EXCEL->dayEventList($this->REQUEST->getPost());
                break;
            case "jsonSearchStudentAttendance":
                $jsondata = $this->STUDENT_ATTENDANCE_EXCEL->jsonSearchStudentAttendance($this->REQUEST->getPost());
                break;
            //@Visal
            case "jsonSearchStudentStatus":
                $jsondata = $this->STUDENT_STATUS_EXCEL->jsonSearchStudentStatus($this->REQUEST->getPost());
                break;
            case "jsonSearchStudentAdvisory":
                $jsondata = $this->STUDENT_ADVISORY_EXCEL->jsonSearchStudentAdvisory($this->REQUEST->getPost());
                break;
            //@veasna
            case "jsonSearchStaffAttendance":
                $jsondata = $this->STUDENT_ATTENDANCE_EXCEL->jsonSearchStaffAttendance($this->REQUEST->getPost());
                break;
            //@veasna    
            case "jsonSearchStudentDiscipline":
                $jsondata = $this->STUDENT_DISCIPLINE_EXCEL->jsonSearchStudentDiscipline($this->REQUEST->getPost());
                break;
            
            case "jsonTrainingPerformance":
                $jsondata = $this->STUDENT_TRAINING_ASSESSMENT_EXCEL->listStudentsClassPerformanceTraining($this->REQUEST->getPost());
                break;
            //@Visal
            case "getStudentAttendanceData":
            case "getStudentDisciplineData":
            case "getStudentAdvisoryData":
                $jsondata = $this->TRADITION_FILTER_EXCEL->getStudentAttendanceData($this->REQUEST->getPost());
                break;
                
            case "studentpersonalinfo":
                $jsondata = $this->STUDENT_PERSONAL_EXCEL->studentpersonalinfo($this->REQUEST->getPost());
                break;
            case "staffpersonalinfo":
                $jsondata = $this->STAFF_PERSONAL_EXCEL->staffpersonalinfo($this->REQUEST->getPost());
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