<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 07.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/training/TrainingDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class TrainingSubjectDBAccess extends SubjectDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function findTrainingSubject($Id) {

        $SQL = "";
        $SQL .= " SELECT A.*,B.*,C.SHORT AS SHORT,C.NAME AS ASSIGNMENTNAME";  //@veasna
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.SUBJECT";
        $SQL .= " LEFT JOIN t_assignment_temp AS C ON B.ASSIGNMENT=C.ID";   //@veasna
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.ID='" . $Id . "'";

        return self::dbAccess()->fetchRow($SQL);
    }

    //$veasna
    public static function findTrainingAssignmentStudent($trainingSubjectId, $studentId) {
        $facette = self::findTrainingSubject($trainingSubjectId);
        $SELECT_A = array(
            'SHORT AS SHORT'
            , 'NAME AS NAME'
        );
        $SELECT_B = array(
            'SCORE AS SCORE'
        );
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_training_assignment'), 'A.ID=B.ASSIGNMENT', $SELECT_B);
        //$SQL->joinLeft(array('C' => 't_training'), 'C.ID=B.TRAINING', $SELECT_C);
        $SQL->where("B.STUDENT = '" . $studentId . "'");
        $SQL->where("B.SUBJECT = '" . $facette->SUBJECT . "'");
        $SQL->where("B.ASSIGNMENT = '" . $facette->ASSIGNMENT . "'");
        $results = self::dbAccess()->fetchAll($SQL);
        //return $results;
        $data = array();

        if ($results) {
            $data["NAME"] = $results[0]->NAME;
            $data["SHORT"] = $results[0]->SHORT;
            $data["SCORE"] = displayNumberFormat($results[0]->SCORE);
            $data["SCORE_MIN"] = displayNumberFormat($facette->SCORE_MIN) ? $facette->SCORE_MIN : 0;
            $data["SCORE_MAX"] = displayNumberFormat($facette->SCORE_MAX) ? $facette->SCORE_MAX : 0;
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["GOALS"] = setShowText($facette->GOALS);
            $data["MATERIALS"] = setShowText($facette->MATERIALS);
            $data["EVALUATION"] = setShowText($facette->EVALUATION);
            $data["OBJECTIVES"] = setShowText($facette->OBJECTIVES);
        } else {
            $data["NAME"] = $facette->ASSIGNMENTNAME;
            $data["SCORE"] = "---";
            $data["SCORE_MIN"] = displayNumberFormat($facette->SCORE_MIN) ? $facette->SCORE_MIN : 0;
            $data["SCORE_MAX"] = displayNumberFormat($facette->SCORE_MAX) ? $facette->SCORE_MAX : 0;
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["GOALS"] = setShowText($facette->GOALS);
            $data["MATERIALS"] = setShowText($facette->MATERIALS);
            $data["EVALUATION"] = setShowText($facette->EVALUATION);
            $data["OBJECTIVES"] = setShowText($facette->OBJECTIVES);
        }
        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function loadTrainingSubject($Id) {
        /*
         $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";  
        $trainingSubjectObject=TrainingDBAccess::findTrainingFromId($Id);
        $data=array();
        if($trainingSubjectObject){
            $data["EVALUATION_TYPE"] =$trainingSubjectObject->EVALUATION_TYPE;   
            if($trainingSubjectObject->ID){
                $facette = self::findTrainingSubject($trainingSubjectObject->ID);
                if($facette){
                    $data["ASSIGNMENTNAME"] = setShowText($facette->ASSIGNMENTNAME);
                    $data["NAME"] = setShowText($facette->NAME);
                    $data["MAX_POSSIBLE_SCORE"] = displayNumberFormat($facette->MAX_POSSIBLE_SCORE);
                    $data["SCORE_MIN"] = displayNumberFormat($facette->SCORE_MIN) ? $facette->SCORE_MIN : 0;
                    $data["SCORE_MAX"] = displayNumberFormat($facette->SCORE_MAX) ? $facette->SCORE_MAX : "";
                    $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
                    $data["GOALS"] = setShowText($facette->GOALS);
                    $data["MATERIALS"] = setShowText($facette->MATERIALS);
                    $data["EVALUATION"] = setShowText($facette->EVALUATION);
                    $data["OBJECTIVES"] = setShowText($facette->OBJECTIVES);
                    $data["INCLUDE_IN_EVALUATION"] = $facette->INCLUDE_IN_EVALUATION ? true : false;
                    $data["SHORT"] = setShowText($facette->SHORT);           
                    $data["SORTKEY"] =setShowText($facette->SORTKEY); 
                }
            }
           
        }
        
        return array(
            "success" => true
            , "data" => $data
        );
        */
        $facette = self::findTrainingSubject($Id);

        $data = array();

        if ($facette) {
            $data["ASSIGNMENTNAME"] = setShowText($facette->ASSIGNMENTNAME);
            $data["NAME"] = setShowText($facette->NAME);
            $data["MAX_POSSIBLE_SCORE"] = displayNumberFormat($facette->MAX_POSSIBLE_SCORE);
            $data["SCORE_MIN"] = displayNumberFormat($facette->SCORE_MIN) ? $facette->SCORE_MIN : 0;
            $data["SCORE_MAX"] = displayNumberFormat($facette->SCORE_MAX) ? $facette->SCORE_MAX : "";
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["GOALS"] = setShowText($facette->GOALS);
            $data["MATERIALS"] = setShowText($facette->MATERIALS);
            $data["EVALUATION"] = setShowText($facette->EVALUATION);
            $data["OBJECTIVES"] = setShowText($facette->OBJECTIVES);
            $data["INCLUDE_IN_EVALUATION"] = $facette->INCLUDE_IN_EVALUATION ? true : false;
            $data["SHORT"] = setShowText($facette->SHORT);
            //$data["WEIGHTING"] =setShowText($facette->WEIGHTING);              
            $data["COEFF_VALUE"] =$facette->COEFF_VALUE ? true : false; 
            
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function jsonUnassignedSubjectsByTraining($params) {

        $data = array();

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $params["target"] = "TRAINING";
        $result = self::getAllSubjectsQuery($params);

        $selectedResult = self::sqlAssignedSubjectsByTraining($params);

        $CHECK_DATA = array();
        if ($selectedResult)
            foreach ($selectedResult as $key => $value) {
                $CHECK_DATA[$value->SUBJECT_ID] = $value->SUBJECT_ID;
            }

        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {

                if (!in_array($value->ID, $CHECK_DATA)) {

                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["SUBJECT_NAME"] = $value->NAME;
                    $data[$i]["SUBJECT_COEFF"] = $value->COEFF ? $value->COEFF : 1;
                    $i++;
                }
            }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function sqlAssignedSubjectsByTraining($params) {

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";  
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : ""; 
        $nationalExam = isset($params["nationalExam"]) ? addText($params["nationalExam"]) : "";
        $subjectType = isset($params["subjectType"]) ? addText($params["subjectType"]) : "";
        $include_in_evaluation = isset($params["include_in_evaluation"]) ? (int) $params["include_in_evaluation"] : "0";

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " B.ID AS PARENT_ID, A.SHORT AS SHORT, A.ID AS SUBJECT_ID";
        $SQL .= " ,A.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,A.COEFF AS COEFF";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.SHORT AS SUBJECT_SHORT";
        $SQL .= " ,A.NAME AS NAME";
        $SQL .= " ,A.ID AS SUBJECT_ID";
        $SQL .= " ,B.OBJECT_TYPE AS OBJECT_TYPE";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.STATUS=1";

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }
        if ($include_in_evaluation)
            $SQL .= " AND B.INCLUDE_IN_EVALUATION ='" . $include_in_evaluation . "'";
        if ($subjectId) {
            $SQL .= " AND A.ID = '" . $subjectId . "'";
        }
        switch ($facette->OBJECT_TYPE) {
                case "TERM":
                    $SQL .= " AND B.TERM='" . $facette->ID . "'";
                    break;
                case "CLASS":
                    $SQL .= " AND B.TRAINING='" . $facette->TERM . "'";
                    break;
        }
        $SQL .= " GROUP BY A.ID";
        $SQL .= " ORDER BY A.NAME";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonAssignedSubjectsByTraining($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = self::sqlAssignedSubjectsByTraining($params);

        $data = array();

        if ($result) {

            $i = 0;
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["SUBJECT_ID"] = $value->SUBJECT_ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["COEFF"] = $value->COEFF;
                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["DURATION"] = $value->DURATION;
                
                $i++;
            }
        }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public static function saveTrainingSubject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SAVEDATA = array();

        if (isset($params["SCORE_MIN"]))
            $SAVEDATA["SCORE_MIN"] = $params["SCORE_MIN"];

        $SAVEDATA["SCORE_MAX"] = $params["SCORE_MAX"];
        
        if (isset($params["SCORE_MAX"])) {
            $SAVEDATA["MAX_POSSIBLE_SCORE"] = addText($params["SCORE_MAX"]);
        }

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);
        if (isset($params["EVALUATION"]))
            $SAVEDATA["EVALUATION"] = addText($params["EVALUATION"]);
        if (isset($params["GOALS"]))
            $SAVEDATA["GOALS"] = addText($params["GOALS"]);
        if (isset($params["MATERIALS"]))
            $SAVEDATA["MATERIALS"] = addText($params["MATERIALS"]);
        if (isset($params["OBJECTIVES"]))
            $SAVEDATA["OBJECTIVES"] = addText($params["OBJECTIVES"]);
        
        $SAVEDATA['INCLUDE_IN_EVALUATION'] = isset($params["INCLUDE_IN_EVALUATION"]) ? 1 : 0;
            
        $WHERE = array();
        $WHERE[] = self::dbAccess()->quoteInto('ID = ?', $objectId);
        self::dbAccess()->update('t_training_subject', $SAVEDATA, $WHERE);

        return array("success" => true);
    }

    protected function checkSubjectINTraining($subjectId, $trainingId) {

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_training_subject";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SUBJECT = '" . $subjectId . "'";

        $SQL .= " AND PROGRAM = '" . $facette->PROGRAM . "'";
        $SQL .= " AND LEVEL = '" . $facette->LEVEL . "'";
        $SQL .= " AND TERM = '" . $facette->TERM . "'";

        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL .= " AND TRAINING = '0'";
                break;
            case "CLASS":
                $SQL .= " AND TRAINING = '" . $trainingId . "'";
                break;
        }

        //echo $SQL;
        $result = self::dbAccess()->fetchRow($SQL);

        return $result->C ? true : false;
    }

    protected function addSubjectINTraining($subjectId, $trainingId) {

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SAVEDATA = array();

        if ($facette) {

            $subjectObject = self::findSubjectFromId($subjectId);

            if ($subjectObject) {
                $SAVEDATA['SCORE_MIN'] = $subjectObject->SCORE_MIN;
                $SAVEDATA['SCORE_MAX'] = $subjectObject->SCORE_MAX;
                if ($subjectObject->SCORE_TYPE)
                $SAVEDATA['SCORE_TYPE'] = $subjectObject->SCORE_TYPE;
                 if ($subjectObject->COEFF_VALUE) {
                    $SAVEDATA['COEFF_VALUE'] = $subjectObject->COEFF_VALUE;
                } else {
                    $SAVEDATA['COEFF_VALUE'] = 1;
                }
            }
            $SAVEDATA['PROGRAM'] = $facette->PROGRAM;
            $SAVEDATA['TERM'] = $facette->ID;
            $SAVEDATA['LEVEL'] = $facette->LEVEL;
            $SAVEDATA['TRAINING'] = $trainingId;
            $SAVEDATA['SUBJECT'] = $subjectId;
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";
            self::dbAccess()->insert('t_training_subject', $SAVEDATA);
        }
    }

    public function removeGradeSubject($params) {

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";

        if ($trainingId && $subjectId) {
            $SQL = "DELETE FROM 't_grade_subject'";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND TRAINING = '" . $trainingId . "'";
            $SQL .= " AND SUBJECT = '" . $subjectId . "'";
            self::dbAccess()->query($SQL);
        }

        return array("success" => true);
    }

    public function getTrainingSubject($trainingId, $subjectId) {

        $SQL = "SELECT *";
        $SQL .= " FROM t_training_subject";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        $SQL .= " AND TRAINING = '" . $trainingId . "'";

        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonAddSubjectToTraining($params) {

        $selectionIds = $params["selectionIds"];
        $trainingId = $params["trainingId"];

        if ($selectionIds != "") {
            $selectedSubjects = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($selectedSubjects)
                foreach ($selectedSubjects as $subjectId) {
                    if (!$this->checkSubjectINTraining($subjectId, $trainingId)) {
                        $this->addSubjectINTraining($subjectId, $trainingId);
                        $selectedCount++;
                    } else {
                        $selectedCount = 0;
                    }
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public function jsonTreeAssignedSubjectsByTraining($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : 0;

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        if ($node == 0) {
            $SQL = "";
            $SQL .= " SELECT ";
            $SQL .= " B.ID AS PARENT_ID, A.NAME AS NAME, A.ID AS SUBJECT_ID";
            $SQL .= " ,A.SUBJECT_TYPE AS SUBJECT_TYPE";
            $SQL .= " ,B.OBJECT_TYPE AS OBJECT_TYPE";
            $SQL .= " FROM t_subject AS A";
            $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.SUBJECT";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND A.STATUS=1";
            $SQL .= " AND B.OBJECT_TYPE='FOLDER'";
            $SQL .= " AND B.PROGRAM='" . $facette->PROGRAM . "'";

            switch ($facette->OBJECT_TYPE) {
                case "TERM":
                    $SQL .= " AND B.TERM='" . $facette->ID . "'";
                    break;
                case "CLASS":
                    $SQL .= " AND B.TRAINING='" . $facette->TERM . "'";
                    break;
            }

            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);
        } else {
            $SQL = "";
            $SQL .= " SELECT ";
            $SQL .= " A.ID AS ASSIGNMENT_ID, A.NAME AS NAME, B.OBJECT_TYPE AS OBJECT_TYPE, B.ID AS RUL_ID";
            $SQL .= " FROM t_assignment_temp AS A";
            $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.ASSIGNMENT";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND B.PROGRAM='" . $facette->PROGRAM . "'";
            //@veasna
            $SQL .= " AND B.LEVEL='" . $facette->LEVEL . "'";
            //
            //$SQL .= " AND B.TERM='" . $facette->TERM . "'";

            switch ($facette->OBJECT_TYPE) {
                case "TERM":
                    //$SQL .= " AND B.LEVEL='" . $trainingId . "'";
                    //@veasna
                    $SQL .= " AND B.TERM='" . $trainingId . "'";
                    //
                    break;
                case "CLASS":
                    $SQL .= " AND B.LEVEL='" . $facette->LEVEL . "'";
                    break;
            }
            $SQL .= " AND B.PARENT='" . $node . "'";

            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);
        }

        $data = array();

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                switch ($value->OBJECT_TYPE) {
                    case 'FOLDER':
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['id'] = "" . $value->PARENT_ID . "";
                        $data[$i]['subjectId'] = "" . $value->SUBJECT_ID . "";
                        $data[$i]['text'] = setShowText($value->NAME);
                        $data[$i]['onlyText'] = setShowText($value->NAME);
                        switch ($value->SUBJECT_TYPE) {
                            case 0:
                                $data[$i]['iconCls'] = "icon-star_silver";
                                break;
                            case 1:
                                $data[$i]['iconCls'] = "icon-star";
                                break;
                            case 2:
                                $data[$i]['iconCls'] = "icon-star_blue";
                                break;
                            case 3:
                                $data[$i]['iconCls'] = "icon-star_red";
                                break;
                            default:
                                $data[$i]['iconCls'] = "icon-star";
                                break;
                        }
                        $data[$i]['leaf'] = false;
                        break;
                    case'ITEM':
                        $data[$i]['id'] = $value->RUL_ID;
                        $data[$i]['text'] = setShowText($value->NAME);
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['iconCls'] = "icon-flag_blue";
                        break;
                }
                $i++;
            }
        }

        return $data;
    }

    public function addAssignment($parentId, $trainingId, $subjectId) {

        $SAVEDATA = array();

        $SQL = "SELECT * FROM t_assignment_temp WHERE TRAINING=1";
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA['PARENT'] = $parentId;
                $SAVEDATA['TRAINING'] = $trainingId;
                $SAVEDATA['SUBJECT'] = $subjectId;
                $SAVEDATA['ASSIGNMENT'] = $value->ID;
                $SAVEDATA['OBJECT_TYPE'] = "ITEM";
                self::dbAccess()->insert('t_training_subject', $SAVEDATA);
            }
        }
    }

    public function jsonRemoveSubjectFromTraining($Id) {
        self::dbAccess()->delete('t_training_subject', "ID='" . $Id . "'");
        self::dbAccess()->delete('t_training_subject', "PARENT='" . $Id . "'");
        return array("success" => true);
    }

    public function jsonLoadTeachersBySubjectTraining($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : 0;
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : 0;

        $SELECTION_A = array(
            'ID AS TEACHER_ID'
            , 'CODE AS CODE'
            , 'FIRSTNAME AS FIRSTNAME'
            , 'LASTNAME AS LASTNAME'
        );

        $SQL = $this->SELECT;
        $SQL->from(array('A' => 't_staff'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_teacher_subject'), 'A.ID=B.TEACHER', array());
        $SQL->where("B.SUBJECT='" . $subjectId . "'");
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($resultRows) {
            foreach ($resultRows as $key => $value) {

                $ASSIGNED = self::checkTeacherSubjectTraining(
                                $value->TEACHER_ID
                                , $subjectId
                                , $trainingId
                );

                $data[$key]["ID"] = $value->TEACHER_ID;
                $data[$key]["CODE"] = $value->CODE;
                $data[$key]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$key]["LASTNAME"] = $value->LASTNAME;
                $data[$key]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$key]["ASSIGNED"] = $ASSIGNED;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function actionSubjectTrainingTeacherClass($params) {

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $teacherId = isset($params["id"]) ? addText($params["id"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $CHECK = self::checkTeacherSubjectTraining(
                        $teacherId
                        , $subjectId
                        , $trainingId
        );

        if ($newValue) {

            if (!$CHECK) {
                $SAVEDATA = array();
                $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                $SAVEDATA["TERM"] = $facette->TERM;
                $SAVEDATA["LEVEL"] = $facette->LEVEL;
                $SAVEDATA["TRAINING"] = $trainingId;
                $SAVEDATA["SUBJECT"] = $subjectId;
                $SAVEDATA["TEACHER"] = $teacherId;
                self::dbAccess()->insert('t_subject_teacher_training', $SAVEDATA);
            }
        } else {

            self::deleteTeacherSubjectTraining(
                    $teacherId
                    , $subjectId
                    , $trainingId
            );
        }

        return array(
            "success" => true
        );
    }

    public static function deleteTeacherSubjectTraining($teacherId, $subjectId, $trainingId) {

        self::dbAccess()->delete(
                't_subject_teacher_training'
                , array("TRAINING='" . $trainingId . "'", "SUBJECT='" . $subjectId . "'", "TEACHER='" . $teacherId . "'")
        );
    }

    public static function deleteSubjectAssignment($subjectId, $trainingId) {

        $WHERE = array();
        $WHERE["SUBJECT"] = $subjectId;
        $WHERE["TRAINING"] = $trainingId;
        $WHERE["OBJECT_TYPE"] = "ITEM";
        self::dbAccess()->delete('t_training_subject', $WHERE);
    }

    public static function checkTeacherSubjectTraining($teacherId, $subjectId, $trainingId) {

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);
        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_training", array("C" => "COUNT(*)"));

        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL->where("TERM='" . $trainingId . "'");
                break;
            case "CLASS":
                $SQL->where("TRAINING='" . $trainingId . "'");
                break;
        }

        if ($trainingId)
            $SQL->where("TRAINING = '" . $trainingId . "'");
        if ($subjectId)
            $SQL->where("SUBJECT = ?",$subjectId);
        if ($teacherId)
            $SQL->where("TEACHER = ?",$teacherId);

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonTeacherTraining($params, $isJson = true) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $trainingId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SELECTION_A = array(
            'ID AS TEACHER_ID'
            , 'CODE AS CODE'
            , 'FIRSTNAME AS FIRSTNAME'
            , 'LASTNAME AS LASTNAME'
            //@soda
            , 'STAFF_SCHOOL_ID AS STAFF_SCHOOL_ID'
            , 'FIRSTNAME_LATIN AS FIRSTNAME_LATIN'
            , 'LASTNAME_LATIN AS LASTNAME_LATIN'
            , 'GENDER AS GENDER'
            , 'DATE_BIRTH AS DATE_BIRTH'
            , 'PHONE AS PHONE'
            , 'EMAIL AS EMAIL'
            , 'CREATED_DATE AS CREATED_DATE'
                //
        );

        $SELECTION_C = array(
            'ID AS SUBJECT_ID'
            , 'NAME AS SUBJECT_NAME'
        );

        $SELECTION_D = array(
            'NAME AS CURRENT_CLASS'
            , 'START_DATE AS START_DATE'
            , 'END_DATE AS END_DATE'
        );

        $SELECTION_E = array(
            'NAME AS LEVEL_NAME'
        );

        $SQL = $this->SELECT;
        $SQL->from(array('A' => 't_staff'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_teacher_training'), 'A.ID=B.TEACHER', array());
        $SQL->joinLeft(array('C' => 't_subject'), 'C.ID=B.SUBJECT', $SELECTION_C);
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=B.LEVEL', $SELECTION_E); //@soda

        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL->joinLeft(array('D' => 't_training'), 'D.ID=B.TERM', $SELECTION_D);
                $SQL->where("B.TERM='" . $trainingId . "'");
                break;
            case "CLASS":
                $SQL->joinLeft(array('D' => 't_training'), 'D.ID=B.TRAINING', $SELECTION_D);
                $SQL->where("B.TRAINING='" . $trainingId . "'");
                break;
        }

        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($resultRows) {
            foreach ($resultRows as $key => $value) {

                $data[$key]["ID"] = $value->TEACHER_ID;
                $data[$key]["CODE"] = $value->CODE;
                $data[$key]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$key]["LASTNAME"] = $value->LASTNAME; 
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$key]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                }else{
                    $data[$key]["FULL_NAME"] = $value->FIRSTNAME . " " . $value->LASTNAME;
                }
                $data[$key]["SUBJECT_NAME"] = $value->SUBJECT_NAME;
                $data[$key]["CURRENT_CLASS"] = $value->CURRENT_CLASS;
                //@soda
                $data[$key]["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                $data[$key]["FIRSTNAME_LATIN"] = $value->FIRSTNAME_LATIN;
                $data[$key]["LASTNAME_LATIN"] = $value->LASTNAME_LATIN;
                $data[$key]["GENDER"] = $value->GENDER;
                $data[$key]["DATE_BIRTH"] = $value->DATE_BIRTH;
                $data[$key]["PHONE"] = $value->PHONE;
                $data[$key]["EMAIL"] = $value->EMAIL;
                $data[$key]["TERM_NAME"] = $value->START_DATE . "  " . $value->END_DATE;
                $data[$key]["CREATED_DATE"] = $value->CREATED_DATE;
                $data[$key]["LEVEL_NAME"] = $value->LEVEL_NAME;
                //
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        //@soda
        if ($isJson) {
            $dataforjson = array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
            return $dataforjson;
        } else {
            return $data;
        }
        //
    }

    //@veasna

    public static function getTeacherTraningByTraining($trainingId) {
        $SQL = "";
        $SQL .= " SELECT A.ID AS TEACHER_ID, A.CODE AS CODE, A.FIRSTNAME AS FIRSTNAME, A.LASTNAME AS LASTNAME, A.STAFF_SCHOOL_ID AS STAFF_SCHOOL_ID";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_training AS B ON A.ID=B.TEACHER";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.TRAINING IN (" . $trainingId . ")";
        $SQL .= " GROUP BY TEACHER_ID" ;
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    public static function jsonTeacherByStudentTraining($params) {

        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $trainingId = "";
        $resultRows = '';
        $arr = array();
        $results = StudentTrainingDBAccess::getTrainigByStudentID($studentId);

        if ($results) {
            foreach ($results as $values) {
                $arr[] = $values->TRAINING_ID;
            }
            $trainingId = implode(",", $arr);
            $resultRows = self::getTeacherTraningByTraining($trainingId);
        }

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    //

    public function SubjectByTrainingCombo() {

        $utiles = Utiles::getInstance();
        $trainingId = $utiles->getValueRegistry("TRAINING_ID");
        $facette = TrainingDBAccess::findTrainingFromId($trainingId);
        if ($facette) {
            $SQL = "";
            $SQL .= " SELECT ";
            $SQL .= " A.SHORT AS SHORT";
            $SQL .= " ,A.NAME AS NAME, A.STATUS AS STATUS";
            $SQL .= " ,A.ID AS SUBJECT_ID";
            $SQL .= " FROM t_subject AS A";
            $SQL .= " LEFT JOIN t_training_subject AS B ON A.ID=B.SUBJECT";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND B.PROGRAM='" . $facette->PROGRAM . "'";
            $SQL .= " AND B.TERM='" . $facette->TERM . "'";
            $SQL .= " AND B.LEVEL='" . $facette->LEVEL . "'";
            $SQL .= " AND B.OBJECT_TYPE='FOLDER'";
            $SQL .= " ORDER BY A.NAME";
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);
        }

        $data = array();
        if ($result)
            foreach ($result as $value) {
                $data[] = "[\"$value->SUBJECT_ID\",\"$value->NAME\"]";
            }

        return "[" . implode(",", $data) . "]";
    }

    public function jsonAddAssignmentToTraining($params) {

        $SAVEDATA = array();

        $selectionIds = $params["selectionIds"];
        $trainingId = $params["trainingId"];
        $subjectId = $params["subjectId"];
        $parentId =  addText($params["parentId"]);

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        self::deleteSubjectAssignment($subjectId, $trainingId);

        if ($selectionIds != "") {
            $assignmentIds = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($assignmentIds)
                foreach ($assignmentIds as $assignmentId) {

                    switch ($facette->OBJECT_TYPE) {
                        case "TERM":
                            $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                            //$SAVEDATA["TERM"] = $facette->TERM;
                            //$SAVEDATA["LEVEL"] = $trainingId;
                            //@veasna
                            $SAVEDATA["TERM"] = $trainingId;
                            $SAVEDATA["LEVEL"] = $facette->LEVEL;
                            //$SAVEDATA["TRAINING"] = $trainingId;
                            //
                                break;
                        case "CLASS":
                            $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                            $SAVEDATA["TERM"] = $facette->TERM;
                            $SAVEDATA["LEVEL"] = $facette->LEVEL;
                            $SAVEDATA["TRAINING"] = $trainingId;
                            break;
                    }

                    $subjectObject = self::findSubjectFromId($subjectId);
                    
                    if ($subjectObject->SCORE_TYPE)
                    $SAVEDATA['SCORE_TYPE'] = $subjectObject->SCORE_TYPE;
                     if ($subjectObject->COEFF_VALUE) {
                        $SAVEDATA['COEFF_VALUE'] = $subjectObject->COEFF_VALUE;
                    } else {
                        $SAVEDATA['COEFF_VALUE'] = 1;
                    }
                    
                    $SAVEDATA["SUBJECT"] = $subjectId;
                    $SAVEDATA["PARENT"] = $parentId;
                    $SAVEDATA["ASSIGNMENT"] = $assignmentId;
                    $SAVEDATA["OBJECT_TYPE"] = "ITEM";
                    self::dbAccess()->insert('t_training_subject', $SAVEDATA);

                    $selectedCount++;
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public function jsonSubjectAssignmentTraining($params) {

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        $SQL = "SELECT ID,CONCAT('(',SHORT,') ',NAME) AS NAME";
        $SQL .= " FROM t_assignment_temp";
        $SQL .= " WHERE TRAINING=1";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($result) {

            $i = 0;
            foreach ($result as $value) {

                $CHECK = self::checkTrainingSubjectAssignment(
                                $trainingId
                                , $subjectId
                                , $value->ID);
                if (!$CHECK) {
                    $data[$i]["ASSIGNMENT_NAME"] = $value->NAME;
                    $data[$i]["ID"] = $value->ID;

                    $i++;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function checkTrainingSubjectAssignment($trainingId, $subjectId, $assignmentId) {

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training_subject", array("C" => "COUNT(*)"));

        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL->where("PROGRAM = '" . $facette->PROGRAM . "'");
                //@veasna
                $SQL->where("TERM = '" . $facette->ID . "'");
                $SQL->where("LEVEL = '" . $facette->LEVEL . "'");
                //
                break;
            case "CLASS":
                $SQL->where("PROGRAM = '" . $facette->PROGRAM . "'");
                $SQL->where("TERM = '" . $facette->TERM . "'");
                $SQL->where("LEVEL = '" . $facette->LEVEL . "'");
                break;
        }

        $SQL->where("SUBJECT = ?",$subjectId);
        $SQL->where("ASSIGNMENT = '" . $assignmentId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getTrainingAssignments($subjectId, $trainingId) {

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SELECTION_A = array(
            'ID AS ID'
            , 'SHORT AS SHORT'
            , 'NAME AS NAME'
        );
        //@veasna
        $SELECTION_B = array(
            'SCORE_MIN AS SCORE_MIN'
            , 'SCORE_MAX AS SCORE_MAX'
            , 'DESCRIPTION AS DESCRIPTION'
        );
        //
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_assignment_temp'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_training_subject'), 'A.ID=B.ASSIGNMENT', $SELECTION_B);
        $SQL->where("B.SUBJECT='" . $subjectId . "'");

        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL->where("B.PROGRAM='" . $facette->PROGRAM . "'");
                $SQL->where("B.TERM='" . $facette->TERM . "'");
                $SQL->where("B.LEVEL='" . $facette->LEVEL . "'");
                break;
            case "CLASS":
                $SQL->where("B.PROGRAM='" . $facette->PROGRAM . "'");
                $SQL->where("B.TERM='" . $facette->TERM . "'");
                $SQL->where("B.LEVEL='" . $facette->LEVEL . "'");
                break;
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getTrainingClassSubject($trainingId) {

        $facette = TrainingDBAccess::findTrainingFromId($trainingId);

        $SELECTION_A = array(
            'ID AS ID'
            , 'SHORT AS SHORT'
            , 'NAME AS NAME'
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_training_subject'), 'A.ID=B.SUBJECT', array());
        $SQL->where("B.OBJECT_TYPE='FOLDER'");
        switch ($facette->OBJECT_TYPE) {
            case "TERM":
                $SQL->where("B.PROGRAM='" . $facette->PROGRAM . "'");
                $SQL->where("B.TERM='" . $facette->TERM . "'");
                $SQL->where("B.LEVEL='" . $facette->LEVEL . "'");
                break;
            case "CLASS":
                $SQL->where("B.PROGRAM='" . $facette->PROGRAM . "'");
                $SQL->where("B.TERM='" . $facette->TERM . "'");
                $SQL->where("B.LEVEL='" . $facette->LEVEL . "'");
                break;
        }
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }
    public static function checkUseSubjectInTraining($subjecId, $trainingId) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_training_subject', 'COUNT(*) AS C');
        $SQL->where("SUBJECT = '" . $subjecId . "'");
        $SQL->where("TRAINING = '" . $trainingId . "'");
        
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    public static function findSubjectTraining($subjectId, $trainingObject) {

        $facette = SubjectDBAccess::findSubjectFromId($subjectId);
        $CHECK = self::checkUseSubjectInTraining($facette->ID, $trainingObject->ID);
        if ($CHECK) {
            $SQL = self::dbAccess()->select();
            $SQL->from('t_training_subject', array("*"));
            $SQL->where("SUBJECT = '" . $facette->ID . "'");          
            $SQL->where("TRAINING = '" . $trainingObject->ID . "'");
            
            
            $result = self::dbAccess()->fetchRow($SQL);
        } else {
            $SQL = self::dbAccess()->select();
            $SQL->from('t_training_subject', array("*"));
            $SQL->where("SUBJECT = '" . $facette->ID . "'");

            $result = self::dbAccess()->fetchRow($SQL);
        } 
        return $result;
    }
    
    public static function getListSubjectsForAssessmentTraining($trainingId) {

        $academicObject = TrainingDBAccess::findTrainingFromId($trainingId);

        if ($academicObject) {
            $params["trainingId"] = $academicObject->ID;
            $params["include_in_evaluation"] = 1;
            return TrainingSubjectDBAccess::sqlAssignedSubjectsByTraining($params);
        }
    }

}

?>