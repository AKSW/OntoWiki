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
        $statements = $event->statements;	    
        foreach ($statements as $subject => $predicatesArray) {
            $this->_notify($subject);
            break;
        }
    }

    public function onDeleteMultipleStatements($event)
    {
        foreach ($event->statements as $subject => $predicatesArray) {
            $this->_notify($subject);
        }
    }

    private function _notify($resourceUri)
    {
        require_once "lib/publisher.php";

        $owApp = OntoWiki::getInstance(); 
        $owApp->logger->debug('Resource: '.$resourceUri);


        $resource = urlencode((string)$resourceUri);
        $model = urlencode((string)$owApp->selectedModel);

        $hub_url = $this->_privateConfig->hubUrl;
        $topic_url = $owApp->config->urlBase . "history/feed?m=$model&r=$resource";

        
        try {
            $p = new Publisher($hub_url);
            
            if ($p->publish_update($topic_url)) {
                $owApp->logger->debug('Successfully notified hubs (' . $hub_url. ') for topic (' . $topic_url . ').');
            } else {
                $owApp->logger->debug('Failed to notify hubs (' . $hub_url . ') for topics (' . $topic_url . ').');
                //$owApp->logger->debug( print_r($p->last_response(), true) );
            }
        } catch (Exception $e) {
            $owApp->logger->debug('Exception: (' . $hub_url . ') for topics (' . $topic_url . ').');
        }
        	    
    }
}
