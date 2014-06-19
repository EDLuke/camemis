<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SubjectDBAccess {

    public $dataforjson = null;
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

        $this->SELECT = self::dbAccess()->select();
        $this->_TOSTRING = $this->SELECT->__toString();
        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();
    }

    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }

    public function getSubjectDataFromId($Id)
    {

        $data = array();
        $facette = self::findSubjectFromId($Id);

        if ($facette)
        {
            $data["ID"] = $facette->ID;
            $data["AVERAGE_FROM_SEMESTER"] = $facette->AVERAGE_FROM_SEMESTER;
            $data["NUMBER_CREDIT"] = $facette->NUMBER_CREDIT;
            $data["NUMBER_SESSION"] = $facette->NUMBER_SESSION;
            $data["PRE_REQUISITE_COURSE"] = $facette->PRE_REQUISITE_COURSE;
            $data["EDUCATION_SYSTEM"] = $facette->EDUCATION_SYSTEM;
            $data["SHORT"] = setShowText($facette->SHORT);
            $data["NAME"] = setShowText($facette->NAME);
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["STATUS"] = $facette->STATUS;
            $data["DEPARTMENT"] = $facette->DEPARTMENT;
            $data["SCORE_TYPE"] = $facette->SCORE_TYPE ? $facette->SCORE_TYPE : 1;
            $data["FORMULA_TYPE"] = $facette->FORMULA_TYPE ? $facette->FORMULA_TYPE : 1;
            $data["COEFF_VALUE"] = $facette->COEFF_VALUE;
            $data["COLOR"] = $facette->COLOR ? $facette->COLOR : "#FFFFFF";
            $data["SUBJECT_TYPE"] = $facette->SUBJECT_TYPE;
            $data["QUALIFICATION_TYPE"] = $facette->EDUCATION_TYPE;
            $data["EVALUATION_TYPE"] = $facette->EVALUATION_TYPE;
            $data["NATIONAL_EXAM"] = $facette->NATIONAL_EXAM ? true : false;
            $data["INCLUDE_IN_EVALUATION"] = $facette->INCLUDE_IN_EVALUATION ? true : false;
            $data["MAX_POSSIBLE_SCORE"] = $facette->MAX_POSSIBLE_SCORE;
            $data["REMOVE_STATUS"] = $this->checkRemoveSubject($facette->ID);
            $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($facette->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($facette->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($facette->DISABLED_DATE);
            $data["CREATED_BY"] = setShowText($facette->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($facette->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($facette->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($facette->DISABLED_BY);
            $data["SCORE_MIN"] = displayNumberFormat($facette->SCORE_MIN);
            $data["SCORE_MAX"] = displayNumberFormat($facette->SCORE_MAX);
        }
        return $data;
    }

    public static function findSubjectFromId($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject", array('*'));
        if (is_numeric($Id))
        {
            $SQL->where("ID = ?", $Id);
        }
        else
        {
            $SQL->where("GUID = ?", $Id);
        }
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadObject($Id)
    {

        $result = self::findSubjectFromId($Id);

        if ($result)
        {
            return array(
                "success" => true
                , "data" => $this->getSubjectDataFromId($Id)
            );
        }
        else
        {
            return array(
                "success" => true
                , "data" => array()
            );
        }
    }

    public static function getAllSubjectsQuery($params)
    {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $staffId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $status = isset($params["status"]) ? addText($params["status"]) : "0";
        $department = isset($params["department"]) ? addText($params["department"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $parent = isset($params["node"]) ? addText($params["node"]) : "";
        $node = isset($params["node"]) ? addText($params["node"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "GENERAL";

        if ($academicId)
        {
            $academicObject = AcademicDBAccess::findGradeFromId($academicId);
            $departmentId = $academicObject->DEPARTMENT;
        }
        else
        {
            $departmentId = 0;
        }

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_subject";
        $SQL .= " WHERE 1=1";

        if ($status)
            $SQL .= " AND STATUS='" . $status . "'";
        if ($globalSearch)
        {
            $SQL .= " AND ((NAME like '" . $globalSearch . "%') ";
            $SQL .= " OR (SHORT like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        switch (strtoupper($target))
        {
            case "GENERAL":
                $SQL .= " AND TRAINING =0";
                if ($department == "YES")
                {
                    if (!$staffId)
                    {
                        if ($academicId)
                        {
                            if ($departmentId)
                            {
                                if ($parent > 4)
                                {
                                    $SQL .= " AND (DEPARTMENT='" . $departmentId . "' OR DEPARTMENT=0)";
                                }
                            }
                        }
                    }
                }
                $SQL .= " AND PARENT ='" . $node . "'";
                break;
            case "TRAINING":
                $SQL .= " AND TRAINING =1";
                break;
        }

        $SQL .= " ORDER BY SHORT";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    ///////////////////////////////////////////////////////
    // Tree: List of Subjects...
    ///////////////////////////////////////////////////////
    public function treeAllSubjects($params)
    {

        $staffId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $department = isset($params["department"]) ? addText($params["department"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $requisiteId = isset($params["requisiteId"]) ? addText($params["requisiteId"]) : "";
        $schoolyear = isset($params["schoolyear"]) ? addText($params["schoolyear"]) : "";
        $gradeSubjectGradId = isset($params["gradeSubjectGradId"]) ? addText($params["gradeSubjectGradId"]) : "";

        $node = $params["node"];

        $WHERE = "WHERE OBJECT_TYPE='QUALIFICATION_TYPE' AND PARENT<>0";
        if ($requisiteId)
        {
            $subjectObject = self::findSubjectFromId($requisiteId);
            if ($subjectObject)
            {
                $WHERE .= " AND ID = '" . $subjectObject->PARENT . "'";
            }
        }

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        GradeSubjectDBAccess::setAutoSubject2GradeYearCreditSystem($academicId);

        $gradeId = $academicObject ? $academicObject->GRADE_ID : 0;
        $schoolyearId = $academicObject ? $academicObject->SCHOOL_YEAR : 0;
        $departmentId = $academicObject ? $academicObject->DEPARTMENT : 0;

        if (substr($params["node"], 19))
        {
            $node = str_replace('QUALIFICATION_TYPE_', '', $params["node"]);
        }
        else
        {
            $node = $params["node"];
        }

        if ($academicObject)
        {
            switch ($academicObject->OBJECT_TYPE)
            {
                case "SCHOOLYEAR":
                    $academicId = 0;
                    break;
                case "CLASS":
                    $academicId = $academicObject->ID;
                    break;
            }
        }

        $data = array();

        if ($node == 0)
        {

            $result = self::dbAccess()->fetchAll("SELECT * FROM t_camemis_type " . $WHERE . "");

            $i = 0;
            if ($result)
            {
                foreach ($result as $value)
                {
                    $data[$i]['id'] = "QUALIFICATION_TYPE_" . $value->ID;
                    $data[$i]['objectId'] = $value->ID;
                    $data[$i]['type'] = "qualification";
                    $data[$i]['show'] = false;
                    $data[$i]['text'] = $value->NAME;
                    $data[$i]['leaf'] = false;
                    $data[$i]['parentId'] = $value->ID;
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                    $i++;
                }
            }
        }
        else
        {

            $params["node"] = $node;
            $result = self::getAllSubjectsQuery($params);

            $i = 0;
            if ($result)
            {
                foreach ($result as $value)
                {

                    if ($gradeId && $schoolyearId)
                    {
                        $CHECK = GradeSubjectDBAccess::checkSubjectINGrade(
                                        $value->ID
                                        , $gradeId
                                        , $schoolyearId
                                        , $academicId
                        );
                        if ($CHECK == 0)
                        {

                            if (self::checkChild($value->ID))
                            {
                                $data[$i]['id'] = $value->ID;
                                $data[$i]['type'] = "subject";
                                $data[$i]['text'] = "(" . $value->SHORT . ")" . " - " . setShowText($value->NAME);
                                $data[$i]['cls'] = "nodeTextBold";
                                $data[$i]['leaf'] = false;
                                $data[$i]['iconCls'] = "icon-folder_magnify";
                                $i++;
                            }
                            else
                            {

                                if ($department == "YES")
                                {
                                    if ($value->DEPARTMENT == $departmentId)
                                    {
                                        $departmentObject = DepartmentDBAccess::findDepartmentFromId($value->DEPARTMENT);
                                        if ($departmentObject)
                                        {
                                            $data[$i]['qtip'] = $departmentObject->NAME;
                                        }
                                        else
                                        {
                                            $data[$i]['qtip'] = '?';
                                        }
                                        $data[$i]['id'] = $value->ID;
                                        $data[$i]['type'] = "subject";
                                        $data[$i]['text'] = "(" . $value->SHORT . ")" . " - " . setShowText($value->NAME);
                                        $data[$i]['cls'] = "nodeTextBlue";
                                        $data[$i]['leaf'] = true;
                                        $data[$i]['iconCls'] = "icon-flag_white";
                                        $i++;
                                    }
                                }
                                else
                                {

                                    $departmentObject = DepartmentDBAccess::findDepartmentFromId($value->DEPARTMENT);
                                    if ($departmentObject)
                                    {
                                        $data[$i]['qtip'] = $departmentObject->NAME;
                                    }
                                    else
                                    {
                                        $data[$i]['qtip'] = '?';
                                    }
                                    $data[$i]['id'] = $value->ID;
                                    $data[$i]['type'] = "subject";
                                    $data[$i]['text'] = "(" . $value->SHORT . ")" . " - " . setShowText($value->NAME);
                                    $data[$i]['cls'] = "nodeTextBlue";
                                    $data[$i]['leaf'] = true;
                                    $data[$i]['iconCls'] = "icon-flag_white";

                                    if ($staffId)
                                    {

                                        if (StaffDBAccess::checkSubjectByTeacher($staffId, $value->ID))
                                        {
                                            $data[$i]['checked'] = true;
                                        }
                                        else
                                        {
                                            $data[$i]['checked'] = false;
                                        }
                                    }
                                    $i++;
                                }
                            }
                        }
                    }
                    else
                    {

                        if (self::checkChild($value->ID))
                        {
                            $data[$i]['id'] = $value->ID;
                            $data[$i]['type'] = "subject";
                            $data[$i]['text'] = "(" . $value->SHORT . " " . $value->ID . ")" . " - " . setShowText($value->NAME);
                            $data[$i]['cls'] = "nodeTextBold";
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            $i++;
                        }
                        else
                        {

                            $departmentObject = DepartmentDBAccess::findDepartmentFromId($value->DEPARTMENT);

                            if ($departmentObject)
                            {
                                $data[$i]['qtip'] = $departmentObject->NAME;
                            }
                            else
                            {
                                $data[$i]['qtip'] = '?';
                            }

                            $data[$i]['id'] = $value->ID;
                            $data[$i]['parentId'] = $value->PARENT;
                            $data[$i]['type'] = "subject";
                            $data[$i]['text'] = "(" . $value->SHORT . ")" . " - " . setShowText($value->NAME);
                            $data[$i]['cls'] = $value->STATUS ? "nodeTextBlue" : "nodeTextRed";
                            $data[$i]['leaf'] = true;

                            if ($staffId)
                            {

                                if (StaffDBAccess::checkSubjectByTeacher($staffId, $value->ID))
                                {
                                    $data[$i]['checked'] = true;
                                }
                                else
                                {
                                    $data[$i]['checked'] = false;
                                }
                            }

                            if ($requisiteId)
                            {
                                $data[$i]['iconCls'] = "icon-shape_square_link";
                                $data[$i]["checked"] = in_array($value->ID, self::getPreRequisiteBySubject($requisiteId)) ? true : false;
                                //@veasna
                                if ($schoolyear)
                                {
                                    $data[$i]['iconCls'] = "icon-shape_square_link";
                                    $snaParams['academicId'] = $gradeSubjectGradId;
                                    $snaParams['schoolyear'] = $schoolyear;
                                    $snaParams['requisiteId'] = $requisiteId;
                                    $data[$i]["checked"] = in_array(
                                                    $value->ID
                                                    , GradeSubjectDBAccess::getPreRequisiteByGradeSubject($snaParams)) ? true : false;
                                }
                                //
                            }
                            else
                            {
                                $data[$i]['iconCls'] = "icon-star";
                            }

                            $i++;
                        }
                    }
                }
            }
        }

        return $data;
    }

    public static function actionPreRequisite2Subject($params)
    {

        $subjectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selecteds = isset($params["selecteds"]) ? addText($params["selecteds"]) : "";
        $facette = self::findSubjectFromId($subjectId);

        if ($facette)
        {
            $SAVEDATA['PRE_REQUISITE_COURSE'] = $selecteds;
            $WHERE[] = "ID = '" . $facette->ID . "'";
            self::dbAccess()->update('t_subject', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    // Grid: Subjects by Teacher...
    ////////////////////////////////////////////////////////////////////////////
    public function loadSubjectByTeacher($params)
    {

        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "0";
        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = self::getAllSubjectsQuery($params);

        $data = array();

        $i = 0;
        if ($result)
        {
            foreach ($result as $value)
            {

                if (!self::checkChild($value->ID))
                {

                    $assigned = $this->checkSubjectByTeacher($teacherId, $value->ID);
                    $inTheClass = $this->checkTeacherBySubjectTeacherClass($teacherId, $value->ID);
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["SUBJECT"] = "(" . $value->SHORT . ") " . $value->NAME;
                    $data[$i]["COEFF"] = $value->COEFF ? $value->COEFF : 1;
                    $data[$i]["MAX_POSSIBLE_SCORE"] = $value->MAX_POSSIBLE_SCORE;
                    $data[$i]["TEACHING"] = $assigned ? 1 : 0;
                    $data[$i]["ASSIGNED"] = $assigned;
                    $data[$i]["STATUS"] = $inTheClass;

                    $qualificationObject = CamemisTypeDBAccess::findObjectFromId($value->EDUCATION_TYPE);
                    $data[$i]["EDUCATION_TYPE"] = $qualificationObject ? $qualificationObject->NAME : "---";

                    $i++;
                }
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    ///////////////////////////////////////////////////////
    // Checktree: Teacher and Subject
    ///////////////////////////////////////////////////////
    public function treeTeacherSubjects($params)
    {

        //checked:true/false
        $data = array();
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "0";
        $result = self::getAllSubjectsQuery($params);

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $check = $this->checkSubjectByTeacher($teacherId, $value->ID);

                $data[$i]['iconCls'] = "icon-page";
                $data[$i]['cls'] = "nodeText";
                $data[$i]['leaf'] = true;
                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = setShowText($value->NAME);
                $data[$i]['cls'] = "nodeText";
                $data[$i]['readonly'] = true;
                $data[$i]['checked'] = $check ? true : false;

                $i++;
            }

        return $data;
    }

    ///////////////////////////////////////////////////////
    //Grid: List of Subjects...
    ///////////////////////////////////////////////////////
    public function allSubjects($params, $forjson)
    {
        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "100";

        $result = self::getAllSubjectsQuery($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["SHORT"] = $value->SHORT;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["MAX_POSSIBLE_SCORE"] = $value->MAX_POSSIBLE_SCORE;
                $data[$i]["COEFF"] = $value->COEFF ? $value->COEFF : 1;
                $data[$i]["SUBJECT_NAME"] = $value->NAME;

                $i++;
            }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++)
        {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        if ($forjson)
        {
            $dataforjson = array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
            return $dataforjson;
        }
        else
            return $a;
    }

    public static function allSubjectsComboData($educationType = false)
    {

        $data = array();

        if ($educationType)
        {
            $params["educationType"] = $educationType;
            $result = self::getAllSubjectsQuery($params);
        }
        else
        {
            $result = self::getAllSubjectsQuery(false);
        }

        $data[0] = "[0,'[---]']";
        $i = 0;
        if ($result)
            foreach ($result as $value)
            {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public function jsonActionSave($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "1";
        $type = isset($params["type"]) ? addText($params["type"]) : 0;

        $facette = self::findSubjectFromId($objectId);

        $SAVEDATA = array();

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        if (isset($params["QUALIFICATION_TYPE"]))
            $SAVEDATA['EDUCATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        if (isset($params["NUMBER_SESSION"]))
            $SAVEDATA['NUMBER_SESSION'] = addText($params["NUMBER_SESSION"]);

        if (isset($params["EDUCATION_SYSTEM"]))
            $SAVEDATA['EDUCATION_SYSTEM'] = addText($params["EDUCATION_SYSTEM"]);

        if (isset($params["DEPARTMENT"]))
            $SAVEDATA['DEPARTMENT'] = addText($params["DEPARTMENT"]);

        if (isset($params["MAX_POSSIBLE_SCORE"]))
            $SAVEDATA['MAX_POSSIBLE_SCORE'] = addText($params["MAX_POSSIBLE_SCORE"]);

        if (isset($params["SUBJECT_TYPE"]))
        {
            $SAVEDATA['SUBJECT_TYPE'] = addText($params["SUBJECT_TYPE"]);
        }

        if (isset($params["AVERAGE_FROM_SEMESTER"]))
            $SAVEDATA['AVERAGE_FROM_SEMESTER'] = addText($params["AVERAGE_FROM_SEMESTER"]);

        if ($facette)
            $SAVEDATA['PRE_REQUISITE_COURSE'] = $facette->PRE_REQUISITE_COURSE;

        if (isset($params["SCORE_MIN"]))
            $SAVEDATA['SCORE_MIN'] = addText($params["SCORE_MIN"]);

        if (isset($params["SCORE_MAX"]))
            $SAVEDATA['SCORE_MAX'] = addText($params["SCORE_MAX"]);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["FORMULA_TYPE"]))
        {
            $SAVEDATA['FORMULA_TYPE'] = addText($params["FORMULA_TYPE"]);
        }
        else
        {
            $SAVEDATA['FORMULA_TYPE'] = 1;
        }

        $SAVEDATA['COEFF_VALUE'] = isset($params["COEFF_VALUE"]) ? addText($params["COEFF_VALUE"]) : 1;

        $SAVEDATA['INCLUDE_IN_EVALUATION'] = isset($params["INCLUDE_IN_EVALUATION"]) ? 1 : 0;
        $SAVEDATA['SCORE_TYPE'] = isset($params["SCORE_TYPE"]) ? addText($params["SCORE_TYPE"]) : 1;

        if ($objectId != "new")
        {
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_subject', $SAVEDATA, $WHERE);
        }
        else
        {

            if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT)
            {
                $SAVEDATA['STATUS'] = 1;
            }

            if (isset($params["educationType"]))
                $SAVEDATA['EDUCATION_TYPE'] = addText($params["educationType"]);

            if ($type)
                $SAVEDATA['TRAINING'] = 1;

            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $SAVEDATA['GUID'] = generateGuid();
            $SAVEDATA['PARENT'] = $parentId;

            if (!self::findLastSubjectId())
            {
                $SAVEDATA['ID'] = self::findLastId() + 1000;
            }

            self::dbAccess()->insert('t_subject', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        return array("success" => true, "objectId" => $objectId);
    }

    public function SubjectCheckBox()
    {

        $entries = self::getAllSubjectsQuery(array());

        $CHECK_BOX = array();
        if ($entries)
            foreach ($entries as $key => $value)
            {
                $CHECK_BOX[$key] = "
                {
                fieldLabel: ''
                ,xtype: 'checkbox'
                ,id: '" . $value->ID . "_ID'
                ,boxLabel: '" . $value->NAME . "'
                ,name: 'CHECK_" . $value->ID . "'
                ,hideLabel: true
                }
                ";
            }

        return implode(",", $CHECK_BOX);
    }

    public function checkRemoveSubject($Id)
    {

        $count2 = $this->checkSubjectBySubjectTeacherClass($Id);
        $count3 = $this->checkSubjectByAssignment($Id);
        $count4 = self::existingSubjectInSchedule($Id);

        if ($count2 || $count3 || $count4)
        {
            $status = true;
        }
        else
        {
            $status = false;
        }

        return $status;
    }

    public function removeObject($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $CHECK = self::checkChild($objectId);

        if ($CHECK)
        {
            self::dbAccess()->query("DELETE FROM t_subject WHERE ID = '" . $objectId . "'");
            self::dbAccess()->query("DELETE FROM t_subject WHERE PARENT = '" . $objectId . "'");
        }
        else
        {
            self::dbAccess()->query("DELETE FROM t_subject WHERE ID = '" . $objectId . "'");
        }

        return array("success" => true);
    }

    protected function countTeachersBySubject($subjectId, $gradeId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select()
                ->from("t_subject", array("C" => "COUNT(*)"))
                ->where("GRADE = '" . $gradeId . "'")
                ->where("SCHOOLYEAR = '" . $schoolyearId . "'")
                ->where("SUBJECT = '" . $subjectId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function countAssignmentBySubject($subjectId, $gradeId)
    {

        $SQL = self::dbAccess()->select()
                ->from("t_assignment", array("C" => "COUNT(*)"))
                ->where("SUBJECT = '" . $subjectId . "'")
                ->where("GRADE = '" . $gradeId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function releaseObject($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findSubjectFromId($objectId);

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_subject";
        $SQL .= " SET";

        if (isset($facette))
        {
            switch ($facette->STATUS)
            {
                case 0:
                    $newStatus = 1;
                    $SQL .= " STATUS=1";
                    $SQL .= " ,ENABLED_DATE='" . getCurrentDBDateTime() . "'";
                    $SQL .= " ,ENABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                    break;
                case 1:
                    $newStatus = 0;
                    $SQL .= " STATUS=0";
                    $SQL .= " ,DISABLED_DATE='" . getCurrentDBDateTime() . "'";
                    $SQL .= " ,DISABLED_BY='" . Zend_Registry::get('USER')->CODE . "'";
                    break;
            }
        }

        $SQL .= " WHERE";
        $SQL .= " ID='" . $objectId . "'";

        self::dbAccess()->query($SQL);

        return array("success" => true, "status" => $newStatus);
    }

    public function treeSubjectsByClass($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "0";

        $result = self::sqlSubjectsByClass($academicId, false, false);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $gradeSubjectObject = GradeSubjectDBAccess::getGradeSubject(
                                false
                                , $value->GRADE_ID
                                , $value->SUBJECT_ID
                                , $value->SCHOOLYEAR_ID
                                , false);

                $data[$i]['id'] = "" . $value->SUBJECT_ID . "";
                $data[$i]['gradeSubjectId'] = $gradeSubjectObject ? $gradeSubjectObject->ID : "";
                $data[$i]['text'] = "" . $value->SUBJECT_NAME . "";
                $data[$i]['gradeId'] = "" . $value->GRADE_ID . "";

                switch ($value->SUBJECT_TYPE)
                {
                    case 0:
                        $data[$i]['iconCls'] = "icon-star_silver";
                        break;
                    case 1:
                        $data[$i]['iconCls'] = "icon-star";
                        break;
                    case 2:
                        $data[$i]['iconCls'] = "icon-star_blue";
                        break;
                    case 3:
                        $data[$i]['iconCls'] = "icon-star_red";
                        break;
                    default:
                        $data[$i]['iconCls'] = "icon-star";
                        break;
                }

                $data[$i]['cls'] = "nodeTextBlue";
                $data[$i]['leaf'] = true;
                $i++;
            }

        return $data;
    }

    protected function checkSubjectByAssignment($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_assignment", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkSubjectBySubjectTeacherClass($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkTeacherBySubjectTeacherClass($teacherId, $subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = ?", $subjectId);
        $SQL->where("TEACHER = ?", $teacherId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkTeacherBySubjectTeacherTraining($teacherId, $subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_training", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = ?", $subjectId);
        $SQL->where("TEACHER = ?", $teacherId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkSubjectByTeacher($teacherId, $subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_teacher_subject", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER = ?", $teacherId);
        $SQL->where("SUBJECT = ?", $subjectId);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function removeSubjectFromTeacher($teacherId, $subjectId)
    {

        $SQL = "
        DELETE FROM t_teacher_subject
        WHERE TEACHER = '" . $teacherId . "'
        AND SUBJECT = '" . $subjectId . "'
        ";
        self::dbAccess()->query($SQL);
    }

    protected function addTeacherSubject($teacherId, $subjectId)
    {

        $SQL = "
        INSERT INTO t_teacher_subject
        SET
        TEACHER = '" . $teacherId . "'
        ,SUBJECT = '" . $subjectId . "'
        ";
        self::dbAccess()->query($SQL);
    }

    public static function existingSubjectInSchedule($subjectId, $term = false)
    {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_schedule";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SUBJECT_ID = '" . $subjectId . "'";
        if ($term)
        {
            $SQL .= " AND TERM = '" . $term . "'";
        }
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function SubjectByTeacherCombo()
    {

        $utiles = Utiles::getInstance();
        $teacherId = "";

        if (UserAuth::getUserType() == 'TEACHER' || UserAuth::getUserType() == "INSTRUCTOR")
        {
            $teacherId = $utiles->getValueRegistry("USERID");
        }

        $gradeId = $utiles->getValueRegistry("GRADE_ID");
        $academicId = $utiles->getValueRegistry("CLASS_ID");

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.GRADE='" . $gradeId . "'";
        $SQL .= " AND B.ACADEMIC='" . $academicId . "'";
        $SQL .= " AND B.TEACHER='" . $teacherId . "'";

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            foreach ($result as $key => $value)
            {
                $data[$key] = "[\"$value->SUBJECT_ID\",\"$value->SUBJECT_NAME\"]";
            }

        return "[" . implode(",", $data) . "]";
    }

    public function actionTeacherSubject($params)
    {

        $teacherId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $subjectId = isset($params["Id"]) ? addText($params["Id"]) : "";
        $checked = isset($params["checked"]) ? addText($params["checked"]) : "";

        $type = isset($params["type"]) ? addText($params["type"]) : "";

        switch ($type)
        {
            case "GENERAL":
                $CHECK_SUBJECT_TEACHER_CLASS = $this->checkTeacherBySubjectTeacherClass($teacherId, $subjectId);
                $CHECK_SUBJECT_TEACHER = $this->checkSubjectByTeacher($teacherId, $subjectId);

                $msg = MSG_RECORD_NOT_CHANGED_DELETED;
                if ($checked == "true")
                {
                    if (!$CHECK_SUBJECT_TEACHER)
                    {
                        $this->addTeacherSubject($teacherId, $subjectId);
                        $msg = RECORD_WAS_ADDED;
                    }
                }
                else
                {
                    if (!$CHECK_SUBJECT_TEACHER_CLASS)
                    {
                        $this->removeSubjectFromTeacher($teacherId, $subjectId);
                    }
                }
                break;
            case "TRAINING":
                $CHECK_SUBJECT_TEACHER_TRAINING = $this->checkTeacherBySubjectTeacherTraining($teacherId, $subjectId);
                $CHECK_SUBJECT_TEACHER = $this->checkSubjectByTeacher($teacherId, $subjectId);

                $msg = MSG_RECORD_NOT_CHANGED_DELETED;
                if ($checked == "true")
                {
                    if (!$CHECK_SUBJECT_TEACHER)
                    {
                        $this->addTeacherSubject($teacherId, $subjectId);
                        $msg = RECORD_WAS_ADDED;
                    }
                }
                else
                {
                    if (!$CHECK_SUBJECT_TEACHER_TRAINING)
                    {
                        $this->removeSubjectFromTeacher($teacherId, $subjectId);
                    }
                }
                break;
        }

        return array("success" => true, "msg" => $msg);
    }

    public static function sqlSubjectsByClass($academicId, $isIncludeEvaluation = false, $isNationalExam = false)
    {

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,A.EVALUATION_TYPE AS EVALUATION_TYPE";
        $SQL .= " ,A.SCORE_TYPE AS SCORE_TYPE";
        $SQL .= " ,A.CREDITS AS CREDITS";
        $SQL .= " ,A.SHORT AS SUBJECT_SHORT";
        $SQL .= " ,A.COEFF AS COEFF";
        $SQL .= " ,A.COEFF_VALUE AS COEFF_VALUE";
        $SQL .= " ,B.GRADE AS GRADE_ID";
        $SQL .= " ,B.SCHOOLYEAR AS SCHOOLYEAR_ID";
        $SQL .= " ,B.ACADEMIC AS CLASS_ID";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.SUBJECT";

        $SQL .= " WHERE 1=1";
        if ($isIncludeEvaluation)
            $SQL .= " AND A.SUBJECT_TYPE <>4";
        if ($isNationalExam)
            $SQL .= " AND A.NATIONAL_EXAM=1";
        $SQL .= " AND B.CLASS='" . $academicId . "'";
        $SQL .= " GROUP BY A.ID ORDER BY A.NAME";
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }

    //@SODA
    public static function sqlSubjectsByTraining($trainingId, $isIncludeEvaluation = false, $isNationalExam = false)
    {

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,A.SCORE_TYPE AS SCORE_TYPE";
        $SQL .= " ,A.TEMPLATE AS TEMPLATE";
        $SQL .= " ,A.SHORT AS SUBJECT_SHORT";
        $SQL .= " ,A.COEFF AS COEFF";
        $SQL .= " ,A.COEFF_VALUE AS COEFF_VALUE";
        $SQL .= " ,B.TRAINING AS TRAINING_ID";
        $SQL .= " ,B.TERM AS TERM_ID";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_training AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";

        if ($isIncludeEvaluation)
            $SQL .= " AND A.SUBJECT_TYPE <>4";
        if ($isNationalExam)
            $SQL .= " AND A.NATIONAL_EXAM=1";
        $SQL .= " AND B.TRAINING='" . $trainingId . "'";
        $SQL .= " GROUP BY A.ID ORDER BY A.NAME";

        //error_log($SQL);

        return self::dbAccess()->fetchAll($SQL);
    }

    //

    public function subjectsByClassPrimary($academicId)
    {

        $result = self::sqlSubjectsByClass($academicId, true, false);

        $data = array();
        if ($result)
        {
            foreach ($result as $key => $value)
            {
                $data[$key]["ID"] = $value->SUBJECT_ID;
                $data[$key]["NAME"] = $value->SUBJECT_NAME;
                $data[$key]["TEMPLATE"] = $value->TEMPLATE;
            }
        }

        return $data;
    }

    public function jsonSubjectsByClass($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $data = array();

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.NAME AS NAME";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.ACADEMIC='" . $academicId . "'";
        $SQL .= " GROUP BY A.ID ORDER BY A.NAME";

        $result = self::dbAccess()->fetchAll($SQL);

        $data[0]["ID"] = "";
        $data[0]["NAME"] = "[---]";

        $data[1]["ID"] = "ALL_SUBJECTS";
        $data[1]["NAME"] = ALL_SUBJECTS;
        $i = 2;
        if ($result)
            foreach ($result as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $i++;
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function jsonSubject($edutype)
    {
        $SQL = "SELECT * FROM T_SUBJECT";
        $SQL .= " WHERE EDUCATION_TYPE=" . $edutype;
        $SQL .= " ORDER BY ID ASC";
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

    public function treeAllTrainingSubjects($params = false)
    {

        $params["target"] = "TRAINING";
        $staffId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $result = self::getAllSubjectsQuery($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $short = $value->SHORT ? $value->SHORT : "---";
                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = "(" . $short . ") " . stripslashes($value->NAME);

                if ($staffId)
                {

                    if (StaffDBAccess::checkSubjectByTeacher($staffId, $value->ID))
                    {
                        $data[$i]['checked'] = true;
                    }
                    else
                    {
                        $data[$i]['checked'] = false;
                    }
                }

                $data[$i]['cls'] = $value->STATUS ? "nodeTextBlue" : "nodeTextRed";
                $data[$i]['iconCls'] = "icon-flag_blue";
                $data[$i]['leaf'] = true;

                $i++;
            }

        return $data;
    }

    public static function checkUseSubjectInClass($subjectId, $classId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', 'COUNT(*) AS C');
        $SQL->where("SUBJECT = '" . $subjectId . "'");
        $SQL->where("CLASS = ?", $classId);
        $SQL->where("USED_IN_CLASS = '1'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getAcademicSubject($subjectId, $academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', '*');
        $SQL->where("SUBJECT = '" . $subjectId . "'");
        if (self::checkUseSubjectInClass($subjectId, $academicId))
        {
            $SQL->where("USED_IN_CLASS = '1'");
        }
        else
        {
            $SQL->where("USED_IN_CLASS = '0'");
        }
        $SQL->where("CLASS = ?", $academicId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        $facette = self::findSubjectFromId($subjectId);

        $data = array();

        if ($result)
        {
            $data["SUBJECT_ID"] = $facette->ID;
            $data["NUMBER_CREDIT"] = $facette->NUMBER_CREDIT;
            $data["SUBJECT_NAME"] = $facette->NAME;
            $data["SUBJECT_SHORT"] = $facette->SHORT;
            $data["SCORE_TYPE"] = $result->SCORE_TYPE;
            $data["SCORE_MIN"] = $result->SCORE_MIN;
            $data["SCORE_MAX"] = $result->SCORE_MAX;
            $data["MAX_POSSIBLE_SCORE"] = $result->MAX_POSSIBLE_SCORE;
            $data["INCLUDE_IN_EVALUATION"] = $result->INCLUDE_IN_EVALUATION;
            $data["GOALS"] = $result->GOALS;
            $data["OBJECTIVES"] = $result->OBJECTIVES;
            $data["COEFF_VALUE"] = $result->COEFF_VALUE;
            $data["AVERAGE_FROM_SEMESTER"] = $result->AVERAGE_FROM_SEMESTER;
            $data["GRADE_SUBJECT_ID"] = $result->ID;
        }

        return (object) $data;
    }

    public static function checkAcademicSubjectSchedule($subjectId, $academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule', 'COUNT(*) AS C');
        $SQL->where("ACADEMIC_ID = '" . $academicId . "'");
        $SQL->where("SUBJECT_ID = ?", $subjectId);
        $SQL->group("SUBJECT_ID");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function findSubjectFromGuId($GuId)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject", array("*"));
        $SQL->where("GUID = ?", $GuId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function checkChild($Id)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?", $Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected static function findLastSubjectId()
    {

        $SQL = "SELECT ID";
        $SQL .= " FROM t_subject";
        $SQL .= " ORDER BY ID DESC LIMIT 0 ,1";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ID : 0;
    }

    protected static function findLastQualificationTypeId()
    {

        $SQL = "SELECT ID";
        $SQL .= " FROM t_camemis_type WHERE OBJECT_TYPE='QUALIFICATION_TYPE'";
        $SQL .= " ORDER BY ID DESC LIMIT 0 ,1";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ID : 0;
    }

    protected static function findLastId()
    {

        return self::findLastSubjectId() + self::findLastQualificationTypeId();
    }

    ///@veasna
    public static function getPreRequisiteBySubject($id)
    {

        $facette = self::findSubjectFromId($id);
        $result = array();

        if ($facette)
        {
            if ($facette->PRE_REQUISITE_COURSE)
                $result = explode(",", $facette->PRE_REQUISITE_COURSE);
        }

        return $result;
    }

}

?>