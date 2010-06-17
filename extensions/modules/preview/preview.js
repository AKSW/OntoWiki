$(document).ready(function() {
    $('body').bind('ontowiki.resource.selected', function(event, data) {
        $('#selected-resource-preview').text(data);
    });
})