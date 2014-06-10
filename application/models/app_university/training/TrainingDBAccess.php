<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 01.03.2012
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once setUserLoacalization();

class TrainingDBAccess {

    CONST TABLE_TRAINING = "t_training";

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

    public static function getTrainingDataFromId($Id) {

        $data = array();
        $result = self::findTrainingFromId($Id);

        if ($result) {

            $data["CODE"] = $result->CODE;
            $data["ID"] = $result->ID;
            $data["STATUS"] = $result->STATUS;
            $data["NAME"] = setShowText($result->NAME);
            $data["EVALUATION_TYPE"] = $result->EVALUATION_TYPE;
            $data["SORTKEY"] = $result->SORTKEY;
            $data["EVALUATION"] = $result->EVALUATION ? true : false;
            $data["SHOW_EVALUATION"] = $result->EVALUATION ? YES : NO;

            $data["CERTIFICATE"] = $result->CERTIFICATE ? true : false;
            $data["SHOW_CERTIFICATE"] = $result->CERTIFICATE ? YES : NO;

            $data["GRADE_LEVEL"] = $result->GRADE_LEVEL;
            $data["POINTS_POSSIBLE"] = $result->POINTS_POSSIBLE ? $result->POINTS_POSSIBLE : 10;
            $data["START_DATE"] = getShowDate($result->START_DATE);
            $data["END_DATE"] = getShowDate($result->END_DATE);

            $data["REGISTRATION_START"] = getShowDate($result->REGISTRATION_START);
            $data["REGISTRATION_END"] = getShowDate($result->REGISTRATION_END);

            $data["TRAINING_END"] = $result->TRAINING_END ? true : false;

            $data["PARENT"] = $result->PARENT;
            $data["TERM"] = $result->TERM;
            $data["PROGRAM"] = $result->PROGRAM;

            $data["OBJECT_TYPE"] = $result->OBJECT_TYPE;

            $data["CONTACT_PERSON"] = setShowText($result->CONTACT_PERSON);
            $data["CONTACT_PHONE"] = setShowText($result->CONTACT_PHONE);
            $data["CONTACT_EMAIL"] = setShowText($result->CONTACT_EMAIL);

            $data["MAX_STUDENTS"] = $result->MAX_STUDENTS ? $result->MAX_STUDENTS : "1";

            $data["ENDTIME_BLOCK_MORNING"] = secondToHour($result->ENDTIME_BLOCK_MORNING);
            $data["ENDTIME_BLOCK_AFTERNOON"] = secondToHour($result->ENDTIME_BLOCK_AFTERNOON);

            $data["MO"] = $result->MO ? true : false;
            $data["TU"] = $result->TU ? true : false;
            $data["WE"] = $result->WE ? true : false;
            $data["TH"] = $result->TH ? true : false;
            $data["FR"] = $result->FR ? true : false;
            $data["SA"] = $result->SA ? true : false;
            $data["SU"] = $result->SU ? true : false;

            $data["SHOW_MO"] = $result->MO ? YES : NO;
            $data["SHOW_TU"] = $result->TU ? YES : NO;
            $data["SHOW_WE"] = $result->WE ? YES : NO;
            $data["SHOW_TH"] = $result->TH ? YES : NO;
            $data["SHOW_FR"] = $result->FR ? YES : NO;
            $data["SHOW_SA"] = $result->SA ? YES : NO;
            $data["SHOW_SU"] = $result->SU ? YES : NO;

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

        $result = self::findTrainingFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => self::getTrainingDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }

    public static function findTrainingFromId($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training");
        $SQL->where("ID = ?", $Id ? $Id : 0);
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function checkCount($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array("C" => "COUNT(*)"));
        $SQL->where("PARENT = ?", $Id);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

    public static function allTrainingprograms($parentId = false, $objectTypeLevel = false, $date = false) {

        $facette = self::findTrainingFromId($parentId);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array('*'));
        if ($parentId) {
            $SQL->where("PARENT = ?", $parentId);
            if ($objectTypeLevel)
                $SQL->where("OBJECT_TYPE='" . $objectTypeLevel . "'");
            switch ($facette->OBJECT_TYPE) {
                case "LEVEL":
                    if ($date) {
                        $SQL->where("START_DATE <= '" . $date . "' AND END_DATE >= '" . $date . "'");
                    }
                    break;
            }
        } else
            $SQL->where("PARENT = '0'");

        $SQL->order('SORTKEY ASC');

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function jsonTreeAllTrainings($params) {

        $objectTypeLevel = isset($params["objectTypeLevel"]) ? $params["objectTypeLevel"] : false;
        $children = isset($params["children"]) ? $params["children"] : false;
        $node = isset($params["node"]) ? addText($params["node"]) : 0;
        $choosedate = isset($params["choosedate"]) ? addText($params["choosedate"]) : "";

        if ($node == 0) {
            $resultRows = self::allTrainingprograms(false);
        } else {
            $resultRows = self::allTrainingprograms($node, $objectTypeLevel, $choosedate);
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
                            $programObject = self::findTrainingFromId($value->PROGRAM);
                            $data[$i]['title'] = stripslashes($programObject->NAME) . " &raquo; " . stripslashes($value->NAME);
                            $data[$i]['leaf'] = false;
                            $data[$i]['iconCls'] = "icon-folder_magnify";
                            $data[$i]['objecttype'] = "LEVEL";
                            $data[$i]['levelId'] = $value->LEVEL;
                            self::updateChildLevel($value->ID);
                            $data[$i]['parentId'] = $value->PARENT;
                            break;

                        case "TERM":
                            $programObject = self::findTrainingFromId($value->PROGRAM);
                            $levelObject = self::findTrainingFromId($value->LEVEL);
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
                            $data[$i]['cls'] = "nodeTextBold";
                            self::updateChildTerm($value->ID);
                            break;

                        case "CLASS":
                            $data[$i]['text'] = stripslashes($value->NAME);
                            $programObject = self::findTrainingFromId($value->PROGRAM);
                            $levelObject = self::findTrainingFromId($value->LEVEL);
                            $data[$i]['leaf'] = true;
                            $data[$i]['objecttype'] = "CLASS";
                            $data[$i]['parentId'] = $value->PARENT;
                            if ($value->STATUS == 1) {
                                $data[$i]['iconCls'] = "icon-blackboard";
                                $data[$i]['cls'] = "nodeTextBlue";
                            } else {
                                $data[$i]['iconCls'] = "icon-page_white_edit";
                                $data[$i]['cls'] = "nodeTextRed";
                            }

                            break;
                    }
                }
                $i++;
            }

        return $data;
    }

    public static function jsonSaveTraining($params) {

        $SAVEDATA = array();

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $parent = isset($params["parent"]) ? addText($params["parent"]) : "";
        $objctType = isset($params["objctType"]) ? addText($params["objctType"]) : "";

        $facette = self::findTrainingFromId($objectId);

        if (isset($params["NAME"])) {
            $SAVEDATA["NAME"] = addText($params["NAME"]);
            $name = $params["NAME"];
        }

        if (isset($params["GRADE_LEVEL"]))
            $SAVEDATA["GRADE_LEVEL"] = addText($params["GRADE_LEVEL"]);

        if (isset($params["EVALUATION_TYPE"]))
            $SAVEDATA["EVALUATION_TYPE"] = addText($params["EVALUATION_TYPE"]);

        if (isset($params["SORTKEY"]))
            $SAVEDATA["SORTKEY"] = addText($params["SORTKEY"]);

        if (isset($params["MAX_STUDENTS"]))
            $SAVEDATA["MAX_STUDENTS"] = addText($params["MAX_STUDENTS"]);

        if (isset($params["DESCRIPTION"]))
            $SAVEDATA["DESCRIPTION"] = addText($params["DESCRIPTION"]);

        if (isset($params["CONTACT_PERSON"]))
            $SAVEDATA["CONTACT_PERSON"] = addText($params["CONTACT_PERSON"]);

        if (isset($params["CONTACT_EMAIL"]))
            $SAVEDATA["CONTACT_EMAIL"] = addText($params["CONTACT_EMAIL"]);

        if (isset($params["CONTACT_PHONE"]))
            $SAVEDATA["CONTACT_PHONE"] = addText($params["CONTACT_PHONE"]);

        if (isset($params["REGISTRATION_START"]))
            $SAVEDATA["REGISTRATION_START"] = setDate2DB($params["REGISTRATION_START"]);

        if (isset($params["REGISTRATION_END"]))
            $SAVEDATA["REGISTRATION_END"] = setDate2DB($params["REGISTRATION_END"]);

        if (isset($params["ENDTIME_BLOCK_MORNING"]))
            $SAVEDATA['ENDTIME_BLOCK_MORNING'] = timeStrToSecond($params["ENDTIME_BLOCK_MORNING"]);

        if (isset($params["ENDTIME_BLOCK_AFTERNOON"]))
            $SAVEDATA['ENDTIME_BLOCK_AFTERNOON'] = timeStrToSecond($params["ENDTIME_BLOCK_AFTERNOON"]);

        if (isset($params["START_DATE"]) && isset($params["END_DATE"])) {
            $SAVEDATA["START_DATE"] = setDate2DB($params["START_DATE"]);
            $SAVEDATA["END_DATE"] = setDate2DB($params["END_DATE"]);
            $name = $params["START_DATE"] . "-" . addText($params["END_DATE"]);
        }

        $SAVEDATA['TRAINING_END'] = isset($params["TRAINING_END"]) ? 1 : 0;
        $SAVEDATA['CERTIFICATE'] = isset($params["CERTIFICATE"]) ? 1 : 0;
        $SAVEDATA['EVALUATION'] = isset($params["EVALUATION"]) ? 1 : 0;
        $SAVEDATA['TRAINING_END'] = isset($params["TRAINING_END"]) ? 1 : 0;
        $SAVEDATA['POINTS_POSSIBLE'] = isset($params["POINTS_POSSIBLE"]) ? $params["POINTS_POSSIBLE"] : 10;

        if ($facette) {

            $parentObject = self::findTrainingFromId($facette->PARENT);

            switch ($facette->OBJECT_TYPE) {

                case "LEVEL":
                    $TERM_OBJECT = self::findTrainingFromId($parentObject->ID);
                    $PROGRAM_OBJECT = self::findTrainingFromId($TERM_OBJECT->PROGRAM);
                    $SAVEDATA['PROGRAM'] = $TERM_OBJECT->PROGRAM;
                    $SAVEDATA['TERM'] = $TERM_OBJECT->ID;
                    $SAVEDATA['LEVEL'] = "";
                    break;

                case "TERM":

                    $SAVEDATA['MO'] = isset($params["MO"]) ? 1 : 0;
                    $SAVEDATA['TU'] = isset($params["TU"]) ? 1 : 0;
                    $SAVEDATA['WE'] = isset($params["WE"]) ? 1 : 0;
                    $SAVEDATA['TH'] = isset($params["TH"]) ? 1 : 0;
                    $SAVEDATA['FR'] = isset($params["FR"]) ? 1 : 0;
                    $SAVEDATA['SA'] = isset($params["SA"]) ? 1 : 0;
                    $SAVEDATA['SU'] = isset($params["SU"]) ? 1 : 0;

                    $SAVEDATA['PROGRAM'] = $parentObject->PROGRAM;
                    $SAVEDATA['LEVEL'] = "";
                    $SAVEDATA['TERM'] = "";

                    $TERM_OBJECT = self::findTrainingFromId($objectId);
                    if ($TERM_OBJECT)
                        $PROGRAM_OBJECT = self::findTrainingFromId($TERM_OBJECT->PARENT);
                    if ($PROGRAM_OBJECT)
                        $SAVEDATA['PROGRAM'] = $PROGRAM_OBJECT->ID;

                    break;

                case "CLASS":

                    $LEVEL_OBJECT = self::findTrainingFromId($parentObject->ID);
                    $SAVEDATA['PROGRAM'] = $LEVEL_OBJECT->PROGRAM;
                    $SAVEDATA['TERM'] = $LEVEL_OBJECT->TERM;
                    $SAVEDATA['LEVEL'] = $LEVEL_OBJECT->ID;
                    $SAVEDATA['CERTIFICATE'] = $LEVEL_OBJECT->CERTIFICATE;
                    $SAVEDATA['EVALUATION'] = $LEVEL_OBJECT->EVALUATION;
                    $SAVEDATA['TRAINING_END'] = $LEVEL_OBJECT->TRAINING_END;
                    $SAVEDATA['START_DATE'] = $LEVEL_OBJECT->START_DATE;
                    $SAVEDATA['END_DATE'] = $LEVEL_OBJECT->END_DATE;

                    $SAVEDATA['MO'] = $LEVEL_OBJECT->MO;
                    $SAVEDATA['TU'] = $LEVEL_OBJECT->TU;
                    $SAVEDATA['WE'] = $LEVEL_OBJECT->WE;
                    $SAVEDATA['TH'] = $LEVEL_OBJECT->TH;
                    $SAVEDATA['FR'] = $LEVEL_OBJECT->FR;
                    $SAVEDATA['SA'] = $LEVEL_OBJECT->SA;
                    $SAVEDATA['SU'] = $LEVEL_OBJECT->SU;
                    break;
            }
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);

            self::dbAccess()->update('t_training', $SAVEDATA, $WHERE);
        } else {

            $parentObject = self::findTrainingFromId($parent);
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
                    $SAVEDATA['CERTIFICATE'] = $parentObject->CERTIFICATE;
                    $SAVEDATA['EVALUATION'] = $parentObject->EVALUATION;
                    $SAVEDATA['PROGRAM'] = $parentObject->PROGRAM;
                    $SAVEDATA['TERM'] = $parentObject->TERM;
                    $SAVEDATA['LEVEL'] = $parentObject->ID;
                    $SAVEDATA['START_DATE'] = $parentObject->START_DATE;
                    $SAVEDATA['END_DATE'] = $parentObject->END_DATE;
                    break;
            }
            $SAVEDATA["PARENT"] = $parent;
            $SAVEDATA["OBJECT_TYPE"] = $objctType;
            $SAVEDATA["CODE"] = createCode();
            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;

            self::dbAccess()->insert(self::TABLE_TRAINING, $SAVEDATA);
        }

        return array("success" => true, "text" => setShowText($name));
    }

    public function jsonReleaseTraining($Id) {

        $SAVEDATA = array();

        $facette = self::findTrainingFromId($Id);
        $newStatus = 0;
        switch ($facette->STATUS) {
            case 0:
                $newStatus = 1;
                $SAVEDATA["STATUS"] = 1;
                $SAVEDATA["ENABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA["ENABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                self::dbAccess()->update(self::TABLE_TRAINING, $SAVEDATA, $WHERE);
                break;
            case 1:
                $newStatus = 0;
                $SAVEDATA["STATUS"] = 0;
                $SAVEDATA["DISABLED_DATE"] = getCurrentDBDateTime();
                $SAVEDATA["DISABLED_BY"] = Zend_Registry::get('USER')->CODE;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $Id);
                self::dbAccess()->update(self::TABLE_TRAINING, $SAVEDATA, $WHERE);
                break;
        }

        return array("success" => true, "status" => $newStatus);
    }

    public function jsonRemoveTraining($Id) {

        self::dbAccess()->delete(
                't_training'
                , array("ID='" . $Id . "'")
        );
        self::dbAccess()->delete(
                't_training'
                , array("PARENT='" . $Id . "'")
        );
        self::dbAccess()->delete(
                't_training_subject'
                , array("TRAINING='" . $Id . "'")
        );
        self::dbAccess()->delete(
                't_student_training'
                , array("TRAINING='" . $Id . "'")
        );
        self::dbAccess()->delete(
                't_subject_teacher_training'
                , array("TRAINING='" . $Id . "'")
        );
        return array("success" => true);
    }

    //All Level....
    public static function updateChildProgram($Id) {

        $facette = self::findTrainingFromId($Id);
        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array('*'));
        $SQL->where("PARENT = ?", $Id);

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA["PROGRAM"] = $facette->ID;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update(self::TABLE_TRAINING, $SAVEDATA, $WHERE);
            }
        }
    }

    //All Term...
    public static function updateChildLevel($Id) {

        $facette = self::findTrainingFromId($Id);
        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array('*'));
        $SQL->where("PARENT = ?", $Id);

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                $SAVEDATA["LEVEL"] = $facette->ID;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update(self::TABLE_TRAINING, $SAVEDATA, $WHERE);
            }
        }
    }

    //All Class....
    public static function updateChildTerm($Id) {

        $facette = self::findTrainingFromId($Id);

        $UPDATE_PARENT = "UPDATE t_training SET";
        $UPDATE_PARENT .= " LEVEL='" . $facette->PARENT . "'";
        $UPDATE_PARENT .= " WHERE ID ='" . $facette->ID . "'";
        self::dbAccess()->query($UPDATE_PARENT);

        $facette = self::findTrainingFromId($Id);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array('*'));
        $SQL->where("PARENT = ?", $Id);

        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);
        if ($result) {
            foreach ($result as $value) {
                $SAVEDATA["PROGRAM"] = $facette->PROGRAM;
                $SAVEDATA["TERM"] = $facette->ID;
                $SAVEDATA["LEVEL"] = $facette->LEVEL;
                $SAVEDATA["CERTIFICATE"] = $facette->CERTIFICATE;
                $SAVEDATA["EVALUATION"] = $facette->EVALUATION;
                $SAVEDATA["GRADE_LEVEL"] = $facette->GRADE_LEVEL;
                $SAVEDATA["START_DATE"] = $facette->START_DATE;
                $SAVEDATA["END_DATE"] = $facette->END_DATE;
                $SAVEDATA["REGISTRATION_START"] = $facette->REGISTRATION_START;
                $SAVEDATA["REGISTRATION_END"] = $facette->REGISTRATION_END;
                $SAVEDATA['MO'] = $facette->MO;
                $SAVEDATA['TU'] = $facette->TU;
                $SAVEDATA['WE'] = $facette->WE;
                $SAVEDATA['TH'] = $facette->TH;
                $SAVEDATA['FR'] = $facette->FR;
                $SAVEDATA['SA'] = $facette->SA;
                $SAVEDATA['SU'] = $facette->SU;
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $value->ID);
                self::dbAccess()->update(self::TABLE_TRAINING, $SAVEDATA, $WHERE);
            }
        }

        $UPDATE_SUBJECT = "UPDATE t_training_subject SET";
        $UPDATE_SUBJECT .= " PROGRAM='" . $facette->PROGRAM . "'";
        $UPDATE_SUBJECT .= " ,TERM='" . $facette->ID . "'";
        $UPDATE_SUBJECT .= " ,LEVEL='" . $facette->LEVEL . "'";
        $UPDATE_SUBJECT .= " WHERE ID ='" . $facette->ID . "'";
        self::dbAccess()->query($UPDATE_SUBJECT);
    }

    public static function sqlTrainingStudentFromId($Id) {
        $SQL = self::dbAccess()->select();
        $SQL .= " FROM t_training AS A";
        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.ID = '" . $Id . "'";
        //error_log($SQL);
        return self::dbAccess()->fetchRow($SQL);
    }

    //////////////////////////////////////////////////////////////
    //@Sea Peng
    public static function checkTrainingClass($date, $trainingId) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_training", array("C" => "COUNT(*)"));
        $SQL->where("OBJECT_TYPE='CLASS'");
        $SQL->where("ID = " . $trainingId . "");
        $SQL->where("'" . $date . "' BETWEEN START_DATE AND END_DATE");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }

}

?>