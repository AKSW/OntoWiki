/*
 * Patternmanager Exec Javascript
 *
 * @package    
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */

$(document).ready(function () {
    $('div#patternmanager div.row-input').each( function (i) {
        node = $(this).find('input');
        vartype = $(this).find('span.vartypename').text();
        $(node).autocomplete(
            urlBase + 'patternmanager/autocomplete',
            {
                loadingClass : 'is-processing',
                minChars: 3 ,
                delay: 1000 ,
                max: 10 ,
                extraParams: {
                    mode : 'exec' ,vartype : vartype }
            }
        );
    });
});