<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – LOV and prefix.cc vocabulary selection
 *
 * Allows for selection of LOV vocabularies in the model import screen
 *
 * @category OntoWiki
 * @package  OntoWiki_Extensions_basicimporter
 * @author   Sebastian Tramp <mail@sebastian.tramp.name>
 */
class SelectorModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Select a Vocabulary';
    }

    public function getContents()
    {
        $this->view->headScript()->appendFile(
            $this->_config->urlBase . 'extensions/basicimporter/SelectorModule.js'
        );

        $data = array();
        $content  = $this->render('templates/basicimporter/search', $data);

        return $content;
    }
}
