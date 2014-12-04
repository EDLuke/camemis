<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 30.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'MyConfig.php';
require_once 'AdminUserDBAccess.php';

class AdminAppDBAccess {

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function findRightFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_school_user_right";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadAppRight($Id) {

        $facette = self::findRightFromId($Id);

        $o = array(
            "success" => true
            , "data" => array()
        );

        if ($facette) {
            if ($facette->ID) {
                $parentObject = self::findRightFromId($facette->PARENT);
                $data["PARENT_NAME"] = $facette->PARENT ? $parentObject->CONST_RIGHT : "---";
                $data["USER_RIGHT"] = $facette->USER_RIGHT;
                $data["CONST_RIGHT"] = $facette->CONST_RIGHT;
                $data["DEFAULT_ROLE"] = $facette->DEFAULT_ROLE;
                $data["OBJECT_TYPE"] = $facette->OBJECT_TYPE;
                $data["ICON"] = $facette->ICON;
                $data["CHECKED"] = $facette->CHECKED;
                $data["SORTKEY"] = $facette->SORTKEY;

                $o = array(
                    "success" => true
                    , "data" => $data
                );
            }
        }
        return $o;
    }

    public static function jsonSaveAppRight($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : '';
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : '';

        $SAVEDATA['USER_RIGHT'] = addText($params["USER_RIGHT"]);
        $SAVEDATA['CONST_RIGHT'] = addText($params["CONST_RIGHT"]);
        $SAVEDATA['OBJECT_TYPE'] = addText($params["OBJECT_TYPE"]);
        $SAVEDATA['ICON'] = addText($params["ICON"]);

        $SAVEDATA['CHECKED'] = $params["CHECKED"];
        $SAVEDATA['SORTKEY'] =  addText($params["SORTKEY"]);

        if ($objectId == "new") {
            $parentObject = self::findRightFromId($parentId);
            $SAVEDATA['DEFAULT_ROLE'] = $parentObject->DEFAULT_ROLE;
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_school_user_right', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE = self::dbAccess()->quoteInto('ID =?', $objectId);
            self::dbAccess()->update('t_school_user_right', $SAVEDATA, $WHERE);
        }

        return array("success" => true, 'objectId' => $objectId);
    }

    public static function jsonTreeAllAppRights($params) {

        $data = array();
        $defaultRole = isset($params["defaultRole"]) ? $params["defaultRole"] : 1;
        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        $SQL = "SELECT * FROM t_school_user_right";
        $SQL .= " WHERE 1=1";
        if (!$node)
            $SQL .= " AND PARENT=0";
        else
            $SQL .= " AND PARENT='" . $node . "'";
        $SQL .= " AND DEFAULT_ROLE='" . $defaultRole . "'";
        $SQL .= " ORDER BY SORTKEY ASC";
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {

            $i = 0;
            foreach ($result as $value) {

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['right'] = "" . $value->USER_RIGHT . "";
                $data[$i]['text'] = $value->CONST_RIGHT;
                $data[$i]['parentId'] = $value->PARENT;
                
                if ($value->CHECKED) {
                    $data[$i]['checked'] = false;
                }

                switch ($value->OBJECT_TYPE) {
                    case "FOLDER":

                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";

                        if ($value->ICON) {
                            $data[$i]['iconCls'] = $value->ICON;
                        } else {
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                        }
                        $data[$i]['objectType'] = "FOLDER";

                        break;
                    case "ITEM":

                        $data[$i]['leaf'] = true;
                        $data[$i]['objectType'] = "ITEM";
                        
                        if ($value->ICON) {
                            $data[$i]['cls'] = "nodeTextBold";
                            $data[$i]['iconCls'] = $value->ICON;
                        } else {
                            switch ($value->USER_RIGHT) {
                                case "READ_RIGHT":
                                    $data[$i]['cls'] = "nodeTextBlue";
                                    $data[$i]['iconCls'] = "icon-bullet_square_grey";
                                    break;
                                case "EDIT_RIGHT":
                                    $data[$i]['cls'] = "nodeTextBlue";
                                    $data[$i]['iconCls'] = "icon-bullet_square_green";
                                    break;
                                case "REMOVE_RIGHT":
                                    $data[$i]['cls'] = "nodeTextBlue";
                                    $data[$i]['iconCls'] = "icon-bullet_square_red";
                                    break;
                                case "EXECUTE_RIGHT":
                                    $data[$i]['cls'] = "nodeTextBlue";
                                    $data[$i]['iconCls'] = "icon-bullet_square_yellow";
                                    break;
                            }
                        }
                        break;
                }
                $i++;
            }
        }

        return $data;
    }

    public static function jsonDeleteAppRight($Id) {
        self::dbAccess()->delete('t_school_user_right', array("ID='" . $Id . "'"));
        return array("success" => true);
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_school_user_right", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>