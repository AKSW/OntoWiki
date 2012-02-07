$(document).ready(function() {
    $('.knowledgeBaseElement').click(function() {
        var url = $(this).attr('data-uri');
        window.location = url;
    });
});