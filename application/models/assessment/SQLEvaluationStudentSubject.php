<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

class SQLEvaluationStudentSubject {

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return false::dbAccess()->select();
    }

    public static function getCallStudentSubjectEvaluation($stdClass)
    {
        $data["LETTER_GRADE_NUMBER"] = "---";
        $data["LETTER_GRADE_CHAR"] = "---";
        $data["SUBJECT_VALUE"] = "---";
        $data["ASSESSMENT_ID"] = "---";
        $data["TEACHER_COMMENT"] = "---";
        $data["RANK"] = "---";
        
        if (isset($stdClass->studentId))
        {
            if ($stdClass->studentId)
            {
                $SELECTION_A = array('SUBJECT_VALUE', 'RANK', 'ASSESSMENT_ID', 'TEACHER_COMMENT');
                $SELECTION_B = array('DESCRIPTION', 'LETTER_GRADE');

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
                $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
                $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");
                $SQL->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");
                $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
                $SQL->where("A.SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

                switch ($stdClass->section)
                {
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
                if ($result)
                {
                    $data["LETTER_GRADE_NUMBER"] = $result->DESCRIPTION;
                    $data["LETTER_GRADE_CHAR"] = $result->LETTER_GRADE;
                    $data["SUBJECT_VALUE"] = $result->SUBJECT_VALUE;
                    $data["TEACHER_COMMENT"] = $result->TEACHER_COMMENT;
                    $data["ASSESSMENT_ID"] = $result->ASSESSMENT_ID;
                    $data["RANK"] = $result->RANK ? $result->RANK : "---";
                }
                else
                {
                    $data["LETTER_GRADE_NUMBER"] = "---";
                    $data["LETTER_GRADE_CHAR"] = "---";
                    $data["SUBJECT_VALUE"] = "---";
                    $data["ASSESSMENT_ID"] = "---";
                    $data["TEACHER_COMMENT"] = "---";
                    $data["RANK"] = "---";
                }
            }
        }

        return (object) $data;
    }

    public static function checkStudentSubjectEvaluation($stdClass)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_assessment", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->where("SUBJECT_ID = '" . $stdClass->subjectId . "'");
        $SQL->where("CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $stdClass->schoolyearId . "'");

        switch ($stdClass->section)
        {
            case "MONTH":
                if (isset($stdClass->month))
                    $SQL->where("MONTH = '" . $stdClass->month . "'");

                if (isset($stdClass->year))
                {
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

    public static function setActionStudentSubjectEvaluation($stdClass)
    {
        if (isset($stdClass->studentId))
        {
            if ($stdClass->studentId)
            {

                if (self::checkStudentSubjectEvaluation($stdClass))
                {

                    $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
                    $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
                    $WHERE[] = "SUBJECT_ID = '" . $stdClass->subjectId . "'";
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

                    if (isset($stdClass->actionValue))
                    {
                        if ($stdClass->actionValue)
                            $UPDATE_DATA["SUBJECT_VALUE"] = $stdClass->actionValue;
                    }

                    if (isset($stdClass->assessmentId))
                        $UPDATE_DATA["ASSESSMENT_ID"] = $stdClass->assessmentId;

                    if (isset($stdClass->actionRank))
                    {
                        if ($stdClass->actionRank)
                            $UPDATE_DATA["RANK"] = $stdClass->actionRank;
                    }

                    if (isset($stdClass->comment))
                        $UPDATE_DATA["TEACHER_COMMENT"] = $stdClass->comment;

                    $UPDATE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
                    $UPDATE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;
                    self::dbAccess()->update('t_student_subject_assessment', $UPDATE_DATA, $WHERE);
                } else
                {

                    $INSERT_DATA["STUDENT_ID"] = $stdClass->studentId;
                    $INSERT_DATA["SUBJECT_ID"] = $stdClass->subjectId;
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

                    if (isset($stdClass->actionRank))
                        $INSERT_DATA["RANK"] = $stdClass->actionRank;

                    if (isset($stdClass->actionValue))
                        $INSERT_DATA["SUBJECT_VALUE"] = $stdClass->actionValue;

                    if (isset($stdClass->assessmentId))
                        $INSERT_DATA["ASSESSMENT_ID"] = $stdClass->assessmentId;

                    if (isset($stdClass->comment))
                        $INSERT_DATA["TEACHER_COMMENT"] = $stdClass->comment;

                    $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
                    $INSERT_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
                    $INSERT_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;

                    self::dbAccess()->insert("t_student_subject_assessment", $INSERT_DATA);
                }
            }
        }
    }

    public static function getActionDeleteSubjectScoreAssessment($stdClass)
    {
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