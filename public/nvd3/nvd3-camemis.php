<?
// License: none (public domain) 
/*
 * Dieses Script concateniert mehrere Dateien und schickt sie gzip
 * komprimiert an den Browser. 
 * Ausserdem wird ein Etag gesetzt, der nötig ist, um das Cachen der 
 * Javascript Datei zu ermöglichen. Der Etag ändert sich jedesmal, wenn 
 * eine der Dateien geändert wird, oder eine hinzu-/weggenommen wird. 
 */
header('Content-Type: text/javascript');
header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600 * 24) . " GMT");

/*
 * Hier die JavaScript Dateien angeben:
 */

chdir('../nvd3');
$PHAT_PUBLIC = getcwd();

//error_log($PHAT_PUBLIC);

$files = array(
    "" . $PHAT_PUBLIC . "/lib/d3.v3.js"
    , "" . $PHAT_PUBLIC . "/nv.d3.js"
    , "" . $PHAT_PUBLIC . "/src/tooltip.js"
    , "" . $PHAT_PUBLIC . "/src/utils.js"
    , "" . $PHAT_PUBLIC . "/src/interactiveLayer.js"
    , "" . $PHAT_PUBLIC . "/src/models/axis.js"
    , "" . $PHAT_PUBLIC . "/src/models/scatter.js"
    , "" . $PHAT_PUBLIC . "/src/models/discreteBar.js"
    , "" . $PHAT_PUBLIC . "/src/models/discreteBarChart.js"
    , "" . $PHAT_PUBLIC . "/src/models/legend.js"
    , "" . $PHAT_PUBLIC . "/src/models/pie.js"
    , "" . $PHAT_PUBLIC . "/src/models/pieChart.js"
    , "" . $PHAT_PUBLIC . "/src/models/stackedArea.js"
    , "" . $PHAT_PUBLIC . "/src/models/stackedAreaChart.js"
    , "" . $PHAT_PUBLIC . "/src/models/multiBarHorizontal.js"
    , "" . $PHAT_PUBLIC . "/src/models/multiBarHorizontalChart.js"
    , "" . $PHAT_PUBLIC . "/src/models/stream_layers.js"
    , "" . $PHAT_PUBLIC . "/src/models/multiBar.js"
    , "" . $PHAT_PUBLIC . "/src/models/multiBarChart.js"
    , "" . $PHAT_PUBLIC . "/src/models/line.js"
    , "" . $PHAT_PUBLIC . "/src/models/cumulativeLineChart.js"
);

$md5 = '';
foreach ($files as $file) {
    $md5 .= md5_file($file);
}

$etag = md5($md5);
header("Etag: $etag");

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
    header('HTTP/1.1 304 Not Modified');
} else {
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], "gzip") !== false) {
        ob_start("ob_gzhandler");
    } else {
        ob_start();
    }

    foreach ($files as $file) {
        echo file_get_contents($file);
    }
}
?>