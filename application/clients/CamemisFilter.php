<?php

///////////////////////////////////////////////////////////
// @Chuy Thong Senior Software Developer
// Date: 06.08.2012
// 79Bz, Phnom Penh, Cambodia
// 
///////////////////////////////////////////////////////////
require_once 'clients/CamemisControl.php';
require_once 'clients/CamemisTree.php';
require_once 'clients/CamemisDynamicCombo.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/BuildData.php";

class CamemisFilter {

    private $LOCATION_TREE = null;
    private $GENERAL_EDU_TREE = null;
    private $TRAINING_TREE = null;
    private $SUBJECT_TREE = null;
    private $STUDENT_DESCRIPTION_TREE = null;
    private $STAFF_DESCRIPTION_TREE = null;
    private $GRADING = null;
    private $SUBJECT = null;
    private $filterGroups = array();
    private $filterFields = array();
    private $filterNames = array();
    private $filters = array();
    private static $instance = null;

    private function __construct() {
        $this->GENERAL_EDU_TREE = new CamemisTree("ACADEMIC", "LIST");
        $this->TRAINING_TREE = new CamemisTree("TRAINING", "LIST");
        $this->SUBJECT_TREE = new CamemisTree("SUBJECT", "LIST");
        $this->LOCATION_TREE = new CamemisTree("DATASET", "LIST");
        $this->STUDENT_DESCRIPTION_TREE = new CamemisTree("DATASET", "LIST_STUDENT_DESC");
        $this->STAFF_DESCRIPTION_TREE = new CamemisTree("DATASET", "LIST_STAFF_DESC");
        $this->GRADING = new CamemisDynCombo("DATASET", "GRADING");
        $this->SUBJECT = new CamemisDynCombo("DATASET", "SUBJECT");
    }

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addFilterColumn($col) {
        $this->filters[] = $col;
    }

    public function addFilterColumns($col) {
        $this->filters = $col;
    }

    public function init() {
        $js = "<script>
                 function getDate(date) {
                    return (date ? date.format('Y-m-j') : '');
                 }
                </script>";
        echo $js;

        $this->filterFields["FIELD_SCHOOL_YEAR"] =
                array("LABEL" => "SCHOOL_YEAR"
                    , "NAME" => "SCHOOL_YEAR_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("SCHOOL_YEAR", "SCHOOL_YEAR_ID", SCHOOL_YEAR, BuildData::comboAcademicData()) . "}"
                    , "PARAM" => "'&schoolyearId=' + form.findField('SCHOOL_YEAR_ID').getValue()");

        $this->filterFields["FIELD_SCHOOL_YEAR2"] =
                array("LABEL" => "ACADEMIC_DATE"
                    , "NAME" => "ACADEMIC_DATE_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("SCHOOLYEAR_START", "SCHOOLYEAR_START", FROM_TEXT . SCHOOL_YEAR, BuildData::comboAcademicData()) . "}
                               ,{" . CamemisControl::getCombo("SCHOOLYEAR_END", "SCHOOLYEAR_END", TO_TEXT . SCHOOL_YEAR, BuildData::comboAcademicData()) . "}"
                    , "PARAM" => "'&schoolYearStart=' + form.findField('SCHOOLYEAR_START').getValue() + '&schoolYearEnd=' + form.findField('SCHOOLYEAR_END').getValue()");

        $this->filterFields["FIELD_ACADEMIC_TYPE"] =
                array("LABEL" => "ACADEMIC_TYPE"
                    , "NAME" => "ACADEMIC_TYPE_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ACADEMICTYPE", "ACADEMICTYPE_ID", ACADEMIC_TYPE) . "}"
                    , "PARAM" => "'&academictype=' + form.findField('ACADEMICTYPE_ID').getValue()");

        $this->filterFields["FIELD_JOB_TYPE_A"] =
                array("LABEL" => "JOB_TYPE"
                    , "NAME" => "JOB_TYPE_A_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("JOB_TYPE", "JOB_TYPE_A_ID", JOB_TYPE) . "}"
                    , "PARAM" => "'&searchJobtype=' + form.findField('JOB_TYPE_A_ID').getValue()");

        $this->filterFields["FIELD_JOB_TYPE_T"] =
                array("LABEL" => "JOB_TYPE"
                    , "NAME" => "JOB_TYPE_T_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("JOB_TYPE", "JOB_TYPE_T_ID", JOB_TYPE) . "}"
                    , "PARAM" => "'&searchJobtype=' + form.findField('JOB_TYPE_T_ID').getValue()");

        $this->filterFields["FIELD_AGE"] =
                array("LABEL" => "AGE"
                    , "NAME" => "AGE_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("AGE_FROM", "AGE_FROM_ID", AGE . FROM_TEXT, CamemisControl::getAgeData()) . "}
                               ,{" . CamemisControl::getCombo("AGE_TO", "AGE_TO_ID", TO_TEXT . AGE, CamemisControl::getAgeData()) . "}"
                    , "PARAM" => "'&agefrom=' + form.findField('AGE_FROM_ID').getValue()
                              + '&ageto=' + form.findField('AGE_TO_ID').getValue()");

        $this->filterFields["FIELD_AMOUNT_OPTION"] =
                array("LABEL" => "AMOUNT_OPTION"
                    , "NAME" => "AMOUNT_OPTION_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("AMOUNTOPTION", "AMOUNTOPTION_ID", AMOUNT_OPTION) . "}"
                    , "PARAM" => "'&amountOption=' + form.findField('AMOUNTOPTION_ID').getValue()");


        $this->filterFields["FIELD_CAMPUS"] =
                array("LABEL" => "CAMPUS"
                    , "NAME" => "CAMPUS_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("CAMPUS", "CAMPUS_ID", CAMPUS, BuildData::comboCampus()) . "}"
                    , "PARAM" => "'&campusId=' + form.findField('CAMPUS_ID').getValue()");

        $this->filterFields["FIELD_DATE_STARTEND_T"] =
                array("LABEL" => "DATE"
                    , "NAME" => "DATE_STARTEND_T_FILTER"
                    , "EXTJS" => "{" . CamemisControl::DateStartfield(FROM_DATE, true) . "}
                               ,{" . CamemisControl::DateEndfield(TO_DATE, true) . "}"
                    , "PARAM" => "'&dateStart=' + getDate(form.findField('END_DATE').getValue())
                              + '&dateEnd=' + getDate(form.findField('START_DATE').getValue())");

        $this->filterFields["FIELD_DATE_STARTEND_A"] =
                array("LABEL" => "DATE"
                    , "NAME" => "DATE_STARTEND_A_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Datefield("DATE_START", "DATE_START_A", FROM_DATE, false, false, true) . "}
                               ,{" . CamemisControl::Datefield("DATE_END", "DATE_END_A", TO_DATE, false, false, true) . "}"
                    , "PARAM" => "'&dateStart=' + getDate(form.findField('DATE_START_A').getValue())
                              + '&dateEnd=' + getDate(form.findField('DATE_END_A').getValue())");


        $this->filterFields["FIELD_ETHNICITY"] =
                array("LABEL" => "ETHNICITY"
                    , "NAME" => "ETHNICITY_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ETHNIC", "ETHNIC_ID", ETHNICITY, BuildData::comboDataAllEthnic()) . "}"
                    , "PARAM" => "'&searchEthnic=' + form.findField('ETHNIC_ID').getValue()");

//        $this->filterFields["FIELD_FEE_CATEGORY"] = 
//             array( "LABEL" => "FEE_CATEGORY"
//                   ,"NAME"  => "FEE_CATEGORY_FILTER"
//                   ,"EXTJS" => "{".CamemisControl::getCombo("FEE_CATEGORY", "FEE_CATEGORY_ID",FEE_CATEGORY,CamemisControl::getFeeCategoryData())."}"
//                   ,"PARAM" => "'&feeCat=' + form.findField('FEE_CATEGORY_ID').getValue()");           

        $this->filterFields["FIELD_FEE_BALANCETYPE"] =
                array("LABEL" => "PAID_STATUS"
                    , "NAME" => "FEE_BALANCETYPE_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("FEEBALANCETYPE", "FEEBALANCETYPE_ID", PAID_STATUS) . "}"
                    , "PARAM" => "'&feeBalance=' + form.findField('FEEBALANCETYPE_ID').getValue()");

        $this->filterFields["FIELD_GENDER"] =
                array("LABEL" => "GENDER"
                    , "NAME" => "GENDER_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("GENDER", "GENDER_ID", GENDER) . "}"
                    , "PARAM" => "'&gender=' + form.findField('GENDER_ID').getValue()");

        $this->filterFields["FIELD_GRADING_A"] =
                array("LABEL" => "GRADING"
                    , "NAME" => "GRADING_A_FILTER"
                    , "EXTJS" => "{xtype: '" . $this->GRADING->getObjectXType() . "'}"
                    , "PARAM" => "'&grading=' + form.findField('GRADING_ID').getValue()");

        $this->filterFields["FIELD_GRADING_T"] =
                array("LABEL" => "GRADING"
                    , "NAME" => "GRADING_T_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("GRADING", "GRADING_T_ID", GRADING, CamemisControl::getGradingSystemData(4)) . "}"
                    , "PARAM" => "'&grading=' + form.findField('GRADING_T_ID').getValue()");

        /*
          $this->filterFields["FIELD_GRADING_PRIMARY"] =
          array( "LABEL" => "GRADING"
          ,"NAME"  => "GRADING_FILTER"
          ,"EXTJS" => "{".CamemisControl::getCombo("GRADING", "GRADING_ID",GRADING, CamemisControl::getGradingSystemData(1))."}"
          ,"PARAM" => "'&grading=' + form.findField('GRADING_ID').getValue()");

          $this->filterFields["FIELD_GRADING_SECONDARY"] =
          array( "LABEL" => "GRADING"
          ,"NAME"  => "GRADING_FILTER"
          ,"EXTJS" => "{".CamemisControl::getCombo("GRADING", "GRADING_ID",GRADING, CamemisControl::getGradingSystemData(2))."}"
          ,"PARAM" => "'&grading=' + form.findField('GRADING_ID').getValue()");

          $this->filterFields["FIELD_GRADING_HIGH"] =
          array( "LABEL" => "GRADING"
          ,"NAME"  => "GRADING_FILTER"
          ,"EXTJS" => "{".CamemisControl::getCombo("GRADING", "GRADING_ID",GRADING, CamemisControl::getGradingSystemData(3))."}"
          ,"PARAM" => "'&grading=' + form.findField('GRADING_ID').getValue()");

         */
        $this->filterFields["FIELD_LOCATION"] =
                array("LABEL" => "CITY_PROVINCE"
                    , "NAME" => "LOCATION_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Trigger2("CHOOSE_CITY_PROVINCE_NAME", CITY_PROVINCE, $this->getTriggerLocation(), false, 150) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_CITY_PROVINCE", false) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_DISTRICT", false) . "}"
                    , "PARAM" => "'&country_province=' + form.findField('CHOOSE_CITY_PROVINCE').getValue()
                              + '&town_city=' + form.findField('CHOOSE_DISTRICT').getValue()");

        $this->filterFields["FIELD_MARITAL_STATUS"] =
                array("LABEL" => "MARITIAL_STATUS"
                    , "NAME" => "MARITIAL_STATUS_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("MARRIED", "MARRIED_ID", MARITIAL_STATUS) . "}"
                    , "PARAM" => "'&mstatus=' + form.findField('MARRIED_ID').getValue()");

        $this->filterFields["FIELD_MINORITY"] =
                array("LABEL" => "MINORITY"
                    , "NAME" => "MINORITY_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("MINORITY", "MINORITY_ID", MINORITY, CamemisControl::getYesNoData()) . "}"
                    , "PARAM" => "'&minority=' + form.findField('MINORITY_ID').getValue()");

        $this->filterFields["FIELD_NATIONALITY"] =
                array("LABEL" => "NATIONALITY"
                    , "NAME" => "NATIONALITY_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("NATIONALITY", "NATIONALITY_ID", NATIONALITY, BuildData::comboDataAllNationality()) . "}"
                    , "PARAM" => "'&nationality=' + form.findField('NATIONALITY_ID').getValue()");

        $this->filterFields["FIELD_ORG_CHART"] =
                array("LABEL" => "ORGANIZATION_CHART"
                    , "NAME" => "ORG_CHART_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ORG_CHART", "ORG_CHART_ID", ORGANIZATION_CHART, CamemisControl::getOrganizationChartData()) . "}"
                    , "PARAM" => "'&searchOrganizationChart=' + form.findField('ORG_CHART_ID').getValue()");


        $this->filterFields["FIELD_PAYMENT_METHOD"] =
                array("LABEL" => "PAYMENT_METHOD"
                    , "NAME" => "PAYMENT_METHOD_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("PAYMENTMETHOD", "PAYMENTMETHOD_ID", PAYMENT_METHOD) . "}"
                    , "PARAM" => "'&paymentMethod=' + form.findField('PAYMENTMETHOD_ID').getValue()");

        $this->filterFields["FIELD_SEMESTER"] =
                array("LABEL" => "SEMESTER"
                    , "NAME" => "SEMESTER_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("SEMESTER", "SEMESTER_ID", SEMESTER) . "}"
                    , "PARAM" => "'&semester=' + form.findField('SEMESTER_ID').getValue()");

        $this->filterFields["FIELD_STAFF_DESCRIPTION"] =
                array("LABEL" => "PERSONAL_DESCRIPTION"
                    , "NAME" => "STAFF_DESCRIPTION_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Trigger2("CHOOSE_STAFF_DESCRIPTION_NAME", ADDITIONAL_INFORMATION, $this->getTriggerStaffDescription(), false, 150) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_STAFF_DESCRIPTION", false) . "}"
                    , "PARAM" => "'&personal_description=' + form.findField('CHOOSE_STAFF_DESCRIPTION').getValue()");

        $this->filterFields["FIELD_STAFF_QUALIFICATION"] =
                array("LABEL" => "QUALIFICATION"
                    , "NAME" => "STAFF_QUALIFICATION_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Textfield("STAFF_QUALIFICATION", QUALIFICATION, false, false) . "}"
                    , "PARAM" => "'&qualification_degree=' + form.findField('STAFF_QUALIFICATION_ID').getValue()");

        $this->filterFields["FIELD_STAFF_SKILL"] =
                array("LABEL" => "SKILL"
                    , "NAME" => "STAFF_SKILL_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Textfield("STAFF_SKILL", SKILL, false, false) . "}"
                    , "PARAM" => "'&skill=' + form.findField('STAFF_SKILL_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_DESCRIPTION"] =
                array("LABEL" => "PERSONAL_DESCRIPTION"
                    , "NAME" => "PERSONAL_DESCRIPTION_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Trigger2("CHOOSE_STUDENT_DESCRIPTION_NAME", PERSONAL_DESCRIPTION, $this->getTriggerStudentDescription(), false, 150) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_STUDENT_DESCRIPTION", false) . "}"
                    , "PARAM" => "'&personal_description=' + form.findField('CHOOSE_STUDENT_DESCRIPTION').getValue()");

        $this->filterFields["FIELD_RELIGION"] =
                array("LABEL" => "RELIGION"
                    , "NAME" => "RELIGION_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("RELIGION", "RELIGION_ID", RELIGION, BuildData::comboDataAllReligion()) . "}"
                    , "PARAM" => "'&searchReligion=' + form.findField('RELIGION_ID').getValue()");

        $this->filterFields["FIELD_SCHOOL_YEAR_OR_CLASS"] =
                array("LABEL" => "SCHOOL_YEAR_OR_CLASS"
                    , "NAME" => "SCHOOL_YEAR_OR_CLASS_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Trigger2("CHOOSE_GRADE_NAME", GRADE_CLASS, $this->getTriggerGeneralEducation(), false, 150) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_CAMPUS", false) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_GRADE", false) . "}"
                    , "PARAM" => "'&isTraining=0&campusId=' + form.findField('CHOOSE_CAMPUS').getValue()
                              + '&choosegrade=' + form.findField('CHOOSE_GRADE').getValue()");

        $this->filterFields["FIELD_SMS_SERVICES_A"] =
                array("LABEL" => "SMS_SERVICES"
                    , "NAME" => "SMS_SERVICES_A_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("SMS_SERVICES", "SMS_SERVICES_A_ID", SMS_SERVICES, CamemisControl::getYesNoData()) . "}"
                    , "PARAM" => "'&searchSMSServices=' + form.findField('SMS_SERVICES_A_ID').getValue()");

        $this->filterFields["FIELD_SMS_SERVICES_T"] =
                array("LABEL" => "SMS_SERVICES"
                    , "NAME" => "SMS_SERVICES_T_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("SMS_SERVICES", "SMS_SERVICES_T_ID", SMS_SERVICES, CamemisControl::getYesNoData()) . "}"
                    , "PARAM" => "'&searchSMSServices=' + form.findField('SMS_SERVICES_T_ID').getValue()");


        $this->filterFields["FIELD_STAFF_ATTENDANCE_A"] =
                array("LABEL" => "STAFF_ATTENDANCE"
                    , "NAME" => "STAFF_ATTENDANCE_A"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ATTENDANCETYPE", "ATTENDANCETYPE_A_ID", STAFF_ATTENDANCE) . "}"
                    , "PARAM" => "'&attendance_type=' + form.findField('ATTENDANCETYPE_A_ID').getValue()");

        $this->filterFields["FIELD_STAFF_ATTENDANCE_T"] =
                array("LABEL" => "STAFF_ATTENDANCE"
                    , "NAME" => "STAFF_ATTENDANCE_T"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ATTENDANCETYPE", "ATTENDANCETYPE_T_ID", STAFF_ATTENDANCE) . "}"
                    , "PARAM" => "'&attendance_type=' + form.findField('ATTENDANCETYPE_T_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_ATTENDANCE_A"] =
                array("LABEL" => "STUDENT_ATTENDANCE"
                    , "NAME" => "STUDENT_ATTENDANCE_A"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ATTENDANCETYPE", "ATTENDANCETYPE_A_ID", STUDENT_ATTENDANCE) . "}"
                    , "PARAM" => "'&attendance_type=' + form.findField('ATTENDANCETYPE_A_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_ATTENDANCE_T"] =
                array("LABEL" => "STUDENT_ATTENDANCE"
                    , "NAME" => "STUDENT_ATTENDANCE_T"
                    , "EXTJS" => "{" . CamemisControl::getCombo("ATTENDANCETYPE", "ATTENDANCETYPE_T_ID", STUDENT_ATTENDANCE) . "}"
                    , "PARAM" => "'&attendance_type=' + form.findField('ATTENDANCETYPE_T_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_DISCIPLINE_A"] =
                array("LABEL" => "STUDENT_DISCIPLINE"
                    , "NAME" => "STUDENT_DISCIPLINE_A"
                    , "EXTJS" => "{" . CamemisControl::getCombo("INFRACTION_TYPE", "INFRACTION_TYPE_A_ID", STUDENT_DISCIPLINE, BuildData::comboDataInfractionType()) . "}"
                    , "PARAM" => "'&infractionType=' + form.findField('INFRACTION_TYPE_A_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_DISCIPLINE_T"] =
                array("LABEL" => "STUDENT_DISCIPLINE"
                    , "NAME" => "STUDENT_DISCIPLINE_T"
                    , "EXTJS" => "{" . CamemisControl::getCombo("INFRACTION_TYPE", "INFRACTION_TYPE_T_ID", STUDENT_DISCIPLINE, BuildData::comboDataInfractionType()) . "}"
                    , "PARAM" => "'&infractionType=' + form.findField('INFRACTION_TYPE_T_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_STATUS_A"] =
                array("LABEL" => "STUDENT_STATUS"
                    , "NAME" => "STUDENT_STATUS_A_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("STUDENTSTATUS", "STUDENTSTATUS_A_ID", STATUS) . "}"
                    , "PARAM" => "'&studentstatusType=' + form.findField('STUDENTSTATUS_A_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_STATUS_T"] =
                array("LABEL" => "STUDENT_STATUS"
                    , "NAME" => "STUDENT_STATUS_T_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("STUDENTSTATUS", "STUDENTSTATUS_T_ID", STATUS) . "}"
                    , "PARAM" => "'&studentstatusType=' + form.findField('STUDENTSTATUS_T_ID').getValue()");

        $this->filterFields["FIELD_STUDENT_TRANSFER"] =
                array("LABEL" => "STUDENT_TRANSFER"
                    , "NAME" => "STUDENT_TRANSFER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("STUDENT_TRANSFER", "STUDENT_TRANSFER_ID", STUDENT_TRANSFER, CamemisControl::getYesNoData()) . "}"
                    , "PARAM" => "'&studentTransfer=' + form.findField('STUDENT_TRANSFER_ID').getValue()");

        $this->filterFields["FIELD_SUBJECT"] =
                array("LABEL" => "SUBJECT"
                    , "NAME" => "SUBJECT_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Trigger2("CHOOSE_SUBJECT_NAME", SUBJECT, $this->getTriggerSubject(), false, 150) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_SUBJECT", false) . "}"
                    , "PARAM" => "'&subjectId=' + form.findField('CHOOSE_SUBJECT').getValue()");

        $this->filterFields["FIELD_SUBJECT_A"] =
                array("LABEL" => "SUBJECT"
                    , "NAME" => "SUBJECT_A_FILTER"
                    , "EXTJS" => "{xtype: '" . $this->SUBJECT->getObjectXType() . "'}"
                    , "PARAM" => "'&subjectId=' + form.findField('SUBJECT_ID').getValue()");

        $this->filterFields["FIELD_SUBJECT_T"] =
                array("LABEL" => "SUBJECT"
                    , "NAME" => "SUBJECT_T_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("SUBJECT_T", "SUBJECT_T_ID", SUBJECT, CamemisControl::getSubjectTraining()) . "}"
                    , "PARAM" => "'&subjectTraining=' + form.findField('SUBJECT_T_ID').getValue()");

        $this->filterFields["FIELD_TRAINING_PROGRAMS"] =
                array("LABEL" => "TRAINING_PROGRAMS"
                    , "NAME" => "TRAINING_PROGRAMS_FILTER"
                    , "EXTJS" => "{" . CamemisControl::Trigger2("CHOOSE_TRAINING_NAME", TRAINING_PROGRAMS, $this->getTriggerTraining(), false, 150) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_TRAINING", false) . "}
                               ,{" . CamemisControl::Hidden("CHOOSE_TRAINING_TYPE", false) . "}"
                    , "PARAM" => "'&isTraining=1&trainingId=' + form.findField('CHOOSE_TRAINING').getValue()
                              + '&chooseTrainingtype=' + form.findField('CHOOSE_TRAINING_TYPE').getValue()");

        $this->filterFields["FIELD_USER_ROLE"] =
                array("LABEL" => "USER_ROLE"
                    , "NAME" => "USER_ROLE_FILTER"
                    , "EXTJS" => "{" . CamemisControl::getCombo("USER_ROLE", "USER_ROLE_ID", USER_ROLE, BuildData::comboDataUserRole(false, true)) . "}"
                    , "PARAM" => "'&searchUserRole=' + form.findField('USER_ROLE_ID').getValue()");

        foreach ($this->filterFields as $key => $value) {
            $this->filterNames[] = $value["NAME"];
        }

        $this->filterGroups["PERSONAL_STUDENT"] =
                array("LABEL" => "PERSONAL_INFORMATION",
                    "FILTERS" => array($this->filterFields["FIELD_GENDER"]
                        , $this->filterFields["FIELD_AGE"]
                        , $this->filterFields["FIELD_NATIONALITY"]
                        , $this->filterFields["FIELD_LOCATION"]
                        , $this->filterFields["FIELD_RELIGION"]
                        , $this->filterFields["FIELD_ETHNICITY"]
//                                      ,$this->filterFields["FIELD_MINORITY"]
                        , $this->filterFields["FIELD_STUDENT_DESCRIPTION"]
                    )
        );

        $this->filterGroups["SCHOOL_GENERAL"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_SCHOOL_YEAR2"]
                , $this->filterFields["FIELD_DATE_STARTEND_A"]
                , $this->filterFields["FIELD_ACADEMIC_TYPE"]
                , $this->filterFields["FIELD_SMS_SERVICES_A"]
                , $this->filterFields["FIELD_STUDENT_STATUS_A"]
                , $this->filterFields["FIELD_STUDENT_ATTENDANCE_A"]
                , $this->filterFields["FIELD_STUDENT_DISCIPLINE_A"]
                , $this->filterFields["FIELD_STUDENT_TRANSFER"]
            )
        );

        $this->filterGroups["SCHOOL_TRAIN"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_DATE_STARTEND_T"]
                , $this->filterFields["FIELD_SMS_SERVICES_T"]
                , $this->filterFields["FIELD_STUDENT_STATUS_T"]
                , $this->filterFields["FIELD_STUDENT_ATTENDANCE_T"]
                , $this->filterFields["FIELD_STUDENT_DISCIPLINE_T"]
            )
        );


        $this->filterGroups["PERSONAL_STAFF"] = array("LABEL" => "PERSONAL_INFORMATION",
            "FILTERS" => array($this->filterFields["FIELD_GENDER"]
//                                  ,$this->filterFields["FIELD_MARITAL_STATUS"]
                , $this->filterFields["FIELD_LOCATION"]
                , $this->filterFields["FIELD_STAFF_QUALIFICATION"]
                , $this->filterFields["FIELD_STAFF_SKILL"]
                , $this->filterFields["FIELD_STAFF_DESCRIPTION"]
        ));

        $this->filterGroups["SCHOOL_FEE"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(//$this->filterFields["FIELD_FEE_CATEGORY"]
                $this->filterFields["FIELD_DATE_STARTEND_A"]
                , $this->filterFields["FIELD_PAYMENT_METHOD"]
                , $this->filterFields["FIELD_GENDER"]
                , $this->filterFields["FIELD_ACADEMIC_TYPE"]
                , $this->filterFields["FIELD_AMOUNT_OPTION"]
                , $this->filterFields["FIELD_FEE_BALANCETYPE"]
        ));

        //$this->filterGroups["SCHOOL_ACADEMIC"] = array("LABEL" => "SCHOOL_INFORMATION",
//                "FILTERS" => array($this->filterFields["FIELD_GENDER"]
//                                  ,$this->filterFields["FIELD_ACADEMIC_TYPE"]
//                                  ,$this->filterFields["FIELD_GRADING"]                                  
//                ));

        $this->filterGroups["SCHOOL_STAFF_ACADEMIC"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_JOB_TYPE_A"]
                , $this->filterFields["FIELD_STAFF_ATTENDANCE_A"]
                , $this->filterFields["FIELD_SUBJECT"]
                , $this->filterFields["FIELD_ORG_CHART"]
                , $this->filterFields["FIELD_DATE_STARTEND_A"]
        ));

        $this->filterGroups["SCHOOL_STAFF_TRAINING"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_STAFF_ATTENDANCE_T"]
                , $this->filterFields["FIELD_JOB_TYPE_T"]
                , $this->filterFields["FIELD_DATE_STARTEND_T"]
                , $this->filterFields["FIELD_SUBJECT_T"]
        ));

        $this->filterGroups["SCHOOL_ACAD_ACADEMIC"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_ACADEMIC_TYPE"]
                , $this->filterFields["FIELD_SUBJECT_A"]
                , $this->filterFields["FIELD_GRADING_A"]
        ));

        $this->filterGroups["SCHOOL_ACAD_TRAINING"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_STUDENT_ATTENDANCE_T"]
                , $this->filterFields["FIELD_GENDER"]
                , $this->filterFields["FIELD_GRADING_T"]
        ));

        $this->filterGroups["SCHOOL_FEE_ACADEMIC"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_ACADEMIC_TYPE"]
                , $this->filterFields["FIELD_FEE_BALANCETYPE"]
                , $this->filterFields["FIELD_GENDER"]
                , $this->filterFields["FIELD_GRADING_A"]
        ));

        $this->filterGroups["SCHOOL_FEE_TRAINING"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_STUDENT_ATTENDANCE_T"]
                , $this->filterFields["FIELD_ACADEMIC_TYPE"]
        ));

        $this->filterGroups["SCHOOL_BALANCE_ACADEMIC"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_STUDENT_ATTENDANCE_A"]
        ));

        $this->filterGroups["SCHOOL_BALANCE_TRAINING"] = array("LABEL" => "SCHOOL_INFORMATION",
            "FILTERS" => array(
                $this->filterFields["FIELD_STUDENT_ATTENDANCE_T"]
                , $this->filterFields["FIELD_ACADEMIC_TYPE"]
        ));
    }

    public function getFilterNames() {
        return $this->filterNames;
    }

    public function getFiltergroup($group) {
        return $this->filterGroups[$group];
    }

    public function getExtJsFilter() {
        $dropdowns = array();
        foreach ($this->filters as $value) {
            foreach ($this->filterFields as $v) {
                if ($v["NAME"] == $value) {
                    $dropdowns[] = $v["EXTJS"];
                    break;
                }
            }
        }
        return implode(",", $dropdowns);
    }

    public function getParams() {
        $params = array();
        foreach ($this->filterFields as $key => $value) {
            if ((in_array($value["NAME"], $this->filters))) {
                $params[] = $value["PARAM"];
            }
        }
        $params = array_unique($params);
        return implode("+", $params) . ";";
    }

    public function renderJSFields() {
        //--------------------------------
        $this->GENERAL_EDU_TREE->isAsyncTreeNode = false;
        $this->GENERAL_EDU_TREE->addTBarItems(CamemisBar::tbarTreeRefresh());
        $this->GENERAL_EDU_TREE->addTBarItems(CamemisBar::tbarTreeExpand());
        $this->GENERAL_EDU_TREE->addTBarItems(CamemisBar::tbarTreeCollapse());

        $this->GENERAL_EDU_TREE->setBaseParams("
            cmd: 'getAllAcademics',
            objectTypeIn: '(\"CAMPUS\",\"GRADE\")'
        ");
        $this->GENERAL_EDU_TREE->isOnClickContextMenu = false;
        $this->GENERAL_EDU_TREE->isTreeExpand = false;
        $this->GENERAL_EDU_TREE->renderJS();

        //--------------------------------
        $this->TRAINING_TREE->setURL('/training/jsontree/');
        $this->TRAINING_TREE->isAsyncTreeNode = false;
        $this->TRAINING_TREE->addTBarItems(CamemisBar::tbarTreeRefresh());
        $this->TRAINING_TREE->addTBarItems(CamemisBar::tbarTreeExpand());
        $this->TRAINING_TREE->addTBarItems(CamemisBar::tbarTreeCollapse());

        $this->TRAINING_TREE->setBaseParams("
            cmd: 'jsonTreeAllTrainings'
           ,objectTypeLevel: 'LEVEL'
        ");
        $this->TRAINING_TREE->backgroundColor = "#f9f9f9";
        $this->TRAINING_TREE->isTreeExpand = false;
        $this->TRAINING_TREE->renderJS();

        //--------------------------------
        $this->LOCATION_TREE->isAsyncTreeNode = false;
        $this->LOCATION_TREE->addTBarItems(CamemisBar::tbarTreeRefresh());
        $this->LOCATION_TREE->addTBarItems(CamemisBar::tbarTreeExpand());
        $this->LOCATION_TREE->addTBarItems(CamemisBar::tbarTreeCollapse());
        $this->LOCATION_TREE->setBaseParams("
            cmd: 'jsonTreeAllLocation'
        ");
        $this->LOCATION_TREE->isOnClickContextMenu = false;
        $this->LOCATION_TREE->isTreeExpand = false;
        $this->LOCATION_TREE->renderJS();

        //--------------------------------
        $this->SUBJECT_TREE->isAsyncTreeNode = false;
        $this->SUBJECT_TREE->addTBarItems(CamemisBar::tbarTreeRefresh());
        $this->SUBJECT_TREE->addTBarItems(CamemisBar::tbarTreeExpand());
        $this->SUBJECT_TREE->addTBarItems(CamemisBar::tbarTreeCollapse());
        $this->SUBJECT_TREE->setBaseParams("
            cmd: 'treeAllSubjects'
        ");
        $this->SUBJECT_TREE->isOnClickContextMenu = false;
        $this->SUBJECT_TREE->isTreeExpand = false;
        $this->SUBJECT_TREE->renderJS();

        $this->STUDENT_DESCRIPTION_TREE->isAsyncTreeNode = false;
        $this->STUDENT_DESCRIPTION_TREE->setBaseParams("
            cmd: 'jsonTreeAllDescription'
            ,personType:'STUDENT'
        ");

        $this->STUDENT_DESCRIPTION_TREE->addTBarItems(CamemisBar::tbarTreeRefresh());
        $this->STUDENT_DESCRIPTION_TREE->addTBarItems(CamemisBar::tbarTreeExpand());
        $this->STUDENT_DESCRIPTION_TREE->addTBarItems(CamemisBar::tbarTreeCollapse());
        $this->STUDENT_DESCRIPTION_TREE->isTreeExpand = false;
        $this->STUDENT_DESCRIPTION_TREE->renderJS();

        $this->STAFF_DESCRIPTION_TREE->isAsyncTreeNode = false;
        $this->STAFF_DESCRIPTION_TREE->setBaseParams("
            cmd: 'jsonTreeAllDescription'
            ,personType:'STAFF'
        ");

        $this->STAFF_DESCRIPTION_TREE->addTBarItems(CamemisBar::tbarTreeRefresh());
        $this->STAFF_DESCRIPTION_TREE->addTBarItems(CamemisBar::tbarTreeExpand());
        $this->STAFF_DESCRIPTION_TREE->addTBarItems(CamemisBar::tbarTreeCollapse());
        $this->STAFF_DESCRIPTION_TREE->isTreeExpand = false;
        $this->STAFF_DESCRIPTION_TREE->renderJS();

        $this->GRADING->objectTitle = GRADING;
        $this->GRADING->allowBlank = "true";
        $this->GRADING->width = 200;
        $this->GRADING->varName = "TOWN_CITY";
        $this->GRADING->setLoadParams("cmd: 'jsonGrading',edutype:'0'");
        $this->GRADING->renderJS();

        $this->SUBJECT->objectTitle = SUBJECT;
        $this->SUBJECT->allowBlank = "true";
        $this->SUBJECT->width = 200;
        $this->SUBJECT->varName = "SUBJECT";
        $this->SUBJECT->setLoadParams("cmd: 'jsonSubject',edutype:'-1'");
        $this->SUBJECT->renderJS();
    }

    private function getTriggerLocation() {
        $triggerOnClickCity = "
        openWinXType('CITY_PROVINCE','" . CITY_PROVINCE . "', '" . $this->LOCATION_TREE->getObjectXType() . "', 450, 400);
        var myTree = " . $this->LOCATION_TREE->ExtgetCmp() . ";
        myTree.on('click', function(node, e){
            Ext.getCmp('CHOOSE_CITY_PROVINCE_NAME_ID').setValue('');
            Ext.getCmp('CHOOSE_CITY_PROVINCE').setValue('');
            Ext.getCmp('CHOOSE_DISTRICT').setValue('');
            if (node.attributes.objecttype=='FOLDER') {
                Ext.getCmp('CHOOSE_CITY_PROVINCE_NAME_ID').setValue(node.text);
                Ext.getCmp('CHOOSE_CITY_PROVINCE').setValue(node.id);
            } else if (node.attributes.objecttype=='ITEM') {
                Ext.getCmp('CHOOSE_CITY_PROVINCE_NAME_ID').setValue(node.attributes.longtext);
                Ext.getCmp('CHOOSE_CITY_PROVINCE').setValue(node.attributes.parentid);
                Ext.getCmp('CHOOSE_DISTRICT').setValue(node.id);
            } 
            Ext.getCmp('CITY_PROVINCE').close();
            //if (node.isLeaf())   ;
        });
        ";
        return $triggerOnClickCity;
    }

    private function getTriggerGeneralEducation() {
        $triggerOnClick = "
        openWinXType('CLASS','" . GENERAL_EDUCATION . "', '" . $this->GENERAL_EDU_TREE->getObjectXType() . "', 450, 400);
        var academicTree = Ext.getCmp('" . $this->GENERAL_EDU_TREE->getObjectId() . "');
        academicTree.getRootNode().expand(true, false);
        academicTree.on('click', function(node, e){
            
            Ext.getCmp('CHOOSE_CAMPUS').setValue('');
            Ext.getCmp('CHOOSE_GRADE').setValue('');
            Ext.getCmp('CHOOSE_GRADE_NAME_ID').setValue('');

            if(node.attributes.objecttype == 'CAMPUS' || 
               node.attributes.objecttype == 'GRADE' ){
                
                var comboChild = Ext.getCmp('GRADING_ID');
                var comboChildSubject = Ext.getCmp('SUBJECT_ID');
                var eduType = 0;
                //Choose Campus
                if (node.attributes.objecttype == 'CAMPUS') {
                    Ext.getCmp('CHOOSE_CAMPUS').setValue(node.id);
                    Ext.getCmp('CHOOSE_GRADE_NAME_ID').setValue(node.text);
                    eduType = node.id;
                }

                //Chosse Grade
                if (node.attributes.objecttype == 'GRADE') {
                    Ext.getCmp('CHOOSE_CAMPUS').setValue(node.attributes.campusId);
                    Ext.getCmp('CHOOSE_GRADE').setValue(node.id);
                    Ext.getCmp('CHOOSE_GRADE_NAME_ID').setValue(node.attributes.title);
                    eduType = node.attributes.campusId;
                }
                
                if(comboChild) {
                    comboChild.setValue('');
                    comboChild.store.baseParams = {
                        cmd: 'jsonGrading'
                        ,edutype: eduType
                    };
                    comboChild.store.reload();
                }
                //*
                if(comboChildSubject) {
                    comboChildSubject.setValue('');
                    comboChildSubject.store.baseParams = {
                        cmd: 'jsonSubject'
                        ,edutype: eduType
                    };
                    comboChildSubject.store.reload();
                }  //*/
				
                Ext.getCmp('CLASS').close();
            }
        });
    ";
        return $triggerOnClick;
    }

    private function getTriggerTraining() {
        $triggerOnClickTraining = "
            openWinXType('XTYPE_TRAINING','" . TRAINING_PROGRAMS . "', '" . $this->TRAINING_TREE->getObjectXType() . "', 450, 400);
            var trainingTree = Ext.getCmp('" . $this->TRAINING_TREE->getObjectId() . "');
            trainingTree.getRootNode().expand(true, false);    
            trainingTree.on('click', function(node, e){   
                Ext.getCmp('CHOOSE_TRAINING_TYPE').setValue('');
                Ext.getCmp('CHOOSE_TRAINING').setValue('');
                if(node.attributes.objecttype == 'CLASS'
                || node.attributes.objecttype == 'LEVEL'
                || node.attributes.objecttype == 'PROGRAM'){
                    Ext.getCmp('CHOOSE_TRAINING_TYPE').setValue(node.attributes.objecttype);
                    Ext.getCmp('CHOOSE_TRAINING_NAME_ID').setValue(node.text);
                    Ext.getCmp('CHOOSE_TRAINING').setValue(node.id);
                    /*
                    //Choose TrainingId
                    if(node.attributes.objecttype == 'CLASS'){
                        Ext.getCmp('CHOOSE_TRAINING').setValue(node.id);
                    }
                    //Choose Term
                    if(node.attributes.objecttype == 'TERM'){
                        Ext.getCmp('CHOOSE_TRAINING').setValue(node.id);
                    }*/
                    Ext.getCmp('XTYPE_TRAINING').close();                
                }
            });
        ";
        return $triggerOnClickTraining;
    }

    private function getTriggerSubject() {
        $triggerOnClickSubject = "
            openWinXType('SUBJECT','" . SUBJECT . "', '" . $this->SUBJECT_TREE->getObjectXType() . "', 450, 400);
            var myTree = " . $this->SUBJECT_TREE->ExtgetCmp() . ";
            myTree.on('click', function(node, e){
                if(node.isLeaf()){
                    Ext.getCmp('CHOOSE_SUBJECT_NAME_ID').setValue(node.attributes.onlytext);
                    Ext.getCmp('CHOOSE_SUBJECT').setValue(node.id);
                    Ext.getCmp('SUBJECT').close();
                }
            });
        ";
        return $triggerOnClickSubject;
    }

    private function getTriggerStudentDescription() {
        $triggerOnClickDescription = "
        openWinXType('DESCRIPTION','" . ADDITIONAL_INFORMATION . "', '" . $this->STUDENT_DESCRIPTION_TREE->getObjectXType() . "', 450, 400);
        var myTree = Ext.getCmp('" . $this->STUDENT_DESCRIPTION_TREE->getObjectId() . "');
        myTree.on('click', function(node, e){
            
                Ext.getCmp('CHOOSE_STUDENT_DESCRIPTION_NAME_ID').setValue(node.attributes.text);
                Ext.getCmp('CHOOSE_STUDENT_DESCRIPTION').setValue(node.attributes.id);
            
            Ext.getCmp('DESCRIPTION').close();
        });
        ";
        return $triggerOnClickDescription;
    }

    private function getTriggerStaffDescription() {
        $triggerOnClickDescription = "
        openWinXType('DESCRIPTION','" . ADDITIONAL_INFORMATION . "', '" . $this->STAFF_DESCRIPTION_TREE->getObjectXType() . "', 450, 400);
        var myTree = Ext.getCmp('" . $this->STAFF_DESCRIPTION_TREE->getObjectId() . "');
        myTree.on('click', function(node, e){
            
                Ext.getCmp('CHOOSE_STAFF_DESCRIPTION_NAME_ID').setValue(node.attributes.text);
                Ext.getCmp('CHOOSE_STAFF_DESCRIPTION').setValue(node.attributes.id);
            
            Ext.getCmp('DESCRIPTION').close();
        });
        ";
        return $triggerOnClickDescription;
    }

}
