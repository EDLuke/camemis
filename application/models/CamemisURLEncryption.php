<?php

class URLEncryption {

    public $isEmbeddedParams = true;

    public function encrypt($pData) {

        //$newpData = "&" . $pData . "&";
        return $this->base64url_encode($pData);
    }

    public function decrypt($pData) {
        return $this->base64url_decode($pData);
    }

    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    function parseEncryptedGET($pData) {

        if ($this->isEmbeddedParams) {
            $str = $this->decrypt($pData);
            if (isset($_GET["setId"]))
                $str .= "&setId=" . trim($_GET["setId"]);
            if (isset($_GET["section"]))
                $str .= "&section=" . trim($_GET["section"]);
            if (isset($_GET["subjectId"]))
                $str .= "&subjectId=" . trim($_GET["subjectId"]);
            if (isset($_GET["classId"]))
                $str .= "&classId=" . trim($_GET["classId"]);
            if (isset($_GET["academicId"]))
                $str .= "&academicId=" . trim($_GET["academicId"]);
            if (isset($_GET["objectId"]))
                $str .= "&objectId=" . trim($_GET["objectId"]);
            if (isset($_GET["studentId"]))
                $str .= "&studentId=" . trim($_GET["studentId"]);
            if (isset($_GET["assignmentId"]))
                $str .= "&assignmentId=" . trim($_GET["assignmentId"]);
            if (isset($_GET["setnewValue"]))
                $str .= "&setnewValue=" . trim($_GET["setnewValue"]);
            if (isset($_GET["monthyear"]))
                $str .= "&monthyear=" . trim($_GET["monthyear"]);
            if (isset($_GET["semester"]))
                $str .= "&semester=" . trim($_GET["semester"]);
            if (isset($_GET["schoolyearId"]))
                $str .= "&schoolyearId=" . trim($_GET["schoolyearId"]);
            if (isset($_GET["newValue"]))
                $str .= "&newValue=" . trim($_GET["newValue"]);
            if (isset($_GET["field"]))
                $str .= "&field=" . trim($_GET["field"]);
            if (isset($_GET["date"]))
                $str .= "&date=" . trim($_GET["date"]);
            if (isset($_GET["objectType"]))
                $str .= "&objectType=" . trim($_GET["objectType"]);
            if (isset($_GET["campusId"]))
                $str .= "&campusId=" . trim($_GET["campusId"]);
            if (isset($_GET["gradeId"]))
                $str .= "&gradeId=" . trim($_GET["gradeId"]);
            if (isset($_GET["target"]))
                $str .= "&target=" . trim($_GET["target"]);
            if (isset($_GET["scheduleId"]))
                $str .= "&scheduleId=" . trim($_GET["scheduleId"]);
            if (isset($_GET["shortday"]))
                $str .= "&shortday=" . trim($_GET["shortday"]);
            if (isset($_GET["parentId"]))
                $str .= "&parentId=" . trim($_GET["parentId"]);
            if (isset($_GET["personType"]))
                $str .= "&personType=" . trim($_GET["personType"]);
            if (isset($_GET["term"]))
                $str .= "&term=" . trim($_GET["term"]);
            if (isset($_GET["choosedate"]))
                $str .= "&choosedate=" . trim($_GET["choosedate"]);
            if (isset($_GET["eventDay"]))
                $str .= "&eventDay=" . trim($_GET["eventDay"]);
        } else {
            $str = $this->decrypt($pData);
        }

        $explode = explode("&", $str);
        $newExplode = array_unique($explode);
        $newStr = implode("&", $newExplode);
        //See source parameters.....
        //error_log($newStr);
        parse_str($newStr, $_GET);
    }

    function parseEncryptedPOST($pData) {
        parse_str($this->decrypt($pData), $_POST);
    }

    function encryptedGet($pGETString) {
        return $this->encrypt($pGETString);
    }

    function createEncryptedPOST($pPOSTString) {
        return $this->encrypt($pPOSTString);
    }

}

?>