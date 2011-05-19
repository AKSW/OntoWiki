<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * Controller for OntoWiki
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_page
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id$
 */
class PageController extends OntoWiki_Controller_Component
{
    /**
     * Default action. Forwards to get action.
     */
    public function __call($action, $params)
    {
        OntoWiki_Navigation::disableNavigation();

        $pagename = str_replace  ( 'Action', '', $action);

        if (!empty($this->_privateConfig->titles->example)) {
            $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_($this->_privateConfig->titles->example));
        } else {
            $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_($pagename));
        }

        $this->render($pagename);
    }

}

