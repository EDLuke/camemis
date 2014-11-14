<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 30.11.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/examination/ExaminationDBAccess.php';
require_once 'models/app_university/examination/StudentExaminationDBAccess.php';
require_once 'models/app_university/examination/StaffExaminationDBAccess.php';
require_once 'models/app_university/assignment/AssignmentDBAccess.php';
require_once 'models/app_university/student/StudentImportDBAccess.php';

class ExaminationController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->UTILES = Utiles::getInstance();

        $this->gradeId = null;

        $this->schoolyearId = null;

        $this->DB_EXAM = ExaminationDBAccess::getInstance();

        $this->type = $this->_getParam('type');

        $this->parentId = $this->_getParam('parentId');

        $this->academicId = $this->_getParam('academicId');

        $this->objectId = $this->_getParam('objectId');

        $this->facette = ExaminationDBAccess::findExamFromId($this->objectId);
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");
    }

    public function listexaminationsAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");
        $this->view->parentId = $this->parentId;
        switch ($this->parentId) {
            //SEMESTER_TEST
            case 1:
                $this->_helper->viewRenderer("semester/index");
                break;
            //REPEAT_TEST
            case 2:
                $this->_helper->viewRenderer("repeat/index");
                break;
            case 3:
                //QUALITY_TEST
                $this->_helper->viewRenderer("quality/index");
                break;
            //OLYMPIA_TEST
            case 4:
                $this->_helper->viewRenderer("olympia/index");
                break;
            case 5:
                //STATE EXAM
                $this->_helper->viewRenderer("state/index");
                break;
            case 6:
                //ENROLLMENT
                $this->_helper->viewRenderer("enrollment/index");
                break;
        }
    }

    public function bysubjectAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");
        $academicObject = AcademicDBAccess::findGradeFromId($this->academicId);
        $this->view->academicId=$this->academicId;
        
        $this->view->academicObject = $academicObject;
        $this->view->facette = $this->facette;

        if ($academicObject) {
            $this->view->gradeId = $academicObject->GRADE_ID;
            $this->view->schoolyearId = $academicObject->SCHOOL_YEAR;
        }

        $this->view->parentId = $this->parentId;
        $this->view->objectId = $this->objectId;

        switch ($this->type) {
            case "1":
                //SEMESTER_TEST
                $this->_helper->viewRenderer("semester/studentsbysubject");
                break;
            case "2":
                //REPEAT_TEST
                $this->_helper->viewRenderer("repeat/studentsbysubject");
                break;
            case "3":
                //OLYMPIA_TEST
                $this->_helper->viewRenderer("olympia/studentsbysubject");
                break;
            case "4":
                //QUALITY_TEST
                $this->_helper->viewRenderer("quality/studentsbysubject");
                break;
            case "5":
                //STATE EXAM
                $this->_helper->viewRenderer("state/studentsbysubject");
                break;
            case "6":
                //ENROLLMENT
                $this->_helper->viewRenderer("enrollment/studentsbysubject");
                break;
        }
    }

    public function byroomAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");

        $this->view->facette = $this->facette;
        $this->view->academicId=$this->academicId;

        if ($this->facette) {
            $this->view->parentId = $this->facette->PARENT;
            $this->view->status = $this->facette->STATUS ? true : false;
            $this->view->removeStatus = $this->facette->STATUS ? false : true;
            
            $this->view->subjectFacette=ExaminationDBAccess::findExamFromId($this->facette->PARENT);
        } else {
            $this->view->parentId = $this->parentId;
        }

        $this->view->objectId = $this->objectId;

        switch ($this->type) {
            case "1":
                //SEMESTER_TEST
                $this->_helper->viewRenderer("semester/studentsbyroom");
                break;
            case "2":
                //REPEAT_TEST
                $this->_helper->viewRenderer("repeat/studentsbyroom");
                break;
            case "3":
                //OLYMPIA_TEST
                $this->_helper->viewRenderer("olympia/studentsbyroom");
                break;
            case "4":
                //QUALITY_TEST
                $this->_helper->viewRenderer("quality/studentsbyroom");
                break;
            case "5":
                //STATE EXAM
                $this->_helper->viewRenderer("state/studentsbyroom");
                break;
            case "6":
                //ENROLLMENT
                $this->_helper->viewRenderer("enrollment/studentsbyroom");
                break;
        }
    }

    public function showsubjectAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");

        $academicObject = AcademicDBAccess::findGradeFromId($this->academicId);
        $this->view->academicId=$this->academicId;

        $this->view->academicObject = $academicObject;
        $this->view->facette = $this->facette;

        if ($this->facette) {
            $this->view->status = $this->facette->STATUS ? true : false;
            $this->view->removeStatus = $this->facette->STATUS ? false : true;
        }

        if ($academicObject) {
            $this->view->gradeId = $academicObject->GRADE_ID;
            $this->view->schoolyearId = $academicObject->SCHOOL_YEAR;
        } elseif ($this->facette) {
            $this->view->gradeId = $this->facette->GRADE_ID;
            $this->view->schoolyearId = $this->facette->SCHOOLYEAR_ID;
        }

        $this->view->parentId = $this->parentId;
        $this->view->objectId = $this->objectId;

        switch ($this->type) {
            case "1":
                //SEMESTER_TEST
                $this->_helper->viewRenderer("semester/subject");
                break;
            case "2":
                //REPEAT_TEST
                $this->_helper->viewRenderer("repeat/subject");
                break;
            case "3":
                //OLYMPIA_TEST
                $this->_helper->viewRenderer("olympia/subject");
                break;
            case "4":
                //QUALITY_TEST
                $this->_helper->viewRenderer("quality/subject");
                break;
            case "5":
                //STATE EXAM
                $this->_helper->viewRenderer("state/subject");
                break;
            case "6":
                //ENROLLMENT
                $this->_helper->viewRenderer("enrollment/subject");
                break;
        }
    }

    public function showroomAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");
        $this->view->facette = $this->facette;
        $this->view->academicId=$this->academicId;

        if ($this->facette) {
            $this->view->parentId = $this->facette->PARENT;
            $this->view->status = $this->facette->STATUS ? true : false;
            $this->view->removeStatus = $this->facette->STATUS ? false : true;
        } else {
            $this->view->parentId = $this->parentId;
        }

        $this->view->objectId = $this->objectId;

        switch ($this->type) {
            case "1":
                //SEMESTER_TEST
                $this->_helper->viewRenderer("semester/room");
                break;
            case "2":
                //REPEAT_TEST
                $this->_helper->viewRenderer("repeat/room");
                break;
            case "3":
                //OLYMPIA_TEST
                $this->_helper->viewRenderer("olympia/room");
                break;
            case "4":
                //QUALITY_TEST
                $this->_helper->viewRenderer("quality/room");
                break;
            case "5":
                //STATE
                $this->_helper->viewRenderer("state/room");
                break;
            case "6":
                //ENROLLMENT
                $this->_helper->viewRenderer("enrollment/room");
                break;
        }
    }

    public function enrollmentmainAction() {
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("enrollment/main");
    }

    public function enrollmentimportxlsAction() {

        $this->view->academicId = $this->academicId;
        $this->view->URL_TEMPLATE_XLS = $this->UTILES->buildURL('examination/enrollmenttemplatexls', array('academicId' => $this->academicId));
        $this->view->URL_STUDENT_IMPORT = $this->UTILES->buildURL('examination/enrollmentimportxls', array('academicId' => $this->academicId));
        $this->_helper->viewRenderer("enrollment/mainimportxls");
    }
    
    //@veasna
    public function enrollmentlistcandidateAction() {
        $studnetImport=new StudentImportDBAccess();
        $params['objectType']='GENERAL_EDUCATION';
        $params['type']='ENROLL';
        $params['campus']=$this->objectId;
        $params['gender']=$this->_getParam('gender')?$this->_getParam('gender'):''; 
        $params['examResult']=$this->_getParam('examResult')?$this->_getParam('examResult'):''; 
        $this->view->objectDataResult=$studnetImport->importStudents($params,false);
        
        $this->view->academicId = $this->objectId;
        $this->view->gender=$this->_getParam('gender')?$this->_getParam('gender'):'';
        $this->view->examResult=$this->_getParam('examResult')?$this->_getParam('examResult'):'';
        $this->_helper->viewRenderer("enrollment/listcandidate");
    }
    //

    public function enrollmenttemplatexlsAction() {
        $this->view->academicId = $this->academicId;
        $this->_helper->viewRenderer("enrollment/maintemplatexls");
    }

    public function exportAction() {

        //UserAuth::actionPermint($this->_request, "EXAMINATION_MANAGEMENT");
        $this->view->facette = $this->facette;
        $this->view->objectId = $this->objectId;

        switch ($this->type) {
            case "1":
                //SEMESTER_TEST
                $this->_helper->viewRenderer("semester/export/index");
                break;
            case "2":
                //REPEAT_TEST
                $this->_helper->viewRenderer("repeat/export/index");
                break;
            case "3":
                //OLYMPIA_TEST
                $this->_helper->viewRenderer("olympia/export/index");
                break;
            case "4":
                //QUALITY_TEST
                $this->_helper->viewRenderer("quality/export/index");
                break;
            case "5":
                //STATE EXAM
                $this->_helper->viewRenderer("state/export/index");
                break;
            case "6":
                //ENROLLMENT
                $this->_helper->viewRenderer("enrollment/export/index");
                break;
        }
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonAllExamSubjects":
                $jsondata = ExaminationDBAccess::jsonAllExamSubjects($this->REQUEST->getPost());
                break;

            case "jsonAllExamRooms":
                $jsondata = ExaminationDBAccess::jsonAllExamRooms($this->REQUEST->getPost());
                break;

            case "loadExamination":
                $jsondata = ExaminationDBAccess::loadExamination($this->REQUEST->getPost('objectId'));
                break;

            case "jsonUnassignedStudentExamination":
                $jsondata = StudentExaminationDBAccess::jsonUnassignedStudentExamination($this->REQUEST->getPost());
                break;
                
            //@veasna
            
            case "jsonUnassignedStudentTmpExamination":
                $jsondata = StudentExaminationDBAccess::jsonUnassignedStudentTmpExamination($this->REQUEST->getPost());
                break;
            
            case "jsonAssignedStudentTmpExamination":
                $jsondata = StudentExaminationDBAccess::jsonAssignedStudentTmpExamination($this->REQUEST->getPost());
                break;
            //

            case "jsonAssignedStudentExamination":
                $jsondata = StudentExaminationDBAccess::jsonAssignedStudentExamination($this->REQUEST->getPost());
                break;

            case "jsonAssignedStudentExamRoom":
                $jsondata = StudentExaminationDBAccess::jsonAssignedStudentExamRoom($this->REQUEST->getPost());
                break;
                
             //@veasna
            case "jsonAssignedStudentTmpExamRoom":
                $jsondata = StudentExaminationDBAccess::jsonAssignedStudentTmpExamRoom($this->REQUEST->getPost());
                break;
            //

            case "jsonUnassignedStudentExamRoom":
                $jsondata = StudentExaminationDBAccess::jsonUnassignedStudentExamRoom($this->REQUEST->getPost());
                break;
            
            //@veasna
            case "jsonUnassignedStudentTmpExamRoom":
                $jsondata = StudentExaminationDBAccess::jsonUnassignedStudentTmpExamRoom($this->REQUEST->getPost());
                break;
            //

            case "jsonAssignedStaffExamination":
                $jsondata = StaffExaminationDBAccess::jsonAssignedStaffExamination($this->REQUEST->getPost());
                break;

            case "jsonUnassignedStaffExamination":
                $jsondata = StaffExaminationDBAccess::jsonUnassignedStaffExamination($this->REQUEST->getPost());
                break;

            case "jsonUnassignedStaffExamRoom":
                $jsondata = StaffExaminationDBAccess::jsonUnassignedStaffExamRoom($this->REQUEST->getPost());
                break;

            case "jsonAssignedStaffExamRoom":
                $jsondata = StaffExaminationDBAccess::jsonAssignedStaffExamRoom($this->REQUEST->getPost());
                break;

            case "loadMainExam":
                $jsondata = ExaminationDBAccess::loadMainExam($this->REQUEST->getPost('academicId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "removeExamination":
                $jsondata = ExaminationDBAccess::removeExamination($this->REQUEST->getPost("objectId"));
                break;

            case "removeRoom":
                $jsondata = ExaminationDBAccess::removeRoom($this->REQUEST->getPost("objectId"));
                break;

            case "jsonActionSaveExamination":
                $jsondata = ExaminationDBAccess::jsonActionSaveExamination($this->REQUEST->getPost());
                break;

            case "jsonActionReleaseExam":
                $jsondata = ExaminationDBAccess::jsonActionReleaseExam($this->REQUEST->getPost("objectId"));
                break;

            case "jsonActionSaveRoom":
                $jsondata = ExaminationDBAccess::jsonActionSaveRoom($this->REQUEST->getPost());
                break;

            case "jsonActionStudentToExamination":
                $jsondata = StudentExaminationDBAccess::jsonActionStudentToExamination($this->REQUEST->getPost());
                break;

            case "jsonActionRemoveStudentFromExamination":
                $jsondata = StudentExaminationDBAccess::jsonActionRemoveStudentFromExamination($this->REQUEST->getPost());
                break;

            case "jsonActionRemoveStaffFromExamination":
                $jsondata = StaffExaminationDBAccess::jsonActionRemoveStaffFromExamination($this->REQUEST->getPost());
                break;

            case "jsonActionChooseStudentManually":
                $jsondata = StudentExaminationDBAccess::jsonActionChooseStudentManually($this->REQUEST->getPost());
                break;
            
            //@veasna
            case "jsonActionChooseStudentTmpManually":
                $jsondata = StudentExaminationDBAccess::jsonActionChooseStudentTmpManually($this->REQUEST->getPost());
                break;
            //

            case "jsonRemoveAllStudentsFromExamination":
                $jsondata = StudentExaminationDBAccess::jsonRemoveAllStudentsFromExamination($this->REQUEST->getPost("objectId"));
                break;

            case "jsonActionAllStudentsToExamination":
                $jsondata = StudentExaminationDBAccess::jsonActionAllStudentsToExamination($this->REQUEST->getPost("objectId"));
                break;
                
            // @veasna
            
            case "jsonActionAllStudentsTmpToExamination":
                $jsondata = StudentExaminationDBAccess::jsonActionAllStudentsTmpToExamination($this->REQUEST->getPost("objectId"),$this->REQUEST->getPost("academicId"));
                break;
            
            //

            case "jsonActionRemoveStudentFromExamRoom":
                $jsondata = StudentExaminationDBAccess::jsonActionRemoveStudentFromExamRoom($this->REQUEST->getPost());
                break;

            case "jsonRemoveAllStudentsFromExamRoom":
                $jsondata = StudentExaminationDBAccess::jsonRemoveAllStudentsFromExamRoom($this->REQUEST->getPost("objectId"));
                break;

            case "jsonActionChooseStudentIntoRoom":
                $jsondata = StudentExaminationDBAccess::jsonActionChooseStudentIntoRoom($this->REQUEST->getPost());
                break;

            case "jsonActionChooseStaffToExamination":
                $jsondata = StaffExaminationDBAccess::jsonActionChooseStaffToExamination($this->REQUEST->getPost());
                break;

            case "jsonActionChooseStaffIntoRoom":
                $jsondata = StaffExaminationDBAccess::jsonActionChooseStaffIntoRoom($this->REQUEST->getPost());
                break;

            case "jsonActionRemoveStaffFromExamRoom":
                $jsondata = StaffExaminationDBAccess::jsonActionRemoveStaffFromExamRoom($this->REQUEST->getPost());
                break;

            case "jsonActionSaveMainExam":
                $jsondata = ExaminationDBAccess::jsonActionSaveMainExam($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllExaminations":
                $jsondata = ExaminationDBAccess::jsonTreeAllExaminations($this->REQUEST->getPost());
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