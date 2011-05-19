<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – query target setter
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_queryeditorfromsetter
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
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


