<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Helper class for the History component.
 *
 * - register the tab for all navigations except the instances list
 *   (this should be undone if the history can be created from a Query2 too)
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage History
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class HistoryHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        // get the main application
        $owApp = OntoWiki::getInstance();

        // get current route info
        $front  = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        OntoWiki_Navigation::register('history', array(
            'controller' => 'history',     // history controller
            'action'     => 'list',       // list action
            'name'       => 'History',
            'priority'   => 30));
    }
}

