<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_university/student/StudentPreschoolDBAccess.php';
require_once 'models/app_university/student/StudentImportDBAccess.php';

class StudentPreschoolController extends Zend_Controller_Action {

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
        
        $this->DB_STUDENT_PRESCHOOL = StudentPreschoolDBAccess::getInstance();
        $this->DB_STUDENT_IMPORT = StudentImportDBAccess::getInstance();
        

        $this->objectId = null;
        $this->type = null;
        $this->facette = null;
        
        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
            
        if ($this->_getParam('type'))
            $this->type = $this->_getParam('type');
            
        $this->facette = StudentImportDBAccess::checkAllImportInTemp();

    }

    public function studentpreschoolmainAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("student/index");            
    }
    
    public function chartreportAction() {

        $this->_helper->viewRenderer("student/chartreport");
    }
    
    public function studentpreschoolshowitemAction() {
        $this->view->objectId = $this->objectId;
        $this->view->type = $this->type;
        $this->_helper->viewRenderer("student/showitem");    
    }
    
    public function importxlsAction() {

        $this->view->URL_TEMPLATE_XLS = $this->UTILES->buildURL('studentpreschool/templatexls', array());
        $this->view->URL_STUDENT_PRESCHOOL_IMPORT = $this->UTILES->buildURL('studentpreschool/importxls', array());
        $this->view->facette = $this->facette; //@Math Man
        $this->_helper->viewRenderer("import/importxls");
    }
    
    public function templatexlsAction() {
        $this->_helper->viewRenderer("import/templatexls");
    }
    
    public function jsonloadAction() {
         switch ($this->REQUEST->getPost('cmd')) {
            case "jsonLoadStudentPreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonLoadStudentPreschool($this->REQUEST->getPost());
                break;
            case "jsonLoadTypePreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonLoadTypePreschool($this->REQUEST->getPost());
                break;
            case "jsonListGridpreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonListGridpreschool($this->REQUEST->getPost());
                break;
            case "jsonShowAllChooseStudentPreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonShowAllChooseStudentPreschool($this->REQUEST->getPost());
                break;
            case "jsonSearchStudentPreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonSearchStudentPreschool($this->REQUEST->getPost());
                break;
            case "importStudents":
                $jsondata = $this->DB_STUDENT_IMPORT->importStudents($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {
        
        switch ($this->REQUEST->getPost('cmd')) {
             case "jsonSaveStudentPreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonSaveStudentPreschool($this->REQUEST->getPost());
                break;
                
             case "jsonSaveTypePreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonSaveTypePreschool($this->REQUEST->getPost());
                break;
             
             case "jsonSaveGridpreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonSaveGridpreschool($this->REQUEST->getPost());
                break;
                
             case "jsonRemoveStudentPreschool":
                $jsondata = $this->DB_STUDENT_PRESCHOOL->jsonRemoveStudentPreschool($this->REQUEST->getPost());
                break;
                
             case "jsonRemoveStudentsFromImport":
                $jsondata = $this->DB_STUDENT_IMPORT->jsonRemoveStudentsFromImport($this->REQUEST->getPost());
                break;
                
             case "jsonAddPreSchoolStudentDB":
                $jsondata = $this->DB_STUDENT_IMPORT->jsonAddPreSchoolStudentDB($this->REQUEST->getPost());
                break;
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonimportAction() {

        $jsondata = $this->DB_STUDENT_IMPORT->importXLS($this->REQUEST->getPost());

        Zend_Loader::loadClass('Zend_Json');

        if (isset($jsondata))
            $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        if (isset($json))
            $this->getResponse()->setBody($json);
    }
    
    public function jsontreeAction() {

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