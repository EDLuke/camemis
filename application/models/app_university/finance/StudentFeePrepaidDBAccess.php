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
    require_once 'models/app_university/finance/FeeOptionDBAccess.php';
    require_once 'models/app_university/training/TrainingDBAccess.php';
    require_once 'models/app_university/CommunicationDBAccess.php';
    //require_once 'models/app_university/student/StudentFeeDBAccess.php';
    require_once setUserLoacalization();

    class StudentFeePrepaidDBAccess {
        
        public $data = array();
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
        
         public static function findFeePrePayment($params){
        
            $code = isset($params['code'])?$params['code']:'';
            $name = isset($params['FEE_NAME'])?$params['FEE_NAME']:'';
            $type = isset($params["type"]) ? $param["type"] : "";
            $campusId = isset($params["campusId"])?$params["campusId"]:'';
            $programId =isset($params["programId"])?$params["programId"]:'';
            
            $SELECTION_A = array(
                "ID AS ID"
                , "CODE AS CODE"
                , "FEE_NAME AS FEE_NAME"
                , "AMOUNT AS AMOUNT"
                , "INCOME_CATAGORY"
                , "DESCRIPTION AS DESCRIPTION"
                , "SCHOOLYEAR_STR AS SCHOOLYEAR_STR"
                , "CREATED_DATE"
                , "CREATED_BY"
                , "MODIFY_DATE"
                , "MODIFY_BY"
            );
            
            $SELECTION_B = array(
                "ID AS INCOM_CATAGORY_ID"
                , "NAME AS INCOME_CATAGORY_NAME"
                , "ACCOUNT_NUMBER AS ACCOUNT_NUMBER"
                , "TAX AS TAX"
                , "DESCRIPTION AS INCOM_CATAGORY_DESCRIPTION"
                
            );
            
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_fee_prepayment'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_income_category'), 'B.ID=A.INCOME_CATAGORY', $SELECTION_B);
            if($campusId){
                $SQL->where("A.CAMPUS_ID=".$campusId);
            }
            if($programId){
                $SQL->where("A.PROGRAM=".$programId);    
            }
            if ($code)
                $SQL->where("A.CODE LIKE 'FEE-".$code."%'");
            
            if ($name)
                $SQL->where("A.NAME LIKE '" . $name . "%'");

            $SQL->order('A.CREATED_DATE DESC'); 
            
           //error_log($SQL->__toString());  
            $resultRows = self::dbAccess()->fetchAll($SQL);
            
            return  $resultRows; 
        
        }

        public static function jsonSearchFeePrePayment($params) {

            $start = isset($params["start"]) ? $params["start"] : "0";
            $limit = isset($params["limit"]) ? $params["limit"] : "50";
            
            $result = self::findFeePrePayment($params);
            
            $data = array();
            $i=0;
            
            if($result){
               
               foreach($result as $value){
                    $data[$i]["ID"] = $value->ID;
                    $data[$i]["FEE_NAME"] = "(".setShowText($value->CODE).") ".setShowText($value->FEE_NAME);
                    $data[$i]["FEES"] =displayNumberFormat($value->AMOUNT);
                    $data[$i]["TAX_CATEGORY"] =displayNumberFormat($value->ACCOUNT_NUMBER);
                    $data[$i]["CREATED_DATE"] =getShowDateTime($value->CREATED_DATE);
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

        public static function findFeePrePaymentById($Id){
            
            $SQL = self::dbSelectAccess();
            $SQL->from('t_fee_prepayment', array('*'));
            $SQL->where("ID='".$Id."'");    
            $resultRows = self::dbAccess()->fetchRow($SQL);
            
            return  $resultRows;
        }
        
        public static function jsonLoadFeePrePayment($params){
            
            $objectId=isset($params['objectId'])?$params['objectId']:'';
            $campusId=isset($params['campusId'])?$params['campusId']:'';
            $program=isset($params['program'])?$params['program']:'';
            $facette = self::findFeePrePaymentById($objectId);
            
            $data = array();
            
            if($facette){
                
                $CATEGORY_OBJECT = IncomeCategoryDBAccess::findObjectFromId($facette->INCOME_CATAGORY);
                
                $data['STATUS']=$facette->STATUS;
                $data['NAME']=$facette->FEE_NAME;
                $data['FEES']=displayNumberFormat($facette->AMOUNT);
                $data["CHOOSE_INCOME_CATEGORY_NAME"] = $CATEGORY_OBJECT->NAME . " (" . $CATEGORY_OBJECT->ACCOUNT_NUMBER . ")";
                $data['HIDDEN_INCOME_CATEGORY']=$facette->INCOME_CATAGORY; 
                $data['DESCRIPTION']=$facette->DESCRIPTION;
                $data['START_DATE']=$facette->START_DATE;
                $data['END_DATE']=$facette->END_DATE;
                
                if($facette->SCHOOLYEAR_STR){
                    $schoolyear_str = $facette->SCHOOLYEAR_STR;
                    $schoolyear_arr=explode(",", $schoolyear_str);
                }
                if($facette->TERM){
                    $schoolyear_str = $facette->TERM;
                    $schoolyear_arr=explode(",", $schoolyear_str);
                }
                foreach($schoolyear_arr as $values){
                    $data['CHECKBOX_'.$values]=true;        
                }
               
                $o = array(
                    "success" => true
                    , "data" => $data
                );       
            }else{
                $o = array(
                    "success" => true
                    , "data" => array()
                );
            }
            
            return $o; 
        }
        
        public static function findStudentPrePaymentByFeePrePay($ID){
        
            $SQL = self::dbSelectAccess();
            $SQL->from('t_student_prepayment', array('*'));
            $SQL->where("FEE_PREPAYMENT_ID='".$ID."'");    
            $resultRows = self::dbAccess()->fetchAll($SQL);
            
            return  $resultRows;   
            
        }
         
        
        public static function jsonSaveFeePrePayment($params){
          
            $CHECKBOX_DATA = array();
          
            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : "";
            $campusId = isset($params["campusId"]) ? addText($params["campusId"]) : "";
            $program=isset($params['program'])?$params['program']:'';
            $FEE_NAME = isset($params["NAME"]) ? addText($params["NAME"]) : "";
            $FEE_AMOUNT = isset($params["FEES"]) ? addText($params["FEES"]) : ""; 
            $DESCRIPTION = isset($params["DESCRIPTION"]) ? addText($params["DESCRIPTION"]) : "";
            $INCOME_CATEGORY = isset($params["HIDDEN_INCOME_CATEGORY"]) ? $params["HIDDEN_INCOME_CATEGORY"] : "";
            $start_date=isset($params['START_DATE'])?$params['START_DATE']:'';
            $end_date=isset($params['END_DATE'])?$params['END_DATE']:'';
           
           if($program){
                $object_school_year = self::findAllTermTraining();
           }else{
                $object_school_year = self::findAllFuturSchoolYear();
           }
            foreach($object_school_year as $values){
                
                $CHECKBOX = isset($params["CHECKBOX_" . $values->ID . ""]) ? $params["CHECKBOX_" . $values->ID . ""] : ""; 
                
                if ($CHECKBOX == "on") {
                        $CHECKBOX_DATA[$values->ID] = $values->ID;
                    }      
            } 
              
           if($objectId=='new'){
               
                $SAVEDATA["CODE"] = self::getFeeCode();
                $SAVEDATA["FEE_NAME"] = $FEE_NAME;
                $SAVEDATA["AMOUNT"]= $FEE_AMOUNT;
                $SAVEDATA["INCOME_CATAGORY"] = $INCOME_CATEGORY;
                if($campusId){
                    $SAVEDATA["SCHOOLYEAR_STR"] = implode(",", $CHECKBOX_DATA);
                    $SAVEDATA["CAMPUS_ID"] = $campusId;
                }
                if($program){
                    $SAVEDATA["TERM"] = implode(",", $CHECKBOX_DATA);
                    $SAVEDATA["PROGRAM"] = $program;
                    $SAVEDATA["START_DATE"]=setDate2DB($start_date);
                    $SAVEDATA["END_DATE"]=setDate2DB($end_date);
                }
                 
                $SAVEDATA["DESCRIPTION"] = $DESCRIPTION;
                $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
                self::dbAccess()->insert('t_fee_prepayment', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
                 
            }else{
                
                $SAVEDATA["FEE_NAME"] = $FEE_NAME;
                $SAVEDATA["AMOUNT"]= $FEE_AMOUNT;
                $SAVEDATA["INCOME_CATAGORY"] = $INCOME_CATEGORY;
                if($campusId){    
                    $SAVEDATA["SCHOOLYEAR_STR"] = implode(",", $CHECKBOX_DATA);
                }
                if($program){
                    $SAVEDATA["TERM"] = implode(",", $CHECKBOX_DATA);
                    $SAVEDATA["START_DATE"]=setDate2DB($start_date);
                    $SAVEDATA["END_DATE"]=setDate2DB($end_date);
                }
                $SAVEDATA["DESCRIPTION"] = $DESCRIPTION;  
                $SAVEDATA['MODIFY_DATE'] = getCurrentDBDateTime();
                $SAVEDATA['MODIFY_BY'] = Zend_Registry::get('USER')->CODE; 
                
                $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                self::dbAccess()->update('t_fee_prepayment', $SAVEDATA, $WHERE);
                
                $studentPrePayObject=self::findStudentPrePaymentByFeePrePay($objectId);
                
                if($studentPrePayObject){
                    if($campusId){
                        foreach($studentPrePayObject as $values){
                            $SAVE['SCHOOLYEAR_STR']=implode(",", $CHECKBOX_DATA);
                            $WHERE = self::dbAccess()->quoteInto("ID = ?", $values->ID);
                            self::dbAccess()->update('t_student_prepayment', $SAVE, $WHERE);
                        }
                    }
                    if($program){
                        foreach($studentPrePayObject as $values){
                            $SAVE['TERM_STR']=implode(",", $CHECKBOX_DATA);
                            $WHERE = self::dbAccess()->quoteInto("ID = ?", $values->ID);
                            self::dbAccess()->update('t_student_prepayment', $SAVE, $WHERE);
                        }
                    }      
                }
                 
            } 
            
            return array("success" => true, "objectId" => $objectId);
            
        }

        public static function removeFeePrePayment($Id) {

            self::dbAccess()->delete('t_fee_prepayment', array("ID = '" . $Id . "'"));

            return array("success" => true);
        }
        
        public static function jsonReleaseFeePrePayment($params) {

            $SAVEDATA = array();

            $objectId = isset($params["objectId"]) ? addText($params["objectId"]) : 0;
            $facette = self::findFeePrePaymentById($objectId);
            $newStatus = 0;

            if ($facette) {
                switch ($facette->STATUS) {
                    case 0:
                        $newStatus = 1;
                        $SAVEDATA["STATUS"] = 1;
                        $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                        self::dbAccess()->update('t_fee_prepayment', $SAVEDATA, $WHERE);
                        break;
                    case 1:
                        $newStatus = 0;
                        $SAVEDATA["STATUS"] = 0;
                        $WHERE = self::dbAccess()->quoteInto("ID = ?", $objectId);
                        self::dbAccess()->update('t_fee_prepayment', $SAVEDATA, $WHERE);
                        break;
                }
            }

            return array("success" => true, "status" => $newStatus);
        }

        public static function findAllFuturSchoolYear(){
             
             $SQL = self::dbSelectAccess();
             $SQL->from('t_academicdate', array('*'));
             $SQL->where('IS_CHILD=0');
             $SQL->where('END >now()');
             $resultRows = self::dbAccess()->fetchAll($SQL);
             
             return  $resultRows;
         }
         
         public static function findAllTermTraining(){
             
             $SQL = self::dbSelectAccess();
             $SQL->from("t_training", array("*"));
             $SQL->where("OBJECT_TYPE='TERM'");
             $SQL->where("END_DATE>now()");
             $resultRows = self::dbAccess()->fetchAll($SQL);
             
             return  $resultRows;
         }
         
        public static function getFeeCode() {

            $SQL = self::dbAccess()->select();
            $SQL->from("t_fee_prepayment", array("CODE"));
            $SQL->order('CODE DESC');
            $SQL->limit(1);
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            if ($result) {
                $cal = substr($result->CODE, 8) + 1;
                return "PRE_FEE-" . $cal;
            } else {
                return "PRE_FEE-100";
            }
        }
         
        public static function findStudentSchoyearAndCampus($studentId){

            $SQL = self::dbSelectAccess();
            $SQL->from(array('A' => 't_student_schoolyear'), array("CAMPUS", "GRADE", "CLASS", "SCHOOL_YEAR"));
            $SQL->joinLeft(array('B' => 't_academicdate'), 'B.ID=A.SCHOOL_YEAR', array(""));
            $SQL->where("A.STUDENT = '" . $studentId . "'");
            $SQL->where("B.END > now()");
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchRow($SQL);
            return $resultRows;
        }
        
        public static function findStudentTermAndProgram($studentId){

            $SQL = self::dbSelectAccess();
            $SQL->from(array('A' => 't_training'), array("PROGRAM", "LEVEL", "TERM"));
            $SQL->joinLeft(array('B' => 't_student_training'), 'B.TRAINING=A.ID', array("TRAINING"));
            $SQL->where("B.STUDENT = '" . $studentId . "'");
            $SQL->where("A.END_DATE > now()");
            $SQL->order('A.END_DATE ASC');
            //error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchRow($SQL);
            return $resultRows;
        }
        
        public static function findPrePaymentByCampus($campus,$training=false){
        
            $SELECTION_A = array(
                "CODE"
                , "ID"
                , "FEE_NAME"
                , "AMOUNT"
                , "SCHOOLYEAR_STR"
                , "PROGRAM"
                , "TERM AS TERM_STR"
                , "CAMPUS_ID"
                , "DESCRIPTION"
                , "STATUS"
                , "START_DATE"
                , "END_DATE"
            );
            $SELECTION_B = array(
                    "NAME"
                    , "ACCOUNT_NUMBER"
                );
            $SELECTION_C = array(
                    "NAME AS TAX_NAME"
                    , "NUMBER"
                    , "PERCENT"
                    , "FORMULAR"
                );
        
            $SQL = self::dbSelectAccess();
            $SQL->from(array('A' => 't_fee_prepayment'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_income_category'), 'A.INCOME_CATAGORY=B.ID', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_tax'), 'C.ID=B.TAX', $SELECTION_C);
            if($training){
                $SQL->where("A.PROGRAM = '" . $campus . "'");    
            }else{
                $SQL->where("A.CAMPUS_ID = '" . $campus . "'");
            }
            $SQL->where("A.STATUS = 1");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchAll($SQL);
            return $result;    
                
        }
    
    ////
    
        public static function findAllStudentPrePayment(){
             
             $SQL = self::dbSelectAccess();
             $SQL->from(array('t_student_prepayment'), array('*'));    
             $result = self::dbAccess()->fetchAll($SQL);
             return $result;
        }
        
        public static function findStudentPrePayment($studentId,$training=false){
            
            $result='';
            if($training){
                $object=self::findStudentTermAndProgram($studentId);
                if($object){
                    $SQL = self::dbSelectAccess();
                    $SQL->from(array('t_student_prepayment'), array('*'));
                    $SQL->where("STUDENT_ID = '" . $studentId . "'");
                    $SQL->where("PROGRAM = '" . $object->PROGRAM . "'");
                    //error_log($SQL->__toString());    
                    $facette= self::dbAccess()->fetchAll($SQL);
                    if($facette){
                        foreach($facette as $value){
                            $term_str=$value->TERM_STR;
                            $term_arr=explode(",",$term_str);
                            foreach($term_arr as $term){
                                if($term==$object->TERM){
                                    $result=$value;   
                                }    
                            }        
                        }
                    }
                }       
            }else{
                
                $object=self::findStudentSchoyearAndCampus($studentId);
                $SQL = self::dbSelectAccess();
                $SQL->from(array('t_student_prepayment'), array('*'));
                $SQL->where("STUDENT_ID = '" . $studentId . "'");
                if(isset($object->CAMPUS))
                $SQL->where("CAMPUS_ID = '" . $object->CAMPUS . "'");   
                if(isset($object->PROGRAM))
                $SQL->where("PROGRAM = '" . $object->PROGRAM . "'");    
                $facette= self::dbAccess()->fetchAll($SQL);
                $result='';
                if($facette){
                    foreach($facette as $value){
                        $schoolyear_str=$value->SCHOOLYEAR_STR;
                        $schoolyear_arr=explode(",",$schoolyear_str);
                        foreach($schoolyear_arr as $schoolyear){
                            if($schoolyear==$object->SCHOOL_YEAR){
                                $result=$value;   
                            }    
                        }        
                    }
                }
            }
                
           return $result; 
        }
        
        public static function removeStudentFeePrePayment($studentFeePrePaymentId){
             self::dbAccess()->delete('t_student_prepayment', array("ID = '" . $studentFeePrePaymentId . "'"));
             return array("success" => true);    
        }
        
        public static function findStudentPrePaymentById($Id){
            
            $SELECTION_A = array(
                    "STUDENT_ID"
                );
            $SELECTION_B = array(
                    "CODE"
                    , "FEE_NAME"
                    , "AMOUNT"
                    , "PROGRAM"
                    , "TERM AS TERM_STR"
                    , "SCHOOLYEAR_STR"
                    , "CAMPUS_ID"
                    , "DESCRIPTION"
                );
            $SELECTION_C = array(
                    "NAME AS INCOME_CATAAGORY_NAME"
                    , "ACCOUNT_NUMBER"
                    , "ID AS INCOME_CATAAGORY"
                    
                );
            $SELECTION_D = array(
                    "NAME AS TAX_NAME"
                    , "NUMBER"
                    , "PERCENT"
                    , "FORMULAR"
                );
        
            $SQL = self::dbSelectAccess();
            $SQL->from(array('A' => 't_student_prepayment'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_fee_prepayment'), 'A.FEE_PREPAYMENT_ID=B.ID', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_income_category'), 'B.INCOME_CATAGORY=C.ID', $SELECTION_C);
            $SQL->joinLeft(array('D' => 't_tax'), 'C.TAX=D.ID', $SELECTION_D);
            
            $SQL->where("A.ID = '" . $Id . "'");
            error_log($SQL->__toString());
            $resultRows = self::dbAccess()->fetchRow($SQL);
             return $resultRows;
            
        }
        
        public static function jsonLoadStudentPrePayment($params){
            
            $studentPrePaymentId=isset($params['studentPrePaymentId'])?$params['studentPrePaymentId']:'';
            $feePrePayment=isset($params['feePrePaymentId'])?$params['feePrePaymentId']:'';
            $studentId=isset($params['objectId'])?$params['objectId']:'';
            $training=isset($params['training'])?$params['training']:'false';
            //error_log('dfsfsdfsdf');
            $facette=self::findStudentPrePaymentById($studentPrePaymentId);
            
            $CHECK=self::checkIncomePrePayment($studentId, $feePrePayment);
            
            if($facette){
                $data = array();
                    
                $data['NAME']=$facette->FEE_NAME;
                $data['FEES']=displayNumberFormat($facette->AMOUNT)." ".Zend_Registry::get('SCHOOL')->CURRENCY;
                $data['HIDDEN_FEES_AMOUNT']=displayNumberFormat($facette->AMOUNT);
                
                
                $taxAmount= ($facette->AMOUNT*$facette->PERCENT)/100;
                $total=$facette->AMOUNT;
                if($facette->FORMULAR==1){
                    $total+=$taxAmount;       
                }else{
                    $total=$facette->AMOUNT;
                }
                
                if($CHECK){
                
                    $data['DISCOUNT']=displayNumberFormat($CHECK->DISCOUNT_PERCENT);
                    $data['DISCOUNT_AMOUNT']=displayNumberFormat($CHECK->DISCOUNT)." ".Zend_Registry::get('SCHOOL')->CURRENCY;
                    $data['TOTAL_PAY']=displayNumberFormat($CHECK->INCOME_AMOUNT)." ".Zend_Registry::get('SCHOOL')->CURRENCY;
                    $data['TAX_AMOUNT']=displayNumberFormat($CHECK->AMOUNT_TAX)." ".Zend_Registry::get('SCHOOL')->CURRENCY;
                    $data['DESCRIPTION']=$CHECK->DESCRIPTION;
                    
                    $CHECK_DATA = explode(",", $CHECK->FINANCE_DESCRIPTION);

                    $SQL = self::dbAccess()->select();
                    $SQL->from("t_finance_description", array('*'));
                    $result = self::dbAccess()->fetchAll($SQL);

                    if ($result) {
                        foreach ($result as $value) {

                            $descriptionObject = FinanceDescriptionDBAccess::findObjectFromId($value->ID);
                            switch ($value->CHOOSE_TYPE) {
                                case 1:
                                    if (in_array($value->ID, $CHECK_DATA)) {
                                        $data["CHECKBOX_" . $value->ID] = true;
                                    } else {
                                        $data["CHECKBOX_" . $value->ID] = false;
                                    }

                                    break;
                                case 2:
                                    if (in_array($value->ID, $CHECK_DATA)) {
                                        $data["RADIOBOX_" . $descriptionObject->PARENT] = $value->ID;
                                    }
                                    break;
                            }
                        }
                    }
                
                }else{
                    $data['TAX_AMOUNT']=displayNumberFormat($taxAmount)." ".Zend_Registry::get('SCHOOL')->CURRENCY;
                    $data['HIDDEN_TAX_AMOUNT']=$taxAmount;
                    $data['HIDDEN_TAX_PERCENT']=$facette->PERCENT;
                    $data['HIDDEN_TAX_FORMULAR']=$facette->FORMULAR; 
                    $data['HIDDEN_INCOME_CATAGORY']=$facette->INCOME_CATAAGORY;
                    $data['TOTAL_PAY']=displayNumberFormat($total)." ".Zend_Registry::get('SCHOOL')->CURRENCY;
                    $data['HIDDEN_TOTAL_PAY']=$total;
                        
                }
            
                if($training=='true'){
                    
                    $term_arr=explode(",",$facette->TERM_STR);
                    $term_str='';
                    foreach($term_arr as $values){
                        $SQL = self::dbAccess()->select();
                        $SQL->from("t_training", array('*'));
                        $SQL->where("ID = '" . $values . "'");
                        $result = self::dbAccess()->fetchRow($SQL);        
                        error_log($SQL->__toString());
                        $term_str.= $result->START_DATE." - ".$result->END_DATE."<br/>";
                      
                    }
                    $data['SCHOOL_YEAR']=$term_str;
                    $data['HIDDEN_SCHOOL_YEAR']=$facette->TERM_STR;    
                }else{
                    $schoolyear_arr=explode(",",$facette->SCHOOLYEAR_STR);
                    $scholyear_str='';
                    foreach($schoolyear_arr as $values){
                        $SQL = self::dbAccess()->select();
                        $SQL->from("t_academicdate", array('*'));
                        $SQL->where("ID = '" . $values . "'");
                        $result = self::dbAccess()->fetchRow($SQL);        
                       
                        $scholyear_str.= $result->NAME."<br/>";
                      
                    }
                    $data['SCHOOL_YEAR']=$scholyear_str;
                    $data['HIDDEN_SCHOOL_YEAR']=$facette->SCHOOLYEAR_STR;
                }
                
                $o = array(
                        "success" => true
                        , "data" => $data
                    );    
            }else {
                    $o = array(
                        "success" => true
                        , "data" => array()
                    );
                }
            return $o;
                
        }
        
        ///check coding
        
       public static function checkIncomePrePayment($studentId, $feePrePaymentId){
            
            $SQL = self::dbAccess()->select();
            $SQL->from("t_income", array("*"));
            $SQL->where("STUDENT = '" . $studentId . "'");
            $SQL->where("FEE_PREPAYMENT = '" . $feePrePaymentId . "'");
            //error_log($SQL->__toString());
            $result = self::dbAccess()->fetchRow($SQL);
            return $result;
                
       }
       
       public static function checkStudentPrePayPaid($studentId){
           
           $CHECK=false;
           $PRE_PAY=false;
           
           $object=self::findStudentPrePayment($studentId);
           if($object){
                $PRE_PAY=true;
                $facette=self::checkIncomePrePayment($studentId, $object->FEE_PREPAYMENT_ID);
                if($facette){
                   $CHECK=true;    
                }    
           }
           
         return array('CHECK'=>$CHECK,'PRE_PAY'=>$PRE_PAY);  
       }
        
       public static function jsonActionStudentPrePayment($params){
        
            $studentId=isset($params['objectId'])?$params['objectId']:'';
            $income_amount=isset($params['HIDDEN_TOTAL_PAY'])?$params['HIDDEN_TOTAL_PAY']:'';
            $amount=isset($params['HIDDEN_FEES_AMOUNT'])?$params['HIDDEN_FEES_AMOUNT']:'';
            $taxAmount=isset($params['HIDDEN_TAX_AMOUNT'])?$params['HIDDEN_TAX_AMOUNT']:'';
            $discount_amount=isset($params['HIDDEN_DISCOUNT_AMOUNT'])?$params['HIDDEN_DISCOUNT_AMOUNT']:'';
            $discount_percent=isset($params['DISCOUNT'])?$params['DISCOUNT']:'';
            $taxFormular=isset($params['HIDDEN_TAX_FORMULAR'])?$params['HIDDEN_TAX_FORMULAR']:'';
            $feePrePaymentId=isset($params['feePrePaymentId'])?$params['feePrePaymentId']:'';
            $incomeCatagory=isset($params['HIDDEN_INCOME_CATAGORY'])?$params['HIDDEN_INCOME_CATAGORY']:'';
            $description=isset($params['DESCRIPTION'])?$params['DESCRIPTION']:'';
            $studentFeePrePaymentId=isset($params['studentPrePaymentId'])?$params['studentPrePaymentId']:'';
            
            $RADIOBOX_DATA = array();
            $CHECKBOX_DATA = array();
           
            $CHECK=self::checkIncomePrePayment($studentId, $feePrePaymentId);
            
            $SAVEDATA["GUID"] = generateGuid();
            if ($studentId) {
                $studentObject = StudentDBAccess::findStudentFromId($studentId);
                $SAVEDATA["NAME"] = $studentObject->LASTNAME . " " . $studentObject->FIRSTNAME . " (" . $studentObject->CODE . ")";
            }
            $SAVEDATA["TRANSACTION_NUMBER"] = StudentFeeDBAccess::getTNIncome();
            $SAVEDATA["INCOME_AMOUNT"]= addText($income_amount);
            $SAVEDATA["AMOUNT"]= addText($amount);
            $SAVEDATA['AMOUNT_TAX'] = addText($taxAmount);
            $SAVEDATA['DISCOUNT'] = addText($discount_amount);
            $SAVEDATA['DISCOUNT_PERCENT'] = addText($discount_percent);
            $SAVEDATA['FORMULAR_TAX'] = addText($taxFormular);
            
            $SAVEDATA["INCOME_CATEGORY"] = addText($incomeCatagory);
          
            $SAVEDATA["FEE_PREPAYMENT"] = addText($feePrePaymentId); 
            $SAVEDATA["STUDENT"] = addText($studentId);
            $SAVEDATA["DESCRIPTION"] = addText($description);
            
            ////////////////////////////////////////////////////////////////////////
            //Description....
            ////////////////////////////////////////////////////////////////////////
            $SQL = self::dbAccess()->select();
            $SQL->from("t_finance_description", array('*'));
            $result = self::dbAccess()->fetchAll($SQL);
            //error_log($SQL->__toString());
            if ($result) {
                foreach ($result as $value) {

                    $CHECKBOX = isset($params["CHECKBOX_" . $value->ID . ""]) ? $params["CHECKBOX_" . $value->ID . ""] : "";
                    $RADIOBOX = isset($params["RADIOBOX_" . $value->ID . ""]) ? $params["RADIOBOX_" . $value->ID . ""] : "";

                    if ($RADIOBOX) {
                        $RADIOBOX_DATA[$RADIOBOX] = $RADIOBOX;
                    }

                    if ($CHECKBOX == "on") {
                        $CHECKBOX_DATA[$value->ID] = $value->ID;
                    }
                }
            }

            $CHOOSE_DATA = $CHECKBOX_DATA + $RADIOBOX_DATA;
            $SAVEDATA["FINANCE_DESCRIPTION"] = implode(",", $CHOOSE_DATA);
            ////////////////////////////////////////////////////////////////////////
            
            $SAVEDATA['CREATED_DATE'] = getCurrentDBDateTime();
            $SAVEDATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;
            
            if(!$CHECK){
                self::dbAccess()->insert('t_income', $SAVEDATA);
                self::setActionStudentPayment($studentFeePrePaymentId);   
            }
            
            return array("success" => true);    
        } 
       
        public static function setActionStudentPayment($studentFeePrePaymentId){
            
            $SAVEDATA["ACTION"] = 1;
            $WHERE = self::dbAccess()->quoteInto("ID = ?", $studentFeePrePaymentId);
            self::dbAccess()->update('t_student_prepayment', $SAVEDATA, $WHERE);    
                
        }
        
        ///
        
        public static function matchStudentAndFeePrepayment($studentId,$training=false){
        
            $resultrow='';
            if($training){
                
                $facette=self::findStudentTermAndProgram($studentId);
                $object=self::findPrePaymentByCampus($facette->PROGRAM,true);
                if($object){
                    foreach($object as $values){
                        $term_str= $values->TERM_STR;
                        $term_arr=explode(",",$term_str);
                        foreach($term_arr as $vlaue){
                            if($vlaue==$facette->TERM){
                            $resultrow=$values;    
                            }   
                        }   
                    }    
                }
                    
            }else{
                
                $facette=self::findStudentSchoyearAndCampus($studentId);
                $object=self::findPrePaymentByCampus($facette->CAMPUS,false);
                if($object){
                    foreach($object as $values){
                        $schoolyear_str= $values->SCHOOLYEAR_STR;
                        $schoolyear_arr=explode(",",$schoolyear_str);
                        foreach($schoolyear_arr as $vlaue){
                            if($vlaue==$facette->SCHOOL_YEAR){
                            $resultrow=$values;    
                            }   
                        }   
                    }    
                }
            }
            return $resultrow;  
        }
            
        public static function jsonSaveStudentFeePrePayment($studentId,$training=false){
           
            $object=self::matchStudentAndFeePrepayment($studentId,$training);
            
            $SAVEDATA=array();
                
                $SAVEDATA["STUDENT_ID"] = $studentId;
                $SAVEDATA["FEE_PREPAYMENT_ID"] = $object->ID;
                if($training){
                    $SAVEDATA["TERM_STR"] = $object->TERM_STR;
                    $SAVEDATA["PROGRAM"] = $object->PROGRAM;    
                }else{  
                    $SAVEDATA["SCHOOLYEAR_STR"] = $object->SCHOOLYEAR_STR;
                    $SAVEDATA["CAMPUS_ID"] = $object->CAMPUS_ID;
                }
                self::dbAccess()->insert('t_student_prepayment', $SAVEDATA);
                $objectId = self::dbAccess()->lastInsertId();
            return array("success" => true, "objectId" => $objectId);
            
        } 
        
        public static function findSchoolyearName($schoolyear){
            
            $SQL = self::dbAccess()->select();
            $SQL->from("t_academicdate", array("*"));
            $SQL->where("ID = '" . $schoolyear . "'");
            $result = self::dbAccess()->fetchRow($SQL);
            return $result;
                
       }
       
       public static function getTrainingPrograme(){
           
            $SQL = self::dbAccess()->select();
            $SQL->from("t_training", array("*"));
            $SQL->where("OBJECT_TYPE = 'PROGRAM'");
            $SQL->where("PARENT = 0");
            $SQL->where("PROGRAM = 0");
            $result = self::dbAccess()->fetchAll($SQL);
            return $result;
           
       }
          
        //
          
    }

?>