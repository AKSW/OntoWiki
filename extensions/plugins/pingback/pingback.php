<?php
require_once 'OntoWiki/Plugin.php';

class PingbackPlugin extends OntoWiki_Plugin
{
    public function onControllerInit(){
		$url = preg_replace('/extensions.plugins.pingback.*/', '', $this->_pluginUrlBase);
		header("X-Pingback: ".$url."index.php/pingback/ping");
		
	}
	
	

}

