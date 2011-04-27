<?php
class PubsubController extends OntoWiki_Controller_Component
{
    public function subscribeuiAction()
    {
        OntoWiki_Navigation::disableNavigation();
        
        $toolbar = $this->_owApp->toolbar;
		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save', 'id' => 'save_btn'))
		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
		$this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $translate  = $this->_owApp->translate;
        $windowTitle = $translate->_('Subscribe to Feed');
        $this->view->placeholder('main.window.title')->set($windowTitle);

        $this->view->formActionUrl = $this->_config->urlBase . 'pubsub/subscribe';
		$this->view->formMethod    = 'get';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'subsribe';
    }

    public function subscribeAction()
    {
        if (!empty($_GET['topic'])) {
            $this->_owApp->logger->debug('No topic! Nothig to subscribe..'); 
            return;
        }
    
        $hubsUrl     = $this->_privateConfig->hubUrls->toArray(); 
        $topicUrl    = $_GET['topic'];
        $callbackUrl = $this->_getCallbackUrl();
        
        $storage = new Zend_Feed_Pubsubhubbub_Model_Subscription;// new SubscriptionModel();
        
        $subscriber = new Zend_Feed_Pubsubhubbub_Subscriber;
        $subscriber->setStorage($storage);
        $subscriber->addHubUrl($hubsUrl[0]);
        $subscriber->setTopicUrl($topicUrl);
        $subscriber->setCallbackUrl($callbackUrl);
        $subscriber->subscribeAll();
        
        $this->_owApp->logger->debug('Subscribed to pubsub '.$topicUrl.' ok..'); 
    }
    
    public function callbackAction()
    {
        $storage = new Zend_Feed_Pubsubhubbub_Model_Subscription;
        $callback = new Zend_Feed_Pubsubhubbub_Subscriber_Callback;
        $callback->setStorage($storage);
        $callback->handle();
        $callback->sendResponse();
        
        $this->_owApp->logger->debug('Handeling pubsub callback..'); 
        
        /**
        * Check if the callback resulting in the receipt of a feed update.
        * Otherwise it was either a (un)sub verification request or invalid request.
        * Typically we need do nothing other than add feed update handling - the rest
        * is handled internally by the class.
        */
        if ($callback->hasFeedUpdate()) {
            $feedString = $callback->getFeedUpdate();
            
            $this->_owApp->logger->debug('Got new feed from pubsub: '.$feedString);
        }
    }
    
}
