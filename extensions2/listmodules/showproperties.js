$(document).ready(function() {
    //Set up drag- and drop-functionality
    $('.show-property').draggable({
                            scope: 'Resource',
                            helper: 'clone',
                            zIndex: 10000,
                            scroll: false,
                            appendTo: 'body'
                        });

    // highlight previously selected properties
    $('.show-property').each(function() {
        var propUri = $(this).attr('about');
        if (!$(this).hasClass('InverseProperty')) {
            var pos = $.inArray(propUri, shownProperties);
        } else {
            var pos = $.inArray(propUri, shownInverseProperties);
        }
        	
        if (pos > -1) {
            $(this).addClass('selected');
        }
    })
    function handleNewSelect() {
        var propUri = $(this).attr('about');
        $(this).toggleClass('selected');
        
        var action = $(this).hasClass('selected')? "add" : "remove";
           
        var inverse = $(this).hasClass('InverseProperty');

        var label = $(this).attr("title");
        
        var data = { shownProperties : [{
                "uri" : propUri,
                "label" : label,
                "action" : action,
                "inverse" : inverse
            }]
        };
        
        var serialized = $.toJSON(data);
        //
        //reload page
        //$('#showproperties form input').attr("value", serialized);
        //$('#showproperties form').submit();
        var arr = document.URL.split('?');
        var url = arr[0]; //without parameters (a s-parameter would re-build the query)
        // or reload list
        var mainInnerContent = $(this).parents('.content.has-innerwindows').eq(0).find('.innercontent');
        mainInnerContent.addClass('is-processing');
        mainInnerContent.load(
            reloadUrl+"",
            {"instancesconfig": serialized, "list":listName},
            function(){
                mainInnerContent.removeClass('is-processing');
                $('body').trigger('ontowiki.resource-list.reloaded');
                showPropertiesAddDroppableToListTable();
            });

      };

    function showPropertiesAddDroppableToListTable() {
        $('.content table').droppable({
             accept: '.show-property',
             scope: 'Resource',
             activeClass: 'ui-droppable-accepted-destination',
             hoverClass: 'ui-droppable-hovered',
             drop:
                 function(event, ui) {$(ui.draggable).each(handleNewSelect);}
        });
    }    
    
    //set click handler for the properties
    $('.show-property').click(handleNewSelect);
    
    showPropertiesAddDroppableToListTable();
})
