<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * edit extension configuration via a gui
 *
 * file permissions for the folders that contain a extension needs to allow modification
 * mostly that would be 0777
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_exconf
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class ExconfController extends OntoWiki_Controller_Component {

    public function __call($method, $args) {
        $this->_forward('list');
    }
    
    public function  init() {
        parent::init();
        OntoWiki_Navigation :: reset();
        OntoWiki_Navigation :: register('component', array (
                'controller' => "exconf",
                'action' => "list",
                'name' => "Components",
                'position' => 0,
                'active' => "active",
                'type' => 'component'
        ));
        OntoWiki_Navigation :: register('module', array (
                'controller' => "exconf",
                'action' => "list",
                'name' => "Modules",
                'position' => 0,
                'active' => "active",
                'type' => 'module'
        ));
        OntoWiki_Navigation :: register('plugin', array (
                'controller' => "exconf",
                'action' => "list",
                'name' => "Plugins",
                'position' => 0,
                'active' => "active",
                'type' => 'plugin'
        ));
    }

    function listAction() {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Configure Extensions'));
        
        $ow = OntoWiki::getInstance();
        
        $type = "component";
        if(isset($this->_request->type)){
            $type = $this->_request->getParam("type");
        }
        
        switch ($type) {
            case "component":
                $compMan = $ow->componentManager;
                $this->view->extensions = $compMan->getComponents();
                break;
            case "plugin":
                $pluginMan = $ow->pluginManager;
                $this->view->extensions = $pluginMan->getPlugins();
                break;
            case "module":
                $modMan = $ow->moduleManager;
                $this->view->extensions = $modMan->getModules();
                break;
            default:
                throw new OntoWiki_Exception("invalid type given");
                break;
        }
        
	OntoWiki_Navigation :: setActive($type);
        ksort($this->view->extensions);
        $this->view->type = $type;
    }
    
    function confAction(){
        if(!isset($this->_request->name) || !isset($this->_request->type)){
            throw new OntoWiki_Exception("param 'name' and 'type' needs to be passed to this action");
        }
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Configure ').' '.$this->_request->getParam('type').' '.$this->_request->getParam('name'));
        if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration') && !$this->_request->isXmlHttpRequest()) {
            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config not allowed for this user", OntoWiki_Message::ERROR));
        }
        $ow = OntoWiki::getInstance();
        
        $toolbar = OntoWiki::getInstance()->toolbar;
        $urlList = new OntoWiki_Url(array('controller'=>'exconf','action'=>'list'), array());
        $urlConf = new OntoWiki_Url(array('controller'=>'exconf','action'=>'conf'), array());
        $urlConf->restore = 1;
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'save'))
                ->appendButton(OntoWiki_Toolbar::CANCEL, array('name' => 'back', 'class' => '', 'url' => (string) $urlList))
                ->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'restore defaults', 'class' => '', 'url' => (string) $urlConf));
        
        // add toolbar
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $type = $this->_request->getParam('type');
        $name = $this->_request->getParam('name');
        switch ($type){
            case 'component':
                $manager        = $ow->componentManager;
                $dirPath  = $manager->getComponentPath(). $name .'/';
                if(!is_dir($dirPath)){
                    throw new OntoWiki_Exception("invalid extension - does not exists");
                }
                $configFilePath = $dirPath.OntoWiki_Component_Manager::COMPONENT_CONFIG_FILE;
                $localIniPath   = $dirPath.OntoWiki_Component_Manager::COMPONENT_PRIVATE_CONFIG_FILE;

                $privateConfig       = $manager->getComponentPrivateConfig($name);
                $config              = ($privateConfig != null ? $privateConfig->toArray() : array());
                $this->view->enabled = $manager->isComponentActive($name);
                
                break;
            case 'module':
                $manager = $ow->moduleManager;
                $dirPath  = $manager->getModulePath(). $name .'/';
                if(!is_dir($dirPath)){
                    throw new OntoWiki_Exception("invalid extension - does not exists");
                }
                $configFilePath = $dirPath.OntoWiki_Module_Manager::MODULE_CONFIG_FILE;
                $localIniPath   = $dirPath.OntoWiki_Module_Manager::MODULE_LOCAL_CONFIG_FILE;

                $config              = $manager->getModuleConfig($name);
                $config              =  isset($config["private"]) ? $config["private"] : array();
                $this->view->enabled = $manager->isModuleEnabled($name);

                break;
            case 'plugin':
                $manager = $ow->pluginManager;

                $config              = $manager->getPlugin($name);
                
                $dirPath  = $config['pluginPath'];
                if(!is_dir($dirPath)){
                    throw new OntoWiki_Exception("invalid extension - dir '$dirPath' does not exists");
                }

                $configFilePath = $dirPath.Erfurt_Plugin_Manager::CONFIG_FILENAME;
                $localIniPath   = $dirPath.Erfurt_Plugin_Manager::CONFIG_LOCAL_FILENAME;
                
                $config              = isset($config["private"]) ? $config["private"] : array();
                $this->view->enabled = $manager->isPluginEnabled($name);

                break;
            case 'wrapper':
                throw new OntoWiki_Exception("not supported yet");
                break;
            case 'theme':
                throw new OntoWiki_Exception("not supported yet");
                break;
            default :
                throw new OntoWiki_Exception("invalid type");
                break;
        }
        $this->view->config  = $config;
        $this->view->name    = $name;
        $this->view->type    = $type;
        
        function assertRights(){
            if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration')) {
                throw new OntoWiki_Exception("config not allowed for this user");
            }
        }
        if(!is_writeable($dirPath)){
            if(!$this->_request->isXmlHttpRequest()){
                OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("the $type folder '$dirPath' is not writeable. no changes can be made", OntoWiki_Message::WARNING));
            }
        } else {
                //react on post data
                if(isset($this->_request->remove)){
                    assertRights();
                    if(rmdir($dirPath)){
                        $this->_redirect($this->urlBase.'exconf/list');
                    } else {
                        OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("extension could not be deleted", OntoWiki_Message::ERROR));
                    }
                }
                if(isset($this->_request->enabled)){
                    assertRights();
                    if(!file_exists($localIniPath)){
                        @touch($localIniPath);
                    }
                    $ini = new Zend_Config_Ini($localIniPath, null, array('allowModifications' => true));
                    $ini->enabled = $this->_request->getParam('enabled') == "true";
                    $writer = new Zend_Config_Writer_Ini(array());
                    $writer->write($localIniPath, $ini, true);
                }
                if(isset($this->_request->config)){
                    $arr = json_decode($this->_request->getParam('config'), true);
                    if($arr == null){
                        throw new OntoWiki_Exception("invalid json: ".$this->_request->getParam('config'));
                    } else {
                        //only modification of the private section and the enabled-property are allowed
                        foreach($arr as $key => $val){
                            if($key != 'enabled' && $key != 'private'){
                                unset($arr[$key]);
                            }
                        }
                        assertRights();
                        $writer = new Zend_Config_Writer_Ini(array());
                        $postIni = new Zend_Config($arr, true);
                        $writer->write($localIniPath, $postIni, true);
                        OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config sucessfully changed", OntoWiki_Message::SUCCESS));
                    }
                    $this->_redirect($this->urlBase.'exconf/conf/?type='.$type.'&name='.$name);
                }
                if(isset($this->_request->reset)){
                    assertRights();
                    if(@unlink($localIniPath)){
                        OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config sucessfully reverted to default", OntoWiki_Message::SUCCESS));
                    } else {
                        OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config not reverted to default - not existing or not writeable", OntoWiki_Message::ERROR));
                    }
                    $this->_redirect($this->urlBase.'exconf/conf/?type='.$type.'&name='.$name);
                }
        }

        if($this->_request->isXmlHttpRequest()){
            //no rendering
            exit;
        }
    }
}

