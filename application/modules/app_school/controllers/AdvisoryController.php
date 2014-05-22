<?php
//////////////////////////////////////////////////////////////////////////////////
//@Sea Peng
//Date: 22.11.2013
////////////////////////////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_school/student/StudentAdvisoryDBAccess.php';
require_once 'models/student_filter/jsonStudentFilterReport.php';

class AdvisoryController extends Zend_Controller_Action {

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
        
        $this->DB_STUDENT_ADVISORY = StudentAdvisoryDBAccess::getInstance();
        
        $this->objectId = null;
        $this->studentId = null;

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
            
        if ($this->_getParam('studentId'))
            $this->studentId = $this->_getParam('studentId');
    }

    public function studentadvisorymainAction() {
        $this->_helper->viewRenderer("student/index");    
    }
    
    public function studentshowitemAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("student/showitem");    
    }
    
    public function studentchartreportAction() {

        $this->_helper->viewRenderer("student/chartreport");
    }

    public function jsonloadAction() {
        switch ($this->REQUEST->getPost('cmd')) {
            case "jsonLoadAllActiveStudents":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonLoadAllActiveStudents($this->REQUEST->getPost());
                break;
                
            case "jsonSearchStudentAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonSearchStudentAdvisory($this->REQUEST->getPost());
                break;
                
            case "jsonLoadAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonLoadAdvisory($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonLoadStudentAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonLoadStudentAdvisory($this->REQUEST->getPost());
                break;
            ////@veasna
            case "getStudentAdvisoryData":
                $objectStudentAttendance = new jsonStudentFilterReport();
                $jsondata = $objectStudentAttendance->getGridData($this->REQUEST->getPost());
                break;
            ///
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
             case "jsonSaveAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonSaveAdvisory($this->REQUEST->getPost());
                break;
                
            case "jsonRemoveAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonRemoveAdvisory($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonActionAddStudentToAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonActionAddStudentToAdvisory($this->REQUEST->getPost());
                break;
                
            case "jsonRemoveStudentAdvisory":
                $jsondata = $this->DB_STUDENT_ADVISORY->jsonRemoveStudentAdvisory($this->REQUEST->getPost());
                break;                                             
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        //
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