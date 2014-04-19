// Create the namespace
Ext.namespace('Ext.ux.plugins.grid');

/**
 * Ext.ux.plugins.grid.CellToolTips plugin for Ext.grid.GridPanel
 *
 * A GridPanel plugin that enables the creation of record based,
 * per-column tooltips.
 *
 * Requires Animal's triggerElement override
 * (from <a href="http://extjs.com/forum/showthread.php?p=265259#post265259">http://extjs.com/forum/showthread.php?p=265259#post265259</a>)
 *
 * @author  BitPoet
 * @date    February 12, 2009
 * @version 1.0
 *
 * @class Ext.ux.plugins.grid.CellToolTips
 * @extends Ext.util.Observable
 */
 
/**
 * Constructor for the Plugin
 *
 * @param {ConfigObject} config
 * @constructor
 */
Ext.ux.plugins.grid.CellToolTips = function(config) {
    var cfgTips;
    if( Ext.isArray(config) ) {
        cfgTips = config;
        config = {};
    } else {
    	cfgTips = config.cellTips;
    }
    Ext.ux.plugins.grid.CellToolTips.superclass.constructor.call(this, config);
    if( config.tipConfig ) {
    	this.tipConfig = config.tipConfig;
    }
    this.cellTips = cfgTips;
} // End of constructor

// plugin code
Ext.extend( Ext.ux.plugins.grid.CellToolTips, Ext.util.Observable, {

    /**
     * Temp storage from the config object
     *
     * @private
     */
    cellTips: false,
    
    /**
     * Tooltip Templates indexed by column id
     *
     * @private
     */
    tipTpls: false,

    /**
     * Tooltip configuration items
     *
     * @private
     */
    tipConfig: {},

    /**
     * Plugin initialization routine
     *
     * @param {Ext.grid.GridPanel} grid
     */
    init: function(grid) {
        if( ! this.cellTips ) {
            return;
        }
        this.tipTpls = {};
        // Generate tooltip templates
        Ext.each( this.cellTips, function(tip) {
        	this.tipTpls[tip.field] = new Ext.XTemplate( tip.tpl );
        }, this);
        // delete now superfluous config entry for cellTips
        delete( this.cellTips );
        grid.on( 'render', this.onGridRender.createDelegate(this) );
    } // End of function init

    /**
     * Set/Add a template for a column
     *
     * @param {String} fld
     * @param {String | Ext.XTemplate} tpl
     */
    ,setFieldTpl: function(fld, tpl) {
        this.tipTpls[fld] = Ext.isObject(tpl) ? tpl : new Ext.XTemplate(tpl);
    } // End of function setFieldTpl

    /**
     * Set up the tooltip when the grid is rendered
     *
     * @private
     * @param {Ext.grid.GridPanel} grid
     */
    ,onGridRender: function(grid) 
    {
        if( ! this.tipTpls ) {
            return;
        }
        // Create one new tooltip for the whole grid
        Ext.apply(this.tipConfig, {
            target:      grid.getView().mainBody,
            delegate:    '.x-grid3-cell-inner',
            trackMouse:  true,
            renderTo:    document.body
        });
        var newTip = new Ext.ToolTip( this.tipConfig );
        // Hook onto the beforeshow event to update the tooltip content
        newTip.on('beforeshow', this.beforeTipShow.createDelegate(newTip, [this, grid], true));
    } // End of function onGridRender

    /**
     * Replace the tooltip body by applying current row data to the template
     *
     * @private
     * @param {Ext.ToolTip} tip
     * @param {Ext.ux.plugins.grid.CellToolTips} ctt
     * @param {Ext.grid.GridPanel} grid
     */
    ,beforeTipShow: function(tip, ctt, grid) {
    	// Get column id and check if a tip is defined for it
        var colIdx = grid.getView().findCellIndex( tip.triggerElement );
        var tipId = grid.getColumnModel().getDataIndex( colIdx );
        //alert( 'Checking if there is a tip for column ' + tipId );
        if( ! ctt.tipTpls[tipId] ) {
            return false;
        }
        // Fetch the row's record from the store and apply the template
        var cellRec = grid.getStore().getAt( grid.getView().findRowIndex( tip.triggerElement ) );
        tip.body.dom.innerHTML = ctt.tipTpls[tipId].apply( cellRec.data );
    } // End of function beforeTipShow

}); // End of extend