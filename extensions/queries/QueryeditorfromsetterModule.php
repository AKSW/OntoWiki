<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – query target setter
 *
 * @category   OntoWiki
 * @package    Extensions_Queries
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class QueryeditorfromsetterModule extends OntoWiki_Module
{
    public function getContents()
    {
        return $this->render('queryeditorfromsetter');
    }

    public function getTitle()
    {
        return "Query Source";
    }
}


