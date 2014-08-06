/**
 * @copyright Copyright (c) 2014, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
$(document).ready(function() {
    extensionOutline();
});

function extensionOutline() {
    var target = $('div.outline').empty();
    target = target.append('<ol class="bullets-none separated" />').children('ol');

    var even = true;
    $('div.extension:visible').each(function(){
        var title = $(this).find('.name').text();
        var id = $(this).attr('id');
        if(even){
            target.append('<li><a title="' +title+ '" href="#' +id+ '">' +title+ '</a></li>');
        } else {
            target.append('<li class="odd"><a title="' +title+ '" href="#' +id+ '">' +title+ '</a></li>');
        }
        even = !even;
    });
};
