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
    if (navEvent == 'init') {
        // init only works if navcontainer is empty
        if (navigationContainer.hasClass('init-me-please')) {
            // set the default config
            var navType = navigationConfig['default'];
            var config = navigationConfig['config'][navType];
            // set the state
            var state = {};
            state['lastEvent'] = navEvent;
            // pack state and config into setup value for post
            var setup = { 'state' : state, 'config' : config };
            navigationContainer.removeClass('init-me-please');
        }
    }

    else if (navEvent == 'navigateDeeper') {
        var setup = navigationSetup;
        setup['state']['lastEvent'] = navEvent;
        setup['state']['parent'] = eventParameter;
    }

    else if (navEvent == 'setType') {
        // set the new config
        var config = navigationConfig['config'][eventParameter];
        // set the state
        var state = {};
        state['lastEvent'] = navEvent;
        // pack state and config into setup value for post
        var setup = { 'state' : state, 'config' : config };
    }

    else if (navEvent == 'search') {
        var setup = navigationSetup;
        setup['state']['lastEvent'] = navEvent;
        setup['state']['searchString'] = eventParameter;
    }

    else if (navEvent == 'reset') {
        // set the default config
        var navType = navigationConfig['default'];
        var config = navigationConfig['config'][navType];
        // set the state
        var state = {};
        state['lastEvent'] = navEvent;
        // pack state and config into setup value for post
        var setup = { 'state' : state, 'config' : config };
    }


    // check for setup and load Navigation
    if ( typeof setup != 'undefined' ) {
        navigationSetup = setup;
        navigationLoad (navEvent, setup);
    } else {
        alert('error: navigationSetup doesnt produced a setup here (event was: '+navEvent+')');
    }
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
