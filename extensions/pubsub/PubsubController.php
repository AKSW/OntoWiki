<?php
require_once 'HubSubscriptionModel.php';
require_once 'HubNotificationModel.php';

class PubsubController extends OntoWiki_Controller_Component
{
    const DEFAULT_LEASE_SECONDS = 2592000; // 30 days
    const CHALLENGE_SALT        = 'csaiojwef89456nucekljads8tv589ncefn4c5m90ikdf9df5s';
    const TEST_CHALLENGE        = 'TestChallenge'; 
    
    public function testAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        echo 'PubsubController is enabled';
    }

    // public function subscribeuiAction()
    //     {
    //         OntoWiki_Navigation::disableNavigation();
    // 
    //         $toolbar = $this->_owApp->toolbar;
    //         $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save', 'id' => 'save_btn'))
    //             ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
    //         $this->view->placeholder('main.window.toolbar')->set($toolbar);
    // 
    //         $translate  = $this->_owApp->translate;
    //         $windowTitle = $translate->_('Subscribe to Feed');
    //         $this->view->placeholder('main.window.title')->set($windowTitle);
    // 
    //         $this->view->formActionUrl = $this->_config->urlBase . 'pubsub/subscribe';
    //         $this->view->formMethod    = 'get';
    //         $this->view->formClass     = 'simple-input input-justify-left';
    //         $this->view->formName      = 'subsribe';
    //     }

    public function subscribeAction()
    {
        // We require GET requests here.
        if (!$this->_request->isGet()) {
            return $this->_exception(400, 'Only GET allowed for subscription');
        }
        
        $get = $this->_request->getQuery();
        
        // No params, so we show the form
        if (empty($get)) {
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
            
            return;
        }
        
        if (!isset($get['topic']) || empty($get['topic'])) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('No topic! Nothing to subscribe..', OntoWiki_Message::ERROR)
            );
            $this->_log('No topic! Nothing to subscribe..'); 
            return;
        }

        $success = false;
        try {
            require_once 'lib/subscriber.php';
            
            $hubUrl      = $this->_privateConfig->hubUrl; 
            $topicUrl    = $get['topic'];
            $callbackUrl = $this->_getCallbackUrl();
            
            $s = new Subscriber($hubUrl, $callbackUrl);
            ob_start();
            $response = $s->subscribe($topicUrl);
            $result = ob_end_clean();
            
            $this->_log('Subscriber Result: ' . $result);
            
            if ($response !== false) {
                $success = true;
            }
        } catch (Exception $e) {
            $this->_log('Subscriber Exception: ' . $e->getMessage());
        } 
        
        if ($success) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Sucessfully subscribed', OntoWiki_Message::SUCCESS)
            );
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Subscription failed', OntoWiki_Message::ERROR)
            );
        }
    }

    public function callbackAction()
    {
        // Disable rendering
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        if ($this->_request->isPost()) {
            $this->_handleCallbackPost(); // Delivery
        } else if ($this->_request->isGet()) {
            $this->_handleCallbackGet(); // Verification
        } else {
            return $this->_exception(400, 'Callback only supports POST/GET requests');
        }
    }
    
    private function _handleCallbackPost()
    {
        $this->_log('Handling pubsub callback (delivery) now...');
// TODO: support others than ATOM
        // Make sure the content type is one of the supported.
        $contentType = strtolower($this->_request->getHeader('Content-Type'));
        if ($contentType !== 'application/atom+xml') {
            return $this->_exception(400, 'Unsupported Content-Type');
        }
        
        // We need the raw POST here.
        $atomData = file_get_contents('php://input');
        
        // Create a new event.
// TODO: Create naming schema for events!
        try {
            $event = new Erfurt_Event('onExternalFeedDidChange');
            $event->feedData = $atomData;
            $event->trigger();
        } catch (Exception $e) {
            return $this->_exception(500, 'Error handling the delivery content: ' . $e->getMessage());
        }
        
// TODO: Schedule event and return 202 Accepted here. Spec wants us to quickly return!
// TODO: X-Hub-On-Behalf-Of header.
        $this->_response->setHttpResponseCode(204); // No Content
        return $this->_response->sendResponse();
    }
    
    private function _handleCallbackGet()
    {
// TODO: store subscriptions locally in order to check the params an be able to unsubscribe.    
    
        $this->_log('Handling pubsub callback (verification) now...');
        
        $get = $this->_request->getQuery();
        
        if (!isset($get['hub_mode'])) {
            return $this->_exception(400, 'hub.mode parameter required');
        }
        
        if (!isset($get['hub_topic'])) {
            return $this->_exception(400, 'hub.topic parameter required');
        }
        
        if (!isset($get['hub_challenge'])) {
            return $this->_exception(400, 'hub.challenge parameter required');
        }
        $challenge = $get['hub_challenge'];
        
        if (!isset($get['hub_lease_seconds'])) {
            return $this->_exception(400, 'hub.lease_seconds parameter required');
        }
        
        $this->_response->setResonseBody($challenge);
        $this->_response->setHttpResponseCode(200);
        return $this->_response->sendResponse();   
    }
    

/**********************************************************************************************************************/
/*** Hub related actions **********************************************************************************************/
/**********************************************************************************************************************/
    
    public function hubbubAction()
    {
        // Disable rendering
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        
        // We require POST requests.
        if (!$this->_request->isPost()) {
            return $this->_exception(400, 'Only POST allowed');
        }
        $post = $this->_request->getPost();
        
        // Check for hub.mode
        if (!isset($post['hub_mode'])) {
            return $this->_exception(400, 'hub.mode is missing');
        }
        $mode = $post['hub_mode'];
        if ($mode === 'publish') {
            try {
                $this->_handleHubNotification($post);
            } catch (Exception $e) {
                return $this->_exception(400, $e->getMessage());
            }
            
        } else if (($mode === 'subscribe') ||($mode === 'unsubscribe')) {
            try {
                $this->_handleHubSubscription($post);
            } catch (Exception $e) {
                return $this->_exception(400, $e->getMessage());
            }
        } else {
            return $this->_exception(400, 'hub.mode is invalid');
        }
    }
    
    public function hubperformasyncverifiesAction()
    {
        // TODO: Make sure that only called from within the host
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $this->_log('Performing async verifications now');

        $hubModel = new HubSubscriptionModel();
        $pending = $hubModel->getPendingAsyncVerifications();
        foreach ($pending as $params) {
            $this->_hubSendVerificationRequest($params);
        }
        $hubModel->removeTimedOutPendingSubscriptions();

        $this->_response->setHttpResponseCode(200);
        return $this->_response->sendResponse();
    }
    
    public function hubdeliverAction()
    {
        // TODO: Maker sure that only called from within the host
        // TODO: X-Hub-On-Behalf-Of
        // TODO: Authenticated Content Distribution
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        // fetch and deliver sheduled notifications
        $notificationModel = new HubNotificationModel();
        $notifications = $notificationModel->getNotifications();

        $this->_log('Performing delivery now: ' . count($notifications) . ' notifications');

        $subscriptionModel = new HubSubscriptionModel();

        foreach ($notifications as $i=>$notification) {
            $subscriptions = $subscriptionModel->getSubscriptionsForTopic($notification['hub.url']);
            
            $this->_log(count($subscriptions) . ' subsriptions for notification ' . $i . ': ' . $notification['hub.url']);

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
                $this->_log('Notification payload: ' . $body);
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

                    $postResponse = $postClient->request();
                    $postStatus = $postResponse->getStatus();
                    // TODO: better log; log levels?
                    $this->_log('Callback Response: ' . $postStatus . ' - ' . (string)$postResponse->getBody());
                    if (($postStatus >= 200) && ($postStatus < 300)) {
                        $this->_log('Delivery sucess (' . $postStatus . '): ' . $subscription['hub.callback']);
                    } else {
                        $this->_log('Delivery failure (' . $postStatus . '): ' . $subscription['hub.callback']);
                    }
                }

                // Delete notification at the end
                $notificationModel->deleteNotification($notification);
            } else if (status === 304) {
                // Ignore
                $this->_log('Ignored content as result of 304');
                $notificationModel->deleteNotification($notification);
                continue;
            } else {
                $this->_log('Unexpected status code: ' . $status);
                $notificationModel->deleteNotification($notification);
                continue;
            }
        }
    }
    
    private function _handleHubSubscription($post)
    {
        $params = array();

        // hub.mode
        $params['hub.mode'] = $post['hub_mode'];

        // hub.callback
        if (!isset($post['hub_callback'])) {
            return $this->_exception(400, 'hub.callback is missing');
        }
        $params['hub.callback'] = urldecode($post['hub_callback']);
        if (strrpos($params['hub.callback'], '#') !== false) {
            return $this->_exception(400, 'hub.callback is invalid');
        }

        // hub.topic
        if (!isset($post['hub_topic'])) {
            return $this->_exception(400, 'hub.topic is missing');
        }
        $params['hub.topic'] = urldecode($post['hub_topic']);
        if (strrpos($params['hub.topic'], '#') !== false) {
            return $this->_exception(400, 'hub.topic is invalid');
        }

        // hub verify
        if (!isset($post['hub_verify'])) {
            return $this->_exception(400, 'hub.verify is missing');
        }
        $verify = $post['hub_verify'];
        // supported values for hub.verify: sync, async
        if (!(($verify === 'sync') || ($verify === 'async'))) {
            return $this->_exception(400, 'hub.verify is invalid');
        }
        $params['hub.verify'] = $verify;

        // optional: hub.lease_seconds
        if (isset($post['hub_lease_seconds'])) {
            $params['hub.lease_seconds'] = $post['hub_lease_seconds'];
        }

        // optional: hub.secret (SHOULD only be provided when hub is behind HTTPS!)
        if (isset($post['hub_secret'])) {
            $params['hub.secret'] = urldecode($post['hub_secret']);
        }

        // optional: hub.verify_token
        if (isset($post['hub_verify_token'])) {
            $params['hub.verify_token'] = urldecode($post['hub_verify_token']);
        }

        // Create a challenge for the verification 
        $challenge = uniqid(mt_rand(), true) . uniqid(mt_rand(), true) . self::CHALLENGE_SALT;
        $challenge = md5($challenge);
        $params['hub.challenge'] = $challenge;
        if (defined('PUBSUB_TEST_MODE')) {
            $params['hub.challenge'] = self::TEST_CHALLENGE;
        }

        $hubModel = new HubSubscriptionModel();
        if ($hubModel->hasSubscription($params)) {
            if ($params['hub.mode'] === 'subscribe') {
                // TODO: Support refreshing of subscription here, as defined in protocol spec.
                return $this->_exception(500, 'already subscribed');
            }
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

            // If we reach this, verification has failed (sync).
            return $this->_exception(500, 'verification (sync) failed');
        }

        $this->_scheduleVerification();
        $this->_response->setHttpResponseCode(202);
        return $this->_response->sendResponse();
    }

    public function _handleHubNotification($post)
    {
        // shedule retrieved topicURLs to be fetched and delivered

        $params = array();

        // hub.url (may be a string or an array)
        if (!isset($post['hub_url'])) {
            return $this->_exception(400, 'hub.url is missing');
        }
        $url = $post['hub_url'];
        if (is_string($url)) {
            $url = urldecode($url);
        } else if (is_array($url)) {
            $urlArray = array();
            foreach ($url as $u) {
                $urlArray[] = urldecode($u);
            }
            $url = $urlArray;
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

    private function _hubSendVerificationRequest($params)
    {
        $callbackURL = $params['hub.callback'];

        // Add hub.mode
        if (strrpos($callbackURL, '?') === false) {
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
        $hubModel = new HubSubscriptionModel(); 
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
        if (defined('PUBSUB_TEST_MODE')) {
            return;
        }
        
        $url = $this->_owApp->getUrlBase() . 'pubsub/hubperformasyncverifies';

        $this->_log('Scheduling verification now: ' . $url);

        ob_start();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = ob_get_clean();
        
        $this->_log('Scheduling result: ' . $result);
    }

    private function _scheduleDelivery()
    {
        if (defined('PUBSUB_TEST_MODE')) {
            return;
        }

        $url = $this->_owApp->getUrlBase() . 'pubsub/hubdeliver';

        $this->_log('Scheduling delivery now: ' . $url);

        ob_start();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = ob_get_clean();
        
        $this->_log('Scheduling result: ' . $result);
    }
    
    private function _getCallbackUrl()
    {
        return $this->_owApp->getUrlBase() . "pubsub/callback/";
    }
    
    private function _log($msg)
    {
        $logger = $this->_owApp->getCustomLogger('pubsub');
        $logger->debug($msg);        
    }
    
    private function _exception($code, $debugMessage)
    {
        if (defined('_OWDEBUG')) {
            $this->_log('OntoWiki_Http_Exception: ' . $code . ' ' . $debugMessage);
            $this->_response->setException(new OntoWiki_Http_Exception($code, $debugMessage));
        } else {
            $this->_response->setException(new OntoWiki_Http_Exception($code));
        }
    }
}
