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
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentStatusDBAccess.php"; //@Visal
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentAdvisoryDBAccess.php"; //@Visal
require_once "models/filter/FilterData.php"; //@Visal
require_once "models/training/StudentTrainingDBAccess.php"; //@CHHE Vathana 
require_once "models/" . Zend_Registry::get('MODUL_API_PATH') . "/student/StudentDBAccess.php"; //@CHHE Vathana

error_reporting(E_ALL);

abstract class CamemisExportDBAccess {

    protected $startHeader = 1;

    function __construct() {
        $this->EXCEL = new PHPExcel();
        $this->WRITER = PHPExcel_IOFactory::createWriter($this->EXCEL, 'Excel5');
        $this->DB_STUDENT_SEARCH = new StudentSearchDBAccess();
        $this->DB_FILTER_STUDENT = new FilterData();//@Visal
        $this->DB_STUDENT_PRESCHOOL = StudentPreschoolDBAccess::getInstance(); //@Visal
        $this->DB_STUDENT_ATTENDANCE = StudentAttendanceDBAccess::getInstance(); //@veasna
        $this->DB_STUDENT_STATUS = StudentStatusDBAccess::getInstance(); //@Visal
        $this->DB_STAFF = StaffDBAccess::getInstance();
        $this->DB_DAYSCHEDULE = DayScheduleDBAccess::getInstance();
        $this->DB_WEEKSCHEDULE = ScheduleDBAccess::getInstance();
        $this->DB_STUDENT_ADVISORY = StudentAdvisoryDBAccess::getInstance(); //@Visal
        $this->DB_STUDENT_TRAINING = StudentTrainingDBAccess::getInstance(); //@CHHE Vathana
        $this->DB_ROOM_LIST = RoomDBAccess::getInstance(); //@CHHE Vathana
        $this->DB_STUDENT_INFO_LIST = StudentDBAccess::getInstance(); //@CHHE Vathana
        $this->DB_STUDENT_PREREQUIREMENT_LIST = StudentDBAccess::getInstance(); //@CHHE Vathana
        
        
    }

    public function getFileStaffList() {
        return self::getUserPhath("_stafflist.xls");
    }

    public function getFileStudentList() {
        return self::getUserPhath("_studentlist.xls");
    }

    public function getFileStudentPreschoolList() {
        return self::getUserPhath("_studentpreschoollist.xls");
    }

    public function getFileDaySchedule() {
        return self::getUserPhath("_dayschedule.xls");
    }

    public function getFileWeekSchedule() {
        return self::getUserPhath("_weekschedule.xls");
    }

    public function getFileStudentAttendance() {
        return self::getUserPhath("_studentattendancelist.xls");
    }

    //@Visal
    public function getFileStudentStatus() {
        return self::getUserPhath("_studentstatuslist.xls");
    }

    public function getFileStudentAvisory() {
        return self::getUserPhath("_studentadvisory.xls");
    }

    public function getFileStaffAttendance() {
        return self::getUserPhath("_staffattendancelist.xls");
    }

    public function getFileStudentDiscipline() {
        return self::getUserPhath("_studentdisciplinelist.xls");
    }
    //@CHHE Vathana
    public function getFileEnrolledStudentYearList() {
        return self::getUserPhath("_enrolledstudentbyyearlist.xls");
    }
    public function getEnrolledStudentTrainingOnTermList() {
        return self::getUserPhath("_enrolledstudentstraningontermlist.xls");
    }
    public function getEnrolledStudentTrainingOnClassList() {
        return self::getUserPhath("_enrolledstudentstraningonclasslist.xls");
    }
    public function getRoomList() {
        return self::getUserPhath("_roomlist.xls");
    }
    public function getStudentPersonalList() {
        return self::getUserPhath("_studentpersonalinformation.xls");
    }
    
    //End...

    public function startContent() {
        return $this->startHeader + 1;
    }

    public function setCellStyle($col, $row, $width, $height, $alignmentLeft = false) {

        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getAlignment()
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        if ($alignmentLeft) {
            $this->EXCEL->getActiveSheet()
                    ->getStyleByColumnAndRow($col, $row)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        } else {
            $this->EXCEL->getActiveSheet()
                    ->getStyleByColumnAndRow($col, $row)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        if ($width)
            $this->EXCEL->getActiveSheet()
                    ->getColumnDimensionByColumn($col)->setWidth($width);

        if ($height)
            $this->EXCEL->getActiveSheet()
                    ->getRowDimension($row)->setRowHeight($height);
    }

    public function setCellContent($col, $row, $content) {
        $this->EXCEL->getActiveSheet()
                ->setCellValueByColumnAndRow($col, $row, $content, false);
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getAlignment()->setWrapText(true);
    }

    public function setFontStyle($col, $row, $bold, $fontSize, $fontColor) {
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

    public function setFullStyle($col, $row, $bgColor) {
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $bgColor ? $bgColor : "FFFFFF")
        )));
    }

    public function setBorderStyle($col, $row, $color = false) {
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

    public function setCellMergeContent($col, $row, $content, $col1, $col2) {

        $this->EXCEL->getActiveSheet()
                ->setCellValueByColumnAndRow($col, $row, $content, false);
        $this->EXCEL->getActiveSheet()
                ->getStyleByColumnAndRow($col, $row)
                ->getAlignment()->setWrapText(true);

        $this->EXCEL->getActiveSheet()
                ->mergeCells("" . $col1 . ":" . $col2 . "");
    }

    public static function getStudentObject($Id) {
        $SQL = UserAuth::dbAccess()->select()
                ->from("t_student", array('*'))
                ->where("ID = '" . $Id . "'");
        $result = UserAuth::dbAccess()->fetchRow($SQL);
        $data["STUDENT"] = $result->LASTNAME . " " . $result->FIRSTNAME;

        return (object) $data;
    }

    public static function getUserPhath($name) {

        return "../public/" . UserAuth::getMyFolder() . "/" . UserAuth::getUserId() . "" . $name . "";
    }

    public function setHeaderInformation() {
        $this->setCellStyle(0, 0, 50, 50);
    }

}

?>