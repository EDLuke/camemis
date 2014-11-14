<?php

////////////////////////////////////////////////////////////////////////////////
//@Sor Veasna
//01.08.2014
////////////////////////////////////////////////////////////////////////////////
require_once("Zend/Loader.php");
require_once 'models/export/CamemisExportDBAccess.php';
require_once 'models/training/TrainingAssessmentDBAccess.php';

class TrainingAssessmentExportDBAccess extends CamemisExportDBAccess {
    
    function __construct($objectId) {

        $this->objectId = $objectId;
        parent::__construct();
    }

    public function getUserSelectedColumns() {
        return Utiles::getSelectedGridColumns($this->objectId);
    }
    
    public function getSubjectTraining(){
        $this->DB_STUDENT_TRAINING->trainingId = $this->trainingId; 
        $subjects = $this->DB_STUDENT_TRAINING->listSubjectsTraining();
        $entries = array();
        if($subjects){
            foreach($subjects as $subject){
                $entries["SUB_".$subject->SUBJECT_ID] = $subject;      
            }
        }
        return $entries;
    }

    public function setContentHeader() {
        
        $i = 0;
       
        $subjects = $this->getSubjectTraining();
        foreach ($this->getUserSelectedColumns() as $value) {
            switch ($value) {
                case "STATUS_KEY":
                    $CONST_NAME = "STATUS";
                    $colWidth = 20;
                    break;
                case "CODE":
                    $CONST_NAME = "CODE";
                    $colWidth = 20;
                    break;
                case "STUDENT":
                    $CONST_NAME = "FULL_NAME";
                    $colWidth = 30;
                    break;
                case "RANK":
                    $CONST_NAME = "RANK";
                    $colWidth = 20;
                    break;
                case "AVERAGE":
                    $CONST_NAME = "AVERAGE";
                    $colWidth = 20;
                    break;
                case "GENDER":
                    $CONST_NAME = "GENDER";
                    $colWidth = 10;
                    break;
                case "ASSESSMENT":
                    $CONST_NAME = "ASSESSMENT";
                    $colWidth = 20;
                    break;
                case "SUB_".$subjects[$value]->SUBJECT_ID:
                    $CONST_NAME = htmlspecialchars_decode($subjects[$value]->SUBJECT_SHORT);
                    $colWidth = 15;
                    break;
                default:
                    $CONST_NAME = defined($value) ? constant($value) : $value;
                    $colWidth = 20;
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

    public function setContent() {
        
        $traininAssessment = new TrainingAssessmentDBAccess();
        $traininAssessment->trainingId = $this->trainingId;
        $entries = $traininAssessment->getListTrainingPerformance(true);
        if ($entries) {
            for ($i = 0; $i < count($entries); $i++) {
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

    public function listStudentsClassPerformanceTraining($searchParams) {
        $params = Utiles::setPostDecrypteParams($searchParams);
        ini_set('max_execution_time', 600000);
        set_time_limit(35000);
        $this->trainingId = isset($params['trainingId'])?addText($params['trainingId']):'';
        $this->EXCEL->setActiveSheetIndex(0);
        $this->setContentHeader();
        $this->setContent(); 
        $this->EXCEL->getActiveSheet()->setTitle("" . ASSESSMENT . "");
        $this->WRITER->save($this->getAssessmentStudentTrainingOnClass());

        return array(
            "success" => true
        );
    }

}

?>