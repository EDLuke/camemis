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

    public function getImplodeCountTeacherSubjectScoreEnter()
    {

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_logininfo"), array("DATE(DATE) AS SECTION_DATE", "DATE"));
        $SQL->joinLeft(array('B' => 't_memberrole'), 'A.ROLE=B.ID', array());
        $SQL->group("DATE(A.DATE)");
        $SQL->order("DATE(A.DATE) ASC");
        //error_log($SQL->__toString());
        $entries = self::dbAccess()->fetchAll($SQL);
    }

    public function getDatasetTeacherSubjectScoreEnter()
    {

        $entries = $this->getCurrentClassAssignments();
        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $str = "{";
                $str .= "name: '(" . $value->SHORT . ") " . $value->NAME . "'";
                $str .= ",data: [3, 4, 3, 5, 4, 10, 12]";
                $str .= "}";
                $data[] = $str;
            }
        }

        return "[" . implode(",", $data) . "]";
    }

}

?>