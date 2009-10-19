/*
 * ContainerManager Plugin Javascript Component(s)
 * Version: $Id$
 */

// ContainerManager Action URI and SideWindow
var cmActionURI = urlBase + "containermanager/manage/";
var cmSideWindowID = "#ContainerManager";

// container manager specific ready statements
$(document).ready(function() {

	/* initial refresh */
	if ($("#ContainerManager").length != 0) {
		//alert($("#ContainerManager").length);
		cmRefreshContainerManager();
	}	

	/* Container Context Menus (with livequery events) */
	$(cmSideWindowID+" .cmContainer span.button")
		.livequery('mouseover', function(event) {
			//hideHref($(this).parent());
			$(".contextmenu-enhanced .contextmenu").remove(); // remove all other menus
		})
		.livequery('click', function(event) { cmShowContainerMenu(event);} )
		.livequery('mouseout', function(event) {
			showHref($(this).parent());
		});
	$("a.cmContainer")
		.livequery('click', function(event) { cmListContainer(event); });
		
	$("a.cmRes")
		.livequery('mouseover', function(event) {
			hideHref($(this).parent());
			$(".contextmenu-enhanced .contextmenu").remove(); // remove all other menus
		})
		.livequery('click', function(event) { cmShowListMenu(event);} )
		.livequery('mouseout', function(event) {
			showHref($(this).parent());
		});

	/* Internal CM Link Handling (with livequery events) */
	$("a.cmDeleteResources")
		.livequery('click', function(event) { cmDeleteResources(event); });
	
	$("a.cmDelRes")
		.livequery('click', function(event) { cmDeleteResource(event); });
	$("a.cmList")
		.livequery('click', function(event) { cmListContainer(event); });
	$("a.cmDel")
		.livequery('click', function(event) { cmDeleteContainer(event); });
	$("a.cmEmpty")
		.livequery('click', function(event) { cmEmptyContainer(event); });
	$("a.cmLearnClass")
		.livequery('click', function(event) { cmLearnClass(event); });
	
	$("#cmName-input")
		.livequery("keypress", function(event) { cmCreateContainer(event); });
	$("#cmName-input")
		.livequery( function(event) { enhanceInput(this); });
		
	$("#cmRes-input")
		.livequery("keypress", function(event) { cmCreateResource(event); });
	$("#cmRes-input")
		.livequery( function(event) { enhanceInput(this); });

	$("#cmMod-input")
		.livequery("keypress", function(event) { cmCreateResource(event); });	
	$("#cmMod-input")
		.livequery( function(event) { enhanceInput(this); });
	
	$("#cmTitle-input")
		.livequery("keypress", function(event) { cmCreateResource(event); });
	$("#cmTitle-input")
		.livequery( function(event) { enhanceInput(this); });
	
	$("a.pager")
		.livequery('click', function(event) { cmListContainer(event); });
			
	// all named resources are draggable and get the move cursor
	//.addClass("cursor-move") (ToDo: nur onclick)
        /* todo: reanimate d&d with new jquery UI
	$(".Resource")
		.Draggable({ zIndex: dragZIndex, ghosting: true,
			revert: true, containment: 'document', autoSize: true });
                */
});

/*
 * todo: neue urlbase
 */
function cmLearnClass ( event ) {
	$(".contextmenu-enhanced .contextmenu").remove();
	cName = encodeURIComponent ($(event.currentTarget).attr("name"));
	replaceWindow(owUriBase+"service/dllearner", "act=learnClass&name="+cName );
}

// This function for a link event lists a container resources by refreshing the main
function cmListContainer ( event ) {
	// delete (all) context menus 
	$(".contextmenu-enhanced .contextmenu").remove();
	// look for the name and encode it for uri
	cName = encodeURIComponent ($(event.currentTarget).attr("name"));
	p = encodeURIComponent ($(event.currentTarget).attr("id"));
	// refresh the main
	replaceWindow(cmActionURI, "act=list&name="+cName+"&p="+p );
}

function cmCreateResource(event) {
	// do not create until user pressed enter
	if (event.which == 13) {
		// look for the name and encode it for uri
		cName = encodeURIComponent ($(event.currentTarget).attr("name"));
		rName = encodeURIComponent ($("#cmRes-input").attr("value"));
		mName = encodeURIComponent ($("#cmMod-input").attr("value"));
		rTitle = encodeURIComponent ($("#cmTitle-input").attr("value"));
		// do the action and refresh the sidewindow
		var re = new RegExp("%20");
		var hit = re.exec(mName);
		if(mName != "undefined" && !hit){
			replaceWindow(cmActionURI, "act=addr&name="+cName+"&r="+rName+"&m="+mName+"&t="+rTitle, null,
			function() { cmRefreshContainerManager ();});
		}
		else{
			replaceWindow(cmActionURI, "act=addr&name="+cName+"&r="+rName+"&t="+rTitle, null,
				function() { cmRefreshContainerManager ();});
		}
		//cmRefreshContainerManager ();
	}
}
function cmDeleteResource(event) {
	// delete (all) context menus 
	$(".contextmenu-enhanced .contextmenu").remove();
	// look for the name and encode it for uri
	cName = encodeURIComponent ($(event.currentTarget).attr("name"));
	entry = encodeURIComponent ($(event.currentTarget).attr("id"));
	
	replaceWindow(cmActionURI, "act=delrN&name="+cName+"&entry="+entry, null,
		function() { cmRefreshContainerManager ();} );
	//cmRefreshContainerManager ();
}

function cmDeleteResources(event) {
	// delete (all) context menus 
	$(".contextmenu-enhanced .contextmenu").remove();
	// look for the name and encode it for uri
	cName = encodeURIComponent ($(event.currentTarget).attr("name"));
	cmRefreshContainerManager( "act=delResources&name="+cName );
	//cmRefreshContainerManager ();
}

// This function is for an input keypress event to create a Container (+refresh)
function cmCreateContainer (event) {
	// do not create until user pressed enter
	if (event.which == 13) {
		// look for the name and encode it for uri
		cName = encodeURIComponent ( event.currentTarget.value );
		// do the action and refresh the sidewindow
		cmRefreshContainerManager ( "act=add&name="+cName );
	}
}

// This function for a link event deletes a container and refreshes the cm sidewindow
function cmDeleteContainer ( event ) {
	// delete (all) context menus 
	$(".contextmenu-enhanced .contextmenu").remove();
	// look for the name and encode it for uri
	cName = encodeURIComponent ($(event.currentTarget).attr("name"));
	// do the action and refresh the sidewindow
	cmRefreshContainerManager ( "act=del&name="+cName );
	
}

function cmEmptyContainer(event) {
	// delete (all) context menus 
	$(".contextmenu-enhanced .contextmenu").remove();
	// look for the name and encode it for uri
	cName = encodeURIComponent ($(event.currentTarget).attr("name"));
	// do the action and refresh the sidewindow
	cmRefreshContainerManager ( "act=empty&name="+cName );
}

// This function for a drag&drop event
// adds a resource to container and refreshes the cm sidewindow
function cmAddResourceByDrop ( event, item ) {
	// do the action and refresh the sidewindow
	cName = encodeURIComponent ($(item).attr("name"));
	cLabel = encodeURIComponent ( $(event).text() );
	cResource = encodeURIComponent ( $(event).attr("about") );
	cmRefreshContainerManager ( "act=addrN&name="+cName+"&r="+cResource+"&t="+cLabel);
	//cmRefreshContainerManager ();
}

// This function refeshes the container box (optional with some parameters)
function cmRefreshContainerManager ( parameters ) {

	if (typeof parameters == 'undefined') {
		parameters = "";
	}

	// refresh the sidewindow
	replaceWindow(cmActionURI, parameters, $(cmSideWindowID),
		function() { cmAfterRefreshContainerManager();});
}

// This function is fired after the sidewindow replacement
// and makes the container places droppable
function cmAfterRefreshContainerManager () {
	// make the new container links droppable for all resources
	$(".cmContainer")
		.Droppable({
			accept: 'Resource', activeclass: 'dropactive', hoverclass: 'drophover',
			tolerance: 'pointer', ondrop: function (drag) { cmAddResourceByDrop (drag, this); }});
}

// This function displays a contextmenu of a container (to delete/list them)
function cmShowContainerMenu(event) {
	// remove all other menus
	$(".contextmenu-enhanced .contextmenu").remove();

	// generate coordinates for context menu
	menuX = event.pageX - 30;
	menuY = event.pageY - 20;
	menuId = "windowmenu-"+menuX+"-"+menuY;

	encodedName = encodeURIComponent( $(event.target).parent().children("span.cmName").html() );

	// create the plain menu with correct style and position
	$(".contextmenu-enhanced").append("<div class='contextmenu' id='"+menuId+"'></div>");
	$("#"+menuId)
		.attr({ style: "z-index: "+menuZIndex+"; top: "+menuY+"px; left: "+menuX+"px;" })
		.click( function(ev){ ev.stopPropagation(); });

	// create the menu content
	$('#'+menuId).append(
		"<ul>"+
		"<li><a class='cmList' name='"+encodedName+"'><strong>List / Edit Container Content</strong></a></li>"+
		"<li><a class='cmDel' name='"+encodedName + "'>Delete Container</a></li>"+
		"<li><a class='cmEmpty' name='"+encodedName + "'>Empty Container</a></li>"+
		//"<li><a class='cmLearnClass' name='"+encodedName + "'>Learn Class from Container</a></li>"+
		"<hr />"+
		"<li><a href='" +cmActionURI+ "?act=export&type=XML&name=" +encodedName+ "'>Export Resources as RDF/XML</a></li>"+
		"<li><a class='cmDeleteResources' name='"+encodedName + "'>Delete Resources from Model</a></li>"+
		"</ul>");

	// fade in the menu and stop the even propagation)
	$('#'+menuId).show();
	event.stopPropagation();
}

function cmShowListMenu(event) {
	// remove all other menus
	$(".contextmenu-enhanced .contextmenu").remove();

	// generate coordinates for context menu
	menuX = event.pageX + 7;
	menuY = event.pageY - 20;
	menuId = "windowmenu-"+menuX+"-"+menuY;

	//encodedName = encodeURIComponent( $(event.target).parent().children("a.cmResName").html() );
	encodedName = encodeURIComponent( $(event.currentTarget).attr("name"));
	encodedId = encodeURIComponent( $(event.currentTarget).attr("id"));

	// create the plain menu with correct style and position
	$(".contextmenu-enhanced").append("<div class='contextmenu' id='"+menuId+"'></div>");
	$("#"+menuId)
		.attr({ style: "z-index: "+menuZIndex+"; top: "+menuY+"px; left: "+menuX+"px;" })
		.click( function(ev){ ev.stopPropagation(); });

	// create the menu content
	$('#'+menuId).append(
		"<ul>"+
		"<li><a class='cmDelRes' id='"+encodedId+"' name='"+encodedName + "'>Delete Resource</a></li>"+
		"</ul>");

	// fade in the menu and stop the even propagation)
	$('#'+menuId).show();
	event.stopPropagation();
}

