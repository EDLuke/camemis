<?php

require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 16.02.2014
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class Utiles {

    public $data = array();

    public function __construct()
    {
        
    }

    static function getInstance()
    {

        static $me;

        if ($me == null)
        {
            $me = new Utiles();
        }

        return $me;
    }

    public static function urlEncryp()
    {
        return new URLEncryption();
    }

    public static function setUserPublicRoot()
    {
        $user = "";
        $explode = explode(".", $_SERVER['SERVER_NAME']);
        if (is_array($explode))
        {
            $user = $explode[0];
        }

        $CHECK_FOLDER_ATTACHMENT = $_SERVER['DOCUMENT_ROOT'] . "/public/users/" . $user . "/attachment/";
        if (!is_dir($CHECK_FOLDER_ATTACHMENT))
        {
            mkdir($CHECK_FOLDER_ATTACHMENT, 0777, true); // true for recursive create
        }
        $CHECK_FOLDER_DATABASE = $_SERVER['DOCUMENT_ROOT'] . "/public/users/" . $user . "/database/";
        if (!is_dir($CHECK_FOLDER_DATABASE))
        {
            mkdir($CHECK_FOLDER_DATABASE, 0777, true); // true for recursive create
        }
    }

    public function processHTML($html)
    {

        return preg_replace("/'/", "/\'/", $html);
    }

    static function comboData($objectArray)
    {
        $data = Array();

        if ($objectArray)
            foreach ($objectArray as $value)
            {
                $data[] = "[\"$value->ID\",\"$value->NAME\"]";
            }
        return "[" . implode(",", $data) . "]";
    }

    public function getValueRegistry($index)
    {
        $registry = Zend_Registry::getInstance();
        return isset($registry[$index]) ? $registry[$index] : 'camemis';
    }

    function xml2phpArray($xml, $arr, $dimension = 0)
    {
        $iter = 0;
        foreach ($xml->children() as $b)
        {
            $a = $b->getName();
            if (!$b->children())
            {
                $arr[$a] = trim($b[0]);
            }
            else
            {

                $_iter = $iter - $dimension;
                $arr[$a][$_iter] = array();
                $arr[$a][$_iter] = $this->xml2phpArray($b, $arr[$a][$_iter], $dimension);
            }

            $iter++;
        }
        return $arr;
    }

    static function removeStatus($data)
    {

        $status = isset($data["STATUS"]) ? $data["STATUS"] : 0;
        $remove_status = isset($data["REMOVE_STATUS"]) ? $data["REMOVE_STATUS"] : 0;

        if ($status == 1)
        {
            $status = false;
        }
        else
        {
            if ($remove_status > 0)
            {
                $status = false;
            }
            else
            {
                $status = true;
            }
        }

        return $status;
    }

    public static function createUrl($url, $params = array())
    {
        $urlEncryp = new URLEncryption();
        return "/" . $url . "/?camIds=" . $urlEncryp->encryptedGet(http_build_query($params));
    }

    public function buildURL($script, $params = array())
    {

        $ret = $script;
        $ret.= "?goId=" . camemisId() . "&";
        if ($params)
            $ret.= http_build_query($params);
        $ret .= "&";

        return "" . Zend_Registry::get('CAMEMIS_URL') . "/" . $ret;
    }

    public function remotedURL($script, $parms = array())
    {

        $ret = $script;

        if (is_array($parms))
        {

            while (list($key, $value) = each($parms))
            {
                if (!empty($value))
                {
                    if (is_array($value))
                    {
                        while (list($key1, $value1) = each($value))
                        {
                            $ret .= $key . "[" . $key1 . "]=" . $value1 . "&";
                        }
                    }
                    else
                    {
                        $ret .= "" . $key . "=" . $value . "&";
                    }
                }
            }
        }

        return "" . Zend_Registry::get('CAMEMIS_URL') . "/" . $ret;
    }

    public static function to_constant($data = array())
    {
        $headers = $data;
        for ($i = 0; $i < count($headers); $i++)
        {
            $headers[$i] = trim($headers[$i]);
            if (defined("" . $headers[$i] . ""))
            {
                $headers[$i] = "'" . constant($headers[$i]) . "'";
            }
            else
            {
                $headers[$i] = '?';
            }
        }
        return $headers;
    }

    public static function setPostDecrypteParams($postParams)
    {
        if (isset($postParams["camIds"]))
        {
            $urlEncryp = new URLEncryption();
            $urlEncryp->parseEncryptedGET($postParams["camIds"]);
            return array_merge($postParams, $_GET);
        }
        else
        {
            return $postParams;
        }
    }

    public static function isLocalServer()
    {
        return ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") ? true : false;
    }

    public static function setRegistry($index, $value)
    {

        $registry = Zend_Registry::getInstance();
        if (Zend_Registry::isRegistered($index))
        {
            $registry->offsetUnset($index);
        }
        $registry->set($index, $value);
    }

    public static function getRegistry($index)
    {
        $result = "";
        if (Zend_Registry::isRegistered($index))
        {
            $result = Zend_Registry::get($index);
        }
        return $result;
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    ////////////////////////////////////////////////////////////////////////////
    //STUDENT ACADEMIC ADDITONAL INFORMATIONS
    ////////////////////////////////////////////////////////////////////////////
    protected static function studentAcademicInformationSetItems($fieldObject)
    {

        $data = Array();
        switch ($fieldObject->CHOOSE_TYPE)
        {
            case 1:
                $entries = AcademicAdditionalDBAccess::sqlAcademicAdditional($fieldObject->ID, 1);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{boxLabel: '" . setShowTextExtjs($value->NAME) . "', name:'CHECKBOX_" . $value->ID . "', inputValue: '" . $value->ID . "'}";
                    }
                }
                break;
            case 2:
                $entries = AcademicAdditionalDBAccess::sqlAcademicAdditional($fieldObject->ID, 2);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{boxLabel: '" . setShowTextExtjs($value->NAME) . "', name:'RADIOBOX_" . $fieldObject->ID . "', inputValue: '" . $value->ID . "'}";
                    }
                }
                break;
            case 3:
                $entries = AcademicAdditionalDBAccess::sqlAcademicAdditional($fieldObject->ID, 3);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{xtype: 'textfield',id: 'INPUTFIELD_" . $value->ID . "'" . ",fieldLabel: '" . stripcslashes($value->NAME) . "',width:250,name: 'INPUTFIELD_" . $value->ID . "'}";
                    }
                }
                break;
            case 4:
                $entries = AcademicAdditionalDBAccess::sqlAcademicAdditional($fieldObject->ID, 4);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{xtype: 'textarea',id: 'TEXTAREA_" . $value->ID . "'" . ",fieldLabel: '" . stripcslashes($value->NAME) . "',width:250,height:100,name: 'TEXTAREA_" . $value->ID . "'}";
                    }
                }
                break;
        }
        return implode(",", $data);
    }

    public static function studentAcademicDisplayFields($student, $classId, $target = 'traditional', $width = 550)
    {

        $FIELD_PANEL_ITEMS = Array();
        $entries = AcademicAdditionalDBAccess::sqlAcademicAdditionalByStudent($student, $classId, $target);
        if ($entries)
        {
            foreach ($entries as $value)
            {

                if (AcademicAdditionalDBAccess::checkChild($value->ID))
                {
                    $fieldObject = AcademicAdditionalDBAccess::findAcademicAdditionalFromId($value->ID);
                    if ($fieldObject)
                    {

                        $ITEMS = "";
                        $ITEMS .= "{";
                        $ITEMS .= "title: '" . setShowTextExtjs($fieldObject->NAME) . "'";
                        $ITEMS .= ",autoHeight: true";
                        $ITEMS .= ",collapsible: true";
                        $ITEMS .= ",collapsed: false";
                        $ITEMS .= ",bodyStyle: 'padding:10px'";
                        $ITEMS .= ",style: 'padding-bottom: 5px'";
                        $ITEMS .= ",width: " . $width . "";

                        switch ($fieldObject->CHOOSE_TYPE)
                        {
                            case 1:
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "xtype: 'checkboxgroup'";
                                $ITEMS .= ",fieldLabel: ''";
                                $ITEMS .= ",hideLabel: true";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",columns:2";
                                $ITEMS .= ",itemCls: 'x-check-group-alt'";
                                $ITEMS .= ",items:[" . self::studentAcademicInformationSetItems($fieldObject) . "]";
                                $ITEMS .= "}]";
                                break;
                            case 2:
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "xtype: 'radiogroup'";
                                $ITEMS .= ",fieldLabel: ''";
                                $ITEMS .= ",hideLabel: true";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",columns:2";
                                $ITEMS .= ",itemCls: 'x-check-group-alt'";
                                $ITEMS .= ",items:[" . self::studentAcademicInformationSetItems($fieldObject) . "]";
                                $ITEMS .= "}]";
                                break;
                            case 3:
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "layout: 'form'";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",bodyStyle: 'padding:10px'";
                                $ITEMS .= ",items:[" . self::studentAcademicInformationSetItems($fieldObject) . "]";
                                $ITEMS .= "}]";
                                break;
                            case 4:
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "layout: 'form'";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",bodyStyle: 'padding:10px'";
                                $ITEMS .= ",items:[" . self::studentAcademicInformationSetItems($fieldObject) . "]";
                                $ITEMS .= "}]";
                                break;
                        }

                        $ITEMS .= "}";

                        $FIELD_PANEL_ITEMS[] = $ITEMS;
                    }
                }
            }
        }

        return implode(",", $FIELD_PANEL_ITEMS);
    }

    ////////////////////////////////////////////////////////////////////////////
    //STUDENT PERSONAL DESCRIPTION
    ////////////////////////////////////////////////////////////////////////////
    protected static function personalDescriptionSetItems($fieldObject, $type, $width = 550)
    {

        if ($width == 550)
        {
            $fieldWidth = 250;
        }
        else
        {
            $fieldWidth = 120;
        }

        $data = Array();
        switch ($fieldObject->CHOOSE_TYPE)
        {
            case 1:
                $entries = DescriptionDBAccess::sqlDescription($fieldObject->ID, $type, 1);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{boxLabel: '" . setShowTextExtjs(setShowText($value->NAME)) . "', name:'CHECKBOX_" . $value->ID . "', inputValue: '" . $value->ID . "'}";
                    }
                }
                break;
            case 2:
                $entries = DescriptionDBAccess::sqlDescription($fieldObject->ID, $type, 2);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{boxLabel: '" . setShowTextExtjs(setShowText($value->NAME)) . "', name:'RADIOBOX_" . $fieldObject->ID . "', inputValue: '" . $value->ID . "'}";
                    }
                }
                break;
            case 3:
                $entries = DescriptionDBAccess::sqlDescription($fieldObject->ID, $type, 3);
                if ($entries)
                {
                    foreach ($entries as $value)
                    {
                        $data[] = "{xtype: 'textfield',id: 'INPUTFIELD_" . $value->ID . "'" . ",fieldLabel: '" . setShowTextExtjs(setShowText($value->NAME)) . "',width:" . $fieldWidth . ",name: 'INPUTFIELD_" . $value->ID . "'}";
                    }
                }
                break;
        }
        return implode(",", $data);
    }

    public static function personalDescriptionDisplayFields($type, $width = 550)
    {

        $FIELD_PANEL_ITEMS = Array();
        $entries = DescriptionDBAccess::sqlDescription(false, $type, false);
        if ($entries)
        {
            foreach ($entries as $value)
            {

                if (DescriptionDBAccess::checkChild($value->ID))
                {
                    $fieldObject = DescriptionDBAccess::findObjectFromId($value->ID);
                    if ($fieldObject)
                    {

                        $ITEMS = "";
                        $ITEMS .= "{";
                        $ITEMS .= "xtype:'fieldset'";
                        $ITEMS .= ",checkboxToggle:true";
                        $ITEMS .= ",collapsed: false";
                        $ITEMS .= ",title: '" . setShowTextExtjs($fieldObject->NAME) . "'";
                        $ITEMS .= ",bodyStyle: 'padding:10px;background:" . CamemisPage::userFormBgColor() . ";'";
                        $ITEMS .= ",style: 'padding-bottom: 5px'";
                        $ITEMS .= ",width: " . $width . "";

                        switch ($fieldObject->CHOOSE_TYPE)
                        {
                            case 1:
                                $entries = DescriptionDBAccess::sqlDescription($fieldObject->ID, $type, 1);
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "xtype: 'checkboxgroup'";
                                $ITEMS .= ",fieldLabel: ''";
                                $ITEMS .= ",hideLabel: true";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",columns:2";
                                $ITEMS .= ",itemCls: 'x-check-group-alt'";
                                $ITEMS .= ",items:[" . self::personalDescriptionSetItems($fieldObject, $type, $width) . "]";
                                if ($entries)
                                {
                                    foreach ($entries as $value)
                                    {
                                        $ITEMS .= ",name:'CHECKBOX_" . $value->ID . "'";
                                    }
                                }
                                $ITEMS .= "}]";
                                break;
                            case 2:
                                $entries = DescriptionDBAccess::sqlDescription($fieldObject->ID, $type, 2);
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "xtype: 'radiogroup'";
                                $ITEMS .= ",fieldLabel: ''";
                                $ITEMS .= ",hideLabel: true";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",columns:2";
                                $ITEMS .= ",itemCls: 'x-check-group-alt'";
                                $ITEMS .= ",items:[" . self::personalDescriptionSetItems($fieldObject, $type, $width) . "]";
                                if ($entries)
                                {
                                    foreach ($entries as $value)
                                    {
                                        $ITEMS .= ",name:'RADIOBOX_" . $value->ID . "'";
                                    }
                                }
                                $ITEMS .= "}]";
                                break;
                            case 3:
                                $entries = DescriptionDBAccess::sqlDescription($fieldObject->ID, $type, 3);
                                $ITEMS .= ",items:[{";
                                $ITEMS .= "layout: 'form'";
                                $ITEMS .= ",border: false";
                                $ITEMS .= ",autoHeight:true";
                                $ITEMS .= ",bodyStyle: 'padding:10px'";
                                $ITEMS .= ",items:[" . self::personalDescriptionSetItems($fieldObject, $type, $width) . "]";
                                if ($entries)
                                {
                                    foreach ($entries as $value)
                                    {
                                        $ITEMS .= ",name:'INPUTFIELD_" . $value->ID . "'";
                                    }
                                }
                                $ITEMS .= "}]";
                                break;
                        }

                        $ITEMS .= "}";

                        $FIELD_PANEL_ITEMS[] = $ITEMS;
                    }
                }
            }
        }

        return implode(",", $FIELD_PANEL_ITEMS);
    }

    ////////////////////////////////////////////////////////////////////////////
    //END OF STUDENT PERSONAL DESCRIPTION
    ////////////////////////////////////////////////////////////////////////////

    /**
     * STUDENT ACADEMIC HISTORY
     */
    protected static function listStudentSchoolyearTraditional($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear'), array());
        $SQL->joinLeft(array('B' => 't_academicdate'), 'A.SCHOOL_YEAR=B.ID', array('ID AS SCHOOLYEAR_ID', 'NAME AS SCHOOLYEAR_NAME', 'START AS YEAR_START'));
        $SQL->where("A.STUDENT='" . $Id . "'");
        $SQL->where("A.CLASS!=''");
        $SQL->order("B.START DESC");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    protected static function listStudentSchoolyearCredit($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear_subject'), array());
        $SQL->joinLeft(array('B' => 't_academicdate'), 'A.SCHOOLYEAR_ID=B.ID', array('ID AS SCHOOLYEAR_ID', 'NAME AS SCHOOLYEAR_NAME', 'START AS YEAR_START'));
        $SQL->where("A.STUDENT_ID='" . $Id . "'");
        $SQL->group("A.SCHOOLYEAR_ID");
        $SQL->order("B.START DESC");
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getStudentClassTraditional($Id, $schoolyearId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear'), array());
        $SQL->joinLeft(array('B' => 't_grade'), 'A.CLASS=B.ID', array('ID AS CLASS_ID', 'NAME AS CLASS_NAME', 'EDUCATION_SYSTEM'));
        $SQL->where("A.STUDENT='" . $Id . "'");
        $SQL->where("A.CLASS!=''");
        $SQL->where("A.SCHOOL_YEAR='" . $schoolyearId . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    //@Sea Peng
    protected static function listStudentTrainingProgram($Id)
    {
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_student_training'), array('A.TRAINING AS TRAINING_ID', 'A.PROGRAM AS PROGRAM_ID'));
        $SQL->joinLeft(array('B' => 't_training'), 'B.ID=A.TRAINING', array('B.NAME AS TRAINING'));
        $SQL->joinLeft(array('C' => 't_training'), 'C.ID=A.TERM', array('C.START_DATE AS START_DATE', 'C.END_DATE AS END_DATE'));
        $SQL->joinLeft(array('D' => 't_training'), 'D.ID=A.LEVEL', array('D.NAME AS LEVEL'));
        $SQL->joinLeft(array('E' => 't_training'), 'E.ID=A.PROGRAM', array('E.NAME AS RROGRAM'));

        $SQL->where("A.STUDENT='" . $Id . "'");
        $SQL->order("B.START_DATE DESC");

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function listStudentSchoolyear($Id)
    {
        $firstData = Array();
        if (self::listStudentSchoolyearTraditional($Id))
        {
            foreach (self::listStudentSchoolyearTraditional($Id) as $value)
            {
                $firstData[$value->YEAR_START]['ID'] = $value->SCHOOLYEAR_ID;
                $firstData[$value->YEAR_START]['NAME'] = $value->SCHOOLYEAR_NAME;
                $firstData[$value->YEAR_START]['DATE'] = $value->YEAR_START;
            }
        }

        $secondData = Array();
        if (self::listStudentSchoolyearCredit($Id))
        {
            foreach (self::listStudentSchoolyearCredit($Id) as $value)
            {
                $secondData[$value->YEAR_START]['ID'] = $value->SCHOOLYEAR_ID;
                $secondData[$value->YEAR_START]['NAME'] = $value->SCHOOLYEAR_NAME;
                $secondData[$value->YEAR_START]['DATE'] = $value->YEAR_START;
            }
        }

        $data = $firstData + $secondData;
        krsort($data);

        return $data;
    }

    //@Sea Peng
    protected static function listDataStudentTrainingProgram($Id)
    {
        $data = Array();
        $i = 0;
        if (self::listStudentTrainingProgram($Id))
        {
            foreach (self::listStudentTrainingProgram($Id) as $value)
            {
                $data[$i]['TRAINING_ID'] = $value->TRAINING_ID;
                $data[$i]['PROGRAM_ID'] = $value->PROGRAM_ID;
                $data[$i]['TRAINING'] = $value->TRAINING;
                $data[$i]['RROGRAM'] = $value->RROGRAM;
                $data[$i]['START_DATE'] = $value->START_DATE;
                $data[$i]['END_DATE'] = $value->END_DATE;

                $i++;
            }
        }
        return $data;
    }

    protected static function getStudentAcademicHistorySubTreeItems($Id, $classObject, $schoolyearId)
    {

        if ($classObject)
        {

            $className = setShowTextExtjs($classObject->CLASS_NAME);
            $classId = $classObject->CLASS_ID;

            switch (Zend_Registry::get('MODUL_API'))
            {
                case "dfe34ef0f0b812ea32d92866dbe9e3cb":
                    $classparam = "academicId=" . $classId . "";
                    break;
                case "dfe34ef0f0b812ea32d02866dbe9e3cb":
                    $classparam = "classId=" . $classId . "";
                    break;
            }

            $item = "{";
            $item .= "text: '" . SCHEDULE . "'";
            $item .= ",title: '" . SCHEDULE . " (" . $className . ")'";
            $item .= ",id: 'SCHEDULE-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/schedule/byclass/?camIds=" . self::urlEncryp()->encryptedGet("academicId=" . $classId . "&target=general") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . EVENT . "'";
            $item .= ",title: '" . EVENT . " (" . $className . ")'";
            $item .= ",id: 'EVENT-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/schoolevent/classevents/?camIds=" . self::urlEncryp()->encryptedGet("" . $classparam . "&target=general") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . SUBJECT_LIST . "'";
            $item .= ",title: '" . SUBJECT_LIST . " (" . $className . ")'";
            $item .= ",id: 'SUBJECT_LIST-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/academic/listsubject/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $classId . "&target=general") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . HOMEWORK . "'";
            $item .= ",title: '" . HOMEWORK . " (" . $className . ")'";
            $item .= ",id: 'HOMEWORK-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/homework/homeworklist/?camIds=" . self::urlEncryp()->encryptedGet("" . $classparam . "&studentId=" . $Id . "") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . ATTENDANCE_INFORMATION . "'";
            $item .= ",title: '" . ATTENDANCE_INFORMATION . " (" . $className . ")'";
            $item .= ",id: 'ATTENDANCE_INFORMATION-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/student/traditionalstudentattendance/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $Id . "&" . $classparam . "") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . DISCIPLINE_INFORMATION . "'";
            $item .= ",title: '" . DISCIPLINE_INFORMATION . " (" . $className . ")'";
            $item .= ",id: 'DISCIPLINE_INFORMATION-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/discipline/bystudent/?camIds=" . self::urlEncryp()->encryptedGet("studentId=" . $Id . "&" . $classparam . "&target=general") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . ASSESSMENT . "'";
            $item .= ",title: '" . ASSESSMENT . " (" . $className . ")'";
            $item .= ",id: 'ASSESSMENT-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/evaluation/gradeboook/?camIds=" . self::urlEncryp()->encryptedGet("" . $classparam . "&studentId=" . $Id . "") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . LIST_OF_STUDENTS . "'";
            $item .= ",title: '" . LIST_OF_STUDENTS . " (" . $className . ")'";
            $item .= ",id: 'LIST_OF_STUDENTS-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/academic/studentlist/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $classId . "") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . LIST_OF_TEACHERS . "'";
            $item .= ",title: '" . LIST_OF_TEACHERS . " (" . $className . ")'";
            $item .= ",id: 'LIST_OF_TEACHERS-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/academic/teacherclassmain/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $classId . "") . "'";
            $item .= ",isClick:true";
            $item .= "}";
            $item .= ",{";
            $item .= "text: '" . EXTRA_CLASS . "'";
            $item .= ",title: '" . EXTRA_CLASS . " (" . $className . ")'";
            $item .= ",id: 'EXTRA_CLASS-" . $classId . "'";
            $item .= ",iconCls: 'icon-shape_square'";
            $item .= ",leaf: true";
            $item .= ",cls:'nodeTextBoldBlue'";
            $item .= ",url:'/extraclass/showitem/?camIds=" . self::urlEncryp()->encryptedGet("studentId=" . $classId . "") . "'";
            $item .= ",isClick:true";
            $item .= "}";
        }
        else
        {

            $entries = StudentAcademicDBAccess::getEnrolledSubjectByStudentCreditSystem($Id, $schoolyearId);

            $data = array();
            if ($entries)
            {
                foreach ($entries as $value)
                {
                    $groupName = $value->GROUP_NAME ? setShowText($value->GROUP_NAME) : "?";
                    $strItem = "{";
                    $strItem .= "text: '" . setShowText($value->SUBJECT_NAME) . " (" . $groupName . ")'";
                    $strItem .= ",title: '" . setShowText($value->SUBJECT_NAME) . " (" . $groupName . ")'";
                    $strItem .= ",id: 'ID-" . $value->CREDIT_ACADEMIC_ID . "'";
                    $strItem .= ",iconCls: 'icon-shape_square'";
                    $strItem .= ",leaf: true";
                    $strItem .= ",cls:'nodeTextBoldBlue'";
                    $strItem .= ",url:'/student/studentsubjectcreditmain/?camIds=" . self::urlEncryp()->encryptedGet("studentId=" . $Id . " &academicId=" . $value->CREDIT_ACADEMIC_ID . "") . "'";
                    $strItem .= ",isClick:true";
                    $strItem .= "}";

                    $data[] = $strItem;
                }
            }

            $item = implode(",", $data);
        }

        return $item;
    }

    public static function getStudentAcademicHistyTreeItems($Id)
    {

        $entries = self::listStudentSchoolyear($Id);
        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $schoolyearId = isset($value['ID']) ? $value['ID'] : "";
                $classObject = self::getStudentClassTraditional($Id, $schoolyearId);
                if ($classObject)
                {
                    $displayName = isset($value['NAME']) ? $value['NAME'] . " (" . $classObject->CLASS_NAME . ")" : "?";
                    $link = "/student/studentacademictraditional/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $Id . "&academicId=" . $classObject->CLASS_ID . "&schoolyearId=" . $schoolyearId . "") . "";
                }
                else
                {
                    $academicObject = StudentAcademicDBAccess::getStudentCurrentAcademicCreditSystem($Id, $schoolyearId);
                    $displayName = isset($value['NAME']) ? $value['NAME'] : "?";
                    if ($academicObject)
                    {
                        $displayName .= " (" . $academicObject->CAMPUS_NAME . ")";
                    }
                    $link = "/student/studentcampuscreditmain/?camIds=" . self::urlEncryp()->encryptedGet("studentId=" . $Id . "&academicId=" . $academicObject->TGRADE_SCHOOLYEAR . "&schoolyearId=" . $schoolyearId . "&campusId=" . $academicObject->CAMPUS_ID . "") . "";  //@veasna modify
                }

                $item = "{";
                $item .= "text: '" . $displayName . "'";
                $item .= ",title: '" . $displayName . "'";
                $item .= ",cls:'nodeTextBold'";
                $item .= ",iconCls: 'icon-folder_magnify'";
                $item .= ",leaf: false";
                $item .= ",expanded: true";
                $item .= ",url:'" . $link . "'";
                $item .= ",isClick:true";
                $item .= ",children: [" . self::getStudentAcademicHistorySubTreeItems($Id, $classObject, $schoolyearId) . "]";
                $item .= "}";
                $data[] = $item;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    protected static function getStudentTrainingHistoryTreeSubItems($Id, $trainingId)
    {

        $item = "{";
        $item .= "text: '" . SCHEDULE . "'";
        $item .= ",id: 'SCHEDULE-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/schedule/byclass/?camIds=" . self::urlEncryp()->encryptedGet("trainingId=" . $trainingId . "&target=training") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . EVENT . "'";
        $item .= ",id: 'EVENT-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/schoolevent/classevents/?camIds=" . self::urlEncryp()->encryptedGet("trainingId=" . $trainingId . "&target=training") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . HOMEWORK . "'";
        $item .= ",id: 'HOMEWORK-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/homework/homeworklisttraining/?camIds=" . self::urlEncryp()->encryptedGet("trainingId=" . $trainingId . "&studentId=" . $Id . "") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . ATTENDANCE_INFORMATION . "'";
        $item .= ",id: 'ATTENDANCE_INFORMATION-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/student/trainingstudentattendance/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $Id . "&" . $trainingId . "") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . DISCIPLINE_INFORMATION . "'";
        $item .= ",id: 'DISCIPLINE_INFORMATION-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/discipline/byclass/?camIds=" . self::urlEncryp()->encryptedGet("target=training&studentId=" . $Id . "&trainingId=" . $trainingId . "") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . ASSESSMENT . "'";
        $item .= ",id: 'ASSESSMENT-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/training/trainingassessment/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $trainingId . "&studentId=" . $Id . "") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . LIST_OF_STUDENTS . "'";
        $item .= ",id: 'LIST_OF_STUDENTS-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/training/studentlist/?camIds=" . self::urlEncryp()->encryptedGet("target=TERM&objectId=" . $trainingId . "") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . LIST_OF_TEACHERS . "'";
        $item .= ",id: 'LIST_OF_TEACHERS-" . $trainingId . "'";
        $item .= ",iconCls: 'icon-shape_square'";
        $item .= ",leaf: true";
        $item .= ",cls:'nodeTextBoldBlue'";
        $item .= ",url:'/training/teacherlist/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $trainingId . "") . "'";
        $item .= ",isClick:true";
        $item .= "}";
        return $item;
    }

    /**
     * Student Training History....
     */
    public static function getStudentTrainingHistoryTreeItems($Id)
    {
        $SQL = "
		SELECT
		DISTINCT
		A.TRAINING AS TRAINING_ID
		, A.PROGRAM AS PROGRAM_ID
		, B.NAME AS TRAINING
		, C.START_DATE AS START_DATE
		, C.END_DATE AS END_DATE
		, D.NAME AS LEVEL
		, E.NAME AS RROGRAM
		FROM t_student_training AS A
		LEFT JOIN t_training AS B ON B.ID=A.TRAINING
		LEFT JOIN t_training AS C ON C.ID=A.TERM
		LEFT JOIN t_training AS D ON D.ID=A.LEVEL
		LEFT JOIN t_training AS E ON E.ID=A.PROGRAM
		WHERE 1=1 AND A.STUDENT='" . $Id . "'
		ORDER BY C.START_DATE DESC
		";
        $entries = self::dbAccess()->fetchAll($SQL);
        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {

                $displayNameShort = $value->RROGRAM;
                $displayNameLong = "(" . getShowDate($value->START_DATE) . "-" . getShowDate($value->END_DATE) . " " . $value->LEVEL . " " . $value->RROGRAM . ")";

                $item = "{";
                $item .= "text: '" . $displayNameShort . "'";
                $item .= ",qtip:'" . $displayNameLong . "'";
                $item .= ",cls:'nodeTextBold'";
                $item .= ",iconCls: 'icon-folder_magnify'";
                $item .= ",leaf: false";
                $item .= ",expanded: true";
                $item .= ",isClick:false";
                $item .= ",children: [" . self::getStudentTrainingHistoryTreeSubItems($Id, $value->TRAINING_ID) . "]";
                $item .= "}";
                $data[] = $item;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    public static function getDashboardItems()
    {

        $data = array();
        switch (UserAuth::getUserType())
        {
            case "SUPERADMIN":
                $data = array(
                    "USER_ONLINE_LOG" => "/chart/stackeareachart/"
                    , "STUDENT" => "/main/dashboardenrolledstudent/"
                    , "STAFF" => "/chart/staffadministration/"
                    , "STUDENT_ATTENDANCE" => "/main/dashboardstudentattendance/"
                    , "STUDENT_DISCIPLINE" => "/main/dashboardstudentdiscipline/"
                    , "STAFF_DISCIPLINE" => "/main/dashboardstaffdiscipline/"
                    , "STAFF_ATTENDANCE" => "/main/dashboardstaffattendance/"
                    , "STAFF_CONTRACT" => "/main/dashboardstaffcontract/"
                    , "FACILITY" => "/main/dashboardfacility/"
                );
                break;
            case "ADMIN":
                $data = array(
                    "USER_ONLINE_LOG" => "/chart/stackeareachart/"
                    , "STUDENT" => "/main/dashboardenrolledstudent/"
                    , "STAFF" => "/chart/staffadministration/"
                    , "STUDENT_ATTENDANCE" => "/main/dashboardstudentattendance/"
                    , "STUDENT_DISCIPLINE" => "/main/dashboardstudentdiscipline/"
                    , "STAFF_DISCIPLINE" => "/main/dashboardstaffdiscipline/"
                    , "STAFF_ATTENDANCE" => "/main/dashboardstaffattendance/"
                    , "FACILITY" => "/main/dashboardfacility/"
                );
                break;
            case "STUDENT":
                $data = array(
                    "USER_ONLINE_LOG" => "/chart/stackeareachart/"
                    , "ATTENDANCE" => "/main/dashboardstudentattendance/"
                    , "DISCIPLINE" => "/main/dashboardstudentdiscipline/"
                    , "ASSESSMENT" => "/main/dashboardstudentassessment/"
                    , "NEWS" => "/main/studentdashboardnews/"
                );
                break;
            case "TEACHER":
                $data = array(
                    "USER_ONLINE_LOG" => "/chart/stackeareachart/"
                    , "ATTENDANCE" => "/main/dashboardstudentattendance/"
                    , "ASSESSMENT" => "/main/dashboardstudentassessment/"
                    , "NEWS" => "/main/teacherdashboardnews/"
                );
                break;
            case "INSTRUCTOR":
                $data = array(
                    "USER_ONLINE_LOG" => "/chart/stackeareachart/"
                    , "STUDENT_ATTENDANCE" => "/main/dashboardstudentattendance/"
                    , "ASSESSMENT" => "/main/dashboardstudentassessment/"
                    , "NEWS" => "/main/teacherdashboardnews/"
                );
                break;
            case "GUARDIAN":
                $data = array(
                    "USER_ONLINE_LOG" => "/chart/stackeareachart/"
                );

                break;
        }

        return $data;
    }

    public static function setDashboard()
    {

        $userId = Zend_Registry::get('USER')->ID;
        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_dashboard", array("C" => "COUNT(*)"));
        $SQL->where("USER_ID = '" . $userId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        $CHECK = $result ? $result->C : 0;

        switch (UserAuth::getUserType())
        {
            case "SUPERADMIN":
                $userType = "SUPERADMIN";
                break;
            case "ADMIN":
                $userType = "ADMIN";
                break;
            case "STUDENT":
                $userType = "STUDENT";
                break;
            case "TEACHER":
                $userType = "TEACHER";
                break;
            case "INSTRUCTOR":
                $userType = "INSTRUCTOR";
                break;
            case "GUARDIAN":
                $userType = "GUARDIAN";
                break;
        }

        if (!$CHECK)
        {
            if (self::getDashboardItems())
            {
                $i = 0;
                foreach (self::getDashboardItems() as $key => $url)
                {
                    $SAVEDATA["USER_ID"] = $userId;
                    $SAVEDATA["POSITION"] = $i + 1;
                    $SAVEDATA["CONST"] = $key;
                    $SAVEDATA["URL"] = $url;
                    $SAVEDATA["USER_TYPE"] = $userType;
                    self::dbAccess()->insert('t_user_dashboard', $SAVEDATA);
                    $i++;
                }
            }
        }
    }

    public static function getUserDashboardItems()
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_dashboard", array("*"));
        $SQL->where("USER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        $SQL->order("POSITION ASC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    //END.....
    ////////////////////////////////////////////////////////////////////////////
    //
	public static function dbClean()
    {
        self::dbAccess()->delete('t_subject_teacher_class', array("TEACHER=''"));
        self::dbAccess()->delete('t_subject_teacher_class', array("CAMPUS='0'"));
        self::dbAccess()->delete('t_subject_teacher_class', array("GRADE='0'"));
        self::dbAccess()->delete('t_subject_teacher_class', array("ACADEMIC='0'"));
        self::dbAccess()->delete('t_subject_teacher_class', array("SUBJECT='0'"));
    }

    ////////////////////////////////////////////////////////////////////////////

    public static function getSelectedGridColumns($objectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_display_column", array("*"));
        $SQL->where("USER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        $SQL->where("GRID_OBJECT_ID = '" . $objectId . "'");
        //error_log($SQL->__toString());
        $facette = self::dbAccess()->fetchRow($SQL);

        $COLUMN_COKIS_DATA = Utiles::getGridColumnData($objectId);
        $DATA = array();

        if ($facette)
        {
            $COLUMN_DATA = explode(",", $facette->COLUMN_DATA);

            if ($COLUMN_DATA)
            {
                if ($COLUMN_COKIS_DATA)
                {
                    $i = 0;
                    foreach ($COLUMN_DATA as $value)
                    {
                        if (checkColHidden($i + 1, $COLUMN_COKIS_DATA) == 'false')
                        {
                            $DATA[$i + 1] = $value;
                        }

                        $i++;
                    }
                }
                else
                {
                    $i = 0;
                    foreach ($COLUMN_DATA as $value)
                    {
                        $DATA[$i + 1] = $value;
                        $i++;
                    }
                }
            }
        }
        return $DATA;
    }

    public static function getGridColumnData($objectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_display_column", array("*"));
        $SQL->where("USER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        $SQL->where("GRID_OBJECT_ID = '" . $objectId . "'");
        //error_log($SQL->__toString());
        $facette = self::dbAccess()->fetchRow($SQL);

        $CHECK_DATA = array();
        if ($facette)
        {
            $string1 = urldecode($facette->COLUMN_DATA_COKIS);
            $string2 = urldecode($string1);
            $string3 = urldecode($string2);
            $string4 = urldecode($string3);
            $string5 = urldecode($string4);
            $string6 = str_replace("o:columns=a:o:id=n", "", $string5);

            $explode = explode("id=n", $string6);

            foreach ($explode as $key => $value)
            {

                $p = strpos($value, "^");
                $i = substr($value, 1, $p - 1);
                if (preg_match('/hidden/i', $value, $matches))
                {
                    $CHECK_DATA[$key] = $key;
                }
            }
        }
        return $CHECK_DATA;
    }

    public static function setGridColumnData($objectId, $cokisdata, $columnsdata = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_user_display_column", array("*"));
        $SQL->where("USER_ID = '" . Zend_Registry::get('USER')->ID . "'");
        $SQL->where("GRID_OBJECT_ID = '" . $objectId . "'");
        $facette = self::dbAccess()->fetchRow($SQL);
        if ($cokisdata)
        {
            $SAVEDATA["COLUMN_DATA_COKIS"] = $cokisdata;
        }
        if ($columnsdata)
        {
            $SAVEDATA["COLUMN_DATA"] = $columnsdata;
        }
        if ($facette)
        {
            $WHERE[] = "USER_ID = '" . Zend_Registry::get('USER')->ID . "'";
            $WHERE[] = "GRID_OBJECT_ID = '" . $objectId . "'";
            self::dbAccess()->update('t_user_display_column', $SAVEDATA, $WHERE);
        }
        else
        {
            $SAVEDATA["USER_ID"] = Zend_Registry::get('USER')->ID;
            $SAVEDATA["GRID_OBJECT_ID"] = $objectId;
            self::dbAccess()->insert('t_user_display_column', $SAVEDATA);
        }
        return array("success" => true);
    }

    ////////////////////////////////////////////////////////////////////////////
    //GUARDIAN TREE  @Sea Peng 15.01.2014
    ////////////////////////////////////////////////////////////////////////////
    public static function getStudentGuardianHistyTreeItems($Id)
    {
        $entries = self::listStudentGuardian($Id);
        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $studentId = isset($value['ID']) ? $value['ID'] : "";
                $displayName = isset($value['STUDENT']) ? $value['STUDENT'] : "";
                $item = "{";
                $item .= "text: '" . $displayName . "'";
                $item .= ",title: '" . $displayName . "'";
                $item .= ",cls:'nodeTextBold'";
                $item .= ",iconCls: 'icon-user'";
                $item .= ",leaf: false";
                $item .= ",expanded: true";
                $item .= ",isClick:true";
                $item .= ",url:'/student/student/?camIds=" . self::urlEncryp()->encryptedGet("objectId=" . $studentId . "") . "'";
                $item .= ",children: [" . self::getStudentGuardianTranditionalSubTreeItems($studentId) . "]";
                $item .= "}";
                $data[] = $item;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    protected static function listStudentGuardian($Id)
    {
        $data = Array();
        $i = 0;
        if (GuardianDBAccess::sqlStudentGuardian($Id))
        {
            foreach (GuardianDBAccess::sqlStudentGuardian($Id) as $value)
            {
                $data[$i]['ID'] = $value->ID;
                if (!SchoolDBAccess::displayPersonNameInGrid())
                {
                    $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }
                else
                {
                    $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $i++;
            }
        }
        return $data;
    }

    protected static function getStudentGuardianTranditionalSubTreeItems($Id)
    {

        $item = "{";
        $item .= "text: '" . GENERAL_EDUCATION . "'";
        $item .= ",title: '" . GENERAL_EDUCATION . "'";
        $item .= ",cls:'nodeTextBold'";
        $item .= ",iconCls: 'icon-school'";
        $item .= ",leaf: false";
        $item .= ",expanded: true";
        $item .= ",isClick:false";
        $item .= ",children: [" . self::getStudentGuardianSchoolyearSubTreeItems($Id) . "]";
        $item .= "}";
        $item .= ",{";
        $item .= "text: '" . TRAINING_PROGRAMS . "'";
        $item .= ",title: '" . TRAINING_PROGRAMS . "'";
        $item .= ",cls:'nodeTextBold'";
        $item .= ",iconCls: 'icon-certificate'";
        $item .= ",leaf: false";
        $item .= ",expanded: true";
        $item .= ",isClick:false";
        $item .= ",children: [" . self::getStudentGuardianTrainingProgramSubTreeItems($Id) . "]";
        $item .= "}";

        return $item;
    }

    protected static function getStudentGuardianSchoolyearSubTreeItems($Id)
    {

        $entries = self::listStudentSchoolyear($Id);

        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $schoolyearId = isset($value['ID']) ? $value['ID'] : "";
                $classObject = self::getStudentClassTraditional($Id, $schoolyearId);

                if ($classObject)
                {
                    $displayName = isset($value['NAME']) ? $value['NAME'] . " (" . $classObject->CLASS_NAME . ")" : "?";
                    $classId = $classObject->CLASS_ID;
                    switch (Zend_Registry::get('MODUL_API'))
                    {
                        case "dfe34ef0f0b812ea32d92866dbe9e3cb":
                            $classparam = "academicId=" . $classId . "";
                            break;
                        case "dfe34ef0f0b812ea32d02866dbe9e3cb":
                            $classparam = "classId=" . $classId . "";
                            break;
                    }
                    $link = "/guardian/studenttranditional/?camIds=" . self::urlEncryp()->encryptedGet("" . $classparam . "&studentId=" . $Id . "") . "";
                }
                else
                {   //@veasna
                    $academicObject = StudentAcademicDBAccess::getStudentCurrentAcademicCreditSystem($Id, $schoolyearId);
                    $displayName = isset($value['NAME']) ? $value['NAME'] : "?";
                    if ($academicObject)
                    {
                        $displayName .= " (" . $academicObject->CAMPUS_NAME . ")";
                    }
                    $link = "/student/studentcampuscreditmain/?camIds=" . self::urlEncryp()->encryptedGet("studentId=" . $Id . "&academicId=" . $academicObject->TGRADE_SCHOOLYEAR . "&schoolyearId=" . $schoolyearId . "&campusId=" . $academicObject->CAMPUS_ID . "") . "";  //@veasna modify            
                }

                $item = "{";
                $item .= "text: '" . $displayName . "'";
                $item .= ",title: '" . $displayName . "'";
                $item .= ",cls:'nodeTextBold'";
                $item .= ",iconCls: 'icon-date'";
                $item .= ",leaf: true";
                $item .= ",expanded: true";
                $item .= ",url:'" . $link . "'";
                $item .= ",isClick:true";
                $item .= "}";
                $data[] = $item;
            }
        }
        return implode(",", $data);
    }

    protected static function getStudentGuardianTrainingProgramSubTreeItems($Id)
    {

        $entries = self::listDataStudentTrainingProgram($Id);

        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $displayName = isset($value['RROGRAM']) ? $value['RROGRAM'] : "";
                $item = "{";
                $item .= "text: '" . $displayName . "'";
                $item .= ",title: '" . $displayName . "'";
                $item .= ",cls:'nodeTextBold'";
                $item .= ",iconCls: 'icon-folder_magnify'";
                $item .= ",leaf: false";
                $item .= ",expanded: true";
                $item .= ",isClick:false";
                $item .= ",children: [" . self::getStudentGuardianTrainingTermSubTreeItems($Id) . "]";
                $item .= "}";
                $data[] = $item;
            }
        }
        return implode(",", $data);
    }

    protected static function getStudentGuardianTrainingTermSubTreeItems($Id)
    {

        $entries = self::listDataStudentTrainingProgram($Id);

        $data = array();

        if ($entries)
        {
            foreach ($entries as $value)
            {
                $trainingId = isset($value['TRAINING_ID']) ? $value['TRAINING_ID'] : "";
                $startDate = isset($value['START_DATE']) ? getShowDate($value['START_DATE']) : "";
                $endDate = isset($value['END_DATE']) ? getShowDate($value['END_DATE']) : "";
                $displayName = $startDate . " - " . $endDate;
                $item = "{";
                $item .= "text: '" . $displayName . "'";
                $item .= ",title: '" . $displayName . "'";
                $item .= ",cls:'nodeTextBold'";
                $item .= ",iconCls: 'icon-date'";
                $item .= ",leaf: true";
                $item .= ",expanded: true";
                $item .= ",url:'/guardian/studenttraining/?camIds=" . self::urlEncryp()->encryptedGet("trainingId=" . $trainingId . "&studentId=" . $Id . "") . "'";
                $item .= ",isClick:true";
                $item .= "}";
                $data[] = $item;
            }
        }
        return implode(",", $data);
    }

}

?>
