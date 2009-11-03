<?php

require_once '../../test_base.php';
require_once 'OntoWiki/Menu.php';

// PHPUnit
require_once 'PHPUnit/Framework.php';

class OntoWiki_Menu_RegistryTest extends PHPUnit_Framework_TestCase
{
    protected $_registry;
    
    public function setUp()
    {
        //we need a fully loaded ow+erfurt here?!
        //$this->_registry = OntoWiki_Menu_Registry::getInstance();
    }

    public function testEmpty(){
        
    }
}

?>
