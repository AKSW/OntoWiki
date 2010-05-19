/*
 * This widget is for entering mailto: Resource URIs as for foaf:mbox
 * todo:
 *  - regexp is maybe not fully complete for RFC
 *  - width of input field maybe not ok for inline use (should be relative)
 *
 */

function MailURIEdit(graph, subject, predicate, object, options) {
    var defaultOptions = {
    };
    
    this.options = $.extend(defaultOptions, options);
    
    this.id = RDFauthor.getNextId();
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object ? object.value : '';

    this.label = null;

    this.remove = false;
}

MailURIEdit.prototype.init = function ()
{
    $('#mail-value-' + this.id).keyup(function(event) {
        if (!MailURIEdit.labelValidate( $(event.currentTarget).val()) ) {
            if ($(event.currentTarget).css('color') != 'red') {
                $(event.currentTarget).data('prevColor', $(event.currentTarget).css('color'));
            }
            $(event.currentTarget).css("color","red");
        } else {
            //alert($(event.currentTarget).data('prevColor'));
            $(event.currentTarget).css("color",$(event.currentTarget).data('prevColor'));
        }
    });
    return true;
}

MailURIEdit.prototype.focus = function() {
    $('#mail-value-' + this.id).focus();
};

MailURIEdit.prototype.getHtml = function()
{
    var html = '';
    html += '<div class="container resource-value" style="width:90%">'+
        '<input type="text resource-edit-input"'+
        ' id="mail-value-' + this.id + '"'+
        ' class="text"' +
        ' value="' + MailURIEdit.uri2label(this.object) + '"'+
        ' style="width:90%; background-position: left center; background-image:url(\''+widgetBase+'img/email.png\'); background-repeat:no-repeat; padding-left:22px;" /></div>';

    return html;
}

MailURIEdit.prototype.onCancel = function()
{
    return true;
}

MailURIEdit.prototype.onRemove = function()
{
    this.remove = true;
}

MailURIEdit.prototype.onSubmit = function()
{
    var dataBank = RDFauthor.getDatabank(this.graph);    
    var newResourceValue = MailURIEdit.label2uri( $('#mail-value-' + this.id).val() );
    
    // Widget just added and nothing entered
    if (this.object == '' && newResourceValue == undefined) {
        return true;
    }

    var hasChanged = (newResourceValue != this.object) && (newResourceValue != '');
    if (hasChanged || this.remove) {
        // Remove the old triple
        if (this.object != '' || this.remove) {
            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.resource('<' + this.object + '>')
            );

            dataBank.remove(oldTriple);
        }
        
        if (!this.remove) {
            // Add new triple
            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.resource('<' + newResourceValue + '>')
            );

            dataBank.add(newTriple);
        };
    }
    
    return true;
}


// registration options
var options = {
    constructorFunction: MailURIEdit,
    hookName: 'property',
    hookValues: ['http://xmlns.com/foaf/0.1/mbox']
};

// register with RDFauthor
RDFauthor.registerWidget(options);



//// THIS IS SPECIAL STUFF FOR THIS WIDGET ONLY

/*
 * This function validates phone number string representation
 */
MailURIEdit.labelValidate = function(label)
{
    var phoneRegExp = /^[a-zA-Z_-]+@([0-9a-z-]+\.)+([a-z]){2,5}$/;
    matches = String(label).match(phoneRegExp);
    if (matches) {
        return true;
    } else {
        return false;
    }
};

/*
 * This function creates a userfriendly label from a tel: URI
 */
MailURIEdit.uri2label = function(uri)
{
    var label = String(uri);
    label = label.replace(/mailto:/g, ''); // remove the mailto: prefix
    return label;
};

/*
 * This function (should) create a mailto:-URI from a mail address
 * as well as ensure the mailto RFC
 */
MailURIEdit.label2uri = function(label)
{
    var uri = String(label);
    return 'mailto:' + uri;
};
