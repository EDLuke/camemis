<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'models/assessment/SQLAcademicPerformances.php';

class SQLAcademicPerformances {

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return false::dbAccess()->select();
    }

    public static function getSQLAverageStudentClassPerformance($stdClass)
    {

        $SELECTION_A = array("SUM(A.SUBJECT_VALUE*B.COEFF_VALUE) AS SUM_VALUE");
        $SELECTION_B = array("IF( B.COEFF_VALUE =0, B.COEFF_VALUE =1, B.COEFF_VALUE )", "SUM(B.COEFF_VALUE) AS SUM_COEFF");

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_subject_assessment'), $SELECTION_A);
        $SQL->joinLeft(array('B' => "t_grade_subject"), 'A.SUBJECT_ID=B.SUBJECT', $SELECTION_B);
        $SQL->where('A.CLASS_ID = ?', $stdClass->academicId);
        $SQL->where('A.STUDENT_ID = ?', $stdClass->studentId);
        $SQL->where('B.SCORE_TYPE = ?', 1);

        if (isset($stdClass->month) && isset($stdClass->year))
        {
            if ($stdClass->month)
                $SQL->where("A.MONTH = '" . $stdClass->month . "'");
            if ($stdClass->year)
                $SQL->where("A.YEAR = '" . $stdClass->year . "'");
        }

        if (!isset($stdClass->month) && !isset($stdClass->year))
        {
            if (isset($stdClass->term))
                $SQL->where("A.TERM = '" . $stdClass->term . "'");
        }

        $SQL->group("A.SUBJECT_ID");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $output = "";

        if ($result)
        {
            if ($result->SUM_COEFF)
                $output = displayRound($result->SUM_VALUE / $result->SUM_COEFF);
        }

        return $output;
    }

    public static function getCallStudentAcademicPerformance($stdClass)
    {

        $data["LETTER_GRADE_NUMBER"] = "---";
        $data["LETTER_GRADE_CHAR"] = "---";
        $data["LEARNING_VALUE"] = "---";
        $data["ASSESSMENT_ID"] = "---";
        $data["TEACHER_COMMENT"] = "---";

        if (isset($stdClass->studentId))
        {
            if ($stdClass->studentId)
            {
                $SELECTION_A = array('LEARNING_VALUE', 'RANK', 'ASSESSMENT_ID', 'TEACHER_COMMENT');
                $SELECTION_B = array('DESCRIPTION', 'LETTER_GRADE');

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => "t_student_learning_performance"), $SELECTION_A);
                $SQL->joinInner(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
                $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");
                $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
                $SQL->where("A.SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

                if (isset($stdClass->section))
                {
                    switch ($stdClass->section)
                    {
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
                        case "YEAR":
                            if ($stdClass->year)
                                $SQL->where("A.YEAR = '" . $stdClass->year . "'");
                            break;
                    }

                    $SQL->where("A.SECTION = '" . $stdClass->section . "'");
                }

                $SQL->group("B.ID");

                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchRow($SQL);
                if ($result)
                {
                    $data["LETTER_GRADE_NUMBER"] = $result->DESCRIPTION;
                    $data["LETTER_GRADE_CHAR"] = $result->LETTER_GRADE;
                    $data["LEARNING_VALUE"] = $result->LEARNING_VALUE;
                    $data["ASSESSMENT_ID"] = $result->ASSESSMENT_ID;
                    $data["TEACHER_COMMENT"] = $result->TEACHER_COMMENT;
                }
            }
        }

        return (object) $data;
    }

    public static function checkStudentClassPerformance($stdClass)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_learning_performance", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->where("CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

        switch ($stdClass->section)
        {
            case "MONTH":
                if ($stdClass->month)
                    $SQL->where("MONTH = '" . $stdClass->month . "'");

                if ($stdClass->year)
                {
                    $SQL->where("YEAR = '" . $stdClass->year . "'");
                }
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                if ($stdClass->term)
                    $SQL->where("TERM = '" . $stdClass->term . "'");
                break;
            case "YEAR":
                if ($stdClass->year)
                {
                    $SQL->where("YEAR = '" . $stdClass->year . "'");
                }
                break;
        }

        $SQL->where("SECTION = '" . $stdClass->section . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function scoreListClassPerformance($listStudents, $stdClass)
    {
        $data = array();
        if ($listStudents)
        {
            foreach ($listStudents as $value)
            {
                $stdClass->studentId = $value->ID;
                $data[] = self::getSQLAverageStudentClassPerformance($stdClass);
            }
        }
        return $data;
    }

    public static function getActionStudentClassPerformance($stdClass)
    {

        if (self::checkStudentClassPerformance($stdClass))
        {

            $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
            $WHERE[] = "SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'";

            switch ($stdClass->section)
            {
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

            if (isset($stdClass->actionField))
            {
                switch ($stdClass->actionField)
                {
                    case "ASSESSMENT_TOTAL":

                        $AVERAGE = self::getSQLAverageStudentClassPerformance($stdClass);
                        $RANK = getScoreRank(self::scoreListClassPerformance($stdClass->listStudents, $stdClass), $AVERAGE);

                        if (isset($stdClass->actionValue))
                            $UPDATE_DATA["ASSESSMENT_ID"] = $stdClass->actionValue;
                        $UPDATE_DATA["LEARNING_VALUE"] = $AVERAGE;
                        $UPDATE_DATA["RANK"] = $RANK ? $RANK : "";

                        break;
                    case "AVERAGE_TOTAL":
                        break;
                }
            }

            $UPDATE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
            $UPDATE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->update('t_student_learning_performance', $UPDATE_DATA, $WHERE);
        }
        else
        {

            $INSERT_DATA["STUDENT_ID"] = $stdClass->studentId;
            $INSERT_DATA["CLASS_ID"] = $stdClass->academicId;
            $INSERT_DATA["SCHOOLYEAR_ID"] = $stdClass->schoolyearId;

            if (isset($stdClass->month))
                $INSERT_DATA["MONTH"] = $stdClass->month;

            if (isset($stdClass->year))
                $INSERT_DATA["YEAR"] = $stdClass->year;

            if (isset($stdClass->term))
                $INSERT_DATA["TERM"] = $stdClass->term;

            if ($stdClass->section)
                $INSERT_DATA["SECTION"] = $stdClass->section;

            if (isset($stdClass->educationSystem))
                $INSERT_DATA["EDUCATION_SYSTEM"] = $stdClass->educationSystem;

            if (isset($stdClass->actionField))
            {
                switch ($stdClass->actionField)
                {
                    case "ASSESSMENT_TOTAL":

                        $AVERAGE = self::getSQLAverageStudentClassPerformance($stdClass);
                        $RANK = getScoreRank(self::scoreListClassPerformance($stdClass->listStudents, $stdClass), $AVERAGE);

                        if (isset($stdClass->actionValue))
                            $INSERT_DATA["ASSESSMENT_ID"] = $stdClass->actionValue;
                        $INSERT_DATA["LEARNING_VALUE"] = $AVERAGE;
                        $INSERT_DATA["RANK"] = $RANK ? $RANK : "";

                        break;
                    case "AVERAGE_TOTAL":
                        break;
                }
            }

            $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
            $INSERT_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
            $INSERT_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert("t_student_learning_performance", $INSERT_DATA);
        }
    }

}

?>