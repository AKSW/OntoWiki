//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: propertyselector.js 3920 2009-08-04 21:35:42Z norman.heino $
//

//
// RDFauthorPropertySelector
//
// A view containing a property selection widget
// Author: Norman Heino <norman.heino@gmail.com>
//
function RDFauthorPropertySelector(options) {
    this.options = $.extend({
        container: 'body', 
        callback: function(uri, title) {}, 
        animationTime: 250, 
        id: 'rdfAuthorPropertySelector'
    }, options);
    
    this.container    = options.container instanceof jQuery ? options.container : $(options.container);
    this.id           = this.options.id;
    this.currentUri   = null;
    this.currentTitle = null;
}

RDFauthorPropertySelector.prototype.getHtml = function () {
    var html = '\
    <div class="property-selector container" id="' + this.id + '" style="display:none;width:42em">\
        <input type="text" class="text" id="property-value-' + this.id + '" style="width:100%"/>\
    </div>';
    
    return html;
};

RDFauthorPropertySelector.prototype.getValue = function () {
    return $('#property-value-' + this.id).val();
};

RDFauthorPropertySelector.prototype.init = function () {
    var instance = this;
    $('#property-value-' + this.id).autocomplete(function(term, cb) {
        return RDFauthorPropertySelector.search(term, cb, true); 
        }, {
            minChars: 3,
            delay: 1000,
            max: 20,
            formatItem: function(data, i, n, term) {
                return '<div style="overflow:hidden">\
                    <span style="white-space: nowrap;font-weight: bold">' + data[0] + '</span>\
                    <br />\
                    <span style="white-space: nowrap;font-size: 0.8em">' + data[1] + '</span>\
                    </div>';
                }
        });
    
    var instance = this;
    $('#property-value-' + this.id).result(function(e, data, formatted) {
        $(this).val(data[1]);
        instance.currentUri   = data[1];
        instance.currentTitle = data[0];
        instance.options.callback();
    });
    
    $('#property-value-' + this.id).keypress(function(e) {
        if (e.which == 13 /* return */) {
            instance.currentUri   = $(this).val();
            
            if (String(instance.currentUri).lastIndexOf('#') > -1) {
                instance.currentTitle = String(instance.currentUri).substr(String(instance.currentUri).lastIndexOf('#') + 1);
            } else {
                instance.currentTitle = String(instance.currentUri).substr(String(instance.currentUri).lastIndexOf('/') + 1);
            }
            
            instance.options.callback();
        }
    });
};

RDFauthorPropertySelector.prototype.dismiss = function (animated) {
    animated = animated || false;
    var cssId = '#' + this.id;
    
    if (this.container.find(cssId).length > 0) {
        if (animated) {
            $(cssId).slideUp(this.options.animationTime);
        } else {
            $(cssId).hide();
        }
        
        // clean up
        $(cssId).remove();
    }
};

RDFauthorPropertySelector.prototype.showInContainer = function (graph, subject, animated) {
    animated = animated || false;
    var cssId = '#' + this.id;
    
    // var widget = new ResourceEdit(graph, subject, null, null, {propertyMode: true});
    
    // append if necessary
    if (this.container.find(cssId).length < 1) {
        // var content = widget.getHtml();
        this.container.append(this.getHtml());
        
        this.init();
        
        // HACK: widget-specific
        // if (typeof widget.init == 'function') {
        //     widget.init();
        // }
    }
    
    /*
    if (animated) {
        $(cssId).slideDown(this.options.animationTime);
    } else {*/
        $(cssId).show();
    /*}*/
    
    // scroll the container to the top, so property selector is visible
    var selectorTop  = $(cssId).offset().top;
    /* var containerTop = this.container.offset().top; */
    this.container.animate({scrollTop: (selectorTop/* - containerTop*/)}, this.animationTime);
    
    // give input focus
    $('#property-value-' + this.id).focus();
};

RDFauthorPropertySelector.search = function(terms, callbackFunction, propertiesOnly)
{
    if (typeof _OWSESSION != 'undefined') {
        var url = urlBase + 'datagathering/search?q=' + encodeURIComponent(terms) + '&mode=1';
        $.getJSON(url, {}, function(data) {
            callbackFunction(data);
        });
    } else {
        RDFauthorPropertySelector.sindiceSearch(terms, callbackFunction);
    }
}

RDFauthorPropertySelector.prototype.selectedProperty = function () {
    return this.currentUri;
};

RDFauthorPropertySelector.prototype.selectedPropertyTitle = function () {
    return this.currentTitle;
};

RDFauthor.loadScript(widgetBase + 'libraries/jquery.autocomplete.js');
RDFauthor.loadStyleSheet(widgetBase + 'libraries/jquery.autocomplete.css');
