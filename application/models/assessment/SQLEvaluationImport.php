<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.05.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once('excel/excel_reader2.php');

class SQLEvaluationImport {

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function getListStudents($stdClass) {
        $data = array();

        if ($stdClass->listStudents) {
            foreach ($stdClass->listStudents as $value) {
                $data[$value->CODE] = $value->ID;
            }
        }

        return $data;
    }

    public static function importScoreAssignment($stdClass) {

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($stdClass->tmp_name);

        $STUDENT_DATA = self::getListStudents($stdClass);

        for ($i = 0; $i <= $xls->sheets[0]['numRows']; $i++) {

            $code = isset($xls->sheets[0]['cells'][$i + 3][1]) ? $xls->sheets[0]['cells'][$i + 3][1] : "";
            $score = isset($xls->sheets[0]['cells'][$i + 3][3]) ? $xls->sheets[0]['cells'][$i + 3][3] : "";

            $studentId = isset($STUDENT_DATA[$code]) ? $STUDENT_DATA[$code] : "";

            if ($studentId) {

                if ($score) {
                    $stdClass->studentId = $studentId;
                    switch ($stdClass->scoreType) {
                        case 1:
                            $stdClass->actionValue = $score;
                            if ($score >= $stdClass->scoreMin && $score <= $stdClass->scoreMax) {
                                SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($stdClass);
                            }
                            break;
                        case 2:
                            $stdClass->actionValue = strtoupper($score);
                            SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($stdClass);
                            break;
                    }
                }
            }
        }
    }

    public static function importScoreSubject($stdClass) {

        $xls = new Spreadsheet_Excel_Reader();
        $xls->setUTFEncoder('iconv');
        $xls->setOutputEncoding('UTF-8');
        $xls->read($stdClass->tmp_name);

        $STUDENT_DATA = self::getListStudents($stdClass);

        for ($i = 0; $i <= $xls->sheets[0]['numRows']; $i++) {

            $code = isset($xls->sheets[0]['cells'][$i + 3][1]) ? $xls->sheets[0]['cells'][$i + 3][1] : "";
            $score = isset($xls->sheets[0]['cells'][$i + 3][3]) ? $xls->sheets[0]['cells'][$i + 3][3] : "";
            $rank = isset($xls->sheets[0]['cells'][$i + 3][4]) ? $xls->sheets[0]['cells'][$i + 3][4] : "";

            $studentId = isset($STUDENT_DATA[$code]) ? $STUDENT_DATA[$code] : "";

            if ($studentId) {

                if (trim($score)) {
                    $stdClass->studentId = $studentId;
                    if (is_numeric($rank))
                        $stdClass->actionRank = $rank;

                    switch ($stdClass->scoreType) {
                        case 1:
                            $stdClass->average = trim($score);
                            if ($score >= $stdClass->scoreMin && $score <= $stdClass->scoreMax) {
                                SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
                            }
                            break;
                        case 2:
                            $stdClass->mappingValue = strtoupper(trim($score));
                            $stdClass->assessmentId = self::getAssessmentId($score, $stdClass->qualificationType);
                            SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
                            break;
                    }
                }
            }
        }
    }

    public static function getAssessmentId($score, $qualificationType) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '2'");
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");
        $SQL->where("LETTER_GRADE = '" . trim($score) . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ID : "";
    }

}

?>