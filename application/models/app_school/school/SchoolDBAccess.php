<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 17.10.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SchoolDBAccess {

    protected $data = array();
    protected $out = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function workingDays() {

        return array(
            'MO' => 'MO'
            , 'TU' => 'TU'
            , 'WE' => 'WE'
            , 'TH' => 'TH'
            , 'FR' => 'FR'
            , 'SA' => 'SA'
            , 'SU' => 'SU'
        );
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function getSchoolData() {

        $result = self::getSchool();

        if ($result) {
            $data["ID"] = $result->ID;
            $data["SCHOOL_TERM"] = $result->SCHOOL_TERM;
            $data["CODE"] = $result->CODE;
            $data["STATUS"] = $result->STATUS;
            $data["SKIN"] = $result->SKIN;
            $data["NAME"] = setShowText($result->NAME);
            $data["EMAIL"] = setShowText($result->EMAIL);
            $data["PHONE"] = setShowText($result->PHONE);
            $data["FAX"] = setShowText($result->FAX);
            $data["MOBIL"] = setShowText($result->MOBIL);
            $data["ADDRESS"] = setShowText($result->ADDRESS);
            $data["DEAN"] = setShowText($result->DEAN);
            $data["CONT_PERSON"] = setShowText($result->CONT_PERSON);
            $data["WEBSITE"] = setShowText($result->WEBSITE);
            $data["FACILITY"] = setShowText($result->FACILITY);
            $data["EMERGENCY_CALL"] = setShowText($result->EMERGENCY_CALL);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["DISPLAY_POSITION_LASTNAME"] = $result->DISPLAY_POSITION_LASTNAME;
            $data["RELEASE_STATUS"] = $result->STATUS ? ENABLED : DISABLED;
            $data["MODUL_KEY"] = $result->MODUL_KEY;
            $data["DECIMAL_PLACES"] = $result->DECIMAL_PLACES ? $result->DECIMAL_PLACES : 1;
            $data["SYSTEM_DATE_FORMAT"] = $result->SYSTEM_DATE_FORMAT;
            $data["CAMEMIS_DATE_FORMAT"] = $result->CAMEMIS_DATE_FORMAT;

            $data["SET_DEFAULT_PASSWORD"] = $result->SET_DEFAULT_PASSWORD;
            $data["CURRENCY"] = $result->CURRENCY;
            $data["SHORT"] = $result->SHORT;
            $data["SUBJECT_DISPLAY"] = $result->SUBJECT_DISPLAY;
            $data["ROOM_DISPLAY"] = $result->ROOM_DISPLAY;
            $data["SMS_DISPLAY"] = $result->SMS_DISPLAY;
            $data["SORT_DISPLAY"] = $result->SORT_DISPLAY;
            $data["SCHOOL_TIMEZONE"] = $result->SCHOOL_TIMEZONE;

            //@Math Man
            $data["ACCOUNT_CREATE_SUBJECT"] = $result->ACCOUNT_CREATE_SUBJECT;
            $data["ACCOUNT_CREATE_NOTIFICATION"] = $result->ACCOUNT_CREATE_NOTIFICATION;
            $data["FORGET_PASSWORD_SUBJECT"] = $result->FORGET_PASSWORD_SUBJECT;
            $data["FORGET_PASSWORD_NOTIFICATION"] = $result->FORGET_PASSWORD_NOTIFICATION;
            $data["SALUTATION_EMAIL"] = $result->SALUTATION_EMAIL;
            $data["SIGNATURE_EMAIL"] = $result->SIGNATURE_EMAIL;
            ///////////////////////
            $data["PMCR"] = $result->PMCR ? true : false;
            $data["ALD"] = $result->ALD;
            $data["ALL"] = $result->ALL;
            $data["MAXPA"] = $result->MAXPA;
            $data["MINPA"] = $result->MINPA;
            $data["MINPL"] = $result->MINPL;

            $data["EMBEDDED_URL"] = "";
            $data["EMBEDDED_URL"] .= "<a href=\"http://" . $_SERVER['SERVER_NAME'] . "?key=" . Zend_Registry::get('SCHOOL')->ID . "\" target=\"_blank\">";
            $data["EMBEDDED_URL"] .= "\n<img src=\"http://" . $_SERVER['SERVER_NAME'] . "/public/images/camemis_small_logo.png\" border=\"0\" width=\"50\" height=\"50\" />\n";
            $data["EMBEDDED_URL"] .= "</a>";

            $data["SCHOOL_LETTER_HEADER"] = $result->SCHOOL_LETTER_HEADER;
            $data["SCHOOL_LETTER_FOOTER"] = $result->SCHOOL_LETTER_FOOTER;

            $data["GENERAL_EDUCATION"] = $result->GENERAL_EDUCATION ? true : false;
            $data["TRAINING_PROGRAMS"] = $result->TRAINING_PROGRAMS ? true : false;

            $data["TRADITIONAL_EDUCATION_SYSTEM"] = $result->TRADITIONAL_EDUCATION_SYSTEM ? true : false;
            $data["CREDIT_EDUCATION_SYSTEM"] = $result->CREDIT_EDUCATION_SYSTEM ? true : false;

            $data["ACTIVATE_SMS_NOTIFICATION_AUTO"] = $result->ACTIVATE_SMS_NOTIFICATION_AUTO ? true : false;
            $data["MULTI_BRANCH_OFFICE"] = $result->MULTI_BRANCH_OFFICE ? true : false;

            $data["CURRENT_ACADEMICDATE_ID"] = $this->getCurrentAcademicDate() ? $this->getCurrentAcademicDate()->ID : 0;
            $data["CURRENT_ACADEMICDATE_NAME"] = $this->getCurrentAcademicDate() ? $this->getCurrentAcademicDate()->NAME : "---";

            $data["CURRENT_ACADEMICDATE"] = $this->getCurrentAcademicDate();

            $data["ENABLE_ITEMS_BY_DEFAULT"] = $result->ENABLE_ITEMS_BY_DEFAULT ? true : false;
            $data["GRADE_WITH_COEFFICIENT"] = $result->GRADE_WITH_COEFFICIENT ? true : false;

            //Heath MBI Standard
            $data["HEALTH_BMI_STANDARD"] = $result->HEALTH_BMI_STANDARD;
            $USED_WORKINGDAY_DATA = explode(',', $result->WORKING_DAYS);

            foreach (self::workingDays() as $value) {
                if (in_array($value, $USED_WORKINGDAY_DATA)) {
                    $data[$value] = true;
                } else {
                    $data[$value] = false;
                }
            }

            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
        }

        return $data;
    }

    public static function getSchool() {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_school";
        $SQL .= " LIMIT 0,1";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonLoadSchool() {
        $result = self::getSchool();

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getSchoolData()
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function updateSchool($params) {

        $SAVEDATA = array();

        if (isset($params["DISPLAY_POSITION_LASTNAME"]))
            $SAVEDATA['DISPLAY_POSITION_LASTNAME'] = addText($params["DISPLAY_POSITION_LASTNAME"]);

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SCHOOL_TERM"]))
            $SAVEDATA['SCHOOL_TERM'] = addText($params["SCHOOL_TERM"]);

        if (isset($params["PHONE"]))
            $SAVEDATA['PHONE'] = addText($params["PHONE"]);

        if (isset($params["FAX"]))
            $SAVEDATA['FAX'] = addText($params["FAX"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        if (isset($params["SUBJECT_DISPLAY"]))
            $SAVEDATA['SUBJECT_DISPLAY'] = addText($params["SUBJECT_DISPLAY"]);

        if (isset($params["ROOM_DISPLAY"]))
            $SAVEDATA['ROOM_DISPLAY'] = addText($params["ROOM_DISPLAY"]);

        if (isset($params["SMS_DISPLAY"]))
            $SAVEDATA['SMS_DISPLAY'] = addText($params["SMS_DISPLAY"]);

        if (isset($params["SORT_DISPLAY"]))
            $SAVEDATA['SORT_DISPLAY'] = addText($params["SORT_DISPLAY"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["ADDRESS"]))
            $SAVEDATA['ADDRESS'] = addText($params["ADDRESS"]);

        if (isset($params["EMERGENCY_CALL"]))
            $SAVEDATA['EMERGENCY_CALL'] = $params["EMERGENCY_CALL"];

        if (isset($params["DEAN"]))
            $SAVEDATA['DEAN'] = addText($params["DEAN"]);

        if (isset($params["HEALTH_BMI_STANDARD"]))
            $SAVEDATA['HEALTH_BMI_STANDARD'] = addText($params["HEALTH_BMI_STANDARD"]);

        if (isset($params["SYSTEM_LANGUAGE"]))
            $SAVEDATA['SYSTEM_LANGUAGE'] = addText($params["SYSTEM_LANGUAGE"]);

        if (isset($params["WEBSITE"]))
            $SAVEDATA['WEBSITE'] = addText($params["WEBSITE"]);

        if (isset($params["CONT_PERSON"]))
            $SAVEDATA['CONT_PERSON'] = addText($params["CONT_PERSON"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["MODUL_KEY"]))
            $SAVEDATA['MODUL_KEY'] = addText($params["MODUL_KEY"]);

        if (isset($params["DECIMAL_PLACES"]))
            $SAVEDATA['DECIMAL_PLACES'] = addText($params["DECIMAL_PLACES"]);

        if (isset($params["SYSTEM_DATE_FORMAT"]))
            $SAVEDATA['SYSTEM_DATE_FORMAT'] = addText($params["SYSTEM_DATE_FORMAT"]);

        if (isset($params["CAMEMIS_DATE_FORMAT"]))
            $SAVEDATA['CAMEMIS_DATE_FORMAT'] = addText($params["CAMEMIS_DATE_FORMAT"]);

        if (isset($params["GENERAL_EDUCATION"])) {
            $SAVEDATA['GENERAL_EDUCATION'] = isset($params["GENERAL_EDUCATION"]) ? 1 : 0;
        }

        if (isset($params["TRAINING_PROGRAMS"])) {
            $SAVEDATA['TRAINING_PROGRAMS'] = isset($params["TRAINING_PROGRAMS"]) ? 1 : 0;
        }

        if (isset($params["TRADITIONAL_EDUCATION_SYSTEM"])) {
            $SAVEDATA['TRADITIONAL_EDUCATION_SYSTEM'] = isset($params["TRADITIONAL_EDUCATION_SYSTEM"]) ? 1 : 0;
        }

        if (isset($params["CREDIT_EDUCATION_SYSTEM"])) {
            $SAVEDATA['CREDIT_EDUCATION_SYSTEM'] = isset($params["CREDIT_EDUCATION_SYSTEM"]) ? 1 : 0;
        }

        if (isset($params["SCHOOL_LETTER_FOOTER"]))
            $SAVEDATA['SCHOOL_LETTER_FOOTER'] = addText($params["SCHOOL_LETTER_FOOTER"]);

        if (isset($params["SCHOOL_LETTER_HEADER"]))
            $SAVEDATA['SCHOOL_LETTER_HEADER'] = addText($params["SCHOOL_LETTER_HEADER"]);

        if (isset($params["CURRENCY"]))
            $SAVEDATA['CURRENCY'] = addText($params["CURRENCY"]);

        if (isset($params["SCHOOL_TIMEZONE"]))
            $SAVEDATA['SCHOOL_TIMEZONE'] = addText($params["SCHOOL_TIMEZONE"]);

        $SAVEDATA['PMCR'] = isset($params["PMCR"]) ? 1 : 0;
        if (isset($params["ALD"]))
            $SAVEDATA['ALD'] = addText($params["ALD"]);
        if (isset($params["ALL"]))
            $SAVEDATA['ALL'] = addText($params["ALL"]);
        if (isset($params["MAXPA"]))
            $SAVEDATA['MAXPA'] = addText($params["MAXPA"]);
        if (isset($params["MINPA"]))
            $SAVEDATA['MINPA'] = addText($params["MINPA"]);
        if (isset($params["MINPL"]))
            $SAVEDATA['MINPL'] = addText($params["MINPL"]);

        //@Math Man
        if (isset($params["ACCOUNT_CREATE_SUBJECT"]))
            $SAVEDATA['ACCOUNT_CREATE_SUBJECT'] = addText($params["ACCOUNT_CREATE_SUBJECT"]);

        if (isset($params["ACCOUNT_CREATE_NOTIFICATION"]))
            $SAVEDATA['ACCOUNT_CREATE_NOTIFICATION'] = addText($params["ACCOUNT_CREATE_NOTIFICATION"]);

        if (isset($params["FORGET_PASSWORD_SUBJECT"]))
            $SAVEDATA['FORGET_PASSWORD_SUBJECT'] = addText($params["FORGET_PASSWORD_SUBJECT"]);

        if (isset($params["FORGET_PASSWORD_NOTIFICATION"]))
            $SAVEDATA['FORGET_PASSWORD_NOTIFICATION'] = addText($params["FORGET_PASSWORD_NOTIFICATION"]);

        if (isset($params["SALUTATION_EMAIL"]))
            $SAVEDATA['SALUTATION_EMAIL'] = addText($params["SALUTATION_EMAIL"]);

        if (isset($params["SIGNATURE_EMAIL"]))
            $SAVEDATA['SIGNATURE_EMAIL'] = addText($params["SIGNATURE_EMAIL"]);
        ////////////////////////////

        $SAVEDATA['ACTIVATE_SMS_NOTIFICATION_AUTO'] = isset($params["ACTIVATE_SMS_NOTIFICATION_AUTO"]) ? 1 : 0;
        $SAVEDATA['MULTI_BRANCH_OFFICE'] = isset($params["MULTI_BRANCH_OFFICE"]) ? 1 : 0;

        $SAVEDATA['ENABLE_ITEMS_BY_DEFAULT'] = isset($params["ENABLE_ITEMS_BY_DEFAULT"]) ? 1 : 0;
        $SAVEDATA['GRADE_WITH_COEFFICIENT'] = isset($params["GRADE_WITH_COEFFICIENT"]) ? 1 : 0;

        $DATA_WORKINGDAY = array();
        foreach (self::workingDays() as $value) {
            if (isset($params[$value])) {
                $DATA_WORKINGDAY[$value] = $value;
            }
        }

        if ($DATA_WORKINGDAY)
            $SAVEDATA['WORKING_DAYS'] = implode(',', $DATA_WORKINGDAY);

        $SAVEDATA['SET_DEFAULT_PASSWORD'] = isset($params["SET_DEFAULT_PASSWORD"]) ? 1 : 0;
        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        $WHERE = self::dbAccess()->quoteInto("ID = ?", Zend_Registry::get('SCHOOL')->ID);
        self::dbAccess()->update('t_school', $SAVEDATA, $WHERE);

        return array("success" => true);
    }

    protected function getCurrentAcademicDate() {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE";
        $SQL .= " NOW()<=END";
        $SQL .= " ORDER BY  YEAR_LEVEL ASC LIMIT 0 ,1";
        //echo $SQL;
        return self::dbAccess()->fetchRow($SQL);
    }

    public function globalSearch($params) {

        $code = isset($params["code"]) ? strtoupper($params["code"]) : '';

        $UTILES = Utiles::getInstance();
        $DB_GRADE = AcademicDBAccess::getInstance();
        $DB_STAFF = StaffDBAccess::getInstance();
        $DB_STUDENT = StudentDBAccess::getInstance();

        $firstCheck = $DB_GRADE->findGradeFromCodeId($code);
        $secondCheck = $DB_STAFF->findStaffFromCodeId($code);
        $thirdCheck = $DB_STUDENT->findStudentFromCodeId($code);

        $title = null;
        $targetUrl = null;

        if ($firstCheck) {
            $Id = $firstCheck->ID;
            switch ($firstCheck->OBJECT_TYPE) {
                case "CAMPUS":
                    $title = $firstCheck->TITLE;
                    $targetUrl = $UTILES->buildURL('academic/editcampus', array("objectId" => $Id));
                    break;
                case "GRADE":
                    $title = $firstCheck->TITLE;
                    $targetUrl = $UTILES->buildURL('academic/editgrade', array("objectId" => $Id));
                    break;
                case "SCHOOLYEAR":
                    $title = $firstCheck->TITLE;
                    $targetUrl = $UTILES->buildURL('academic/editschoolyear', array("objectId" => $Id));
                    break;
                case "CLASS":
                    $title = $firstCheck->TITLE;
                    $targetUrl = $UTILES->buildURL('academic/editclass', array("objectId" => $Id));
                    break;
            }
        }
        if ($secondCheck) {
            $Id = $secondCheck->ID;
            $title = $secondCheck->LASTNAME . " " . $secondCheck->FIRSTNAME;
            $targetUrl = $UTILES->buildURL('staff/showitem', array("objectId" => $Id));
        }
        if ($thirdCheck) {
            $Id = $thirdCheck->ID;
            $title = $thirdCheck->LASTNAME . " " . $thirdCheck->FIRSTNAME;
            $targetUrl = $UTILES->buildURL('student/showitem', array("objectId" => $Id));
        }

        return array("success" => true, "title" => $title, "url" => $targetUrl);
    }

    public static function getSchoolWorkingDays() {

        $facette = self::getSchool();
        if ($facette) {
            return explode(",", $facette->WORKING_DAYS);
        } else {
            return array();
        }
    }

    public function jsonLogout() {

        $SAVEDATA = array();
        $SAVEDATA["DATE_END"] = getCurrentDBDateTime();
        $loginObject = SessionAccess::dataSession(Zend_Registry::get('SESSIONID'));

        if ($loginObject) {
            $WHERE = self::dbAccess()->quoteInto("SESSION_ID = ?", Zend_Registry::get('SESSIONID'));
            self::dbAccess()->update('t_logininfo', $SAVEDATA, $WHERE);
            self::dbAccess()->delete('t_session', array("ID='" . Zend_Registry::get('SESSIONID') . "'"));
        }
    }

    public static function displayFullName($firstName, $lastName) {

        if (Zend_Registry::get('SCHOOL')->DISPLAY_POSITION_LASTNAME) {
            $result = $firstName . " " . $lastName;
        } else {
            $result = $lastName . " " . $firstName;
        }
        return $result;
    }

    public static function displayPersonNameInGrid() {
        return Zend_Registry::get('SCHOOL')->DISPLAY_POSITION_LASTNAME;
    }

}

?>