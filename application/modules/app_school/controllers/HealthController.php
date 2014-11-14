<?php

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/app_school/student/StudentHealthDBAccess.php';

class HealthController extends Zend_Controller_Action {

    public function init()
    {

        if (!UserAuth::identify())
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

        $this->DB_STUDENT_HEALTH = StudentHealthDBAccess::getInstance();

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');
    }

    public function indexAction()
    {
        
    }

    public function searchresultAction()
    {
        
    }

    public function chartreportAction()
    {
        
    }

    public function jsonloadAction()
    {

        // depending on what type of post request is made pass the table (and potentially other data into the proper method)
        if ($this->REQUEST->getPost('type') === "readAll")
        {
            $jsondata = $this->DB_STUDENT_HEALTH->getTableRows($this->REQUEST->getPost('table'));
            error_log("I controlled some shit: " . $jsondata);
        }
        // else if
        //
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction()
    {

        //
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction()
    {

        //
        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function setJSON($jsondata)
    {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>
