<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Manage the session variable that stores multiple lists (mostly the current instance list)
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes_Controller_ActionHelper
 */
class OntoWiki_Controller_ActionHelper_List extends Zend_Controller_Action_Helper_Abstract{
    protected $_owApp;

    public function  __construct()
    {
        $this->_owApp = OntoWiki::getInstance();
        if (!isset($this->_owApp->session->managedLists)) {
            $this->_owApp->session->managedLists = array();
        }
    }

    /**
     *
     * @return OntoWiki_Model_Instances 
     */
    public function getLastList()
    {
        $name = $this->_owApp->session->lastList;
        if (isset($this->_owApp->session->managedLists)) {
            $lists = $this->_owApp->session->managedLists;
            if (key_exists($name, $lists)) {
                return $lists[$name];
            }
        }
        return null;
    }

     /**
     *
     * @return string 
     */
    public function getLastListName()
    {
        return $this->_owApp->session->lastList;
    }

    /**
     *
     * @return bool 
     */
    public function listExists($name)
    {
        $lists = $this->_owApp->session->managedLists;
        if (key_exists($name, $lists)) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return OntoWiki_Model_Instances 
     */
    public function getList($name)
    {
        $lists = $this->_owApp->session->managedLists;

        if (key_exists($name, $lists)) {
            return $lists[$name];
        }

        throw new InvalidArgumentException("list was not found. check with listExists() first");
    }

    public function addListPermanently(
            $name,
            OntoWiki_Model_Instances $list,
            Zend_View_Interface $view,
            $mainTemplate = 'list_std_main',
            $other = null
    )
    {
        $this->updateList($name, $list, true);
        $this->addList($name, $list, $view, $mainTemplate, $other);
    }

    public function addList(
            $listName,
            OntoWiki_Model_Instances $list,
            Zend_View_Interface $view,
            $mainTemplate = 'list_std_main',
            $other = null
    )
    {
        if ($other === null ) {
            $other = new stdClass();
        }
        $this->getResponse()->append(
            'default',
            $view->partial(
                'partials/list.phtml',
                array(
                    'listName'              => $listName,
                    'instances'             => $list,
                    'mainTemplate'          => $mainTemplate,
                    'other'                 => $other
                )
            )
        );
        $this->_owApp->session->lastList = $listName;
        //$this->getActionController()->addModuleContext('main.window.list');
    }

    public function updateList($name, OntoWiki_Model_Instances $list, $setLast = false)
    {
        $lists = $this->_owApp->session->managedLists;
        $lists[$name] = $list;
        $this->_owApp->session->managedLists = $lists;
        if ($setLast) {
            $this->_owApp->session->lastList = $name;
        }
    }

    public function getAllLists()
    {
        return $this->_owApp->session->managedLists;
    }

    public function removeAllLists()
    {
        $this->_owApp->session->managedLists = array();
    }

    public function removeList($name)
    {
        $lists = $this->_owApp->session->managedLists;

        if (key_exists($name, $lists)) {
            unset ($lists[$name]);
        }
       
        throw new InvalidArgumentException("list was not found. check with listExists() first");
    }
}
