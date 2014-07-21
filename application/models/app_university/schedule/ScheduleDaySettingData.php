<?php

////////////////////////////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////
require_once 'models/app_university/schedule/SQLScheduleDaySetting.php';
require_once 'models/app_university/schedule/ScheduleDBAccess.php';

class ScheduleDaySettingData {

    public function __construct() {
        
    }

    public function dataScheduleDayTrainingSetting($params, $isJson = true) {

        $data = array();

        $start = isset($params["start"]) ? (int) $params["start"] : "0";
        $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
        $trainingId = isset($params["trainingId"]) ? $params["trainingId"] : "";
        $LINKED_SCHEDULE_CREDIT_DATA = array();
        $DISPLAY_SUBJECT = Zend_Registry::get('SCHOOL')->SUBJECT_DISPLAY ? "SUBJECT_SHORT" : "SUBJECT_NAME";
        $checkAcademicId = "";
        $chooseId = "TRAINING_ID";
        $type = "TRAINING";
        $i = 0;
        $groupDay = SQLScheduleDaySetting::getScheduleTrainingGroupDay($trainingId);
        if ($groupDay) {
            foreach ($groupDay as $duration) {

                $params['startDate'] = $duration->START_DATE;
                $params['endDate'] = $duration->END_DATE;
                $resultRows = ScheduleDBAccess::getSQLClassEvents($params);
                if ($resultRows) {
                    foreach ($resultRows as $value) {
                        $data[$i]["DURATION"] = getShowDate($duration->START_DATE) . " - " . getShowDate($duration->END_DATE);
                        $data[$i]["ID"] = $value->GUID;
                        $data[$i]["STATUS"] = $value->SCHEDULE_STATUS;
                        $data[$i]["CODE"] = setShowText($value->SCHEDULE_CODE);
                        $data[$i]["TIME"] = secondToHour($value->START_TIME) . " - " . secondToHour($value->END_TIME);
                        $data[$i]["EXTRA_START_DATE"] = secondToHour($value->START_TIME);
                        $data[$i]["EXTRA_END_DATE"] = secondToHour($value->END_TIME);

                        $MO_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "MO", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["MO"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["MO_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["MO_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["MO_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["MO_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["MO_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["MO_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $MO_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $TU_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "TU", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["TU"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TU_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TU_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TU_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TU_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TU_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TU_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $TU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $WE_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "WE", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["WE"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["WE_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["WE_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["WE_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["WE_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["WE_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["WE_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $WE_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $TH_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "TH", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["TH"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TH_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TH_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TH_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TH_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TH_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["TH_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $TH_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $FR_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "FR", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["FR"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["FR_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["FR_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["FR_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["FR_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["FR_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["FR_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $FR_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $SA_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "SA", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["SA"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SA_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SA_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SA_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SA_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SA_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SA_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $SA_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $SU_OBJECT = ScheduleDBAccess::loadSQLClassEvents($value->START_TIME, $value->END_TIME, "SU", $value->$chooseId, $value->TERM, true, $type, false, $duration->START_DATE, $duration->END_DATE);

                        $data[$i]["SU"] = ScheduleDBAccess::displayEvent("DAY_EVENT", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SU_GUID"] = ScheduleDBAccess::displayEvent("DAY_GUID", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SU_EVENT"] = ScheduleDBAccess::displayEvent("DAY_NAME_EVENT", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SU_COLOR"] = ScheduleDBAccess::displayEvent("DAY_COLOR", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SU_COLOR_FONT"] = ScheduleDBAccess::displayEvent("DAY_COLOR_FONT", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SU_DESCRIPTION"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);
                        $data[$i]["SU_DESCRIPTION_EX"] = ScheduleDBAccess::displayEvent("DAY_DESCRIPTION_EX", $checkAcademicId, $SU_OBJECT, $LINKED_SCHEDULE_CREDIT_DATA, $DISPLAY_SUBJECT);

                        $i++;
                    }
                }
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
                , "totalCount" => sizeof($data)
                , "rows" => $a
            );
        } else {
            return $data;
        }
    }

}

?>