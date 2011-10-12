<?php
require_once 'OntoWiki/Plugin.php';
/**
 * Plugin to select default model if only one available or always.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class DefaultmodelPlugin extends OntoWiki_Plugin
{   
    public function onAfterInitController($event)
    {
        //only do this once (so if the model is changed later, this plugin will not prevent it)
        if(isset($_SESSION['defaultModelHasBeenSet']) && $_SESSION['defaultModelHasBeenSet']){
            return;
        }
        $_SESSION['defaultModelHasBeenSet'] = true;
        
        require_once 'OntoWiki/Module/Registry.php';

        $owApp = OntoWiki::getInstance();
        $efApp = Erfurt_App::getInstance();
        $efStore = $efApp->getStore();
        $config = $this->_privateConfig->toArray();
        $availableModels = $efStore->getAvailableModels();

        if ( 
            array_key_exists('modelUri',$config) 
            &&  array_key_exists($config['modelUri'],$availableModels) 
        ) {
            $modelUri = $config['modelUri'];
        } elseif ( count($availableModels) === 1 ) {
            $modelUri = current(array_keys($availableModels));
        } else {
            $modelUri = false;
        }

        // disable model box if config value is true and modelmanangement isn't allowed
        if ( $config['modelsHide'] && !$efApp->getAc()->isActionAllowed($config['modelsExclusiveRight']) ) {
            $registry = OntoWiki_Module_Registry::getInstance();
            $registry->disableModule('modellist','main.sidewindow');
        } 

        // set default model if it could be determined
        if ( $modelUri && !$efApp->getAc()->isActionAllowed($config['modelsExclusiveRight']) ) {

            if ($owApp->selectedModel && $modelUri == $owApp->selectedModel->getModelUri()) {
                // everythings fine nothing to do here
            } else {
                $owApp->selectedModel = $efStore->getModel($modelUri);
                
                // set redirect to "model/info" and send immediately
                //$response = Zend_Controller_Front::getInstance()->getResponse();
                //$url = new OntoWiki_Url(array('controller' => 'model', 'action' => 'info'), array());
                //$response->setRedirect((string)$url, 303)
                //         ->sendResponse();
                //exit;
            }
            if($config['setSelectedResource']){
                $owApp->selectedResource = $modelUri;
            }
        } else {
            // could not determine model so do nothing
        }
        
    }
}
