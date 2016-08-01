<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class comtains tests for the OntoWiki service controller.
 *
 * @category   OntoWiki
 * @package    controlers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 */
class ResourceControllerTest extends OntoWiki_Test_ControllerTestCase
{
    public function setUp()
    {
        $this->setUpUnitTest();
    }

    public function testExportActionReturnsCorrectContentTypeForTurtle()
    {
        $r = 'http://example.org/resource1';
        $m = 'http://example.org/model1/';
        $this->_storeAdapter->createModel($m);

        $this->request->setParam('r', $r);
        $this->request->setParam('m', $m);
        $this->request->setParam('f', 'turtle');
        $this->dispatch('/resource/export');

        $this->assertController('resource');
        $this->assertAction('export');
        $this->assertResponseCode(200);
        $this->assertHeaderContains('Content-Type', 'text/turtle');
    }
}
