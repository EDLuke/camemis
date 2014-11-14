<?

// KAOM Vibolrith: 04.07.2013
/*
 * Dieses Script concateniert mehrere Dateien und schickt sie gzip
 * komprimiert an den Browser. 
 * Ausserdem wird ein Etag gesetzt, der nötig ist, um das Cachen der 
 * Javascript Datei zu ermöglichen. Der Etag ändert sich jedesmal, wenn 
 * eine der Dateien geändert wird, oder eine hinzu-/weggenommen wird. 
 */
header("Content-type: text/css; charset: UTF-8");
header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600 * 24) . " GMT");

/*
 * Hier die JavaScript Dateien angeben:
 */

$files = array(
    "ext-all.css"
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