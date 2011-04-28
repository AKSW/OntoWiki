/**
 * distributed semantic social network client (JavaScript components)
 *
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 *
 */

/*jslint white: true, onevar: true, undef: true, nomen: true, regexp: true, plusplus: true, bitwise: true, newcap: true, maxerr: 50, indent: 4 */
/*global $: true, document: true*/

/*
 * Creates a menu which offers different Activity Actions
 * - Comment this activity
 * - Like this activity
 * - Delete this activity
 * -
 * - Re-share this resource
 * - Comment this resource
 * - Like this resource
 *
 * - Ignore activities from User1
 * - Ignore activities of type 'shared the following'
 * - Ignore 'shared the following' activities from User1
 * -
 */
function dssnActivityOptions(event) {
    //alert('create menu here');
    event.preventDefault();
}

/*
 * A generic callback used by dssnSendActivity
 * - adds an info paragraph to the form (context)
 */
function dssnSendActivityCallback(jqXHR, textStatus, context) {
    var text, boxClass, html;
    // remove the spinner and de-focus the input field
    $(context).find('input.dssn').removeClass('is-processing').blur();

    // add information paragraph
    message  = jQuery.parseJSON(jqXHR.responseText).message;
    if (textStatus === 'success') {
        text     = 'Activity successfully sent';
        boxClass = 'success';
    } else {
        text     = 'Activity NOT sent (' + jqXHR.statusText + ')';
        boxClass = 'error';
    }
    html = '<p title="' + message + '" class="messagebox ' + boxClass + '">' + text + '</p>';
    $(context).append(html);
}

/*
 * send a new activity async to save
 */
function dssnSendActivity(event) {
    var target, form, url, data;
    // do not send until user pressed enter
    if (event.which === 13) {
        target = $(event.target);
        form   = target.parents('form');
        url    = $(form).attr('action');
        data   = $(form).serializeArray();

        target.addClass('is-processing');
        target.removeAttr('value');

        $.ajax({
            type: 'POST',
            context: form,
            url: url,
            datatype: 'json',
            data: data,
            complete: function (jqXHR, textStatus) {
                dssnSendActivityCallback(jqXHR, textStatus, this);
            }
        });

        event.preventDefault();
    }
}

/*
 * assign events to DOM nodes
 */
$(document).ready(function() {
    // all input elements in dssn activity forms
    $('input.dssn').keypress(function (event) {
        dssnSendActivity(event);
    });
});

