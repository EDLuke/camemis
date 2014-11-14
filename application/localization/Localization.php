<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 09.04.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
//require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/TextDBAccess.php';

ini_set('memory_limit', '50M');

$DB_TRANSLATION = TextDBAccess::getInstance();
$TRANSLATION_ENTRIES = $DB_TRANSLATION->allTexts();

$CHOOSE_FIELD = Zend_Registry::get('SYSTEM_LANGUAGE');

if ($TRANSLATION_ENTRIES)
    while (list($key, $row) = each($TRANSLATION_ENTRIES)) {
        $value = isset($row->$CHOOSE_FIELD) ? $row->$CHOOSE_FIELD : $row->CONST;
        $CONST = defined(trim($row->CONST)) ? constant(trim($row->CONST)) : trim($row->CONST);
        switch ($CHOOSE_FIELD) {
            case "THAI":
                if ($row->THAI) {
                    eval(define("" . $CONST . "", setBR(addslashes($value))) . ";");
                } else {
                    eval(define("" . $CONST . "", setBR(addslashes($row->ENGLISH))) . ";");
                }
                break;

            default:
                if ($CHOOSE_FIELD) {
                    if ($row->$CHOOSE_FIELD) {
                        eval(define("" . $CONST . "", setBR(addslashes($value))) . ";");
                    } else {
                        eval(define("" . $CONST . "", setBR(addslashes($row->ENGLISH))) . ";");
                    }
                }
                break;
        }
    }
?>