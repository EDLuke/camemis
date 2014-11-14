<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') ."/AbsentTypeDBAccess.php";
require_once "models/CamemisTypeDBAccess.php"; 

class TraditionalFilterExportDBAccess extends CamemisExportDBAccess {

    
    
    function __construct($objectId) {

        $this->objectId = $objectId;
        parent::__construct();
    }

    public function getUserSelectedColumns() {
        return Utiles::getSelectedGridColumns($this->objectId);
    }

    public function setContentHeader($searchParams) {
        $gridType = isset($searchParams["gridType"]) ? addText($searchParams["gridType"]) : "";
        $i = 0;
        foreach ($this->getUserSelectedColumns() as $value) {
            switch ($value) {
                case "FIRST_CULUMN":
                    $CONST_NAME = "GRADE";
                    $colWidth = 30;
                    
                    break;
                default:
                    $tmp=explode("_",$value);
                    switch($gridType){
                        case "STUDENT_ATTENDANCE_FILTER":
                            $objectType = AbsentTypeDBAccess::findObjectFromId($tmp[1]);
                            break;
                        default:
                            $objectType = CamemisTypeDBAccess::findObjectFromId($tmp[1]);
                            break;
                    }
                    $CONST_NAME = defined($objectType->NAME) ? constant($objectType->NAME) : $objectType->NAME;
                    $colWidth = 30;
                    break;
            }
            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, $this->startHeader, $COLUMN_NAME);
            $this->setFontStyle($i, $this->startHeader, true, 11, "000000");
            $this->setFullStyle($i, $this->startHeader, "DFE3E8");
            $this->setCellStyle($i, $this->startHeader, $colWidth, 40);

            $i++;
        }
    }

    public function setContent($searchParams) {
        //$campusId = isset($params["campusId"]) ? addText($params["campusId"]) : "";
        $this->DB_FILTER_STUDENT->schoolyearId = isset($searchParams["schoolyearId"]) ? addText($searchParams["schoolyearId"]) : "";
        $this->DB_FILTER_STUDENT->objectType = isset($searchParams["objectType"]) ? addText($searchParams["objectType"]) : "";
        $this->DB_FILTER_STUDENT->personType = isset($searchParams["personType"]) ? addText($searchParams["personType"]) : "";
        $this->DB_FILTER_STUDENT->gridType = isset($searchParams["gridType"]) ? addText($searchParams["gridType"]) : "";
        $this->DB_FILTER_STUDENT->status = isset($searchParams["status"]) ? addText($searchParams["status"]) : "";
        
        switch($this->DB_FILTER_STUDENT->objectType){
            case "CAMPUS":
                $this->DB_FILTER_STUDENT->campusId = isset($searchParams["campusId"]) ? addText($searchParams["campusId"]) : "";
                break;
            case "GRADE":
                $this->DB_FILTER_STUDENT->gradeId = isset($searchParams["gradeId"]) ? addText($searchParams["gradeId"]) : "";
                break;
            case "CLASS":
                $this->DB_FILTER_STUDENT->classId = isset($searchParams["classId"]) ? addText($searchParams["classId"]) : "";
                break;
            
        }
        
        $entries = $this->DB_FILTER_STUDENT->getFilterData();
        
        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + $this->startContent();
                foreach ($this->getUserSelectedColumns() as $colName) {

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

    public function getStudentAttendanceData($searchParams) {
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader($searchParams);
        $this->setContent($searchParams);
        $this->EXCEL->getActiveSheet()->setTitle("Student Fiter");
        $this->WRITER->save($this->getFileStudentList());

        return array(
            "success" => true
        );
    }

}

?>