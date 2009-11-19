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
    
    this.container  = options.container instanceof jQuery ? options.container : $(options.container);
    this.id         = this.options.id;
    
    function getChrome(id, content) {
        var html = '<div class="property-selector container" id="' + id + '" style="display:none">' + content + '</div>';
        
        return html;
    }
    
    RDFauthorPropertySelector.prototype.getHtml = function (content) {
        content = content || null;
        
        return getChrome(this.id, content);
    };
}

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
    
    var widget = new ResourceEdit(graph, subject, null, null, {propertyMode: true});
    
    // prepend if necessary
    if (this.container.find(cssId).length < 1) {
        var content = widget.getHtml();
        this.container.append(this.getHtml(content));
        
        // HACK: widget-specific
        if (typeof widget.init == 'function') {
            widget.init();
        }
    }
    
    if (animated) {
        $(cssId).slideDown(this.options.animationTime);
    } else {
        $(cssId).show();
    }
    
    // scroll the container to the top, so property selector is visible
    var selectorTop  = $(cssId).offset().top;
    var containerTop = this.container.offset().top;
    this.container.animate({scrollTop: (selectorTop - containerTop) + 'px'}, 0);
    
    // HACK: depends on widget
    $('#resource-value-' + widget.id).focus();
    var instance = this;
    $('#resource-value-' + widget.id).keypress(function(event) {
        if (event.which == 13) {
            var value = widget.getValue();
            instance.currentUri   = value.uri;
            instance.currentTitle = value.title;
            // instance.dismiss(false);
            instance.options.callback();
        }
    });
};

RDFauthorPropertySelector.prototype.selectedProperty = function () {
    return this.currentUri;
};

RDFauthorPropertySelector.prototype.selectedPropertyTitle = function () {
    return this.currentTitle;
};