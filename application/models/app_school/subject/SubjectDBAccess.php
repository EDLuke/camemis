<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class SubjectDBAccess {

    public $dataforjson = null;
    public $data = array();

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new SubjectDBAccess();
        }
        return $me;
    }

    public function __construct() {

        $this->SELECT = self::dbAccess()->select();
        $this->_TOSTRING = $this->SELECT->__toString();
        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function getSubjectDataFromId($Id) {

        $data = array();
        $facette = self::findSubjectFromId($Id);

        if ($facette) {
            $data["ID"] = $facette->ID;
            $facette = self::findSubjectFromId($Id);

            $data["AVERAGE_FROM_SEMESTER"] = $facette->AVERAGE_FROM_SEMESTER;
            $data["NUMBER_CREDIT"] = displayNumberFormat($facette->NUMBER_CREDIT);
            $data["SHORT"] = setShowText($facette->SHORT);
            $data["COEFF"] = $facette->COEFF ? $facette->COEFF : 1;
            $data["NAME"] = setShowText($facette->NAME);
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["NUMBER_SESSION"] = $facette->NUMBER_SESSION;
            $data["STATUS"] = $facette->STATUS;
            $data["PRE_REQUISITE_COURSE"] = $facette->PRE_REQUISITE_COURSE;
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

    public static function findSubjectFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject", array('*'));
        if (is_numeric($Id)) {
            $SQL->where("ID = ?",$Id);
        } else {
            $SQL->where("GUID = ?",$Id);
        }
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public function loadObject($Id) {

        $result = self::findSubjectFromId($Id);
        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getSubjectDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function getAllSubjectsQuery($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $status = isset($params["status"]) ? addText($params["status"]) : "0";
        $educationType = isset($params["educationType"]) ? addText($params["educationType"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "GENERAL";

        if ($gradeId) {
            $gradeObject = AcademicDBAccess::findGradeFromId($gradeId);
            $educationType = $gradeObject->EDUCATION_TYPE;
        }

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " WHERE 1=1";

        if ($status)
            $SQL .= " AND A.STATUS='" . $status . "'";

        if ($educationType)
            $SQL .= " AND A.EDUCATION_TYPE = '" . $educationType . "'";

        if ($globalSearch) {
            $SQL .= " AND ((NAME like '" . $globalSearch . "%') ";
            $SQL .= " OR (SHORT like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        switch (strtoupper($target)) {
            case "GENERAL":
                $SQL .= " AND TRAINING =0";
                break;
            case "TRAINING":
                $SQL .= " AND TRAINING =1";
                break;
        }

        $SQL .= " ORDER BY A.NAME";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    ///////////////////////////////////////////////////////
    // Tree: List of Subjects...
    ///////////////////////////////////////////////////////
    public function treeAllSubjects($params) {

        $requisiteId = isset($params["requisiteId"]) ? addText($params["requisiteId"]) : "";
        //@veasna
        $schoolyear = isset($params["schoolyear"]) ? addText($params["schoolyear"]) : "";
        $gradeSubjectGradId = isset($params["gradeSubjectGradId"]) ? addText($params["gradeSubjectGradId"]) : "";
        //
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        $checkUsed = false;

        if ($academicObject) {
            $checkUsed = true;
            switch ($academicObject->OBJECT_TYPE) {
                case "CLASS":
                    $classId = $academicObject->ID;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
                case "SCHOOLYEAR":
                    $classId = false;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
            }
        }

        if (substr($params["node"], 19)) {
            $node = str_replace('QUALIFICATION_TYPE_', '', $params["node"]);
        } else {
            $node = $params["node"];
        }

        $data = array();

        $WHERE = "WHERE OBJECT_TYPE='QUALIFICATION_TYPE' AND PARENT<>0";
        if ($requisiteId) {
            $subjectObject = self::findSubjectFromId($requisiteId);
            if ($subjectObject) {
                $WHERE .= " AND ID = '" . $subjectObject->EDUCATION_TYPE . "'";
            }
        }

        if ($node == 0) {
            $i = 0;

            $result = self::dbAccess()->fetchAll("SELECT * FROM t_camemis_type " . $WHERE . "");

            if ($result) {
                foreach ($result as $key => $value) {
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
        } else {
            $params["educationType"] = $node;
            $result = self::getAllSubjectsQuery($params);
            $i = 0;

            if ($result) {
                switch (UserAuth::getUserType()) { //@Math Man
                    case "STUDENT":
                        $checkedPrerequisite = true;
                        foreach ($result as $key => $value) {
                            if ($requisiteId) {
                                if ($schoolyear) {
                                    $snaParams['academicId'] = $gradeSubjectGradId;
                                    $snaParams['schoolyear'] = $schoolyear;
                                    $snaParams['requisiteId'] = $requisiteId;
                                    $checkedPrerequisite = in_array($value->ID, GradeSubjectDBAccess::getPreRequisiteByGradeSubject($snaParams)) ? true : false;
                                } else {
                                    $checkedPrerequisite = in_array($value->ID, self::getPreRequisiteBySubject($requisiteId)) ? true : false;
                                }
                            }
                            if ($checkedPrerequisite) {
                                $data[$i]['id'] = $value->ID;
                                $data[$i]['subjectId'] = $value->ID;
                                if (!$checkUsed) {
                                    $CHECK_USED = false;
                                    $data[$i]['cls'] = $value->STATUS ? "nodeTextBlue" : "nodeTextRed";
                                    $data[$i]['isUsed'] = false;
                                } else {
                                    $CHECK_USED = GradeSubjectDBAccess::checkSubjectINGrade($value->ID, $gradeId, $schoolyearId, $classId);
                                    $data[$i]['cls'] = $CHECK_USED ? "nodeTextUnderline" : "nodeTextBlue";
                                    $data[$i]['isUsed'] = $CHECK_USED ? true : false;
                                }
                                $data[$i]['educationType'] = $value->EDUCATION_TYPE;
                                $data[$i]['text'] = "(" . $value->SHORT . ") " . setShowText($value->NAME);
                                $data[$i]['onlytext'] = setShowText($value->NAME);
                                $data[$i]['leaf'] = true;
                                $data[$i]['iconCls'] = "icon-shape_square_link";
                                $i++;
                            }
                        }
                        break;
                    ///////////////////////
                    default:
                        foreach ($result as $key => $value) {

                            $data[$i]['id'] = $value->ID;
                            $data[$i]['subjectId'] = $value->ID;

                            if (!$checkUsed) {
                                $CHECK_USED = false;
                                $data[$i]['cls'] = $value->STATUS ? "nodeTextBlue" : "nodeTextRed";
                                $data[$i]['isUsed'] = false;
                            } else {
                                $CHECK_USED = GradeSubjectDBAccess::checkSubjectINGrade($value->ID, $gradeId, $schoolyearId, $classId);
                                $data[$i]['cls'] = $CHECK_USED ? "nodeTextUnderline" : "nodeTextBlue";
                                $data[$i]['isUsed'] = $CHECK_USED ? true : false;
                            }

                            $data[$i]['educationType'] = $value->EDUCATION_TYPE;
                            $data[$i]['text'] = "(" . $value->SHORT . ") " . setShowText($value->NAME);
                            $data[$i]['onlytext'] = setShowText($value->NAME);

                            $data[$i]['leaf'] = true;

                            if ($requisiteId) {
                                $data[$i]['iconCls'] = "icon-shape_square_link";
                                $data[$i]["checked"] = in_array($value->ID, self::getPreRequisiteBySubject($requisiteId)) ? true : false;
                                //@veasna
                                if ($schoolyear) {
                                    $data[$i]['iconCls'] = "icon-shape_square_link";
                                    $snaParams['academicId'] = $gradeSubjectGradId;
                                    $snaParams['schoolyear'] = $schoolyear;
                                    $snaParams['requisiteId'] = $requisiteId;
                                    $data[$i]["checked"] = in_array($value->ID, GradeSubjectDBAccess::getPreRequisiteByGradeSubject($snaParams)) ? true : false;
                                }
                                //
                            } else {
                                $data[$i]['iconCls'] = "icon-star";
                            }
                            $i++;
                        }
                        break;
                }
            }
        }

        return $data;
    }

    ///////////////////////////////////////////////////////
    // Grid: Subjects by Teacher...
    ///////////////////////////////////////////////////////
    public function loadSubjectByTeacher($params) {

        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "0";
        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $params["status"] = 1;
        $result = self::getAllSubjectsQuery($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $qualificationObject = CamemisTypeDBAccess::findObjectFromId($value->EDUCATION_TYPE);
                $data[$i]["EDUCATION_TYPE"] = $qualificationObject ? $qualificationObject->NAME : "---";

                $data[$i]["ID"] = $value->ID;
                $assigned = $this->checkSubjectByTeacher($teacherId, $value->ID);
                $inTheClass = $this->checkTeacherBySubjectTeacherClass($teacherId, $value->ID);
                $data[$i]["ID"] = $value->ID;
                $data[$i]["SUBJECT"] = "(" . $value->SHORT . ") " . $value->NAME;
                $data[$i]["COEFF"] = $value->COEFF ? $value->COEFF : 1;
                $data[$i]["MAX_POSSIBLE_SCORE"] = $value->MAX_POSSIBLE_SCORE;
                $data[$i]["TEACHING"] = $assigned ? 1 : 0;
                $data[$i]["ASSIGNED"] = $assigned;
                $data[$i]["STATUS"] = $inTheClass;

                $i++;
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
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
    public function treeTeacherSubjects($params) {

        //checked:true/false
        $data = array();
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "0";
        $result = self::getAllSubjectsQuery($params);

        $i = 0;
        if ($result)
            foreach ($result as $value) {

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
    public function allSubjects($params, $forjson = true) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";

        $result = self::getAllSubjectsQuery($params);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["SHORT"] = $value->SHORT;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["MAX_POSSIBLE_SCORE"] = $value->MAX_POSSIBLE_SCORE;
                $data[$i]["COEFF"] = $value->COEFF ? $value->COEFF : 1;
                $data[$i]["SUBJECT_NAME"] = $value->NAME;

                $i++;
            }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        if ($forjson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else
            return $a;
    }

    public static function allSubjectsComboData() {

        $data = array();
        $result = self::getAllSubjectsQuery(false);

        $data[0] = "[0,'[---]']";
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public function removeSubject($params) {

        $subjectId = isset($params["removeId"]) ? addText($params["removeId"]) : "";

        $SQL = "DELETE FROM t_grade_subject";
        $SQL .= " WHERE";
        $SQL .= " SUBJECT='" . $subjectId . "' AND";

        if (!$this->checkRemoveSubject($subjectId)) {
            self::dbAccess()->query($SQL);
        }

        return array("success" => true);
    }

    public function updateSubject($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findSubjectFromId($objectId);

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        if (isset($params["NUMBER_SESSION"]))
            $SAVEDATA['NUMBER_SESSION'] = addText($params["NUMBER_SESSION"]);

        if (isset($params["QUALIFICATION_TYPE"]))
            $SAVEDATA['EDUCATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);

        if (isset($params["MAX_POSSIBLE_SCORE"]))
            $SAVEDATA['MAX_POSSIBLE_SCORE'] = addText($params["MAX_POSSIBLE_SCORE"]);

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        if (isset($params["AVERAGE_FROM_SEMESTER"]))
            $SAVEDATA['AVERAGE_FROM_SEMESTER'] = addText($params["AVERAGE_FROM_SEMESTER"]);

        if (isset($params["SUBJECT_TYPE"])) {
            $SAVEDATA['SUBJECT_TYPE'] = addText($params["SUBJECT_TYPE"]);
        }

        if ($facette) {
            $SAVEDATA['PRE_REQUISITE_COURSE'] = $facette->PRE_REQUISITE_COURSE;
        }

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        if (isset($params["FORMULA_TYPE"])) {
            $SAVEDATA['FORMULA_TYPE'] = addText($params["FORMULA_TYPE"]);
        } else {
            $SAVEDATA['FORMULA_TYPE'] = 1;
        }
        $SAVEDATA['COEFF_VALUE'] = addText($params["COEFF_VALUE"]);
        $SAVEDATA['INCLUDE_IN_EVALUATION'] = isset($params["INCLUDE_IN_EVALUATION"]) ? 1 : 0;

        if (isset($params["SCORE_TYPE"])) {
            $SAVEDATA['SCORE_TYPE'] = $params["SCORE_TYPE"];
        } else {
            $SAVEDATA['SCORE_TYPE'] = 1;
        }

        $SAVEDATA['COEFF_VALUE'] = isset($params["COEFF_VALUE"]) ? addText($params["COEFF_VALUE"]) : 1;
        $SAVEDATA['NATIONAL_EXAM'] = isset($params["NATIONAL_EXAM"]) ? 1 : 0;

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        if (isset($params["SCORE_MIN"]))
            $SAVEDATA['SCORE_MIN'] = addText($params["SCORE_MIN"]);

        if (isset($params["SCORE_MAX"]))
            $SAVEDATA['SCORE_MAX'] = addText($params["SCORE_MAX"]);

        if (isset($params["MAX_POSSIBLE_SCORE"]))
            $SAVEDATA['MAX_POSSIBLE_SCORE'] = addText($params["MAX_POSSIBLE_SCORE"]);

        if ($params["objectId"] != "new") {
            $WHERE = Zend_Registry::get('DB_ACCESS')->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_subject', $SAVEDATA, $WHERE);
        } else {
            if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
                $SAVEDATA['STATUS'] = 1;
            }

            self::dbAccess()->insert('t_subject', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    public function SubjectCheckBox() {

        $entries = self::getAllSubjectsQuery(array());

        $CHECK_BOX = array();
        if ($entries)
            foreach ($entries as $key => $value) {
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

    ////////////////////////////////////////////////////////////////////////////
    //@Sea Peng 22.08.2013
    ////////////////////////////////////////////////////////////////////////////

    public function createOnlyItem($params) {

        $SAVEDATA = array();

        $educationType = isset($params["educationType"]) ? addText($params["educationType"]) : "";

        $SAVEDATA['GUID'] = generateGuid();

        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT) {
            $SAVEDATA['STATUS'] = 0;
        }
        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        if (isset($params["NUMBER_SESSION"]))
            $SAVEDATA['NUMBER_SESSION'] = addText($params["NUMBER_SESSION"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);

        $SAVEDATA['NATIONAL_EXAM'] = isset($params["NATIONAL_EXAM"]) ? 1 : 0;
        $SAVEDATA['SCORE_TYPE'] = isset($params["SCORE_TYPE"]) ? addText($params["SCORE_TYPE"]) : 1;
        $SAVEDATA['EDUCATION_TYPE'] = addText($educationType);

        if ($educationType == "TRAINING") {
            $SAVEDATA['TRAINING'] = 1;
            $SAVEDATA['EVALUATION_TYPE'] = 1;
        }

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SCORE_MAX"]))
            $SAVEDATA['SCORE_MAX'] = addText($params["SCORE_MAX"]);

        if (isset($params["SCORE_MIN"]))
            $SAVEDATA['SCORE_MIN'] = addText($params["SCORE_MIN"]);

        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

        if (!self::findLastSubjectId()) {
            $SAVEDATA['ID'] = self::findLastId() + 1000;
        }

        self::dbAccess()->insert('t_subject', $SAVEDATA);

        $objectId = self::dbAccess()->lastInsertId();

        return array(
            "success" => true
            , "objectId" => $objectId
        );
    }

    ////////////////////////////////////////////////////////////////////////////

    public function checkRemoveSubject($Id) {

        $count2 = $this->checkSubjectBySubjectTeacherClass($Id);
        $count3 = $this->checkSubjectByAssignment($Id);
        $count4 = self::existingSubjectInSchedule($Id);

        if ($count2 || $count3 || $count4) {
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }

    public function removeObject($params) {

        $SQL = "
		DELETE FROM t_subject
		WHERE ID = '" . addText($params["objectId"]) . "'
		";
        self::dbAccess()->query($SQL);
        return array("success" => true);
    }

    protected function countTeachersBySubject($subjectId, $gradeId, $schoolyearId) {

        $SQL = self::dbAccess()->select()
                ->from("t_subject", array("C" => "COUNT(*)"))
                ->where("GRADE = '" . $gradeId . "'")
                ->where("SCHOOLYEAR = '" . $schoolyearId . "'")
                ->where("SUBJECT = '" . $subjectId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function countAssignmentBySubject($subjectId, $gradeId) {

        $SQL = self::dbAccess()->select()
                ->from("t_assignment", array("C" => "COUNT(*)"))
                ->where("SUBJECT = '" . $subjectId . "'")
                ->where("GRADE = '" . $gradeId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function releaseObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findSubjectFromId($objectId);

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_subject";
        $SQL .= " SET";

        if (isset($facette)) {
            switch ($facette->STATUS) {
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

    public function treeSubjectsByClass($params) {

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "0";

        $result = self::sqlSubjectsByClass($classId, false, false);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $gradeSubjectObject = GradeSubjectDBAccess::getGradeSubject(
                                false
                                , $value->GRADE_ID
                                , $value->SUBJECT_ID
                                , $value->SCHOOLYEAR_ID
                                , $classId);

                $data[$i]['id'] = $value->SUBJECT_ID;
                $data[$i]['gradeSubjectId'] = $gradeSubjectObject ? $gradeSubjectObject->ID : "";
                $data[$i]['text'] = "" . $value->SUBJECT_NAME . "";
                $data[$i]['gradeId'] = "" . $value->GRADE_ID . "";

                $data[$i]['iconCls'] = "icon-star";

                $data[$i]['cls'] = "nodeTextBlue";
                $data[$i]['leaf'] = true;
                $i++;
            }

        return $data;
    }

    protected function checkSubjectByAssignment($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_assignment", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkSubjectBySubjectTeacherClass($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = '" . $Id . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkTeacherBySubjectTeacherClass($teacherId, $subjectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("SUBJECT = ?",$subjectId);
        $SQL->where("TEACHER = ?",$teacherId);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function checkSubjectByTeacher($teacherId, $subjectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_teacher_subject", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER = ?",$teacherId);
        $SQL->where("SUBJECT = ?",$subjectId);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function actionTeacherSubjects($params) {

        $newValue = $params["newValue"];
        $field = $params["field"];
        $subjectId = isset($params["id"]) ? addText($params["id"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";

        $check = $this->checkTeacherBySubjectTeacherClass($teacherId, $subjectId);

        if ($check == 0) {

            $this->removeSubjectFromTeacher($teacherId, $subjectId);

            if ($newValue == 1 && $field == "TEACHING") {
                $this->addTeacherSubject($teacherId, $subjectId);
                $ACTION_STATUS = 0;
            } else {
                $ACTION_STATUS = 0;
            }
        } else {
            $ACTION_STATUS = 1;
        }

        return array(
            "success" => true,
            "ACTION_STATUS" => $ACTION_STATUS
        );
    }

    protected function removeSubjectFromTeacher($teacherId, $subjectId) {

        $SQL = "
		DELETE FROM t_teacher_subject
		WHERE TEACHER = '" . $teacherId . "'
		AND SUBJECT = '" . $subjectId . "'
		";
        self::dbAccess()->query($SQL);
    }

    protected function addTeacherSubject($teacherId, $subjectId) {

        $SQL = "
		INSERT INTO t_teacher_subject
		SET
		TEACHER = '" . $teacherId . "'
		,SUBJECT = '" . $subjectId . "'
		";
        self::dbAccess()->query($SQL);
    }

    public static function existingSubjectInSchedule($subjectId, $term = false) {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_schedule";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SUBJECT_ID = '" . $subjectId . "'";
        if ($term) {
            $SQL .= " AND TERM = '" . $term . "'";
        }
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function SubjectByTeacherCombo() {

        $utiles = Utiles::getInstance();
        $teacherId = "";

        if (UserAuth::getUserType() == 'TEACHER' || UserAuth::getUserType() == "INSTRUCTOR") {
            $teacherId = $utiles->getValueRegistry("USERID");
        }

        $gradeId = $utiles->getValueRegistry("GRADE_ID");
        $classId = $utiles->getValueRegistry("CLASS_ID");

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.GRADE='" . $gradeId . "'";
        $SQL .= " AND B.ACADEMIC='" . $classId . "'";
        $SQL .= " AND B.TEACHER='" . $teacherId . "'";

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            foreach ($result as $key => $value) {
                $data[$key] = "[\"$value->SUBJECT_ID\",\"$value->SUBJECT_NAME\"]";
            }

        return "[" . implode(",", $data) . "]";
    }

    public function actionTeacherSubject($params) {

        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $subjectId = isset($params["id"]) ? addText($params["id"]) : "";

        $isInClass = $this->checkTeacherBySubjectTeacherClass($teacherId, $subjectId);
        $check = $this->checkSubjectByTeacher($teacherId, $subjectId);

        if ($params["newValue"] == 1) {
            if (!$check) {
                $this->addTeacherSubject($teacherId, $subjectId);
            }
        }
        if ($params["newValue"] == 0) {
            if (!$isInClass) {
                $this->removeSubjectFromTeacher($teacherId, $subjectId);
            }
        }

        return array("success" => true);
    }

    public static function sqlSubjectsByClass($classId, $isIncludeEvaluation = false, $isNationalExam = false) {

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,A.EVALUATION_TYPE AS EVALUATION_TYPE";
        $SQL .= " ,A.SCORE_TYPE AS SCORE_TYPE";
        $SQL .= " ,A.TEMPLATE AS TEMPLATE";
        $SQL .= " ,A.SHORT AS SUBJECT_SHORT";
        $SQL .= " ,A.COEFF AS COEFF";
        $SQL .= " ,A.COEFF_VALUE AS COEFF_VALUE";
        $SQL .= " ,B.GRADE AS GRADE_ID";
        $SQL .= " ,B.SCHOOLYEAR AS SCHOOLYEAR_ID";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";

        if ($isIncludeEvaluation)
            $SQL .= " AND A.SUBJECT_TYPE <>4";
        if ($isNationalExam)
            $SQL .= " AND A.NATIONAL_EXAM=1";
        $SQL .= " AND B.ACADEMIC='" . $classId . "'";
        $SQL .= " GROUP BY A.ID ORDER BY A.NAME";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    //@SODA
    public static function sqlSubjectsByTraining($trainingId, $isIncludeEvaluation = false, $isNationalExam = false) {

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,A.EVALUATION_TYPE AS EVALUATION_TYPE";
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

    public function subjectsByClassPrimary($classId) {

        $result = self::sqlSubjectsByClass($classId, true, false);

        $data = array();
        if ($result) {
            foreach ($result as $key => $value) {
                $data[$key]["ID"] = $value->SUBJECT_ID;
                $data[$key]["NAME"] = $value->SUBJECT_NAME;
                $data[$key]["TEMPLATE"] = $value->TEMPLATE;
            }
        }

        return $data;
    }

    public function jsonSubjectsByClass($params) {

        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";

        $data = array();

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " A.ID AS ID";
        $SQL .= " ,A.NAME AS NAME";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.ACADEMIC='" . $classId . "'";
        $SQL .= " GROUP BY A.ID ORDER BY A.NAME";

        $result = self::dbAccess()->fetchAll($SQL);

        $data[0]["ID"] = "";
        $data[0]["NAME"] = "[---]";

        $data[1]["ID"] = "ALL_SUBJECTS";
        $data[1]["NAME"] = ALL_SUBJECTS;
        $i = 2;
        if ($result)
            foreach ($result as $value) {
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

    public static function jsonSubject($edutype) {
        $SQL = "SELECT * FROM T_SUBJECT";
        $SQL .= " WHERE EDUCATION_TYPE=" . $edutype;
        $SQL .= " ORDER BY ID ASC";
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

    public function treeAllTrainingSubjects() {

        $searchParams["target"] = "TRAINING";
        $result = self::getAllSubjectsQuery($searchParams);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $short = $value->SHORT ? $value->SHORT : "---";
                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['text'] = "(" . $short . ") " . stripslashes($value->NAME);

                if ($value->STATUS) {
                    $data[$i]['iconCls'] = "icon-green";
                } else {
                    $data[$i]['iconCls'] = "icon-red";
                }

                $data[$i]['cls'] = "nodeTextBold";
                $data[$i]['leaf'] = true;

                $i++;
            }

        return $data;
    }

    protected static function findLastSubjectId() {

        $SQL = "SELECT ID";
        $SQL .= " FROM t_subject";
        $SQL .= " ORDER BY ID DESC LIMIT 0 ,1";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->ID : 0;
    }

    public static function checkUseSubjectInClass($subjecId, $classId) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', 'COUNT(*) AS C');
        $SQL->where("SUBJECT = '" . $subjecId . "'");
        $SQL->where("CLASS = ?",$classId);
        $SQL->where("USED_IN_CLASS = '1'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getAcademicSubject($subjectId, $academicId) {

        $SELECTION_B = array(
            "INCLUDE_IN_EVALUATION"
            , "MAX_POSSIBLE_SCORE"
            , "SCORE_MIN"
            , "SCORE_MAX"
            , "GOALS"
            , "OBJECTIVES"
            , "MATERIALS"
            , "EVALUATION"
            , "GOALS"
            , "COEFF_VALUE"
            , "AVERAGE_FROM_SEMESTER"
            , "ID AS GRADE_SUBJECT_ID"
        );

        $SELECTION_C = array(
            "NAME AS SUBJECT_NAME"
            , "SHORT AS SUBJECT_SHORT"
            , "ID AS SUBJECT_ID"
            , "SCORE_TYPE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_schedule"), array());
        $SQL->joinLeft(array('B' => "t_grade_subject"), 'A.SUBJECT_ID=B.SUBJECT', $SELECTION_B);
        $SQL->joinLeft(array('C' => "t_subject"), 'A.SUBJECT_ID=C.ID', $SELECTION_C);
        $SQL->where('A.ACADEMIC_ID = ?', $academicId);
        $SQL->where('A.SUBJECT_ID = ?', $subjectId);
        $SQL->group("A.SUBJECT_ID");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findSubjectFromGuId($GuId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject", array("*"));
        $SQL->where("GUID = '" . $GuId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    protected static function findLastId() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject", array("C" => "COUNT(*)"));
        $SQL->order('ID DESC');
        $SQL->limitPage(0, 1);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function actionPreRequisite2Subject($params) {

        $subjectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selecteds = isset($params["selecteds"]) ? addText($params["selecteds"]) : "";
        $facette = self::findSubjectFromId($subjectId);

        if ($facette) {
            $SAVEDATA['PRE_REQUISITE_COURSE'] = $selecteds;
            $WHERE[] = "ID = '" . $facette->ID . "'";
            self::dbAccess()->update('t_subject', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    public static function getPreRequisiteBySubject($id) {

        $facette = self::findSubjectFromId($id);
        $result = array();

        if ($facette) {
            if ($facette->PRE_REQUISITE_COURSE)
                $result = explode(",", $facette->PRE_REQUISITE_COURSE);
        }

        return $result;
    }

}

?>