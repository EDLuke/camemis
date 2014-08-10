<?

////////////////////////////////////////////////////////////////////////////////
// CAMEMIS HIGH CHART
// @THORN Visal
// Date: 07.08.2014
////////////////////////////////////////////////////////////////////////////////

Class CamemisHighChart {

    const PIC_CHART = "PIC_CHART";
    const COLUMN_COMPARE_CHART = "COLUMN_COMPARE_CHART";
    const COLUMN_CHART = "COLUMN_CHART";
    const AREA_SPLINE_CHART = "AREA_SPLINE_CHART";
    const AREA_STACKED_CHART = "AREA_STACKED_CHART";

    public $maxwidth = 750;
    public $allowPointSelect = "false";
    public $plotShadow = "false";
    public $dataLabels = "true";
    public $height = null;

    public $plotBackgroundColor = "null";
    public $plotBorderWidth = "null";
    public $pointColor = "#FFFFFF";
    public $cursor = "pointer";
    public $pointPadding = 0.2;
    public $borderWidth = 0;
    public $headerFontsize = null;
    public $tooltipsShared = "true";
    public $tooltipsUseHTML = "true";
    public $yAxisMin = 0;
    public $legend = "false";
    public $dataLabelsColor = "#FFFFFF";
    public $dataLabelsAlign = "right";
    public $dataLabelsX = 4;
    public $dataLabelsY = 10;
    public $dataLabelsStyleFontsize = 13;
    public $dataLabelsStyleFontfamily = "Verdana, sans-serif";
    public $dataLabelsStyleShadow = "0 0 3px black";
    public $dataLabelsRotation = -90;
    public $xAxisLabelsRotation = -45;
    public $xAxisLabelsStyleFontsize = 13;
    public $xAxisLablesStyleFontfamily = "Verdana, sans-serif";
    public $legendLayout = "vertical";
    public $legendAlign = "left";
    public $legendVerticalAlign = "top";
    public $legendX = 150;
    public $legendY = 100;
    public $legendFloating = "true";
    public $legendBorderWidth = 1;
    public $plotBandsFrom = 4.5;
    public $plotBandsTo = 6.5;
    public $plotBandsColor = "rgba(68, 170, 213, .2)";
    public $tooltipsValueSuffix = "false";
    public $credits = "false";
    public $plotOptionsAreasplineFillOpacity = 0.5;
    public $tickmarkPlacement = "on";
    public $plotOptionsAreaStacking = "normal";
    public $plotOptionsAreaLineColor = "#666666";
    public $plotOptionsAreaLineWidth = 1;
    public $plotOptionsAreaMarkerLineColor = "#666666";
    public $plotOptionsAreaMarkerLineWidth = 1;

    function __construct($type, $id, $title, $dataSet)
    {

        $this->type = strtoupper($type);
        $this->title = $title;
        $this->dataSet = $dataSet;
        $this->id = $id;
    }

    public function setChartScript()
    {

        switch ($this->type)
        {
            case self::PIC_CHART:
                $chart = new picChart(
                        $this->id
                        , $this->text
                        , $this->dataSet
                        , $this->allowPointSelect
                        , $this->title
                        , $this->plotShadow
                        , $this->plotBackgroundColor
                        , $this->plotBorderWidth
                        , $this->pointColor
                        , $this->cursor
                        , $this->dataLabels);
                break;
            case self::COLUMN_COMPARE_CHART:
                $chart = new columnCompareChart(
                        $this->id
                        , $this->title
                        , $this->dataSet
                        , $this->xAxis
                        , $this->text
                        , $this->pointPadding
                        , $this->borderWidth
                        , $this->headerFontsize
                        , $this->tooltipsShared
                        , $this->tooltipsUseHTML
                        , $this->yAxisMin
                        , $this->plotBorderWidth);
                break;
            case self::COLUMN_CHART:
                $chart = new columnChart(
                        $this->id
                        , $this->text
                        , $this->dataSet
                        , $this->title
                        , $this->legend
                        , $this->seriesName
                        , $this->dataLabelsColor
                        , $this->dataLabelsAlign
                        , $this->dataLabelsX
                        , $this->dataLabelsY
                        , $this->dataLabelsStyleFontsize
                        , $this->dataLabelsStyleFontfamily
                        , $this->dataLabelsStyleShadow
                        , $this->dataLabels
                        , $this->dataLabelsRotation
                        , $this->xAxisLabelsRotation
                        , $this->xAxisLabelsStyleFontsize
                        , $this->xAxisLablesStyleFontfamily
                        , $this->plotBorderWidth
                        , $this->yAxisMin);
                break;
            case self::AREA_SPLINE_CHART:
                $chart = new areaSplineChart(
                        $this->id
                        , $this->title
                        , $this->dataSet
                        , $this->xAxis
                        , $this->text
                        , $this->plotBorderWidth
                        , $this->tooltipsShared
                        , $this->pointColor
                        , $this->legendLayout
                        , $this->legendAlign
                        , $this->legendVerticalAlign
                        , $this->legendX
                        , $this->legendY
                        , $this->legendFloating
                        , $this->legendBorderWidth
                        , $this->plotBandsFrom
                        , $this->plotBandsTo
                        , $this->plotBandsColor
                        , $this->tooltipsValueSuffix
                        , $this->credits
                        , $this->plotOptionsAreasplineFillOpacity);
                break;
            case self::AREA_STACKED_CHART:
                $chart = new areaStackedChart(
                        $this->id
                        , $this->title
                        , $this->dataSet
                        , $this->xAxis
                        , $this->text
                        , $this->plotBorderWidth
                        , $this->tooltipsShared
                        , $this->pointColor
                        , $this->tooltipsValueSuffix
                        , $this->tickmarkPlacement
                        , $this->plotOptionsAreaStacking
                        , $this->plotOptionsAreaLineColor
                        , $this->plotOptionsAreaLineWidth
                        , $this->plotOptionsAreaMarkerLineColor
                        , $this->plotOptionsAreaMarkerLineWidth);
                break;
        }

        print $chart->rendererChart();
    }

    public function setChartDisplay()
    {

        $js = "";

        switch ($this->type)
        {

            case self::PIC_CHART:
            case self::COLUMN_COMPARE_CHART:
            case self::COLUMN_CHART:
            case self::AREA_SPLINE_CHART:
            case self::AREA_STACKED_CHART:
                $js = "<div id='" . $this->id . "' style='height: " . $this->height . "px; max-width:85%; margin: 0 auto'></div>";
                break;
        }

        print $js;
    }

}

Class picChart {

    function __construct($id, $text, $dataSet, $allowPointSelect, $title, $plotShadow, $plotBackgroundColor, $plotBorderWidth, $pointColor, $cursor, $dataLabels)
    {

        $this->text = $text;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->allowPointSelect = $allowPointSelect;
        $this->title = $title;
        $this->id = $id;
        $this->plotShadow = $plotShadow;
        $this->plotBackgroundColor = $plotBackgroundColor;
        $this->plotBorderWidth = $plotBorderWidth;
        $this->pointColor = $pointColor;
        $this->cursor = $cursor;
        $this->dataLabels = $dataLabels;
    }

    public static function dafaultDataSet()
    {
        return "[
                ['One', 1]
                ,['Two',2]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                ,['Six',6]
        ]";
    }

    public function rendererChart()
    {

        $js = "$('#" . $this->id . "').highcharts({";
        $js .="chart: {";
        $js .="plotBackgroundColor: " . $this->plotBackgroundColor . ",";
        $js .="plotBorderWidth: " . $this->plotBorderWidth . ",";
        $js .="plotShadow: " . $this->plotShadow;
        $js .="},";
        $js .="title: {";
        $js .="text: '" . $this->title . "'";
        $js .="},";
        $js .="tooltip: {";
        $js .="pointFormat: '{series.name}: <b>{point.y}</b>'";
        $js .="},";
        $js .="plotOptions: {";
        $js .="pie: {";
        $js .="allowPointSelect: " . $this->allowPointSelect . ",";
        $js .="cursor: '" . $this->cursor . "',";
        $js .="dataLabels: {";
        $js .="enabled: " . $this->dataLabels . ",";
        $js .="format: '{point.name}: {point.y}',";
        $js .="style: {";
        $js .="color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || '" . $this->pointColor . "'";
        $js .="}";
        $js .="}";
        $js .="}";
        $js .="},";
        $js .="series: [{";
        $js .="type: 'pie',";
        $js .="name: '" . $this->text . "',";
        $js .="data:" . $this->dataSet;
        $js .="}]";
        $js .="});";

        return $js;
    }

}

Class columnCompareChart {

    function __construct($id, $title, $dataSet, $xAxis, $text, $pointPadding, $borderWidth, $headerFontsize, $tooltipsShared, $tooltipsUseHTML, $yAxisMin, $plotBorderWidth)
    {

        $this->title = $title;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->xAxis = $xAxis ? $xAxis : self::dafaultxAxis();
        $this->id = $id;
        $this->text = $text;
        $this->pointPadding = $pointPadding;
        $this->borderWidth = $borderWidth;
        $this->headerFontsize = $headerFontsize;
        $this->tooltipsShared = $tooltipsShared;
        $this->tooltipsUseHTML = $tooltipsUseHTML;
        $this->yAxisMin = $yAxisMin;
        $this->plotBorderWidth = $plotBorderWidth;
    }

    public static function dafaultDataSet()
    {
        return "[{
            name: 'Mail',
            data: [10,5,10]

        }, {
            name: 'Femail',
            data: [2,5,15]

        }]";
    }

    public static function dafaultxAxis()
    {
        return "{
            categories: [
                'Good',
                'Bad',
                'Very Good',
            ]
        }";
    }

    public function rendererChart()
    {


        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'column'";
        $js.=",plotBorderWidth:" . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="}";
        $js.=",xAxis:" . $this->xAxis;
        $js.=",yAxis: {";
        $js.="min: " . $this->yAxisMin . ",";
        $js.="title: {";
        $js.="text: '" . $this->text . "'";
        $js.="}";
        $js.="},";
        $js.="tooltip: {";
        $js.="headerFormat: '<span style=\"font-size:" . $this->headerFontsize . "px\">{point.key}</span><table>',";
        $js.="pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +";
        $js.="'<td style=\"padding:0\"><b>{point.y}</b></td></tr>',";
        $js.="footerFormat: '</table>',";
        $js.="shared: " . $this->tooltipsShared . ",";
        $js.="useHTML: " . $this->tooltipsUseHTML;
        $js.="},";
        $js.="plotOptions: {";
        $js.="column: {";
        $js.="pointPadding: " . $this->pointPadding . ",";
        $js.="borderWidth: " . $this->borderWidth;
        $js.="}";
        $js.="},";
        $js.="series: " . $this->dataSet;
        $js.="});";

        return $js;
    }

}

Class columnChart {

    function __construct($id, $text, $dataSet, $title, $legend, $seriesName, $dataLabelsColor, $dataLabelsAlign, $dataLabelsX, $dataLabelsY, $dataLabelsStyleFontsize, $dataLabelsStyleFontfamily, $dataLabelsStyleShadow, $dataLabels, $dataLabelsRotation, $xAxisLabelsRotation, $xAxisLabelsStyleFontsize, $xAxisLablesStyleFontfamily, $plotBorderWidth, $yAxisMin)
    {

        $this->id = $id;
        $this->text = $text;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->title = $title;
        $this->legend = $legend;
        $this->seriesName = $seriesName;
        $this->dataLabelsColor = $dataLabelsColor;
        $this->dataLabelsAlign = $dataLabelsAlign;
        $this->dataLabelsX = $dataLabelsX;
        $this->dataLabelsY = $dataLabelsY;
        $this->dataLabelsStyleFontsize = $dataLabelsStyleFontsize;
        $this->dataLabelsStyleFontfamily = $dataLabelsStyleFontfamily;
        $this->dataLabelsStyleShadow = $dataLabelsStyleShadow;
        $this->dataLabels = $dataLabels;
        $this->dataLabelsRotation = $dataLabelsRotation;
        $this->xAxisLabelsRotation = $xAxisLabelsRotation;
        $this->xAxisLabelsStyleFontsize = $xAxisLabelsStyleFontsize;
        $this->xAxisLablesStyleFontfamily = $xAxisLablesStyleFontfamily;
        $this->plotBorderWidth = $plotBorderWidth;
        $this->yAxisMin = $yAxisMin;
    }

    public static function dafaultDataSet()
    {
        return "[
                ['One', 1]
                ,['Two',2]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Six',6]
                ,['Three',3]
                ,['Four',4]
                ,['Five',5]
                
        ]";
    }

    public function rendererChart()
    {

        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'column'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="},";
        $js.="xAxis: {";
        $js.="type: 'category',";
        $js.="labels: {";
        $js.="rotation:" . $this->xAxisLabelsRotation . ",";
        $js.="style: {";
        $js.="fontSize: '" . $this->xAxisLabelsStyleFontsize . "px',";
        $js.="fontFamily: '" . $this->xAxisLablesStyleFontfamily . "'";
        $js.="}";
        $js.="}";
        $js.="},";
        $js.="yAxis: {";
        $js.="min: " . $this->yAxisMin . ",";
        $js.="title: {";
        $js.="text: '" . $this->text . "'";
        $js.="}";
        $js.="},";
        $js.="legend: {";
        $js.="enabled: " . $this->legend . "";
        $js.="},";
        $js.="tooltip: {";
        $js.="pointFormat: '{series.name}: <b>{point.y:.1f}</b>',";
        $js.="},";
        $js.="series: [{";
        $js.="name: 'AVG',";
        $js.="data:" . $this->dataSet . "";
        $js.=",dataLabels: {";
        $js.="enabled: " . $this->dataLabels . ",";
        $js.="rotation:" . $this->dataLabelsRotation . ",";
        $js.="color: '" . $this->dataLabelsColor . "',";
        $js.="align: '" . $this->dataLabelsAlign . "',";
        $js.="x: " . $this->dataLabelsX . ",";
        $js.="y: " . $this->dataLabelsY . ",";
        $js.="style: {";
        $js.="fontSize: '" . $this->dataLabelsStyleFontsize . "px',";
        $js.="fontFamily: '" . $this->dataLabelsStyleFontfamily . "',";
        $js.="textShadow: '" . $this->dataLabelsStyleShadow . "'";
        $js.="}";
        $js.="}";
        $js.="}]";
        $js.="});";

        return $js;
    }

}

Class areaSplineChart {

    function __construct($id, $title, $dataSet, $xAxis, $text, $plotBorderWidth, $tooltipsShared, $pointColor, $legendLayout, $legendAlign, $legendVerticalAlign, $legendX, $legendY, $legendFloating, $legendBorderWidth
    , $plotBandsFrom, $plotBandsTo, $plotBandsColor, $tooltipsValueSuffix, $credits, $plotOptionsAreasplineFillOpacity)
    {

        $this->title = $title;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->xAxis = $xAxis ? $xAxis : self::dafaultxAxis();
        $this->id = $id;
        $this->text = $text;
        $this->plotBorderWidth = $plotBorderWidth;
        $this->tooltipsShared = $tooltipsShared;
        $this->pointColor = $pointColor;
        $this->legendLayout = $legendLayout;
        $this->legendAlign = $legendAlign;
        $this->legendVerticalAlign = $legendVerticalAlign;
        $this->legendX = $legendX;
        $this->legendY = $legendY;
        $this->legendFloating = $legendFloating;
        $this->legendBorderWidth = $legendBorderWidth;
        $this->plotBandsFrom = $plotBandsFrom;
        $this->plotBandsTo = $plotBandsTo;
        $this->plotBandsColor = $plotBandsColor;
        $this->tooltipsValueSuffix = $tooltipsValueSuffix;
        $this->credits = $credits;
        $this->plotOptionsAreasplineFillOpacity = $plotOptionsAreasplineFillOpacity;
    }

    public static function dafaultDataSet()
    {
        return "[{
            name: 'John',
            data: [3, 4, 3, 5, 4, 10, 12]
        }, {
            name: 'Jane',
            data: [1, 3, 4, 3, 3, 5, 4]
        }]";
    }

    public static function dafaultxAxis()
    {
        return "
            categories: ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']
        ";
    }

    public function rendererChart()
    {

        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'areaspline'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="},";
        $js.="legend: {";
        $js.="layout: '" . $this->legendLayout . "'";
        $js.=",align: '" . $this->legendAlign . "',";
        $js.="verticalAlign: '" . $this->legendVerticalAlign . "',";
        $js.="x: " . $this->legendX . ",";
        $js.="y: " . $this->legendY . ",";
        $js.="floating: " . $this->legendFloating . ",";
        $js.="borderWidth: " . $this->legendBorderWidth . ",";
        $js.="backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '" . $this->pointColor . "'";
        $js.="},";
        $js.="xAxis: {" . $this->xAxis . ",";
        $js.="plotBands: [{";
        $js.="from: " . $this->plotBandsFrom . ",";
        $js.="to: " . $this->plotBandsTo . ",";
        $js.="color: '" . $this->plotBandsColor . "'";
        $js.="}]";
        $js.="},";
        $js.="yAxis: {";
        $js.="title: {";
        $js.="text: '" . $this->text . "'";
        $js.="}";
        $js.="},";
        $js.="tooltip: {";
        $js.="shared: " . $this->tooltipsShared . ",";
        $js.="valueSuffix: '" . $this->tooltipsValueSuffix . "'";
        $js.="},";
        $js.="credits: {";
        $js.="enabled: " . $this->credits;
        $js.="},";
        $js.="plotOptions: {";
        $js.="areaspline:{";
        $js.="fillOpacity: " . $this->plotOptionsAreasplineFillOpacity;
        $js.="}";
        $js.="},";
        $js.="series: " . $this->dataSet;
        $js.="});";

        return $js;
    }

}

Class areaStackedChart {

    function __construct($id, $title, $dataSet, $xAxis, $text, $plotBorderWidth, $tooltipsShared, $pointColor, $tooltipsValueSuffix, $tickmarkPlacement
    , $plotOptionsAreaStacking, $plotOptionsAreaLineColor, $plotOptionsAreaLineWidth, $plotOptionsAreaMarkerLineColor, $plotOptionsAreaMarkerLineWidth)
    {

        $this->title = $title;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->xAxis = $xAxis ? $xAxis : self::dafaultxAxis();
        $this->id = $id;
        $this->text = $text;
        $this->plotBorderWidth = $plotBorderWidth;
        $this->tooltipsShared = $tooltipsShared;
        $this->pointColor = $pointColor;
        $this->tooltipsValueSuffix = $tooltipsValueSuffix;
        $this->tickmarkPlacement = $tickmarkPlacement;
        $this->plotOptionsAreaStacking = $plotOptionsAreaStacking;
        $this->plotOptionsAreaLineColor = $plotOptionsAreaLineColor;
        $this->plotOptionsAreaLineWidth = $plotOptionsAreaLineWidth;
        $this->plotOptionsAreaMarkerLineColor = $plotOptionsAreaMarkerLineColor;
        $this->plotOptionsAreaMarkerLineWidth = $plotOptionsAreaMarkerLineWidth;
    }

    public static function dafaultDataSet()
    {
        return "[{
            name: 'Asia',
            data: [502, 635, 809, 947, 1402, 3634, 5268]
        }, {
            name: 'Africa',
            data: [106, 107, 111, 133, 221, 767, 1766]
        }, {
            name: 'Europe',
            data: [163, 203, 276, 408, 547, 729, 628]
        }, {
            name: 'America',
            data: [18, 31, 54, 156, 339, 818, 1201]
        }, {
            name: 'Oceania',
            data: [2, 2, 2, 6, 13, 30, 46]
        }]";
    }

    public static function dafaultxAxis()
    {
        return "
            categories: ['1750', '1800', '1850', '1900', '1950', '1999', '2050']
        ";
    }

    public function rendererChart()
    {

        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'area'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth . "";
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="},";
        $js.="xAxis: {" . $this->xAxis . ",";
        $js.="tickmarkPlacement: '" . $this->tickmarkPlacement . "',";
        $js.="},";
        $js.="yAxis: {";
        $js.="title: {";
        $js.="text: '" . $this->text . "'";
        $js.="},";
        $js.="labels: {";
        $js.="formatter: function() {";
        $js.="return this.value / 1000;";
        $js.="}";
        $js.="}";
        $js.="},";
        $js.="tooltip: {";
        $js.="shared: " . $this->tooltipsShared . ",";
        $js.="valueSuffix: '" . $this->tooltipsValueSuffix . "'";
        $js.="},";
        $js.="plotOptions: {";
        $js.="area: {";
        $js.="stacking: '" . $this->plotOptionsAreaStacking . "',";
        $js.="lineColor: '" . $this->plotOptionsAreaLineColor . "',";
        $js.="lineWidth: " . $this->plotOptionsAreaLineWidth . ",";
        $js.="marker: {";
        $js.="lineWidth: " . $this->plotOptionsAreaMarkerLineWidth . ",";
        $js.="lineColor: '" . $this->plotOptionsAreaMarkerLineColor . "'";
        $js.="}";
        $js.="}";
        $js.="},";
        $js.="series: " . $this->dataSet;
        $js.="});";

        return $js;
    }

}

?>