<?php

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SubjectHomeworkDBAccess { 
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
    
    public static function getGradeSubjectHomework($objectId, $subjectId,$classId, $studentId, $globalSearch) {             
        
        $SELECTION_A = array(
            "ID AS GRADE_ID"
        );
        
        $SELECTION_B = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_C = array(
            "ID AS CONTENT_ID"
            , "SUBJECT AS SUBJECT"
            , "CLASS_ID AS CLASS_ID"
            , "TEACHER AS TEACHER" 
            , "CONTENT AS CONTENT" 
            , "START_DATE AS START_DATE"
            , "END_DATE AS END_DATE"
            , "NAME AS NAME"
            , "STATUS AS STATUS"
            , "DISABLED_BY AS DISABLED_BY"  
        );

        $SELECTION_D = array(
            "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"                       
        );
                    
        $SELECTION_E = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT_STUDENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "HOMEWORK_ID AS HOMEWORK_ID"                       
        );         

        $SQL = self::dbAccess()->select();            
        $SQL->from(array('A' => 't_grade_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject'), 'B.ID = A.SUBJECT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_subject_homework'), 'B.ID = C.SUBJECT', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_staff'), 'D.ID = C.TEACHER', $SELECTION_D);
        $SQL->joinLeft(array('E' => 't_student_homework'), 'C.ID = E.HOMEWORK_ID', $SELECTION_E);
         
        if ($objectId)
            $SQL->where('A.ID = ?', $objectId);          
        if($subjectId)
            $SQL->where('C.SUBJECT = ?', $subjectId);
        if($studentId)
            $SQL->where('E.STUDENT_ID = ?', $studentId);
        if($classId)    
            $SQL->where('C.CLASS_ID = ?', $classId); 
        
        if ($globalSearch) {
            $SEARCH = " ((B.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (D.FIRSTNAME LIKE '" . $globalSearch . "%')"; 
            $SEARCH .= " OR (D.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (C.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        } 
        $SQL->order('C.START_DATE DESC');
        //error_log($SQL->__toString());  
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function getAllSubjectHomeworks($subjectId, $globalSearch = false, $classId, $teacherId) {
        
        $SELECTION_A = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_B = array(
            "ID AS CONTENT_ID"
            , "SUBJECT AS SUBJECT"
            , "CLASS_ID AS CLASS_ID"
            , "TEACHER AS TEACHER" 
            , "CONTENT AS CONTENT" 
            , "START_DATE AS START_DATE"
            , "END_DATE AS END_DATE"
            , "NAME AS NAME"
            , "STATUS AS STATUS"
            , "DISABLED_BY AS DISABLED_BY"  
        );

        $SELECTION_C = array(
            "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"                       
        );
                    
        $SELECTION_D = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT_STUDENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "HOMEWORK_ID AS HOMEWORK_ID"                       
        );         

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_homework'), 'A.ID=B.SUBJECT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_staff'), 'C.ID = B.TEACHER', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_student_homework'), 'B.ID = D.HOMEWORK_ID', $SELECTION_D);
        if($subjectId)
            $SQL->where('B.SUBJECT = ?', $subjectId);
        if($classId)    
            $SQL->where('B.CLASS_ID = ?', $classId); 
        
        switch (UserAuth::getUserType()) {
            case "TEACHER":
            case "INSTRUCTOR":
                $SQL->where('B.TEACHER = ?', $teacherId);
                break;   
            case "STUDENT":
                $SQL->where('B.STATUS = ?', 1);
                break;  
        }
        
        if ($globalSearch) {
            $SEARCH = " ((A.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (C.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (C.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        
        $SQL->order('B.START_DATE DESC');
        //error_log($SQL);  
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function getClassStudentHomework($classId){
        $SQL = "";
        $SQL .= " SELECT COUNT(*) AS TOTAL";
        $SQL .= " FROM t_student AS A";
        $SQL .= " LEFT JOIN t_student_schoolyear AS B ON A.ID = B.STUDENT";
        $SQL .= " LEFT JOIN t_grade AS C ON B.GRADE = C.ID";
        $SQL .= " LEFT JOIN t_grade AS D ON B.CLASS = D.ID";
        $SQL .= " LEFT JOIN t_academicdate AS E ON E.ID = B.SCHOOL_YEAR";
        $SQL .= " WHERE 1=1"; 
        $SQL .= " AND B.CLASS ='" . $classId . "'";
        $result=self::dbAccess()->fetchRow($SQL);  
        return $result?$result->TOTAL:'';
    }
        
    public static function getClassStudentHomeworkSend($subjectId, $classId, $teacherId, $homeworkId){ 
        $SQL = "";
        $SQL .= " SELECT COUNT(*) AS STUDENT_COUNT";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_homework AS B ON A.ID = B.SUBJECT";
        $SQL .= " LEFT JOIN t_staff  AS C ON C.ID = B.TEACHER";
        $SQL .= " LEFT JOIN t_student_homework AS D ON B.ID = D.HOMEWORK_ID";
        $SQL .= " LEFT JOIN t_student AS E ON E.ID = D.STUDENT_ID";
        $SQL .= " WHERE 1=1"; 
        $SQL .= " AND B.SUBJECT ='" . $subjectId . "'";
        $SQL .= " AND B.CLASS_ID ='" . $classId . "'";
        $SQL .= " AND B.TEACHER ='" . $teacherId . "'";
        
        if($homeworkId == ''){
              $SQL .= " AND D.HOMEWORK_ID = 0";
        }else{
              $SQL .= " AND D.HOMEWORK_ID ='" . $homeworkId . "'";
        }
        
        $result=self::dbAccess()->fetchRow($SQL);
        return $result?$result->STUDENT_COUNT:'';
    }
    
    /* ------------ */
        
    public static function jsonLoadAllSubjectHomeworks($params) {
     
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $gradesubjectId = isset($params["gradesubjectId"]) ? addText($params["gradesubjectId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        /* 
        switch(UserAuth::getUserType()){
            case 'INSTRUCTOR':
            case 'TEACHER':
                $teacherId = Zend_Registry::get('USER')->ID;
                break;
            default:
                $teacherId = "";
                break;  
        }
        */
        switch(UserAuth::getUserType()){
            case "SUPERADMIN":
            case "ADMIN":
                $result = self::getGradeSubjectHomework($gradesubjectId, $subjectId,$classId, $studentId, $globalSearch);
                break;
            case 'INSTRUCTOR':
            case 'TEACHER':
            case 'STUDENT':
                $result = self::getAllSubjectHomeworks($subjectId, $globalSearch, $classId, $teacherId);
                break;  
        }

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {
                $data[$key]["ID"] = $value->CONTENT_ID;
                $data[$key]["CONTENT"] = setShowText($value->CONTENT);
                $data[$key]["NAME"] = setShowText($value->NAME);
                $data[$key]["TEACHER"] = setShowText($value->TEACHER);
                $data[$key]["SUBJECT"] = setShowText($value->SUBJECT);
                $data[$key]["CLASS_ID"] = setShowText($value->CLASS_ID);  
                $data[$key]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$key]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$key]["SUBJECT_NAME"] = setShowText($value->SUBJECT_NAME);
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$key]["FULL_NAME"] = $value->LASTNAME." ".$value->FIRSTNAME;    
                }else{
                    $data[$key]["FULL_NAME"] = $value->FIRSTNAME." ".$value->LASTNAME;    
                }
                $data[$key]["STATUS"] = $value->STATUS;
                $data[$key]["DISABLED_BY"] = $value->DISABLED_BY;
                $data[$key]["COUNT"] = self::getClassStudentHomeworkSend($value->SUBJECT,$value->CLASS_ID,$value->TEACHER,$value->HOMEWORK_ID).'/'.self::getClassStudentHomework($value->CLASS_ID); 
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

    public static function jsonAddSubjectHomework($params) {

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";
        $title = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $content = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : ""; 

        if ($objectId != "new") {
            $SAVEDATA['NAME'] = addText($title);
            $SAVEDATA['CONTENT'] = $content;
            $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
            $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]); 
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);  
            self::dbAccess()->update('t_subject_homework', $SAVEDATA, $WHERE);
        } else { 
            $SAVEDATA['SUBJECT'] = $subjectId;
            $SAVEDATA['CLASS_ID'] = $classId;
            $SAVEDATA['TEACHER'] = Zend_Registry::get('USER')->ID;
            $SAVEDATA['NAME'] = addText($title);
            $SAVEDATA['CONTENT'] = $content;
            $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
            $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]); 
            self::dbAccess()->insert('t_subject_homework', $SAVEDATA); 
            $objectId = self::dbAccess()->lastInsertId(); 
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public static function jsonDeleteSubjectHomework($Id) { 
        self::dbAccess()->delete("t_subject_homework", "ID = '" . $Id . "'");

        return array(
            "success" => true
        );
    }

    public static function findSubjectHomeworkFromId($Id) { 
        $SELECTION_A = array(
            "ID AS ID"
            , "SUBJECT AS SUBJECT"
            , "CLASS_ID AS CLASS_ID"
            , "TEACHER AS TEACHER" 
            , "CONTENT AS CONTENT" 
            , "START_DATE AS START_DATE"
            , "END_DATE AS END_DATE"
            , "NAME AS NAME"
            , "STATUS AS STATUS"
            , "DISABLED_BY AS DISABLED_BY"  
        );
   
        $SELECTION_B = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT_STUDENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "HOMEWORK_ID AS HOMEWORK_ID"                       
        ); 
                
        $SELECTION_C = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        ); 
         
        $SQL = self::dbAccess()->select(); 
        $SQL->from(array('A' => 't_subject_homework'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_homework'), 'A.ID = B.HOMEWORK_ID', $SELECTION_B); 
        $SQL->joinLeft(array('C' => 't_subject'), 'A.SUBJECT = C.ID', $SELECTION_C);
        $SQL->where('A.ID = ?', $Id); 
        
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL); 
    }

    public static function jsonLoadSubjectHomework($Id) {
        $facette = self::findSubjectHomeworkFromId($Id); 
        if ($facette) {
            $data['STATUS'] = setShowText($facette->STATUS);
            $data['CONTENT'] = setShowText($facette->CONTENT);
            $data['START_DATE'] = getShowDate($facette->START_DATE);
            $data['END_DATE'] = getShowDate($facette->END_DATE);
            $data['NAME'] = setShowText($facette->NAME);
            $data['HOMEWORK_ID'] = setShowText($facette->HOMEWORK_ID);
            $data['SUBJECT_NAME'] = setShowText($facette->SUBJECT_NAME);
            $o = array(
                "success" => true
                , "data" => $data
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function jsonReleaseSubjectHomework($params) {  
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0; 
        $facette = self::findSubjectHomeworkFromId($objectId);   

        $data = array();
        if (isset($facette)) {
            switch ($facette->STATUS) {
                case 0:
                    $newStatus = 1;
                    $data['STATUS']       = 1;
                    $data['ENABLED_DATE'] = "'". getCurrentDBDateTime() ."'";
                    $data['ENABLED_BY']   = "'". Zend_Registry::get('USER')->CODE ."'";
                    break;
                case 1:
                    $newStatus = 0;
                    $data['STATUS']       = 0;
                    $data['DISABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                    $data['DISABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                    break;
            }
        }

        self::dbAccess()->update("t_subject_homework", $data, "ID='". $objectId ."'");
        return array("success" => true, "status" => $newStatus);
    }
    
    /* Soda */
        
    public static function jsonAddStudentHomework($params) {
                                                                 
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $homeworkId = isset($params["homeworkId"]) ? addText($params["homeworkId"]) : ""; 
        
        $checkStudentSubmit = self::findStudentHomeworkFromId($homeworkId,$studentId);

        if ($studentId)
            $SAVEDATA['STUDENT_ID'] = $studentId;
        
        if ($homeworkId)
            $SAVEDATA['HOMEWORK_ID'] = $homeworkId;
        
        if (isset($params["TITLE_NAME"]))
            $SAVEDATA['TITLE_NAME'] = addText($params["TITLE_NAME"]);
            
        if (isset($params["STUDENT_CONTENT"]))
            $SAVEDATA['CONTENT'] = addText($params["STUDENT_CONTENT"]);
            
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        
        if($checkStudentSubmit){
            $WHERE = self::dbAccess()->quoteInto("HOMEWORK_ID = ".$homeworkId." AND STUDENT_ID = '".$studentId."'");
            self::dbAccess()->update('t_student_homework', $SAVEDATA, $WHERE);    
        }else{    
            self::dbAccess()->insert('t_student_homework', $SAVEDATA);
        }
        
        return array(
            "success" => true
        );
    }
    
    public static function findStudentHomeworkFromId($Id,$stuId) {
        $SQL = self::dbAccess()->select()
                ->from("t_student_homework", array('*')) 
                ->where("HOMEWORK_ID = " . $Id )
                ->where("STUDENT_ID ='".$stuId."'");
                
        //error_log($SQL);                             
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function jsonLoadStudentHomework($Id,$stuId) {
        $facette = self::findStudentHomeworkFromId($Id,$stuId);
        if ($facette) {
            $data['TITLE_NAME'] = setShowText($facette->TITLE_NAME);
            $data['STUDENT_CONTENT'] = setShowText($facette->CONTENT);
            $data['CREATED_DATE'] = getShowDateTime($facette->CREATED_DATE);
            $o = array(
                "success" => true
                , "data" => $data
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }
    
    public static function jsonLoadStudentSubjectHomework($params) { 
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50"; 
        $Id = isset($params["objectId"]) ? addText($params["objectId"]) : ""; 
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $result = self::getAllStudentSubjectHomeworks($Id, $globalSearch);    
        $data = array();                  
        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {
                $data[$key]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$key]["TITLE_NAME"] = $value->TITLE_NAME;
                $data[$key]["CONTENT_STUDENT"] = $value->CONTENT_STUDENT;
                $data[$key]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                $data[$key]["SUBJECT_NAME"] = $value->SUBJECT_NAME;
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$key]["FULL_NAME"] = $value->LASTNAME_STUDENT." ".$value->FIRSTNAME_STUDENT;
                }else{
                    $data[$key]["FULL_NAME"] = $value->FIRSTNAME_STUDENT." ".$value->LASTNAME_STUDENT;
                }
                $data[$key]["CLASS_ID"] = $value->CLASS_ID;
                $data[$key]["SUBJECT"] = $value->SUBJECT;  
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
    
    public static function getAllStudentSubjectHomeworks($Id, $globalSearch = false) {
    
        $SELECTION_A = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_B = array(
            "ID AS CONTENT_ID" 
            , "CLASS_ID AS CLASS_ID"
            , "SUBJECT AS SUBJECT"
        );

        $SELECTION_C = array(
            "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"                       
        );
        
        $SELECTION_D = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT_STUDENT"
            , "CREATED_DATE AS CREATED_DATE"                       
        );
        
        $SELECTION_E = array(
            "FIRSTNAME AS FIRSTNAME_STUDENT"
            , "LASTNAME AS LASTNAME_STUDENT"                       
        );
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_homework'), 'A.ID=B.SUBJECT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_staff'), 'C.ID = B.TEACHER', $SELECTION_C);  
        $SQL->joinLeft(array('D' => 't_student_homework'), 'B.ID = D.HOMEWORK_ID', $SELECTION_D);  
        $SQL->joinLeft(array('E' => 't_student'), 'E.ID = D.STUDENT_ID', $SELECTION_E); 
        
        if ($Id) {
            $SQL->where('D.HOMEWORK_ID = ?', $Id);
        }
        if ($globalSearch) {
            $SEARCH = " ((A.NAME LIKE '" . $globalSearch . "%')"; 
            $SEARCH .= " OR (E.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (E.LASTNAME LIKE '" . $globalSearch . "%')"; 
            $SEARCH .= " OR (D.TITLE_NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        
        $SQL->order('B.START_DATE DESC'); 
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function findStudentInfoHomeworkFromId($stuId) {
        $SQL = self::dbAccess()->select()
                ->from("t_student_homework", array('*'))
                ->where("STUDENT_ID = '" . $stuId . "'");
        return self::dbAccess()->fetchRow($SQL);
    }
        
    public static function findStudentInfoSubjectHomeworkFromId($stuId) {

        $SELECTION_A = array(
            "ID AS ID"
            , "SUBJECT AS SUBJECT"
            , "CLASS_ID AS CLASS_ID"  
        );
   
        $SELECTION_B = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "HOMEWORK_ID AS HOMEWORK_ID"                       
        );         

        $SQL = self::dbAccess()->select(); 
        $SQL->from(array('A' => 't_subject_homework'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_student_homework'), 'A.ID = B.HOMEWORK_ID', $SELECTION_B);
        $SQL->where('B.STUDENT_ID = ?', $stuId);
        return self::dbAccess()->fetchRow($SQL);
    }
        
    public static function jsonLoadStudentHomeworkInfo($stuId) {
        $facette = self::findStudentInfoSubjectHomeworkFromId($stuId);
        if ($facette) {
            $data['TITLE_NAME'] = setShowText($facette->TITLE_NAME);
            $data['STUDENT_CONTENT'] = setShowText($facette->CONTENT);
            $data['SUBJECT'] = setShowText($facette->SUBJECT);
            $data['CLASS_ID'] = setShowText($facette->CLASS_ID);
            $data['CREATED_DATE'] = getShowDateTime($facette->CREATED_DATE); 

            $o = array(
                "success" => true
                , "data" => $data
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }
        
    public static function findStudentSubjectHomeworkFromId($Id) {
        $SQL = "SELECT *";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_homework AS B ON A.ID=B.SUBJECT";
        $SQL .= " LEFT JOIN t_staff AS C ON C.ID = B.TEACHER";
        $SQL .= " LEFT JOIN t_student_homework AS D ON B.ID = D.HOMEWORK_ID";
        $SQL .= " LEFT JOIN t_student_homework AS E ON E.ID = D.STUDENT_ID";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND D.STUDENT_ID ='" . $Id . "'"; 
        return self::dbAccess()->fetchRow($SQL);
    }
        
    public static function jsonRemoveStudentSubjectHomework($params) { 
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $academicObject = self::findStudentSubjectHomeworkFromId($studentId);
        if ($academicObject) { 
            self::dbAccess()->delete('t_student_homework', array(
                "STUDENT_ID = '" . $studentId . "'"
            ));
        }
        return array("success" => true);
    }
    
    public static function getTrainingStudentHomework($trainingId){
        $SQL = "";
        $SQL .= " SELECT COUNT(*) AS TOTAL";
        $SQL .= " FROM t_student AS A";
        $SQL .= " LEFT JOIN t_student_training as B ON A.ID=B.STUDENT";
        $SQL .= " LEFT JOIN t_training AS C ON C.ID=B.TRAINING"; 
        $SQL .= " LEFT JOIN t_training AS D ON D.ID=B.TERM"; 
        $SQL .= " LEFT JOIN t_training AS E ON E.ID=B.LEVEL"; 
        $SQL .= " LEFT JOIN t_training AS F ON F.ID=B.PROGRAM"; 
        $SQL .= " WHERE 1=1"; 
        $SQL .= " AND B.TRAINING ='" . $trainingId . "'";
        $result=self::dbAccess()->fetchRow($SQL);  
        return $result?$result->TOTAL:'';  
    }
        
    public static function getTrainingStudentHomeworkSend($subjectId, $trainingId, $teacherId,$homeworkId){
        $SQL = "";
        $SQL .= " SELECT COUNT(*) AS STUDENT_COUNT";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_homework AS B ON A.ID = B.SUBJECT";
        $SQL .= " LEFT JOIN t_staff  AS C ON C.ID = B.TEACHER";
        $SQL .= " LEFT JOIN t_student_homework AS D ON B.ID = D.HOMEWORK_ID";
        $SQL .= " LEFT JOIN t_student AS E ON E.ID = D.STUDENT_ID";
        $SQL .= " WHERE 1=1"; 
        $SQL .= " AND B.SUBJECT ='" . $subjectId . "'";
        $SQL .= " AND B.CLASS_ID ='" . $trainingId . "'";
        $SQL .= " AND B.TEACHER ='" . $teacherId . "'";
        
        if($homeworkId == ''){
              $SQL .= " AND D.HOMEWORK_ID = 0";
        }else{
              $SQL .= " AND D.HOMEWORK_ID ='" . $homeworkId . "'";
        }

        $result=self::dbAccess()->fetchRow($SQL);
        return $result?$result->STUDENT_COUNT:'';
    }
    
    public static function jsonLoadAllSubjectTrainingHomeworks($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $homeworkId = isset($params["homeworkId"]) ? addText($params["homeworkId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        
        switch(UserAuth::getUserType()){
            case 'INSTRUCTOR':
            case 'TEACHER':
                $teacherId = Zend_Registry::get('USER')->ID;
                break;
            default:
                $teacherId = "";
                break;  
        }

        switch(UserAuth::getUserType()){
            case "SUPERADMIN":
            case "ADMIN":
                $result = self::getTrainingSubjectHomework($objectId, $subjectId, $studentId, $globalSearch);
                break;
            case 'INSTRUCTOR':
            case 'TEACHER':
            case 'STUDENT':
            case 'GUARDIAN':
                $result = self::getAllSubjectTriningHomeworks($subjectId, $globalSearch, $trainingId, $teacherId);
                break;  
        }

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $key => $value) {
                $data[$key]["ID"] = $value->CONTENT_ID;
                $data[$key]["CONTENT"] = setShowText($value->CONTENT);
                $data[$key]["NAME"] = setShowText($value->NAME);
                $data[$key]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$key]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$key]["SUBJECT_NAME"] = setShowText($value->SUBJECT_NAME);
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$key]["FULL_NAME"] = $value->LASTNAME." ".$value->FIRSTNAME;
                }else{
                    $data[$key]["FULL_NAME"] = $value->FIRSTNAME." ".$value->LASTNAME;
                }
                $data[$key]["STATUS"] = $value->STATUS;
                $data[$key]["DISABLED_BY"] = $value->DISABLED_BY;
                $data[$key]["COUNT"] = self::getTrainingStudentHomeworkSend($value->SUBJECT,$value->CLASS_ID,$value->TEACHER,$value->HOMEWORK_ID).'/'.self::getTrainingStudentHomework($value->CLASS_ID); 
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
        
    public static function getAllSubjectTriningHomeworks($subjectId, $globalSearch, $trainingId, $teacherId) {

        $SELECTION_A = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_B = array(
            "ID AS CONTENT_ID"
            , "SUBJECT AS SUBJECT"
            , "CLASS_ID AS CLASS_ID"
            , "TEACHER AS TEACHER" 
            , "CONTENT AS CONTENT" 
            , "START_DATE AS START_DATE"
            , "END_DATE AS END_DATE"
            , "NAME AS NAME"
            , "STATUS AS STATUS"
            , "DISABLED_BY AS DISABLED_BY"  
        );

        $SELECTION_C = array(
            "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"                       
        );
                    
        $SELECTION_D = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT_STUDENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "HOMEWORK_ID AS HOMEWORK_ID"                       
        );         

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject_homework'), 'A.ID=B.SUBJECT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_staff'), 'C.ID = B.TEACHER', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_student_homework'), 'B.ID = D.HOMEWORK_ID', $SELECTION_D);
        if($subjectId != '')
            $SQL->where('B.SUBJECT = ?', $subjectId);
        $SQL->where('B.CLASS_ID = ?', $trainingId);

        switch (UserAuth::getUserType()) {
            case "TEACHER":
                $SQL->where('B.TEACHER = ?', $teacherId);
                break;   
            case "STUDENT":
                $SQL->where('B.STATUS = ?', 1);
                break;  
        }
        
        if ($globalSearch) {
            $SEARCH = " ((A.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (B.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (C.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (C.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        
        $SQL->order('B.START_DATE DESC'); 

        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function getTrainingSubjectHomework($objectId, $subjectId, $studentId, $globalSearch = false){
        
        $SELECTION_A = array(
            "ID AS ID"
        );
        
        $SELECTION_B = array(
            "ID AS SUBJECT_ID"
            , "NAME AS SUBJECT_NAME"
        );

        $SELECTION_C = array(
            "ID AS CONTENT_ID"
            , "SUBJECT AS SUBJECT"
            , "CLASS_ID AS CLASS_ID"
            , "TEACHER AS TEACHER" 
            , "CONTENT AS CONTENT" 
            , "START_DATE AS START_DATE"
            , "END_DATE AS END_DATE"
            , "NAME AS NAME"
            , "STATUS AS STATUS"
            , "DISABLED_BY AS DISABLED_BY"  
        );

        $SELECTION_D = array(
            "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"                       
        );
                    
        $SELECTION_E = array( 
            "STUDENT_ID AS STUDENT_ID"
            , "TITLE_NAME AS TITLE_NAME"
            , "CONTENT AS CONTENT_STUDENT"
            , "CREATED_DATE AS CREATED_DATE"
            , "HOMEWORK_ID AS HOMEWORK_ID"                       
        );         
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_training_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject'), 'B.ID = A.SUBJECT', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_subject_homework'), 'B.ID=C.SUBJECT', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_staff'), 'D.ID = C.TEACHER', $SELECTION_D);
        $SQL->joinLeft(array('E' => 't_student_homework'), 'C.ID = E.HOMEWORK_ID', $SELECTION_E);
        if ($objectId)
            $SQL->where('A.ID = ?', $objectId); 
        if ($subjectId)
            $SQL->where('C.SUBJECT = ?', $subjectId);
        if ($studentId)
            $SQL->where('E.STUDENT_ID = ?', $studentId);
            
        if ($globalSearch) {
            $SEARCH = " ((B.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (D.FIRSTNAME LIKE '" . $globalSearch . "%')"; 
            $SEARCH .= " OR (D.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (C.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }    
        $SQL->order('C.START_DATE DESC'); 
        return self::dbAccess()->fetchAll($SQL);
    }    
    
    public static function jsonAddTrainingSubjectHomework($params) {

        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";
        $title = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $content = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if ($objectId != "new") {
            $SAVEDATA['NAME'] = addText($title);
            $SAVEDATA['CONTENT'] = addText($content);
            $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
            $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]);
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_subject_homework', $SAVEDATA, $WHERE);
        } else {
            $SAVEDATA['SUBJECT'] = $subjectId;
            $SAVEDATA['CLASS_ID'] = $trainingId;
            $SAVEDATA['TEACHER'] = Zend_Registry::get('USER')->ID;
            $SAVEDATA['NAME'] = addText($title);
            $SAVEDATA['CONTENT'] = addText($content);
            $SAVEDATA['START_DATE'] = setDate2DB($params["START_DATE"]);
            $SAVEDATA['END_DATE'] = setDate2DB($params["END_DATE"]);
            self::dbAccess()->insert('t_subject_homework', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }
                
 }

?>