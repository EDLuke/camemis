<?php
	///////////////////////////////////////////////////////////
    // @sor veasna
    // Date: 01/03/2013
    // Adress
    /////////////////////////////////////////////////////////// 
 	require_once 'excel/phpexcel/Classes/PHPExcel.php';
    require_once 'excel/phpexcel/Classes/PHPExcel/IOFactory.php';
    require_once 'clients/CamemisExcelReport.php';
    class CamemisExportToExcel extends CamemisExcelReport{
        public $CamemisExcelReport;        
        public $objPHPExcel;
        public function __construct() {  
            $this->CamemisExcelReport = CamemisExcelReport::GetInstance();

        }
        public static function getSchoolInfo(){ 
            $schoolObject = Zend_Registry::get('SCHOOL');
            return  $schoolObject;  
        }
        /*  vesna old function
        
        public static function entreeDatas($results,$fildShows){
            $i=0;
            $entries=array();
            
            if($results){
                if (is_object($results[0])){
                   foreach($results as $value){
                    $j=$i+1;
                        if ($fildShows){
                            foreach ($fildShows as $fildname){  
                                if ($fildname=="NA"){
                                    $entries[$i][$fildname]=$j; 
                                }
                                elseif($fildname=="GENDER"){
                                    $entries[$i][$fildname]= isset($value->$fildname)?getGenderName($value->GENDER): "";    
                                }
                                else{  
                                    $entries[$i][$fildname]= isset($value->$fildname)?$value->$fildname: "";      
                                }    
                            }  
                        }
                    ++$i;
                    } 
                }else{
                    foreach($results as $value){
                        $j=$i+1;
                        if ($fildShows){
                            foreach ($fildShows as $fildname){  
                                if ($fildname=="NA"){
                                    $entries[$i][$fildname]=$j; 
                                }
                                elseif($fildname=="GENDER"){
                                    $entries[$i][$fildname]= isset($value[$fildname])?getGenderName($value['GENDER']): "";    
                                }
                                else{  
                                    $entries[$i][$fildname]= isset($value[$fildname])?$value[$fildname]: "";      
                                }    
                            }  
                        }
                        ++$i;
                    }
                }
            }
            return $entries;              
        }
        */
        
        public static function entreeDatas($results,$fildShows){
            $i=0;
            $entries=array();
            if($results){
                if (is_object($results[0])){
                   foreach($results as $value){
                    $j=$i+1;
                        if ($fildShows){
                            foreach ($fildShows as $fildname){  
                                if ($fildname=="NA"){
                                    $entries[$i][$fildname]=$j; 
                                }
                                elseif($fildname=="GENDER"){
                                    $entries[$i][$fildname]= isset($value->$fildname)?getGenderName($value->GENDER): "";    
                                }
                                else{  
                                    $entries[$i][$fildname]= isset($value->$fildname)?$value->$fildname: "";      
                                }    
                            }  
                        }
                    ++$i;
                    } 
                }else{
                    foreach($results as $value){
                        $j=$i+1;
                        if ($fildShows){
                            foreach ($fildShows as $fildname){  
                                if ($fildname=="NA"){
                                    $entries[$i][$fildname]=$j; 
                                }
                                elseif($fildname=="GENDER"){
                                    $entries[$i][$fildname]= isset($value[$fildname])?getGenderName($value['GENDER']): "";    
                                }
                                else{  
                                    $entries[$i][$fildname]= isset($value[$fildname])?$value[$fildname]: "";      
                                }    
                            }  
                        }
                        ++$i;
                    }
                }
            }
            return $entries;              
        }
        
        public function objExcel($results,$columns,$ShowColumns,$rowStart=2){
            $entries=self::entreeDatas($results,$columns);
            $this->CamemisExcelReport->iHeaderRowStart = $rowStart;
            $this->CamemisExcelReport->iRowStart = $rowStart+1;
            $this->objPHPExcel = $this->CamemisExcelReport->createExcelObject($columns, $ShowColumns, $entries, null, false, true);
            $this->objPHPExcel->getActiveSheet()->getRowDimension($rowStart)->setRowHeight(30);
            
            return  $this->objPHPExcel;
        } 
        public function excelHeader($param,$numRow,$endCharExcel){
            $style1 = array(
            'alignment' => array(
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
            );

            if(isset($param['SCHOOL_INFO'])){
                $this->CamemisExcelReport->createTitle($param['SCHOOL_INFO'], $endCharExcel, $numRow, "A");
                $this->objPHPExcel->getActiveSheet()->getRowDimension($numRow)->setRowHeight(70);
                $this->objPHPExcel->getActiveSheet()->getStyle("A".$numRow.":".$endCharExcel.$numRow)->applyFromArray($style1); 
            }
            if(isset($param['CLASS_INFO'])){
                $row=$numRow+1;
                $this->objPHPExcel->getActiveSheet()->setCellValue("A".$row,  $param['CLASS_INFO']);    
                $this->objPHPExcel->getActiveSheet()->mergeCells("A".$row.":".$endCharExcel.$row);
                $this->objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(115);
                $this->objPHPExcel->getActiveSheet()->getStyle("A".$row.":".$endCharExcel.$row)->applyFromArray($style1);
            }         
        }
        public function excelFooter(){
            $this->CamemisExcelReport->createSignatureField(); 
        }
        public function save($filename){
            header("Content-Type: application/x-msexcel; name=\"" . $filename . ".xls\"");
            header("Content-Disposition: attachment; filename=\"" . $filename . ".xls\"");
            // IE <= 6 cache fix
            header('Expires: 0');
            header('Pragma: cache');
            header('Cache-Control: private');
            $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
            $objWriter->save('php://output'); 
        }  
    } 
?>