<?php
///////////////////////////////////////////////////////////
//@Chung veng Web Developer
//Date: 22.06.2013
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/club/ClubDBAccess.php';
require_once 'models/app_university/student/StudentDBAccess.php';

require_once setUserLoacalization();

class StudentClubDBAccess extends ClubDBAccess {

    static function getInstance() {

        return new StudentClubDBAccess();
    } 
    public static function sqlStudentClub($globalSearch, $clubId, $studentId) {
        $CLUB_OBJECT = self::findClubFromId($clubId);
        $SELECT_A = array(
            'CODE'
            , 'ID'
            , 'ID AS STUDENT_ID'
            , 'STUDENT_SCHOOL_ID'
            , 'CODE'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'FIRSTNAME_LATIN'
            , 'LASTNAME_LATIN'
            , 'CREATED_DATE'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );
         $SELECT_B = array(
            'ID AS OBJECT_ID'   
        );      
        $SELECT_C = array('NAME AS CLUB_NAME');                                                     
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_club'), 'A.ID=B.STUDENT',$SELECT_B);
        $SQL->joinLeft(array('C' => 't_club'), 'C.ID=B.CLUB',$SELECT_C);           
        if ($CLUB_OBJECT) {  
            $SQL->where('B.CLUB = ?', $clubId); 
        };      
        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        if ($studentId)      
            $SQL->where("B.STUDENT='" . $studentId . "'");
            
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY A.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY A.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY A.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }
    public static function jsonStudentClub($params, $isJson = true) {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $clubId = isset($params["objectId"]) ? addText($params["objectId"]) : ""; 
        $resultRows = self::sqlStudentClub($globalSearch, $clubId, $studentId);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
                $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["CLUB_NAME"] = setShowText($value->CLUB_NAME);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);
                
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson == true) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }    
    ///teacher   
     public static function sqlTeacherClub($globalSearch, $clubId, $studentId) {

        $CLUB_OBJECT = self::findClubFromId($clubId);

        $SELECT_A = array(
            'CODE'
            , 'ID'
            , 'ID AS STAFF_ID'
            , 'STAFF_SCHOOL_ID'
            , 'CODE'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'FIRSTNAME_LATIN'
            , 'LASTNAME_LATIN'
            , 'CREATED_DATE'
            , 'PHONE'
            , 'EMAIL'
            , 'GENDER'
            , 'MOBIL_PHONE'
            , 'DATE_BIRTH'
        );
         $SELECT_B = array(
            'ID AS OBJECT_ID'   
        );
       
        $SELECT_C = array('NAME AS CLUB_NAME');                                             
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_club'), 'A.ID=B.TEACHER',$SELECT_B);
        $SQL->joinLeft(array('C' => 't_club'), 'C.ID=B.CLUB',$SELECT_C);           
        if ($CLUB_OBJECT) {  
            $SQL->where('B.CLUB = ?', $clubId); 
        };      
        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        if ($studentId)       
            $SQL->where("B.TEACHER='" . $studentId . "'");
        
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY A.STAFF_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY A.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY A.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
     }   
     public static function jsonTeacherClubs($params, $isJson = true) {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $clubId = isset($params["objectId"]) ? addText($params["objectId"]) : "";   
        $resultRows = self::sqlTeacherClub($globalSearch, $clubId, $teacherId);
        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {
                $data[$i]["ID"] = $value->OBJECT_ID;
                $data[$i]["STAFF_ID"] = $value->STAFF_ID;
                $data[$i]["STAFF_SCHOOL_ID"] = $value->STAFF_SCHOOL_ID;
                $data[$i]["CODE"] = setShowText($value->CODE);
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["CLUB_NAME"] = setShowText($value->CLUB_NAME);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["MOBIL_PHONE"] = setShowText($value->MOBIL_PHONE);
                $data[$i]["GENDER"] = getGenderName($value->GENDER);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["CREATED_DATE"] = getShowDate($value->CREATED_DATE);                
                $i++;
            }
        }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        if ($isJson == true) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }
    ///
    public static function jsonListStudentInSchool($params) {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $clubId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $SELECT_A = array(
            'CODE'
            , 'ID AS STUDENT_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'GENDER'
            , 'DATE_BIRTH'
        );

        $SELECT_C = array('NAME AS CLUB_NAME');
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_club'), 'A.ID=B.STUDENT', array());
        $SQL->joinLeft(array('C' => 't_club'), 'C.ID=B.CLUB', $SELECT_C);
        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY A.STUDENT_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY A.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY A.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL->__toString());
        $SQL->group("A.ID");
        $resultRows = self::dbAccess()->fetchAll($SQL);
        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                if (!self::checkStudentInClubEducation($value->STUDENT_ID, $clubId)) {

                    $data[$i]["ID"] = $value->STUDENT_ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);  
                    if(!SchoolDBAccess::displayPersonNameInGrid()){
                        $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    }else{
                        $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);

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
    public static function checkStudentInClubEducation($studentId, $clubId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_club", array("C" => "COUNT(*)"));
        if ($studentId)
            $SQL->where("STUDENT = ?",$studentId);
        if ($clubId)
            $SQL->where("CLUB = '" . $clubId . "'");
        $result = self::dbAccess()->fetchRow($SQL); 
        return $result ? $result->C : 0;
    }
   /// teacher
    public static function actionTeacherToClub($params) {
        $SAVEDATA = array();
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $teacher = isset($params["id"]) ? addText($params["id"]) : "";
        $clubId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        
        if ($field == "ASSIGNEDs") {
            if ($newValue) {
                $SAVEDATA["TEACHER"] = $teacher;
                $SAVEDATA["CLUB"] = $clubId;
                self::dbAccess()->insert('t_student_club', $SAVEDATA);
            }
        }
        $o = array(
            "success" => true
        );
        return $o;
    }  
    public static function actionRemoveTeacherClub($params) {
        $id = isset($params["id"]) ? addText($params["id"]) : "0";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "0";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : ""; 
        
        if ($field == "CHECKEDs") {
            if ($newValue) {
                if ($objectId) 
                    self::dbAccess()->delete('t_student_club', array("ID='" . $id . "'"));
            }
        }

        return array(
            "success" => true
        );
    }
    public static function jsonListTeacherInSchool($params) {
        $data = array();
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $clubId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SELECT_A = array(
            'CODE'
            , 'ID AS TEACHER_ID'
            , 'FIRSTNAME'
            , 'LASTNAME'
            , 'GENDER'
        );
        $SELECT_C = array('NAME AS CLUB_NAME');

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff'), $SELECT_A);
        $SQL->joinLeft(array('B' => 't_student_club'), 'A.ID=B.TEACHER', array());
        $SQL->joinLeft(array('C' => 't_club'), 'C.ID=B.CLUB', $SELECT_C);

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY A.STAFF_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY A.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY A.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {

                if (!self::checkTeacherInClubEducation($value->TEACHER_ID, $clubId)) {

                    $data[$i]["ID"] = $value->TEACHER_ID;
                    $data[$i]["CODE"] = setShowText($value->CODE);  
                    if(!SchoolDBAccess::displayPersonNameInGrid()){
                        $data[$i]["TEACHER"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                    }else{
                        $data[$i]["TEACHER"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                    }
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);

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
    public static function checkTeacherInClubEducation($clubId, $trainingId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_club", array("C" => "COUNT(*)"));
        if ($clubId)
            $SQL->where("TEACHER = '" . $clubId . "'");
        if ($trainingId)
            $SQL->where("CLUB = '" . $trainingId . "'");
        $result = self::dbAccess()->fetchRow($SQL); 
        return $result ? $result->C : 0;
    }
    ///
    public static function actionStudentToClub($params) {

        $SAVEDATA = array();

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $studentId = isset($params["id"]) ? addText($params["id"]) : "";
        $clubId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        
        if ($field == "ASSIGNED") {
            if ($newValue) {
                $SAVEDATA["STUDENT"] = $studentId;
                $SAVEDATA["CLUB"] = $clubId;
                self::dbAccess()->insert('t_student_club', $SAVEDATA); 
            }
        }

        $o = array(
            "success" => true
        );
        return $o;
    }
     public static function actionRemoveStudentClub($params) {
        
        $id = isset($params["id"]) ? addText($params["id"]) : "0";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "0";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : ""; 
        
        if ($field == "CHECKED") {
            if ($newValue) {
                if ($objectId) 
                    self::dbAccess()->delete('t_student_club', array("ID='" . $id . "'"));
            }
        }

        return array(
            "success" => true
        );
    }

 
}

?>