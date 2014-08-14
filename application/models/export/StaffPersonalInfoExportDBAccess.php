<?php

////////////////////////////////////////////////////////////////////////////////
//@CHHE Vathana
//24.06.2014
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';


class StaffPersonalInfoExportDBAccess extends CamemisExportDBAccess {

    function __construct($objectId) {

        $this->objectId = $objectId;
        parent::__construct();
    }

    //Personal Information
    public function setContentPersonalInfo($params){
        
         $staffObject = StaffDBAccess::findStaffFromId($this->objectId);
         $i=3;
         if($staffObject){
            foreach($params as $values){
                //error_log($studentObject->$values);
                switch ($staffObject->$values)  {
                    case "2":
                        $result = "Female";
                        break;
                    case "1":
                        $result = "Male";
                        break;
                    case "0":
                        $result = "No Data";
                        break;
                    default:
                        $result = $staffObject->$values; 
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
                        $result = $staffObject->$values?CamemisTypeDBAccess::findObjectFromId($staffObject->RELIGION)->NAME:'?';   
                        break; 
                    case "ETHNIC":
                        $CONST_NAME = "Ethnic:"; 
                        $result = $staffObject->$values?CamemisTypeDBAccess::findObjectFromId($staffObject->ETHNIC)->NAME:'?';  
                        break; 
                    case "NATIONALITY":
                        $CONST_NAME = "Nationality:"; 
                        $result = $staffObject->$values?CamemisTypeDBAccess::findObjectFromId($staffObject->NATIONALITY)->NAME:'?';  
                        break; 
                    case "ADDRESS":
                        $CONST_NAME = "Address:";   
                        break; 
                    case "COUNTRY_PROVINCE":
                        $CONST_NAME = "Province:";
                        $result = $staffObject->$values?LocationDBAccess::findObjectFromId($staffObject->COUNTRY_PROVINCE)->NAME:'?';          
                        break;
                    case "TOWN_CITY":
                        $CONST_NAME = "City:";
                        $result = $staffObject->$values?LocationDBAccess::findObjectFromId($staffObject->TOWN_CITY)->NAME:'?';          
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
    
    
    //Work Experience
    
    public function getWorkExperienceSelectedColumns() {
        return Utiles::getSelectedGridColumns('WORK_EXPERIENCES_LIST_ID');
    }
    
    public function setWorkExperienceHeader($rowIndex) {

        $i = $rowIndex;
        foreach ($this->getWorkExperienceSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                
                case "START_DATE":
                    $CONST_NAME = "START_DATE";
                    $colWidth = 20;
                    break;
                    
                case "END_DATE":
                    $CONST_NAME = "END_DATE";
                    $colWidth = 20;
                    break;
                
                case "COMPANY_NAME":
                    $CONST_NAME = "COMPANY_NAME";
                    $colWidth = 20;
                    break;
                
                case "POSITION":
                    $CONST_NAME = "POSITION";
                    $colWidth = 20;
                    break;
                
                case "POSITION":
                    $CONST_NAME = "POSITION";
                    $colWidth = 20;
                    break;
                
                case "START_SALARY":
                    $CONST_NAME = "START_SALARY";
                    $colWidth = 20;
                    break;
                
                case "END_SALARY":
                    $CONST_NAME = "END_SALARY";
                    $colWidth = 20;
                    break;
                
                case "ORGANIZATION_TYPE":
                    $CONST_NAME = "ORGANIZATION_TYPE";
                    $colWidth = 20;
                    break;                
                
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
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

    public function setContentWorkExperience() {
        
        $entries = $this->DB_STAFF_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId,'object'=>'WORK_EXPERIENCE'), false);
        
        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 13;
                
                foreach ($this->getWorkExperienceSelectedColumns() as $colName) {
                     
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
    
    
    public function setWorkExperiencePanel() {
        
        
        $this->setCellMergeContent(0, 18,"Work Experience","A18","O18");
        $this->setCellContent(0, 18,"Work Experience");
        $this->setFontStyle(0, 18, true, 11, "000000");
        $this->setFullStyle(0, 18, "DFE3E8");
        $this->setCellStyle(0, 18, 30, 40, true);
        $this->setWorkExperienceHeader(0);
        $this->setContentWorkExperience(); 
        

    }
    
    //Short Training
    
    public function getShortTrainingSelectedColumns() {
        return Utiles::getSelectedGridColumns('SHORT_TRAINING_LIST_ID');
    }
    
    public function setShortTrainingHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getShortTrainingSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                
                case "START_DATE":
                    $CONST_NAME = "START_DATE";
                    $colWidth = 20;
                    break;
                    
                case "END_DATE":
                    $CONST_NAME = "END_DATE";
                    $colWidth = 20;
                    break;
                
                case "COURSE":
                    $CONST_NAME = "COURSE";
                    $colWidth = 20;
                    break;
                
                case "INSTITUTION_NAME":
                    $CONST_NAME = "INSTITUTION_NAME";
                    $colWidth = 20;
                    break;
                
                case "DESCRIPTION":
                    $CONST_NAME = "DESCRIPTION";
                    $colWidth = 20;
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
    
    public function setContentShortTraining() {
        
        $entries = $this->DB_STAFF_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId,'object'=>'PROFESSION'), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 25;
                
                foreach ($this->getShortTrainingSelectedColumns() as $colName) {
                     
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
    
    public function setShortTrainingPanel() {
        
        
        $this->setCellMergeContent(0, 29,"Short Training","A29","O29");
        $this->setCellContent(0, 29,"Short Training");
        $this->setFontStyle(0, 29, true, 11, "000000");
        $this->setFullStyle(0, 29, "DFE3E8");
        $this->setCellStyle(0, 29, 30, 40, true);
        $this->setShortTrainingHeader(0);
        $this->setContentShortTraining();
         
    }
    
    //Relative Information
    
    public function getRelativeInformationSelectedColumns() {
        return Utiles::getSelectedGridColumns('RELATIVE_INFORMATION_LIST_ID');
    }
    
    public function setRelativeInformationHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getRelativeInformationSelectedColumns() as $value) {
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
                    $colWidth = 20;
                    break;
                
                case "RELATIONSHIP":
                    $CONST_NAME = "RELATIONSHIP";
                    $colWidth = 20;
                    break;
                
                case "ETHNICITY":
                    $CONST_NAME = "ETHNICITY";
                    $colWidth = 20;
                    break;
                
                case "NATIONALITY":
                    $CONST_NAME = "NATIONALITY";
                    $colWidth = 20;
                    break;
                
                case "EMERGENCY_CONTACT":
                    $CONST_NAME = "EMERGENCY_CONTACT";
                    $colWidth = 20;
                    break;
                
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
        
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 34, $COLUMN_NAME);
            $this->setFontStyle($i, 34, true, 10, "000000");
            $this->setFullStyle($i, 34, "e8e6e6");
            $this->setCellStyle($i, 34, $colWidth, 20);

            $i++;
        }
    }
    
    public function setContentRelativeInformation() {
        
        $entries = $this->DB_STAFF_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId, 'object'=>'EMERCP'), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 35;
                
                foreach ($this->getRelativeInformationSelectedColumns() as $colName) {
                     
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
    
    public function setRelativeInformationPanel() {
        
        $this->setCellMergeContent(0, 39,"Relative Information","A39","O39");
        $this->setCellContent(0, 39,"Relative Information");
        $this->setFontStyle(0, 39, true, 11, "000000");
        $this->setFullStyle(0, 39, "DFE3E8");
        $this->setCellStyle(0, 39, 30, 40, true);
        $this->setRelativeInformationHeader(0);
        $this->setContentRelativeInformation();  
    }
    
    //Education Background
    
    public function getEducationBackgroundSelectedColumns() {
        return Utiles::getSelectedGridColumns('EDUCATION_BACKGROUND_STAFF_LIST_ID');
    }
    
    public function setEducationBackgroundHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getEducationBackgroundSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                
                case "ACADEMIC_YEAR":
                    $CONST_NAME = "ACADEMIC_YEAR";
                    $colWidth = 20;
                    break;
                    
                case "INSTITUTION_NAME":
                    $CONST_NAME = "INSTITUTION_NAME";
                    $colWidth = 20;
                    break;
                
                case "MAJOR_TYPE":
                    $CONST_NAME = "MAJOR_TYPE";
                    $colWidth = 20;
                    break;
                
                case "MAJOR":
                    $CONST_NAME = "MAJOR";
                    $colWidth = 20;
                    break;
                
                case "QUALIFICATION_DEGREE":
                    $CONST_NAME = "QUALIFICATION_DEGREE";
                    $colWidth = 20;
                    break;
                
                case "COUNTRY":
                    $CONST_NAME = "COUNTRY";
                    $colWidth = 20;
                    break;
                
                case "CERTIFICATE_NUMBER":
                    $CONST_NAME = "CERTIFICATE_NUMBER";
                    $colWidth = 20;
                    break;
                                    
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
        
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 43, $COLUMN_NAME);
            $this->setFontStyle($i, 43, true, 10, "000000");
            $this->setFullStyle($i, 43, "e8e6e6");
            $this->setCellStyle($i, 43, $colWidth, 20);

            $i++;
        }
    }
    
    public function setContentEducationBackground() {
        
        $entries = $this->DB_STAFF_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId, 'object'=>'EDUBACK'), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 44;
                
                foreach ($this->getEducationBackgroundSelectedColumns() as $colName) {
                     
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
    
    public function setEducationBackgroundPanel() {
        
        $this->setCellMergeContent(0, 50,"Education Background","A50","O50");
        $this->setCellContent(0, 50,"Education Background");
        $this->setFontStyle(0, 50, true, 11, "000000");
        $this->setFullStyle(0, 50, "DFE3E8");
        $this->setCellStyle(0, 50, 30, 40, true);
        $this->setEducationBackgroundHeader(0);
        $this->setContentEducationBackground();  
    }
    
    //Publication
    
    public function getPublicationSelectedColumns() {
        return Utiles::getSelectedGridColumns('PUBLICATION_LIST_ID');
    }
    
    public function setPublicationHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getPublicationSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                
                case "START_DATE":
                    $CONST_NAME = "START_DATE";
                    $colWidth = 20;
                    break;
                    
                case "END_DATE":
                    $CONST_NAME = "END_DATE";
                    $colWidth = 20;
                    break;
                
                case "PUBLICATION_TITLE":
                    $CONST_NAME = "PUBLICATION_TITLE";
                    $colWidth = 20;
                    break;
                
                case "DESCRIPTION":
                    $CONST_NAME = "DESCRIPTION";
                    $colWidth = 20;
                    break;
                
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
        
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 52, $COLUMN_NAME);
            $this->setFontStyle($i, 52, true, 10, "000000");
            $this->setFullStyle($i, 52, "e8e6e6");
            $this->setCellStyle($i, 52, $colWidth, 20);

            $i++;
        }
    }
    
    public function setContentPublication() {
        
        $entries = $this->DB_STAFF_INFO_LIST->jsonListPersonInfos(array('objectId'=>$this->objectId, 'object'=>'PUBLICATION'), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 53;
                
                foreach ($this->getPublicationSelectedColumns() as $colName) {
                     
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
    
    public function setPublicationPanel() {
        
        $this->setCellMergeContent(0, 58,"Publication","A58","O58");
        $this->setCellContent(0, 58,"Publication");
        $this->setFontStyle(0, 58, true, 11, "000000");
        $this->setFullStyle(0, 58, "DFE3E8");
        $this->setCellStyle(0, 58, 30, 40, true);
        $this->setPublicationHeader(0);
        $this->setContentPublication();  
    }
    
    //Staff Contract
    
    public function getStaffContractSelectedColumns() {
        return Utiles::getSelectedGridColumns('STAFF_CONTRACT_LIST_ID');
    }
    
    public function setStaffContractHeader($rowIndex) {
        
        $i = $rowIndex;
        foreach ($this->getStaffContractSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                
                case "SUBJECT_NAME":
                    $CONST_NAME = "SUBJECT_NAME";
                    $colWidth = 20;
                    break;
                    
                case "CONTRACT_TYPE":
                    $CONST_NAME = "CONTRACT_TYPE";
                    $colWidth = 20;
                    break;
                
                case "DESCRIPTION":
                    $CONST_NAME = "DESCRIPTION";
                    $colWidth = 20;
                    break;
                
                case "START_DATE":
                    $CONST_NAME = "START_DATE";
                    $colWidth = 20;
                    break;
                
                case "EXPIRED_DATE":
                    $CONST_NAME = "EXPIRED_DATE";
                    $colWidth = 20;
                    break;
                
                case "CREATED_BY":
                    $CONST_NAME = "CREATED_BY";
                    $colWidth = 20;
                    break;
                
                case "CREATED_DATE":
                    $CONST_NAME = "CREATED_DATE";
                    $colWidth = 20;
                    break;
                
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
        
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 61, $COLUMN_NAME);
            $this->setFontStyle($i, 61, true, 10, "000000");
            $this->setFullStyle($i, 61, "e8e6e6");
            $this->setCellStyle($i, 61, $colWidth, 20);

            $i++;
        }
    }
    
    public function setContentStaffContract() {
        
        $entries = $this->DB_STAFF_CONTRACT_LIST->jsonShowAllStaffContracts(array('objectId'=>$this->objectId), false);
        
        if ($entries) {
            
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 62;
                
                foreach ($this->getStaffContractSelectedColumns() as $colName) {
                     
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
    
    public function setStaffContractPanel() {
        
        $this->setCellMergeContent(0, 65,"Staff Contract","A65","O65");
        $this->setCellContent(0, 65,"Staff Contract");
        $this->setFontStyle(0, 65, true, 11, "000000");
        $this->setFullStyle(0, 65, "DFE3E8");
        $this->setCellStyle(0, 65, 30, 40, true);
        $this->setStaffContractHeader(0);
        $this->setContentStaffContract();  
    }

    

    public function staffpersonalinfo($searchParams) {
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);
        
        $this->objectId = isset($searchParams['objectId'])?$searchParams['objectId']:'';
        $this->EXCEL->setActiveSheetIndex(0);
        
        $this->setPersonalInfoPanel();
        $this->setWorkExperiencePanel();
        $this->setShortTrainingPanel();
        $this->setRelativeInformationPanel();
        $this->setEducationBackgroundPanel();
        $this->setPublicationPanel();
        $this->setStaffContractPanel();
        
        $this->EXCEL->getActiveSheet()->setTitle("Profile of Staff");
        $this->WRITER->save($this->getStaffPersonalList());

        return array(
            "success" => true
        );
    }

}

?>