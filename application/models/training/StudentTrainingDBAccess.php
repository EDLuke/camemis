<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.08.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/training/TrainingDBAccess.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/finance/StudentFeeDBAccess.php";
require_once "models/training/TrainingSubjectDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/assignment/AssignmentTempDBAccess.php"; //@CHHE Vathana
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

    static function getInstance()
    {

        return new StudentTrainingDBAccess();
    }

    public function __construct($trainingId = false, $subjectId = false, $assignmentId = false)
    {

        $this->DB_ASSIGNMENT = AssignmentTempDBAccess::getInstance();
        $this->trainingId = $trainingId;
        $this->subjectId = $subjectId;
        $this->assignmentId = $assignmentId;
    }

    public static function findStudentTrainingFromId($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("ID = ?", $Id);
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function findStudentTrainingByStudentId($studentId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("STUDENT = ?", $studentId);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function getLinkStudentAndTraining($studentId, $trainingId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("STUDENT = ?", $studentId);
        $SQL->where("TRAINING = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function sqlStudentScholarship($params)
    {

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

    public static function sqlStudentTrainingRow($trainingId, $studentId)
    {

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
            , 'PROGRAM'
            , 'LEVEL'
            , 'TERM'
            , 'TRAINING'
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
        $SQL->where('B.IS_TRANSFER = ?', 0);
        if ($TRAINING_OBJECT)
        {
            switch ($TRAINING_OBJECT->OBJECT_TYPE)
            {
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

    public static function sqlStudentTraining($globalSearch, $trainingId, $studentId, $isTransfer = false)
    {

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

        if ($TRAINING_OBJECT)
        {
            switch ($TRAINING_OBJECT->OBJECT_TYPE)
            {
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

            if ($isTransfer)
            {
                $SQL->where('B.IS_TRANSFER = ?', 1);
            }
            else
            {
                $SQL->where('B.IS_TRANSFER = ?', 0);
            }
        }


        if ($globalSearch)
        {
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

        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY)
        {
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
        return self::dbAccess()->fetchAll($SQL);
    }

    //@veasna

    public static function getTrainigByStudentID($studentId)
    {
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

    public static function jsonStudentByStudentTraining($params)
    {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $trainingId = "";

        $resultRows = "";
        $arr = array();
        $results = self::getTrainigByStudentID($studentId);

        if ($results)
        {
            foreach ($results as $values)
            {
                $arr[] = $values->TRAINING_ID;
            }
            $trainingId = implode(",", $arr);
            $resultRows = self::sqlStudentByTraining($globalSearch, $trainingId);
        }

        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

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
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function getTrainigByTeacherID($teacherID)
    {
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

    public static function sqlStudentByTraining($globalSearch, $trainingId)
    {

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

        if ($globalSearch)
        {
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

    public static function jsonStudentTeacherTraining($params)
    {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $trainingId = "";

        $arr = array();
        $results = self::getTrainigByTeacherID($teacherId);

        if ($results)
        {
            foreach ($results as $values)
            {
                $arr[] = $values->TRAINING_ID;
            }
            $trainingId = implode(",", $arr);
            $resultRows = self::sqlStudentByTraining($globalSearch, $trainingId);
        }

        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

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
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function jsonStudentTraining($params, $isJson = true)
    {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findTrainingFromId($trainingId);
        if ($facette)
        {
            $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
            $SAVEDATA["TERM"] = $facette->TERM;
            $SAVEDATA["LEVEL"] = $facette->LEVEL;
            $WHERE = self::dbAccess()->quoteInto("TRAINING = ?", $trainingId);
            self::dbAccess()->update('t_student_training', $SAVEDATA, $WHERE);
        }

        $resultRows = self::sqlStudentTraining($globalSearch, $trainingId, $studentId);

        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

                $data[$i]["ID"] = $value->OBJECT_ID;

                $data[$i]["STATUS_KEY"] = $value->STATUS_SHORT;
                $data[$i]["BG_COLOR"] = $value->STATUS_COLOR;
                $data[$i]["BG_COLOR_FONT"] = $value->STATUS_COLOR_FONT;

                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
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
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson == true)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        }
    }

    public static function loadStudentTraining($Id)
    {

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

    public static function actionRemoveStudentTraining($params)
    {

        $studentId = isset($params["chooseId"]) ? addText($params["chooseId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        $CONDITION = array("STUDENT='" . $studentId . "'", "TRAINING='" . $trainingId . "'");
        self::dbAccess()->delete('t_student_training', $CONDITION);
        self::dbAccess()->delete('t_student_training_assignment', $CONDITION);

        return array(
            "success" => true
        );
    }

    public static function findStudentFromId($Id)
    {
        $SQL = self::dbAccess()->select()
                ->from("t_student", array('*'))
                ->where("ID = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonListStudentInSchool($params)
    {

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

        if ($startDate and $endDate)
        {
            $SQL->where("C.START_DATE <= '" . $startDate . "'");
            $SQL->where("C.END_DATE >= '" . $endDate . "'");
        }

        if ($globalSearch)
        {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        $SQL->group("A.ID");
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY)
        {
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
        if ($resultRows)
        {
            foreach ($resultRows as $value)
            {

                if (!self::checkEnrollmentCountTrainingByStudentId($value->STUDENT_ID, $trainingId))
                {

                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);
                    if (!SchoolDBAccess::displayPersonNameInGrid())
                    {
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    }
                    else
                    {
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
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function checkEnrollmentCountTrainingByStudentId($studentId, $trainingId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array("C" => "COUNT(*)"));
        if ($studentId)
            $SQL->where("STUDENT = ?", $studentId);
        if ($trainingId)
            $SQL->where("TRAINING = '" . $trainingId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function actionStudentToTraining($params)
    {

        $SAVEDATA = array();

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";

        $classObject = self::findTrainingFromId($trainingId);
        $LEVEL_OBJECT = self::findTrainingFromId($classObject->PARENT);
        $TERM_OBJECT = self::findTrainingFromId($LEVEL_OBJECT->PARENT);
        $PROGRAM_OBJECT = self::findTrainingFromId($TERM_OBJECT->PARENT);

        if ($field == "ASSIGNED")
        {
            if ($newValue)
            {
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

    public static function getStaticTermTraining($studentId, $termId)
    {

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
        if ($resultRows)
        {
            $i = 0;
            foreach ($resultRows as $value)
            {
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

    public static function getStaticTrainingHistory($studentId)
    {

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

        if ($resultRows)
        {
            $i = 0;
            foreach ($resultRows as $value)
            {

                $DATA[$i] = "{";
                $DATA[$i] .= "text:'" . stripslashes($value->PROGRAM_NAME) . "'";
                $DATA[$i] .= ",id:'" . $value->TERM_ID . "'";
                $DATA[$i] .= ",iconCls: 'icon-folder_magnify'";
                $DATA[$i] .= ",expanded: true";
                $DATA[$i] .= ",cls:'nodeTextBold'";
                if (self::getStaticTermTraining($studentId, $value->TERM_ID))
                {
                    $DATA[$i] .= ",leaf: false";
                    $DATA[$i] .= ",children: [" . self::getStaticTermTraining($studentId, $value->TERM_ID) . "]";
                }
                else
                {
                    $DATA[$i] .= ",leaf: true";
                }

                $DATA[$i] .= "}";
                $i++;
            }
        }

        return implode(",", $DATA);
    }

    public static function jsonTreeStudentTrainings($params)
    {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $facette = self::findTrainingFromId($node);

        if ($studentId)
        {
            $objectId = $studentId;
        }
        else
        {
            $objectId = Zend_Registry::get('USER')->ID;
        }

        $data = array();

        if ($node == 0)
        {

            $SQL = "";
            $SQL .= " SELECT DISTINCT C.ID AS PROGRAM_ID, C.NAME AS PRGRAM_NAME";
            $SQL .= " FROM t_student_training AS A";
            $SQL .= " LEFT JOIN t_training AS B ON B.ID=A.TRAINING";
            $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.PROGRAM";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND C.NAME<>''";
            $SQL .= " AND A.STUDENT='" . $objectId . "'";

            $resultRows = self::dbAccess()->fetchAll($SQL);
        }
        else
        {

            $SQL = "";

            switch ($facette->OBJECT_TYPE)
            {
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

            switch ($facette->OBJECT_TYPE)
            {
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

        if ($node == 0)
        {
            if ($resultRows)
            {
                foreach ($resultRows as $key => $value)
                {
                    $data[$key]['id'] = "" . $value->PROGRAM_ID . "";
                    $data[$key]['iconCls'] = "icon-bricks";
                    $data[$key]['text'] = stripslashes($value->PRGRAM_NAME);
                    $data[$key]['leaf'] = false;
                    $data[$key]['cls'] = "nodeTextBold";
                }
            }
        }
        else
        {

            switch ($facette->OBJECT_TYPE)
            {
                case "PROGRAM":
                    foreach ($resultRows as $key => $value)
                    {
                        $data[$key]['id'] = "" . $value->LEVEL_ID . "";
                        $data[$key]['iconCls'] = "icon-folder_magnify";
                        $data[$key]['text'] = stripslashes($value->LEVEL_NAME);
                        $data[$key]['leaf'] = false;
                        $data[$key]['cls'] = "nodeTextBold";
                    }
                    break;
                case "LEVEL":
                    foreach ($resultRows as $key => $value)
                    {
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
                    foreach ($resultRows as $key => $value)
                    {

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
    public static function jsonStudentSubjectTraining($params, $isJson = true)
    {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : false;

        $params["trainingId"] = $trainingId;
        $resultRows = self::sqlStudentTraining(false, $trainingId, $studentId);

        $i = 0;
        if ($resultRows)
        {
            foreach ($resultRows as $key => $value)
            {

                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["STUDENT"] = setShowText($value->CODE) . ") ";
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] .= setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
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
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
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
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson)
        {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        }
        else
        {
            return $data;
        }
    }

    public static function getQueryStudentEnrollmentTraining($trainingId, $studentId = false)
    {

        $params = array();

        if ($trainingId)
            $params["trainingId"] = $trainingId;

        if ($studentId)
            $params["studentId"] = $studentId;

        return self::sqlStudentTraining(false, $trainingId, $studentId);
    }

    public function listStudentsData()
    {

        $data = array();
        $entries = $this->listStudentsByTraining();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $studentId = $value->ID;

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($studentId);
                $data[$i]["ID"] = $studentId;
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["STUDENT"] = getFullName($value->FIRSTNAME, $value->LASTNAME);
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;

                $i++;
            }
        }

        return $data;
    }

    public function getTrainingObject()
    {
        return TrainingDBAccess::findTrainingFromId($this->trainingId);
    }

    public function getAssignmentObject()
    {

        return AssignmentTempDBAccess::findAssignmentJoinCategory($this->assignmentId);
    }

    public function listStudentsByTraining($globalSearch = false)
    {
        return self::getQueryStudentEnrollmentTraining($this->trainingId, false);
    }

    public function listSubjectsTraining()
    {

        return TrainingSubjectDBAccess::getListSubjectsForAssessmentTraining($this->trainingId);
    }

    public static function listStudentTrainings($params)
    {

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

        if ($resultRows)
        {
            foreach ($resultRows as $key => $value)
            {

                $data[$key]["ID"] = $value->CLASS_ID;
                $data[$key]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                $data[$key]["TERM_NAME"] = getShowDate($value->START_DATE) . "-" . getShowDate($value->END_DATE);
                $data[$key]["LEVEL_NAME"] = setShowText($value->LEVEL_NAME);
                $data[$key]["PROGRAMM_NAME"] = setShowText($value->PROGRAMM_NAME);

                if (self::checkEnrollmentCountTrainingByStudentId($studentId, $value->CLASS_ID))
                {
                    $data[$key]["ASSIGNED"] = 1;
                }
                else
                {
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

    public static function getCurrentTrainingByTerm($studentId, $termId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training'), array('TRAINING AS ID'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TRAINING', array());
        $SQL->where("B.PARENT = '" . $termId . "'");
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        $SQL->group('A.STUDENT');
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->ID : false;
    }

    public static function actionStudentTrainingTransfer($params)
    {

        $SAVEDATA = array();

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $chooseId = isset($params["id"]) ? addText($params["id"]) : "0";

        $currentTraning = self::getCurrentTrainingByTerm($studentId, $parentId);

        if ($currentTraning)
        {
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

    public static function actionStudentTrainingDescription($params)
    {

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

        if ($result)
        {
            foreach ($result as $value)
            {

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? addText($params["RADIOBOX_" . $value->ID . ""]) : "";

                if ($RADIOBOX)
                {
                    $RADIOBOX_DATA[$RADIOBOX] = $RADIOBOX;
                }

                if ($CHECKBOX == "on")
                {
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

    public static function loadStudentTrainingDescripton($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $facette = self::getLinkStudentAndTraining($studentId, $objectId);
        $data = array();

        if ($facette)
        {

            $CHECK_DATA = explode(",", $facette->DESCRIPTION);
            $SQL = self::dbAccess()->select();
            $SQL->from("t_personal_description", array('*'));
            $SQL->where("PERSON='STUDENT_TRAINING'");
            $result = self::dbAccess()->fetchAll($SQL);

            if ($result)
            {
                foreach ($result as $value)
                {

                    $descriptionObject = DescriptionDBAccess::findObjectFromId($value->ID);
                    switch ($value->CHOOSE_TYPE)
                    {
                        case 1:
                            if (in_array($value->ID, $CHECK_DATA))
                            {
                                $data["CHECKBOX_" . $value->ID] = true;
                            }
                            else
                            {
                                $data["CHECKBOX_" . $value->ID] = false;
                            }

                            break;
                        case 2:
                            if (in_array($value->ID, $CHECK_DATA))
                            {
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

    public static function getStudentTraining($studentId, $trainingId, $searchIndex = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_training", array('*'));
        $SQL->where("STUDENT = ?", $studentId);

        if ($searchIndex == "TERM")
        {
            $SQL->where("TERM = '" . $trainingId . "'");
        }
        else
        {
            $SQL->where("TRAINING = '" . $trainingId . "'");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadStudentTraining($trainingId, $studentId)
    {

        $data = self::getTrainingDataFromId($trainingId);
        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function getCurrentTrainingsByStudent($studentId)
    {
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

    public static function getCurrentStudentTraining($studentId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training'), array('TRAINING AS TRAINING_ID'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TRAINING', array("NAME AS CLASS_NAME"));
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.TERM', array("START_DATE", "END_DATE"));
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=A.LEVEL', array("NAME AS LEVEL_NAME"));
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=A.PROGRAM', array("NAME AS PROGRAM_NAME"));
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getTrainingSubjectAssignment($evaluationType, $trainingId, $subjectId, $assignmentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training_subject", array('*'));
        
        if ($evaluationType)
        {
            $SQL->where("TERM = ?", $trainingId);
        }
        else
        {
            $SQL->where("TRAINING = ?", $trainingId);
        }
        $SQL->where("TERM = ?", $trainingId);
        $SQL->where("SUBJECT = ?", $subjectId);
        $SQL->where("ASSIGNMENT = ?", $assignmentId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

}

?>