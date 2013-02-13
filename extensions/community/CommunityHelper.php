<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * Helper class for the Community component.
 *
 * - register the tab for navigation on properties view
 *
 * @category   OntoWiki
 * @package    Extensions_Community
 * @copyright  Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class CommunityHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        /*
         * check for $request->getParam('mode') == 'multi' if tab should also be displayed for
         * multiple resources/lists ($request->getActionName() == 'instances')
         * And set 'mode' => 'multi' to tell the controller the multi mode
         *
         * Multi mode was disabled because it doesn't seam to work
         */

        $owApp = OntoWiki::getInstance();

        if ($owApp->lastRoute == 'properties' && $owApp->selectedResource != null) {
            $owApp->getNavigation()->register(
                'community',
                array(
                    'controller' => 'community',
                    'action'     => 'list',
                    'name'       => 'Community',
                    'mode'       => 'single',
                    'priority'   => 50
                )
            );
        }
    }
}
