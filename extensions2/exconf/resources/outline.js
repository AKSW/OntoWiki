$(document).ready(function() {
    extensionOutline();
});

function extensionOutline() {
    target=$('div.outline').empty();
    target=target.append('<ol class="bullets-none separated" />').children('ol');
    
    var even = true;
    $('div.extension:visible').each(function(){
        title=$(this).find('h3').text();
        id=$(this).attr('id');
        if(even){
            target.append('<li><a title="' +title+ '" href="#' +id+ '">' +title+ '</a></li>');
        } else {
            target.append('<li class="odd"><a title="' +title+ '" href="#' +id+ '">' +title+ '</a></li>');
        }
        even = !even;
    });
};
