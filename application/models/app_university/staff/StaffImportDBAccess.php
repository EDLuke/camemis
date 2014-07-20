<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once('excel/excel_reader2.php');
require_once 'models/app_university/user/UserRoleDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StaffImportDBAccess {

    private $subjectAccess;

    public function __construct() {

        $this->subjectAccess = SubjectDBAccess::getInstance();
    }

    static function getInstance() {
        return new StaffImportDBAccess();
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    protected function importQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT DISTINCT A.ID AS ID
		,A.STAFF_SCHOOL_ID AS STAFF_SCHOOL_ID
		,A.NAME AS NAME
		,A.FIRSTNAME AS FIRSTNAME
		,A.LASTNAME AS LASTNAME
		,A.FIRSTNAME_LATIN AS FIRSTNAME_LATIN
		,A.LASTNAME_LATIN AS LASTNAME_LATIN
		,A.DATE_BIRTH AS DATE_BIRTH
		,A.GENDER AS GENDER
		,A.JOB_TYPE AS JOB_TYPE
		,A.CAMPUS AS CAMPUS
		,A.PROGRAM AS PROGRAM
		,A.ROLE AS ROLE
		,A.CODE AS CODE
		,A.START_DATE AS START_DATE
		,A.MARRIED AS MARRIED
		,A.NUMBER_CHILDREN AS NUMBER_CHILDREN
		,A.BIRTH_PLACE AS BIRTH_PLACE
		,A.PHONE AS PHONE
		,A.EMAIL AS EMAIL
		,A.ADDRESS AS ADDRESS
		,A.COUNTRY AS COUNTRY
		,A.PROFESSION AS PROFESSION
		,A.COUNTRY_PROVINCE AS COUNTRY_PROVINCE
		,A.TOWN_CITY AS TOWN_CITY
		,A.POSTCODE_ZIPCODE AS POSTCODE_ZIPCODE
		,A.WORK_EXPERIENCE AS WORK_EXPERIENCE
		,A.SUBJECTS AS SUBJECTS
		,A.CREATED_DATE AS CREATED_DATE
		,B.NAME AS ROLE_NAME
		";
        $SQL .= " FROM t_staff_temp AS A";
        $SQL .= " LEFT JOIN t_memberrole AS B ON B.ID=A.ROLE";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {

            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME like '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME like '" . $globalSearch . "%')";
            $SQL .= " OR (A.CODE like '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }

        $SQL .= " ORDER BY A.STAFF_SCHOOL_ID";

        return self::dbAccess()->fetchAll($SQL);
    }

    public function importStaffs($params) {

        $data = array();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->importQuery($params);

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $campusObject = AcademicDBAccess::findGradeFromId($value->CAMPUS);

                $data[$i]["ID"] = $value->ID;
                if ($campusObject)
                    $data[$i]["CAMPUS_NAME"] = setShowText($campusObject->NAME);
                $data[$i]["ACTION_STATUS_ICON"] = iconImportStatus($this->checkStaffBySchoolId($value->STAFF_SCHOOL_ID));
                $data[$i]["STAFF_SCHOOL_ID"] = setShowText($value->STAFF_SCHOOL_ID);
                $data[$i]["STAFF"] = setShowText($value->NAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["ROLE_NAME"] = setShowText($value->ROLE_NAME);
                $data[$i]["JOB_TYPE"] = getJobType($value->JOB_TYPE);

                $data[$i]["NUMBER_CHILDREN"] = setShowText($value->NUMBER_CHILDREN);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["ADDRESS"] = setShowText($value->ADDRESS);
                $data[$i]["COUNTRY_PROVINCE"] = setShowText($value->COUNTRY_PROVINCE);
                $data[$i]["TOWN_CITY"] = setShowText($value->TOWN_CITY);
                $data[$i]["POSTCODE_ZIPCODE"] = setShowText($value->POSTCODE_ZIPCODE);
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);

                $data[$i]["SUBJECTS"] = $value->SUBJECTS;

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

    public function importXLS($params) {

        $xls = new Spreadsheet_Excel_Reader($_FILES["xlsfile"]['tmp_name']);
        $campusId = isset($params["CAMPUS"]) ? addText($params["CAMPUS"]) : "0";
        $programId = isset($params["PROGRAM"]) ? addText($params["PROGRAM"]) : "0";
        $roleId = isset($params["USER_ROLE"]) ? addText($params["USER_ROLE"]) : "0";
        $dates = isset($params["CREATED_DATE"]) ? addText($params["CREATED_DATE"])  : "";

        for ($iCol = 1; $iCol <= $xls->sheets[0]['numCols']; $iCol++) {

            $field = isset($xls->sheets[0]['cells'][1][$iCol]) ? $xls->sheets[0]['cells'][1][$iCol] : "";

            switch ($field) {
                case "STAFF_SCHOOL_ID":
                    $Col_STAFF_SCHOOL_ID = $iCol;
                    break;
                case "LASTNAME":
                    $Col_LASTNAME = $iCol;
                    break;
                case "FIRSTNAME":
                    $Col_FIRSTNAME = $iCol;
                    break;
                case "LASTNAME_LATIN":
                    $Col_LASTNAME_LATIN = $iCol;
                    break;
                case "FIRSTNAME_LATIN":
                    $Col_FIRSTNAME_LATIN = $iCol;
                    break;
                case "GENDER":
                    $Col_GENDER = $iCol;
                    break;
                case "JOB_TYPE":
                    $Col_JOB_TYPE = $iCol;
                    break;
                case "START_DATE":
                    $Col_START_DATE = $iCol;
                    break;
                case "MARRIED":
                    $Col_MARRIED = $iCol;
                    break;
                case "NUMBER_CHILDREN":
                    $Col_NUMBER_CHILDREN = $iCol;
                    break;
                case "DATE_BIRTH":
                    $Col_DATE_BIRTH = $iCol;
                    break;
                case "BIRTH_PLACE":
                    $Col_BIRTH_PLACE = $iCol;
                    break;
                case "EMAIL":
                    $Col_EMAIL = $iCol;
                    break;
                case "PHONE":
                    $Col_PHONE = $iCol;
                    break;
                case "ADDRESS":
                    $Col_ADDRESS = $iCol;
                    break;
                case "COUNTRY":
                    $Col_COUNTRY = $iCol;
                    break;
                case "COUNTRY_PROVINCE":
                    $Col_COUNTRY_PROVINCE = $iCol;
                    break;
                case "TOWN_CITY":
                    $Col_TOWN_CITY = $iCol;
                    break;
                case "POSTCODE_ZIPCODE":
                    $Col_POSTCODE_ZIPCODE = $iCol;
                    break;
            }
        }

        for ($i = 1; $i <= $xls->sheets[0]['numRows']; $i++) {
            //STAFF_SCHOOL_ID

            $STAFF_SCHOOL_ID = isset($xls->sheets[0]['cells'][$i + 2][$Col_STAFF_SCHOOL_ID]) ? $xls->sheets[0]['cells'][$i + 2][$Col_STAFF_SCHOOL_ID] : "";

            //FIRSTNAME
            $FIRSTNAME = isset($xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME]) ? $xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME] : "";

            //LASTNAME
            $LASTNAME = isset($xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME]) ? $xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME] : "";

            //FIRSTNAME_LATIN
            $FIRSTNAME_LATIN = isset($xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME_LATIN]) ? $xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME_LATIN] : "";

            //LASTNAME_LATIN
            $LASTNAME_LATIN = isset($xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME_LATIN]) ? $xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME_LATIN] : "";

            //GENDER
            $GENDER = isset($xls->sheets[0]['cells'][$i + 2][$Col_GENDER]) ? $xls->sheets[0]['cells'][$i + 2][$Col_GENDER] : "";

            //JOB_TYPE
            $JOB_TYPE = isset($xls->sheets[0]['cells'][$i + 2][$Col_JOB_TYPE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_JOB_TYPE] : "";
            //START_DATE

            $START_DATE = isset($xls->sheets[0]['cells'][$i + 2][$Col_START_DATE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_START_DATE] : "";
            //MARRIED

            $MARRIED = isset($xls->sheets[0]['cells'][$i + 2][$Col_MARRIED]) ? $xls->sheets[0]['cells'][$i + 2][$Col_MARRIED] : "";
            //NUMBER_CHILDREN

            $NUMBER_CHILDREN = isset($xls->sheets[0]['cells'][$i + 2][$Col_NUMBER_CHILDREN]) ? $xls->sheets[0]['cells'][$i + 2][$Col_NUMBER_CHILDREN] : "";
            //DATE_BIRTH

            $DATE_BIRTH = isset($xls->sheets[0]['cells'][$i + 2][$Col_DATE_BIRTH]) ? $xls->sheets[0]['cells'][$i + 2][$Col_DATE_BIRTH] : "";
            //BIRTH_PLACE

            $BIRTH_PLACE = isset($xls->sheets[0]['cells'][$i + 2][$Col_BIRTH_PLACE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_BIRTH_PLACE] : "";
            //PHONE

            $PHONE = isset($xls->sheets[0]['cells'][$i + 2][$Col_PHONE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_PHONE] : "";
            //EMAIL

            $EMAIL = isset($xls->sheets[0]['cells'][$i + 2][$Col_EMAIL]) ? $xls->sheets[0]['cells'][$i + 2][$Col_EMAIL] : "";
            //ADDRESS

            $ADDRESS = isset($xls->sheets[0]['cells'][$i + 2][$Col_ADDRESS]) ? $xls->sheets[0]['cells'][$i + 2][$Col_ADDRESS] : "";
            //COUNTRY

            $COUNTRY = isset($xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY]) ? $xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY] : "";
            //COUNTRY_PORVINCE

            $COUNTRY_PROVINCE = isset($xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY_PROVINCE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY_PROVINCE] : "";
            //TOWN_CITY

            $TOWN_CITY = isset($xls->sheets[0]['cells'][$i + 2][$Col_TOWN_CITY]) ? $xls->sheets[0]['cells'][$i + 2][$Col_TOWN_CITY] : "";
            //POSTCODE_ZIOCODE

            $POSTCODE_ZIPCODE = isset($xls->sheets[0]['cells'][$i + 2][$Col_POSTCODE_ZIPCODE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_POSTCODE_ZIPCODE] : "";

            $IMPORT_DATA['ID'] = generateGuid();
            $IMPORT_DATA['CODE'] = createCode();

            $IMPORT_DATA['STAFF_SCHOOL_ID'] = $STAFF_SCHOOL_ID;

            switch (UserAuth::systemLanguage()) {
                case "VIETNAMESE":
                    $IMPORT_DATA['NAME'] = setImportChartset($LASTNAME) . " " . setImportChartset($FIRSTNAME);
                    $IMPORT_DATA['FIRSTNAME'] = setImportChartset($FIRSTNAME);
                    $IMPORT_DATA['LASTNAME'] = setImportChartset($LASTNAME);
                    $IMPORT_DATA['FIRSTNAME_LATIN'] = setImportChartset($FIRSTNAME_LATIN);
                    $IMPORT_DATA['LASTNAME_LATIN'] = setImportChartset($LASTNAME_LATIN);
                    break;
                default:
                    $IMPORT_DATA['NAME'] = addText($LASTNAME) . " " . addText($FIRSTNAME);
                    $IMPORT_DATA['FIRSTNAME'] = addText($FIRSTNAME);
                    $IMPORT_DATA['LASTNAME'] = addText($LASTNAME);
                    $IMPORT_DATA['FIRSTNAME_LATIN'] = addText($FIRSTNAME_LATIN);
                    $IMPORT_DATA['LASTNAME_LATIN'] = addText($LASTNAME_LATIN);
                    break;
            }

            $IMPORT_DATA['GENDER'] = addText($GENDER);

            $IMPORT_DATA['JOB_TYPE'] = addText($JOB_TYPE);

            if ($campusId)
                $IMPORT_DATA['CAMPUS'] = addText($campusId);
            if ($programId)
                $IMPORT_DATA['PROGRAM'] = addText($programId);

            $IMPORT_DATA['ROLE'] = $roleId;

            if (isset($DATE_BIRTH)) {
                if ($DATE_BIRTH != "") {

                    $date = str_replace("/", ".", $DATE_BIRTH);
                    if ($date) {
                        $explode = explode(".", $date);
                        $d = isset($explode[0]) ? trim($explode[0]) : "00";
                        $m = isset($explode[1]) ? trim($explode[1]) : "00";
                        $y = isset($explode[2]) ? trim($explode[2]) : "0000";
                        $IMPORT_DATA['DATE_BIRTH'] = $y . "-" . $m . "-" . $d;
                    } else {
                        $IMPORT_DATA['DATE_BIRTH'] = "0000-00-00";
                    }
                }
            } else {
                $IMPORT_DATA['DATE_BIRTH'] = "0000-00-00";
            }

            if (isset($START_DATE)) {
                if ($START_DATE != "") {
                    $date = str_replace("/", ".", $START_DATE);

                    if ($date) {
                        $explode = explode(".", $date);
                        $d = isset($explode[0]) ? trim($explode[0]) : "00";
                        $m = isset($explode[1]) ? trim($explode[1]) : "00";
                        $y = isset($explode[2]) ? trim($explode[2]) : "0000";
                        $IMPORT_DATA['START_DATE'] = $y . "-" . $m . "-" . $d;
                    }
                }
            } else {
                $IMPORT_DATA['START_DATE'] = "0000-00-00";
            }

            $IMPORT_DATA['MARRIED'] = addText($MARRIED);

            $IMPORT_DATA['NUMBER_CHILDREN'] = addText($NUMBER_CHILDREN);

            $IMPORT_DATA['BIRTH_PLACE'] = addText($BIRTH_PLACE);

            $IMPORT_DATA['PHONE'] = addText($PHONE);

            $IMPORT_DATA['EMAIL'] = addText($EMAIL);

            $IMPORT_DATA['ADDRESS'] = addText($ADDRESS);

            $IMPORT_DATA['COUNTRY_PROVINCE'] = addText($COUNTRY_PROVINCE);

            $IMPORT_DATA['TOWN_CITY'] = addText($TOWN_CITY);

            $IMPORT_DATA['POSTCODE_ZIPCODE'] = addText($POSTCODE_ZIPCODE);

            $IMPORT_DATA['COUNTRY'] = addText($COUNTRY);

            if ($dates) {
                $IMPORT_DATA['CREATED_DATE'] = setDatetimeFormat($dates);
            } else {
                $IMPORT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            }

            $IMPORT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            if (isset($STAFF_SCHOOL_ID) && isset($FIRSTNAME) && isset($LASTNAME)) {

                if (!$this->checkStaff($STAFF_SCHOOL_ID, $FIRSTNAME, $LASTNAME)) {

                    if (!$this->checkSchoolcodeInTemp($STAFF_SCHOOL_ID)) {
                        if ($FIRSTNAME && $LASTNAME)
                            self::dbAccess()->insert('t_staff_temp', $IMPORT_DATA);
                    }
                }
            }
        }
        return array("success" => true);
    }

    public function jsonAddStaffToStaffDB($params) {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $result = $this->importQuery($params);

        $DB_USER_ROLE = UserRoleDBAccess::getInstance();

        if ($selectionIds) {

            $entries = explode(",", $selectionIds);

            $importCount = 0;

            if ($result)
                foreach ($result as $value) {

                    $check = $this->checkStaffBySchoolId($value->STAFF_SCHOOL_ID);

                    if (in_array($value->ID, $entries) && !$check) {

                        $userroleObject = $DB_USER_ROLE->findUserRoleFromId($value->ROLE);
                        $STAFF_DATA["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                        $STAFF_DATA["ID"] = $value->ID;
                        $STAFF_DATA["CODE"] = $value->CODE;
                        $STAFF_DATA["NAME"] = addText($value->NAME);
                        $STAFF_DATA["FIRSTNAME"] = addText($value->FIRSTNAME);
                        $STAFF_DATA["LASTNAME"] = addText($value->LASTNAME);
                        $STAFF_DATA["FIRSTNAME_LATIN"] = addText($value->FIRSTNAME_LATIN);
                        $STAFF_DATA["LASTNAME_LATIN"] = addText($value->LASTNAME_LATIN);
                        $STAFF_DATA["GENDER"] = $value->GENDER;
                        $STAFF_DATA["JOB_TYPE"] = $value->JOB_TYPE;
                        $STAFF_DATA['START_DATE'] = $value->START_DATE;
                        $STAFF_DATA['DATE_BIRTH'] = $value->DATE_BIRTH;
                        $STAFF_DATA['MARRIED'] = $value->MARRIED;
                        $STAFF_DATA['NUMBER_CHILDREN'] = $value->NUMBER_CHILDREN;
                        $STAFF_DATA['BIRTH_PLACE'] = addText($value->BIRTH_PLACE);
                        $STAFF_DATA['PHONE'] = $value->PHONE;
                        $STAFF_DATA['EMAIL'] = $value->EMAIL;
                        $STAFF_DATA['ADDRESS'] = addText($value->ADDRESS);
                        $STAFF_DATA['COUNTRY_PROVINCE'] = addText($value->COUNTRY_PROVINCE);
                        $STAFF_DATA['TOWN_CITY'] = addText($value->TOWN_CITY);
                        $STAFF_DATA['POSTCODE_ZIPCODE'] = $value->POSTCODE_ZIPCODE;
                        $STAFF_DATA['COUNTRY'] = $value->COUNTRY;

                        $STAFF_DATA['CREATED_DATE'] = $value->CREATED_DATE;
                        $STAFF_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                            $STAFF_DATA['STATUS'] = 1;
                        }
                        self::dbAccess()->insert('t_staff', $STAFF_DATA);

                        if ($userroleObject)
                            $SAVEDATA_USER["ROLE"] = $userroleObject->ID;

                        $SAVEDATA_USER['ID'] = $value->ID;
                        $SAVEDATA_USER["NAME"] = addText($value->NAME);
                        $SAVEDATA_USER["FIRSTNAME"] = addText($value->FIRSTNAME);
                        $SAVEDATA_USER["LASTNAME"] = addText($value->LASTNAME);
                        $SAVEDATA_USER['PHONE'] = $value->PHONE;
                        $SAVEDATA_USER['EMAIL'] = $value->EMAIL;
                        $SAVEDATA_USER['LID'] = $value->CODE;
                        $SAVEDATA_USER['LOGINNAME'] = $value->CODE;
                        $SAVEDATA_USER['CODE'] = $value->CODE;
                        $SAVEDATA_USER['CREATED_DATE'] = $value->CREATED_DATE;
                        $SAVEDATA_USER['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                        $password = '123';
                        if (Zend_Registry::get('SCHOOL')->SET_DEFAULT_PASSWORD) {
                            $SAVEDATA_USER['PASSWORD'] = md5("123-D99A6718-9D2A-8538-8610-E048177BECD5");
                        } else {
                            $password = createpassword();
                            $SAVEDATA_USER['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
                        }

                        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                            $SAVEDATA_USER['STATUS'] = 1;
                        }
                        self::dbAccess()->insert('t_members', $SAVEDATA_USER);

                        $this->deleteStaffFromImport($value->ID);

                        $importCount += 1;
                    }

                    //@Math Man
                    if ($SAVEDATA_USER['EMAIL']) {
                        $recipientName = $STUDENT_DATA["LASTNAME"] . " " . $STUDENT_DATA["FIRSTNAME"];
                        if (Zend_Registry::get('SCHOOL')->DISPLAY_POSITION_LASTNAME == 1)
                            $recipientName = $STUDENT_DATA["FIRSTNAME"] . " " . $STUDENT_DATA["LASTNAME"];
                        $result = SchoolDBAccess::getSchool();
                        $subject_email = $result->ACCOUNT_CREATE_SUBJECT;
                        $sendTo = $SAVEDATA_USER['EMAIL'];
                        $content_email = $result->SALUTATION_EMAIL . ' ' . $recipientName . ',' . "\r\n";
                        $content_email .= "\r\n" . $result->ACCOUNT_CREATE_NOTIFICATION . "\r\n";
                        $content_email .= SCHOOL . ': ' . $result->NAME . "\r\n";
                        $content_email .= WEBSITE . ': http://' . $_SERVER['SERVER_NAME'] . "\r\n";
                        $content_email .= LOGINNAME . ': ' . $SAVEDATA_USER["LOGINNAME"] . "\r\n";
                        $content_email .= PASSWORD . ': ' . $password . "\r\n";
                        $content_email .= "\r\n" . $result->SIGNATURE_EMAIL . "\r\n";
                        $headers = 'MIME-Version: 1.0' . "\r\n";
                        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                        if ($result->SMS_DISPLAY) {
                            $headers .= 'From:' . $result->SMS_DISPLAY . "\r\n" .
                                    'Reply-To:' . $result->SMS_DISPLAY . "\r\n" .
                                    'X-Mailer: PHP/' . phpversion();
                        } else {
                            $headers .= 'From: noreply@camemis.com' . "\r\n" .
                                    'Reply-To: noreply@camemis.com' . "\r\n" .
                                    'X-Mailer: PHP/' . phpversion();
                        }

                        mail($sendTo, '=?utf-8?B?' . base64_encode($subject_email) . '?=', $content_email, $headers);
                    }
                    ///////////////////////////
                }
        }

        return array(
            "success" => true
            , 'selectedCount' => $importCount
        );
    }

    protected function deleteStaffFromImport($removeId) {

        self::dbAccess()->delete("t_staff_temp", array("ID='" . $removeId . "'"));
    }

    protected function checkStaff($staff_school_id, $firstname, $lastname) {

        $SQL = self::dbAccess()->select()
                ->from("t_staff_temp", array("C" => "COUNT(*)"))
                ->where("STAFF_SCHOOL_ID = '" . $staff_school_id . "'")
                ->where("LASTNAME = '" . $lastname . "'")
                ->where("FIRSTNAME = '" . $firstname . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonRemoveStaffsFromImport($params) {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";

        $selectedCount = 0;
        if ($selectionIds) {

            $entries = explode(",", $selectionIds);
            $selectedCount = sizeof($entries);

            if ($entries)
                foreach ($entries as $staffId) {

                    self::dbAccess()->query("DELETE FROM t_staff WHERE ID ='" . $staffId . "'");
                    self::dbAccess()->query("DELETE FROM t_staff_temp WHERE ID ='" . $staffId . "'");
                }
        }

        return array(
            "success" => true
            , 'selectedCount' => $selectedCount
        );
    }

    protected function checkSchoolcodeInTemp($codeId) {
        $SQL = self::dbAccess()->select()
                ->from("t_staff_temp", array("C" => "COUNT(*)"))
                ->where("STAFF_SCHOOL_ID = '" . $codeId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    //@Math Man
    public static function checkAllImportInTemp() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_temp", array("C" => "COUNT(*)"));
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkStaffBySchoolId($schoolId) {

        $SQL = self::dbAccess()->select()
                ->from("t_staff", array("C" => "COUNT(*)"))
                ->where("STAFF_SCHOOL_ID = '" . $schoolId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonActionChangeStaffImport($params) {

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_staff_temp";
        $SQL .= " SET";
        switch ($params["field"]) {
            case "START_DATE":
                $date = str_replace("/", ".", $params["newValue"]);
                if ($date) {
                    $explode = explode(".", $date);
                    $d = isset($explode[0]) ? trim($explode[0]) : "00";
                    $m = isset($explode[1]) ? trim($explode[1]) : "00";
                    $y = isset($explode[2]) ? trim($explode[2]) : "0000";
                    $newValue = $y . "-" . $m . "-" . $d;
                    $SQL .= " " . $params["field"] . "='" . $newValue . "'";
                }
                break;
            default:
                $SQL .= " " . $params["field"] . "='" . $params["newValue"] . "'";
                break;
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $params["id"] . "'";
        self::dbAccess()->query($SQL);

        return array("success" => true);
    }

    public function setSubjectsImplode($subjectString) {

        $data = array();
        if ($subjectString) {

            $subjectEntries = explode(",", $subjectString);
            foreach ($subjectEntries as &$value) {

                $facette = SubjectDBAccess::findSubjectFromId(trim($value));

                if ($facette) {
                    $data[] = $facette->ID;
                }
            }
        }
        return implode(",", $data);
    }

}

?>