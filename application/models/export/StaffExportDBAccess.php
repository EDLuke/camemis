<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';

class StaffExportDBAccess extends CamemisExportDBAccess {

    function __construct($objectId)
    {

        $this->objectId = $objectId;
        parent::__construct();
    }

    public function getUserSelectedColumns()
    {
        return Utiles::getSelectedGridColumns($this->objectId);
    }

    public function setContentHeader()
    {

        $i = 0;
        foreach ($this->getUserSelectedColumns() as $value)
        {
            switch ($value)
            {
                case "STATUS_KEY":
                    $CONST_NAME = "STATUS";
                    $colWidth = 20;
                    break;
                case "CODE":
                    $CONST_NAME = "CODE_ID";
                    $colWidth = 20;
                    break;
                case "STAFF_SCHOOL_ID":
                    $CONST_NAME = "STAFF_SCHOOL_ID";
                    $colWidth = 20;
                    break;
                case "LASTNAME":
                    $CONST_NAME = "LASTNAME";
                    $colWidth = 20;
                    break;
                case "LASTNAME_LATIN":
                    $CONST_NAME = "LASTNAME_LATIN";
                    $colWidth = 25;
                    break;
                case "GENDER":
                    $CONST_NAME = "GENDER";
                    $colWidth = 15;
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
            $this->setBorderStyle($i, $this->startHeader, "DADCDD");

            $i++;
        }
    }

    public function setContent($searchParams)
    {

        $entries = $this->DB_STAFF->searchStaffs($searchParams, false);

        if ($entries)
        {
            for ($i = 0; $i <= count($entries); $i++)
            {
                $colIndex = 0;
                $rowIndex = $i + $this->startContent();
                foreach ($this->getUserSelectedColumns() as $colName)
                {

                    $STATUS_KEY = isset($entries[$i]["STATUS_KEY"]) ? $entries[$i]["STATUS_KEY"] : "";
                    $CONTENT = isset($entries[$i][$colName]) ? $entries[$i][$colName] : "";
                    $BG_COLOR = isset($entries[$i]["BG_COLOR"]) ? $entries[$i]["BG_COLOR"] : "";

                    switch ($colName)
                    {
                        case "STATUS_KEY":
                            $this->setCellContent($colIndex, $rowIndex, $STATUS_KEY);
                            $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                            $this->setFullStyle($colIndex, $rowIndex, substr($BG_COLOR, 1));
                            $this->setCellStyle($colIndex, $rowIndex, false, 15);
                            $this->setBorderStyle($colIndex, $rowIndex, "DADCDD");
                            break;
                        case "GENDER":
                            $CONTENT = isset($entries[$i]["GENDER"]) ? $entries[$i]["GENDER"] : "";
                            $this->setCellContent($colIndex, $rowIndex, $CONTENT);
                            $this->setFontStyle($colIndex, $rowIndex, false, 9, "000000");
                            $this->setCellStyle($colIndex, $rowIndex, false, 15);
                            break;
                        default:
                            if ($CONTENT)
                            {
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

    public function staffSearch($searchParams)
    {
        ini_set('max_execution_time', 600000);
        set_time_limit (35000);
        
        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader();
        $this->setContent($searchParams);
        $this->EXCEL->getActiveSheet()->setTitle("" . LIST_OF_STAFFS . "");
        $this->WRITER->save($this->getFileStaffList());

        return array(
            "success" => true
        );
    }

}

?>