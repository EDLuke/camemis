<?php

////////////////////////////////////////////////////////////////////////////////
//@CHHE Vathana
//24.06.2014
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");                             
require_once 'models/export/CamemisExportDBAccess.php';
require_once 'models/CamemisTypeDBAccess.php';
class StudentPersonalInfoExportDBAccess extends CamemisExportDBAccess {

    function __construct($objectId) {

        $this->objectId = $objectId;
        parent::__construct();
    }
    public static function dbAccess()
    {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function dbSelectAccess()
    {
        return self::dbAccess()->select();
    }
    //Personal Information
    public function setContentPersonalInfo($params){
       
         $studentObject = StudentDBAccess::findStudentFromId($this->objectId);
         
         $i=3;
         if($studentObject){
           
            foreach($params as $key => $values){
                
                switch ($studentObject->$values)  {
                    case "2":
                        $result = "Female";
                        break;
                    case "1":
                        $result = "Male";
                        break;
                    default:
                        $result = $studentObject->$values; 
                        break;
                }
                switch ($values){    
                                    
                    case "FIRSTNAME":
                        $CONST_NAME = "Firstname in Khmer:";
                        break;
                    case "FIRSTNAME_LATIN":
                        $CONST_NAME = "Firstname in Latin:";
                        break;
                    case "LASTNAME":
                        $CONST_NAME = "Lastname in Khmer:";
                        break;
                    case "LASTNAME_LATIN":
                        $CONST_NAME = "Lastname in Latin:";
                        break;
                    case "LASTNAME_LATIN":
                        $CONST_NAME = "Lastname in Latin:";
                        break;
                    case "GENDER":
                        $CONST_NAME = "Gender:";
                        break;                         
                    case "DATE_BIRTH":
                        $CONST_NAME = "Date of birth:";   
                        break;                    
                    case "BIRTH_PLACE":
                        $CONST_NAME = "Place of birth:";   
                        break;  
                    case "RELIGION":          
                        $CONST_NAME = "Religion:";
                        $result = $studentObject->$values?CamemisTypeDBAccess::findObjectFromId($studentObject->RELIGION)->NAME:'?';   
                        break; 
                    case "ETHNIC":
                        $CONST_NAME = "Ethnic:"; 
                        $result = $studentObject->$values?CamemisTypeDBAccess::findObjectFromId($studentObject->ETHNIC)->NAME:'?';  
                        break; 
                    case "NATIONALITY":
                        $CONST_NAME = "Nationality:"; 
                        $result = $studentObject->$values?CamemisTypeDBAccess::findObjectFromId($studentObject->NATIONALITY)->NAME:'?';  
                        break; 
                    case "ADDRESS":
                        $CONST_NAME = "Address:";   
                        break; 
                    case "COUNTRY_PROVINCE":
                        $CONST_NAME = "Province:";
                        $result = $studentObject->$values?LocationDBAccess::findObjectFromId($studentObject->COUNTRY_PROVINCE)->NAME:'?';          
                        break;
                    case "TOWN_CITY":
                        $CONST_NAME = "City:";
                        $result = $studentObject->$values?LocationDBAccess::findObjectFromId($studentObject->TOWN_CITY)->NAME:'?';          
                        break;
                    case "PHONE":
                        $CONST_NAME = "Phone:";          
                        break;
                    case "EMAIL":
                        $CONST_NAME = "Email:";
                        break;         
                    
                }
                
             
                $this->setCellContent(0, $i,$CONST_NAME);
                $this->setFontStyle(0, $i, false, 11, "000000");
                $this->setCellStyle(0, $i, 30,20, true);
                
                $this->setCellContent(1, $i, $result);
                $this->setFontStyle(1, $i, false, 11, "000000");
                $this->setCellStyle(1, $i, 30, 20, true);  
                  
                $i++;
            }      
         } 
                   
    }
    
    public function setPersonalInfoPanel() {

        $this->setCellMergeContent(0,1,"Personal Information","A1","O1");
        $this->setCellContent(0, 1,"Personal Information");
        $this->setFontStyle(0, 1, true, 11, "000000");
        $this->setFullStyle(0, 1, "DFE3E8");
        $this->setCellStyle(0, 1, 30, 40, true);  
        $this->setContentPersonalInfo(array('FIRSTNAME','FIRSTNAME_LATIN','LASTNAME','LASTNAME_LATIN','GENDER','DATE_BIRTH','BIRTH_PLACE','RELIGION','ETHNIC','NATIONALITY','ADDRESS','COUNTRY_PROVINCE','TOWN_CITY','PHONE','EMAIL'));
        
        
    }
    
    
    //Education Background
    
    public function getEducationBackgroundSelectedColumns() {
        return Utiles::getSelectedGridColumns('STUDENT_EDUCATIONAL_BACKGROUND_LIST_ID');
    }
    
    public function setEducationBackgroundHeader($rowIndex) {

        $i = $rowIndex;
        foreach ($this->getEducationBackgroundSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                case "ACADEMIC_YEAR":
                    $CONST_NAME = "Academic Year";
                    $colWidth = 20;
                    break;
                case "INSTITUTION_NAME":
                    $CONST_NAME = "Institution name";
                    $colWidth = 20;
                    break;
                case "MAJOR_TYPE":
                    $CONST_NAME = "Major type";
                    $colWidth = 20;
                    break;
                case "MAJOR":
                    $CONST_NAME = "Major";
                    $colWidth = 20;
                    break;
                case "QUALIFICATION_DEGREE":
                    $CONST_NAME = "Qualification degree";
                    $colWidth = 20;
                    break;
                case "CITY_PROVINCE":
                    $CONST_NAME = "City Province";
                    $colWidth = 25;
                    break;
                case "CERTIFICATE_NUMBER":
                    $CONST_NAME = "City Certificate Number";
                    $colWidth = 25;
                    break;                
                
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 12, $COLUMN_NAME);
            $this->setFontStyle($i, 12, true, 10, "000000");
            $this->setFullStyle($i, 12, "e8e6e6");
            $this->setCellStyle($i, 12, $colWidth, 20);

            $i++;
        }
    }

    public function setContentEducationBackground() {
        
        $entries = $this->DB_STUDENT_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId,'object'=>'EDUBACK'), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 13;
                
                foreach ($this->getEducationBackgroundSelectedColumns() as $colName) {
                     
                    $STATUS_KEY = isset($entries[$i]["STATUS_KEY"]) ? $entries[$i]["STATUS_KEY"] : "";
                    $CONTENT = isset($entries[$i][$colName]) ? $entries[$i][$colName] : "";
                    $BG_COLOR = isset($entries[$i]["BG_COLOR"]) ? $entries[$i]["BG_COLOR"] : "";
                    
                    switch ($colName) {
                        case "STATUS_KEY":
                            $this->setCellContent($colIndex, $rowIndex, $STATUS_KEY);
                            $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                            $this->setFullStyle($colIndex, $rowIndex, substr($BG_COLOR, 1));
                            $this->setCellStyle($colIndex, $rowIndex, false, 15);
                            $this->setBorderStyle($colIndex, $rowIndex, "DADCDD");
                            break;
                        default:
                            if ($CONTENT) {
                                $this->setCellContent($colIndex, $rowIndex, $CONTENT);
                                $this->setFontStyle($colIndex, $rowIndex, false, 9, "000000");
                                $this->setCellStyle($colIndex, $rowIndex, false, 20);
                                
                            }

                            break;
                    }
                    $colIndex ++;
                }
            }
        }
    }
    
    
    public function setEducationBackgroundPanel() {
        
        
        $this->setCellMergeContent(0, 18,"Background Education","A18","O18");
        $this->setCellContent(0, 18,"Background Education");
        $this->setFontStyle(0, 18, true, 11, "000000");
        $this->setFullStyle(0, 18, "DFE3E8");      
        $this->setCellStyle(0, 18, 30, 40, true);  
        $this->setEducationBackgroundHeader(0);
        $this->setContentEducationBackground(); 
        

    }
    
    //Parent/Guardian
    
    public function getParentGuardianSelectedColumns() {
        return Utiles::getSelectedGridColumns('PARENT_GUARDIAN_LIST_ID');
    }
    
    public function setParentGuardianHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getParentGuardianSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                case "NAME":
                    $CONST_NAME = "NAME";
                    $colWidth = 20;
                    break;
                case "PHONE":
                    $CONST_NAME = "PHONE";
                    $colWidth = 20;
                    break;
                case "EMAIL":
                    $CONST_NAME = "EMAIL";
                    $colWidth = 20;
                    break;
                case "ADDRESS":
                    $CONST_NAME = "ADDRESS";
                    $colWidth = 20;
                    break;
                case "DATE_BIRTH":
                    $CONST_NAME = "DATE_BIRTH";
                    $colWidth = 20;
                    break;
                case "OCCUPATION":
                    $CONST_NAME = "OCCUPATION";
                    $colWidth = 25;
                    break;
                case "RELATIONSHIP":
                    $CONST_NAME = "RELATIONSHIP";
                    $colWidth = 25;
                    break;
                case "ETHNICITY":
                    $CONST_NAME = "ETHNICITY";
                    $colWidth = 25;
                    break;            
                case "NATIONALITY":
                    $CONST_NAME = "NATIONALITY";
                    $colWidth = 25;
                    break;
           
                case "EMERGENCY_CONTACT":
                    $CONST_NAME = "EMERGENCY_CONTACT";
                    $colWidth = 25;
                    break;
                
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
        
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 24, $COLUMN_NAME);
            $this->setFontStyle($i, 24, true, 10, "000000");
            $this->setFullStyle($i, 24, "e8e6e6");
            $this->setCellStyle($i, 24, $colWidth, 20);

            $i++;
        }
    }
    
    public function setContentParentGaudian() {
        
        $entries = $this->DB_STUDENT_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId,'object'=>'PARINFO'), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 25;
                
                foreach ($this->getParentGuardianSelectedColumns() as $colName) {
                     
                    $STATUS_KEY = isset($entries[$i]["STATUS_KEY"]) ? $entries[$i]["STATUS_KEY"] : "";
                    $CONTENT = isset($entries[$i][$colName]) ? $entries[$i][$colName] : "";
                    $BG_COLOR = isset($entries[$i]["BG_COLOR"]) ? $entries[$i]["BG_COLOR"] : "";
                    
                    switch ($colName) {
                        case "STATUS_KEY":
                            $this->setCellContent($colIndex, $rowIndex, $STATUS_KEY);
                            $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                            $this->setFullStyle($colIndex, $rowIndex, substr($BG_COLOR, 1));
                            $this->setCellStyle($colIndex, $rowIndex, false, 15);
                            $this->setBorderStyle($colIndex, $rowIndex, "DADCDD");
                            break;
                        default:
                            if ($CONTENT) {
                                $this->setCellContent($colIndex, $rowIndex, $CONTENT);
                                $this->setFontStyle($colIndex, $rowIndex, false, 9, "000000");
                                $this->setCellStyle($colIndex, $rowIndex, false, 20);
                                
                            }

                            break;
                    }
                    $colIndex ++;
                }
            }
        }
    }    
    
    public function setParentGuardianPanel() {
        
        
        $this->setCellMergeContent(0, 30,"Parent/Guardian","A22","O22");
        $this->setCellContent(0, 30,"Parent/Guardian");
        $this->setFontStyle(0, 30, true, 11, "000000");
        $this->setFullStyle(0, 30, "DFE3E8");
        $this->setCellStyle(0, 30, 30, 40, true);
        $this->setParentGuardianHeader(0);
        $this->setContentParentGaudian();
         
    }
    //Pre Requirements
    
    public function getPrerequirementSelectedColumns() {
        return Utiles::getSelectedGridColumns('STUDENT_PREREQUIREMENTS_LIST_ID');
    }
    
    public function setPrerequirementHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getPrerequirementSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                case "NAME":
                    $CONST_NAME = "NAME";
                    $colWidth = 20;
                    break;
                case "PHONE":
                    $CONST_NAME = "PHONE";
                    $colWidth = 20;
                    break;
                case "EMAIL":
                    $CONST_NAME = "EMAIL";
                    $colWidth = 20;
                    break;
                case "ADDRESS":
                    $CONST_NAME = "ADDRESS";
                    $colWidth = 20;
                    break;
                case "DATE_BIRTH":
                    $CONST_NAME = "DATE_BIRTH";
                    $colWidth = 20;
                    break;
                case "OCCUPATION":
                    $CONST_NAME = "OCCUPATION";
                    $colWidth = 25;
                    break;
                case "RELATIONSHIP":
                    $CONST_NAME = "RELATIONSHIP";
                    $colWidth = 25;
                    break;
                case "ETHNICITY":
                    $CONST_NAME = "ETHNICITY";
                    $colWidth = 25;
                    break;
                case "NATIONALITY":
                    $CONST_NAME = "NATIONALITY";
                    $colWidth = 25;
                    break;                              
                case "EMERGENCY_CONTACT":
                    $CONST_NAME = "EMERGENCY_CONTACT";
                    $colWidth = 25;
                    break;
    
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
        
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 38, $COLUMN_NAME);
            $this->setFontStyle($i, 38, true, 10, "000000");
            $this->setFullStyle($i, 38, "e8e6e6");
            $this->setCellStyle($i, 38, $colWidth, 20);

            $i++;
        }
    }
    
    public function setContentPrerequirement() {
        
        $entries = $this->DB_STUDENT_PREREQUIREMENT_LIST->jsonStudentPrerequirements(array('objectId'=>$this->objectId), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 39;
                
                foreach ($this->getPrerequirementSelectedColumns() as $colName) {
                     
                    $STATUS_KEY = isset($entries[$i]["STATUS_KEY"]) ? $entries[$i]["STATUS_KEY"] : "";
                    $CONTENT = isset($entries[$i][$colName]) ? $entries[$i][$colName] : "";
                    $BG_COLOR = isset($entries[$i]["BG_COLOR"]) ? $entries[$i]["BG_COLOR"] : "";
                    //error_log($CONTENT);
                    switch ($colName) {
                        case "STATUS_KEY":
                            $this->setCellContent($colIndex, $rowIndex, $STATUS_KEY);
                            $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                            $this->setFullStyle($colIndex, $rowIndex, substr($BG_COLOR, 1));
                            $this->setCellStyle($colIndex, $rowIndex, false, 15);
                            $this->setBorderStyle($colIndex, $rowIndex, "DADCDD");
                            break;
                        default:
                            if ($CONTENT) {
                                $this->setCellContent($colIndex, $rowIndex, $CONTENT);
                                $this->setFontStyle($colIndex, $rowIndex, false, 9, "000000");
                                $this->setCellStyle($colIndex, $rowIndex, false, 20);
                                
                            }

                            break;
                    }
                    $colIndex ++;
                }
            }
        }
    }
    
    public function setPrerequirementsPanel() {
        
        $this->setCellMergeContent(0, 44,"Pre requirements","A36","O36");
        $this->setCellContent(0, 44,"Pre requirements");
        $this->setFontStyle(0, 44, true, 11, "000000");
        $this->setFullStyle(0, 44, "DFE3E8");
        $this->setCellStyle(0, 44, 30, 40, true);
        $this->setPrerequirementHeader(0);
        $this->setContentPrerequirement();  
    }

    

    public function studentpersonalinfo($searchParams) {
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);
        $this->objectId = isset($searchParams['objectId'])?$searchParams['objectId']:'';
        $this->EXCEL->setActiveSheetIndex(0);
        $this->setPersonalInfoPanel();
        $this->setEducationBackgroundPanel();
        $this->setParentGuardianPanel();
        $this->setPrerequirementsPanel();
        
        $this->EXCEL->getActiveSheet()->setTitle("Profile of Student");
        $this->WRITER->save($this->getStudentPersonalList());

        return array(
            "success" => true
        );
    }

}

?>