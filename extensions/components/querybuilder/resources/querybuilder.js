// event assertion and onReady  stuff
$(document).ready(function() {

  $('#updateresults').click(function(){
    updateResults();
    return false;
  });

  $('#reset').click(function(){
    alert(resetLink);
    return false;
  });

  $('.triplepattern > td > input') .livequery('keydown', function(event) {
   	if(event.keyCode == 13) {
   		updateResults();
   	}
    return event;
  });
  
  $('.triplepattern > td > input') .livequery('focus', function(event) {
  		id = $(this).parents(".triplepattern").attr('id');
  		qb_js_tripleinfo[id]['search'] = $(this).attr("name");
		return event;
	});
	
  $('.triplepattern > td > input[name="o"]') .livequery('keyup', function(event) {
  		//~ a = $(this).val();
  		//~ $(this).val(a+"aaa");
  		if($(this).val()==""){
  			id = $(this).parents(".triplepattern").attr('id');
  			resetOValues(id)
  			
  			}
  			return event;
  	
	});
	
  
  $('.triplepattern > td > input') .livequery('blur', function(event) {
  		//id = $(this).parents(".triplepattern").attr('id');
  		id = getIDforParent(this);
  		delete qb_js_tripleinfo[id]['search'];
	});
  
  $('#limit') .livequery('change', function(event) {
    updateResults();
    return false;
  });
  
   $('.qb-addtp') .livequery('click', function(event) {
   //	id = $(this).parents(".triplepattern").attr('id');
   	id = getIDforParent(this);
   	addPattern(id);
   	updateResults();
    return false;
  });

  $('.qb-deltp') .livequery('click', function(event) {
  	// id = $(this).parents(".triplepattern").attr('id');
  	 id = getIDforParent(this);
  	 var l = $(".triplepattern").length;
    if(l<2){}
	else {
	 	deletePattern(id);
		updateResults();
		}
    return false;
  });

  //TODO not working
  /*$('#showquerytextarea') .livequery('click', function(event) {
  		updateSPARQLQuery();
  		return event;
	});*/
	

  $('#savequerybutton').mouseover(function() {
    var element = "none";
    var json = jsonPatterns(element);
    $('#hidden_json').val(json);
  });
/*
	$('#savequerybutton').unbind('click');
    $('#savequerybutton').click(function() {
        	 // submit all forms inside this submit button's parent window
        $(this).parents('.window').eq(0).find('form').each(function() {
            if ($(this).hasClass('ajaxForm')) {
                // submit asynchronously
                var actionUrl = $(this).attr('action');
                var method    = $(this).attr('method');
                var data      = $(document.forms[$(this).attr('name')]).serialize();
                
                if ($(this).hasClass('reloadOnSuccess')) {
                    var mainContent = $(this).parents('.content.has-innerwindows').eq(0).children('.innercontent');
                    var onSuccess = function() {
                        mainContent.load(document.URL);
                    }
                }
                // alert(data);
                if (method == 'post') {
                    $.post(actionUrl, data, onSuccess);
                } else {
                    $.get(actionUrl, data, onSuccess);
                }
                document.forms[$(this).attr('name')].reset()
            } else {
                // submit normally
                document.forms[$(this).attr('name')].submit();
            }
        })
    	
       
    });*/
  
   updateResults();
   //initPatterns(".pattern");
	// $(this).tablesorter({sortList:[[0,0]], widgets: ["zebra"]});
	
	 initPatterns('.triplepattern > td > input');
});

function resetOValues(id){
		
  		delete qb_js_tripleinfo[id]['otype'];
  		delete	qb_js_tripleinfo[id]['datatype'];
  		delete	qb_js_tripleinfo[id]['lang'];
	
	}


function addPattern(id){
	//test = $('#'+id+'.triplepattern');
	newid = getNextID();
	//$('#'+id+'.triplepattern').clone().appendTo(".separated-vertical");
	//clone
	original = $('#'+id+'.triplepattern');
	var s =  original.children('td').children("input[name='s']");
	var p =  original.children('td').children("input[name='p']");
	var o =  original.children('td').children("input[name='o']");
	
	
	qb_js_tripleinfo['qb_triple_'+newid]=eval(uneval(qb_js_tripleinfo[id]));
	
	clone = original.clone();
	//TODO change attributes
	
	//INSERT 
	clone.attr('id', 'qb_triple_'+newid)
		.insertAfter('#'+id+'.triplepattern');
	
	initPatterns('#qb_triple_'+newid+' > td > input');
	return false;
	
	//*TODO CODE BELOW needs to be adapted**//
	/*var n = parent.childNodes;
	for (var x = 0;x<n.length;x++){
		//alert(n[x]);
		}
	//alert(n.length);
	//n.each(function(i){
		//alert(n[i]);
		//});
	
	
	
	//$("#gp div:last").clone().prependTo("#gp div:last");
	$("#gp div:last").clone().appendTo("#gp");
	String.prototype.startsWith = function(s) { return this.indexOf(s)==0; }
	if(!($("#gp div:last  > input[name='s']").val().startsWith("?"))){
		$("#gp div:last  > input[name='s']").val("?newSubject");
		};
	if(!($("#gp div:last  > input[name='p']").val().startsWith("?"))){
		$("#gp div:last  > input[name='p']").val("?newPredicate");
		};
	if(!($("#gp div:last  > input[name='o']").val().startsWith("?"))){
		$("#gp div:last  > input[name='o']").val("?newObject");
		$("#gp div:last  > input[name='o']").attr("otype","");
		$("#gp div:last  > input[name='o']").attr("lang","");
		$("#gp div:last  > input[name='o']").attr("datatype","");
		};
	
	updateResults();
	var url = "autocompletion.php";
	var autocompOptions = getAutocompleteOptions();
	
	$("#gp div:last >input").autocomplete(url, autocompOptions);
	
		
	initPatterns("#gp div:last >input");
	
	*/
	
	}
	
function initPatterns(jqselect){
	
		var url = urlBase+"querybuilder/autocomplete";
		var autocompOptions = getAutocompleteOptions();
		 $(jqselect).autocomplete(url, autocompOptions);
	
		$(jqselect).result(function(event, data, formatted) {
			if(data){
				var value = data[1];
				var type = 	data[2];
				var lang = 	data[3];
				var datatype = 	data[4];
				
				id = getIDforParent(this);
				 
				$(this).val(value);
				
				if( $(this).attr("name") == "o"){
					//var tmp = qb_js_tripleinfo[id];
					//tmp["otype"] = type;
					//alert(id);
					qb_js_tripleinfo[id]["otype"] = type;
					qb_js_tripleinfo[id]["lang"] = lang;
					qb_js_tripleinfo[id]["datatype"] = datatype;
					
					}
				}
			updateResults();
	});
}

function getIDforParent(node){
	id = $(node).parents(".triplepattern").attr('id');
	return id;
	}

function deletePattern(id){
	 	//$("#"+id+".triplepattern").remove();
	 	$("#"+id).remove();
	 	delete qb_js_tripleinfo[id];
	}


function getAutocompleteOptions(){
		var autocompOptions = {
		max: 50,
    	minChars: 0,
    	selectFirst: false,
    	scrollHeight: 360,
    	matchContains: true,
    	width:400,
    	delay:1000,
		/*should not be used as queries can be started
		from different input windows. Therefore a cache and subsets are
		contraproductive*/
		matchSubset: false,
		cacheLength: 0,
		extraParams: {
   			json: function() { return jsonPatterns($(this));}
   			
   		}//end extraparam
		};
		
		return autocompOptions;
	
	}


	
	/*
	
function initPatterns(jqselect){
	
	
	$(jqselect).focus(function () {
        $(this).attr("id", "current" );
    });

	 $(jqselect).blur(function () {
        $(this).attr("id", "" );
     });
    
  	$(jqselect).keydown(function(){
     	  var json = jsonPatterns($(this));
       	  var limit = $("#limit").val();
       	   $('#autocompletionquery').attr('value', json);
       	  $.get("AjaxGetAutocompletionQuery.php", {func : "getAutocompletionQuery", json: json , limit: limit}, getAutocompletionQuery_cb);
       	  //x_getAutocompletionQuery(json,limit, getAutocompletionQuery_cb);
       	  displayQuery();
     });
    
     
     $(jqselect).result(function(event, data, formatted) {
     	if(data){
   			var value = data[1];
   			var type = 	data[2];
   			var lang = 	data[3];
   			var datatype = 	data[4];
   			$(this).val(value);
   			
			if( $(this).attr("name") == "o"){
				$(this).attr("otype",type);
				//alert($(this).attr("tmp"));
				$(this).attr("lang",lang);
				$(this).attr("datatype", datatype);
				}
			
			
   			}
   		
	});
	$(jqselect).keyup(function(event){
  			if(event.keyCode == 13){
  				  $("#updateresult").click();
 	 		}
	});
}
*/

 function getAutocompletionQuery_cb(z){
   	//alert(z);
   		//$('#autocompletionquery').attr('value', z );
	};

	
	function updateSPARQLQuery(){
		var limit = $("#limit").val();
		var element = "none";
		var json = jsonPatterns(element);
		//x_getSPARQLQuery(json,limit, displayQuery_cb);
		$.get(urlBase+"querybuilder/updatesparql", {json: json , limit: limit}, updateSPARQLQuery_cb);
	};

	function updateSPARQLQuery_cb(z){
		$('#showquerytextarea').attr('value',z);
	}
	
	function updateResults(){  
		$("#resulttable").empty();
		var limit = $("#limit").val();
		var element = "none";
		var json = jsonPatterns(element);

        // setting hidden input to be able to save queries
        $('#hidden_query').val(json);

		//alert(json);
		//$("#updating").attr("style", "display:all;");
		//x_getResults(json,limit, updateResults_cb);
		//$.get("AjaxUpdateTable.php", {json: json , limit: limit}, updateResults_cb);
		//alert(urlBase);
		//updateSPARQLQuery();
		$.get(urlBase+"querybuilder/updatetable", {json: json , limit: limit}, updateResults_cb);
	};
	
	
	function updateResults_cb(data){

        // Prepare to show results in second window
        if ($('#qbresulttable').length > 0) {
            $('#qbresulttable').replaceWith(data);
        } else {
            $('.active-tab-content').append(data);
        }

        // Making table sortable
        //TODO Stylesheets for tablesorter
        $('#qbresulttable').tablesorter();
	}
	
	//element should only be set for autocompletion
	function jsonPatterns(element){
		var tr = $(".triplepattern");
		var patterns = new Object();
		tr.each(function(i) {
			currentid = tr[i].id;
			current = $('#'+currentid+'.triplepattern');
            //alert(current.html());
			patterns[currentid] = new Object();
			//input = current.children('td').children('input');
			//ddd
			//alert(input);
			//alert(tr[i]);
			//var current = $("#gp  div:eq("+i+")");
			
			var s =  current.children('td').children("input[name='s']");
			var p =  current.children('td').children("input[name='p']");
			var o =  current.children('td').children("input[name='o']");
			patterns[currentid]["s"] =s.val();
			patterns[currentid]["p"] =p.val();
			patterns[currentid]["o"] = o.val();
			
			//TODO this could coause problems, when converting to json
			if(qb_js_tripleinfo[currentid]['otype']!=null){
				patterns[currentid]["otype"] = qb_js_tripleinfo[currentid]['otype'];
			}
			if(qb_js_tripleinfo[currentid]['lang']!=null){
				patterns[currentid]["lang"] = qb_js_tripleinfo[currentid]['lang']; 
			}
			if(qb_js_tripleinfo[currentid]['datatype']!=null){
				patterns[currentid]["datatype"] = qb_js_tripleinfo[currentid]['datatype'];
			}
			
			if(qb_js_tripleinfo[currentid]['search']!=null){
				patterns[currentid]['search'] = qb_js_tripleinfo[currentid]['search'];
			}
			
			//var test =  s.attr("id");
			/*element.name 
			
			if( s.attr("id")=="current"){patterns[i]["current"]="s";}
			else if(p.attr("id")=="current"){patterns[i]["current"]="p";}
			else if(o.attr("id")=="current"){
					patterns[i]["current"]="o";
				}*/
			
			});
			var result = $.toJSON(patterns); 
			//var result = $.toJSON(qb_js_tripleinfo); 
		return result;
		}

function toggleDebugCode () {
  $('#debugquery').toggle();
}

function toggleSparqlCode () {
  updateSPARQLQuery();
  $('#showquery').toggle();
  
}
