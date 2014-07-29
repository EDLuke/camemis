<?php

require_once 'Zend/Acl.php';
require_once 'models/app_admin/AdminHelpDBAccess.php';
require_once 'models/app_admin/AdminUploadDBAccess.php';
require_once 'models/app_admin/AdminUserAuth.php';

class HelpController extends Zend_Controller_Action {

    public function init()
    {

        if (!AdminUserAuth::identify())
        {

            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();

        $this->objectId = null;
        $this->HELP_OBJECT = null;

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        $this->HELP_ACCESS = new AdminHelpDBAccess();
        $this->HELP_ACCESS->objectId = $this->objectId;
        
        $this->HELP_ACCESS = new AdminHelpDBAccess();
        $this->UPLOAD_ACCESS = new AdminUploadDBAccess();
    }

    public function indexAction()
    {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
    }

    public function showmainAction()
    {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->view->objectId = $this->objectId;
    }

    public function imageAction()
    {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->view->objectId = $this->objectId;
    }

    public function showdetailAction()
    {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->view->objectId = $this->objectId;
    }

    public function displaycontentAction()
    {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->view->objectId = $this->objectId;
    }

    public function editcontentAction()
    {

        AdminUserAuth::actionPermint($this->REQUEST, "CAMEMIS_HELP");
        $this->view->objectId = $this->objectId;
    }

    public function jsonloadAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonAllHelps":
                $jsondata = $this->HELP_ACCESS->jsonAllHelps($this->REQUEST->getPost());
                break;

            case "jsonLoadHelp":
                $jsondata = $this->HELP_ACCESS->jsonLoadHelp($this->REQUEST->getPost('objectId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonSaveHelp":
                $jsondata = $this->HELP_ACCESS->jsonSaveHelp($this->REQUEST->getPost());
                break;

            case "jsonRemoveHelp":
                $jsondata = $this->HELP_ACCESS->jsonRemoveHelp($this->REQUEST->getPost('objectId'));
                break;

            case "jsonUploadFile":
                $jsondata = $this->UPLOAD_ACCESS->jsonUploadFile($this->REQUEST->getPost());
                break;

            case "actionDeleteFile":
                $jsondata = $this->UPLOAD_ACCESS->deleteFile($this->REQUEST->getPost('blobId'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "jsonTreeHelps":
                $jsondata = $this->HELP_ACCESS->jsonTreeHelps($this->REQUEST->getPost());
                break;
            case "addFolder":
                $jsondata = $this->HELP_ACCESS->addFolder($this->REQUEST->getPost());
                break;
            case "additem":
                $jsondata = $this->HELP_ACCESS->additem($this->REQUEST->getPost());
                break;
        }

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