<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentAttendanceDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffAttendanceDBAccess.php";

class StudentAttendanceExportDBAccess extends CamemisExportDBAccess {

    public $columnIndex = 0;
    private $CONST_NAME = null;
    private $colWidth = null;
    function __construct($objectId)
    {

        $this->objectId = $objectId;
        parent::__construct();
    }

    public function getUserSelectedColumns()
    {
        return Utiles::getSelectedGridColumns($this->objectId);
    }
    
    public function studentHeader($value){
        switch ($value)
            {
                case "STUDENT":
                    $this->columnIndex=1;
                    $this->CONST_NAME = FULL_NAME;
                    $this->colWidth = 50;
                    break;
                case "ABSENT_TYPE":
                    $this->CONST_NAME = TYPE;
                    $this->colWidth = 40;
                    break;
                case "START_DATE":
                    $this->CONST_NAME = FROM_DATE;
                    $this->colWidth = 30;
                    break;
                case "END_DATE":
                    $this->CONST_NAME = TO_DATE;
                    $this->colWidth = 20;
                    break;
                case "COUNT_DATE":
                    $this->CONST_NAME = DAYS;
                    $this->colWidth = 30;
                    break;
                case "DESCRIPTION":
                    $this->CONST_NAME = DESCRIPTION;
                    $this->colWidth = 50;
                    break;
                case "SUBJECT_NAME":
                    $this->CONST_NAME = SUBJECT;
                    $this->colWidth = 40;
                    break;
                case "SCHOOLYEAR_NAME":
                    $this->CONST_NAME = CURRENT_SCHOOL_YEAR;
                    $this->colWidth = 30;
                    break;
                case "CLASS_NAME":
                    $this->CONST_NAME = CURRENT_CLASS;
                    $this->colWidth = 20;
                    break;
                case "TRAINING_TERM":
                    $this->CONST_NAME = CURRENT_TERM;
                    $this->colWidth = 30;
                    break;
                case "TRAINING_NAME":
                    $this->CONST_NAME = CURRENT_COURSE;
                    $this->colWidth = 30;
                    break;

                default:
                    $this->CONST_NAME = defined($value) ? constant($value) : $value;
                    $this->colWidth = 30;
                    break;
            }    
    }
    
    public function staffHeader($value){
        switch ($value)
            {
                case "STAFF":
                    $this->columnIndex=1;
                    $this->CONST_NAME = FULL_NAME;
                    $this->colWidth = 50;
                    break;
                case "ABSENT_TYPE":
                    $this->CONST_NAME = ATTENDANCE_TYPE;
                    $this->colWidth = 40;
                    break;
                case "DATE":
                    $this->CONST_NAME = DATE;
                    $this->colWidth = 30;
                    break;
                case "COUNT_DATE":
                    $this->CONST_NAME = COUNT_DATE;
                    $this->colWidth = 30;
                    break;
                case "SUBJECT_NAME":
                    $this->CONST_NAME = SUBJECT;
                    $this->colWidth = 40;
                    break;
                case "STAFF_CONTRACT_TYPE":
                    $this->CONST_NAME = CONTRACT_TYPE;
                    $this->colWidth = 30;
                    break;

                default:
                    $this->CONST_NAME = defined($value) ? constant($value) : $value;
                    $this->colWidth = 30;
                    break;
            }    
    }

    public function setContentHeader($type)
    {
        $i = 0;
        foreach ($this->getUserSelectedColumns() as $value)
        {
            switch ($type)
            {
                case "STUDENT":
                    $this->studentHeader($value); 
                    break;
                case "STAFF":
                    $this->staffHeader($value); 
                    break;        
            }    

            $COLUMN_NAME = defined($this->CONST_NAME) ? constant($this->CONST_NAME) : $this->CONST_NAME;
            $this->setCellContent($i, $this->startHeader, $COLUMN_NAME);
            $this->setFontStyle($i, $this->startHeader, true, 11, "000000");
            $this->setFullStyle($i, $this->startHeader, "DFE3E8");
            $this->setCellStyle($i, $this->startHeader, $this->colWidth, 40);

            $i++;
        }
    }

    public static function getGroupAssoc($array, $key)
    {
        $return = array();
        foreach ($array as $v)
        {
            $return[$v[$key]][] = $v;
        }
        return $return;
    }
    
    /////////////////////
    /// Results as Array
    ///
    /////////////////
    public function getStudentAttendanceArrayResult($searchParams){
        $entries = $this->DB_STUDENT_ATTENDANCE->jsonSearchStudentAttendance($searchParams, false);
        return $entries;         
    }
    
    public function getStaffAttendanceArrayResult($searchParams){
        $entries = StaffAttendanceDBAccess::jsonSearchStaffAttendance($searchParams, false);
        return $entries;         
    }
    ///

    public function setContent($entries,$groupId,$groupField)
    {
        if ($entries)
        {
            $GOUPING_DATA = self::getGroupAssoc($entries, $groupId);
            if ($GOUPING_DATA)
            {
                $rowIndex = $this->startContent();

                foreach ($GOUPING_DATA as $dataId => $EACH_DATA)
                {

                    //Name of Student
                    $colIndex = 0;
                    $GROUP_NAME = $EACH_DATA[0][$groupField];
                    $this->setCellContent($colIndex, $rowIndex, $GROUP_NAME);
                    $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                    $this->setCellStyle($colIndex, $rowIndex, false, 20);
                    for ($ii = 0; $ii < count($this->getUserSelectedColumns()); $ii++)
                    {
                        $this->setBorderStyle($ii, $rowIndex, "FF6495ED");
                        $this->setFullStyle($ii, $rowIndex,"8DB2E3");
                    }

                    for ($j = 0; $j < count($EACH_DATA); $j++)
                    {

                        $rowIndex++;
                        $colIndex = $this->columnIndex;

                        foreach ($this->getUserSelectedColumns() as $colName)
                        {

                            $CONTENT = isset($EACH_DATA[$j][$colName]) ? $EACH_DATA[$j][$colName] : "";
                            //skip column data 
                            if ($colName == $groupField)
                                continue;
                            /////
                            
                            $this->setCellContent($colIndex, $rowIndex, $CONTENT);
                            $this->setFontStyle($colIndex, $rowIndex, false, 9, "000000");
                            $this->setCellStyle($colIndex, $rowIndex, false, 20);

                            $colIndex++;
                        }
                    }
                    $rowIndex++;
                }
            }
        }
    }

    public function jsonSearchStudentAttendance($params)
    {

        ini_set('max_execution_time', 600000);
        set_time_limit(35000);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader("STUDENT");
        $entries=$this->getStudentAttendanceArrayResult($params);  //new
        $this->setContent($entries,"STUDENT_ID","STUDENT"); //new
        $this->EXCEL->getActiveSheet()->setTitle("" . STUDENT_ATTENDANCE . "");
        $this->WRITER->save($this->getFileStudentAttendance());
        return array(
            "success" => true
        );
    }
    
    public function jsonSearchStaffAttendance($params)
    {

        ini_set('max_execution_time', 600000);
        set_time_limit(35000);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader("STAFF");
        $entries=$this->getStaffAttendanceArrayResult($params);  //new
        $this->setContent($entries,"STAFF_ID","STAFF"); //new
        $this->EXCEL->getActiveSheet()->setTitle("" . STAFF_ATTENDANCE . "");
        $this->WRITER->save($this->getFileStaffAttendance());
        return array(
            "success" => true
        );
    }

}

?>