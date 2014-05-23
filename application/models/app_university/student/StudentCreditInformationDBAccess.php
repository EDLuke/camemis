<?php

    ///////////////////////////////////////////////////////////
    // @Sor Veasna
    // Date: 26.12.2013
    // 
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once 'models/app_university/BuildData.php';
    require_once 'models/app_university/student/StudentDBAccess.php';
    require_once 'models/app_university/student/StudentAcademicDBAccess.php';
    require_once 'models/app_university/academic/AcademicDBAccess.php';
    require_once 'models/app_university/subject/GradeSubjectDBAccess.php';
    require_once 'models/app_university/subject/SubjectDBAccess.php';
    require_once 'models/app_university/AcademicDateDBAccess.php';   
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once setUserLoacalization();

    class StudentCreditInformationDBAccess{

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
        
        /////////////////////
        /////Data Query
        /////////////////////
        
        public static function getStudentSchoolYearSubjectById($Id){
            $SQL = self::dbAccess()->select();
            $SQL->from(array('t_student_schoolyear_subject'), array('*')); 
            $SQL->where("ID = ?",$Id);   
            //error_log($SQL);
            return self::dbAccess()->fetchRow($SQL);    
        }
        
        public static function sqlStudentEnrolledSubject($params){
            
            $globalSearch = isset($params["query"]) ? addText($params["query"]) : "";
            $schoolyearId = isset($params["schoolyearId"]) ? addText($params["schoolyearId"]) : "";
            $subjectId = isset($params["subjectId"]) ? addText($params["subjectId"]) : "";
            $creditStatus = isset($params["creditStatus"]) ? addText($params["creditStatus"]) : "";
            $creditNumber = isset($params["creditNumber"]) ? $params["creditNumber"] : "";
            $gradeId = isset($params["gradeId"]) ? (int) $params["gradeId"] : "";
            $studentId = isset($params["studentId"]) ? addText($params["studentId"]) : "";
            $lastname = isset($params["lastname"]) ? addText($params["lastname"]) : "";
            $firstname = isset($params["firstname"]) ? addText($params["firstname"]) : "";
            $gender = isset($params["gender"]) ? addText($params["gender"]) : "";
            $studentschoolId = isset($params["studentschoolId"]) ? addText($params["studentschoolId"]) : "";
            $code = isset($params["code"]) ? addText($params["code"]) : "";
            
            $SELECTION_A = array(
                "ID AS ENROLL_ID"
                , "SORTKEY AS SORTKEY"
                , "SCHOOLYEAR_ID AS SCHOOL_YEAR"
                , "CLASS_ID AS CLASS"
                , "CREDIT_STATUS AS CREDIT_STATUS"
                , "CREDIT_STATUS_DESCRIPTION AS CREDIT_STATUS_DESCRIPTION" 
                , "CAMPUS_ID AS CAMPUS_ID" 
                , "CREDIT_STATUS_BY AS CREDIT_STATUS_BY"
                , "CREDIT_STATUS_DATED AS CREDIT_STATUS_DATED"
            );
            
            $SELECTION_B = array(
                "ID AS STUDENT_ID"
                , "STUDENT_INDEX"
                , "CODE AS CODE_ID"
                , "CODE AS STUDENT_CODE"
                , "FIRSTNAME AS FIRSTNAME"
                , "LASTNAME AS LASTNAME"
                , "FIRSTNAME_LATIN AS FIRSTNAME_LATIN"
                , "LASTNAME_LATIN AS LASTNAME_LATIN"
                , "GENDER AS GENDER"
                , "EMAIL AS EMAIL"
                , "PHONE AS PHONE"
                , "DATE_BIRTH AS DATE_BIRTH"
                , "SMS_SERVICES AS SMS_SERVICES"
                , "MOBIL_PHONE AS MOBIL_PHONE"
                , "STUDENT_SCHOOL_ID AS STUDENT_SCHOOL_ID"
                , "CONCAT(LASTNAME,' ',FIRSTNAME) AS FULL_NAME"
                , "CREATED_DATE AS CREATED_DATE"
            );
            
            $SELECTION_C = array(
                "ID AS GRADE_ID"
                , "NAME AS GRADE_NAME"
                , "PARENT AS ACADEMIC_ID"
            );
            
            $SELECTION_D = array(
                "ID AS SUBJECT_ID"
                , "NAME AS SUBJECT_NAME"
                , "NUMBER_CREDIT AS NUMBER_CREDIT"
                , "NUMBER_SESSION AS NUMBER_SESSION"
                , "COLOR AS COLOR"
            );
            
            $SELECTION_E = array(
                "ID AS SCHOOLYEAR_ID"
                , "NAME AS SCHOOLYEAR_NAME"
            );
            
            $SQL = self::dbAccess()->select();
            $SQL->from(array('A' => 't_student_schoolyear_subject'), $SELECTION_A);
            $SQL->joinLeft(array('B' => 't_student'), 'B.ID=A.STUDENT_ID', $SELECTION_B);
            $SQL->joinLeft(array('C' => 't_grade'), 'C.ID=A.CLASS_ID', $SELECTION_C);
            $SQL->joinLeft(array('D' => 't_subject'), 'D.ID=A.SUBJECT_ID', $SELECTION_D);
            $SQL->joinLeft(array('E' => 't_academicdate'), 'E.ID=A.SCHOOLYEAR_ID', $SELECTION_E);   

            if($studentId)
            $SQL->where("B.ID='" . $studentId . "'");
            
            if($schoolyearId)
            $SQL->where("A.SCHOOLYEAR_ID='" . $schoolyearId . "'");
            
            if($subjectId)
            $SQL->where("A.SUBJECT_ID='" . $subjectId . "'");
            
            if($creditStatus != "")
            $SQL->where("A.CREDIT_STATUS='" . $creditStatus . "'");
            
            if($creditNumber)
            $SQL->where("D.NUMBER_CREDIT='" . $creditNumber . "'");
            
            if($studentschoolId)
            $SQL->where("B.STUDENT_SCHOOL_ID LIKE '%" . $studentId . "%'");
            
            if($code)
            $SQL->where("B.CODE LIKE '%" . $code . "%'");
            
            if ($firstname)
            $SQL->where("B.FIRSTNAME LIKE '%" . $firstname . "%'");

            if ($lastname)
            $SQL->where("B.LASTNAME LIKE '%" . $lastname . "%'");
            
            if ($gender)
            $SQL->where("B.GENDER = '" . $gender . "'");
            
            if ($globalSearch) {
                $SEARCH = " ((B.LASTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (B.FIRSTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (B.FIRSTNAME_LATIN LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (B.LASTNAME LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (B.STUDENT_SCHOOL_ID LIKE '" . $globalSearch . "%')";
                $SEARCH .= " OR (B.CODE LIKE '" . strtoupper($globalSearch) . "%')";
                $SEARCH .= " ) ";
                $SQL->where($SEARCH);
            }
            
            //error_log($SQL);
            return self::dbAccess()->fetchAll($SQL);
            
        }
        
        public static function findCreditInformationByStudentId($studentId){
            
            $result = self::sqlStudentEnrolledSubject(array('studentId'=>$studentId));
            $count = 0;
            if($result){
                $i = 0;
                foreach($result as $value){
                  if($value->CREDIT_STATUS==2){  
                      $gradeSubjectObject = GradeSubjectDBAccess::getGradeSubject(false, $value->CAMPUS_ID, $value->SUBJECT_ID, $value->SCHOOLYEAR_ID);
                      if($gradeSubjectObject){
                          if($i){
                            $count = $count + getNumberFormat($gradeSubjectObject->NUMBER_CREDIT);
                          }else{
                            $count = getNumberFormat($gradeSubjectObject->NUMBER_CREDIT);    
                          }
                          
                          ++$i;
                      }
                  }  
                    
                }   
            }
            
            return  $count; 
        }
        
        public static function getTermSubjectGrade($subjectId, $academicId){
            
            $SELECT_DATA = array(
                "TERM AS TERM"
            );
            $SQL = self::dbAccess()->select();
            $SQL->from('t_schedule', $SELECT_DATA);
            $SQL->where("SUBJECT_ID='" . $subjectId . "'");
            $SQL->where("ACADEMIC_ID='" . $academicId . "'");
            //error_log($SQL);
            $result = self::dbAccess()->fetchRow($SQL);
            
            return  $result?$result->TERM:''; 
        }
        
        public function getAllCompusCredit(){
          
           $SQL = self::dbAccess()->select();
           $SQL->from('t_grade', array('*')); 
           $SQL->where("OBJECT_TYPE='CAMPUS'");
           $SQL->where("EDUCATION_SYSTEM='1'");
           $result = self::dbAccess()->fetchAll($SQL); 
           return $result;   
        }
        
        public static function checkSubjectStatus($subjectId,$academicId){
            
           $academicObject = AcademicDBAccess::findGradeFromId($academicId);
           switch($academicObject->OBJECT_TYPE){
                case 'SCHOOLYEAR':
                    $gradeSchoolYearId = $academicObject->ID;
                    break;
                case 'SUBJECT':
                    $gradeSchoolYearId = $academicObject->PARENT;
                    break;    
           } 
           $term = self::getTermSubjectGrade($subjectId, $academicId);
           $CREDIT_STATUS = AcademicDBAccess::getTermByDateAcademic(date('Y-m-d'), $gradeSchoolYearId, $term);
           return $CREDIT_STATUS?$CREDIT_STATUS:0;         
        }
        
        ////////////////
        ////data json
        //////////////
        public static function jsonListCreditStudentInformation($params){
            
            $start = isset($params["start"]) ? (int) $params["start"] : "0";
            $limit = isset($params["limit"]) ? (int) $params["limit"] : "50";
            $result = self::sqlStudentEnrolledSubject($params);
            
            $data = array();

            $i = 0;
            if ($result){
                foreach ($result as $value) {
                   
                    $subjectStatus = "";
                    $gradeSubjectObject = "";
                    $creditstatus = "---";
                    
                    $creditNumber = self::findCreditInformationByStudentId($value->STUDENT_ID);
                    $term = self::getTermSubjectGrade($value->SUBJECT_ID, $value->ACADEMIC_ID);
                    
                    if($value->SUBJECT_ID && $value->ACADEMIC_ID){
                        $subjectStatus = self::checkSubjectStatus($value->SUBJECT_ID,$value->ACADEMIC_ID);
                        $gradeSubjectObject = GradeSubjectDBAccess::getGradeSubject(false, $value->CAMPUS_ID, $value->SUBJECT_ID, $value->SCHOOLYEAR_ID);
                    }
                    
                    switch($value->CREDIT_STATUS){
                        case '0':
                            $creditstatus = "Ongoing";
                            break; 
                        case '1':
                            $creditstatus = "Incompleted";
                            break;  
                        case '2':
                            $creditstatus = "Completed";
                            break;  
                    }
                
                    $data[$i]["ID"] =$value->ENROLL_ID;
                    $data[$i]["STUDENT_TOTAL_CREDIT"] =$value->FULL_NAME. " (Total credit completed:" . $creditNumber ." Credits)";  
                    $data[$i]["SUBJECT"] = $value->SUBJECT_NAME;
                    $data[$i]["CREDIT_NUMBER"] = $gradeSubjectObject?$gradeSubjectObject->NUMBER_CREDIT:getNumberFormat($value->NUMBER_CREDIT);
                    $data[$i]["SESSION"] = $gradeSubjectObject?$gradeSubjectObject->NUMBER_SESSION:$value->NUMBER_SESSION;
                    $data[$i]["SCHOOLYEAR"] = $value->SCHOOLYEAR_NAME;
                    if($term)
                    $data[$i]["SCHOOLYEAR"] .= "<BR/> (" . $term .")";
                    //$data[$i]["STATUS"] = $subjectStatus?"Ongoing":$creditstatus;
                    $data[$i]["STATUS"] = $creditstatus;
                    $data[$i]["DESCRIPTION"] = $value->CREDIT_STATUS_DESCRIPTION;
                    $data[$i]["COLOR"] = $value->COLOR;
                    $data[$i]["CREDIT_STATUS_BY"] = $value->CREDIT_STATUS_BY;
                    $data[$i]["CREDIT_STATUS_DATED"] = getShowDate($value->CREDIT_STATUS_DATED);
                
                    ++$i;   
                  
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
        
        public static function jsonStudentCreditStatus($objectId){
            
            $result = self::getStudentSchoolYearSubjectById($objectId);
            $data = array();
            if($result){
                $data["CREDIT_STATUS"] = $result->CREDIT_STATUS;
                $data["CONTENT"] = $result->CREDIT_STATUS_DESCRIPTION;       
            }
            return array(
                "success" => true
                , "data" => $data
            );
        }
        
        /////////////////
        /// Data Action
        /////////////////
        
        public static function changeCrditeStudentInformation($params){
            
            $objectId = isset($params['id'])? addText($params["id"]): '';
            $newValue = isset($params['newValue'])? addText($params["newValue"]): '';
            $file = isset($params['field'])? addText($params["field"]): '';
            $object = self::getStudentSchoolYearSubjectById($objectId);
            $error = "";
            $SAVEDATA = array();
            if($file && $object){
                $WHERE[] = "ID = '" . $objectId . "'";
                switch($file){
                    case 'DESCRIPTION':
                        $SAVEDATA['CREDIT_STATUS_DESCRIPTION'] = $newValue;    
                        self::dbAccess()->update('t_student_schoolyear_subject', $SAVEDATA, $WHERE);         
                    break;
                    case 'STATUS':
                        $subjectStatus = self::checkSubjectStatus($object->SUBJECT_ID,$object->CREDIT_ACADEMIC_ID);
                        if(!$subjectStatus){
                            $SAVEDATA['CREDIT_STATUS'] = $newValue; 
                            self::dbAccess()->update('t_student_schoolyear_subject', $SAVEDATA, $WHERE);   
                        }else{
                            $error = "Subject is Ongoing!";
                        }            
                    break;        
                }  
            }else{
                $error = "SAVE ERROR!";    
            }
            
            return array(
                "success" => true,"error" => $error
            );   
        } 
        
        public static function changeStudentSubjectCreditInfo($params){
            
            $objectId = isset($params['studentSubjectId'])?addText($params['studentSubjectId']):'';
            $studentSubjectObject = self::getStudentSchoolYearSubjectById($objectId);
            $error = ""; 
            $SAVEDATA = array();
            if($studentSubjectObject){
                $WHERE[] = "ID = '" . $objectId . "'";
                if(isset($params['CREDIT_STATUS']))
                $SAVEDATA['CREDIT_STATUS'] = addText($params['CREDIT_STATUS']);
                
                if(isset($params['CONTENT']))
                $SAVEDATA['CREDIT_STATUS_DESCRIPTION'] = addText($params['CONTENT']);
                
                $SAVEDATA['CREDIT_STATUS_DATED'] = getCurrentDBDateTime();
                $SAVEDATA['CREDIT_STATUS_BY'] = Zend_Registry::get('USER')->CODE; 
                self::dbAccess()->update('t_student_schoolyear_subject', $SAVEDATA, $WHERE);   
            }else{
                $error = "error";    
            }
            
            return array(
                "success" => true,"objectId" => $objectId,"error" => $error
            );   
                
        }
    
    }

?>
