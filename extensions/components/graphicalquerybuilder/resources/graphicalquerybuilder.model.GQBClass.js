/**
 * Represents a collection of data objects, which all belong
 * to the same RDF class.
 * @param {Object} rdfType A GQBrdfClass, indicates the type of object represented by this GQBClass.
 * @class
 * @constructs
 */
function GQBClass(rdfType){
	//vars
	this.id = GQB.model.countClassesInPatterns++; // unique for each instance!!

	this.type = rdfType;

	// An array of GQBSelectedLinks.  This must be a subset of
	// rdfType.outgoingLinks UNION rdfType.nonModelConformLinks:
	this.selectedLinks = new Array();
	// An array of GQBProperties which are "selected" to be shown in the results table.
	// This must be a subset of rdfType.properties UNION rdfType.nonModelConformProperties:
	this.selectedProperties = new Array();

	// The number of instances in the data base which conform to my type and
	// my restrictions.  This value is generally recalculated whenever some
	// change occurs to this class or its parent pattern (such as addition of
	// a restriction, expansion of pattern etc.):
	this.numInstances = 0;
	this.showUri = false;
	this.withChilds = true;

	// Restrictions are stored as a 2-level tree in CNF:
	this.restrictions = new GQBRestrictionStructure("AND", 1);
	this.restrictions.addMember(new GQBRestrictionStructure("OR", 2))

	// Marker used when performing search through query pattern
	// (should always be zero except when performing a search):
	this.hasBeenVisited = 0;

	//methods
	// Whether or not this GQBClass is completely loaded:
	this.isReady;

	// These methods retrieve the properties of my rdfType
	// from the database:
	this.getPropertiesWithInherited;
	this.getConformPropsAndNonConformLinksAndProps;
	this.getConformLinks;

	// Recalculates the number of instances of objects 
	// corresponding to this GQBClass which exist in the database:
	this.recalculateNumInstances;

	// Search methods:
	this.findPropertyByUri;
	this.findRestrictionById;
	this.findSelectedLinkByUri;

	// For adding and removing selected properties:
	this.addShownProperty;
	this.removeShownProperty;

	// For adding and removing restrictions:
	this.addRestriction;
	this.deleteRestriction;

	// Used when merging this GQBClass with another GQBClass:
	this.mergeWithClass;
	this.restrictionDisjunction;

	// Used when storing the parent GQBQueryPattern in the database,
	// or when restoring from the database:
	this.restore;
	this.toSaveable;
}

/** 
 * Determines if this GQBClass object is ready (has all properties and links).
 * @return true if ready, otherwise false.
 */
GQBClass.prototype.isReady = function() {return this.type.ready;}

/** 
 * Merges the properties of the passed class into this class.
 * The passed class remains unchanged.
 * The merging can be either a union or an intersection, as 
 * specified by mode.
 * @param otherClass the class to be merged into this class (as a copy)
 * @param mode either "union" or "intersection"
 */
GQBClass.prototype.mergeWithClass = function(otherClass, mode){
	if (otherClass.type.uri != this.type.uri) return;  // can only merge classes of same type

	// combine selected links and properties
	for(var i = 0; i < otherClass.selectedLinks.length; i++) {
		this.selectedLinks.push(otherClass.selectedLinks[i]);
	}
	for(var i = 0; i < otherClass.selectedProperties.length; i++) {
		this.addShownProperty(otherClass.selectedProperties[i].uri);
	}

	// handle restrictions
	if (mode == "union") {
		this.restrictionDisjunction(otherClass);
	}
	else if (mode == "intersection") {
		// first we make sure we don't end up with any empty restriction structures:
		if (this.restrictions.members[0].members.length <= 0 &&
				otherClass.restrictions.members[0].members.length > 0)
			this.restrictions = new GQBRestrictionStructure("AND", 1);
		// then we can simply combine the restrictions at top level (conjunction):
		for(var i = 0; i < otherClass.restrictions.members.length; i++) {
			if (otherClass.restrictions.members[i].members.length > 0)
				this.restrictions.members.push(otherClass.restrictions.members[i]);
		}
	}

	GQB.model.findPatternOfClass(this).recalculateAllNumInstances();
};

/**
 * Combines the restrictions in this class with the restrictions of the passed class
 * at top level by disjunction.
 * @param {GQBClass} otherClass the class to merge with
 */
GQBClass.prototype.restrictionDisjunction = function(otherClass){
// (a & b) | (c & d) = ((a & b) | c) & ((a & b) | d) = (a | c) & (b | c) & (a | d) & (b | d) 
// We must take each AND structure in this class and combine it's contents with
// the contents of each AND structure in the other class.
	var otherRest = otherClass.restrictions;
	var myNewRestrictions = new Array();
	var myNewAndStrct = new GQBRestrictionStructure("AND", 1);

	for(var i = 0; i < this.restrictions.members.length; i++) {
		var myCurOrStrct = this.restrictions.members[i];
		
		for(var j = 0; j < otherRest.members.length; j++) {
			var otherCurOrStrct = otherRest.members[j];
			
			var myCurNewOrStrct = new GQBRestrictionStructure("OR", 2);
			for(var x = 0; x < myCurOrStrct.members.length; x++) {
				var curMemberCopy = GQB.copyRestriction(myCurOrStrct.members[x]);
				if(curMemberCopy != null) {
					myCurNewOrStrct.addMember(curMemberCopy);
				}
			}
			for(var y = 0; y < otherCurOrStrct.members.length; y++) {
				var curMemberCopy = GQB.copyRestriction(otherCurOrStrct.members[y]);
				if(curMemberCopy != null) {
					myCurNewOrStrct.addMember(curMemberCopy);
				}
			}
			if (otherCurOrStrct.members.length != 0)
				myNewAndStrct.addMember(myCurNewOrStrct);
		}
	}
	if (myNewAndStrct.members.length == 0)
		myNewAndStrct.addMember(new GQBRestrictionStructure("OR", 2));
	this.restrictions = myNewAndStrct;
};

/**
 * Search through all of my properties looking for one with a certain uri.
 * @param {Object} uri The uri to search for.
 * @return A member type.properties or null if not found.
 */ 
GQBClass.prototype.findPropertyByUri = function(uri){
	if (!uri) return null;

	for(var i = 0; i < this.type.properties.length; i++){
		if (this.type.properties[i].uri == uri) {
			return this.type.properties[i];
		}
	}
	return null;
};

/**
 * Search for a selected link with the passed uri.
 * @param {Object} uri The uri to search for.
 * @return A member of selectedLinks or null if not found.
 */ 
GQBClass.prototype.findSelectedLinkByUri = function(uri){
	if (!uri) return null;

	for(var i = 0; i < this.selectedLinks.length; i++){
		if (this.selectedLinks[i].property.uri == uri) {
			return this.selectedLinks[i];
		}
	}
	return null;
};

/**
 * Search through my restriction structure with search key id.
 * @param {Object} id The id to search for.
 * @return A GQBRestriction or a GQBRestrictionStructure or null if nothing found.
 */ 
GQBClass.prototype.findRestrictionById = function(id){
	return this.restrictions.findRestrictionById(id);
};

/**
 * Add a property to this class that will be shown in the sparql-query and result as a column.
 * @param {Object} uri The uri of the property to add.  A new GQBProperty of this kind
 *         will be added to my selectedProperties, but only if one doesn't already exist.
 */
GQBClass.prototype.addShownProperty = function(uri){
	var theProp = this.findPropertyByUri(uri);
	if (!theProp) return;
	for (var i = 0; i < this.selectedProperties.length; i++) {
		if (this.selectedProperties[i].uri == theProp.uri) return;  // don't allow duplicates
	}
	this.selectedProperties.push(theProp);
};

/**
 * Remove a selected property that was previously added.
 * @param {Object} uri The uri of the property to remove.
 */
GQBClass.prototype.removeShownProperty = function(uri){
	var newShownProperties = new Array();
	for ( var i = 0; i < this.selectedProperties.length; i++ ) {
		if (this.selectedProperties[i].uri != uri)
			newShownProperties.push(this.selectedProperties[i]);
	}
	this.selectedProperties = newShownProperties
};

/** 
 * Adds the restriction rest to this class's restriction tree.  The index
 * given indicates the "OR" structure to which the restriction is to be
 * added.  If orIdx is undefined, then a new "OR" structure is first added
 * to the top-level "AND" structure of this GQBClass, to which rest is then added. 
 * @param rest A GQBRestriction or GQBRestrictionStructure of any kind
 * @param orIdx The index of the "OR" structure to which rest is to be added, or undefined
*/
GQBClass.prototype.addRestriction = function(rest, orIdx){
	if(!this.restrictions.members[orIdx]){
	 var tmp = new GQBRestrictionStructure("OR", 2);
	 tmp.addMember(rest);
	 this.restrictions.addMember(tmp);
	} else {
		this.restrictions.members[orIdx].addMember(rest);
	}
	GQB.model.findPatternOfClass(this).recalculateAllNumInstances();
	var gqbEvent = new GQBEvent("addedRestriction", this);
	GQB.controller.notify(gqbEvent);
};

/**
 * Remove a restriction from this class.
 * @param {Object} restrictionObj The GQBRestriction or GQBRestrictionStructure to remove.
 * @param {Object} level1Idx The index in my top level GQBRestrictionStructure ("AND") of
 *          either the GQBRestrictionStructure ("OR") containing the GQBRestriction to remove, 
 *          or the index of the GQBRestriction to remove.
 */
GQBClass.prototype.deleteRestriction = function(restrictionObj,level1Idx){
	this.restrictions.members[level1Idx].removeMember(restrictionObj);
	if(!this.restrictions.members[level1Idx].hasMember()){
		GQB.arrayRemoveObj(this.restrictions.members, this.restrictions.members[level1Idx]);
	}
	GQB.model.findPatternOfClass(this).recalculateAllNumInstances();
	var gqbEvent = new GQBEvent("deletedRestriction", this);
	GQB.controller.notify(gqbEvent);
};

/**
 * Build the sparql-query and send it to the "getquerysize" action in GraphicalquerybuilderController.php.
 * This action executes the query, counts the results and sends back the count, which is faster
 * than counting the results client side.  To avoid long delays, a limit of 1000 results is built in.
 */
GQBClass.prototype.recalculateNumInstances = function(){
	// Build query but limit it to this class and to a max of 1000 instances:
	var query = escape(GQB.model.findPatternOfClass(this).getQueryAsString(this)) + "\n LIMIT 1000";

	// send the request to the "getquerysize" service
	var url = urlBase+'graphicalquerybuilder/getquerysize/?default-graph-uri='+GQB.model.graphs[0]+'&query='+query;

	var me = this;
	$.get(url, function(data) {
		me.numInstances = parseInt(data);
		if (me.numInstances >= 1000) me.numInstances = "1000+";
		var gqbEvent = new GQBEvent("gotNumInstances", me);
		GQB.controller.notify(gqbEvent);
	});
};

/**
 * Restore a class (loading it from the db)
 * at first a dummy class is created, then this method gets invoked and overwrites everything in order to restore the old object
 * @param {Object} savedclass this is a stripped off version of the old class. all nessesary stuff is in here and needs to be copied
 * @param {Object} pattern helper because "getPatternOfClass"-function doesnt work well with restoring
 */
GQBClass.prototype.restore = function(savedclass, pattern){
	// See if the type of this class already exists:
	var foundType = GQB.model.findRDFClassByUri(savedclass.type.uri);
	if (foundType) {
		// we can use the existing type:
		this.type = foundType;
		// it may be busy getting properties,
		// in this case we don't want to disturb it:
		if (foundType.isGettingPropsOrLinks) {
			; // do nothing
		// otherwise we can restore it, but only if it hasn't
		// already gotten its properties:
		} else if (!foundType.ready) {
			foundType.restore(savedclass.type);
		} else {
			var gqbEvent = new GQBEvent("classReady", foundType);
			GQB.controller.notify(gqbEvent);
		}
	}
	// if the type isn't found, we have an error:
	else {
		alert(GQB.translate("savedClassNotInModelMsg", savedclass.type.uri));
		return;
	}

	// Restore my selected properties:
	for (var i = 0; i < savedclass.selectedProperties.length; i++) {
		var prop = this.type.findAnyPropertyByUri(savedclass.selectedProperties[i].uri)
		if (prop)
			this.selectedProperties.push(prop);
	}

	// restore my restrictions and my showUri:
	this.restrictions.restore(savedclass.restrictions);
	this.showUri = savedclass.showUri;
	this.withChilds = savedclass.withChilds;

	// restore children:
	this.selectedLinks = new Array();
	for (var i = 0; i < savedclass.selectedLinks.length; i++) {
		if (!GQB.model.findRDFClassByUri(savedclass.selectedLinks[i].target.type.uri)) {
			alert(GQB.translate("savedClassNotInModelMsg", savedclass.selectedLinks[i].target.type.uri));
			return;
		}

		var property = new GQBProperty(savedclass.selectedLinks[i].property.uri);
		for (var lang in savedclass.selectedLinks[i].property.labels) {
			property.addLabel(savedclass.selectedLinks[i].property.labels[lang], lang);
		}
		property.range = savedclass.selectedLinks[i].property.range;

		// parameter false tells the class not to get its properties from the server
		// (since they will be restored anyway)
		var nclass = pattern.add(this, property, false, savedclass.selectedLinks[i].target.type.uri);
		nclass.restore(savedclass.selectedLinks[i].target, pattern, this);
	}

	if (pattern.selectedClass.id == savedclass.id)
		pattern.setSelectedClass(this);
};

/**
 * Strips all unessasary stuff away (mostly functions) and returns a pure array of this class
 * @return An array of this class that can be JSON encoded and later be restored by .restore(thereturnedobj)
 */
GQBClass.prototype.toSaveable = function(){
	var newSelectedLinks = new Array();
	for (var i = 0; i < this.selectedLinks.length; i++) {
		newSelectedLinks.push({
			property: this.selectedLinks[i].property.toSaveable(),
			target: this.selectedLinks[i].target.toSaveable(),
			optional: this.selectedLinks[i].optional
		});
	}

	var newSelectedProperties = new Array();
	for (var i = 0; i < this.selectedProperties.length; i++)
		newSelectedProperties[i] = this.selectedProperties[i].toSaveable();

	return {
		id: this.id,
		type: this.type.toSaveable(),
		selectedProperties : newSelectedProperties,
		restrictions: this.restrictions.toSaveable(),
		selectedLinks: newSelectedLinks,
		showUri: this.showUri,
		withChilds: this.withChilds
	};
};

/** 
 * end of getting properties and non-modelconform properties+links
 *
 * @param classes An array of URIs containing all parent RDF classes to this GQBClass's RDF type
 *                as well as the URI of its own type.
 */
GQBClass.prototype.getConformPropsAndNonConformLinksAndProps = function(){
	var model = GQB.model.graphs[0];
	var getPropertiesQuery = 
		"PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> \
			PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>\
			SELECT DISTINCT ?property ?label ?order ?range\
			FROM <"+model+">\
			WHERE {\
				?property a <http://www.w3.org/2002/07/owl#DatatypeProperty> . \
				?property rdfs:domain ?type . \
				?property rdfs:label ?label . \
				?property rdfs:range ?range . \
				OPTIONAL { \
					?property <http://ns.ontowiki.net/SysOnt/order> ?order \
				} \
				FILTER(sameTerm(?type, <"+this.type.uri+">) || sameTerm(?type, <";

	// the returned URI should be a property of one of the parent classes:
	var parUris = [];for(var i=0; i < this.type.parents.length; i++) parUris[i] = this.type.parents[i].uri;
	getPropertiesQuery += parUris.join(">) || sameTerm(?type, <");
	getPropertiesQuery += ">))}";
	// this line causes problems in mySql if ?order is optional:
	//getPropertiesQuery += " ORDER BY ?order";

	// build sparql query url:
	var queryUrl = urlBase + "service/sparql";
	var me = this;
	var myType = this.type;
	// send off query:
	$.ajax({
		type: "POST",
		url: queryUrl,
		data: {query: getPropertiesQuery, "default-graph-uri" : GQB.model.graphs[0]},
		dataType: "json",
		success: function (jsonResult) {
			var modelconformPropsUris = new Array();
			if (jsonResult.bindings.length > 0) { 
				var uri;
				var label;
				var lang;
				var order;
				var exrange;

				for (var i=0; i < jsonResult.bindings.length; i++) {
					uri = jsonResult.bindings[i].property.value;
					label = jsonResult.bindings[i].label.value;
					if (jsonResult.bindings[i].range) {
						exrange = jsonResult.bindings[i].range.value.split("#").pop();
						if ( exrange == "date" || exrange == "gDay" || exrange == "gYearMonth" || exrange == "gMonth" || exrange == "gMonthDay" ) continue;
					} 
					lang = " ";
					for (var j = 0; j < GQB.supportedLangs.length; j++) {
						if (jsonResult.bindings[i].label["xml:lang"] == GQB.supportedLangs[j]) {
							lang = GQB.supportedLangs[j];
							break;
						}
					}
					order = (jsonResult.bindings[i].order != undefined ? jsonResult.bindings[i].order.value : undefined);

					var foundProp = myType.findAnyPropertyByUri(uri);
					if (foundProp) {
						foundProp.addLabel(label,lang);
					} else {
						modelconformPropsUris.push(jsonResult.bindings[i].property.value);
						myType.properties.push(new GQBProperty(uri, label, lang, (jsonResult.bindings[i].range ? jsonResult.bindings[i].range.value : "not found"), order));
					}
				}
			} else { 
				//no props found
				//alert(GQB.translate("fatalErrorNoPropsFoundMsg", myType.getLabel()));
			}
			
			me.getNonConformProps(modelconformPropsUris);
		},
                complete: function(){
                    myType.hasGottenProps = true;
			
                    if (myType.hasGottenProps && myType.hasGottenLinks && myType.hasGottenNonModelConformPropsAndLinks) {
                            myType.sortAllPropArraysByOrder();
                            myType.ready = true;
                            myType.isGettingPropsOrLinks = false;
                            var gqbEvent = new GQBEvent("classReady", myType);
                            GQB.controller.notify(gqbEvent);
                    }
                }
	});
};

/** 
 * Gets all properties of this GQBClass's RDF class, which are not model conform.
 * A list of all properties which are model conform must be provided, so that the
 * non model conform properties may be identified.
 * Links (owl#ObjectProperties) are identified by their range that is a class
 * @param classes An array of URIs containing all parent RDF classes to this GQBClass's RDF type
 *                as well as the URI of its own type.
 * @param modelconformPropsUris A list of model conform URIs.
 */
GQBClass.prototype.getNonConformProps = function(modelconformPropsUris){
	var model = GQB.model.graphs[0];
	var getNonModelConformPropertiesQuery = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> \n\
PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>\n\
SELECT DISTINCT ?uri ?label ?range \n\
FROM <"+model+"> \n\
WHERE { \n\
    ?instance ?uri ?o . \n\
    ?instance a ?type . \n\
    OPTIONAL {?o rdf:type ?range } . \n\
    OPTIONAL {?uri rdfs:label ?label} .\n\
    FILTER(!sameTerm(?uri, rdf:type) && !sameTerm(?uri, <";

	// the returned URI should not be model conform:
	getNonModelConformPropertiesQuery += modelconformPropsUris.join(">) && !sameTerm(?uri, <");
	getNonModelConformPropertiesQuery += ">))";

	// the returned URI should be a property of one of the parent classes:
	var parUris = [];for(var i=0; i < this.type.parents.length; i++) parUris[i] = this.type.parents[i].uri;
	getNonModelConformPropertiesQuery += " FILTER(sameTerm(?type, <"+this.type.uri+">) || sameTerm(?type, <";
	getNonModelConformPropertiesQuery += parUris.join(">) || sameTerm(?type, <");
	getNonModelConformPropertiesQuery += ">))}";
	// this line causes problems in mySql if ?order is optional:
	//getNonModelConformPropertiesQuery += " ORDER BY ?order";

	// build sparql query url:
	var queryUrl = urlBase + "service/sparql";
	
	var myType = this.type;
	// send off query:
	$.ajax({
		type: "POST",
		url: queryUrl,
		data: {query: getNonModelConformPropertiesQuery, "default-graph-uri" : GQB.model.graphs[0]},
		dataType: "json",
		success: function (jsonResult) {
                        //$.dump(jsonResult);
			if (jsonResult.bindings.length > 0) {
				var uri;
				var label;
				var lang;
				var order;
				var exrange;
                                for (var i=0; i < jsonResult.bindings.length; i++) {
					exrange = "";
					uri = jsonResult.bindings[i].uri.value;
                                        var extracted_label = jsonResult.bindings[i].uri.value.split('/\/#/');
                                        label = jsonResult.bindings[i].label ? jsonResult.bindings[i].label.value : extracted_label[extracted_label.length -1];
					if (jsonResult.bindings[i].range) { 
						exrange = jsonResult.bindings[i].range.value.split("#").pop();
						if ( exrange == "date" || exrange == "gDay" || exrange == "gYearMonth" || exrange == "gMonth" || exrange == "gMonthDay" ) continue;
					}
					lang = " ";
					for (var j = 0; j < GQB.supportedLangs.length; j++) {
						if (jsonResult.bindings[i].label && jsonResult.bindings[i].label["xml:lang"] == GQB.supportedLangs[j]) {
							lang = GQB.supportedLangs[j];
							break;
						}
					}
					order = (jsonResult.bindings[i].order != undefined ? jsonResult.bindings[i].order.value : undefined);

					var foundProp = myType.findAnyPropertyByUri(uri);
					if (foundProp) {
						foundProp.addLabel(label,lang);
					} else {
                                                var newProp = new GQBProperty(uri, label, lang, (jsonResult.bindings[i].range ? jsonResult.bindings[i].range.value : ""), order);
						if (jsonResult.bindings[i].range) {
                                                        myType.outgoingLinks.push(newProp);
							myType.nonModelConformLinks.push(newProp);
						} else {
							myType.properties.push(newProp);
							myType.nonModelConformProperties.push(newProp);
						}
					}
				}

			}
			
		},
                complete: function(){
                    myType.hasGottenNonModelConformPropsAndLinks = true;
                    if (myType.hasGottenProps && myType.hasGottenLinks && myType.hasGottenNonModelConformPropsAndLinks) {
                            myType.sortAllPropArraysByOrder();
                            myType.ready = true;
                            myType.isGettingPropsOrLinks = false;
                            var gqbEvent = new GQBEvent("classReady", myType);
                            GQB.controller.notify(gqbEvent);
                    }
                }
	});
};

/** 
 * Gets all model conform links of my RDF type or any parent RDF type.
 * Links are defined as owl#ObjectProperties.
 * @param classes An array of URIs containing all parent RDF classes to this GQBClass's RDF type
 *                as well as the URI of its own type.
 */
GQBClass.prototype.getConformLinks = function(){
	var model = GQB.model.graphs[0];
	var getLinksQuery = 
				"PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> \
				PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>\
				SELECT DISTINCT ?property ?label ?order ?range \
				FROM <"+model+">\
				WHERE {\
					?property a <http://www.w3.org/2002/07/owl#ObjectProperty> . \
					?property rdfs:domain ?type . \
					?property rdfs:label ?label . \
					{?x ?property ?class . ?class a ?range} UNION { ?property rdfs:range ?range }. \
					OPTIONAL { \
						?property <http://ns.ontowiki.net/SysOnt/order> ?order \
					} \
					FILTER(sameTerm(?type, <"+this.type.uri+">) || sameTerm(?type, <";

	// the returned URI should be a property of one of the parent classes:
	var parUris = [];for(var i=0; i < this.type.parents.length; i++) parUris[i] = this.type.parents[i].uri;
	getLinksQuery += parUris.join(">) || sameTerm(?type, <");
	getLinksQuery += ">))}";
	// this line causes problems in mySql if ?order is optional:
	//getLinksQuery += " ORDER BY ?order";

	// build sparql query url:
	var queryUrl = urlBase + "service/sparql";

	var myType = this.type;
	$.ajax({
		type: "POST",
		url: queryUrl,
		data: {query: getLinksQuery, "default-graph-uri" : GQB.model.graphs[0]},
		dataType: "json",
		success: function (jsonResult) {
			if (jsonResult.bindings.length > 0) {
				var rangeclass;
				var uri;
				var label;
				var lang;
				var order;
				var exrange;

				for (var i=0; i < jsonResult.bindings.length; i++) { 
					uri = jsonResult.bindings[i].property.value;
					label = jsonResult.bindings[i].label.value;
					
					lang = " ";
					for (var j = 0; j < GQB.supportedLangs.length; j++) {
						if (jsonResult.bindings[i].label["xml:lang"] == GQB.supportedLangs[j]) {
							lang = GQB.supportedLangs[j];
							break;
						}
					}
					order = (jsonResult.bindings[i].order != undefined ? jsonResult.bindings[i].order.value : undefined);

					var foundProp = myType.findAnyPropertyByUri(uri);
					if (foundProp) {
						foundProp.addLabel(label,lang);
					} else {
						myType.outgoingLinks.push(new GQBProperty(uri, label, lang, (jsonResult.bindings[i].range ? jsonResult.bindings[i].range.value : "unknown"), order));
					}
				}
			}

			
		},
                complete: function(){
                    myType.hasGottenLinks = true;
                    if (myType.hasGottenProps && myType.hasGottenLinks && myType.hasGottenNonModelConformPropsAndLinks) {
                            myType.sortAllPropArraysByOrder();
                            myType.ready = true;
                            myType.isGettingPropsOrLinks = false;
                            var gqbEvent = new GQBEvent("classReady", myType);
                            GQB.controller.notify(gqbEvent);
                    }
                }
	});
};

/**
 * Get all properties of a class as well as inherited properties
 * it is really big and hard to read. it does the following:
 * - get the parent classes of this class
 * - get the properties (owl:DatatypeProperties) of this class and of all parentClasses (because some properties might be inherited)
 * - get the properties (all predicates) without DatatypeProperties (these are all non-modelconform properties)
 * - do the above 2 steps with owl:ObjectProperties (all relations to other Objects in the model)
 */
GQBClass.prototype.getPropertiesWithInherited = function(){
	// if my type is currently already busy getting links:
	if (this.type.isGettingPropsOrLinks) return;
	this.type.isGettingPropsOrLinks = true;
	// if there is already a class of the same type in the model, then we don't need to
	// get the properties from the server:
	if (this.type.ready) {
		this.type.isGettingPropsOrLinks = false;
		var gqbEvent = new GQBEvent("classReady", this);
		GQB.controller.notify(gqbEvent);
		return;
	}
	this.getConformPropsAndNonConformLinksAndProps();
	this.getConformLinks();
};