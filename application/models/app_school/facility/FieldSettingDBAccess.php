<?

//////////////////////////////////////////////////////////////
//@Sea Peng 01.10.2013
//////////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

Class FieldSettingDBAccess {

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

    public static function jsonSaveFieldSetting($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : 0;
        $type = isset($params["CHOOSE_TYPE"]) ? addText($params["CHOOSE_TYPE"]) : '';

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA['SORTKEY'] =  addText($params["SORTKEY"]);

        if ($type)
            $SAVEDATA['CHOOSE_TYPE'] = $type;

        if ($parentId) {
            $facette = self::findFieldSettingFromId($parentId);
            if ($facette)
                $SAVEDATA['CHOOSE_TYPE'] = $facette->CHOOSE_TYPE;
            $SAVEDATA['OBJECT_TYPE'] = "ITEM";
        }else {
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";
        }

        if ($objectId == "new") {
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_facility_description', $SAVEDATA);

            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_facility_description', $SAVEDATA, $WHERE);

            if (!$parentId)
                self::updateChildren($objectId, $type);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public static function updateChildren($parentId, $type) {
        $SAVEDATA = array();
        if ($parentId and $type) {
            $SAVEDATA['CHOOSE_TYPE'] = $type;
            $WHERE = self::dbAccess()->quoteInto("PARENT = ?", $parentId);
            self::dbAccess()->update('t_facility_description', $SAVEDATA, $WHERE);
        }
    }

    public static function findFieldSettingFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_description", array('*'));
        $SQL->where("ID = ?",$Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadFieldSetting($Id) {

        $facette = self::findFieldSettingFromId($Id);

        $data = Array();
        if ($facette) {
            $data["ID"] = $facette->ID;
            $data["CHOOSE_TYPE"] = $facette->CHOOSE_TYPE;
            $data["NAME"] = setShowText($facette->NAME);
            $data["SORTKEY"] = $facette->SORTKEY;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function sqlFieldSetting($node, $type = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_facility_description'), array('*'));

        if (is_numeric($node)) {
            if ($node == 0) {
                $SQL->where("PARENT=0");
            } else {
                $SQL->where("PARENT = ?",$node);
            }
        }

        if ($type)
            $SQL->where("CHOOSE_TYPE=" . $type . "");
        $SQL->order("SORTKEY ASC");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonRemoveFieldSetting($Id) {

        self::dbAccess()->delete('t_facility_description', array("ID=" . $Id . " OR PARENT=" . $Id . ""));
        return array("success" => true);
    }

    public static function findChild($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_description", array('*'));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_facility_description", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getFieldSettingByCategory($id) {

        $facette = FacilityDBAccess::findFacilityType($id);
        $result = array();

        if ($facette) {
            if ($facette->FIELD_SETTING) {
                $result = explode(",", $facette->FIELD_SETTING);
            }
        }

        return $result;
    }

    public static function jsonAllTreeFieldSetting($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $aliasId = isset($params["aliasId"]) ? $params["aliasId"] : "";

        $data = array();
        $result = self::sqlFieldSetting($node);

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                $data[$i]['text'] = $value->NAME;
                $data[$i]['id'] = $value->ID;

                switch ($value->OBJECT_TYPE) {
                    case "FOLDER":
                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-folder_magnify";

                        if ($aliasId) {
                            $data[$i]['iconCls'] = "icon-shape_square_link";
                            $data[$i]['checked'] = $data[$i]["checked"] = in_array($value->ID, self::getFieldSettingByCategory($aliasId)) ? true : false;
                        } else {
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                        }

                        break;
                    case "ITEM":
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        break;
                }
                $i++;
            }
        }

        return $data;
    }

}

?>