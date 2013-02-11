<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class OntoWiki_MenuTest extends PHPUnit_Framework_TestCase
{
    private $_menu = null;

    public function setUp()
    {
        $this->_menu = new OntoWiki_Menu();
    }

    public function testSetEntryString()
    {
        $this->_menu->setEntry('Entry 1', 'Value 1');

        $this->assertEquals($this->_menu->toArray(), array('Entry 1' => 'Value 1'));
    }

    public function testSetEntryObject()
    {
        $subMenuA = new OntoWiki_Menu();
        $subMenuA->setEntry('Sub Entry 1', 'Sub Value 1');

        $this->_menu->setEntry('Sub Menu 1', $subMenuA);

        $this->assertEquals($this->_menu->toArray(), array('Sub Menu 1' => array('Sub Entry 1' => 'Sub Value 1')));

    }

    public function testReplaceEntry()
    {
        $this->_menu->setEntry('Entry 1', 'Old Value')
            ->setEntry('Entry 1', 'New Value');

        $this->assertEquals($this->_menu->toArray(), array('Entry 1' => 'New Value'));
    }

    public function testToArray()
    {
        $subMenuA = new OntoWiki_Menu();
        $subMenuA->setEntry('Sub Entry 1', 'Sub Value 1');

        $subMenuB = new OntoWiki_Menu();
        $subMenuB->setEntry('Sub Entry 2', 'Old Sub Value 2')
            ->setEntry('Sub Entry 2', 'New Sub Value 2');

        $this->_menu->setEntry('Entry 1', 'Value 1')
            ->setEntry('Sub Menu 1', $subMenuA)
            ->setEntry('Sub Menu 2', $subMenuB);

        $expected = array(
            'Entry 1'    => 'Value 1',
            'Sub Menu 1' => array('Sub Entry 1' => 'Sub Value 1'),
            'Sub Menu 2' => array('Sub Entry 2' => 'New Sub Value 2')
        );
        $this->assertEquals($this->_menu->toArray(), $expected);

        $this->_menu->setEntry('Sub Menu 1', 'Replaced Sub Menu Entry');
        $expected = array(
            'Entry 1'    => 'Value 1',
            'Sub Menu 1' => 'Replaced Sub Menu Entry',
            'Sub Menu 2' => array('Sub Entry 2' => 'New Sub Value 2')
        );
        $this->assertEquals($this->_menu->toArray(), $expected);
    }

    public function testToJson()
    {
        $subMenuA = new OntoWiki_Menu();
        $subMenuA->setEntry('Sub Entry 1', 'Sub Value 1');

        $subMenuB = new OntoWiki_Menu();
        $subMenuB->setEntry('Sub Entry 2', 'Old Sub Value 2')
            ->setEntry('Sub Entry 2', 'New Sub Value 2');

        $this->_menu->setEntry('Entry 1', 'Value 1')
            ->setEntry('Sub Menu 1', $subMenuA)
            ->setEntry('Sub Menu 2', $subMenuB);

        $expected = '{"Entry 1":"Value 1","Sub Menu 1":{"Sub Entry 1":"Sub Value 1"},' .
            '"Sub Menu 2":{"Sub Entry 2":"New Sub Value 2"}}';
        $this->assertEquals($expected, $this->_menu->toJson(false));

        $this->_menu->setEntry('Sub Menu 1', 'Replaced Sub Menu Entry');
        $expected = '{"Entry 1":"Value 1","Sub Menu 1":"Replaced Sub Menu Entry","Sub Menu 2":' .
            '{"Sub Entry 2":"New Sub Value 2"}}';
        $this->assertEquals($expected, $this->_menu->toJson(false));
    }

    /**
     * @expectedException OntoWiki_Exception
     */
    public function testWrongKey()
    {
        $this->_menu->setEntry(null, 'Bar');
    }

    /**
     * @expectedException OntoWiki_Exception
     */
    public function testContentError()
    {
        $this->_menu->setEntry('Foo', 12345);
    }

    /**
     * @expectedException OntoWiki_Exception
     */
    public function testReplaceError()
    {
        $this->_menu->setEntry('Existing Key', 'Bar');
        $this->_menu->setEntry('Existing Key', 'Baz', false);
    }
}
