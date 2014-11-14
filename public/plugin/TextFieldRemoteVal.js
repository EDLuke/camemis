/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
Ext.form.TextFieldRemoteVal = function(config){
    Ext.form.TextFieldRemoteVal.superclass.constructor.call(this, config);
    if( this.urlRemoteVal ) {
        if( this.remoteValidation == 'onValidate' ) {
            this.on('valid', this.startRemoteVal.createDelegate(this));
        }else if( this.remoteValidation == 'onBlur' ) {
            this.on('blur', this.startRemoteVal.createDelegate(this));
        }
    }
};

Ext.extend(Ext.form.TextFieldRemoteVal, Ext.form.TextField, {
    remoteValidation: null, /* 'onValidate' or 'onBlur' */
    urlRemoteVal: null,
    timeout: 30,
    method: 'POST',
    badServerRespText: 'Error: bad server response during validation',
    badComText: 'Error: validation unavailable',

    // redefinition
    onRender : function(ct){
        Ext.form.TextFieldRemoteVal.superclass.onRender.call(this, ct);

        this.remoteCheckIcon = ct.createChild({
            tav:'div',
            cls:'x-form-remote-wait'
        });
        this.remoteCheckIcon.hide();
    },

    // private
    alignRemoteCheckIcon : function(){
        this.remoteCheckIcon.alignTo(this.el, 'tl-tr', [2, 2]);
    },

    // private
    getParams: function() {
        var tfp = (this.name||this.id)+'='+this.getValue();
        var p = (this.paramsRemoteVal?this.paramsRemoteVal:'');
        if(p){
            if(typeof p == "object")
                tfp += '&' + Ext.urlEncode(p);
            else if(typeof p == 'string' && p.length)
                tfp += '&' + p;
        }
        return tfp;
    },

    // public
    startRemoteVal: function() {
        var v = this.getValue();
        if( this.lastValue != v ) { // don't start a remote validation if the value doesn't change (getFocus/lostFocus for example)
            this.lastValue = v;
            if( this.transaction ) {
                this.abort();
            }
            this.alignRemoteCheckIcon();
            this.remoteCheckIcon.show();
            var params = this.getParams();
            this.transaction = Ext.lib.Ajax.request(
                this.method,
                this.urlRemoteVal + (this.method=='GET' ? '?' + params : ''),
                {
                    success: this.successRemoteVal,
                    failure: this.failureRemoteVal,
                    scope: this,
                    timeout: (this.timeout*1000)
                    },
                params);
        }
    },

    // public
    abort : function(){
        if(this.transaction){
            Ext.lib.Ajax.abort(this.transaction);
        }
    },

    // private
    successRemoteVal: function(response) {
        this.transaction = null;
        this.remoteCheckIcon.hide();
        var result = this.processResponse(response);
        if(result) {
            if(result.errors) {
                this.markInvalid(result.errors);
                this.isValid = false;
            }
        }else{
            this.markInvalid(this.badServerRespText);
            this.isValid = false;
        }
    },

    // private
    failureRemoteVal: function(response) {
        this.transaction = null;
        this.remoteCheckIcon.hide();
        this.markInvalid(this.badComText);
        this.isValid = false;
    },

    // private
    processResponse: function(response) {
        return (!response.responseText ? false : Ext.decode(response.responseText));
    }

});


