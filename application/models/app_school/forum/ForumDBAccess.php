<?php

///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 18.03.2014
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/training/TrainingDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/CamemisTypeDBAccess.php';
require_once 'models/CAMEMISUploadDBAccess.php';
require_once setUserLoacalization();

class ForumDBAccess {

    public $data = array();
    private static $instance = null; 
    public $numberReplies = 0;
    
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
    
    public static function getForumById($ID){
        
        $SQL = self::dbAccess()->select(); 
        $SQL->from("t_forum", array("*")); 
        $SQL->where("ID = '".$ID."'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result;
              
    }
    
    public static function sqlForum($params){
        
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $CAMEMIS_TYPE = isset($params["CAMEMIS_TYPE"]) ? $params["CAMEMIS_TYPE"] : "";
        $object_type = isset($params["object_type"]) ? $params["object_type"] : "";
        $objectId = isset($params["ID"]) ? $params["ID"] : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        
        $SQL = self::dbAccess()->select();
        $SQL->from('t_forum', array('*'));
        if ($parentId) {
            $SQL->where("PARENT='" . $parentId . "'");
        } else {
            $SQL->where("PARENT='0'");
        }
        
        if($objectId){
            $SQL->where("ID='".$objectId."'");    
        }
        
        if($CAMEMIS_TYPE){
            $SQL->where("CAMEMIS_TYPE='" . $CAMEMIS_TYPE . "'");        
        }
        
        if($object_type){
            $SQL->where("OBJECT_TYPE='" . $object_type . "'");    
        }
        
        if ($globalSearch) {
            $SQL->where("NAME LIKE '%" . $globalSearch . "%'");
            $SQL->ORwhere("CONTENT LIKE '%" . $globalSearch . "%'");
        }
        
        $SQL->order("CREATED_DATE DESC");
            
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function countReply($forumId){
    
       $SQL = self::dbAccess()->select();
       $SQL->from("t_forum", array("C" => "COUNT(*)"));
       $SQL->where("PARENT='" . $forumId . "'");             
       $result = self::dbAccess()->fetchRow($SQL);
       return $result ? $result->C : 0; 
    }
    
    public function getCountReplies($forumId){
       
       $count = ForumDBAccess::countReply($forumId);
       $this->numberReplies += $count;
       $params["parentId"]=$forumId;
       $result = ForumDBAccess::sqlForum($params);
       if($result){
            foreach($result as $value){
                if (ForumDBAccess::checkForumChild($value->ID)){
                    $this->getCountReplies($value->ID);
                } 
            }    
       }
       return $this->numberReplies;     
    }
    
    public static function jsonTopicForum($params){
        
        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $data = array();
        $i = 0;
        $result = self::sqlForum($params);
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["TOPIC"] = $value->NAME;
                $data[$i]["CONTENT"] = $value->CONTENT;
                $data[$i]["CREATED_BY"] = $value->CREATED_BY;
                $data[$i]["POST_DATE"] = getShowDate($value->CREATED_DATE);
                $forumObject = new ForumDBAccess();
                $data[$i]["REPLY_NUM"] = $forumObject->getCountReplies($value->ID);
                $data[$i]["STATUS"] = $value->STATUS;
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
    
    public static function checkForumChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_forum", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    public static function jsonTreeGridForum($params) {
        
        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";
        if($params["node"]=="0"){
            $params["ID"] = $params["objectId"];  
        }
        $result = self::sqlForum($params);

        $i = 0;
        $data = array();

        if ($result) {
            foreach ($result as $value) {

                if ($value->NAME) {
                    if (self::checkForumChild($value->ID)) {
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-message";
                    } else {
                        $data[$i]['leaf'] = true;
                        $data[$i]['iconCls'] = "icon-message_edit";   
                    }
                    $data[$i]['id'] = $value->ID;
                    $data[$i]['TOPIC'] = "<b>".stripslashes($value->NAME)."</b><br/><br/><span class='myclass'>".$value->CONTENT."</span>";
                    $data[$i]["POST_DATE"] = "<b>".getShowDate($value->CREATED_DATE) . "</b><br/>by " .$value->CREATED_BY;
                    $data[$i]["CREATED_BY"] = $value->CREATED_BY;
                    $data[$i]['cls'] = "mytexnode";
                    
                    $data[$i]['parentId'] = $value->PARENT;
                    $i++;
                }
            }
        }
        return $data;
    }
    
    public static function jsonSaveForum($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $facette = self::getForumById($objectId);
        $SAVEDATA = array();
        
        
        if (isset($params["CAMEMIS_TYPE"]))
            $SAVEDATA['CAMEMIS_TYPE'] = addText($params["CAMEMIS_TYPE"]);
        if (isset($params["TOPIC"]))
            $SAVEDATA['NAME'] = addText($params["TOPIC"]);
        if (isset($params["OBJECT_TYPE"]))
            $SAVEDATA['OBJECT_TYPE'] = addText($params["OBJECT_TYPE"]);
        if (isset($params["CONTENT"]))
            $SAVEDATA['CONTENT'] = $params["CONTENT"];
        if (isset($params["STATUS"]))
            $SAVEDATA['STATUS'] = $params["STATUS"];
        
        if($facette){
            $SAVEDATA['PARENT'] = $facette->PARENT;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_forum', $SAVEDATA, $WHERE);    
        }else{
            if (isset($params["parentId"]))
            $SAVEDATA['PARENT'] = addText($params["parentId"]);
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_forum', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }
        
        return array("success" => true, "objectId" => $objectId);    
    }
    
    public static function jsonLoadForum($Id) {

        $facette = self::getForumById($Id);
        $data = array();
        if ($facette) {
            $data['TOPIC'] = $facette->NAME;
            $data["CONTENT"] = $facette->CONTENT;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }
    
    public static function deleteParendsChilesItemTree($id) {

        $SQL_DELETE = "DELETE FROM t_forum";
        $SQL_DELETE .= " WHERE";
        $SQL_DELETE .= " ID IN (" . $id . ")";
        //error_log($SQL);
        self::dbAccess()->query($SQL_DELETE);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_forum", array("*"));
        $SQL->where("PARENT IN (" . $id . ")");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            $idchilearr = array();
            foreach ($result as $values) {
                $idchilearr[] = $values->ID;
            }
            $chileID = implode(",", $idchilearr);

            return self::deleteParendsChilesItemTree($chileID);
        } else {

            return 0;
        }
    }
    
    public static function deleteForum($id) {

        self::deleteParendsChilesItemTree($id);
        return array("success" => true);
    }
    
    public function getForumCAMEMISType($target) {
        $params = array();
        
        switch(strtoupper($target)){
            case 'ALUMNI':
                $objectType = 'FORUM_ALUMNI';    
                break;    
        }
        $SQL = self::dbAccess()->select();
        $SQL->from(array('t_camemis_type'));
        if($objectType)
        $SQL->where("OBJECT_TYPE='" . $objectType . "'");
        $SQL .= " ORDER BY NAME";

        //error_log($SQL);                   
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
        
    }
 
}

?>