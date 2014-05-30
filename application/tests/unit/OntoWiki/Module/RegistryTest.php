<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

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
        $moduleName = 'test';
        $this->_registry->register($moduleName, 'TestModule.php');

        $this->assertEquals(true, $this->_registry->isModuleEnabled($moduleName));
    }

    public function testRegisterModuleDisabled()
    {
        $moduleName = 'test';
        $this->_registry->register(
            $moduleName, 'TestModule.php', OntoWiki_Module_Registry::DEFAULT_CONTEXT, array('enabled' => false)
        );

        $this->assertEquals(false, $this->_registry->isModuleEnabled($moduleName));
    }

    public function testDisableModule()
    {
        $moduleName = 'test';
        $this->_registry->register($moduleName, 'TestModule.php');

        $this->assertEquals(true, $this->_registry->isModuleEnabled($moduleName));
        $this->_registry->disableModule($moduleName);
        $this->assertEquals(false, $this->_registry->isModuleEnabled($moduleName));
    }

    public function testGetModulesReturnesAllModules()
    {
        $this->_registry->register('enabledmodule1', 'Enabledmodule1Module.php');
        $this->assertEquals(true, $this->_registry->isModuleEnabled('enabledmodule1'));

        $this->_registry->register(
            'disabledmodule', 'DisabledmoduleModule.php', OntoWiki_Module_Registry::DEFAULT_CONTEXT,
            array('enabled' => false)
        );
        $this->assertEquals(false, $this->_registry->isModuleEnabled('disabledmodule'));

        $this->_registry->register('enabledmodule2', 'Enabledmodule2Module.php');
        $this->assertEquals(true, $this->_registry->isModuleEnabled('enabledmodule2'));

        $expected = array(
            'enabledmodule1' => array(
                'enabled'       => true,
                'id'            => 'enabledmodule1',
                'classes'       => '',
                'name'          => 'Enabledmodule1',
                'extensionName' => 'enabledmodule1',
                'private'       => array()
            ),
            'enabledmodule2' => array(
                'enabled'       => true,
                'name'          => 'Enabledmodule2',
                'id'            => 'enabledmodule2',
                'classes'       => '',
                'extensionName' => 'enabledmodule2',
                'private'       => array()
            ),
            'disabledmodule' => array(
                'enabled'       => false,
                'name'          => 'Disabledmodule',
                'id'            => 'disabledmodule',
                'classes'       => '',
                'extensionName' => 'disabledmodule',
                'private'       => array()
            )
        );

        $actualModules = $this->_registry->getModules();
        $this->assertTrue(isset($actualModules['enabledmodule1']));
        $this->assertTrue(isset($actualModules['enabledmodule2']));
        $this->assertTrue(isset($actualModules['disabledmodule']));
        $this->assertEquals($expected['enabledmodule1'], $actualModules['enabledmodule1']->toArray());
        $this->assertEquals($expected['enabledmodule2'], $actualModules['enabledmodule2']->toArray());
        $this->assertEquals($expected['disabledmodule'], $actualModules['disabledmodule']->toArray());
    }

    public function testGetModulesReturnesAllOptions()
    {
        $optionsA = array(
            'enabled'       => true,
            'class'         => 'foo-class',
            'id'            => 'bar-id',
            'name'          => 'foo',
            'classes'       => '',
            'extensionName' => 'disabledmodule',
            'private'       => array()
        );
        $this->_registry->register('foo', 'FooModule.php', OntoWiki_Module_Registry::DEFAULT_CONTEXT, $optionsA);

        $optionsB = array(
            'enabled'       => false,
            'class'         => 'fuu-class',
            'id'            => 'baz-id',
            'name'          => 'foo',
            'classes'       => '',
            'extensionName' => 'disabledmodule',
            'private'       => array()
        );
        $this->_registry->register('bar', 'BarModule.php', OntoWiki_Module_Registry::DEFAULT_CONTEXT, $optionsB);

        $actualModules = $this->_registry->getModules();
        $this->assertTrue(isset($actualModules['foo']));
        $this->assertTrue(isset($actualModules['bar']));
        $this->assertEquals($optionsA, $actualModules['foo']->toArray());
        $this->assertEquals($optionsB, $actualModules['bar']->toArray());
    }
}
