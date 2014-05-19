<?php
//////////////////////////////////////////////////////////////////////////
//@Sea Peng
//Date: 22.11.2013
//////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/user/UserRoleDBAccess.php';
require_once setUserLoacalization();

class CamemisEvaluationDBAccess {

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

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }
    
    //////////////////////////////////////////////////////////////////////////////
    //ANSWER SETTING
    /////////////////////////////////////////////////////////////////////////////
    public static function findAnswerObjectFromId($Id ) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_evaluation_answer'), array('*'));
        
        $SQL->where("A.ID='" . $Id . "'");

        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function getAnswerObjectDataFromId($Id) {

        $result = self::findAnswerObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["ANSWER_TYPE"] = setShowText($result->ANSWER_TYPE);
        }

        return $data;
    }
    
    public static function jsonLoadEvaluationAnswer($Id) {
        
        $result = self::findAnswerObjectFromId($Id);
        
        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getAnswerObjectDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;   
    }
    
    public static function jsonSaveEvaluationAnswer($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) :'new';
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) :0;
        
        $SAVEDATA = array();
        
        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);
        
        if ($params["parentId"] <> 0){
            $facette = self::findAnswerObjectFromId($params["parentId"]);
            $SAVEDATA['PARENT'] = (int) $params["parentId"];
            
            if ($facette)
                $SAVEDATA['ANSWER_TYPE'] = $facette->ANSWER_TYPE;
        }
            
        if (isset($params["ANSWER_TYPE"]))
            $SAVEDATA["ANSWER_TYPE"] = addText($params["ANSWER_TYPE"]);
        
        if($parentId){
            $SAVEDATA['OBJECT_TYPE'] = "ITEM";    
        }else{
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";    
        }
    
        if($objectId == "new"){
            self::dbAccess()->insert('t_evaluation_answer', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();   
        }else{
            $WHERE[] = "ID = '" . $objectId . "'";

            self::dbAccess()->update('t_evaluation_answer', $SAVEDATA, $WHERE);
        }
        
        return array(
            "success" => true
            , "objectId" => $objectId
        );   
    }
    
    public function jsonRemoveEvaluationAnswer($Id) {
        self::dbAccess()->delete('t_evaluation_answer', array("ID='" . $Id . "'"));
        self::dbAccess()->delete('t_evaluation_answer', array("PARENT='" . $Id . "'"));
        
        return array(
            "success" => true
        );    
    }
    
    public static function sqlAllEvaluationAnswer($node) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_evaluation_answer'), array('*'));
        if (!$node) {
            $SQL->where("PARENT=0");
        } else {
            $SQL->where("PARENT=" . $node . "");
        }
        $SQL->order("NAME ASC");
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonTreeAllEvaluationAnswer($params) {
        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        
        $result = self::sqlAllEvaluationAnswer($node);
        $data = array();
        $i = 0;
        
        if ($result) {
            foreach ($result as $value) {
                $data[$i]['id'] = $value->ID;
                $data[$i]['parent'] = $value->PARENT;
                switch ($value->OBJECT_TYPE) {
                    case "FOLDER":
                        $data[$i]['text'] = $value->NAME.' ('.firstUppercase($value->ANSWER_TYPE).')';
                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        break;
                    case "ITEM":
                        $data[$i]['text'] = $value->NAME;
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        break;        
                }
                $i++;
            }    
        }
        return $data;
    }
    
    //////////////////////////////////////////////////////////////////////////////
    //QUESTION SETTING
    /////////////////////////////////////////////////////////////////////////////
    public static function findQuestionObjectFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_evaluation_question'), array('A.ID','A.NAME AS QUESTION_NAME','DESCRIPTION','ANSWERID'));
        $SQL->joinLeft(array('B' => 't_evaluation_answer'), 'B.ID=A.ANSWERID', array('B.NAME AS ANSWER_NAME'));
            
        $SQL->where("A.ID='" . $Id . "'");

        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function getQuestionObjectDataFromId($Id) {

        $result = self::findQuestionObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["QUESTION_NAME"] = setShowText($result->QUESTION_NAME);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["ANSWER_NAME"] = setShowText($result->ANSWER_NAME);
        }

        return $data;
    }
    
    public static function jsonLoadEvaluationQuestion($Id) {
        
        $result = self::findQuestionObjectFromId($Id);
        
        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getQuestionObjectDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;   
    }
    
    public static function jsonSaveEvaluationQuestion($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) :'new'; 
        
        $SAVEDATA = array();
        
        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);
        
        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);
            
        if (isset($params["ANSWERID"]))
            $SAVEDATA["ANSWERID"] = addText($params["ANSWERID"]);
    
        if($objectId == "new"){
            self::dbAccess()->insert('t_evaluation_question', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();   
        }else{
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_evaluation_question', $SAVEDATA, $WHERE);
        }
        
        return array(
            "success" => true
            , "objectId" => $objectId
        );   
    }
    
    public function jsonRemoveEvaluationQuestion($Id) {
        self::dbAccess()->delete('t_evaluation_question', array("ID='" . $Id . "'"));
        //self::dbAccess()->delete('t_student_advisory', array("ADVISORY_ID='" . $Id . "'"));
        
        return array(
            "success" => true
        );    
    }
    
    public static function sqlLoadAllEvaluationQuestion($params) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_evaluation_question'), array('A.ID','A.NAME AS QUESTION_NAME','DESCRIPTION'));
        $SQL->joinLeft(array('B' => 't_evaluation_answer'), 'B.PARENT=A.ANSWERID AND B.PARENT<>0', array('B.NAME AS ANSWER_NAME'));
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);    
    }
    
    public static function jsonLoadAllEvaluationQuestion($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        
        $data = array();
        $i = 0;
        
        $result = self::sqlLoadAllEvaluationQuestion($params);
        if($result){
            foreach ($result as $value){
                $data[$i]["ID"] = $value->ID;
                $data[$i]["QUESTION_NAME"] = setShowText($value->QUESTION_NAME);
                $data[$i]["ANSWER_NAME"] = setShowText($value->ANSWER_NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION); 
                
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
    
    public static function sqlLoadEvaluationQuestionByTopic($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_camemis_evaluation_question'), array('*'));
        $SQL->joinLeft(array('B' => 't_evaluation_question'), 'B.ID=A.QUESTION_ID', array('B.NAME AS QUESTION_NAME','DESCRIPTION'));
        $SQL->joinLeft(array('C' => 't_evaluation_answer'), 'C.PARENT=B.ANSWERID', array('C.NAME AS ANSWER_NAME'));
        
        $SQL->where("A.TOPIC_ID='" . $objectId . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);    
    }
    
    public static function jsonLoadEvaluationQuestionByTopic($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        
        $data = array();
        $i = 0;
        
        $result = self::sqlLoadEvaluationQuestionByTopic($params);
        if($result){
            foreach ($result as $value){
                $data[$i]["ID"] = $value->ID;
                $data[$i]["QUESTION_NAME"] = setShowText($value->QUESTION_NAME);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["ANSWER_NAME"] = setShowText($value->ANSWER_NAME); 
                
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
    
    public static function sqlLoadUnassignedQuestionToTopic($params) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_evaluation_question'), array('A.ID','A.NAME AS QUESTION_NAME','DESCRIPTION'));
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);    
    }
    
    public static function checkAssignedQuestionToTopic($topicId, $questionId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_evaluation_question", array("C" => "COUNT(*)"));
        $SQL->where("TOPIC_ID = '" . $topicId . "'");
        $SQL->where("QUESTION_ID = '" . $questionId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    public static function jsonLoadUnassignedQuestionToTopic($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        
        $data = array();
        $i = 0;
        
        $result = self::sqlLoadUnassignedQuestionToTopic($params);
        if($result){
            foreach ($result as $value){
                $questionAssigned = self::checkAssignedQuestionToTopic($objectId, $value->ID);
                if (!$questionAssigned) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["QUESTION_NAME"] = setShowText($value->QUESTION_NAME);
                    $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION); 
                    
                    $i++; 
                } 
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
    
    public static function jsonActionAddQuestionToTopic($params) {
    
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $selectedCount = 0;

        if ($selectionIds) {
            $selectedQuestion = explode(",", $selectionIds);
            if ($selectedQuestion) {
                foreach ($selectedQuestion as $questionId) {
                    $SAVEDATA['TOPIC_ID'] = $objectId;
                    $SAVEDATA['QUESTION_ID'] = $questionId;

                    self::dbAccess()->insert('t_camemis_evaluation_question', $SAVEDATA);
                    ++$selectedCount;
                }
            }
        }

        return array(
            "success" => true
            , 'selectedCount' => $selectedCount
        );
    }
    
    //////////////////////////////////////////////////////////////////////////////
    //TOPIC SETTING
    /////////////////////////////////////////////////////////////////////////////
    public static function jsonSaveEvaluationTopic($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) :'new'; 
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : '';
        
        $SAVEDATA = array();
        
        if (isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);
        
        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);
    
        if($objectId == "new"){
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_camemis_evaluation', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }else{
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_camemis_evaluation', $SAVEDATA, $WHERE);
        }
        
        return array(
            "success" => true
            , "objectId" => $objectId
        );   
    }
    
    public static function findGuidById($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('t_camemis_evaluation'));
        $SQL->where("ID='" . $Id . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function findTopicObjectFromId($Id ) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_camemis_evaluation'), array('*'));
        
        $SQL->where("A.ID='" . $Id . "'");

        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function getTopicObjectDataFromId($Id) {

        $result = self::findTopicObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
        }

        return $data;
    }
    
    public static function jsonLoadEvaluationTopic($Id) {
        
        $result = self::findTopicObjectFromId($Id);
        
        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getTopicObjectDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;   
    }
    
    public static function sqlAllEvaluationTopic($params) {
        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_camemis_evaluation'), array('*'));
        if (!$node) {
            $SQL->where("PARENT=0");
        } else {
            $SQL->where("PARENT=" . $node . "");
        }
        $SQL->order("NAME ASC");
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public function jsonTreeAllEvaluationTopic($params) {

        $result = self::sqlAllEvaluationTopic($params);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if ($value->NAME) {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['parentId'] = "" . $value->PARENT . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['cls'] = "nodeTextBold";

                    if (!self::checkTopicChild($value->ID)) {
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['iconCls'] = "icon-attach";
                    } else {
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                    }

                    $i++;
                }
            }

        return $data;
    }
    
    public function checkTopicChild($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_evaluation", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    public function jsonRemoveEvaluationTopic($Id) {
        self::dbAccess()->delete('t_camemis_evaluation', array("ID=" . $Id . ""));
        self::dbAccess()->delete('t_evaluation_user', array("EVALUATION=" . $Id . ""));
        
        return array(
            "success" => true
        );    
    } 
    
    public static function getAcademicsByEvaluation($params) {

        $DATA_FOR_JSON = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $educationSystem = isset($params["educationSystem"]) ? addText($params["educationSystem"])  : 0;
        $result = AcademicLevelDBAccess::getSQLAllAcademics($params);
        
        if ($result)
            foreach ($result as $value) {

                $data['id'] = "" . $value->ID . "";
                $data['guid'] = "" . $value->GUID . "";
                $data['parentId'] = "" . $value->PARENT . "";
                $data['objectType'] = $value->OBJECT_TYPE;
                $data['schoolyearId'] = $value->SCHOOL_YEAR;

                switch ($value->OBJECT_TYPE) {

                    case "CAMPUS":
                        $data['leaf'] = false;
                        $data['text'] = setShowText($value->NAME);
                        $data['cls'] = "nodeFolderBold";
                        $data['iconCls'] = "icon-bricks";
                        break;
                    case "GRADE":
                        $data['leaf'] = false;
                        $data['text'] = setShowText($value->NAME);
                        $data['cls'] = "nodeTextBoldBlue";
                        $data['iconCls'] = "icon-folder_magnify";
                        break;
                    case "SCHOOLYEAR":
                        if($educationSystem){
                            $data['leaf'] = false;    
                        }else{
                            $data['leaf'] = true;    
                        }
                        
                        $data['text'] = setShowText($value->NAME);
                        $data['cls'] = "nodeTextBlue";
                        $data['iconCls'] = "icon-group_link";
                        $data['checked'] = self::checkEvaluationAcademic($objectId, $value->ID);
                        break;
                    case "SUBJECT":
                        $data['leaf'] = true;
                        $subjectObject = SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);

                        if ($subjectObject) {
                            $data['title'] = $subjectObject->NAME;
                            if ($subjectObject->SHORT) {
                                $data['text'] = "($subjectObject->SHORT) " . $subjectObject->NAME;
                            } else {
                                $data['text'] = $subjectObject->NAME;
                            }
                        } else {
                            $data['text'] = "?";
                        }
                        $data['cls'] = "nodeFolderBold";
                        $data['iconCls'] = "icon-group_link";
                        $data['checked'] = self::checkEvaluationAcademic($objectId, $value->ID);
                        break;
                }

                $DATA_FOR_JSON[] = $data;
            }

        return $DATA_FOR_JSON;
    }

    public static function checkEvaluationAcademic($objectId, $Id) {

        $academicObject = AcademicDBAccess::findGradeFromId($Id);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_evaluation_user", array("C" => "COUNT(*)"));

        $SQL->where("GRADE = '" . $academicObject->GRADE_ID . "'");
        $SQL->where("SCHOOLYEAR_ID = '" . $academicObject->SCHOOL_YEAR . "'");
        $SQL->where("EVALUATION = '" . $objectId . "'");

        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            return $result->C ? true : false;
        } else {
            return false;
        }
    }
    
    public static function jsonActionAcademicToEvaluation($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $checked = isset($params["checked"]) ? $params["checked"] : "";
        $academicId = isset($params["academic"]) ? (int) $params["academic"] : "";
        $userroleId = isset($params["userroleId"]) ? $params["userroleId"] : "";

        if ($checked == "true") {
            if ($academicId) {
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                $SAVEDATA["GRADE"] = $academicObject->GRADE_ID; 
                $SAVEDATA["SCHOOLYEAR_ID"] = $academicObject->SCHOOL_YEAR;
            }

            if ($userroleId) {
                $SAVEDATA["USER_ROLE_ID"] = $userroleId;
            }

            $SAVEDATA["EVALUATION"] = $objectId;
            self::dbAccess()->insert("t_evaluation_user", $SAVEDATA);

            $msg = RECORD_WAS_ADDED;
        } else {
            if ($academicId) {
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                self::dbAccess()->delete("t_evaluation_user", array("GRADE='" . $academicObject->GRADE_ID . "'", "SCHOOLYEAR_ID='" . $academicObject->SCHOOL_YEAR . "'", "EVALUATION='" . $objectId . "'"));
            }

            if ($userroleId) {
                self::dbAccess()->delete("t_evaluation_user", array("USER_ROLE_ID='" . $userroleId . "'", "EVALUATION='" . $objectId . "'"));
            }

            $msg = MSG_RECORD_NOT_CHANGED_DELETED;
        }

        return array("success" => true, "msg" => $msg);
    }

}

?>
