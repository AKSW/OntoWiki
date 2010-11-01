<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'Zend/Http/Client.php';

/**
 *  Controller for the OntoWiki Plugin Manager
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_repository
 * @author     Qiu Feng <qiu_feng39@hotmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */

class RepositoryController extends OntoWiki_Controller_Component {
  public function init(){
    parent::init();

    require_once('Zend/Paginator.php');
    require_once('Zend/Paginator/Adapter/Array.php');
	require_once 'Zend/Http/Client.php';

    OntoWiki_Navigation::reset();
  }

  /**
   * categorieAction
   *
   * This action can let the user to choose the category
   * of plungin that they want to search

   */
  public function indexAction(){
    $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Plugin Categories'));
    $this->view->headLink()->appendStylesheet($this->_componentUrlBase.'templates/repository/css/login.css', 'screen, projection');
	$this->view->login_url = $this->getFrontController()->getBaseUrl()."/repository/index";
	if ($this->_request->isPost()) {
		$username = $this->_request->getPost('username');
		$password = $this->_request->getPost('password');
		$client = Erfurt_App::getInstance()->getHttpClient($this->_config->urlBase . 'sparql/');	
		$u_query = 'PREFIX type: <http://www.w3.org/1999/02/22-rdf-syntax-ns#type>
					PREFIX value: <http://rdfs.org/sioc/ns#User>
					PREFIX password: <http://ns.ontowiki.net/SysOnt/userPassword>
					PREFIX firstname: <http://rdfs.org/sioc/ns#first_name>
					PREFIX lastname: <http://rdfs.org/sioc/ns#last_name>
					PREFIX id: 	<http://rdfs.org/sioc/ns#id>
					
					SELECT ?node ?id ?password ?firstname ?lastname
					WHERE {?node type: value:.
					       ?node id: ?id.
					       ?node password: ?password.
					OPTIONAL {?node firstname: ?firstname.
					          ?node lastname: ?lastname.}
					}';
	    $client->setParameterGet('query',$u_query);
	    $client->setHeaders('Accept', 'application/sparql-results+json');
		$response = $client->request('GET');
		$sparl_results = Zend_Json::decode($response->getBody());
		//print_r($sparl_results['bindings'][0]);
		$logged_in =  false;
		foreach ($sparl_results['bindings'] as $a_sparal_result){
			if($a_sparal_result['id']['value'] == $username){
				$r_username = $a_sparal_result['id']['value'];
				if($a_sparal_result['password']['value'] == $password){
					$logged_in = true;
					break;
				}
			}
		}
		$this->view->message = "Username:".$username."     Password:".$password;
		if ($logged_in) {
			$this->view->message .= '<br/><font size="4" color="blue">You are logged in!</font><br/>';
		}
		else{
			$this->view->message .= '<br/><font size="4" color="red">Wrong username or password, please try again!</font><br/>';
		}
	}
  }
  
  public function registerAction(){
  	// for testing
  	//echo 'aaaaaaaaaaaaaaa';
  	require_once 'Erfurt/Store.php';
	try{
		$my_store = new Erfurt_Store('virtuoso', array('username' =>'dba', 'password' => 'dba'), null);
	}catch (Exception $e){
		echo $e;
	}
  	
  	
  	
  	
  	$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Register Page'));
  	$this->view->headLink()->appendStylesheet($this->_componentUrlBase.'templates/repository/css/registory.css', 'screen, projection');
  	$this->view->headScript()->appendFile($this->_componentUrlBase . 'templates/resources/check.js');
    $pluginurl = $this->getFrontController()->getBaseUrl()."/repository/register";
    $this->view->pluginurl = $pluginurl;
    if ($this->_request->isPost()) {
    	$username = $this->_request->getPost('username');
    	$password = $this->_request->getPost('password');
    	//echo $username."     ".$password;
    	$client = Erfurt_App::getInstance()->getHttpClient($this->_config->urlBase . 'sparql/');
		$u_query = 'INSERT in graph <http://localhost/PluginRepository/>
					{<http://ns.aksw.org/PluginRepository/account_3> 
						<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>  
						<http://rdfs.org/sioc/ns#User>.
					}';
		$client->setParameterGet('query',$u_query);
	    $client->setHeaders('Accept', 'application/sparql-results+json');
	    $client->setHeaders('Content-Type', 'application/sparql-query');
	    //print_r($client);
		$response = $client->request('GET');
		print_r($response);
    }
  }

  public function uploadAction(){
  	$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Uploas File'));
  	// default file URI
    $defaultUri = $this->_config->urlBase . 'repository/';
    require_once 'OntoWiki/Url.php';
    $url = new OntoWiki_Url(array('controller' => 'repository', 'action' => 'upload'), array());
	// check for POST'ed data
    if ($this->_request->isPost()) {
    	if ($_FILES['plugin_source']['error'] == UPLOAD_ERR_OK) {
        // upload ok, move file
	        $fileUri  = $this->_request->getPost('file_uri');
	        $fileName = $_FILES['plugin_source']['name'];
	        $tmpName  = $_FILES['plugin_source']['tmp_name'];
	        $mimeType = $_FILES['plugin_source']['type'];
                
	      	// check for unchanged uri
	        if ($fileUri == $defaultUri) {
	        $fileUri = $defaultUri
	                   . 'file'
	                   . (count(scandir(_OWROOT . $this->_privateConfig['path'])) - 2);
	         }
	                
	         // build path
	         $pathHashed = _OWROOT
	                            . $this->_privateConfig['path']
	                            . DIRECTORY_SEPARATOR
	                            . md5($fileUri);
	                
	          // move file
	         if (move_uploaded_file($tmpName, $pathHashed)) {
		         $mimeProperty = $this->_privateConfig['mime.property'];
		         $fileClass    = $this->_privateConfig['class'];
		         $fileModel    = $this->_privateConfig['model'];
		         $store        = $this->_owApp->erfurt->getStore();
	                    
	             // add file resource class
	             $this->_owApp->configModel->addStatement(
	                    $fileUri, EF_RDF_TYPE, array('value' => $fileClass, 'type' => 'uri'), false
	             );
	             // add file resource mime type
	             $this->_owApp->configModel->addStatement(
	                   $fileUri, $mimeProperty, array('value' => $mimeType, 'type' => 'literal'), false
	              );
	             // add file resource model
	             $this->_owApp->configModel->addStatement(
	                   $fileUri, $fileModel, array('value' => (string) $this->_owApp->selectedModel, 'type' => 'uri'), false
	             );
	             
	             require_once('OntoWiki/Message.php');
               	 $this->_owApp->appendMessage(
          	          new OntoWiki_Message('Your source-file is uploaded with the URL:'
          	          			. $this->_config->urlBase
          	          			. $this->_privateConfig['path']
	                            . '/'
	                            . md5($fileUri), OntoWiki_Message::SUCCESS )
         	     );       
	             //$url->action = 'manage';
	             //$this->_redirect((string) $url);
             }
             else {
             	require 'OntoWiki/Message.php';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error during move uploaded file!', OntoWiki_Message::ERROR)
                );
             }
         } 
         
        else if ($_FILES['logo_source']['error'] == UPLOAD_ERR_OK) {
        // upload ok, move file
	        $fileUri  = $this->_request->getPost('logo_uri');
	        $fileName = $_FILES['logo_source']['name'];
	        $tmpName  = $_FILES['logo_source']['tmp_name'];
	        $mimeType = $_FILES['logo_source']['type'];
                
	      	// check for unchanged uri
	        if ($fileUri == $defaultUri) {
	        $fileUri = $defaultUri
	                   . 'file'
	                   . (count(scandir(_OWROOT . $this->_privateConfig['path'])) - 2);
	         }
	                
	         // build path
	         $pathHashed = _OWROOT
	                            . $this->_privateConfig['path']
	                            . DIRECTORY_SEPARATOR
	                            . md5($fileUri);
	                
	          // move file
	         if (move_uploaded_file($tmpName, $pathHashed)) {
		         $mimeProperty = $this->_privateConfig['mime.property'];
		         $fileClass    = $this->_privateConfig['class'];
		         $fileModel    = $this->_privateConfig['model'];
		         $store        = $this->_owApp->erfurt->getStore();
	                    
	             // add file resource class
	             $this->_owApp->configModel->addStatement(
	                    $fileUri, EF_RDF_TYPE, array('value' => $fileClass, 'type' => 'uri'), false
	             );
	             // add file resource mime type
	             $this->_owApp->configModel->addStatement(
	                   $fileUri, $mimeProperty, array('value' => $mimeType, 'type' => 'literal'), false
	              );
	             // add file resource model
	             $this->_owApp->configModel->addStatement(
	                   $fileUri, $fileModel, array('value' => (string) $this->_owApp->selectedModel, 'type' => 'uri'), false
	             );
	             require_once('OntoWiki/Message.php');
               	 $this->_owApp->appendMessage(
          	          new OntoWiki_Message('Your logo-file is uploaded with the URL:'
          	          			. $this->_config->urlBase
          	          			. $this->_privateConfig['path']
	                            . '/'
	                            . md5($fileUri), OntoWiki_Message::SUCCESS )
         	     );              
	             //$url->action = 'manage';
	             //$this->_redirect((string) $url);
             }
             else {
             	require_once('OntoWiki/Message.php');
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error during move uploaded file!', OntoWiki_Message::ERROR)
                );
             }
         } 
         else {
                require 'OntoWiki/Message.php';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error during file upload.', OntoWiki_Message::ERROR)
                );
        }
     }
		$toolbar = $this->_owApp->toolbar;
        $url->action = 'repository';
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Upload File'));
        
        $this->view->defaultUri = $defaultUri;
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $url->action = 'upload';
        $this->view->formActionUrl = (string) $url;
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'fileupload';
		$this->view->formEncoding  = 'multipart/form-data';
		
		/*if (!is_writable($this->_privateConfig['path'])) {
            require_once 'OntoWiki/Message.php';
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Uploads folder is not writable.', OntoWiki_Message::WARNING)
            );
            return;
        }*/
        
        // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
	    header('Connection: close');
    
  }
  





}

?>