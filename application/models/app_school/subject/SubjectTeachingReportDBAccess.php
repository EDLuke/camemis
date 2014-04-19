<?php
///////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 03.05.2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once setUserLoacalization();

class SubjectTeachingReportDBAccess {

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
    
    public static function getSubjectTeachingReportsById($contentId) {
       
        $SELECTION_A = array(
            "ID AS CONTENT_ID"
            , "SUBJECT AS SUBJECT_ID"
            , "CLASS AS CLASS_ID"
            , "TEACHER AS TEACHER"
            , "TITLE AS TITLE"
            , "CONTENT AS CONTENT"
            , "CREATED_DATE AS CREATED_DATE"
        );
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject_teaching_report'), $SELECTION_A);
        $SQL->where('A.ID ='.$contentId);

        //error_log($SQL->__toString());
        $result= self::dbAccess()->fetchRow($SQL);
        return $result ? $result : 0;
    }
    
    public static function getAllSubjectTeachingReports($subjectId, $classId, $teacherId) {

        $SELECTION_A = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_B = array(
            "ID AS CONTENT_ID"
            , "TITLE AS TITLE"
            , "CONTENT AS CONTENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "TEACHER AS TEACHER"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_teaching_report'), 'A.ID=B.SUBJECT', $SELECTION_B);
        $SQL->where('B.TEACHER = ?', $teacherId);//@THORN Visal
        if($subjectId){
            $SQL->where('B.SUBJECT = ?', $subjectId);
        }
        
        if($classId){
            $SQL->where('B.CLASS = ?', $classId);
        }
        
        

        switch (UserAuth::getUserType()) {
            case "TEACHER":
                $SQL->where('B.TEACHER = ?', $teacherId);
                break;
        }
        //////////
        $SQL->order('B.CREATED_DATE DESC');
        //error_log($SQL->__toString());
        
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonLoadAllSubjectTeachingReport($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $classId = isset($params["classId"]) ? addText($params["classId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        switch(UserAuth::getUserType()){
            case "TEACHER":
            case "INSTRUCTOR":
                $teacherId = Zend_Registry::get('USER')->ID;
            break;
            default:
                $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
            break;
        }
        

        $result = self::getAllSubjectTeachingReports(
            $subjectId
            , $classId
            , $teacherId);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {

                $data[$key]["CONTENT_ID"] = $value->CONTENT_ID;
                $data[$key]["TITLE"] = $value->TITLE;
                $data[$key]["CONTENT"] = $value->CONTENT;
                $data[$key]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                $facette = StaffDBAccess::findStaffFromId($value->TEACHER);
                $data[$key]["TEACHER_NAME"] = $facette->LASTNAME . " " . $facette->FIRSTNAME;
                
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
    
    public static function jsonLoadTeachingReport($contentId){
        
        $result = self::getSubjectTeachingReportsById($contentId);
        $data = array();
        if ($result) {

            $data["CONTENT_ID"] = $result->CONTENT_ID;
            $data["TITLE"] = setShowText($result->TITLE);
            $data["CONTENT"] = $result->CONTENT;
            $data["DATE"] = $result->CREATED_DATE;
            $data["CREATED_DATE"] = $result->CREATED_DATE;
              
        }
       
       return array(
                "success" => true
                , "data" => $data
            ); 
    }

    public static function jsonSaveSubjectTeachingReport($params) {
        
        $objectId=isset($params["objectId"])?$params["objectId"]:'';
        $CREATEDATE=isset($params["CREATED_DATE"]) ? setDate2DB(($params["CREATED_DATE"])) : '';
        
        if($objectId){
             
            $SAVEDATA['TITLE'] = isset($params["TITLE"]) ? addText($params["TITLE"]) : "";
            $SAVEDATA['CREATED_DATE'] = $CREATEDATE ? $CREATEDATE : getCurrentDBDate();
            $SAVEDATA['CONTENT'] = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_subject_teaching_report', $SAVEDATA, $WHERE);  
        }else{
           
            $SAVEDATA['SUBJECT'] = isset($params["subjectId"]) ? ($params["subjectId"]) : "";
            $SAVEDATA['CLASS'] = isset($params["classId"]) ? ($params["classId"]) : "";
            $SAVEDATA['TEACHER']=Zend_Registry::get('USER')->ID;       
            $SAVEDATA['TITLE'] = isset($params["TITLE"]) ? addText($params["TITLE"]) : "";
            $SAVEDATA['CREATED_DATE'] = $CREATEDATE ? $CREATEDATE : getCurrentDBDate();
            $SAVEDATA['CONTENT'] = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";
            self::dbAccess()->insert('t_subject_teaching_report', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }
        return array(
            "success" => true
            ,"objectId" => $objectId
        );
    }
    
    public function jsonDeleteSubjectTeachingReport($params) { 
       
        if ($this->checkSubjectTeachingReport($params["objectId"])) {
            self::dbAccess()->delete('t_subject_teaching_report', array("ID='" . $params["objectId"] . "'"));
        }
      
        return array("success" => true);
    }
    
    public function checkSubjectTeachingReport($Id) {

        $CHECK = self::getSubjectTeachingReportsById($Id);

        if ($CHECK) {
            return true;
        } else {
            return false;
        }
    }
    
}

?>