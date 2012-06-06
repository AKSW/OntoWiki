<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Helper class for the Source component.
 *
 * - register the tab for all navigations 
 *
 * @category OntoWiki
 * @package Extensions_Source
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SourceHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        OntoWiki::getInstance ()->getNavigation()->register('source', array(
            'controller' => 'source',     // source controller
            'action'     => 'edit',       // ecit action
            'name'       => 'Source',
            'priority'   => 60));
    }
}
