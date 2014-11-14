<?php

require_once 'models/app_admin/AdminUserDBAccess.php';
require_once 'models/app_admin/AdminSessionAccess.php';

class IndexController extends Zend_Controller_Action {
    
    public function init() {

        $this->REQUEST = $this->getRequest();
    }
    
    public function indexAction() {
        
    }
    
    public function testAction() {
        
    }

}

?>