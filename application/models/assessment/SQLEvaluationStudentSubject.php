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

    public static function getCallStudentSubjectEvaluation($studentId, $classId, $subjectId, $term, $month, $year, $section)
    {

        $SELECTION_A = Array('SUBJECT_VALUE', 'RANK', 'TEACHER_COMMENT');
        $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("A.CLASS_ID = '" . $classId . "'");

        if ($section)
        {
            $SQL->where("A.SECTION = '" . $section . "'");
        }
        else
        {
            if ($month)
                $SQL->where("A.MONTH = '" . $month . "'");

            if ($year)
                $SQL->where("A.YEAR = '" . $year . "'");

            if ($term)
                $SQL->where("A.TERM = '" . $term . "'");
        }

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        if ($result)
        {
            $data["LETTER_GRADE_NUMBER"] = $result->DESCRIPTION;
            $data["LETTER_GRADE_CHAR"] = $result->LETTER_GRADE;
            $data["SUBJECT_VALUE"] = $result->SUBJECT_VALUE;
        }
        else
        {
            $data["LETTER_GRADE_NUMBER"] = "";
            $data["LETTER_GRADE_CHAR"] = "";
            $data["SUBJECT_VALUE"] = "";
        }

        return (object) $data;
    }

}

?>