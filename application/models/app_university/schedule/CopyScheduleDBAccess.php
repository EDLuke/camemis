<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 18.05.2011
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_university/academic/AcademicDBAccess.php';
require_once 'models/app_university/AcademicDateDBAccess.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
require_once 'models/app_university/room/RoomDBAccess.php';
require_once 'models/app_university/schedule/ScheduleDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class CopyScheduleDBAccess extends ScheduleDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new CopyScheduleDBAccess();
        }
        return $me;
    }

    public function getScheduleByClassId($academicId, $shortday = false, $term = false, $trainingId = false) {

        $SQL = self::dbSelect();
        $SQL->from(array('A' => self::TABLE_SCHEDULE), array('*'));

        if ($academicId)
            $SQL->where('A.ACADEMIC_ID = ?', $academicId);

        if ($trainingId)
            $SQL->where('A.TRAINING_ID = ?', $trainingId);

        if ($shortday) {
            $SQL->where('A.SHORTDAY = ?', $shortday);
        }

        if ($term) {
            $SQL->where('A.TERM = ?', $term);
        }
        $SQL->group('A.GUID');
        $SQL->order('A.START_TIME');
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public function getSubjectTeacherClassByClassId($academicId) {

        $SQL = self::dbSelect();
        $SQL->from(array('A' => 't_subject_teacher_class'), array());
        $SQL->where('A.ACADEMIC = ?', $academicId);
        return self::dbAccess()->fetchAll($SQL);
    }

    public function copyScheduleFromPastClassId($newClassId, $oldClassId) {

        $classObject = AcademicDBAccess::findGradeFromId($newClassId);

        $OLD_SCHEULE_OBJECT_ARR = $this->getScheduleByClassId($oldClassId, false, false, false);

        $OLD_SUBJECT_TEACHER_CLASS_OBJECT_ARR = $this->getSubjectTeacherClassByClassId($oldClassId);

        $INSERT_SCHEDULE_COUNT = 0;

        if ($OLD_SCHEULE_OBJECT_ARR) {

            foreach ($OLD_SCHEULE_OBJECT_ARR as $key => $value) {

                if ($classObject) {

                    $SAVEDATA["CODE"] = createCode();
                    $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                    $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                    $SAVEDATA["GUID"] = generateGuid();
                    $SAVEDATA["SHORTDAY"] = $value->SHORTDAY;
                    $SAVEDATA["SCHOOLYEAR_ID"] = $classObject->SCHOOL_YEAR;
                    $SAVEDATA["TERM"] = $value->TERM;
                    $SAVEDATA["TEACHER_ID"] = $value->TEACHER_ID;
                    $SAVEDATA["ACADEMIC_ID"] = $newClassId;
                    $SAVEDATA["ROOM_ID"] = $value->ROOM_ID;
                    $SAVEDATA["SUBJECT_ID"] = $value->SUBJECT_ID;
                    $SAVEDATA["EVENT"] = $value->EVENT;
                    $SAVEDATA["STATUS"] = $value->STATUS;
                    $SAVEDATA["START_TIME"] = $value->START_TIME;
                    $SAVEDATA["END_TIME"] = $value->END_TIME;
                    $SAVEDATA["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                    $SAVEDATA["SHARED_SCHEDULE"] = $value->SHARED_SCHEDULE;
                    $SAVEDATA["GRADE_ID"] = $value->GRADE_ID;

                    self::dbAccess()->insert(self::TABLE_SCHEDULE, $SAVEDATA);
                    $newId = self::dbAccess()->lastInsertId();
                    self::copySharedAcademic($value->ID, $newId);

                    $INSERT_SCHEDULE_COUNT++;
                }
            }
        }

        if ($OLD_SUBJECT_TEACHER_CLASS_OBJECT_ARR) {

            if ($INSERT_SCHEDULE_COUNT) {

                foreach ($OLD_SUBJECT_TEACHER_CLASS_OBJECT_ARR as $key => $value) {

                    $SAVEDATA["TEACHER"] = $value->TEACHER;
                    $SAVEDATA["GRADE"] = $value->GRADE;
                    $SAVEDATA["ACADEMIC"] = $newClassId;
                    $SAVEDATA["SUBJECT"] = $value->SUBJECT;
                    $SAVEDATA["SCHOOLYEAR"] = $classObject->SCHOOL_YEAR;
                    $SAVEDATA["GRADINGTERM"] = $value->GRADINGTERM;

                    self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);

                    if ($value->CLASS && $value->TEACHER && $value->SUBJECT) {
                        self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);
                    }
                }
            }
        }
    }

    public function copyScheduleFromPreviousTerm($academicId, $fromTerm, $toTerm) {

        $condi = array(
            'ACADEMIC_ID = ? ' => $academicId
            , 'TERM = ? ' => $toTerm
        );
        self::dbAccess()->delete(self::TABLE_SCHEDULE, $condi);
        $result = $this->getScheduleByClassId($academicId, false, $fromTerm, false);

        if ($result) {
            foreach ($result as $value) {

                $SCHDEULE_DATA["CODE"] = createCode();
                $SCHDEULE_DATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SCHDEULE_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                $SCHDEULE_DATA["GUID"] = generateGuid();
                $SCHDEULE_DATA["SHORTDAY"] = $value->SHORTDAY;
                $SCHDEULE_DATA["SCHOOLYEAR_ID"] = $value->SCHOOLYEAR_ID;
                $SCHDEULE_DATA["TERM"] = $toTerm;
                $SCHDEULE_DATA["TEACHER_ID"] = $value->TEACHER_ID;
                $SCHDEULE_DATA["ACADEMIC_ID"] = $value->ACADEMIC_ID;
                $SCHDEULE_DATA["ROOM_ID"] = $value->ROOM_ID;
                $SCHDEULE_DATA["SUBJECT_ID"] = $value->SUBJECT_ID;
                $SCHDEULE_DATA["EVENT"] = $value->EVENT;
                $SCHDEULE_DATA["STATUS"] = $value->STATUS;
                $SCHDEULE_DATA["START_TIME"] = $value->START_TIME;
                $SCHDEULE_DATA["END_TIME"] = $value->END_TIME;
                $SCHDEULE_DATA["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                $SCHDEULE_DATA["SHARED_SCHEDULE"] = $value->SHARED_SCHEDULE;
                $SCHDEULE_DATA["GRADE_ID"] = $value->GRADE_ID;

                $CHECK_SS_SCHEDULE_ITEMS = $this->checkSecondSemesterScheduleItems($value);

                if (!$CHECK_SS_SCHEDULE_ITEMS) {

                    self::dbAccess()->insert(self::TABLE_SCHEDULE, $SCHDEULE_DATA);
                    $newId = self::dbAccess()->lastInsertId();
                    self::copySharedAcademic($value->ID, $newId);

                    $ASSIGNED_SUBJECT_TEACHER_CLASS = $this->checkAssignedSubjectTeacherClass(
                            $value->SUBJECT_ID
                            , $value->TEACHER_ID
                            , $value->ACADEMIC_ID
                            , $toTerm);

                    if (!$ASSIGNED_SUBJECT_TEACHER_CLASS) {
                        $classObject = AcademicDBAccess::findGradeFromId($value->ACADEMIC_ID);
                        $SUBECT_TEACHER_CLASS_DATA["TEACHER"] = $value->TEACHER_ID;
                        $SUBECT_TEACHER_CLASS_DATA["GRADE"] = $classObject->GRADE_ID;
                        $SUBECT_TEACHER_CLASS_DATA["ACADEMIC"] = $value->ACADEMIC_ID;
                        $SUBECT_TEACHER_CLASS_DATA["SUBJECT"] = $value->SUBJECT_ID;
                        $SUBECT_TEACHER_CLASS_DATA["SCHOOLYEAR"] = $value->SCHOOLYEAR_ID;
                        $SUBECT_TEACHER_CLASS_DATA["GRADINGTERM"] = $toTerm;

                        if ($value->ACADEMIC_ID && $value->TEACHER_ID && $value->SUBJECT_ID) {
                            self::dbAccess()->insert('t_subject_teacher_class', $SUBECT_TEACHER_CLASS_DATA);
                        }
                    }
                }
            }
        }

        return array(
            "success" => true
        );
    }

    protected function checkSecondSemesterScheduleItems($scheduleObject) {

        $SQL = "SELECT DISTINCT count(*) AS C";
        $SQL .= " FROM " . self::TABLE_SCHEDULE . "";
        $SQL .= " WHERE";
        $SQL .= " SUBJECT_ID = '" . $scheduleObject->SUBJECT_ID . "'";
        $SQL .= " AND ACADEMIC_ID = '" . $scheduleObject->ACADEMIC_ID . "'";
        $SQL .= " AND ROOM_ID = '" . $scheduleObject->ROOM_ID . "'";
        $SQL .= " AND SHORTDAY = '" . $scheduleObject->SHORTDAY . "'";
        $SQL .= " AND START_TIME = '" . $scheduleObject->START_TIME . "'";
        $SQL .= " AND END_TIME = '" . $scheduleObject->END_TIME . "'";
        $SQL .= " AND TERM = 'SECOND_SEMESTER'";
        $SQL .= " AND SCHOOLYEAR_ID = '" . $scheduleObject->SCHOOLYEAR_ID . "'";
        $result = self::dbAccess()->fetchRow($SQL);

        return $result ? $result->C : 0;
    }

    public function jsonCopyDayClassEvent($params) {

        $academicId = isset($params["academicId"]) ? (int) $params["academicId"] : false;
        $targetshortday = isset($params["targetshortday"]) ? $params["targetshortday"] : false;
        $sourceshortday = isset($params["sourceshortday"]) ? $params["sourceshortday"] : false;
        $term = isset($params["term"]) ? addText($params["term"]) : false;
        $trainingId = isset($params["trainingId"]) ? (int) $params["trainingId"] : "";

        if ($trainingId) {
            $result = $this->getScheduleByClassId(false, $sourceshortday, false, $trainingId);
            $condi = array(
                'TRAINING_ID = ? ' => $trainingId
                , 'SHORTDAY = ? ' => $targetshortday
            );
            self::dbAccess()->delete(self::TABLE_SCHEDULE, $condi);
        } else {
            $result = $this->getScheduleByClassId($academicId, $sourceshortday, $term, false);
            $condi = array(
                'ACADEMIC_ID = ? ' => $academicId
                , 'SHORTDAY = ? ' => $targetshortday
                , 'TERM = ? ' => $term
            );
            self::dbAccess()->delete(self::TABLE_SCHEDULE, $condi);
        }

        if ($result) {
            foreach ($result as $value) {

                $SCHDEULE_DATA["CODE"] = createCode();
                $SCHDEULE_DATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SCHDEULE_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                $SCHDEULE_DATA["GUID"] = generateGuid();
                $SCHDEULE_DATA["SHORTDAY"] = $targetshortday;
                $SCHDEULE_DATA["ROOM_ID"] = $value->ROOM_ID;
                $SCHDEULE_DATA["SUBJECT_ID"] = $value->SUBJECT_ID;
                $SCHDEULE_DATA["EVENT"] = $value->EVENT;
                $SCHDEULE_DATA["STATUS"] = $value->STATUS;
                $SCHDEULE_DATA["START_TIME"] = $value->START_TIME;
                $SCHDEULE_DATA["END_TIME"] = $value->END_TIME;
                $SCHDEULE_DATA["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                $SCHDEULE_DATA["SHARED_SCHEDULE"] = $value->SHARED_SCHEDULE;
                $SCHDEULE_DATA["GRADE_ID"] = $value->GRADE_ID;

                if ($trainingId) {
                    $SCHDEULE_DATA["TRAINING_ID"] = $trainingId;
                    $SCHDEULE_DATA["TEACHER_ID"] = $value->TEACHER_ID;
                    $SCHDEULE_DATA["TERM"] = "";
                    $SCHDEULE_DATA["SCHOOLYEAR_ID"] = "";
                    $SCHDEULE_DATA["ACADEMIC_ID"] = "";
                    $CHECK = self::checkAssignedSubjectTeacherTraining($value->SUBJECT_ID, $value->TEACHER_ID, $trainingId);
                    if (!$CHECK) {
                        if ($trainingId && $value->TEACHER_ID && $value->SUBJECT_ID) {

                            $trainingObject = TrainingDBAccess::findTrainingFromId($trainingId);
                            $SAVEDATA["TRAINING"] = $trainingId;
                            $SAVEDATA["TERM"] = $trainingObject->TERM;
                            $SAVEDATA["LEVEL"] = $trainingObject->LEVEL;
                            $SAVEDATA["PROGRAM"] = $trainingObject->PROGRAM;
                            $SAVEDATA["TEACHER"] = $value->TEACHER_ID;

                            self::dbAccess()->insert('t_subject_teacher_training', $SAVEDATA);
                        }
                    }
                } else {

                    $SCHDEULE_DATA["SCHOOLYEAR_ID"] = $value->SCHOOLYEAR_ID;
                    $SCHDEULE_DATA["TERM"] = $term;
                    $SCHDEULE_DATA["TEACHER_ID"] = $value->TEACHER_ID;
                    $SCHDEULE_DATA["ACADEMIC_ID"] = $value->ACADEMIC_ID;

                    $CHECK = $this->checkAssignedSubjectTeacherClass(
                            $value->SUBJECT_ID
                            , $value->TEACHER_ID
                            , $value->ACADEMIC_ID
                            , $term);

                    if (!$CHECK) {
                        $classObject = AcademicDBAccess::findGradeFromId($value->ACADEMIC_ID);
                        $SAVEDATA["TEACHER"] = $value->TEACHER_ID;
                        $SAVEDATA["GRADE"] = $classObject->GRADE_ID;
                        $SAVEDATA["ACADEMIC"] = $value->ACADEMIC_ID;
                        $SAVEDATA["SCHOOLYEAR"] = $value->SCHOOLYEAR_ID;
                        $SAVEDATA["GRADINGTERM"] = $term;

                        if ($value->ACADEMIC_ID && $value->TEACHER_ID && $value->SUBJECT_ID) {
                            self::dbAccess()->insert('t_subject_teacher_class', $SAVEDATA);
                        }
                    }
                }

                self::dbAccess()->insert(self::TABLE_SCHEDULE, $SCHDEULE_DATA);
                $newId = self::dbAccess()->lastInsertId();
                self::copySharedAcademic($value->ID, $newId);
            }
        }
    }

    public static function copySharedAcademic($targetId, $newId) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_link_schedule_academic', '*');
        $SQL->where("SCHEDULE_ID = '" . $targetId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                if ($newId) {
                    $SAVEDATA["TARGET"] = $value->TARGET;
                    $SAVEDATA["SCHEDULE_ID"] = $newId;
                    $SAVEDATA["ACADEMIC_ID"] = $value->ACADEMIC_ID;
                    $SAVEDATA["PARENT_ACADEMIC_ID"] = $value->PARENT_ACADEMIC_ID;
                    $SAVEDATA["TEACHING_SESSION_ID"] = $value->TEACHING_SESSION_ID;
                    self::dbAccess()->insert('t_link_schedule_academic', $SAVEDATA);
                }
            }
        }
    }

}

?>