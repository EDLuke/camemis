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
require_once 'models/app_school/enrollment/EnrollmentDBAccess.php';
require_once 'models/UserAuth.php';

class EnrollmentController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        $this->DB_ENROLLMENT = EnrollmentDBAccess::getInstance();

        $this->classId = null;

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "PASS_FAIL_MANAGEMENT");
    }

    public function studentsearchAction() {

        //UserAuth::actionPermint($this->_request, "PASS_FAIL_MANAGEMENT");
    }
    
    //@veasna
    
    public function trainingclasstransferAction(){
        $this->_helper->viewRenderer("classtransfertraining/index");
    }
    public function trainingtransferAction(){
        $this->_helper->viewRenderer("classtransfertraining/traininglist");    
    }
    public function transfertrainingAction(){
        $this->view->objectId = $this->_getParam('objectId');
        $this->_helper->viewRenderer("classtransfertraining/transfertraining");    
    }
    public function traininghistorytransferAction(){
        $this->_helper->viewRenderer("classtransfertraining/historytransfer");    
    }
    public function historytransferAction(){
        
    }
    
    public function transferstudentAction(){
        
    }
    
    public function enrollmentbyyearAction() {
        $this->view->isCurrentYear = $this->_getParam('isCurrentYear');

        $this->view->objectId = $this->_getParam('objectId');
        $this->view->schoolyearId = $this->_getParam('schoolyearId');
        $this->view->campusId = $this->_getParam('campusId');
        $this->view->gradeId = $this->_getParam('gradeId');
        $this->view->gradeObject = AcademicDBAccess::findGradeFromId($this->_getParam('gradeId'));
        
    }
    
    public function diplaygradebookyearAction() {
        
        $this->view->classId = $this->_getParam('classId');
        $this->view->teacherId = $this->_getParam('teacherId');
        $this->view->studentId = $this->_getParam('studentId');
    }  
    
     public function studentattendanceyearAction() {
        
        $this->view->studentId = $this->_getParam('studentId');
        $this->view->classId = $this->_getParam('classId');
        $this->view->trainingId = $this->_getParam('trainingId');
        $this->view->target = $this->_getParam('target');
           
    }
    //

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonListStudentLastSchoolyear":
                $jsondata = EnrollmentDBAccess::jsonListStudentLastSchoolyear($this->REQUEST->getPost());
                break;

            case "jsonListStudentNextSchoolyear":
                $jsondata = EnrollmentDBAccess::jsonListStudentNextSchoolyear($this->REQUEST->getPost());
                break;
            
            case "jsonAllStudentsHistory":
                $jsondata = EnrollmentDBAccess::jsonAllStudentsHistory($this->REQUEST->getPost());
                break;
            //@veasna
            case "jsonListStudentTraining":
                $jsondata = $this->DB_ENROLLMENT->jsonListStudentTraining($this->REQUEST->getPost());
                break;
            
            case "jsonAllStudentsTrainingHistory":
                $jsondata = $this->DB_ENROLLMENT->jsonAllStudentsTrainingHistory($this->REQUEST->getPost());
                break;
            
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "addStudentToNewGradeSchoolyear":
                $jsondata = EnrollmentDBAccess::addStudentToNewGradeSchoolyear($this->REQUEST->getPost());
                break;
            //@veasna
            case "transferStudentToGradeSchoolyear":
                 //error_log('dsfsdfdsf');
                $jsondata = EnrollmentDBAccess::transferStudentToGradeSchoolyear($this->REQUEST->getPost());
                break;
            //
            case "deletestudentFromNewGradeSchoolyear":
                $jsondata = EnrollmentDBAccess::deletestudentFromNewGradeSchoolyear($this->REQUEST->getPost());
                break;
            case "transferStudentTraining"://@veasna
                $jsondata = $this->DB_ENROLLMENT->transferStudentTraining($this->REQUEST->getPost());
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