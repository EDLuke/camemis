<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 14.10.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/assignment/AssignmentTempDBAccess.php';
require_once 'models/app_school/assignment/AssignmentDBAccess.php';
require_once 'models/UserAuth.php';

class AssignmentController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->getResponse()->setHeader('X-FRAME-OPTIONS', 'SAMEORIGIN');
        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->DB_ASSIGNMENT = AssignmentDBAccess::getInstance();

        $this->DB_ASSIGNMENT_TEMP = AssignmentTempDBAccess::getInstance();

        $this->objectId = null;

        $this->gradeId = null;

        $this->target = "GENERAL";

        $this->subjectId = null;

        $this->facette = null;

        $this->classId = null;

        $this->teacherId = null;

        $this->objectData = array();

        if ($this->_getParam('objectId')) {

            if (substr($this->_getParam('objectId'), 8)) {
                $this->objectId = str_replace('CAMEMIS_', '', $this->_getParam('objectId'));
            } else {
                $this->objectId = $this->_getParam('objectId');
            }

            $this->objectData = $this->DB_ASSIGNMENT->getAssignmentDataFromId($this->objectId);
            $this->facette = AssignmentDBAccess::findAssignmentFromId($this->objectId);
        }

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('gradeId'))
            $this->gradeId = $this->_getParam('gradeId');

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('subjectId'))
            $this->subjectId = $this->_getParam('subjectId');

        if ($this->_getParam('teacherId'))
            $this->teacherId = $this->_getParam('teacherId');

        Zend_Registry::set('CHOOSE_GRADE', $this->gradeId);
        Zend_Registry::set('CHOOSE_OBJECT', $this->objectId);
        Zend_Registry::set('SUBJECT_ID', $this->subjectId);
    }

    public function indexAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->gradeId = $this->gradeId;

        if ($this->classId && $this->teacherId) {
            $gradeObject = AcademicDBAccess::findGradeFromId($this->classId);
        } else {
            $gradeObject = AcademicDBAccess::findGradeFromId($this->gradeId);
        }

        $this->view->term_number = $gradeObject->TERM_NUMBER;
        $this->view->subjectId = $this->subjectId;
    }

    public function showitemAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $this->view->facette = $this->facette;

        if ($this->facette) {
            $this->view->subjectId = $this->facette->SUBJECT;
            $this->view->status = $this->facette->STATUS;
            if ($this->facette->STATUS) {
                $this->view->remove_status = false;
            } else {
                $this->view->remove_status = true;
            }
        } else {
            $this->view->subjectId = $this->subjectId;
        }
    }

    public function showtempAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $facette = $this->DB_ASSIGNMENT_TEMP->findAssignmentTempFromId($this->objectId);
        $this->view->facette = $facette;

        if ($this->objectId != "new") {

            if ($facette) {
                if ($facette->TRAINING) {
                    $this->view->target = "TRAINING";
                } else {
                    $this->view->target = "GENERAL";
                }
            }
        } else {
            $this->view->target = strtoupper($this->target);
        }
    }

    public function jsonimportAction() {

        //UserAuth::actionPermint($this->_request, "ACADEMIC_SETTING");

        $jsondata = $this->DB_ASSIGNMENT->importAssignmentXLS($this->REQUEST->getPost());

        Zend_Loader::loadClass('Zend_Json');

        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        $this->getResponse()->setBody($json);
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_ASSIGNMENT->loadAssignmentFromId($this->REQUEST->getPost('objectId'));
                break;

            case "jsonAssignmentsByGrade":
                $jsondata = $this->DB_ASSIGNMENT->jsonAssignmentsByGrade($this->REQUEST->getPost());
                break;

            case "jsonComboST":
                $jsondata = $this->DB_ASSIGNMENT->jsonComboST($this->REQUEST->getPost());
                break;

            case "jsonClassInAssignment":
                $jsondata = $this->DB_ASSIGNMENT->jsonClassInAssignment($this->REQUEST->getPost());
                break;

            case "jsonLoadAssignmentTemp":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonLoadAssignmentTemp($this->REQUEST->getPost('objectId'));
                break;

            case "jsonListAssignmentTemp":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonListAssignmentTemp($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "actionAssignment":
                $jsondata = $this->DB_ASSIGNMENT->actionAssignment($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_ASSIGNMENT->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_ASSIGNMENT->releaseObject($this->REQUEST->getPost());
                break;

            case "jsonSaveClassInAssignment":
                $jsondata = $this->DB_ASSIGNMENT->jsonSaveClassInAssignment($this->REQUEST->getPost());
                break;

            case "jsonSaveAssignmentTemp":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonSaveAssignmentTemp($this->REQUEST->getPost());
                break;

            case "jsonRemoveAssignmentTemp":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonRemoveAssignmentTemp($this->REQUEST->getPost('objectId'));
                break;

            case "jsonAddAssignmentToSubject":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonAddAssignmentToSubject($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "treeAssignmentBySubject":
                $jsondata = $this->DB_ASSIGNMENT->treeAssignmentBySubject($this->REQUEST->getPost());
                break;

            case "jsonTreeAllAssignmentTemp":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonTreeAllAssignmentTemp($this->REQUEST->getPost());
                break;

            case "jsonTreeAssignmentsBySubjctClass":
                $jsondata = $this->DB_ASSIGNMENT->jsonTreeAssignmentsBySubjctClass($this->REQUEST->getPost());
                break;

            case "jsonTreeAssignmentsBySubjctTraining":
                $jsondata = $this->DB_ASSIGNMENT_TEMP->jsonTreeAssignmentsBySubjctTraining($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function importallassignmentsAction() {
        $this->view->classId = $this->classId;
        $this->view->teacherId = $this->teacherId;
        $this->view->subjectId = $this->subjectId;
        $this->view->classObject = $this->classObject;
    }

    public function exportallassignmentsAction() {

        $this->view->classId = $this->classId;
        $this->view->teacherId = $this->teacherId;
        $this->view->subjectId = $this->subjectId;
        $this->view->classObject = $this->classObject;
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>