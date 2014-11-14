<?

require_once setUserLoacalization();

class ExtjsLocalization {

    static function render() {

        $js = "";

        $js .= "Ext.UpdateManager.defaults.indicatorText = '<div class=\"loading-indicator\">" . LOADING_IND . "</div>';";

        $js .= "if(Ext.DataView){";
        $js .= "Ext.DataView.prototype.emptyText = \"\";";
        $js .= "}";

        $js .= "if(Ext.grid.GridPanel){";
        $js .= "Ext.grid.GridPanel.prototype.ddText = \"{0} selected row{1}\";";
        $js .= "}";

        $js .= "if(Ext.LoadMask){";
        $js .= "Ext.LoadMask.prototype.msg = \"" . LOADING . "\";";
        $js .= "}";

        $js .= "Date.monthNames = [";
        $js .= "\"" . JANUARY . "\",";
        $js .= "\"" . FEBRUARY . "\",";
        $js .= "\"" . MARCH . "\",";
        $js .= "\"" . APRIL . "\",";
        $js .= "\"" . MAY . "\",";
        $js .= "\"" . JUNE . "\",";
        $js .= "\"" . JULY . "\",";
        $js .= "\"" . AUGUST . "\",";
        $js .= "\"" . SEPTEMBER . "\",";
        $js .= "\"" . OCTOBER . "\",";
        $js .= "\"" . NOVEMBER . "\",";
        $js .= "\"" . DECEMBER . "\"";
        $js .= "];";

        $js .= "Date.getShortMonthName = function(month) {";
        $js .= "return Date.monthNames[month].substring(0, 3);";
        $js .= "};";

        $js .= "Date.monthNumbers = {";
        $js .= "Jan : 0,";
        $js .= "Feb : 1,";
        $js .= "Mar : 2,";
        $js .= "Apr : 3,";
        $js .= "May : 4,";
        $js .= "Jun : 5,";
        $js .= "Jul : 6,";
        $js .= "Aug : 7,";
        $js .= "Sep : 8,";
        $js .= "Oct : 9,";
        $js .= "Nov : 10,";
        $js .= "Dec : 11";
        $js .= "};";

        $js .= "Date.getMonthNumber = function(name) {";
        $js .= "return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];";
        $js .= "};";

        $js .= "Date.dayNames = [";
        $js .= "\"" . SU . "\",";
        $js .= "\"" . MO . "\",";
        $js .= "\"" . TU . "\",";
        $js .= "\"" . WE . "\",";
        $js .= "\"" . TH . "\",";
        $js .= "\"" . FR . "\",";
        $js .= "\"" . SA . "\"";
        $js .= "];";

        $js .= "Date.getShortDayName = function(day) {";
        $js .= "return Date.dayNames[day].substring(0, 3);";
        $js .= "};";

        $js .= "Date.parseCodes.S.s = \"(?:st|nd|rd|th)\";";

        $js .= "if(Ext.MessageBox){";
        $js .= "Ext.MessageBox.buttonText = {";
        $js .= "ok: \"" . OK . "\",";
        $js .= "cancel: \"" . CANCEL . "\",";
        $js .= "yes: \"" . YES . "\",";
        $js .= "no: \"" . NO . "\"";
        $js .= "};";
        $js .= "}";

        $js .= "if(Ext.util.Format){";
        $js .= "Ext.util.Format.date = function(v, format){";
        $js .= "if(!v) return \"\";";
        $js .= "if(!(v instanceof Date)) v = new Date(Date.parse(v));";
        $js .= "return v.dateFormat(format || \"m/d/Y\");";
        $js .= "};";
        $js .= "}";

        $js .= "if(Ext.DatePicker){";
        $js .= "Ext.apply(Ext.DatePicker.prototype, {";
        $js .= "todayText: \"" . TODAY . "\",";
        $js .= "minText: \"" . DATE_IS_BEFORE_MINIMUM_DATE . "\",";
        $js .= "maxText: \"" . DATE_IS_AFTER_MAXIMUM_DATE . "\",";
        $js .= "disabledDaysText  : \"\",";
        $js .= "disabledDatesText : \"\",";
        $js .= "monthNames: Date.monthNames,";
        $js .= "dayNames: Date.dayNames,";
        $js .= "nextText: '" . NEXT_MONTH_CONTROL_RIGHT . "',";
        $js .= "prevText: '" . PREVIOUS_MONTH_CONTROL_LEFT . "',";
        $js .= "monthYearText: '" . CHOOSE_A_MONTH . "',";
        $js .= "todayTip: \"{0} " . SPACEBAR . "\",";
        $js .= "format: \"m/d/y\",";
        $js .= "okText: \"&#160;" . OK . "&#160;\",";
        $js .= "cancelText: \"" . CANCEL . "\",";
        $js .= "startDay: 0";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.PagingToolbar){";
        $js .= "Ext.apply(Ext.PagingToolbar.prototype, {";
        $js .= "beforePageText:\"" . PAGE . "\",";
        $js .= "afterPageText:\"" . OF . " {0}\",";
        $js .= "firstText:\"" . FIRST_PAGE . "\",";
        $js .= "prevText:\"" . PREVIOUS_PAGE . "\",";
        $js .= "nextText:\"" . NEXT_PAGE . "\",";
        $js .= "lastText:\"" . LAST_PAGE . "\",";
        $js .= "refreshText:\"" . REFRESH . "\",";
        $js .= "displayMsg:\"" . DISPLAYING . " {0} - {1} " . OF . " {2}\",";
        $js .= "emptyMsg: '" . NO_DATA_TO_DISPLAY . "'";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.BasicForm){";
        $js .= "Ext.form.BasicForm.prototype.waitTitle = \"" . PLEASE_WAIT . "\"";
        $js .= "}";

        $js .= "if(Ext.form.Field){";
        $js .= "Ext.form.Field.prototype.invalidText = \"" . VALUE_IS_INVALID . "\";";
        $js .= "}";

        $js .= "if(Ext.form.TextField){";
        $js .= "Ext.apply(Ext.form.TextField.prototype, {";
        $js .= "minLengthText:\"" . MINIMUM_LENGTH_IS . " {0}\",";
        $js .= "maxLengthText:\"" . MAXIMUM_LENGTH_IS . " {0}\",";
        $js .= "blankText:\"" . THIS_FIELD_IS_REQIRED . "\",";
        $js .= "regexText:\"\",";
        $js .= "emptyText: null";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.NumberField){";
        $js .= "Ext.apply(Ext.form.NumberField.prototype, {";
        $js .= "decimalSeparator : \".\",";
        $js .= "decimalPrecision : 2,";
        $js .= "minText:\"" . MINIMUM_VALUE_IS . " {0}\",";
        $js .= "maxText:\"" . MAXIMUM_VALUE_IS . " {0}\",";
        $js .= "nanText:\"{0} " . IS_NOT_A_VALID_NUMBER . "\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.DateField){";
        $js .= "Ext.apply(Ext.form.DateField.prototype, {";
        $js .= "disabledDaysText: \"" . DISABLED_EXTJS . "\",";
        $js .= "disabledDatesText : \"" . DISABLED_EXTJS . "\",";
        $js .= "minText: \"" . DATE_MUST_BE_AFTER . " {0}\",";
        $js .= "maxText: \"" . DATE_MUST_BE_BEFORE . " {0}\",";
        $js .= "invalidText: \"{0} " . NOT_VALID_DATE_MUST_BE_IN_FORMAT . " {1}\",";
        $js .= "format: \"m/d/y\",";
        $js .= "altFormats: \"m/d/Y|m-d-y|m-d-Y|m/d|m-d|md|mdy|mdY|d|Y-m-d\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.ComboBox){";
        $js .= "Ext.apply(Ext.form.ComboBox.prototype, {";
        $js .= "loadingText: \"" . LOADING . "\",";
        $js .= "valueNotFoundText : undefined";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.VTypes){";
        $js .= "Ext.apply(Ext.form.VTypes, {";
        $js .= "emailText: '" . EMAIL_ADRESS_FORMAT . "',";
        $js .= "urlText: '" . URL_FORMAT . "',";
        $js .= "alphaText: '" . FIELD_SHOULD_CONTAIN_LETTERS . "',";
        $js .= "alphanumText: '" . FIELD_SHOULD_CONTAIN_LETTERS_NUMBERS . "'";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.HtmlEditor){";
        $js .= "Ext.apply(Ext.form.HtmlEditor.prototype, {";
        $js .= "createLinkText : 'Please enter the URL for the link:',";
        $js .= "buttonTips : {";
        $js .= "bold : {";
        $js .= "title: '" . BOLD_CTRL_B . "',";
        $js .= "text: '" . MAKE_THE_SELECTED_TEXT_BOLD . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "italic : {";
        $js .= "title: '" . ITALIC_CTRL_I . "',";
        $js .= "text: '" . MAKE_THE_SELECTED_TEXT_ITALIC . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "underline : {";
        $js .= "title: '" . UNDERLINE_CTRL_U . "',";
        $js .= "text: '" . UNDERLINE_THE_SELECTED_TEXT . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "increasefontsize : {";
        $js .= "title: '" . GROW_TEXT . "',";
        $js .= "text: '" . INCREASE_THE_FONT_SIZE . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "decreasefontsize : {";
        $js .= "title: '" . SHRINK_TEXT . "',";
        $js .= "text: '" . DECREASE_THE_FONT_SIZE . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "backcolor : {";
        $js .= "title: '" . TEXT_HIGHLIGHT_COLOR . "',";
        $js .= "text: '" . CHANGE_THE_BACKGROUND_COLOR_OF_THE_SELECTED_TEXT . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "forecolor : {";
        $js .= "title: '" . FONT_COLOR . "',";
        $js .= "text: '" . CHANGE_THE_COLOR_OF_THE_SELECTED_TEXT . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "justifyleft : {";
        $js .= "title: '" . ALIGN_TEXT_LEFT . "',";
        $js .= "text: '" . ALIGN_TEXT_TO_THE_LEFT . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "justifycenter : {";
        $js .= "title: '" . CENTER_TEXT . "',";
        $js .= "text: '" . CENTER_TEXT_IN_THE_EDITOR . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "justifyright : {";
        $js .= "title: '" . ALIGN_TEXT_RIGHT . "',";
        $js .= "text: '" . ALIGN_TEXT_TO_THE_RIGHT . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "insertunorderedlist : {";
        $js .= "title: '" . BULLET_LIST . "',";
        $js .= "text: '" . START_A_BULLETED_LIST . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "insertorderedlist : {";
        $js .= "title: '" . NUMBERED_LIST . "',";
        $js .= "text: '" . START_A_NUMBERED_LIST . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "createlink : {";
        $js .= "title: '" . HYPERLINK . "',";
        $js .= "text: '" . MAKE_THE_SELECTED_TEXT_A_HYPERLINK . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "},";
        $js .= "sourceedit : {";
        $js .= "title: '" . SOURCE_EDIT . "',";
        $js .= "text: '" . SWITCH_TO_SOURCE_EDITING_MODE . "',";
        $js .= "cls: 'x-html-editor-tip'";
        $js .= "}";
        $js .= "}";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.grid.GridView){";
        $js .= "Ext.apply(Ext.grid.GridView.prototype, {";
        $js .= "sortAscText  : \"" . SORT_ASCENDING . "\",";
        $js .= "sortDescText : \"" . SORT_DESCENDING . "\",";
        $js .= "columnsText  : \"" . COLUMNS . "\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.grid.GroupingView){";
        $js .= "Ext.apply(Ext.grid.GroupingView.prototype, {";
        $js .= "emptyGroupText: '(" . NONE . ")',";
        $js .= "groupByText:'" . GROUP_BY_THIS_FIELD . "',";
        $js .= "showGroupsText : '" . SHOW_IN_GROUPS . "'";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.grid.PropertyColumnModel){";
        $js .= "Ext.apply(Ext.grid.PropertyColumnModel.prototype, {";
        $js .= "nameText:\"" . NAME . "\",";
        $js .= "valueText:\"" . VALUE . "\",";
        $js .= "dateFormat:\"m/j/Y\",";
        $js .= "trueText:\"true\",";
        $js .= "falseText:\"false\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.grid.BooleanColumn){";
        $js .= "Ext.apply(Ext.grid.BooleanColumn.prototype, {";
        $js .= "trueText:\"true\",";
        $js .= "falseText:\"false\",";
        $js .= "undefinedText: '&#160;'";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.grid.NumberColumn){";
        $js .= "Ext.apply(Ext.grid.NumberColumn.prototype, {";
        $js .= "format : '0,000.00'";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.grid.DateColumn){";
        $js .= "Ext.apply(Ext.grid.DateColumn.prototype, {";
        $js .= "format : 'm/d/Y'";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.layout.BorderLayout && Ext.layout.BorderLayout.SplitRegion){";
        $js .= "Ext.apply(Ext.layout.BorderLayout.SplitRegion.prototype, {";
        $js .= "splitTip: \"" . DRAG_TO_RESIZE . "\",";
        $js .= "collapsibleSplitTip : \"" . DRAG_TO_RESIZE_DOUBLE_CLICK_TO_HIDE . "\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.TimeField){";
        $js .= "Ext.apply(Ext.form.TimeField.prototype, {";
        $js .= "minText:\"" . TIME_MUST_BE_EQUAL_OR_AFTER . " {0}\",";
        $js .= "maxText:\"" . TIME_MUST_BE_EQUAL_OR_BEFORE . " {0}\",";
        $js .= "invalidText : \"{0} " . IS_NOT_A_VALID_TIME . "\",";
        $js .= "format : \"g:i A\",";
        $js .= "altFormats : \"g:ia|g:iA|g:i a|g:i A|h:i|g:i|H:i|ga|ha|gA|h a|g a|g A|gi|hi|gia|hia|g|H\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.CheckboxGroup){";
        $js .= "Ext.apply(Ext.form.CheckboxGroup.prototype, {";
        $js .= " blankText : \"" . SELECT_AT_LEAST_ONE_ITEM_IN_GROUP . "\"";
        $js .= "});";
        $js .= "}";

        $js .= "if(Ext.form.RadioGroup){";
        $js .= "Ext.apply(Ext.form.RadioGroup.prototype, {";
        $js .= "blankText : \"" . SELECT_ONE_ITEM_IN_GROUP . "\"";
        $js .= "});";
        $js .= "}";

        return $js;
    }

}

?>
