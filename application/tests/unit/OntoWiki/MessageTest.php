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
class OntoWiki_MessageTest extends PHPUnit_Framework_TestCase
{
    public function testMessageGetTypeDefaultInfo()
    {
        $msg = new OntoWiki_Message('ttt');
        $this->assertEquals($msg->getType(), OntoWiki_Message::INFO);
    }

    public function testMessageGetTypeSuccess()
    {
        $msg = new OntoWiki_Message('ttt', OntoWiki_Message::SUCCESS);
        $this->assertEquals($msg->getType(), OntoWiki_Message::SUCCESS);
    }

    public function testMessageGetTypeInfo()
    {
        $msg = new OntoWiki_Message('ttt', OntoWiki_Message::INFO);
        $this->assertEquals($msg->getType(), OntoWiki_Message::INFO);
    }

    public function testMessageGetTypeWarning()
    {
        $msg = new OntoWiki_Message('ttt', OntoWiki_Message::WARNING);
        $this->assertEquals($msg->getType(), OntoWiki_Message::WARNING);
    }

    public function testMessageGetTypeError()
    {
        $msg = new OntoWiki_Message('ttt', OntoWiki_Message::ERROR);
        $this->assertEquals($msg->getType(), OntoWiki_Message::ERROR);
    }

    public function testMessageGetText()
    {
        $msg = new OntoWiki_Message('The test string for the message object.', OntoWiki_Message::SUCCESS);
        $this->assertEquals($msg->getText(), 'The test string for the message object.');
    }
}
