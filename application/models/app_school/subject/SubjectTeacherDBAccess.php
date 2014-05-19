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

class SubjectTeacherDBAccess extends SubjectDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function findTeacherInClass($teacherId) {
        $SQL = self::dbAccess()->select()
                ->from("t_subject_teacher_class", array('*'))
                ->where("TEACHER = '" . $teacherId . "'");
        return self::dbAccess()->fetchAll($SQL);
    }

    public function checkAssignedSubjectTeacherClass($subjectId, $teacherId, $classId, $term) {

        $SQL = "SELECT DISTINCT COUNT(*) AS C"; 
        $SQL .= " FROM t_subject_teacher_class";
        $SQL .= " WHERE 1=1";
        if ($subjectId)
            $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        if ($teacherId)
            $SQL .= " AND TEACHER = '" . $teacherId . "'";
        if ($classId)
            $SQL .= " AND ACADEMIC = '" . $classId . "'";
        if ($term)
            $SQL .= " AND GRADINGTERM = '" . $term . "'";

        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    //@SODA
    public function checkAssignedSubjectTeacherTraining($subjectId, $teacherId, $classId, $term) {

        $SQL = "SELECT DISTINCT COUNT(*) AS C"; 
        $SQL .= " FROM t_subject_teacher_training";
        $SQL .= " WHERE 1=1";
        if ($subjectId)
            $SQL .= " AND SUBJECT = '" . $subjectId . "'";
        if ($teacherId)
            $SQL .= " AND TEACHER = '" . $teacherId . "'";
        if ($classId)
            $SQL .= " AND TRAINING = '" . $classId . "'";
        if ($term)
            $SQL .= " AND LEVEL = '" . $term . "'";

        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    //

    public function jsonLoadTeachersBySubject($params) {

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";

        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : "0";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "0";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);
        $classId = $academicObject ? $academicObject->ID : 0;

        $SQL = "";
        $SQL .= " SELECT DISTINCT";
        $SQL .= " A.ID AS TEACHER_ID";
        $SQL .= " ,A.LASTNAME AS LASTNAME";
        $SQL .= " ,A.FIRSTNAME AS FIRSTNAME";
        $SQL .= " ,CONCAT(A.LASTNAME,' ',A.FIRSTNAME) AS FULL_NAME";
        $SQL .= " ,A.CODE AS TEACHER_CODE";
        $SQL .= " FROM t_staff AS A";
        $SQL .= " RIGHT JOIN t_teacher_subject AS B ON A.ID=B.TEACHER";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.STATUS='1'";
        $SQL .= " AND B.SUBJECT='" . $subjectId . "'";
        $SQL .= " AND B.TEACHER IS NOT NULL";
        $SQL .= " GROUP BY A.ID";
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();

        if ($result) {
            $i = 0;
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->TEACHER_ID;
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["CODE"] = $value->TEACHER_CODE;
                $data[$i]["FIRST_SEMESTER"] = $this->checkAssignedSubjectTeacherClass(
                        $subjectId
                        , $value->TEACHER_ID
                        , $classId
                        , "FIRST_SEMESTER"
                );
                $data[$i]["SECOND_SEMESTER"] = $this->checkAssignedSubjectTeacherClass(
                        $subjectId
                        , $value->TEACHER_ID
                        , $classId
                        , "SECOND_SEMESTER"
                );

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
            , "totalCount" => $data
            , "rows" => $a
        );
    } 
    
    public static function jsonLoadTeachersBySubjectID($gradesubjectId, $subjectId, $classId) {

        $SQL = "";
        $SQL .= " SELECT";
        $SQL .= " B.TEACHER AS TEACHER";
        $SQL .= " FROM t_grade_subject AS A"; 
        $SQL .= " LEFT JOIN t_subject_teacher_class AS B ON B.SUBJECT = A.SUBJECT";
        $SQL .= " WHERE 1=1";
        if ($gradesubjectId)
            $SQL .= " AND A.ID = '" . $gradesubjectId . "'";
        if ($subjectId)
            $SQL .= " AND B.SUBJECT = '" . $subjectId . "'"; 
        if ($classId) 
            $SQL .= " AND B.ACADEMIC = '" . $classId . "'"; 
            $SQL .= " GROUP BY A.ID"; 
            //error_log($SQL);     
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function addSubjectTeacherClassTerm($subjectId, $teacherId, $academicId, $term) {

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        //Egal was kommt, wird entfernt...
        $condition = array(
            'GRADE = ? ' => $academicObject->GRADE_ID
            , 'ACADEMIC = ? ' => $academicObject->ID
            , 'SUBJECT = ? ' => $subjectId
            , 'GRADINGTERM = ? ' => $term
        );
        self::dbAccess()->delete('t_subject_teacher_class', $condition);

        $SAVEDATA["GRADINGTERM"] = $term;
        $SAVEDATA["CAMPUS"] = $academicObject->CAMPUS_ID;
        $SAVEDATA["SCHOOLYEAR"] = $academicObject->SCHOOL_YEAR;
        $SAVEDATA["SUBJECT"] = $subjectId;
        $SAVEDATA["ACADEMIC"] = $academicObject->ID;
        $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
        $SAVEDATA["TEACHER"] = $teacherId;

        self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);
    }

    public static function addSubjectTeacherTraining($subjectId, $teacherId, $trainingId) {

        $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);

        //Egal was kommt, wird entfernt...
        $condition = array(
            'TERM = ? ' => $trainingObject->TERM
            , 'LEVEL = ? ' => $trainingObject->LEVEL
            , 'SUBJECT = ? ' => $subjectId
            , 'TRAINING = ? ' => $trainingObject->ID
            , 'PROGRAM = ? ' => $trainingObject->PROGRAM
        );
        self::dbAccess()->delete('t_subject_teacher_training', $condition);

        $SAVEDATA["TERM"] = $trainingObject->TERM;
        $SAVEDATA["LEVEL"] = $trainingObject->LEVEL;
        $SAVEDATA["SUBJECT"] = $subjectId;
        $SAVEDATA["TRAINING"] = $trainingObject->ID;
        $SAVEDATA["PROGRAM"] = $trainingObject->PROGRAM;
        $SAVEDATA["TEACHER"] = $teacherId;

        self::dbAccess()->insert('t_subject_teacher_training', $SAVEDATA);
    }

    public function actionSubjectTeacherClass($params) {

        $SAVEDATA = array();

        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $term = isset($params["field"]) ? addText($params["field"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";
        $teacherId = isset($params["id"]) ? addText($params["id"]) : "";
        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : "";

        $academicObject = AcademicDBAccess::findGradeFromId($academicId);

        //Load Teacher by subjectId, classId, term
        $SQL = self::dbAccess()->select();
        $SQL->from('t_schedule', '*');
        $SQL->where("SUBJECT_ID = '" . $subjectId . "'");
        $SQL->where("ACADEMIC_ID = '" . $academicObject->ID . "'");
        $SQL->where("TERM = '" . $term . "'");
        $SQL->group("SUBJECT_ID");
        //error_log($SQL->__toString());
        $facette = self::dbAccess()->fetchRow($SQL);

        //Egal was kommt, wird entfernt...
        $condition = array(
            'GRADE = ? ' => $academicObject->GRADE_ID
            , 'ACADEMIC = ? ' => $academicObject->ID
            , 'SUBJECT = ? ' => $subjectId
            , 'GRADINGTERM = ? ' => $term
        );
        self::dbAccess()->delete('t_subject_teacher_class', $condition);

        //ob Teacher in Schedule
        $inSchedule = false;
        if ($facette) {
            if ($facette->TEACHER_ID) {
                //verwendet nur Teacher in Schedule
                $newTeacher = $facette->TEACHER_ID;
                $inSchedule = true;
            } else {
                //kein Teacher in Schedule, dann verwendet selected Teacher
                $newTeacher = $teacherId;
            }
        } else {
            //kein Teacher in Schedule, dann verwendet selected Teacher
            $newTeacher = $teacherId;
        }

        $SAVEDATA["GRADINGTERM"] = $term;
        $SAVEDATA["CAMPUS"] = $academicObject->CAMPUS_ID;
        $SAVEDATA["SCHOOLYEAR"] = $academicObject->SCHOOL_YEAR;
        $SAVEDATA["SUBJECT"] = $subjectId;
        $SAVEDATA["ACADEMIC"] = $academicObject->ID;
        $SAVEDATA["GRADE"] = $academicObject->GRADE_ID;
        $SAVEDATA["TEACHER"] = $newTeacher;

        if ($newValue) {
            //bei Aktivierung, dann DB schreiben
            self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);
        } elseif ($inSchedule && !$newValue) {
            //Trotz Deaktivierung in DB schreiben, weil der Teacher zuvor entfernt worde war.
            self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);
        }

        return array(
            "success" => true
        );
    }

    public static function checkTeacherAvailableDays($teacherId, $subjectId, $term) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_teacher_subject', '*');
        $SQL->where("SUBJECT = '" . $subjectId . "'");
        $SQL->where("TEACHER = '" . $teacherId . "'");
        //error_log($SQL->__toString());
        $facette = self::dbAccess()->fetchRow($SQL);

        if ($facette) {

            $FS_DAYS = $facette->FS_DAYS ? explode(",", $facette->FS_DAYS) : array();
            $SS_DAYS = $facette->SS_DAYS ? explode(",", $facette->SS_DAYS) : array();

            $FS_CHECK_DAY = array();
            if ($FS_DAYS) {
                foreach ($FS_DAYS as $value) {
                    $FS_CHECK_DAY[$value] = defined($value) ? constant($value) : $value;
                }
            }

            $SS_CHECK_DAY = array();
            if ($SS_DAYS) {
                foreach ($SS_DAYS as $value) {
                    $SS_CHECK_DAY[$value] = defined($value) ? constant($value) : $value;
                }
            }

            switch ($term) {
                case "FIRST_SEMESTER":
                    return implode(", ", $FS_CHECK_DAY);
                    break;
                case "SECOND_SEMESTER":
                    return implode(", ", $SS_CHECK_DAY);
                    break;
            }
        } else {
            return "---";
        }
    }

    public static function jsonLoadTeacherSubjectDays($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : "";

        $SQL = self::dbAccess()->select();
        $SQL->from('t_teacher_subject', '*');
        $SQL->where("SUBJECT = '" . $subjectId . "'");
        $SQL->where("TEACHER = '" . $objectId . "'");
        //error_log($SQL->__toString());
        $facette = self::dbAccess()->fetchRow($SQL);

        if ($facette) {

            $FS_DAYS = $facette->FS_DAYS ? explode(",", $facette->FS_DAYS) : array();
            $SS_DAYS = $facette->SS_DAYS ? explode(",", $facette->SS_DAYS) : array();

            $FS_CHECK_DAY = array();
            if ($FS_DAYS) {
                foreach ($FS_DAYS as $value) {
                    $FS_CHECK_DAY[$value] = $value;
                }
            }

            $SS_CHECK_DAY = array();
            if ($SS_DAYS) {
                foreach ($SS_DAYS as $value) {
                    $SS_CHECK_DAY[$value] = $value;
                }
            }

            $data["FS_MONDAY"] = isset($FS_CHECK_DAY["MONDAY"]) ? true : false;
            $data["FS_TUESDAY"] = isset($FS_CHECK_DAY["TUESDAY"]) ? true : false;
            $data["FS_WEDNESDAY"] = isset($FS_CHECK_DAY["WEDNESDAY"]) ? true : false;
            $data["FS_THURSDAY"] = isset($FS_CHECK_DAY["THURSDAY"]) ? true : false;
            $data["FS_FRIDAY"] = isset($FS_CHECK_DAY["FRIDAY"]) ? true : false;
            $data["FS_SATURDAY"] = isset($FS_CHECK_DAY["SATURDAY"]) ? true : false;
            $data["FS_SUNDAY"] = isset($FS_CHECK_DAY["SUNDAY"]) ? true : false;

            $data["SS_MONDAY"] = isset($SS_CHECK_DAY["MONDAY"]) ? true : false;
            $data["SS_TUESDAY"] = isset($SS_CHECK_DAY["TUESDAY"]) ? true : false;
            $data["SS_WEDNESDAY"] = isset($SS_CHECK_DAY["WE"]) ? true : false;
            $data["SS_THURSDAY"] = isset($SS_CHECK_DAY["THURSDAY"]) ? true : false;
            $data["SS_FRIDAY"] = isset($SS_CHECK_DAY["FRIDAY"]) ? true : false;
            $data["SS_SATURDAY"] = isset($SS_CHECK_DAY["SATURDAY"]) ? true : false;
            $data["SS_SUNDAY"] = isset($SS_CHECK_DAY["SUNDAY"]) ? true : false;

            $o = array(
                "success" => true
                , "data" => $data
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }

        return $o;
    }

    public static function jsonSaveTeacherSubjectDays($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $subjectId = isset($params["subjectId"]) ? (int) $params["subjectId"] : "";

        $FIRST_SEMESTER = array(
            "FS_MONDAY" => "FS_MONDAY"
            , "FS_TUESDAY" => "FS_TUESDAY"
            , "FS_WEDNESDAY" => "FS_WEDNESDAY"
            , "FS_THURSDAY" => "FS_THURSDAY"
            , "FS_FRIDAY" => "FS_FRIDAY"
            , "FS_SATURDAY" => "FS_SATURDAY"
            , "FS_SUNDAY" => "FS_SUNDAY"
        );

        $SECOND_SEMESTER = array(
            "SS_MONDAY" => "SS_MONDAY"
            , "SS_TUESDAY" => "SS_TUESDAY"
            , "SS_WEDNESDAY" => "SS_WEDNESDAY"
            , "SS_THURSDAY" => "SS_THURSDAY"
            , "SS_FRIDAY" => "SS_FRIDAY"
            , "SS_SATURDAY" => "SS_SATURDAY"
            , "SS_SUNDAY" => "SS_SUNDAY"
        );

        $CHOOSE_DAYS_FS = array();
        foreach ($FIRST_SEMESTER as $value) {
            if (isset($params["$value"])) {
                $CHOOSE_DAYS_FS[substr($value, 3)] = substr($value, 3);
            }
        }

        $FS_DAYS = implode(",", $CHOOSE_DAYS_FS);
        $CHOOSE_DAYS_SS = array();
        foreach ($SECOND_SEMESTER as $value) {
            if (isset($params["$value"])) {
                $CHOOSE_DAYS_SS[substr($value, 3)] = substr($value, 3);
            }
        }

        $SS_DAYS = implode(",", $CHOOSE_DAYS_SS);

        $SQL = "";
        $SQL .= "UPDATE t_teacher_subject";
        $SQL .= " SET";
        $SQL .= " FS_DAYS='" . $FS_DAYS . "'";
        $SQL .= " ,SS_DAYS='" . $SS_DAYS . "'";
        $SQL .= " WHERE";
        $SQL .= " TEACHER='" . $objectId . "'";
        $SQL .= " AND SUBJECT='" . $subjectId . "'";
        self::dbAccess()->query($SQL);

        return array(
            "success" => true
        );
    }

}

?>