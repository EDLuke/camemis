<?

///////////////////////////////////////////////////////////
// @Kaom Vibolrith Senior Software Developer
// Date: 03.03.2009
// Am Stollheen 18, 55120 Mainz, Germany
///////////////////////////////////////////////////////////
require_once ("Zend/Loader.php");

class AdminBar {

    public $data = array();

    public function __construct()
    {

        //
    }

    static function tbarTreeCollapse()
    {

        $js = "";
        $js .= "id: 'TBAR_COLLAPSE_ID'";
        $js .= ",tooltip:'Collapse'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-collapse-all'";
        $js .= ",handler: this.onCollapse";

        return $js;
    }

    static function tbarTreeExpand()
    {

        $js = "";
        $js .= "id: 'TBAR_EXPAND_ID'";
        $js .= ",tooltip:'Expand'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-expand-all'";
        $js .= ",handler: this.onExpand";

        return $js;
    }

    static function tbarLoad($disabled = false)
    {

        $js = "";
        $js .= "text: 'Load'";
        $js .= ",id: 'LOAD_ID'";
        $js .= ",iconCls:'icon-reload'";
        $js .= ",formBind:true";
        $js .= ",scope:this";
        $js .= ",handler:this.onLoad";

        return $js;
    }

    static function tbarFormRefresh($Id)
    {

        $js = "";
        $js .= "id: '" . $Id . "'";
        $js .= ",text: 'Refresh'";
        $js .= ",iconCls:'icon-reload'";
        $js .= ",handler: function(){";

        $js .= "}";

        return $js;
    }

    static function tbarSingleSave()
    {

        $js = "";
        $js .= "text: 'Save'";
        $js .= ",id: 'SINGLE_SAVE_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",tooltip:'Save'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarSimpleSave()
    {

        $js = "";
        $js .= "text: 'Save'";
        $js .= ",id: 'SIMPLE_SAVE_ID'";
        $js .= ",formBind:true";
        $js .= ",iconCls:'icon-disk'";
        $js .= ",scope:this";
        $js .= ",tooltip:'Save'";
        $js .= ",handler:this.onSubmit";

        return $js;
    }

    static function tbarReset($disabled = false)
    {

        $js = "";
        $js .= "text:'Reset'";
        $js .= ",tooltip:'Reset'";
        $js .= ",id: 'RESET_ID'";
        $js .= ",iconCls:'icon-arrow_undo'";
        $js .= ",scope:this";
        $js .= ",handler: this.onReset";

        return $js;
    }

    static function tbarTreeRefresh($Id = false)
    {

        $js = "";
        if ($Id)
        {
            $js .= "id: 'REFRESH_ID_" . $Id . "'";
        }
        else
        {
            $js .= "id: 'REFRESH_ID'";
        }
        $js .= ",tooltip:'Refresh'";
        $js .= ",scope:this";
        $js .= ",iconCls:'icon-reload'";
        $js .= ",handler: this.onTBarRefresh";

        return $js;
    }

    static function tbarCreate($handler, $Id = false)
    {

        $js = "";
        $js .= "text: 'Add'";
        if ($Id)
        {
            $js .= ",id: 'ADD_ENTRY_ID_" . $Id . "'";
        }
        else
        {
            $js .= ",id: 'ADD_ENTRY_ID'";
        }
        $js .= ",iconCls:'icon-application_form_add'";
        $js .= ",formBind:true";
        $js .= ",scope:this";
        $js .= ",tooltip:'Add'";
        $js .= ",handler: " . $handler . "";
        return $js;
    }

    static function tbarSingleRemove()
    {

        $js = "";
        $js .= "id: 'SINGLE_REMOVE_ID'";
        $js .= ",text: 'Remove'";
        $js .= ",iconCls:'icon-delete'";
        $js .= ",scope:this";
        $js .= ",handler: this.onSetObjectRemove";
        return $js;
    }

}

?>