/**
 * This file is part of the navigation extension for OntoWiki
 *
 * @author     Sebastian Dietzold <sebastian@dietzold.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 *
 */

/**
 * The main document.ready assignments and code
 */
$(document).ready(function() {
    /* some used variables */
    navigationContainer = $('#navigation-content');
    navigationInput = $("#navigation-input");
    navigationWindow = $("#navigation");
    navigationUrl = urlBase + 'navigation/explore';

    /* Navigation toolbar */
    $('#navFirst').click(function(event){
        navigationEvent('navigateRoot');
    })
    $('#navBack').click(function(event){
        navigationEvent('navigateHigher');
    })
    $('#navSearch').click(function(event){
        navigationInput.toggle().focus();
    })

    navigationInput.livequery('keypress', function(event) {
        // do not create until user pressed enter
	if ((event.which == 13) && (event.currentTarget.value != '') ) {
            navigationEvent('search', event.currentTarget.value);
            $(event.currentTarget).val('');
	}
    });

    /* first start */
    navigationEvent('init');
});

/**
 * Setups the Navgiation Parameters and start the request
 */
function navigationSetParam (key, value) {

}


/**
 * Setups the Navgiation Parameters and start the request
 */
function navigationEvent (navEvent, eventParameter) {
    var setup, navType;

    /* init config when not existing or resetted by user */
    if ( ( typeof navigationSetup == 'undefined' ) || (navEvent == 'reset') || (navEvent == 'setType') ) {
        // set the default or setType config
        if (navEvent == 'setType') {
            navType = eventParameter;
        } else {
            navType = navigationConfig['default'];
        }

        var config = navigationConfig['config'][navType];
        // set the state
        var state = {};
        state['path'] = new Array;
        // pack state and config into setup value for post
        setup = { 'state' : state, 'config' : config };
    } else {
        setup = navigationSetup;
    }
    setup['state']['lastEvent'] = navEvent;


    if ( (navEvent == 'init') || (navEvent == 'reset') || (navEvent == 'setType') ) {
        // remove init sign and setup module title
        navigationContainer.removeClass('init-me-please');
        $('#navigation h1.title').text('Navigation: '+setup['config']['name']);
    }

    else if (navEvent == 'navigateDeeper') {
        // save path element
        if ( typeof setup['state']['parent'] != 'undefined' ) {
            setup['state']['path'].push(setup['state']['parent']);
        }
        // set new parent
        setup['state']['parent'] = eventParameter;
    }

    else if (navEvent == 'navigateHigher') {
        // count path elements
        var pathlength = setup['state']['path'].length;
        if ( typeof setup['state']['parent'] == 'undefined' ) {
            // we are at root level, so nothing higher than here
            return;
        }
        if (pathlength == 0) {
            // we are at the first sublevel (so we go to root)
            delete(setup['state']['parent']);
        } else {
            // we are somewhere deeper ...
            // set parent to the last path element
            setup['state']['parent'] = setup['state']['path'][pathlength-1];
            // and delete the last path element
            setup['state']['path'].pop();
        }
    }

    else if (navEvent == 'navigateRoot') {
        delete(setup['state']['parent']);
        setup['state']['path'] = new Array;
    }

    else if (navEvent == 'search') {
        setup['state']['searchString'] = eventParameter;
    }

    else if (navEvent == 'toggleHidden') {
        if (setup['state']['showHidden'] == true) {
            setup['state']['showHidden'] = false;
        } else {
            setup['state']['showHidden'] = true;
        }
    }

    else {
        alert('error: unknown navigation event: '+navEvent);
        return;
    }

    navigationSetup = setup;
    navigationLoad (navEvent, setup);
    return;
}

/**
 * request the navigation
 */
function navigationLoad (navEvent, setup) {
    if (typeof setup == 'undefined') {
        alert('error: No navigation setup given, but navigationLoad requested');
        return false;
    }

    // first we set the processing status
    navigationInput.addClass('is-processing');

    navigationContainer.css('overflow', 'hidden');
    navigationContainer.animate({marginLeft:'-100%'},'slow', '', function(){
        $.post(navigationUrl, { setup: $.toJSON(setup) },
            function (data) {
                navigationContainer.empty();
                navigationContainer.append(data);
                // remove the processing status
                navigationInput.removeClass('is-processing');

                navigationContainer.css('marginLeft', '100%');
                navigationContainer.animate({marginLeft:'0px'},'fast');

                $('.navigation').click(function(event) {
                    navigationEvent('navigateDeeper', $(this).attr('about'));
                });
            }
        );
    });

    return true;
}
