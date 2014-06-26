<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/CommunicationDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/SpecialDBAccess.php';
require_once setUserLoacalization();

class AcademicDBAccess {

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

    public function getGradeDataFromId($Id) {

        $data = array();

        $facette = self::findGradeFromId($Id);

        if ($facette) {

            ////////////////////////////////////////////////////////////////////
            $parent = self::findGradeFromId($facette->PARENT);

            if (isset($parent)) {
                $parentName = isset($parent->NAME) ? $parent->NAME : "";
            } else {
                $parentName = "";
            }

            $title = $parentName;

            switch ($facette->OBJECT_TYPE) {
                case "SCHOOLYEAR":
                    $DB_SCHOOLYEAR = AcademicDateDBAccess::getInstance();

                    if ($facette->SCHOOL_YEAR) {
                        $data["IS_CURRENT_YEAR"] = $DB_SCHOOLYEAR->isCurrentSchoolyear($facette->SCHOOL_YEAR);
                    } else {
                        $data["IS_CURRENT_YEAR"] = 0;
                    }

                    if ($facette->NAME) {
                        $data["NAME"] = $facette->NAME;
                    } else {
                        $data["NAME"] = "---";
                    }

                    break;
                case "SUBJECT":
                    if ($facette->SUBJECT_ID) {

                        $subjectObject = SubjectDBAccess::findSubjectFromId($facette->SUBJECT_ID);
                        if ($facette->NUMBER_CREDIT) {
                            $data["NUMBER_CREDIT"] = $facette->NUMBER_CREDIT;
                        } else {
                            if ($subjectObject) {
                                $data["SHORT_CODE"] = $subjectObject->SHORT;
                                $data["SUBJECT_NAME"] = $subjectObject->NAME;
                                $data["NUMBER_CREDIT"] = $subjectObject->NUMBER_CREDIT;
                                $data["SUBJECT_ID"] = $subjectObject->ID;
                            }
                        }

                        $title = $parentName . ": " . $subjectObject->NAME;
                        $data["NAME"] = setShowText($subjectObject->NAME);
                    } else {
                        $title = $parentName;
                        $data["NAME"] = setShowText($parentName);
                    }
                    break;
                default:
                    $data["NAME"] = setShowText($facette->NAME);
                    break;
            }

            $data["TITLE"] = $title;
            $data["COLOR"] = $facette->COLOR;
            $data["ID"] = $facette->ID;
            $data["SHORT"] = $facette->SHORT;
            $data["SORTKEY"] = $facette->SORTKEY;
            $data["GRADE_ID"] = $facette->GRADE_ID;
            $data["CAMPUS_ID"] = $facette->CAMPUS_ID;
            $data["EDUCATION_TYPE"] = $facette->EDUCATION_TYPE;
            $data["QUALIFICATION_TYPE"] = $facette->EDUCATION_TYPE;
            $data["SCHOOL_TYPE"] = $facette->SCHOOL_TYPE;

            $data["CODE"] = $facette->CODE;
            $data["STATUS"] = $facette->STATUS;

            if ($facette->EDUCATION_SYSTEM) {
                $data["EDUCATION_SYSTEM_NAME"] = NUMBER_CREDIT;
            } else {
                $data["EDUCATION_SYSTEM_NAME"] = TRADITIONAL;
            }
            $data["USE_OF_GROUPS"] = $facette->USE_OF_GROUPS ? true : false;
            $data["OBJECT_TYPE"] = $facette->OBJECT_TYPE;
            $data["SCHOOL_YEAR"] = $facette->SCHOOL_YEAR;
            $data["CLASS_TYPE"] = $facette->CLASS_TYPE;
            $data["NUMBER_OF_STUDENTS"] = setShowText($facette->NUMBER_OF_STUDENTS);
            $data["CONTACT_PERSON"] = setShowText($facette->CONTACT_PERSON);
            $data["BOARD_OF_EXAMINERS"] = setShowText($facette->BOARD_OF_EXAMINERS);
            $data["EMAIL"] = setShowText($facette->EMAIL);
            $data["PHONE"] = setShowText($facette->PHONE);
            $data["LEVEL"] = setShowText($facette->LEVEL);
            $data["PRE_REQUIREMENTS"] = setShowText($facette->PRE_REQUIREMENTS);
            $data["YEAR_RESULT"] = $facette->YEAR_RESULT;

            //@Math Man 17.01.2014
            if ($facette->OBJECT_TYPE == "CAMPUS" && UserAuth::getUserType() == "STUDENT") {
                $data["CAMPUS_NAME"] = setShowText($facette->NAME);
                $data["CAMPUS_SHORT"] = $facette->SHORT;
                $data["CAMPUS_CODE"] = $facette->CODE;
                $data["CAMPUS_CONTACT_PERSON"] = setShowText($facette->CONTACT_PERSON);
                $data["CAMPUS_EMAIL"] = setShowText($facette->EMAIL);
                $data["CAMPUS_PHONE"] = setShowText($facette->PHONE);
            }
            ///////////

            $data["MO"] = $facette->MO ? true : false;
            $data["TU"] = $facette->TU ? true : false;
            $data["WE"] = $facette->WE ? true : false;
            $data["TH"] = $facette->TH ? true : false;
            $data["FR"] = $facette->FR ? true : false;
            $data["SA"] = $facette->SA ? true : false;
            $data["SU"] = $facette->SU ? true : false;

            $data["SHOW_MO"] = $facette->MO ? YES : NO;
            $data["SHOW_TU"] = $facette->TU ? YES : NO;
            $data["SHOW_WE"] = $facette->WE ? YES : NO;
            $data["SHOW_TH"] = $facette->TH ? YES : NO;
            $data["SHOW_FR"] = $facette->FR ? YES : NO;
            $data["SHOW_SA"] = $facette->SA ? YES : NO;
            $data["SHOW_SU"] = $facette->SU ? YES : NO;

            $data["SEMESTER1_WEIGHTING"] = $facette->SEMESTER1_WEIGHTING ? $facette->SEMESTER1_WEIGHTING : 1;
            $data["SEMESTER2_WEIGHTING"] = $facette->SEMESTER2_WEIGHTING ? $facette->SEMESTER2_WEIGHTING : 1;
            $data["TERM1_WEIGHTING"] = $facette->TERM1_WEIGHTING ? $facette->TERM1_WEIGHTING : 1;
            $data["TERM2_WEIGHTING"] = $facette->TERM2_WEIGHTING ? $facette->TERM2_WEIGHTING : 1;

            $data["SCHOOLYEAR_START"] = getShowDate($facette->SCHOOLYEAR_START);
            $data["SCHOOLYEAR_END"] = getShowDate($facette->SCHOOLYEAR_END);

            $data["SEMESTER1_START"] = showSeconds2Date($facette->SEMESTER1_START);
            $data["SEMESTER1_END"] = showSeconds2Date($facette->SEMESTER1_END);
            $data["SEMESTER2_START"] = showSeconds2Date($facette->SEMESTER2_START);
            $data["SEMESTER2_END"] = showSeconds2Date($facette->SEMESTER2_END);

            $data["TERM1_START"] = showSeconds2Date($facette->TERM1_START);
            $data["TERM1_END"] = showSeconds2Date($facette->TERM1_END);
            $data["TERM2_START"] = showSeconds2Date($facette->TERM2_START);
            $data["TERM2_END"] = showSeconds2Date($facette->TERM2_END);
            $data["TERM3_START"] = showSeconds2Date($facette->TERM3_START);
            $data["TERM3_END"] = showSeconds2Date($facette->TERM3_END);

            $data["QUARTER1_START"] = showSeconds2Date($facette->QUARTER1_START);
            $data["QUARTER1_END"] = showSeconds2Date($facette->QUARTER1_END);
            $data["QUARTER2_START"] = showSeconds2Date($facette->QUARTER2_START);
            $data["QUARTER2_END"] = showSeconds2Date($facette->QUARTER2_END);
            $data["QUARTER3_START"] = showSeconds2Date($facette->QUARTER3_START);
            $data["QUARTER3_END"] = showSeconds2Date($facette->QUARTER3_END);
            $data["QUARTER4_START"] = showSeconds2Date($facette->QUARTER4_START);
            $data["QUARTER4_END"] = showSeconds2Date($facette->QUARTER4_END);

            $data["DISPLAY_MONTH_RESULT"] = $facette->DISPLAY_MONTH_RESULT ? true : false;
            $data["DISPLAY_FIRST_RESULT"] = $facette->DISPLAY_FIRST_RESULT ? true : false;
            $data["DISPLAY_SECOND_RESULT"] = $facette->DISPLAY_SECOND_RESULT ? true : false;
            $data["DISPLAY_THIRD_RESULT"] = $facette->DISPLAY_THIRD_RESULT ? true : false;
            $data["DISPLAY_FOURTH_RESULT"] = $facette->DISPLAY_FOURTH_RESULT ? true : false;
            $data["DISPLAY_YEAR_RESULT"] = $facette->DISPLAY_YEAR_RESULT ? true : false;
            $data["DISPLAY_GPA"] = $facette->DISPLAY_GPA ? true : false;
            $data["DISPLAY_GRADE_POINTS"] = $facette->DISPLAY_GRADE_POINTS ? true : false;
            $data["YEAR_MULTI_ENROLLMENT"] = $facette->YEAR_MULTI_ENROLLMENT ? true : false;
            $data["EVALUATION_TYPE"] = $facette->EVALUATION_TYPE;
            $data["EVALUATION_OPTION"] = $facette->EVALUATION_OPTION;
            $data["GRADING_TYPE"] = $facette->GRADING_TYPE;

            $data["PERFORMANCE_MONTH_DIVISION_VALUE"] = $facette->PERFORMANCE_MONTH_DIVISION_VALUE;
            $data["PERFORMANCE_FIRST_DIVISION_VALUE"] = $facette->PERFORMANCE_FIRST_DIVISION_VALUE;
            $data["PERFORMANCE_SECOND_DIVISION_VALUE"] = $facette->PERFORMANCE_SECOND_DIVISION_VALUE;
            $data["PERFORMANCE_THIRD_DIVISION_VALUE"] = $facette->PERFORMANCE_THIRD_DIVISION_VALUE;
            $data["PERFORMANCE_FOURTH_DIVISION_VALUE"] = $facette->PERFORMANCE_FOURTH_DIVISION_VALUE;

            $data["FORMULA_TERM"] = $facette->FORMULA_TERM;
            $data["FORMULA_YEAR"] = $facette->FORMULA_YEAR;

            $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($facette->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($facette->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($facette->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($facette->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($facette->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($facette->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($facette->DISABLED_BY);
        }

        return $data;
    }

    public static function sqlGradeFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array('*'));
        if (is_numeric($Id)) {
            $SQL->where("ID = ?", $Id ? $Id : 0);
        } else {
            $SQL->where("GUID = ?", $Id ? $Id : 0);
        }

        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function sqlSchoolyearSubjectFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_schoolyear_subject", array('*'));
        $SQL->where("ID = ?", $Id);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findGradeFromId($Id) {

        return self::sqlGradeFromId($Id, "DEFAULT");
    }

    public static function findSchoolyearSubjectFromId($Id) {

        return self::sqlSchoolyearSubjectFromId($Id, "DEFAULT");
    }

    public function findObjectCampusFromId($Id) {

        return self::sqlGradeFromId($Id, "CAMPUS");
    }

    public function findObjectGradeFromId($Id) {

        return self::sqlGradeFromId($Id, "GRADE");
    }

    public function findObjectGradeSchoolyearFromId($Id) {

        return self::sqlGradeFromId($Id, "GRADESCHOOLYEAR");
    }

    public function findObjectClassFromId($Id) {

        return self::sqlGradeFromId($Id, "CLASS");
    }

    public function findGradeFromCodeId($codeId) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', '*');
        $SQL->where("CODE='" . strtoupper(trim($codeId)) . "'");
        $SQL->limit(1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    /**
     * JSON: Student by StudentId....
     */
    public function loadGradeFromId($Id) {
        $result = self::findGradeFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getGradeDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public function removeNodeAndChildren($params) {

        $objectId = $params["objectId"];
        $academicObject = self::findGradeFromId($objectId);

        if ($academicObject) {
            switch ($academicObject->OBJECT_TYPE) {
                case "CAMPUS":
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_grade", array('*'));
                    $SQL->where("CAMPUS_ID = ?", $objectId);
                    $allRows = self::dbAccess()->fetchAll($SQL);
                    break;
                case "GRADE":
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_grade", array('*'));
                    $SQL->where("GRADE_ID = ?", $objectId);
                    $allRows = self::dbAccess()->fetchAll($SQL);
                    break;
                case "SCHOOLYEAR":
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_grade", array('*'));
                    $SQL->where("PARENT = ?", $objectId);
                    $allRows = self::dbAccess()->fetchAll($SQL);
                    break;
                default:
                    break;
            }
        }

        if (isset($allRows)) {
            foreach ($allRows as $row) {
                $paramsChild["ObjectType"] = $row->OBJECT_TYPE;
                $paramsChild["objectId"] = $row->ID;
                $this->removeNode($paramsChild);
            }
        }

        $params["ObjectType"] = $academicObject->OBJECT_TYPE;
        $this->removeNode($params);

        return array("success" => true);
    }

    public function removeNode($params) {

        $facette = self::findGradeFromId($params["objectId"]);

        if ($facette) {
            switch ($facette->OBJECT_TYPE) {
                case "CAMPUS":
                    self::dbAccess()->delete('t_staff_campus', array("CAMPUS='" . $facette->ID . "'"));
                    break;
                case "GRADE":
                    self::dbAccess()->delete('t_assignment', array("GRADE='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_grade_subject', array("GRADE='" . $facette->ID . "'"));
                    break;
                case "SCHOOLYEAR":
                    self::dbAccess()->delete('t_assignment', array("SCHOOLYEAR='" . $facette->SCHOOL_YEAR . "'"));
                    self::dbAccess()->delete('t_grade_subject', array("SCHOOLYEAR='" . $facette->SCHOOL_YEAR . "'"));
                    break;
                case "CLASS";
                case "SUBJECT";
                    self::dbAccess()->delete('t_student_attendance', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_subject_assessment', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_assignment', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_schoolyear', array("CLASS='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_schedule', array("ACADEMIC_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_subject_teacher_class', array("ACADEMIC='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_grade', array("PARENT='" . $facette->ID . "'"));

                    $DB_COMMUNICATION = CommunicationDBAccess::getInstance();
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_communication", array('*'));
                    $SQL->where("CLASS_ID = '" . $facette->ID . "'");
                    $allRows = self::dbAccess()->fetchAll($SQL);

                    if ($allRows) {
                        foreach ($allRows as $row) {
                            $DB_COMMUNICATION->removeStudentCommunication($row->ID);
                        }
                    }
                    self::dbAccess()->delete('t_communication', array("CLASS_ID='" . $facette->ID . "'"));

                    if ($facette->EDUCATION_SYSTEM) {

                        self::dbAccess()->delete('t_grade_subject', array(
                            "GRADE='" . $facette->GRADE_ID . "'"
                            , "SCHOOLYEAR='" . $facette->SCHOOL_YEAR . "'"
                            , "SUBJECT='" . $facette->SUBJECT_ID . "'"));
                    }

                    break;
            }

            self::dbAccess()->delete('t_grade', array("ID='" . $facette->ID . "'"));
        }
        return array("success" => true);
    }

    ////////////////////////////////////////////////////////////////////////////
    // Update academic...
    ////////////////////////////////////////////////////////////////////////////
    public function updateGrade($params) {

        $name = isset($params["NAME"]) ? addText($params["NAME"]) : "---";

        $objectId = $params["objectId"];
        $academicObject = self::findGradeFromId($objectId);
        $OBJECT_PARENT = self::findGradeFromId($academicObject->PARENT);

        if (isset($params["CODE"]))
            $SAVEDATA['CODE'] = addText($params["CODE"]);

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA['SORTKEY'] = addText($params["SORTKEY"]);

        if (isset($params["CLASS_TYPE"]))
            $SAVEDATA['CLASS_TYPE'] = addText($params["CLASS_TYPE"]);

        if (isset($params["CONTACT_PERSON"]))
            $SAVEDATA['CONTACT_PERSON'] = addText($params["CONTACT_PERSON"]);

        if (isset($params["PRE_REQUIREMENTS"]))
            $SAVEDATA['PRE_REQUIREMENTS'] = addText($params["PRE_REQUIREMENTS"]);

        if (isset($params["BOARD_OF_EXAMINERS"]))
            $SAVEDATA['BOARD_OF_EXAMINERS'] = addText($params["BOARD_OF_EXAMINERS"]);

        if (isset($params["NUMBER_OF_STUDENTS"]))
            $SAVEDATA['NUMBER_OF_STUDENTS'] = addText($params["NUMBER_OF_STUDENTS"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["PHONE"]))
            $SAVEDATA['PHONE'] = addText($params["PHONE"]);

        if (isset($params["LEVEL"]))
            $SAVEDATA['LEVEL'] = addText($params["LEVEL"]);

        if (isset($params["QUALIFICATION_TYPE"])) {
            $SAVEDATA['QUALIFICATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);
            $SAVEDATA['EDUCATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);
        }

        if (isset($params["SCHOOL_TYPE"]))
            $SAVEDATA['SCHOOL_TYPE'] = addText($params["SCHOOL_TYPE"]);

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        if (isset($params["SEMESTER1_WEIGHTING"]))
            $SAVEDATA['SEMESTER1_WEIGHTING'] = addText($params["SEMESTER1_WEIGHTING"]);

        if (isset($params["SEMESTER2_WEIGHTING"]))
            $SAVEDATA['SEMESTER2_WEIGHTING'] = addText($params["SEMESTER2_WEIGHTING"]);

        if (isset($params["TERM1_WEIGHTING"]))
            $SAVEDATA['TERM1_WEIGHTING'] = addText($params["TERM1_WEIGHTING"]);

        if (isset($params["TERM2_WEIGHTING"]))
            $SAVEDATA['TERM2_WEIGHTING'] = addText($params["TERM2_WEIGHTING"]);

        if (isset($params["EVALUATION_TYPE"]))
            $SAVEDATA['EVALUATION_TYPE'] = (int) $params["EVALUATION_TYPE"];

        if (isset($params["YEAR_RESULT"]))
            $SAVEDATA['YEAR_RESULT'] = addText($params["YEAR_RESULT"]);

        $SAVEDATA['USE_OF_GROUPS'] = isset($params["USE_OF_GROUPS"]) ? 1 : 0;

        $SAVEDATA['DISPLAY_MONTH_RESULT'] = isset($params["DISPLAY_MONTH_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_FIRST_RESULT'] = isset($params["DISPLAY_FIRST_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_SECOND_RESULT'] = isset($params["DISPLAY_SECOND_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_THIRD_RESULT'] = isset($params["DISPLAY_THIRD_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_FOURTH_RESULT'] = isset($params["DISPLAY_FOURTH_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_YEAR_RESULT'] = isset($params["DISPLAY_YEAR_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_GPA'] = isset($params["DISPLAY_GPA"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_GRADE_POINTS'] = isset($params["DISPLAY_GRADE_POINTS"]) ? 1 : 0;
        $SAVEDATA['YEAR_MULTI_ENROLLMENT'] = isset($params["YEAR_MULTI_ENROLLMENT"]) ? 1 : 0;

        if (isset($params["EVALUATION_OPTION"]))
            $SAVEDATA['EVALUATION_OPTION'] = addText($params["EVALUATION_OPTION"]);

        if (isset($params["GRADING_TYPE"]))
            $SAVEDATA['GRADING_TYPE'] = addText($params["GRADING_TYPE"]);

        if (isset($params["PERFORMANCE_MONTH_DIVISION_VALUE"]))
            $SAVEDATA['PERFORMANCE_MONTH_DIVISION_VALUE'] = addText($params["PERFORMANCE_MONTH_DIVISION_VALUE"]);
        if (isset($params["PERFORMANCE_FIRST_DIVISION_VALUE"]))
            $SAVEDATA['PERFORMANCE_FIRST_DIVISION_VALUE'] = addText($params["PERFORMANCE_FIRST_DIVISION_VALUE"]);
        if (isset($params["PERFORMANCE_SECOND_DIVISION_VALUE"]))
            $SAVEDATA['PERFORMANCE_SECOND_DIVISION_VALUE'] = addText($params["PERFORMANCE_SECOND_DIVISION_VALUE"]);
        if (isset($params["PERFORMANCE_THIRD_DIVISION_VALUE"]))
            $SAVEDATA['PERFORMANCE_THIRD_DIVISION_VALUE'] = addText($params["PERFORMANCE_THIRD_DIVISION_VALUE"]);
        if (isset($params["PERFORMANCE_FOURTH_DIVISION_VALUE"]))
            $SAVEDATA['PERFORMANCE_FOURTH_DIVISION_VALUE'] = addText($params["PERFORMANCE_FOURTH_DIVISION_VALUE"]);
        if (isset($params["FORMULA_TERM"]))
            $SAVEDATA['FORMULA_TERM'] = addText($params["FORMULA_TERM"]);
        if (isset($params["FORMULA_YEAR"]))
            $SAVEDATA['FORMULA_YEAR'] = addText($params["FORMULA_YEAR"]);

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        $WHERE = array();

        switch ($academicObject->OBJECT_TYPE) {

            case "CAMPUS":
                $name = addText($params["NAME"]);
                $SAVEDATA['TITLE'] = $name;
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
                $this->updateAllCampusChildren(self::findGradeFromId($params["objectId"]));

                break;

            case "GRADE":
                $name = addText($params["NAME"]);
                $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $name;
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
                $SAVEDATA['END_OF_GRADE'] = isset($params["END_OF_GRADE"]) ? 1 : 0;

                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);

                $this->updateAllGradeChildren(self::findGradeFromId($params["objectId"]));

                break;

            case "SCHOOLYEAR":
                $name = addText($params["NAME"]);
                $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $name;
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

                if (!$academicObject->EDUCATION_SYSTEM) {
                    $SAVEDATA['MO'] = isset($params["MO"]) ? 1 : 0;
                    $SAVEDATA['TU'] = isset($params["TU"]) ? 1 : 0;
                    $SAVEDATA['WE'] = isset($params["WE"]) ? 1 : 0;
                    $SAVEDATA['TH'] = isset($params["TH"]) ? 1 : 0;
                    $SAVEDATA['FR'] = isset($params["FR"]) ? 1 : 0;
                    $SAVEDATA['SA'] = isset($params["SA"]) ? 1 : 0;
                    $SAVEDATA['SU'] = isset($params["SU"]) ? 1 : 0;
                }

                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);

                $facette = self::findGradeFromId($params["objectId"]);
                self::updateAllSchoolyearChildren($facette);

                break;

            case "CLASS":

                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
                break;
            case "SUBJECT":

                $SAVEDATA['MO'] = isset($params["MO"]) ? 1 : 0;
                $SAVEDATA['TU'] = isset($params["TU"]) ? 1 : 0;
                $SAVEDATA['WE'] = isset($params["WE"]) ? 1 : 0;
                $SAVEDATA['TH'] = isset($params["TH"]) ? 1 : 0;
                $SAVEDATA['FR'] = isset($params["FR"]) ? 1 : 0;
                $SAVEDATA['SA'] = isset($params["SA"]) ? 1 : 0;
                $SAVEDATA['SU'] = isset($params["SU"]) ? 1 : 0;

                $subjectId = isset($params["SUBJECT_ID"]) ? addText($params["SUBJECT_ID"]) : "";
                $subjectObject = SubjectDBAccess::findSubjectFromId($subjectId);
                if ($subjectObject) {
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $subjectObject->NAME;
                } else {
                    $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $name;
                }

                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);

                $facette = self::findGradeFromId($params["objectId"]);
                self::updateAllSchoolyearSubjectChildren($facette);

                break;
        }

        return array(
            "success" => true
            , "text" => setShowText($name)
        );
    }

    public function findGradeIdFromObjectId($Id) {

        $facette = self::findGradeFromId($Id);
        return $facette->GRADE_ID;
    }

    public function findSchoolYearFromObjectId($Id) {

        $facette = self::findGradeFromId($Id);
        if ($facette->OBJECT_TYPE == "SCHOOL_YEAR") {
            return $facette->NAME;
        }
        return "";
    }

    protected function sqlEnrolledStudents($schoolyearId, $gradeId, $classId, $gender) {

        $gradeObject = self::findGradeFromId($gradeId);

        $SQL = "";
        $SQL .= " SELECT DISTINCT A.ID AS ID";
        $SQL .= " FROM t_student AS A";
        $SQL .= " LEFT JOIN t_student_schoolyear AS B ON A.ID=B.STUDENT";
        $SQL .= " LEFT JOIN t_grade AS C ON C.SCHOOL_YEAR=B.SCHOOL_YEAR";
        $SQL .= " WHERE 1=1";
        if ($schoolyearId)
            $SQL .= " AND C.SCHOOL_YEAR='" . $schoolyearId . "'";
        if ($gradeId)
            $SQL .= " AND B.GRADE='" . $gradeObject->GRADE_ID . "'";
        if ($classId)
            $SQL .= " AND B.CLASS='" . $classId . "'";
        if ($gender)
            $SQL .= " AND A.GENDER='" . $gender . "'";

        $SQL .= " ORDER BY A.LASTNAME";
        //error_log("KAOM VIBOLRITH ".$SQL,0);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function classesByGradeSchoolyear($gradeId, $schoolyearId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("GRADE_ID = ?", $gradeId);
        $SQL->where("OBJECT_TYPE = 'CLASS'");
        if ($schoolyearId)
            $SQL->where("SCHOOL_YEAR = ?", $schoolyearId);
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    //////////////////////

    protected function childrenByParent($Id, $objectType) {
        $facette = self::findGradeFromId($Id);
        $SQL = "SELECT * FROM t_grade ";

        switch ($objectType) {
            case "CAMPUS":
                $SQL .= "WHERE CAMPUS_ID ='" . $Id . "'";
                break;
            case "GRADE":
                $SQL .= "WHERE GRADE_ID ='" . $Id . "'";
                break;
            case "SCHOOLYEAR":
                $SQL .= "WHERE SCHOOL_YEAR ='" . $facette->SCHOOL_YEAR . "'";
                $SQL .= " AND GRADE_ID ='" . $facette->GRADE_ID . "'";
                $SQL .= " AND OBJECT_TYPE =  'CLASS'";
                break;
        }
        return self::dbAccess()->fetchAll($SQL);
    }

    public function classesComboData($gradeId) {
        $facette = self::findGradeFromId($gradeId);
        $SQL = "
		SELECT * 
		FROM  t_grade
		WHERE GRADE_ID ='" . $facette->GRADE_ID . " '
		AND OBJECT_TYPE =  'CLASS'
		";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result)
            foreach ($result as $value) {

                $data[$value->ID] = "['" . $value->ID . "','" . $value->NAME . "']";
            }

        $dataString = "[]";
        if ($data)
            $dataString = "[" . implode(",", $data) . "]";

        return $dataString;
    }

    public function searchClass($params) {

        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : 0;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : 0;
        $leftClass = isset($params["leftClass"]) ? $params["leftClass"] : '';

        $SQL = "";
        $SQL .= "SELECT * ";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND GRADE_ID = '" . $gradeId . "' AND OBJECT_TYPE = 'CLASS'";
        $SQL .= " AND SCHOOL_YEAR = '" . $schoolyearId . "'";
        if ($leftClass) {
            $SQL .= " AND ID <>'" . $leftClass . "'";
        }
        //error_log($SQL);
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function searchGrade($params) {

        $campusId = isset($params["campusId"]) ? addText($params["campusId"]) : 0;
        //@veasna
        $leftGrade = isset($params["gradeLeftId"]) ? $params["gradeLeftId"] : '';
        $leftSchoolYear = isset($params["leftSchoolYear"]) ? $params["leftSchoolYear"] : '';
        $rightSchoolYear = isset($params["rightSchoolYear"]) ? $params["rightSchoolYear"] : '';
        $check = ($leftSchoolYear == $rightSchoolYear) ? 1 : 0;

        $object = '';
        if ($leftGrade)
            $object = self::sqlGradeFromId($leftGrade);

        $SQL = "";
        $SQL .= "SELECT * ";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE 1=1";
        if ($campusId) {
            $SQL .= " AND CAMPUS_ID = '" . $campusId . "'";
        }

        $SQL .= " AND OBJECT_TYPE = 'GRADE'";
        $SQL .= " ORDER BY LEVEL ASC";

        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function releaseObject($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;

        $facette = self::findGradeFromId($objectId);
        $status = $facette->STATUS;
        $newStatus = 0;
        if ($facette) {
            $SQL = "";
            $SQL .= "UPDATE ";
            $SQL .= " t_grade";
            $SQL .= " SET";

            switch ($status) {
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

            $SQL .= " WHERE";
            $SQL .= " ID='" . $facette->ID . "'";

            self::dbAccess()->query($SQL);
        }
        return array("success" => true, "status" => $newStatus);
    }

    public function findGradeBySchoolyear($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("SCHOOL_YEAR = '" . $Id . "'");
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function allCampus() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'CAMPUS'");
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function allGrade($campusId = false) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        if ($campusId)
            $SQL->where("PARENT = '" . $campusId . "'");
        $SQL->where("OBJECT_TYPE = 'GRADE'");
        $SQL->order("SORTKEY ASC");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function allCampusComboData() {

        $result = self::allCampus();

        $data[0] = "[0,'[---]']";
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"" . addslashes($value->NAME) . "\"]";

                $i++;
            }

        return "[" . implode(",", $data) . "]";
    }

    public function getWorkingdayName($gradeObject, $shortDay) {

        return $gradeObject->$shortDay ? $shortDay : "---";
    }

    protected function updateAllCampusChildren($campusObject) {

        $ENTRIES = $this->childrenByParent($campusObject->ID, "CAMPUS");

        if ($ENTRIES)
            foreach ($ENTRIES as $value) {
                $SAVEDATA["YEAR_MULTI_ENROLLMENT"] = $campusObject->YEAR_MULTI_ENROLLMENT;
                $SAVEDATA["EDUCATION_TYPE"] = $campusObject->EDUCATION_TYPE;
                $SAVEDATA["QUALIFICATION_TYPE"] = $campusObject->QUALIFICATION_TYPE;
                $SAVEDATA["SCHOOL_TYPE"] = $campusObject->SCHOOL_TYPE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
            }

        return true;
    }

    protected function updateAllGradeChildren($gradeObject) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("PARENT = '" . $gradeObject->ID . "'");
        $SQL->where("OBJECT_TYPE = 'GRADE'");
        //error_log($SQL->__toString());
        $entries = self::dbAccess()->fetchAll($SQL);

        if ($entries) {
            foreach ($entries as $value) {

                $SAVEDATA["END_OF_GRADE"] = $gradeObject->END_OF_GRADE;
                $SAVEDATA["NUMBER_CREDIT"] = $gradeObject->NUMBER_CREDIT;
                $SAVEDATA["LEVEL"] = $gradeObject->LEVEL;
                $SAVEDATA["YEAR_MULTI_ENROLLMENT"] = $gradeObject->YEAR_MULTI_ENROLLMENT;
                $SAVEDATA["SEMESTER1_WEIGHTING"] = $gradeObject->SEMESTER1_WEIGHTING;
                $SAVEDATA["SEMESTER2_WEIGHTING"] = $gradeObject->SEMESTER2_WEIGHTING;
                $SAVEDATA["EDUCATION_TYPE"] = $gradeObject->EDUCATION_TYPE;
                $SAVEDATA["QUALIFICATION_TYPE"] = $gradeObject->QUALIFICATION_TYPE;
                $SAVEDATA["END_SCHOOL"] = $gradeObject->END_SCHOOL;

                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
            }
        }
    }

    public static function updateAllSchoolyearChildren($schoolyearObject) {

        if (isset($schoolyearObject->ID)) {
            $FIRST_SQL = self::dbAccess()->select();
            $FIRST_SQL->from("t_grade", array("*"));

            if ($schoolyearObject->EDUCATION_SYSTEM) {
                $FIRST_SQL->where("OBJECT_TYPE <>'CAMPUS'");
            } else {
                $FIRST_SQL->where("PARENT = '" . $schoolyearObject->ID . "'");
                $FIRST_SQL->where("OBJECT_TYPE = 'CLASS'");
            }

            //error_log($FIRST_SQL->__toString());
            $firstEntries = self::dbAccess()->fetchAll($FIRST_SQL);

            if ($firstEntries) {
                foreach ($firstEntries as $value) {

                    $FIRST_SAVEDATA["YEAR_MULTI_ENROLLMENT"] = $schoolyearObject->YEAR_MULTI_ENROLLMENT;
                    $FIRST_SAVEDATA["END_OF_GRADE"] = $schoolyearObject->END_OF_GRADE;
                    $FIRST_SAVEDATA["NUMBER_CREDIT"] = $schoolyearObject->NUMBER_CREDIT;
                    $FIRST_SAVEDATA["EDUCATION_TYPE"] = $schoolyearObject->EDUCATION_TYPE;
                    $FIRST_SAVEDATA["QUALIFICATION_TYPE"] = $schoolyearObject->QUALIFICATION_TYPE;
                    $FIRST_SAVEDATA["YEAR_RESULT"] = $schoolyearObject->YEAR_RESULT;
                    $FIRST_SAVEDATA["DISPLAY_GPA"] = $schoolyearObject->DISPLAY_GPA;
                    $FIRST_SAVEDATA["DISPLAY_GRADE_POINTS"] = $schoolyearObject->DISPLAY_GRADE_POINTS;

                    $FIRST_SAVEDATA["EVALUATION_TYPE"] = $schoolyearObject->EVALUATION_TYPE;
                    $FIRST_SAVEDATA['DISPLAY_MONTH_RESULT'] = $schoolyearObject->DISPLAY_MONTH_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_FIRST_RESULT'] = $schoolyearObject->DISPLAY_FIRST_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_SECOND_RESULT'] = $schoolyearObject->DISPLAY_SECOND_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_THIRD_RESULT'] = $schoolyearObject->DISPLAY_THIRD_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_FOURTH_RESULT'] = $schoolyearObject->DISPLAY_FOURTH_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_YEAR_RESULT'] = $schoolyearObject->DISPLAY_YEAR_RESULT;

                    $FIRST_SAVEDATA["SCHOOLYEAR_START"] = $schoolyearObject->SCHOOLYEAR_START;
                    $FIRST_SAVEDATA["SCHOOLYEAR_END"] = $schoolyearObject->SCHOOLYEAR_END;
                    $FIRST_SAVEDATA["SEMESTER1_START"] = $schoolyearObject->SEMESTER1_START;
                    $FIRST_SAVEDATA["SEMESTER1_END"] = $schoolyearObject->SEMESTER1_END;
                    $FIRST_SAVEDATA["SEMESTER2_START"] = $schoolyearObject->SEMESTER2_START;
                    $FIRST_SAVEDATA["SEMESTER2_END"] = $schoolyearObject->SEMESTER2_END;

                    if (!$schoolyearObject->EDUCATION_SYSTEM) {
                        $FIRST_SAVEDATA["MO"] = $schoolyearObject->MO;
                        $FIRST_SAVEDATA["TU"] = $schoolyearObject->TU;
                        $FIRST_SAVEDATA["WE"] = $schoolyearObject->WE;
                        $FIRST_SAVEDATA["TH"] = $schoolyearObject->TH;
                        $FIRST_SAVEDATA["FR"] = $schoolyearObject->FR;
                        $FIRST_SAVEDATA["SA"] = $schoolyearObject->SA;
                        $FIRST_SAVEDATA["SU"] = $schoolyearObject->SU;
                    }

                    $FIRST_SAVEDATA["SEMESTER1_WEIGHTING"] = $schoolyearObject->SEMESTER1_WEIGHTING;
                    $FIRST_SAVEDATA["SEMESTER2_WEIGHTING"] = $schoolyearObject->SEMESTER2_WEIGHTING;
                    $FIRST_SAVEDATA["EVALUATION_OPTION"] = $schoolyearObject->EVALUATION_OPTION;
                    $FIRST_SAVEDATA["GRADING_TYPE"] = $schoolyearObject->GRADING_TYPE;

                    $FIRST_SAVEDATA["PERFORMANCE_MONTH_DIVISION_VALUE"] = $schoolyearObject->PERFORMANCE_MONTH_DIVISION_VALUE;
                    $FIRST_SAVEDATA["PERFORMANCE_FIRST_DIVISION_VALUE"] = $schoolyearObject->PERFORMANCE_FIRST_DIVISION_VALUE;
                    $FIRST_SAVEDATA["PERFORMANCE_SECOND_DIVISION_VALUE"] = $schoolyearObject->PERFORMANCE_SECOND_DIVISION_VALUE;
                    $FIRST_SAVEDATA["PERFORMANCE_THIRD_DIVISION_VALUE"] = $schoolyearObject->PERFORMANCE_THIRD_DIVISION_VALUE;
                    $FIRST_SAVEDATA["PERFORMANCE_FOURTH_DIVISION_VALUE"] = $schoolyearObject->PERFORMANCE_FOURTH_DIVISION_VALUE;
                    $FIRST_SAVEDATA["FORMULA_TERM"] = $schoolyearObject->FORMULA_TERM;
                    $FIRST_SAVEDATA["FORMULA_YEAR"] = $schoolyearObject->FORMULA_YEAR;

                    $FIRST_WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                    self::dbAccess()->update('t_grade', $FIRST_SAVEDATA, $FIRST_WHERE);
                }
            }
        }

        $SECOND_SQL = self::dbAccess()->select();
        $SECOND_SQL->from("t_assignment", array("*"));
        $SECOND_SQL->where("GRADE = '" . $schoolyearObject->GRADE_ID . "'");
        $SECOND_SQL->where("SCHOOLYEAR = '" . $schoolyearObject->SCHOOL_YEAR . "'");
        //error_log($SQL->__toString());
        $secondEntries = self::dbAccess()->fetchAll($SECOND_SQL);

        if ($secondEntries) {
            foreach ($secondEntries as $value) {
                $SECOND_SAVEDATA['EVALUATION_TYPE'] = $schoolyearObject->EVALUATION_TYPE;
                $SECOND_WHERE[] = "ID = '" . $value->ID . "'";
                self::dbAccess()->update('t_assignment', $SECOND_SAVEDATA, $SECOND_WHERE);
            }
        }

        $THIRD_SQL = self::dbAccess()->select();
        $THIRD_SQL->from("t_grade_subject", array("*"));
        $THIRD_SQL->where("GRADE = '" . $schoolyearObject->GRADE_ID . "'");
        $THIRD_SQL->where("SCHOOLYEAR = '" . $schoolyearObject->SCHOOL_YEAR . "'");
        //error_log($SQL->__toString());
        $thirdEntries = self::dbAccess()->fetchAll($THIRD_SQL);

        if ($thirdEntries) {
            foreach ($thirdEntries as $value) {
                $THIRD_SAVEDATA['EVALUATION_TYPE'] = $schoolyearObject->EVALUATION_TYPE;
                $THIRD_WHERE[] = "ID = '" . $value->ID . "'";
                self::dbAccess()->update('t_grade_subject', $THIRD_SAVEDATA, $THIRD_WHERE);
            }
        }
    }

    public static function updateAllSchoolyearSubjectChildren($schoolyearsubjectObject) {

        if (isset($schoolyearsubjectObject->ID)) {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_grade", array("*"));
            $SQL->where("PARENT = '" . $schoolyearsubjectObject->ID . "'");
            //error_log($SQL->__toString());
            $entries = self::dbAccess()->fetchAll($SQL);

            if ($entries) {
                foreach ($entries as $value) {
                    $SAVEDATA["YEAR_MULTI_ENROLLMENT"] = $schoolyearsubjectObject->YEAR_MULTI_ENROLLMENT;
                    $SAVEDATA["NUMBER_CREDIT"] = $schoolyearsubjectObject->NUMBER_CREDIT;
                    $SAVEDATA["EDUCATION_TYPE"] = $schoolyearsubjectObject->EDUCATION_TYPE;
                    $SAVEDATA["QUALIFICATION_TYPE"] = $schoolyearsubjectObject->QUALIFICATION_TYPE;
                    $SAVEDATA["SEMESTER1_WEIGHTING"] = $schoolyearsubjectObject->SEMESTER1_WEIGHTING;
                    $SAVEDATA["SEMESTER2_WEIGHTING"] = $schoolyearsubjectObject->SEMESTER2_WEIGHTING;
                    $SAVEDATA["YEAR_RESULT"] = $schoolyearsubjectObject->YEAR_RESULT;
                    $SAVEDATA["EVALUATION_TYPE"] = $schoolyearsubjectObject->EVALUATION_TYPE;

                    $SAVEDATA['DISPLAY_MONTH_RESULT'] = $schoolyearsubjectObject->DISPLAY_MONTH_RESULT;
                    $SAVEDATA['DISPLAY_FIRST_RESULT'] = $schoolyearsubjectObject->DISPLAY_FIRST_RESULT;
                    $SAVEDATA['DISPLAY_SECOND_RESULT'] = $schoolyearsubjectObject->DISPLAY_SECOND_RESULT;
                    $SAVEDATA['DISPLAY_THIRD_RESULT'] = $schoolyearsubjectObject->DISPLAY_THIRD_RESULT;
                    $SAVEDATA['DISPLAY_FOURTH_RESULT'] = $schoolyearsubjectObject->DISPLAY_FOURTH_RESULT;
                    $SAVEDATA['DISPLAY_YEAR_RESULT'] = $schoolyearsubjectObject->DISPLAY_YEAR_RESULT;
                    $SAVEDATA["EVALUATION_OPTION"] = $schoolyearsubjectObject->EVALUATION_OPTION;
                    $SAVEDATA["GRADING_TYPE"] = $schoolyearsubjectObject->GRADING_TYPE;

                    $SAVEDATA["PERFORMANCE_MONTH_DIVISION_VALUE"] = $schoolyearsubjectObject->PERFORMANCE_MONTH_DIVISION_VALUE;
                    $SAVEDATA["PERFORMANCE_FIRST_DIVISION_VALUE"] = $schoolyearsubjectObject->PERFORMANCE_FIRST_DIVISION_VALUE;
                    $SAVEDATA["PERFORMANCE_SECOND_DIVISION_VALUE"] = $schoolyearsubjectObject->PERFORMANCE_SECOND_DIVISION_VALUE;
                    $SAVEDATA["PERFORMANCE_THIRD_DIVISION_VALUE"] = $schoolyearsubjectObject->PERFORMANCE_THIRD_DIVISION_VALUE;
                    $SAVEDATA["PERFORMANCE_FOURTH_DIVISION_VALUE"] = $schoolyearsubjectObject->PERFORMANCE_FOURTH_DIVISION_VALUE;
                    $SAVEDATA["FORMULA_TERM"] = $schoolyearsubjectObject->FORMULA_TERM;
                    $SAVEDATA["FORMULA_YEAR"] = $schoolyearsubjectObject->FORMULA_YEAR;

                    $SAVEDATA["NUMBER_CREDIT"] = $schoolyearsubjectObject->NUMBER_CREDIT;
                    $SAVEDATA["MO"] = $schoolyearsubjectObject->MO;
                    $SAVEDATA["TU"] = $schoolyearsubjectObject->TU;
                    $SAVEDATA["WE"] = $schoolyearsubjectObject->WE;
                    $SAVEDATA["TH"] = $schoolyearsubjectObject->TH;
                    $SAVEDATA["FR"] = $schoolyearsubjectObject->FR;
                    $SAVEDATA["SA"] = $schoolyearsubjectObject->SA;
                    $SAVEDATA["SU"] = $schoolyearsubjectObject->SU;
                    $SAVEDATA["USE_OF_GROUPS"] = $schoolyearsubjectObject->USE_OF_GROUPS;
                    $SAVEDATA["SUBJECT_ID"] = $schoolyearsubjectObject->SUBJECT_ID;

                    $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                    self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
                }
            }
        }
    }

    public function jsonSearchGrade($params) {

        $notNull = isset($params["nutNull"]) ? true : false;
        $searchType = isset($params["searchType"]) ? $params["searchType"] : "";

        switch ($searchType) {
            case "CLASS":
                $result = $this->searchClass($params);
                break;
            case "GRADE":
                $result = $this->searchGrade($params);
                break;
        }

        $data = array();
        $i = 0;

        if ($notNull) {
            if ($result) {
                foreach ($result as $key => $value) {
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["NAME"] = setShowText($value->NAME);

                    $i++;
                }
            }
        } else {
            $data[0]["ID"] = "0";
            $data[0]["NAME"] = "[---]";
            if ($result) {
                foreach ($result as $key => $value) {

                    $data[$i + 1]["ID"] = $value->ID;
                    $data[$i + 1]["NAME"] = setShowText($value->NAME);

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

    public function checkCurrentSchoolyearByClass($Id) {

        $facette = self::findGradeFromId($Id);

        $SQL = "SELECT COUNT(*) AS C FROM t_academicdate";
        $SQL .= " WHERE (START<=DATE(NOW()) AND END>=DATE(NOW()))";
        $SQL .= " AND ID='" . $facette->SCHOOL_YEAR . "'";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonEnrollmentType($params) {

        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : 0;
        $facette = self::findGradeFromId($gradeId);

        $data = array();

        switch ($facette->END_SCHOOL) {
            case 2:
            case 3:
                $data[0]["ID"] = 3;
                $data[0]["NAME"] = setICONV(UNDERGRADUATE_STUDENTS);
                $data[1]["ID"] = 5;
                $data[1]["NAME"] = setICONV(SCHOOL_LEAVING_EXAM);
                break;
            case 1:
            default:
                $data[0]["ID"] = 1;
                $data[0]["NAME"] = setICONV(GRADUATED_STUDENTS);
                $data[1]["ID"] = 3;
                $data[1]["NAME"] = setICONV(UNDERGRADUATE_STUDENTS);
                break;
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function getClassEducationType($id, $isJson = false) {

        $SQL = "SELECT EDUCATION_TYPE FROM t_grade";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND ID='" . $id . "'";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);

        $json = array('success' => true,
            'education_type' => $result ? $result->EDUCATION_TYPE : 0);

        if ($isJson == false)
            return $result ? $result->EDUCATION_TYPE : 0;
        else
            return $json;
    }

    public function sqlSubjectGradeByTeacherId($params) {
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";

        $DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $select = $DB_ACCESS->select();
        $select->from('t_subject_teacher_class');

        if ($teacherId)
            $select->where('TEACHER = ?', $teacherId);
        if ($schoolyearId)
            $select->where('SCHOOLYEAR = ?', $schoolyearId);

        //$select->__toString();
        $stmt = $DB_ACCESS->query($select);

        return $stmt->fetchAll();
    }

    public function sqlGradeWorkingDays($gradeId) {

        $DB_ACCESS = Zend_Registry::get('DB_ACCESS');

        $selectColumns = array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU');
        $select = $DB_ACCESS->select();
        $select->distinct();
        $select->from('t_grade', $selectColumns);

        $select->where("ID = ?", $gradeId);
        //echo $this->SELECT->__toString();
        $stmt = $DB_ACCESS->query($select);

        $result = $stmt->fetch();

        return isset($result) ? $result : null;
    }

    public function checkDatelineSendSMS($Id, $term) {

        $SQL = "SELECT COUNT(*) AS C FROM t_grade";
        switch ($term) {
            case "FIRST_SEMESTER":
                $SQL .= " WHERE FIRST_SCORE_END<=DATE(NOW())";
                break;
            case "SECOND_SEMESTER":
                $SQL .= " WHERE SECOND_SCORE_END<=DATE(NOW())";
                break;
            case "YEAR":
                $SQL .= " WHERE YEAR_SCORE_END<=DATE(NOW())";
                break;
        }

        $SQL .= " AND ID='" . $Id . "'";
        self::dbAccess()->fetchRow($SQL);
    }

    public function checkTeacherScoreEnter($teacherId, $gradeId, $term, $startDate, $endDate) {

        $SQL1 = "SELECT DISTINCT A.SUBJECT_ID";
        $SQL1 .= " FROM t_student_assignment AS A";
        $SQL1 .= " LEFT JOIN t_grade AS B ON A.CLASS_ID=B.ID";
        $SQL1 .= " LEFT JOIN t_subject_teacher_class AS C ON A.SUBJECT_ID=C.SUBJECT";
        $SQL1 .= " WHERE A.TEACHER_ID = '" . $teacherId . "'";
        $SQL1 .= " AND B.GRADE_ID = '" . $gradeId . "'";
        $SQL1 .= " AND A.CREATED_DATE BETWEEN '" . setDate2DB($startDate) . "' AND '" . setDate2DB($endDate) . "' ";
        $result1 = self::dbAccess()->fetchAll($SQL1);

        $SQL2 = "SELECT DISTINCT SUBJECT";
        $SQL2 .= " FROM t_subject_teacher_class";
        $SQL2 .= " WHERE TEACHER = '" . $teacherId . "'";
        $SQL2 .= " AND GRADE = '" . $gradeId . "'";
        $SQL2 .= " AND GRADINGTERM = '" . $term . "'";
        $SQL2 .= " GROUP BY SUBJECT";
        $result2 = self::dbAccess()->fetchAll($SQL2);

        $data2 = array();
        if ($result2) {
            foreach ($result2 as $key => $value) {
                $data2[$value->SUBJECT] = $value->SUBJECT;
            }
        }

        $data1 = array();
        if ($result1) {
            foreach ($result1 as $key => $value) {
                if (in_array($value->SUBJECT_ID, $data2)) {
                    $data1[$value->SUBJECT_ID] = $value->SUBJECT_ID;
                }
            }
        }
        return $data1 ? count($data1) : 0;
    }

    public function countSubjectsByTeacher($teacherId, $gradeId, $term) {

        $SQL = "SELECT DISTINCT SUBJECT";
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE TEACHER = '" . $teacherId . "'";
        $SQL .= " AND GRADE = '" . $gradeId . "'";
        $SQL .= " AND GRADINGTERM = '" . $term . "'";
        $SQL .= " GROUP BY SUBJECT";
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public function jsonCheckTeacherScoreEnter($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
        $term = isset($params["term"]) ? addText($params["term"]) : "";
        $startDate = isset($params["startDate"]) ? addText($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? addText($params["endDate"]) : "";

        $SQL = "SELECT DISTINCT
		C.NAME as CLASS_NAME
		,C.ID AS CLASS_ID
		,A.FIRSTNAME as FIRSTNAME
		,A.LASTNAME as LASTNAME
		,A.ID AS TEACHER_ID
		";

        $SQL .= " FROM t_staff AS A ";
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B on A.ID = B.TEACHER ";
        $SQL .= " LEFT JOIN t_grade AS C on C.ID = B.ACADEMIC ";
        $SQL .= " WHERE B.SCHOOLYEAR = '" . $schoolyearId . "'";
        $SQL .= " AND B.GRADE = '" . $gradeId . "' ";
        $SQL .= " AND B.GRADINGTERM = '" . $term . "' ";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $COUNT_SUBJECTS_SCORE = $this->checkTeacherScoreEnter(
                        $value->TEACHER_ID
                        , $gradeId
                        , $term
                        , $startDate
                        , $endDate
                );
                $COUNT_SUBJECTS_TEACHER = $this->countSubjectsByTeacher(
                        $value->TEACHER_ID
                        , $gradeId
                        , $term
                );

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["SCORE_SUBJECT_COUNT"] = "<B>" . $COUNT_SUBJECTS_SCORE . "</B>";
                $data[$i]["SUBJECT_COUNT"] = "<B>" . $COUNT_SUBJECTS_TEACHER . "</B>";
                $data[$i]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;
                $data[$i]["CLASS"] = $value->CLASS_NAME;

                $i++;
            }
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

    public static function findClass($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("ID = ?", $Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findAcademicFromGuId($GuId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("GUID = ?", $GuId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function setGuId() {

        $result1 = self::dbAccess()->fetchAll("SELECT * FROM t_grade");
        if ($result1) {
            foreach ($result1 as $value) {
                self::dbAccess()->query("UPDATE t_grade SET GUID='" . generateGuid() . "' WHERE ID='" . $value->ID . "'");
            }
        }

        $result2 = self::dbAccess()->fetchAll("SELECT * FROM t_subject");
        if ($result2) {
            foreach ($result2 as $value) {
                self::dbAccess()->query("UPDATE t_subject SET GUID='" . generateGuid() . "' WHERE ID='" . $value->ID . "'");
            }
        }
    }

    public static function findCampusSchoolyear($campusId, $schoolyearId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SCHOOLYEAR'");
        $SQL->where("CAMPUS_ID = ?", $campusId);
        $SQL->where("SCHOOL_YEAR = ?", $schoolyearId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findGradeSchoolyear($gradeId, $schoolyearId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SCHOOLYEAR'");
        $SQL->where("GRADE_ID = ?", $gradeId);
        $SQL->where("SCHOOL_YEAR = ?", $schoolyearId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonInstructorsByClass($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $academicId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $SQL = "SELECT DISTINCT
		A.ID AS TEACHER_ID
		,A.CODE AS CODE
		,A.FIRSTNAME as FIRSTNAME
		,A.LASTNAME as LASTNAME
		";

        $SQL .= " FROM t_members AS A ";
        $SQL .= " LEFT JOIN t_memberrole AS B on B.ID = A.ROLE";
        $SQL .= " LEFT JOIN t_instructor AS C on A.ID = C.TEACHER";
        $SQL .= " WHERE B.ID = 2 AND A.STATUS=1 OR B.PARENT=2";

        if ($globalSearch) {

            $SQL .= " AND ((A.NAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SQL .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SQL .= " ) ";
        }

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["CHECKED"] = self::checkClassInstructor($value->TEACHER_ID, $academicId) ? 1 : 0;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;

                $i++;
            }
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

    public static function checkClassInstructor($instructorId, $classId) {

        $academicObject = self::findGradeFromId($classId);
        $SQL = "SELECT count(*) AS C";
        $SQL .= " FROM t_instructor";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND TEACHER = '" . $instructorId . "'";
        $SQL .= " AND CLASS = '" . $academicObject->ID . "'";
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getClassByInstrutor($instrutorId) {

        $SQL = "SELECT *";
        $SQL .= " FROM t_instructor";
        $SQL .= " WHERE TEACHER = '" . $instrutorId . "'";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function actionClassInstructor($params) {

        $teacherId = isset($params["id"]) ? addText($params["id"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $academicObject = self::findGradeFromId($objectId);

        if ($academicObject && $teacherId) {
            self::dbAccess()->delete('t_instructor', array("TEACHER='" . $teacherId . "'", "CLASS='" . $academicObject->ID . "'"));
            $SAVE_DATA["CLASS"] = $academicObject->ID;
            $SAVE_DATA["TEACHER"] = $teacherId;
            if ($newValue)
                self::dbAccess()->insert('t_instructor', $SAVE_DATA);
        }

        return array(
            "success" => true
        );
    }

    public static function listClassesByInstructorSchoolyear($instructorId, $schoolyearId) {

        $SQL = "SELECT *";
        $SQL .= " FROM t_instructor AS A";
        $SQL .= " LEFT JOIN t_grade AS B on A.CLASS = B.ID";
        $SQL .= " WHERE B.SCHOOL_YEAR = '" . $schoolyearId . "'";
        $SQL .= " AND A.TEACHER = '" . $instructorId . "'";
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function listClassByGradeSchoolyear($gradeId, $schoolyearId) {

        $SQL = "SELECT *";
        $SQL .= " FROM t_grade";
        $SQL .= " WHERE GRADE_ID = '" . $gradeId . "'";
        $SQL .= " AND SCHOOL_YEAR = '" . $schoolyearId . "'";
        $SQL .= " AND OBJECT_TYPE = 'CLASS'";
        return self::dbAccess()->fetchAll($SQL);
    }

    //@Sea Peng
    public static function sqlAllSchoolYearQuery() {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_academicdate", array('*'));
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findAcademicBetweenDate($date) {

        $SQL = "SELECT *";
        $SQL .= " FROM t_academicdate";
        $SQL .= " WHERE START <= '" . $date . "' AND END>='" . $date . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function mappingAcademicSchoolyear($academicObject, $schoolyearObject = false) {

        if (!$schoolyearObject) {
            $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($academicObject->SCHOOL_YEAR);
        }
        if ($academicObject && $schoolyearObject) {
            $SAVEDATA['SCHOOLYEAR_START'] = $schoolyearObject->START;
            $SAVEDATA['SCHOOLYEAR_END'] = $schoolyearObject->END;
            $termNumber = self::findAcademicTerm($academicObject->SCHOOL_YEAR);

            switch ($termNumber) {
                case 1:
                    if (!$academicObject->TERM1_START) {
                        if (strtotime($schoolyearObject->TERM1_START)) {
                            if (strtotime($schoolyearObject->TERM1_START))
                                $SAVEDATA['TERM1_START'] = strtotime($schoolyearObject->TERM1_START);
                        }
                    }
                    if (!$academicObject->TERM1_END) {
                        if (strtotime($schoolyearObject->TERM1_END)) {
                            if (strtotime($schoolyearObject->TERM1_END))
                                $SAVEDATA['TERM1_END'] = strtotime($schoolyearObject->TERM1_END);
                        }
                    }

                    if (!$academicObject->TERM2_START) {
                        if (strtotime($schoolyearObject->TERM2_START)) {
                            if (strtotime($schoolyearObject->TERM2_START))
                                $SAVEDATA['TERM2_START'] = strtotime($schoolyearObject->TERM2_START);
                        }
                    }
                    if (!$academicObject->TERM2_END) {
                        if (strtotime($schoolyearObject->TERM2_END)) {
                            if (strtotime($schoolyearObject->TERM2_END))
                                $SAVEDATA['TERM2_END'] = strtotime($schoolyearObject->TERM2_END);
                        }
                    }

                    if (!$academicObject->TERM3_START) {
                        if (strtotime($schoolyearObject->TERM3_START)) {
                            if (strtotime($schoolyearObject->TERM3_START))
                                $SAVEDATA['TERM3_START'] = strtotime($schoolyearObject->TERM3_START);
                        }
                    }
                    if (!$academicObject->TERM3_END) {
                        if (strtotime($schoolyearObject->TERM3_END)) {
                            if (strtotime($schoolyearObject->TERM3_END))
                                $SAVEDATA['TERM3_END'] = strtotime($schoolyearObject->TERM3_END);
                        }
                    }
                    break;
                case 2:
                    if (!$academicObject->QUARTER1_START) {
                        if (strtotime($schoolyearObject->QUARTER1_START)) {
                            if (strtotime($schoolyearObject->QUARTER1_START))
                                $SAVEDATA['QUARTER1_START'] = strtotime($schoolyearObject->QUARTER1_START);
                        }
                    }
                    if (!$academicObject->QUARTER1_END) {
                        if (strtotime($schoolyearObject->QUARTER1_END)) {
                            if (strtotime($schoolyearObject->QUARTER1_END))
                                $SAVEDATA['QUARTER1_END'] = strtotime($schoolyearObject->QUARTER1_END);
                        }
                    }
                    if (!$academicObject->QUARTER2_START) {
                        if (strtotime($schoolyearObject->QUARTER2_START)) {
                            if (strtotime($schoolyearObject->QUARTER2_START))
                                $SAVEDATA['QUARTER2_START'] = strtotime($schoolyearObject->QUARTER2_START);
                        }
                    }
                    if (!$academicObject->QUARTER2_END) {
                        if (strtotime($schoolyearObject->QUARTER2_END)) {
                            if (strtotime($schoolyearObject->QUARTER2_END))
                                $SAVEDATA['QUARTER2_END'] = strtotime($schoolyearObject->QUARTER2_END);
                        }
                    }
                    if (!$academicObject->QUARTER3_START) {
                        if (strtotime($schoolyearObject->QUARTER3_START)) {
                            if (strtotime($schoolyearObject->QUARTER3_START))
                                $SAVEDATA['QUARTER3_START'] = strtotime($schoolyearObject->QUARTER3_START);
                        }
                    }
                    if (!$academicObject->QUARTER3_END) {
                        if (strtotime($schoolyearObject->QUARTER3_END)) {
                            if (strtotime($schoolyearObject->QUARTER3_END))
                                $SAVEDATA['QUARTER3_END'] = strtotime($schoolyearObject->QUARTER3_END);
                        }
                    }
                    if (!$academicObject->QUARTER4_START) {
                        if (strtotime($schoolyearObject->QUARTER4_START)) {
                            if (strtotime($schoolyearObject->QUARTER4_START))
                                $SAVEDATA['QUARTER4_START'] = strtotime($schoolyearObject->QUARTER4_START);
                        }
                    }
                    if (!$academicObject->QUARTER4_END) {
                        if (strtotime($schoolyearObject->QUARTER4_END)) {
                            if (strtotime($schoolyearObject->QUARTER4_END))
                                $SAVEDATA['QUARTER4_END'] = strtotime($schoolyearObject->QUARTER4_END);
                        }
                    }
                    break;
                default:
                    if (!$academicObject->SEMESTER1_START) {
                        if (strtotime($schoolyearObject->SEMESTER1_START)) {
                            if (strtotime($schoolyearObject->SEMESTER1_START))
                                $SAVEDATA['SEMESTER1_START'] = strtotime($schoolyearObject->SEMESTER1_START);
                        }
                    }
                    if (!$academicObject->SEMESTER1_END) {
                        if (strtotime($schoolyearObject->SEMESTER1_END)) {
                            if (strtotime($schoolyearObject->SEMESTER1_END))
                                $SAVEDATA['SEMESTER1_END'] = strtotime($schoolyearObject->SEMESTER1_END);
                        }
                    }

                    if (!$academicObject->SEMESTER2_START) {
                        if (strtotime($schoolyearObject->SEMESTER2_START)) {
                            if (strtotime($schoolyearObject->SEMESTER2_START))
                                $SAVEDATA['SEMESTER2_START'] = strtotime($schoolyearObject->SEMESTER2_START);
                        }
                    }

                    if (!$academicObject->SEMESTER2_END) {
                        if (strtotime($schoolyearObject->SEMESTER2_END)) {
                            if (strtotime($schoolyearObject->SEMESTER2_END))
                                $SAVEDATA['SEMESTER2_END'] = strtotime($schoolyearObject->SEMESTER2_END);
                        }
                    }
                    break;
            }

            if ($academicObject->EDUCATION_SYSTEM) {
                $WHERE[] = "CAMPUS_ID = '" . $academicObject->CAMPUS_ID . "'";
                $WHERE[] = "SCHOOL_YEAR = '" . $schoolyearObject->ID . "'";
            } else {
                $WHERE[] = "GRADE_ID = '" . $academicObject->GRADE_ID . "'";
                $WHERE[] = "SCHOOL_YEAR = '" . $schoolyearObject->ID . "'";
            }

            self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
            self::mappingDateTerm2Schedule($academicObject->ID);
            self::updateAllSchoolyearChildren($academicObject->ID);
        }
    }

    public static function saveSchoolyearDateSetting($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $academicObject = self::findGradeFromId($objectId);
        $termNumber = self::findAcademicTerm($academicObject->SCHOOL_YEAR);

        switch ($termNumber) {
            case 1:

                if (isset($params["TERM1_START"]))
                    $SAVEDATA['TERM1_START'] = strtotime(setDate2DB($params["TERM1_START"]));
                if (isset($params["TERM1_END"]))
                    $SAVEDATA['TERM1_END'] = strtotime(setDate2DB($params["TERM1_END"]));

                if (isset($params["TERM2_START"]))
                    $SAVEDATA['TERM2_START'] = strtotime(setDate2DB($params["TERM2_START"]));
                if (isset($params["TERM2_END"]))
                    $SAVEDATA['TERM2_END'] = strtotime(setDate2DB($params["TERM2_END"]));

                if (isset($params["TERM3_START"]))
                    $SAVEDATA['TERM3_START'] = strtotime(setDate2DB($params["TERM3_START"]));
                if (isset($params["TERM3_END"]))
                    $SAVEDATA['TERM3_END'] = strtotime(setDate2DB($params["TERM3_END"]));

                break;
            case 2:

                if (isset($params["QUARTER1_START"]))
                    $SAVEDATA['QUARTER1_START'] = strtotime(setDate2DB($params["QUARTER1_START"]));
                if (isset($params["QUARTER1_END"]))
                    $SAVEDATA['QUARTER1_END'] = strtotime(setDate2DB($params["QUARTER1_END"]));

                if (isset($params["QUARTER2_START"]))
                    $SAVEDATA['QUARTER2_START'] = strtotime(setDate2DB($params["QUARTER2_START"]));
                if (isset($params["QUARTER2_END"]))
                    $SAVEDATA['QUARTER2_END'] = strtotime(setDate2DB($params["QUARTER2_END"]));

                if (isset($params["QUARTER3_START"]))
                    $SAVEDATA['QUARTER3_START'] = strtotime(setDate2DB($params["QUARTER3_START"]));
                if (isset($params["QUARTER3_END"]))
                    $SAVEDATA['QUARTER3_END'] = strtotime(setDate2DB($params["QUARTER3_END"]));

                if (isset($params["QUARTER4_START"]))
                    $SAVEDATA['QUARTER4_START'] = strtotime(setDate2DB($params["QUARTER4_START"]));
                if (isset($params["QUARTER4_END"]))
                    $SAVEDATA['QUARTER4_END'] = strtotime(setDate2DB($params["QUARTER4_END"]));

                break;

            default:
                if (isset($params["SEMESTER1_START"]))
                    $SAVEDATA['SEMESTER1_START'] = strtotime(setDate2DB($params["SEMESTER1_START"]));
                if (isset($params["SEMESTER1_END"]))
                    $SAVEDATA['SEMESTER1_END'] = strtotime(setDate2DB($params["SEMESTER1_END"]));
                if (isset($params["SEMESTER2_START"]))
                    $SAVEDATA['SEMESTER2_START'] = strtotime(setDate2DB($params["SEMESTER2_START"]));
                if (isset($params["SEMESTER2_END"]))
                    $SAVEDATA['SEMESTER2_END'] = strtotime(setDate2DB($params["SEMESTER2_END"]));

                break;
        }

        if ($SAVEDATA) {
            $WHERE[] = "ID = '" . $academicObject->ID . "'";
            self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    public static function actionStaffPermissionScore($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $selecteds = isset($params["selecteds"]) ? addText($params["selecteds"]) : "";
        $facette = self::findGradeFromId($objectId);

        if ($facette) {
            $SAVEDATA['STAFF_SCORE_PERMISSION'] = addText($selecteds);
            $WHERE[] = "ID = '" . $facette->ID . "'";
            self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);

            switch ($facette->OBJECT_TYPE) {
                case "SCHOOLYAER":
                    $entries = self::dbAccess()->fetchAll("SELECT * FROM t_grade WHERE PARENT='" . $facette->ID . "' AND OBJECT_TYPE='CLASS'");
                    if ($entries) {
                        foreach ($entries as $value) {
                            self::dbAccess()->query("UPDATE t_grade SET STAFF_SCORE_PERMISSION='" . addText($selecteds) . "' WHERE ID='" . $value->ID . "'");
                        }
                    }
                    break;
            }
        }
        return array(
            "success" => true
        );
    }

    public static function getListStaffsScorePermission($Id) {
        $facette = self::findGradeFromId($Id);
        if ($facette) {
            $data = explode(",", $facette->STAFF_SCORE_PERMISSION);
        }

        return (array) $data;
    }

    ///@veasna
    public static function findCreditGradeSchoolyear($schoolyearId, $compusId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SCHOOLYEAR'");
        $SQL->where("EDUCATION_SYSTEM = 1");
        $SQL->where("SCHOOL_YEAR = ?", $schoolyearId);
        $SQL->where("CAMPUS_ID = ?", $compusId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function addSubClass($params) {

        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $facette = self::findGradeFromId($objectId);

        if ($field) {
            $name = isset($params["newValue"]) ? addText($params["newValue"]) : "";
            $Id = isset($params["id"]) ? addText($params["id"]) : "";
            if ($name && $facette) {
                self::dbAccess()->query("UPDATE t_grade SET NAME='" . addText($name) . "' WHERE ID='" . $Id . "'");
            }
        } else {
            $name = isset($params["name"]) ? addText($params["name"]) : "";
            if ($name && $facette) {
                $SAVEDATA["GUID"] = generateGuid();
                $SAVEDATA["NAME"] = addText($name);
                $SAVEDATA["PARENT"] = $facette->ID;
                $SAVEDATA["OBJECT_TYPE"] = "SUBCLASS";
                $SAVEDATA["CAMPUS_ID"] = $facette->CAMPUS_ID;
                $SAVEDATA["GRADE_ID"] = $facette->GRADE_ID;
                $SAVEDATA["EDUCATION_SYSTEM"] = $facette->EDUCATION_SYSTEM;
                $SAVEDATA["SEMESTER1_START"] = $facette->SEMESTER1_START;
                $SAVEDATA["SEMESTER1_END"] = $facette->SEMESTER1_END;
                $SAVEDATA["SEMESTER2_START"] = $facette->SEMESTER2_START;
                $SAVEDATA["SEMESTER2_END"] = $facette->SEMESTER2_END;
                $SAVEDATA["SCHOOLYEAR_START"] = $facette->SCHOOLYEAR_START;
                $SAVEDATA["SCHOOLYEAR_END"] = $facette->SCHOOLYEAR_END;
                $SAVEDATA["SCHOOL_YEAR"] = $facette->SCHOOL_YEAR;
                $SAVEDATA["DISPLAY_MONTH_RESULT"] = $facette->DISPLAY_MONTH_RESULT;
                $SAVEDATA["DISPLAY_FIRST_RESULT"] = $facette->DISPLAY_FIRST_RESULT;
                $SAVEDATA["DISPLAY_SECOND_RESULT"] = $facette->DISPLAY_SECOND_RESULT;
                $SAVEDATA["DISPLAY_YEAR_RESULT"] = $facette->DISPLAY_YEAR_RESULT;
                self::dbAccess()->insert("t_grade", $SAVEDATA);
            }
        }

        return array(
            "success" => true
        );
    }

    public static function deleteSubClass($Id) {
        self::dbAccess()->delete('t_grade', array("ID='" . $Id . "'"));
        return array(
            "success" => true
        );
    }

    public static function jsonListSubClass($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = self::findGradeFromId($objectId);
        $parentId = $facette ? $facette->ID : 0;

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("PARENT = ?", $parentId);
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);
        $data = array();

        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["NAME"] = $value->NAME;
                $i++;
            }
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

    public static function getChildGradeSchoolyear($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array('*'));
        $SQL->where("PARENT = ?", $Id);
        $SQL->where("OBJECT_TYPE='CLASS'");
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getSubClasses($parentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SUBCLASS'");
        $SQL->where("PARENT = ?", $parentId);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getTermByDateAcademic($date, $academicId, $type) {

        $output = 0;

        $academicObject = self::findGradeFromId($academicId);

        if ($academicObject) {
            $SQL = self::dbAccess()->select();
            $SQL->from('t_grade', 'COUNT(*) AS C');

            $academicObject = self::findGradeFromId($academicId);
            $termNumber = self::findAcademicTerm($academicObject->SCHOOL_YEAR);

            switch ($termNumber) {
                case 1:
                    switch ($type) {
                        case "FIRST_TERM":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN TERM1_START AND TERM1_END");
                            break;
                        case "SECOND_TERM":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN TERM2_START AND TERM2_END");
                            break;
                        case "THIRD_TERM":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN TERM3_START AND TERM3_END");
                            break;
                    }
                    break;
                case 2:
                    switch ($type) {
                        case "FIRST_QUARTER":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN QUARTER1_START AND QUARTER1_END");
                            break;
                        case "SECOND_QUARTER":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN QUARTER2_START AND QUARTER2_END");
                            break;
                        case "THIRD_QUARTER":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN QUARTER3_START AND QUARTER3_END");
                            break;
                        case "FOURTH_QUARTER":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN QUARTER4_START AND QUARTER4_END");
                            break;
                    }
                    break;
                default:
                    switch ($type) {
                        case "FIRST_SEMESTER":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN SEMESTER1_START AND SEMESTER1_END");
                            break;
                        case "SECOND_SEMESTER":
                            $SQL->where("'" . setDateToSecond($date) . "' BETWEEN SEMESTER2_START AND SEMESTER2_END");
                            break;
                    }
                    break;
            }

            $SQL->where("ID = ?", $academicId);
            $SQL->limit(1);
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            $output = $result ? $result->C : 0;
        }

        return $output;
    }

    public static function getNameOfSchoolTermByDate($date, $academicId, $schoolyearId = false) {

        $termNumber = "";

        $academicObject = self::findGradeFromId($academicId);

        if ($academicObject) {
            $termNumber = self::findAcademicTerm($academicObject->SCHOOL_YEAR);
        }

        if ($schoolyearId) {
            $termNumber = self::findAcademicTerm($schoolyearId);
        }

        $flage = "TERM_ERROR";
        switch ($termNumber) {
            case 1:

                $CHECK_TERM1 = self::getTermByDateAcademic($date, $academicId, "FIRST_TERM");
                $CHECK_TERM2 = self::getTermByDateAcademic($date, $academicId, "SECOND_TERM");
                $CHECK_TERM3 = self::getTermByDateAcademic($date, $academicId, "THIRD_TERM");

                if ($CHECK_TERM1) {
                    $flage = "FIRST_TERM";
                } elseif ($CHECK_TERM2) {
                    $flage = "SECOND_TERM";
                } elseif ($CHECK_TERM3) {
                    $flage = "THIRD_TERM";
                }
                break;
            case 2:

                $CHECK_QUARTER1 = self::getTermByDateAcademic($date, $academicId, "FIRST_QUARTER");
                $CHECK_QUARTER2 = self::getTermByDateAcademic($date, $academicId, "SECOND_QUARTER");
                $CHECK_QUARTER3 = self::getTermByDateAcademic($date, $academicId, "THIRD_QUARTER");
                $CHECK_QUARTER4 = self::getTermByDateAcademic($date, $academicId, "FOURTH_QUARTER");

                if ($CHECK_QUARTER1) {
                    $flage = "FIRST_QUARTER";
                } elseif ($CHECK_QUARTER2) {
                    $flage = "SECOND_QUARTER";
                } elseif ($CHECK_QUARTER3) {
                    $flage = "THIRD_QUARTER";
                } elseif ($CHECK_QUARTER4) {
                    $flage = "FOURTH_QUARTER";
                }

                break;
            default:

                $CHECK_FIRST_SEMESTER = self::getTermByDateAcademic($date, $academicId, "FIRST_SEMESTER");
                $CHECK_SECOND_SEMESTER = self::getTermByDateAcademic($date, $academicId, "SECOND_SEMESTER");

                if ($CHECK_FIRST_SEMESTER) {
                    $flage = "FIRST_SEMESTER";
                } elseif ($CHECK_SECOND_SEMESTER) {
                    $flage = "SECOND_SEMESTER";
                }

                break;
        }

        return $flage;
    }

    public static function getTermByMonthYear($academicId, $monthyear) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', '*');
        $SQL->where("ID = ?", $academicId);
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $facette = self::dbAccess()->fetchRow($SQL);

        $result = "";

        if ($facette) {
            $termNumber = self::findAcademicTerm($facette->SCHOOL_YEAR);
            switch ($termNumber) {
                case 1:
                    $FIRST_MONTHS = unserialize($facette->FIRST_MONTHS);
                    $FIRST_TERM = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($FIRST_MONTHS, $monthyear) : "";
                    $SECOND_MONTHS = unserialize($facette->SECOND_MONTHS);
                    $SECOND_TERM = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($SECOND_MONTHS, $monthyear) : "";
                    $THIRD_MONTHS = unserialize($facette->THIRD_MONTHS);
                    $THIRD_TERM = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($THIRD_MONTHS, $monthyear) : "";

                    if ($FIRST_TERM) {
                        $result = "FIRST_TERM";
                    } elseif ($SECOND_SEMESTER) {
                        $result = "SECOND_TERM";
                    } elseif ($THIRD_TERM) {
                        $result = "THIRD_TERM";
                    }
                    break;
                case 2:
                    $FIRST_MONTHS = unserialize($facette->FIRST_MONTHS);
                    $FIRST_QUARTER = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($FIRST_MONTHS, $monthyear) : "";
                    $SECOND_MONTHS = unserialize($facette->SECOND_MONTHS);
                    $SECOND_QUARTER = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($SECOND_MONTHS, $monthyear) : "";
                    $THIRD_MONTHS = unserialize($facette->THIRD_MONTHS);
                    $THIRD_QUARTER = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($THIRD_MONTHS, $monthyear) : "";
                    $FOURTH_MONTHS = unserialize($facette->FOURTH_MONTHS);
                    $FOURTH_QUARTER = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($FOURTH_MONTHS, $monthyear) : "";

                    if ($FIRST_QUARTER) {
                        $result = "FIRST_QUARTER";
                    } elseif ($SECOND_QUARTER) {
                        $result = "SECOND_QUARTER";
                    } elseif ($THIRD_QUARTER) {
                        $result = "THIRD_QUARTER";
                    } elseif ($FOURTH_QUARTER) {
                        $result = "FOURTH_QUARTER";
                    }
                    break;
                default:
                    $FIRST_MONTHS = unserialize($facette->FIRST_MONTHS);
                    $FIRST_SEMESTER = findTermByMonthYear($FIRST_MONTHS, $monthyear) ? findTermByMonthYear($FIRST_MONTHS, $monthyear) : "";
                    $SECOND_MONTHS = unserialize($facette->SECOND_MONTHS);
                    $SECOND_SEMESTER = findTermByMonthYear($SECOND_MONTHS, $monthyear) ? findTermByMonthYear($SECOND_MONTHS, $monthyear) : "";

                    if ($FIRST_SEMESTER) {
                        $result = "FIRST_SEMESTER";
                    } elseif ($SECOND_SEMESTER) {
                        $result = "SECOND_SEMESTER";
                    }
                    break;
            }
        }

        return $result;
    }

    public static function setAcademicMonthList($academicId) {

        $facette = self::findGradeFromId($academicId);
        $termNumber = self::findAcademicTerm($facette->SCHOOL_YEAR);

        $SAVEDATA['ID'] = $academicId;

        switch ($termNumber) {
            case 1:
                $DATA1 = getMonthsBy2Date(date('Y-m-d', $facette->TERM1_START), date('Y-m-d', $facette->TERM1_END));
                $DATA2 = getMonthsBy2Date(date('Y-m-d', $facette->TERM2_START), date('Y-m-d', $facette->TERM2_END));
                $DATA3 = getMonthsBy2Date(date('Y-m-d', $facette->TERM3_START), date('Y-m-d', $facette->TERM3_END));

                $SAVEDATA['FIRST_MONTHS'] = serialize($DATA1);
                $SAVEDATA['SECOND_MONTHS'] = serialize($DATA2);
                $SAVEDATA['THIRD_MONTHS'] = serialize($DATA3);

                break;
            case 2:
                $DATA1 = getMonthsBy2Date(date('Y-m-d', $facette->QUARTER1_START), date('Y-m-d', $facette->QUARTER1_END));
                $DATA2 = getMonthsBy2Date(date('Y-m-d', $facette->QUARTER2_START), date('Y-m-d', $facette->QUARTER2_END));
                $DATA3 = getMonthsBy2Date(date('Y-m-d', $facette->QUARTER3_START), date('Y-m-d', $facette->QUARTER3_END));
                $DATA4 = getMonthsBy2Date(date('Y-m-d', $facette->QUARTER4_START), date('Y-m-d', $facette->QUARTER4_END));

                $SAVEDATA['FIRST_MONTHS'] = serialize($DATA1);
                $SAVEDATA['SECOND_MONTHS'] = serialize($DATA2);
                $SAVEDATA['THIRD_MONTHS'] = serialize($DATA3);
                $SAVEDATA['FOURTH_MONTHS'] = serialize($DATA4);

                break;
            default:
                $DATA1 = getMonthsBy2Date(date('Y-m-d', $facette->SEMESTER1_START), date('Y-m-d', $facette->SEMESTER1_END));
                $DATA2 = getMonthsBy2Date(date('Y-m-d', $facette->SEMESTER2_START), date('Y-m-d', $facette->SEMESTER2_END));

                $SAVEDATA['FIRST_MONTHS'] = serialize($DATA1);
                $SAVEDATA['SECOND_MONTHS'] = serialize($DATA2);

                break;
        }

        $WHERE[] = "ID = '" . $academicId . "'";
        self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
    }

    public static function getAcademicMonthList($academicId) {

        self::setAcademicMonthList($academicId);
        $facette = self::findGradeFromId($academicId);
        $entries = array();

        $termNumber = self::findAcademicTerm($facette->SCHOOL_YEAR);
        switch ($termNumber) {
            case 1:
                $DATA1 = unserialize($facette->FIRST_MONTHS);
                $DATA2 = unserialize($facette->SECOND_MONTHS);
                $DATA3 = unserialize($facette->THIRD_MONTHS);
                $entries = array_merge($DATA1, $DATA2, $DATA3);
                break;
            case 2:
                $DATA1 = unserialize($facette->FIRST_MONTHS);
                $DATA2 = unserialize($facette->SECOND_MONTHS);
                $DATA3 = unserialize($facette->THIRD_MONTHS);
                $DATA4 = unserialize($facette->FOURTH_MONTHS);
                $entries = array_merge($DATA1, $DATA2, $DATA3, $DATA4);
                break;
            default:
                $DATA1 = unserialize($facette->FIRST_MONTHS);
                $DATA2 = unserialize($facette->SECOND_MONTHS);
                $entries = array_merge($DATA1, $DATA2);
                break;
        }

        $CHECK_DATA = array();
        foreach ($entries as $value) {
            if (isset($value["month"]) && isset($value["year"])) {
                if ($value["year"] != 1970) {
                    $CHECK_DATA[] = array("MONTH" => getMonthNrByName(strtoupper($value["month"])), "YEAR" => $value["year"]);
                }
            }
        }

        sortByOrder($CHECK_DATA, "MONTH");

        $data = array();
        foreach ($CHECK_DATA as $value) {
            if (isset($value["MONTH"]) && isset($value["YEAR"])) {
                $data[getMonthNameByNumber($value["MONTH"])] = $value["YEAR"];
            }
        }

        return $data;
    }

    public static function getDateBySchoolTerm($academicId, $term) {
        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', '*');
        $SQL->where("ID = ?", $academicId);
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $data["START_DATE"] = "";
        $data["END_DATE"] = "";

        if ($result) {
            switch ($term) {
                case "FIRST_SEMESTER":
                    $data["START_DATE"] = date('Y-m-d', $result->SEMESTER1_START);
                    $data["END_DATE"] = date('Y-m-d', $result->SEMESTER1_END);
                    break;
                case "SECOND_SEMESTER":
                    $data["START_DATE"] = date('Y-m-d', $result->SEMESTER2_START);
                    $data["END_DATE"] = date('Y-m-d', $result->SEMESTER2_END);
                    break;
                case "FIRST_TERM":
                    $data["START_DATE"] = date('Y-m-d', $result->TERM1_START);
                    $data["END_DATE"] = date('Y-m-d', $result->TERM1_END);
                    break;
                case "SECOND_TERM":
                    $data["START_DATE"] = date('Y-m-d', $result->TERM2_START);
                    $data["END_DATE"] = date('Y-m-d', $result->TERM2_END);
                    break;
                case "THIRD_TERM":
                    $data["START_DATE"] = date('Y-m-d', $result->TERM3_START);
                    $data["END_DATE"] = date('Y-m-d', $result->TERM3_END);
                    break;
                case "FIRST_QUARTER":
                    $data["START_DATE"] = date('Y-m-d', $result->QUARTER1_START);
                    $data["END_DATE"] = date('Y-m-d', $result->QUARTER1_END);
                    break;
                case "SECOND_QUARTER":
                    $data["START_DATE"] = date('Y-m-d', $result->QUARTER2_START);
                    $data["END_DATE"] = date('Y-m-d', $result->QUARTER2_END);
                    break;
                case "THIRD_QUARTER":
                    $data["START_DATE"] = date('Y-m-d', $result->QUARTER3_START);
                    $data["END_DATE"] = date('Y-m-d', $result->QUARTER3_END);
                    break;
                case "FOURTH_QUARTER":
                    $data["START_DATE"] = date('Y-m-d', $result->QUARTER4_START);
                    $data["END_DATE"] = date('Y-m-d', $result->QUARTER4_END);
                    break;
            }
        }

        return (object) $data;
    }

    public static function mappingDateTerm2Schedule($academicId) {

        $data = array();
        $entries = self::getChildGradeSchoolyear($academicId);
        if ($entries) {
            foreach ($entries as $value) {
                $data[$value->ID] = $value->ID;
            }
        }

        $SQL = self::dbAccess()->select();
        $SQL->from("t_schedule", array("ACADEMIC_ID", "TERM"));
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                if (in_array($value->ACADEMIC_ID, $data)) {
                    $dateObject = self::getDateBySchoolTerm($value->ACADEMIC_ID, $value->TERM);
                    $SQL = "UPDATE t_schedule SET";
                    $SQL .= " START_DATE='" . $dateObject->START_DATE . "', END_DATE='" . $dateObject->END_DATE . "'";
                    $SQL .= " WHERE ACADEMIC_ID='" . $value->ACADEMIC_ID . "'";
                    self::dbAccess()->query($SQL);
                }
            }
        }
    }

    public static function findAcademicTerm($schoolyearId, $academicId = false) {

        $Id = "";
        if ($academicId && !$schoolyearId) {
            $academicObject = self::findGradeFromId($academicId);
            $Id = $academicObject ? $academicObject->SCHOOL_YEAR : "";
        } elseif ($schoolyearId && !$academicId) {
            $Id = $schoolyearId;
        }

        $schoolyearObject = AcademicDateDBAccess::findAcademicDateFromId($Id);
        return $schoolyearObject ? $schoolyearObject->TERM_NUMBER : 0;
    }

}

?>