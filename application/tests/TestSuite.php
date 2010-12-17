<?php

require_once 'TestHelper.php';

require_once _TESTROOT .'controllers/TestSuite.php';

require_once 'OntoWikiTest.php';

//require_once _TESTROOT .'OntoWiki/InstancesTest.php';
//require_once _TESTROOT .'OntoWiki/MenuTest.php';
//require_once _TESTROOT .'OntoWiki/MessageTest.php';


class TestSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('OntoWiki Tests');
        
        $suite->addTestSuite('Controllers_TestSuite');
        
        $suite->addTestSuite('OntoWikiTest');
        
        /*
        $suite->addTestSuite('InstancesTest');
        $suite->addTestSuite('MenuTest');
        $suite->addTestSuite('MessageTest');
        
        */
        
        return $suite;
    }
}
