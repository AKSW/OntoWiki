<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * edit extension configuration via a gui
 *
 * file permissions for the folders that contain a extension needs to allow modification
 * mostly that would be 0777
 *
 * @category   OntoWiki
 * @package    Extensions_Exconf
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ExconfController extends OntoWiki_Controller_Component
{
    const EXTENSION_CLASS = 'http://usefulinc.com/ns/doap#Project';
    const VERSION_CLASS = 'http://usefulinc.com/ns/doap#Version';
    const EXTENSION_TITLE_PROPERTY = 'http://www.w3.org/2000/01/rdf-schema#label'; //rdfs:label
    const EXTENSION_NAME_PROPERTY = 'http://usefulinc.com/ns/doap#name'; //doap:name
    const EXTENSION_DESCRIPTION_PROPERTY = 'http://usefulinc.com/ns/doap#description'; //doap:description
    const EXTENSION_RELEASELOCATION_PROPERTY = 'http://usefulinc.com/ns/doap#file-release';
    const EXTENSION_RELEASE_PROPERTY = 'http://usefulinc.com/ns/doap#release';
    const EXTENSION_PAGE_PROPERTY = 'http://usefulinc.com/ns/doap#homepage';
    const EXTENSION_RELEASE_ID_PROPERTY = 'http://usefulinc.com/ns/doap#revision';
    const EXTENSION_AUTHOR_PROPERTY = 'http://usefulinc.com/ns/doap#maintainer';
    const EXTENSION_AUTHORLABEL_PROPERTY = 'http://xmlns.com/foaf/0.1/name';
    const EXTENSION_AUTHORPAGE_PROPERTY = 'http://xmlns.com/foaf/0.1/homepage';
    const EXTENSION_AUTHORMAIL_PROPERTY = 'http://xmlns.com/foaf/0.1/mbox';
    const EXTENSION_MINOWVERSION_PROPERTY = 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/minOWVersion';
    const EXTENSION_NS = 'http://ns.ontowiki.net/SysOnt/ExtensionConfig/';

    protected $_useFtp = false;
    protected $_folderWriteable = true;

    protected $_connection = null;
    protected $_sftp = null;

    public function __call($method, $args)
    {
        $this->_forward('list');
    }

    public function init()
    {
        parent::init();
        $nav = OntoWiki::getInstance()->getNavigation();
        $nav->reset();

        $nav->register(
            'list',
            array(
                'route'      => null,
                'action'     => 'list',
                'controller' => 'exconf',
                'name'   => 'Locally Installed'
            )
        );
        $nav->register(
            'repo',
            array(
                'route'      => null,
                'action'     => 'explorerepo',
                'controller' => 'exconf',
                'name'   => 'Install / Upgrade from Repo'
            )
        );

        $ow = OntoWiki::getInstance();
        $modMan = $ow->extensionManager;

        //determine how to write to the filesystem
        if (!is_writeable($modMan->getExtensionPath())) {
            $con = $this->ftpConnect();
            if ($con->connection == null) {
                $this->_folderWriteable = false;
                $this->_connection = false;
                $this->_sftp = false;
            } else {
                $this->_useFtp = true;
                $this->_connection = $con->connection;
                $this->_sftp = $con->sftp;
            }
        }
    }

    function listAction()
    {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Configure Extensions'));

        $this->addModuleContext('main.window.exconf');

        $ow = OntoWiki::getInstance();
        if (
            !$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration') &&
            !$this->_request->isXmlHttpRequest()
        ) {
            OntoWiki::getInstance()->appendMessage(
                new OntoWiki_Message('config not allowed for this user', OntoWiki_Message::ERROR)
            );
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
                $volume[$key]  = $row->title;
            }
            array_multisort($volume, SORT_ASC, $extensions);

            //some statistics
            $numEnabled = 0;
            $numDisabled = 0;
            foreach ($extensions as $extension) {
                if ($extension->enabled) {
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

            if (!is_writeable($modMan->getExtensionPath())) {
                if (!$this->_request->isXmlHttpRequest()) {
                    OntoWiki::getInstance()->appendMessage(
                        new OntoWiki_Message(
                            "the extension folder '".$modMan->getExtensionPath()."' is not writeable.".
                            " no changes can be made",
                            OntoWiki_Message::WARNING
                        )
                    );
                }
            }

            $this->view->coreExtensions = $this->_config->extensions->core->toArray();
        }
        $this->view->extensions = $extensions;
    }

    function confAction()
    {
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        $this->view->placeholder('main.window.title')->set(
            $this->_owApp->translate->_('Configure ').' '.$this->_request->getParam('name')
        );
        if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration')) {
           throw new OntoWiki_Exception('config not allowed for this user');
        } else {
            if (!isset($this->_request->name)) {
                throw new OntoWiki_Exception("param 'name' needs to be passed to this action");
            }
            $ow = OntoWiki::getInstance();
            $toolbar = $ow->toolbar;
            $urlList = new OntoWiki_Url(array('controller'=>'exconf','action'=>'list'), array());
            $urlConf = new OntoWiki_Url(array('controller'=>'exconf','action'=>'conf'), array());
            $urlConf->restore = 1;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'save'))
                    ->appendButton(
                        OntoWiki_Toolbar::CANCEL,
                        array('name' => 'back', 'class' => '', 'url' => (string) $urlList)
                    )
                    ->appendButton(
                        OntoWiki_Toolbar::EDIT,
                        array('name' => 'restore defaults', 'class' => '', 'url' => (string) $urlConf)
                    );

            // add toolbar
            $this->view->placeholder('main.window.toolbar')->set($toolbar);

            $name = $this->_request->getParam('name');
            $manager        = $ow->extensionManager;
            $dirPath  = $manager->getExtensionPath(). $name .DIRECTORY_SEPARATOR;
            if (!is_dir($dirPath)) {
                throw new OntoWiki_Exception('invalid extension - '.$dirPath.' does not exist or no folder');
            }
            //$configFilePath = $dirPath.Ontowiki_Extension_Manager::EXTENSION_DEFAULT_DOAP_FILE;
            $localIniPath   = $manager->getExtensionPath().$name.".ini";

            $privateConfig       = $manager->getPrivateConfig($name);
            $config              = ($privateConfig != null ? $privateConfig->toArray() : array());
            $this->view->enabled = $manager->isExtensionActive($name);
            $fullConfig = $manager->getExtensionConfig($name);
            $this->view->isCoreExtension = isset($fullConfig->isCoreExtension) && $fullConfig->isCoreExtension;

            $this->view->config  = $config;
            $this->view->name    = $name;

            $this->view->coreExtensions = $this->_config->extensions->core->toArray();

            if (!is_writeable($manager->getExtensionPath())) {
                if (!$this->_request->isXmlHttpRequest()) {
                    OntoWiki::getInstance()->appendMessage(
                        new OntoWiki_Message(
                            "the extension folder '".$manager->getExtensionPath()."' is not writeable. ".
                            'no changes can be made',
                            OntoWiki_Message::WARNING
                        )
                    );
                }
            } else {
                    //react on post data
                    if (isset($this->_request->remove)) {
                        if (self::rrmdir($dirPath)) {
                            OntoWiki::getInstance()->appendMessage(
                                new OntoWiki_Message('extension deleted', OntoWiki_Message::SUCCESS)
                            );
                            $this->_redirect($this->urlBase.'exconf/list');
                        } else {
                            OntoWiki::getInstance()->appendMessage(
                                new OntoWiki_Message('extension could not be deleted', OntoWiki_Message::ERROR)
                            );
                        }
                    }
                    //the togglebuttons in the extension list action, send only a new enabled state
                    if (isset($this->_request->enabled)) {
                        if (!file_exists($localIniPath)) {
                            @touch($localIniPath);
                            chmod($localIniPath, 0777);
                        }
                        $ini = new Zend_Config_Ini($localIniPath, null, array('allowModifications' => true));
                        $ini->enabled = $this->_request->getParam('enabled') == "true";
                        $writer = new Zend_Config_Writer_Ini(array());
                        $writer->write($localIniPath, $ini, true);
                    }
                    // the conf action sends a complete config array as json
                    if (isset($this->_request->config)) {
                        $arr = json_decode($this->_request->getParam('config'), true);
                        if ($arr == null) {
                            throw new OntoWiki_Exception('invalid json: '.$this->_request->getParam('config'));
                        } else {
                            if (!file_exists($localIniPath)) {
                                @touch($localIniPath);
                                chmod($localIniPath, 0777);
                            }
                            //only modification of the private section and the enabled-property are allowed
                            foreach ($arr as $key => $val) {
                                if ($key != 'enabled' && $key != 'private') {
                                    unset($arr[$key]);
                                }
                            }
                            $writer = new Zend_Config_Writer_Ini(array());
                            $postIni = new Zend_Config($arr, true);
                            $writer->write($localIniPath, $postIni, true);
                            OntoWiki::getInstance()->appendMessage(
                                new OntoWiki_Message('config sucessfully changed', OntoWiki_Message::SUCCESS)
                            );
                        }
                        $this->_redirect($this->urlBase.'exconf/conf/?name='.$name);
                    }
                    if (isset($this->_request->reset)) {
                        if (@unlink($localIniPath)) {
                            OntoWiki::getInstance()->appendMessage(
                                new OntoWiki_Message(
                                    'config sucessfully reverted to default',
                                    OntoWiki_Message::SUCCESS
                                )
                            );
                        } else {
                            OntoWiki::getInstance()->appendMessage(
                                new OntoWiki_Message(
                                    'config not reverted to default - not existing or not writeable',
                                    OntoWiki_Message::ERROR
                                )
                            );
                        }
                        $this->_redirect($this->urlBase.'exconf/conf/?name='.$name);
                    }
            }
        }

        if ($this->_request->isXmlHttpRequest()) {
            //no rendering
            $this->_helper->viewRenderer->setNoRender();
        }
    }

    public function explorerepoAction()
    {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Explore Repo'));

        if (!$this->_erfurt->getAc()->isActionAllowed('ExtensionConfiguration')) {
           throw new OntoWiki_Exception('config not allowed for this user');
        }
        $repoUrl = $this->_privateConfig->repoUrl;

        if (($otherRepo = $this->getParam('repoUrl')) != null) {
            $repoUrl = $otherRepo;
        }
        $graph = $this->_privateConfig->graph;
        if (($otherGraph = $this->getParam('graph')) != null) {
            $graph = $otherGraph;
        }
        $this->view->repoUrl = $repoUrl;
        $this->view->graph = $graph;
        $ow = OntoWiki::getInstance();

        $ow->appendMessage(new OntoWiki_Message('Repository: '.$repoUrl, OntoWiki_Message::INFO));
        //$ow->appendMessage(new OntoWiki_Message("Graph: ".$graph, OntoWiki_Message::INFO));
        //define the list on a new store, that queries a sparql endpoint
        $adapter = new Erfurt_Store_Adapter_Sparql(array('serviceUrl'=>$repoUrl, 'graphs'=>array($graph)));
        $store = new Erfurt_Store(array('adapterInstance'=>$adapter), 'sparql');

        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = 'extensions';
        if ($listHelper->listExists($listName)) {
            $list = $listHelper->getList($listName);
            $list->setStore($store);
            $list->invalidate(); //remote repo may change data
            $listHelper->addList($listName, $list, $this->view, 'list_extensions_main');
        } else {
            $rdfGraphObj = new Erfurt_Rdf_Model($graph);
            $list = new OntoWiki_Model_Instances($store, $rdfGraphObj, array(Erfurt_Store::USE_CACHE => false));
            $list->addTypeFilter(self::VERSION_CLASS, null, array('withChilds'=>false));

            //the version needs to be related to a project (inverse)
            $projectVar = new Erfurt_Sparql_Query2_Var('project');
            $list->addTripleFilter(
                array(
                    new Erfurt_Sparql_Query2_Triple(
                        $projectVar,
                        new Erfurt_Sparql_Query2_IriRef(self::EXTENSION_RELEASE_PROPERTY),
                        $list->getResourceVar()
                    )
                )
            );

            //$list->addShownProperty(self::EXTENSION_RELEASE_PROPERTY, 'project', true);

            //internal name (folder name)
            $this->addProjectProperty(self::EXTENSION_NAME_PROPERTY, $projectVar, $list, 'name');
            //pretty name (label)
            $this->addProjectProperty(self::EXTENSION_TITLE_PROPERTY, $projectVar, $list, 'title');
            $this->addProjectProperty(self::EXTENSION_DESCRIPTION_PROPERTY, $projectVar, $list);
            $this->addProjectProperty(self::EXTENSION_PAGE_PROPERTY, $projectVar, $list);
            $this->addProjectProperty(self::EXTENSION_AUTHOR_PROPERTY, $projectVar, $list);

            $this->addAuthorProperty(self::EXTENSION_AUTHORLABEL_PROPERTY, $projectVar, $list, 'authorLabel');
            $this->addAuthorProperty(self::EXTENSION_AUTHORPAGE_PROPERTY, $projectVar, $list);
            $this->addAuthorProperty(self::EXTENSION_AUTHORMAIL_PROPERTY, $projectVar, $list);

            //properties of the versions
            $list->addShownProperty(self::EXTENSION_RELEASELOCATION_PROPERTY, 'zip');
            $list->addShownProperty(self::EXTENSION_RELEASE_ID_PROPERTY, 'revision');
            $list->addShownProperty(self::EXTENSION_MINOWVERSION_PROPERTY, 'minOwVersion');

            $listHelper->addListPermanently($listName, $list, $this->view, 'list_extensions_main');
        }

        //$this->addModuleContext('main.window.list');

        //echo htmlentities($list->getResourceQuery());
        //echo htmlentities($list->getQuery());
    }

    private function addProjectProperty($p, $projectVar, $list, $name = null)
    {
        $pIri = new Erfurt_Sparql_Query2_IriRef($p);
        if ($name == null) {
            $var = new Erfurt_Sparql_Query2_Var($pIri);
        } else {
            $var = new Erfurt_Sparql_Query2_Var($name);
        }
        $projectVar = new Erfurt_Sparql_Query2_Var($projectVar->getName().  substr(md5($pIri), 0, 5));
        $versionToProjectTriple = new Erfurt_Sparql_Query2_Triple(
            $projectVar,
            new Erfurt_Sparql_Query2_IriRef(self::EXTENSION_RELEASE_PROPERTY),
            $list->getResourceVar()
        );
        $projectPropertyTriple = new Erfurt_Sparql_Query2_Triple($projectVar, $pIri, $var);
        $list->addShownPropertyCustom(array($versionToProjectTriple, $projectPropertyTriple), $var);
    }

    private function addAuthorProperty($p, $projectVar, $list, $name = null)
    {
        $pIri = new Erfurt_Sparql_Query2_IriRef($p);
        if ($name == null) {
            $var = new Erfurt_Sparql_Query2_Var($pIri);
        } else {
            $var = new Erfurt_Sparql_Query2_Var($name);
        }
        $projectVar = new Erfurt_Sparql_Query2_Var($projectVar->getName().  substr(md5($pIri), 0, 5));
        //for each property a new author var
        $authorVar = new Erfurt_Sparql_Query2_Var('author'.  substr(md5($pIri), 0, 5));
        $versionToProjectTriple = new Erfurt_Sparql_Query2_Triple(
            $projectVar,
            new Erfurt_Sparql_Query2_IriRef(self::EXTENSION_RELEASE_PROPERTY),
            $list->getResourceVar()
        );
        $projectToAuthorTriple = new Erfurt_Sparql_Query2_Triple(
            $projectVar,
            new Erfurt_Sparql_Query2_IriRef(self::EXTENSION_AUTHOR_PROPERTY),
            $authorVar
        );
        $authorPropertyTriple = new Erfurt_Sparql_Query2_Triple($authorVar, $pIri, $var);
        $list->addShownPropertyCustom(
            array($versionToProjectTriple, $projectToAuthorTriple, $authorPropertyTriple),
            $var
        );
    }

    /**
     * download a archive file from a remote webserver
     */
    public function installarchiveremoteAction()
    {
        $ontoWiki = OntoWiki::getInstance();
        $url = $this->getParam('url', '');
        $name = $this->getParam('name', '');
        if ($url == '' || $name == '') {
            $ontoWiki->appendMessage(new OntoWiki_Message('parameters url and name needed.', OntoWiki_Message::ERROR));
        } else {
            $fileStr = file_get_contents($url);
            if ($fileStr != false) {
                $tmp = sys_get_temp_dir();
                if (!(substr($tmp, -1) == PATH_SEPARATOR)) {
                    $tmp .= PATH_SEPARATOR;
                }
                $tmpfname = tempnam($tmp, 'OW_downloadedArchive.zip');

                $localFilehandle = fopen($tmpfname, 'w+');
                fwrite($localFilehandle, $fileStr);
                rewind($localFilehandle);

                $this->installArchive($tmpfname, $name);
                fclose($localFilehandle); //deletes file
            } else {
                $ontoWiki->appendMessage(new OntoWiki_Message('could not download.', OntoWiki_Message::ERROR));
            }
        }
    }

    /**
     * display a upload form
     */
    public function archiveuploadformAction()
    {
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
    public function installarchiveuploadAction()
    {
        $ontoWiki = OntoWiki::getInstance();
        if ($_FILES['archive_file']['error'] == UPLOAD_ERR_OK) {
            // upload ok,
            //$fileName = $_FILES['archive_file']['name'];
            $tmpName  = $_FILES['archive_file']['tmp_name'];
            //$mimeType = $_FILES['archive_file']['type'];
            $cachedir = ini_get('upload_tmp_dir');
            $name = $this->getParam('name', "");
            if ($name == '') {
                $ontoWiki->appendMessage(
                    new OntoWiki_Message('parameters url and name needed.', OntoWiki_Message::ERROR)
                );
            } else {
                $this->installArchive($cachedir.$tmpName, $name);
            }
        } else {
            $ontoWiki->appendMessage(new OntoWiki_Message('upload error.', OntoWiki_Message::ERROR));
        }
    }

    /**
     * handle a uploaded archive (from browser or remote webserver)
     * extract it to extension dir
     *
     * @param <type> $filePath
     * @param <type> $fileHandle
     */
    protected function installArchive($filePath, $name)
    {
        require_once 'pclzip.lib.php';
        $ext = mime_content_type($filePath);
        $this->view->success = false;

        $ontoWiki = OntoWiki::getInstance();
        switch ($ext){
            case 'application/zip':
                $this->view->success = true;
                $zip = new PclZip($filePath);

                $modMan = $ontoWiki->extensionManager;
                $path = $modMan->getExtensionPath();

                //check the uploaded archive
                $content = $zip->listContent();
                $toplevelItem = null;
                $tooManyTopLevelItems = false; //only 1 allowed
                $sumBytes = 0;
                foreach ($content as $key => $item) {
                  $level = substr_count($item['filename'], '/');
                  if ($level == 1 && substr($item['filename'], -1, 1) == DIRECTORY_SEPARATOR) {
                      if ($toplevelItem === null) {
                          $toplevelItem = $key;
                      } else {
                         $tooManyTopLevelItems = true;
                         break;
                      }
                  }

                  $sumBytes += $item['size'];
                  if ($sumBytes >= 10000000) {
                      break;
                  }
                }
                // extract contents of archive to disk (extension dir)
                //only one item at top level allowed and max. 10MioB
                if (!$tooManyTopLevelItems  && $sumBytes < 10000000) {
                    $folderName = substr($content[$toplevelItem]['filename'], 0, -1);
                    if (file_exists($path.$folderName)) {
                        self::rrmdir($path.$folderName);
                    }
                    $zip->extract(PCLZIP_OPT_PATH, $path);
                    if (file_exists($path.$folderName)) {
                        if ($folderName != $name) {
                            rename($path.$folderName, $path.$name); //move folder to expected name
                            $folderName = $name;
                        }
                        // make all writable
                        // otherwise, they can only be deleted by users www-data or root
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($path.$folderName),
                            RecursiveIteratorIterator::CHILD_FIRST
                        );
                        foreach ($iterator as $key => $handle) {
                            chmod($handle->__toString(), 0777);
                        }

                        $ontoWiki->appendMessage(
                            new OntoWiki_Message($folderName.' extension installed.', OntoWiki_Message::SUCCESS)
                        );
                    } else {
                        $ontoWiki->appendMessage(
                            new OntoWiki_Message(
                                'archiv could not be extracted. check permissions of extensions folder.',
                                OntoWiki_Message::ERROR
                            )
                        );
                    }
                } else {
                    $ontoWiki->appendMessage(
                        new OntoWiki_Message(
                            'uploaded archive was not accepted (must be < 10MB, and contain one folder).',
                            OntoWiki_Message::ERROR
                        )
                    );
                }
                break;
            default :
                $ontoWiki->appendMessage(
                    new OntoWiki_Message(
                        'uploaded archive type was not accepted (must be zip).',
                        OntoWiki_Message::ERROR
                    )
                );
                break;
        }
        $url = new OntoWiki_Url(array('controller'=>'exconf', 'action'=>'explorerepo'));
        $this->_redirect($url);
    }

    protected function checkForUpdates()
    {

    }

    /**
     * Get the connection to ftp-server
     *
     * @param unknown_type $sftp
     * @param unknown_type $connection
     */
    public function ftpConnect()
    {
        if (isset($this->_privateConfig->ftp)) {
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

    static function checkRightsRec($dir)
    {
       $right = is_writable($dir);
       if ($right && is_dir($dir)) {
         $objects = scandir($dir);
         $curObjRight = false;
         foreach ($objects as $object) {
           if ($object != '.' && $object != '..') {
             $curObjRight = self::checkRightsRec($dir.DIRECTORY_SEPARATOR.$object);
             if (!$curObjRight) {
                 $right = false;
             }
           }
         }
       }
       return $right;
    }

    static function rrmdir($dir, $check = true)
    {
       if ($check) {
         if (!self::checkRightsRec($dir)) {
           return false;
         }
       }
       if (is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
           if ($object != '.' && $object != '..') {
             if (is_dir($dir.DIRECTORY_SEPARATOR.$object)) {
                 self::rrmdir($dir.DIRECTORY_SEPARATOR.$object, false);
             } else {
                 unlink($dir.DIRECTORY_SEPARATOR.$object);
             }
           }
         }
         reset($objects);
         rmdir($dir);
       }
       return true;
    }
}
