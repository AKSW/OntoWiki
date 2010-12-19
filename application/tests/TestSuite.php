<?php

require_once 'TestHelper.php';

require_once _TESTROOT .'controllers/TestSuite.php';
require_once _TESTROOT .'OntoWiki/TestSuite.php';

require_once 'OntoWikiTest.php';

class TestSuite extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new TestSuite('OntoWiki Tests');
        
        $suite->addTestSuite('Controllers_TestSuite');
        $suite->addTestSuite('OntoWiki_TestSuite');
        
        $suite->addTestSuite('OntoWikiTest');
        
        return $suite;
    }
}
