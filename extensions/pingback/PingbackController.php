<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * semantic pingback controller
 *
 * @category   OntoWiki
 * @package    Extensions_Pingback
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @author     Sebastian Tramp <mail@sebastian.tramp.name>
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @author     Natanael Arndt <arndtn@gmail.com>
 */
class PingbackController extends OntoWiki_Controller_Component
{

    protected $_targetGraph = null;
    protected $_sourceRdf = null;
    private $_dbChecked = false;

    /**
     * receive a ping
     */
    public function pingAction()
    {
        $owApp = OntoWiki::getInstance();
        $logger = $owApp->logger;
        $logger->debug('Pingback Server Init.');

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $this->_owApp->appendMessage(
            new OntoWiki_Message('Ping received.', OntoWiki_Message::INFO)
        );

        if (isset($_POST['source']) && isset($_POST['target'])) {
            // Simplified Semantic Pingback

            // read config and put it into options
            $options = array();
            $config = $this->_privateConfig;
            if (isset($config->rdfa->enabled)) {
                $options['rdfa'] = $config->rdfa->enabled;
            }
            if (isset($config->title_properties)) {
                $options['title_properties'] = $config->title_properties->toArray();
            }
            if (isset($config->generic_relation)) {
                $options['generic_relation'] = $config->generic_relation;
            }

            $ping = new Erfurt_Ping($options);
            echo $ping->receive($_POST['source'], $_POST['target']);

            return;
        } else {
            // Create XML RPC Server
            $server = new Zend_XmlRpc_Server();
            $server->setClass($this, 'pingback');

            // Let the server handle the RPC calls.
            $response = $this->getResponse();
            $response->setBody($server->handle());
            return;
        }
    }
}
