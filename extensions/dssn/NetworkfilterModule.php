<?php
/**
 * DSSN module – Network / Friends Filter
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_networkfilter
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class NetworkfilterModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Filter your Network';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->networkfilter != true) {
            return false;
        }
        return true;
    }

    function getContents()
    {
        $content = $this->render('modules/networkfilter', false, 'data');
        return $content;
    }
}


