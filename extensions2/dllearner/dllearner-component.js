/**
 * This file is part of the dllearner extension for OntoWiki
 *
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 *
 */

/**
 * The main document.ready assignments
 */
$(document).ready(function() {
    $(".dll-showDetails").click(function () {
        $(this).parents('.dll-solution').find('.dll-details').slideToggle();
    });
    $(".dll-showAllDetails").click(function () {
        $('.dll-details').slideToggle();
    });
});
