<?php

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 28.06.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////

require_once("Zend/Loader.php");
require_once 'utiles/Utiles.php';
require_once('excel/excel_reader2.php');
require_once 'include/Common.inc.php';
require_once 'models/app_university/subject/SubjectDBAccess.php';
require_once setUserLoacalization();

class SubjectImportDBAccess extends SubjectDBAccess {

    public function __construct() {
        
    }

    static function getInstance() {
        return new SubjectImportDBAccess();
    }

    public static function dbAccess() {
        return Zend_Registry::get('DB_ACCESS');
    }

    public static function importXLS($params) {

        $xls = new Spreadsheet_Excel_Reader($_FILES["xlsfile"]['tmp_name']);
        $parentId = isset($params["parentId"]) ? $params["parentId"] : "0";

        $facette = self::findSubjectFromId($parentId);

        for ($iCol = 1; $iCol <= $xls->sheets[0]['numCols']; $iCol++) {

            $field = isset($xls->sheets[0]['cells'][1][$iCol]) ? $xls->sheets[0]['cells'][1][$iCol] : "";

            switch ($field) {
                case "SUBJECT_CODE":
                    $Col_SHORT = $iCol;
                    break;
                case "SUBJECT_NAME":
                    $Col_NAME = $iCol;
                    break;
                case "CREDIT":
                    $Col_CREDIT = $iCol;
                    break;
            }
        }

        for ($i = 1; $i <= $xls->sheets[0]['numRows']; $i++) {

            $SHORT = isset($xls->sheets[0]['cells'][$i + 2][$Col_SHORT]) ? $xls->sheets[0]['cells'][$i + 2][$Col_SHORT] : "";

            $NAME = isset($xls->sheets[0]['cells'][$i + 2][$Col_NAME]) ? $xls->sheets[0]['cells'][$i + 2][$Col_NAME] : "";

            $CREDIT = isset($xls->sheets[0]['cells'][$i + 2][$Col_CREDIT]) ? $xls->sheets[0]['cells'][$i + 2][$Col_CREDIT] : "";

            $IMPORT_DATA['ID'] = self::findLastId() + 1000;
            $IMPORT_DATA['SHORT'] = $SHORT;
            $IMPORT_DATA['GUID'] = generateGuid();

            switch (UserAuth::systemLanguage()) {
                case "VIETNAMESE":
                    $IMPORT_DATA['NAME'] = setImportChartset($NAME);
                    break;
                default:
                    $IMPORT_DATA['NAME'] = addText($NAME);
                    break;
            }

            $IMPORT_DATA['NUMBER_CREDIT'] = $CREDIT;

            if ($facette) {
                if ($facette->EDUCATION_TYPE) {
                    $IMPORT_DATA['EDUCATION_TYPE'] = $facette->EDUCATION_TYPE;
                } else {
                    $IMPORT_DATA['EDUCATION_TYPE'] = $parentId;
                }
            }

            $IMPORT_DATA['PARENT'] = $parentId;
            $IMPORT_DATA['CREATED_DATE'] = getCurrentDBDateTime();
            $IMPORT_DATA['CREATED_BY'] = Zend_Registry::get('USER')->CODE;

            if (isset($SHORT) && isset($NAME)) {

                if ($SHORT && $NAME)
                    self::dbAccess()->insert('t_subject', $IMPORT_DATA);
            }
        }
        return array("success" => true);
    }

}

?>