<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once realpath(dirname(dirname(dirname(dirname(__FILE__))))) . DIRECTORY_SEPARATOR . 'Bootstrap.php';

/**
 * OntoWiki bootstrap class.
 *
 * Provides on-demand loading of application resources.
 *
 * @category  OntoWiki
 * @package   OntoWiki_Bootstrap
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class OntoWiki_Test_UnitTestBootstrap extends Bootstrap
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

        $erfurt->setAc(new Erfurt_Ac_None());

        // make available
        $ontoWiki->erfurt = $erfurt;

        return $erfurt;
    }

    public function _initPlugins()
    {
        // require front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');

        // We do not register any plugins for unit tests.
    }
}
