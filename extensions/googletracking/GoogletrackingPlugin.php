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
   
class GoogletrackingPlugin extends OntoWiki_Plugin {


    public function onAfterInitController($event) {

        if(isset($this->_privateConfig->trackingID))
        {
        $this->view->headScript()->appendScript("
        var gaJsHost = ((\"https:\" == document.location.protocol) ? \"https://ssl.\" : \"http://www.\");
        document.write(unescape(\"%3Cscript src='\" + gaJsHost + \"google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E\"));
        ");
        $this->view->headScript()->appendScript("
        try {
        var pageTracker = _gat._getTracker(\"".$this->_privateConfig->trackingID."\");
        pageTracker._trackPageview();
        } catch(err) {}");
        }

    }
}

