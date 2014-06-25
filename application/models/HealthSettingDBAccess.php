<?

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 16.02.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once setUserLoacalization();

Class HealthSettingDBAccess {

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

    public static function jsonSaveHealthSetting($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : 0;
        $type = isset($params["FIELD_TYPE"]) ? addText($params["FIELD_TYPE"]) : '';

        $parentObject = self::findHealthSettingFromId($parentId);

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["NAME_EN"]))
            $SAVEDATA['NAME_EN'] = addText($params["NAME_EN"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA['SORTKEY'] = addText($params["SORTKEY"]);

        $SAVEDATA['FIELD_IS_REQUIRED'] = isset($params["FIELD_IS_REQUIRED"]) ? 1 : 0;

        if ($type)
        {
            $SAVEDATA['FIELD_TYPE'] = $type;
        }

        if ($parentObject)
        {
            switch ($parentObject->OBJECT_TYPE)
            {
                case "FOLDER":
                    $SAVEDATA['OBJECT_TYPE'] = "ITEM";
                    if ($type == 3 || $type == 4 || $type == 5)
                    {
                        $SAVEDATA['OBJECT_TYPE'] = "SUBITEM";
                    }
                    else
                    {
                        if (isset($params["FIELD_TYPE"]))
                            $SAVEDATA['FIELD_TYPE'] = addText($params["FIELD_TYPE"]);
                    }

                    break;
                case "ITEM":
                    $SAVEDATA['OBJECT_TYPE'] = "SUBITEM";
                    $SAVEDATA['FIELD_TYPE'] = $parentObject->FIELD_TYPE;
                    break;
            }
        }

        if ($objectId == "new")
        {
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_health_setting', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }
        else
        {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_health_setting', $SAVEDATA, $WHERE);
            if (!$parentId)
                self::updateChildren($objectId, $type);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public static function removeSubHealthSetting($Id)
    {
        self::dbAccess()->delete('t_health_setting', array("ID=" . $Id . ""));
        return array(
            "success" => true
        );
    }

    public static function updateChildren($parentId, $type)
    {
        $SAVEDATA = array();
        if ($parentId and $type)
        {
            $SAVEDATA['FIELD_TYPE'] = $type;
            $WHERE = self::dbAccess()->quoteInto("PARENT = ?", $parentId);
            self::dbAccess()->update('t_health_setting', $SAVEDATA, $WHERE);
        }
    }

    public static function findHealthSettingFromId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        $SQL->where("ID = ?", $Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonLoadHealthSetting($Id)
    {

        $facette = self::findHealthSettingFromId($Id);

        $data = Array();
        if ($facette)
        {
            $data["ID"] = $facette->ID;
            $data["FIELD_TYPE"] = $facette->FIELD_TYPE;
            $data["DESCRIPTION"] = $facette->DESCRIPTION;
            $data["SORTKEY"] = $facette->SORTKEY;
            $data["NAME"] = setShowText($facette->NAME);
            $data["NAME_EN"] = setShowText($facette->NAME_EN);
            $data["FIELD_IS_REQUIRED"] = $facette->FIELD_IS_REQUIRED ? 1 : 0;
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function sqlHealthSetting($node, $type = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_health_setting'), array('*'));
        if (!$node)
        {
            $SQL->where("PARENT=0");
        }
        else
        {
            $SQL->where("PARENT = ?", $node);
        }

        if ($type)
            $SQL->where("FIELD_TYPE=" . $type . "");
        $SQL->order("SORTKEY ASC");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonRemoveHealthSetting($Id)
    {

        $facette = self::findChild($Id);
        if ($facette)
        {
            foreach ($facette as $value)
            {
                self::dbAccess()->delete('t_student_health_setting', array("ITEM=" . $value->ID . ""));
            }
        }

        self::dbAccess()->delete('t_health_setting', array("ID=" . $Id . " or PARENT=" . $Id . ""));

        return array("success" => true);
    }

    public static function findChild($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        $SQL->where("PARENT = ?", $Id);
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function checkChild($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?", $Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function jsonAllTreeHealthSetting($params)
    {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $data = array();
        $result = self::sqlHealthSetting($node);

        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {

                $data[$i]['id'] = $value->ID;
                $data[$i]['objectType'] = $value->OBJECT_TYPE;

                $name = UserAuth::isDefaultSystemLanguage() ? $value->NAME_EN : $value->NAME;

                switch ($value->OBJECT_TYPE)
                {
                    case "FOLDER":
                        $data[$i]['text'] = setShowText($name);
                        $data[$i]['leaf'] = false;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeTextBold";
                        $data[$i]['iconCls'] = "icon-brick_magnify";
                        break;
                    case "ITEM":
                        switch ($value->FIELD_TYPE)
                        {
                            case 1:
                                $data[$i]['text'] = setShowText($name) . " (Checkbox)";
                                break;
                            case 2:
                                $data[$i]['text'] = setShowText($name) . " (Radiobox)";
                                break;
                            case 3:
                                $data[$i]['text'] = setShowText($name) . " (Inputfield)";
                                break;
                            case 4:
                                $data[$i]['text'] = setShowText($name) . " (Textarea)";
                                break;
                            case 5:
                                $data[$i]['text'] = setShowText($name) . " (Date)";
                                break;
                        }
                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeText";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        break;
                    case "SUBITEM":
                        $data[$i]['text'] = setShowText($name);
                        $data[$i]['leaf'] = true;
                        $data[$i]['disabled'] = false;
                        $data[$i]['cls'] = "nodeText";
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                        break;
                }
                $i++;
            }
        }

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////

    protected static function renderFormItems($Id, $width = 250)
    {
        $data = array();
        $entries = self::sqlHealthSetting($Id, false);
        if ($entries)
        {
            foreach ($entries as $value)
            {
                $name = (UserAuth::systemLanguage() == "ENGLISH") ? $value->NAME_EN : $value->NAME;
                switch ($value->FIELD_TYPE)
                {
                    case 1:
                        $data[] = "{boxLabel: '" . setShowText($name) . "', name:'CHECKBOX_" . $value->ID . "', inputValue: '" . $value->ID . "'}";
                        break;
                    case 2:
                        $data[] = "{boxLabel: '" . setShowText($name) . "', name:'RADIOBOX_" . $Id . "', inputValue: '" . $value->ID . "'}";
                        break;
                    case 3:
                        $data[] = "{xtype: 'textfield',id: 'INPUTFIELD_" . $value->ID . "'" . ",fieldLabel: '" . setShowText($name) . "',width:" . $width . ",name: 'INPUTFIELD_" . $value->ID . "'}";
                        break;
                    case 4:
                        $data[] = "{xtype: 'textarea',id: 'TEXTAREA_" . $value->ID . "'" . ",fieldLabel: '" . setShowText($name) . "',width:" . $width . ",name: 'TEXTAREA_" . $value->ID . "'}";
                        break;
                    case 5:
                        $data[] = "{xtype: 'datefield',id: 'DATEFIELD_" . $value->ID . "'" . ",fieldLabel: '" . setShowText($name) . "',width:" . $width . ",name: 'DATEFIELD_" . $value->ID . "'}";
                        break;
                }
            }
        }

        return implode(",", $data);
    }

    public static function renderHealthField($Id, $width = 500, $isAddObjectItem = false, $checkboxToggle = false)
    {

        $panelItem = "";
        $entries = self::sqlHealthSetting($Id, false);
        $textFieldWidth = ($width <= 350) ? 150 : 250; //@veasna
        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {

                $name = (UserAuth::systemLanguage() == "ENGLISH") ? $value->NAME_EN : $value->NAME;

                switch ($value->FIELD_TYPE)
                {
                    case 1:
                        $panelItem = "{
                            xtype: 'checkboxgroup'
                            ,fieldLabel: ''
                            ,columns:1
                            ,border: false
                            ,autoHeight:true  
                            ,items:[" . self::renderFormItems($value->ID, $textFieldWidth) . "]
                        }
                        ";
                        break;
                    case 2:
                        $panelItem = "{
                            xtype: 'radiogroup'
                            ,fieldLabel: ''
                            ,columns:1
                            ,border: false
                            ,autoHeight:true  
                            ,items:[" . self::renderFormItems($value->ID, $textFieldWidth) . "]
                        }
                        ";
                        break;
                    case 3:
                    case 4:
                    case 5:
                        $panelItem = "{
                            layout: 'form'
                            ,border: false
                            ,autoHeight:true         
                            ,bodyStyle: 'padding:15px'
                            ,items:[" . self::renderFormItems($value->ID, $textFieldWidth) . "]
                        }
                        ";
                        break;
                }

                $html = "";
                if (!$isAddObjectItem)
                    $html .= "{";
                $html .= "title: '" . setShowText($name) . "'";
                $html .= ",id: 'FIELDSET_" . $value->ID . "'";
                $html .= ",xtype:'fieldset'";
                if ($checkboxToggle)
                {
                    $html .= ",checkboxToggle: true";
                }
                else
                {
                    $html .= ",collapsible: true ";
                }
                $html .= ",collapsed: false";
                $html .= ",autoHeight: true";
                $html .= ",style: 'padding-bottom: 5px'";
                $html .= ",width:" . $width . "";
                $html .= ",items:[" . $panelItem . "]";
                if (!$isAddObjectItem)
                    $html .= "}";

                $data[] = $html;
            }
        }

        return ($isAddObjectItem) ? $data : implode(",", $data);
    }

    public static function getHealthComboItems($objectIndex)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        $SQL->where("OBJECT_INDEX = ?", $objectIndex);
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        $data[0] = "['0','[---]']";

        if ($result)
        {
            $i = 0;
            foreach ($result as $value)
            {
                $data[$i + 1] = "['" . $value->ID . "','" . UserAuth::isDefaultSystemLanguage() ? $value->NAME_EN : $value->NAME . "']";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

}

?>