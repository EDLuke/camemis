<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.10.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

class AssessmentConfig {

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function getGradePointsById($Id) {
        return self::makeGrade($Id, "GRADE_POINTS");
    }

    public static function makeGrade($Id, $type = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("ID = ?", $Id);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        if ($type) {
            return $result ? $result->$type : "---";
        } else {
            return $result ? $result->DESCRIPTION : "---";
        }
    }

    public static function getListGradingScales($scoreType, $qualificationType) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '" . $scoreType . "'");
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getSQLGradingScale($score, $scoreType, $qualificationType, $all) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '" . $scoreType . "'");
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");

        if ($all) {
            //error_log($SQL->__toString());
            return self::dbAccess()->fetchAll($SQL);
        } else {
            $SQL->where("LETTER_GRADE = '" . $score . "'");
            //error_log($SQL->__toString());
            return self::dbAccess()->fetchRow($SQL);
        }
    }

    public static function findGradingAlphabet($score, $qualificationType) {
        $result = self::getSQLGradingScale($score, 2, $qualificationType, false);
        return $result ? $result->NUMERIC_GRADE : "";
    }

    public static function findAlphabetGradingScaleId($score, $qualificationType) {

        $result = self::getSQLGradingScale($score, 2, $qualificationType, false);
        return $result ? $result->ID : "";
    }

    public static function findGradingScaleId($score, $qualificationType) {

        $result = self::getSQLGradingScale($score, 1, $qualificationType, true);

        $make = "?";
        if ($result) {
            foreach ($result as $value) {
                if (number_is_between($score, $value->SCORE_MIN, $value->SCORE_MAX)) {
                    $make = $value->ID;
                }
            }
        }

        if ($score) {
            return $make;
        } else {
            return "";
        }
    }

    public static function findGrading($score, $qualificationType) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '1'");
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $make = "?";
        if ($result) {
            foreach ($result as $value) {
                if (number_is_between($score, $value->SCORE_MIN, $value->SCORE_MAX)) {
                    $make = $value->DESCRIPTION;
                }
            }
        }

        if ($score) {
            return $make;
        } else {
            return "";
        }
    }

    public static function findRank($scoreList, $checkSchore) {

        $position = 0;
        $result = count($scoreList);

        if (is_numeric($checkSchore)) {

            if ($scoreList) {
                rsort($scoreList);
                if ($scoreList) {
                    foreach ($scoreList as $key => $value) {
                        if ($key) {
                            if ($value == $checkSchore) {
                                $position = $key;
                                break;
                            }
                        }
                    }
                }

                $ranks = array(1);
                for ($i = 1; $i < count($scoreList); $i++) {
                    if ($scoreList[$i] != $scoreList[$i - 1])
                        $ranks[$i] = $i + 1;
                    else
                        $ranks[$i] = $ranks[$i - 1];
                }

                $result = isset($ranks[$position]) ? $ranks[$position] : count($scoreList);
            }
        }else {
            $result = "---";
        }

        return $result;
    }

    public static function comboGPA($academicObject) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("EDUCATION_TYPE = '" . $academicObject->QUALIFICATION_TYPE . "'");
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data[0] = "{chooseValue: '0', chooseDisplay: '---'}";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "{chooseValue: '" . $value->ID . "', chooseDisplay: '" . $value->GPA . "'}";
                $i++;
            }
        }
        return implode(",", $data);
    }

    public static function comboGradingSystem($scoreType, $academicObject = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '" . $scoreType . "'");

        if ($academicObject) {
            $GRADING_TYPE = $academicObject->GRADING_TYPE ? "LETTER_GRADE" : "DESCRIPTION";
            $SQL->where("EDUCATION_TYPE = '" . $academicObject->QUALIFICATION_TYPE . "'");
        } else {
            $GRADING_TYPE = "DESCRIPTION";
            $SQL->where("EDUCATION_TYPE = 'training'");
        }

        $SQL->order("SORTKEY ASC");
        #error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data[0] = "{chooseValue: '0', chooseDisplay: '---'}";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "{chooseValue: '" . $value->ID . "', chooseDisplay: '" . $value->$GRADING_TYPE . "'}";
                $i++;
            }
        }
        return implode(",", $data);
    }

    public static function comboScoreAlphabet($qualificationType) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '2'");
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");
        $SQL->order("SORTKEY ASC");
        #error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data[0] = "{chooseValue: '0', chooseDisplay: '---'}";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "{chooseValue: '" . $value->ID . "', chooseDisplay: '" . $value->LETTER_GRADE . "'}";
                $i++;
            }
        }
        return implode(",", $data);
    }

    public static function calculateGradingScale($checkValue, $qualificationType) {

        $OUTPUT = "";
        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");
        $SQL->order("SCORE_MAX DESC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                if (number_is_between($checkValue, $value->SCORE_MIN, $value->SCORE_MAX)) {
                    $OUTPUT = $value->ID;
                    break;
                }
            }
        }

        return $OUTPUT;
    }

    ////////////////////////////////////////////////////////////////////////////
    public static function getListAssignmentScoreDate($academicId, $formTerm, $subjectId, $term, $monthyear, $isGroupBy) {

        $SQL = self::dbAccess()->select();
        $SQL->from(Array('A' => 't_assignment'), array("ID", "SHORT", "COEFF_VALUE"));
        $SQL->joinLeft(Array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array("ID AS OBJECT_ID", "SCORE_INPUT_DATE"));
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.CLASS_ID = ?", "" . $academicId . "");

        if ($term && !$monthyear) {
            if ($formTerm) {
                $SQL->where("A.INCLUDE_IN_EVALUATION IN (2,3)");
            }
            $SQL->where("B.TERM = ?", $term);
        }

        if (!$term && $monthyear) {
            $SQL->where("A.INCLUDE_IN_EVALUATION = 1");
            $SQL->where("MONTH(B.SCORE_INPUT_DATE) = ?", getMonthNumberFromMonthYear($monthyear) * 1);
            $SQL->where("YEAR(B.SCORE_INPUT_DATE) = ?", getYearFromMonthYear($monthyear) * 1);
        }
        if ($isGroupBy)
            $SQL->group("B.ASSIGNMENT_ID");
        $SQL->order('A.SORTKEY ASC');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getListAssignmentCategoryScoreDate($academicId, $formTerm, $subjectId, $term, $monthyear) {
        $SQL = self::dbAccess()->select();
        $SQL->from(Array('A' => 't_assignment'), array("INCLUDE_IN_EVALUATION"));
        $SQL->joinLeft(Array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array("ID AS OBJECT_ID", "SCORE_INPUT_DATE"));
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.CLASS_ID = ?", "" . $academicId . "");
        if ($term && !$monthyear) {
            if ($formTerm) {
                $SQL->where("A.INCLUDE_IN_EVALUATION IN (2,3)");
            }
            $SQL->where("B.TERM = ?", $term);
        }
        if (!$term && $monthyear) {
            $SQL->where("A.INCLUDE_IN_EVALUATION = 1");
            $SQL->where("MONTH(B.SCORE_INPUT_DATE) = ?", getMonthNumberFromMonthYear($monthyear) * 1);
            $SQL->where("YEAR(B.SCORE_INPUT_DATE) = ?", getYearFromMonthYear($monthyear) * 1);
        }
        $SQL->group("A.INCLUDE_IN_EVALUATION");
        $SQL->order('A.SORTKEY ASC');
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                $obj = new stdClass();
                $obj->ID = $value->INCLUDE_IN_EVALUATION;
                switch ($value->INCLUDE_IN_EVALUATION) {
                    case 1:
                        $obj->TITLE = MONTHLY;
                        break;
                    case 2:
                        $obj->TITLE = MIDDLE_SEMESTER;
                        break;
                    case 3:
                        $obj->TITLE = SEMESTER;
                        break;
                }

                $data[] = $obj;
            }
        }

        return $data;
    }

    public static function getMonthCoutScoreDate($assignmentId, $academicId, $subjectId, $monthyear) {
        $SQL = UserAuth::dbAccess()->select();
        $SQL->from(Array('A' => 't_assignment'), Array("C" => "COUNT(*)"));
        $SQL->joinLeft(Array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array());
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.CLASS_ID = ?", $academicId);
        $SQL->where("A.INCLUDE_IN_EVALUATION = 1");
        $SQL->where("MONTH(B.SCORE_INPUT_DATE) = ?", getMonthNumberFromMonthYear($monthyear) * 1);
        $SQL->where("YEAR(B.SCORE_INPUT_DATE) = ?", getYearFromMonthYear($monthyear) * 1);
        $SQL->where("B.ASSIGNMENT_ID = ?", $assignmentId);
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getTermCountScoreDate($assignmentId, $academicId, $subjectId, $term) {
        $SQL = UserAuth::dbAccess()->select();
        $SQL->from(Array('A' => 't_assignment'), Array("C" => "COUNT(*)"));
        $SQL->joinLeft(Array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array());
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.CLASS_ID = ?", $academicId);
        $SQL->where("B.TERM = ?", $term);
        $SQL->where("B.ASSIGNMENT_ID = ?", $assignmentId);
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getMonthCoutCategoryScoreDate($category, $academicId, $subjectId, $monthyear) {
        $SQL = UserAuth::dbAccess()->select();
        $SQL->from(Array('A' => 't_assignment'), Array("C" => "COUNT(*)"));
        $SQL->joinLeft(Array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array());
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.CLASS_ID = ?", $academicId);
        $SQL->where("A.INCLUDE_IN_EVALUATION = 1");
        $SQL->where("MONTH(B.SCORE_INPUT_DATE) = ?", getMonthNumberFromMonthYear($monthyear) * 1);
        $SQL->where("YEAR(B.SCORE_INPUT_DATE) = ?", getYearFromMonthYear($monthyear) * 1);
        $SQL->where("A.INCLUDE_IN_EVALUATION = ?", $category);
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getTermCountCategoryScoreDate($category, $academicId, $subjectId, $term) {
        $SQL = UserAuth::dbAccess()->select();
        $SQL->from(Array('A' => 't_assignment'), Array("C" => "COUNT(*)"));
        $SQL->joinLeft(Array('B' => 't_student_score_date'), 'A.ID=B.ASSIGNMENT_ID', array());
        $SQL->where("B.SUBJECT_ID = ?", $subjectId);
        $SQL->where("B.CLASS_ID = ?", $academicId);
        $SQL->where("B.TERM = ?", $term);
        $SQL->where("A.INCLUDE_IN_EVALUATION = ?", $category);
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function setGradingScale($academicObject = false) {
        if ($academicObject) {
            $EDUCATION_TYPE = AcademicDBAccess::findGradeFromId($academicObject->CAMPUS_ID)->QUALIFICATION_TYPE;
        } else {
            $EDUCATION_TYPE = "training";
        }

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("EDUCATION_TYPE = '" . $EDUCATION_TYPE . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                if (strpos(trim($value->MARK), "<") !== false) {
                    $data = array();
                    $data['SCORE_MIN'] = 0;
                    $data['SCORE_MAX'] = "'" . substr(trim($value->MARK), 1) . "'";
                    self::dbAccess()->update("t_gradingsystem", $data, "ID='" . $value->ID . "'");
                } else {
                    $explode = explode("-", trim($value->MARK));
                    $MIN = isset($explode[0]) ? $explode[0] : 0;
                    $MAX = isset($explode[1]) ? $explode[1] : 0;

                    $SQL = "";
                    $SQL .= "UPDATE t_gradingsystem";
                    $SQL .= " SET ";
                    $SQL .= " SCORE_MIN='" . $MIN . "'";
                    $SQL .= " ,SCORE_MAX='" . $MAX . "' ";
                    $SQL .= " WHERE ID='" . $value->ID . "' ";
                    self::dbAccess()->query($SQL);
                }
            }
        }
    }

}

?>