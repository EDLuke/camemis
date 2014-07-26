<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/school/SchoolFacilityDBAccess.php';
require_once 'models/app_university/room/RoomDescriptionDBAccess.php';
require_once setUserLoacalization();

class RoomDBAccess {

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

    public function getRoomDataFromId($Id) {

        $facette = self::findRoomFromId($Id);

        $data = array();

        if ($facette) {

            $data["ID"] = $facette->ID;
            $data["CODE"] = setShowText($facette->CODE);
            $data["SHORT"] = setShowText($facette->SHORT);
            $data["NAME"] = setShowText($facette->NAME);
            $data["BUILDING"] = setShowText($facette->BUILDING);
            $data["FLOOR"] = setShowText($facette->FLOOR);
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["STATUS"] = $facette->STATUS;
            $data["ROOM_TYPE"] = $facette->ROOM_TYPE;
            $data["ROOM_SIZE"] = $facette->ROOM_SIZE;
            $data["MAX_COUNT"] = $facette->MAX_COUNT;

            $SQL = self::dbAccess()->select();
            $SQL->from("t_room_description", array('*'));
            $result = self::dbAccess()->fetchAll($SQL);

            $CHECK_DATA = explode(",", $facette->FACILITIES);
            if ($result) {
                foreach ($result as $value) {

                    switch ($value->CHOOSE_TYPE) {
                        case 1:
                            if (in_array($value->ID, $CHECK_DATA)) {
                                $data["CHECKBOX_" . $value->ID] = true;
                            } else {
                                $data["CHECKBOX_" . $value->ID] = false;
                            }

                            break;
                        case 2:
                            $descriptionObject = RoomDescriptionDBAccess::findObjectFromId($value->ID);
                            if (in_array($value->ID, $CHECK_DATA)) {
                                if ($descriptionObject)
                                    $data["RADIOBOX_" . $descriptionObject->PARENT] = $value->ID;
                            }
                            break;
                    }
                }
            }

            $data["REMOVE_STATUS"] = $this->checkRoomINSchedule($facette->ID);
            $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($facette->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($facette->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($facette->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($facette->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($facette->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($facette->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($facette->DISABLED_BY);
        }

        return $data;
    }

    public static function findRoomFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_room";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadObject($Id) {

        $result = self::findRoomFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getRoomDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function getAllRoomsQuery($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : "0";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";

        if ($parentId) {
            $_parentId = $parentId;
        } else {
            $_parentId = $node;
        }

        $name = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $roomSize = isset($params["ROOM_SIZE"]) ? addText($params["ROOM_SIZE"]) : "";
        $maxCount = isset($params["MAX_COUNT"]) ? addText($params["MAX_COUNT"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $facilityName = isset($params["ITEM_NAME"]) ? addText($params["ITEM_NAME"]) : "";
        $barCode = isset($params["BARCODE"]) ? addText($params["BARCODE"]) : "";

        $SQL_FACILITY = self::dbAccess()->select();
        $SQL_FACILITY->from('t_room_description', array('*'));
        $SQL_FACILITY->where("OBJECT_TYPE='ITEM'");
        //error_log($SQL_FACILITY);
        $entriesFacilities = self::dbAccess()->fetchAll($SQL_FACILITY);

         $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_room'), array('*'));
        $SQL->joinLeft(array('B' => 't_facility_user_item'), 'A.ID=B.ROOM_ID', array('FACILITY_ID'));
        $SQL->joinLeft(array('C' => 't_facility'), 'B.FACILITY_ID=C.ID', array('NAME AS FACILITY_NAME','BARCODE'));

        if ($roomSize)
            $SQL->where("A.ROOM_SIZE='" . $roomSize . "'");

        if ($maxCount)
            $SQL->where("A.MAX_COUNT='" . $maxCount . "'");

        if ($name)
            $SQL->where("A.NAME LIKE'" . $name . "%'");
            
        if ($facilityName){
            $SQL->where("C.NAME LIKE'" . $facilityName . "%'");
            $SQL->where("C.STATUS='CHECK-OUT'");  
        }
            
        if ($barCode){
            $SQL->where("C.BARCODE LIKE'" . $barCode . "%'");
            $SQL->where("C.STATUS='CHECK-OUT'");
        }
            
        if ($globalSearch) {
            $SQL->where("A.NAME LIKE'" . $globalSearch . "%'");
            $SQL->orWhere("A.ROOM_SIZE ='" . $globalSearch . "'");
            $SQL->orWhere("A.MAX_COUNT ='" . $globalSearch . "'");
        }

        $CHECKBOX = array();
        $RADIOBOX = array();
        if ($entriesFacilities) {
            foreach ($entriesFacilities as $value) {
                if (isset($params["CHECKBOX_" . $value->ID])) {
                    $CHECKBOX[$value->ID] = $value->ID;
                }

                $parentObject = RoomDescriptionDBAccess::findObjectFromId($value->ID);
                if ($parentObject->PARENT) {
                    if (isset($params["RADIOBOX_" . $parentObject->PARENT])) {
                        $RADIOBOX[$value->ID] = $value->ID;
                    }
                }
            }
        }

        $PARAMS_FACILITY = $CHECKBOX + $RADIOBOX;
        $PARAMS_FACILITY_STR = $PARAMS_FACILITY ? implode(',', $PARAMS_FACILITY) : "";

        if ($PARAMS_FACILITY_STR) {
            $SQL->where("A.FACILITIES IN (" . $PARAMS_FACILITY_STR . ")");
        } else {
            if ($_parentId)
                $SQL->where("A.PARENT ='" . $_parentId . "'");
        }

        $SQL->order("A.NAME");
         $SQL->group("A.ID");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function allRoomsComboData() {

        $data = array();
        $result = $this->getAllRoomsQuery(false);

        $data[0] = "[0,'[---]']";
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public function allRooms($params, $isJson=true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = $this->getAllRoomsQuery($params);
        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $CHECK_AVAIABLE_ROOM = self::checkAvailableRoom($value->ID);

                $data[$i]["ID"] = $value->ID;
                $data[$i]["SHORT"] = setShowText($value->SHORT);
                $data[$i]["MAX_COUNT"] = $value->MAX_COUNT ? $value->MAX_COUNT : "---";
                $data[$i]["ROOM_SIZE"] = $value->ROOM_SIZE ? $value->ROOM_SIZE : "---";
                $data[$i]["ROOM"] = setShowText($value->NAME);
                $data[$i]["BUILDING"] = setShowText($value->BUILDING);
                $data[$i]["FLOOR"] = setShowText($value->FLOOR);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["STATUS_KEY"] = iconStatus($value->STATUS);
                $data[$i]["STATUS"] = $value->STATUS;

                if ($CHECK_AVAIABLE_ROOM)
                    $data[$i]["AVAIABLE_ROOM"] = $CHECK_AVAIABLE_ROOM;

                $i++;
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

    public function jsonTreeAllRooms($params) {

        $data = array();

        $node = isset($params["node"]) ? addText($params["node"]) : "0";
        $params["parentId"] = $node;
        $result = $this->getAllRoomsQuery($params);

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]['id'] = "" . $value->ID . "";

                if ($value->SHORT) {
                    $data[$i]['text'] = "(" . $value->SHORT . ") " . stripslashes($value->NAME);
                } else {
                    $data[$i]['text'] = stripslashes($value->NAME);
                }

                if (self::checkChild($value->ID)) {
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                } else {
                    $data[$i]['cls'] = "nodeTextBlue";
                    $data[$i]['leaf'] = true;
                    $data[$i]['iconCls'] = "icon-application_form_magnify";
                }

                $i++;
            }

        return $data;
    }

    public function createOnlyItem($params) {

        $SAVEDATA = array();
        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
            $SAVEDATA['STATUS'] = 1;
        }

        $SAVEDATA['CODE'] = createCode();
        $SAVEDATA['NAME'] = addText($params["name"]);

        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        self::dbAccess()->insert('t_room', $SAVEDATA);

        return array("success" => true);
    }

    public function removeObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        if (!$this->checkRoomINSchedule($objectId)) {
            $REMVOE = true;
        } else {
            $REMVOE = false;
        }

        $CHECK = self::checkChild($objectId);

        if ($CHECK) {
            if ($REMVOE)
                self::dbAccess()->delete("t_room", " ID ='" . $objectId . "'");
            if ($REMVOE)
                self::dbAccess()->delete("t_room", " PARENT ='" . $objectId . "'");
        }else {
            if ($REMVOE)
                self::dbAccess()->delete("t_room", " ID ='" . $objectId . "'");
        }

        return array("success" => true);
    }

    protected function checkRoomINSchedule($Id) {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_schedule";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND";
        $SQL .= " ROOM_ID = '" . $Id . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function actionSaveRoom($params) {

        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";

        $facette = self::findRoomFromId($objectId);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_room_description", array('*'));
        $result = self::dbAccess()->fetchAll($SQL);
        //error_log($SQL->__toString());

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);
        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);
        if (isset($params["BUILDING"]))
            $SAVEDATA['BUILDING'] = addText($params["BUILDING"]);
        if (isset($params["FLOOR"]))
            $SAVEDATA['FLOOR'] = addText($params["FLOOR"]);
        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);
        if (isset($params["MAX_COUNT"]))
            $SAVEDATA['MAX_COUNT'] = addText($params["MAX_COUNT"]);

        if (isset($params["ROOM_SIZE"]))
            $SAVEDATA['ROOM_SIZE'] = $params["ROOM_SIZE"];

        if ($facette) {

            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA['STATUS'] = 1;

            if ($result) {
                foreach ($result as $value) {
                    switch ($value->CHOOSE_TYPE) {
                        case 1:
                            if (isset($params["CHECKBOX_" . $value->ID . ""])) {
                                $CHECKBOX_DATA[$value->ID] = $params["CHECKBOX_" . $value->ID . ""];
                            }
                            break;
                        case 2:
                            if (isset($params["RADIOBOX_" . $value->PARENT . ""])) {
                                $RADIOBOX_DATA[$value->ID] = $value->ID;
                            }
                            break;
                    }
                }
            }

            $CHOOSE_DATA = $CHECKBOX_DATA + $RADIOBOX_DATA;
            $SAVEDATA["FACILITIES"] = implode(",", $CHOOSE_DATA);

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_room', $SAVEDATA, $WHERE);
        } else {
            $SAVEDATA['CODE'] = createCode();
            $SAVEDATA['PARENT'] = $parentId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_room', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function releaseObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findRoomFromId($objectId);
        $status = $facette->STATUS;

        $data = array();
        switch ($status) {
            case 0:
                $newStatus = 1;
                $data['STATUS']      = 1;
                $data['ENABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                $data['ENABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                break;
            case 1:
                $newStatus = 0;
                $data['STATUS']       = 0;
                $data['DISABLED_DATE']= "'". getCurrentDBDateTime() ."'";
                $data['DISABLED_BY']  = "'". Zend_Registry::get('USER')->CODE ."'";
                break;
        }
        self::dbAccess()->update("t_room", $data, "ID='". $objectId ."'");

        return array("success" => true, "status" => $newStatus);
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_room", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    //@PENG 12.11.2013
    public static function checkAvailableRoom($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_room'), array("C" => "COUNT(*)"));
        $SQL->joinRight(array('B' => 't_schedule'), 'A.ID=B.ROOM_ID', array());
        $SQL->where("A.ID = " . $Id . "");
        $SQL->where("" . timeStrToSecond(getCurrentTime()) . " BETWEEN B.START_TIME AND B.END_TIME");
        $SQL->where("B.SHORTDAY = '" . getCurrentShortDay() . "'");

        //error_log($SQL);
        //error_log(getCurrentTime());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    //@PENG 23.01.2014
    public static function findFacilityByRoomId($Id, $roomName) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_facility_user_item'), array("*"));
        $SQL->joinLeft(array('B' => 't_facility'), 'A.FACILITY_ID=B.ID', array('FACILITY_TYPE'));
        $SQL->joinLeft(array('C' => 't_facility_type'), 'B.FACILITY_TYPE=C.ID', array('NAME','SHORT'));
        $SQL->where("A.ROOM_ID = " . $Id . "");
        $SQL->where("A.LOCATION = '" . $roomName . "'");
        $SQL->group("FACILITY_TYPE");
        
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function countFacilityByType($Id) {
        $permanentCheckOut = self::checkFacilityPermanent($Id);
 
        if(!$permanentCheckOut){
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_facility_user_item'), array("C" => "COUNT(*)"));
            $SQL->joinLeft(array('B' => 't_facility'), 'A.FACILITY_ID=B.ID', array('FACILITY_TYPE','PERMANENT_CHECKOUT','STATUS'));
            $SQL->where("A.ACTION_TYPE = 1");
            $SQL->where("B.FACILITY_TYPE = " . $Id . "");
            $SQL->where("B.STATUS = 'CHECK-OUT'");
            
            //error_log($SQL);
            $data = self::dbAccess()->fetchRow($SQL);
            $result = $data ? $data->C : 0;
        }else{
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_facility_user_item'), array("QUANTITY"));
            $SQL->where("A.FACILITY_ID = ".$Id."");
            
            //error_log($SQL);
            $data = self::dbAccess()->fetchRow($SQL);
            $result = $data ? $data->QUANTITY : 0;
        }
        
        return $result;
    }
    
    public static function checkFacilityPermanent($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_facility'), array("PERMANENT_CHECKOUT"));
        $SQL->where("A.ID = ".$Id."");
        
        //error_log($SQL); 
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->PERMANENT_CHECKOUT : 0;       
    }
}

?>