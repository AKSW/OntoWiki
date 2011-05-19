<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id: sindice.php 4117 2009-09-04 08:19:50Z pfrischmuth $
 */

require_once 'OntoWiki/Plugin.php';

/**
 * Plugin for the Sindice search service. This plugin is used by the datagathering component search service.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL),
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class SindicePlugin extends OntoWiki_Plugin
{
    public function onDatagatheringComponentSearch($event)
    {
        $baseUri   = 'http://api.sindice.com/v2/search?qt=term&page=1&q=';
        $searchUri = $baseUri . implode('+', $event->termsArray); 

        $client = Erfurt_App::getInstance()->getHttpClient($searchUri, array(
            'maxredirects'  => 2,
            'timeout'       => 10
        ));
        $client->setHeaders('Accept', 'application/json');
        $response = $client->request();
            
        $sindiceResult = json_decode($response->getBody(), true);
        $result = array();

        // unfortunatly json_last_error is PHP >= 5.3.0, so this is not perfect
        // and someday we should us ---> if (json_last_error() == JSON_ERROR_NONE) {
        if ($sindiceResult != null) {
            // TODO Keep order of original sindice result!!!
            foreach ($sindiceResult['entries'] as $row) {
                $title = implode(' - ', $row['title']);
                $uri = $row['link'];

                $result[$uri] = str_replace('|', '&Iota;', $title) . '|' . $uri . '|' . $event->translate->_('Sindice Search');
            }
        }
        
        return $result;
    }    
}
