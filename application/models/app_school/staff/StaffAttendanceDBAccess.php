<?php

///////////////////////////////////////////////////////////
// Vikensoft UG Partent Nr.....
// @Morng Thou
// 27.02.2011
// 03 Rue des Piblues Bailly Romainvilliers
// @VIK Modify....
// 24.05.2011
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'models/app_school/staff/StaffDBAccess.php';
require_once 'models/app_school/AcademicDateDBAccess.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once 'models/app_school/training/TrainingDBAccess.php';
require_once 'models/app_school/SchooleventDBAccess.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class StaffAttendanceDBAccess extends StaffDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function jsonLoadStaffAttendance($Id) {

        $facette = self::findAttendanceFromId($Id);

        $data = array();

        if ($facette) {

            $data["ID"] = $facette->ID;
            $data["STAFF_ID"] = $facette->STAFF_ID;
            $data["STATUS"] = $facette->STATUS;
            $data["ABSENT_TYPE"] = $facette->ABSENT_TYPE;
            $data["ACTION_TYPE"] = $facette->ACTION_TYPE;

            switch ($facette->ACTION_TYPE) {
                case 1:
                    $data["SINGLE_DATE"] = getShowDate($facette->START_DATE);
                    break;
                case 2:
                    $data["START_DATE"] = getShowDate($facette->START_DATE);
                    $data["END_DATE"] = getShowDate($facette->END_DATE);
                    break;
            }

            $data["COUNT_DATE"] = $facette->COUNT_DATE;
            $data["COMMENT"] = setShowText($facette->COMMENT);

            if ($facette->CAL_DATE) {
                $caldate = explode(",", $facette->CAL_DATE);

                if ($caldate) {
                    foreach ($caldate as $value) {
                        $data["COUNT_DATE"] .= "<br>" . getShowDate($value);
                    }
                }
            }
            if ($facette->START_TIME && $facette->END_TIME) {
                //error_log(secondToHour($facette->START_TIME));
                $data["START_TIME"] = secondToHour($facette->START_TIME);
                $data["END_TIME"] = secondToHour($facette->END_TIME);
            }

            $data["CREATED_DATE"] = getShowDateTime($facette->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($facette->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($facette->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($facette->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($facette->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($facette->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($facette->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($facette->DISABLED_BY);
        }

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public static function findStaffCurrentAbsence($staffId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array('*'));
        $SQL->where("STAFF_ID = '" . $staffId . "'");
        $SQL->where("'" . getCurrentDBDate() . "' BETWEEN START_DATE AND END_DATE");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //////////////////////////////////////////////////////////////////////
    //@Sea Peng 01.08.2013
    /////////////////////////////////////////////////////////////////////
    public static function findAttendanceFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array('*'));
        $SQL->where("ID = ?",$Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function findStaffAbsence($staffId, $startDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array('*'));
        $SQL->where("STAFF_ID = '" . $staffId . "'");
        $SQL->where("START_DATE='" . $startDate . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public function jsonDeleteStaffAttendance($Id) {

        if ($Id) {
            self::dbAccess()->delete('t_staff_attendance', array('ID = ? ' => $Id));
            self::dbAccess()->delete('t_teacher_attendance_subject', array('ATTENDANCE_ID = ? ' => $Id));
        }

        return array("success" => true);
    }

    public static function jsonStaffDailyAttendance($params) {

        require_once 'models/app_school/schedule/TeacherScheduleDBAccess.php';
        $DB_ACCESS = TeacherScheduleDBAccess::getInstance();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $absentDate = isset($params["choosedate"]) ? setDate2DB($params["choosedate"]) : "";

        $ABSENT_TYPES = AbsentTypeDBAccess::allAbsentType('STAFF', 1);

        $data = array();

        $result = self::sqlStaffAttendance($params);

        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $isDisabled = '';
                $staffIndex = $value->STAFF_INDEX;
                $staffId = $value->STAFF_ID;

                $data[$i]["ID"] = $value->STAFF_ID;
                $data[$i]["CODE"] = $value->STAFF_CODE;
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                ////////////////////////////////////////////////////////////////////
                //Status of staff...
                ////////////////////////////////////////////////////////////////////
                $STATUS_DATA = StaffStatusDBAccess::getCurrentStaffStatus($value->STAFF_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                if ($ABSENT_TYPES) {
                    foreach ($ABSENT_TYPES as $absentType) {

                        $CHECK_STATUS_BLOCK = false;
                        $CHECK_STATUS_DAILY = false;

                        $newIndex = $staffIndex . "_" . $absentType->ID;
                        $checkday = TeacherScheduleDBAccess::checkDay($absentDate);
                        if (!$checkday) {
                            $userObject = UserMemberDBAccess::findUserFromId($staffId);
                            if ($userObject) {
                                switch ($userObject->ROLE) {
                                    case 2:
                                    case 3:
                                        $teacherId = $value->STAFF_ID;
                                        
                                        $CHECK_SESSION = $DB_ACCESS->checkTeacherSession($teacherId, $absentDate);
                                        if (!$CHECK_SESSION) {
                                            $isDisabled = 'disabled';
                                        }
                                        break;

                                    default:
                                        $isDisabled = '';
                                        break;
                                }
                            }
                        } else {
                            $isDisabled = 'disabled';
                        }

                        $data[$i]["" . $absentType->ID . "_COLOR"] = $absentType->COLOR;
                        $data[$i]["" . $absentType->ID . "_COLOR_FONT"] = getFontColor($absentType->COLOR);
                        $data[$i]["TYPE_" . $absentType->ID . ""] = "";
                        $data[$i]["TYPE_" . $absentType->ID . ""].= "<div style='padding :6px;'>";

                        /**
                         * Check attendance by action block...
                         */
                        $CHECK_BLOCK = self::checkExistStaffBlockAttendance($staffId, $absentType->ID, $absentDate);

                        if ($CHECK_BLOCK) {
                            $CHECK_STATUS_BLOCK = $CHECK_BLOCK ? "checked" : "";
                        } else {
                            /**
                             * Check attendance by action daily...
                             */
                            $CHECK_DAILY = self::checkExistStaffDailyAttendance($staffId, $absentType->ID, $absentDate);
                            $CHECK_STATUS_DAILY = $CHECK_DAILY ? "checked" : "";
                        }


                        if ($CHECK_STATUS_BLOCK) {
                            $CHECKED = "checked";
                        } else if ($CHECK_STATUS_DAILY) {
                            $CHECKED = "checked";
                        } else {
                            $CHECKED = "";
                        }

                        $data[$i]["TYPE_" . $absentType->ID . ""] .= "<input onclick='functionActionDaily" . $newIndex . "()' " . $isDisabled . " " . $CHECKED . "  type='checkbox' value='1' id='TYPE_" . $newIndex . "'>";
                        $data[$i]["TYPE_" . $absentType->ID . ""] .= "</div>";
                    }
                }
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

    public static function getQueryStaffAttendance() {

        $params = array();

        return self::sqlStaffAttendance($params);
    }

    public function jsonActionStaffDailyAttendance($params) {

        $staffId = isset($params["staffId"]) ? addText($params["staffId"]) : "";
        $absentDate = isset($params["choosedate"]) ? setDate2DB($params["choosedate"]) : "";
        $newValue = isset($params["newValue"]) ? addText($params["newValue"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";

        $SUCCESS_DATA = array();
        $SUCCESS_DATA['msg'] = '';

        $CHECK_BLOCK = self::checkExistStaffBlockAttendance($staffId, false, $absentDate);
        if (!$CHECK_BLOCK) {
            if ($newValue) {
                $CHECK_EXIST = self::checkExistStaffDailyAttendance($staffId, false, $absentDate);
                if ($CHECK_EXIST) {
                    $SUCCESS_DATA["error"] = true;
                    $SUCCESS_DATA['msg'] = ABSENCE_ALREADY_SET_THIS_DAY;
                    $SUCCESS_DATA["checked"] = $newValue ? false : true;
                } else {
                    $SUCCESS_DATA["error"] = false;
                    $SUCCESS_DATA["checked"] = $newValue ? true : false;
                }
            } else {
                $result = self::findStaffAbsence($staffId, $absentDate);
                if ($result)
                    self::jsonDeleteStaffAttendance($result->ID);
                $SUCCESS_DATA['msg'] = MSG_ACTION_REMOVED_ITEM;
            }
        }else {
            $SUCCESS_DATA["error"] = true;
            $SUCCESS_DATA['msg'] = ABSENCE_ALREADY_SET_THIS_DAY;
            $SUCCESS_DATA["checked"] = $newValue ? false : true;
        }

        $SUCCESS_DATA["field"] = $field;
        $SUCCESS_DATA["success"] = true;

        return $SUCCESS_DATA;
    }

    public static function addStaffDailyAttendance($staffId, $absentType, $absentDate) {

        $SAVEDATA["STAFF_ID"] = $staffId;
        $SAVEDATA["ACTION_TYPE"] = 1;
        $SAVEDATA["ABSENT_TYPE"] = $absentType;
        $SAVEDATA["START_DATE"] = $absentDate;
        $SAVEDATA["END_DATE"] = $absentDate;
        $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
        $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
        self::dbAccess()->insert('t_staff_attendance', $SAVEDATA);
        return self::dbAccess()->lastInsertId();
    }

    public static function checkExistStaffBlockAttendance($staffId, $absentType, $absentDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array("C" => "COUNT(*)"));
        $SQL->where("STAFF_ID = '" . $staffId . "'");
        if ($absentType)
            $SQL->where("ABSENT_TYPE = '" . $absentType . "'");
        $SQL->where("START_DATE <= '" . $absentDate . "' and END_DATE >= '" . $absentDate . "'");
        $SQL->where("ACTION_TYPE='2'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function checkExistStaffDailyAttendance($staffId, $absentType, $absentDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array("C" => "COUNT(*)"));
        $SQL->where("STAFF_ID = '" . $staffId . "'");
        if ($absentType)
            $SQL->where("ABSENT_TYPE = '" . $absentType . "'");
        $SQL->where("START_DATE <= '" . $absentDate . "' and END_DATE >= '" . $absentDate . "'");
        $SQL->where("ACTION_TYPE='1'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function findExistStaffDailyAttendance($staffId, $absentType, $absentDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_attendance", array('*'));
        $SQL->where("STAFF_ID = '" . $staffId . "'");
        if ($absentType)
            $SQL->where("ABSENT_TYPE = '" . $absentType . "'");
        $SQL->where("START_DATE <= '" . $absentDate . "' and END_DATE >= '" . $absentDate . "'");
        $SQL->where("ACTION_TYPE='1'");
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function jsonActonStaffAttendance($params) {

        $SAVEDATA = array();

        $errors = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $absentType = isset($params["absentType"]) ? addText($params["absentType"]) : "";
        $choosedate = isset($params["choosedate"]) ? setDate2DB($params["choosedate"]) : "";

        $facette = self::findAttendanceFromId($objectId);

        if (!$facette) {
            $staffId = isset($params["staffId"]) ? addText($params["staffId"]) : "";
            $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : "";
        } else {
            $staffId = $facette->STAFF_ID;
            $actionType = $facette->ACTION_TYPE;
        }

        $CALCULATED_COUNT_DATE_DATA = self::calCountByStartDateANDEndDate($startDate, $endDate);

        $CHECK_COUNT_EXISTING_DATE = self::checkExistingStaffAttendanceDate($staffId, $startDate, $endDate);

        if ($facette) {
            if ($CHECK_COUNT_EXISTING_DATE == 1) {
                $CHECK_ERROR_EXISTING_DATE = 0;
            } elseif ($CHECK_COUNT_EXISTING_DATE == 0) {
                $CHECK_ERROR_EXISTING_DATE = 0;
            } else {
                $CHECK_ERROR_EXISTING_DATE = 1;
            }
        } else {
            if ($CHECK_COUNT_EXISTING_DATE) {
                $CHECK_ERROR_EXISTING_DATE = 1;
            } else {
                $CHECK_ERROR_EXISTING_DATE = 0;
            }
        }

        if ($CHECK_ERROR_EXISTING_DATE) {
            $errors["START_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
            $errors["END_DATE"] = ABSENCE_ALREADY_SET_THIS_DAY;
            //error_log("Check B");
        }
        if (!$CALCULATED_COUNT_DATE_DATA) {
            $errors["START_DATE"] = SCHOOL_EVENT_OR_NOT_WORKING_DAY;
            $errors["END_DATE"] = SCHOOL_EVENT_OR_NOT_WORKING_DAY;
            //error_log("Check C");
        }

        $SAVEDATA['COUNT_DATE'] = $CALCULATED_COUNT_DATE_DATA["COUNT_DATE"];
        $SAVEDATA['CAL_DATE'] = $CALCULATED_COUNT_DATE_DATA["CAL_DATE"];

        if (isset($params["ABSENT_TYPE"]))
            $SAVEDATA["ABSENT_TYPE"] = $params["ABSENT_TYPE"];
        if (isset($params["START_TIME"]))
            $SAVEDATA["START_TIME"] = timeStrToSecond($params["START_TIME"]);
        if (isset($params["END_TIME"]))
            $SAVEDATA["END_TIME"] = timeStrToSecond($params["END_TIME"]);
        if (isset($params["COMMENT"]))
            $SAVEDATA["COMMENT"] = addText($params["COMMENT"]);

        if ($actionType)
            $SAVEDATA["ACTION_TYPE"] = $actionType;
        if ($absentType)
            $SAVEDATA["ABSENT_TYPE"] = $absentType;
        if ($startDate)
            $SAVEDATA["START_DATE"] = $startDate;
        if ($endDate)
            $SAVEDATA["END_DATE"] = $endDate;
        if ($choosedate) {
            $SAVEDATA["START_DATE"] = $choosedate;
            $SAVEDATA["END_DATE"] = $choosedate;
            $SAVEDATA["STATUS"] = 1;
        }

        $result = self::findExistStaffDailyAttendance($staffId, $absentType, $choosedate);

        if (!$result && $objectId == "new") {
            $SAVEDATA["STAFF_ID"] = $staffId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            if ($CALCULATED_COUNT_DATE_DATA && !$errors) {
                self::dbAccess()->insert('t_staff_attendance', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
            }
        } else {
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE[] = "ID = '" . $objectId . "'";
            if ($CALCULATED_COUNT_DATE_DATA && !$errors) {
                self::dbAccess()->update('t_staff_attendance', $SAVEDATA, $WHERE);
            }

            if ($result)
                $objectId = $result->ID;
        }

        $userObject = UserMemberDBAccess::findUserFromId($staffId);
        if ($userObject && $actionType == 1) {
            switch ($userObject->ROLE) {
                case 2:
                case 3:
                    self::jsonActionTeacherAttendanceSubject($params, $objectId);
                    break;
            }
        }

        if ($errors) {
            return array(
                "success" => false
                , "errors" => $errors
            );
        } else {
            return array(
                "success" => true
                , "errors" => $errors
                , "objectId" => $objectId
            );
        }
    }

    public static function sqlStaffAttendance($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $absentType = isset($params["ABSENT_TYPE"]) ? addText($params["ABSENT_TYPE"]) : "";
        $code = isset($params["CODE"]) ? addText($params["CODE"]) : "";
        $firstname = isset($params["FIRSTNAME"]) ? addText($params["FIRSTNAME"]) : "";
        $lastname = isset($params["LASTNAME"]) ? addText($params["LASTNAME"]) : "";
        $staffSchoolId = isset($params["STAFF_SCHOOL_ID"]) ? addText($params["STAFF_SCHOOL_ID"]) : "";
        $startDate = isset($params["START_DATE"]) ? addText($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? addText($params["END_DATE"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $object = isset($params["object"]) ? addText($params["object"]) : "";
        $actionType = isset($params["actionType"]) ? addText($params["actionType"]) : "";
        $searchSubjecttype = isset($params["CHOOSE_SUBJECT"]) ? addText($params["CHOOSE_SUBJECT"]) : "";
        $contract_type = isset($params["CONTRACT_TYPE"]) ? addText($params["CONTRACT_TYPE"]) : "";
        
        $SELECTION_A = array(
            "STAFF_INDEX"
            , "ID AS STAFF_ID"
            , "CODE AS STAFF_CODE"
            , "FIRSTNAME"
            , "LASTNAME"
            , "FIRSTNAME_LATIN"
            , "LASTNAME_LATIN"
            , "STAFF_SCHOOL_ID"
            , "GENDER"
            , "DATE_BIRTH"
            , "EMAIL"
            , "PHONE"
            , "CREATED_DATE"
        );

        $SELECTION_B = array(
            'ID AS ABSENT_ID'
            , 'ABSENT_TYPE'
            , 'COUNT_DATE'
            , 'START_DATE'
            , 'END_DATE'
            , 'START_TIME'
            , 'END_TIME'
            , "ACTION_TYPE"
        );
        $SELECTION_C = array(
            'ID AS ID'
            , 'SUBJECT_ID'          
        );

        $SQL = self::dbAccess()->select();
        $SQL->distinct();
        $SQL->from(array('A' => 't_staff'), $SELECTION_A);
        $SQL->joinLeft(array('C' => 't_teacher_attendance_subject'), 'A.ID=C.TEACHER_ID', $SELECTION_C);
        
        if ($object != 'h84k964g3434b5v6b54h34n45hgh3')
            $SQL->joinLeft(array('B' => 't_staff_attendance'), 'A.ID=B.STAFF_ID', $SELECTION_B);
        ///@veasna
        if($contract_type){
            $SQL->joinLeft(array('D' => 't_staff_contract'), 'B.STAFF_ID=D.STAFF_ID', array('CONTRACT_TYPE'));
            $SQL->where("D.CONTRACT_TYPE='" . $contract_type . "'");    
        }
        if ($objectId)
            $SQL->where("A.ID='" . $objectId . "'");

        if ($firstname)
            $SQL->where("A.FIRSTNAME LIKE '" . $firstname . "%'");

        if ($lastname)
            $SQL->where("A.LASTNAME LIKE '" . $lastname . "%'");

        if ($staffSchoolId)
            $SQL->where("A.STAFF_SCHOOL_ID LIKE '" . $staffSchoolId . "%'");

        if ($code)
            $SQL->where("A.CODE LIKE '" . $code . "%'");

        if ($absentType)       
            $SQL->where("B.ABSENT_TYPE = '" . $absentType . "%'");

        if ($searchSubjecttype)       
            $SQL->where("C.SUBJECT_ID = '" . $searchSubjecttype . "%'");
            
        if ($actionType)
            $SQL->where("B.ACTION_TYPE='" . $actionType . "'");

        if ($startDate && $endDate) {
            $SQL->where("B.START_DATE >= '" . $startDate . "' OR B.END_DATE <= '" . $endDate . "'");
        }

        if ($globalSearch) {
            $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.LASTNAME LIKE '" . $globalSearch . "%')";
            $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
            $SEARCH .= " ) ";
            $SQL->where($SEARCH);
        }

        switch (Zend_Registry::get('SCHOOL')->SORT_DISPLAY) {
            default:
                $SQL .= " ORDER BY A.STAFF_SCHOOL_ID DESC";
                break;
            case "1":
                $SQL .= " ORDER BY A.LASTNAME DESC";
                break;
            case "2":
                $SQL .= " ORDER BY A.FIRSTNAME DESC";
                break;
        }
        //error_log($SQL);
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonStaffBlockAttendance($params) {

        $data = array();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";

        $result = self::sqlStaffAttendance($params);

        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->STAFF_ID;
                $data[$i]["STAFF"] = "(" . $value->STAFF_CODE . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                $data[$i]["STAFF_SCHOOL_ID"] = setShowText($value->STAFF_SCHOOL_ID);
                $data[$i]["CODE"] = $value->STAFF_CODE;
                if (!SchoolDBAccess::displayPersonNameInGrid()) {
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                } else {
                    $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = $value->GENDER;
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE); 

                ////////////////////////////////////////////////////////////////////
                //Status of staff...
                ////////////////////////////////////////////////////////////////////
                $STATUS_DATA = StaffStatusDBAccess::getCurrentStaffStatus($value->STAFF_ID);
                $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
                $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
                $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";

                $i++;
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

    public static function jsonSearchStaffAttendance($params, $isJson = true) {

        $data = array();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";
        //$objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $contract_type = isset($params["CONTRACT_TYPE"]) ? addText($params["CONTRACT_TYPE"]) : "";

        $result = self::sqlStaffAttendance($params);

        $i = 0;
        if ($result)
            foreach ($result as $value) {
                $subjecType=SubjectDBAccess::findSubjectFromId($value->SUBJECT_ID);
                $data[$i]["STAFF_ID"] = $value->STAFF_ID;
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$i]["STAFF"] = "(" . $value->STAFF_CODE . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }else{
                    $data[$i]["STAFF"] = "(" . $value->STAFF_CODE . ") " . setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["STAFF_SCHOOL_ID"] = setShowText($value->STAFF_SCHOOL_ID);
                $data[$i]["CODE"] = $value->STAFF_CODE;
                if(!SchoolDBAccess::displayPersonNameInGrid()){
                    $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
                }else{
                    $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
                }
                $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
                $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
                $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
                $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
                $data[$i]["GENDER"] = $value->GENDER;
                $data[$i]["DATE_BIRTH"] = getShowDate($value->DATE_BIRTH);
                $data[$i]["EMAIL"] = setShowText($value->EMAIL);
                $data[$i]["PHONE"] = setShowText($value->PHONE);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                //@veasna
                $camemis_typeObject="";
                if($contract_type){
                    $camemis_typeObject = CamemisTypeDBAccess::findObjectFromId($value->CONTRACT_TYPE);
                    
                }
                $data[$i]["STAFF_CONTRACT_TYPE"] = $camemis_typeObject?$camemis_typeObject->NAME:'';
                //

                if($subjecType){
                        $data[$i]["SUBJECT_NAME"] = $subjecType->NAME;
                }else{
                    $data[$i]["SUBJECT_NAME"] ="?";
                }
                
                if ($value->ABSENT_TYPE) {
                    $data[$i]["ABSENT_ID"] = $value->ABSENT_ID;
                    $absentType = AbsentTypeDBAccess::findObjectFromId($value->ABSENT_TYPE);
                    if ($absentType) {
                        $data[$i]["ABSENT_TYPE"] = $absentType->NAME;
                    }
                    $data[$i]["DATE"] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                    $data[$i]["COUNT_DATE"] = $value->COUNT_DATE;
                } else {
                    $data[$i]["ABSENT_ID"] = "new";
                    $data[$i]["ABSENT_TYPE"] = "?";
                    $data[$i]["DATE"] = "?";
                    $data[$i]["COUNT_DATE"] = "?";
                }

                $i++;
            }

        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        if ($isJson) {
            return array(
                "success" => true
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

    public function jsonTeacherDayClassEvents($params) {

        require_once 'models/app_school/schedule/TeacherScheduleDBAccess.php';
        $DB_ACCESS = TeacherScheduleDBAccess::getInstance();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $choosedate = isset($params["choosedate"]) ? addText($params["choosedate"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";
        $staffId = isset($params["staffId"]) ? addText($params["staffId"]) : "";

        $searchParams["teacherId"] = $staffId;
        $searchParams["startdt"] = $choosedate;
        $searchParams["enddt"] = $choosedate;
        $searchParams["target"] = $target;

        return $DB_ACCESS->searchTeachingSession($searchParams, false);
    }

    public static function jsonActionTeacherAttendanceSubject($params, $objectId = "") {

        $scheduleId = $params["id"] ? addText($params["id"]) : "0";
        $newValue = $params["newValue"] ? addText($params["newValue"]) : "0";
        $choosedate = $params["choosedate"] ? setDate2DB($params["choosedate"]) : "";

        $attendanceObject = self::findAttendanceFromId($objectId);
        $scheduleObject = ScheduleDBAccess::findScheduleFromGuId($scheduleId);

        if ($attendanceObject && $scheduleObject) {

            if ($newValue) {
                $SAVEDATA["TEACHER_ID"] = $attendanceObject->STAFF_ID;
                $SAVEDATA["ATTENDANCE_ID"] = $objectId;
                $SAVEDATA["SCHEDULE_ID"] = $scheduleObject->ID;
                $SAVEDATA["SUBJECT_ID"] = $scheduleObject->SUBJECT_ID;
                $SAVEDATA["CLASS_ID"] = $scheduleObject->ACADEMIC_ID;
                $SAVEDATA["START_TIME"] = $scheduleObject->START_TIME;
                $SAVEDATA["END_TIME"] = $scheduleObject->END_TIME;
                $SAVEDATA["ATTENDANCE_DATE"] = $choosedate;
                self::dbAccess()->insert('t_teacher_attendance_subject', $SAVEDATA);
            } else {
                $CONDITION = array(
                    'TEACHER_ID = ? ' => $attendanceObject->STAFF_ID
                    , 'ATTENDANCE_ID = ? ' => $objectId
                    , 'SUBJECT_ID = ? ' => $scheduleObject->SUBJECT_ID
                    , 'START_TIME = ?' => $scheduleObject->START_TIME
                );

                self::dbAccess()->delete('t_teacher_attendance_subject', $CONDITION);
            }
        }
        return array("success" => true);
    }

    ////////////////////////////////////////////////////////////////////////////////////

    public function jsonReleaseStaffAttendance($params) {

        $objectId = $params["objectId"] ? addText($params["objectId"]) : "0";
        $facette = self::findAttendanceFromId($objectId);
        $status = $facette->STATUS;

        $SQL = "";
        $SQL .= "UPDATE ";
        $SQL .= " t_staff_attendance";
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
        $SQL .= " ID='" . $objectId . "'";
        self::dbAccess()->query($SQL);
        return array("success" => true, "status" => $newStatus);
    }

    public static function calCountByStartDateANDEndDate($startDate, $endDate) {

        $attendanceDatesRange = getDatesBetween2Dates($startDate, $endDate);
        $eventDays = SchooleventDBAccess::getEventDaysByCurrentSchoolyear(true);
        $schoolWorkingDays = SchoolDBAccess::getSchoolWorkingDays();

        $CHECK_DAYS = array();
        if ($attendanceDatesRange) {
            foreach ($attendanceDatesRange as $day) {
                if (!in_array($day, $eventDays)) {
                    $CHECK_DAYS[$day] = getWEEKDAY($day);
                }
            }
        }

        $count = 0;
        $data_caldate = array();
        if ($CHECK_DAYS) {
            foreach ($CHECK_DAYS as $day => $shortday) {
                if (in_array($shortday, $schoolWorkingDays)) {
                    $data_caldate[] = $day;
                    $count++;
                }
            }
        }

        $result['COUNT_DATE'] = $count;
        $result['CAL_DATE'] = implode(",", $data_caldate);
        return $result;
    }

    public static function getDatesRange($START_DATE, $END_DATE) {

        $START_DATE = strtotime($START_DATE);
        $END_DATE = strtotime($END_DATE);
        $DATE_RANGE = array();

        if ($START_DATE <= $END_DATE) {
            $DATE_RANGE[] = date('Y-m-d', $START_DATE);
            while ($START_DATE <> $END_DATE) {
                $START_DATE = mktime(0, 0, 0, date("m", $START_DATE), date("d", $START_DATE) + 1, date("Y", $START_DATE));
                $DATE_RANGE[] = date('Y-m-d', $START_DATE);
            }
        }
        return $DATE_RANGE;
    }

    public static function checkFuturDate($date) {

        $date = strtotime($date);
        $today = strtotime(getCurrentDBDate());

        if ($date > $today) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkExistingStaffAttendanceDate($staffId, $startDate, $endDate) {

        $SQL = self::dbAccess()->select();
        $SQL->from('t_staff_attendance', '*');
        $SQL->where("'" . $startDate . "' >= START_DATE AND '" . $startDate . "' <= END_DATE");
        $SQL->orWhere("'" . $endDate . "' >= START_DATE AND '" . $endDate . "' <= END_DATE");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        if ($result) {
            foreach ($result as $value) {
                $data[$value->STAFF_ID] = $value->STAFF_ID;
            }
        }

        if (!in_array($staffId, $data)) {
            return false;
        } else {
            return true;
        }
    }

    public static function checkTeacherAttendanceSubject($teacherId, $academicId, $subjectId, $starttime, $endtime, $day) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_teacher_attendance_subject", array("C" => "COUNT(*)"));
        $SQL->where("TEACHER_ID = ?",$teacherId);
        $SQL->where("CLASS_ID = '" . $academicId . "'");
        $SQL->where("SUBJECT_ID = ?",$subjectId);
        $SQL->where("START_TIME = '" . $starttime . "'");
        $SQL->where("END_TIME = '" . $endtime . "'");
        $SQL->where("    ATTENDANCE_DATE = '" . $day . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>