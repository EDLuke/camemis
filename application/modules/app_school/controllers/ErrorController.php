<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 26.01.2010
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once 'Zend/Controller/Action.php';

class ErrorController extends Zend_Controller_Action {

    public function errorAction() {

        $this->exception = $this->_getParam('error_handler');
        #echo get_class($this->exception->exception);
        #print_r($this->exception);
        #exit;
        switch ($this->exception->type) {

            // EXCEPTION_NO_CONTROLLER
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:

                $this->_request->setControllerName('error');

                if (!UserAuth::isVerifyTime()) {
                    $this->_request->setActionName('expired');
                } else {
                    $this->_request->setActionName('nocontroller');
                }

                $this->_request->setDispatched(false);

                //echo "<b>EXCEPTION_NO_CONTROLLER</b>";
                break;

            // EXCEPTION_NO_ACTION
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                $this->_request->setControllerName('error');

                if (!UserAuth::isVerifyTime()) {
                    $this->_request->setActionName('expired');
                } else {
                    $this->_request->setActionName('noaction');
                }
                $this->_request->setDispatched(false);
                //echo "<b>EXCEPTION_NO_ACTION</b>";
                break;
            // EXCEPTION_OTHER
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:

                //echo get_class($this->exception->exception);

                switch (get_class($this->exception->exception)) {

                    case 'Zend_Viewexception' :
                        $this->_request->setControllerName('error');

                        if (!UserAuth::isVerifyTime()) {
                            $this->_request->setActionName('expired');
                        } else {
                            $this->_request->setActionName('viewexception');
                        }

                        $this->_request->setDispatched(false);
                        //echo "<b>Zend_Db_Statement_Exception</b>";
                        break;

                    case 'Zend_Db_Statement_Exception':
                        $this->_request->setControllerName('error');

                        if (!UserAuth::isVerifyTime()) {
                            $this->_request->setActionName('expired');
                        } else {
                            $this->_request->setActionName('dbstatement');
                        }

                        $this->_request->setDispatched(false);
                        //echo "<b>Zend_Db_Statement_Exception</b>";
                        break;

                    case 'Zend_Dbexception' :
                        //echo "<b>Zend_Dbexception</b>";
                        $this->_request->setControllerName('error');
                        $this->_request->setActionName('dbstatement');
                        $this->_request->setDispatched(false);

                        break;

                    case 'Zend_Auth_Adapterexception' :
                        //echo "<b>Zend_Auth_Adapterexception</b>";
                        break;

                    case 'Zend_Exception':

                        //$this->_request->setControllerName('expired');
                        //$this->_request->setActionName('expired');
                        //$this->_request->setDispatched(false);
                        //echo "Zend_Exception....";
                        break;

                    default:
                        //$this->_request->setActionName('expired');
                        //$this->_request->setDispatched(false);
                        echo "Default....";
                        break;
                }
                break;
        }
    }

    public function expiredAction() {
        
    }

    public function noactionAction() {
        
    }

    public function nocontrollerAction() {
        
    }

    public function dbstatementAction() {
        
    }

    public function permissionAction() {
        
    }

    public function viewexceptionAction() {
        
    }

}

?>