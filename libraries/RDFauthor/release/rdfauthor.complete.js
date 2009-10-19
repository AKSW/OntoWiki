//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: rdfauthor.complete.js 3920 2009-08-04 21:35:42Z norman.heino $
//

// pre-create RDFa namespace
var RDFA = RDFA || {};

// set widget base if not existing
var widgetBase = widgetBase || 'http://localhost/RDFauthor/';

// local load for faster loading
__RDFA_BASE = widgetBase + 'libraries/';

//
// RDFauthor
//
// Basic object managing and delegating editing tasks
// Author: Norman Heino <norman.heino@gmail.com>
//
RDFauthor = {
    databanks: {}, 
    defaultGraph: null, 
    graphInfo: {}, 
    predicateInfo: {}, 
    tripleInfo: {}, 
    originalDatabanks: {}, 
    protectedTriples: {}, 
    
    idSeed: 123, 
    
    // instance has been initialized
    initialized: false,
     
    // the view object
    view: null, 
    
    // widget store
    widgetRegistry: {
        'resource':  {}, 
        'property':  {}, 
        'range':     {}, 
        'datatype':  {}, 
        '__object':  {},
        '__literal': {}, 
        '__default': {}
    }, 
    
    // options object
    options: {
        title: 'Title', 
        saveButtonTitle: 'saveButtonTitle', 
        cancelButtonTitle: 'cancelButtonTitle', 
        showButtons: true, 
        animated: true, 
        bulkMode: true
    }, 
    
    updateNs: 'http://ns.aksw.org/update/', 
    
    ignoreNs: ['http://www.w3.org/1999/xhtml/vocab#'], 
    
    internalPredicates: {
        defaultGraph:   'http://ns.aksw.org/update/defaultGraph', 
        sourceGraph:    'http://ns.aksw.org/update/sourceGraph', 
        queryEndpoint:  'http://ns.aksw.org/update/queryEndpoint', 
        updateEndpoint: 'http://ns.aksw.org/update/updateEndpoint'
    }, 
    
    updateEndpoint: null, 
    
    // optional widget methods
    optionalMethods: ['onRemove'], 
    
    pageParsed: false, 
    
    // mandatory widget methods
    requiredMethods: ['getHtml', 'init', 'onCancel', 'onSubmit'], 
    
    selectionCache: {}, 
    
    selectionCacheLoaded: false, 
    
    isInitialized: function () {
       return this.initialized;
    }, 
    
    cancelEditing: function () {
        var view = this.getView();
        // TODO: cancel editing
        view.hide(this.options.animated, function() {
            view.reset();
        });
    }, 
    
    cloneDatabanks: function () {
        var done = false;
        
        for (var graph in this.graphInfo) {
            if (graph in this.databanks && this.databanks[graph] instanceof $.rdf.databank) {
                var originalBank = $.rdf.databank();
                
                this.databanks[graph].triples().each(function() {
                    // HACK: reverse HTML escaping in literals
                    if (this instanceof $.rdf.literal) {
                        this.object.value = this.object.value.replace(/&lt;/, '<').replace(/&gt;/, '>');
                    }
                    originalBank.add(this);
                });
                
                this.originalDatabanks[graph] = originalBank;           
            } else {
                this.originalDatabanks[graph] = $.rdf.databank();
            }
        }
    }, 
    
    // fills the current view with property rows out of the databanks
    createPropertyView: function () {
        var view = this.getView();
        view.reset();
        
        if (this.options.bulkMode) {
            // for all graphs
            for (var graph in this.databanks) {
                // only add triples from editable graphs
                // i.e. for which an update endpoint has been defined
                if (graph in this.graphInfo && this.graphInfo[graph].updateEndpoint != 'undefined') {
                    // iterate over all triples
                    var triples = this.databanks[graph].triples();
                    // for (index in triples) {
                    for (var i = 0, length = triples.length; i < length; i++) {
                        var current = triples[i];
                         // build RDF/JSON object
                         var object = {};
                         if (current.object.type == 'uri') {
                             object.type  = 'uri';
                             object.value = current.object.value;
                         } else {
                             object.type  = 'literal';
                             object.value = current.object.value;

                             // typed literal
                             if (current.object.datatype) {
                                 object.datatype = current.object.datatype;
                             } else if (current.object.lang) {
                                 object.lang = current.object.lang;
                             }
                        }
                        
                        var predicateTitle = null;
                        if (current.property.value in this.predicateInfo) {
                            predicateTitle = this.predicateInfo[current.property.value].title;
                        }
                        view.addRow(current.subject.value, current.property.value, predicateTitle, object, graph);
                    }
                };
            }
        } else {
           // add click event to element/edit trigger
           // add triple to store
        }
        
        return view;
    }, 
    
    getDefaultGraph: function () {
        if (null === this.defaultGraph) {
            // get default graph + info
            var defaultGraph          = $('link[rel$=defaultGraph]').attr('href');
            var defaultQueryEndpoint  = $('link[about=' + defaultGraph + '][rel$=queryEndpoint]').attr('href');
            var defaultUpdateEndpoint = $('link[about=' + defaultGraph + '][rel$=updateEndpoint]').attr('href');
            
            // store default graph
            this.defaultGraph = defaultGraph;
            
            // store default graph info
            this.graphInfo[defaultGraph] = {};
            this.graphInfo[defaultGraph].queryEndpoint  = defaultQueryEndpoint;
            this.graphInfo[defaultGraph].updateEndpoint = defaultUpdateEndpoint;
        }
        
        return this.defaultGraph;
    }, 
    
    getDefaultResource: function () {
        if (this.options.defaultResource) {
            return this.options.defaultResource;
        }
        
        return null;
    },
    
    // returns a unique id to be used for element ID
    getNextId: function () {
        return this.idSeed++;
    }, 
    
    // returns a human-readable representation for a predicate URI
    getPredicateTitle: function (element, predicateUri) {
        // TODO: move OntoWiki-specific code to plug-in
        var title = $(element).closest('td').prev().children().eq(0).text();
        
        if (title != 'undefined') {
            return title;
        }
        
        return null;
    }, 
    
    // Returns the databank for the named graph specified
    getDatabank: function (graph) {
        graph = graph || this.getDefaultGraph();
        
        if (typeof this.databanks[graph] != 'object') {
            this.databanks[graph] = $.rdf.databank();
        }
        
        return this.databanks[graph];
    }, 
    
    // Creates and returns the RDFauthor view object used by this instance
    getView: function () {
        if (null === this.view) {
            if ($('.modal-wrapper').length < 1) {
                $('body').append('<div class="modal-wrapper" style="display:none"></div>');
            }
            
            var jModalWrapper = $('.modal-wrapper').eq(0);
            var instance = this;
            var options = $.extend(this.options, {
                beforeSubmit: function () {
                    // keep db before changes
                    instance.cloneDatabanks();
                }, 
                afterSubmit: function () {
                    instance.updateSources();
                    instance.view = null;
                }, 
                afterCancel: function () {
                    instance.cancelEditing();
                    if (typeof instance.options.onCancel == 'function') {
                        instance.options.onCancel();
                    }
                    instance.view = null;
                }, 
                container: jModalWrapper
            });
            // init view
            this.view = new RDFauthorView(options);
            RDFauthor.loadStyleSheet(widgetBase + 'src/rdfauthor.css');
        }
        
        return this.view;
    }, 
    
    // returns literal datatypes to be used by widgets
    getLiteralDatatypes: function () {
        var types = [];
        for (var t in $.typedValue.types) {
            types.push(t);
        }
        
        return types;
    }, 
    
    
    // returns languages to be used by widgets
    getLiteralLanguages: function () {
        return ['de', 'en', 'fr', 'cn'];
    }, 
    
    // returns the SPARQL service URI for a given triple
    getServiceUriForTriple: function (triple) {
       var tripleString  = triple.toString();
       var graph         = this.tripleInfo[tripleString];
       var queryEndpoint = this.graphInfo[graph].queryEndpoint;
       
       return queryEndpoint;
    }, 
    
    // returns a new widget instance that has been registered for hook
    // or null if no widget has been registered
    getWidgetForHook: function (hookName, hookValue, options) {
        options = $.extend({
            graph:     null, 
            subject:   null, 
            predicate: null, 
            object:    null
        }, options);
        
        var widgetConstructor = this.widgetRegistry[hookName][''];
        
        if (typeof widgetConstructor == 'function') {
            return new widgetConstructor(options.graph, options.subject, options.predicate, options.object);
        }
        
        return null;
    },
    
    // returns a new widget instance for the given statement
    getWidgetForStatement: function (graph, subject, predicate, object) {
        var widgetConstructor = null;
        
        // local widget selection
        if (subject in this.widgetRegistry.resource) {
            widgetConstructor = this.widgetRegistry.resource[subject];
        } else if (predicate in this.widgetRegistry.property) {
            widgetConstructor = this.widgetRegistry.property[predicate];
        } else if (object && object.datatype && object.datatype in this.widgetRegistry.datatype) {
            widgetConstructor = this.widgetRegistry.datatype[object.datatype];
        }
        
        // query the selection cache
        if (predicate in this.selectionCache) {
            var cachedInfo = this.selectionCache[predicate];
            
            // use cached info
            if ('types' in cachedInfo) {
                var types = $.isArray(cachedInfo.types) ? cachedInfo.types : [cachedInfo.types];
                
                if (($.inArray('http://www.w3.org/2002/07/owl#DatatypeProperty', types) != -1)
                    || $.inArray('http://www.w3.org/2002/07/owl#AnnotationProperty', types) != -1) {
                    widgetConstructor = this.widgetRegistry.__literal[''];
                } else if ($.inArray('http://www.w3.org/2002/07/owl#ObjectProperty', types) != -1) {
                    widgetConstructor = this.widgetRegistry.__object[''];
                }
            }
        }
        
        // fallback to default widgets
        if (null === widgetConstructor) {
            if (object) {
                if (object.type == 'literal') {
                    widgetConstructor = this.widgetRegistry.__literal[''];
                } else {
                    widgetConstructor = this.widgetRegistry.__object[''];
                }
            } else {
                widgetConstructor = this.widgetRegistry.__default[''];
            }
        }
        
        var widgetInstance = null;
        if (typeof widgetConstructor == 'function') {
            widgetInstance = new widgetConstructor(graph, subject, predicate, object);
        }
        
        return widgetInstance;
    }, 
    
    invalidatePage: function() {
        // this.pageParsed = false;
        this.view = null;
    }, 
    
    loadScript: function (scriptUri) {
        var script  = document.createElement('script');
        script.type = 'text/javascript';
        script.src  = scriptUri;
        $('head').append(script);
    }, 
    
    loadStyleSheet: function (styleSheetUri) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.media = 'screen';
        link.href = styleSheetUri;
        $('head').append(link);
        
        // if ($('#rdfAuthorStylesheets').length < 1) {
        //     var style  = document.createElement('style');
        //     style.type = 'text/css';
        //     style.id   = 'rdfAuthorStylesheets';
        //     $('head').append(style);
        // }
        // 
        // $('#rdfAuthorStylesheets').append('@import url("' + styleSheetUri + '") screen;');
    }, 
    
    // method to add a new property that is not contained in the RDFa
    newProperty: function () {
        var view = this.getView();
        var widgetInstance = new ResourceEdit(null, null, null, null, {propertyMode: true});
        view.addWidgetInstance(widgetInstance);
        view.display(this.options.animated);
    }, 
    
    query: function (query, callback) {
        // var triple   = arguments.caller.triple;
        var endpoint = this.getServiceUriForTriple();
        
        $.getJSON(endpoint, {'query': query}, function(data) {
            callback(data);
        });
    }, 
    
    updateSources: function () {
        // send results for all graphs
        for (graph in this.graphInfo) {
            // get graph's update URI
            var updateUri = this.graphInfo[graph].updateEndpoint;
            
            var jsonAdded   = null;
            var jsonRemoved = null;
            
            var instance = this;
            
            // only proceed if graph is updateable
            if (typeof updateUri == 'string' && updateUri !== '') {
                if (this.databanks[graph]) {
                    if (this.protectedTriples[graph] instanceof $.rdf.databank) {
                        // add protected triples that are saved anyway
                        this.protectedTriples[graph].triples().each(function() {
                            instance.databanks[graph].add(this);
                        });
                    }
                            
                    var added = this.databanks[graph].except(this.originalDatabanks[graph]);
                    jsonAdded = $.rdf.dump(added.triples(), {format: 'application/json'});
                    
                    var removed = this.originalDatabanks[graph].except(this.databanks[graph]);
                    jsonRemoved = $.rdf.dump(removed.triples(), {format: 'application/json'});
                }
                
                // alert('Added: ' + $.toJSON(jsonAdded ? jsonAdded : {}) + '\n' + 
                //       'Removed: ' + $.toJSON(jsonRemoved ? jsonRemoved : {}));
                
                if (jsonAdded || jsonRemoved) {
                    // sumbit only if something has changed
                    $.post(updateUri, {
                        'named-graph-uri': graph, 
                        'insert': $.toJSON(jsonAdded ? jsonAdded : {}), 
                        'delete': $.toJSON(jsonRemoved ? jsonRemoved : {})
                    }, function () {
                        if (typeof instance.options.onSubmitSuccess == 'function') {
                            instance.options.onSubmitSuccess();
                        }
                    });
                }
            }
        }
        
        this.view.hide(this.options.animated);
    }, 
    
    // depending on the current edit mode adds a triple row to the edit view
    // or adds a click event to the element
    makeElementEditable: function (element, triple, graph) {
        var ignore = false;
        
        // check if namespace should be ignored
        for (var i = 0; i < this.ignoreNs.length; i++) {
            if (triple.predicate.uri.match(this.ignoreNs[i])) {                
                ignore = true;
                break;
            }
        }
        
        // check and store internal predicates
        if (triple.predicate.uri.match(this.updateNs)) {
            if (triple.predicate.uri == this.internalPredicates.updateGraph 
                || triple.predicate.uri == this.internalPredicates.defaultGraph) {
                
                var objectGraph = triple.object.uri;
                if (!this.graphInfo[objectGraph]) {
                    this.graphInfo[objectGraph] = {};
                }
                
                if (triple.predicate.uri == this.internalPredicates.defaultGraph) {
                    this.defaultGraph = objectGraph;
                }
            } else {
                // HACK:
                var subjectGraph = triple.subject.uri;
                if (!this.graphInfo[subjectGraph]) {
                    this.graphInfo[subjectGraph] = {};
                }
                
                if (triple.predicate.uri == this.internalPredicates.queryEndpoint) {
                    this.graphInfo[subjectGraph].queryEndpoint = triple.object.uri;
                } else if (triple.predicate.uri == this.internalPredicates.updateEndpoint) {
                    this.graphInfo[subjectGraph].updateEndpoint = triple.object.uri;
                }
            }
            
            ignore = true;
        }
        
        if (!ignore) {
            var predicateTitle = this.getPredicateTitle(element, triple.predicate.uri);
            
            if (!this.predicateInfo[triple.predicate.uri]) {
                this.predicateInfo[triple.predicate.uri] = {title: predicateTitle};
            }
            
            var objectOptions = {};
            var object = triple.object.value;
            
            if (triple.object.lang) {
                objectOptions.lang = triple.object.lang;
            } else if (triple.object.datatype) {
                objectOptions.datatype = triple.object.datatype.uri;
            } else {
                object = '"' + triple.object.value + '"';
            }
            
            var tripleObject = triple.object.uri 
                             ? $.rdf.resource('<' + triple.object.uri + '>') 
                             : $.rdf.literal(object, objectOptions);
            
            var rdfTriple = $.rdf.triple(
                $.rdf.resource('<' + triple.subject.uri + '>'), 
                $.rdf.resource('<' + triple.predicate.uri + '>'), 
                tripleObject
            );
            
            // add triple info (graph)
            this.tripleInfo[triple] = graph;
            
            // add triple to databanks
            var databank = this.getDatabank(graph);
            databank.add(rdfTriple);
        }
    }, 
    
    // registers a widget with the API
    // widgetSpec is an object that must contain the following keys:
    //  - constructor: a reference to or the name of the constructor function 
    //    for instances of that widget
    //  - hookName: resource, property, range, datatype, __object, __literal
    //  - hookValues: an array of possible values for hookName that trigger the widget
    registerWidget: function (widgetSpec) {
        var defaultSpec = {
            hookValues: ['']
        };
        widgetSpec = $.extend(defaultSpec, widgetSpec);
        
        if (widgetSpec.hookName in this.widgetRegistry) {
            // keep this in a closure
            var instance = this;
            
            // register for all hook values
            for (var i = 0; i < widgetSpec.hookValues.length; i++) {
                var value = widgetSpec.hookValues[i];
                if (!(value in instance.widgetRegistry[widgetSpec.hookName])) {
                    instance.widgetRegistry[widgetSpec.hookName][value] = widgetSpec.constructorFunction;
                }
            }
            
            return true;
        }
        
        // unsupported widget hook
        return false;
    }, 
    
    // sets a set of options for the RDFauthor API
    setOptions: function (options) {
        this.options = $.extend(this.options, options);
    }, 
    
    // shows the edit view
    startEditing: function () {
        var instance = this;
        
        // load selection cache
        if (!this.selectionCacheLoaded) {
            $.getJSON(widgetBase + 'src/selectionCache.json', function(data) {
                this.selectionCacheLoaded = true;
                if (typeof data == 'object') {
                    RDFauthor.selectionCache = data;
                }
            });
        }
        
        if (!RDFauthor.pageParsed) {
            RDFA.parse();
            
            RDFA.CALLBACK_DONE_PARSING = function () {
                var view = instance.createPropertyView();
                view.display(instance.options.animated);
                RDFauthor.pageParsed = true;
            };
        } else {
            var view = this.createPropertyView();
            view.display(instance.options.animated);
        }
    }, 
    
    // start editing a resource based on a template
    startTemplate: function (template) {
        // HACK:
        var graph = this.getDefaultGraph();
        
        // TODO: load selection cache
        var statementFragments = [];
        var databank = this.getDatabank();
        
        this.protectedTriples[graph] = $.rdf.databank();
        
        if (typeof template == 'object') {
            for (var resource in template) {
                for (var property in template[resource]) {
                    var values = template[resource][property];
                    
                    for (var index = 0; index < values.length; index++) {
                        var current = values[index];
                        
                        if ('value' in current) {
                            // complete statement
                            // construct triple
                            var objectOptions = {};
                            if ('lang' in current) {
                                objectOptions.lang = current.lang;
                            } else if ('datatype' in current) {
                                objectOptions.datatype = current.datatype;
                            }

                            var object = current.type == 'uri' 
                                       ? $.rdf.resource('<' + current.value + '>') 
                                       : $.rdf.literal(current.value, objectOptions);

                            var rdfTriple = $.rdf.triple(
                                $.rdf.resource('<' + resource + '>'), 
                                $.rdf.resource('<' + property + '>'), 
                                object
                            );

                            // add it
                            // databank.add(rdfTriple);
                            
                            // mark it as protected, since it won't be changed
                            this.protectedTriples[graph].add(rdfTriple);
                            
                            // save title
                            this.predicateInfo[property] = {title: current.title};
                        } else {
                            // inclomplete triple, keep for later
                            statementFragments.push({'s': resource, 'p': property, 't': current.title});
                        }
                    }
                }
            }
            
            // databank has been filled, create view
            var view = this.createPropertyView();
            RDFauthor.pageParsed = true;
            
            for (var i = 0; i < statementFragments.length; i++) {
                var currentFragment = statementFragments[i];
                var id = view.addRow(currentFragment.s, currentFragment.p, currentFragment.t, null, this.getDefaultGraph());
            }
            
            view.display(this.options.animated);
        }
    }
};

///////////////////////////////////////////////////////////////////////////////
// RDFa Parsing Setup
///////////////////////////////////////////////////////////////////////////////

/**
 * Callback when the RDFa parsing is done.
 */
RDFA.CALLBACK_DONE_PARSING = function () {
    RDFauthor.pageParsed = true;
};

/**
 * Callback when the RDFa loading is done.
 */
RDFA.CALLBACK_DONE_LOADING = function () {
    RDFauthor.parserLoaded = true;
};

/**
 * Is called when a new triple with a resource object has been found.
 *
 * @param object object URI
 * @param triple Statement
 */
RDFA.CALLBACK_NEW_TRIPLE_WITH_URI_OBJECT = function (element, triple, graph) {
    if (triple) {
        RDFauthor.makeElementEditable(element, triple, graph);
    }
};

/**
 * Is called when a new triple with a literal object has been found.
 *
 * @param element HTML element
 * @param triple Statement
 */
RDFA.CALLBACK_NEW_TRIPLE_WITH_LITERAL_OBJECT = function (element, triple, graph) {
    if (triple) {
        RDFauthor.makeElementEditable(element, triple, graph);
    }
};


///////////////////////////////////////////////////////////////////////////////
// Property Row
///////////////////////////////////////////////////////////////////////////////

//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: rdfauthor.complete.js 3920 2009-08-04 21:35:42Z norman.heino $
//

//
// RDFauthorPropertyRow
//
// Row object representing an editable property row
// Author: Norman Heino <norman.heino@gmail.com>
//
function RDFauthorPropertyRow(container, subject, property, title) {    
    // the subject for this row
    this.subject  = subject;
    
    // the property this row operates on
    this.property = property;
    
    // the human-readable string representing the property 
    if (typeof title == 'string') {
        this.title = title;
    } else {
        this.title = property;
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
                    <a class="delete-button"></a>\
                    <a class="add-button"></a>\
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
            this.widgets[index] = widgetInstance;
            // store widget-id index mapping
            this.widgetsById[widgetId] = index;
            
            $('#property-row-' + this.id).children('fieldset').append(widgetHtml);
            
            // process init request
            if (typeof widgetInstance.init == 'function') {
                widgetInstance.init();
            }
            
            // return widget's CSS id
            return 'widget-' + widgetId;
        } else {
            $('#property-row-' + this.id).children('fieldset').append('<div class="container">No suitable widget found.</div>');
        }
    };
}

// Variable to keep track of the number of instances
RDFauthorPropertyRow.prototype.numberOfRows = 0;

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
$(document).ready(function() {
    $('.actions a.delete-button').live('click', function(event) {
        if ($(this).closest('.property-row').length) {
            var rowId    = $(this).closest('.property-row').attr('id').replace('property-row-', '');
            var widgetId = $(this).closest('.widget').attr('id').replace('widget-', '');
            
            var view = RDFauthor.getView();
            var row  = view.getRow(rowId);
            
            var widgetIndex = row.widgetsById[widgetId];
            row.removeWidget(widgetIndex, widgetId);
        }
    });
    
    $('.actions .add-button').live('click', function() {
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
});


///////////////////////////////////////////////////////////////////////////////
// View
///////////////////////////////////////////////////////////////////////////////

//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: rdfauthor.complete.js 3920 2009-08-04 21:35:42Z norman.heino $
//

//
// RDFauthorView
//
// RDFa Editing View
// Author: Norman Heino <norman.heino@gmail.com>
//
function RDFauthorView(options) {
    // default options
    var defaults = {
        title: 'Edit Properties', 
        saveButtonTitle: 'Save', 
        cancelButtonTitle: 'Cancel', 
        addPropertyButtonTitle: 'Add Property', 
        showButtons: true, 
        showAddPropertyButton: true, 
        animationTime: 250,   // ms
        id: 'rdfAuthorContainer'
    };
    this.options = $.extend(defaults, options);
    
    this.container = this.options.container != 'undefined' ? this.options.container : $('body');
    
    // the property selector object
    this.propertySelector = null;
    
    // subject => property => row
    this.rows = {};
    
    // id => row
    this.rowsById = {};
    
    this.id = this.options.id;
    
    // retain this for private members
    var instance = this;
    
    function getChrome() {
        var chrome = '\
            <div class="window" id="' + instance.id + '" style="display:none">\
                <h2 class="title">' + instance.options.title + '</h2>\
                <div class="rdfAuthorPropertyRows">\
                </div>\
                    ' + getButtons() + '\
                <div style="clear:both"></div>\
            </div>';
        
        return chrome;
    }
    
    if ($('#' + this.id).length < 1) {
        // append chrome
        var chromeHtml = getChrome();
        this.container.append(chromeHtml);
        
        // make draggable if jQuery UI loaded
        if (typeof jQuery.ui != 'undefined') {
            $('#' + this.id).draggable({handle: 'h2', zIndex: 1000});
        }
    }
    
    function getButtons() {
        var buttonsHtml = '';
        if (instance.options.showButtons) {
            
            var propertyButton = '';
            if (instance.options.showAddPropertyButton) {
                propertyButton = '\
                    <button type="button" class="rdfAuthorButtonAddProperty">\
                        ' + instance.options.addPropertyButtonTitle + '\
                    </button>';
            }
            
            buttonsHtml = '\
                <div id="rdfAuthorButtons">\
                    ' + propertyButton + '\
                    <button type="button" class="rdfAuthorButtonCancel">' + instance.options.cancelButtonTitle + '</button>\
                    <button type="button" class="rdfAuthorButtonSave">' + instance.options.saveButtonTitle + '</button>\
                </div>';
        }
        
        return buttonsHtml;
    }
    
    //
    // Privileged methods
    //
    
    this.reset = function () {
        // reset data
        this.rows = {};
        this.rowsById = {};

        // reset HTML
        if ($('#' + this.id).length) {
            $('#' + this.id).replaceWith(getChrome());
        }
    };
}

//
// Public shared methods
//

RDFauthorView.prototype.addRow = function (subject, property, title, object, graph) {    
    if (!(subject in this.rows)) {
        this.rows[subject] = {};
    }
    
    var row;
    if (property in this.rows[subject]) {
        // property row already exists
        row = this.rows[subject][property];
    } else {
        // create new property row
        var container = $('.rdfAuthorPropertyRows');
        row = new RDFauthorPropertyRow(container, subject, property, title);
        this.rows[subject][property] = row;
        
        this.rowsById[row.id] = row;
    }
    
    return row.addWidget(object, graph);
};

RDFauthorView.prototype.addWidgetInstance = function (widgetInstance) {
    var container = $('.rdfAuthorPropertyRows');
    var newProperty = '\
        <div class="container" id="newPropertyContainer123">\
            ' + widgetInstance.getHtml() + '<button id="addWidgetButton">Add Widget</button>\
        </div>';
    container.append(newProperty);
    
    var instance = this;
    $('#addWidgetButton').click(function() {
        var info = widgetInstance.getValue();
        instance.addRow(subject, info.uri, info.title, null, graph);
    });
    
    // process init request
    if (typeof widgetInstance.init == 'function') {
        widgetInstance.init();
    }
};

RDFauthorView.prototype.callHook = function (hookName) {
    if (typeof this.options[hookName] == 'function') {
        this.options[hookName]();
    }
};

RDFauthorView.prototype.display = function (animated) {
    if (this.options.anchorElement) {
        // use anchor element
        var jElement = $(this.options.anchorElement);
        $('#' + this.id).css('top', jElement.offset().top);
        $('#' + this.id).css('left', jElement.offset().left);
    } else {
        // center
    }
    
    // force max height for container
    var htmlHeight  = $('html').height();
    var modalHeight = this.container.height();
    this.container.height(Math.max(htmlHeight, modalHeight) + 'px');
    
    this.container.show();
    if (!animated) {
        $('#' + this.id).show();
    } else {
        $('#' + this.id).fadeIn(this.options.animationTime);
    }
};

RDFauthorView.prototype.getPropertySelector = function () {
    if (null === this.propertySelector) {
        var instance  = this;
        
        var graph   = RDFauthor.getDefaultGraph();
        var subject = RDFauthor.getDefaultResource();
        
        var selectorOptions = {
            container: '.rdfAuthorPropertyRows', 
            callback: function() {
                var propertySelector = instance.getPropertySelector();
                var propertyUri      = propertySelector.selectedProperty();
                var propertyTitle    = propertySelector.selectedPropertyTitle();
                
                // TODO: animate
                propertySelector.dismiss(false);
                
                var id = instance.addRow(subject, propertyUri, propertyTitle, null, graph);
                var widgetTop    = $('#' + id).offset().top;
                var containerTop = $('.rdfAuthorPropertyRows').offset().top;
                
                if (widgetTop > 0) {
                    $('.rdfAuthorPropertyRows').animate({scrollTop: (widgetTop - containerTop) + 'px'}, instance.options.animationTime);
                }
            }, 
            id: 'rdfAuthorPropertySelector'
        };
        this.propertySelector = new RDFauthorPropertySelector(selectorOptions);
    }
    
    return this.propertySelector;
};

RDFauthorView.prototype.getRow = function (id) {
    var row = null;
    
    if (id in this.rowsById) {
        row = this.rowsById[id];
    }
    
    return row;
};

RDFauthorView.prototype.hide = function (animated, callback) {
    if (!animated) {
        $('#' + this.id).hide();
        this.container.hide();
    } else {
        var instance = this;
        $('#' + this.id).fadeOut(this.options.animationTime, function() {
            instance.container.hide();
            if (typeof callback == 'function') {
                callback();
            }
        });
    }
};

RDFauthorView.prototype.onAddProperty = function () {
    this.callHook('addProperty');
    
    var selector = this.getPropertySelector();
    selector.showInContainer(null, null, true);
};

RDFauthorView.prototype.onCancel = function () {
    this.callHook('beforeCancel');
    
    for (var subject in this.rows) {
        for (var property in this.rows[subject]) {
            var row = this.rows[subject][property];
            row.onCancel();
        }
    }
    
    // dismiss property selector
    if (this.propertySelector) {
        this.propertySelector.dismiss(false);
    }
    
    this.callHook('afterCancel');
};

RDFauthorView.prototype.onSubmit = function () {
    this.callHook('beforeSubmit');
    
    var submitOk = true;
    for (var subject in this.rows) {
        for (var property in this.rows[subject]) {
            var row = this.rows[subject][property];
            submitOk = row.onSubmit() && submitOk;
        }
    }
    
    // dismiss property selector
    if (this.propertySelector) {
        this.propertySelector.dismiss(false);
    }
    
    if (submitOk) {
        this.callHook('afterSubmit');
    }
};

///////////////////////////////////////////////////////////////////////////////

// create live triggers for click buttons
$('document').ready(function() {
    $('#rdfAuthorButtons .rdfAuthorButtonSave').live('click', function() {
        var view = RDFauthor.getView();
        view.onSubmit();
    });
    
    $('#rdfAuthorButtons .rdfAuthorButtonCancel').live('click', function() {
        var view = RDFauthor.getView();
        view.onCancel();
    });
    
    $('#rdfAuthorButtons .rdfAuthorButtonAddProperty').live('click', function() {
        var view = RDFauthor.getView();
        view.onAddProperty();
    });
});



///////////////////////////////////////////////////////////////////////////////
// Property Selector
///////////////////////////////////////////////////////////////////////////////

//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: rdfauthor.complete.js 3920 2009-08-04 21:35:42Z norman.heino $
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
        this.container.prepend(this.getHtml(content));
        
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

///////////////////////////////////////////////////////////////////////////////
// Literal Edit Widget
///////////////////////////////////////////////////////////////////////////////

function LiteralEdit(graph, subject, predicate, object) {
    
    var rdfNS = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    var xsdNS = 'http://www.w3.org/2001/XMLSchema#';
    
    this.ns = {};
    
    this.id = RDFauthor.getNextId();
    
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.languages = RDFauthor.getLiteralLanguages();
    this.datatypes = RDFauthor.getLiteralDatatypes();
    
    if ($('table.Resource').length) {
        var ns = $('table.Resource').xmlns();
        for (var prefix in ns) {
            if (ns[prefix] == rdfNS || ns[prefix] == xsdNS) {
                this.ns[prefix] = ns[prefix];
            }
        }
    }
    
    this.languages.unshift('');
    this.datatypes.unshift('');
    
    if (object) {
        this.object   = object.value;
        this.language = object.lang ? object.lang : '';
        this.datatype = object.datatype ? object.datatype : '';
    } else {
        this.object   = '';
        this.language = '';
        this.datatype = '';
    }
    
    this.remove = false;
}

LiteralEdit.prototype.getHtml = function() {
    var html = '\
        <div class="container literal-value">\
            <input type="text" class="text" id="literal-value-' + this.id + '" value="' + this.object + '" size="30" maxLength="128" />\
        </div>\
        <div class="container literal-type util">\
            <label><input type="radio" class="radio" name="literal-type-' + this.id + '"' + (this.datatype ? '' : ' checked="checked"') + ' value="plain" />Plain</label>\
            <label><input type="radio" class="radio" name="literal-type-' + this.id + '"' + (this.datatype ? ' checked="checked"' : '') + ' value="typed" />Typed</label>\
        </div>\
        <div class="container util">\
            <div class="literal-lang"' + (this.datatype ? ' style="display:none"' : '') + '>\
                <label for="literal-lang-' + this.id + '">Language:\
                    <select id="literal-lang-' + this.id + '" name="literal-lang-' + this.id + '">\
                        ' + this.makeOptionString(this.languages, this.language) + '\
                    </select>\
                </label>\
            </div>\
            <div class="literal-datatype"' + (this.datatype ? '' : ' style="display:none"') + '>\
                <label>Datatype:\
                    <select id="literal-datatype-' + this.id + '" name="literal-datatype-' + this.id + '">\
                        ' + this.makeOptionString(this.datatypes, this.datatype, true) + '\
                    </select>\
                </label>\
            </div>\
        </div>';
    
    return html;
};

LiteralEdit.prototype.makeOptionString = function(options, selected, replaceNS) {
    replaceNS = replaceNS || false;
    
    var optionString = '';
    for (var i = 0; i < options.length; i++) {
        var display = options[i];
        if (replaceNS) {
            for (var s in this.ns) {
                if (options[i].match(this.ns[s])) {
                    display = options[i].replace(this.ns[s], s + ':');
                    break;
                }
            }
        }
        
        var current = options[i] == selected;
        if (current) {
            // TODO: do something
        }
        optionString += '<option value="' + options[i] + '"' + (current ? 'selected="selected"' : '') + '>' + display + '</option>';
    }
    
    return optionString;
};

LiteralEdit.prototype.onCancel = function() {
    return true;
};

LiteralEdit.prototype.onRemove = function() {
    this.remove = true;
};

LiteralEdit.prototype.onSubmit = function() {
    var dataBank = RDFauthor.getDatabank(this.graph);
    
    // get new values
    var newObjectValue    = $('#literal-value-' + this.id).val();
    var newObjectLang     = $('#literal-lang-' + this.id + ' option:selected').eq(0).val();
    var newObjectDatatype = $('#literal-datatype-' + this.id + ' option:selected').eq(0).val();
    
    var somethingChanged = (newObjectValue != this.object) || (newObjectLang != this.language) || (newObjectDatatype != this.datatype);
    
    if (somethingChanged || this.remove) {
        // remove old triple
        if (this.object !== '') {
            var objectOptions = {};
            var object = this.object;
            
            if (this.datatype !== '') {
                objectOptions.datatype = this.datatype;
            } else if (this.language !== '') {
                objectOptions.lang = this.language;
            } else {
                object = '"' + this.object + '"';
            }

            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(object, objectOptions)
            );

            dataBank.remove(oldTriple);
        }
        
        if (!this.remove) {
            // add new triple
            var newObjectOptions = {};
            var newObject = newObjectValue;

            if (newObjectLang !== '') {
                newObjectOptions.lang = newObjectLang;
            } else if (newObjectDatatype !== '') {
                newObjectOptions.datatype = newObjectDatatype;
            } else {
                newObject = '"' + newObjectValue + '"';
            }

            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(newObject, newObjectOptions)
            );

            dataBank.add(newTriple);
        }
    }
    
    return true;
};

RDFauthor.registerWidget({constructorFunction: LiteralEdit, hookName: '__literal'});

$(document).ready(function() {
    $('.literal-type .radio').live('click', function() {
        var jDatatypeDiv = $(this).parents('.widget').children().find('.literal-datatype');
        var jLangDiv     = $(this).parents('.widget').children().find('.literal-lang');
        
        if ($(this).val() == 'plain') {
            jDatatypeDiv.hide();
            jLangDiv.show();
        } else {
            jDatatypeDiv.show();
            jLangDiv.hide();
        }
    });
});

///////////////////////////////////////////////////////////////////////////////
// Date Edit Widget
///////////////////////////////////////////////////////////////////////////////

function DateEdit(graph, subject, predicate, object) {
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object ? object.value : '';
    this.id        = RDFauthor.getNextId();
    
    this.remove = false;
}

DateEdit.prototype.datatype = 'http://www.w3.org/2001/XMLSchema#date';

DateEdit.prototype.init = function () {
    // RDFauthor.loadStyleSheet(widgetBase + 'src/dateedit.css');
    
    $('#date-edit-' + this.id).datepicker({
        dateFormat: $.datepicker.ISO_8601, 
        // showOn: 'both', 
        firstDay: 1
    });
};

DateEdit.prototype.getHtml = function() {
    var html = 
        '<div class="container">' + 
            '<input type="text" class="text" id="date-edit-' + this.id + '" value="' + this.object + '"/>' + 
        '</div>';
    
    return html;
};

DateEdit.prototype.onCancel = function () {
    return true;
};

DateEdit.prototype.onRemove = function () {
    this.remove = true;
};

DateEdit.prototype.onSubmit = function () {
    var dataBank = RDFauthor.getDatabank(this.graph);
    var newValue = $('#date-edit-' + this.id).val();
    
    if ((newValue != this.object) || this.remove) {
        // remove old triple
        if (this.object !== '') {
            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(this.object, {datatype: DateEdit.prototype.datatype})
            );

            dataBank.remove(oldTriple);
        }
        
        if (!this.remove) {
            // add new triple
            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(newValue, {datatype: DateEdit.prototype.datatype})
            );

            dataBank.add(newTriple);
        }        
    }
    
    return true;
};

RDFauthor.registerWidget({constructorFunction: DateEdit, hookName: 'datatype', hookValues: [DateEdit.prototype.datatype]});

///////////////////////////////////////////////////////////////////////////////
// Resource Edit Widget
///////////////////////////////////////////////////////////////////////////////

function ResourceEdit(graph, subject, predicate, object, options) {
    var defaultOptions = {
        propertyMode: false
    };
    
    this.options = $.extend(defaultOptions, options);
    
    this.id = RDFauthor.getNextId();
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object ? object.value : '';
    this.label = null;
    
    this.remove = false;
}

ResourceEdit.prototype.init = function () {
    var instance = this;
    
    if (this.options.propertyMode) {
        $('#resource-value-' + this.id).autocomplete(function(term, cb) { return ResourceEdit.propertySearch(term, cb); }, {
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
        
        $('#resource-value-' + this.id).result(function(e, data, formated) {
            $(this).attr('value', data[1]);
            instance.label = data[0];
        });
    } else {
		//$('#resource-value-' + this.id).parents('div').eq(0).prev('div').children('label').eq(0).children('.radio').eq(0).click();
		$('#resource-value-' + this.id).autocomplete(function(term, cb) { return ResourceEdit.endpointSearch(term, cb); }, {
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

		$('#resource-value-' + this.id).result(function(e, data, formated) {
            $(this).attr('value', data[1]);
        });
	}	
}


ResourceEdit.prototype.getHtml = function() {
    var html = '';
    
    if (!this.options.propertyMode) {
        html += '\
            <div class="inline-container resource-action util">\
				<label><input type="radio" class="radio" name="literal-type-' + this.id + '" checked="checked" value="2" />Local Search</label>\
                <label><input type="radio" class="radio" name="literal-type-' + this.id + '" value="0" />Direct Input</label>\
                <label><input type="radio" class="radio" name="literal-type-' + this.id + '" value="1" />Sindice Search</label>\
            </div>';
    }
    
    html += '\
        <div class="container resource-value">\
            <input type="text" id="resource-value-' + this.id + '" class="text width99 resource-edit-input" value="' + this.object + '" size="55" maxLength="128" />\
        </div>';
    
    return html;
}

ResourceEdit.prototype.onCancel = function() {
    return true;
}

ResourceEdit.prototype.onRemove = function() {
    this.remove = true;
}

ResourceEdit.prototype.onSubmit = function() {
    var dataBank = RDFauthor.getDatabank(this.graph);
    
    var newResourceValue = $('#resource-value-' + this.id).val();
    // alert(newResourceValue);
    var hasChanged = (newResourceValue != this.object) && (newResourceValue != '');
    if (hasChanged || this.remove) {
        // Remove the old triple
        if (this.object != '') {
            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.resource('<' + this.object + '>')
            );

            dataBank.remove(oldTriple);
        }
        
        if (!this.remove) {
            // Add new triple
            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.resource('<' + newResourceValue + '>')
            );

            dataBank.add(newTriple);
        };
    }
    
    return true;
}

ResourceEdit.prototype.getValue = function() {
    var value = $('#resource-value-' + this.id).val();
    
    return {
        uri: value, 
        title: this.label
    }
}

ResourceEdit.sindiceSearch = function(term, cb) {
    $.getJSON('http://api.sindice.com/v2/search?q=' + encodeURIComponent(term) + '&qt=term&page=1&format=json&callback=?', 
        function(jsonData) {
            var dataString = "";
            for (var i=0; i<jsonData.entries.length; ++i) {
                var titleString = new String(jsonData.entries[i].title);
                var linkString = new String(jsonData.entries[i].link);
                dataString += titleString + '|' + linkString + '\n';
            }
            
            cb(dataString);
        });
}

ResourceEdit.endpointSearch = function(term, cb) {
	
    var uri    = urlBase + 'service/urisearch?q=' + encodeURIComponent(term);
    
    $.get(uri, {}, function(data) {
        cb(data);
    });
}

ResourceEdit.propertySearch = function(term, cb) {
	
    var uri    = urlBase + 'service/propsearch?q=' + encodeURIComponent(term);
    
    $.get(uri, {}, function(data) {
        cb(data);
    });
}

RDFauthor.loadScript(widgetBase + 'libraries/jquery.autocomplete.js');
RDFauthor.loadStyleSheet(widgetBase + 'libraries/jquery.autocomplete.css');
RDFauthor.registerWidget({constructorFunction: ResourceEdit, hookName: '__object'});

$(document).ready(function() {
    $('.resource-action .radio').live('click', function() {
		var inputElem = $(this).parents('div').eq(0).next('div').eq(0).children('.resource-edit-input');
	
        if ($(this).val() == '0') {
            // Direct input
            inputElem.unautocomplete();
        } else if ($(this).val() == '1') {
            // sindice.com
            inputElem.unautocomplete();
            inputElem.autocomplete(function(term, cb) { return ResourceEdit.sindiceSearch(term, cb); }, {
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
            
            inputElem.result(function(e, data, formated) {
                $(this).attr('value', data[1]);
            });
        } else {
            // SPARQL Endpoint
			inputElem.unautocomplete();
            inputElem.autocomplete(function(term, cb) { return ResourceEdit.endpointSearch(term, cb); }, {
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
	
			inputElem.result(function(e, data, formated) {
                $(this).attr('value', data[1]);
            });
        }

    });
})


///////////////////////////////////////////////////////////////////////////////
// Meta Edit Widget
///////////////////////////////////////////////////////////////////////////////

function MetaEdit(graph, subject, predicate, object, options) {
    this.options = $.extend({
        active: 'literal'
    }, options);
    
    this.id        = RDFauthor.getNextId();
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object;
    
    options = {graph: this.graph, subject: this.subject, predicate: this.predicate, object:this.object};
    this.resourceEdit = RDFauthor.getWidgetForHook('__object', null, options);
    this.literalEdit  = RDFauthor.getWidgetForHook('__literal', null, options);
}

MetaEdit.prototype.init = function () {
	this.resourceEdit.init();
};

MetaEdit.prototype.getHtml = function () {
    var active = this.getActiveWidget() == this.literalEdit ? 'literal' : 'resource';
    
    var html = '\
        <div class="meta-select">\
            <div class="inline-container meta-type util">\
                <label><input type="radio" class="radio" ' + (active == 'resource' ? 'checked="checked"' : '') + ' name="meta-type-' + this.id + '"' + ' value="resource" />Resource</label>\
                <label><input type="radio" class="radio" ' + (active == 'literal' ? 'checked="checked"' : '') + ' name="meta-type-' + this.id + '"' + ' value="literal" />Literal</label>\
            </div>\
            <hr style="clear:left; height:0; border:none" />\
            <div class="meta-resource" id="meta-resource-' + this.id + '"' + (active == 'resource' ? '' : 'style="display:none"') + '>\
                ' + this.resourceEdit.getHtml() + '\
            </div>\
            <div class="meta-literal" id="meta-literal-' + this.id + '"' + (active == 'literal' ? '' : 'style="display:none"') + '>\
                ' + this.literalEdit.getHtml() + '\
            </div>\
        </div>';
    
    return html;
};

MetaEdit.prototype.onCancel = function () {
    if (this.activeWidget) {
        return this.getActiveWidget().onCancel();
    }
    
    return true;
};

MetaEdit.prototype.onRemove = function() {
    this.getActiveWidget().onRemove();
};

MetaEdit.prototype.onSubmit = function () {
    return this.getActiveWidget().onSubmit();
};

MetaEdit.prototype.getActiveWidget = function () {
    var activeWidget = null;
    
    if ($('input:radio[name=meta-type-' + this.id + ']:checked').length) {
        var value = $('input:radio[name=meta-type-' + this.id + ']:checked').val();
        
        if (value == 'literal') {
            activeWidget = this.literalEdit;
        } else {
            activeWidget = this.resourceEdit;
        }
    } else {
        activeWidget = this.options.active == 'literal' ? this.literalEdit : this.resourceedit;
    }
    
    return activeWidget;
};

RDFauthor.registerWidget({constructorFunction: MetaEdit, hookName: '__default'});

$(document).ready(function() {
    $('.meta-type .radio').live('click', function() {
        var jResourceDiv = $(this).closest('.meta-select').children('.meta-resource');
        var jLiteralDiv  = $(this).closest('.meta-select').children('.meta-literal');
        
        if ($(this).val() == 'resource') {
            jResourceDiv.show();
            jLiteralDiv.hide();
        } else {
            jResourceDiv.hide();
            jLiteralDiv.show();
        }
    });
});

///////////////////////////////////////////////////////////////////////////////
// Libraries
///////////////////////////////////////////////////////////////////////////////

// scripts to be loaded
var scripts = [
    // rdfquery libs
    widgetBase + 'libraries/jquery.rdfquery.core.js', 
    // RDFa parser
    widgetBase + 'libraries/rdfa.js', 
];

// load libraries
for (var i = 0; i < scripts.length; i++) {
    RDFauthor.loadScript(scripts[i]);
}
