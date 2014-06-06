<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.04.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

class SQLEvaluationStudentAssignment {

    CONST EVALUATION_TYPE_NUMBER = 0;
    CONST EVALUATION_TYPE_PERCENT = 1;

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return false::dbAccess()->select();
    }

    public static function getScoreSubjectAssignment($stdClass) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_assignment", array("*"));
        $SQL->where("CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("SUBJECT_ID = '" . $stdClass->subjectId . "'");
        $SQL->where("STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->where("ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'");
        $SQL->where("SCORE_DATE = '" . $stdClass->date . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getAverageSubjectAssignment($stdClass, $include) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_assignment'), array("AVG(POINTS) AS AVG"));
        $SQL->joinInner(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array());
        $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");
        $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");
        if (isset($stdClass->assignmentId))
            $SQL->where("A.ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'");
        $SQL->where("A.SCORE_TYPE = '1'");

        if ($stdClass->month)
            $SQL->where("A.MONTH = '" . $stdClass->month . "'");

        if ($stdClass->year)
            $SQL->where("A.YEAR = '" . $stdClass->year . "'");

        if ($stdClass->term)
            $SQL->where("A.TERM = '" . $stdClass->term . "'");

        if ($include)
            $SQL->where("B.INCLUDE_IN_EVALUATION IN (" . $include . ")");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->AVG : "";
    }

    public static function getListStudentAssignmentScoreDate($stdClass, $include) {
        $SELECTION_A = array("ASSIGNMENT_ID");

        $SELECTION_B = array(
            "COEFF_VALUE AS COEFF_VALUE"
            , "INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_student_assignment"), $SELECTION_A)
                ->joinLeft(array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', $SELECTION_B)
                ->where("A.CLASS_ID = '" . $stdClass->academicId . "'")
                ->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");

        $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");

        if ($stdClass->month)
            $SQL->where("A.MONTH = '" . $stdClass->month . "'");

        if ($stdClass->year)
            $SQL->where("A.YEAR = '" . $stdClass->year . "'");

        if ($stdClass->term)
            $SQL->where("A.TERM = '" . $stdClass->term . "'");

        if ($include)
            $SQL->where("B.INCLUDE_IN_EVALUATION IN (" . $include . ")");

        $SQL->group("A.ASSIGNMENT_ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function calculatedPercentageAverageSubjectResult($stdClass, $include) {
        $SUM_VALUE = 0;
        $enties = self::getListStudentAssignmentScoreDate($stdClass, $include);
        if ($enties) {
            foreach ($enties as $value) {

                $stdClass->assignmentId = $value->ASSIGNMENT_ID;
                $_VALUE = self::getAverageSubjectAssignment($stdClass, $include);

                $COEFF_VALUE = $value->COEFF_VALUE ? $value->COEFF_VALUE : 1;
                $VALUE = $_VALUE ? $_VALUE : 0;
                $SUM_VALUE += ($VALUE * $COEFF_VALUE) / 100;
            }
        }

        return displayRound($SUM_VALUE);
    }

    public static function calulateNumberAverageSubjectResult($stdClass, $include) {
        $SUM_VALUE = 0;
        $SUM_COEFF_VALUE = 0;
        $output = 0;
        $enties = self::getListStudentAssignmentScoreDate($stdClass, $include);

        if ($enties) {
            foreach ($enties as $value) {

                $stdClass->assignmentId = $value->ASSIGNMENT_ID;
                $_VALUE = self::getAverageSubjectAssignment($stdClass, $include);

                $COEFF_VALUE = $value->COEFF_VALUE ? $value->COEFF_VALUE : 1;
                $VALUE = $_VALUE ? $_VALUE : 0;
                $SUM_VALUE += $VALUE * $COEFF_VALUE;
                $SUM_COEFF_VALUE += $COEFF_VALUE;
            }
        }

        if (is_numeric($SUM_COEFF_VALUE)) {
            if ($SUM_COEFF_VALUE) {
                $output = displayRound($SUM_VALUE / $SUM_COEFF_VALUE);
            }
        }

        return $output;
    }

    public static function calculatedAverageSubjectResult($stdClass, $include) {

        $output = "";
        switch ($stdClass->evaluationType) {
            case self::EVALUATION_TYPE_NUMBER:
                $output = self::calulateNumberAverageSubjectResult($stdClass, $include);
                break;
            case self::EVALUATION_TYPE_PERCENT:
                if (self::CheckComplexPercentage($stdClass)) {
                    $output = self::calculatedPercentageAverageSubjectResult($stdClass, $include);
                } else {
                    $output = self::calulateNumberAverageSubjectResult($stdClass, $include);
                }
                break;
        }

        return $output;
    }

    public static function getImplodeQuerySubjectAssignment($stdClass, $include) {

        $result = self::getQueryStudentSubjectAssignments($stdClass, $include);

        $data = array();

        if ($result) {
            foreach ($result as $value) {
                $data[] = $value->POINTS;
            }
        }

        return $data ? implode("|", $data) : "---";
    }

    public static function getQueryStudentSubjectAssignments($stdClass, $include = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_assignment'), array("*"));
        $SQL->joinLeft(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array('NAME AS ASSIGNMENT'));
        $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");
        $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");

        if (isset($stdClass->assignmentId)) {
            if ($stdClass->assignmentId)
                $SQL->where("A.ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'");
        }

        if (isset($stdClass->month)) {
            if ($stdClass->month)
                $SQL->where("A.MONTH = '" . $stdClass->month . "'");
        }

        if (isset($stdClass->year)) {
            if ($stdClass->year)
                $SQL->where("A.YEAR = '" . $stdClass->year . "'");
        }

        if (isset($stdClass->term)) {
            if ($stdClass->term)
                $SQL->where("A.TERM = '" . $stdClass->term . "'");
        }

        if ($include)
            $SQL->where("B.INCLUDE_IN_EVALUATION IN (" . $include . ")");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function setActionStudentScoreSubjectAssignment($stdClass) {

        $facette = self::getScoreSubjectAssignment($stdClass);

        if ($facette) {
            $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $stdClass->subjectId . "'";
            $WHERE[] = "ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'";
            $WHERE[] = "SCORE_DATE = '" . $stdClass->date . "'";

            switch ($stdClass->actionField) {
                case "SCORE":
                    $UPDATE_DATA['POINTS'] = $stdClass->actionValue;
                    break;
                case "TEACHER_COMMENTS":
                    $UPDATE_DATA['TEACHER_COMMENTS'] = $stdClass->actionValue;
                    break;
            }

            $UPDATE_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $UPDATE_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->update('t_student_assignment', $UPDATE_DATA, $WHERE);
        } else {

            switch ($stdClass->actionField) {
                case "SCORE":
                    $INSERT_DATA['POINTS'] = $stdClass->actionValue;
                    break;
                case "TEACHER_COMMENTS":
                    $INSERT_DATA['TEACHER_COMMENTS'] = $stdClass->actionValue;
                    break;
            }

            $INSERT_DATA['STUDENT_ID'] = $stdClass->studentId;
            $INSERT_DATA['CLASS_ID'] = $stdClass->academicId;
            $INSERT_DATA['SUBJECT_ID'] = $stdClass->subjectId;
            $INSERT_DATA['ASSIGNMENT_ID'] = $stdClass->assignmentId;
            $INSERT_DATA['TERM'] = $stdClass->term;

            $INSERT_DATA['SCORE_TYPE'] = $stdClass->scoreType;
            $INSERT_DATA['MONTH'] = $stdClass->month;
            $INSERT_DATA['YEAR'] = $stdClass->year;
            $INSERT_DATA['COEFF_VALUE'] = $stdClass->coeffValue;
            $INSERT_DATA['SCORE_DATE'] = $stdClass->date;
            $INSERT_DATA['INCLUDE_IN_EVALUATION'] = $stdClass->include_in_valuation;

            $INSERT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $INSERT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $INSERT_DATA['TEACHER_ID'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert("t_student_assignment", $INSERT_DATA);
            self::addStudentScoreDate($stdClass);
        }
    }

    public static function checkExistStudentSubjectAssignmentByYear($stdClass) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_assignment", array("C" => "COUNT(*)"));
        $SQL->where("CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->group("SUBJECT_ID");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkExistStudentSubjectAssignment($stdClass, $include = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_assignment'), array("C" => "COUNT(*)"));
        $SQL->joinInner(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array('NAME AS ASSIGNMENT'));
        $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");

        if (isset($stdClass->month)) {
            if ($stdClass->month)
                $SQL->where("A.MONTH = '" . $stdClass->month . "'");
        }

        if (isset($stdClass->year)) {
            if ($stdClass->year)
                $SQL->where("A.YEAR = '" . $stdClass->year . "'");
        }

        if (isset($stdClass->term)) {
            if ($stdClass->term)
                $SQL->where("A.TERM = '" . $stdClass->term . "'");
        }

        if ($include)
            $SQL->where("B.INCLUDE_IN_EVALUATION IN (" . $include . ")");

        if (isset($stdClass->subjectId)) {
            if ($stdClass->subjectId)
                $SQL->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");
        }

        $SQL->group("A.SUBJECT_ID");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getCountTeacherScoreDate($stdClass) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", array("C" => "COUNT(*)"))
                ->where("CLASS_ID = '" . $stdClass->academicId . "'")
                ->where("SUBJECT_ID = '" . $stdClass->subjectId . "'")
                ->where("TERM = '" . $stdClass->term . "'")
                ->where("ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'")
                ->where("SCORE_INPUT_DATE = '" . $stdClass->date . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function addStudentScoreDate($stdClass) {
        $count = self::getCountTeacherScoreDate($stdClass);

        if (!$count) {
            $INSERT_DATA['CLASS_ID'] = $stdClass->academicId;
            $INSERT_DATA['SUBJECT_ID'] = $stdClass->subjectId;
            $INSERT_DATA['ASSIGNMENT_ID'] = $stdClass->assignmentId;
            $INSERT_DATA['SCORE_INPUT_DATE'] = $stdClass->date;
            $INSERT_DATA['TERM'] = $stdClass->term;
            self::dbAccess()->insert("t_student_score_date", $INSERT_DATA);
        }
    }

    public static function getActionDeleteAllStudentsTeacherScoreEnter($stdClass) {

        $SQL = "DELETE FROM t_student_assignment WHERE";
        $SQL .= " CLASS_ID='" . $stdClass->academicId . "'";
        $SQL .= " AND SUBJECT_ID='" . $stdClass->subjectId . "'";
        $SQL .= " AND ASSIGNMENT_ID='" . $stdClass->assignmentId . "'";
        $SQL .= " AND SCORE_DATE='" . $stdClass->date . "'";
        self::dbAccess()->query($SQL);
    }

    public static function getActionDeleteOneStudentTeacherScoreEnter($stdClass) {

        $SQL = "DELETE FROM t_student_assignment WHERE";
        $SQL .= " CLASS_ID='" . $stdClass->academicId . "'";
        $SQL .= " AND STUDENT_ID='" . $stdClass->studentId . "'";
        $SQL .= " AND SUBJECT_ID='" . $stdClass->subjectId . "'";
        $SQL .= " AND ASSIGNMENT_ID='" . $stdClass->assignmentId . "'";
        $SQL .= " AND SCORE_DATE='" . $stdClass->date . "'";
        self::dbAccess()->query($SQL);
    }

    public static function getAcitonSubjectAssignmentModifyScoreDate($stdClass) {
        $setIds = explode("_", $stdClass->setId);
        $assignmentId = isset($setIds[0]) ? $setIds[0] : "";
        $olddate = isset($setIds[1]) ? $setIds[1] : "";

        $TERM_NAME = AcademicDBAccess::getNameOfSchoolTermByDate(
                        $stdClass->modify_date
                        , $stdClass->academicId);

        $CHECK_ERROR = ($TERM_NAME == "TERM_ERROR") ? true : false;

        $ACTION_ERROR = true;
        if (!$CHECK_ERROR && $olddate) {
            $ACTION_ERROR = false;
            $date = new DateTime($stdClass->modify_date);
            $FIRST = "UPDATE t_student_assignment";
            $FIRST .= " SET";
            $FIRST .= " TERM='" . $TERM_NAME . "'";
            $FIRST .= " ,SCORE_DATE='" . setDate2DB($stdClass->modify_date) . "'";
            $FIRST .= " ,MONTH='" . $date->format('m') . "'";
            $FIRST .= " ,YEAR='" . $date->format('Y') . "'";
            $FIRST .= " WHERE";
            $FIRST .= " ASSIGNMENT_ID = '" . $assignmentId . "'";
            $FIRST .= " AND SUBJECT_ID = '" . $stdClass->subjectId . "'";
            $FIRST .= " AND CLASS_ID = '" . $stdClass->academicId . "'";
            $FIRST .= " AND SCORE_DATE = '" . $olddate . "'";
            self::dbAccess()->query($FIRST);

            $SECOND = "UPDATE t_student_score_date";
            $SECOND .= " SET";
            $SECOND .= " TERM='" . $TERM_NAME . "'";
            $SECOND .= " ,SCORE_INPUT_DATE='" . setDate2DB($stdClass->modify_date) . "'";
            $SECOND .= " WHERE";
            $SECOND .= " ASSIGNMENT_ID = '" . $assignmentId . "'";
            $SECOND .= " AND SUBJECT_ID = '" . $stdClass->subjectId . "'";
            $SECOND .= " AND CLASS_ID = '" . $stdClass->academicId . "'";
            $SECOND .= " AND SCORE_INPUT_DATE = '" . $olddate . "'";
            self::dbAccess()->query($SECOND);
        }
    }

    public static function getActionContentTeacherScoreInputDate($stdClass) {

        if ($stdClass->setId && $stdClass->content) {
            $SAVEDATA['CONTENT'] = $stdClass->content;
            $WHERE[] = "ID = '" . $stdClass->setId . "'";
            self::dbAccess()->update('t_student_score_date', $SAVEDATA, $WHERE);
        }
    }

    public static function findScoreInputDate($stdClass) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => "t_student_score_date"), array("SCORE_INPUT_DATE", "CONTENT"));
        $SQL->joinLeft(array('B' => 't_assignment'), 'A.ASSIGNMENT_ID=B.ID', array("SHORT", "NAME"));
        $SQL->where("A.ID = '" . $stdClass->setId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function CheckComplexPercentage($stdClass) {
        $calculate = 0;
        $entries = self::getListStudentAssignmentScoreDate($stdClass);
        if ($entries) {
            foreach ($entries as $value) {
                if ($value->COEFF_VALUE) {
                    $calculate +=$value->COEFF_VALUE;
                }
            }
        }
        return ($calculate == 100) ? true : false;
    }

}

?>