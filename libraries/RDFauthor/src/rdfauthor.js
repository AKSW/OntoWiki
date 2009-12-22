//
// This file is part of the RDFauthor Widget Library
//
// A library that allows to inject editing functionality into an 
// RDFa-annotated HTML web site.
// 
// Copyright (c) 2008 Norman Heino <norman.heino@gmail.com>
// Version: $Id: rdfauthor.js 4281 2009-10-12 10:24:00Z norman.heino $
//

// pre-create RDFa namespace
var RDFA = RDFA || {};

// set widget base if not existing
var widgetBase = widgetBase || 'http://localhost/RDFauthor/';

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
    tripleInfo: {}, 
    elementInfo: {}, 
    originalDatabanks: {}, 
    protectedTriples: {}, 
    // Array of inline views
    inlineViews: {},
    
    // instance has been initialized
    initialized: false,
    
    errors: 0, 
     
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
    
    // initialization for ids
    idSeed: 123, 
    
    // options object
    options: {
        title: 'Title', 
        saveButtonTitle: 'saveButtonTitle', 
        cancelButtonTitle: 'cancelButtonTitle', 
        showButtons: true, 
        animated: true, 
        bulkMode: true
    }, 
    
    // loaded scripts to be stored
    loadedScripts: {}, 
    
    // loaded stylesheets to be stored
    loadedStylesheets: {}, 
    
    
    rdfNs: 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 
    rdfsNs: 'http://www.w3.org/2000/01/rdf-schema#', 
    owlNs: 'http://www.w3.org/2002/07/owl#', 
    updateNs: 'http://ns.aksw.org/update/', 
    
    // namespaces to be ignored
    ignoreNs: ['http://www.w3.org/1999/xhtml/vocab#'], 
    
    // predicates used internally
    internalPredicates: {
        defaultGraph:   'http://ns.aksw.org/update/defaultGraph', 
        sourceGraph:    'http://ns.aksw.org/update/sourceGraph', 
        queryEndpoint:  'http://ns.aksw.org/update/queryEndpoint', 
        updateEndpoint: 'http://ns.aksw.org/update/updateEndpoint'
    }, 
    
    // optional widget methods
    optionalMethods: ['onRemove'], 
    
    pageParsed: false, 
    
    // JSON structure with predicate metadata
    // title, ranges, types
    predicateInfo: {}, 
    predicateInfoExtended: false, 
    
    // mandatory widget methods
    requiredMethods: ['getHtml', 'init', 'onCancel', 'onSubmit'], 
    
    selectionCache: {}, 
    selectionCacheLoaded: false, 
    
    isInitialized: function () {
       return this.initialized;
    }, 
    
    cancelEditing: function () {
        var view;
        if (view = this.getView()) {
            view.hide(this.options.animated, function() {
                view.reset();
            });
        }
        
        // query all inline views for submission
        for (viewId in this.inlineViews) {
            var view = this.inlineViews[viewId];
            view.reset();
        }
        
        this.reset();
    }, 
    
    commitEditing: function () {
        // keep db before changes
        this.cloneDatabanks();
        
        // query all inline views for submission
        for (viewId in this.inlineViews) {
            var view = this.inlineViews[viewId];
            view.onSubmit();
        }
        
        // submit global view (if any)
        if (this.view) {
            this.view.onSubmit();
        }
        
        // update the sources
        this.updateSources();
    },
    
    // clones the databank for each graph before calling 
    // for the widgets to write their data
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
    createPropertyView: function (subject) {
        var view = this.getView();
        view.reset();
        
        // for all graphs
        for (var graph in this.databanks) {
            // only add triples from editable graphs
            // i.e. for which an update endpoint has been defined
            if (graph in this.graphInfo && this.graphInfo[graph].updateEndpoint != 'undefined') {
                // iterate over all triples
                var triples = this.databanks[graph].triples();
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
                    
                    if (subject) {
                        var currentSubjectString = String(current.subject.value).replace('<', '').replace('>', '');
                        if (subject === currentSubjectString) {
                            view.addRow(current.subject.value, current.property.value, predicateTitle, object, graph);
                        }
                    } else {
                        view.addRow(current.subject.value, current.property.value, predicateTitle, object, graph);
                    }
                }
            };
        }
        
        return view;
    }, 
    
    createInlineView: function (element) {
        var view = this.getInlineView($(element));
        
        // for all graphs
        for (var graph in this.databanks) {
            // only add triples from editable graphs
            // i.e. for which an update endpoint has been defined
            if (graph in this.graphInfo && this.graphInfo[graph].updateEndpoint != 'undefined') {
                // iterate over all triples
                var triples = this.databanks[graph].triples();
                for (var i = 0, length = triples.length; i < length; i++) {
                    var current = triples[i];
                    var tripleElement = this.elementInfo[current];
                    var instance = this;
                    
                    $(element).andSelf().find('*[content], *[resource]').each(function() {
                        if (this != tripleElement) {
                            return;
                        }
                        
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
                        if (current.property.value in instance.predicateInfo) {
                            predicateTitle = instance.predicateInfo[current.property.value].title;
                        }
                        view.addRow(current.subject.value, current.property.value, predicateTitle, object, graph);
                    })
                }
            };
        }
        
        return view;
    }, 
    
    fetchPredicateInfo: function (predicateUri) {
        var filters = [];
        
        if (arguments.length == 1) {
            filters.push('sameTerm(?predicate, <' + predicateUri + '>)');
            filters.push('sameTerm(?predicate, <http://example.com/dummy>)')
        } else if (!this.predicateInfoExtended) {
            // query for ranges and extend predicate info
            for (p in this.predicateInfo) {
                filters.push('sameTerm(?predicate, <' + p + '>)')
            }
        }
        
        if (filters.length > 0) {
            // build query
            var query = '\
                SELECT DISTINCT ?predicate ?type ?range ?label\
                WHERE {\
                    {?predicate <' + this.rdfNs + 'type> ?type.}\
                    UNION\
                    {?predicate <' + this.rdfsNs + 'range> ?range.}\
                    UNION\
                    {?predicate <' + this.rdfsNs + 'label> ?label.}\
                    FILTER(' + filters.join(' || ') + ')\
                }';
            query = query.replace(/\s+/g, ' ');
            
            // query graph
            var instance = this;
            this.query(this.getDefaultGraph(), query, function(json) {
                // hack for old Erfurt format
                var bindings = ('results' in json) ? json['results']['bindings'] : json['bindings'];
                
                for (i in bindings) {
                    // load predicate
                    var predicate = bindings[i]['predicate']['value'];
                    
                    // current info object
                    var info;
                    if (instance.predicateInfo[predicate]) {
                        info = instance.predicateInfo[predicate];
                    } else {
                        info = {};
                    }
                    
                    var type = bindings[i]['type'] ? bindings[i]['type']['value'] : null;
                    if (type) {
                        // add or set new type
                        if ('types' in info) {
                            if (0 <= $.inArray(type, info['types'])) {
                                info['types'].push(type);
                            }
                        } else {
                            info['types'] = [type];
                        }
                    }
                    
                    var range = bindings[i]['range'] ? bindings[i]['range']['value'] : null;
                    if (range) {
                        // add or set new range
                        if ('ranges' in info) {
                            if (0 <= $.inArray(range, info['ranges'])) {
                                info['ranges'].push(range);
                            }
                        } else {
                            info['ranges'] = [range];
                        }
                    }
                    
                    var label = bindings[i]['label'] ? bindings[i]['label']['value'] : null;
                    if (label) {
                        // set new title
                        info.title = info.title ? info.title : label;
                    }
                    
                    // store new info
                    instance.predicateInfo[predicate] = info;
                }
                
                instance.predicateInfoExtended = true;
            }, function(status, error) {
                instance.predicateInfoExtended = true;
            }, false);
        }
    },
    
    // returns the default graph that receives newly added statements
    getDefaultGraph: function () {
        if (null === this.defaultGraph) {
            var defaultGraph = null;
            var defaultQueryEndpoint = null;
            var defaultUpdateEndpoint = null;
            
            if (this.options.defaultGraph) {
                // get default graph from options
                defaultGraph          = this.options.defaultGraph;
                defaultUpdateEndpoint = this.getDefaultUpdateEndpoint();
            } else {
                // try RDFa
                defaultGraph          = $('link[rel$=defaultGraph]').attr('href');
                defaultQueryEndpoint  = $('link[about=' + defaultGraph + '][rel$=queryEndpoint]').attr('href');
                defaultUpdateEndpoint = $('link[about=' + defaultGraph + '][rel$=updateEndpoint]').attr('href');
            }
            
            if (defaultGraph) {
                // store default graph
                this.defaultGraph = defaultGraph;

                // store default graph info
                if (!this.graphInfo[defaultGraph]) {
                    this.graphInfo[defaultGraph] = {};
                }

                if (defaultQueryEndpoint) {
                    this.graphInfo[defaultGraph].queryEndpoint  = defaultQueryEndpoint;
                }
                if (defaultUpdateEndpoint) {
                    this.graphInfo[defaultGraph].updateEndpoint = defaultUpdateEndpoint;
                }
            }
        }
        
        return this.defaultGraph;
    }, 
    
    // returns the default resource that is the subject of newly added statements
    getDefaultResource: function () {
        if (this.options.defaultResource) {
            return this.options.defaultResource;
        }
        
        return null;
    }, 
    
    // returns the default update Service URI
    getDefaultUpdateEndpoint: function () {
        if (this.options.defaultUpdateEndpoint) {
            return this.options.defaultUpdateEndpoint;
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
    
    // Creates and returns a new RDFauthor view object in inline configuration
    getInlineView: function (elementOrId) {
        var view;
        
        if (typeof elementOrId == 'object') {
            var instance = this;

            var options = $.extend(this.options, {
                container: elementOrId, 
                inline: true, 
                replaceContainerContent: true
            });

            // init view
            view = new RDFauthorView(options);
            // store as inline view
            this.inlineViews[view.options.id] = view;
            
            RDFauthor.loadStyleSheet(widgetBase + 'libraries/default.css', 'default.css');
            RDFauthor.loadStyleSheet(widgetBase + 'src/rdfauthor.css');
        } else {
            if (this.inlineViews[elementOrId]) {
                view = this.inlineViews[elementOrId];
            }
        }
        
        return view;
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
            RDFauthor.loadStyleSheet(widgetBase + 'libraries/default.css', 'default.css');
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
        return ['de', 'en', 'fr', 'nl', 'es', 'it', 'cn'];
    }, 
    
    // returns the SPARQL service URI for a given triple
    getServiceUriForTriple: function (triple) {
       var tripleString  = triple.toString();
       var graph         = this.tripleInfo[tripleString];
       return this.getServiceUriForGraph(graph);
    }, 
    
    // returns the SPARQL service URI for a given graph
    getServiceUriForGraph: function (graph) {
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
        
        // specifically fetch the property if it isn't available
        if (!(predicate in this.predicateInfo)) {
            this.fetchPredicateInfo(predicate);
        }
        
        // local widget selection
        if (subject in this.widgetRegistry.resource) {
            widgetConstructor = this.widgetRegistry.resource[subject];
        } else if (predicate in this.widgetRegistry.property) {
            widgetConstructor = this.widgetRegistry.property[predicate];
        } else if (object && object.datatype && object.datatype in this.widgetRegistry.datatype) {
            widgetConstructor = this.widgetRegistry.datatype[object.datatype];
        }
        
        // query predicate info/selection cache
        if ((null === widgetConstructor) && (predicate in this.predicateInfo)) {
            var info = this.predicateInfo[predicate];
            
            // use rdf:type of property
            if ('types' in info) {
                var types = $.isArray(info.types) ? info.types : [info.types];
                
                if (0 <= ($.inArray(this.owlNs + 'DatatypeProperty', types))
                    || 0 <= $.inArray(this.owlNs + 'AnnotationProperty', types)) {
                    widgetConstructor = this.widgetRegistry.__literal[''];
                } else if (0 <= $.inArray(this.owlNs + 'ObjectProperty', types)) {
                    widgetConstructor = this.widgetRegistry.__object[''];
                }
            }
            
            // use rdfs:range of property
            if ('ranges' in info) {
                var ranges = $.isArray(info.ranges) ? info.ranges : [info.ranges];
                
                if (0 <= $.inArray(this.rdfNs + 'Literal', ranges)) {
                    widgetConstructor = this.widgetRegistry.__literal[''];
                }
            }
            
            // TODO: use more ranges
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
    
    invalidatePage: function () {
        // this.pageParsed = false;
        this.view = null;
    }, 
    
    loadScript: function (uris, callback) {
        // make sure array
        if (uris.constructor !== Array) {
            uris = [uris];
        }
        
        var max = uris.length;
        for (var i = 0; i < max; i++) {
            var scriptUri = uris[i];
            
            if (!(scriptUri in this.loadedScripts)) {
                var s = document.createElement('script');
                s.type = 'text/javascript';
                s.src = scriptUri;
                s.charset = 'utf-8';
                
                // add onload event only for the last script in the URIs array
                if ((i === (max - 1)) && (typeof callback == 'function')) {
                    // works: Safari, Chrome, Firefox
                    s.onload = callback;
                }
                
                document.getElementsByTagName('head')[0].appendChild(s);
                this.loadedScripts[scriptUri] = true;
            }
        }
    }, 
    
    loadStyleSheet: function (styleSheetUri, file) {
        var styleSheetLoaded = false;
        var links = document.getElementsByTagName('link');
        var max = links.length;
        
        // check if stylesheet (or file) is loaded already
        for (var i = 0; i < max; ++i) {
            var uri = links[i].getAttribute('href');
            if ((uri && uri == styleSheetUri) || (file && (uri.search(file) > -1))) {
                styleSheetLoaded = true;
                break;
            }
        }
        
        if (!styleSheetLoaded) {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.type = 'text/css';
            link.media = 'screen';
            link.href = styleSheetUri;
            $('head').append(link);
        }
    }, 
    
    // method to add a new property that is not contained in the RDFa
    newProperty: function () {
        var view = this.getView();
        var widgetInstance = new ResourceEdit(null, null, null, null, {propertyMode: true});
        view.addWidgetInstance(widgetInstance);
        view.display(this.options.animated);
    }, 
    
    // preforms a SPARQL query to the store accociated with the graph provided
    // and returns a JSON object to the function supplied as callback parameter
    query: function (graph, query, callbackSuccess, callbackError, async) {
        if (graph) {
            var endpoint = this.getServiceUriForGraph(graph);

            // parameters
            var endpointParams = {
                'query': query, 
                'named-graph-uri': graph
            };

            // call the JSON service via low-level ajax method
            $.ajax({
                'dataType': 'jsonp', 
                'timeout':  2000, 
                'beforeSend': function(xhr) {
                    xhr.setRequestHeader('Accept', 'application/sparql-results+json');
                }, 
                'url':      endpoint, 
                callbackParameter: 'callback', 
                'success': function(data, status) {
                    if (typeof callbackSuccess == 'function') {
                        callbackSuccess(data);
                    }
                }, 
                'error': function(request, status, error) {
                    // ignore
                    // if (typeof callbackError == 'function') {
                    //     callbackError(status, error);
                    // }
                }, 
                'data': endpointParams, 
                'async': async
            });
        } else {
            if (typeof callbackError == 'function') {
                callbackError();
            }
        }
    }, 
    
    updateSources: function () {        
        // send results for all graphs
        var defaultGraph = this.getDefaultGraph();
        var defaultService = this.getDefaultUpdateEndpoint();
        var sendAlways = false;
        var useDefaults = false;
        
        if (defaultGraph && defaultService) {
            sendAlways = true;
            useDefaults = true;
        }
        
        for (graph in this.graphInfo) {            
            // get graph's update URI
            var updateUri   = this.graphInfo[graph].updateEndpoint;
            var jsonAdded   = null;
            var jsonRemoved = null;
            var instance    = this;
            
            // only proceed if graph is updateable
            if (typeof updateUri == 'string' && updateUri !== '') {
                if (this.databanks[graph]) {
                    if (this.protectedTriples[graph] instanceof $.rdf.databank) {
                        // add protected triples that are saved anyway
                        this.protectedTriples[graph].triples().each(function() {
                            instance.databanks[graph].add(this);
                        });
                    }
                    
                    if (sendAlways) {
                        // send all triples
                        var added = this.databanks[graph];
                        jsonAdded = $.rdf.dump(added.triples(), {format: 'application/json'});
                    } else {
                        var added = this.databanks[graph].except(this.originalDatabanks[graph]);
                        jsonAdded = $.rdf.dump(added.triples(), {format: 'application/json'});
                    }
                    
                    var removed = this.originalDatabanks[graph].except(this.databanks[graph]);
                    jsonRemoved = $.rdf.dump(removed.triples(), {format: 'application/json'});
                }
                
                // alert('Adding to: ' + graph + ' (' + updateUri + ')' + 
                // '\nAdded: ' + $.toJSON(jsonAdded ? jsonAdded : {}) +
                // '\nRemoved: ' + $.toJSON(jsonRemoved ? jsonRemoved : {}));
                
                if (jsonAdded || jsonRemoved) {
                    // submit only if something has changed
                    if (useDefaults) {
                        // x-domain request sending works w/ $.get only
                        $.get(defaultService, {
                            'named-graph-uri': defaultGraph, 
                            'insert': $.toJSON(jsonAdded ? jsonAdded : {}), 
                            'delete': $.toJSON(jsonRemoved ? jsonRemoved : {})
                        });
                    } else {
                        // x-domain request sending works w/ $.get only
                        $.get(updateUri, {
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
        }
        
        if (this.view) {
            this.view.hide(this.options.animated);
        }
    }, 
    
    // depending on the current edit mode adds a triple row to the edit view
    // or adds a click event to the element
    makeElementEditable: function (element, triple, graph) {
        // alert(graph + '\n' + triple);
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
                
                if (triple.predicate.uri == this.internalPredicates.defaultGraph && !this.options.defaultGraph) {
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
            var quoteLiteral = true;
            
            if (triple.object.lang) {
                objectOptions.lang = triple.object.lang;
                quoteLiteral = false;
            } else if (triple.object.datatype) {
                objectOptions.datatype = triple.object.datatype.uri;
                quoteLiteral = false;
            }
            
            if (!triple.object.uri && quoteLiteral) {
                // replace quotes
                if (String(object).match(/["]/)) {
                    object = object.replace(/["]/g, '\\\"');
                }
                
                object = '"' + object + '"';
            }
            
            try {
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
                
                // add element info
                this.elementInfo[triple] = element;
                
                // add triple to databanks
                var databank = this.getDatabank(graph);
                databank.add(rdfTriple);
                
            } catch (error) {
                // TODO: handle
                this.errors++;
            }
        }
    }, 
    
    reset: function() {
        this.pageParsed = false;
        this.databanks = {}; 
        this.defaultGraph = null; 
        this.graphInfo = {}; 
        this.tripleInfo = {}; 
        this.elementInfo = {}; 
        this.originalDatabanks = {}; 
        this.protectedTriples = {}; 
        this.inlineViews = {}; 
        this.errors = 0; 
        view = null; 
        this.widgetRegistry = {
            'resource':  {}, 
            'property':  {}, 
            'range':     {}, 
            'datatype':  {}, 
            '__object':  {},
            '__literal': {}, 
            '__default': {}
        };
    }, 
    
    // Registers a widget with the API
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
    startEditing: function (subject) {        
        var instance = this;
        if (!RDFauthor.pageParsed) {
            try {
                RDFA.parse();
            } catch (e) {
                alert('RDFa parsing error: ' + e);
            }
            
            var instance = this;
            RDFA.CALLBACK_DONE_PARSING = function () {
                // fetch predicate infos
                instance.fetchPredicateInfo();
                
                if (instance.errors > 0) {
                    alert('There where ' + instance.errors + ' errors while parsing the page. All valid triples have been extracted.');
                }
                
                var view = instance.createPropertyView(subject);
                view.display(instance.options.animated);
                RDFauthor.pageParsed = true;
            };
        } else {
            var view = this.createPropertyView(subject);
            view.display(instance.options.animated);
        }
    }, 
    
    // Starts the editing process based on a DOM element.
    // The element's content will be replaced with RDFauthor widgets for 
    // editing RDFa statements completed inside the element.
    startInline: function(element) {
        // parse page if necessary
        if (!RDFauthor.pageParsed) {
            try {
                RDFA.parse();
            } catch (e) {
                alert('RDFa parsing error: ' + e);
            }
            
            var instance = this;
            RDFA.CALLBACK_DONE_PARSING = function () {
                RDFauthor.pageParsed = true;
                
                // fetch predicate infos
                instance.fetchPredicateInfo();
                
                if (instance.errors > 0) {
                    alert('There where ' + instance.errors + ' errors while parsing the page. All valid triples have been extracted.');
                }
                
                if ($(element).length > 0) {
                    $(element).each(function() {
                        var inlineView = instance.createInlineView(this);
                        inlineView.display(instance.options.animated);
                    });
                } else if (typeof element == 'object') {
                    var inlineView = instance.createInlineView(element);
                    inlineView.display(instance.options.animated);
                }
            };
        } else {
            if ($(element).length > 0) {
                var instance = this;
                $(element).each(function() {
                    var inlineView = instance.createInlineView(this);
                    inlineView.display(instance.options.animated);
                });
            } else if (typeof element == 'object') {
                var inlineView = this.createInlineView(element);
                inlineView.display(this.options.animated);
            }
        }
    }, 
    
    // Prepares inline editing for the DOM element or
    // matched jQuery objects
    prepareInline: function (element) {
        if (!RDFauthor.pageParsed) {
            try {
                RDFA.parse();
            } catch (e) {
                alert('RDFa parsing error: ' + e);
            }
            
            var instance = this;
            RDFA.CALLBACK_DONE_PARSING = function () {
                RDFauthor.pageParsed = true;
                
                // fetch predicate infos
                instance.fetchPredicateInfo();
                
                if (instance.errors > 0) {
                    alert('There where ' + instance.errors + ' errors while parsing the page. All valid triples have been extracted.');
                }
                
                // prepare inline mode
                if (typeof element == 'string' && $(element).length > 0) {
                    $(element).each(function() {
                        var subject = $(this).closest('*[about]').attr('about');
                        // HACK: ;)
                        if (!(subject in instance.databanks)) {
                            $(this).append('<a href="javascript:RDFauthor.startEditing(\'' + subject + '\')" style="float:right">Edit</a>');
                        }
                    });
                }
            }
        }
    },
    
    // Start editing a resource based on a template
    startTemplate: function (template) {
        // HACK:
        var graph = this.getDefaultGraph();
        
        // TODO: load selection cache
        var predefinedStatements = [];
        var databank = this.getDatabank();
        
        this.protectedTriples[graph] = $.rdf.databank();
        
        if (typeof template == 'object') {
            for (var resource in template) {
                for (var property in template[resource]) {
                    var values = template[resource][property];
                    
                    for (var index = 0; index < values.length; index++) {
                        var current = values[index];
                        this.predicateInfo[property] = {};
                        
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
                            
                            if ('hidden' in current && current['hidden']) {
                                // mark it as protected (hidden), since it won't be changed
                                this.protectedTriples[graph].add(rdfTriple);
                            } else {
                                // add it to databank, so it can be changed
                                predefinedStatements.push({'s': resource, 'p': property, 'o': object, 't': current.title});
                            }
                            
                            // save title
                            if (current.title) {
                                this.predicateInfo[property]['title'] = current.title;
                            }
                        } else {
                            // inclomplete triple, keep for later
                            predefinedStatements.push({'s': resource, 'p': property, 'o': null, 't': current.title});
                        }
                    }
                }
            }
            
            this.fetchPredicateInfo();
            
            var instance = this;
            window.setTimeout(function() {
                // databank has been filled, create view
                var view = instance.createPropertyView();
                RDFauthor.pageParsed = true;

                // fill view with to-be-filled rows
                for (var i = 0; i < predefinedStatements.length; i++) {
                    var currentFragment = predefinedStatements[i];
                    var id = view.addRow(currentFragment.s, 
                                         currentFragment.p, 
                                         currentFragment.t, 
                                         currentFragment.o, 
                                         instance.getDefaultGraph());
                }

                view.display(instance.options.animated);
            }, 0);
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
// Libraries
///////////////////////////////////////////////////////////////////////////////

// hack for faster loading
__RDFA_BASE = widgetBase + 'libraries/';

// recursively load scripts
RDFauthor.loadScript([
    // jQuery plug-ins
    widgetBase + 'libraries/jquery.jsonp.js', 
    // RDFauthor modules
    widgetBase + 'src/propertyrow.js', 
    widgetBase + 'src/view.js', 
    widgetBase + 'src/propertyselector.js'
], function() {
    RDFauthor.loadScript([
        // RDFa parser
        widgetBase + 'libraries/rdfa.js', 
        // rdfquery libs
        widgetBase + 'libraries/jquery.rdfquery.core.js', 
        // widgets
        widgetBase + 'src/literaledit.js', 
        widgetBase + 'src/xmlliteral.js', 
        widgetBase + 'src/dateedit.js', 
        widgetBase + 'src/resourceedit.js', 
        widgetBase + 'src/metaedit.js',
        widgetBase + 'src/teluriedit.js',
        widgetBase + 'src/mailtouriedit.js'
    ], function() {
        if (typeof rdfauthor_loaded_callback === 'function') {
            rdfauthor_loaded_callback();
        }
    })
})

