<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/SQLAcademicPerformances.php';

class SQLAcademicPerformances {

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return false::dbAccess()->select();
    }

    public static function getSQLAverageStudentClassPerformance($object) {

        $SELECTION_A = array("SUM(A.SUBJECT_VALUE*B.COEFF_VALUE) AS SUM_VALUE");
        $SELECTION_B = array("IF( B.COEFF_VALUE =0, B.COEFF_VALUE =1, B.COEFF_VALUE )", "SUM(B.COEFF_VALUE) AS SUM_COEFF");

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_subject_assessment'), $SELECTION_A);
        $SQL->joinLeft(array('B' => "t_grade_subject"), 'A.SUBJECT_ID=B.SUBJECT', $SELECTION_B);
        $SQL->where('A.CLASS_ID = ?', $object->academicId);
        $SQL->where('A.STUDENT_ID = ?', $object->studentId);
        $SQL->where('B.SCORE_TYPE = ?', 1);

        switch ($object->section) {
            case "MONTH":
                if ($object->month)
                    $SQL->where("A.MONTH = '" . $object->month . "'");

                if ($object->year)
                    $SQL->where("A.YEAR = '" . $object->year . "'");
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                if ($object->term)
                    $SQL->where("A.TERM = '" . $object->term . "'");
                break;
            case "YEAR":
                if ($object->year)
                    $SQL->where("A.YEAR = '" . $object->year . "'");
                break;
        }

        $SQL->group("A.SUBJECT_ID");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $output = "";

        if ($result) {
            if ($result->SUM_COEFF)
                $output = displayRound($result->SUM_VALUE / $result->SUM_COEFF);
        }

        return $output;
    }

    public static function getCallStudentAcademicPerformance($object) {

        $data["LETTER_GRADE_NUMBER"] = "";
        $data["LETTER_GRADE_CHAR"] = "";
        $data["LEARNING_VALUE"] = "";
        $data["ASSESSMENT_ID"] = "";

        if (isset($object->studentId)) {
            if ($object->studentId) {
                $SELECTION_A = array('LEARNING_VALUE', 'RANK', 'ASSESSMENT_ID', 'TEACHER_COMMENT');
                $SELECTION_B = array('DESCRIPTION', 'LETTER_GRADE');

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => "t_student_learning_performance"), $SELECTION_A);
                $SQL->joinInner(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
                $SQL->where("A.STUDENT_ID = '" . $object->studentId . "'");
                $SQL->where("A.CLASS_ID = '" . $object->academicId . "'");
                $SQL->where("A.SCHOOLYEAR_ID = '" . $object->schoolyearId . "'");

                switch ($object->section) {
                    case "MONTH":
                        if ($object->month)
                            $SQL->where("A.MONTH = '" . $object->month . "'");

                        if ($object->year)
                            $SQL->where("A.YEAR = '" . $object->year . "'");
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        if ($object->term)
                            $SQL->where("A.TERM = '" . $object->term . "'");
                        break;
                    case "YEAR":
                        if ($object->year)
                            $SQL->where("A.YEAR = '" . $object->year . "'");
                        break;
                }

                $SQL->where("A.SECTION = '" . $object->section . "'");
                $SQL->group("B.ID");

                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchRow($SQL);
                if ($result) {
                    $data["LETTER_GRADE_NUMBER"] = $result->DESCRIPTION;
                    $data["LETTER_GRADE_CHAR"] = $result->LETTER_GRADE;
                    $data["LEARNING_VALUE"] = $result->LEARNING_VALUE;
                    $data["ASSESSMENT_ID"] = $result->ASSESSMENT_ID;
                }
            }
        }

        return (object) $data;
    }

    public static function checkStudentClassPerformance($object) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_learning_performance", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $object->studentId . "'");
        $SQL->where("CLASS_ID = '" . $object->academicId . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $object->schoolyearId . "'");

        switch ($object->section) {
            case "MONTH":
                if ($object->month)
                    $SQL->where("MONTH = '" . $object->month . "'");

                if ($object->year) {
                    $SQL->where("YEAR = '" . $object->year . "'");
                }
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                if ($object->term)
                    $SQL->where("TERM = '" . $object->term . "'");
                break;
            case "YEAR":
                if ($object->year) {
                    $SQL->where("YEAR = '" . $object->year . "'");
                }
                break;
        }

        $SQL->where("SECTION = '" . $object->section . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getActionStudentClassPerformance($object) {

        if (self::checkStudentClassPerformance($object)) {

            $WHERE[] = "STUDENT_ID = '" . $object->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $object->academicId . "'";
            $WHERE[] = "SCHOOLYEAR_ID = '" . $object->schoolyearId . "'";

            switch ($object->section) {
                case "MONTH":
                    $WHERE[] = "MONTH = '" . $object->month . "'";
                    $WHERE[] = "YEAR = '" . $object->year . "'";
                    break;
                case "TERM":
                case "QUARTER":
                case "SEMESTER":
                    $WHERE[] = "TERM = '" . $object->term . "'";
                    break;
                case "YEAR":
                    $WHERE[] = "SECTION = 'YEAR'";
                    break;
            }
            $WHERE[] = "ACTION_TYPE = 'ASSESSMENT'";

            switch ($object->actionField) {
                case "ASSESSMENT_TOTAL":
                    $facette = self::getCallStudentAcademicPerformance($object);
                    $UPDATE_DATA["ASSESSMENT_ID"] = $object->actionValue;
                    $UPDATE_DATA["LEARNING_VALUE"] = self::getSQLAverageStudentClassPerformance($object);
                    $UPDATE_DATA["RANK"] = $facette->RANK;
                    break;
                case "AVERAGE_TOTAL":
                    break;
            }

            $UPDATE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
            $UPDATE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->update('t_student_learning_performance', $UPDATE_DATA, $WHERE);
        } else {

            $INSERT_DATA["STUDENT_ID"] = $object->studentId;
            $INSERT_DATA["CLASS_ID"] = $object->academicId;
            $INSERT_DATA["SCHOOLYEAR_ID"] = $object->schoolyearId;

            if ($object->month)
                $INSERT_DATA["MONTH"] = $object->month;

            if ($object->year)
                $INSERT_DATA["YEAR"] = $object->year;

            if ($object->term)
                $INSERT_DATA["TERM"] = $object->term;

            if ($object->section)
                $INSERT_DATA["SECTION"] = $object->section;

            if ($object->educationSystem)
                $INSERT_DATA["EDUCATION_SYSTEM"] = $object->educationSystem;

            switch ($object->actionField) {
                case "ASSESSMENT_TOTAL":
                    $facette = self::getCallStudentAcademicPerformance($object);
                    $INSERT_DATA["ASSESSMENT_ID"] = $object->actionValue;
                    $INSERT_DATA["LEARNING_VALUE"] = self::getSQLAverageStudentClassPerformance($object);
                    $INSERT_DATA["RANK"] = $facette->RANK;
                    break;
                case "AVERAGE_TOTAL":
                    break;
            }

            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
            $INSERT_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
            $INSERT_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert("t_student_learning_performance", $INSERT_DATA);
        }
    }

}

?>