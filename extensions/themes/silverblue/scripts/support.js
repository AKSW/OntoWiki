/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Support functions
 */

function toggleExpansion(event) {
    var target = $(event.target);
    var resourceUri = target.next().attr('about') ? target.next().attr('about') : target.next().attr('resource');
    
    if (target.hasClass('expand')) {
        target.removeClass('expand').addClass('collapse');
        
        if (target.parent().children('.expansion').length) {
            target.parent().children('.expansion').slideDown(effectTime);
        } else {
            var expansion = $('<div class="expansion" style="font-size:90%"></div>');
            target.parent().append(expansion);
            var url    = urlBase + 'view/';
            var params = 'r=' + encodeURIComponent(resourceUri);
            $.ajax({
                url:      url, 
                data:     params, 
                dataType: 'html', 
                success:  function(content) {
                    expansion.hide();
                    expansion.append(content);
                    expansion.slideDown(effectTime);
                    /*map.updateInfoWindow([
                        new GInfoWindowTab('', target.parent().html())
                    ])*/ // only a javascript error, i think it was neccessery for the old ontowiki
                }
            });
        }
    } else {
        target.removeClass('collapse').addClass('expand');
        target.parent().find('.expansion').slideUp(effectTime);
    }
}

function expand(event) {
    target = $(event.target);
    resourceURI = target.next().attr('about');
    encodedResourceURI = encodeURIComponent(resourceURI);
    resource = target.next();

    if (target.is('.expand')) {
        target.removeClass('expand').addClass('deexpand');
        // target.next().after('<div class="is-processing expanded-content"></div>');
        url = urlBase + 'resource/properties/';
        params = 'r=' + encodedResourceURI;
        $.ajax({
            url: url,
            data: params,
            dataType: 'html',
            // success: function(msg){alert( 'Data Saved: ' + msg );}
            success: function(content) {
                map.updateInfoWindow([new GInfoWindowTab('', target.parent().html() + '<div style="font-size:90%">' + content + '</div>')]);
                // resource.next().html(content);
                // resource.next().removeClass('is-processing');
                }
        });
    }
    else if (target.is('.deexpand')) {
        target.removeClass('deexpand').addClass('expand');
        target.next().next().remove();
    }
}

/**
 * Changes the Ratio between main and side-section
 */
function setSectionRatio(x) {
    $('div.section-sidewindows').css('width', x + '%');
    $('div.section-mainwindows').css('width', (100 - x) + '%');
    $('div.section-mainwindows').css('margin-left', x + '%');
}

function showWindowMenu(event) {
    // remove all other menus
    $('.contextmenu-enhanced .contextmenu').remove();
    
    menuX  = event.pageX - 11;
    menuY  = event.pageY - 11;
    menuId = 'windowmenu-' + menuX + '-' + menuY;

    // create the plain menu with correct style and position
    $('.contextmenu-enhanced').append('<div class="contextmenu is-processing" id="' + menuId + '"></div>');
    $('#' + menuId)
        .attr({style: 'z-index: ' + menuZIndex + '; top: ' + menuY + 'px; left: ' + menuX + 'px;'})
        .click(function(event) {event.stopPropagation();});

    $('#' + menuId).fadeIn();

    // setting url parameters
    var urlParams = {};
    urlParams.module = $(event.target).parents('.window').eq(0).attr('id');

    // load menu with specific options from service
    $.ajax({
        type: "GET",
        url: urlBase + 'service/menu/',
        data: urlParams,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("error occured - details at firebug-console");
            console.log("menu service error\nfailure message:\n" + textStatus);
            $('#' + menuId).fadeOut();
        },
        success: function(data, textStatus) {
            try {

                menuData = $.evalJSON(data);

                var menuStr = '';
                var tempStr = '';
                var href    = '';

                // construct menu content
                for (var key in menuData) {
                    if ( menuData[key] == '_---_' ) {
                        menuStr += '</ul><hr/><ul>';
                    } else {
                        if ( typeof(menuData[key]) == 'string' ) {
                            tempStr = '<a href="' + menuData[key] + '">' + key + '</a>';
                        } else {
                            tempStr = '<a ';
                            for (var attr in menuData[key]) {
                                tempStr += attr + '="' + menuData[key][attr] + '" ';
                            }
                            tempStr += '>' + key + '</a>';
                        }
                        menuStr += '<li>' + tempStr + '</li>';
                    }
                }

                // append menu string with surrounding list
                $('#' + menuId).append('<ul>' + menuStr + '</ul>');

                // remove is-processing
                $('#' + menuId).toggleClass('is-processing');

            } catch (e) {
                alert("error occured - details at firebug-console");
                console.log("menu service error\nmenu service replied:\n" + data);
                $('#' + menuId).fadeOut();
            }

        }
    });

    // prevent href trigger
    event.stopPropagation();

}

/*
 * Save a key-value pair via ajax
 */
function sessionStore(name, value, options) {
    var defaultOptions = {
        encode:    false, 
        namespace: _OWSESSION, 
        callback:  null, 
        method:    'set', 
        url:       urlBase + 'service/session/', 
        withValue: false
    };
    var config = $.extend(defaultOptions, options);

    // TODO
    if (!config.encode) {
        if (!config.withValue) {
            config.url += '?name=' + name + '&value=' + value + '&method=' + config.method + '&namespace=' + config.namespace;
        } else {
            config.url += '?name=' + name + '&' + value + '&method=' + config.method + '&namespace=' + config.namespace;
        }

        $.get(config.url, config.callback);
    } else {
        var params = {name: name, value: value, namespace: config.namespace};
        $.get(config.url, params, config.callback);
    }
}

/*
 * This function sets an automatic id attribute if no id exists
 * parameter: el -> jquery element
 */
function setAutoId(element) {
    if (!element.attr('id')) {
        element.attr('id', 'autoid' + idCounter++);
    }
}

/*
 * hide a href by putting this attribute into an array
 * parameter: el -> jquery element
 */
function hideHref(element) {
    setAutoId(element);
    
    if (element.attr('href')) {
        tempHrefs[element.attr('id')] = element.attr('href');
        element.removeAttr('href');
    }
}

function showHref(element) {
    if (tempHrefs[element.attr('id')]) {
        element.attr('href', tempHrefs[element.attr('id')]);
    }
}

function serializeArray(array, key)
{
    if (typeof key == 'undefined') {
        key = 'value';
    }
    
    var serialization = '';
    
    if (array.length) {
        serialization += key + '[]=' + encodeURIComponent(array[0]);
        
        for (var i = 1; i < array.length; ++i) {
            serialization += '&' + key + '[]=' + encodeURIComponent(array[i]);
        }
    } else {
        serialization += key + '=';
    }
    
    return serialization;
}

/*
 * remove all other menus
 */
function removeResourceMenus() {
    $('.contextmenu-enhanced .contextmenu').remove();
}


function showResourceMenu(event, json) {
    // remove all other menus
    removeResourceMenus();
    
    menuX  = event.pageX - 30;
    menuY  = event.pageY - 20;
    menuId = 'windowmenu-' + menuX + '-' + menuY;
    
    // create the plain menu with correct style and position
    $('.contextmenu-enhanced').append('<div class="contextmenu is-processing" id="' + menuId + '"></div>');
    $('#' + menuId)
        .attr({style: 'z-index: ' + menuZIndex + '; top: ' + menuY + 'px; left: ' + menuX + 'px;'})
        .click(function(event) {event.stopPropagation();});

    $('#' + menuId).fadeIn();
    
    parentHref = tempHrefs[$(event.target).parent().attr('id')];
    
    function onJSON(menuData, textStatus) {
        try {
            //console.log(menuData)
            var menuStr = '';
            var tempStr = '';
            var href    = '';

            // construct menu content
            for (var key in menuData) {
                href = menuData[key];
                if ( menuData[key] == '_---_' ) {
                    menuStr += '</ul><hr/><ul>';
                } else {
                    if (typeof(href) == 'object') {
                        tempStr = '<a class="' + href['class'] + '" about="' + href['about'] + '">' + key + '</a>';
                    } else {
                        tempStr = '<a href="' + href + '">' + key + '</a>';
                        if (href == parentHref) {
                            tempStr = '<strong>' + tempStr + '</strong>';
                        }
                    }
                    menuStr += '<li>' + tempStr + '</li>';
                }
            }

            // append menu string with surrounding list
            $('#' + menuId).append('<ul>' + menuStr + '</ul>');

            // remove is-processing
            $('#' + menuId).toggleClass('is-processing');

        } catch (e) {
            alert("error occured - details at firebug-console");
            console.log("menu service error\nmenu service replied:\n" + data);
            $('#' + menuId).fadeOut();
        }
    }
    
    if(json == undefined){
        // URI of the resource clicked (used attribute can be about and resource)
        if ( typeof $(event.target).parent().attr('about') != 'undefined' ) {
            resourceUri = $(event.target).parent().attr('about');
        } else if ( typeof $(event.target).parent().attr('resource') != 'undefined' ) {
            resourceUri = $(event.target).parent().attr('resource');
        } else {
            // no usable resource uri, so we exit here
            return false;
        }

        encodedResourceUri = encodeURIComponent(resourceUri);
        resource = $(event.target).parent();

        

        var urlParams = {};
        urlParams.resource = resourceUri;


        // load menu with specific options from service
        $.ajax({
            type: "GET",
            url: urlBase + 'service/menu/',
            data: urlParams,
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("error occured - details at firebug-console");
                console.log("menu service error\nfailure message:\n" + textStatus);
                $('#' + menuId).fadeOut();
            },
            success: function(data, textStatus){onJSON($.evalJSON(data), textStatus);}
        });
    } else {
        onJSON(json)
    }

    // prevent href trigger
    event.stopPropagation();
}

/**
 * Loads RDFauthor if necessary and executes callback afterwards.
 */
function loadRDFauthor(callback) {
    var loaderURI = RDFAUTHOR_BASE + 'src/rdfauthor.js';
    
    if ($('head').children('script[src=' + loaderURI + ']').length > 0) {
        callback();
    } else {
        RDFAUTHOR_READY_CALLBACK = callback;
        // load script
        var s = document.createElement('script');
        s.type = 'text/javascript';
        s.src = loaderURI;
        document.getElementsByTagName('head')[0].appendChild(s);
    }
}

function populateRDFauthor(data, protect, resource, graph) {
    protect  = arguments.length >= 2 ? protect : true;
    resource = arguments.length >= 3 ? resource : null;
    graph    = arguments.length >= 4 ? graph : null;
    
    for (var currentSubject in data) {
        for (var currentProperty in data[currentSubject]) {
            var objects = data[currentSubject][currentProperty];

            for (var i = 0; i < objects.length; i++) {
                var objSpec = objects[i];
                
                var newObjectSpec = {
                    value: (objSpec.type == 'uri') ? ('<' + objSpec.value + '>') : objSpec.value, 
                    type: String(objSpec.type).replace('typed-', '')
                }
                
                if (objSpec.value) {
                    if (objSpec.type == 'typed-literal') {
                        newObjectSpec.options = {
                            datatype: objSpec.datatype
                        }
                    } else if (objSpec.lang) {
                        newObjectSpec.options = {
                            lang: objSpec.lang
                        }
                    }
                }
                
                RDFauthor.addStatement(new Statement({
                    subject: '<' + currentSubject + '>', 
                    predicate: '<' + currentProperty + '>', 
                    object: newObjectSpec
                }, {
                    graph: graph, 
                    title: objSpec.title, 
                    protected: protect ? true : false, 
                    hidden: objSpec.hidden ? objSpec.hidden : false
                }));
            }
        }
    }
}

/*
 * get the rdfa init description from the service in class mode and start the
 * RDFauthor window
 * dataCallback is called right after the json request to manipulate the requested data
 */
function createInstanceFromClassURI(type, dataCallback) {
    var serviceUri = urlBase + 'service/rdfauthorinit';

    // remove resource menus
    removeResourceMenus();

    loadRDFauthor(function() {
        $.getJSON(serviceUri, {
            mode: 'class',
            uri: type
        }, function(data) {
            // pass data through callback
            if (typeof dataCallback == 'function') {
                data = dataCallback(data);
            }

            // get default resource uri for subjects in added statements (issue 673)
            // grab first object key
            for (var subjectUri in data) {break;};
            // add statements to RDFauthor
            populateRDFauthor(data, true, subjectUri, selectedGraph.URI);
            RDFauthor.setOptions({
                saveButtonTitle: 'Create Resource',
                cancelButtonTitle: 'Cancel',
                title: 'Create New Instance of ' + type,  
                autoParse: false, 
                showPropertyButton: true, 
                onSubmitSuccess: function (responseData) {
                    var newLocation;
                    if (responseData && responseData.changed) {
                        newLocation = resourceURL(responseData.changed);
                    } else {
                        newLocation = window.location.href;
                    }
                    // HACK: reload whole page after 500 ms
                    window.setTimeout(function () {
                        window.location.href = newLocation;
                    }, 500);
                }
            });
           
            RDFauthor.start();
        })
    });
}

/*
 * get the rdfauthor init description from the service in and start the RDFauthor window
 */
function editResourceFromURI(resource) {
    var serviceUri = urlBase + 'service/rdfauthorinit';

    // remove resource menus
    removeResourceMenus();

    loadRDFauthor(function() {
        $.getJSON(serviceUri, {
           mode: 'edit',
           uri: resource
        }, function(data) {
            
            // get default resource uri for subjects in added statements (issue 673)
            // grab first object key
            for (var subjectUri in data) {break;};

            // add statements to RDFauthor
            populateRDFauthor(data, false, resource, selectedGraph.URI);

            RDFauthor.setOptions({
                saveButtonTitle: 'Save Changes',
                cancelButtonTitle: 'Cancel',
                title: 'Edit Resource ' + resource,  
                autoParse: false, 
                showPropertyButton: true, 
                onSubmitSuccess: function () {
                    // HACK: reload whole page after 500 ms
                    window.setTimeout(function () {
                        window.location.href = window.location.href;
                    }, 500);
                }
            });

            RDFauthor.start();
        })
    });
}

/**
 * Creates a new internal OntoWiki URL for the given resource URI.
 * @return string
 */
function resourceURL(resourceURI) {
    if (resourceURI.indexOf(urlBase) === 0) {
        // URL base is a prefix of requested resource URL
        return resourceURI;
    }

    return urlBase + 'view/?r=' + encodeURIComponent(resourceURI);
}

/*
 * Edit a complete OW property view property section
 */
function editProperty(event) {
    var element = $.event.fix(event).target;
    loadRDFauthor(function () {
        RDFauthor.setOptions({
            onSubmitSuccess: function () {
                $('.edit').each(function() {
                    $(this).fadeOut(effectTime);
                });
                $('.edit-enable').removeClass('active');

                // HACK: reload whole page after 1000 ms
                window.setTimeout(function () {
                    window.location.href = window.location.href;
                }, 1000);
            }, 
            onCancel: function () {
                $('.edit').each(function() {
                    $(this).fadeOut(effectTime);
                });
                $('.edit-enable').removeClass('active');
            }, 
            saveButtonTitle: 'Save Changes', 
            cancelButtonTitle: 'Cancel', 
            title: $('.section-mainwindows .window').eq(0).children('.title').eq(0).text(), 
            viewOptions: {
                type: RDFAUTHOR_VIEW_MODE, 
                container: function (statement) {
                    var element = RDFauthor.elementForStatement(statement);
                    var parent  = $(element).closest('div');

                    if (!parent.hasClass('ontowiki-processed')) {
                        parent.children().each(function () {
                            $(this).hide();
                        });
                        parent.addClass('ontowiki-processed');
                    }

                    return parent.get(0);
                }
            }
        });

        RDFauthor.start($(element).parents('td'));
        $('.edit-enable').addClass('active');
        $('.edit').each(function() {
            var button = this;
            $(this).fadeIn(effectTime);
        });
    });

    //return false;
}

function addProperty() {
    var ID = RDFauthor.nextID();
    var td1ID = 'rdfauthor-property-selector-' + ID;
    var td2ID = 'rdfauthor-property-widget-' + ID;

    $('.edit').each(function() {
        $(this).fadeIn(effectTime);
    });

    $('table.rdfa')
        .children('tbody')
        .prepend('<tr><td colspan="2" width="120"><div style="width:75%" id="' + td1ID + '"></div></td></tr>');
    
    var selectorOptions = {
        container: $('#' + td1ID), 
        selectionCallback: function (uri, label) {
            var statement = new Statement({
                subject: '<' + RDFAUTHOR_DEFAULT_SUBJECT + '>', 
                predicate: '<' + uri + '>'
            }, {
                title: label, 
                graph: RDFAUTHOR_DEFAULT_GRAPH
            });
            
            var owURL = urlBase + 'view?r=' + encodeURIComponent(uri);
            $('#' + td1ID).closest('td')
                .attr('colspan', '1')
                .html('<a class="hasMenu" about="' + uri + '" href="' + owURL + '">' + label + '</a>')
                .after('<td id="' + td2ID + '"></td>');
            RDFauthor.getView().addWidget(statement, null, {container: $('#' + td2ID), activate: true});
        }
    };
    
    var selector = new Selector(RDFAUTHOR_DEFAULT_GRAPH, RDFAUTHOR_DEFAULT_SUBJECT, selectorOptions);
    selector.presentInContainer();
}
