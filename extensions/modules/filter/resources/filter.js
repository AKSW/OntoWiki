var filterboxcounter = 0; // dont overwrite previous filters
for(onefilter in filtersFromSession){
    filterboxcounter++;
}

function showAddFilterBox(){
    // $("#addFilterWindowOverlay").show();
    $("#addFilterWindowOverlay").modal({
        overlay: 80,
        overlayCss: {backgroundColor: '#000'}, 
        overlayClose: true, 
        onOpen: function (dialog) {
        	dialog.overlay.fadeIn(effectTime, function () {
                dialog.data.show();
        		dialog.container.fadeIn(effectTime);
        	})
    	}, 
    	onClose: function (dialog) {
    	    dialog.container.fadeOut(effectTime, function() {
    	        dialog.overlay.fadeOut(effectTime, function() {
    	            $.modal.close();
    	        });
    	    });
    	}
    });
}

function removeAllFilters(){
    // $("#addFilterWindowOverlay").hide();
    $.modal.close();
    filter.removeAll(function() {

    });
}

$(document).ready(function(){
    //initial layout
    $("#gqbclassrestrictionsexist").hide();
    $("#addFilterWindowOverlay").hide();
    $("#filterbox #clear").hide();

    //move them up so everything is overlayed
    // $("body").append($("#addFilterWindowOverlay"));
    // $("#addFilterWindowOverlay").height($('html').height()+50);
    // $("#addFilterWindowOverlay").width($('html').width());

    // $("#addwindow").css("min-height", 250);
    // $("#addwindow").width(600);
    // $("#addwindow").css("top", 50);
    // $("#addwindow").css("left", 200);


    $("#addwindowhide").click(function(){
        // $("#addFilterWindowOverlay").hide();
        $.modal.close();
    });

    $("#addwindow #add").click( function(){
        // $("#addFilterWindowOverlay").hide();
        $.modal.close();

        var prop = $("#addwindow #property option:selected").attr("about");
        var propLabel = $("#addwindow #property option:selected").html();
        var inverse = $("#addwindow #property option:selected").hasClass("InverseProperty");

        var filtertype = $("#addwindow #resttype option:selected").html();
        var negate = $("#negate").is(':checked');
        var value1 = $("#addwindow #value1").val();
        if(typeof value1 == "undefined"){
            value1 = null;
        }

        var value2 = $("#addwindow #value2").val();
        if(typeof value2 == "undefined"){
            value2 = null;
        }

        var type = "literal";
        var typedata = null;
        
        // if value entering is possible but nothing entered: check if user selected something in the possible values box
        if(value1 == "" && $("#valueboxes").children().length == 1){
            if($("#addwindow #possiblevalues option:selected").length == 0){
                return; // block add button
            }
            value1 = $("#addwindow #possiblevalues option:selected").attr("value");
            filtertype = "equals";
            type = $("#addwindow #possiblevalues option:selected").attr("type");
            var language = $("#addwindow #possiblevalues option:selected").attr("language");
            var datatype = $("#addwindow #possiblevalues option:selected").attr("datatype");

            if(type == "literal" && typeof language != 'undefined'){
                typedata = language;
            } else if(type == "typed-literal"){
                typedata = datatype;
            }
        }

        filter.add("filterbox"+filterboxcounter, prop, inverse, propLabel, filtertype, value1, value2, type, typedata, function(newfilter) {
            //react in filter box
            filterboxcounter++;
            //$("#addwindow").hide();
        }, false, negate);
    });

    //show possible values for select property
    $("#property").change(function () {
      $("#addwindow #possiblevalues").addClass("is-processing");
      $("#property option:selected").each(function () {
            var inverse = $(this).hasClass("InverseProperty") ? "true" : "false";
            $("#possiblevalues").load(urlBase+"filter/getpossiblevalues?predicate="+escape($(this).attr("about"))+"&inverse="+inverse, {}, function(){
                 $("#addwindow #possiblevalues").removeClass("is-processing");
            });
          });
    });
    
    //different filter types need different value input fields
    // bound: none
    // contains, larger, smaller: one
    // between: two - not implemented
    // date: datepicker - not implemented
    $("#resttype").change(function () {
      var type = $("#resttype option:selected").val();
      if(type == "contains" || type == "larger" || type == "smaller"){
          if($("#valueboxes").children().length != 1){
              $("#valueboxes").empty();
              $("#valueboxes").append("<input type=\"text\" id=\"value1\"/>");
          }
      }
      if(type == "between"){
          if($("#valueboxes").children().length != 2){
              $("#valueboxes").empty();
              $("#valueboxes").append("<input type=\"text\" id=\"value1\"/>");
              $("#valueboxes").append("<input type=\"text\" id=\"value2\"/>");
        }
      }
      if(type == "bound"){
          if($("#valueboxes").children().length != 0){
              $("#valueboxes").empty();
          }
      }
    });
    
    //$.dump(filter);
    //register the filter box for (other) filter events
    //filter.addCallback(function(newfilter){ showFilter() });

    $('.filter .delete').click(function(){
        filter.remove($(this).parents('.filter').attr('id'));
    })
});

