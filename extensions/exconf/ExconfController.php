<?php
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
 */
class ExconfController extends OntoWiki_Controller_Component {

    public function __call($method, $args) {
        $this->_forward('list');
    }
    
    public function  init() {
        parent::init();
        OntoWiki_Navigation::disableNavigation();
    }

    function listAction() {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Configure Extensions'));

        $this->addModuleContext('main.window.exconf');
        
        $ow = OntoWiki::getInstance();
        if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration') && !$this->_request->isXmlHttpRequest()) {
            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config not allowed for this user", OntoWiki_Message::ERROR));
            $extensions = array();
        } else {
            //get extension from manager
            $modMan = $ow->extensionManager;
            $extensions = $modMan->getExtensions();

            //sort by name property
            $volume = array();
            foreach ($extensions as $key => $row) {
                $volume[$key]  = $row->name;
            }
            array_multisort($volume, SORT_ASC, $extensions);

            //some statistics
            $numEnabled = 0;
            $numDisabled = 0;
            foreach($extensions as $extension){
                if($extension->enabled){
                    $numEnabled++;
                } else {
                    $numDisabled++;
                }
            }
            $numAll = count($extensions);

            //save to view
            $this->view->numEnabled = $numEnabled;
            $this->view->numDisabled = $numDisabled;
            $this->view->numAll = $numAll;

            if(!is_writeable($modMan->getExtensionPath())){
                if(!$this->_request->isXmlHttpRequest()){
                    OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("the extension folder '".$modMan->getExtensionPath()."' is not writeable. no changes can be made", OntoWiki_Message::WARNING));
                }
            }
        }
        $this->view->extensions = $extensions;
    }
    
    function confAction(){
        
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Configure ').' '.$this->_request->getParam('name'));
        if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration')) {
           throw new OntoWiki_Exception("config not allowed for this user");
        } else {
            if(!isset($this->_request->name)){
                throw new OntoWiki_Exception("param 'name' needs to be passed to this action");
            }
            $ow = OntoWiki::getInstance();
            $toolbar = $ow->toolbar;
            $urlList = new OntoWiki_Url(array('controller'=>'exconf','action'=>'list'), array());
            $urlConf = new OntoWiki_Url(array('controller'=>'exconf','action'=>'conf'), array());
            $urlConf->restore = 1;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'save'))
                    ->appendButton(OntoWiki_Toolbar::CANCEL, array('name' => 'back', 'class' => '', 'url' => (string) $urlList))
                    ->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'restore defaults', 'class' => '', 'url' => (string) $urlConf));

            // add toolbar
            $this->view->placeholder('main.window.toolbar')->set($toolbar);

            $name = $this->_request->getParam('name');
            $manager        = $ow->extensionManager;
            $dirPath  = $manager->getExtensionPath(). $name .'/';
            if(!is_dir($dirPath)){
                throw new OntoWiki_Exception("invalid extension - does not exists");
            }
            $configFilePath = $dirPath.Ontowiki_Extension_Manager::DEFAULT_CONFIG_FILE;
            $localIniPath   = $manager->getExtensionPath().$name.".ini";

            $privateConfig       = $manager->getPrivateConfig($name);
            $config              = ($privateConfig != null ? $privateConfig->toArray() : array());
            $this->view->enabled = $manager->isExtensionActive($name);

            $this->view->config  = $config;
            $this->view->name    = $name;

            if(!is_writeable($manager->getExtensionPath())){
                if(!$this->_request->isXmlHttpRequest()){
                    OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("the extension folder '".$manager->getExtensionPath()."' is not writeable. no changes can be made", OntoWiki_Message::WARNING));
                }
            } else  {
                    //react on post data
                    if(isset($this->_request->remove)){
                        if(rmdir($dirPath)){
                            $this->_redirect($this->urlBase.'exconf/list');
                        } else {
                            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("extension could not be deleted", OntoWiki_Message::ERROR));
                        }
                    }
                    if(isset($this->_request->enabled)){
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
                            $writer = new Zend_Config_Writer_Ini(array());
                            $postIni = new Zend_Config($arr, true);
                            $writer->write($localIniPath, $postIni, true);
                            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config sucessfully changed", OntoWiki_Message::SUCCESS));
                        }
                        $this->_redirect($this->urlBase.'exconf/conf/?name='.$name);
                    }
                    if(isset($this->_request->reset)){
                        if(@unlink($localIniPath)){
                            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config sucessfully reverted to default", OntoWiki_Message::SUCCESS));
                        } else {
                            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config not reverted to default - not existing or not writeable", OntoWiki_Message::ERROR));
                        }
                        $this->_redirect($this->urlBase.'exconf/conf/?name='.$name);
                    }
            }
        }

        if($this->_request->isXmlHttpRequest()){
            //no rendering
            exit;
        }
    }
}

