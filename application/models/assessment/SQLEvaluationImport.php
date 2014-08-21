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

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', '1200');

        $EXCEL_DATA = new Spreadsheet_Excel_Reader();
        $EXCEL_DATA->setUTFEncoder('iconv');
        $EXCEL_DATA->setOutputEncoding('UTF-8');
        $EXCEL_DATA->read($stdClass->tmp_name);

        $COUNT = count($EXCEL_DATA->sheets);

        switch ($COUNT) {
            case 1:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                break;
            case 2:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                break;
            case 3:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                break;
            case 4:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                break;
            case 5:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[4], $stdClass);
                break;
            case 6:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[4], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[5], $stdClass);
                break;
            case 7:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[4], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[5], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[6], $stdClass);
                break;
            case 8:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[4], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[5], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[6], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[7], $stdClass);
                break;
            case 9:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[4], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[5], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[6], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[7], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[8], $stdClass);
                break;
            case 10:
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[0], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[1], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[2], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[3], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[4], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[5], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[6], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[7], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[8], $stdClass);
                self::actionImportScoreAssignment($EXCEL_DATA->sheets[9], $stdClass);
                break;
        }
    }

    public static function actionImportScoreAssignment($sheets, $stdClass) {

        $STUDENT_DATA = self::getListStudents($stdClass);

        $date = isset($sheets['cells'][2][2]) ? $sheets['cells'][2][2] : "";
        $keys = isset($sheets['cells'][2][3]) ? $sheets['cells'][2][3] : "";
        $stdClass->isManual = 0;

        if ($keys) {

            $explode = explode("_", $keys);
            $academicId = isset($explode[0]) ? $explode[0] : "";
            $subjectId = isset($explode[1]) ? $explode[1] : "";
            $assignmentId = isset($explode[2]) ? $explode[2] : "";

            if ($academicId && $subjectId && $assignmentId) {

                $assessment = new EvaluationSubjectAssessment();
                $assessment->setAcademicId($academicId);
                $assessment->setSubjectId($subjectId);
                $assessment->setAssignmentId($assignmentId);
                $assessment->setDate($date);

                $stdClass->include_in_valuation = $assessment->getAssignmentInCludeEvaluation();
                $stdClass->term = $assessment->getAcademicTerm();
                $stdClass->scoreMax = $assessment->getSubjectScoreMax();
                $stdClass->scoreMin = $assessment->getSubjectScoreMin();
                $stdClass->scoreType = $assessment->getSubjectScoreType();
                $stdClass->coeffValue = $assessment->getAssignmentCoeff();

                $stdClass->actionType = "IMPORT";
                $stdClass->academicId = $academicId;
                $stdClass->subjectId = $subjectId;
                $stdClass->assignmentId = $assignmentId;
                $stdClass->date = setDate2DB($date);

                for ($i = 1; $i <= $sheets['numRows']; $i++) {
                    $studentCodeId = isset($sheets['cells'][$i + 3][1]) ? $sheets['cells'][$i + 3][1] : "";
                    $score = isset($sheets['cells'][$i + 3][3]) ? $sheets['cells'][$i + 3][3] : "";
                    $studentId = isset($STUDENT_DATA[$studentCodeId]) ? $STUDENT_DATA[$studentCodeId] : "";

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
                                    if (!is_numeric($score)) {
                                        $stdClass->actionValue = strtoupper($score);
                                        SQLEvaluationStudentAssignment::setActionStudentScoreSubjectAssignment($stdClass);
                                    }
                                    break;
                            }
                        }
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
        $stdClass->isManual = 0;
        $stdClass->evaluationOption = 1;

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
                            if (is_numeric($score)) {
                                $stdClass->average = trim($score);
                                if ($score >= $stdClass->scoreMin && $score <= $stdClass->scoreMax) {
                                    SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
                                }
                            }

                            break;
                        case 2:
                            if (!is_numeric($score)) {
                                $stdClass->mappingValue = strtoupper(trim($score));
                                $stdClass->assessmentId = self::getAssessmentId($score, $stdClass->qualificationType);
                                SQLEvaluationStudentSubject::setActionStudentSubjectEvaluation($stdClass);
                            }

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