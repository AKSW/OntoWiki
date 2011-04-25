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
function dssnActivityOptions (event) {
    alert('create menu here');
    event.preventDefault();
}

/*
 * A generic callback used by dssnSendActivity
 * - adds an info paragraph to the form (context)
 */
function dssnSendActivityCallback (jqXHR, textStatus, context) {
    // remove the spinner and de-focus the input field
    $(context).find('input.dssn').removeClass('is-processing').blur();

    // add information paragraph
    if (textStatus == 'success') {
        text     = 'Activity successfully sent';
        boxClass = 'success';
    } else {
        text     = 'Activity NOT sent (' + textStatus + ')';
        boxClass = 'error';
    }
    html = '<p class="messagebox ' + boxClass + '">' + text + '</p>';
    $(context).append(html);
}

/*
 * send a new activity async to save
 */
function dssnSendActivity (event) {
    // do not send until user pressed enter
    if (event.which == 13) {
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
            data: data,
            complete: function (jqXHR, textStatus) {
                dssnSendActivityCallback (jqXHR, textStatus, this);
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
    $('input.dssn')
        .keypress(function(event) { dssnSendActivity(event); });
});

