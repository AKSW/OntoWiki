<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';

/**
 * Plugin to select default model if only one available or always.
 *
 * @category   OntoWiki
 * @package    Extensions_Defaultmodel
 */
class DefaultmodelPlugin extends OntoWiki_Plugin
{
    public function onAfterInitController($event)
    {
        //this extension should only run if no explicit model is given via request parameter "m"
        $request = new Zend_Controller_Request_Http();
        if ($request->get("m")) {
            return;
        }

        $config = $this->_privateConfig->toArray();
        $efApp  = Erfurt_App::getInstance();

        // disable model box if config value is true and modelmanangement isn't allowed
        if ($config['modelsHide'] && !$efApp->getAc()->isActionAllowed($config['modelsExclusiveRight'])) {
            $registry = OntoWiki_Module_Registry::getInstance();
            $registry->disableModule('modellist', 'main.sidewindow');
        }

        //only do this once (so if the model is changed later, this plugin will not prevent it)
        if ($config['setOnce'] && isset($_SESSION['defaultModelHasBeenSet']) && $_SESSION['defaultModelHasBeenSet']) {
            return;
        }

        $_SESSION['defaultModelHasBeenSet'] = true;

        require_once 'OntoWiki/Module/Registry.php';

        $owApp           = OntoWiki::getInstance();
        $efStore         = $efApp->getStore();
        $availableModels = $efStore->getAvailableModels();

        if (array_key_exists('modelUri', $config)
            && array_key_exists($config['modelUri'], $availableModels)
        ) {
            $modelUri = $config['modelUri'];
        } elseif (count($availableModels) === 1) {
            $modelUri = current(array_keys($availableModels));
        } else {
            $modelUri = false;
        }

        // set default model if it could be determined
        if ($modelUri && !$efApp->getAc()->isActionAllowed($config['modelsExclusiveRight'])) {

            if (!($owApp->selectedModel && ($modelUri == $owApp->selectedModel->getModelUri()))) {
                $owApp->selectedModel = $efStore->getModel($modelUri);
                return;
            }

            if ($config['setSelectedResource']) {
                $owApp->selectedResource = $modelUri;
            }
        }
    }
}
