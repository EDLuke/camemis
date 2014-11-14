<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 20.08.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");

class myConfig {

    CONST SOCHEATA_KENNUNG = "893031F3-F789-2FFD-D211-3B3949FE347D";
    
    static function getACLValue($index) {
        $registry = Zend_Registry::getInstance();
        return isset($registry["" . $index . ""]) ? $registry["" . $index . ""] : false;
    }
}

?>