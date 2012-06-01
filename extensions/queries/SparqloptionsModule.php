<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – sparqloptions
 *
 * @category   OntoWiki
 * @package    Extensions_Queries
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SparqloptionsModule extends OntoWiki_Module
{
    public function getContents()
    {
        return $this->render('sparqloptions');
    }

    public function getTitle() {
        return "Output Format";
    }
}
