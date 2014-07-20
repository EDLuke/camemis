<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 23.August.2010
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once 'include/Common.inc.php';
require_once 'models/app_university/student/StudentDBAccess.php';
require_once 'models/app_university/staff/StaffDBAccess.php';
//@sea Peng 02.05.2013
require_once 'models/app_university/sms/SendSMSDBAccess.php';
//@end sea Peng 02.05.2013
require_once setUserLoacalization();

class CommunicationDBAccess{

    protected $data = array();
    protected $out = array();

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
        
    public function getCommunicationtDataFromId($Id) {

        $result = $this->findCommunicationFromId($Id);

        $data = array();
        if ($result) {

            $data["ID"] = $result->ID;
            $data["SUBJECT"] = setShowText($result->SUBJECT);
            $data["CONTENT"] = $result->CONTENT;
            $data["SEND_RECIPIENT"] = $result->SEND_RECIPIENT;

            if ($result->TYPE == "REPLY_DRAFTS") {
                $data["STR_RECIPIENT_NAME"] = $result->SEND_RECIPIENT;
            } else {
                $data["STR_RECIPIENT_NAME"] = $this->strRecipientName($Id);
            }

            $data["STR_RECIPIENT"] = $this->strRecipientId($Id);
            $data["SENT_ON"] = getShowDateTime($result->SEND_DATE);
        }

        return $data;
    }

    public function jsonLoadCommunication($Id) {

        $result = $this->findCommunicationFromId($Id);

        if ($result) {
            $o = array(
                "success" => true
                , "data" => $this->getCommunicationtDataFromId($Id)
            );
        } else {
            $o = array(
                "success" => true
                , "data" => array()
            );
        }
        return $o;
    }
    
    public function jsonLoadAllCommunications($params) {

        $start = $params["start"] ? (int) $params["start"] : "0";
        $limit = $params["limit"] ? (int) $params["limit"] : "50";

        $result = $this->sqlAllCommunication($params);

        $i = 0;
        $data = array();
        if ($result) {
            foreach ($result as $value) {

                $data[$i]['ID'] = "" . $value->ID . "";
                $data[$i]['SEND_DATE'] = getShowDateTime($value->SEND_DATE);
                //@sea peng
                $data[$i]['SEND_TYPE'] = "" . $value->SEND_TYPE . "";
                //
                $data[$i]['COMMUNICATION_TYPE'] = "" . $value->COMMUNICATION_TYPE . "";
                //@veasna
                switch($value->SENDER_TYPE){   
                   case "STAFF":
                      $staffObject=StaffDBAccess::findStaffFromId($value->SENDER);
                      break;
                }
                //

                if (isset($value->IS_READ)) {
                    if ($value->IS_READ == 0) {
                        $data[$i]['SUBJECT'] = "<b>" . stripslashes($value->SUBJECT) . "</b>";
                        $data[$i]['SENDER'] = "<b>" . $staffObject->NAME . "</b>";
                    } else {
                        $data[$i]['SUBJECT'] = stripslashes($value->SUBJECT);
                        $data[$i]['SENDER'] = "" . $staffObject->NAME . "";
                    }
                } else {
                    $data[$i]['SUBJECT'] = stripslashes($value->SUBJECT);
                }
                //@veasna
                
                $content=explode(">>>>>>>>>>>>>>>>",$value->CONTENT);
                $checkContent=count($content);
                $more=""; 
                if($checkContent<=1){
                    if(strlen($value->CONTENT)>50)
                        $more=".....";  
                    $data[$i]['CONTENT']= substr($value->CONTENT,0,50). " .....";
                    
                }else{
                     if(strlen($content[$checkContent-1])>90)
                        $more=".....";
                     $lastContent=substr($content[$checkContent-1],0,50);
                     $data[$i]['CONTENT'] = "" . strip_tags($lastContent) . " .....";  
                }                                           
                
                //$data[$i]['SENDER'] = "" . $staffObject->NAME . "";
                $data[$i]['SEND_RECIPIENT'] = "" . substr($value->SEND_RECIPIENT,0,50) . "";
                $data[$i]['CREATED_DATE'] = "" . $value->CREATED_DATE . "";
                
                switch($value->COMMUNICATION_TYPE){   
                   case "STAFF_TO_STAFF":
                      $data[$i]['COMMUNICATION'] = "" . MAIL_TO_STAFF . "";
                      break;
                   case "STAFF_TO_STUDENTS":
                   case "TEACHER_TO_STUDENTS":
                      $data[$i]['COMMUNICATION'] = "" . MAIL_TO_STUDENTS . "";
                      break;
                   case "STUDENT_TO_TEACHER":
                      $data[$i]['COMMUNICATION'] = "" . MAIL_TO_TEACHER . "";
                      break; 
                }
                
                //
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

    public function jsonLoadInboxCommunication($Id) {

        $result = $this->findCommunicationFromId($Id);

        $senderData = $this->getSenderData($Id);

        $data["ID"] = $result->ID;
        $data["SUBJECT"] = setShowText($result->SUBJECT);
        $data["CONTENT"] = $result->CONTENT;
        $data["SENT_ON"] = getShowDateTime($result->SEND_DATE);
        $data["SENDER"] = $senderData["STR_RECIPIENT_NAME"];
        $data["STR_RECIPIENT_ID"] = $senderData["STR_RECIPIENT_ID"];

        //Read message..
        $SAVEDATA["IS_READ"] = 1;    
        $WHERE = array();
        $WHERE[] = self::dbAccess()->quoteInto('RECIPIENT_ID = ?', Zend_Registry::get('USER')->ID);
        $WHERE[] = self::dbAccess()->quoteInto('COMMUNICATION_ID = ?', $Id);
        self::dbAccess()->update('t_recipient_communication', $SAVEDATA, $WHERE);

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function jsonLoadReplyCommunication($Id) {

        $result = $this->findCommunicationFromId($Id);

        $senderData = $this->getSenderData($result->PARENT);

        $data["ID"] = $result->ID;
        $data["SUBJECT"] = setShowText($result->SUBJECT);
        $data["CONTENT"] = $result->CONTENT;
        $data["STR_RECIPIENT_NAME"] = $senderData["STR_RECIPIENT_NAME"];
        $data["STR_RECIPIENT_ID"] = $senderData["STR_RECIPIENT_ID"];

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function jsonLoadAddReplyCommunication($Id) {

        $result = $this->findCommunicationFromId($Id);

        $senderData = $this->getSenderData($Id);

        $data["ID"] = $result->ID;
        $data["SUBJECT"] = setShowText($result->SUBJECT);
        $data["STR_RECIPIENT_NAME"] = $senderData["STR_RECIPIENT_NAME"];
        $data["STR_RECIPIENT_ID"] = $senderData["STR_RECIPIENT_ID"];

        return array(
            "success" => true
            , "data" => $data
        );
    }

    public function findCommunicationFromId($Id) {

        $SQL = "SELECT DISTINCT *";
        $SQL .= " FROM t_communication";
        $SQL .= " WHERE";
        $SQL .= " ID = '" . $Id . "'";
        return self::dbAccess()->fetchRow($SQL);
    }

    public function sqlAllCommunication($params) {

        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
        $type = isset($params["type"]) ? addText($params["type"]) : "";
        $academicId = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        $SQL = "";
        $SQL .= "
            SELECT A.ID AS ID
            ,A.SUBJECT AS SUBJECT
            ,A.SENDER AS SENDER
            ,A.CONTENT
            ,A.SEND_DATE AS SEND_DATE
            ,A.SEND_TYPE AS SEND_TYPE
            ,A.SENDER_TYPE AS SENDER_TYPE
            ,A.CREATED_DATE AS CREATED_DATE
            ,A.SEND_RECIPIENT AS SEND_RECIPIENT
            ,A.COMMUNICATION_TYPE AS COMMUNICATION_TYPE
        ";
        if ($type == "INBOX") {
            $SQL .= " ,B.IS_READ";
        }
        $SQL .= " FROM t_communication AS A";

        if ($type == "INBOX") {
            $SQL .= " LEFT JOIN t_recipient_communication AS B ON A.ID=B.COMMUNICATION_ID";
        }

        $SQL .= " WHERE 1=1";

        switch ($type) {
            case "INBOX":
                $SQL .= " AND A.TYPE = 'SEND'";
                break;
            case "DRAFTS":
            case "REPLY_DRAFTS":
                $SQL .= " AND A.TYPE = 'REPLY_DRAFTS' OR A.TYPE = 'DRAFTS'";
                break;
            default:
                $SQL .= " AND A.TYPE = '" . $type . "' ";
                break;
        }

        if ($globalSearch) {

            $SQL .= " AND ((A.SUBJECT LIKE '%" . $globalSearch . "%')";
            $SQL .= " OR (A.CONTENT LIKE '%" . $globalSearch . "%')";
            $SQL .= " ) ";
        }

        switch (UserAuth::getUserType()) {
            case "STUDENT":
                if ($academicId) {
                    $SQL .= " AND A.CLASS_ID = '" . $academicId . "' ";
                }
                break;
        }

        if ($type == "INBOX") {
            $SQL .= " AND B.RECIPIENT_ID = '" . Zend_Registry::get('USER')->ID . "' ";
        } else {
            $SQL .= " AND A.SENDER = '" . Zend_Registry::get('USER')->ID . "' ";
        }

        switch ($type) {
            case "INBOX":
                $SQL .= " ORDER BY A.CREATED_DATE DESC";
                break;
            case "SEND":
                $SQL .= " ORDER BY A.SEND_DATE DESC";
                break;
            default:
                $SQL .= " ORDER BY A.SUBJECT";
                break;
        }
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    public function jsonRemoveCommunication($params) {

        $selectionIds = $params["selectionIds"];

        if ($selectionIds != "") {
            $removeIds = explode(",", $selectionIds);
            $selectedCount = 0;
            if ($removeIds)
                foreach ($removeIds as $removeId) {
                    
                    self::dbAccess()->delete('t_communication', array("ID='" . $removeId . "'","SENDER='" . Zend_Registry::get('USER')->ID . "'"));
                    self::dbAccess()->delete('t_recipient_communication', array("COMMUNICATION_ID='" . $removeId . "'"));
                    
                    $selectedCount++;
                }
        } else {
            $selectedCount = 0;
        }

        return array("success" => true, 'selectedCount' => $selectedCount);
    }

    protected function removeRecipientCommunication($Id) {
        self::dbAccess()->query("DELETE FROM t_recipient_communication WHERE COMMUNICATION_ID = '" . $Id . "'");
    }

    protected function addRecipientCommunication($objectId, $recipientId, $recipientType) {

        if ($recipientId != Zend_Registry::get('USER')->ID) {
            $SAVEDATA['RECIPIENT_ID'] = $recipientId;
            $SAVEDATA['RECIPIENT_TYPE'] = $recipientType;
            $SAVEDATA['COMMUNICATION_ID'] = $objectId;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            self::dbAccess()->insert('t_recipient_communication', $SAVEDATA);
        }
    }

    public function sqlRecipientByCommunication($Id) {

        $facette = $this->findCommunicationFromId($Id);
        $parentId = $facette->PARENT ? $facette->PARENT : $Id;

        $SQL = "";
        $SQL .= "
            SELECT
            A.RECIPIENT_ID AS RECIPIENT_ID
            ,B.LASTNAME AS LASTNAME
            ,B.FIRSTNAME AS FIRSTNAME
        ";
        $SQL .= " FROM t_recipient_communication AS A";

        if ($parentId) {

            $parentObject = $this->findCommunicationFromId($parentId);

            switch ($parentObject->COMMUNICATION_TYPE) {
                case "STUDENT_TO_TEACHER":
                case "STAFF_TO_STAFF":
                    $SQL .= " LEFT JOIN t_staff AS B ON B.ID = A.RECIPIENT_ID";
                    break;
                case "TEACHER_TO_STUDENTS":
                case "TEACHER_TO_STUDENT":
                    $SQL .= " LEFT JOIN t_student AS B ON B.ID = A.RECIPIENT_ID";
                    break;
            }
        }

        $SQL .= " WHERE 1=1";
        $SQL .= " AND A.COMMUNICATION_ID='" . $Id . "'";
        //echo $SQL;
        return self::dbAccess()->fetchAll($SQL);
    }

    protected function strRecipientId($Id) {

        $result = $this->sqlRecipientByCommunication($Id);

        $data = array();
        if ($result)
            foreach ($result as $value) {
                $data[$value->RECIPIENT_ID] = $value->RECIPIENT_ID;
            }

        return implode(",", $data);
    }

    protected function strRecipientName($Id) {

        $result = $this->sqlRecipientByCommunication($Id);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                $ii = $i + 1;
                $data[$value->RECIPIENT_ID] = $ii . ") " . setShowText($value->LASTNAME) . " " . setShowText($value->FIRSTNAME);

                $i++;
            }

        return implode("\n", $data);
    }
    
    //@sea peng 02.05.2013
    public function jsonActionSingleCommunication($params) {
        $send_type = isset($params["SEND_TYPE"]) ? addText($params["SEND_TYPE"]) : "";
        $result = SchoolDBAccess::getSchool();
        switch ($send_type){
            case "EMAIL":
                $sendTo = isset($params["EMAIL"])? addText($params["EMAIL"]) : "";
                $subject_email = isset($params["SUBJECT_EMAIL"])? addText($params["SUBJECT_EMAIL"]) : "";
                $content_eamil = isset($params["CONTENT_EMAIL"])? addText($params["CONTENT_EMAIL"]) : "";
                $header="";
                if($result->SMS_DISPLAY){
                    $header = 'From:'.$result->SMS_DISPLAY. "\r\n" .
                    'Reply-To:'.$result->SMS_DISPLAY . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                }else{
                    $header = 'From: noreply@camemis.com'. "\r\n" .
                    'Reply-To: noreply@camemis.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();                  
                }
                mail($sendTo, $subject_email, $content_eamil, $header);
                break;
            case "SMS":
                $sendTo = isset($params["MOBIL_PHONE"])? addText($params["MOBIL_PHONE"]) : "";
                $content_phone = isset($params["CONTENT_PHONE"])? addText($params["CONTENT_PHONE"]) : "";
                SendSMSDBAccess::curlSendSMS($sendTo, $content_phone);
                break;
        }  

        $this->jsonSaveCommunication($params);                        
    }
    //@end sea peng 02.05.2013

    public function jsonSaveCommunication($params) {

        $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";

        $facette = $this->findCommunicationFromId($objectId);

        $str_recipient_id = isset($params["STR_RECIPIENT"]) ? addText($params["STR_RECIPIENT"]) : "";
        $send_recipients = isset($params["STR_RECIPIENT_NAME"]) ? addText($params["STR_RECIPIENT_NAME"]) : "";

        $sender_type = isset($params["sender_type"]) ? addText($params["sender_type"]) : "";
        $contentReply = isset($params["CONTENT_REPLY"]) ? addText($params["CONTENT_REPLY"]) : "";

        $recipient_type = isset($params["recipient_type"]) ? addText($params["recipient_type"]) : "";
        $action = isset($params["action"]) ? $params["action"] : "DRAFTS";

        if (isset($params["SUBJECT"]))
            $SAVEDATA['SUBJECT'] = isset($params["SUBJECT"]) ? addText($params["SUBJECT"]) : "";

        if (isset($params["academicId"]))
            $SAVEDATA['CLASS_ID'] = isset($params["academicId"]) ? addText($params["academicId"]) : "";

        if (isset($params["parentId"])) {

            $SAVEDATA['PARENT'] = isset($params["parentId"]) ? addText($params["parentId"]) : "";
            $parentObject = $this->findCommunicationFromId($params["parentId"]);
            $newContent = addText($parentObject->CONTENT);
            $newContent .= "<BR/><BR/><B>" . getCurrentDBDateTime() . " >>>>>>>>>>>>>>>></B><BR/><BR/>";
            $newContent .= $contentReply;
            $SAVEDATA['CONTENT'] = $newContent;
        } else {
            if (isset($params["CONTENT"]))
                $SAVEDATA['CONTENT'] = isset($params["CONTENT"]) ? addText($params["CONTENT"]) : "";
        }

        if (isset($params["communication_type"]))
            $SAVEDATA['COMMUNICATION_TYPE'] = isset($params["communication_type"]) ? $params["communication_type"] : "";
        
        //@sea peng 02.05.2013
        $send_type = isset($params["SEND_TYPE"]) ? addText($params["SEND_TYPE"]) : "";
        $subject_email = isset($params["SUBJECT_EMAIL"]) ? addText($params["SUBJECT_EMAIL"]) : "";
        $content_email = isset($params["CONTENT_EMAIL"]) ? addText($params["CONTENT_EMAIL"]) : "";
        $content_phone = isset($params["CONTENT_PHONE"]) ? addText($params["CONTENT_PHONE"]) : "";
        $subject_message = isset($params["SUBJECT_MESSAGE"]) ? addText($params["SUBJECT_MESSAGE"]) : "";
        $content_message = isset($params["CONTENT_MESSAGE"]) ? addText($params["CONTENT_MESSAGE"]) : "";
        $subject_sms = isset($params["SEND_TYPE"]) ? addText($params["SEND_TYPE"]) : "";

        switch ($send_type){
            case "EMAIL":
                $SAVEDATA['SEND_TYPE'] = $send_type;
                $SAVEDATA['SUBJECT'] = $subject_email;
                $SAVEDATA['CONTENT'] = $content_email;
                break;
            case "SMS":
                $SAVEDATA['SEND_TYPE'] = $send_type;
                $SAVEDATA['SUBJECT'] = $subject_sms; 
                $SAVEDATA['CONTENT'] =  $content_phone;
                break;
            case "MESSAGE":
                $SAVEDATA['SEND_TYPE'] = $send_type;
                $SAVEDATA['SUBJECT'] = $subject_message;                   
                $SAVEDATA['CONTENT'] = $content_message;
                break;        
        }

        if (isset($params["interface_type"]))
        $SAVEDATA['INTERFACE_TYPE'] = isset($params["interface_type"]) ? $params["interface_type"] : "";
        //@end sea peng 02.05.2013

        switch ($action) {

            case "SEND":
                $SAVEDATA['SEND_DATE'] = getCurrentDBDateTime();
                $SAVEDATA["TYPE"] = "SEND";
                $SAVEDATA["SEND_RECIPIENT"] = $send_recipients;
                break;
            case "REPLY_SEND":
                $SAVEDATA['SEND_DATE'] = getCurrentDBDateTime();
                $SAVEDATA["TYPE"] = "SEND";
                $SAVEDATA["SEND_RECIPIENT"] = $send_recipients;
                break;
            case "DRAFTS":
                $SAVEDATA["TYPE"] = "DRAFTS";
                $SAVEDATA["SEND_RECIPIENT"] = $send_recipients;
                break;
            case "REPLY_DRAFTS":
                $SAVEDATA["TYPE"] = "REPLY_DRAFTS";
                $SAVEDATA["SEND_RECIPIENT"] = $send_recipients;
                break;
        }

        if ($facette) {

            $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
            self::dbAccess()->update('t_communication', $SAVEDATA, $WHERE);
        } else {

            $SAVEDATA["SENDER"] = Zend_Registry::get('USER')->ID;
            $SAVEDATA["SENDER_TYPE"] = $sender_type;
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;       
            self::dbAccess()->insert('t_communication', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
        }

        $this->removeRecipientCommunication($objectId);

        switch ($action) {
            case "REPLY_SEND":
            case "REPLY_DRAFTS":
                $this->addRecipientCommunication($objectId, $parentObject->SENDER, $recipient_type);
                break;
            default:
                $entries = explode(",", $str_recipient_id);
                if ($entries) {
                    foreach ($entries as $recipientId) {
                        if ($objectId <> "new")
                            $this->addRecipientCommunication($objectId, $recipientId, $recipient_type);
                    }
                }
                break;
        }
        return array("success" => true, "objectId" => $objectId);
    }

    public function getSenderData($Id) {

        $facette = $this->findCommunicationFromId($Id);

        $parentId = $facette->PARENT ? $facette->PARENT : $Id;
        
        $data = array();
        
        $data["STR_RECIPIENT_NAME"] = "";
        $data["STR_RECIPIENT_ID"] = "";
        $data["RECIPIENT_TYPE"] = "";
        $data["COMMUNICATION_TYPE"] = "";
        $data["SENDER_TYPE"]="";

        switch (UserAuth::getUserType()) {
            case "TEACHER":
            case "INSTRUCTOR":
            case "ADMIN":
            case "SUPERADMIN":
                $data["SENDER_TYPE"] = "STAFF";
                break;
            case "STUDENT":
                $data["SENDER_TYPE"] = "STUDENT";
                break;
        }

        if ($parentId) {

            $parentObject = $this->findCommunicationFromId($parentId);

            switch ($parentObject->COMMUNICATION_TYPE) {
                case "STUDENT_TO_TEACHER":
                    $data["COMMUNICATION_TYPE"] = "TEACHER_TO_STUDENT";
                    break;
                case "TEACHER_TO_STUDENT":
                    $data["COMMUNICATION_TYPE"] = "STUDENT_TO_TEACHER";
                    break;
                case "STAFF_TO_STAFF":
                    $data["COMMUNICATION_TYPE"] = "STAFF_TO_STAFF";
                    break;
            }
        }
        
        if ($facette) {

            $staffObject = StaffDBAccess::findStaffFromId($facette->SENDER);
            $studentObject = StudentDBAccess::findStudentFromId($facette->SENDER);

            if ($staffObject) {

                $data["STR_RECIPIENT_ID"] = $staffObject->ID;
                $data["STR_RECIPIENT_NAME"] = $staffObject->LASTNAME . " " . $staffObject->FIRSTNAME;
                $data["RECIPIENT_TYPE"] = "STAFF";
            }

            if ($studentObject) {

                $data["STR_RECIPIENT_ID"] = $studentObject->ID;
                $data["STR_RECIPIENT_NAME"] = $studentObject->LASTNAME . " " . $studentObject->FIRSTNAME;
                $data["RECIPIENT_TYPE"] = "STUDENT";
            }
        }

        return $data;
    }

    public function jsonLoadNewMessage($params) {
        $type=isset($params["type"])?$params["type"]:"";
        $SQL = "SELECT COUNT(*) AS C";
        $SQL .= " FROM t_recipient_communication AS A";
        $SQL .= " LEFT JOIN t_communication AS B ON B.ID = A.COMMUNICATION_ID";
        $SQL .= " WHERE 1= 1";
        
        if($type){
            switch ($type) {
                case 'DRAFTS':
                    $SQL .= " AND B.TYPE = 'DRAFTS'";
                    $SQL .= " AND B.SENDER = '" . Zend_Registry::get('USER')->ID . "'";
                    break;
                //sea peng 09.05.2013
                case 'SEND':
                    $SQL .= " AND B.TYPE = 'SEND'";
                    $SQL .= " AND B.SENDER = '" . Zend_Registry::get('USER')->ID . "'";
                    break;
                //
                default:
                    $SQL .= " AND B.TYPE = 'SEND'";
                    $SQL .= " AND A.RECIPIENT_ID = '" . Zend_Registry::get('USER')->ID . "'";
                    break;
            }
        
        }
        $SQL .= " AND A.IS_READ = '0'";
        
        $result = self::dbAccess()->fetchRow($SQL);
        //error_log($SQL);
        $count = $result ? $result->C : 0;
        return array(
            "success" => true
            , "count" => $count
        );
    }
    
    //@sea peng 08.05.2013
    public static function getAllCommunicationSubjectQuery($params) {

        $parentId = isset($params["parentId"]) ? addText($params["parentId"]) : "";
        $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";

        $SQL = "";
        $SQL .= " SELECT *";
        $SQL .= " FROM t_communication_subject AS A";
        $SQL .= " WHERE 1=1";

        if ($globalSearch) {
            $SQL .= " AND ((A.NAME like '" . $globalSearch . "%') ";
            $SQL .= " ) ";
        }
        
        if($parentId){
            $SQL .= " AND PARENT='".$parentId."'";
        }else{
            $SQL .= " AND PARENT='0'";
        }
        
        $SQL .= " ORDER BY A.NAME";
        $result = self::dbAccess()->fetchAll($SQL);

        return $result;
    }
    
    public static function checkStaffCommunicationSubject($staffId, $communicationSubjectId) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_staff_campus", array("C" => "COUNT(*)"));
        if ($staffId) $SQL->where("STAFF = ?",$staffId);
        if ($communicationSubjectId) $SQL->where("CAMPUS = '" . $communicationSubjectId . "'");
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    
    public function jsonTreeAllCommunicationSubject($params) {

        $params["parentId"] = isset($params["node"]) ? addText($params["node"]) : "0";
        $userId = isset($params["userId"]) ? addText($params["userId"]) : "";
        $result = self::getAllCommunicationSubjectQuery($params);

        $data = array();
        $i = 0;
        if ($result)
            foreach ($result as $value) {

                if (self::checkChild($value->ID)) {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['iconCls'] = "icon-folder_magnify";
                    $data[$i]['cls'] = "nodeTextBold";
                    $data[$i]['leaf'] = false;
                } else {
                    $data[$i]['id'] = "" . $value->ID . "";
                    $data[$i]['text'] = stripslashes($value->NAME);
                    $data[$i]['cls'] = "nodeTextBlue";
                    $data[$i]['leaf'] = true;
                    
                    if($userId){
                        
                        $data[$i]['iconCls'] = "icon-application_form_magnify_link";  
                        if(self::checkStaffCommunicationSubject($userId, $value->ID)){
                            $data[$i]['checked'] = true;
                        }else{
                            $data[$i]['checked'] = false;
                        }
                        
                    }else{
                        $data[$i]['iconCls'] = "icon-application_form_magnify";
                    }
                }
                $i++;
        }

        return $data;
    }
    
    public static function checkChild($Id) {

        $SQL = self::dbAccess()->select();
        $SQL->from("t_communication_subject", array("C" => "COUNT(*)"));
        if ($Id) $SQL->where("PARENT = ?",$Id);
        //error_log($SQL);
        $result = self::dbAccess()->fetchRow($SQL);
        return $result ? $result->C : 0;
    }
    //@end sea peng 08.05.2013
}

?>