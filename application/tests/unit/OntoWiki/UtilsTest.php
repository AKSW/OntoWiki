<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class comtains tests for the OntoWiki Utils.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2016, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Natanael Arndt <arndtn@googlemail.com>
 */
class OntoWiki_UtilsTest extends PHPUnit_Framework_TestCase
{
    public function testMatchMimetypeFromRequest()
    {
        $request = new Zend_Controller_Request_HttpTestCase();
        $request->setHeader("Accept", "text/*");
        $support = array(
            "text/ttl",
            "application/rdf+xml"
        );
        $mime = OntoWiki_Utils::matchMimetypeFromRequest($request, $support);
        $this->assertEquals($mime, "text/ttl");
    }
}
