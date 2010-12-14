// Dllearner Action URI and SideWindow
var dlActionURI = owUriBase + "service/dllearner/";
var dlSideWindowID = "#Dllearner";

// container manager specific ready statements
$jq(document).ready(function() {

	/* initial refresh */
	/*
	if($jq('div.section-mainwindows').children('div.window').eq(0).length != 0){
		dlRefreshDllearner();
	}
	*/

	/* Internal CM Link Handling (with livequery events) */
	$jq("a.tab")
		.livequery('click', function(event) { dlChangeGUI(event); });
	// open DL-Learner GUI from the extras menu of the ontoWiki	
	$jq("a.dllOpen")
		.livequery('click', function(event) { dlChangeGUI(event); });
	
	$jq("a.dlRes")
		.livequery('mouseover', function(event) {
			hideHref($jq(this).parent());
			$jq(".contextmenu-enhanced .contextmenu").remove(); // remove all other menus
		})
		.livequery('click', function(event) { dlShowListMenu(event);} )
		.livequery('mouseout', function(event) {
			showHref($jq(this).parent());
		});

	$jq("a.addToLearner")
		.livequery('click', function(event) { dlMakeAction(event); });
	
    $jq("a.addToModelDef")
		.livequery('click', function(event) { dlMakeModelDef(event); });
		
	$jq("a.addToModelSub")
		.livequery('click', function(event) { dlMakeModelSub(event); });
	
	$jq("a.remove")
		.livequery('click', function(event) { dlMakeAction(event); });
	
	$jq("a.learnThis")
		.livequery('click', function(event) { dlLearn(event); });
		
	$jq("a.delProcess")
		.livequery('click', function(event) { dlDelProcess(event); });
	
	$jq('.property-add').click(function(event) {
		var table = $jq('#table' + $jq(this).attr('id').replace('property-add', '')).get(0);
		var row = table.insertRow(table.rows.length);
		$jq(row).addClass('editing');
		var c1 = row.insertCell(0);
		var c2 = row.insertCell(1);
		// c1.style.verticalAlign = 'top';
		$jq(c1).mouseover(function(event) {
			$jq(this).children('img').eq(0).css('visibility', '');
		});
		$jq(c1).mouseout(function(event) {
			$jq(this).children('img').eq(0).css('visibility', 'hidden');
		});
		// new Ajax.Updater(c1, owUriBase + 'service/getPropertySelector/?property=', {evalScripts: true});
		$jq(c1).load(owUriBase + 'service/getPropertySelector/');
	});
	/*$jq("a.formbutton")
		.livequery('click', function(event) { dlShowGUI (event); });*/
});

//on pressing the New-Process-Button the action learn this is called
function dlLearn ( event ) {
	var options = '';
	var p = '';
	var n = '';
	i=0;
	
	options += $jq("#reasoner").val()+"/";
	options += $jq("#lerningProblem").val()+"/";
	options += $jq("#lerningAlgorithm").val();
			
	while(i < $jq("#radio"+i).attr("size")*2){
	
	//alert($jq("#radio"+i).attr("value"));
		
		if($jq("#radio"+i).attr("checked") && $jq("#radio"+i).attr("value") == 1){
			//alert($jq("#radio"+i).attr("name"));
			//pos[i] = encodeURIComponent ($jq("#radio"+i).attr("name"));
			p += encodeURIComponent ($jq("#radio"+i).attr("name"))+"/";
		}
		else if ($jq("#radio"+i).attr("checked") && $jq("#radio"+i).attr("value") == 0){
			//alert($jq("#radio"+i).attr("name"));
			//neg[i] = encodeURIComponent ($jq("#radio"+i).attr("name"));
			n += encodeURIComponent ($jq("#radio"+i).attr("name"))+"/";
		}
		i++;
	}
	action = encodeURIComponent ($jq(event.currentTarget).attr("name"));
	// refresh the main
	replaceWindow(dlActionURI, "act="+action+"&neg="+n+"&pos="+p+"&opt="+options);
}

// called if changing tabs or open from the extras menu
function dlChangeGUI ( event ) {
	// look for the name and encode it for uri
	action = encodeURIComponent ($jq(event.currentTarget).attr("name"));
	// refresh the main
	replaceWindow(dlActionURI, "act="+action);
	//dlRefreshDllearner();
}

function dlMakeModelDef( event ) {
    var s='';
    //i=1;
	for(i =1; i<=6;i++){
	if ($jq("#radio"+i).attr("checked")){
	s += encodeURIComponent ($jq("#radio"+i).attr("value"));
	//alert(s);
	//i++;
	}
	}
	action = encodeURIComponent ($jq(event.currentTarget).attr("name"));
	replaceWindow(dlActionURI, "act="+action+"&s="+s);
}

function dlMakeModelSub( event ) {
    var s='';
    //i=1;
	for(i =1; i<=6;i++){
	if ($jq("#radio"+i).attr("checked")){
	s += encodeURIComponent ($jq("#radio"+i).attr("value"));
	//alert(s);
	//i++;
	}
	}
	action = encodeURIComponent ($jq(event.currentTarget).attr("name"));
	replaceWindow(dlActionURI, "act="+action+"&s="+s);
}

function dlRefreshDllearner ( parameters ) {

	if (typeof parameters == 'undefined') {
		parameters = "";
	}
	replaceWindow(dlActionURI, parameters);
}
// deleting the selected Process
function dlDelProcess ( event ) {
	action = encodeURIComponent ($jq(event.currentTarget).attr("name"));
	id = encodeURIComponent ($jq(event.currentTarget).attr("id"));
	replaceWindow(dlActionURI, "act="+action+"&id="+id);
}

// makes the usual actions with action name and container name
function dlMakeAction ( event ) {

	$jq(".contextmenu-enhanced .contextmenu").remove();
	// look for the name and encode it for uri
	action = encodeURIComponent ($jq(event.currentTarget).attr("name"));
	if($jq(event.currentTarget).attr("id")){
		name = encodeURIComponent ($jq(event.currentTarget).attr("id"));
	}
	else{
		name = $jq("#sel").val();
	}
	// refresh the main
	replaceWindow(dlActionURI, "act="+action+"&name="+name);
}

function dlShowListMenu(event) {
	// remove all other menus
	$jq(".contextmenu-enhanced .contextmenu").remove();

	// generate coordinates for context menu
	menuX = event.pageX - 30;
	menuY = event.pageY - 20;
	menuId = "windowmenu-"+menuX+"-"+menuY;

	//encodedName = encodeURIComponent( $jq(event.target).parent().children("a.cmResName").html() );
	encodedName = $jq(event.currentTarget).attr("name");
	//encodedId = encodeURIComponent( $jq(event.currentTarget).attr("id"));

	// create the plain menu with correct style and position
	$jq(".contextmenu-enhanced").append("<div class='contextmenu' id='"+menuId+"'></div>");
	$jq("#"+menuId)
		.attr({ style: "z-index: "+menuZIndex+"; top: "+menuY+"px; left: "+menuX+"px;" })
		.click( function(ev){ ev.stopPropagation(); });

	// create the menu content
	$jq('#'+menuId).append(
		"<ul>"+
		"<b>Positive Examples:</b><br> "+encodedName+
		"For more Information please visit <a href='http://dl-learner.org/Projects/DLLearner'><b>DL-Learner Homepage</b></a>"+
		"</ul>");

	// fade in the menu and stop the even propagation)
	$jq('#'+menuId).show();
	event.stopPropagation();
}