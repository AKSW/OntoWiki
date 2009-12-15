function MetaEdit(graph, subject, predicate, object, options) {
    this.options = $.extend({
        active: 'literal'
    }, options);
    
    this.id        = RDFauthor.getNextId();
    this.graph     = graph;
    this.subject   = subject;
    this.predicate = predicate;
    this.object    = object;
    
    options = {graph: this.graph, subject: this.subject, predicate: this.predicate, object:this.object};
    this.resourceEdit = RDFauthor.getWidgetForHook('__object', null, options);
    this.literalEdit  = RDFauthor.getWidgetForHook('__literal', null, options);
}

MetaEdit.prototype.eventsRegistered = false;

MetaEdit.prototype.init = function () {
	this.resourceEdit.init();
};

MetaEdit.prototype.getHtml = function () {
    var active = this.getActiveWidget() == this.literalEdit ? 'literal' : 'resource';
    
    var html = '\
        <div class="meta-select">\
            <div class="inline-container meta-type util">\
                <label><input type="radio" class="radio" ' + (active == 'resource' ? 'checked="checked"' : '') + ' name="meta-type-' + this.id + '"' + ' value="resource" />Resource</label>\
                <label><input type="radio" class="radio" ' + (active == 'literal' ? 'checked="checked"' : '') + ' name="meta-type-' + this.id + '"' + ' value="literal" />Literal</label>\
            </div>\
            <hr style="clear:left; height:0; border:none" />\
            <div class="meta-resource" id="meta-resource-' + this.id + '"' + (active == 'resource' ? '' : 'style="display:none"') + '>\
                ' + this.resourceEdit.getHtml() + '\
            </div>\
            <div class="meta-literal" id="meta-literal-' + this.id + '"' + (active == 'literal' ? '' : 'style="display:none"') + '>\
                ' + this.literalEdit.getHtml() + '\
            </div>\
        </div>';
    
    return html;
};

MetaEdit.prototype.onCancel = function () {
    if (this.activeWidget) {
        return this.getActiveWidget().onCancel();
    }
    
    return true;
};

MetaEdit.prototype.onRemove = function() {
    this.getActiveWidget().onRemove();
};

MetaEdit.prototype.onSubmit = function () {
    return this.getActiveWidget().onSubmit();
};

MetaEdit.prototype.getActiveWidget = function () {
    var activeWidget = null;
    
    if ($('input:radio[name=meta-type-' + this.id + ']:checked').length) {
        var value = $('input:radio[name=meta-type-' + this.id + ']:checked').val();
        
        if (value == 'literal') {
            activeWidget = this.literalEdit;
        } else {
            activeWidget = this.resourceEdit;
        }
    } else {
        activeWidget = this.options.active == 'literal' ? this.literalEdit : this.resourceedit;
    }
    
    return activeWidget;
};

RDFauthor.registerWidget({constructorFunction: MetaEdit, hookName: '__default'});

if (!MetaEdit.prototype.eventsRegistered) {
    $('.meta-type .radio').live('click', function() {
        var jResourceDiv = $(this).closest('.meta-select').children('.meta-resource');
        var jLiteralDiv  = $(this).closest('.meta-select').children('.meta-literal');
        
        if ($(this).val() == 'resource') {
            jResourceDiv.show();
            jLiteralDiv.hide();
        } else {
            jResourceDiv.hide();
            jLiteralDiv.show();
        }
    });
    
    MetaEdit.prototype.eventsRegistered = true;
}