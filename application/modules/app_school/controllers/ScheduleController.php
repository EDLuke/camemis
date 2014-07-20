<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();
require_once 'models/app_school/UserDBAccess.php';
require_once 'models/UserAuth.php';
require_once 'models/training/TrainingDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDBAccess.php';
require_once 'models/app_school/schedule/DayScheduleDBAccess.php';
require_once 'models/app_school/schedule/CampusScheduleDBAccess.php';
require_once 'models/app_school/schedule/TeacherScheduleDBAccess.php';
require_once 'models/app_school/schedule/TeachingSessionDBAccess.php';
require_once 'models/app_school/schedule/CopyScheduleDBAccess.php';
require_once 'models/app_school/schedule/ImportScheduleDBAccess.php';
require_once 'models/app_school/schedule/TeachingSessionDBAccess.php';
require_once 'models/app_school/schedule/ScheduleDaySettingData.php';//@veasna
require_once 'models/app_school/room/RoomSessionDBAccess.php';

class ScheduleController extends Zend_Controller_Action {

    protected $roleAdmin = array("SYSTEM");

    public function init() {

        if (!UserAuth::identify()) {
            $this->_request->setControllerName('error');
            $this->_request->setActionName('expired');
            $this->_request->setDispatched(false);
        }

        $this->REQUEST = $this->getRequest();
        $this->UTILES = Utiles::getInstance();

        $this->urlEncryp = new URLEncryption();
        $this->view->urlEncryp = $this->urlEncryp;
        if ($this->_getParam('camIds')) {
            $this->urlEncryp->parseEncryptedGET($this->_getParam('camIds'));
        }

        $this->DB_TRAINING = TrainingDBAccess::getInstance();
        
        $this->DB_SCHEDULE_DAY_SETTING = new ScheduleDaySettingData();

        $this->DB_ACADEMIC = AcademicDBAccess::getInstance();

        $this->DB_SCHEDULE = ScheduleDBAccess::getInstance();

        $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();

        $this->DB_DAY_SCHEDULE = DayScheduleDBAccess::getInstance();

        $this->DB_COPY_SCHEDULE = CopyScheduleDBAccess::getInstance();

        $this->DB_CAMPUS_SCHEDULE = CampusScheduleDBAccess::getInstance();

        $this->DB_TEACHER_SCHEDULE = TeacherScheduleDBAccess::getInstance();

        $this->DB_IMPORT_SCHEDULE = ImportScheduleDBAccess::getInstance();

        $this->scheduleId = null;

        $this->teachingId = null;

        $this->term = null;

        $this->academicId = null;

        $this->teacherId = null;

        $this->shortday = null;

        $this->subjectId = null;

        $this->roomId = null;

        $this->academicId = null;

        $this->CLASS_OBJECT = null;

        $this->facette = null;

        $this->target = "GENERAL";

        $this->trainingId = null;

        $this->choosedate = null;

        $this->objectId = null;

        $this->classId = null; //@veasna

        $this->studentId = null; //@veasna

        $this->schoolyearId = null; //@veasna

        $this->SCHEDULE_DATA = array();

        if ($this->_getParam('classId'))
            $this->classId = $this->_getParam('classId'); //@veasna

        if ($this->_getParam('studentId'))
            $this->studentId = $this->_getParam('studentId'); //@veasna

        if ($this->_getParam('schoolyearId'))
            $this->schoolyearId = $this->_getParam('schoolyearId'); //@veasna

        if ($this->_getParam('target'))
            $this->target = $this->_getParam('target');

        if ($this->_getParam('trainingId'))
            $this->trainingId = $this->_getParam('trainingId');

        if ($this->_getParam('academicId'))
            $this->academicId = $this->_getParam('academicId');

        if ($this->_getParam('scheduleId'))
            $this->scheduleId = $this->_getParam('scheduleId');

        if ($this->_getParam('teacherId'))
            $this->teacherId = $this->_getParam('teacherId');

        if ($this->_getParam('shortday'))
            $this->shortday = $this->_getParam('shortday');

        if ($this->_getParam('term'))
            $this->term = $this->_getParam('term');

        if ($this->_getParam('choosedate'))
            $this->choosedate = $this->_getParam('choosedate');

        if ($this->_getParam('objectId'))
            $this->objectId = $this->_getParam('objectId');

        $this->CLASS_OBJECT = AcademicDBAccess::findGradeFromId($this->academicId);

        if ($this->scheduleId)
            $this->facette = $this->DB_SCHEDULE->findScheduleFromGuId($this->scheduleId);
    }

    public function indexAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->gradeschoolyearId = $this->gradeschoolyearId;
        $this->view->sourceModul = $this->sourceModul;

        $this->view->URL_EVENT_SETTING = $this->UTILES->buildURL(
                "schedule/classeventsetting"
                , array(
            "schoolyearId" => $this->schoolyearId,
            "gradeschoolyearId" => $this->gradeschoolyearId
                )
        );

        $this->view->URL_DAY_EVENT_LIST = $this->UTILES->buildURL(
                "schedule/dayeventlist"
                , array(
            "schoolyearId" => $this->schoolyearId,
            "gradeschoolyearId" => $this->gradeschoolyearId
                )
        );
    }

    public function campusscheduleAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $this->view->academicId = $this->academicId;

        $this->view->URL_CAMPUS_SCHEDULE_DAY = $this->UTILES->buildURL(
                "schedule/campusscheduleday", array(
            "academicId" => $this->academicId)
        );

        $this->view->URL_CAMPUS_SCHEDULE_WEEK = $this->UTILES->buildURL(
                "schedule/campusscheduleweek", array(
            "academicId" => $this->academicId)
        );
    }

    public function campusscheduledayAction() {

        $this->view->academicId = $this->academicId;
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->_helper->viewRenderer("day/campus");
    }

    public function campusscheduleweekAction() {

        $this->view->academicId = $this->academicId;
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->_helper->viewRenderer("week/campus");
    }

    public function assignedtoroomAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
    }

    public function assignedtoteacherAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
    }

    public function trainingscheduleAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
    }

    public function trainingscheduledayAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $this->view->trainingId = $this->trainingId;
        $this->_helper->viewRenderer("day/training");
    }

    public function trainingscheduleweekAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->view->trainingId = $this->trainingId;
        $this->_helper->viewRenderer("week/training");
    }

    public function listteachingsessionAction() {
        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
        $this->_helper->viewRenderer("teachingsession/listteachingsession");
    }

    public function classeventsettingAction() {

        $this->view->academicId = $this->academicId;
        $this->view->teacherId = $this->teacherId;
        $this->view->target = $this->target;
        $this->view->classId = $this->classId;

        if ($this->academicId) {
            $classObject = AcademicDBAccess::findGradeFromId($this->academicId);
            $this->view->classObject = $classObject;
            $schoolyearObject = AcademicDateDBAccess::getInstance();
            $this->view->isCurrentYear = $schoolyearObject->isCurrentSchoolyear($classObject->SCHOOL_YEAR);
            Zend_Registry::set('GRADE_ID', $classObject->GRADE_ID);
            Zend_Registry::set('SCHOOLYEAR_ID', $classObject->SCHOOL_YEAR);
        }

        if ($this->trainingId) {
            $this->view->trainingId = $this->trainingId;
            $this->view->trainingObject = $this->DB_TRAINING->findTrainingFromId($this->trainingId);
        }

        $this->view->URL_SHOW_WEEK_CLASS_EVENTS = $this->UTILES->buildURL(
                "schedule/weekclassevents", array(
            "academicId" => $this->academicId
            , "target" => $this->target
            , "teacherId" => $this->teacherId
            , "classId" => $this->classId
            , "trainingId" => $this->trainingId
                )
        );

        $this->view->URL_IMPORT_FROM_EXCEL = $this->UTILES->buildURL("schedule/import", array("academicId" => $this->academicId));
    }

    public function teachingsessionchartAction() {
        $this->_helper->viewRenderer("teachingsession/chart");
    }

    public function weekclasseventsAction() {

        $this->view->academicId = $this->academicId;
        $this->view->teacherId = $this->teacherId;
        $this->view->term = $this->term;
        $this->view->classId = $this->classId; //@veansa
        $this->view->target = $this->target;
        $this->view->trainingId = $this->trainingId;

        switch (strtoupper($this->target)) {
            case "GENERAL":
                $classObject = AcademicDBAccess::findGradeFromId($this->academicId);
                $schoolyearObject = AcademicDateDBAccess::getInstance();
                $this->view->isCurrentYear = $schoolyearObject->isCurrentSchoolyear($classObject->SCHOOL_YEAR);
                $this->view->termNumber = $classObject->TERM_NUMBER;
                $this->view->MO = $classObject->MO;
                $this->view->TU = $classObject->TU;
                $this->view->WE = $classObject->WE;
                $this->view->TH = $classObject->TH;
                $this->view->FR = $classObject->FR;
                $this->view->SA = $classObject->SA;
                $this->view->SU = $classObject->SU;
                break;
            case "TRAINING":
                $TRAINING_OBJECT = TrainingDBAccess::findTrainingFromId($this->trainingId);
                $this->view->MO = $TRAINING_OBJECT->MO;
                $this->view->TU = $TRAINING_OBJECT->TU;
                $this->view->WE = $TRAINING_OBJECT->WE;
                $this->view->TH = $TRAINING_OBJECT->TH;
                $this->view->FR = $TRAINING_OBJECT->FR;
                $this->view->SA = $TRAINING_OBJECT->SA;
                $this->view->SU = $TRAINING_OBJECT->SU;
                break;
        }

        $this->view->URL_SHOW_DAY_CLASS_EVENT = $this->UTILES->buildURL(
                "schedule/showdayclassevent"
                , array(
            "academicId" => $this->academicId
                )
        );

        $this->_helper->viewRenderer("week/classevent");
    }

    public function showdayclasseventAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $this->view->academicId = $this->academicId;
        $this->view->target = $this->target;
        $this->view->trainingId = $this->trainingId;
        $this->view->teacherId = $this->teacherId;

        $classObject = AcademicDBAccess::findGradeFromId($this->academicId);
        $this->view->checkActivityInClass = $this->DB_SCHEDULE->checkActivityInClass($this->academicId);

        $schoolyearObject = AcademicDateDBAccess::getInstance();
        $this->view->isCurrentYear = $schoolyearObject->isCurrentSchoolyear($classObject->SCHOOL_YEAR);

        $this->view->termNumber = $classObject->TERM_NUMBER;
        $this->view->MO = $classObject->MO;
        $this->view->TU = $classObject->TU;
        $this->view->WE = $classObject->WE;
        $this->view->TH = $classObject->TH;
        $this->view->FR = $classObject->FR;
        $this->view->SA = $classObject->SA;
        $this->view->SU = $classObject->SU;

        Zend_Registry::set('GRADE_ID', $classObject->GRADE_ID);
        Zend_Registry::set('SCHOOLYEAR_ID', $classObject->SCHOOL_YEAR);
        Zend_Registry::set('TRAINING_ID', $this->trainingId);

        $this->view->URL_SHOW_CLASS_EVENT = $this->UTILES->buildURL("schedule/showclassevent", array("academicId" => $this->academicId));

        $this->_helper->viewRenderer("day/classevent");
    }

    public function teachingsessionAction() {

        $this->view->scheduleId = $this->scheduleId;
        $this->view->target = $this->target;

        $scheduleObject = $this->DB_SCHEDULE->findScheduleFromGuId($this->scheduleId);
        $teachingsessionObject = TeachingSessionDBAccess::getTeachingSessionFromId($this->scheduleId);

        if ($scheduleObject) {
            $this->view->facette = $scheduleObject;
        } elseif ($teachingsessionObject) {
            $this->view->facette = $teachingsessionObject;
        }

        switch (strtoupper($this->_getParam('type'))) {
            case "SUBSTITUTE":
                $this->_helper->viewRenderer("teachingsession/substitute");
                break;
            case "DAYOFFSCHOOL":
                $this->_helper->viewRenderer("teachingsession/dayoffschool");
                break;
        }
    }

    public function extrateachingsessionAction() {

        $this->view->scheduleId = $this->scheduleId;
        $this->view->target = $this->target;
        $this->view->academicId = $this->academicId;
        $this->view->trainingId = $this->trainingId;

        $this->view->facette = TeachingSessionDBAccess::getTeachingSessionFromId($this->scheduleId);
        $this->view->academicObject = AcademicDBAccess::findGradeFromId($this->academicId);
        $this->_helper->viewRenderer("teachingsession/extrateachingsession");
    }

    public function importAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $this->view->term = $this->term;
        $this->view->academicId = $this->academicId;
    }

    public function exportweekscheduleAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
    }

    public function templatexlsAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");
    }

    public function dayeventlistAction() {

        $this->view->academicId = $this->academicId;
        $this->view->teacherId = $this->teacherId;
        $this->view->target = $this->target;
        $this->view->classId = $this->classId; //@veansa

        $this->view->trainingId = $this->trainingId;

        if ($this->academicId) {
            $classObject = AcademicDBAccess::findGradeFromId($this->academicId);
            $schoolyearObject = AcademicDateDBAccess::getInstance();
            $schoolyearData = $schoolyearObject->getAcademicDatetDataFromId($classObject->SCHOOL_YEAR);
            $this->view->schoolyearDate = isset($schoolyearData["SCHOOLYEAR_DATE"]) ? $schoolyearData["SCHOOLYEAR_DATE"] : "---";
            $this->view->isCurrentYear = $schoolyearObject->isCurrentSchoolyear($classObject->SCHOOL_YEAR);
            $this->view->URL_SHOW_SCHOOLYEAR = $this->UTILES->buildURL("academicdate/showitem", array("objectId" => $classObject->SCHOOL_YEAR, "onlyShow" => true));
        }

        $this->view->URL_SHOW_SUBSTITUTE = $this->UTILES->buildURL("schedule/showsubstitute", array());

        $this->_helper->viewRenderer("day/list");
    }

    //@veasna

    public function daycrediteventlistAction() {

        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;
        //$this->view->target = $this->target;

        $this->_helper->viewRenderer("day/creditlist");
    }

    public function creditstudenteventsettingAction() {

        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;
        //$this->view->target = $this->target;
    }

    public function weekcrediteventAction() {

        $this->view->studentId = $this->studentId;
        $this->view->schoolyearId = $this->schoolyearId;
        $this->view->term = $this->term;
        //$this->view->target = $this->target;

        $this->_helper->viewRenderer("week/creditevent");
    }

    public function byclassAction() {

        $this->view->academicId = $this->academicId;
        $this->view->target = $this->target;
        $this->view->trainingId = $this->trainingId;
        $this->view->teacherId = $this->teacherId;
        $this->view->classId = $this->classId; //@veansa

        $academicObject = AcademicDBAccess::findGradeFromId($this->academicId);
        $this->view->academicObjedct = $academicObject;

        $dbSchoolyear = AcademicDateDBAccess::getInstance();

        if ($academicObject) {
            $schoolyearObject = $dbSchoolyear->findAcademicDateFromId($academicObject->SCHOOL_YEAR);
            if ($schoolyearObject) {
                $this->view->className = $academicObject->NAME . " &raquo; " . $schoolyearObject->NAME;
                $this->view->isCurrentYear = $dbSchoolyear->isCurrentSchoolyear($academicObject->SCHOOL_YEAR);
            }
        }

        $this->view->URL_DAY_SCHEDULE = $this->UTILES->buildURL(
                "schedule/dayeventlist"
                , array(
            "academicId" => $this->academicId
            , "teacherId" => $this->teacherId
            , "target" => $this->target
            , "trainingId" => $this->trainingId)
        );

        $this->view->URL_WEEK_SCHEDULE = $this->UTILES->buildURL(
                "schedule/classeventsetting"
                , array(
            "academicId" => $this->academicId
            , "teacherId" => $this->teacherId
            , "target" => $this->target
            , "classId" => $this->classId
            , "trainingId" => $this->trainingId)
        );

        $this->view->URL_EXTRA_TEACHING_SESSION_LIST = $this->UTILES->buildURL(
                "schedule/listextrateachingsession"
                , array(
            "academicId" => $this->academicId
            , "teacherId" => $this->teacherId
            , "target" => $this->target
            , "trainingId" => $this->trainingId)
        );
    }

    public function listextrateachingsessionAction() {
        $this->view->academicId = $this->academicId;
        $this->view->trainingId = $this->trainingId;
        $this->view->target = $this->target;
        $this->view->classId = $this->classId;
        $this->_helper->viewRenderer("teachingsession/listextrateachingsession");
    }

    public function groupscheduleAction() {
        $this->view->scheduleId = $this->scheduleId;
    }

    // Aktuell.....
    public function showclasseventAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $this->view->remove_status = true;

        if ($this->scheduleId == "new") {
            $this->view->scheduleId = generateGuid();
            $this->view->term = $this->term;
            $this->view->roomId = $this->roomId;
            $this->view->subjectId = $this->subjectId;
            $this->view->teacherId = $this->teacherId;
            $this->view->shortday = $this->shortday;
            $this->view->scheduleAction = "INSERT";
            $this->view->status = false;
        } else {
            if ($this->facette) {
                $this->view->minValue = "06:00";
                $this->view->scheduleId = $this->facette->GUID;
                $this->view->term = $this->facette->TERM;
                $this->view->roomId = $this->facette->ROOM_ID;
                $this->view->subjectId = $this->facette->SUBJECT_ID;
                $this->view->teacherId = $this->facette->TEACHER_ID;
                $this->view->shortday = $this->facette->SHORTDAY;

                $this->view->scheduleAction = "UPDATE";

                if ($this->facette->STATUS) {
                    $this->view->remove_status = false;
                    $this->view->status = true;
                } else {
                    $this->view->remove_status = true;
                    $this->view->status = true;
                }
            }
        }

        $this->view->facette = $this->facette;
        $this->view->scheduleData = $this->SCHEDULE_DATA;
        $this->view->academicId = $this->academicId;

        $this->view->academicObject = AcademicDBAccess::findGradeFromId($this->academicId);

        if (isset($this->CLASS_OBJECT->GRADE_ID))
            $this->view->gradeId = $this->CLASS_OBJECT->GRADE_ID;

        if (isset($this->CLASS_OBJECT->SCHOOL_YEAR))
            $this->view->schoolyearId = $this->CLASS_OBJECT->SCHOOL_YEAR;

        $this->view->target = $this->target;
        $this->view->trainingId = $this->trainingId;
    }

    public function jsonloadAction() {

        switch ($this->REQUEST->getPost('cmd')) {

            case "loadClassEvent":
                $jsondata = $this->DB_SCHEDULE->loadClassEvent($this->REQUEST->getPost('scheduleId'));
                break;

            case "loadClassEvents":
                $jsondata = $this->DB_SCHEDULE->loadClassEvents($this->REQUEST->getPost());
                break;
                
            case "dataScheduleDayTrainingSetting"://@veasna 
                $jsondata = $this->DB_SCHEDULE_DAY_SETTING->dataScheduleDayTrainingSetting($this->REQUEST->getPost());
                break;

            case "dayEventList":
                $jsondata = $this->DB_DAY_SCHEDULE->dayEventList($this->REQUEST->getPost());
                break;
            //@veasna    
            case "loadCreditStudentSubjectEvents":
                $jsondata = $this->DB_SCHEDULE->loadCreditStudentSubjectEvents($this->REQUEST->getPost());
                break;

            case "availableTeacher":
                $jsondata = $this->DB_SCHEDULE->availableTeacher($this->REQUEST->getPost());
                break;

            case "availableRoom":
                $jsondata = $this->DB_SCHEDULE->availableRoom($this->REQUEST->getPost());
                break;

            ///@veasna
            case "availableGridRoom":
                $jsondata = $this->DB_SCHEDULE->availableGridRoom($this->REQUEST->getPost());
                break;

            case "jsonListTeacherCredit":
                $jsondata = $this->DB_SCHEDULE->jsonListTeacherCredit($this->REQUEST->getPost());
                break;
            //

            case "campusTimeList":
                $jsondata = $this->DB_CAMPUS_SCHEDULE->campusTimeList($this->REQUEST->getPost());
                break;

            case "campusTeacherList":
                $jsondata = $this->DB_CAMPUS_SCHEDULE->campusTeacherList($this->REQUEST->getPost());
                break;

            case "campusrRoomList":
                $jsondata = $this->DB_CAMPUS_SCHEDULE->campusrRoomList($this->REQUEST->getPost());
                break;

            case "campusEventList":
                $jsondata = $this->DB_CAMPUS_SCHEDULE->campusEventList($this->REQUEST->getPost());
                break;

            case "jsonTeacherSchedule":
                $jsondata = $this->DB_CAMPUS_SCHEDULE->jsonTeacherSchedule($this->REQUEST->getPost());
                break;

            case "jsonLoadTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonLoadTeachingSession($this->REQUEST->getPost('scheduleId'));
                break;

            case "checkAssignedTeacherInSchedule":
                $jsondata = $this->DB_SCHEDULE->checkAssignedTeacherInSchedule($this->REQUEST->getPost());
                break;

            case "checkAssignedRoomInSchedule":
                $jsondata = $this->DB_SCHEDULE->checkAssignedRoomInSchedule($this->REQUEST->getPost());
                break;

            case "searchTeachingSession":
                $jsondata = $this->DB_TEACHER_SCHEDULE->searchTeachingSession($this->REQUEST->getPost());
                break;

            case "jsonListExtraTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonListExtraTeachingSession($this->REQUEST->getPost());
                break;

            //@veasna
            case "jsonListCreditStudentExtraTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonListCreditStudentExtraTeachingSession($this->REQUEST->getPost());
                break;

            //

            case "jsonListTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonListTeachingSession($this->REQUEST->getPost());
                break;

            case "linkedScheduleAcademic":
                $jsondata = $this->DB_SCHEDULE->linkedScheduleAcademic($this->REQUEST->getPost('scheduleId'), $this->REQUEST->getPost('type'));
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonsaveAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        switch ($this->REQUEST->getPost('cmd')) {

            case "saveClassEvent":
                $jsondata = $this->DB_SCHEDULE->saveClassEvent($this->REQUEST->getPost());
                break;

            case "jsonDeleteDayClassEvent":
                $jsondata = $this->DB_SCHEDULE->jsonDeleteDayClassEvent($this->REQUEST->getPost('scheduleId'));
                break;

            case "releaseClassEvent":
                $jsondata = $this->DB_SCHEDULE->releaseClassEvent($this->REQUEST->getPost());
                break;

            case "copyScheduleFromPreviousTerm":
                $jsondata = $this->DB_COPY_SCHEDULE->copyScheduleFromPreviousTerm(
                        $this->REQUEST->getPost('academicId')
                        , $this->REQUEST->getPost('fromterm')
                        , $this->REQUEST->getPost('toterm'));
                break;

            case "deleteOldSubject":
                $jsondata = $this->DB_SCHEDULE->deleteOldSubject($this->REQUEST->getPost());
                break;

            case "jsonCopyDayClassEvent":
                $jsondata = $this->DB_COPY_SCHEDULE->jsonCopyDayClassEvent($this->REQUEST->getPost());
                break;

            case "jsonDeleteAllClassEventByDay":
                $jsondata = $this->DB_SCHEDULE->jsonDeleteAllClassEventByDay($this->REQUEST->getPost());
                break;

            case "jsonActionTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonActionTeachingSession($this->REQUEST->getPost());
                break;

            case "jsonDeleteTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonDeleteTeachingSession($this->REQUEST->getPost('scheduleId'));
                break;

            case "jsonActionExtraTeachingSession":
                $this->DB_TEACHING_SESSION = TeachingSessionDBAccess::getInstance();
                $jsondata = $this->DB_TEACHING_SESSION->jsonActionExtraTeachingSession($this->REQUEST->getPost());
                break;

            case "jsonActionLinkSchedule2Academic":
                $jsondata = $this->DB_SCHEDULE->jsonActionLinkSchedule2Academic($this->REQUEST->getPost());
                break;

            case "actionSharingSchedule2Academic":
                $jsondata = ScheduleDBAccess::actionSharingSchedule2Academic($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsontreeAction() {

        switch ($this->REQUEST->getPost('cmd')) {
            case "jsonTreeSharedSchedule2Academic":
                $jsondata = ScheduleDBAccess::jsonTreeSharedSchedule2Academic($this->REQUEST->getPost());
                break;
        }

        if (isset($jsondata))
            $this->setJSON($jsondata);
    }

    public function jsonimportAction() {

        //UserAuth::actionPermintGroup($this->_request, "GENERAL_EDUCATION", "TRAINING_PROGRAMS");

        $jsondata = $this->DB_IMPORT_SCHEDULE->importXLS($this->REQUEST->getPost());
        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/html');
        $this->getResponse()->setBody($json);
    }

    public function setJSON($jsondata) {

        Zend_Loader::loadClass('Zend_Json');
        $json = Zend_Json::encode($jsondata);
        $this->getResponse()->setHeader('Content-Type', 'text/javascript');
        $this->getResponse()->setBody($json);
    }

}

?>