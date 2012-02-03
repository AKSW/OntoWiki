$(document).ready(function() {
    $('#modellist').change(function() {
        var url = $('#modellist option:selected').attr('data-uri');
        window.location = url;
    });
});