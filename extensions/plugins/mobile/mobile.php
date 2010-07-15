<?php
require_once 'OntoWiki/Plugin.php';

class MobilePlugin extends OntoWiki_Plugin
{
    public function onInitLayout($array){
        $owApp = OntoWiki::getInstance();
        $logger = $owApp->logger;

        $logger->debug('MOBILE: Called');

        //if( !isset($) ) return;

        //if( strpos($user_agent, "iPhone") > -1 || strpos($user_agent, "Adnroid") > -1 ){
            //if( !strpos($_SERVER["REQUEST_URI"], "initmobile") ) header ("Location: $url");
            $array['layout'] = 'mobile';
        //}
    }
}
