function ResourceEdit(graph, subject, predicate, object, options) {    
    var defaultOptions = {
        propertyMode: false
    };
    
    this.options = $.extend(defaultOptions, options);
    
    this.id = RDFauthor.getNextId();
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object ? object.value : '';

    // @todo: try to use the graph to identify the label
    if (this.options.objectLabel) {
        this.objectLabel = this.options.objectLabel;
    } else if (object) {
        this.objectLabel = object.value;
    } else {
        this.objectLabel = '';
    }
    this.label = null;
    
    this.remove = false;
}

ResourceEdit.prototype.init = function () 
{
    var instance = this;

    // if (this.options.propertyMode) {
    //     $('#resource-value-' + this.id).autocomplete(function(term, cb) { return ResourceEdit.search(term, cb, true, instance.graph, instance.predicate); }, {
    //         minChars: 3,
    //         delay: 1000,
    //         max: 20,
    //         formatItem: function(data, i, n, term) {
    //             return '<div style="overflow:hidden">\
    //                 <span style="white-space: nowrap;font-weight: bold">' + data[0] + '</span>\
    //                 <br />\
    //                 <span style="white-space: nowrap;font-size: 0.8em">' + data[1] + '</span>\
    //                 </div>';
    //         }
    //     });
    //     
    //     $('#resource-value-' + this.id).result(function(e, data, formated) {
    //         $(this).attr('value', data[1]);
    //         instance.label = data[0];
    //     });
    // } else {
        $('#resource-name-' + this.id).autocomplete(
            function(term, cb) {
                var ret = ResourceEdit.search(term, cb, false, instance.graph, instance.predicate);
                
                if (undefined === ret) {
                    $('#resource-value-' + this.id).val($('#resource-name-' + this.id).val());
                }
                
                return ret;
            },
            {
                minChars: 3,
                delay: 1000,
                max: 20,
                formatItem: function(data, i, n, term) {
                    return '<div style="overflow:hidden">\
                        <span style="white-space: nowrap;font-size: 0.8em">' + data[2] + '</span>\
                        <br />\
                        <span style="white-space: nowrap;font-weight: bold">' + data[0] + '</span>\
                        <br />\
                        <span style="white-space: nowrap;font-size: 0.8em">' + data[1] + '</span>\
                        </div>';
            }
        });

        $('#resource-name-' + this.id).result(function(e, data, formated) {
            $(this).attr('value', data[0]);
            $(this).attr('title', data[1]);
            $(this).prev().attr('value', data[1]);
        });
    // }
}

ResourceEdit.prototype.focus = function() {
    $('#resource-name-' + this.id).focus();
};

ResourceEdit.prototype.getHtml = function() 
{
    var html = '';
    if (this.options.propertyMode) {
        html += '<div class="container resource-value" style="width:90%">'+
            '<input type="text"'+
            ' id="resource-value-' + this.id + '"'+
            ' class="text resource-edit-input"'+
            ' value="' +this.object + '"'+
            ' style="width:100%;" />'+
            '</div>';
    } else {
        html += '<div class="container resource-value" style="width:90%">'+
            '<input type="text resource-edit-input"'+
            ' id="resource-value-' + this.id + '"'+
            ' class="text resource-edit-input"'+
            ' value="' +this.object + '"'+
            ' style="width:100%; display:none" />'+
            '<input type="text"'+
            ' id="resource-name-' + this.id + '"'+
            ' class="text"'+
            ' value="' + this.objectLabel + '"'+
            ' title="' + this.object + '"'+
            ' style="width:100%" />'+
            '</div>';
    }
    
    return html;
}

ResourceEdit.prototype.onCancel = function() 
{
    return true;
}

ResourceEdit.prototype.onRemove = function() 
{
    this.remove = true;
}

ResourceEdit.prototype.onSubmit = function() 
{
    var dataBank = RDFauthor.getDatabank(this.graph);    
    var newResourceValue = $('#resource-value-' + this.id).val();
    
    if (!newResourceValue) {
        // try name
        newResourceValue = $('#resource-name-' + this.id).val();
    }
    
    // Widget added an nothing entered
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
            // alert('Removed: ' + oldTriple);
        }
        
        if (!this.remove) {
            // Add new triple
            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.resource('<' + newResourceValue + '>')
            );

            dataBank.add(newTriple);
        }
    }
    
    return true;
}

ResourceEdit.prototype.getValue = function() 
{
    var value = $('#resource-value-' + this.id).val();
    
    return {
        uri: value, 
        title: this.label
    }
}

ResourceEdit.search = function(terms, callbackFunction, propertiesOnly, graph, predicate)
{
    // Currently RDFauthor has no generic SPARQL service, so we use the OW service if used with OW.
    // Otherwise we currently only support the sindice search.	
    if (typeof urlBase != 'undefined') {
        var url = urlBase + 'datagathering/search?q=' + encodeURIComponent(terms);
        if (propertiesOnly) {
            url += '&mode=1';
        }
        
        var classHint = RDFauthor.getPredicateInfo(predicate, 'ranges');
        if (classHint != undefined) {
            url = url + '&classes=' + encodeURIComponent($.toJSON(classHint));
        }
        
        $.getJSON(url + '&callback=?', 
            function(data) {
                callbackFunction(data);
            }
        );
    } else {
        ResourceEdit.sindiceSearch(terms, callbackFunction);
    }
};

ResourceEdit.sindiceSearch = function(terms, callbackFunction) 
{
    $.getJSON('http://api.sindice.com/v2/search?q=' + encodeURIComponent(terms) + '&qt=term&page=1&format=json&callback=?', 
        function(jsonData) {
            var dataString = "";
            for (var i=0; i<jsonData.entries.length; ++i) {
                var titleString = new String(jsonData.entries[i].title);
                var linkString = new String(jsonData.entries[i].link);
                dataString += titleString + '|' + linkString + '|Sindice Search\n';
            }
            
            callbackFunction(dataString);
        });
};


RDFauthor.loadScript(widgetBase + 'libraries/jquery.autocomplete.js');
RDFauthor.loadStyleSheet(widgetBase + 'libraries/jquery.autocomplete.css');
RDFauthor.registerWidget({constructorFunction: ResourceEdit, hookName: '__object'});
