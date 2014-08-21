<?php

////////////////////////////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 31.03.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';

class StaffStatusExportDBAccess extends CamemisExportDBAccess {

    public $columnIndex = 0;
    private $CONST_NAME = null;
    private $colWidth = null;
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
                case "STAFF":
                    $CONST_NAME = "FULL_NAME";
                    $colWidth = 30;
                    break;
                case "START_DATE":
                    $CONST_NAME = "FROM_DATE";
                    $colWidth = 30;
                    break;
                case "END_DATE":
                    $CONST_NAME = "TO_DATE";
                    $colWidth = 20;
                    break;
                case "PHONE":
                    $CONST_NAME = "PHONE";
                    $colWidth = 20;
                    break;
                case "EMAIL":
                    $CONST_NAME = "EMAIL";
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
    
    public function getStaffStatusArrayResult($searchParams){
        $entries = $this->DB_STAFF_STATUS_LIST->jsonSearchStaffStatus($searchParams, false);
        return $entries;         
    }

    public function setContent($entries,$groupId,$groupField)
    {
        if ($entries)
        {
            $GOUPING_DATA = self::getGroupAssoc($entries, $groupId);
            if ($GOUPING_DATA)
            {
                $rowIndex = $this->startContent();

                foreach ($GOUPING_DATA as $dataId => $EACH_DATA)
                {

                    //Name of Contract
                    $colIndex = 0;
                    $GROUP_NAME = $EACH_DATA[0][$groupField];
                    $this->setCellContent($colIndex, $rowIndex, $GROUP_NAME);
                    $this->setFontStyle($colIndex, $rowIndex, true, 10, "FFFFFF");
                    $this->setCellStyle($colIndex, $rowIndex, false, 20);
                    for ($ii = 0; $ii < count($this->getUserSelectedColumns()); $ii++)
                    {
                        $this->setBorderStyle($ii, $rowIndex, "FF6495ED");
                        $this->setFullStyle($ii, $rowIndex,"8DB2E3");
                    }

                    for ($j = 0; $j < count($EACH_DATA); $j++)
                    {

                        $rowIndex++;
                        $colIndex = $this->columnIndex;

                        foreach ($this->getUserSelectedColumns() as $colName)
                        {

                            $CONTENT = isset($EACH_DATA[$j][$colName]) ? $EACH_DATA[$j][$colName] : "";
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

    public function jsonStaffStatus($searchParams)
    {
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);
        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader();
        $entries=$this->getStaffStatusArrayResult($searchParams);
        $this->setContent($entries,"ID","STAFF");
        $this->EXCEL->getActiveSheet()->setTitle('Staff Status');
        $this->WRITER->save($this->getStaffStatus());
        return array(
            "success" => true
        );
    }
}

?>