<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
require_once 'OntoWiki/Menu.php';
/**
 * @category   OntoWiki
 * @package    Extensions_Savedqueries
 */
class SimplesparqlresultviewHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki::getInstance();
        // if a model has been selected
        if ($owApp->selectedModel != null) {
        }
    }
}
