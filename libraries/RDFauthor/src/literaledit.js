function LiteralEdit(graph, subject, predicate, object) {
    
    var rdfNS = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    var xsdNS = 'http://www.w3.org/2001/XMLSchema#';
    
    this.ns = {};
    
    this.id = RDFauthor.getNextId();
    this.disclosureId = RDFauthor.getNextId();
    
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.languages = RDFauthor.getLiteralLanguages();
    this.datatypes = RDFauthor.getLiteralDatatypes();
    
    if ($('table.Resource').length) {
        var ns = $('table.Resource').xmlns();
        for (var prefix in ns) {
            if (ns[prefix] == rdfNS || ns[prefix] == xsdNS) {
                this.ns[prefix] = ns[prefix];
            }
        }
    }
    
    this.languages.unshift('');
    this.datatypes.unshift('');
    
    if (object) {
        this.object   = object.value;
        this.language = object.lang ? object.lang : '';
        this.datatype = object.datatype ? object.datatype : '';
    } else {
        this.object   = '';
        this.language = '';
        this.datatype = '';
    }
    
    this.remove = false;
}

LiteralEdit.prototype.getHtml = function() {
    var widget = this;
    function shortArea() {
        return '\
            <div class="container literal-value">\
                <textarea rows="1" cols="20" style="width:16em;height:1.4em;padding-top:0.2em" id="literal-value-' + 
                    widget.id + '">' + widget.object + '</textarea>\
            </div>\
            <div class="container util">\
                <a class="disclosure-button disclosure-button-horizontal open" id="' + widget.disclosureId + '" title="Toggle details disclosure"></a>\
            </div>';
    };
    
    function longArea() {
        return '\
            <div class="container literal-value">\
                <textarea rows="3" cols="20" style="width:38em" id="literal-value-' + 
                    widget.id + '">' + widget.object + '</textarea>\
            </div>\
            <div class="container util" style="clear:left">\
                <a class="disclosure-button disclosure-button-vertical open" id="' + widget.disclosureId + '" title="Toggle details disclosure"></a>\
            </div>';
    }
    var html = '\
        ' + (this.isLarge() ? longArea() : shortArea()) + '\
        <div class="container literal-type util ' + this.disclosureId + '" style="display:none">\
            <label><input type="radio" class="radio" name="literal-type-' + this.id + '"' + (this.datatype ? '' : ' checked="checked"') + ' value="plain" />Plain</label>\
            <label><input type="radio" class="radio" name="literal-type-' + this.id + '"' + (this.datatype ? ' checked="checked"' : '') + ' value="typed" />Typed</label>\
        </div>\
        <div class="container util ' + this.disclosureId + '" style="display:none">\
            <div class="literal-lang"' + (this.datatype ? ' style="display:none"' : '') + '>\
                <label for="literal-lang-' + this.id + '">Language:\
                    <select id="literal-lang-' + this.id + '" name="literal-lang-' + this.id + '">\
                        ' + this.makeOptionString(this.languages, this.language) + '\
                    </select>\
                </label>\
            </div>\
            <div class="literal-datatype"' + (this.datatype ? '' : ' style="display:none"') + '>\
                <label>Datatype:\
                    <select id="literal-datatype-' + this.id + '" name="literal-datatype-' + this.id + '">\
                        ' + this.makeOptionString(this.datatypes, this.datatype, true) + '\
                    </select>\
                </label>\
            </div>\
        </div>';
    
    return html;
};

LiteralEdit.prototype.initDisplay = function () {
    if (!this.isLarge()) {
        $('textarea#literal-value-' + this.id).autoResize({extraSpace: 0});
    } else {
        $('textarea#literal-value-' + this.id).autoResize({extraSpace: 10}).trigger('change');
    }
};

LiteralEdit.prototype.isLarge = function () {
    return ((this.object.length >= 50) || this.object.match(/\n/));
}

LiteralEdit.prototype.makeOptionString = function(options, selected, replaceNS) {
    replaceNS = replaceNS || false;
    
    var optionString = '';
    for (var i = 0; i < options.length; i++) {
        var display = options[i];
        if (replaceNS) {
            for (var s in this.ns) {
                if (options[i].match(this.ns[s])) {
                    display = options[i].replace(this.ns[s], s + ':');
                    break;
                }
            }
        }
        
        var current = options[i] == selected;
        if (current) {
            // TODO: do something
        }
        optionString += '<option value="' + options[i] + '"' + (current ? 'selected="selected"' : '') + '>' + display + '</option>';
    }
    
    return optionString;
};

LiteralEdit.prototype.onCancel = function() {
    return true;
};

LiteralEdit.prototype.onRemove = function() {
    this.remove = true;
};

LiteralEdit.prototype.onSubmit = function() {
    var dataBank = RDFauthor.getDatabank(this.graph);
    
    // get new values
    var newObjectValue    = $('#literal-value-' + this.id).val();
    var newObjectLang     = $('#literal-lang-' + this.id + ' option:selected').eq(0).val();
    var newObjectDatatype = $('#literal-datatype-' + this.id + ' option:selected').eq(0).val();
    
    var somethingChanged = (newObjectValue != this.object) || (newObjectLang != this.language) || (newObjectDatatype != this.datatype);
    
    if (somethingChanged || this.remove) {
        // remove old triple
        if (this.object !== '') {
            var objectOptions = {};
            var object = this.object;
            
            if (this.datatype !== '') {
                objectOptions.datatype = this.datatype;
            } else if (this.language !== '') {
                objectOptions.lang = this.language;
            } else {
                object = '"' + this.object + '"';
            }

            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(object, objectOptions)
            );

            dataBank.remove(oldTriple);
        }
        
        if (!this.remove) {
            // add new triple
            var newObjectOptions = {};
            var newObject = newObjectValue;

            if (newObjectLang !== '') {
                newObjectOptions.lang = newObjectLang;
            } else if (newObjectDatatype !== '') {
                newObjectOptions.datatype = newObjectDatatype;
            } else {
                newObject = '"' + newObjectValue + '"';
            }

            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(newObject, newObjectOptions)
            );

            dataBank.add(newTriple);
        }
    }
    
    return true;
};

RDFauthor.loadScript(widgetBase + 'libraries/autoresize.jquery.min.js');
RDFauthor.registerWidget({constructorFunction: LiteralEdit, hookName: '__literal'});

$(document).ready(function() {
    $('.literal-type .radio').live('click', function() {
        var jDatatypeDiv = $(this).parents('.widget').children().find('.literal-datatype');
        var jLangDiv     = $(this).parents('.widget').children().find('.literal-lang');
        
        if ($(this).val() == 'plain') {
            jDatatypeDiv.hide();
            jLangDiv.show();
            // clear datatype
            jDatatypeDiv.find('select').val('');
        } else {
            jDatatypeDiv.show();
            jLangDiv.hide();
            // clear lang
            jLangDiv.find('select').val('');
        }
    });
    
    $('.disclosure-button').live('click', function() {
        // get disclosure button's id
        var id    = $(this).attr('id');
        var close = $(this).hasClass('open') ? true : false;
        
        // update UI accordingly
        var button = this;
        if (close) {
            $('.' + id).fadeIn(250, function() {
                $(button).removeClass('open').addClass('closed');
            });
        } else {
            $('.' + id).fadeOut(250, function() {
                $(button).removeClass('cosed').addClass('open');
            });
        }
    });
});