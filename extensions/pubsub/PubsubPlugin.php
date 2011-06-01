<?php
require_once 'OntoWiki/Plugin.php';

class PubsubPlugin extends OntoWiki_Plugin
{
// TODO: use real event name
    public function onFeedChange($event)
    {
        $feedUrl = $event->feedUrl;
        $this->_notify($feedUrl);
    }
    
    private function _notify($feedUrl)
    {
        // Only notify if hub is set.
        $hubUrl = $this->_privateConfig->hubUrl;
        if (null == $hubUrl) {
            $this->_log('No hub configured');
            return;
        }
        $this->_log('Using hub: ' . $hubUrl);
        
        // Only notify for resources that are owned by the ow instance.
        $owApp = OntoWiki::getInstance();
        $urlBase = $owApp->getUrlBase();
        if (!(substr($feedUrl, 0, strlen($urlBase)) === $urlBase)) {
            $this->_log('Pubsub notifications only supported for internal resources');
            return;
        }
        
        $this->_log('Will notify hub for feed: ' . $feedUrl);
        
        // Execute the notification
        require_once "lib/publisher.php";
        try {
            $p = new Publisher($hubUrl);
            
            if ($p->publish_update($feedUrl)) {
                $this->_log('Successfully notified hubs (' . $hubUrl. ') for topic (' . $feedUrl . ').');
            } else {
                $this->_log('Failed to notify hubs (' . $hubUrl . ') for topics (' . $feedUrl . ').');
            }
        } catch (Exception $e) {
            $this->_log('Exception: ' . $e->getMessage());
        }
    }
    
    private function _log($msg)
    {
        $logger = OntoWiki::getInstance()->getCustomLogger('pubsub');
        $logger->debug($msg);        
    }
}
