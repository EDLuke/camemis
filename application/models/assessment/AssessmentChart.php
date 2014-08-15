<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/assignment/AssignmentDBAccess.php";
require_once 'models/assessment/AssessmentProperties.php';

class AssessmentChart extends AssessmentProperties {

    const SCORE_NUMBER = 1;

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return false::dbAccess()->select();
    }

    public function setAcademicId($value)
    {
        return $this->academicId = $value;
    }

    public function setSubjectId($value)
    {
        return $this->subjectId = $value;
    }

    public function setTerm($value)
    {
        return $this->term = $value;
    }

    public function getSubjectGradingScaleByClass()
    {

        $entries = AssessmentConfig::getListGradingScales(
                        self::SCORE_NUMBER
                        , $this->getSettingQualificationType());

        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = "['" . $value->DESCRIPTION . "', " . $value->ID . "]";
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    public function getSubjectAVGStudentList()
    {
        $entries = $this->listClassStudents();
        $data = array();
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $ii = $i + 1;
                $data[] = "['" . getFullName($value->FIRSTNAME, $value->LASTNAME) . "', " . $i . "]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    public function getSubjectPassStatus()
    {

        return "[{
            name: '" . MALE_STUDENTS . "',
            data: [10,5]

        }, {
            name: '" . FEMALE_STUDENTS . "',
            data: [2,5]

        }]";
    }

    public function getImplodeTeacherSubjectScoreDate()
    {
        $entries = AssignmentDBAccess::getAllScoreDate(false, $this->academicId, $this->subjectId);
        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = "" . strtotime($value->SCORE_INPUT_DATE) * 1000 . "";
            }
        }

        return implode(",", $data);
    }

    public function getCountTeacherSubjectScoreEnter($assignmentId, $date)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_student_assignment', 'COUNT(*) AS C');
        $SQL->where("ASSIGNMENT_ID = ?", "" . $assignmentId . "");
        $SQL->where("CLASS_ID = ?", "" . $this->academicId . "");
        $SQL->where("SUBJECT_ID = ?", "" . $this->subjectId . "");
        $SQL->where("SCORE_DATE = ?", "" . $date . "");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function getDataTeacherSubjectScoreEnter($assignmentId)
    {

        $entries = AssignmentDBAccess::getAllScoreDate(false, $this->academicId, $this->subjectId);
        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = $this->getCountTeacherSubjectScoreEnter($assignmentId, $value->SCORE_INPUT_DATE);
            }
        }

        return implode(",", $data);
    }

    public function getDatasetTeacherSubjectScoreEnter()
    {

        $entries = $this->getCurrentClassAssignments();
        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {

                #error_log("academicId: " . $this->academicId . " subjectId: " . $this->subjectId . " assignmentId: " . $value->ASSIGNMENT_ID);

                $str = "{";
                $str .= "name: '(" . $value->SHORT . ") " . $value->NAME . "'";
                $str .= ",data: [" . $this->getDataTeacherSubjectScoreEnter($value->ASSIGNMENT_ID) . "]";
                $str .= "}";
                $data[] = $str;
            }
        }

        return "[" . implode(",", $data) . "]";
    }

}

?>