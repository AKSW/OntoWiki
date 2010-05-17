/**
 * the GQB is a browser for rdf data (or in the trivial case you use it like a common db search mask)
 * it has a startClass (which links to other classes (e.g. "hasSon"), who link to others(e.g. "hasDog"), and so on - a chained list) 
 * classes can have restrictions over their properties
 * (which results in a smaller number of instances matching these rules)
 * the result is the set of instances matching the restrictions 
 * of the last class in the chain of classes
 * 
 * 
 * Info for javascript noobs: 
 * 
 * function Name(){
 *  this.do;
 * }
 * 
 * is used as a class declaration here
 * 
 * Name.prototype.do = function(){doIt();}
 * 
 * function declarations need to be added after class declaration
 */

/**
 * represents a rdf-class
 * @param {Object} _uri
 * @param {Object} _label
 * @class
 */
function GQBrdfClass(_uri, _label, _lang){
	if (!_uri) _uri = "";
	if (!_label) _label = "";
	if (!_lang) _lang = " ";

	//var
	this.labels = {};
	if(_lang && _label)
		this.labels[_lang] = _label;
	this.uri = _uri;

	this.parents = new Array();
	this.children = new Array();
	/** all available owl:datatypeProperties of that class */
	this.properties = new Array();   
	/** all predicates of instances that are no datatypeProperties */      
	this.nonModelConformProperties = new Array();
	/** all owl:objectProperties of this class */
	this.outgoingLinks = new Array();
	/** all predicates whos range starts with "http" and is not an objectProperty */
	this.nonModelConformLinks = new Array();

	this.isGettingPropsOrLinks = false;
	this.hasGottenProps = false;
	this.hasGottenLinks = false;
	this.hasGottenNonModelConformPropsAndLinks = false;
	
	/** true if all properties and links are present yet (db returned result) */
	this.ready = false;

	// methods
	this.getLabel;
	this.addLabel;
	this.findAnyPropertyByUri;
	this.sortPropsByOrder;
	this.toSaveable;
	this.restore;
}

/**
 * @return stripped off all functions
 */
GQBrdfClass.prototype.toSaveable = function() {
	var newProperties = new Array();
	for (var i = 0; i < this.properties.length; i++)
		newProperties[i] = this.properties[i].toSaveable();
	var newNonModelConformProperties = new Array();
	for (var i = 0; i < this.nonModelConformProperties.length; i++)
		newNonModelConformProperties[i] = this.nonModelConformProperties[i].toSaveable();
	var newOutgoingLinks = new Array();
	for (var i = 0; i < this.outgoingLinks.length; i++)
		newOutgoingLinks[i] = this.outgoingLinks[i].toSaveable();
	var newNonModelConformLinks = new Array();
	for (var i = 0; i < this.nonModelConformLinks.length; i++)
		newNonModelConformLinks[i] = this.nonModelConformLinks[i].toSaveable();

	return {
		labels : this.labels,
		uri : this.uri,
		properties : newProperties,
		nonModelConformProperties : newNonModelConformProperties,
		outgoingLinks : newOutgoingLinks,
		nonModelConformLinks: newNonModelConformLinks
	};
};

/**
 * restore a rdfclass
 * @param {Object} savedType
 */
GQBrdfClass.prototype.restore = function(savedType) {
	// we don't want to restore if we're already getting properties
	// from somewhere else:
	if (this.isGettingPropsOrLinks) return;
	this.ready = false;
	this.isGettingPropsOrLinks = true;

	this.uri = savedType.uri;
	this.properties = new Array();
	this.nonModelConformProperties = new Array();
	this.outgoingLinks = new Array();
	this.nonModelConformLinks = new Array();
	this.labels = {};
	
	for (var lang in savedType.labels) this.addLabel(savedType.labels[lang],lang);

	var property;
	for (var i = 0; i < savedType.properties.length; i++) {
		property = new GQBProperty(savedType.properties[i].uri)
		for (var lang in savedType.properties[i].labels) {
			property.addLabel(savedType.properties[i].labels[lang], lang);
		}
		property.range = savedType.properties[i].range;
		this.properties.push(property);
	}
	for (var i = 0; i < savedType.nonModelConformProperties.length; i++) {
		// all non model conform properties should also be in the properties array:
		var found = this.findAnyPropertyByUri(savedType.nonModelConformProperties[i].uri);
		if (found) {
			this.nonModelConformProperties.push(found);
			continue;
		}
		// this code is kept just in case:
		property = new GQBProperty(savedType.nonModelConformProperties[i].uri)
		for (var lang in savedType.nonModelConformProperties[i].labels) {
			property.addLabel(savedType.nonModelConformProperties[i].labels[lang], lang);
		}
		property.range = savedType.nonModelConformProperties[i].range;
		this.nonModelConformProperties.push(property);
		this.properties.push(property);
	}
	for (var i = 0; i < savedType.outgoingLinks.length; i++) {
		property = new GQBProperty(savedType.outgoingLinks[i].uri)
		for (var lang in savedType.outgoingLinks[i].labels) {
			property.addLabel(savedType.outgoingLinks[i].labels[lang], lang);
		}
		property.range = savedType.outgoingLinks[i].range;
		this.outgoingLinks.push(property);
	}
	for (var i = 0; i < savedType.nonModelConformLinks.length; i++) {
		// all non model conform links should also be in the outgoingLinks array:
		var found = this.findAnyPropertyByUri(savedType.nonModelConformLinks[i].uri);
		if (found) {
			this.nonModelConformLinks.push(found);
			continue;
		}
		property = new GQBProperty(savedType.nonModelConformLinks[i].uri)
		for (var lang in savedType.nonModelConformLinks[i].labels) {
			property.addLabel(savedType.nonModelConformLinks[i].labels[lang], lang);
		}
		property.range = savedType.nonModelConformLinks[i].range;
		this.nonModelConformLinks.push(property);
		this.outgoingLinks.push(property);
	}

	this.hasGottenProps = this.hasGottenLinks = this.hasGottenNonModelConformPropsAndLinks = this.ready = true;
	this.isGettingPropsOrLinks = false;
	var gqbEvent = new GQBEvent("classReady", this);
	GQB.controller.notify(gqbEvent);
};
/**
 * get Label of this Property
 * @param {Object} lang
 * @return the label in the language given. if no argument passed: in GQB.currLang
 */
GQBrdfClass.prototype.getLabel = function(lang) {
	if (!lang) lang = GQB.currLang;
	if (this.labels[lang]) return this.labels[lang];
	else for (l in this.labels) return this.labels[l];
	return "";
};

/**
 * add a label
 * @param {Object} label
 * @param {Object} lang
 */
GQBrdfClass.prototype.addLabel = function(label, lang) {
	if (!this.labels[lang]) this.labels[lang] = label;
};

/**
 * sort all property arrays
 */
GQBrdfClass.prototype.sortAllPropArraysByOrder = function() {
	this.sortPropArrayByOrder(0);
	this.sortPropArrayByOrder(1);
	this.sortPropArrayByOrder(2);
	this.sortPropArrayByOrder(3);
};

/**
 * sort given property array by property[i].order
 * (this should be SysOnt:Order - workaround for a bug at Sparql-"ORDER BY" )
 * 0 = this.properties, 
 * 1 = this.nonModelConformProperties, 
 * 2 = this.outgoingLinks, 
 * 3 = this.nonModelConformLinks
 * @param {int} arrayToSort
 */
GQBrdfClass.prototype.sortPropArrayByOrder = function(arrayToSort) {
	// sort according to "order"
	var arraysToSort = [ this.properties, this.nonModelConformProperties, this.outgoingLinks, this.nonModelConformLinks ];
	var propArray = arraysToSort[arrayToSort];
	var orderedProps = new Array();
	for (var i = 0; i < propArray.length; i++) { if(propArray[i].order != undefined) orderedProps.push(propArray[i]); }
	for (var i = 0; i < orderedProps.length; i++) {
		var minIdx = i;
		for (var j = i+1; j < orderedProps.length; j++) {
			if (parseInt(orderedProps[j].order) < parseInt(orderedProps[minIdx].order)) minIdx = j;
		}
		if (minIdx == i) continue;
		var tmp = orderedProps[i];
		orderedProps[i] = orderedProps[minIdx];
		orderedProps[minIdx] = tmp;
	}
	for (var i = 0; i < propArray.length; i++) { if(propArray[i].order == undefined) orderedProps.push(propArray[i]); }

	this.properties = arrayToSort == 0 ? orderedProps : this.properties;
	this.nonModelConformProperties = arrayToSort == 1 ? orderedProps : this.nonModelConformProperties;
	this.outgoingLinks = arrayToSort == 2 ? orderedProps : this.outgoingLinks;
	this.nonModelConformLinks = arrayToSort == 3 ? orderedProps : this.nonModelConformLinks;
};

/**
 * find a property (no matter if its an modelconform property or not, or a Link or not)
 * @param {string} uri
 */
GQBrdfClass.prototype.findAnyPropertyByUri = function(uri) {
	for (var i = 0; i < this.properties.length; i++) {
		if (this.properties[i].uri == uri){
                        return this.properties[i];
                }
	}
	for (var i = 0; i < this.nonModelConformProperties.length; i++) {
		if (this.nonModelConformProperties[i].uri == uri){
                        return this.nonModelConformProperties[i];
                }
	}
	for (var i = 0; i < this.outgoingLinks.length; i++) {
		if (this.outgoingLinks[i].uri == uri){
                        return this.outgoingLinks[i];
                }
	}
	for (var i = 0; i < this.nonModelConformLinks.length; i++) {
		if (this.nonModelConformLinks[i].uri == uri){
                        return this.nonModelConformLinks[i];
                }
	}
	return null;
};

/**
 * Property of a rdf-class (e.g. "name" or "birthday")
 * @param {Object} _uri
 * @param {Object} _label
 * @param {Object} _range
 * @class
 */
function GQBProperty(_uri, _label, _lang, _range, _order){
	if (!_uri) _uri = "";
	if (!_label) _label = "";
	if (!_lang) _lang = " ";
	if (!_range) _range = "";

	//vars'
	this.uri = _uri;
	this.labels = {};
	if(_label && _lang)
		this.labels[_lang] = _label;
	this.range = _range;
	this.order = _order;

	this.getLabel;
	this.getRangeLabel;
	this.addLabel;
	this.toSaveable;
}

/**
 * get the label of the range
 * @param {Object} lang
 * @return the label in the language given. if no argument passed: in GQB.currLang
 */
GQBProperty.prototype.getRangeLabel = function(lang) {
	if (!lang) lang = GQB.currLang;
	if (this.range.substr(0,4)=="http") {
		var rangeclass = GQB.model.findRDFClassByUri(this.range);
		if (rangeclass){
			return rangeclass.getLabel();
		}else{
			return "not found";
		}
	}
	return this.range;
};

/**
 * get Label of this Property
 * @param {Object} lang
 * @return the label in the language given. if no argument passed: in GQB.currLang
 */
GQBProperty.prototype.getLabel = function(lang) {
	if (!lang) lang = GQB.currLang;
	if (this.labels[lang]) return this.labels[lang];
	else for (l in this.labels) return this.labels[l];
	return "";
};

/**
 * add a label
 * @param {Object} label
 * @param {Object} lang
 */
GQBProperty.prototype.addLabel = function(label, lang) {
	if (!this.labels[lang]) this.labels[lang] = label;
};

/**
 * @return stripped off all functions
 */
GQBProperty.prototype.toSaveable = function() {
	return {
		uri : this.uri,
		labels : this.labels,
		range : this.range,
		order : this.order
	};
};

/**
 * reprensents a link. (a line between two boxes)
 * consists of a property (which determines the label)
 * and a target (class at the end of the line)
 * @param {Object} _property pointer to a GQBProperty
 * @param {Object} _target pointer to GQBClass
 * @class
 */
function GQBSelectedLink(_property, _target){
	//varslabel : string
	this.property = _property;
	this.optional = false;
	this.target = _target;
}