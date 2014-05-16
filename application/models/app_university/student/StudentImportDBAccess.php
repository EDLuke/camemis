<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once('excel/excel_reader2.php');
require_once 'include/Common.inc.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/training/TrainingDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/examination/ExaminationDBAccess.php';
require_once 'models/app_university/examination/StudentExaminationDBAccess.php';
require_once setUserLoacalization();

class StudentImportDBAccess {

    static function getInstance() {

        return new StudentImportDBAccess();
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    protected function importQuery($params) {

        $display = isset($params["display"]) ? addText($params["display"])  : "1";
        $educationSystem = isset($params["educationSystem"]) ? addText($params["educationSystem"])  : "0";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        //@veasna
        $type = isset($params["type"]) ? addText($params["type"]) : '';

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';

        if ($objectId) {
            $campus = $objectId;
        } else {
            $campus = isset($params["campus"]) ? addText($params["campus"])  : "";
        }

        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $examResult = isset($params["examResult"]) ? addText($params["examResult"])  : "";

        if ($examResult) {
            $facette = AcademicDBAccess::sqlGradeFromId($campus);
        }
        //

        $SQL = "";
        $SQL .= " SELECT
        A.ID AS ID
        ,A.STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID
        ,A.CODE AS CODE
        ,A.ENROLL_AVG AS ENROLL_AVG
        ,A.STATUS AS STATUS
        ,A.FIRSTNAME AS FIRSTNAME
        ,A.LASTNAME_LATIN AS LASTNAME_LATIN
        ,A.FIRSTNAME_LATIN AS FIRSTNAME_LATIN
        ,A.LASTNAME AS LASTNAME
        ,A.GENDER AS GENDER
        ,A.DATE_BIRTH AS DATE_BIRTH
        ,A.ACADEMIC_TYPE AS ACADEMIC_TYPE
        ,A.BIRTH_PLACE AS BIRTH_PLACE
        ,A.ADDRESS AS ADDRESS
        ,A.EMAIL AS EMAIL
        ,A.COUNTRY AS COUNTRY
        ,A.COUNTRY_PROVINCE AS COUNTRY_PROVINCE
        ,A.TOWN_CITY AS TOWN_CITY
        ,A.POSTCODE_ZIPCODE AS POSTCODE_ZIPCODE
        ,A.PHONE AS PHONE
        ,A.TYPE AS TYPE
        ,A.TRAINING AS TRAINING
        ,A.CREATED_DATE AS CREATED_DATE
        ";
        switch ($display) {
            case 1:

                $SQL .= "
                ,B.NAME AS CAMPUS_NAME
                ,B.ID AS CAMPUS_ID
                ,B.ENROLL_EXAM_EXPECTED_SCORE AS ENROLL_EXAM_EXPECTED_SCORE
                ,B.ENROLL_EXAM_NAME AS ENROLL_EXAM_NAME
                ";
                $SQL .= " FROM t_student_temp AS A";
                $SQL .= " LEFT JOIN t_grade AS B ON B.ID=A.CAMPUS";
                break;
            case 2:
                $SQL .= "
                ,B.NAME AS CAMPUS_NAME
                ,B.ID AS CAMPUS_ID
                ";
                $SQL .= " FROM t_student_temp AS A";
                $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";
                break;
        }

        $SQL .= " WHERE 1=1";

        if ($type) { //@ THORN Visal
            //@veasna
            $SQL .= " AND A.TYPE ='" . $type . "'";
        }else{
            $SQL .= " AND A.TYPE <> 'ENROLL'";    
        }

        if ($campus)
            $SQL .= " AND A.CAMPUS ='" . $campus . "'";

        if ($gender)
            $SQL .= " AND A.GENDER ='" . $gender . "'";

        //
        switch ($display) {
            case 2:
                $SQL .= " AND A.TRAINING<>0";
                $SQL .= " AND A.CAMPUS IS NULL";
                break;
            case 1:
                $SQL .= " AND A.EDUCATION_SYSTEM=" . $educationSystem . "";
                $SQL .= " AND A.TRAINING=0";
                //$SQL .= " AND A.CAMPUS <> 0"; //@THORN Visal -> Old "A.CAMPUS <> 0"
                switch ($examResult) {
                    case 1:
                        if ($facette)
                            $SQL .= " AND A.ENROLL_AVG >='" . $facette->ENROLL_EXAM_EXPECTED_SCORE . "'";
                        break;
                    case 2:
                        if ($facette)
                            $SQL .= " AND A.ENROLL_AVG <'" . $facette->ENROLL_EXAM_EXPECTED_SCORE . "'";
                        break;
                }
                break;
        }

        if ($globalSearch) {

            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME like '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME like '" . $globalSearch . "%')";
            $SQL .= " OR (A.CODE like '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }

        if ($type == 'ENROLL') {
            $SQL .= " ORDER BY A.ENROLL_AVG DESC";
        } else {
            $SQL .= " ORDER BY A.STUDENT_SCHOOL_ID";
        }

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function importStudents($params, $isJason = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        ///
        $type = isset($params['type']) ? addText($params['type']) : '';
        $campus = isset($params['campus']) ? (int)($params['campus']) : '';
        $training = isset($params['trainingId']) ? (int)($params['trainingId']) : '';
        $subjectExam = "";
        if ($type == 'ENROLL') {
            $facette = ExaminationDBAccess::findAcademicById($campus);
            $arr["type"] = 6;
            $arr["schoolyearId"] = $facette->SCHOOL_YEAR;
            $arr["gradeId"] = $facette->GRADE_ID;
            $subjectExam = ExaminationDBAccess::getSQLExamination($arr);
        }
        ////
        $result = $this->importQuery($params);
        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["STATUS"] = getStatus($value->STATUS);
                $data[$i]["STUDENT_SCHOOL_ID"] = setShowText($value->STUDENT_SCHOOL_ID);
                $data[$i]["ACTION_STATUS"] = $this->checkStudentBySchoolId($value->STUDENT_SCHOOL_ID);
                $data[$i]["ACTION_STATUS_ICON"] = iconImportStatus($this->checkStudentBySchoolId($value->STUDENT_SCHOOL_ID));

                //@veasna
                $data[$i]['ENROLLMENT_STATUS'] = $this->checkStudentById($value->ID) ? YES : NO;

                if ($value->TRAINING) {
                    $data[$i]["CAMPUS_NAME"] = $this->getTraining($value->TRAINING)->NAME;
                } else {
                    $data[$i]["CAMPUS_NAME"] = $this->getAcademic($value->CAMPUS_ID)->NAME;
                }

                //@veasna
                if ($value->TYPE == 'ENROLL') {

                    $facette = ExaminationDBAccess::findAcademicById($value->CAMPUS_ID);
                    if ($facette) {
                        $studentExamCodeObject = StudentExaminationDBAccess::findStudentExamCode($value->ID, '6', $facette->SCHOOL_YEAR);
                        $data[$i]["CANDIDATE_CODE"] = $studentExamCodeObject ? $studentExamCodeObject[0]->EXAM_CODE : '';

                        if ($subjectExam) {
                            foreach ($subjectExam as $subject) {

                                $objectRsult = StudentExaminationDBAccess::getStudentExam($value->ID, $subject->GUID);
                                $data[$i][$subject->EXM_ID] = $objectRsult ? (($objectRsult->POINTS) ? $objectRsult->POINTS : '---') : '---';
                            }
                        }
                        if ($value->ENROLL_AVG >= $value->ENROLL_EXAM_EXPECTED_SCORE) {
                            $data[$i]['RESULT'] = 'PASS';
                        } else {
                            $data[$i]['RESULT'] = 'FALL';
                        }
                    }
                }

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME . " " . $value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME . " " . $value->LASTNAME);
                }

                $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME . " " . $value->FIRSTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["COUNTRY"] = setShowText($value->COUNTRY);
                $data[$i]["ADDRESS"] = setShowText($value->ADDRESS);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["TOWN_CITY"] = setShowText($value->TOWN_CITY);
                $data[$i]["POSTCODE_ZIPCODE"] = setShowText($value->POSTCODE_ZIPCODE);
                $data[$i]["COUNTRY_PROVINCE"] = setShowText($value->COUNTRY_PROVINCE);
                $data[$i]["BIRTH_PLACE"] = setShowText($value->BIRTH_PLACE);
                $data[$i]["ENROLL_AVG"] = $value->ENROLL_AVG ? round($value->ENROLL_AVG, getDecimalPlaces()) : '---';
                if (isset($value->ENROLL_EXAM_NAME))
                    $data[$i]["ENROLL_EXAM_NAME"] = $value->ENROLL_EXAM_NAME;

                $i++;
            }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        if ($isJason) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    ///////////////////////////////////////////////////////
    // Import from XLS
    //////////////////////////////////////////////////////
    public function importXLS($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";
        $educationSystem = isset($params["educationSystem"]) ? addText($params["educationSystem"])  : "0";
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : "0";

        //@veasna
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        //

        $dates = isset($params["CREATED_DATE"]) ? addText($params["CREATED_DATE"])  : "";

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($_FILES["xlsfile"]['tmp_name']);

        for ($iCol = 1; $iCol < $xls->sheets[0]['numCols']; $iCol++) {
            $field = $xls->sheets[0]['cells'][1][$iCol];
            switch ($field) {
                case "STUDENT_SCHOOL_ID":
                    $Col_STUDENT_SCHOOL_ID = $iCol;
                    break;
                case "FIRSTNAME":
                    $Col_FIRSTNAME = $iCol;
                    break;
                case "LASTNAME":
                    $Col_LASTNAME = $iCol;
                    break;
                case "FIRSTNAME_LATIN":
                    $Col_FIRSTNAME_LATIN = $iCol;
                    break;
                case "LASTNAME_LATIN":
                    $Col_LASTNAME_LATIN = $iCol;
                    break;
                case "GENDER":
                    $Col_GENDER = $iCol;
                    break;
                case "ACADEMIC_TYPE":
                    $Col_ACADEMIC_TYPE = $iCol;
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

            //STUDENT_SCHOOL_ID
            $STUDENT_SCHOOL_ID = isset($xls->sheets[0]['cells'][$i + 2][$Col_STUDENT_SCHOOL_ID]) ? $xls->sheets[0]['cells'][$i + 2][$Col_STUDENT_SCHOOL_ID] : "";
            //FIRSTNAME

            $FIRSTNAME = isset($xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME]) ? $xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME] : "";
            //LASTNAME

            $LASTNAME = isset($xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME]) ? $xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME] : "";
            //GENDER

            $FIRSTNAME_LATIN = isset($xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME_LATIN]) ? $xls->sheets[0]['cells'][$i + 2][$Col_FIRSTNAME_LATIN] : "";
            //LASTNAME

            $LASTNAME_LATIN = isset($xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME_LATIN]) ? $xls->sheets[0]['cells'][$i + 2][$Col_LASTNAME_LATIN] : "";
            //GENDER

            $GENDER = isset($xls->sheets[0]['cells'][$i + 2][$Col_GENDER]) ? $xls->sheets[0]['cells'][$i + 2][$Col_GENDER] : "";
            //ACADEMIC_TYPE

            $ACADEMIC_TYPE = isset($xls->sheets[0]['cells'][$i + 2][$Col_ACADEMIC_TYPE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_ACADEMIC_TYPE] : "";
            //DATE_BIRTH

            $DATE_BIRTH = isset($xls->sheets[0]['cells'][$i + 2][$Col_DATE_BIRTH]) ? $xls->sheets[0]['cells'][$i + 2][$Col_DATE_BIRTH] : "";
            //BIRTH_PLACE

            $BIRTH_PLACE = isset($xls->sheets[0]['cells'][$i + 2][$Col_BIRTH_PLACE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_BIRTH_PLACE] : "";
            //EMAIL

            $EMAIL = isset($xls->sheets[0]['cells'][$i + 2][$Col_EMAIL]) ? $xls->sheets[0]['cells'][$i + 2][$Col_EMAIL] : "";
            //PHONE

            $PHONE = isset($xls->sheets[0]['cells'][$i + 2][$Col_PHONE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_PHONE] : "";
            //ADDRESS

            $ADDRESS = isset($xls->sheets[0]['cells'][$i + 2][$Col_ADDRESS]) ? $xls->sheets[0]['cells'][$i + 2][$Col_ADDRESS] : "";
            //COUNTRY

            $COUNTRY = isset($xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY]) ? $xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY] : "";
            //COUNTRY_PROVINCE

            $COUNTRY_PROVINCE = isset($xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY_PROVINCE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_COUNTRY_PROVINCE] : "";
            //TOWN_CITY

            $TOWN_CITY = isset($xls->sheets[0]['cells'][$i + 2][$Col_TOWN_CITY]) ? $xls->sheets[0]['cells'][$i + 2][$Col_TOWN_CITY] : "";
            //POSTCODE_ZIPCODE

            $POSTCODE_ZIPCODE = isset($xls->sheets[0]['cells'][$i + 2][$Col_POSTCODE_ZIPCODE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_POSTCODE_ZIPCODE] : "";
            //error_log($STUDENT_SCHOOL_ID." ### LASTNAME: ".$LASTNAME." FIRSTNAME:".$FIRSTNAME);

            $IMPORT_DATA['ID'] = generateGuid();
            $IMPORT_DATA['CODE'] = createCode();
            $IMPORT_DATA['ACADEMIC_TYPE'] = addText($ACADEMIC_TYPE);

            switch (UserAuth::systemLanguage()) {
                case "VIETNAMESE":
                    $IMPORT_DATA['FIRSTNAME'] = setImportChartset($FIRSTNAME);
                    $IMPORT_DATA['LASTNAME'] = setImportChartset($LASTNAME);
                    $IMPORT_DATA['FIRSTNAME_LATIN'] = setImportChartset($FIRSTNAME_LATIN);
                    $IMPORT_DATA['LASTNAME_LATIN'] = setImportChartset($LASTNAME_LATIN);
                    break;
                default:
                    $IMPORT_DATA['FIRSTNAME'] = addText($FIRSTNAME);
                    $IMPORT_DATA['LASTNAME'] = addText($LASTNAME);
                    $IMPORT_DATA['FIRSTNAME_LATIN'] = setImportChartset($FIRSTNAME_LATIN);
                    $IMPORT_DATA['LASTNAME_LATIN'] = setImportChartset($LASTNAME_LATIN);
                    break;
            }

            $IMPORT_DATA['GENDER'] = addText($GENDER);
            $IMPORT_DATA['BIRTH_PLACE'] = setImportChartset($BIRTH_PLACE);
            $IMPORT_DATA['EMAIL'] = setImportChartset($EMAIL);
            $IMPORT_DATA['PHONE'] = setImportChartset($PHONE);
            $IMPORT_DATA['ADDRESS'] = setImportChartset($ADDRESS);
            $IMPORT_DATA['COUNTRY'] = setImportChartset($COUNTRY);
            $IMPORT_DATA['COUNTRY_PROVINCE'] = setImportChartset($COUNTRY_PROVINCE);
            $IMPORT_DATA['TOWN_CITY'] = setImportChartset($TOWN_CITY);
            $IMPORT_DATA['POSTCODE_ZIPCODE'] = setImportChartset($POSTCODE_ZIPCODE);
            $IMPORT_DATA['STUDENT_SCHOOL_ID'] = addText($STUDENT_SCHOOL_ID);

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

            $IMPORT_DATA['EDUCATION_SYSTEM'] = $educationSystem;
            //error_log($objectId);
            if ($objectId)
                $IMPORT_DATA['CAMPUS'] = $objectId;

            if ($trainingId)
                $IMPORT_DATA['TRAINING'] = $trainingId;
            //$veansa
            if ($type)
                $IMPORT_DATA['TYPE'] = $type;
            //
            if ($dates) {
                $IMPORT_DATA['CREATED_DATE'] = setDatetimeFormat($dates);
            } else {
                $IMPORT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            }

            $IMPORT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            if (isset($STUDENT_SCHOOL_ID) && isset($FIRSTNAME) && isset($LASTNAME)) {
                if ($STUDENT_SCHOOL_ID && $FIRSTNAME && $LASTNAME) {
                    if (!$this->checkSchoolcodeInTemp($STUDENT_SCHOOL_ID)) {
                        self::dbAccess()->insert('t_student_temp', $IMPORT_DATA);
                    }
                }
            }
        }
    }

    //@veasna
    public static function checkStudentExamImportScore($examId, $roomId, $candidateCode) { //check student import score
        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_examination', array("*"));
        $SQL->where('EXAM_ID = ?', $examId);
        $SQL->where('ROOM_ID = ?', $roomId);
        $SQL->where('EXAM_CODE = ?', $candidateCode);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    public static function findAVGStudentEnrollById($studentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_examination', array("C" => "AVG(POINTS)"));
        $SQL->where('STUDENT_ID = ?', $studentId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function importStudentExamScoreXLS($params) {

        $examId = isset($params["examId"]) ? (int) $params["examId"] : "0";
        $roomId = isset($params["roomId"]) ? addText($params["roomId"]) : "0";

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($_FILES["xlsfile"]['tmp_name']);

        for ($iCol = 1; $iCol <= $xls->sheets[0]['numCols']; $iCol++) {
            $field = $xls->sheets[0]['cells'][1][$iCol];
            switch ($field) {
                case "CANDIDATE_CODE":
                    $Col_CANDIDATE_CODE = $iCol;
                    break;
                case "SCORE":
                    $Col_SCORE = $iCol;
                    break;
            }
        }

        for ($i = 1; $i <= $xls->sheets[0]['numRows']; $i++) {

            $CANDIDATE_CODE = isset($xls->sheets[0]['cells'][$i + 2][$Col_CANDIDATE_CODE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_CANDIDATE_CODE] : "";

            $SCORE = isset($xls->sheets[0]['cells'][$i + 2][$Col_SCORE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_SCORE] : "";

            $checkStudentTmp = self::checkStudentExamImportScore($examId, $roomId, $CANDIDATE_CODE);
            //error_log($checkStudentTmp);
            if ($checkStudentTmp) {
                //error_log($SCORE);
                $WHERE = array();
                $IMPORT_DATA['POINTS'] = $SCORE;
                $WHERE[] = "EXAM_CODE = '" . $CANDIDATE_CODE . "'";
                $WHERE[] = "EXAM_ID = '" . $examId . "'";
                self::dbAccess()->update('t_student_examination', $IMPORT_DATA, $WHERE);

                $studentAVG = self::findAVGStudentEnrollById($checkStudentTmp->STUDENT_ID); //find avg of student enroll
                $SAVEDATA = array();
                $SAVEDATA['ENROLL_AVG'] = $studentAVG;
                $HAVING = "ID= '" . $checkStudentTmp->STUDENT_ID . "'";
                self::dbAccess()->update('t_student_temp', $SAVEDATA, $HAVING);
            }
        }
    }

    //

    public function jsonAddTrainingToStudentDB($params) {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";

        $result = $this->importQuery($params);

        if ($selectionIds) {

            $entries = explode(",", $selectionIds);

            $importCount = 0;
            if ($result)
                foreach ($result as $value) {

                    $check = $this->checkStudentBySchoolId($value->STUDENT_SCHOOL_ID);

                    if (in_array($value->ID, $entries) && !$check) {

                        if (Zend_Registry::get('SCHOOL')->SET_DEFAULT_PASSWORD) {
                            $STUDENT_DATA['PASSWORD'] = md5("123-D99A6718-9D2A-8538-8610-E048177BECD5");
                        }

                        $STUDENT_DATA["ID"] = $value->ID;
                        $STUDENT_DATA["CODE"] = $value->CODE;
                        $STUDENT_DATA["LOGINNAME"] = $value->CODE;
                        $STUDENT_DATA["NAME"] = $value->LASTNAME . ", " . $value->FIRSTNAME;
                        $STUDENT_DATA["FIRSTNAME"] = $value->FIRSTNAME;
                        $STUDENT_DATA["LASTNAME"] = $value->LASTNAME;
                        $STUDENT_DATA["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                        $STUDENT_DATA["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                        $STUDENT_DATA["GENDER"] = $value->GENDER;
                        $STUDENT_DATA["DATE_BIRTH"] = $value->DATE_BIRTH;
                        $STUDENT_DATA["ACADEMIC_TYPE"] = $value->ACADEMIC_TYPE;
                        $STUDENT_DATA['CREATED_DATE'] = $value->CREATED_DATE;
                        $STUDENT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                        $STUDENT_DATA["ROLE"] = 'STUDENT';

                        $STUDENT_DATA['BIRTH_PLACE'] = $value->BIRTH_PLACE;
                        $STUDENT_DATA['EMAIL'] = $value->EMAIL;
                        $STUDENT_DATA['PHONE'] = $value->PHONE;
                        //$STUDENT_DATA['PARENT_INFO'] = $value->PARENT_INFO;
                        $STUDENT_DATA['ADDRESS'] = $value->ADDRESS;
                        $STUDENT_DATA['COUNTRY'] = $value->COUNTRY;
                        $STUDENT_DATA['COUNTRY_PROVINCE'] = $value->COUNTRY_PROVINCE;
                        $STUDENT_DATA['TOWN_CITY'] = $value->TOWN_CITY;
                        $STUDENT_DATA['POSTCODE_ZIPCODE'] = $value->POSTCODE_ZIPCODE;
                        $STUDENT_DATA['STUDENT_SCHOOL_ID'] = $value->STUDENT_SCHOOL_ID;

                        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                            $STUDENT_TRAINING_DATA['STATUS'] = 1;
                        }

                        $TRAINING_OBJECT = TrainingDBAccess::findTrainingFromId($value->TRAINING);

                        if ($TRAINING_OBJECT) {

                            switch ($TRAINING_OBJECT->OBJECT_TYPE) {
                                case "CLASS":
                                    $STUDENT_TRAINING_DATA["PROGRAM"] = $TRAINING_OBJECT->PROGRAM;
                                    $STUDENT_TRAINING_DATA["TERM"] = $TRAINING_OBJECT->TERM;
                                    $STUDENT_TRAINING_DATA["LEVEL"] = $TRAINING_OBJECT->LEVEL;
                                    break;
                            }

                            $STUDENT_TRAINING_DATA["STUDENT"] = $value->ID;
                            $STUDENT_TRAINING_DATA["TRAINING"] = $value->TRAINING;
                            $STUDENT_TRAINING_DATA['CREATED_DATE'] = $value->CREATED_DATE;
                            $STUDENT_TRAINING_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                            self::dbAccess()->insert('t_student_training', $STUDENT_TRAINING_DATA);
                        }

                        self::dbAccess()->insert('t_student', $STUDENT_DATA);
                        $this->deleteStudentFromImport($value->ID);

                        $importCount += 1;
                    }
                }
        }

        return array("success" => true, 'selectedCount' => $importCount);
    }

    public function jsonAddStudentToStudentDB($params) {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $result = $this->importQuery($params);

        if ($selectionIds) {

            $entries = explode(",", $selectionIds);

            $importCount = 0;

            if ($result)
                foreach ($result as $value) {

                    $check = $this->checkStudentBySchoolId($value->STUDENT_SCHOOL_ID);

                    if (in_array($value->ID, $entries) && !$check) {

                        ///@veasna  check the enroll_average scores
                        if ($value->TYPE == 'ENROLL') {
                            if ($value->ENROLL_AVG < $value->ENROLL_EXAM_EXPECTED_SCORE) {
                                continue;
                            }
                        }
                        ///////////////////////////

                        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                            $STUDENT_DATA['STATUS'] = 1;
                            $STUDENT_SCHOOLYEAR_DATA['STATUS'] = 1;
                        }

                        $password = '123'; //@Math Man
                        if (Zend_Registry::get('SCHOOL')->SET_DEFAULT_PASSWORD) {
                            $STUDENT_DATA['PASSWORD'] = md5("123-D99A6718-9D2A-8538-8610-E048177BECD5");
                        } else { //@Math Man
                            $password = createpassword();
                            $STUDENT_DATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
                        }

                        $STUDENT_DATA["ID"] = $value->ID;
                        $STUDENT_DATA["CODE"] = $value->CODE;
                        $STUDENT_DATA["LOGINNAME"] = $value->CODE;
                        $STUDENT_DATA["NAME"] = $value->LASTNAME . ", " . $value->FIRSTNAME;
                        $STUDENT_DATA["FIRSTNAME"] = $value->FIRSTNAME;
                        $STUDENT_DATA["LASTNAME"] = $value->LASTNAME;
                        $STUDENT_DATA["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                        $STUDENT_DATA["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                        $STUDENT_DATA["GENDER"] = $value->GENDER;
                        $STUDENT_DATA["DATE_BIRTH"] = $value->DATE_BIRTH;
                        $STUDENT_DATA["ACADEMIC_TYPE"] = $value->ACADEMIC_TYPE;
                        $STUDENT_DATA['CREATED_DATE'] = $value->CREATED_DATE;
                        $STUDENT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                        $STUDENT_DATA["ROLE"] = 'STUDENT';

                        $STUDENT_DATA['BIRTH_PLACE'] = $value->BIRTH_PLACE;
                        $STUDENT_DATA['EMAIL'] = $value->EMAIL;
                        $STUDENT_DATA['PHONE'] = $value->PHONE;
                        $STUDENT_DATA['ADDRESS'] = $value->ADDRESS;
                        $STUDENT_DATA['COUNTRY'] = $value->COUNTRY;
                        $STUDENT_DATA['COUNTRY_PROVINCE'] = $value->COUNTRY_PROVINCE;
                        $STUDENT_DATA['TOWN_CITY'] = $value->TOWN_CITY;
                        $STUDENT_DATA['POSTCODE_ZIPCODE'] = $value->POSTCODE_ZIPCODE;
                        if ($value->TYPE != 'ENROLL') {
                            $STUDENT_DATA['STUDENT_SCHOOL_ID'] = $value->STUDENT_SCHOOL_ID;
                        }

                        self::dbAccess()->insert('t_student', $STUDENT_DATA);

                        $facette = AcademicDBAccess::findGradeFromId($value->CAMPUS_ID);

                        if ($facette) {

                            if ($facette->EDUCATION_SYSTEM) {
                                if ($facette->SUBJECT_ID) {
                                    switch ($facette->OBJECT_TYPE) {
                                        case "SUBJECT":
                                            $STUDENT_SCHOOLYEAR_SUBJECT_DATA["CLASS_ID"] = '';
                                            $STUDENT_SCHOOLYEAR_SUBJECT_DATA["CREDIT_ACADEMIC_ID"] = $facette->ID; //@veasna
                                            break;
                                        case "CLASS":
                                            $STUDENT_SCHOOLYEAR_SUBJECT_DATA["CLASS_ID"] = $facette->ID;
                                            $STUDENT_SCHOOLYEAR_SUBJECT_DATA["CREDIT_ACADEMIC_ID"] = $facette->PARENT; //@veasna
                                            break;
                                    }

                                    $STUDENT_SCHOOLYEAR_SUBJECT_DATA["STUDENT_ID"] = $value->ID;
                                    $STUDENT_SCHOOLYEAR_SUBJECT_DATA["CAMPUS_ID"] = $facette->CAMPUS_ID;
                                    $STUDENT_SCHOOLYEAR_SUBJECT_DATA["SUBJECT_ID"] = $facette->SUBJECT_ID;
                                    $STUDENT_SCHOOLYEAR_SUBJECT_DATA["SCHOOLYEAR_ID"] = $facette->SCHOOL_YEAR;
                                    $STUDENT_SCHOOLYEAR_SUBJECT_DATA['CREATED_DATE'] = $value->CREATED_DATE;
                                    $STUDENT_SCHOOLYEAR_SUBJECT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                                    self::dbAccess()->insert('t_student_schoolyear_subject', $STUDENT_SCHOOLYEAR_SUBJECT_DATA);
                                }
                            } else {
                                switch ($facette->OBJECT_TYPE) {
                                    case "SCHOOLYEAR":
                                        $STUDENT_SCHOOLYEAR_DATA["CLASS"] = "";
                                        break;
                                    case "CLASS":
                                        $STUDENT_SCHOOLYEAR_DATA["CLASS"] = $facette->ID;
                                        break;
                                }

                                $STUDENT_SCHOOLYEAR_DATA["STUDENT"] = $value->ID;
                                $STUDENT_SCHOOLYEAR_DATA["CAMPUS"] = $facette->CAMPUS_ID;
                                $STUDENT_SCHOOLYEAR_DATA["GRADE"] = $facette->GRADE_ID;
                                $STUDENT_SCHOOLYEAR_DATA["ACADEMIC_TYPE"] = $value->ACADEMIC_TYPE;
                                $STUDENT_SCHOOLYEAR_DATA["SCHOOL_YEAR"] = $facette->SCHOOL_YEAR;
                                $STUDENT_SCHOOLYEAR_DATA['CREATED_DATE'] = $value->CREATED_DATE;
                                $STUDENT_SCHOOLYEAR_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                                self::dbAccess()->insert('t_student_schoolyear', $STUDENT_SCHOOLYEAR_DATA);
                            }
                        }
                        if ($value->TYPE == 'ENROLL') {
                            
                        } else {
                            $this->deleteStudentFromImport($value->ID);
                        }
                        $importCount++;
                    }

                    //@Math Man
                    if ($STUDENT_DATA['EMAIL']) {
                        $recipientName = $STUDENT_DATA["LASTNAME"] . " " . $STUDENT_DATA["FIRSTNAME"];
                        if (Zend_Registry::get('SCHOOL')->DISPLAY_POSITION_LASTNAME == 1)
                            $recipientName = $STUDENT_DATA["FIRSTNAME"] . " " . $STUDENT_DATA["LASTNAME"];
                        $result = SchoolDBAccess::getSchool();
                        $subject_email = $result->ACCOUNT_CREATE_SUBJECT;
                        $sendTo = $STUDENT_DATA['EMAIL'];
                        $content_email = $result->SALUTATION_EMAIL . ' ' . $recipientName . ',' . "\r\n";
                        $content_email .= "\r\n" . $result->ACCOUNT_CREATE_NOTIFICATION . "\r\n";
                        $content_email .= SCHOOL . ': ' . $result->NAME . "\r\n";
                        $content_email .= WEBSITE . ': http://' . $_SERVER['SERVER_NAME'] . "\r\n";
                        $content_email .= LOGINNAME . ': ' . $STUDENT_DATA["LOGINNAME"] . "\r\n";
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
            , 'selectedCount' => $importCount);
    }

    //@ THORN Visal
    protected function checkPreschoolBySchoolId($schoolId) {
        $SQL = self::dbAccess()->select()
                ->from("t_student_preschool", array("C" => "COUNT(*)"))
                ->where("STUDENT_SCHOOL_ID = '" . $schoolId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
        //error_log($SQL);
    }

    public function jsonAddPreSchoolStudentDB($params) {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $type = isset($params["type"]) ? addText($params["type"])  : "PRE_SCHOOL";

        if ($selectionIds) {

            $entries = explode(",", $selectionIds);
            $importCount = 0;
            $result = $this->importQuery($params);
            if ($result) {
                //error_log($type);
                foreach ($result as $value) {
                    $check = $this->checkPreschoolBySchoolId($value->STUDENT_SCHOOL_ID);
                    //error_log($check);
                    if (in_array($value->ID, $entries) && !$check) {
                        $STUDENT_DATA["ID"] = generateGuid();
                        $STUDENT_DATA["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                        $STUDENT_DATA["FIRSTNAME"] = $value->FIRSTNAME;
                        $STUDENT_DATA["LASTNAME"] = $value->LASTNAME;
                        $STUDENT_DATA["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                        $STUDENT_DATA["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                        $STUDENT_DATA["GENDER"] = $value->GENDER;
                        $STUDENT_DATA['EMAIL'] = $value->EMAIL;
                        $STUDENT_DATA['PHONE'] = $value->PHONE;
                        $STUDENT_DATA['ADDRESS'] = $value->ADDRESS;

                        $GETDATA['PRESTUDENT'] = $STUDENT_DATA['ID'];
                        $GETDATA['OBJECT_TYPE'] = "REFERENCE";
                        self::dbAccess()->insert('t_student_preschooltype', $GETDATA);
                        self::dbAccess()->insert('t_student_preschool', $STUDENT_DATA);
                        $this->deleteStudentFromImport($value->ID);

                        $importCount += 1;
                    }
                }
            }
        }

        return array("success" => true, 'selectedCount' => $importCount);
    }

    protected function deleteStudentFromImport($removeId) {

        $SQL = "DELETE FROM t_student_temp";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND ID ='" . $removeId . "'";
        self::dbAccess()->query($SQL);
    }

    protected function checkStudent($STUDENT_SCHOOL_ID, $FIRSTNAME, $LASTNAME) {

        $SELECTION = self::dbAccess()->select()
                ->from("t_student_temp", array("C" => "COUNT(*)"))
                ->where("LASTNAME = '" . $LASTNAME . "'")
                ->where("FIRSTNAME = '" . $FIRSTNAME . "'")
                ->where("STUDENT_SCHOOL_ID = '" . $STUDENT_SCHOOL_ID . "'");
        $result = self::dbAccess()->fetchRow($SELECTION);
        return $result ? $result->C : 0;
    }

    public static function checkStudentExam($studentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_examination", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $studentId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonRemoveStudentsFromImport($params) {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";

        $selectedCount = 0;
        if ($selectionIds) {
            $entries = explode(",", $selectionIds);
            $selectedCount = sizeof($entries);
            $i = 0;
            if ($entries)
                foreach ($entries as $studentId) {
                    $success = 'false';
                    $checkStudentInExam = self::checkStudentExam($studentId);
                    if (!$checkStudentInExam) {
                        $SQL = "DELETE FROM t_student_temp";
                        $SQL .= " WHERE 1=1";
                        $SQL .= " AND ID ='" . $studentId . "'";
                        self::dbAccess()->query($SQL);
                        $success = 'true';
                        $i++;
                    }
                }
        }

        return array(
            "success" => $success
            , 'selectedCount' => $i);
    }

    protected function checkStudentBySchoolId($schoolId) {

        $SQL = self::dbAccess()->select()
                ->from("t_student", array("C" => "COUNT(*)"))
                ->where("STUDENT_SCHOOL_ID = '" . $schoolId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///@veasna 
    protected function checkStudentById($Id) {

        $SQL = self::dbAccess()->select()
                ->from("t_student", array("C" => "COUNT(*)"))
                ->where("ID = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///
    protected function getAcademic($Id) {

        $facette = AcademicDBAccess::findGradeFromId($Id);
        $data["NAME"] = "";

        if ($facette) {
            switch ($facette->OBJECT_TYPE) {
                case "SCHOOLYEAR":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_grade'), array("NAME AS SCHOOLYEAR_NAME"));
                    $SQL->joinLeft(array('B' => 't_grade'), 'A.GRADE_ID=B.ID', array("NAME AS GRADE_NAME"));
                    $SQL->joinLeft(array('C' => 't_academicdate'), 'A.SCHOOL_YEAR=C.ID', array());
                    $SQL->where("A.ID ='" . $Id . "'");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchRow($SQL);
                    if ($result) {
                        $data["NAME"] = setShowText($result->GRADE_NAME);
                        $data["NAME"] .= "<br>";
                        $data["NAME"] .= setShowText($result->SCHOOLYEAR_NAME);
                    }
                    break;
                case "SUBJECT":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_grade'), array());
                    $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array("NAME AS SUBJECT_NAME"));
                    $SQL->joinLeft(array('C' => 't_academicdate'), 'A.SCHOOL_YEAR=C.ID', array("NAME AS SCHOOLYEAR_NAME"));
                    $SQL->where("A.ID ='" . $Id . "'");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchRow($SQL);
                    if ($result) {
                        $data["NAME"] = setShowText($result->SUBJECT_NAME);
                        $data["NAME"] .= "<br>";
                        $data["NAME"] .= setShowText($result->SCHOOLYEAR_NAME);
                    }
                    break;
                case "CLASS":

                    if ($facette->EDUCATION_SYSTEM) {
                        $SQL = self::dbAccess()->select();
                        $SQL->from(array('A' => 't_grade'), array("NAME AS CLASS_NAME"));
                        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array("NAME AS SUBJECT_NAME"));
                        $SQL->joinLeft(array('C' => 't_academicdate'), 'A.SCHOOL_YEAR=C.ID', array("NAME AS SCHOOLYEAR_NAME"));
                        $SQL->where("A.ID ='" . $Id . "'");
                        //error_log($SQL->__toString());
                        $result = self::dbAccess()->fetchRow($SQL);
                        if ($result) {
                            $data["NAME"] = setShowText($result->CLASS_NAME) . " (" . setShowText($result->SUBJECT_NAME) . ")";
                            $data["NAME"] .= "<br>";
                            $data["NAME"] .= setShowText($result->SCHOOLYEAR_NAME);
                        }
                    } else {
                        $SQL = self::dbAccess()->select();
                        $SQL->from(array('A' => 't_grade'), array("NAME AS CLASS_NAME"));
                        $SQL->joinLeft(array('B' => 't_academicdate'), 'A.SCHOOL_YEAR=B.ID', array("NAME AS SCHOOLYEAR_NAME"));
                        $SQL->joinLeft(array('C' => 't_grade'), 'A.GRADE_ID=C.ID', array("NAME AS GRADE_NAME"));
                        $SQL->where("A.ID ='" . $Id . "'");
                        //error_log($SQL->__toString());
                        $result = self::dbAccess()->fetchRow($SQL);
                        if ($result) {
                            $data["NAME"] = setShowText($result->CLASS_NAME) . " (" . setShowText($result->GRADE_NAME) . ")";
                            $data["NAME"] .= "<br>";
                            $data["NAME"] .= setShowText($result->SCHOOLYEAR_NAME);
                        }
                    }

                    break;
            }
        }

        return (object) $data;
    }

    protected function getTraining($Id) {

        $facette = TrainingDBAccess::findTrainingFromId($Id);
        $data["NAME"] = "";
        if ($facette) {
            switch ($facette->OBJECT_TYPE) {
                case "TERM":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_training'), array("START_DATE", "END_DATE"));
                    $SQL->joinLeft(array('B' => 't_training'), 'A.LEVEL=B.ID', array("NAME AS LEVEL_NAME"));
                    $SQL->where("A.ID ='" . $Id . "'");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchRow($SQL);
                    if ($result) {
                        $data["NAME"] = setShowText($result->LEVEL_NAME);
                        $data["NAME"] .= "<br>";
                        $data["NAME"] .= getShowDate($result->START_DATE) . " - " . getShowDate($result->END_DATE);
                    }
                    break;
                case "CLASS":
                    $SQL = self::dbAccess()->select();
                    $SQL->from(array('A' => 't_training'), array("NAME AS CLASS_NAME"));
                    $SQL->joinLeft(array('B' => 't_training'), 'A.TERM=B.ID', array("START_DATE", "END_DATE"));
                    $SQL->joinLeft(array('C' => 't_training'), 'A.LEVEL=C.ID', array("NAME AS LEVEL_NAME"));
                    $SQL->where("A.ID ='" . $Id . "'");
                    //error_log($SQL->__toString());
                    $result = self::dbAccess()->fetchRow($SQL);
                    if ($result) {
                        $data["NAME"] = setShowText($result->CLASS_NAME) . " (" . setShowText($result->LEVEL_NAME) . ")";
                        $data["NAME"] .= "<br>";
                        $data["NAME"] .= getShowDate($result->START_DATE) . " - " . getShowDate($result->END_DATE);
                    }
                    break;
            }
        }

        return (object) $data;
    }

    protected function checkSchoolcodeInTemp($codeId) {

        $SELECTION = self::dbAccess()->select()
                ->from("t_student_temp", array("C" => "COUNT(*)"))
                ->where("STUDENT_SCHOOL_ID = '" . $codeId . "'");
        $result = self::dbAccess()->fetchRow($SELECTION);
        return $result ? $result->C : 0;
    }

    protected function checkImportInTemp($firstName, $lastName) {

        $SELECTION = self::dbAccess()->select()
                ->from("t_student_temp", array("C" => "COUNT(*)"))
                ->where("FIRSTNAME = '" . $firstName . "'")
                ->where("LASTNAME = '" . $lastName . "'");
        $result = self::dbAccess()->fetchRow($SELECTION);
        return $result ? $result->C : 0;
    }

    //@Math Man
    public static function checkAllImportInTemp() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_temp", array("C" => "COUNT(*)"));
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonActionChangeStudentImport($params) {

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_student_temp";
        $SQL .= " SET";
        switch ($params["field"]) {
            case "DATE_BIRTH":
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

}

?>