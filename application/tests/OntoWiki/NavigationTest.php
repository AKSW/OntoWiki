<?php

require_once dirname (__FILE__) .'/../TestHelper.php';

class OntoWiki_NavigationTest extends PHPUnit_Framework_TestCase
{
    protected $_ontoWikiNavigation;
    
    public function setUp()
    {
        $this->_ontoWikiNavigation = new OntoWiki_Navigation ();
    }
    
    public function tearDown() 
    {
        
    }
    
    /**
     * Test cases for
     * OntoWiki_Navigation -> register ---------------------------------
     */
    
    public function testRegisterKeyEmptyOptions()
    {
        $this->_ontoWikiNavigation->register (
            'foo', array (), false
        );
        
        $check = array ( 'foo' => array(
            'route' => NULL, 'controller' => NULL, 'action' => NULL, 'name' => 'foo', 'active' => 'active'
        ));
        
        $this->assertEquals ( $check, $this->_ontoWikiNavigation->getNavigation () );
    }
    
    public function testRegisterKeyDefaultOptions()
    {
        $this->_ontoWikiNavigation->register (
            'foo', array('route' => NULL, 'controller' => NULL, 'action' => NULL, 'name' => 'foo'), false
        );
        
        $check = array ( 'foo' => array(
            'route' => NULL, 'controller' => NULL, 'action' => NULL, 'name' => 'foo', 'active' => 'active'
        ));
        
        $this->assertEquals ( $check, $this->_ontoWikiNavigation->getNavigation () );
    }
    
    public function testRegisterAlreadyUsedKeyDefaultOptions()
    {
        $this->_ontoWikiNavigation->register (
            'foo', array('route' => NULL, 'controller' => NULL, 'action' => NULL, 'name' => 'foo'), false
        );
        
        try {
            $this->_ontoWikiNavigation->register (
                'foo', array('route' => NULL, 'controller' => NULL, 'action' => NULL, 'name' => 'foo'), false
            );
        } catch ( Exception $e ) { 
            // Everything is fine
            return;
        }
        
        throw new Exception ('OntoWiki_Navigation->register have to trow an exception here!');
    }
    
    public function testRegisterAlreadyUsedKeyFilledOptions()
    {
        $this->_ontoWikiNavigation->register (
            'foo', array('route' => 'foo', 'controller' => 'bar', 'action' => 'bar', 'name' => 'foo'), false
        );
        
        try {
            $this->_ontoWikiNavigation->register (
                'foo', array('route' => 'foo', 'controller' => 'bar', 'action' => 'bar', 'name' => 'foo'), false
            );
        } catch ( Exception $e ) { 
            // Everything is fine
            return;
        }
        
        throw new Exception ('OntoWiki_Navigation->register have to trow an exception here!');
    }
    
    public function testRegisterNoKey()
    {
        try {
            $this->_ontoWikiNavigation->register ('', array(), false);
        } catch ( Exception $e ) { 
            // Everything is fine
            return;
        }
        
        throw new Exception ('OntoWiki_Navigation->register have to trow an exception here!');
    }
    
    public function testRegisterNullKey()
    {
        try {
            $this->_ontoWikiNavigation->register (null, array(), false);
        } catch ( Exception $e ) { 
            // Everything is fine
            return;
        }
        
        throw new Exception ('OntoWiki_Navigation->register have to trow an exception here!');
    }
    
    public function testRegisterNumberKey()
    {
        try {
            $this->_ontoWikiNavigation->register (0, array(), false);
            return;
        } catch ( Exception $e ) { 
            throw $e;
        }
    }    
    
    /**
     * Test cases for
     * OntoWiki_Navigation -> setActive --------------------------------
     */
     
    public function testSetActiveNoKey () 
    {
        try {
            $this->_ontoWikiNavigation->setActive ('');
        } catch ( Exception $e ) {
            if ( 'Navigation component with key \'\' not registered.' != $e->getMessage () ) {
                throw $e;
            }
        }
    }
     
    public function testSetActiveNullKey () 
    {
        try {
            $this->_ontoWikiNavigation->setActive (null);
        } catch ( Exception $e ) {
            if ( 'Navigation component with key \'\' not registered.' != $e->getMessage () ) {
                throw $e;
            }
        }
    }
     
    public function testSetActiveKey () 
    {
        $this->_ontoWikiNavigation->register ('foo', array(), false);;
        
        $this->_ontoWikiNavigation->setActive ('foo');
        
        $active = $this->_ontoWikiNavigation->getActive();
        if ('foo' != $active ['name']) {
            throw new Exception ('foo is not active. Active is '. $active ['name']);
        }
    }
     
    public function testSetActiveKeyCheckActive () 
    {
        $this->_ontoWikiNavigation->register ('foo', array(), false);;
        
        $active = $this->_ontoWikiNavigation->getActive();
        if ('foo' != $active ['name']) {
            throw new Exception ('foo is not active. Active is '. $active ['name']);
        }
    }
     
    public function testSetActiveKeyChangeActive () 
    {
        $this->_ontoWikiNavigation->register ('foo', array(), false);
        $this->_ontoWikiNavigation->register ('oldActive', array(), false);
        $this->_ontoWikiNavigation->setActive ('oldActive');
        
        $this->_ontoWikiNavigation->setActive ('foo');
        
        $active = $this->_ontoWikiNavigation->getActive();
        if ('foo' != $active ['name']) {
            throw new Exception ('foo is not active. Active is '. $active ['name']);
        }
    }
}
