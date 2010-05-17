/*
 * This widget is for entering tel: Resource URIs as for foaf:phone
 * todo:
 *  - regexp is maybe not fully complete for RFC
 *  - width of input field maybe not ok for inline use (should be relative)
 *
 */
function TelURIEdit(graph, subject, predicate, object, options) {    
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

TelURIEdit.prototype.init = function ()
{
    $('#phone-value-' + this.id).keyup(function(event) {
        if (!TelURIEdit.labelValidate( $(event.currentTarget).val()) ) {
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

TelURIEdit.prototype.focus = function() {
    $('#phone-value-' + this.id).focus();
};

TelURIEdit.prototype.getHtml = function()
{
    var html = '';
    html += '<div class="container resource-value" style="width:90%">'+
        '<input type="text"'+
        ' id="phone-value-' + this.id + '"'+
        ' class="text" size="20"' +
        ' value="' + TelURIEdit.uri2label(this.object) + '"'+
        ' style="background-position: left center; background-image:url(\''+widgetBase+'img/phone.png\'); background-repeat:no-repeat; padding-left:20px;" /></div>';

    return html;
}

TelURIEdit.prototype.onCancel = function()
{
    return true;
}

TelURIEdit.prototype.onRemove = function()
{
    this.remove = true;
}

TelURIEdit.prototype.onSubmit = function()
{
    var dataBank = RDFauthor.getDatabank(this.graph);    
    var newResourceValue = TelURIEdit.label2uri( $('#phone-value-' + this.id).val() );
    
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
    constructorFunction: TelURIEdit,
    hookName: 'property',
    hookValues: ['http://xmlns.com/foaf/0.1/phone', 'http://purl.org/net/ldap#mobile', 'http://purl.org/net/ldap#homePhone','http://purl.org/net/ldap#telephoneNumber', 'http://purl.org/net/ldap#fax']
};

// register with RDFauthor
RDFauthor.registerWidget(options);



//// THIS IS SPECIAL STUFF FOR THIS WIDGET ONLY

/*
 * This function validates phone number string representation
 */
TelURIEdit.labelValidate = function(label)
{
    var phoneRegExp = /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/;
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
TelURIEdit.uri2label = function(uri)
{
    var label = String(uri);
    label = label.replace(/tel:/g, ''); // remove the tel: prefix
    label = label.replace(/-/g, ' '); // create spaces
    return label;
};

/*
 * This function (should) create a tel:-URI from a phone number string
 * as well as ensure RFC3966 (http://www.rfc-editor.org/rfc/rfc3966.txt)
 */
TelURIEdit.label2uri = function(label)
{
    var uri = String(label);
    uri = uri.replace(/\ /g, '-'); // create - instead of spaces
    return 'tel:' + uri;
};
