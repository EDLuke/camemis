<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/SQLAcademicPerformances.php';

class SQLAcademicPerformances {

    const TRADITIONAL_SYSTEM = 0;
    const CREDIT_SYSTEM = 1;

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return false::dbAccess()->select();
    }

    public static function getSQLStudentGPA($stdClass, $term = false) {

        switch ($stdClass->educationSystem) {
            case self::TRADITIONAL_SYSTEM:
                $SELECTION = array(
                    "SUM(GRADE_POINTS) AS SUM_FIRST"
                    , "COUNT(GRADE_POINTS) AS SUM_SECOND");
                break;
            case self::CREDIT_SYSTEM:
                $SELECTION = array(
                    "SUM(GRADE_POINTS*CREDIT_HOURS) AS SUM_FIRST"
                    , "SUM(GRADE_POINTS) AS SUM_SECOND");
                break;
        }

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_assessment", $SELECTION);
        $SQL->where('CLASS_ID = ?', $stdClass->academicId);
        $SQL->where('STUDENT_ID = ?', $stdClass->studentId);
        $SQL->where('SCORE_TYPE = ?', 1);
        if (isset($stdClass->month) && isset($stdClass->year)) {
            if ($stdClass->month)
                $SQL->where("MONTH = '" . $stdClass->month . "'");
            if ($stdClass->year)
                $SQL->where("YEAR = '" . $stdClass->year . "'");
        }

        if ($term) {
            $SQL->where("TERM = '" . $term . "'");
        } else {
            if (!isset($stdClass->month) && !isset($stdClass->year)) {
                if (isset($stdClass->term))
                    $SQL->where("TERM = '" . $stdClass->term . "'");
            }
        }

        $SQL->group("TERM");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $output = "";

        if ($result) {
            if ($result->SUM_SECOND)
                $output = displayRound($result->SUM_FIRST / $result->SUM_SECOND);
        }

        return $output;
    }

    public static function getSQLAverageStudentAcademicPerformance($stdClass, $type = false, $term = false) {

        if ($type) {
            $SELECTION = array(
                "SUM(SUBJECT_VALUE_PERCENT*COEFF_VALUE) AS SUM_VALUE"
                , "IF(COEFF_VALUE =0,COEFF_VALUE =1,COEFF_VALUE )"
                , "SUM(COEFF_VALUE) AS SUM_COEFF");
        } else {
            $SELECTION = array(
                "SUM(SUBJECT_VALUE*COEFF_VALUE) AS SUM_VALUE"
                , "IF(COEFF_VALUE =0,COEFF_VALUE =1,COEFF_VALUE )"
                , "SUM(COEFF_VALUE) AS SUM_COEFF");
        }

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_assessment", $SELECTION);
        $SQL->where('CLASS_ID = ?', $stdClass->academicId);
        $SQL->where('STUDENT_ID = ?', $stdClass->studentId);
        $SQL->where('SCORE_TYPE = ?', 1);
        if (isset($stdClass->month) && isset($stdClass->year)) {
            if ($stdClass->month)
                $SQL->where("MONTH = '" . $stdClass->month . "'");
            if ($stdClass->year)
                $SQL->where("YEAR = '" . $stdClass->year . "'");
        }

        if ($term) {
            $SQL->where("TERM = '" . $term . "'");
        } else {
            if (!isset($stdClass->month) && !isset($stdClass->year)) {
                if (isset($stdClass->term))
                    $SQL->where("TERM = '" . $stdClass->term . "'");
            }
        }

        $SQL->group("TERM");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $output = "";

        if ($result) {
            if ($result->SUM_COEFF)
                $output = displayRound($result->SUM_VALUE / $result->SUM_COEFF);
        }

        return $output;
    }

    public static function getCallStudentAcademicPerformance($stdClass) {

        $academicObject = AcademicDBAccess::findGradeFromId($stdClass->academicId);
        $GRADING_TYPE = $academicObject->GRADING_TYPE ? "LETTER_GRADE" : "DESCRIPTION";

        $data["GRADING"] = "---";
        $data["RANK"] = "---";
        $data["GRADE_POINTS"] = "---";
        $data["IS_FAIL"] = "";
        $data["GPA"] = "---";
        $data["TOTAL_RESULT"] = "---";
        $data["FIRST_RESULT"] = "---";
        $data["SECOND_RESULT"] = "---";
        $data["THIRD_RESULT"] = "---";
        $data["FOURTH_RESULT"] = "---";
        $data["ASSESSMENT_ID"] = "---";
        $data["TEACHER_COMMENT"] = "---";

        if (isset($stdClass->studentId)) {
            if ($stdClass->studentId) {
                $SELECTION_A = array(
                    'RANK'
                    , 'ASSESSMENT_ID'
                    , 'GRADE_POINTS'
                    , 'GPA'
                    , 'TOTAL_RESULT'
                    , 'FIRST_RESULT'
                    , 'SECOND_RESULT'
                    , 'THIRD_RESULT'
                    , 'FOURTH_RESULT'
                    , 'TEACHER_COMMENT'
                );

                $SELECTION_B = array("" . $GRADING_TYPE . "", "IS_FAIL");

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => "t_student_learning_performance"), $SELECTION_A);
                $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
                $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");
                $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
                $SQL->where("A.SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

                if (isset($stdClass->section)) {
                    switch ($stdClass->section) {
                        case "MONTH":
                            if ($stdClass->month)
                                $SQL->where("A.MONTH = '" . $stdClass->month . "'");

                            if ($stdClass->year)
                                $SQL->where("A.YEAR = '" . $stdClass->year . "'");
                            break;
                        case "TERM":
                        case "QUARTER":
                        case "SEMESTER":
                            if ($stdClass->term)
                                $SQL->where("A.TERM = '" . $stdClass->term . "'");
                            break;
                    }

                    $SQL->where("A.SECTION = '" . $stdClass->section . "'");
                }

                $SQL->group("B.ID");

                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchRow($SQL);
                if ($result) {
                    $data["GRADING"] = $result->$GRADING_TYPE ? $result->$GRADING_TYPE : "---";
                    $data["RANK"] = $result->RANK ? $result->RANK : "---";
                    $data["IS_FAIL"] = $result->IS_FAIL;
                    $data["GPA"] = $result->GPA ? $result->GPA : "---";
                    $data["GRADE_POINTS"] = $result->GRADE_POINTS ? $result->GRADE_POINTS : "---";
                    $data["TOTAL_RESULT"] = $result->TOTAL_RESULT ? $result->TOTAL_RESULT : "---";
                    $data["FIRST_RESULT"] = $result->FIRST_RESULT ? $result->FIRST_RESULT : "---";
                    $data["SECOND_RESULT"] = $result->SECOND_RESULT ? $result->SECOND_RESULT : "---";
                    $data["THIRD_RESULT"] = $result->THIRD_RESULT ? $result->THIRD_RESULT : "---";
                    $data["FOURTH_RESULT"] = $result->FOURTH_RESULT ? $result->FOURTH_RESULT : "---";
                    $data["ASSESSMENT_ID"] = $result->ASSESSMENT_ID;
                    $data["TEACHER_COMMENT"] = $result->TEACHER_COMMENT;
                }
            }
        }

        return (object) $data;
    }

    public static function checkStudentAcademicPerformance($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_learning_performance", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->where("CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

        switch ($stdClass->section) {
            case "MONTH":
                if ($stdClass->month)
                    $SQL->where("MONTH = '" . $stdClass->month . "'");

                if ($stdClass->year) {
                    $SQL->where("YEAR = '" . $stdClass->year . "'");
                }
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                if ($stdClass->term)
                    $SQL->where("TERM = '" . $stdClass->term . "'");
                break;
        }

        $SQL->where("SECTION = '" . $stdClass->section . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function scoreListAcademicPerformance($listStudents, $stdClass) {
        $data = array();
        if ($listStudents) {
            foreach ($listStudents as $value) {
                $stdClass->studentId = $value->ID;
                $data[] = self::getSQLAverageStudentAcademicPerformance($stdClass, false, false);
            }
        }
        return $data;
    }

    public static function getActionStudentAcademicPerformance($stdClass) {

        if (isset($stdClass->average)) {
            $SAVE_DATA["TOTAL_RESULT"] = $stdClass->average;
        }

        if (isset($stdClass->rank)) {
            if (is_numeric($stdClass->average)) {
                $SAVE_DATA["RANK"] = $stdClass->rank;
            }
        }

        if (isset($stdClass->averagePercent)) {
            $SAVE_DATA["TOTAL_RESULT_PERCENT"] = $stdClass->averagePercent;
            if ($stdClass->averagePercent) {
                $SAVE_DATA["ASSESSMENT_ID"] = AssessmentConfig::calculateGradingScale(
                                $stdClass->averagePercent
                                , $stdClass->qualificationType
                );
            }
        } else {
            if (isset($stdClass->assessmentId)) {
                $SAVE_DATA["ASSESSMENT_ID"] = $stdClass->assessmentId;
            }
        }

        if (isset($stdClass->firstResult)) {
            $SAVE_DATA["FIRST_RESULT"] = $stdClass->firstResult;
        }

        if (isset($stdClass->secondResult)) {
            $SAVE_DATA["SECOND_RESULT"] = $stdClass->secondResult;
        }

        if (isset($stdClass->thirdResult)) {
            $SAVE_DATA["THIRD_RESULT"] = $stdClass->thirdResult;
        }

        if (isset($stdClass->fourthResult)) {
            $SAVE_DATA["FOURTH_RESULT"] = $stdClass->fourthResult;
        }

        if (isset($stdClass->firstResultPercent)) {
            $SAVE_DATA["FIRST_RESULT_PERCENT"] = $stdClass->firstResultPercent;
        }

        if (isset($stdClass->secondResultPercent)) {
            $SAVE_DATA["SECOND_RESULT_PERCENT"] = $stdClass->secondResultPercent;
        }

        if (isset($stdClass->thirdResultPercent)) {
            $SAVE_DATA["THIRD_RESULT_PERCENT"] = $stdClass->thirdResultPercent;
        }

        if (isset($stdClass->fourthResultPercent)) {
            $SAVE_DATA["FOURTH_RESULT_PERCENT"] = $stdClass->fourthResultPercent;
        }

        if (isset($stdClass->gpaValue)) {
            $SAVE_DATA["GPA"] = $stdClass->gpaValue;
        }

        if (self::checkStudentAcademicPerformance($stdClass)) {

            $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
            $WHERE[] = "SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'";

            switch ($stdClass->section) {
                case "MONTH":
                    $WHERE[] = "MONTH = '" . $stdClass->month . "'";
                    $WHERE[] = "YEAR = '" . $stdClass->year . "'";
                    break;
                case "TERM":
                case "QUARTER":
                case "SEMESTER":
                    $WHERE[] = "TERM = '" . $stdClass->term . "'";
                    break;
                case "YEAR":
                    $WHERE[] = "SECTION = 'YEAR'";
                    break;
            }

            $WHERE[] = "ACTION_TYPE = 'ASSESSMENT'";

            $SAVE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
            $SAVE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->update('t_student_learning_performance', $SAVE_DATA, $WHERE);
        } else {

            $SAVE_DATA["STUDENT_ID"] = $stdClass->studentId;
            $SAVE_DATA["CLASS_ID"] = $stdClass->academicId;
            $SAVE_DATA["SCHOOLYEAR_ID"] = $stdClass->schoolyearId;

            if (isset($stdClass->month))
                $SAVE_DATA["MONTH"] = $stdClass->month;

            if (isset($stdClass->year))
                $SAVE_DATA["YEAR"] = $stdClass->year;

            if (isset($stdClass->term))
                $SAVE_DATA["TERM"] = $stdClass->term;

            if ($stdClass->section)
                $SAVE_DATA["SECTION"] = $stdClass->section;

            if (isset($stdClass->educationSystem))
                $SAVE_DATA["EDUCATION_SYSTEM"] = $stdClass->educationSystem;

            $SAVE_DATA["ACTION_TYPE"] = "ASSESSMENT";
            $SAVE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
            $SAVE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert("t_student_learning_performance", $SAVE_DATA);
        }
    }

}

?>