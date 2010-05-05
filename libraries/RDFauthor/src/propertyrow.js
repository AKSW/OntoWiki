//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: propertyrow.js 4272 2009-10-10 20:10:01Z norman.heino $
//

//
// RDFauthorPropertyRow
//
// Row object representing an editable property row
// Author: Norman Heino <norman.heino@gmail.com>
//
function RDFauthorPropertyRow(container, subject, property, title) {    
    // the subject for this row
    this.subject = subject;
    
    // the property this row operates on
    this.property = property;
    
    // the human-readable string representing the property 
    if (typeof title == 'string' && '' != title) {
        this.title = title;
    } else {
        if (String(property).lastIndexOf('#') > -1) {
            this.title = String(property).substr(String(property).lastIndexOf('#') + 1);
        } else {
            this.title = String(property).substr(String(property).lastIndexOf('/') + 1);
        }
    }
    
    // widget array
    this.widgets = {};
    
    this.eventsAdded = false;
    
    // stores the index to the widget array by widget CSS id
    this.widgetsById = {};
    this.widgetCount = 0;
    
    // increase the instance count and give this instance an ID
    this.id = RDFauthorPropertyRow.prototype.numberOfRows++;
    
    var instance = this;
    
    // local method that returns the basic HTML code for the row
    function getChrome() {
        var html = '\
            <div class="property-row" id="property-row-' + instance.id + '">\
                <fieldset>\
                    <legend>' + instance.title + '</legend>\
                </fieldset>\
            </div>';
        
        return html;
    }
    
    container.append(getChrome());
    
    // returns the widget HTML + widget chrome
    function getWidgetChrome(widgetId, widgetHtml) {
        var html = '\
            <div class="widget" id="widget-' + widgetId + '">\
                <div class="container actions">\
                    <a class="delete-button" title="Remove widget and data."></a>\
                    <a class="add-button" title="Add another widget of the same type."></a>\
                </div>\
                ' + widgetHtml + '\
                <hr style="clear:both; height:0; border:none" />\
            </div>';
        
        return html;
    }
    
    // object is optional (RDF/JSON)
    this.addWidget = function (object, graph, constructor) {
        var widgetInstance = null;
        
        if (typeof constructor == 'function') {
            widgetInstance = new constructor(graph, this.subject, this.property, object);
        } else {
            widgetInstance = RDFauthor.getWidgetForStatement(graph, this.subject, this.property, object);
        }
        
        var widgetId = RDFauthor.getNextId();
        
        if (null !== widgetInstance) {            
            var widgetHtml = getWidgetChrome(widgetId, widgetInstance.getHtml());
            var index = this.widgetCount++;
            
            // store widget-id index mapping
            this.widgets[index] = widgetInstance;
            this.widgetsById[widgetId] = index;
            
            $('#property-row-' + this.id).children('fieldset').append(widgetHtml);
            
            // process init request
            if (typeof widgetInstance.init == 'function') {
                widgetInstance.init();
            }
            
            // call initDisplay only, if PropertyRow has layout (height > 0)
            if (($('#property-row-' + this.id).height() > 0) && (typeof widgetInstance.initDisplay == 'function')) {
                widgetInstance.initDisplay();
            }
            
            // return widget's CSS id
            return 'widget-' + widgetId;
            // return widgetInstance;
        } else {
            $('#property-row-' + this.id).children('fieldset').append('<div class="container">No suitable widget found.</div>');
        }
    };
}

RDFauthorPropertyRow.prototype.eventsRegistered = false;

// Variable to keep track of the number of instances
RDFauthorPropertyRow.prototype.numberOfRows = 0;

RDFauthorPropertyRow.prototype.getWidgetForId = function(cssId) {
    var id = cssId.replace('widget-', '');
    return this.widgets[this.widgetsById[id]];
}

RDFauthorPropertyRow.prototype.initDisplay = function () {
    for (var i = 0; i < this.widgetCount; i++) {
        var widgetInstance = this.widgets[i];
        if ('initDisplay' in widgetInstance) {
            widgetInstance.initDisplay();
        }
    }
}

RDFauthorPropertyRow.prototype.removeWidget = function (index, id) {
    // remove one widget at position id
    var widgetInstance = this.widgets[index];
    widgetInstance.onRemove();
    // this.widgets[id] = null;
    $('#property-row-' + this.id).children('fieldset').children('#widget-' + id).remove();
};

RDFauthorPropertyRow.prototype.onCancel = function () {
    for (var i = 0; i < this.widgetCount; i++) {
        var widgetInstance = this.widgets[i];
        if (widgetInstance) {
            widgetInstance.onCancel();
        }
    }
};

RDFauthorPropertyRow.prototype.onSubmit = function () {
    var submitOk = true;
    
    for (var i = 0; i < this.widgetCount; i++) {
        var widgetInstance = this.widgets[i];
        
        if (widgetInstance) {
            submitOk = widgetInstance.onSubmit() && submitOk;
        }
    }
    
    return submitOk;
};

///////////////////////////////////////////////////////////////////////////////

// add generic jQuery events for widget action buttons
if (!RDFauthorPropertyRow.prototype.eventsRegistered) {
    // non-inline
    $('#rdfAuthorContainer .actions .delete-button').live('click', function(event) {
        if ($(this).closest('.property-row').length) {
            var rowId    = $(this).closest('.property-row').attr('id').replace('property-row-', '');
            var widgetId = $(this).closest('.widget').attr('id').replace('widget-', '');
            
            var view = RDFauthor.getView();
            var row  = view.getRow(rowId);
            
            var widgetIndex = row.widgetsById[widgetId];
            row.removeWidget(widgetIndex, widgetId);
        }
    });
    
    $('#rdfAuthorContainer .actions .add-button').live('click', function() {
        if ($(this).closest('.property-row').length) {
            var rowId    = $(this).closest('.property-row').attr('id').replace('property-row-', '');
            var widgetId = $(this).closest('.widget').attr('id').replace('widget-', '');
            
            var view = RDFauthor.getView();
            var row  = view.getRow(rowId);
            
            var widgetIndex = row.widgetsById[widgetId];
            var graph       = row.widgets[widgetIndex].graph;
            var constructor = row.widgets[widgetIndex].constructor;
            row.addWidget(null, graph, constructor);
        }
    });
    
    // inline
    $('.rdfAuthorInline .actions .delete-button').live('click', function(event) {
        if ($(this).closest('.property-row').length) {
            var viewId   = $(this).closest('.rdfAuthorInline').attr('id');
            var rowId    = $(this).closest('.property-row').attr('id').replace('property-row-', '');
            var widgetId = $(this).closest('.widget').attr('id').replace('widget-', '');
            
            var view = RDFauthor.getInlineView(viewId);
            var row  = view.getRow(rowId);
            
            var widgetIndex = row.widgetsById[widgetId];
            row.removeWidget(widgetIndex, widgetId);
        }
    });
    
    $('.rdfAuthorInline .actions .add-button').live('click', function() {
        if ($(this).closest('.property-row').length) {
            var viewId   = $(this).closest('.rdfAuthorInline').attr('id');
            var rowId    = $(this).closest('.property-row').attr('id').replace('property-row-', '');
            var widgetId = $(this).closest('.widget').attr('id').replace('widget-', '');
            
            var view = RDFauthor.getInlineView(viewId);
            var row  = view.getRow(rowId);
            
            var widgetIndex = row.widgetsById[widgetId];
            var graph       = row.widgets[widgetIndex].graph;
            var constructor = row.widgets[widgetIndex].constructor;
            row.addWidget(null, graph, constructor);
        }
    });
    
    RDFauthorPropertyRow.prototype.eventsRegistered = true;
}
