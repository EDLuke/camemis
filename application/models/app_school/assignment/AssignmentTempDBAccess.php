<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/training/TrainingSubjectDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class AssignmentTempDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public function __construct() {

        $this->DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $this->SELECT = $this->DB_ACCESS->select();
        $this->_TOSTRING = $this->SELECT->__toString();
    }

    public static function findAssignmentTempFromId($Id) {

        $query = self::dbAccess()->select();
        $query->from('t_assignment_temp', '*');
        $query->where('ID = ?', $Id);
        //echo $query->__toString();    
        return self::dbAccess()->fetchRow($query);
    }

    public function getAssignmentTempDataFromId($Id) {

        $result = self::findAssignmentTempFromId($Id);

        $data = array();

        if ($result) {

            $data["EDUCATION_TYPE"] = $result->EDUCATION_TYPE;
            $data["SHORT"] = $result->SHORT;
            $data["WEIGHTING"] = $result->WEIGHTING;
            $data["COEFF_VALUE"] = $result->COEFF_VALUE;
            $data["POINTS_POSSIBLE"] = $result->POINTS_POSSIBLE;
            $data["INCLUDE_IN_EVALUATION"] = $result->INCLUDE_IN_EVALUATION;
            $data["EVALUATION_TYPE"] = $result->EVALUATION_TYPE;
            $data["SUBJECT"] = $result->SUBJECT;
            $data["SMS_SEND"] = $result->SMS_SEND;
            $data["TRAINING"] = $result->TRAINING;
            $data["NAME"] = setShowText($result->NAME);
            $data["SORTKEY"] = setShowText($result->SORTKEY);
        }

        return $data;
    }

    public function jsonLoadAssignmentTemp($Id) {

        $result = self::findAssignmentTempFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getAssignmentTempDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function jsonSaveAssignmentTemp($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : false;

        if (isset($params["educationType"]))
            $SAVEDATA["EDUCATION_TYPE"] = (int) $params["educationType"];

        if (isset($params["SHORT"]))
            $SAVEDATA["SHORT"] = addText($params["SHORT"]);

        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA["SORTKEY"] = addText($params["SORTKEY"]);

        if (isset($params["COEFF_VALUE"]))
            $SAVEDATA["COEFF_VALUE"] = addText($params["COEFF_VALUE"]);

        if (isset($params["POINTS_POSSIBLE"]))
            $SAVEDATA["POINTS_POSSIBLE"] = addText($params["POINTS_POSSIBLE"]);

        if (isset($params["EVALUATION_TYPE"])) {
            $SAVEDATA["EVALUATION_TYPE"] = (int) $params["EVALUATION_TYPE"];
            if (isset($params["SUBJECT"]))
                $SAVEDATA["SUBJECT"] = (int) $params["SUBJECT"];
        }

        if (isset($params["WEIGHTING"])) {
            $SAVEDATA["WEIGHTING"] = addText($params["WEIGHTING"]);
        } else {
            $SAVEDATA["WEIGHTING"] = 1;
        }

        switch (strtoupper($params["target"])) {
            case "TRAINING":
                $SAVEDATA["TRAINING"] = 1;
                break;
        }

        if (isset($params["INCLUDE_IN_EVALUATION"]))
            $SAVEDATA["INCLUDE_IN_EVALUATION"] = (int) $params["INCLUDE_IN_EVALUATION"];

        switch ($objectId) {
            case "new":
                $CHECK = self::findLastId();
                if (!$CHECK)
                    $SAVEDATA["ID"] = 1000;
                $this->DB_ACCESS->insert('t_assignment_temp', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
                break;
            default:
                $WHERE = $this->DB_ACCESS->quoteInto("ID = ?", $objectId);
                $this->DB_ACCESS->update('t_assignment_temp', $SAVEDATA, $WHERE);
                break;
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function getSQLAssignmentTemp($params) {

        $target = isset($params["target"]) ? addText($params["target"]) : "GENERAL";
        $educationType = isset($params["educationType"]) ? addText($params["educationType"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $SELECT_DATA = array(
            "A.ID AS ID"
            , "A.SHORT AS SHORT"
            , "A.EVALUATION_TYPE AS EVALUATION_TYPE"
            , "A.COEFF_VALUE AS COEFF_VALUE"
            , "A.NAME AS NAME"
            , "A.SORTKEY AS SORTKEY"
            , "A.INCLUDE_IN_EVALUATION"
            , "A.COEFF_VALUE"
            , "A.EDUCATION_TYPE AS EDUCATION_TYPE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), $SELECT_DATA);

        if ($educationType)
            $SQL->where("A.EDUCATION_TYPE='" . $educationType . "'");

        if ($subjectId) {
            $SQL->where("A.SUBJECT='" . $subjectId . "'");
        }

        switch (strtoupper($target)) {
            case "GENERAL":
                $SQL->where("A.TRAINING='0'");
                break;
            case "TRAINING":
                $SQL->where("A.TRAINING='1'");
                break;
        }
        $SQL->group("A.ID");
        $SQL->order("A.SORTKEY ASC");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonTreeAllAssignmentTemp($params) {
        $data = array();

        $node = isset($params["node"]) ? addText($params["node"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";

        if (strtoupper($target) == "GENERAL") {
            $data = array();

            if (substr($node, 14)) {
                $node = str_replace('QUALIFICATION_', '', $node);
                $objectType = "QUALIFICATION";
            } elseif (substr($node, 8)) {
                $node = str_replace('SUBJECT_', '', $node);
                $objectType = "SUBJECT";
            } else {
                $node = $node;
                $objectType = "ASSIGNMENT";
            }

            $academicObject = '';
            $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
            if ($academicId)
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);

            $SQL = "";
            $SQL .= "SELECT * FROM t_camemis_type WHERE OBJECT_TYPE='QUALIFICATION_TYPE' AND PARENT<>0";
            if ($academicObject)
                $SQL .= " AND ID='" . $academicObject->QUALIFICATION_TYPE . "'";
            $result = self::dbAccess()->fetchAll($SQL);

            if ($node == 0) {
                $i = 0;
                if ($result) {
                    foreach ($result as $value) {
                        $data[$i]['id'] = "QUALIFICATION_" . $value->ID;
                        $data[$i]['add'] = true;
                        $data[$i]['show'] = false;
                        $data[$i]['type'] = "qualification";
                        $data[$i]['show'] = false;
                        $data[$i]['text'] = $value->NAME;
                        $data[$i]['leaf'] = false;
                        $data[$i]['parentId'] = $value->ID;
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        $data[$i]['cls'] = "nodeTextBold";
                        $i++;
                    }
                }
            } else {

                switch ($objectType) {
                    case "QUALIFICATION":
                        $entries = self::getDefaultListAssignments($node);
                        //print_r($entries);
                        if ($entries) {
                            $i = 0;
                            foreach ($entries as $value) {

                                $data[$i]['id'] = $value->ID;
                                //Assignment
                                if (!$value->SUBJECT) {
                                    $data[$i]['id'] = $value->ID;
                                    if ($value->EVALUATION_TYPE) {
                                        $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                                    } else {
                                        $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                                    }

                                    switch ($value->INCLUDE_IN_EVALUATION) {
                                        case 1:
                                            $data[$i]['iconCls'] = "icon-flag_blue";
                                            break;
                                        case 3:
                                            $data[$i]['iconCls'] = "icon-flag_red";
                                            break;
                                        case 2:
                                            $data[$i]['iconCls'] = "icon-flag_yellow";
                                            break;
                                    }

                                    $data[$i]['assignmentId'] = $value->ID;
                                    $data[$i]['leaf'] = true;
                                    $data[$i]['add'] = false;
                                    $data[$i]['show'] = true;
                                    //Subject
                                } else {

                                    $data[$i]['id'] = "SUBJECT_" . $value->ID;
                                    $data[$i]['text'] = $value->NAME;
                                    $data[$i]['iconCls'] = "icon-star";
                                    $data[$i]['leaf'] = false;
                                }

                                $i++;
                            }
                        }
                        break;
                    case "SUBJECT":
                        $entries = self::getListAssignments($node, false);
                        $i = 0;
                        foreach ($entries as $value) {
                            $data[$i]['leaf'] = true;
                            $data[$i]['add'] = false;
                            $data[$i]['show'] = true;
                            $data[$i]['id'] = $value->ID;
                            $data[$i]['parentId'] = $value->EDUCATION_TYPE;
                            $data[$i]['assignmentId'] = $value->ID;

                            if ($value->EVALUATION_TYPE) {
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                            } else {
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                            }

                            switch ($value->INCLUDE_IN_EVALUATION) {
                                case 1:
                                    $data[$i]['iconCls'] = "icon-flag_blue";
                                    break;
                                case 3:
                                    $data[$i]['iconCls'] = "icon-flag_red";
                                    break;
                                case 2:
                                    $data[$i]['iconCls'] = "icon-flag_yellow";
                                    break;
                            }

                            $i++;
                        }
                        break;
                }
            }
        }

        ////////////////////////////////////////////////////////////////////////
        // FOR TRAINING PROGRAMS...
        ////////////////////////////////////////////////////////////////////////
        if (strtoupper($target) == "TRAINING") {

            if (substr($node, 8)) {
                $node = str_replace('SUBJECT_', '', $node);
                $objectType = "SUBJECT";
            } else {
                $node = $node;
                $objectType = "ASSIGNMENT";
            }

            if ($node == 0) {
                $result = self::getDefaultListAssignments(false, true);
            } else {
                $sarchParam["target"] = "TRAINING";
                $sarchParam["subjectId"] = $node;
                $result = $this->getSQLAssignmentTemp($sarchParam);
            }

            $i = 0;
            if ($result) {
                foreach ($result as $value) {

                    $data[$i]['text'] = stripslashes($value->NAME);
                    if (isset($value->SUBJECT)) {
                        if ($value->SUBJECT) {
                            $data[$i]['id'] = "SUBJECT_" . $value->ID;
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-star";
                        } else {
                            $data[$i]['id'] = $value->ID;
                            $data[$i]['assignmentId'] = $value->ID;
                            $data[$i]['leaf'] = true;
                            $data[$i]['iconCls'] = "icon-flag_blue";

                            if ($value->EVALUATION_TYPE) {
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                            } else {
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                            }
                        }
                    } else {
                        $data[$i]['id'] = $value->ID;
                        $data[$i]['assignmentId'] = $value->ID;
                        $data[$i]['leaf'] = true;
                        $data[$i]['iconCls'] = "icon-flag_blue";

                        if ($value->EVALUATION_TYPE) {
                            $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                        } else {
                            $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                        }
                    }

                    $i++;
                }
            }
        }

        return $data;
    }

    public static function getListAssignments($subjectId, $educationType) {
        $SELECT_DATA = array(
            "ID"
            , "SHORT"
            , "NAME"
            , "COEFF_VALUE"
            , "INCLUDE_IN_EVALUATION"
            , "EVALUATION_TYPE"
            , "EDUCATION_TYPE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), $SELECT_DATA);
        if ($subjectId) {
            $SQL->where("A.SUBJECT='" . $subjectId . "'");
        } else {
            if ($educationType) {
                $SQL->where("A.EDUCATION_TYPE='" . $educationType . "'");
                $SQL->where("A.SUBJECT='0'");
            }
        }

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonRemoveAssignmentTemp($Id) {

        $condition = array(
            'ID = ? ' => $Id
        );

        if ($Id)
            $this->DB_ACCESS->delete('t_assignment_temp', $condition);

        return array(
            "success" => true
        );
    }

    public function jsonListAssignmentTemp($params) {

        $data = array();
        $result = $this->getSQLAssignmentTemp($params);

        if ($result) {

            $i = 0;
            foreach ($result as $value) {

                $j = $i + 1;
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = "(" . $j . ") " . $value->NAME;

                $qualificationObject = CamemisTypeDBAccess::findObjectFromId($value->EDUCATION_TYPE);

                if ($qualificationObject) {
                    $data[$i]["EDUCATION_TYPE"] = $qualificationObject->NAME;
                } else {
                    $data[$i]["EDUCATION_TYPE"] = "---";
                }

                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function jsonAddAssignmentToSubject($params) {

        $assignmentId = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : '';

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($assignmentId) {
            if ($academicObject) {
                switch ($academicObject->OBJECT_TYPE) {
                    case "SCHOOLYEAR":
                        $classId = 0;
                        $gradeId = $academicObject->GRADE_ID;
                        $schoolyearId = $academicObject->SCHOOL_YEAR;
                        $used_in_class = 0;
                        break;
                    case "CLASS":
                        $classId = $academicObject->ID;
                        $gradeId = $academicObject->GRADE_ID;
                        $schoolyearId = $academicObject->SCHOOL_YEAR;
                        $used_in_class = 1;
                        break;
                }
            } else {
                exit("No academicObject....");
            }

            $facette = self::findAssignmentTempFromId($assignmentId);
            $CHECK = self::checkExistAssignmentTemp($assignmentId, $subjectId, $classId, $gradeId, $schoolyearId);

            if (!$CHECK && $facette) {
                $SAVEDATA["EDUCATION_SYSTEM"] = $academicObject->EDUCATION_SYSTEM;
                $SAVEDATA["STATUS"] = 1;
                $SAVEDATA["SORTKEY"] = $facette->SORTKEY;
                $SAVEDATA["NAME"] = $facette->NAME;
                $SAVEDATA["SHORT"] = $facette->SHORT;
                $SAVEDATA["GRADE"] = $gradeId;
                $SAVEDATA["SUBJECT"] = $subjectId;
                $SAVEDATA["SCHOOLYEAR"] = $schoolyearId;
                $SAVEDATA["TEMP_ID"] = $facette->ID;
                $SAVEDATA["CLASS"] = $classId;
                $SAVEDATA["COEFF_VALUE"] = $facette->COEFF_VALUE ? $facette->COEFF_VALUE : 1;
                $SAVEDATA["INCLUDE_IN_EVALUATION"] = $facette->INCLUDE_IN_EVALUATION;
                $SAVEDATA["EVALUATION_TYPE"] = $academicObject->EVALUATION_TYPE;
                $SAVEDATA["SMS_SEND"] = $facette->SMS_SEND;
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                $SAVEDATA['USED_IN_CLASS'] = $used_in_class;
                $this->DB_ACCESS->insert('t_assignment', $SAVEDATA);
            }
        }

        return array("success" => true);
    }

    protected static function findLastId() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_assignment_temp", array("C" => "COUNT(*)"));
        $SQL->order('ID DESC');
        $SQL->limitPage(0, 1);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkExistAssignmentTemp($tempId, $subjectId, $classId, $gradeId, $schoolyearId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_assignment", array("C" => "COUNT(*)"));

        if ($classId) {
            $SQL->where("CLASS = ?", $classId);
            $SQL->where("USED_IN_CLASS='1'");
        } else {
            $SQL->where("USED_IN_CLASS='0'");
        }

        $SQL->where("GRADE = ?", $gradeId);
        $SQL->where("SUBJECT = ?", $subjectId);
        $SQL->where("SCHOOLYEAR = ?", $schoolyearId);
        $SQL->where("TEMP_ID = ?", $tempId);

        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getAllScoreDate($assignmentId, $trainingId, $subjectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", array('*'));
        $SQL->where("ASSIGNMENT_ID = ?", $assignmentId);
        $SQL->where("SUBJECT_ID = ?", $subjectId);
        $SQL->where("TRAINING_ID = ?", $trainingId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findAssignmentTempFromIdTraining($Id) {

        $query = self::dbAccess()->select();
        $query->from('t_student_training_assignment', '*');
        $query->where('ID = ?', $Id);
        //echo $query->__toString();
        return self::dbAccess()->fetchRow($query);
    }

    public function jsonTreeAssignmentsBySubjctTraining($params) {

        $data = Array();

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $includeInEvaluation = isset($params["includeInEvaluation"]) ? (int) $params["includeInEvaluation"] : 0;
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        $classObject = TrainingDBAccess::findTrainingFromId($trainingId);

        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);
        if ($classObject && $subjectObject) {
            $trainingId = $classObject->ID;
            $subjectId = $subjectObject->ID;
        } else {
            return $data;
        }

        $facette = self::findAssignmentTempFromId($node);
        $entries = array();
        if ($facette) {
            $entries = $this->getAllScoreDate($node, $trainingId, $subjectId);
        } else {

            $searchParams["subjectId"] = $subjectId;
            $searchParams["trainingId"] = $trainingId;

            if ($includeInEvaluation) {
                $searchParams["includeInEvaluation"] = 1;
            }
            $entries = self::getAllAssignmentQuery($searchParams);
        }

        if ($entries) {
            $i = 0;
            foreach ($entries as $value) {

                if (!$facette) {

                    $data[$i]['id'] = "" . $value->ASSIGNMENT_ID . "";
                    $data[$i]['text'] = "" . $value->NAME . "";
                    $data[$i]['leaf'] = false;
                    $data[$i]['isClick'] = true;
                    $data[$i]['iconCls'] = "icon-flag_blue";
                } else {

                    $data[$i]['cls'] = "nodeTextBlue";
                    $facette = self::findAssignmentTempFromId($node);
                    $data[$i]['leaf'] = true;
                    $data[$i]['isClick'] = true;
                    $data[$i]['setId'] = $value->ASSIGNMENT_ID;
                    $data[$i]['id'] = "" . $value->ASSIGNMENT_ID . "_" . $value->SCORE_INPUT_DATE;
                    $data[$i]['display'] = "" . $facette->NAME;
                    $data[$i]['display'] .= ": " . getShowDate($value->SCORE_INPUT_DATE) . "";
                    $data[$i]['text'] = "" . getShowDate($value->SCORE_INPUT_DATE) . "";
                    $data[$i]['iconCls'] = "icon-date_edit";
                }
                $i++;
            }
        }
        return $data;
    }

    public function getAllAssignmentQuery($params) {

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $facette = TrainingDBAccess::findTrainingFromId($trainingId);
        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $trainingTermId = $facette->ID;
                break;
            case "CLASS":
                $trainingTermId = $facette->TERM;
                break;
        }
        $trainingSubject = TrainingSubjectDBAccess::getInstance();
        $trainingSubjectObject = $trainingSubject->getTrainingSubject($trainingTermId, $subjectId);
        $node = $trainingSubjectObject ? $trainingSubjectObject->ID : 0;

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS ASSIGNMENT_ID
                , A.NAME AS NAME
                , B.SUBJECT AS SUBJECT
                , B.OBJECT_TYPE AS OBJECT_TYPE
                , B.ID AS RUL_ID";
        $SQL .= " FROM t_assignment_temp AS A";
        $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.ASSIGNMENT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.PROGRAM='" . $facette->PROGRAM . "'";

        if ($subjectId)
            $SQL .= " AND B.SUBJECT='" . $subjectId . "'";

        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL .= " AND B.TERM='" . $facette->ID . "'";
                break;
            case "CLASS":
                $SQL .= " AND B.TERM='" . $facette->TERM . "'";
                break;
        }

        $SQL .= " AND B.PARENT='" . $node . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findAssignmentJoinCategory($Id = false) {

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignment = isset($params["assignmentId"]) ? addText($params["assignmentId"]) : "";
        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS ASSIGNMENT_ID
                , A.NAME AS NAME
                , B.INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION
                , B.SUBJECT AS SUBJECT
                , B.OBJECT_TYPE AS OBJECT_TYPE
                , B.ID AS RUL_ID";
        $SQL .= " FROM t_assignment_temp AS A";
        $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.ASSIGNMENT";
        $SQL .= " WHERE";
        $SQL .= " B.SUBJECT = '" . $subjectId . "'";
        $SQL .= " AND B.ASSIGNMENT = '" . $assignment . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    /**
     * 
     * @param type $eduType
     * @return type $isTraining
     */
    public static function getDefaultListAssignments($eduType, $isTraining = false) {

        $FIRST_SQL = self::dbAccess()->select();
        $FIRST_SQL->from("t_assignment_temp", array('*'));
        $FIRST_SQL->where("SUBJECT = ?", 0);
        if ($eduType)
            $FIRST_SQL->where("EDUCATION_TYPE = ?", $eduType);
        if ($isTraining)
            $FIRST_SQL->where("TRAINING = ?", 1);
        //error_log($FIRST_SQL->__toString());
        $FIRST_RESULT = self::dbAccess()->fetchAll($FIRST_SQL);

        $SECOND_SQL = self::dbAccess()->select();
        $SECOND_SQL->from(array('A' => 't_subject'), array("ID AS ID", "SHORT AS SHORT", "NAME AS NAME"));
        $SECOND_SQL->joinLeft(array('B' => 't_assignment_temp'), 'B.SUBJECT = A.ID', array("SUBJECT"));
        $SECOND_SQL->where("B.SUBJECT>0");
        if ($eduType)
            $SECOND_SQL->where("B.EDUCATION_TYPE='" . $eduType . "'");
        if ($isTraining)
            $SECOND_SQL->where("B.TRAINING='1'");

        $SECOND_SQL->group("A.ID");
        //error_log($SECOND_SQL->__toString());
        $SECOND_RESULT = self::dbAccess()->fetchAll($SECOND_SQL);
        
        return array_merge($FIRST_RESULT, $SECOND_RESULT);
    }
}

?>