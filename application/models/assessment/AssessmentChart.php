<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';
require_once 'models/assessment/SQLEvaluationStudentSubject.php';

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

    public function setSection($value)
    {
        return $this->section = $value;
    }

    public function setParams()
    {
        $object = new stdClass();
        $object->academicId = $this->academicId;
        $object->scoreType = self::SCORE_NUMBER;
        $object->subjectId = $this->subjectId;
        if ($this->term)
            $object->term = $this->term;
        $object->month = $this->getMonth();
        $object->year = $this->getYear();
        $object->section = $this->getSection();
        $object->schoolyearId = $this->getSchoolyearId();

        return $object;
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

    ////////////////////////////////////////////////////////////////////////////
    //  CLASS STUDENTS SUBJECT AVERAGE...
    ////////////////////////////////////////////////////////////////////////////
    public function getSubjectAVGStudentList()
    {
        $stdClass = $this->setParams();

        $data = array();
        $entries = $this->listClassStudents();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $stdClass->studentId = $value->ID;
                $data[] = "['" . getFullName($value->FIRSTNAME, $value->LASTNAME) . "', " . self::getStudentSubjectValue($stdClass) . "]";
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    //  END CLASS STUDENTS SUBJECT AVERAGE...
    ////////////////////////////////////////////////////////////////////////////

    public function getSubjectPassStatus()
    {

        $stdClass = $this->setParams();
        $entries = $this->listClassStudents();

        $MALE_FAL_DATA = array();
        $MALE_PASS_DATA = array();

        $FEMALE_FAL_DATA = array();
        $FEMALE_PASS_DATA = array();

        $UNKNOWN_FAL_DATA = array();
        $UNKNOWN_PASS_DATA = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $stdClass->studentId = $value->ID;
                $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, false);

                if ($facette)
                {
                    switch ($value->GENDER)
                    {
                        case 1:
                            if ($facette->IS_FAIL)
                            {
                                $MALE_FAL_DATA[] = 1;
                            }
                            else
                            {
                                $MALE_PASS_DATA[] = 1;
                            }
                            break;
                        case 2:
                            if ($facette->IS_FAIL)
                            {
                                $FEMALE_FAL_DATA[] = 1;
                            }
                            else
                            {
                                $FEMALE_PASS_DATA[] = 1;
                            }
                            break;
                        default:
                            if ($facette->IS_FAIL)
                            {
                                $UNKNOWN_FAL_DATA[] = 1;
                            }
                            else
                            {
                                $UNKNOWN_PASS_DATA[] = 1;
                            }
                            break;
                    }
                }
            }
        }

        $COUNT_MALE_FAIL = count($MALE_FAL_DATA);
        $COUNT_MALE_PASS = count($MALE_PASS_DATA);

        $COUNT_FEMALE_FAIL = count($FEMALE_FAL_DATA);
        $COUNT_FEMALE_PASS = count($FEMALE_PASS_DATA);

        $COUNT_UNKNOWN_FAIL = count($UNKNOWN_FAL_DATA);
        $COUNT_UNKNOWN_PASS = count($UNKNOWN_PASS_DATA);

        return "[{
            name: '" . MALE . "',
            data: [" . $COUNT_MALE_PASS . "," . $COUNT_MALE_FAIL . ", ]
        },{
            name: '" . FEMALE . "',
            data: [" . $COUNT_FEMALE_PASS . "," . $COUNT_FEMALE_FAIL . "]
        },{
            name: '" . UNKNOWN . "',
            data: [" . $COUNT_UNKNOWN_PASS . "," . $COUNT_UNKNOWN_FAIL . "]
        }]";
    }

    ////////////////////////////////////////////////////////////////////////////
    // TEACHER ENTER SCORE...
    ////////////////////////////////////////////////////////////////////////////
    public function getImplodeAssignments()
    {

        $entries = $this->getCurrentClassAssignments();

        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $data[] = "'" . addslashes($value->SHORT) . "'";
            }
        }

        return implode(",", $data);
    }

    public function getCountTeacherEnterScore($date)
    {

        $entries = $this->getCurrentClassAssignments();

        $data = array();
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_student_assignment", Array("C" => "COUNT(*)"));
                $SQL->where("SUBJECT_ID = ?", $this->subjectId);
                $SQL->where("CLASS_ID = '" . $this->academicId . "'");
                $SQL->where("SCORE_DATE = '" . $date . "'");
                $SQL->where("ASSIGNMENT_ID = '" . $value->ASSIGNMENT_ID . "'");
                $SQL->group('ASSIGNMENT_ID');
                //error_log($SQL->__toString());
                $facette = self::dbAccess()->fetchRow($SQL);
                $data[] = $facette ? $facette->C : 0;
            }
        }
        return implode(",", $data);
    }

    public function getDataSetTeacherEnterScore()
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", Array("*"));
        $SQL->where("SUBJECT_ID = ?", $this->subjectId);
        $SQL->where("CLASS_ID = '" . $this->academicId . "'");
        $SQL->group('SCORE_INPUT_DATE');
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
        {
            foreach ($result as $value)
            {
                $name = "{name:'" . getShowDate($value->SCORE_INPUT_DATE) . "'";
                $name .= ",data:[" . $this->getCountTeacherEnterScore($value->SCORE_INPUT_DATE) . "]}";
                $data[] = $name;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    ////////////////////////////////////////////////////////////////////////////
    // END TEACHER ENTER SCORE...
    ////////////////////////////////////////////////////////////////////////////

    public static function getStudentSubjectValue($stdClass)
    {
        $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, false);
        if ($facette)
        {
            return (is_numeric($facette->SUBJECT_VALUE)) ? $facette->SUBJECT_VALUE : 1;
        }
        else
        {
            return 0;
        }
    }

    public static function getStudentSubjectPassFailValue($stdClass)
    {
        $facette = SQLEvaluationStudentSubject::getCallStudentSubjectEvaluation($stdClass, false);
        return $facette ? $facette->IS_FAIL : 1;
    }

}

?>