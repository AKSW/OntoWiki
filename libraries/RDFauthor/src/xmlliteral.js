//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id:$
//

//
// XmlLiteralEdit – A literal widget for rich text saved as XHTML markup
//
// Author: Norman Heino <norman.heino@gmail.com>
//
function XmlLiteralEdit(graph, subject, predicate, object) {
    
    this.id = RDFauthor.getNextId();
    this.editorLoaded = false;
    
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object ? object.value : '';
    this.remove    = false;
}

// 
// XML literal datatype according to 
// http://www.w3.org/TR/rdf-concepts/#section-XMLLiteral 
//
XmlLiteralEdit.prototype.datatype = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral';

//
// Returns the widget's HTML code
//
XmlLiteralEdit.prototype.getHtml = function () {
    var html = '\
        <div class="container xmlliteral-value">\
            <textarea id="' + this.id + '" style="width:38em;height:5.4em;visibility:hidden">' + this.object + '</textarea>\
        </div>';
    
    return html;
};

//
// Fetches the nicEditor instance for a given textarea
//
XmlLiteralEdit.prototype.initDisplay = function() {
    if (!this.editorLoaded) {
        // alert($('#' + this.id).height());
        var editorOptions = {
            iconsPath: widgetBase + 'libraries/nicEditorIcons.gif', 
            fullPanel: true, 
            xhtml: true
        };

        var editor = new nicEditor(editorOptions).panelInstance('' + this.id);
        
        this.editorLoaded = true;
    }
};

//
// Nothing to do when cancelling
//
XmlLiteralEdit.prototype.onCancel = function () {
    return true;
};

//
// Switches into remove mode.
//
XmlLiteralEdit.prototype.onRemove = function () {
    this.remove = true;
};

//
// Removes the old triple from the databank.
// if not in remove mode, adds a triple with the current values.
//
XmlLiteralEdit.prototype.onSubmit = function () {
    var dataBank = RDFauthor.getDatabank(this.graph);
    var remove   = this.remove;
    
    if (!this.remove) {
        var newValue = nicEditors.findEditor('' + this.id).getContent();
        remove = (newValue != this.object);
    }
    
    if (remove) {
        // remove old triple
        if (this.object !== '' || this.remove) {
            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(this.object, {datatype: XmlLiteralEdit.prototype.datatype})
            );

            dataBank.remove(oldTriple);
        }
        
        // nothing to save in remove mode
        if ((this.subject !== '') && !this.remove) {
            // add new triple
            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(newValue, {datatype: XmlLiteralEdit.prototype.datatype})
            );

            dataBank.add(newTriple);
        }
    }
    
    return true;
};

// ----------------------------------------------------------------------------

// load niEditor script
RDFauthor.loadScript(widgetBase + 'libraries/nicEdit.js');

// registration options
var options = {
    constructorFunction: XmlLiteralEdit, 
    hookName: 'datatype', 
    hookValues: [XmlLiteralEdit.prototype.datatype]
};

// register with RDFauthor
RDFauthor.registerWidget(options);