<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';

/**
 * Plugin to load a additional css with some fancy rounded corners and maybe more thats not release-friendly looking
 *
 * @category   OntoWiki
 * @package    Extensions_Fancystyle
 */
class FancystylePlugin extends OntoWiki_Plugin
{

    public function onAfterInitController($event)
    {
        $this->view->headLink()->appendStylesheet($this->_pluginUrlBase.'fancystyle.css');
    }
}
