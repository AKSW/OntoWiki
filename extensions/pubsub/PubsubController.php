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
        if (empty($_GET['topic'])) {
            $this->_owApp->logger->debug('No topic! Nothig to subscribe..'); 
            return;
        }
        
        require_once "lib/subscriber.php";
    
        $hub_url     = $this->_privateConfig->hubUrl; 
        $topic_url   = $_GET['topic'];
        $callback_url = $this->_getCallbackUrl();
        
        // create a new subscriber
        $s = new Subscriber($hub_url, $callback_url);

        // subscribe to a feed
        //$s->unsubscribe($feed);
        echo "Subscribed!\n";
        print_r(  $s->subscribe($topic_url) );//*/
        
        $this->_owApp->logger->debug('Subscribed to pubsub '.$hub_url.' for '.$topic_url.' with callback '.$callback_url); 
    }
    
    private function _getCallbackUrl(){
        return $this->_owApp->config->urlBase . "pubsub/callback/";
    }
    
    public function callbackAction()
    {
        $this->_owApp->logger->debug('Handeling pubsub callback..'); 
    
        header('HTTP/1.0 200 OK');
        if( isset( $_REQUEST['hub_challenge'] ) ){
            // answer check
            echo $_REQUEST['hub_challenge'];
        }else{
            $this->_owApp->logger->debug('Got new feed from pubsub: '.print_r($_REQUEST,true));
        }
    }
    
}
