<?php
    ///////////////////////////////////////////////////////////
    // @veasna
    // Date: 02.03.2013
    // 
    ///////////////////////////////////////////////////////////
    require_once("Zend/Loader.php");
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once 'models/app_university/student/StudentDBAccess.php';
    require_once 'models/app_university/student/StudentSearchDBAccess.php';
    require_once 'models/app_university/finance/ExpenseDBAccess.php';
    require_once 'models/app_university/finance/FeeDBAccess.php';
    require_once 'models/app_university/training/TrainingDBAccess.php';
    require_once 'models/app_university/CommunicationDBAccess.php';
    require_once setUserLoacalization();

    class StudentPaymentSettingDBAccess {
        
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
        
        public static function getCurrency() {

            return Zend_Registry::get('SCHOOL')->CURRENCY;
        }
        //student schoolyear and grade
        
        public static function findStudentsBySchoolyearGrade($schoolyear,$grade){  //check again for the student own money
            
            $SELECTION_A = array(
                "CODE AS STUDENT_CODE"
                , "FIRSTNAME AS FIRSTNAME"
                , "LASTNAME AS LASTNAME"
                , "PHONE AS PHONE"
                , "EMAIL AS EMAIL"
                , "GENDER AS GENDER"
            );
        
            $SELECTION_B = array(
                "SCHOOL_YEAR AS SCHOOLYEAR"
                , "GRADE AS GRADE"
                , "CLASS AS CLASS"
                , "CAMPUS AS CAMPUS"
            );
            
            $SELECTION_C = array(
                "NAME AS CLASS_NAME"
                , "TITLE AS TITLE"
            );
            
            $SQL = self::dbAccess()->select();
            
            $SQL->from(array('A' => 't_student'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_student_schoolyear'), 'A.ID=B.STUDENT', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_grade'), 'B.CLASS=C.ID', $SELECTION_C);

            $SQL->where("B.SCHOOL_YEAR = '" . $schoolyear . "'");
            $SQL->where("B.GRADE in (" . $grade . ")");
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchAll($SQL);
            
            return $resultRows; 
            
        }
        
        public static function getGradebyFeeSchoolyear($feeId,$schoolyear){
            
            $SELECTION_A = array(
                "GRADE AS GRADE"
            );
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_fee_general'), $SELECTION_A); 
            
            $SQL->where("A.FEE = '" . $feeId . "'");
            $SQL->where("A.SCHOOLYEAR = '" . $schoolyear . "'");
            
            $resultRows = self::dbAccess()->fetchAll($SQL);
            
            $data = array();
            if ($resultRows) {
                foreach ($resultRows as $value) {  
                     $data[]=$value->GRADE;   
                }
            }
                   
           return  $data;
        }
        
        public static function checkStudentPaid($feeId, $studentId){
            
            $SQL = self::dbAccess()->select();
            $SQL->from("t_income", array("*"));
            $SQL->where("STUDENT = ?",$studentId);
            $SQL->where("FEE = '" . $feeId . "'");
            $SQL->order('CREATED_DATE DESC');
            //error_log($SQL->__toString());
            $result=self::dbAccess()->fetchAll($SQL);
            
            return  $result;
            
        }
        
        public static function checkStudentAlert($lastPaidDate,$daysAfterPaid){
               
            $currentdate=getCurrentDBDate();
            $date1 = new DateTime($currentdate); 
            $date2 = new DateTime($lastPaidDate);
            $interval = $date1->diff($date2);
            if($interval->d<=$daysAfterPaid){
             return true;
            }else{
             return false;
            }  
            
           //return $interval->d;
        }
        
        public static function jsonLoadStudentFeeReminder($params){
        
            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $feeId = isset($params["feeID"]) ? addText($params["feeID"]) : '';
            $schoolyear=isset($params["schoolyear"]) ? addText($params["schoolyear"]) : '';
            
            $result = self::findStudentsByFeeID($params);
            

            $data = array();
            $i = 0;
            if ($result) {
                foreach ($result as $value) {
                    
                    $objectPaidFee=self::getLastPaidDate($value->CODE_ID,$value->FEE_ID); //paid for student
                    
                    if($objectPaidFee){//check piad student
                    
                         $nPiad=count($objectPaidFee);  //n paid student
                         $objectfeeNoption=FeeOptionDBAccess::getFeeOptionById($value->CHOOSE_OPTION);//fee Noption
                         $nFeeOption=$objectfeeNoption->N_OPTION;
                         
                         if($nPiad < $nFeeOption){  //if not yet paid all
                         
                            $nextPay=FeeOptionDBAccess::findFeeOptionByFeeIdNoption($value->FEE_ID,$nPiad+1);
                            
                            if($nextPay->DiffDate<=7){
                                $check=true;
                                $duedays=$nextPay->DiffDate;
                                $dateline= $nextPay->END_DATE;   
                            }else{
                                $check=false;
                            }
                                
                         }else{
                            
                             $check=false; 
                             
                         }
                         
                    
                    }else{
                         $check=true;
                         $objectFirstFeeOption=FeeOptionDBAccess::getFristFeeOptionByFeeId($value->FEE_ID); 
                         $duedays=$objectFirstFeeOption->DiffDate;
                         $dateline= $objectFirstFeeOption->END_DATE;        
                    }
                   
                    if($check){
                        $paid_status='';
                        switch ($value->PAID_STATUS){
                            case 'NOT_YET_PAID':
                                $paid_status=NOT_YET_PAID;
                            break;
                            case 'PAID':
                                $paid_status=PAID;
                            break;
                             case 'PARTLY_PAID':
                                $paid_status=PARTLY_PAID;
                            break;
                        } 
                        $data[$i]["CODE_ID"] = $value->CODE_ID;
                        $data[$i]["STUDENT_FEE_ID"] = $value->STUDENT_FEE_ID;
                        $data[$i]["STUDENT_CODE"] = $value->STUDENT_CODE;  
                        if(!SchoolDBAccess::displayPersonNameInGrid()){
                            $data[$i]["STUDENT_NAME"] = $value->LASTNAME." ".$value->FIRSTNAME;
                        }else{
                            $data[$i]["STUDENT_NAME"] = $value->FIRSTNAME." ".$value->LASTNAME;
                        }
                        $data[$i]["LASTNAME"] = $value->LASTNAME;
                        $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                        $data[$i]["EMAIL"] = $value->EMAIL;
                        $data[$i]["PHONE"] = $value->PHONE;
                        $data[$i]["GENDER"] = getGenderName($value->GENDER);
                        $data[$i]["TOTAL_AMOUNT"] = displayNumberFormat($value->TOTAL_AMOUNT)?displayNumberFormat($value->TOTAL_AMOUNT). " " . self::getCurrency() : "0 " . self::getCurrency();
                        $data[$i]["AMOUNT_PAID"] = displayNumberFormat($value->AMOUNT_PAID)?displayNumberFormat($value->AMOUNT_PAID). " " . self::getCurrency() : "0 " . self::getCurrency();
                        $data[$i]["PAID_STATUS"] = $paid_status;
                        $data[$i]["AMOUNT_OWED"] = displayNumberFormat($value->AMOUNT_OWED)?displayNumberFormat($value->AMOUNT_OWED). " " . self::getCurrency() : "0 " . self::getCurrency();
                        $data[$i]["START_DATE"] = $value->START_DATE;
                        $data[$i]["FIRST_DUE_DATE_A"] = $value->FIRST_DUE_DATE_A;
                        $data[$i]["CLASS_NAME"] = $value->CLASS_NAME?$value->CLASS_NAME:'???';
                        $data[$i]["DUE_DAY"]=$duedays;
                        $data[$i]["DUE_DATE"] = getShowDate($dateline);
                        if($value->CHOOSE_OPTION){
                            $feeOptionObject=FeeOptionDBAccess::getFeeOptionById($value->CHOOSE_OPTION);
                            $data[$i]["CHOOSE_OPTION"] =$feeOptionObject->NAME;   
                        }else{
                            $data[$i]["CHOOSE_OPTION"] = '---';   
                        }
                        
                    
                        if($objectPaidFee){
                           $data[$i]["NPAID"]=count($objectPaidFee);
                           $data[$i]["LAST_PAID_DATE"]=getShowDate($objectPaidFee[0]->CREATED_DATE);     
                        }else{
                           $data[$i]["NPAID"]='---';
                           $data[$i]["LAST_PAID_DATE"]='---';  
                        }
                        
                        $studentAlertObject=self::getStudentAlert($value->CODE_ID,$value->FEE_ID);
                        if($studentAlertObject){
                            $data[$i]["NALERT"]=count($studentAlertObject);
                            $data[$i]["LAST_ALERT_DATE"]=getShowDate($studentAlertObject[0]->ALERT_DATE);
                                
                        }else{
                            $data[$i]["NALERT"]='---';
                            $data[$i]["LAST_ALERT_DATE"]='---'; 
                        }
                     $i++;
                    }
                    
                   
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
        //
        
        public static function findStudentsByFeeID($params){
            
            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
            $feeId = isset($params["feeID"]) ? addText($params["feeID"]) : '';
            $schoolyear = isset($params["schoolyear"]) ? addText($params["schoolyear"]) : '';
            $studentReminds=isset($params["studentReminds"])?$params["studentReminds"]:'';
            
            //$ChildSchoolYear=FeeDBAccess::checkChildSchoolyearById($schoolyear);
            
            $SELECTION_A = array(
                "CODE AS STUDENT_CODE"
                , "ID AS CODE_ID"
                , "FIRSTNAME AS FIRSTNAME"
                , "LASTNAME AS LASTNAME"
                , "PHONE AS PHONE"
                , "EMAIL AS EMAIL"
                , "GENDER AS GENDER"
            );
        
            $SELECTION_B = array(
                "SCHOOLYEAR AS SCHOOLYEAR"
                , "ID AS STUDENT_FEE_ID"
                , "GRADE AS GRADE"
                , "TOTAL_AMOUNT AS TOTAL_AMOUNT"
                , "AMOUNT_PAID AS AMOUNT_PAID"
                , "PAID_STATUS AS PAID_STATUS"
                , "AMOUNT_OWED AS AMOUNT_OWED"
                , "CHOOSE_OPTION AS CHOOSE_OPTION"
                , "ACTION_PAYMENT AS ACTION_PAYMENT"
            );
            
            $SELECTION_C = array(
                "NAME AS FEE_NAME"
                , "ID AS FEE_ID"
                , "START_DATE AS START_DATE"
                , "END_DATE AS END_DATE"
                , "FIRST_DUE_DATE_A AS FIRST_DUE_DATE_A"
                , "SECOND_DUE_DATE_A AS SECOND_DUE_DATE_A"
                , "SECOND_DUE_DATE_B AS SECOND_DUE_DATE_B"
                , "THIRD_DUE_DATE_A AS THIRD_DUE_DATE_A"
                , "THIRD_DUE_DATE_B AS THIRD_DUE_DATE_B"
                , "THIRD_DUE_DATE_C AS THIRD_DUE_DATE_C"
                , "FOURTH_DUE_DATE_A AS FOURTH_DUE_DATE_A"
                , "FOURTH_DUE_DATE_B AS FOURTH_DUE_DATE_B"
                , "FOURTH_DUE_DATE_C AS FOURTH_DUE_DATE_C"
                , "FOURTH_DUE_DATE_D AS FOURTH_DUE_DATE_D"
                , "FIRST_AMOUNT AS FIRST_AMOUNT"
                , "SECOND_AMOUNT AS SECOND_AMOUNT"
                , "THIRD_AMOUNT AS THIRD_AMOUNT"
                , "FOURTH_AMOUNT AS FOURTH_AMOUNT"
                , "FIRST_TOTAL AS FIRST_TOTAL"
                , "SECOND_TOTAL AS SECOND_TOTAL"
                , "THIRD_TOTAL AS THIRD_TOTAL"
                , "FOURTH_TOTAL AS FOURTH_TOTAL"
            );
            
            $SELECTION_D = array(
                "SCHOOL_YEAR AS SCHOOLYEAR"
                , "GRADE AS GRADE"
                , "CLASS AS CLASS"
                , "CAMPUS AS CAMPUS"
            );
            
            $SELECTION_E = array(
                "NAME AS CLASS_NAME"
                , "TITLE AS TITLE"
            );
        
            
            $SQL = self::dbAccess()->select();
            
            
            $SQL->from(array('A' => 't_student'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_student_fee'), 'A.ID=B.STUDENT', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_fee'), 'B.FEE=C.ID', $SELECTION_C);
            $SQL->joinLeft(array('D' => 't_student_schoolyear'), 'B.STUDENT=D.STUDENT', $SELECTION_D);
            $SQL->joinLeft(array('E' => 't_grade'), 'D.CLASS=E.ID', $SELECTION_E);
            
            if($studentReminds){$SQL->where("B.PAID_STATUS != 'PAID'");}
            
            $SQL->where("B.FEE = '" . $feeId . "'");
            
            $SQL->where("B.SCHOOLYEAR = '" . $schoolyear . "'");
            
            if ($globalSearch) {
                
                $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
                $SEARCH .= " ) ";
                $SQL->where($SEARCH);
                
            }
            
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchAll($SQL);
            return $resultRows;  
            
        }
        
        public static function countFeePaid($studentId,$feeId){
        
            $SQL = "SELECT COUNT(*) AS NPAID";
            $SQL .= " FROM t_income";
            $SQL .= " WHERE";
            $SQL .= " STUDENT = '" . $studentId . "'";
            $SQL .= " AND FEE = '" . $feeId . "'";
            $SQL .= " AND PAYMENT_REMOVE = '0'";
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);

            return $result->NPAID;
            
        }
        
        public static function getLastPaidDate($studentId,$feeId){
        
            $SQL = "SELECT *";
            $SQL .= " FROM t_income";
            $SQL .= " WHERE";
            $SQL .= " STUDENT = '" . $studentId . "'";
            $SQL .= " AND FEE = '" . $feeId . "'";
            $SQL .= " AND PAYMENT_REMOVE = '0'";
            $SQL .= " ORDER BY CREATED_DATE DESC";
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);

            return $result;
            
        }
        
        public static function jsonLoadStudentFeeGrade($params){
        
            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            
            $result = self::findStudentsByFeeID($params);
            
            $data = array();
            $i = 0;
            if ($result) {
                foreach ($result as $value) {
                    $data[$i]["CODE_ID"] = $value->CODE_ID;
                    $data[$i]["STUDENT_FEE_ID"] = $value->STUDENT_FEE_ID;
                    $data[$i]["STUDENT_CODE"] = $value->STUDENT_CODE;          
                    if(!SchoolDBAccess::displayPersonNameInGrid()){
                        $data[$i]["STUDENT_NAME"] = $value->LASTNAME." ".$value->FIRSTNAME;
                    }else{
                        $data[$i]["STUDENT_NAME"] = $value->FIRSTNAME." ".$value->LASTNAME;
                    }
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["EMAIL"] = $value->EMAIL;
                    $data[$i]["PHONE"] = $value->PHONE;
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["TOTAL_AMOUNT"] = displayNumberFormat($value->TOTAL_AMOUNT)?displayNumberFormat($value->TOTAL_AMOUNT). " " . self::getCurrency() : "0 " . self::getCurrency();
                    
                    $data[$i]["AMOUNT_PAID"] = (StudentFeeDBAccess::findPaidAmounGeneral($value->CODE_ID,$value->FEE_ID))?displayNumberFormat(StudentFeeDBAccess::findPaidAmounGeneral($value->CODE_ID,$value->FEE_ID)). " " . self::getCurrency() : "0 " . self::getCurrency();
                    
                    if($value->PAID_STATUS){
                        $piadStatus='';
                        switch ($value->PAID_STATUS){
                            case "PAID":
                              $piadStatus=PAID;
                              break;
                            case "PARTLY_PAID":
                              $piadStatus=PARTLY_PAID;
                              break;
                            case "NOT_YET_PAID":
                              $piadStatus=NOT_YET_PIAD;
                              break;
                           
                        }
                    }
                    $data[$i]["PAID_STATUS"] = $piadStatus;//$value->PAID_STATUS;
                    $data[$i]["AMOUNT_OWED"] = displayNumberFormat($value->AMOUNT_OWED)?displayNumberFormat($value->AMOUNT_OWED). " " . self::getCurrency() : "0 " . self::getCurrency();
                    $data[$i]["START_DATE"] = $value->START_DATE;
                    $data[$i]["END_DATE"] = $value->END_DATE;
                    $data[$i]["FIRST_DUE_DATE_A"] = $value->FIRST_DUE_DATE_A;
                    $data[$i]["CLASS_NAME"] = $value->CLASS_NAME?$value->CLASS_NAME:'???';
                    
                    if($value->CHOOSE_OPTION){
                        $feeOptionObject=FeeOptionDBAccess::getFeeOptionById($value->CHOOSE_OPTION);
                        $data[$i]["CHOOSE_OPTION"] =$feeOptionObject->NAME;   
                    }else{
                        $data[$i]["CHOOSE_OPTION"] = '---';   
                    }
                    
                    
                    $NPAID=self::countFeePaid($value->CODE_ID,$value->FEE_ID);
                    $data[$i]["NPAID"]=($NPAID)?$NPAID:'---';
                    
                    $NALERT=self::countAlertStudent($value->CODE_ID,$value->FEE_ID);
                    $data[$i]["NALERT"]=($NALERT)?$NALERT:'---'; 
                                                                                   
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
        ///send reminder student
        
        public static function countAlertStudent($studentId,$feeId){
            
            $SQL = "SELECT COUNT(*) AS NALERT";
            $SQL .= " FROM t_user_payment_alert";
            $SQL .= " WHERE";
            $SQL .= " ALERT_USER = '" . $studentId . "'";
            $SQL .= " AND FEE_ID = '" . $feeId . "'";
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);

            return $result->NALERT;    
            
        }
        
         public static function getStudentAlert($studentId,$feeId){
            
            $SQL = "SELECT *";
            $SQL .= " FROM t_user_payment_alert";
            $SQL .= " WHERE";
            $SQL .= " ALERT_USER = '" . $studentId . "'";
            $SQL .= " AND FEE_ID = '" . $feeId . "'";
            $SQL .= " ORDER BY ALERT_DATE DESC";
            //error_log($SQL);
            $result = self::dbAccess()->fetchAll($SQL);

            return $result;  
            
        }
        
        public static function jsonSaveAlertFeeCommunication($params) {
            
            $send_recipients = isset($params["STR_RECIPIENT"]) ? $params["STR_RECIPIENT"]: "";
            $send_type=isset($params["SEND_TYPE"]) ? addText($params["SEND_TYPE"]) : "";
            $send_recipients_email=isset($params["STR_RECIPIENT_EMAIL"])?addText($params["STR_RECIPIENT_EMAIL"]): "";
            $send_recipients_phone=isset($params["STR_RECIPIENT_PHONE"])?$params["STR_RECIPIENT_PHONE"]: "";
            $subject=isset($params["SUBJECT"])?$params["SUBJECT"]: "";
            $content=isset($params["CONTENT"])?addText($params["CONTENT"]): "";
            $fee_ID=isset($params["fee"])?$params["fee"]:'';
            
            switch ($send_type){
                case "EMAIL":
                    $params["SUBJECT_EMAIL"]=$subject;
                    $params["CONTENT_EMAIL"]=$content;
                    break;
                case "SMS":
                    $params["CONTENT_PHONE"]=$content;
                    break;
                case "MESSAGE":
                    $params["SUBJECT_MESSAGE"]=$subject;
                    $params["CONTENT_MESSAGE"]=$content;
                    break;        
            }
            
            $param=array();
            $param['FEE_ID']=$fee_ID;
            $param['USER_TYPE']=isset($params["recipient_type"])?$params["recipient_type"]:'';
            
            $DB_communication=new CommunicationDBAccess();
            $newobjectId=$DB_communication->jsonSaveCommunication($params);
            
            $param['COMMUNICATION']=isset($newobjectId['objectId'])?$newobjectId['objectId']:'';
           
            $send_recipients_arr=explode(',', $send_recipients);
            foreach( $send_recipients_arr as $values){
                
                $param['RECIPIENT']=$values;
                self::actionStudentAlert($param);    
                
            }
            //send email and sms
            switch ($send_type){
                case "EMAIL":
                    $sendTo = $send_recipients_email;
                    $subject_email = $subject;
                    $content_eamil = $content;
                    $header = 'From: noreply@camemis.com' . "\r\n" .
                    'Reply-To: noreply@camemis.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                    mail($sendTo, $subject_email, $content_eamil, $header);
                    break;
                case "SMS":
                    $sendTo = $send_recipients_phone;
                    $content_phone = $content;
                    SendSMSDBAccess::curlSendSMS($sendTo, $content_phone);
                    self::addUserSMS($send_recipients,'STUDENT','FEE_ALERT',$fee_ID);
                    break;
            }  
            //
            
            return array("success" => true);
        }
        
        public static function addUserSMS($send_recipients,$recipient_type,$event_type,$fee_ID){
            
            
            $send_recipients_arr=explode(',', $send_recipients);
            foreach($send_recipients_arr as $values){
                $SAVEDATA=array();
                $SAVEDATA['USER_ID']= $values;
                $SAVEDATA['SEND_DATE']= getCurrentDBDate();
                $SAVEDATA['USER_TYPE']= $recipient_type;
                $SAVEDATA['EVENT_TYPE']= $event_type; 
                $SAVEDATA['SEND_BY']= Zend_Registry::get('USER')->ID;  
                $SAVEDATA['OBJECT_ID']= $fee_ID;
                self::dbAccess()->insert('t_user_sms', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
                
            }    
                
        }
        
        public static function actionStudentAlert($param){
            
            $communicationId=isset($param['COMMUNICATION'])?$param['COMMUNICATION']:'';
            $feeId=isset($param['FEE_ID'])?$param['FEE_ID']:'';
            $recipient=isset($param['RECIPIENT'])?$param['RECIPIENT']:'';
            $userType=isset($param['USER_TYPE'])?$param['USER_TYPE']:'';
            
            $SAVEDATA = array();
            
            $SAVEDATA['FEE_ID']=$feeId;
            $SAVEDATA['COMMUNICATION']=$communicationId;
            $SAVEDATA['SENDER']=Zend_Registry::get('USER')->ID;
            $SAVEDATA['ALERT_DATE']=getCurrentDBDateTime();
            $SAVEDATA['ALERT_USER']=$recipient;
            $SAVEDATA['USER_TYPE']=$userType;
            
            self::dbAccess()->insert('t_user_payment_alert', $SAVEDATA);
            $objectId = self::dbAccess()->lastInsertId();
            
            return array("success" => true,"objectId" => $objectId); 
        }
        
        //training
        public static function gettrainingbyFee($feeId){
            
            $SELECTION_A = array(
                "TRAINING AS TRAINING"
            );
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_fee_training'), $SELECTION_A); 
            
            $SQL->where("A.FEE = '" . $feeId . "'");
             //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchAll($SQL);
            
            $data = array();
            if ($resultRows) {
                foreach ($resultRows as $value) {  
                     $data[]=$value->TRAINING;   
                }
            }
                   
           return  $data;
        }
        
         public static function findStudentsTrainingFee($params){
             
            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
            $feeId = isset($params["feeID"]) ? addText($params["feeID"]) : '';
           
            $trainingArray = self::gettrainingbyFee($feeId);
            $trainingId=implode(",", $trainingArray);
         
            $SELECTION_A = array(
                "CODE AS STUDENT_CODE"
                , "ID AS CODE_ID"
                , "FIRSTNAME AS FIRSTNAME"
                , "LASTNAME AS LASTNAME"
                , "PHONE AS PHONE"
                , "EMAIL AS EMAIL"
                , "GENDER AS GENDER"
            );
            
            $SELECTION_B = array(
                "SCHOOLYEAR AS SCHOOLYEAR"
                , "ID AS STUDENT_FEE_ID"
                , "GRADE AS GRADE"
                , "TOTAL_AMOUNT AS TOTAL_AMOUNT"
                , "AMOUNT_PAID AS AMOUNT_PAID"
                , "PAID_STATUS AS PAID_STATUS"
                , "AMOUNT_OWED AS AMOUNT_OWED"
                , "CHOOSE_OPTION AS CHOOSE_OPTION"
                , "ACTION_PAYMENT AS ACTION_PAYMENT"
            );
            
            $SELECTION_C = array(
                "NAME AS FEE_NAME"
                , "START_DATE AS START_DATE"
                , "END_DATE AS END_DATE"
                , "FIRST_DUE_DATE_A AS FIRST_DUE_DATE_A"
                , "SECOND_DUE_DATE_A AS SECOND_DUE_DATE_A"
                , "SECOND_DUE_DATE_B AS SECOND_DUE_DATE_B"
                , "THIRD_DUE_DATE_A AS THIRD_DUE_DATE_A"
                , "THIRD_DUE_DATE_B AS THIRD_DUE_DATE_B"
                , "THIRD_DUE_DATE_C AS THIRD_DUE_DATE_C"
                , "FOURTH_DUE_DATE_A AS FOURTH_DUE_DATE_A"
                , "FOURTH_DUE_DATE_B AS FOURTH_DUE_DATE_B"
                , "FOURTH_DUE_DATE_C AS FOURTH_DUE_DATE_C"
                , "FOURTH_DUE_DATE_D AS FOURTH_DUE_DATE_D"
                , "FIRST_AMOUNT AS FIRST_AMOUNT"
                , "SECOND_AMOUNT AS SECOND_AMOUNT"
                , "THIRD_AMOUNT AS THIRD_AMOUNT"
                , "FOURTH_AMOUNT AS FOURTH_AMOUNT"
                , "FIRST_TOTAL AS FIRST_TOTAL"
                , "SECOND_TOTAL AS SECOND_TOTAL"
                , "THIRD_TOTAL AS THIRD_TOTAL"
                , "FOURTH_TOTAL AS FOURTH_TOTAL"
            );
            
            $SELECTION_D = array(
                "TRAINING AS TRAINING"
            );
            
            $SELECTION_E = array(
                "NAME AS TRAINING_NAME"
            ); 

            $SQL = self::dbAccess()->select();

            $SQL->from(array('A' => 't_student'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_student_fee'), 'A.ID=B.STUDENT', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_fee'), 'B.FEE=C.ID', $SELECTION_C);
            $SQL->joinLeft(array('D' => 't_student_training'), 'A.ID=D.STUDENT', $SELECTION_D);
            $SQL->joinLeft(array('E' => 't_training'), 'D.TRAINING=E.ID', $SELECTION_E);

            $SQL->where("B.TRAINING in (" . $trainingId . ")");
            
            if ($globalSearch) {
                
                $SEARCH = " ((A.LASTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (A.FIRSTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (A.CODE LIKE '" . strtoupper($globalSearch) . "%')";
                $SEARCH .= " ) ";
                $SQL->where($SEARCH);
                
            }
            
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchAll($SQL);
            return $resultRows;    
        
         }
         
         
        public static function jsonLoadStudentFeeTraining($params){
    
            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $feeId = isset($params["feeID"]) ? addText($params["feeID"]) : '';
            
            $result = self::findStudentsTrainingFee($params);

            $data = array();
            $i = 0;
            if ($result) {
                foreach ($result as $value) {
                    $data[$i]["STUDENT_CODE"] = $value->STUDENT_CODE;  
                    if(!SchoolDBAccess::displayPersonNameInGrid()){
                        $data[$i]["STUDENT_NAME"] = $value->LASTNAME." ".$value->FIRSTNAME;
                    }else{
                        $data[$i]["STUDENT_NAME"] = $value->FIRSTNAME." ".$value->LASTNAME;
                    }
                    $data[$i]["LASTNAME"] = $value->LASTNAME;
                    $data[$i]["FIRSTNAME"] = $value->FIRSTNAME;
                    $data[$i]["EMAIL"] = $value->EMAIL;
                    $data[$i]["PHONE"] = $value->PHONE;
                    $data[$i]["GENDER"] = getGenderName($value->GENDER);
                    $data[$i]["TRAINING_NAME"] = $value->TRAINING_NAME;
                    $data[$i]["FIRST_DUE_DATE_A"] = $value->FIRST_DUE_DATE_A;
                    
                    $data[$i]["TOTAL_AMOUNT"] = displayNumberFormat($value->TOTAL_AMOUNT);
                    $data[$i]["AMOUNT_PAID"] = displayNumberFormat($value->AMOUNT_PAID);
                    $data[$i]["PAID_STATUS"] = $value->PAID_STATUS;
                    $data[$i]["AMOUNT_OWED"] = displayNumberFormat($value->AMOUNT_OWED);
                    $data[$i]["START_DATE"] = $value->START_DATE;
                    $data[$i]["END_DATE"] = $value->END_DATE;
                    
                    
                    $i++;
                }
            }
            //error_log('run');
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
          
        //
       
      
    }

?>