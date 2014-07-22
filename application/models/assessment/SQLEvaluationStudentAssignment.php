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
        $SQL->from(array('A' => 't_student_assignment'), array("A.POINTS"));
        $SQL->joinInner(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array("B.POINTS_POSSIBLE"));
        $SQL->where("A.CLASS_ID = '" . $stdClass->academicId . "'");
        $SQL->where("A.SUBJECT_ID = '" . $stdClass->subjectId . "'");
        $SQL->where("A.STUDENT_ID = '" . $stdClass->studentId . "'");
        $SQL->where("A.ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'");
        $SQL->where("A.SCORE_DATE = '" . $stdClass->date . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getAVGSubjectAssignment($stdClass, $include) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_assignment'), array("AVG(A.POINTS) AS AVG_POINTS"));
        $SQL->joinInner(array('B' => 't_assignment'), 'B.ID=A.ASSIGNMENT_ID', array("AVG(B.POINTS_POSSIBLE) AS AVG_POINTS_POSSIBLE"));
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
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getListStudentAssignmentScoreDate($stdClass, $include) {

        $SELECTION_A = array(
            "COEFF_VALUE AS COEFF_VALUE"
            , "POINTS_POSSIBLE AS POINTS_POSSIBLE"
            , "INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION"
        );

        $SELECTION_B = array(
            "ASSIGNMENT_ID"
            , "SCORE_TYPE"
            , "POINTS"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_assignment"), $SELECTION_A)
                ->joinLeft(array('B' => 't_student_assignment'), 'B.ASSIGNMENT_ID=A.ID', $SELECTION_B)
                ->where("B.CLASS_ID = '" . $stdClass->academicId . "'")
                ->where("B.SUBJECT_ID = '" . $stdClass->subjectId . "'");

        $SQL->where("B.STUDENT_ID = '" . $stdClass->studentId . "'");

        if ($stdClass->month)
            $SQL->where("B.MONTH = '" . $stdClass->month . "'");

        if ($stdClass->year)
            $SQL->where("B.YEAR = '" . $stdClass->year . "'");

        if ($stdClass->term)
            $SQL->where("B.TERM = '" . $stdClass->term . "'");

        if ($include)
            $SQL->where("A.INCLUDE_IN_EVALUATION IN (" . $include . ")");

        switch ($stdClass->evaluationType) {
            case self::EVALUATION_TYPE_NUMBER:
                switch (UserAuth::getCountryEducation()) {
                    case "DEFAULT":
                    case "COL":
                    case "KHM":
                    case "PER":
                    case "THA":
                    case "LAO":
                        $SQL->group("A.ID");
                        break;
                }
                break;
            case self::EVALUATION_TYPE_PERCENT:
                $SQL->group("A.ID");
                break;
        }
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function calculateAVGPercentageSubjectResult($stdClass, $include) {
        $SUM_VALUE = 0;
        $AVG_POINTS = 0;
        $AVG_POINTS_POSSIBLE = 0;

        $enties = self::getListStudentAssignmentScoreDate($stdClass, $include);

        if ($enties) {
            foreach ($enties as $value) {

                $stdClass->assignmentId = $value->ASSIGNMENT_ID;
                $facette = self::getAVGSubjectAssignment($stdClass, $include);

                if ($facette) {
                    $AVG_POINTS = $facette->AVG_POINTS;
                    $AVG_POINTS_POSSIBLE = $facette->AVG_POINTS_POSSIBLE;
                }

                if ($AVG_POINTS && $AVG_POINTS_POSSIBLE) {
                    $COEFF_VALUE = $value->COEFF_VALUE ? $value->COEFF_VALUE : 1;
                    $SUM_VALUE += ($AVG_POINTS / $AVG_POINTS_POSSIBLE) * 100 * ($COEFF_VALUE / 100);
                }
            }
        }

        return displayRound($SUM_VALUE);
    }

    public static function calculateAVGNumberSubjectResult($stdClass, $include) {

        $SUM_VALUE = 0;
        $SUM_COEFF_VALUE = 0;
        $OUTPUT = 0;
        $enties = self::getListStudentAssignmentScoreDate($stdClass, $include);

        if ($enties) {
            foreach ($enties as $value) {

                switch (UserAuth::getCountryEducation()) {
                    case "DEFAULT":
                    case "COL":
                    case "KHM":
                    case "PER":
                    case "THA":
                    case "LAO":
                        $stdClass->assignmentId = $value->ASSIGNMENT_ID;
                        $facette = self::getAVGSubjectAssignment($stdClass, $include);
                        $NEW_VALUE = $facette ? $facette->AVG_POINTS : 0;
                        break;
                    case "VNM":
                        $NEW_VALUE = $value->POINTS;
                        break;
                }

                $COEFF_VALUE = $value->COEFF_VALUE ? $value->COEFF_VALUE : 1;
                $VALUE = $NEW_VALUE ? $NEW_VALUE : 0;
                $SUM_VALUE += $VALUE * $COEFF_VALUE;
                $SUM_COEFF_VALUE += $COEFF_VALUE;
            }
        }

        if (is_numeric($SUM_COEFF_VALUE)) {
            if ($SUM_COEFF_VALUE) {
                $OUTPUT = displayRound($SUM_VALUE / $SUM_COEFF_VALUE);
            }
        }

        return $OUTPUT;
    }

    public static function calculatedSubjectResults($stdClass, $include) {

        $OUTPUT = "";
        switch ($stdClass->evaluationType) {
            case self::EVALUATION_TYPE_NUMBER:
                $OUTPUT = self::calculateAVGNumberSubjectResult($stdClass, $include);
                break;
            case self::EVALUATION_TYPE_PERCENT:
                $OUTPUT = self::calculateAVGPercentageSubjectResult($stdClass, $include);
                break;
        }

        return $OUTPUT;
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

        if (isset($stdClass->actionType)) {
            if ($stdClass->actionType == "IMPORT") {
                $stdClass->month = getMonthByDate($stdClass->date);
                $stdClass->year = getYearByDate($stdClass->date);
            }
        }

        if ($facette) {
            $WHERE[] = "STUDENT_ID = '" . $stdClass->studentId . "'";
            $WHERE[] = "CLASS_ID = '" . $stdClass->academicId . "'";
            $WHERE[] = "SUBJECT_ID = '" . $stdClass->subjectId . "'";
            $WHERE[] = "ASSIGNMENT_ID = '" . $stdClass->assignmentId . "'";
            $WHERE[] = "SCORE_DATE = '" . $stdClass->date . "'";

            switch ($stdClass->actionField) {
                case "SCORE":
                    $UPDATE_DATA['POINTS'] = $stdClass->actionValue;
                    $UPDATE_DATA['POINTS_REPEAT'] = "";
                    break;
                case "SCORE_REPEAT":
                    $UPDATE_DATA['POINTS'] = $stdClass->actionValue;
                    $UPDATE_DATA['POINTS_REPEAT'] = $facette->POINTS;
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
            if (isset($stdClass->term))
                $INSERT_DATA['TERM'] = $stdClass->term;

            $INSERT_DATA['SCORE_TYPE'] = $stdClass->scoreType;
            if (isset($stdClass->month))
                $INSERT_DATA['MONTH'] = $stdClass->month;
            if (isset($stdClass->year))
                $INSERT_DATA['YEAR'] = $stdClass->year;
            if (isset($stdClass->coeffValue))
                $INSERT_DATA['COEFF_VALUE'] = $stdClass->coeffValue;
            if (isset($stdClass->date))
                $INSERT_DATA['SCORE_DATE'] = $stdClass->date;
            if (isset($stdClass->include_in_valuation))
                $INSERT_DATA['INCLUDE_IN_EVALUATION'] = $stdClass->include_in_valuation;

            $INSERT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $INSERT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $INSERT_DATA['TEACHER_ID'] = Zend_Registry::get('USER')->CODE;

            if ($stdClass->term != 'TERM_ERROR') {
                self::dbAccess()->insert("t_student_assignment", $INSERT_DATA);
                self::addStudentScoreDate($stdClass);
            }
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

        if (isset($stdClass->term)) {
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
        } else {
            return 0;
        }
    }

    public static function addStudentScoreDate($stdClass) {
        $count = self::getCountTeacherScoreDate($stdClass);

        if (isset($stdClass->term)) {
            if (!$count) {
                $INSERT_DATA['CLASS_ID'] = $stdClass->academicId;
                $INSERT_DATA['SUBJECT_ID'] = $stdClass->subjectId;
                $INSERT_DATA['ASSIGNMENT_ID'] = $stdClass->assignmentId;
                $INSERT_DATA['SCORE_INPUT_DATE'] = $stdClass->date;
                $INSERT_DATA['TERM'] = $stdClass->term;
                self::dbAccess()->insert("t_student_score_date", $INSERT_DATA);
            }
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

    public static function findTerm($data, $academicId) {

        return AcademicDBAccess::getNameOfSchoolTermByDate($data, $academicId);
    }

}

?>