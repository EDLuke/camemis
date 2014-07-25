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
    
    
    public function getDataSetStudentEDMajor(){
        
        $entries = FilterProperties::getCamemisType('MAJOR_TYPE');
        $this->type="MAJOR";
        $DATASET = "[{";
        $DATASET .= "key: '".MAJOR_TYPE."',";
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
    
    //
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
    
}

?>