<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';
require_once "models/DisciplineDBAccess.php";

class StudentDisciplineExportDBAccess extends CamemisExportDBAccess {
    public $columnIndex=0;
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
                case "DISCIPLINE_TYPE":
                    $CONST_NAME = INFRACTION_TYPE;
                    $colWidth = 60;
                    $this->columnIndex=1;
                    break;
                case "CODE":
                    $CONST_NAME = CODE_ID;
                    $colWidth = $this->columnIndex?20:60;
                    break;
                case "STUDENT":
                    $CONST_NAME = FULL_NAME;
                    $colWidth = 40;
                    break;
                case "GENDER":
                    $CONST_NAME = GENDER;
                    $colWidth = 20;
                    break;
                case "INFRACTION_DATE":
                    $CONST_NAME = INFRACTION_DATE;
                    $colWidth = 20;
                    break;
                case "PUNISHMENT_TYPE":
                    $CONST_NAME = PUNISHMENT_TYPE;
                    $colWidth = 30;
                    break;
                case "CURRENT_ACADEMIC":
                    $CONST_NAME = CURRENT_CLASS;
                    $colWidth = 20;
                    break;
                case "CURRENT_SCHOOLYEAR":
                    $CONST_NAME = CURRENT_SCHOOL_YEAR;
                    $colWidth = 40;
                    break;
                case "CURRENT_COURSE":
                    $CONST_NAME = CURRENT_TERM;
                    $colWidth = 30;
                    break;
                case "TRAINING_NAME":
                    $CONST_NAME = COURSE;
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

    public static function getGroupAssoc($array, $key)
    {
        $return = array();
        foreach ($array as $v)
        {
            $return[$v[$key]][] = $v;
        }
        return $return;
    }

    public function setContent($searchParams)
    {
        $DB_STUDENT_DISCIPLINE = new DisciplineDBAccess();
        $entries = $DB_STUDENT_DISCIPLINE->jsonListByDicipline($searchParams, false);
        if ($entries)
        {

            $GOUPING_DATA = self::getGroupAssoc($entries, "DISCIPLINE_TYPE_ID");
            if ($GOUPING_DATA)
            {

                $rowIndex = $this->startContent();

                foreach ($GOUPING_DATA as $discipline_type_Id => $DISCIPLINE_STUDENT_DATA)
                {

                    //Name of Student
                    $colIndex = 0;
                    $groupField = "DISCIPLINE_TYPE";
                    $STUDENT_NAME = $DISCIPLINE_STUDENT_DATA[0][$groupField];
                    $this->setCellContent($colIndex, $rowIndex, $STUDENT_NAME);
                    $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                    $this->setCellStyle($colIndex, $rowIndex, false, 20);
                    for ($ii = 0; $ii < count($this->getUserSelectedColumns()); $ii++)
                    {
                        $this->setBorderStyle($ii, $rowIndex, "FF6495ED");
                        $this->setFullStyle($ii, $rowIndex,"8DB2E3");
                    }

                    for ($j = 0; $j < count($DISCIPLINE_STUDENT_DATA); $j++)
                    {

                        $rowIndex++;
                        $colIndex = $this->columnIndex;

                        foreach ($this->getUserSelectedColumns() as $colName)
                        {

                            $CONTENT = isset($DISCIPLINE_STUDENT_DATA[$j][$colName]) ? $DISCIPLINE_STUDENT_DATA[$j][$colName] : "";
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

    public function jsonSearchStudentDiscipline($params)
    {

        ini_set('max_execution_time', 600000);
        set_time_limit(35000);

        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader();
        $this->setContent($params);
        $this->EXCEL->getActiveSheet()->setTitle("" . STUDENT_DISCIPLINE . "");
        $this->WRITER->save($this->getFileStudentDiscipline());
        return array(
            "success" => true
        );
    }

}

?>