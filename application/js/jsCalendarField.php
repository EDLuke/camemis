<?php

///////////////////////////////////////////////////////////
// @Morng Thou
// @date
// 03 Rue des Piblues Bailly Romainvilliers
///////////////////////////////////////////////////////////

function jsReminderField() {
    $NONE = NONE;
    $AT_START_TIME = AT_START_TIME;
    $_5_MIN_BEFORE_START = _5_MIN_BEFORE_START;
    $_15_MIN_BEFORE_START = _15_MIN_BEFORE_START;
    $_30_MIN_BEFORE_START = _30_MIN_BEFORE_START;
    $_45_MIN_BEFORE_START = _45_MIN_BEFORE_START;
    $_1_HOUR_BEFORE_START = _1_HOUR_BEFORE_START;
    $_1_5_HOUR_BEFORE_START = _1_5_HOUR_BEFORE_START;
    $_2_HOUR_BEFORE_START = _2_HOUR_BEFORE_START;
    $_3_HOUR_BEFORE_START = _3_HOUR_BEFORE_START;
    $_6_HOUR_BEFORE_START = _6_HOUR_BEFORE_START;
    $_12_HOUR_BEFORE_START =  _12_HOUR_BEFORE_START;
    $_1_DAY_BEFORE_START = _1_DAY_BEFORE_START;
    $_2_DAY_BEFORE_START = _2_DAY_BEFORE_START;
    $_3_DAY_BEFORE_START = _3_DAY_BEFORE_START;
    $_4_DAY_BEFORE_START = _4_DAY_BEFORE_START;
    $_5_DAY_BEFORE_START = _5_DAY_BEFORE_START;
    $_1_WEEK_BEFORE_START = _1_WEEK_BEFORE_START;
    $_2_WEEK_BEFORE_START = _2_WEEK_BEFORE_START;

    $REMINDER = REMINDER;
    $string = <<<CODE
    /*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.ReminderField
 * @extends Ext.form.ComboBox
 * <p>A custom combo used for choosing a reminder setting for an event.</p>
 * <p>This is pretty much a standard combo that is simply pre-configured for the options needed by the
 * calendar components. The default configs are as follows:<pre><code>
    width: 200,
CODE;
$string .= "fieldLabel: '" . $REMINDER . "',";
$string .= <<<CODE
    mode: 'local',
    triggerAction: 'all',
    forceSelection: true,
    displayField: 'desc',
    valueField: 'value'
</code></pre>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.ReminderField = Ext.extend(Ext.form.ComboBox, {
    width: 200,
    fieldLabel: 'Reminder',
    mode: 'local',
    triggerAction: 'all',
    forceSelection: true,
    displayField: 'desc',
    valueField: 'value',

    // private
    initComponent: function() {
        Ext.calendar.ReminderField.superclass.initComponent.call(this);

        this.store = this.store || new Ext.data.ArrayStore({
            fields: ['value', 'desc'],
            idIndex: 0,
            data: [
CODE;
    $string .= "['', '" . $NONE . "'],";

    $string .= "['0', '" . $AT_START_TIME . "'],";
    $string .= "['5', '" . $_5_MIN_BEFORE_START . "'],";
    $string .= "['15', '" . $_15_MIN_BEFORE_START . "'],";
    $string .= "['30', '" . $_30_MIN_BEFORE_START . "'],";
    $string .= "['45', '" . $_45_MIN_BEFORE_START . "'],";
    $string .= "['60', '" . $_1_HOUR_BEFORE_START . "'],";
    $string .= "['90', '" . $_1_5_HOUR_BEFORE_START . "'],";
    $string .= "['120', '" . $_2_HOUR_BEFORE_START . "'],";
    $string .= "['180', '" . $_3_HOUR_BEFORE_START . "'],";
    $string .= "['360', '" . $_6_HOUR_BEFORE_START . "'],";
    $string .= "['720', '" . $_12_HOUR_BEFORE_START . "'],";
    $string .= "['1440', '" . $_1_DAY_BEFORE_START . "'],";
    $string .= "['2880', '" . $_2_DAY_BEFORE_START . "'],";
    $string .= "['4320', '" . $_3_DAY_BEFORE_START . "'],";
    $string .= "['5760', '" . $_4_DAY_BEFORE_START . "'],";
    $string .= "['7200', '" . $_5_DAY_BEFORE_START . "'],";
    $string .= "['10080', '" . $_1_WEEK_BEFORE_START . "'],";
    $string .= "['20160', '" . $_2_WEEK_BEFORE_START . "']";

    $string .= <<<CODE
            ]
        });
    },

    // inherited docs
    initValue: function() {
        if (this.value !== undefined) {
            this.setValue(this.value);
        }
        else {
            this.setValue('');
        }
        this.originalValue = this.getValue();
    }
});

Ext.reg('reminderfield', Ext.calendar.ReminderField);
CODE;
    
    return $string;
}

function jsDateRangeField(){
    
    $ALL_DAY  = ALL_DAY;
    $START_TIME = START_TIME;
    $END_TIME = END_TIME;
    $string = <<<CODE
    
    /*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.DateRangeField
 * @extends Ext.form.Field
 * <p>A combination field that includes start and end dates and times, as well as an optional all-day checkbox.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.DateRangeField = Ext.extend(Ext.form.Field, {
    /**
     * @cfg {String} toText
     * The text to display in between the date/time fields (defaults to 'to')
     */
    toText: 'to',
CODE;
   $string .= "startText: '".$START_TIME."',"; 
   $string .= "endText: '".$END_TIME."',"; 
   
   $string .= <<<CODE
    /**
     * @cfg {String} toText
     * The text to display as the label for the all day checkbox (defaults to 'All day')
     */
CODE;
   $string .= "allDayText: '".$ALL_DAY."',"; 
   $string .= <<<CODE
    

    // private
    onRender: function(ct, position) {
        if (!this.el) {
            this.startDate = new Ext.form.DateField({
                id: this.id + '-start-date',
                style: {marginBottom: '5px'},
CODE;
   $string .= "format: '" . setExtDatafieldFormat() . "',";
   $string .= <<<CODE
                width: 100,
                listeners: {
                    'change': {
                        fn: function() {
                            this.checkDates('date', 'start');
                            var button = Ext.getCmp("save-btn");
                            button.enable();
                        },
                        scope: this
                    }
                }
            });
            this.startTime = new Ext.form.TimeField({
                id: this.id + '-start-time',
                style: {marginBottom: '5px'},
                format:'H:i',
                increment: 30,
                hidden: this.showTimes === false,
                labelWidth: 0,
                autoSelect: false,
                hideLabel: true,
                width: 90,                
                //minValue: '06:00',
                //maxValue: '22:00',
                listeners: {
                    'select': {
                        fn: function() {
                            this.checkDates('time', 'start');
                            var button = Ext.getCmp("save-btn");
                            button.enable();                            
                        },
                        scope: this
                    }
                }
            });
            this.endTime = new Ext.form.TimeField({
                id: this.id + '-end-time',
                style: {
                    marginBottom: '5px'
                },
                format:'H:i',
                increment: 30,
                hidden: this.showTimes === false,
                labelWidth: 0,
                hideLabel: true,
                width: 90,
                //minValue: '6:00',
                //maxValue: '22:00',
                listeners: {
                    'select': {
                        fn: function() {
                            this.checkDates('time', 'end');
                            var button = Ext.getCmp("save-btn");
                            button.enable();                            
                        },
                        scope: this
                    }
                }
            });
            this.endDate = new Ext.form.DateField({
                id: this.id + '-end-date',
                style: {
                    marginBottom: '5px'
                },
CODE;
   $string .= "format: '" . setExtDatafieldFormat() . "',";
   $string .= <<<CODE
                hideLabel: true,
                width: 100,
                listeners: {
                    'change': {
                        fn: function() {
                            this.checkDates('date', 'end');
                            var button = Ext.getCmp("save-btn");
                            button.enable();
                        },
                        scope: this
                    }
                }
            });
            this.allDay = new Ext.form.Checkbox({
                id: this.id + '-allday',
                hidden: this.showTimes === false || this.showAllDay === false,
                boxLabel: this.allDayText,
                handler: function(chk, checked) {
                    this.startTime.setVisible(!checked);
                    this.endTime.setVisible(!checked);
                    var button = Ext.getCmp("save-btn");
                    button.enable();
                },
                scope: this
            });
            this.toLabel = new Ext.form.Label({
                xtype: 'label',
                id: this.id + '-to-label',
                text: this.toText
            });
            this.startLabel = new Ext.form.Label({
                xtype: 'label',
                id: this.id + '-start-label',
                text: this.startText
            });
            this.endLabel = new Ext.form.Label({
                xtype: 'label',
                id: this.id + '-end-label',
                text: this.endText
            });

            this.fieldCt = new Ext.Container({
                autoEl: {
                    id: this.id
                },
                //make sure the container el has the field's id
                cls: 'ext-dt-range',
                renderTo: ct,
                layout: 'table',
                layoutConfig: {
                    columns: 3
                },
                defaults: {
                    hideParent: true
                },
                items: [
                    this.startLabel,
                    this.startDate,
                    this.startTime,

                    this.endLabel,
                    this.endDate,
                    this.endTime,
                    this.allDay
                ]
            });

            this.fieldCt.ownerCt = this;
            this.el = this.fieldCt.getEl();
            this.items = new Ext.util.MixedCollection();
            this.items.addAll([this.startDate, this.endDate, this.toLabel, this.startTime, this.endTime, this.allDay]);
        }
        Ext.calendar.DateRangeField.superclass.onRender.call(this, ct, position);
    },

    // private
    checkDates: function(type, startend) {
        var startField = Ext.getCmp(this.id + '-start-' + type),
        endField = Ext.getCmp(this.id + '-end-' + type),
        startValue = this.getDT('start'),
        endValue = this.getDT('end');

        if (startValue > endValue) {
            if (startend == 'start') {
                endField.setValue(startValue);
            } else {
                startField.setValue(endValue);
                this.checkDates(type, 'start');
            }
        }
        if (type == 'date') {
            this.checkDates('time', startend);
        }
    },

    /**
     * Returns an array containing the following values in order:<div class="mdetail-params"><ul>
     * <li><b><code>DateTime</code></b> : <div class="sub-desc">The start date/time</div></li>
     * <li><b><code>DateTime</code></b> : <div class="sub-desc">The end date/time</div></li>
     * <li><b><code>Boolean</code></b> : <div class="sub-desc">True if the dates are all-day, false 
     * if the time values should be used</div></li><ul></div>
     * @return {Array} The array of return values
     */
    getValue: function() {
        return [
        this.getDT('start'),
        this.getDT('end'),
        this.allDay.getValue()
        ];
    },

    // private getValue helper
    getDT: function(startend) {
        var time = this[startend + 'Time'].getValue(),
        dt = this[startend + 'Date'].getValue();

        if (Ext.isDate(dt)) {
            dt = dt.format(this[startend + 'Date'].format);
        }
        else {
            return null;
        };
        if (time != '' && this[startend + 'Time'].isVisible()) {
            return Date.parseDate(dt + ' ' + time, this[startend + 'Date'].format + ' ' + this[startend + 'Time'].format);
        }
        return Date.parseDate(dt, this[startend + 'Date'].format);

    },

    /**
     * Sets the values to use in the date range.
     * @param {Array/Date/Object} v The value(s) to set into the field. Valid types are as follows:<div class="mdetail-params"><ul>
     * <li><b><code>Array</code></b> : <div class="sub-desc">An array containing, in order, a start date, end date and all-day flag.
     * This array should exactly match the return type as specified by {@link #getValue}.</div></li>
     * <li><b><code>DateTime</code></b> : <div class="sub-desc">A single Date object, which will be used for both the start and
     * end dates in the range.  The all-day flag will be defaulted to false.</div></li>
     * <li><b><code>Object</code></b> : <div class="sub-desc">An object containing properties for StartDate, EndDate and IsAllDay
     * as defined in {@link Ext.calendar.EventMappings}.</div></li><ul></div>
     */
    setValue: function(v) {
        if (Ext.isArray(v)) {
            this.setDT(v[0], 'start');
            this.setDT(v[1], 'end');
            this.allDay.setValue( !! v[2]);
        }
        else if (Ext.isDate(v)) {
            this.setDT(v, 'start');
            this.setDT(v, 'end');
            this.allDay.setValue(false);
        }
        else if (v[Ext.calendar.EventMappings.StartDate.name]) {
            //object
            this.setDT(v[Ext.calendar.EventMappings.StartDate.name], 'start');
            if (!this.setDT(v[Ext.calendar.EventMappings.EndDate.name], 'end')) {
                this.setDT(v[Ext.calendar.EventMappings.StartDate.name], 'end');
            }
            this.allDay.setValue( !! v[Ext.calendar.EventMappings.IsAllDay.name]);
        }
    },

    // private setValue helper
    setDT: function(dt, startend) {
        if (dt && Ext.isDate(dt)) {
            this[startend + 'Date'].setValue(dt);
            this[startend + 'Time'].setValue(dt.format(this[startend + 'Time'].format));
            return true;
        }
    },

    // inherited docs
    isDirty: function() {
        var dirty = false;
        if (this.rendered && !this.disabled) {
            this.items.each(function(item) {
                if (item.isDirty()) {
                    dirty = true;
                    return false;
                }
            });
        }
        return dirty;
    },

    // private
    onDisable: function() {
        this.delegateFn('disable');
    },

    // private
    onEnable: function() {
        this.delegateFn('enable');
    },

    // inherited docs
    reset: function() {
        this.delegateFn('reset');
    },

    // private
    delegateFn: function(fn) {
        this.items.each(function(item) {
            if (item[fn]) {
                item[fn]();
            }
        });
    },

    // private
    beforeDestroy: function() {
        Ext.destroy(this.fieldCt);
        Ext.calendar.DateRangeField.superclass.beforeDestroy.call(this);
    },

    /**
     * @method getRawValue
     * @hide
     */
    getRawValue: Ext.emptyFn,
    /**
     * @method setRawValue
     * @hide
     */
    setRawValue: Ext.emptyFn
});

Ext.reg('daterangefield', Ext.calendar.DateRangeField);

CODE;
    
    return $string;
}

function jsCalendarPicker(){
    $TYPE = TYPE;
    $string = <<<CODE
    /*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.CalendarPicker
 * @extends Ext.form.ComboBox
 * <p>A custom combo used for choosing from the list of available calendars to assign an event to.</p>
 * <p>This is pretty much a standard combo that is simply pre-configured for the options needed by the
 * calendar components. The default configs are as follows:<pre><code>
    fieldLabel: 'Calendar',
    valueField: 'CalendarId',
    displayField: 'Title',
    triggerAction: 'all',
    mode: 'local',
    forceSelection: true,
    width: 200
</code></pre>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.CalendarPicker = Ext.extend(Ext.form.ComboBox, {
CODE;
    $string .= "fieldLabel: '".$TYPE."',";
    $string .= <<<CODE

    valueField: 'CalendarId',
    displayField: 'Title',
    triggerAction: 'all',
    mode: 'local',
    forceSelection: true,
    width: 200,

    // private
    initComponent: function() {
        Ext.calendar.CalendarPicker.superclass.initComponent.call(this);
        this.tpl = this.tpl ||
        '<tpl for="."><div class="x-combo-list-item ext-color-{' + this.valueField +
        '}"><div class="ext-cal-picker-icon">&nbsp;</div>{' + this.displayField + '}</div></tpl>';
    },

    // private
    afterRender: function() {
        Ext.calendar.CalendarPicker.superclass.afterRender.call(this);

        this.wrap = this.el.up('.x-form-field-wrap');
        this.wrap.addClass('ext-calendar-picker');

        this.icon = Ext.DomHelper.append(this.wrap, {
            tag: 'div',
            cls: 'ext-cal-picker-icon ext-cal-picker-mainicon'
        });
    },

    // inherited docs
    setValue: function(value) {
        this.wrap.removeClass('ext-color-' + this.getValue());
        if (!value && this.store !== undefined) {
            // always default to a valid calendar
            value = this.store.getAt(0).data.CalendarId;
        }
        Ext.calendar.CalendarPicker.superclass.setValue.call(this, value);
        this.wrap.addClass('ext-color-' + value);
    }
});

Ext.reg('calendarpicker', Ext.calendar.CalendarPicker);
CODE;
    return $string;
}
?>
