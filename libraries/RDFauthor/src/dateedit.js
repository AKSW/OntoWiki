function DateEdit(graph, subject, predicate, object) {
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object ? object.value : '';
    this.id        = RDFauthor.getNextId();
    
    this.remove = false;
}

DateEdit.prototype.datatype = 'http://www.w3.org/2001/XMLSchema#date';

DateEdit.prototype.init = function () {    
    $('#date-edit-' + this.id).datepicker({
        dateFormat: $.datepicker.ISO_8601, 
        // showOn: 'both', 
        firstDay: 1
    });
    
    $('#ui-datepicker-div').css('z-index', 10000);
};

DateEdit.prototype.getHtml = function() {
    var html = 
        '<div class="container">' + 
            '<input type="text" class="text" id="date-edit-' + this.id + '" value="' + this.object + '"/>' + 
        '</div>';
    
    return html;
};

DateEdit.prototype.onCancel = function () {
    return true;
};

DateEdit.prototype.onRemove = function () {
    this.remove = true;
};

DateEdit.prototype.onSubmit = function () {
    var dataBank = RDFauthor.getDatabank(this.graph);
    var newValue = $('#date-edit-' + this.id).val();
    
    // Widget just added and nothing entered
    if (this.object == '' && newValue == undefined) {
        return true;
    }
    
    if ((newValue != this.object) || this.remove) {
        // remove old triple
        if (this.object !== '' || this.remove) {
            var oldTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(this.object, {datatype: DateEdit.prototype.datatype})
            );

            dataBank.remove(oldTriple);
        }
        
        if (!this.remove) {
            // add new triple
            var newTriple = $.rdf.triple(
                $.rdf.resource('<' + this.subject + '>'), 
                $.rdf.resource('<' + this.predicate + '>'), 
                $.rdf.literal(newValue, {datatype: DateEdit.prototype.datatype})
            );

            dataBank.add(newTriple);
        }        
    }
    
    return true;
};

RDFauthor.registerWidget({constructorFunction: DateEdit, hookName: 'datatype', hookValues: [DateEdit.prototype.datatype]});