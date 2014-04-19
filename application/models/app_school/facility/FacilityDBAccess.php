<?php

///////////////////////////////////////////////////////////
// @veasna
// Date: 31.08.2013
// 
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once('excel/excel_reader2.php');
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/CAMEMISUploadDBAccess.php';
require_once 'models/app_school/facility/FacilityUserDBAccess.php';

class FacilityDBAccess {

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

    //// facility type

    public static function findFacilityType($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_type', '*');
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findFacilityTypeChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_type", array("*"));
        $SQL->where("PARENT = '" . $Id . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkFacilityTypeChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_type", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function jsonSearchFacility($params) {
        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $result = self::getAllFacilityQuery($params);

        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["BARCODE"] = $value->BARCODE;
                $data[$i]["QUANTITY"] = $value->QUANTITY;
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
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

    public static function getAllFacilityQuery($params) {

        $END_DATE = isset($params['endDate']) ? $params['endDate'] : '';
        $START_DATE = isset($params['startDate']) ? $params['startDate'] : '';
        $NAME = isset($params['name']) ? $params['name'] : '';
        $BARCODE = isset($params['barcode']) ? $params['barcode'] : '';

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.NAME AS NAME";
        $SQL .= " ,A.BARCODE AS BARCODE";
        $SQL .= " ,A.QUANTITY AS QUANTITY";
        $SQL .= " ,A.DELIVERED_DATE AS START_DATE";
        $SQL .= " ,A.EXPIRED_WARRANTY AS END_DATE";

        $SQL .= " FROM t_facility AS A";

        $SQL .= " WHERE 1=1";
        if ($NAME)
            $SQL .= " AND A.NAME like '" . $NAME . "%'";
        if ($BARCODE)
            $SQL .= " AND A.BARCODE like '" . $BARCODE . "%'";
        if ($START_DATE && $END_DATE)
            $SQL .= " AND A.DELIVERED_DATE >= '" . $START_DATE . "' AND A.EXPIRED_WARRANTY <= '" . $END_DATE . "'";

        //error_log($SQL);

        return self::dbAccess()->fetchAll($SQL);
    }

    protected static function getAllFacilityType($params) {

        $parentId = isset($params["parentId"]) ? $params["parentId"] : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_type', array('*'));

        if ($parentId) {
            $SQL->where("PARENT='" . $parentId . "'");
        } else {
            $SQL->where("PARENT='0'");
        }

        if ($globalSearch) {
            $SQL->where("NAME '" . $globalSearch . "%'");
        }

        $SQL->order("SORTKEY ASC");

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllFacilityType($params) {

        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";
        $result = self::getAllFacilityType($params);

        $i = 0;
        $data = array();

        if ($result) {
            foreach ($result as $value) {

                if ($value->NAME) {
                    if (self::checkFacilityTypeChild($value->ID)) {
                        $data[$i]['leaf'] = false;
                        $data[$i]['id'] = $value->ID;
                        $data[$i]['text'] = stripslashes($value->NAME);
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        $data[$i]['parentId'] = $value->PARENT;
                    } else {
                        $short = $value->SHORT ? stripslashes($value->SHORT) : '?';
                        $data[$i]['leaf'] = true;
                        $data[$i]['id'] = $value->ID;
                        $data[$i]['text'] = "(" . $short . ") " . stripslashes($value->NAME);
                        $data[$i]['cls'] = "nodeTextBlue";

                        if ($value->PARENT == 0) {
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                        } else {
                            $data[$i]['iconCls'] = "icon-application_form_magnify";
                        }
                        $data[$i]['parentId'] = $value->PARENT;
                    }
                    $i++;
                }
            }
        }
        return $data;
    }

    public static function jsonLoadFacilityType($Id) {
        $facette = self::findFacilityType($Id);
        $data = array();
        if ($facette) {
            $data["NAME"] = $facette->NAME;
            $data["SHORT"] = $facette->SHORT;
            $data["COLOR"] = $facette->COLOR;
            $data["SORTKEY"] = $facette->SORTKEY;
            $data["DESCRIPTION"] = $facette->DESCRIPTION;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function jsonSaveFacilityType($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';

        $SAVEDATA = array();

        $SAVEDATA['NAME'] = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $SAVEDATA['SHORT'] = isset($params["SHORT"]) ? addText($params["SHORT"]) : "";
        $SAVEDATA['SORTKEY'] = isset($params["SORTKEY"]) ? addText($params["SORTKEY"]) : 0;
        $SAVEDATA['COLOR'] = isset($params["COLOR"]) ? addText($params["COLOR"]) : 0;
        $SAVEDATA['DESCRIPTION'] = isset($params["DESCRIPTION"]) ? $params["DESCRIPTION"] : "";

        if ($objectId == 'new') {
            $SAVEDATA['PARENT'] = isset($params["parentId"]) ? $params["parentId"] : 0;
            self::dbAccess()->insert('t_facility_type', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_facility_type', $SAVEDATA, $WHERE);
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function deleteParendsChilesTypeTree($id) {

        $SQL_DELETE = "DELETE FROM t_facility_type";
        $SQL_DELETE .= " WHERE";
        $SQL_DELETE .= " ID IN (" . $id . ")";
        //error_log($SQL);
        self::dbAccess()->query($SQL_DELETE);
        self::dbAccess()->delete('t_facility', array("FACILITY_TYPE='" . $id . "'"));

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_type", array("*"));
        $SQL->where("PARENT IN (" . $id . ")");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            $idchilearr = array();
            foreach ($result as $values) {
                $idchilearr[] = $values->ID;
            }
            $chileID = implode(",", $idchilearr);

            return self::deleteParendsChilesTypeTree($chileID);
        } else {

            return 0;
        }
    }

    public static function deleteFacilityType($id) {

        //self::dbAccess()->delete('t_facility_type', array("ID='" . $id . "'"));
        self::deleteParendsChilesTypeTree($id);
        // must delete all items under the type....... 
        // more delete..
        return array("success" => true);
    }

    //// facility
    public static function findFacilityItem($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility', '*');
        $SQL->where("ID = '" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getCountAvailableSubItem($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        $SQL->where("STATUS = 'CHECK-IN'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getCountSubItem($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = '" . $Id . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getAllFacilityItem($params) {

        $parentId = isset($params["parentId"]) ? $params["parentId"] : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $facility_type = isset($params["type"]) ? addText($params["type"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility', array('*'));

        if ($parentId) {
            $SQL->where("PARENT='" . $parentId . "'");
        } else {
            $SQL->where("PARENT='0'");
        }

        if ($facility_type) {
            $SQL->where("FACILITY_TYPE = ?", $facility_type);
        }

        if ($globalSearch) {
            $SQL->where("NAME '" . $globalSearch . "%'");
        }

        $SQL->order("NAME ASC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //@veasna
    public static function getItemFacilityItem($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $facility_type = isset($params["type"]) ? addText($params["type"]) : "";

        $SELECTION_A = array(
            "NAME AS NAME"
            , "ID AS ID"
            , "COLOR AS COLOR"
            , "DESCRIPTION AS DESCRIPTION"
            , "BARCODE AS BARCODE"
            , "COST AS COST"
            , "QUANTITY AS QUANTITY"
            , "INSTOCK_QUANTITY AS INSTOCK_QUANTITY"
            , "DELIVERED_DATE AS DELIVERED_DATE"
            , "EXPIRED_WARRANTY AS EXPIRED_WARRANTY"
            , "CREATED_BY AS CREATED_BY"
            , "CREATED_DATE AS CREATED_DATE"
            , "LOCATION AS LOCATION"
            , "PERMANENT_CHECKOUT AS PERMANENT_CHECKOUT"
            , "STATUS AS STATUS"
        );
        $SELECTION_B = array(
            "NAME AS PARENT_NAME"
            , "PARENT AS PARENT"
        );
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_facility'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_facility'), 'A.PARENT=B.ID', $SELECTION_B);
        $SQL->where("A.PARENT <> 0 ");

        if ($facility_type) {
            $SQL->where("A.FACILITY_TYPE = ?", $facility_type);
        }

        if ($globalSearch) {
            $SQL->where("A.NAME '" . $globalSearch . "%'");
        }

        $SQL->order("A.NAME ASC");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonLoadAvailableFacilityItem($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $result = self::getItemFacilityItem($params);

        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                if ($value->PERMANENT_CHECKOUT) {
                    if ($value->INSTOCK_QUANTITY != 0) {
                        $data[$i]["ID"] = $value->ID;
                        $data[$i]["ITEM_STATUS"] = "---";
                        $data[$i]["DELIVERED_DATE"] = getShowDate($value->DELIVERED_DATE);
                        $data[$i]["EXPIRED_WARRANTY"] = getShowDate($value->EXPIRED_WARRANTY);
                        $data[$i]["BARCODE"] = $value->BARCODE ? "<img src=\"/facility/barcode/?&code=" . $value->BARCODE . "\" alt=\"" . $value->BARCODE . "\">" : '---';
                        $data[$i]["QUANTITY"] = displayNumberFormat($value->QUANTITY);
                        $data[$i]["INSTOCK_QUANTITY"] = displayNumberFormat($value->INSTOCK_QUANTITY);
                        $data[$i]["COST"] = displayNumberFormat($value->COST) . " " . Zend_Registry::get('SCHOOL')->CURRENCY;
                        $data[$i]["LOCATION"] = $value->LOCATION;
                        $data[$i]["PERMANENT"] = $value->PERMANENT_CHECKOUT;
                        $data[$i]["PARENT"] = $value->PARENT_NAME;
                        $data[$i]["ITEM_BARCODE"] = $value->BARCODE;
                    }
                } else {
                    if ($value->STATUS == 'CHECK-IN') {
                        $data[$i]["ID"] = $value->ID;
                        $data[$i]["ITEM_STATUS"] = "---";
                        $data[$i]["DELIVERED_DATE"] = getShowDate($value->DELIVERED_DATE);
                        $data[$i]["EXPIRED_WARRANTY"] = getShowDate($value->EXPIRED_WARRANTY);
                        $data[$i]["BARCODE"] = $value->BARCODE ? "<img src=\"/facility/barcode/?&code=" . $value->BARCODE . "\" alt=\"" . $value->BARCODE . "\">" : '---';
                        $data[$i]["QUANTITY"] = $value->QUANTITY ? displayNumberFormat($value->QUANTITY) : '1';
                        $data[$i]["INSTOCK_QUANTITY"] = $value->INSTOCK_QUANTITY ? displayNumberFormat($value->INSTOCK_QUANTITY) : '1';
                        $data[$i]["COST"] = displayNumberFormat($value->COST) . " " . Zend_Registry::get('SCHOOL')->CURRENCY;
                        $data[$i]["LOCATION"] = $value->LOCATION;
                        $data[$i]["PERMANENT"] = $value->PERMANENT_CHECKOUT;
                        $data[$i]["PARENT"] = $value->PARENT_NAME;
                        $data[$i]["ITEM_BARCODE"] = $value->BARCODE;
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

    // 

    public static function jsonTreeAllFacilityItem($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $params["parentId"] = $node;
        $result = self::getAllFacilityItem($params);

        $COUNT_IN_STOCK = 0;

        if ($result) {
            foreach ($result as $value) {
                if ($value->PERMANENT_CHECKOUT) {
                    $subParams['parentId'] = $value->ID;
                    $subObject = self::getAllFacilityItem($subParams);
                    foreach ($subObject as $v) {
                        $COUNT_IN_STOCK += $v->INSTOCK_QUANTITY;
                    }
                }
            }
        }

        if ($node)
            self::updateInstockQuantity($node, $COUNT_IN_STOCK);

        $i = 0;
        $data = array();

        if ($result) {
            foreach ($result as $value) {

                if ($node == 0) {

                    $data[$i]['leaf'] = false;
                    $data[$i]['id'] = $value->ID;
                    if ($value->PERMANENT_CHECKOUT) {
                        $subParams['parentId'] = $value->ID;
                        $obejct = self::getAllFacilityItem($subParams);
                        $inStock = 0;
                        foreach ($obejct as $item) {
                            $inStock+=$item->INSTOCK_QUANTITY;
                        }
                        $data[$i]['text'] = stripslashes($value->NAME) . " (" . $inStock . ")";
                        $data[$i]['iconCls'] = "icon-folder_wrench";
                    } else {
                        $data[$i]['text'] = stripslashes($value->NAME) . " (" . self::getCountAvailableSubItem($value->ID) . ")";
                        $data[$i]['iconCls'] = "icon-folder_up";
                    }

                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['parentId'] = $value->PARENT;
                } else {

                    if (self::getCountSubItem($value->ID)) {
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-folder_up";
                        $data[$i]['cls'] = "nodeTextBold";
                    } else {
                        $data[$i]['leaf'] = true;
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        $data[$i]['cls'] = "nodeTextBlue";
                    }

                    $data[$i]['id'] = $value->ID;
                    $data[$i]['text'] = $value->NAME ? stripslashes($value->NAME) : "?";
                }

                $i++;
            }
        }
        return $data;
    }

    public static function jsonLoadFacilityItem($Id) {

        $facette = self::findFacilityItem($Id);
        $data = array();
        if ($facette) {

            $typeObject = self::findFacilityType($facette->FACILITY_TYPE);
            if ($typeObject) {
                $data['FACILITY_TYPE'] = $typeObject->NAME;
                $data["CHOOSE_FACILITY_TYPE"] = $typeObject->ID;
            }

            $data['STATUS'] = $facette->STATUS;
            $data["NAME"] = $facette->NAME;
            $data["COLOR"] = $facette->COLOR;
            $data["BARCODE"] = $facette->BARCODE;
            $data["DESCRIPTION"] = $facette->DESCRIPTION;
            $data["COST"] = $facette->COST;
            $data["QUANTITY"] = displayNumberFormat($facette->QUANTITY);
            $data["DELIVERED_DATE"] = getShowDate($facette->DELIVERED_DATE);
            $data["EXPIRED_WARRANTY"] = getShowDate($facette->EXPIRED_WARRANTY);
            $data["LOCATION"] = $facette->LOCATION;
            $data["PERMANENT_CHCK_OUT"] = $facette->PERMANENT_CHECKOUT ? true : false;
            $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
            $data["CREATED_BY"] = setShowText($facette->CREATED_BY);

            if ($facette->PARENT) {
                $fields = self::getFieldsFromId($facette->ID);
                if ($fields) {
                    foreach ($fields as $value) {
                        switch ($value->CHOOSE_TYPE) {
                            case 1:
                                $data["CHECKBOX_" . $value->FIELD] = true;
                                break;
                            case 2:
                                $data["RADIOBOX_" . $value->FIELD] = setShowText($value->DESCRIPTION);
                                break;
                            case 3:
                                $data["INPUTFIELD_" . $value->FIELD] = setShowText($value->DESCRIPTION);
                                break;
                            case 4:
                                $data["TEXTAREA_" . $value->FIELD] = setShowText($value->DESCRIPTION);
                                break;
                            case 5:
                                $data["DATE_" . $value->FIELD] = getShowDate($value->DESCRIPTION);
                                break;
                        }
                    }
                }
            }
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function jsonSaveFacilityItem($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $parentId = isset($params["parentId"]) ? $params["parentId"] : 0;
        $DELIVERED_DATE = isset($params["DELIVERED_DATE"]) ? $params["DELIVERED_DATE"] : '';
        $EXPIRED_WARRANTY = isset($params["EXPIRED_WARRANTY"]) ? $params["EXPIRED_WARRANTY"] : '';

        $facette = self::findFacilityItem($objectId);

        if ($facette) {
            $fields = self::dbAccess()->fetchAll("SELECT * FROM t_facility_description");

            $SELECTED_CHECK_FIELD = Array();
            $SELECTED_RADIO_FIELD = Array();
            $SELECTED_INPUT_FIELD = Array();
            $SELECTED_TEXTAREA_FIELD = Array();
            $SELECTED_DATE_FIELD = Array();

            if ($fields) {
                foreach ($fields as $value) {
                    $checkField = isset($params["CHECKBOX_" . $value->ID]) ? $params["CHECKBOX_" . $value->ID] : "";
                    if ($checkField) {
                        $SELECTED_CHECK_FIELD[$value->ID] = 1;
                    }

                    $radioField = isset($params["RADIOBOX_" . $value->ID]) ? $params["RADIOBOX_" . $value->ID] : "";
                    if ($radioField) {
                        $SELECTED_RADIO_FIELD[$value->ID] = $radioField;
                    }

                    $inputField = isset($params["INPUTFIELD_" . $value->ID]) ? $params["INPUTFIELD_" . $value->ID] : "";
                    if ($inputField) {
                        $SELECTED_INPUT_FIELD[$value->ID] = $inputField;
                    }

                    $textareaField = isset($params["TEXTAREA_" . $value->ID]) ? $params["TEXTAREA_" . $value->ID] : "";
                    if ($textareaField) {
                        $SELECTED_TEXTAREA_FIELD[$value->ID] = $textareaField;
                    }

                    $dateField = isset($params["DATE_" . $value->ID]) ? $params["DATE_" . $value->ID] : "";
                    if ($dateField) {
                        $SELECTED_DATE_FIELD[$value->ID] = setDate2DB($dateField);
                    }
                }
            }

            $result = $SELECTED_CHECK_FIELD + $SELECTED_RADIO_FIELD + $SELECTED_INPUT_FIELD + $SELECTED_TEXTAREA_FIELD + $SELECTED_DATE_FIELD;

            if ($result) {
                self::dbAccess()->delete('t_facility_item_field', array('ITEM = ? ' => $facette->ID));
                foreach ($result as $fieldId => $fieldValue) {
                    $FIELD_ITEM_DATA['ITEM'] = $facette->ID;
                    $FIELD_ITEM_DATA['FIELD'] = $fieldId;
                    $FIELD_ITEM_DATA['DESCRIPTION'] = addText($fieldValue);
                    self::dbAccess()->insert('t_facility_item_field', $FIELD_ITEM_DATA);
                }
            }
        }

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["QUANTITY"]))
            $SAVEDATA['QUANTITY'] = addText($params["QUANTITY"]);

        if (isset($params["QUANTITY"]))
            $SAVEDATA['INSTOCK_QUANTITY'] = addText($params["QUANTITY"]);

        if (isset($params["COST"]))
            $SAVEDATA['COST'] = addText($params["COST"]);

        if (isset($params["BARCODE"]))
            $SAVEDATA['BARCODE'] = addText($params["BARCODE"]);

        if (isset($params["LOCATION"]))
            $SAVEDATA['LOCATION'] = addText($params["LOCATION"]);

        if ($DELIVERED_DATE)
            $SAVEDATA['DELIVERED_DATE'] = setDate2DB($params["DELIVERED_DATE"]);

        if ($EXPIRED_WARRANTY)
            $SAVEDATA['EXPIRED_WARRANTY'] = setDate2DB($params["EXPIRED_WARRANTY"]);

        if (isset($params["CHOOSE_FACILITY_TYPE"]))
            $SAVEDATA['FACILITY_TYPE'] = addText($params["CHOOSE_FACILITY_TYPE"]);

        if (isset($params["STATUS"]))
            $SAVEDATA['STATUS'] = addText($params["STATUS"]);

        $SAVEDATA['PERMANENT_CHECKOUT'] = isset($params["PERMANENT_CHCK_OUT"]) ? 1 : 0;

        if (!$facette) {
            $SAVEDATA['PARENT'] = $parentId;

            if ($parentId) {
                $parentObject = self::findFacilityItem($parentId);
                if ($parentObject) {
                    $SAVEDATA['PERMANENT_CHECKOUT'] = $parentObject->PERMANENT_CHECKOUT;
                    $SAVEDATA['FACILITY_TYPE'] = $parentObject->FACILITY_TYPE;
                }
            }

            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_facility', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {

            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_facility', $SAVEDATA, $WHERE);
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function deleteParendsChilesItemTree($id) {

        $SQL_DELETE = "DELETE FROM t_facility";
        $SQL_DELETE .= " WHERE";
        $SQL_DELETE .= " ID IN (" . $id . ")";
        //error_log($SQL);
        self::dbAccess()->query($SQL_DELETE);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility", array("*"));
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

    public static function deleteFacilityItem($id) {

        self::deleteParendsChilesItemTree($id);
        return array("success" => true);
    }

    //@veasna
    public static function findFacilityStatus($params) {
        $type = isset($params['type']) ? $params['type'] : '';
        $facilityId = isset($params['facilityId']) ? $params['facilityId'] : '';
        $SQL = self::dbAccess()->select();
        $SQL->from('t_facility_user_item', '*');
        $SQL->where("FACILITY_ID = '" . $facilityId . "'");
        if ($type)
            $SQL->where("ACTION_TYPE = '" . $type . "'");

        if ($type == 1) {
            $SQL->order('ISSUED_DATE DESC');
        } else {
            $SQL->order('RECEIVED_DATE DESC');
        }
        $SQL->limit(1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //

    public static function jsonSearchFacilityItem($params) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $result = self::getAllFacilityItem($params);

        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $location = $value->LOCATION ? $value->LOCATION : "---";
                $date = '?';
                if (!$value->PERMANENT_CHECKOUT) {
                    $params['facilityId'] = $value->ID;
                    if ($value->STATUS == 'CHECK-OUT') {
                        $params['type'] = 1;
                        $object = self::findFacilityStatus($params);
                        $date = $object ? getShowDate($object->ISSUED_DATE) : '---';
                    } else {
                        $params['type'] = 2;
                        $object = self::findFacilityStatus($params);
                        $date = $object ? getShowDate($object->RECEIVED_DATE) : '---';
                    }
                }
                $status = ($value->STATUS == 'CHECK-IN') ? CHECK_IN : CHECK_OUT;
                $data[$i]["STATUS"] = $location . "<br/>" . $status . " (" . $date . ")";
                $data[$i]["ID"] = $value->ID;
                $data[$i]["ITEM_STATUS"] = $value->STATUS ? $value->STATUS : '---';
                $data[$i]["DELIVERED_DATE"] = getShowDate($value->DELIVERED_DATE);
                $data[$i]["EXPIRED_WARRANTY"] = getShowDate($value->EXPIRED_WARRANTY);
                $data[$i]["BARCODE"] = $value->BARCODE ? "<img src=\"/facility/barcode/?&code=" . $value->BARCODE . "\" alt=\"" . $value->BARCODE . "\">" : "---";
                $data[$i]["QUANTITY"] = displayNumberFormat($value->QUANTITY);
                $data[$i]["COST"] = displayNumberFormat($value->COST) . " " . Zend_Registry::get('SCHOOL')->CURRENCY;
                $data[$i]["LOCATION"] = $location;
                $data[$i]["PERMANENT"] = $value->PERMANENT_CHECKOUT;
                $data[$i]["INSTOCK_QUANTITY"] = $value->INSTOCK_QUANTITY;

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

    public static function checkFacilityBarcode($barcodeId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility", array("C" => "COUNT(*)"));
        $SQL->where("BARCODE = '" . $barcodeId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function jsonCheckBarcodeID($barcodeId) {

        $check = self::checkFacilityBarcode($barcodeId);

        if ($check) {
            return array("success" => false, "status" => false, "errors" => "Barcode Have Already");
        } else {
            return array("success" => true, "status" => true);
        }
    }

    public static function actionFieldSetting2Category($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selecteds = isset($params["selecteds"]) ? addText($params["selecteds"]) : "";
        $facette = self::findFacilityType($objectId);

        if ($facette) {
            $SAVEDATA['FIELD_SETTING'] = $selecteds;
            $WHERE[] = "ID = '" . $facette->ID . "'";
            self::dbAccess()->update('t_facility_type', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    public static function getFieldsFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_facility_item_field'), array('ITEM', 'FIELD', 'DESCRIPTION'));
        $SQL->joinLeft(array('B' => 't_facility_description'), 'A.FIELD=B.ID', array('CHOOSE_TYPE'));
        $SQL->where("A.ITEM = '" . $Id . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////
    //Import: Item...
    public static function importXLS($params) {

        $xls = new Spreadsheet_Excel_Reader($_FILES["xlsfile"]['tmp_name']);
        $parentId = isset($params["parentId"]) ? $params["parentId"] : "0";
        $parentObject = self::findFacilityItem($parentId);

        $importCount = 0;

        for ($iCol = 1; $iCol <= $xls->sheets[0]['numCols']; $iCol++) {

            $field = isset($xls->sheets[0]['cells'][1][$iCol]) ? $xls->sheets[0]['cells'][1][$iCol] : "";

            switch ($field) {
                case "NAME":
                    $Col_NAME = $iCol;
                    break;
                case "BARCODE":
                    $Col_BARCODE = $iCol;
                    break;
                case "COST":
                    $Col_COST = $iCol;
                    break;
                case "QUANTITY":
                    $Col_QUANTITY = $iCol;
                    break;
                case "LOCATION":
                    $Col_LOCATION = $iCol;
                    break;
                case "DELIVERED_DATE":
                    $Col_DELIVERED_DATE = $iCol;
                    break;
                case "EXPIRED_WARRANTY":
                    $Col_EXPIRED_WARRANTY = $iCol;
                    break;
            }
        }

        for ($i = 1; $i <= $xls->sheets[0]['numRows']; $i++) {

            $NAME = isset($xls->sheets[0]['cells'][$i + 2][$Col_NAME]) ? $xls->sheets[0]['cells'][$i + 2][$Col_NAME] : "";
            $BARCODE = isset($xls->sheets[0]['cells'][$i + 2][$Col_BARCODE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_BARCODE] : "";
            $COST = isset($xls->sheets[0]['cells'][$i + 2][$Col_COST]) ? $xls->sheets[0]['cells'][$i + 2][$Col_COST] : "";
            $QUANTITY = isset($xls->sheets[0]['cells'][$i + 2][$Col_QUANTITY]) ? $xls->sheets[0]['cells'][$i + 2][$Col_QUANTITY] : "";
            $LOCATION = isset($xls->sheets[0]['cells'][$i + 2][$Col_LOCATION]) ? $xls->sheets[0]['cells'][$i + 2][$Col_LOCATION] : "";
            $DELIVERED_DATE = isset($xls->sheets[0]['cells'][$i + 2][$Col_DELIVERED_DATE]) ? $xls->sheets[0]['cells'][$i + 2][$Col_DELIVERED_DATE] : "";
            $EXPIRED_WARRANTY = isset($xls->sheets[0]['cells'][$i + 2][$Col_EXPIRED_WARRANTY]) ? $xls->sheets[0]['cells'][$i + 2][$Col_EXPIRED_WARRANTY] : "";

            switch (UserAuth::systemLanguage()) {
                case "VIETNAMESE":
                    $IMPORT_DATA['NAME'] = setImportChartset($NAME);
                    $IMPORT_DATA['BARCODE'] = addText($BARCODE);
                    $IMPORT_DATA['COST'] = addText($COST);
                    $IMPORT_DATA['QUANTITY'] = addText($QUANTITY);
                    $IMPORT_DATA['LOCATION'] = setImportChartset($LOCATION);
                    break;
                default:
                    $IMPORT_DATA['NAME'] = addText($NAME);
                    $IMPORT_DATA['BARCODE'] = addText($BARCODE);
                    $IMPORT_DATA['COST'] = addText($COST);
                    $IMPORT_DATA['QUANTITY'] = addText($QUANTITY);
                    $IMPORT_DATA['LOCATION'] = addText($LOCATION);
                    break;
            }

            if (isset($DELIVERED_DATE)) {
                if ($DELIVERED_DATE != "") {
                    $date = str_replace("/", ".", $DELIVERED_DATE);
                    if ($date) {
                        $explode = explode(".", $date);
                        $d = isset($explode[0]) ? trim($explode[0]) : "00";
                        $m = isset($explode[1]) ? trim($explode[1]) : "00";
                        $y = isset($explode[2]) ? trim($explode[2]) : "0000";
                        $IMPORT_DATA['DELIVERED_DATE'] = $y . "-" . $m . "-" . $d;
                    } else {
                        $IMPORT_DATA['DELIVERED_DATE'] = "0000-00-00";
                    }
                }
            } else {
                $IMPORT_DATA['DELIVERED_DATE'] = "0000-00-00";
            }

            if (isset($EXPIRED_WARRANTY)) {
                if ($EXPIRED_WARRANTY != "") {
                    $date = str_replace("/", ".", $EXPIRED_WARRANTY);
                    if ($date) {
                        $explode = explode(".", $date);
                        $d = isset($explode[0]) ? trim($explode[0]) : "00";
                        $m = isset($explode[1]) ? trim($explode[1]) : "00";
                        $y = isset($explode[2]) ? trim($explode[2]) : "0000";
                        $IMPORT_DATA['EXPIRED_WARRANTY'] = $y . "-" . $m . "-" . $d;
                    } else {
                        $IMPORT_DATA['EXPIRED_WARRANTY'] = "0000-00-00";
                    }
                }
            } else {
                $IMPORT_DATA['EXPIRED_WARRANTY'] = "0000-00-00";
            }

            $IMPORT_DATA['PARENT'] = $parentId;
            $IMPORT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $IMPORT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            if ($NAME && $parentObject) {
                $importCount += $QUANTITY;
                $IMPORT_DATA['FACILITY_TYPE'] = $parentObject->FACILITY_TYPE;
                $IMPORT_DATA['PERMANENT_CHECKOUT'] = $parentObject->PERMANENT_CHECKOUT;
                $IMPORT_DATA['STATUS'] = "CHCK-IN";
                self::dbAccess()->insert('t_facility', $IMPORT_DATA);
            }
        }

        self::updateInstockQuantity($parentId, $importCount);
    }

    public static function updateInstockQuantity($Id, $count) {
        $facette = self::findFacilityItem($Id);
        if ($facette) {
            $SAVEDATA["INSTOCK_QUANTITY"] = $facette->INSTOCK_QUANTITY + $count;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
            self::dbAccess()->update('t_facility', $SAVEDATA, $WHERE);
        }
    }

    ////////////////////////////////////////////////////////////////////////////

    public static function mappingFieldSetting4Child($Id) {

        $childObject = self::findFacilityType($Id);
        if ($childObject) {
            $parentObject = self::findFacilityType($childObject->PARENT);
        }

        if ($parentObject && $childObject) {

            if (!$childObject->FIELD_SETTING) {
                $SAVEDATA["FIELD_SETTING"] = $parentObject->FIELD_SETTING;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                self::dbAccess()->update('t_facility_type', $SAVEDATA, $WHERE);
            }
        }
    }

    public static function mappingType4Child($Id) {
        $childObject = self::findFacilityItem($Id);
        if ($childObject) {
            $parentObject = self::findFacilityItem($childObject->PARENT);
        }
        if ($parentObject && $childObject) {
            if (!$childObject->FACILITY_TYPE) {
                $SAVEDATA["FACILITY_TYPE"] = $parentObject->FACILITY_TYPE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                self::dbAccess()->update('t_facility', $SAVEDATA, $WHERE);
            }
        }
    }

}

?>