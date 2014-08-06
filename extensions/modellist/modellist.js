/**
 * @copyright Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
$(document).ready(function() {
    $('.modellist_hidden_button').livequery('click', function() {
        if ($(this).hasClass('show')) {
            sessionStore('showHiddenGraphs', true, {
                method: 'set', 
                callback: function() { 
                    updateModellistModule(); 
                }
            });
    } else {
        sessionStore('showHiddenGraphs', false, {
            method: 'unset', 
            callback: function() { 
                updateModellistModule(); 
            }
        });
    }
});
});

function updateModellistModule()
{
    // Remove the context menu.
    $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){
        $(this).remove();
    })

    var options = {
        url: urlBase + 'module/get/name/modellist/id/modellist'
    };

    $.get(options.url, function(data) {
        $('#modellist').replaceWith(data);
        $('#modellist').addClass('has-contextmenus-block')
        .addClass('windowbuttonscount-right-1')
        .addClass('windowbuttonscount-left-1');

        $('#modellist').enhanceWindow();
    });
}
