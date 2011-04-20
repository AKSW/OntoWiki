<?php
/**
 * DSSN module – Share It!
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_bdays
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ShareitModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Share It!';
    }

    public function init()
    {
        /* nothing to do right now */
    }

    public function shouldShow() {
        // module can be turned off in extension config
        if ($this->_privateConfig->modules->shareit != true) {
            return false;
        }
        return true;
    }

    function getContents()
    {
        $content = array(
            'Status' => $this->render('modules/shareit-status', false, 'shareit'),
            'Foto'   => $this->render('modules/shareit-foto', false, 'shareit'),
            'Link'   => $this->render('modules/shareit-link', false, 'shareit'),
            'Video'  => $this->render('modules/shareit-video', false, 'shareit')
        );
        return $content;
    }
}


