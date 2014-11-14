<?
//////////////////////////////////////////////////////////////
//@Sea Peng 01.10.2013
//////////////////////////////////////////////////////////////

require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once setUserLoacalization();

Class AcademicAdditionalDBAccess {
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
    
    public static function jsonSaveAcademicAdditional($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "new";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : 0;
        $type = isset($params["CHOOSE_TYPE"]) ? addText($params["CHOOSE_TYPE"]): '';

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);
            
        if ($type)
            $SAVEDATA['CHOOSE_TYPE'] = $type;
            
        if($parentId){
            $facette = self::findAcademicAdditionalFromId($parentId);
            if($facette)
                $SAVEDATA['CHOOSE_TYPE'] = $facette->CHOOSE_TYPE;
            $SAVEDATA['OBJECT_TYPE'] = "ITEM";     
        }else{
            $SAVEDATA['OBJECT_TYPE'] = "FOLDER";    
        }

        if ($objectId == "new") {
            $SAVEDATA['PARENT'] = $parentId;
            self::dbAccess()->insert('t_additional_information', $SAVEDATA);

            $objectId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_additional_information', $SAVEDATA, $WHERE);
            
           if(!$parentId)
                self::updateChildren($objectId,$type);
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }
    
    public static function updateChildren($parentId,$type) {
        $SAVEDATA = array();
        if ($parentId and $type) {
            $SAVEDATA['CHOOSE_TYPE'] = $type;
            $WHERE = self::dbAccess()->quoteInto("PARENT = ?", $parentId);
            self::dbAccess()->update('t_additional_information', $SAVEDATA, $WHERE);
        }
    }
    
    public static function findAcademicAdditionalFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_additional_information", array('*'));
        $SQL->where("ID = ?",$Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }
    
    public static function jsonLoadAcademicAdditional($Id) {

        $facette = self::findAcademicAdditionalFromId($Id);

        $data = Array();
        if ($facette) {
            $data["ID"] = $facette->ID;
            $data["CHOOSE_TYPE"] = $facette->CHOOSE_TYPE;
            $data["NAME"] = setShowText($facette->NAME);
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }
    
    public static function sqlAcademicAdditional($node, $type = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_additional_information'),  array('*'));
        if (!$node){
            $SQL->where("PARENT=0");   
        }
        else{
            $SQL->where("PARENT=".$node."");   
        }

        if ($type)
            $SQL->where("CHOOSE_TYPE=".$type."");
        $SQL->order("NAME ASC");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonRemoveAcademicAdditional($Id) {
        $facette = self::findChild($Id);
        if($facette){
            foreach ($facette as $value) {
                self::dbAccess()->delete('t_additional_information_item', array("ITEM=" .$value->ID. ""));
            }       
        }
        
        self::dbAccess()->delete('t_additional_information', array("ID=" .$Id. " or PARENT=" .$Id. ""));
        self::dbAccess()->delete('t_academic_additional_information', array("ADDITIONAL_INFORMATION=" .$Id. ""));
        self::dbAccess()->delete('t_additional_information_item', array("ITEM=" .$Id. ""));

        return array("success" => true);
    }
    
    public static function findChild($Id){
        $SQL = self::dbAccess()->select();
        $SQL->from("t_additional_information", array('*'));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);        
    }
    
    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_additional_information", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    public static function sqlAcademicAdditionalByStudent($studentId, $classId) {        
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_additional_information'), array('*'));
        $SQL->joinRight(array('B' => 't_academic_additional_information'), 'A.ID=B.ADDITIONAL_INFORMATION', array('GRADE', 'SCHOOL_YEAR'));
        $SQL->joinLeft(array('C' => 't_student_schoolyear'), 'B.GRADE=C.GRADE AND B.SCHOOL_YEAR=C.SCHOOL_YEAR', array());
        $SQL->joinLeft(array('D' => 't_grade'), 'C.CLASS=D.ID', array());
        
        if ($studentId)
            $SQL->where("C.STUDENT='" . $studentId . "'");
            
        if ($classId)
            $SQL->where("D.GUID='" . $classId . "'"); 
            
        $SQL->order("NAME ASC");
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function jsonAllTreeAdditionalInformation($params) {

        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $data = array();
        $result = self::sqlAcademicAdditional($node);

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
    
                        if($objectId)
                            $data[$i]['checked'] = self::checkAcademicAdditionalInformation($objectId,$value->ID);
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
    
    public static function checkAcademicAdditionalInformation($objectId,$Id) {

        $academicObject = AcademicDBAccess::findGradeFromId($objectId);
        
        $SQL = self::dbAccess()->select();
        $SQL->from("t_academic_additional_information", array("C" => "COUNT(*)"));
        $SQL->where("GRADE = '" . $academicObject->GRADE_ID . "'");
        $SQL->where("SCHOOL_YEAR = '" . $academicObject->SCHOOL_YEAR . "'");
        $SQL->where("ADDITIONAL_INFORMATION = '" . $Id . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        if ($result) {
            return $result->C ? true : false;
        } else {
            return false;
        }
    }
    
    public static function jsonAdditionalInformationToAcademic($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $additionalId = isset($params["additionalId"]) ? $params["additionalId"] : "";
        $checked = isset($params["checked"]) ? addText($params["checked"]) : "";

        $academicObject = AcademicDBAccess::findGradeFromId($objectId);
        
        if ($checked == "true") {
            $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
            $SAVEDATA["SCHOOL_YEAR"] = $academicObject->SCHOOL_YEAR;
            $SAVEDATA["ADDITIONAL_INFORMATION"] = $additionalId;
            self::dbAccess()->insert("t_academic_additional_information", $SAVEDATA);
            $msg = RECORD_WAS_ADDED;
        } else {
            self::dbAccess()->delete("t_academic_additional_information", array("GRADE='" . $academicObject->GRADE_ID . "'", "SCHOOL_YEAR='" . $academicObject->SCHOOL_YEAR . "'", "ADDITIONAL_INFORMATION='" . $additionalId . "'"));
            $msg = MSG_RECORD_NOT_CHANGED_DELETED;
        }

        return array("success" => true, "msg" => $msg);
    }
}
?>