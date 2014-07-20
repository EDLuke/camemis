<?php

switch (Zend_Registry::get('MODUL_API')) {
    case "dfe34ef0f0b812ea32d92866dbe9e3cb":
        require_once 'models/UserAuth.php';
        break;
    case "dfe34ef0f0b812ea32d12866dbe9c3cb":
        require_once 'models/UserAuth.php';
        break;
    default:
        require_once 'models/UserAuth.php';
        break;
}

class ElearningController extends Zend_Controller_Action {

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->objectId = null;
        $this->HELP_OBJECT = null;

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
    }

    public function indexAction() {
        
    }

    public function lessonAction() {
        $this->_helper->viewRenderer("lesson/index");
    }

    public function courseAction() {
        $this->_helper->viewRenderer("course/index");
    }

    public function forumAction() {
        $this->_helper->viewRenderer("forum/index");
    }

    public function settingAction() {
        $this->_helper->viewRenderer("setting/index");
    }

    public function categoryAction() {
        $this->_helper->viewRenderer("category/index");
    }
    
    public function reportAction() {
        $this->_helper->viewRenderer("report/index");
    }

}

?>