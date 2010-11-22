/**
 * GQBRestrictionDateBetween
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and two dates
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property which property should be restricted
 * @param {Date} _restrictionDateFrom Date1
 * @param {Date} _restrictionDateTo Date2
 * @param {Bool} _applyToVarFrom Whether the from date contains the uri of another property.
 * @param {Bool} _applyToVarTo Whether the to date contains the uri of another property.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionDateBetween(_property, _restrictionDateFrom, _restrictionDateTo, _applyToVarFrom, _applyToVarTo, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionDateFrom = _restrictionDateFrom;
	this.restrictionDateTo = _restrictionDateTo;
	this.restrictionType = "GQBRestrictionDateBetween";
	this.negation = _negation;
	this.applyToVarFrom = _applyToVarFrom ? _applyToVarFrom : false;
	this.applyToVarTo = _applyToVarTo ? _applyToVarTo : false;

	// casting to xsd:gYear rounds down somehow?? this +1 is necessary:
	if (!this.applyToVarTo)
		this.restrictionDateTo = parseInt(this.restrictionDateTo);

	this.toString;
	this.toFilterString;
}

/**
 * GQBRestrictionDateBetween.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionDateBetween.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("between")+" " + this.restrictionDateFrom + " "+GQB.translate("and")+" " + this.restrictionDateTo;
	else return this.property.getLabel() + " "+GQB.translate("not between")+" " + this.restrictionDateFrom + " "+GQB.translate("and")+" " + this.restrictionDateTo;
};

/**
 * GQBRestrictionDateBetween.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionDateBetween.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	var fromString = this.applyToVarFrom ? classVarName + "Var" + (varOffset+1) : "\"" + this.restrictionDateFrom + "\"^^xsd:gYear";
	var toString = this.applyToVarTo ? classVarName + "Var" + (varOffset + (this.applyToVarFrom ? 2 : 1)) : "\"" + this.restrictionDateTo + "\"^^xsd:gYear";
	if(!this.negation) return "("+varName+" >= "+fromString+" && "+varName+" <= "+toString+" )";
	else return "("+varName+" < "+fromString+" || "+varName+" > "+toString+" )";
};
/**
 * GQBRestrictionDateBetween.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionDateBetween.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),  
		restrictionDateFrom: this.restrictionDateFrom,
		restrictionDateTo: this.restrictionDateTo,
		restrictionType: this.restrictionType,
		applyToVarFrom: this.applyToVarFrom,
		applyToVarTo: this.applyToVarTo,
		negation: this.negation
	};
};

/**
 * GQBRestrictionDateAfter
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and one date
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property which property should be restricted
 * @param {Date} _restrictionDateFrom Date
 * @param {Bool} _applyToVarFrom Whether the from date contains the uri of another property.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionDateAfter(_property, _restrictionDateFrom, _applyToVarFrom, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionDateFrom = _restrictionDateFrom;
 
	this.restrictionType = "GQBRestrictionDateAfter";
	this.negation = _negation;
	this.applyToVarFrom = _applyToVarFrom ? _applyToVarFrom : false;

	this.toString;
	this.toFilterString;
}

/**
 * GQBRestrictionDateAfter.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionDateAfter.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("after")+" " + this.restrictionDateFrom;
	else return this.property.getLabel() + " "+GQB.translate("not after")+" " + this.restrictionDateFrom;
};

/**
 * GQBRestrictionDateAfter.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionDateAfter.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	var fromString = this.applyToVarFrom ? classVarName + "Var" + (varOffset+1) : "\"" + this.restrictionDateFrom + "\"^^xsd:gYear";
	if(!this.negation) return "("+varName+" >= "+fromString+")";
	else return "!("+varName+" >= "+fromString+")";
};
/**
 * GQBRestrictionDateAfter.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionDateAfter.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),
		restrictionDateFrom: this.restrictionDateFrom,
		restrictionType: this.restrictionType,
		applyToVarFrom: this.applyToVarFrom,
		negation: this.negation
	};
};

/**
 * GQBRestrictionDateBefore
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and one date
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property which property should be restricted
 * @param {Date} _restrictionDateTo Date
 * @param {Bool} _applyToVarTo Whether the to date contains the uri of another property.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionDateBefore(_property, _restrictionDateTo, _applyToVarTo, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionDateTo = _restrictionDateTo;
	this.restrictionType = "GQBRestrictionDateBefore"
	this.applyToVarTo = _applyToVarTo ? _applyToVarTo : false;
	this.negation = _negation;

	// casting to xsd:gYear rounds down somehow?? this +1 is necessary:
	if (!this.applyToVarTo)
		this.restrictionDateTo = parseInt(this.restrictionDateTo);

	this.toString;
	this.toFilterString;
}

/**
 * GQBRestrictionDateBefore.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionDateBefore.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("before")+" " + this.restrictionDateTo;
	else return this.property.getLabel() + " "+GQB.translate("not before")+" " + this.restrictionDateTo;
};

/**
 * GQBRestrictionDateBefore.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionDateBefore.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	var toString = this.applyToVarTo ? classVarName + "Var" + (varOffset + 1) : "\"" + this.restrictionDateTo + "\"^^xsd:gYear";
	if(!this.negation) return "("+varName+" <= "+toString+")";
	else return "!("+varName+" <= "+toString+")";
};
/**
 * GQBRestrictionDateBefore.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionDateBefore.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),
		restrictionDateTo: this.restrictionDateTo,
		restrictionType: this.restrictionType,
		applyToVarTo: this.applyToVarTo,
		negation: this.negation
	};
};

/**
 * GQBRestrictionHasProperty
 * can be added as an restriction to a class
 * need to pass a property which should be restricted 
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property
 * @param {Bool} _negation
 * @class
 */
function GQBRestrictionHasProperty(_property, _negation){

	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionType = "GQBRestrictionHasProperty";
	this.negation = _negation;
	
	this.toString;  
	this.toFilterString;
}

/**
 * GQBRestrictionHasProperty.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionHasProperty.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("is empty");
	else return this.property.getLabel() + " "+GQB.translate("is not empty");
};

/**
 * GQBRestrictionHasProperty.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionHasProperty.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	if(!this.negation){
		return "!bound("+varName+")";
	} else {
		return "bound("+varName+")";
	}
};
/**
 * GQBRestrictionHasProperty.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionHasProperty.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),
		restrictionType: this.restrictionType,
		negation: this.negation
	}
};
/**
 * GQBRestrictionStringContains
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and a string which all entities should contain in that property
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property
 * @param {string} _restrictionString
 * @param {Bool} _applyToVar Whether the restriction string contains a uri.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionStringContains(_property, _restrictionString, _applyToVar, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionString = _restrictionString;
	this.restrictionType = "GQBRestrictionStringContains";
	this.negation = _negation;
	this.applyToVar = _applyToVar ? _applyToVar : false;
	
	this.toString;  
	this.toFilterString;
}

/**
 * GQBRestrictionStringContains.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionStringContains.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("contains")+" \"" + this.restrictionString + "\"";
	else return this.property.getLabel() + " "+GQB.translate("does not contain")+" \"" + this.restrictionString + "\"";
};

/**
 * GQBRestrictionStringContains.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionStringContains.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	if(!this.negation){
		if (this.applyToVar) {
			return "regex(str("+varName+"), str("+classVarName + "Var" + (varOffset+1) +"), \"i\")";
		}

		var letters = this.restrictionString.split("");
		var restStrWorkaround ="";
		for(var i=0; i < letters.length; i++){
			restStrWorkaround += "["+letters[i].toUpperCase()+letters[i].toLowerCase()+"]";
		}
		return "regex(str("+varName+"), \""+restStrWorkaround+"\", \"i\")";
	} 
	else{
		if (this.applyToVar) {
			return "!regex(str("+varName+"), str("+classVarName + "Var" + (varOffset+1) +"), \"i\")";
		}

		var letters = this.restrictionString.split("");
		var restStrWorkaround ="";
		for(var i=0; i < letters.length; i++){
			restStrWorkaround += "["+letters[i].toUpperCase()+letters[i].toLowerCase()+"]";
		}
		return "!regex(str("+varName+"), \""+restStrWorkaround+"\", \"i\")";
	}
};
/**
 * GQBRestrictionStringContains.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionStringContains.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),
		restrictionString: this.restrictionString,
		restrictionType: this.restrictionType,
		applyToVar : this.applyToVar,
		negation: this.negation
	}
};

/**
 * GQBRestrictionStringEquals
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and a string which all entities should contain in that property
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property
 * @param {string} _restrictionString
 * @param {Bool} _applyToVar Whether the restriction string contains a uri.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionStringEquals(_property, _restrictionString, _applyToVar, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionString = _restrictionString;
	this.restrictionType = "GQBRestrictionStringEquals";
	this.negation = _negation;
	this.applyToVar = _applyToVar ? _applyToVar : false;

	this.toString;  
	this.toFilterString;
}

/**
 * GQBRestrictionStringEquals.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionStringEquals.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("is equal to")+" \"" + this.restrictionString + "\"";
	else return this.property.getLabel() + " "+GQB.translate("is not equal to")+" \"" + this.restrictionString + "\"";
};

/**
 * GQBRestrictionStringEquals.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionStringEquals.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;

	if(!this.negation) {
		if (!this.applyToVar) 
			return "(str("+varName+") = str(\""+this.restrictionString+"\"))";
		else
			return "(str("+varName+") = str("+classVarName + "Var" + (varOffset+1)+"))";
	} else return "!(str("+varName+") = str(\""+this.restrictionString+"\"))";
};
/**
 * GQBRestrictionStringEquals.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionStringEquals.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),
		restrictionString: this.restrictionString,
		restrictionType: this.restrictionType,
		applyToVar : this.applyToVar,
		negation: this.negation
	}
};

/**
 * GQBRestrictionIntegerBetween
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and two integers
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property which property should be restricted (GQBProperty)
 * @param {int} _restrictionIntFrom int1
 * @param {int} _restrictionIntTo int2
 * @param {Bool} _applyToVarFrom Whether the from int contains the uri of another property.
 * @param {Bool} _applyToVarTo Whether the to int contains the uri of another property.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionIntegerBetween(_property, _restrictionIntFrom, _restrictionIntTo, _applyToVarFrom, _applyToVarTo, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionIntFrom = _restrictionIntFrom;
	this.restrictionIntTo = _restrictionIntTo;
	this.restrictionType = "GQBRestrictionIntegerBetween";
	this.negation = _negation;
	this.applyToVarFrom = _applyToVarFrom ? _applyToVarFrom : false;
	this.applyToVarTo = _applyToVarTo ? _applyToVarTo : false;

	this.toString; 
	this.toFilterString;
}

/**
 * GQBRestrictionIntegerBetween.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionIntegerBetween.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("between")+" " + this.restrictionIntFrom + " "+GQB.translate("and")+" " + this.restrictionIntTo;
	else return this.property.getLabel() + " "+GQB.translate("not between")+" " + this.restrictionIntFrom + " "+GQB.translate("and")+" " + this.restrictionIntTo;
};

/**
 * GQBRestrictionIntegerBetween.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionIntegerBetween.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	var fromString = this.applyToVarFrom ? classVarName + "Var" + (varOffset+1) : this.restrictionIntFrom;
	var toString = this.applyToVarTo ? classVarName + "Var" + (varOffset + (this.applyToVarFrom ? 2 : 1)) : this.restrictionIntTo;
	if(!this.negation) return "("+varName+" >= "+fromString+" && "+varName+" <= "+toString+")";
	else return "("+varName+" < "+fromString+" || "+varName+" > "+toString+")";
};

/**
 * GQBRestrictionIntegerBetween.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionIntegerBetween.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),  
		restrictionIntFrom: this.restrictionIntFrom,
		restrictionIntTo: this.restrictionIntTo,
		restrictionType: this.restrictionType,
		applyToVarFrom: this.applyToVarFrom,
		applyToVarTo: this.applyToVarTo,
		negation: this.negation
	};
};

/**
 * GQBRestrictionIntegerBiggerAs
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and one integer
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property which property should be restricted (GQBProperty)
 * @param {int} _restrictionIntFrom int
 * @param {Bool} _applyToVarFrom Whether the from int contains the uri of another property.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionIntegerBiggerAs(_property, _restrictionIntFrom, _applyToVarFrom, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionIntFrom = _restrictionIntFrom;
	this.restrictionType = "GQBRestrictionIntegerBiggerAs";
	this.negation = _negation;
	this.applyToVarFrom = _applyToVarFrom ? _applyToVarFrom : false;

	this.toString; 
	this.toFilterString;
}

/**
 * GQBRestrictionIntegerBiggerAs.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionIntegerBiggerAs.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("greater than")+" " + this.restrictionIntFrom;
	else return this.property.getLabel() + " "+GQB.translate("not greater than")+" " + this.restrictionIntFrom;
};

/**
 * GQBRestrictionIntegerBiggerAs.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionIntegerBiggerAs.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	var fromString = this.applyToVarFrom ? classVarName + "Var" + (varOffset+1) : this.restrictionIntFrom;
	if(!this.negation) return "("+varName+" >= "+fromString+")";
	else return "("+varName+" < "+fromString+")";
};

/**
 * GQBRestrictionIntegerBiggerAs.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionIntegerBiggerAs.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),  
		restrictionIntFrom: this.restrictionIntFrom,
		restrictionType: this.restrictionType,
		applyToVarFrom: this.applyToVarFrom,
		negation: this.negation
	};
};

/**
 * GQBRestrictionSmallerAs
 * can be added as an restriction to a class
 * need to pass a property which should be restricted and one integer
 * the restriction then can be turned in a sparql-filter-expression
 * @param {GQBProperty} _property which property should be restricted (GQBProperty)
 * @param {int} _restrictionIntTo int
 * @param {Bool} _applyToVarTo Whether the to int contains the uri of another property.
 * @param {Bool} _negation Whether the restriction should be negated or not.
 * @class
 */
function GQBRestrictionIntegerSmallerAs(_property, _restrictionIntTo, _applyToVarTo, _negation){
	do {
		this.id = Math.floor(32767*32767*(Math.random()));
	} while (GQB.model.findRestrictionById(this.id) != null);

	this.property = _property;
	this.restrictionIntTo = _restrictionIntTo;
	this.restrictionType = "GQBRestrictionIntegerSmallerAs";
	this.negation = _negation;
	this.applyToVarTo = _applyToVarTo ? _applyToVarTo : false;

	this.toString; 
	this.toFilterString;
}

/**
 * GQBRestrictionIntegerSmallerAs.toString()
 * @return human readable string form of this restriction
 */ 
GQBRestrictionIntegerSmallerAs.prototype.toString = function(){
	if(!this.negation) return this.property.getLabel() + " "+GQB.translate("less than")+" " + this.restrictionIntTo;
	else return this.property.getLabel() + " "+GQB.translate("not less than")+" " + this.restrictionIntTo;
};

/**
 * GQBRestrictionIntegerSmallerAs.toFilterString()
 * @return sparql-conform expression of this restriction
 */ 
GQBRestrictionIntegerSmallerAs.prototype.toFilterString = function(classVarName, varOffset){
	var varName = classVarName + "Var" + varOffset;
	var toString = this.applyToVarTo ? classVarName + "Var" + (varOffset + 1) : this.restrictionIntTo;
	if(!this.negation) return "("+varName+" <= "+toString+")";
	else return "("+varName+" > "+toString+")";
};

/**
 * GQBRestrictionIntegerSmallerAs.toSaveable()
 * @return this without functions and unnessecary
 */ 
GQBRestrictionIntegerSmallerAs.prototype.toSaveable = function(){
	return {
		property: this.property.toSaveable(),
		restrictionIntTo: this.restrictionIntTo,
		restrictionType: this.restrictionType,
		applyToVarTo: this.applyToVarTo,
		negation: this.negation
	};
};

/**
 * GQBRestrictionStructure
 * a logical structure
 * can hold many restrictions and gives them a structure 
 * e.g. (A && B)  (where A and B are "members")
 * @param {string} _mode must be "AND or "OR"
 * @param {int} _level must be 1 or 2
 * @class
 */
function GQBRestrictionStructure( _mode, _level){
	this.members = new Array();

	this.mode = _mode; 
	this.level = _level;
	this.varOffset = 0;
	this.restrictionType = "GQBRestrictionStructure";

	//methods
	this.addMember;
	this.removeMember;
	this.compareRestrictions;
	this.empty;
	this.toString;
	this.toFilterString;
	this.toWhereString;
	this.findRestrictionById;
}

/**
 * GQBRestrictionStructure.hasMember()
 * @return boolean true if the structure (or children) contain any restrictions
 */
GQBRestrictionStructure.prototype.hasMember = function (){
	for ( var i = 0; i < this.members.length; i++ ) {
		if ( this.members[i].hasMember != undefined ){
			//is structure
			if(this.members[i].hasMember() == true)
				return true;
		} else return true;
	}
	return false;
};

/**
 * GQBRestrictionStructure.addMember
 * add a new member (e.g. GQBRestrictionStringContains) to this structure
 * @param {GQBRestriction} nMember the restriction to add
 */
GQBRestrictionStructure.prototype.addMember = function (nMember){
	if(nMember.addMember != undefined && this.level != 1){
		alert("trying to add a GQBRestrictionStructure to a second-level structure - not knf/dnf");
		return;
	}
	this.members.push(nMember);
};

/**
 * delete a member
 * @param {GQBRestriction} nMember member to delete
 */
GQBRestrictionStructure.prototype.removeMember = function (nMember){
	var newMembers = new Array();
	for (var i = 0; i < this.members.length; i++) {
		if(this.members[i].restrictionType == "GQBRestrictionStructure"){
			continue; //skip
		}
		if ( this.compareRestrictions(this.members[i], nMember) != 0 )
			newMembers.push(this.members[i]);
	}
	this.members = newMembers;
};

/**
 * go through members and find a restriction
 * @param {Object} id id of restriction to find
 */
GQBRestrictionStructure.prototype.findRestrictionById = function (id){
	var tmp;
	for (var i = 0; i < this.members.length; i++) {
		if (this.members[i].restrictionType != "GQBRestrictionStructure") {
			if (this.members[i].id == id) {
				return this.members[i];
			}
		}
		else {
			//the member is another structure
			tmp = this.members[i].findRestrictionById(id);
			if (tmp != null) 
				return tmp;
		}
	}
	return null;
};

/**
 * compare two restriction
 * "shallow" compare - look into the objects and compare values
 * @param {Object} rest1
 * @param {Object} rest2
 * @return true if equal - false otherwise
 */
GQBRestrictionStructure.prototype.compareRestrictions = function(rest1, rest2){
	if ( rest1.restrictionType != rest2.restrictionType ) return 1;
	switch ( rest1.restrictionType ) {
		case "GQBRestrictionStringContains":
			if ( (rest1.restrictionString != rest2.restrictionString) || (rest1.property.uri != rest2.property.uri) ) {
				return 1;
			}
			break;
		case "GQBRestrictionIntegerBetween":
			if ( (rest1.restrictionIntFrom != rest2.restrictionIntFrom) || (rest1.restrictionIntTo != rest2.restrictionIntTo) || (rest1.property.uri != rest2.property.uri) ) {
				return 1;
			}
			break;
		case "GQBRestrictionDateBetween":
			if ( (rest1.restrictionDateFrom != rest2.restrictionDateFrom) || (rest1.restrictionDateTo != rest2.restrictionDateTo) || (rest1.property.uri != rest2.property.uri) ) {
				return 1;
			}
			break;
		case "GQBRestrictionHasProperty":
			return 1;
			break;
	}
	return 0;
};

/**
* evaluate this restriction to a sparql-conform where-part
* @return a string which must be added to the WHERE clause of a query in 
* order to use this restriction structure:
* (recurses into daughter structures)
* @param classVarName is the variable name of the class to which this restriciton belongs in the SPARQL-Query 
* 	 is a string of the form "?classX" with X being an integer
* @param varOffset indicates where the counting of restriction variables should start
* each restriction then receives a variable name of the form "?classXVarY" with integers X and Y
*/
GQBRestrictionStructure.prototype.toWhereString = function(classVarName, varOffset) {
	if (varOffset == undefined) varOffset = 0;
	this.varOffset = varOffset;

	var result = "";
	for (var i = 0; i < this.members.length; i++) {
		if ( this.members[i].restrictionType == "GQBRestrictionStructure" ) {
			// is a structure
			result += this.members[i].toWhereString(classVarName, this.varOffset);  // recursion if a structure
			this.varOffset = this.members[i].varOffset;
		} 
		else {
			// is a Restriction
			if(this.members[i].restrictionType=="GQBRestrictionHasProperty"){
				result += "OPTIONAL { "+classVarName + " <" + this.members[i].property.uri + "> "+classVarName + "Var" + this.varOffset + " } ";
			} else {
				result += classVarName + " <" + this.members[i].property.uri + "> "+classVarName + "Var" + this.varOffset + " . ";
			}
			this.varOffset++;
			if (this.members[i].applyToVar == true) {
				result += classVarName + " <" + this.members[i].restrictionString + "> "+classVarName + "Var" + this.varOffset + " . ";
				this.varOffset++;
			} 
			if (this.members[i].applyToVarFrom == true) {
				var fromString = " ";
				if (this.members[i].restrictionType.match("GQBRestrictionDate"))
					fromString = this.members[i].restrictionDateFrom;
				else if (this.members[i].restrictionType.match("GQBRestrictionInt"))
					fromString = this.members[i].restrictionIntFrom;
				result += classVarName + " <" + fromString + "> "+classVarName + "Var" + this.varOffset + " . ";
				this.varOffset++;
			} 
			if (this.members[i].applyToVarTo == true) {
				var toString = " ";
				if (this.members[i].restrictionType.match("GQBRestrictionDate"))
					toString = this.members[i].restrictionDateTo;
				else if (this.members[i].restrictionType.match("GQBRestrictionInt"))
					toString = this.members[i].restrictionIntTo;
				result += classVarName + " <" + toString + "> "+classVarName + "Var" + this.varOffset + " . ";
				this.varOffset++;
			}
		}
	} 
	return result;
};

/**
 * build a sparql-conform filter-expression from thiss restriction
 * @param {Object} classVarName
 * @param {Object} varOffset
 * @return combined the filter-expressions of all members
 */
GQBRestrictionStructure.prototype.toFilterString = function(classVarName, varOffset) {
	if (varOffset == undefined) varOffset = 0;
	this.varOffset = varOffset;
	
	var result = "";
	var operator = "||";
	if ( this.mode == "AND" ) {
		operator = "&&";
	}

	for (var i = 0; i < this.members.length; i++) {
		if ( i+1 != this.members.length ) {
			result += this.members[i].toFilterString(classVarName, this.varOffset) + " " + operator + " "; 
		} else {
			result += this.members[i].toFilterString(classVarName, this.varOffset);
		}

		if ( this.members[i].restrictionType == "GQBRestrictionStructure" ) {
			// the structure will keep track of how many variables are added,
			// we then take that total when "toFilterString()" returns
			this.varOffset = this.members[i].varOffset;  
		} else {
			// each restriction adds one variable...
			this.varOffset++;  
			// unless comparing two variables:
			if (this.members[i].applyToVar == true) {
				this.varOffset++;
			} else if (this.members[i].applyToVarFrom == true) {
				this.varOffset++;
			} else if (this.members[i].applyToVarTo == true) {
				this.varOffset++;
			}
		}
	}
	if(result != ""){
		result = "(" + result + ")";
	}
	return result;
};

/**
 * restore a GQBRestrictionStructure from db
 * @param {Object} savedStructure
 */
GQBRestrictionStructure.prototype.restore = function(savedStructure) {
	this.mode = savedStructure.mode;
	this.level = savedStructure.level;
	this.members = new Array();
	var member;
	var property;
	for(var i=0; i< savedStructure.member.length; i++){
		if (savedStructure.member[i].restrictionType != "GQBRestrictionStructure") {
			property = new GQBProperty(savedStructure.member[i].property.uri);
			property.order = savedStructure.member[i].property.order;
			for (var lang in savedStructure.member[i].property.labels) 
				property.addLabel(savedStructure.member[i].property.labels[lang], lang);
		}
		switch(savedStructure.member[i].restrictionType){
			case "GQBRestrictionStructure":
				member = new GQBRestrictionStructure(savedStructure.member[i].mode, savedStructure.member[i].level);
				member.restore(savedStructure.member[i]);
				break;
			case "GQBRestrictionStringContains":
				
				member = new GQBRestrictionStringContains(property, savedStructure.member[i].restrictionString, savedStructure.member[i].applyToVar, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionStringEquals":
				member = new GQBRestrictionStringEquals(property, savedStructure.member[i].restrictionString, savedStructure.member[i].applyToVar, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionDateBetween":
				member = new GQBRestrictionDateBetween(property, savedStructure.member[i].restrictionDateFrom, savedStructure.member[i].restrictionDateTo, savedStructure.member[i].applyToVarFrom, savedStructure.member[i].applyToVarTo, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionDateAfter":
				member = new GQBRestrictionDateAfter(property, savedStructure.member[i].restrictionDateFrom, savedStructure.member[i].applyToVarFrom, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionDateBefore":
				member = new GQBRestrictionDateBefore(property, savedStructure.member[i].restrictionDateTo, savedStructure.member[i].applyToVarTo, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionIntegerBetween":
				member = new GQBRestrictionIntegerBetween(property, savedStructure.member[i].restrictionIntFrom, savedStructure.member[i].restrictionIntTo, savedStructure.member[i].applyToVarFrom, savedStructure.member[i].applyToVarTo, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionIntegerBiggerAs":
				member = new GQBRestrictionIntegerBiggerAs(property, savedStructure.member[i].restrictionIntFrom, savedStructure.member[i].applyToVarFrom, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionIntegerSmallerAs":
				member = new GQBRestrictionIntegerSmallerAs(property, savedStructure.member[i].restrictionIntTo, savedStructure.member[i].applyToVarTo, savedStructure.member[i].negation);
				break;
			case "GQBRestrictionHasProperty":
				member = new GQBRestrictionHasProperty(property, savedStructure.member[i].negation);
				break;
		}
		this.members.push(member);
	}
};

/**
 * strip off all unnessesary stuff and return a pure array object (no functions)
 */
GQBRestrictionStructure.prototype.toSaveable = function() {
	var newmembers = new Array();
	for(var i=0; i< this.members.length; i++){
		newmembers[i] = this.members[i].toSaveable();
	}
	return {
		mode: this.mode, 
		level: this.level,
		varOffset: this.varOffset,
		restrictionType: this.restrictionType,
		member: newmembers
	}
};

/**
 * Returns a new GQBRestriction, which is a copy of the passed
 * GQBRestriction.  Does not copy GQBRestrictionStructures.
 * @param rest The GQBRestricion to copy.
 * @return A new GQBRestriction of the same type and with the same 
 *         properties as the passed GQBRestriction, or null if
 *         something other than a GQBRestriction was passed.
 */
GQB.copyRestriction = function(rest) {
	if(rest.restrictionType == undefined) return null;

	if(rest.restrictionType == "GQBRestrictionStringContains") {
		return new GQBRestrictionStringContains(rest.property, rest.restrictionString, rest.applyToVar, rest.negation);
	}
	if(rest.restrictionType == "GQBRestrictionStringEquals") {
		return new GQBRestrictionStringContains(rest.property, rest.restrictionString, rest.applyToVar, rest.negation);
	}
	else if(rest.restrictionType == "GQBRestrictionIntegerBetween") {
		return new GQBRestrictionIntegerBetween(rest.property, rest.restrictionIntFrom, rest.restrictionIntTo, rest.applyToVarFrom, rest.applyToVarTo, rest.negation);
	}
	else if(rest.restrictionType == "GQBRestrictionIntegerSmallerAs") {
		return new GQBRestrictionIntegerBetween(rest.property, rest.restrictionIntTo, rest.applyToVarTo, rest.negation);
	}
	else if(rest.restrictionType == "GQBRestrictionIntegerBiggerAs") {
		return new GQBRestrictionIntegerBetween(rest.property, rest.restrictionIntFrom, rest.applyToVarFrom, rest.negation);
	}
	else if(rest.restrictionType == "GQBRestrictionStringDateBetween") {
		return new GQBRestrictionDateBetween(rest.property, rest.restrictionDateFrom, rest.restrictionDateTo, rest.applyToVarFrom, rest.applyToVarTo, rest.negation);
	} 
	else if(rest.restrictionType == "GQBRestrictionDateBefore") {
		return new GQBRestrictionIntegerBetween(rest.property, rest.restrictionDateTo, rest.applyToVarTo, rest.negation);
	}
	else if(rest.restrictionType == "GQBRestrictionDateAfter") {
		return new GQBRestrictionIntegerBetween(rest.property, rest.restrictionDateFrom, rest.applyToVarFrom, rest.negation);
	}
	if(rest.restrictionType == "GQBRestrictionHasProperty") {
		return new GQBRestrictionStringContains(rest.property, rest.negation);
	}
	else {
		return null;
	}
};