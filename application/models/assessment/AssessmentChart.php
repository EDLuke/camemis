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
                $data[] = "['" . $value->LASTNAME . "', " . $i . "]";

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

}

?>