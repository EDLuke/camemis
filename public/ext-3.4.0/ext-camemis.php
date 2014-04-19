<?

// KAOM Vibolrith: 04.07.2013
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

chdir('../');
$PHAT_PUBLIC = getcwd();
chdir('ext-3.4.0');

$files = array(
    "adapter/ext/ext-base.js"
    , "ext-all.js"
    , "" . $PHAT_PUBLIC . "/plugin/Cookies.js"
	, "" . $PHAT_PUBLIC . "/plugin/TextFieldRemoteVal.js"
    , "" . $PHAT_PUBLIC . "/plugin/IFrameComponent.js"
    , "" . $PHAT_PUBLIC . "/plugin/SearchField.js"
	, "" . $PHAT_PUBLIC . "/ux/GroupSummary.js"
	, "" . $PHAT_PUBLIC . "/ux/Portal.js"
	, "" . $PHAT_PUBLIC . "/ux/PortalColumn.js"
	, "" . $PHAT_PUBLIC . "/ux/ColumnHeaderGroup.js"
	, "" . $PHAT_PUBLIC . "/ux/Ext.ux.form.BrowseButton.js"
	, "" . $PHAT_PUBLIC . "/ux/statusbar/StatusBar.js"
	, "" . $PHAT_PUBLIC . "/ux/Ext.ux.tot2ivn.VrTabPanel.js"
	, "" . $PHAT_PUBLIC . "/ux/RowLayout.js"
    , "" . $PHAT_PUBLIC . "/ux/treegrid/TreeGridSorter.js"
    , "" . $PHAT_PUBLIC . "/ux/treegrid/TreeGridColumnResizer.js"
    , "" . $PHAT_PUBLIC . "/ux/treegrid/TreeGridNodeUI.js"
    , "" . $PHAT_PUBLIC . "/ux/treegrid/TreeGridLoader.js"
    , "" . $PHAT_PUBLIC . "/ux/treegrid/TreeGridColumns.js"
    , "" . $PHAT_PUBLIC . "/ux/treegrid/TreeGrid.js"
    , "" . $PHAT_PUBLIC . "/plugin/Ext.ux.grid.CheckboxColumn.js"
    , "" . $PHAT_PUBLIC . "/plugin/FileUploadField.js"
    , "" . $PHAT_PUBLIC . "/plugin/ColorField.js"
    , "" . $PHAT_PUBLIC . "/plugin/SpinnerField.js"
    , "" . $PHAT_PUBLIC . "/plugin/Spinner.js"
    , "" . $PHAT_PUBLIC . "/plugin/Ext.ux.VrTabPanel.js"
    , "" . $PHAT_PUBLIC . "/plugin/Ext.ux.plugins.IconCombo.js"
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