<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 06.04.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/SchooleventDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';

class SchooleventController extends Zend_Controller_Action {

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

        $this->DB_SCHOOLEVENT = SchooleventDBAccess::getInstance();

        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();

        $this->objectId = null;

        $this->schoolyearId = null;

        $this->classId = null;

        $this->subjectId = null;

        $this->trainingId = null;

        $this->gradeId = null;

        $this->current_instructor = null;

        $this->objectData = array();

        $this->target = null;

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('subjectId'))
            $this->subjectId = $this->_getParam('subjectId');

        if ($this->_getParam('current_instructor'))
            $this->current_instructor = $this->_getParam('current_instructor');

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId');


        if ($this->_getParam('objectId')) {
            $this->objectId = $this->_getParam('objectId');
            $this->objectData = $this->DB_SCHOOLEVENT->getSchooleventDataFromId($this->objectId);
        }

        if ($this->_getParam('gradeId'))
            $this->gradeId = $this->_getParam('gradeId');

        if ($this->_getParam('trainingId'))
            $this->trainingId = $this->_getParam('trainingId');
    }

    public function indexAction() {

        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('schoolevent/showitem', array());
    }

    public function byclassAction() {

        $this->view->classId = $this->classId;
        $this->view->current_instructor = $this->current_instructor;

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('schoolevent/showitem', array());
    }

    //sea peng
    public function showitemAction() {

        $this->view->objectId = $this->objectId;
        $this->view->objectData = $this->objectData;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->subjectId = $this->subjectId;
        $this->view->classId = $this->classId;
        $this->view->target = $this->target;

        /* if ($this->objectData["STATUS"]) {
          $this->view->remove_status = false;
          } else {
          $this->view->remove_status = true;
          } */
    }

    //
    public function testscheduleAction() {

        $this->view->gradeId = $this->gradeId;
        $this->view->schoolyearId = $this->schoolyearId;
    }

    public function classeventsAction() {

        $this->view->classId = $this->classId;
        $this->view->trainingId = $this->trainingId;
        $this->view->gradeId = $this->gradeId;
        $this->view->target = $this->target;
        $this->view->subjectId = $this->subjectId;
        $classObject = AcademicDBAccess::findGradeFromId($this->classId);
        $this->view->schoolyearId = $classObject->SCHOOL_YEAR;
        if ($classObject) {
            $this->view->URL_SCHOOL_EVENT = $this->UTILES->buildURL("schoolevent", array(
                'schoolyearId' => $classObject->SCHOOL_YEAR
                , 'target' => true
                    )
            );

            $this->view->URL_EXAM_SCHEDULE = $this->UTILES->buildURL('examination/schedule', array(
                "gradeId" => $classObject->GRADE_ID
                , "schoolyearId" => $classObject->SCHOOL_YEAR
                , 'target' => true
            ));
        } else {
            if ($this->trainingId) {
                $currentSchoolyear = AcademicDateDBAccess::loadCurrentSchoolyear();
                $this->view->URL_SCHOOL_EVENT = $this->UTILES->buildURL("schoolevent", array(
                    'schoolyearId' => $currentSchoolyear ? $currentSchoolyear->ID : ''
                    //,'schoolyearId' => $classObject->SCHOOL_YEAR
                    , 'target' => true
                        )
                );
            }
        }

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('schoolevent/showitem', array());
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadObject":
                $jsondata = $this->DB_SCHOOLEVENT->loadObject($this->REQUEST->getPost('objectId'));
                break;

            case "allSchoolevents":
                $jsondata = $this->DB_SCHOOLEVENT->allSchoolevents($this->REQUEST->getPost());
                break;

            case "jsonLoadTestSchedule":
                $jsondata = $this->DB_SCHOOLEVENT->jsonLoadTestSchedule($this->REQUEST->getPost());
                break;

            case "jsonTestSchedule":
                $jsondata = $this->DB_SCHOOLEVENT->jsonTestSchedule($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "addObject":
                $jsondata = $this->DB_SCHOOLEVENT->createOnlyItem($this->REQUEST->getPost());
                break;

            case "updateObject":
                $jsondata = $this->DB_SCHOOLEVENT->updateSchoolevent($this->REQUEST->getPost());
                break;

            case "removeObject":
                $jsondata = $this->DB_SCHOOLEVENT->removeObject($this->REQUEST->getPost());
                break;

            case "releaseObject":
                $jsondata = $this->DB_SCHOOLEVENT->releaseObject($this->REQUEST->getPost());
                break;

            case "jsonSaveEvent":
                $jsondata = $this->DB_SCHOOLEVENT->jsonSaveEvent($this->REQUEST->getPost());
                break;

            case "jsonActionTestSchedule":
                $jsondata = $this->DB_SCHOOLEVENT->jsonActionTestSchedule($this->REQUEST->getPost());
                break;

            case "jsonActionClassEvent":
                $jsondata = $this->DB_SCHOOLEVENT->jsonActionClassEvent($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllSchoolevents":
                $jsondata = $this->DB_SCHOOLEVENT->jsonTreeAllSchoolevents($this->REQUEST->getPost());
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