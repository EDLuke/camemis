<?php
///////////////////////////////////////////////////////////
// @Sea Peng
// Date: 06.06.2013
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_school/academic/AcademicDBAccess.php';
require_once setUserLoacalization();

class ExtraClassDBAccess {

    public $data = array();
    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public function getObject() {
        return AcademicDBAccess::findClass($this->classId);
    }

    public static function getExtraClassDataFromId($Id) {

        $data = array();
        $result = self::findExtraClassFromId($Id);

        if ($result) {

            $data["CODE"] = $result->CODE;
            $data["ID"] = $result->ID;
            $data["STATUS"] = $result->STATUS;
            $data["NAME"] = setShowText($result->NAME);
            $data["SUBJECT_ID"] = setShowText($result->SUBJECT_ID);
            $data["TEACHER_ID"] = setShowText($result->TEACHER_ID);
            $data["ROOM_ID"] = setShowText($result->ROOM_ID);
            $data["TEACHER_NAME"] = $result->LASTNAME . " " . $result->FIRSTNAME;
            $data["ROOM_NAME"] = $result->ROOM_NAME;
            $data["SORTKEY"] = $result->SORTKEY;

            $data["MONDAY"] = $result->MO;
            $data["TUESDAY"] = $result->TU;
            $data["WEDNESDAY"] = $result->WE;
            $data["THURSDAY"] = $result->TH;
            $data["FRIDAY"] = $result->FR;
            $data["SATURDAY"] = $result->SA;
            $data["SUNDAY"] = $result->SU;

            $data["GRADE_LEVEL"] = $result->GRADE_LEVEL;
            $data["POINTS_POSSIBLE"] = $result->POINTS_POSSIBLE ? $result->POINTS_POSSIBLE : 10;
            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);

            $data["PARENT"] = $result->PARENT;
            $data["TERM"] = $result->TERM;
            $data["PROGRAM"] = $result->PROGRAM;

            $data["OBJECT_TYPE"] = $result->OBJECT_TYPE;

            $data["CONTACT_PERSON"] = setShowText($result->CONTACT_PERSON);
            $data["CONTACT_PHONE"] = setShowText($result->CONTACT_PHONE);
            $data["CONTACT_EMAIL"] = setShowText($result->CONTACT_EMAIL);

            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);

            $data["CREATED_DATE"] = getShowDateTime($result->CREATED_DATE);
            $data["MODIFY_DATE"] = getShowDateTime($result->MODIFY_DATE);
            $data["ENABLED_DATE"] = getShowDateTime($result->ENABLED_DATE);
            $data["DISABLED_DATE"] = getShowDateTime($result->DISABLED_DATE);

            $data["CREATED_BY"] = setShowText($result->CREATED_BY);
            $data["MODIFY_BY"] = setShowText($result->MODIFY_BY);
            $data["ENABLED_BY"] = setShowText($result->ENABLED_BY);
            $data["DISABLED_BY"] = setShowText($result->DISABLED_BY);
        }

        return $data;
    }

    public function jsonLoadObject($Id) {

        $result = self::findExtraClassFromId($Id);
        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getExtraClassDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function findExtraClassFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_extraclass'));
        $SQL->joinLeft(array('B' => 't_staff'), 'A.TEACHER_ID=B.ID', array('B.FIRSTNAME', 'B.LASTNAME'));
        $SQL->joinLeft(array('C' => 't_room'), 'A.ROOM_ID=C.ID', array('C.NAME AS ROOM_NAME'));
        $SQL->where("A.ID = ?",$Id);
        //error_log($SQL->__toString());

        $stmt = self::dbAccess()->query($SQL);
        return $stmt->fetch();
    }

    public static function checkCount($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_extraclass", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?",$Id);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function allExtraClassprograms($parentId = false, $objectTypeLevel = false) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_extraclass", array('*'));
        if ($parentId) {
            $SQL->where("PARENT = ?",$parentId);
            if ($objectTypeLevel)
                $SQL->where("OBJECT_TYPE='" . $objectTypeLevel . "'");
        } else
            $SQL->where("PARENT = '0'");
        $SQL->order('SORTKEY ASC');
        //error_log($SQL->__toString());

        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllExtraClass($params) {

        $objectTypeLevel = isset($params["objectTypeLevel"]) ? $params["objectTypeLevel"] : false;
        $children = isset($params["children"]) ? $params["children"] : false;
        $node = isset($params["node"]) ? addText($params["node"]) : 0;

        if ($node == 0) {
            $resultRows = self::allExtraClassprograms(false);
        } else {
            $resultRows = self::allExtraClassprograms($node, $objectTypeLevel);
        }

        $data = array();
        $i = 0;
        if ($resultRows)
            foreach ($resultRows as $value) {

                if (Zend_Registry::get('IS_SUPER_ADMIN') == 1) {
                    $data[$i]['allowDelete'] = true;
                } else {
                    if (self::checkCount($value->ID)) {
                        $data[$i]['allowDelete'] = false;
                    } else {
                        $data[$i]['allowDelete'] = true;
                    }
                }

                $data[$i]['id'] = "" . $value->ID . "";
                $data[$i]['leaf'] = false;

                $data[$i]['cls'] = "nodeTextBoldBlue";

                if ($children == "TERM") {
                    switch ($value->OBJECT_TYPE) {
                        case "PROGRAM":

                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-bricks";
                            $data[$i]['objecttype'] = "PROGRAM";
                            self::updateChildProgram($value->ID);
                            break;

                        case "LEVEL":

                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            $data[$i]['objecttype'] = "LEVEL";
                            self::updateChildLevel($value->ID);
                            break;

                        case "TERM":

                            $data[$i]['leaf'] = true;
                            $data[$i]['objecttype'] = "TERM";
                            $data[$i]['text'] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                            if ($value->STATUS == 1) {
                                $data[$i]['iconCls'] = "icon-date";
                            } else {
                                $data[$i]['iconCls'] = "icon-date_edit";
                            }
                            self::updateChildTerm($value->ID);
                            break;
                    }
                } else {
                    switch ($value->OBJECT_TYPE) {
                        case "PROGRAM":
                            $data[$i]['text'] = stripslashes($value->NAME);
                            $data[$i]['title'] = stripslashes($value->NAME);
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-bricks";
                            $data[$i]['objecttype'] = "PROGRAM";
                            $data[$i]['programId'] = $value->PROGRAM;
                            self::updateChildProgram($value->ID);
                            $data[$i]['parentId'] = $value->PARENT;
                            break;

                        case "LEVEL":
                            $data[$i]['text'] = stripslashes($value->NAME);
                            $programObject = self::findExtraClassFromId($value->PROGRAM);
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            $data[$i]['objecttype'] = "LEVEL";
                            $data[$i]['levelId'] = $value->LEVEL;
                            self::updateChildLevel($value->ID);
                            $data[$i]['parentId'] = $value->PARENT;
                            break;

                        case "TERM":
                            $programObject = self::findExtraClassFromId($value->PROGRAM);
                            $levelObject = self::findExtraClassFromId($value->LEVEL);
                            $data[$i]['leaf'] = false;
                            $data[$i]['objecttype'] = "TERM";
                            $data[$i]['text'] = getShowDate($value->START_DATE) . " - " . getShowDate($value->END_DATE);
                            if ($value->STATUS == 1) {
                                $data[$i]['iconCls'] = "icon-date";
                            } else {
                                $data[$i]['iconCls'] = "icon-date_edit";
                            }
                            $data[$i]['parentId'] = $value->PARENT;
                            $data[$i]['termId'] = $value->TERM;
                            self::updateChildTerm($value->ID);
                            break;

                        case "CLASS":
                            $data[$i]['text'] = stripslashes($value->NAME);
                            $programObject = self::findExtraClassFromId($value->PROGRAM);
                            $levelObject = self::findExtraClassFromId($value->LEVEL);
                            $data[$i]['title'] = stripslashes($programObject->NAME) . " &raquo; " . stripslashes($levelObject->NAME) . " &raquo; " . stripslashes($value->NAME);
                            $data[$i]['leaf'] = true;
                            $data[$i]['objecttype'] = "CLASS";
                            $data[$i]['parentId'] = $value->PARENT;
                            if ($value->STATUS == 1) {
                                $data[$i]['iconCls'] = "icon-blackboard";
                                $data[$i]['cls'] = "nodeTextBoldBlue";
                            } else {
                                $data[$i]['iconCls'] = "icon-page_white_edit";
                                $data[$i]['cls'] = "nodeTextRedBold";
                            }

                            break;
                    }
                }
                $i++;
            }

        return $data;
    }

    public static function jsonSaveExtraClass($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parent = isset($params["parent"]) ? addText($params["parent"]) : "";
        $objctType = isset($params["objctType"]) ? addText($params["objctType"]) : "";
        $date = isset($params["START_DATE"]) ? addText($params["START_DATE"]) : "";


        $errors = array();

        $facette = self::findExtraClassFromId($objectId);

        if ($date) {
            $ACADEMIC_OBJECT = AcademicDBAccess::findAcademicBetweenDate(setDate2DB($date));
            $SAVEDATA["SCHOOLYEAR_ID"] = $ACADEMIC_OBJECT->ID;
        }

        if (isset($params["NAME"])) {
            $SAVEDATA["NAME"] = addText($params["NAME"]);
            $name = addText($params["NAME"]);
        }

        if (isset($params["SUBJECT_ID"]))
            $SAVEDATA["SUBJECT_ID"] = addText($params["SUBJECT_ID"]);

        if (isset($params["TEACHER_ID"]))
            $SAVEDATA["TEACHER_ID"] = addText($params["TEACHER_ID"]);

        if (isset($params["ROOM_ID"]))
            $SAVEDATA["ROOM_ID"] = addText($params["ROOM_ID"]);

        if (isset($params["GRADE_LEVEL"]))
            $SAVEDATA["GRADE_LEVEL"] = addText($params["GRADE_LEVEL"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA["SORTKEY"] =  addText($params["SORTKEY"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if (isset($params["MONDAY"]) && $params["MONDAY"] != 'Start Time - End Time')
            $SAVEDATA["MO"] = addText($params["MONDAY"]);

        if (isset($params["TUESDAY"]) && $params["TUESDAY"] != 'Start Time - End Time')
            $SAVEDATA["TU"] = addText($params["TUESDAY"]);

        if (isset($params["WEDNESDAY"]) && $params["WEDNESDAY"] != 'Start Time - End Time')
            $SAVEDATA["WE"] = addText($params["WEDNESDAY"]);

        if (isset($params["THURSDAY"]) && $params["THURSDAY"] != 'Start Time - End Time')
            $SAVEDATA["TH"] = addText($params["THURSDAY"]);

        if (isset($params["FRIDAY"]) && $params["FRIDAY"] != 'Start Time - End Time')
            $SAVEDATA["FR"] = addText($params["FRIDAY"]);

        if (isset($params["SATURDAY"]) && $params["SATURDAY"] != 'Start Time - End Time')
            $SAVEDATA["SA"] = addText($params["SATURDAY"]);

        if (isset($params["SUNDAY"]) && $params["SUNDAY"] != 'Start Time - End Time')
            $SAVEDATA["SU"] = addText($params["SUNDAY"]);

        if (isset($params["CONTACT_PERSON"]))
            $SAVEDATA["CONTACT_PERSON"] = $params["CONTACT_PERSON"];

        if (isset($params["CONTACT_EMAIL"]))
            $SAVEDATA["CONTACT_EMAIL"] = $params["CONTACT_EMAIL"];

        if (isset($params["CONTACT_PHONE"]))
            $SAVEDATA["CONTACT_PHONE"] = $params["CONTACT_PHONE"];

        if (isset($params["START_DATE"]) && isset($params["END_DATE"])) {
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);
            $SAVEDATA["END_DATE"] = setDate2DB($params["END_DATE"]);

            $name = $params["START_DATE"] . "-" . $params["END_DATE"];

            $SAVEDATA['POINTS_POSSIBLE'] = isset($params["POINTS_POSSIBLE"]) ? $params["POINTS_POSSIBLE"] : 10;

            //check date errors
            $CHECK_ERROR_START_DATE = timeDifference(getCurrentDBDate(), setDate2DB($params["START_DATE"]));
            $CHECK_ERROR_END_DATE = timeDifference(getCurrentDBDate(), setDate2DB($params["END_DATE"]));
            $CHECK_ERROR_START_END_DATE = timeDifference(setDate2DB($params["START_DATE"]), setDate2DB($params["END_DATE"]));

            if ($CHECK_ERROR_START_DATE) {
                $errors["START_DATE"] = CHECK_DATE_PAST;
            } elseif ($CHECK_ERROR_END_DATE) {
                $errors["END_DATE"] = CHECK_DATE_PAST;
            } elseif ($CHECK_ERROR_START_DATE && $CHECK_ERROR_END_DATE) {
                $errors["START_DATE"] = CHECK_DATE_PAST;
                $errors["END_DATE"] = CHECK_DATE_PAST;
            } elseif ($CHECK_ERROR_START_END_DATE) {
                $errors["START_DATE"] = ERROR;
                $errors["END_DATE"] = ERROR;
            } else {
                $errors = array();
            }
        }


        if ($facette) {
            $parentObject = self::findExtraClassFromId($facette->PARENT);

            switch ($facette->OBJECT_TYPE) {

                case "LEVEL":
                    $TERM_OBJECT = self::findExtraClassFromId($parentObject->ID);
                    $PROGRAM_OBJECT = self::findExtraClassFromId($TERM_OBJECT->PROGRAM);
                    $SAVEDATA['PROGRAM'] = $TERM_OBJECT->PROGRAM;
                    $SAVEDATA['TERM'] = $TERM_OBJECT->ID;
                    $SAVEDATA['LEVEL'] = "";
                    break;

                case "TERM":
                    $SAVEDATA['PROGRAM'] = $parentObject->PROGRAM;
                    $SAVEDATA['LEVEL'] = "";
                    $SAVEDATA['TERM'] = "";

                    $TERM_OBJECT = self::findExtraClassFromId($objectId);
                    if ($TERM_OBJECT)
                        $PROGRAM_OBJECT = self::findExtraClassFromId($TERM_OBJECT->PARENT);
                    if ($PROGRAM_OBJECT)
                        $SAVEDATA['PROGRAM'] = $PROGRAM_OBJECT->ID;
                    break;

                case "CLASS":
                    $LEVEL_OBJECT = self::findExtraClassFromId($parentObject->ID);
                    $SAVEDATA['PROGRAM'] = $LEVEL_OBJECT->PROGRAM;
                    $SAVEDATA['TERM'] = $LEVEL_OBJECT->TERM;
                    $SAVEDATA['LEVEL'] = $LEVEL_OBJECT->ID;
                    $SAVEDATA['START_DATE'] = $LEVEL_OBJECT->START_DATE;
                    $SAVEDATA['END_DATE'] = $LEVEL_OBJECT->END_DATE;
                    $SAVEDATA['SCHOOLYEAR_ID'] = $LEVEL_OBJECT->SCHOOLYEAR_ID;
                    break;
            }
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);

            if (!$errors)
                self::dbAccess()->update('t_extraclass', $SAVEDATA, $WHERE);
        } else {

            $parentObject = self::findExtraClassFromId($parent);
            switch ($objctType) {

                case "LEVEL":
                    $SAVEDATA['PROGRAM'] = $parentObject->PROGRAM;
                    $SAVEDATA['TERM'] = $parentObject->ID;
                    $SAVEDATA['START_DATE'] = $parentObject->START_DATE;
                    $SAVEDATA['END_DATE'] = $parentObject->END_DATE;
                    $SAVEDATA['LEVEL'] = '';
                    break;

                case "TERM":
                    $SAVEDATA['PROGRAM'] = $parentObject->ID;
                    $SAVEDATA['LEVEL'] = '';
                    break;

                case "CLASS":
                    $SAVEDATA['PROGRAM'] = $parentObject->PROGRAM;
                    $SAVEDATA['TERM'] = $parentObject->TERM;
                    $SAVEDATA['LEVEL'] = $parentObject->ID;
                    $SAVEDATA['START_DATE'] = $parentObject->START_DATE;
                    $SAVEDATA['END_DATE'] = $parentObject->END_DATE;
                    $SAVEDATA['SCHOOLYEAR_ID'] = $parentObject->SCHOOLYEAR_ID;
                    break;
            }
            $SAVEDATA["PARENT"] = $parent;
            $SAVEDATA["OBJECT_TYPE"] = $objctType;
            $SAVEDATA["CODE"] = createCode();
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            if (!$errors)
                self::dbAccess()->insert('t_extraclass', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
            //error_log($objectId);
        }

        if ($errors) {
            return array("success" => false, "errors" => $errors);
        } else {
            return array(
                "success" => true
                , "text" => setShowText($name)
                , "objectId" => $objectId);
        }
    }

    public function jsonReleaseExtraClass($Id) {

        $SAVEDATA = array();

        $facette = self::findExtraClassFromId($Id);
        $newStatus = 0;
        switch ($facette->STATUS) {
            case 0:
                $newStatus = 1;
                $SAVEDATA["STATUS"] = 1;
                $SAVEDATA["ENABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA["ENABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                self::dbAccess()->update('t_extraclass', $SAVEDATA, $WHERE);
                break;
            case 1:
                $newStatus = 0;
                $SAVEDATA["STATUS"] = 0;
                $SAVEDATA["DISABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA["DISABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                self::dbAccess()->update('t_extraclass', $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true, "status" => $newStatus);
    }

    public function jsonRemoveExtraClass($Id) {

        self::dbAccess()->delete(
                't_extraclass'
                , array("ID='" . $Id . "'")
        );
        self::dbAccess()->delete(
                't_extraclass'
                , array("PARENT='" . $Id . "'")
        );
        self::dbAccess()->delete(
                't_student_extraclass'
                , array("EXTRACLASS='" . $Id . "'")
        );
        return array("success" => true);
    }

    //All Level....
    public static function updateChildProgram($Id) {

        $facette = self::findExtraClassFromId($Id);
        $SQL = self::dbAccess()->select();
        $SQL->from("t_extraclass", array('*'));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA["PROGRAM"] = $facette->ID;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update('t_extraclass', $SAVEDATA, $WHERE);
            }
        }
    }

    //All Term...
    public static function updateChildLevel($Id) {

        $facette = self::findExtraClassFromId($Id);
        $SQL = self::dbAccess()->select();
        $SQL->from("t_extraclass", array('*'));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                $SAVEDATA["LEVEL"] = $facette->ID;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update('t_extraclass', $SAVEDATA, $WHERE);
            }
        }
    }

    //All Class....
    public static function updateChildTerm($Id) {

        $facette = self::findExtraClassFromId($Id);
        $SQL = self::dbAccess()->select();
        $SQL->from("t_extraclass", array('*'));
        $SQL->where("PARENT = ?",$Id);
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                $SAVEDATA["TERM"] = $facette->ID;
                $SAVEDATA["LEVEL"] = $facette->LEVEL;
                $SAVEDATA["GRADE_LEVEL"] = $facette->GRADE_LEVEL;
                $SAVEDATA["START_DATE"] = $facette->START_DATE;
                $SAVEDATA["END_DATE"] = $facette->END_DATE;

                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update('t_extraclass', $SAVEDATA, $WHERE);
            }
        }
    }

    public function jsonTeacherExtraClass($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : '';

        $SELECT_DATA = array(
            "A.ID AS ID"
            , "A.CODE AS CODE"
            , "A.FIRSTNAME AS FIRSTNAME"
            , "A.LASTNAME AS LASTNAME"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_staff'), $SELECT_DATA);
        $SQL->joinLeft(array('B' => 't_teacher_subject'), 'A.ID=B.TEACHER', array());
        $SQL->joinLeft(array('C' => 't_subject'), 'B.SUBJECT=C.ID', array());

        if ($subjectId)
            $SQL->where("C.ID ='" . $subjectId . "'");

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
        //$SQL->group("ID");
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {
                $status = "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user_add.png' border='0'>";

                $data[$i]["ID"] = $value->ID;
                $data[$i]["STATUS"] = $status;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["LASTNAME"] = $value->LASTNAME;
                $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                $data[$i]["FULL_NAME"] = $value->LASTNAME . " " . $value->FIRSTNAME;

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

    public function jsonListExtraClass($params) {
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
        $teacherId = isset($params["teacherId"]) ? addText($params["teacherId"]) : "";

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_extraclass'));
        $SQL->joinLeft(array('B' => 't_student_extraclass'), 'A.ID=B.EXTRACLASS', array('B.EXTRACLASS'));
        $SQL->joinLeft(array('C' => 't_staff'), 'A.TEACHER_ID=C.ID', array('C.FIRSTNAME', 'C.LASTNAME'));
        $SQL->joinLeft(array('D' => 't_room'), 'A.ROOM_ID=D.ID', array('D.NAME AS ROOM_NAME'));

        if ($studentId)
            $SQL->where("B.STUDENT ='" . $studentId . "'");

        if ($teacherId)
            $SQL->where("A.TEACHER_ID ='" . $teacherId . "'");

        $SQL->group("A.ID");
        //error_log($SQL->__toString());

        $result = self::dbAccess()->fetchAll($SQL);

        $data = array();
        $i = 0;
        if ($result) {
            foreach ($result as $value) {

                $data[$i]["ID"] = $value->ID;
                $data[$i]["CODE"] = $value->CODE;
                $data[$i]["STATUS"] = $value->STATUS;
                $data[$i]["SUBJECT_NAME"] = $value->NAME;
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["TEACHER_NAME"] = $value->FIRSTNAME . " " . $value->LASTNAME;
                $data[$i]["ROOM_NAME"] = setShowText($value->ROOM_NAME);
                $data[$i]["START_DATE"] = getShowDate($value->START_DATE);
                $data[$i]["END_DATE"] = getShowDate($value->END_DATE);
                $data[$i]["MONDAY"] = setShowText($value->MO);
                $data[$i]["TUESDAY"] = setShowText($value->TU);
                $data[$i]["WEDNESDAY"] = setShowText($value->WE);
                $data[$i]["THURSDAY"] = setShowText($value->TH);
                $data[$i]["FRIDAY"] = setShowText($value->FR);
                $data[$i]["SATURDAY"] = setShowText($value->SA);
                $data[$i]["SUNDAY"] = setShowText($value->SU);

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

}

?>