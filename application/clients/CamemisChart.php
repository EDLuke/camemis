<?

////////////////////////////////////////////////////////////////////////////////
// CAMEMIS CHART
// @Kaom Vibolrith Senior Software Developer
// Date: 24.05.2014
// Am Stollheen 18, 55120 Mainz, Germany
////////////////////////////////////////////////////////////////////////////////

Class CamemisChart {

    const MULTIBARHIRIZONTALCHART = "MULTIBARHIRIZONTALCHART";
    const DISCRETEBARCHART = "DISCRETEBARCHART";
    const PICHCHART = "PICHCHART";

    public $staggerLabels = "false";
    public $showLegend = "false";
    public $displayType = "false";
    public $labelType = "false";
    public $stacked = "false";
    public $showValues = "false";
    public $tooltips = "false";
    public $height = null;
    public $width = null;
    public $chartDiv = "default";
    public $chartSVG = "default";
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

    public function setChartScript() {

        switch ($this->type) {
            case "STACKEAREACHART":
                $chart = new stackeAreaChart(
                        $this->name
                        , $this->dataSet
                        , $this->width
                        , $this->height
                        , $this->chartDiv);
                break;
            case "MULTIBARCHART":
                $chart = new multiBarChart(
                        $this->name
                        , $this->dataSet
                        , $this->chartSVG
                        , $this->showLegend
                        , $this->stacked);
                break;
            case self::PICHCHART:
                $chart = new picChart(
                        $this->name
                        , $this->dataSet
                        , $this->chartSVG
                        , $this->showLegend
                        , $this->width
                        , $this->height);
                break;
            case self::DISCRETEBARCHART:
                $chart = new discreteBarChart(
                        $this->name
                        , $this->dataSet
                        , $this->chartSVG
                        , $this->showValues
                        , $this->staggerLabels
                        , $this->tooltips);
                break;
            case self::MULTIBARHIRIZONTALCHART:
                $chart = new discreteBarChart(
                        $this->name
                        , $this->dataSet
                        , $this->chartSVG
                        , $this->showValues
                        , $this->staggerLabels
                        , $this->tooltips);
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
            case self::PICHCHART:
                $js .="<div style='float:left;margin:5px;'>";
                $js .="<svg id=\"" . $this->chartSVG . "\" style='width:" . $this->width . "px;border: solid 1px #B3B2B2;'></svg>";
                $js .="</div>";
                break;
            case self::DISCRETEBARCHART:
                $js .= "<div style=\"height:" . $this->height . "px;\">";
                $js .= "<svg id=\"" . $this->chartDiv . "\"></svg>";
                $js .= "</div>";
                break;
            case self::MULTIBARHIRIZONTALCHART:
                $js .="<div id=\"" . $this->chartSVG . "\" style=\"height:" . $this->height . "px; margin: 10px;\"><svg></svg></div>";
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
        $js .=" .showControls(false)";
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
        $this->stacked = $stacked;
    }

    public function rendererChart() {

        $js = "var $this->name;
            nv.addGraph(function() {
            $this->name = nv.models.multiBarChart()
            .showControls(false)
            .showLegend(" . $this->showLegend . ")//true,false
            .stacked(" . $this->stacked . ")//true,false
            .showXAxis(true)//true,false
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

    function __construct($name, $dataSet, $chartSVG, $showLegend, $width, $height) {

        $this->name = $name;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->chartSVG = $chartSVG;
        $this->width = $width;
        $this->height = $height;
        $this->showLegend = $showLegend;
    }

    public static function dafaultDataSet() {
        return "[{
            key: 'One',y: 5
        },{
            key: 'Two',y: 2
        },{
            key: 'Three', y: 9
        },{
            key: 'Four',y: 7
        },{
            key: 'Five',y: 4
        },{
            key: 'Six',y: 3
        },{
            key: 'Seven',y: .5
        }]";
    }

    public function rendererChart() {

        $js = "nv.addGraph(function() {
            var width = $this->width,
            height = $this->height;
            var $this->name = nv.models.pieChart()
            .x(function(d) { return d.key })
            .y(function(d) { return d.y })
            .showLegend(" . $this->showLegend . ")
            .color(d3.scale.category10().range())
            .width(width)
            .height(height)
            .donut(true);
             $this->name.pie.donutLabelsOutside(true).donut(true);
            d3.select(\"#" . $this->chartSVG . "\")
            .datum(" . $this->dataSet . ")
            .transition().duration(1200)
            .attr('width', width)
            .attr('height', height)
            .call($this->name);
            $this->name.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
            return $this->name;
        });";

        return $js;
    }

}

Class discreteBarChart {

    function __construct($name, $dataSet, $chartSVG, $showValues, $staggerLabels, $tooltips) {

        $this->name = $name;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->chartSVG = $chartSVG;
        $this->showValues = $showValues;
        $this->staggerLabels = $staggerLabels;
        $this->tooltips = $tooltips;
    }

    public static function dafaultDataSet() {
        return "[{
          key: 'Cumulative Return',
          values: [{ 
              'label' : 'A' ,
              'value' : 29.765957771107
            } , { 
              'label' : 'B' , 
              'value' : 0
            } , { 
              'label' : 'C' , 
              'value' : 32.807804682612
            } , { 
              'label' : 'D' , 
              'value' : 196.45946739256
            } , { 
              'label' : 'E' ,
              'value' : 0.19434030906893
            } , { 
              'label' : 'F' , 
              'value' : 98.079782601442
            } , { 
              'label' : 'G' , 
              'value' : 13.925743130903
            } , { 
              'label' : 'H' , 
              'value' : 5.1387322875705
            }]
        }]";
    }

    public function rendererChart() {

        $js = "nv.addGraph(function() {  
            var $this->name = nv.models.discreteBarChart()
            .x(function(d) { return d.label })
            .y(function(d) { return d.value })
            .staggerLabels(" . $this->staggerLabels . ")        // true, false
            //.staggerLabels(historicalBarChart[0].values.length > 8)
            .tooltips(" . $this->tooltips . ")                  // true, false
            .showValues(" . $this->showValues . ")              // true, false
            .transitionDuration(250);
            d3.select('#" . $this->chartSVG . " svg')
            .datum(" . $this->dataSet . ")
            .call($this->name);
            nv.utils.windowResize($this->name.update);
            return $this->name;
        });";

        return $js;
    }

}

class multiBarHorizontalChart {

    function __construct($name, $dataSet, $chartSVG, $showValues, $showControls, $tooltips, $stacked) {

        $this->name = $name;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->chartSVG = $chartSVG;
        $this->showValues = $showValues;
        $this->tooltips = $tooltips;
        $this->showControls = $showControls;
        $this->stacked = $stacked;
    }

    public static function dafaultDataSet() {
        return "[{
            key: 'Series1',
            color: '#d62728',
            values: [{ 
                'label' : 'Group A' ,
                'value' : 1.8746444827653
              },{ 
                'label' : 'Group B' ,
                'value' : 8.0961543492239
              },{ 
                'label' : 'Group C' ,
                'value' : 0.57072943117674
              },{ 
                'label' : 'Group D' ,
                'value' : 2.4174010336624
              },{ 
                'label' : 'Group E' ,
                'value' : 0.72009071426284
              },{ 
                'label' : 'Group F' ,
                'value' : 0.77154485523777
              },{ 
                'label' : 'Group G' ,
                'value' : 0.90152097798131
              },{ 
                'label' : 'Group H' ,
                'value' : 0.91445417330854
              },{ 
                'label' : 'Group I' ,
                'value' : 0.055746319141851
              }]
        },{ 
            key: 'Series2',
            color: '#1f77b4',
            values: [{ 
                'label' : 'Group A' ,
                'value' : 25.307646510375
              },{  
                'label' : 'Group B' ,
                'value' : 16.756779544553
              },{ 
                'label' : 'Group C' ,
                'value' : 18.451534877007
              },{ 
                'label' : 'Group D' ,
                'value' : 8.6142352811805
              },{ 
                'label' : 'Group E' ,
                'value' : 7.8082472075876
              },{ 
                'label' : 'Group F' ,
                'value' : 5.259101026956
              },{ 
                'label' : 'Group G' ,
                'value' : 0.30947953487127
              },{ 
                'label' : 'Group H' ,
                'value' : 0
              },{ 
                'label' : 'Group I' ,
                'value' : 0 
              }]
          }]";
    }

    public function rendererChart() {

        $js = "var $this->name;
        nv.addGraph(function() {
            $this->name = nv.models.multiBarHorizontalChart()
            .x(function(d) { return d.label })
            .y(function(d) { return d.value })
            .margin({top: 30, right: 20, bottom: 50, left: 175})
            .showValues(" . $this->showValues . ")
            .tooltips(" . $this->tooltips . ")
            .barColor(d3.scale.category20().range())
            .transitionDuration(250)
            .stacked(" . $this->stacked . ")
            .showControls(" . $this->showControls . ");
            $this->name.yAxis
            .tickFormat(function(d){ return d;});
            d3.select('#" . $this->chartSVG . " svg')
            .datum(" . $this->dataSet . ")
            .call($this->name);
            nv.utils.windowResize(chart.update);
            $this->name.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });
            return $this->name;
        });
        ";

        return $js;
    }

}

?>