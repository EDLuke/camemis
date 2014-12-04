<?php

require_once("Zend/Loader.php");
require_once("Zend/Soap/AutoDiscover.php");
require_once 'Zend/Soap/clients.php';
require_once 'models/app_admin/DBWebservices.php';

class SoapController extends Zend_Controller_Action {

    public function init() {
        
    }

    private $_WSDL_URI = "http://admin-tes.camemis.vn/soap";

    public function indexAction() {
        // disable layouts and renderers
        $this->getHelper('viewRenderer')->setNoRender(true);
        // initialize server and set URI
        $server = new Zend_Soap_Server(null,
                        array('uri' => $this->_WSDL_URI));
        // set SOAP service class
        $server->setEncoding('ISO-8859-1');
        $server->setClass('DBWebservices');
        // handle request
        $server->handle();
    }
}