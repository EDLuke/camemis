<?php

///////////////////////////////////////////////////////////
// @Morng Thou Softaware developper
// @16.02.2011
// 03 Rue des Piblues Bailly Romainvilliers
///////////////////////////////////////////////////////////

function jsCalendarPanel() {

    $DAY = DAY;
    $TODAY = TODAY;
    $WEEK = WEEK;
    $MONTH = MONTH;

    $string = <<<CODE
/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.CalendarPanel
 * @extends Ext.Panel
 * <p>This is the default container for Ext calendar views. It supports day, week and month views as well
 * as a built-in event edit form. The only requirement for displaying a calendar is passing in a valid
 * {@link #calendarStore} config containing records of type {@link Ext.calendar.EventRecord EventRecord}. In order
 * to make the calendar interactive (enable editing, drag/drop, etc.) you can handle any of the various
 * events fired by the underlying views and exposed through the CalendarPanel.</p>
 * {@link #layoutConfig} option if needed.</p>
 * @constructor
 * @param {Object} config The config object
 * @xtype calendarpanel
 */
Ext.calendar.CalendarPanel = Ext.extend(Ext.Panel, {
    /**
     * @cfg {Boolean} showDayView
     * True to include the day view (and toolbar button), false to hide them (defaults to true).
     */
    showDayView: true,
    /**
     * @cfg {Boolean} showWeekView
     * True to include the week view (and toolbar button), false to hide them (defaults to true).
     */
    showWeekView: true,
    /**
     * @cfg {Boolean} showMonthView
     * True to include the month view (and toolbar button), false to hide them (defaults to true).
     * If the day and week views are both hidden, the month view will show by default even if
     * this config is false.
     */
    showMonthView: true,
    /**
     * @cfg {Boolean} showNavBar
     * True to display the calendar navigation toolbar, false to hide it (defaults to true). Note that
     * if you hide the default navigation toolbar you'll have to provide an alternate means of navigating the calendar.
     */
    showNavBar: true,
    /**
     * @cfg {String} todayText
     * Alternate text to use for the 'Today' nav bar button.
     */
CODE;

    $string .= "todayText: '" . $TODAY . "',";

    $string .= <<<CODE
    /**
     * @cfg {Boolean} showTodayText
     * True to show the value of {@link #todayText} instead of today's date in the calendar's current day box,
     * false to display the day number(defaults to true).
     */
    showTodayText: true,
    /**
     * @cfg {Boolean} showTime
     * True to display the current time next to the date in the calendar's current day box, false to not show it 
     * (defaults to true).
     */
    showTime: true,
    /**
     * @cfg {String} dayText
     * Alternate text to use for the 'Day' nav bar button.
     */

CODE;

    $string .= "dayText: '" . $DAY . "',";

    $string .= <<<CODE

    /**
     * @cfg {String} weekText
     * Alternate text to use for the 'Week' nav bar button.
     */
CODE;
    $string .= "weekText: '" . $WEEK . "',";
    $string .= <<<CODE
    /**
     * @cfg {String} monthText
     * Alternate text to use for the 'Month' nav bar button.
     */
CODE;
    $string .= "monthText: '" . $MONTH . "',";
    $string .= <<<CODE

    // private
    layoutConfig: {
        layoutOnCardChange: true,
        deferredRender: true
    },

    // private property
    startDate: new Date(),

    // private
    initComponent: function() {
        this.tbar = {
            cls: 'ext-cal-toolbar',
            border: true,
            buttonAlign: 'center',
            items: [{
                id: this.id + '-tb-prev',
                handler: this.onPrevClick,
                scope: this,
                iconCls: 'x-tbar-page-prev'
            }]
        };

        this.viewCount = 0;

        if (this.showDayView) {
            this.tbar.items.push({
                id: this.id + '-tb-day',
                text: this.dayText,
                hidden : true,
                disabled : true,
                handler: this.onDayClick,
                scope: this,
                toggleGroup: 'tb-views'
            });
            this.viewCount++;
        }
        if (this.showWeekView) {
            this.tbar.items.push({
                id: this.id + '-tb-week',
                text: this.weekText,
                handler: this.onWeekClick,
                scope: this,
                toggleGroup: 'tb-views'
            });
            this.viewCount++;
        }
        if (this.showMonthView || this.viewCount == 0) {
            this.tbar.items.push({
                id: this.id + '-tb-month',
                text: this.monthText,
                handler: this.onMonthClick,
                scope: this,
                toggleGroup: 'tb-views'
            });
            this.viewCount++;
            this.showMonthView = true;
        }
        this.tbar.items.push({
            id: this.id + '-tb-next',
            handler: this.onNextClick,
            scope: this,
            iconCls: 'x-tbar-page-next'
        });
        this.tbar.items.push('->');

        var idx = this.viewCount - 1;
        this.activeItem = this.activeItem === undefined ? idx: (this.activeItem > idx ? idx: this.activeItem);

        if (this.showNavBar === false) {
            delete this.tbar;
            this.addClass('x-calendar-nonav');
        }

        Ext.calendar.CalendarPanel.superclass.initComponent.call(this);

        this.addEvents({
            /**
             * @event eventadd
             * Fires after a new event is added to the underlying store
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was added
             */
            eventadd: true,
            /**
             * @event eventupdate
             * Fires after an existing event is updated
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was updated
             */
            eventupdate: true,
            /**
             * @event eventdelete
             * Fires after an event is removed from the underlying store
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was removed
             */
            eventdelete: true,
            /**
             * @event eventcancel
             * Fires after an event add/edit operation is canceled by the user and no store update took place
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was canceled
             */
            eventcancel: true,
            /**
             * @event viewchange
             * Fires after a different calendar view is activated (but not when the event edit form is activated)
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.CalendarView} view The view being activated (any valid {@link Ext.calendar.CalendarView CalendarView} subclass)
             * @param {Object} info Extra information about the newly activated view. This is a plain object 
             * with following properties:<div class="mdetail-params"><ul>
             * <li><b><code>activeDate</code></b> : <div class="sub-desc">The currently-selected date</div></li>
             * <li><b><code>viewStart</code></b> : <div class="sub-desc">The first date in the new view range</div></li>
             * <li><b><code>viewEnd</code></b> : <div class="sub-desc">The last date in the new view range</div></li>
             * </ul></div>
             */
            viewchange: true

            //
            // NOTE: CalendarPanel also relays the following events from contained views as if they originated from this:
            //
            /**
             * @event eventsrendered
             * Fires after events are finished rendering in the view
             * @param {Ext.calendar.CalendarPanel} this 
             */
            /**
             * @event eventclick
             * Fires after the user clicks on an event element
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The {@link Ext.calendar.EventRecord record} for the event that was clicked on
             * @param {HTMLNode} el The DOM node that was clicked on
             */
            /**
             * @event eventover
             * Fires anytime the mouse is over an event element
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The {@link Ext.calendar.EventRecord record} for the event that the cursor is over
             * @param {HTMLNode} el The DOM node that is being moused over
             */
            /**
             * @event eventout
             * Fires anytime the mouse exits an event element
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The {@link Ext.calendar.EventRecord record} for the event that the cursor exited
             * @param {HTMLNode} el The DOM node that was exited
             */
            /**
             * @event datechange
             * Fires after the start date of the view changes
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Date} startDate The start date of the view (as explained in {@link #getStartDate}
             * @param {Date} viewStart The first displayed date in the view
             * @param {Date} viewEnd The last displayed date in the view
             */
            /**
             * @event rangeselect
             * Fires after the user drags on the calendar to select a range of dates/times in which to create an event
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Object} dates An object containing the start (StartDate property) and end (EndDate property) dates selected
             * @param {Function} callback A callback function that MUST be called after the event handling is complete so that
             * the view is properly cleaned up (shim elements are persisted in the view while the user is prompted to handle the
             * range selection). The callback is already created in the proper scope, so it simply needs to be executed as a standard
             * function call (e.g., callback()).
             */
            /**
             * @event eventmove
             * Fires after an event element is dragged by the user and dropped in a new position
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The {@link Ext.calendar.EventRecord record} for the event that was moved with
             * updated start and end dates
             */
            /**
             * @event initdrag
             * Fires when a drag operation is initiated in the view
             * @param {Ext.calendar.CalendarPanel} this
             */
            /**
             * @event eventresize
             * Fires after the user drags the resize handle of an event to resize it
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Ext.calendar.EventRecord} rec The {@link Ext.calendar.EventRecord record} for the event that was resized
             * containing the updated start and end dates
             */
            /**
             * @event dayclick
             * Fires after the user clicks within a day/week view container and not on an event element
             * @param {Ext.calendar.CalendarPanel} this
             * @param {Date} dt The date/time that was clicked on
             * @param {Boolean} allday True if the day clicked on represents an all-day box, else false.
             * @param {Ext.Element} el The Element that was clicked on
             */
        });

        this.layout = 'card';
        // do not allow override
        if (this.showDayView) {
            var day = Ext.apply({
                xtype: 'dayview',
                title: this.dayText,
                showToday: this.showToday,
                showTodayText: this.showTodayText,
                showTime: this.showTime
            },
            this.dayViewCfg);

            day.id = this.id + '-day';
            day.store = day.store || this.eventStore;
            this.initEventRelay(day);
            this.add(day);
        }
        if (this.showWeekView) {
            var wk = Ext.applyIf({
                xtype: 'weekview',
                title: this.weekText,
                showToday: this.showToday,
                showTodayText: this.showTodayText,
                showTime: this.showTime
            },
            this.weekViewCfg);

            wk.id = this.id + '-week';
            wk.store = wk.store || this.eventStore;
            this.initEventRelay(wk);
            this.add(wk);
        }
        if (this.showMonthView) {
            var month = Ext.applyIf({
                xtype: 'monthview',
                title: this.monthText,
                showToday: this.showToday,
                showTodayText: this.showTodayText,
                showTime: this.showTime,
                listeners: {
                    'weekclick': {
                        fn: function(vw, dt) {
                            this.showWeek(dt);
                        },
                        scope: this
                    }
                }
            },
            this.monthViewCfg);

            month.id = this.id + '-month';
            month.store = month.store || this.eventStore;
            this.initEventRelay(month);
            this.add(month);
        }

        this.add(Ext.applyIf({
            xtype: 'eventeditform',
            id: this.id + '-edit',
            calendarStore: this.calendarStore,
            listeners: {
                'eventadd': {
                    scope: this,
                    fn: this.onEventAdd
                },
                'eventupdate': {
                    scope: this,
                    fn: this.onEventUpdate
                },
                'eventdelete': {
                    scope: this,
                    fn: this.onEventDelete
                },
                'eventcancel': {
                    scope: this,
                    fn: this.onEventCancel
                }
            }
        },
        this.editViewCfg));
    },

    // private
    initEventRelay: function(cfg) {
        cfg.listeners = cfg.listeners || {};
        cfg.listeners.afterrender = {
            fn: function(c) {
                // relay the view events so that app code only has to handle them in one place
                this.relayEvents(c, ['eventsrendered', 'eventclick', 'eventover', 'eventout', 'dayclick',
                'eventmove', 'datechange', 'rangeselect', 'eventdelete', 'eventresize', 'initdrag']);
            },
            scope: this,
            single: true
        };
    },

    // private
    afterRender: function() {
        Ext.calendar.CalendarPanel.superclass.afterRender.call(this);
        this.fireViewChange();
    },

    // private
    onLayout: function() {
        Ext.calendar.CalendarPanel.superclass.onLayout.call(this);
        if (!this.navInitComplete) {
            this.updateNavState();
            this.navInitComplete = true;
        }
    },

    // private
    onEventAdd: function(form, rec) {
        rec.data[Ext.calendar.EventMappings.IsNew.name] = false;
        this.eventStore.add(rec);
        this.hideEditForm();
        this.fireEvent('eventadd', this, rec);
    },

    // private
    onEventUpdate: function(form, rec) {
        rec.commit();
        this.hideEditForm();
        this.fireEvent('eventupdate', this, rec);
    },

    // private
    onEventDelete: function(form, rec) {
        this.eventStore.remove(rec);
        this.hideEditForm();
        this.fireEvent('eventdelete', this, rec);
    },

    // private
    onEventCancel: function(form, rec) {
        this.hideEditForm();
        this.fireEvent('eventcancel', this, rec);
    },

    /**
     * Shows the built-in event edit form for the passed in event record.  This method automatically
     * hides the calendar views and navigation toolbar.  To return to the calendar, call {@link #hideEditForm}.
     * @param {Ext.calendar.EventRecord} record The event record to edit
     * @return {Ext.calendar.CalendarPanel} this
     */
    showEditForm: function(rec) {
        this.preEditView = this.layout.activeItem.id;
        this.setActiveView(this.id + '-edit');
        this.layout.activeItem.loadRecord(rec);
        return this;
    },

    /**
     * Hides the built-in event edit form and returns to the previous calendar view. If the edit form is
     * not currently visible this method has no effect.
     * @return {Ext.calendar.CalendarPanel} this
     */
    hideEditForm: function() {
        if (this.preEditView) {
            this.setActiveView(this.preEditView);
            delete this.preEditView;
        }
        return this;
    },

    // private
    setActiveView: function(id) {
        var l = this.layout;
        l.setActiveItem(id);

        if (id == this.id + '-edit') {
            this.getTopToolbar().hide();
            this.doLayout();
        }
        else {
            l.activeItem.refresh();
            this.getTopToolbar().show();
            this.updateNavState();
        }
        this.activeView = l.activeItem;
        this.fireViewChange();
    },

    // private
    fireViewChange: function() {
        var info = null,
            view = this.layout.activeItem;

        if (view.getViewBounds) {
            vb = view.getViewBounds();
            info = {
                activeDate: view.getStartDate(),
                viewStart: vb.start,
                viewEnd: vb.end
            };
        };
        this.fireEvent('viewchange', this, view, info);
    },

    // private
    updateNavState: function() {
        if (this.showNavBar !== false) {
            var item = this.layout.activeItem,
            suffix = item.id.split(this.id + '-')[1];

            var btn = Ext.getCmp(this.id + '-tb-' + suffix);
            btn.toggle(true);
        }
    },

    /**
     * Sets the start date for the currently-active calendar view.
     * @param {Date} dt
     */
    setStartDate: function(dt) {
        this.layout.activeItem.setStartDate(dt, true);
        this.updateNavState();
        this.fireViewChange();
    },

    // private
    showWeek: function(dt) {
        this.setActiveView(this.id + '-week');
        this.setStartDate(dt);
    },

    // private
    onPrevClick: function() {
        this.startDate = this.layout.activeItem.movePrev();
        this.updateNavState();
        this.fireViewChange();
    },

    // private
    onNextClick: function() {
        this.startDate = this.layout.activeItem.moveNext();
        this.updateNavState();
        this.fireViewChange();
    },

    // private
    onDayClick: function() {
        this.setActiveView(this.id + '-day');
    },

    // private
    onWeekClick: function() {
        this.setActiveView(this.id + '-week');
    },

    // private
    onMonthClick: function() {
        this.setActiveView(this.id + '-month');
    },

    /**
     * Return the calendar view that is currently active, which will be a subclass of
     * {@link Ext.calendar.CalendarView CalendarView}.
     * @return {Ext.calendar.CalendarView} The active view
     */
    getActiveView: function() {
        return this.layout.activeItem;
    }
});

Ext.reg('calendarpanel', Ext.calendar.CalendarPanel);

CODE;
    return $string;
}

function jsEventEditWindow() {

    $TITLE = TITLE;
    $SAVE = SAVE;
    $CANCEL = CANCEL;
    $DELETE = DELETE;
    $WHEN = WHEN;
    $SAVING_CHANGES = SAVING_CHANGES;
    $DELETING_EVENT = DELETING_EVENT;
    $EDIT_EVENT = EDIT_MY_EVENT;
    $ADD_EVENT = ADD_MY_EVENT;

    $string = <<<CODE
/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.EventEditWindow
 * @extends Ext.Window
 * <p>A custom window containing a basic edit form used for quick editing of events.</p>
 * <p>This window also provides custom events specific to the calendar so that other calendar components can be easily
 * notified when an event has been edited via this component.</p>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.EventEditWindow = function(config) {
    var formPanelCfg = {
        xtype: 'form',
        labelWidth: 65,
        frame: false,
        bodyStyle: 'background:transparent;padding:5px 10px 10px;',
        bodyBorder: false,
        border: false,
        items: [{
            id: 'title',
            name: Ext.calendar.EventMappings.Title.name,
CODE;
    $string .= "fieldLabel: '" . $TITLE . "',";
    $string .= <<<CODE

            xtype: 'textfield',
            anchor: '100%',
            listeners :{
                'change' : function(text, newValue, oldValue ){
                    if(newValue != oldValue){
                        var button = Ext.getCmp("save-btn");
                        button.enable();
                    }
                }
            }
        },
        {
            xtype: 'daterangefield',
            id: 'date-range',
            anchor: '100%',
            
CODE;
    $string .= "fieldLabel: '" . $WHEN . "',";
    $string .= <<<CODE
            listeners :{
                'change' : function(text, newValue, oldValue ){
                    if(newValue != oldValue){
                        var button = Ext.getCmp("save-btn");
                        button.enable();
                    }
                }
            }
        }]
    };

    if (config.calendarStore) {
        this.calendarStore = config.calendarStore;
        delete config.calendarStore;

        formPanelCfg.items.push({
            xtype: 'calendarpicker',
            id: 'calendar',
            name: 'calendar',
            anchor: '100%',
            store: this.calendarStore,
            hidden: true,
            disabled :true,
            listeners :{
                'change' : function(text, newValue, oldValue ){
                    if(newValue != oldValue){
                        var button = Ext.getCmp("save-btn");
                        button.enable();
                    }
            }
        }
        });
    }

    Ext.calendar.EventEditWindow.superclass.constructor.call(this, Ext.apply({
CODE;
    $string .= "titleTextAdd: '" . $ADD_EVENT . "',";
    $string .= "titleTextEdit: '" . $EDIT_EVENT . "',";
    $string .= <<<CODE
        width: 600,
        autocreate: true,
        border: true,
        closeAction: "hide",
        modal: false,
        resizable: false,
        buttonAlign: 'left',
CODE;
    $string .= "savingMessage: '" . $SAVING_CHANGES . "',";
    $string .= "deletingMessage: '" . $DELETING_EVENT . "',";
    $string .= <<<CODE
        fbar: [{
            xtype: 'tbtext',
CODE;
    $string .= "text: '<a href=\"#\" id=\"tblink\">" . $EDIT_EVENT . "</a>'";
    $string .= <<<CODE

        },
        '->', {
CODE;
    $string .= "text: '" . $SAVE . "',";
    $string .= <<<CODE
            id : "save-btn",
            xtype: 'button',
            disabled: true,
            handler: this.onSave,
            scope: this
        },
        {
            id: 'delete-btn',
CODE;
    $string .= "text: '" . $DELETE . "',";
    $string .= <<<CODE
            disabled: false,
            xtype: 'button',
            handler: this.onDelete,
            scope: this,
            hideMode: 'offsets'
        },
        {
CODE;
    $string .= "text: '" . $CANCEL . "',";
    $string .= <<<CODE
            disabled: false,
            handler: this.onCancel,
            scope: this
        }],
        items: formPanelCfg
    },
    config));
};

Ext.extend(Ext.calendar.EventEditWindow, Ext.Window, {
    // private
    newId: 10000,
        listeners : {
            "hide" : function(){
                Ext.getCmp("save-btn").disable();
            }
        },
    // private
    initComponent: function() {
        Ext.calendar.EventEditWindow.superclass.initComponent.call(this);

        this.formPanel = this.items.items[0];

        this.addEvents({
            /**
             * @event eventadd
             * Fires after a new event is added
             * @param {Ext.calendar.EventEditWindow} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was added
             */
            eventadd: true,
            /**
             * @event eventupdate
             * Fires after an existing event is updated
             * @param {Ext.calendar.EventEditWindow} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was updated
             */
            eventupdate: true,
            /**
             * @event eventdelete
             * Fires after an event is deleted
             * @param {Ext.calendar.EventEditWindow} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was deleted
             */
            eventdelete: true,
            /**
             * @event eventcancel
             * Fires after an event add/edit operation is canceled by the user and no store update took place
             * @param {Ext.calendar.EventEditWindow} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was canceled
             */
            eventcancel: true,
            /**
             * @event editdetails
             * Fires when the user selects the option in this window to continue editing in the detailed edit form
             * (by default, an instance of {@link Ext.calendar.EventEditForm}. Handling code should hide this window
             * and transfer the current event record to the appropriate instance of the detailed form by showing it
             * and calling {@link Ext.calendar.EventEditForm#loadRecord loadRecord}.
             * @param {Ext.calendar.EventEditWindow} this
             * @param {Ext.calendar.EventRecord} rec The {@link Ext.calendar.EventRecord record} that is currently being edited
             */
            editdetails: true
        });
    },

    // private
    afterRender: function() {
        Ext.calendar.EventEditWindow.superclass.afterRender.call(this);

        this.el.addClass('ext-cal-event-win');

        Ext.get('tblink').on('click',
        function(e) {
            e.stopEvent();
            this.updateRecord();
            this.fireEvent('editdetails', this, this.activeRecord);
        },
        this);
    },

    /**
     * Shows the window, rendering it first if necessary, or activates it and brings it to front if hidden.
	 * @param {Ext.data.Record/Object} o Either a {@link Ext.data.Record} if showing the form
	 * for an existing event in edit mode, or a plain object containing a StartDate property (and 
	 * optionally an EndDate property) for showing the form in add mode. 
     * @param {String/Element} animateTarget (optional) The target element or id from which the window should
     * animate while opening (defaults to null with no animation)
     * @return {Ext.Window} this
     */
    show: function(o, animateTarget) {
        // Work around the CSS day cell height hack needed for initial render in IE8/strict:
        var anim = (Ext.isIE8 && Ext.isStrict) ? null: animateTarget;

        Ext.calendar.EventEditWindow.superclass.show.call(this, anim,
        function() {
            Ext.getCmp('title').focus(false, 100);
        });
        Ext.getCmp('delete-btn')[o.data && o.data[Ext.calendar.EventMappings.EventId.name] ? 'show': 'hide']();

        var rec,
        f = this.formPanel.form;

        if (o.data) {
            rec = o;
            this.isAdd = !!rec.data[Ext.calendar.EventMappings.IsNew.name];
            if (this.isAdd) {
                // Enable adding the default record that was passed in
                // if it's new even if the user makes no changes
                rec.markDirty();
                this.setTitle(this.titleTextAdd);
            }
            else {
                this.setTitle(this.titleTextEdit);
            }

            f.loadRecord(rec);
        }
        else {
            this.isAdd = true;
            this.setTitle(this.titleTextAdd);

            var M = Ext.calendar.EventMappings,
            eventId = M.EventId.name,
            start = o[M.StartDate.name],
            end = o[M.EndDate.name] || start.add('h', 1);

            rec = new Ext.calendar.EventRecord();
            rec.data[M.EventId.name] = this.newId++;
            rec.data[M.StartDate.name] = start;
            rec.data[M.EndDate.name] = end;
            rec.data[M.IsAllDay.name] = !!o[M.IsAllDay.name] || start.getDate() != end.clone().add(Date.MILLI, 1).getDate();
            rec.data[M.IsNew.name] = true;

            f.reset();
            f.loadRecord(rec);
        }

        if (this.calendarStore) {
            Ext.getCmp('calendar').setValue(rec.data[Ext.calendar.EventMappings.CalendarId.name]);
        }
        Ext.getCmp('date-range').setValue(rec.data);
        this.activeRecord = rec;

        return this;
    },

    // private
    roundTime: function(dt, incr) {
        incr = incr || 15;
        var m = parseInt(dt.getMinutes(), 10);
        return dt.add('mi', incr - (m % incr));
    },

    // private
    onCancel: function() {
        this.cleanup(true);
        Ext.getCmp("save-btn").disable();
        this.fireEvent('eventcancel', this);
    },

    // private
    cleanup: function(hide) {
        if (this.activeRecord && this.activeRecord.dirty) {
            this.activeRecord.reject();
        }
        delete this.activeRecord;

        if (hide === true) {
            // Work around the CSS day cell height hack needed for initial render in IE8/strict:
            //var anim = afterDelete || (Ext.isIE8 && Ext.isStrict) ? null : this.animateTarget;
            this.hide();
        }
    },

    // private
    updateRecord: function() {
        var f = this.formPanel.form,
        dates = Ext.getCmp('date-range').getValue(),
        M = Ext.calendar.EventMappings;

        f.updateRecord(this.activeRecord);
        this.activeRecord.set(M.StartDate.name, dates[0]);
        this.activeRecord.set(M.EndDate.name, dates[1]);
        this.activeRecord.set(M.IsAllDay.name, dates[2]);
        this.activeRecord.set(M.CalendarId.name, this.formPanel.form.findField('calendar').getValue());
    },

    // private
    onSave: function() {
        if (!this.formPanel.form.isValid()) {
            return;
        }
        this.updateRecord();

        if (!this.activeRecord.dirty) {
            this.onCancel();
            return;
        }
        Ext.getCmp("save-btn").disable();
        this.fireEvent(this.isAdd ? 'eventadd': 'eventupdate', this, this.activeRecord);
    },

    // private
    onDelete: function() {
        this.fireEvent('eventdelete', this, this.activeRecord);
    }
});

CODE;
    return $string;
}

function jsSchoolEventWindow() {

    $TITLE = NAME;
    $SUBJECT = SUBJECT;
    $START_DATE = START_DATE;
    $END_DATE = END_DATE;
    
    $EVENT = EVENTS;
    $SCHOOL_EVENT = SCHOOL_EVENTS;
    $EXAM = EXAM;
    $EXAM_CODE = EXAM_CODE;
    $ROOM  = ROOM;
    $GRADE = GRADE;
    $CLASS_EVENT = EVENT_CLASS;
    $string = <<<CODE
/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.EventEditWindow
 * @extends Ext.Window
 * <p>A custom window containing a basic edit form used for quick editing of events.</p>
 * <p>This window also provides custom events specific to the calendar so that other calendar components can be easily
 * notified when an event has been edited via this component.</p>
 * @constructor
 * @param {Object} config The config object
 */

function fmtDate(date_time, type) {
  if (date_time) {
    var dt = new Date(); 
    dt.setTime(date_time);
  if(type == 1)
    return dt.format('
CODE;
    $string .= setExtDatafieldFormat();
    $string .= <<<CODE
    ');
   if(type ==2)     
    return dt.format('H:i');
  }
};
CODE;
    $string .= "var subject = '" . $SUBJECT . "';";
    $string .= "var sc_event = '" . $SCHOOL_EVENT . "';";
    $string .= "var cl_event = '" . $CLASS_EVENT . "';";
    $string .= "var name = '" . $TITLE . "';";
    $string .= <<<CODE

Ext.calendar.SchoolEventWindow = function(config) {


    var formPanelCfg = {
        id : 'school_form',
        xtype: 'panel',
        layout: 'form',
        labelWidth: 65,
        frame: false,
        bodyStyle: 'background:transparent;padding:5px 10px 10px;',
        bodyBorder: false,
        border: false,
        items: [{
            id: 'sc_title',
            name: Ext.calendar.EventMappings.Title.name,
CODE;
    $string .= "fieldLabel: '" . $TITLE . "',";
    $string .= <<<CODE
            xtype: 'displayfield',
            labelStyle: 'width:100px;'
        },{
            xtype: 'displayfield',
            id: 'exam_code',
            name: Ext.calendar.EventMappings.ExamCode.name,
            style: { marginBottom: '5px'},
            labelStyle: 'width:100px;',
            hidden : true,
CODE;
    $string .= "fieldLabel: '" . $EXAM_CODE . "',";
    $string .= <<<CODE
        },{
            xtype: 'displayfield',
            id: 'room',
            name: Ext.calendar.EventMappings.Room.name,
            style: { marginBottom: '5px'},
            labelStyle: 'width:100px;',
            hidden : true,
CODE;
    $string .= "fieldLabel: '" . $ROOM . "',";
    $string .= <<<CODE
        },{
            xtype: 'displayfield',
            id: 'grade',
            name: Ext.calendar.EventMappings.Grade.name,
            style: { marginBottom: '5px'},
            labelStyle: 'width:100px;',
            hidden : true,
CODE;
    $string .= "fieldLabel: '" . $GRADE . "',";
    $string .= <<<CODE
        },{
            xtype: 'displayfield',
            id: 'start-date',
            name: Ext.calendar.EventMappings.StartDate.name,
            style: { marginBottom: '5px'},
            labelStyle: 'width:100px;',
CODE;
    $string .= "fieldLabel: '" . $START_DATE . "',";
    $string .= <<<CODE
        },{
            xtype: 'displayfield',
            id: 'end-date',
            name: Ext.calendar.EventMappings.EndDate.name,
            style: { marginBottom: '5px'},
            labelStyle: 'width:100px;',
CODE;
    $string .= "fieldLabel: '" . $END_DATE . "',";
    $string .= <<<CODE
        }]
    };

    Ext.calendar.SchoolEventWindow.superclass.constructor.call(this, Ext.apply({

CODE;
    $string .= "title: '" . $EVENT . "',";
    $string .= "titleSchoolEvent: '" . $SCHOOL_EVENT . "',";
    $string .= "titleClassEvent: '" . $CLASS_EVENT . "',";
    $string .= "titleExam: '" . $EXAM . "',";
    $string .= <<<CODE
        width: 600,
        autocreate: true,
        border: true,
        closeAction: "hide",
        modal: false,
        resizable: false,
        buttonAlign: 'left',
        items: formPanelCfg
    },
    config));
};

Ext.extend(Ext.calendar.SchoolEventWindow, Ext.Window, {
    // private
    newId: 10000,
        listeners : {
            "hide" : function(){
                Ext.getCmp("save-btn").disable();
            }
        },
    initComponent: function() {
        Ext.calendar.SchoolEventWindow.superclass.initComponent.call(this);
        this.formPanel = this.items.items[0];
    },
    // private
    afterRender: function() {
        Ext.calendar.SchoolEventWindow.superclass.afterRender.call(this);

        this.el.addClass('ext-cal-event-win');
    },

    /**
     * Shows the window, rendering it first if necessary, or activates it and brings it to front if hidden.
	 * @param {Ext.data.Record/Object} o Either a {@link Ext.data.Record} if showing the form
	 * for an existing event in edit mode, or a plain object containing a StartDate property (and 
	 * optionally an EndDate property) for showing the form in add mode. 
     * @param {String/Element} animateTarget (optional) The target element or id from which the window should
     * animate while opening (defaults to null with no animation)
     * @return {Ext.Window} this
     */
    show: function(o, animateTarget) {
        // Work around the CSS day cell height hack needed for initial render in IE8/strict:
        var anim = (Ext.isIE8 && Ext.isStrict) ? null: animateTarget;

        Ext.calendar.SchoolEventWindow.superclass.show.call(this, anim,
        function() {
            Ext.getCmp('title').focus(false, 100);
        });
        Ext.getCmp('delete-btn')[o.data && o.data[Ext.calendar.EventMappings.EventId.name] ? 'show': 'hide']();

        var rec,
        f = this.formPanel.form;

        if (o.data) {
            rec = o;
            switch(rec.data.CalendarId){
                case 2 : 
                    this.setTitle(this.titleSchoolEvent);
                    Ext.getCmp('sc_title').label.update(sc_event+':');
                    Ext.getCmp('exam_code').hide();
                    Ext.getCmp('room').hide();
                    Ext.getCmp('grade').hide();
                    break;
                case 3 : 
                    this.setTitle(this.titleClassEvent); 
                    Ext.getCmp('sc_title').label.update(cl_event+':');
                    Ext.getCmp('exam_code').hide();
                    Ext.getCmp('room').hide();
                    Ext.getCmp('grade').hide();
                    break;
                case 4 : 
                    this.setTitle(this.titleExam); 
                    Ext.getCmp('sc_title').label.update(subject +':');
                    if(rec.data[Ext.calendar.EventMappings.ExamCode.name]){
                        Ext.getCmp('exam_code').show(); 
                        Ext.getCmp('exam_code').setValue(rec.data[Ext.calendar.EventMappings.ExamCode.name]);
                    }
                    if(rec.data[Ext.calendar.EventMappings.Room.name]){
                        Ext.getCmp('room').show();
                        Ext.getCmp('room').setValue(rec.data[Ext.calendar.EventMappings.Room.name]);
                    }
                    if(rec.data[Ext.calendar.EventMappings.Grade.name]){
                        Ext.getCmp('grade').show();
                        Ext.getCmp('grade').setValue(rec.data[Ext.calendar.EventMappings.Grade.name]);
                    }
                    break;
                default : break;

            }

            Ext.getCmp('sc_title').setValue(rec.data[Ext.calendar.EventMappings.Title.name]);
            Ext.getCmp('start-date').setValue(fmtDate(rec.data.StartDate,2) + ' - ' + fmtDate(rec.data.StartDate, 1));
            Ext.getCmp('end-date').setValue( fmtDate(rec.data.EndDate,2) + ' - ' + fmtDate(rec.data.EndDate, 1));

            //this.loadRecord(rec);
        }
        else {
            this.isAdd = true;

            var M = Ext.calendar.EventMappings,
            eventId = M.EventId.name,
            start = o[M.StartDate.name],
            end = o[M.EndDate.name] || start.add('h', 1);

            rec = new Ext.calendar.EventRecord();
            rec.data[M.EventId.name] = this.newId++;
            rec.data[M.StartDate.name] = start;
            rec.data[M.EndDate.name] = end;
            rec.data[M.IsAllDay.name] = !!o[M.IsAllDay.name] || start.getDate() != end.clone().add(Date.MILLI, 1).getDate();
            rec.data[M.IsNew.name] = true;

            f.reset();
            //f.loadRecord(rec);
        }

        if (this.calendarStore) {
            Ext.getCmp('calendar').setValue(rec.data[Ext.calendar.EventMappings.CalendarId.name]);
        }
        this.activeRecord = rec;

        return this;
    },
    // private
    onCancel: function() {
        this.cleanup(true);
        Ext.getCmp("save-btn").disable();
        this.fireEvent('eventcancel', this);
    },
});

CODE;
    return $string;
}

function jsEventEditForm() {

    $SAVE = SAVE;
    $CANCEL = CANCEL;
    $DELETE = DELETE;

    $ADD_EVENT = ADD_MY_EVENT;
    $EDIT_EVENT = EDIT_EVENT;
    $EVENT_FORM = EVENT_FORM;

    $TITLE = TITLE;
    $WHEN = TIME;

    $NOTES = NOTE;
    $LOCATION = LOCATION;

    $WEB_LINK = WEB_LINK;

    $string = <<<CODE
/*!
 * Ext JS Library 3.3.1
 * Copyright(c) 2006-2010 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * @class Ext.calendar.EventEditForm
 * @extends Ext.form.FormPanel
 * <p>A custom form used for detailed editing of events.</p>
 * <p>This is pretty much a standard form that is simply pre-configured for the options needed by the
 * calendar components. It is also configured to automatically bind records of type {@link Ext.calendar.EventRecord}
 * to and from the form.</p>
 * <p>This form also provides custom events specific to the calendar so that other calendar components can be easily
 * notified when an event has been edited via this component.</p>
 * <p>The default configs are as follows:</p><pre><code>
    labelWidth: 65,
    title: 'Event Form',
    titleTextAdd: 'Add Event',
    titleTextEdit: 'Edit Event',
    bodyStyle: 'background:transparent;padding:20px 20px 10px;',
    border: false,
    buttonAlign: 'center',
    autoHeight: true,
    cls: 'ext-evt-edit-form',
</code></pre>
 * @constructor
 * @param {Object} config The config object
 */
Ext.calendar.EventEditForm = Ext.extend(Ext.form.FormPanel, {
    labelWidth: 65,

CODE;
    $string .= "title: '" . $EVENT_FORM . "',";
    $string .= "titleTextAdd: '" . $ADD_EVENT . "',";
    $string .= "titleTextEdit: '" . $EDIT_EVENT . "',";
    $string .= <<<CODE

    bodyStyle: 'background:transparent;padding:20px 20px 10px;',
    border: false,
    buttonAlign: 'center',
    autoHeight: true,
    // to allow for the notes field to autogrow
    cls: 'ext-evt-edit-form',

    // private properties:
    newId: 10000,
    layout: 'column',

    // private
    initComponent: function() {

        this.addEvents({
            /**
             * @event eventadd
             * Fires after a new event is added
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was added
             */
            eventadd: true,
            /**
             * @event eventupdate
             * Fires after an existing event is updated
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was updated
             */
            eventupdate: true,
            /**
             * @event eventdelete
             * Fires after an event is deleted
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was deleted
             */
            eventdelete: true,
            /**
             * @event eventcancel
             * Fires after an event add/edit operation is canceled by the user and no store update took place
             * @param {Ext.calendar.EventEditForm} this
             * @param {Ext.calendar.EventRecord} rec The new {@link Ext.calendar.EventRecord record} that was canceled
             */
            eventcancel: true
        });

        this.titleField = new Ext.form.TextField({      
CODE;
    $string .= "fieldLabel: '" . $TITLE . "',";
    $string .= <<<CODE
            name: Ext.calendar.EventMappings.Title.name,
            anchor: '90%',
            listeners :{
                'change' : function(text, newValue, oldValue ){
                    if(newValue != oldValue){
                        var button = Ext.getCmp("save-btn");
                        button.enable();
                    }
                }
            }
        });
        this.dateRangeField = new Ext.calendar.DateRangeField({
CODE;
    $string .= "fieldLabel: '" . $WHEN . "',";
    $string .= <<<CODE
            anchor: '90%'
                
        });
        
        this.notesField = new Ext.form.TextArea({
CODE;
    $string .= "fieldLabel: '" . $NOTES . "',";
    $string .= <<<CODE
            name: Ext.calendar.EventMappings.Notes.name,
            grow: true,
            growMax: 150,
            anchor: '100%'
        });

        var leftFields = [this.titleField, this.dateRangeField],
        rightFields = [this.notesField];

this.items = [{
            id: 'left-col',
            columnWidth: 0.65,
            layout: 'form',
            border: false,
            items: leftFields
        },
        {
            id: 'right-col',
            columnWidth: 0.35,
            layout: 'form',
            border: false,
            items: rightFields
        }];

        this.fbar = [{
CODE;
    $string .= "text: '" . $SAVE . "',";
    $string .= <<<CODE
            xtype: 'button',
            scope: this,
            id: 'save-btn',
            handler: this.onSave,
            disabled: true
        },
        {
            cls: 'ext-del-btn',
CODE;
    $string .= "text: '" . $DELETE . "',";
    $string .= <<<CODE
            xtype: 'button',
            scope: this,
            handler: this.onDelete
        },
        {
CODE;
    $string .= "text: '" . $CANCEL . "',";
    $string .= <<<CODE
            scope: this,
            handler: this.onCancel
        }];

        Ext.calendar.EventEditForm.superclass.initComponent.call(this);
    },

    // inherited docs
    loadRecord: function(rec) {
        this.form.loadRecord.apply(this.form, arguments);
        this.activeRecord = rec;
        this.dateRangeField.setValue(rec.data);
        if (this.calendarStore) {
            this.form.setValues({
                'calendar': rec.data[Ext.calendar.EventMappings.CalendarId.name]
            });
        }
        this.isAdd = !!rec.data[Ext.calendar.EventMappings.IsNew.name];
        if (this.isAdd) {
            rec.markDirty();
            this.setTitle(this.titleTextAdd);
            Ext.select('.ext-del-btn').setDisplayed(false);
        }
        else {
            this.setTitle(this.titleTextEdit);
            Ext.select('.ext-del-btn').setDisplayed(true);
        }
        this.titleField.focus();
    },

    // inherited docs
    updateRecord: function() {
        var dates = this.dateRangeField.getValue();

        this.form.updateRecord(this.activeRecord);
        this.activeRecord.set(Ext.calendar.EventMappings.StartDate.name, dates[0]);
        this.activeRecord.set(Ext.calendar.EventMappings.EndDate.name, dates[1]);
        this.activeRecord.set(Ext.calendar.EventMappings.IsAllDay.name, dates[2]);
    },

    // private
    onCancel: function() {
        this.cleanup(true);
        Ext.getCmp("save-btn").disable();
        this.fireEvent('eventcancel', this, this.activeRecord);
    },

    // private
    cleanup: function(hide) {
        if (this.activeRecord && this.activeRecord.dirty) {
            this.activeRecord.reject();
        }
        delete this.activeRecord;

        if (this.form.isDirty()) {
            this.form.reset();
        }
    },

    // private
    onSave: function() {
        if (!this.form.isValid()) {
            return;
        }
        this.updateRecord();

        if (!this.activeRecord.dirty) {
            this.onCancel();
            return;
        }
        Ext.getCmp("save-btn").disable();
        this.fireEvent(this.isAdd ? 'eventadd': 'eventupdate', this, this.activeRecord);
    },

    // private
    onDelete: function() {
        this.fireEvent('eventdelete', this, this.activeRecord);
    }
});

Ext.reg('eventeditform', Ext.calendar.EventEditForm);

CODE;
    return $string;
}

?>
