/*
 * OntoWiki jQuery extensions
 *
 * @package    theme
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: jquery.ontowiki.js 4167 2009-09-14 12:51:57Z norman.heino $
 */
(function($) {
    
    /**
     *  Enhances input fields with inner labels
     */
    $.fn.innerLabel = function() {
        return this.each(function() {
            // the input field
            var input = $(this);
            // the associated label element
            var label = $('label[for=' + input.attr('id') + ']');
            // the label text
            var labelText = label.text();
            
            if (typeof label != 'undefined') {
                input.focus(function() {
                    // if if label text is input's only content, set it empty
                    if (input.val() == labelText) {
                        input.val('');
                    }
                }).blur(function() {
                    // if nothing has been entered, set label text
                    if (input.val() == '') {
                        input.val(labelText);
                    }
                })
                
                label.addClass('onlyAural');
            }
        });
    }
    
    /**
     * Enhances input fields where the predefined value must be kept as a
     * prefix for any value entered.
     */
    $.fn.prefixValue = function() {
        return this.each(function() {
            var input = $(this);
            var prefix = input.val();
            
            input.keyup(function() {
                if (!input.val().match(prefix)) {
                    input.val(prefix);
                }
            });
            
            input.blur(function() {
                if (!input.val().match(prefix)) {
                    input.val(prefix);
                }
            });
        });
    }
    
    /**
     * Enhances windows with desktop-style GUI elements
     */
    $.fn.enhanceWindow = function() {
        return this.each(function() {
            var win = $(this);

            // add window buttons
            win.children('.window-buttons').remove();
            win.append('<div class="window-buttons"><div class="window-buttons-left"></div><div class="window-buttons-right"></div></div>');

            win.find('.window-buttons-right').append('<span class="button button-windowminimize"></span>');
            win.addClass('windowbuttonscount-right-1');

            if (win.hasClass('is-minimized')) {
                win.find('.button-windowminimize')
                    .removeClass('button-windowminimize')
                    .addClass('button-windowrestore');
            }

            // minimize
            win.find('.button-windowminimize').click(function() {
                win.toggleWindow();
            })
            // restore
            win.find('.button-windowrestore').click(function() {
                win.toggleWindow();
            })
            // minimize/maximize on title
            // win.find('.title').dblclick(function() {
            //     win.toggleWindow();
            // })
            
            // context menu button
            if (win.children('div').children('.contextmenu').length) {
                win.find('.window-buttons-left').append('<span class="button button-contextmenu"></span>');
                win.addClass('windowbuttonscount-left-1');

                // context menu action
                win.find('.button-contextmenu').click(function(event) {
                    showWindowMenu(event);
                })
            }

            // add menu
            if (win.children('div').children('ul.menu').length) {
                win.addClass('has-menu');
                win.children('div').children('ul.menu').clickMenu();
            }

            // create the additional tabbed class
            if (win.children('div').children('.tabs').length > 0) {
                win.addClass('tabbed');
                
                if (win.children('div').children('.active-tab-content').length == 0) {
                    win.children('div').children('.content').eq(0).addClass('active-tab-content');
                }
            }
            return win;
        });
    }
    
    /**
     * Minimizes/restores a window
     */
    $.fn.toggleWindow = function() {
        var win = this;
        
        if (win.hasClass('is-minimized')) {
            // TODO: why is this necessary
            win.children('.slidehelper').hide();
            win.removeClass('is-minimized');

            if (win.hasClass('has-menu-disabled')) {
                win.removeClass('has-menu-disabled').addClass('has-menu');
            }

            win.children('.slidehelper')
                .slideDown(effectTime, function() {
                    win.find('.button-windowrestore')
                        .removeClass('button-windowrestore')
                        .addClass('button-windowminimize');
                    }
                );

            win.find('div.cmDiv').adjustClickMenu();

            sessionStore(win.attr('id'), 1, {encode: true, namespace: 'Module_Registry'});
        } else {
            win.find('h1.title').attr('style', '');
            win.children('.slidehelper')
                .slideUp(effectTime, function() {
                    win.find('.button-windowminimize')
                        .removeClass('button-windowminimize')
                        .addClass('button-windowrestore');

                        if (win.hasClass('has-menu')) {
                            win.removeClass('has-menu').addClass('has-menu-disabled');
                        }
                        win.addClass('is-minimized');
                    }
                );

            sessionStore(win.attr('id'), 2, {encode: true, namespace: 'Module_Registry'});
        }
    }
    
    /**
     * Make link expandable
     */
    $.fn.expandable = function() {
        return this.each(function() {
            if (!$(this).prev().hasClass('collapse')) {
                $(this).before('<span class="icon-button expand"></span>');
            }
            $(this).prev().click(function(event) {
                toggleExpansion(event)
            });
        })
    }

    /**
     * Enhance link with a menu toogle for showResourceMenu
     */
    $.fn.createResourceMenuToggle = function() {
        return this.each(function() {
            //if (!$(this).find('span.toggle')) {
                $(this).append('<span class="toggle" title="Menu"></span>');
            //}
            $(this).children('span.toggle')
                .mouseover(function() {
                    hideHref($(this).parent());
                    $('.contextmenu-enhanced .contextmenu').remove(); // remove all other menus
                })
                .click(function(event) {
                    showResourceMenu(event);
                })
                .mouseout(function() {
                    showHref($(this).parent())
                });
        })
    }
    
    /**
     * Make inline elements editable.
     */
    $.fn.makeEditable = function () {
         return this.each(function() {
             if($(this).hasClass('editable')){
                 $(this).addClass('has-contextmenu-area').css('display', 'block');

                 if ($(this).children('.contextmenu').length < 1) {
                     $(this).append('<div class="contextmenu"></div>');
                 }

                 $(this).children('.contextmenu').append('\
                     <div class="item">\
                         <span class="icon icon-edit" title="Edit these values">\
                         </span>\
                         <!--span class="icon icon-delete" title="Delete all values">\
                         </span-->\
                     </div>\
                 ');
             }
         })
    }
    
    /**
     * Checks whether two elements are equal
     */
    $.fn.equals = function (element) {
        return this.each(function () {
            
        });
    }

    /**
     * adjust the space what is needed by the window menu
     */
    $.fn.adjustClickMenu = function () {
        return this.each(function () {
            var menu = $(this);
            menu.parents('div.window').children('h1.title').attr('style', 'margin-bottom:'+menu.outerHeight(true)+'px !important;');
        });
    }

})(jQuery);

//-----------------------------------------------------------------------------
// Defaults
//-----------------------------------------------------------------------------

// set defaults for clickmenu
$.fn.clickMenu.setDefaults({arrowSrc: themeUrlBase + 'images/submenu-indicator.png'});

//-----------------------------------------------------------------------------
//------- old funcs
//-----------------------------------------------------------------------------

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
//			success: function(msg){alert( 'Data Saved: ' + msg );}
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


function showResourceMenu(event) {
    // remove all other menus
    removeResourceMenus();
    
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
	
	parentHref = tempHrefs[$(event.target).parent().attr('id')];
    
    menuX  = event.pageX - 30;
	menuY  = event.pageY - 20;
	menuId = 'windowmenu-' + menuX + '-' + menuY;
	
	// create the plain menu with correct style and position
	$('.contextmenu-enhanced').append('<div class="contextmenu is-processing" id="' + menuId + '"></div>');
	$('#' + menuId)
	    .attr({style: 'z-index: ' + menuZIndex + '; top: ' + menuY + 'px; left: ' + menuX + 'px;'})
		.click(function(event) {event.stopPropagation();});

    $('#' + menuId).fadeIn();

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
        success: function(data, textStatus) {
            try {

                menuData = $.evalJSON(data);

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
    });

    // prevent href trigger
    event.stopPropagation();
}

/**
 * Loads RDFauthor if necessary and executes callback afterwards.
 */
function loadRDFauthor(callback) {
    var loaderURI = RDFAUTHOR_BASE + 'src/rdfauthor.js';
    
    if ($('head').children('script').children('@src=' + loaderURI).length > 0) {
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
           // if there is a given callback method, start it
           if (typeof dataCallback != 'undefined') {
               data = dataCallback(data);
           }

           // get default resource uri for subjects in added statements (issue 673)
           // grab first object key
           for (var subjectUri in data) {break;};

           RDFauthor.setOptions({
               defaultResource: subjectUri, 
               anchorElement: '.innercontent',
               onSubmitSuccess: function () {
                   // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
                   // mainInnerContent.load(document.URL);

                   // tell RDFauthor that page content has changed
                   // RDFauthor.invalidatePage();

                   $('.edit').each(function() {
                       $(this).fadeOut(effectTime);
                   });
                   $('.edit-enable').removeClass('active');

                   // reload whole page
                   window.location.href = window.location.href;
               },
               onCancel: function () {
                   $('.edit').each(function() {
                       $(this).fadeOut(effectTime);
                   });
                   $('.edit-enable').removeClass('active');
               },
               saveButtonTitle: 'Save Changes',
               cancelButtonTitle: 'Cancel',
               title: 'Create new ' + type
           });

           RDFauthor.startTemplate(data);
        })
    });
}

/*
 * get the rdfa init description from the service in clone mode and start the
 * RDFauthor window
 * TODO: merge it with createInstanceFromClassURI!!
 */
function createInstanceFromURI(resource) {
    var serviceUri = urlBase + 'service/rdfauthorinit';

    // remove resource menus
    removeResourceMenus();
    
    loadRDFauthor(function() {
        $.getJSON(serviceUri, {
           mode: 'clone',
           uri: resource
        }, function(data) {
            // grab first object key
            for (var subjectUri in data) {break;};
            RDFauthor.setOptions({
                defaultResource: subjectUri, 
                anchorElement: '.innercontent',
                onSubmitSuccess: function () {
                   // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
                   // mainInnerContent.load(document.URL);

                   // tell RDFauthor that page content has changed
                   // RDFauthor.invalidatePage();

                   $('.edit').each(function() {
                       $(this).fadeOut(effectTime);
                   });
                   $('.edit-enable').removeClass('active');

                   // reload whole page
                   window.location.href = window.location.href;
                },
                onCancel: function () {
                   $('.edit').each(function() {
                       $(this).fadeOut(effectTime);
                   });
                   $('.edit-enable').removeClass('active');
                },
                saveButtonTitle: 'Create New Resource',
                cancelButtonTitle: 'Cancel',
                title: 'Clone Resource ' + resource
                });
            
            RDFauthor.startTemplate(data);
        })
    });
}
