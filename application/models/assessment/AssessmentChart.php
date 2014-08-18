<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/AssessmentProperties.php';

class AssessmentChart extends AssessmentProperties {

    const SCORE_NUMBER = 1;

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return false::dbAccess()->select();
    }

    public function setAcademicId($value) {
        return $this->academicId = $value;
    }

    public function setSubjectId($value) {
        return $this->subjectId = $value;
    }

    public function setTerm($value) {
        return $this->term = $value;
    }

    public function getSubjectGradingScaleByClass() {

        $entries = AssessmentConfig::getListGradingScales(
                        self::SCORE_NUMBER
                        , $this->getSettingQualificationType());

        $data = array();
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = "['" . $value->DESCRIPTION . "', " . $value->ID . "]";
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    public function getSubjectAVGStudentList() {
        $entries = $this->listClassStudents();
        $data = array();
        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {
                $ii = $i + 1;
                $data[] = "['" . getFullName($value->FIRSTNAME, $value->LASTNAME) . "', " . $i . "]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    public function getSubjectPassStatus() {

        return "[{
            name: '" . MALE_STUDENTS . "',
            data: [10,5]

        }, {
            name: '" . FEMALE_STUDENTS . "',
            data: [2,5]

        }]";
    }

    ////////////////////////////////////////////////////////////////////////////
    // TEACHER ENTER SCORE...
    ////////////////////////////////////////////////////////////////////////////
    public function getImplodeAssignments() {

        $entries = $this->getCurrentClassAssignments();

        $data = array();
        if ($entries) {
            foreach ($entries as $value) {
                $data[] = "'" . addslashes($value->SHORT) . "'";
            }
        }

        return implode(",", $data);
    }

    public function getCountTeacherEnterScore($date) {

        $entries = $this->getCurrentClassAssignments();

        $data = array();
        if ($entries) {
            foreach ($entries as $value) {

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

    public function getDataSetTeacherEnterScore() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", Array("*"));
        $SQL->where("SUBJECT_ID = ?", $this->subjectId);
        $SQL->where("CLASS_ID = '" . $this->academicId . "'");
        $SQL->group('SCORE_INPUT_DATE');
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
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
}

?>