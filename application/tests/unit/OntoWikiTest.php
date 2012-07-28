<?php
/**
 * OntoWiki
 *
 * LICENSE
 *
 * This file is part of the OntoWiki project.
 * Copyright (C) 2006-2010, AKSW
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the 
 * Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * A copy of the GNU General Public License is bundled with this package in
 * the file LICENSE.txt. It is also available through the world-wide-web at 
 * this URL: http://opensource.org/licenses/gpl-2.0.php
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @version    $Id: $
 */

/**
 * This test class comtains tests for the OntoWiki index controller.
 * 
 * @category   OntoWiki
 * @package    OntoWiki
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006-2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
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
}
