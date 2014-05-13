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

    public static function makeGrade($Id, $type = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        if ($type) {
            return $result ? $result->$type : "---";
        } else {
            return $result ? $result->DESCRIPTION : "---";
        }
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

    public static function comboGradingSystem($scoreType, $qualificationType) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_gradingsystem", array("*"));
        $SQL->where("SCORE_TYPE = '" . $scoreType . "'");
        $SQL->where("EDUCATION_TYPE = '" . $qualificationType . "'");
        $SQL->order("SORTKEY ASC");
        #error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data[0] = "{chooseValue: '0', chooseDisplay: '---'}";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "{chooseValue: '" . $value->ID . "', chooseDisplay: '" . $value->DESCRIPTION . "'}";
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

}

?>