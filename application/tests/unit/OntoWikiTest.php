<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
