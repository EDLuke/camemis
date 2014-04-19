<?php

///////////////////////////////////////////////////////////
// @Thou Morng Sothearung Software Developer
// Date: 05.01.2011
// 03 Rue des Pibleus, Bailly Romainvilliers, France
// VIKENSOFT
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';
require_once 'models/ReportDBAccess.php';

class ReportController extends Zend_Controller_Action {

    public function init() {
        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "treeReportTraditionalAcademic":
                $jsondata = ReportDBAccess::treeReportTraditionalAcademic($this->REQUEST->getPost());
                break;
            case "treeReportCreditAcademic":
                $jsondata = ReportDBAccess::treeReportCreditAcademic($this->REQUEST->getPost());
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