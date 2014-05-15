<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 27.12.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/subject/SubjectDBAccess.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/room/RoomDBAccess.php';
require_once 'models/app_school/academic/AcademicLevelDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDBAccess.php';
require_once 'models/app_school/schedule/TeachingSessionDBAccess.php';
require_once 'models/app_school/student/StudentAttendanceDBAccess.php';
require_once setUserLoacalization();

class DayScheduleDBAccess extends ScheduleDBAccess {

    static function getInstance() {
        static $me;
        if ($me == null) {
            $me = new DayScheduleDBAccess();
        }
        return $me;
    }

    public function dayEventList($params, $isJson = true) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "50";

        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : false;
        $trainingId = isset($params["trainingId"]) ? addText($params["trainingId"]) : false;
        $eventDay = isset($params["eventDay"]) ? $params["eventDay"] : false;
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : false;
        $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : false;   //@new....  veasna
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : false; //@new....  veasna
        ///@veasna
        $checkAcademicId = '';
        $term = '';
        $schoolyearId = '';
        $shortday = getWEEKDAY($eventDay);

        if (!$studentId) {
            if ($academicId) {
                $academicObject = AcademicDBAccess::findGradeFromId($academicId);
                //@veasna  credit education system
                if ($academicObject->EDUCATION_SYSTEM) {
                    switch ($academicObject->OBJECT_TYPE) {
                        case "SUBJECT":
                            $academicId = $academicObject->ID;
                            $checkAcademicId = "";
                            break;
                        case "CLASS":
                            $academicId = $academicObject->PARENT;
                            $checkAcademicId = $academicObject->ID;
                            break;
                    }
                }
                $schoolyearId = $academicObject->SCHOOL_YEAR;
                $term = AcademicDBAccess::getNameOfSchoolTermByDate($eventDay, $academicObject->ID);
            } else {
                $TRAINING_OBJECT = $this->DB_TRAINING->findTrainingFromId($trainingId);
                $term = $TRAINING_OBJECT->TERM;
            }
        }

        ///@veasna
        if ($checkAcademicId) {
            $scheduldClassArr = array();
            $scheduldClass = self::findLinkedScheduleAcademicByAcademicId($checkAcademicId);
            foreach ($scheduldClass as $schedule) {
                $scheduldClassArr[] = $schedule->SCHEDULE_ID;
            }
        }

        if ($studentId) {
            $facette = GradeSubjectDBAccess::getStudentCreditSubject($studentId, $schoolyearId);
            $studentGroupArr = array();
            $studentGroup = '';
            if ($facette) {
                foreach ($facette as $studentInSubject) {
                    if ($studentInSubject->CLASS_ID) {
                        $studentGroupArr[] = $studentInSubject->CLASS_ID;
                    }
                }
                $studentGroup = implode(',', $studentGroupArr);
            }
        }
        ///

        $params["academicId"] = $academicId;
        $params["schoolyearId"] = $schoolyearId;
        $params["shortday"] = $shortday;
        $params["term"] = $term;
        if ($teacherId)
            $params["teacherId"] = $teacherId;
        $resultRows = self::getSQLClassEvents($params);

        $DISPLAY_SUBJECT = Zend_Registry::get('SCHOOL')->SUBJECT_DISPLAY ? "SUBJECT_SHORT" : "SUBJECT_NAME";
        $data = array();

        $i = 0;
        if ($resultRows) {
            foreach ($resultRows as $value) {
                ///$veasna
                if ($checkAcademicId) {
                    if (!in_array($value->SCHEDULE_ID, $scheduldClassArr))
                        continue;
                }
                if ($studentId) {
                    $inCheck = self::checkCreditScheduleInGroup($value->SCHEDULE_ID, $studentGroup);
                    if (!$inCheck) {
                        continue;
                    }

                    $groupObject = StudentAttendanceDBAccess::getClassStudentInSchedule($studentId, $value->SCHEDULE_ID);
                    if ($groupObject) {
                        $data[$i]["GROUP_NAME"] = $groupObject->CLASS_NAME;
                        $data[$i]["GROUP_ID"] = $groupObject->CLASS_ID;
                    }
                }
                ///

                $TEACHING_OBJECT = TeachingSessionDBAccess::getTeachingSessionFromId($value->GUID);

                if ($TEACHING_OBJECT) {
                    $data[$i]["ID"] = $TEACHING_OBJECT->GUID;

                    switch ($TEACHING_OBJECT->ACTION_TYPE) {
                        case "SUBSTITUTE":
                            $data[$i]["CHDECK_STATUS"] = 1;
                            $data[$i]["TEACHING_STATUS"] = TeachingSessionDBAccess::getDetailSubstitute($TEACHING_OBJECT);
                            break;
                        case "DAYOFFSCHOOL":
                            $data[$i]["CHDECK_STATUS"] = 2;
                            $data[$i]["TEACHING_STATUS"] = DAY_OFF_SCHOOL;
                            break;
                    }
                } else {
                    $data[$i]["ID"] = $value->GUID;
                    $data[$i]["CHDECK_STATUS"] = 0;
                    $data[$i]["TEACHING_STATUS"] = "";
                }

                $data[$i]["CODE"] = setShowText($value->SCHEDULE_CODE);

                switch ($value->SCHEDULE_TYPE) {
                    case 1:
                        $data[$i]["EVENT"] = setShowText($value->$DISPLAY_SUBJECT);
                        $groupObject = self::findLinkedScheduleAcademicByScheduleId($value->SCHEDULE_ID);
                        if ($groupObject) {
                            $j = 0;
                            foreach ($groupObject as $group) {
                                if ($j != 0)
                                    $data[$i]["EVENT"] .=", ";
                                $data[$i]["EVENT"] .= setShowText($group->NAME);
                                ++$j;
                            }
                            $data[$i]["EVENT"] .= "<br>";
                        }
                        $data[$i]["COLOR"] = $value->SUBJECT_COLOR;
                        $data[$i]["COLOR_FONT"] = getFontColor($value->SUBJECT_COLOR);
                        break;
                    case 2:
                        $data[$i]["EVENT"] = EVENT;
                        $data[$i]["EVENT"] .= "<br>";
                        $data[$i]["EVENT"] .= setShowText($value->EVENT);
                        $data[$i]["COLOR"] = "#FFF";
                        break;
                }
                $data[$i]["DESCRIPTION"] = $value->DESCRIPTION;
                $data[$i]["SCHEDULE_TYPE"] = $value->SCHEDULE_TYPE;
                $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                $data[$i]["START_DATE"] = secondToHour($value->START_TIME);
                $data[$i]["END_DATE"] = secondToHour($value->END_TIME);
                $data[$i]["TEACHER"] = setShowText($value->TEACHER);
                $data[$i]["ROOM"] = setShowText($value->ROOM);

                $i++;
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        //@soda
        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
        //
    }

}

?>