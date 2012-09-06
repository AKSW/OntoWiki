$(document).ready(function()
{
    // ------------------------------------------------------------------------
    // --- Location Bar -------------------------------------------------------
    // ------------------------------------------------------------------------

    // Bind the menu entry to the show and hide methods on click.
    $('a.location_bar').parent('li').click(function() {
        if ($('a.location_bar').hasClass('show')) {
            showLocationBar();
            sessionStore('showLocationBar', true, {method: 'set'});
        } else {
            hideLocationBar();
            sessionStore('showLocationBar', false, {method: 'unset'});
        }
    });

    // Show location bar if it is active...
    if (typeof($('a.location_bar').get(0)) != 'undefined' && !$('a.location_bar').hasClass('show')) {
        showLocationBar();
    }

    // Click event for the View button
    $('#location_open').live('click', function(e) {
        if (e.which == 1) {
            window.location = urlBase + 'resource/properties?r=' + encodeURIComponent($('#location_bar_input').val());
        }
    });

    // Enter event for the View button
    $('#location_bar_input').live('keypress', function(evt) {
        if (evt.which == 13) {
            window.location = urlBase + 'resource/properties?r=' + encodeURIComponent($('#location_bar_input').val());
        }
    });


    // ------------------------------------------------------------------------
    // --- Datagathering ------------------------------------------------------
    // ------------------------------------------------------------------------

    // Datagathering via resource context menus
    $('.fetch_data_button').livequery('click', function() {
        var uriValue = $(this).attr('about');
        var classesArray = $(this).attr('class').split(' ');
        var wrapperName = '';
        for (var i=0; i<classesArray.length; ++i) {
            if (classesArray[i].substr(0, 7) == 'wrapper') {
                wrapperName = classesArray[i].substr(8);
                break;
            }
        }
        var url = urlBase + 'datagathering/import/';
        $(this).replaceWith('<span class="is-processing" style="min-height: 16px; display: block"></span>');

        var request = $.ajax({
           url: url,
           data: { uri: uriValue, wrapper: wrapperName },
           success: function(data, textStatus, jqXHR) {
               $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){ $(this).remove(); });
               window.location = document.URL;
           },
           error: function(jqXHR, textStatus, errorThrown) {
               $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){ $(this).remove(); });
               window.location = document.URL;
           },
           timeout: 30000
        });

        return false;
    });

    // Datagathering via resource context menus
    $('.sync_data_button').livequery('click', function() {
        var uriValue = $(this).attr('about');
        var classesArray = $(this).attr('class').split(' ');
        var wrapperName = '';
        for (var i=0; i<classesArray.length; ++i) {
            if (classesArray[i].substr(0, 7) == 'wrapper') {
                wrapperName = classesArray[i].substr(8);
                break;
            }
        }
        var url = urlBase + 'datagathering/sync';
        $(this).replaceWith('<span class="is-processing" style="min-height: 16px; display: block"></span>');
        $.getJSON(url, {uri: uriValue, wrapper: wrapperName}, function(data) {
            if (data['redirect']) {
                $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){ $(this).remove(); })
                window.location = data['redirect'];
                return false;
            }

            $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){ $(this).remove(); })
            window.location = document.URL;
            return false;
        });

        return false;
    });

    // Check for updates. Currently only linkeddata is supported!
    var checkElem = $('#dg_check_update');
    if (typeof(checkElem.get(0)) != 'undefined') {
        var uriValue = $('div.section-mainwindows table').eq(0).attr('about');
        var url = urlBase + 'datagathering/modified';

        $.getJSON(url, {uri: uriValue, wrapper: 'linkeddata'}, function(data) {
            if (data != false) {
                $('#dg_configured_text').hide();
                $('#dg_updated_text').show();

                $('#dg_lastmod_date').html(data.lastMod);
                $('#dg_lastmod_date').show();
                $('#dg_lastmod_text').show();
                $('#dg_sync_button').show();



                checkElem.parent('p').removeClass('info').addClass('success');

                $('#dg_sync_button').livequery('click', function() {
                    var url = urlBase + 'datagathering/sync';
                    $(this).append('<div class="is-processing" style="float:right; width: 16px; min-height: 16px; margin-left: 4px;"></div>');
                    $.getJSON(url, {uri: uriValue, wrapper: 'linkeddata'}, function(data) {
                        window.location = document.URL;
                    });

                    return false;
                });
            }
        });
    }
});


// ------------------------------------------------------------------------
// --- Location Bar related functions -------------------------------------
// ------------------------------------------------------------------------

/**
 * Shows the location bar.
 *//**
 * Function that executes a URI search.
 */
function locationBarUriSearch(term, cb)
{
    var searchUrl = urlBase + 'datagathering/search?q=' + term;

    $.getJSON(searchUrl,
        function(jsonData) {
            cb(jsonData);
    });
}
function showLocationBar()
{
    $('a.location_bar').removeClass('show');
    $('#location_bar_container').show();

    // $('#location_bar_input')._autocomplete(function(term, cb) { locationBarUriSearch(term, cb); }, {
    //         minChars: 3,
    //         delay: 1000,
    //         max: 100,
    //         formatItem: function(data, i, n, term) {
    //             return '<div style="overflow:hidden">\
    //                     <span style="white-space: nowrap;font-size: 0.8em">' + data[2] + '</span>\
    //                     <br />\
    //                     <span style="white-space: nowrap;font-weight: bold">' + data[0] + '</span>\
    //                     <br />\
    //                     <span style="white-space: nowrap;font-size: 0.8em">' + data[1] + '</span>\
    //                     </div>';
    //         }
    //     });

    $('#location_bar_input').result(function(e, data, formated) {
        $(this).attr('value', data[1]);
    });
}

/**
 * Removes the location bar.
 */
function hideLocationBar()
{
    $('#location_bar_container').hide();
    $('a.location_bar').addClass('show');
}

/**
 * Function that executes a URI search.
 */
function uriSearch(term, cb)
{
    var searchUrl = urlBase + 'datagathering/search?q=' + term;

    $.getJSON(searchUrl,
        function(jsonData) {
            cb(jsonData);
        });
}
