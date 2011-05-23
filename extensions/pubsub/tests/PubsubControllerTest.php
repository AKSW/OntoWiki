<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'TestHelper.php';

require_once '../HubSubscriptionModel.php';

// This constant will not be defined iff this file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'PubsubControllerTest::main');
}

class PubsubControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    /**
     * The main method, which executes all tests inside this class.
     * 
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(new ReflectionClass('PubsubControllerTest'));
    }

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(
            'default',
            ONTOWIKI_ROOT . 'application/config/application.ini'
        );
        parent::setUp();
    }

    public function testCallWithoutAction()
    {
        $this->dispatch('/pubsub');

        // We expect the error controller with error action here, since ther is
        // no default index action for this controller.
        $this->assertController('error');
        $this->assertAction('error');
    }

    public function testTestAction()
    {
        $this->dispatch('/pubsub/test');

        $this->assertController('pubsub');
        $this->assertAction('test');
        $this->assertResponseCode(200);

        $this->assertQueryContentContains('body', 'PubsubController is enabled');
    }

    public function testHubsubscriptionActionGet()
    {
        $this->dispatch('/pubsub/hubsubscription');

        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);        
    }

    public function testHubsubscriptionActionPostNoParams()
    {
        $this->request->setMethod('POST');

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }

    public function testHubsubscriptionActionPostInvalidCallback()
    {
        $this->request->setMethod('POST');

        $this->_request->setParams(array(
            'hub.callback' => 'http://example.org/pubsub/callback#fragment'
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }

    public function testHubsubscriptionActionPostInvalidMode()
    {
        $this->request->setMethod('POST');

        $this->_request->setParams(array(
            'hub.mode' => 'invalidHubMode'
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }

    public function testHubsubscriptionActionPostInvalidTopic()
    {
        $this->request->setMethod('POST');

        $this->_request->setParams(array(
            'hub.topic' => 'http://example.org/content/topic#fragment'
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }

    public function testHubsubscriptionActionPostInvalidVerify()
    {
        $this->request->setMethod('POST');
        $this->_request->setPost(array(
            'hub.verify' => 'invalidVerifyParam'
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(400);
    }

    public function testHubsubscriptionActionPostSubscribeAlreadySubscribed()
    {
        $callback = 'http://exmaple.org/callback';
        $topic = 'http://example.org/topic';
        $mode = 'subscribe';
        $verify = 'sync';

        HubSubscriptionModel::$testMode = true;
        HubSubscriptionModel::$testData = array();
        $hubModel = new HubSubscriptionModel();
        $hubModel->addSubscription(array(
            'hub.callback'  => $callback,
            'hub.topic'     => $topic,
            'hub.mode'      => $mode,
            'hub.verify'    => $verify,
            'hub.challenge' => md5('Test')
        ));

        $this->request->setMethod('POST');
        $this->_request->setPost(array(
            'hub.callback'  => $callback,
            'hub.topic'     => $topic,
            'hub.mode'      => $mode,
            'hub.verify'    => $verify
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(500, 'Response code is: ' . $this->_response->getHttpResponseCode());
    }    

    public function testHubsubscriptionActionPostSubscribeSyncVerificationFailNoOK()
    {
        $callback = 'http://exmaple.org/callback';
        $topic = 'http://example.org/topic';
        $mode = 'subscribe';
        $verify = 'sync';

        HubSubscriptionModel::$testMode = true;
        HubSubscriptionModel::$testData = array();

        $adapter = new Zend_Http_Client_Adapter_Test();
        Erfurt_App::$httpAdapter = $adapter;
        $adapter->setResponse(new Zend_Http_Response(
            404, 
            array(), 
            'Not Found'
        ));

        $this->request->setMethod('POST');
        $this->_request->setPost(array(
            'hub.callback'  => $callback,
            'hub.topic'     => $topic,
            'hub.mode'      => $mode,
            'hub.verify'    => $verify
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(500, 'Response code is: ' . $this->_response->getHttpResponseCode());
    }

    public function testHubsubscriptionActionPostSubscribeSyncVerificationFailWrongPayload()
    {
        $callback = 'http://exmaple.org/callback';
        $topic = 'http://example.org/topic';
        $mode = 'subscribe';
        $verify = 'sync';

        HubSubscriptionModel::$testMode = true;
        HubSubscriptionModel::$testData = array();

        $adapter = new Zend_Http_Client_Adapter_Test();
        Erfurt_App::$httpAdapter = $adapter;
        $adapter->setResponse(new Zend_Http_Response(
            200, 
            array(), 
            'ThisIsAWrongChallengeResult'
        ));

        $this->request->setMethod('POST');
        $this->_request->setPost(array(
            'hub.callback'  => $callback,
            'hub.topic'     => $topic,
            'hub.mode'      => $mode,
            'hub.verify'    => $verify
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('error');
        $this->assertAction('error');
        $this->assertResponseCode(500, 'Response code is: ' . $this->_response->getHttpResponseCode());
    }

    public function testHubsubscriptionActionPostSubscribeSyncVerificationSuccess204()
    {
        if (!defined('PUBSUB_TEST_MODE')) {
            define('PUBSUB_TEST_MODE', 1);
        }

        $callback = 'http://exmaple.org/callback';
        $topic = 'http://example.org/topic';
        $mode = 'subscribe';
        $verify = 'sync';

        HubSubscriptionModel::$testMode = true;
        HubSubscriptionModel::$testData = array();

        $adapter = new Zend_Http_Client_Adapter_Test();
        Erfurt_App::$httpAdapter = $adapter;
        $adapter->setResponse(new Zend_Http_Response(
            200, 
            array(), 
            'TestChallenge'
        ));

        $this->request->setMethod('POST');
        $this->_request->setPost(array(
            'hub.callback'  => $callback,
            'hub.topic'     => $topic,
            'hub.mode'      => $mode,
            'hub.verify'    => $verify
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('pubsub');
        $this->assertAction('hubsubscription');
        $this->assertResponseCode(204, 'Response code is: ' . $this->_response->getHttpResponseCode());
    }

    public function testHubsubscriptionActionPostSubscribeAsyncVerificationSuccess202()
    {
        if (!defined('PUBSUB_TEST_MODE')) {
            define('PUBSUB_TEST_MODE', 1);
        }

        $callback = 'http://exmaple.org/callback';
        $topic = 'http://example.org/topic';
        $mode = 'subscribe';
        $verify = 'async';

        HubSubscriptionModel::$testMode = true;
        HubSubscriptionModel::$testData = array();

        $adapter = new Zend_Http_Client_Adapter_Test();
        Erfurt_App::$httpAdapter = $adapter;
        $adapter->setResponse(new Zend_Http_Response(
            200, 
            array(), 
            'TestChallenge'
        ));

        $this->request->setMethod('POST');
        $this->_request->setPost(array(
            'hub.callback'  => $callback,
            'hub.topic'     => $topic,
            'hub.mode'      => $mode,
            'hub.verify'    => $verify
        ));

        $this->dispatch('/pubsub/hubsubscription');
        $this->assertController('pubsub');
        $this->assertAction('hubsubscription');
        $this->assertResponseCode(202, 'Response code is: ' . $this->_response->getHttpResponseCode());
    }

    public function testHubperformasyncverifiesActionNoPending()
    {
        HubSubscriptionModel::$testMode = true;
        HubSubscriptionModel::$testData = array();

        $this->dispatch('/pubsub/hubperformasyncverifies');
        $this->assertController('pubsub');
        $this->assertAction('hubperformasyncverifies');
        $this->assertResponseCode(200, 'Response code is: ' . $this->_response->getHttpResponseCode());
    }
}

// If this file is executed directly, execute the tests.
if (PHPUnit_MAIN_METHOD === 'PubsubControllerTest::main') {
    PubsubControllerTest::main();
}
