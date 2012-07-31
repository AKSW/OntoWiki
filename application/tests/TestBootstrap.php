<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Bootstrap.php'; 

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
class TestBootstrap extends Bootstrap
{
    public function _initExtensionManager()
    {
        // We do not want extensions loaded while unit testing!
        return null;
    }
    
    public function _initErfurt()
    {
        $erfurt = null;

        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');

        // require OntoWiki
        $this->bootstrap('OntoWiki');
        $ontoWiki = $this->getResource('OntoWiki');

        // require Logger, since Erfurt logger should write into OW logs dir
        $this->bootstrap('Logger');

        // Reset the Erfurt app for testability... needs to be refactored.
        Erfurt_App::reset();

        try {
            $erfurt = Erfurt_App::getInstance(false)->start($config);
        } catch (Erfurt_Exception $ee) {
            throw new OntoWiki_Exception('Error loading Erfurt framework: ' . $ee->getMessage());
        } catch (Exception $e) {
            throw new OntoWiki_Exception('Unexpected error: ' . $e->getMessage());
        }

        $store = new Erfurt_Store(
            array(
                'adapterInstance' => new Erfurt_Store_Adapter_Test()
            ), 
            'Test'
        );
        $erfurt->setStore($store);

        // make available
        $ontoWiki->erfurt = $erfurt;

        return $erfurt;
    }

    /**
     * Sets up the view environment
     *
     * @since 0.9.5
     */
    public function _initView()
    {
        $viewOptions = array(
            'use_module_cache' => false,
            'cache_path'        => '/',
            'lang'              => 'en'
        );

        // init view
        $view = new OntoWiki_View($viewOptions, null);

        // set Zend_View to emit notices in debug mode
        $view->strictVars(true);

        // initialize layout
        Zend_Layout::startMvc(
            array(
                // for layouts we use the default path
                'layoutPath' => '/'
            )
        );

        return $view;
    }
}
