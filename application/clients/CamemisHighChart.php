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
    const COLUMN_DRILLDOWN_CHART = "COLUMN_DRILLDOWN_CHART";

    public $maxWidthPercentage = true;
    public $text = null;
    public $subtitle = "";
    public $xAxis = null;
    public $seriesName = null;
    public $maxWidth = 85;
    public $minWidth = 310;
    public $allowPointSelect = "false";
    public $plotShadow = "false";
    public $dataLabels = "true";
    public $height = null;
    public $plotBackgroundColor = "null";
    public $plotBorderWidth = 1;
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
    public $preSet = "<pre id='tsv' style='display:none'>Browser Version    Total Market Share
Microsoft Internet Explorer 8.0    26.61%
Microsoft Internet Explorer 9.0    16.96%
Chrome 18.0    8.01%
Chrome 19.0    7.73%
Firefox 12    6.72%
Microsoft Internet Explorer 6.0    6.40%
Firefox 11    4.72%
Microsoft Internet Explorer 7.0    3.55%
Safari 5.1    3.53%
Firefox 13    2.16%
Firefox 3.6    1.87%
Opera 11.x    1.30%
Chrome 17.0    1.13%
Firefox 10    0.90%
Safari 5.0    0.85%
Firefox 9.0    0.65%
Firefox 8.0    0.55%
Firefox 4.0    0.50%
Chrome 16.0    0.45%
Firefox 3.0    0.36%
Firefox 3.5    0.36%
Firefox 6.0    0.32%
Firefox 5.0    0.31%
Firefox 7.0    0.29%
Proprietary or Undetectable    0.29%
Chrome 18.0 - Maxthon Edition    0.26%
Chrome 14.0    0.25%
Chrome 20.0    0.24%
Chrome 15.0    0.18%
Chrome 12.0    0.16%
Opera 12.x    0.15%
Safari 4.0    0.14%
Chrome 13.0    0.13%
Safari 4.1    0.12%
Chrome 11.0    0.10%
Firefox 14    0.10%
Firefox 2.0    0.09%
Chrome 10.0    0.09%
Opera 10.x    0.09%
Microsoft Internet Explorer 8.0 - Tencent Traveler Edition    0.09%</pre>";

    function __construct($type, $id, $title, $dataSet) {

        $this->type = strtoupper($type);
        $this->title = $title;
        $this->dataSet = $dataSet;
        $this->id = $id;
    }

    public static function setHighChartLIB() {
        $js = "";
        $js .= "<script type = \"text/javascript\" src = \"http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js\"></script>";
        $js .= "<script src=\"/public/js/highcharts.js\"></script>";
        $js .= "<script src=\"/public/js/modules/drilldown.js\"></script>";
        $js .= "<script src=\"/public/js/modules/exporting.js\"></script>";
        $js .= "<script src=\"/public/js/modules/data.js\"></script>";
        echo $js;
    }

    public function setHighChartScript() {

        switch ($this->type) {
            case self::PIC_CHART:
                $chart = new picChart(
                        $this->id
                        , $this->text
                        , $this->dataSet
                        , $this->allowPointSelect
                        , $this->title
                        , $this->subtitle
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
                        , $this->subtitle
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
                        , $this->subtitle
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
                        , $this->subtitle
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
                        , $this->subtitle
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
            case self::COLUMN_DRILLDOWN_CHART:
                $chart = new calumnDrillDownChart(
                        $this->id
                        , $this->title
                        , $this->subtitle
                        , $this->text
                        , $this->legend
                        , $this->dataLabels
                        , $this->headerFontsize
                        , $this->plotBorderWidth);
                break;
        }

        $js = "<script type=\"text/javascript\">";
        $js .= $chart->createHighChartJS();
        $js .= "</script>";

        print $js;
    }

    public function setDIVHighChart() {

        $js = "";

        switch ($this->type) {

            case self::PIC_CHART:
            case self::COLUMN_COMPARE_CHART:
            case self::COLUMN_CHART:
            case self::AREA_SPLINE_CHART:
            case self::AREA_STACKED_CHART:
                if ($this->maxWidthPercentage) {
                    $js = "<div id='" . $this->id . "'; style='height: " . $this->height . "px; width:" . $this->maxWidth . "%; float:left; margin:20px;'></div>";
                } else {
                    $js = "<div id='" . $this->id . "'; style='height: " . $this->height . "px; width:" . $this->maxWidth . "px; float:left; margin:20px;'></div>";
                }

                break;
            case self::COLUMN_DRILLDOWN_CHART:
                $js = "<div id='" . $this->id . "' style='height: " . $this->height . "px; min-width: " . $this->minWidth . "px; float:left; margin:20px;'></div>";
                $js.= $this->preSet;

                break;
        }

        print $js;
    }

}

Class picChart {

    function __construct($id, $text, $dataSet, $allowPointSelect, $title, $subtitle, $plotShadow, $plotBackgroundColor, $plotBorderWidth, $pointColor, $cursor, $dataLabels) {

        $this->text = $text;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->allowPointSelect = $allowPointSelect;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->id = $id;
        $this->plotShadow = $plotShadow;
        $this->plotBackgroundColor = $plotBackgroundColor;
        $this->plotBorderWidth = $plotBorderWidth;
        $this->pointColor = $pointColor;
        $this->cursor = $cursor;
        $this->dataLabels = $dataLabels;
    }

    public static function dafaultDataSet() {
        return "[
            ['One', 1]
            ,['Two',2]
            ,['Three',3]
            ,['Four',4]
            ,['Five',5]
            ,['Six',6]
            ]";
    }

    public function createHighChartJS() {

        $js = "$('#" . $this->id . "').highcharts({";
        $js .="chart: {";
        $js .="plotBackgroundColor: " . $this->plotBackgroundColor . ",";
        $js .="plotBorderWidth: " . $this->plotBorderWidth . ",";
        $js .="plotShadow: " . $this->plotShadow;
        $js .="},";
        $js .="title: {";
        $js .="text: '" . $this->title . "'";
        $js .="},";

        if ($this->subtitle) {
            $js.=",subtitle: {";
            $js.="text: '" . $this->subtitle . "'";
            $js.="}";
        }

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

    function __construct($id, $title, $subtitle, $dataSet, $xAxis, $text, $pointPadding, $borderWidth, $headerFontsize, $tooltipsShared, $tooltipsUseHTML, $yAxisMin, $plotBorderWidth) {

        $this->title = $title;
        $this->subtitle = $subtitle;
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

    public static function dafaultDataSet() {
        return "[{
            name: 'Mail',
            data: [10,5,10]
        },{
            name: 'Femail',
            data: [2,5,15]
        }]";
    }

    public static function dafaultxAxis() {
        return "{
            categories: [
                'Good',
                'Bad',
                'Very Good',
            ]
        }";
    }

    public function createHighChartJS() {


        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'column'";
        $js.=",plotBorderWidth:" . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="}";

        $js.=",subtitle: {";
        $js.="text: '" . $this->subtitle . "'";
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

    function __construct($id, $text, $dataSet, $title, $subtitle, $legend, $seriesName, $dataLabelsColor, $dataLabelsAlign, $dataLabelsX, $dataLabelsY, $dataLabelsStyleFontsize, $dataLabelsStyleFontfamily, $dataLabelsStyleShadow, $dataLabels, $dataLabelsRotation, $xAxisLabelsRotation, $xAxisLabelsStyleFontsize, $xAxisLablesStyleFontfamily, $plotBorderWidth, $yAxisMin) {

        $this->id = $id;
        $this->text = $text;
        $this->dataSet = $dataSet ? $dataSet : self::dafaultDataSet();
        $this->title = $title;
        $this->subtitle = $subtitle;
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

    public static function dafaultDataSet() {
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

    public function createHighChartJS() {

        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'column'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="}";

        $js.=",subtitle: {";
        $js.="text: '" . $this->subtitle . "'";
        $js.="}";

        $js.=",xAxis: {";
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

    function __construct($id, $title, $subtitle, $dataSet, $xAxis, $text, $plotBorderWidth, $tooltipsShared, $pointColor, $legendLayout, $legendAlign, $legendVerticalAlign, $legendX, $legendY, $legendFloating, $legendBorderWidth
    , $plotBandsFrom, $plotBandsTo, $plotBandsColor, $tooltipsValueSuffix, $credits, $plotOptionsAreasplineFillOpacity) {

        $this->title = $title;
        $this->subtitle = $subtitle;
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

    public static function dafaultDataSet() {
        return "[{
            name: 'John',
            data: [3, 4, 3, 5, 4, 10, 12]
        },{
            name: 'Jane',
            data: [1, 3, 4, 3, 3, 5, 4]
        }]";
    }

    public static function dafaultxAxis() {
        return "
            categories: ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']
        ";
    }

    public function createHighChartJS() {

        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'areaspline'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="}";

        $js.=",subtitle: {";
        $js.="text: '" . $this->subtitle . "'";
        $js.="}";

        $js.=",legend: {";
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

    function __construct($id, $title, $subtitle, $dataSet, $xAxis, $text, $plotBorderWidth, $tooltipsShared, $pointColor, $tooltipsValueSuffix, $tickmarkPlacement
    , $plotOptionsAreaStacking, $plotOptionsAreaLineColor, $plotOptionsAreaLineWidth, $plotOptionsAreaMarkerLineColor, $plotOptionsAreaMarkerLineWidth) {

        $this->title = $title;
        $this->subtitle = $subtitle;
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

    public static function dafaultDataSet() {
        return "[{
            name: 'Asia',
            data: [502, 635, 809, 947, 1402, 3634, 5268]
        },{
            name: 'Africa',
            data: [106, 107, 111, 133, 221, 767, 1766]
        },{
            name: 'Europe',
            data: [163, 203, 276, 408, 547, 729, 628]
        },{
            name: 'America',
            data: [18, 31, 54, 156, 339, 818, 1201]
        },{
            name: 'Oceania',
            data: [2, 2, 2, 6, 13, 30, 46]
        }]";
    }

    public static function dafaultxAxis() {
        return "
            categories: ['1750', '1800', '1850', '1900', '1950', '1999', '2050']
        ";
    }

    public function createHighChartJS() {

        $js = "$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'area'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth . "";
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="}";

        $js.=",subtitle: {";
        $js.="text: '" . $this->subtitle . "'";
        $js.="}";

        $js.=",xAxis: {" . $this->xAxis . ",";
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

Class calumnDrillDownChart {

    function __construct($id, $title, $subtitle, $text, $legend, $dataLabels, $headerFontsize, $plotBorderWidth) {
        $this->id = $id;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->text = $text;
        $this->legend = $legend;
        $this->dataLabels = $dataLabels;
        $this->headerFontsize = $headerFontsize;
        $this->plotBorderWidth = $plotBorderWidth;
    }

    public function createHighChartJS() {

        $js = "Highcharts.data({";
        $js.="csv: document.getElementById('tsv').innerHTML,";
        $js.="itemDelimiter: '    ',";
        $js.="parsed: function (columns) {";
        $js.="var brands = {},";
        $js.="brandsData = [],";
        $js.="versions = {},";
        $js.="drilldownSeries = [];";
// Parse percentage strings
        $js.="columns[1] = $.map(columns[1], function (value) {";
        $js.="if (value.indexOf('%') === value.length - 1) {";
        $js.="value = parseFloat(value);";
        $js.="}";
        $js.="return value;";
        $js.="});";
        $js.="$.each(columns[0], function (i, name) {";
        $js.="var brand,";
        $js.="version;";
        $js.="if (i > 0) {";
// Remove special edition notes
        $js.="name = name.split(' -')[0];";
// Split into brand and version
        $js.="version = name.match(/([0-9]+[\.0-9x]*)/);";
        $js.="if (version) {";
        $js.="version = version[0];";
        $js.="}";
        $js.="brand = name.replace(version, '');";
// Create the main data
        $js.="if (!brands[brand]) {";
        $js.="brands[brand] = columns[1][i];";
        $js.="} else {";
        $js.="brands[brand] += columns[1][i];";
        $js.="}";
// Create the version data
        $js.="if (version !== null) {";
        $js.="if (!versions[brand]) {";
        $js.="versions[brand] = [];";
        $js.="}";
        $js.="versions[brand].push(['v' + version, columns[1][i]]);";
        $js.="}";
        $js.="}";

        $js.="});";

        $js.="$.each(brands, function (name, y) {";
        $js.="brandsData.push({";
        $js.="name: name,";
        $js.="y: y,";
        $js.="drilldown: versions[name] ? name : null";
        $js.="});";
        $js.="});";
        $js.="$.each(versions, function (key, value) {";
        $js.="drilldownSeries.push({";
        $js.="name: key,";
        $js.="id: key,";
        $js.="data: value";
        $js.="});";
        $js.="});";

// Create the chart
        $js.="$('#" . $this->id . "').highcharts({";
        $js.="chart: {";
        $js.="type: 'column'";
        $js.=",plotBorderWidth: " . $this->plotBorderWidth;
        $js.="},";
        $js.="title: {";
        $js.="text: '" . $this->title . "'";
        $js.="}";

        $js.=",subtitle: {";
        $js.="text: '" . $this->subtitle . "'";
        $js.="}";

        $js.=",xAxis: {";
        $js.="type: 'category'";
        $js.="},";
        $js.="yAxis: {";
        $js.="title: {";
        $js.="text: '" . $this->text . "'";
        $js.="}";
        $js.="},";
        $js.="legend: {";
        $js.="enabled: " . $this->legend;
        $js.="},";
        $js.="plotOptions: {";
        $js.="series: {";
        $js.="borderWidth: 0,";
        $js.="dataLabels: {";
        $js.="enabled: " . $this->dataLabels . ",";
        $js.="format: '{point.y:.1f}%'";
        $js.="}";
        $js.="}";
        $js.="},";
        $js.="tooltip: {";
        $js.="headerFormat: '<span style=\"font-size:" . $this->headerFontsize . "px\">{series.name}</span><br>',";
        $js.="pointFormat: '<span style=\"color:{point.color}\">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'";
        $js.="},";
        $js.="series: [{";
        $js.="name: 'Brands',";
        $js.="colorByPoint: true,";
        $js.="data: brandsData";
        $js.="}],";
        $js.="drilldown: {";
        $js.="series: drilldownSeries";
        $js.="}";
        $js.="})";
        $js.="}";
        $js.="});";
        return $js;
    }

}

?>