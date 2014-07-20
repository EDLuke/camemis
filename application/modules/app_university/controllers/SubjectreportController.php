<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 03.05.2013
// 
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/subject/SubjectTeachingReportDBAccess.php';
require_once setUserLoacalization();

class SubjectreportController extends Zend_Controller_Action {

    protected $OBJECT_DATA;
    protected $roleAdmin = array("SYSTEM");

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->UTILES = Utiles::getInstance();

        $this->REQUEST = $this->getRequest();
        
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->DB_SUBJECT_TEACHING_REPORT = SubjectTeachingReportDBAccess::getInstance();

        $this->objectId = null;

        $this->classId = null;

        $this->subjectId = null;

        $this->teacherId = null;

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId');

        if ($this->_getParam('subjectId'))
            $this->subjectId = $this->_getParam('subjectId');

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
            
        if ($this->_getParam('teacherId'))
            $this->teacherId = $this->_getParam('teacherId');
    }

    public function subjectreportmainAction() {

        $this->view->classId = $this->classId;
        $this->view->teacherId = $this->teacherId;
        $this->_helper->viewRenderer("main");
    }

    public function subjectreportlistAction() {

        $this->view->classId = $this->classId;
        $this->view->teacherId = $this->teacherId;
        $this->view->subjectId = $this->subjectId;

        $this->view->URL_SHOWITEM = $this->UTILES->buildURL(
                'subjectreport/showitem', array(
            "classId" => $this->classId
                )
        );

        $this->_helper->viewRenderer("list");
    }

    public function showitemAction() {
        $this->view->classId = $this->classId;
        $this->view->subjectId = $this->subjectId;
        $this->view->objectId = $this->objectId;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadAllSubjectTeachingReport":
                $jsondata = SubjectTeachingReportDBAccess::jsonLoadAllSubjectTeachingReport($this->REQUEST->getPost());
                break;
            case "jsonLoadTeachingReport":
                $jsondata = SubjectTeachingReportDBAccess::jsonLoadTeachingReport($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveSubjectTeachingReport":
                $jsondata = SubjectTeachingReportDBAccess::jsonSaveSubjectTeachingReport($this->REQUEST->getPost());
                break;
            //go here
            case "jsonDeleteSubjectTeachingReport":
                $jsondata = $this->DB_SUBJECT_TEACHING_REPORT->jsonDeleteSubjectTeachingReport($this->REQUEST->getPost());
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