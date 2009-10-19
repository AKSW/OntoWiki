<?php

require_once 'test_base.php';
require_once 'OntoWiki/Component/Manager.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWiki_Component_ManagerTest extends PHPUnit_Framework_TestCase
{
    protected $_componentManager;
    
    public function testGetComponentPath()
    {
        $componentManager = new OntoWiki_Component_Manager(dirname(__FILE__));
        $this->assertEquals(dirname(__FILE__), $componentManager->getComponentPath());
    }
    
    public function testGetComponentsEmpty()
    {
        $componentManager = new OntoWiki_Component_Manager(dirname(__FILE__));
        $this->assertEquals(array(), $componentManager->getComponents());
    }
    
    public function testGetComponentsNotEmpty()
    {
        $this->_componentManager = new OntoWiki_Component_Manager(dirname(__FILE__) . '/components/');
        
        $mockArray = array(
            'mock' => array(
                'templates' => 'templates/', 
                'active'    => 'true', 
                'name'      => 'Mock', 
                'position'  => '1', 
                'path'      => dirname(__FILE__) . '/components/mock/'
            )
        );
        $this->assertEquals($mockArray, $this->_componentManager->getComponents());
        $this->assertEquals(true, $this->_componentManager->isComponentRegistered('mock'));
    }
    
    public function testInactiveComponentNotScanned()
    {
        $this->_componentManager = new OntoWiki_Component_Manager(dirname(__FILE__) . '/components2/');
        
        $this->assertEquals(true, $this->_componentManager->isComponentRegistered('mock1'));
        $this->assertEquals(false, $this->_componentManager->isComponentRegistered('mock2'));
    }
}

?>
