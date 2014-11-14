<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';

class ScheduleExportDBAccess extends CamemisExportDBAccess {

    function __construct($academicId, $trainingId) {
        $this->academicId = $academicId;
        $this->trainingId = $trainingId;
        parent::__construct();
    }

    public function setWeekContentHeader() {

        $this->setCellContent(0, $this->startHeader, TIME);
        $this->setCellStyle(0, $this->startHeader, 20, 40);
        $this->setFontStyle(0, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(0, $this->startHeader, "DFE3E8");
        //////////
        $this->setCellContent(1, $this->startHeader, MONDAY);
        $this->setCellStyle(1, $this->startHeader, 30, 40);
        $this->setFontStyle(1, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(1, $this->startHeader, "DFE3E8");
        ///////////
        $this->setCellContent(2, $this->startHeader, TUESDAY);
        $this->setCellStyle(2, $this->startHeader, 30, 40);
        $this->setFontStyle(2, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(2, $this->startHeader, "DFE3E8");
        ///////////
        $this->setCellContent(3, $this->startHeader, WEDNESDAY);
        $this->setCellStyle(3, $this->startHeader, 30, 40);
        $this->setFontStyle(3, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(3, $this->startHeader, "DFE3E8");
        ///////////
        $this->setCellContent(4, $this->startHeader, THURSDAY);
        $this->setCellStyle(4, $this->startHeader, 30, 40);
        $this->setFontStyle(4, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(4, $this->startHeader, "DFE3E8");

        $this->setCellContent(5, $this->startHeader, FRIDAY);
        $this->setCellStyle(5, $this->startHeader, 30, 40);
        $this->setFontStyle(5, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(5, $this->startHeader, "DFE3E8");

        $this->setCellContent(6, $this->startHeader, SATURDAY);
        $this->setCellStyle(6, $this->startHeader, 30, 40);
        $this->setFontStyle(6, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(6, $this->startHeader, "DFE3E8");

        $this->setCellContent(6, $this->startHeader, SUNDAY);
        $this->setCellStyle(6, $this->startHeader, 30, 40);
        $this->setFontStyle(6, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(6, $this->startHeader, "DFE3E8");
    }

    public function setWeekContent($searchParams) {
        $entries = $this->DB_WEEKSCHEDULE->loadClassEvents($searchParams, false);
        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {
                $j = $i + $this->startContent();

                $TIME = isset($entries[$i]["TIME"]) ? $entries[$i]["TIME"] : "";
                $MO = isset($entries[$i]["MO"]) ? str_replace("<br>", "\r", $entries[$i]["MO"]) : "";
                $MO_COLOR = isset($entries[$i]["MO_COLOR"]) ? substr($entries[$i]["MO_COLOR"], 1) : "";
                $MO_COLOR_FONT = isset($entries[$i]["MO_COLOR_FONT"]) ? substr($entries[$i]["MO_COLOR_FONT"], 1) : "";

                $TU = isset($entries[$i]["TH"]) ? str_replace("<br>", "\r", $entries[$i]["TU"]) : "";
                $TU_COLOR = isset($entries[$i]["TU_COLOR"]) ? substr($entries[$i]["TU_COLOR"], 1) : "";
                $TU_COLOR_FONT = isset($entries[$i]["TU_COLOR_FONT"]) ? substr($entries[$i]["TU_COLOR_FONT"], 1) : "";

                $WE = isset($entries[$i]["WE"]) ? str_replace("<br>", "\r", $entries[$i]["WE"]) : "";
                $WE_COLOR = isset($entries[$i]["WE_COLOR"]) ? substr($entries[$i]["WE_COLOR"], 1) : "";
                $WE_COLOR_FONT = isset($entries[$i]["WE_COLOR_FONT"]) ? substr($entries[$i]["WE_COLOR_FONT"], 1) : "";

                $TH = isset($entries[$i]["TH"]) ? str_replace("<br>", "\r", $entries[$i]["TH"]) : "";
                $TH_COLOR = isset($entries[$i]["TH_COLOR"]) ? substr($entries[$i]["TH_COLOR"], 1) : "";
                $TH_COLOR_FONT = isset($entries[$i]["TH_COLOR_FONT"]) ? substr($entries[$i]["TH_COLOR_FONT"], 1) : "";

                $FR = isset($entries[$i]["FR"]) ? str_replace("<br>", "\r", $entries[$i]["FR"]) : "";
                $FR_COLOR = isset($entries[$i]["FR_COLOR"]) ? substr($entries[$i]["FR_COLOR"], 1) : "";
                $FR_COLOR_FONT = isset($entries[$i]["FR_COLOR_FONT"]) ? substr($entries[$i]["FR_COLOR_FONT"], 1) : "";

                $SA = isset($entries[$i]["SA"]) ? str_replace("<br>", "\r", $entries[$i]["SA"]) : "";
                $SA_COLOR = isset($entries[$i]["SA_COLOR"]) ? substr($entries[$i]["SA_COLOR"], 1) : "";
                $SA_COLOR_FONT = isset($entries[$i]["SA_COLOR_FONT"]) ? substr($entries[$i]["SA_COLOR_FONT"], 1) : "";

                $SU = isset($entries[$i]["SU"]) ? str_replace("<br>", "\r", $entries[$i]["SU"]) : "";
                $SU_COLOR = isset($entries[$i]["SU_COLOR"]) ? substr($entries[$i]["SU_COLOR"], 1) : "";
                $SU_COLOR_FONT = isset($entries[$i]["SU_COLOR_FONT"]) ? substr($entries[$i]["SU_COLOR_FONT"], 1) : "";

                $this->setCellContent(0, $j, $TIME);
                $this->setFontStyle(0, $j, true, 9, "000000");
                $this->setFullStyle(0, $j, "FFFFFF");
                $this->setCellStyle(0, $j, false, 80);

                if ($MO) {
                    $this->setCellContent(1, $j, $MO);
                    $this->setFontStyle(1, $j, false, 9, $MO_COLOR_FONT);
                    $this->setFullStyle(1, $j, $MO_COLOR);
                    $this->setCellStyle(1, $j, false, 80);
                }

                if ($TU) {
                    $this->setCellContent(2, $j, $TU);
                    $this->setFontStyle(2, $j, false, 9, $TU_COLOR_FONT);
                    $this->setFullStyle(2, $j, $TU_COLOR);
                    $this->setCellStyle(2, $j, false, 80);
                }

                if ($WE) {
                    $this->setCellContent(3, $j, $WE);
                    $this->setFontStyle(3, $j, false, 9, $WE_COLOR_FONT);
                    $this->setFullStyle(3, $j, $WE_COLOR);
                    $this->setCellStyle(3, $j, false, 80);
                }

                if ($TH) {
                    $this->setCellContent(4, $j, $TH);
                    $this->setFontStyle(4, $j, false, 9, $TH_COLOR_FONT);
                    $this->setFullStyle(4, $j, $TH_COLOR);
                    $this->setCellStyle(4, $j, false, 80);
                }

                if ($FR) {
                    $this->setCellContent(5, $j, $FR);
                    $this->setFontStyle(5, $j, false, 9, $FR_COLOR_FONT);
                    $this->setFullStyle(5, $j, $FR_COLOR);
                    $this->setCellStyle(5, $j, false, 80);
                }

                if ($SA) {
                    $this->setCellContent(6, $j, $SA);
                    $this->setFontStyle(6, $j, false, 9, $SA_COLOR_FONT);
                    $this->setFullStyle(6, $j, $SA_COLOR);
                    $this->setCellStyle(6, $j, false, 80);
                }
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////
    //DAY SCHEDULE....
    ////////////////////////////////////////////////////////////////////////////
    public function setDayContentHeader() {

        $this->setCellContent(0, $this->startHeader, TIME);
        $this->setCellStyle(0, $this->startHeader, 20, 40);
        $this->setFontStyle(0, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(0, $this->startHeader, "DFE3E8");
        //////////
        $this->setCellContent(1, $this->startHeader, EVENT);
        $this->setCellStyle(1, $this->startHeader, 40, 40);
        $this->setFontStyle(1, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(1, $this->startHeader, "DFE3E8");
        ///////////
        $this->setCellContent(2, $this->startHeader, TEACHER);
        $this->setCellStyle(2, $this->startHeader, 30, 40);
        $this->setFontStyle(2, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(2, $this->startHeader, "DFE3E8");
        ///////////
        $this->setCellContent(3, $this->startHeader, ROOM);
        $this->setCellStyle(3, $this->startHeader, 20, 40);
        $this->setFontStyle(3, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(3, $this->startHeader, "DFE3E8");
        ///////////
        $this->setCellContent(4, $this->startHeader, STATUS);
        $this->setCellStyle(4, $this->startHeader, 30, 40);
        $this->setFontStyle(4, $this->startHeader, true, 11, "000000");
        $this->setFullStyle(4, $this->startHeader, "DFE3E8");
    }

    public function setDayContent($searchParams) {
        $entries = $this->DB_DAYSCHEDULE->dayEventList($searchParams, false);
        if ($entries) {
            for ($i = 0; $i <= count($entries); $i++) {
                $j = $i + $this->startContent();

                $TIME = isset($entries[$i]["TIME"]) ? $entries[$i]["TIME"] : "";
                $EVENT = isset($entries[$i]["EVENT"]) ? $entries[$i]["EVENT"] : "";
                $TEACHER = isset($entries[$i]["TEACHER"]) ? $entries[$i]["TEACHER"] : "";
                $STATUS = isset($entries[$i]["STATUS"]) ? $entries[$i]["STATUS"] : "";
                $ROOM = isset($entries[$i]["ROOM"]) ? $entries[$i]["ROOM"] : "";
                $COLOR_FONT = isset($entries[$i]["COLOR_FONT"]) ? $entries[$i]["COLOR_FONT"] : "";
                $COLOR = isset($entries[$i]["COLOR"]) ? $entries[$i]["COLOR"] : "";

                if ($TIME) {
                    $this->setCellContent(0, $j, $TIME);
                    $this->setFontStyle(0, $j, true, 9, substr($COLOR_FONT, 1));
                    $this->setFullStyle(0, $j, substr($COLOR, 1));
                }

                if ($EVENT) {
                    $this->setCellContent(1, $j, $EVENT);
                    $this->setFontStyle(1, $j, false, 9, substr($COLOR_FONT, 1));
                    $this->setFullStyle(1, $j, substr($COLOR, 1));
                }

                if ($TEACHER) {
                    $this->setCellContent(2, $j, $TEACHER);
                    $this->setFontStyle(2, $j, false, 9, substr($COLOR_FONT, 1));
                    $this->setFullStyle(2, $j, substr($COLOR, 1));
                }

                if ($ROOM) {
                    $this->setCellContent(3, $j, $ROOM);
                    $this->setFontStyle(3, $j, false, 9, substr($COLOR_FONT, 1));
                    $this->setFullStyle(3, $j, substr($COLOR, 1));
                }

                if ($STATUS) {
                    $this->setCellContent(4, $j, $STATUS);
                    $this->setFontStyle(4, $j, false, 9, substr($COLOR_FONT, 1));
                    $this->setFullStyle(4, $j, substr($COLOR, 1));
                }

                $this->setCellStyle(0, $j, false, 45);
                $this->setCellStyle(1, $j, false, 45);
                $this->setCellStyle(2, $j, false, 45);
                $this->setCellStyle(3, $j, false, 45);
                $this->setCellStyle(4, $j, false, 45);
            }
        }
    }

    public function dayEventList($encrypParams) {
        $searchParams = Utiles::setPostDecrypteParams($encrypParams);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setDayContentHeader();
        $this->setDayContent($searchParams);
        $this->EXCEL->getActiveSheet()->setTitle("" . SCHEDULE . "");
        $this->WRITER->save($this->getFileDaySchedule());

        return array(
            "success" => true
        );
    }

    public function loadClassEvents($encrypParams) {
        $searchParams = Utiles::setPostDecrypteParams($encrypParams);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setWeekContentHeader();
        $this->setWeekContent($searchParams);
        $this->EXCEL->getActiveSheet()->setTitle("" . SCHEDULE . "");
        $this->WRITER->save($this->getFileWeekSchedule());
        return array(
            "success" => true
        );
    }

}

?>