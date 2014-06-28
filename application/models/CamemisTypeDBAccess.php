<?php

//////////////////////////////////////////////////////////////////////////
//@Sea Peng
//Date: 22.11.2013
//////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CamemisTypeDBAccess {

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

    public static function findObjectFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('t_camemis_type'));
        $SQL->where("ID = ?", $Id);
        //error_log($SQL);

        return self::dbAccess()->fetchRow($SQL);
    }

    //@Visal
    public static function findObjectType($objectType, $parentId) {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('t_camemis_type'));
        $SQL->where("OBJECT_TYPE = ?", $objectType);
        if($parentId)
            $SQL->where("PARENT =?", $parentId);

        //error_log($SQL); 

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonPunishment($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : "";

        $data = array();
        $i = 0;
        $result = self::findObjectType($objectType,$objectId);
        $data[$i]["ID"] = "0";
        $data[$i]["NAME"] = "[---]";

        if ($result) {
            foreach ($result as $value) {                
                $data[$i + 1]["ID"] = $value->ID;
                $data[$i + 1]["NAME"] = $value->NAME;
                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function getCamemisTypeComboData($objectType, $isParent = false) {

        $data = array();
        $result = self::getCamemisType($objectType, $isParent);

        $data[0] = "[\"0\",\"[---]\"]";
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";
                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public static function getCamemisType($objectType, $isParent = false) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('t_camemis_type'));
        if ($isParent) {
            $SQL->where("PARENT=0");
        } else {
            $SQL->where("PARENT<>0");
        }
        if ($objectType)
            $SQL->where("OBJECT_TYPE='" . $objectType . "'");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getObjectDataFromId($Id) {

        $result = self::findObjectFromId($Id);

        $data = array();
        if ($result) {
            $data["ID"] = $result->ID;
            $data["NAME"] = setShowText($result->NAME);
            if ($result->DESCRIPTION) {
                $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            } else {
                $data["DESCRIPTION"] = "---";
            }

            $CHECK_DATA = explode(",", $result->SHARED_ID);
            if ($CHECK_DATA) {
                foreach ($CHECK_DATA as $check) {
                    $data[$check] = 1;
                }
            }
        }

        return $data;
    }

    public static function loadCamemisType($Id) {

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

    public static function getAllCamemisTypeQuery($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : 0;
        $readonly = isset($params["readOnly"]) ? addText($params["readOnly"]) : ''; //@veasna

        $facette = self::findObjectFromId($objectId);
        $objectType = isset($params["objectType"]) ? addText($params["objectType"]) : 0;

        $SQL = self::dbAccess()->select();
        $SQL->from(array('t_camemis_type'));

        if ($parentId) {
            $SQL->where("PARENT = ?", $parentId);
        } else {

            $SQL->where("PARENT=0");
        }

        if ($facette) {
            $SQL->where("OBJECT_TYPE='" . $facette->OBJECT_TYPE . "'");
        } else {
            if ($objectType)
                $SQL->where("OBJECT_TYPE='" . $objectType . "'");
        }
        if ($readonly) {//@veasna
            $SQL->where("READONLY='" . $readonly . "'");
        }

        $SQL .= " ORDER BY NAME";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkChild($Id) {
        if ($Id) {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_camemis_type", array("C" => "COUNT(*)"));
            $SQL->where("PARENT = " . $Id . "");
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            return $result ? $result->C : 0;
        } else {
            return 0;
        }
    }

    ///@veasna

    public static function jsonTreeCatagories($params) {

        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";
        if ($params["node"] == "0") {
            $params["parentId"] = $params["objectId"];
        }
        $result = self::getAllCamemisTypeQuery($params);
        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = stripslashes($value->NAME);
                $data[$i]['type'] = setShowText($value->OBJECT_TYPE);
                if (self::checkChild($value->ID)) {
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "forum-parent";
                    $data[$i]['cls'] = "forum-ct";
                } else {
                    $data[$i]['leaf'] = true;
                    $data[$i]['iconCls'] = "icon-forum";
                    $data[$i]['cls'] = "forum";
                }
                $i++;
            }
        }

        return $data;
    }

    //

    public static function jsonTreeAllCamemisType($params) {
        $parentId = isset($params["node"]) ? addText($params["node"]) : 0;
        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";
        $result = self::getAllCamemisTypeQuery($params);
        $data = array();
        $i = 0;

        if ($result) {
            foreach ($result as $value) {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = stripslashes($value->NAME);
                $data[$i]['type'] = setShowText($value->OBJECT_TYPE);

                if (!self::checkChild($value->ID)) {
                    if ($value->IS_PARENT) {
                        $data[$i]['leaf'] = false;
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        $data[$i]['cls'] = "nodeTextBold";
                    } else {
                        $data[$i]['leaf'] = true;
                        $data[$i]['isParent'] = $value->IS_PARENT;
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        $data[$i]['cls'] = "nodeTextBlue";
                    }
                } else {
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                }

                if (!$parentId) {
                    $data[$i]['leaf'] = false;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                }

                $i++;
            }
        }

        return $data;
    }

    public static function jsonSaveCamemisType($params) {
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : 0;


        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if ($objectId == "new") {
            $SAVEDATA['PARENT'] = $parentId;
            $parentObject = self::findObjectFromId($parentId);

            if ($parentObject) {

                switch ($parentObject->OBJECT_TYPE) {
                    case "FORUM_ALUMNI":
                    case "FORUM_ELEARNING":
                    case "MAJOR_TYPE":
                        if ($parentObject->READONLY) {
                            $SAVEDATA['IS_PARENT'] = $parentObject->IS_PARENT;
                        } else {
                            $SAVEDATA['IS_PARENT'] = 0;
                        }
                        $SAVEDATA['OBJECT_TYPE'] = $parentObject->OBJECT_TYPE;
                        break;
                    case "DISCIPLINE_TYPE_STUDENT":
                        if ($parentObject->IS_PARENT) {
                            if($parentObject->PARENT){
                                $SAVEDATA['OBJECT_TYPE'] = "PUNISHMENT_TYPE_STUDENT";
                                $SAVEDATA['IS_PARENT'] = 0;
                            }else{
                                $SAVEDATA['OBJECT_TYPE'] = "DISCIPLINE_TYPE_STUDENT";
                                $SAVEDATA['IS_PARENT'] = $parentObject->IS_PARENT;
                            }
                            
                        }
                        break;
                    case "DISCIPLINE_TYPE_STAFF":
                        if ($parentObject->IS_PARENT) {
                            $SAVEDATA['OBJECT_TYPE'] = "PUNISHMENT_TYPE_STAFF";
                            $SAVEDATA['IS_PARENT'] = 0;
                        }
                        break;
                    default:
                        $SAVEDATA['IS_PARENT'] = $parentObject->IS_PARENT;
                        $SAVEDATA['OBJECT_TYPE'] = $parentObject->OBJECT_TYPE;
                        break;
                }
            }
            self::dbAccess()->insert('t_camemis_type', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_camemis_type', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public static function jsonRemoveCamemisType($Id) {

        self::dbAccess()->delete('t_camemis_type', array("ID='" . $Id . "'"));
        return array("success" => true);
    }

    public static function comboxCamemisType($objectType, $isParent = 0) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('t_camemis_type'));
        $SQL->where("PARENT<>0");
        $SQL->where("OBJECT_TYPE='" . $objectType . "'");
        $SQL->where("IS_PARENT='" . $isParent . "'");

        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $json = "[";
        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $json .= $i ? "," : "";
                $json .= "{chooseValue: '" . $value->ID . "', chooseDisplay: '" . addslashes(setShowText($value->NAME)) . "'}";
                $i++;
            }
        }

        $json .= "]";

        return $json;
    }

}

?>
