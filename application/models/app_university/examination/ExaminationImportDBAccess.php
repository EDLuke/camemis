<?php

    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 07.11.2010
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////

    require_once("Zend/Loader.php");
    require_once('excel/excel_reader2.php');
    require_once 'utiles/Utiles.php';
    require_once 'include/Common.inc.php';
    require_once setUserLoacalization();

    class ExaminationImportDBAccess extends ExaminationDBAccess {

        static function getInstance() {
            static $me;
            if ($me == null) {
                $me = new ExaminationImportDBAccess();
            }
            return $me;
        }

        public function importXLS($params) {

            $GuId = isset($params["GuId"]) ? $params["GuId"] : "";

            $facette = self::findExamFromId($GuId);

            $xls = new Spreadsheet_Excel_Reader($_FILES["xlsfile"]['tmp_name']);

            for ($i = 2; $i <= $xls->sheets[0]['numRows']; $i++) {

                $examCode = isset($xls->sheets[0]['cells'][$i + 1][1])?$xls->sheets[0]['cells'][$i + 1][1]:"";
                $examPoints = isset($xls->sheets[0]['cells'][$i + 1][2])?$xls->sheets[0]['cells'][$i + 1][2]:"";
                $examComment = isset($xls->sheets[0]['cells'][$i + 1][3])?$xls->sheets[0]['cells'][$i + 1][3]:"";

                //////////////////////////////////////???????
            }
        }
    }

?>