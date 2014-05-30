<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class TestModule extends OntoWiki_Module
{
    public function init()
    {

    }

    /**
     * Returns the title of the module
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Test';
    }

    public function shouldShow()
    {
        return true;
    }

    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu()
    {
        return OntoWiki_Menu_Registry::getInstance()->getMenu('application');
    }

    public function getContents()
    {
        return '';
    }

    public function allowCaching()
    {
        // no caching
        return false;
    }
}


