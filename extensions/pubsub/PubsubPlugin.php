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
        // Only notify if hub is set.
        $hubUrl = $this->_privateConfig->hubUrl;
        if (null == $hubUrl) {
            return;
        }
        $this->_log('Using hub: ' . $hubUrl);
        
        // Only notify for resources that are owned by the ow instance.
        $owApp = OntoWiki::getInstance();
        $urlBase = $owApp->getUrlBase();
        if (!(substr($resourceUri, 0, strlen($urlBase)) === $urlBase)) {
            return;
        }

        require_once "lib/publisher.php";
        $rParam = urlencode((string)$resourceUri);
        
        // TODO: Use the activity feed here!
        $mParam = urlencode((string)$owApp->selectedModel);
        
        $topicUrl = $owApp->getUrlBase() . "history/feed?m=$model&r=$resource";
        $this->_log('Will notify hub for topic: ' . $topicUrl);
        
        // Execute the notification
        try {
            $p = new Publisher($hub_url);
            
            if ($p->publish_update($topic_url)) {
                $this->_log('Successfully notified hubs (' . $hub_url. ') for topic (' . $topic_url . ').');
            } else {
                $this->_log('Failed to notify hubs (' . $hub_url . ') for topics (' . $topic_url . ').');
                //$owApp->logger->debug( print_r($p->last_response(), true) );
            }
        } catch (Exception $e) {
            $this->_log('Exception: (' . $hub_url . ') for topics (' . $topic_url . ').');
        }
        	    
    }
    
    private function _log($msg)
    {
        $logger = OntoWiki::getInstance()->getCustomLogger('pubsub');
        $logger->debug($msg);        
    }
}
