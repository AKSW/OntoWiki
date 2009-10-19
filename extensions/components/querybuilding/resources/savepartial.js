$(document).ready(function() {
	$("#savequerybutton").click(function(){
		var box = $("#savebox");
		if(box.hasClass("querybuilder")){
			$.get(urlBase+"querybuilder/updatesparql", {json: $('#hidden_json').val() , limit: $("#limit").val()}, function(query){
				$.ajax({
				  url: urlBase + "querybuilding/savequery",
				  type: "POST",
			      data: ({
					json: $('#hidden_json').val(),
					name: $('#qname').val(),
					qdesc: $('#qdesc').val(),
					"query": query,
					generator: "qb",
					share: $("#savequerysharecheckbox").is(':checked') ? "true" : "false"
				  }),
			      dataType: "text",
			      success: function(msg){
			         //TODO check for status
					 if(msg != "All OK")
					    alert("Fehler "+msg);
					 //open(urlBase + "querybuilding/listquery");
			      }
				});

			});
						
		} else if(box.hasClass("graphicalquerybuilder")){
			if (!GQB.view.selectedViewClass) { alert(GQB.translate("noPatternSelMsg")); return; }
			var modelPattern = GQB.view.selectedViewClass.parentViewPattern.modelPattern;
			if (!modelPattern) return;  // sollte nicht passieren, ist schwerer Fehler
			modelPattern.name = $('#qname').val();
			modelPattern.description= $('#qdesc').val();
			modelPattern.save();
	
		} else if(box.hasClass("queryeditor")){
			$.ajax({
				  url: urlBase + "querybuilding/savequery",
				  type: "POST",
			      data: ({
					json: "",
					name: $('#qname').val(),
					qdesc: $('#qdesc').val(),
					"query": $(".code-input[name=query]").val(),
					generator: "qe",
					share: $("#savequerysharecheckbox").is(':checked') ? "true" : "false"
				  }),
			      dataType: "text",
			      success: function(msg){
			         //TODO check for status
					 if (msg != "All OK") {
					 	alert("Fehler " + msg);
					 } else {
					 	$('.innercontent').prepend("<p class=\"messagebox info\" id=\"savequerynotification\">The Query was saved</p>");
						
						setTimeout(function (){
							$("#savequerynotification").remove();
						}, 5000);
					 }
					 //open(urlBase + "querybuilding/listquery");
			      }
				});
		} else {
			alert("error: dont know which builder this is");
		}
		
		
	
	});
	$(".inner-label").innerLabel();
});