<?

    ///////////////////////////////////////////////////////////
    // @Kaom Vibolrith Senior Software Developer
    // Date: 07.11.2010
    // Am Stollheen 18, 55120 Mainz, Germany
    ///////////////////////////////////////////////////////////
    
    require_once 'excel/phpexcel/Classes/PHPExcel.php';
    require_once 'excel/phpexcel/Classes/PHPExcel/IOFactory.php';
    require_once setUserLoacalization();
    require_once 'excel/phpexcel/Classes/PHPExcel/Cell/AdvancedValueBinder.php';

    class CamemisExcelReport {

        private $iColStart = 1; // 1 == A
        public $iHeaderRowStart = 2;
        private $iHeaderFontSize = 11;
        private $iColumnFontSize = 10;
        public $iRowStart = 3;
        public $highestRow;
        public $highestCol;
        private $ActiveSheet;
        private $objPHPExcel;
        private $FontName = "Times New Roman";

        static function getInstance() {
            static $me;
            if ($me == null) {
                $me = new CamemisExcelReport ();
            }
            return $me;
        }

        private function formatHeaderCell($sCol, $sRow=false, $bCenterAll = false, $vCenterAll = false, $underline=true) {

            $test = $sCol;
            if ($sRow)
                $test .= $sRow;
            else
                $test .= $this->iHeaderRowStart;
            $Style = $this->ActiveSheet->getStyle($test);

            $Font = $Style->getFont();

            $Font->setName($this->FontName);
            $Font->setSize($this->iHeaderFontSize);
            $Font->setBold(true);
            $Font->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
            $this->ActiveSheet->getColumnDimension($sCol)->setAutoSize(true);
            //$Style->getFill ()->setFillType ( PHPExcel_Style_Fill::FILL_SOLID );
            //$Style->getFill ()->getStartColor ()->setARGB ("FF8db4e3");
            if ($bCenterAll)
                $Style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            else
                $Style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if ($vCenterAll)
                $Style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            else
                $Style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            if ($underline)
                $Font->setUnderline(PHPExcel_Style_Font::UNDERLINE_NONE);
        }

        public function formatSecondTitleCell($sCol, $bCenterAll = false) {

            $Style = $this->ActiveSheet->getStyle($sCol);
            $Font = $Style->getFont();

            $Font->setName($this->FontName);
            $Font->setSize(11);
            $Font->setBold(true);
            if ($bCenterAll)
                $Style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        private function getExcelCol($iCol) {
            if ($iCol > 90) {
                $first = floor($iCol / 91) + 64;
                $temp = chr($first);
                $second = $iCol % 91 + + 65;
                $temp .= chr($second);
            } else {
                $temp = chr($iCol);
            }
            return $temp;
        }

        public function createStatisticBloc($title, $tab=false) {

            $highestRow = $this->ActiveSheet->getHighestRow();
            $startRow = $highestRow + 4;

            $this->ActiveSheet->setCellValue("B" . $startRow, $title);
            $this->ActiveSheet->mergeCells("B" . $startRow . ":D" . $startRow);
            $this->formatSecondTitleCell("B" . $startRow, true);
            $this->ActiveSheet->getStyle("B" . $startRow)->getFont()->setName($this->FontName);
            $currentRow = $startRow + 2;
            $a = array("B", "C", "D");
            foreach ($tab as $key => $value) {
                $this->ActiveSheet->setCellValue("B" . $currentRow, constant($value["CONST"]));
                $this->ActiveSheet->setCellValue("C" . $currentRow, $value["NUMBER"]);
                $this->ActiveSheet->setCellValue("D" . $currentRow, $value["PERCENT"]);
                foreach ($a as $key2 => $value2) {
                    $this->ActiveSheet->getStyle($value2 . $currentRow)->getFont()->setName($this->FontName);
                    $this->ActiveSheet->getStyle($value2 . $currentRow)->getFont()->setSize($this->iColumnFontSize);
                    $this->ActiveSheet->getStyle($value2 . $currentRow)->getFont()->setSize($this->iColumnFontSize);
                    $this->ActiveSheet->getStyle($value2 . $currentRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                }
                $currentRow++;
            }
            $range = "B" . $startRow;
            $range .= " : ";
            $range .= "D" . $currentRow;
            $Style = $this->ActiveSheet->getStyle($range);
            $styleArray = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        //                  'color' => array('argb' => 'FFFF0000'),
                    ),
                ),
            );
            $Style->applyFromArray($styleArray);
            return true;
        }

        public function createTitle($title, $column, $row=1, $startColumn="A") {
            $this->ActiveSheet->setCellValue($startColumn . $row, $title);
            $this->ActiveSheet->mergeCells($startColumn . $row . ":" . $column . $row);
            $this->ActiveSheet->getStyle($startColumn . $row . ":" . $column . $row)->getFont()->setName($this->FontName);
            $styleTitle = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'font' => array(
                    'bold' => true,
                    'size' => 15,
                    'color' => array(
                        'argb' => PHPExcel_Style_Color::COLOR_DARKBLUE
                    )
                )
            );
            $this->ActiveSheet->getStyle($startColumn . $row . ":" . $column . $row)->applyFromArray($styleTitle);
        }

        public function createInfoBloc($info, $column, $row=2) {
            $this->ActiveSheet->setCellValue("A" . $row, $info);
            $this->ActiveSheet->mergeCells("A" . $row . ":" . $column . $row);
            $this->ActiveSheet->getStyle("A" . $row . ":" . $column . $row)->getFont()->setName($this->FontName);
            $style = array(
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                ),
                'font' => array(
                    'bold' => true,
                )
            );
            $this->ActiveSheet->getStyle("A" . $row . ":" . $column . $row)->applyFromArray($style);
            //$this->ActiveSheet->getColumnDimension("A:" . $column)->setAutoSize(true);
        }

        public function createInlineTable($rows, $data, $rowStart, $columnStart, $title, $bCenterAll = false, $border = false) {

            $styleBorder = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
            $this->ActiveSheet->setCellValue($columnStart . $rowStart, $title);
            $this->formatSecondTitleCell($columnStart . $rowStart, true);
            $iRow = $rowStart + 2;
            $iCol = $columnStart;

            foreach ($data as $key => $value) {
                $iCol = $columnStart;
                for ($i = 0; $i < count($rows); $i++) {
                    $this->ActiveSheet->setCellValueExplicit($iCol . $iRow, $value [$rows [$i]]);
                    $this->ActiveSheet->getStyle($iCol . $iRow)->getFont()->setName($this->FontName);
                    $this->ActiveSheet->getStyle($iCol . $iRow)->getFont()->setSize($this->iColumnFontSize);
                    if ($bCenterAll) {
                        $this->ActiveSheet->getStyle($iCol . $iRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $this->ActiveSheet->getStyle($iCol . $iRow)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    }
                    if ($border) {

                        $this->ActiveSheet->getStyle($iCol . $iRow)->applyFromArray($styleBorder);
                    }
                    $iCol++;
                }
                $iRow++;
            }
            return true;
        }

        public function createSignatureField($startCol = false, $signature = true) {

            $startRow = $this->highestRow + 3;
            if (!$startCol)
                $startCol = $this->highestCol;
            $this->ActiveSheet->setCellValue($startCol . $startRow, "..............................");


            $startRow += 2;
            if ($signature === true) {
                $this->ActiveSheet->setCellValue($startCol . $startRow, SIGNATURE);
            } elseif ($signature) {
                $this->ActiveSheet->setCellValue($startCol . $startRow, constant($signature));
                $startRow++;
                $this->ActiveSheet->setCellValue($startCol . $startRow, '(' . SIGNATURE . ')');
                $startRow--;
            }
            $startRow -= 2;
            $startCol++;
            $date = DATE . " : " . getShowDate(getCurrentDBDate());
            $this->ActiveSheet->setCellValue($startCol . $startRow, $date);

            return true;
        }

        public function createExcelObject($columns, $ShowColumns, $data, $colors = null, $bCenterAll = false, $border=false) {

            PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

            $this->objPHPExcel = new PHPExcel ();
            $this->objPHPExcel->setActiveSheetIndex(0);
            $this->ActiveSheet = $this->objPHPExcel->getActiveSheet();

            // Header
            $iCol = $this->iColStart + 64;
            for ($i = 0; $i < count($ShowColumns); $i++) {
                $sCol = $this->getExcelCol($iCol);
                $iRow = $this->iHeaderRowStart;
                $this->ActiveSheet->setCellValue($sCol . $iRow, $ShowColumns [$i]);
                $this->formatHeaderCell($sCol, false, $bCenterAll);
                $iCol++;
            }

            // Data
            $iRow = $this->iRowStart;
            foreach ($data as $key => $value) {
                $iCol = $this->iColStart + 64;
                for ($i = 0; $i < count($columns); $i++) {
                    $sCol = $this->getExcelCol($iCol);
                    $this->ActiveSheet->setCellValue($sCol . $iRow, $value [$columns [$i]]);
                    $this->ActiveSheet->getStyle($sCol . $iRow)->getFont()->setName($this->FontName);
                    $this->ActiveSheet->getStyle($sCol . $iRow)->getFont()->setSize($this->iColumnFontSize);
                    $this->ActiveSheet->getColumnDimension($sCol)->setAutoSize(true);
                    //                $this->ActiveSheet->getStyle( $sCol . $iRow )->getAlignment()->setWrapText(true);
                    if ($colors) {
                        $color = $colors[$iRow - $this->iRowStart + 1] [$columns [$i]];
                        if ($color <> 'FFFFFFFF') {
                            $Style = $this->ActiveSheet->getStyle($sCol . $iRow);
                            $Style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                            $Style->getFill()->getStartColor()->setARGB($color);
                        }
                    }
                    $iCol++;
                }
                $iRow++;
            }
            $this->highestRow = count($data) + $this->iRowStart - 1;
            $this->highestCol = $this->getExcelCol(count($columns) + $this->iColStart + 64 - 2);
            // Format ColumnWidth
            $iCol = $this->iColStart + 64;
            for ($i = 0; $i < count($columns); $i++) {
                $sCol = $this->getExcelCol($iCol);
                //$this->ActiveSheet->getColumnDimension($sCol)->setAutoSize(true);
                $iCol++;
            }

            $range = 'A';
            $range .= $this->iHeaderRowStart . ':';
            $range .= $this->getExcelCol(count($columns) + $this->iColStart + 64 - 1);
            $range .= count($data) + $this->iRowStart - 1;
            if ($colors || $border) {
                $Style = $this->ActiveSheet->getStyle($range);
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            //                          'color' => array('argb' => 'FFFF0000'),
                        ),
                    ),
                );
                $Style->applyFromArray($styleArray);                                
                // when setting colors the border is gone - so set them again
                // $Style->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_HAIR);
            }
            if ($bCenterAll) {
                $Style = $this->ActiveSheet->getStyle($range);
                $Style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $Style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }
            //format title/infobloc/header rowheight
            $this->ActiveSheet->getRowDimension(1)->setRowHeight(50);
            $this->ActiveSheet->getRowDimension(2)->setRowHeight(60);
            //$this->ActiveSheet->getRowDimension(3)->setRowHeight(25);

            $this->ActiveSheet->getColumnDimension("A")->setAutoSize(true);
            //$this->ActiveSheet->getColumnDimension("A")->setWidth(5);
            return $this->objPHPExcel;
        }

    }

?>