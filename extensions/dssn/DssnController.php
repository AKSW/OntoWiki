<?php
/**
 * distributed semantic social network client
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DssnController extends OntoWiki_Controller_Component {

    public function init() {
        parent::init();
        OntoWiki_Navigation::reset();

        OntoWiki_Navigation::register('news', array('route'      => null,
            'controller' => 'dssn',
            'action'     => 'news',
            'name'   => 'News'));
        OntoWiki_Navigation::register('friends', array('route'      => null,
            'controller' => 'dssn',
            'action'     => 'friends',
            'name'   => 'Friends'));

        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'js/dssn.js');
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'css/dssn.css');
    }

    function newsAction() {
        $ow = OntoWiki::getInstance();

        $this->view->placeholder('main.window.title')->set($ow->translate->_('News'));
        $this->addModuleContext('main.window.dssn.news');
    }

    function friendsAction() {
        $ow = OntoWiki::getInstance();

        $this->view->placeholder('main.window.title')->set($ow->translate->_('Friends'));
        $this->addModuleContext('main.window.dssn.news');
    }
}

