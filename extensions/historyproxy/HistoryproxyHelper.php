<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Helper class for the History component.
 *
 * @category OntoWiki
 * @package Extensions_History
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class HistoryproxyHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        OntoWiki::getInstance()->getNavigation()->register(
            'historyproxy',
            array(
                'controller' => 'historyproxy',     // history controller
                'action'     => 'view',        // list action
                'name'       => 'Historyproxy',
                'priority'   => 30
            )
        );
    }
}

