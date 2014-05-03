<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 29.11.2010
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'include/Common.inc.php';
require_once 'excel/phpexcel/Classes/PHPExcel.php';
require_once 'excel/phpexcel/Classes/PHPExcel/IOFactory.php';
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentSearchDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/staff/StaffDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/schedule/DayScheduleDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/schedule/ScheduleDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentPreschoolDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentAttendanceDBAccess.php";
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentStatusDBAccess.php";//@Visal

error_reporting(E_ALL);

abstract class CamemisExportDBAccess {

    protected $startHeader = 1;

    function __construct()
    {
        $this->EXCEL = new PHPExcel();
        $this->WRITER = PHPExcel_IOFactory::createWriter($this->EXCEL, 'Excel5');
        $this->DB_STUDENT_SEARCH = new StudentSearchDBAccess();
        $this->DB_STUDENT_PRESCHOOL = StudentPreschoolDBAccess::getInstance(); //@Visal
        $this->DB_STUDENT_ATTENDANCE = StudentAttendanceDBAccess::getInstance(); //@veasna
        $this->DB_STUDENT_STATUS = StudentStatusDBAccess::getInstance(); //@Visal
        $this->DB_STAFF = StaffDBAccess::getInstance();
        $this->DB_DAYSCHEDULE = DayScheduleDBAccess::getInstance();
        $this->DB_WEEKSCHEDULE = ScheduleDBAccess::getInstance();
    }

    public function getFileStaffList()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_stafflist.xls";
    }

    public function getFileStudentList()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_studentlist.xls";
    }

    public function getFileStudentPreschoolList()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_studentpreschoollist.xls";
    }

    public function getFileDaySchedule()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_dayschedule.xls";
    }

    public function getFileWeekSchedule()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_weekschedule.xls";
    }

    public function getFileStudentAttendance()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_studentattendancelist.xls";
    }
    //@Visal
    public function getFileStudentStatus()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_studentstatuslist.xls";
    }

    public function getFileStaffAttendance()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_staffattendancelist.xls";
    }

    public function getFileStudentDiscipline()
    {
        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "_studentdisciplinelist.xls";
    }

    public function startContent()
    {
        return $this->startHeader + 1;
    }

    public function setCellStyle($col, $row, $width, $height)
    {
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getAlignment()
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        if ($width)
            $this->EXCEL->getActiveSheet()
                    ->getColumnDimensionByColumn($col)->setWidth($width);

        if ($height)
            $this->EXCEL->getActiveSheet()
                    ->getRowDimension($row)->setRowHeight($height);
    }

    public function setCellContent($col, $row, $content)
    {
        $this->EXCEL->getActiveSheet()
                ->setCellValueByColumnAndRow($col, $row, $content, false);
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getAlignment()->setWrapText(true);
    }

    public function setFontStyle($col, $row, $bold, $fontSize, $fontColor)
    {
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->applyFromArray(array(
                    'font' => array(
                        'bold' => $bold ? true : false,
                        'color' => array('rgb' => $fontColor ? $fontColor : "FFFFFF"),
                        'size' => $fontSize,
                        'name' => 'Arial'
        )));
    }

    public function setFullStyle($col, $row, $bgColor)
    {
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $bgColor ? $bgColor : "FFFFFF")
        )));
    }

    public function setBorderStyle($col, $row, $color = false)
    {
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->applyFromArray(array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => $color ? $color : "DADCDD")
                        ),
                        'left' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => $color ? $color : "DADCDD")
                        ),
                        'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => $color ? $color : "DADCDD")
                        )
        )));
    }

    public static function getStudentObject($Id)
    {
        $SQL = UserAuth::dbAccess()->select()
                ->from("t_student", array('*'))
                ->where("ID = '" . $Id . "'");
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        $data["STUDENT"] = $result->LASTNAME . " " . $result->FIRSTNAME;

        return (object) $data;
    }

    public static function colFilter()
    {
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $bgColor ? $bgColor : "FFFFFF")
        )));
        /*
        foreach ($this->EXCEL->getActiveSheet()->getRowIterator() as $row)
        {
            if ($this->EXCEL->getActiveSheet()->getRowDimension($row->getRowIndex())->getVisible())
            {
                echo '    Row number - ', $row->getRowIndex(), ' ';
                echo $this->EXCEL->getActiveSheet()->getCell(
                        'C' . $row->getRowIndex()
                )->getValue(), ' ';
                echo $objPHPExcel->getActiveSheet()->getCell(
                        'D' . $row->getRowIndex()
                )->getFormattedValue(), ' ';
                echo EOL;
            }
        }
        */
    }

    public function setHeaderInformation()
    {
        $this->setCellStyle(0, 0, 50, 50);
    }

}

?>