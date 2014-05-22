<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.02.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/UserDBAccess.php";

class UserAuth {

    static function userId() {
        $registry = self::getRegistry();
        if (isset($registry["USERID"])) {
            return $registry["USERID"];
        } else {
            exit("CAMEMIS: Access denied");
        }
    }

    static function getRegistry() {
        return Zend_Registry::getInstance();
    }

    static function getUserType() {

        $registry = self::getRegistry();
        $USER_OBJECT = isset($registry["USER"]) ? $registry["USER"] : "";

        $isProvider = isset($registry['IS_PROVIDER']) ? $registry['IS_PROVIDER'] : false;
        $USER_TYPE = isset($registry['USER_TYPE']) ? $registry['USER_TYPE'] : false;

        switch ($USER_TYPE) {
            case "INSTRUCTOR":
                $ROLE = "INSTRUCTOR";
                break;
            case "TEACHER":
                $ROLE = "TEACHER";
                break;
            case "STUDENT":
                $ROLE = "STUDENT";
                break;
            case "GUARDIAN":
                $ROLE = "GUARDIAN";
                break;
            default:
                if ($isProvider) {
                    $ROLE = "SUPERADMIN";
                } else {
                    if ($USER_OBJECT) {
                        switch ($USER_OBJECT->ROLE) {
                            case 1:
                                $ROLE = "SUPERADMIN";
                                break;
                            default:
                                $ROLE = "ADMIN";
                                break;
                        }
                    } else {
                        $ROLE = "SUPERADMIN";
                    }
                }
                break;
        }

        return $ROLE;
    }

    static function isSuperAdmin() {
        $registry = self::getRegistry();
        return isset($registry['IS_PROVIDER']) ? $registry['IS_PROVIDER'] : false;
    }

    static function isUserTypeAdmin() {
        switch (self::getUserType()) {
            case "SUPERADMIN":
            case "ADMIN":
                return true;
            default:
                return false;
        }
    }

    static function userIdFromSessionId() {
        $member = UserDBAccess::getInstance();
        $userObject = $member->getMemberBySessionId(addText(Zend_Registry::get('SESSIONID')));

        if ($userObject) {
            return $userObject->ID;
        } else {
            exit("CAMEMIS: Access denied");
        }
    }

    public static function mainidentify() {
        if (addText(Zend_Registry::get('SESSIONID'))) {
            $member = UserDBAccess::getInstance();
            $isRun = $member->checkMemberConstraints(addText(Zend_Registry::get('SESSIONID')));
            if ($isRun && self::userId() == self::userIdFromSessionId()) {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function identify() {
        if (addText(Zend_Registry::get('SESSIONID'))) {
            $member = UserDBAccess::getInstance();
            $isRun = $member->checkMemberConstraints(addText(Zend_Registry::get('SESSIONID')));
            if ($isRun && self::userId() == self::userIdFromSessionId()) {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function loginDialog() {
        //Your session has expired, please log in again.
        $js = "
            <script>
            window.location.href='/expired';
            </script>
            ";

        print$js;
    }

    static function getACLValue($index) {
        $data = Zend_Registry::get('ACL');
        return isset($data["" . $index . ""]) ? $data["" . $index . ""] : false;
    }

    static function actionMyArea($httpRequest, $permitValue) {

        $data = Zend_Registry::get('ACL');

        if ($permitValue) {

            if (isset($data["" . $permitValue . ""])) {
                if (!$data["" . $permitValue . ""]) {
                    $httpRequest->setControllerName('error');
                    $httpRequest->setActionName('permission');
                    $httpRequest->setDispatched(false);
                }
            } else {
                $httpRequest->setControllerName('error');
                $httpRequest->setActionName('permission');
                $httpRequest->setDispatched(false);
            }
        }
    }

    static function actionPermint($httpRequest, $permitValue) {

        $data = Zend_Registry::get('ACL');

        if ($permitValue) {
            switch (self::getUserType()) {
                case "TEACHER":
                    if (isset($data["T_" . $permitValue . ""])) {
                        if (!$data["T_" . $permitValue . ""]) {
                            $httpRequest->setControllerName('error');
                            $httpRequest->setActionName('permission');
                            $httpRequest->setDispatched(false);
                        }
                    } else {
                        $httpRequest->setControllerName('error');
                        $httpRequest->setActionName('permission');
                        $httpRequest->setDispatched(false);
                    }
                    break;
                case "INSTRUCTOR":
                    if (isset($data["I_" . $permitValue . ""])) {
                        if (!$data["I_" . $permitValue . ""]) {
                            $httpRequest->setControllerName('error');
                            $httpRequest->setActionName('permission');
                            $httpRequest->setDispatched(false);
                        }
                    } else {
                        $httpRequest->setControllerName('error');
                        $httpRequest->setActionName('permission');
                        $httpRequest->setDispatched(false);
                    }
                    break;
                default:
                    if (isset($data["" . $permitValue . ""])) {
                        if (!$data["" . $permitValue . ""]) {
                            $httpRequest->setControllerName('error');
                            $httpRequest->setActionName('permission');
                            $httpRequest->setDispatched(false);
                        }
                    } else {
                        $httpRequest->setControllerName('error');
                        $httpRequest->setActionName('permission');
                        $httpRequest->setDispatched(false);
                    }
                    break;
            }
        }
    }

    static function actionPermintGroup($httpRequest, $permitValue1, $permitValue2) {

        $data = Zend_Registry::get('ACL');

        if ($permitValue1 || $permitValue2) {

            if (isset($data["" . $permitValue1 . ""]) || isset($data["" . $permitValue2 . ""])) {
                if (!$data["" . $permitValue1 . ""] || !$data["" . $permitValue2 . ""]) {
                    $httpRequest->setControllerName('error');
                    $httpRequest->setActionName('permission');
                    $httpRequest->setDispatched(false);
                }
            } else {
                $httpRequest->setControllerName('error');
                $httpRequest->setActionName('permission');
                $httpRequest->setDispatched(false);
            }
        }
    }

    static function rolePermint($httpRequest, $permintGroup) {

        if ($permintGroup) {
            if (!in_array(Zend_Registry::get('USER_TYPE'), $permintGroup)) {
                $httpRequest->setControllerName('error');
                $httpRequest->setActionName('permission');
                $httpRequest->setDispatched(false);
            }
        }
    }

    static function rolePermintTutor($httpRequest) {

        $permintGroup = array(
            "INSTRUCTOR"
            , "TEACHER"
        );

        if ($permintGroup) {
            if (!in_array(Zend_Registry::get('USER_TYPE'), $permintGroup)) {
                $httpRequest->setControllerName('error');
                $httpRequest->setActionName('permission');
                $httpRequest->setDispatched(false);
            }
        }
    }

    static function rolePermintSystemANDTutor($httpRequest) {

        $permintGroup = array(
            "SYSTEM"
            , "INSTRUCTOR"
            , "TEACHER"
        );

        if ($permintGroup) {
            if (!in_array(Zend_Registry::get('USER_TYPE'), $permintGroup)) {
                $httpRequest->setControllerName('error');
                $httpRequest->setActionName('permission');
                $httpRequest->setDispatched(false);
            }
        }
    }

    static function getACLStudent($index) {

        $allACL = Zend_Registry::get('ACL');

        if (isset($allACL["STUDENT_SEARCH"])) {
            $result = isset($allACL["STUDENT_SEARCH_" . $index . ""]) ? $allACL["STUDENT_SEARCH_" . $index . ""] : 0;
        } elseif (isset($allACL["STUDENT_MODUL"])) {
            $result = isset($allACL["STUDENT_MODUL_" . $index . ""]) ? $allACL["STUDENT_MODUL_" . $index . ""] : 0;
        } elseif (isset($allACL["LIST_OF_STUDENTS"])) {
            $result = isset($allACL["LIST_OF_STUDENTS_" . $index . ""]) ? $allACL["LIST_OF_STUDENTS_" . $index . ""] : 0;
        } else {
            $result = 0;
        }

        return $result;
    }

    static function getACLTutorPermint($index) {
        $data = Zend_Registry::get('ACL');
        switch (self::getUserType()) {
            case "TEACHER":
                return isset($data["T_" . $index . ""]) ? $data["T_" . $index . ""] : false;
            case "INSTRUCTOR":
                return isset($data["I_" . $index . ""]) ? $data["I_" . $index . ""] : false;
            default:
                return isset($data["" . $index . ""]) ? $data["" . $index . ""] : false;
        }
    }

    static function isUserStudent() {
        return (Zend_Registry::get('USER_TYPE') == "STUDENT") ? true : false;
    }

    static function isVerifyTime() {

        $sessionObject = SessionAccess::getInstance();
        return $sessionObject->verifyTime(addText(Zend_Registry::get('SESSIONID')));
    }

    static function getAddedUserRole() {
        $registry = Zend_Registry::getInstance();
        $userObject = isset($registry['USER']) ? $registry['USER'] : false;
        return isset($userObject->ADDITIONAL_ROLE) ? $userObject->ADDITIONAL_ROLE : false;
    }

    static function printCLData() {
        print_r(Zend_Registry::get('ACL'));
    }

    ////////////////////////////////////////////////////////////////////////////
    //UserAuth....
    //Show display General or Training
    ////////////////////////////////////////////////////////////////////////////
    static function displayRoleGeneralEducation() {
        $result = false;
        switch (self::getUserType()) {
            case "SUPERADMIN":
            case "ADMIN":
                if (Zend_Registry::get('SCHOOL')->GENERAL_EDUCATION) {
                    if (UserAuth::getACLValue("ACADEMIC_GENERAL_EDUCATION")) {
                        $result = true;
                    } else {
                        $result = false;
                    }
                }
                break;
            case "TEACHER":
            case "INSTRUCTOR":
            case "STUDENT":
            case "GUARDIAN":
                if (Zend_Registry::get('SCHOOL')->GENERAL_EDUCATION) {
                    $result = true;
                }
                break;
        }

        return $result;
    }

    static function displayRoleTrainingEducation() {
        $result = false;
        switch (self::getUserType()) {
            case "SUPERADMIN":
            case "ADMIN":
            case "GUARDIAN":
                if (Zend_Registry::get('SCHOOL')->TRAINING_PROGRAMS) {
                    if (UserAuth::getACLValue("ACADEMIC_TRAINING_PROGRAMS")) {
                        $result = true;
                    } else {
                        $result = false;
                    }
                }

                break;
            case "TEACHER":
            case "INSTRUCTOR":
            case "STUDENT":
                if (Zend_Registry::get('SCHOOL')->TRAINING_PROGRAMS) {
                    $result = true;
                }
                break;
        }

        return $result;
    }

    static function displayCreditEducationSystem() {

        $result = false;
        if (Zend_Registry::get('SCHOOL')->GENERAL_EDUCATION) {
            if (Zend_Registry::get('SCHOOL')->CREDIT_EDUCATION_SYSTEM) {
                $result = true;
            }
        }

        return $result;
    }

    static function displayTraditionalEducationSystem() {

        $result = false;
        if (Zend_Registry::get('SCHOOL')->GENERAL_EDUCATION) {
            if (Zend_Registry::get('SCHOOL')->TRADITIONAL_EDUCATION_SYSTEM) {
                $result = true;
            }
        }

        return $result;
    }

    static function staffPermissionScroe($academicObject) {
        $result = false;

        if (self::getUserType() == "SUPERADMIN") {
            $result = true;
        } else {
            if (is_object($academicObject)) {
                $data = explode(",", $academicObject->STAFF_SCORE_PERMISSION);
                if (in_array(self::getUserId(), $data)) {
                    $result = true;
                }
            }
        }

        return $result;
    }

    public static function getUserLoginActionStatus() {
        $result = false;
        if (Zend_Registry::get('USER')->UMCPANL) {
            $result = true;
        }
        return $result;
    }

    //

    static function getMinPasswordLength() {
        return Zend_Registry::get('SCHOOL')->MINPL ? Zend_Registry::get('SCHOOL')->MINPL : 8;
    }

    //MAXIMUM_PASSWORD_AGE
    static function getMaxPasswordAge() {
        $result = false;
        if (Zend_Registry::get('SCHOOL')->MAXPA) {
            $CHANGE_PASSWORD_DATE = Zend_Registry::get('USER')->CHANGE_PASSWORD_DATE;
            if (findDaysFrom2Dates(false, $CHANGE_PASSWORD_DATE) > Zend_Registry::get('SCHOOL')->MAXPA) {
                $result = true;
            }
        }
    }

    static function isPasswordComplexityRequirements() {
        return Zend_Registry::get('SCHOOL')->PMCR ? Zend_Registry::get('SCHOOL')->PMCR : 0;
    }

    static function dbName() {
        return Zend_Registry::get('CHOOSE_DB_NAME');
    }

    static function dbHost() {
        return Zend_Registry::get('CHOOSE_DB_HOST');
    }

    static function dbUser() {
        return Zend_Registry::get('CHOOSE_DB_USER');
    }

    static function dbPassword() {
        return Zend_Registry::get('CHOOSE_DB_PWD');
    }

    static function systemLanguage() {
        return Zend_Registry::get('SYSTEM_LANGUAGE');
    }

    static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    static function tableObject() {
        return "Tables_in_" . self::dbName();
    }

    static function getUserPublicRoot() {
        $result = "";
        $explode = explode(".", $_SERVER['SERVER_NAME']);
        if (is_array($explode)) {
            $result = $explode[0];
        }
        return $result;
    }

    static function getFileBackUp() {
        $filename = UserAuth::getUserPublicRoot() . "_dump.sql.gz";
        $myFile = "../public/users/" . UserAuth::getUserPublicRoot() . "/database/" . $filename . "";
        if (file_exists($myFile)) {
            return $filename . " (" . showFileSize($myFile) . ")";
        } else {
            return "---";
        }
    }

    static function getUserId() {
        return Zend_Registry::get('USER')->ID;
    }

    static function myObject($Id) {
        return (Zend_Registry::get('USER')->ID == $Id) ? true : false;
    }

    public static function getMyFolder() {
        $folder = "";
        $explode = explode(".", $_SERVER['SERVER_NAME']);
        if (is_array($explode)) {
            $folder = "users/" . $explode[0] . "/attachment/";
        }

        return $folder;
    }

}

?>