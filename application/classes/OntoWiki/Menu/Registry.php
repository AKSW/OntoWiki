<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki menu registry class.
 *
 * Serves as a central registry for menus and provides methods for setting
 * and retrieving menu instances.
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes_Menu
 * @author   Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Menu_Registry
{
    /**
     * Menu registry; an array of menu instances
     *
     * @var array
     */
    private $_menus = array();

    /**
     * Singleton instance
     *
     * @var OntoWiki_Menu_Registry
     */
    private static $_instance = null;

    /**
     * Singleton instance
     *
     * @return OntoWiki_Menu_Registry
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Returns the menu denoted by $menuKey.
     *
     * @param string $menuKey
     *
     * @return OntoWiki_Menu
     */
    public function getMenu($menuKey, $context = null)
    {
        if (!is_string($menuKey)) {
            throw new OntoWiki_Exception('Menu key must be string.');
        }

        if (!isset($this->_menus[$context])) {
            $this->_menus[$context] = array();
        }

        if (!array_key_exists($menuKey, $this->_menus[$context])) {
            $getMethod = '_get' . ucfirst($menuKey) . 'Menu';
            if (method_exists($this, $getMethod)) {
                $this->setMenu($menuKey, $context, $this->$getMethod($context));
            } else {
                $this->setMenu($menuKey, $context, new OntoWiki_Menu());
            }
        }

        return $this->_menus[$context][$menuKey];
    }

    /**
     * Stores the menu $menu with key $menuKey in the registry.
     *
     * @param string        $menuKey
     * @param OntoWiki_Menu $menu
     * @param boolean       $replace
     *
     * @return OntoWiki_Menu_Registry
     */
    public function setMenu($menuKey, $context, OntoWiki_Menu $menu, $replace = true)
    {
        if (!is_string($menuKey)) {
            throw new OntoWiki_Exception('Menu key must be string.');
        }

        if (!isset($this->_menus[$context])) {
            $this->_menus[$context] = array();
        }

        if (!$replace && array_key_exists($menuKey, $this->_menus[$context])) {
            throw new OntoWiki_Exception("Menu with key '$menuKey' already registered.");
        }

        $this->_menus[$context][$menuKey] = $menu;

        return $this;
    }

    private function __construct()
    {
        $owApp = OntoWiki::getInstance();
        $this->setMenu('application', null, $this->_getApplicationMenu());

        // check if a resource is selected
        if (isset($owApp->selectedResource) && $owApp->selectedResource) {
            $resource = (string)$owApp->selectedResource;
            $this->setMenu('resource', $resource, $this->_getResourceMenu($resource));
        }
    }

    /**
     * Create the application menu and fill it with its default entries
     */
    private function _getApplicationMenu($context = null)
    {
        $owApp = OntoWiki::getInstance();

        // user sub menu
        if ($owApp->erfurt->isActionAllowed('RegisterNewUser')
            && !(isset($owApp->config->ac)
            && ((boolean)$owApp->config->ac->deactivateRegistration === true))
        ) {

            if (!($owApp->erfurt->getAc() instanceof Erfurt_Ac_None)) {
                $userMenu = new OntoWiki_Menu();
                $userMenu->setEntry('Register New User', $owApp->config->urlBase . 'application/register');
            }
        }
        if ($owApp->user && !$owApp->user->isAnonymousUser()) {
            if (!isset($userMenu)) {
                $userMenu = new OntoWiki_Menu();
            }

            if (!$owApp->user->isDbUser()) {
                $userMenu->setEntry('Preferences', $owApp->config->urlBase . 'application/preferences');
            }

            $userMenu->setEntry('Logout', $owApp->config->urlBase . 'application/logout');
        }

        // view sub menu
        $viewMenu = new OntoWiki_Menu();

        // extras sub menu
        $extrasMenu = new OntoWiki_Menu();

        $extrasMenu->setEntry('News', $owApp->config->urlBase . 'index/news');

        // help sub menue
        $helpMenu = new OntoWiki_Menu();

        if (isset($owApp->config->help->documentation) && (trim($owApp->config->help->documentation) !== '')) {
            $helpMenu->setEntry('Documentation', trim($owApp->config->help->documentation));
        }
        if (isset($owApp->config->help->issues) && (trim($owApp->config->help->issues) !== '')) {
            $helpMenu->setEntry('Bug Report', trim($owApp->config->help->issues));
        }
        if (isset($owApp->config->help->versioninfo) && (trim($owApp->config->help->versioninfo) !== '')) {
            $helpMenu->setEntry('Version Info', trim($owApp->config->help->versioninfo));
        }
        $helpMenu->setEntry('About', $owApp->config->urlBase . 'application/about');

        // build menu out of sub menus
        $applicationMenu = new OntoWiki_Menu();
        if (isset($userMenu)) {
            $applicationMenu->setEntry('User', $userMenu);
        }
        $applicationMenu-> /*setEntry('View', $viewMenu)
                        ->*/
            setEntry('Extras', $extrasMenu)
            ->setEntry('Help', $helpMenu);

        // add cache entry only if use is allowed to use debug action
        if ($owApp->erfurt->isActionAllowed('Debug')) {
            $debugMenu = new OntoWiki_Menu();
            $debugMenu->setEntry('Clear Module Cache', $owApp->config->urlBase . 'debug/clearmodulecache')
                ->setEntry('Clear Translation Cache', $owApp->config->urlBase . 'debug/cleartranslationcache')
                ->setEntry('Clear Object & Query Cache', $owApp->config->urlBase . 'debug/clearquerycache')
                ->setEntry('Start xdebug Session', $owApp->config->urlBase . '?XDEBUG_SESSION_START=xdebug')
                ->setEntry('Reset Session', $owApp->config->urlBase . 'debug/destroysession');

            // for testing sub menus
            // $test1 = new OntoWiki_Menu();
            // $test1->appendEntry('Test 1', '#');
            // $test2 = new OntoWiki_Menu();
            // $test2->appendEntry('Test 2', $test1);
            // $debugMenu->setEntry('Test', $test2);

            $applicationMenu->setEntry('Debug', $debugMenu);
        }

        return $applicationMenu;
    }

    /**
     * Create the context menu for models/knowledge bases and fill it with its default entries
     */
    private function _getModelMenu($model = null)
    {
        $owApp = OntoWiki::getInstance();
        if ($model === null) {
            $model = $owApp->selectedModel;
        }
        $config = $owApp->config;

        $modelMenu = new OntoWiki_Menu();

        // Select Knowledge Base
        $url = new OntoWiki_Url(
            array('controller' => 'model', 'action' => 'select'),
            array()
        );
        $url->setParam('m', $model, false);
        $modelMenu->appendEntry(
            'Select Knowledge Base',
            (string)$url
        );

        // View resource
        $url = new OntoWiki_Url(
            array('action' => 'view'),
            array()
        );
        $url->setParam('m', $model, false);
        $url->setParam('r', $model, true);

        $modelMenu->appendEntry(
            'View as Resource',
            (string)$url
        );

        // check if model could be edited (prefixes and data)
        if ($owApp->erfurt->getAc()->isModelAllowed('edit', $model)) {
            // Configure Knowledge Base
            $url = new OntoWiki_Url(
                array('controller' => 'model', 'action' => 'config'),
                array()
            );
            $url->setParam('m', $model, false);
            $modelMenu->appendEntry(
                'Configure Knowledge Base',
                (string)$url
            );

            // Add Data to Knowledge Base
            $url = new OntoWiki_Url(
                array('controller' => 'model', 'action' => 'add'),
                array()
            );
            $url->setParam('m', $model, false);
            $modelMenu->appendEntry(
                'Add Data to Knowledge Base',
                (string)$url
            );
        }

        // Model export
        if ($owApp->erfurt->getAc()->isActionAllowed(Erfurt_Ac_Default::ACTION_MODEL_EXPORT)) {
            // add entries for supported export formats
            foreach (Erfurt_Syntax_RdfSerializer::getSupportedFormats() as $key => $format) {

                $url = new OntoWiki_Url(
                    array('controller' => 'model', 'action' => 'export'),
                    array()
                );
                $url->setParam('m', $model, false);
                $url->setParam('f', $key);

                $modelMenu->appendEntry(
                    'Export Knowledge Base as ' . $format,
                    (string)$url
                );
            }
        }

        // can user delete models?
        if ($owApp->erfurt->getAc()->isModelAllowed('edit', $model)
            && $owApp->erfurt->getAc()->isActionAllowed('ModelManagement')
        ) {

            $url = new OntoWiki_Url(
                array('controller' => 'model', 'action' => 'delete'),
                array()
            );
            $url->setParam('model', $model, false);

            $modelMenu->appendEntry(
                'Delete Knowledge Base',
                (string)$url
            );
        }

        // add a seperator
        $modelMenu->appendEntry(OntoWiki_Menu::SEPARATOR);

        return $modelMenu;
    }

    /**
     * Create the (context) menu for resource and fill it with its default entries
     */
    private function _getResourceMenu($resource = null)
    {
        $owApp = OntoWiki::getInstance();
        if ($resource === null) {
            $resource = $owApp->selectedResource;
        }
        $config = $owApp->config;

        $resourceMenu = new OntoWiki_Menu();

        // Add the class Menu if the current resource is a class
        $classMenu = $this->_getClassMenu($resource)->toArray();
        foreach ($classMenu as $key => $value) {
            $resourceMenu->appendEntry($key, $value);
        }
        if (count($classMenu) > 0) {
            $resourceMenu->appendEntry(OntoWiki_Menu::SEPARATOR);
        }

        // View resource
        $url = new OntoWiki_Url(
            array('action' => 'view'),
            array()
        );
        $url->setParam('r', $resource, true);

        $resourceMenu->appendEntry(
            'View Resource',
            (string)$url
        );

        // Edit entries
        if ($owApp->erfurt->getAc()->isModelAllowed('edit', $owApp->selectedModel)) {
            // edit resource option
            $resourceMenu->appendEntry(
                'Edit Resource',
                'javascript:editResourceFromURI(\'' . (string)$resource . '\')'
            );

            // Delete resource option
            $url = new OntoWiki_Url(
                array('controller' => 'resource', 'action' => 'delete'),
                array('r')
            );
            $url->setParam('r', (string)$resource, false);
            $resourceMenu->appendEntry('Delete Resource', (string)$url);
        }

        $resourceMenu->appendEntry(
            'Go to Resource (external)',
            (string)$resource
        );

        $resourceMenu->appendEntry(OntoWiki_Menu::SEPARATOR);

        foreach (Erfurt_Syntax_RdfSerializer::getSupportedFormats() as $key => $format) {
            $resourceMenu->appendEntry(
                'Export Resource as ' . $format,
                $config->urlBase . 'resource/export/f/' . $key . '?r=' . urlencode($resource)
            );
        }

        return $resourceMenu;
    }

    /**
     * Create the (context) menu for classes and fill it with its default entries
     */
    private function _getClassMenu($resource = null)
    {
        $owApp = OntoWiki::getInstance();
        $classMenu = new OntoWiki_Menu();

        $query     = Erfurt_Sparql_SimpleQuery::initWithString(
            'SELECT *
             FROM <' . (string)$owApp->selectedModel . '>
             WHERE {
                <' . $resource . '> a ?type  .
             }'
        );
        $results[] = $owApp->erfurt->getStore()->sparqlQuery($query);

        $query = Erfurt_Sparql_SimpleQuery::initWithString(
            'SELECT *
             FROM <' . (string)$owApp->selectedModel . '>
             WHERE {
                ?inst a <' . $resource . '> .
             } LIMIT 2'
        );

        if (count($owApp->erfurt->getStore()->sparqlQuery($query)) > 0) {
            $hasInstances = true;
        } else {
            $hasInstances = false;
        }

        $typeArray = array();
        foreach ($results[0] as $row) {
            $typeArray[] = $row['type'];
        }

        if (in_array(EF_RDFS_CLASS, $typeArray)
            || in_array(EF_OWL_CLASS, $typeArray)
            || $hasInstances
        ) {
            $url = new OntoWiki_Url(
                array('action' => 'list'),
                array()
            );
            $url->setParam('class', $resource, false);
            $url->setParam('init', "true", true);

            $classMenu->appendEntry(
                'List Instances',
                (string)$url
            );

            // add class menu entries
            if ($owApp->erfurt->getAc()->isModelAllowed('edit', $owApp->selectedModel)) {
                $classMenu->appendEntry(
                    'Create Instance',
                    "javascript:createInstanceFromClassURI('$resource');"
                );
            }
        }

        return $classMenu;
    }
}
