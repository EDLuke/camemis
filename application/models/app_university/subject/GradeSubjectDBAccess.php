<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class GradeSubjectDBAccess extends SubjectDBAccess {

    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function loadSubjectGrade($params)
    {

        $gradesubjectId = isset($params["gradesubjectId"]) ? addText($params["gradesubjectId"]) : "";
        $facette = self::getGradeSubject($gradesubjectId, false, false, false);

        if ($facette)
        {

            $subjectObject = SubjectDBAccess::findSubjectFromId($facette->SUBJECT);

            $data["ID"] = $facette->ID;
            $data["SHORT"] = $subjectObject->SHORT;
            $data["NAME"] = $subjectObject->NAME;
            $data["GRADE"] = $facette->GRADE;
            $data["SUBJECT"] = $facette->SUBJECT;
            $data["SCORE_TYPE"] = $facette->SCORE_TYPE;
            $data["FORMULA_TYPE"] = $facette->FORMULA_TYPE ? $facette->FORMULA_TYPE : 1;
            $data["COEFF_VALUE"] = $facette->COEFF_VALUE ? $facette->COEFF_VALUE : 1;

            if ($facette->ASSIGNED_SUBJECT)
            {
                $assignedSubject = SubjectDBAccess::findSubjectFromId($facette->ASSIGNED_SUBJECT);
                $data["CHOOSE_ASSIGNED_SUBJECT_NAME"] = $assignedSubject->NAME;
            }

            $data["NUMBER_SESSION"] = $facette->NUMBER_SESSION;
            $data["EDUCATION_SYSTEM"] = $facette->EDUCATION_SYSTEM;
            $data["DEPARTMENT"] = $facette->DEPARTMENT;
            $data["NUMBER_CREDIT"] = $facette->NUMBER_CREDIT;
            $data["NUMBER_SESSION"] = $facette->NUMBER_SESSION;

            $data["AVERAGE_FROM_SEMESTER"] = $facette->AVERAGE_FROM_SEMESTER;
            $data["COLOR"] = $facette->COLOR ? $facette->COLOR : "#FFFFFF";
            $data["SUBJECT_TYPE"] = $facette->SUBJECT_TYPE;
            $data["EDUCATION_TYPE"] = $facette->EDUCATION_TYPE;
            $data["EVALUATION_TYPE"] = $facette->EVALUATION_TYPE ? $facette->EVALUATION_TYPE : 1;
            $data["NATIONAL_EXAM"] = $facette->NATIONAL_EXAM ? true : false;
            $data["INCLUDE_IN_EVALUATION"] = $facette->INCLUDE_IN_EVALUATION ? true : false;
            $data["TEMPLATE"] = $facette->TEMPLATE;
            $data["MAX_POSSIBLE_SCORE"] = displayNumberFormat($facette->MAX_POSSIBLE_SCORE);
            $data["DESCRIPTION"] = setShowText($facette->DESCRIPTION);
            $data["MATERIALS"] = setShowText($facette->MATERIALS);
            $data["EVALUATION"] = setShowText($facette->EVALUATION);
            $data["OBJECTIVES"] = setShowText($facette->OBJECTIVES);
            $data["GOALS"] = setShowText($facette->GOALS);
            $data["BODY_OF_LESSON"] = setShowText($facette->BODY_OF_LESSON);
            $data["SCORE_MIN"] = displayNumberFormat($facette->SCORE_MIN);
            $data["SCORE_MAX"] = displayNumberFormat($facette->SCORE_MAX);
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function getSubjectsOfClass($academicId)
    {
        $params = array();
        $params["academicId"] = $academicId;
        return self::sqlAssignedSubjectsByGrade($params);
    }

    public static function getListSubjectsToAcademic($academicId, $term = false)
    {

        $result = "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {
            $SELECT_B = array(
                "ID AS SUBJECT_ID"
                , "NAME AS SUBJECT_NAME"
                , "SHORT AS SUBJECT_SHORT"
                , "SCORE_TYPE AS SCORE_TYPE"
            );
            $SELECT_C = array(
                "INCLUDE_IN_EVALUATION"
                ,"COEFF_VALUE"
            );
            $SQL = self::dbAccess()->select();
            $SQL->distinct();
            $SQL->from(array('A' => "t_schedule"), array());
            $SQL->joinLeft(array('B' => "t_subject"), 'A.SUBJECT_ID=B.ID', $SELECT_B);
            $SQL->joinLeft(array('C' => "t_grade_subject"), 'A.SUBJECT_ID=C.SUBJECT', $SELECT_C);
            $SQL->where('A.ACADEMIC_ID = ?', $academicObject->ID);
            if ($term)
                $SQL->where('A.TERM = ?', $term);
            $SQL->group("A.SUBJECT_ID");
            $SQL->order('B.NAME');
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
        }

        return $result;
    }

    public static function sqlAssignedSubjectsByGrade($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $subjectType = isset($params["subjectType"]) ? addText($params["subjectType"]) : "";
        $include_in_evaluation = isset($params["include_in_evaluation"]) ? (int) $params["include_in_evaluation"] : "0";

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        if ($academicId)
        {
            $objectAcademic = AcademicDBAccess::findGradeFromId($academicId);
        }
        if ($gradeId)
        {
            $objectAcademic = AcademicDBAccess::findGradeFromId($gradeId);
        }

        $isTutor = false;
        $used_in_class = 0;
        if (isset($objectAcademic))
        {
            switch ($objectAcademic->OBJECT_TYPE)
            {
                case "CLASS":
                    $academicId = $objectAcademic->ID;
                    $gradeId = $objectAcademic->GRADE_ID;
                    $schoolyearId = $objectAcademic->SCHOOL_YEAR;
                    $used_in_class = 1;
                    break;
                case "SUBCLASS":
                    $academicId = $objectAcademic->PARENT;
                    $gradeId = $objectAcademic->GRADE_ID;
                    $schoolyearId = $objectAcademic->SCHOOL_YEAR;
                    $used_in_class = 1;
                    break;
                case "SCHOOLYEAR":
                    $used_in_class = 0;
                    $gradeId = $objectAcademic->GRADE_ID;
                    $schoolyearId = $objectAcademic->SCHOOL_YEAR;
                    break;
            }
        }

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " B.ID AS ID, A.SHORT AS SHORT, A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.SHORT AS SUBJECT_SHORT";
        $SQL .= " ,A.GUID AS GUID";
        $SQL .= " ,B.SCORE_TYPE AS SCORE_TYPE";
        $SQL .= " ,B.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,B.EVALUATION_TYPE AS EVALUATION_TYPE";
        $SQL .= " ,B.COEFF AS COEFF";
        $SQL .= " ,B.MAX_POSSIBLE_SCORE AS MAX_POSSIBLE_SCORE";
        $SQL .= " ,B.INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION";
        $SQL .= " ,B.EDUCATION_TYPE AS EDUCATION_TYPE";
        $SQL .= " ,A.NAME AS NAME, A.STATUS AS STATUS";
        $SQL .= " ,B.DURATION AS DURATION";
        $SQL .= " ,B.ID AS SUBJECT_GRADE_ID";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_grade_subject AS B ON A.ID=B.SUBJECT";
        if ($isTutor)
        {
            $SQL .= " LEFT JOIN t_subject_teacher_class AS C ON A.ID=C.SUBJECT";
        }
        $SQL .= " WHERE 1=1";

        if ($subjectType)
        {
            $SQL .= " AND A.SUBJECT_TYPE='" . $subjectType . "'";
        }

        if ($subjectId)
        {
            $SQL .= " AND A.ID = '" . $subjectId . "'";
        }

        if ($gradeId)
        {
            $SQL .= " AND B.GRADE='" . $gradeId . "'";
        }

        if ($schoolyearId)
        {
            $SQL .= " AND B.SCHOOLYEAR='" . $schoolyearId . "'";
        }

        if ($academicId)
        {
            $SQL .= " AND B.CLASS='" . $academicId . "'";
        }

        $SQL .= " AND B.USED_IN_CLASS='" . $used_in_class . "'";

        if ($globalSearch)
        {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        if ($isTutor)
        {
            $SQL .= " AND C.TEACHER='" . Zend_Registry::get('USER')->ID . "'";
        }

        if ($include_in_evaluation)
            $SQL .= " AND B.INCLUDE_IN_EVALUATION ='" . $include_in_evaluation . "'";

        $SQL .= " GROUP BY A.ID";
        $SQL .= " ORDER BY A.NAME";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function updateSubjectGrade($params)
    {

        $SAVEDATA = array();
        $gradesubjectId = isset($params["gradesubjectId"]) ? addText($params["gradesubjectId"]) : "";

        if (isset($params["TEMPLATE"]))
            $SAVEDATA['TEMPLATE'] = addText($params["TEMPLATE"]);

        if (isset($params["MAX_POSSIBLE_SCORE"]))
            $SAVEDATA['MAX_POSSIBLE_SCORE'] = addText($params["MAX_POSSIBLE_SCORE"]);

        if (isset($params["SUBJECT_TYPE"]))
            $SAVEDATA['SUBJECT_TYPE'] = addText($params["SUBJECT_TYPE"]);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["NUMBER_SESSION"]))
            $SAVEDATA['NUMBER_SESSION'] = addText($params["NUMBER_SESSION"]);

        $SAVEDATA['COEFF'] = isset($params["COEFF"]) ? 1 : 0;

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        if (isset($params["AVERAGE_FROM_SEMESTER"]))
            $SAVEDATA['AVERAGE_FROM_SEMESTER'] = $params["AVERAGE_FROM_SEMESTER"];

        if (isset($params["EVALUATION"]))
            $SAVEDATA["EVALUATION"] = addText($params["EVALUATION"]);

        if (isset($params["MATERIALS"]))
            $SAVEDATA["MATERIALS"] = addText($params["MATERIALS"]);

        if (isset($params["OBJECTIVES"]))
            $SAVEDATA["OBJECTIVES"] = addText($params["OBJECTIVES"]);

        if (isset($params["EVALUATION_TYPE"]))
            $SAVEDATA["EVALUATION_TYPE"] = (int) $params["EVALUATION_TYPE"];

        if (isset($params["CHOOSE_ASSIGNED_SUBJECT"]))
            $SAVEDATA["ASSIGNED_SUBJECT"] = addText($params["CHOOSE_ASSIGNED_SUBJECT"]);

        if (isset($params["EDUCATION_SYSTEM"]))
            $SAVEDATA["EDUCATION_SYSTEM"] = addText($params["EDUCATION_SYSTEM"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if (isset($params["DEPARTMENT"]))
            $SAVEDATA["DEPARTMENT"] = addText($params["DEPARTMENT"]);

        $SAVEDATA['NATIONAL_EXAM'] = isset($params["NATIONAL_EXAM"]) ? 1 : 0;

        $SAVEDATA['INCLUDE_IN_EVALUATION'] = isset($params["INCLUDE_IN_EVALUATION"]) ? 1 : 0;

        if (isset($params["COEFF_VALUE"]))
        {
            $SAVEDATA['COEFF_VALUE'] =  addText($params["COEFF_VALUE"]);
        }

        $SAVEDATA['FORMULA_TYPE'] = addText($params["FORMULA_TYPE"]);
        $SAVEDATA['SCORE_TYPE'] = isset($params["SCORE_TYPE"]) ? $params["SCORE_TYPE"] : 1;

        if (isset($params["SCORE_MIN"]))
            $SAVEDATA['SCORE_MIN'] = addText($params["SCORE_MIN"]);

        if (isset($params["SCORE_MAX"]))
        {
            $SAVEDATA['SCORE_MAX'] = addText($params["SCORE_MAX"]);
            $SAVEDATA['MAX_POSSIBLE_SCORE'] = addText($params["MAX_POSSIBLE_SCORE"]);
        }

        $WHERE = array();
        $WHERE[] = self::dbAccess()->quoteInto('ID = ?', $gradesubjectId);
        self::dbAccess()->update('t_grade_subject', $SAVEDATA, $WHERE);
        return array("success" => true);
    }

    public function SubjectByGradeCombo()
    {

        $data = array();

        $utiles = Utiles::getInstance();
        $gradeId = $utiles->getValueRegistry("GRADE_ID");
        $schoolyearId = $utiles->getValueRegistry("SCHOOLYEAR_ID");

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " B.ID AS ID, A.SHORT AS SHORT";
        $SQL .= " ,A.NAME AS NAME, B.STATUS AS STATUS";
        $SQL .= " ,A.MAX_POSSIBLE_SCORE AS MAX_POSSIBLE_SCORE";
        $SQL .= " ,A.COEFF AS COEFF";
        $SQL .= " ,B.DURATION AS DURATION";
        $SQL .= " ,B.INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION";
        $SQL .= " ,A.ID AS SUBJECT_ID";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_grade_subject AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND GRADE='" . $gradeId . "'";
        $SQL .= " AND SCHOOLYEAR='" . $schoolyearId . "'";
        $SQL .= " GROUP BY A.ID";
        $SQL .= " ORDER BY A.NAME";

        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
            foreach ($result as $value)
            {
                $data[] = "[\"$value->SUBJECT_ID\",\"$value->NAME\"]";
            }

        return "[" . implode(",", $data) . "]";
    }

    protected static function checkSubjectINGrade($subjectId, $gradeId, $schoolyearId, $academicId = false)
    {

        $result = self::getGradeSubject(false, $gradeId, $subjectId, $schoolyearId, $academicId);
        return $result ? true : false;
    }

    protected static function addSubjectINGrade($subjectId, $gradeId, $schoolyearId, $academicId, $usedInClass = false)
    {

        $SAVEDATA = array();

        $facette = SubjectDBAccess::findSubjectFromId($subjectId);

        if ($facette)
        {

            if ($facette->SUBJECT_TYPE)
                $SAVEDATA['SUBJECT_TYPE'] = $facette->SUBJECT_TYPE;

            if ($facette->EDUCATION_TYPE)
                $SAVEDATA['EDUCATION_TYPE'] = $facette->EDUCATION_TYPE;

            if ($facette->NUMBER_SESSION)
                $SAVEDATA['NUMBER_SESSION'] = $facette->NUMBER_SESSION;

            if ($facette->TEMPLATE)
                $SAVEDATA['TEMPLATE'] = $facette->TEMPLATE;

            if ($facette->FORMULA_TYPE)
                $SAVEDATA['FORMULA_TYPE'] = $facette->FORMULA_TYPE;

            if ($facette->COEFF_VALUE)
            {
                $SAVEDATA['COEFF_VALUE'] = $facette->COEFF_VALUE;
            }
            else
            {
                $SAVEDATA['COEFF_VALUE'] = 1;
            }

            if ($facette->SCORE_TYPE)
                $SAVEDATA['SCORE_TYPE'] = $facette->SCORE_TYPE;

            if ($facette->MAX_POSSIBLE_SCORE)
                $SAVEDATA['MAX_POSSIBLE_SCORE'] = $facette->MAX_POSSIBLE_SCORE;

            if ($facette->SCORE_MIN)
                $SAVEDATA['SCORE_MIN'] = $facette->SCORE_MIN;

            if ($facette->SCORE_MAX)
            {
                $SAVEDATA['SCORE_MAX'] = $facette->SCORE_MAX;
                $SAVEDATA['MAX_POSSIBLE_SCORE'] = $facette->MAX_POSSIBLE_SCORE;
            }

            if ($facette->DEPARTMENT)
                $SAVEDATA['DEPARTMENT'] = $facette->DEPARTMENT;

            if ($facette->EDUCATION_SYSTEM)
                $SAVEDATA['EDUCATION_SYSTEM'] = $facette->EDUCATION_SYSTEM;

            if ($facette->INCLUDE_IN_EVALUATION)
                $SAVEDATA['INCLUDE_IN_EVALUATION'] = $facette->INCLUDE_IN_EVALUATION;

            $SAVEDATA['SUBJECT_GUID'] = generateGuid();
            $SAVEDATA['SUBJECT'] = $subjectId;
            $SAVEDATA['GRADE'] = $gradeId;
            $SAVEDATA['SCHOOLYEAR'] = $schoolyearId;

            if ($academicId)
            {
                $SAVEDATA['CLASS'] = $academicId;
            }
            else
            {
                $SAVEDATA['CLASS'] = 0;
            }

            if ($usedInClass)
            {
                $SAVEDATA['USED_IN_CLASS'] = 1;
            }
            else
            {
                $SAVEDATA['USED_IN_CLASS'] = 0;
            }

            self::dbAccess()->insert('t_grade_subject', $SAVEDATA);
        }
    }

    public static function removeSubjectGrade($gradesubjectId)
    {

        $facette = self::getGradeSubject($gradesubjectId, false, false, false);

        if ($facette)
        {

            self::dbAccess()->delete('t_grade_subject', array("ID='" . $facette->ID . "'"));

            $where = array();
            if ($facette->SUBJECT)
                $where[] = self::dbAccess()->quoteInto('SUBJECT = ?', $facette->SUBJECT);
            if ($facette->CLASS)
                $where[] = self::dbAccess()->quoteInto('CLASS = ?', $facette->CLASS);
            if ($facette->GRADE)
                $where[] = self::dbAccess()->quoteInto('GRADE = ?', $facette->GRADE);
            if ($facette->SCHOOLYEAR)
                $where[] = self::dbAccess()->quoteInto('SCHOOLYEAR = ?', $facette->SCHOOLYEAR);
            self::dbAccess()->delete('t_assignment', $where);

            ////////////////////////////////////////////////////////////////////
            //delete in schedule...
            self::dbAccess()->delete('t_schedule', array(
                "ACADEMIC_ID = '" . $facette->CLASS . "'"
                , "SUBJECT_ID = '" . $facette->SUBJECT . "'"
            ));
            ////////////////////////////////////////////////////////////////////
            //delete in subject teacher class...
            ////////////////////////////////////////////////////////////////////
            self::dbAccess()->delete('t_subject_teacher_class', array(
                "ACADEMIC = '" . $facette->CLASS . "'"
                , "SUBJECT = '" . $facette->SUBJECT . "'"
                , "GRADE = '" . $facette->GRADE . "'"
            ));
            ////////////////////////////////////////////////////////////////////
            //delete in t_student_assignment...
            ////////////////////////////////////////////////////////////////////
            self::dbAccess()->delete('t_student_assignment', array(
                "CLASS_ID = '" . $facette->CLASS . "'"
                , "SUBJECT_ID = '" . $facette->SUBJECT . "'"
            ));
            ////////////////////////////////////////////////////////////////////
            //delete in t_student_subject_assessment...
            ////////////////////////////////////////////////////////////////////
            self::dbAccess()->delete('t_student_subject_assessment', array(
                "CLASS_ID = '" . $facette->CLASS . "'"
                , "SUBJECT_ID = '" . $facette->SUBJECT . "'"
            ));
            ////////////////////////////////////////////////////////////////////
        }

        return array("success" => true);
    }

    protected function loadActionGradeSubjectQuery($params)
    {

        $Id = isset($params["id"]) ? addText($params["id"]) : "";
        $result = self::getGradeSubject($Id, false, false, false);
        return $result;
    }

    public static function getGradeSubject($Id, $gradeId, $subjectId, $schoolyearId, $academicId = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));

        if ($Id)
        {
            $SQL->where("ID='" . $Id . "'");
        }
        else
        {
            if ($subjectId)
                $SQL->where("SUBJECT='" . $subjectId . "'");
            if ($gradeId)
                $SQL->where("GRADE='" . $gradeId . "'");
            if ($schoolyearId)
                $SQL->where("SCHOOLYEAR='" . $schoolyearId . "'");
            if ($academicId)
            {
                $SQL->where("CLASS='" . $academicId . "'");
                $SQL->where("USED_IN_CLASS='1'");
            }
            else
            {
                $SQL->where("USED_IN_CLASS='0'");
            }
        }

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    /* soda */

    public static function getGradeSubjectTeacherCount($subjectId, $teacher, $academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_subject_teacher_content', 'COUNT(*) AS C');
        if ($subjectId)
            $SQL->where("SUBJECT = '" . $subjectId . "'");
        if ($teacher)
            $SQL->where("TEACHER = '" . $teacher . "'");
        if ($academicId)
            $SQL->where("ACADEMIC = '" . $academicId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        return $result ? $result->C : 0;
    }

    public static function getGradeSubjectYear($subjectId, $academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        $SQL->where("SUBJECT='" . $subjectId . "'");
        $SQL->where("CLASS='" . $academicId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getGradeSubjectTeacher($subjectId, $teacher, $academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_subject_teacher_content', array('*'));
        if ($subjectId)
            $SQL->where("SUBJECT='" . $subjectId . "'");
        if ($teacher)
            $SQL->where("TEACHER='" . $teacher . "'");
        if ($academicId)
            $SQL->where("ACADEMIC='" . $academicId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getGradeSubjectClass($subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        $SQL->where("SUBJECT='" . $subjectId . "'");
        $SQL->where("CLASS='0'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    /* end */

    public function loadActionGradeSubject($params)
    {

        $result = $this->loadActionGradeSubjectQuery($params);

        return array(
            "success" => true,
            "STATUS_KEY" => iconStatus($result->STATUS),
            "DURATION" => $result->DURATION,
            "STATUS" => $result->STATUS
        );
    }

    public static function addSubject2Grade($selectionIds, $academicId)
    {

        if (substr($selectionIds, 8))
        {
            $selectionIds = str_replace('CAMEMIS_', '', $selectionIds);
        }

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {

            switch ($academicObject->OBJECT_TYPE)
            {
                case "SCHOOLYEAR":
                    $classId = 0;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $used_in_class = 0;
                    break;
                case "CLASS":
                    $classId = $academicObject->ID;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    $used_in_class = 1;
                    break;
            }
        }
        else
        {
            exit("No academicObject....");
        }

        if ($selectionIds != "")
        {
            $selectedSubjects = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($selectedSubjects)
                foreach ($selectedSubjects as $subjectId)
                {

                    if (!self::checkSubjectINGrade($subjectId, $gradeId, $schoolyearId, $classId))
                    {
                        self::addSubjectINGrade($subjectId, $gradeId, $schoolyearId, $classId);
                        $selectedCount++;
                    }
                    else
                    {
                        $selectedCount = 0;
                    }
                }
        }
        else
        {
            $selectedCount = 0;
        }

        return $selectedCount;
    }

    public function jsonAddSubjectToGrade($params)
    {

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($subjectId && $academicObject)
        {

            switch ($academicObject->OBJECT_TYPE)
            {
                case "SCHOOLYEAR":
                    if (!self::checkSubjectINGrade(
                                    $subjectId
                                    , $academicObject->GRADE_ID
                                    , $academicObject->SCHOOL_YEAR
                                    , false)
                    )
                    {
                        self::addSubjectINGrade(
                                $subjectId
                                , $academicObject->GRADE_ID
                                , $academicObject->SCHOOL_YEAR
                                , false
                                , false
                        );
                    }
                    break;
                case "CLASS":
                    if (!self::checkSubjectINGrade(
                                    $subjectId
                                    , $academicObject->GRADE_ID
                                    , $academicObject->SCHOOL_YEAR
                                    , $academicObject->ID)
                    )
                    {
                        self::addSubjectINGrade(
                                $subjectId
                                , $academicObject->GRADE_ID
                                , $academicObject->SCHOOL_YEAR
                                , $academicObject->ID
                                , true
                        );
                    }
                    break;
            }
        }

        return array("success" => true);
    }

    public function treeSubjectsByGrade($params)
    {

        $data = array();

        $result = self::sqlAssignedSubjectsByGrade($params);

        if ($result)
            foreach ($result as $key => $value)
            {

                $data[$key]['id'] = "" . $value->SUBJECT_ID . "";
                $data[$key]['text'] = "(" . setShowText($value->SHORT) . ") " . setShowText($value->NAME);
                $data[$key]['onlyText'] = setShowText($value->NAME);
                $data[$key]['cls'] = "nodeTextBold";

                switch ($value->INCLUDE_IN_EVALUATION)
                {
                    case 1:
                        $data[$i]['iconCls'] = "icon-flag_blue";
                        break;
                    case 2:
                        $data[$i]['iconCls'] = "icon-flag_red";
                        break;
                    default:
                        $data[$i]['iconCls'] = "icon-flag_white";
                        break;
                }

                $data[$key]['leaf'] = true;
            }

        return $data;
    }

    public static function jsonTreeAcademicSubjectAssignment($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        if (strpos($node, 'SUBJECT') !== false)
        {
            $subjectId = substr($params["node"], 8);
        }
        else
        {
            $subjectId = 0;
        }

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        if ($academicObject)
        {
            switch ($academicObject->OBJECT_TYPE)
            {
                case "SCHOOLYEAR":
                    $used_in_class = 0;
                    $academicId = 0;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
                case "CLASS":
                    $used_in_class = 1;
                    $academicId = $academicObject->ID;
                    $gradeId = $academicObject->GRADE_ID;
                    $schoolyearId = $academicObject->SCHOOL_YEAR;
                    break;
            }

            if ($node == 0 && !$subjectId)
            {
                $SELECTION_A = array(
                    'ID AS SUBJECT_ID'
                    , 'SHORT AS SHORT'
                    , 'NAME AS NAME'
                );

                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => 't_subject'), $SELECTION_A);
                $SQL->joinLeft(array('B' => 't_grade_subject'), 'A.ID=B.SUBJECT', array('ID AS GRADESUBJECT_ID', 'CLASS AS CLASS_ID'));

                if ($academicId)
                {
                    $SQL->where("B.CLASS='" . $academicId . "'");
                }
                else
                {
                    $SQL->where("B.CLASS='0'");
                }

                $SQL->where("B.USED_IN_CLASS='" . $used_in_class . "'");

                if ($gradeId)
                    $SQL->where("B.GRADE='" . $gradeId . "'");
                if ($schoolyearId)
                    $SQL->where("B.SCHOOLYEAR='" . $schoolyearId . "'");

                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchAll($SQL);
            }else
            {
                $SQL = self::dbAccess()->select();
                $SQL->from(array('A' => 't_assignment'), array(
                    'SUBJECT AS SUBJECT_ID'
                    , 'ID AS ASSSIGNMENT_ID'
                    , 'NAME AS ASSIGNMENT_NAME'
                    , 'EVALUATION_TYPE'
                    , 'COEFF_VALUE'
                    , 'INCLUDE_IN_EVALUATION')
                );
                $SQL->where("A.SUBJECT='" . $subjectId . "'");

                if ($academicId)
                {
                    $SQL->where("A.CLASS='" . $academicId . "'");
                }
                else
                {
                    $SQL->where("A.CLASS='0'");
                }

                $SQL->where("A.USED_IN_CLASS='" . $used_in_class . "'");

                if ($gradeId)
                    $SQL->where("A.GRADE='" . $gradeId . "'");
                if ($schoolyearId)
                    $SQL->where("A.SCHOOLYEAR='" . $schoolyearId . "'");
                $SQL->order("A.SORTKEY ASC");
                $result = self::dbAccess()->fetchAll($SQL);
                //error_log($SQL->__toString());
            }

            $data = array();

            if ($result)
            {
                $i = 0;
                foreach ($result as $value)
                {

                    if ($node == 0 && !$subjectId)
                    {
                        $data[$i]['id'] = "SUBJECT_" . $value->SUBJECT_ID . "";
                        $data[$i]['subjectId'] = "" . $value->SUBJECT_ID . "";
                        $data[$i]['gradeSubjectId'] = "" . $value->GRADESUBJECT_ID . "";
                        if ($value->SHORT)
                        {
                            $data[$i]['text'] = "(" . setShowText($value->SHORT) . ") " . setShowText($value->NAME);
                        }
                        else
                        {
                            $data[$i]['text'] = setShowText($value->NAME);
                        }

                        $data[$i]['leaf'] = false;
                        $data[$i]['cls'] = "nodeTextBoldBlue";
                        $data[$i]['iconCls'] = "icon-star";
                    }
                    else
                    {

                        $data[$i]['id'] = "ASSSIGNMENT_" . $value->ASSSIGNMENT_ID . "";
                        $data[$i]['subjectId'] = "" . $value->SUBJECT_ID . "";
                        $data[$i]['assignmentId'] = "" . $value->ASSSIGNMENT_ID . "";
                        $data[$i]['leaf'] = true;
                        $data[$i]['cls'] = "nodeTextBlue";

                        if ($value->EVALUATION_TYPE)
                        {
                            $data[$i]['text'] = setShowText($value->ASSIGNMENT_NAME) . " (" . $value->COEFF_VALUE . "%)";
                        }
                        else
                        {
                            $data[$i]['text'] = setShowText($value->ASSIGNMENT_NAME) . " (" . $value->COEFF_VALUE . ")";
                        }

                        switch ($value->INCLUDE_IN_EVALUATION)
                        {
                            case 1:
                                $data[$i]['iconCls'] = "icon-flag_blue";
                                break;
                            case 2:
                                $data[$i]['iconCls'] = "icon-flag_red";
                                break;
                            default:
                                $data[$i]['iconCls'] = "icon-flag_white";
                                break;
                        }
                    }
                    if (isset($value->CLASS_ID))
                        $data[$i]['classId'] = "" . $value->CLASS_ID . "";

                    $i++;
                }
            }
        }

        return $data;
    }

    public static function getCountSubjectByClass_Term($academicId, $subjectId, $term)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule', 'COUNT(*) AS C');
        $SQL->where("ACADEMIC_ID = '" . $academicId . "'");
        $SQL->where("SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("TERM = '" . $term . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonAllSubjectByClass($params)
    {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "0";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        $searchParams["academicId"] = $academicObject->ID;
        $entries = self::sqlAssignedSubjectsByGrade($searchParams);

        $data = array();
        $a = array();

        $TERM_NUMBER = AcademicDBAccess::findAcademicTerm(false, $academicId);

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["SUBJECT_SHORT"] = $value->SUBJECT_SHORT;
                $data[$i]["SUBJECT_NAME"] = $value->SUBJECT_NAME;

                switch ($TERM_NUMBER)
                {
                    case 1:
                        $data[$i]["FIRST_TERM"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "FIRST_TERM");
                        $data[$i]["SECOND_TERM"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "SECOND_TERM");
                        $data[$i]["THIRD_TERM"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "THIRD_TERM");
                        break;
                    case 2:
                        $data[$i]["FIRST_QUARTER"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "FIRST_QUARTER");
                        $data[$i]["SECOND_QUARTER"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "SECOND_QUARTER");
                        $data[$i]["THIRD_QUARTER"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "THIRD_QUARTER");
                        $data[$i]["FOURTH_QUARTER"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "FOURTH_QUARTER");
                        break;
                    default:
                        $data[$i]["FIRST_SEMESTER"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "FIRST_SEMESTER");
                        $data[$i]["SECOND_SEMESTER"] = self::getCountSubjectByClass_Term($academicId, $value->SUBJECT_ID, "FIRST_SEMESTER");
                        break;
                }

                $i++;
            }

            for ($i = $start; $i < $start + $limit; $i++)
            {
                if (isset($data[$i]))
                    $a[] = $data[$i];
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

    public function checkAssignmentBySubject($subjectId, $gradeId, $academicId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_assignment', 'COUNT(*) AS C');
        $SQL->where("SUBJECT = '" . $subjectId . "'");
        if ($gradeId)
            $SQL->where("GRADE = '" . $gradeId . "'");
        if ($academicId)
            $SQL->where("CLASS = '" . $academicId . "'");
        $SQL->where("SCHOOLYEAR = '" . $schoolyearId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonStudentExemptions($params)
    {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "0";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "0";

        $SQL = "SELECT  
		A.ID AS SUBJECT_ID
		, A.NAME AS SUBJECT_NAME
		, CONCAT(D.LASTNAME,' ', D.FIRSTNAME, ' (', D.CODE,')') AS STUDENT_NAME
		, C.TERM AS TERM
		, C.STUDENT_ID AS STUDENT_ID
		, C.CLASS_ID AS CLASS_ID
		, E.NAME AS CLASS_NAME
		";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " RIGHT JOIN t_grade_subject AS B ON A.ID=B.SUBJECT";
        $SQL .= " RIGHT JOIN t_student_exemption_subject AS C ON A.ID=C.SUBJECT_ID";
        $SQL .= " RIGHT JOIN t_student AS D ON C.STUDENT_ID=D.ID";
        $SQL .= " RIGHT JOIN t_grade AS E ON C.CLASS_ID=E.ID";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SCHOOLYEAR ='" . $schoolyearId . "'";
        $SQL .= " AND GRADE ='" . $gradeId . "'";

        $resultRows = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($resultRows)
        {

            $i = 0;
            foreach ($resultRows as $value)
            {

                $data[$i]["ID"] = $value->SUBJECT_ID;
                $data[$i]["SUBJECT_NAME"] = $value->SUBJECT_NAME;
                $data[$i]["STUDENT"] = $value->STUDENT_NAME;
                $data[$i]["CLASS_NAME"] = $value->CLASS_NAME;

                switch ($value->TERM)
                {
                    case "FIRST_SEMESTER":
                        $data[$i]["TERM"] = FIRST_SEMESTER;
                        break;
                    case "SECOND_SEMESTER":
                        $data[$i]["TERM"] = SECOND_SEMESTER;
                        break;
                }

                $i++;
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

    public function mappingAllSubjectGrade($gradeId, $schoolyearId)
    {

        $SAVEDATA = array();

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        if ($gradeId)
            $SQL->where("GRADE='" . $gradeId . "'");
        if ($schoolyearId)
            $SQL->where("SCHOOLYEAR='" . $schoolyearId . "'");
        //echo $SQL->__toString();
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result)
        {
            foreach ($result as $value)
            {
                $facette = SubjectDBAccess::findSubjectFromId($value->SUBJECT);

                if ($facette)
                {

                    if (!$value->USED_IN_CLASS)
                    {
                        $SQL = "UPDATE t_grade_subject";
                        $SQL .= " SET";
                        $SQL .= " SUBJECT_TYPE ='" . $facette->SUBJECT_TYPE . "'";
                        $SQL .= " ,INCLUDE_IN_EVALUATION ='" . $facette->INCLUDE_IN_EVALUATION . "'";
                        $SQL .= " ,EDUCATION_TYPE ='" . $facette->EDUCATION_TYPE . "'";
                        $SQL .= " ,EDUCATION_SYSTEM ='" . $facette->EDUCATION_SYSTEM . "'";
                        $SQL .= " ,DEPARTMENT ='" . $facette->DEPARTMENT . "'";
                        $SQL .= " ,EVALUATION_TYPE ='" . $facette->EVALUATION_TYPE . "'";
                        $SQL .= " ,DEPARTMENT ='" . $facette->DEPARTMENT . "'";
                        $SQL .= " ,NUMBER_SESSION ='" . $facette->NUMBER_SESSION . "'";
                        $SQL .= " ,NUMBER_CREDIT ='" . $facette->NUMBER_CREDIT . "'";
                        $SQL .= " ,NATIONAL_EXAM ='" . $facette->NATIONAL_EXAM . "'";
                        $SQL .= " ,SCORE_TYPE ='" . $facette->SCORE_TYPE . "'";
                        $SQL .= " ,TEMPLATE ='" . $facette->TEMPLATE . "'";
                        $SQL .= " ,TRAINING ='" . $facette->TRAINING . "'";
                        $SQL .= " ,MAX_POSSIBLE_SCORE ='" . $facette->MAX_POSSIBLE_SCORE . "'";
                        $SQL .= " ,SCORE_MIN ='" . $facette->SCORE_MIN . "'";
                        $SQL .= " ,SCORE_MAX ='" . $facette->SCORE_MAX . "'";
                        $SQL .= " ,COEFF_VALUE ='" . $facette->COEFF_VALUE . "'";
                        $SQL .= " ,FORMULA_TYPE ='" . $facette->FORMULA_TYPE . "'";
                        $SQL .= " ,WHERE";
                        $SQL .= " SUBJECT='" . $value->SUBJECT . "'";
                        $SQL .= " AND GRADE='" . $gradeId . "'";
                        $SQL .= " AND SCHOOLYEAR='" . $schoolyearId . "'";

                        self::dbAccess()->query($SAVEDATA);
                    }
                }
            }
        }
    }

    public static function checkAssignedSubjectByClass($academicId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', 'COUNT(*) AS C');
        $SQL->where("CLASS = '" . $academicId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function copySubjectAssingmentToClass($academicId, $copyFrom)
    {

        $SAVEDATA = array();
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {

            $DELETE_SUBJECT = "DELETE FROM t_grade_subject WHERE CLASS = '" . $academicObject->ID . "'";
            self::dbAccess()->query($DELETE_SUBJECT);

            $DELETE_ASSIGNMENT = "DELETE FROM t_assignment WHERE CLASS = '" . $academicObject->ID . "'";
            self::dbAccess()->query($DELETE_ASSIGNMENT);

            if ($copyFrom == "schoolyear")
            {

                $USED_IN_CLASS = 0;

                $SQL = self::dbAccess()->select();
                $SQL->from('t_grade_subject', array('*'));
                $SQL->where("GRADE='" . $academicObject->GRADE_ID . "'");
                $SQL->where("SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'");
                $SQL->where("USED_IN_CLASS='0'");
                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchAll($SQL);
            }
            else
            {

                $USED_IN_CLASS = 1;
                $SQL = self::dbAccess()->select();
                $SQL->from('t_grade_subject', array('*'));
                $SQL->where("CLASS='" . $copyFrom . "'");
                $SQL->where("GRADE='" . $academicObject->GRADE_ID . "'");
                $SQL->where("SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'");
                $SQL->where("USED_IN_CLASS='1'");
                //error_log($SQL->__toString());
                $result = self::dbAccess()->fetchAll($SQL);
            }

            if (!self::checkAssignedSubjectByClass($academicObject->ID))
            {
                if ($result)
                {
                    foreach ($result as $value)
                    {

                        $SAVEDATA['SUBJECT_TYPE'] = $value->SUBJECT_TYPE;
                        $SAVEDATA['INCLUDE_IN_EVALUATION'] = $value->INCLUDE_IN_EVALUATION;
                        $SAVEDATA['EDUCATION_TYPE'] = $value->EDUCATION_TYPE;
                        $SAVEDATA['NUMBER_SESSION'] = $value->NUMBER_SESSION;
                        $SAVEDATA['NUMBER_CREDIT'] = $value->NUMBER_CREDIT;
                        $SAVEDATA['NATIONAL_EXAM'] = $value->NATIONAL_EXAM;
                        $SAVEDATA['TEMPLATE'] = $value->TEMPLATE;
                        $SAVEDATA['FORMULA_TYPE'] = $value->FORMULA_TYPE;
                        $SAVEDATA['COEFF_VALUE'] = $value->COEFF_VALUE;
                        $SAVEDATA['SCORE_TYPE'] = $value->SCORE_TYPE;
                        $SAVEDATA['MAX_POSSIBLE_SCORE'] = $value->MAX_POSSIBLE_SCORE;
                        $SAVEDATA['SCORE_MIN'] = $value->SCORE_MIN;
                        $SAVEDATA['SCORE_MAX'] = $value->SCORE_MAX;
                        $SAVEDATA['CLASS'] = $academicObject->ID;
                        $SAVEDATA['SUBJECT_GUID'] = generateGuid();
                        $SAVEDATA['SUBJECT'] = $value->SUBJECT;
                        $SAVEDATA['GRADE'] = $value->GRADE;
                        $SAVEDATA['SCHOOLYEAR'] = $value->SCHOOLYEAR;
                        $SAVEDATA['USED_IN_CLASS'] = 1;
                        $SAVEDATA['EDUCATION_SYSTEM'] = $academicObject->QUALIFICATION_TYPE;
                        $SAVEDATA['DEPARTMENT'] = $value->DEPARTMENT;

                        $SAVEDATA['GOALS'] = $value->GOALS;
                        $SAVEDATA['OBJECTIVES'] = $value->OBJECTIVES;
                        $SAVEDATA['MATERIALS'] = $value->MATERIALS;

                        self::dbAccess()->insert('t_grade_subject', $SAVEDATA);

                        self::copyAssignmentToSubjctClass(
                                $value->SUBJECT
                                , $academicObject->ID
                                , $value->GRADE
                                , $value->SCHOOLYEAR
                                , $copyFrom
                        );
                    }
                }
            }
        }
    }

    public static function checkUsedAssignment($academicObject, $subjectId, $assignmentId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_assignment', 'COUNT(*) AS C');
        $SQL->where("TEMP_ID = '" . $assignmentId . "'");

        switch ($academicObject->OBJECT_TYPE)
        {
            case "SCHOOLYEAR":
                $SQL->where("GRADE = '" . $academicObject->GRADE_ID . "'");
                $SQL->where("SCHOOLYEAR = '" . $academicObject->SCHOOL_YEAR . "'");
                $SQL->where("USED_IN_CLASS = '0'");
                break;
            case "CLASS":
            case "SUBJECT":
                $SQL->where("CLASS = '" . $academicObject->ID . "'");
                $SQL->where("USED_IN_CLASS = '1'");
                break;
        }

        $SQL->where("SUBJECT = '" . $subjectId . "'");
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function copyAssignmentToSubjctClass($subjectId, $academicId, $gradeId, $schoolyearId, $copyFromId)
    {

        $SAVEDATA = array();
        $USED_IN_CLASS = 0;
        //error_log("ssss " . $copyFromId);
        //Copy from classId...
        if ($copyFromId == "schoolyear")
        {
            $USED_IN_CLASS = 0;
            $SQL = self::dbAccess()->select();
            $SQL->from("t_assignment", array('*'));
            $SQL->where("SUBJECT = '" . $subjectId . "'");
            $SQL->where("GRADE = '" . $gradeId . "'");
            $SQL->where("CLASS = '0'");
            $SQL->where("SCHOOLYEAR = '" . $schoolyearId . "'");
            $SQL->where("USED_IN_CLASS = '0'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);

            //Copy from Schoolyear...
        }
        elseif ($copyFromId > 0)
        {
            $USED_IN_CLASS = 1;
            $SQL = self::dbAccess()->select();
            $SQL->from("t_assignment", array('*'));
            $SQL->where("SUBJECT = '" . $subjectId . "'");
            $SQL->where("GRADE = '" . $gradeId . "'");
            $SQL->where("CLASS = '" . $copyFromId . "'");
            $SQL->where("SCHOOLYEAR = '" . $schoolyearId . "'");
            $SQL->where("USED_IN_CLASS = '1'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
        }

        foreach ($result as $value)
        {

            $SAVEDATA['SORTKEY'] = $value->SORTKEY;
            $SAVEDATA['NAME'] = $value->NAME;
            $SAVEDATA['STATUS'] = $value->STATUS;
            $SAVEDATA['SHORT'] = $value->SHORT;
            $SAVEDATA['GRADE'] = $value->GRADE;
            $SAVEDATA['CLASS'] = $academicId;
            $SAVEDATA['SUBJECT'] = $value->SUBJECT;
            $SAVEDATA['TASK'] = $value->TASK;
            $SAVEDATA['SCHOOLYEAR'] = $value->SCHOOLYEAR;
            $SAVEDATA['TEMP_ID'] = $value->TEMP_ID;
            $SAVEDATA['EVALUATION_TYPE'] = $value->EVALUATION_TYPE;
            $SAVEDATA['SMS_SEND'] = $value->SMS_SEND;
            $SAVEDATA['DESCRIPTION'] = $value->DESCRIPTION;
            $SAVEDATA['USED_IN_CLASS'] = $USED_IN_CLASS;
            $SAVEDATA['INCLUDE_IN_EVALUATION'] = $value->INCLUDE_IN_EVALUATION;
            $SAVEDATA['COEFF_VALUE'] = $value->COEFF_VALUE;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert('t_assignment', $SAVEDATA);
        }

        return true;
    }

    public static function jsonUnassignedCourseBySubject($params)
    {

        $parentSubject = isset($params["parentSubject"]) ? $params["parentSubject"] : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $SQL = self::dbAccess()->select();
        $SQL->from('t_subject', array('*'));
        $SQL->where("PARENT='" . $parentSubject . "'");
        //error_log($SQL->__toString());
        $resultRows = self::dbAccess()->fetchAll($SQL);
        $data = array();

        if ($resultRows)
        {
            $i = 0;
            foreach ($resultRows as $value)
            {

                $CHECK = self::checkSubjectINGrade(
                                $value->ID
                                , $academicObject->GRADE_ID
                                , $academicObject->SCHOOL_YEAR
                                , false
                );

                if (!$CHECK)
                {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["SHORT"] = $value->SHORT;
                    $data[$i]["NAME"] = $value->NAME;
                    $i++;
                }
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function jsonAddCurseToSubject($params)
    {

        $selectionIds = isset($params["selectionIds"]) ? addText($params["selectionIds"]) : "";
        $parentSubject = isset($params["parentSubject"]) ? $params["parentSubject"] : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($selectionIds != "" && $academicObject)
        {
            $selectedSubjectIds = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($selectedSubjectIds)
                foreach ($selectedSubjectIds as $subjectId)
                {
                    $CHECK = self::checkSubjectINGrade(
                                    $subjectId
                                    , $academicObject->GRADE_ID
                                    , $academicObject->SCHOOL_YEAR
                                    , false
                    );

                    if (!$CHECK)
                    {
                        self::addSubjectINGrade(
                                $subjectId
                                , $academicObject->GRADE_ID
                                , $academicObject->SCHOOL_YEAR
                                , false
                                , $parentSubject
                        );
                        $selectedCount++;
                    }
                    else
                    {
                        $selectedCount = 0;
                    }
                }
        }
        else
        {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public static function setAutoSubject2GradeYearCreditSystem($academicId)
    {

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {
            $SQL = self::dbAccess()->select();
            $SQL->from('t_grade', array('SUBJECT_ID'));
            $SQL->where("OBJECT_TYPE='SUBJECT'");
            $SQL->where("GRADE_ID='" . $academicObject->GRADE_ID . "'");
            $SQL->where("SCHOOL_YEAR='" . $academicObject->SCHOOL_YEAR . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);

            if ($result)
            {
                foreach ($result as $value)
                {
                    $CHECK = self::checkSubjectINGrade(
                                    $value->SUBJECT_ID
                                    , $academicObject->GRADE_ID
                                    , $academicObject->SCHOOL_YEAR
                                    , false
                    );
                    if (!$CHECK)
                    {
                        self::addSubjectINGrade(
                                $value->SUBJECT_ID
                                , $academicObject->GRADE_ID
                                , $academicObject->SCHOOL_YEAR
                                , false
                        );
                    }
                }
            }
        }
    }

    public static function removeSubjectFromAcademic($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $gradesubjectId = isset($params["gradesubjectId"]) ? addText($params["gradesubjectId"]) : "";
        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        if ($academicObject)
        {
            $condition1 = array(
                "SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'"
                , "GRADE='" . $academicObject->GRADE_ID . "'"
                , "SUBJECT='" . $gradesubjectId . "'"
            );
            self::dbAccess()->delete('t_grade_subject', $condition1);

            $condition2 = array(
                "SCHOOLYEAR='" . $academicObject->SCHOOL_YEAR . "'"
                , "GRADE='" . $academicObject->GRADE_ID . "'"
                , "PARENT='" . $gradesubjectId . "'"
            );
            self::dbAccess()->delete('t_grade_subject', $condition2);
        }

        return array("success" => true);
    }

    public static function sqlAllSubjectsByClass($academicId)
    {

        $params["academicId"] = $academicId;
        return self::sqlAssignedSubjectsByGrade($params);
    }

    public static function sqlSubjectTeacherClass($academicId, $subjectId, $term)
    {

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        $SQL = "";
        $SQL .= " SELECT A.ID CLASS_ID, A.NAME AS CLASS_NAME";
        $SQL .= " FROM t_grade AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.ACADEMIC";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.CAMPUS ='" . $academicObject->CAMPUS_ID . "'";
        $SQL .= " AND B.GRADE ='" . $academicObject->GRADE_ID . "'";
        $SQL .= " AND B.SUBJECT ='" . $subjectId . "'";
        $SQL .= " AND B.GRADINGTERM ='" . $term . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    //@veasna
    public static function actionPreRequisite2GradeSubject($params)
    {

        $subjectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selecteds = isset($params["selecteds"]) ? addText($params["selecteds"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $schoolyear = isset($params["schoolyear"]) ? addText($params["schoolyear"]) : "";
        $facette = self::getGradeSubject(false, $academicId, $subjectId, $schoolyear, false);

        if ($facette)
        {
            $SAVEDATA['PRE_REQUISITE_COURSE'] = $selecteds;
            $WHERE[] = "ID = '" . $facette->ID . "'";
            self::dbAccess()->update('t_grade_subject', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    public static function getPreRequisiteByGradeSubject($params)
    {

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $schoolyear = isset($params["schoolyear"]) ? addText($params["schoolyear"]) : "";
        $subjectId = isset($params["requisiteId"]) ? addText($params["requisiteId"]) : "";

        $facette = self::getGradeSubject(false, $academicId, $subjectId, $schoolyear, false);
        $result = array();

        if ($facette)
        {
            if ($facette->PRE_REQUISITE_COURSE)
                $result = explode(",", $facette->PRE_REQUISITE_COURSE);
        }

        return $result;
    }

    /**
     * Get registered subject of each student
     * 
     * @author Math Man 22.01.2014
     */
    public static function getStudentCreditSubject($studentId, $schoolyearId)
    {
        $SELECTION_A = array(
            "ID AS ID"
            , "CAMPUS_ID AS CAMPUS_ID"
            , "CLASS_ID AS CLASS_ID"
            , "SCHOOLYEAR_ID AS SCHOOLYEAR_ID"
            , "TRANSFER AS TRANSFER"
            , "TRANSFER_DESCRIPTION AS TRANSFER_DESCRIPTION"
            , "CREDIT_STATUS AS STATUS"
        );
        $SELECTION_B = array(
            "ID AS SUBJECT_ID"
            , "GUID AS GUID"
            , "SHORT AS SUBJECT_SHORT"
            , "NAME AS SUBJECT_NAME"
            , "NUMBER_CREDIT AS SUBJECT_NUMBER_CREDIT"
            , "NUMBER_SESSION AS SUBJECT_NUMBER_SESSION"
        );
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear_subject'), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', $SELECTION_B);
        $SQL->where("A.STUDENT_ID = '" . $studentId . "'");
        $SQL->where("A.SCHOOLYEAR_ID = '" . $schoolyearId . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    /**
     * Rendering registered subject of each student
     * 
     * @author Math Man 22.01.2014
     */
    public static function jsonStudentSubjectCredit($params)
    {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";

        $results = self::getStudentCreditSubject($studentId, $schoolyearId);

        $data = array();
        $a = array();

        if ($results)
        {
            $i = 0;
            foreach ($results as $value)
            {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["SUBJECT_ID"] = $value->SUBJECT_ID;
                $data[$i]["CLASS_ID"] = $value->CLASS_ID;
                $data[$i]["SUBJECT_SHORT"] = $value->SUBJECT_SHORT;
                $data[$i]["SUBJECT_NAME"] = $value->SUBJECT_NAME;
                $data[$i]["CREDIT_NUMBER"] = $value->SUBJECT_NUMBER_CREDIT;
                $data[$i]["SESSION"] = $value->SUBJECT_NUMBER_SESSION;
                if ($value->STATUS == 0)
                    $data[$i]["STATUS"] = "Ongoing";
                else if ($value->STATUS == 1)
                    $data[$i]["STATUS"] = "incompleted";
                else
                    $data[$i]["STATUS"] = "Completed";
                $data[$i]["FIRST_SEMESTER"] = "---";
                $data[$i]["SECOND_SEMESTER"] = "---";
                $i++;
            }

            for ($i = $start; $i < $start + $limit; $i++)
            {
                if (isset($data[$i]))
                    $a[] = $data[$i];
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );
    }

}

?>