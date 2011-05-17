<?php
require_once 'HubSubscriptionModel.php';
require_once 'HubNotificationModel.php';

class PubsubController extends OntoWiki_Controller_Component
{
    const DEFAULT_LEASE_SECONDS = 2592000; // 30 days
    const CHALLENGE_SALT= 'csaiojwef89456nucekljads8tv589ncefn4c5m90ikdf9df5s';
    
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
        $this->_helper->layout()->disableLayout();
    
        $this->_owApp->logger->debug('Handeling pubsub callback..'); 
        
        $this->_owApp->logger->debug('Data from hub: '.print_r($_REQUEST,true));
    
        header('HTTP/1.0 200 OK');
        if( isset( $_REQUEST['hub_challenge'] ) ){
            // answer check
            echo $_REQUEST['hub_challenge'];
            $this->_owApp->logger->debug('Got verify request from pubsub');
        }else{
            $this->_owApp->logger->debug('Got new feed from pubsub');
        }
    }
    
    /**
     * Hub related actions
     */
     
     public function hubsubscriptionAction()
     {
         // Disable rendering
         $this->_helper->viewRenderer->setNoRender();
         $this->_helper->layout()->disableLayout();
         
         // We require POST requests here.
         if (!$this->_request->isPost()) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         
         $params = array();
         
         // hub.callback
         if (!isset($_POST['hub.callback'])) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         $params['hub.callback'] = urldecode($_POST['hub.callback']);
         if (strrpos($params['hub.callback'], '#') !== false) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         
         // hub.mode
         if (!isset($_POST['hub.mode'])) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         $mode = $_POST['hub.mode'];
         if (!(($mode === 'subscribe') || ($mode === 'unsubscribe'))) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         $params['hub.mode'] = $mode;
          
         // hub.topic
         if (!isset($_POST['hub.topic'])) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         $params['hub.topic'] = urldecode($_POST['hub.topic']);
         if (strrpos($params['hub.topic'], '#') !== false) {
              $this->_response->setException(new OntoWiki_Http_Exception(400));
              return;
          }
         
         // hub verify
         if (!isset($_POST['hub.verify'])) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         $verify = $_POST['hub.verify'];
         // supported values for hub.verify: sync, async
         if (!(($verify === 'sync') || ($verify === 'async'))) {
             $this->_response->setException(new OntoWiki_Http_Exception(400));
             return;
         }
         $params['hub.verify'] = $verify
         
         // optional: hub.lease_seconds
         $leaseSeconds = null;
         if (isset($_POST['hub.lease_seconds'])) {
             $params['hub.lease_seconds'] = $_POST['hub.lease_seconds'];
         }
         
         // optional: hub.secret (SHOULD only be provided when hub is behind HTTPS!)
         $secret = null;
         if (isset($_POST['hub.secret'])) {
             $params['hub.secret'] = urldecode($_POST['hub.secret']);
         }
         
         // optional: hub.verify_token
         $verifyToken = null;
         if (isset($_POST['hub.verify_token'])) {
             $params['hub.verify_token'] = urldecode($_POST['hub.verify_token']);
         }
         
         // Create a challenge for the verification
         $challenge = uniqid(mt_rand(), true) . uniqid(mt_rand(), true) . self::CHALLENGE_SALT;
         $challenge = md5($challenge);
         $params['hub.challenge'] = $challenge;
         
         $hubModel = new HubSubscriptionModel();
         if ($hubModel->hasSubscription($params)) {
             if ($params['hub.mode'] === 'subscribe') {
                 $this->_response->setException(new OntoWiki_Http_Exception(500));
                 return;
         }
         
         // Subscribe/Unsubscribe
         if ($params['hub.mode'] === 'subscribe') {
             $hubModel->addSubscription($params);
         }
         
         if ($params['hub.verify'] === 'sync') {
             $success = $this->_hubSendVerificationRequest($params);
             
             if ($success) {
                 $this->_response->setHttpResponseCode(204);
                 return $this->_response->sendResponse();
             }
             
             $this->_response->setException(new OntoWiki_Http_Exception(500));
             return;
         }
         
         $this->_scheduleVerification();
         $this->_response->setHttpResponseCode(202);
         return $this->_response->sendResponse();
         
     }
     
     public function hubperformasyncverifies()
     {
         // TODO: Maker sure that only called from within the host
         
         $hubModel = new HubSubscriptionModel();
         $pending = $hubModel->getPendingAsyncVerifications();
         foreach ($pending as $params) {
             $this->_hubSendVerificationRequest($params);
         }
         
         $hubModel->removeTimedOutPendingSubscriptions();
     }
     
     public function hubpingAction()
     {
         // shedule retrieved topicURLs to be fetched and delivered
         
         // Disable rendering
          $this->_helper->viewRenderer->setNoRender();
          $this->_helper->layout()->disableLayout();

          // We require POST requests here.
          if (!$this->_request->isPost()) {
              $this->_response->setException(new OntoWiki_Http_Exception(400));
              return;
          }

          $params = array();

          // hub.mode
          if (!isset($_POST['hub.mode'])) {
              $this->_response->setException(new OntoWiki_Http_Exception(400));
              return;
          }
          $params['hub.mode'] = $_POST['hub.mode'];
          if (!($params['hub.mode'] === 'publish')) {
              $this->_response->setException(new OntoWiki_Http_Exception(400));
              return;
          }
          
          // hub.url (may be a string or an array)
          if (!isset($_POST['hub.url'])) {
              $this->_response->setException(new OntoWiki_Http_Exception(400));
              return;
          }
          $url = $_POST['hub.url'];
          if (is_string($url)) {
              $url = urldecode($url);
          } else if (is_array($url)) {
              $urlArray = array();
              foreach ($url as $u) {
                  $urlArray[] = urldecode($u);
              }
              $url = $urlArray;
          } else {
              $this->_response->setException(new OntoWiki_Http_Exception(400));
              return;
          }
          $params['hub.url'] = $url;
          
          $notificationModel = new HubNotificationModel();
          if (!$notificationModel->hasNotification($params)) {
              $notificationModel->addNotification($params);
          }
          
          $this->_scheduleDelivery();
          
          $this->_response->setHttpResponseCode(204);
          return $this->_response->sendResponse();
     }
     
     public function hubdeliverAction()
     {
         // TODO: Maker sure that only called from within the host
         // TODO: X-Hub-On-Behalf-Of
         // TODO: Authenticated Content Distribution
         
         // fetch and deliver sheduled notifications
         $notificationModel = new HubNotificationModel();
         $notifications = $notificationModel->getNotifications();
         
         $subscriptionModel = new HubSubscriptionModel();
         
         foreach ($notifications as $notification) {
             $subscriptions = $subscriptionModel->getSubscriptionsForTopic($notification['hub.url']);
             
             // TODO: Is the hub url correct here?
             $userAgent = 'OntoWiki Hub (+' . $this->_componentUrlBase . '; ' . count($subscriptions) . ' subscribers)';
             
             $modifiedSince = null;
             if (null !== $notification['last_fetch']) {
                 $modifiedSince = date('r', $notification['last_fetch']);
             }
             
             $client = Erfurt_App::getInstance()->getHttpClient($notification['hub.url'], array(
                  'maxredirects'  => 10,
                  'timeout'       => 30,
                  'useragent'     => $userAgent
             ));
             if (null != $modifiedSince) {
                 $client->setHeaders('If-Modified-Since', $modifiedSince);
             }
             
             $response = $client->request();
             $status = $response->getStatus(); 
             if ($status === 200) {
                 $body = trim($response->getBody());
                 // TODO: support alle Feed types... currently ATOM
                 
                 // Deliver to all subscribers
                 // TODO: retry failed deliveries later
                 foreach ($subscriptions as $subscription) {
                     $postClient = Erfurt_App::getInstance()->getHttpClient($subscription['hub.callback'], array(
                           'maxredirects'  => 0,
                           'timeout'       => 30
                      ));
                      $postClient->setMethod(Zend_Http_Client::POST);
                      $postClient->setHeaders('Content-Type', 'application/atom+xml');
                      $postClient->setRawData($body);
                      
                      $response = $client->request();
                      $status = $response->getStatus();
                      if (($status >= 200) && ($status < 300)) {
                          // TODO: log
                      } else {
                          // TODO: log
                      }
                 }
                 
                 // Delete notification at the end
                 $notificationModel->deleteNotification($notification);
             } else if (status === 304) {
                 // Ignore
                 $notificationModel->deleteNotification($notification);
                 continue;
             } else {
                 // TODO: log?! remove?!
             }
         }
     }
     
     private function _hubSendVerificationRequest($params)
     {
         $callbackURL = $params['hub.callback'];
         
         // Add hub.mode
         if (strrpos($callback, '?') === false) {
             // No query part yet
             $callbackURL .= '?hub.mode=' . $params['hub.mode'];
         } else {
             // We already have a query part
             $callbackURL .= '&hub.mode=' . $params['hub.mode'];
         }
         
         // Add hub.topic
         $callbackURL .= '&hub.topic=' . urlencode($params['hub.topic']);
         
         // Add hub.challenge (generated by caller of this methods)
         $callbackURL .= '&hub.challenge=' . $params['hub.challenge'];
         
         // Add lease seconds
         $leaseSeconds = self::DEFAULT_LEASE_SECONDS;
         if (isset($params['hub.lease_seconds'])) {
             $leaseSeconds = $params['hub.lease_seconds'];
         }
         $callbackURL .= '&hub.lease_seconds=' . $leaseSeconds;
         
         if (isset($params['hub.verify_token'])) {
             $callbackURL .= '&hub.verify_token=' . urlencode($params['hub.verify_token']);
         }
         
         // Execute the GET request to callback
         $hubModel = HubSubscriptionModel(); 
         $client = Erfurt_App::getInstance()->getHttpClient($callbackURL, array(
             'maxredirects'  => 0,
             'timeout'       => 30
         ));
         $response = $client->request();
         $status = $response->getStatus(); 
         if (($status >= 200) && ($status < 300)) {
             $body = trim($response->getBody());
             if ($body === $params['hub.challenge']) {
                 // successful, save/delete in model
                 if ($params['hub.mode'] === 'subscribe') {
                     $params['subscription_state'] = 'active';
                     $hubModel->updateSubscription($params);
                 } else {
                     $hubModel->deleteSubscription($params);
                 }
                 
                 return true;
             }
         }
         
         // If failed, we delete the subscription for sync mode or increment retries for async
         if ($params['hub.verify'] === 'async') {
             $params['number_of_retries'] = 'true';
             $hubModel->updateSubscription($params);
         } else {
             if ($params['hub.mode'] === 'subscribe') {
                 $hubModel->deleteSubscription($params);
             }
         }
         
         return false;
     }
     
     private function _scheduleVerification()
     {
         $url = $this->_componentUrlBase . '/hubperformasyncverifies';
         
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
         curl_setopt($ch, CURLOPT_TIMEOUT, 1);
         $result = curl_exec($ch);
         curl_close($ch);
     }
     
     private function _scheduleDelivery()
     {
         $url = $this->_componentUrlBase . '/hubdeliver';

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
          curl_setopt($ch, CURLOPT_TIMEOUT, 1);
          $result = curl_exec($ch);
          curl_close($ch);
     }
}
