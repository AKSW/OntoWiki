<?php

class OntoWiki_NavigationTest extends PHPUnit_Framework_TestCase
{
    private $_ontoWikiNavigation = null;
    
    public function setUp()
    {
        $this->_ontoWikiNavigation = new OntoWiki_Navigation();
    }
        
    public function testRegisterKeyEmptyOptions()
    {
        $this->_ontoWikiNavigation->register('foo', array (), false);
        
        $check = array('foo' => array(
            'route'      => null, 
            'controller' => null, 
            'action'     => null, 
            'name'       => 'foo', 
            'active'     => 'active'
        ));
        
        $this->assertEquals($check, $this->_ontoWikiNavigation->getNavigation());
    }
    
    public function testRegisterKeyDefaultOptions()
    {
        $this->_ontoWikiNavigation->register(
            'foo', 
            array(
                'route'      => null, 
                'controller' => null, 
                'action'     => null, 
                'name'       => 'foo'
            ), 
            false
        );
        
        $check = array('foo' => array(
            'route'      => null, 
            'controller' => null, 
            'action'     => null, 
            'name'       => 'foo', 
            'active'     => 'active'
        ));
        
        $this->assertEquals($check, $this->_ontoWikiNavigation->getNavigation());
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testRegisterAlreadyUsedKeyDefaultOptions()
    {
        $this->_ontoWikiNavigation->register(
            'foo', 
            array(
                'route'      => null, 
                'controller' => null, 
                'action'     => null, 
                'name'       => 'foo'
            ), 
            false
        );
        
        $this->_ontoWikiNavigation->register(
            'foo', 
            array(
                'route'      => null, 
                'controller' => null, 
                'action'     => null, 
                'name'       => 'foo'
            ), 
            false
        );
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testRegisterAlreadyUsedKeyFilledOptions()
    {
        $this->_ontoWikiNavigation->register(
            'foo', 
            array(
                'route'      => 'foo', 
                'controller' => 'bar', 
                'action'     => 'bar', 
                'name'       => 'foo'
            ), 
            false
        );
        
        $this->_ontoWikiNavigation->register(
            'foo', 
            array(
                'route'      => 'foo', 
                'controller' => 'bar', 
                'action'     => 'bar', 
                'name'       => 'foo'
            ), 
            false
        );
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testRegisterNoKey()
    {
        $this->_ontoWikiNavigation->register('', array(), false);
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testRegisterNullKey()
    {
        $this->_ontoWikiNavigation->register(null, array(), false);
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testRegisterNumberKey()
    {
        $this->_ontoWikiNavigation->register(0, array(), false);
    }    
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testSetActiveNoKey() 
    {
        $this->_ontoWikiNavigation->setActive('');
    }
    
    /**
     * @expectedException OntoWiki_Exception
     */
    public function testSetActiveNullKey() 
    {
        $this->_ontoWikiNavigation->setActive(null);
    }
    
    public function testSetActiveKey() 
    {
        $this->_ontoWikiNavigation->register('foo', array(), false);
        $this->_ontoWikiNavigation->setActive('foo');
        
        $activeItem = $this->_ontoWikiNavigation->getActive();
        $this->assertEquals('foo', $activeItem['name']);
    }
     
    public function testNoSetActiveKeyCheckActive() 
    {
        $this->_ontoWikiNavigation->register('foo', array(), false);
        
        $activeItem = $this->_ontoWikiNavigation->getActive();
        $this->assertEquals('foo', $activeItem['name']);
    }
    
    public function testNoSetActiveKeyCheckNotActiveMultipleItems() 
    {
        $this->_ontoWikiNavigation->register('foo', array(), false);
        $this->_ontoWikiNavigation->register('bar', array(), false);
        
        $activeItem = $this->_ontoWikiNavigation->getActive();
        $this->assertNotEquals('bar', $activeItem['name']);
    }
     
    public function testSetActiveKeyChangeActive() 
    {
        $this->_ontoWikiNavigation->register('foo', array(), false);
        $this->_ontoWikiNavigation->register('oldActive', array(), false);
        $this->_ontoWikiNavigation->setActive('oldActive');
        $this->_ontoWikiNavigation->setActive ('foo');
        
        $activeItem = $this->_ontoWikiNavigation->getActive();
        $this->assertEquals('foo', $activeItem['name']);
    }
}
