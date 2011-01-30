<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – save query button
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_savequery
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SavequeryModule extends OntoWiki_Module
{
    public function getContents()
    {
        return $this->render('savequery');
    }

    public function getTitle()
    {
        return "Save Query";
    }
}


