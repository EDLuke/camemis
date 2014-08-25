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

class StaffContractDBAccess {

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
    
    public static function sqlStaffContract($params) {
     
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_contract'), array('*'));
        $SQL->joinLeft(array('B' => 't_members'), 'A.CREATED_BY=B.ID',  array('*'));
        
        $SQL->where("A.ID='" . $objectId . "'");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);       
    }
    
    public static function findObjectFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_staff_contract'), array('*'));
        $SQL->joinLeft(array('B' => 't_members'), 'A.STAFF_ID= B.ID', array('ID AS MEMBER_ID', 'FIRSTNAME AS STAFF_FIRSTNAME', 'LASTNAME AS STAFF_LASTNAME'));
        $SQL->where("A.ID = ?",$Id);
        
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function getObjectDataFromId($Id) {
        
        $result = self::findObjectFromId($Id);
         
        $data = array();
        if ($result) {
            $data['ID']= $result->ID;
            $data['NAME']= $result->NAME;
            $data['DESCRIPTION']= $result->DESCRIPTION;
            $data['START_DATE']= getShowDate($result->START_DATE);
            $data['EXPIRED_DATE']= getShowDate($result->EXPIRED_DATE);
            $data['CONTRACT_TYPE']= $result->CONTRACT_TYPE;    
            if(!SchoolDBAccess::displayPersonNameInGrid()){
                $data["STAFF_NAME"] = setShowText($result->STAFF_LASTNAME) . " " . setShowText($result->STAFF_FIRSTNAME);
                $data["CHOSE_STAFF"] = $result->MEMBER_ID;
            }else{
                $data["STAFF_NAME"] = setShowText($result->STAFF_FIRSTNAME) . " " . setShowText($result->STAFF_LASTNAME);
                $data["CHOSE_STAFF"] = $result->MEMBER_ID;
            }
            
        }

        return $data;
        
    }
    
    public static function jsonLoadStaffContract($Id) {
        
        $result = self::findObjectFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getObjectDataFromId($Id)
            );
            
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;   
    }
        
    
    public static function sqlGetStaffContact($params){
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $startDate = isset($params["startDate"]) ? addText($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? addText($params["endDate"]) : "";
        
        $code = isset($params["code"]) ? addText($params["code"]) : "";
        $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
        $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
        $name = isset($params["name"]) ? addText($params["name"]) : "";
        $expiredDate = isset($params["expiredDate"]) ? $params["expiredDate"] : "";
        $searchType = isset($params["searchType"]) ? $params["searchType"] : "";
        $contractType = isset($params["contractType"]) ? addText($params["contractType"]) : "";
        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff_contract'), array('*'));
        $SQL->joinLeft(array('B' => 't_members'), 'A.STAFF_ID= B.ID', array('CODE','FIRSTNAME AS STAFF_FIRSTNAME', 'LASTNAME AS STAFF_LASTNAME'));
        $SQL->joinLeft(array('C' => 't_members'), 'A.CREATED_BY = C.ID', array('FIRSTNAME AS MEMBER_FIRSTNAME', 'LASTNAME AS MEMBER_LASTNAME'));
        $SQL->joinLeft(array('D' => 't_camemis_type'), 'A.CONTRACT_TYPE = D.ID', array('NAME AS CONTRACT_NAME'));
        
        //if($objectId)
            //$SQL->where("B.ID = '".$objectId."'");
            
        if($startDate and $endDate){
            if($searchType == 'START_DATE'){
                $SQL->where("A.START_DATE >= '".$startDate."' AND A.START_DATE <='".$endDate."'");
            }else if($searchType == 'EXPIRED_DATE'){
                $SQL->where("A.EXPIRED_DATE >= '".$startDate."' AND A.EXPIRED_DATE <='".$endDate."'");
            }else{
                $SQL->where("A.CREATED_DATE >= '".$startDate."' AND A.CREATED_DATE <='".$endDate."'");
            }
        }
            
        if($code)
            $SQL->where("B.CODE LIKE '".$code."%'");
            
        if($firstname)
            $SQL->where("B.FIRSTNAME LIKE '".$firstname."%'");
            
        if($lastname)
            $SQL->where("B.LASTNAME LIKE '".$lastname."%'");
            
        if($name)
            $SQL->where("A.NAME LIKE '".$name."%'");
        
        if($contractType)
                $SQL->where("A.CONTRACT_TYPE = '".$contractType."'");    
        //if($expiredDate)
            //$SQL->where("A.EXPIRED_DATE = '".$expiredDate."'");
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    //@CHHE Vathana
    
    public static function jsonShowAllStaffContracts($params, $isJson = true) {
        
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        
        $data = array();
        $i = 0;
        
        $result = self::sqlGetStaffContact($params);
        
        if($result){
            foreach($result as $value){
                $data[$i]['ID']= $value->ID;
                $data[$i]['SUBJECT_NAME']= $value->NAME;
                $data[$i]['CONTRACT_TYPE']= $value->CONTRACT_NAME;
                $data[$i]['DESCRIPTION']= $value->DESCRIPTION;
                $data[$i]['START_DATE']= getShowDate($value->START_DATE);
                $data[$i]['EXPIRED_DATE']= getShowDate($value->EXPIRED_DATE);
                $data[$i]['CREATED_DATE']= getShowDate($value->CREATED_DATE);
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$i]["STAFF_NAME"] = setShowText($value->STAFF_LASTNAME) . " " . setShowText($value->STAFF_FIRSTNAME);
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                }else{
                    $data[$i]["STAFF_NAME"] = setShowText($value->STAFF_FIRSTNAME) . " " . setShowText($value->STAFF_LASTNAME);
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                }          
                $i++;    
            }
        }
        
        
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if($isJson){
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        }else{
            return $data;    
        }
        
    }
    
    public static function sqlGetAllMembers($params){
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_members'), array('ID as ID_MEMBER', 'CODE', 'FIRSTNAME', 'LASTNAME', 'ROLE'));
        $SQL->joinLeft(array('B' => 't_memberrole'), 'A.ROLE= B.ID', array('ID', 'NAME'));
        
        if ($globalSearch) {
            $SQL->where("A.NAME LIKE '%" . $globalSearch . "%'");
            $SQL->orWhere("A.FIRSTNAME LIKE '%" . $globalSearch . "%'");
            $SQL->orWhere("A.LASTNAME LIKE '%" . $globalSearch . "%'");
            $SQL->orWhere("A.CODE LIKE '%" . $globalSearch . "%'");
        }    
        //$SQL->where("A.ID='" . $objectId . "'");
        //error_log($SQL);
        
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonShowAllMembers($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        
        $data = array();
        $i = 0;
        
        $result = self::sqlGetAllMembers($params);
        
        if($result){
            foreach($result as $value){
                $data[$i]['ID']= $value->ID_MEMBER;
                //////////////////////////////////////
                $STATUS_DATA = StaffStatusDBAccess::getCurrentStaffContractStatus($value->ID_MEMBER);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
                
                $data[$i]['CODE']= $value->CODE;
                $data[$i]['NAME']= setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]['ROLE']= $value->NAME;
                
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
    
    public static function jsonSaveStaffContract($params) {
        
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) :'new';
        
        $SAVEDATA = array();
        
       
        if(isset($params["NAME"]))
            $SAVEDATA["NAME"] = addText($params["NAME"]);
        
        if(isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);    
            
        if(isset($params["START_DATE"]))
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);
            
        if(isset($params["EXPIRED_DATE"]))
            $SAVEDATA["EXPIRED_DATE"] = setDate2DB($params["EXPIRED_DATE"]);
            
        if(isset($params["CONTRACT_TYPE"]))
            $SAVEDATA["CONTRACT_TYPE"] = addText($params["CONTRACT_TYPE"]);
            
        if(isset($params["CHOSE_STAFF"]))
            $SAVEDATA["STAFF_ID"] = addText($params["CHOSE_STAFF"]);            
            
        if($objectId == "new"){
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->ID;
            self::dbAccess()->insert('t_staff_contract', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }else{
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_staff_contract', $SAVEDATA, $WHERE);
        } 
        
        return array(
            "success" => true
            ,"objectId" => $objectId
        );            
    }
    
    public function jsonRemoveStaffContract($Id) {
        self::dbAccess()->delete('t_staff_contract', array("ID='" . $Id . "'"));
        
        return array(
            "success" => true
        );    
    }
    
    public static function jsonSearchStaffContract($params) {
        
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        
        $data = array();
        $i = 0;
        
        $result = self::sqlGetStaffContact($params);
        
        if($result){
            foreach($result as $value){
                $data[$i]['ID']= $value->ID;
                $data[$i]['SUBJECT_NAME']= $value->NAME;
                $data[$i]['CONTRACT_TYPE']= $value->CONTRACT_NAME;
                $data[$i]['DESCRIPTION']= $value->DESCRIPTION;
                $data[$i]['START_DATE']= getShowDate($value->START_DATE);
                $data[$i]['EXPIRED_DATE']= getShowDate($value->EXPIRED_DATE);
                $data[$i]['CREATED_DATE']= getShowDate($value->CREATED_DATE);
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$i]["STAFF_NAME"] = setShowText($value->STAFF_LASTNAME) . " " . setShowText($value->STAFF_FIRSTNAME);
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                }else{
                    $data[$i]["STAFF_NAME"] = setShowText($value->STAFF_FIRSTNAME) . " " . setShowText($value->STAFF_LASTNAME);
                    $data[$i]["CREATED_BY"] = setShowText($value->MEMBER_LASTNAME) . " " . setShowText($value->MEMBER_FIRSTNAME);
                }          
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
}

?>