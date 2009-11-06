/**
 * @class
 */
function FilterAPI(){
	/*
         * @var
         */
        this.uri = urlBase+"resource/instances/";

        /*
         *@var array
         */
        this.callbacks = new Array();
	
        /**
         * @var array
         */
        this.filters = filtersFromSession;

        /**
         *@method
         *
         */
	this.addCallback = function(callback){
		if(typeof callback == 'function' || typeof callback == 'object')
			this.callbacks.push(callback);
	};
	/**
         * add a filter
         * @method
         * @param id int,string
         * @param property string an iri (predicate) which values should be filtered
         * @param isInverse boolean if the property is inverse
         * @param propertyLabel string a label for the property (will be displayed instead)
         * @param filter string can be "contains" or "equals" . going to be enhanced
         * @param value1 mixed the value applied to the filter
         * @param value2 mixed the value applied to the filter. often optional (used for "between")
         * @param valuetype string may be "uri" or "literal" or "typedliteral" or "langtaggedliteral"
         * @param literaltype string if valuetype is "typedliteral" or "langtaggedliteral": you can put stuff like "de" or "xsd:int" here...
         * @param callback function will be called on success
         * @param hidden boolean will not show up in filterbox if true
         */
	this.add = function(id, property, isInverse, propertyLabel, filter, value1, value2, valuetype, literaltype, callback, hidden){
            if(typeof callback != 'function' && typeof callback != 'object')
		callback = function(){};
                
		var data =
                    {
                    filter:
                        [
                            {
                                "action" : "add",
                                "id" : id,
                                "property" : property,
                                "isInverse" : typeof isInverse != 'undefined' ? isInverse : false,
                                "propertyLabel" : typeof propertyLabel != 'undefined' ? propertyLabel : null,
                                "filter" : filter,
                                "value1": value1,
                                "value2": typeof value2 != 'undefined' ? value2 : null,
                                "valuetype": typeof valuetype != 'undefined' ? valuetype : null,
                                "literaltype" : typeof literaltype != 'undefined' ? literaltype : null,
                                "hidden" : typeof hidden != 'undefined' ? hidden : false
                            }
                        ]
                };

		var dataserialized = $.toJSON(data);
		
		me = this;
		$.post(
		this.uri,
		{
                    "instancesconfig" : dataserialized
                }, //as post because of size
		function(res) {
                    if(true){ //how to check for success
                        //remember
                        me.filters[id] = data;

                        //do default action
                        me.reloadInstances();

                        //do caller specific action
                        callback(me.filters);

                        //inform others
                        for(key in me.callbacks){
                                me.callbacks[key](me.filters);
                        }
                    } else alert("Could not add filter!\nGot error from service/session: \n"+res);
		}, 
		"text");
	};
	
	this.reloadInstances = function(){
            //$('.content .innercontent').load(document.URL);
            window.location = urlBase+"list/r/"+encodeURI(classUri);
            //$.dump(this);
	};

        this.filterExists = function(id){
            //alert(id+" : "+typeof this.filters[id]); $.dump(this);
            return (typeof this.filters[id] != 'undefined');
        }

        this.getFilterById = function(id){
            return this.filters[id];
        }

	
	this.remove = function(id, callback){
            if(typeof callback != 'function' && typeof callback != 'object')
                callback = function(){};

            var data = {
                filter: [
                    {
                        "action" : "remove",
                        "id" : id
                    }
                ]
            };

            var dataserialized = $.toJSON(data);

            me = this;
            $.post(
		this.uri,
		{
                    "instancesconfig" : dataserialized
                }, //as post because of size
                function(res) {
                    if(true){
                        //unset
                        delete me.filters[id];
                        //do default action
                        me.reloadInstances();

                        //do caller specific action
                        callback(me.filters);

                        //inform others
                        for(key in me.callbacks){
                                me.callbacks[key](me.filters);
                        }
                    } else alert("Could not remove filter with id "+id+"!\nReason: \n"+res);
                }
            );
	};
	
	this.removeAll = function(callback){
            if(typeof callback != 'function' && typeof callback != 'object')
                    callback = function(){};
            me = this;
            $.get(this.uri+"&method=unsetArray", function(res) {
                if(res==""){
                    //do default action
                    me.reloadInstances();

                    //forget
                    me.filters = new Array();

                    //do caller specific action
                    callback(me.filters);

                    //inform others
                    for(key in me.callbacks){
                            me.callbacks[key](me.filters);
                    }
                 } else alert("Could not remove all filters!\nReason: \n"+res);
            });
	};
}

var filter = new FilterAPI();
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

