<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2017, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This test class comtains tests for the OntoWiki service controller.
 *
 * @category   OntoWiki
 * @package    controlers
 * @subpackage IntegrationTests
 * @copyright  Copyright (c) 2017, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPLv2)
 * @author     Fabian Niehoff <niehoff.fabian@web.de>
 */
class ModelControllerTest extends OntoWiki_Test_ControllerTestCase
{
    public function setUp()
    {
        $this->setUpIntegrationTest();
        //this is necessary to allow the dispatch to create a model (ac)
        $this->frontEndLogin();
    }

    /**
     * @dataProvider uriProvider
     */
    public function testCreateActionFiltersOnlyIncorrectUris($uri, $correctUri)
    {
        $this->dispatch('/service/auth');

        $this->request->setPost(
            array(
                'title' => 'test',
                'modeluri' => $uri,
                'importOptions' => 'import-empty'
            )
        );
        $this->dispatch('/model/create');
        $this->assertController('model');
        $this->assertAction('create');
        //when the URI is correct expect the model in the store
        $store = OntoWiki::getInstance()->erfurt->getStore();
        if ($correctUri) {
            $this->assertTrue($store->isModelAvailable($uri, true));
        } else {
            $this->assertFalse($store->isModelAvailable($uri, true));
        }
    }

    public function uriProvider()
    {
        return [
            ['ftp://ftp.is.co.za.example.org/ontowiki/ontowiki.txt', true],
            ['gopher://spinaltap.micro.umn.example.edu/00/Weather/California/Los%20Angeles', true],
            ['http://www.ontowiki.aksw.no.example.net/faq/ontowiki-faq/part1.html', true],
            ['mailto:aksw@ifi.unizh.example.gov', true],
            ['news:comp.aksw.www.servers.unix', true],
            ['telnet://melvyl.ucop.example.edu/', true],
            ['http://www.ietf.org/rfc/rfc2396.txt', true],
            ['ldap://[2001:db8::7]/c=GB?objectClass?one', true],
            ['mailto:AKSW.L@example.com', true],
            ['telnet://192.0.2.16:80/', true],
            ['urn:oasis:names:specification:docbook:dtd:xml:4.1.2', true],
            ['https://www.aksw.org/faq', true],
            ['ptth://www.aksw.org', true],
            ['\\äüö', false],
            ['plainText', false],
            ['noProtocol.de', false],
            ['http://www.äß', false],
            ['http://www.⺅⺔.com', false]
        ];
    }
}
