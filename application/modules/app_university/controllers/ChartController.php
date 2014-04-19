<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 26.06.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class ChartController extends Zend_Controller_Action {

    public function init()
    {

        if (!UserAuth::mainidentify())
        {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds'))
        {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }
    }

    public function indexAction()
    {
        
    }

    public function studentacademictraditionalAction()
    {
        
    }

    public function studentacademiccreditAction()
    {
        
    }

    public function studentattendanceAction()
    {
        
    }

    public function staffattendanceAction()
    {
        
    }

    public function studentacademictrainingAction()
    {
        
    }

    public function staffadministrationAction()
    {
        
    }

    public function studentadvisoryAction()
    {
        
    }

    public function staffcontractAction()
    {
        
    }

    public function useronlineAction()
    {
        
    }

    public function disciplineAction()
    {
        
    }

    public function facilityAction()
    {
        
    }

    public function letterdashboardAction()
    {
        
    }

    public function studentcreditinformationAction()
    {
        
    }

}

?>