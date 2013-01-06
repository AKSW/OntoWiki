/*
  turn a div or checkbox into a sliding toggle switch (like the apple slide to unlock)
*/

/*# AVOID COLLISIONS #*/
if(window.jQuery) (function($){
/*# AVOID COLLISIONS #*/

	// plugin initialization
	$.fn.togglebutton = function(options){
                
		// Initialize options for this call
		var options = $.extend(
			{}/* new object */,
			$.fn.togglebutton.options/* default options */,
			options || {} /* just-in-time options */
		);


                function enable(button, withCallback){
                    var realChange = false;
                    if(button.prop("selected") != "true"){
                        realChange = true;
                    }
                    button.prop("selected", "true");
                    button.css("background-color", "#a0e876");
                    var slider = button.find("> .slider").eq(0);
                    slider.animate({left:0}, parseInt(100,10));
                    if(realChange && withCallback && options.onEnable && typeof options.onEnable == "function"){
                        options.onEnable(button);
                    }
                }

                function disable(button, withCallback){
                    var realChange = false;
                    if(button.prop("selected") != "false"){
                        realChange = true;
                    }
                    button.prop("selected", "false");
                    button.css("background-color", "#e95d46");
                    var slider = button.find("> .slider").eq(0);
                    slider.animate({left:button.width() - slider.width()}, parseInt(100,10));
                    if(realChange && withCallback && options.onDisable && typeof options.onDisable == "function"){
                        options.onDisable(button);
                    }
                }

		// loop through each matched element
		this.each(function(){
                    var container = $(this);
                    if(!container.is("div")){
                        var newNode = $("<div/>");
                        container.replaceWith(newNode); //returns the old node
                        // initialize property based on attribute
                        if(container.is(":checked") || container.attr("selected") == "selected"){
                            newNode.prop("selected", "true");
                        }
                        container = newNode;
                    }

                    container.addClass('togglebutton');

                    var slider = $("<div></div>").addClass("slider");
                    container.append(slider);
                    if(!container.hasClass("frozen")){
                        slider.draggable({
                            axis:"x",
                            containment:"parent",
                            snapMode:"inner",
                            snapTolerance:10,
                            scroll: false
                        });
                    }
                    
                    var ref = slider.position().left;
                    
                    // initialize property based on attribute
                    if(options.enabled || container.is(":checked") || container.attr("selected") == "selected"){
                    	container.prop("selected","true");
                        enable(container, false);
                    } else {
                    	container.prop("selected","false");
                        disable(container, false);
                    }
                    
                    container.droppable({
                        accept: ".slider",
                        drop: function(){
                            if(container.hasClass("frozen")){
                                return;
                            }
                            var l = slider.position().left  - ref;

                            var r = container.width() - l - slider.width();
                            if(l < r){
                                enable(container, true);
                            } else {
                                disable(container, true);
                            }
                        }
                    });
                    
                    container.click(function(){
                        if(container.hasClass("frozen")){
                            return;
                        }
                        if(container.prop('selected')=='true'){
                            disable(container, true);
                        } else  {
                            enable(container, true);
                        }
                    });
                    
                }); // each element
                

		return this; // don't break the chain...
	};

	/*--------------------------------------------------------*/

	/*
		### Core functionality and API ###
	*/
	$.extend($.fn.togglebutton, {
                selected: function(){
                    return $(this).prop("selected") == "true";
                }
 });

	/*--------------------------------------------------------*/

	/*
		### Default Settings ###
		eg.: You can override default control like this:
		$.fn.rating.options.cancel = 'Clear';
	*/
	$.fn.togglebutton.options = {  };

	/*--------------------------------------------------------*/

	/*
		### Default implementation ###
		The plugin will attach itself to divs with class togglebutton when the page loads
	*/
	$(function(){
	   
	});



/*# AVOID COLLISIONS #*/
})(jQuery);
/*# AVOID COLLISIONS #*/


