<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.02.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////          
require_once("Zend/Loader.php");
require_once 'models/app_university/BuildData.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/training/StudentTrainingDBAccess.php';
require_once 'models/app_university/student/StudentSearchDBAccess.php';
require_once 'models/app_university/student/StudentAcademicDBAccess.php';
require_once 'models/app_university/student/StudentStatusDBAccess.php';
require_once 'models/app_university/finance/FeeDBAccess.php'; //@veasna
require_once 'models/app_university/finance/StudentFeeDBAccess.php'; //@veasna
require_once 'models/app_university/LocationDBAccess.php';
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class StudentDBAccess {

    CONST TABLE_STUDENT = "t_student";

    public $data = array();
    public $dataforjson = array();
    public $entries = array();
    public $savedata = array();
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

    public function getStudentDataFromId($Id) {

        $data = array();

        $result = self::findStudentFromId($Id);

        if ($result) {

            //self::mappingLogin($Id);

            $studentAcademicObject = StudentAcademicDBAccess::getCurrentStudentAcademic($result->ID);

            $data["BOX_FIRSTNAME"] = $result->FIRSTNAME;
            $data["BOX_LASTNAME"] = $result->LASTNAME;

            $data["ID"] = $result->ID;
            $data["CODE"] = $result->CODE;
            $data["STUDENT_SCHOOL_ID"] = $result->STUDENT_SCHOOL_ID;
            $data["STATUS"] = $result->STATUS;
            $data["STUDENT_STATUS"] = setShowText($result->STATUS_ID);
            $data["FIRSTNAME"] = setShowText($result->FIRSTNAME);
            $data["LASTNAME"] = setShowText($result->LASTNAME);
            $data["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
            $data["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
            $data["GENDER"] = $result->GENDER;
            $data["FULLNAME_FATHER"] = setShowText($result->FULLNAME_FATHER);
            $data["FULLNAME_MOTHER"] = setShowText($result->FULLNAME_MOTHER);

            $data["GENDER_NAME"] = getGenderName($result->GENDER);
            $data["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);
            $data["BIRTH_PLACE"] = setShowText($result->BIRTH_PLACE);
            $data["RELEASE_STATUS"] = $result->STATUS ? ENABLED : DISABLED;
            $data["LOGINNAME"] = $result->LOGINNAME;
            $data["SOURCE_IMAGE"] = $result->SOURCE_IMAGE;
            $data["SYSTEM_LANGUAGE"] = $result->SYSTEM_LANGUAGE;

            $data["RELIGION"] = $result->RELIGION;
            $data["ETHNIC"] = $result->ETHNIC;

            $data["CURRENT_SCHOOLYEAR"] = $studentAcademicObject->SCHOOLYEAR;
            $data["CURRENT_ACADEMIC"] = $studentAcademicObject->ACADEMIC;

            $data["IS_PASSWORD"] = $result->PASSWORD ? true : false;
            $data["PASSWORD"] = "**********";
            $data["PASSWORD_REPEAT"] = "**********";
            $data["UMCPANL"] = $result->UMCPANL ? 1 : 0;
            $data["UCNCP"] = $result->UCNCP ? 1 : 0;

            $data["ADDRESS"] = setShowText($result->ADDRESS);
            $data["EMAIL"] = setShowText($result->EMAIL);
            $data["PHONE"] = setShowText($result->PHONE);
            $data["PHONE_COUNTRY_CODE"] = setShowText($result->PHONE_COUNTRY_CODE);

            $data["COUNTRY_PROVINCE"] = setShowText($result->COUNTRY_PROVINCE);
            $data["COUNTRY"] = setShowText($result->COUNTRY);
            $data["POSTCODE_ZIPCODE"] = setShowText($result->POSTCODE_ZIPCODE);
            $data["TOWN_CITY"] = setShowText($result->TOWN_CITY);

            $data["SMS_SERVICES"] = $result->SMS_SERVICES;
            $data["MOBIL_PHONE"] = setShowText($result->MOBIL_PHONE);

            $data["ACADEMIC_TYPE"] = $result->ACADEMIC_TYPE;
            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);

            $data["PERSONAL_DESCRIPTION"] = setShowText($result->PERSONAL_DESCRIPTION);
        }

        return $data;
    }

    public static function queryAllStudents($params, $groupby = false, $orderby = false) {

        return StudentSearchDBAccess::queryAllStudents($params, $groupby, $orderby);
    }

    /**
     * Object student by StudentId...
     */
    public static function findStudentFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), array('*'));
        $SQL->joinLeft(array('B' => 't_student_status'), 'A.ID=B.STUDENT', array('B.STATUS_ID'));

        $SQL->where("A.ID = ?", $Id);
        //error_log($SQL->__toString());       
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public function findStudentFromCodeId($codeId) {

        $SQL = self::dbAccess()->select()
                ->from("t_student", array('*'))
                ->where("CODE = '" . strtoupper(trim($codeId)) . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    /**
     * JSON: Student by StudentId....
     */
    public function loadStudentFromId($Id) {
        $result = self::findStudentFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getStudentDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    /**
     * Upadete student by StudentId...
     */
    public function updateStudent($params) {

        $SAVEDATA = array();

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if (isset($params["CODE"])) {
            $SAVEDATA['CODE'] = addText($params["CODE"]);
        }

        if (isset($params["STUDENT_SCHOOL_ID"]))
            $SAVEDATA['STUDENT_SCHOOL_ID'] = addText($params["STUDENT_SCHOOL_ID"]);

        if (isset($params["FIRSTNAME"])) {
            $SAVEDATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);
        }
        if (isset($params["LASTNAME"]))
            $SAVEDATA['LASTNAME'] = addText($params["LASTNAME"]);

        if (isset($params["FIRSTNAME_LATIN"]))
            $SAVEDATA['FIRSTNAME_LATIN'] = addText($params["FIRSTNAME_LATIN"]);

        if (isset($params["LASTNAME_LATIN"]))
            $SAVEDATA['LASTNAME_LATIN'] = addText($params["LASTNAME_LATIN"]);

        if (isset($params["FULLNAME_FATHER"]))
            $SAVEDATA['FULLNAME_FATHER'] = addText($params["FULLNAME_FATHER"]);

        if (isset($params["FULLNAME_MOTHER"]))
            $SAVEDATA['FULLNAME_MOTHER'] = addText($params["FULLNAME_MOTHER"]);

        if (isset($params["SYSTEM_LANGUAGE"]))
            $SAVEDATA['SYSTEM_LANGUAGE'] = addText($params["SYSTEM_LANGUAGE"]);

        if (isset($params["GENDER"]))
            $SAVEDATA['GENDER'] = addText($params["GENDER"]);

        if (isset($params["DATE_BIRTH"]))
            $SAVEDATA['DATE_BIRTH'] = setDate2DB($params["DATE_BIRTH"]);

        if (isset($params["BIRTH_PLACE"]))
            $SAVEDATA['BIRTH_PLACE'] = addText($params["BIRTH_PLACE"]);

        if (isset($params["ADDRESS"]))
            $SAVEDATA['ADDRESS'] = addText($params["ADDRESS"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["PHONE"]))
            $SAVEDATA['PHONE'] = addText($params["PHONE"]);

        if (isset($params["PHONE_COUNTRY_CODE"]))
            $SAVEDATA['PHONE_COUNTRY_CODE'] = addText($params["PHONE_COUNTRY_CODE"]);

        if (isset($params["TOWN_CITY"]))
            $SAVEDATA['TOWN_CITY'] = addText($params["TOWN_CITY"]);

        if (isset($params["COUNTRY"]))
            $SAVEDATA['COUNTRY'] = addText($params["COUNTRY"]);

        if (isset($params["POSTCODE_ZIPCODE"]))
            $SAVEDATA['POSTCODE_ZIPCODE'] = addText($params["POSTCODE_ZIPCODE"]);

        if (isset($params["COUNTRY_PROVINCE"]))
            $SAVEDATA['COUNTRY_PROVINCE'] = addText($params["COUNTRY_PROVINCE"]);

        if (isset($params["FIRSTNAME"]) && isset($params["LASTNAME"])) {
            $SAVEDATA['NAME'] = addText($params["LASTNAME"]) . ", " . addText($params["FIRSTNAME"]);
        }

        if (isset($params["RELIGION"]))
            $SAVEDATA['RELIGION'] = addText($params["RELIGION"]);

        if (isset($params["ETHNIC"]))
            $SAVEDATA['ETHNIC'] = addText($params["ETHNIC"]);

        if (isset($params["NATIONALITY"])) {
            $SAVEDATA['NATIONALITY'] = addText($params["NATIONALITY"]);
        }

        if (isset($params["SMS_SERVICES"]))
            $SAVEDATA['SMS_SERVICES'] = addText($params["SMS_SERVICES"]);

        if (isset($params["MOBIL_PHONE"]))
            $SAVEDATA['MOBIL_PHONE'] = addText($params["MOBIL_PHONE"]);

        if (isset($params["BIRTH_DAY"]))
            $SAVEDATA['BIRTH_DAY'] = addText($params["BIRTH_DAY"]);

        if (isset($params["BIRTH_MONTH"]))
            $SAVEDATA['BIRTH_MONTH'] = addText($params["BIRTH_MONTH"]);

        if (isset($params["BIRTH_YEAR"]))
            $SAVEDATA['BIRTH_YEAR'] = addText($params["BIRTH_YEAR"]);

        if (isset($params["ACADEMIC_TYPE"]))
            $SAVEDATA['ACADEMIC_TYPE'] = addText($params["ACADEMIC_TYPE"]);

        if (isset($params["SORTKEY"])) {
            StudentAcademicDBAccess::setStudentSortkeyInClass(
                    $studentId
                    , $params["academicId"]
                    , $params["SORTKEY"]);
        }

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();

        if (Zend_Registry::get('ROLE') != "STUDENT") {
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
        }

        $SAVEDATA['TS'] = time();

        $errors = Array();
        ////////////////////////////////////////////////////////////////////////
        //CHANGE PASSWORD...
        ////////////////////////////////////////////////////////////////////////
        $password = isset($params["PASSWORD"]) ? addText($params["PASSWORD"]) : "";
        $password_repeat = isset($params["PASSWORD_REPEAT"]) ? addText($params["PASSWORD_REPEAT"]) : "";
        $SAVEDATA['UMCPANL'] = isset($params["UMCPANL"]) ? 1 : 0;
        $SAVEDATA['UCNCP'] = isset($params["UCNCP"]) ? 1 : 0;
        if ($password != "" && $password_repeat != "") {
            if (strlen($password) < UserAuth::getMinPasswordLength()) {
                $errors['PASSWORD'] = PASSWORD_IS_TOO_SHORT;
            } else {
                if (UserAuth::isPasswordComplexityRequirements()) {
                    if (!preg_match("#[0-9]+#", $password)) {
                        $errors['PASSWORD'] = PASSWORD_MUST_INCLUDE_AT_LEAST_ONE_NUMBER;
                    }

                    if (!preg_match("#[a-zA-Z]+#", $password)) {
                        $errors['PASSWORD'] = PASSWORD_MUST_INCLUDE_AT_LEAST_ONE_LETTER;
                    }
                }
            }
            if ($password == $password_repeat) {
                $SAVEDATA['CHANGE_PASSWORD'] = 1;
                $SAVEDATA['UMCPANL'] = 0;
                $SAVEDATA['CHANGE_PASSWORD_DATE'] = time();
                $SAVEDATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
            }
        }

        ////////////////////////////////////////////////////////////////////////
        //CHANGE LOGINNAME...
        ////////////////////////////////////////////////////////////////////////
        $loginName = isset($params["LOGINNAME"]) ? addText($params["LOGINNAME"]) : "";
        if ($loginName != "") {
            $loginObject = self::findLoginName($loginName);
            if ($loginObject) {
                if ($studentId != $loginObject->ID) {
                    $errors['LOGINNAME'] = LOGINNAME_NOT_AVAILABLE;
                }
            }
            $SAVEDATA['LOGINNAME'] = addText($params["LOGINNAME"]);
        }
        ////////////////////////////////////////////////////////////////////////
        $WHERE[] = "ID = '" . $studentId . "'";
        if (!$errors && $studentId)
            self::dbAccess()->update('t_student', $SAVEDATA, $WHERE);

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array("success" => true);
        }
    }

    /**
     * Release student by gradeId...
     */
    public function releaseStudent($params) {

        $SAVEDATA = array();
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findStudentFromId($objectId);
        $status = $facette->STATUS;

        switch ($status) {
            case 0:
                $newStatus = 1;
                $SAVEDATA ["STATUS"] = 1;
                $SAVEDATA ["ENABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA ["ENABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update(self::TABLE_STUDENT, $SAVEDATA, $WHERE);
                break;
            case 1:
                $newStatus = 0;
                $SAVEDATA ["STATUS"] = 0;
                $SAVEDATA ["ENABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA ["ENABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update(self::TABLE_STUDENT, $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true, "status" => $newStatus);
    }

    public function jsonUnassignedStudents($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $data = array();
        $a = array();

        if ($academicObject) {

            switch ($academicObject->OBJECT_TYPE) {
                case "SCHOOLYEAR":
                    $gradeId = false;
                    break;
                case "CLASS":
                    $gradeId = $academicObject->GRADE_ID;
                    break;
            }

            $ALL_STUDENTS = StudentAcademicDBAccess::getQueryStudentEnrollment(
                            false
                            , $globalSearch
                            , false
            );

            $i = 0;
            if ($academicObject) {
                if ($ALL_STUDENTS) {

                    while (list($key, $row) = each($ALL_STUDENTS)) {

                        $CHECK_IN_SCHOOLYEAR = self::checkStudentINGradeSchoolyear(
                                        $row->STUDENT_ID
                                        , $academicObject);

                        if (!$CHECK_IN_SCHOOLYEAR) {

                            $data[$i]["ID"] = $row->STUDENT_ID;
                            $data[$i]["CODE"] = $row->STUDENT_CODE;
                            $data[$i]["STUDENT_SCHOOL_ID"] = $row->STUDENT_SCHOOL_ID;
                            $data[$i]["FIRSTNAME"] = setShowText($row->FIRSTNAME);
                            $data[$i]["LASTNAME"] = setShowText($row->LASTNAME);
                            $data[$i]["STUDENT_NAME"] = setShowText($row->LASTNAME) . " " . setShowText($row->FIRSTNAME);
                            $data[$i]["DATE_BIRTH"] = getShowDate($row->DATE_BIRTH);
                            $data[$i]["GENDER"] = getGenderName($row->GENDER);

                            ////////////////////////////////////////////////////
                            //Status of student...
                            ////////////////////////////////////////////////////
                            $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($row->STUDENT_ID);
                            $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                            $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                            $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                            $i++;
                        }
                    }
                }

                for ($i = $start; $i < $start + $limit; $i++) {
                    if (isset($data[$i]))
                        $a[] = $data[$i];
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function checkStudentEnrolledCreditSystem($studentId, $academicObject) {

        $output = 0;
        if ($academicObject) {

            $CHECK_TRADITIONAL = StudentAcademicDBAccess::findEnrollmentCountGeneralByStudentId(
                            $studentId
                            , false
                            , $academicObject->SCHOOL_YEAR);

            if (!$CHECK_TRADITIONAL) {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_student_schoolyear_subject", array("C" => "COUNT(*)"));
                $SQL->where("STUDENT_ID = ?", $studentId);
                $SQL->where("SCHOOLYEAR_ID = '" . $academicObject->SCHOOL_YEAR . "'");

                switch ($academicObject->OBJECT_TYPE) {
                    case "SUBJECT":
                        $SQL->where("SUBJECT_ID = '" . $academicObject->SUBJECT_ID . "'");
                        $SQL->where("CLASS_ID=0");
                        break;
                    case "CLASS":
                        $SQL->where("SUBJECT_ID = '" . $academicObject->SUBJECT_ID . "'");
                        $SQL->where("CLASS_ID<>0");
                        break;
                }
                //error_log($SQL->__toString());
                $SQL->group("STUDENT_ID");
                $result = self::dbAccess()->fetchRow($SQL);
                $output = $result ? $result->C : 0;
            } else {
                $output = 1;
            }
        }
        return $output;
    }

    public static function addStudentAuto2GradeSchoolyear($academicObject) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_schoolyear_subject", array("*"));
        $SQL->where("SCHOOLYEAR_ID = '" . $academicObject->SCHOOL_YEAR . "'");
        $SQL->where("SUBJECT_ID = '" . $academicObject->SUBJECT_ID . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                self::addStudentSchoolYear(
                        $value->STUDENT_ID
                        , $value->CAMPUS_ID
                        , $value->SCHOOLYEAR_ID
                );
            }
        }
    }

    public function jsonAssignedStudents($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        self::addStudentAuto2GradeSchoolyear($academicObject);
        $result = $this->queryAssignedStudentSchoolYear($params, false);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["TRANSFER"] = $value->TRANSFER ? YES : NO;

                if ($value->TRANSFER) {
                    $data[$i]["STUDENT_NAME"] = getTransferAssessmentIcon($value->TRANSFER) . " " . $value->NAME;
                } else {
                    $data[$i]["STUDENT_NAME"] = setShowText($value->NAME);
                }

                $data[$i]["STUDENT_NAME_EN"] = setShowText($value->NAME_EN);
                $data[$i]["GRADE_NAME"] = setShowText($value->GRADE_NAME);
                if (isset($value->CLASS_NAME))
                    $data[$i]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                if (isset($value->CLASS_NAME))
                    $data[$i]["CLASS"] = setShowText($value->CLASS_NAME);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["STATUS_KEY"] = iconStatus($value->RECOMMENDATION);
                $data[$i]["STATUS"] = $value->RECOMMENDATION ? 1 : 0;
                $data[$i]["SCHOOL_YEAR_NAME"] = $value->SCHOOL_YEAR_NAME;
                $data[$i]["CURRENT_LEVEL"] = $value->CURRENT_LEVEL ? 1 : 0;

                ////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                $i++;
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function jsonAddEnrollStudentSchoolyear($params) {

        $SQLIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        $feeObject = new FeeDBAccess(); //@veasna

        if ($SQLIds != "" && $academicObject) {
            $selectedStudents = explode(",", $SQLIds);

            $selectedCount = 0;
            if ($selectedStudents)
                foreach ($selectedStudents as $studentId) {
                    self::addStudentSchoolYear(
                            $studentId
                            , $academicObject->CAMPUS_ID
                            , $academicObject->GRADE_ID
                            , $academicObject->SCHOOL_YEAR
                    );

                    //@veasna 
                    //check fee and add students to fee
                    $objectCheck = $feeObject->getFeesByGradeSchoolyear($academicObject->GRADE_ID, $academicObject->SCHOOL_YEAR, '');
                    if ($objectCheck) {
                        StudentFeeDBAccess::addStudent2Fee($studentId, $objectCheck[0]->ID, $academicObject->GRADE_ID, $academicObject->SCHOOL_YEAR, false, true);
                    }
                    $selectedCount++;

                    self::setCurrentStudentAcademic($studentId);
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Remove: Student from School...
    ////////////////////////////////////////////////////////////////////////////
    public function jsonRemoveStudentFromSchool($params) {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if (UserAuth::getACLStudent("REMOVE_RIGHT")) {
            self::dbAccess()->delete('t_student', array("ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_income', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_schoolyear', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_schoolyear_subject', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_subject_assessment', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_attendance', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_discipline', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_examination', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_fee', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_guardian', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_medical', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_notes', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_status', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_subject', array("STUDENT_ID='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_training', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_student_training_assignment', array("STUDENT='" . $studentId . "'"));
            self::dbAccess()->delete('t_user_sms', array("USER_ID='" . $studentId . "'"));
        }

        return array(
            "success" => true
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    // Remove: Enrolled Student from Grade and School Year...
    ////////////////////////////////////////////////////////////////////////////
    public function jsonRemoveEnrolledStudentSchoolyear($params) {

        //removeId !=studetnId
        $studentId = isset($params["removeId"]) ? addText($params["removeId"]) : '';
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : '';

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject && $studentId) {
            $condition = array(
                'STUDENT = ? ' => $studentId
                , 'SCHOOL_YEAR = ? ' => $academicObject->SCHOOL_YEAR
            );
            self::dbAccess()->delete("t_student_schoolyear", $condition);

            self::setCurrentStudentAcademic($studentId);
        }

        return array(
            "success" => true
        );
    }

    public static function addStudentSchoolYear($studentId, $campusId, $gradeId, $schoolyearId) {

        $gradeObject = AcademicDBAccess::findGradeFromId($gradeId);
        $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($schoolyearId);

        $CHECK_STUDENT_SCHOOLYEAR = self::checkStudentINGradeSchoolyear(
                        $studentId
                        , $schoolyearObject
        );


        $SAVEDATA = array();

        if ($schoolyearObject && $gradeObject) {

            ////////////////////////////////////////////////////////////////////
            if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                $SAVEDATA['STATUS'] = 1;
            }
            if (!$CHECK_STUDENT_SCHOOLYEAR) {
                $SAVEDATA['STUDENT'] = $studentId;
                $SAVEDATA['CAMPUS'] = $campusId;
                $SAVEDATA['GRADE'] = $gradeObject->ID;
                $SAVEDATA['SCHOOL_YEAR'] = $schoolyearObject->ID;
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                self::dbAccess()->insert('t_student_schoolyear', $SAVEDATA);
            }
            ////////////////////////////////////////////////////////////////////
        }
    }

    protected function addStudentInClass($studentId, $academicObject) {

        if (!self::checkStudentINGradeSchoolyear($studentId, $academicObject)) {
            $WHERE[] = "GRADE = '" . $academicObject->GRADE_ID . "'";
            $WHERE[] = "SCHOOL_YEAR = '" . $academicObject->SCHOOL_YEAR . "'";
            $WHERE[] = "STUDENT = '" . $studentId . "'";
            $SAVEDATA['CLASS'] = $academicObject->ID;
            self::dbAccess()->update('t_student_schoolyear', $SAVEDATA, $WHERE);

            self::setCurrentStudentAcademic($studentId);
        }
    }

    public function queryAssignedStudentSchoolYear($params, $isacademicId = false) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $sortkey = isset($params["sortkey"]) ? $params["sortkey"] : 0;
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : '';

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject) {
            switch ($academicObject->OBJECT_TYPE) {
                case "CLASS":
                case "SUBJECT":
                    $academicId = $academicObject->ID;
                    break;
                default:
                    $academicId = $academicObject->ID;
                    break;
            }
            $SQL = "";
            $SQL .= " SELECT DISTINCT";
            $SQL .= " A.ID AS ID";
            $SQL .= " ,A.CODE AS CODE";
            $SQL .= " ,A.ISLOGIN AS ISLOGIN";
            $SQL .= " ,A.STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID";
            if (!SchoolDBAccess::displayPersonNameInGrid()) {
                $SQL .= " ,CONCAT(A.LASTNAME,' ',A.FIRSTNAME) AS NAME";
            } else {
                $SQL .= " ,CONCAT(A.FIRSTNAME,' ',A.LASTNAME) AS NAME";
            }
            $SQL .= " ,CONCAT(A.LASTNAME_LATIN,' ',A.FIRSTNAME_LATIN) AS NAME_EN";
            $SQL .= " ,A.FIRSTNAME AS FIRSTNAME";
            $SQL .= " ,A.LASTNAME AS LASTNAME";
            $SQL .= " ,A.LOGINNAME AS LOGINNAME";
            $SQL .= " ,A.PASSWORD AS PASSWORD";
            $SQL .= " ,A.GENDER AS GENDER";
            $SQL .= " ,A.DATE_BIRTH AS DATE_BIRTH";
            $SQL .= " ,A.EMAIL AS EMAIL";
            $SQL .= " ,A.PHONE AS PHONE";
            $SQL .= " ,A.STATUS AS STATUS";
            $SQL .= " ,C.NAME AS GRADE_NAME";
            //@soda
            $SQL .= " ,A.FIRSTNAME_LATIN AS FIRSTNAME_LATIN";
            $SQL .= " ,A.LASTNAME_LATIN AS LASTNAME_LATIN";
            $SQL .= " ,A.CREATED_DATE AS CREATED_DATE";
            $SQL .= " ,D.ID AS CLASS";
            $SQL .= " ,D.NAME AS CLASS_NAME";
            $SQL .= " ,D.ID AS CLASS_ID";
            $SQL .= " ,B.ID AS ENROLLMENT_ID";
            $SQL .= " ,B.CURRENT_LEVEL AS CURRENT_LEVEL";
            $SQL .= " ,B.STATUS AS RECOMMENDATION";
            $SQL .= " ,B.ACADEMIC_TYPE AS ACADEMIC_TYPE";
            $SQL .= " ,B.TRANSFER AS TRANSFER";
            $SQL .= " ,B.STATUS_SCORE_TRANSFER AS STATUS_SCORE_TRANSFER";
            $SQL .= " ,B.SORTKEY AS SORTKEY";
            $SQL .= " ,D.SCHOOL_YEAR AS SCHOOL_YEAR";
            $SQL .= " ,E.NAME AS SCHOOL_YEAR_NAME";
            $SQL .= " FROM";
            $SQL .= " t_student AS A";
            $SQL .= " LEFT JOIN t_student_schoolyear AS B ON A.ID = B.STUDENT";
            $SQL .= " LEFT JOIN t_grade AS C ON B.GRADE = C.ID";
            $SQL .= " LEFT JOIN t_grade AS D ON B.CLASS = D.ID";
            $SQL .= " LEFT JOIN t_academicdate AS E ON E.ID = B.SCHOOL_YEAR";

            $SQL .= " WHERE 1=1";

            $SQL .= " AND B.GRADE='" . $academicObject->GRADE_ID . "'";
            if ($academicId && $isacademicId)
                $SQL .= " AND B.CLASS='" . $academicObject->ID . "'";

            $SQL .= " AND B.SCHOOL_YEAR='" . $academicObject->SCHOOL_YEAR . "' ";

            if ($globalSearch) {

                $SQL .= " AND ((A.NAME LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (A.STUDENT_SCHOOL_ID LIKE '" . $globalSearch . "%')";
                $SQL .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
                $SQL .= " ) ";
            }
            switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
                default:
                    $SQL .= " ORDER BY A.STUDENT_SCHOOL_ID DESC";
                    break;
                case "1":
                    $SQL .= " ORDER BY A.LASTNAME DESC";
                    break;
                case "2":
                    $SQL .= " ORDER BY A.FIRSTNAME DESC";
                    break;
            }
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);
        }
        return isset($result) ? $result : false;
    }

    public function jsonUnassignedStudentsByClass($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $result = $this->queryAssignedStudentSchoolYear($params, false);
        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if (!$value->CLASS) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["CODE"] = $value->CODE;
                    $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["ENROLLMENT_ID"] = $value->ENROLLMENT_ID;
                    $data[$i]["ENROLLMENT_ID"] = $value->ENROLLMENT_ID;
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);

                    ////////////////////////////////////////////////////////////
                    //Status of student...
                    ////////////////////////////////////////////////////////////
                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);
                    $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                    $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                    $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                    $i++;
                }
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    protected function studentsInClass($params) {

        $academicId = $params["academicId"];
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $data = array();
        if ($academicObject) {

            $SQL = self::dbAccess()->select()
                    ->from("t_student_schoolyear", array('*'))
                    ->where("CLASS='" . $academicObject->ID . "'")
                    ->where("GRADE='" . $academicObject->GRADE_ID . "'")
                    ->where("SCHOOL_YEAR='" . $academicObject->SCHOOL_YEAR . "'");
            $result = self::dbAccess()->fetchAll($SQL);
            if ($result)
                foreach ($result as $value) {
                    $data[$value->STUDENT] = $value->STUDENT;
                }
        }

        return $data;
    }

    public function jsonAddEnrollStudentClass($params) {

        $SQLIds = $params["selectionIds"];
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($SQLIds != "" && $academicObject) {
            $selectedStudents = explode(",", $SQLIds);

            $selectedCount = 0;
            if ($selectedStudents)
                foreach ($selectedStudents as $studentId) {

                    if (!self::checkStudentINGradeSchoolyear($studentId, $academicObject)) {
                        $this->addStudentInClass(
                                $studentId
                                , $academicObject);

                        $selectedCount++;
                    } else {
                        $selectedCount = 0;
                    }

                    $selectedCount++;
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public static function jsonRemoveEnrolledStudentClass($params) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $objectId = isset($params["removeId"]) ? addText($params["removeId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject) {
            $SQL = "UPDATE t_student_schoolyear SET";
            $SQL .= " CLASS= ''";
            $SQL .= " ,FIRST_ACADEMIC = 0,SECOND_ACADEMIC = 0,THIRD_ACADEMIC = 0,FOURTH_ACADEMIC = 0";
            $SQL .= " WHERE";
            $SQL .= " STUDENT = '" . $objectId . "'";
            $SQL .= " AND CLASS='" . $academicObject->ID . "'";
            $SQL .= " AND SCHOOL_YEAR='" . $academicObject->SCHOOL_YEAR . "'";
            self::dbAccess()->query($SQL);

            self::dbAccess()->delete('t_student_assignment', array(
                "STUDENT_ID = '" . $objectId . "'", "CLASS_ID = '" . $academicObject->ID . "'"
            ));
            self::dbAccess()->delete('t_student_subject_assessment', array(
                "STUDENT_ID = '" . $objectId . "'", "CLASS_ID = '" . $academicObject->ID . "'"
            ));
            self::dbAccess()->delete('t_student_learning_performance', array(
                "STUDENT_ID = '" . $objectId . "'", "CLASS_ID = '" . $academicObject->ID . "'"
            ));
        }
        return array("success" => true);
    }

    public function entriesStudentsByClass($academicId) {

        return StudentAcademicDBAccess::getQueryStudentEnrollment($academicId, false);
    }

    protected function getStudentBySchoolYear($Id) {

        $SQL = "SELECT ";
        $SQL .= "
		C.NAME AS CLASS
		,A.STATUS AS STATUS
		,A.STUDENT AS STUDENT_ID
		,A.GRADE AS GRADE_ID
		";
        $SQL .= " FROM t_student_schoolyear AS A";
        $SQL .= " LEFT JOIN t_student AS B ON A.STUDENT = B.ID";
        $SQL .= " LEFT JOIN t_grade AS C ON A.CLASS = C.ID";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.ID = '" . $Id . "'";

        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadActionStudentSchoolYear($params) {
        $Id = isset($params["id"]) ? addText($params["id"]) : "0";
        $schoolyearObject = $this->getStudentBySchoolYear($Id);

        return array(
            "success" => true,
            "CLASS" => $schoolyearObject->CLASS,
            "STATUS_KEY" => iconStatus($schoolyearObject->STATUS),
            "STATUS" => $schoolyearObject->STATUS
        );
    }

    public function actionStudentSchoolYear($params) {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "0";
        $academicTerm = isset($params["academicTerm"]) ? addText($params["academicTerm"]) : "";

        $callData = array();

        switch ($field) {
            case "ACADEMIC_TYPE":
            case "TRANSFER":
            case "STATUS":
                $data = array();
                $data["'" . $field . "'"] = "'" . $newValue . "'";
                if ($newValue == 1) {
                    $data['ENABLED_DATE'] = "'" . getCurrentDBDateTime() . "'";
                    $data['ENABLED_BY'] = "'" . Zend_Registry::get('USER')->CODE . "'";
                } elseif ($params["newValue"] == 0) {
                    $data['ENABLED_DATE'] = "'" . getCurrentDBDateTime() . "'";
                    $data['ENABLED_BY'] = "'" . Zend_Registry::get('USER')->CODE . "'";
                }

                $data['MODIFY_DATE'] = "'" . getCurrentDBDateTime() . "'";
                $data['MODIFY_BY'] = "'" . Zend_Registry::get('USER')->CODE . "'";
                self::dbAccess()->update("t_student_schoolyear", $data, "ID='" . $studentId . "'");
                break;
            case "GENDER":
                self::dbAccess()->update("t_student", array('GENDER' => "'" . $newValue . "'"), "ID='" . $studentId . "'");
                break;
            case "DATE_BIRTH":
                self::dbAccess()->update("t_student", array('DATE_BIRTH' => "'" . setDate2DB($newValue) . "'"), "ID='" . $studentId . "'");
                break;
            case "FIRSTNAME":
                self::dbAccess()->update("t_student", array('FIRSTNAME' => "'" . $newValue . "'"), "ID='" . $studentId . "'");
                break;
            case "LASTNAME":
                self::dbAccess()->update("t_student", array('LASTNAME' => "'" . $newValue . "'"), "ID='" . $studentId . "'");
                break;
            case "CURRENT_LEVEL":
            case "ENROLLMENT_TYPE":

                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                $CHECK = self::checkStudentCurrentLevel($studentId, $academicObject->SCHOOL_YEAR);

                $data = array();
                $where = array();
                if ($newValue && $CHECK) {
                    $data['CURRENT_LEVEL'] = '0';
                    $callData["CURRENT_LEVEL"] = 0;
                } elseif ($newValue && !$CHECK) {
                    $data['CURRENT_LEVEL'] = '1';
                    $callData["CURRENT_LEVEL"] = 1;
                } else {
                    $data['CURRENT_LEVEL'] = '0';
                    $callData["CURRENT_LEVEL"] = 0;
                }

                $where[] = "STUDENT = '" . $studentId . "'";
                $where[] = "GRADE = '" . $academicObject->GRADE_ID . "'";
                $where[] = "SCHOOL_YEAR = '" . $academicObject->SCHOOL_YEAR . "'";
                self::dbAccess()->update("t_student_schoolyear", $data, $where);
                break;
            case "FIRST_ACADEMIC":
            case "SECOND_ACADEMIC":
            case "THIRD_ACADEMIC":
            case "FOURTH_ACADEMIC":
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                $data["" . $field . ""] = $newValue;
                $where[] = "STUDENT = '" . $studentId . "'";
                $where[] = "CLASS = '" . $academicObject->ID . "'";
                $where[] = "SCHOOL_YEAR = '" . $academicObject->SCHOOL_YEAR . "'";
                self::dbAccess()->update("t_student_schoolyear", $data, $where);
                break;
        }
        //error_log($SQL);
        return array(
            "success" => true
            , "data" => $callData
        );
    }

    protected static function checkStudentCurrentLevel($studentId, $schoolyearId) {
        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear", array("C" => "COUNT(*)"))
                ->where("STUDENT = '" . $studentId . "'")
                ->where("CURRENT_LEVEL = '1'")
                ->where("SCHOOL_YEAR = '" . $schoolyearId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Finde, ob dieser Student bei Schuljahr zugelassen worden ist.
    ////////////////////////////////////////////////////////////////////////////
    protected function checkStudentInSchoolYear($Id) {

        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear", array("C" => "COUNT(*)"))
                ->where("STUDENT = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkStudentInSchoolYearSubject($Id, $subjectId) {

        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear_subject", array("C" => "COUNT(*)"))
                ->where("STUDENT_ID = '" . $Id . "'")
                ->where("SUBJECT_ID = '" . $subjectId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Finde, ob dieser Student bei Anwesenheitslist vorhandelt.
    ////////////////////////////////////////////////////////////////////////////
    protected function checkStudentInAttendance($Id, $schoolyearId = false) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_student_attendance AS A";

        if ($schoolyearId) {
            $SQL .= " LEFT JOIN t_grade AS B ON B.ID = A.CLASS_ID";
        }

        $SQL .= " WHERE 1=1";
        if ($Id)
            $SQL .= " AND A.STUDENT_ID = '" . $Id . "'";
        if ($schoolyearId) {
            $SQL .= " AND B.SCHOOL_YEAR = '" . $schoolyearId . "'";
        }

        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////
    // Finde, ob dieser Student bei Discipline vorhandelt.
    ////////////////////////////////////////////////////////////////////////////
    protected function checkStudentInDiscipline($Id, $schoolyearId = false) {

        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_discipline AS A";

        if ($schoolyearId) {
            $SQL .= " LEFT JOIN t_grade AS B ON B.ID = A.CLASS_ID";
        }

        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.STUDENT_ID = '" . $Id . "'";
        if ($schoolyearId) {
            $SQL .= " AND B.SCHOOL_YEAR = '" . $schoolyearId . "'";
        }

        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function comboDataStudentByClass() {

        $result = self::queryStudentByClass(Zend_Registry::get('CLASS_ID'), false);
        $data = array();

        if ($result)
            foreach ($result as $value) {
                $NAME = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[] = "[\"$value->STUDENT_ID\",\"$NAME\"]";
            }

        return "[" . implode(",", $data) . "]";
    }

    public static function queryStudentByClass($academicId, $schoolyearId, $globalSearch = false) {

        return StudentAcademicDBAccess::getQueryStudentEnrollment(
                        $academicId
                        , $globalSearch
                        , $schoolyearId
        );
    }

    public function StudentsStrByClass() {

        $result = self::queryStudentByClass(
                        Zend_Registry::get('CLASS_ID')
                        , false
        );

        $data = array();

        if ($result)
            foreach ($result as $value) {
                $data[] = $value->STUDENT_ID;
            }

        return implode(",", $data);
    }

    public function findStudentInClass($StudentId) {
        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear", array('*'))
                ->where("STUDENT = '" . $StudentId . "'");
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function findStudentsInGrade($gradeId) {

        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear", array('*'))
                ->where("GRADE='" . $gradeId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            while (list($key, $row) = each($result)) {
                $data[$row->STUDENT_ID] = $row->STUDENT_ID;
            }

        return $data;
    }

    protected function findStudentsInSchoolyear($schoolyearId) {

        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear", array('*'))
                ->where("SCHOOL_YEAR='" . $schoolyearId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            while (list($key, $row) = each($result)) {
                $data[$row->STUDENT_ID] = $row->STUDENT_ID;
            }

        return $data;
    }

    protected function checkStudentSchoolId($staffId, $studentschoolId) {

        $studentObject = self::findStudentFromId($staffId);

        if ($studentObject) {
            if ($studentObject->STUDENT_SCHOOL_ID == $studentschoolId) {
                return false;
            } else {

                $SQL = self::dbAccess()->select()
                        ->from("t_student", array("C" => "COUNT(*)"))
                        ->where("STUDENT_SCHOOL_ID = '" . $studentschoolId . "'");
                $result = self::dbAccess()->fetchRow($SQL);

                if ($result) {
                    if ($result->C) {
                        return true;
                    }
                } else {
                    return false;
                }
            }
        } else {
            $SQL = self::dbAccess()->select()
                    ->from("t_student", array("C" => "COUNT(*)"))
                    ->where("STUDENT_SCHOOL_ID = '" . $studentschoolId . "'");
            $result = self::dbAccess()->fetchRow($SQL);

            if ($result) {
                if ($result->C) {
                    return true;
                }
            } else {
                return false;
            }
        }
    }

    public function checkRemoveStudentFromSchool($status, $Id) {

        $remove = 0;

        $countAttendancce = $this->checkStudentInAttendance($Id, false);
        $countDiscipline = $this->checkStudentInDiscipline($Id, false);

        if ($countAttendancce || $countDiscipline) {

            $check = true;
        }

        if ($status) {
            $remove = 0;
        } else {

            if ($check) {
                $remove = 0;
            } else {
                $remove = 1;
            }
        }

        return $remove;
    }

    public function getCountScoreEnterByStudent($Id) {

        $countInSchoolYear = $this->checkStudentInSchoolYear($Id, false);
        $countAttendancce = $this->checkStudentInAttendance($Id, false);
        $countDiscipline = $this->checkStudentInDiscipline($Id, false);

        if ($countInSchoolYear || $countAttendancce || $countDiscipline) {

            return true;
        } else {
            return false;
        }
    }

    public function checkStudentActivityInSchoolyear($Id, $schoolyearId) {

        $countAttendancce = $this->checkStudentInAttendance($Id, $schoolyearId);
        $countDiscipline = $this->checkStudentInDiscipline($Id, $schoolyearId);

        if ($countAttendancce || $countDiscipline) {

            return true;
        } else {
            return false;
        }
    }

    public function jsonCheckStudentSchoolID($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $studentSchoolId = isset($params["studentSchoolId"]) ? addText($params["studentSchoolId"]) : 0;

        $check = $this->checkStudentSchoolId($objectId, $studentSchoolId);

        if ($check) {
            return array("success" => false, "status" => false, "errors" => setICONV(SCHOOL_ID_EXISTS));
        } else {
            return array("success" => true, "status" => true);
        }
    }

    public function enrolledStudentByNextYear($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : 0;
        $chooseSchoolyearId = isset($params["chooseSchoolyearId"]) ? addText($params["chooseSchoolyearId"]) : 0;
        $nextSchoolyearId = isset($params["nextSchoolyearId"]) ? addText($params["nextSchoolyearId"]) : 0;
        $result = $this->queryStudentByClass($academicId, $chooseSchoolyearId);

        $STUDENT_IN_NEXT_SCHOOLYEAR = $this->findStudentsInSchoolyear($nextSchoolyearId);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if (!in_array($value->STUDENT_ID, $STUDENT_IN_NEXT_SCHOOLYEAR)) {
                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = $value->STUDENT_CODE;
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                    } else {
                        $data[$i]["STUDENT_NAME"] = $value->FIRSTNAME . " " . $value->LASTNAME;
                    }
                    $data[$i]["GRADE_NAME"] = setShowText($value->GRADE_NAME);
                    $data[$i]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                    $data[$i]["CLASS"] = setShowText($value->CLASS_NAME);
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["SCHOOLYEAR_NAME"] = $value->SCHOOLYEAR_NAME;

                    $i++;
                }
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function actionEnrolledStudentByNextYear($params) {

        $SQLIds = $params["selectionIds"];
        $academicId = $params["chooseacademicId"];

        $classObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($SQLIds != "" && $classObject) {

            $selectedStudents = explode(",", $SQLIds);

            $selectedCount = 0;
            if ($selectedStudents)
                foreach ($selectedStudents as $studentId) {

                    if (!self::checkStudentINGradeSchoolyear($studentId, $classObject)) {

                        $STUDENT_DATA['STUDENT'] = $studentId;
                        $STUDENT_DATA['CAMPUS'] = $classObject->CAMPUS_ID;
                        $STUDENT_DATA['GRADE'] = $classObject->GRADE_ID;
                        $STUDENT_DATA['CLASS'] = $classObject->ID;
                        $STUDENT_DATA['SCHOOL_YEAR'] = $classObject->SCHOOL_YEAR;

                        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                            $STUDENT_DATA['STATUS'] = 1;
                        }
                        $STUDENT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
                        $STUDENT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                        self::dbAccess()->insert('t_student_schoolyear', $STUDENT_DATA);

                        $selectedCount++;
                    } else {
                        $selectedCount = 0;
                    }
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public function actionGeneratePassword($params) {

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        if ($trainingId) {
            $params["trainingId"] = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
            $result = StudentTrainingDBAccess::sqlStudentTraining($params);
        } else {
            $params["gradeId"] = isset($params["academicId"]) ? addText($params["academicId"]) : "";
            $result = $this->queryAssignedStudentSchoolYear($params, true);
        }

        $password = "123";

        if ($result)
            foreach ($result as $value) {

                if ($value->PASSWORD == "") {
                    if (!self::findStudentFromId($value->ID)->CHANGE_PASSWORD) {
                        $WHERE[] = "ID = '" . $value->ID . "'";
                        $SAVEDATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
                        self::dbAccess()->update('t_student', $SAVEDATA, $WHERE);
                    }
                }
            }

        return array("success" => true);
    }

    public function setReligion($Id) {
        $facette = CamemisTypeDBAccess::findObjectFromId($Id);
        return $facette ? $facette->NAME : "---";
    }

    public function setEthnic($Id) {
        $facette = CamemisTypeDBAccess::findObjectFromId($Id);
        return $facette ? $facette->NAME : "---";
    }

    public function jsonActionStudentClassTransfer($params) {

        $WHERE = array();
        $WHERE_STUDENT_SCHOOLYEAR = array();
        $WHERE_COMMUNICATION = array();

        $WHERE[] = "STUDENT_ID = '" . addText($params["objectId"]) . "'";
        $WHERE[] = "CLASS_ID = '" . (int) $params["olClassId"] . "'";
        $SAVEDATA['CLASS_ID'] = (int) $params["newClassId"];
        self::dbAccess()->update('t_student_subject_assessment', $SAVEDATA, $WHERE);
        self::dbAccess()->update('t_student_assignment', $SAVEDATA, $WHERE);

        $WHERE[] = "STUDENT_ID = '" . addText($params["objectId"]) . "'";
        $WHERE[] = "CLASS_ID = '" . (int) $params["olClassId"] . "'";
        $SAVEDATA['CLASS_ID'] = (int) $params["newClassId"];
        self::dbAccess()->update('t_student_attendance', $SAVEDATA, $WHERE);
        self::dbAccess()->update('t_discipline', $SAVEDATA, $WHERE);

        $WHERE_STUDENT_SCHOOLYEAR[] = "STUDENT = '" . addText($params["objectId"]) . "'";
        $WHERE_STUDENT_SCHOOLYEAR[] = "CLASS = '" . (int) $params["olClassId"] . "'";
        $SAVEDATA_STUDENT_SCHOOLYEAR['CLASS'] = (int) $params["newClassId"];
        self::dbAccess()->update('t_student_schoolyear', $SAVEDATA_STUDENT_SCHOOLYEAR, $WHERE_STUDENT_SCHOOLYEAR);

        $WHERE_COMMUNICATION[] = "SENDER = '" . addText($params["objectId"]) . "'";
        $WHERE_COMMUNICATION[] = "CLASS_ID = '" . (int) $params["olClassId"] . "'";
        $SAVEDATA_COMMUNICATION['CLASS_ID'] = (int) $params["newClassId"];
        self::dbAccess()->update('t_communication', $SAVEDATA_COMMUNICATION, $WHERE_COMMUNICATION);

        return array("success" => true);
    }

    public function sqlCountStudentsbyClass($academicId, $schoolyearId) {

        $SQL = self::dbAccess()->select()
                ->from("t_student_schoolyear", array("C" => "COUNT(*)"))
                ->where("CLASS = '" . $academicId . "'")
                ->where("SCHOOL_YEAR = '" . $schoolyearId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function checkStudentExpelled($Id) {

        $SQL = self::dbAccess()->select()
                ->from("t_student_expel", array("C" => "COUNT(*)"))
                ->where("STUDENT_ID = '" . $Id . "'")
                ->where("EXPEL_TYPE=2")
                ->where("START_DATE<=DATE(NOW())");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkStudentINGradeSchoolyear($Id, $academicObject) {

        $CHECK_SQL = self::dbAccess()->select();
        $CHECK_SQL->from("t_student_schoolyear_subject", array("C" => "COUNT(*)"));
        $CHECK_SQL->where("SCHOOLYEAR_ID='" . $academicObject->SCHOOL_YEAR . "'");
        $CHECK_SQL->where("STUDENT_ID='" . $Id . "'");
        $CHECK_SQL->group("STUDENT_ID");
        $checkResult = self::dbAccess()->fetchRow($CHECK_SQL);

        $CHECK = $checkResult ? $checkResult->C : 0;

        if ($CHECK) {
            return 1;
        } else {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_student_schoolyear", array("C" => "COUNT(*)"));
            $SQL->where("STUDENT = '" . $Id . "'");

            if ($academicObject->OBJECT_TYPE == "CLASS") {
                $SQL->where("CLASS = '" . $academicObject->ID . "'");
            }

            $SQL->where("GRADE = ?", $academicObject->GRADE_ID);
            if ($academicObject->ENROLLMENT_TYPE == 1) {
                $SQL->orWhere("FIRST_ACADEMIC = '1'");
                $SQL->orWhere("SECOND_ACADEMIC = '1'");
                $SQL->orWhere("THIRD_ACADEMIC = '1'");
                $SQL->orWhere("FOURTH_ACADEMIC = '1'");
            }
            $SQL->where("SCHOOL_YEAR = ?", $academicObject->SCHOOL_YEAR);
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        }
    }

    public function actionRemoveSMSRegistration($Id) {

        $WHERE = array();
        $WHERE[] = "ID = '" . $Id . "'";
        $SAVEDATA['SMS_SERVICES'] = "0";
        self::dbAccess()->update('t_student', $SAVEDATA, $WHERE);
    }

    public function actionStudentSchoolYearSorting($params) {

        $WHERE = array();
        $studentId = isset($params["id"]) ? addText($params["id"]) : 0;
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : 0;
        $sortkey = isset($params["newValue"]) ? addText($params["newValue"]) : 0;

        if ($studentId && $academicId) {
            $WHERE[] = "STUDENT = '" . $studentId . "'";
            $WHERE[] = "CLASS = '" . $academicId . "'";
            $SAVEDATA['SORTKEY'] = $sortkey;
            self::dbAccess()->update('t_student_schoolyear', $SAVEDATA, $WHERE);
        }

        return array("success" => true);
    }

    ////////////////////////////////////////////////////////////////////////////
    //Action Student Prerequirements....
    ////////////////////////////////////////////////////////////////////////////
    public static function actionStudentPrerequirements($params) {
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";

        $SAVEDATA = array();
        if ($objectId) {
            switch ($field) {
                case "DELETE":
                    self::dbAccess()->delete("t_student_prerequirements", "ID='" . $objectId . "'");
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_student_prerequirements", $SAVEDATA, $WHERE);
                    break;
            }
        } else {
            $SAVEDATA["" . $field . ""] = addText($newValue);
            $SAVEDATA["STUDENT_ID"] = $studentId;
            self::dbAccess()->insert('t_student_prerequirements', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_student_prerequirements WHERE ID='" . $objectId . "'");

        switch ($field) {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["NAME"] = $facette->NAME;
                $SUCCESS_DATA["DESCRIPTION"] = $facette->DESCRIPTION;
                break;
        }
        return $SUCCESS_DATA;
    }

    public static function actionPersonInfos($params) {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";
        $objectType = isset($params["object"]) ? addText($params["object"]) : "";

        switch ($field) {
            case "CITY_PROVINCE":
            case "RELATIONSHIP":
            case "ETHNICITY":
            case "NATIONALITY":
            case "EMERGENCY_CONTACT":
            case "MAJOR_TYPE":
            case "MAJOR":
            case "QUALIFICATION_DEGREE":
                $newValue = isset($params["camboValue"]) ? addText($params["camboValue"]) : "";
                break;
            default:
                $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
                break;
        }

        $SAVEDATA = array();
        if ($objectId) {
            switch ($field) {
                case "DELETE":
                    self::dbAccess()->delete("t_person_infos", "ID='" . $objectId . "'");
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_person_infos", $SAVEDATA, $WHERE);
                    break;
            }
        } else {
            $SAVEDATA["" . $field . ""] = addText($newValue);
            $SAVEDATA["USER_ID"] = $studentId;
            $SAVEDATA["OBJECT_TYPE"] = $objectType;
            $SAVEDATA["USER_TYPE"] = "STUDENT";
            self::dbAccess()->insert('t_person_infos', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_person_infos WHERE ID='" . $objectId . "'");

        switch ($field) {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["NAME"] = $facette->NAME;
                $SUCCESS_DATA["OCCUPATION"] = $facette->OCCUPATION;
                $SUCCESS_DATA["RELATIONSHIP"] = $facette->RELATIONSHIP;
                $SUCCESS_DATA["ACADEMIC_YEAR"] = $facette->ACADEMIC_YEAR;
                $SUCCESS_DATA["INSTITUTION_NAME"] = $facette->INSTITUTION_NAME;
                $SUCCESS_DATA["MAJOR"] = $facette->MAJOR;
                $SUCCESS_DATA["QUALIFICATION_DEGREE"] = $facette->QUALIFICATION_DEGREE;
                $SUCCESS_DATA["CITY_PROVINCE"] = $facette->CITY_PROVINCE;
                $SUCCESS_DATA["CERTIFICATE_NUMBER"] = $facette->CERTIFICATE_NUMBER;
                $SUCCESS_DATA["PHONE"] = $facette->PHONE;
                $SUCCESS_DATA["EMAIL"] = $facette->EMAIL;
                $SUCCESS_DATA["ADDRESS"] = $facette->ADDRESS;
                $SUCCESS_DATA["DATE_BIRTH"] = $facette->DATE_BIRTH;
                $SUCCESS_DATA["ETHNICITY"] = $facette->ETHNICITY;
                $SUCCESS_DATA["NATIONALITY"] = $facette->NATIONALITY;
                $SUCCESS_DATA["EMERGENCY_CONTACT"] = $facette->EMERGENCY_CONTACT;

                break;
        }
        return $SUCCESS_DATA;
    }

    //@CHHE Vathana
    public static function jsonListPersonInfos($params, $isJson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $objectType = isset($params["object"]) ? addText($params["object"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_person_infos", array('*'));
        $SQL->where("USER_ID='" . $studentId . "'");
        $SQL->where("OBJECT_TYPE='" . $objectType . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result) {

            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["ACADEMIC_YEAR"] = setShowText($value->ACADEMIC_YEAR);
                $data[$i]["INSTITUTION_NAME"] = setShowText($value->INSTITUTION_NAME);
                $data[$i]["CERTIFICATE_NUMBER"] = setShowText($value->CERTIFICATE_NUMBER);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["ADDRESS"] = setShowText($value->ADDRESS);
                $data[$i]["DATE_BIRTH"] = setShowText($value->DATE_BIRTH);
                $data[$i]["OCCUPATION"] = setShowText($value->OCCUPATION);
                if ($value->CITY_PROVINCE <> 0)
                    $data[$i]["CITY_PROVINCE"] = LocationDBAccess::findObjectFromId($value->CITY_PROVINCE)->NAME;
                if ($value->ETHNICITY <> 0)
                    $data[$i]["ETHNICITY"] = CamemisTypeDBAccess::findObjectFromId($value->ETHNICITY)->NAME;
                if ($value->NATIONALITY <> 0)
                    $data[$i]["NATIONALITY"] = CamemisTypeDBAccess::findObjectFromId($value->NATIONALITY)->NAME;
                if ($value->RELATIONSHIP <> 0)
                    $data[$i]["RELATIONSHIP"] = CamemisTypeDBAccess::findObjectFromId($value->RELATIONSHIP)->NAME;
                if ($value->EMERGENCY_CONTACT <> 0)
                    $data[$i]["EMERGENCY_CONTACT"] = CamemisTypeDBAccess::findObjectFromId($value->EMERGENCY_CONTACT)->NAME;
                if ($value->MAJOR_TYPE <> 0)
                    $data[$i]["MAJOR_TYPE"] = CamemisTypeDBAccess::findObjectFromId($value->MAJOR_TYPE)->NAME;
                if ($value->MAJOR <> 0)
                    $data[$i]["MAJOR"] = CamemisTypeDBAccess::findObjectFromId($value->MAJOR)->NAME;
                if ($value->QUALIFICATION_DEGREE <> 0)
                    $data[$i]["QUALIFICATION_DEGREE"] = CamemisTypeDBAccess::findObjectFromId($value->QUALIFICATION_DEGREE)->NAME;

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    //End
    //@CHHE Vathana
    public static function jsonStudentPrerequirements($params, $isJson = true) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select()
                ->from("t_student_prerequirements", array('*'))
                ->where("STUDENT_ID = '" . $studentId . "'");
        error_log($SQL);

        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result) {

            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    //End
    ////////////////////////////////////////////////////////////////////////////
    //Medical Information...
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonStudentMedical($params) {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select()
                ->from("t_student_medical", array('*'))
                ->where("STUDENT_ID = '" . $studentId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result) {

            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function actionStudentDescription($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_personal_description'), array('ID', 'PARENT'));
        $SQL->joinLeft(array('B' => 't_personal_description'), 'A.PARENT=B.ID', array('CHOOSE_TYPE'));
        $SQL->where("B.PERSON='STUDENT'");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());

        self::dbAccess()->delete('t_person_description_item', array("PERSON_ID='" . $objectId . "'"));

        if ($result && $objectId) {
            foreach ($result as $value) {

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->PARENT . ""]) ? addText($params["RADIOBOX_" . $value->PARENT . ""]) : "";
                $INPUTFIELD = isset($params["INPUTFIELD_" . $value->ID . ""]) ? addText($params["INPUTFIELD_" . $value->ID . ""]) : "";
                $TEXTAREA = isset($params["TEXTAREA_" . $value->ID . ""]) ? addText($params["TEXTAREA_" . $value->ID . ""]) : "";

                switch ($value->CHOOSE_TYPE) {
                    case 1:
                        if ($CHECKBOX == "on") {
                            $SAVEDATA['ITEM'] = $value->ID;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            if (!self::checkUseDescriptionItem($objectId, $value->ID))
                                self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                    case 2:
                        if ($RADIOBOX) {
                            $SAVEDATA['ITEM'] = $RADIOBOX;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            if (!self::checkUseDescriptionItem($objectId, $RADIOBOX))
                                self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                    case 3:
                        if ($INPUTFIELD) {
                            $SAVEDATA['ITEM'] = $value->ID;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            $SAVEDATA['DESCRIPTION'] = $INPUTFIELD;
                            self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                    case 4:
                        if ($TEXTAREA) {
                            $SAVEDATA['ITEM'] = $value->ID;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            $SAVEDATA['DESCRIPTION'] = $TEXTAREA;
                            self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                }
            }
        }

        return array(
            "success" => true
        );
    }

    public function loadStudentDescripton($Id) {

        $facette = self::findStudentFromId($Id);
        $data = array();

        if ($facette) {

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_person_description_item'), array('ITEM', 'DESCRIPTION'));
            $SQL->joinLeft(array('B' => 't_personal_description'), 'A.ITEM=B.ID', array('PARENT'));
            $SQL->joinLeft(array('C' => 't_personal_description'), 'B.PARENT=C.ID', array('CHOOSE_TYPE'));
            $SQL->where("A.PERSON_ID='" . $facette->ID . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);

            foreach ($result as $value) {
                switch ($value->CHOOSE_TYPE) {
                    case 1:
                        $data["CHECKBOX_" . $value->ITEM] = true;
                        break;
                    case 2:
                        $data["RADIOBOX_" . $value->PARENT] = $value->ITEM;
                        break;
                    case 3:
                        $data["INPUTFIELD_" . $value->ITEM] = $value->DESCRIPTION;
                        break;
                    case 4:
                        $data["TEXTAREA_" . $value->ITEM] = $value->DESCRIPTION;
                        break;
                }
            }
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function mappingLogin($Id) {

        $facette = self::findStudentFromId($Id);
        $SAVEDATA["LOGINNAME"] = $facette->CODE;
        $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
        self::dbAccess()->update("t_student", $SAVEDATA, $WHERE);
    }

    protected static function findLoginName($loginname) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student", array('*'));
        $SQL->where("LOGINNAME='" . $loginname . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function checkUseDescriptionItem($Id, $item) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_person_description_item", array("C" => "COUNT(*)"));
        $SQL->where("PERSON_ID='" . $Id . "'");
        $SQL->where("ITEM='" . $item . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkStudentEducationSystem($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $status = 0;
        if ($objectId && $schoolyearId) {

            $FIRST_SQL = self::dbAccess()->select();
            $FIRST_SQL->from("t_student_schoolyear_subject", array("*"));
            $FIRST_SQL->where("SCHOOLYEAR_ID='" . $schoolyearId . "'");
            $FIRST_SQL->where("STUDENT_ID='" . $objectId . "'");
            $firstResult = self::dbAccess()->fetchRow($FIRST_SQL);

            $SECOND_SQL = self::dbAccess()->select();
            $SECOND_SQL->from("t_student_schoolyear", array("*"));
            $SECOND_SQL->where("SCHOOL_YEAR='" . $schoolyearId . "'");
            $SECOND_SQL->where("STUDENT='" . $objectId . "'");
            $secondResult = self::dbAccess()->fetchRow($SECOND_SQL);

            if ($firstResult) {
                $status = 2;
                $academicObject = AcademicDBAccess::findGradeSchoolyear(
                                $firstResult->GRADE_ID, $firstResult->SCHOOLYEAR_ID);
                $academicId = $academicObject ? $academicObject->ID : "";
            } elseif ($secondResult) {
                $status = 1;
                $academicId = $secondResult->CLASS;
            }
        }

        return array(
            "success" => true
            , "status" => $status
            , "academicId" => $academicId
        );
    }

    /////////////////////////////////////////////////////////////////////
    //@Sea Peng
    /////////////////////////////////////////////////////////////////////
    public static function actionStudentAcademicInformation($params) {

        $SAVEDATA = array();
        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_academic_additional", array('*'));
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? addText($params["RADIOBOX_" . $value->ID . ""]) : "";

                if ($RADIOBOX) {
                    $RADIOBOX_DATA[$RADIOBOX] = $RADIOBOX;
                }

                if ($CHECKBOX == "on") {
                    $CHECKBOX_DATA[$value->ID] = $value->ID;
                }
            }
        }

        $CHOOSE_DATA = $CHECKBOX_DATA + $RADIOBOX_DATA;
        $SAVEDATA["ACADEMIC_ADDITIONAL"] = implode(",", $CHOOSE_DATA);
        $WHERE = self::dbAccess()->quoteInto("STUDENT = ?", $objectId);
        self::dbAccess()->update("t_student_schoolyear", $SAVEDATA, $WHERE);

        return array(
            "success" => true
        );
    }

    public static function findAcademicByStudentId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_schoolyear');
        $SQL->where("STUDENT = '" . $Id . "'");
        //error_log($SQL->__toString());       
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public static function loadStudentAcademicInformation($Id) {

        $facette = self::findAcademicByStudentId($Id);
        $data = array();

        if ($facette) {

            $CHECK_DATA = explode(",", $facette->ACADEMIC_ADDITIONAL);
            $SQL = self::dbAccess()->select();
            $SQL->from("t_academic_additional", array('*'));
            $result = self::dbAccess()->fetchAll($SQL);

            if ($result) {
                foreach ($result as $value) {

                    $descriptionObject = SpecialDBAccess::findAcademicAdditionalFromId($value->ID);
                    switch ($value->CHOOSE_TYPE) {
                        case 1:
                            if (in_array($value->ID, $CHECK_DATA)) {
                                $data["CHECKBOX_" . $value->ID] = true;
                            } else {
                                $data["CHECKBOX_" . $value->ID] = false;
                            }

                            break;
                        case 2:
                            if (in_array($value->ID, $CHECK_DATA)) {
                                $data["RADIOBOX_" . $descriptionObject->PARENT] = $value->ID;
                            }
                            break;
                    }
                }
            }
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function actionStudent2ClassSectionTraditional($params) {

        $sectionId = isset($params["field"]) ? substr($params["field"], 8) : 0;
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";

        $data = array();
        $where = array();
        if ($newValue) {
            $data['SECTION'] = $sectionId;
        } else {
            $data['SECTION'] = 0;
        }
        $where[] = "STUDENT='" . $studentId . "'";
        $where[] = "CLASS='" . $academicId . "'";
        self::dbAccess()->update("t_student_schoolyear", $data, $where);

        return array(
            "success" => true
        );
    }

    public static function checkStudentClassSectionTraditional($studentId, $classId, $sectionId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_schoolyear", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT='" . $studentId . "'");
        $SQL->where("CLASS = ?", $classId);
        $SQL->where("SECTION='" . $sectionId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        $COUNT = $result ? $result->C : 0;
        return $COUNT ? 1 : 0;
    }

    //@Sea Peng 09.01.2014
    public static function sqlAllActiveStudents($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), array('*'));
        $SQL->where("A.STATUS=1");
        if ($globalSearch) {

            $SQL .= " AND ((A.NAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    //@Math Man 25.12.2013
    public static function findStudentLoginNameOrEmail($loginNameOrEmail) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student", array("*"));
        $SQL->where("LOGINNAME='" . $loginNameOrEmail . "'");
        $SQL->orwhere("EMAIL='" . $loginNameOrEmail . "'");
        $SQL->limit(1);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function resetNewPassword($params) {
        $DATA['PASSWORD'] = addText($params['PASSWORD']);
        $WHERE[] = "LOGINNAME = '" . addText($params['LOGINNAME']) . "'";
        self::dbAccess()->update('t_student', $DATA, $WHERE);
    }

    public static function setCurrentStudentAcademic($studentId = false) {
        ini_set('memory_limit', '128M');

        if ($studentId) {
            $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($studentId);
            $WHERE[] = "ID = '" . $studentId . "'";
            $SAVE_DATA['CURRENT_ACADEMIC'] = StudentSearchDBAccess::getCurrentAcademic($studentId)->CURRENT_ACADEMIC;
            $SAVE_DATA['CURRENT_SCHOOLYEAR'] = StudentSearchDBAccess::getCurrentAcademic($studentId)->CURRENT_SCHOOLYEAR;
            $SAVE_DATA['CURRENT_COURSE'] = StudentSearchDBAccess::getCurrentTraining($studentId);
            $SAVE_DATA['STATUS_SHORT'] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
            $SAVE_DATA['STATUS_COLOR'] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
            $SAVE_DATA['STATUS_COLOR_FONT'] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
            self::dbAccess()->update('t_student', $SAVE_DATA, $WHERE);
        } else {
            $studentObject = new StudentSearchDBAccess();
            $entries = $studentObject->queryAllStudents();
            if ($entries) {
                foreach ($entries as $value) {
                    self::setCurrentStudentAcademic($value->ID);
                }
            }
        }
    }

    public static function getAge($studentId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student", array("DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(DATE_BIRTH)), '%Y')+0 AS AGE"));
        $SQL->where("ID = '" . $studentId . "'");
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchRow($SQL);

        $output = "---";
        if ($result) {
            if (is_numeric($result->AGE)) {
                $output = $result->AGE;
            }
        }

        return $output;
    }

}

?>
