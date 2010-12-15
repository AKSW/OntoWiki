/**
 * This file is part of the keyboard extension for OntoWiki
 * 
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: $
 *
 */

/* the selector which is used for items inside a context */
owKeyboard = new Array;
owKeyboard['itemSelector'] = 'a.Resource, input';

/**
 * The main document.ready assignments
 */
$(document).ready(function() {
    $("input").focus(function () { return unbindKeyboard() });
    bindKeyboard();
});

/**
 * binds the main keyboard commands
 * TODO: turn the keyboard stuff off while typing in an input
 */
function bindKeyboard () {
    $(document).bind('keydown', 'esc', function(event) { return deactivateWindow(event) });
    $(document).bind('keydown', 'a', function(event) { return activateWindow(event, 'application') });
    $(document).bind('keydown', 'm', function(event) { return activateWindow(event, 'modellist') });
    $(document).bind('keydown', 'c', function(event) { return activateWindow(event, 'hierarchy') });
    return false;
}

/**
 * unbind the main keyboard commands at the first level
 */
function unbindKeyboard () {
    $(document).unbind('keydown', 'esc', function(event) { return deactivateWindow(event) });
    $(document).unbind('keydown', 'a', function(event) { return activateWindow(event, 'application') });
    $(document).unbind('keydown', 'm', function(event) { return activateWindow(event, 'modellist') });
    $(document).unbind('keydown', 'c', function(event) { return activateWindow(event, 'hierarchy') });
    return false;
}

/**
 * Aeactivates any (window) keyboard context
 */
function deactivateWindow(event) {
    $('.activeKeyContext').toggleClass('activeKeyContext');
    $('.activeKeyItem').toggleClass('activeKeyItem');
    $(document).unbind('keydown', 'up', function(event) { return selectPreviousItem(event) });
    $(document).unbind('keydown', 'down', function(event) { return selectNextItem(event) });
    $(document).unbind('keydown', 'return', function(event) { return callMainFunction(event) });
}

/**
 * Activates/Toggles a (window) keyboard context
 */
function activateWindow(event, windowID) {
    if ($('#'+windowID+'.activeKeyContext').size() > 0 ) {
        // deactivate  (all) if the the window context is already active
        return deactivateWindow(event);
    } else {
        // de-activate the last window context
        deactivateWindow(event);

        // activate the new window context
        $('#'+windowID).toggleClass('activeKeyContext');

        // activate first activateable item inside of the context
        $('#'+windowID).find(owKeyboard['itemSelector']).slice(0, 1).toggleClass('activeKeyItem')

        // assign context specific keys (cursor + enter)
        $(document).bind('keydown', 'up', function(event) { return selectPreviousItem(event) });
        $(document).bind('keydown', 'down', function(event) { return selectNextItem(event) });
        $(document).bind('keydown', 'return', function(event) { return callMainFunction(event) });

        return true;
    }
}

/**
 * This function selects the next useable item inside the activated window (context)
 * TODO: go to the first if someone wants the next on the last
 */
function selectNextItem(event) {
    var activeItem = undefined;
    var allItems = $('.activeKeyContext').find(owKeyboard['itemSelector']);
    var itemCount = allItems.size();
    
    allItems.each(function (i) {
        if ($(this).hasClass('activeKeyItem')) {
            activeItem = i;
        }
        if (i == activeItem+1) {
            allItems.removeClass('activeKeyItem');
            $(this).toggleClass('activeKeyItem');
        }
      });
      return false;
}

/**
 * This function selects the previous useable item inside the activated window (context)
 * TODO: go to the last if someone wants the previous on the first
 */
function selectPreviousItem(event) {
    var previousItem = undefined;
    var allItems = $('.activeKeyContext').find(owKeyboard['itemSelector']);
    allItems.each(function (i) {
        if ($(this).hasClass('activeKeyItem')) {
            $(this).toggleClass('activeKeyItem');
            allItems.slice(previousItem,previousItem+1).toggleClass('activeKeyItem');
        }
        previousItem = i;
    });
    return false;
}


/**
 * This function triggers the main function of an activated item, which is mostly the link
 */
function callMainFunction(event) {
    var activeItem = $('.activeKeyContext').find('.activeKeyItem').slice(0, 1);
    if (typeof activeItem.attr('href') != 'undefined') {
        window.location.href = activeItem.attr('href');
    }
    return false;
}