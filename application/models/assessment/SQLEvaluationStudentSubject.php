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

    public static function getPropertiesStudentSE($stdClass, $staticTerm = false) {

        $academicObject = AcademicDBAccess::findGradeFromId($stdClass->academicId);
        $GRADING_TYPE = $academicObject->GRADING_TYPE ? "LETTER_GRADE" : "DESCRIPTION";

        $data = array(
            'SUBJECT_VALUE' => ""
            , 'IS_MANUAL' => 0
            , 'SUBJECT_VALUE_REPEAT' => ""
            , 'RANK' => ""
            , 'GRADE_POINTS' => ""
            , 'GRADING' => ""
            , 'IS_FAIL' => ""
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
                    , 'IS_MANUAL'
                    , 'SUBJECT_VALUE_REPEAT'
                    , 'RANK'
                    , 'GRADE_POINTS'
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
                            $SELECTION_B = array("" . $GRADING_TYPE . " AS GRADING", "IS_FAIL");
                            break;
                        case 2:
                            $SELECTION_B = array('LETTER_GRADE AS GRADING', "IS_FAIL");
                            break;
                    }
                } else {
                    $SELECTION_B = array("" . $GRADING_TYPE . " AS GRADING", "IS_FAIL");
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

                        if ($staticTerm) {
                            $SQL->where("A.TERM = '" . $staticTerm . "'");
                        } else {
                            if (isset($stdClass->term))
                                $SQL->where("A.TERM = '" . $stdClass->term . "'");
                        }

                        break;
                }

                $SQL->where("A.SECTION = '" . $stdClass->section . "'");
                $SQL->group("B.ID");

                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchRow($SQL);
                if ($result) {
                    $data = array(
                        'SUBJECT_VALUE' => $result->SUBJECT_VALUE
                        , 'SUBJECT_VALUE_REPEAT' => $result->SUBJECT_VALUE_REPEAT ? $result->SUBJECT_VALUE_REPEAT : "---"
                        , 'RANK' => $result->RANK ? $result->RANK : "---"
                        , 'GRADE_POINTS' => $result->GRADE_POINTS
                        , 'GRADING' => $result->GRADING ? $result->GRADING : "---"
                        , 'IS_FAIL' => $result->IS_FAIL
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
                        , 'IS_MANUAL' => $result->IS_MANUAL
                    );
                }

                return (object) $data;
            }
        }

        return (object) $data;
    }

    public static function findStudentSubjectEvaluation($stdClass) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_assessment", array("*"));
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
        }

        $SQL->where("SECTION = '" . $stdClass->section . "'");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function setActionStudentSubjectEvaluation($stdClass) {

        $studentId = (isset($stdClass->studentId)) ? $stdClass->studentId : "";
        $comment = (isset($stdClass->comment)) ? $stdClass->comment : "";
        $isManual = (isset($stdClass->isManual)) ? $stdClass->isManual : 0;
        $average = (isset($stdClass->average)) ? $stdClass->average : "";
        $oldValue = (isset($stdClass->oldValue)) ? $stdClass->oldValue : "";
        $actionRank = (isset($stdClass->actionRank)) ? $stdClass->actionRank : "";
        $term = (isset($stdClass->term)) ? $stdClass->term : "";
        $month = (isset($stdClass->month)) ? $stdClass->month : "";
        $year = (isset($stdClass->year)) ? $stdClass->year : "";
        $coeffValue = (isset($stdClass->coeffValue)) ? $stdClass->coeffValue : "";
        $scoreType = (isset($stdClass->scoreType)) ? $stdClass->scoreType : "";
        $section = (isset($stdClass->section)) ? $stdClass->section : "";

        $assessmentId = (isset($stdClass->assessmentId)) ? $stdClass->assessmentId : "";
        $monthResult = (isset($stdClass->monthResult)) ? $stdClass->monthResult : "";
        $termResult = (isset($stdClass->termResult)) ? $stdClass->termResult : "";
        $monthAssignment = (isset($stdClass->monthAssignment)) ? $stdClass->monthAssignment : "";
        $termAssignment = (isset($stdClass->termAssignment)) ? $stdClass->termAssignment : "";
        $firstResult = (isset($stdClass->firstResult)) ? $stdClass->firstResult : "";
        $secondResult = (isset($stdClass->secondResult)) ? $stdClass->secondResult : "";
        $thirdResult = (isset($stdClass->thirdResult)) ? $stdClass->thirdResult : "";
        $fourthResult = (isset($stdClass->fourthResult)) ? $stdClass->fourthResult : "";
        $creditHours = (isset($stdClass->creditHours)) ? $stdClass->creditHours : "";
        $mappingValue = (isset($stdClass->mappingValue)) ? $stdClass->mappingValue : "";
        $gradePoints = (isset($stdClass->gradePoints)) ? $stdClass->gradePoints : "";

        if ($average && $oldValue) {
            if (is_numeric($oldValue)) {
                $SAVE_DATA["SUBJECT_VALUE_REPEAT"] = $oldValue;
                $SAVE_DATA["SUBJECT_VALUE"] = $average;
            }
        } else {
            $SAVE_DATA["SUBJECT_VALUE_REPEAT"] = "";
        }

        if ($mappingValue)
            $SAVE_DATA["SUBJECT_VALUE"] = $mappingValue;
        if ($comment)
            $SAVE_DATA["TEACHER_COMMENT"] = $comment;
        if ($monthResult)
            $SAVE_DATA["MONTH_RESULT"] = $monthResult;
        if ($termResult)
            $SAVE_DATA["TERM_RESULT"] = $termResult;
        if ($monthAssignment)
            $SAVE_DATA["ASSIGNMENT_MONTH"] = $monthAssignment;
        if ($termAssignment)
            $SAVE_DATA["ASSIGNMENT_TERM"] = $termAssignment;
        if ($firstResult)
            $SAVE_DATA["FIRST_RESULT"] = $firstResult;
        if ($secondResult)
            $SAVE_DATA["SECOND_RESULT"] = $secondResult;
        if ($thirdResult)
            $SAVE_DATA["THIRD_RESULT"] = $thirdResult;
        if ($fourthResult)
            $SAVE_DATA["FOURTH_RESULT"] = $fourthResult;
        if ($creditHours)
            $SAVE_DATA["CREDIT_HOURS"] = $creditHours;
        if ($average)
            $SAVE_DATA["SUBJECT_VALUE"] = $average;
        if ($actionRank)
            $SAVE_DATA["RANK"] = $actionRank;
        if ($term)
            $SAVE_DATA["TERM"] = $term;
        if ($coeffValue)
            $SAVE_DATA["COEFF_VALUE"] = $coeffValue;
        if ($scoreType)
            $SAVE_DATA["SCORE_TYPE"] = $scoreType;
        if ($gradePoints)
            $SAVE_DATA["GRADE_POINTS"] = $gradePoints;

        if ($studentId) {

            $facette = self::findStudentSubjectEvaluation($stdClass);
            if ($facette) {

                if (!$facette->IS_MANUAL && !$isManual) {
                    $SAVE_DATA["ASSESSMENT_ID"] = $assessmentId;
                }

                if (!$facette->IS_MANUAL && $isManual) {
                    $SAVE_DATA["ASSESSMENT_ID"] = $assessmentId;
                }

                if ($facette->IS_MANUAL && $isManual) {
                    $SAVE_DATA["ASSESSMENT_ID"] = $assessmentId;
                }

                $SAVE_DATA['IS_MANUAL'] = $isManual;

                $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
                $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
                $WHERE[] = "SUBJECT_ID = '" . $stdClass->subjectId . "'";
                $WHERE[] = "SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'";

                switch ($section) {
                    case "MONTH":
                        $WHERE[] = "MONTH = '" . $month . "'";
                        $WHERE[] = "YEAR = '" . $year . "'";
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        $WHERE[] = "TERM = '" . $term . "'";
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

                if ($assessmentId) {
                    $SAVE_DATA["ASSESSMENT_ID"] = $assessmentId;
                }

                if ($month)
                    $SAVE_DATA["MONTH"] = $month;

                if ($year)
                    $SAVE_DATA["YEAR"] = $year;

                if ($term)
                    $SAVE_DATA["TERM"] = $term;

                if ($section)
                    $SAVE_DATA["SECTION"] = $section;

                if (isset($stdClass->educationSystem))
                    $SAVE_DATA["EDUCATION_SYSTEM"] = $stdClass->educationSystem;

                if ($comment)
                    $SAVE_DATA["TEACHER_COMMENT"] = $comment;

                $SAVE_DATA["ACTION_TYPE"] = "ASSESSMENT";
                $SAVE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
                $SAVE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;
                self::dbAccess()->insert("t_student_subject_assessment", $SAVE_DATA);
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

        self::dbAccess()->delete('t_student_score_date'
                , array(
            "CLASS_ID='" . $stdClass->academicId . "'"
            , "SUBJECT_ID='" . $stdClass->subjectId . "'")
        );
    }

}

?>