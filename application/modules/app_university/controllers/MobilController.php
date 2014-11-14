<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/UserAuth.php';

class MobilController extends Zend_Controller_Action {

    protected $OBJECT_DATA;
    protected $objectId;
    protected $roleAdmin = array("SYSTEM");

    public function init() {
        
        /*
        if (!UserAuth::identify()) {
            
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();
        */
    }

    public function indexAction() {

       
    }
}

?>