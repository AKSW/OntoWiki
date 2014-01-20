<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';
//require_once realpath(dirname(__FILE__)) . '/classes/ResourceUriGenerator.php';

/**
 * Description goes here.
 *
 * @category   OntoWiki
 * @package    Extensions_Historyproxy
 */
class HistoryproxyPlugin extends OntoWiki_Plugin
{

   
    public function onQueryHistory($event)
    {
        $logger = OntoWiki::getInstance()<
        $logger->info('[doge]');
    }
}
