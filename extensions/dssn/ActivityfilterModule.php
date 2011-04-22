<?php
/**
 * DSSN module – Activity Filter
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_activityfilter
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ActivityfilterModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Filter and Search Activities';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->activityfilter != true) {
            return false;
        }
        return true;
    }

    function getContents()
    {
        $content = $this->render('modules/activityfilter', false, 'activityfilter');
        return $content;
    }
}


