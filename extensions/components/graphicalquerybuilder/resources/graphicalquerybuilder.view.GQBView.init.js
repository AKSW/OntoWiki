/**
 * Initializes the JQuery layout, the Raphael canvas and trash, and all
 * mouse motion, mouse drag, mouse click and change handlers.
 * Should always be called immediatly after creating the view.
 */
GQBView.prototype.init = function (){
	this.showInfo(GQB.translate("tip1"));
	this.initLayout();
	this.initControlLabels();
	this.initCanvas();
	this.initMouseMoveAndDropHandlers();
	this.initMouseClickHandlers();
};

/**
 * Initializes the JQuery layout.
 */
GQBView.prototype.initLayout = function() {
	//setup the tree of saved queries and basic classses 
	//$("#gqbsavedqueriestree").treeview();
	//setup the tree of restrictions
	$('#gqbClassPropertiesRestrictions').treeview();
        window.onbeforeunload = function (evt) {
          var message = 'Unsaved queries will be lost.';
          if (typeof evt == 'undefined') {
            evt = window.event;
          }
          if (evt) {
            evt.returnValue = message;
          }
          return message;
        }

};

GQBView.prototype.initControlLabels = function() {
	// top button row
	$("#gqbdisplayresultsbutton").html(GQB.translate("dispResultsBtnLbl"));
	$("#gqbshowquerybutton").html(GQB.translate("showQueryBtnLbl"));
	$("#gqbSavePatternButton").html(GQB.translate("savePatternBtnLbl"));

	// results panel
	$("#gqbresulttitle").html(GQB.translate("resultsPanelLbl"));
	$("#gqbresultlabel").html(GQB.translate("resultsPanelLbl"));
	$("#gqbprevresultspagebuttonlabel").html(GQB.translate("prevPageBtnLbl"));
	$("#gqbnextresultspagebuttonlabel").html(GQB.translate("nextPageBtnLbl"));
	$("#gqbprintbuttonlabel").html(GQB.translate("printBtnLbl"));
	$("#gqbresultpaginationlabel").html(GQB.translate("resultsPerPageLbl"));
	$(".gqb-button-movecolleft > img").attr("title", GQB.translate("moveColLeftLabel"));
	$(".gqb-button-movecolright > img").attr("title", GQB.translate("moveColRightLabel"));
	$(".gqb-button-sortasc > img").attr("title", GQB.translate("sortColAscMsg"));
	$(".gqb-button-sortdesc > img").attr("title", GQB.translate("sortColDescMsg"));
	$("#gqbresultsperpageselectorAlle").html(GQB.translate("allResultsLabel"));
	$("#gqbreloadbuttonlabel").html(GQB.translate("updateResultsBtnLbl"));

	// "east" pane
	$("#gqbpropertiesheader").html(GQB.translate("propertiesFieldLbl"));
	$("#gqbPatternPropertiesNameLabel").html(GQB.translate("patternNameFieldLbl"));
	$("#gqbPatternPropertiesInstLabel").html(GQB.translate("patternInstFieldLbl",0));
	$("#gqbPatternPropertiesDescLabel").html(GQB.translate("patternDescFieldLbl"));
	$("#gqbPatternPropertiesSave").html(GQB.translate("setPatternPropsBtnLbl"));
	$("#gqbShowClassButton").html(GQB.translate("backToClassPropsBtnLbl"));
	$("#gqbPatternPropertiesSelectedClassLabel").html(GQB.translate("selectedClassFieldLbl"));
	$("#gqbClassPropertiesRdftypeLabel").html(GQB.translate("classTypeFieldLbl"));
	$("#gqbClassPropertiesNumInstancesLabel").html(GQB.translate("numInstancesFieldLbl"));
	$("#gqbShowPatternButton").html(GQB.translate("showPatternPropsBtnLbl"));
	$("#gqbClassPropertiesPropertiesLabel").html(GQB.translate("classPropsFieldLbl"));
	$("#gqbClassPropertiesRestrictionsLabel").html(GQB.translate("classRestFieldLbl"));
	$("#gqbaddrestrictionlabel").html(GQB.translate("addRestFieldLbl"));
	$("#gqbClassPropertiesOutgoinglinksLabel").html(GQB.translate("classAllLinksFieldLbl"));
	$("#gqbClassPropertiesSelectedlinksLabel").html(GQB.translate("classSelLinksFieldLbl"));

	// "west" pane
	/*
	$("#gqbqueryheader").html(GQB.translate("queryPatternsFolderLbl"));
	$("#gqbsavedqueriestreelabel").html(GQB.translate("savedQueriesFolderLbl"));
	$("#gqbsavedqueriestree-saved-categorieslabel").html(GQB.translate("profsFolderLbl"));
	$("#gqbsavedsampleentrylabel").html(GQB.translate("profsBefore1945Lbl"));
	$("#gqbsavedqueriestree-basiclabel").html(GQB.translate("baseClassesFolderLbl"));
	*/
	// strings in dialog boxes (union/intersect, link selection)
	$("#gqbDialogUnionIntLabel").html(GQB.translate("combinePatternsHowLbl"));
	$("#gqbDialogUnionIntUnion").html(GQB.translate("unionBtnLbl"));
	$("#gqbDialogUnionIntInt").html(GQB.translate("intersectBtnLbl"));
	$("#gqbDialogUnionIntCancel").html(GQB.translate("cancelBtnLbl"));
	$("#gqbDialogLinkSelLabel").html(GQB.translate("linkClassesHowMsg"));
	$("#gqbDialogLinkSelOk").html(GQB.translate("okBtnLbl"));
	$("#gqbDialogLinkSelCancel").html(GQB.translate("cancelBtnLbl"));
};

/**
 * Initializes the Raphael canvas and trash, as well
 * as the mousedown handler for the trash.
 */
GQBView.prototype.initCanvas = function() {
	//setup canvas and store width and height
	this.raphCanvasWidth = parseInt($("#gqbcanvas").css("width"));
	this.raphCanvasHeight = parseInt($("#gqbcanvas").css("height"));
	this.raphaelCanvas = Raphael("gqbcanvas", this.raphCanvasWidth, this.raphCanvasHeight);

	// add trash and trashmask to canvas:
	this.trash = this.raphaelCanvas.image(urlBase.substr(0,urlBase.length-10)+"extensions/components/graphicalquerybuilder/resources/images/trash.png", this.raphCanvasWidth - 50, this.raphCanvasHeight - 50, 48, 48);
	var trashmask = this.raphaelCanvas.image(urlBase.substr(0,urlBase.length-10)+"extensions/components/graphicalquerybuilder/resources/images/trashmask.png", this.raphCanvasWidth - 50, this.raphCanvasHeight - 50, 48, 48);
	trashmask.mousedown(function(){ 
		if (confirm(GQB.translate("confirmDelAllPatMsg"))) {
			for (var i = 0; i < GQB.view.patterns.length; i++) {
				if (!GQB.model.findPatternById(GQB.view.patterns[i].id).allClassesHaveGottenProps()) {
					alert(GQB.translate("cantDeleteAllPatternsUntilLoadedMsg"));
					return;  
				}
			}
			while (GQB.view.patterns[0]) {
				GQB.view.deletePattern(GQB.view.patterns[0]);
			}
			GQB.view.showInfo(GQB.translate("deletedAllPatMsg"));
		}
	});

	trashmask.toFront();
	this.trash.toBack();
	this.trash.m = 48*48*10;
	this.trash.x = this.trash.attr("x");
	this.trash.y = this.trash.attr("y");
	trashmask.mouseover(function(){GQB.view.mousedOverTrash = true;});
	trashmask.mouseout(function(){GQB.view.mousedOverTrash = false;});
};

/**
 * Sets up the handlers "document.onmousemove" and "document.onmouseup".
 * Also sets a JQuery "droppable" handler for the Raphael canvas.
 */
GQBView.prototype.initMouseMoveAndDropHandlers = function() {
    $(".navigation.Resource").livequery(
        function(){$(this).draggable({
            appendTo: 'body',
            iframeFix: false,
            cursorAt: {right: 0},
            zIndex: 1000,
            ghosting: true,
            revert: true,
            opacity: 0.7,
            scroll: false,
            stack: '#gqbcanvas',
            helper: 'clone'
        });
    });
	
	// Handles the translation of dragged objects on the Raphael canvas, as
	// well as collision detection with the canvas edge.
	var oldMouseMove = document.onmousemove;
	document.onmousemove = function (e) {
		if (typeof oldMouseMove == "function") oldMouseMove(e);

		e = e || window.event;

		// handle resizing of columns by drag:
		//if (GQB.view.draggedColLeftHead) {
		//	GQB.view.draggedColLeftHead.css("width", (GQB.view.dragColBaseWidth + GQB.view.dragColMouseDownX - e.clientX) + "px");
		//}
		//if (GQB.view.draggedColRightHead) {
		//	GQB.view.draggedColRightHead.css("width", (GQB.view.dragColBaseWidth + e.clientX - GQB.view.dragColMouseDownX) + "px");
		//}

		// handle dragging of rapheal boxes:
		if (GQB.view.draggedViewClass) {
			GQB.view.draggedViewClass.translate(e.clientX - GQB.view.draggedViewClass.dx, e.clientY - GQB.view.draggedViewClass.dy);
			GQB.view.draggedViewClass.raphLabel.toBack();
			GQB.view.trash.toBack();
			
			// don't allow dragging off the edges:
			var x = GQB.view.draggedViewClass.x;
			var y = GQB.view.draggedViewClass.y;
			var width = GQB.view.draggedViewClass.width;
			var height = GQB.view.draggedViewClass.height;
			if (x+width-5 < 0) {
				GQB.view.draggedViewClass.translate(10-x-width,0);
			} else if (x+5 > GQB.view.raphCanvasWidth) {
				GQB.view.draggedViewClass.translate(GQB.view.raphCanvasWidth-x-10,0);
			}
			if (y+height-5 < 0) {
				GQB.view.draggedViewClass.translate(0,10-y-height);
			} else if (y+5 > GQB.view.raphCanvasHeight) {
				GQB.view.draggedViewClass.translate(0,GQB.view.raphCanvasHeight-y-10);
			}
			
			if (!GQB.view.draggedViewClass.parentViewPattern.isBlackBoxed) {
				for (var j = 0; j < GQB.view.draggedViewClass.parentViewPattern.connections.length; j++) {
					GQB.view.raphaelCanvas.connectionWithArrowAndLabel(GQB.view.draggedViewClass.parentViewPattern.connections[j].raphConnection);
				}
			}
			
			GQB.view.raphaelCanvas.safari();
			GQB.view.draggedViewClass.dx = e.clientX;
			GQB.view.draggedViewClass.dy = e.clientY;
			//GQB.view.refreshCanvas(); 
		}
	};

	// Handles dropping of canvas items onto other canvas items (trash or
	// Raphael boxes).
	var oldMouseUp = document.onmouseup;
	document.onmouseup = function (e) {
		if (typeof oldMouseUp == "function") oldMouseUp(e);

		e = e || window.event;
		GQB.view.draggedColLeftHead = false;
		GQB.view.draggedColRightHead = false;

		if (!GQB.view.draggedViewClass) return;

		var draggedViewClass = GQB.view.draggedViewClass;
		GQB.view.draggedViewClass = false;

		draggedViewClass.raphBox.animate({"fill-opacity": 0}, 500);

		// dragged box over trash: remove corresponding pattern:
		if (GQB.view.mousedOverTrash) {
		
			var doCleanup = function () {
				draggedViewClass.translate(-200, -150);
				// update connections
				if (!draggedViewClass.parentViewPattern.isBlackBoxed) {
					for (var j = 0; j < draggedViewClass.parentViewPattern.connections.length; j++) {
						GQB.view.raphaelCanvas.connectionWithArrowAndLabel(draggedViewClass.parentViewPattern.connections[j].raphConnection);
					}
				}
			};
			
			var confDelPattern = confirm(GQB.translate("confirmDelPatMsg"));
			if (confDelPattern) {          
				if (!draggedViewClass.parentViewPattern.modelPattern.allClassesHaveGottenProps()) {
					alert(GQB.translate("cantDeletePatternUntilLoadedMsg"));
					doCleanup();
					return;
				}
				GQB.view.deletePattern(draggedViewClass.parentViewPattern);
			} else {
				var confDelClass = confirm(GQB.translate("confirmDelClassMsg"));
				if (confDelClass) {
					if (!draggedViewClass.modelClass.isReady()) {
						alert(GQB.translate("cantDeleteClassUntilLoadedMsg"));
						doCleanup();
						return;
					}
					GQB.view.deleteClass(draggedViewClass);
				}
				else {
					doCleanup();
				}
			}
			GQB.view.clearClassProperties();
		}
		
		// dragged box was dropped onto another box:
		else if (GQB.view.mousedOverViewClass) {
		  var doCleanup = function () {
				draggedViewClass.raphBox.toFront();
				draggedViewClass.raphTrashIcon.toFront();
				if (draggedViewClass.raphBlackBoxIcon)
					draggedViewClass.raphBlackBoxIcon.toFront();
			};
		
			var draggedClassId = draggedViewClass.id;
			var mousedOverClassId = GQB.view.mousedOverViewClass.id;

			// we don't want to allow dragging of an object onto itself.
			if (draggedClassId == mousedOverClassId) { 
				doCleanup();
				return; 
			}

			var mousedOverClass = GQB.view.mousedOverViewClass.modelClass;

			// if dragged onto an object that isn't ready (no properties):
			if (!mousedOverClass.isReady()) {
				alert (GQB.translate("classLoadingTryAgainMsg", mousedOverClass.type.getLabel())); 
				doCleanup();
				return;
			}
			
			// can't combine classes that have different types:
			if (mousedOverClass.type.uri != draggedViewClass.modelClass.type.uri) {
				doCleanup();
				return;
			}
			
			var draggedClass = draggedViewClass.modelClass;
			var draggedPattern = draggedViewClass.parentViewPattern.modelPattern;

			var mousedOverPattern = GQB.view.mousedOverViewClass.parentViewPattern.modelPattern;

			// at least one class should be the start classes of its respective pattern
			// can't combine classes of the same pattern:
			if ((draggedPattern.startClass.id != draggedClass.id && mousedOverPattern.startClass.id != mousedOverClass.id)
			   || draggedPattern.id == mousedOverPattern.id) {
				doCleanup();
				return;
			}
			
			// store the dragged and moused over objs in special variables:
			GQB.view.draggedViewClassToCombine = draggedViewClass;
			GQB.view.mousedOverViewClassToCombine = GQB.view.mousedOverViewClass;
			// show a dialog asking how to combine the classes:
			$("#gqbDialogBg").css("display","block");
			$("#gqbDialogUnionInt").css("display","block");
			
		// normal drag, not dragged over anything (goes here, because draggedViewClass was removed in above if statement)
		} else {  
			draggedViewClass.raphBox.toFront();
			draggedViewClass.raphTrashIcon.toFront();
			if (draggedViewClass.raphBlackBoxIcon)
				draggedViewClass.raphBlackBoxIcon.toFront();
		}
	};
	
	// Handles dropping of items from the left ("west") tree onto the Raphael canvas.
	// These items can be either saved queries or basic classes (both with a "modelId").  
	// Also stores the dropped mouse position for later use.
	$("#gqbcanvas").droppable({
		drop: function(event,ui) {
			//if ($(ui.draggable).attr("modelId") == undefined) return;
			
			GQB.view.dropCursorPosX = event.pageX;
			GQB.view.dropCursorPosY = event.pageY;
			
			// handle dropping of saved queries:
			if ($(ui.draggable).hasClass("savedquery")) {
				// we need to get the saved query from the DB, and for that we need it's "saveId",
				// which is stored in the html attribute "modelId":
				var savedpattern = GQB.view.findModelObjectById($(ui.draggable).attr("modelId"));
				if ((!savedpattern.saveId && savedpattern.saveId != 0) || !savedpattern.type) {alert("saveId oder type undefiniert! ("+$(ui.draggable).attr("modelId")+")");return;}
				var sp_id = savedpattern.saveId;
				// this sparql query gets the desired saved query from the DB:
				var getSavedQuery = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#> \
															PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#> \
															SELECT ?queryuri ?name ?desc ?type ?typelabel ?date ?patternjson ?query \
															WHERE { ?queryuri rdf:type <"+GQB.patternClassName+"> . \
															?queryuri <"+GQB.patternName+"> ?name . \
															OPTIONAL {?queryuri <"+GQB.patternDesc+"> ?desc } . \
															?queryuri <"+GQB.patternType+"> ?type . \
															?queryuri <"+GQB.patternTypeLabel+"> ?typelabel . \
															OPTIONAL {?queryuri <"+GQB.patternDate+"> ?date} . \
															?queryuri <"+GQB.patternJson+"> ?patternjson . \
															?queryuri <"+GQB.patternSaveId+"> ?id . \
															OPTIONAL {?queryuri <"+GQB.patternQuery+"> ?query } . \
															FILTER(?id="+sp_id+")\
															}";

				//set base url of SPARQL query service
				var endpoint = urlBase + "service/sparql";

				// before we send off the query, we set up a dummy class to be displayed
				// in the model.  This dummy class will show a loading spinner until
				// the saved query is retrieved, at which point it will turn into the saved pattern.
				var dummypattern = new GQBQueryPattern();
				GQB.model.addPattern(dummypattern);
				var dummyStartClass = new GQBClass(savedpattern.type);
				dummypattern.setStartClass(dummyStartClass);
				GQB.view.findViewClassById(dummyStartClass.id).wait();  // show spinner

				// send the query:
				$.ajaxSetup({'beforeSend': function(xhr) {xhr.setRequestHeader("Accept", "application/sparql-results+json")}}) 
				jQuery.post(
					endpoint, 
					{
						"default-graph-uri": GQB.userDbUri,
						query: getSavedQuery
					},
					function(result){
						if(result == ""){
							alert(GQB.translate("cantLoadClassesPlsLogInMsg"));
							return;
						}

						// interpret the result, which is json encoded:
						try {
							var jsonresult = eval ( " ( "+ result +" ) ");
						} catch (e) {
							alert(GQB.translate("errorParsingQueriesMsg"));
							return;
						}
						if(jsonresult.bindings.length != 1){
							alert(GQB.translate("errorLoadingQueryMsg", jsonresult.bindings.length));
							return;
						}

						// Get data from the result:
						// (do we want to do anything with this other data??)
						//var queryuri = $(jsonresult.bindings[0].queryuri).attr("value");
						//var name = $(jsonresult.bindings[0].name).attr("value");
						//var id = $(jsonresult.bindings[0].id).attr("value");
						var json = $(jsonresult.bindings[0].patternjson).attr("value");
						//var query = $(jsonresult.bindings[0].query).attr("value");
						//var date = $(jsonresult.bindings[0].date).attr("value");
						//var desc = $(jsonresult.bindings[0].desc).attr("value");
						//var type = $(jsonresult.bindings[0].type).attr("value");
						//var typelabel = $(jsonresult.bindings[0].typelabel).attr("value");

						try {
							// now that we have the saved query as a json object, we can attempt
							// to restore it, thereby overwriting the previously created dummy pattern and class:
							var savedPattern = eval ( " ( "+ json +" ) ");
							GQB.view.findPatternById(dummypattern.id).fromBlackBox();  // expand if contracted
							dummypattern.restore(savedPattern);  // this restores the retrieved data

							// restore proper label (of start class):
							var dummyViewClass = GQB.view.findViewClassById(dummyStartClass.id);
							dummyViewClass.setLabel(dummyStartClass.type.getLabel());
							dummyViewClass.raphLabel.attr("font-style","italic");  // start class is italic

							// as a graphical effect, show the newly restored pattern being expanded:
							GQB.view.findPatternById(dummypattern.id).toBlackBox();  // contract first
							// comment out the following line if the restored pattern shouldn't automatically expand:
							GQB.view.findPatternById(dummypattern.id).fromBlackBox();
						} catch (e) {
							alert(GQB.translate("errorParsingPatMsg",e));
							return;
						}
					}
				);
				return;
			}
			
			// handle dropping of basic classes
			var draggedClass = GQB.model.findRDFClassByUri($(ui.draggable).attr("about"));
			if (!draggedClass) {
				//not found - ontowiki tree does some strange things
				//alert("not found: "+draggedClass);
				draggedClass = new GQBrdfClass($(ui.draggable).attr("about"), $.trim($(ui.draggable).html().split("<")[0]), GQB.currLang);
                                GQB.model.classes.push(draggedClass);
			}
			
			
			// the dropped class was either dropped on a preexisting class-box or onto some whitespace
			// we differentiate between the two:
			if (GQB.view.mousedOverViewClass) { // dropped over a preexisting box
			
				var mousedOverClass = GQB.view.mousedOverViewClass.modelClass;
				var mousedOverPattern = GQB.view.mousedOverViewClass.parentViewPattern.modelPattern;
				
				if (!mousedOverClass.isReady()) {
					alert (GQB.translate("classLoadingTryAgainMsg", mousedOverClass.type.getLabel())); 
					return;
				}
				
				// attempt to find possible links between topMousedOverClass and draggedClass:
				var linksBetweenClasses = new Array();
				for (var i = 0; i < mousedOverClass.type.outgoingLinks.length; i++) {
					if (mousedOverClass.type.outgoingLinks[i].range == draggedClass.uri) {
						linksBetweenClasses.push([mousedOverClass.type.outgoingLinks[i], draggedClass.uri]);
					}
					for (var j = 0; j < draggedClass.parents.length; j++) {
						if (mousedOverClass.type.outgoingLinks[i].range == draggedClass.parents[j].uri) {
							linksBetweenClasses.push([mousedOverClass.type.outgoingLinks[i], draggedClass.uri]);
						}
					}
				}
				
				// no links found:
				if (linksBetweenClasses.length==0) { 
					alert(GQB.translate("noConnectionBetweenClassesMsg", mousedOverClass.type.getLabel(), draggedClass.getLabel())); 
					return; 
				}
				// one link found,
				// expand mousedOverPattern by the currently dragged class:
				else if (linksBetweenClasses.length==1) {
					if(GQB.view.mousedOverViewClass.parentViewPattern) {
						if(GQB.view.mousedOverViewClass.parentViewPattern.isBlackBoxed)
							GQB.view.mousedOverViewClass.parentViewPattern.toggleBlackBox();
					}
					mousedOverPattern.add(mousedOverClass, linksBetweenClasses[0][0], true, linksBetweenClasses[0][1]);
					GQB.view.showInfo(GQB.translate("patternExpandedMsg"));
				}
				// multiple links found,
				// show dialog box with all links: 
				else {
					$("#gqbDialogLinkSelSel").html("");
					for (var i = 0; i < linksBetweenClasses.length; i++) {  
						 $("#gqbDialogLinkSelSel").append("<option value=\""+linksBetweenClasses[i][0].uri+"\", whattype=\""+linksBetweenClasses[i][1]+"\">"+linksBetweenClasses[i][0].getLabel()+"</option>");
					}
					GQB.view.mousedOverViewClassToCombine = mousedOverClass;
					$("#gqbDialogBg").css("display","block");
					$("#gqbDialogLinkSel").css("display","block");
				}
			} else { // dropped over whitespace  ->  create a new pattern   
				var newPattern = new GQBQueryPattern();
				GQB.model.addPattern(newPattern);
				var newStartClass = new GQBClass(draggedClass);
				newStartClass.getPropertiesWithInherited();
				newPattern.setStartClass(newStartClass);
				
				GQB.view.showInfo(GQB.translate("newPatternMsg"));
			}
		}
	});
};

/**
 * Sets up the click and change handlers for the various
 * buttons and controls in the view or in any of the panes
 * ("west", "east", "north", "south", etc.).  This is all
 * done via jQuery.
 */
GQBView.prototype.initMouseClickHandlers = function() {
	$("#gqbDialogUnionIntUnion").click(function(){
		GQB.view.combineDraggedAndMousedOverClasses("union");
		$("#gqbDialogBg").css("display","none");
		$("#gqbDialogUnionInt").css("display","none");  
		GQB.view.showInfo(GQB.translate("unionPatMsg"));
	});

	$("#gqbDialogUnionIntInt").click(function(){
		GQB.view.combineDraggedAndMousedOverClasses("intersection");
		$("#gqbDialogBg").css("display","none");
		$("#gqbDialogUnionInt").css("display","none");  
		GQB.view.showInfo(GQB.translate("intersectPatMsg"));
	});

	$("#gqbDialogUnionIntCancel").click(function(){
		GQB.view.draggedViewClassToCombine = false;
		GQB.view.mousedOverViewClassToCombine = false;
		$("#gqbDialogBg").css("display","none");
		$("#gqbDialogUnionInt").css("display","none");  
	});

	$("#gqbDialogLinkSelOk").click(function(){
		// GQB.view.mousedOverViewClassToCombine has been set to the model(!) class of the moused over object:
		var mousedOverPattern = GQB.model.findPatternOfClass(GQB.view.mousedOverViewClassToCombine);
		
		// find which link was selected in the selector box:
		var theLink = null;
		for (var i = 0; i < GQB.view.mousedOverViewClassToCombine.type.outgoingLinks.length; i++) {
			if (GQB.view.mousedOverViewClassToCombine.type.outgoingLinks[i].uri == $("#gqbDialogLinkSelSel").attr("value")) {
				theLink = GQB.view.mousedOverViewClassToCombine.type.outgoingLinks[i];
			}
		}
		if (theLink) {
			var whattype = $("#gqbDialogLinkSelSel > option[value='"+$("#gqbDialogLinkSelSel").attr("value")+"']").attr("whattype");
			mousedOverPattern.add(GQB.view.mousedOverViewClassToCombine, theLink, true, whattype);
		}
		GQB.view.mousedOverViewClassToCombine = false;
		if(GQB.view.findPatternById(mousedOverPattern.id).isBlackBoxed) {
			GQB.view.findPatternById(mousedOverPattern.id).toggleBlackBox();
		}
		$("#gqbDialogBg").css("display","none");
		$("#gqbDialogLinkSel").css("display","none");  
		GQB.view.showInfo(GQB.translate("patternExpandedMsg"));
	});

	$("#gqbDialogLinkSelCancel").click(function(){
		$("#gqbDialogBg").css("display","none");
		$("#gqbDialogLinkSel").css("display","none");  
	});

	$(".gqbFunButton").click(function(){
		if(GQB.view.runPhysSteps == undefined) GQB.view.runPhysSteps = 0;
		GQB.view.runPhysSteps = (GQB.view.runPhysSteps == 0) ? -1 : 0;

		if(GQB.view.runPhysSteps == 0) {
			window.clearInterval(GQB.view.runPhysInterval);
			GQB.view.runPhysInterval = false;
			$(this).html("");
			alert("Eieiei!");
			for (var i = 0; i < GQB.view.patterns.length; i++) {
				for (var j = 0; j < GQB.view.patterns[i].classes.length; j++) {
					GQB.view.patterns[i].classes[j].raphBox.attr({"stroke":GQB.view.box_color});
				}
			}
			return; 
		}
		alert("Was ist denn hier los??");
		for (var i = 0; i < GQB.view.patterns.length; i++) {
			for (var j = 0; j < GQB.view.patterns[i].classes.length; j++) {
				GQB.view.patterns[i].classes[j].vx = 240*Math.random() - 120.0;
				GQB.view.patterns[i].classes[j].vy = 240*Math.random() - 120.0;
				GQB.view.patterns[i].classes[j].ax = 0.0;
				GQB.view.patterns[i].classes[j].ay = 0.0;
				GQB.view.patterns[i].classes[j].rdec = 0;
				GQB.view.patterns[i].classes[j].gdec = 11;
				GQB.view.patterns[i].classes[j].bdec = 15;
				GQB.view.patterns[i].classes[j].m = GQB.view.patterns[i].classes[j].width*GQB.view.patterns[i].classes[j].height;
				GQB.view.patterns[i].classes[j].dontChangeColor = false;
			}
		}
		
		for (var i = 0; i < GQB.view.patterns.length; i++) {
			for (var j = 0; j < GQB.view.patterns[i].connections.length; j++) {
				GQB.view.patterns[i].connections[j].dontCalc = false;
				GQB.view.patterns[i].connections[j].neutralSpringLength = Math.sqrt( 
												Math.pow(GQB.view.patterns[i].connections[j].startViewClass.x + 
												 GQB.view.patterns[i].connections[j].startViewClass.width/2 - 
												 GQB.view.patterns[i].connections[j].endViewClass.x - 
												 GQB.view.patterns[i].connections[j].endViewClass.width/2,2) + 
												Math.pow(GQB.view.patterns[i].connections[j].startViewClass.y + 
												 GQB.view.patterns[i].connections[j].startViewClass.height/2 - 
												 GQB.view.patterns[i].connections[j].endViewClass.y - 
												 GQB.view.patterns[i].connections[j].endViewClass.height/2,2) );
			}
		}
		
		$(this).html("OFF");
		GQB.view.runPhysics(50);
	});

	$(".gqbClassPropertiesSelectedlink").live("click", function(){
		var tmp = GQB.view.findModelObjectById($(this).attr("modelId"));
		tmp.optional = !tmp.optional;
	});

	$("#gqbPropertySwitcher").live("click", function(){
		if ($("#gqbClassPropertiesProperties").css("display") == "none" ) {
			$("#gqbClassPropertiesProperties").show();
			$("#gqbMinimizeProperties").show();
			$("#gqbMaximizeProperties").hide();
		} else {
			$("#gqbClassPropertiesProperties").hide();
			$("#gqbMinimizeProperties").hide();
			$("#gqbMaximizeProperties").show();
		}
	});

	$("#gqbClassPropertiesWithChilds").live("click", function(){
		var tmp = GQB.view.findModelObjectById($(this).attr("modelId"));
		tmp.withChilds = !tmp.withChilds;
		tmp.recalculateNumInstances();
	});
	
	$("#gqbClassPropertiesShowUriCheckBox").live("click", function(){
		var tmp = GQB.view.findModelObjectById($(this).attr("modelId"));
		tmp.showUri = !tmp.showUri;
	});

	$("#gqbPatternPropertiesDistinct").live("click", function(){
		var tmp = GQB.view.findModelObjectById($(this).attr("modelId"));
		tmp.distinct = !tmp.distinct;
	});

	$(".gqbPatternPropertiesSelectedClassRadio").live("click", function(){
		var mo_id = $(this).attr("modelId");
		var selClass = GQB.view.findModelObjectById(mo_id);
		var pattern = GQB.model.findPatternOfClass(selClass);
		pattern.setSelectedClass(selClass);
	});

	$(".classPropertiesPropertiesCheckBox").live("click",function(){
		var tmp = GQB.view.findModelObjectById($(this).attr("modelId"));

		if($(this).is(":checked")){
			GQB.view.selectedViewClass.modelClass.addShownProperty(tmp.uri);
		}
		else{
			GQB.view.selectedViewClass.modelClass.removeShownProperty(tmp.uri);
		} 
	});

	$(".gqb-button-deletequery").live("click" ,function(){
		var id = $(this).attr("modelId");
		var modelPattern = GQB.view.findModelObjectById(id);
		if (!modelPattern) return;  // sollte nicht passieren, ist schwerer Fehler
		
		if(confirm(GQB.translate("confirmDelSavedPatternMsg"))){
			modelPattern.deletefromdb();
		}
	});

	$("#gqbprevresultspagebutton").click(function(){
		if(!GQB.view.resultColumns[0]) return;
		if(GQB.view.displayAllResults) return;
		if(GQB.view.curResultsPage > 0) GQB.view.curResultsPage--;
		
		$("#gqbpagedisplay").html("&nbsp;&nbsp;&nbsp;&nbsp;<b>"+GQB.translate("numResultsLbl", GQB.view.curResultsPage*GQB.view.resultsPerPage+1, ((GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage)<=(GQB.view.resultColumns[0].length-1)?(GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage):(GQB.view.resultColumns[0].length-1)), GQB.view.resultColumns[0].length-1)+"</b>");
		GQB.view.refreshCurrentResultsTable();
	});

	$("#gqbnextresultspagebutton").click(function(){
		if(!GQB.view.resultColumns[0]) return;
		if(GQB.view.displayAllResults) return;
		if(GQB.view.curResultsPage < (GQB.view.resultColumns[0].length-1)/GQB.view.resultsPerPage) GQB.view.curResultsPage++;
		
		$("#gqbpagedisplay").html("&nbsp;&nbsp;&nbsp;&nbsp;<b>"+GQB.translate("numResultsLbl", GQB.view.curResultsPage*GQB.view.resultsPerPage+1, ((GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage)<=(GQB.view.resultColumns[0].length-1)?(GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage):(GQB.view.resultColumns[0].length-1)), GQB.view.resultColumns[0].length-1)+"</b>");
		GQB.view.refreshCurrentResultsTable();
	});

	$(".gqbresultsperpageselector").click(function(){
		var rpp = $(this).attr("rpp");
		var oldrpp = GQB.view.resultsPerPage;
		
		$("#gqbresultsperpageselector"+rpp).addClass("gqbcurrentresultsperpageselector");
		$("#gqbresultsperpageselector"+(GQB.view.displayAllResults ? "Alle" : oldrpp)).removeClass("gqbcurrentresultsperpageselector");

		if (rpp == "Alle") {
			rpp = (GQB.view.resultColumns[0]) ? GQB.view.resultColumns[0].length : 1;
			GQB.view.displayAllResults = true;
		} else {
			rpp = parseInt(rpp);
			GQB.view.displayAllResults = false;
		}
		GQB.view.resultsPerPage = rpp;

		var newCurrentPage = Math.floor(oldrpp * GQB.view.curResultsPage / rpp);
		GQB.view.curResultsPage = newCurrentPage;
		if(!GQB.view.resultColumns[0]) return;

		// show page number (old):
		//$("#gqbpagedisplay").html("&nbsp;&nbsp;&nbsp;&nbsp;<b>Seite: "+(GQB.view.curResultsPage+1)+"/"+((Math.floor(GQB.view.resultColumns[0].length/GQB.view.resultsPerPage)) + 1)+"</b>");

		// show result number instead:
		$("#gqbpagedisplay").html("&nbsp;&nbsp;&nbsp;&nbsp;<b>"+GQB.translate("numResultsLbl", GQB.view.curResultsPage*GQB.view.resultsPerPage+1, ((GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage)<=(GQB.view.resultColumns[0].length-1)?(GQB.view.curResultsPage*GQB.view.resultsPerPage+GQB.view.resultsPerPage):(GQB.view.resultColumns[0].length-1)), GQB.view.resultColumns[0].length-1)+"</b>");
		$(".ui-layout-south").get(0).scrollTop = 0;
		GQB.view.refreshCurrentResultsTable();
	});

	$(".gqb-button-sortasc").live("click", function(){
		// index of the column to sort by:
		var cIdx = parseInt($(this).attr("cIdx"));
		GQB.view.sortColumns(cIdx,"up");
		GQB.view.refreshCurrentResultsTable();
	});

	$(".gqb-button-sortdesc").live("click", function(){
		// index of the column to sort by:
		var cIdx = parseInt($(this).attr("cIdx"));
		GQB.view.sortColumns(cIdx,"down");
		GQB.view.refreshCurrentResultsTable();
	});

	$(".gqb-button-movecolleft").live("click", function(){
		// index of the column to move:
		var cIdx = parseInt($(this).attr("cIdx"));
		if (cIdx <= 0 || GQB.view.resultColumns.length <= cIdx) return;

		// swap cIdx and cIdx-1:
		var tmpCol = GQB.view.resultColumns[cIdx-1];
		GQB.view.resultColumns[cIdx-1] = GQB.view.resultColumns[cIdx];
		GQB.view.resultColumns[cIdx] = tmpCol;

		GQB.view.refreshCurrentResultsTable();
	});

	$(".gqb-button-movecolright").live("click", function(){
		// index of the column to move:
		var cIdx = parseInt($(this).attr("cIdx"));
		if (cIdx < 0 || GQB.view.resultColumns.length <= cIdx+1) return;

		// swap cIdx and cIdx+1:
		var tmpCol = GQB.view.resultColumns[cIdx];
		GQB.view.resultColumns[cIdx] = GQB.view.resultColumns[cIdx+1];
		GQB.view.resultColumns[cIdx+1] = tmpCol;

		GQB.view.refreshCurrentResultsTable();
	});

	$(".gqb-button-movecolleft").live("mousedown", function(event){
		var cIdx = parseInt($(this).attr("cIdx"));
		GQB.view.draggedColLeftHead = $(this).parents("th");
		GQB.view.dragColMouseDownX = event.pageX;
		GQB.view.dragColBaseWidth = parseInt($(this).parent().css("width"));
	});

	$(".gqb-button-movecolright").live("mousedown", function(event){
		var cIdx = parseInt($(this).attr("cIdx"));
		GQB.view.draggedColRightHead = $(this).parents("th");
		GQB.view.dragColMouseDownX = event.pageX;
		GQB.view.dragColBaseWidth = parseInt($(this).parent().css("width"));
	});

	$("#gqbprintbutton").click(function(){
		var curPattern = GQB.view.selectedViewClass.parentViewPattern.modelPattern;

		if (!curPattern) return;
		// Put querystring in hidden div for printout
		$('#gqbresultsparqlquery').html("<h1 class='title'>"+GQB.translate("sparqlQueryLbl")+"</h1><p>"+$('<div/>').text(curPattern.getQueryAsString()).html().replace(/\n/g, "<br />")+"</p>");
		$('#gqbresultquerydesc').html("<h1 class='title'>"+curPattern.name+"</h1><p>"+curPattern.description+"</p>");
		// Issue the print command
				
		window.print();
	});

	$('#gqbdisplayresultsbutton').click(function(){
		GQB.view.updateResult();
	});

	$('#gqbreloadbutton').click(function(){
		GQB.view.updateResult();
	});

	$("#gqbClassProperties").hide();
	$("#gqbPatternProperties").hide();

	$("#gqbshowquerybutton").click(function(){
		if (!GQB.view.selectedViewClass) {
			alert(GQB.translate("noPatternSelMsg"));
		} else {
			GQB.view.showInfo(GQB.view.selectedViewClass.parentViewPattern.modelPattern.getQueryAsString());
		}
	});

	//add restriction by clicking the button right of the according object 
	$("#gqbaddrestriction").live("click", function(){
		if (!GQB.view.selectedViewClass) {
			alert(GQB.translate("noClassSelMsg"));
		} else if (!( $("#restrictionpropselector").length > 0 )) {
			var selClass = GQB.view.selectedViewClass.modelClass;
			if(!selClass.isReady()){
				alert(GQB.translate("classLoadingTryAgainMsg", GQB.view.selectedViewClass.modelClass.type.getLabel())); 
			} else {
				GQB.view.addRestrictionPanel(selClass , $("#gqbClassPropertiesAddRestriction"));

				$("#setrestrictionbutton").click(function(){
					GQB.view.setRestriction(selClass, parseInt("-1"));
					$("#gqbClassPropertiesAddRestriction").empty();
				});

				$("#cancelrestrictionbutton").click(function(){
					$("#gqbClassPropertiesAddRestriction").empty();
				});
			}
		}
		return false;
	});

	//The Restriction-Typeselector and the ValueInputs change dynamically by changing the Property to restrict
	$("#restrictionpropselector").live("change", function(){
		GQB.view.initRestrictionTypeBox();
		GQB.view.initRestrictionValueInputs($("#restvalueinputs"));
		$('#gqbRestrictionNegation').attr('checked', false);
	});
	
	//The Restriction Valueinputs change dynamically by changing the Type of Restriction
	$("#restrictiontypeselector").live("change", function(){
		GQB.view.initRestrictionValueInputs($("#restvalueinputs"));
		$('#gqbRestrictionNegation').attr('checked', false);
	});

	//add Restriction by Click on Button right of the AND-Folder 
	$("#gqbaddrestrictionbuttonand").live("click", function(){
		
		if (!GQB.view.selectedViewClass) {
			alert(GQB.translate("noClassSelMsg"));
		} else if (!( $("#restrictionpropselector").length > 0 )) {
			// Wenn Restriktionshinzufger noch nicht geffnet, das Formular ffnen
			var selClass = GQB.view.selectedViewClass.modelClass;

			GQB.view.addRestrictionPanel(selClass , $("#addrestrictionandpanel"));
			$("#setrestrictionbutton").click(function(){
				GQB.view.setRestriction(selClass, parseInt("-1"));
				$("#addrestrictionandpanel").empty();
			});

			$("#cancelrestrictionbutton").click(function(){
				$("#addrestrictionandpanel").empty();
			});
		}
	});

	// Add an OR-Restriction to the clicked OR-add-Restriction Button 
	$(".gqb-button-add-or").live("click", function(){

		if (!GQB.view.selectedViewClass) {
			alert(GQB.translate("noClassSelMsg"));
		} else if (!( $("#restrictionpropselector").length > 0 )) {

			var selClass = GQB.view.selectedViewClass.modelClass;
			var orIndex = $(this).attr("modelId");

			GQB.view.addRestrictionPanel(selClass , $("#addrestrictionorpanel"+orIndex));
			
			$("#setrestrictionbutton").click(function(){
				GQB.view.setRestriction(selClass, orIndex);
				$("#addrestrictionorpanel"+orIndex).empty();
			});

			$("#cancelrestrictionbutton").click(function(){
				$("#addrestrictionorpanel"+orIndex).empty();
			});
		}
	});
	
	// Delete a Restriction by clicking the delete Restriction-Button
	$(".gqb-button-delete-restriction").live("click", function(){
	  if (!GQB.view.selectedViewClass) {
			alert(GQB.translate("noClassSelMsg"));
			return;
		} 
		var confDel = confirm(GQB.translate("confirmDelRestr"));
		if (confDel) { 
			var selClass = GQB.view.selectedViewClass.modelClass;
			var toplevelIndex = $(this).attr("toplevelIndex");
			var secondlevelIndex = $(this).attr("secondlevelIndex");
			selClass.deleteRestriction(selClass.restrictions.members[toplevelIndex].members[secondlevelIndex],toplevelIndex);		
		}
	});

	// Edit a Restriction by clicking the edit Restriction-Button
	$(".gqb-button-edit-restriction").live("click", function(){
		if (!GQB.view.selectedViewClass) {
			alert(GQB.translate("noClassSelMsg"));
			return;
		} 
		if (!( $("#restrictionpropselector").length > 0 )) {
			var selClass = GQB.view.selectedViewClass.modelClass;
			var toplevelIndex = $(this).attr("toplevelIndex");
			var secondlevelIndex = $(this).attr("secondlevelIndex");

			GQB.view.editRestrictionPanel(selClass , $("#editrestrictionpanel"+toplevelIndex+"-"+secondlevelIndex));

			//set the restriction-property that was chosen before
			$("#restrictionpropselector").val(selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].property.getLabel());

			GQB.view.initRestrictionTypeBox();
			//set the restriction-type that was chosen before (has to be done, if there are more possibilities of choice)
			$("#restrictiontypeselector").val(GQB.view.getRestrictionTypeByMember(selClass.restrictions.members[toplevelIndex].members[secondlevelIndex]));

			//show the valueboxes according to the type
			GQB.view.initRestrictionValueInputs($("#restvalueinputs"));

			//set the values that were set before
			var restrictionType = parseInt($("#restrictiontypeselector :selected").attr("restrictionType"));
			function addExclIfProp(val) {
				var prop = selClass.findPropertyByUri(val);
				if (!prop) return val;
				else return "!" + prop.getLabel();
			};
			switch(restrictionType){
				case 0: //string contains
					var restVal = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionString;
					$("#restvalueselector").attr("value", addExclIfProp(restVal));
					break;
				case 1: //string equals
					var restVal = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionString;
					$("#restvalueselector").attr("value", addExclIfProp(restVal));
					break;
				case 5: //dates after
					var restValA = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionDateFrom;
					$("#restvalueselectorA").attr("value", addExclIfProp(restValA));
					break;
				case 6: //dates before
					var restValB = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionDateTo;
					$("#restvalueselectorB").attr("value", addExclIfProp(restValB));
					break;
				case 7: //dates between
					var restValA = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionDateFrom;
					var restValB = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionDateTo;
					$("#restvalueselectorA").attr("value", addExclIfProp(restValA));
					$("#restvalueselectorB").attr("value", addExclIfProp(restValB));
					break;
				case 10: //number bigger
					var restValA = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionIntFrom;
					$("#restvalueselectorA").attr("value", addExclIfProp(restValA));
					break;
				case 11: //number smaller
					var restValB = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionIntTo;
					$("#restvalueselectorB").attr("value", addExclIfProp(restValB));
					break;
				case 12: //number between
					var restValA = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionIntFrom;
					var restValB = selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].restrictionIntTo;
					$("#restvalueselectorA").attr("value", addExclIfProp(restValA));
					$("#restvalueselectorB").attr("value", addExclIfProp(restValB));
					break;
				default:
					break;
			}

			//set the negation value to the negationcheckbox that was set before
			$('#gqbRestrictionNegation').attr('checked', selClass.restrictions.members[toplevelIndex].members[secondlevelIndex].negation);
		}

		$("#setrestrictionbutton").click(function(){
			selClass.restrictions.members[toplevelIndex].members[secondlevelIndex] = GQB.view.setEditedRestriction(selClass.restrictions.members[toplevelIndex].members[secondlevelIndex]);
			$("#editrestrictionpanel"+toplevelIndex+"-"+secondlevelIndex).empty();
		});

		$("#cancelrestrictionbutton").click(function(){
			$("#editrestrictionpanel"+toplevelIndex+"-"+secondlevelIndex).empty();  
		});
	});

	$('#gqbShowPatternButton').live("click", function(){
		GQB.view.showPatternProperties();
	});

	$('#gqbShowClassButton').live("click", function(){
		GQB.view.showClassProperties();
	});

	$('#gqbPatternPropertiesSave').live("click", function(){
		var pattern = GQB.view.findModelObjectById($(this).attr("modelId"));
		GQB.view.showInfo(GQB.translate("savedChangesMsg"));
		pattern.name = $("#gqbPatternPropertiesName").attr("value");
		pattern.description = $("#gqbPatternPropertiesDesc").val();
	});

	$(".gqbsetlangbutton").click(function(){
		var theLang = $(this).attr("lang");
		if (GQB.supportedLangs.indexOf(theLang) == -1) {
			alert (GQB.translate("langNotSupportedMsg"));
			return;
		}

		GQB.currLang = theLang;
		GQB.model.lang = GQB.currLang;
		GQB.view.initControlLabels();
		GQB.view.showClassProperties();
		$(".basicclass").each(function(){
			var obj = GQB.view.findModelObjectById($(this).attr("modelId"));
			$(this).html(obj.getLabel());
		});
		$(".folder").each(function(){
			var obj = GQB.view.findModelObjectById($(this).attr("modelId"));
			if (obj)
				$(this).html(obj.getLabel());
		});
		for (var i = 0; i < GQB.view.patterns.length; i++) {
			for (var j = 0; j < GQB.view.patterns[i].classes.length; j++) {
				GQB.view.patterns[i].classes[j].setLabel(GQB.view.patterns[i].classes[j].modelClass.type.getLabel());
			}
			for (var j = 0; j < GQB.view.patterns[i].connections.length; j++) {
				GQB.view.patterns[i].connections[j].setLabel();
			}
		}
		GQB.view.showInfo(GQB.translate("setCurLanguageMsg"));
	});
};
