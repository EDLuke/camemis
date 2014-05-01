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

    public static function getCallStudentSubjectEvaluation($object) {

        $data["LETTER_GRADE_NUMBER"] = "";
        $data["LETTER_GRADE_CHAR"] = "";
        $data["SUBJECT_VALUE"] = "";
        $data["ASSESSMENT_ID"] = "";

        if (isset($object->studentId)) {
            if ($object->studentId) {
                $SELECTION_A = Array('SUBJECT_VALUE', 'RANK', 'ASSESSMENT_ID', 'TEACHER_COMMENT');
                $SELECTION_B = Array('DESCRIPTION', 'LETTER_GRADE');

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => "t_student_subject_assessment"), $SELECTION_A);
                $SQL->joinInner(array('B' => 't_gradingsystem'), 'A.ASSESSMENT_ID=B.ID', $SELECTION_B);
                $SQL->where("A.STUDENT_ID = '" . $object->studentId . "'");
                $SQL->where("A.SUBJECT_ID = '" . $object->subjectId . "'");
                $SQL->where("A.CLASS_ID = '" . $object->academicId . "'");
                $SQL->where("A.SCHOOLYEAR_ID = '" . $object->schoolyearId . "'");

                switch ($object->section) {
                    case "MONTH":
                        if ($object->month)
                            $SQL->where("A.MONTH = '" . $object->month . "'");

                        if ($object->year) {
                            $SQL->where("A.YEAR = '" . $object->year . "'");
                        }
                        break;
                    case "TERM":
                    case "QUARTER":
                    case "SEMESTER":
                        if ($object->term)
                            $SQL->where("A.TERM = '" . $object->term . "'");
                        break;
                    case "YEAR":
                        if ($object->year) {
                            $SQL->where("A.YEAR = '" . $object->year . "'");
                        }
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

    public static function checkStudentSubjectEvaluation($object) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_subject_assessment", array("C" => "COUNT(*)"));
        $SQL->where("STUDENT_ID = '" . $object->studentId . "'");
        $SQL->where("SUBJECT_ID = '" . $object->subjectId . "'");
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

    public static function setActionStudentSubjectEvaluation($object) {

        if (isset($object->studentId)) {
            if ($object->studentId) {

                if (self::checkStudentSubjectEvaluation($object)) {

                    $WHERE[] = "STUDENT_ID = '" . $object->studentId . "'";
                    $WHERE[] = "CLASS_ID = '" . $object->academicId . "'";
                    $WHERE[] = "SUBJECT_ID = '" . $object->subjectId . "'";
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

                    if (isset($object->subjectValue)) {
                        if ($object->subjectValue)
                            $UPDATE_DATA["SUBJECT_VALUE"] = $object->subjectValue;
                    }

                    if (isset($object->actionType)) {
                        if ($object->actionType)
                            $UPDATE_DATA["ACTION_TYPE"] = $object->actionType;
                    }

                    if (isset($object->assessmentId))
                        $UPDATE_DATA["ASSESSMENT_ID"] = $object->assessmentId;

                    if (isset($object->actionRank)) {
                        if ($object->actionRank)
                            $UPDATE_DATA["RANK"] = $object->actionRank;
                    }

                    $UPDATE_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
                    $UPDATE_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;
                    self::dbAccess()->update('t_student_subject_assessment', $UPDATE_DATA, $WHERE);
                } else {

                    $INSERT_DATA["STUDENT_ID"] = $object->studentId;
                    $INSERT_DATA["SUBJECT_ID"] = $object->subjectId;
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

                    if (isset($object->actionRank))
                        $INSERT_DATA["RANK"] = $object->actionRank;

                    if (isset($object->subjectValue))
                        $INSERT_DATA["SUBJECT_VALUE"] = $object->subjectValue;

                    if (isset($object->actionType))
                        $INSERT_DATA["SUBJECT_VALUE"] = $object->actionType;

                    if (isset($object->assessmentId))
                        $INSERT_DATA["ASSESSMENT_ID"] = $object->assessmentId;

                    $INSERT_DATA["ACTION_TYPE"] = "ASSESSMENT";
                    $INSERT_DATA['PUBLISHED_DATE'] = getCurrentDBDateTime();
                    $INSERT_DATA['PUBLISHED_BY'] = Zend_Registry::get('USER')->CODE;

                    self::dbAccess()->insert("t_student_subject_assessment", $INSERT_DATA);
                }
            }
        }
    }

}

?>