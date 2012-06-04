<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The main class for the datagathering plugin.
 *
 * @category   OntoWiki
 * @package    Extensions_Datagathering
 * @copyright  Copyright (c) 2012 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class DatagatheringPlugin extends OntoWiki_Plugin
{
    // ------------------------------------------------------------------------
    // --- Private properties -------------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Contains propertiy URIs configured in the ini file
     *
     * @var array
     */
    private $_properties = array();

    /**
     * The URI of the sync model as configured in the ini.
     *
     * @var string
     */
    private $_syncModelUri = null;

    /**
     * Contains the fetched sync configuration in order to avoid multiple
     * fetching of it via SPARQL queries.
     *
     * @var array
     */
    private $_syncConfigCache = null;

    /**
     * Contains the fetched sync configurations in order to avoid multiple
     * fetching of them via SPARQL queries.
     *
     * @var array
     */
    private $_syncConfigListCache = null;

    /**
     * The initialization method. Sets some properties.
     */
    public function init()
    {
        parent::init();
        //$this->_properties   = $this->_privateConfig->properties->toArray();
        $this->_syncModelUri = $this->_privateConfig->syncModelUri;

        // Translation hack in order to enable the plugin to translate...
        $translate = OntoWiki::getInstance()->translate;
        $translate->addTranslation(
            $this->_pluginRoot . 'languages',
            null,
            array('scan' => Zend_Translate::LOCALE_FILENAME)
        );
        $translate->setLocale(OntoWiki::getInstance()->config->languages->locale);
    }


    // ------------------------------------------------------------------------
    // --- Plugin handler methods ---------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Event handler method, which is called on menu creation. Adds some
     * datagathering relevant menu entries.
     *
     * @param Erfurt_Event $event
     *
     * @return bool
     */
    public function onCreateMenu($event)
    {
        $menu     = $event->menu;
        $resource = $event->resource;
        $model    = $event->model;
        $owApp    = OntoWiki::getInstance();

        // We only add entries to the menu, if all params are given and the
        // model is editable.
        if ((null === $resource) || (null === $model) || !$model->isEditable()
                || !$owApp->erfurt->getAc()->isModelAllowed('edit', $owApp->selectedModel)) {
            return;
        }

        $owApp     = OntoWiki::getInstance();
        $translate = $owApp->translate;

        $wrapperRegistry   = Erfurt_Wrapper_Registry::getInstance();
        $activeWrapperList = $wrapperRegistry->listActiveWrapper();

        if ((boolean)$this->_privateConfig->sync->enabled) {
            $syncConfigList = $this->_listSyncConfigs();
        } else {
            $syncConfigList = array();
        }

        $uri      = (string)$resource;
        $modelUri = (string)$model;

        $menuArray = array();

        // Check all active wrapper extensions, whether URI is handled. Also
        // check, whether a sync config exists.
        foreach ($activeWrapperList as $wrapperName) {
            $hash = $this->_getHash($uri, $wrapperName, $modelUri);

            $r = new Erfurt_Rdf_Resource($uri);
            $r->setLocator($this->_getProxyUri($uri));

            $wrapperInstance = $wrapperRegistry->getWrapperInstance($wrapperName);
            if ($wrapperInstance->isHandled($r, $modelUri)) {
                $menuArray[$wrapperName] = array(
                    'instance' => $wrapperInstance
                );

                if (isset($syncConfigList[$hash])) {
                    $menuArray[$wrapperName]['sync'] = true;
                }
            }
        }

        // Only add a separator, if at least one active wrapper exists.
        if (count($menuArray) > 0) {
            $menu->appendEntry(OntoWiki_Menu::SEPARATOR);
        }

        foreach ($menuArray as $wrapperName => $wrapperArray) {
            $wrapperInstance = $wrapperArray['instance'];

            if (isset($wrapperArray['sync']) && $wrapperArray['sync'] === true) {
                $message = $translate->_('Sync Data with %1$s');

                $menu->appendEntry(
                    sprintf($message, $wrapperInstance->getName()),
                    array(
                        'about' => $uri,
                        'class' => 'sync_data_button wrapper_' . $wrapperName
                    )
                );
            } else {
                $message = $translate->_('Import Data with %1$s');

                $menu->appendEntry(
                    sprintf($message, $wrapperInstance->getName()),
                    array(
                        'about' => $uri,
                        'class' => 'fetch_data_button wrapper_' . $wrapperName
                    )
                );
            }

            // Configure for sync entry.
            if ((boolean)$this->_privateConfig->sync->enabled) {
                $configUrl = $owApp->config->urlBase . 'datagathering/config?uri=' . urlencode($uri) .
                    '&wrapper=' . urlencode($wrapperName);

                if ($event->isModel) {
                    $configUrl .= '&m=' . urlencode($uri);
                }

                $message = $translate->_('Configure Sync with %1$s');

                $menu->appendEntry(
                    sprintf($message, $wrapperInstance->getName()),
                    $configUrl
                );
            }
        }

        return true;
    }

    /**
     * Event handler method, which is called whenever the property view of
     * a resource will be displayed. Adds the location bar to the menu and
     * adds a message if a resource is configured for sync.
     *
     * @param Erfurt_Event $event
     *
     * @return bool
     */
    public function onPropertiesAction($event)
    {
        $translate = OntoWiki::getInstance()->translate;
        $session   = new Zend_Session_Namespace(_OWSESSION);

        // Add the location bar menu entry.
        $menu = OntoWiki_Menu_Registry::getInstance()->getMenu('resource');
        $menu->prependEntry(OntoWiki_Menu::SEPARATOR);
        if ($session->showLocationBar === false) {
            $entry = $translate->_('Show/Hide Location Bar');
            $menu->prependEntry($entry, array('class' => 'location_bar show'));
        } else {
            $entry = $translate->_('Show/Hide Location Bar');
            $menu->prependEntry($entry, array('class' => 'location_bar'));
        }

        $uri      = $event->uri;
        $modelUri = $event->graph;

        if ((boolean)$this->_privateConfig->sync->enabled) {
            $syncConfig = $this->_getSyncConfig($uri, 'linkeddata', $modelUri);
        } else {
            $syncConfig = false;
        }
        if ($syncConfig === false || $syncConfig['checkHasChanged'] === false) {
            return false;
        }

        // Thre resource is configured for sync, so show a message box.
        $message = '<span id="dg_check_update" >
                        <span id="dg_configured_text">' .
                            $translate->_('This Resource is configured for Sync') . '.' .
                        '</span>' .
                        '<span id="dg_updated_text" style="display: none">' .
                            $translate->_('This Resource has changed since last sync') . '.' .
                        '</span>
                        <br />';

        $message .= '<span style="font-size:0.8em; font-weight: bold">';
        if (isset($syncConfig['lastSyncDateTime'])) {
            $message .= $translate->_('Last Sync') . ': ' . date('r', strtotime($syncConfig['lastSyncDateTime']));
            $message .= '<br />';
        }

        $message .= '<span id="dg_lastmod_text" style="display: none">' .
                        $translate->_('Last Modified') . ': ' .
                        '<span id="dg_lastmod_date">
                        </span>
                    </span>';
        $message .= '</span>';
        $message .= '<a id="dg_sync_button" class="minibutton"'.
            ' style="display: none; float: right; min-height: 20px; padding-top: 8px">';
        $message .= $translate->_('Sync') . '</a>';
        $message .= '</span>';

        OntoWiki::getInstance()->appendMessage(
            new OntoWiki_Message($message, OntoWiki_Message::INFO, array('escape' => false))
        );

        return true;
    }

    /**
     * Event handler method, which is called before tabs content is created.
     * Adds the location bar to the page.
     *
     * @param Erfurt_Event $event
     *
     * @return string
     */
    public function onPreTabsContentAction($event)
    {
        $translate = OntoWiki::getInstance()->translate;
        $uri = $event->uri;

        $html = '<div style="display: none; padding: 10px 20px 10px 30px" id="location_bar_container" class="cmDiv">
                    <input id="location_bar_input" class="text width75" type="text" value="' . $uri . '" name="l" />
                    <a id="location_open" class="minibutton" style="float: none">' .
                        $translate->_('View Resource') .
                    '</a>
                 </div>';

        return $html;
    }

    /**
     * Event handler method, which is called when a resource is deleted.
     * We remove all sync configurations with that resource.
     *
     * @param Erfurt_Event $event
     *
     * @return bool
     */
    public function onDeleteResources($event)
    {
        if ($this->_properties->sync->enabled) {
            $modelUri = $event->modelUri;
            $uriArray = $event->resourceArray;

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?s ?o');
            $query->addFrom($this->_syncModelUri);
            $query->setWherePart(
                'WHERE {
                ?s <' . EF_RDF_TYPE . '> <' . $this->_properties['syncConfigClass'] . '> .
                ?s <' . $this->_properties['targetModel'] . '> <' . $modelUri . '> .
                ?s <' . $this->_properties['syncResource'] . '> ?o .
                }'
            );

            $store = Erfurt_App::getInstance()->getStore();
            $result = $store->sparqlQuery($query, array('use_ac' => false));

            foreach ($result as $row) {
                if (in_array($row['o'], $uriArray)) {
                    $store->deleteMatchingStatements(
                        $this->_syncModelUri,
                        $row['s'],
                        null,
                        null,
                        array('use_ac' => false)
                    );
                }
            }
        }
        return true;
    }

    /**
     * Event handler method, which is called before a model is deleted.
     * We remove all sync configurations with that model.
     *
     * @param Erfurt_Event $event
     *
     * @return bool
     */
    public function onPreDeleteModel($event)
    {
        if ($this->_properties->sync->enabled) {
            $modelUri = $event->modelUri;

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?s');
            $query->addFrom($this->_syncModelUri);
            $query->setWherePart(
                'WHERE {
                    ?s <' . EF_RDF_TYPE . '> <' . $this->_properties['syncConfigClass'] . '> .
                    ?s <' . $this->_properties['targetModel'] . '> <' . $modelUri . '> .
                }'
            );

            $store = Erfurt_App::getInstance()->getStore();
            $result = $store->sparqlQuery($query, array('use_ac' => false));

            foreach ($result as $row) {
                $store->deleteMatchingStatements($this->_syncModelUri, $row['s'], null, null, array('use_ac' => false));
            }
        }
        return true;
    }


    // ------------------------------------------------------------------------
    // --- Private helpder methods --------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Returns a md5 hash of the given parameters.
     *
     * @param string $uri
     * @param string $wrapperName
     * @param string $modelUri
     *
     * @return string
     */
    private function _getHash($uri, $wrapperName, $modelUri)
    {
        $uri         = (string)$uri;
        $wrapperName = (string)$wrapperName;
        $modelUri    = (string)$modelUri;

        return md5(($uri . $wrapperName . $modelUri));
    }

    /**
     * Returns the sync config for the given parameters or false, if no such exists.
     *
     * @param string $uri The resource uri.
     * @param string $wrapperName The wrapper name.
     * @param string $modelUri The model uri.
     *
     * @return array|bool
     */
    private function _getSyncConfig($uri, $wrapperName, $modelUri)
    {
        if (null === $this->_syncConfigCache) {
            $store = Erfurt_App::getInstance()->getStore();

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?s ?p ?o');
            $query->addFrom($this->_syncModelUri);
            $where = 'WHERE {
                ?s ?p ?o .
                ?s <' . EF_RDF_TYPE . '> <' . $this->_properties['syncConfigClass'] . '> .
                ?s <' . $this->_properties['syncResource'] . '> <' . $uri . '> .
                ?s <' . $this->_properties['targetModel'] .'> <' . $modelUri . '> .
                ?s <' . $this->_properties['wrapperName'] . '> "' . $wrapperName . '" .
            }';
            $query->setWherePart($where);

            $result = $store->sparqlQuery($query, array('use_ac' => false));

            if (count($result) === 0) {
                return false;
            }

            $retVal = array();
            foreach ($result as $row) {
                if (!isset($retVal[$row['s']])) {
                    $retVal[$row['s']] = array(
                        'uri' => $row['s']
                    );
                }

                switch ($row['p']) {
                    case $this->_properties['targetModel']:
                        $retVal[$row['s']]['targetModel'] = $row['o'];
                        break;
                    case $this->_properties['syncResource']:
                        $retVal[$row['s']]['syncResource'] = $row['o'];
                        break;
                    case $this->_properties['wrapperName']:
                        $retVal[$row['s']]['wrapperName'] = $row['o'];
                        break;
                    case $this->_properties['lastSyncPayload']:
                        $retVal[$row['s']]['lastSyncPayload'] = unserialize($row['o']);
                        break;
                    case $this->_properties['lastSyncDateTime']:
                        $retVal[$row['s']]['lastSyncDateTime'] = $row['o'];
                        break;
                    case $this->_properties['syncQuery']:
                        $retVal[$row['s']]['syncQuery'] = $row['o'];
                        break;
                    case $this->_properties['checkHasChanged']:
                        $retVal[$row['s']]['checkHasChanged'] = (bool)$row['o'];
                        break;
                }
            }

            $this->_syncConfigCache = array_values($retVal);
            $this->_syncConfigCache = $this->_syncConfigCache[0]; // Only return one config!
        }

        return $this->_syncConfigCache;
    }

    /**
     * Returns all existing sync configurations.
     *
     * @return array|bool
     */
    private function _listSyncConfigs()
    {
        if (null === $this->_syncConfigListCache) {
            $store = Erfurt_App::getInstance()->getStore();

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?s ?p ?o');
            $query->addFrom($this->_syncModelUri);
            $where = 'WHERE {
                ?s ?p ?o .
                ?s <' . EF_RDF_TYPE . '> <' . $this->_properties['syncConfigClass'] . '> . }';
            $query->setWherePart($where);
            $result = $store->sparqlQuery($query, array('use_ac' => false));

            if (count($result) === 0) {
                return false;
            }

            $retVal = array();
            foreach ($result as $row) {
                if (!isset($retVal[$row['s']])) {
                    $retVal[$row['s']] = array(
                        'uri' => $row['s']
                    );
                }

                switch ($row['p']) {
                    case $this->_properties['targetModel']:
                        $retVal[$row['s']]['targetModel'] = $row['o'];
                        break;
                    case $this->_properties['syncResource']:
                        $retVal[$row['s']]['syncResource'] = $row['o'];
                        break;
                    case $this->_properties['wrapperName']:
                        $retVal[$row['s']]['wrapperName'] = $row['o'];
                        break;
                    case $this->_properties['lastSyncPayload']:
                        $retVal[$row['s']]['lastSyncPayload'] = unserialize($row['o']);
                        break;
                    case $this->_properties['lastSyncDateTime']:
                        $retVal[$row['s']]['lastSyncDateTime'] = $row['o'];
                        break;
                    case $this->_properties['syncQuery']:
                        $retVal[$row['s']]['syncQuery'] = $row['o'];
                        break;
                    case $this->_properties['checkHasChanged']:
                        $retVal[$row['s']]['checkHasChanged'] = (bool)$row['o'];
                        break;
                }
            }

            $cacheVal = array();
            foreach ($retVal as $s=>$valueArray) {
                $hash = $this->_getHash(
                    $valueArray['syncResource'],
                    $valueArray['wrapperName'],
                    $valueArray['targetModel']
                );
                $cacheVal[$hash] = $valueArray;
            }

            $this->_syncConfigListCache = $cacheVal;
        }

        return $this->_syncConfigListCache;
    }

    private function _getProxyUri($uri)
    {
        // If at least one rewrite rule is defined, we iterate through them.
        if (isset($this->_privateConfig->rewrite)) {
            $rulesArray = $this->_privateConfig->rewrite->toArray();
            foreach ($rulesArray as $ruleId => $ruleSpec) {
                $proxyUri = @preg_replace($ruleSpec['pattern'], $ruleSpec['replacement'], $uri);
                if ($proxyUri !== $uri) {
                    return $proxyUri;
                }
            }
        }

        return null;
    }
}
