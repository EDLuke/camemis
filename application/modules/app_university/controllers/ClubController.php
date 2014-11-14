<?php
///////////////////////////////////////////////////////////
//@Chung veng Web Developer
//Date: 22.06.2013
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_university/club/ClubDBAccess.php';
require_once 'models/app_university/club/StudentClubDBAccess.php';
require_once 'models/UserAuth.php';

class ClubController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        $this->DB_FILE = ClubDBAccess::getInstance();

        $this->objectId = null;
        
        $this->parentId = null;
        
        $this->facette = null;
        
        if ($this->_getParam('parentId')) {
            $this->parentId = $this->_getParam('parentId');
        }
        
        if ($this->_getParam('objectId')) {

            $this->objectId = $this->_getParam('objectId');
            $this->facette = ClubDBAccess::findClubFromId($this->objectId);
        }
        
        Zend_Registry::set('IS_CLUB',1);
    }

    public function indexAction() {
        $this->view->objectId = $this->objectId;
        $this->view->URL_ADDITEM = $this->UTILES->buildURL('club/show', array());  
        $this->view->URL_SHOWITEM = $this->UTILES->buildURL('club/showitem', array()); 
             
    }
   public function showAction() {
  
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        
       /*if($this->facette){    
            if ($this->objectId == 'new') {   
                    $this->_helper->viewRenderer("showitem"); 
            } else { 
                    $this->_helper->viewRenderer("show"); 
            }
            $this->view->parentId = $this->facette->PARENT; 
           
           //$this->_helper->viewRenderer("showitem");
        }else{                                             
            $this->view->parentId = $this->parentId; 
      
        } */
        if($this->facette){
             $this->view->parentId = $this->facette->PARENT;
        }else{
            $this->view->parentId = $this->parentId;
            $this->_helper->viewRenderer("showitem"); 
        }
        $this->view->parentObject = ClubDBAccess::findClubFromId($this->parentId);
    }      
   
    public function showitemAction() {
       
        $this->view->objectId = $this->objectId;
        $this->view->facette = $this->facette;
        
        if($this->facette){ 
            $this->view->parentId = $this->facette->PARENT;
          
        }else{
            $this->view->parentId = $this->parentId;
        }
        $this->view->parentObject = ClubDBAccess::findClubFromId($this->parentId);
    }
    
     public function clubeventsAction() {
      
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("clubevents/list");
        $this->view->URL_CLUB_SHOWITEM = $this->UTILES->buildURL('club/showitemsevent', array()); 
    }
    
    public function showitemseventAction() {
        $this->view->objectId = $this->objectId;
        $this->facette = ClubDBAccess::findClubeventFromId($this->objectId);
        $this->view->facette = $this->facette;
        $this->_helper->viewRenderer("/clubevents/showitems");
    }
    
    public function showitemsAction() {
        $this->view->objectId = $this->objectId;
        $this->_helper->viewRenderer("showitems");
    }
    
   
    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonLoadClub":               
                $jsondata = $this->DB_FILE->jsonLoadClub($this->REQUEST->getPost('objectId'));
                break;
                
            case "jsonListStudentInSchool":
                $jsondata = StudentClubDBAccess::jsonListStudentInSchool($this->REQUEST->getPost());
                break;
                
            case "jsonStudentClub":                
                $jsondata = StudentClubDBAccess::jsonStudentClub($this->REQUEST->getPost());
                break;
                
            case "jsonTeacherClubs":                
                $jsondata = StudentClubDBAccess::jsonTeacherClubs($this->REQUEST->getPost());
                break;
                
            case "jsonListTeacherInSchool":
                $jsondata = StudentClubDBAccess::jsonListTeacherInSchool($this->REQUEST->getPost());
                break;
                
            case "jsonStudentAttendanceMonth":
                $jsondata = $this->DB_FILE->jsonStudentAttendanceMonth($this->REQUEST->getPost());
                break;
                
            case "jsonTeacherClub":               
                $jsondata = $this->DB_FILE->jsonTeacherClub($this->REQUEST->getPost());
                break;
                
            case "allClubevents":
                $jsondata = $this->DB_FILE->allClubevents($this->REQUEST->getPost());
                break;
            case "loadObject":
                $jsondata = $this->DB_FILE->loadObject($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonSaveClub":             
                $jsondata = $this->DB_FILE->jsonSaveClub($this->REQUEST->getPost());
                break;
            
            case "jsonRemoveClub":                
                $jsondata = $this->DB_FILE->jsonRemoveClub($this->REQUEST->getPost('objectId'));
                break;
                
            case "actionStudentToClub":                
                $jsondata = StudentClubDBAccess::actionStudentToClub($this->REQUEST->getPost());
                break;
                
            case "actionRemoveStudentClub":              
                $jsondata = StudentClubDBAccess::actionRemoveStudentClub($this->REQUEST->getPost());
                break;
                
            case "jsonReleaseClub":
                $jsondata = $this->DB_FILE->jsonReleaseClub($this->REQUEST->getPost('objectId'));
                break;
            
            case "actionTeacherToClub":                
                $jsondata = StudentClubDBAccess::actionTeacherToClub($this->REQUEST->getPost());
                break;
                
            case "actionRemoveTeacherClub":               
                $jsondata = StudentClubDBAccess::actionRemoveTeacherClub($this->REQUEST->getPost());
                break;
         
            case "addObject":
                $jsondata = $this->DB_FILE->createOnlyItem($this->REQUEST->getPost());
                break;
            case "jsonSaveEvent":
                $jsondata = $this->DB_FILE->jsonSaveEvent($this->REQUEST->getPost());
                break;
            case "removeObject":
                $jsondata = $this->DB_FILE->removeObject($this->REQUEST->getPost());
                break;
                
            case "releaseObjectEvent":
                $jsondata = $this->DB_FILE->releaseObjectEvent($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }
    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "jsonTreeAllClubs":
                
                $jsondata = $this->DB_FILE->jsonTreeAllClubs($this->REQUEST->getPost());
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