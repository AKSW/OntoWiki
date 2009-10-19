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
    this.label = null;
    
    this.remove = false;
}

ResourceEdit.prototype.init = function () 
{
    var instance = this;

    if (this.options.propertyMode) {
        $('#resource-value-' + this.id).autocomplete(function(term, cb) { return ResourceEdit.search(term, cb, true); }, {
            minChars: 3,
            delay: 1000,
            max: 20,
            formatItem: function(data, i, n, term) {
                return '<div style="overflow:hidden">\
						<span style="white-space: nowrap;font-weight: bold">' + data[0] + '</span>\
						<br />\
						<span style="white-space: nowrap;font-size: 0.8em">' + data[1] + '</span>\
						</div>';
            }
        });
        
        $('#resource-value-' + this.id).result(function(e, data, formated) {
            $(this).attr('value', data[1]);
            instance.label = data[0];
        });
    } else {
		$('#resource-value-' + this.id).autocomplete(function(term, cb) { return ResourceEdit.search(term, cb, false); }, {
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

		$('#resource-value-' + this.id).result(function(e, data, formated) {
            $(this).attr('value', data[1]);
        });
	}	
}


ResourceEdit.prototype.getHtml = function() 
{    
	var html = '\
      <div class="container resource-value">\
        <input type="text" id="resource-value-' + this.id +	'" class="text resource-edit-input" value="' + 
		this.object + '" style="width:38em" />\
      </div>';
    
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

    var hasChanged = (newResourceValue != this.object) && (newResourceValue != '');
    if (hasChanged || this.remove) {
        // Remove the old triple
        if (this.object != '') {
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

ResourceEdit.prototype.getValue = function() 
{
    var value = $('#resource-value-' + this.id).val();
    
    return {
        uri: value, 
        title: this.label
    }
}

ResourceEdit.search = function(terms, callbackFunction, propertiesOnly)
{
	// Currently RDFauthor has no generic SPARQL service, so we use the OW service if used with OW.
	// Otherwise we currently only support the sindice search.
	var isOW = false;
	if ($('title').html() === 'OntoWiki') {
		isOW = true;
	}
	
	if (isOW) {
		var url = urlBase + 'datagathering/search?q=' + encodeURIComponent(terms);
		
		if (propertiesOnly) {
			url += '&mode=1';
		}
		
		$.getJSON(url, {}, function(data) {
	        callbackFunction(data);
	    });
	} else {
		ResourceEdit.sindiceSearch(terms, callbackFunction);
	}
}

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
}


RDFauthor.loadScript(widgetBase + 'libraries/jquery.autocomplete.js');
RDFauthor.loadStyleSheet(widgetBase + 'libraries/jquery.autocomplete.css');
RDFauthor.registerWidget({constructorFunction: ResourceEdit, hookName: '__object'});


$(document).ready(function() {
	// Currently nothing to do here.
})
