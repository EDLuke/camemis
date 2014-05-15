<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/subject/TrainingSubjectDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class AssignmentTempDBAccess {

    public $dataforjson = null;
    public $data = array();

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new AssignmentTempDBAccess();
        }
        return $me;
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

        $data = array();

        $result = self::findAssignmentTempFromId($Id);

        if ($result) {

            $data["EDUCATION_SYSTEM"] = $result->EDUCATION_SYSTEM;
            $data["SHORT"] = $result->SHORT;
            $data["WEIGHTING"] = $result->WEIGHTING;
            $data["COEFF_VALUE"] = $result->COEFF_VALUE;
            $data["INCLUDE_IN_EVALUATION"] = $result->INCLUDE_IN_EVALUATION;
            $data["EVALUATION_TYPE"] = $result->EVALUATION_TYPE;
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

        if (isset($params["SHORT"]))
            $SAVEDATA["SHORT"] = addText($params["SHORT"]);

        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA["SORTKEY"] = addText($params["SORTKEY"]);

        if (isset($params["COEFF_VALUE"]))
            $SAVEDATA["COEFF_VALUE"] = addText($params["COEFF_VALUE"]);

        if (isset($params["WEIGHTING"])) {
            $SAVEDATA["WEIGHTING"] = $params["WEIGHTING"];
        } else {
            $SAVEDATA["WEIGHTING"] = 1;
        }

        switch (strtoupper($params["target"])) {
            case "TRAINING":
                $SAVEDATA["TRAINING"] = 1;
                break;
        }

        if (isset($params["INCLUDE_IN_EVALUATION"]))
            $SAVEDATA["INCLUDE_IN_EVALUATION"] = addText($params["INCLUDE_IN_EVALUATION"]);

        if (isset($params["SMS_SEND"]))
            $SAVEDATA['SMS_SEND'] = addText($params["SMS_SEND"]);

        switch ($objectId) {
            case "new":
                if (isset($params["parentId"])) {
                    $SAVEDATA["EDUCATION_TYPE"] = $params["parentId"];
                }

                if (!self::checkCount())
                    $SAVEDATA['ID'] = 1000;
                self::dbAccess()->insert('t_assignment_temp', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
                break;
            default:
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update('t_assignment_temp', $SAVEDATA, $WHERE);
                break;
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function getSQLAssignmentTemp($params) {

        $type = isset($params["type"]) ? addText($params["type"]) : 1;
        $parentId = isset($params["parentId"]) ? $params["parentId"] : "";

        $SELECT_DATA = array(
            "A.ID AS ID"
            , "A.NAME AS NAME"
            , "A.EVALUATION_TYPE AS EVALUATION_TYPE"
            , "A.COEFF_VALUE AS COEFF_VALUE"
            , "A.SORTKEY AS SORTKEY"
            , "A.INCLUDE_IN_EVALUATION"
            , "A.COEFF_VALUE"
            , "A.EDUCATION_SYSTEM AS EDUCATION_SYSTEM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), $SELECT_DATA);

        if ($parentId) {
            $SQL->where("A.EDUCATION_TYPE='" . $parentId . "'");
        }

        switch ($type) {
            case "1":
                $SQL->where("A.TRAINING='0'");
                break;
            case "2":
                $SQL->where("A.TRAINING='1'");
                break;
        }

        $SQL->group("A.ID");
        $SQL->order("A.SORTKEY ASC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonTreeAllAssignmentTemp($params) {

        $data = array();

        if (substr($params["node"], 8)) {
            $node = str_replace('CAMEMIS_', '', $params["node"]);
        } else {
            $node = $params["node"];
        }

        $type = isset($params["type"]) ? addText($params["type"]) : 1;

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $gradeId = "";
        $schoolyearId = "";
        if ($academicObject)
            switch ($academicObject->OBJECT_TYPE) {
                case "CLASS":
                case "SUBJECT":
                    $academicId = $academicObject->ID;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
                case "SCHOOLYEAR":
                    $academicId = "";
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
            }

        switch ($type) {
            case 1:
                $result = self::dbAccess()->fetchAll("SELECT * FROM t_camemis_type WHERE OBJECT_TYPE='QUALIFICATION_TYPE' AND PARENT<>0");
                if ($node == 0) {
                    $i = 0;
                    foreach ($result as $value) {

                        $data[$i]['id'] = "CAMEMIS_" . $value->ID;
                        $data[$i]['text'] = $value->NAME;
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_brick";
                        $i++;
                    }
                } else {

                    $sarchParam["parentId"] = $node;
                    $result = $this->getSQLAssignmentTemp($sarchParam);

                    $i = 0;
                    foreach ($result as $value) {

                        if ($academicObject) {
                            if (!AssignmentDBAccess::checkAcademicAssignment($subjectId, $academicId, $gradeId, $schoolyearId, $value->ID)) {
                                $data[$i]['id'] = $node . "_" . $value->ID;
                                $data[$i]['assignmentId'] = $value->ID;
                                $data[$i]['text'] = stripslashes($value->NAME);
                                $data[$i]['leaf'] = true;
                                $data[$i]['cls'] = "nodeTextBlue";

                                if ($value->EVALUATION_TYPE) {
                                    $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                                } else {
                                    $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                                }

                                switch ($value->INCLUDE_IN_EVALUATION) {
                                    case 1:
                                        $data[$i]['iconCls'] = "icon-flag_blue";
                                        break;
                                    case 2:
                                        $data[$i]['iconCls'] = "icon-flag_red";
                                        break;
                                    default:
                                        $data[$i]['iconCls'] = "icon-flag_white";
                                        break;
                                }

                                $i++;
                            }
                        } else {
                            $data[$i]['id'] = $node . "_" . $value->ID;
                            $data[$i]['assignmentId'] = $value->ID;
                            $data[$i]['text'] = stripslashes($value->NAME);
                            $data[$i]['leaf'] = true;
                            $data[$i]['cls'] = "nodeTextBlue";

                            if ($value->EVALUATION_TYPE) {
                                $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                            } else {
                                $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                            }

                            switch ($value->INCLUDE_IN_EVALUATION) {
                                case 1:
                                    $data[$i]['iconCls'] = "icon-flag_blue";
                                    break;
                                case 2:
                                    $data[$i]['iconCls'] = "icon-flag_red";
                                    break;
                                default:
                                    $data[$i]['iconCls'] = "icon-flag_white";
                                    break;
                            }

                            $i++;
                        }
                    }
                }
                break;

            case 2:
                //Training...
                $sarchParam["type"] = 2;

                $result = $this->getSQLAssignmentTemp($sarchParam);

                $i = 0;
                if ($result) {
                    foreach ($result as $value) {

                        $data[$i]['leaf'] = true;
                        $data[$i]['id'] = $node . "_" . $value->ID;
                        $data[$i]['assignmentId'] = $value->ID;
                        $data[$i]['text'] = stripslashes($value->NAME);
                        $data[$i]['iconCls'] = "icon-flag_purple";
                        if ($value->EVALUATION_TYPE) {
                            $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                        } else {
                            $data[$i]['text'] = stripslashes($value->NAME) . " (" . $value->COEFF_VALUE . ")";
                        }
                        $i++;
                    }
                }
                break;
        }

        return $data;
    }

    public function jsonRemoveAssignmentTemp($Id) {

        $condition = array('ID = ? ' => $Id);

        if ($Id)
            self::dbAccess()->delete('t_assignment_temp', $condition);

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

        $assignmentId = isset($params["assignmentId"]) ? $params["assignmentId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        if (strpos($assignmentId, "_") !== false) {
            $explode = explode("_", $assignmentId);
            $assignmentId = $explode[1];
        }

        $assignmentObject = self::findAssignmentTempFromId($assignmentId);
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject && $assignmentId) {

            switch ($academicObject->OBJECT_TYPE) {
                case "SCHOOLYEAR":
                    $classId = 0;
                    $academicId = 0;
                    $usedInClass = 0;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
                case "CLASS":
                    $classId = $academicObject->ID;
                    $gradeId = $academicObject->GRADE_ID;
                    $academicId = $academicObject->ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $usedInClass = 1;
                    break;
            }

            $CHECK = self::checkExistAssignmentTemp($assignmentId, $subjectId, $classId, $gradeId, $schoolyearId);

            $SAVEDATA["EDUCATION_SYSTEM"] = $academicObject->EDUCATION_SYSTEM;
            $SAVEDATA["TEMP_ID"] = $assignmentObject->ID;
            $SAVEDATA["SORTKEY"] = $assignmentObject->SORTKEY;
            $SAVEDATA["NAME"] = $assignmentObject->NAME;
            $SAVEDATA["SHORT"] = $assignmentObject->SHORT;
            $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
            $SAVEDATA["SUBJECT"] = $subjectId;
            $SAVEDATA["SCHOOLYEAR"] = $academicObject->SCHOOL_YEAR;
            $SAVEDATA["CLASS"] = $academicId;
            $SAVEDATA["COEFF_VALUE"] = $assignmentObject->COEFF_VALUE ? $assignmentObject->COEFF_VALUE : 1;
            $SAVEDATA["INCLUDE_IN_EVALUATION"] = $assignmentObject->INCLUDE_IN_EVALUATION;
            $SAVEDATA["EVALUATION_TYPE"] = $academicObject->EVALUATION_TYPE;
            $SAVEDATA["SMS_SEND"] = $assignmentObject->SMS_SEND;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA['USED_IN_CLASS'] = $usedInClass;

            if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                $SAVEDATA['STATUS'] = 1;
            }

            if (!$CHECK)
                self::dbAccess()->insert('t_assignment', $SAVEDATA);
        }

        return array("success" => true);
    }

    public static function checkCount() {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_assignment_temp", array("C" => "COUNT(*)"));
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkExistAssignmentTemp($tempId, $subjectId, $classId, $gradeId, $schoolyearId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_assignment", array("C" => "COUNT(*)"));

        if ($classId) {
            $SQL->where("CLASS='" . $classId . "'");
            $SQL->where("USED_IN_CLASS='1'");
        } else {
            $SQL->where("USED_IN_CLASS='0'");
        }

        $SQL->where("GRADE='" . $gradeId . "'");
        $SQL->where("SUBJECT='" . $subjectId . "'");
        $SQL->where("SCHOOLYEAR='" . $schoolyearId . "'");
        $SQL->where("TEMP_ID='" . $tempId . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    ////@veng
    public function jsonTreeAssignmentsBySubjctTraining($params) {
        
        $data = Array();

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $includeInEvaluation = isset($params["includeInEvaluation"]) ? $params["includeInEvaluation"] : 0;
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : "";

        //$classObject = AcademicDBAccess::findGradeFromId($classId);
        $classObject = TrainingDBAccess::findTrainingFromId($trainingId);
        
        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);
        if ($classObject && $subjectObject) {
            $trainingId = $classObject->ID;
            $subjectId = $subjectObject->ID;
        } else {
            return $data;
        }

        $facette = self::findAssignmentTempFromId($node);   
        $entries=array();
        if ($facette) {             
            $entries = $this->getAllScoreDate($node, $trainingId, $subjectId);
        } else {
            
            $searchParams["subjectId"] = $subjectId;
            $searchParams["trainingId"] = $trainingId;
            //$searchParams["term"] = $node;
            
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
                    $data[$i]['text'] = "" . $facette->NAME;
                    $data[$i]['text'] .= ": " . getShowDate($value->SCORE_INPUT_DATE) . "";
                    $data[$i]['iconCls'] = "icon-date_edit";      
                }
                $i++;
            }
        }
        return $data;
    }
    
     public static function getAllScoreDate($assignmentId, $trainingId, $subjectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_score_date", array('*'));
        $SQL->where("ASSIGNMENT_ID = '" . $assignmentId . "'");
        $SQL->where("SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("TRAINING_ID = '" . $trainingId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }
    
    public function getAllAssignmentQuery($params) {
        
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : "";
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
        $node = $trainingSubjectObject?$trainingSubjectObject->ID:0;
               
        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS ASSIGNMENT_ID, A.NAME AS NAME,B.SUBJECT AS SUBJECT, B.OBJECT_TYPE AS OBJECT_TYPE, B.ID AS RUL_ID";
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
    
    public static function findAssignmentJoinCategory($Id=false) {    
      
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $assignment = isset($params["assignmentId"]) ? $params["assignmentId"] : "";    
        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS ASSIGNMENT_ID, A.NAME AS NAME,B.INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION,B.SUBJECT AS SUBJECT, B.OBJECT_TYPE AS OBJECT_TYPE, B.ID AS RUL_ID";
        $SQL .= " FROM t_assignment_temp AS A";   
        $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.ASSIGNMENT";   
        $SQL .= " WHERE";
        $SQL .= " B.SUBJECT = '".$subjectId."'";
        $SQL .= " AND B.ASSIGNMENT = '".$assignment."'";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);     
    } 
    
     public function getListAssignmentsForAssessmentTraining($trainingId, $subjectId) {

        $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);
        $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);

        if ($trainingObject && $subjectObject) {
            $searchParams["trainingId"] = $trainingObject->ID;
            $searchParams["subjectId"] = $subjectObject->ID;
            return $this->getAllAssignmentQuery($searchParams);
        }
    }

}

?>