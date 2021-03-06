<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';

class StudentExportDBAccess extends CamemisExportDBAccess {

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

            switch ($value) {
                case "STATUS_KEY":
                    $CONST_NAME = "STATUS";
                    $colWidth = 20;
                    break;
                case "CODE":
                    $CONST_NAME = "CODE_ID";
                    $colWidth = 20;
                    break;
                case "STUDENT_SCHOOL_ID":
                    $CONST_NAME = "STUDENT_SCHOOL_ID";
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
                case "CURRENT_ACADEMIC":
                    $CONST_NAME = CURRENT_LEVEL;
                    $colWidth = 30;
                    break;
                case "CURRENT_SCHOOLYEAR":
                    $CONST_NAME = CURRENT_SCHOOL_YEAR;
                    $colWidth = 30;
                    break;
                case "TRAINING_TERM":
                    $CONST_NAME = CURRENT_TERM;
                    $colWidth = 30;
                    break;
                case "CURRENT_COURSE":
                    $CONST_NAME = TRAINING_PROGRAMS;
                    $colWidth = 30;
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
        $entries = $this->DB_STUDENT_SEARCH->searchStudents($searchParams, false);
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

    public function studentSearch($searchParams) {

        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader();
        $this->setContent($searchParams);
        $this->EXCEL->getActiveSheet()->setTitle("" . LIST_OF_STUDENTS . "");
        $this->WRITER->save($this->getFileStudentList());

        return array(
            "success" => true
        );
    }

    ///////////////////////////////////////////////////////////////////////////////////
    //Enrolled Student By Year
    //@CHHE Vathana
    //////////////////////////////////////////////////////////////////////////////////

    public function getTopHeader() {

        $DB_SCHOOLYEAR = AcademicDateDBAccess::getInstance();

        $CURRENT_SCHOOLYEAR = $DB_SCHOOLYEAR->isCurrentSchoolyear($this->facette->SCHOOL_YEAR);
        $TERM_NUMBER = AcademicDBAccess::findAcademicTerm($this->facette->NAME);
        $name = "Dara";
        $this->setCellContent(0, 1, $TERM_NUMBER);
        $this->setFontStyle(0, 1, true, 11, "000000");
        $this->setFullStyle(0, 1, "DFE3E8");
    }

    public function setEnrolledStudentYearContentHeader() {

        $i = 0;
        foreach ($this->getUserSelectedColumns() as $value) {

            switch ($value) {
                case "STATUS_KEY":
                    $CONST_NAME = "STATUS";
                    $colWidth = 20;
                    break;
                case "CODE":
                    $CONST_NAME = "CODE_ID";
                    $colWidth = 20;
                    break;
                case "STUDENT_SCHOOL_ID":
                    $CONST_NAME = "STUDENT_SCHOOL_ID";
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
                case "CURRENT_ACADEMIC":
                    $CONST_NAME = CURRENT_LEVEL;
                    $colWidth = 30;
                    break;
                case "CURRENT_SCHOOLYEAR":
                    $CONST_NAME = CURRENT_SCHOOL_YEAR;
                    $colWidth = 30;
                    break;
                case "TRAINING_TERM":
                    $CONST_NAME = CURRENT_TERM;
                    $colWidth = 30;
                    break;
                case "CURRENT_COURSE":
                    $CONST_NAME = TRAINING_PROGRAMS;
                    $colWidth = 30;
                    break;
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 30;
                    break;
            }

            $COLUMN_NAME = defined($CONST_NAME) ? constant($CONST_NAME) : $CONST_NAME;
            $this->setCellContent($i, 5, $COLUMN_NAME);
            $this->setFontStyle($i, 5, true, 11, "000000");
            $this->setFullStyle($i, 5, "DFE3E8");
            $this->setCellStyle($i, 5, $colWidth, 40);

            $i++;
        }
    }

    public function setEnrolledStudentYearContent($searchParams) {
        $entries = $this->DB_STUDENT_SEARCH->searchStudents($searchParams, false);
        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {
                $colIndex = 0;
                $rowIndex = $i + 6;
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

    public function enrolledStudentYearSearch($searchParams) {
        
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);

        $this->getTopHeader();
        $this->EXCEL->setActiveSheetIndex(0);
        $this->setEnrolledStudentYearContentHeader();
        $this->setEnrolledStudentYearContent($searchParams);
        $this->EXCEL->getActiveSheet()->setTitle("" . LIST_OF_STUDENTS . "");
        $this->WRITER->save($this->getFileEnrolledStudentYearList());

        return array(
            "success" => true
        );
    }

}

?>