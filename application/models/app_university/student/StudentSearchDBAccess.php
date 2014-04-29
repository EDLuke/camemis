<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StudentSearchDBAccess {

    public $datafield = array();

    public function __construct()
    {
        
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->datafield))
        {
            return $this->datafield[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->datafield[$name] = $value;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->datafield);
    }

    public function __unset($name)
    {
        unset($this->datafield[$name]);
    }

    public function searchStudents($params, $isJson = true)
    {
        $this->start = isset($params["start"]) ? $params["start"] : 0;
        $this->limit = isset($params["limit"]) ? $params["limit"] : 100;
        $this->displayCurrentAcademic = isset($params["displayCurrentAcademic"]) ? $params["displayCurrentAcademic"] : 1;
        $this->startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $this->endDate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $this->globalSearch = isset($params["query"]) ? $params["query"] : "";
        $this->firstname = isset($params["FIRSTNAME"]) ? $params["FIRSTNAME"] : "";
        $this->lastname = isset($params["LASTNAME"]) ? $params["LASTNAME"] : "";
        $this->gender = isset($params["GENDER"]) ? $params["GENDER"] : "";
        $this->email = isset($params["EMAIL"]) ? $params["EMAIL"] : "";
        $this->phone = isset($params["PHONE"]) ? $params["PHONE"] : "";
        $this->studentSchoolId = isset($params["STUDENT_SCHOOL_ID"]) ? $params["STUDENT_SCHOOL_ID"] : "";
        $this->code = isset($params["CODE"]) ? $params["CODE"] : "";
        $this->religion = isset($params["RELIGION"]) ? $params["RELIGION"] : "";
        $this->ethnic = isset($params["ETHNIC"]) ? $params["ETHNIC"] : "";
        $this->firstnameLatin = isset($params["FIRSTNAME_LATIN"]) ? $params["FIRSTNAME_LATIN"] : "";
        $this->lastnameLatin = isset($params["LASTNAME_LATIN"]) ? $params["LASTNAME_LATIN"] : "";
        $this->searchDescription = self::searchDescriptionItems($params);
        $this->campusId = isset($params["campusId"]) ? $params["campusId"] : "";
        $this->gradeId = isset($params["gradeId"]) ? $params["gradeId"] : "";
        $this->schoolyearId = isset($params["schoolyearId"]) ? $params["schoolyearId"] : "";
        $this->classId = isset($params["classId"]) ? $params["classId"] : "";
        $this->programId = isset($params["programId"]) ? $params["programId"] : "";
        $this->levelId = isset($params["levelId"]) ? $params["levelId"] : "";
        $this->termId = isset($params["termId"]) ? $params["termId"] : "";
        $this->trainingId = isset($params["trainingId"]) ? $params["trainingId"] : "";
        $this->filter = isset($params["filter"]) ? $params["filter"] : "";
        $this->isJson = $isJson;

        return $this->getGridData($this->queryAllStudents());
    }

    public function getSearchIn()
    {
        if ($this->campusId || $this->gradeId || $this->schoolyearId || $this->classId)
        {
            return "TRADITIONAL";
        }
        elseif ($this->creditCampusId || $this->creditSchoolyearId || $this->creditSubjectId)
        {
            return "CREDIT";
        }
        elseif ($this->programId || $this->levelId || $this->termId || $this->courseId)
        {
            return "COURSE";
        }
        else
        {
            return "STUDENT";
        }
    }

    public function queryAllStudents()
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('STUDENT' => 't_student'), "*");

        switch ($this->getSearchIn())
        {
            case "TRADITIONAL":
                $SQL->joinInner(array('TRADITIONAL' => 't_student_schoolyear'), 'TRADITIONAL.STUDENT=STUDENT.ID', array());
                $SQL->joinInner(array('CAMPUS' => 't_grade'), 'CAMPUS.ID=TRADITIONAL.CAMPUS', array("NAME AS CAMPUS_NAME"));
                $SQL->joinInner(array('GRADE' => 't_grade'), 'GRADE.ID=TRADITIONAL.GRADE', array("ID AS GRADE_ID", "NAME AS GRADE_NAME"));
                $SQL->joinInner(array('SCHOOLYEAR' => 't_academicdate'), 'SCHOOLYEAR.ID=TRADITIONAL.SCHOOL_YEAR', array("ID AS SCHOOLYEAR_ID", "NAME AS SCHOOLYEAR_NAME"));
                $SQL->joinInner(array('CLASS' => 't_grade'), 'CLASS.ID=TRADITIONAL.CLASS', array("NAME AS CLASS_NAME"));

                if ($this->campusId)
                    $SQL->where("TRADITIONAL.CAMPUS = '" . $this->campusId . "'");

                if ($this->gradeId)
                    $SQL->where("TRADITIONAL.GRADE = '" . $this->gradeId . "'");

                if ($this->schoolyearId)
                    $SQL->where("TRADITIONAL.SCHOOL_YEAR = '" . $this->schoolyearId . "'");

                if ($this->classId)
                    $SQL->where("TRADITIONAL.CLASS = '" . $this->classId . "'");

                break;
            case "CREDIT":
                $SQL->joinInner(array('CREDIT' => 't_student_schoolyear_subject'), 'CREDIT.STUDENT_ID=STUDENT.ID', array());
                $SQL->joinInner(array('CAMPUS' => 't_grade'), 'CAMPUS.ID=CREDIT.CAMPUS_ID', array("NAME AS CAMPUS_NAME"));
                $SQL->joinInner(array('SCHOOLYEAR' => 't_academicdate'), 'SCHOOLYEAR.ID=CREDIT.SCHOOLYEAR_ID', array("ID AS SCHOOLYEAR_ID", "NAME AS SCHOOLYEAR_NAME"));
                $SQL->joinInner(array('SUBJECT' => 't_subject'), 'SUBJECT.ID=CREDIT.SUBJECT_ID', array("NAME AS SUBJECT_NAME"));

                if ($this->creditCampusId)
                    $SQL->where("CREDIT.CAMPUS_ID = '" . $this->creditCampusId . "'");

                if ($this->creditSubjectId)
                    $SQL->where("CREDIT.SUBJECT_ID = '" . $this->creditSubjectId . "'");

                if ($this->creditSchoolyearId)
                    $SQL->where("CREDIT.SCHOOLYEAR_ID = '" . $this->creditSchoolyearId . "'");

                break;
            case "COURSE":
                $SQL->joinInner(array('COURSE' => 't_student_training'), array());
                $SQL->joinInner(array('TRAINING' => 't_training'), 'COURSE.TRAINING=TRAINING.ID', array("ID AS TRAINING_ID", "NAME AS TRAINING_NAME"));
                $SQL->joinInner(array('TERM' => 't_training'), 'TERM.ID=COURSE.TERM', array("START_DATE", "END_DATE"));
                $SQL->joinInner(array('LEVEL' => 't_training'), 'LEVEL.ID=COURSE.LEVEL', array("ID AS LEVEL_ID", "NAME AS LEVEL_NAME"));
                $SQL->joinInner(array('PROGRAM' => 't_training'), 'PROGRAM.ID=COURSE.PROGRAM', array("ID AS PROGRAM_ID", "NAME AS PROGRAM_NAME"));

                if ($this->programId)
                    $SQL->where("COURSE.PROGRAM = '" . $this->programId . "'");

                if ($this->levelId)
                    $SQL->where("COURSE.LEVEL = '" . $this->levelId . "'");

                if ($this->termId)
                    $SQL->where("COURSE.TERM = '" . $this->termId . "'");

                if ($this->trainingId)
                    $SQL->where("COURSE.TRAINING = '" . $this->trainingId . "'");
                break;
        }

        if ($this->searchDescription)
        {
            $SQL->joinLeft(array('PERSON_DESCRIPTION' => 't_person_description_item'), 'PERSON_DESCRIPTION.PERSON_ID=STUDENT.ID', array());
            $SQL->where("PERSON_DESCRIPTION.ITEM IN (" . $this->searchDescription . ")");
        }

        if ($this->firstname)
            $SQL->where('STUDENT.FIRSTNAME LIKE ?', "" . $this->firstname . "%");

        if ($this->lastname)
            $SQL->where('STUDENT.LASTNAME LIKE ?', "" . $this->lastname . "%");

        if ($this->firstnameLatin)
            $SQL->where('STUDENT.FIRSTNAME_LATIN LIKE ?', "" . $this->firstnameLatin . "%");

        if ($this->lastnameLatin)
            $SQL->where('STUDENT.LASTNAME_LATIN LIKE ?', "" . $this->lastnameLatin . "%");

        if ($this->email)
            $SQL->where('STUDENT.EMAIL LIKE ?', "" . $this->email . "%");

        if ($this->code)
            $SQL->where('STUDENT.CODE LIKE ?', "" . strtoupper($this->code) . "%");

        if ($this->studentSchoolId)
            $SQL->where('STUDENT.STUDENT_SCHOOL_ID LIKE ?', "" . strtoupper($this->studentSchoolId) . "%");

        if ($this->gender)
            $SQL->where("STUDENT.GENDER = '" . $this->gender . "'");

        if ($this->religion)
            $SQL->where("STUDENT.RELIGION = '" . $this->religion . "'");

        if ($this->ethnic)
            $SQL->where("STUDENT.ETHNIC = '" . $this->ethnic . "'");

        if ($this->startDate != "" && $this->endDate != "")
        {
            $SQL->where("(STUDENT.CREATED_DATE BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "') OR (STUDENT.CREATED_DATE BETWEEN '" . $this->startDate . "' AND '" . $this->endDate . "') ");
        }

        if ($this->globalSearch)
        {
            $SQL->where('STUDENT.NAME LIKE ?', "%" . $this->globalSearch . "%");
            $SQL->orWhere('STUDENT.FIRSTNAME LIKE ?', "%" . $this->globalSearch . "%");
            $SQL->orWhere('STUDENT.LASTNAME LIKE ?', "%" . $this->globalSearch . "%");
            $SQL->orWhere('STUDENT.FIRSTNAME_LATIN LIKE ?', "%" . $this->globalSearch . "%");
            $SQL->orWhere('STUDENT.LASTNAME_LATIN LIKE ?', "%" . $this->globalSearch . "%");
            $SQL->orWhere('STUDENT.CODE LIKE ?', "%" . strtoupper($this->globalSearch) . "%");
            $SQL->orWhere('STUDENT.STUDENT_SCHOOL_ID LIKE ?', "%" . strtoupper($this->globalSearch) . "%");
        }

        $SQL->group('STUDENT.ID');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function searchDescriptionItems($params)
    {
        $entries = DescriptionDBAccess::sqlDescription("ALL", "STUDENT", false);
        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();
        if ($entries)
        {
            foreach ($entries as $value)
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
        return $PERSON_DES_DATA ? implode(",", $PERSON_DES_DATA) : array();
    }

    public static function getFullName($firstname, $lastname)
    {
        if (!SchoolDBAccess::displayPersonNameInGrid())
        {
            return setShowText($lastname) . " " . setShowText($firstname);
        }
        else
        {
            return setShowText($firstname) . " " . setShowText($lastname);
        }
    }

    public function getGridData($entries)
    {
        $data = array();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["FULL_NAME"] = self::getFullName($value->FIRSTNAME, $value->LASTNAME);
                $data[$i]["STUDENT_SCHOOL_ID"] = setShowText($value->STUDENT_SCHOOL_ID);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["AGE"] = self::getAge($value->ID);
                $data[$i]["NATIONALITY"] = self::getCamemisType($value->NATIONALITY);
                $data[$i]["RELIGION"] = self::getCamemisType($value->RELIGION);
                $data[$i]["ETHNIC_GROUPS"] = self::getCamemisType($value->ETHNIC);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

                switch ($this->displayCurrentAcademic)
                {
                    case 1:
                        $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->ID);

                        $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                        $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                        $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                        $data[$i]["CURRENT_SCHOOLYEAR"] = self::getCurrentAcademic($value->ID)->CURRENT_SCHOOLYEAR;
                        $data[$i]["CURRENT_ACADEMIC"] = self::getCurrentAcademic($value->ID)->CURRENT_ACADEMIC;
                        $data[$i]["CURRENT_TRAINING_NAME"] = self::getCurrentTraining($value->ID);

                        break;
                    case 2:
                        $data[$i]["CURRENT_CAMPUS"] = $value->CAMPUS_NAME ? $value->CAMPUS_NAME : "---";
                        $data[$i]["CURRENT_GRADE"] = $value->GRADE_NAME ? $value->GRADE_NAME : "---";
                        $data[$i]["CURRENT_CLASS"] = $value->CLASS_NAME ? $value->CLASS_NAME : "---";
                        break;
                    case 3:
                        break;
                }

                $i++;
            }
        }

        $a = array();
        for ($i = $this->start; $i < $this->start + $this->limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($this->isJson)
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

    public static function getCurrentTraining($studentId)
    {

        $SELECT_A = array(
            'TRAINING AS TRAINING_ID'
        );

        $SELECT_B = array(
            'ID AS OBJECT_ID'
            , 'NAME AS TRAINING_NAME'
        );

        $SELECT_C = array(
            'START_DATE'
            , 'END_DATE'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_training'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_training'), 'A.TRAINING=B.ID', $SELECT_B);
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.TERM', $SELECT_C);
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=A.LEVEL', array());
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=A.PROGRAM', array());
        $SQL->where("A.STUDENT = '" . $studentId . "'");
        $SQL->where("DATEDIFF(C.END_DATE,'" . date('Y-m-d') . "') > 0");
        $entries = self::dbAccess()->fetchAll($SQL);

        $result = "---";
        if ($entries)
        {
            $o = array();
            $i = 0;
            foreach ($entries as $v)
            {
                $o[$i] = $v->TRAINING_NAME;
                $i++;
            }
            $result = setShowText(implode(" | ", $o));
        }

        return $result;
    }

    public static function getCamemisType($typeId)
    {
        $facette = CamemisTypeDBAccess::findObjectFromId($typeId);
        return $facette ? $facette->NAME : "---";
    }

    public static function getCurrentAcademic($studentId)
    {
        $data = array();
        $facette = StudentAcademicDBAccess::getCurrentStudentAcademic($studentId);
        $data["CURRENT_SCHOOLYEAR"] = $facette ? $facette->SCHOOLYEAR : "---";
        $data["CURRENT_ACADEMIC"] = $facette ? $facette->ACADEMIC : "---";
        return (object) $data;
    }

    public static function getAge($studentId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student", array("DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(DATE_BIRTH)), '%Y')+0 AS AGE"));
        $SQL->where("ID = '" . $studentId . "'");
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->AGE : "--";
    }

}

?>
