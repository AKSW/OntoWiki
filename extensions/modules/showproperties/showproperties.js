$(document).ready(function() {
    //Set up drag- and dropp-functionality
    $('.show-property').draggable({ helper: 'clone' });
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

    //set click handler
    $('.show-property').click(function() {
        var propUri = $(this).attr('about');
        if (!$(this).hasClass('InverseProperty')) {
            var pos = $.inArray(propUri, shownProperties);
            if (pos > -1) {
                shownProperties.splice(pos, 1);
                $(this).removeClass('selected');
            } else {
                shownProperties.push(propUri);
                $(this).addClass('selected');
            }
        } else {
            var pos = $.inArray(propUri, shownInverseProperties);
            if (pos > -1) {
                shownInverseProperties.splice(pos, 1);
                $(this).removeClass('selected');
            } else {
                shownInverseProperties.push(propUri);
                $(this).addClass('selected');
            }
        }

        if($(this).hasClass('InverseProperty')){
            var sessionVar = "shownInverseProperties";
            var sendArray = shownInverseProperties;
        } else {
            var sessionVar = "shownProperties";
            var sendArray = shownProperties;
        }
        var mainInnerContent = $(this).parents('.content.has-innerwindows').eq(0).find('.innercontent');
        sessionStore(sessionVar, serializeArray(sendArray), {withValue: true, method: 'set', callback: function() {
            mainInnerContent.load(document.URL);
        }});
    })
})
