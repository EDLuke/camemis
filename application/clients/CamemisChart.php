<?

Class CamemisChart {

    public static function renderJsStackedAreaChart($name, $dataset, $divExtContent, $divSVG, $divHeight = 260) {
        
        $dataset = $dataset?$dataset:"[]";
        $js = "<script>";
        $js .="var colors = d3.scale.category20();keyColor = function(d, i) {return colors(d.key)};";
        $js .="nv.addGraph(function() {";
            $js .="".$name." = nv.models.stackedAreaChart()";
            $js .=".x(function(d) { return d[0] })";
            $js .=".y(function(d) { return d[1] })";
            $js .=".color(keyColor);";
            //$js .=".clipEdge(true);";
            //$js .="".$name.".stacked.scatter.clipVoronoi(false);";
            $js .="".$name.".xAxis";
            $js .=".tickFormat(function(d) { return d3.time.format('%x')(new Date(d)) });";
            $js .="".$name.".yAxis";
            $js .=".tickFormat(d3.format(',.2f'));";
            $js .="d3.select('#".$divSVG."')";
            $js .=".datum(".$dataset.")";
            $js .=".transition().duration(500).call(".$name.");";
            $js .="nv.utils.windowResize(".$name.".update);";
            $js .="".$name.".dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });";
            $js .="return ".$name.";";
        $js .="});";
        $js .= "</script>";
        
        $html = "";
        $html .= "<div id='".$divExtContent."' class='x-hidden'>";
            $html .= "<div id='SVG-".$divSVG."'>";
                $html .= "<svg style='background-color:#FFF;height:".$divHeight."px;'></svg>";
            $html .= "</div>";
        $html .= "</div>";
        
        echo $js;
        echo $html;
        
    }
    public static function renderJsMultiBarChart($name, $dataset, $divExtContent, $divSVG, $divHeight = 260) {
        
        /**
         * [
            {
                'key' : 'First Block'
                ,'color':'#FFDEDE'
                ,'values':[
                    {'x':'Monday','y':0}
                    ,{'x':'Tuesday','y':0}
                    ,{'x':'Wednesday','y':0}
                    ,{'x':'Thursday','y':0}
                    ,{'x':'Friday','y':0}
                    ,{'x':'Saturday','y':0}
                    ,{'x':'Sunday','y':0}
                ]
            },{
                'key' : 'Second Block'
                ,'color':'#99BBE8'
                ,'values':[
                    {'x':'Monday','y':0}
                    ,{'x':'Tuesday','y':0}
                    ,{'x':'Wednesday','y':0}
                    ,{'x':'Thursday','y':0}
                    ,{'x':'Friday','y':0}
                    ,{'x':'Saturday','y':0}
                    ,{'x':'Sunday','y':0}
                ]
            }
        ];
         */
        
        $dataset = $dataset?$dataset:"[]";
        $js = "<script>";
        $js .= "nv.addGraph(function() {";
            $js .= "".$name." = nv.models.multiBarChart();";
            $js .= "".$name.".width(percentWidth(95));";
            $js .= "".$name.".multibar";
            $js .= ".hideable(true);";
            $js .= "".$name.".reduceXTicks(false).staggerLabels(true);";
            $js .= "".$name.".xAxis";
            $js .= ".tickFormat(function(d){ return d });";
            $js .= "".$name.".yAxis";
            $js .= ".tickFormat(d3.format(',f'));";
            $js .= "d3.select('#".$divSVG." svg')";
            $js .= ".datum(".$dataset.")";
            $js .= ".transition().duration(500).call(".$name.");";
            $js .= "nv.utils.windowResize(".$name.".update);";
            $js .= "".$name.".dispatch.on('stateChange', function(e) {";
                $js .= "nv.log('New State:', JSON.stringify(e));";
            $js .= "});";
            $js .= "return ".$name.";";
        $js .= "});";
        $js .= "</script>";
        
        $html = "";
        $html .= "<div id='".$divExtContent."' class='x-hidden'>";
            $html .= "<div id='SVG-".$divSVG."'>";
                $html .= "<svg style='background-color:#FFF;height:".$divHeight."px;'></svg>";
            $html .= "</div>";
        $html .= "</div>";
        
        echo $js;
        echo $html;
    }

}

?>