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

class LocationDBAccess {

    protected $data = array();
    protected $out = array();
    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }

    public static function getObjectDataFromId($Id)
    {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result)
        {
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
        }

        return $data;
    }

    public static function findObjectFromId($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_location", array("*"));
        $SQL->where("ID = ?",$Id);
        return self::dbAccess()->fetchRow($SQL);
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

    public static function addObject($params)
    {

        $SAVEDATA = array();
        if (isset($params["name"]))
        {
            $SAVEDATA['NAME'] = addText($params["name"]);
        }

        if (isset($params["NAME"]))
        {
            $SAVEDATA['NAME'] = addText($params["NAME"]);
        }

        if ($params["parentId"] > 0)
        {
            $SAVEDATA['PARENT'] = addText($params["parentId"]);
        }
        else
        {
            $SAVEDATA['PARENT'] = 0;
        }
        self::dbAccess()->insert('t_location', $SAVEDATA);
        return array("success" => true);
    }

    public static function removeObject($params)
    {

        $removeId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findObjectFromId($removeId);

        if ($facette)
        {
            if (self::checkChild($facette->ID))
            {
                self::sqlRemoveObject($facette->PARENT);
            }
        }

        self::sqlRemoveObject($removeId);
        return array("success" => true);
    }

    public static function sqlRemoveObject($Id)
    {
        self::dbAccess()->delete("t_location", " ID ='" . $Id . "'");
    }

    public static function updateObject($params)
    {
        self::dbAccess()->update("t_location", array('NAME' => "'". addText($params["NAME"]) ."'"), "ID='". addText($params["objectId"]) ."'");
        return array("success" => true);
    }

    public static function jsonTreeAllLocation($params)
    {

        $data = array();
        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        $SQL = self::dbAccess()->select();
        $SQL->from("t_location", array("*"));
        if ($node)
        {
            $SQL->where("PARENT = '" . $node . "'");
        }
        else
        {
            $SQL->where("PARENT = 0");
        }

        $SQL->order("NAME");
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
        {
            foreach ($result as $key => $value)
            {

                $data[$key]['id'] = "" . $value->ID . "";
                $data[$key]['text'] = setShowText($value->NAME);
                $data[$key]['parentId'] = $value->PARENT;

                if ($value->PARENT == 0)
                {
                    $data[$key]['leaf'] = false;
                    $data[$key]['disabled'] = false;
                    $data[$key]['cls'] = "nodeTextBold";
                    $data[$key]['iconCls'] = "icon-folder_magnify";
                }
                else
                {
                    if (self::checkChild($value->ID))
                    {
                        $data[$key]['leaf'] = false;
                        $data[$key]['disabled'] = false;
                        $data[$key]['cls'] = "nodeTextBold";
                        $data[$key]['iconCls'] = "icon-folder_magnify";
                    }
                    else
                    {
                        $facette = self::findObjectFromId($value->PARENT);
                        $data[$key]['leaf'] = true;
                        $data[$key]['cls'] = "nodeText";
                        $data[$key]['iconCls'] = "icon-application_form_magnify";
                    }
                }
            }
        }

        return $data;
    }

    public static function jsonAllLocation($parentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_location", array("*"));
        if ($parentId)
        {
            $SQL->where("PARENT = ?",$parentId);
        }
        else
        {
            $SQL->where("PARENT = 0");
        }

        $SQL->order("NAME");
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
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

    public static function comboxLocation()
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_location", array("*"));
        $SQL->where("PARENT = 0");
        $SQL->order("NAME");
        $result = self::dbAccess()->fetchAll($SQL);
        $json = "[";
        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $json .= $i ? "," : "";
                $json .= "{chooseValue: '" . $value->ID . "', chooseDisplay: '" . setShowText($value->NAME) . "'}";
                $i++;
            }
        }

        $json .= "]";

        return $json;
    }

    public static function checkChild($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_location", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>