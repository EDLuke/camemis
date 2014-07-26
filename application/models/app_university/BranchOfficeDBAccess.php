<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 04.07.2010 
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/SessionAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class BranchOfficeDBAccess {

    protected $data = array();
    protected $out = array();
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

    public static function getObjectDataFromId($Id) {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["SORTKEY"] = setShowText($result->SORTKEY);
            $data["NAME"] = setShowText($result->NAME);
            $data["EMAIL"] = setShowText($result->EMAIL);
            $data["PHONE"] = setShowText($result->PHONE);
            $data["CONTACT_PERSON"] = setShowText($result->CONTACT_PERSON);
        }

        return $data;
    }

    public static function findObjectFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_branch_office";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function loadObject($Id) {

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

    public static function removeObject($params) {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findObjectFromId($removeId);

        if ($facette) {
            if ($facette->PARENT) {
                self::dbAccess()->delete("t_branch_office", "PARENT = '" . $removeId . "'");
            }
        }

        self::sqlRemoveObject($removeId);
        return array("success" => true);
    }

    public static function sqlRemoveObject($Id) {
        self::dbAccess()->delete("t_branch_office", "ID = '" . $Id . "'");
    }

    public static function updateObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $name = isset($params["NAME"]) ? addText($params["NAME"]) : "";
        $email = isset($params["EMAIL"]) ? addText($params["EMAIL"]) : "";
        $phone = isset($params["PHONE"]) ? addText($params["PHONE"]) : "";
        $contact_person = isset($params["CONTACT_PERSON"]) ? addText($params["CONTACT_PERSON"]) : "";
        $short = isset($params["SHORT"]) ? addText($params["SHORT"]) : "";
        $sortkey = isset($params["SORTKEY"]) ? addText($params["SORTKEY"]) : "";
        
        if ($objectId == "new") {
            $SAVEDATA = array();
            $SAVEDATA['NAME'] = addText($name);
            $SAVEDATA['EMAIL'] = addText($email);
            $SAVEDATA['PHONE'] = addText($phone);
            $SAVEDATA['SHORT'] = addText($short);
            $SAVEDATA['SORTKEY'] = addText($sortkey);
            $SAVEDATA['CONTACT_PERSON'] = addText($contact_person);
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_branch_office', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $data = array();
            $data['NAME']    = "'". addText($name) ."'";
            $data['EMAIL']   = "'". addText($email) ."'";
            $data['PHONE']   = "'". addText($phone) ."'";
            $data['SHORT']   = "'". addText($short) ."'";
            $data['SORTKEY'] = "'". addText($sortkey) ."'";
            $data['CONTACT_PERSON'] = "'". addText($contact_person) ."'";
            self::dbAccess()->update("t_branch_office", $data, "ID='". $objectId ."'");
        }

        return array("success" => true, 'objectId' => $objectId);
    }

    public static function jsonTreeAllBranchOffices($params) {

        $data = array();
        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $SQL = "SELECT * FROM t_branch_office";
        $SQL .= " WHERE 1=1";
        if (!$node)
            $SQL .= " AND PARENT=0";
        else
            $SQL .= " AND PARENT=" . $node . "";
        $SQL .= " ORDER BY SORTKEY ASC";
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            $i = 0;
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
                    $data[$i]['iconCls'] = "icon-application_form_magnify";
                    $data[$i]['leaf'] = true;
                }
                $i++;
            }
        }

        return $data;
    }

    public static function jsonAllBranchOffices($parentId) {

        //error_log($parentId);
        $SQL = "SELECT * FROM t_branch_office";
        $SQL .= " WHERE 1=1";
        if ($parentId)
            $SQL .= " AND PARENT='" . $parentId . "'";
        else
            $SQL .= " AND PARENT='0'";
        $SQL .= " ORDER BY SORTKEY ASC";

        $result = self::dbAccess()->fetchAll($SQL);
        $data = array();
        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_branch_office", array("C" => "COUNT(*)"));
        if ($Id)
            $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>