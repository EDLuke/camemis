<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class AbsentTypeDBAccess {

    public $data = array();
    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }

    public static function allAbsentType($objectType, $actionType = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_absent_type", array("*"));
        $SQL->where("STATUS = 1");
        $SQL->where("OBJECT_TYPE = '" . $objectType . "'");
        if ($actionType)
        {
            $SQL->where("TYPE IN (0,$actionType)");
        }
        $SQL->order("SORTKEY ASC");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getObjectDataFromId($Id)
    {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result)
        {

            $data["ID"] = $result->ID;
            $data["CODE"] = setShowText($result->CODE);
            $data["COLOR"] = setShowText($result->COLOR);
            $data["OBJECT_TYPE"] = $result->OBJECT_TYPE;
            $data["TYPE"] = $result->TYPE;
            $data["SORTKEY"] = $result->SORTKEY;
            $data["ABSENT_NAME"] = setShowText($result->NAME);
            $data["STATUS"] = setShowText($result->STATUS);

            if ($result->DESCRIPTION)
            {
                $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            }
            else
            {
                $data["DESCRIPTION"] = "---";
            }
        }

        return $data;
    }

    public static function findObjectFromId($Id)
    {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_absent_type";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonReleaseAbsentType($params)
    {

        $objectId = $params["objectId"] ? addText($params["objectId"]) : "0";
        $facette = self::findObjectFromId($objectId);
        $status = $facette->STATUS;

        $data = array();
        switch ($status)
        {
            case 0:
                $newStatus = 1;
                $data["STATUS"] = 1;
                $data["ENABLED_DATE"] = "'" . getCurrentDBDateTime() . "'";
                $data["ENABLED_BY"] = "'" . Zend_Registry::get('USER')->CODE . "'";
                break;
            case 1:
                $newStatus = 0;
                $data["STATUS"] = 0;
                $data["DISABLED_DATE"] = "'" . getCurrentDBDateTime() . "'";
                $data["DISABLED_BY"] = "'" . Zend_Registry::get('USER')->CODE . "'";
                break;
        }
        self::dbAccess()->update("t_absent_type", $data, "ID=". $objectId );
        return array("success" => true, "status" => $newStatus);
    }

    public static function loadObject($Id)
    {

        $result = self::findObjectFromId($Id);

        if ($result)
        {
            $o = array(
                "success" => true
                , "data" => self::getObjectDataFromId($Id)
            );
        }
        else
        {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }

        return $o;
    }

    public static function createOnlyItem($params)
    {

        $name = isset($params["name"]) ? addText($params["name"]) : "";
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : "";

        $SQL = "
            INSERT INTO t_absent_type SET
            NAME = '" . addText($name) . "'
            ,OBJECT_TYPE = '" . addText($objectType) . "'
            ,CREATED_DATE = '" . getCurrentDBDateTime() . "'
            ,CREATED_BY = '" . Zend_Registry::get('USER')->CODE . "'
            ";
        self::dbAccess()->query($SQL);

        return array("success" => true);
    }

    public static function jsonTreeAllAbsentType($params)
    {

        $data = array();
        $result = self::getAllAbsentType($params);

        $i = 0;
        if ($result)
        {
            foreach ($result as $value)
            {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = "(" . setShowText($value->CODE) . ") " . setShowText($value->NAME);
                $data[$i]['iconCls'] = "icon-application_form_magnify";
                $data[$i]['cls'] = "nodeTextBlue";
                $data[$i]['leaf'] = true;

                $i++;
            }
        }
        return $data;
    }

    public static function getAllAbsentType($params)
    {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : "";
        $status = isset($params["status"]) ? addText($params["status"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_absent_type";
        $SQL .= " WHERE 1=1";

        if ($globalSearch)
        {
            $SQL .= " AND ((NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }
        if ($status)
        {
            $SQL .= " AND STATUS = '1'";
        }

        if ($type)
        {
            switch (strtoupper($type))
            {
                case 'DAILY':
                    $SQL .= " AND TYPE = 1";
                    break;
                case 'BLOCK':
                    $SQL .= " AND TYPE = 2";
                    break;
            }
        }

        switch ($objectType)
        {
            case "STUDENT":
                $SQL .= " AND OBJECT_TYPE = 'STUDENT'";
                break;
            case "STAFF":
                $SQL .= " AND OBJECT_TYPE = 'STAFF'";
        }

        $SQL .= " ORDER BY SORTKEY ASC";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonSaveAbsentType($params)
    {

        $SAVEDATA = array();
        $WHERE = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findObjectFromId($objectId);

        if (isset($params["ABSENT_NAME"]))
        {
            $SAVEDATA['NAME'] = addText($params["ABSENT_NAME"]);
        }

        if (isset($params["objectType"]))
            $SAVEDATA['OBJECT_TYPE'] = addText($params["objectType"]);

        if (isset($params["TYPE"]))
            $SAVEDATA['TYPE'] = addText($params["TYPE"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA['SORTKEY'] = addText($params["SORTKEY"]);

        if (isset($params["CODE"]))
            $SAVEDATA['CODE'] = addText($params["CODE"]);

        if ($facette)
        {
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_absent_type', $SAVEDATA, $WHERE);
        }
        else
        {
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert('t_absent_type', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public static function jsonRemoveAbsentType($Id)
    {
        self::dbAccess()->delete('t_absent_type', array("ID='" . $Id . "'"));
        return array(
            "success" => true
        );
    }

}

?>