<?php
class PubsubController extends OntoWiki_Controller_Component
{
    public function subscribeAction()
    {
        $hubUrl      = 'http://example.org'; 
        $topicUrl    = $_GET['topic'];
        $callbackUrl = $this->_getCallbackUrl();
        
        $storage = new SubscriptionModel();
        
        $subscriber = new Zend_Feed_Pubsubhubbub_Subscriber;
        $subscriber->setStorage($storage);
        $subscriber->addHubUrl($hubUrl);
        $subscriber->setTopicUrl($topicUrl);
        $subscriber->setCallbackUrl($callbackUrl);
        $subscriber->subscribeAll();
        
        
        
        
        
        
        
        
        
        if (!empty($_GET)) {
            return;
        }
        
        OntoWiki_Navigation::disableNavigation();
        
        $toolbar = $this->_owApp->toolbar;
		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save'))
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
    
    public function callbackAction()
    {
        
    }
    
}