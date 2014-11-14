<?php

////////////////////////////////////////////////////////////////////////////////
//@chungveng
//23.08.2014
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';


class GuardianExportDBAccess extends CamemisExportDBAccess {

    function __construct($objectId) {

        $this->objectId = $objectId;
        parent::__construct();
    }

    public function getUserSelectedColumns() {
        return Utiles::getSelectedGridColumns($this->objectId);
    }

    public function setContentHeader() {

        $i = 0;
        foreach ($this->getUserSelectedColumns() as $value) {
            //error_log($value);
            switch ($value) {
                case "CODE":
                    $CONST_NAME = "CODE_ID";
                    $colWidth = 20;
                    break;
                case "LASTNAME":
                    $CONST_NAME = "LASTNAME";
                    $colWidth = 20;
                    break;
                case "FIRSTNAME":
                    $CONST_NAME = "FIRSTNAME";
                    $colWidth = 20;
                    break;
                case "LASTNAME_LATIN":
                    $CONST_NAME = "LASTNAME_LATIN";
                    $colWidth = 20;
                    break;
                case "GENDER":
                    $CONST_NAME = "GENDER";
                    $colWidth = 20;
                    break;
                case "PHONE":
                    $CONST_NAME = "PHONE";
                    $colWidth = 25;
                    break;
                case "EMAIL":
                    $CONST_NAME = "EMAIL";
                    $colWidth = 25;
                    break;
                
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
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
        
        $entries = $this->DB_GUARDIAN->jsonSearchStudentGuardian($searchParams, false);
        
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

    public function jsonGuardian($searchParams) {
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);
        
        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader();
        $this->setContent($searchParams); 
        $this->EXCEL->getActiveSheet()->setTitle("List of Guardian");
        $this->WRITER->save($this->getGuardian());

        return array(
            "success" => true
        );
    }

}

?>