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

    public function testDummyTestUnlessNoWorkingActualTestExists()
    {
        $this->assertTrue(true);
    }

    /*
        public function testListInstantiation()
        {
            $this->request->setMethod('POST')
                ->setPost(
                array(
                    'list' => 'instances',
                    'init' => true,
                )
            );

            $this->dispatch('/list');

            $this->assertController('resource');
            $this->assertAction('instances');
            $this->assertResponseCode(200);
        }


        public function testListConfig()
        {
            $c = array(
                'filter' => array(
                    'action' => 'add',
                    'mode' => 'box',
                    'filter' => 'equals',
                    'value' => 'http://test.com/'
                )
            );
            $this->request->setMethod('POST')
                ->setPost(
                array(
                    'list' => 'instances',
                    'init' => true,
                    'instancesconfig' => json_encode($c)
                )
            );

            $this->dispatch('/list');

            $this->assertController('resource');
            $this->assertAction('instances');
            $this->assertResponseCode(200);
        }

        public function testListError()
        {
            $c = array();
            $this->request->setMethod('POST')
                ->setPost(
                array(
                    'list' => 'newone',
                    //no init parameter
                    'instancesconfig' => json_encode($c)
                )
            );

            $this->dispatch('/list');

            $this->assertController('error');
        }
    */
}
