<?php

require_once 'Zend/Loader.php';

class IndexController extends Zend_Controller_Action {

    public function indexAction() {

        $tokenId = isset($_COOKIE['tokenId']) ? $_COOKIE['tokenId'] : '';
        $condition = array('ID = ? ' => $tokenId);

        if ($tokenId != "") {
            Zend_Registry::get('DB_ACCESS')->delete('t_sessions', $condition);
        }
    }

    public function index2Action() {
        
    }

    public function errorAction() {
        
    }

}

?>