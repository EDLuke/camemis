<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/training/TrainingDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/finance/StudentFeeDBAccess.php';
require_once 'models/app_school/subject/TrainingSubjectDBAccess.php';
require_once 'models/assessment/AssessmentConfig.php';
require_once setUserLoacalization();

class StudentTrainingDBAccess extends TrainingDBAccess {

    public $data = Array();
    //
    public $assignmentObject = null;
    //
    public $trainingSubject = null;
    //
    public $subjectId = null;
    public $trainingObject = null;

    static function getInstance() {

        return new StudentTrainingDBAccess();
    }

    public function __construct($trainingId = false, $subjectId = false, $assignmentId = false) {

        $this->DB_ASSIGNMENT = AssignmentTempDBAccess::getInstance();
        $this->trainingId = $trainingId;
        $this->subjectId = $subjectId;
        $this->assignmentId = $assignmentId;
    }

    public static function makeGrade($score) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '1'");
        $SQL->where("EDUCATION_TYPE = 'training'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $make = "?";
        if ($result) {
            foreach ($result as $value) {
                $FIRST_MATCH = strpos($value->NUMERIC_GRADE, '-');
                $SECOND_MATCH = strpos($value->NUMERIC_GRADE, '<');

                if ($FIRST_MATCH != false) {
                    $explode = explode('-', $value->NUMERIC_GRADE);
                    if (is_array($explode)) {

                        $scoreMin = isset($explode[0]) ? $explode[0] : 0;
                        $scoreMax = isset($explode[1]) ? $explode[1] : 0;

                        if (number_is_between($score, $scoreMin, $scoreMax)) {
                            $make = $value->DESCRIPTION;
                        }
                    }
                } elseif ($SECOND_MATCH != false) {
                    $make = $value->DESCRIPTION;
                }
            }
        }

        if ($score) {
            return $make;
        } else {
            return "";
        }
    }

    public static function findStudentTrainingFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("ID = ?", $Id);
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function findStudentTrainingByStudentId($studentId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("STUDENT = ?", $studentId);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function getLinkStudentAndTraining($studentId, $trainingId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("STUDENT = ?", $studentId);
        $SQL->where("TRAINING = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function sqlStudentScholarship($params) {

        $studentSchoolCode = isset($params["studentSchoolCode"]) ? addText($params["studentSchoolCode"]) : "";
        $code = isset($params["code"]) ? addText($params["code"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $scholarshipType = isset($params["scholarshipType"]) ? addText($params["scholarshipType"]) : "";
        $programId = isset($params["programId"]) ? addText($params["programId"]) : "";
        $termId = isset($params["termId"]) ? addText($params["termId"]) : "";
        $levelId = isset($params["levelId"]) ? addText($params["levelId"]) : "";

        $SELECT_A = array(
            'CODE'
            , 'CODE AS STUDENT_CODE'
            , 'ID AS STUDENT_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );

        $SELECT_B = array(
            'ID AS OBJECT_ID'
            , 'SCHOLARSHIP AS SCHOLARSHIP'
        );

        $SELECT_C = array(
            'NAME AS TRAINING_NAME'
            , 'START_DATE'
            , 'END_DATE'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.STUDENT', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);

        if ($studentSchoolCode)
            $SQL->where("A.STUDENT_SCHOOL_ID LIKE '%" . $studentSchoolCode . "%'");

        if ($code)
            $SQL->where("A.CODE LIKE '%" . $code . "%'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '%" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '%" . $lastname . "%'");

        if ($gender)
            $SQL->where("A.GENDER = '" . $gender . "'");

        if ($scholarshipType)
            $SQL->where("B.SCHOLARSHIP = '" . $scholarshipType . "'");

        if ($programId)
            $SQL->where("B.PROGRAM = '" . $programId . "'");

        if ($termId)
            $SQL->where("B.TERM = '" . $termId . "'");

        if ($levelId)
            $SQL->where("B.LEVEL = '" . $levelId . "'");

        $SQL->where("B.SCHOLARSHIP != 0");

        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public static function sqlStudentTrainingRow($trainingId, $studentId) {

        $TRAINING_OBJECT = self::findTrainingFromId($trainingId);

        $SELECT_A = array(
            'CODE'
            , 'STUDENT_INDEX'
            , 'CODE AS STUDENT_CODE'
            , 'ID'
            , 'ID AS STUDENT_ID'
            , 'STUDENT_SCHOOL_ID'
            , 'CODE'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'FIRSTNAME_LATIN'
            , 'LASTNAME_LATIN'
            , 'CREATED_DATE'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );

        $SELECT_B = array(
            'ID AS OBJECT_ID'
            , 'SCHOLARSHIP AS SCHOLARSHIP'
        );

        $SELECT_C = array(
            'NAME AS TRAINING_NAME'
            , 'START_DATE AS START_DATE'
            , 'END_DATE AS END_DATE'
        );

        /* @soda */
        $SELECT_D = array(
            'NAME AS LEVEL_NAME'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.STUDENT', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=B.LEVEL', $SELECT_D);   /* @soda */

        if ($TRAINING_OBJECT) {
            switch ($TRAINING_OBJECT->OBJECT_TYPE) {
                case "TERM":
                    $SQL->where('B.TERM = ?', $trainingId);
                    break;
                case "LEVEL":
                    $SQL->where('B.LEVEL = ?', $trainingId);
                    break;
                case "CLASS":
                    $SQL->where('B.TRAINING = ?', $trainingId);
                    break;
            }
        }

        if ($studentId)
            $SQL->where("B.STUDENT='" . $studentId . "'");
        $SQL->order('B.SORTKEY DESC');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function sqlStudentTraining($globalSearch, $trainingId, $studentId) {

        $TRAINING_OBJECT = self::findTrainingFromId($trainingId);

        $SELECT_A = array(
            'CODE'
            , 'STATUS_SHORT'
            , 'STATUS_COLOR'
            , 'STATUS_COLOR_FONT'
            , 'STUDENT_INDEX'
            , 'CODE AS STUDENT_CODE'
            , 'ID'
            , 'ID AS STUDENT_ID'
            , 'STUDENT_SCHOOL_ID'
            , 'CODE'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'FIRSTNAME_LATIN'
            , 'LASTNAME_LATIN'
            , 'CREATED_DATE'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );

        $SELECT_B = array(
            'ID AS OBJECT_ID'
            , 'SCHOLARSHIP AS SCHOLARSHIP'
        );

        $SELECT_C = array(
            'NAME AS TRAINING_NAME'
            , 'START_DATE AS START_DATE'
            , 'END_DATE AS END_DATE'
        );

        /* @soda */
        $SELECT_D = array(
            'NAME AS LEVEL_NAME'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.STUDENT', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=B.LEVEL', $SELECT_D);   /* @soda */

        if ($TRAINING_OBJECT) {
            switch ($TRAINING_OBJECT->OBJECT_TYPE) {
                case "TERM":
                    $SQL->where('B.TERM = ?', $trainingId);
                    break;
                case "LEVEL":
                    $SQL->where('B.LEVEL = ?', $trainingId);
                    break;
                case "CLASS":
                    $SQL->where('B.TRAINING = ?', $trainingId);
                    break;
            }
        }

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        if ($studentId)
        //@veasna
            $SQL->where("B.STUDENT='" . $studentId . "'");

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
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //@veasna

    public static function getTrainigByStudentID($studentId) {
        $SELECTION_A = array(
            "ID AS TRAINING_ID"
            , "NAME AS TRAINING_NAME"
        );
        $SELECTION_B = array(
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_training'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.TRAINING', $SELECTION_B);

        $SQL->where("B.STUDENT ='" . $studentId . "'");
        //error_log($SQL->__toString());          
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonStudentByStudentTraining($params) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $trainingId = "";

        $resultRows = "";
        $arr = array();
        $results = self::getTrainigByStudentID($studentId);

        if ($results) {
            foreach ($results as $values) {
                $arr[] = $values->TRAINING_ID;
            }
            $trainingId = implode(",", $arr);
            $resultRows = self::sqlStudentByTraining($globalSearch, $trainingId);
        }

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                //$data[$i]["SORTKEY"] = $value->SORTKEY;
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

    public static function getTrainigByTeacherID($teacherID) {
        $SELECTION_A = array(
            "ID AS TRAINING_ID"
            , "NAME AS TRAINING_NAME"
        );
        $SELECTION_B = array(
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_training'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_teacher_training'), 'A.ID=B.TRAINING', $SELECTION_B);

        $SQL->where("B.TEACHER ='" . $teacherID . "'");
        $SQL->where("A.START_DATE <='" . date("Y-m-d") . "' AND '" . date("Y-m-d") . "'<=A.END_DATE");
        $SQL->where("B.TERM!=0");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function sqlStudentByTraining($globalSearch, $trainingId) {

        $SELECT_A = array(
            'CODE'
            , 'ID'
            , 'ID AS STUDENT_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );

        $SELECT_B = array(
            'ID AS OBJECT_ID'
            , 'SCHOLARSHIP AS SCHOLARSHIP'
        );

        $SELECT_C = array(
            'NAME AS TRAINING_NAME'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.STUDENT', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);

        $SQL->where('B.TRAINING IN (' . $trainingId . ')');

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        $SQL->order('B.SORTKEY DESC');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonStudentTeacherTraining($params) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $trainingId = "";

        $arr = array();
        $results = self::getTrainigByTeacherID($teacherId);

        if ($results) {
            foreach ($results as $values) {
                $arr[] = $values->TRAINING_ID;
            }
            $trainingId = implode(",", $arr);
            $resultRows = self::sqlStudentByTraining($globalSearch, $trainingId);
        }

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                //$data[$i]["SORTKEY"] = $value->SORTKEY;
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

    public static function jsonStudentTraining($params, $isJson = true) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findTrainingFromId($trainingId);
        if ($facette) {
            $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
            $SAVEDATA["TERM"] = $facette->TERM;
            $SAVEDATA["LEVEL"] = $facette->LEVEL;
            $WHERE = self::dbAccess()->quoteInto("TRAINING = ?", $trainingId);
            self::dbAccess()->update('t_student_training', $SAVEDATA, $WHERE);
        }

        $resultRows = self::sqlStudentTraining($globalSearch, $trainingId, $studentId);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                $data[$i]["ID"] = $value->OBJECT_ID;

                $data[$i]["STATUS_KEY"] = $value->STATUS_SHORT;
                $data[$i]["BG_COLOR"] = $value->STATUS_COLOR;
                $data[$i]["BG_COLOR_FONT"] = $value->STATUS_COLOR_FONT;

                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                $data[$i]["LEVEL_NAME"] = setShowText($value->LEVEL_NAME);
                $data[$i]["TERM_NAME"] = getShowDate($value->START_DATE) . '-' . getShowDate($value->END_DATE);
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson == true) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public static function loadStudentTraining($Id) {

        $STUDETN_TRAINING_OBJECT = self::findStudentTrainingFromId($Id);

        $STUDENT_OBJECT = StudentDBAccess::findStudentFromId($STUDETN_TRAINING_OBJECT->STUDENT);

        $data = array();

        $data["CODE_ID"] = $STUDENT_OBJECT->CODE;
        $data["STUDENT_ID"] = $STUDENT_OBJECT->LASTNAME . " " . $STUDENT_OBJECT->FIRSTNAME;
        $data["GENDER_ID"] = getGenderName($STUDENT_OBJECT->GENDER);
        $data["DATE_BIRTH_ID"] = getShowDate($STUDENT_OBJECT->DATE_BIRTH);
        $data["PHONE_ID"] = setShowText($STUDENT_OBJECT->PHONE);
        $data["MOBIL_PHONE_ID"] = setShowText($STUDENT_OBJECT->MOBIL_PHONE);
        $data["EMAIL_ID"] = setShowText($STUDENT_OBJECT->EMAIL);

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function actionRemoveStudentTraining($params) {

        $studentId = isset($params["chooseId"]) ? addText($params["chooseId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        $CONDITION = array("STUDENT='" . $studentId . "'", "TRAINING='" . $trainingId . "'");
        self::dbAccess()->delete('t_student_training', $CONDITION);
        self::dbAccess()->delete('t_student_training_assignment', $CONDITION);

        return array(
            "success" => true
        );
    }

    public static function findStudentFromId($Id) {
        $SQL = self::dbAccess()->select()
                ->from("t_student", array('*'))
                ->where("ID = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonListStudentInSchool($params) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SELECT_A = array(
            'CODE'
            , 'ID AS STUDENT_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );

        $SELECT_C = array(
            'NAME AS TRAINING_NAME'
            , 'START_DATE'
            , 'END_DATE'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_training'), 'A.ID=B.STUDENT', array());
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);

        if ($startDate and $endDate) {
            $SQL->where("C.START_DATE <= '" . $startDate . "'");
            $SQL->where("C.END_DATE >= '" . $endDate . "'");
        }

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        $SQL->group("A.ID");
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
        //echo $SQL->__toString();
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                if (!self::checkEnrollmentCountTrainingByStudentId($value->STUDENT_ID, $trainingId)) {

                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    } else {
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["PHONE"] = setShowText($value->PHONE);
                    $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                    $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                    $data[$i]["CURRENT_CLASS"] = setShowText($value->TRAINING_NAME);
                    $i++;
                }
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

    public static function checkEnrollmentCountTrainingByStudentId($studentId, $trainingId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array("C" => "COUNT(*)"));
        if ($studentId)
            $SQL->where("STUDENT = ?", $studentId);
        if ($trainingId)
            $SQL->where("TRAINING = '" . $trainingId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function actionStudentToTraining($params) {

        $SAVEDATA = array();

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";

        $classObject = self::findTrainingFromId($trainingId);
        $LEVEL_OBJECT = self::findTrainingFromId($classObject->PARENT);
        $TERM_OBJECT = self::findTrainingFromId($LEVEL_OBJECT->PARENT);
        $PROGRAM_OBJECT = self::findTrainingFromId($TERM_OBJECT->PARENT);

        if ($field == "ASSIGNED") {
            if ($newValue) {
                $SAVEDATA["STUDENT"] = $studentId;
                $SAVEDATA["TRAINING"] = $trainingId;
                $SAVEDATA["PROGRAM"] = $PROGRAM_OBJECT->ID;
                $SAVEDATA["TERM"] = $TERM_OBJECT->ID;
                $SAVEDATA["LEVEL"] = $LEVEL_OBJECT->ID;
                self::dbAccess()->insert('t_student_training', $SAVEDATA);
            }
        }

        return array(
            "success" => true
        );
    }

    public static function getStaticTermTraining($studentId, $termId) {

        $SQL = "";
        $SQL .= " SELECT A.LEVEL AS LEVEL_ID, B.START_DATE AS START_DATE, B.END_DATE AS END_DATE";
        $SQL .= " ,C.ID AS CLASS_ID, C.NAME AS TRAINING_NAME";
        $SQL .= " ,D.ID AS LEVEL_ID, D.NAME AS LEVEL_NAME";
        $SQL .= " FROM t_student_training AS A";
        $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TERM";
        $SQL .= " LEFT JOIN t_training AS C ON C.ID=A.TRAINING";
        $SQL .= " LEFT JOIN t_training AS D ON D.ID=A.LEVEL";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.STUDENT='" . $studentId . "'";
        $SQL .= " AND A.TERM='" . $termId . "'";

        //error_log($SQL);
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $DATA = array();
        if ($resultRows) {
            $i = 0;
            foreach ($resultRows as $value) {
                $DATA[$i] = "{";
                $DATA[$i] .= "text:'" . $value->LEVEL_NAME . ": " . $value->TRAINING_NAME . " (" . getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE) . ")'";
                $DATA[$i] .= ",iconCls: 'icon-component_red'";
                $DATA[$i] .= ",url: '/training/studenttraining/?studentId=" . $studentId . "&objectId=" . $value->CLASS_ID . "'";
                $DATA[$i] .= ",cls:'nodeTextBlue'";
                $DATA[$i] .= ",isClick:true";
                $DATA[$i] .= ",leaf: true";
                $DATA[$i] .= "}";
                $i++;
            }
        }

        $output = implode(",", $DATA);
        return $output;
    }

    public static function getStaticTrainingHistory($studentId) {

        $SQL = "";
        $SQL .= " SELECT A.TERM AS TERM_ID, C.NAME AS PROGRAM_NAME";
        $SQL .= " FROM t_student_training AS A";
        $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";
        $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.PROGRAM";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.STUDENT='" . $studentId . "'";
        //error_log($SQL);
        $resultRows = self::dbAccess()->fetchAll($SQL);
        $DATA = array();

        if ($resultRows) {
            $i = 0;
            foreach ($resultRows as $value) {

                $DATA[$i] = "{";
                $DATA[$i] .= "text:'" . stripslashes($value->PROGRAM_NAME) . "'";
                $DATA[$i] .= ",id:'" . $value->TERM_ID . "'";
                $DATA[$i] .= ",iconCls: 'icon-folder_magnify'";
                $DATA[$i] .= ",expanded: true";
                $DATA[$i] .= ",cls:'nodeTextBold'";
                if (self::getStaticTermTraining($studentId, $value->TERM_ID)) {
                    $DATA[$i] .= ",leaf: false";
                    $DATA[$i] .= ",children: [" . self::getStaticTermTraining($studentId, $value->TERM_ID) . "]";
                } else {
                    $DATA[$i] .= ",leaf: true";
                }

                $DATA[$i] .= "}";
                $i++;
            }
        }

        return implode(",", $DATA);
    }

    public static function jsonTreeStudentTrainings($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $facette = self::findTrainingFromId($node);

        if ($studentId) {
            $objectId = $studentId;
        } else {
            $objectId = Zend_Registry::get('USER')->ID;
        }

        $data = array();

        if ($node == 0) {

            $SQL = "";
            $SQL .= " SELECT DISTINCT C.ID AS PROGRAM_ID, C.NAME AS PRGRAM_NAME";
            $SQL .= " FROM t_student_training AS A";
            $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";
            $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.PROGRAM";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND C.NAME<>''";
            $SQL .= " AND A.STUDENT='" . $objectId . "'";

            $resultRows = self::dbAccess()->fetchAll($SQL);
        } else {

            $SQL = "";

            switch ($facette->OBJECT_TYPE) {
                case "PROGRAM":
                    $SQL .= " SELECT DISTINCT C.ID AS LEVEL_ID, C.NAME AS LEVEL_NAME";
                    break;
                case "LEVEL":
                    $SQL .= " SELECT DISTINCT C.ID AS TERM_ID, C.START_DATE AS START_DATE, C.END_DATE AS END_DATE";
                    break;
                case "TERM":
                    $SQL .= " SELECT DISTINCT C.ID AS CLASS_ID, C.NAME AS CLASS_NAME";
                    break;
            }

            $SQL .= " FROM t_student_training AS A";
            $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";

            switch ($facette->OBJECT_TYPE) {
                case "PROGRAM":
                    $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.LEVEL";
                    //@veasna
                    $SQL .= " WHERE 1=1";
                    $SQL .= " AND C.PROGRAM=" . $node;
                    //
                    break;
                case "LEVEL":
                    $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.TERM";
                    //@veasna
                    $SQL .= " WHERE 1=1";
                    $SQL .= " AND C.LEVEL=" . $node;
                    //
                    break;
                case "TERM":
                    $SQL .= " LEFT JOIN t_training AS C ON C.ID=A.TRAINING";
                    //$veasna
                    $SQL .= " WHERE 1=1";
                    $SQL .= " AND C.TERM=" . $node;
                    //
                    break;
            }


            $SQL .= " AND A.STUDENT='" . $objectId . "'";
            //error_log($SQL);
            $resultRows = self::dbAccess()->fetchAll($SQL);
        }

        if ($node == 0) {
            if ($resultRows) {
                foreach ($resultRows as $key => $value) {
                    $data[$key]['id'] = "" . $value->PROGRAM_ID . "";
                    $data[$key]['iconCls'] = "icon-bricks";
                    $data[$key]['text'] = stripslashes($value->PRGRAM_NAME);
                    $data[$key]['leaf'] = false;
                    $data[$key]['cls'] = "nodeTextBold";
                }
            }
        } else {

            switch ($facette->OBJECT_TYPE) {
                case "PROGRAM":
                    foreach ($resultRows as $key => $value) {
                        $data[$key]['id'] = "" . $value->LEVEL_ID . "";
                        $data[$key]['iconCls'] = "icon-folder_magnify";
                        $data[$key]['text'] = stripslashes($value->LEVEL_NAME);
                        $data[$key]['leaf'] = false;
                        $data[$key]['cls'] = "nodeTextBold";
                    }
                    break;
                case "LEVEL":
                    foreach ($resultRows as $key => $value) {
                        //@veasna
                        $data[$key]['id'] = "" . $value->TERM_ID . "";
                        //$data[$key]['classId']= $resultset[0]->CLASS_ID;
                        $data[$key]['iconCls'] = "icon-date";
                        $data[$key]['text'] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                        $data[$key]['leaf'] = false;
                        $data[$key]['cls'] = "nodeTextBold";
                    }
                    break;
                case "TERM":
                    //@veasna 
                    foreach ($resultRows as $key => $value) {

                        $data[$key]['id'] = "" . $value->CLASS_ID . "";
                        $data[$key]['iconCls'] = "icon-blackboard";
                        $data[$key]['text'] = stripslashes($value->CLASS_NAME);
                        $data[$key]['leaf'] = true;
                        $data[$key]['cls'] = "nodeTextBold";
                    }
                    break;
            }
        }
        return $data;
    }

    ////@veng
    public static function jsonStudentSubjectTraining($params, $isJson = true) {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : false;

        $params["trainingId"] = $trainingId;
        $resultRows = self::sqlStudentTraining(false, $trainingId, $studentId);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $key => $value) {

                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["STUDENT"] = setShowText($value->CODE) . ") ";
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] .= setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] .= setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["STUDENT_SCHOOL_ID"] = setShowText($value->STUDENT_SCHOOL_ID);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["TRAINING_NAME"] = setShowText($value->TRAINING_NAME);
                $data[$i]["LEVEL_NAME"] = setShowText($value->LEVEL_NAME);
                $data[$i]["TEAM_NAME"] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["GENDERS"] = getGenderName($value->GENDER);

                $AVERAGE = self::studentTrainingSubjectAverage(
                                $value->STUDENT_ID
                                , $subjectId
                                , $trainingId);

                $data[$i]["AVERAGE"] = $AVERAGE;

                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
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

    public static function getQueryStudentEnrollmentTraining($trainingId, $studentId = false) {

        $params = array();

        if ($trainingId)
            $params["trainingId"] = $trainingId;

        if ($studentId)
            $params["studentId"] = $studentId;

        return self::sqlStudentTraining(false, $trainingId, $studentId);
    }

    public function loadStudentSubjectAssignmentScoreTraining() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training_assignment", Array("*"));
        $SQL->where("ASSIGNMENT = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT = '" . $this->subjectId . "'");
        $SQL->where("TRAINING = '" . $this->trainingId . "'");
        $SQL->where("STUDENT = '" . $this->studentId . "'");
        $SQL->where("SCORE_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonStudentSubjectAssignment($params, $isJson = true) {

        $params = Utiles::setPostDecrypteParams($params);

        $start = isset($params["start"]) ? (int) $params["start"] : 0;
        $limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $this->assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";
        $this->date = isset($params["date"]) ? addText($params["date"]) : "";
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->assignmenObject = $this->getAssignmentObject();
        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubject = $this->getTrainingSubject();
        $this->scoreType = $this->trainingSubject ? $this->trainingSubject->SCORE_TYPE : "";

        $data = Array();

        $entries = self::getQueryStudentEnrollmentTraining(
                        $this->trainingId
                        , false
        );

        if ($entries) {

            $i = 0;
            foreach ($entries as $value) {

                $this->studentId = $value->STUDENT_ID;
                $STUDENT_SCORE = $this->loadStudentSubjectAssignmentScoreTraining();

                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                if ($this->scoreType == 1) {
                    if ($STUDENT_SCORE) {
                        if ($STUDENT_SCORE->SCORE == 0) {
                            $data[$i]["SCORE"] = 0;
                        } else {
                            $data[$i]["SCORE"] = $STUDENT_SCORE->SCORE;
                        }
                        $data[$i]["TEACHER_COMMENTS"] = setShowText($STUDENT_SCORE->TEACHER_COMMENTS);
                    } else {
                        $data[$i]["SCORE"] = '---';
                        $data[$i]["TEACHER_COMMENTS"] = "---";
                    }
                } else {
                    if ($STUDENT_SCORE) {
                        $data[$i]["SCORE"] = $STUDENT_SCORE->SCORE;
                        $data[$i]["TEACHER_COMMENTS"] = setShowText($STUDENT_SCORE->TEACHER_COMMENTS);
                    } else {
                        $data[$i]["SCORE"] = '---';
                        $data[$i]["TEACHER_COMMENTS"] = "---";
                    }
                }

                $i++;
            }
        }

        $a = Array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson) {
            return Array("success" => true, "totalCount" => sizeof($data), "rows" => $a);
        } else {
            return $data;
        }
    }

    /////@veng
    public static function calculate_average($arr) {
        $count = count($arr);
        $total = 0;
        if (is_array($arr)) {

            foreach ($arr as $value) {
                $total = $total + $value;
            }
        }
        $average = ($total / $count);
        return $average;
    }

    public static function average($array) {
        return array_sum($array) / count($array);
    }

    public function jsonActionPublishSubjectAssessmentTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->section = isset($params["section"]) ? addText($params["section"]) : "";

        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubject = $this->getTrainingSubject();

        if ($this->section) {

            $this->scoreList = $this->scoreListSubjectByTraining();
        }

        if ($this->listStudentsByTraining()) {
            foreach ($this->listStudentsByTraining() as $studentObject) {

                if ($this->checkStudentAssignmentTraining($studentObject->STUDENT_ID, $this->subjectId, false))
                    $this->setStudentSubjectAssessmentTraining(
                            $studentObject->STUDENT_ID
                            , $this->subjectId
                            , false
                            , false
                            , $this->scoreList);
            }
        }
        return array(
            "success" => true
        );
    }

    protected function scoreListSubjectByTraining() {

        $data = Array();
        $entries = $this->listStudentsByTraining();

        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $this->studentAvgAllAssignmentsBySubjectTraining(
                        $value->STUDENT_ID
                        , $this->subjectId
                        , false
                        , false
                );
            }
        }
        return $data;
    }

    public function getCountScoreEnterByStudentTraining($studentId, $subjectId, $setIncludeInValuation) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_training_assignment"), Array("COUNT(*) AS C"));
        $SQL->joinLeft(Array('B' => 't_assignment_temp'), 'A.ASSIGNMENT_ID=B.ID', Array());

        if ($subjectId) {
            $SQL->where("A.SUBJECT = ?", $subjectId);
        }

        if ($studentId) {
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        }
        if ($this->trainingId) {
            $SQL->where("A.TRAINING = '" . $this->trainingId . "'");
        }

        if ($setIncludeInValuation) {

            $SQL->where("B.INCLUDE_IN_EVALUATION IN (1)");
        }

        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL->__toString());
        return $result ? $result->C : 0;
    }

    public function jsonActionContentTeacherScoreInputDateTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $comment = isset($params["name"]) ? addText($params["name"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $date = isset($params["date"]) ? addText($params["date"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";

        $SAVEDATA['TEACHER_COMMENTS'] = addText($comment);

        $WHERE = Array();
        $WHERE[] = "STUDENT = '" . $studentId . "'";
        $WHERE[] = "TRAINING = '" . $trainingId . "'";
        $WHERE[] = "SUBJECT = '" . $subjectId . "'";
        $WHERE[] = "ASSIGNMENT = '" . $assignmentId . "'";
        $WHERE[] = "SCORE_DATE = '" . $date . "'";
        self::dbAccess()->update('t_student_training_assignment', $SAVEDATA, $WHERE);

        return Array(
            "success" => true
        );
    }

    public function jsonActionDeleteSingleScoreTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $date = isset($params["date"]) ? addText($params["date"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";

        $WHERE = Array();
        $WHERE[] = self::dbAccess()->quoteInto('STUDENT = ?', $studentId);
        $WHERE[] = self::dbAccess()->quoteInto('ASSIGNMENT = ?', $assignmentId);
        $WHERE[] = self::dbAccess()->quoteInto('SUBJECT = ?', $subjectId);
        $WHERE[] = self::dbAccess()->quoteInto('TRAINING = ?', $trainingId);
        $WHERE[] = self::dbAccess()->quoteInto('SCORE_DATE = ?', $date);
        self::dbAccess()->delete('t_student_training_assignment', $WHERE);

        return Array("success" => true);
    }

    public function jsonActionDeleteAllScoresAssignmentTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $date = isset($params["date"]) ? addText($params["date"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";

        $WHERE_A = Array();
        $WHERE_A[] = self::dbAccess()->quoteInto('ASSIGNMENT = ?', $assignmentId);
        $WHERE_A[] = self::dbAccess()->quoteInto('SUBJECT= ?', $subjectId);
        $WHERE_A[] = self::dbAccess()->quoteInto('TRAINING = ?', $trainingId);
        $WHERE_A[] = self::dbAccess()->quoteInto('SCORE_DATE = ?', $date);
        self::dbAccess()->delete('t_student_training_assignment', $WHERE_A);

        $WHERE_B = Array();
        $WHERE_B[] = self::dbAccess()->quoteInto('ASSIGNMENT_ID = ?', $assignmentId);
        $WHERE_B[] = self::dbAccess()->quoteInto('SUBJECT_ID = ?', $subjectId);
        $WHERE_B[] = self::dbAccess()->quoteInto('TRAINING_ID = ?', $trainingId);
        $WHERE_B[] = self::dbAccess()->quoteInto('SCORE_INPUT_DATE = ?', $date);
        self::dbAccess()->delete('t_student_score_date', $WHERE_B);

        return Array("success" => true);
    }

    public function jsonActionDeleteAllScoresSubjectTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $WHERE = Array();
        $WHERE[] = "TRAINING_ID ='" . $trainingId . "'";
        $WHERE[] = "SUBJECT_ID ='" . $subjectId . "'";

        $WHERE_A = Array();
        $WHERE_A[] = "TRAINING ='" . $trainingId . "'";
        $WHERE_A[] = "SUBJECT='" . $subjectId . "'";

        self::dbAccess()->delete('t_student_training_assignment', $WHERE_A);
        self::dbAccess()->delete('t_student_subject_training_assessment', $WHERE);
        self::dbAccess()->delete('t_student_score_date', $WHERE);

        return Array(
            "success" => true
        );
    }

    public function getStudentSubjectAssessmentTraining($studentId, $subjectId, $actionType = false) {

        $SELECTION_A = Array('SUBJECT_VALUE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_subject_training_assessment"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = ?", $studentId);
        $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("A.TRAINING_ID = '" . $this->trainingId . "'");

        if (!$actionType) {
            $SQL->where("A.ACTION_TYPE = 'ASSESSMENT'");
        } else {
            $SQL->where("A.ACTION_TYPE = '" . $actionType . "'");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function setStudentSubjectAssessmentTraining($studentId, $subjectId, $comment, $assessmentId, $scoreList) {

        $facette = $this->getStudentSubjectAssessmentTraining($studentId, $subjectId);
        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);

        $AVERAGE = $this->studentAvgSubjectTraining($studentId, $subjectId);
        $RANK = AssessmentConfig::findRank($scoreList, $AVERAGE);

        if ($facette) {

            switch ($subjectObject->SCORE_TYPE) {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE)) {
                        $UPDATE_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId) {
                        $UPDATE_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId) {
                $UPDATE_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $UPDATE_DATA["RANK"] = $RANK;
            if ($comment)
                $UPDATE_DATA["TEACHER_COMMENT"] = addText($comment);

            $WHERE[] = "STUDENT_ID = '" . $studentId . "'";
            $WHERE[] = "TRAINING_ID = '" . $this->trainingId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $subjectId . "'";

            $WHERE[] = "ACTION_TYPE = 'ASSESSMENT'";
            self::dbAccess()->update('t_student_subject_training_assessment', $UPDATE_DATA, $WHERE);
        } else {
            $INSERT_DATA["STUDENT_ID"] = $studentId;
            $INSERT_DATA["SUBJECT_ID"] = $subjectId;
            $INSERT_DATA["TRAINING_ID"] = $this->trainingId;

            switch ($subjectObject->SCORE_TYPE) {
                case 1:
                    if (is_numeric($AVERAGE) || is_string($AVERAGE)) {
                        $INSERT_DATA["SUBJECT_VALUE"] = $AVERAGE;
                    }
                    break;
                case 2:
                    if ($assessmentId) {
                        $INSERT_DATA["SUBJECT_VALUE"] = AssessmentConfig::makeGrade($assessmentId, "LETTER_GRADE");
                    }
                    break;
            }

            if ($assessmentId) {
                $INSERT_DATA["ASSESSMENT_ID"] = $assessmentId;
            }

            if ($RANK)
                $INSERT_DATA["RANK"] = $RANK;
            if ($comment)
                $INSERT_DATA["TEACHER_COMMENT"] = addText($comment);

            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";

            self::dbAccess()->insert("t_student_subject_training_assessment", $INSERT_DATA);
        }
    }

    /*     * *******************************************************************
     * Action Student Subject Assessment...
     * ********************************************************************** */

    public function jsonActionStudentSubjectAssessmentTraining($encrypParams, $noJson = false) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->section = isset($params["section"]) ? addText($params["section"]) : "";

        $fieldValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $comment = isset($params["comment"]) ? addText($params["comment"]) : "";

        $this->classObject = $this->getClassObject();
        $this->classSubject = $this->getClassSubject();

        $assessmentId = "";

        if ($field == "ASSESSMENT") {
            $this->studentId = isset($params["id"]) ? addText($params["id"]) : "";
            $assessmentId = $fieldValue;
        }

        if ($comment) {
            $this->studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        }
        $this->setStudentSubjectAssessmentTraining(
                $this->studentId
                , $this->subjectId
                , $comment
                , $assessmentId
                , $this->scoreListSubjectByTraining()
        );

        if (!$noJson) {
            return Array(
                "success" => true
            );
        }
    }

    public function getstudentsSubjectResultTraining() {

        ini_set('memory_limit', '50M');

        $data = Array();

        $entries = $this->listStudentsByTraining();
        $scoreList = $this->scoreListSubjectByTraining();
        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {

                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                //Show Average...
                $AVERAGE = $this->studentAvgSubjectTraining($value->STUDENT_ID, $this->subjectId);
                $data[$i]["AVERAGE"] = $AVERAGE;
                // Show Rank
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE);
                //Show assignment score implode..
                if ($this->listAssignmentsByTraining()) {
                    foreach ($this->listAssignmentsByTraining() as $assignment) {
                        $data[$i][$assignment->ASSIGNMENT_ID] = $this->getImplodeStudentAssignmentTraining(
                                $value->STUDENT_ID
                                , $this->subjectId
                                , $assignment->ASSIGNMENT_ID
                        );
                    }
                }
                $i++;
            }
        }


        $a = Array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return Array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function getImplodeStudentAssignmentTraining($studentId, $subjectId, $assignmentId) {

        $data = Array();
        $entries = $this->getSQLStudentAssignmentTraining(
                $studentId
                , $subjectId
                , $assignmentId
                , false);
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $value->SCORE;
            }
        }
        return $data ? implode('|', $data) : "---";
    }

    public function getSQLStudentAssignmentTraining($studentId, $subjectId, $assignmentId, $setIncludeInValuation = false) {

        $SELECTION_A = Array(
            "SCORE"
            , "TEACHER_COMMENTS"
            , "CALCULATED_POINTS"
        );

        $SELECTION_B = Array(
            "COEFF_VALUE"
            , "NAME AS ASSIGNMENT_NAME"
            , "EVALUATION_TYPE"
        );
        $SELECTION_C = Array(
            "INCLUDE_IN_EVALUATION"
        );
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_training_assignment"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_assignment_temp'), 'A.ASSIGNMENT=B.ID', $SELECTION_B);
        $SQL->joinLeft(Array('C' => 't_training_subject'), 'B.ID=C.ASSIGNMENT', $SELECTION_C);

        if ($assignmentId) {
            $SQL->where("A.ASSIGNMENT = '" . $assignmentId . "'");
        }

        if ($subjectId) {
            $SQL->where("A.SUBJECT= '" . $subjectId . "'");
        }

        if ($studentId) {
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        }
        if ($this->trainingId) {
            $SQL->where("A.TRAINING = '" . $this->trainingId . "'");
        }

        if ($setIncludeInValuation) {

            $SQL->where("C.INCLUDE_IN_EVALUATION IN (1)");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonSubjectResultTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->start = isset($params["start"]) ? (int) $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubject = $this->getTrainingSubject();
        $this->scoreType = $this->trainingSubject ? $this->trainingSubject->SCORE_TYPE : "";
        $this->section = 1;

        return $this->getstudentsSubjectResultTraining();
    }

    public function jsonListStudentsClassPerformanceTraining($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $this->section = isset($params["section"]) ? addText($params["section"]) : "";
        $this->start = isset($params["start"]) ? (int) $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? (int) $params["limit"] : 100;
        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubject = $this->getTrainingSubject();

        return $this->resultClassPerformanceTraining();
    }

    public function resultClassPerformanceTraining() {

        $data = array();

        $entries = $this->listStudentsByTraining();
        $scoreList = $this->scoreListClassPerformanceTraining();
        if ($entries) {

            $i = 0;
            foreach ($entries as $value) {
                $this->studentId = $value->STUDENT_ID;
                $data[$i]["ID"] = $value->STUDENT_ID;
                $data[$i]["CODE"] = setShowText($value->STUDENT_CODE);

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }

                $AVERAGE_TOTAL = $this->getAvgClassPerformanceTraining(
                        $value->STUDENT_ID
                );

                $data[$i]["AVERAGE"] = $AVERAGE_TOTAL;
                $data[$i]["RANK"] = AssessmentConfig::findRank($scoreList, $AVERAGE_TOTAL);

                if ($this->listSubjectsTraining()) {
                    foreach ($this->listSubjectsTraining() as $subjectObject) {
                        $SUBJECT_ASSESSMENT = $this->getStudentSubjectAssessmentTraining(
                                $value->STUDENT_ID
                                , $subjectObject->SUBJECT_ID
                                , false);

                        $data[$i][$subjectObject->SUBJECT_ID] = $SUBJECT_ASSESSMENT ? $SUBJECT_ASSESSMENT->SUBJECT_VALUE : "---";
                    }
                }
                $i++;
            }
        }

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    protected function getAvgClassPerformanceTraining($studentId) {

        $entries = $this->getSQLClassPerformanceTraining($studentId);

        $sumAvg = "";
        $sumCoeff = "";
        $result = "---";

        if ($entries) {
            foreach ($entries as $value) {
                if ($value->SCORE_TYPE == 1) {
                    if (is_numeric($value->SUBJECT_VALUE)) {
                        if ($value->COEFF_VALUE) {
                            $sumAvg += $value->SUBJECT_VALUE * $value->COEFF_VALUE;
                            $sumCoeff += $value->COEFF_VALUE;
                        }
                    }
                }
            }
        }
        if (is_numeric($sumAvg) && is_numeric($sumCoeff)) {
            $result = displayRound($sumAvg / $sumCoeff);
        }

        return $result;
    }

    protected function scoreListClassPerformanceTraining() {

        $data = Array();
        $entries = $this->listStudentsByTraining();
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = $this->getAvgClassPerformanceTraining($value->STUDENT_ID, false);
            }
        }
        return $data;
    }

    protected function getSQLClassPerformanceTraining($studentId) {
        $SELECTION_A = Array(
            'SUBJECT_VALUE'
            , 'SUBJECT_ID'
            , 'RANK'
        );

        $SELECTION_B = Array(
            'INCLUDE_IN_EVALUATION'
            , 'SCORE_TYPE'
            , 'COEFF_VALUE'
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_subject_training_assessment"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_training_subject'), 'A.SUBJECT_ID=B.SUBJECT', $SELECTION_B);

        if ($studentId) {
            $SQL->where("A.STUDENT_ID = ?", $studentId);
        }

        if ($this->trainingId) {
            $SQL->where("A.TRAINING_ID = '" . $this->trainingId . "'");
        }


        $SQL->group("A.SUBJECT_ID");
        // error_log($SQL);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function checkStudentAssignmentTraining($studentId, $subjectId, $setInclude) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_training_assignment"), Array("COUNT(*) AS C"));
        $SQL->joinLeft(Array('B' => 't_assignment_temp'), 'A.ASSIGNMENT=B.ID', Array());

        if ($subjectId) {
            $SQL->where("A.SUBJECT = ?", $subjectId);
        }

        if ($studentId) {
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        }
        if ($this->trainingId) {
            $SQL->where("A.TRAINING = '" . $this->trainingId . "'");
        }

        if ($setInclude) {
            $SQL->where("B.INCLUDE_IN_EVALUATION IN (0,2)");
        }
        $SQL->group("A.STUDENT");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL->__toString());
        return $result ? $result->C : 0;
    }

    public function getSQLAvgStudentAssignmentTraining($studentId, $subjectId, $assignmentId) {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_training_assignment"), Array('AVG(SCORE) AS AVG'));
        $SQL->joinLeft(Array('B' => 't_assignment_temp'), 'A.ASSIGNMENT=B.ID', Array());

        if ($assignmentId) {
            $SQL->where("A.ASSIGNMENT = '" . $assignmentId . "'");
        }

        if ($subjectId) {
            $SQL->where("A.SUBJECT= '" . $subjectId . "'");
        }

        if ($studentId) {
            $SQL->where("A.STUDENT = '" . $studentId . "'");
        }
        if ($this->trainingId) {
            $SQL->where("A.TRAINING = '" . $this->trainingId . "'");
        }


        $SQL->group('A.ASSIGNMENT');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->AVG : 0;
    }

    public function getAllAssignmentsInScoreInputDateTraining($subjectId = false, $assignmentId = false, $setInclude = false) {

        $SELECTION_A = Array(
            'ASSIGNMENT_ID'
            , 'SUBJECT_ID'
        );
        $SELECTION_B = Array('COEFF_VALUE');

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(Array('A' => "t_student_score_date"), $SELECTION_A);
        $SQL->joinLeft(Array('B' => 't_assignment_temp'), 'A.ASSIGNMENT_ID=B.ID', $SELECTION_B);

        if ($assignmentId) {
            $SQL->where("A.ASSIGNMENT_ID = '" . $assignmentId . "'");
        } else {
            if ($assignmentId)
                $SQL->where("A.ASSIGNMENT_ID = '" . $assignmentId . "'");
        }

        if ($subjectId) {
            $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        } else {
            if ($this->subjectId)
                $SQL->where("A.SUBJECT_ID = '" . $this->subjectId . "'");
        }

        if ($this->trainingId)
            $SQL->where("A.TRAINING_ID = '" . $this->trainingId . "'");

        if ($setInclude) {
            $SQL->where("B.INCLUDE_IN_EVALUATION IN (0,2)");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function studentAvgSubjectTraining($studentId, $subjectId) {

        $CHECK = $this->checkStudentAssignmentTraining($studentId, $subjectId, false);
        if ($CHECK) {
            return $this->studentAvgAllAssignmentsBySubjectTraining(
                            $studentId
                            , $subjectId
                            , false
                            , false
            );
        } else {
            return "---";
        }
    }

    protected function studentAvgAllAssignmentsBySubjectTraining($studentId, $subjectId, $assignmentId, $setInclude = false) {

        $result = "";
        $SUM_COUNT = "";
        $SUM_CALCULATED = "";

        $entries = $this->getAllAssignmentsInScoreInputDateTraining(
                $subjectId
                , $assignmentId
                , $setInclude);

        if ($this->trainingObject->EVALUATION_TYPE == 0) {
            if ($entries) {
                foreach ($entries as $value) {
                    if ($value->COEFF_VALUE) {

                        $AVG = $this->getSQLAvgStudentAssignmentTraining(
                                $studentId
                                , $subjectId
                                , $value->ASSIGNMENT_ID);
                        if (is_numeric($AVG)) {
                            $SUM_CALCULATED += $AVG * $value->COEFF_VALUE;
                            $SUM_COUNT += $value->COEFF_VALUE;
                        }
                    }
                }
                if ($SUM_COUNT)
                    $result = $SUM_CALCULATED / $SUM_COUNT;
            }
        } elseif ($this->trainingObject->EVALUATION_TYPE == 1) {
            if ($this->check100Percent(
                            $studentId
                            , $subjectId
                            , $assignmentId)
            ) {

                if ($entries) {
                    foreach ($entries as $value) {
                        if ($value->COEFF_VALUE) {
                            $AVG = $this->getSQLAvgStudentAssignmentTraining(
                                    $studentId
                                    , $subjectId
                                    , $value->ASSIGNMENT_ID);
                            if (is_numeric($AVG)) {
                                $SUM_CALCULATED += ($AVG * $value->COEFF_VALUE) / 100;
                            }
                        }
                    }

                    $result = $SUM_CALCULATED;
                }
            } else {
                if ($entries) {
                    foreach ($entries as $value) {
                        if ($value->COEFF_VALUE) {
                            $AVG = $this->getSQLAvgStudentAssignmentTraining(
                                    $studentId
                                    , $subjectId
                                    , $value->ASSIGNMENT_ID);
                            if (is_numeric($AVG)) {
                                $SUM_CALCULATED += $AVG * $value->COEFF_VALUE;
                                $SUM_COUNT += $value->COEFF_VALUE;
                            }
                        }
                    }
                    $result = $SUM_CALCULATED / $SUM_COUNT;
                }
            }
        }

        return displayRound($result);
    }

    public static function jsonStudentTrainingAssessment($params) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $resultRows = self::sqlStudentTraining(false, $trainingId, false);
        $scoreList = ""; // $this->scoreListSubjectByTraining();
        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $key => $value) {

                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["STUDENT"] = "(" . setShowText($value->CODE) . ") ";
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT"] .= setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["STUDENT"] .= setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $ENTRIES_SUBJECT = TrainingSubjectDBAccess::getTrainingClassSubject(
                                $trainingId
                );

                if ($ENTRIES_SUBJECT) {
                    foreach ($ENTRIES_SUBJECT as $key => $subject) {

                        $AVERAGE = self::studentTrainingSubjectAverage(
                                        $value->STUDENT_ID
                                        , $subject->ID
                                        , $trainingId);

                        $data[$i]["SUBJECT_" . $subject->ID . ""] = $AVERAGE ? $AVERAGE : "---";
                    }
                }

                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

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

    ///////////////@veng

    public function getTrainingSubject() {

        if ($this->subjectId && $this->getTrainingObject()) {
            return TrainingSubjectDBAccess::findSubjectTraining($this->subjectId, $this->getTrainingObject());
        }
    }

    public function getTrainingObject() {
        return TrainingDBAccess::findTrainingFromId($this->trainingId);
    }

    public function getAssignmentObject() {

        return AssignmentTempDBAccess::findAssignmentJoinCategory($this->assignmentId);
    }

    public function listStudentsByTraining($globalSearch = false) {
        return self::getQueryStudentEnrollmentTraining($this->trainingId, false);
    }

    public function listSubjectsTraining() {

        return TrainingSubjectDBAccess::getListSubjectsForAssessmentTraining($this->trainingId);
    }

    public function listAssignmentsByTraining() {

        return $this->DB_ASSIGNMENT->getListAssignmentsForAssessmentTraining($this->trainingId, $this->subjectId);
    }

    public function actionTrainingStudentAssignment($params) {

        $params = Utiles::setPostDecrypteParams($params);

        $this->scoreInput = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $this->studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $this->trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $this->assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";

        $this->subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $this->date = isset($params["date"]) ? addText($params["date"]) : "";
        $this->assignmenObject = AssignmentTempDBAccess::findAssignmentJoinCategory();
        $this->trainingObject = $this->getTrainingObject();
        $this->trainingSubject = $this->getTrainingSubject();
        $this->maxScore = $this->trainingSubject ? $this->trainingSubject->SCORE_MAX : "";
        $this->scoreType = $this->trainingSubject ? $this->trainingSubject->SCORE_TYPE : "";
        $this->teacherId = Zend_Registry::get('USER')->ID;

        if ($this->date) {
            $explode = explode('-', $this->date);
        }

        $ERROR = 0;
        $SCHORE_DATE = 0;

        if ($this->assignmenObject) {

            if ($this->scoreType == 1) {

                if ($this->scoreInput <= $this->maxScore) {

                    $ERROR = 0;
                } else {

                    $ERROR = 1;
                }
            } else {
                $ERROR = 0;
            }
        } else {
            $ERROR = 1;
        }

        if (!$ERROR) {
            $this->setStudentScoreSubjectAssignment();
            $SCHORE_DATE = $this->getCountScoreInputDate();
        } else {
            $this->setStudentScoreSubjectAssignment();
            $SCHORE_DATE = $this->getCountScoreInputDate();
        }

        return Array(
            "success" => true
            , "ERROR" => $ERROR
            , "SCHORE_DATE" => $SCHORE_DATE
        );
    }

    protected function setStudentScoreSubjectAssignment() {

        $SAVEDATA = Array();

        $SAVEDATA["SCORE"] = $this->scoreInput;

        if ($this->checkStudentScoreSubjectAssignment()) {
            $WHERE[] = "ASSIGNMENT = '" . $this->assignmentId . "'";
            $WHERE[] = "SUBJECT= '" . $this->subjectId . "'";
            $WHERE[] = "STUDENT = '" . $this->studentId . "'";
            $WHERE[] = "TRAINING= '" . $this->trainingId . "'";
            $WHERE[] = "SCORE_DATE = '" . $this->date . "'";
            self::dbAccess()->update('t_student_training_assignment', $SAVEDATA, $WHERE);
        } else {

            $SAVEDATA["ASSIGNMENT"] = $this->assignmentId;
            $SAVEDATA["STUDENT"] = $this->studentId;
            $SAVEDATA["SUBJECT"] = $this->subjectId;
            $SAVEDATA["TRAINING"] = $this->trainingId;
            $SAVEDATA["SCORE_DATE"] = $this->date;
            $SAVEDATA["SCORE_TYPE"] = $this->scoreType;
            $SAVEDATA["TEACHER"] = $this->teacherId;
            self::dbAccess()->insert("t_student_training_assignment", $SAVEDATA);
            $this->addScoreDate();
        }
    }

    protected function checkStudentScoreSubjectAssignment() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training_assignment", Array("C" => "COUNT(*)"));
        $SQL->where("ASSIGNMENT = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT = '" . $this->subjectId . "'");
        $SQL->where("TRAINING= '" . $this->trainingId . "'");
        $SQL->where("STUDENT= '" . $this->studentId . "'");
        $SQL->where("SCORE_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function getCountScoreInputDate() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", Array("C" => "COUNT(*)"));
        $SQL->where("ASSIGNMENT_ID = '" . $this->assignmentId . "'");
        $SQL->where("SUBJECT_ID= '" . $this->subjectId . "'");
        $SQL->where("TRAINING_ID = '" . $this->trainingId . "'");
        $SQL->where("SCORE_INPUT_DATE = '" . $this->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    protected function addScoreDate() {

        if (!$this->getCountScoreInputDate()) {
            $SAVEDATA = Array();
            $SAVEDATA["ASSIGNMENT_ID"] = $this->assignmentId;
            $SAVEDATA["SUBJECT_ID"] = $this->subjectId;
            $SAVEDATA["TRAINING_ID"] = $this->trainingId;
            $SAVEDATA["SCORE_INPUT_DATE"] = $this->date;
            self::dbAccess()->insert("t_student_score_date", $SAVEDATA);
        }
    }

    public static function loadScoreStudentTraining($studentId, $training, $subjectId, $asssignmentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training_assignment");
        $SQL->where("STUDENT = ?", $studentId);
        $SQL->where("TRAINING = '" . $training . "'");
        $SQL->where("SUBJECT = ?", $subjectId);
        $SQL->where("ASSIGNMENT = '" . $asssignmentId . "'");
        //error_log($SQL->__toString());
        $stmt = self::dbAccess()->query($SQL);
        $result = $stmt->fetch();
        return $result ? $result->SCORE : "";
    }

    ///////////////
    public static function studentTrainingSubjectAverage($studentId, $subjectId, $trainingId) {

        $SELECTION = array(
            "SUM_WEIGHTING" => "SUM(B.WEIGHTING)"
            , "SUM_SCORE" => "SUM(A.SCORE*B.WEIGHTING)"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training_assignment'), $SELECTION);
        $SQL->joinLeft(array('B' => 't_assignment_temp'), 'B.ID=A.ASSIGNMENT', array());
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        $SQL->where("A.SUBJECT = ?", $subjectId);
        $SQL->where("A.TRAINING = '" . $trainingId . "'");

        $result = self::dbAccess()->fetchRow($SQL);

        $stmp = "";

        if ($result) {

            if ($result->SUM_SCORE && $result->SUM_WEIGHTING) {
                $VALUE = $result->SUM_SCORE / $result->SUM_WEIGHTING;
                $stmp = round($VALUE, 0);
            }
        }

        return $stmp;
    }

    public static function jsonAssessemntByTrainingSubjects($params) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        $TRAINING_OBJECT = self::findTrainingFromId($trainingId);
        $params["trainingId"] = $TRAINING_OBJECT->PARENT;
        $resultRows = TrainingSubjectDBAccess::sqlAssignedSubjectsByTraining($params);

        if ($resultRows) {
            foreach ($resultRows as $key => $value) {

                $data[$key]["ID"] = $value->SUBJECT_ID;
                $data[$key]["SUBJECT"] = setShowText($value->NAME);
                $AVERAGE = self::studentTrainingSubjectAverage(
                                $studentId
                                , $value->SUBJECT_ID
                                , $trainingId);

                $data[$key]["AVERAGE"] = $AVERAGE ? $AVERAGE : "---";
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

    public static function listStudentTrainings($params) {

        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_training'), array('ID AS CLASS_ID', 'NAME AS CLASS_NAME'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TERM', array('START_DATE', 'END_DATE'));
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.LEVEL', array('NAME AS LEVEL_NAME'));
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=A.PROGRAM', array('NAME AS PROGRAMM_NAME'));
        $SQL->where("A.PARENT = ?", $parentId);
        //echo $SQL->__toString();
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($resultRows) {
            foreach ($resultRows as $key => $value) {

                $data[$key]["ID"] = $value->CLASS_ID;
                $data[$key]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                $data[$key]["TERM_NAME"] = getShowDate($value->START_DATE) . "-" . getShowDate($value->END_DATE);
                $data[$key]["LEVEL_NAME"] = setShowText($value->LEVEL_NAME);
                $data[$key]["PROGRAMM_NAME"] = setShowText($value->PROGRAMM_NAME);

                if (self::checkEnrollmentCountTrainingByStudentId($studentId, $value->CLASS_ID)) {
                    $data[$key]["ASSIGNED"] = 1;
                } else {
                    $data[$key]["ASSIGNED"] = 0;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function getCurrentTrainingByTerm($studentId, $termId) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training'), array('TRAINING AS ID'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TRAINING', array());
        $SQL->where("B.PARENT = '" . $termId . "'");
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        $SQL->group('A.STUDENT');
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->ID : false;
    }

    public static function actionStudentTrainingTransfer($params) {

        $SAVEDATA = array();

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $chooseId = isset($params["id"]) ? addText($params["id"]) : "0";

        $currentTraning = self::getCurrentTrainingByTerm($studentId, $parentId);

        if ($currentTraning) {
            $WHERE[] = "STUDENT='" . $studentId . "'";
            $WHERE[] = "TRAINING='" . $currentTraning . "'";
            $SAVEDATA["STUDENT"] = $studentId;
            $SAVEDATA["TRAINING"] = $chooseId;
            self::dbAccess()->update('t_student_training', $SAVEDATA, $WHERE);
            self::dbAccess()->update('t_student_training_assignment', $SAVEDATA, $WHERE);
        }
        return array(
            "success" => true
        );
    }

    public static function actionStudentTrainingDescription($params) {

        $SAVEDATA = array();
        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_personal_description", array('*'));
        $SQL->where("PERSON='STUDENT_TRAINING'");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());

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
        $SAVEDATA["DESCRIPTION"] = implode(",", $CHOOSE_DATA);

        $WHERE = array();
        $WHERE[] = "STUDENT = '" . $studentId . "'";
        $WHERE[] = "TRAINING = '" . $objectId . "'";
        self::dbAccess()->update("t_student_training", $SAVEDATA, $WHERE);

        return array(
            "success" => true
        );
    }

    public static function loadStudentTrainingDescripton($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $facette = self::getLinkStudentAndTraining($studentId, $objectId);
        $data = array();

        if ($facette) {

            $CHECK_DATA = explode(",", $facette->DESCRIPTION);
            $SQL = self::dbAccess()->select();
            $SQL->from("t_personal_description", array('*'));
            $SQL->where("PERSON='STUDENT_TRAINING'");
            $result = self::dbAccess()->fetchAll($SQL);

            if ($result) {
                foreach ($result as $value) {

                    $descriptionObject = DescriptionDBAccess::findObjectFromId($value->ID);
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

    public static function getStudentTraining($studentId, $trainingId, $searchIndex = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("STUDENT = ?", $studentId);

        if ($searchIndex == "TERM") {
            $SQL->where("TERM = '" . $trainingId . "'");
        } else {
            $SQL->where("TRAINING = '" . $trainingId . "'");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadStudentTraining($trainingId, $studentId) {

        $data = self::getTrainingDataFromId($trainingId);
        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function getCurrentTrainingsByStudent($studentId) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training'), array('TRAINING AS ID'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TRAINING', array("NAME AS CLASS_NAME"));
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.TERM', array("START_DATE", "END_DATE"));
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=A.LEVEL', array("NAME AS LEVEL_NAME"));
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=A.PROGRAM', array("NAME AS PROGRAM_NAME"));
        $SQL->where("NOW()<=C.END_DATE");
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        //echo $SQL->__toString();
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getCurrentStudentTraining($studentId) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training'), array('TRAINING AS TRAINING_ID'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TRAINING', array("NAME AS CLASS_NAME"));
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.TERM', array("START_DATE", "END_DATE"));
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=A.LEVEL', array("NAME AS LEVEL_NAME"));
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=A.PROGRAM', array("NAME AS PROGRAM_NAME"));
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

}

?>