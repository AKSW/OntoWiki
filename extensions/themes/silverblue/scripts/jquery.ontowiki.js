/*
 * OntoWiki jQuery extensions
 *
 * @package    theme
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
                toggleExpansion(event);
                return false; // -> event is not given further
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
