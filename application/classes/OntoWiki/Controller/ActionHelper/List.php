<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Manage the session variable that stores multiple lists (mostly the current instance list)
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes_Controller_ActionHelper
 */
class OntoWiki_Controller_ActionHelper_List extends Zend_Controller_Action_Helper_Abstract
{
    protected $_owApp;

    public function __construct()
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

        throw new InvalidArgumentException('list was not found. check with listExists() first');
    }

    /**
     * Render a list, add it to the cache and add it to the current view
     *
     * @param $listName string with a listname
     * @param $list OntoWiki_Model_Instances a list of resources
     * @param $view the current view to which the list should be added
     * @param $mainTemplate the template to use for rendering the list
     * @param $other array of other values available to the template
     * @param $returnOutput true|false if false, the list is rendered directly to the view else the
     *        rendered list is returned
     * @return the rendered list if $returnOutput is true
     */
    public function addListPermanently(
        $listName,
        OntoWiki_Model_Instances $list,
        Zend_View_Interface $view,
        $mainTemplate = 'list_std_main',
        $other = null,
        $returnOutput = false
    ) {
        $this->updateList($listName, $list, true);
        return $this->addList($listName, $list, $view, $mainTemplate, $other, $returnOutput);
    }

    /**
     * Render a list and add it to the current view
     *
     * @param $listName string with a listname
     * @param $list OntoWiki_Model_Instances a list of resources
     * @param $view the current view to which the list should be added
     * @param $mainTemplate the template to use for rendering the list
     * @param $other array of other values available to the template
     * @param $returnOutput true|false if false, the list is rendered directly to the view else the
     *        rendered list is returned
     * @return the rendered list if $returnOutput is true
     */
    public function addList(
        $listName,
        OntoWiki_Model_Instances $list,
        Zend_View_Interface $view,
        $mainTemplate = 'list_std_main',
        $other = null,
        $returnOutput = false
    ) {
        if ($other === null) {
            $other = new stdClass();
        }

        $renderedList = $view->partial(
            'partials/list.phtml',
            array(
                 'listName'     => $listName,
                 'instances'    => $list,
                 'mainTemplate' => $mainTemplate,
                 'other'        => $other
            )
        );

        $this->_owApp->session->lastList = $listName;

        if ($returnOutput) {
            return $renderedList;
        } else {
            $this->getResponse()->append('default', $renderedList);
        }
    }

    public function updateList($name, OntoWiki_Model_Instances $list, $setLast = false)
    {
        $lists                               = $this->_owApp->session->managedLists;
        $lists[$name]                        = $list;
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

        throw new InvalidArgumentException('list was not found. check with listExists() first');
    }
}
