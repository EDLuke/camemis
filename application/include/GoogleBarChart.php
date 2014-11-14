<?php

///////////////////////////////////////////////////////////
// @Chuy Thong Senior Software Developer
// Date: 29.06.2012
// 79Bz, Phnom Penh, Cambodia
///////////////////////////////////////////////////////////
include_once 'IGoogleChart.php';

class GoogleBarChart implements IChart {

    protected $objectName = null;
    protected $object = null;
    protected $modul = 'modul';
    private $headers;
    private $data;
    private $title;
    private $title_x;
    private $width;
    private $height;
    private $position;
    private $config_options;

    public function __construct($object, $modul = 'modul') {
        $this->object = $object;
        $this->modul = $modul;
    }

    public function getObjectId() {
        if ($this->modul) {
            return "googlebar_" . strtolower($this->object) . "_" . strtolower($this->modul);
        } else {
            return "googlebar_" . strtolower($this->object);
        }
    }

    public function set($prop, $value) {
        switch ($prop) {
            case "TITLE":
                $this->title = $value;
                break;
            case "TITLE_X":
                $this->title_x = $value;
                break;
            case "WIDTH":
                $this->width = $value;
                break;
            case "HEIGHT":
                $this->height = $value;
                break;
        }
    }
    
    public function get($prop) {
        switch ($prop) {
            case "TITLE":
                return $this->title;
            case "TITLE_X":
                return $this->title_x;
            case "WIDTH":
                return $this->width;
            case "HEIGHT":
                return $this->height;
            default:
                return "";
        }
    }

    public function setConfigOption($options) {
        $html = "";
        if ($options) {
            $html .= "{" . implode(",", $options) . "}";
        }
        $this->config_options = $html;
    }
    
    public function setOption() {
        $js = "{";
        $js .= "'title':" . ($this->title ? "'" . $this->title . "'" : "''");
        $js .= ",hAxis: {title: '" . ($this->title_x ? "'" . $this->title_x . "'" : "''") . "', titleTextStyle: {color: 'red'}}";
        $js .= ",'width':" . ($this->width ? $this->width : 350);
        $js .= ",'height':" . ($this->height ? $this->height : 200);
        $js .= "}";
        return $js;
    }
    
    public function setPosition($pos) {
        $this->position = $pos;
    }
    
    public function getPosition() {
        return $this->position;
    }    

    public function renderJS() {
        $js = "";
        $js .= "google.load('visualization', '1', {'packages':['corechart']});";
        $js .= "google.setOnLoadCallback(f_" . $this->getObjectId() . ");";
        $js .= "function f_" . $this->getObjectId() . "() {";
        $js .= "var data_" . $this->getObjectId() . " = google.visualization.arrayToDataTable(";
        $js .= "[";
        $js .= $this->headers . ",";
        $js .= $this->data;
        $js .= "]);";

        $js .= "var bar_" . $this->getObjectId() . " = new google.visualization.BarChart(document.getElementById('" . $this->getObjectId() . "'));";
        $js .= "bar_" . $this->getObjectId() . ".draw(data_" . $this->getObjectId() . ", " . $this->config_options . " );";
        $js .= "}";
        return $js;
    }

    public function setHeaders($headers) {
        $this->headers = "[" . implode(",", $headers) . "]";
    }

    public function setData($json) {
        $data = Array();
        foreach ($json as $row) {
            $data[] = "[" . implode(",", (array) $row) . "]";
        }
        $this->data = implode(",", $data);
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
