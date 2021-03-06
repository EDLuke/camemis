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
require_once 'models/UserAuth.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/filter/jsonFilterReport.php';
require_once 'models/DisciplineDBAccess.php';

class DisciplineController extends Zend_Controller_Action {

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

        $this->DB_DISCIPLINE = DisciplineDBAccess::getInstance();

        $this->target = null;
        $this->objectId = null;
        $this->studentId = null;
        $this->classId = null;
        $this->trainingId = null;
        $this->teacherId = null;
        $this->facette = null;
        $this->schoolyearId = null;
        $this->personType = null; //@Man

        $this->studentId = $this->_getParam('studentId');

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('trainingId'))
            $this->trainingId = $this->_getParam('trainingId');

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');

        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->facette = $this->DB_DISCIPLINE->findDisciplineFromId($this->objectId);
        }

        if ($this->_getParam('infractionId')) {
            $this->infractionId = $this->_getParam('infractionId');
            $this->infractionData = $this->DB_DISCIPLINE->loadInfractionFromId($this->infractionId);
        }

        if ($this->_getParam('personType'))
            $this->personType = $this->_getParam('personType');
    }

    public function searchresultAction() {
        $this->view->personType = $this->personType;
    }

    public function chartreportAction() {
        
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_DISCIPLINE");
        $this->view->classId = $this->classId;
        $this->view->personType = $this->personType;
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('discipline/showitem', array());

        $this->view->URL_EXPORT_EXCEL = $this->UTILES->buildURL(
                'discipline/exportexcel', array()
        );
    }

    public function exportexcelAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_DISCIPLINE");
        $this->view->target = $this->target;
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_DISCIPLINE");

        $this->view->objectId = $this->objectId;
        $this->view->disciplineObject = $this->DB_DISCIPLINE->findDisciplineFromId($this->objectId);
        $this->view->studentId = $this->studentId;
        $this->view->facette = $this->facette;
        $this->view->trainingId = $this->trainingId;
        $this->view->classId = $this->classId;
        $this->view->target = $this->target;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->personType = $this->personType;

        $this->view->status = $this->facette ? $this->facette->STATUS : 0;

        if ($this->objectId != "new") {
            if ($this->facette->STATUS == 0)
                $this->view->remove_status = true;
            else
                $this->view->remove_status = false;
        }else {
            $this->view->remove_status = false;
        }
    }

    ///creditsystem @veasna
    public function creditsystemshowitemAction() {

        //UserAuth::actionPermint($this->_request, "STUDENT_DISCIPLINE");

        $this->view->objectId = $this->objectId;
        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;
        $compusId = StudentAcademicDBAccess::getStudentCurrentAcademicCreditSystem($this->studentId, $this->schoolyearId);

        if ($compusId) {
            $this->view->creditAcademicId = $compusId->ID;
        }

        $this->view->facette = $this->facette;
        $this->view->target = $this->target;

        $this->view->status = $this->facette ? $this->facette->STATUS : 0;

        if ($this->objectId != "new") {
            if ($this->facette->STATUS == 0)
                $this->view->remove_status = true;
            else
                $this->view->remove_status = false;
        }else {
            $this->view->remove_status = false;
        }

        $this->_helper->viewRenderer("creditsystem/showitem");
    }

    /////

    public function byclassAction() {

        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->trainingId = $this->trainingId;
        $this->view->personType = $this->personType;
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('discipline/showitem', array());
        $this->view->URL_NEW_SHOWITEM = $this->UTILES->buildURL('discipline/showitem'
                , array(
            'objectId' => 'new'
            , 'studentId' => $this->studentId
            , 'classId' => $this->classId
                )
        );

        $this->_helper->viewRenderer("list");
    }

    public function studentdisciplinesAction() {

        $this->view->target = $this->target;
        $this->view->classId = $this->classId;
        $this->view->teacherId = $this->teacherId;
        $this->view->trainingId = $this->trainingId;

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('discipline/showitem', array());
        $this->view->URL_NEW_SHOWITEM = $this->UTILES->buildURL('discipline/showitem'
                , array(
            'objectId' => 'new'
            , 'studentId' => $this->studentId
            , 'classId' => $this->classId
            , 'studentId' => $this->target
            , 'schoolyearId' => $this->schoolyearId
                )
        );
        switch ($this->target) {
            case "general":
                $this->_helper->viewRenderer("general/student/list");
                break;
            case "training":
                $this->_helper->viewRenderer("training/student/list");
                break;
        }
    }

    public function bystudentAction() {
        $this->view->classId = $this->classId;
        $this->view->studentId = $this->studentId;
        $this->view->trainingId = $this->trainingId;
        $this->view->personType = $this->personType;
        
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('discipline/showitem', array());
        $this->view->URL_NEW_SHOWITEM = $this->UTILES->buildURL('discipline/showitem'
                , array(
            'objectId' => 'new'
            , 'studentId' => $this->studentId
            , 'classId' => $this->classId
                )
        );

        $this->_helper->viewRenderer("list");
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "allDisciplines":
                $jsondata = $this->DB_DISCIPLINE->allDisciplines($this->REQUEST->getPost());
                break;

            case "loadDiscipline":
                $jsondata = $this->DB_DISCIPLINE->loadDiscipline($this->REQUEST->getPost());
                break;

            case "jsonListByDicipline":
                $jsondata = $this->DB_DISCIPLINE->jsonListByDicipline($this->REQUEST->getPost());
                break;
            case "jsonShowAllStudents":
                $jsondata = $this->DB_DISCIPLINE->jsonShowAllStudents($this->REQUEST->getPost());
                break;
            ////@veasna
            case "getStudentDisciplineData":
                $objectStudentAttendance = new jsonFilterReport();
                $jsondata = $objectStudentAttendance->getGridData($this->REQUEST->getPost());
                break;
            ///
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "removeDiscipline":
                $jsondata = $this->DB_DISCIPLINE->removeDiscipline($this->REQUEST->getPost("objectId"));
                break;

            case "actionDiscipline":
                $jsondata = $this->DB_DISCIPLINE->actionDiscipline($this->REQUEST->getPost());
                break;

            case "releaseDiscipline":
                $jsondata = $this->DB_DISCIPLINE->releaseDiscipline($this->REQUEST->getPost("objectId"));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            
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