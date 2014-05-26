<?

////////////////////////////////////////////////////////////////////////////////
// CAMEMIS CHART
// @Kaom Vibolrith Senior Software Developer
// Date: 24.05.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

Class CamemisChart {

    public $datafield = array();

    public function __get($name) {
        if (array_key_exists($name, $this->datafield)) {
            return $this->datafield[$name];
        }
        return null;
    }

    public function __set($name, $value) {
        $this->datafield[$name] = $value;
    }

    function __construct($type, $name, $dataSet) {

        $this->type = strtoupper($type);
        $this->name = strtoupper($name);
        $this->dataSet = $dataSet;
    }

    public function setChartDIV($value) {
        return $this->chartDiv = $value;
    }

    public function setChartSVG($value) {
        return $this->chartSVG = $value;
    }

    public function setWidth($value) {
        return $this->width = $value;
    }

    public function setHeight($value) {
        return $this->height = $value;
    }

    public function setShowLegend($value) {
        return $this->showLegend = $value;
    }

    public function setDisplayType($value) {
        return $this->displayType = $value;
    }

    public function setLabelType($value) {
        return $this->labelType = $value;
    }

    public function setStacked($value) {
        return $this->stacked = $value;
    }

    public function setChartScript() {

        switch ($this->type) {
            case "STACKEAREACHART":
                $chart = new stackeAreaChart($this->name, $this->dataSet, $this->width, $this->height, $this->chartDiv);
                break;
            case "MULTIBARCHART":
                $chart = new multiBarChart($this->name, $this->dataSet, $this->chartSVG, $this->showLegend, $this->stacked);
                break;
            case "PICHCHART":
                $chart = new picChart($this->name, $this->dataSet, $this->chartSVG, $this->displayType, $this->labelType);
                break;
        }

        print $chart->rendererChart();
    }

    public function setChartDisplay() {

        $js = "";

        switch ($this->type) {
            case "STACKEAREACHART":
                $js .= "<div style=\"height:" . $this->height . "px;\">";
                $js .= "<svg id=\"" . $this->chartDiv . "\"></svg>";
                $js .= "</div>";
                break;
            case "MULTIBARCHART":
                $js .="<div id=\"" . $this->chartSVG . "\" style=\"height:" . $this->height . "px; margin: 10px;\"><svg></svg></div>";
                break;
            case "PICHCHART":

                break;
        }

        print $js;
    }

}

Class stackeAreaChart {

    function __construct($name, $dataSet, $width, $height, $chartDiv) {

        $this->name = $name;
        $this->dataSet = $dataSet;
        $this->width = $width;
        $this->height = $height;
        $this->chartDiv = $chartDiv;
    }

    public function rendererChart() {

        $js = "";
        $js .= "var colors = d3.scale.category20();";
        $js .= "keyColor = function(d, i) {return colors(d.key);};";
        $js .= "var chart_" . $this->name . ";";
        $js .= "nv.addGraph(function() {";
        $js .= "chart_" . $this->name . " = nv.models.stackedAreaChart()";

        if ($this->height) {
            $js .= ".height(" . $this->height . ")";
        }

        if ($this->width) {
            $js .= ".width(" . $this->width . ")";
        }

        $js .= ".showLegend(false)";
        $js .= ".useInteractiveGuideline(true)";
        $js .= ".x(function(d) { return d[0]; })";
        $js .= ".y(function(d) { return d[1];})";
        $js .= ".color(keyColor)";
        $js .= ".transitionDuration(300);";
        //$js .= ".clipEdge(true);";
        //$js .= "chart_".$this->name.".stacked.scatter.clipVoronoi(false);";
        $js .= "chart_" . $this->name . ".xAxis";
        $js .= ".tickFormat(function(d) { return d3.time.format(\"%d.%m.%Y\")(new Date(d));});";
        $js .= "chart_" . $this->name . ".yAxis";
        //$js .= ".tickFormat(d3.format(',.2f'));";
        $js .= ".tickFormat(function(d){return d;});";
        $js .= "d3.select('#" . $this->chartDiv . "')";
        $js .= ".datum(" . $this->dataSet . ")";
        $js .= ".transition().duration(0)";
        $js .= ".call(chart_" . $this->name . ");";
        $js .= "nv.utils.windowResize(chart_" . $this->name . ".update);";
        $js .= "chart_" . $this->name . ".dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });";
        $js .= "return chart_" . $this->name . ";";
        $js .= "});";

        return $js;
    }

}

Class multiBarChart {

    function __construct($name, $dataSet, $chartSVG, $showLegend, $stacked) {

        $this->name = $name;
        $this->dataSet = $dataSet;
        $this->chartSVG = $chartSVG;
        $this->showLegend = $showLegend;
        $this->stacked = $stacked ? "true" : "false";
    }

    public function rendererChart() {

        $js = "var $this->name;
            nv.addGraph(function() {
            $this->name = nv.models.multiBarChart()
            .showLegend(" . $this->showLegend . ")//true,false
            .stacked(" . $this->stacked . ")//true,false
            .showXAxis(true)
            .barColor(d3.scale.category20().range())
            .margin({bottom: 50})
            .transitionDuration(300)
            .delay(0)
            .rotateLabels(0)
            .groupSpacing(0.1);
            $this->name.multibar
            .hideable(true);
            $this->name.reduceXTicks(false).staggerLabels(true);
            $this->name.xAxis
            .showMaxMin(false)
            .tickFormat(function(d){ return d;});
            $this->name.yAxis
            .tickFormat(function(d){ return d;});
            d3.select('#" . $this->chartSVG . " svg')
            .datum(" . $this->dataSet . ")
            .call($this->name);
            nv.utils.windowResize($this->name.update);
            $this->name.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
            return $this->name;
        });";

        return $js;
    }

}

Class picChart {

    function __construct($dataSet, $chartSVG, $displayType, $labelType) {

        $this->dataSet = $dataSet;
        $this->chartSVG = $chartSVG;
        $this->labelType = $labelType;
        $this->showLabels = $showLabels;
    }

    public function rendererChart() {

        $js = "";
        $js .= "chart_$this->name.addGraph(function() {";
        $js .= "var chart_$this->name = chart.models.pieChart()";
        $js .= ".x(function(d) { return d.label })";
        $js .= ".y(function(d) { return d.value })";
        $js .= ".showLabels(" . $this->showLabels . ")";
        $js .= ".labelThreshold(.05)";
        $js .= ".labelType(" . $this->labelType . ")";
        $js .= ".donut(true)";
        $js .= ".donutRatio(0.35);";
        $js .= "d3.select(\"#" . $this->chartSVG . " svg\")";
        $js .= ".datum(" . $this->dataSet . ")";
        $js .= ".transition().duration(350)";
        $js .= ".call(chart_$this->name);";
        $js .= "return chart_$this->name;";
        $js .= "});";

        return $js;
    }

}

?>