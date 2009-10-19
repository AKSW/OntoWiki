<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id:$
 */

require_once 'OntoWiki/Plugin.php';
require_once 'OntoWiki/Utils.php';


/**
 * This class includes an audioplayer for pre-defined properties.
 *
 * Long description for class (if any) ...
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Christian Maier <christianmaier83@gmail.com> and Michael Niederstätter <michael.niederstaetter@gmail.com>
 */
       
class AudiolinkPlugin extends OntoWiki_Plugin
{

   public function init()
    {

         // load js
        $this->view->headScript()->appendFile($this->_pluginUrlBase . 'resources/js/swfobject.js');
        $this->view->headScript()->appendFile($this->_pluginUrlBase . 'resources/js/visibility.js');
       
        // load styles
        $this->view->headLink()->appendStylesheet($this->_pluginUrlBase . 'resources/styles/audiolink.css','screen, projection');

    }
    
    public function onDisplayObjectPropertyValue($event)
    {

       if (in_array($event->property,  $this->_privateConfig->properties->toArray(), true))
       {
           //create player ID

            static $playerId=0;
            $playerId ++;

            if ($playerId >= 999)
            {
                $playerId = 0;
            }

            // add the plug-in's root directory to the list of paths where Zend_View looks for templates
            $this->view->addScriptPath($this->_pluginRoot);

            // set script variables in view object
            $this->view->playerSrc = $this->_pluginUrlBase . 'resources/swf/player-viral.swf';
            $this->view->uri       = $event->value;
            $this->view->title       = $event->title;
            $this->view->link       = $event->link;
            $this->view->divId    = 'audioObjectProperty' . $playerId;

            // render the template
            $html = $this->view->render('audiolink.phtml');

            return $html;
       }
    }
    
    public function onDisplayLiteralPropertyValue($event)
    {
        if (in_array($event->property,  $this->_privateConfig->properties->toArray(), true))
        {
            //create player ID

            static $playerId=0;
            $playerId ++;

            if ($playerId >= 999)
            {
                $playerId = 0;
            }

            // add the plug-in's root directory to the list of paths where Zend_View looks for templates
            $this->view->addScriptPath($this->_pluginRoot);

            // set script variables in view object
            $this->view->playerSrc = $this->_pluginUrlBase . 'resources/swf/player-viral.swf';
            $this->view->uri       = $event->value;
            $this->view->divId    = 'audioLiteralProperty' . $playerId;

            // render the template
            $html = $this->view->render('audiolink.phtml');

            return $html; 

        }
    }
}

