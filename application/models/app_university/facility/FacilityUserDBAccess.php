<?php

///////////////////////////////////////////////////////////
// @veasna
// Date: 31.08.2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/CAMEMISUploadDBAccess.php';
require_once 'models/app_university/facility/FacilityDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';

class FacilityUserDBAccess {

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

    public static function getAssignedUserFacility($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_user', '*');
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getOutCode($type) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_user", array("REFERENCE_NUMBER"));
        $SQL->where("ACTION_TYPE = " . $type);
        $SQL->order('REFERENCE_NUMBER DESC');
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        if ($type == 1) {
            if ($result) {
                $cal = substr($result->REFERENCE_NUMBER, 4) + 1;
                return "OUT-" . $cal;
            } else {
                return "OUT-100";
            }
        } else {
            if ($result) {
                $cal = substr($result->REFERENCE_NUMBER, 3) + 1;
                return "IN-" . $cal;
            } else {
                return "IN-100";
            }
        }
    }

    public static function jsonSaveFacilityUser($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $type = isset($params["type"]) ? addText($params["type"]) : 1;
        $TARGET_USER = isset($params["CHECK_OUT_ID"]) ? $params["CHECK_OUT_ID"] : '';

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["USER_ID"]))
            $SAVEDATA['USER_ID'] = addText($params["USER_ID"]);

        if (isset($params["LOCATION"]))
            $SAVEDATA['LOCATION'] = addText($params["LOCATION"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["USER"])) {
            $SAVEDATA['USER_ID'] = addText($params["USER"]);
            $SAVEDATA['USER_TYPE'] = 'STAFF';
        }

        if ($TARGET_USER)
            $SAVEDATA['TARGET_USER'] = $TARGET_USER;

        if ($objectId == 'new') {
            $SAVEDATA['REFERENCE_NUMBER'] = self::getOutCode($type);
            $SAVEDATA['ACTION_TYPE'] = $type;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_facility_user', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_facility_user', $SAVEDATA, $WHERE);
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function jsonLoadFacilityUser($Id) {

        $facette = self::getAssignedUserFacility($Id);

        $data = array();

        if ($facette) {
            $data['LOCATION'] = $facette->LOCATION;
            $data['REFERENCE_NUMBER'] = $facette->REFERENCE_NUMBER;
            $data['USER_ID'] = $facette->USER_ID;
            $data['NAME'] = $facette->NAME;
            $data['DESCRIPTION'] = $facette->DESCRIPTION;

            if ($facette->USER_ID) {
                $staffObject = StaffDBAccess::findStaffFromId($facette->USER_ID);
                if ($staffObject) {
                    if (!SchoolDBAccess::displayPersonNameInGrid()) {
                        $data["PERSON"] = setShowText($staffObject->LASTNAME) . " " . setShowText($staffObject->FIRSTNAME);
                    } else {
                        $data["PERSON"] = setShowText($staffObject->FIRSTNAME) . " " . setShowText($staffObject->LASTNAME);
                    }
                }
            }
        }
        return array("success" => true, "data" => $data);
    }

    public static function deleteFacilityUser($Id) {
        $object = self::findUserItemFacilityByFacUserId($Id);
        if ($object) {
            foreach ($object as $value) {
                self::deleteUserItemFacility($value->ID);
            }
        }
        self::dbAccess()->delete('t_facility_user', array("ID='" . $Id . "'"));
        return array("success" => true);
    }

    //@veasna
    public static function findFacilityUserById($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_user', '*');
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getSqlUserCheckOutItems($params) {

        $BARCODE = isset($params['BARCODE']) ? $params['BARCODE'] : '';
        $END_DATE = isset($params['END_DATE']) ? setDate2DB($params['END_DATE']) : '';
        $START_DATE = isset($params['START_DATE']) ? setDate2DB($params['START_DATE']) : '';
        $DEAD_LINE_END_DATE = isset($params['DEADLINE_END']) ? setDate2DB($params['DEADLINE_END']) : '';
        $DEAD_LINE_START_DATE = isset($params['DEADLINE_START']) ? setDate2DB($params['DEADLINE_START']) : '';
        $NAME = isset($params['NAME']) ? $params['NAME'] : '';
        $ITEM_NAME = isset($params['ITEM_NAME']) ? $params['ITEM_NAME'] : '';
        $ACTION_TYPE = isset($params['ACTION_TYPE']) ? $params['ACTION_TYPE'] : '';
        $ITEM_ACTION_TYPE = isset($params['ITEM_ACTION_TYPE']) ? $params['ITEM_ACTION_TYPE'] : '';
        $USER_INVOICE_ID = isset($params['objectId']) ? $params['objectId'] : '';
        $FIRST_NAME = isset($params['FIRSTNAME']) ? $params['FIRSTNAME'] : '';
        $LAST_NAME = isset($params['LASTNAME']) ? $params['LASTNAME'] : '';
        $CODE = isset($params['CODE']) ? $params['CODE'] : '';
        $GENDER = isset($params['GENDER']) ? $params['GENDER'] : '';
        $TARGET_USER = isset($params['REF_ID']) ? $params['REF_ID'] : '';

        $SELECTION_A = array(
            "REFERENCE_NUMBER AS REFERENCE_NUMBER"
            , "NAME AS INVOICE_NAME"
            , "ID AS USER_INVOICE_ID"
            , "USER_ID AS USER_ID"
            , "USER_TYPE AS USER_TYPE"
            , "CREATED_DATE AS CREATED_DATE"
            , "CREATED_BY AS CREATED_BY"
            , "DESCRIPTION AS DESCRIPTION"
            , "MODIFY_BY AS MODIFY_BY"
            , "MODIFY_DATE AS MODIFY_DATE"
            , "ENABLED_BY AS ENABLED_BY"
            , "ENABLED_DATE AS ENABLED_DATE"
            , "TARGET_USER AS TARGET_USER"
        );
        $SELECTION_B = array(
            "QUANTITY AS QUANTITY"
            , "ID AS USER_ITEM_ID"
            , "ACTION_TYPE AS ITEM_ACTION_TYPE"
            , "FACILITY_ID AS FACILITY_ID"
            , "LOCATION AS LOCATION"
            , "RECEIVED_DATE AS RECEIVED_DATE"
            , "RECEIVED_BY AS RECEIVED_BY"
            , "ISSUED_DATE AS ISSUED_DATE"
            , "ISSUED_BY AS ISSUED_BY"
            , "DEADLINE AS DEADLINE"
        );
        $SELECTION_C = array(
            "NAME AS NAME"
            , "ID AS FACILITY_ID"
            , "COLOR AS COLOR"
            , "DESCRIPTION AS DESCRIPTION"
            , "FACILITY_TYPE AS FACILITY_TYPE"
            , "BARCODE AS BARCODE"
            , "COST AS COST"
            , "DELIVERED_DATE AS DELIVERED_DATE"
            , "EXPIRED_WARRANTY AS EXPIRED_WARRANTY"
            , "PERMANENT_CHECKOUT AS PERMANENT_CHECKOUT"
        );

        $SELECTION_D = array(
            "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "GENDER AS GENDER"
            , "CODE AS CODE"
            , "DATE_BIRTH AS DATE_BIRTH"
            , "BIRTH_PLACE AS BIRTH_PLACE"
            , "PHONE AS PHONE"
        );

        ////
        $SELECTION_E = array(
            "REFERENCE_NUMBER AS REFERENCE_NUMBER_OUT"
            , "NAME AS INVOICE_NAME_OUT"
            , "ID AS USER_INVOICE_OUT_ID"
        );
        ///

        $SQL = self::dbAccess()->select();
        //$SQL->from(array('A' => 't_facility_user'), $SELECTION_A);
        //$SQL->joinLeft(array('B' => 't_facility_user_item'), 'A.ID=B.FACUSER_ID', $SELECTION_B);
        $SQL->from(array('B' => 't_facility_user_item'), $SELECTION_B);
        $SQL->joinLeft(array('A' => 't_facility_user'), 'A.ID=B.FACUSER_ID', $SELECTION_A);
        if ($ACTION_TYPE == 2)
            $SQL->joinLeft(array('E' => 't_facility_user'), 'E.ID=A.TARGET_USER', $SELECTION_E);

        $SQL->joinLeft(array('C' => 't_facility'), 'B.FACILITY_ID=C.ID', $SELECTION_C);
        $SQL->joinLeft(array('D' => 't_staff'), 'A.USER_ID=D.ID', $SELECTION_D);
        if ($ACTION_TYPE)
            $SQL->where("A.ACTION_TYPE = '" . $ACTION_TYPE . "'");
        if ($ITEM_ACTION_TYPE)
            $SQL->where("B.ACTION_TYPE = '" . $ITEM_ACTION_TYPE . "'");
        if ($USER_INVOICE_ID)
            $SQL->where("A.ID = '" . $USER_INVOICE_ID . "'");
        if ($NAME)
            $SQL->where("A.NAME LIKE '" . $NAME . "%'");
        if ($BARCODE)
            $SQL->where("C.BARCODE LIKE '" . $BARCODE . "%'");
        if ($ITEM_NAME)
            $SQL->where("C.NAME LIKE '" . $ITEM_NAME . "%'");

        if ($TARGET_USER)
            $SQL->where("E.REFERENCE_NUMBER = 'OUT-" . $TARGET_USER . "'");

        if ($DEAD_LINE_START_DATE && $DEAD_LINE_END_DATE)
            $SQL->where("B.DEADLINE >= '" . $DEAD_LINE_START_DATE . "' && B.DEADLINE <= '" . $DEAD_LINE_END_DATE . "'");

        if ($START_DATE && $END_DATE && $ACTION_TYPE == 1)
            $SQL->where("B.ISSUED_DATE >= '" . $START_DATE . "' && B.ISSUED_DATE <= '" . $END_DATE . "'");
        if ($START_DATE && $END_DATE && $ACTION_TYPE == 2)
            $SQL->where("B.RECEIVED_DATE >= '" . $START_DATE . "' && B.RECEIVED_DATE <= '" . $END_DATE . "'");
        if ($FIRST_NAME)
            $SQL->where("D.FIRSTNAME LIKE '" . $FIRST_NAME . "%'");
        if ($LAST_NAME)
            $SQL->where("D.LASTNAME LIKE '" . $LAST_NAME . "%'");
        if ($CODE)
            $SQL->where("D.CODE LIKE '" . $CODE . "%'");
        if ($GENDER)
            $SQL->where("D.GENDER = '" . $GENDER . "'");

        //$SQL->->order("A.CREATED_DATE DESC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonAllCheckOutItems($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $type = isset($params["ACTION_TYPE"]) ? $params["ACTION_TYPE"] : "";

        $result = self::getSqlUserCheckOutItems($params);

        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $check_in = array();
                $returndate = array();
                $recieveDate = '';
                $facette = self::allCheckInByFacUserId($value->USER_INVOICE_ID);

                foreach ($facette as $item) {
                    $check_in[] = $item->FACILITY_ID;
                    $returndate[$item->FACILITY_ID] = $item->RECEIVED_DATE;
                }

                if (in_array($value->FACILITY_ID, $check_in)) {
                    $data[$i]["RETURN"] = "true";
                    $recieveDate = $returndate[$value->FACILITY_ID];
                } else {
                    $data[$i]["RETURN"] = "false";
                    $recieveDate = '';
                }
                if ($type == 2) {
                    $data[$i]["REFERENCE_NUMBER"] = $value->REFERENCE_NUMBER . " (" . $value->REFERENCE_NUMBER_OUT . " - " . $value->LASTNAME . " " . $value->FIRSTNAME . ")";
                } else {
                    $data[$i]["REFERENCE_NUMBER"] = $value->REFERENCE_NUMBER . " (" . $value->INVOICE_NAME . " - " . $value->LASTNAME . " " . $value->FIRSTNAME . ")";
                }


                $data[$i]["USER_INVOICE_ID"] = $value->USER_INVOICE_ID;
                $data[$i]['TARGET_USER'] = $value->TARGET_USER;
                $data[$i]["ID"] = $value->USER_ITEM_ID;
                $data[$i]["ITEM_NAME"] = $value->NAME ? $value->NAME : '---';
                $data[$i]["DELIVERED_DATE"] = getShowDate($value->DELIVERED_DATE);
                $data[$i]["EXPIRED_WARRANTY"] = getShowDate($value->EXPIRED_WARRANTY);
                $data[$i]["BARCODE"] = $value->BARCODE ? "<img src=\"/facility/barcode/?&code=" . $value->BARCODE . "\" alt=\"" . $value->BARCODE . "\">" : '---';
                $data[$i]["QUANTITY"] = $value->QUANTITY ? displayNumberFormat($value->QUANTITY) : '---';
                $data[$i]["COST"] = displayNumberFormat($value->COST) . " " . Zend_Registry::get('SCHOOL')->CURRENCY;
                $data[$i]["PERMANENT"] = $value->PERMANENT_CHECKOUT;
                $data[$i]["LOCATION"] = $value->LOCATION ? $value->LOCATION : '---';
                $data[$i]["ISSUED_DATE"] = getShowDate($value->ISSUED_DATE) ? getShowDate($value->ISSUED_DATE) : '---';
                $data[$i]["ISSUED_BY"] = $value->ISSUED_BY ? $value->ISSUED_BY : '---';
                $data[$i]["RECEIVED_DATE"] = getShowDate($value->RECEIVED_DATE) ? getShowDate($value->RECEIVED_DATE) : '---';
                $data[$i]["RECEIVED_BY"] = $value->RECEIVED_BY ? $value->RECEIVED_BY : '---';
                $data[$i]["ITEM_ACTION_TYPE"] = $value->ITEM_ACTION_TYPE ? $value->ITEM_ACTION_TYPE : '';
                $location = $value->LOCATION ? $value->LOCATION : '---';
                if ($value->PERMANENT_CHECKOUT) {

                    $data[$i]["STATUS"] = $location . "<br/>" . ISSUED_DATE . ": " . getShowDate($value->ISSUED_DATE);
                } else {
                    if ($value->ITEM_ACTION_TYPE == 1) {
                        $data[$i]["STATUS"] = $location . "<br/>" . ISSUED_DATE . ": " . getShowDate($value->ISSUED_DATE) . " (" . RETURNED_DATE . ": " . getShowDate($value->DEADLINE) . ")<br/>" . RECEIVED_DATE . ": " . getShowDate($recieveDate);
                    } else {

                        $data[$i]["STATUS"] = $location . "<br/>" . RECEIVED_DATE . ": " . getShowDate($value->RECEIVED_DATE) . " (" . RETURNED_DATE . ": " . getShowDate($value->DEADLINE) . ")";
                    }
                }

                ++$i;
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

    public static function updateFacilityItems($params) {

        $objectId = isset($params['objectId']) ? $params['objectId'] : '';
        $QUANTITY = isset($params['QUANTITY']) ? $params['QUANTITY'] : '';
        $STATUS = isset($params['STATUS']) ? $params['STATUS'] : '';
        $old_objectId = isset($params['oldObjectId']) ? $params['oldObjectId'] : '';
        $OLD_QUANTITY = isset($params['OLD_QUANTITY']) ? $params['OLD_QUANTITY'] : '';


        $object = FacilityDBAccess::findFacilityItem($objectId);
        $SAVEDATA = array();
        if ($object) {

            if ($object->PERMANENT_CHECKOUT) {
                if ($objectId == $old_objectId) {
                    if ($QUANTITY >= $OLD_QUANTITY) {
                        $interval = $QUANTITY - $OLD_QUANTITY;
                        $SAVEDATA['INSTOCK_QUANTITY'] = ($object->INSTOCK_QUANTITY - $interval);
                    } else {
                        $SAVEDATA['INSTOCK_QUANTITY'] = $object->INSTOCK_QUANTITY + ($OLD_QUANTITY - $QUANTITY);
                    }
                } else {
                    $SAVEDATA['INSTOCK_QUANTITY'] = $object->INSTOCK_QUANTITY - $QUANTITY;
                }
            } else {
                $SAVEDATA['LOCATION'] = isset($params['LOCATION']) ? addText($params['LOCATION']) : '';
                $SAVEDATA['STATUS'] = $STATUS;
                $SAVEDATA['CHECK_OUT_DATE'] = getCurrentDBDateTime();
            }
            //error_log($SAVEDATA['QUANTITY']);

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_facility', $SAVEDATA, $WHERE);
        }
    }

    public static function jsonSaveFacilityUserItems($params) {

        $FACUSER_ID = isset($params['facUserId']) ? $params['facUserId'] : '';
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $choose_FacilityId = isset($params["CHOOSE_ITEM"]) ? $params["CHOOSE_ITEM"] : '';
        $deadline = isset($params["DEADLINE"]) ? $params["DEADLINE"] : '';
        $type = isset($params["type"]) ? addText($params["type"]) : 1;
        $old_user_facility = '';
        $old_user_facility_quality = '';

        $SAVEDATA = array();

        if (isset($params["QUANTITY"]))
            $SAVEDATA['QUANTITY'] = addText($params["QUANTITY"]);

        if (isset($params["LOCATION"]))
            $SAVEDATA['LOCATION'] = addText($params["LOCATION"]);
            
        if (isset($params["ROOM_ID"]))
            $SAVEDATA['ROOM_ID'] = addText($params["ROOM_ID"]);

        if ($deadline)
            $SAVEDATA['DEADLINE'] = setDate2DB($params["DEADLINE"]);
        if ($type == 2) {
            $SAVEDATA['RECEIVED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['RECEIVED_BY'] = Zend_Registry::get('USER')->CODE;
        } else {
            $SAVEDATA['ISSUED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['ISSUED_BY'] = Zend_Registry::get('USER')->CODE;
        }

        if ($objectId == 'new') {
            $SAVEDATA['ACTION_TYPE'] = $type;
            $SAVEDATA['FACILITY_ID'] = $choose_FacilityId;
            $SAVEDATA['FACUSER_ID'] = isset($params["facUserId"]) ? $params["facUserId"] : '';
            self::dbAccess()->insert('t_facility_user_item', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $object = self::getUserItemFacility($objectId);

            //update old item 
            if ($object) {
                $old_user_facility = $object->FACILITY_ID;
                $old_user_facility_quality = $object->QUANTITY;
                if ($choose_FacilityId) {
                    if ($object->FACILITY_ID != $choose_FacilityId) {

                        $SAVEITEM = array();
                        if ($object->PERMANENT_CHECKOUT) {
                            $SAVEITEM['INSTOCK_QUANTITY'] = $object->QUANTITY_INSTOCK + $object->QUANTITY;
                        } else {

                            $SAVEITEM['LOCATION'] = '';
                            $SAVEITEM['STATUS'] = 'CHECK-IN';
                        }
                        $WHERE = self::dbAccess()->quoteInto("ID = ?", $object->FACILITY_ID);
                        self::dbAccess()->update('t_facility', $SAVEITEM, $WHERE);

                        $SAVEDATA['FACILITY_ID'] = $choose_FacilityId;
                    }
                }

                //
                $SAVEDATA['ACTION_TYPE'] = $type;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update('t_facility_user_item', $SAVEDATA, $WHERE);
            }
        }

        $subParams['oldObjectId'] = $old_user_facility;
        $subParams['OLD_QUANTITY'] = $old_user_facility_quality;
        $subParams['objectId'] = $choose_FacilityId ? $choose_FacilityId : $old_user_facility;
        $subParams['QUANTITY'] = isset($params["QUANTITY"]) ? $params["QUANTITY"] : '';
        $subParams['STATUS'] = ($type == 1) ? 'CHECK-OUT' : 'CHECK-IN';
        $subParams['LOCATION'] = isset($params["LOCATION"]) ? $params["LOCATION"] : '';

        self::updateFacilityItems($subParams);

        return array("success" => true, "objectId" => $objectId);
    }

    public static function getUserItemFacility($Id) {

        $SELECTION_A = array(
            "QUANTITY AS QUANTITY"
            , "ACTION_TYPE AS ACTION_TYPE"
            , "ID AS USER_ITEM_ID"
            , "LOCATION AS LOCATION"
            , "ROOM_ID AS ROOM_ID"
            , "RECEIVED_DATE AS RECEIVED_DATE"
            , "RECEIVED_BY AS RECEIVED_BY"
            , "ISSUED_DATE AS ISSUED_DATE"
            , "ISSUED_BY AS ISSUED_BY"
            , "DEADLINE AS DEADLINE"
        );
        $SELECTION_B = array(
            "NAME AS NAME"
            , "ID AS FACILITY_ID"
            , "COLOR AS COLOR"
            , "DESCRIPTION AS DESCRIPTION"
            , "FACILITY_TYPE AS FACILITY_TYPE"
            , "BARCODE AS BARCODE"
            , "COST AS COST"
            , "INSTOCK_QUANTITY AS QUANTITY_INSTOCK"
            , "DELIVERED_DATE AS DELIVERED_DATE"
            , "EXPIRED_WARRANTY AS EXPIRED_WARRANTY"
            , "PERMANENT_CHECKOUT AS PERMANENT_CHECKOUT"
        );
        $SELECTION_C = array(
            "REFERENCE_NUMBER AS REFERENCE_NUMBER"
            , "NAME AS INVOICE_NAME"
            , "USER_ID AS USER_ID"
        );
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_facility_user_item'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_facility'), 'A.FACILITY_ID=B.ID', $SELECTION_B);
        $SQL->joinLeft(array('C' => 't_facility_user'), 'C.ID=A.FACUSER_ID', $SELECTION_C);
        $SQL->where("A.ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadFacilityUserItem($Id) {

        $facette = self::getUserItemFacility($Id);
        $data = array();

        if ($facette) {
            $object = StaffDBAccess::findStaffFromId($facette->USER_ID);
            $data['ITEM_AVAILABLE_NAME'] = $facette->NAME;
            $data['CHOOSE_ITEM'] = $facette->FACILITY_ID;
            $data['QUANTITY'] = $facette->QUANTITY;
            $data['LOCATION'] = $facette->LOCATION;
            $data['ROOM_ID'] = $facette->ROOM_ID;
            $data['REFERENCE_NUMBER'] = $facette->REFERENCE_NUMBER;
            $data['NAME'] = $facette->INVOICE_NAME;
            $data['FACILITY_NAME'] = $facette->NAME;
            $data['ISSUED_DATE'] = getShowDate($facette->ISSUED_DATE);
            $data['PERSON'] = $object->LASTNAME . " " . $object->FIRSTNAME;
            $data['DEADLINE'] = getShowDate($facette->DEADLINE);
        }
        return array("success" => true, "data" => $data);
    }

    public static function deleteUserItemFacility($id) {

        $object = self::getUserItemFacility($id);
        if ($object) {
            $SAVEITEM = array();
            if ($object->PERMANENT_CHECKOUT) {
                $SAVEITEM['QUANTITY'] = $object->QUANTITY_INSTOCK + $object->QUANTITY;
            } else {

                $SAVEITEM['LOCATION'] = '';
                $SAVEITEM['STATUS'] = ($object->ACTION_TYPE == 1) ? 'CHECK-IN' : 'CHECK-OUT';
            }
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $object->FACILITY_ID);
            self::dbAccess()->update('t_facility', $SAVEITEM, $WHERE);
        }

        self::dbAccess()->delete('t_facility_user_item', array("ID='" . $id . "'"));

        return array(
            "success" => true
        );
    }

    public static function findUserItemFacilityByFacUserId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_user_item', '*');
        $SQL->where("FACUSER_ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function allCheckInByFacUserId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_facility_user_item'), array('*'));
        $SQL->joinLeft(array('B' => 't_facility_user'), 'A.FACUSER_ID=B.ID');
        $SQL->where("B.TARGET_USER = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonAllNotReturnItems($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $result = self::getSqlUserCheckOutItems($params);
        if ($params['objectId']) {
            $check_in = array();
            $facette = self::allCheckInByFacUserId($params['objectId']);
            foreach ($facette as $item) {
                $check_in[] = $item->FACILITY_ID;
            }
        }


        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                if (!in_array($value->FACILITY_ID, $check_in)) {
                    if (!$value->PERMANENT_CHECKOUT) {
                        $data[$i]["REFERENCE_NUMBER"] = $value->REFERENCE_NUMBER . " (" . $value->INVOICE_NAME . " - " . $value->LASTNAME . " " . $value->FIRSTNAME . ")";
                        $data[$i]["USER_INVOICE_ID"] = $value->USER_INVOICE_ID;
                        $data[$i]["FACILITY_ID"] = $value->FACILITY_ID;
                        $data[$i]["ID"] = $value->USER_ITEM_ID;
                        $data[$i]["ITEM_NAME"] = $value->NAME ? $value->NAME : '---';
                        $data[$i]["DELIVERED_DATE"] = getShowDate($value->DELIVERED_DATE);
                        $data[$i]["EXPIRED_WARRANTY"] = getShowDate($value->EXPIRED_WARRANTY);
                        $data[$i]["ITEM_BARCODE"] = $value->BARCODE ? $value->BARCODE : '';
                        $data[$i]["BARCODE"] = $value->BARCODE ? "<img src=\"/facility/barcode/?&code=" . $value->BARCODE . "\" alt=\"" . $value->BARCODE . "\">" : '---';
                        $data[$i]["QUANTITY"] = displayNumberFormat($value->QUANTITY);
                        $data[$i]["COST"] = displayNumberFormat($value->COST) . " " . Zend_Registry::get('SCHOOL')->CURRENCY;
                        $data[$i]["PERMANENT"] = $value->PERMANENT_CHECKOUT;
                        $data[$i]["LOCATION"] = $value->LOCATION ? $value->LOCATION : '---';
                        $data[$i]["ISSUED_DATE"] = getShowDate($value->ISSUED_DATE) ? getShowDate($value->ISSUED_DATE) : '---';
                        $data[$i]["ISSUED_BY"] = $value->ISSUED_BY ? $value->ISSUED_BY : '---';
                        $data[$i]["RECEIVED_DATE"] = getShowDate($value->RECEIVED_DATE) ? getShowDate($value->RECEIVED_DATE) : '---';
                        $data[$i]["RECEIVED_BY"] = $value->RECEIVED_BY ? $value->RECEIVED_BY : '---';
                        $data[$i]["ITEM_ACTION_TYPE"] = $value->ITEM_ACTION_TYPE ? $value->ITEM_ACTION_TYPE : '';
                        $data[$i]["DEADLINE"] = getShowDate($value->DEADLINE);
                        $location = $value->LOCATION ? $value->LOCATION : '---';

                        ++$i;
                    }
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

    public static function getFacUserItemByFacIdAndFacUserId($FacId, $FacUserId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_user_item', array('*'));
        $SQL->where("FACILITY_ID = '" . $FacId . "'");
        $SQL->where("FACUSER_ID = '" . $FacUserId . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonSaveCheckIn($params) {

        $newValue = isset($params['newValue']) ? addText($params["newValue"]) : '';
        $facUserItemId = isset($params['id']) ? addText($params["id"]) : '';
        $object = self::getUserItemFacility($facUserItemId);

        if ($newValue) {
            $subparams['CHOOSE_ITEM'] = $object->FACILITY_ID;
            $subparams['objectId'] = 'new';
            $subparams['facUserId'] = isset($params['facUserId']) ? $params['facUserId'] : '';
            $facUserObject = self::findFacilityUserById($params['facUserId']);
            $subparams['type'] = isset($params['type']) ? addText($params['type']) : '';
            $subparams['LOCATION'] = $newValue;
            $subparams['QUANTITY'] = 1;
            $subparams['DEADLINE'] = getShowDate($object->DEADLINE);
            self::jsonSaveFacilityUserItems($subparams);
        }

        return array(
            "success" => true
            , "error" => ''
        );
    }

    //
}

?>