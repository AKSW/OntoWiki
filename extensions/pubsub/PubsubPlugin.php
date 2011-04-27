<?php
require_once 'OntoWiki/Plugin.php';

class PubsubPlugin extends OntoWiki_Plugin
{
    public function onAddStatement($event)
	{	
	    $s = $event->statement['subject'];
	    $this->_notify($s);
	}
	
	public function onAddMultipleStatements($event)
	{
	    //$statements = $event->statements;
	    //foreach ($statements as $subject => $predicatesArray) {
            $this->_notify($subject);
	    //}
	}
	
	public function onDeleteMultipleStatements($event)
	{
	    foreach ($event->statements as $subject => $predicatesArray) {
            $this->_notify($subject);
	    }
	}
	
	private function _notify($resourceUri)
	{
	    $owApp = OntoWiki::getInstance(); 
	    
	    $rParam = urlencode((string)$resourceUri);
	    $mParam = urlencode((string)$owApp->selectedModel);
	    
	    $hubs = $this->_privateConfig->hubUrls->toArray();
	    $topics = array(
	        // http://tim.ontowiki.net/history/feed?r=http%3A%2F%2Ftim.ontowiki.net%2Ffoaf&amp;mUriEncoded
	       $owApp->config->urlBase . "history/feed?r=".$mParam//?r=$rParam&m=$mParam"
	    );
	    
	    $publisher = new Zend_Feed_Pubsubhubbub_Publisher;
	    $publisher->addHubUrls($hubs);
	    $publisher->addUpdatedTopicUrls($topics);
	    $publisher->notifyAll();
	    
	    if (!$publisher->isSuccess()) {
	        $owApp->logger->debug('Failed to notify hubs (' . implode(', ', $hubs) . ') for topics (' . implode (', ', $topics) . ').');
	    } else {
	        $owApp->logger->debug('Successfully notified hubs (' . implode(', ', $hubs) . ') for topics (' . implode (', ', $topics) . ').');
	    }
	    
	}
}
