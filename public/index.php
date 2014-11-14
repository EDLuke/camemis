<?php

/* * ******************************************************************
 * @Kaom Vibolrith
 * Am Stolheen 18
 * 55120 Mainz Mombach
 * Germany
 * Start CAMEMIS-Project
 * Start Date: Su, 24.07.2008
 * ***************************************************************** */

error_reporting(E_ALL | E_STRICT);

if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
    ini_set('display_errors', 'off');
else
    ini_set('display_errors', 'off');
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '../library');
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '../application');

require_once("Zend/Loader.php");
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Db.php';
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
require_once 'config/CAMEMISConfig.php';
require_once 'config/CAMEMISConfigBasic.php';
require_once 'config/CAMEMISModulConfig.php';
require_once 'config/CAMEMISLoginLanguage.php';
require_once 'config/CAMEMISRegistry.php';
require_once 'models/CamemisURLEncryption.php';
require_once 'include/Common.inc.php';

$tokenId = isset($_COOKIE['tokenId']) ? addText($_COOKIE['tokenId']) : '';

if (isset($_COOKIE['languageId'])) {
    $languageId = ($_COOKIE['languageId'] != 'undefined') ? addText($_COOKIE['languageId']) : 'ENGLISH';
} else {
    $languageId = 'ENGLISH';
}

$getLanguageId = isset($_GET['languageId']) ? addText($_GET['languageId']) : '';

Zend_Registry::set('SESSIONID', "");
Zend_Registry::set('SERVER_NAME', $_SERVER['SERVER_NAME']);

Zend_Loader::loadClass('Zend_Controller_Front');

CAMEMISConfig::setRegistryUserDB();

Zend_Registry::set('CAMEMIS_URL', CAMEMISConfigBasic::CAMEMIS_URL);

Zend_Registry::set('TITLE', "CAMEMIS Easy and Efficient Education Management");
Zend_Registry::set('EXTJS_VERSION', CAMEMISConfigBasic::EXTJS_VERSION);

$registry = Zend_Registry::getInstance();
if (isset($tokenId)) {

    if (!isset($registry["MODUL_API"])) {
        echo "<h1>Please register " . $_SERVER['SERVER_NAME'] . "<h1>";
        exit;
    }

    switch (Zend_Registry::get('MODUL_API')) {
        case "dfe34ef0f0b812ea32d12866dbe9e3cb":

            require_once 'models/app_admin/AdminSessionAccess.php';

            $SESSION_OBJECT = new AdminSessionDBAccess();
            $SESSION_OBJECT->setSessionId($tokenId);
            $SESSION = $SESSION_OBJECT->getSession();

            if ($SESSION) {
                Zend_Registry::set('SESSIONID', $SESSION->ID);
                Zend_Registry::set('SESSION', $SESSION);
            }

            break;
        case "dfe34ef0f0b812ea32d02866dbe9e3cb":

            Zend_Registry::set('MODUL_API_PATH', "app_school");

            if ($languageId) {

                if ($getLanguageId) {
                    Zend_Registry::set('SYSTEM_LANGUAGE', $getLanguageId);
                } else {
                    Zend_Registry::set('SYSTEM_LANGUAGE', $languageId);
                }
            } else {
                Zend_Registry::set('SYSTEM_LANGUAGE', CAMEMISLoginLanguage::getLoginLanguage($tokenId));
            }

            require_once 'models/app_school/SessionAccess.php';
            require_once 'models/app_school/UserDBAccess.php';
            require_once 'models/app_school/user/UserRoleDBAccess.php';
            require_once 'models/app_school/school/SchoolDBAccess.php';

            $sessionId = $tokenId;
            $SESSION_DB = SessionAccess::getInstance();
            $SCHOOL_DB = SchoolDBAccess::getInstance();
            $SESSION_OBJECT = $SESSION_DB->dataSession($sessionId);

            if ($SESSION_OBJECT) {

                Zend_Registry::set('SESSION_OBJECT', $SESSION_DB);
                $MEMBER_DB = UserDBAccess::getInstance();
                $MEMBER_ROLE_DB = UserRoleDBAccess::getInstance();
                $SCHOOL_DB = SchoolDBAccess::getInstance();
                $MEMBER_OBJECT = $MEMBER_DB->getMemberBySessionId($sessionId);
                $SCHOOL_OBJECT = $SCHOOL_DB->getSchool();

                SessionAccess::makeSchoolRegistry($SCHOOL_OBJECT, $SESSION_OBJECT);
                SessionAccess::makeModulRegistry();

                if (is_numeric($MEMBER_OBJECT->ROLE)) {
                    UserRoleDBAccess::setACLData($MEMBER_OBJECT->ROLE);
                    $USER_ROLE_OBJECT = $MEMBER_ROLE_DB->getObjectUserRole($MEMBER_OBJECT->ROLE);
                    $ACL_DATA = UserRoleDBAccess::getACLData($MEMBER_OBJECT->ROLE);
                } else {
                    switch ($MEMBER_OBJECT->ROLE) {
                        case "STUDENT":
                            Zend_Registry::set('USER_TYPE', 'STUDENT');
                            $USER_ROLE_OBJECT = $MEMBER_ROLE_DB->getObjectUserRole(4);
                            $ACL_DATA = UserRoleDBAccess::getACLData(4);
                            break;
                        case "GUARDIAN":
                            Zend_Registry::set('USER_TYPE', 'GUARDIAN');
                            $USER_ROLE_OBJECT = $MEMBER_ROLE_DB->getObjectUserRole(5);
                            $ACL_DATA = UserRoleDBAccess::getACLData(5);
                            break;
                    }
                }

                SessionAccess::makeUserRegistry($MEMBER_OBJECT, array(), $USER_ROLE_OBJECT, $SESSION_OBJECT);
                Zend_Registry::set('ACL', $ACL_DATA);
            }

            break;
        case "dfe34ef0f0b812ea32d92866dbe9e3cb":

            Zend_Registry::set('MODUL_API_PATH', "app_university");
            Zend_Registry::set('SYSTEM_LANGUAGE', CAMEMISLoginLanguage::getLoginLanguage($tokenId));

            if ($languageId) {

                if ($getLanguageId) {
                    Zend_Registry::set('SYSTEM_LANGUAGE', $getLanguageId);
                } else {
                    Zend_Registry::set('SYSTEM_LANGUAGE', $languageId);
                }
            } else {
                Zend_Registry::set('SYSTEM_LANGUAGE', CAMEMISLoginLanguage::getLoginLanguage($tokenId));
            }

            require_once 'models/app_university/SessionAccess.php';
            require_once 'models/app_university/UserDBAccess.php';
            require_once 'models/app_university/user/UserRoleDBAccess.php';
            require_once 'models/app_university/school/SchoolDBAccess.php';

            $sessionId = $tokenId;
            $SESSION_DB = SessionAccess::getInstance();
            $SCHOOL_DB = SchoolDBAccess::getInstance();
            $SESSION_OBJECT = $SESSION_DB->dataSession($sessionId);

            if ($SESSION_OBJECT) {

                Zend_Registry::set('SESSION_OBJECT', $SESSION_DB);
                $MEMBER_DB = UserDBAccess::getInstance();
                $MEMBER_ROLE_DB = UserRoleDBAccess::getInstance();
                $SCHOOL_DB = SchoolDBAccess::getInstance();
                $MEMBER_OBJECT = $MEMBER_DB->getMemberBySessionId($sessionId);
                $SCHOOL_OBJECT = $SCHOOL_DB->getSchool();

                SessionAccess::makeSchoolRegistry($SCHOOL_OBJECT, $SESSION_OBJECT);
                SessionAccess::makeModulRegistry();

                if (is_numeric($MEMBER_OBJECT->ROLE)) {
                    UserRoleDBAccess::setACLData($MEMBER_OBJECT->ROLE);
                    $USER_ROLE_OBJECT = $MEMBER_ROLE_DB->getObjectUserRole($MEMBER_OBJECT->ROLE);
                    $ACL_DATA = UserRoleDBAccess::getACLData($MEMBER_OBJECT->ROLE);
                } else {
                    switch ($MEMBER_OBJECT->ROLE) {
                        case "STUDENT":
                            Zend_Registry::set('USER_TYPE', 'STUDENT');
                            $USER_ROLE_OBJECT = $MEMBER_ROLE_DB->getObjectUserRole(4);
                            $ACL_DATA = UserRoleDBAccess::getACLData(4);
                            break;
                        case "GUARDIAN":
                            Zend_Registry::set('USER_TYPE', 'GUARDIAN');
                            $USER_ROLE_OBJECT = $MEMBER_ROLE_DB->getObjectUserRole(5);
                            $ACL_DATA = UserRoleDBAccess::getACLData(5);
                            break;
                    }
                }

                SessionAccess::makeUserRegistry($MEMBER_OBJECT, array(), $USER_ROLE_OBJECT, $SESSION_OBJECT);
                Zend_Registry::set('ACL', $ACL_DATA);
            }
            break;
        case "KINDERGARTEN":

            break;
    }
}

$frontController = Zend_Controller_Front::getInstance();

switch (Zend_Registry::get('MODUL_API')) {
    case "dfe34ef0f0b812ea32d02866dbe9e3cb":
        if (strpos(ltrim($_SERVER["REQUEST_URI"], '/'), 'elearning') !== FALSE) {
            $application_type = '../application/modules/app_elearning/controllers';
        } else {
            $application_type = '../application/modules/app_school/controllers';
        }
        break;
    case "dfe34ef0f0b812ea32d92866dbe9e3cb":
        if (strpos(ltrim($_SERVER["REQUEST_URI"], '/'), 'elearning') !== FALSE) {
            $application_type = '../application/modules/app_elearning/controllers';
        } else {
            $application_type = '../application/modules/app_university/controllers';
        }
        break;
    case "dfe34ef0f0b812ea32d12866dbe9c3cb":
        $application_type = '../application/modules/app_kindergarten/controllers';
        break;
    case "dfe34ef0f0b812ea32d12866dbe9e3cb":
        $application_type = '../application/modules/app_admin/controllers';
        break;
    default: $application_type = '../application/modules/app_school/controllers';
        break;
}

$frontController->setControllerDirectory(array(
    'default' => $application_type
));
$frontController->dispatch();
?>