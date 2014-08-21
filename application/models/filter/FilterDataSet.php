<?php

////////////////////////////////////////////////////////////////////////////////
// @Sor Veasna
// Date: 21.05.2014
////////////////////////////////////////////////////////////////////////////////
require_once 'models/filter/FilterData.php';
require_once 'include/Common.inc.php';

class FilterDataSet extends FilterData{

    function __construct() {
        parent::__construct();
    }
    
    public function getDataSetAbsenceType(){
        
        $entries = $this->getAttendanceType($this->personType,$this->type);
        $DATASET = "[";
        /*switch(strtoupper($this->type)){
            case 'DAILY':
                $DATASET .= "key: 'daily',";
                break;
            case 'BLOCK':
                $DATASET .= "key: 'block',";
                break;       
        }*/
        
        //$DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->absentType=$value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "[";
                //$DATASET .= "'label':'" . $value->NAME . "'";
                switch(strtoupper($this->personType)){
                    case 'STUDENT':
                        switch(strtoupper($this->type)){
                            case 'DAILY':
                                 $DATASET .= "'".$value->NAME."'," . $this->getCountDailyStudentAbsence() . "";
                                break;
                            case 'BLOCK':
                                $DATASET .= "'".$value->NAME."'," . $this->getCountBlockStudentAbsence() . "";
                                break;       
                        }
                       
                        break;
                    case 'STAFF':
                        switch(strtoupper($this->type)){
                            case 'DAILY':
                                 $DATASET .= "'".$value->NAME."'," . $this->getCountDailyTeacherAbsence() . "";
                                break;
                            case 'BLOCK':
                                $DATASET .= "'".$value->NAME."'," . $this->getCountBlockTeacherAbsence() . "";
                                break;       
                        }
                        break;       
                }
                
                $DATASET .= "]";
                $i++;
            }
        }
        $DATASET .= "]";
        //$DATASET .= "}]";
        
        return $DATASET;      
    }
    
    //Status
    public function getDataSethighchartStatus(){
        
        $entries = $this->getPersonStatus($this->personType);
        $DATASET = "[";
        //$DATASET .= "key: '".STATUS."',";
        //$DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->statusType=$value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "[";
                //$DATASET .= "'label':'" . $value->NAME . "'";
                switch(strtoupper($this->personType)){
                    case 'STUDENT':
                        $DATASET .= "'" .$value->NAME."'," . $this->getCountStudentStatus() . "";
                        break;
                    case 'STAFF':
                        $DATASET .= "'" .$value->NAME."'," . $this->getCountTeacherStatus() . "";
                        break;       
                }
                
                $DATASET .= "]";
                $i++;
            }
        }
        $DATASET .= "]";
        //$DATASET .= "}]";
        
        return $DATASET;      
    }
    //
    
    public function getDataPersonInfo(){
        
        $entries = FilterProperties::getCamemisType($this->type);
        $DATASET = "[{";
        $DATASET .= "key: '".$this->type."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->camemisType=$value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getcountPersonInfo() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    /////////////////////////////
    //Student data set
    ////////////////////////////
    public function getDataSetStudentMaleFemale(){
        $entries = array('1' => MALE, '2' => FEMALE);
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value . "'";
                if($key==1)
                $DATASET .= ",'y':'" . $this->getCountStudentMale() . "'";
                if($key==2)
                $DATASET .= ",'y':'" . $this->getCountStudentFemale() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;     
    }
    
    public function getDataSetStudentActiveAndDeactive(){
        
        $entries = array('ACTIVE' => ACTIVE, 'DEACTIVE' => 'Deactive');
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value . "'";
                if($key=='ACTIVE')
                $DATASET .= ",'y':'" . $this->getCountStudentActive() . "'";
                if($key=='DEACTIVE')
                $DATASET .= ",'y':'" . $this->getCountStudentDeactive() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;    
    }
    
    public function getDataSetReligion(){
        
        $entries = FilterProperties::getCamemisType('RELIGION_TYPE');
        $this->dataType='religion';
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value->NAME . "'";
                $DATASET .= ",'y':'" . $this->getCountStudentPersonalInformation() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;      
    }
    
    public function getDataSetSMS(){
        
        $entries = array('0'=>'NO','1'=>'YES');
        $this->dataType='SMS';
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                $this->dataValue = $key;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value . "'";
                $DATASET .= ",'y':'" . $this->getCountStudentPersonalInformation() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;      
    }
    
    public function getDataSetNationality(){
        
        $entries = FilterProperties::getCamemisType('NATIONALITY_TYPE');
        $this->dataType='nationality';
        $DATASET = "[{";
        $DATASET .= "key: '".NATIONALITY."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getCountStudentPersonalInformation() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
     public function getDataSetEthnicity(){
        
        $entries = FilterProperties::getCamemisType('ETHNICITY_TYPE');
        $this->dataType='ethnicity';
        $DATASET = "[{";
        $DATASET .= "key: '".ETHNICITY."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getCountStudentPersonalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    public function getDataSetStudentAge(){
        $entries = $this->groupAge();
        $DATASET = "[{";
        $DATASET .= "key: '".AGE."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'".AGE." " . $key . "'";
                $DATASET .= ",'value':'" . count($value) . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    public function getDataSetCountryProvince(){
        
        $entries = SQLStudentFilterReport::getAllLocation();
        $this->dataType='country_province';
        $DATASET = "[{";
        $DATASET .= "key: '".CITY_PROVINCE."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getCountStudentPersonalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    public function getDataSetStudentEDDegree(){
        
        $entries = FilterProperties::getCamemisType('QUALIFICATION_DEGREE_TYPE');
        $this->type="QUALIFICATION_DEGREE";
        $DATASET = "[{";
        $DATASET .= "key: '".DEGREE_TYPE."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->camemisType=$value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getcountStudentEDBackgroundDegreeOrMajor() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    //@Visal
    public function getDataSetStudentAdditional($parentId){
        
        $entries = SQLStudentFilterReport::getAllAdditionalChild($parentId);
        $this->dataType='ethnicity';
        $DATASET = "[{";
        $DATASET .= "key: '".ADDITIONAL_INFORMATION."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . str_replace("'","\'",$value->NAME ) . "'";
                $DATASET .= ",'value':'" . $this->getCountStudentAdditionalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    public function getDataSetHighChartStudentRegistration($month){
        
        $DATASET = "[";
            $DATASET .= "{";
            $DATASET .= "name:'".FEMALE."'";
            $DATASET .= ",data: [";
            $i=0;
            foreach($month as $key=>$value){
                $this->month=$key."_".$value;
                $this->gender=2; 
                $DATASET .= $i ? "," : ""; 
                $DATASET .= $this->getCountStudentRegistration();
                $i++;  
            }
            $DATASET .= "]";
            $DATASET .= "},{";
            $DATASET .= "name:'".MALE."'";
            $DATASET .= ",data: [";
            $i=0;
            foreach($month as $key=>$value){
                $this->month=$key."_".$value;
                $this->gender=1; 
                $DATASET .= $i ? "," : ""; 
                $DATASET .= $this->getCountStudentRegistration();
                $i++;  
            }
            $DATASET .= "]";
            $DATASET .= "}";
        $DATASET .= "]";
        
        return $DATASET;      
    }
    
    /////////////////////////////////
    ///Staff Data set
    /////////////////////////////////
    
    //@Visal
    public function getDataSetStaffActiveAndDeactive(){
        
        $entries = array('ACTIVE' => ACTIVE, 'DEACTIVE' => 'Deactive');
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value . "'";
                if($key=='ACTIVE')
                $DATASET .= ",'y':'" . $this->getCountStaffActive() . "'";
                if($key=='DEACTIVE')
                $DATASET .= ",'y':'" . $this->getCountStaffDeactive() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;    
    }
    
    //@Visal
    public function getDataSetStaffNationality(){
        
        $entries = FilterProperties::getCamemisType('NATIONALITY_TYPE');
        $this->dataType='nationality';
        $DATASET = "[{";
        $DATASET .= "key: '".NATIONALITY."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getCountStaffPersonalInformation() . "'";
                
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    
    
    //@Visal
    public function getDataSetStaffAge(){
        $entries = $this->groupStaffAge();
        $DATASET = "[{";
        $DATASET .= "key: '".AGE."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                if($key <> null){
                    $DATASET .= $i ? "," : "";
                    $DATASET .= "{";
                    $DATASET .= "'label':'".AGE." " . $key . "'";
                    $DATASET .= ",'value':'" . count($value) . "'";
                    $DATASET .= "}";
                    $i++;
                }
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    
    //@Visal
    public function getDataSetStaffCountryProvince(){
        
        $entries = SQLStudentFilterReport::getAllLocation();
        $this->dataType='country_province';
        $DATASET = "[{";
        $DATASET .= "key: '".CITY_PROVINCE."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getCountStaffPersonalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    //
    public function getDataSetStaffGender(){
        $entries = array('1' => MALE, '2' => FEMALE);
        $this->dataType='gender';
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $key=>$value)
            {
                $this->dataValue=$key;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value . "'";
                $DATASET .= ",'y':'" . $this->getCountStaffPersonalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;     
    }
    
    public function getDataSetStaffEthnicity(){
        
        $entries = FilterProperties::getCamemisType('ETHNICITY_TYPE');
        $this->dataType='ethnicity';
        $DATASET = "[{";
        $DATASET .= "key: '".ETHNICITY."',";
        $DATASET .= "values: [";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'label':'" . $value->NAME . "'";
                $DATASET .= ",'value':'" . $this->getCountStaffPersonalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        $DATASET .= "}]";
        
        return $DATASET;      
    }
    
    public function getDataSetStaffReligion(){
        
        $entries = FilterProperties::getCamemisType('RELIGION_TYPE');
        $this->dataType='religion';
        $DATASET = "[";
        if ($entries)
        {
            $i = 0;
            foreach ($entries as $value)
            {
                $this->dataValue = $value->ID;
                $DATASET .= $i ? "," : "";
                $DATASET .= "{";
                $DATASET .= "'key':'" . $value->NAME . "'";
                $DATASET .= ",'y':'" . $this->getCountStaffPersonalInformation() . "'";
                $DATASET .= "}";
                $i++;
            }
        }
        $DATASET .= "]";
        return $DATASET;      
    }
    
}

?>