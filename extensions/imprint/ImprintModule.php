<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – Navigation
 *
 * this is the main navigation module
 *
 * @category   OntoWiki
 * @package    Extensions_Imprint
 * @author     Sebastian Dietzold <sebastian@dietzold.de>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ImprintModule extends OntoWiki_Module
{
    protected $session = null;

    public function init() {
        $this->session = $this->_owApp->session;
    }

    /**
     * Returns the content
     */
    public function getContents() {
        $this->view->notice = $this->_privateConfig->notice;
        $this->view->mail = $this->_privateConfig->mail;
        $this->view->name = $this->_privateConfig->name;
        $this->view->street = $this->_privateConfig->street;
        $this->view->zip = $this->_privateConfig->zip;
        $this->view->city = $this->_privateConfig->city;
        $content = $this->render('imprint'); //
        return $content;
    }

    public function getTitle(){
        return 'Imprint';
    }

    public function shouldShow(){
       return true;
    }

}


