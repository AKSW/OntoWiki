<?php
require_once realpath(dirname(dirname(dirname(dirname(__FILE__))))). DIRECTORY_SEPARATOR . 'Bootstrap.php';

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki bootstrap class.
 *
 * Provides on-demand loading of application resources.
 *
 * @category OntoWiki
 * @package OntoWiki_Bootstrap
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class OntoWiki_Test_IntegrationTestBootstrap extends Bootstrap
{
    /**
     * Overwrite the config bootstrap method, such that the store backend can be set via an environment variable.
     *
     * @return void|Zend_Config_Ini
     */
    public function _initConfig()
    {
        $config = parent::_initConfig();

        // Overwrite database settings from test config
        // load user application configuration files
        $tryDistConfig = false;
        try {
            $privateConfig = new Zend_Config_Ini(ONTOWIKI_ROOT . 'application/tests/config.ini', 'private', true);
            $config->merge($privateConfig);
        } catch (Zend_Config_Exception $e) {
            $tryDistConfig = true;
        }

        if ($tryDistConfig === true) {
            try {
                $privateConfig = new Zend_Config_Ini(ONTOWIKI_ROOT . 'application/tests/config.ini.dist', 'private', true);
                $config->merge($privateConfig);
            } catch (Zend_Config_Exception $e) {
                $message = 'Failed to find test config';
                throw new OntoWiki_Exception($message);
            }
        }

        // overwrite store adapter to use with environment variable if set
        // this is useful, when we want to test with different stores without manually
        // editing the config
        if ($config instanceof Zend_Config) {
            $storeAdapter = getenv('EF_STORE_ADAPTER');
            if (($storeAdapter === 'virtuoso') || ($storeAdapter === 'zenddb')) {
                $config->store->backend = $storeAdapter;
            } else if ($storeAdapter !== false) {
                throw new Exception('Invalid value of $EF_STORE_ADAPTER: ' . $storeAdapter);
            }
        }

        return $config;
    }
}
