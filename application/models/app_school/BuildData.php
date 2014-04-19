<?

/**
 * @author Kaom Vibolrith
 * 06.08.2009
 */
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/academic/AcademicLevelDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/student/StudentDBAccess.php';
require_once 'models/DisciplineDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'models/app_school/subject/GradeSubjectDBAccess.php';
require_once 'models/app_school/school/SchoolDBAccess.php';
require_once 'models/app_school/user/UserRoleDBAccess.php';
require_once 'models/app_school/user/OrganizationDBAccess.php';
require_once 'models/app_school/AbsentTypeDBAccess.php';
require_once 'utiles/Utiles.php';
require_once 'models/app_school/room/RoomDBAccess.php';
require_once 'models/app_school/subject/TrainingSubjectDBAccess.php';
require_once 'models/app_school/training/TrainingDBAccess.php';
require_once 'models/CamemisTypeDBAccess.php';

Class BuildData {

    static function comboAbsentType($objectType) {

        $result = AbsentTypeDBAccess::allAbsentType($objectType);
        $data = array();

        $data[0] = "[\"0\",\"[---]\"]";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"$value->NAME\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    //@THORN Visal
    static function comboAcademicClasses($params) {

        $result = ScheduleDBAccess::getCreditClassInAcademicSubject($params);
        $data = array();

        $data[0] = "[\"0\",\"[---]\"]";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"$value->NAME\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    //@Sea Peng
    static function comboCamemisType($objectType) {

        $result = CamemisTypeDBAccess::getCamemisType($objectType);
        $data = array();

        $data[0] = "[\"\",\"[---]\"]";

        if ($result) {
            $i = 0;
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"$value->NAME\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    //

    static function comboAcademicData() {
        $facett = AcademicDateDBAccess::getInstance();
        return $facett->AcademicDateCombo();
    }

    static function comboCampus() {

        $result = AcademicDBAccess::allCampus();
        $data = array();

        if ($result) {
            $i = 0;
            $data[0] = "['0','[---]']";
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"$value->NAME\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    static function comboTrainingprograms() {

        $result = TrainingDBAccess::allTrainingprograms();
        $data = array();

        if ($result) {
            $i = 0;
            $data[0] = "['0','[---]']";
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"$value->NAME\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    static function comboGrade() {
        return Utiles::comboData(AcademicDBAccess::allGrade());
    }

    static function comboLastSchoolyearData() {
        return Utiles::comboData(AcademicDateDBAccess::getListLastSchoolyear());
    }

    static function comboDataTeacherByGrade() {

        $facett = StaffDBAccess::getInstance();
        return Utiles::comboData($facett->allTeachersByGrade());
    }

    static function comboDataUserRole() {
        $result = UserRoleDBAccess::allUserRole();

        $data = array();

        if ($result) {
            $i = 0;
            $data[0] = "['0','---']";
            foreach ($result as $value) {
                $data[$i + 1] = "[\"" . $value->ID . "\",\"" . $value->NAME . "\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    static function comboDataStudentByClass() {

        $facett = StudentDBAccess::getInstance();
        return $facett->comboDataStudentByClass();
    }

    static function comboDataAllTeacher() {

        $DB_STAFF = StaffDBAccess::getInstance();
        $result = $DB_STAFF->comboAllTutor();

        $data = array();

        if ($result) {
            $i = 0;
            $data[0] = "['0','[---]']";
            foreach ($result as $value) {
                $data[$i + 1] = "[\"$value->ID\",\"$value->NAME\"]";
                $i++;
            }
        }
        return "[" . implode(",", $data) . "]";
    }

    static function comboDataTeachersByGrade() {

        $facett = StaffDBAccess::getInstance();
        return Utiles::comboData($facett->allTeachersByGrade());
    }

    static function comboDataRoom() {

        $facett = RoomDBAccess::getInstance();
        return $facett->allRoomsComboData();
    }

    static function comboDataSubjectsByGrade() {

        $facett = GradeSubjectDBAccess::getInstance();
        return $facett->SubjectByGradeCombo();
    }

    static function comboDataSubjectsByTraining() {

        $facett = TrainingSubjectDBAccess::getInstance();
        return $facett->SubjectByTrainingCombo();
    }

    static function checkboxDataSubjects() {

        $facett = SubjectDBAccess::getInstance();
        return $facett->SubjectCheckBox();
    }

    static function superboxSubjects() {
        return SubjectDBAccess::allSubjectsComboData();
    }

    static function superboxCampus() {

        $facett = AcademicLevelDBAccess::getInstance();
        return $facett->allCampusComboData();
    }

    static function comboDataPunishment() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("PUNISHMENT_TYPE");
    }

    static function comboDataSubjectsByTeacher() {

        $facett = SubjectDBAccess::getInstance();
        return $facett->SubjectByTeacherCombo();
    }

    static function comboDataAllSubjects() {
        return SubjectDBAccess::allSubjectsComboData();
    }

    static function comboDataAllReligion() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("RELIGION_TYPE");
    }

    static function comboDataAllEthnic() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("ETHNICITY_TYPE");
    }

    static function comboDataAllNationality() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("NATIONALITY_TYPE");
    }
    
    ///
    static function comboDataAllMajor() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("MAJOR_TYPE");
    }
    
    static function comboDataAllQualitycationDegree() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("QUALIFICATION_DEGREE_TYPE");
    }
    ///

    static function comboDataAllOrganization() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("ORGANIZATION_TYPE");
    }

    static function comboDataAllPersonalDescription($parent, $personType, $type = false) {
        $facett = DescriptionDBAccess::getInstance();
        return $facett->allPersonalDescriptionComboData($parent, $personType, $type);
    }

    static function comboDataQualificationType() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("QUALIFICATION_TYPE");
    }

    static function comboDataSubjectType() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("SUBJECT_TYPE");
    }

    static function comboDataAllEyeChart() {
        return CamemisTypeDBAccess::getCamemisTypeComboData("EYECHART_TYPE");
    }

}

?>