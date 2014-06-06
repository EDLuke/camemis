<?php

//////////////////////////////////////////////////////////////////////////
//@THORN Visal
//Date: 16.12.2013
//////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

class StudentPreschoolDBAccess {

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

    public static function findObjectFromIdAttach($objectId){
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_preschooltype'), array('*'));
        $SQL->joinRight(array('B' => 't_student_preschool'), 'A.PRESTUDENT = B.ID', array('ID AS STUDENT_ID', 'FIRSTNAME AS STUDENT_FIRSTNAME', 'LASTNAME AS STUDENT_LASTNAME'));
        $SQL->where("A.ID='" . $objectId . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function findObjectFromId($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
      
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_preschool'), array('*'));
        $SQL->joinLeft(array('B' => 't_members'), 'A.CREATED_BY = B.ID', array('FIRSTNAME AS MEMBER_FIRSTNAME', 'LASTNAME AS MEMBER_LASTNAME'));
        $SQL->where("A.STUDENT_INDEX='" . $objectId . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function getObjectDataFromId($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $type = isset($params["type"]) ? addText($params["type"]) : '';
        $result = self::findObjectFromId($params);
        $data = array();
        
        if ($result) {
            $data['STUDENT_ID'] = $result->ID;
            $data['FIRSTNAME'] = $result->FIRSTNAME;
            $data['LASTNAME'] = $result->LASTNAME;
            $data['FIRSTNAME_LATIN'] = $result->FIRSTNAME_LATIN;
            $data['LASTNAME_LATIN'] = $result->LASTNAME_LATIN;
            $data['PHONE'] = $result->PHONE;
            $data['ADDRESS'] = $result->ADDRESS;
            $data['EMAIL'] = $result->EMAIL;
            $data['GENDER'] = $result->GENDER;
            $data['DATE_BIRTH'] = getShowDate($result->DATE_BIRTH);
            $data['CREATED_DATE'] = getShowDate($result->CREATED_DATE);
            if (!SchoolDBAccess::displayPersonNameInGrid()) {
                $data["CREATED_BY"] = setShowText($result->MEMBER_FIRSTNAME) . " " . setShowText($result->MEMBER_LASTNAME);
            } else {
                $data["CREATED_BY"] = setShowText($result->MEMBER_LASTNAME) . " " . setShowText($result->MEMBER_FIRSTNAME);
                
            }
        }
        
        return $data;
    }
    
    public static function jsonLoadStudentPreschool($params) {
        $result = self::findObjectFromId($params);
        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getObjectDataFromId($params)
            );
        }else{
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        
        return $o;
    }
    
    public static function findObjectTypeFromId($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : '';
        
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_preschooltype'), array('*'));
        $SQL->where("PRESTUDENT='" . $objectId . "'");
        $SQL->where("OBJECT_TYPE='" . $objectType . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);

    }
    
    public static function getObjectTypeFromId($params) {
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : '';
        
        $result = self::findObjectTypeFromId($params);
        $data = array();
        switch($objectType){
            case "TESTING":
                if ($result) {
                    $data['TESTING_TYPE'] = $result->CAMEMIS_TYPE;
                    $data['SCORE'] = $result->SCORE;
                    $data['DESCRIPTION'] = $result->DESCRIPTION;
                }
                break;
            case "APPLICATION":
                if ($result) {
                    $data['APPLICATION_TYPE'] = $result->CAMEMIS_TYPE;
                    $data['DESCRIPTION'] = $result->DESCRIPTION;
                }
                break;
            case "REFERENCE":
                if ($result) {
                    $data['REFERENCE_TYPE'] = $result->CAMEMIS_TYPE;
                    $data['DESCRIPTION'] = $result->DESCRIPTION;
                    $data['REF_FIRSTNAME'] = $result->FIRSTNAME;
                    $data['REF_LASTNAME'] = $result->LASTNAME;
                    $data['REF_PHONE'] = $result->PHONE;
                    $data['REF_EMAIL'] = $result->EMAIL;
                }
                break;
        }
        
        
        return $data;
    }
    
    public static function jsonLoadTypePreschool($params) {
        $result = self::findObjectTypeFromId($params);
        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getObjectTypeFromId($params)
            );
        }else{
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        
        return $o;
    }
    
    public static function checkReferenceOfStudent($Id) {
     
        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_student_preschooltype";
        $SQL .= " WHERE";
        $SQL .= " PRESTUDENT = '" . $Id . "'";
        $SQL .= " AND OBJECT_TYPE = 'REFERENCE'";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    
    public static function jsonSaveTypePreschool($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : '';
        $stdId = isset($params["stdId"]) ? addText($params["stdId"]) : '';
        $SAVEDATA = array();
        
        
                if (isset($params["REFERENCE_TYPE"]))
                    $SAVEDATA["CAMEMIS_TYPE"] = addText($params["REFERENCE_TYPE"]);
                    
                if (isset($params["DESCRIPTION"]))
                    $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);
                    
                if (isset($params["REF_FIRSTNAME"]))
                    $SAVEDATA["FIRSTNAME"] = addText($params["REF_FIRSTNAME"]);
                
                if (isset($params["REF_LASTNAME"]))
                    $SAVEDATA["LASTNAME"] = addText($params["REF_LASTNAME"]);
                    
                if (isset($params["REF_PHONE"]))
                    $SAVEDATA["PHONE"] = addText($params["REF_PHONE"]);
                    
                if (isset($params["REF_EMAIL"]))
                    $SAVEDATA["EMAIL"] = addText($params["REF_EMAIL"]);
          
        if($objectId != 'new'){
             $WHERE[] = "PRESTUDENT = '" . $objectId . "'";
             $WHERE[] = "OBJECT_TYPE = '".$objectType."'";
        
            self::dbAccess()->update('t_student_preschooltype', $SAVEDATA, $WHERE);
        }else{
            $SAVEDATA["PRESTUDENT"] = addText($stdId);
            $SAVEDATA["OBJECT_TYPE"] = addText($objectType);
            self::dbAccess()->insert('t_student_preschooltype', $SAVEDATA);
        }
       
        
        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }
     
    public static function jsonSaveStudentPreschool($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        
        $date = isset($params["DATE_BIRTH"])? setDate2DB($params["DATE_BIRTH"]):'';
  
        $SAVEDATA = array();
        $GETDATA = array();

        if (isset($params["FIRSTNAME"]))
            $SAVEDATA["FIRSTNAME"] = addText($params["FIRSTNAME"]);

        if (isset($params["LASTNAME"]))
            $SAVEDATA["LASTNAME"] = addText($params["LASTNAME"]);
            
        if (isset($params["FIRSTNAME_LATIN"]))
            $SAVEDATA["FIRSTNAME_LATIN"] = addText($params["FIRSTNAME_LATIN"]);

        if (isset($params["LASTNAME_LATIN"]))
            $SAVEDATA["LASTNAME_LATIN"] = addText($params["LASTNAME_LATIN"]);

        if (isset($params["PHONE"]))
            $SAVEDATA["PHONE"] = addText($params["PHONE"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA["EMAIL"] = addText($params["EMAIL"]);

        if (isset($params["ADDRESS"]))
            $SAVEDATA["ADDRESS"] = addText($params["ADDRESS"]);
            
        if (isset($params["GENDER"]))
            $SAVEDATA["GENDER"] = addText($params["GENDER"]);
            
        if (isset($date))    
            $SAVEDATA["DATE_BIRTH"] = $date;
            
        if ($objectId == "new") {
            $SAVEDATA['ID'] = generateGuid();
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
            
            self::dbAccess()->insert('t_student_preschool', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE[] = "STUDENT_INDEX = '" . $objectId . "'";
            self::dbAccess()->update('t_student_preschool', $SAVEDATA, $WHERE);
        }
        
        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }
    
    public function jsonRemoveStudentPreschool($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $stdId = isset($params["stdId"]) ? addText($params["stdId"]) : '';
        
        self::dbAccess()->delete('t_student_preschooltype', array("PRESTUDENT='" . $stdId . "'"));
        self::dbAccess()->delete('t_student_preschool', array("STUDENT_INDEX='" . $objectId . "'"));
            
        return array(
            "success" => true
        );
    }
    
    public static function sqlGetStudentPreschool($params) {
        
        $startDate = "";
        $endDate = "";
        $objectId = isset($params["objectId"])? addText($params["objectId"]) : "";
        $gender = isset($params["GENDER"]) ? addText($params["GENDER"]) : "";
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : "";
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : "";
        $address = isset($params["ADDRESS"]) ? addText($params["ADDRESS"]) : "";
        $phone = isset($params["PHONE"]) ? addText($params["PHONE"]) : "";
        $email = isset($params["EMAIL"]) ? addText($params["EMAIL"]) : "";
        $session = isset($params["SESSION_EVENT"]) ? addText($params["SESSION_EVENT"]) : "";
        $applicationStatus = isset($params["APPLICATION_STATUS"]) ? addText($params["APPLICATION_STATUS"]) : "";
        $degree = isset($params["DEGREE_TYPE"]) ? addText($params["DEGREE_TYPE"]) : "";
        $dob = isset($params["DATE_BIRTH"]) ? addText($params["DATE_BIRTH"]) : "";
        $informationType = isset($params["INFORMATION_TYPE"]) ? addText($params["INFORMATION_TYPE"]) : "";
        $infoType = isset($params["infoType"]) ? addText($params["infoType"]) : "";
        
        if($informationType == "APPLICATION"){
            $sDate = isset($params["APPLICATION_START_DATE"]) ? $params["APPLICATION_START_DATE"] : "";
            $eDate = isset($params["APPLICATION_END_DATE"]) ? addText($params["APPLICATION_END_DATE"]) : "";
            $startDate = setDate2DB($sDate);
            $endDate = setDate2DB($eDate);
            $cametype = isset($params["APPLICATION_TYPE"]) ? addText($params["APPLICATION_TYPE"]) : "";
        }else if($informationType == "REFERENCE"){
            $cametype = isset($params["REFERENCE_TYPE"]) ? addText($params["REFERENCE_TYPE"]) : "";
        }else{
            $sDate = isset($params["TESTING_START_DATE"]) ? $params["TESTING_START_DATE"] : "";
            $eDate = isset($params["TESTING_END_DATE"]) ? addText($params["TESTING_END_DATE"]) : "";
            $startDate = setDate2DB($sDate);
            $endDate = setDate2DB($eDate);
            $cametype = isset($params["TESTING_TYPE"]) ? addText($params["TESTING_TYPE"]) : "";
        }

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_preschool'), array('*'));
        if($informationType == "CLEAR"){
            //$SQL->JOIN_OUTER('t_student_preschooltype AS B', 'A.ID= B.PRESTUDENT WHERE B.PRESTUDENT Is NULL');
            $SQL->joinLeft(array('B' => 't_student_preschooltype'), 'A.id = B.PRESTUDENT', array('ID AS PRESCHOOLTYPE_ID', 'OBJECT_TYPE AS PRESCHOOL_OBJECT_TYPE', 'DESCRIPTION AS PRESCHOOLTYPE_DES', 'CAMEMIS_TYPE AS PRESCHOOLTYPE_CAM',  'CREATED_DATE', 'CREATED_BY', 'SCORE', 'DEGREE_TYPE', 'APPLICATION_STATUS', 'SESSION_EVENT'));
            //$SQL->where("B.OBJECT_TYPE = 'APPLICATION' && B.OBJECT_TYPE = 'TESTING'");
            $SQL->where('B.PRESTUDENT IS NULL');
        }else{
             $SQL->joinLeft(array('B' => 't_student_preschooltype'), 'A.ID= B.PRESTUDENT', array('ID AS PRESCHOOLTYPE_ID', 'OBJECT_TYPE AS PRESCHOOL_OBJECT_TYPE', 'DESCRIPTION AS PRESCHOOLTYPE_DES', 'CAMEMIS_TYPE AS PRESCHOOLTYPE_CAM',  'CREATED_DATE', 'CREATED_BY', 'SCORE', 'DEGREE_TYPE', 'APPLICATION_STATUS', 'SESSION_EVENT'));
            if ($informationType){
                $SQL->where("B.OBJECT_TYPE = '" . $informationType. "'");
                //$SQL->where("B.CAMEMIS_TYPE != 0");
            }
        }
        
        $SQL->joinLeft(array('C' => 't_camemis_type'), 'B.CAMEMIS_TYPE = C.ID', array('NAME AS CAM_NAME', 'OBJECT_TYPE'));
             $SQL->joinLeft(array('D' => 't_members'), 'B.CREATED_BY = D.ID', array('FIRSTNAME AS MEMBER_FIRSTNAME', 'LASTNAME AS MEMBER_LASTNAME'));
             $SQL->joinLeft(array('E' => 't_camemis_type'), 'B.DEGREE_TYPE = E.ID', array('NAME AS DEGRE_NAME', 'OBJECT_TYPE'));
             $SQL->joinLeft(array('F' => 't_camemis_type'), 'B.APPLICATION_STATUS = F.ID', array('NAME AS APPLICATION_STATUS', 'OBJECT_TYPE'));
        
        if ($startDate and $endDate) {
                $SQL->where("B.CREATED_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'");
                //$SQL->where("C.OBJECT_TYPE ='" . $infoType . "'");
        }
            
        if ($gender)
            $SQL->where("A.GENDER ='" . $gender . "'");
        
        $DateOfBirth = setDate2DB($dob);
        if ($DateOfBirth)
            $SQL->where("A.DATE_BIRTH ='" . $DateOfBirth . "'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '" . $lastname . "%'");

        if ($address)
            $SQL->where("A.ADDRESS LIKE '" . $address . "%'");
            
        if ($phone)
            $SQL->where("A.PHONE LIKE '" . $phone . "%'");
            
        if ($email)
            $SQL->where("A.EMAIL LIKE '" . $email . "%'");
            
        if ($degree)
            $SQL->where("B.DEGREE_TYPE LIKE '" . $degree . "%'");
            
        if ($session)
            $SQL->where("B.SESSION_EVENT LIKE '" . $session . "%'");
            
        if ($applicationStatus)
            $SQL->where("B.APPLICATION_STATUS LIKE '" . $applicationStatus . "%'");
        
        if ($cametype)
                $SQL->where("B.CAMEMIS_TYPE = '" . $cametype . "'");
        

        if ($infoType)
            $SQL->where("C.OBJECT_TYPE = '".$infoType."'");
            
        //$SQL .= "GROUP BY A.ID";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonSearchStudentPreschool($params, $isJson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $infoType = isset($params["infoType"]) ? addText($params["infoType"]) : "";

        $data = array();
        $i = 0;

        $result = self::sqlGetStudentPreschool($params);
        if ($result) {
            
            foreach ($result as $value) {
                $data[$i]['ID'] = $value->ID;
                $data[$i]['STUDENT_INDEX'] = $value->STUDENT_INDEX;
                $data[$i]['ID_PRESCHOOLTYPE'] = $value->PRESCHOOLTYPE_ID;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["STUDENT_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME)." "."(".getGenderName($value->GENDER).")";
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_FIRSTNAME) . " " . setShowText($value->MEMBER_LASTNAME);
                } else {
                    $data[$i]["STUDENT_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME)." "."(".getGenderName($value->GENDER).")";
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                    
                }
                
                
                $data[$i]['CAMEMIS_TYPE'] = $value->CAM_NAME;
                $data[$i]['DEGREE_TYPE'] = $value->DEGRE_NAME;
                $data[$i]['SESSION_EVENT'] = $value->SESSION_EVENT;
                $data[$i]['APPLICATION_STATUS'] = $value->APPLICATION_STATUS;
                $data[$i]['DESCRIPTION'] = $value->PRESCHOOLTYPE_DES;

                $data[$i]['CREATED_DATE'] = getShowDate($value->CREATED_DATE);
                $data[$i]['OBJECT_TYPE'] = $value->OBJECT_TYPE;
                
                if($value->OBJECT_TYPE != 'APPLICATION_TYPE')
                $data[$i]['SCORE'] = $value->SCORE;
                
                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );

        if ($isJson)
        {
            return $dataforjson;
        }
        else
        {
            return $data;
        }
    }
    
    public static function sqlStudentPreschoolId($objectId){
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_preschool'), array('*'));
        $SQL->where("A.STUDENT_INDEX = '" . $objectId . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonSaveGridpreschool($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $objectId = isset($params["id"]) ? addText($params["id"]) : "";
        $objectType = isset($params["object"]) ? addText($params["object"]) : "";
        $comboValue = isset($params["comboValue"]) ? addText($params["comboValue"]) : "";
        
        $SAVEDATA = array();
        switch ($field)
        {
            case "CAMEMIS_TYPE":
                $newValue = $comboValue;
                break;
            case "DEGREE_TYPE":
                $newValue = $comboValue;
                break;
            case "APPLICATION_STATUS":
                $newValue = $comboValue;
                break;
            default:
                $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
                break;
        }
        
        if ($objectId)
        {         
            switch ($field)
            {
                case "DELETE":
                    self::dbAccess()->delete("t_student_preschooltype", "ID='" . $objectId . "'");
                    break;
                case "SCORE":
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    $SAVEDATA['ENTER_BY'] = Zend_Registry::get('USER')->ID;
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_student_preschooltype", $SAVEDATA, $WHERE);
                    break;
                default:
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                    self::dbAccess()->update("t_student_preschooltype", $SAVEDATA, $WHERE);
                    break;
            }
        }else{
            switch ($field)
            {
                case "SCORE":
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    $SAVEDATA['ENTER_BY'] = Zend_Registry::get('USER')->ID;

                    break;
                
                default:
                    $SAVEDATA["" . $field . ""] = addText($newValue);
                    break;
            }

            $SAVEDATA["PRESTUDENT"] = $studentId;
            $SAVEDATA["OBJECT_TYPE"] = $objectType;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
            
            self::dbAccess()->insert('t_student_preschooltype', $SAVEDATA);

            $objectId = self::dbAccess()->lastInsertId();
        }

        $SUCCESS_DATA = array();
        $SUCCESS_DATA["success"] = true;

        $facette = self::dbAccess()->fetchRow("SELECT * FROM t_student_preschooltype WHERE ID='" . $objectId . "'");

        switch ($field)
        {
            case "DELETE":
                $SUCCESS_DATA["DELETE"] = true;
                break;
            default:
                $SUCCESS_DATA["DELETE"] = false;
                $SUCCESS_DATA["ID"] = $facette->ID;
                $SUCCESS_DATA["CAMEMIS_TYPE"] = $facette->CAMEMIS_TYPE;
                $SUCCESS_DATA["DESCRIPTION"] = $facette->DESCRIPTION;
                break;
        }
        return $SUCCESS_DATA;
    }
    
    public static function jsonListGridpreschool($params)
    {

        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $objectType = isset($params["object"]) ? addText($params["object"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_preschooltype'), array('*'));
        $SQL->joinLeft(array('B' => 't_members'), 'A.ENTER_BY = B.ID', array('FIRSTNAME AS MEMBER_FIRSTNAME', 'LASTNAME AS MEMBER_LASTNAME'));
        $SQL->where("PRESTUDENT='" . $studentId . "'");
        $SQL->where("OBJECT_TYPE='" . $objectType . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        $i = 0;
        $data = array();
        if ($result)
        {

            foreach ($result as $value)
            {
                switch($objectType){
                    case "APPLICATION":
                        $data[$i]["ID"] = $value->ID;
                        $data[$i]["DESCRIPTION"] = $value->DESCRIPTION;
                        if ($value->APPLICATION_STATUS <> 0)
                            $data[$i]["APPLICATION_STATUS"] = CamemisTypeDBAccess::findObjectFromId($value->APPLICATION_STATUS)->NAME;
                        if ($value->DEGREE_TYPE <> 0)
                            $data[$i]["DEGREE_TYPE"] = CamemisTypeDBAccess::findObjectFromId($value->DEGREE_TYPE)->NAME;
                        if ($value->CAMEMIS_TYPE <> 0)
                            $data[$i]["CAMEMIS_TYPE"] = CamemisTypeDBAccess::findObjectFromId($value->CAMEMIS_TYPE)->NAME;
                        break;
                    default:
                        $data[$i]["ID"] = $value->ID;
                        $data[$i]["DESCRIPTION"] = $value->DESCRIPTION;
                        $data[$i]["SCORE"] = $value->SCORE;
                        $data[$i]["LEVEL"] = $value->LEVEL;
                        $data[$i]["STATUS"] = $value->TESTING_STATUS;
                        if (!SchoolDBAccess::displayPersonNameInGrid()) {
                            $data[$i]["ENTER_BY"] = setShowText($value->MEMBER_FIRSTNAME) . " " . setShowText($value->MEMBER_LASTNAME);
                        } else {
                            $data[$i]["ENTER_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                        }
                        $data[$i]["RESULT_DATE"] = $value->RESULT_DATE;
                        if ($value->CAMEMIS_TYPE <> 0)
                            $data[$i]["CAMEMIS_TYPE"] = CamemisTypeDBAccess::findObjectFromId($value->CAMEMIS_TYPE)->NAME;
                        break;
                    
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

}

?>