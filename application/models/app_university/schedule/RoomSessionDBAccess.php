<?php

///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 30.10.2013
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/schedule/ScheduleDBAccess.php';
require_once setUserLoacalization();

class RoomSessionDBAccess extends ScheduleDBAccess {

    public $dataforjson = null;
    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getRoomSession($roomId, $day) {
        
        $SELECT_DATA = array(
            "A.ID AS SCHEDULE_ID"
            , "A.GUID AS GUID"
            , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
            , "A.ROOM_ID AS ROOM_ID"
            , "A.ACADEMIC_ID AS ACADEMIC_ID"
            , "A.TRAINING_ID AS TRAINING_ID"
            , "A.SHORTDAY AS SHORTDAY"
            , "A.START_TIME AS START_TIME"
            , "A.END_TIME AS END_TIME"
            , "A.EVENT AS EVENT"
            , "A.TERM AS TERM"
            , "B.ID AS SUBJECT_ID"
            , "B.NAME AS SUBJECT_NAME"
            , "B.COLOR AS SUBJECT_COLOR"
            , "C.NAME AS ROOM"
            , "CONCAT(C.NAME,'<br>',C.BUILDING,'<br>',C.FLOOR) AS ROOM"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_teaching_session'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_room'), 'A.ROOM_ID=C.ID', array());
        $SQL->where("ROOM_ID='" . $roomId . "'");
        $SQL->where("TEACHING_DATE='" . $day . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }
    
    public static function getRoomEvent($roomId, $shortday, $term, $type) {
        
        $SELECT_DATA = array(
            "A.ID AS SCHEDULE_ID"
            , "A.GUID AS GUID"
            , "A.SCHEDULE_TYPE AS SCHEDULE_TYPE"
            , "A.STATUS AS SCHEDULE_STATUS"
            , "A.ROOM_ID AS ROOM_ID"
            , "A.ACADEMIC_ID AS ACADEMIC_ID"
            , "A.TRAINING_ID AS TRAINING_ID"
            , "A.SHORTDAY AS SHORTDAY"
            , "A.CODE AS SCHEDULE_CODE"
            , "A.START_TIME AS START_TIME"
            , "A.END_TIME AS END_TIME"
            , "A.EVENT AS EVENT"
            , "A.TERM AS TERM"
            , "B.ID AS SUBJECT_ID"
            , "B.NAME AS SUBJECT_NAME"
            , "B.COLOR AS SUBJECT_COLOR"
            , "D.NAME AS ROOM"
            , "CONCAT(D.NAME,'<br>',D.BUILDING,'<br>',D.FLOOR) AS ROOM"
        );
        
        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_schedule'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_subject'), 'A.SUBJECT_ID=B.ID', array());
        $SQL->joinLeft(array('C' => 't_staff'), 'A.TEACHER_ID=C.ID', array());
        $SQL->joinLeft(array('D' => 't_room'), 'A.ROOM_ID=D.ID', array());

        if ($shortday)
            $SQL->where("A.SHORTDAY = '" . $shortday . "'");
        if ($roomId)
            $SQL->where("A.ROOM_ID = '" . $roomId . "'");

        if ($type == "GENERAL") {
            $SQL->where("A.ACADEMIC_ID<>0");
            if ($term)
                $SQL->where("A.TERM = '" . $term . "'");
        }
        if ($type == "TRAINING") {
            $SQL->where("A.TRAINING_ID<>0");
        }
        $SQL->order('START_TIME');
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
     
     public static function sqlRoomSession($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";
        $term = TeacherScheduleDBAccess::getTermByDate($endDate);

        $SELECTION_A = array(
            "ID AS ROOM_ID"
            , "CODE AS ROOM_CODE"
            , "NAME AS ROOM_NAME"
            , "ROOM_SIZE"
            , "BUILDING"
            , "MAX_COUNT"
            , "CREATED_DATE"
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_room'), $SELECTION_A);
        $SQL->joinRight(array('B' => 't_schedule'), 'A.ID=B.ROOM_ID', array());
        $SQL->where("B.TERM = '" . $term . "'");

        if ($globalSearch) {
            $SEARCH = "";
            $SEARCH .= " ((A.NAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.ROOM_SIZE LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.BUILDING LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.MAX_COUNT LIKE '" . $globalSearch . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }
     
     public static function jsonListRoomSession($params, $isJson = true) {

        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "100";
        $startDate = isset($params["startDate"]) ? setDate2DB($params["startDate"]) : "";
        $endDate = isset($params["endDate"]) ? setDate2DB($params["endDate"]) : "";
        $academicType = isset($params["type"]) ? addText($params["type"]) : "GENERAL";
        
        $data = array();
        
        $result = self::sqlRoomSession($params);
        $countAllSession = 0;
        $countAllExtraSession = 0; 
        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $countSession = 0;
                $countExtraSession = 0;
                $days = getDatesBetween2Dates($startDate, $endDate);
                
                if($days){
                        
                    foreach ($days as $day) {
                        $schoolyearId = TeacherScheduleDBAccess::getSchoolyearIdByDate($day);
                        $term = TeacherScheduleDBAccess::getCurrentTermNew($day,$schoolyearId);
                        $shortday = getWEEKDAY($day);
                        
                        $EXTRA_TEACHING_OBJECT = self::getRoomSession($value->ROOM_ID, $day);
                        if($EXTRA_TEACHING_OBJECT){
                            foreach($EXTRA_TEACHING_OBJECT as $extraTeachingSession){
                                $countExtraSession++;
                                $countAllExtraSession++;    
                            }   
                        }
                        
                        $SESSION_OBJECT = self::getRoomEvent(
                                                $value->ROOM_ID
                                                , $shortday
                                                , $term
                                                , $academicType);
                        if($SESSION_OBJECT) {
                            foreach ($SESSION_OBJECT as $session) {
                                $countSession++;
                                $countAllSession++;   
                            }    
                        }                                     
                    }   
                    $total = $countSession + $countExtraSession;
                    
                    $data[$i]["ROOM_ID"] = $value->ROOM_ID;
                    $data[$i]["ROOM_CODE"] = $value->ROOM_CODE;
                    $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);
                    $data[$i]["ROOM_SIZE"] = $value->ROOM_SIZE;
                    $data[$i]["BUILDING"] = setShowText($value->BUILDING);
                    $data[$i]["MAX_COUNT"] = setShowText($value->MAX_COUNT);
                    $data[$i]["NUMBER_SESSION"] = displayNumberFormat($countSession);
                    $data[$i]["NUMBER_EXTRA_SESSION"] = displayNumberFormat($countExtraSession);
                    $data[$i]["TOTAL"] = displayNumberFormat($total);

                    $i++;
                }
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        
        if ($isJson) {
            return array(
                "success" => true
                ,"totalCount" => sizeof($data)
                ,"totalAllSession" => $countAllSession
                ,"totalAllExtraSession" => $countAllExtraSession 
                ,"rows" => $a
            );
        } else {
            return $data;
        }
    }
    
    public static function countRoomSession($roomId, $startDate,$endDate) {
        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_teaching_session'), array("C" => "COUNT(*)"));
        $SQL->where("ROOM_ID='" . $roomId . "'");
        $SQL->where("TEACHING_DATE BETWEEN  '".setDate2DB($startDate)."'  AND '".setDate2DB($endDate)."'");         
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;    
    }
    
    public function jsonShowRoomSession($params, $noJson = false) {
        
        $start = isset($params["start"]) ? $params["start"] : "0";
        $limit = isset($params["limit"]) ? $params["limit"] : "100";
        $startdt = isset($params["startdt"]) ? substr($params["startdt"], 0, 10) : "";
        $enddt = isset($params["enddt"]) ? substr($params["enddt"], 0, 10) : "";
        $roomId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";
        $academicType = isset($params["type"]) ? addText($params["type"]) : "GENERAL";
        $schoolyearId = isset($params["schoolyearId"])? addText($params["schoolyearId"]) :'';
        
        $days = getDatesBetween2Dates($startdt, $enddt);

        $data = array();

        if ($days) {
            $i = self::countRoomSession($roomId,$startdt,$enddt);
            $j = 0;
            
            foreach ($days as $day) {
                if(!$schoolyearId)$schoolyearId = teacherScheduleDBAccess::getSchoolyearIdByDate($day);
                 
                $term = teacherScheduleDBAccess::getCurrentTermNew($day,$schoolyearId);
                $shortday = getWEEKDAY($day);
                ////////////////////////////////////////////////////////////////////
                //EXTRA TEACHING SESSION
                ///////////////////////////////////////////////////////////////////
                $EXTRA_ROOM_OBJECT = self::getRoomSession($roomId, $day);
                
                if($EXTRA_ROOM_OBJECT){
                    foreach($EXTRA_ROOM_OBJECT as $extraTeachingSession){
                        $data[$j]["ID"] = $extraTeachingSession->GUID;
                        $data[$j]["ROOM"] = setShowText($extraTeachingSession->ROOM);
                        $data[$j]["SESSION_DATE"] = getShowDate($day);
                        $data[$j]["COLOR"] = $extraTeachingSession->SUBJECT_COLOR;
                        $data[$j]["COLOR_FONT"] = getFontColor($extraTeachingSession->SUBJECT_COLOR);
                        
                        $subjectObject = SubjectDBAccess::findSubjectFromId($extraTeachingSession->SUBJECT_ID);
                        
                        if($subjectObject)
                            $data[$j]["EVENT"] = setShowText($subjectObject->NAME);        
                        
                        $academicObject = AcademicDBAccess::findGradeFromId($extraTeachingSession->ACADEMIC_ID);

                        if ($academicObject) {
                            $data[$j]["CLASS"] = $academicObject->NAME;
                            $data[$j]["CLASS"] .= "<br>";
                            $data[$j]["CLASS"] .= secondToHour($extraTeachingSession->START_TIME) . " - " . secondToHour($extraTeachingSession->END_TIME);
                            $data[$j]["TARGET"] = "GENERAL";
                            $data[$j]["CLASS_NAME"] = $academicObject->NAME;
                            $data[$j]["TEACHING_STATUS"] = EXTRA_TEACHING_SESSION;
                        }
                        $j++;   
                    }   
                }
                
                ////////////////////////////////////////////////////////////////////////////
                //TEACHING SESSION
                //////////////////////////////////////////////////////////////////////////// 
                $facette = self::getRoomEvent($roomId, $shortday, $term, $academicType);
                if ($facette) {
                    foreach ($facette as $facette) {
                        $data[$i]["ID"] = $facette->GUID;
                        $data[$i]["CHOOSE_DATE"] = $day;

                        if ($facette->ACADEMIC_ID || $facette->TRAINING_ID) {

                            $data[$i]["ACADEMIC_ID"] = $facette->ACADEMIC_ID;
                            $data[$i]["TEACHER_ID"] = $roomId;
                            $data[$i]["SUBJECT_ID"] = $facette->SUBJECT_ID;
                            $data[$i]["SHORTDAY"] = $facette->SHORTDAY;

                            $data[$i]["SESSION_STATUS"] = "Absence, Extra Session...";
                            $data[$i]["SESSION_DATE"] = getShowDate($day);
                            $data[$i]["CODE"] = $facette->SCHEDULE_CODE;
                            $data[$i]["DAY"] = getShortdayName($facette->SHORTDAY);
                            $data[$i]["DAY"] .= "<br>";
                            $data[$i]["DAY"] .= getShowDate($day);
                            $data[$i]["SCHEDULE_TYPE"] = $facette->SCHEDULE_TYPE;

                            switch ($facette->SCHEDULE_TYPE) {
                                case 1:
                                    $data[$i]["EVENT"] = setShowText($facette->SUBJECT_NAME);
                                    $data[$i]["COLOR"] = $facette->SUBJECT_COLOR;
                                    $data[$i]["COLOR_FONT"] = getFontColor($facette->SUBJECT_COLOR);
                                    $data[$i]["EVENT_NAME"] = setShowText($facette->SUBJECT_NAME);
                                    break;
                                case 2:
                                    $data[$i]["EVENT"] = setShowText($facette->EVENT);
                                    $data[$i]["COLOR"] = "#CCCCCC";
                                    $data[$i]["COLOR_FONT"] = getFontColor($facette->SUBJECT_COLOR);
                                    $data[$i]["EVENT_NAME"] = setShowText($facette->EVENT);
                                    break;
                            }

                            $START_TIME = secondToHour($facette->START_TIME);
                            $END_TIME = secondToHour($facette->END_TIME);
                            $data[$i]["TIME"] = $START_TIME . " - " . $END_TIME;

                            $data[$i]["START_TIME"] = $START_TIME;
                            $data[$i]["END_TIME"] = $END_TIME;
                            $data[$i]["ROOM"] = setShowText($facette->ROOM);

                            if ($facette->ACADEMIC_ID) {

                                $academicObject = AcademicDBAccess::findGradeFromId($facette->ACADEMIC_ID);
                                $data[$i]["TERM"] = displaySchoolTerm($facette->TERM);

                                if ($academicObject) {
                                    $data[$i]["CLASS"] = $academicObject->NAME;
                                    $data[$i]["CLASS"] .= "<br>";
                                    $data[$i]["CLASS"] .= secondToHour($facette->START_TIME) . " - " . secondToHour($facette->END_TIME);
                                    $data[$i]["TARGET"] = "GENERAL";
                                    $data[$i]["CLASS_NAME"] = $academicObject->NAME;
                                }
                            } elseif ($facette->TRAINING_ID) {

                                $data[$i]["TARGET"] = "TRAINING";
                                $TRAINING_OBEJECT = TrainingDBAccess::findTrainingFromId($facette->TRAINING_ID);

                                if ($TRAINING_OBEJECT) {
                                    $data[$i]["CLASS"] = $TRAINING_OBEJECT->NAME . " (" . TRAINING_PROGRAMS . ")";
                                    $data[$i]["CLASS"] .= "<br>";
                                    $data[$i]["CLASS"] .= secondToHour($facette->START_TIME) . " - " . secondToHour($facette->END_TIME);
                                    $data[$i]["CLASS_NAME"] = $TRAINING_OBEJECT->NAME . " (" . TRAINING_PROGRAMS . ")";
                                }
                            }

                            $i++;
                        }
                    }
                }
            }
        }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }
        
        if ($noJson) {
            return $a;
        } else {
            return array(
                "success" => true
                , "rows" => $a
            );
        }
    }
}

?>