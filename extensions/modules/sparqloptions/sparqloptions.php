<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – sparqloptions
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_sparqloptions
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: sparqloptions.php 4092 2009-08-19 22:20:53Z christian.wuerker $
 */
class SparqloptionsModule extends OntoWiki_Module
{    
    public function getContents()
    {
        return $this->render('sparqloptions');
    }
}


