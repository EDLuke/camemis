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
        $type = isset($params["type"]) ? addText($params["type"]) : '';
        switch($type){
            case "studentPreschool":
                $SQL = self::dbAccess()->select();
                $SQL->distinct();
                $SQL->from(array('A' => 't_student_preschool'), array('*'));
                $SQL->where("A.STUDENT_INDEX='" . $objectId . "'");
                //error_log($SQL);
                return self::dbAccess()->fetchRow($SQL);
            break;
            default:
                $SQL = self::dbAccess()->select();
                $SQL->distinct();
                $SQL->from(array('A' => 't_student_preschooltype'), array('*'));
                $SQL->joinRight(array('B' => 't_student_preschool'), 'A.PRESTUDENT = B.ID', array('ID AS STUDENT_ID', 'FIRSTNAME AS STUDENT_FIRSTNAME', 'LASTNAME AS STUDENT_LASTNAME'));
                $SQL->where("A.ID='" . $objectId . "'");
                //error_log($SQL);
                return self::dbAccess()->fetchRow($SQL);
            break;
        }
    }
    
    public static function getObjectDataFromId($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $type = isset($params["type"]) ? addText($params["type"]) : '';
        $result = self::findObjectFromId($params);
        $data = array();
        switch($type){
            case "studentPreschool":
                if ($result) {
                    $data['FIRSTNAME'] = $result->FIRSTNAME;
                    $data['LASTNAME'] = $result->LASTNAME;
                    $data['PHONE'] = $result->PHONE;
                    $data['ADDRESS'] = $result->ADDRESS;
                    $data['EMAIL'] = $result->EMAIL;
                    $data['GENDER'] = $result->GENDER;
                }
            break;
            case "APPLICATION_TYPE":
                 if ($result) {
                    $data['APPLICATION_TYPE'] = $result->CAMEMIS_TYPE;
                    $data['DESCRIPTION'] = $result->DESCRIPTION;
                    if (!SchoolDBAccess::displayPersonNameInGrid()){
                        $data["STUDENT_PRESCHOOL_NAME"] = setShowText($result->STUDENT_FIRSTNAME) . " " . setShowText($result->STUDENT_LASTNAME);
                        $data["CHOSE_STUDENT_PRESCHOOL"] = $result->STUDENT_ID;
                    }else{
                        $data["STUDENT_PRESCHOOL_NAME"] = setShowText($result->STUDENT_LASTNAME) . " " . setShowText($result->STUDENT_FIRSTNAME);
                        $data["CHOSE_STUDENT_PRESCHOOL"] = $result->STUDENT_ID;
                    }
                    
                }
            break;
            case "TESTING_TYPE":
                if ($result) {
                    $data['TESTING_TYPE'] = $result->CAMEMIS_TYPE;
                    $data['DESCRIPTION'] = $result->DESCRIPTION;
                    $data['SCORE'] = $result->SCORE;
                    if (!SchoolDBAccess::displayPersonNameInGrid()){
                        $data["STUDENT_PRESCHOOL_NAME"] = setShowText($result->STUDENT_FIRSTNAME) . " " . setShowText($result->STUDENT_LASTNAME);
                        $data["CHOSE_STUDENT_PRESCHOOL"] = $result->STUDENT_ID;
                    }else{
                        $data["STUDENT_PRESCHOOL_NAME"] = setShowText($result->STUDENT_LASTNAME) . " " . setShowText($result->STUDENT_FIRSTNAME);
                        $data["CHOSE_STUDENT_PRESCHOOL"] = $result->STUDENT_ID;
                    }
                    
                }
            break;
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
    
    public static function sqlGetAllChooseStudentPreschool($params) {
        
        $globalSearch = isset($params["query"]) ? trim($params["query"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_preschool'), array('*'));
        $SQL .= " WHERE 1=1";
        if ($globalSearch) {
            $SQL .= " AND ((A.EMAIL LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.ADDRESS LIKE '" . $globalSearch . "%')";
            $SQL .= " ) ";
        }
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonShowAllChooseStudentPreschool($params) {
        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $data = array();
        $i = 0;

        $result = self::sqlGetAllChooseStudentPreschool($params);

        if ($result) {
            foreach ($result as $value) {
                $data[$i]['ID'] = $value->ID;
                //////////////////////////////////////
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["PHONE"] = $value->PHONE;
                $data[$i]["EMAIL"] = $value->EMAIL;
                $data[$i]["ADDRESS"] = $value->ADDRESS;
                $data[$i]["GENDER"] = getGenderName($value->GENDER);

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
    
    
    public static function jsonSaveTypePreschool($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : '';
        $SAVEDATA = array();
        
        switch($objectType){
            case "TESTING":
                if (isset($params["TESTING_TYPE"]))
                    $SAVEDATA["CAMEMIS_TYPE"] = addText($params["TESTING_TYPE"]);

                if (isset($params["SCORE"]))
                    $SAVEDATA["SCORE"] = addText($params["SCORE"]);
                    
                if (isset($params["DESCRIPTION"]))
                    $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);
                break;
            
            case "APPLICATION":
                if (isset($params["APPLICATION_TYPE"]))
                    $SAVEDATA["CAMEMIS_TYPE"] = addText($params["APPLICATION_TYPE"]);
                    
                if (isset($params["DESCRIPTION"]))
                    $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);
                break; 
            
            case "REFERENCE":
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
                break;
        }

        $WHERE[] = "PRESTUDENT = '" . $objectId . "'";
        $WHERE[] = "OBJECT_TYPE = '".$objectType."'";
        
        self::dbAccess()->update('t_student_preschooltype', $SAVEDATA, $WHERE);
        
        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }
     
    public static function jsonSaveStudentPreschool($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 'new';
        $type = isset($params["type"]) ? addText($params["type"]) : '';

        $SAVEDATA = array();
        $GETDATA = array();

        if (isset($params["FIRSTNAME"]))
            $SAVEDATA["FIRSTNAME"] = addText($params["FIRSTNAME"]);

        if (isset($params["LASTNAME"]))
            $SAVEDATA["LASTNAME"] = addText($params["LASTNAME"]);

        if (isset($params["PHONE"]))
            $SAVEDATA["PHONE"] = addText($params["PHONE"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA["EMAIL"] = addText($params["EMAIL"]);

        if (isset($params["ADDRESS"]))
            $SAVEDATA["ADDRESS"] = addText($params["ADDRESS"]);
            
        if (isset($params["GENDER"]))
            $SAVEDATA["GENDER"] = addText($params["GENDER"]);
        if ($objectId == "new") {
            $SAVEDATA['ID'] = generateGuid();
            $GETDATA['PRESTUDENT'] = $SAVEDATA['ID'];
            $GETDATA['OBJECT_TYPE'] = "REFERENCE";
            $GETAPPDATA['PRESTUDENT'] = $SAVEDATA['ID'];
            $GETAPPDATA['OBJECT_TYPE'] = "APPLICATION";
            $GETAPPDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $GETAPPDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
            $GETTESTDATA['PRESTUDENT'] = $SAVEDATA['ID'];
            $GETTESTDATA['OBJECT_TYPE'] = "TESTING";
            $GETTESTDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $GETTESTDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
            
            self::dbAccess()->insert('t_student_preschooltype', $GETTESTDATA);
            self::dbAccess()->insert('t_student_preschooltype', $GETAPPDATA);
            self::dbAccess()->insert('t_student_preschooltype', $GETDATA);
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
        $type = isset($params["type"]) ? addText($params["type"]) : '';
        
        switch($type){
            case "studentPreschool":
                $result = self::sqlGetStudentPreschool($params);
                foreach($result as $value){
                    self::dbAccess()->delete('t_student_preschool_reference', array("STD_PRESCHOOL_ID='" . $value->ID . "'"));
                    self::dbAccess()->delete('t_student_preschooltype', array("PRESTUDENT='" . $value->ID . "'"));
                }
                self::dbAccess()->delete('t_student_preschool', array("STUDENT_INDEX='" . $objectId . "'"));
            break;
            case "APPLICATION_TYPE":
            case "TESTING_TYPE":
                self::dbAccess()->delete('t_student_preschooltype', array("ID='" . $objectId . "'"));
            break;
        }
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
        $SQL->joinLeft(array('B' => 't_student_preschooltype'), 'A.ID= B.PRESTUDENT', array('ID AS PRESCHOOLTYPE_ID', 'DESCRIPTION AS PRESCHOOLTYPE_DES', 'CAMEMIS_TYPE AS PRESCHOOLTYPE_CAM',  'CREATED_DATE', 'CREATED_BY', 'SCORE'));
        $SQL->joinLeft(array('C' => 't_camemis_type'), 'B.CAMEMIS_TYPE = C.ID', array('NAME AS CAM_NAME', 'OBJECT_TYPE'));
        $SQL->joinLeft(array('D' => 't_members'), 'B.CREATED_BY = D.ID', array('FIRSTNAME AS MEMBER_FIRSTNAME', 'LASTNAME AS MEMBER_LASTNAME'));
        
        
        if ($startDate and $endDate) {
                $SQL->where("B.CREATED_DATE BETWEEN '" . $startDate . "' AND '" . $endDate . "'");
                //$SQL->where("C.OBJECT_TYPE ='" . $infoType . "'");
        }
        //error_log($objectId);
        //if ($objectId)
            //$SQL->where("A.STUDENT_INDEX ='" . $objectId . "'"); 
            
        if ($gender)
            $SQL->where("A.GENDER ='" . $gender . "'");

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
        
        if ($cametype)
                $SQL->where("B.CAMEMIS_TYPE = '" . $cametype . "'");
        
            
        if ($informationType){
            switch($informationType){
                case "CLEAR":
                    $SQL->where("B.CAMEMIS_TYPE = '0'");
                    break;
                default:
                    $SQL->where("B.OBJECT_TYPE = '" . $informationType. "'");
                    $SQL->where("B.CAMEMIS_TYPE != '0'");
                    break;
            }
            
        }

        if ($infoType)
            $SQL->where("C.OBJECT_TYPE = '".$infoType."'");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonSearchStudentPreschool($params, $isJson = true) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";
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
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                } else {
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                }
                
                
                $data[$i]['CAMEMIS_TYPE'] = $value->CAM_NAME;
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

}

?>