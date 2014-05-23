<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/CommunicationDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/SpecialDBAccess.php';
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

        $academicObject = self::findGradeFromId($Id);

        if ($academicObject) {

            $parent = self::findGradeFromId($academicObject->PARENT);

            if (isset($parent)) {
                $parentName = isset($parent->NAME) ? $parent->NAME : "";
            } else {
                $parentName = "";
            }

            $title = $parentName;

            switch ($academicObject->OBJECT_TYPE) {
                case "SCHOOLYEAR":
                    $DB_SCHOOLYEAR = AcademicDateDBAccess::getInstance();

                    if ($academicObject->SCHOOL_YEAR) {
                        $data["IS_CURRENT_YEAR"] = $DB_SCHOOLYEAR->isCurrentSchoolyear($academicObject->SCHOOL_YEAR);
                    } else {
                        $data["IS_CURRENT_YEAR"] = 0;
                    }

                    if ($academicObject->NAME) {
                        $data["NAME"] = $academicObject->NAME;
                    } else {
                        $data["NAME"] = "---";
                    }

                    self::updateAllSchoolyearChildren($academicObject);

                    break;
                case "SUBCLASS":
                    $subjectObject = SubjectDBAccess::findSubjectFromId($academicObject->SUBJECT_ID);
                    if ($subjectObject) {
                        $data["SHORT_CODE"] = $subjectObject->SHORT;
                        $data["SUBJECT_NAME"] = $subjectObject->NAME;
                        $data["SUBJECT_CREDIT"] = $subjectObject->NUMBER_CREDIT;
                        $data["SUBJECT_ID"] = $subjectObject->ID;
                    }
                    $title = $parent ? $academicObject->NAME . ": " . $academicObject->NAME : $parent->NAME;
                    $data["NAME"] = setShowText($parent->NAME);
                    break;
                case "SUBJECT":
                    if ($academicObject->SUBJECT_ID) {
                        $subjectObject = SubjectDBAccess::findSubjectFromId($academicObject->SUBJECT_ID);
                        if ($subjectObject) {
                            $data["SHORT_CODE"] = $subjectObject->SHORT;
                            $data["SUBJECT_NAME"] = $subjectObject->NAME;
                            $data["SUBJECT_CREDIT"] = $subjectObject->NUMBER_CREDIT;
                            $data["SUBJECT_ID"] = $subjectObject->ID;
                        }
                        $title = $parentName . ": " . $subjectObject->NAME;
                        $data["NAME"] = setShowText($subjectObject->NAME);
                    } else {
                        $title = $parentName;
                        $data["NAME"] = setShowText($parentName);
                    }
                    break;
                default:
                    $data["NAME"] = setShowText($academicObject->NAME);
                    break;
            }

            $data["TITLE"] = $title;

            $data["ID"] = $academicObject->ID;
            $data["SORTKEY"] = $academicObject->SORTKEY;
            $data["COLOR"] = $academicObject->COLOR;
            $data["SHORT"] = $academicObject->SHORT;
            $data["GRADE_ID"] = $academicObject->GRADE_ID;
            $data["CAMPUS_ID"] = $academicObject->CAMPUS_ID;
            $data["CODE"] = $academicObject->CODE;
            $data["STATUS"] = $academicObject->STATUS;
            $data["EVALUATION_OPTION"] = $academicObject->EVALUATION_OPTION;
            $data["EDUCATION_SYSTEM"] = $academicObject->EDUCATION_SYSTEM;

            $data["SCHOOLYEAR_START"] = getShowDate($academicObject->SCHOOLYEAR_START);
            $data["SCHOOLYEAR_END"] = getShowDate($academicObject->SCHOOLYEAR_END);

            $data["SEMESTER1_START"] = showSeconds2Date($academicObject->SEMESTER1_START);
            $data["SEMESTER1_END"] = showSeconds2Date($academicObject->SEMESTER1_END);
            $data["SEMESTER2_START"] = showSeconds2Date($academicObject->SEMESTER2_START);
            $data["SEMESTER2_END"] = showSeconds2Date($academicObject->SEMESTER2_END);

            $data["TERM1_START"] = showSeconds2Date($academicObject->TERM1_START);
            $data["TERM1_END"] = showSeconds2Date($academicObject->TERM1_END);
            $data["TERM2_START"] = showSeconds2Date($academicObject->TERM2_START);
            $data["TERM2_END"] = showSeconds2Date($academicObject->TERM2_END);
            $data["TERM3_START"] = showSeconds2Date($academicObject->TERM3_START);
            $data["TERM3_END"] = showSeconds2Date($academicObject->TERM3_END);

            $data["QUARTER1_START"] = showSeconds2Date($academicObject->QUARTER1_START);
            $data["QUARTER1_END"] = showSeconds2Date($academicObject->QUARTER1_END);
            $data["QUARTER2_START"] = showSeconds2Date($academicObject->QUARTER2_START);
            $data["QUARTER2_END"] = showSeconds2Date($academicObject->QUARTER2_END);
            $data["QUARTER3_START"] = showSeconds2Date($academicObject->QUARTER3_START);
            $data["QUARTER3_END"] = showSeconds2Date($academicObject->QUARTER3_END);
            $data["QUARTER4_START"] = showSeconds2Date($academicObject->QUARTER4_START);
            $data["QUARTER4_END"] = showSeconds2Date($academicObject->QUARTER4_END);

            if ($academicObject->EDUCATION_SYSTEM) {
                $data["EDUCATION_SYSTEM_NAME"] = NUMBER_CREDIT;
            } else {
                $data["EDUCATION_SYSTEM_NAME"] = TRADITIONAL;
            }

            $data["USE_OF_GROUPS"] = $academicObject->USE_OF_GROUPS ? true : false;
            $data["SUBJECT"] = $academicObject->SUBJECT_ID;
            $data["NUMBER_CREDIT"] = $academicObject->NUMBER_CREDIT;

            $deapartmentObject = DepartmentDBAccess::findDepartmentFromId($academicObject->DEPARTMENT);

            if ($deapartmentObject) {
                $data["CHOOSE_DEPARTMENT_NAME"] = $deapartmentObject->NAME;
                $data["CHOOSE_DEPARTMENT"] = $deapartmentObject->ID;
            }

            if ($academicObject->EXTRA_SEMESTER_DATE) {
                $data["DISPLAY_FIRST_SEMESTER"] = getShowDate($academicObject->SEMESTER1_START) . " - " . getShowDate($academicObject->SEMESTER1_END);
                $data["DISPLAY_SECOND_SEMESTER"] = getShowDate($academicObject->SEMESTER2_START) . " - " . getShowDate($academicObject->SEMESTER2_END);
            }

            $data["QUALIFICATION_TYPE"] = $academicObject->QUALIFICATION_TYPE;
            $data["OBJECT_TYPE"] = $academicObject->OBJECT_TYPE;
            $data["SCHOOL_YEAR"] = $academicObject->SCHOOL_YEAR;
            $data["CONTACT_PERSON"] = setShowText($academicObject->CONTACT_PERSON);
            $data["NUMBER_OF_STUDENTS"] = setShowText($academicObject->NUMBER_OF_STUDENTS);
            $data["EMAIL"] = setShowText($academicObject->EMAIL);
            $data["PHONE"] = setShowText($academicObject->PHONE);
            $data["LEVEL"] = setShowText($academicObject->LEVEL);
            $data["PRE_REQUIREMENTS"] = setShowText($academicObject->PRE_REQUIREMENTS);
            $data["SCORE_MODIFICATION"] = displayNumberFormat($academicObject->SCORE_MODIFICATION);

            $data["END_OF_GRADE"] = $academicObject->END_OF_GRADE ? true : false;
            $data["EXTRA_SEMESTER_DATE"] = $academicObject->EXTRA_SEMESTER_DATE ? true : false;

            //@Math Man 17.01.2014
            if ($academicObject->OBJECT_TYPE == "CAMPUS" && UserAuth::getUserType() == "STUDENT") {
                $qualificationObject = self::getQualificationTypeName($academicObject->EDUCATION_TYPE);
                $data["CAMPUS_NAME"] = setShowText($academicObject->NAME);
                $data["CAMPUS_SHORT"] = $academicObject->SHORT;
                $data["CAMPUS_CODE"] = $academicObject->CODE;
                $data["QUALIFICATION_TYPE"] = setShowText($qualificationObject->NAME);
                $data["CAMPUS_CONTACT_PERSON"] = setShowText($academicObject->CONTACT_PERSON);
                $data["CAMPUS_EMAIL"] = setShowText($academicObject->EMAIL);
                $data["CAMPUS_PHONE"] = setShowText($academicObject->PHONE);
            }
            ///////////

            $data["MO"] = $academicObject->MO ? true : false;
            $data["TU"] = $academicObject->TU ? true : false;
            $data["WE"] = $academicObject->WE ? true : false;
            $data["TH"] = $academicObject->TH ? true : false;
            $data["FR"] = $academicObject->FR ? true : false;
            $data["SA"] = $academicObject->SA ? true : false;
            $data["SU"] = $academicObject->SU ? true : false;

            $data["SHOW_MO"] = $academicObject->MO ? YES : NO;
            $data["SHOW_TU"] = $academicObject->TU ? YES : NO;
            $data["SHOW_WE"] = $academicObject->WE ? YES : NO;
            $data["SHOW_TH"] = $academicObject->TH ? YES : NO;
            $data["SHOW_FR"] = $academicObject->FR ? YES : NO;
            $data["SHOW_SA"] = $academicObject->SA ? YES : NO;
            $data["SHOW_SU"] = $academicObject->SU ? YES : NO;

            $data["FIRST_SCORE_START"] = getShowDate($academicObject->FIRST_SCORE_START);
            $data["FIRST_SCORE_END"] = getShowDate($academicObject->FIRST_SCORE_END);
            $data["SECOND_SCORE_START"] = getShowDate($academicObject->SECOND_SCORE_START);
            $data["SECOND_SCORE_END"] = getShowDate($academicObject->SECOND_SCORE_END);
            $data["THIRD_SCORE_START"] = getShowDate($academicObject->THIRD_SCORE_START);
            $data["THIRD_SCORE_END"] = getShowDate($academicObject->THIRD_SCORE_END);
            $data["FOURTH_SCORE_START"] = getShowDate($academicObject->FOURTH_SCORE_START);
            $data["FOURTH_SCORE_END"] = getShowDate($academicObject->FOURTH_SCORE_END);

            $data["YEAR_SCORE_START"] = getShowDate($academicObject->YEAR_SCORE_START);
            $data["YEAR_SCORE_END"] = getShowDate($academicObject->YEAR_SCORE_END);

            $data["END_SCHOOL"] = $academicObject->END_SCHOOL ? true : false;

            $data["DISTRIBUTION_VALUE"] = $academicObject->DISTRIBUTION_VALUE;
            $data["DISPLAY_MONTH_RESULT"] = $academicObject->DISPLAY_MONTH_RESULT ? true : false;
            $data["DISPLAY_FIRST_RESULT"] = $academicObject->DISPLAY_FIRST_RESULT ? true : false;
            $data["DISPLAY_SECOND_RESULT"] = $academicObject->DISPLAY_SECOND_RESULT ? true : false;
            $data["DISPLAY_THIRD_RESULT"] = $academicObject->DISPLAY_THIRD_RESULT ? true : false;
            $data["DISPLAY_FOURTH_RESULT"] = $academicObject->DISPLAY_FOURTH_RESULT ? true : false;
            $data["DISPLAY_YEAR_RESULT"] = $academicObject->DISPLAY_YEAR_RESULT ? true : false;
            $data["EVALUATION_TYPE"] = $academicObject->EVALUATION_TYPE;
            $data["YEAR_RESULT"] = $academicObject->YEAR_RESULT;

            $data["SEMESTER1_WEIGHTING"] = $academicObject->SEMESTER1_WEIGHTING ? $academicObject->SEMESTER1_WEIGHTING : 1;
            $data["SEMESTER2_WEIGHTING"] = $academicObject->SEMESTER2_WEIGHTING ? $academicObject->SEMESTER2_WEIGHTING : 1;
            $data["TERM1_WEIGHTING"] = $academicObject->TERM1_WEIGHTING ? $academicObject->TERM1_WEIGHTING : 1;
            $data["TERM2_WEIGHTING"] = $academicObject->TERM2_WEIGHTING ? $academicObject->TERM2_WEIGHTING : 1;

            $data["CREATED_DATE"] = getShowDateTime($academicObject->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($academicObject->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($academicObject->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($academicObject->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($academicObject->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($academicObject->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($academicObject->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($academicObject->DISABLED_BY);
        }

        return $data;
    }

    /**
     * Select Qualification Type Name for Student Login
     * 
     * @author Math Man 17.01.2014
     * @param mixed $Id
     * @return array dataset
     */
    public static function getQualificationTypeName($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_camemis_type", array("NAME" => "NAME"));
        $SQL->where("ID = ?",$Id);
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findGradeFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', '*');
        if (is_numeric($Id)) {
            $SQL->where("ID = ?",$Id);
        } else {
            $SQL->where("GUID='" . $Id . "'");
        }
        $SQL->limit(1);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
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
                    $SQL->where("CAMPUS_ID = '" . $academicObject->ID . "'");
                    $allRows = self::dbAccess()->fetchAll($SQL);
                    break;
                case "GRADE":
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_grade", array('*'));
                    $SQL->where("GRADE_ID = '" . $academicObject->ID . "'");
                    $allRows = self::dbAccess()->fetchAll($SQL);
                    break;
                case "SCHOOLYEAR":
                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_grade", array('*'));
                    $SQL->where("PARENT = '" . $academicObject->ID . "'");
                    $allRows = self::dbAccess()->fetchAll($SQL);
                    break;
                default:
                    break;
            }
            $params["ObjectType"] = $academicObject->OBJECT_TYPE;
        }

        $this->removeNode($params);

        return array("success" => true);
    }

    public function removeNode($params) {

        $objectId = $params["objectId"];
        $facette = self::findGradeFromId($objectId);

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
                    self::dbAccess()->delete('t_student_attendance', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_subject_assessment', array("CLASS_ID='" . $facette->ID . "'"));
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

                    break;
                case "SUBJECT";

                    self::dbAccess()->delete('t_student_attendance', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_subject_assessment', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_student_assignment', array("CLASS_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_schedule', array("ACADEMIC_ID='" . $facette->ID . "'"));
                    self::dbAccess()->delete('t_subject_teacher_class', array("ACADEMIC='" . $facette->ID . "'"));

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

                    break;
            }

            self::dbAccess()->delete('t_grade', array("ID='" . $facette->ID . "'"));
        }
        return array("success" => true);
    }

    public function updateGrade($params) {

        $name = isset($params["NAME"]) ? addText($params["NAME"]) : "---";

        $objectId = $params["objectId"];
        $academicObject = self::findGradeFromId($objectId);
        $OBJECT_PARENT = self::findGradeFromId($academicObject->PARENT);

        if (isset($params["COLOR"]))
            $SAVEDATA['COLOR'] = addText($params["COLOR"]);

        if (isset($params["NUMBER_OF_STUDENTS"]))
            $SAVEDATA['NUMBER_OF_STUDENTS'] = addText($params["NUMBER_OF_STUDENTS"]);

        if (isset($params["CODE"]))
            $SAVEDATA['CODE'] = addText($params["CODE"]);

        if (isset($params["SUBJECT"]))
            $SAVEDATA['SUBJECT_ID'] = addText($params["SUBJECT"]);

        if (isset($params["EDUCATION_SYSTEM"]))
            $SAVEDATA['EDUCATION_SYSTEM'] = addText($params["EDUCATION_SYSTEM"]);

        if (isset($params["NAME"]))
            $SAVEDATA['NAME'] = addText($params["NAME"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA['SORTKEY'] = addText($params["SORTKEY"]);

        if (isset($params["PRE_REQUIREMENTS"]))
            $SAVEDATA['PRE_REQUIREMENTS'] = addText($params["PRE_REQUIREMENTS"]);

        if (isset($params["CONTACT_PERSON"]))
            $SAVEDATA['CONTACT_PERSON'] = addText($params["CONTACT_PERSON"]);

        if (isset($params["EMAIL"]))
            $SAVEDATA['EMAIL'] = addText($params["EMAIL"]);

        if (isset($params["SHORT"]))
            $SAVEDATA['SHORT'] = addText($params["SHORT"]);

        if (isset($params["QUALIFICATION_TYPE"]))
            $SAVEDATA['QUALIFICATION_TYPE'] = addText($params["QUALIFICATION_TYPE"]);

        if (isset($params["CHOOSE_DEPARTMENT"]))
            $SAVEDATA["DEPARTMENT"] = addText($params["CHOOSE_DEPARTMENT"]);

        if (isset($params["NUMBER_CREDIT"]))
            $SAVEDATA['NUMBER_CREDIT'] = addText($params["NUMBER_CREDIT"]);

        if (isset($params["PHONE"]))
            $SAVEDATA['PHONE'] = addText($params["PHONE"]);

        if (isset($params["LEVEL"]))
            $SAVEDATA['LEVEL'] = addText($params["LEVEL"]);

        if (isset($params["SEMESTER1_WEIGHTING"]))
            $SAVEDATA['SEMESTER1_WEIGHTING'] = addText($params["SEMESTER1_WEIGHTING"]);

        if (isset($params["SEMESTER2_WEIGHTING"]))
            $SAVEDATA['SEMESTER2_WEIGHTING'] = addText($params["SEMESTER2_WEIGHTING"]);

        if (isset($params["TERM1_WEIGHTING"]))
            $SAVEDATA['TERM1_WEIGHTING'] = addText($params["TERM1_WEIGHTING"]);

        if (isset($params["TERM2_WEIGHTING"]))
            $SAVEDATA['TERM2_WEIGHTING'] = addText($params["TERM2_WEIGHTING"]);

        if (isset($params["DISTRIBUTION_VALUE"]))
            $SAVEDATA['DISTRIBUTION_VALUE'] = addText($params["DISTRIBUTION_VALUE"]);

        if (isset($params["EVALUATION_TYPE"]))
            $SAVEDATA['EVALUATION_TYPE'] = $params["EVALUATION_TYPE"];

        if (isset($params["YEAR_RESULT"]))
            $SAVEDATA['YEAR_RESULT'] = $params["YEAR_RESULT"];

        $SAVEDATA['USE_OF_GROUPS'] = isset($params["USE_OF_GROUPS"]) ? 1 : 0;

        $SAVEDATA['DISPLAY_MONTH_RESULT'] = isset($params["DISPLAY_MONTH_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_FIRST_RESULT'] = isset($params["DISPLAY_FIRST_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_SECOND_RESULT'] = isset($params["DISPLAY_SECOND_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_THIRD_RESULT'] = isset($params["DISPLAY_THIRD_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_FOURTH_RESULT'] = isset($params["DISPLAY_FOURTH_RESULT"]) ? 1 : 0;
        $SAVEDATA['DISPLAY_YEAR_RESULT'] = isset($params["DISPLAY_YEAR_RESULT"]) ? 1 : 0;

        if (isset($params["EVALUATION_OPTION"]))
            $SAVEDATA['EVALUATION_OPTION'] = addText($params["EVALUATION_OPTION"]);

        $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        $WHERE = array();
        switch ($academicObject->OBJECT_TYPE) {

            case "CAMPUS":

                $SAVEDATA['TITLE'] = $name;
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);

                $this->updateAllCampusChildren(self::findGradeFromId($academicObject->ID));

                break;

            case "GRADE":

                $SAVEDATA['TITLE'] = $OBJECT_PARENT->TITLE . " &raquo; " . $name;
                $SAVEDATA['END_OF_GRADE'] = isset($params["END_OF_GRADE"]) ? 1 : 0;
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

                $WHERE[] = "ID = '" . $academicObject->ID . "'";
                self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);

                $this->updateAllGradeChildren(self::findGradeFromId($academicObject->ID));

                break;

            case "SCHOOLYEAR":

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

                $facette = self::findGradeFromId($academicObject->ID);
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

                $facette = self::findGradeFromId($academicObject->ID);
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

    protected function sqlEnrolledStudents($schoolyearId, $gradeId, $academicId, $gender) {

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
        if ($academicId)
            $SQL .= " AND B.CLASS='" . $academicId . "'";
        if ($gender)
            $SQL .= " AND A.GENDER='" . $gender . "'";

        $SQL .= " ORDER BY A.LASTNAME";
        //error_log("KAOM VIBOLRITH ".$SQL,0);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function classesByGradeSchoolyear($gradeId, $schoolyearId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("GRADE_ID = ?",$gradeId);
        $SQL->where("OBJECT_TYPE = 'CLASS'");
        $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function childrenByParent($Id, $objectType) {
        $facette = self::findGradeFromId($Id);
        $SQL = "SELECT * FROM  t_grade";

        switch ($objectType) {
            case "CAMPUS":
                $SQL .= " WHERE CAMPUS_ID ='" . $Id . "'";
                break;
            case "GRADE":
                $SQL .= " WHERE GRADE_ID ='" . $Id . "'";
                break;
            case "SCHOOLYEAR":
                $SQL .= " WHERE SCHOOL_YEAR ='" . $facette->SCHOOL_YEAR . "'";
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
            $object = self::findGradeFromId($leftGrade);

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

                $SAVEDATA["QUALIFICATION_TYPE"] = $campusObject->QUALIFICATION_TYPE;
                $SAVEDATA["EDUCATION_SYSTEM"] = $campusObject->EDUCATION_SYSTEM;
                $SAVEDATA["DEPARTMENT"] = $campusObject->DEPARTMENT;
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
                $SAVEDATA["CREDITS"] = $gradeObject->CREDITS;
                $SAVEDATA["LEVEL"] = $gradeObject->LEVEL;
                $SAVEDATA["QUALIFICATION_TYPE"] = $gradeObject->QUALIFICATION_TYPE;
                $SAVEDATA["EDUCATION_SYSTEM"] = $gradeObject->EDUCATION_SYSTEM;
                $SAVEDATA["DEPARTMENT"] = $gradeObject->DEPARTMENT;
                $SAVEDATA["END_SCHOOL"] = $gradeObject->END_SCHOOL;
                $SAVEDATA["SEMESTER1_WEIGHTING"] = $gradeObject->SEMESTER1_WEIGHTING;
                $SAVEDATA["SEMESTER2_WEIGHTING"] = $gradeObject->SEMESTER2_WEIGHTING;
                $SAVEDATA["TERM1_WEIGHTING"] = $gradeObject->TERM1_WEIGHTING;
                $SAVEDATA["TERM2_WEIGHTING"] = $gradeObject->SEMESTER2_WEIGHTING;

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

                    $FIRST_SAVEDATA["FIRST_SCORE_START"] = $schoolyearObject->FIRST_SCORE_START;
                    $FIRST_SAVEDATA["FIRST_SCORE_END"] = $schoolyearObject->FIRST_SCORE_END;
                    $FIRST_SAVEDATA["SECOND_SCORE_START"] = $schoolyearObject->SECOND_SCORE_START;
                    $FIRST_SAVEDATA["SECOND_SCORE_END"] = $schoolyearObject->SECOND_SCORE_END;
                    $FIRST_SAVEDATA["THIRD_SCORE_START"] = $schoolyearObject->THIRD_SCORE_START;
                    $FIRST_SAVEDATA["THIRD_SCORE_END"] = $schoolyearObject->THIRD_SCORE_END;
                    $FIRST_SAVEDATA["FOURTH_SCORE_START"] = $schoolyearObject->FOURTH_SCORE_START;
                    $FIRST_SAVEDATA["FOURTH_SCORE_END"] = $schoolyearObject->FOURTH_SCORE_END;
                    $FIRST_SAVEDATA["YEAR_SCORE_START"] = $schoolyearObject->YEAR_SCORE_START;
                    $FIRST_SAVEDATA["YEAR_SCORE_END"] = $schoolyearObject->YEAR_SCORE_END;
                    $FIRST_SAVEDATA["QUALIFICATION_TYPE"] = $schoolyearObject->QUALIFICATION_TYPE;
                    $FIRST_SAVEDATA["EDUCATION_SYSTEM"] = $schoolyearObject->EDUCATION_SYSTEM;
                    $FIRST_SAVEDATA["DEPARTMENT"] = $schoolyearObject->DEPARTMENT;
                    $FIRST_SAVEDATA["NUMBER_CREDIT"] = $schoolyearObject->NUMBER_CREDIT;

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

                    $FIRST_SAVEDATA["END_OF_GRADE"] = $schoolyearObject->END_OF_GRADE;
                    $FIRST_SAVEDATA["DISTRIBUTION_VALUE"] = $schoolyearObject->DISTRIBUTION_VALUE;
                    $FIRST_SAVEDATA["EVALUATION_OPTION"] = $schoolyearObject->EVALUATION_OPTION;

                    $FIRST_SAVEDATA['DISPLAY_MONTH_RESULT'] = $schoolyearObject->DISPLAY_MONTH_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_FIRST_RESULT'] = $schoolyearObject->DISPLAY_FIRST_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_SECOND_RESULT'] = $schoolyearObject->DISPLAY_SECOND_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_THIRD_RESULT'] = $schoolyearObject->DISPLAY_THIRD_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_FOURTH_RESULT'] = $schoolyearObject->DISPLAY_FOURTH_RESULT;
                    $FIRST_SAVEDATA['DISPLAY_YEAR_RESULT'] = $schoolyearObject->DISPLAY_YEAR_RESULT;

                    $FIRST_SAVEDATA["YEAR_RESULT"] = $schoolyearObject->YEAR_RESULT;
                    $FIRST_SAVEDATA["SEMESTER1_WEIGHTING"] = $schoolyearObject->SEMESTER1_WEIGHTING;
                    $FIRST_SAVEDATA["SEMESTER2_WEIGHTING"] = $schoolyearObject->SEMESTER2_WEIGHTING;
                    $FIRST_SAVEDATA["TERM1_WEIGHTING"] = $schoolyearObject->TERM1_WEIGHTING;
                    $FIRST_SAVEDATA["TERM2_WEIGHTING"] = $schoolyearObject->TERM2_WEIGHTING;
                    $FIRST_SAVEDATA["USE_OF_GROUPS"] = $schoolyearObject->USE_OF_GROUPS;
                    $FIRST_WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                    self::dbAccess()->update('t_grade', $FIRST_SAVEDATA, $FIRST_WHERE);
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
    }

    public static function updateAllSchoolyearSubjectChildren($schoolyearsubjectObject) {

        if (isset($schoolyearsubjectObject->ID)) {
            $SQL = self::dbAccess()->select();
            $SQL->from("t_grade", array("*"));
            $SQL->where("PARENT = '" . $schoolyearsubjectObject->ID . "'");
            $SQL->where("OBJECT_TYPE = 'SUBJECT'");
            //error_log($SQL->__toString());
            $entries = self::dbAccess()->fetchAll($SQL);

            if ($entries) {
                foreach ($entries as $value) {

                    $SAVEDATA["NUMBER_CREDIT"] = $schoolyearsubjectObject->NUMBER_CREDIT;
                    $SAVEDATA["FIRST_SCORE_START"] = $schoolyearsubjectObject->FIRST_SCORE_START;
                    $SAVEDATA["FIRST_SCORE_END"] = $schoolyearsubjectObject->FIRST_SCORE_END;
                    $SAVEDATA["SECOND_SCORE_START"] = $schoolyearsubjectObject->SECOND_SCORE_START;
                    $SAVEDATA["SECOND_SCORE_END"] = $schoolyearsubjectObject->SECOND_SCORE_END;
                    $SAVEDATA["THIRD_SCORE_START"] = $schoolyearsubjectObject->THIRD_SCORE_START;
                    $SAVEDATA["THIRD_SCORE_END"] = $schoolyearsubjectObject->THIRD_SCORE_END;
                    $SAVEDATA["FOURTH_SCORE_START"] = $schoolyearsubjectObject->FOURTH_SCORE_START;
                    $SAVEDATA["FOURTH_SCORE_END"] = $schoolyearsubjectObject->FOURTH_SCORE_END;
                    $SAVEDATA["YEAR_SCORE_START"] = $schoolyearsubjectObject->YEAR_SCORE_START;
                    $SAVEDATA["YEAR_SCORE_END"] = $schoolyearsubjectObject->YEAR_SCORE_END;
                    $SAVEDATA["EDUCATION_TYPE"] = $schoolyearsubjectObject->EDUCATION_TYPE;
                    $SAVEDATA["QUALIFICATION_TYPE"] = $schoolyearsubjectObject->QUALIFICATION_TYPE;
                    $SAVEDATA["SEMESTER1_WEIGHTING"] = $schoolyearsubjectObject->SEMESTER1_WEIGHTING;
                    $SAVEDATA["SEMESTER2_WEIGHTING"] = $schoolyearsubjectObject->SEMESTER2_WEIGHTING;
                    $SAVEDATA["YEAR_RESULT"] = $schoolyearsubjectObject->YEAR_RESULT;
                    $SAVEDATA["EVALUATION_TYPE"] = $schoolyearsubjectObject->EVALUATION_TYPE;
                    $SAVEDATA["DISTRIBUTION_VALUE"] = $schoolyearsubjectObject->DISTRIBUTION_VALUE;

                    $SAVEDATA['DISPLAY_MONTH_RESULT'] = $schoolyearsubjectObject->DISPLAY_MONTH_RESULT;
                    $SAVEDATA['DISPLAY_FIRST_RESULT'] = $schoolyearsubjectObject->DISPLAY_FIRST_RESULT;
                    $SAVEDATA['DISPLAY_SECOND_RESULT'] = $schoolyearsubjectObject->DISPLAY_SECOND_RESULT;
                    $SAVEDATA['DISPLAY_THIRD_RESULT'] = $schoolyearsubjectObject->DISPLAY_THIRD_RESULT;
                    $SAVEDATA['DISPLAY_FOURTH_RESULT'] = $schoolyearsubjectObject->DISPLAY_FOURTH_RESULT;
                    $SAVEDATA['DISPLAY_YEAR_RESULT'] = $schoolyearsubjectObject->DISPLAY_YEAR_RESULT;
                    $SAVEDATA["EVALUATION_OPTION"] = $schoolyearsubjectObject->EVALUATION_OPTION;

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

    public function actionScoreDuration($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : 0;

        $UPDATE_VALUES['MODIFY_DATE'] = getCurrentDBDateTime();
        $UPDATE_VALUES['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

        switch ($actionType) {
            case 1:

                $CHECK_ERROR_START_END_DATE = timeDifference(setDate2DB($params["FIRST_SCORE_START"]), setDate2DB($params["FIRST_SCORE_END"]));

                $UPDATE_VALUES["FIRST_SCORE_START"] = setDate2DB($params["FIRST_SCORE_START"]);
                $UPDATE_VALUES["FIRST_SCORE_END"] = setDate2DB($params["FIRST_SCORE_END"]);

                if ($objectId && !$CHECK_ERROR_START_END_DATE) {
                    $WHERE[] = "ID = '" . $objectId . "'";
                    self::dbAccess()->update('t_grade', $UPDATE_VALUES, $WHERE);
                }

                $text = DURATION_FOR_SCORE_MANAGEMENT . " (" . FIRST_SEMESTER . ")";
                SpecialDBAccess::jsonActionLogAcademic($objectId, $text);

                break;
            case 2:

                $CHECK_ERROR_START_END_DATE = timeDifference(setDate2DB($params["SECOND_SCORE_START"]), setDate2DB($params["SECOND_SCORE_END"]));
                $UPDATE_VALUES["SECOND_SCORE_START"] = setDate2DB($params["SECOND_SCORE_START"]);
                $UPDATE_VALUES["SECOND_SCORE_END"] = setDate2DB($params["SECOND_SCORE_END"]);

                if ($objectId && !$CHECK_ERROR_START_END_DATE) {
                    $WHERE[] = "ID = '" . $objectId . "'";
                    self::dbAccess()->update('t_grade', $UPDATE_VALUES, $WHERE);
                }

                $text = DURATION_FOR_SCORE_MANAGEMENT . " (" . SECOND_SEMESTER . ")";
                SpecialDBAccess::jsonActionLogAcademic($objectId, $text);

                break;
            case 3:

                $CHECK_ERROR_START_END_DATE = timeDifference(setDate2DB($params["YEAR_SCORE_START"]), setDate2DB($params["YEAR_SCORE_END"]));
                $UPDATE_VALUES["YEAR_SCORE_START"] = setDate2DB($params["YEAR_SCORE_START"]);
                $UPDATE_VALUES["YEAR_SCORE_END"] = setDate2DB($params["YEAR_SCORE_END"]);
                if ($objectId && !$CHECK_ERROR_START_END_DATE) {
                    $WHERE[] = "ID = '" . $objectId . "'";
                    self::dbAccess()->update('t_grade', $UPDATE_VALUES, $WHERE);
                }

                $text = DURATION_FOR_SCORE_MANAGEMENT . " (" . YEAR . ")";
                SpecialDBAccess::jsonActionLogAcademic($objectId, $text);

                break;
        }

        switch ($actionType) {
            case 1:
                /*
                  if ($CHECK_ERROR_START_DATE) {
                  $errors["FIRST_SCORE_START"] = CHECK_DATE_PAST;
                  } elseif ($CHECK_ERROR_END_DATE) {
                  $errors["FIRST_SCORE_END"] = CHECK_DATE_PAST;
                  } elseif ($CHECK_ERROR_START_DATE && $CHECK_ERROR_END_DATE) {
                  $errors["FIRST_SCORE_START"] = CHECK_DATE_PAST;
                  $errors["FIRST_SCORE_END"] = CHECK_DATE_PAST;
                  } elseif ($CHECK_ERROR_START_END_DATE) {
                  $errors["FIRST_SCORE_START"] = ERROR;
                  $errors["FIRST_SCORE_END"] = ERROR;
                  } else {
                  $errors = array();
                  }
                 */
                if ($CHECK_ERROR_START_END_DATE) {
                    $errors["FIRST_SCORE_START"] = ERROR;
                    $errors["FIRST_SCORE_END"] = ERROR;
                } else {
                    $errors = array();
                }
                break;
            case 2:
                /*
                  if ($CHECK_ERROR_START_DATE) {
                  $errors["SECOND_SCORE_START"] = CHECK_DATE_PAST;
                  } elseif ($CHECK_ERROR_END_DATE) {
                  $errors["SECOND_SCORE_END"] = CHECK_DATE_PAST;
                  } elseif ($CHECK_ERROR_START_DATE && $CHECK_ERROR_END_DATE) {
                  $errors["SECOND_SCORE_START"] = CHECK_DATE_PAST;
                  $errors["SECOND_SCORE_END"] = CHECK_DATE_PAST;
                  } elseif ($CHECK_ERROR_START_END_DATE) {
                  $errors["SECOND_SCORE_START"] = ERROR;
                  $errors["SECOND_SCORE_END"] = ERROR;
                  } else {
                  $errors = array();
                  }
                 */
                if ($CHECK_ERROR_START_END_DATE) {
                    $errors["SECOND_SCORE_START"] = ERROR;
                    $errors["SECOND_SCORE_END"] = ERROR;
                } else {
                    $errors = array();
                }
                break;
            case 3:

                if ($CHECK_ERROR_START_END_DATE) {
                    $errors["YEAR_SCORE_START"] = ERROR;
                    $errors["YEAR_SCORE_END"] = ERROR;
                } else {
                    $errors = array();
                }

                break;
        }

        ///
        $facette = self::findGradeFromId($objectId);
        self::updateAllSchoolyearChildren($facette);

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array("success" => true, "errors" => $errors);
        }
    }

    public function jsonLoadScoreDeadLine($params) {

        $schoolyearId = isset($params['schoolyearId']) ? addText($params["schoolyearId"]) : "";

        $DB_ACADEMIC = AcademicDateDBAccess::getInstance();
        $DB_STUDENT = StudentDBAccess::getInstance();

        $GRADES = array();
        switch (UserAuth::getUserType()) {
            case "INSTRUCTOR":
            case "TEACHER":
                $evt_params['teacherId'] = Zend_Registry::get('USERID');
                $evt_params['schoolyearId'] = $DB_ACADEMIC->findCurrentSchoolyear(Zend_Registry::get('SCHOOL_ID'));
                $teacherGrades = $this->sqlSubjectGradeByTeacherId($evt_params);
                foreach ($teacherGrades as $key => $value) {
                    if (!in_array($value->GRADE, $GRADES))
                        array_push($GRADES, $value->GRADE);
                }
                break;
            case "STUDENT" :
                $USER = $DB_STUDENT->getStudentDataFromId(Zend_Registry::get('USERID'));
                $current_gradeId = $USER['CURRENT_GRADE_ID'];
                if ($current_gradeId)
                    array_push($GRADES, $current_gradeId);
                break;
            case "SYSTEM":
                $evt_params['schoolyearId'] = $DB_ACADEMIC->findCurrentSchoolyear(Zend_Registry::get('SCHOOL_ID'));
                $teacherGrades = $this->sqlSubjectGradeByTeacherId($evt_params);
                foreach ($teacherGrades as $key => $value) {
                    if (!in_array($value->GRADE, $GRADES))
                        array_push($GRADES, $value->GRADE);
                }
                break;
            default:
                break;
        }
        if (!$schoolyearId)
            $schoolyearId = $DB_ACADEMIC->findCurrentSchoolyear(Zend_Registry::get('SCHOOL_ID'));
        $entries = $this->sqlAllGradesDeadline($schoolyearId);

        $data = array();
        $USED_GRADE = array();
        $i = 0;
        if ($entries) {
            foreach ($entries as $key => $value) {
                if (in_array($value->GRADE_ID, $GRADES) && !in_array($value->GRADE_ID, $USED_GRADE)) {
                    array_push($USED_GRADE, $value->GRADE_ID);
                    //FIRST_SEMESTER
                    if ($value->FIRST_SCORE_START && $value->FIRST_SCORE_END) {
                        $data[$i]["GRADE_NAME"] = $value->GRADE_NAME;
                        $data[$i]["FIRST_SEMESTER_DATE"] = getShowDate($value->FIRST_SCORE_START) . ' - ' . getShowDate($value->FIRST_SCORE_END);
                        $data[$i]["SECOND_SEMESTER_DATE"] = getShowDate($value->SECOND_SCORE_START) . ' - ' . getShowDate($value->SECOND_SCORE_END);
                        $data[$i]["YEAR_DATE"] = getShowDate($value->YEAR_SCORE_START) . ' - ' . getShowDate($value->YEAR_SCORE_END);
                        $i++;
                    }
                }
            }
        }
        if ($data) {
            $data = arraySortByKeyDate($data, "START_DATE");
        }
        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public function sqlAllGradesDeadline($schoolyearId) {

        $SQL = "SELECT DISTINCT
		B.NAME as GRADE_NAME,
		B.ID as GRADE_ID,
		A.FIRST_SCORE_START as FIRST_SCORE_START,
		A.FIRST_SCORE_END as FIRST_SCORE_END,
		A.SECOND_SCORE_START as SECOND_SCORE_START,
		A.SECOND_SCORE_END as SECOND_SCORE_END,
		A.YEAR_SCORE_START as YEAR_SCORE_START,
		A.YEAR_SCORE_END as YEAR_SCORE_END
		";

        $SQL .= " FROM t_grade AS A ";
        $SQL .= " LEFT JOIN t_grade AS B on A.GRADE_ID = B.ID ";

        $SQL .= " WHERE A.SCHOOL_YEAR = '" . $schoolyearId . "'";
        $SQL .= " AND A.OBJECT_TYPE = 'CLASS' ";

        $DB_ACCESS = Zend_Registry::get('DB_ACCESS');
        $stmt = $DB_ACCESS->query($SQL);

        return $stmt->fetchAll();
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

        $SQL = self::dbAccess()->select();
        $SQL->from("t_subject_teacher_class", array("*"));
        $SQL->where("TEACHER = ?",$teacherId);
        $SQL->where("GRADE = ?",$gradeId);
        $SQL->where("GRADINGTERM = '" . $term . "'");
        $SQL->group("SUBJECT");
        //error_log($SQL->__toString());
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

    public static function getChildGradeSchoolyear($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array('*'));
        $SQL->where("PARENT = ?",$Id);
        $SQL->where("OBJECT_TYPE='CLASS'");
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function findClass($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("ID = ?",$Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonActionScoreModification($Id, $type) {

        if ($Id) {
            $SQL = "UPDATE t_grade SET SCORE_MODIFICATION='" . $type . "' WHERE ID='" . $Id . "'";
            self::dbAccess()->query($SQL);

            if (!$type) {
                $text = ENABLE_SCORE_MODIFICATION;
            } else {
                $text = DISABLE_SCORE_MODIFICATION;
            }

            SpecialDBAccess::jsonActionLogAcademic($Id, $text);
        }
        return array(
            "success" => true
        );
    }

    public static function findCampusSchoolyear($campusId, $schoolyearId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SCHOOLYEAR'");
        $SQL->where("CAMPUS_ID = ?",$campusId);
        $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findGradeSchoolyear($gradeId, $schoolyearId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SCHOOLYEAR'");
        $SQL->where("GRADE_ID = ?",$gradeId);
        $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findAcademicFromGuId($GuId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("GUID = ?",$GuId);
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

    public static function jsonActionExtraSemesterDate($params) {

        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        if ($type) {
            switch ($type) {
                case 1:
                    if (isset($params["SEMESTER1_START"]))
                        $SAVEDATA['SEMESTER1_START'] = setDate2DB($params["SEMESTER1_START"]);

                    if (isset($params["SEMESTER1_END"]))
                        $SAVEDATA['SEMESTER1_END'] = setDate2DB($params["SEMESTER1_END"]);
                    break;
                case 2:
                    if (isset($params["SEMESTER2_START"]))
                        $SAVEDATA['SEMESTER2_START'] = setDate2DB($params["SEMESTER2_START"]);

                    if (isset($params["SEMESTER2_END"]))
                        $SAVEDATA['SEMESTER2_END'] = setDate2DB($params["SEMESTER2_END"]);
                    break;
            }

            $WHERE[] = "GUID = '" . $objectId . "'";
            self::dbAccess()->update('t_grade', $SAVEDATA, $WHERE);
        }

        return array(
            "success" => true
        );
    }

    //@Sea Peng
    public static function findAcademicBetweenDate($date) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_academicdate', '*');
        $SQL->where("START <= '" . $date . "' AND END>='" . $date . "'");
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
        $SQL->where("SCHOOL_YEAR = ?",$schoolyearId);
        $SQL->where("CAMPUS_ID = ?",$compusId);
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
        $SQL->where("PARENT = ?",$parentId);
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

    public static function getSubClasses($parentId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_grade", array("*"));
        $SQL->where("OBJECT_TYPE = 'SUBCLASS'");
        $SQL->where("PARENT = ?",$parentId);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function getTermByDateAcademic($date, $academicId, $type) {
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

        $SQL->where("ID = ?",$academicId);
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function getNameOfSchoolTermByDate($date, $academicId, $schoolyearId = false) {

        $academicObject = self::findGradeFromId($academicId);
        $termNumber = self::findAcademicTerm($academicObject->SCHOOL_YEAR);

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

    public static function getAcademicMonthList($academicId) {

        $entries = array();

        $SQL = self::dbAccess()->select();
        $SQL->from('t_grade', '*');
        $SQL->where("ID = ?",$academicId);
        $SQL->limit(1);
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);

        $academicObject = self::findGradeFromId($academicId);
        $termNumber = self::findAcademicTerm($academicObject->SCHOOL_YEAR);

        switch ($termNumber) {
            case 1:
                $TERM1_DATA = getMonthsBy2Date(date('Y-m-d', $result->TERM1_START), date('Y-m-d', $result->TERM1_END));
                $TERM2_DATA = getMonthsBy2Date(date('Y-m-d', $result->TERM2_START), date('Y-m-d', $result->TERM2_END));
                $TERM3_DATA = getMonthsBy2Date(date('Y-m-d', $result->TERM3_START), date('Y-m-d', $result->TERM3_END));
                $entries = array_merge($TERM1_DATA, $TERM2_DATA, $TERM3_DATA);
                break;
            case 2:
                $QUARTER1_DATA = getMonthsBy2Date(date('Y-m-d', $result->QUARTER1_START), date('Y-m-d', $result->QUARTER1_END));
                $QUARTER2_DATA = getMonthsBy2Date(date('Y-m-d', $result->QUARTER2_START), date('Y-m-d', $result->QUARTER2_END));
                $QUARTER3_DATA = getMonthsBy2Date(date('Y-m-d', $result->QUARTER3_START), date('Y-m-d', $result->QUARTER3_END));
                $QUARTER4_DATA = getMonthsBy2Date(date('Y-m-d', $result->QUARTER4_START), date('Y-m-d', $result->QUARTER4_END));
                $entries = array_merge($QUARTER1_DATA, $QUARTER2_DATA, $QUARTER3_DATA, $QUARTER4_DATA);
                break;
            default:
                $SEMESTER1_DATA = getMonthsBy2Date(date('Y-m-d', $result->SEMESTER1_START), date('Y-m-d', $result->SEMESTER1_END));
                $SEMESTER2_DATA = getMonthsBy2Date(date('Y-m-d', $result->SEMESTER2_START), date('Y-m-d', $result->SEMESTER2_END));
                $entries = array_merge($SEMESTER1_DATA, $SEMESTER2_DATA);
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
        $SQL->where("ID = ?",$academicId);
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