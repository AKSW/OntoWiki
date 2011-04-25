<?php
/**
 * DSSN module – Make Friends
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_bdays
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class MakefriendsModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Search Contacts / Make new Friends :-)';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->makefriends != true) {
            return false;
        }
        return true;
    }

    function getContents()
    {
        $content = $this->render('modules/makefriends', false, 'data');
        return $content;
    }
}


