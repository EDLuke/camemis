<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once("Zend/Loader.php");

function setUserLoacalization() {

    $file = 'localization/Localization.php';

    return $file;
}

function getNumberFormat($value) {
    $locale = new Zend_Locale('de_AT');
    return Zend_Locale_Format::toInteger($value, array('locale' => $locale));
}

function createpassword() {
    $string1 = "aeiou";
    $string2 = "bcdfghklmnpqrstvwyz";
    $string3 = "123456789";

    $passwort = $string1{rand(0, strlen($string1))};
    $passwort .= $string2{rand(0, strlen($string2))};
    $passwort .= $string1{rand(0, strlen($string1))};
    $passwort .= $string1{rand(0, strlen($string1))};
    $passwort .= $string2{rand(0, strlen($string2))};
    $passwort .= $string1{rand(0, strlen($string1))};
    $passwort .= $string3{rand(0, strlen($string3))};
    $passwort .= $string3{rand(0, strlen($string3))};

    return $passwort;
}

function randomPrefix($length) {
    $random = "";
    srand((double) microtime() * 1000000);

    $data = "AbcDE123IJKLMN67QRSTUVWXYZ";
    $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
    $data .= "0FGH45OP89";

    for ($i = 0; $i < $length; $i++) {
        $random .= substr($data, (rand() % (strlen($data))), 1);
    }

    return $random;
}

function arrayMin(array $array) {
    sort($array, SORT_NUMERIC);
    $v = each($array);
    return $v['value'];
}

function arrayMax(array $array) {
    sort($array, SORT_NUMERIC);
    return end($array);
}

function getNumber($value) {

    if (Zend_Registry::get('SCHOOL')->DECIMAL_PLACES) {

        switch (Zend_Registry::get('SCHOOL')->DECIMAL_PLACES) {
            case 1:
                $result = substr($value, 0, -3);
                break;
            case 2:
                $result = substr($value, 0, -1);
                break;
            case 3:
                $result = $value;
                break;
        }
    } else {
        $result = isset($value) ? $value : 0;
    }

    return $result;
}

function getSystemDateFormat() {

    if (Zend_Registry::get('SCHOOL')->SYSTEM_DATE_FORMAT) {
        return Zend_Registry::get('SCHOOL')->SYSTEM_DATE_FORMAT;
    } else {
        return "dd.MM.yyyy";
    }
}

function setExtDatafieldFormat() {

    $format = "d.m.Y";

    switch (getSystemDateFormat()) {

        case "DD.MM.YYYY": $format = "d.m.Y";
            break;
        case "DD-MM-YYYY": $format = "d-m-Y";
            break;
        case "DD/MM/YYYY": $format = "d/m/Y";
            break;
        case "MM.DD.YYYY": $format = "m.d.Y";
            break;
        case "MM-DD-YYYY": $format = "m-d-Y";
            break;
        case "MM/DD/YYYY": $format = "m/d/Y";
            break;

        default: $format = "d.m.Y";
            break;
    }

    return $format;
}

function showNewlineText($value) {
    return preg_replace("#\r\n|\n|\r#", '<br />', $value);
}

function setLocalText($value) {
    $clear = "*";
    //$clear = "";
    return addslashes($value . $clear);
}

function camemisId() {

    return randomPrefix(35);
}

function generateGuid() {
    return randomPrefix(35);
}

function createCodeExam() {

    mt_srand((double) microtime() * 100000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = "_";
    $uuid1 = "&lt;"
            . substr($charid, 10, 2) . $hyphen
            . substr($charid, 13, 2)
            . "";

    $uuid2 = str_replace('&lt;', '', $uuid1);
    $uuid3 = str_replace('&gt;', '', $uuid2);

    return $uuid3;
}

function createCode() {

    mt_srand((double) microtime() * 100000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = "-";
    $uuid1 = "&lt;"
            . substr($charid, 10, 4) . $hyphen
            . substr($charid, 13, 4)
            . "";

    $uuid2 = str_replace('&lt;', '', $uuid1);
    $uuid3 = str_replace('&gt;', '', $uuid2);

    return $uuid3;
}

function sourceCode() {

    mt_srand((double) microtime() * 100000);
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = "-";
    $uuid1 = "&lt;"
            . substr($charid, 10, 5) . $hyphen
            . substr($charid, 13, 5)
            . "";

    $uuid2 = str_replace('&lt;', '', $uuid1);
    $uuid3 = str_replace('&gt;', '', $uuid2);

    return $uuid3;
}

function setDevision($a, $b) {
    if ($b > 0) {
        return $a / $b;
    } else {
        return $a;
    }
}

function getCurrentTimestamp() {

    $date = new Zend_Date();
    return $date->get(Zend_Date::TIMESTAMP);
}

function getDate2TSP($date) {
    if ($date) {
        list($y, $m, $d) = explode("-", $date);
        $Date = new Zend_Date(array('year' => $y, 'month' => $m, 'day' => $d));
        return $Date->get(Zend_Date::TIMESTAMP);
    }
}

////////////////////////////////////////////////////////////////////////////////
function getMonthFromDate($date) {
    if ($date) {
        $explode = explode("-", $date);
        return isset($explode[1]) ? $explode[1] : "";
    }
}

function getYearFromDate($date) {
    if ($date) {
        $explode = explode("-", $date);
        return isset($explode[0]) ? $explode[0] : "";
    }
}

function getMonthNameByNumber($value) {

    switch ($value) {
        case "1":
            return "JANUARY";
        case "2":
            return "FEBRUARY";
        case "3":
            return "MARCH";
        case "4":
            return "APRIL";
        case "5":
            return "MAY";
        case "6":
            return "JUNE";
        case "7":
            return "JULY";
        case "8":
            return "AUGUST";
        case "9":
            return "SEPTEMBER";
        case "10":
            return "OCTOBER";
        case "11":
            return "NOVEMBER";
        case "12":
            return "DECEMBER";
    }
}

function getMonthNrByName($value) {

    if (is_numeric($value)) {
        return $value;
    } else {
        switch (strtoupper($value)) {
            case "JANUARY":
                return 1;
            case "FEBRUARY":
                return 2;
            case "MARCH":
                return 3;
            case "APRIL":
                return 4;
            case "MAY":
                return 5;
            case "JUNE":
                return 6;
            case "JULY":
                return 7;
            case "AUGUST":
                return 8;
            case "SEPTEMBER":
                return 9;
            case "OCTOBER":
                return 10;
            case "NOVEMBER":
                return 11;
            case "DECEMBER":
                return 12;
        }
    }
}

//JULY_2013
function getMonthNumberFromMonthYear($str) {
    if ($str) {
        $explode = explode("_", $str);
        if (isset($explode[0])) {
            switch ($explode[0]) {
                case "JANUARY":
                    return '01';
                case "FEBRUARY":
                    return '02';
                case "MARCH":
                    return '03';
                case "APRIL":
                    return '04';
                case "MAY":
                    return '05';
                case "JUNE":
                    return '06';
                case "JULY":
                    return '07';
                case "AUGUST":
                    return '08';
                case "SEPTEMBER":
                    return '9';
                case "OCTOBER":
                    return '10';
                case "NOVEMBER":
                    return '11';
                case "DECEMBER":
                    return '12';
            }
        }
    }
}

function getYearFromMonthYear($str) {
    if ($str) {
        $explode = explode("_", $str);
        return isset($explode[1]) ? $explode[1] : "";
    }
}

////////////////////////////////////////////////////////////////////////////////
function getCurrentDBDate() {

    $date = new Zend_Date();
    return $date->toString('YYYY-MM-dd');
}

function getCurrentYear() {

    $date = new Zend_Date();
    return $date->toString('YYYY');
}

function showCurrentDBDate() {

    $date = date('d.m.Y');
    return setDate2DB($date);
}

function showCurrentDate() {

    $date = date('d.m.Y H:i:s');
    return $date->toString(getSystemDateFormat());
}

function getCurrentDBDateTime() {

    $date = new Zend_Date();
    return $date->toString('YYYY-MM-dd HH:mm:ss');
}

function getCurrentDBDateTimeActiveStatus() {

    $date = new Zend_Date();
    return $date->toString('MM/dd/YYYY');
}

//@Sea Peng 14.11.2013
function getCurrentShortDay() {
    return getWEEKDAY(getCurrentDBDateTime());
}

function getCurrentTime() {
    $date = date("H:i");
    return $date;
}

function firstUppercase($text) {
    return ucfirst(strtolower($text));
}

function setDate2DB($date) {

    switch (strtoupper(getSystemDateFormat())) {

        case "DD.MM.YYYY":
            if (strpos($date, ".") > 0)
                list($d, $m, $y) = explode(".", $date);
            break;
        case "DD-MM-YYYY":
            if (strpos($date, "-") > 0)
                list($d, $m, $y) = explode("-", $date);
            break;
        case "DD/MM/YYYY":
            if (strpos($date, "/") > 0)
                list($d, $m, $y) = explode("/", $date);
            break;
        case "MM.DD.YYYY":
            if (strpos($date, ".") > 0)
                list($m, $d, $y) = explode(".", $date);
            break;
        case "MM-DD-YYYY":
            if (strpos($date, "-") > 0)
                list($m, $d, $y) = explode("-", $date);
            break;
        case "MM/DD/YYYY":
            if (strpos($date, "/") > 0)
                list($m, $d, $y) = explode("/", $date);

            break;
    }
    if (isset($d) && isset($m) && isset($y)) {
        return $y . "-" . $m . "-" . $d;
    }
}

function setCalendarDate2DB($date) {
    if (strpos($date, "-") > 0)
        list($m, $d, $y) = explode("-", $date);
    if (isset($d) && isset($m) && isset($y)) {
        return $y . "-" . $m . "-" . $d;
    }
}

function getNumberYear($date) {
    $Date = new Zend_Date($date);
    return $Date->toString('Y');
}

function getNumberMonth($date) {
    $Date = new Zend_Date($date);
    return $Date->toString('M');
}

function setDatetime2DB($date) {

    if ($date) {
        if (Zend_Date::isDate($date)) {
            $Date = new Zend_Date($date);
            return $Date->toString('YYYY-MM-dd HH:mm:ss');
        }
    }
}

function getShowDateTime($datetime) {

    if ($datetime != "0000-00-00 00:00:00") {

        switch (getSystemDateFormat()) {
            case "DD.MM.YYYY": $format = "dd.MM.yyyy HH:mm:ss";
                break;
            case "DD-MM-YYYY": $format = "dd-MM-yyyy HH:mm:ss";
                break;
            case "DD/MM/YYYY": $format = "dd/MM/yyyy HH:mm:ss";
                break;
            case "MM.DD.YYYY": $format = "MM.dd.yyyy HH:mm:ss";
                break;
            case "MM-DD-YYYY": $format = "MM-dd-yyyy HH:mm:ss";
                break;
            case "MM/DD/YYYY": $format = "MM/dd/yyyy HH:mm:ss";
                break;
            default: $format = "dd.MM.yyyy HH:mm:ss";
                break;
        }
        $locale = new Zend_Locale('de_AT');
        $Date = new Zend_Date($datetime, false, $locale);
        return $Date->toString("" . $format);
    } else {
        return "---";
    }
}

function getShowDate($value) {

    if ($value) {
        if ($value != "0000-00-00") {
            switch (getSystemDateFormat()) {
                case "DD.MM.YYYY": $format = "dd.MM.yyyy";
                    break;
                case "DD-MM-YYYY": $format = "dd-MM-yyyy";
                    break;
                case "DD/MM/YYYY": $format = "dd/MM/yyyy";
                    break;
                case "MM.DD.YYYY": $format = "MM.dd.yyyy";
                    break;
                case "MM-DD-YYYY": $format = "MM-dd-yyyy";
                    break;
                case "MM/DD/YYYY": $format = "MM/dd/yyyy";
                    break;
                default: $format = "dd.MM.yyyy";
                    break;
            }
            $locale = new Zend_Locale('de_AT');
            $Date = new Zend_Date($value, false, $locale);
            return $Date->toString("" . $format);
        } else {
            return "---";
        }
    } else {
        return "---";
    }
}

function getShortdayName($value) {
    $name = "---";
    switch ($value) {
        case 'MO':
            $name = MONDAY;
            break;
        case 'TU':
            $name = TUESDAY;
            break;
        case 'WE':
            $name = WEDNESDAY;
            break;
        case 'TH':
            $name = THURSDAY;
            break;
        case 'FR':
            $name = FRIDAY;
            break;
        case 'SA':
            $name = SATURDAY;
            break;
        case 'SU':
            $name = SUNDAY;
            break;
    }

    return $name;
}

function getWEEKDAY($newDate) {

    //2013-06-04
    $date = new Zend_Date(strtotime($newDate));
    $WEEKDAY_DIGIT = $date->toString(Zend_Date::WEEKDAY_DIGIT);

    switch ($WEEKDAY_DIGIT) {
        case '1':
            $shortDay = "MO";
            break;
        case '2':
            $shortDay = "TU";
            break;
        case '3':
            $shortDay = "WE";
            break;
        case '4':
            $shortDay = "TH";
            break;
        case '5':
            $shortDay = "FR";
            break;
        case '6':
            $shortDay = "SA";
            break;
        case '0':
            $shortDay = "SU";
            break;
    }

    return $shortDay;
}

function getFeeType($value) {
    switch ($value) {
        case "SCHOOL": return setICONV(SCHOOL_ENROLLMENT_FEES);
        case "COURSE": return setICONV(COURSE_ENROLLMENT_FEES);
    }
}

function getScoreType($value) {
    switch ($value) {
        case 1: return setICONV(SCORE_ON_NUMBER);
        case 2: return setICONV(SCORE_ON_ALPHABET);
        case 0: return "?";
    }
}

function getSMSPriorityName($value) {
    switch ($value) {
        case 1: return setICONV(IMPORTANT);
        case 2: return setICONV(URGENT);
        case 3: return 'SOS';
        case 0: return setICONV(INFORMATION);
    }
}

function getVideoIcon() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/32/media_play.png' border='0'>";
}

function getCompletedIcon($value) {
    switch ($value) {
        case 1: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/green.png' border='0'>";
        case 0: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/red.png' border='0'>";
    }
}

function getTransferAssessmentIcon($value) {
    switch ($value) {
        case 1: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user1_into.png' border='0'>";
    }
}

function getSMSPriorityIcon($value) {
    switch ($value) {
        case 1: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/24/nav_plain_green.png' border='0'>";
        case 2: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/24/nav_plain_yellow.png' border='0'>";
        case 3: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/24/nav_plain_red.png' border='0'>";
        case 0: return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/24/nav_plain_blue.png' border='0'>";
    }
}

function getGenderName($value) {
    switch ($value) {
        case 1: return MALE;
        case 2: return FEMALE;
        case 0: return "---";
    }
}

function getStaffType($value) {
    switch ($value) {
        case 1: return INSTRUCTOR;
        case 2: return TEACHER;
    }
}

function displaySchoolTerm($value) {

    switch ($value) {
        case 'FIRST_SEMESTER': return setICONV(FIRST_SEMESTER);
        case 'SECOND_SEMESTER': return setICONV(SECOND_SEMESTER);
        case 'FIRST_TRIMESTER': return setICONV(FIRST_TRIMESTER);
        case 'SECOND_TRIMESTER': return setICONV(SECOND_TRIMESTER);
        case 'THIRD_TRIMESTER': return setICONV(THIRD_TRIMESTER);
        case 'FIRST_QUARTER': return setICONV(FIRST_QUARTER);
        case 'SECOND_QUARTER': return setICONV(SECOND_QUARTER);
        case 'THIRD_QUARTER': return setICONV(THIRD_QUARTER);
        case 'FOURT_QUARTER': return setICONV(FOURT_QUARTER);
        case 'FIRST_TERM': return setICONV(FIRST_TERM);
        case 'SECOND_TERM': return setICONV(SECOND_TERM);
        case 'THIRD_TERM': return setICONV(THIRD_TERM);
        case 'FOURT_TERM': return setICONV(FOURT_TERM);
        case 'FIFTH_TERM': return setICONV(FIFTH_TERM);
        case 'SIXTH_TERM': return setICONV(SIXTH_TERM);
    }
}

function getStatus($value) {

    switch ($value) {
        case 1: return ENABLED;
        case 0: return DISABLED;
    }
}

function getActive($value) {

    switch ($value) {

        case 1: return YES;
        case 0: return NO;
    }
}

function getJobType($value) {

    switch ($value) {

        case "1": return FULL_TIME_JOB;
        case "2": return PART_TIME_JOB;
        case "0": return "---";
    }
}

function getJobTypeICONV($value) {

    switch ($value) {

        case "1": return setICONV(FULL_TIME_JOB);
        case "2": return setICONV(PART_TIME_JOB);
        case "0": return "---";
    }
}

function getTermNumberShort($value) {

    switch ($value) {

        case "1": return TWO_SEMESTERS;
        case "2": return THREE_ITEMS;
        case "3": return FOUR_QUARTERS;
        case "4": return SIX_TERMS;
        case "5": return ONE_TERM;
        case "0": return "---";
    }
}

function getPriority($value) {
    switch ($value) {
        case "1": return NORMAL;
        case "2": return IMPORTANT;
        case "3": return WITH_CONFIRMATION;
        default: return "---";
    }
}

function getOnOff($value) {
    switch ($value) {
        case "0": return OFF;
        case "1": return ON;
    }
}

function dataIds($entries) {
    $data = array();
    if ($entries)
        foreach ($entries as $value) {
            $data[$value] = utf8_decode($value);
        }

    return $data;
}

function checkAvailable($all = array(), $selected = array()) {
    $tempdata = dataIds($selected);

    $data = array();

    if ($all)
        foreach ($all as $value) {
            if (!in_array($value["ID"], $selected)) {

                if (!in_array($value["ID"], $tempdata)) {
                    $data[$value["ID"]] = $value["ID"];
                }
            }
        }
    return $data;
}

function setAddition($value) {
    if ($value == "" || $value == "null") {
        return "&nbsp;";
    } else {
        return "&nbsp;<b>(" . $value . ")</b>";
    }
}

function setShowTextExtjs($text) {
    if (isUTF8($text)) {
        return $text ? htmlspecialchars_decode(addslashes(trim($text))) : "";
    } else {
        return "?";
    }
}

function setShowText($text) {
    if (isUTF8($text)) {
        return $text ? htmlspecialchars_decode(stripslashes(trim($text))) : "";
    } else {
        return "?";
    }
}

function addText($text) {
    return addslashes(htmlspecialchars($text));
}

function explodeData($explodestr) {

    $data = array();

    if ($explodestr) {
        $explode = explode(",", $explodestr);
        if ($explode)
            foreach ($explode as $value) {
                $data[$value] = $value;
            }
    }

    return $data;
}

function iconNoEntry() {

    return setICONV(NOT_ASSIGNED);
}

function iconTeachingSession() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/note_information.png' border='0'>";
}

function iconError() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/warning.png' border='0'>";
}

function iconOk() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/tick.png' border='0'>";
}

function iconKeyStop() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/key_stop.png' border='0'>";
}

function iconKeyAdd() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/key_add.png' border='0'>";
}

function iconNew() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/new.png' border='0'>";
}

function iconClock() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/clock_break.png' border='0'>";
}

function iconNoWorkingDay() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/noworkingday.png' border='0'>";
}

function iconRuby() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/ruby.png' border='0'>";
}

function iconSubstitute() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/substitute.png' border='0'>";
}

function iconStar() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/star.png' border='0'>";
}

function iconAddItem() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/add.png' border='0'>";
}

function iconCardUserInfo() {
    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/card_user_info.png' border='0'>";
}

function iconComment($v) {
    return $v ? "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/comment.png' border='0'>" : "";
}

function iconFiletype($extension) {
    switch ($extension) {
        case 1 :
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/image.png' border='0'>";

        case 2 :
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/page_word.png' border='0'>";

        case 3 :
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/page_excel.png' border='0'>";

        case 4 :
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/page_white_powerpoint.png' border='0'>";

        case 5 :
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/pdf.png' border='0'>";

        case 6:
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/film.png' border='0'>";

        default:
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/page.png' border='0'>";
    }
}

function iconReplyStatus($status) {

    if ($status == 1)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/email_edit.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/email.png' border='0'>";
}

function iconIncludeValuation($status) {

    if ($status == 1)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/accept.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/cross.png' border='0'>";
}

function iconStatus($status) {

    if ($status == 1)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/green.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/red.png' border='0'>";
}

function iconWarning() {

    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/warning.png' border='0'>";
}

function iconImportStatus($status) {

    if ($status >= 1)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/warning.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/tick.png' border='0'>";
}

function iconRemoveStatus($status) {

    if ($status >= 1)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/link.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/link_delete.png' border='0'>";
}

function iconSMSServices($status) {

    if ($status > 0)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/tick.png' border='0'>";
    else
        return "---";
}

function iconInSchedule($status) {

    if ($status == 0)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/date_edit.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/date_link.png' border='0'>";
}

function iconAssignedStatus($status) {

    if ($status == 0)
        return "---";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/accept.png' border='0'>";
}

function iconTeacherInClassSchedule($value) {

    switch ($value) {
        case 1:
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user_go.png' border='0'>";

        case 2:
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user_add.png' border='0'>";

        case 3:
            return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user_cross.png' border='0'>";
    }
}

function iconScheduleEvent() {

    return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/date-time.png' border='0'>";
}

function iconYESNO($status) {

    if ($status > 0)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/tick.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/cross.png' border='0'>";
}

function iconHasSubstitute($status) {

    if ($status > 0)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/tick.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/cross.png' border='0'>";
}

function iconTeacherInClass($status) {

    if ($status > 0)
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user.png' border='0'>";
    else
        return "<img src='" . Zend_Registry::get('CAMEMIS_URL') . "/public/images/user_cross.png' border='0'>";
}

function callPercent($pts_possible, $pts_received) {
    $percent = 0;
    if ($pts_received)
        $percent = round((100 / $pts_possible) * $pts_received, 2);
    return $percent;
}

function timeStrToSecond($timeStr) {
    // time string: 7:45
    $result = 0;
    if ($timeStr != "") {
        $timeArr = explode(":", $timeStr);

        if ($timeArr) {
            $HOUR = $timeArr[0];
            if (isset($timeArr[1])) {
                $MINUTES = substr($timeArr[1], 0, 2);
                $SECOND = 0;
                $result = $HOUR * 60 * 60 + $MINUTES * 60 + $SECOND;
            }
        }
    }
    return $result;
}

function secondToHour($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds - ($hours * 3600)) / 60);
    $seconds = round($seconds - ($hours * 3600) - ($minutes * 60), 0);

    if ($hours <= 9) {
        $strHours = "0" . $hours;
    } else {
        $strHours = $hours;
    }

    if ($minutes <= 9) {
        $strMinutes = "0" . $minutes;
    } else {
        $strMinutes = $minutes;
    }

    if ($seconds <= 9) {
        $strSeconds = "0" . $seconds;
    } else {
        $strSeconds = $seconds;
    }
    return "$strHours:$strMinutes";
}

/**
 * convert utf-8 To tis-620
 * $string = iconv("TIS-620","UTF-8",$string);
 *
 */
function setICONV($value) {

    //    switch (Zend_Registry::get('SYSTEM_LANGUAGE')) {
    //        case "THAI":
    //            return iconv("TIS-620", "UTF-8", $value);
    //            break;
    //        default:
    //            return $value;
    //            break;
    //    }

    return $value;
}

function setImportChartset($string) {

    $searchArray = array(
        "\xed"
        , "\xf9"
        , "\xd4"
        , "\xe0"
        , "\xfa"
        , "\xec"
        , "\xe3"
        , "\xf2"
        , "\xf4"
        , "\xf5"
        , "\xea"
        , "\xf3"
        , "\xe2"
    );

    $replaceArray = array(
        iconv("ISO-8859-1", "UTF-8", "\xed")
        , iconv("ISO-8859-1", "UTF-8", "\xf9")
        , iconv("ISO-8859-1", "UTF-8", "\xd4")
        , iconv("ISO-8859-1", "UTF-8", "\xe0")
        , iconv("ISO-8859-1", "UTF-8", "\xfa")
        , iconv("ISO-8859-1", "UTF-8", "\xec")
        , iconv("ISO-8859-1", "UTF-8", "\xe3")
        , iconv("ISO-8859-1", "UTF-8", "\xf2")
        , iconv("ISO-8859-1", "UTF-8", "\xf4")
        , iconv("ISO-8859-1", "UTF-8", "\xf5")
        , iconv("ISO-8859-1", "UTF-8", "\xea")
        , iconv("ISO-8859-1", "UTF-8", "\xf3")
        , iconv("ISO-8859-1", "UTF-8", "\xe2")
    );

    switch (Zend_Registry::get('SYSTEM_LANGUAGE')) {

        case "VIETNAMESE":
            return trim(str_replace($searchArray, $replaceArray, $string));

        case "THAI":
            return utf8_decode(iconv("ISO-8859-1", "UTF-8", $string));

        case "KHMER":
            return utf8_decode(iconv("ISO-8859-1", "UTF-8", $string));

        default:
            return iconv("ISO-8859-1", "UTF-8", $string);
    }
}

function setChartset($string) {

    switch (Zend_Registry::get('SYSTEM_LANGUAGE')) {

        case "THAI":
            return utf8_decode(iconv("ISO-8859-1", "UTF-8", $string));

        default:
            return utf8_decode(iconv("ISO-8859-1", "UTF-8", $string));
    }
}

function setBR($html) {

    $html1 = str_replace("\r", "<br>", $html);
    $html2 = str_replace("\n", "<br>", $html1);
    $html3 = str_replace("\r\n", "<br>", $html2);

    return $html3;
}

function getTimeBlockName($endtime_block_morning, $endtime_block_afternoon, $value) {

    if ($value <= $endtime_block_morning)
        return setICONV(MORNING);
    if ($endtime_block_morning <= $value && $value <= $endtime_block_afternoon)
        return setICONV(AFTERNOON);
    if ($value >= $endtime_block_afternoon)
        return setICONV(EVENING);
}

function findNameGradingTerm($value) {
    switch ($value) {
        case 'FIRST_SEMESTER': return FIRST_SEMESTER;
        case 'SECOND_SEMESTER': return SECOND_SEMESTER;
    }
}

function listGradingTerm($value) {
    switch ($value) {
        case 1:
            $data = array(
                'FIRST_SEMESTER' => FIRST_SEMESTER
                , 'SECOND_SEMESTER' => SECOND_SEMESTER
            );
            break;
        case 2:
            $data = array(
                'FIRST_TRIMESTER' => FIRST_TRIMESTER
                , 'SECOND_TRIMESTER' => SECOND_TRIMESTER
                , 'THIRD_TRIMESTER' => THIRD_TRIMESTER
            );
            break;
        default:
            $data = array(
                'FIRST_SEMESTER' => FIRST_SEMESTER
                , 'SECOND_SEMESTER' => SECOND_SEMESTER
            );
            break;
    }

    return $data;
}

function setFormatDate($d, $delimiter = "-") {
    list($y, $m, $d) = explode($delimiter, $d);

    if (strlen($d) == 1)
        $d = "0" . $d;
    if (strlen($m) == 1)
        $m = "0" . $m;

    return $y . "-" . $m . "-" . $d;
}

function setAverageRound($a, $b) {
    if ($b) {
        return round($a / $b, Zend_Registry::get('SCHOOL')->DECIMAL_PLACES);
    } else {
        return round($a / 1, Zend_Registry::get('SCHOOL')->DECIMAL_PLACES);
    }
}

function displayRound($a) {

    if (is_numeric($a)) {
        return round($a, Zend_Registry::get('SCHOOL')->DECIMAL_PLACES);
    } else {
        return "---";
    }
}

function writeTempFile($source, $data, $id, $path = 'temp/') {

    $filename = false;

    if ($data && $source) {
        $extension_test = explode(".", $source);
        $extension = array_pop($extension_test);

        $filename = $path . $id;
        $filename .= "." . $extension;
        if (is_file($filename)) {
            unlink($filename);
        }

        $source = fopen($filename, "w+");
        fwrite($source, $data);
        fclose($source);
    }
    return $filename;
}

function setViewColor($color, $value) {

    return "<span style=\"font:bold 11px tahoma, helvetica, sans-serif;color:" . $color . ";\">" . $value . "</span>";
}

function calculatedPercent($value, $total, $sign = true) {

    if ($value && $total && $value != '---' && $total != '---') {
        $calculated = ($value / $total) * 100;
        $result = round($calculated, Zend_Registry::get('SCHOOL')->DECIMAL_PLACES);
    } else {
        $result = 0;
    }

    return $sign ? $result . " %" : $result;
}

function getUserActivityName($value) {
    switch ($value) {
        case "UPDATE": return setICONV(UPDATE);
        case "CREATE": return setICONV(CREATE);
        case "REMOVE": return setICONV(REMOVE);
    }
}

/* THOU MORNG - 23.06.2011 */

function arraySortByKeyDate($tab, $key) {
    $compare = create_function('$a,$b', ' 
            $_date1 = isset($a["' . $key . '"])?$a["' . $key . '"]:"";
            $_date2 = isset($b["' . $key . '"])?$b["' . $key . '"]:"";
            $date1  = strtotime($_date1);
            $date2  = strtotime($_date2);
            if($date1 == $date2){return 0;}
        else {return ($date1 > $date2) ? -1 : 1;}');
    usort($tab, $compare);
    return $tab;
}

/* THOU MORNG - 05.01.2011 */

function arraySortByKeyFloat($tab, $key) {
    $compare = create_function('$a,$b', 'if ($a["' . $key . '"] == $b["' . $key . '"]) {return 0;}else {return ($a["' . $key . '"] > $b["' . $key . '"]) ? -1 : 1;}');
    usort($tab, $compare);
    return $tab;
}

/* THOU MORNG - 05.01.2011 */

function arraySortByKeyString($tab, $key) {
    $compare = create_function('$a,$b', 'if ($a["' . $key . '"] == $b["' . $key . '"]){return 0;}else {return ($a["' . $key . '"] < $b["' . $key . '"]) ? -1 : 1;}');
    usort($tab, $compare);
    return $tab;
}

function setAddress($street = false, $town = false, $postcode = false, $province = false) {
    $address = "";
    if ($street && $street != '---')
        $address .= $street . " ";
    if ($town && $town != '---')
        $address .= $town . " ";
    if ($postcode && $postcode != '---')
        $address .= $postcode . " ";
    if ($province && $province != '---')
        $address .= $province . " ";

    return $address ? $address : "---";
}

function checkNumericType($value) {
    if (is_numeric($value))
        return $value;
    else
        return "---";
}

function is_char($chr) {
    if (!$chr)
        return false;
    else
        return is_string($chr);
}

//VIK: 27.07.2011
function deleteTemp() {
    if (is_dir("./public/temp")) {
        chmod("public/temp", 777);
        $mydir = "public/temp/";
        $d = dir($mydir);
        while ($file = $d->read()) {
            if ($file != "..") {
                if (isset($_SERVER["SERVER_ADDR"])) {
                    if ($_SERVER["SERVER_ADDR"] <> "127.0.0.1")
                        unlink("./public/temp/" . $file);
                    else
                        unlink("./public/temp/" . $file);
                }
            }
        }
        $d->close();
        return true;
    } else {
        return false;
    }
    return false;
}

/**
 * The methode will convert the time in second
 * @param time ('YYYY-MM-dd HH:mm:ss' or 'HH:mm:ss') $d
 * @param $delimiter 
 * @return: the number in second 
 */
function convertTimeInSecond($d, $delimiter = " ") {
    $time = explode($delimiter, trim($d));
    if (count($time) == 2) {
        $time = explode(":", trim($time[1]));
    } else {
        $time = explode(":", $d);
    }
    return $time[0] * 3600 + $time[1] * 60 + $time[2];
}

/**
 * Set format for calender time: HH:mm - HH:mm
 * @param calender time (hh:mm - hh:mm) $t
 * @param $isForStartTime
 * @param $delimiter
 * @return time format hh:mm:ss
 */
function setFormatTime($t, $isForStartTime = true, $delimiter = "-") {
    $t = explode($delimiter, $t);
    $t = $isForStartTime ? trim($t[0]) : trim($t[1]);
    return $t . ":00";
}

function mround($number, $precision = 0) {

    $precision = ($precision == 0 ? 1 : $precision);
    $pow = pow(10, $precision);

    $ceil = ceil($number * $pow) / $pow;
    $floor = floor($number * $pow) / $pow;

    $pow = pow(10, $precision + 1);

    $diffCeil = $pow * ($ceil - $number);
    $diffFloor = $pow * ($number - $floor) + ($number < 0 ? -1 : 1);

    if ($diffCeil >= $diffFloor)
        return $floor;
    else
        return $ceil;
}

function timeDifference($date1, $date2) {

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    if ($date2 < $date1) {
        return 1;
    } else {
        return 0;
    }
}

function countDiffDate($date1, $date2) {

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $daysDiff = ($date2 - $date1) / (24 * 60 * 60);

    return $daysDiff;
}

function scaleImageFileToBlob($file) {

    $max_width = 300;
    $max_height = 300;

    list($width, $height, $image_type) = getimagesize($file);

    switch ($image_type) {
        case 1: $src = imagecreatefromgif($file);
            break;
        case 2: $src = imagecreatefromjpeg($file);
            break;
        case 3: $src = imagecreatefrompng($file);
            break;
        default: return '';
    }

    $x_ratio = $max_width / $width;
    $y_ratio = $max_height / $height;

    if (($width <= $max_width) && ($height <= $max_height)) {
        $tn_width = $width;
        $tn_height = $height;
    } elseif (($x_ratio * $height) < $max_height) {
        $tn_height = ceil($x_ratio * $height);
        $tn_width = $max_width;
    } else {
        $tn_width = ceil($y_ratio * $width);
        $tn_height = $max_height;
    }

    $tmp = imagecreatetruecolor($tn_width, $tn_height);

    /* Check if this image is PNG or GIF to preserve its transparency */
    if (($image_type == 1) OR ( $image_type == 3)) {
        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
    }
    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tn_width, $tn_height, $width, $height);

    /*
     * imageXXX() has only two options, save as a file, or send to the browser.
     * It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
     * So I start the output buffering, use imageXXX() to output the data stream to the browser, 
     * get the contents of the stream, and use clean to silently discard the buffered contents.
     */
    ob_start();

    switch ($image_type) {
        case 1: imagegif($tmp);
            break;
        case 2: imagejpeg($tmp, NULL, 100);
            break; // best quality
        case 3: imagepng($tmp, NULL, 0);
            break; // no compression
        default: echo '';
            break;
    }

    $final_image = ob_get_contents();

    ob_end_clean();

    return $final_image;
}

function getObjectStatus($object) {

    if ($object) {
        if ($object->STATUS) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function getFontColor($value) {

    switch ($value) {
        case "#000000":
        case "#993300":
        case "#333300":
        case "#003300":
        case "#003366":
        case "#000080":
        case "#333399":
        case "#333333":
        case "#800000":
        case "#808000":
        case "#008000":
        case "#008080":
        case "#0000FF":
        case "#666699":
        case "#808080":
        case "#339966":
        case "#3366FF":
        case "#800080":
        case "#969696":
        case "#993366":
        case "#993366":
        case "#993366":
        case "#993366":
        case "#FF6600":
        case "#FF0000":
        case "#FF00FF":
        case "#99CC00":
        case "#FF4500":
            return "#FFFFFF";
        case "#FFCC00":
        case "#FF99CC":
            return "#000000";
        default:
            return "#000000";
    }
}

function _format_bytes($a_bytes) {

    if (!$a_bytes) {
        return $value = "";
    } elseif ($a_bytes < 1024) {
        return $value = $a_bytes . ' B';
    } elseif ($a_bytes < 1048576) {
        return $value = round($a_bytes / 1024, 2) . ' KB';
    } elseif ($a_bytes < 1073741824) {
        return $value = round($a_bytes / 1048576, 2) . ' MB';
    } elseif ($a_bytes < 1099511627776) {
        return $value = round($a_bytes / 1073741824, 2) . ' GB';
    } elseif ($a_bytes < 1125899906842624) {
        return $value = round($a_bytes / 1099511627776, 2) . ' TB';
    } elseif ($a_bytes < 1152921504606846976) {
        return $value = round($a_bytes / 1125899906842624, 2) . ' PB';
    } elseif ($a_bytes < 1180591620717411303424) {
        return $value = round($a_bytes / 1152921504606846976, 2) . ' EB';
    } elseif ($a_bytes < 1208925819614629174706176) {
        return $value = round($a_bytes / 1180591620717411303424, 2) . ' ZB';
    } else {
        return $value = round($a_bytes / 1208925819614629174706176, 2) . ' YB';
    }

    return ($value) ? "(" . $value . ")" : "";
}

function setScoreFormat($value) {

    if (is_numeric($value)) {
        return number_format($value, 1, '.', '');
    } else {
        return $value;
    }
}

function getFielTypeIcon($value) {
    if ($value) {
        switch ($value) {
            case "application/msword":
                return "icon-page_word";
            case "application/vnd.ms-word":
                return "icon-page_word";
            case "application/vnd.ms-powerpoint":
                return "icon-page_white_powerpoint";
            case "application/vnd.ms-excel":
                return "icon-page_white_excel";
            case "application/pdf":
                return "icon-pdf";
            case "image/jpeg":
                return "icon-picture";
            case "image/png":
                return "icon-picture";
            case "application/x-msexcel":
                return "icon-page_white_excel";
            default:
                return "icon-message_information";
        }
    } else {
        return "icon-message_information";
    }
}

function checkPermittedFileFormat($file_extension) {

    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'docx' => 'application/msword',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    if (array_key_exists($file_extension, $mime_types)) {
        return true;
    } else {
        return false;
    }
}

function splitFile($filename) {

    $data = array();

    list ($name, $extension) = split('[/\\.]', $filename);

    $data["NAME"] = $name;
    $data["EXTENSION"] = $extension;
}

function getFileExtension($filename) {
    $i = explode('.', $filename);
    return strtolower(end($i));
}

function setDateToSecond($data) {

    return strtotime("" . $data . " 00:00:00");
}

function getMonthsBy2Date($date1, $date2) {
    //convert dates to UNIX timestamp
    $months = array();
    $time1 = strtotime($date1);
    $time2 = strtotime($date2);
    $tmp = date('mY', $time2);

    $months[] = array("month" => date('F', $time1), "year" => date('Y', $time1));

    while ($time1 < $time2) {
        $time1 = strtotime(date('Y-m-d', $time1) . ' +1 month');
        if (date('mY', $time1) != $tmp && ($time1 < $time2)) {
            $months[] = array("month" => date('F', $time1), "year" => date('Y', $time1));
        }
    }
    $months[] = array("month" => date('F', $time2), "year" => date('Y', $time2));
    return $months; //returns array of month names with year
}

function getMimeContentType($filename) {

    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
    );

    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (array_key_exists($ext, $mime_types)) {
        return $mime_types [$ext];
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;
    } else {
        return 'application/octet-stream';
    }
}

//Chuy Thong
//29/06/2012
function jsonCountGender($total) {
    $amount = 0;
    $sexcount = array();
    if (isset($total['MALE']) && $total['MALE'] != 0) {
        $sexcount[] = array("'" . constant("MALE") . "'", $total['MALE']);
        $amount += $total['MALE'];
    }

    if (isset($total['FEMALE']) && $total['FEMALE'] != 0) {
        $sexcount[] = array("'" . constant("FEMALE") . "'", $total['FEMALE']);
        $amount += $total['FEMALE'];
    }

    if (isset($total['UNKNOWN']) && $total['UNKNOWN'] != 0) {
        $sexcount[] = array("'" . constant("UNKNOWN") . "'", $total['UNKNOWN']);
        $amount += $total['UNKNOWN'];
    }
    $sexcount[] = array("'" . constant("TOTAL") . "'", $amount);

    return $sexcount;
}

// Chuy Thong 02/07/2012
function jsonBornYear($total) {
    $amount = 0;
    $count = array();
    ksort($total);
    foreach ($total as $k => $v) {
        $count[] = array("'" . $k . "'", $v);
        $amount += $v;
    }
    $count[] = array("'" . constant("TOTAL") . "'", $amount);
    return $count;
}

function setDate2Time($date) {
    //2009-02-01
    return strtotime($date);
}

function getDatesBetween2Dates($d1, $d2) {

    $day = 86400;
    $format = 'Y-m-d';
    $d1 = strtotime($d1);
    $d2 = strtotime($d2);

    $days = array();

    if ($d1 && $d2) {
        $numDays = round(($d2 - $d1) / $day) + 1;
        for ($i = 0; $i < $numDays; $i++) {
            $days[] = date($format, ($d1 + ($i * $day)));
        }
    }

    return $days;
}

function calculateTax($amount, $percentTax) {

    $result = 0;
    if ($amount && $percentTax) {
        $result = getNumberFormat($amount * $percentTax / 100);
    }

    return $result;
}

function calculateTaxPlus($amount, $percentTax) {

    $result = 0;
    $calculateTax = calculateTax($amount, $percentTax);
    if ($amount) {
        $result = getNumberFormat($amount + str2no($calculateTax));
    }

    return $result;
}

function calculateTaxMinus($amount, $percentTax) {

    $result = 0;
    $calculateTax = calculateTax($amount, $percentTax);
    if ($amount > $calculateTax) {
        $result = getNumberFormat($amount - str2no($calculateTax));
    }

    return $result;
}

function str2no($number) {
    $number = str_replace(".", "", addText($number));
    $number = str_replace(",", ".", addText($number));
    return is_numeric(addText($number)) ? addText($number) : "";
}

function calculateScholarship($amountValue, $percentValue) {

    $result = 0;
    if ($amountValue && $percentValue) {
        $result = $amountValue * $percentValue / 100;
    }

    return $result;
}

function checkFuturDate($date) {
    $date = strtotime($date);
    $today = strtotime(getCurrentDBDate());
    if ($date > $today) {
        return false;
    } elseif ($date < $today) {
        return true;
    }
}

function isUTF8($string) {

    if (mb_detect_encoding($string, 'UTF-8, ISO-8859-1') === 'UTF-8') {
        return 1;
    } else {
        return 0;
    }
}

function is_utf8($str) {
    $strlen = strlen($str);
    return $result = 1;
    for ($i = 0; $i < $strlen; $i++) {
        $ord = ord($str[$i]);
        if ($ord < 0x80)
            continue; // 0bbbbbbb
        elseif (($ord & 0xE0) === 0xC0 && $ord > 0xC1)
            $n = 1; // 110bbbbb (exkl C0-C1)
        elseif (($ord & 0xF0) === 0xE0)
            $n = 2; // 1110bbbb
        elseif (($ord & 0xF8) === 0xF0 && $ord < 0xF5)
            $n = 3; // 11110bbb (exkl F5-FF)
        else
            return $result = 0; // ungültiges UTF-8-Zeichen
        for ($c = 0; $c < $n; $c++) // $n Folgebytes? // 10bbbbbb
            if (++$i === $strlen || (ord($str[$i]) & 0xC0) !== 0x80) {
                return $result = 0; // ungültiges UTF-8-Zeichen       
            }
    }
    return $result; // kein ungültiges UTF-8-Zeichen gefunden
}

function firstDayOfMonth() {
    return date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
}

function lastDayOfMonth() {
    return date("Y-m-d", mktime(0, 0, 0, date("m") + 1, 0, date("Y")));
}

////////////////////////////////////////////////////////////////////////////////
//Remove duplicate array entries
//$a = array(1, 5, 8, 'Michael', 5, 4, 9, 'Martin', 18, 12, 'Michael', 4, 12);
////////////////////////////////////////////////////////////////////////////////
function removeDuplicates($array) {
    $counts = array_count_values($array);

    foreach ($counts as $value => $counter) {
        if ($counter > 1) {
            unset($counts[$value]);
        }
    }

    return array_keys($counts);
}

function getFirstDayOfMonth($month, $year) {
    return date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
}

function getLastDayOfMonth($month, $year) {

    return date('Y-m-d', mktime(0, 0, 0, $month, date('t', strtotime(getFirstDayOfMonth($month, $year))), $year));
}

function getDecimalPlaces() {

    return trim(Zend_Registry::get('SCHOOL')->DECIMAL_PLACES);
}

function number_is_between($number, $a, $b) {
    $min = min($a, $b);
    $max = max($a, $b);
    if ($number < $min)
        return FALSE;
    if ($number > $max)
        return FALSE;
    return TRUE;
}

function floatArrayCountValues($digits) {

    $newDigits = array();
    foreach ($digits as $key => $value)
    // cast float as string
        if (is_float($value))
            $newDigits[$key] = (string) $value;
        // for actual int and string values
        else
            $newDigits[$key] = $value;

    return array_count_values($newDigits);
}

function setEncryptId($valueId) {
    $arr = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "c", "v", "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "C", "V", "W", "X", "Y", "Z");
    $str1 = "";
    for ($i = 0; $i < strlen($valueId); $i++) {
        $str2 = "";
        for ($j = 0; $j < 5; $j++) {
            $r = rand(0, 50);
            $str2.=$arr[$r];
        }
        $str1.=substr($valueId, $i, 1) . $str2;
    }
    return strrev($str1);
}

function getDescryptId($EncryptId) {
    $str1 = "";
    $str2 = "";
    $str1 = strrev($EncryptId);
    for ($i = 0; $i < strlen($str1); $i+=6) {
        $str2.=substr($str1, $i, 1);
    }
    return $str2;
}

function getColorIndex($color) {
    switch ($color) {
        case "#000000":
            return 111;
        case "#800000":
            return 112;
        case "#FF0000":
            return 113;
        case "#FF00FF":
            return 114;
        case "#FF99CC":
            return 115;
        case "#993300":
            return 116;
        case "#FF6600":
            return 117;
        case "#FF9900":
            return 118;
        case "#FFCC00":
            return 119;
        case "#FFCC99":
            return 120;
        case "#333300":
            return 121;
        case "#808000":
            return 122;
        case "#99CC00":
            return 123;
        case "#FFFF00":
            return 124;
        case "#FFFF99":
            return 125;
        case "#003300":
            return 126;
        case "#008000":
            return 127;
        case "#339966":
            return 128;
        case "#00FF00":
            return 129;
        case "#CCFFCC":
            return 130;
        case "#003366":
            return 131;
        case "#008080":
            return 132;
        case "#33CCCC":
            return 133;
        case "#00FFFF":
            return 134;
        case "#CCFFFF":
            return 135;
        case "#000080":
            return 136;
        case "#0000FF":
            return 137;
        case "#3366FF":
            return 138;
        case "#00CCFF":
            return 139;
        case "#99CCFF":
            return 140;
        case "#333399":
            return 141;
        case "#666699":
            return 142;
        case "#800080":
            return 143;
        case "#993366":
            return 144;
        case "#CC99FF":
            return 145;
        case "#333333":
            return 146;
        case "#808080":
            return 147;
        case "#969696":
            return 148;
        case "#C0C0C0":
            return 149;
        case "#FFFFFF":
            return 1150;
    }
}

function current_age($birthdate) {
    return( floor((time() - strtotime($birthdate)) / 31536000) );
}

// Converts array elements to a CSV string
function csv($array) {
    $csv = "";
    for ($i = 0; $i < count($array); $i++) {
        $csv .= '"' . str_replace('"', '""', $array[$i]) . '"';
        if ($i < count($array) - 1)
            $csv .= ",";
    }
    return $csv;
}

function displayNumberFormat($value) {

    switch (Zend_Registry::get('SCHOOL')->CAMEMIS_DATE_FORMAT) {
        case 0:
            if (is_string($value)) {
                $result = $value;
            }
            if (is_int($value)) {
                $result = $value;
            }
            if (is_float($value)) {
                $result = str_replace(".", ".", (float) $value);
            }
            break;
        case 1:
            if (is_string($value)) {
                $result = $value;
            }
            if (is_int($value)) {
                $result = $value;
            }
            if (is_float($value)) {
                $result = str_replace(".", ",", (float) $value);
            }
            break;
        default:
            if (is_string($value)) {
                $result = $value;
            }
            if (is_int($value)) {
                $result = $value;
            }
            if (is_float($value)) {
                $result = str_replace(".", ".", (float) $value);
            }
            break;
    }
    return $result;
}

function setDatetimeFormat($datetime) {

    switch (getSystemDateFormat()) {
        case "DD.MM.YYYY": $format = "YYYY-MM-dd HH:mm:ss";
            break;
        case "DD-MM-YYYY": $format = "YYYY-MM-dd HH:mm:ss";
            break;
        case "DD/MM/YYYY": $format = "YYYY-MM-dd HH:mm:ss";
            break;
        case "MM.DD.YYYY": $format = "YYYY-MM-dd HH:mm:ss";
            break;
        case "MM-DD-YYYY": $format = "YYYY-MM-dd HH:mm:ss";
            break;
        case "MM/DD/YYYY": $format = "YYYY-MM-dd HH:mm:ss";
            break;
        default: $format = "YYYY-MM-dd HH:mm:ss";
            break;
    }
    $locale = new Zend_Locale('de_AT');
    $Date = new Zend_Date($datetime, false, $locale);
    return $Date->toString("" . $format);
}

function showSeconds2Date($seconds) {

    if ($seconds) {
        $format = date('Y-m-d', $seconds);
        switch (getSystemDateFormat()) {
            case "DD.MM.YYYY": $format = date('d.m.Y', $seconds);
                break;
            case "DD-MM-YYYY": $format = date('d-m-Y', $seconds);
                break;
            case "DD/MM/YYYY": $format = date('d/m/Y', $seconds);
                break;
            case "MM.DD.YYYY": $format = date('m.d.Y', $seconds);
                break;
            case "MM-DD-YYYY": $format = date('m-d-Y', $seconds);
                break;
            case "MM/DD/YYYY": $format = date('m/d/Y', $seconds);
                break;
            default: $format = date('Y-m-d', $seconds);
                break;
        }
    } else {
        $format = "---";
    }
    return $format;
}

function checkColHidden($index, $data) {
    return isset($data[$index]) ? "true" : "false";
}

function getColorFromIndex($index) {
    switch ($index) {
        case 0:
            return "#FF0000";
        case 1:
            return "#FF00FF";
        case 2:
            return "#FF99CC";
        case 3:
            return "#993300";
        case 4:
            return "#FF6600";
        case 5:
            return "#FF9900";
        case 6:
            return "#FFCC00";
        case 7:
            return "#FFCC99";
        case 8:
            return "#808000";
        case 9:
            return "#99CC00";
        case 10:
            return "#FFFF00";
        case 11:
            return "#FFFF99";
        case 12:
            return "#003300";
        case 13:
            return "#008000";
        case 14:
            return "#339966";
        case 15:
            return "#00FF00";
        case 16:
            return "#CCFFCC";
        case 17:
            return "#003366";
        case 18:
            return "#008080";
        case 19:
            return "#33CCCC";
        case 20:
            return "#00FFFF";
        case 21:
            return "#CCFFFF";
        case 22:
            return "#000080";
        case 23:
            return "#0000FF";
        case 24:
            return "#3366FF";
        case 25:
            return "#00CCFF";
        case 26:
            return "#99CCFF";
        case 27:
            return "#333399";
        case 28:
            return "#666699";
        case 29:
            return "#800080";
        case 30:
            return "#993366";
        case 31:
            return "#CC99FF";
        case 32:
            return "#333333";
        case 33:
            return "#808080";
        case 34:
            return "#969696";
        case 35:
            return "#C0C0C0";
    }
}

function sortByOrder(&$array, $key) {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach ($array as $ii => $va) {
        if (isset($va[$key])) {
            $sorter[$ii] = $va[$key];
        }
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        if (isset($array[$ii])) {
            $ret[$ii] = $array[$ii];
        }
    }
    $array = $ret;
}

// find number of days between two dates
function findDaysFrom2Dates($datetime1, $datetime2) {
    $datetime1 = $datetime1 ? $datetime1 : time();
    $datediff = $now - $datetime2;
    return floor($datediff / (60 * 60 * 24));
}

function showFileSize($file) {
    $a = array("B", "KB", "MB", "GB", "TB", "PB");
    $pos = 0;
    $size = filesize($file);
    while ($size >= 1024) {
        $size /= 1024;
        $pos++;
    }
    $result = isset($a[$pos]) ? round($size, 2) . " " . $a[$pos] : "";
    return $result;
}

function compressMultiSpacesToSingleSpace($inputstring) {
    if (!(strpos($inputstring, '  ') === false)) {
        $inputstring = str_replace('  ', ' ', $inputstring);
        $inputstring = compressMultiSpacesToSingleSpace($inputstring);
    }
    return $inputstring;
}

function get_file_name_extension($filenamestring) {
    return substr(strrchr($filenamestring, '.'), 1);
}

function delete_directory($dirname) {
    if (is_dir($dirname))
        $dir_handle = opendir($dirname);
    if (!$dir_handle)
        return false;
    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname . "/" . $file))
                unlink($dirname . "/" . $file);
            else
                delete_directory($dirname . '/' . $file);
        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}

function delete_files($target) {
    if (is_dir($target)) {
        $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

        foreach ($files as $file) {
            delete_files($file);
        }

        rmdir($target);
    } elseif (is_file($target)) {
        unlink($target);
    }
}

function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getGroupAssoc($array, $key) {
    $data = array();
    foreach ($array as $v) {
        $data[$v[$key]][] = $v;
    }
    return $data;
}

function getMonthYearByDateStr($value) {
    $data = array();
    if ($value) {
        $explode = explode('-', $value);
        $data["YEAR"] = isset($explode[0]) ? $explode[0] : "";
        $data["MONTH"] = isset($explode[1]) ? $explode[1] : "";
    }

    return (object) $data;
}

function getFullName($firstname, $lastname) {
    if (!SchoolDBAccess::displayPersonNameInGrid()) {
        return setShowText($lastname) . " " . setShowText($firstname);
    } else {
        return setShowText($firstname) . " " . setShowText($lastname);
    }
}

function getScoreRank($scoreList, $checkSchore) {

    $position = 0;
    $result = count($scoreList);

    if (is_numeric($checkSchore)) {

        if ($scoreList) {
            rsort($scoreList);
            if ($scoreList) {
                foreach ($scoreList as $key => $value) {
                    if ($key) {
                        if ($value == $checkSchore) {
                            $position = $key;
                            break;
                        }
                    }
                }
            }

            $ranks = array(1);
            for ($i = 1; $i < count($scoreList); $i++) {
                if ($scoreList[$i] != $scoreList[$i - 1])
                    $ranks[$i] = $i + 1;
                else
                    $ranks[$i] = $ranks[$i - 1];
            }

            $result = isset($ranks[$position]) ? $ranks[$position] : count($scoreList);
        }
    }else {
        $result = "---";
    }

    return $result;
}

////////////////////////////////////////////////////////////////////////////////
//Check and return duplicates array
////////////////////////////////////////////////////////////////////////////////
function returndup($array) {
    $results = array();
    $duplicates = array();
    foreach ($array as $item) {
        if (in_array($item, $results)) {
            $duplicates[] = $item;
        }

        $results[] = $item;
    }

    return $duplicates;
}

function diplayNameMonthYear($str) {
    
    $output = "---";
    $explode = explode("_", $str);
    if($explode){
        $month = isset($explode[0])?$explode[0]:"";
        $year = isset($explode[1])?$explode[1]:"";
        $output = constant($month)." (".$year.")";
    }
    
    return $output;
}

?>