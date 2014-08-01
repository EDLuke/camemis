<?php

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/CAMEMISHelpDBAccess.php';
require_once setUserLoacalization();

class HelpController extends Zend_Controller_Action {

    public function init()
    {

        if (!UserAuth::identify())
        {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
    }

    public function indexAction()
    {
        $this->view->key = $this->_getParam('key');
    }

    public function displaymainAction()
    {
        $this->view->objectId = $this->_getParam('objectId');
    }

    public function displaycontentAction()
    {
        $this->view->objectId = $this->_getParam('objectId');
    }

    public function jsontreeAction()
    {

        switch ($this->REQUEST->getPost('cmd'))
        {

            case "treeUserHelps":
                $jsondata = CAMEMISHelpDBAccess::treeUserHelps($this->REQUEST->getPost());
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