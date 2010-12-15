<?php

require_once 'test_base.php';

require_once _TESTROOT .'controllers/ServiceControllerTest.php';

/*
require_once _TESTROOT .'OntoWiki/InstancesTest.php';
require_once _TESTROOT .'OntoWiki/MenuTest.php';
require_once _TESTROOT .'OntoWiki/MessageTest.php';
require_once _TESTROOT .'OntoWiki/OntoWikiTest.php';
*/

class TestSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('OntoWiki Tests');
        
        $suite->addTestSuite('ServiceControllerTest');
        
        /*
        $suite->addTestSuite('InstancesTest');
        $suite->addTestSuite('MenuTest');
        $suite->addTestSuite('MessageTest');
        $suite->addTestSuite('OntoWikiTest');
        */
        
        return $suite;
    }
}
