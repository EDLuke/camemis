<?php

///////////////////////////////////////////////////////////
// @Chuy Thong Senior Software Developer
// Date: 29.06.2012
// 79Bz, Phnom Penh, Cambodia
///////////////////////////////////////////////////////////
require_once 'IGoogleChart.php';

class GoogleTableChart implements IChart {
    
    protected $objectName = null; 
    protected $object = null;
    protected $modul = 'modul';
    
    private $headers;
    private $data;
    private $width;
    private $height;
    private $config_options = 'null';
    private $position;
    
    public function __construct($object, $modul = 'modul') {
        $this->object = $object;
        $this->modul = $modul;
    }
    
    public function getObjectId() {
        if ($this->modul) {
            return "googletable_" . strtolower($this->object) . "_" . strtolower($this->modul);
        } else {
            return "googletable_" . strtolower($this->object);
        }
    }
    
    public function setConfigOption($options) {
        $html = "";
        if ($options) {
            $html .= "{" . implode(",", $options) . "}";
        }
        $this->config_options = $html;
        /*
        foreach($options as $k => $v) {
            if (isset($v[$k])) {
                switch ($k) {
                    case "allowHtml": 
                        $html .= "allowHtml:" . $v[$k] . ",";
                        break;
                    case "alternatingRowStyle":
                        $html .= "alternatingRowStyle:" . ",";
                    case cssClassNames: null|{headerRow: cssHeaderRow, tableRow: cssTableRow, oddTableRow: cssOddTableRow, selectedTableRow: cssSelectedTableRow,
                    hoverTableRow: cssHoverTableRow, headerCell: cssHeaderCell, tableCell: cssTableCell, rowNumberCell: cssRowNumberCell} ,
                    showRowNumber: false, 
                    firstRowNumber: 1, //The row number for the first row in the dataTable. Used only if showRowNumber is true. 
                    height: '',
                    page: 'disable'|'enable'|'event',
                    pageSize: 10, //	The number of rows in each page, when paging is enabled with the page option.
                    rtlTable: false,
                    scrollLeftStartPosition: 0,
                    sort: 'enable'|'event'|'disable'
                    sortAscending: true,
                    sortColumn: -1,
                    startPage: 0,
                    width:                         
                }
            }
        }*/
    }
    
    public function renderJS() {        
        $js  = "";
        $js .= "google.load('visualization', '1', {packages:['table']});";
        $js .= "google.setOnLoadCallback(f_" . $this->getObjectId() . ");";
        $js .= "function f_" . $this->getObjectId() . "() {";
        $js .= "var data_" . $this->getObjectId() . " = google.visualization.arrayToDataTable(";
	$js .= "[";
           $js .= $this->headers . ",";
           $js .= $this->data;
        $js .= "]);";
        
        $js .= "var table_" . $this->getObjectId() . " = new google.visualization.Table(document.getElementById('" . $this->getObjectId() . "'));";
        
        $js .= "table_" . $this->getObjectId() . ".draw(data_" . $this->getObjectId() . "," . $this->config_options . ");";
        $js .= "}";
        return $js;
    }
    
    public function setHeaders($headers) {
        $this->headers = "[" . implode(",", $headers) . "]";
    }
    
    public function setData($json) {
        $data = Array();
        foreach($json as $row) {
            if (is_array($row))
                $data[] = "[" . implode(",", $row) . "]";
            else
                $data[] = "[" . implode(",",(array) $row) . "]";
        }
        $this->data = implode(",", $data);
    }
    
    public function setPosition($pos) {
        $this->position = $pos;
    }
    
    public function getPosition() {
        return $this->position;
    }    
    
    public function getDraggableId() {
        return "draggable_". $this->getObjectId();
    }
    
    public function show() {
        $js  = "<div id='" . $this->getDraggableId() . "'>";
        $js .= "<h1>&nbsp;</h1>";
        $js .= "<div id='" . $this->getObjectId() . "'>";
        /*
        if (isset($this->width) || isset($this->height)) {
            $js .= " style='";
            if(isset($this->width))
                $js .= "width:" . $this->width . ";";
            if(isset($this->height))
                $js .= "height:" . $this->height . ";";
            $js .= "'";
        }
        $js .= ">";*/
        $js .= "</div></div>";
        return $js;
    }
}

?>
