<?php
///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 06.02.2014
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/CamemisEvaluationDBAccess.php';

class CamemisevaluationController extends Zend_Controller_Action {
    
    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->DB_CAMEMIS_EVALUATION = CamemisEvaluationDBAccess::getInstance();
        
        $this->objectId = null;
        $this->parentId = null;
        $this->facette = null; 

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
            
        if ($this->_getParam('parentId'))
            $this->parentId = $this->_getParam('parentId');
    }

    public function evaluationmanagementmainAction() {
        
        $this->_helper->viewRenderer("index");    
    }
    
    public function evaluationdashboardAction() {
        
        $this->_helper->viewRenderer("dashboard");     
    }
    
    public function evaluationtopicAction() {
        
        $this->_helper->viewRenderer("topic/index");     
    }
    
    public function evaluationsettingAction() {
        
        $this->_helper->viewRenderer("setting");     
    }
    
    public function evaluationquestionAction() {
        
        $this->_helper->viewRenderer("question/index");     
    }
    
    public function evaluationanswerAction() {
        
        $this->_helper->viewRenderer("answer/index");     
    }
    
    public function questionshowAction() {
        
        $this->view->objectId = $this->objectId;
        $this->view->facette = CamemisEvaluationDBAccess::findQuestionObjectFromId($this->_getParam('objectId'));
        $this->_helper->viewRenderer("question/showitem");     
    }
    
    public function answershowAction() {
        
        $this->view->objectId = $this->objectId;
        $this->view->parentId = $this->parentId;
        $this->view->facette = CamemisEvaluationDBAccess::findAnswerObjectFromId($this->_getParam('objectId'));
        $this->_helper->viewRenderer("answer/showitem");     
    }
    
    public function topicshowAction() {
        
        $this->view->objectId = $this->objectId;
        $this->view->parentId = $this->parentId;
        $this->view->facette = CamemisEvaluationDBAccess::findTopicObjectFromId($this->_getParam('objectId'));
        $this->_helper->viewRenderer("topic/showitem");     
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {
             case "jsonLoadEvaluationQuestion":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonLoadEvaluationQuestion($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonLoadAllEvaluationQuestion":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonLoadAllEvaluationQuestion($this->REQUEST->getPost());
                break;
                
            case "jsonLoadEvaluationAnswer":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonLoadEvaluationAnswer($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonLoadEvaluationTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonLoadEvaluationTopic($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonLoadUnassignedQuestionToTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonLoadUnassignedQuestionToTopic($this->REQUEST->getPost());
                break;
                
            case "jsonLoadEvaluationQuestionByTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonLoadEvaluationQuestionByTopic($this->REQUEST->getPost());
                break;                                                                        
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {
             case "jsonSaveEvaluationQuestion":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonSaveEvaluationQuestion($this->REQUEST->getPost());
                break;
                
            case "jsonRemoveEvaluationQuestion":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonRemoveEvaluationQuestion($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonSaveEvaluationAnswer":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonSaveEvaluationAnswer($this->REQUEST->getPost());
                break;
                
            case "jsonRemoveEvaluationAnswer":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonRemoveEvaluationAnswer($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonSaveEvaluationTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonSaveEvaluationTopic($this->REQUEST->getPost());
                break;
                
            case "jsonRemoveEvaluationTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonRemoveEvaluationTopic($this->REQUEST->getPost("objectId"));
                break;
                
            case "jsonActionAcademicToEvaluation":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonActionAcademicToEvaluation($this->REQUEST->getPost());
                break;
                
            case "jsonActionAddQuestionToTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonActionAddQuestionToTopic($this->REQUEST->getPost());
                break;
                
            case "jsonRemoveQuestionFromTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonRemoveQuestionFromTopic($this->REQUEST->getPost("objectId"));
                break;                                                                                           
        }
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            
            case "jsonTreeAllEvaluationAnswer":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonTreeAllEvaluationAnswer($this->REQUEST->getPost());
                break;
                
            case "jsonTreeAllEvaluationTopic":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->jsonTreeAllEvaluationTopic($this->REQUEST->getPost());
                break;
                
            case "getAcademicsByEvaluation":
                $jsondata = $this->DB_CAMEMIS_EVALUATION->getAcademicsByEvaluation($this->REQUEST->getPost());
                break;
                
            case "treeAllStaffs":
                $jsondata = StaffDBAccess::treeAllStaffs($this->REQUEST->getPost());
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