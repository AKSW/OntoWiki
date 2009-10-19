<?php

require_once 'test_base.php';
require_once 'OntoWiki/Message.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWiki_MessageTest extends PHPUnit_Framework_TestCase
{
    
    protected $_fixture;
    
    public function setUp()
    {
        $this->_fixture = new OntoWiki_Message('The test string for the message object.', OntoWiki_Message::SUCCESS);
    }
    
    public function testMessageType()
    {
        $this->assertEquals($this->_fixture->getType(), 'success');
    }
    
    public function testMessageText()
    {
        $this->assertEquals($this->_fixture->getText(), 'The test string for the message object.');
    }
}

?>
