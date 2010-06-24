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
    /* some used variables */
    navigationContainer = $('#navigation-content');
    navigationInput = $("#navigation-input");
    navigationWindow = $("#navigation");
    navigationExploreUrl = urlBase + 'navigation/explore';
    navigationMoreUrl = urlBase + 'navigation/more';
    navigationSaveUrl = urlBase + 'navigation/savestate';
    navigationLoadUrl = urlBase + 'navigation/loadstate';
    navigationListUrl = urlBase + 'list';
    navSetup = '';

    navigationInput.livequery('keypress', function(event) {
        // do not create until user pressed enter
        if ((event.which == 13) && (event.currentTarget.value != '') ) {
            navigationEvent('search', event.currentTarget.value);
            $(event.currentTarget).val('');
            return false;
        } else if ( event.which == 13 ) {
            return false;
        }
        return true;
    });
    
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

    // delete old search string
    delete(setup['state']['searchString']);

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
            $('#navigation h1.title').text('Navigation: '+setup['config']['name']);
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
        case 'setType':
            // remove init sign and setup module title
            navigationContainer.removeClass('init-me-please');
            $('#navigation h1.title').text('Navigation: '+setup['config']['name']);
            break;

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

        case 'toggleHidden':
            if ( typeof setup['state']['showHidden'] != 'undefined' ) {
                delete(setup['state']['showHidden']);
                $("a[href='javascript:navigationEvent(\'toggleHidden\')']").text("Show Hidden Elements");
            } else {
                setup['state']['showHidden'] = true;
                $("a[href='javascript:navigationEvent(\'toggleHidden\')']").text("Hide Hidden Elements");
            }
            break;

        case 'toggleEmpty':
            // if no state is set, use default value from config
            if ( typeof setup['state']['showEmpty'] == 'undefined' ) {
                if ( typeof setup['config']['showEmptyElements'] != 'undefined' ) {
                    setup['state']['showEmpty'] = setup['config']['showEmptyElements'];
                    if(setup['state']['showEmpty'] == true){
                        $("a[href='javascript:navigationEvent(\'toggleEmpty\')']").text("Hide Empty Elements");
                    }
                } else {
                    setup['state']['showEmpty'] = true;
                    $("a[href='javascript:navigationEvent(\'toggleEmpty\')']").text("Hide Empty Elements");
                }
            } else if (setup['state']['showEmpty'] == false) {
                setup['state']['showEmpty'] = true;
                $("a[href='javascript:navigationEvent(\'toggleEmpty\')']").text("Hide Empty Elements");
            } else {
                setup['state']['showEmpty'] = false;
                $("a[href='javascript:navigationEvent(\'toggleEmpty\')']").text("Show Empty Elements");
            }
            break;

        case 'toggleImplicit':
            // if no state is set, use default value from config
            if ( typeof setup['state']['showImplicit'] == 'undefined' ) {
                if ( typeof setup['config']['showImplicitElements'] != 'undefined' ) {
                    setup['state']['showImplicit'] = setup['config']['showImplicitElements'];
                    if(setup['state']['showImplicit'] == true){
                        $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Hide Implicit Elements");
                    }else{
                        $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Show Implicit Elements");
                    }
                } else {
                    setup['state']['showImplicit'] = true;
                    $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Hide Implicit Elements");
                }
            } else if (setup['state']['showImplicit'] == false) {
                setup['state']['showImplicit'] = true;
                $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Hide Implicit Elements");
            } else {
                setup['state']['showImplicit'] = false;
                $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Show Implicit Elements");
            }
            break;
        case 'more':
            if( setup['state']['offset'] !== undefined  ){
                setup['state']['offset'] = setup['state']['offset']*2;
            }else{
                setup['state']['offset'] = parseInt(setup['state']['limit']) + 10;
            }
            setup['state']['limit'] = setup['state']['offset'];
            break;
        case 'setSort':
            setup['state']['sorting'] = eventParameter;
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
                //alert(data);
                navigationContainer.empty();
                navigationContainer.append(data);
                // remove the processing status
                navigationInput.removeClass('is-processing');

                switch (navEvent) {
                    case 'more':
                        navigationMore.remove();
                        // remove the processing status
                        navigationMore.removeClass('is-processing');
                    case 'refresh':
                        // no animation in refresh event (just the is processing)
                        break;
                    case 'navigateHigher':
                        navigationContainer.css('marginLeft', '-100%');
                        navigationContainer.animate({marginLeft:'0px'},'slow');
                        break;
                    case 'navigateDeeper':
                        navigationContainer.css('marginLeft', '100%');
                        navigationContainer.animate({marginLeft:'0px'},'slow');
                        break;
                }

                navigationPrepareList();
            }
        );
    }

    // first we set the processing status
    navigationInput.addClass('is-processing');
    navigationContainer.css('overflow', 'hidden');

    switch (navEvent) {
        case 'more':
            navigationMore = $("#naviganion-more");
            navigationMore.html('&nbsp;&nbsp;&nbsp;&nbsp;');
            navigationMore.addClass('is-processing');
        case 'refresh':
            // no animation in refresh event (just the is processing)
            cbAfterLoad();
            break;
        case 'navigateHigher':
            navigationContainer.animate({marginLeft:'100%'},'slow', '', cbAfterLoad);
            break;
        case 'navigateDeeper':
            navigationContainer.animate({marginLeft:'-100%'},'slow', '', cbAfterLoad);
            break;
        default:
            //navigationContainer.slideUp('fast', cbAfterLoad);
            cbAfterLoad();
    }

    return ;
}

function navigationPrepareToggles(){
    if (navigationSetup['state']['showHidden'] == true ) {
        $("a[href='javascript:navigationEvent(\'toggleHidden\')']").text("Hide Hidden Elements");
    } else {
        $("a[href='javascript:navigationEvent(\'toggleHidden\')']").text("Show Hidden Elements");
    }

    // if no state is set, use default value from config
    if (navigationSetup['state']['showEmpty'] == true) {
        $("a[href='javascript:navigationEvent(\'toggleEmpty\')']").text("Hide Empty Elements");
    } else {
        $("a[href='javascript:navigationEvent(\'toggleEmpty\')']").text("Show Empty Elements");
    }

    // if no state is set, use default value from config
    if (navigationSetup['state']['showImplicit'] == true) {
        $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Hide Implicit Elements");
    } else {
        $("a[href='javascript:navigationEvent(\'toggleImplicit')']").text("Show Implicit Elements");
    }
}

/*
 * This function creates navigation events
 */
function navigationPrepareList () {
    //saveState();
    
    // the links to deeper navigation entries
    $('.navDeeper').click(function(event) {
        navigationEvent('navigateDeeper', $(this).parent().attr('about'));
        return false;
    });

    // the link to the root
    $('.navFirst').click(function(event){
        navigationEvent('navigateRoot');
        return false;
    })
    
    // the link to higher level
    $('.navBack').click(function(event){
        navigationEvent('navigateHigher');
        return false;
    })

    // the link to the instance list
    $('.navList').click(function(event){
        window.location.href = $(this).attr('href');
        return false;
    })

    navigationPrepareToggles();
}

/*
 * Starts RDFauthor with a specific class init depending on position and config
 */
function navigationAddElement(){
    // we use the first configured navigationType to create
    var classResource = navigationSetup['config']['hierarchyTypes'][0];

    // callback which manipulates the data given from the init json service
    dataCallback = function(data) {
        var config = navigationSetup['config']; // configured navigation setup
        var state = navigationSetup['state']; // current navigation state

        // subjectUri is the main resource
        for (var newElementUri in data) {break;};

        // check for parent element
        if (typeof state['parent'] != 'undefined') {
            var parentUri = state['parent'];
            var relations = config['hierarchyRelations']; // configured hierarchy relations

            if (typeof relations['in'] != 'undefined') {
                // check for hierarchy relations (incoming eg. subClassOf)
                var propertyUri = relations['in'][0]

                // TODO: this should be done by a future RDF/JSON API
                data[newElementUri][propertyUri] = [ {"type" : "uri" , "value" : parentUri} ];
            } else if (typeof relations['out'] != 'undefined') {
                // check for hierarchy relations (outgoing eg. skos:narrower)
                var propertyUri = relations['out'][0];

                // TODO: this should be done by a future RDF/JSON API
                var newStatement = {};
                newStatement[propertyUri] = [{
                        "hidden": true ,
                        "type" : "uri" ,
                        "value" : newElementUri
                    }];
                data[parentUri] = newStatement;
            }
        }

        // dont forget to return the manipulated data
        return data;
    }

    // start RDFauthor
    createInstanceFromClassURI(classResource, dataCallback);
    
}
