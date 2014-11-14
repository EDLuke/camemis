<?php

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/forum/ForumDBAccess.php';
require_once 'models/CamemisTypeDBAccess.php';
require_once 'models/UserAuth.php';


class ForumController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }
        
        $this->DB_FORUM = ForumDBAccess::getInstance();

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
        if ($this->_getParam('parentId'))
            $this->parentId = $this->_getParam('parentId');
        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');
        if ($this->_getParam('camemisType'))
            $this->camemisType = $this->_getParam('camemisType');
         if ($this->_getParam('targetAction'))
            $this->targetAction = $this->_getParam('targetAction');      
    }

    public function indexAction() {
        $this->view->target = strtoupper($this->target);    
    }
    
    public function forumAction() {
        $this->view->target = strtoupper($this->target); 
        $cagatoryObject = $this->DB_FORUM->getForumCAMEMISType(strtoupper($this->target)); 
        foreach($cagatoryObject as $values){
            if($values->READONLY)
            $this->view->camemisTypeParent=$values->ID;
            $this->view->camemisObjectType=$values->OBJECT_TYPE;
            if(!CamemisTypeDBAccess::checkChild($values->ID)){
                $this->view->treeSelectId =$values->ID;
                break;         
            }   
        }
         
         
    }
    
    public function showAction() {
        $this->view->objectId = $this->_getParam('objectId')? $this->objectId :''; 
        $this->view->parentId = $this->_getParam('parentId')? $this->parentId :''; 
        $this->view->target = strtoupper($this->target);
        $this->view->camemisType = $this->camemisType;
        $this->view->targetAction = $this->_getParam('targetAction')?strtoupper($this->targetAction):'';
        $this->_helper->viewRenderer("showtopic"); 
    }
    
    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTopicForum":
                $jsondata = ForumDBAccess::jsonTopicForum($this->REQUEST->getPost());
                break;
            case "jsonLoadForum":
                $jsondata = ForumDBAccess::jsonLoadForum($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveForum":
                $jsondata = ForumDBAccess::jsonSaveForum($this->REQUEST->getPost());
                break;
            case "deleteForum":
                $jsondata = ForumDBAccess::deleteForum($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {
        
       switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeCatagories":
                $jsondata = CamemisTypeDBAccess::jsonTreeCatagories($this->REQUEST->getPost());
                break;
            case "jsonTreeGridForum":
                $jsondata = ForumDBAccess::jsonTreeGridForum($this->REQUEST->getPost());
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
