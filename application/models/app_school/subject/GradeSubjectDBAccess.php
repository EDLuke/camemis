<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
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
            $data["NUMBER_SESSION"] = $facette->NUMBER_SESSION;
            $data["NUMBER_CREDIT"] = $facette->NUMBER_CREDIT;
            $data["SUBJECT"] = $facette->SUBJECT;
            $data["SCORE_TYPE"] = $facette->SCORE_TYPE;
            $data["FORMULA_TYPE"] = $facette->FORMULA_TYPE ? $facette->FORMULA_TYPE : 1;
            $data["COEFF_VALUE"] = $facette->COEFF_VALUE ? $facette->COEFF_VALUE : 1;

            if ($facette->ASSIGNED_SUBJECT)
            {
                $assignedSubject = SubjectDBAccess::findSubjectFromId($facette->ASSIGNED_SUBJECT);
                $data["CHOOSE_ASSIGNED_SUBJECT_NAME"] = $assignedSubject->NAME;
            }

            $data["AVERAGE_FROM_SEMESTER"] = $facette->AVERAGE_FROM_SEMESTER;
            $data["COEFF"] = $facette->COEFF ? true : false;
            $data["COEFF_VALUE"] = $facette->COEFF_VALUE ? $facette->COEFF_VALUE : 1;
            $data["COLOR"] = $subjectObject->COLOR ? $subjectObject->COLOR : "#FFFFFF";
            $data["SUBJECT_TYPE"] = $facette->SUBJECT_TYPE;
            $data["EDUCATION_TYPE"] = $facette->EDUCATION_TYPE;
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

        $o = array(
            "success" => true
            , "data" => $data
        );

        return $o;
    }

    public function getSubjectsOfClass($classId)
    {
        $params = array();
        $params["academicId"] = $classId;
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
        $classId = isset($params["classId"]) ? (int) $params["classId"] : "";

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
        $nationalExam = isset($params["nationalExam"]) ? addText($params["nationalExam"]) : "";
        $subjectType = isset($params["subjectType"]) ? addText($params["subjectType"]) : "";
        $include_in_evaluation = isset($params["include_in_evaluation"]) ? (int) $params["include_in_evaluation"] : "0";

        $isTutor = false;
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        if ($classId)
        {
            $objectAcademic = AcademicDBAccess::findGradeFromId($classId);
        }
        elseif ($academicId)
        {
            $classId = 0;
            $objectAcademic = AcademicDBAccess::findGradeFromId($academicId);
        }

        $gradeId = 0;
        $schoolyearId = 0;
        $used_in_class = 0;

        if (isset($objectAcademic))
        {
            switch ($objectAcademic->OBJECT_TYPE)
            {
                case "CLASS":
                    $classId = $objectAcademic->ID;
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
                    $gradeId = $objectAcademic->GRADE_ID;
                    $schoolyearId = $objectAcademic->SCHOOL_YEAR;
                    $used_in_class = 0;
                    $classId = 0;
                    break;
            }
        }

        $SQL = "";
        $SQL .= " SELECT ";
        $SQL .= " B.ID AS ID, A.SHORT AS SHORT, A.ID AS SUBJECT_ID";
        $SQL .= " ,A.NAME AS SUBJECT_NAME";
        $SQL .= " ,A.NUMBER_SESSION AS NUMBER_SESSION";
        $SQL .= " ,A.SHORT AS SUBJECT_SHORT";
        $SQL .= " ,B.SUBJECT_TYPE AS SUBJECT_TYPE";
        $SQL .= " ,A.GUID AS GUID";
        $SQL .= " ,B.SCORE_TYPE AS SCORE_TYPE";
        $SQL .= " ,B.EVALUATION_TYPE AS EVALUATION_TYPE";
        $SQL .= " ,B.COEFF AS COEFF";
        $SQL .= " ,B.INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION";
        $SQL .= " ,B.MAX_POSSIBLE_SCORE AS MAX_POSSIBLE_SCORE";
        $SQL .= " ,B.EDUCATION_TYPE AS EDUCATION_TYPE";
        $SQL .= " ,A.NAME AS NAME, A.STATUS AS STATUS";
        $SQL .= " ,B.ID AS SUBJECT_GRADE_ID";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_grade_subject AS B ON A.ID=B.SUBJECT";
        if ($isTutor)
        {
            $SQL .= " LEFT JOIN t_subject_teacher_class AS C ON A.ID=C.SUBJECT";
        }
        $SQL .= " WHERE 1=1";
        //$SQL .= " AND A.STATUS=1";

        if ($subjectType)
        {
            $SQL .= " AND A.SUBJECT_TYPE='" . $subjectType . "'";
        }

        if ($include_in_evaluation)
            $SQL .= " AND B.INCLUDE_IN_EVALUATION ='" . $include_in_evaluation . "'";

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

        if ($classId)
        {
            $SQL .= " AND B.CLASS='" . $classId . "'";
        }
        else
        {
            $SQL .= " AND B.CLASS='0'";
        }

        if ($nationalExam)
        {
            $SQL .= " AND A.NATIONAL_EXAM = 1";
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

        $SQL .= " GROUP BY A.ID";
        $SQL .= " ORDER BY A.NAME";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function updateSubjectGrade($params)
    {

        $SAVEDATA = array();
        $gradesubjectId = isset($params["gradesubjectId"]) ? addText($params["gradesubjectId"]) : "";
        $gradeSubjectObject = self::getGradeSubject($gradesubjectId, false, false, false);
        if ($gradeSubjectObject)
        {
            $academicObject = AcademicDBAccess::findGradeFromId($gradeSubjectObject->GRADE);
        }

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

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        $SAVEDATA['COEFF'] = isset($params["COEFF"]) ? 1 : 0;

        if (isset($params["AVERAGE_FROM_SEMESTER"]))
            $SAVEDATA['AVERAGE_FROM_SEMESTER'] = $params["AVERAGE_FROM_SEMESTER"];

        if (isset($params["COEFF_VALUE"]))
            $SAVEDATA['COEFF_VALUE'] =  addText($params["COEFF_VALUE"]);

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

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if (isset($params["SCORE_MIN"]))
            $SAVEDATA['SCORE_MIN'] = addText($params["SCORE_MIN"]);

        if (isset($params["SCORE_MAX"]))
        {
            $SAVEDATA['SCORE_MAX'] = addText($params["SCORE_MAX"]);
            $SAVEDATA['MAX_POSSIBLE_SCORE'] = addText($params["MAX_POSSIBLE_SCORE"]);
        }

        $SAVEDATA['INCLUDE_IN_EVALUATION'] = isset($params["INCLUDE_IN_EVALUATION"]) ? 1 : 0;

        $SAVEDATA['USED_IN_CLASS'] = $academicObject->EDUCATION_SYSTEM ? 0 : 1;
        $SAVEDATA['NATIONAL_EXAM'] = isset($params["NATIONAL_EXAM"]) ? 1 : 0;
        $SAVEDATA['FORMULA_TYPE'] = addText($params["FORMULA_TYPE"]);
        $SAVEDATA['SCORE_TYPE'] = isset($params["SCORE_TYPE"]) ? addText($params["SCORE_TYPE"]) : 1;

        $WHERE = array();
        $WHERE[] = self::dbAccess()->quoteInto('ID = ?', $gradesubjectId);
        self::dbAccess()->update('t_grade_subject', $SAVEDATA, $WHERE);
        return array("success" => true);
    }

    public function SubjectByGradeCombo()
    {

        $utiles = Utiles::getInstance();
        $gradeId = $utiles->getValueRegistry("GRADE_ID");
        $schoolyearId = $utiles->getValueRegistry("SCHOOLYEAR_ID");

        $data = array();

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

    public static function checkSubjectINGrade($subjectId, $gradeId, $schoolyearId, $classId = false)
    {

        $result = self::getGradeSubject(false, $gradeId, $subjectId, $schoolyearId, $classId);
        return $result ? 1 : 0;
    }

    protected static function addSubjectINGrade($subjectId, $gradeId, $schoolyearId, $classId = false)
    {

        $SAVEDATA = array();

        $facette = SubjectDBAccess::findSubjectFromId($subjectId);

        if ($facette)
        {

            if ($facette->SUBJECT_TYPE)
                $SAVEDATA['SUBJECT_TYPE'] = $facette->SUBJECT_TYPE;

            if ($facette->EDUCATION_TYPE)
                $SAVEDATA['EDUCATION_TYPE'] = $facette->EDUCATION_TYPE;

            if ($facette->EDUCATION_SYSTEM)
                $SAVEDATA['EDUCATION_SYSTEM'] = $facette->EDUCATION_SYSTEM;

            if ($facette->NATIONAL_EXAM)
                $SAVEDATA['NATIONAL_EXAM'] = $facette->NATIONAL_EXAM;

            if ($facette->PRE_REQUISITE_COURSE)
                $SAVEDATA['PRE_REQUISITE_COURSE'] = $facette->PRE_REQUISITE_COURSE;

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

            if ($facette->INCLUDE_IN_EVALUATION)
                $SAVEDATA['INCLUDE_IN_EVALUATION'] = $facette->INCLUDE_IN_EVALUATION;

            if ($classId)
            {
                $SAVEDATA['CLASS'] = $classId;
                $SAVEDATA['USED_IN_CLASS'] = 1;
            }

            $SAVEDATA['SUBJECT_GUID'] = generateGuid();
            $SAVEDATA['SUBJECT'] = $subjectId;
            $SAVEDATA['GRADE'] = $gradeId;
            $SAVEDATA['SCHOOLYEAR'] = $schoolyearId;

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

    public static function getGradeSubject($Id, $gradeId, $subjectId, $schoolyearId, $classId = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        if ($Id)
        {
            $SQL->where("ID = ?",$Id);
        }
        else
        {
            if ($subjectId)
                $SQL->where("SUBJECT = ?",$subjectId);
            if ($gradeId)
                $SQL->where("GRADE = ?",$gradeId);
            if ($schoolyearId)
                $SQL->where("SCHOOLYEAR = ?",$schoolyearId);
            if ($classId)
            {
                $SQL->where("CLASS = ?",$classId);
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

    /* @soda */

    public static function getGradeSubjectYear($subjectId, $classId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        $SQL->where("SUBJECT = ?",$subjectId);
        $SQL->where("CLASS = ?",$classId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getGradeSubjectClass($subjectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        $SQL->where("SUBJECT = ?",$subjectId);
        $SQL->where("CLASS='0'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getGradeSubjectTeacherCount($subjectId, $teacher, $classId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_subject_teacher_content', 'COUNT(*) AS C');
        if ($subjectId)
            $SQL->where("SUBJECT = ?",$subjectId);
        if ($teacher)
            $SQL->where("TEACHER = '" . $teacher . "'");
        if ($classId)
            $SQL->where("ACADEMIC = ?",$classId);
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        return $result ? $result->C : 0;
    }

    public static function getGradeSubjectTeacher($subjectId, $teacher, $classId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_subject_teacher_content', array('*'));
        if ($subjectId)
            $SQL->where("SUBJECT = ?",$subjectId);
        if ($teacher)
            $SQL->where("TEACHER='" . $teacher . "'");
        if ($classId)
            $SQL->where("ACADEMIC='" . $classId . "'");
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

        $selectionIds = $params["selectionIds"];
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $selectedCount = self::addSubject2Grade($selectionIds, $academicId);

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    public function treeSubjectsByGrade($params)
    {

        $data = array();

        $result = self::sqlAssignedSubjectsByGrade($params);

        if ($result)
            $i = 0;
        foreach ($result as $value)
        {

            $data[$i]['id'] = "" . $value->SUBJECT_ID . "";
            $data[$i]['text'] = "(" . setShowText($value->SHORT) . ") " . setShowText($value->NAME);
            $data[$i]['onlyText'] = setShowText($value->NAME);
            $data[$i]['cls'] = "nodeTextBold";
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
            $data[$i]['leaf'] = true;
            $i++;
        }

        return $data;
    }

    public function treeGradeSubjectAssignments($params)
    {

        $subjectId = 0;
        $classId = 0;
        $gradeId = 0;
        $schoolyearId = 0;
        $used_in_class = 0;

        $node = $params["node"];

        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : 0;
        $chooseSubjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : 0;

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : 0;
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

        if ($chooseSubjectId)
        {
            $subjectId = $chooseSubjectId;
        }
        else
        {

            if (is_numeric($node))
            {
                $subjectId = $node;
            }
            else
            {
                $node = substr($node, 0, 8);
            }
        }

        if ($subjectId == 0)
        {
            $result = self::sqlAssignedSubjectsByGrade($params);
        }
        else
        {

            $SQL = "";
            $SQL .= " SELECT A.ID AS ID
                ,A.STATUS AS STATUS
                ,A.EVALUATION_TYPE AS EVALUATION_TYPE
                ,A.COEFF_VALUE AS COEFF_VALUE
                ,A.INCLUDE_IN_EVALUATION AS INCLUDE_IN_EVALUATION
                ,C.SUBJECT_TYPE AS SUBJECT_TYPE
                ,A.NAME AS NAME
                ,A.SHORT AS SHORT
                ,C.ID AS SUBJECT_ID
                ,C.EDUCATION_TYPE AS EDUCATION_TYPE
                ";
            $SQL .= " ,C.SUBJECT_TYPE AS SUBJECT_TYPE";
            $SQL .= " FROM t_assignment AS A";
            $SQL .= " LEFT JOIN t_grade_subject AS B ON B.SUBJECT=A.SUBJECT";
            $SQL .= " LEFT JOIN t_subject AS C ON C.ID=A.SUBJECT";
            $SQL .= " WHERE 1=1";
            $SQL .= " AND A.SUBJECT = '" . $subjectId . "'";

            if ($schoolyearId)
                $SQL .= " AND A.SCHOOLYEAR = '" . $schoolyearId . "'";

            if ($gradeId)
                $SQL .= " AND A.GRADE = '" . $gradeId . "'";

            if ($classId)
            {
                $SQL .= " AND A.CLASS = '" . $classId . "'";
            }
            else
            {
                $SQL .= " AND A.CLASS = '0'";
            }

            if ($classId && $chooseSubjectId && $teacherId)
            {
                $SQL .= " AND A.STATUS = 1";
            }
            $SQL .= " AND A.USED_IN_CLASS = '" . $used_in_class . "'";

            $SQL .= " GROUP BY A.ID";
            $SQL .= " ORDER BY A.SORTKEY ASC";

            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);

            $data = array();
        }

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                if ($subjectId == 0)
                {

                    $data[$i]['id'] = $value->SUBJECT_ID;
                    $data[$i]['gradesubjectId'] = $value->SUBJECT_GRADE_ID;
                    $data[$i]['subjectId'] = $value->SUBJECT_ID;
                    $data[$i]['text'] = "(" . $value->SHORT . ") " . setShowText($value->NAME);
                    $data[$i]['treeType'] = "SUBJECT";
                    $data[$i]['cls'] = "nodeFolderBold";
                    $data[$i]['hasAssignment'] = 0;
                    $data[$i]['iconCls'] = "icon-star";
                    $data[$i]['leaf'] = false;
                }
                else
                {

                    $data[$i]['id'] = "CAMEMIS_" . $value->ID;
                    $data[$i]['treeType'] = "ASSIGNMENT";
                    $data[$i]['assignmentId'] = $value->ID;
                    $data[$i]['subjectId'] = $value->SUBJECT_ID;
                    $data[$i]['educationType'] = $value->EDUCATION_TYPE;

                    if ($value->STATUS == 1)
                    {
                        $data[$i]['cls'] = "nodeTextBlue";
                    }
                    else
                    {
                        $data[$i]['cls'] = "nodeTextRed";
                    }

                    if ($academicObject->EVALUATION_TYPE)
                    {
                        $data[$i]['text'] = "(".setShowText($value->SHORT) . ") ".setShowText($value->NAME) . " (" . $value->COEFF_VALUE . "%)";
                    }
                    else
                    {
                        $data[$i]['text'] = "(".setShowText($value->SHORT) . ") ".setShowText($value->NAME) . " (" . $value->COEFF_VALUE . ")";
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
                    $data[$i]['leaf'] = true;
                }

                $i++;
            }

        return $data;
    }

    public static function getCountSubjectByClass_Term($academicId, $subjectId, $term)
    {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule', 'COUNT(*) AS C');
        $SQL->where("ACADEMIC_ID = ?",$academicId);
        $SQL->where("SUBJECT_ID = ?",$subjectId);
        $SQL->where("TERM = ?",$term);
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
                $data[$i]["SUBJECT_ID"] = $value->SUBJECT_ID;

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

    public function checkAssignmentBySubject($subjectId, $gradeId, $classId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_assignment', 'COUNT(*) AS C');
        $SQL->where("SUBJECT = ?",$subjectId);
        if ($gradeId)
            $SQL->where("GRADE = ?",$gradeId);
        if ($classId)
            $SQL->where("CLASS = ?",$classId);
        $SQL->where("SCHOOLYEAR = ?",$schoolyearId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function mappingAllSubjectGrade($gradeId, $schoolyearId)
    {

        $SAVEDATA = array();

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', array('*'));
        if ($gradeId)
            $SQL->where("GRADE = ?",$gradeId);
        if ($schoolyearId)
            $SQL->where("SCHOOLYEAR = ?",$schoolyearId);
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
                        $SQL .= " ,EVALUATION_TYPE ='" . $facette->EVALUATION_TYPE . "'";
                        $SQL .= " ,NATIONAL_EXAM ='" . $facette->NATIONAL_EXAM . "'";
                        $SQL .= " ,SCORE_TYPE ='" . $facette->SCORE_TYPE . "'";
                        $SQL .= " ,TEMPLATE ='" . $facette->TEMPLATE . "'";
                        $SQL .= " ,TRAINING ='" . $facette->TRAINING . "'";
                        $SQL .= " ,MAX_POSSIBLE_SCORE ='" . $facette->MAX_POSSIBLE_SCORE . "'";
                        $SQL .= " ,SCORE_MIN ='" . $facette->SCORE_MIN . "'";
                        $SQL .= " ,SCORE_MAX ='" . $facette->SCORE_MAX . "'";
                        $SQL .= " ,COEFF_VALUE ='" . $facette->COEFF_VALUE . "'";
                        $SQL .= " ,FORMULA_TYPE ='" . $facette->FORMULA_TYPE . "'";
                        $SQL .= " ,COEFF ='" . $facette->COEFF . "'";
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

    public static function checkAssignedSubjectByClass($classId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade_subject', 'COUNT(*) AS C');
        $SQL->where("CLASS = ?",$classId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    ///////////////////////////////////////////////////////////
    // @Sor Veasna
    // Date: 17.02.2013
    // Modify some code 
    ///////////////////////////////////////////////////////////
    public static function copySubjectAssingmentToClass($academicId, $copyFrom)
    {

        $SAVEDATA = array();
        $academicObject = AcademicDBAccess::findAcademicFromGuId($academicId);

        if ($academicObject)
        {

            $DELETE_SUBJECT = "DELETE FROM t_grade_subject WHERE CLASS = '" . $academicObject->ID . "'";
            self::dbAccess()->query($DELETE_SUBJECT);

            $DELETE_ASSIGNMENT = "DELETE FROM t_assignment WHERE CLASS = '" . $academicObject->ID . "'";
            self::dbAccess()->query($DELETE_ASSIGNMENT);

            if ($copyFrom == "schoolyear" || $copyFrom == "autoschoolyear")
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
                        $SAVEDATA['NUMBER_SESSION'] = $value->NUMBER_SESSION;
                        $SAVEDATA['EDUCATION_TYPE'] = $value->EDUCATION_TYPE;
                        $SAVEDATA['EVALUATION_TYPE'] = $value->EVALUATION_TYPE;
                        $SAVEDATA['NUMBER_SESSION'] = $value->NUMBER_SESSION;
                        $SAVEDATA['NUMBER_CREDIT'] = $value->NUMBER_CREDIT;
                        $SAVEDATA['COEFF'] = $value->COEFF;
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
                        $SAVEDATA['EDUCATION_SYSTEM'] = $value->EDUCATION_SYSTEM;
                        $SAVEDATA['INCLUDE_IN_EVALUATION'] = $value->INCLUDE_IN_EVALUATION;
                        $SAVEDATA['DEPARTMENT'] = $value->DEPARTMENT;

                        $SAVEDATA['GOALS'] = $value->GOALS;
                        $SAVEDATA['OBJECTIVES'] = $value->OBJECTIVES;
                        $SAVEDATA['MATERIALS'] = $value->MATERIALS;

                        if (Zend_Registry::get('SCHOOL')->ENABLE_ITEMS_BY_DEFAULT)
                        {
                            $SAVEDATA['STATUS'] = 1;
                        }

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

    ///////////////////////////

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
                $SQL->where("CLASS = '" . $academicObject->ID . "'");
                $SQL->where("USED_IN_CLASS = '1'");
                break;
        }

        $SQL->where("SUBJECT = ?",$subjectId);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function copyAssignmentToSubjctClass($subjectId, $academicId, $gradeId, $schoolyearId, $copyFrom)
    {

        $SAVEDATA = array();

        if ($copyFrom == "schoolyear")
        {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_assignment", array('*'));
            $SQL->where("SUBJECT = ?",$subjectId);
            $SQL->where("GRADE = ?",$gradeId);
            $SQL->where("CLASS = '0'");
            $SQL->where("SCHOOLYEAR = ?",$schoolyearId);
            $SQL->where("USED_IN_CLASS = '0'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
        }
        elseif ($copyFrom > 0)
        {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_assignment", array('*'));
            $SQL->where("SUBJECT = ?",$subjectId);
            $SQL->where("GRADE = ?",$gradeId);
            $SQL->where("CLASS = '" . $copyFrom . "'");
            $SQL->where("SCHOOLYEAR = ?",$schoolyearId);
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
            $SAVEDATA['USED_IN_CLASS'] = 1;
            $SAVEDATA['INCLUDE_IN_EVALUATION'] = $value->INCLUDE_IN_EVALUATION;
            $SAVEDATA['COEFF_VALUE'] = $value->COEFF_VALUE;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert('t_assignment', $SAVEDATA);
        }

        return true;
    }

    public static function sqlAllSubjectsByClass($academicId)
    {

        $params["academicId"] = $academicId;
        return self::sqlAssignedSubjectsByGrade($params);
    }

    public static function sqlSubjectTeacherClass($classId, $subjectId, $term)
    {

        $academicObject = AcademicDBAccess::findGradeFromId($classId);

        $SQL = "";
        $SQL .= " SELECT A.ID AS CLASS_ID, A.NAME AS CLASS_NAME";
        $SQL .= " FROM t_grade AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.ACADEMIC";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.CAMPUS ='" . $academicObject->CAMPUS_ID . "'";
        $SQL .= " AND B.GRADE ='" . $academicObject->GRADE_ID . "'";
        $SQL .= " AND B.SUBJECT ='" . $subjectId . "'";
        $SQL .= " AND B.GRADINGTERM ='" . $term . "'";
        return self::dbAccess()->fetchAll($SQL);
    }

    protected static function mappingUsingSubejctInClass($subjectId, $classId)
    {

        if ($classId && $subjectId)
        {
            $SQL = self::dbAccess()->select();
            $SQL->from('t_grade_subject', 'COUNT(*) AS C');
            $SQL->where("CLASS = ?",$classId);
            $SQL->where("SUBJECT = ?",$subjectId);
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            if ($result)
            {
                if (!$result->C)
                {
                    self::dbAccess()->query("UPDATE t_grade_subject SET USED_IN_CLASS=1 WHERE CLASS='" . $classId . "' AND SUBJECT='" . $subjectId . "'");
                }
            }
        }
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

    public static function getStudentCreditSubject($studentId, $schoolyearId)
    {
        $SELECTION_A = array(
            "ID AS ID"
            , "CREDIT_ACADEMIC_ID AS ACADEMIC_ID" //@Man
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
        $SQL->where("A.STUDENT_ID = ?",$studentId);
        $SQL->where("A.SCHOOLYEAR_ID = '" . $schoolyearId . "'");

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

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
                $data[$i]["ACADEMIC_ID"] = $value->ACADEMIC_ID;
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

    public static function getCreditStudentGroup($studentId, $schooyearId)
    {
        $SELECTION_A = array(
            "ID AS ID"
            , "CAMPUS_ID AS CAMPUS_ID"
            , "CLASS_ID AS CLASS_ID"
            , "SCHOOLYEAR_ID AS SCHOOLYEAR_ID"
            , "TRANSFER AS TRANSFER"
            , "TRANSFER_DESCRIPTION AS TRANSFER_DESCRIPTION"
        );
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_schoolyear_subject'), $SELECTION_A);
        $SQL->where("A.STUDENT_ID = ?",$studentId);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        return $result;
    }

    ///
}

?>