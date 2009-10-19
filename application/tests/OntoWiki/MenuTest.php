<?php

require_once 'test_base.php';
require_once 'OntoWiki/Menu.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWiki_MenuTest extends PHPUnit_Framework_TestCase
{
    protected $_menu;
    
    public function setUp()
    {
        $subMenu1 = new OntoWiki_Menu();
        $subMenu1->setEntry('Sub Entry 1', 'Sub Value 1');
        
        $subMenu2 = new OntoWiki_Menu();
        $subMenu2->setEntry('Sub Entry 2', 'Old Sub Value 2')
                 ->setEntry('Sub Entry 2', 'New Sub Value 2');
        
        $this->_menu = new OntoWiki_Menu();
        $this->_menu->setEntry('Entry 1', 'Value 1')
                    ->setEntry('Sub Menu 1', $subMenu1)
                    ->setEntry('Sub Menu 2', $subMenu2);
    }
    
    public function testSetEntryString()
    {
        $menu = new OntoWiki_Menu();
        $menu->setEntry('Entry 1', 'Value 1');
        
        $this->assertEquals($menu->toArray(), array('Entry 1' => 'Value 1'));
    }
    
    public function testSetEntryObject()
    {
        $subMenu1 = new OntoWiki_Menu();
        $subMenu1->setEntry('Sub Entry 1', 'Sub Value 1');
        
        $menu = new OntoWiki_Menu();
        $menu->setEntry('Sub Menu 1', $subMenu1);
        
        $this->assertEquals($menu->toArray(), array('Sub Menu 1' => array('Sub Entry 1' => 'Sub Value 1')));
        
    }
    
    public function testReplaceEntry()
    {
        $menu = new OntoWiki_Menu();
        $menu->setEntry('Entry 1', 'Old Value')
             ->setEntry('Entry 1', 'New Value');
        
        $this->assertEquals($menu->toArray(), array('Entry 1' => 'New Value'));
    }
    
    public function testToArray()
    {
        $expected = array(
            'Entry 1' => 'Value 1', 
            'Sub Menu 1' => array('Sub Entry 1' => 'Sub Value 1'), 
            'Sub Menu 2' => array('Sub Entry 2' => 'New Sub Value 2')
        );
        $this->assertEquals($this->_menu->toArray(), $expected);
        
        $this->_menu->setEntry('Sub Menu 1', 'Replaced Sub Menu Entry');
        $expected = array(
            'Entry 1' => 'Value 1', 
            'Sub Menu 1' => 'Replaced Sub Menu Entry', 
            'Sub Menu 2' => array('Sub Entry 2' => 'New Sub Value 2')
        );
        $this->assertEquals($this->_menu->toArray(), $expected);
    }
    
    public function testToJson()
    {
        $expected = '{"Entry 1":"Value 1","Sub Menu 1":{"Sub Entry 1":"Sub Value 1"},"Sub Menu 2":{"Sub Entry 2":"New Sub Value 2"}}';
        $this->assertEquals($expected, $this->_menu->toJson(false));
        
        $this->_menu->setEntry('Sub Menu 1', 'Replaced Sub Menu Entry');
        $expected = '{"Entry 1":"Value 1","Sub Menu 1":"Replaced Sub Menu Entry","Sub Menu 2":{"Sub Entry 2":"New Sub Value 2"}}';
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testWrongKey()
    {
        $menu = new OntoWiki_Menu();
        $menu->setEntry(null, 'Bar');
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testContentError()
    {
        $menu = new OntoWiki_Menu();
        $menu->setEntry('Foo', 12345);
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testReplaceError()
    {
        $menu = new OntoWiki_Menu();
        $menu->setEntry('Existing Key', 'Bar');
        $menu->setEntry('Existing Key', 'Baz', false);
    }
}

?>
