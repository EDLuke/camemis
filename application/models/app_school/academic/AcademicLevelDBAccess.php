<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDBAccess.php';
require_once 'models/app_school/schedule/CopyScheduleDBAccess.php';
require_once setUserLoacalization();

class AcademicLevelDBAccess extends AcademicDBAccess {

    private $dataforjson = null;
    private static $instance = null;

    static function getInstance()
    {
        if (self::$instance === null)
        {

            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * TREE: Generaledu....
     */
    public static function getSQLAllAcademics($params)
    {

        $educationSystem = isset($params["educationSystem"]) ? $params["educationSystem"] : 0;
        $parentCampus = isset($params["parentCampus"]) ? $params["parentCampus"] : "";
        $parentGrade = isset($params["parentGrade"]) ? $params["parentGrade"] : "";
        $gradeSchoolyearId = isset($params["gradeSchoolyearId"]) ? $params["gradeSchoolyearId"] : 0;
        $objectType = isset($params["objectType"]) ? $params["objectType"] : "";
        $objectTypeIn = isset($params["objectTypeIn"]) ? $params["objectTypeIn"] : "";

        $searchCampus = isset($params["searchCampus"]) ? $params["searchCampus"] : "";
        $searchGrade = isset($params["searchGrade"]) ? $params["searchGrade"] : "";
        $searchSchoolyear = isset($params["searchSchoolyear"]) ? $params["searchSchoolyear"] : "";

        $parentNode = '';

        if ($gradeSchoolyearId)
        {
            $GRADE_SCHOOLYEAR_OBJECT = AcademicDBAccess::findGradeFromId($gradeSchoolyearId);
            $schoolyearId = $GRADE_SCHOOLYEAR_OBJECT->SCHOOL_YEAR;
        }
        else
        {
            $schoolyearId = isset($params["SCHOOLYEAR"]) ? $params["SCHOOLYEAR"] : 0;
        }

        $schoolyearId = isset($params["SCHOOLYEAR"]) ? $params["SCHOOLYEAR"] : 0;

        $parent = $params["node"];
        $academicObject = AcademicDBAccess::findGradeFromId($parent);

        $SQL = "";
        $SQL .= " SELECT DISTINCT
            A.ID AS ID
            ,A.USE_OF_GROUPS AS USE_OF_GROUPS
            ,A.GUID AS GUID
            ,A.PARENT AS PARENT
            ,A.NUMBER_CREDIT AS NUMBER_CREDIT
            ,A.EDUCATION_SYSTEM AS EDUCATION_SYSTEM
            ,A.STATUS AS STATUS
            ,A.CAMPUS_ID AS CAMPUS_ID
            ,A.GRADE_ID AS GRADE_ID
            ,A.LEVEL AS LEVEL
            ,A.TITLE AS TITLE
            ,A.SUBJECT_ID AS SUBJECT_ID
            ,A.NAME AS NAME
            ,A.TERM_NUMBER AS TERM_NUMBER
            ,A.TREE_TYPE AS TREE_TYPE
            ,A.OBJECT_TYPE AS OBJECT_TYPE
            ,A.SORTKEY AS SORTKEY
            ,A.SCHOOL_YEAR AS SCHOOL_YEAR
            ,B.NAME AS SCHOOL_YEAR_NAME
            ";
        $SQL .= " FROM t_grade AS A";
        $SQL .= " LEFT JOIN t_academicdate AS B ON A.SCHOOL_YEAR=B.ID";
        $SQL .= " WHERE 1=1";

        if (!$parentNode)
        {
            if ($educationSystem)
            {
                $SQL .= " AND A.EDUCATION_SYSTEM = 1";
            }
            else
            {
                $SQL .= " AND A.EDUCATION_SYSTEM = 0";
            }
        }

        if (!$searchCampus || !$searchGrade || !$searchSchoolyear)
        {

            if ($parentCampus)
            {
                if (!$parent)
                {
                    $SQL .= " AND A.ID = '" . $parentCampus . "'";
                }
                else
                {
                    $SQL .= " AND A.PARENT = '" . $parent . "'";
                }
            }
            elseif ($parentGrade)
            {
                if (!$parent)
                {
                    $SQL .= " AND A.ID = '" . $parentGrade . "'";
                }
                else
                {
                    $SQL .= " AND A.PARENT = '" . $parent . "'";
                }
            }
            else
            {
                $SQL .= " AND A.PARENT = '" . $parent . "'";
                if ($academicObject)
                {//@veasna
                    if ($academicObject->OBJECT_TYPE == "CAMPUS" && $academicObject->EDUCATION_SYSTEM == 1)
                    {
                        if ($schoolyearId)
                        {
                            $SQL .= " AND A.SCHOOL_YEAR = '" . $schoolyearId . "'";
                        }
                    }
                }
            }

            if ($objectType == "CAMPUS")
            {
                $SQL .= " AND A.OBJECT_TYPE = 'CAMPUS'";
            }

            if ($objectTypeIn)
            {
                $SQL .= " AND A.OBJECT_TYPE IN " . $objectTypeIn;
            }
        }

        if ($parent == 0)
        {
            if ($searchCampus)
            {
                $SQL .= " AND A.ID = '" . $searchCampus . "'";
            }
            $SQL .= " ORDER BY A.SORTKEY ASC";
        }
        else
        {

            switch ($objectType)
            {
                case "GRADE":
                    $SQL .= " AND A.OBJECT_TYPE = 'GRADE'";
                    break;
            }

            if ($academicObject)
            {

                switch ($academicObject->OBJECT_TYPE)
                {

                    case "CAMPUS":

                        if ($searchGrade)
                        {
                            $SQL .= " AND A.ID = '" . $searchGrade . "'";
                        }
                        $SQL .= " ORDER BY A.SORTKEY ASC";

                        break;
                    case "GRADE":
                        if ($searchSchoolyear)
                        {
                            $SQL .= " AND A.OBJECT_TYPE = 'SCHOOLYEAR'";
                            $SQL .= " AND A.GRADE_ID = '" . $searchGrade . "'";
                            $SQL .= " AND A.SCHOOL_YEAR = '" . $searchSchoolyear . "'";
                        }
                        if ($schoolyearId)
                        {
                            $SQL .= " AND A.SCHOOL_YEAR = '" . $schoolyearId . "'";
                        }

                        $SQL .= " ORDER BY A.SORTKEY ASC";

                        break;
                    case "SCHOOLYEAR":

                        if ($searchSchoolyear)
                        {
                            $SQL .= " AND A.OBJECT_TYPE = 'CLASS'";
                            $SQL .= " AND A.GRADE_ID = '" . $searchGrade . "'";
                            $SQL .= " AND A.SCHOOL_YEAR = '" . $searchSchoolyear . "'";
                        }

                        if ($schoolyearId)
                            $SQL .= " AND A.SCHOOL_YEAR = '" . $schoolyearId . "'";
                        $SQL .= " ORDER BY A.SORTKEY ASC";

                        break;
                    case "CLASS":
                        if ($schoolyearId)
                            $SQL .= " AND A.SCHOOL_YEAR = '" . $schoolyearId . "'";
                        $SQL .= " ORDER BY A.SORTKEY ASC";
                        break;
                }
            }
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function getTreeTraditionalEducationSystem($params)
    {

        $isClassCheckbox = isset($params["isClassCheckbox"]) ? $params["isClassCheckbox"] : "";

        $schoolyearObject = AcademicDateDBAccess::getInstance();
        $params["educationSystem"] = 0;
        $result = self::getSQLAllAcademics($params);

        $allowDelete = false;
        if (Zend_Registry::get('IS_SUPER_ADMIN') == 1)
        {
            $allowDelete = true;
        }
        elseif (UserAuth::getUserType() == "SUPERADMIN")
        {
            $allowDelete = true;
        }

        if ($result)
            foreach ($result as $value)
            {

                $isCurrentYear = $schoolyearObject->isCurrentSchoolyear($value->SCHOOL_YEAR);

                $data['allowDelete'] = $allowDelete;
                $data['id'] = "" . $value->ID . "";
                $data['guId'] = "" . $value->GUID . "";
                $data['parentId'] = "" . $value->PARENT . "";
                $data['objectType'] = $value->OBJECT_TYPE;
                $data['schoolyearId'] = $value->SCHOOL_YEAR;
                $data['educationSystem'] = $value->EDUCATION_SYSTEM;
                $data['subjectId'] = $value->SUBJECT_ID;

                switch ($value->OBJECT_TYPE)
                {

                    case "CAMPUS":
                        $data['text'] = setShowText($value->NAME);
                        $data['title'] = setShowText($value->NAME);
                        $data['cls'] = "nodeCampus";
                        $data['iconCls'] = "icon-bricks";
                        $data['bulletinCampusId'] = $value->CAMPUS_ID;

                        break;
                    case "GRADE":
                        $data['text'] = setShowText($value->NAME);
                        $result = self::findGradeFromId($value->PARENT);
                        $data['title'] = setShowText($result->NAME . " ( " . $value->NAME . " )");
                        $data['cls'] = "nodeGrade";
                        $data['gradeId'] = $value->GRADE_ID;
                        $data['iconCls'] = "icon-folder_magnify";
                        $data['campusId'] = $value->CAMPUS_ID;
                        break;
                    case "SCHOOLYEAR":
                        $data['text'] = setShowText($value->NAME);
                        $data['title'] = setShowText($value->NAME);
                        $data['campusId'] = $value->CAMPUS_ID;
                        $data['gradeId'] = $value->GRADE_ID;
                        $data['schoolyearId'] = $value->SCHOOL_YEAR;

                        if ($isCurrentYear)
                        {
                            $data['cls'] = "nodeTextBoldBlue";
                        }
                        else
                        {
                            $data['cls'] = "nodeTextRedBold";
                        }
                        if ($value->STATUS == 1)
                        {
                            $data['iconCls'] = "icon-date";
                        }
                        else
                        {
                            $data['iconCls'] = "icon-date_edit";
                        }
                        break;
                    case "CLASS":
                        $data['text'] = setShowText($value->NAME);
                        $data['title'] = setShowText($value->NAME);
                        $data['classStatus'] = $value->STATUS;
                        $data['campusId'] = $value->CAMPUS_ID;
                        $data['gradeId'] = $value->GRADE_ID;
                        $data['schoolyearId'] = $value->SCHOOL_YEAR;
                        $data['useOfGroups'] = $value->USE_OF_GROUPS;
                        if ($isCurrentYear)
                        {
                            $data['cls'] = "nodeTextBlue";
                        }
                        else
                        {
                            $data['cls'] = "nodeTextRedBold";
                        }
                        if ($value->STATUS == 1)
                        {
                            $data['iconCls'] = "icon-blackboard";
                        }
                        else
                        {
                            $data['iconCls'] = "icon-page_white_edit";
                        }

                        if ($isClassCheckbox)
                        {
                            $data['checked'] = false;
                        }
                        $data['click'] = true;
                        $data['leaf'] = true;
                        break;
                }

                $data['navtitle'] = $value->TITLE;
                $data['term_number'] = $value->TERM_NUMBER;
                $data['objecttype'] = $value->OBJECT_TYPE;
                $data['type'] = $value->TREE_TYPE;

                $this->dataforjson[] = $data;
            }

        return $this->dataforjson;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getTreeCreditEducationSystem($params)
    {

        $isClassCheckbox = isset($params["isClassCheckbox"]) ? $params["isClassCheckbox"] : "";

        $schoolyearObject = AcademicDateDBAccess::getInstance();
        $params["educationSystem"] = 1;
        $result = self::getSQLAllAcademics($params);

        $allowDelete = false;
        if (Zend_Registry::get('IS_SUPER_ADMIN') == 1)
        {
            $allowDelete = true;
        }
        elseif (UserAuth::getUserType() == "SUPERADMIN")
        {
            $allowDelete = true;
        }

        if ($result)
            foreach ($result as $value)
            {

                $isCurrentYear = $schoolyearObject->isCurrentSchoolyear($value->SCHOOL_YEAR);

                $data['allowDelete'] = $allowDelete;
                $data['id'] = "" . $value->ID . "";
                $data['guId'] = "" . $value->GUID . "";
                $data['parentId'] = "" . $value->PARENT . "";
                $data['objectType'] = $value->OBJECT_TYPE;
                $data['schoolyearId'] = $value->SCHOOL_YEAR;
                $data['educationSystem'] = $value->EDUCATION_SYSTEM;
                $data['subjectId'] = $value->SUBJECT_ID;

                switch ($value->OBJECT_TYPE)
                {

                    case "CAMPUS":
                        $data['text'] = setShowText($value->NAME);
                        $data['title'] = setShowText($value->NAME);
                        $data['cls'] = "nodeCampus";
                        $data['iconCls'] = "icon-bricks";
                        $data['bulletinCampusId'] = $value->CAMPUS_ID;
                        break;
                    case "SCHOOLYEAR":

                        $data['text'] = setShowText($value->NAME);
                        $data['title'] = setShowText($value->NAME);
                        $data['campusId'] = $value->CAMPUS_ID;
                        $data['schoolyearId'] = $value->SCHOOL_YEAR;

                        if ($isCurrentYear)
                        {
                            $data['cls'] = "nodeTextBoldBlue";
                        }
                        else
                        {
                            $data['cls'] = "nodeTextRedBold";
                        }
                        if ($value->STATUS == 1)
                        {
                            $data['iconCls'] = "icon-date";
                        }
                        else
                        {
                            $data['iconCls'] = "icon-date_edit";
                        }
                        break;
                    case "SUBJECT":

                        $subjectObject = SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);

                        if ($subjectObject)
                        {
                            $data['title'] = $subjectObject->NAME;
                            if ($subjectObject->SHORT)
                            {
                                $data['text'] = "($subjectObject->SHORT) " . $subjectObject->NAME;
                            }
                            else
                            {
                                $data['text'] = $subjectObject->NAME;
                            }
                        }
                        else
                        {
                            $data['text'] = "?";
                        }

                        $data['classStatus'] = $value->STATUS;
                        $data['campusId'] = $value->CAMPUS_ID;
                        $data['schoolyearId'] = $value->SCHOOL_YEAR;
                        $data['click'] = true;
                        $data['leaf'] = false;
                        $data['cls'] = "nodeFolderBold";
                        $data['iconCls'] = "icon-folder_star";

                        break;
                    case "CLASS":
                        $subjectObject = SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);

                        if ($subjectObject)
                        {
                            $data['title'] = setShowText($subjectObject->NAME) . " &raquo; " . setShowText($value->NAME);
                        }

                        $data['text'] = setShowText($value->NAME);
                        $data['classStatus'] = $value->STATUS;
                        $data['campusId'] = $value->CAMPUS_ID;
                        $data['schoolyearId'] = $value->SCHOOL_YEAR;
                        if ($isCurrentYear)
                        {
                            $data['cls'] = "nodeTextBlue";
                        }
                        else
                        {
                            $data['cls'] = "nodeTextRedBold";
                        }
                        if ($value->STATUS == 1)
                        {
                            $data['iconCls'] = "icon-blackboard";
                        }
                        else
                        {
                            $data['iconCls'] = "icon-page_white_edit";
                        }

                        if ($isClassCheckbox)
                        {
                            $data['checked'] = false;
                        }
                        $data['click'] = true;
                        $data['leaf'] = true;
                        break;
                }

                $data['navtitle'] = $value->TITLE;
                $data['term_number'] = $value->TERM_NUMBER;
                $data['objecttype'] = $value->OBJECT_TYPE;
                $data['type'] = $value->TREE_TYPE;

                $this->dataforjson[] = $data;
            }

        return $this->dataforjson;
    }

    ////////////////////////////////////////////////////////////////////////////
    public function getCountChildren($Id)
    {
        $SQL = "SELECT count(*) AS C FROM t_grade WHERE PARENT = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Add Node...
    ////////////////////////////////////////////////////////////////////////////
    public function addNode($params)
    {

        $parentId = $params["parentId"];

        $OBJECT_PARENT = AcademicDBAccess::findGradeFromId($parentId);

        $SAVEDATA['GUID'] = generateGuid();
        $SAVEDATA['TERM_NUMBER'] = 1;

        if (isset($params["parentId"]))
            $SAVEDATA['PARENT'] = $parentId;
        if (isset($params["objecttype"]))
            $SAVEDATA['OBJECT_TYPE'] = $params["objecttype"];

        $postName = isset($params["NAME"]) ? addText($params["NAME"]) . "" : "";
        $chooseSubjectName = isset($params["CHOOSE_SUBJECT_NAME"]) ? addText($params["CHOOSE_SUBJECT_NAME"]) . "" : "";
        $chooseSubjectId = isset($params["CHOOSE_SUBJECT"]) ? addText($params["CHOOSE_SUBJECT"]) . "" : "";

        if (isset($params["SORTKEY"]))
            $SAVEDATA['SORTKEY'] = addText($params["SORTKEY"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        switch ($params["objecttype"])
        {
            case "CAMPUS":
                $name = setShowText($postName);
                $SAVEDATA['NAME'] = $name;
                $SAVEDATA['TITLE'] = $name;
                $SAVEDATA['TREE_TYPE'] = 0;

                if (isset($params["QUALIFICATION_TYPE"]))
                {
                    $SAVEDATA['EDUCATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);
                    $SAVEDATA['QUALIFICATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);
                }

                if (isset($params["EDUCATION_SYSTEM"]))
                    $SAVEDATA['EDUCATION_SYSTEM'] = $params["EDUCATION_SYSTEM"];

                if (isset($params["SCHOOL_TYPE"]))
                    $SAVEDATA['SCHOOL_TYPE'] = addText($params["SCHOOL_TYPE"]);

                if (isset($params["DEPARTMENT"]))
                    $SAVEDATA['DEPARTMENT'] = addText($params["DEPARTMENT"]);

                $SAVEDATA['EMAIL'] = "info@camemis.com";

                $leaf = false;
                $objecttype = "CAMPUS";
                $iconCls = "icon-bricks";
                $navtitle = $name;
                $cls = "nodeCampus";

                break;

            case "GRADE":
                $name = setShowText($postName);
                $SAVEDATA['NAME'] = $postName;
                $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $postName;
                $SAVEDATA['TREE_TYPE'] = 0;

                if (isset($params["LEVEL"]))
                    $SAVEDATA['LEVEL'] = addText($params["LEVEL"]);

                $SAVEDATA['SHORT'] = $OBJECT_PARENT->SHORT;
                $SAVEDATA['TERM_NUMBER'] = $OBJECT_PARENT->TERM_NUMBER;
                $SAVEDATA['CAMPUS_ID'] = $OBJECT_PARENT->ID;
                $SAVEDATA['EDUCATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                $SAVEDATA['QUALIFICATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                $SAVEDATA['EDUCATION_SYSTEM'] = $OBJECT_PARENT->EDUCATION_SYSTEM;

                $SAVEDATA['PRE_REQUIREMENTS'] = $OBJECT_PARENT->PRE_REQUIREMENTS;
                $SAVEDATA['SEMESTER1_WEIGHTING'] = 1;
                $SAVEDATA['SEMESTER2_WEIGHTING'] = 2;

                $leaf = false;
                $objecttype = "GRADE";
                $iconCls = "icon-package_white";
                $navtitle = $OBJECT_PARENT->TITLE . " &raquo; " . $postName;
                $cls = "nodeGrade";

                break;

            case "SCHOOLYEAR":

                $SAVEDATA['EVALUATION_TYPE'] = isset($params["EVALUATION_TYPE"]) ? 1 : 0;

                if (isset($params["DISTRIBUTION_VALUE"]))
                    $SAVEDATA['DISTRIBUTION_VALUE'] = addText($params["DISTRIBUTION_VALUE"]);

                if (isset($params["NUMBER_CREDIT"]))
                    $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

                $SAVEDATA['DISPLAY_MONTH_RESULT'] = isset($params["DISPLAY_MONTH_RESULT"]) ? 1 : 0;
                $SAVEDATA['DISPLAY_FIRST_RESULT'] = isset($params["DISPLAY_FIRST_RESULT"]) ? 1 : 0;
                $SAVEDATA['DISPLAY_SECOND_RESULT'] = isset($params["DISPLAY_SECOND_RESULT"]) ? 1 : 0;
                $SAVEDATA['DISPLAY_YEAR_RESULT'] = isset($params["DISPLAY_YEAR_RESULT"]) ? 1 : 0;
                $newSchoolyear = isset($params["HIDDEN_SCHOOLYEAR"]) ? $params["HIDDEN_SCHOOLYEAR"] : 0;

                $SAVEDATA['TREE_TYPE'] = 0;
                $OBJECT_SCHOOL_YEAR = AcademicDateDBAccess::findAcademicDateFromId($newSchoolyear);

                if ($OBJECT_SCHOOL_YEAR)
                {
                    $SAVEDATA['SHORT'] = $OBJECT_PARENT->SHORT;
                    $SAVEDATA['NAME'] = $OBJECT_SCHOOL_YEAR->NAME;
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $OBJECT_SCHOOL_YEAR->NAME . " (" . getTermNumberShort($OBJECT_PARENT->TERM_NUMBER) . ")";
                    $SAVEDATA['SCHOOL_YEAR'] = $OBJECT_SCHOOL_YEAR->ID;

                    $SAVEDATA['LEVEL'] = $OBJECT_PARENT->LEVEL;
                    $SAVEDATA['EDUCATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                    $SAVEDATA['QUALIFICATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                    $SAVEDATA['EDUCATION_SYSTEM'] = $OBJECT_PARENT->EDUCATION_SYSTEM;

                    $SAVEDATA['SCHOOL_TYPE'] = $OBJECT_PARENT->SCHOOL_TYPE;
                    $SAVEDATA['TERM_NUMBER'] = $OBJECT_PARENT->TERM_NUMBER;
                    $SAVEDATA['GRADE_ID'] = $OBJECT_PARENT->ID;

                    if ($OBJECT_PARENT->EDUCATION_SYSTEM)
                    {
                        $SAVEDATA['CAMPUS_ID'] = $OBJECT_PARENT->ID;
                    }
                    else
                    {
                        $SAVEDATA['CAMPUS_ID'] = $OBJECT_PARENT->CAMPUS_ID;
                    }

                    $SAVEDATA['PRE_REQUIREMENTS'] = $OBJECT_PARENT->PRE_REQUIREMENTS;
                    $SAVEDATA['SEMESTER1_WEIGHTING'] = $OBJECT_PARENT->SEMESTER1_WEIGHTING;
                    $SAVEDATA['SEMESTER2_WEIGHTING'] = $OBJECT_PARENT->SEMESTER2_WEIGHTING;
                    $name = $OBJECT_SCHOOL_YEAR->NAME . " (" . getTermNumberShort($OBJECT_PARENT->TERM_NUMBER) . ")";
                    $leaf = false;
                    $objecttype = "SCHOOLYEAR";
                    $iconCls = "icon-date_add";
                    $navtitle = $OBJECT_PARENT->TITLE . " &raquo; " . $OBJECT_SCHOOL_YEAR->NAME . " (" . getTermNumberShort($OBJECT_PARENT->TERM_NUMBER) . ")";
                    $cls = "nodeTextBoldBlue";
                }
                else
                {

                    $name = "";
                    $leaf = false;
                    $objecttype = "SCHOOLYEAR";
                    $iconCls = "icon-date_add";
                    $navtitle = "";
                    $cls = "nodeTextBoldBlue";
                }

                break;

            case "CLASS":

                $SAVEDATA['NUMBER_CREDIT'] = $OBJECT_PARENT->NUMBER_CREDIT;

                if ($chooseSubjectId)
                {
                    $name = $chooseSubjectName . " (?)";
                    $SAVEDATA['NAME'] = $chooseSubjectName;
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $chooseSubjectName;
                    $SAVEDATA['TREE_TYPE'] = 1;
                    $SAVEDATA['SUBJECT_ID'] = $chooseSubjectId;

                    GradeSubjectDBAccess::addSubject2Grade($chooseSubjectId, $OBJECT_PARENT->ID);
                }
                else
                {
                    $name = $postName . " (?)";
                    $SAVEDATA['NAME'] = $postName;
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $postName;
                    $SAVEDATA['TREE_TYPE'] = 1;
                }

                $SAVEDATA['SHORT'] = $OBJECT_PARENT->SHORT;
                $SAVEDATA['LEVEL'] = $OBJECT_PARENT->LEVEL;
                $SAVEDATA['TERM_NUMBER'] = $OBJECT_PARENT->TERM_NUMBER;
                $SAVEDATA['SCHOOL_YEAR'] = $OBJECT_PARENT->SCHOOL_YEAR;
                $SAVEDATA['EDUCATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                $SAVEDATA['QUALIFICATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                $SAVEDATA['EDUCATION_SYSTEM'] = $OBJECT_PARENT->EDUCATION_SYSTEM;

                $SAVEDATA['SCHOOL_TYPE'] = $OBJECT_PARENT->SCHOOL_TYPE;
                $SAVEDATA['END_SCHOOL'] = $OBJECT_PARENT->END_SCHOOL;

                $SAVEDATA['GRADE_ID'] = $OBJECT_PARENT->GRADE_ID;
                $SAVEDATA['CAMPUS_ID'] = $OBJECT_PARENT->CAMPUS_ID;
                $SAVEDATA['PRE_REQUIREMENTS'] = $OBJECT_PARENT->PRE_REQUIREMENTS;

                $SAVEDATA['MO'] = $OBJECT_PARENT->MO;
                $SAVEDATA['TU'] = $OBJECT_PARENT->TU;
                $SAVEDATA['WE'] = $OBJECT_PARENT->WE;
                $SAVEDATA['TH'] = $OBJECT_PARENT->TH;
                $SAVEDATA['FR'] = $OBJECT_PARENT->FR;
                $SAVEDATA['SA'] = $OBJECT_PARENT->SA;
                $SAVEDATA['SU'] = $OBJECT_PARENT->SU;

                $SAVEDATA['SEMESTER1_WEIGHTING'] = $OBJECT_PARENT->SEMESTER1_WEIGHTING;
                $SAVEDATA['SEMESTER2_WEIGHTING'] = $OBJECT_PARENT->SEMESTER2_WEIGHTING;

                $SAVEDATA['EVALUATION_TYPE'] = $OBJECT_PARENT->EVALUATION_TYPE;
                $SAVEDATA['DISTRIBUTION_VALUE'] = $OBJECT_PARENT->DISTRIBUTION_VALUE;
                $SAVEDATA['DISPLAY_MONTH_RESULT'] = $OBJECT_PARENT->DISPLAY_MONTH_RESULT;
                $SAVEDATA['DISPLAY_FIRST_RESULT'] = $OBJECT_PARENT->DISPLAY_FIRST_RESULT;
                $SAVEDATA['DISPLAY_SECOND_RESULT'] = $OBJECT_PARENT->DISPLAY_SECOND_RESULT;
                $SAVEDATA['DISPLAY_YEAR_RESULT'] = $OBJECT_PARENT->DISPLAY_YEAR_RESULT;
                ///@veasna
                $SAVEDATA['SEMESTER1_START'] = $OBJECT_PARENT->SEMESTER1_START;
                $SAVEDATA['SEMESTER1_END'] = $OBJECT_PARENT->SEMESTER1_END;
                $SAVEDATA['SEMESTER2_START'] = $OBJECT_PARENT->SEMESTER2_START;
                $SAVEDATA['SEMESTER2_END'] = $OBJECT_PARENT->SEMESTER2_END;
                $SAVEDATA['TERM1_START'] = $OBJECT_PARENT->TERM1_START;
                $SAVEDATA['TERM1_END'] = $OBJECT_PARENT->TERM1_END;
                $SAVEDATA['TERM2_START'] = $OBJECT_PARENT->TERM2_START;
                $SAVEDATA['TERM2_END'] = $OBJECT_PARENT->TERM2_END;
                $SAVEDATA['TERM3_START'] = $OBJECT_PARENT->TERM3_START;
                $SAVEDATA['TERM3_END'] = $OBJECT_PARENT->TERM3_END;
                $SAVEDATA['QUARTER1_START'] = $OBJECT_PARENT->QUARTER1_START;
                $SAVEDATA['QUARTER1_END'] = $OBJECT_PARENT->QUARTER1_END;
                $SAVEDATA['QUARTER2_START'] = $OBJECT_PARENT->QUARTER2_START;
                $SAVEDATA['QUARTER2_END'] = $OBJECT_PARENT->QUARTER2_END;
                $SAVEDATA['QUARTER3_START'] = $OBJECT_PARENT->QUARTER3_START;
                $SAVEDATA['QUARTER3_END'] = $OBJECT_PARENT->QUARTER3_END;
                $SAVEDATA['QUARTER4_START'] = $OBJECT_PARENT->QUARTER4_START;
                $SAVEDATA['QUARTER4_END'] = $OBJECT_PARENT->QUARTER4_END;
                ///

                if ($OBJECT_PARENT->EDUCATION_SYSTEM)
                {
                    $SAVEDATA['SUBJECT_ID'] = $OBJECT_PARENT->SUBJECT_ID;
                }

                $leaf = true;
                $objecttype = "CLASS";
                $iconCls = "icon-blackboard";
                $navtitle = $OBJECT_PARENT->TITLE . " &raquo; ";
                $navtitle .= $postName;
                $cls = "nodeClass";
                break;
            case "SUBJECT":

                $SAVEDATA['NUMBER_CREDIT'] = $OBJECT_PARENT->NUMBER_CREDIT;

                if ($chooseSubjectId)
                {
                    $name = $chooseSubjectName . " (?)";
                    $SAVEDATA['NAME'] = $chooseSubjectName;
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $chooseSubjectName;
                    $SAVEDATA['TREE_TYPE'] = 1;
                    $SAVEDATA['SUBJECT_ID'] = $chooseSubjectId;

                    GradeSubjectDBAccess::addSubject2Grade($chooseSubjectId, $OBJECT_PARENT->ID);
                }
                else
                {
                    $name = $postName . " (?)";
                    $SAVEDATA['NAME'] = $postName;
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $postName;
                    $SAVEDATA['TREE_TYPE'] = 1;
                }

                $SAVEDATA['SHORT'] = $OBJECT_PARENT->SHORT;
                $SAVEDATA['LEVEL'] = $OBJECT_PARENT->LEVEL;
                $SAVEDATA['TERM_NUMBER'] = $OBJECT_PARENT->TERM_NUMBER;
                $SAVEDATA['SCHOOL_YEAR'] = $OBJECT_PARENT->SCHOOL_YEAR;
                $SAVEDATA['EDUCATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                $SAVEDATA['QUALIFICATION_TYPE'] = $OBJECT_PARENT->EDUCATION_TYPE;
                $SAVEDATA['EDUCATION_SYSTEM'] = $OBJECT_PARENT->EDUCATION_SYSTEM;
                $SAVEDATA['SCHOOL_TYPE'] = $OBJECT_PARENT->SCHOOL_TYPE;
                $SAVEDATA['END_SCHOOL'] = $OBJECT_PARENT->END_SCHOOL;
                $SAVEDATA['CAMPUS_ID'] = $OBJECT_PARENT->CAMPUS_ID;
                $SAVEDATA['PRE_REQUIREMENTS'] = $OBJECT_PARENT->PRE_REQUIREMENTS;
                $SAVEDATA['SEMESTER1_WEIGHTING'] = $OBJECT_PARENT->SEMESTER1_WEIGHTING;
                $SAVEDATA['SEMESTER2_WEIGHTING'] = $OBJECT_PARENT->SEMESTER2_WEIGHTING;

                $SAVEDATA['EVALUATION_TYPE'] = $OBJECT_PARENT->EVALUATION_TYPE;
                $SAVEDATA['DISTRIBUTION_VALUE'] = $OBJECT_PARENT->DISTRIBUTION_VALUE;
                $SAVEDATA['DISPLAY_MONTH_RESULT'] = $OBJECT_PARENT->DISPLAY_MONTH_RESULT;
                $SAVEDATA['DISPLAY_FIRST_RESULT'] = $OBJECT_PARENT->DISPLAY_FIRST_RESULT;
                $SAVEDATA['DISPLAY_SECOND_RESULT'] = $OBJECT_PARENT->DISPLAY_SECOND_RESULT;
                $SAVEDATA['DISPLAY_YEAR_RESULT'] = $OBJECT_PARENT->DISPLAY_YEAR_RESULT;
                ///@veasna
                $SAVEDATA['SEMESTER1_START'] = $OBJECT_PARENT->SEMESTER1_START;
                $SAVEDATA['SEMESTER1_END'] = $OBJECT_PARENT->SEMESTER1_END;
                $SAVEDATA['SEMESTER2_START'] = $OBJECT_PARENT->SEMESTER2_START;
                $SAVEDATA['SEMESTER2_END'] = $OBJECT_PARENT->SEMESTER2_END;
                $SAVEDATA['TERM1_START'] = $OBJECT_PARENT->TERM1_START;
                $SAVEDATA['TERM1_END'] = $OBJECT_PARENT->TERM1_END;
                $SAVEDATA['TERM2_START'] = $OBJECT_PARENT->TERM2_START;
                $SAVEDATA['TERM2_END'] = $OBJECT_PARENT->TERM2_END;
                $SAVEDATA['TERM3_START'] = $OBJECT_PARENT->TERM3_START;
                $SAVEDATA['TERM3_END'] = $OBJECT_PARENT->TERM3_END;
                $SAVEDATA['QUARTER1_START'] = $OBJECT_PARENT->QUARTER1_START;
                $SAVEDATA['QUARTER1_END'] = $OBJECT_PARENT->QUARTER1_END;
                $SAVEDATA['QUARTER2_START'] = $OBJECT_PARENT->QUARTER2_START;
                $SAVEDATA['QUARTER2_END'] = $OBJECT_PARENT->QUARTER2_END;
                $SAVEDATA['QUARTER3_START'] = $OBJECT_PARENT->QUARTER3_START;
                $SAVEDATA['QUARTER3_END'] = $OBJECT_PARENT->QUARTER3_END;
                $SAVEDATA['QUARTER4_START'] = $OBJECT_PARENT->QUARTER4_START;
                $SAVEDATA['QUARTER4_END'] = $OBJECT_PARENT->QUARTER4_END;
                ///

                $leaf = true;
                $objecttype = "CLASS";
                $iconCls = "icon-blackboard";
                $navtitle = $OBJECT_PARENT->TITLE . " &raquo; ";
                $navtitle .= $postName;
                $cls = "nodeClass";

                break;
        }

        $SAVEDATA['CODE'] = createCode();
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

        self::dbAccess()->insert('t_grade', $SAVEDATA);
        $objectId = self::dbAccess()->lastInsertId();

        switch ($params["objecttype"])
        {

            case "SCHOOLYEAR":
                $newSchoolyearId = isset($params["NEW_SCHOOLYEAR"]) ? $params["NEW_SCHOOLYEAR"] : "";
                $QUESTION = isset($params["QUESTION"]) ? $params["QUESTION"] : "";
                $COPY_LASTSCHOOLYEAR = isset($params["COPY_LASTSCHOOLYEAR"]) ? $params["COPY_LASTSCHOOLYEAR"] : "";
                $COPY_SUBJECT = isset($params["COPY_SUBJECT"]) ? $params["COPY_SUBJECT"] : "";
                $COPY_ASSIGNMENT = isset($params["COPY_ASSIGNMENT"]) ? $params["COPY_ASSIGNMENT"] : "";
                $gradeId = isset($params["parentId"]) ? $params["parentId"] : "";
                $newGradeSchoolyearId = $objectId;

                if ($QUESTION == "YES")
                {
                    $ACTION = true;
                }
                else
                {
                    $ACTION = false;
                }

                if ($COPY_LASTSCHOOLYEAR && $ACTION)
                {

                    $entriesClass = self::dbAccess()->fetchAll("SELECT * FROM t_grade WHERE SCHOOL_YEAR='" . $COPY_LASTSCHOOLYEAR . "' AND GRADE_ID='" . $gradeId . "' AND OBJECT_TYPE='CLASS'");

                    if ($newSchoolyearId)
                    {

                        if ($entriesClass)
                        {
                            foreach ($entriesClass as $value)
                            {
                                $SAVE_CLASS_DATA['PARENT'] = $newGradeSchoolyearId;
                                $SAVE_CLASS_DATA['CODE'] = createCode();
                                $SAVE_CLASS_DATA['GUID'] = generateGuid();
                                $SAVE_CLASS_DATA['NAME'] = $value->NAME;
                                $SAVE_CLASS_DATA['TITLE'] = $value->TITLE;
                                $SAVE_CLASS_DATA['LEVEL'] = $value->LEVEL;
                                $SAVE_CLASS_DATA['NUMBER_CREDIT'] = $value->NUMBER_CREDIT;
                                $SAVE_CLASS_DATA['TERM_NUMBER'] = $value->TERM_NUMBER;
                                $SAVE_CLASS_DATA['SCHOOL_YEAR'] = $newSchoolyearId;
                                $SAVE_CLASS_DATA['EDUCATION_TYPE'] = $value->EDUCATION_TYPE;
                                $SAVE_CLASS_DATA['QUALIFICATION_TYPE'] = $value->EDUCATION_TYPE;
                                $SAVE_CLASS_DATA['EDUCATION_SYSTEM'] = $value->EDUCATION_SYSTEM;

                                $SAVE_CLASS_DATA['SCHOOL_TYPE'] = $value->SCHOOL_TYPE;
                                $SAVE_CLASS_DATA['END_SCHOOL'] = $value->END_SCHOOL;
                                $SAVE_CLASS_DATA['GRADE_ID'] = $value->GRADE_ID;
                                $SAVE_CLASS_DATA['CAMPUS_ID'] = $value->CAMPUS_ID;
                                $SAVE_CLASS_DATA['YEAR_FORMULAR'] = $value->YEAR_FORMULAR;
                                $SAVE_CLASS_DATA['SEMESTER_FORMULAR'] = $value->SEMESTER_FORMULAR;
                                $SAVE_CLASS_DATA['YEAR_FORMULAR'] = $value->YEAR_FORMULAR;
                                $SAVE_CLASS_DATA['MO'] = $value->MO;
                                $SAVE_CLASS_DATA['TU'] = $value->TU;
                                $SAVE_CLASS_DATA['WE'] = $value->WE;
                                $SAVE_CLASS_DATA['TH'] = $value->TH;
                                $SAVE_CLASS_DATA['FR'] = $value->FR;
                                $SAVE_CLASS_DATA['SA'] = $value->SA;
                                $SAVE_CLASS_DATA['SU'] = $value->SU;
                                $SAVE_CLASS_DATA['OBJECT_TYPE'] = $value->OBJECT_TYPE;
                                $SAVE_CLASS_DATA['SEMESTER1_WEIGHTING'] = $value->SEMESTER1_WEIGHTING;
                                $SAVE_CLASS_DATA['SEMESTER2_WEIGHTING'] = $value->SEMESTER2_WEIGHTING;
                                self::dbAccess()->insert('t_grade', $SAVE_CLASS_DATA);
                            }
                        }
                    }
                }

                ////////////////////////////////////////////////////////////////
                //COPY SUBJECT...
                ////////////////////////////////////////////////////////////////
                if ($COPY_SUBJECT && $ACTION)
                {

                    $entriesSubject = self::dbAccess()->fetchAll("SELECT * FROM t_grade_subject WHERE SCHOOLYEAR='" . $COPY_LASTSCHOOLYEAR . "' AND GRADE='" . $gradeId . "'");

                    if ($newSchoolyearId)
                    {

                        if ($entriesSubject)
                        {
                            foreach ($entriesSubject as $value)
                            {

                                $SAVE_SUBJECT_DATA['SCHOOLYEAR'] = $newSchoolyearId;
                                $SAVE_SUBJECT_DATA['ASSIGNED_SUBJECT'] = $value->ASSIGNED_SUBJECT;
                                $SAVE_SUBJECT_DATA['SUBJECT_GUID'] = generateGuid();
                                $SAVE_SUBJECT_DATA['NUMBER_CREDIT'] = $value->NUMBER_CREDIT;
                                $SAVE_SUBJECT_DATA['GRADE'] = $value->GRADE;
                                $SAVE_SUBJECT_DATA['SUBJECT'] = $value->SUBJECT;
                                $SAVE_SUBJECT_DATA['DESCRIPTION'] = $value->DESCRIPTION;
                                $SAVE_SUBJECT_DATA['GOALS'] = $value->GOALS;
                                $SAVE_SUBJECT_DATA['OBJECTIVES'] = $value->OBJECTIVES;
                                $SAVE_SUBJECT_DATA['MATERIALS'] = $value->MATERIALS;
                                $SAVE_SUBJECT_DATA['BODY_OF_LESSON'] = $value->BODY_OF_LESSON;
                                $SAVE_SUBJECT_DATA['EVALUATION'] = $value->EVALUATION;
                                $SAVE_SUBJECT_DATA['DURATION'] = $value->DURATION;
                                $SAVE_SUBJECT_DATA['SORTKEY'] = $value->SORTKEY;
                                $SAVE_SUBJECT_DATA['NAME'] = $value->NAME;
                                $SAVE_SUBJECT_DATA['GRADING'] = $value->GRADING;
                                $SAVE_SUBJECT_DATA['SUBJECT_TYPE'] = $value->SUBJECT_TYPE;
                                $SAVE_SUBJECT_DATA['EDUCATION_TYPE'] = $value->EDUCATION_TYPE;
                                $SAVE_SUBJECT_DATA['QUALIFICATION_TYPE'] = $value->EDUCATION_TYPE;
                                $SAVE_SUBJECT_DATA['COEFF'] = $value->COEFF;
                                $SAVE_SUBJECT_DATA['NATIONAL_EXAM'] = $value->NATIONAL_EXAM;
                                $SAVE_SUBJECT_DATA['FORMULA_TYPE'] = $value->FORMULA_TYPE;
                                $SAVE_SUBJECT_DATA['COEFF_VALUE'] = $value->COEFF_VALUE;
                                $SAVE_SUBJECT_DATA['AVERAGE_FROM_SEMESTER'] = $value->AVERAGE_FROM_SEMESTER;
                                $SAVE_SUBJECT_DATA['SCORE_TYPE'] = $value->SCORE_TYPE;
                                $SAVE_SUBJECT_DATA['COEFF_VALUE'] = $value->COEFF_VALUE;
                                $SAVE_SUBJECT_DATA['INCLUDE_IN_EVALUATION'] = $value->INCLUDE_IN_EVALUATION;
                                $SAVE_SUBJECT_DATA['MAX_POSSIBLE_SCORE'] = displayNumberFormat($value->MAX_POSSIBLE_SCORE);
                                self::dbAccess()->insert('t_grade_subject', $SAVE_SUBJECT_DATA);
                            }
                        }
                    }
                }
                ////////////////////////////////////////////////////////////////
                //COPY ASSIGNMENT...
                ////////////////////////////////////////////////////////////////
                if ($COPY_ASSIGNMENT && $ACTION)
                {
                    $entriesAssignment = self::dbAccess()->fetchAll("SELECT * FROM t_assignment WHERE SCHOOLYEAR='" . $COPY_LASTSCHOOLYEAR . "' AND GRADE='" . $gradeId . "'");
                    if ($newSchoolyearId)
                    {

                        if ($entriesAssignment)
                        {
                            foreach ($entriesAssignment as $value)
                            {

                                $SAVE_ASSIGNMENT_DATA['SCHOOLYEAR'] = $newSchoolyearId;
                                $SAVE_ASSIGNMENT_DATA['GRADE'] = $value->GRADE;
                                $SAVE_ASSIGNMENT_DATA['STATUS'] = $value->STATUS;
                                $SAVE_ASSIGNMENT_DATA['NAME'] = $value->NAME;
                                $SAVE_ASSIGNMENT_DATA['SHORT'] = $value->SHORT;
                                $SAVE_ASSIGNMENT_DATA['SUBJECT'] = $value->SUBJECT;
                                $SAVE_ASSIGNMENT_DATA['INCLUDE_IN_EVALUATION'] = $value->INCLUDE_IN_EVALUATION;
                                $SAVE_ASSIGNMENT_DATA['EVALUATION_TYPE'] = $value->EVALUATION_TYPE;
                                $SAVE_ASSIGNMENT_DATA['COEFF_VALUE'] = $value->COEFF_VALUE;
                                $SAVE_ASSIGNMENT_DATA['SMS_SEND'] = $value->SMS_SEND;
                                $SAVE_ASSIGNMENT_DATA['DESCRIPTION'] = $value->DESCRIPTION;
                                $SAVE_ASSIGNMENT_DATA['CATEGORY'] = $value->CATEGORY;
                                $SAVE_ASSIGNMENT_DATA['TASK'] = $value->TASK;
                                self::dbAccess()->insert('t_assignment', $SAVE_ASSIGNMENT_DATA);
                            }
                        }
                    }
                }

                break;
        }

        return array(
            "success" => true
            , "objectId" => $objectId
            , "text" => $name
            , "leaf" => $leaf
            , "objecttype" => $objecttype
            , "iconCls" => $iconCls
            , "navtitle" => $navtitle
            , "cls" => $cls
            , "allowDelete" => true
        );
    }

    ///////////////////////////////////////////////////////´////////////////////
    //List: GradingMethods
    ////////////////////////////////////////////////////////////////////////////
    public function allGradingMethods($params)
    {

        $this->DB_GRADING_METHOD = GradingMethodDBAccess::getInstance();
        return $this->DB_GRADING_METHOD->allGradingMethods($params);
    }

    public function teachersByGrade($params)
    {

        $subjectId = $params["subjectId"];

        $SQL = "";
        $SQL .= " SELECT DISTINCT A.ID AS ID, CONCAT(A.LASTNAME,' ', A.FIRSTNAME) AS NAME, A.GENDER";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " LEFT JOIN t_members AS C ON C.ID=A.ID";
        $SQL .= " LEFT JOIN t_memberrole AS D ON D.ID=C.ROLE";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND D.TUTOR IN(1,2)";
        $SQL .= " ORDER BY A.LASTNAME";

        $result = self::dbAccess()->fetchAll($SQL);

        if ($result)
            foreach ($result as $value)
            {
                $data['id'] = "" . $value->ID . "";
                $data['text'] = $value->NAME;
                $data['leaf'] = true;

                $check = $this->checkTeacherByGrade($value->ID, $subjectId);

                if ($check)
                {
                    $data['checked'] = true;
                }
                else
                {
                    $data['checked'] = false;
                }

                $data['cls'] = "nodeText";

                if ($value->GENDER == 1)
                {
                    $data['iconCls'] = "icon-user_male";
                }
                else
                {
                    $data['iconCls'] = "icon-user_female";
                }

                $this->dataforjson[] = $data;
            }

        return $this->dataforjson;
    }

    protected function subjectsByGradeQuery($params)
    {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT DISTINCT A.ID AS ID ,A.NAME AS NAME";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_grade_subject AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.GRADE = '" . Zend_Registry::get('OBJECT_SCHOOLYEAR')->GRADE_ID . "'";

        if ($globalSearch)
        {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }

        return self::dbAccess()->fetchAll($SQL);
    }

    //vesna
    public function subjectsByGradeId($id)
    {
        $SQL = "";
        $SQL .= " SELECT DISTINCT A.ID AS ID ,A.NAME AS NAME";
        $SQL .= " FROM t_subject AS A";
        $SQL .= " LEFT JOIN t_grade_subject AS B ON A.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.GRADE = '" . $id . "'";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        $i = 0;
        if ($result)
            while (list($key, $row) = each($result))
            {
                $data[$i]["ID"] = $row->ID;
                $data[$i]["SUBJECT"] = $row->NAME;

                $i++;
            }

        return $data;
    }

    public function subjectsByGradeArray()
    {

        $result = $this->subjectsByGradeQuery(false);

        $data = array();

        $i = 0;
        if ($result)
            while (list($key, $row) = each($result))
            {
                $data[$i]["ID"] = $row->ID;
                $data[$i]["SUBJECT"] = $row->NAME;

                $i++;
            }

        return $data;
    }

    public function subjectsByGrade($params)
    {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $result = $this->subjectsByGradeQuery($params);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $data[$i]["SUBJECT"] = $value->NAME;
                $data[$i]["DETAILS"] = $this->getTeacherSubjectClass($value->ID);

                $i++;
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

    public function addTeachersInToSchoolyear($params)
    {

        $selectionIds = $params["checkItems"];
        $subjectId = $params["subjectId"];

        $this->removeTeachersFromSchoolYear($subjectId);

        if ($selectionIds != "")
        {
            $selectedTeachers = explode(",", $selectionIds);

            $selectedCount = 0;
            if ($selectedTeachers)
                foreach ($selectedTeachers as $teacherId)
                {

                    $this->addTeacherSchoolYear($teacherId, $subjectId);
                    $selectedCount++;
                }
        }
        else
        {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    protected function removeTeachersFromSchoolYear($subjectId)
    {

        $SQL = "
            DELETE FROM t_subject_teacher_class
            WHERE 1=1 
            AND GRADE ='" . Zend_Registry::get('OBJECT_SCHOOLYEAR')->GRADE_ID . "'
            AND SUBJECT ='" . $subjectId . "'
            AND SCHOOL_YEAR ='" . Zend_Registry::get('OBJECT_SCHOOLYEAR')->SCHOOL_YEAR . "'
            ";
        self::dbAccess()->query($SQL);
    }

    protected function addTeacherSchoolYear($teacherId, $subjectId)
    {

        $SAVEDATA['TEACHER'] = $teacherId;
        $SAVEDATA['SUBJECT'] = $subjectId;
        $SAVEDATA['GRADE'] = Zend_Registry::get('OBJECT_SCHOOLYEAR')->GRADE_ID;
        $SAVEDATA['SCHOOL_YEAR'] = Zend_Registry::get('OBJECT_SCHOOLYEAR')->SCHOOL_YEAR;
        self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);
    }

    protected function checkTeacherByGrade($teacherId, $subjectId)
    {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND TEACHER = '" . $teacherId . "'";
        $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        $SQL .= " AND GRADE = '" . Zend_Registry::get('OBJECT_SCHOOLYEAR')->GRADE_ID . "'";
        $SQL .= " AND SCHOOL_YEAR = '" . Zend_Registry::get('OBJECT_SCHOOLYEAR')->SCHOOL_YEAR . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result->C ? true : false;
    }

    protected function getTeacherSubjectClass($subjectId)
    {

        $SQL = "SELECT A.ID AS ID, CONCAT(A.LASTNAME,' ',A.FIRSTNAME,' (',C.NAME,')') AS NAME";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " LEFT JOIN t_grade AS C ON C.ID=B.ACADEMIC";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND B.SUBJECT = '" . $subjectId . "'";
        $SQL .= " AND C.OBJECT_TYPE = 'CLASS'";
        $SQL .= " AND B.GRADE = '" . Zend_Registry::get('OBJECT_SCHOOLYEAR')->GRADE_ID . "'";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $ii = $i + 1;
                $data[$i] = $ii . ") " . $value->NAME;

                $i++;
            }

        return implode("<BR>", $data);
    }

    public function getClassesByGradeId($gradeId, $schoolyearId)
    {
        $SQL = "";
        $SQL .= "SELECT ID, NAME";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND GRADE_ID='" . $gradeId . "'";
        $SQL .= " AND SCHOOL_YEAR='" . $schoolyearId . "'";
        $SQL .= " AND OBJECT_TYPE='CLASS'";
        $SQL .= " ORDER BY NAME";
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function actionSubjectTeacherClass($params)
    {

        $start = $params["start"] ? $params["start"] : "0";
        $limit = $params["limit"] ? $params["limit"] : "50";

        $gradeId = $params["gradeId"] ? addText($params["gradeId"]) : "0";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : 0;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;

        $DB_STAFF = StaffDBAccess::getInstance();

        $paramters["subjectId"] = $subjectId;
        $paramters["staffTypeStr"] = "3,4";

        $CLASS_LIST = $this->getClassesByGradeId($gradeId, $schoolyearId);
        $TEACHER_LIST = $DB_STAFF->queryAllStaffs($paramters, "A.ID", "A.LASTNAME");

        $data = array();
        $i = 0;
        if ($TEACHER_LIST)
            foreach ($TEACHER_LIST as $STAFF_OBJECT)
            {

                $data[$i]["ID"] = $STAFF_OBJECT->ID;
                if ($CLASS_LIST)
                    foreach ($CLASS_LIST as $classObject)
                    {

                        $share = $this->isTeacherInSchedule($STAFF_OBJECT->ID, $classObject->ID, $schoolyearId);
                        $data[$i]["TEACHER"] = iconInSchedule($share) . "&nbsp;" . $STAFF_OBJECT->NAME . "&nbsp;(" . $STAFF_OBJECT->CODE . ")";
                        $check = $this->findSubjectTeacherClass($STAFF_OBJECT->ID, $classObject->ID, $subjectId);
                        $data[$i]["CLASS_" . $classObject->ID . ""] = $check ? 1 : 0;
                    }

                $data[$i]["TEACHER"] = $STAFF_OBJECT->NAME . "&nbsp;(" . $STAFF_OBJECT->CODE . ")";

                $i++;
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

    ////////////////////////////////////////////////////////////////////////////
    // Set Subject - Teacher - Class
    ////////////////////////////////////////////////////////////////////////////
    public function setSubjectTeacherClass($params)
    {

        $teacherId = isset($params["id"]) ? addText($params["id"]) : 0;
        $gradeId = isset($params["gradeId"]) ? addText($params["gradeId"]) : 0;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : 0;
        $classId = isset($params["field"]) ? substr($params["field"], 6) : 0;
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : 0;

        $checkSchedule = $this->checkTeacherInSchedule($teacherId, $classId, $subjectId);

        if ($newValue == 1)
        {

            $check = $this->findSubjectTeacherClass($teacherId, $classId, $subjectId);
            $isSubjectInClass = $this->isSubjectInClass($classId, $subjectId);

            if ($check == 0 && $isSubjectInClass == 0)
            {

                $SQL = "";
                $SQL .= "INSERT INTO t_subject_teacher_class";
                $SQL .= " SET";
                $SQL .= " TEACHER='" . $teacherId . "'";
                $SQL .= ",GRADE='" . $gradeId . "'";
                $SQL .= ",ACADEMIC='" . $classId . "'";
                $SQL .= ",SUBJECT='" . $subjectId . "'";
                $SQL .= ",SCHOOLYEAR='" . $schoolyearId . "'";

                self::dbAccess()->query($SQL);
            }
        }
        else
        {

            ////////////////////////////////////////////////////////////////////
            // Teacher in Schedule?
            ////////////////////////////////////////////////////////////////////
            if (!$checkSchedule)
            {

                $SQL = "";
                $SQL .= "DELETE FROM t_subject_teacher_class";
                $SQL .= " WHERE";
                $SQL .= " TEACHER='" . $teacherId . "'";
                $SQL .= " AND GRADE='" . $gradeId . "'";
                $SQL .= " AND ACADEMIC='" . $classId . "'";
                $SQL .= " AND SUBJECT='" . $subjectId . "'";
                $SQL .= " AND SCHOOLYEAR='" . $schoolyearId . "'";

                self::dbAccess()->query($SQL);
            }
        }

        return array("success" => true);
    }

    protected function findSubjectTeacherClass($teacherId, $classId, $subjectId)
    {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND TEACHER = '" . $teacherId . "'";
        $SQL .= " AND ACADEMIC = '" . $classId . "'";
        $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function isSubjectInClass($classId, $subjectId)
    {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND ACADEMIC = '" . $classId . "'";
        $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    protected function removeScheduleFromClass($classId)
    {

        $SQL = "DELETE FROM ";
        $SQL .= " t_schedule";
        $SQL .= " WHERE ";
        $SQL .= " ACADEMIC_ID = '" . $classId . "'";
        self::dbAccess()->query($SQL);
    }

    protected function checkSchoolyearByGrade($gradeId, $schoolyearId)
    {

        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND SCHOOL_YEAR = '" . $schoolyearId . "'";
        $SQL .= " AND GRADE_ID = '" . $gradeId . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function saveWorkingDays($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $checkItems = isset($params["checkItems"]) ? $params["checkItems"] : 0;

        if ($checkItems)
        {

            $days = explode(",", $checkItems);

            $UPDATE_DATA["MO"] = in_array("MO", $days) ? 1 : 0;
            $UPDATE_DATA["TU"] = in_array("TU", $days) ? 1 : 0;
            $UPDATE_DATA["WE"] = in_array("WE", $days) ? 1 : 0;
            $UPDATE_DATA["TH"] = in_array("TH", $days) ? 1 : 0;
            $UPDATE_DATA["FR"] = in_array("FR", $days) ? 1 : 0;
            $UPDATE_DATA["SA"] = in_array("SA", $days) ? 1 : 0;
            $UPDATE_DATA["SU"] = in_array("SU", $days) ? 1 : 0;

            $WHERE[] = "ID = '" . $objectId . "'";
            self::dbAccess()->update('t_grade', $UPDATE_DATA, $WHERE);
        }

        return array("success" => true);
    }

    public function treeWorkingDays($params)
    {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $academicObject = self::findGradeFromId($objectId);

        $data = array();

        for ($i = 0; $i <= 6; $i++)
        {

            switch ($i)
            {

                case 0:
                    $dayName = MONDAY;
                    $dayId = "MO";
                    $checkDay = $academicObject->MO ? true : false;
                    break;
                case 1:
                    $dayName = TUESDAY;
                    $dayId = "TU";
                    $checkDay = $academicObject->TU ? true : false;
                    break;
                case 2:
                    $dayName = WEDNESDAY;
                    $dayId = "WE";
                    $checkDay = $academicObject->WE ? true : false;
                    break;
                case 3:
                    $dayName = THURSDAY;
                    $dayId = "TH";
                    $checkDay = $academicObject->TH ? true : false;
                    break;
                case 4:
                    $dayName = FRIDAY;
                    $dayId = "FR";
                    $checkDay = $academicObject->FR ? true : false;
                    break;
                case 5:
                    $dayName = SATURDAY;
                    $dayId = "SA";
                    $checkDay = $academicObject->SA ? true : false;
                    break;
                case 6:
                    $dayName = SUNDAY;
                    $dayId = "SU";
                    $checkDay = $academicObject->SU ? true : false;
                    break;
            }

            $data[$i]['id'] = $dayId;
            $data[$i]['text'] = $dayName;
            $data[$i]['leaf'] = true;
            $data[$i]['checked'] = $checkDay;
            $data[$i]['cls'] = "nodeText";
            $data[$i]['iconCls'] = "icon-tabs";
        }
        return $data;
    }

    public function jsonSubjectTeacherClass($params)
    {

        $start = $params["start"] ? $params["start"] : "0";
        $limit = $params["limit"] ? $params["limit"] : "50";

        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : 0;
        $gradeId = isset($params["gradeId"]) ? addText($params["gradeId"]) : 0;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;

        $SQL = "";
        $SQL .= " SELECT
            DISTINCT B.ID AS ID
            ,A.CODE AS CODE
            ,CONCAT(A.LASTNAME,' ', A.FIRSTNAME) AS NAME
            ,A.ID AS TEACHER_ID
            ,C.ID AS CLASSS_ID
            ,C.NAME AS CLASSS_NAME
            ";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " LEFT JOIN t_grade AS C ON C.ID=B.ACADEMIC";
        $SQL .= " LEFT JOIN t_grade AS D ON D.ID=B.GRADE";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.TUTOR IN(1,2)";
        $SQL .= " AND B.SUBJECT = '" . $subjectId . "'";
        $SQL .= " AND B.GRADE = '" . $gradeId . "'";
        $SQL .= " AND C.SCHOOL_YEAR = '" . $schoolyearId . "'";

        $SQL .= " ORDER BY A.LASTNAME";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $share = $this->isTeacherInSchedule($value->TEACHER_ID, $value->CLASSS_ID, $schoolyearId);

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["IN_SCHEDULE"] = iconInSchedule($share);
                $data[$i]["TEACHER"] = $value->NAME . "&nbsp;(" . $value->CODE . ")";
                $data[$i]["CLASSS_NAME"] = $value->CLASSS_NAME;

                $i++;
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

    public function checkTeacherInSchedule($teacherId, $classId, $subjectId, $gradingterm = false)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("C" => "COUNT(*)"));
        if ($classId)
            $SQL->where("ACADEMIC_ID = '" . $classId . "'");
        if ($subjectId)
            $SQL->where("SUBJECT_ID = '" . $subjectId . "'");
        if ($teacherId)
            $SQL->where("TEACHER_ID = '" . $teacherId . "'");
        if ($gradingterm)
            $SQL->where("TERM = '" . $gradingterm . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result->C ? 1 : 0;
    }

    protected function isTeacherInSchedule($teacherId, $classId, $schoolyearId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("C" => "COUNT(*)"));
        if ($teacherId)
            $SQL->where("TEACHER_ID = '" . $teacherId . "'");
        if ($classId)
            $SQL->where("ACADEMIC_ID = '" . $classId . "'");
        if ($schoolyearId)
            $SQL->where("SCHOOLYEAR_ID = '" . $schoolyearId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function gradesByCampus($params)
    {

        $campusId = isset($params["campusId"]) ? addText($params["campusId"]) : "0";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND CAMPUS_ID='" . $campusId . "'";
        $SQL .= " AND OBJECT_TYPE='GRADE'";
        $SQL .= " ORDER BY NAME";

        $result = self::dbAccess()->fetchAll($SQL);
        $data = array();
        $i = 0;

        $data[0]["ID"] = "0";
        $data[0]["NAME"] = "[---]";
        if ($result)
            foreach ($result as $value)
            {

                $data[$i + 1]["ID"] = $value->ID;
                $data[$i + 1]["NAME"] = $value->NAME;

                $i++;
            }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function getSQLSubjectTeacherClass($params, $groupby = false)
    {

        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : 0;
        $gradeId = isset($params["gradeId"]) ? addText($params["gradeId"]) : 0;

        $SQL = "";
        $SQL .= " SELECT
            DISTINCT B.ID AS ID
            ,A.CODE AS CODE
            ,A.FIRSTNAME AS FIRSTNAME
            ,A.LASTNAME AS LASTNAME
            ,CONCAT(A.LASTNAME,' ', A.FIRSTNAME) AS NAME
            ,A.ID AS TEACHER_ID
            ,C.ID AS CLASS_ID
            ,C.NAME AS CLASS_NAME
            ,E.ID AS SUBJECT_ID
            ,E.NAME AS SUBJECT_NAME
            ";

        $SQL .= " FROM t_staff AS A";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON A.ID=B.TEACHER";
        $SQL .= " LEFT JOIN t_grade AS C ON C.ID=B.ACADEMIC";
        $SQL .= " LEFT JOIN t_grade AS D ON D.ID=B.GRADE";
        $SQL .= " LEFT JOIN t_subject AS E ON E.ID=B.SUBJECT";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.TUTOR IN(1,2)";
        $SQL .= " AND E.SUBJECT_TYPE<>4";

        if ($teacherId)
            $SQL .= " AND B.TEACHER = '" . $teacherId . "'";
        if ($gradeId)
            $SQL .= " AND B.GRADE = '" . $gradeId . "'";
        if ($schoolyearId)
            $SQL .= " AND C.SCHOOL_YEAR = '" . $schoolyearId . "'";

        if ($groupby)
        {
            $SQL .= " GROUP BY $groupby";
        }

        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonAssignedTeachers($params)
    {

        $start = $params["start"] ? $params["start"] : "0";
        $limit = $params["limit"] ? $params["limit"] : "50";

        $schoolyearGradeId = isset($params["gradeId"]) ? addText($params["gradeId"]) : 0;
        $academicObject = self::findGradeFromId($schoolyearGradeId);

        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;

        $searchParams["gradeId"] = $academicObject->GRADE_ID;
        $searchParams["schoolyearId"] = $schoolyearId;
        $result = $this->getSQLSubjectTeacherClass($searchParams, "A.ID");

        $data = array();

        $i = 0;
        if ($result)
            foreach ($result as $value)
            {

                $share = $this->isTeacherInSchedule($value->TEACHER_ID, $value->CLASSS_ID, $schoolyearId);

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["IN_SCHEDULE"] = iconInSchedule($share);
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["TEACHER"] = $value->LASTNAME . ", " . $value->FIRSTNAME;
                $data[$i]["CLASSS_NAME"] = $value->CLASSS_NAME;

                $i++;
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

    public function checkSubjctGrade($objectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade_subject", array("C" => "COUNT(*)"));
        $SQL->where("GRADE = '" . $objectId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function checkStaffInClass($objectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("C" => "COUNT(*)"));
        $SQL->where("ACADEMIC = '" . $objectId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function checkStudentInClass($objectId)
    {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_schoolyear", array("C" => "COUNT(*)"));
        $SQL->where("CLASS = '" . $objectId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonTreeTeacherWorkingClass()
    {

        $searchParams["teacherId"] = Zend_Registry::get('USER')->ID;
        $entries = $this->getSQLSubjectTeacherClass($searchParams, "B.CLASS");

        $data = array();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $data[$i]['id'] = $value->CLASS_ID;
                $data[$i]['text'] = $value->CLASS_NAME;
                $data[$i]['leaf'] = true;
                $data[$i]['cls'] = "nodeText";
                $data[$i]['iconCls'] = "icon-blackboard";

                $i++;
            }
        }
        return $data;
    }

    public function jsonTreeTeacherWorkingSubject()
    {

        $DB_SCHEDULE = ScheduleDBAccess::getInstance();

        $searchParams["teacherId"] = Zend_Registry::get('USER')->ID;
        $entries = $this->getSQLSubjectTeacherClass($searchParams, "B.SUBJECT");

        $data = array();

        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {

                $CHECK_SUBJECTS_IN_SCHEDULE = $DB_SCHEDULE->getSubjectsInSchedule(
                        Zend_Registry::get('USER')->ID
                        , $value->CLASS_ID
                );

                if (in_array($value->SUBJECT_ID, $CHECK_SUBJECTS_IN_SCHEDULE))
                {

                    $data[$i]['id'] = $value->SUBJECT_ID;
                    $data[$i]['text'] = $value->SUBJECT_NAME;
                    $data[$i]['leaf'] = true;
                    $data[$i]['cls'] = "nodeText";
                    $data[$i]['iconCls'] = "icon-flag_pink";

                    $i++;
                }
            }
        }
        return $data;
    }

    public static function jsonTreeAllAcademicGradeSchoolyear($params)
    {

        $data = array();
        $node = isset($params["node"]) ? addText($params["node"]) : "";

        $SELECTION_A = array(
            "ID"
            , "CAMPUS_ID"
            , "GRADE_ID"
            , "NAME"
            , "SCHOOL_YEAR"
            , "OBJECT_TYPE"
            , "STATUS"
        );

        $SELECTION_B = array(
            "NAME AS SCHOOLYEAR_NAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => "t_grade"), $SELECTION_A);
        $SQL->joinLeft(array('B' => 't_academicdate'), 'A.SCHOOL_YEAR=B.ID', $SELECTION_B);
        $SQL->where('A.PARENT = ?', $node);
        $SQL->where("A.OBJECT_TYPE IN ('CAMPUS', 'GRADE', 'SCHOOLYEAR')");
        $SQL->order("A.SORTKEY ASC");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $schoolyearObject = AcademicDateDBAccess::getInstance();

        $i = 0;
        if ($result)
        {
            foreach ($result as $value)
            {

                $isCurrentYear = $schoolyearObject->isCurrentSchoolyear($value->SCHOOL_YEAR);
                $data[$i]['objectType'] = $value->OBJECT_TYPE;
                $data[$i]['schoolyearId'] = $value->SCHOOL_YEAR;

                switch ($value->OBJECT_TYPE)
                {
                    case "CAMPUS":
                        $data[$i]['id'] = $value->ID;
                        $data[$i]['text'] = setShowText($value->NAME);
                        $data[$i]['cls'] = "nodeCampus";
                        $data[$i]['objecttype'] = "CAMPUS";
                        $data[$i]['iconCls'] = "icon-bricks";
                        $data[$i]['bulletinCampusId'] = $value->CAMPUS_ID;
                        break;
                    case "GRADE":
                        $data[$i]['id'] = $value->ID;
                        $data[$i]['text'] = setShowText($value->NAME);
                        $data[$i]['cls'] = "nodeGrade";
                        $data[$i]['objecttype'] = "GRADE";
                        $data[$i]['iconCls'] = "icon-folder_magnify";
                        $data[$i]['campusId'] = $value->CAMPUS_ID;
                        break;
                    case "SCHOOLYEAR":
                        $data[$i]['id'] = "SCHOOLYEAR_" . $value->ID;
                        $gradeObject = AcademicDBAccess::findGradeFromId($value->GRADE_ID);

                        $data[$i]['text'] = setShowText($value->NAME);
                        $data[$i]['objecttype'] = "SCHOOLYEAR";

                        if ($gradeObject)
                        {
                            $data[$i]['title'] = setShowText($gradeObject->NAME) . " (" . setShowText($value->NAME) . ") ";
                        }
                        else
                        {
                            $data[$i]['title'] = setShowText($value->NAME);
                        }

                        $data[$i]['academicId'] = $value->ID;
                        $data[$i]['campusId'] = $value->CAMPUS_ID;
                        $data[$i]['gradeId'] = $value->GRADE_ID;
                        $data[$i]['schoolyearId'] = $value->SCHOOL_YEAR;

                        if ($isCurrentYear)
                        {
                            $data[$i]['cls'] = "nodeTextBoldBlue";
                        }
                        else
                        {
                            $data[$i]['cls'] = "nodeTextRedBold";
                        }

                        if ($value->STATUS == 1)
                        {
                            $data[$i]['iconCls'] = "icon-date";
                        }
                        else
                        {
                            $data[$i]['iconCls'] = "icon-date_edit";
                        }

                        $data[$i]['leaf'] = true;
                        break;
                }
                ++$i;
            }
        }
        return $data;
    }

}

?>