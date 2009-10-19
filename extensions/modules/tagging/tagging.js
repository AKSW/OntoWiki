/**
 * This file is part of the tagging extension for OntoWiki
 *
 * @author     Atanas Alexandrov
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: tagging.js 4301 2009-10-13 21:31:35Z sebastian.dietzold $
 *
 */

/**
 * The main document.ready assignments and code
 */
$(document).ready(function() {
    taglistContainer = $('#tagging-content');
    taggedResource = taglistContainer.attr('about');
    taggingInput = $("#tagging-input");
    taggingUrl = urlBase + 'tagging/';
    taggingAction('listtags'); // uncomment this to load tags on pageload

    taggingInput.livequery('keypress', function(event) {
        // do not create until user pressed enter
	if ((event.which == 13) && (event.currentTarget.value != '') ) {
            taggingAction('addtag', event.currentTarget.value);
            $(event.currentTarget).val('');
	}
    });

    $('.Tag').livequery('click', function(event) {
        taggingAction('deltag', $(this).attr('about') );
        return false;
    });
	
	// suggestion tagging
    $('#tagging-input').autocomplete(taggingUrl+'autocomplete/', {
         formatItem: function(data, i, n, value) {
            return "<font color='#3399CC'>" + value + "</font>";
         },
         formatResult: function(data,value) { 	 
             return value;   
         }
    });
});

/**
 * request a tagging action
 */
function taggingAction ( type , typeparam) {
    // first we set the processing status
    taggingInput.addClass('is-processing');
 
    singleResource = '{\"0\" : '+'\"'+taggedResource+'\"'+'}';
	
    if (type == 'listtags') {
        params = { resources: singleResource };
    } else if (type == 'deltag') {
        params = { resources: singleResource, tagresource: typeparam};
    } else if (type == 'addtag') {
        params = { resources: singleResource, tag: typeparam};
    } else {
        taggingInput.removeClass('is-processing');
        return false;
    }
    
    $.post(taggingUrl + type, params,
        function (data) {
            taglistContainer.empty();
            taglistContainer.append(data);
            // remove the processing status
            taggingInput.removeClass('is-processing');
        }
    );

    return true;
}

/** 
 * Function that executes a URI search.
 */
function uriSearch(term)
{
    var searchUrl = urlBase + 'tagging/autocomplete/?term=' + term;	
    $.getJSON(searchUrl,myCallback);
}

function myCallback (jsonData) {
  test = jsonData;
  // console.log(test);
}
