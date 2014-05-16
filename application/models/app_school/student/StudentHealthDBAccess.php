<?php

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/UserAuth.php';
require_once 'models/HealthSettingDBAccess.php';
require_once setUserLoacalization();

class StudentHealthDBAccess {

    private static $instance = null;

    static function getInstance() {
        if (self::$instance === null) {

            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess() {
        return self::dbAccess()->select();
    }

    public static function getEyeData() {
        $data = array(
            1 => array("METRE" => "6/60", "FOOT" => "20/200", "DECIMAL" => "0.10", "LOGMAR" => "1.00"),
            2 => array("METRE" => "6/48", "FOOT" => "20/160", "DECIMAL" => "0.125", "LOGMAR" => "0.90"),
            3 => array("METRE" => "6/38", "FOOT" => "20/125", "DECIMAL" => "0.16", "LOGMAR" => "0.80"),
            4 => array("METRE" => "6/30", "FOOT" => "20/100", "DECIMAL" => "0.20", "LOGMAR" => "0.70"),
            5 => array("METRE" => "6/24", "FOOT" => "20/80", "DECIMAL" => "0.25", "LOGMAR" => "0.60"),
            6 => array("METRE" => "6/19", "FOOT" => "20/63", "DECIMAL" => "0.32", "LOGMAR" => "0.50"),
            7 => array("METRE" => "6/15", "FOOT" => "20/50", "DECIMAL" => "0.40", "LOGMAR" => "0.40"),
            8 => array("METRE" => "6/12", "FOOT" => "20/40", "DECIMAL" => "0.50", "LOGMAR" => "0.30"),
            9 => array("METRE" => "6/9.5", "FOOT" => "20/32", "DECIMAL" => "0.63", "LOGMAR" => "0.20"),
            10 => array("METRE" => "6/7.5", "FOOT" => "20/25", "DECIMAL" => "0.80", "LOGMAR" => "0.10"),
            11 => array("METRE" => "6/6.0", "FOOT" => "20/20", "DECIMAL" => "1.00", "LOGMAR" => "0.00"),
            12 => array("METRE" => "6/4.8", "FOOT" => "20/16", "DECIMAL" => "1.25", "LOGMAR" => "-0.10"),
            13 => array("METRE" => "6/3.8", "FOOT" => "20/12.5", "DECIMAL" => "1.60", "LOGMAR" => "-0.20"),
            15 => array("METRE" => "6/3.0", "FOOT" => "20/10", "DECIMAL" => "2.00", "LOGMAR" => "-0.30")
        );

        return $data;
    }

    public static function listHealthValuesOfEye($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";

        $facette = self::findStudentHealth($setId, $studentId, false);

        $data = array();
        $i = 0;
        if (self::getEyeData()) {
            foreach (self::getEyeData() as $key => $value) {
                $data[$i]["ID"] = $key;
                $data[$i]["EYE_LEFT"] = ($key == $facette->EYE_LEFT) ? 1 : 0;
                $data[$i]["EYE_RIGHT"] = ($key == $facette->EYE_RIGHT) ? 1 : 0;
                $data[$i]["FOOT"] = $value["FOOT"];
                $data[$i]["METRE"] = $value["METRE"];
                $data[$i]["DECIMAL"] = $value["DECIMAL"];
                $data[$i]["LOGMAR"] = $value["LOGMAR"];
                $i++;
            }
        }

        return array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $data
        );
    }

    public static function createStudentHealthItem($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";

        self::dbAccess()->delete('t_student_medical_item', array(
            "STUDENT_ID='" . $objectId . "'"
            , "MEDICAL_ID='" . $setId . "'"
        ));

        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        //error_log($SQL);
        $result = self::dbAccess()->fetchAll($SQL);

        $SAVEDATA['MEDICAL_ID'] = $setId;
        $SAVEDATA['STUDENT_ID'] = $objectId;

        if ($result) {
            foreach ($result as $value) {

                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? $params["CHECKBOX_" . $value->ID . ""] : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->PARENT . ""]) ? $params["RADIOBOX_" . $value->PARENT . ""] : "";
                $INPUTFIELD = isset($params["INPUTFIELD_" . $value->ID . ""]) ? $params["INPUTFIELD_" . $value->ID . ""] : "";
                $TEXTAREA = isset($params["TEXTAREA_" . $value->ID . ""]) ? $params["TEXTAREA_" . $value->ID . ""] : "";
                $DATE = isset($params["DATE_" . $value->ID . ""]) ? $params["DATE_" . $value->ID . ""] : "";

                $SAVEDATA['FIELD_DESCRIPTION'] = '';
                $SAVEDATA['FIELD_DATE'] = '';

                switch ($value->FIELD_TYPE) {
                    case 1:
                        if ($CHECKBOX) {
                            $SAVEDATA['ITEM'] = $value->ID;
                            self::dbAccess()->insert('t_student_medical_item', $SAVEDATA);
                        }
                        break;

                    case 2:
                        if ($RADIOBOX) {
                            $SAVEDATA['ITEM'] = $RADIOBOX;
                            self::dbAccess()->insert('t_student_medical_item', $SAVEDATA);
                        }
                        break;

                    case 3:
                        if ($INPUTFIELD) {
                            $SAVEDATA['FIELD_DESCRIPTION'] = $INPUTFIELD;
                            self::dbAccess()->insert('t_student_medical_item', $SAVEDATA);
                        }
                        break;

                    case 4:
                        if ($TEXTAREA) {
                            $SAVEDATA['FIELD_DESCRIPTION'] = $TEXTAREA;
                            self::dbAccess()->insert('t_student_medical_item', $SAVEDATA);
                        }
                        break;
                    case 5:
                        if ($DATE) {
                            $SAVEDATA['DATE_FIELD'] = $DATE;
                            self::dbAccess()->insert('t_student_medical_item', $SAVEDATA);
                        }
                        break;
                }
            }
        }
    }

    public static function createStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $target = isset($params["target"]) ? addText($params["target"]) : "DYNAMIC";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";

        $SAVEDATA['OBJECT_TYPE'] = $target;

        if (isset($params["DESCRIPTION"])) {
            $SAVEDATA['DESCRIPTION'] = addText($params["DESCRIPTION"]);
        }

        if (isset($params["WEIGHT"])) {
            $SAVEDATA['WEIGHT'] = addText($params["WEIGHT"]);
        }

        if (isset($params["HEIGHT"])) {
            $SAVEDATA['HEIGHT'] = addText($params["HEIGHT"]);
        }

        if (isset($params["PULSE"])) {
            $SAVEDATA['PULSE'] = addText($params["PULSE"]);
        }

        if (isset($params["BLOOD_PRESSURE"])) {
            $SAVEDATA['BLOOD_PRESSURE'] = addText($params["BLOOD_PRESSURE"]);
        }

        if (isset($params["DOCTOR_NAME"])) {
            $SAVEDATA['DOCTOR_NAME'] = addText($params["DOCTOR_NAME"]);
        }

        if (isset($params["REASON"])) {
            $SAVEDATA['REASON'] = addText($params["REASON"]);
        }

        if (isset($params["DOCTOR_COMMENT"])) {
            $SAVEDATA['DOCTOR_COMMENT'] = addText($params["DOCTOR_COMMENT"]);
        }

        if (isset($params["NEXT_VISIT"])) {
            $SAVEDATA['NEXT_VISIT'] = setDate2DB($params["NEXT_VISIT"]);
        }

        if (isset($params["MEDICAL_DATE"])) {
            $SAVEDATA['MEDICAL_DATE'] = setDate2DB($params["MEDICAL_DATE"]);
        }

        if ($setId == "new") {
            $SAVEDATA['MEDICAL_SETTING_ID'] = $settingId;
            $SAVEDATA['STUDENT_ID'] = $objectId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_student_medical', $SAVEDATA);
            $setId = self::dbAccess()->lastInsertId();
        } else {
            $WHERE[] = "ID = '" . $setId . "'";
            self::dbAccess()->update('t_student_medical', $SAVEDATA, $WHERE);
        }

        $facette = self::findStudentHealth($setId, $objectId, false);

        if ($facette) {
            self::calculationBMI($facette->ID);
        }

        if ($settingId)
            self::createStudentHealthItem($params);
        return array(
            "success" => true
            , "setId" => $setId
        );
    }

    public static function deleteStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";

        self::dbAccess()->delete('t_student_medical', array(
            "ID='" . $setId . "'"
            , "STUDENT_ID='" . $objectId . "'"
            , "MEDICAL_SETTING_ID='" . $settingId . "'"
        ));

        self::dbAccess()->delete('t_student_medical_item', array(
            "STUDENT_ID='" . $objectId . "'"
            , "MEDICAL_ID='" . $settingId . "'"
        ));

        return array(
            "success" => true
        );
    }

    public static function findStudentHealth($Id, $studentId = false, $settingId = false) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_medical", array('*'));
        $SQL->where("ID='" . $Id . "'");
        if ($settingId)
            $SQL->where("MEDICAL_SETTING_ID='" . $settingId . "'");
        if ($studentId)
            $SQL->where("STUDENT_ID='" . $studentId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //@veasna
    public static function sqlStudentHealth($params) {

        $studentId = isset($params['studentId']) ? $params['studentId'] : "";
        $code = isset($params["CODE"]) ? $params["CODE"] : "";
        $schoolCode = isset($params["STUDENT_SCHOOL_ID"]) ? $params["STUDENT_SCHOOL_ID"] : "";
        $firstname = isset($params["FIRSTNAME"]) ? $params["FIRSTNAME"] : "";
        $lastname = isset($params["LASTNAME"]) ? $params["LASTNAME"] : "";
        $gender = isset($params["GENDER"]) ? $params["GENDER"] : "";
        $religion = isset($params["RELIGION"]) ? $params["RELIGION"] : "";
        $ethnic = isset($params["ETHNIC"]) ? $params["ETHNIC"] : "";
        $nationality = isset($params["NATIONALITY"]) ? $params["NATIONALITY"] : "";
        $healthItems = isset($params["healthItems"]) ? $params["healthItems"] : "";
        $startDate = isset($params["START_DATE"]) ? setDate2DB($params["START_DATE"]) : "";
        $endDate = isset($params["END_DATE"]) ? setDate2DB($params["END_DATE"]) : "";
        $nexVisitDate = isset($params["NEXT_VISIT"]) ? setDate2DB($params["NEXT_VISIT"]) : "";
        $WEIGHT = isset($params["WEIGHT"]) ? $params["WEIGHT"] : "";
        $HEIGHT = isset($params["HEIGHT"]) ? $params["HEIGHT"] : "";
        $PULSE = isset($params["PULSE"]) ? $params["PULSE"] : "";
        $BLOOD_PRESSURE = isset($params["BLOOD_PRESSURE"]) ? $params["BLOOD_PRESSURE"] : "";
        $DOCTOR_NAME = isset($params["DOCTOR_NAME"]) ? $params["DOCTOR_NAME"] : "";
        $BMI = isset($params["BMI"]) ? $params["BMI"] : "";
        $EYE_LEFT = isset($params["EYE_LEFT"]) ? $params["EYE_LEFT"] : "";
        $EYE_RIGHT = isset($params["EYE_RIGHT"]) ? $params["EYE_RIGHT"] : "";
        $health_type = isset($params["health_type"]) ? $params["health_type"] : "";
        
        $BMI_STATUS = isset($params["BMI_STATUS"]) ? $params["BMI_STATUS"] : "";

        $SELECTION_C = array(
            "ID AS STUDENT_ID"
            , "CODE AS CODE"
            , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
            , "ACADEMIC_TYPE AS ACADEMIC_TYPE"
            , "CONCAT(LASTNAME,', ',FIRSTNAME) AS NAME"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
            , "LASTNAME_LATIN AS LASTNAME_LATIN"
            , "CONCAT(LASTNAME_LATIN,', ',FIRSTNAME_LATIN) AS FULLNAME_LATIN"
            , "ADDRESS AS ADDRESS"
            , "NATIONALITY AS NATIONALITY"
            , "ETHNIC AS ETHNIC"
            , "MINORITY AS MINORITY"
            , "RELIGION AS RELIGION"
            , "GENDER AS GENDER"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_medical'), array('*'));
        $SQL->joinLeft(array('B' => 't_student_medical_item'), 'A.ID=B.MEDICAL_ID', array("ITEM", "FIELD_DESCRIPTION", "FIELD_DATE"));
        $SQL->joinLeft(array('C' => 't_student'), 'C.ID=A.STUDENT_ID', $SELECTION_C);

        if ($studentId)
            $SQL->where("A.STUDENT_ID='" . $studentId . "'");

        if ($startDate && $endDate) {
            $SQL->where("A.MEDICAL_DATE >='" . $startDate . "' AND A.MEDICAL_DATE <='" . $endDate . "'");
        }

        if ($BMI_STATUS) {
            $SQL->where("A.STATUS = '" . $BMI_STATUS . "'");
        }

        if ($nexVisitDate)
            $SQL->where("A.NEXT_VISIT = '" . $nexVisitDate . "'");

        if ($WEIGHT)
            $SQL->where("A.WEIGHT = '" . $WEIGHT . "'");

        if ($HEIGHT)
            $SQL->where("A.HEIGHT = '" . $HEIGHT . "'");

        if ($PULSE)
            $SQL->where("A.PULSE LIKE '" . $PULSE . "%'");

        if ($BLOOD_PRESSURE)
            $SQL->where("A.BLOOD_PRESSURE LIKE '" . $BLOOD_PRESSURE . "%'");

        if ($DOCTOR_NAME)
            $SQL->where("A.DOCTOR_NAME LIKE '" . $DOCTOR_NAME . "%'");

        if ($BMI)
            $SQL->where("A.BMI = '" . $BMI . "'");

        if ($EYE_LEFT)
            $SQL->where("A.EYE_LEFT = '" . $EYE_LEFT . "'");

        if ($EYE_RIGHT)
            $SQL->where("A.EYE_RIGHT = '" . $EYE_RIGHT . "'");

        if ($health_type) {
            switch ($health_type) {
                case 'GROWTH_CHART':
                case 'MEDICAL_VISIT':
                case 'BMI':
                    $SQL->where("A.OBJECT_TYPE = '" . $health_type . "'");
                    break;
                default:
                    $SQL->where("A.MEDICAL_SETTING_ID = '" . $health_type . "'");
                    break;
            }
        }

        if ($healthItems)
            $SQL->where("B.ITEM IN (" . $healthItems . ")");

        if ($code)
            $SQL->where("C.CODE LIKE '" . $code . "%'");
        if ($schoolCode)
            $SQL->where("C.STUDENT_SCHOOL_ID LIKE '" . $code . "%'");
        if ($firstname)
            $SQL->where("C.FIRSTNAME LIKE '" . $firstname . "%'");
        if ($lastname)
            $SQL->where("C.LASTNAME LIKE '" . $lastname . "%'");
        if ($gender)
            $SQL->where("C.GENDER ='" . $gender . "'");
        if ($religion)
            $SQL->where("C.RELIGION='" . $religion . "'");
        if ($ethnic)
            $SQL->where("C.ETHNIC='" . $ethnic . "'");
        if ($nationality)
            $SQL->where("C.NATIONALITY='" . $nationality . "'");

        if(is_numeric($health_type)){
            $SQL->group("B.MEDICAL_ID");
        }
        
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function searchStudentHealth($encrypParams, $isJson = true) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";
        $health_type = isset($params["health_type"]) ? $params["health_type"] : "";
        $facette = "";
        if ($health_type) {
            switch ($health_type) {
                case 'GROWTH_CHART':
                case 'MEDICAL_VISIT':
                case 'BMI':
                    $facette = "";
                    break;
                default:
                    $facette = HealthSettingDBAccess::sqlHealthSetting($health_type, false);
                    break;
            }
        }

        $healthItems = array();
        if ($facette) {
            foreach ($facette as $item) {
                $entries = HealthSettingDBAccess::sqlHealthSetting($item->ID, false);
                foreach ($entries as $value) {
                    switch ($value->FIELD_TYPE) {
                        case 1:
                            if (isset($params["CHECKBOX_" . $value->ID]))
                                $healthItems[] = $params["CHECKBOX_" . $value->ID];
                            break;
                        case 2:
                            if (isset($params["RADIOBOX_" . $item->ID]))
                                $healthItems[] = $params["RADIOBOX_" . $item->ID];
                            break;
                        case 3:
                            //$data[] = "{xtype: 'textfield',id: 'INPUTFIELD_" . $value->ID . "'" . ",fieldLabel: '" . setShowText($name) . "',width:" . $width . ",name: 'INPUTFIELD_" . $value->ID . "'}";
                            break;
                        case 4:
                            //$data[] = "{xtype: 'textarea',id: 'TEXTAREA_" . $value->ID . "'" . ",fieldLabel: '" . setShowText($name) . "',width:" . $width . ",name: 'TEXTAREA_" . $value->ID . "'}";
                            break;
                        case 5:
                            //$data[] = "{xtype: 'datefield',id: 'DATEFIELD_" . $value->ID . "'" . ",fieldLabel: '" . setShowText($name) . "',width:" . $width . ",name: 'DATEFIELD_" . $value->ID . "'}";
                            break;
                    }
                }
            }
        }
        if ($healthItems) {
            $params['healthItems'] = implode(',', $healthItems);
        }
        //error_log(implode(',',$healthItems));
        $result = self::sqlStudentHealth($params);
        $data = array();
        $i = 0;
        foreach ($result as $value) {

            $data[$i]["CODE"] = $value->CODE;
            $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
            $data[$i]["ID"] = $value->ID;
            $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;
            $data[$i]["FULLNAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
            if (!SchoolDBAccess::displayPersonNameInGrid()) {
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
            } else {
                $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
            }
            $data[$i]["FIRSTNAME"] = setShowText($value->FIRSTNAME);
            $data[$i]["LASTNAME"] = setShowText($value->LASTNAME);
            if (!SchoolDBAccess::displayPersonNameInGrid()) {
                $data[$i]["FULL_NAME"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
            } else {
                $data[$i]["FULL_NAME"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
            }
            $data[$i]["FIRSTNAME_LATIN"] = setShowText($value->FIRSTNAME_LATIN);
            $data[$i]["LASTNAME_LATIN"] = setShowText($value->LASTNAME_LATIN);
            $data[$i]["FULLNAME_LATIN"] = setShowText($value->FULLNAME_LATIN);
            $data[$i]["NATIONALITY"] = setShowText($value->NATIONALITY);
            $data[$i]["RELIGION"] = $value->RELIGION;
            $data[$i]["ETHNIC"] = $value->ETHNIC;
            $data[$i]["ETHNICITY"] = $value->ETHNIC;
            $data[$i]["GENDER"] = getGenderName($value->GENDER);
            $STATUS_DATA = StudentStatusDBAccess::getCurrentStudentStatus($value->STUDENT_ID);
            $data[$i]["STATUS_KEY"] = isset($STATUS_DATA["SHORT"]) ? $STATUS_DATA["SHORT"] : "";
            $data[$i]["BG_COLOR"] = isset($STATUS_DATA["COLOR"]) ? $STATUS_DATA["COLOR"] : "";
            $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
            $data[$i]["BG_COLOR_FONT"] = isset($STATUS_DATA["COLOR_FONT"]) ? $STATUS_DATA["COLOR_FONT"] : "";
            //growth chart
            $data[$i]["WEIGHT"] = $value->WEIGHT ? $value->WEIGHT : '---';
            $data[$i]["HEIGHT"] = $value->HEIGHT ? $value->HEIGHT : '---';
            $data[$i]["PULSE"] = $value->PULSE ? $value->PULSE : '---';
            $data[$i]["BLOOD_PRESSURE"] = $value->BLOOD_PRESSURE ? $value->BLOOD_PRESSURE : '---';

            //Medical visit
            $data[$i]["DOCTOR_NAME"] = $value->DOCTOR_NAME ? $value->DOCTOR_NAME : '---';
            $data[$i]["REASON"] = $value->REASON ? $value->REASON : '---';
            $data[$i]["DOCTOR_COMMENT"] = $value->DOCTOR_COMMENT ? $value->DOCTOR_COMMENT : '---';
            $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT);

            //BMI
            $data[$i]["BMI"] = $value->BMI ? $value->BMI : '---';
            $data[$i]["STATUS"] = self::showBMIStatus($value->STATUS);
            $data[$i]["MEDICAL_DATE"] = getShowDate($value->MEDICAL_DATE);
            $data[$i]["DESCRIPTION"] = $value->DESCRIPTION;

            $data[$i]["MEDICAL_SETTING_ID"] = $value->MEDICAL_SETTING_ID;
            $i++;
        }
        $a = array();
        for ($i = $start; $i < $start + $limit; $i++) {
            if (isset($data[$i]))
                $a[] = $data[$i];
        }

        $dataforjson = array(
            "success" => true
            , "totalCount" => sizeof($data)
            , "rows" => $a
        );

        if ($isJson) {
            return $dataforjson;
        } else {
            return $data;
        }
    }

    public static function loadStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";

        $result = self::findStudentHealth($setId, $objectId, $settingId);

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_medical_item", array('*'));
        $SQL->where("MEDICAL_ID='" . $setId . "'");
        $SQL->where("STUDENT_ID='" . $objectId . "'");
        //error_log($SQL->__toString());
        $entries = self::dbAccess()->fetchAll($SQL);

        if ($result) {

            $data["MEDICAL_DATE"] = getShowDate($result->MEDICAL_DATE);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["BMI"] = setShowText($result->BMI);
            $data["WEIGHT"] = setShowText($result->WEIGHT);
            $data["HEIGHT"] = setShowText($result->HEIGHT);
            $data["PULSE"] = setShowText($result->PULSE);
            $data["BLOOD_PRESSURE"] = setShowText($result->BLOOD_PRESSURE);
            $data["DOCTOR_NAME"] = setShowText($result->DOCTOR_NAME);
            $data["REASON"] = setShowText($result->REASON);
            $data["DOCTOR_COMMENT"] = setShowText($result->DOCTOR_COMMENT);
            $data["NEXT_VISIT"] = getShowDate($result->NEXT_VISIT);

            foreach ($entries as $value) {

                $facette = self::getHealthSetting($value->ITEM);

                if ($facette) {
                    switch ($facette->FIELD_TYPE) {
                        case 1:
                            $data["CHECKBOX_" . $facette->ID] = true;
                            break;
                        case 2:
                            $data["RADIOBOX_" . $facette->PARENT] = $facette->ID;
                            break;
                        case 3:
                            $data["INPUTFIELD_" . $facette->ID] = $value->FIELD_DESCRIPTION;
                            break;
                        case 4:
                            $data["TEXTAREA_" . $facette->ID] = $value->FIELD_DESCRIPTION;
                            break;
                        case 5:
                            $data["DATE_" . $facette->ID] = $value->FIELD_DATE;
                            break;
                    }
                }
            }

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

    public static function listStudentHealth($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $settingId = isset($params["settingId"]) ? addText($params["settingId"]) : "";
        $target = isset($params["target"]) ? addText($params["target"]) : "";

        $data = array();
        $i = 0;

        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_medical", array('*'));

        switch ($target) {
            case "BMI":
            case "GROWTH_CHART":
            case "MEDICAL_VISIT":
                $SQL->where("OBJECT_TYPE='" . $target . "'");
                break;
            default:
                if ($settingId)
                    $SQL->where("MEDICAL_SETTING_ID='" . $settingId . "'");
                break;
        }

        if ($objectId)
            $SQL->where("STUDENT_ID='" . $objectId . "'");
        //error_log($SQL->__toString());
        $result = self::dbAccess()->fetchAll($SQL);

        if ($result) {
            foreach ($result as $value) {
                $data[$i]["ID"] = $value->ID;
                $data[$i]["BMI"] = setShowText($value->BMI);
                $data[$i]["STATUS"] = self::showBMIStatus($value->STATUS);
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["MEDICAL_DATE"] = getShowDate($value->MEDICAL_DATE);
                $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                $data[$i]["PULSE"] = setShowText($value->PULSE);
                $data[$i]["BLOOD_PRESSURE"] = setShowText($value->BLOOD_PRESSURE);
                $data[$i]["DOCTOR_NAME"] = setShowText($value->DOCTOR_NAME);
                $data[$i]["PLACE"] = setShowText($value->PLACE);
                $data[$i]["REASON"] = setShowText($value->REASON);
                $data[$i]["DOCTOR_COMMENT"] = setShowText($value->DOCTOR_COMMENT);
                $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

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

    public static function calculationBMI($Id) {

        $facette = self::findStudentHealth($Id, false, false);

        /**
         * Example: Weight = 68 kg, Height = 165 cm (1.65 m)
          Calculation: 68 ÷ (1.65)2 = 24.98
         */
        /*
         * Example: Weight = 150 lbs, Height = 5'5" (65")
          Calculation: [150 ÷ (65)2] x 703 = 24.96
         */
        $value = "";
        $HEALTH_BMI_STANDARD = Zend_Registry::get('SCHOOL')->HEALTH_BMI_STANDARD;
        $STANDARD = $HEALTH_BMI_STANDARD ? $HEALTH_BMI_STANDARD : 1;
        if ($facette) {
            switch ($STANDARD) {
                case 1:
                    if (is_numeric($facette->HEIGHT) && is_numeric($facette->WEIGHT)) {
                        $value = round($facette->WEIGHT / pow($facette->HEIGHT / 100, 2), 2);
                    }
                    break;
                case 2:
                    if (is_numeric($facette->HEIGHT) && is_numeric($facette->WEIGHT)) {
                        $value = round((($facette->WEIGHT) / pow($facette->HEIGHT * 2.54, 2)) * 703, 2);
                    }
                    break;
            }
            if ($value) {
                $SQL = "UPDATE t_student_medical";
                $SQL .= " SET";
                $SQL .= " BMI='" . $value . "'";
                $SQL .= " ,STATUS='" . self::calculationBMIStatus($value) . "'";
                $SQL .= " WHERE ID='" . $facette->ID . "'";
                self::dbAccess()->query($SQL);
            }
        }
    }

    public static function calculationBMIStatus($value) {
        /**
         * Underweight = <18.5
          Normal weight = 18.5–24.9
          Overweight = 25–29.9
          Obesity = BMI of 30 or greater
         */
        $result = "";
        if ($value) {
            if ($value <= 18.49) {
                $result = 1;
            } elseif ($value >= 18.50 && $value <= 24.99) {
                $result = 2;
            } elseif ($value >= 25.00 && $value <= 29.99) {
                $result = 3;
            } elseif ($value >= 30.00) {
                $result = 4;
            }
        }

        return $result;
    }

    public static function showBMIStatus($value) {
        switch ($value) {
            case 1:
                $result = "Underweight";
                break;
            case 2:
                $result = "Normal weight";
                break;
            case 3:
                $result = "Overweight";
                break;
            case 4:
                $result = "Obesity";
                break;
            default:
                $result = "---";
                break;
        }

        return $result;
    }

    public static function getHealthSetting($Id) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        $SQL->where("ID='" . $Id . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function actionStudentHealthEyeInfo($encrypParams) {
        $params = Utiles::setPostDecrypteParams($encrypParams);
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";
        $field = isset($params["field"]) ? addText($params["field"]) : "";
        $rowValue = isset($params["id"]) ? addText($params["id"]) : "";

        $WHERE[] = "ID = '" . $setId . "'";
        switch ($field) {
            case "EYE_LEFT":
                $SAVEDATA["EYE_LEFT"] = addText($rowValue);
                break;
            case "EYE_RIGHT":
                $SAVEDATA["EYE_RIGHT"] = addText($rowValue);
                break;
        }
        self::dbAccess()->update('t_student_medical', $SAVEDATA, $WHERE);

        return array(
            "success" => true
        );
    }

}

?>