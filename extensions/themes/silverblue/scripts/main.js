// namespace for script variables
var OntoWiki = {};

// how fast should we fade, slide, ...
var effectTime = 250;

// integer value of the dragNdrop z-index and context-menu z-index
var dragZIndex = 1000;
var menuZIndex = 1000;

// number of chars entered before autocompleting starts
var autoCompleteMinChars = 3;

// time to wait before autocompleting (ms)
var autoCompleteDelay = 500;

// The id counter is used to create autoids
idCounter = 1;

// This array is used to temp. store href attributes
tempHrefs = new Array();

/*
 * core css assignments
 */
$(document).ready(function() {
    // the body gets a new class to indicate that javascript is turned on
    $('body').removeClass('javascript-off').addClass('javascript-on');

    // the body gets the contextmenu clone container
    $('body').append('<div class="contextmenu-enhanced"></div>');

    // every click fadeout (and remove) all contextmenus
    // every click un-marks all marked elements
    $('html').click(function(){
        $('.contextmenu-enhanced .contextmenu').fadeOut(effectTime, function(){$(this).remove();})
        $('.marked').removeClass('marked');
    });
    
    // add section resizer
    $('.section-sidewindows').append('<span class="resizer-horizontal"></span>');
    
    // give it a nice (non-standard) cursor
    if ($.browser.safari) {
        $('.resizer-horizontal').css('cursor', 'col-resize');
    } else if ($.browser.mozilla) {
        $('.resizer-horizontal').css('cursor', 'ew-resize');
    }
    
    // make resizer draggable
    // draggables need an explicit (inline) position
    $('.section-sidewindows .resizer-horizontal')
        .css('position', 'absolute')
        .draggable({
            axis: 'x', 
            zIndex: dragZIndex,  
            cursor: 'move', 
            start: function(event, ui) {
                $('.section-sidewindows .resizer-horizontal').addClass('dragging');
            }, 
            stop: function(event, ui) {
                var resizerWidth = $('.section-sidewindows .resizer-horizontal').width();
                var sectionRatioPercent = Math.round((((event.pageX) / $(document).width())) * 1000) * 0.1;
                setSectionRatio(sectionRatioPercent);
                sessionStore('sectionRation', sectionRatioPercent, {encode: true});
                $('.window div.cmDiv').adjustClickMenu();
                // jQuery UI bug in Safari
                $('.section-sidewindows').css('position', 'absolute');
                $('.section-sidewindows .resizer-horizontal').removeClass('dragging');
            }});
    
    // resize separator when all ajax crap is loaded
    window.setTimeout(function () {        
        $('.section-sidewindows .resizer-horizontal').height(
            Math.max(
                $(document).height(),
                $(window).height(),
                /* for Opera: */
                document.documentElement.clientHeight
            ) + 'px');
    }, 750);
    
    if (typeof sectionRatio != 'undefined') {
        setSectionRatio(sectionRatio);
    }
    
    /* list selection */
    $('table.resource-list > tbody > tr').live('click', function(e) {
        var selectee     = $(this);
        var selectionURI = $(this).children('td').children('a').attr('about');

        // return if we have no URI (e.g. a Literal list)
        if (typeof selectionURI == 'undefined') {
            return false;
        }

        // return true if user clicked on a link (so the link is fired)
        if ( $(e.target).is('a') ) {
            return true;
        }

        // create array for all selected resources
        if (typeof OntoWiki.selectedResources == 'undefined') {
            OntoWiki.selectedResources = [];
        }
        
        if (!selectee.hasClass('list-selected')) { // select a resource
            // TODO: check for macos UI compability
            if (e.ctrlKey) {
                // ctrl+click for select multiple resources

            } else if (e.shiftKey) {
                // shift+click for select multiple resources in a range
                // not implemented yet
            } else {
                // normal click on unselected means deselect all and select this one
                // deselect all resources
                $('.list-selected').removeClass('list-selected');
                // purge the container array
                OntoWiki.selectedResources = [];
            }

            // add this resource
            selectee.addClass('list-selected');
            OntoWiki.selectedResources.push(selectionURI);
            // event for most recent selection
            $('body').trigger('ontowiki.resource.selected', [selectionURI]);
        } else { // deselect a resource
            // TODO: check for macos UI compability
            if (e.ctrlKey) {
                // ctrl+click on selected means deselect this one
                selectee.removeClass('list-selected');
                var pos = $.inArray(selectionURI, OntoWiki.selectedResources);
                OntoWiki.selectedResources.splice(pos, 1);
            } else if (e.shiftKey) {
                // shift+click for select multiple resources in a range
                // not implemented yet
            } else {
                // normal click on selected means deselect all
                // deselect all resources
                $('.list-selected').removeClass('list-selected');
                // purge the container array
                OntoWiki.selectedResources = [];
            }

            // event for most recent unselection
            $('body').trigger('ontowiki.resource.unselected', [selectionURI]);
        }
        
        // event for all selected
        $('body').trigger('ontowiki.selection.changed', [OntoWiki.selectedResources]);
    });
    
    $('body').bind('ontowiki.resource-list.reloaded', function() {
        // synchronize selection with list style
        $('.resource-list tr').each(function() {
            var resourceURI = $(this).find('*[about]').eq(0).attr('about');
            if ($.inArray(resourceURI, OntoWiki.selectedResources) > -1) {
                $(this).addClass('list-selected');
            }
        })
    })
    
    /* end: list selection */
    
    // inner labels
    $('input.inner-label').innerLabel().blur();
    
    // prefix preserving inputs
    $('input.prefix-value').prefixValue();
    
    $('.editable').makeEditable();
    
    // autosubmit
    $('a.submit').click(function() {
        // submit all forms inside this submit button's parent window
        var formName = $(this).attr('id');
        var formSpec = formName ? '[name=' + formName + ']' : '';
        
        $(this).parents('.window').eq(0).find('form' + formSpec).each(function() {
            if ($(this).hasClass('ajaxForm')) {
                // submit asynchronously
                var actionUrl = $(this).attr('action');
                var method    = $(this).attr('method');
                var data      = $(this).serialize();
                
                if ($(this).hasClass('reloadOnSuccess')) {
                    var mainContent = $(this).parents('.content.has-innerwindows').eq(0).children('.innercontent');
                    var onSuccess = function() {
                        mainContent.load(document.URL);
                    }
                }
                // alert(data);
                if (method == 'post') {
                    $.post(actionUrl, data, onSuccess);
                } else {
                    $.get(actionUrl, data, onSuccess);
                }
                
                this.reset();
            } else {
                // submit normally
                this.submit();
            }
        })
    });
    
    /*
     *  simulate Safari behaviour for other browsers
     *  on return/enter, submit the form
     */
    if (!$.browser.safari) {
        $('.submitOnEnter').keypress(function(event) {
            // return pressed
            if (event.target.tagName.toLowerCase() != 'textarea' && event.which == 13) {
                $(this).parents('form').submit();
            }
        });
    }
    /*
     *  on press enter, this type of textbox looses focus and gives it to the next textfield
     */
    $('.focusNextOnEnter').keypress(function(event) {
        // return pressed
        if (event.target.tagName.toLowerCase() != 'textarea' && event.which == 13) {
            var me = $(this)
            var next = me.next();
            if(next.get(0).tagName.toLowerCase() == me.get(0).tagName.toLowerCase()){
                next.focus();
            } else {
                var next2 = me.parent().next().find('>'+me.get(0).tagName.toLowerCase()+':first')
                if (next2.length != 0){
                    next2.focus();
                } 
            } 
        }
    });
    
    // autosubmit
    $('a.reset').click(function() {
        // reset all forms inside this submit button's parent window
        $(this).parents('.window').find('form').each(function() {
            document.forms[$(this).attr('name')].reset();
        })
    });
    
    // init new resource based on type
    $('.init-resource').click(function() {
        var type       = $(this).closest('.window').find('*[typeof]').eq(0).attr('typeof');
        createInstanceFromClassURI(type);
    });
    
    $('.edit.save').click(function() {
        RDFauthor.commit();
    });
    
    $('.edit.cancel').click(function() {
        // reload page
        window.location.href = window.location.href;
        RDFauthor.cancel();
        // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
        // mainInnerContent.load(document.URL);
        // $('.edit-enable').click();
    });
    
//    $('.icon-edit').click(function() {return editProperty(this)});
    
    // disable inline-editing for not readable models
    if (typeof selectedGraph !== 'undefined' && !selectedGraph.editable) {
        $('.icon-edit').closest('a').remove();
    }
    
    // edit mode
    $('.edit-enable').click(function() {
        var button = this;
        if ($(button).hasClass('active')) {
            RDFauthor.cancel();
            $('.edit').each(function() {
                $(this).fadeOut(effectTime);
            });
            $(button).removeClass('active');
            window.location.href = window.location.href;
        } else {
            if(typeof(RDFauthor) !== 'undefined') {
                RDFauthor.cancel();
            }
            loadRDFauthor(function () {
                RDFauthor.setOptions({
                    onSubmitSuccess: function () {
                        // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
                        // mainInnerContent.load(document.URL);

                        // tell RDFauthor that page content has changed
                        // RDFauthor.invalidatePage();

                        $('.edit').each(function() {
                            $(this).fadeOut(effectTime);
                        });
                        $('.edit-enable').removeClass('active');
                        
                        // HACK: reload whole page after 1000 ms
                        window.setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 500);
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
                        // no statements needs popover
                        type: $('.section-mainwindows table.Resource').length ? RDFAUTHOR_VIEW_MODE : 'popover', 
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
                
                RDFauthor.start();
                
                $('.edit').each(function() {
                    $(this).fadeIn(effectTime, function() {
                        $(button).addClass('active');
                    });
                });
            });
        }
    });
    
    $('.clone-resource').click(function() {
        loadRDFauthor(function () {
            var serviceURI = urlBase + 'service/rdfauthorinit';
            var prototypeResource = selectedResource.URI;
            RDFauthor.reset();

            $.getJSON(serviceURI, {
               mode: 'clone',
               uri: prototypeResource
            }, function(data) {
                // get default resource uri for subjects in added statements (issue 673)
                // grab first object key
                for (var subjectUri in data) {break;};
                
                populateRDFauthor(data, true, subjectUri, selectedGraph.URI);
                
                RDFauthor.setOptions({
                    saveButtonTitle: 'Create Resource',
                    cancelButtonTitle: 'Cancel',
                    title: 'Create New Resource by Cloning ' + selectedResource.title,  
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
            });
        });
    })
    
    // add property
    $('.property-add').click(function() {
        if(typeof(RDFauthor) === 'undefined') {
            loadRDFauthor(function () {
                RDFauthor.setOptions({
                    onSubmitSuccess: function () {
                        // var mainInnerContent = $('.window .content.has-innerwindows').eq(0).find('.innercontent');
                        // mainInnerContent.load(document.URL);

                        // tell RDFauthor that page content has changed
                        // RDFauthor.invalidatePage();

                        $('.edit').each(function() {
                            $(this).fadeOut(effectTime);
                        });
                        $('.edit-enable').removeClass('active');
                        
                        // HACK: reload whole page after 1000 ms
                        window.setTimeout(function () {
                            window.location.href = window.location.href;
                        }, 500);
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
                        // no statements needs popover
                        type: $('.section-mainwindows table.Resource').length ? RDFAUTHOR_VIEW_MODE : 'popover', 
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
                
                RDFauthor.start('html');
                $('.edit-enable').addClass('active');
                setTimeout("addProperty()",500);
            });
        } else {
            addProperty();
        }
        
    });
    
    $('.tabs').children('li').children('a').click(function() {
        var url = $(this).attr('href');
        
        $(this).parents('.tabs').children('li').removeClass('active');
        $(this).parent('li').addClass('active');
                
        if (url.match(/#/)) {
            var wnd = $(this).parents('.window').eq(0);
            wnd.children('div').children('.content').removeClass('active-tab-content');
            wnd.children('div').children('.content' + url).addClass('active-tab-content');
            return false;
        } else {
            return true;
        }
    });
    
    // box display/hide    
    // $('.toggle-module-display').click(function() {
    //     var module = $('.window#' + $(this).attr('id').replace('toggle-', ''));
    //     var menuEntry = $(this);
    //     if (module.length) {
    //         if (module.hasClass('is-disabled')) {
    //             module.removeClass('is-disabled');
    //             module.fadeIn(effectTime, function() {
    //                 menuEntry.text(menuEntry.text().replace('Show', 'Hide'));
    //             })
    //         } else {
    //             module.fadeOut(effectTime, function() {
    //                 module.addClass('is-disabled');
    //                 menuEntry.text(menuEntry.text().replace('Hide', 'Show'));
    //             });
    //         }
    //     }
    // })
    
    
    // make sidebar windows sortable
/*    if ($('.section-sidewindows .window').length) {
        $('.section-sidewindows .window .title').css('cursor', 'move');
        $('.section-sidewindows').sortable({
            items: '.window', 
            handle: '.title', 
            // containment: 'parent', 
            opacity: 0.8, 
            axis: 'y', 
            cursor: 'move', 
            revert: true, 
            start: function(event, ui) {
                ui.helper.css('width', $('.section-sidewindows .window').eq(0).width() + 'px');
                ui.helper.css('margin-left', '0');
            }, 
            update: function() {
                var moduleOrder = $('.section-sidewindows').sortable('serialize', {
                    expression: '(.*)', 
                    key: 'value'
                });
                sessionStore('moduleOrder', moduleOrder, {encode: false, namespace: 'Module_Registry'});
            }
        });
        // draggables need an explicit (inline) position
        $('.section-sidewindows').css('position', 'absolute');
    }
*/
    
    // make tabs sortable
    // if ($('#tabs').children().length) {
    //     $('#tabs').sortable({
    //         axis: 'x', 
    //         // containment: 'parent', 
    //         opacity: 0.8, 
    //         revert: true, 
    //         update: function() {
    //             var tabOrder = $('#tabs').sortable('serialize', {
    //                 expression: '(.*)', 
    //                 key: 'value'
    //             });
    //             sessionStore('tabOrder', tabOrder, {encode: false, namespace: 'ONTOWIKI_NAVIGATION'});
    //         }
    //     });
    // }
    
    // inline widgets
    // $('.inline-edit-local').live('click', function() {
    //     RDFauthor.startInline($(this).closest('.editable').get(0));
    // });
    
    $('.hidden').hide();
    
    //-------------------------------------------------------------------------
    //---- liveQuery triggers
    //-------------------------------------------------------------------------
    
    // expandables
    $('.expandable').livequery(function() {
        $(this).expandable();
    });

    // create showResourceMenu toogle where wanted and applicable
    $('a.hasMenu[about]').livequery(function() {
        $(this).createResourceMenuToggle();
    });
    $('a.hasMenu[resource]').livequery(function() {
        $(this).createResourceMenuToggle();
    });

    // All RDFa elements with @about or @resource attribute are resources
    $('*[about]').livequery(function() {
        $(this).addClass('Resource');
    });
    $('*[resource]').livequery(function() {
        $(this).addClass('Resource');
    });
    
    
    var liveSearchMinChars = 3;
    var liveSearchTimeout  = 250; // ms
    var count = 0;
    
    // live-search
    $('input.live-search').livequery('keyup', function() {
        var localCount = ++count;
        var searchInput = $(this);
        
        window.setTimeout(function() {
            // no more input, so do something
            if (count == localCount) {
                if (($(searchInput).val().length >= liveSearchMinChars)) {
                    $(searchInput).parents('.content').children('ul').hide();
                    if ($(searchInput).parents('.content').children('.messagebox').length < 1) {
                        $(searchInput).parents('.content').append(
                            '<div style="display:none" class="messagebox info">Not implemented yet.</div>');
                        $(searchInput).parents('.content').children('.messagebox').fadeIn(effectTime);
                    }
                } else {
                    // load normal hierarchy
                    $(searchInput).parents('.content').children('ul').fadeIn(effectTime);
                    $(searchInput).parents('.content').children('.messagebox').remove();
                }                
            }
        }, liveSearchTimeout);
    });
    
    /* RESOURCE CONTEXT MENUS */
    $('.has-contextmenus-block .Resource').livequery(function() {
        $(this).append('<span class="button"></span>');
    });
    
    $('.has-contextmenus-block .Resource span.button').livequery(function() {
        $(this).mouseover(function() {
            hideHref($(this).parent());
            $('.contextmenu-enhanced .contextmenu').remove(); // remove all other menus
        })
        .click(function(event) {
            showResourceMenu(event);
        }).mouseout(function() {
            showHref($(this).parent())
        });
    })
    
    var loadChildren = function(li) {
        var ul;
        var a   = $(li).children('.hierarchy-toggle');
        var uri = $(li).children('.has-children').attr('about');
        
        var toggleDisplay = function(ul) {
            if (ul.css('display') != 'none') {
                ul.slideUp(effectTime, function() {
                    a.removeClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'unset', withValue: true});
                });
            } else {
                ul.slideDown(effectTime, function() {
                    a.addClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'push', withValue: true});
                });
            }
        }
        
        var serviceUrl = urlBase + 'service/hierarchy?entry=' + encodeURIComponent(uri);
        $.get(serviceUrl, function(data) {
            ul = $(data);
            ul.css('display', 'none');
            $(li).append(ul);
            toggleDisplay(ul);
        })
    }
    
    $('ul .hierarchy .has-children').livequery(function() {
        // is open and should have children but has none
        if ($(this).prev('.hierarchy-toggle').hasClass('open') && $(this).parent().children('ul').length < 1) {
            loadChildren($(this).parent());
        }
    });
    
    $('.hierarchy-toggle').livequery('click', function(event) {
        var ul;
        var a   = $(this);
        var uri = a.next().attr('about');
        
        var toggleDisplay = function(ul) {
            if (ul.css('display') != 'none') {
                ul.slideUp(effectTime, function() {
                    a.removeClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'unset', withValue: true});
                });
            } else {
                ul.slideDown(effectTime, function() {
                    a.addClass('open');
                    sessionStore('hierarchyOpen', 'value=' + encodeURIComponent(uri), {method: 'push', withValue: true});
                });
            }
        }
        
        if ($(this).parent('li').children('ul').length < 1) {
            // TODO: Ajax
            var serviceUrl = urlBase + 'service/hierarchy?entry=' + encodeURIComponent(uri);
            $.get(serviceUrl, function(data) {
                ul = $(data);
                ul.css('display', 'none');
                a.parent('li').append(ul);
                toggleDisplay(ul);
            })
        } else {
            ul = a.parent('li').children('ul');
            toggleDisplay(ul);
        }
        
        event.stopPropagation();
    })
    
    $('tbody a.toggle').live('click', function() {
        $(this).closest('tbody').toggleClass('closed');
    })
    
    // site is ready, processing is finished
    $('body').removeClass('is-processing');

    // enhance every window with buttons, menu and resizer
    // this must be done at the end of the onready block (because we generate the menu automatically)
    $('.window').enhanceWindow();
    
    // adjust neede space for clickmenu
    $('.window div.cmDiv').adjustClickMenu();
    
}) // $(document).ready

