<?php

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/SchooleventDBAccess.php';
require_once 'models/app_school/sms/SendSMSDBAccess.php';
require_once 'models/app_school/finance/StudentFeeDBAccess.php';
require_once 'models/app_school/ScholarshipDBAccess.php'; //@veasna
require_once 'models/app_school/AcademicAdditionalDBAccess.php'; //@Sea Peng
require_once setUserLoacalization();

class StudentAcademicDBAccess extends StudentDBAccess {

    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }

    public static function getSQLAllStudents()
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student", array('*'));
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function jsonGetAllStudents()
    {
        $entries = self::getSQLAllStudents();
        $data = array();

        $i = 0;
        if ($entries)
        {
            foreach ($entries as $result)
            {
                $data[$i]["ID"] = $result->ID;
                $data[$i]["SOURCE_IMAGE"] = $result->SOURCE_IMAGE;
                $data[$i]["STUDENT_SCHOOL_ID"] = $result->STUDENT_SCHOOL_ID;
                $data[$i]["CODE_ID"] = $result->CODE;
                $data[$i]["NAME"] = setShowText($result->LASTNAME) . " " . setShowText($result->FIRSTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($result->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($result->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
                $data[$i]["LOGINNAME"] = $result->LOGINNAME ? $result->LOGINNAME : "S" . $result->CODE;
                $data[$i]["PASSWORD"] = "";
                $data[$i]["STATUS"] = $result->STATUS;
                $data[$i]["ACADEMIC_TYPE"] = $result->ACADEMIC_TYPE;
                $data[$i]["GENDER"] = getGenderName($result->GENDER);
                $data[$i]["RELIGION"] = $result->RELIGION;
                $data[$i]["ETHNIC"] = $result->ETHNIC;
                $data[$i]["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);
                $data[$i]["BIRTH_PLACE"] = setShowText($result->BIRTH_PLACE);
                $data[$i]["EMAIL"] = setShowText($result->EMAIL);
                $data[$i]["PHONE"] = setShowText($result->PHONE);
                $data[$i]["SMS_SERVICES"] = $result->SMS_SERVICES;
                $data[$i]["MOBIL_PHONE"] = setShowText($result->MOBIL_PHONE);
                $data[$i]["STREET"] = setShowText($result->STREET);
                $data[$i]["COUNTRY_PROVINCE"] = setShowText($result->COUNTRY_PROVINCE);
                $data[$i]["COUNTRY"] = setShowText($result->COUNTRY);
                $data[$i]["POSTCODE_ZIPCODE"] = setShowText($result->POSTCODE_ZIPCODE);
                $data[$i]["TOWN_CITY"] = setShowText($result->TOWN_CITY);
                $data[$i]["FULLNAME_FATHER"] = setShowText($result->FULLNAME_FATHER);
                $data[$i]["FULLNAME_MOTHER"] = setShowText($result->FULLNAME_MOTHER);
                $data[$i]["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
                $data[$i]["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
                $data[$i]["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
                $data[$i]["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($result->CREATED_BY);
                $data[$i]["MODIFY_BY"] = setShowText($result->MODIFY_BY);
                $data[$i]["ENABLED_BY"] = setShowText($result->ENABLED_BY);
                $data[$i]["DISABLED_BY"] = setShowText($result->DISABLED_BY);
                $data[$i]["SYSTEM_LANGUAGE"] = $result->SYSTEM_LANGUAGE;

                ++$i;
            }
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($entries)
            , "rows" => $data
        );
    }

    public static function getSQLStudentEnrollment($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $globalSearch = isset($params["globalSearch"]) ? addText($params["globalSearch"]) : "";

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $studentSchoolCode = isset($params["studentSchoolCode"]) ? addText($params["studentSchoolCode"]) : "";
        $studentCode = isset($params["code"]) ? addText($params["code"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $chooseSchoolYear = isset($params["chooseSchoolYear"]) ? addText($params["chooseSchoolYear"]) : "";
        $chooseLevel = isset($params["chooseLevel"]) ? addText($params["chooseLevel"]) : "";
        $chooseCampus = isset($params["chooseCampus"]) ? addText($params["chooseCampus"]) : "";

        $classIds = isset($params["classIds"]) ? addText($params["classIds"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {
            switch ($academicObject->OBJECT_TYPE)
            {
                case "SUBJECT":
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $subjectId = $academicObject->SUBJECT_ID;
                    if (UserAuth::getUserType() != 'STUDENT')
                        $classId = "";
                    break;
                case "SCHOOLYEAR":
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $classId = "";
                    $subjectId = "";
                    break;
                case "CLASS":
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $classId = $academicObject->ID;
                    $subjectId = $academicObject->SUBJECT_ID ? $academicObject->SUBJECT_ID : 0;
                    break;
            }
        }

        if (!SchoolDBAccess::displayPersonNameInGrid())
        {
            $SELECTION_A = array(
                "ID AS STUDENT_ID"
                , "STUDENT_INDEX"
                , "CODE AS CODE_ID"
                , "CODE AS STUDENT_CODE"
                , "FIRSTNAME AS FIRSTNAME"
                , "LASTNAME AS LASTNAME"
                , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
                , "LASTNAME_LATIN AS LASTNAME_LATIN"
                , "GENDER AS GENDER"
                , "EMAIL AS EMAIL"
                , "PHONE AS PHONE"
                , "DATE_BIRTH AS DATE_BIRTH"
                , "SMS_SERVICES AS SMS_SERVICES"
                , "MOBIL_PHONE AS MOBIL_PHONE"
                , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
                , "CONCAT(LASTNAME,' ',FIRSTNAME) AS FULL_NAME"
                , "CREATED_DATE AS CREATED_DATE"
            );
        }
        else
        {
            $SELECTION_A = array(
                "ID AS STUDENT_ID"
                , "STUDENT_INDEX"
                , "CODE AS CODE_ID"
                , "CODE AS STUDENT_CODE"
                , "FIRSTNAME AS FIRSTNAME"
                , "LASTNAME AS LASTNAME"
                , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
                , "LASTNAME_LATIN AS LASTNAME_LATIN"
                , "GENDER AS GENDER"
                , "EMAIL AS EMAIL"
                , "PHONE AS PHONE"
                , "DATE_BIRTH AS DATE_BIRTH"
                , "SMS_SERVICES AS SMS_SERVICES"
                , "MOBIL_PHONE AS MOBIL_PHONE"
                , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
                , "CONCAT(FIRSTNAME,' ',LASTNAME) AS FULL_NAME"
                , "CREATED_DATE AS CREATED_DATE"
            );
        }

        if ($academicObject)
        {
            switch ($academicObject->EDUCATION_SYSTEM)
            {
                case 0:
                    $SELECTION_B = array(
                        "ID AS ENROLL_ID"
                        , "SORTKEY AS SORTKEY"
                        , "SCHOOL_YEAR AS SCHOOL_YEAR"
                        , "CLASS AS CLASS"
                        , "GRADE AS GRADE"
                        , "STATUS AS SCHOOLYEAR_STATUS"
                        , "TRANSFER AS TRANSFER"
                    );
                    break;
                case 1:
                    $SELECTION_B = array(
                        "ID AS ENROLL_ID"
                        , "SORTKEY AS SORTKEY"
                        , "SCHOOLYEAR_ID AS SCHOOL_YEAR"
                        , "CLASS_ID AS CLASS"
                        , "CREDIT_STATUS AS CREDIT_STATUS"
                        , "CREDIT_STATUS_DESCRIPTION AS CREDIT_STATUS_DESCRIPTION"
                        , "CREDIT_STATUS_BY AS CREDIT_STATUS_BY"
                        , "CREDIT_STATUS_DATED AS CREDIT_STATUS_DATED"
                    );
                    break;
            }
        }

        $SELECTION_C = array("NAME AS GRADE_NAME");
        $SELECTION_D = array("NAME AS SCHOOL_YEAR");
        $SELECTION_E = array("NAME AS CLASS_NAME");

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECTION_A);

        if ($academicObject)
        {
            switch ($academicObject->EDUCATION_SYSTEM)
            {
                case 0:
                    $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.ID=B.STUDENT', $SELECTION_B);
                    $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.GRADE', $SELECTION_C);
                    $SQL->joinLeft(array('E' => 't_grade'), 'E.ID=B.CLASS', $SELECTION_E);
                    $SQL->joinLeft(array('D' => 't_academicdate'), 'D.ID=B.SCHOOL_YEAR', $SELECTION_D);

                    if ($classId)
                    {
                        $SQL->where("B.CLASS='" . $classId . "'");
                    }
                    if ($gradeId)
                    {
                        $SQL->where("B.GRADE='" . $gradeId . "'");
                    }
                    if ($studentId)
                    {
                        $SQL->where("A.ID='" . $studentId . "'");
                    }
                    if ($schoolyearId)
                    {
                        $SQL->where("B.SCHOOL_YEAR='" . $schoolyearId . "'");
                    }
                    if ($studentSchoolCode)
                    {
                        $SQL->where("A.STUDENT_SCHOOL_ID LIKE '%" . $studentSchoolCode . "%'");
                    }
                    if ($gender)
                    {
                        $SQL->where("A.GENDER = '" . $gender . "'");
                    }
                    if ($chooseSchoolYear)
                    {
                        $SQL->where("B.SCHOOL_YEAR = '" . $chooseSchoolYear . "'");
                    }
                    if ($chooseLevel)
                    {
                        $SQL->where("B.GRADE = '" . $chooseLevel . "'");
                    }
                    if ($chooseCampus)
                    {
                        $SQL->where("B.CAMPUS = '" . $chooseCampus . "'");
                    }
                    if ($classIds)
                    {
                        $SQL->where("B.CLASS IN (" . $classIds . ")");
                    }
                    break;
                case 1:
                    $SQL->joinLeft(array('B' => 't_student_schoolyear_subject'), 'A.ID=B.STUDENT_ID', $SELECTION_B);
                    $SQL->joinLeft(array('E' => 't_grade'), 'E.ID=B.CLASS_ID', $SELECTION_E);
                    $SQL->joinLeft(array('D' => 't_academicdate'), 'D.ID=B.SCHOOLYEAR_ID', $SELECTION_D);
                    $SQL->joinLeft(array('F' => 't_grade'), 'F.ID=B.CREDIT_ACADEMIC_ID', array("NAME AS SUBJECT_TGRADE"));  //$veasna 

                    if ($studentId)
                        $SQL->where("A.ID='" . $studentId . "'");

                    if ($schoolyearId)
                        $SQL->where("B.SCHOOLYEAR_ID='" . $schoolyearId . "'");

                    if ($subjectId)
                        $SQL->where("B.SUBJECT_ID = '" . $subjectId . "'");

                    if ($classId)
                        $SQL->where("B.CLASS_ID = '" . $classId . "'");

                    break;
            }
        }

        if ($studentId)
            $SQL->where("A.ID='" . $studentId . "'");

        if ($studentSchoolCode)
            $SQL->where("A.STUDENT_SCHOOL_ID LIKE '%" . $studentSchoolCode . "%'");

        if ($studentCode)
            $SQL->where("A.CODE LIKE '%" . $studentCode . "%'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '%" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '%" . $lastname . "%'");

        if ($studentCode)
            $SQL->where("A.CODE LIKE '" . $studentCode . "%'");

        if ($gender)
            $SQL->where("A.GENDER = '" . $gender . "'");

        if ($globalSearch)
        {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.STUDENT_SCHOOL_ID LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        $SQL .= " GROUP BY A.ID";
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

    public static function getQueryStudentEnrollment($academicId, $globalSearch, $schoolyearId = false, $studentId = false)
    {

        $params = array();

        if ($academicId)
            $params["academicId"] = $academicId;
        if ($globalSearch)
            $params["globalSearch"] = $globalSearch;
        if ($schoolyearId)
            $params["schoolyearId"] = $schoolyearId;
        if ($studentId)
            $params["studentId"] = $studentId;

        return self::getSQLStudentEnrollment($params);
    }

    public static function getCountStudentByClass($academicId)
    {

        $result = self::getQueryStudentEnrollment($academicId, false, false);
        return sizeof($result);
    }

    public static function setStudentSortkeyInClass($studentId, $classId, $sortkey)
    {

        $SQL = "UPDATE";
        $SQL .= " t_student_schoolyear";
        $SQL .= " SET SORTKEY = '" . $sortkey . "'";
        $SQL .= " WHERE";
        $SQL .= " STUDENT = '" . $studentId . "'";
        $SQL .= " AND CLASS = '" . $classId . "'";
        return self::dbAccess()->query($SQL);
    }

    public static function sqlRemoveStudentEnrollmentSchoolyear($studentId, $gradeId, $schoolyearId)
    {

        $condition = array(
            'STUDENT = ? ' => $studentId
            , 'GRADE = ? ' => $gradeId
            , 'SCHOOL_YEAR = ? ' => $schoolyearId
        );

        return self::dbAccess()->delete("t_student_schoolyear", $condition);
    }

    public static function getStudentsByClass($classId, $globalSearch, $classIds)
    {

        $result = self::getQueryStudentEnrollment($classId, $globalSearch, $classIds);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                switch (UserAuth::getUserType())
                {
                    case "STUDENT":
                        $data[$i]["ID"] = camemisId();
                        break;
                    default:
                        $data[$i]["ID"] = $value->STUDENT_ID;
                        $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                        break;
                }
                $data[$i]["TRANSFER"] = $value->TRANSFER ? YES : NO;
                $data[$i]["SORTKEY"] = $value->SORTKEY;
                $data[$i]["CLASS_NAME"] = setShowText($value->CLASS_NAME);
                $data[$i]["SORTKEY"] = $value->SORTKEY ? $value->SORTKEY : "---";
                $data[$i]["CODE"] = $value->CODE_ID;
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["STUDENT_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["SCHOOL_YEAR"] = setShowText($value->SCHOOL_YEAR);

                ////////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////////
                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                ++$i;
            }

        return $data;
    }

    //@veasna
    public static function getCurrentClassByStudentID($studnetID)
    {

        $SELECTION_B = array(
            "ID AS CLASS_ID"
            , "CODE AS CLASS_CODE"
            , "NAME AS CLASS_NAME"
        );
        $SELECTION_C = array(
            "ID AS SCHOOLYEAR"
            , "NAME AS SCHOOLYEAR_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear'), array());
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_academicdate'), 'A.SCHOOL_YEAR=C.ID', $SELECTION_C);
        $SQL->where("A.STUDENT ='" . $studnetID . "'");
        $SQL->where("C.START <='" . date("Y-m-d") . "' AND '" . date("Y-m-d") . "'<=C.END");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonListStudentsByStudentClass($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : 0;
        $classIds = isset($params["classIds"]) ? addText($params["classIds"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $data = array();
        $arr = array();
        $results = self::getCurrentClassByStudentID($studentId);
        if ($results)
        {
            foreach ($results as $values)
            {
                $arr[] = $values->CLASS_ID;
            }
            $classId = implode(",", $arr);
            $data = self::getStudentsByClass($classId, $globalSearch, $classIds);
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

    public static function getClassByTeacherID($teacherID)
    {
        $SELECTION_A = array(
            "ID AS SCHOOLYEAR"
        );
        $SELECTION_B = array(
            "SUBJECT AS SUBJECT"
        );
        $SELECTION_C = array(
            "ID AS CLASS_ID"
            , "GRADE_ID AS GRADE_ID"
            , "NAME AS CLASS_NAME"
            , "INSTRUCTOR AS INSTRUCTOR"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_academicdate'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_teacher_class'), 'A.ID=B.SCHOOLYEAR', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_grade'), 'B.ACADEMIC=C.ID', $SELECTION_C);
        $SQL->where("B.TEACHER ='" . $teacherID . "'");
        $SQL->where("A.START <='" . date("Y-m-d") . "' AND '" . date("Y-m-d") . "'<=A.END");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getClassInstructorByTeacherID($teacherID)
    {
        $SELECTION_A = array(
            "ID AS SCHOOLYEAR"
        );

        $SELECTION_B = array(
            "ID AS CLASS_ID"
            , "GRADE_ID AS GRADE_ID"
            , "NAME AS CLASS_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_academicdate'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_grade'), 'A.ID=B.SCHOOL_YEAR', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_instructor'), 'B.ID=C.CLASS', array());
        $SQL->where("C.TEACHER ='" . $teacherID . "'");
        $SQL->where("A.START <='" . date("Y-m-d") . "' AND '" . date("Y-m-d") . "'<=A.END");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonListStudentsByTeacherClass($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $classIds = isset($params["classIds"]) ? addText($params["classIds"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $arr = array();
        $results = self::getClassByTeacherID($teacherId);
        if ($results)
        {
            foreach ($results as $values)
            {
                $arr[] = $values->CLASS_ID;
            }
        }
        $_classId = implode(",", $arr);
        //error_log($classId);
        $data = array();
        if ($_classId)
        {
            $data = self::getStudentsByClass($_classId, $globalSearch, $classIds);
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

    public static function jsonListStudentsByClass($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : 0;
        $classIds = isset($params["classIds"]) ? addText($params["classIds"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $data = self::getStudentsByClass($classId, $globalSearch, $classIds);

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

    public static function actionTransferDes($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "0";
        $CONTENT = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "0";

        $SQL = "UPDATE";
        $SQL .= " t_student_schoolyear";
        $SQL .= " SET TRANSFER_DESCRIPTION = '" . mysql_escape_string(stripslashes($CONTENT)) . "'";
        $SQL .= " WHERE";
        $SQL .= " STUDENT = '" . $studentId . "'";
        $SQL .= " AND CLASS = '" . $classId . "'";

        self::dbAccess()->query($SQL);

        return array(
            "success" => true
        );
    }

    public function loadStudentTransferDes($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";

        $facette = self::getCurrentStudentAcademic($studentId);

        $data["CONTENT"] = $facette ? $facette->TRANSFER_DESCRIPTION : "---";

        $o = array(
            "success" => true
            , "data" => $data
        );
        return $o;
    }

    public static function getCurrentStudentAcademic($studentId)
    {

        $output["SCHOOLYEAR"] = "";
        $output["ACADEMIC"] = "";
        $output["GRADE_ID"] = "";
        $output["SCHOOLYEAR_ID"] = "";

        $TRADITIONAL_SQL = self::dbAccess()->select();
        $TRADITIONAL_SQL->from(array('A' => 't_student_schoolyear'), array());
        $TRADITIONAL_SQL->joinLeft(array('B' => 't_academicdate'), 'B.ID=A.SCHOOL_YEAR', array("ID AS SCHOOLYEAR_ID", "NAME AS SCHOOLYEAR_NAME"));
        $TRADITIONAL_SQL->where("A.STUDENT = '" . $studentId . "'");
        $TRADITIONAL_SQL->order("B.START DESC LIMIT 0,1");
        $TRADITIONAL_RESULT = self::dbAccess()->fetchRow($TRADITIONAL_SQL);
        $CHECK_TRADITIONAL_SYSTEM = $TRADITIONAL_RESULT ? $TRADITIONAL_RESULT->SCHOOLYEAR_ID : 0;

        $CREDIT_SQL = self::dbAccess()->select();
        $CREDIT_SQL->from(array('A' => 't_student_schoolyear_subject'), array());
        $CREDIT_SQL->joinLeft(array('B' => 't_academicdate'), 'B.ID=A.SCHOOLYEAR_ID', array("ID AS SCHOOLYEAR_ID", "NAME AS SCHOOLYEAR_NAME"));
        $CREDIT_SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $CREDIT_SQL->order("B.START DESC LIMIT 0,1");
        $CREDIT_RESULT = self::dbAccess()->fetchRow($CREDIT_SQL);
        $CHECK_CREDIT_SYSTEM = $CREDIT_RESULT ? $CREDIT_RESULT->SCHOOLYEAR_ID : 0;

        if ($CHECK_CREDIT_SYSTEM)
        {

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_student_schoolyear_subject'), array());
            $SQL->joinLeft(array('B' => 't_grade'), 'B.ID=A.CAMPUS_ID', array("NAME AS CAMPUS_NAME"));
            $SQL->joinLeft(array('C' => 't_academicdate'), 'C.ID=A.SCHOOLYEAR_ID', array("ID AS SCHOOLYEAR_ID", "NAME AS SCHOOLYEAR_NAME"));
            $SQL->joinLeft(array('D' => 't_subject'), 'D.ID=A.SUBJECT_ID', array("NAME AS SUBJECT_NAME"));
            $SQL->where("A.STUDENT_ID='" . $studentId . "'");
            $SQL->where("A.SCHOOLYEAR_ID='" . $CREDIT_RESULT->SCHOOLYEAR_ID . "'");
            //error_log($SQL->__toString());
            $SQL->order("C.START DESC");
            $result = self::dbAccess()->fetchAll($SQL);

            $data = array();
            if ($result)
            {
                foreach ($result as $value)
                {
                    $data[] = setShowText($value->SUBJECT_NAME) . " (" . setShowText($value->CAMPUS_NAME) . ")";
                }
            }

            $output["SCHOOLYEAR"] = $CREDIT_RESULT->SCHOOLYEAR_NAME;
            $output["ACADEMIC"] = implode("<br>", $data);
        }
        elseif ($CHECK_TRADITIONAL_SYSTEM)
        {

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_student_schoolyear'), array());
            $SQL->joinLeft(array('B' => 't_grade'), 'B.ID=A.CAMPUS', array("NAME AS CAMPUS_NAME"));
            $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=A.GRADE', array("ID AS GRADE_ID", "NAME AS GRADE_NAME"));
            $SQL->joinLeft(array('D' => 't_academicdate'), 'D.ID=A.SCHOOL_YEAR', array("ID AS SCHOOLYEAR_ID", "NAME AS SCHOOLYEAR_NAME"));
            $SQL->joinLeft(array('E' => 't_grade'), 'E.ID=A.CLASS', array("NAME AS CLASS_NAME"));
            $SQL->where("A.STUDENT='" . $studentId . "'");
            $SQL->where("A.SCHOOL_YEAR='" . $TRADITIONAL_RESULT->SCHOOLYEAR_ID . "'");
            $SQL->order("D.START DESC LIMIT 0,1");
            //echo $SQL->__toString(); 
            $result = self::dbAccess()->fetchRow($SQL);

            if ($result)
            {
                $output["SCHOOLYEAR"] = setShowText($result->SCHOOLYEAR_NAME);
                $output["ACADEMIC"] = setShowText($result->CLASS_NAME) . " (" . setShowText($result->GRADE_NAME) . ")";
                $output["SCHOOLYEAR_ID"] = $result->SCHOOLYEAR_ID;
                $output["GRADE_ID"] = $result->GRADE_ID;
            }
        }

        return (object) $output;
    }

    public static function checkStudentEnrolledTraditionalSystem($studentId, $gradeId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_schoolyear", array("*"));
        $SQL->where("STUDENT = ?", $studentId);
        if ($gradeId)
            $SQL->where("GRADE = ?", $gradeId);
        if ($schoolyearId)
            $SQL->where("SCHOOL_YEAR = ?", $schoolyearId);

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result : 0;
    }

    //@veasna
    public static function findEnrollmentCountGeneralByStudentId($studentId, $gradeId, $schoolyearId)
    {
        return self::checkStudentEnrolledTraditionalSystem($studentId, $gradeId, $schoolyearId);
    }

    public static function checkEnrollmentCountTrainingByStudentId($studentId)
    {

        $SQL = self::dbAccess()->select()
                ->from("t_student_training", array("C" => "COUNT(*)"))
                ->where("STUDENT = '" . $studentId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function countStudentGender()
    {
        $SQL = "";
        $SQL .= "SELECT gender, count( gender ) AS total";
        $SQL .= " FROM t_student";
        $SQL .= " GROUP BY gender";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonCountStudentGender()
    {

        $data = self::countStudentGender();
        $a = array();
        if ($data)
        {
            foreach ($data as $key => $value)
            {
                if ($value->gender == 1 || $value->gender == 2)
                    $value->gender = "'" . getGenderName($value->gender) . "'";
                else
                    $value->gender = "'" . $value->gender . "'";
                $a[$key] = $value;
            }
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function countStudentBornYear()
    {
        $SQL = "";
        $SQL .= "SELECT year( date_birth ) AS born, count( * ) AS total";
        $SQL .= " FROM t_student";
        $SQL .= " GROUP BY year( date_birth )";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonCountStudentBornYear()
    {
        $data = self::countStudentBornYear();
        $a = array();
        if ($data)
        {
            foreach ($data as $key => $value)
            {
                $value->born = "'" . $value->born . "'";
                $a[$key] = $value;
            }
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function jsonListStudentScholarship($params)
    {

        $target = isset($params["target"]) ? addText($params["target"]) : "";
        $result = ScholarshipDBAccess::getSQLStudentEnrollmentScholarship($params);

        $data = array();
        ////check combibed together....
        //if (strtoupper($target) == "GENERAL" OR !$target) {
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->STUDENT_SCOLARSHIP_ID;
                $data[$i]["CODE"] = $value->STUDENT_CODE;
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = "( " . $value->STUDENT_CODE . " ) " . $value->LASTNAME . " " . $value->FIRSTNAME . " | " . getGenderName($value->GENDER);
                }
                else
                {
                    $data[$i]["STUDENT"] = "( " . $value->STUDENT_CODE . " ) " . $value->FIRSTNAME . " " . $value->LASTNAME . " | " . getGenderName($value->GENDER);
                }
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["SCHOLARSHIP"] = $value->SCHOLARSHIP . " (" . $value->SCHOLARSHIP_VALUE . "%)"; //@veasna
                if ($value->T_SCOLARSHIP_SCHOOLYEAR)
                {
                    $academicDateObject = AcademicDateDBAccess::findAcademicDateFromId($value->T_SCOLARSHIP_SCHOOLYEAR);
                    $data[$i]["SCHOOLYEAR"] = $academicDateObject->NAME;
                }
                if ($value->T_SCOLARSHIP_TERM)
                {
                    $trainingObject = TrainingDBAccess::findTrainingFromId($value->T_SCOLARSHIP_TERM);
                    $data[$i]["TERM"] = $trainingObject->START_DATE . "-" . $trainingObject->END_DATE;
                }

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    //@veasna
    public static function getSQLAllStudentInSchoolyear($schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_schoolyear', array('*'));
        $SQL->where("SCHOOL_YEAR = ?", $schoolyearId);
        //error_log($SQL->__toString());       
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findStudentClassGradeSchoolyear($studentId, $academicObject)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_schoolyear');
        $SQL->where("STUDENT = ?", $studentId);
        $SQL->where("GRADE = ?", $academicObject->GRADE_ID);
        $SQL->where("SCHOOL_YEAR = ?", $academicObject->SCHOOL_YEAR);
        //error_log($SQL->__toString());       
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function checkAdditionalInformationItem($Id, $item)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_additional_information_item", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT='" . $Id . "'");
        $SQL->where("ITEM='" . $item . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function jsonStudentAcademicTraditional($params)
    {

        $ACADEMIC = AcademicDBAccess::getInstance();

        $classId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $schoolarship = isset($params["SCHOLARSHIP_ID"]) ? addText($params["SCHOLARSHIP_ID"]) : "";

        $data = $ACADEMIC->getGradeDataFromId($classId);
        $academicObject = AcademicDBAccess::findGradeFromId($classId);

        $STUDENT_SCHOLARSHIP = ScholarshipDBAccess::getStudentScholarship($studentId, false, $academicObject->SCHOOL_YEAR);

        if ($STUDENT_SCHOLARSHIP)
        {
            $data["SCHOLARSHIP_NAME"] = $STUDENT_SCHOLARSHIP->NAME . " ($STUDENT_SCHOLARSHIP->SCHOLARSHIP_VALUE)%";
        }
        else
        {
            $data["SCHOLARSHIP_NAME"] = "---";
        }

        //@Sea Peng
        $facette = self::findStudentClassGradeSchoolyear($studentId, $academicObject);

        if ($facette)
        {
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_additional_information_item'), array('ITEM', 'DESCRIPTION'));
            $SQL->joinLeft(array('B' => 't_additional_information'), 'A.ITEM=B.ID', array('PARENT', 'CHOOSE_TYPE'));
            $SQL->where("A.CLASS='" . $classId . "'");
            $SQL->where("A.STUDENT='" . $studentId . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);

            foreach ($result as $value)
            {
                switch ($value->CHOOSE_TYPE)
                {
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
        //

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function jsonActionStudentAcademicTraditional($params)
    {

        $classGuid = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $scholarshipId = isset($params["SCHOLARSHIP_ID"]) ? $params["SCHOLARSHIP_ID"] : "";
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($classGuid);

        if ($academicObject)
        {

            if (is_numeric($scholarshipId))
            {
                $saveParams['compusId'] = $academicObject->CAMPUS_ID;
                $saveParams['studentId'] = $studentId;
                $saveParams['CHOOSE_SCHOLARSHIP'] = $scholarshipId;
                $saveParams['schoolyearId'] = $academicObject->SCHOOL_YEAR;
                ScholarshipDBAccess::addStudentSchoolar($saveParams);
            }
            elseif (is_string($scholarshipId))
            {
                $condition = array(
                    'STUDENT = ? ' => $studentId
                    , 'SCHOOLYEAR = ? ' => $academicObject->SCHOOL_YEAR
                );
                self::dbAccess()->delete("t_student_scholarship", $condition);
            }

            //@Sea Peng
            $SAVEDATA = array();

            $SQL = self::dbAccess()->select();
            $SQL->from("t_additional_information", array('*'));
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);

            self::dbAccess()->delete('t_additional_information_item', array("STUDENT='" . $studentId . "'", "CLASS='" . $academicObject->ID . "'"));

            if ($result)
            {
                foreach ($result as $value)
                {

                    $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                    $RADIOBOX = isset($params["RADIOBOX_" . $value->PARENT . ""]) ? addText($params["RADIOBOX_" . $value->PARENT . ""]) : "";
                    $INPUTFIELD = isset($params["INPUTFIELD_" . $value->ID . ""]) ? addText($params["INPUTFIELD_" . $value->ID . ""]) : "";
                    $TEXTAREA = isset($params["TEXTAREA_" . $value->ID . ""]) ? addText($params["TEXTAREA_" . $value->ID . ""]) : "";

                    $SAVEDATA['DESCRIPTION'] = '';

                    switch ($value->CHOOSE_TYPE)
                    {
                        case 1:
                            if ($CHECKBOX)
                            {
                                $SAVEDATA['CLASS'] = $classGuid;
                                $SAVEDATA['STUDENT'] = $studentId;
                                $SAVEDATA['ITEM'] = $value->ID;
                                self::dbAccess()->insert('t_additional_information_item', $SAVEDATA);
                            }
                            break;

                        case 2:
                            if ($RADIOBOX)
                            {
                                $SAVEDATA['CLASS'] = $classGuid;
                                $SAVEDATA['STUDENT'] = $studentId;
                                $SAVEDATA['ITEM'] = $RADIOBOX;
                                if (!self::checkAdditionalInformationItem($studentId, $RADIOBOX))
                                    self::dbAccess()->insert('t_additional_information_item', $SAVEDATA);
                            }
                            break;

                        case 3:
                            if ($INPUTFIELD)
                            {
                                $SAVEDATA['CLASS'] = $classGuid;
                                $SAVEDATA['STUDENT'] = $studentId;
                                $SAVEDATA['ITEM'] = $value->ID;
                                $SAVEDATA['DESCRIPTION'] = $INPUTFIELD;
                                self::dbAccess()->insert('t_additional_information_item', $SAVEDATA);
                            }
                            break;

                        case 4:
                            if ($TEXTAREA)
                            {
                                $SAVEDATA['CLASS'] = $classGuid;
                                $SAVEDATA['STUDENT'] = $studentId;
                                $SAVEDATA['ITEM'] = $value->ID;
                                $SAVEDATA['DESCRIPTION'] = $TEXTAREA;
                                self::dbAccess()->insert('t_additional_information_item', $SAVEDATA);
                            }
                            break;
                    }
                }
            }
            //
        }

        return array(
            "success" => true
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    // Unenrolled Student by Subject...
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonUnenrolledStudentSubject($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $ACADEMIC_ID = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($ACADEMIC_ID);

        $WHERE = "";
        if ($globalSearch)
        {

            $WHERE .= " ((A.NAME LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.STUDENT_SCHOOL_ID LIKE '" . $globalSearch . "%')";
            $WHERE .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $WHERE .= " ) ";
        }

        $SELECTION = array(
            "ID"
            , "CODE"
            , "LASTNAME"
            , "FIRSTNAME"
            , "FIRSTNAME_LATIN"
            , "LASTNAME_LATIN"
            , "GENDER"
            , "DATE_BIRTH"
            , "EMAIL"
            , "PHONE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECTION);
        $SQL->joinLeft(array('B' => 't_student_schoolyear_subject'), 'A.ID=B.STUDENT_ID', array());

        if ($WHERE)
            $SQL->where($WHERE);

        switch ($academicObject->OBJECT_TYPE)
        {
            case "CLASS":
                $SQL->where("B.SCHOOLYEAR_ID='" . $academicObject->SCHOOL_YEAR . "'");
                $SQL->where("B.SUBJECT_ID='" . $academicObject->SUBJECT_ID . "'");
                $SQL->where("B.CLASS_ID='0'");
                break;
        }
        $SQL->group("A.ID");
        //error_log($SQL->__toString());
        $entries = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($entries && $academicObject)
        {
            $i = 0;
            foreach ($entries as $result)
            {

                $CHECK = self::checkStudentEnrolledCreditSystem($result->ID, $academicObject);
                if (!$CHECK)
                {
                    $data[$i]["ID"] = $result->ID;
                    $data[$i]["CODE_ID"] = $result->CODE;
                    $data[$i]["NAME"] = setShowText($result->LASTNAME) . " " . setShowText($result->FIRSTNAME);
                    $data[$i]["FIRSTNAME"] = setShowText($result->FIRSTNAME);
                    $data[$i]["LASTNAME"] = setShowText($result->LASTNAME);
                    $data[$i]["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
                    $data[$i]["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
                    $data[$i]["GENDER"] = getGenderName($result->GENDER);
                    $data[$i]["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);
                    $data[$i]["EMAIL"] = setShowText($result->EMAIL);
                    $data[$i]["PHONE"] = setShowText($result->PHONE);

                    ////////////////////////////////////////////////////////////
                    //Status of student...
                    ////////////////////////////////////////////////////////////
                    $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($result->ID);
                    $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                    $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                    $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                    ++$i;
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

    ////////////////////////////////////////////////////////////////////////////
    // Credit System....
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonEnrolledStudentBySubject($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $entries = self::getSQLStudentEnrollment($params);

        $data = array();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $result)
            {

                $data[$i]["ID"] = $result->STUDENT_ID;
                $data[$i]["CODE_ID"] = $result->STUDENT_CODE;
                $data[$i]["SUBJECT_TGRADE"] = $result->SUBJECT_TGRADE;  //$veasna
                $data[$i]["STUDENT"] = setShowText($result->LASTNAME) . " " . setShowText($result->FIRSTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($result->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($result->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
                $data[$i]["GENDER"] = getGenderName($result->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);

                ////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($result->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : " ";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (isset($result->CLASS))
                {
                    $classObject = AcademicDBAccess::findGradeFromId($result->CLASS);
                    $data[$i]["GRADEGROUP"] = $classObject ? $classObject->NAME : "---";
                }
                else
                {
                    $data[$i]["GRADEGROUP"] = "---";
                }
                ++$i;
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

    ///@veasna
    public static function jsonEnrolledCreditStudentInSchoolyear($params)
    {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $gradeSchoolyearObject = AcademicDBAccess::findCreditGradeSchoolyear($schoolyearId);
        if (!$gradeSchoolyearObject)
        {
            return array(
                "success" => true
                , "totalCount" => 0
                , "rows" => array()
            );
        }

        $params["academicId"] = $gradeSchoolyearObject ? $gradeSchoolyearObject->GUID : '';

        $entries = self::getSQLStudentEnrollment($params);

        $data = array();
        $checkStudent = array();
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $result)
            {

                if (in_array($result->STUDENT_ID, $checkStudent))
                {
                    continue;
                }

                $checkStudent[] = $result->STUDENT_ID;

                $data[$i]["ID"] = $result->STUDENT_ID;
                $data[$i]["CODE_ID"] = $result->STUDENT_CODE;
                $data[$i]["STUDENT"] = setShowText($result->LASTNAME) . " " . setShowText($result->FIRSTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($result->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($result->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
                $data[$i]["GENDER"] = getGenderName($result->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);

                ////////////////////////////////////////////////////////////////
                //Status of student...
                ////////////////////////////////////////////////////////////////

                $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($result->STUDENT_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : " ";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if (isset($result->CLASS))
                {
                    $classObject = AcademicDBAccess::findGradeFromId($result->CLASS);
                    $data[$i]["GRADEGROUP"] = $classObject ? $classObject->NAME : "---";
                }
                else
                {
                    $data[$i]["GRADEGROUP"] = "---";
                }
                ++$i;
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

    ////////////////////////////////////////////////////////////////////////////
    // Credit System....
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonAddEnrollStudentSubject($params)
    {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($selectionIds != "" && $academicObject)
        {

            $selectedStudents = explode(",", $selectionIds);
            $selectedCount = 0;

            $SAVEDATA = Array();

            if ($selectedStudents)
            {
                foreach ($selectedStudents as $studentId)
                {

                    switch ($academicObject->OBJECT_TYPE)
                    {
                        case "SUBJECT":
                            $CHECK = self::checkStudentEnrolledCreditSystem($studentId, $academicObject);
                            if (!$CHECK)
                            {
                                $SAVEDATA['STUDENT_ID'] = $studentId;
                                $SAVEDATA['CREDIT_ACADEMIC_ID'] = $academicObject->ID;
                                $SAVEDATA['CAMPUS_ID'] = $academicObject->CAMPUS_ID;
                                $SAVEDATA['SUBJECT_ID'] = $academicObject->SUBJECT_ID;
                                $SAVEDATA['SCHOOLYEAR_ID'] = $academicObject->SCHOOL_YEAR;
                                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                                self::dbAccess()->insert('t_student_schoolyear_subject', $SAVEDATA);

                                $selectedCount++;
                            }
                            else
                            {

                                $selectedCount = 0;
                            }
                            break;
                        case "CLASS":
                            $SQL = "UPDATE";
                            $SQL .= " t_student_schoolyear_subject";
                            $SQL .= " SET CLASS_ID = '" . $academicObject->ID . "'";
                            $SQL .=" WHERE";
                            $SQL .= " STUDENT_ID = '" . $studentId . "'";
                            $SQL .= " AND CAMPUS_ID = '" . $academicObject->CAMPUS_ID . "'";
                            $SQL .= " AND SCHOOLYEAR_ID = '" . $academicObject->SCHOOL_YEAR . "'";
                            $SQL .= " AND SUBJECT_ID = '" . $academicObject->SUBJECT_ID . "'";
                            self::dbAccess()->query($SQL);
                            $selectedCount++;

                            break;
                    }
                }
            }
            else
            {
                $selectedCount = 0;
            }
        }
        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Credit System....
    ////////////////////////////////////////////////////////////////////////////
    public static function jsonRemoveEnrolledStudentSubject($params)
    {

        $studentId = isset($params["removeId"]) ? addText($params["removeId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {
            switch ($academicObject->OBJECT_TYPE)
            {
                case "SUBJECT":
                    $SQL = "DELETE";
                    $SQL .= " FROM t_student_schoolyear_subject";
                    $SQL .=" WHERE";
                    $SQL .= " STUDENT_ID = '" . $studentId . "'";
                    $SQL .= " AND CAMPUS_ID = '" . $academicObject->CAMPUS_ID . "'";
                    $SQL .= " AND SCHOOLYEAR_ID = '" . $academicObject->SCHOOL_YEAR . "'";
                    $SQL .= " AND SUBJECT_ID = '" . $academicObject->SUBJECT_ID . "'";
                    self::dbAccess()->query($SQL);
                    break;
                case "CLASS":
                    $SQL = "UPDATE";
                    $SQL .= " t_student_schoolyear_subject";
                    $SQL .= " SET CLASS_ID = ''";
                    $SQL .=" WHERE";
                    $SQL .= " STUDENT_ID = '" . $studentId . "'";
                    $SQL .= " AND CAMPUS_ID = '" . $academicObject->CAMPUS_ID . "'";
                    $SQL .= " AND SCHOOLYEAR_ID = '" . $academicObject->SCHOOL_YEAR . "'";
                    $SQL .= " AND SUBJECT_ID = '" . $academicObject->SUBJECT_ID . "'";
                    self::dbAccess()->query($SQL);
                    break;
            }
        }

        return array("success" => true);
    }

    public static function getStudentCurrentAcademicCreditSystem($studentId, $schoolyearId)
    {

        $SELECTION = array(
            "ID AS CAMPUS_ID"
            , "NAME AS CAMPUS_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_grade'), $SELECTION);
        $SQL->joinLeft(array('B' => 't_student_schoolyear_subject'), 'A.ID=B.CAMPUS_ID', array("CREDIT_ACADEMIC_ID"));
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.CREDIT_ACADEMIC_ID', array("PARENT AS TGRADE_SCHOOLYEAR"));  //@veasna
        $SQL->where("B.SCHOOLYEAR_ID = '" . $schoolyearId . "'");
        $SQL->where("B.STUDENT_ID = '" . $studentId . "'");
        $SQL->group("B.SCHOOLYEAR_ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getEnrolledSubjectByStudentCreditSystem($studentId, $schoolyearId)
    {
        $SELECTION_A = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_B = array(
            "ID AS CREDIT_ACADEMIC_ID"
            , "CLASS_ID AS GROUP_ID"
        );

        $SELECTION_C = array(
            "NAME AS GROUP_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_schoolyear_subject'), 'A.ID=B.SUBJECT_ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=B.CLASS_ID', $SELECTION_C);
        $SQL->where("B.SCHOOLYEAR_ID = '" . $schoolyearId . "'");
        $SQL->where("B.STUDENT_ID = '" . $studentId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonLoadStudentEnrollBySubject($params)
    {
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $LOAD_DATA = array();
        $DEFAULT_DATA = array();
        if ($academicObject)
        {
            $FIRST_SQL = self::dbAccess()->select();
            $FIRST_SQL->from("t_student_schoolyear", array("*"));
            $FIRST_SQL->where("STUDENT = ?", $studentId);
            $FIRST_SQL->where("GRADE = ?", $academicObject->GRADE_ID);
            $FIRST_SQL->where("SCHOOL_YEAR = ?", $academicObject->SCHOOL_YEAR);
            $FIRST_SQL->where("CLASS = ?", $academicObject->ID);
            //error_log($SQL->__toString());
            $fristResult = self::dbAccess()->fetchRow($FIRST_SQL);

            $listSubjects = GradeSubjectDBAccess::getListSubjectsFromAcademic($academicId, false);
            if ($listSubjects && $fristResult)
            {
                foreach ($listSubjects as $value)
                {
                    $DEFAULT_DATA["SUB_" . $value->SUBJECT_ID . ""] = $value->SUBJECT_ID;
                }

                if (!$fristResult->SUBJECTIDS)
                {
                    $subjectIds = implode(",", $DEFAULT_DATA);
                    $UPDATE_SQL = "UPDATE";
                    $UPDATE_SQL .= " t_student_schoolyear";
                    $UPDATE_SQL .= " SET SUBJECTIDS = '" . $subjectIds . "'";
                    $UPDATE_SQL .= " WHERE";
                    $UPDATE_SQL .= " STUDENT = '" . $studentId . "'";
                    $UPDATE_SQL .= " AND CLASS = '" . $academicObject->ID . "'";
                    self::dbAccess()->query($UPDATE_SQL);
                }

                $SECOND_SQL = self::dbAccess()->select();
                $SECOND_SQL->from("t_student_schoolyear", array("*"));
                $SECOND_SQL->where("STUDENT = ?", $studentId);
                $SECOND_SQL->where("GRADE = ?", $academicObject->GRADE_ID);
                $SECOND_SQL->where("SCHOOL_YEAR = ?", $academicObject->SCHOOL_YEAR);
                $SECOND_SQL->where("CLASS = ?", $academicObject->ID);
                //error_log($SQL->__toString());
                $secondResult = self::dbAccess()->fetchRow($SECOND_SQL);

                $selectedIds = explode(",", $secondResult->SUBJECTIDS);
                foreach ($listSubjects as $value)
                {
                    $LOAD_DATA["SUB_" . $value->SUBJECT_ID . ""] = (in_array($value->SUBJECT_ID, $selectedIds)) ? true : false;
                }
            }
        }

        return array(
            "success" => true
            , "data" => $LOAD_DATA
        );
    }

    public static function jsonSaveStudentEnrollBySubject($params)
    {
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $data = array();
        if ($academicObject)
        {
            $listSubjects = GradeSubjectDBAccess::getListSubjectsFromAcademic($academicId, false);
            if ($listSubjects)
            {
                foreach ($listSubjects as $value)
                {
                    $checkedId = isset($params["SUB_" . $value->SUBJECT_ID . ""]) ? addText($params["SUB_" . $value->SUBJECT_ID . ""]) : "";
                    if ($checkedId)
                        $data[] = $checkedId;
                }
            }

            $subjectIds = implode(",", $data);
            $SQL = "UPDATE";
            $SQL .= " t_student_schoolyear";
            $SQL .= " SET SUBJECTIDS = '" . $subjectIds . "'";
            $SQL .= " WHERE";
            $SQL .= " STUDENT = '" . $studentId . "'";
            $SQL .= " AND CLASS = '" . $academicObject->ID . "'";
            self::dbAccess()->query($SQL);
        }
        return array("success" => true);
    }

}

?>