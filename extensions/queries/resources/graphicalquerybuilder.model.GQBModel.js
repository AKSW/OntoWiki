/** 
 * GQBModel
 * represents the model-part of the mvc.
 * here are the classes and everything stored.
 * here is the db-connectivity.
 * @param {string} _graph the uri of the graph
 * @class
 */
function GQBModel(_graph){
    /**
     * here are all available rdf-classes from the db stored
     */
    this.classes = new Array();
    this.savedQueries = new Array();
    // we need to know if all rdf classes are available:
    this.hasGottenClasses = false;
    
    /**
     * here are all available rdf-classes from the db stored
     */
    this.queryPatterns = new Array();
    this.countClassesInPatterns = 0; // used for id
    this.countPatterns = 0; // used for id
    this.graphs = new Array();
    this.graphs[0] = _graph;
    
    this.lang = GQB.currLang;//"de";
    //private vars
    this.numVarsUsedByUri = new Array();
    this.varNumsOfObjectById = new Array();
    
    //methods
    this.init;
    this.findRDFClassByUri;
    this.findClassInPatternsByUri;
    this.findClassInPatternsById;
    this.findPatternOfClass;
    this.findPatternById;
    this.findRestrictionById;
    
    this.getClasses;
    this.addClass;
    this.getSavedQueries;
    
    this.addPattern;
    
    this.varNameOf;
}

/**
 * initiate loading of saved queries and get all available rdf-classes
 */
GQBModel.prototype.init = function(){
    this.getClasses();
};

/**
 * store a rdf-class
 * @param {Object} nclass
 */
GQBModel.prototype.addClass = function(uri, label, lang){
    var found = this.findRDFClassByUri(uri);
    if (found) {
        found.addLabel(label, lang);
    }
    else {
        this.classes.push(new GQBrdfClass(uri, label, lang));
    }
};

/**
 * get the GQBRdfClass Object when you only have the uri
 * @param {Object} uri
 */
GQBModel.prototype.findRDFClassByUri = function(needle){
    for (var i = 0; i < this.classes.length; i++) {
        if (this.classes[i].uri == needle) {
            return this.classes[i];
        }
    }
    return null;
};

/** searches in all patterns for a GQBClass with a given uri.  
 * @param uri the uri to search for
 * @return theclass or null if nothing found
 */
GQBModel.prototype.findClassInPatternsByUri = function(uri){
    var foundClass;
    for (var i = 0; i < this.queryPatterns.length; i++) {
        foundClass = this.queryPatterns[i].findClassByUri(uri);
        if (foundClass != null) {
            return foundClass;
        }
    }
    return null;
};

/** searches in all patterns for a GQBClass with a given id.  
 * @param id the id to search for
 * @return theclass or null if nothing found
 */
GQBModel.prototype.findClassInPatternsById = function(id){
    var foundClass = null;
    for (var i = 0; i < this.queryPatterns.length; i++) {
        foundClass = this.queryPatterns[i].findClassById(id);
        if (foundClass) {
            return foundClass;
        }
    }
    return null;
};

/** searches in all patterns for a GQBQUeryPattern with a given id.  
 * @param id the id to search for
 * @return thepattern or null if nothing found
 */
GQBModel.prototype.findPatternById = function(id){
    for (var i = 0; i < this.queryPatterns.length; i++) {
        if (this.queryPatterns[i].id == id) {
            return this.queryPatterns[i];
        }
    }
    return null;
};

/** searches in all patterns (and their classes) for a GQBRestriction with a given id.  
 * @param id the id to search for
 * @return therestriction or null if nothing found
 */
GQBModel.prototype.findRestrictionById = function(id){
    var curProp;
    for (var i = 0; i < this.queryPatterns.length; i++) {
        curProp = this.queryPatterns[i].findRestrictionById(id);
        if (curProp != null) {
            return curProp;
        }
    }
    return null;
};

/** searches in all patterns for a GQBClass and returns the parent pattern.  
 * @param nclass the class to search for
 * @return thecpattern or null if nothing found
 */
GQBModel.prototype.findPatternOfClass = function(nclass){
    for (var i = 0; i < this.queryPatterns.length; i++) {
        if (this.queryPatterns[i].findClassById(nclass.id) != null) {
            return this.queryPatterns[i];
        }
    }
    return null;
};

/** 
 * Queries  for a list of all classes in the model
 * creates a "gotClasses" Event with results in a view action which display them
 */
GQBModel.prototype.getClasses = function(){
    var getClassesQuery = "SELECT DISTINCT ?type ?label \n \
			WHERE { {?type a <http://www.w3.org/2000/01/rdf-schema#Class> } UNION {?type a <http://www.w3.org/2002/07/owl#Class> }. \n \
			?type <http://www.w3.org/2000/01/rdf-schema#label> ?label . \n " +
    "OPTIONAL {?type <http://ns.ontowiki.net/SysOnt/order> ?order} }";
    
    //set base url of SPARQL query service
    var endpoint = urlBase + "service/sparql";
    
    var me = this;
    
    $.ajax({
        url: endpoint,
        dataType: "json",
        data: {
            "default-graph-uri": this.graphs[0],
            query: getClassesQuery
        },
        success: function(jsonresult){
            for (var i = 0; i < jsonresult.bindings.length; i++) {
                var resource = jsonresult.bindings[i].type ? jsonresult.bindings[i].type.value : "";
                var label = jsonresult.bindings[i].label ? jsonresult.bindings[i].label.value : "";
                var lang = " ";
                for (var j = 0; j < GQB.supportedLangs.length; j++) {
                    if (jsonresult.bindings[i].label["xml:lang"] == GQB.supportedLangs[j]) {
                        lang = GQB.supportedLangs[j];
                        break;
                    }
                }

                me.addClass(resource, label, lang);
            }

            var getInheritanceQuery = "SELECT DISTINCT ?child ?parent \n \
                            WHERE { \n \
                             ?child <http://www.w3.org/2000/01/rdf-schema#subClassOf> ?parent . \n \
                            }";

            //set base url of SPARQL query service
            var endpoint = urlBase + "service/sparql";
            var inheritanceStructure = new Array();

            $.ajax({
                url: endpoint,
                dataType: "json",
                data: {
                    "default-graph-uri": me.graphs[0],
                    query: getInheritanceQuery
                },
                success: function(jsonresult){
                    for (var i = 0; i < jsonresult.bindings.length; i++) {
                        var parent = jsonresult.bindings[i].parent ? jsonresult.bindings[i].parent.value : "";
                        var child = jsonresult.bindings[i].child ? jsonresult.bindings[i].child.value : "";
                        inheritanceStructure.push({
                            "parent": parent,
                            "child": child
                        });
                    }

                    // calculate transitive closure of the parent-child relation:
                    for (var i = 0; i < inheritanceStructure.length; i++) {
                        var parClass = GQB.model.findRDFClassByUri(inheritanceStructure[i].parent);
                        if(parClass == null) {
                            continue;
                        }
                        var childClass = GQB.model.findRDFClassByUri(inheritanceStructure[i].child);
                        if(childClass == null) {
                            continue;
                        }

                        parClass.children.push(childClass);

                        if(!childClass.directParent) childClass.directParent = parClass;
                        childClass.parents.push(parClass);

                    }
                    var change = false;
                    do {
                        change = false;
                        for (var i = 0; i < GQB.model.classes.length; i++) {
                            for (var j = 0; j < GQB.model.classes.length; j++) {
                                if (i == j)
                                    continue;
                                var parClass = GQB.model.classes[i];
                                var childClass = GQB.model.classes[j];
                                var parIsParOfChild = false;
                                for (var c = 0; c < parClass.children.length; c++) {
                                    if (parClass.children[c].uri == childClass.uri) {
                                        parIsParOfChild = true;
                                        break;
                                    }
                                }
                                for (var c = 0; c < childClass.parents.length && !parIsParOfChild; c++) {
                                    if (childClass.parents[c].uri == parClass.uri) {
                                        parIsParOfChild = true;
                                        break;
                                    }
                                }
                                if (!parIsParOfChild)
                                    continue;
                                for (var s = 0; s < parClass.parents.length; s++) {
                                    var foundInChild = false;
                                    for (var t = 0; t < childClass.parents.length; t++) {
                                        if (childClass.parents[t].uri == parClass.parents[s].uri) {
                                            foundInChild = true;
                                            break;
                                        }
                                    }
                                    if (!foundInChild) {
                                        childClass.parents.push(GQB.model.findRDFClassByUri(parClass.parents[s].uri));
                                        change = true;
                                    }
                                }
                                for (var s = 0; s < childClass.children.length; s++) {
                                    var foundInPar = false;
                                    for (var t = 0; t < parClass.children.length; t++) {
                                        if (childClass.children[s].uri == parClass.children[t].uri) {
                                            foundInPar = true;
                                            break;
                                        }
                                    }
                                    if (!foundInPar) {
                                        parClass.children.push(GQB.model.findRDFClassByUri(childClass.children[s].uri));
                                        change = true;
                                    }
                                }
                            }
                        }
                    }
                    while (change);

                },
                complete: function(){
                    //now load query (open link from listquery)
                    if (GQB.toload)
                        me.loadquery(GQB.toload);

                    me.hasGottenClasses = true;
                    var gqbEvent = new GQBEvent("gotClasses", me.classes);
                    GQB.controller.notify(gqbEvent);
                }
            });
        },
        complete: function(){
            //
            if (GQB.toload)
                me.loadquery(GQB.toload);

            me.hasGottenClasses = true;
            var gqbEvent = new GQBEvent("gotClasses", me.classes);
            GQB.controller.notify(gqbEvent);
        }
    });
};

/**
 * Queries for a list of all saved queries
 * creates a "gotQueries" Event with results in a view action which display them
 */
GQBModel.prototype.getSavedQueries = function(){
    if (!this.hasGottenClasses) {
        setTimeout("GQB.model.getSavedQueries()", 1000);
        return;
    }
    var getSavedQueriesQuery = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> \
			PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#> \
			SELECT ?query ?name ?desc ?type ?typelabel ?saveId\
			WHERE { ?query rdf:type <" + GQB.patternClassName + "> . \
			?query <" +
    GQB.patternName +
    "> ?name .\
			OPTIONAL {?query <" +
    GQB.patternDesc +
    "> ?desc }.\
			?query <" +
    GQB.patternType +
    "> ?type .\
			?query <" +
    GQB.patternSaveId +
    "> ?saveId .\
			?query <" +
    GQB.patternTypeLabel +
    "> ?typelabel \
			} \
			ORDER BY ?type";
    
    //set base url of SPARQL query service
    var endpoint = urlBase + "service/sparql";
    
    var me = this;
    
    $.ajax({
        url: endpoint,
        data: {
            "default-graph-uri": GQB.userDbUri,
            query: getSavedQueriesQuery
        },
        dataType: "json",
        success: function(jsonresult){
            //delete previous list of queries
            me.savedQueries.length = 0;
            for (var i = 0; i < jsonresult.bindings.length; i++) {
                var query = (jsonresult.bindings[i].query) ? jsonresult.bindings[i].query.value : " ";
                var name = (jsonresult.bindings[i].name) ? jsonresult.bindings[i].name.value : "Query";
                var desc = (jsonresult.bindings[i].desc) ? jsonresult.bindings[i].desc.value : " ";
                var type = (jsonresult.bindings[i].type) ? jsonresult.bindings[i].type.value : undefined;
                var typelabel = (jsonresult.bindings[i].typelabel) ? jsonresult.bindings[i].typelabel.value : " ";
                var id = (jsonresult.bindings[i].saveId) ? jsonresult.bindings[i].saveId.value : undefined;
                me.savedQueries.push(new GQBQueryPatternPre(name, desc, type, id));
            }


        },
        complete: function(){
            var gqbEvent = new GQBEvent("gotQueries", me.savedQueries);
            GQB.controller.notify(gqbEvent);
        }
    });
};

GQBModel.prototype.loadquery = function(uri){
    var getSavedQueryQuery = "SELECT ?name ?desc ?type ?typelabel ?json \n\
 FROM <" + GQB.userDbUri + ">\
 FROM <" +
    GQB.selectedModelUri +
    ">\
WHERE { \n\
?uri a <" +
    GQB.patternClassName +
    "> . \n\
OPTIONAL {?uri <" +
    GQB.patternName +
    "> ?name} .\n\
OPTIONAL {?uri <" +
    GQB.patternDesc +
    "> ?desc} .\n\
OPTIONAL {?uri <" +
    GQB.patternType +
    "> ?type } . \n\
OPTIONAL {?uri <" +
    GQB.patternTypeLabel +
    "> ?typelabel } . \n\
OPTIONAL {?uri <" +
    GQB.patternJson +
    "> ?json} .\n\
FILTER(sameTerm(?uri, <" +
    GQB.toload +
    ">))\
}";
    
    //set base url of SPARQL query service
    var endpoint = urlBase + "service/sparql";
    
    var me = this;
    
    $.getJSON({
        url: endpoint,
        data: {
            query: getSavedQueryQuery
        },
        dataType: "json",
        success: function(jsonresult){
            if (result == "") {
                //no db exist yet
                return;
            }

            //delete previous list of queries
            me.savedQueries.length = 0;
            for (var i = 0; i < jsonresult.bindings.length; i++) {
                var json = (jsonresult.bindings[i].json) ? jsonresult.bindings[i].json.value : " ";
                var name = (jsonresult.bindings[i].name) ? jsonresult.bindings[i].name.value : "Query";
                var desc = (jsonresult.bindings[i].desc) ? jsonresult.bindings[i].desc.value : " ";
                var type = (jsonresult.bindings[i].type) ? jsonresult.bindings[i].type.value : undefined;
                var typelabel = (jsonresult.bindings[i].typelabel) ? jsonresult.bindings[i].typelabel.value : " ";

                var canPos = GQB.getPositionOfDOMObj("gqbcanvas");
                GQB.view.dropCursorPosX = canPos.x+100;
                GQB.view.dropCursorPosY = canPos.y+100;

                var savedpattern = eval(" ( " + json + " ) ");
                var dummypattern = new GQBQueryPattern();


                GQB.model.addPattern(dummypattern);
                dummypattern.restore(savedpattern);
                // as a graphical effect, show the newly restored pattern being expanded:
                //GQB.view.findPatternById(dummypattern.id).toBlackBox(); // contract first
                // comment out the following line if the restored pattern shouldn't automatically expand:
                GQB.view.findPatternById(dummypattern.id).fromBlackBox();
            }
        }
    });
}

/**
 * add a pattern and notify the controller
 * @param {Object} pattern
 */
GQBModel.prototype.addPattern = function(pattern, frominit){
    this.queryPatterns.push(pattern);
    frominit = false; //Debug
    var gqbEvent = new GQBEvent(!frominit ? "newPattern" : "newPatternInit", pattern);
    GQB.controller.notify(gqbEvent);
};

/**
 * helper function for getting variable-names of GQBClass and GQBProperty
 * @param {Object} obj
 */
GQBModel.prototype.varNameOf = function(obj){
    var rawName;
    if (obj.type != undefined) {
        // is a GQBClass
        rawName = obj.type.getLabel();
    }
    else {
        // is a GQBProperty
        rawName = obj.getLabel();
    }
    if (!rawName) 
        return;
    rawName = rawName.toLowerCase();
    var varName = "";
    
    for (var i = 0; i < rawName.length; i++) {
        if ((rawName[i] >= 'a' && rawName[i] <= 'z') || (rawName[i] >= '0' && rawName[i] <= '9')) {
            varName += rawName[i];
        }
        else {
            if (rawName[i] == 'ä') {
                varName += "ae";
            }
            if (rawName[i] == 'ö') {
                varName += "oe";
            }
            if (rawName[i] == 'ü') {
                varName += "ue";
            }
            if (rawName[i] == 'ß') {
                varName += "ss";
            }
        }
    }
    
    varName = varName.split("/\W/").join().toLowerCase();
    if (obj.id == undefined) {
        return varName;
    }
    
    if (this.varNumsOfObjectById[obj.id] == undefined) {
        if (this.numVarsUsedByUri[obj.type.uri] == undefined) {
            this.numVarsUsedByUri[obj.type.uri] = 0;
        }
        this.varNumsOfObjectById[obj.id] = ++this.numVarsUsedByUri[obj.type.uri];
    }
    return varName + this.varNumsOfObjectById[obj.id];
};
