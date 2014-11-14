<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.05.2010 14:15
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
class CAMEMISRegistry {

    static public function makeDBTableRegistry() {

        Zend_Registry::set('T_ACADEMICDATE', "t_academicdate");
        Zend_Registry::set('T_ASSIGNMENT', "t_assignment");
        Zend_Registry::set('T_ASSIGNMENT_TEMP', "t_assignment_temp");
        Zend_Registry::set('T_ASSIGNMENTCATEGORY', "t_category");
        Zend_Registry::set('T_TEACHING_PROTOCOL', "t_teaching_protocol");
        Zend_Registry::set('T_COMMUNICATION', "t_communication");
        Zend_Registry::set('T_DISPOSITION_TYPE', "t_disposition_type");
        Zend_Registry::set('T_GRADE', "t_grade");
        Zend_Registry::set('T_INFRACTION_TYPE', "t_infraction_type");
        Zend_Registry::set('T_LOCALIZATION', "t_localization");
        Zend_Registry::set('T_LOGININFO', "t_logininfo");
        Zend_Registry::set('T_MEMBERS', "t_members");
        Zend_Registry::set('T_MEMBERROLE', "t_memberrole");
        Zend_Registry::set('T_PERIOD', "t_period");
        Zend_Registry::set('T_ROOM', "t_room");
        Zend_Registry::set('T_SCALE', "t_scale");
        Zend_Registry::set('T_SCHEDULE', "t_schedule");
        Zend_Registry::set('T_SCHOOL', "t_school");
        Zend_Registry::set('T_SCHOOLEVENT', "t_schoolevent");
        Zend_Registry::set('T_SESSIONS', "t_sessions");
        Zend_Registry::set('T_STAFF', "t_staff");
        Zend_Registry::set('T_STAFF_CAMPUS', "t_staff_campus");
        Zend_Registry::set('T_USER_ORGANIZATION', "t_user_organization");
        Zend_Registry::set('T_STAFF_TEMP', "t_staff_temp");
        Zend_Registry::set('T_TEACHER_SUBJECT', "t_teacher_subject");
        Zend_Registry::set('T_STUDENT', "t_student");
        Zend_Registry::set('T_STUDENT_SUBJECT_AVERAGE', "t_student_subject_average");
        Zend_Registry::set('T_STUDENT_YEAR_SUBJECT_AVERAGE', "t_student_year_subject_average");
        Zend_Registry::set('T_STUDENT_ATTENDANCE', "t_student_attendance");
        Zend_Registry::set('T_STUDENT_CAMPUS', "t_student_campus");
        Zend_Registry::set('T_STUDENT_EXAMINATION', "t_student_examination");
        Zend_Registry::set('T_STUDENT_EXPEL', "t_student_expel");
        Zend_Registry::set('T_STUDENT_SELECTIVE_SUBJECT', "t_student_selective_subject");
        Zend_Registry::set('T_STUDENT_ASSIGNMENT', "t_student_assignment");
        Zend_Registry::set('T_STUDENT_ANUAL_GPA', "t_student_anual_gpa");
        Zend_Registry::set('T_STUDENT_SUBJECT_REPEAT', "t_student_subject_repeat");
        Zend_Registry::set('T_STUDENT_DISCIPLINE', "t_student_discipline");
        Zend_Registry::set('T_STUDENT_SCHOOLYEAR', "t_student_schoolyear");
        Zend_Registry::set('T_STUDENT_REPRESENTATIVE', "t_student_representative");
        Zend_Registry::set('T_STUDENT_TEMP', "t_student_temp");
        Zend_Registry::set('T_RECIPIENT_COMMUNICATION', "t_recipient_communication");
        Zend_Registry::set('T_SUBJECT', "t_subject");
        Zend_Registry::set('T_SUBJECT_GRADE', "t_subject_grade");
        Zend_Registry::set('T_SUBJECT_TEACHER_CLASS', "t_subject_teacher_class");
        Zend_Registry::set('T_SMS', "t_sms");
        Zend_Registry::set('T_LOGSMS', "t_logsms");
        Zend_Registry::set('T_USER_SMS', "t_user_sms");
        Zend_Registry::set('T_STUDENT_SUBJECT_AFTER_REPEAT', "t_student_stubject_after_repeat");
        
        Zend_Registry::set('T_RELIGION', "t_religion");
        Zend_Registry::set('T_ETHNIC', "t_ethnic");
        Zend_Registry::set('T_EXAMINATION', "t_examination");
        Zend_Registry::set('T_TEACHER_EXAMINATION', "t_teacher_examination");
        Zend_Registry::set('T_ENROLL_STUDENT_TEMP', "t_enroll_student_temp");
        Zend_Registry::set('T_USER_ACTIVITY', "t_user_activity");
        Zend_Registry::set('T_VIEW_ACADEMIC', "t_view_academic");
        Zend_Registry::set('T_VIEW_STUDENT_SCHOOLYEAR', "t_view_student_schoolyear");
        Zend_Registry::set('T_VIEW_STUDENT_EXPEL', "t_view_student_expel");
        Zend_Registry::set('T_VIEW_STUDENT_SUBJECT_AVERAGE', "t_view_student_subject_average");
        Zend_Registry::set('T_VIEW_STUDENT_YEAR_SUBJECT_AVERAGE', "t_view_student_year_subject_average");
        Zend_Registry::set('T_VIEW_STUDENT_ANUAL_GPA', "t_view_student_anual_gpa");
        Zend_Registry::set('T_VIEW_ASSIGNMENT', "t_view_assignment");
        Zend_Registry::set('T_VIEW_STUDENT_ASSIGNMENT', "t_view_student_assignment");
        Zend_Registry::set('T_VIEW_SUBJECT_TEACHER_CLASS', "t_view_subject_teacher_class");
        
    }

    static public function makeSchoolRegistry($MEMBER_OBJECT, $schoolObject, $SESSION_OBJECT) {
        
        Zend_Registry::set('SCHOOL_ID', $schoolObject->ID);
        Zend_Registry::set('SCHOOL', $schoolObject);
        Zend_Registry::set('IS_PROVIDER', $SESSION_OBJECT->ISSUPERADMIN);
        Zend_Registry::set('URL_MAIN', "main?_sid=" . $SESSION_OBJECT->ID . "");
        
        //Zend_Registry::set('SMS_URL_GATEWARY', "http://service.altamedia.vn/mygateway/sendsms?");
        Zend_Registry::set('SMS_URL_GATEWARY', "http://igapi.altamedia.vn/sendsms.aspx?");
        
        if (Zend_Registry::get('MODUL_API') == "SCHOOL") {
            switch ($schoolObject->MODUL_KEY) {

                case CAMEMISModulConfig::CE_KEY:
                    return self::getModulCE();
                    break;
                case CAMEMISModulConfig::ME_KEY:
                    return self::getModulME();
                    break;
                default:
                    return self::getModulCE();
                    break;
            }
        } elseif (Zend_Registry::get('MODUL_API') == "UNIVERSITY") {
            return self::getModulUE();
        } else {
            return self::getModulCE();
        }
    }

    static public function makeModulRegistry() {

        Zend_Registry::set('ZEND_REGISTRY', Zend_Registry::getInstance());
        Zend_Registry::set('APPLICATION_TYPE', CAMEMISConfigBasic::APPLICATION_TYPE);
        Zend_Registry::set('APPLICATION_DEMO', CAMEMISModulConfig::APPLICATION_DEMO);
        Zend_Registry::set('MULTI_SYSTEM_LANGUAGE', CAMEMISModulConfig::MULTI_SYSTEM_LANGUAGE);
    }

    static public function makeUserRegistry($MEMBER_OBJECT, $ACL_DATA, $USER_ROLE_OBJECT, $SESSION_OBJECT) {

        Zend_Registry::set('SESSIONID', $SESSION_OBJECT->ID);
        Zend_Registry::set('USER', $MEMBER_OBJECT);
        Zend_Registry::set('USERID', $MEMBER_OBJECT->ID);
        Zend_Registry::set('ROLE', $MEMBER_OBJECT->ROLE);
        Zend_Registry::set('LESSON_COUNT', Zend_Registry::get('SESSION_OBJECT')->checkCountSessions($MEMBER_OBJECT->ID));

        if ($SESSION_OBJECT->ISSUPERADMIN == 1) {
            Zend_Registry::set('IS_SUPER_ADMIN', 1);
        } else {
            Zend_Registry::set('IS_SUPER_ADMIN', 0);
        }
        self::makeGeneralUserEduRegistry($MEMBER_OBJECT, $ACL_DATA, $USER_ROLE_OBJECT, $SESSION_OBJECT);
    }

    static public function makeGeneralUserEduRegistry($MEMBER_OBJECT, $ACL_DATA, $USER_ROLE_OBJECT, $SESSION_OBJECT) {
        
        Zend_Registry::set('ADDITIONAL_ROLE', false);
        Zend_Registry::set('MODUL_NAME', CAMEMISModulConfig::ME_NAME);
        
        if ($MEMBER_OBJECT->ROLE == "STUDENT") {
            
            Zend_Registry::set('SKIN', "BLUE");
            Zend_Registry::set('ISDEMO', false);
            Zend_Registry::set('LOGIN_USER', 1);
            Zend_Registry::set('USER_TYPE', "STUDENT");
            Zend_Registry::set('ACL_DATA', array());
            
        } else {

            Zend_Registry::set('SKIN', $MEMBER_OBJECT->SKIN);
            Zend_Registry::set('ISDEMO', 0);
            Zend_Registry::set('LOGIN_USER', 1);

            switch ($USER_ROLE_OBJECT->TUTOR) {
                case 1:
                    Zend_Registry::set('USER_TYPE', "INSTRUCTOR");
                    Zend_Registry::set('INSTRUCTOR_ID', $MEMBER_OBJECT->ID);
                    Zend_Registry::set('TEACHER_ID', $MEMBER_OBJECT->ID);
                    break;
                case 2:
                    Zend_Registry::set('USER_TYPE', "TEACHER");
                    Zend_Registry::set('TEACHER_ID', $MEMBER_OBJECT->ID);
                    break;
                default:
                    
                    Zend_Registry::set('ADDITIONAL_ROLE', $MEMBER_OBJECT->ADDITIONAL_ROLE);
                    if (self::rightValue($ACL_DATA, 'SIMPLE_USER')) {

                        Zend_Registry::set('USER_TYPE', "SIMPLE_USER");
                    } else {
                        Zend_Registry::set('USER_TYPE', "SYSTEM");
                    }

                    if ($SESSION_OBJECT->ISSUPERADMIN == 1) {
                        Zend_Registry::set('SUPER_ADMIN', true);
                    }
                    break;
            }
            
            Zend_Registry::set('ACL_DATA', $ACL_DATA);
            
            Zend_Registry::set('USER_ROLE', $USER_ROLE_OBJECT);
            
            Zend_Registry::set('USER_ROLE', $USER_ROLE_OBJECT->NAME);
        }
    }

    static public function rightsStudentModul($ACL_DATA) {

    }

    static public function rightsStaffModul($ACL_DATA) {

    }

    static public function rightsAcademicModul($ACL_DATA) {

    }

    static public function rightsAdministrationModul($ACL_DATA) {

    }

    static public function makeHigherUserEduRegistry($MEMBER_OBJECT, $ACL_DATA, $SESSION_OBJECT) {

    }

    static function getModulCE() {

    }

    static function getModulME() {

    }

    static function getModulUE() {

    }

    static function rightValue($data, $index) {
        return isset($data["" . $index . ""]) ? $data["" . $index . ""] : false;
    }
}

?>