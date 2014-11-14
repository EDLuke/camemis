Ext.ns('Ext.ux.grid');

/**
 * A Column definition class which renders enum data fields.
 * @class Ext.ux.grid.CheckboxColumn
 * @extends Ext.grid.Column
 * @author Tran Cong Ly - tcl_java@yahoo.com - http://5cent.net
 * Create the column:
 *   
var cm = new Ext.grid.ColumnModel([
new Ext.ux.grid.CheckboxColumn({
    header: 'Header #1',
    dataIndex: 'field_name_1'
},
{
    xtype: 'checkboxcolumn',
    header: 'Header #2',
    dataIndex: 'field_name_2',
    on: 1,
    off: 0
},
{
    xtype: 'checkboxcolumn',
    header: 'Header #3',
    dataIndex: 'field_name_3',
    on: 'abc',
    off: 'def'
}])
 
 */
Ext.ux.grid.CheckboxColumn = Ext.extend(Ext.grid.Column, {
    on: true,
    off: false,
    constructor: function (cfg) {
        Ext.ux.grid.CheckboxColumn.superclass.constructor.call(this, cfg);
        //this.editor = new Ext.form.Field();
        this.editor = new Ext.grid.GridEditor(new Ext.form.Field());
        var cellEditor = this.getCellEditor(),
            on = this.on,
            off = this.off;
        cellEditor.on('startedit', function (el, v) {
            cellEditor.setValue(String(v) == String(on) ? off : on);
            cellEditor.hide();
        });
        this.renderer = function (value, metaData, record, rowIndex, colIndex, store) {
            metaData.css += ' x-grid3-check-col-td';
            return '<div class="x-grid3-check-col' + (String(value) == String(on) ? '-on' : '') + '"></div>';
        }
    }
});
Ext.grid.Column.types['checkboxcolumn'] = Ext.ux.grid.CheckboxColumn;