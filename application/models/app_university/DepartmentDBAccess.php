<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 22.12.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class DepartmentDBAccess {

    private $dataforjson = null;

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new DepartmentDBAccess();
        }
        return $me;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public function getDepartmentDataFromId($Id) {

        $data = array();
        $result = self::findDepartmentFromId($Id);

        if ($result) {
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            $data["SHORT_CONTENT"] = setShowText($result->SHORT_CONTENT);
            $data["CONTENT"] = setShowText($result->CONTENT);
        }

        return $data;
    }

    public static function findDepartmentFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_department";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";

        $result = self::dbAccess()->fetchRow($SQL);

        return $result;
    }

    public function jsonLoadDepartment($Id) {

        $result = self::findDepartmentFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getDepartmentDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function getAllDepartmentsQuery($params) {

        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_department AS A";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        if ($parentId) {
            $SQL .= " AND PARENT='" . $parentId . "'";
        } else {
            $SQL .= " AND PARENT='0'";
        }

        $SQL .= " ORDER BY A.NAME";
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    public static function getComboDataDepartment() {

        $result = self::getAllDepartmentsQuery(false);

        $data = array();
        $data[0] = "[0,'[---]']";
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public function jsonTreeAllDepartments($params) {

        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";
        $userId = isset($params["userId"]) ? $params["userId"] : "";
        $result = self::getAllDepartmentsQuery($params);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if (self::checkChild($value->ID)) {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                } else {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['cls'] = "nodeTextBlue";
                    $data[$i]['leaf'] = true;

                    if ($userId) {

                        $data[$i]['iconCls'] = "icon-application_form_magnify_link";
                        if (self::checkStaffDepartment($userId, $value->ID)) {
                            $data[$i]['checked'] = true;
                        } else {
                            $data[$i]['checked'] = false;
                        }
                    } else {
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                    }
                }
                $i++;
            }

        return $data;
    }

    public function jsonSaveDepartment($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "0";

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SHORT_CONTENT"]))
            $SAVEDATA['SHORT_CONTENT'] = addText($params["SHORT_CONTENT"]);

        if (isset($params["CONTENT"]))
            $SAVEDATA['CONTENT'] = addText($params["CONTENT"]);

        if ($objectId == "new") {
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_department', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_department', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function jsonRemoveDepartment($Id) {

        $SQL = "DELETE FROM t_department";
        $SQL .= " WHERE";
        $SQL .= " ID='" . $Id . "'";
        self::dbAccess()->query($SQL);

        if (self::checkChild($Id)) {
            $SQL = "DELETE FROM t_department";
            $SQL .= " WHERE";
            $SQL .= " PARENT='" . $Id . "'";
            self::dbAccess()->query($SQL);
        }

        return array("success" => true);
    }

    public static function checkStaffDepartment($staffId, $departmentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_campus", array("C" => "COUNT(*)"));
        if ($staffId)
            $SQL->where("STAFF = '" . $staffId . "'");
        if ($departmentId)
            $SQL->where("CAMPUS = '" . $departmentId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_department", array("C" => "COUNT(*)"));
        if ($Id)
            $SQL->where("PARENT = '" . $Id . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>