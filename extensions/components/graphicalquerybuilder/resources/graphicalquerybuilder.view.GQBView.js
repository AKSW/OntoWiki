/**
 * Returns the position of the passed DOM object relative to the
 *  client window.  Adds up all the DOM offsets until the desired element is reached.
 * @param elementId The id of the DOM object to locate.
 * @return An object with fields "x" and "y" containing the position relative to the client (with scroll state).
 */
GQB.getPositionOfDOMObj = function(elementId) {
	var element = document.getElementById(elementId);

	var left = 0; 
	var top = 0;
	// correct for scroll state: (not necessary when getting cursor pos through jquery (event.pageX/Y))
	//var left = 0-window.pageXOffset;  // IE: document.documentElement.scrollLeft;   DOM standard: document.body.scrollLeft;    
	//var top = 0-window.pageYOffset;   // IE: document.documentElement.scrollTop;   DOM standard: document.body.scrollTop;

	while (element) {
		if (element.x) {  // absolute position
			left += element.x;
			top += element.y; 
			return {x:left, y:top};
		}
		left += element.offsetLeft;
		top += element.offsetTop;
		element = element.offsetParent;
	}
	return {x:left, y:top};
};

/**
 * "things" in the view that are 1:1 representations of objects in the model can be handled with this class
 * e.g. the rdf-classes in the tree: when they are added as HTML, the node gets an attribute "modelId"
 * if you later click on it you have the id, you can find the corresponding model object by using findModelObjectById function
 * that means you dont have to store information (like uri of the rdfclass) in html attributes - you directly get the object itself
 * @param {Object} _obj
 * @class
 */
function GQBViewModelObject (_obj, _viewnode){
	this.id = ++GQB.view.modelObjectCounter;
	this.obj = _obj;
	this.viewnode = _viewnode;
}

/**
 * the view-part of the mvc
 * everthing you see in the browser is created here
 * @class
 */
function GQBView(){
	/** Our main jQuery layout object*/
	this.mainLayout;

	/** An array of GQBViewModelObjects which can keep track of
	 * various model objects as necessary 
	 * @see GQBViewModelObject()
	 */
	this.modelObjects = new Array();
	/** counter is used to generate unique IDs for each modelObject. */
	this.modelObjectCounter = 0;

	/** Our view consists of GQBViewPatterns, each in turn
	* consisting of GQBViewClasses (also known as "boxes") */
	this.patterns = new Array();  // Array of GQBViewPattern

	/** The user can select any of the GQBViewClasses by clicking it.
	* We keep track of which class is currently selected */
	this.selectedViewClass = false;

	/** We need to keep track of dragged and moused over GQBViewClasses
	* for various reasons.  Two additional variables are needed when
	* combining patterns (via union or intersection) */ 
	this.draggedViewClass = false;
	this.mousedOverViewClass = false;
	this.draggedViewClassToCombine = false;
	this.mousedOverViewClassToCombine = false;
	this.mousedOverTrash = false;

	/** We use the Raphael (http://raphaeljs.com/) SVG library to
	* draw boxes, lines and icons onto a canvas.  The following
	* object represents the main Raphael canvas, where all the
	* drawing takes place */
	this.raphaelCanvas;
	/** Width and height of the canvas */
	this.raphCanvasWidth = 460;
	this.raphCanvasHeight = 350;
	/** Our trash is a Raphael icon */
	this.trash;

	/** We store the mouse position when an object from the "west" 
	* tree view is dropped onto the canvas.  This is used to
	* create a box at that position when the model calls our
	* add methods*/
	this.dropCursorPosX = 0;
	this.dropCursorPosY = 0;

	/** color constants 
	* color of normal boxes*/
	this.box_color = "#000000";
	/** color of selected boxes */
	this.sel_color = "#0000FF";
	/** color of mousedover boxes */
	this.mouseover_color = "#FF0000";
	/** color of "black boxed" boxes (actually grey) */
	this.blackbox_color = "#B0B0B0";
	/** color of connections (arrows and lines) */
	this.connection_color = "#000000";
	/** color of connection labels */
	this.conlabel_color = "#0000FF";

	/** We sometimes run a minimal physical simulation, treating all boxes
	* as physical bodies, for graphical effect.  The simulation can run
	* for a specified number of steps before terminating.  This variable
	* holds the number of steps which still remain (if -1 simulation never ends): */
	this.runPhysSteps = 0;
	/** The simulation will run at a specified interval.  The call to
	* setInterval() is stored so that it may be deactivated: */
	this.runPhysInterval = false;

	/** Variables used in displaying the results table:
	* Each results column is itself an array of result strings.
	* the first entry in each result column is the column header.
	* All result columns have the same length (and may thus 
	* contain "blank" results). */
	this.resultColumns = new Array();

	/** the resultRowsMap is used in sorting the rows.  It is a
	* permutation of the result rows (so we don't have to swap
	* in all of the different columns). */
	this.resultRowsMap = new Array();

	/** We can either display a set number of results per result page,
	// or display all results:*/
	this.resultsPerPage = 20;
	this.displayAllResults = false;
	/** the currently displayed results page: */
	this.curResultsPage = 0;
	/** whether or not the first column (which always contains the URIs of
	* the start class of the query) should be displayed: */
	this.displayFirstColumn = true;

	/** When large numbers of result rows must be displayed, we display them
	* in chunks using setInterval() with an interval time of 1ms.  The
	* call to setInterval() is stored so that it can be deactivated on certain
	* events with clearInterval(): */
	this.rowDispInterval = false;

	/**
	 * It is possible to resize results columns by dragging certain handles.
	 * When a column is being resized, we store a jQuery object referring to that
	 * columns header (<th>) element in these variables: 
	 */
	this.draggedColLeftHead = false;
	this.draggedColRightHead = false;

	/**
	 * When resizing columns, we need to know the old column width (before resizing):
	 */
	this.dragColBaseWidth = 0;

	/**
	 * When resizing columns, we need to know at what x position the mouse was pressed:
	 */
	this.dragColMouseDownX = 0;

	// methods (detailed comments in method headers)
	// initialization of layout, canvas, etc. (see view.GQBView.init.js):
	this.init;
	this.initLayout;
	this.initCanvas;
	this.initMouseMoveAndDropHandlers;
	this.initMouseClickHandlers;

	// selects a specified GQBViewClass:
	this.selectViewClass;
	// selects the GQBViewClass of a specified GQBClass:
	this.selectClass;

	// adds a GQBViewModelObject to this.modelObjects:
	this.addModelObject;

	// for filling the various info fields and panels:
	this.showInfo;
	this.showClassProperties;
	this.showPatternProperties;
	this.clearClassProperties;
	this.updateResult;
	this.showResult;
	this.displayResultsByRow;
	this.refreshCurrentResultsTable;
	this.clearResultsTable;

	// for adding objects to the "west" tree view:
	this.addRdfClassesToTree;
	this.addQueriesToTree;
	this.addQueryToTree;

	// called by controller when classes have been
	// added to patterns in the model or on other model events.
	// these methods create all the GQBViewPatterns and GQBViewClasses
	// in the view.
	this.addPatternToCanvas;
	this.addClassToPatternAtPos;
	this.classIsReady;
	this.setBlackBoxClass;

	// for deleting GQBViewPatterns and GQBViewClasses
	// (also delete the corresponding objects in the model):
	this.deletePattern;
	this.deleteClass;

	// for combining classes, which have been dragged ontop of one another:
	this.combineDraggedAndMousedOverClasses;

	// for creating and modifying restrictions (see view.GQBView.restrictions.js):
	this.initRestrictionTypeBox;
	this.getRestrictionTypeByMember;
	this.addRestrictionPanel;
	this.editRestrictionPanel;
	this.initRestrictionValueInputs;
	this.setRestriction;
	this.setEditedRestriction;
	this.getNewRestrictionOfType;
	this.getRestrictionInputFieldValues;

	// various search methods:
	this.findModelObjectById;
	this.findModelObjectOfObject;
	this.findPatternById;
	this.findPatternOfClass;
	this.findViewClassById;

	// refreshes the Raphael canvas to remove streaks:
	this.refreshCanvas;

	// for sorting the results table:
	this.sortColumns;

	// for displaying the loading spinner in the results table:
	this.wait;
	this.unwait;

	// runs a physical simulation which makes the boxes appear
	// to move (used for graphical effect):
	this.runPhysics;
}

/**
 * The results table is displayed in intervals, in order to improve performance
 * when displaying large result sets.  This function will stop the further display
 * of any results by clearing the corresponding interval (which was set by a 
 * call to window.setInterval()).
 */
GQBView.prototype.stopDisplayingRows = function() {
	if (!this.rowDispInterval) return;
	window.clearInterval(this.rowDispInterval);
	this.rowDispInterval = false;
};

/** 
 * Refreshes the Rafael canvas.
 * This is no longer needed.
 */
GQBView.prototype.refreshCanvas = function() {
	if (!this.raphaelCanvas) return;
	// force raphael to refresh the canvas by resizing it,
	// this will clear graphical streaks:
	this.raphaelCanvas.setSize(this.raphCanvasWidth+1,this.raphCanvasHeight);
	this.raphaelCanvas.setSize(this.raphCanvasWidth,this.raphCanvasHeight); 
};

/**
 * Deletes the passed viewPattern from the view and
 * the corresponding model pattern from the model.
 * @param viewPattern The GQBViewPattern to delete from the view.
 */
GQBView.prototype.deletePattern = function(viewPattern) {
	if (!viewPattern) return;

	viewPattern.deletePattern();
	GQB.arrayRemoveObj(GQB.view.patterns, viewPattern);
	// also remove the model pattern:
	GQB.arrayRemoveObj(GQB.model.queryPatterns, viewPattern.modelPattern);

	this.showInfo(GQB.translate("patternDeletedMsg"));
	this.selectedViewClass = this.mousedOverViewClass = this.draggedViewClass = false;
	//this.refreshCanvas();
	this.clearClassProperties();
};

/** 
 * Deletes the passed GQBViewClass from its parent GQBViewPattern and
 * the corresponding model class from its parent model pattern.
 * All child classes of the passed class are also removed.
 * @param viewClass The GQBViewClass to be deleted. 
 */
GQBView.prototype.deleteClass = function(viewClass) {
	if (!viewClass || !viewClass.parentViewPattern) return;

	// if class to delete is start class, delete whole pattern:
	if (viewClass.parentViewPattern.modelPattern.startClass.id == viewClass.id) {
		this.deletePattern(viewClass.parentViewPattern);
		return;
	}

	var parentClass = viewClass.parentViewPattern.deleteClass(viewClass);
	// remove the class in the model:
	viewClass.parentViewPattern.modelPattern.remove(parentClass, viewClass.modelClass);

	if (this.selectedViewClass && !this.findViewClassById(this.selectedViewClass.id)) {
		this.selectClass(parentClass);
		this.showClassProperties();
	}
	if (this.mousedOverViewClass && !this.findViewClassById(this.mousedOverViewClass.id))
		this.mousedOverViewClass = false;
	if (this.draggedViewClass && !this.findViewClassById(this.draggedViewClass.id))
		this.draggedViewClass = false;
	this.showInfo(GQB.translate("classDeletedMsg"));
	//this.refreshCanvas();
};

/** 
 * The following function sorts the result columns.
 * The specified column (cIdx) will be sorted in ascending
 * or descending order (based on mode), and all other columns
 * will be correspondingly matched.  This function actually
 * orders the mapping "resultRowsMap", which is a permutation
 * of the row indices, so that no actual swapping of column 
 * data must occur. All columns must have the same length.
 * @param cIdx The index of the column to be used for sorting.
 * @param mode Sort direction: either "up" or "down".
 */
GQBView.prototype.sortColumns = function(cIdx, mode) {
	if (!this.resultColumns) return;
	if (!this.resultColumns[cIdx]) return; // no results
	this.stopDisplayingRows();

	// all columns have same length
	var colLength = GQB.view.resultColumns[cIdx].length;

	// To start, map all rows onto themselves (no sort):
	// (all columns have same length)
	for(var i = 0; i < colLength; i++) {
		this.resultRowsMap[i] = i;
	}

	// QUICK sort (non recursive in place):
	var limits = new Array();
	limits.push(colLength-1);
	limits.push(1);  // first row contains column headers
	while (limits.length > 0) {
		var lidx = limits.pop();
		var ridx = limits.pop();
		if (lidx >= ridx) continue;
		var oldlidx = lidx, oldridx = ridx;
		var pivot = this.resultColumns[cIdx][this.resultRowsMap[ridx]].value+"";
		do {
			while (this.resultColumns[cIdx][this.resultRowsMap[lidx]].value+"" < pivot) lidx++;
			while (this.resultColumns[cIdx][this.resultRowsMap[ridx]].value+"" > pivot) ridx--;
			if (lidx <= ridx) {
				var temp = this.resultRowsMap[lidx];
				this.resultRowsMap[lidx] = this.resultRowsMap[ridx];
				this.resultRowsMap[ridx] = temp;
				lidx++;
				ridx--;
			}
		} while (lidx <= ridx);
		limits.push(ridx); limits.push(oldlidx);
		limits.push(oldridx); limits.push(lidx);
	}
	// reverse direction if necessary
	if (mode == "down") {
		var newRRM = new Array();
		for (var i = 1; i < colLength; i++) {
			newRRM[i] = this.resultRowsMap[colLength-i];
		}
		for (var i = 1; i < colLength; i++) {
			this.resultRowsMap[i] = newRRM[i];
		}
	}
};

/**
 * GQBView.combineDraggedAndMousedOverClasses()
 * Constructs the union or intersection of two objects, one of which has been
 * dragged onto the other.  The variables GQB.view.draggedViewClassToCombine and
 * GQB.view.mousedOverViewClassToCombine are set in the mouseup handler (and SHOULD NOT
 * be set anywhere else!!), and are used as additional input to this function.
 * The classes are assumed to be separate (with different ids) and the properties
 * for the moused over class are assumed to be ready.  We also assume that the classes
 * are of the same type (meaning their uris are identical). These checks are performed 
 * in the mouseup handler (and thus are not repeated here).
 * @param mode Either "union" or "intersection".
 */
GQBView.prototype.combineDraggedAndMousedOverClasses = function(mode){
	var draggedViewClass = this.draggedViewClassToCombine;
	var mousedOverViewClass = this.mousedOverViewClassToCombine;
	if (!draggedViewClass || !mousedOverViewClass) return false;

	// get the GQBClasses and GQBPatterns (in the model) of the
	// dragged and moused over classes:
	var draggedClass = draggedViewClass.modelClass;
	var draggedPattern = draggedViewClass.parentViewPattern.modelPattern;
	var mousedOverClass = mousedOverViewClass.modelClass;
	var mousedOverPattern = mousedOverViewClass.parentViewPattern.modelPattern;

	// When combining two patterns, at least one of the classes to combine must
	// be a start class.  Combining two classes which belong to the same pattern
	// is also not possible.
	if ((draggedPattern.startClass.id == draggedClass.id ||
		mousedOverPattern.startClass.id == mousedOverClass.id) &&
		draggedPattern.id != mousedOverPattern.id) {

		// combine the classes in the model:
		mousedOverClass.mergeWithClass(draggedClass, mode);

		// get the GQBViewPatterns of the classes:
		var draggedViewPattern = draggedViewClass.parentViewPattern;
		var mousedOverViewPattern = mousedOverViewClass.parentViewPattern;

		// revert both from black box mode if necessary:
		draggedViewPattern.fromBlackBox();
		mousedOverViewPattern.fromBlackBox();

		// add each class in the dragged pattern to the moused over pattern,
		// except for the class which was itself dragged (which gets deleted):
		for (var i = 0; i < draggedViewPattern.classes.length; i++) { 
			if (draggedViewPattern.classes[i].id != draggedViewClass.id) {
				mousedOverViewPattern.classes.push(draggedViewPattern.classes[i]);
				draggedViewPattern.classes[i].parentViewPattern = mousedOverViewPattern;
			} 
		}
		// add each connection in the dragged pattern to the moused over pattern,
		// except for connections from the dragged class itself (which is start class,
		// so has no incoming connections).  We must reattach all outgoing 
		// connections from the dragged class onto the moused over class:
		for (var i = 0; i < draggedViewPattern.connections.length; i++) {
			if (draggedViewPattern.connections[i].startViewClass.id != draggedViewClass.id) {
				mousedOverViewPattern.connections.push(draggedViewPattern.connections[i]);    
			} 
			else {
			  // remove and reattach:
				draggedViewPattern.connections[i].remove();
				var con = new GQBViewConnection(mousedOverViewClass, 
																						draggedViewPattern.connections[i].endViewClass, 
																						draggedViewPattern.connections[i].modelLink,
																						draggedViewPattern.connections[i].label);
				mousedOverViewPattern.connections.push(con);
			}
		}

		// remove dragged class:
		draggedViewClass.raphObjSet.remove();
		GQB.arrayRemoveObj(GQB.view.patterns, draggedViewPattern);
		GQB.arrayRemoveObj(GQB.model.queryPatterns, draggedPattern);
		//this.refreshCanvas();
	}

	this.draggedViewClassToCombine = this.mousedOverViewClassToCombine = this.selectedViewClass = this.mousedOverViewClass = this.draggedViewClass = false;
	this.clearClassProperties();
};

/**
 * Finds the view pattern to a corresponding view class.
 * Better is usually just viewClass.parentViewPattern.
 * @param viewClass The class whose pattern should be returned.
 * @return The GQBViewPattern containing viewClass or null if not found.
 */
GQBView.prototype.findPatternOfClass = function(viewClass) {
	if (!viewClass) return null;
	
	for(var i = 0; i < this.patterns.length; i++) {
		for(var j = 0; j < this.patterns[i].classes.length; j++) {
			if(this.patterns[i].classes[j].id == viewClass.id)
				return this.patterns[i];
		}
	}
	return null;
};

/**
 * 
 * @param {Object} id
 */
GQBView.prototype.findModelObjectById = function(id){
	for(var i = 0; i < this.modelObjects.length; i++){
		if(this.modelObjects[i].id == id){
			return this.modelObjects[i].obj;
		}
	} 
	return null;
};

/**
 * Searches the view for a GQBViewPattern with the
 * passed id.
 * @param id The id of the GQBViewPattern to find.
 * @return The GQBViewPattern with id or null if not found.
 */
GQBView.prototype.findPatternById = function(id) {
	for(var i = 0; i < this.patterns.length; i++){
		if(this.patterns[i].id == id){
			return this.patterns[i];
		}
	}
	return null;
};

/**
 * Searches through all GQBViewPatterns of the view for 
 * a GQBViewClass with the passed id.
 * @param id The id of the GQBViewClass to find.
 * @return The GQBViewClass with id or null if not found.
 */
GQBView.prototype.findViewClassById = function(id) {
	for (var i = 0; i < this.patterns.length; i++) {
		var found = this.patterns[i].findViewClassById(id);
		if (found) return found;
	}
	return null;
};

/**
 * Searches for a GQBViewModelObject representing the passed obj.
 * @param obj The model object which the desired GQBViewModelObject represents.
 * @return The GQBViewModelObject corresponding to obj or null if not found.
 */
GQBView.prototype.findModelObjectOfObject = function(obj){
	for(var i = 0; i < this.modelObjects.length; i++){
		if(this.modelObjects[i].obj === obj){
			return this.modelObjects[i];
		}
	} 
	return null;
};

/**
 * Adds a new GQBViewModelObject representing the passed obj
 * to the view.  Only one such GQBViewModelObject may exist,
 * so this function will fail if one has already been added.
 * @param obj The model object to which the new GQBViewModelObject should correspond.
 * @return The unique id of the (new or old) GQBViewModelObject representing "obj".
 */
GQBView.prototype.addModelObject = function(obj){
	var foundobj = this.findModelObjectOfObject(obj);
	if(!foundobj){
		var createdobj = new GQBViewModelObject(obj);
		this.modelObjects.push(createdobj);
		return createdobj.id;
	} else {
		return foundobj.id;
	}
};

/**
 * Display a message in the info-field (e.g. errors or the query or hints like "click on some buttons to do something")
 * @param info - the text to display
 */
GQBView.prototype.showInfo = function(info){
	// a hack to html-encode the text (looks strange, but its clean)
	$('#gqbinfo').html($('<div/>').text(info).html().replace(/\n/g, "<br />"));
};

/**
 * Shows a loading spinner in the results area.
 */
GQBView.prototype.wait = function (){
	$("#gqbwait").show();
};

/**
 * Hides the loading spinner in the results area.
 */
GQBView.prototype.unwait = function (){
	$("#gqbwait").hide();
};

/**
 * Sets the passed GQBViewClass as the selected class,
 * deselecting all other classes.  Shows the properties
 * of the newly selected class.
 * @param viewClass The GQBViewClass to select.
 * @param force If false, a new selection will only occur if
 *        the user isn't currently viewing the east pane.
 */
GQBView.prototype.selectViewClass = function(viewClass, force) {
	if (!viewClass) return;
	if (!this.findPatternOfClass(viewClass)) return;
	// don't select a new class if:
		// 1. force is false and
		// 2. User is viewing the east pane and
		// 3. there is a currently selected class and
		// 4. the currently selected class is not the passed viewClass
	if ((force == false) && this.selectedViewClass &&
				(viewClass.modelClass.id != this.selectedViewClass.modelClass.id)) return;
	if (this.selectedViewClass) this.selectedViewClass.unselect();
	this.selectedViewClass = viewClass;
	viewClass.select();
	this.showClassProperties();
};

/**
 * Sets the GQBViewClass corresponding to the passed GQBClass
 * as the currently selected class (if found).
 * Deselects previously selected class.
 * @param modelClass The GQBClass whose corresponding GQBViewClass should be selected.
 * @param force If false, the selection will only occur if
 *        the user isn't currently viewing the east pane.
 */
GQBView.prototype.selectClass = function(modelClass, force){
	var viewClassToSelect;
	for (var i = 0; i < this.patterns.length; i++) {
		for (var j = 0; j < this.patterns[i].classes.length; j++) {
			if (this.patterns[i].classes[j].modelClass.id == modelClass.id) {
				this.selectViewClass(this.patterns[i].classes[j], force);
				break;
			}
		}
	}
};

/**
 * Add an array of rdf-classes to the tree view in the "west" pane.
 * @param nclasses An array of GQBRdfClasses to be added to the tree.
 */
GQBView.prototype.addRdfClassesToTree = function (nclasses){
	var change = false;
	var htmlToAppend = "";
	var classToAddMOId;
	do {
		change = false;
		for (var i = 0; i < nclasses.length; i++) {
			if (GQB.view.findModelObjectOfObject(nclasses[i])) continue;
			var allParentsFound = true;
			for (var j = 0; j < nclasses[i].parents.length; j++) {
				if (!GQB.view.findModelObjectOfObject(nclasses[i].parents[j])) {
					allParentsFound = false;
					break;
				} 
			}
			if (!allParentsFound) continue;
			classToAddMOId = this.addModelObject(nclasses[i]);
			change = true;

			if (nclasses[i].children.length == 0) {
				htmlToAppend = $("<li></li>");
				var span = $("<span></span>").addClass("file").addClass("basicclass");
				span.attr("modelId", classToAddMOId);
				span.html(nclasses[i].getLabel());
				htmlToAppend.append(span);
			}
			else {
				htmlToAppend = $("<li class='open lastCollapsable'><span class='folder basicclass' modelId=\""+classToAddMOId+"\">"+nclasses[i].getLabel()+"</span><ul><li><span style=\"display:none;\">dummy</span></li></ul></li>");
			}

			if (nclasses[i].parents.length == 0) 
				htmlToAppend.appendTo("#gqbsavedqueriestree-basic");
			else 
				htmlToAppend.appendTo($(".basicclass[modelId='"+this.findModelObjectOfObject(nclasses[i].directParent).id+"']").siblings("ul"));
			if (nclasses[i].children.length != 0)
				$("#gqbsavedqueriestree").treeview({add:htmlToAppend});
		}
	} while (change);

	// Set the new tree objects to be draggable:
	$(".basicclass").draggable({
		appendTo: '#gqbmain',
		iframeFix: false,
		cursorAt: { right: 0 },
		zIndex: 1000,
		ghosting: true,
		revert: true,
		opacity: 0.7,
		scroll: false,
		stack: {group:'#gqbcanvas',min: 50},
		helper: 'clone'
	});

	//this.mainLayout.resizeAll();
};

/**
 * Add an array of saved queries to the tree view in the "west" pane.
 * @param queries An array of (???) to add to the tree.
 */
GQBView.prototype.addQueriesToTree = function (queries){
	$('#gqbsavedqueriestree-saved-categories').empty();
	for(var i = 0; i < queries.length; i++){
		this.addQueryToTree(queries[i])
	}
	this.mainLayout.resizeAll();
};

/**
 * Add a single saved queries to the tree view in the "west" pane.
 * @param query The query to add.
 */
GQBView.prototype.addQueryToTree = function(query){
	//find folder
	var folder = $('#gqbsavedqueriestree-saved-categories li[name='+query.type.getLabel()+']'); 
	if(folder.size() == 0){
		folder = $("<li class='open lastCollapsable' name=\""+query.type.getLabel()+"\"><span class='folder' modelId=\""+this.addModelObject(query.type)+"\">"+query.type.getLabel()+"</span><ul><li><span style=\"display:none;\">dummy</span></li></ul></li>").prependTo("#gqbsavedqueriestree-saved-categories");
		$("#gqbsavedqueriestree").treeview({add:folder});
	}
	var folder_ul = folder.find("ul");

	//make new list element
	var mo_id = this.addModelObject(query);
	var li = $("<li></li>").attr("modelId", mo_id);
	var span = $("<span></span>").addClass("file").addClass("savedquery");
	span.attr("modelId", mo_id);
	span.html(query.name);
	var delbutton = $("<span>&nbsp;&nbsp;&nbsp;</span>").addClass("gqb-button-deletequery").attr("modelId", mo_id);
	li.append(span);
	span.append(delbutton);

	// So the queries can be dragged onto the canvas:
	span.draggable({
		appendTo: '#gqbmain',
		iframeFix: false,
		cursorAt: { right: 0 },
		zIndex: 1000,
		ghosting: true,
		revert: true,
		opacity: 0.7,
		scroll: false,
		stack: {group:'#gqbcanvas',min: 50},
		helper: 'clone'
	});

	folder_ul.append(li);
};

/**
 * Adds a new GQBViewPattern to the view which corresponds to
 * the passed GQBPattern (model pattern).  This function is 
 * called by the controller whenever a pattern is added to the model.
 * @param pattern The GQBPattern which corresponds to the GQBViewPattern to be added.
 */
GQBView.prototype.addPatternToCanvas = function (pattern){
	this.patterns.push(new GQBViewPattern(pattern.id));
};

/**
 * Called by the controller when a pattern has been expanded in the model.
 * The view responds by expanding the corresponding GQBViewPattern.  A canvas
 * position of the new view class can be optionally indicated.
 * @param pattern The GQBQueryPattern which has been expanded.
 * @param classToAdd The GQBClass which has been added to "pattern".
 * @param existingParentClass The GQBClass which is the new parent of "classToAdd" in the model.
 * @param x (optional) Canvas x position of the new boxes center point.  
 *						If ommitted: uses last mouse drop position.
 * @param y (optional) Canvas y position of the new boxes center point.  
 *						If ommitted: uses last mouse drop position.
 */
GQBView.prototype.addClassToPatternAtPos = function (pattern, classToAdd, existingParentClass, x, y) {
  // if no coordinates are given, we must pick them: we use position where last drop occured.
	// first determine the position of the canvas:
	var canPos = GQB.getPositionOfDOMObj("gqbcanvas");
	// subtract canvas position from cursor position to get relative position:
	if (!x)
		x = GQB.view.dropCursorPosX - canPos.x;
	if (!y)
		y = GQB.view.dropCursorPosY - canPos.y;

	// Find the proper GQBViewPattern to which to add the new class, and add it.
	// That class gets a waiting spinner if it isn't loaded yet in the model.
	for (var i = 0; i < this.patterns.length; i++) {
		if (this.patterns[i].id == pattern.id) {
			var newViewClass = this.patterns[i].addClassToPatternAtPos(classToAdd, existingParentClass, x, y);
			if (newViewClass && !newViewClass.modelClass.isReady()) {
				newViewClass.wait();
			}
			return;
		}
	}
};

/**
 * Called by the controller to indicate that the passed GQBrdfClass has gotten all
 * properties and links and is otherwise "ready".
 * The view responds by removing the waiting spinner from all classes of the same type.
 * @param rdfClass The GQBrdfClass which is ready and has all of its properties.
 */
GQBView.prototype.classIsReady = function(rdfClass) {
	if (!rdfClass || !rdfClass.uri) return;
	// unwait all view classes that have the same type as the passed model class:
	for (var i = 0; i < this.patterns.length; i++) {
		for (var j = 0; j < this.patterns[i].classes.length; j++) {
			if (this.patterns[i].classes[j].modelClass.type.uri == rdfClass.uri) {
				this.patterns[i].classes[j].unwait();
				// additionally try updating the east pane (no force)
				this.selectViewClass(this.patterns[i].classes[j], false);
			}
		}
	}
};

/**
 * Set a GQBViewClass to be the "black box class" of its GQBViewPattern
 * (see also comments there).  This is called by the controller in response
 * to "setSelectedClass" events in the model.
 * @param modelClass The GQBClass which was selected in the model.
 */
GQBView.prototype.setBlackBoxClass = function(modelClass) {
	if (!modelClass) return;
	for (var i = 0; i < this.patterns.length; i++) {
		for (var j = 0; j < this.patterns[i].classes.length; j++) {
			if (this.patterns[i].classes[j].modelClass.id == modelClass.id) {
				this.patterns[i].setBlackBoxClass(this.patterns[i].classes[j]);
				return;
			}
		}
	}
};

/**
 * Hides the current result table and displays a spinner animation 
 * updating is taking place.
 * This function calls getResults().
 */
GQBView.prototype.updateResult = function (){
	if(GQB.view.selectedViewClass) {
		var curPattern = GQB.view.selectedViewClass.parentViewPattern.modelPattern;

		if (curPattern.numInstances == "1000+") {
			if (!confirm(GQB.translate("largeQueryConfirmContinueMsg"))) return;
		}

		$('#gqbresultwait').show();
		$('#gqbresultsparqlquery').html("<h1 class='title'>"+GQB.translate("sparqlQueryLbl")+"</h1><p>"+$('<div/>').text(curPattern.getQueryAsString()).html().replace(/\n/g, "<br />")+"</p>");
		$('#gqbresultquerydesc').html("<h1 class='title'>"+curPattern.name+"</h1><p>"+curPattern.description+"</p>");

		// if the start class of the selected pattern has no selected properties,
		// display the URI (always first column) per default
		GQB.view.displayFirstColumn = GQB.view.selectedViewClass.parentViewPattern.modelPattern.startClass.showUri || GQB.view.selectedViewClass.parentViewPattern.modelPattern.startClass.selectedProperties.length == 0;
		curPattern.getResults();

		$.scrollTo( '#gqbresulttable', 800 );
		$('#gqbresulttable').hide(); 
	} else {
		alert (GQB.translate("noPatternSelMsg"));
	}
};

/**
 * Clears the results table.
 */
GQBView.prototype.clearResultsTable = function() {
	$('#gqbresulttable tbody').empty();
	$('#gqbresulttable thead tr').empty();
	this.curResultsPage = 0;
	this.resultColumns.length = 0;
	this.resultRowsMap.length = 0;
	$("#gqbpagedisplay").html("&nbsp;&nbsp;&nbsp;&nbsp;<b>"+GQB.translate("page")+": 0/0</b>");
	$('#gqbresultwait').hide();
	$('#gqbresulttable').show();
	this.unwait();
};

/**
 * Shows the result of a query in the results table below the canvas.
 * This function first parses the results and fills the variable "resultColumns",
 * before calling displayResultsByRow().
 * @param result A JSON encoded object containing the results of a SPARQL-query.
 */
GQBView.prototype.showResult = function (result){
	this.stopDisplayingRows();
	if ($('#gqbresulttable').length > 0) {
            $('#gqbresulttable').replaceWith(result);
        } else {
            $('.innercontent').append(result);
        }
	
	return;
	
	//old 
	
	// Clear results table if no results:
	if (result.vars.length == 0) {
		this.clearResultsTable();
		return;
	}

	// The results will be stored in "resultColumns".
	// resultRowsMap is used in sortig
	this.resultColumns = new Array(); // one column for each variable
	this.resultRowsMap = new Array(); // mapping for column rows, for easy sorting
	this.resultRowsMap[0] = 0;

	// The first entry of each column is the column header:
	// (parsing out the SPARQL variable names up to the first "_"):
	for (var i = 0; i < result.vars.length; i++){
		this.resultColumns[i] = new Array();
		this.resultColumns[i].push(result.vars[i].slice(result.vars[i].indexOf("_")+1));
	}

	// Fill the results columns.  If no result for
	// a certain variable is available, use an empty result (" "):
	var emptyResult = {value : " &nbsp;", type : ""};
	jQuery.each(result.bindings, function(){
		for (var i = 0; i < result.vars.length; i++){
			var varFound = false;
			for(variable in this){
				if(result.vars[i] == variable) {
					GQB.view.resultColumns[i].push(this[variable]);
					varFound = true;
					break;
				}
			}
			if(!varFound) GQB.view.resultColumns[i].push(emptyResult);
		}
	});

	// remove empty result rows
	for (var r = 1; r < this.resultColumns[0].length; r++) {
		var skipRow = true;
		for (var c = this.displayFirstColumn ? 0 : 1; c < this.resultColumns.length; c++) {
			if (this.resultColumns[c][r].type.length > 0) {
				skipRow = false;
				break;
			}
		}
		if (skipRow) {
			for (var c = 0; c < this.resultColumns.length; c++) {
				var newCol = new Array();
				for (var r2 = 0; r2 < this.resultColumns[c].length; r2++) {
					if (r == r2) continue;
					newCol.push(this.resultColumns[c][r2]);
				}
				this.resultColumns[c] = newCol;
			}
			r--;
		}
	}

	// if no rows remain, clear table:
	if (this.resultColumns[0].length <= 1) {
		this.clearResultsTable();
		return;
	}

	// Set up the default row mapping, which is simply the order
	// in which the results were returned from the server:
	for(var i = 1; i < this.resultColumns[0].length; i++) {
		this.resultRowsMap[i] = i;
	}

	// display the results by page using the current number of results per page:
	if (this.displayAllResults) this.resultsPerPage = this.resultColumns[0].length;
	this.curResultsPage = 0;
	$("#gqbpagedisplay").html("&nbsp;&nbsp;&nbsp;&nbsp;<b>"+GQB.translate("numResultsLbl", GQB.view.curResultsPage*GQB.view.resultsPerPage+1, ((GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage)<=(GQB.view.resultColumns[0].length-1)?(GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage):(GQB.view.resultColumns[0].length-1)), GQB.view.resultColumns[0].length-1)+"</b>");
	this.displayResultsByRow(1,this.resultsPerPage);
};

// function to display columns from rowStart to rowEnd:
GQBView.prototype.displayResultsByRow = function(rowStart,rowEnd) {
	if (this.resultColumns.length == 0 || this.resultColumns[0].length == 0) return;  // no results yet
	this.stopDisplayingRows();

	// check for good arguments:
	if (rowStart == undefined) rowStart = 1;
	if (rowEnd == undefined) rowEnd = this.resultColumns[0].length - 1;

	if (rowStart < 1 || rowStart >= this.resultColumns[0].length || rowStart > rowEnd) return;

	if (rowEnd >= this.resultColumns[0].length) {
		rowEnd = this.resultColumns[0].length - 1;
	}

	// clear table
	$('#gqbresulttablebody').empty();
	$('#gqbresulttablehead > tr').empty();

	// display column headers:
	var me = this;
	var c = 0;
	if (!this.displayFirstColumn) c = 1;
	for(; c < this.resultColumns.length; c++) {
		// the following long line adds to each column header:
		//   sort up and sort down buttons
		//   move left and move right buttons
		//   the header text
		$('#gqbresulttablehead > tr').append($("<th style=\"padding: 2px;\"></th>").html("<table style=\"border-width: 0px; padding: 0px; width=100%;\"><tr>"+
				(((this.displayFirstColumn && c > 0) || c > 1) ? "<td style=\"border-width: 0px; padding: 0px; vertical-align:middle;\"><a cIdx="+c+" class=\"gqb-button-movecolleft\"><img src=\"../../../extensions/components/graphicalquerybuilder/resources/images/toggle-lt.gif\" title='"+GQB.translate("moveColLeftLabel")+"'></a></td>" : "")+
				"<td style=\"border-width: 0px; padding: 0px; vertical-align:middle;\">"+
				"<span class=\"gqbcolumnheader\" colId=\""+c+"\" style=\"background: #EFEFEF;\">"+this.resultColumns[c][0].substring(0,1).toUpperCase()+this.resultColumns[c][0].substring(1)+"</span>&nbsp;&nbsp;"+
				"</td><td style=\"border-width: 0px; padding: 0px; vertical-align:middle;\">"+
				"<div style='width: 38px; float: right;' class='gqb-button-sort'><a cIdx="+c+" class=\"gqb-button-sortdesc\"><img src='"+urlBase+"extensions/components/graphicalquerybuilder/resources/images/icon-downarrow.png' title='"+GQB.translate("sortColDescMsg")+"' /></a>&nbsp;<a cIdx="+c+" class=\"gqb-button-sortasc\"><img src='../../../extensions/components/graphicalquerybuilder/resources/images/icon-uparrow.png' title='"+GQB.translate("sortColAscMsg")+"' /></a></div>"+
				"</td>"+
				((c < this.resultColumns.length - 1) ? "<td style=\"border-width: 0px; padding: 0px; vertical-align:middle;\"><a cIdx="+c+" class=\"gqb-button-movecolright\"><img src=\"../../../extensions/components/graphicalquerybuilder/resources/images/toggle-rt.gif\" title='"+GQB.translate("moveColRightLabel")+"'></a></td>" : "")+
				"</tr></table>"));
		
		//$('#gqbresulttable thead tr').append($('<th style=\"vertical-align:baseline;\"></th>').html(""+(((this.displayFirstColumn && c > 0) || c > 1) ? "<a cIdx="+c+" class=\"gqb-button-movecolleft\"><img src=\"../../../extensions/components/graphicalquerybuilder/resources/images/toggle-lt.gif\" title='"+GQB.translate("moveColLeftLabel")+"'></a>" : "") + (c < this.resultColumns.length - 1 ? "<a cIdx="+c+" class=\"gqb-button-movecolright\"><img src=\"../../../extensions/components/graphicalquerybuilder/resources/images/toggle-rt.gif\" title='"+GQB.translate("moveColRightLabel")+"'></a>" : "") + "<div style='width: 38px; float: right;' class='gqb-button-sort'><a cIdx="+c+" class=\"gqb-button-sortdesc\"><img src='../../../extensions/components/graphicalquerybuilder/resources/images/icon-downarrow.png' title='"+GQB.translate("sortColDescMsg")+"' /></a>&nbsp;<a cIdx="+c+" class=\"gqb-button-sortasc\"><img src='../../../extensions/components/graphicalquerybuilder/resources/images/icon-uparrow.png' title='"+GQB.translate("sortColAscMsg")+"' /></a></div><span class=\"gqbcolumnheader\" colId=\""+c+"\">"+this.resultColumns[c][0].substring(0,1).toUpperCase()+this.resultColumns[c][0].substring(1)+"</span>&nbsp;&nbsp;"));
	}
	// make the column header text editable by turning it into an
	// input field on click:
	$(".gqbcolumnheader").click(function(){
		if ($(this).find("#gqbcolheadinput").length == 0) {
			$(this).html($("<input id=\"gqbcolheadinput\" value=\""+$(this).html()+"\" />").blur(function(){
				if($(this).attr("value").replace(/\s/g, "").length == 0) $(this).attr("value", "&nbsp;&nbsp;&nbsp;&nbsp;");
				GQB.view.resultColumns[$(this).parent().attr("colId")][0] = $(this).attr("value");
				$(this).parent().html($(this).attr("value"));
			}));
			$(this).find("#gqbcolheadinput").focus();
		}
	});

	// display desired rows:
	// We display rows in intervals, with the number of rows being displayed
	// in each 1ms interval depending on the number of result columns.
	// The more columns there are, the fewer rows to display in each interval,
	// because otherwise we would get slowdowns.
	var row = rowStart;
	var rowsPerMs = 30;
	if (this.resultColumns.length >= 3 && this.resultColumns.length <= 4) rowsPerMs = 20;
	else if (this.resultColumns.length > 4) rowsPerMs = 15;
	this.rowDispInterval = window.setInterval(function displaySomeRows (){
		for (var r = 0; r < rowsPerMs && row <= rowEnd && GQB.view.rowDispInterval; r++) {
			var curRow = $('<tr></tr>').appendTo($('#gqbresulttablebody'));
			for (var c = GQB.view.displayFirstColumn ? 0 : 1; c < GQB.view.resultColumns.length && GQB.view.rowDispInterval; c++) {
				if(GQB.view.resultColumns[c][GQB.view.resultRowsMap[row]].type == "uri"){
					curRow.append($('<td></td>').append($("<a></a>").attr("href", urlBase + "view/?r=" + GQB.view.resultColumns[c][GQB.view.resultRowsMap[row]].value).html(GQB.view.resultColumns[c][GQB.view.resultRowsMap[row]].value)));
				} else {
					curRow.append($('<td></td>').html(GQB.view.resultColumns[c][GQB.view.resultRowsMap[row]].value));
				}
			}
			row++;
			if (row > rowEnd) { window.clearInterval(GQB.view.rowDispInterval); GQB.view.rowDispInterval = false; }
		}
		if (row > rowEnd) { window.clearInterval(GQB.view.rowDispInterval); GQB.view.rowDispInterval = false; }
	},1);

	$('#gqbresultwait').hide();
	$('#gqbresulttable').show();
	this.unwait();
};

/**
 * This function will redisplay the results table at the current page without changing
 * the results themselves.  This is useful if some graphical changes have been made to
 * the table like a column switch or a sort.
 */
GQBView.prototype.refreshCurrentResultsTable = function() {
	this.displayResultsByRow(this.curResultsPage*this.resultsPerPage+1, 
														 (this.curResultsPage+1)*this.resultsPerPage)
};

/**
 * Clear the "east" pane of all class specific properties, links, etc..
 */
GQBView.prototype.clearClassProperties = function() {
	//hide both boxes from layout
	$('#gqbPatternProperties').hide();
	$('#gqbClassProperties').hide();

	//remove old data
	$('#gqbObjPropertiesType').html("");
	$('#gqbClassPropertiesProperties').empty();
	$('#gqbClassPropertiesSelectedproperties').empty();
	$('#gqbClassPropertiesOutgoinglinks').empty();
	$('#gqbClassPropertiesSelectedlinks').empty();
	$('#gqbclassrestrictionsexist').empty();
	$('#gqbClassPropertiesAddRestriction').empty();
	$('gqbClassPropertiesRdftype').html("");
	$('#gqbClassPropertiesNumInstances').html("0");
	$('gqbClassPropertiesWithChilds').attr("checked", "");
};

/**
 * Populate the properties of the currently selected pattern in the "east" pane.
 */
GQBView.prototype.showPatternProperties = function() {
	if (!this.selectedViewClass) return;
	var currentpattern = this.selectedViewClass.parentViewPattern.modelPattern;
	$('#gqbClassProperties').hide();
	$('#gqbObjPropertiesType').html("Pattern");
	$('#gqbPatternPropertiesName').val(currentpattern.name);
	$("#gqbPatternPropertiesInstLabel").html(GQB.translate("patternInstFieldLbl",currentpattern.numInstances));
	$('#gqbPatternPropertiesDesc').val(currentpattern.description);
	$('#gqbPatternPropertiesSave').attr("modelId", this.addModelObject(currentpattern));
	$('#gqbPatternProperties').show();

	$('#gqbPatternPropertiesDistinct').attr("modelId", this.addModelObject(currentpattern));
	$('#gqbPatternPropertiesDistinct').attr("checked", currentpattern.distinct);

	$('#gqbPatternPropertiesSelectedClass').attr("modelId", this.addModelObject(currentpattern));
	var classes = currentpattern.getClassesFlat();
	var curradio;
	$('#gqbPatternPropertiesSelectedClass').empty();
	for(var i=0; i<classes.length; i++){
		curradio = $('<input type=\"radio\" class=\"gqbPatternPropertiesSelectedClassRadio\" name=\"gqbPatternPropertiesSelectedClass\" modelId=\"'+this.addModelObject(classes[i])+'\"/>');
		if(currentpattern.selectedClass && currentpattern.selectedClass.id == classes[i].id){
			curradio.attr("checked", "checked");
		}
		$('#gqbPatternPropertiesSelectedClass').append(curradio).append(" "+classes[i].type.getLabel()+"<br />");
	}
};

/**
 * Populate the properties of the currently selected class in the "east" pane.
 */
GQBView.prototype.showClassProperties = function() {
	if (!this.selectedViewClass) return;
	var currentclass = this.selectedViewClass.modelClass;
	this.clearClassProperties();

	//load new data
	$('#gqbObjPropertiesType').html(GQB.translate("eastPanelClassLbl"));
	$('#gqbClassPropertiesRdftype').html(currentclass.type.getLabel());
	$('#gqbClassPropertiesNumInstances').html(currentclass.numInstances);
	$('#gqbShowPatternButton').attr("modelId", this.addModelObject(GQB.model.findPatternOfClass(currentclass)));
	$('#gqbClassPropertiesWithChildsLabel').html(GQB.translate("classChildsFieldLbl"));

	$('#gqbClassPropertiesWithChilds').attr("modelId" , this.addModelObject(currentclass));
	if (currentclass.withChilds) {
		$('#gqbClassPropertiesWithChilds').attr("checked", "checked");
	}

	if (currentclass.type.properties.length == 0) {
		$('#gqbClassPropertiesProperties').append('<li>'+GQB.translate("none")+'</li>');
	}
	else {
		var curcheckbox;
		var curli;
		curcheckbox = $('<input type=\'checkbox\' id=\"gqbClassPropertiesShowUriCheckBox\" modelId=\"' + this.addModelObject(currentclass) + '\" />');
		if (currentclass.showUri) {
			curcheckbox.attr("checked", "checked");
		}
		$('#gqbClassPropertiesProperties').append($('<li></li>').append(curcheckbox).append(' URI'));

		for (var i = 0; i < currentclass.type.properties.length; i++) {
			curcheckbox = $('<input type=\'checkbox\' class=\"classPropertiesPropertiesCheckBox\" modelId=\"' + this.addModelObject(currentclass.type.properties[i]) + '\" />');
			if (GQB.arrayContains(currentclass.selectedProperties, currentclass.type.properties[i])) {
				curcheckbox.attr("checked", "checked");
			}
			curli = $('<li></li>').append(curcheckbox).append(' ' + currentclass.type.properties[i].getLabel());
			if (GQB.arrayContains(currentclass.type.nonModelConformProperties, currentclass.type.properties[i])) {
				curli.css("color", "red");
			}

			$('#gqbClassPropertiesProperties').append(curli);
		}
	}
	if (currentclass.type.outgoingLinks.length == 0) {
		$('#gqbClassPropertiesOutgoinglinks').append('<li>'+GQB.translate("none")+'</li>');
	}
	else {
		for (var i = 0; i < currentclass.type.outgoingLinks.length; i++){
			curli =$("<li></li>").html(currentclass.type.outgoingLinks[i].getLabel() + " ("+ currentclass.type.outgoingLinks[i].getRangeLabel()+")");
			if(GQB.arrayContains(currentclass.type.nonModelConformLinks, currentclass.type.outgoingLinks[i])){
				curli.css("color","red");
			}
			$('#gqbClassPropertiesOutgoinglinks').append(curli);
		}
	}
	if (currentclass.selectedLinks.length == 0){
		$('#gqbClassPropertiesSelectedlinks').append('<li>'+GQB.translate("none")+'</li>');
	}
	else {
		var id;
		for (var i = 0; i < currentclass.selectedLinks.length; i++){
			id = this.addModelObject(currentclass.selectedLinks[i]);
			$('#gqbClassPropertiesSelectedlinks').append(
				$('<li></li>')
				.html(currentclass.selectedLinks[i].property.getLabel() + ' ('+GQB.translate("optional")+': ')
				.append($("<input class=\'gqbClassPropertiesSelectedlink\' modelId=\'"+id+"\' type=\'checkbox\' />").attr(currentclass.selectedLinks[i].optional ? "checked" : "unchecked", true))
				.append(")")
			);
		}
	}
	if (!currentclass.restrictions.hasMember()) {
		$("#gqbclassrestrictionsexist").hide();
		$("#gqbclassrestrictionsnotexist").show();
	}
	else {
		$("#gqbclassrestrictionsnotexist").hide();
		$("#gqbclassrestrictionsexist").show();
		
		if(currentclass.restrictions.mode == "AND"){
			var toplevellabel = GQB.translate("AND"); // &and;
			var secondlevellabel = GQB.translate("OR"); // &or;
		} else {
			var toplevellabel = GQB.translate("OR"); // &or;
			var secondlevellabel = GQB.translate("AND"); // &and;
		}
		$("#gqbclassrestrictionsexist").append($("<span id=\"gqbaddrestrictionbuttonand\" class=\"gqb-button gqb-button-add\" title=\""+GQB.translate("addAndRestMsg")+"\"></span>"));
		$("#gqbclassrestrictionsexist").append($("<span class=\"folder\"></span").html(toplevellabel));
		$("#gqbclassrestrictionsexist").append($("<div id=\"addrestrictionandpanel\"></div>"));

		for (var i = 0; i < currentclass.restrictions.members.length; i++) {
			var toplevelelem = $("<ul id=\"gqbclassrestrictionstoplevelmembers\"></ul>");
			var addButton = $("<span id=\"gqbaddrestrictionbuttonor"+i+"\" class=\"gqb-button gqb-button-add gqb-button-add-or\" modelId=\""+i+"\" title=\""+GQB.translate("addOrRestMsg")+"\"></span>");
			var x;
			var secondlevel;
			if(currentclass.restrictions.members[i].members.length > 1){
				x = $("<li id=\"gqbrestrictionorstructure"+i+"\" class=\"gqbclassrestrictionstoplevelmember\"><span class=\"folder\">"+secondlevellabel+"</span></li>");
				secondlevel = $("<ul class=\"gqbclassrestrictionssecondlevelmembers\"></ul>");
			}
			var orAddRestrictionPanel = $("<div id=\"addrestrictionorpanel"+i+"\"></div>");

			for (var j = 0; j < currentclass.restrictions.members[i].members.length; j++) {
				var label = currentclass.restrictions.members[i].members[j].toString();
				var deleteButton = $("<span id=\"gqbdeleterestrictionbutton"+i+"-"+j+"\" class=\"gqb-button gqb-button-delete gqb-button-delete-restriction\" toplevelIndex=\""+i+"\" secondlevelIndex=\""+j+"\" title=\""+GQB.translate("deleteRestMsg")+"\"></span>");
				var editButton = $("<span id=\"gqbeditrestrictionbutton"+i+"-"+j+"\" class=\"gqb-button gqb-button-edit gqb-button-edit-restriction\" toplevelIndex=\""+i+"\" secondlevelIndex=\""+j+"\" title=\""+GQB.translate("editRestMsg")+"\"></span>");
				var secondlevelelem = $("<li><span class=\"file gqbclassrestrictionssecondlevelmember\">"+label+"</span></li>");  
				var editRestrictionPanel = $("<div id=\"editrestrictionpanel"+i+"-"+j+"\"></div>");
				if(currentclass.restrictions.members[i].members.length > 1){
					secondlevel.append(deleteButton);
					secondlevel.append(editButton);
					secondlevel.append(secondlevelelem);
					secondlevel.append(editRestrictionPanel);
				} else {
					toplevelelem.append(addButton);
					toplevelelem.append(deleteButton);
					toplevelelem.append(editButton);
					toplevelelem.append(secondlevelelem);
					toplevelelem.append(orAddRestrictionPanel);
					toplevelelem.append(editRestrictionPanel);
				}
			}
			if(currentclass.restrictions.members[i].members.length > 1){
				x.append(orAddRestrictionPanel);
				x.append(secondlevel);
				toplevelelem.append(addButton);
				toplevelelem.append(x);
			}

			$("#gqbclassrestrictionsexist").append(toplevelelem);
			
		}
	}
	$('#gqbClassProperties').show();
};

/** 
 * This function is necessary for the expansion of "black boxes"!
 * GQBViewClasses are treated as physical objects with velocity, acceleration,
 * mass etc..  This is basically a simple (naive) physical simulation.
 * @param dt timestep in milliseconds.  Function will call itself after this interval.
 * @param friction if true, saps velocity from each object every timestep.
 */
GQBView.prototype.runPhysics = function(dt, friction) {
	if(GQB.view.runPhysSteps == 0) { GQB.view.runPhysInterval = false; return; }
	// set up a call interval for this function
	if (!GQB.view.runPhysInterval) {
		GQB.view.runPhysInterval = window.setInterval("GQB.view.runPhysics("+dt+","+friction+")", dt);
		return;
	}
	var hex = "0123456789ABCDEFF"; // used for color changing

	// our velocities are in pixels/second, so convert the timestep:
	var dtsec = dt/1000.0;

	// We process each class in each pattern individually:
	for (var i = 0; i < GQB.view.patterns.length; i++) {
		for (var j = 0; j < GQB.view.patterns[i].classes.length; j++) {
			// the GQBViewClass object, which is currently being processed:
			var curBox = GQB.view.patterns[i].classes[j];

			// we support color changing, but this is only used as 
			// part of an easter egg:
			if(curBox.dontChangeColor != true && Math.random()<0.65){
				if(curBox.rdec>0) curBox.rdec--;
				if(curBox.gdec<11) curBox.gdec++; 
				if(curBox.bdec<15)curBox.bdec++;  
			}
			if (curBox.dontChangeColor != true)
				curBox.raphBox.attr({"stroke":"#"+hex[curBox.rdec]+hex[curBox.gdec]+hex[curBox.bdec]});

			// store the position at the beginning of this timestep:
			var oldx = curBox.x;
			var oldy = curBox.y;

			// do the numerical integration (resetting acceleration to 0):
			curBox.vx += curBox.ax*dtsec;
			curBox.vy += curBox.ay*dtsec;
			curBox.ax = 0;
			curBox.ay = 0;
			curBox.x += curBox.vx*dtsec;
			curBox.y += curBox.vy*dtsec;

			// this isn't real friction, but it serves to sap velocity
			// (used for graphical slow down effect):
			if (friction == true) {
				curBox.vx /= 1.3;
				curBox.vy /= 1.3;
			}

			// we're doing collision detection next, so store some
			// frequently used values:
			var x = curBox.x;
			var y = curBox.y;
			var width = curBox.width;
			var height = curBox.height;

			// first do collision detection with the canvas edges:
			// left edge:
			if (x < 0) {
				curBox.x += 2-x;
				curBox.vx = -1 * curBox.vx;
				// boxes turn red on collision (used in easter egg):
				curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
			} 
			// right edge:
			else if (x+width > GQB.view.raphCanvasWidth) {
				curBox.x += GQB.view.raphCanvasWidth-x-width-2;
				curBox.vx = -1 * curBox.vx;
				curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
			}
			// top edge:
			if (y < 0) {
				curBox.y += 2-y;
				curBox.vy = -1 * curBox.vy;
				curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
			} 
			// bottom edge:
			else if (y+height > GQB.view.raphCanvasHeight) {
				curBox.y += GQB.view.raphCanvasHeight-y-height-2;
				curBox.vy = -1 * curBox.vy;
				curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
			}  

			// now we do collision detection with other boxes.
			// we check each possible pairing (except with ourselves):
			for (var a = 0; a < GQB.view.patterns.length; a++) {
				for (var b = 0; b < GQB.view.patterns[a].classes.length; b++) {
					if ((a == i) && (b == j)) continue;  // don't collide with ourselves
					// each collision can change our position, so get it again:
					x = curBox.x;
					y = curBox.y;

					// the box we might collide with:
					var otherBox = GQB.view.patterns[a].classes[b];
					var x2 = otherBox.x;
					var y2 = otherBox.y;
					var width2 = otherBox.width;
					var height2 = otherBox.height;

					// we check for collision from each side individually:
					// (all cases similar)
					// 1. we collide with the other box from the left:
					if(oldx+width <= x2 && x+width > x2 && y+height>y2 && y<y2+height2) {
						// first translate ourselves, so we're no longer inside the other box:
						curBox.x += x2-x-width-2;

						// now determine the new velocities (ours and the other box's)
						// based on the masses and initial velocities and assuming elastic collisions:
						var m1 = curBox.m;
						var m2 = otherBox.m;
						var m1PlusM2 = m1+m2;
						var quot = (m1 - m2)/m1PlusM2;
						var tempvx = curBox.vx;
						curBox.vx = quot*curBox.vx + 2.0*m2/m1PlusM2*otherBox.vx;
						otherBox.vx = -1.0*quot*otherBox.vx + 2.0*m1/m1PlusM2*tempvx;

						// collision causes both parties to turn red (easter egg use):
						curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
						otherBox.rdec = 15;  otherBox.gdec = 0;  otherBox.bdec = 0;
					}
					// 2. we collide with the other box from the right:
					else if(oldx >= x2+width2 && x < x2+width2 && y+height>y2 && y<y2+height2) {
						curBox.x += x2+width2-x+2;

						var m1 = curBox.m;
						var m2 = otherBox.m;
						var m1PlusM2 = m1+m2;
						var quot = (m1 - m2)/m1PlusM2;
						var tempvx = curBox.vx;
						curBox.vx = quot*curBox.vx + 2.0*m2/m1PlusM2*otherBox.vx;
						otherBox.vx = -1.0*quot*otherBox.vx + 2.0*m1/m1PlusM2*tempvx;

						curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
						otherBox.rdec = 15;  otherBox.gdec = 0;  otherBox.bdec = 0;
					}
					// 3. we collide with the other box from the top:
					if(oldy+height <= y2 && y+height > y2 && x+width>x2 && x<x2+width2) {
						curBox.y += y2-y-height-2;

						var m1 = curBox.m;
						var m2 = otherBox.m;
						var m1PlusM2 = m1+m2;
						var quot = (m1 - m2)/m1PlusM2;
						var tempvy = curBox.vy;
						curBox.vy = quot*curBox.vy + 2.0*m2/m1PlusM2*otherBox.vy;
						otherBox.vy = -1.0*quot*otherBox.vy + 2.0*m1/m1PlusM2*tempvy;

						curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
						otherBox.rdec = 15;  otherBox.gdec = 0;  otherBox.bdec = 0;
					}
					// 4. we collide with the other box from the bottom:
					else if(oldy >= y2+height2 && y < y2+height2 && x+width>x2 && x<x2+width2) {
						curBox.y += y2+height2-y+2;

						var m1 = curBox.m;
						var m2 = otherBox.m;
						var m1PlusM2 = m1+m2;
						var quot = (m1 - m2)/m1PlusM2;
						var tempvy = curBox.vy;
						curBox.vy = quot*curBox.vy + 2.0*m2/m1PlusM2*otherBox.vy;
						otherBox.vy = -1.0*quot*otherBox.vy + 2.0*m1/m1PlusM2*tempvy;

						curBox.rdec = 15;  curBox.gdec = 0;  curBox.bdec = 0;
						otherBox.rdec = 15;  otherBox.gdec = 0;  otherBox.bdec = 0;
					}
				}
			}
			// now that we've done all the collision detection, we will no longer
			// modify the box's position in this time step.  Thus we can perform the
			// on screen translation here (note that we can't use GQBViewClass.translate(),
			// since we have already modified the parameters x and y.  All that remains
			// is to translate the Raphael objects):
			var dxpix = Math.round(curBox.x) - curBox.raphBox.attr("x");
			var dypix = Math.round(curBox.y) - curBox.raphBox.attr("y");
			curBox.raphObjSet.translate(dxpix,dypix);
		}
	}

	// All classes have now been integrated for this timestep, but
	// we still need to determine the accelerations (forces) for use
	// in the next time step.
	// Connections act as springs, so here a spring constant (value
	// heuristically determined to be graphically "nice"):
	var k = 550.0;
	// Process all connections (springs):
	for (var i = 0; i < GQB.view.patterns.length; i++) {
		for (var j = 0; j < GQB.view.patterns[i].connections.length; j++) {
			var curCon = GQB.view.patterns[i].connections[j];
			// we take this opportunity to update the connection graphics (which we
			// must do anyway, since some boxes have changed their positions).
			// Black boxed patterns display no visible connections, so we must skip them:
			if (!GQB.view.patterns[i].isBlackBoxed) {
				GQB.view.raphaelCanvas.connectionWithArrowAndLabel(curCon.raphConnection); // update the connection
			}
			// some connections should not be treated as springs.  These we skip:
			if (curCon.dontCalcAsSpring == true) continue;

			// Each connection has a start and an end point, to which
			// forces will be applied:
			var curBox = curCon.startViewClass;
			var otherBox = curCon.endViewClass;
			var x = curBox.x;
			var y = curBox.y;   
			var width = curBox.width;
			var height = curBox.height;
			var width2 = otherBox.width;
			var height2 = otherBox.height;
			var m = curBox.m;
			var m2 = otherBox.m;

			// determine the separation of the center points of the
			// two boxes:
			var xsep = x + width/2 - otherBox.x - width2/2;
			var ysep = y + height/2 - otherBox.y - height2/2;
			var sep =  Math.sqrt(xsep*xsep + ysep*ysep);

			// calculate how far the string is stretched or compressed:
			var dsep = sep - curCon.neutralSpringLength;

			// Hooke's law:
			var dFx = dsep * Math.abs(dsep) * k * Math.abs(xsep/sep);
			var dFy = dsep * Math.abs(dsep) * k * Math.abs(ysep/sep);

			// modify the accelerations of the start and end boxes
			// according to their relative position:

			if (curBox.x > otherBox.x) {
				curBox.ax -= dFx/m;
				otherBox.ax += dFx/m2;
			} else {
				curBox.ax += dFx/m;
				otherBox.ax -= dFx/m2;
			}
			if (curBox.y > otherBox.y) {
				curBox.ay -= dFy/m;
				otherBox.ay += dFy/m2;
			} else {
				curBox.ay += dFy/m;
				otherBox.ay -= dFy/m2;
			}

			// due to some error (possibly due to collisions), there
			// seems to be a slight energy increase from each connection.
			// we try to counteract that here by sapping some velocity:
			curBox.vx /= 1.00020;
			curBox.vy /= 1.00020;
			otherBox.vx /= 1.00020;
			otherBox.vy /= 1.00020; 
		}
	}

	// If there are steps remaining in the simulation, we set a new call
	// to runPhysics() after the given time step dt has elapsed:
	// (runPhysSteps = -1 means that the simulation will never end)
	if (GQB.view.runPhysSteps > 0)
		GQB.view.runPhysSteps--;
	if(GQB.view.runPhysSteps > 0 || GQB.view.runPhysSteps == -1)
		;//setTimeout("GQB.view.runPhysics("+dt+","+friction+")", dt);
	else {
		window.clearInterval(GQB.view.runPhysInterval);
		GQB.view.runPhysInterval = false;
		// reset all masses at end of simulation:
		for (var i = 0; i < GQB.view.patterns.length; i++) {
			for (var j = 0; j < GQB.view.patterns[i].classes.length; j++) {
				GQB.view.patterns[i].classes[j].m = GQB.view.patterns[i].classes[j].x * GQB.view.patterns[i].classes[j].y;
			}
		}
	}
};
