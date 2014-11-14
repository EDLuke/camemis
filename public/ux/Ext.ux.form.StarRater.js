/*!
 * Ext.ux.form.StarRater
 * Copyright(c) 2010 Tobias Uhlig
 * uhlig@extthemes.com
 * license: GPL
 */

Ext.ns("Ext.ux.form");

Ext.ux.form.StarRater = Ext.extend(Ext.form.Field, {

    // public configs

     countVotings    : 0       // count of user-votings for this rater
    ,displayValue    : 0       // initialise overall voted-value
    ,maxValue        : 5       // default number of stars
    ,ratedValue      : 0       // initialise user-voting
    ,showResult      : true    // true:  show the result of the vote
    ,unit            : 20      // default size (px) of each star

    ,showResultImg   : false   // true:  show img for vote confirmation
    ,topText         : null    // default text above the stars
    ,bottomText      : null    // default text beneath the stars
    ,topHoverText    : null    // array with text when hovering over stars displayed above the stars
    ,bottomHoverText : null    // array with text when hovering over stars displayed beneath the stars

    // private configs
    ,rated : false // true after getting rated

    // css config
    ,wrapClass       : 'ux-form-rater-wrap'        // container class to hold rater
    ,starsClass      : 'ux-form-rater-stars'       // container class to hold stars
    ,hoverClass      : 'ux-form-rater-hover'       // class when star get hovered
    ,voteClass       : 'ux-form-rater-vote'        // class for displaying current rating
    ,votedClass      : 'ux-form-rater-voted'       // class for displaying user rated rating

    ,textTopClass    : 'ux-form-rater-text-top'    //class for top text container
    ,textBottomClass : 'ux-form-rater-text-bottom' //class for bottom text container

    ,autoSize: Ext.emptyFn

    ,initComponent : function(){
        Ext.ux.form.StarRater.superclass.initComponent.call(this);

        this.addEvents({
             beforeRate : true
            ,rate : true
        });
    }

    // private
    ,onRender : function(ct, position){

        Ext.ux.form.StarRater.superclass.onRender.call(this, ct, position);

        if (this.ratedValue > 0) {
            this.disabled = true;
        }

        this.wrap = this.el.wrap({cls: this.wrapClass});
        if (Ext.isIE) this.wrap.setHeight(this.unit); //fix for ie using in dynamic form
        this.el.addClass('x-hidden');

        this.createStars();
        this.createTextContainers();

        // correct out of bound values
        if (this.displayValue > this.maxValue) {
            this.displayValue = this.maxValue;
        }

        //display yellow stars
        if (this.displayValue > 0){
            this.displayRating();
        }

        if (this.ratedValue > 0) {
            this.showRatedImage();
        }
    },

    // private
    createStars : function(){
        if (this.getStars().getValue() > 0) {
            return;
        }

        var ul = this.wrap.createChild({
             cls : this.starsClass
            ,tag : 'ul'
        });
        ul.setSize(this.unit * this.maxValue, this.unit);

        ul.on('mouseover', this.onFocus, this); //maintain focus while hovering stars - useful in editor
        ul.on('click',     this.onFocus, this); //maintain focus while hovering stars - useful in editor
        ul.on('mouseout',  this.onBlur, this);  //blur when not hovering stars - useful in editor


        //append to rating container
        var tplr = new Ext.Template('<li class="rating"></li>'); //template for displaying the rating (yellow)
        var tpls = new Ext.Template('<li class="star"></li>');   //template for each rating (star)

        tplr.append(ul, [], true).setHeight(this.unit)           //append rating to its ul container

        this.stars = [];

        for (var i = this.maxValue; i > 0; i--){
            this.stars[i] = tpls.append(ul, [], true);       //append star to its ul container
            this.stars[i].setSize(this.unit * i, this.unit); //dimensions of the stars - declines in size, overlapping each other

            //attach events on the stars
            this.enableStar(this.stars[i]);
        }

        this.alignStars();
    },

    //private
    createTextContainers : function (){
        var ct = this.getStarsContainer();

        if (!this.textTopContainer)   this.textTopContainer    = Ext.DomHelper.insertBefore(ct, {tag:"div", cls:this.textTopClass},    true);
        if (!this.textBottomContainer)this.textBottomContainer = Ext.DomHelper.insertAfter( ct, {tag:"div", cls:this.textBottomClass}, true);

        //hide the containers on default
        this.textTopContainer   .addClass('x-hidden');
        this.textBottomContainer.addClass('x-hidden');

        //set the text
        if(this.topText)    this.setTopText(this.topText);
        if(this.bottomText) this.setBottomText(this.bottomText);

        //set the hover text - top
        if(this.topHoverText instanceof Array){
            var stars = this.getStars();
            for (var i = 0; i < stars.getValue(); i++){
                stars.item(i).on('mouseover', function(){this.setTopText(this.topHoverText[this.hoverValue-1])}, this, {delay:5} );        //delayed so hovervalue gets set
                stars.item(i).on('mouseout',  function(){this.setTopText(this.topText)}, this );
            }
        }

        //set the hover text - bottom
        if(this.bottomHoverText instanceof Array){
            var stars = this.getStars();
            for (var i = 0; i < stars.getValue(); i++){
                stars.item(i).on('mouseover', function(){this.setBottomText(this.bottomHoverText[this.hoverValue-1])}, this, {delay:5} );        //delayed so hovervalue gets set
                stars.item(i).on('mouseout',  function(){this.setBottomText(this.bottomText)}, this );
            }
        }
    },

    isRated : function(){
        return this.rated;
    },

    getTopText : function(){
        return this.textTopContainer.dom.innerHTML;
    },

    getBottomText : function(){
        return this.textBottomContainer.dom.innerHTML;
    },

    setTopText : function(t){
        this.textTopContainer.dom.innerHTML = t;
        (t == null || t == '') ? this.textTopContainer.addClass('x-hidden') : this.textTopContainer.removeClass('x-hidden') ;
    },

    setBottomText : function(t){
        this.textBottomContainer.dom.innerHTML = t;
        (t == null || t == '') ? this.textBottomContainer.addClass('x-hidden') : this.textBottomContainer.removeClass('x-hidden') ;
    },

    // private
    getStarsContainer : function(){
        return this.wrap.select('.'+this.starsClass, true).item(0);
    },

    // private
    getRating : function(){
        return this.wrap.select("li.rating", true);
    },

    // private
    getStars : function(){
        return this.wrap.select("li.star", true);
    },

    alignStars : function(){
        var ct         = this.getStarsContainer();
        var rating     = this.getRating();
        var stars      = this.getStars();
        var leftOffset = Ext.fly(document.body).getAlignToXY(ct)[0];        //left absolute positioning of rating and stars
        var isInForm   = (ct.findParent('.x-form-item', 5)) ? true : false; //used to fix weird aligning problem - dont't have a nice solution yet
        var isInEditor = (ct.findParent('.x-editor', 5))    ? true : false; //used to fix weird aligning problem - dont't have a nice solution yet

        if (!isInForm && !isInEditor) {                                     //left offset of the rating <li> (yellow stars)
            rating.setLeft(leftOffset);
            stars .setLeft(leftOffset);
        } else {
            rating.alignTo(ct, 'tl');
            stars .alignTo(ct, 'tl');
        }
    },

    // private
    displayHover : function(e){
        var target = Ext.get(e.getTarget()); //get originating element from the event
        target.addClass(this.hoverClass);    //add class show the hover stars

        var stars = this.getStars();
        var i = 0;

        //loop star till originating star to get the value and store it in hoverValue for later use
        while (stars.item(i) != null){
            if (stars.item(i) == target) {
                this.hoverValue = this.maxValue - i;
                return;
            }
            i++;
        }
    },

    // private
    displayRating : function(){
        var el = this.getRating();                             //get <li> for displaying the value (yellow star)

        if (this.__displayFinalRating && this.showResult) {
            el.setWidth(this.hoverValue * this.unit);         //set width according to the hovered value
            el.replaceClass(this.voteClass, this.votedClass);
            return;
        }
        el.setWidth(this.displayValue * this.unit);           //set width according to the value
        el.addClass(this.voteClass);                          //show yellow stars
    },

    // private
    rate : function (e) {
        this.fireEvent('beforeRate', this, false);

        this.setTopText(this.topText);       //revert to default text if set
        this.setBottomText(this.bottomText); //revert to default text if set

        this.__displayFinalRating = true;    //used in displayRating
        this.removeHover(e);
        this.removeListeners();
        this.onBlur();
        this.rated = true;
        this.el.dom.readOnly = true

        this.setValue(this.hoverValue);
        this.displayRating();

        this.fireEvent('rate', {
             name  : this.name
            ,value : this.hoverValue
        }, this);
    },

    // private
    removeHover : function(e){
        var el = e.getTarget();
        Ext.fly(el).removeClass(this.hoverClass);
    },

    // private
    removeListeners : function(){
        this.wrap.select("*", true).removeAllListeners();
    },

    // private
    onDisable : function(){
        Ext.ux.form.StarRater.superclass.onDisable.call(this);
        this.removeListeners();
    },

    // private
    enableStar : function(star){
        if (this.disabled != true){
            star.on('mouseover', this.displayHover, this);
            star.on('mouseout',  this.removeHover, this, {delay:5});    // delayed for anti-flicker
            star.on('click',     this.rate, this);
        }
    },

    // private
    enableAllStars : function(){
        for (var i = this.maxValue; i > 0; i--) {
            this.enableStar(this.stars[i])
        }
    },

    // public
    reInitializeStars : function (countVotings, displayValue, ratedValue) {
        this.countVotings = countVotings;
        this.displayValue = displayValue;
        this.ratedValue   = ratedValue;
        this.hideStars();

        if (this.ratedValue > 0) {
            this.disabled = true;
            this.removeListeners();
            this.showRatedImage();
        } else {
            this.disabled = false;
            this.enableAllStars();
            this.hideRatedImage();
        }
    },

    // private
    displayStars : function () {
        var el = this.getRating();

        el.replaceClass(this.votedClass, this.voteClass);

        el.setWidth(this.displayValue * this.unit, {
             duration : .7
            ,easing   : 'bounceOut'
            ,scope    : this
        });
    },

    // private
    hideStars : function () {
        var el = this.getRating();
        el.setWidth(0, {
             callback : this.displayStars
            ,duration : .7
            ,easing   : 'bounceIn'
            ,scope    : this
        });
    },

    // private
    hideRatedImage : function () {
        if (this.showResultImg != true)return;

        this.votedImg.switchOff({
             duration : .3
            ,easing   : 'easeIn'
            ,remove   : true
        });
        this.showResultImg = false;
    },

    // private
    renewRatedImage : function () {
        if (this.showResultImg != true)return;

        this.votedImg.switchOff({
             callback : this.showRatedImage
            ,duration : .3
            ,easing   : 'easeIn'
            ,remove   : true
            ,scope    : this
        });
        this.showResultImg = false;
    },

    // private
    showRatedImage : function () {
        if (this.showResultImg != false) {
            this.renewRatedImage();
            return;
        }

        var imgMarginLeft = this.maxValue * this.unit + 5;
        var imgMarginTop  = - 17;
        this.votedImg = Ext.DomHelper.insertAfter(this.getStarsContainer(),
            '<img id="img'+this.name+'" '+
            'style="position:absolute; margin-left:' + imgMarginLeft +'px; margin-top:' + imgMarginTop +'px; width:16px; height:16px" '+
            'src="../ux/accept.png" '+
            'ext:qtip="You voted '+this.ratedValue+' stars!" '+
            'ext:width="100">', true);
        this.showResultImg = true;
    }
});

Ext.reg('starRater', Ext.ux.form.StarRater);