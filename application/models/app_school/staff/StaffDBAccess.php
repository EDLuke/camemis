<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 11.10.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/user/UserMemberDBAccess.php';
require_once 'models/app_school/user/OrganizationDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDBAccess.php';
require_once 'models/app_school/staff/StaffStatusDBAccess.php';
require_once 'models/app_school/LocationDBAccess.php';
require_once 'models/app_school/DescriptionDBAccess.php';
require_once setUserLoacalization();

class StaffDBAccess {

    public $data = array();
    public $dataforjson = array();
    public $entries = array();
    public $out = array();
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

    public function getSaffDataFromId($Id)
    {

        $data = array();

        $result = self::findStaffFromId($Id);

        if ($result)
        {

            $USER_DB_ACCESS = UserMemberDBAccess::getInstance();
            $USER_OBJECT = $USER_DB_ACCESS->findUserFromId($Id);

            if (!$USER_OBJECT->SUPERUSER)
            {
                $data["IS_PASSWORD"] = $USER_OBJECT->PASSWORD ? true : false;
                $data["PASSWORD"] = "**********";
                $data["PASSWORD_REPEAT"] = "**********";
                $data["UMCPANL"] = $USER_OBJECT->UMCPANL ? 1 : 0;
                $data["UCNCP"] = $USER_OBJECT->UCNCP ? 1 : 0;
            }

            $data["BOX_NAME"] = $result->NAME;
            $data["ID"] = $result->ID;
            $data["CODE"] = $result->CODE;
            $data["STAFF_SCHOOL_ID"] = $result->STAFF_SCHOOL_ID;
            $data["TITLE"] = setShowText($result->TITLE);
            $data["POSITION"] = setShowText($result->POSITION);
            $data["LOGINNAME"] = $USER_OBJECT ? $USER_OBJECT->LOGINNAME : $result->CODE;
            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);
            $data["STATUS"] = $result->STATUS;
            $data["TUTOR"] = $result->TUTOR;
            $data["USER_ROLE"] = $this->getUserRoleByStaffId($result->ID);
            $data["FIRSTNAME"] = setShowText($result->FIRSTNAME);
            $data["LASTNAME"] = setShowText($result->LASTNAME);
            $data["FIRSTNAME_LATIN"] = setShowText($result->FIRSTNAME_LATIN);
            $data["LASTNAME_LATIN"] = setShowText($result->LASTNAME_LATIN);
            $data["ADDRESS"] = setShowText($result->ADDRESS);
            $data["RELIGION"] = $result->RELIGION;
            $data["ETHNIC"] = $result->ETHNIC;
            $data["NATIONALITY"] = $result->NATIONALITY;
            $data["GENDER"] = getGenderName($result->GENDER);
            $data["DATE_BIRTH"] = getShowDate($result->DATE_BIRTH);
            $data["JOB_TYPE"] = $result->JOB_TYPE;
            $data["MARRIED"] = setShowText($result->MARRIED);
            $data["BIRTH_PLACE"] = setShowText($result->BIRTH_PLACE);
            $data["SUBJECTS"] = $this->subjectsByTeacher($result->ID);
            $data["EMAIL"] = setShowText($result->EMAIL);
            $data["PHONE_COUNTRY_CODE"] = setShowText($result->PHONE_COUNTRY_CODE);
            $data["PHONE"] = setShowText($result->PHONE);
            $data["MOBIL_PHONE"] = setShowText($result->MOBIL_PHONE);
            $data["COUNTRY_PROVINCE"] = setShowText($result->COUNTRY_PROVINCE);
            $data["POSTCODE_ZIPCODE"] = setShowText($result->POSTCODE_ZIPCODE);
            $data["COUNTRY"] = setShowText($result->COUNTRY);
            $data["TOWN_CITY"] = setShowText($result->TOWN_CITY);
            $data["CREATED_DATE"] = getShowDate($result->CREATED_DATE);
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

    /**
     * Object student by StaffId...
     */
    //@ Visal
    public static function checkStaffFromId($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_staff', 'COUNT(*) AS C');
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    //
    public static function findStaffFromId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff", array("*"));
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function findStaffFromCodeId($codeId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff", array("*"));
        $SQL->where("CODE = '" . strtoupper(trim($codeId)) . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public function getUserNameByCode($codeId)
    {

        $facette = $this->findStaffFromCodeId($codeId);

        if ($facette)
        {
            return $facette->LASTNAME . " " . $facette->FIRSTNAME;
        }
        else
        {
            return "---";
        }
    }

    /**
     * JSON: Staff by StaffId....
     */
    public function loadStaffFromId($Id)
    {
        $result = self::findStaffFromId($Id);

        if ($result)
        {
            $o = array(
                "success" => true
                , "data" => $this->getSaffDataFromId($Id)
            );
        }
        else
        {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function queryAllStaffs($params, $groupby = false)
    {

        $staff_school_id = isset($params["STAFF_SCHOOL_ID"]) ? $params["STAFF_SCHOOL_ID"] : false;
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : false;
        $status = isset($params["status"]) ? addText($params["status"]) : false;
        $searchTutor = isset($params["searchTutor"]) ? $params["searchTutor"] : false;
        $isTutor = isset($params["isTutor"]) ? $params["isTutor"] : false;
        $isInstructor = isset($params["isInstuctor"]) ? $params["isInstuctor"] : false;
        $isTraining = isset($params["isTraining"]) ? addText($params["isTraining"]) : "";
        $educationType = isset($params["educationType"]) ? addText($params["educationType"]) : false;
        $choosegrade = isset($params["choosegrade"]) ? addText($params["choosegrade"]) : false;
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : false;
        $code = isset($params["CODE"]) ? addText($params["CODE"]) : false;
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : false;
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : false;
        $gender = isset($params["GENDER"]) ? addText($params["GENDER"]) : false;
        $email = isset($params["EMAIL"]) ? addText($params["EMAIL"]) : false;
        $phone = isset($params["PHONE"]) ? addText($params["PHONE"]) : false;
        $campusId = isset($params["CHOOSE_CAMPUS"]) ? addText($params["CHOOSE_CAMPUS"]) : false;
        $gradeId = isset($params["CHOOSE_GRADE"]) ? addText($params["CHOOSE_GRADE"]) : false;
        $classId = isset($params["CHOOSE_CLASS"]) ? addText($params["CHOOSE_CLASS"]) : false;
        $subjectId = isset($params["CHOOSE_SUBJECT"]) ? addText($params["CHOOSE_SUBJECT"]) : false;
        $schoolyearId = isset($params["CHOOSE_SCHOOLYEAR"]) ? addText($params["CHOOSE_SCHOOLYEAR"]) : false;
        $chooseTraining = isset($params["CHOOSE_TRAINING"]) ? addText($params["CHOOSE_TRAINING"]) : "";
        $country_province = isset($params["COUNTRY_PROVINCE"]) ? addText($params["COUNTRY_PROVINCE"]) : "";
        $searchOrganizationChart = isset($params["ORGANIZATION"]) ? $params["ORGANIZATION"] : "";
        $town_city = isset($params["TOWN_CITY"]) ? addText($params["TOWN_CITY"]) : "";
        $searchEthnic = isset($params["ETHNIC"]) ? addText($params["ETHNIC"]) : "";
        $nationality = isset($params["NATIONALITY"]) ? addText($params["NATIONALITY"]) : "";
        $searchReligion = isset($params["RELIGION"]) ? addText($params["RELIGION"]) : "";
        $searchUserRole = isset($params["USER_ROLE"]) ? $params["USER_ROLE"] : false;
        $subjectTraining = isset($params["subjectTraining"]) ? $params["subjectTraining"] : "";
        $attendance_type = isset($params["attendance_type"]) ? addText($params["attendance_type"]) : "";
        $teacherType = isset($params["teacherType"]) ? $params["teacherType"] : "";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $major = isset($params["MAJOR"]) ? addText($params["MAJOR"]) : "";
        $qualification_degree = isset($params["QUALIFICATION_DEGREE"]) ? addText($params["QUALIFICATION_DEGREE"]) : "";
        $facette = AcademicDBAccess::findGradeFromId($choosegrade);

        if ($facette)
        {

            switch ($facette->OBJECT_TYPE)
            {
                case "CAMPUS":
                    $campusId = $facette->ID;
                    break;
                case "GRADE":
                    $gradeId = $facette->ID;
                    break;
                case "SCHOOLYEAR":
                    $schoolyearId = $facette->SCHOOL_YEAR;
                    $gradeId = $facette->GRADE_ID;
                    break;
                case "CLASS":
                    $classId = $facette->ID;
                    break;
            }
        }

        $programId = "";
        $levelId = "";
        $termId = "";
        $class = "";
        if ($chooseTraining)
        {
            $DB_TRAINING = TrainingDBAccess::findTrainingFromId($chooseTraining);

            switch ($DB_TRAINING->OBJECT_TYPE)
            {
                case "PROGRAM":
                    $programId = $DB_TRAINING->ID;
                    break;
                case "LEVEL":
                    $programId = $DB_TRAINING->PROGRAM;
                    $levelId = $DB_TRAINING->LEVEL;
                    break;
                case "TERM":
                    $programId = $DB_TRAINING->PROGRAM;
                    $levelId = $DB_TRAINING->LEVEL;
                    $termId = $DB_TRAINING->TERM;
                    break;
                case "CLASS":
                    $programId = $DB_TRAINING->PROGRAM;
                    $levelId = $DB_TRAINING->LEVEL;
                    $termId = $DB_TRAINING->TERM;
                    $class = $DB_TRAINING->ID;
            }
        }

        ////////////////////////////////////////////////////////////////////////
        //PERSON DESCRIPTION...
        ////////////////////////////////////////////////////////////////////////
        $person_description_entries = DescriptionDBAccess::sqlDescription("ALL", "STAFF", false);
        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();
        if ($person_description_entries)
        {
            foreach ($person_description_entries as $value)
            {
                if (isset($params["CHECKBOX_" . $value->ID . ""]))
                {
                    $CHECKBOX_DATA[] = addText($params["CHECKBOX_" . $value->ID . ""]);
                }

                if (isset($params["RADIOBOX_" . $value->ID . ""]))
                {
                    $RADIOBOX_DATA[] = addText($params["RADIOBOX_" . $value->ID . ""]);
                }
            }
        }
        $PERSON_DES_DATA = $CHECKBOX_DATA + $RADIOBOX_DATA;
        $PERSON_DES_IDS = $PERSON_DES_DATA ? implode(",", $PERSON_DES_DATA) : array();
        ////////////////////////////////////////////////////////////////////////

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.ID AS STAFF_ID";
        $SQL .= " ,A.TUTOR AS TUTOR";
        $SQL .= " ,A.CODE AS CODE";
        $SQL .= " ,A.STAFF_SCHOOL_ID AS STAFF_SCHOOL_ID";
        if (!SchoolDBAccess::displayPersonNameInGrid())
        {
            $SQL .= " ,CONCAT(A.LASTNAME,' ',A.FIRSTNAME) AS NAME";
        }
        else
        {
            $SQL .= " ,CONCAT(A.FIRSTNAME,' ',A.LASTNAME) AS NAME";
        }
        $SQL .= " ,A.FIRSTNAME AS FIRSTNAME";
        $SQL .= " ,A.LASTNAME AS LASTNAME";
        $SQL .= " ,A.FIRSTNAME_LATIN AS FIRSTNAME_LATIN";
        $SQL .= " ,A.LASTNAME_LATIN AS LASTNAME_LATIN";
        $SQL .= " ,A.JOB_TYPE AS JOB_TYPE";
        $SQL .= " ,A.GENDER AS GENDER";
        $SQL .= " ,A.RELIGION AS RELIGION";
        $SQL .= " ,A.ETHNIC AS ETHNIC";
        $SQL .= " ,A.NATIONALITY AS NATIONALITY";
        $SQL .= " ,A.DATE_BIRTH AS DATE_BIRTH";
        $SQL .= " ,YEAR(A.DATE_BIRTH) AS BORN_YEAR";
        $SQL .= " ,A.EMAIL AS EMAIL";
        $SQL .= " ,A.PHONE AS PHONE";
        $SQL .= " ,A.PHONE_COUNTRY_CODE AS PHONE_COUNTRY_CODE";
        $SQL .= " ,A.MOBIL_PHONE AS MOBIL_PHONE";
        $SQL .= " ,A.ADDRESS AS ADDRESS";
        $SQL .= " ,A.TOWN_CITY AS TOWN_CITY";
        $SQL .= " ,A.POSTCODE_ZIPCODE AS POSTCODE_ZIPCODE";
        $SQL .= " ,A.COUNTRY_PROVINCE AS COUNTRY_PROVINCE";
        $SQL .= " ,A.COUNTRY AS COUNTRY";
        $SQL .= " ,A.TITLE AS TITLE";
        $SQL .= " ,A.POSITION AS POSITION";
        $SQL .= " ,A.STATUS AS STATUS";
        $SQL .= " ,A.CREATED_DATE AS CREATED_DATE";
        $SQL .= " ,A.MODIFY_DATE AS MODIFY_DATE";
        $SQL .= " ,A.ENABLED_DATE AS ENABLED_DATE";
        $SQL .= " ,A.DISABLED_DATE AS DISABLED_DATE";

        if ($schoolyearId || $gradeId || $classId || $educationType || $searchTutor || $isTutor)
        {
            $SQL .= " ,C.NAME AS INSTRUCTOR";
        }

        $SQL .= " ,A.CREATED_BY AS CREATED_BY";
        $SQL .= " ,A.MODIFY_BY AS MODIFY_BY";
        $SQL .= " ,A.ENABLED_BY AS ENABLED_BY";
        $SQL .= " ,A.DISABLED_BY AS DISABLED_BY";
        $SQL .= " ,E.NAME AS USER_ROLE";
        $SQL .= " ,D.SUPERUSER AS SUPERUSER";

        //@soda
        if ($schoolyearId || $gradeId || $classId || $educationType || $searchTutor || $isTutor || $subjectId)
        {
            $SQL .= " ,W.NAME AS CURRENT_NAME ";
            $SQL .= " ,U.NAME AS LEVEL_NAME_TRAINING";
            $SQL .= " ,V.NAME AS SCHOOL_YEAR_GENERAL";
        }

        if ($chooseTraining)
        {
            $SQL .= " ,K.NAME AS CURRENT_NAME";
            $SQL .= " ,CONCAT(K.START_DATE,' ',K.END_DATE) AS GRADE_NAME";
            $SQL .= " ,Z.NAME AS LEVEL_NAME_TRAINING";
        }

        if ($isTraining || $subjectTraining)
        {
            $SQL .= " ,K.NAME AS CURRENT_NAME";
            $SQL .= " ,CONCAT(K.START_DATE,' ',K.END_DATE) AS GRADE_NAME";
            $SQL .= " ,Z.NAME AS LEVEL_NAME_TRAINING";
        }
        //
        if ($searchOrganizationChart)
        {
            $SQL .= " ,H.ORGANIZATION_ID AS ORGANIZATION_CHART";
        }

        $SQL .= " FROM";
        $SQL .= " t_staff AS A";

        if ($schoolyearId || $gradeId || $classId || $educationType || $searchTutor || $isTutor || $subjectId)
        {
            $SQL .= " LEFT JOIN t_teacher_subject AS B ON A.ID=B.TEACHER";
            $SQL .= " LEFT JOIN t_subject AS I ON I.ID=B.SUBJECT";
            $SQL .= " LEFT JOIN t_grade AS C ON A.ID=C.INSTRUCTOR";
        }

        $SQL .= " LEFT JOIN t_members AS D ON D.ID = A.ID";
        $SQL .= " LEFT JOIN t_memberrole AS E ON E.ID = D.ROLE";
        $SQL .= " LEFT JOIN t_person_infos AS Z ON Z.USER_ID = A.ID";

        if ($schoolyearId || $campusId || $gradeId || $classId || $educationType || $searchTutor || $isTutor)
        {
            $SQL .= " LEFT JOIN t_subject_teacher_class AS F ON A.ID=F.TEACHER";
            $SQL .= " LEFT JOIN t_academicdate AS V ON V.ID=F.SCHOOLYEAR";
            $SQL .= " LEFT JOIN t_grade AS W ON W.ID=F.ACADEMIC";
            $SQL .= " LEFT JOIN t_grade AS U ON U.ID=F.GRADE";
        }

        if ($searchOrganizationChart)
        {
            $SQL .= " LEFT JOIN t_user_organization AS H ON A.ID=H.USER_ID";
            $SQL .= " LEFT JOIN T_ORGANIZATION AS G ON G.ID=H.ORGANIZATION_ID";
        }

        if ($chooseTraining)
        {
            $SQL .= " LEFT JOIN T_SUBJECT_TEACHER_TRAINING AS J ON A.ID=J.TEACHER";
            $SQL .= " LEFT JOIN T_TRAINING AS K ON K.ID=J.TRAINING";
            $SQL .= " LEFT JOIN T_TRAINING AS Z ON Z.ID=J.LEVEL"; //@SODA
        }

        if ($isTraining || $subjectTraining)
        {
            $SQL .= " RIGHT JOIN T_SUBJECT_TEACHER_TRAINING AS J ON A.ID=J.TEACHER";
            $SQL .= " LEFT JOIN T_TRAINING AS K ON K.ID=J.TRAINING";
            $SQL .= " LEFT JOIN T_TRAINING AS Z ON Z.ID=J.LEVEL"; //@SODA
        }

        if ($PERSON_DES_IDS)
        {
            $SQL .= " LEFT JOIN t_person_description_item AS PERSON_DES ON PERSON_DES.PERSON_ID=A.ID";
        }

        if ($attendance_type)
        {
            $SQL .= " RIGHT JOIN t_staff_attendance AS ATT ON ATT.STAFF_ID=A.ID";
        }

        $SQL .= " WHERE 1=1";

        if ($searchUserRole)
        {
            $SQL .= " AND D.ROLE=" . $searchUserRole . "";
        }

        if ($staff_school_id)
            $SQL .= " AND A.STAFF_SCHOOL_ID LIKE '" . strtoupper($staff_school_id) . "%' ";

        if ($code)
            $SQL .= " AND A.CODE LIKE '" . strtoupper($code) . "%' ";

        if ($firstname)
            $SQL .= " AND A.FIRSTNAME LIKE '" . $firstname . "%'";

        if ($lastname)
            $SQL .= " AND A.LASTNAME LIKE '" . $lastname . "%'";

        if ($gender)
            $SQL .= " AND A.GENDER='" . $gender . "'";

        if ($phone)
            $SQL .= " AND A.PHONE LIKE '" . $phone . "%'";

        if ($email)
            $SQL .= " AND A.EMAIL LIKE '" . $email . "%'";

        if ($searchOrganizationChart)
            $SQL .= " AND H.ORGANIZATION_ID=" . $searchOrganizationChart . "";

        if ($teacherType)
        {
            switch ($teacherType)
            {
                case 1:
                    $SQL .= " AND A.TUTOR='1'";
                    break;
                case 2:
                    $SQL .= " AND A.TUTOR='2'";
                    break;
                default:
                    $SQL .= " AND A.TUTOR='" . $teacherType . "'";
                    break;
            }
        }

        if ($searchTutor)
        {

            switch ($this->getTutorByRoleId($searchTutor))
            {
                case 1:
                    $SQL .= " AND A.TUTOR='1'";
                    break;
                case 2:
                    $SQL .= " AND A.TUTOR='2'";
                    break;
                default:
                    $SQL .= " AND A.TUTOR='" . $searchTutor . "'";
                    break;
            }
        }

        if ($isInstructor)
        {
            $SQL .= " AND E.ID='2'";
        }

        if ($status)
        {
            $SQL .= " AND A.STATUS IN (1)";
        }

        if ($isTutor)
            $SQL .= " AND E.ID IN (2,3)";

        if ($nationality)
            $SQL .= " AND A.NATIONALITY='" . $nationality . "' ";

        if ($major)
            $SQL .= " AND Z.MAJOR='" . $major . "' ";

        if ($qualification_degree)
            $SQL .= " AND Z.QUALIFICATION_DEGREE='" . $qualification_degree . "' ";

        if ($searchReligion)
            $SQL .= " AND A.RELIGION='" . $searchReligion . "' ";

        if ($searchEthnic)
            $SQL .= " AND A.ETHNIC='" . $searchEthnic . "' ";

        if ($campusId && $gradeId == "")
        {
            $SQL .= " AND F.GRADE IN (SELECT ID FROM t_grade WHERE GRADE_ID=0 AND CAMPUS_ID=" . $campusId . ")";
        }

        if ($gradeId)
            $SQL .= " AND F.GRADE=" . $gradeId;

        if ($classId)
            $SQL .= " AND F.ACADEMIC='" . $classId . "'";

        if ($schoolyearId)
            $SQL .= " AND F.SCHOOLYEAR='" . $schoolyearId . "'";

        if ($subjectId)
            $SQL .= " AND B.SUBJECT=" . $subjectId . "";

        if ($teacherId)
        {
            $SQL .= " AND A.ID='" . $teacherId . "'";
        }

        if ($globalSearch)
        {
            $SQL .= " AND ((A.NAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }

        if ($subjectTraining)
        {
            $SQL .= " AND J.SUBJECT=" . $subjectTraining;
        }

        if ($programId)
        {
            $SQL .= " AND K.PROGRAM=" . $programId;
        }

        if ($levelId)
        {
            $SQL .= " AND K.LEVEL=" . $levelId;
        }

        if ($termId)
        {
            $SQL .= " AND K.TERM=" . $termId;
        }

        if ($class)
        {
            $SQL .= " AND K.ID=" . $class;
        }

        if ($country_province)
        {
            $SQL .= " AND COUNTRY_PROVINCE=" . $country_province;
        }

        if ($town_city)
        {
            $SQL .= " AND TOWN_CITY=" . $town_city;
        }

        if ($attendance_type)
        {
            $SQL .= " AND ATTENDANCE_TYPE=" . $attendance_type;
            if ($dateStart != "" && $dateEnd != "")
            {
                $SQL .= " AND ( (ATT.start_date between '" . $dateStart . "' AND '" . $dateEnd . "') or (ATT.end_date between '" . $dateStart . "' AND '" . $dateEnd . "') )";
            }
        }

        if ($startDate && $endDate)
        {
            $SQL .= " AND (DATE(A.CREATED_DATE) >= '" . $startDate . "' AND DATE(A.CREATED_DATE) <= '" . $endDate . "')";
        }

        if ($PERSON_DES_IDS)
        {
            $SQL .= " AND PERSON_DES.ITEM IN (" . $PERSON_DES_IDS . ")";
        }

        if ($groupby)
        {
            $SQL .= " GROUP BY " . $groupby . "";
        }
        else
        {
            $SQL .= " GROUP BY A.ID";
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY)
        {
            default:
                $SQL .= " ORDER BY A.STAFF_SCHOOL_ID DESC";
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

    public function searchStaffs($params, $isJson = true)
    {

        /**
         * Advanced Search Staff....
         */
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        //@veasna
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        if ($studentId)
        {
            $results = StudentAcademicDBAccess::getCurrentClassByStudentID($studentId);
            if ($results)
            {
                foreach ($results as $values)
                {
                    $arr[] = $values->CLASS_ID;
                }
                $classId = implode(",", $arr);
                $params["classId"] = $classId;
            }
        }
        //

        $result = $this->queryAllStaffs($params, "A.ID", "A.FIRSTNAME");

        $i = 0;

        $data = array();

        if ($result)
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["ASSIGNED_SUBJECTS"] = $this->subjectsByTeacher($value->ID, true);
                $data[$i]["SUPERUSER"] = $value->SUPERUSER;
                $data[$i]["CARD_USER_INFO"] = iconCardUserInfo();
                $data[$i]["STAFF_SCHOOL_ID"] = setShowText($value->STAFF_SCHOOL_ID);
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["STATUS"] = $value->STATUS;
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["STAFF"] = setShowText($value->NAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["ADDRESS"] = setShowText($value->ADDRESS);
                $data[$i]["COUNTRY_PROVINCE"] = setShowText($value->COUNTRY_PROVINCE);
                $data[$i]["COUNTRY"] = setShowText($value->COUNTRY);
                $data[$i]["POSTCODE_ZIPCODE"] = setShowText($value->POSTCODE_ZIPCODE);
                $data[$i]["TOWN_CITY"] = setShowText($value->TOWN_CITY);

                $data[$i]["CONTACT"] = $data[$i]["MOBIL_PHONE"] . "<br>" .
                        $data[$i]["PHONE"] . "<br>" .
                        $data[$i]["EMAIL"];

                $data[$i]["RELIGION"] = $this->setReligion($value->RELIGION);
                $data[$i]["ETHNIC"] = $this->setEthnic($value->ETHNIC);
                $data[$i]["NATIONALITY"] = $this->setNationality($value->NATIONALITY);

                if (isset($value->ORGANIZATION_CHART))
                {
                    $data[$i]["ORGANIZATION_CHART"] = $this->setOrganization($value->ORGANIZATION_CHART);
                }
                else
                {
                    $data[$i]["ORGANIZATION_CHART"] = "---";
                }

                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["TITLE"] = setShowText($value->TITLE);
                $data[$i]["POSITION"] = setShowText($value->POSITION);
                $data[$i]["USER_ROLE"] = setShowText($value->USER_ROLE);
                $data[$i]["JOB_TYPE"] = getJobTypeICONV($value->JOB_TYPE);

                ////////////////////////////////////////////////////////////////
                //Status of staff...
                ////////////////////////////////////////////////////////////////
                $STATUS_DATA = StaffStatusDBAccess::getCurrentStaffStatus($value->ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["SUBJECTS"] = $this->subjectsByTeacher($value->ID, true);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["MODIFY_DATE"] = getShowDateTime($value->MODIFY_DATE);
                $data[$i]["ENABLED_DATE"] = getShowDateTime($value->ENABLED_DATE);
                $data[$i]["DISABLED_DATE"] = getShowDateTime($value->DISABLED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);
                $data[$i]["MODIFY_BY"] = setShowText($value->MODIFY_BY);
                $data[$i]["ENABLED_BY"] = setShowText($value->ENABLED_BY);
                $data[$i]["DISABLED_BY"] = setShowText($value->DISABLED_BY);
                //@soda

                if (isset($value->CURRENT_NAME))
                {
                    $data[$i]["CURRENT_CLASS"] = setShowText($value->CURRENT_NAME);
                }
                else
                {
                    $data[$i]["CURRENT_CLASS"] = '---';
                }
                if (isset($value->GRADE_NAME))
                {
                    $data[$i]["GRADE"] = $value->GRADE_NAME;
                }
                else
                {
                    $data[$i]["GRADE"] = '---';
                }
                if (isset($value->LEVEL_NAME_TRAINING))
                {
                    $data[$i]["LEVEL_NAME"] = setShowText($value->LEVEL_NAME_TRAINING);
                }
                else
                {
                    $data[$i]["LEVEL_NAME"] = '---';
                }

                if (isset($value->SCHOOL_YEAR_GENERAL))
                {
                    $data[$i]["SCHOOL_YEAR"] = setShowText($value->SCHOOL_YEAR_GENERAL);
                }
                else
                {
                    $data[$i]["SCHOOL_YEAR"] = '---';
                }
                //
                $i++;
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );

        if ($isJson)
        {
            return $this->dataforjson;
        }
        else
        {
            return $data;
        }
    }

    public function updateStaff($params)
    {

        $errors = array();

        if (isset($params["SYSTEM_LANGUAGE"]))
            $SAVEDATA['SYSTEM_LANGUAGE'] = addText($params["SYSTEM_LANGUAGE"]);

        if (isset($params["CODE"]))
            $SAVEDATA['CODE'] = $params["CODE"];

        if (isset($params["STAFF_SCHOOL_ID"]))
            $SAVEDATA['STAFF_SCHOOL_ID'] = addText($params["STAFF_SCHOOL_ID"]);

        if (isset($params["TITLE"]))
            $SAVEDATA['TITLE'] = addText($params["TITLE"]);

        if (isset($params["START_DATE"]))
            $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);

        if (isset($params["END_DATE"]))
            $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]);

        if (isset($params["MARRIED"]))
            $SAVEDATA['MARRIED'] = $params["MARRIED"];

        if (isset($params["JOB_TYPE"]))
            $SAVEDATA['JOB_TYPE'] = $params["JOB_TYPE"];

        if (isset($params["BIRTH_PLACE"]))
            $SAVEDATA['BIRTH_PLACE'] = $params["BIRTH_PLACE"];

        if (isset($params["FIRSTNAME"]))
            $SAVEDATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);

        if (isset($params["LASTNAME"]))
            $SAVEDATA['LASTNAME'] = addText($params["LASTNAME"]);

        if (isset($params["FIRSTNAME_LATIN"]))
            $SAVEDATA['FIRSTNAME_LATIN'] = addText($params["FIRSTNAME_LATIN"]);

        if (isset($params["LASTNAME_LATIN"]))
            $SAVEDATA['LASTNAME_LATIN'] = addText($params["LASTNAME_LATIN"]);

        if (isset($params["GENDER"]))
            $SAVEDATA['GENDER'] = (int) $params["GENDER"];

        if (isset($params["DATE_BIRTH"]))
            $SAVEDATA['DATE_BIRTH'] = setDate2DB($params["DATE_BIRTH"]);

        if (isset($params["ADDRESS"]))
            $SAVEDATA['ADDRESS'] = addText($params["ADDRESS"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["TOWN_CITY"]))
            $SAVEDATA['TOWN_CITY'] = addText($params["TOWN_CITY"]);

        if (isset($params["PHONE"]))
        {
            $SAVEDATA['PHONE'] = addText($params["PHONE"]);
            $SAVEDATA['MOBIL_PHONE'] = addText($params["PHONE"]);
        }

        if (isset($params["MOBIL_PHONE"]))
        {
            $SAVEDATA['MOBIL_PHONE'] = addText($params["MOBIL_PHONE"]);
            $SAVEDATA['PHONE'] = addText($params["MOBIL_PHONE"]);
        }

        if (isset($params["COUNTRY_PROVINCE"]))
        {
            $SAVEDATA['COUNTRY_PROVINCE'] = addText($params["COUNTRY_PROVINCE"]);
        }

        if (isset($params["POSTCODE_ZIPCODE"]))
            $SAVEDATA['POSTCODE_ZIPCODE'] = addText($params["POSTCODE_ZIPCODE"]);

        if (isset($params["COUNTRY"]))
            $SAVEDATA['COUNTRY'] = addText($params["COUNTRY"]);

        if (isset($params["QUALIFICATION"]))
            $SAVEDATA['QUALIFICATION'] = addText($params["QUALIFICATION"]);

        if (isset($params["PROFESSION"]))
            $SAVEDATA['PROFESSION'] = addText($params["PROFESSION"]);

        if (isset($params["BACKGROUND_INFO"]))
            $SAVEDATA['BACKGROUND_INFO'] = addText($params["BACKGROUND_INFO"]);

        if (isset($params["WORK_EXPERIENCE"]))
            $SAVEDATA['WORK_EXPERIENCE'] = addText($params["WORK_EXPERIENCE"]);

        if (isset($params["FIRSTNAME"]) && isset($params["LASTNAME"]))
        {
            $SAVEDATA['NAME'] = addText($params["LASTNAME"]) . " " . addText($params["FIRSTNAME"]);
        }

        if (isset($params["RELIGION"]))
            $SAVEDATA['RELIGION'] = addText($params["RELIGION"]);

        if (isset($params["ETHNIC"]))
            $SAVEDATA['ETHNIC'] = addText($params["ETHNIC"]);

        if (isset($params["NATIONALITY"]))
            $SAVEDATA['NATIONALITY'] = addText($params["NATIONALITY"]);

        $loginName = isset($params["LOGINNAME"]) ? addText($params["LOGINNAME"]) : "";

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        $SAVEDATA['TS'] = time();

        if (!$errors)
        {

            if (isset($params["objectId"]))
            {

                $USER_OBJECT = UserMemberDBAccess::findUserFromId($params["objectId"]);

                $WHERE[] = "ID = '" . addText($params["objectId"]) . "'";
                self::dbAccess()->update('t_staff', $SAVEDATA, $WHERE);

                if (isset($params["FIRSTNAME"]))
                    $USERDATA['FIRSTNAME'] = addText($params["FIRSTNAME"]);

                if (isset($params["LASTNAME"]))
                    $USERDATA['LASTNAME'] = addText($params["LASTNAME"]);

                if (isset($params["EMAIL"]))
                    $USERDATA['EMAIL'] = addText($params["EMAIL"]);

                if (isset($params["PHONE"]))
                    $USERDATA['PHONE'] = addText($params["PHONE"]);

                if (isset($params["MOBIL_PHONE"]))
                    $USERDATA['MOBIL_PHONE'] = addText($params["MOBIL_PHONE"]);

                if (isset($params["TUTOR"]))
                    $USERDATA['ROLE'] = $params["TUTOR"];

                if (isset($params["LASTNAME"]) && isset($params["FIRSTNAME"]))
                {
                    $USERDATA['NAME'] = addText($params["LASTNAME"]) . " " . addText($params["FIRSTNAME"]);
                }

                ////////////////////////////////////////////////////////////////
                //CHANGE PASSWORD...
                ////////////////////////////////////////////////////////////////
                $password = isset($params["PASSWORD"]) ? addText($params["PASSWORD"]) : "";
                $password_repeat = isset($params["PASSWORD_REPEAT"]) ? addText($params["PASSWORD_REPEAT"]) : "";
                $USERDATA['UMCPANL'] = isset($params["UMCPANL"]) ? 1 : 0;
                $USERDATA['UCNCP'] = isset($params["UCNCP"]) ? 1 : 0;
                if ($password != "" && $password_repeat != "")
                {
                    if (strlen($password) < UserAuth::getMinPasswordLength())
                    {
                        $errors['PASSWORD'] = PASSWORD_IS_TOO_SHORT;
                    }
                    else
                    {
                        if (UserAuth::isPasswordComplexityRequirements())
                        {
                            if (!preg_match("#[0-9]+#", $password))
                            {
                                $errors['PASSWORD'] = PASSWORD_MUST_INCLUDE_AT_LEAST_ONE_NUMBER;
                            }

                            if (!preg_match("#[a-zA-Z]+#", $password))
                            {
                                $errors['PASSWORD'] = PASSWORD_MUST_INCLUDE_AT_LEAST_ONE_LETTER;
                            }
                        }
                    }
                    if ($password == $password_repeat)
                    {
                        $USERDATA['CHANGE_PASSWORD'] = 1;
                        $USERDATA['UMCPANL'] = 0;
                        $USERDATA['CHANGE_PASSWORD_DATE'] = time();
                        $USERDATA['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
                    }
                }
                ////////////////////////////////////////////////////////////////
                if (isset($params["USER_ROLE"]))
                {
                    $USERDATA['ROLE'] = $params["USER_ROLE"];
                }

                $USERDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $USERDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

                if ($USER_OBJECT)
                {
                    $objectId = $params["objectId"];
                    if ($loginName != "")
                    {
                        $loginObject = self::findLoginName($loginName);
                        if ($loginObject)
                        {
                            if ($objectId != $loginObject->ID)
                            {
                                $errors['LOGINNAME'] = LOGINNAME_NOT_AVAILABLE;
                            }
                        }
                        $USERDATA['LOGINNAME'] = addText($params["LOGINNAME"]);
                    }

                    $WHERE[] = "ID = '" . $objectId . "'";
                    if (!$errors)
                        self::dbAccess()->update('t_members', $USERDATA, $WHERE);
                } else
                {

                    if (isset($params["objectId"]))
                        $INSERT_USER['ID'] = $params["objectId"];

                    if (isset($params["USER_ROLE"]))
                    {
                        $INSERT_USER['ROLE'] = $params["USER_ROLE"];
                    }

                    if (isset($params["FIRSTNAME"]) && isset($params["LASTNAME"]))
                    {
                        $INSERT_USER['NAME'] = addText($params["LASTNAME"]) . " " . addText($params["FIRSTNAME"]);
                    }

                    if (isset($params["FIRSTNAME"]))
                        $INSERT_USER['FIRSTNAME'] = addText($params["FIRSTNAME"]);

                    if (isset($params["LASTNAME"]))
                        $INSERT_USER['LASTNAME'] = addText($params["LASTNAME"]);

                    if (isset($params["EMAIL"]))
                        $INSERT_USER['EMAIL'] = addText($params["EMAIL"]);

                    if (isset($params["CODE"]))
                        $INSERT_USER['CODE'] = addText($params["CODE"]);

                    $INSERT_USER['CREATED_DATE'] = getCurrentDBDateTime();
                    $INSERT_USER['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

                    self::dbAccess()->insert('t_members', $INSERT_USER);
                }
            }
        }

        if (sizeof($errors))
        {
            $o = array("success" => false, "errors" => $errors);
        }
        else
        {
            $o = array("success" => true);
        }

        return $o;
    }

    public function releaseObject($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findStaffFromId($objectId);

        $status = $facette->STATUS;

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_staff";
        $SQL .= " SET";

        switch ($status)
        {
            case 0:
                $newStatus = 1;
                $USERDATA["STATUS"] = 1;
                $SQL .= " STATUS=1";
                $SQL .= " ,ENABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,ENABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
            case 1:
                $newStatus = 0;
                $USERDATA["STATUS"] = 0;
                $SQL .= " STATUS=0";
                $SQL .= " ,DISABLED_DATE='" . getCurrentDBDateTime() . "'";
                $SQL .= " ,DISABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                break;
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $objectId . "'";

        self::dbAccess()->query($SQL);

        $WHERE[] = "ID = '" . $objectId . "'";
        self::dbAccess()->update('t_members', $USERDATA, $WHERE);

        return array("success" => true, "status" => $newStatus);
    }

    /**
     * registrationRecord
     */
    public function registrationRecord($params)
    {

        if (isset($params["firstname"]))
            $SAVEDATA['FIRSTNAME'] = addText($params["firstname"]);

        if (isset($params["lastname"]))
            $SAVEDATA['LASTNAME'] = addText($params["lastname"]);

        if (isset($params["firstname"]) && isset($params["lastname"]))
        {
            $SAVEDATA['NAME'] = addText($params["firstname"]) . ", " . addText($params["lastname"]);
        }

        if (isset($params["gender"]))
            $SAVEDATA['GENDER'] = $params["gender"];

        if (isset($params["datebirth"]))
            $SAVEDATA['DATE_BIRTH'] = $params["datebirth"];

        if (isset($params["birth_place"]))
            $SAVEDATA['BIRTH_PLACE'] = addText($params["birth_place"]);

        if (isset($params["address"]))
            $SAVEDATA['ADDRESS'] = addText($params["address"]);

        if (isset($params["postcode_zipcode"]))
            $SAVEDATA['POSTCODE_ZIPCODE'] = addText($params["postcode_zipcode"]);

        if (isset($params["country"]))
            $SAVEDATA['COUNTRY'] = addText($params["country"]);

        if (isset($params["generaledu"]))
            $SAVEDATA['QUALIFICATION'] = addText($params["generaledu"]);

        if (isset($params["married"]))
            $SAVEDATA['MARRIED'] = addText($params["married"]);

        if (isset($params["children"]))
            $SAVEDATA['NUMBER_CHILDREN'] = addText($params["children"]);

        if (isset($params["town_city"]))
            $SAVEDATA['TOWN_CITY'] = addText($params["town_city"]);

        if (isset($params["country_province"]))
            $SAVEDATA['COUNTRY_PROVINCE'] = addText($params["country_province"]);

        if (isset($params["phone"]))
            $SAVEDATA['PHONE'] = $params["phone"];

        if (isset($params["email"]))
            $SAVEDATA['EMAIL'] = addText($params["email"]);

        if (isset($params["nationality"]))
            $SAVEDATA['NATIONALITY'] = $params["nationality"];

        if (isset($params["profession"]))
            $SAVEDATA['PROFESSION'] = addText($params["profession"]);

        if (isset($params["qualification"]))
            $SAVEDATA['QUALIFICATION'] = addText($params["qualification"]);

        if (isset($params["work_experience"]))
            $SAVEDATA['WORK_EXPERIENCE'] = addText($params["work_experience"]);

        if (isset($params["title"]))
            $SAVEDATA['TITLE'] = addText($params["title"]);

        if (isset($params["position"]))
            $SAVEDATA['POSITION'] = addText($params["position"]);

        if (isset($params["married"]))
            $SAVEDATA['MARRIED'] = $params["married"];

        if (isset($params["job_type"]))
            $SAVEDATA['JOB_TYPE'] = $params["job_type"];

        if (isset($params["number_children"]))
            $SAVEDATA['NUMBER_CHILDREN'] = $params["number_children"];

        if (isset($params["userRole"]))
            $SAVEDATA['TUTOR'] = $this->getTutorByRoleId($params["userRole"]);
            
        $SAVEDATA['ID'] = $params["staffId"];
        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT)
        {
            $SAVEDATA['STATUS'] = 1;
        }
        $SAVEDATA['CODE'] = createCode();

        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        $SAVEDATA['TS'] = time();

        $STAFF_OBJECT = self::findStaffFromId($params["staffId"]);

        if (!$STAFF_OBJECT)
        {

            self::dbAccess()->insert('t_staff', $SAVEDATA);
            
            ///////////////////////////////////////////////
            /// create staff contract 
            /// @veasna
            ///////////////////////////////////////////////
            if (isset($params["CONTRACT_TYPE"])){
                
                $contract_params['NAME']= addText($params["NAME"]);
                $contract_params['CONTRACT_TYPE']= $params["CONTRACT_TYPE"]; 
                $contract_params['START_DATE']= $params["START_DATE"];
                $contract_params['EXPIRED_DATE']= $params["EXPIRED_DATE"];
                $contract_params['STAFF_ID']= $params["staffId"];
                $contract_params['CREATED_DATE'] = getCurrentDBDateTime();
                $contract_params['CREATED_BY'] = Zend_Registry::get('USER')->ID;
                self::dbAccess()->insert('t_staff_contract', $contract_params);  
                   
            }

            ///////////////////////////////////////////////////
            // CREATE USER....
            ///////////////////////////////////////////////////
            $SAVEDATA_USER['ID'] = $params["staffId"];
            $SAVEDATA_USER['ROLE'] = $params["userRole"];
            $SAVEDATA_USER['NAME'] = addText(addText($params["firstname"]) . " " . addText($params["lastname"]));
            $SAVEDATA_USER['FIRSTNAME'] = addText(addText($params["firstname"]));
            $SAVEDATA_USER['LASTNAME'] = addText($params["lastname"]);
            $SAVEDATA_USER['EMAIL'] = $params["email"];
            $SAVEDATA_USER['LID'] = createCode();
            $SAVEDATA_USER['LOGINNAME'] = createCode();
            $SAVEDATA_USER['CODE'] = createCode();
            $SAVEDATA_USER['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA_USER['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $password = '123'; //@Math Man
            if (Zend_Registry::get('SCHOOL')->SET_DEFAULT_PASSWORD)
            {
                $SAVEDATA_USER['PASSWORD'] = md5("123-D99A6718-9D2A-8538-8610-E048177BECD5");
            }
            else
            { //@Math Man
                $password = createpassword();
                $SAVEDATA_USER['PASSWORD'] = md5($password . "-D99A6718-9D2A-8538-8610-E048177BECD5");
            }
            self::dbAccess()->insert('t_members', $SAVEDATA_USER);
            $CAMPUS_DATA["STAFF"] = $params["staffId"];
            $CAMPUS_DATA["CAMPUS"] = isset($params["campusId"]) ? addText($params["campusId"]) : "";
            self::dbAccess()->insert('t_staff_campus', $CAMPUS_DATA);
            // END
            ///////////////////////////////////////////////////
            //@Math Man
            if (isset($params["email"]))
            {
                $result = SchoolDBAccess::getSchool();
                $sendTo = addText($params["email"]);
                $recipientName = addText($params["lastname"]) . " " . addText($params["firstname"]);
                if ($result->DISPLAY_POSITION_LASTNAME == 1)
                    $recipientName = addText($params["firstname"]) . " " . addText($params["lastname"]);
                $subject_email = $result->ACCOUNT_CREATE_SUBJECT;
                $content_email = $result->SALUTATION_EMAIL . ' ' . $recipientName . ',' . "\r\n";
                $content_email .= "\r\n" . $result->ACCOUNT_CREATE_NOTIFICATION . "\r\n";
                $content_email .= SCHOOL . ': ' . $result->NAME . "\r\n";
                $content_email .= WEBSITE . ': http://' . $_SERVER['SERVER_NAME'] . "\r\n";
                $content_email .= LOGINNAME . ': ' . $SAVEDATA_USER['LOGINNAME'] . "\r\n";
                $content_email .= PASSWORD . ': ' . $password . "\r\n";
                $content_email .= "\r\n" . $result->SIGNATURE_EMAIL . "\r\n";
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                if ($result->SMS_DISPLAY)
                {
                    $headers .= 'From:' . $result->SMS_DISPLAY . "\r\n" .
                            'Reply-To:' . $result->SMS_DISPLAY . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();
                }
                else
                {
                    $headers .= 'From: noreply@camemis.com' . "\r\n" .
                            'Reply-To: noreply@camemis.com' . "\r\n" .
                            'X-Mailer: PHP/' . phpversion();
                }
                mail($sendTo, '=?utf-8?B?' . base64_encode($subject_email) . '?=', $content_email, $headers);
            }
            //////////////////////////////
        }

        return array("success" => true, "Id" => $params["staffId"]);
    }

    public function allTeachersByGrade()
    {

        $utiles = Utiles::getInstance();
        $gradeId = $utiles->getValueRegistry("GRADE_ID");

        $SQL = "";
        $SQL .= " SELECT A.ID AS ID, CONCAT('(',A.CODE,') ',A.LASTNAME,' ', A.FIRSTNAME) AS NAME";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " WHERE 1=1 AND A.FIRSTNAME<>'' AND A.LASTNAME<>''";
        $SQL .= " AND A.TUTOR IN (1,2)";
        $SQL .= " AND A.FIRSTNAME<>''";
        $SQL .= " AND A.LASTNAME<>''";
        $SQL .= " AND B.GRADE = '" . $gradeId . "'";
        $SQL .= " AND A.STATUS = 1";
        $SQL .= " GROUP BY A.ID";
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function academicHistoryTree($params)
    {

        $data = array();

        $teacherId = isset($params["objectId"]) ? addText($params["objectId"]) : "0";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "0";

        if (substr($params["node"], 8))
        {
            $isClass = true;
            $node = str_replace('CAMEMIS_', '', $params["node"]);
        }
        else
        {
            $isClass = false;
            $node = $params["node"];
        }

        $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($node);
        $classObject = AcademicDBAccess::findGradeFromId($node);

        if (is_numeric($node) && $node == 0)
        {

            $FIRST_DATA = array();

            $SQL1 = "";
            $SQL1 .= " SELECT B.ID AS SCHOOLYEAR_ID, B.NAME AS SCHOOLYEAR_NAME";
            $SQL1 .= " FROM t_subject_teacher_class AS A";
            $SQL1 .= " LEFT JOIN t_academicdate AS B ON B.ID=A.SCHOOLYEAR";
            $SQL1 .= " LEFT JOIN t_grade AS C ON C.ID=A.GRADE";
            $SQL1 .= " WHERE 1=1";
            $SQL1 .= " AND A.TEACHER ='" . $teacherId . "'";
            $SQL1 .= " GROUP BY A.SCHOOLYEAR";
            //error_log($SQL);
            $result1 = self::dbAccess()->fetchAll($SQL1);

            if ($result1)
            {
                foreach ($result1 as $value)
                {
                    $FIRST_DATA[$value->SCHOOLYEAR_ID] = $value->SCHOOLYEAR_NAME;
                }
            }

            $SECND_DATA = array();

            $SQL2 = "";
            $SQL2 .= " SELECT C.ID AS SCHOOLYEAR_ID, C.NAME AS SCHOOLYEAR_NAME";
            $SQL2 .= " FROM t_instructor AS A";
            $SQL2 .= " LEFT JOIN t_grade AS B ON B.ID=A.CLASS";
            $SQL2 .= " LEFT JOIN t_academicdate AS C ON C.ID=B.SCHOOL_YEAR";
            $SQL2 .= " WHERE 1=1";
            $SQL2 .= " AND A.TEACHER ='" . $teacherId . "'";
            $SQL2 .= " GROUP BY B.SCHOOL_YEAR";
            //error_log($SQL);
            $result2 = self::dbAccess()->fetchAll($SQL2);

            if ($result2)
            {
                foreach ($result2 as $value)
                {
                    $SECND_DATA[$value->SCHOOLYEAR_ID] = $value->SCHOOLYEAR_NAME;
                }
            }

            $result = array_unique($FIRST_DATA + $SECND_DATA);
        }
        else
        {

            if ($schoolyearObject && !$classObject)
            {

                $result = self::getClassesByTutor($teacherId, $node);
            }
            elseif (!$schoolyearObject && $classObject)
            {
                $SQL = "";
                $SQL .= " 
				SELECT A.ID AS TASK_ID
				,B.ID AS SUBJECT_ID
				,B.SUBJECT_TYPE AS SUBJECT_TYPE
				,B.EVALUATION_TYPE AS EVALUATION_TYPE
				,B.COEFF AS SUBJECT_COEFF
				,B.NAME AS SUBJECT_NAME
				,B.GUID AS SUBJECT_GUID
				,C.ID AS CLASS_ID
				,C.GRADE_ID AS GRADE_ID
				,C.GUID AS GUID
				,C.NAME AS CLASS_NAME
				,D.ID AS SCHOOLYEAR_ID
				,D.NAME AS SCHOOLYEAR_NAME
				";
                $SQL .= " FROM t_subject_teacher_class AS A";
                $SQL .= " LEFT JOIN t_subject AS B ON B.ID=A.SUBJECT";
                $SQL .= " LEFT JOIN t_grade AS C ON C.ID=A.ACADEMIC";
                $SQL .= " LEFT JOIN t_academicdate AS D ON D.ID=A.SCHOOLYEAR";
                $SQL .= " WHERE 1=1";
                $SQL .= " AND B.SUBJECT_TYPE<>4";
                $SQL .= " AND A.TEACHER ='" . $teacherId . "'";
                $SQL .= " AND A.ACADEMIC ='" . $node . "'";
                $SQL .= " GROUP BY B.ID ORDER BY B.NAME ";
                //error_log($SQL);
                $result = self::dbAccess()->fetchAll($SQL);
            }
        }

        if (is_numeric($node) && $node == 0)
        {
            $i = 0;
            if ($result)
            {
                foreach ($result as $key => $value)
                {
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-bricks";
                    $data[$i]['treeType'] = "SCHOOLYEAR";
                    $data[$i]['id'] = $key;
                    $data[$i]['text'] = $value;
                    ++$i;
                }
            }
        }
        else
        {

            if ($schoolyearObject && !$classObject)
            {
                $i = 0;
                if ($result)
                {
                    foreach ($result as $value)
                    {

                        $data[$i]['id'] = "CAMEMIS_" . $value->ID;
                        $data[$i]['text'] = $value->NAME;
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-blackboard";
                        $data[$i]['treeType'] = "CLASS";
                        $data[$i]['classId'] = $value->ID;
                        $data[$i]['schoolyearId'] = $value->SCHOOL_YEAR;
                        $data[$i]['gradeId'] = $value->GRADE_ID;
                        ++$i;
                    }
                }
            }
            elseif (!$schoolyearObject && $classObject)
            {
                if ($result)
                {
                    $i = 0;
                    foreach ($result as $value)
                    {
                        //// chungveng
                        $gradeSubjectObject = GradeSubjectDBAccess::getGradeSubject(
                                        false
                                        , $value->GRADE_ID
                                        , $value->SUBJECT_ID
                                        , $value->SCHOOLYEAR_ID
                                        , $classId);
                        $data[$i]['gradeSubjectId'] = $gradeSubjectObject ? $gradeSubjectObject->ID : "";
                        ////
                        $data[$i]['leaf'] = true;
                        $data[$i]['iconCls'] = "icon-note";
                        $data[$i]['treeType'] = "SUBJECT";
                        $data[$i]['className'] = $value->SCHOOLYEAR_NAME . " &raquo; " . $value->CLASS_NAME;
                        $data[$i]['id'] = createCode();
                        $data[$i]['subjectdisplayId'] = $value->SUBJECT_ID . "_" . $value->SCHOOLYEAR_ID . "_" . $value->GRADE_ID;
                        $data[$i]['classId'] = $value->CLASS_ID;
                        $data[$i]['subjectId'] = $value->SUBJECT_ID;
                        $data[$i]['gradeId'] = $value->GRADE_ID;
                        $data[$i]['subjectGuId'] = $value->SUBJECT_GUID;
                        $data[$i]['schoolyearId'] = $value->SCHOOLYEAR_ID;
                        $data[$i]['subjectSchoolyear'] = $value->TASK_ID;
                        $data[$i]['text'] = "<b>" . $value->SUBJECT_NAME . "</b>";
                        $data[$i]['teacherId'] = $teacherId;
                        ++$i;
                    }
                }
            }
        }
        return $data;
    }

    public function findTaskFromId($Id)
    {

        $SQL = "";
        $SQL .= " SELECT A.GRADE AS GRADE_ID, A.SUBJECT AS SUBJECT_ID, A.ACADEMIC AS CLASS_ID";
        $SQL .= " FROM t_subject_teacher_class AS A";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function checkStaffInSchedule($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_teaching_session", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER_ID = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///////////////////////////////////////////////////////
    // Finde, ob diese Belegschaft bei Anwesenheitslist vorhandelt.
    ///////////////////////////////////////////////////////
    protected function checkStaffInSubjectTeacherClass($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///////////////////////////////////////////////////////
    // Staff in Grade (Instructor)....
    ///////////////////////////////////////////////////////
    public function gettStaffInGrade($Id, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("INSTRUCTOR = '" . $Id . "'");
        if ($schoolyearId)
            $SQL->where("SCHOOL_YEAR = '" . $schoolyearId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected function checkStaffInGrade($Id, $schoolyearId)
    {

        $facette = $this->gettStaffInGrade($Id, $schoolyearId);
        return $facette ? 1 : 0;
    }

    public function jsonRemoveStaffFromSchool($params)
    {

        $removeId = $params["removeId"];

        $check = $this->checkStaffAssignment($removeId);
        $in_use = false;

        if ($check)
        {
            $removeStatus = false;
            $in_use = true;
        }
        else
        {
            $removeStatus = true;
            self::dbAccess()->delete('t_staff', array("ID = '" . $removeId . "'"));
            self::dbAccess()->delete('t_members', array("ID = '" . $removeId . "'"));
            self::dbAccess()->delete('t_teacher_subject', array("TEACHER = '" . $removeId . "'"));
            self::dbAccess()->delete('t_staff_campus', array("STAFF = '" . $removeId . "'"));
        }

        return array(
            "success" => true
            , "REMOVE_STATUS" => $removeStatus
            , "IN_USE" => $in_use
        );
    }

    public function teachersByClassANDSubject($params, $isJson = true)
    {

        $data = array();

        $classId = isset($params["classId"]) ? (int) $params["classId"] : 0;
        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : 0;

        $SQL = "";
        $SQL .= " SELECT A.ID AS TEACHER_ID";
        $SQL .= " ,CONCAT(A.LASTNAME,' ',A.FIRSTNAME) AS TEACHER_NAME";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.TUTOR IN (1,2)";
        $SQL .= " AND A.STATUS=1";
        $SQL .= " AND B.ACADEMIC = '" . $classId . "'";

        if ($subjectId)
            $SQL .= " AND B.SUBJECT = '" . $subjectId . "'";

        $SQL .= " GROUP BY B.TEACHER";
        //echo $SQL;
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["NAME"] = $value->TEACHER_NAME;
                $i++;
            }

        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
        if ($isJson)
            return $this->dataforjson;
        else
            return $data[0];
    }

    public function getSQLSubjectTeacherClass($subjectId, $teacherId, $classId, $gradeId, $term, $schoolyearId)
    {

        $SQL = "";
        $SQL .= " SELECT A.ID AS TEACHER_ID";
        $SQL .= " ,A.STAFF_SCHOOL_ID AS STAFF_SCHOOL_ID";
        $SQL .= " ,A.CODE AS TEACHER_CODE";
        $SQL .= " ,A.ID AS ID_TEACHER";
        if (!SchoolDBAccess::displayPersonNameInGrid())
        {
            $SQL .= " ,CONCAT(A.LASTNAME,' ',A.FIRSTNAME) AS TEACHER_NAME";
        }
        else
        {
            $SQL .= " ,CONCAT(A.FIRSTNAME,' ',A.LASTNAME) AS TEACHER_NAME";
        }

        if (!SchoolDBAccess::displayPersonNameInGrid())
        {
            $SQL .= " ,CONCAT(A.LASTNAME,' ',A.FIRSTNAME) AS FULL_NAME"; //@soda
        }
        else
        {
            $SQL .= " ,CONCAT(A.FIRSTNAME,' ',A.LASTNAME) AS FULL_NAME"; //@soda
        }
        $SQL .= " ,A.LASTNAME AS LASTNAME";
        $SQL .= " ,A.FIRSTNAME AS FIRSTNAME";
        $SQL .= " ,A.FIRSTNAME_LATIN AS FIRSTNAME_LATIN";
        $SQL .= " ,A.LASTNAME_LATIN AS LASTNAME_LATIN";
        $SQL .= " ,A.GENDER AS GENDER";
        $SQL .= " ,A.DATE_BIRTH AS DATE_BIRTH";
        $SQL .= " ,A.PHONE AS PHONE";
        $SQL .= " ,A.EMAIL AS EMAIL";
        $SQL .= " ,B.ACADEMIC_ID AS CLASS_ID ";
        $SQL .= " ,B.SUBJECT_ID AS SUBJECT_ID ";
        $SQL .= " ,B.TERM AS TERM ";
        $SQL .= " ,C.NAME AS SUBJECT_NAME ";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_schedule AS B ON A.ID=B.TEACHER_ID";
        $SQL .= " LEFT JOIN t_subject AS C ON C.ID=B.SUBJECT_ID";
        $SQL .= " WHERE 1=1";

        if ($term != "ALL")
        {
            $SQL .= " AND B.TERM= '" . $term . "'";
        }

        if ($teacherId)
            $SQL .= " AND B.TEACHER_ID = '" . $teacherId . "'";

        if ($subjectId)
            $SQL .= " AND B.SUBJECT_ID= '" . $subjectId . "'";

        if ($classId)
            $SQL .= " AND B.ACADEMIC_ID IN (" . $classId . ")";

        //echo $SQL;
        //error_log($SQL);
        $SQL .= " GROUP BY B.SUBJECT_ID";
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function checkTeacherANADSubject($teacherId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("SCHOOLYEAR = '" . $schoolyearId . "'");
        $SQL->where("TEACHER = '" . $teacherId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonAssignedTeachersByClass($params, $isJson = true)
    {

        $data = array();
        $term = isset($params["gradingterm"]) ? $params["gradingterm"] : "";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $classIds = isset($params["classIds"]) ? addText($params["classIds"]) : "";
        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        if ($academicObject)
        {

            switch ($academicObject->OBJECT_TYPE)
            {
                case "GRADE":
                    $gradeId = $academicObject->ID;
                    $classId = "";
                    break;
                case "CLASS":
                    $classId = $academicObject->ID;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
            }
            $result = $this->getSQLSubjectTeacherClass(
                    $subjectId
                    , false
                    , $classId
                    , $gradeId
                    , $term
                    , $schoolyearId
            );
        }
        elseif ($classIds)
        {
            $result = $this->getSQLSubjectTeacherClass(
                    $subjectId
                    , false
                    , $classIds
                    , false
                    , $term
                    , $schoolyearId
            );
        }

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {
                if ($value->TEACHER_ID == UserAuth::userId())
                {  //@veasna
                    continue;
                } ///

                $COUNT_SUBJECT = $this->lessonCountByTeacher(
                        $value->TEACHER_ID
                        , $value->CLASS_ID
                        , $value->SUBJECT_ID
                        , $term
                );

                if ($value->TEACHER_ID && $value->SUBJECT_NAME)
                {
                    $data[$i]["ID"] = $value->TEACHER_ID;
                    $data[$i]["SUBJECT_ID"] = $value->SUBJECT_ID;
                    $data[$i]["SUBJECT_NAME"] = $value->SUBJECT_NAME;
                    $data[$i]["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                    $data[$i]["TEACHER_CODE"] = $value->TEACHER_CODE;
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    $data[$i]["TEACHER_NAME"] = $value->TEACHER_NAME . " (" . $value->TEACHER_CODE . ")";
                    
                    $data[$i]["FULL_NAME"] = $value->FULL_NAME;
                    $data[$i]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                    $data[$i]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                    $data[$i]["GENDER"] = $value->GENDER;
                    $data[$i]["DATE_BIRTH"] = $value->DATE_BIRTH;
                    $data[$i]["SUBJECT"] = $value->SUBJECT_NAME . " (" . $COUNT_SUBJECT . ")";

                    switch ($term)
                    {
                        case "ALL":
                            $data[$i]["TERM"] = "---";
                            break;
                        default:
                            $data[$i]["TERM"] = constant($value->TERM);
                            break;
                    }

                    $data[$i]["PHONE"] = $value->PHONE;
                    $data[$i]["EMAIL"] = $value->EMAIL;

                    $i++;
                }
            }

        $this->dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
        if ($isJson == true)
            return $this->dataforjson;
        else
            return $data;
    }

    public function gradingtermByTeacher($Id, $classId, $subjectId, $gradingterm)
    {

        $SQL = "SELECT GRADINGTERM";
        $SQL .= " FROM t_schedule";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND TEACHER_ID = '" . $Id . "'";
        $SQL .= " AND CLASS_ID = '" . $classId . "'";
        $SQL .= " AND TERM = '" . $gradingterm . "'";
        $SQL .= " AND SUBJECT_ID = '" . $subjectId . "'";

        //echo $SQL;
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? displaySchoolTerm($result->GRADINGTERM) : "---";
    }

    public function lessonCountByTeacher($Id, $classId, $subjectId, $gradingterm)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER_ID = '" . $Id . "'");
        $SQL->where("ACADEMIC_ID = '" . $classId . "'");
        $SQL->where("SUBJECT_ID = '" . $subjectId . "'");
        if ($gradingterm != "ALL")
        {
            $SQL->where("TERM = '" . $gradingterm . "'");
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function getSubstittue($teacherId, $classId, $subjectId, $term)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("*"));
        $SQL->where("TEACHER_ID = '" . $teacherId . "'");
        $SQL->where("ACADEMIC_ID = '" . $classId . "'");
        $SQL->where("TERM = '" . $term . "'");
        $SQL->where("SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("SUBSTITUTE_ID<>");
        $result = self::dbAccess()->fetchRow($SQL);

        $show = "";
        if (isset($result->SUBSTITUTE_ID))
        {

            $show = $result->SUBSTITUTE_NAME . " (" . $result->SUBSTITUTE_CODE . ")";
            $show .= "<br>" . getShowDate($result->SUBSTITUTE_START) . " - " . getShowDate($result->SUBSTITUTE_END);
        }

        return $show;
    }

    public function totalLessonCountByTeacher($Id, $schoolyearId, $term = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER_ID = '" . $Id . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $schoolyearId . "'");
        if ($term)
            $SQL->where("TERM = '" . $term . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function subjectsByTeacher($Id, $subjectName = false)
    {

        $SQL = "SELECT A.SUBJECT AS SUBJECT, B.NAME AS SUBJECT_NAME";
        $SQL .= " FROM t_teacher_subject AS A";
        $SQL .= " LEFT JOIN t_subject AS B ON B.ID=A.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.TEACHER = '" . $Id . "'";
        $SQL .= " GROUP BY A.SUBJECT";

        $result = self::dbAccess()->fetchAll($SQL);

        $withID = array();
        $withSUBJECT_NAME = array();
        if ($result)
            foreach ($result as $value)
            {

                $withID[$value->SUBJECT] = $value->SUBJECT;
                $withSUBJECT_NAME[$value->SUBJECT] = $value->SUBJECT_NAME;
            }
        if ($subjectName)
        {
            return implode("<br>", $withSUBJECT_NAME);
        }
        else
        {
            return implode(",", $withID);
        }
    }

    public static function checkSubjectByTeacher($teacherId, $subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_teacher_subject", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER = '" . $teacherId . "'");
        $SQL->where("SUBJECT = '" . $subjectId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function addTeacherSubject($teacherId, $subjectId)
    {

        $SQL = "
		INSERT INTO t_teacher_subject
		SET 
		TEACHER = '" . $teacherId . "'
		,SUBJECT = '" . $subjectId . "'
		";
        self::dbAccess()->query($SQL);
    }

    protected function removeSubjectFromTeacher($teacherId)
    {

        self::dbAccess()->delete('t_teacher_subject', array("TEACHER = '" . $teacherId . "'"));
    }

    protected function subjectsTeaching($teacherId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("*"));
        $SQL->where("TEACHER = '" . $teacherId . "'");
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function checkStaffSchoolId($staffId, $staff_school_id)
    {

        $STAFF_OBJECT = self::findStaffFromId($staffId);

        if ($STAFF_OBJECT)
        {
            if ($STAFF_OBJECT->STAFF_SCHOOL_ID == $staff_school_id)
            {
                return false;
            }
            else
            {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_staff", array("C" => "COUNT(*)"));
                $SQL->where("STAFF_SCHOOL_ID = '" . $staff_school_id . "'");
                $result = self::dbAccess()->fetchRow($SQL);
                if ($result)
                {
                    if ($result->C)
                    {
                        return true;
                    }
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_staff", array("C" => "COUNT(*)"));
            $SQL->where("STAFF_SCHOOL_ID = '" . $staff_school_id . "'");
            $result = self::dbAccess()->fetchRow($SQL);

            if ($result)
            {
                if ($result->C)
                {
                    return true;
                }
            }
            else
            {
                return false;
            }
        }
    }

    public function checkStaffAssignment($Id)
    {

        $check1 = $this->checkStaffInSubjectTeacherClass($Id);
        $check2 = $this->checkStaffInSchedule($Id);
        $check3 = $this->checkStaffInGrade($Id, false);

        if ($check1 || $check2 || $check3)
        {

            return true;
        }
        else
        {
            return false;
        }
    }

    protected static function checkTeacherInClass($teacherId, $classId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        if ($teacherId)
            $SQL->where("TEACHER = '" . $teacherId . "'");
        if ($classId)
            $SQL->where("ACADEMIC = '" . $classId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    protected function checkTeacherBySubjectSchoolyear($teacherId, $subjectId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        if ($teacherId)
            $SQL->where("TEACHER = '" . $teacherId . "'");
        if ($subjectId)
            $SQL->where("SUBJECT = '" . $subjectId . "'");
        if ($schoolyearId)
            $SQL->where("SCHOOLYEAR = '" . $schoolyearId . "'");
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonCheckStaffSchoolID($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $staffSchoolId = isset($params["staffSchoolId"]) ? $params["staffSchoolId"] : 0;

        $check = $this->checkStaffSchoolId($objectId, $staffSchoolId);

        if ($check)
        {
            $o = array("success" => false, "status" => false, "errors" => setICONV(SCHOOL_ID_EXISTS));
        }
        else
        {
            $o = array("success" => true, "status" => true);
        }

        return $o;
    }

    public static function findCampusByStaff($staffId, $Id, $type)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_campus", array("C" => "COUNT(*)"));
        $SQL->where("STAFF = '" . $staffId . "'");

        switch ($type)
        {
            case "CAMPUS":
                $SQL->where("CAMPUS = '" . $Id . "'");
                break;
            case "PROGRAM":
                $SQL->where("PROGRAM = '" . $Id . "'");
                break;
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonStaffCampus($params)
    {

        $data = array();

        $staffId = isset($params["staffId"]) ? $params["staffId"] : "0";
        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'CAMPUS'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CAMPUS"] = getEducationType($value->EDUCATION_TYPE);
                $data[$i]["ASSIGNED"] = self::findCampusByStaff($staffId, $value->ID, "CAMPUS");
                $i++;
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

    public function actionsStaffCampus($params)
    {

        $CAMPUS_DATA = array();

        $staffId = isset($params["staffId"]) ? $params["staffId"] : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";

        $Id = isset($params["id"]) ? addText($params["id"]) : "";

        $campusId = "";
        $programId = "";

        switch (strtoupper($target))
        {
            case "CAMPUS":
                $check = self::findCampusByStaff($staffId, $Id, "CAMPUS");
                $campusId = $Id;
                break;
            case "PROGRAM":
                $check = self::findCampusByStaff($staffId, $Id, "PROGRAM");
                $programId = $Id;
                break;
        }

        if ($params["newValue"] == 1)
        {
            if (!$check)
            {
                $CAMPUS_DATA['STAFF'] = $staffId;
                if ($campusId)
                    $CAMPUS_DATA['CAMPUS'] = $campusId;
                if ($programId)
                    $CAMPUS_DATA['PROGRAM'] = $programId;
                $this->insert(Zend_Registry::get('T_STAFF_CAMPUS'), $CAMPUS_DATA);
            }
        }
        if ($params["newValue"] == 0)
        {
            switch (strtoupper($target))
            {
                case "CAMPUS":
                    $condi = array("STAFF = '" . $staffId . "'", "CAMPUS = '" . $Id . "'");
                    break;
                case "PROGRAM":
                    $condi = array("STAFF = '" . $staffId . "'", "PROGRAM = '" . $Id . "'");
                    break;
            }

            self::dbAccess()->delete('t_staff_campus', $condi);
        }

        return array("success" => true);
    }

    public function getTutorByRoleId($Id)
    {
        $SQL = "SELECT TUTOR";
        $SQL .= " FROM t_memberrole";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND ID = '" . $Id . "'";
        //echo $SQL;
        $result = self::dbAccess()->fetchRow($SQL);
        if ($result)
        {
            return $result->TUTOR;
        }
        else
        {
            return 0;
        }
    }

    public function getUserRoleByStaffId($Id)
    {

        $SQL = "SELECT B.ID AS ID";
        $SQL .= " FROM t_members AS A";
        $SQL .= " LEFT JOIN t_memberrole AS B ON B.ID=A.ROLE";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.ID = '" . $Id . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->ID : '---';
    }

    public function treeAllTutors($params)
    {

        if (isset($params["campusId"]))
            $_params["campusId"] = $params["campusId"];
        if (isset($params["classId"]))
            $_params["classId"] = $params["classId"];

        $result = $this->queryAllStaffs($_params, "A.ID", false);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                if ($value->CODE)
                {

                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = $value->CODE . " " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    $data[$i]['cls'] = "nodeText";
                    $data[$i]['iconCls'] = "icon-user";
                    $data[$i]['leaf'] = true;
                    $i++;
                }
            }

        return $data;
    }

    public function jsonAllInstructors($params)
    {

        $data = array();

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $classId = $params["classId"] ? (int) $params["classId"] : "0";

        $facette = AcademicDBAccess::findGradeFromId($classId);

        /////////////////Neue Methoden finden, wie noch besser machen kann....
        $SQL = "";
        $SQL .= " 
		SELECT 
		A.ID AS TEACHER_ID
		, A.CODE AS CODE
		, A.FIRSTNAME AS FIRSTNAME
		, A.LASTNAME AS LASTNAME
		";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_members AS B ON A.ID=B.ID";
        $SQL .= " WHERE B.ROLE ='2'";
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $INSTRUCTOR_CLASS = $this->gettStaffInGrade($value->TEACHER_ID, $facette->SCHOOL_YEAR);

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["CODE"] = $value->CODE;

                if ($INSTRUCTOR_CLASS)
                {
                    $data[$i]["IS_INCLASS"] = 0;
                    $data[$i]["STATUS_ICON"] = iconTeacherInClass(0);
                    $data[$i]["INSTRUCTOR"] = $INSTRUCTOR_CLASS->NAME;
                    $data[$i]["EDUCATION_TYPE"] = getEducationType($INSTRUCTOR_CLASS->EDUCATION_TYPE);
                }
                else
                {
                    $data[$i]["IS_INCLASS"] = 1;
                    $data[$i]["STATUS_ICON"] = iconTeacherInClass(1);
                    $data[$i]["INSTRUCTOR"] = "---";
                    $data[$i]["EDUCATION_TYPE"] = "---";
                }

                $data[$i]["STAFF"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);

                $i++;
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

    public function setOrganization($organizationId)
    {
        $facette = CamemisTypeDBAccess::findObjectFromId($organizationId);
        return $facette ? $facette->NAME : "---";
    }

    public function setReligion($religionId)
    {

        $facette = CamemisTypeDBAccess::findObjectFromId($religionId);
        return $facette ? $facette->NAME : "---";
    }

    public function setEthnic($ethnicId)
    {

        $facette = CamemisTypeDBAccess::findObjectFromId($ethnicId);
        return $facette ? $facette->NAME : "---";
    }

    public function setNationality($nationalityId)
    {

        $facette = CamemisTypeDBAccess::findObjectFromId($nationalityId);
        return $facette ? $facette->NAME : "---";
    }

    public function jsonActionTeacherChange($params)
    {

        $SAVEDATA_A = array();
        $WHERE_A = array();
        $SAVEDATA_A["TEACHER"] = $params["newTeacherId"];
        $WHERE_A[] = self::dbAccess()->quoteInto('GRADINGTERM = ?', $params["TERM"]);
        $WHERE_A[] = self::dbAccess()->quoteInto('SUBJECT = ?', $params["subjectId"]);
        $WHERE_A[] = self::dbAccess()->quoteInto('ACADEMIC = ?', $params["classId"]);
        $WHERE_A[] = self::dbAccess()->quoteInto('TEACHER = ?', $params["oldTeacherId"]);
        $WHERE_A[] = self::dbAccess()->quoteInto('SCHOOLYEAR = ?', $params["schoolyearId"]);
        self::dbAccess()->update('t_subject_teacher_class', $SAVEDATA_A, $WHERE_A);

        $SAVEDATA_B = array();
        $WHERE_B = array();
        $SAVEDATA_B["TEACHER_ID"] = $params["newTeacherId"];
        $WHERE_B[] = self::dbAccess()->quoteInto('SUBJECT_ID = ?', $params["subjectId"]);
        $WHERE_B[] = self::dbAccess()->quoteInto('ACADEMIC_ID = ?', $params["classId"]);
        $WHERE_B[] = self::dbAccess()->quoteInto('TEACHER_ID = ?', $params["oldTeacherId"]);
        $WHERE_B[] = self::dbAccess()->quoteInto('SCHOOLYEAR_ID = ?', $params["schoolyearId"]);
        $WHERE_B[] = self::dbAccess()->quoteInto('TERM = ?', $params["TERM"]);
        self::dbAccess()->update('t_schedule', $SAVEDATA_B, $WHERE_B);

        return array("success" => true);
    }

    public function checkTeacherInTeaching()
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function comboAllTutor()
    {

        $SQL = "";
        $SQL .= " SELECT A.ID, CONCAT('(',A.CODE,') ',A.LASTNAME, ' ', A.FIRSTNAME) AS NAME";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " LEFT JOIN t_schedule AS C ON A.ID=C.TEACHER_ID";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.STATUS = 1";
        $SQL .= " GROUP BY B.TEACHER";
        $SQL .= " ORDER BY A.FIRSTNAME";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function setMappingCode_LoginName($Id, $code)
    {
        $SAVEDATA = array();
        $SAVEDATA['LOGINNAME'] = $code;
        $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
        self::dbAccess()->update('t_members', $SAVEDATA, $WHERE);
    }

    public function findClassInstructorByTeacherId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade');
        $SQL->where("INSTRUCTOR = '" . $Id . "'");
        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public static function findTeacherInEducationTypes($Id)
    {

        $SQL = "";
        $SQL .= " SELECT A.EDUCATION_TYPE AS EDUCATION_TYPE";
        $SQL .= " FROM t_grade AS A";
        $SQL .= " LEFT JOIN t_staff_campus AS B ON A.ID=B.CAMPUS";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.STAFF = '" . $Id . "'";

        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
        {
            foreach ($result as $value)
            {
                $data[$value->EDUCATION_TYPE] = $value->EDUCATION_TYPE;
            }
        }
        return $data;
    }

    ///////////////////////////////////////////////////////
    //Begin of Skill....
    ///////////////////////////////////////////////////////
    public static function actionStaffSkill($params)
    {
        $staffId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";

        $SAVEDATA = array();
        if ($objectId)
        {
            switch ($field)
            {
                case "DELETE":
                    self::dbAccess()->delete("t_staff_skill", "ID='" . $objectId . "'");
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = $newValue;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_staff_skill", $SAVEDATA, $WHERE);
                    break;
            }
        }
        else
        {
            $SAVEDATA["" . $field . ""] = $newValue;
            $SAVEDATA["STAFF_ID"] = $staffId;
            self::dbAccess()->insert('t_staff_skill', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_staff_skill WHERE ID='" . $objectId . "'");

        switch ($field)
        {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["NAME"] = $facette->NAME;
                $SUCCESS_DATA["DESCRIPTION"] = $facette->DESCRIPTION;
                $SUCCESS_DATA["SKILL_YEAR"] = $facette->SKILL_YEAR;
                break;
        }
        return $SUCCESS_DATA;
    }

    public static function jsonStaffSkill($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select()
                ->from("t_staff_skill", array('*'))
                ->where("STAFF_ID = '" . $studentId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result)
        {

            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["SKILL_YEAR"] = setShowText($value->SKILL_YEAR);

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    //End of Staff Skill....
    ///////////////////////////////////////////////////////
    //Experience....
    ///////////////////////////////////////////////////////
    public static function actionStaffExperience($params)
    {
        $staffId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";

        $SAVEDATA = array();
        if ($objectId)
        {
            switch ($field)
            {
                case "DELETE":
                    self::dbAccess()->delete("t_staff_experience", "ID='" . $objectId . "'");
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = $newValue;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_staff_experience", $SAVEDATA, $WHERE);
                    break;
            }
        }
        else
        {
            $SAVEDATA["" . $field . ""] = $newValue;
            $SAVEDATA["STAFF_ID"] = $staffId;
            self::dbAccess()->insert('t_staff_experience', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_staff_experience WHERE ID='" . $objectId . "'");

        switch ($field)
        {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["NAME"] = $facette->NAME;
                $SUCCESS_DATA["DESCRIPTION"] = $facette->DESCRIPTION;
                $SUCCESS_DATA["EXPERIENCE_START"] = $facette->EXPERIENCE_START;
                $SUCCESS_DATA["EXPERIENCE_END"] = $facette->EXPERIENCE_END;
                break;
        }
        return $SUCCESS_DATA;
    }

    public static function jsonStaffExperience($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select()
                ->from("t_staff_experience", array('*'))
                ->where("STAFF_ID = '" . $studentId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result)
        {

            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["EXPERIENCE_START"] = setShowText($value->EXPERIENCE_START);
                $data[$i]["EXPERIENCE_END"] = setShowText($value->EXPERIENCE_END);
                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    //End of Staff Experience....
    ///////////////////////////////////////////////////////
    //Begin of Staff Qualification...
    ///////////////////////////////////////////////////////
    public static function actionStaffQualification($params)
    {
        $staffId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";

        $SAVEDATA = array();
        if ($objectId)
        {
            switch ($field)
            {
                case "DELETE":
                    self::dbAccess()->delete("t_staff_qualification", "ID='" . $objectId . "'");
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = $newValue;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_staff_qualification", $SAVEDATA, $WHERE);
                    break;
            }
        }
        else
        {
            $SAVEDATA["" . $field . ""] = $newValue;
            $SAVEDATA["STAFF_ID"] = $staffId;
            self::dbAccess()->insert('t_staff_qualification', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_staff_qualification WHERE ID='" . $objectId . "'");

        switch ($field)
        {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["NAME"] = $facette->NAME;
                $SUCCESS_DATA["DESCRIPTION"] = $facette->DESCRIPTION;
                $SUCCESS_DATA["QUALIFICATION_YEAR"] = $facette->QUALIFICATION_YEAR;
                break;
        }
        return $SUCCESS_DATA;
    }

    public static function jsonStaffQualification($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select()
                ->from("t_staff_qualification", array('*'))
                ->where("STAFF_ID = '" . $studentId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result)
        {

            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = setShowText($value->NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["QUALIFICATION_YEAR"] = setShowText($value->QUALIFICATION_YEAR);

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    //End of Staff Qualification...
    public static function actionStaffDescription($params)
    {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_personal_description'), array('ID', 'PARENT'));
        $SQL->joinLeft(array('B' => 't_personal_description'), 'A.PARENT=B.ID', array('CHOOSE_TYPE'));
        $SQL->where("B.PERSON='STAFF'");
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());

        self::dbAccess()->delete('t_person_description_item', array("PERSON_ID='" . $objectId . "'"));

        if ($result && $objectId)
        {
            foreach ($result as $value)
            {

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->PARENT . ""]) ? addText($params["RADIOBOX_" . $value->PARENT . ""]) : "";
                $INPUTFIELD = isset($params["INPUTFIELD_" . $value->ID . ""]) ? addText($params["INPUTFIELD_" . $value->ID . ""]) : "";
                $TEXTAREA = isset($params["TEXTAREA_" . $value->ID . ""]) ? addText($params["TEXTAREA_" . $value->ID . ""]) : "";

                switch ($value->CHOOSE_TYPE)
                {
                    case 1:
                        if ($CHECKBOX == "on")
                        {
                            $SAVEDATA['ITEM'] = $value->ID;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            if (!self::checkUseDescriptionItem($objectId, $value->ID))
                                self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                    case 2:
                        if ($RADIOBOX)
                        {
                            $SAVEDATA['ITEM'] = $RADIOBOX;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            if (!self::checkUseDescriptionItem($objectId, $RADIOBOX))
                                self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                    case 3:
                        if ($INPUTFIELD)
                        {
                            $SAVEDATA['ITEM'] = $value->ID;
                            $SAVEDATA['PERSON_ID'] = $objectId;
                            $SAVEDATA['DESCRIPTION'] = $INPUTFIELD;
                            self::dbAccess()->insert('t_person_description_item', $SAVEDATA);
                        }
                        break;
                    case 4:
                        if ($TEXTAREA)
                        {
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

    public function loadStaffDescripton($Id)
    {

        $facette = self::findStaffFromId($Id);
        $data = array();

        if ($facette)
        {

            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_person_description_item'), array('ITEM', 'DESCRIPTION'));
            $SQL->joinLeft(array('B' => 't_personal_description'), 'A.ITEM=B.ID', array('PARENT'));
            $SQL->joinLeft(array('C' => 't_personal_description'), 'B.PARENT=C.ID', array('CHOOSE_TYPE'));
            $SQL->where("A.PERSON_ID='" . $facette->ID . "'");
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

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function jsonStaffTrainingPrograms($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array('*'));
        $SQL->where("OBJECT_TYPE='PROGRAM'");
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["PROGRAM"] = setShowText($value->NAME);
                $data[$i]["ASSIGNED"] = self::findCampusByStaff($Id, $value->ID, "PROGRAM");
                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function actionStaffProfil($params)
    {

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_staff";
        $SQL .= " SET";
        switch ($params["field"])
        {
            case "DATE_BIRTH":
                $date = str_replace("/", ".", $params["newValue"]);
                if ($date)
                {
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
        //error_log($SQL);
        self::dbAccess()->query($SQL);
        return array("success" => true);
    }

    public static function getClassesByTutor($teacherId, $schoolyearId)
    {

        $FIRST_DATA = array();

        $SQL1 = "";
        $SQL1 .= "
		SELECT 
		B.ID AS CLASS_ID
		,B.NAME AS CLASS_NAME";
        $SQL1 .= " FROM t_instructor AS A";
        $SQL1 .= " LEFT JOIN t_grade AS B ON B.ID=A.CLASS";
        $SQL1 .= " LEFT JOIN t_grade AS C ON C.ID=B.GRADE_ID";
        $SQL1 .= " LEFT JOIN t_academicdate AS D ON D.ID=B.SCHOOL_YEAR";
        $SQL1 .= " WHERE 1=1";
        $SQL1 .= " AND A.TEACHER ='" . $teacherId . "'";
        $SQL1 .= " AND B.SCHOOL_YEAR ='" . $schoolyearId . "'";
        $SQL1 .= " GROUP BY A.CLASS ORDER BY B.SORTKEY ASC";
        //error_log($SQL1);
        $result1 = self::dbAccess()->fetchAll($SQL1);

        if ($result1)
        {
            foreach ($result1 as $value)
            {
                $FIRST_DATA[$value->CLASS_ID] = $value->CLASS_ID;
            }
        }

        $SECOND_DATA = array();

        $SQL2 = "";
        $SQL2 .= "
		SELECT C.ID AS CLASS_ID
		,C.NAME AS CLASS_NAME";
        $SQL2 .= " FROM t_subject_teacher_class AS A";
        $SQL2 .= " LEFT JOIN t_grade AS B ON B.ID=A.GRADE";
        $SQL2 .= " LEFT JOIN t_grade AS C ON C.ID=A.ACADEMIC";
        $SQL2 .= " LEFT JOIN t_academicdate AS D ON D.ID=A.SCHOOLYEAR";
        $SQL2 .= " WHERE 1=1";
        $SQL2 .= " AND A.TEACHER ='" . $teacherId . "'";
        $SQL2 .= " AND A.SCHOOLYEAR ='" . $schoolyearId . "'";
        $SQL2 .= " GROUP BY A.CLASS ORDER BY B.SORTKEY ASC";
        $result2 = self::dbAccess()->fetchAll($SQL2);

        if ($result2)
        {
            foreach ($result2 as $value)
            {
                $SECOND_DATA[$value->CLASS_ID] = $value->CLASS_ID;
            }
        }

        $entries = array_unique($FIRST_DATA + $SECOND_DATA);

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE ";
        $SQL .= " ID IN (" . implode(",", $entries) . ")";
        $SQL .= " ORDER BY SORTKEY ASC";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getClassTeachingSession($teacherId, $schoolyearId)
    {

        $SQL = "";
        $SQL .= "SELECT C.ID AS CLASS_ID,C.NAME AS CLASS_NAME";
        $SQL .= " FROM t_subject_teacher_class AS A";
        $SQL .= " LEFT JOIN t_grade AS B ON B.ID=A.GRADE";
        $SQL .= " LEFT JOIN t_grade AS C ON C.ID=A.ACADEMIC";
        $SQL .= " LEFT JOIN t_academicdate AS D ON D.ID=A.SCHOOLYEAR";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.TEACHER ='" . $teacherId . "'";
        $SQL .= " AND A.SCHOOLYEAR ='" . $schoolyearId . "'";
        $SQL .= " GROUP BY A.ACADEMIC ORDER BY B.SORTKEY ASC";

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getSubjectTeacherClass($teacherId)
    {

        $SQL = "";
        $SQL .= "SELECT";
        $SQL .= " A.ACADEMIC AS ID";
        $SQL .= " FROM t_subject_teacher_class AS A";
        $SQL .= " LEFT JOIN t_academicdate AS B ON A.SCHOOLYEAR=B.ID";
        $SQL .= " WHERE ";
        $SQL .= " A.TEACHER='" . $teacherId . "'";
        $SQL .= " GROUP BY A.SUBJECT ORDER BY B.YEAR_LEVEL DESC";
        $SQL .= " LIMIT 0 , 1";
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function findLoginName($loginname)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_members", array('*'));
        $SQL->where("LOGINNAME='" . $loginname . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function checkUseDescriptionItem($Id, $item)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_person_description_item", array("C" => "COUNT(*)"));
        $SQL->where("PERSON_ID='" . $Id . "'");
        $SQL->where("ITEM='" . $item . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    //@Math Man 24.12.2013
    public static function findStaffLoginNameOrEmail($loginNameOrEmail)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_members", array("*"));
        $SQL->where("LOGINNAME='" . $loginNameOrEmail . "'");
        $SQL->orwhere("EMAIL='" . $loginNameOrEmail . "'");
        $SQL->limit(1);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
    }

    public static function resetNewPassword($params)
    {
        $DATA['PASSWORD'] = addText($params['PASSWORD']);
        $WHERE[] = "LOGINNAME = '" . addText($params['LOGINNAME']) . "'";
        self::dbAccess()->update('t_members', $DATA, $WHERE);
    }

    ///////////////////
    //SEAPENG 28.02.2014
    public static function treeAllStaffs($params)
    {

        $parent = $params["node"];
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $result = self::getAllStaffsQuery($params);

        $data = array();

        if ($parent == 0)
        {

            $i = 0;
            if ($result)
                foreach ($result as $i => $value)
                {

                    $data[$i]['id'] = $value->ID;
                    $data[$i]['cls'] = "nodeTextBoldBlue";
                    $data[$i]['text'] = stripslashes($value->NAME);

                    switch ($value->ID)
                    {
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                            $data[$i]['iconCls'] = "icon-user1_lock";
                            break;
                        default:
                            $data[$i]['iconCls'] = "icon-user1_monitor";
                            break;
                    }

                    $data[$i]['checked'] = self::checkFileStaff($objectId, $value->ID);
                    $i++;
                }
        }

        return $data;
    }

    public static function getAllStaffsQuery()
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('t_memberrole'));
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkFileStaff($objectId, $Id)
    {

        $userRoleObject = UserRoleDBAccess::findUserRoleFromId($Id);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_file_user", array("C" => "COUNT(*)"));
        $SQL->where("USER_ROLE_ID = '" . $userRoleObject->ID . "'");
        $SQL->where("FILE = '" . $objectId . "'");

        $result = self::dbAccess()->fetchRow($SQL);

        if ($result)
        {
            return $result->C ? true : false;
        }
        else
        {
            return false;
        }
    }

    //PERSON INFOS
    ////////////////////////////////////////////////////////////////////////////
    public static function actionPersonInfos($params)
    {

        $staffId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";
        $object = isset($params["object"]) ? addText($params["object"]) : "";

        $SAVEDATA = array();
        if ($objectId)
        {
            switch ($field)
            {
                case "DELETE":
                    self::dbAccess()->delete("t_person_infos", "ID='" . $objectId . "'");
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_person_infos", $SAVEDATA, $WHERE);
                    break;
            }
        }
        else
        {
            $SAVEDATA["" . $field . ""] = addText($newValue);
            $SAVEDATA["USER_ID"] = $staffId;
            $SAVEDATA["OBJECT_TYPE"] = $object;
            $SAVEDATA["USER_TYPE"] = "STAFF";
            self::dbAccess()->insert('t_person_infos', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_person_infos WHERE ID='" . $objectId . "'");

        switch ($field)
        {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["NAME"] = $facette->NAME;
                $SUCCESS_DATA["DESCRIPTION"] = $facette->DESCRIPTION;
                $SUCCESS_DATA["PHONE"] = $facette->PHONE;
                $SUCCESS_DATA["EMAIL"] = $facette->EMAIL;
                $SUCCESS_DATA["RELATIONSHIP"] = $facette->RELATIONSHIP;

                break;
        }
        return $SUCCESS_DATA;
    }

    public static function jsonListPersonInfos($params)
    {

        $staffId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = self::dbAccess()->select()
                ->from("t_person_infos", array('*'))
                ->where("USER_ID = '" . $staffId . "'");
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result)
        {

            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["OCCUPATION"] = $value->OCCUPATION;
                $data[$i]["ACADEMIC_YEAR"] = setShowText($value->ACADEMIC_YEAR);
                $data[$i]["START_DATE"] = setShowText($value->START_DATE);
                $data[$i]["END_DATE"] = setShowText($value->END_DATE);
                $data[$i]["INSTITUTION_NAME"] = setShowText($value->INSTITUTION_NAME);
                $data[$i]["MAJOR"] = setShowText($value->MAJOR);
                $data[$i]["QUALIFICATION_DEGREE"] = setShowText($value->QUALIFICATION_DEGREE);
                $data[$i]["CERTIFICATE_NUMBER"] = setShowText($value->CERTIFICATE_NUMBER);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["ADDRESS"] = setShowText($value->ADDRESS);
                $data[$i]["DATE_BIRTH"] = setShowText($value->DATE_BIRTH);
                $data[$i]["PUBLICATION_TITLE"] = setShowText($value->PUBLICATION_TITLE);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["COMPANY_NAME"] = setShowText($value->COMPANY_NAME);
                $data[$i]["COURSE"] = setShowText($value->COURSE);
                $data[$i]["POSITION"] = setShowText($value->POSITION);
                $data[$i]["START_SALARY"] = setShowText($value->START_SALARY);
                $data[$i]["END_SALARY"] = setShowText($value->END_SALARY);

                if ($value->COUNTRY <> 0)
                    $data[$i]["COUNTRY"] = CamemisTypeDBAccess::findObjectFromId($value->COUNTRY)->NAME;
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
                if ($value->ORGANIZATION_TYPE <> 0)
                    $data[$i]["ORGANIZATION_TYPE"] = CamemisTypeDBAccess::findObjectFromId($value->ORGANIZATION_TYPE)->NAME;

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }
    ////////////////////////////////////////////////////////////////////////////
}

?>
