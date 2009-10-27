/**
 * This file is part of the navigation extension for OntoWiki
 *
 * @author     Sebastian Dietzold <sebastian@dietzold.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 *
 */

/**
 * The main document.ready assignments and code
 */
$(document).ready(function() {
    /* some used variables */
    navigationContainer = $('#navigation-content');
    navigationInput = $("#navigation-input");
    navigationWindow = $("#navigation");
    navigationUrl = urlBase + 'navigation/explore';

    /* first start */
    navigationLoad();
});

/**
 * request the navigation
 */
function navigationLoad () {
    // first we set the processing status
    navigationInput.addClass('is-processing');
 
    params = { setup: 'singleResource' };

    navigationContainer.css('overflow', 'hidden');
    navigationContainer.animate({marginLeft:'-100%'},'slow', '', function(){
        $.post(navigationUrl, params,
            function (data) {
                navigationContainer.empty();
                navigationContainer.append(data);
                // remove the processing status
                navigationInput.removeClass('is-processing');

                navigationContainer.css('marginLeft', '100%');
                navigationContainer.animate({marginLeft:'0px'},'fast');

                $('.navigation').click(function(event) {
                    navigationLoad();
                });
            }
        );
    });

    return true;
}
