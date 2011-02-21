/**
 * Represents a queryPattern graphically as a set of
 * GQBViewClasses and GQBViewConnections.
 * @param _id The id of the pattern in the model, to which this view pattern belongs. Must be valid!
 * @class
 */
function GQBViewPattern (_id){
	this.id = _id;
	this.modelPattern = GQB.model.findPatternById(this.id);
	if (!this.modelPattern) { alert(GQB.translate("fatalErrorCreatingViewPatMsg")); return null; }

	// We store arrays of GQBViewClass and GQBViewConnection objects, which
	// compose this GQBViewPattern:
	this.classes = new Array();
	this.connections = new Array();

	// a pattern can be "black boxed", that is collapsed so that
	// only its start class is visible (and greyed out):
	this.isBlackBoxed = false;
	// the single visible class when this pattern is black boxed:
	this.blackBoxClass = false;

	// methods
	// for switching "black box mode" on or off:
	this.toBlackBox;
	this.fromBlackBox;
	this.toggleBlackBox;

	this.setBlackBoxClass;

	this.findViewClassById;

	this.deleteClass;
	this.deletePattern;
}

/**
 * Toggles the black box mode of this view pattern.
 */
GQBViewPattern.prototype.toggleBlackBox = function() {
	if (this.isBlackBoxed) this.fromBlackBox();
	else this.toBlackBox();
};

/**
 * Turns this pattern into a "black box", where only the start class is
 * visible (and greyed out), and where all other classes and connections are hidden.
 */
GQBViewPattern.prototype.toBlackBox = function() {
	if (this.isBlackBoxed) return;
	if (this.classes.length <= 1) return; // don't black box with a single class

	// first hide all raphael objects (classes, connections, labels, buttons, etc):
	for (var i = 0; i < this.classes.length; i++) {
		this.classes[i].hide(); 
	}
	for (var i = 0; i < this.connections.length; i++) {
		this.connections[i].hide();
	}

	// then only show one single class (the "black box"):
	this.blackBoxClass.show();
	// set that class to be "greyed out":
	this.blackBoxClass.raphBox.attr({fill: GQB.view.blackbox_color, stroke: GQB.view.blackbox_color}); 
	this.isBlackBoxed = true;
	//GQB.view.refreshCanvas();
};

/**
 * Expands this view pattern from "black box mode" so that all classes
 * and connections are again visible.
 */
GQBViewPattern.prototype.fromBlackBox = function() {
	if (!this.isBlackBoxed) return;

	// When un-"blackboxing" a pattern, we want the classes to push each other apart
	// so that a relatively uncluttered workspace results.  To do this we run a
	// simple phyiscal simulation, treating all our boxes as massive bodies,
	// and connections as springs attached to them (s. GQBView.runPhysics()).
	// Here we simply set some initial values for position, velocity, acceleration and mass
	// for all GQBViewClasses in this pattern:
	for (var i = 0; i < this.classes.length; i++) {
		if (this.classes[i].modelClass.id == this.blackBoxClass.id) continue;
		// first show the objects which were previously hidden:
		this.classes[i].show(); 

		// we want the pattern to expand from the point where the black box is currently located,
		// so we move all classes to roughly that position (with some random variation):
		this.classes[i].translate(this.blackBoxClass.x - this.classes[i].x + 70*Math.random() - 25, 
																		 this.blackBoxClass.y - this.classes[i].y + 70*Math.random() - 25);
 
		// we set random initial velocites and no acceleration
		this.classes[i].vx = 100*Math.random() - 50;
		this.classes[i].vy = 100*Math.random() - 50;
		this.classes[i].ax = 0;
		this.classes[i].ay = 0;

		// the objects have mass according to their area (constant density):
		this.classes[i].m = this.classes[i].width*this.classes[i].height;

		// GQBView.runPhysics() allows for the changing of colors as a graphical effect,
		// but we don't want that here:
		if (GQB.view.runPhysSteps >= 0) {
			this.classes[i].dontChangeColor = true;
		}
	}

	// each connection should be treated as a spring, so here we set the
	// "neutral" spring distance (at which it applies no force) as 170 pixels
	// (since the classes start close together, they will be pushed apart by
	// the springs).
	for (var i = 0; i < this.connections.length; i++) {
		// first show the previously hidden connections:
		this.connections[i].show();
		GQB.view.raphaelCanvas.connectionWithArrowAndLabel(this.connections[i].raphConnection);
		// GQB.view.runPhysSteps is -1 when the simulation is running continuously
		// (which is used as an easter egg), in which case we don't want to disturb
		// it.  Otherwise we can set the connection properties.
		if (GQB.view.runPhysSteps >= 0) {
			this.connections[i].neutralSpringLength = 150.0;
			this.connections[i].dontCalcAsSpring = false;
		}
	}

	// if no simulation is currently running, we must make sure that
	// we don't disturb other objects:
	if (GQB.view.runPhysSteps == 0) {
		// turn trash gravity off:
		GQB.view.trash.dontCalcGrav = true;
		// turn off spring behavior for all connections on the canvas except
		// for those in this pattern:
		for (var i = 0; i < GQB.view.patterns.length; i++) {
			if (GQB.view.patterns[i].id == this.id) continue;
			for (var j = 0; j < GQB.view.patterns[i].connections.length; j++) {
				GQB.view.patterns[i].connections[j].dontCalcAsSpring = true;
			}
		}
	}

	// since we're returning from "black box" status, we want to set our black box class
	// back to normal color (was greyed out):
	this.blackBoxClass.raphBox.attr({fill: GQB.view.box_color, stroke: GQB.view.box_color}); 

	this.isBlackBoxed = false;

	// now we're ready to start the simulation.
	// If no other simulation is running yet:
	if (GQB.view.runPhysSteps == 0) {
		// start one and run it for 30 steps = 1500msec
		this.blackBoxClass.m = 10000000;  // large mass so black box class doesn't move
		GQB.view.runPhysSteps = 30;
		GQB.view.runPhysics(50, true);  // (step interval is 40ms)
	}
	// if another simulation is already running, but only temporarily: 
	else if (GQB.view.runPhysSteps > 0) {
		// we set the simulation duration to 30 steps total:
		this.blackBoxClass.m = 10000000;  // large mass so black box class doesn't move
		GQB.view.runPhysSteps = 30;
	}
	// if a continuous simulation is running (easter egg):
	else {
		// running non-temporarily, don't change num steps...
		;
	}
	// no need to refresh the canvas, since runPhysics() does this
};

/**
 * Sets which GQBViewClass of this pattern will display the "black box
 * button", which turns this pattern into a "black box".  The passed
 * GQBViewClass must be contained in this pattern, in order for the
 * button to be added.  An invalid value for "newBBClass" will clear
 * the button from all classes of this pattern.
 * @param newBBClass A GQBViewClass belonging to this pattern to set,
 *                      or anything else to reset the black box button.
 */
GQBViewPattern.prototype.setBlackBoxClass = function(newBBClass) {
	if (!newBBClass || (!newBBClass.id && newBBClass.id != 0) 
			|| !this.findViewClassById(newBBClass.id)) 
	newBBClass = false;

	if (this.blackBoxClass) this.blackBoxClass.removeBlackBoxIcon();
	this.blackBoxClass = newBBClass;
	if (this.blackBoxClass) this.blackBoxClass.addBlackBoxIcon();
};

/**
 * Returns the GQBViewClass in this pattern with the passed id, or null if not found.
 * @param id The id of the GQBViewClass to search for.
 * @return The GQBViewClass with id (if found) or null.
 */
GQBViewPattern.prototype.findViewClassById = function(id) {
	for (var i = 0; i < this.classes.length; i++) {
		if (this.classes[i].id == id)
			return this.classes[i];
	}
	return null;
};

/**
 * Adds a GQBViewClass object to this GQBViewPattern.  The class can be added as the first class
 * (start class) or as a class connected to an already existing class (must be in this pattern).
 * Optionally a canvas position for the new GQBViewClass can be given.
 * @param classToAdd The GQBClass (model class) which will be mapped to the new GQBViewClass
 * @param existingParentClass (optional) The GQBClass which is the parent class of the first parameter ("classToAdd") in the model.
 * @param x (optional) The x coordinate of the new class relative to the Raphael canvas
 * @param y (optional) The y coordinate of the new class relative to the Raphael canvas
 * @return The newly created and added GQBViewClass or null if an error occurs.
 */
GQBViewPattern.prototype.addClassToPatternAtPos = function (classToAdd, existingParentClass, x, y) {
	if (!this.modelPattern) return null;

	// first create the new GQBViewClass and initialize it:
	var newViewClass = new GQBViewClass(classToAdd.id, this);
	newViewClass.initAtPos(x, y, classToAdd.type.getLabel());

	// if the new class is the start class of the pattern (in the model),
	// then distinguish it through italic print:
	if (this.modelPattern.startClass.id == classToAdd.id) {
		newViewClass.raphLabel.attr("font-style","italic");
	}

	// add the new class to this pattern's list of classes  
	this.classes.push(newViewClass);

	// if we just added the first class to the pattern, add a black box button:
	if (this.classes.length == 1) {
		newViewClass.addBlackBoxIcon();
		this.blackBoxClass = newViewClass;
	}

	// if the new class was added as an expansion of an existing pattern, we
	// must add the corresponding connection (s. GQBViewConnection):
	if (existingParentClass) {
		var existingParentViewClass = null;
		// find which GQBViewClass in this pattern corresponds to the passed GQBClass (existingParentClass):
		for (var i = 0; i < this.classes.length; i++) {
			if (this.classes[i].id == existingParentClass.id) {
				existingParentViewClass = this.classes[i];
				break;
			}
		}

		// if no parent GQBViewClass was found, there is some (probably major) problem...
		// otherwise we can now add the connection:
		if (existingParentViewClass) {
			// Find which link in the model corresponds to the view link which we are creating:
			var modelLink;
			for (var i = 0; i < existingParentClass.selectedLinks.length; i++) {
				if (existingParentClass.selectedLinks[i].target.id == classToAdd.id) {
					modelLink = existingParentClass.selectedLinks[i];
					break;
				}
			}
			// create the actual GQBViewConnection object and push it onto our connections array:
			this.connections.push(new GQBViewConnection(existingParentViewClass, newViewClass, modelLink));
		}
	}

	return newViewClass;
};

/** 
  * Deletes all classes and connections from this GQBViewPattern, 
  * but does not make any changes in the model.
  */
GQBViewPattern.prototype.deletePattern = function() {
	for (var i = 0; i < this.classes.length; i++) {
		this.classes[i].raphObjSet.remove();
	}

	for (var i = 0; i < this.connections.length; i++) {
		this.connections[i].remove();
	}

	// clear our arrays:
	this.classes.length = 0;
	this.connections.length = 0;
};

/** Deletes the passed GQBViewClass from this GQBViewPattern along with
 *  all child classes and all corresponding connections.  Does not make
 *  any changes in the model.  Returns the GQBClass (model class) which
 *  corresponds to the parent of the deleted viewClass (this class is not
 *  deleted).
 *@param viewClass The GQBViewClass to delete from this pattern.
 *@return The parent of viewClass as a GQBClass or null.
 */
GQBViewPattern.prototype.deleteClass = function(viewClassToDelete) {
	if (!viewClassToDelete || !this.findViewClassById(viewClassToDelete.id)) return null;
	
	// we may need to remove multiple classes and connections:
	var viewClassesToRemove = new Array();
	var connectionsToRemove = new Array();   

	// first class to remove is the passed class:
	viewClassesToRemove.push(viewClassToDelete);

	// find parent model class of the class to be removed by
	// looking through all connections until the one which
	// leads to the passed class is found (at most one exists).
	// This is returned at the end.
	var theParentClass = null;
	for (var i = 0; i < this.connections.length; i++) {
		if (this.connections[i].endViewClass.id == viewClassToDelete.id) {
			connectionsToRemove.push(this.connections[i]);
			theParentClass = this.connections[i].startViewClass.modelClass;
			break;  
		}
	}
	
	// find which classes and connections should be removed:
	// (loop through all connections adding the end classes of those to 
	//  be deleted to an array)
	do {
		var changed = false;
		for (var i = 0; i < this.connections.length; i++) {
			var curStartId = this.connections[i].startViewClass.id;
			var idMatchesClassToRemove = false;  // will the start class of this connection be removed?
			for (var j = 0; j < viewClassesToRemove.length; j++) {
				if (viewClassesToRemove[j].id == curStartId) 
					idMatchesClassToRemove = true;
			}  
			// we need to remove the end class as well,
			// but only if we haven't seen it yet:
			if (idMatchesClassToRemove) {
				var endClassAlreadyFound = false;  
				for (var j = 0; j < viewClassesToRemove.length; j++) {
					if (viewClassesToRemove[j].id == this.connections[i].endViewClass.id) {
						endClassAlreadyFound = true;
						break;
					}
				} 
				if (!endClassAlreadyFound) {
					viewClassesToRemove.push(this.connections[i].endViewClass);
					connectionsToRemove.push(this.connections[i]);
					changed = true;
				}
			}
		}
	} while (changed); // loop until nothing new was added to the arrays

	// remove the found classes and connections:
	var removedBlackBoxClass = false;
	for (var i = 0; i < viewClassesToRemove.length; i++) {
		// watch for the black box class, which we'll remove seperately at the end:
		if (this.blackBoxClass && viewClassesToRemove[i].id == this.blackBoxClass.id) {
			removedBlackBoxClass = true;
			continue;
		}
		viewClassesToRemove[i].raphObjSet.remove();
		GQB.arrayRemoveObj(this.classes, viewClassesToRemove[i]);
	}
	for (var i = 0; i < connectionsToRemove.length; i++) {
		connectionsToRemove[i].remove();
		GQB.arrayRemoveObj(this.connections, connectionsToRemove[i]);
	}
	// handle removing the black box class:
	if (removedBlackBoxClass) {
		var oldBlackBoxClass = this.blackBoxClass;
		// if it's the only class left:
		if (this.classes.length <= 1) {
			; // do nothing
		} 
		// otherwise set a new black box class before removing the old one:
		else {
			this.setBlackBoxClass(this.classes[this.classes[0].id != this.blackBoxClass.id ? 0 : 1]);
		}
		// remove the old black box class:
		oldBlackBoxClass.raphObjSet.remove();
		GQB.arrayRemoveObj(this.classes, oldBlackBoxClass);
	}

	return theParentClass;
};

/**
 * A GQBViewClass maps to a single GQBClass in the model.  It consists of several Raphael objects
 * (box, label, buttons) and possesses physical characteristics such as velocity and acceleration.
 * Each GQBViewClass must be the child of a single GQBViewPattern.
 * @param _id The id of the GQBClass in the model which corresponds to this GQBViewClass.  Must be valid!
 * @param _parentViewPattern The GQBViewPattern which is the parent of this GQBViewClass.  Must be valid!
 * @class
 */
function GQBViewClass (_id, _parentViewPattern) {
	this.id = _id;
	this.modelClass = GQB.model.findClassInPatternsById(this.id);
	if (!this.modelClass) { alert(GQB.translate("fatalErrorCreatingViewClassMsg1")); return null; }

	if (!_parentViewPattern || (!_parentViewPattern.id && _parentViewPattern.id != 0) || !GQB.view.findPatternById(_parentViewPattern.id)) {
		alert(GQB.translate("fatalErrorCreatingViewClassMsg2"));
		return null;
	}
	this.parentViewPattern = _parentViewPattern;

	// graphical and physical parameters
	this.x = 0.0;				// x position (left side) in pixels from top left of canvas
	this.y = 0.0;				// y position (top side) in pixels from top left of canvas
	this.vx = 0.0;			// x velocity in pixels/second (+ right, - left)
	this.vy = 0.0;			// y velocity in pixels/second (+ down, - up)
	this.ax = 0.0;			// x acceleration in pix/sec/sec 
	this.ay = 0.0;			// y acceleration in pix/sec/sec
	this.width = 1;			// width in pixels of box
	this.height = 1;		// height in pixels of box
	this.m = 1.0;				// mass (= width*height at constant density)

	// used for color changing, decimal values of R,G,B (0-15):
	this.rdec = 0;
	this.gdec = 11;
	this.bdec = 15;
	// whether or not this object will change color during a physical simulation:
	this.dontChangeColor = (GQB.view.runPhysSteps != -1);  

	// whether or not this GQBViewClass is currently in "wait" mode
	// (i.e. displaying a spinner icon)
	this.isWaiting = false;

	// raphael objects
	this.raphBox;
	this.raphLabel;
	this.raphTrashIcon;
	this.raphBlackBoxIcon;
	this.raphSpinngerIcon;
	this.raphObjSet;				//keeps track of multiple graphical objects

	// methods
	this.initAtPos;
	this.translate;
	this.setLabel;

	// for adding and removing a single black box button
	// from this class:
	this.addBlackBoxIcon;
	this.removeBlackBoxIcon;

	// change colors based on select state:
	this.unselect;
	this.select;

	// display or hide a spinner icon in my box:
	this.wait;
	this.unwait;

	// show or hide the entire class
	this.show;
	this.hide;
}

/**
 * Initializes this GQBViewClass.  The class will be displayed in such a way,
 * that its center point will be located at the relative Raphael canvas position
 * of (_x,_y).  Its label will be the passed string.  This function also sets up
 * all necessary mouse listeners and handlers.
 * @param _x The relative canvas x position of this GQBViewClass's center point.
 * @param _y The relative canvas y position of this GQBViewClass's center point.
 * @param label The label of this GQBViewClass.
 * @return The initialized GQBViewClass.
 */
GQBViewClass.prototype.initAtPos = function(_x, _y, label) {
	if (!_x) _x = 0.0;
	if (!_y) _y = 0.0;
	if (!label) label = " ";

	// Box width is proportional to label length:
	this.width = label.length*8+4;
	this.height = 34;

	// The passed position (_x,_y) should be the center
	// point of this box, so subtract width/2, height/2.
	// Use random initial velocity.
	// Mass is proportional to area.
	this.x = _x - this.width/2;
	this.y = _y - this.height/2;
	this.vx = 300.0*Math.random() - 150.0;
	this.vy = 300.0*Math.random() - 150.0;
	this.m = this.width * this.height;

	// create Raphael objects (Box, label, trash icon)
	this.raphLabel = GQB.view.raphaelCanvas.text(this.x+(label.length*4)+2, this.y+16, label); 
	this.raphBox = GQB.view.raphaelCanvas.rect(this.x, this.y, this.width, this.height, 10); 
	this.raphTrashIcon = GQB.view.raphaelCanvas.image(urlBase+"extensions/components/graphicalquerybuilder/resources/images/canvas-btn-close.png", this.x-1, this.y-1, 13, 13);
	this.raphTrashIcon.node.setAttribute("class", "trashicon");
	this.raphBox.attr({fill: GQB.view.box_color, stroke: GQB.view.box_color, "fill-opacity": 0, "stroke-width": 2});
	this.raphBox.node.style.cursor = "move";
	this.raphSpinnerIcon = GQB.view.raphaelCanvas.image(urlBase+"extensions/components/graphicalquerybuilder/resources/images/spinner.gif", this.x+this.width/2-8, this.y+this.height/2-8, 16, 16);
	this.raphSpinnerIcon.hide();

	// store a reference to the GQBViewClass in the raphBox and raphTrashIcon for use in the mouse handlers:
	this.raphBox.parentGQBViewClass = this;
	this.raphTrashIcon.parentGQBViewClass = this;

	// set mousedown handler for the class box:
	this.raphBox.mousedown(function(e) { 
		// the GQBViewClass which corresponds to the moused down box:
		var parentVClass = this.parentGQBViewClass;
		// store mouse position for use in dragging:
		parentVClass.dx = e.clientX;
		parentVClass.dy = e.clientY;

		// mouse down on a box causes it to stop moving:
		parentVClass.vx = 0.0;
		parentVClass.vy = 0.0;

		// mouse down on a box means the parent class is being dragged:
		GQB.view.draggedViewClass = parentVClass;

		// make the box look darker when moused down on:
		this.animate({"fill-opacity": .2}, 500);

		// in order to capture mouse over events on other objects on the canvas, we send the
		// dragged object to the back (all parts of it):
		parentVClass.raphObjSet.toBack();

		// mousing down on a box selects the corresponding class in the view, so we have
		// to change some box colors.
		// first, if a box was previously selected, change its color back to normal:
		if (GQB.view.selectedViewClass) {
			GQB.view.selectedViewClass.raphBox.attr({fill: GQB.view.box_color, stroke: GQB.view.box_color});
			// in case the selected box was black boxed, it must be turned grey:
			if (GQB.view.selectedViewClass.parentViewPattern && GQB.view.selectedViewClass.parentViewPattern.isBlackBoxed) {
				GQB.view.selectedViewClass.raphBox.attr({fill: GQB.view.blackbox_color, stroke: GQB.view.blackbox_color});  
			} 
		}
		// then set my box color to the selection color:
		this.attr({fill: GQB.view.sel_color, stroke: GQB.view.sel_color});

		// now we are the selected class, which also means showing our properties in the "east" pane:
		GQB.view.selectedViewClass = parentVClass;
		GQB.view.showClassProperties(GQB.view.selectedViewClass.modelClass);
	});

	// the mouseover handler changes the box color and keeps track of the moused-over object:
	this.raphBox.mouseover(function(){ 
		GQB.view.mousedOverViewClass = this.parentGQBViewClass;
		this.attr({fill: GQB.view.mouseover_color, stroke: GQB.view.mouseover_color}); 
	});

	// the mouseout handler reverses the mouseover handler by changing the color back to normal,
	// and clearing the current moused over object:
	this.raphBox.mouseout(function(){ 
		GQB.view.mousedOverViewClass = false; 
		// change our color depending on whether we are selected, black boxed or normal:
		if (GQB.view.selectedViewClass.id == this.parentGQBViewClass.id) {
			this.attr({fill: GQB.view.sel_color, stroke: GQB.view.sel_color}); 
		} else if (this.parentGQBViewClass.parentViewPattern && this.parentGQBViewClass.parentViewPattern.isBlackBoxed) {
			this.attr({fill: GQB.view.blackbox_color, stroke: GQB.view.blackbox_color});  
		} else {
			this.attr({fill: GQB.view.box_color, stroke: GQB.view.box_color}); 
		} 
	});

	// mouse down on the trash icon: ask user if this class should be deleted:
	this.raphTrashIcon.mousedown(function(){
		if (!this.parentGQBViewClass.modelClass.isReady()) {
			alert(GQB.translate("cantDeleteClassUntilLoadedMsg"));
			return;
		}
		if (confirm(GQB.translate("confirmDelClassMsg"))) {
			GQB.view.deleteClass(this.parentGQBViewClass);
		}
	});

	// keep track of all of the graphical elements of this GQBClass by
	// using a Raphael "set" object:
	this.raphObjSet = GQB.view.raphaelCanvas.set();
	this.raphObjSet.push(this.raphBox);
	this.raphObjSet.push(this.raphLabel);
	this.raphObjSet.push(this.raphTrashIcon);
	this.raphObjSet.push(this.raphSpinnerIcon);

	return this;  // return refernce to the newly initialized GQBViewClass
};

/**
 * Translates this view class by the given distances (in pixels, relative to previous position).
 * @param dx The (positive or negative) distance to translate in x direction.
 * @param dy The (positive or negative) distance to translate in y direction.
 */
GQBViewClass.prototype.translate = function(dx,dy) {
	if(!dx) dx = 0;
	if(!dy) dy = 0;
	this.raphObjSet.translate(dx, dy);
	this.x += dx;
	this.y += dy;
};

/**
 * Sets the label of this view class.
 * If this view class corresponds to the start class of
 * its model pattern, the label is italicized.
 * @param newLabel The new label to set.
 */
GQBViewClass.prototype.setLabel = function(newLabel) {
	if(!newLabel) newLabel = " ";

	if (this.raphLabel) this.raphLabel.remove();

	this.raphLabel = GQB.view.raphaelCanvas.text(this.x+(newLabel.length*4)+2, this.y+16, newLabel); 
	if (this.parentViewPattern.modelPattern.startClass.id == this.modelClass.id)
		this.raphLabel.attr("font-style","italic");

	this.width = newLabel.length*8+4;
	this.raphBox.attr("width", this.width);

	this.raphObjSet = GQB.view.raphaelCanvas.set();
	this.raphObjSet.push(this.raphBox);
	this.raphObjSet.push(this.raphTrashIcon);
	this.raphObjSet.push(this.raphLabel);
	this.raphObjSet.push(this.raphSpinnerIcon);
	if (this.raphBlackBoxIcon) {
		this.raphBlackBoxIcon.attr("x", this.x+this.width-15);
		this.raphObjSet.push(this.raphBlackBoxIcon);
	}
	this.raphSpinnerIcon.attr("x", this.x+this.width/2-8);

	// hide the newly changed label if we're black boxed and not the start class:
	if(this.parentViewPattern.isBlackBoxed && this.parentViewPattern.blackBoxClass.modelClass.id != this.modelClass.id) 
		this.raphLabel.hide();
};

/**
 * Adds a black box button to this GQBViewClass, if it doesn't already have one.
 * The button is positioned in the top right corner and will activate the parent
 * pattern's toggleBlackBox() method.
 */
GQBViewClass.prototype.addBlackBoxIcon = function() {
	if (this.raphBlackBoxIcon) return; // already have one

	this.raphBlackBoxIcon = GQB.view.raphaelCanvas.image(urlBase+"extensions/components/graphicalquerybuilder/resources/images/canvas-btn-open.png", this.x+this.width-15, this.y-1, 13, 13);
	this.raphBlackBoxIcon.parentViewPattern = this.parentViewPattern;
	this.raphBlackBoxIcon.mousedown(function(){ this.parentViewPattern.toggleBlackBox(); });
	this.raphObjSet.push(this.raphBlackBoxIcon);
};

/**
 * Completely removes a black box button from this GQBViewClass if
 * one exists.
 */
GQBViewClass.prototype.removeBlackBoxIcon = function() {
	if (!this.raphBlackBoxIcon) return; // no icon to remove

	this.raphBlackBoxIcon.remove();
	this.raphBlackBoxIcon.mousedown(function(){}); // clear mousedown
	this.raphBlackBoxIcon = false;

	// remove from set by creating new set without black box icon:
	this.raphObjSet = GQB.view.raphaelCanvas.set();
	this.raphObjSet.push(this.raphBox);
	this.raphObjSet.push(this.raphTrashIcon);
	this.raphObjSet.push(this.raphLabel);
	this.raphObjSet.push(this.raphSpinnerIcon);
};

/**
 * Selects this GQBViewClass by changing its color.
 */
GQBViewClass.prototype.select = function() {
	this.raphBox.attr({fill: GQB.view.sel_color, stroke: GQB.view.sel_color});  
};

/**
 * Deselects this GQBViewClass by changing its color.
 */
GQBViewClass.prototype.unselect = function() {
	if (this.parentViewPattern.isBlackBoxed) {
		this.raphBox.attr({fill: GQB.view.blackbox_color, stroke: GQB.view.blackbox_color});  
	} else {
		this.raphBox.attr({fill: GQB.view.box_color, stroke: GQB.view.box_color}); 
	} 
};

/**
 * Displays a spinner icon over this class's box,
 * if no such icon is already showing.
 */
GQBViewClass.prototype.wait = function() {
	if (this.isWaiting) return;
	this.raphSpinnerIcon.show();
	this.isWaiting = true;
};

/**
 * Hides any currently showing spinner icon.
 */
GQBViewClass.prototype.unwait = function() {
	if (!this.isWaiting) return;
	this.raphSpinnerIcon.hide();
	this.isWaiting = false;
};

/**
 * Makes all graphical components of this class visible.
 * Only makes the spinner visible if the class is waiting.
 */
GQBViewClass.prototype.show = function() {
	this.raphObjSet.show();
	if (!this.isWaiting)
		this.raphSpinnerIcon.hide();
};

/**
 * Makes all graphical components of this class invisible.
 */
GQBViewClass.prototype.hide = function() {
	this.raphObjSet.hide();
};

/**
 * Represents a connection (i.e. line with arrow) between two GQBViewClasses graphically.
 * The passed label will be displayed in the middle of the connection.  Set the color
 * of new connections by modifying the GQB.view.connection_color variable.
 * @param _startViewClass The GQBViewClass from which this connection will go out.
 * @param _endViewClass The GQBViewClass to which the arrow points.
 * @param _modelLink The GQBSelectedLink which corresponds to this GQBViewConnection.
 * @param _label The label of this connection as a string.
 * @class
 */
function GQBViewConnection(_startViewClass, _endViewClass, _modelLink, _label) {
	if (!_startViewClass || !_startViewClass.raphBox || !_endViewClass || !_endViewClass.raphBox) {
		alert (GQB.translate("fatalErrorCreatingViewConMsg"));
		return undefined;
	}

	this.startViewClass = _startViewClass;
	this.endViewClass = _endViewClass;
	this.modelLink = _modelLink;
	if (!this.modelLink) { alert(GQB.translate("fatalErrorCreatingViewConMsg")); return undefined; }
	this.label = _label;
	if (!this.label) this.label = this.modelLink.property.getLabel();

	// Connections are treated as springs in the physical simulation (for graphical effect).
	// The following parameter indicates the neutral ("slack") length,
	// at which no force is generated.
	this.neutralSpringLength = 1.0;
	// Set the following parameter to true to avoid treating this
	// connection as a spring.
	this.dontCalcAsSpring = false;

	// The actual Raphael connection object:
	this.raphConnection = GQB.view.raphaelCanvas.connectionWithArrowAndLabel(this.startViewClass.raphBox, 
																						this.endViewClass.raphBox, GQB.view.connection_color, null, this.label);

	// methods
	this.setLabel;
	this.hide;
	this.show;
	this.remove;
}

/**
 * Sets the label of this GQBViewConnection.
 * @param _label The label of this connection as a string.
 *        If this is undefined, the label of the model link will be used.
 */
GQBViewConnection.prototype.setLabel = function(_label) {
	if(!this.raphConnection) return;
	this.label = _label;
	if (!this.label) this.label = this.modelLink.property.getLabel();

	this.raphConnection.to.label.remove();
	this.raphConnection.to.label = GQB.view.raphaelCanvas.text(this.raphConnection.to.label.attr("x"),
																						this.raphConnection.to.label.attr("y"), this.label);
	this.raphConnection.to.label.attr({"font-size": 14, fill: GQB.view.conlabel_color});

	if(this.startViewClass.parentViewPattern.isBlackBoxed)
		this.raphConnection.to.label.hide();
};

/**
 * Hides all graphical components of this connection (line, arrow and label).
 */
GQBViewConnection.prototype.hide = function() {
	if (!this.raphConnection) return;
	this.raphConnection.to.arrow.hide();
	this.raphConnection.to.label.hide();
	this.raphConnection.line.hide();
};

/**
 * Shows all graphical components of this connection (line, arrow and label).
 */
GQBViewConnection.prototype.show = function() {
	if (!this.raphConnection) return;
	this.raphConnection.to.arrow.show();
	this.raphConnection.to.label.show();
	this.raphConnection.line.show();
};

/**
 * Removes all graphical components of this connection (line, arrow and label).
 */
GQBViewConnection.prototype.remove = function() {
	if (!this.raphConnection) return;
	this.raphConnection.to.arrow.remove();
	this.raphConnection.to.label.remove();
	this.raphConnection.line.remove();
};
