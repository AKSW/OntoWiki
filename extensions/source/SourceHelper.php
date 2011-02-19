<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Helper class for the Source component.
 *
 * - register the tab for all navigations 
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage Source
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SourceHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        // get the main application
        $owApp = OntoWiki::getInstance();

        // get current route info
        $front  = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();

        OntoWiki_Navigation::register('source', array(
            'controller' => 'source',     // source controller
            'action'     => 'edit',       // ecit action
            'name'       => 'Source',
            'priority'   => 60));
    }
}
