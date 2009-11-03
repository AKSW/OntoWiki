<?php

require_once '../test_base.php';
require_once '../../src/application/classes/OntoWiki.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWikiTest extends PHPUnit_Framework_TestCase
{
    protected $_application;
    
    public function setUp()
    {
        $this->_application = OntoWiki::getInstance();
    }
    
    public function testGetInstance()
    {
        $newInstance = OntoWiki::getInstance();
        
        $this->assertSame($this->_application, $newInstance);
    }
    
    public function testSetValue()
    {
        $this->_application->foo = 'bar';

        $this->assertEquals($this->_application->foo, 'bar');
    }
    
    public function testIssetValue()
    {
        $this->assertEquals(isset($this->_application->anotherFoo), false);
    }
    
    public function testGetValue()
    {
        $this->assertEquals($this->_application->YetAnotherFoo, null);
        
        $this->_application->YetAnotherFoo = 'bar';
        
        $this->assertEquals($this->_application->YetAnotherFoo, 'bar');
    }

    /* that method does not exist anymore in 0.9.5, right?
     *
    public function testSetUrlBase()
    {
        $this->_application->setUrlBase('http://example.com/test', true);
        $this->assertEquals($this->_application->urlBase, 'http://example.com/test');
        $this->assertEquals($this->_application->staticUrlBase, 'http://example.com/test');
        
        define('_OWBOOT', 'index.php');
        $this->_application->setUrlBase('http://example.com/test/', false);
        $this->assertEquals($this->_application->urlBase, 'http://example.com/test/index.php');
        $this->assertEquals($this->_application->staticUrlBase, 'http://example.com/test/');
    }*/
}



?>
