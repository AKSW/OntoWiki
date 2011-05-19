<?php

function checkRightsRec($dir) {
   $right = is_writable($dir);
   if ($right && is_dir($dir)) {
     $objects = scandir($dir);
     $curObjRight = false;
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         $curObjRight = checkRightsRec($dir."/".$object);
         if(!$curObjRight){
             $right = false;
         }
       }
     }
   }
   return $right;
}
function rrmdir($dir, $check = true) {
   if($check){
     if(!checkRightsRec($dir)){
       return false;
     }
   }
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object, false); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
   return true;
}
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

    const EXTENSION_CLASS = "http://ns.ontowiki.net/Extensions/Extension";
    const EXTENSION_TITLE_PROPERTY = "http://www.w3.org/2000/01/rdf-schema#label"; //rdfs:label
    const EXTENSION_NAME_PROPERTY = "http://rdfs.org/sioc/ns#name"; //rdfs:label
    const EXTENSION_DESCRIPTION_PROPERTY = "http://purl.org/dc/elements/1.1/description"; //dc:description
    const EXTENSION_LATESTVERSION_PROPERTY = "http://ns.ontowiki.net/Extensions/latestVersion";
    const EXTENSION_LATESTRELEASELOCATION_PROPERTY = "http://ns.ontowiki.net/Extensions/latestReleaseLocation";


    protected $use_ftp = false;
    protected $writeable = true;

    protected $connection = null;
    protected $sftp = null;

    public function __call($method, $args) {
        echo "forward";
        $this->_forward('list');
    }
    
    public function  init() {
        parent::init();
        OntoWiki_Navigation::reset();
        
        OntoWiki_Navigation::register('list', array('route'      => null,
            'controller' => 'exconf',
            'action'     => 'list',
            'name'   => 'List Installed'));
        OntoWiki_Navigation::register('repo', array('route'      => null,
            'controller' => 'exconf',
            'action'     => 'explorerepo',
            'name'   => 'Install from Repo'));
        OntoWiki_Navigation::register('upload', array('route'      => null,
            'controller' => 'exconf',
            'action'     => 'archiveuploadform',
            'name'   => 'Install from local upload'));


        $ow = OntoWiki::getInstance();
        $modMan = $ow->extensionManager;

        //determine how to write to the filesystem
        if(!is_writeable($modMan->getExtensionPath())){
            $con = $this->ftpConnect();
            if($con->connection == null){
                $this->writeable = false;
                $this->connection = false;
                $this->sftp = false;
            } else {
                $this->use_ftp = true;
                $this->connection = $con->connection;
                $this->sftp = $con->sftp;
            }
        }
    }

    function listAction() {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Configure Extensions'));

        $this->addModuleContext('main.window.exconf');
        
        $ow = OntoWiki::getInstance();
        if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration') && !$this->_request->isXmlHttpRequest()) {
            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("config not allowed for this user", OntoWiki_Message::ERROR));
            $this->view->isAllowed = false;
            $extensions = array();
        } else {
            $this->view->isAllowed = true;
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
                        if(rrmdir($dirPath)){
                            $this->_redirect($this->urlBase.'exconf/list');
                        } else {
                            OntoWiki::getInstance()->appendMessage(new OntoWiki_Message("extension could not be deleted", OntoWiki_Message::ERROR));
                        }
                    }
                    //the togglebuttons in the extension list action, send only a new enabled state
                    if(isset($this->_request->enabled)){
                        if(!file_exists($localIniPath)){
                            @touch($localIniPath);
                            chmod($localIniPath, 0777);
                        }
                        $ini = new Zend_Config_Ini($localIniPath, null, array('allowModifications' => true));
                        $ini->enabled = $this->_request->getParam('enabled') == "true";
                        $writer = new Zend_Config_Writer_Ini(array());
                        $writer->write($localIniPath, $ini, true);
                    }
                    // the conf action sends a complete config array as json
                    if(isset($this->_request->config)){
                        $arr = json_decode($this->_request->getParam('config'), true);
                        if($arr == null){
                            throw new OntoWiki_Exception("invalid json: ".$this->_request->getParam('config'));
                        } else {
                            if(!file_exists($localIniPath)){
                                @touch($localIniPath);
                                chmod($localIniPath, 0777);
                            }
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

    public function explorerepoAction(){
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Explore Repo'));

        $repoUrl = $this->_privateConfig->repoUrl;
        if(($otherRepo = $this->getParam("repoUrl")) != null){
            $repoUrl = $otherRepo;
        }
        $graph = $this->_privateConfig->graph;
        if(($otherGraph = $this->getParam("graph")) != null){
            $graph = $otherGraph;
        }
        $this->view->repoUrl = $repoUrl;
        $ow = OntoWiki::getInstance();
        $manager        = $ow->extensionManager;
        $configs = $manager->getExtensions();
        $other = new stdClass();
        $other->configs = $configs;
       
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = "extensions";
        if($listHelper->listExists($listName)){
            $list = $listHelper->getList($listName);
            $list->invalidate(); //remote repo may change data
            //$list->setStore($store); //TODO serialization replaces the store with the default store...
            $listHelper->addList($listName, $list, $this->view, "list_extensions_main", $other);
        } else {
            $adapter = new Erfurt_Store_Adapter_Sparql(array("serviceurl"=>$repoUrl, 'graphs'=>array($graph)));
            $store = new Erfurt_Store(array("adapterInstance"=>$adapter), "sparql");
            $rdfGraphObj = new Erfurt_Rdf_Model($graph);

            $list = new OntoWiki_Model_Instances($store, $rdfGraphObj, array());
            $list->addTypeFilter(self::EXTENSION_CLASS, null, array('withChilds'=>false));
            $list->addShownProperty(self::EXTENSION_NAME_PROPERTY, "name"); //internal name
            $list->addShownProperty(self::EXTENSION_TITLE_PROPERTY, "title"); //pretty name
            $list->addShownProperty(self::EXTENSION_DESCRIPTION_PROPERTY, "description");
            $list->addShownProperty(self::EXTENSION_LATESTVERSION_PROPERTY, "latestVersion");
            $list->addShownProperty(self::EXTENSION_LATESTRELEASELOCATION_PROPERTY, "latestReleaseLocation");

            $listHelper->addListPermanently($listName, $list, $this->view, "list_extensions_main", $other);
        }
    }

    /**
     * download a archive file from a remote webserver
     */
    public function installarchiveremoteAction(){
        $url = $this->getParam('url', "");
        if($url == ""){
            $ontoWiki->appendMessage(new OntoWiki_Message("parameter url needed.", OntoWiki_Message::ERROR));
        } else {
            $fileStr = file_get_contents($url);
            if($fileStr != false){
                $tmp = sys_get_temp_dir();
                if(!(substr($tmp, -1) == PATH_SEPARATOR)){
                    $tmp .= PATH_SEPARATOR;
                }
                $tmpfname = tempnam($tmp, "OW_downloadedArchive.zip");

                $localFilehandle = fopen($tmpfname, "w+");
                fwrite($localFilehandle, $fileStr);
                rewind($localFilehandle);

                $this->installArchive($tmpfname);
                fclose($localFilehandle); //deletes file
            } else {
                $ontoWiki->appendMessage(new OntoWiki_Message("could not download.", OntoWiki_Message::ERROR));
            }
        }
    }

    /**
     * display a upload form
     */
    public function archiveuploadformAction(){
        $this->view->placeholder('main.window.title')->set('Upload new extension archive');
        $this->view->formActionUrl = $this->_config->urlBase . 'exconf/installarchiveupload';
        $this->view->formEncoding  = 'multipart/form-data';
        $this->view->formClass     = 'simple-input input-justify-left';
        $this->view->formMethod    = 'post';
        $this->view->formName      = 'archiveupload';

        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Upload Archive', 'id' => 'archiveupload'))
                ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel', 'id' => 'archiveupload'));
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

    }

    /**
     * handle a archive upload (from browser)
     */
    public function installarchiveuploadAction(){
        if ($_FILES['archive_file']['error'] == UPLOAD_ERR_OK) {
            // upload ok, move file
            //$fileUri  = $this->_request->getPost('file_uri');
            $fileName = $_FILES['archive_file']['name'];
            $tmpName  = $_FILES['archive_file']['tmp_name'];
            $mimeType = $_FILES['archive_file']['type'];
            $cachedir = ini_get('upload_tmp_dir');

            $this->installArchive($cachedir.$tmpName);

        } else {echo "error";}

    }

    /**
     * handle a uploaded archive (from browser or remote webserver)
     * extract it to extension dir
     *
     * @param <type> $filePath
     * @param <type> $fileHandle
     */
    protected function installArchive($filePath){
        require_once 'pclzip.lib.php';
        $ext = mime_content_type($filePath);
        $this->view->success = false;
        
        $ontoWiki = OntoWiki::getInstance();
        switch ($ext){
            case "application/zip":
                $this->view->success = true;
                $zip = new PclZip($filePath);

                $modMan = $ontoWiki->extensionManager;
                $path = $modMan->getExtensionPath();

                //check the uploaded archive
                $content = $zip->listContent();
                $toplevelItem = null;
                $tooManyTopLevelItems = false;
                $sumBytes = 0;
                foreach($content as $key => $item){
                  $level = substr_count($item["filename"], '/');
                  if($level == 1 && substr($item["filename"],-1, 1) == "/"){
                      if($toplevelItem === null){
                          $toplevelItem = $key;
                      } else {
                         $tooManyTopLevelItems = true;
                         break;
                      }
                  }

                  $sumBytes += $item["size"];
                  if($sumBytes >= 10000000){
                      break;
                  }
                }
                // extract contents of archive to disk (extension dir)
                if(!$tooManyTopLevelItems  && $sumBytes < 10000000){ //only one item at top level allowed and max. 10MioB
                    $extensionName = substr($content[$toplevelItem]['filename'], 0, -1);
                    if(file_exists($path.$extensionName)){
                        rrmdir($path.$extensionName);
                    }
                    $zip->extract(PCLZIP_OPT_PATH, $path);

                    //make all writable so the files are not so alienated (otherwise, they can only be deleted by www-data or root)
                    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path.$extensionName),
                                              RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($iterator as $name => $handle) {
                        chmod($handle->__toString(), 0777);
                    }
                    
                    $ontoWiki->appendMessage(new OntoWiki_Message($path.$extensionName, OntoWiki_Message::SUCCESS));
                    $ontoWiki->appendMessage(new OntoWiki_Message("extension installed.", OntoWiki_Message::SUCCESS));
                } else {
                    $ontoWiki->appendMessage(new OntoWiki_Message("uploaded archive was not accepted (must be < 10MB, and contain one folder).", OntoWiki_Message::ERROR));
                }
                break;
            default :
                $ontoWiki->appendMessage(new OntoWiki_Message("uploaded archive was not accepted (must be zip).", OntoWiki_Message::ERROR));
                break;
        }
        $url = new OntoWiki_Url(array('controller'=>'exconf', 'action'=>'explorerepo'));
        $this->_redirect($url);
    }

    protected function checkForUpdates(){

    }

    /**
     * Get the connection to ftp-server
     *
     * @param unknown_type $sftp
     * @param unknown_type $connection
     */
    public function ftpConnect(){
        if(isset($this->_privateConfig->ftp)){
            $username = $this->_privateConfig->ftp->username;
            $password = $this->_privateConfig->ftp->password;
            $hostname = $this->_privateConfig->ftp->hostname;
            //$ssh2 = "ssh2.sftp://$username:$password@$hostname:22";
            $connection = ssh2_connect($hostname, 22);
            ssh2_auth_password($connection, $username, $password);
            $sftp = ssh2_sftp($connection);

            $ret = new stdClass();
            $ret->connection = $connection;
            $ret->sftp = $sftp;
            return $ret;
        } else {
            $ret = new stdClass();
            $ret->connection = null;
            $ret->sftp = null;
            return $ret;
        }
    }
}