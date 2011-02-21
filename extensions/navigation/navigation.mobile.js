/**
 * This file is part of the navigation extension for OntoWiki
 *
 * @author     Sebastian Tramp <tramp@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 *
 */

/**
 * The main document.ready assignments and code
 */
$(document).ready(function() {
    navClickEvent = new $.Event("navigation.done");

    /* some used variables */
    navigationExploreUrl = urlBase + 'navigation/explore';
    navigationMoreUrl = urlBase + 'navigation/more';
    navigationSaveUrl = urlBase + 'navigation/savestate';
    navigationLoadUrl = urlBase + 'navigation/loadstate';
    navigationListUrl = urlBase + 'list';
    navSetup = '';

    if( typeof navigationStateSetup != 'undefined'){
        navigationSetup = navigationStateSetup;
    }
});

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
            navType = navigationConfig['defaults']['config'];
        }
        var config = navigationConfig['config'][navType];

        // the limit
        var limit = navigationConfig['defaults']['limit'];

        // set the state
        var state = {};
        state['limit'] = limit;
        state['path'] = new Array;
        // pack state and config into setup value for post
        setup = {'state': state, 'config': config};
    } else {
        setup = navigationSetup;
    }

    // nav event
    switch (navEvent) {
        case 'init':
            // save hidden, implicit and empty to state
            if(typeof navigationStateSetup != 'undefined'){
                if(typeof navigationStateSetup['state']['showEmpty'] != 'undefined'){
                    setup['state']['showEmpty'] = navigationStateSetup['state']['showEmpty'];
                }
                if(typeof navigationStateSetup['state']['showImplicit'] != 'undefined'){
                    setup['state']['showImplicit'] = navigationStateSetup['state']['showImplicit'];
                }
                if(typeof navigationStateSetup['state']['showHidden'] != 'undefined'){
                    setup['state']['showHidden'] = navigationStateSetup['state']['showHidden'];
                }
            }else{
                if(setup['config']['showEmptyElements'] == '1'){
                    setup['state']['showEmpty'] = true;
                }
                if(setup['config']['showImplicitElements'] == '1'){
                    setup['state']['showImplicit'] = true;
                }
                if(setup['config']['showHiddenElements'] == '1'){
                    setup['state']['showHidden'] = true;
                }
            }
            // remove init sign and setup module title
            navigationContainer.removeClass('init-me-please');
            //$('#navigation h1.title').text('Navigation: '+setup['config']['name']);
            break;
        case 'reset':
            if(setup['config']['showEmptyElements'] == '1'){
                setup['state']['showEmpty'] = true;
            }
            if(setup['config']['showImplicitElements'] == '1'){
                setup['state']['showImplicit'] = true;
            }
            if(setup['config']['showHiddenElements'] == '1'){
                setup['state']['showHidden'] = true;
            }
        case 'refresh':
            break;

        case 'showResourceList':
            setup['state']['parent'] = eventParameter;
            break;

        case 'navigateDeeper':
            // save path element
            if ( typeof setup['state']['parent'] != 'undefined' ) {
                setup['state']['path'].push(setup['state']['parent']);
            }
            // set new parent
            setup['state']['parent'] = eventParameter;
            break;
        
        case 'navigateHigher':
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
            break;

        case 'navigateRoot':
            // we are at root level, so nothing higher than here
            // exception: after a search, it should be also rootable
            if ( ( typeof setup['state']['parent'] == 'undefined' )
                && ( setup['state']['lastEvent'] != 'search' ) ){
                return;
            }
            delete(setup['state']['parent']);
            setup['state']['path'] = new Array;
            break;
            
        case 'search':
            setup['state']['searchString'] = eventParameter;
            break;

        case 'setLimit':
            setup['state']['limit'] = eventParameter;
            delete(setup['state']['offset']);
            break;
            
        case 'more':
            if( setup['state']['offset'] !== undefined  ){
                setup['state']['offset'] = setup['state']['offset']*2;
            }else{
                setup['state']['offset'] = parseInt(setup['state']['limit']) + 10;
            }
            setup['state']['limit'] = setup['state']['offset'];
            break;

        default:
            alert('error: unknown navigation event: '+navEvent);
            return;
    }

    setup['state']['lastEvent'] = navEvent;
    navigationSetup = setup;
    navSetup = setup;
    navigationLoad (navEvent, setup);
    
    return; 
}

/**
 * request the navigation
 */
function navigationLoad (navEvent, setup) {
    if (typeof setup == 'undefined') {
        alert('error: No navigation setup given, but navigationLoad requested');
        return;
    }

    // preparation of a callback function
    var cbAfterLoad = function(){
        $.post(navigationExploreUrl, {setup: $.toJSON(setup)},
            function (data) {
                //navigationContainer.empty();
                //navigationContainer.append(data);
                provider.saveNavigation(data);
                // remove the processing status
                //navigationInput.removeClass('is-processing');

                $(document).trigger(navClickEvent);
            }
        );
    }

    // first we set the processing status
    //navigationInput.addClass('is-processing');
    //navigationContainer.css('overflow', 'hidden');
    
    cbAfterLoad();
    
    return ;
}
