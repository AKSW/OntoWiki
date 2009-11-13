var filterboxcounter = filtersFromSession.length; // dont overwrite previous filters

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
        var value = $("#addwindow #value").val();
        var type = "literal";
         var typedata = null;
        if(value==""){
            value = $("#addwindow #possiblevalues option:selected").attr("value");
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

        filter.add("filterbox"+filterboxcounter, prop, inverse, propLabel, filtertype, value, null, type, typedata, function(newfilter) {
            //react in filter box
            filterboxcounter++;
            //$("#addwindow").hide();
        }, false);
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
    //$.dump(filter);
    //register the filter box for (other) filter events
    //filter.addCallback(function(newfilter){ showFilter() });

    $('.filter .delete').click(function(){
        filter.remove($(this).parent().attr('id'));
    })
});

