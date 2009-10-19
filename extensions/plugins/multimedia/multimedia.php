<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id: multimedia.php 4093 2009-08-19 22:29:29Z christian.wuerker $
 */

require_once 'OntoWiki/Plugin.php';
require_once 'OntoWiki/Utils.php';

/**
 * Short description for class
 *
 * Long description for class (if any) ...
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class MultimediaPlugin extends OntoWiki_Plugin
{    
// TODO check for Image, Document, etc classes...
// TODO support more types
    public function onPrePropertiesContentAction($event)
    {
        if (preg_match("/^.*\.(jpg|jpeg|png|gif|tiff)$/", $event->uri)) {
            return $this->_handleImage($event->uri);
        } else if (preg_match("/^http:\/\/www\.youtube\.com\/watch\?v=(.*)$/", $event->uri)) {
            return $this->_handleYoutube($event->uri);
        } else {
            return false;
        }
    }
    
    private function _handleImage($uri)
    {
        return '<a href="' . $uri . '"><img class="object-30em" src="' . $uri . '" alt="image of ' . $uri . '"/></a>';
    }
    
    private function _handleYoutube($uri)
    {
        $urlTokens = array();
        preg_match("/^http:\/\/www\.youtube\.com\/watch\?v=(.*)$/", $uri, $urlTokens);
        $videoId = $urlTokens[1];
        
        $youtubeUrl = 'http://www.youtube.com/v/' . $videoId . '&hl=de&fs=1&rel=0';
        
        return '<object width="425" height="344" class="object-30em"><param name="movie" value="' . $youtubeUrl . '"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed width="425" height="344" src="' . $youtubeUrl . '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
    }
}

