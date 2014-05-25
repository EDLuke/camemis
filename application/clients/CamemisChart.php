<?

////////////////////////////////////////////////////////////////////////////////
// CAMEMIS CHART
// @Kaom Vibolrith Senior Software Developer
// Date: 24.05.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

Class CamemisChart {

    protected $chartDIV;
    
    protected $chartSVG;
    
    protected $width;
    
    function __construct($type, $name, $dataSet) {
        
        $this->type = strtoupper($type);
        $this->name = strtoupper($name);
        $this->dataSet = $dataSet;
    }

    public function setChartDIV($value){
        return $this->chartDIV = $value;
    }
    
    public function setChartSVG(){
        return $this->chartSVG = $value;
    }
    
    public function setWidth($value){
        return $this->width = $value;
    }
    
    public function setShowLegend($value){
        return $this->showLegend = $value;
    }
    
    public function drawChart() {

        switch ($this->type) {
            case "STACKEAREACHART":
                $chart = new stackeAreaChart($this->name, $this->dataSet, $this->width, $this->height, $this->chartDIV);
                break;
            case "MULTIBARCHART":
                $chart = new multiBarChart($this->name, $this->dataSet, $this->chartSVG, $this->showLegend);
                break;
            case "PICHCHART":
                $chart = new picChart($this->name, $this->dataSet);
                break;
            case "STACKEAREACHART":
                $chart = new stackeAreaChart($this->name, $this->dataSet, $this->width, $this->height);
                break;
        }
        
        print $chart->rendererChart();
    }
}

Class stackeAreaChart {

    function __construct($dataSet, $width=600, $height=500, $chartDIV) {
        
        $this->dataSet = $dataSet;
        $this->width = $width;
        $this->height = $height;
    }

    public function rendererChart() {

        $js = "";
        $js .= "var colors = d3.scale.category20();";
        $js .= "keyColor = function(d, i) {return colors(d.key);};";
        $js .= "var myDataset = " . $this->dataSet . ";";
        $js .= "var chart_".$this->name.";";
        $js .= "nv.addGraph(function() {";
            $js .= "chart_".$this->name." = nv.models.stackedAreaChart()";
            //$js .= ".width(".$this->width.").height(".$this->height.")";
            $js .= ".showLegend(false)";
            $js .= ".useInteractiveGuideline(true)";
            $js .= ".x(function(d) { return d[0]; })";
            $js .= ".y(function(d) { return d[1];})";
            $js .= ".color(keyColor)";
            $js .= ".transitionDuration(300);";
            //$js .= ".clipEdge(true);";
            //$js .= "chart_".$this->name.".stacked.scatter.clipVoronoi(false);";
            $js .= "chart_".$this->name.".xAxis";
            $js .= ".tickFormat(function(d) { return d3.time.format(\"%d.%m.%Y\")(new Date(d));});";
            $js .= "chart_".$this->name.".yAxis";
            //$js .= ".tickFormat(d3.format(',.2f'));";
            $js .= ".tickFormat(function(d){return d;});";
            $js .= "d3.select('#".$this->chartDIV."')";
            $js .= ".datum(myDataset)";
            $js .= ".transition().duration(0)";
            $js .= ".call(chart_".$this->name.");";
            $js .= "nv.utils.windowResize(chart.update);";
            $js .= "chart_".$this->name.".dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });";
            $js .= "return chart_".$this->name.";";
        $js .= "});";
        
        return $js;
    }
}

Class multiBarChart{
    
    function __construct($dataSet, $chartSVG, $showLegend) {
        
        $this->dataSet = $dataSet;
        $this->width = $width;
        $this->height = $height;
    }
    
    public function rendererChart() {
        
        $js = "";
        $js .= "var chart_".$this->name.";";
        $js .= "nv.addGraph(function() {";
            $js .= "chart_".$this->name." = nv.models.multiBarChart()";
            $js .= ".showLegend(".$this->showLegen.")";
            $js .= ".showXAxis(true)";
            $js .= ".barColor(d3.scale.category20().range())";
            $js .= ".margin({bottom: 50})";
            $js .= ".transitionDuration(300)";
            $js .= ".delay(0)";
            $js .= ".rotateLabels(0)";
            $js .= ".chart_".$this->name."(0.1);";
            $js .= "chart_".$this->name.".multibar";
            $js .= ".hideable(true);";
            $js .= "chart_".$this->name.".reduceXTicks(false).staggerLabels(true);";
            $js .= "chart_".$this->name.".xAxis";
            $js .= ".showMaxMin(false)";
            $js .= ".tickFormat(function(d){ return d;});";
            $js .= "chart_".$this->name.".yAxis";
            $js .= ".tickFormat(function(d){ return d;});";
            $js .= "d3.select('#".$this->chartSVG." svg')";
            $js .= ".datum(".$this->dataSet.")";
            $js .= ".call(chart_".$this->name.");";
            $js .= "nv.utils.windowResize(chart_".$this->name.".update);";
            $js .= "chart_".$this->name.".dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });";
            $js .= "return chart_".$this->name.";";
        $js .= "});";
        
        return $js;
    }
}

Class picChart{
    
    function __construct($dataSet, $chartSVG, $displayType, $labelType) {
        
        //$displayType (percent, key, value, percent)
        $this->dataSet = $dataSet;
        $this->chartSVG = $chartSVG;
        $this->labelType = $labelType;
        $this->showLabels = $showLabels;
    }
    
    public function rendererChart() {
        
        $js = "";
        $js .= "chart.addGraph(function() {";
            $js .= "var chart = chart.models.pieChart()";
            $js .= ".x(function(d) { return d.label })";
            $js .= ".y(function(d) { return d.value })";
            $js .= ".showLabels(".$this->showLabels.")     //Display pie labels";
            $js .= ".labelThreshold(.05)";
            $js .= ".labelType(".$this->labelType.")";
            $js .= ".donut(true)";
            $js .= ".donutRatio(0.35)";
            $js .= ";";
            $js .= "d3.select(\"#".$this->chartSVG." svg\")";
            $js .= ".datum(exampleData())";
            $js .= ".transition().duration(350)";
            $js .= ".call(chart);";
            $js .= "return chart;";
        $js .= "});";
        
        return $js;
    }
}
?>