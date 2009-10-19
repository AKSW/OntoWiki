/**
 * Represents a Sparql-Query
 * has methods to extend the query and get the query-string and fetch results
 * @class
 */
function GQBQueryPattern(){
    this.id = GQB.model.countPatterns++;
    
    /**
     * pointer to the first class in the "chain"
     */
    this.startClass;
    
    /**
     * pointer to the class that this queries represents most
     * this has no sparql equivalent - used to use this pattern as a blackbox
     */
    this.selectedClass;
    
    /**
     * the current sparql-query as a string
     */
    this.sparqlQuery;
    
    this.description = "";
    this.name = "default";
    this.saveId = 1;
    this.isFromDb = false;
    this.distinct = false;
    
    //private variables used when constructing the query:
    this.selectPart = "";
    this.wherePart = "";
    this.filterPart = "";
    this.query = "";
    this.result;
    this.numInstances = 0;
    
    //methods
    this.setStartClass;
    this.setSelectedClass;
    
    this.getClassesFlat;
    this.findClassByUri;
    this.findClassById;
    this.findPropertyByUri;
    this.findRestrictionById;
    
    this.getResults;
    this.getQueryAsString;
    this.buildQuery;
    this.add;
    this.remove;
    
    this.allClassesHaveGottenProps;
    this.recalculateAllNumInstances;
    this.recalculateNumInstances;
    
    this.save;
    this.checkIfNameAvailableAndSave;
    this.toSaveable;
    this.restore;
}

/**
 * Checks all classes of this pattern to have gotten Properties
 * @return True if all classes in this pattern are ready with properties and links,
 *         false otherwise.
 */
GQBQueryPattern.prototype.allClassesHaveGottenProps = function(){
    var classes = this.getClassesFlat();
    for (var i = 0; i < classes.length; i++) {
        if (!classes[i].isReady()) 
            return false;
    }
    return true;
};

/**
 * Recalculates the number of instances for each class in this pattern
 * and for the pattern itself.
 */
GQBQueryPattern.prototype.recalculateAllNumInstances = function(){
    this.recalculateNumInstances();
    //var classes = this.getClassesFlat();
    //for (var i = 0; i < classes.length; i++) {
    // we used to calculate the num instances for each class
    // seperately, using the idea of a "limit class", but this
    // seems to be somewhat useless:
    //classes[i].recalculateNumInstances();
    // instead we just set the numInstances for all classes
    // to the numInstances of the pattern (done in the callback fctn):
    //}
};

/**
 * Recalculates the number of results for this pattern.
 */
GQBQueryPattern.prototype.recalculateNumInstances = function(){
    // set to "..." before numInstances retrieved:
    var classes = this.getClassesFlat();
    for (var i = 0; i < classes.length; i++) {
        classes[i].numInstances = "...";
    }
    this.numInstances = "...";
    
    // build query
    var query = escape(this.getQueryAsString()) + "\nLIMIT 1000";
    
    // send the request to the "getquerysize" service
    var url = urlBase + 'graphicalquerybuilder/getquerysize/?default-graph-uri=' + GQB.model.graphs[0] + '&query=' + query;
    
    var me = this;
    $.get(url, function(data){
        me.numInstances = parseInt(data);
        if (me.numInstances >= 1000) 
            me.numInstances = "1000+";
        var classes = me.getClassesFlat();
        for (var i = 0; i < classes.length; i++) {
            classes[i].numInstances = me.numInstances;
        }
    });
};

/**
 * change the "selectedClass" and notify controller
 * @param {Object} nclass
 */
GQBQueryPattern.prototype.setSelectedClass = function(nclass){
    this.selectedClass = nclass;
    var gqbEvent = new GQBEvent("setSelectedClass", nclass);
    GQB.controller.notify(gqbEvent);
};

/**
 * Sets the "startClass" if none already exists and notifies the controller.
 * @param {Object} nclass The new start class.
 */
GQBQueryPattern.prototype.setStartClass = function(nclass){
    if (this.startClass) 
        return;
    if (!nclass) 
        return;
    
    this.startClass = nclass;
    this.selectedClass = nclass;
    
    this.recalculateAllNumInstances();
    
    var pair = [this, nclass];
    var gqbEvent = new GQBEvent("expandedPattern", pair);
    GQB.controller.notify(gqbEvent);
};

/**
 * The classes are stored as a tree
 * this might be unhandy when you just want to quickly iterate over them
 * so this functions returns an array of all classes.
 *
 * mode of operation:
 * Do a depth first search through the pattern graph (searching for "uri").
 * Our graph structure will probably contain no loops, but just to be sure
 * we utilize the variable "GQBClass.hasBeenVisited" to keep track of visited classes
 * during the search.  This also prevents us from searching classes multiple times (in
 * case one class has the same neighbor for several different links)
 * @return array of all classes
 */
GQBQueryPattern.prototype.getClassesFlat = function(){
    if (!this.startClass) 
        return [];
    var classArray = new Array();
    var classStack = new Array(); // the stack for our depth first search
    var curClass;
    
    // add "startClass" to the stack
    classStack.push(this.startClass);
    this.startClass.hasBeenVisited = 1;
    classArray.push(this.startClass);
    
    // loop until the stack is empty
    do {
        curClass = classStack.pop();
        
        // add all unvisited neighbors to the stack and mark them as visited
        for (var i = 0; i < curClass.selectedLinks.length; i++) {
            if (curClass.selectedLinks[i].target.hasBeenVisited == 0) {
                classStack.push(curClass.selectedLinks[i].target);
                classArray.push(curClass.selectedLinks[i].target);
                curClass.selectedLinks[i].target.hasBeenVisited = 1;
            }
        }
    }
    while (classStack.length > 0);
    
    // reset "hasBeenVisited"-state for all visited classes
    for (var i = 0; i < classArray.length; i++) {
        classArray[i].hasBeenVisited = 0;
    }
    return classArray;
};

/**
 * get class with a given uri that is somewhere in this pattern
 * @param {Object} uri
 * @return thclass or null if not found
 */
GQBQueryPattern.prototype.findClassByUri = function(uri){
    var classes = this.getClassesFlat();
    
    for (var i = 0; i < classes.length; i++) {
        // here is the search condition:
        if (classes[i].type.uri == uri) {
            return classes[i];
        }
    }
    return null;
};

/**
 * get class with a given id that is somewhere in this pattern
 * @param {Object} id
 * @return thclass or null if not found
 */
GQBQueryPattern.prototype.findClassById = function(id){
    var classes = this.getClassesFlat();
    
    for (var i = 0; i < classes.length; i++) {
        // here is the search condition:
        if (classes[i].id == id) {
            return classes[i];
        }
    }
    return null;
};

/**
 * find a property of a class in all classes of this pattern
 * @param {string} uri uri of the property
 * @see GQBClass#findPropertyByUri
 */
GQBQueryPattern.prototype.findPropertyByUri = function(uri){
    var classes = this.getClassesFlat();
    var result = null;
    
    for (var i = 0; i < classes.length; i++) {
        result = classes[i].findPropertyByUri(uri);
        if (result != null) {
            break;
        }
    }
    return result;
};

/**
 * find a restriction of a class in all classes of this pattern
 * @param {int} id
 * @see GQBClass#findRestrictionById
 */
GQBQueryPattern.prototype.findRestrictionById = function(id){
    var classes = this.getClassesFlat();
    var result = null;
    
    for (var i = 0; i < classes.length; i++) {
        result = classes[i].findRestrictionById(id);
        if (result != null) {
            break;
        }
    }
    return result;
};

/**
 * Expand this pattern by adding a new GQBClass.
 * @param {Object} where The GQBClass where the new Class should be added (the leaf of the tree).
 * @param {Object} how The GQBProperty (outgoingLink) by which it shoud be expanded (the new class is determined by the "range"(datatype) of this property)
 * @param {boolean} getProps Set false if the add is invoked during a restore (properties dont need to be fetched there because they are saved and resotred too)
 * @param {String} whattype The URI of the GQBClass to add.  If omitted, the range of "how" will be used instead.
 */
GQBQueryPattern.prototype.add = function(where, how, getProps, whattype){
    if (!how) {
        alert(GQB.translate("addToPatHowErrorMsg"))
        return null;
    }
    if (!whattype) {
        whattype = GQB.model.findRDFClassByUri(how.range);
    }
    else 
        if (!whattype.uri) {
            whattype = GQB.model.findRDFClassByUri(whattype);
        }
    if (!whattype) {
        alert(GQB.translate("addToPatLinkErrorMsg"))
        return null;
    }
    
    var what = new GQBClass(whattype);
    if (getProps != false) 
        what.getPropertiesWithInherited();
    
    if (where != null) {
        where.selectedLinks.push(new GQBSelectedLink(how, what));
    }
    if (this.startClass == undefined) {
        this.setStartClass(what);
    }
    if (this.selectedClass == undefined) {
        this.setSelectedClass(what);
    }
    
    this.recalculateAllNumInstances();
    
    var triple = [this, what, where];
    var gqbEvent = new GQBEvent("expandedPattern", triple);
    GQB.controller.notify(gqbEvent);
    
    return what;
};

/**
 * Remove a class from the pattern.
 * @param {Object} where The parent GQBClass of the class to remove,
 must be in this pattern and cannot be the start class.
 * @param {Object} what The GQBClass to remove, must be in this pattern.
 */
GQBQueryPattern.prototype.remove = function(where, what){
    if (!where || !what || !this.findClassById(where.id) || !this.findClassById(what.id)) 
        return;
    if (what.id == this.startClass.id) 
        return;
    // find which link connects parent and child:
    var selectedLinkToRemove = null;
    for (var i = 0; i < where.selectedLinks.length; i++) {
        if (where.selectedLinks[i].target.id == what.id) {
            selectedLinkToRemove = where.selectedLinks[i];
            break;
        }
    }
    if (!selectedLinkToRemove) 
        return;
    // remove the selectedLink from the parent class (effectively
    // "deleting that class and all subclasses, since no 
    // links to them will remain):
    GQB.arrayRemoveObj(where.selectedLinks, selectedLinkToRemove);
    
    // see if the selected class was deleted:
    var hasSelectedClass = false;
    var classesRemaining = this.getClassesFlat();
    for (var i = 0; i < classesRemaining.length; i++) {
        if (this.selectedClass && classesRemaining[i].id == this.selectedClass.id) {
            hasSelectedClass = true;
            break;
        }
    }
    // if the selected class was deleted or there is no selected class,
    // set it to "where" (which remains in the pattern because
    // "where" is not the start class)
    if (!hasSelectedClass) {
        this.setSelectedClass(where);
    }
    
    this.recalculateAllNumInstances();
    GQB.controller.notify(new GQBEvent("removedClassFromPattern", ""));
};

/**
 * Private helper function used for building the query string.
 * @param {GQBClass} limitClass stop building the query here
 */
GQBQueryPattern.prototype.buildQuery = function(limitClass){
    var curClass;
    var curClassVar;
    var curPropVar;
    var classStack = new Array(); // the stack for our depth first search
    var vistedClasses = new Array(); // keeps track of all visited classes
    // add "startClass" to the stack
    classStack.push(this.startClass);
    this.startClass.hasBeenVisited = 1;
    vistedClasses.push(this.startClass);
    if (this.distinct) {
        this.selectPart = "DISTINCT ";
    }
    
    if (this.startClass.type.children.length == 0 || !this.startClass.withChilds) 
        this.wherePart = "?" + GQB.model.varNameOf(this.startClass) + " rdf:type <" + this.startClass.type.uri + "> .\n";
    else {
        this.wherePart = "{?" + GQB.model.varNameOf(this.startClass) + " rdf:type <" + this.startClass.type.uri + "> }\n";
    }
    
    if (this.startClass.withChilds) {
        for (var i = 0; i < this.startClass.type.children.length; i++) {
            this.wherePart += " UNION {?" + GQB.model.varNameOf(this.startClass) + " rdf:type <" + this.startClass.type.children[i].uri + "> } \n";
        }
    }
    
    // loop until the stack is empty
    do {
        curClass = classStack.pop();
        
        // here is the action:
        curClassVar = GQB.model.varNameOf(curClass);
        
        // if user selected no properties of this class
        // or if he explicitly wants to see the uri of objects of this class
        // show it
        if (curClass.selectedProperties.length == 0 || curClass.showUri || this.startClass.id == curClass.id) {
            this.selectPart += "?" + curClassVar + " ";
        }
        
        //process selectProperties of this class
        for (var i = 0; i < curClass.selectedProperties.length; i++) {
            curPropVar = "?" + curClassVar + "_" + GQB.model.varNameOf(curClass.selectedProperties[i]);
            
            // add vars of properties that should be displayed in the result
            this.selectPart += curPropVar + " ";
            
            // bind the vars of selected (shown) properties
            if (curClass.selectedProperties[i].uri == "http://www.w3.org/2000/01/rdf-schema#label") {
                this.wherePart += "OPTIONAL { ?" + curClassVar + " rdfs:label " + curPropVar + " } . \n";
            }
            else {
                this.wherePart += "OPTIONAL { ?" + curClassVar + " <" + curClass.selectedProperties[i].uri + "> " + curPropVar + " } . \n";
            }
            
            this.filterPart += " (LANG(" + curPropVar + ") = \"" + GQB.model.lang + "\" || LANG(" + curPropVar + ") = \"\") && ";
        }
        
        //process restrictions of this class
        // add the "WHERE" parts of each restriction belonging to this class:
        this.wherePart += curClass.restrictions.toWhereString("?" + curClassVar);
        
        // add the "FILTER" parts of each restriction belonging to this class:
        // (avoiding some troublesome cases that occur from time to time)
        var curFilterPart = curClass.restrictions.toFilterString("?" + curClassVar);
        if (curFilterPart != "()" && curFilterPart != "(&&)" && curFilterPart != "" &&
        curFilterPart != "( )" &&
        curFilterPart != "( && )") {
            this.filterPart += curFilterPart;
            this.filterPart += " && ";
        }
        
        //process selectedLinks of this class
        // add the condition for links from this class to one of its neighbors:
        //   - the variable name of the neighbor class receives a suffix based upon its position in the neighbor array
        for (var i = 0; i < curClass.selectedLinks.length; i++) {
            if (curClass.selectedLinks[i].optional) {
                this.wherePart += "OPTIONAL {\n";
            }
            this.wherePart += "?" + curClassVar + " <" + curClass.selectedLinks[i].property.uri + "> ?" + GQB.model.varNameOf(curClass.selectedLinks[i].target) + " . \n";
            
            if (curClass.selectedLinks[i].target.type.children.length == 0 || !curClass.selectedLinks[i].target.withChilds) 
                this.wherePart += "?" + GQB.model.varNameOf(curClass.selectedLinks[i].target) + " rdf:type <" + curClass.selectedLinks[i].target.type.uri + "> . ";
            else {
                this.wherePart += "{?" + GQB.model.varNameOf(curClass.selectedLinks[i].target) + " rdf:type <" + curClass.selectedLinks[i].target.type.uri + "> } \n";
            }
            
            if (curClass.selectedLinks[i].target.withChilds) {
                for (var j = 0; j < curClass.selectedLinks[i].target.type.children.length; j++) {
                    this.wherePart += "UNION {?" + GQB.model.varNameOf(curClass.selectedLinks[i].target) + " rdf:type <" + curClass.selectedLinks[i].target.type.children[j].uri + "> }\n";
                }
            }
            
            if (curClass.selectedLinks[i].optional) {
                this.wherePart += "}\n";
            }
        }
        
        //END of action 
        
        // add all neighbors of the current class to the stack and mark them as visited
        // unless the current class is the "limit" class:
        
        if (limitClass == undefined || curClass != limitClass) { // this check can be performed using the classIDs
            for (var i = 0; i < curClass.selectedLinks.length; i++) {
                if (curClass.selectedLinks[i].target.hasBeenVisited == 0) {
                    classStack.push(curClass.selectedLinks[i].target);
                    curClass.selectedLinks[i].target.hasBeenVisited = 1;
                    
                    // keep track of which classes were visited
                    vistedClasses.push(curClass.selectedLinks[i].target);
                }
            }
        }
        
    }
    while (classStack.length > 0);
    
    for (var i = 0; i < vistedClasses.length; i++) {
        vistedClasses[i].hasBeenVisited = 0;
    }
};

/** 
 * Creates a query string out of this pattern starting with "startClass".
 * (Should not be called asynchronously.)
 * @param limitClass building the query will stop if this class is ever reached. if undefined, no limit
 * @return a string representation of this pattern's query
 */
GQBQueryPattern.prototype.getQueryAsString = function(limitClass){
    // clear previous var names, so we don't end up with "professor15" or something:
    GQB.model.varNumsOfObjectById = new Array();
    GQB.model.numVarsUsedByUri = new Array();
    this.selectPart = "";
    this.wherePart = "";
    this.filterPart = "";
    this.buildQuery(limitClass);
    if (this.filterPart != "") {
        // add "FILTER()" and remove the last "&& " from the filter string
        this.filterPart = "FILTER( " + this.filterPart.substr(0, this.filterPart.length - 3) + ")";
    }
    
    this.sparqlQuery = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> \n\
		PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#> \n\
		SELECT " + this.selectPart + " \nFROM <" + GQB.model.graphs[0] + "> \nWHERE { \n" + this.wherePart + this.filterPart + "}";
    return this.sparqlQuery;
};

/**
 * Builds a query and sends it off to the sparqlClient.
 * A callback function is called when the results are
 * ready (asynchronous).
 */
GQBQueryPattern.prototype.getResults = function(){
    jQuery.post(urlBase + "graphicalquerybuilder/getresulttable", {
        query: this.getQueryAsString()
    }, function(table){
        var gqbEvent = new GQBEvent("gotResult", table);
        GQB.controller.notify(gqbEvent);
    });
};

/**
 * when saving the id must be unique
 * this function checks if the current id is available. if yes - save, if no change id and invoke itself
 */
GQBQueryPattern.prototype.checkIfNameAvailableAndSave = function(){
    if (this.name == "") {
        alert(GQB.translate("savePatNoNameMsg"));
        return;
    }
    
    // if this is an update - name can be kept
    if (this.isFromDb) {
        this.save();
        return;
    }
    
    //check if name available
    var getSavedQueriesQuery = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>" +
    "SELECT DISTINCT ?pattern " +
    " WHERE { ?pattern rdf:type <" +
    GQB.patternClassName +
    "> . " +
    "?pattern <" +
    GQB.patternSaveId +
    "> ?id . " +
    "FILTER ( str(?id) = str(" +
    this.saveId +
    ")) }";
    
    //set base url of SPARQL query service
    var endpoint = urlBase + "service/sparql";
    
    //configure the default graph(s) to query
    var graph = GQB.userDbUri;
    
    var me = this;
    $.ajaxSetup({
        'beforeSend': function(xhr){
            xhr.setRequestHeader("Accept", "application/sparql-results+json")
        }
    })
    jQuery.post(endpoint, {
        "default-graph-uri": graph,
        query: getSavedQueriesQuery
    }, function(result){
        if (result == "") {
            // db not existing yet
            me.save();
            return;
        }
        try {
            var jsonresult = eval(" ( " + result + " ) ");
        } 
        catch (e) {
            alert(GQB.translate("noIdAvailableErrorMsg"));
        }
        
        if (jsonresult.bindings.length == 0) {
            //ok
            me.save();
        }
        else {
            // change name
            if (me.saveId < 2) {
                me.saveId = 2;
            }
            else {
                //exponential backoff
                me.saveId = Math.floor(Math.random() * (me.saveId * me.saveId)) + 3;
            }
            me.checkIfNameAvailableAndSave();
        }
    });
};

/**
 * restore the pattern
 * overwrite everthing with the given object that comes from the database
 * @param savedobj
 */
GQBQueryPattern.prototype.restore = function(savedobj){
    this.name = savedobj.name;
    
    // TEMPORARILY set my selectedClass variable to the old ID (!)
    // of the selected class (id which was saved), so that it can
    // be identified in "restore()"... (selectedClass usually stores
    // a GQBClass)
    this.selectedClass = {
        "id": savedobj.selectedClass
    };
	if(!this.startClass)
		this.startClass = new GQBClass();
	
    this.startClass.restore(savedobj.startClass, this);
    
    this.description = unescape(savedobj.description);
    this.distinct = savedobj.distinct;
    this.isFromDb = true;
};

/**
 * returns the object without functions and unnessary stuff
 * @return the object without functions and unnessary stuff
 */
GQBQueryPattern.prototype.toSaveable = function(){
    return {
        id: this.id,
        name: this.name,
        description: escape(this.description),
        startClass: this.startClass.toSaveable(),
        selectedClass: this.selectedClass.id,
        distinct: this.distinct
    };
};

/**
 * saves the pattern in the "user query db"
 */
GQBQueryPattern.prototype.save = function(){
    if (this.name == "") {
        alert(GQB.translate("savePatNoNameMsg"));
        return;
    }
    
    var patternStr = $.toJSON(this.toSaveable());
    var typeuri = this.selectedClass.type.uri;
    var typeLabel = this.selectedClass.type.getLabel();
    var queryStr = this.getQueryAsString();
    
    var postdata = { // POST data
        json: patternStr,
        name: this.name,
        qdesc: this.description,
        type: typeuri,
        typelabel: typeLabel,
        query: queryStr,
		generator: "gqb",
		share: $("#savequerysharecheckbox").is(':checked') ? "true" : "false"
    };
    
    var me = this;
    $.post(urlBase + "querybuilding/savequery", 
		postdata, 
		function(result){
        if (result == "All OK") {
            var gqbEvent = new GQBEvent("saved", me);
            GQB.controller.notify(gqbEvent);
        }
        else {
            var gqbEvent = new GQBEvent("cantSave", result);
            GQB.controller.notify(gqbEvent);
        }
    });
    
    
};

/**
 * Helper Class for QueryPatterns that are saved and not yet loaded
 * @class
 */
function GQBQueryPatternPre(_name, _desc, _type, _saveId){
    this.name = _name;
    this.desc = _desc;
    this.type = GQB.model.findRDFClassByUri(_type);
    this.saveId = _saveId;
}

/**
 * delete a pattern that is not loaded from the db
 */
GQBQueryPatternPre.prototype.deletefromdb = function(){
    var me = this;
    $.get(urlBase + "graphicalquerybuilder/deletepattern?id=" + this.saveId, function(result){
        if (result = "All OK") {
            var gqbEvent = new GQBEvent("deleted", me);
            GQB.controller.notify(gqbEvent);
        }
        else {
            //alert(GQB.translate("errorDeletingQueryFromDBMsg"));
        }
        GQB.model.getSavedQueries();
    });
};
