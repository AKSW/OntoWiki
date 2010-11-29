/**
 * Method creates the content of the Restriction-Type Select Box
 * is called when the Property to restrict changes and on startup, when clicking add a restriction
 */
GQBView.prototype.initRestrictionTypeBox = function(){
	$("#restrictiontypeselector").empty();
	var mo_id= parseInt($("#restrictionpropselector :selected").attr("modelId"));
	var property = this.findModelObjectById(mo_id);
	switch(property.range) {
                case 'http://www.w3.org/2001/XMLSchema#string':
		case "string":
			$("#restrictiontypeselector").append("<option restrictionType=\"0\">"+GQB.translate("stringContainsMsg")+"</option>");
			$("#restrictiontypeselector").append("<option restrictionType=\"1\">"+GQB.translate("stringEqualsMsg")+"</option>");
			break;
		case "resource-uri (string)":
			$("#restrictiontypeselector").append("<option restrictionType=\"0\">"+GQB.translate("stringContainsMsg")+"</option>");
			$("#restrictiontypeselector").append("<option restrictionType=\"1\">"+GQB.translate("stringEqualsMsg")+"</option>");
			break;
		case "gYear":
			$("#restrictiontypeselector").append("<option restrictionType=\"5\">"+GQB.translate("yearsAfterMsg")+"</option>");
			$("#restrictiontypeselector").append("<option restrictionType=\"6\">"+GQB.translate("yearsBeforeMsg")+"</option>");
			$("#restrictiontypeselector").append("<option restrictionType=\"7\">"+GQB.translate("yearsBetweenMsg")+"</option>");
			break;
		case "int":
			$("#restrictiontypeselector").append("<option restrictionType=\"10\">"+GQB.translate("numGreaterMsg")+"</option>");
			$("#restrictiontypeselector").append("<option restrictionType=\"11\">"+GQB.translate("numLessMsg")+"</option>");
			$("#restrictiontypeselector").append("<option restrictionType=\"12\">"+GQB.translate("numBetweenMsg")+"</option>");
			break;
		default:
			alert(GQB.translate("unsupportedRestMsg") + "(type: "+property.range+")");
			break;
	 }
	 $("#restrictiontypeselector").append("<option restrictionType=\"13\">"+GQB.translate("valueEmptyMsg")+"</option>");

};

/**
 * method to find the restriction Type of the restrictiontypeselector
 * @param {Object} member - the restriction member whose type is searched
 * @return the string of the type 
 */
GQBView.prototype.getRestrictionTypeByMember = function(member){
	var result;
	switch(member.restrictionType){
		case "GQBRestrictionStringContains":
			result = GQB.translate("stringContainsMsg");
			break;
		case "GQBRestrictionStringEquals":
			result = GQB.translate("stringEqualsMsg");
			break;
		case "GQBRestrictionDateAfter":
			result = GQB.translate("yearsAfterMsg");
			break;
		case "GQBRestrictionDateBefore":
			result = GQB.translate("yearsBeforeMsg");
			break;
		case "GQBRestrictionDateBetween":
			result = GQB.translate("yearsBetweenMsg");
			break;
		case "GQBRestrictionIntegerBiggerAs":
			result = GQB.translate("numGreaterMsg");
			break;
		case "GQBRestrictionIntegerSmallerAs":
			result = GQB.translate("numLessMsg");
			break;
		case "GQBRestrictionIntegerBetween":
			result = GQB.translate("numBetweenMsg");
			break;
		case "GQBRestrictionHasProperty":
			result = GQB.translate("valueEmptyMsg");
			break;
	}
	return result;
};

/**
 * to show a add Restriction panel at a certain div-point in the tree
 * @param {Object} selClass - selected Class
 * @param {jQuery-Object} jQueryDivToAdd - div Element to which the panel should be added
 */
GQBView.prototype.addRestrictionPanel = function(selClass, jQueryDivToAdd) {
	jQueryDivToAdd.append(GQB.translate("choosePropertyMsg")+": <select id=\"restrictionpropselector\" class=\"gqb-selector\"></select><br/>");
	jQueryDivToAdd.append(GQB.translate("restTypeMsg")+": <select id=\"restrictiontypeselector\" class=\"gqb-selector\"></select>");
	
	for (var i = 0; i < selClass.type.properties.length; i++) {
		$("#restrictionpropselector").append("<option modelId=\""+this.addModelObject(selClass.type.properties[i])+"\" >" + selClass.type.properties[i].getLabel() + "</option>");
	}

	//set initial Restriction types to the SelectBox 
	this.initRestrictionTypeBox();
	//set the input boxes for the values
	jQueryDivToAdd.append("<div id=\"restvalueinputs\"></div>");
	this.initRestrictionValueInputs($("#restvalueinputs"));
	//set the negation checkbox
	jQueryDivToAdd.append("<input type=\'checkbox\' id=\"gqbRestrictionNegation\"/>&nbsp;"+GQB.translate("negateRestMsg")+"<br/>");
	//set the add und cancel button
	jQueryDivToAdd.append("<a id=\"setrestrictionbutton\">"+GQB.translate("Add")+"</a>&nbsp;<a id=\"cancelrestrictionbutton\">"+GQB.translate("Cancel")+"</a><br/>");
};

/**
 * to show a edit Restriction panel at a certain div-point in the tree
 * @param {Object} selClass - selected Class
 * @param {jQuery-Object} jQueryDivToAdd - div Element to which the panel should be added
 */
GQBView.prototype.editRestrictionPanel = function(selClass, jQueryDivToAdd) {
	jQueryDivToAdd.append(GQB.translate("choosePropertyMsg")+": <select id=\"restrictionpropselector\" class=\"gqb-selector\"></select><br/>");
	jQueryDivToAdd.append(GQB.translate("restTypeMsg")+":<select id=\"restrictiontypeselector\" class=\"gqb-selector\"></select>");
	
	for (var i = 0; i < selClass.type.properties.length; i++) {
		$("#restrictionpropselector").append("<option modelId=\""+this.addModelObject(selClass.type.properties[i])+"\" >" + selClass.type.properties[i].getLabel() + "</option>");
	}

	//set the input boxes for the values
	jQueryDivToAdd.append("<div id=\"restvalueinputs\"></div>");
	//set the negation checkbox
	jQueryDivToAdd.append("<input type=\'checkbox\' id=\"gqbRestrictionNegation\"/>&nbsp;"+GQB.translate("negateRestMsg")+"<br/>");
	//set the change und cancel button
	jQueryDivToAdd.append("<a id=\"setrestrictionbutton\">"+GQB.translate("Edit")+"</a>&nbsp;<a id=\"cancelrestrictionbutton\">"+GQB.translate("Cancel")+"</a><br/>");
};

/**
 * For different Restriction-ranges have to be different Valueinput
 * this method creates according to the range the Valueinput
 * @param {jQuery-Object} jQueryDivToAdd - div Element to which the Valueinput should be added
 */
GQBView.prototype.initRestrictionValueInputs = function(jQueryDivToAdd){
	var restrictionType = parseInt($("#restrictiontypeselector :selected").attr("restrictionType"));
	jQueryDivToAdd.empty();
	switch(restrictionType){
		case 0: //string contains
			jQueryDivToAdd.append("<input id=\"restvalueselector\" type=\"text\" value=\"\"><br/>");
			break;
		case 1: //string equals
			jQueryDivToAdd.append("<input id=\"restvalueselector\" type=\"text\" value=\"\"><br/>");
			break;
		case 5: //dates after
			jQueryDivToAdd.append("<input id=\"restvalueselectorA\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"><br/>");
			break;
		case 6: //dates before
			jQueryDivToAdd.append("<input id=\"restvalueselectorB\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"><br/>");
			break;
		case 7: //dates between
			jQueryDivToAdd.append("<input id=\"restvalueselectorA\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"> und ");
			jQueryDivToAdd.append("<input id=\"restvalueselectorB\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"><br/>");
			break;
		case 10: //number bigger
			jQueryDivToAdd.append("<input id=\"restvalueselectorA\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"><br/>");
			break;
		case 11: //number smaller
			jQueryDivToAdd.append("<input id=\"restvalueselectorB\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"><br/>");
			break;
		case 12: //number between
			jQueryDivToAdd.append("<input id=\"restvalueselectorA\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"> und ");
			jQueryDivToAdd.append("<input id=\"restvalueselectorB\" type=\"text\" value=\"\" class=\"gqbclassrestrictionvalueselector\"><br/>");
			break;
		case 13: //"valueEmpty"
			//no value needed
			break;
		default:
			break;
		
	}
};

/** 
 * Returns an object containing the values of the user input fields
 * corresponding to the passed restrictionType.  The object may
 * have members "val1", "applyToVar1", "val2" or "applyToVar2".
 * "val1" and "val2" indicate type converted user input.
 * "applyToVar1" and "applyToVar2" (boolean) indicate whether or not "val1" and "val2"
 * contain URIs of properties, to which the restriction should be applied.
 * @param restrictionType An integer indicating the restriction type (0,1,5,6,7,10,11,12 valid).
 * @return An object as described above.
 */
GQBView.prototype.getRestrictionInputFieldValues = function(restrictionType) {
	var val1 = " ", val2 = " ";
	var applyToVar1 = false, applyToVar2 = false;
	switch(restrictionType){
		case 0: //string contains
			val1 = $("#restvalueselector").attr("value"); break;
		case 1: //string equals
			val1 = $("#restvalueselector").attr("value"); break;
		case 5: //dates after
			val1 = $("#restvalueselectorA").attr("value"); break;
		case 6: //dates before
			val1 = $("#restvalueselectorB").attr("value"); break;
		case 7: //dates between
			val1 = $("#restvalueselectorA").attr("value");
			val2 = $("#restvalueselectorB").attr("value"); break;
		case 10: //number bigger
			val1 = $("#restvalueselectorA").attr("value"); break;
		case 11: //number smaller
			val1 = $("#restvalueselectorB").attr("value"); break;
		case 12: //number between
			val1 = $("#restvalueselectorA").attr("value");
			val2 = $("#restvalueselectorB").attr("value"); break;
		default:
			;
	}

	// restrictions of the form "!<propLabel>" indicate, that a prop is to be compared with another
	if (val1[0] == "!") {
		val1 = val1.slice(1);
		// get the prop uri
		var propUri = null;
		for (var i = 0; i < this.selectedViewClass.modelClass.type.properties.length; i++) {
			if (this.selectedViewClass.modelClass.type.properties[i].getLabel().toLowerCase() == val1.toLowerCase()) {
				propUri = this.selectedViewClass.modelClass.type.properties[i].uri;
				break;
			}
		}
		if (!propUri) {
			// recreate old string if no prop found
			val1 = "!" + val1;
		} else {
			val1 = propUri;
			applyToVar1 = true;
		}
	}
	if (val2[0] == "!") {
		val2 = val2.slice(1);
		// get the prop uri
		var propUri = null;
		for (var i = 0; i < this.selectedViewClass.modelClass.type.properties.length; i++) {
			if (this.selectedViewClass.modelClass.type.properties[i].getLabel().toLowerCase() == val2.toLowerCase()) {
				propUri = this.selectedViewClass.modelClass.type.properties[i].uri;
				break;
			}
		}
		if (!propUri) {
			// recreate old string if no prop found
			val2 = "!" + val2;
		} else {
			val2 = propUri;
			applyToVar2 = true;
		}
	}
	if (!applyToVar1) {
		switch (restrictionType) {
			case 5, 6, 7, 10, 11, 12:  // numeric types
				val1 = parseInt(val1);
		}
	}
	if (!applyToVar2) {
		switch (restrictionType) {
			case 7, 12:  // numeric types with two variables
				val2 = parseInt(val2);
		}
	}
	return { "val1" : val1, "applyToVar1" : applyToVar1, "val2" : val2, "applyToVar2" : applyToVar2 }
};

/** 
 * Creates a new restriction of the given type with the given property and value(s).
 * The first three parameters must be valid, the last two are not always used, but
 * are necessary for some restrictions.
 * @param restrictionType An integer indicating the restriction type (0,1,5,6,7,10,11,12 valid).
 * @param property The GQBProperty of the restrictions.
 * @param val1 The first restriction value (string or int).
 * @param val2 The second restriction value (always int).
 * @param compVar Whether the restriction is applied to a variable or not.
 * @param negation  
 * @return A GQBRestriction* of the proper kind. 
 */
GQBView.prototype.getNewRestrictionOfType = function(restrictionType, property, val1, val2, applyToVar1, applyToVar2, negation) {
	switch(restrictionType){
		case 0: //string contains
			return new GQBRestrictionStringContains(property, val1, applyToVar1, negation); 
			break;
		case 1: //string equals
			return new GQBRestrictionStringEquals(property, val1, applyToVar1, negation);
		case 5: //dates after
			return new GQBRestrictionDateAfter(property, val1, applyToVar1, negation);
		case 6: //dates before
			return new GQBRestrictionDateBefore(property, val1, applyToVar1, negation);
		case 7: //dates between
			return new GQBRestrictionDateBetween(property, val1, val2, applyToVar1, applyToVar2, negation);
		case 10: //number bigger
			return new GQBRestrictionIntegerBiggerAs(property, val1, applyToVar1, negation);
		case 11: //number smaller
			return new GQBRestrictionIntegerSmallerAs(property, val1, applyToVar1, negation);
		case 12: //number between
			return new GQBRestrictionIntegerBetween(property, val1, val2, applyToVar1, applyToVar2, negation);
		case 13: //has property
			return new GQBRestrictionHasProperty(property, negation);
		default:
			return null;
	}
};

/**
 * used when a new restriction is added to the Restriction tree
 * @param {Object} selClass - selected Class
 * @param {int} orIndex - is used when an OR-Restriction is added to find the correct secondlevel part to add
 */
GQBView.prototype.setRestriction = function(selClass, orIndex) {
	var mo_id= parseInt($("#restrictionpropselector :selected").attr("modelId"));
	var property = this.findModelObjectById(mo_id);
	
	var restrictionType = parseInt($("#restrictiontypeselector :selected").attr("restrictionType"));

	//get the restriction values dependent on the type of restriction
	var restrictionInputs = this.getRestrictionInputFieldValues(restrictionType);

	var negation;
	//get the negation checkbox content
	if($('#gqbRestrictionNegation').is(':checked')){
		negation = true;
	}else{
		negation = false;
	}

	var level1Idx;
	if (orIndex == parseInt("-1")) {

		if (selClass.restrictions.hasMember()) {
			level1Idx = selClass.restrictions.members.length; 
		} else{
			level1Idx = 0;
		}
	} else {
		level1Idx = orIndex;
	}

	this.selectedViewClass.modelClass.addRestriction(this.getNewRestrictionOfType(restrictionType, property, 
													restrictionInputs.val1, restrictionInputs.val2, 
													restrictionInputs.applyToVar1, restrictionInputs.applyToVar2, negation), level1Idx);
};

/**
 * used when a restriction has been edited to set the edited one instead of the old one
 * @return edited (new) Member that takes the place of the old one
 */
GQBView.prototype.setEditedRestriction = function() {
	var mo_id= parseInt($("#restrictionpropselector :selected").attr("modelId"));
	var property = this.findModelObjectById(mo_id);
	
	var restrictionType = parseInt($("#restrictiontypeselector :selected").attr("restrictionType"));

	//get the restriction values dependent on the type of restriction
	var restrictionInputs = this.getRestrictionInputFieldValues(restrictionType);

	var negation;
	
	//get the negation checkbox content
	if($('#gqbRestrictionNegation').is(':checked')){
		negation = true;
	}else{
		negation = false;
	}

	var memberToEdit = this.getNewRestrictionOfType(restrictionType, property, 
													restrictionInputs.val1, restrictionInputs.val2, 
													restrictionInputs.applyToVar1, restrictionInputs.applyToVar2, negation);
	
	var obj = this.selectedViewClass.modelClass;
	var gqbEvent = new GQBEvent("editRestriction", obj);
	GQB.controller.notify(gqbEvent);
	this.selectedViewClass.parentViewPattern.modelPattern.recalculateAllNumInstances();
	
	return memberToEdit;
};