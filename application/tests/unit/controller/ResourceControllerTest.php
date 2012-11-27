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
 * @package    controllers
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @version    $Id: $
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
