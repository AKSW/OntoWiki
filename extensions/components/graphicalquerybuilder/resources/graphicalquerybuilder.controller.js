/**
 * a controller event.
 * the model fires events to inform the controller of changes. the controller reacts with actions in the view
 * @param {string} _name
 * @param {Object} _obj
 * @class
 */
function GQBEvent(_name, _obj){
	this.name = _name;
	this.obj = _obj;
}

/**
 * the controller-part of the mvc.
 * is used to handle events triggerd in the model
 * @class
 */
function GQBController(){
	this.notify;
}

/**
 * here happens the event-dispatching
 * @param {Object} _event
 */
GQBController.prototype.notify = function (_event){
	switch(_event.name) {
		case "gotClasses":
			//GQB.view.addRdfClassesToTree(_event.obj);
			break;
		case "gotQueries":
			//GQB.view.addQueriesToTree(_event.obj);
			break;
		case "newPattern":
			GQB.view.addPatternToCanvas(_event.obj);
			GQB.view.showInfo(GQB.translate("newPatternMsg"));
			break;
		case "expandedPattern":
			GQB.view.addClassToPatternAtPos(_event.obj[0], _event.obj[1], _event.obj[2]);
			GQB.view.selectClass(_event.obj[1], false);
			GQB.view.showInfo(GQB.translate("patternExpandedMsg"));
			break;
		case "setSelectedClass": 
			// "black boxing" in view corresponds to selecting in model:
			GQB.view.setBlackBoxClass(_event.obj);
			break;
		case "cantSave":
			alert(GQB.translate("cantSaveErrorMsg", _event.obj));
			break;
		case "saved":
			if (_event.obj.isFromDb) {
				GQB.view.showInfo(GQB.translate("savedPatChangesMsg"));
			} else {
				GQB.view.showInfo(GQB.translate("savedPatMsg"));
			}
			break;
		case "deleted":
			GQB.view.showInfo(GQB.translate("deletedPatFromDBMsg"));
			break;
		case "classReady":  // an RDF class has loaded all props and links:
			GQB.view.classIsReady(_event.obj);
			break;
		case "gotNumInstances":
			GQB.view.selectClass(_event.obj, false);  // select object but don't force
			break;
		case "removedClassFromPattern":
			// refresh the east panel, since numInstances will have changed:
			GQB.view.showClassProperties();
			break;
		case "addedRestriction":
		case "deletedRestriction":
		case "editRestriction":
			GQB.view.selectClass(_event.obj, true);  // force selection
			break;
		case "gotDBError": 
			GQB.view.showInfo(GQB.translate("virtuosoErrorMsg", _event.obj));
			break;
		case "gotResult": 
			GQB.view.showResult(_event.obj);
			break;
		default:
			GQB.view.showInfo(GQB.translate("controllerErrorMsg", _event.name));
			break;
	}
};