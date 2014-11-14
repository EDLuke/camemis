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

    public static function getListEyeDataInfo() {
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

    public static function getEyeData($index) {

        $result = self::getListEyeDataInfo();
        $facette = isset($result[$index]) ? (object) $result[$index] : "";

        $output = "";
        if ($facette) {
            $output = "&bull; " . "Metre: " . $facette->METRE;
            $output .= "<br>&bull; " . "Foot: " . $facette->FOOT;
            $output .= "<br>&bull; " . "Decimal: " . $facette->DECIMAL;
            $output .= "<br>&bull; " . "LogMAR: " . $facette->LOGMAR;
        }

        return $output;
    }

    public static function listHealthValuesOfEye($encrypParams) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $studentId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
        $setId = isset($params["setId"]) ? addText($params["setId"]) : "";

        $facette = self::findStudentHealth($setId, $studentId, false);

        $data = array();
        $i = 0;
        if (self::getListEyeDataInfo()) {
            foreach (self::getListEyeDataInfo() as $key => $value) {
                $data[$i]["ID"] = $key;
                if ($facette)
                    $data[$i]["EYE_LEFT"] = ($key == $facette->EYE_LEFT) ? 1 : 0;
                if ($facette)
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

        if (isset($params["NEXT_VISIT_TIME"])) {
            $SAVEDATA['NEXT_VISIT_TIME'] = timeStrToSecond($params["NEXT_VISIT_TIME"]);
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

        if (isset($params["BLOOD_PRESSURE_SYSTOLIC"])) {
            $SAVEDATA['BLOOD_PRESSURE_SYSTOLIC'] = addText($params["BLOOD_PRESSURE_SYSTOLIC"]);
        }

        if (isset($params["BLOOD_PRESSURE_DIASTOLIC"])) {
            $SAVEDATA['BLOOD_PRESSURE_DIASTOLIC'] = addText($params["BLOOD_PRESSURE_DIASTOLIC"]);
        }

        if (isset($params["TEMPERATURE"])) {
            $SAVEDATA['TEMPERATURE'] = addText($params["TEMPERATURE"]);
        }
        	
   		if (isset($params["BMI_Z_SCORE"])) {
            $SAVEDATA['BMI_Z_SCORE'] = addText($params["BMI_Z_SCORE"]);
        }

        if (isset($params["WT_Z_SCORE"])) {
            $SAVEDATA['WT_Z_SCORE'] = addText($params["WT_Z_SCORE"]);
        }

        if (isset($params["HT_Z_SCORE"])) {
            $SAVEDATA['HT_Z_SCORE'] = addText($params["HT_Z_SCORE"]);
        }

        if (isset($params["LOCATION"])) {
            $SAVEDATA['LOCATION'] = addText($params["LOCATION"]);
        }

        if (isset($params["OTHER"])) {
            $SAVEDATA['OTHER'] = addText($params["OTHER"]);
        }

        if (isset($params["FULL_NAME"])) {
            $SAVEDATA['DOCTOR_NAME'] = addText($params["FULL_NAME"]);
        }

        if (isset($params["NEXT_VISIT"])) {
            if ($params["NEXT_VISIT"])
                $SAVEDATA['NEXT_VISIT'] = setDate2DB($params["NEXT_VISIT"]);
        }

        if (isset($params["MEDICAL_DATE"])) {
            $SAVEDATA['MEDICAL_DATE'] = setDate2DB($params["MEDICAL_DATE"]);
        }

        $SQL = self::dbAccess()->select();
        $SQL->from("t_health_setting", array('*'));
        //error_log($SQL);
        $entries = self::dbAccess()->fetchAll($SQL);

        $CHECKBOX_DATA = array();
        $RADIOBOX_DATA = array();

        if ($entries) {
            foreach ($entries as $value) {
                $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? addText($params["CHECKBOX_" . $value->ID . ""]) : "";
                $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? addText($params["RADIOBOX_" . $value->ID . ""]) : "";
                if ($CHECKBOX) {
                    $CHECKBOX_DATA[$value->ID] = $CHECKBOX;
                }
                if ($RADIOBOX) {
                    $RADIOBOX_DATA[$value->ID] = $RADIOBOX;
                }
            }
        }

        if ($CHECKBOX_DATA) {
            $SAVEDATA['CHECKBOX_DATA'] = implode(",", $CHECKBOX_DATA);
        }

        if ($RADIOBOX_DATA) {
            $SAVEDATA['RADIOBOX_DATA'] = implode(",", $RADIOBOX_DATA);
        }

        $SAVEDATA['DATA_ITEMS'] = implode(",", $RADIOBOX_DATA + $CHECKBOX_DATA);

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

        return array(
            "success" => true
        );
    }

    public static function findStudentHealth($Id, $studentId = false, $settingId = false) {
        $SQL = self::dbAccess()->select();
        $SQL->from("t_student_medical", array('*'));
        $SQL->where("ID = ?", $Id);
        if ($settingId)
            $SQL->where("MEDICAL_SETTING_ID='" . $settingId . "'");
        if ($studentId)
            $SQL->where("STUDENT_ID='" . $studentId . "'");
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    //@veasna
    public static function sqlStudentHealth($params) {

        $codeId = isset($params["codeId"]) ? addText($params["codeId"]) : "";
        $studentSchoolCode = isset($params["studentSchoolCode"]) ? addText($params["studentSchoolCode"]) : "";
        $firstName = isset($params["firstName"]) ? addText($params["firstName"]) : "";
        $lastName = isset($params["lastName"]) ? addText($params["lastName"]) : "";
        $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
        $bmiStatus = isset($params["bmiStatus"]) ? addText($params["bmiStatus"]) : "";
        $start = isset($params["start"]) ? $params["start"] : "";
        $end = isset($params["end"]) ? $params["end"] : "";
        $nextVisit = isset($params["nextVisit"]) ? $params["nextVisit"] : "";

        $health_type = isset($params["health_type"]) ? $params["health_type"] : "";

        $SELECTION_C = array(
            "ID AS STUDENT_ID"
            , "CODE AS CODE"
            , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
            , "ACADEMIC_TYPE AS ACADEMIC_TYPE"
            , "CONCAT(LASTNAME,', ',FIRSTNAME) AS NAME"
            , "FIRSTNAME AS FIRSTNAME"
            , "LASTNAME AS LASTNAME"
            , "GENDER AS GENDER"
        );

        $SQL = self::dbAccess()->select();
        $SQL->from(array('A' => 't_student_medical'), array('*'));
        $SQL->joinLeft(array('B' => 't_health_setting'), 'B.ID=A.MEDICAL_SETTING_ID', array());
        $SQL->joinLeft(array('C' => 't_student'), 'C.ID=A.STUDENT_ID', $SELECTION_C);

        if ($health_type)
            $SQL->where("B.OBJECT_INDEX = '" . $health_type . "'");
        if ($start && $end)
            $SQL->where("A.MEDICAL_DATE >='" . $start . "' AND A.MEDICAL_DATE <='" . $end . "'");
        if ($nextVisit)
            $SQL->where("A.START_DATE <= '" . $nextVisit . "' AND A.END_DATE >= '" . $nextVisit . "'");
        if ($bmiStatus)
            $SQL->where("A.STATUS = '" . $bmiStatus . "'");
        if ($codeId)
            $SQL->where("C.CODE LIKE '" . $codeId . "%'");
        if ($studentSchoolCode)
            $SQL->where("C.STUDENT_SCHOOL_ID LIKE '" . $studentSchoolCode . "%'");
        if ($firstName)
            $SQL->where("C.FIRSTNAME LIKE '" . $firstName . "%'");
        if ($lastName)
            $SQL->where("C.LASTNAME LIKE '" . $lastName . "%'");
        if ($gender)
            $SQL->where("C.GENDER ='" . $gender . "'");

        //error_log($SQL->__toString());
        return self::dbAccess()->fetchAll($SQL);
    }

    public static function searchStudentHealth($encrypParams, $isJson = true) {

        $params = Utiles::setPostDecrypteParams($encrypParams);
        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "100";
        $health_type = isset($params["health_type"]) ? $params["health_type"] : "";

        $result = self::sqlStudentHealth($params);
        $data = array();
        $i = 0;
        foreach ($result as $value) {

            $data[$i]["MEDICAL_DATE"] = getShowDate($value->MEDICAL_DATE);
            $data[$i]["CODE"] = $value->CODE;
            $data[$i]["STUDENT_ID"] = $value->STUDENT_ID;
            $data[$i]["ID"] = $value->ID;
            $data[$i]["STUDENT_SCHOOL_ID"] = $value->STUDENT_SCHOOL_ID;

            if (!SchoolDBAccess::displayPersonNameInGrid()) {
                $data[$i]["STUDENT"] = setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);
            } else {
                $data[$i]["STUDENT"] = setShowText($value->FIRSTNAME) . " " . setShowText($value->LASTNAME);
            }

            switch ($health_type) {
                case "DENTAL":
                    $data[$i]["EXAM_TYPE"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_EXAM_TYPE");
                    $data[$i]["FLUORIDE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_FLUORIDE_TREATMENT");
                    $data[$i]["X_RAYS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_X_RAYS");
                    $data[$i]["DENTAL_CARIES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_CARIES");
                    $data[$i]["TOOTH_NUMBER"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_TOOTH_NUMBER");
                    break;

                case "INJURY":
                    $data[$i]["LOCATION"] = setShowText($value->LOCATION);
                    $data[$i]["KIND_OF_INJURY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "KIND_OF_INJURY");
                    break;

                case "MEDICAL_VISIT":

                    if (getShowDate($value->NEXT_VISIT) != "---") {
                        $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT) . " " . secondToHour($value->NEXT_VISIT_TIME);
                    } else {
                        $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT);
                    }

                    $data[$i]["FULL_NAME"] = setShowText($value->DOCTOR_NAME);
                    $data[$i]["VISITED_BY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_BY");
                    $data[$i]["REASON"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_REASON");
                    $data[$i]["LOCATION"] = setShowText($value->LOCATION);
                    break;

                case "VACCINATION":
                    $data[$i]["TYPES_OF_VACCINES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "TYPES_OF_VACCINES");
                    break;

                case "VISION":
                    $data[$i]["OTHER"] = setShowText($value->OTHER);
                    $data[$i]["EYE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_TREATMENT");
                    $data[$i]["EYE_CHART"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_CHART");
                    $data[$i]["VALUES_OF_LEFT_EYE"] = self::getEyeData($value->EYE_LEFT);
                    $data[$i]["VALUES_OF_RIGHT_EYE"] = self::getEyeData($value->EYE_RIGHT);
                    break;

                case "VITAMIN":
                    $data[$i]["VND"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING");
                    $data[$i]["DP"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING_PILL");
                    $data[$i]["MMS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_MMS");
                    break;

                case "BMI":
                    $data[$i]["BMI"] = setShowText($value->BMI);
                    $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                    $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                    $data[$i]["STATUS"] = self::showBMIStatus($value->STATUS);
                    $data[$i]["BMI_Z_SCORE"] = setshowText($value->BMI_Z_SCORE);
                    $data[$i]["WT_Z_SCORE"] = setshowText($value->WT_Z_SCORE);
                    $data[$i]["HT_Z_SCORE"] = setshowText($value->HT_Z_SCORE);
                    break;

                case "GROWTH_CHART":
                    $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                    $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                    $data[$i]["PULSE"] = setShowText($value->PULSE);
                    $data[$i]["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($value->BLOOD_PRESSURE_SYSTOLIC);
                    $data[$i]["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($value->BLOOD_PRESSURE_DIASTOLIC);
                    $data[$i]["TEMPERATURE"] = setShowText($value->TEMPERATURE);
                    break;
            }

            $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
            $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

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

        $data = array();

        if ($result) {
            $data["MEDICAL_DATE"] = getShowDate($result->MEDICAL_DATE);
            $data["DESCRIPTION"] = setShowText($result->DESCRIPTION);
            $data["LOCATION"] = setShowText($result->LOCATION);
            $data["FULL_NAME"] = setShowText($result->DOCTOR_NAME);
            $data["DOCTOR_COMMENT"] = setShowText($result->DOCTOR_COMMENT);
            $data["OTHER"] = setShowText($result->OTHER);
            $data["WEIGHT"] = setShowText($result->WEIGHT);
            $data["HEIGHT"] = setShowText($result->HEIGHT);
            $data["PULSE"] = setShowText($result->PULSE);
            $data["NEXT_VISIT"] = getShowDate($result->NEXT_VISIT);
            $data["NEXT_VISIT_TIME"] = secondToHour($result->NEXT_VISIT_TIME);
            $data["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($result->BLOOD_PRESSURE_SYSTOLIC);
			$data["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($result->BLOOD_PRESSURE_DIASTOLIC);
			$data["TEMPERATURE"] = setShowText($result->TEMPERATURE);
			$data["BMI_Z_SCORE"] = setShowText($result->BMI_Z_SCORE);
			$data["WT_Z_SCORE"] = setShowText($result->WT_Z_SCORE);
			$data["HT_Z_SCORE"] = setShowText($result->HT_Z_SCORE);

            $LIST_CHECKBOX = explode(",", $result->CHECKBOX_DATA);
            if ($LIST_CHECKBOX) {
                foreach ($LIST_CHECKBOX as $value) {
                    $data["CHECKBOX_" . $value] = true;
                }
            }
            $LIST_RADIOBOX = explode(",", $result->RADIOBOX_DATA);
            if ($LIST_RADIOBOX) {
                foreach ($LIST_RADIOBOX as $value) {
                    $setting = HealthSettingDBAccess::findHealthSettingFromId($value);
                    $data["RADIOBOX_" . $setting->PARENT] = $value;
                }
            }
        }

        $o = array(
            "success" => true
            , "data" => $data
        );
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
                $data[$i]["DESCRIPTION"] = setShowText($value->DESCRIPTION);
                $data[$i]["MEDICAL_DATE"] = getShowDate($value->MEDICAL_DATE);
                $data[$i]["DOCTOR_COMMENT"] = setShowText($value->DOCTOR_COMMENT);
                $data[$i]["CREATED_DATE"] = getShowDateTime($value->CREATED_DATE);
                $data[$i]["CREATED_BY"] = setShowText($value->CREATED_BY);

                switch ($target) {
                    case "BMI":
                        $data[$i]["BMI"] = setShowText($value->BMI);
                        $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                        $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                        $data[$i]["STATUS"] = self::showBMIStatus($value->STATUS);
                        $data[$i]["BMI_Z_SCORE"] = setShowText($value->BMI_Z_SCORE);
                        $data[$i]["WT_Z_SCORE"] = setShowText($value->WT_Z_SCORE);
                        $data[$i]["HT_Z_SCORE"] = setShowText($value->HT_Z_SCORE);
                        break;

                    case "DENTAL":
                        $data[$i]["EXAM_TYPE"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_EXAM_TYPE");
                        $data[$i]["FLUORIDE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_FLUORIDE_TREATMENT");
                        $data[$i]["X_RAYS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_X_RAYS");
                        $data[$i]["DENTAL_CARIES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_CARIES");
                        $data[$i]["TOOTH_NUMBER"] = self::getStudentHealthSetting($value->DATA_ITEMS, "DENTAL_TOOTH_NUMBER");
                        break;
                    case "INJURY":
                        $data[$i]["KIND_OF_INJURY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "KIND_OF_INJURY");
                        break;
                    case "MEDICAL_VISIT":
                        if (getShowDate($value->NEXT_VISIT) != "---") {
                            $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT) . " " . secondToHour($value->NEXT_VISIT_TIME);
                        } else {
                            $data[$i]["NEXT_VISIT"] = getShowDate($value->NEXT_VISIT);
                        }
                        $data[$i]["FULL_NAME"] = setShowText($value->DOCTOR_NAME);
                        $data[$i]["VISITED_BY"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_BY");
                        $data[$i]["REASON"] = self::getStudentHealthSetting($value->DATA_ITEMS, "MEDICAL_VISIT_REASON");
                        $data[$i]["LOCATION"] = setShowText($value->LOCATION);
                        break;
                    case "VACCINATION":
                        $data[$i]["TYPES_OF_VACCINES"] = self::getStudentHealthSetting($value->DATA_ITEMS, "TYPES_OF_VACCINES");
                        break;
                    case "VISION":
                        $data[$i]["OTHER"] = setShowText($value->OTHER);
                        $data[$i]["EYE_TREATMENT"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_TREATMENT");
                        $data[$i]["EYE_CHART"] = self::getStudentHealthSetting($value->DATA_ITEMS, "EYE_CHART");
                        $data[$i]["VALUES_OF_LEFT_EYE"] = self::getEyeData($value->EYE_LEFT);
                        $data[$i]["VALUES_OF_RIGHT_EYE"] = self::getEyeData($value->EYE_RIGHT);
                        break;
                    case "VITAMIN":
                        $data[$i]["VND"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING");
                        $data[$i]["DP"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_DEWORMING_PILL");
                        $data[$i]["MMS"] = self::getStudentHealthSetting($value->DATA_ITEMS, "VITAMINS_MMS");
                        break;
                    case "GROWTH_CHART":
                        $data[$i]["WEIGHT"] = setShowText($value->WEIGHT);
                        $data[$i]["HEIGHT"] = setShowText($value->HEIGHT);
                        $data[$i]["PULSE"] = setShowText($value->PULSE);
                        $data[$i]["BLOOD_PRESSURE_SYSTOLIC"] = setShowText($value->BLOOD_PRESSURE_SYSTOLIC);
                        $data[$i]["BLOOD_PRESSURE_DIASTOLIC"] = setShowText($value->BLOOD_PRESSURE_DIASTOLIC);
                        $data[$i]["TEMPERATURE"] = setShowText($value->TEMPERATURE);
                        break;
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

        if ($facette) {
            switch (HealthSettingDBAccess::unitBMI()) {
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
                $data = array();
                $data['BMI']   = "'". $value ."'";
                $data['STATUS']= "'". self::calculationBMIStatus($value) ."'";
                self::dbAccess()->update("t_student_medical", $data, "ID='". $facette->ID ."'");
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
        $SQL->where("ID = ?", $Id);
        //error_log($SQL->__toString());
        return self::dbAccess()->fetchRow($SQL);
    }

    public static function getStudentHealthSetting($dataItems, $objectIndex) {

        $data = array();
        if ($dataItems) {
            $CHECK_DATA = explode(",", $dataItems);
            $entries = HealthSettingDBAccess::getListObjectIndizes($objectIndex);
            if ($entries) {
                foreach ($entries as $value) {
                    if (in_array($value->ID, $CHECK_DATA)) {
                        $data[] = "&bull; " . setShowText($value->NAME);
                    }
                }
            }
        }

        return implode("<br>", $data);
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