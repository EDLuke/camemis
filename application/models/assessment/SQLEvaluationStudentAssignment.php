<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

class SQLEvaluationStudentAssignment {

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return false::dbAccess()->select();
    }

    public static function getScoreSubjectAssignment($object)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_assignment", array("*"))
                ->where("CLASS_ID = '" . $object->academicId . "'")
                ->where("SUBJECT_ID = '" . $object->subjectId . "'")
                ->where("STUDENT_ID = '" . $object->studentId . "'")
                ->where("ASSIGNMENT_ID = '" . $object->assignmentId . "'")
                ->where("SCORE_DATE = '" . $object->date . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getAverageSubjectAssignment($studentId, $academicId, $subjectId, $assignmentId, $term, $month, $year, $include)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_assignment'), array("AVG(POINTS) AS AVG"))
                ->joinInner(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array())
                ->where("A.CLASS_ID = '" . $academicId . "'")
                ->where("A.SUBJECT_ID = '" . $subjectId . "'")
                ->where("A.STUDENT_ID = '" . $studentId . "'")
                ->where("A.ASSIGNMENT_ID = '" . $assignmentId . "'")
                ->where("A.SCORE_TYPE = '1'");

        if ($month)
            $SQL->where("A.MONTH = '" . $month . "'");

        if ($year)
            $SQL->where("A.YEAR = '" . $year . "'");

        if ($term)
            $SQL->where("A.TERM = '" . $term . "'");

        $SQL->where("B.INCLUDE_IN_EVALUATION = '" . $include . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->AVG : "";
    }

    public static function getListStudentAssignmentScoreDate($studentId, $academicId, $subjectId, $term, $month, $year, $include)
    {
        $SELECTION_A = array("ASSIGNMENT_ID");

        $SELECTION_B = array(
            "COEFF_VALUE AS COEFF_VALUE"
            , "INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_student_assignment"), $SELECTION_A)
                ->joinLeft(array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', $SELECTION_B)
                ->where("A.CLASS_ID = '" . $academicId . "'")
                ->where("A.SUBJECT_ID = '" . $subjectId . "'");

        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");

        if ($month)
            $SQL->where("A.MONTH = '" . $month . "'");

        if ($year)
            $SQL->where("A.YEAR = '" . $year . "'");

        if ($term)
            $SQL->where("A.TERM = '" . $term . "'");

        if ($include)
            $SQL->where("B.INCLUDE_IN_EVALUATION = '" . $include . "'");

        $SQL->group("A.ASSIGNMENT_ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function calculatedAverageSubjectResult($studentId, $academicId, $subjectId, $term, $month, $year, $include)
    {
        $SUM_VALUE = "";
        $SUM_COEFF_VALUE = "";
        $output = "";

        $enties = self::getListStudentAssignmentScoreDate(
                        $studentId
                        , $academicId
                        , $subjectId
                        , $term
                        , $month
                        , $year
                        , $include);

        if ($enties)
        {
            foreach ($enties as $value)
            {

                $_VALUE = self::getAverageSubjectAssignment(
                                $studentId
                                , $academicId
                                , $subjectId
                                , $value->ASSIGNMENT_ID
                                , $term
                                , $month
                                , $year
                                , $include);

                $COEFF_VALUE = $value->COEFF_VALUE ? $value->COEFF_VALUE : 1;
                $VALUE = $_VALUE ? $_VALUE : 0;
                $SUM_VALUE += $VALUE * $COEFF_VALUE;
                $SUM_COEFF_VALUE += $COEFF_VALUE;
            }
        }

        if (is_numeric($SUM_COEFF_VALUE))
        {
            if ($SUM_COEFF_VALUE)
            {
                $output = displayRound($SUM_VALUE / $SUM_COEFF_VALUE);
            }
            else
            {
                $output = 0;
            }
        }
        else
        {
            $output = 0;
        }

        return $output;
    }

    public static function getImplodeQuerySubjectAssignment($studentId, $academicId, $subjectId, $assignmentId, $term, $month, $year, $include)
    {

        $object = (object) array(
                    "studentId" => $studentId
                    , "academicId" => $academicId
                    , "subjectId" => $subjectId
                    , "assignmentId" => $assignmentId
                    , "term" => $term
                    , "month" => $month
                    , "year" => $year
                    , "include_in_evaluation" => $include
        );

        $result = self::getQueryStudentSubjectAssignments($object);

        $data = array();

        if ($result)
        {
            foreach ($result as $value)
            {
                $data[] = $value->POINTS;
            }
        }

        return $data ? implode("|", $data) : "---";
    }

    public static function getQueryStudentSubjectAssignments($object)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_assignment'), array("*"));
        $SQL->joinInner(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array('NAME AS ASSIGNMENT'));
        $SQL->where("A.CLASS_ID = '" . $object->academicId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $object->subjectId . "'");
        $SQL->where("A.STUDENT_ID = '" . $object->studentId . "'");

        if (isset($object->assignmentId))
        {
            if ($object->assignmentId)
                $SQL->where("A.ASSIGNMENT_ID = '" . $object->assignmentId . "'");
        }

        if (isset($object->month))
        {
            if ($object->month)
                $SQL->where("A.MONTH = '" . $object->month . "'");
        }

        if (isset($object->year))
        {
            if ($object->year)
                $SQL->where("A.YEAR = '" . $object->year . "'");
        }

        if (isset($object->term))
        {
            if ($object->term)
                $SQL->where("A.TERM = '" . $object->term . "'");
        }


        if (isset($object->include_in_evaluation))
        {
            if ($object->include_in_evaluation)
                $SQL->where("B.INCLUDE_IN_EVALUATION = '" . $object->include_in_evaluation . "'");
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function setActionStudentScoreSubjectAssignment($object)
    {

        $facette = self::getScoreSubjectAssignment($object);

        if ($facette)
        {
            $WHERE[] = "STUDENT_ID = '" . $object->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $object->academicId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $object->subjectId . "'";
            $WHERE[] = "ASSIGNMENT_ID = '" . $object->assignmentId . "'";
            $WHERE[] = "SCORE_DATE = '" . $object->date . "'";

            switch ($object->actionField)
            {
                case "SCORE":
                    $UPDATE_DATA['POINTS'] = $object->actionValue;
                    break;
                case "TEACHER_COMMENTS":
                    $UPDATE_DATA['TEACHER_COMMENTS'] = $object->actionValue;
                    break;
            }

            $UPDATE_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $UPDATE_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->update('t_student_assignment', $UPDATE_DATA, $WHERE);
        }
        else
        {

            switch ($object->actionField)
            {
                case "SCORE":
                    $INSERT_DATA['POINTS'] = $object->actionValue;
                    break;
                case "TEACHER_COMMENTS":
                    $INSERT_DATA['TEACHER_COMMENTS'] = $object->actionValue;
                    break;
            }

            $INSERT_DATA['STUDENT_ID'] = $object->studentId;
            $INSERT_DATA['CLASS_ID'] = $object->academicId;
            $INSERT_DATA['SUBJECT_ID'] = $object->subjectId;
            $INSERT_DATA['ASSIGNMENT_ID'] = $object->assignmentId;
            $INSERT_DATA['TERM'] = $object->term;

            $INSERT_DATA['SCORE_TYPE'] = $object->scoreType;
            $INSERT_DATA['MONTH'] = $object->month;
            $INSERT_DATA['YEAR'] = $object->year;
            $INSERT_DATA['COEFF_VALUE'] = $object->coeffValue;
            $INSERT_DATA['SCORE_DATE'] = $object->date;
            $INSERT_DATA['INCLUDE_IN_EVALUATION'] = $object->include_in_valuation;

            $INSERT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $INSERT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $INSERT_DATA['TEACHER_ID'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert("t_student_assignment", $INSERT_DATA);
            self::addStudentScoreDate($object);
        }
    }

    public static function getCountTeacherScoreDate($object)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", array("C" => "COUNT(*)"))
                ->where("CLASS_ID = '" . $object->academicId . "'")
                ->where("SUBJECT_ID = '" . $object->subjectId . "'")
                ->where("TERM = '" . $object->term . "'")
                ->where("ASSIGNMENT_ID = '" . $object->assignmentId . "'")
                ->where("SCORE_INPUT_DATE = '" . $object->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function addStudentScoreDate($object)
    {
        $count = self::getCountTeacherScoreDate($object);

        if (!$count)
        {
            $INSERT_DATA['CLASS_ID'] = $object->academicId;
            $INSERT_DATA['SUBJECT_ID'] = $object->subjectId;
            $INSERT_DATA['ASSIGNMENT_ID'] = $object->assignmentId;
            $INSERT_DATA['SCORE_INPUT_DATE'] = $object->date;
            $INSERT_DATA['TERM'] = $object->term;
            self::dbAccess()->insert("t_student_score_date", $INSERT_DATA);
        }
    }

    public static function getActionDeleteAllStudentsTeacherScoreEnter($object)
    {
        self::dbAccess()->delete('t_student_assignment'
                , array(
            "CLASS_ID='" . $object->academicId . "'"
            , "SUBJECT_ID='" . $object->subjectId . "'"
            , "ASSIGNMENT_ID='" . $object->assignmentId . "'"
            , "TERM='" . $object->term . "'"
            , "SCORE_DATE='" . $object->date . "'"
                )
        );
    }

    public static function getActionDeleteOneStudentTeacherScoreEnter($object)
    {
        self::dbAccess()->delete('t_student_assignment'
                , array(
            "STUDENT_ID='" . $object->studentId . "'"
            , "CLASS_ID='" . $object->academicId . "'"
            , "SUBJECT_ID='" . $object->subjectId . "'"
            , "ASSIGNMENT_ID='" . $object->assignmentId . "'"
            , "TERM='" . $object->term . "'"
            , "SCORE_DATE='" . $object->date . "'"
                )
        );
    }

}

?>