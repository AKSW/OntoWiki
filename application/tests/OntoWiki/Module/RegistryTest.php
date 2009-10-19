<?php

require_once 'test_base.php';
require_once 'OntoWiki/Module/Registry.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWiki_Module_RegistryTest extends PHPUnit_Framework_TestCase
{
    protected $_registry;
    
    public function setUp()
    {
        $this->_registry = OntoWiki_Module_Registry::getInstance();
    }
    
    public function tearDown()
    {
        $this->_registry->resetInstance();
    }
    
    public function testRegisterModuleEnabled()
    {
        $this->_registry->register('testmodule');
        
        $this->assertEquals(true, $this->_registry->isModuleEnabled('testmodule'));
    }
    
    public function testRegisterModuleDisabled()
    {
        $this->_registry->register('testmodule', OntoWiki_Module_Registry::DEFAULT_NAMESPACE, array('enabled' => false));
        
        $this->assertEquals(false, $this->_registry->isModuleEnabled('testmodule'));
    }
    
    public function testRegisterModuleWithNamespace()
    {
        $this->_registry->register('testmodule', 'test namespace');
        
        $this->assertEquals(false, $this->_registry->isModuleEnabled('testmodule'));
        $this->assertEquals(true, $this->_registry->isModuleEnabled('testmodule', 'test namespace'));
    }
    
    public function testDisableModule()
    {
        $this->_registry->register('testmodule');
        
        $this->assertEquals(true, $this->_registry->isModuleEnabled('testmodule'));
        $this->_registry->disableModule('testmodule');
        $this->assertEquals(false, $this->_registry->isModuleEnabled('testmodule'));
    }
    
    public function testGetModulesReturnesEnabledModulesOnly()
    {
        $this->_registry->register('enabledmodule1');
        $this->assertEquals(true, $this->_registry->isModuleEnabled('enabledmodule1'));
        
        $this->_registry->register('disabledmodule', OntoWiki_Module_Registry::DEFAULT_NAMESPACE, array('enabled' => false));
        $this->assertEquals(false, $this->_registry->isModuleEnabled('disabledmodule'));
        
        $this->_registry->register('enabledmodule2', OntoWiki_Module_Registry::DEFAULT_NAMESPACE);
        $this->assertEquals(true, $this->_registry->isModuleEnabled('enabledmodule2'));
        
        $expected = array(
            'enabledmodule1' => array(
                'enabled' => true, 
                'id'      => 'enabledmodule1', 
                'classes' => '', 
                'name'    => 'enabledmodule1'
            ), 
            'enabledmodule2' => array(
                'enabled' => true, 
                'name'    => 'enabledmodule2', 
                'id'      => 'enabledmodule2', 
                'classes' => ''
            )
        );
        
        $this->assertEquals($expected, $this->_registry->getModules());
        $this->assertEquals(array(), $this->_registry->getModules('othernamespace'));
    }
    
    public function testGetModulesReturnesAllOptions()
    {
        $options1 = array(
            'enabled' => true, 
            'class'   => 'foo-class', 
            'id'      => 'bar-id', 
            'name'    => 'foomodule', 
            'classes' => ''
        );
        $this->_registry->register('foomodule', 'foo', $options1);
        
        $options2 = array(
            'enabled' => false, 
            'class'   => 'fuu-class', 
            'id'      => 'baz-id'
        );
        $this->_registry->register('barmodule', 'foo', $options2);
        
        $this->assertEquals(array('foomodule' => $options1), $this->_registry->getModules('foo'));
    }
}

?>
