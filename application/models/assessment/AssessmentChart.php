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
    const SCORE_STRING = 2;
    const IS_PASS = 1;
    const IS_FAIL = 2;

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

    public function setMonthYear($value)
    {
        return $this->monthyear = $value;
    }

    public function setParams()
    {
        $object = new stdClass();
        $object->academicId = $this->academicId;
        $object->scoreType = $this->getSubjectScoreType();
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

        $stdClass = $this->setParams();
        $entries = AssessmentConfig::getListGradingScales(
                        $stdClass->scoreType
                        , $this->getSettingQualificationType());

        $data = array();

        $COUNT = 0;
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $SQL = self::dbAccess()->select();
                $SQL->from("t_student_subject_assessment", Array("C" => "COUNT(*)"));
                $SQL->where("SUBJECT_ID = ?", $this->subjectId);
                $SQL->where("CLASS_ID = '" . $this->academicId . "'");
                $SQL->where("ASSESSMENT_ID = '" . $value->ID . "'");
                switch ($stdClass->section)
                {
                    case "MONTH":
                        if (isset($stdClass->month))
                            $SQL->where("MONTH = '" . $stdClass->month . "'");

                        if (isset($stdClass->year))
                            $SQL->where("YEAR = '" . $stdClass->year . "'");
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        $SQL->where("TERM = '" . $stdClass->term . "'");
                        break;
                }
                $SQL->where("SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");
                $SQL->where("SECTION = '" . $stdClass->section . "'");
                $SQL->group("ASSESSMENT_ID");

                //error_log($SQL->__toString());
                $facette = self::dbAccess()->fetchRow($SQL);
                $COUNT = $facette ? $facette->C : 0;

                switch ($stdClass->scoreType)
                {
                    case self::SCORE_NUMBER:
                        $data[] = "['" . $value->DESCRIPTION . "', " . $COUNT . "]";
                        break;
                    case self::SCORE_STRING:
                        $data[] = "['" . $value->LETTER_GRADE . "', " . $COUNT . "]";
                        break;
                }
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
        $entries = $this->listClassStudents();

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

    public function getSubjectPassStatus($display)
    {

        $stdClass = $this->setParams();
        $entries = $this->listClassStudents();

        switch ($display)
        {
            case "WITH_GENDER":
                $COUNT_MALE_FAIL = 0;
                $COUNT_MALE_PASS = 0;
                $COUNT_FEMALE_FAIL = 0;
                $COUNT_FEMALE_PASS = 0;
                $COUNT_UNKNOWN_FAIL = 0;
                $COUNT_UNKNOWN_PASS = 0;

                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $stdClass->studentId = $value->ID;
                        $facette = SQLEvaluationStudentSubject::getPropertiesStudentSE($stdClass, false);
                        if ($facette)
                        {
                            switch ($value->GENDER)
                            {
                                case 1:
                                    $COUNT_MALE_FAIL += ($facette->IS_FAIL == self::IS_FAIL) ? 1 : 0;
                                    $COUNT_MALE_PASS += ($facette->IS_FAIL == self::IS_PASS) ? 1 : 0;
                                    break;
                                case 2:
                                    $COUNT_FEMALE_FAIL += ($facette->IS_FAIL == self::IS_FAIL) ? 1 : 0;
                                    $COUNT_FEMALE_PASS += ($facette->IS_FAIL == self::IS_PASS) ? 1 : 0;
                                    break;
                                default:
                                    $COUNT_UNKNOWN_FAIL += ($facette->IS_FAIL == self::IS_FAIL) ? 1 : 0;
                                    $COUNT_UNKNOWN_PASS += ($facette->IS_FAIL == self::IS_PASS) ? 1 : 0;
                                    break;
                            }
                        }
                    }
                }

                $OUTPUT = "";
                $OUTPUT .= "[{";
                $OUTPUT .= "name:'" . MALE . "',data:[" . $COUNT_MALE_PASS . "," . $COUNT_MALE_FAIL . ",]";
                $OUTPUT .= "},{";
                $OUTPUT .= "name:'" . FEMALE . "',data:[" . $COUNT_FEMALE_PASS . "," . $COUNT_FEMALE_FAIL . "]";
                $OUTPUT .= "},{";
                $OUTPUT .= "name:'" . UNKNOWN . "',data:[" . $COUNT_UNKNOWN_PASS . "," . $COUNT_UNKNOWN_FAIL . "]";
                $OUTPUT .= "}]";

                break;
            case "WITHOUT_GENDER":
                $COUNT_FAIL = 0;
                $COUNT_PASS = 0;
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $stdClass->studentId = $value->ID;
                        $facette = SQLEvaluationStudentSubject::getPropertiesStudentSE($stdClass, false);
                        if ($facette)
                        {
                            $COUNT_FAIL += ($facette->IS_FAIL == self::IS_FAIL) ? 1 : 0;
                            $COUNT_PASS += ($facette->IS_FAIL == self::IS_PASS) ? 1 : 0;
                        }
                    }

                    $OUTPUT = "[['" . PASS . "'," . $COUNT_PASS . "],['" . FAIL . "'," . $COUNT_FAIL . "]]";
                }
                break;
        }

        return $OUTPUT;
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
        $facette = SQLEvaluationStudentSubject::getPropertiesStudentSE($stdClass, false);
        if ($facette)
        {
            return (is_numeric($facette->SUBJECT_VALUE)) ? $facette->SUBJECT_VALUE : 1;
        }
        else
        {
            return 0;
        }
    }

}

?>