<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

class SQLEvaluationStudentSubject {

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return false::dbAccess()->select();
    }

    public static function getCallStudentSubjectEvaluation($stdClass) {

        $data = array(
            'SUBJECT_VALUE' => ""
            , 'RANK' => ""
            , 'GRADING' => ""
            , 'ASSESSMENT_ID' => ""
            , 'TEACHER_COMMENT' => ""
            , 'MONTH_RESULT' => ""
            , 'TERM_RESULT' => ""
            , 'ASSIGNMENT_MONTH' => ""
            , 'ASSIGNMENT_TERM' => ""
            , 'FIRST_RESULT' => ""
            , 'SECOND_RESULT' => ""
            , 'THIRD_RESULT' => ""
            , 'FOURTH_RESULT' => ""
        );

        if (isset($stdClass->studentId)) {
            if ($stdClass->studentId) {
                $SELECTION_A = array(
                    'SUBJECT_VALUE'
                    , 'RANK'
                    , 'ASSESSMENT_ID'
                    , 'TEACHER_COMMENT'
                    , 'MONTH_RESULT'
                    , 'TERM_RESULT'
                    , 'ASSIGNMENT_MONTH'
                    , 'ASSIGNMENT_TERM'
                    , 'FIRST_RESULT'
                    , 'SECOND_RESULT'
                    , 'THIRD_RESULT'
                    , 'FOURTH_RESULT'
                );

                if (isset($stdClass->scoreType)) {
                    switch ($stdClass->scoreType) {
                        case 1:
                            $SELECTION_B = array('DESCRIPTION AS GRADING');
                            break;
                        case 2:
                            $SELECTION_B = array('LETTER_GRADE AS GRADING');
                            break;
                    }
                } else {
                    $SELECTION_B = array('DESCRIPTION AS GRADING');
                }

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
                $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
                $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");
                $SQL->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");
                $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
                $SQL->where("A.SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

                switch ($stdClass->section) {
                    case "MONTH":
                        if (isset($stdClass->month))
                            $SQL->where("A.MONTH = '" . $stdClass->month . "'");

                        if (isset($stdClass->year))
                            $SQL->where("A.YEAR = '" . $stdClass->year . "'");
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        if (isset($stdClass->term))
                            $SQL->where("A.TERM = '" . $stdClass->term . "'");
                        break;
                    case "YEAR":
                        if (isset($stdClass->year))
                            $SQL->where("A.YEAR = '" . $stdClass->year . "'");
                        break;
                }

                $SQL->where("A.SECTION = '" . $stdClass->section . "'");
                $SQL->group("B.ID");

                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchRow($SQL);
                if ($result) {
                    $data = array(
                        'SUBJECT_VALUE' => $result->SUBJECT_VALUE
                        , 'RANK' => $result->RANK ? $result->RANK : "---"
                        , 'GRADING' => $result->GRADING ? $result->GRADING : "---"
                        , 'ASSESSMENT_ID' => $result->ASSESSMENT_ID
                        , 'TEACHER_COMMENT' => $result->TEACHER_COMMENT
                        , 'MONTH_RESULT' => $result->MONTH_RESULT
                        , 'TERM_RESULT' => $result->TERM_RESULT
                        , 'ASSIGNMENT_MONTH' => $result->ASSIGNMENT_MONTH
                        , 'ASSIGNMENT_TERM' => $result->ASSIGNMENT_TERM
                        , 'FIRST_RESULT' => $result->FIRST_RESULT
                        , 'SECOND_RESULT' => $result->SECOND_RESULT
                        , 'THIRD_RESULT' => $result->THIRD_RESULT
                        , 'FOURTH_RESULT' => $result->FOURTH_RESULT
                    );
                }

                return (object) $data;
            }
        }

        return (object) $data;
    }

    public static function checkStudentSubjectEvaluation($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_assessment", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->where("SUBJECT_ID = '" . $stdClass->subjectId . "'");
        $SQL->where("CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

        switch ($stdClass->section) {
            case "MONTH":
                if (isset($stdClass->month))
                    $SQL->where("MONTH = '" . $stdClass->month . "'");

                if (isset($stdClass->year)) {
                    $SQL->where("YEAR = '" . $stdClass->year . "'");
                }
                break;
            case "TERM":
            case "QUARTER":
            case "SEMESTER":
                if (isset($stdClass->term))
                    $SQL->where("TERM = '" . $stdClass->term . "'");
                break;
            case "YEAR":
                if (isset($stdClass->year))
                    $SQL->where("YEAR = '" . $stdClass->year . "'");
                break;
        }

        $SQL->where("SECTION = '" . $stdClass->section . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function setActionStudentSubjectEvaluation($stdClass) {

        if (isset($stdClass->actionValue)) {
            if ($stdClass->actionValue)
                $SAVE_DATA["SUBJECT_VALUE"] = $stdClass->actionValue;
        }

        if (isset($stdClass->assessmentId))
            $SAVE_DATA["ASSESSMENT_ID"] = $stdClass->assessmentId;

        if (isset($stdClass->actionRank)) {
            if ($stdClass->actionRank)
                $SAVE_DATA["RANK"] = $stdClass->actionRank;
        }

        if (isset($stdClass->comment))
            $SAVE_DATA["TEACHER_COMMENT"] = $stdClass->comment;

        if (isset($stdClass->monthResult))
            $SAVE_DATA["MONTH_RESULT"] = $stdClass->monthResult;

        if (isset($stdClass->termResult))
            $SAVE_DATA["TERM_RESULT"] = $stdClass->termResult;

        if (isset($stdClass->monthAssignment))
            $SAVE_DATA["ASSIGNMENT_MONTH"] = $stdClass->monthAssignment;

        if (isset($stdClass->termAssignment))
            $SAVE_DATA["ASSIGNMENT_TERM"] = $stdClass->termAssignment;

        if (isset($stdClass->firstResult))
            $SAVE_DATA["FIRST_RESULT"] = $stdClass->firstResult;

        if (isset($stdClass->secondResult))
            $SAVE_DATA["SECOND_RESULT"] = $stdClass->secondResult;

        if (isset($stdClass->thirdResult))
            $SAVE_DATA["THIRD_RESULT"] = $stdClass->thirdResult;

        if (isset($stdClass->fourthResult))
            $SAVE_DATA["FOURTH_RESULT"] = $stdClass->fourthResult;

        if (isset($stdClass->studentId)) {
            if ($stdClass->studentId) {

                if (self::checkStudentSubjectEvaluation($stdClass)) {

                    $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
                    $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
                    $WHERE[] = "SUBJECT_ID = '" . $stdClass->subjectId . "'";
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
                    self::dbAccess()->update('t_student_subject_assessment', $SAVE_DATA, $WHERE);
                } else {

                    $SAVE_DATA["STUDENT_ID"] = $stdClass->studentId;
                    $SAVE_DATA["SUBJECT_ID"] = $stdClass->subjectId;
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

                    if (isset($stdClass->comment))
                        $SAVE_DATA["TEACHER_COMMENT"] = $stdClass->comment;

                    $SAVE_DATA["ACTION_TYPE"] = "ASSESSMENT";
                    $SAVE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
                    $SAVE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;

                    self::dbAccess()->insert("t_student_subject_assessment", $SAVE_DATA);
                }
            }
        }
    }

    public static function getActionDeleteSubjectScoreAssessment($stdClass) {
        self::dbAccess()->delete('t_student_subject_assessment'
                , array(
            "CLASS_ID='" . $stdClass->academicId . "'"
            , "SUBJECT_ID='" . $stdClass->subjectId . "'")
        );

        self::dbAccess()->delete('t_student_assignment'
                , array(
            "CLASS_ID='" . $stdClass->academicId . "'"
            , "SUBJECT_ID='" . $stdClass->subjectId . "'")
        );
    }

}

?>