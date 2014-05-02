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
        $data["SUBJECT_VALUE"] = "";
        $data["ASSESSMENT_ID"] = "";

        if (isset($object->studentId)) {
            if ($object->studentId) {
                $SELECTION_A = Array('LEARNING_VALUE', 'RANK', 'ASSESSMENT_ID', 'TEACHER_COMMENT');
                $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');

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
                    $data["SUBJECT_VALUE"] = $result->SUBJECT_VALUE;
                    $data["ASSESSMENT_ID"] = $result->ASSESSMENT_ID;
                }
            }
        }

        return (object) $data;
    }

}

?>