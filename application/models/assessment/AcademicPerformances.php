<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLAcademicPerformances.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/SpecialDBAccess.php";

class AcademicPerformances extends AssessmentProperties {

    const NO_MONTH = false;
    const NO_YEAR = false;
    const NO_TERM = false;
    const NO_ASSIGNMENT = false;
    const NO_SECTION = false;
    const NO_SCHOOLYEAR_ID = false;
    const SCORE_NUMBER = 1;
    const SCORE_CHAR = 2;
    const INCLUDE_IN_MONTH = 1;
    const INCLUDE_IN_TERM = 2;
    const SCORE_TYPE_NUMBER = 1;
    const SCORE_TYPE_CHAR = 2;

    function __construct()
    {
        parent::__construct();
    }

    protected function listStudentsData()
    {

        $data = array();

        if ($this->listClassStudents())
        {
            $i = 0;
            foreach ($this->listClassStudents() as $value)
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

    public function getListStudentsMonthClassPerformance()
    {

        $data = array();

        $object = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "term" => $this->term
                    , "month" => $this->getMonth()
                    , "year" => $this->getYear()
        );

        $entries = SQLAcademicPerformances::getSQLListStudentsMonthClassPerformance($object);

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $data[$i]["ID"] = $value->ID;

                $i++;
            }
        }

        return $data;
    }

    public function getListStudentsTermClassPerformance()
    {

        $data = array();

        $object = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "term" => $this->term
        );

        $entries = SQLAcademicPerformances::getSQLListStudentsTermClassPerformance($object);

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $data[$i]["ID"] = $value->ID;

                $i++;
            }
        }

        return $data;
    }

    public function getListStudentsYearClassPerformance()
    {

        $data = array();

        $object = (object) array(
                    "studentId" => $this->studentId
                    , "academicId" => $this->academicId
                    , "term" => $this->term
        );

        $entries = SQLAcademicPerformances::getSQLListStudentsYearClassPerformance($object);

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $data[$i]["ID"] = $value->ID;

                $i++;
            }
        }

        return $data;
    }

}

?>