<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'Zend/Http/Client.php';


/**
 *  Controller for the OntoWiki Plugin Manager
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_plugins
 * @author     Qiu Feng <qiu_feng39@hotmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */

class PluginsController extends OntoWiki_Controller_Component 
{
    public function init()
    {
        parent::init();
        require_once('Zend/Paginator.php');
        require_once('Zend/Paginator/Adapter/Array.php');
        
        // Action Based Access Control for the Plugin Manager
        $owApp = OntoWiki::getInstance();
        if (!$this->_erfurt->isActionAllowed('PluginManagement')){
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception('You are not allowed to use the Plugin Manager.');
        }

        OntoWiki_Navigation::reset();
        OntoWiki_Navigation::register('categories', array(
	        'controller' => 'plugins',
	        'action'     => 'categories',
	        'name'       => 'Search',
	        'position'   => 0,
	        'active'     => false
        ));
        OntoWiki_Navigation::register('new', array(
	        'controller' => 'plugins',
	        'action'     => 'new-plugins',
	        'name'       => 'What\'s new',
	        'position'   => 1,
	        'active'     => false
        ));
        OntoWiki_Navigation::register('installed', array(
	        'controller' => 'plugins',
	        'action'     => 'installed',
	        'name'       => 'Installed',
	        'position'   => 2,
	        'active'     => false
        ));
    }
	
	/**
	*
	*
	*
	*/
	public function pluglistAction()
	{
		$client = new Zend_Http_Client($this->_privateConfig->repository->r_url);
	
		$u_query = 'PREFIX type: <'.$this->_privateConfig->p_type_base.'>
					PREFIX plugin: <'.$this->_privateConfig->plugin_base.'>
					PREFIX name: <'.$this->_privateConfig->p_name_base.'>
					PREFIX description: <'.$this->_privateConfig->p_desc_base.'>
					PREFIX developer: <'.$this->_privateConfig->p_dev_base.'>
					PREFIX release: <'.$this->_privateConfig->p_release_base.'>
					PREFIX url: <'.$this->_privateConfig->filerelease_base.'>
							
					SELECT ?node ?name ?description ?developer ?url
					WHERE {?node type: plugin:.
					    ?node name: ?name.
					    ?node description: ?description.
					    ?node developer: ?developer.
					    ?node release: ?version.
			       	    ?version url: ?url.}';
						
		$client->setParameterPost('query', $u_query);
	    $client->setHeaders('Accept', 'application/sparql-results+json');
	    $response = $client->request('POST');
	    $sparl_results = Zend_Json::decode($response->getBody());
	    $results = array();
		$wrong_response = false;
	        
	    if (!strstr($response->getBody(),'bindings')) {
	        $wrong_response = true;
			$this->_owApp->appendMessage(
	          	new OntoWiki_Message('Plugin Repository not reachable', OntoWiki_Message::ERROR )
	        );
	    }
	    
		if (count($sparl_results['bindings']) != 0 && !$wrong_response) {
	        $i = 0;
	        foreach ($sparl_results['bindings'] as $a_sparal_result) {
	            $plugin_name = $a_sparal_result['name']['value'];
	            $plugin_developer = $a_sparal_result['developer']['value'];
	            $plugin_developer = str_replace($this->_privateConfig->rdf_base_url, '', $plugin_developer);
	            $plugin_desciption = $a_sparal_result['description']['value'];
	            $results[$i] = array(
		            'name'			=> $plugin_name,
		            'developer'		=> $plugin_developer,
		            'description'	=> $plugin_desciption,
	            );
	            $i++;
	        }
	    }
		
		$this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'templates/plugins/css/plugin.css');
		$this->view->plugins = $results;
		//return 'plugins list';
	}
	
	
	
    /**
    * categorieAction
    *
    * This action can let the user to choose the category
    * of plungin that they want to search
    */
    public function categoriesAction()
    {
        //Rights check
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Plugin Categories'));
        OntoWiki_Navigation::setActive('categories',true);
        require_once 'OntoWiki/Message.php';
        //End Rights check

        //Javascript for the plugintemplates
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'templates/plugins/css/plugin.css');
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'templates/resources/pluginselect.js');
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'templates/plugins/css/tagcloud.css');
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'templates/resources/createtagcloud.js');
        $pluginurl = $this->_componentUrlBase;
        $this->view->pluginurl = $pluginurl;
        //End Javascript

        /*Query to the Repository*/
        $from = "categories";
        $search_keyword = $this->_request->getPost('search_keyword', '');
        if ($this->_request->isPost()) {
            $categorie_select = $this->_request->getPost('categorie_select');
            $sort_kind = $this->_request->getPost('sort_kind', 'name');
            $sort_priority = $this->_request->getPost('sort_priority', 'ASC');
            $this->_session->plugins_per_page = $this->_request->getPost('plugins_per_page', 3);
            $this->_session->sort_priority = $sort_priority;
        	$this->_session->sort_kind = $sort_kind;         
            //echo $categorie_select."   ".$sort_kind."   ".$sort_priority."     ".$this->_session->plugins_per_page;
        }
        else {
            $sort_kind = "name";
            $sort_priority = "ASC";
            $selected_tag = $this->getParam('tag');
            if ($selected_tag != ''){
                $from = "tag";
            }
        }
        
        //Information for sorting
		if (isset($this->_session->sort_kind)) {
        	$sort_kind = $this->_session->sort_kind;
        }
        else {
        	$sort_kind = 'name';
        }        
        if (isset($this->_session->sort_priority)) {
        	$sort_priority = $this->_session->sort_priority;
        }
        else {
        	$sort_priority = 'ASC';
        }
        
        
        $client = new Zend_Http_Client($this->_privateConfig->repository->r_url);
        
        
        
        //$client = new Zend_Http_Client($this->_config->urlBase . 'sparql/');
        //echo $this->_config->urlBase . 'sparql/';
        //$u_query = 'SELECT * WHERE {?s ?p ?o} limit 100';
        $wrong_response = false;
        // if there is no search
        if ($search_keyword == '') {
	        if ($from == 'categories' && $search_keyword == '') {
	            $u_query =	'PREFIX type: <'.$this->_privateConfig->p_type_base.'>
							 PREFIX plugin: <'.$this->_privateConfig->plugin_base.'>
							 PREFIX name: <'.$this->_privateConfig->p_name_base.'>
							 PREFIX description: <'.$this->_privateConfig->p_desc_base.'>
							 PREFIX developer: <'.$this->_privateConfig->p_dev_base.'>
							 PREFIX release: <'.$this->_privateConfig->p_release_base.'>
							 PREFIX url: <'.$this->_privateConfig->filerelease_base.'>
							
						 	 SELECT ?node ?name ?description ?developer ?url
					  		 WHERE {?node type: plugin:.
							        ?node name: ?name.
							        ?node description: ?description.
							        ?node developer: ?developer.
								    ?node release: ?version.
			       				    ?version url: ?url.}';
	        }
	        // User search for somthing
	        /*elseif ($from == 'categories' && $search_keyword != '') {
	        	$search_keyword = trim($search_keyword);
	        	$this->searchForUrl($search_keyword, $client);
	        	
	        	
	        	$search_keyword = trim($search_keyword);
	        	$first_letter = strtoupper(substr($search_keyword, 0, 1)); 
	        	$search_keyword = $first_letter . substr($search_keyword, 1);
	        	$u_query =	"	PREFIX type: <'.$this->_privateConfig->p_type_base.'>
		    					PREFIX plugin: <'.$this->_privateConfig->plugin_base.'>
								PREFIX name: <'.$this->_privateConfig->p_name_base.'>
								PREFIX description: <'.$this->_privateConfig->p_desc_base.'>
								PREFIX developer: <'.$this->_privateConfig->p_dev_base.'>
								PREFIX release: <'.$this->_privateConfig->p_release_base.'>
								PREFIX url: <'.$this->_privateConfig->filerelease_base.'>
								PREFIX tag: <'.$this->_privateConfig->p_tag_base.'>
													
								SELECT ?node ?name ?description ?developer ?url
								WHERE { ?node type: plugin:.
								        ?node name: ?name.
										?node description: ?description.
										?node developer: ?developer.
										?node release: ?version.
										?version url: ?url.
								        ?version tag: \"$search_keyword\"^^xsd:string.}";
	        }*/
	        
	        elseif ($from == 'tag') {
	            $u_query =	"	PREFIX type: <".$this->_privateConfig->p_type_base.">
		    					PREFIX plugin: <".$this->_privateConfig->plugin_base.">
								PREFIX name: <".$this->_privateConfig->p_name_base.">
								PREFIX description: <".$this->_privateConfig->p_desc_base.">
								PREFIX developer: <".$this->_privateConfig->p_dev_base.">
								PREFIX release: <".$this->_privateConfig->p_release_base.">
								PREFIX url: <".$this->_privateConfig->filerelease_base.">
								PREFIX tag: <".$this->_privateConfig->p_tag_base.">
													
								SELECT ?node ?name ?description ?developer ?url
								WHERE { ?node type: plugin:.
								        ?node name: ?name.
										?node description: ?description.
										?node developer: ?developer.
										?node release: ?version.
										?version url: ?url.
								        ?version tag: \"$selected_tag\"^^xsd:string.}";
	        }
	
	
	        $u_query = $u_query . "ORDER BY " . $sort_priority . "(?" . $sort_kind . ")";
	        //$u_query = $u_query." ORDER BY DESC(?name)";
	        //echo "query: ".$u_query."<br/>";
	        $client->setParameterPost('query', $u_query);
	        $client->setHeaders('Accept', 'application/sparql-results+json');
	        $response = $client->request('POST');
	        //print_r($response);
	        //print_r($response->getBody());
	        //echo"<br/><br/>";
	        /*End*/
	
	        /*Change the results in an array*/
	        $sparl_results = Zend_Json::decode($response->getBody());
	        //print_r($sparl_results['bindings'][0]);
	        $results = array();
	
	        
	        if (!strstr($response->getBody(),'bindings')) {
	            $wrong_response = true;
	            $this->_owApp->appendMessage(
	            	new OntoWiki_Message('Plugin Repository not reachable', OntoWiki_Message::ERROR )
	            );
	        }
	
	
	        if (count($sparl_results['bindings']) != 0 && !$wrong_response) {
	            $i = 0;
	            foreach ($sparl_results['bindings'] as $a_sparal_result) {
	                $plugin_name = $a_sparal_result['name']['value'];
	                $plugin_developer = $a_sparal_result['developer']['value'];
	                $plugin_developer = str_replace($this->_privateConfig->rdf_base_url, '', $plugin_developer);
	                $plugin_desciption = $a_sparal_result['description']['value'];
	                $plugin_install_url = $a_sparal_result['url']['value'];
	                $results[$i] = array(
		                'name'			=> $plugin_name,
		                'developer'		=> $plugin_developer,
		                'description'	=> $plugin_desciption,
		                'install_url'	=> $plugin_install_url
	                );
	                $i++;
	            }
	        }
        } // end if there is no search
        // if user wants to search
        else {
        	$results = $this->searchForPlugins($search_keyword, $client, &$wrong_response, $sort_kind, $sort_priority);
        }
        
        $count = count($results);
        if (!$wrong_response) {
        	if (!isset($results)|| $count == 0) {
	            $this->_owApp->appendMessage(
	            	new OntoWiki_Message('No Matching!', OntoWiki_Message::WARNING )
	            );
	        }
	        else {
	            $message = $count.' plugin(s) found!';
	            $this->_owApp->appendMessage(
	            	new OntoWiki_Message($message, OntoWiki_Message::INFO )
	            );
	        }
        }
        

        /* Get Tags */
        if ($from == 'tag') {
            $tags_results = $this->getTags($selected_tag);
        }
        else {
            $tags_results = $this->getTags(null);
        }
        $this->view->tags = $tags_results;
        //echo "<br/><br/>TAGS_DECODE: ".print_r($tags_results)."<br/>";
        //echo"<br/><br/>";
		
        //Check the ftp-configuation in component.ini
        if (isset($this->_privateConfig->ftp->username) && isset($this->_privateConfig->ftp->password) && isset($this->_privateConfig->ftp->hostname)) {
        	$ftp_config = true;  // true or false didn't work?!
        }
        else {
        	$ftp_config = false;
        }
        
        //Start use a Zend_paginator;
        $page =1;
        $numPerPage = 3;
        if (isset($this->_session->plugins_per_page)) {
        	$numPerPage = $this->_session->plugins_per_page;
        }
        $this->view->plugins_per_page = $numPerPage;
        if (isset($_GET['page']) && is_numeric($_GET['page'])){
            $page = $_GET['page'];
        }
        $offset = $numPerPage*$page;
        $this->count = $count;
        $this->view->results = $results;
        $this->view->to_translate = $this->getFrontController()->getBaseUrl() . '/plugins/categories';
        $this->view->toinstall_url = $this->getFrontController()->getBaseUrl() . '/plugins/getinfo';
        $this->view->ftp_config = $ftp_config;
        $this->view->plugin_outlook = $this->_privateConfig->client->plugin_outlook;
        $this->view->sort_priority = $sort_priority;
		$this->view->sort_kind = $sort_kind;
        $this->page($page, $numPerPage, $results);
        $this->render();
        //End
     //End
    }
    
    /**
     * newPluginsAction
     * 
     * Show the new Plugins
     */
    public function newPluginsAction()
    {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('The plugins, new or just updatet'));
        OntoWiki_Navigation::setActive('new',true);
        require_once 'OntoWiki/Message.php';

        //Javascript for the plugintemplates
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'templates/plugins/css/plugin.css');
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'templates/resources/pluginselect.js');
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'templates/plugins/css/tagcloud.css');
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'templates/resources/createtagcloud.js');
        $pluginurl = $this->_componentUrlBase;
        $this->view->pluginurl = $pluginurl;
        //End

        /*Query to the Repository*/
        $from = "newPlugins";
        if ($this->_request->isPost()) {
            $sort_kind = $this->_request->getPost('sort_kind');
            $sort_priority = $this->_request->getPost('sort_priority');
            $this->_session->plugins_per_page = $this->_request->getPost('plugins_per_page');
            $newplugins_in_days = $this->_request->getPost('newplugins_in_days');
            $this->_session->newplugins_in_days = $newplugins_in_days;
            $this->_session->sort_priority = $sort_priority;
            $this->_session->sort_kind = $sort_kind;
        }
        else {
        	if (isset($this->_session->newplugins_in_days)) {
            	$newplugins_in_days = $this->_session->newplugins_in_days;
            }
            else {
            	$newplugins_in_days = 0;
            }    
        	if (isset($this->_session->sort_priority)) {
        		$sort_priority = $this->_session->sort_priority;
        	}
        	else {
        		$sort_priority = "ASC";
        	}
        	if (isset($this->_session->sort_kind)) {
        		$sort_kind = $this->_session->sort_kind;
        	}
        	else {
        		$sort_kind = "name";
        	}
        	 
            $selected_tag = $this->getParam('tag');
            if ($selected_tag != ''){
                $from = "tag";
            }    
        }

        $daysago_in_seconds = $newplugins_in_days*60*60*24;
        $deadline = date('Y-m-d', time() - $daysago_in_seconds) . 'T00:00:00.000';
        //echo "<br/>$deadline<br/>";
        $this->view->deadline = $deadline;

        $client = new Zend_Http_Client($this->_privateConfig->repository->r_url);

        $u_query = 'PREFIX type: <'.$this->_privateConfig->p_type_base.'>
					PREFIX plugin: <'.$this->_privateConfig->plugin_base.'>
					PREFIX name: <'.$this->_privateConfig->p_name_base.'>
					PREFIX description: <'.$this->_privateConfig->p_desc_base.'>
					PREFIX developer: <'.$this->_privateConfig->p_dev_base.'>
					PREFIX release: <'.$this->_privateConfig->p_release_base.'>
					PREFIX url: <'.$this->_privateConfig->filerelease_base.'>
					PREFIX modified: <'.$this->_privateConfig->p_modified_base.'>
					
					SELECT ?node ?name ?description ?developer ?url ?modified
					WHERE {?node type: plugin:.
					       ?node name: ?name.
					       ?node description: ?description.
					       ?node developer: ?developer.
						   ?node release: ?version.
						   ?node modified: ?modified.
	       				   ?version url: ?url.';
        $u_query = $u_query . ' FILTER (xsd:dateTime(?modified)>="' . $deadline . '"^^xsd:dateTime) }';
        //echo "<br/>u_query: $u_query<br/>";
        $u_query = $u_query . "ORDER BY " . $sort_priority . "(?" . $sort_kind . ")";
        $client->setParameterPost('query',$u_query);
        $client->setHeaders('Accept', 'application/sparql-results+json');
        $response = $client->request('POST');

        $sparl_results = Zend_Json::decode($response->getBody());
        //print_r($sparl_results['bindings'][0]);
        $results = array();
        
        

        if (count($sparl_results['bindings']) != 0) {
            $i = 0;
            foreach ($sparl_results['bindings'] as $a_sparal_result){
                $plugin_name = $a_sparal_result['name']['value'];
                $plugin_developer = $a_sparal_result['developer']['value'];
                $plugin_developer = str_replace($this->_privateConfig->rdf_base_url, "", $plugin_developer);
                $plugin_desciption = $a_sparal_result['description']['value'];
                $plugin_install_url = $a_sparal_result['url']['value'];
                $results[$i] = array(
	                'name'			=> $plugin_name,
	                'developer'		=> $plugin_developer,
	                'description'	=> $plugin_desciption,
	                'install_url'	=> $plugin_install_url
                );
                $i++;
            }
        }

        $count = count($results);
        if (!isset($results)|| $count == 0) {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('No Matching!', OntoWiki_Message::WARNING )
            );
        }
        else {
            $message = $count.' plugin(s) found!';
            $this->_owApp->appendMessage(
            	new OntoWiki_Message($message, OntoWiki_Message::INFO )
            );

        }
        
        /* Get Tags */
        if ($from == 'tag') {
            $tags_results = $this->getTags($selected_tag);
        }
        else {
            $tags_results = $this->getTags(null);
        }        
        $this->view->tags = $tags_results;
        
        //Start use a Zend_paginator;
        $page =1;
        $numPerPage = 3;
        if (isset($this->_session->plugins_per_page)){
            $numPerPage = $this->_session->plugins_per_page;
        }
        $this->view->plugins_per_page = $numPerPage;
        if (isset($_GET['page']) && is_numeric($_GET['page'])){
            $page = $_GET['page'];
        }
        $offset = $numPerPage*$page;
        $this->count = $count;
        $this->view->results = $results;
        $this->view->new_plugins = $this->getFrontController()->getBaseUrl().'/plugins/new-plugins';
        $this->view->toinstall_url = $this->getFrontController()->getBaseUrl().'/plugins/toinstall';
        $this->view->plugin_outlook = $this->_privateConfig->client->plugin_outlook;
        $this->view->newplugins_in_days = $newplugins_in_days;
        $this->view->sort_priority = $sort_priority;
		$this->view->sort_kind = $sort_kind;
        $this->page($page, $numPerPage, $results);
        $this->render();
    }


    /**
     * installedAction
     *
     * The functions for a plugin-developer
     */
    public function installedAction()
    {
        $selected_categorie = 'all';	// this function can have more uses
        if ($this->_request->isPost()) {
            $selected_categorie = $this->_request->getPost('categorie_select');
            $_SESSION['qiufeng']['plugins_per_page'] = $this->_request->getPost('plugins_per_page');
        }
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Installed Plugins'));
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'templates/resources/makesure.js');
        OntoWiki_Navigation::setActive('installed', true);

        /* Ready from SystemConfig */
        $results = $this->readFromSysConfig('all');
        //print_r($results);
        $this->view->installedaction_url = $this->getFrontController()->getBaseUrl() . '/plugins/installed';
        $this->view->toinstall_url = $this->getFrontController()->getBaseUrl() . '/plugins/toinstall';
        $this->view->touninstall_url = $this->getFrontController()->getBaseUrl() . '/plugins/touninstall';
        $this->view->install_from_file_url = $this->getFrontController()->getBaseUrl() . '/plugins/install-From-File';

        if ($results === null) {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('No plugin is installed!', OntoWiki_Message::WARNING )
            );
            $this->view->results = null;
            $numPerPage = 3;
            $this->view->plugins_per_page = $numPerPage;
            if (isset($_SESSION['qiufeng']['plugins_per_page'])) {
                $numPerPage = $_SESSION['qiufeng']['plugins_per_page'];
            }
            $this->view->plugins_per_page = $numPerPage;
            $this->page(1, $numPerPage, array());
            $this->render();
        }
        else {
            $results_without_upgrade = $results;
            $results = $this->searchupgrade($results_without_upgrade);
            $count = count($results);
            $message = "$count plugin(s) installed!";
            $this->_owApp->appendMessage(
            	new OntoWiki_Message($message, OntoWiki_Message::INFO )
           	);

            //Start use a Zend_pageinator;
            $page =1;
            $numPerPage = 3;
            $this->view->plugins_per_page = $numPerPage;
            if (isset($_SESSION['qiufeng']['plugins_per_page'])) {
                $numPerPage = $_SESSION['qiufeng']['plugins_per_page'];
            }
            $this->view->plugins_per_page = $numPerPage;
            if (isset($_GET['page']) && is_numeric($_GET['page'])) {
                $page = $_GET['page'];
            }
            $offset = $numPerPage*$page;
            $this->count = $count;
            $this->view->results = $results;
            $this->view->to_translate = $this->getFrontController()->getBaseUrl() . '/plugins/installed';
            $this->page($page,$numPerPage,$results);
            $this->render();
            //End
        }
    }

    
    /**
     * installfromFileAction
     * 
     * Action for installation from a file
     */
    public function installFromFileAction()
    {
        $this->getFrontController()->setParam('noViewRenderer', true);
        if ($_FILES['install_from_file']['error'] == UPLOAD_ERR_OK) {
            // upload ok, move file
            //$fileUri  = $this->_request->getPost('file_uri');
            $fileName = $_FILES['install_from_file']['name'];
            $tmpName  = $_FILES['install_from_file']['tmp_name'];
            $mimeType = $_FILES['install_from_file']['type'];
            //echo"<br/>fileName: $fileName    tempName:$tmpName    mimeType:$mimeType<br/>";
            if (substr(trim($fileName),-4) == '.zip') {
            	$install_with_ftp = false;
                try {
                    if (!move_uploaded_file($_FILES['install_from_file']['tmp_name'],$this->_componentRoot . '/temp/installfromfile.zip')) {
                        $install_with_ftp = true;
                    	rename($_FILES['install_from_file']['tmp_name'], $_FILES['install_from_file']['tmp_name'] . '.zip');
                        $tmp_file = $_FILES['install_from_file']['tmp_name'] . '.zip';
                        $fp = fopen($tmp_file, 'r');
                        echo "<br/>tmp_file:$tmp_file<br/>";
		                if ($fp) {
		                    $data = '';
		                    while(!feof($fp)) {
		                        $data .= fread($fp, 1024);
		                    }
		                    fclose($fp);
		                    $fp = fopen($_FILES['install_from_file']['tmp_name'] . '_tmp.zip', "w+");
                    
		                    if ($fp) {
		                        fwrite($fp,$data);
		                        fclose($fp);
		                    }
		                    $sftp = null;
		                    $connection = null;
		                    $this->ftpConnect(&$sftp, &$connection);
							if (!$sftp) {
								$this->_redirect('plugins/errors/error/noftp');
							}
							else{
								$from_file = $_FILES['install_from_file']['tmp_name'] . '_tmp.zip';
								$to_file = $this->_componentRoot . '/temp/installfromfile.zip';
								$copy = ssh2_exec($connection, "cp $from_file $to_file");
								$chmod = ssh2_exec($connection, "chmod 0777 $to_file");
								unlink($from_file);
								unlink($_FILES['install_from_file']['tmp_name'] . '.zip');
								if( $copy && $chmod ){
									//do nothing				
								}
								else {
									$this->_redirect('plugins/errors/error/exec');
								}
							}
		                    
		                }                  	
                    }
                }
                catch (Exception $e) {
                }
                
                $this->_redirect('plugins/install-choice/from/installfromfile/name/' . str_replace('.zip','',$fileName));
                
            }
            else {
                $this->_redirect('plugins/errors/error/zip');
            }
        }
        else {
            $this->_redirect('plugins/errors/error/filenotfound');
        }

    }

    /**
   * Action to show the errormessage
   * if for example the rdf-file is 
   * not correct
   *
   */
    public function errorsAction()
    {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Error!'));
        $error = $this->_getParam('error');
        if ($error == 'rdf') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('Error in RDF-file, installtion canceled!', OntoWiki_Message::ERROR)
            );
        }
        elseif ($error == 'zip') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('You can only upload an ZIP file!', OntoWiki_Message::ERROR)
            );
        }
        elseif ($error == 'filenotfound') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('File not found!', OntoWiki_Message::ERROR)
            );
        }
        elseif ($error == 'moveinstallfile') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('Could not move uploaded file!', OntoWiki_Message::ERROR)
            );
        }
        elseif ($error == 'noftp') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('Apache has no write right and could not build an ftp-connection!', OntoWiki_Message::ERROR)
            );
        }
        elseif ($error == 'exec') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('SSH2_EXEC ERROR!', OntoWiki_Message::ERROR)
            );
        }
        elseif ($error == 'installnoftp') {
            $this->_owApp->appendMessage(
            	new OntoWiki_Message('Install: No FTP-connection!', OntoWiki_Message::ERROR)
            );
        }
    }

    
    /**
     * Action for Zen_paginator
     *
     * @param int $page
     * @param int $numPerPage
     * @param array $results
     */
    public function page($page, $numPerPage, $results)
    {
        $paginator = Zend_Paginator::factory($results);
        $paginator->setCurrentPageNumber($page)->setItemCountPerPage($numPerPage);
        $this->view->paginator = $paginator;
    }


    /**
     * Action for Zend_paginator
     *
     */
    public function pagelistAction(){
        Zend_Paginator::setDefaultScrollingStyle('Elastic');
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('../pagelist/categoriepagelist.phtml');
        $paginator->setView($view);

    }


    
    /**
     * Function to install a plugin
     *
     */
    public function toinstallAction() 
    {
        require_once("Erfurt/Syntax/RdfParser.php");
        /* maybe will be used */
        if ($this->_request->isPost()) {
            // do nothing
        }

        /* really installation*/
        else {
            $from = $this->_getParam('from');
            if ($from =='installfromfile' || $from == 'upgrade' || $from == 'reinstall' || $from = 'categoriepage') {
                $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Installing...'));
                $p_name = $this->_getParam('name');
                if ($from == 'categoriepage') {
                    $url = $this->decodeSparqlQuery($this->_getParam('file_url'));
                }
                elseif ($from == 'installfromfile') {
                    $url = $this->_componentRoot.'/temp/installfromfile.zip';
                }
                elseif ($from == 'upgrade') {
                    $url = $this->decodeSparqlQuery($this->_getParam('file_url'));
                }
                elseif ($from == 'reinstall') {
                    $url = $this->decodeSparqlQuery($this->_getParam('file_url'));
                }
                

                $this->view->install_png_url = $this->_componentUrlBase . 'img/download.png';
                //echo "<h3>categorie:".$p_categorie."</h3>";
                //echo '<h3 style="color:#FF0000">Plugin:'.$p_name."from: ".$url." is installed!</h3>";
                $fp = fopen($url, 'r');
                if ($fp) {
                    $data = '';
                    while(!feof($fp)) {
                        $data .= fread($fp, 1024);
                    }
                    fclose($fp);
                    $filename = 'extensions/components/plugins/' . $p_name. '.zip';
                    
                    $fp = fopen($filename, "w+");
                    
                    if ($fp && is_writable('extensions/components/plugins/')) {
                    	clearstatcache();
                        fwrite($fp,$data);
                        fclose($fp);
                        $filepath = $filename;
                        if (file_exists($filepath)) {
                            $zip = new ZipArchive();
                            $rs = $zip->open($filepath);
                            if ($rs) {
                                $fd = explode(".",basename($filepath));
                                $zip->extractTo(dirname($filepath) . '/' . $fd[0]);
                                $zip->close();
                                unlink($filepath);


                                /* check the rdf-file */
                                $rdf_file_path = 'extensions/components/plugins/' . $p_name . '/package.rdf';
                                $fpt = fopen($url, 'r');
                                if ($fpt) {
                                    /*Parse the rdf-file in an array*/
                                    $rdf_parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdf');
                                    $rdf_result = $rdf_parser->parse($rdf_file_path, 20);
                                    //print_r($rdf_result);
                                    $rdf_format =  true;
                                    $config_info = $this->checkAndRead(&$rdf_format,$rdf_result);
                                    //print_r($config_info);
                                    /* Check the directories */
                                    if ($rdf_format && $config_info != null) {
                                        $extension_dir = $config_info['extension_dir'];
                                        $extension_file = $config_info['extension_file'];
                                        $error_message = '';
                                        if ($this->checkDir($extension_dir,$extension_file,&$error_message)){
                                            /* Copy the directories */

                                            $extension_path = $this->_componentRoot;
                                            $extension_path = str_replace('\\', '/', $extension_path);
                                            $extension_path = str_replace('/components/plugins','',$extension_path);
                                            $correct_installed = true;
                                            $component_root = str_replace('\\', '/', $this->_componentRoot);
                                            for ($i=0; $i<count($extension_dir); $i++) {
                                                $a_dir = $extension_dir[$i];
                                                try {
                                                    $from_dir = $component_root . '/' . $p_name. '/' . $a_dir;
                                                    $to_dir = $extension_path . '/' . $a_dir;
                                                    //echo "from_dir: ".$from_dir."<br/>"."to_dir".$to_dir."<br/>";
                                                    rename($from_dir,$to_dir);
                                                    $correct_installed = file_exists($to_dir);
                                                }
                                                catch (Exception $e) {
                                                    $this->_owApp->appendMessage(
                                                    	new OntoWiki_Message("Can't copy directory to $to_dir", OntoWiki_Message::ERROR)
                                                    );
                                                    $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                                                }
                                            }
                                            for ($i=0; $i<count($extension_file); $i++) {
                                                $a_file = $extension_file[$i];
                                                try {
                                                    $from_dir = $component_root.'/' . $p_name. '/' . $a_file;
                                                    $to_dir = $extension_path . '/' . $a_file;
                                                    //echo "from_dir: ".$from_dir."<br/>"."to_dir".$to_dir."<br/>";
                                                    rename($from_dir,$to_dir);
                                                    $correct_installed = file_exists($to_dir);
                                                }
                                                catch (Exception $e) {
                                                    $this->_owApp->appendMessage(
                                                    	new OntoWiki_Message("Can't copy file to $to_dir", OntoWiki_Message::ERROR)
                                                    );
                                                    $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                                                }
                                            }
                                            /* delete the unzipped file in a directory!!*/

                                            $this->delDirAndFile($this->_componentRoot . '/' . $p_name);
                                            $correct_deleted = file_exists($this->_componentRoot . '/' . $p_name);
                                            if ($correct_installed && !$correct_deleted) {
                                                if ($from == 'installfromfile') {
                                                    /* delete the template sourcefile */
                                                    unlink($this->_componentRoot.'temp/installfromfile.zip');
                                                }
                                                $this->_owApp->appendMessage(
                                                	new OntoWiki_Message("New Plugin: $p_name is installed successfully!", OntoWiki_Message::SUCCESS)
                                                );
                                                $this->view->install_info_png =  $this->_componentUrlBase . 'img/true.png';
                                            }
                                            else {
                                                $this->_owApp->appendMessage(
                                                	new OntoWiki_Message('Unknow error', OntoWiki_Message::ERROR)
                                                );
                                                $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                                            }
                                            /* write into SysConfig */
                                            $this->insertIntoSysConfig($config_info);
                                            if (!$this->_privateConfig->client->debug) {
                                            	$this->_redirect('plugins/categories');
                                            }
                                        }
                                        else {
                                            /* Possible that the extension schould be upgraded, test if they have the same Base-URL */
                                            echo"<br>$error_message<br/>";
                                            if ($this->isinstalled($config_info)) {
                                                $dirs = $config_info['extension_dir'];
                                                $files = $config_info['extension_file'];
                                                $plugin_baseurl = $config_info['plugin_baseurl'];
                                                $dir_serialized = $this->seriation($dirs);
                                                $file_serialized = $this->seriation($files);

                                                /*Take care of '/' because of Zend_framwork */
                                                $dir_serialized = str_replace('/','@slash@',$dir_serialized);
                                                $file_serialized = str_replace('/','@slash@',$file_serialized);
                                                $plugin_baseurl = str_replace('/','@slash@',$plugin_baseurl);
                                                $reinstall_url = 'plugins/trytoreinstall/dirs/' . $dir_serialized . '/files/' . $file_serialized . '/p_name/' . $p_name . '/extension_url/' . $plugin_baseurl;
                                                if ($from == 'installfromfile') {
                                                    $url_zip_file = $this->_componentRoot . '/temp/installfromfile.zip';
                                                    $url_zip_file = str_replace('\\', '/', $url_zip_file);
                                                    $reinstall_url = $reinstall_url . '/reinstall_from/file/file_url/' . $this->codeSparqlQuery($url_zip_file);
                                                }
                                                elseif ($from == 'upgrade') {
                                                    $upgrade_file_url = $this->_getParam('file_url');
                                                    $reinstall_url = $reinstall_url . '/reinstall_from/upgrade/file_url/' . $upgrade_file_url;  //$upgrade_file_url is already coded
                                                }
                                                elseif ($from == 'categoriepage') {
                                                    $upgrade_file_url = $this->_getParam('file_url');
                                                    $reinstall_url = $reinstall_url . '/reinstall_from/upgrade/file_url/' . $upgrade_file_url;  //$upgrade_file_url is already coded
                                                }
                                                $this->_redirect($reinstall_url);
                                            }
                                            else {
                                                $this->_owApp->appendMessage(
                                                	new OntoWiki_Message($error_message, OntoWiki_Message::ERROR)
                                                );
                                                $this->view->install_info_png = $this->_componentUrlBase.'img/false.png';
                                            }
                                        }

                                    }
                                    else {
                                        $this->_owApp->appendMessage(
                                        	new OntoWiki_Message("Wrong RDf-format!", OntoWiki_Message::ERROR)
                                        );
                                        $this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
                                    }
                                }
                                else {
                                    $this->_owApp->appendMessage(
                                    	new OntoWiki_Message('RDF-file not found!: '.$filename, OntoWiki_Message::ERROR)
                                    );
                                    $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                                }
                            }
                        }
                        else {
                            $this->_owApp->appendMessage(
                            	new OntoWiki_Message("Copyed zip-file not found!: $filename", OntoWiki_Message::ERROR)
                            );
                            $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                        }
                    }
                    else {
                        $this->_owApp->appendMessage(
                        	new OntoWiki_Message("Can't wirite file to: $filename", OntoWiki_Message::ERROR)
                        );
                        $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                    }
                }
                else {
                    $this->_owApp->appendMessage(
                    	new OntoWiki_Message("Can't open file from: $url", OntoWiki_Message::ERROR)
                    );
                    $this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                }
            }
        }
    }

    
    /**
     * Intall the plugins with FTP
     *
     */
    public function toInstallWithFtpAction() 
    {
    	$sftp = null;
    	$connection = null;
    	$this->ftpConnect(&$sftp, &$connection);
    	if (!$sftp) {
    		$this->_redirect('plugins/errors/error/installnoftp');
    	}
    	
        require_once("Erfurt/Syntax/RdfParser.php");
        /* maybe will be used */
        if ($this->_request->isPost()) {
            // do nothing
        }

        /* really installation*/
        else {
            $from = $this->_getParam('from');
            if ($from =='installfromfile' || $from == 'upgrade' || $from == 'reinstall' || $from = 'categoriepage') {
                $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Installing...'));
                $p_name = $this->_getParam('name');
                if ($from == 'categoriepage') {
                    $url = $this->decodeSparqlQuery($this->_getParam('file_url'));
                }
                elseif ($from == 'installfromfile') {
                    $url = $this->_componentRoot.'/temp/installfromfile.zip';
                }
                elseif ($from == 'upgrade') {
                    $url = $this->decodeSparqlQuery($this->_getParam('file_url'));
                }
                elseif ($from == 'reinstall') {
                    $url = $this->decodeSparqlQuery($this->_getParam('file_url'));
                }
                

                $this->view->install_png_url = $this->_componentUrlBase . 'img/download.png';
                
                $sftp = null;
                $connection = null;
                $this->ftpConnect(&$sftp, &$connection);
                $componentRoot = $this->_componentRoot;
                if ($sftp) {
                	/* Copy zip-file to the plugins-directory */
                	$getfile = ssh2_exec($connection, "wget -O $componentRoot/$p_name.zip $url");
                	$copy = ssh2_exec($connection, "cp $url $componentRoot/$p_name.zip"); 
                	
                	/* Unzip the zip-file */
                	if (file_exists("$componentRoot/$p_name.zip")) {
                		$unzip = ssh2_exec($connection, "unzip $componentRoot/$p_name.zip -d $componentRoot/$p_name");
                		$del =  ssh2_exec($connection, "rm $componentRoot/$p_name.zip");
                		/* try to read from rdf-file */
                		if (file_exists("$componentRoot/$p_name")) {
                			ssh2_exec($connection, "chmod 777 $componentRoot/$p_name");
                			$rdf_file_path = "extensions/components/plugins/$p_name/package.rdf";
                            /* Parse the rdf-file in an array */
                            $rdf_parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdf');
                            $rdf_result = $rdf_parser->parse($rdf_file_path, 20);
                            //print_r($rdf_result);
                            $rdf_format =  true;
                            $config_info = $this->checkAndRead(&$rdf_format,$rdf_result);
                            //print_r($config_info);
                            
                            // Check the directories
                            if ($rdf_format && $config_info != null) {
                            	$extension_dir = $config_info['extension_dir'];
                            	$extension_file = $config_info['extension_file'];
                            	$error_message = '';
                            	if ($this->checkDir($extension_dir,$extension_file,&$error_message)){
                            		$extension_path = $this->_componentRoot;
                            		$extension_path = str_replace('\\', '/', $extension_path);
                            		$extension_path = str_replace('/components/plugins','',$extension_path);
                            		$correct_installed = true;
                            		$component_root = str_replace('\\', '/', $this->_componentRoot);
                            		for ($i=0; $i<count($extension_dir); $i++) {
                            			$a_dir = $extension_dir[$i];
                            			$from_dir = $component_root . '/' . $p_name. '/' . $a_dir;
                            			$to_dir = $extension_path . '/' . $a_dir;
                            			//echo "from_dir: ".$from_dir."<br/>"."to_dir".$to_dir."<br/>";
                            			ssh2_exec($connection, "mv $from_dir $to_dir");
                            			if (!file_exists($to_dir)) {
                            				$correct_installed = file_exists($to_dir);
                            				$this->_owApp->appendMessage(
                            					new OntoWiki_Message("Can't copy directory to $to_dir FTP", OntoWiki_Message::ERROR)
                            				);
                            				$this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
                            			}                   				
                            		}
                            		for ($i=0; $i<count($extension_file); $i++) {
                            			$a_file = $extension_file[$i];
                            			try {
                            				$from_dir = $component_root.'/' . $p_name. '/' . $a_file;
                            				$to_dir = $extension_path . '/' . $a_file;
                            				//echo "from_file: ".$from_dir."<br/>"."to_file: ".$to_dir."<br/>";
                            				ssh2_exec($connection, "mv $from_dir $to_dir");
                            				if (!file_exists($to_dir)) {
	                            				$correct_installed = file_exists($to_dir);
	                            				$this->_owApp->appendMessage(
	                            					new OntoWiki_Message("Can't copy file to $to_dir FTP", OntoWiki_Message::ERROR)
	                            				);
	                            				$this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
	                            			}          
                            			}
                            			catch (Exception $e) {
                            				$this->_owApp->appendMessage(
                            					new OntoWiki_Message("Can't copy file to $to_dir with FTP", OntoWiki_Message::ERROR)
                            				);
                            				$this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                            			}
                            		}
                            		/* delete the unzipped file in a directory!! */
                            		ssh2_exec($connection, "rm -rf $componentRoot/$p_name");
                            		//echo "<br/> rm:    rm -rf $componentRoot/$p_name <br/>";
                            		$correct_deleted = file_exists("$componentRoot/$p_name");
                            		if ($correct_installed && !$correct_deleted) {
                            			if ($from == 'installfromfile') {
                            				// delete the template sourcefile
                            				ssh2_exec($connection, "rm $componentRoot/temp/installfromfile.zip");
                            			}
                            			$this->_owApp->appendMessage(
                            				new OntoWiki_Message("New Plugin: $p_name is installed successfully!", OntoWiki_Message::SUCCESS)
                            			);
                            			$this->view->install_info_png =  $this->_componentUrlBase . 'img/true.png';
                            		}
                            		else {
                            			$this->_owApp->appendMessage(
                            				new OntoWiki_Message('Unknow error', OntoWiki_Message::ERROR)
                            			);
                            			$this->view->install_info_png =  $this->_componentUrlBase.'img/false.png';
                            		}
                            		/* write into SysConfig */
                            		$this->insertIntoSysConfig($config_info);
                            		if (!$this->_privateConfig->client->debug) {
                            			$this->_redirect('plugins/categories');
                            		}
                            	}
                            	else {
                            		/* Possible that the extension schould be upgraded, test if they have the same Base-URL */
                            		echo"<br>$error_message<br/>";
                            		if ($this->isinstalled($config_info)) {
                            			$dirs = $config_info['extension_dir'];
                            			$files = $config_info['extension_file'];
                            			$plugin_baseurl = $config_info['plugin_baseurl'];
                            			$dir_serialized = $this->seriation($dirs);
                            			$file_serialized = $this->seriation($files);

                            			/*Take care of '/' because of Zend_framwork */
                            			$dir_serialized = str_replace('/','@slash@',$dir_serialized);
                            			$file_serialized = str_replace('/','@slash@',$file_serialized);
                            			$plugin_baseurl = str_replace('/','@slash@',$plugin_baseurl);
                            			$reinstall_url = 'plugins/trytoreinstall/dirs/' . $dir_serialized . '/files/' . $file_serialized . '/p_name/' . $p_name . '/extension_url/' . $plugin_baseurl . '/ftp/1';
                            			if ($from == 'installfromfile') {
                            				$url_zip_file = $this->_componentRoot . '/temp/installfromfile.zip';
                            				$url_zip_file = str_replace('\\', '/', $url_zip_file);
                            				$reinstall_url = $reinstall_url . '/reinstall_from/file/file_url/' . $this->codeSparqlQuery($url_zip_file);
                            			}
                            			elseif ($from == 'upgrade') {
                            				$upgrade_file_url = $this->_getParam('file_url');
                            				$reinstall_url = $reinstall_url . '/reinstall_from/upgrade/file_url/' . $upgrade_file_url;  //$upgrade_file_url is already coded
                            			}
                            			elseif ($from == 'categoriepage') {
                            				$upgrade_file_url = $this->_getParam('file_url');
                            				$reinstall_url = $reinstall_url . '/reinstall_from/upgrade/file_url/' . $upgrade_file_url;  //$upgrade_file_url is already coded
                            			}
                            			$this->_redirect($reinstall_url);
                            		}
                            		else {
                            			$this->_owApp->appendMessage(
                            				new OntoWiki_Message($error_message, OntoWiki_Message::ERROR)
                            			);
                            			$this->view->install_info_png = $this->_componentUrlBase.'img/false.png';
                            		}
                            	}
                            }
                            else {
                            	$this->_owApp->appendMessage(
			                		new OntoWiki_Message("RDF-file not readable with FTP", OntoWiki_Message::ERROR)
			                	);
			                	$this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
                            }
                		}
                		else {
                			$this->_owApp->appendMessage(
		                		new OntoWiki_Message("Failed to unzip file", OntoWiki_Message::ERROR)
		                	);
		                	$this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
                		}
                	}
                	else {
                		$this->_owApp->appendMessage(
	                		new OntoWiki_Message("File not found with FTP", OntoWiki_Message::ERROR)
	                	);
	                	$this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
                	}
                }
                else {
                	$this->_owApp->appendMessage(
                		new OntoWiki_Message("Can't copy directory to $to_dir with FTP", OntoWiki_Message::ERROR)
                	);
                	$this->view->install_info_png =  $this->_componentUrlBase . 'img/false.png';
                }
            }
        }
    }

    
    
    
    
    /**
     * Function to uninstall a plugin
     *
     */
    public function touninstallAction()
    {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Uninstalling...'));
        $this->view->del_png_url = $this->_componentUrlBase . 'img/remove.png';
        $error = false;
        $reinstall = false;
        /* The user wants to uninstall it, not because of reinstall */
        if ($this->_request->isPost()) {
            $p_name = $this->_request->getPost('uninstall_name');
            //echo "<h1>Try to unstall extension: $p_name </h1><br/>";
            $dirs =$_POST['uninstall_dir'];
            $files = $_POST['uninstall_file'];
            $extension_url = $_POST['extension_url'];
        }
        else {
            if ($this->_getParam('from') == 'reinstall') {
                $p_name = $this->_getParam('p_name');
                $dirs = $this->_getParam('dirs');
                $files = $this->_getParam('files');
                $extension_url = $this->_getParam('plugin_baseurl');
                $reinstall_from = $this->_getParam('reinstall_from');
                $dirs = str_replace('@slash@', '/', $dirs);
                $files = str_replace('@slash@', '/', $files);
                $extension_url = str_replace('@slash@', '/', $extension_url);
                //echo "<br/> p_name: $p_name <br/> dirs: $dirs <br/> files: $files <br/> baseurl: $extension_url <br/>";
                $reinstall = true;
            }
        }

        $extension_dir = array();
        $extension_file = array();
        $extension_dir = explode("@trennung@",$dirs);
        $extension_file = explode("@trennung@",$files);

        //print_r($extension_dir);
        //print_r($extension_file);
		
        $del_with_ftp = false;
        
        if ($extension_dir[0]!='') {
            foreach ($extension_dir as $p_dir) {
                $p_dir = str_replace ('/components/plugins', '', ($this->_componentRoot)) . $p_dir;
                //echo"<br/>p_dir: $p_dir<br/>";
                if (file_exists($p_dir) && !$error) {
                    if (!$this->delDirAndFile($p_dir)) {
                    	$del_with_ftp = true;
                    	
                    	$sftp = null;
					    $connection = null;
					    $this->ftpConnect(&$sftp, &$connection);
					    
                        $this->_owApp->appendMessage(
                        	new OntoWiki_Message("Dirctory :$p_dir could not be deleted! Try to use FTP! ", OntoWiki_Message::ERROR)
                        );
                        if (!$sftp) {
                        	$error = true;
                        	$this->_owApp->appendMessage(
                        		new OntoWiki_Message("Dirctory :$p_dir could not be deleted! Either with PHP or with FTP (No Connection)! ", OntoWiki_Message::ERROR)
                        	);
                        	$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
                        	break;
                        }
				    	else {	
				    		ssh2_exec($connection, "rm -rf $p_dir");
		        			if (file_exists($p_dir)) {
		        				$error = true;
		        				$this->_owApp->appendMessage(
		        					new OntoWiki_Message("Dirctory :$p_dir could not be deleted! Either with PHP or with FTP!", OntoWiki_Message::ERROR)
		        				);
		        				$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
		        				break;
		        			}
		        			else {
		        				if ($this->_privateConfig->client->debug) {
			        				echo "<h2> Directory: $p_dir is deleted with FTP!";
		        				}
		        			}
				    	}
                        
                    }
                    else {
                    	if ($this->_privateConfig->client->debug) {
	                        echo "<h2> Directory: $p_dir is deleted!";
                    	}
                    }
                }
                else {
                    $error = true;
                    $this->_owApp->appendMessage(
                    	new OntoWiki_Message("Dirctory :$p_dir not found!", OntoWiki_Message::ERROR)
                    );
                    $this->view->del_info_png =  $this->_componentUrlBase . 'img/false.png';
                    break;
                }
            }
        }
        if ($extension_file[0]!= '' && !$error) {
            foreach ($extension_file as $p_file) {
                $p_file = str_replace ('/components/plugins', '', ($this->_componentRoot)).$p_file;
                if (file_exists($p_file)) {
                	if (unlink($p_file)) {
                		if ($this->_privateConfig->client->debug) {
                			echo "<h2>File: $p_file is deleted!</h2>";
                		}	
                	}
                	else {
                		$del_with_ftp = true;
                		
                		$sftp = null;
					    $connection = null;
					    $this->ftpConnect(&$sftp, &$connection);
					    
                		$this->_owApp->appendMessage(
                			new OntoWiki_Message("File :$p_file could not be deleted! Try to use FTP! ", OntoWiki_Message::ERROR)
                		);
                		if (!$sftp) {
                			$error = true;
                			$this->_owApp->appendMessage(
                				new OntoWiki_Message("File :$p_file could not be deleted! Either with PHP or with FTP (No Connection)! ", OntoWiki_Message::ERROR)
                			);
                			$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
                			break;
                		}
                		else {
                			ssh2_exec($connection, "rm  $p_file");
                			if (!file_exists($p_file)) {
                				if ($this->_privateConfig->client->debug) {
	                				echo "<h2>File: $p_file is deleted with FTP!</h2>";
                				}
                			}
                			else {
                				$error = true;
                				$this->_owApp->appendMessage(
                					new OntoWiki_Message("File : $p_file could not be deleted! Either with PHP or with FTP ", OntoWiki_Message::ERROR)
                				);
                				$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
                				break;
                			}
                		}
                	}
                }
                else {
                	$this->_owApp->appendMessage(
                		new OntoWiki_Message("File : $p_file is not found!", OntoWiki_Message::ERROR)
                	);
                	$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
                	break;
                }       
            }
        }
        if (!$error) {
            $this->delFromSysConfig($extension_url);
            if (!$del_with_ftp) {
            	$this->_owApp->appendMessage(
	            	new OntoWiki_Message("Extension : $p_name is uninstalled successfully! ", OntoWiki_Message::SUCCESS)
	            );
            }
            else {
            	$this->_owApp->appendMessage(
	            	new OntoWiki_Message("Extension : $p_name is uninstalled with FTP successfully! ", OntoWiki_Message::SUCCESS)
	            );
            }
            
            $this->view->del_info_png =  $this->_componentUrlBase . 'img/true.png';

            /* if the extension should be upgraded or reinstalled */
            if ($reinstall) {
                $url_toinstall = '';
                if (!$del_with_ftp) {
                	$url_toinstall = $this->getFrontController()->getBaseUrl() . '/plugins/toinstall/from/reinstall/name/' . $p_name;
                }
                else {
                	$url_toinstall = $this->getFrontController()->getBaseUrl() . '/plugins/to-install-with-ftp/from/reinstall/name/' . $p_name;
                }
                $upgrade_file_url = $this->getParam('file_url');
                $url_toinstall = $url_toinstall . '/file_url/' . $upgrade_file_url;
                //$this->_redirect('http://localhost/ontowiki/index.php');
                //echo"<br/> to_install_url: $url_toinstall <br/>";
                //echo "<h2>Now you can reinstall:</h2> <a href=\'".$url_toinstall."\'>Reinstall!</a>";
                $this->view->to_install_url = $url_toinstall;
            }
            else {
            	if (!$this->_privateConfig->client->debug) {
            		$this->_redirect("plugins/installed");
            	}
            }
        }
    }

	
    /**
	 * To uninstall a plugin with FTP
	 *
	 */
   	public function toUninstallFtpAction()
   	{
   		$sftp = null;
    	$connection = null;
    	$this->ftpConnect(&$sftp, &$connection);
    	if (!$sftp) {
    		$this->_redirect('plugins/errors/error/installnoftp');
    	}
    	
   		$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Uninstalling...'));
        $this->view->del_png_url = $this->_componentUrlBase . 'img/remove.png';
        $error = false;
        $reinstall = false;
        /* The user wants to uninstall it, not because of reinstall */
        if ($this->_request->isPost()) {
            $p_name = $this->_request->getPost('uninstall_name');
            //echo "<h1>Try to unstall extension: $p_name </h1><br/>";
            $dirs = $this->_request->getPost('uninstall_dir');
            $files = $this->_request->getPost('uninstall_file');
            $extension_url = $this->_request->getPost('extension_url');
        }
        else {
            if ($this->_getParam('from') == 'reinstall') {
                $p_name = $this->_getParam('p_name');
                $dirs = $this->_getParam('dirs');
                $files = $this->_getParam('files');
                $extension_url = $this->_getParam('plugin_baseurl');
                $reinstall_from = $this->_getParam('reinstall_from');
                $dirs = str_replace('@slash@', '/', $dirs);
                $files = str_replace('@slash@', '/', $files);
                $extension_url = str_replace('@slash@', '/', $extension_url);
                //echo "<br/> p_name: $p_name <br/> dirs: $dirs <br/> files: $files <br/> baseurl: $extension_url <br/>";
                $reinstall = true;
            }
        }

        $extension_dir = array();
        $extension_file = array();
        $extension_dir = explode("@trennung@",$dirs);
        $extension_file = explode("@trennung@",$files);

        //print_r($extension_dir);
        //print_r($extension_file);

        if ($extension_dir[0] != '') {
        	foreach ($extension_dir as $p_dir) {
        		$p_dir = str_replace ('/components/plugins', '', ($this->_componentRoot)) . $p_dir;
        		//echo"<br/>p_dir: $p_dir<br/>";
        		
        		if (file_exists($p_dir) && !$error) {
        			ssh2_exec($connection, "rm -rf $p_dir");
        			if (file_exists($p_dir)) {
        				$error = true;
        				$this->_owApp->appendMessage(
        					new OntoWiki_Message("Dirctory :$p_dir could not be deleted! ", OntoWiki_Message::ERROR)
        				);
        				$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
        				break;
        			}
        			else {
        				if ($this->_privateConfig->client->debug) {
	        				echo "<h2> Directory: $p_dir is deleted!";
        				}
        			}
        		}
        		else {
        			$error = true;
        			$this->_owApp->appendMessage(
        				new OntoWiki_Message("Dirctory :$p_dir not found!", OntoWiki_Message::ERROR)
        			);
        			$this->view->del_info_png =  $this->_componentUrlBase . 'img/false.png';
        			break;
        		}
        	}
        }
        if ($extension_file[0]!= '' && !$error) {
        	foreach ($extension_file as $p_file) {
        		$p_file = str_replace ('/components/plugins', '', ($this->_componentRoot)).$p_file;
        		//echo"<br/>p_file: $p_file<br/>";
        		ssh2_exec($connection, "rm  $p_file");
        		if (!file_exists($p_file)) {
        			if ($this->_privateConfig->client->debug) {
	        			echo "<h2>File: $p_file is deleted!</h2>";
        			}
        		}
        		else {
        			$error = true;
        			$this->_owApp->appendMessage(
        				new OntoWiki_Message("File : $p_file could not be deleted! ", OntoWiki_Message::ERROR)
        			);
        			$this->view->del_info_png =  $this->_componentUrlBase.'img/false.png';
        			break;
        		}
        	}
        }
        
        if (!$error) {
            $this->delFromSysConfig($extension_url);
            $this->_owApp->appendMessage(
            	new OntoWiki_Message("Extension : $p_name is uninstalled successfully! ", OntoWiki_Message::SUCCESS)
            );
            $this->view->del_info_png =  $this->_componentUrlBase . 'img/true.png';

            /* if the extension should be upgraded or reinstalled */
            if ($reinstall) {
                $url_toinstall = '';
                $url_toinstall = $this->getFrontController()->getBaseUrl() . '/plugins/to-install-with-ftp/from/reinstall/name/' . $p_name;
                $upgrade_file_url = $this->getParam('file_url');
                $url_toinstall = $url_toinstall . '/file_url/' . $upgrade_file_url;
                $this->view->to_install_url = $url_toinstall;
            }
        }
      
   	}
    
   
    /**
     * Choice if man wants to install the extesion again (reinstall)
     *
     */
    function trytoreinstallAction()
    {
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Do you want to reinsatll?'));
        $this->_owApp->appendMessage(
        	new OntoWiki_Message('This extension is alreadly installed, you must uninstall it, if you want to reinstall it.', OntoWiki_Message::WARNING )
        );
        $p_name = $this->_getParam('p_name');
        $dirs = $this->_getParam('dirs');
        $files = $this->_getParam('files');
        $extension_url = $this->_getParam('extension_url');
        $reinstall_from = $this->_getParam('reinstall_from');
        $file_url = $this->_getParam('file_url');
        $withFtp = $this->_getParam('ftp', 0);
        //echo "<br/> dirs: $dirs <br/> files: $files <br/>";
        //echo "<br/> reinstall_from: $reinstall_from <br/> file_url: $file_url <br/>";
        if ($withFtp > 0) {
        	$reinstall_url = $this->getFrontController()->getBaseUrl() . '/plugins/to-uninstall-ftp/from/reinstall/p_name/' . $p_name.'/dirs/' . $dirs . '/files/' . $files . '/plugin_baseurl/' . $extension_url;
        }
        else {
        	$reinstall_url = $this->getFrontController()->getBaseUrl() . '/plugins/touninstall/from/reinstall/p_name/' . $p_name.'/dirs/' . $dirs . '/files/' . $files . '/plugin_baseurl/' . $extension_url;
        }
        
        $reinstall_url = $reinstall_url . '/reinstall_from/' . $reinstall_from . '/file_url/' . $file_url;
        $this->view->reinstall = $reinstall_url;
        $this->view->notreinstall = $this->getFrontController()->getBaseUrl() . '/plugins/installed';
    }

    
    /**
     * Function to delete the directory
     *
     * @param string $dirName
     * @return bool
     */
    function delDirAndFile($dirName)
    {
        $succ = true;
        if ($handle = opendir("$dirName")) { 	
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != ".." ) {
                    if ( is_dir( "$dirName/$item" )) {
                        $this->delDirAndFile("$dirName/$item");
                    }
                    else {
                        if (!unlink("$dirName/$item")) {
                            $succ = false;
                            echo '<h3 style="color:#FF0000">Error: Can\'t delete file: '.$dirName/$item.'</h3>';
                        }
                    }
                }
            }
            closedir($handle);
            if (!rmdir($dirName)) {
                $succ = false;
                '<h3 style="color:#FF0000">Error: Can\'t delete directory: '.$dirName.'</h3>';
            }
        }
        return $succ;
    }
    
    /**
     * Get the information from a 
     * categroie.phtml
     *
     */
    function getinfoAction()
    {
    	$this->getFrontController()->setParam('noViewRenderer', true);
    	if ($this->_request->isPost()) {
    		$url = $this->_request->getPost('install_url');
            $p_name = $this->_request->getPost('t_p_name');
            $ftp_config = $this->_request->getPost('ftp_config');
            if (!$ftp_config) {
            	$change_url = "plugins/toinstall/from/categoriepage/name/$p_name";
            }
            else {
            	$change_url = "plugins/install-choice/from/categoriepage/name/$p_name";
            }
            $change_url = $change_url . '/file_url/' . $this->codeSparqlQuery($url);
            $this->_redirect($change_url);
    	}
    }

    
    /**
     * Insert the parsed information about
     * installed plugins into SystemConfigation
     *
     * @param array $config_info
     */
    function insertIntoSysConfig($config_info)
    {
        //print_r($config_info);
        $a_plugin = $config_info['plugin_baseurl'];
        $des = $config_info['description'];
        /*prepare for the query, because of the spaces, point and so on */
        $des = $this->codeSparqlQuery($des);
        $this->_owApp->configModel->addStatement($a_plugin, self::P_TYPE_BASE,$config_info['type'], array());
        $this->_owApp->configModel->addStatement($a_plugin, self::P_LABEL_BASE,$config_info['label'], array('object_type' => Erfurt_Store::TYPE_LITERAL));
        $this->_owApp->configModel->addStatement($a_plugin, self::P_DESC_BASE,$des, array('object_type' => Erfurt_Store::TYPE_LITERAL));
        $this->_owApp->configModel->addStatement($a_plugin, self::P_RELEASE_BASE,$config_info['release'], array());
        $this->_owApp->configModel->addStatement($config_info['release'], self::FILERELEASE_BASE,$config_info['file_release'], array('object_type' => Erfurt_Store::TYPE_LITERAL));
        $this->_owApp->configModel->addStatement($config_info['release'], self::REVISION_BASE,$config_info['revision'], array('object_type' => Erfurt_Store::TYPE_LITERAL));
        $this->_owApp->configModel->addStatement($config_info['release'], self::CREATED_BASE,$config_info['created'], array('object_type' => Erfurt_Store::TYPE_LITERAL));
        $extension_dirs = $config_info['extension_dir'];
        if (sizeof($extension_dirs) != 0) {
            foreach ($extension_dirs as $a_extension_dir) {
                $this->_owApp->configModel->addStatement($config_info['release'], self::EXTENSION_DIR_BASE, $a_extension_dir, array('object_type' => Erfurt_Store::TYPE_LITERAL));
            }
        }
        $extension_files = $config_info['extension_file'];
        if (sizeof($extension_files) != 0) {
            foreach ($extension_files as $a_extension_file) {
                $this->_owApp->configModel->addStatement($config_info['release'], self::EXTENSION_FILE_BASE, $a_extension_file, array('object_type' => Erfurt_Store::TYPE_LITERAL));
            }
        }
    }

    
    /**
     * Delete all the information about
     * installed plugins from SystemConfigation
     *
     * @param string $extension_url
     */
    function delFromSysConfig($extension_url)
    {
        $query = '	PREFIX release: <'.$this->_privateConfig->p_release_base.'>
					PREFIX node: <' . $extension_url . '>											
									
					SELECT ?p_release 
					WHERE { node: release: ?p_release.}';
        $release = array();
        $release = $this->_owApp->configModel->sparqlQuery($query);
        $release_url = $release[0]['p_release'];
        $this->_owApp->configModel->deleteMatchingStatements($release_url, null, null, array());
        $this->_owApp->configModel->deleteMatchingStatements($extension_url, null, null, array());
    }

    
    /**
     * Read all the information about
     * installed plugins from SystemConfigation
     *
     * @param string $categorie
     * @return array
     */
    function readFromSysConfig($categorie)
    {
        $query = '	PREFIX type: <'.$this->_privateConfig->p_type_base.'>
					PREFIX plugin: <'.$this->_privateConfig->plugin_base.'>
					PREFIX label: <'.$this->_privateConfig->p_label_base.'>
					PREFIX description: <'.$this->_privateConfig->p_desc_base.'>
					PREFIX release: <'.$this->_privateConfig->p_release_base.'>
					PREFIX revision: <'.$this->_privateConfig->p_revision_base.'>
					PREFIX file_release: <'.$this->_privateConfig->filerelease_base.'>
					PREFIX created: <'.$this->_privateConfig->p_created_base.'>
				
				
												
								
					SELECT ?node ?p_name ?p_desc ?p_release ?p_revision ?p_f_release ?p_created 
					WHERE {?node type: plugin:.
					       ?node label: ?p_name.
					       ?node description: ?p_desc.
					       ?node release: ?p_release.
					       ?p_release revision: ?p_revision.
					       ?p_release file_release: ?p_f_release.
					       ?p_release created: ?p_created.
					       }';


        $results_without_dirs = array();
        $results_without_dirs = $this->_owApp->configModel->sparqlQuery($query);

        /* Read the extensionDir and extensionFiles from SystemConfig */
        $results = array();
        if (count($results_without_dirs)!=0) {
            foreach ($results_without_dirs as $a_plugin) {
                $a_plugin['p_desc'] = $this->decodeSparqlQuery($a_plugin['p_desc']);
                $node = $a_plugin['p_release'];
                $query_dir = '	PREFIX node: <' . $node . '>
								PREFIX extension_dir: <'.$this->_privateConfig->extension_dir.'>
												
								SELECT ?extension_dir
								WHERE {node: extension_dir: ?extension_dir.}';
                $a_plugin['extension_dir'] = array();
                $extension_dirs = $this->_owApp->configModel->sparqlQuery($query_dir);
                //echo ("<br/>extension_dirs:<br/>");
                //print_r($extension_dirs);
                //echo ("<br/>END<br/>");
                if (count($extension_dirs)!=0) {
                    foreach ($extension_dirs as $a_dir) {
                        $a_plugin['extension_dir'][] = $a_dir['extension_dir'];
                    }
                }

                $query_files = 'PREFIX node: <' . $node . '>
							PREFIX extension_file: <'.$this->_privateConfig->extension_file.'>
											
							SELECT ?extension_file
							WHERE {node: extension_file: ?extension_file.}';
                $a_plugin['extension_file'] = array();
                $extension_files = $this->_owApp->configModel->sparqlQuery($query_files);
                //echo ("<br/>extension_files:<br/>");
                //print_r($extension_files);
                //echo ("<br/>END<br/>");
                if (count($extension_files)!=0) {
                    foreach ($extension_files as $a_file) {
                        $a_plugin['extension_file'][] = $a_file['extension_file'];
                    }
                }
                $results[] = $a_plugin;
            }
        }

        //print_r($results);

        if (count($results)!=0) {
            return $results;
        }
        else {
            return null;
        }
    }

    
    /**
     * Prepare for Sparql-Query because
   	 * of the spaces, points and so on
   	 * in the context
     *
     * @param string $text
     * @return string
     */
    
    function codeSparqlQuery($text)
    {
        $text = trim($text);
        $text = htmlspecialchars($text,ENT_QUOTES,"ISO-8859-15");
        $text = str_replace(" ", "&nbsp;", $text);
        $text = str_replace(".", "&pnt;", $text);
        $text = str_replace("-", "&slsh;", $text);
        $text = str_replace("/", "&slash;", $text);
        return $text;
    }

   
    /**
     * Decode the for Sparql coded String back
     *
     * @param string $text
     * @return string
     */
    function decodeSparqlQuery($text)
    {
        $text = trim($text);
        $text = htmlspecialchars_decode($text,ENT_QUOTES);
        $text = str_replace("&nbsp;", " ", $text);
        $text = str_replace("&pnt;", ".", $text);
        $text = str_replace("&slsh;", "-", $text);
        $text = str_replace("&slash;", "/", $text);
        return $text;
    }

    
    /**
     * Check and read
     * Check the format of the RDF-file, if correct,
     * read the information from the RDF-file
     *
     * @param bool $rdf_format
     * @param array $rdf_result
     * @return array
     */
    function checkAndRead(&$rdf_format, $rdf_result)
    {
        try {
            $plugin_baseurl = key($rdf_result);
            //echo "<br/>plugin_baseurl:".$plugin_baseurl."<br/>";
            $a_plugin = $rdf_result[$plugin_baseurl];
            //print_r($a_plugin);
            $type = $a_plugin[self::P_TYPE_BASE][0]['value'];
            $label = $a_plugin[self::P_LABEL_BASE][0]['value'];
            $description = $a_plugin[self::P_DESC_BASE][0]['value'];
            $release = $a_plugin[self::P_RELEASE_BASE][0]['value'];

            $a_release = $rdf_result[$release];
            $file_release = $a_release[self::FILERELEASE_BASE][0]['value'];
            $revision = $a_release[self::REVISION_BASE][0]['value'];
            $created = $a_release[self::CREATED_BASE][0]['value'];
            $extension_dir = array();
            if (array_key_exists(self::EXTENSION_DIR_BASE,$a_release)) {
                foreach ($a_release[self::EXTENSION_DIR_BASE] as $a_extension_dir) {
                    $extension_dir[] = $a_extension_dir['value'];
                }
            }

            $extension_file =  array();
            if (array_key_exists(self::EXTENSION_FILE_BASE,$a_release)) {
                foreach ($a_release[self::EXTENSION_FILE_BASE] as $a_extension_file) {
                    $extension_file[] = $a_extension_file['value'];
                }
            }

            if (isset($type) && isset($label) && isset($description) && isset($release) && isset($file_release) && isset($revision) && isset($created)){
                $config_info = array(
	                "plugin_baseurl"	=> $plugin_baseurl,
	                "type" 				=> $type,
	                "label" 			=> $label,
	                "description" 		=> $description,
	                "release" 			=> $release,
	                "file_release" 		=> $file_release,
	                "revision" 			=> $revision,
	                "created" 			=> $created,
	                "extension_dir" 	=> $extension_dir,
	                "extension_file" 	=> $extension_file
                );
                return $config_info;
            }
            else {
                $rdf_format = false;
                return null;
            }

        }
        catch (Exception  $e) {
            $rdf_format = false;
            return null;
        }
    }

    
    /**
     * checkDir
     * Check the directories, if all the needed directories 
     * are there
     *
     * @param string $extension_dir
     * @param string $extension_file
     * @param string $message
     * @return bool
     */
	public function checkDir($extension_dir, $extension_file, &$message)
    {
        if (count($extension_dir) != 0) {
            foreach ($extension_dir as $a_dir){
                $to_check = explode('/', $a_dir);
                $path = $this->_componentRoot;
                $path = str_replace('\\', '/', $path);
                $path = str_replace('/components/plugins', '', $path);
                for ($i=0; $i!=count($to_check)-1; $i++) {
                    $path = $path . '/' . $to_check[$i];
                    if (!file_exists($path)) {
                        $message = "Directory: $path is not found!";
                        return false;
                    }
                }
                $path = $path . '/' . $to_check[count($to_check)-1];
                if (file_exists($path)) {
                    $message = "Directory: $path already exists!";
                    return false;
                }
            }
        }
        if (count($extension_file) != 0) {
            foreach ($extension_file as $a_file) {
                $to_check = explode("/",$a_file);
                $path = $this->_componentRoot;
                $path = str_replace("\\","/", $path);
                $path = str_replace("/components/plugins","",$path);
                for ($i=0; $i!=count($to_check)-1; $i++) {
                    $path = $path."/".$to_check[$i];
                    if (!file_exists($path)) {
                        $message = "Directory: $path is not found!";
                        return false;
                    }
                }
                $path = $path."/".$to_check[count($to_check)-1];
                if (file_exists($path)) {
                    $message = "File: $path already exists!";
                    return false;
                }
            }
        }
        //echo "<br/>Dirs are ok! <br/>";
        return true;
    }

    
    /**
     * The function to test, if the extension is installed,
     * but with another version.
     * 
     * @param array $config_info
     * @return bool
     */
    public function isinstalled($config_info)
    {
        $plugin_baseurl = $config_info['plugin_baseurl'];
        $query = '	PREFIX node: <' . $plugin_baseurl . '>
					SELECT ?x ?y
					WHERE {node: ?x ?y.}';
        $a_extension = $this->_owApp->configModel->sparqlQuery($query);
        if (count($a_extension)<2) {
            return false;
        }
        else {
            return true;
        }
    }


    
    
    /**
     * Seriation an Array into a String
     *
     * @param array $extension_dir
     * @return string
     */
    public function seriation($extension_dir)
    {
        $extension_dir_serialized = '';
        for ($i = 0; $i<count($extension_dir); $i++){
            if ($i == count($extension_dir)-1){
                $extension_dir_serialized = $extension_dir_serialized.$extension_dir[$i];
            }
            else {
                $extension_dir_serialized = $extension_dir_serialized.$extension_dir[$i]."@trennung@";
            }
        }
        return $extension_dir_serialized;
    }
    
    
    /**
     * Search for upgrades
     *
     * @param array $results_without_upgrade
     * @return array
     */
    public function searchupgrade($results_without_upgrade)
    {
        $results = array();
        foreach ($results_without_upgrade as $a_result) {
            $plugin_url = $a_result['node'];
            $p_created = $a_result['p_created'];
            $query = '	PREFIX modified: <'.$this->_privateConfig->p_modified_base.'>
	  					PREFIX release: <'.$this->_privateConfig->p_release_base.'>
	  					PREFIX file_release: <'.$this->_privateConfig->filerelease_base.'>
						PREFIX node: <' . $plugin_url . '>
															
						SELECT ?new_created ?p_f_release
						WHERE {	node: modified: ?new_created.
								node: release: ?p_release.
								?p_release file_release: ?p_f_release.}';
            $client = new Zend_Http_Client($this->_privateConfig->repository->r_url);

            $client->setParameterPost('query', $query);
            $client->setHeaders('Accept', 'application/sparql-results+json');
            $response = $client->request('POST');

            $sparl_results = Zend_Json::decode($response->getBody());
            //echo "<br/> An_upgrade: "; print_r($sparl_results); echo "<br/>";
            if (strstr($response->getBody(),"new_created") && strstr($response->getBody(), "p_f_release")) {
                $new_created = $sparl_results['bindings'][0]['new_created']['value'];
                $file_url = $sparl_results['bindings'][0]['p_f_release']['value'];
            }
            if (!isset($new_created) || $new_created === null) {
                $a_result['upgrade'] = '';
            }
            else {
                $new_created = substr(trim($new_created),0,10);
                $new_created = strtotime($new_created);
                $p_created = strtotime($p_created);
                if ($new_created > $p_created) {
                    $a_result['upgrade'] = $file_url;
                }
                else {
                    $a_result['upgrade'] = '';
                }
            }    
            $results[] = $a_result;
        }
        return $results;
    }

    
    /**
     * Generate an array for the tags
     *
     * @param array $tags
     * @param array $categorie_url
     * @return array
     */
    public function createtags($tags,$categorie_url)
    {
        $tags_with_url = array();
        if (count($tags)>0 && $tags!=null) {
            foreach ($tags as $a_tag) {
                $tags_with_url[] = array(	
                	'tag' 		=> $a_tag['tag'],
	                'url' 		=> $categorie_url.'tag/' . $a_tag['tag'],
	                'weight' 	=> $a_tag['weight']
	           	);
            }
            return $tags_with_url;
        }
        else {
            return array();
        }
    }

    
    /**
     * Get the tags from repository
     *
     * @param array $tag
     * @return array
     */
    public function getTags($tag)
    {
        $num_tags = $this->_privateConfig->client->tags_num;
        $tags_client = new Zend_Http_Client($this->_privateConfig->repository->tags_url);
        $tags_client->setHeaders('Accept', 'application/json');
        $tags_client->setParameterPost('count', $num_tags);
        if ($tag!=null) {
            $tags_client->setParameterPost('tags', Zend_Json::encode(array($tag)));
        }
        $tags_response = $tags_client->request('POST');
        $tags_results = Zend_Json::decode($tags_response->getBody());
        //print_r($tags_response);
        //echo "<br/>TAGS_RESPONSE: ".print_r($tags_response->getBody())."<br/>";
        
        $tags_results = $this->createtags($tags_results, $this->getFrontController()->getBaseUrl() . '/plugins/categories/');
        return $tags_results;
    }
    
    
    /**
     * Get the connection to ftp-server
     *
     * @param unknown_type $sftp
     * @param unknown_type $connection
     */
    public function ftpConnect(&$sftp, &$connection){
    	$username = $this->_privateConfig->ftp->username;
    	$password = $this->_privateConfig->ftp->password;
    	$hostname = $this->_privateConfig->ftp->hostname;
    	$ssh2 = "ssh2.sftp://$username:$password@$hostname:22";
    	$connection = ssh2_connect("$hostname", 22);
    	ssh2_auth_password($connection, $username, $password);
    	$sftp = ssh2_sftp($connection);
    }
    
    
    /**
     * Choose the way of installation, weather
     * with FTP or PHP
     *
     */
    public function installChoiceAction()
    {	
    	$from = $this->getParam('from');
    	$name = $this->getParam('name');
    	if ($from == 'file_url') {
			$file_url = '';    		
    	}
    	else {
    		$file_url = $this->getParam('file_url');
    	}
    	$this->view->with_ftp = $this->getFrontController()->getBaseUrl() .  "/plugins/to-install-with-ftp/from/$from/name/$name/file_url/$file_url";
    	$this->view->no_ftp = $this->getFrontController()->getBaseUrl() . "/plugins/toinstall/from/$from/name/$name/file_url/$file_url";
    }
    
    
    public function successAction()
    {
    	$from = $this->getParam('from');
    	if ($from == 'uninstall') {
    		$p_name = $this->getParam('p_name');
			$this->_owApp->appendMessage(
            	new OntoWiki_Message("Extension : $p_name is uninstalled successfully! ", OntoWiki_Message::SUCCESS)
            );    		
    	}
    }
    
    /**
     * Search for plugins according to the information,
     * the user entered in searchbox.
     *
     * @param string_type $search_keyword
     * @param object $client
     * @param bool $wrong_response
     * @param string $sort_kind
     * @param string $sort_priority
     * @return array
     */
    public function searchForPlugins($search_keyword, $client, &$wrong_response, $sort_kind, $sort_priority)
    {
    	$query =   "PREFIX type: <".$this->_privateConfig->p_type_base.">
					PREFIX plugin: <".$this->_privateConfig->plugin_base.">
					
    				SELECT DISTINCT ?packageUri
    				WHERE {
    					?packageUri type: plugin:.
    					?packageUri ?prop ?FilteredLiteral.
    					FILTER regex(?FilteredLiteral, \"$search_keyword\", \"i\")
    				}";
    	$client->setParameterPost('query',$query);
        $client->setHeaders('Accept', 'application/sparql-results+json');
        $response = $client->request('POST');

        $sparl_results = Zend_Json::decode($response->getBody());
        //print_r($sparl_results);
        
        // if there is no error in reponse
        if (strstr($response->getBody(),'bindings')) {
        	$url_results = array();
        	$bindings = $sparl_results['bindings'];
        	foreach ($bindings as $a_binding) {
        		$url_results[] = $a_binding['packageUri']['value'];
        	}
        	$plugin_results = array();
	        if (count($url_results) != 0) {
	        	$this->searchForUrl($url_results, $client, &$plugin_results, $sort_kind, $sort_priority);
	        }
	        return $plugin_results;
        }
        else {
        	$this->_owApp->appendMessage(
            	new OntoWiki_Message('Plugin Repository not reachable', OntoWiki_Message::ERROR )
            );
            $wrong_response = true;
            return null;
        }
    }
    
    
    /**
     * Search for the plugins according to the information
     * from the function searchForPlugins
     *
     * @param array $url_results
     * @param object $client
     * @param array $results
     * @param string $sort_kind
     * @param string $sort_priority
     */
    public function searchForUrl($url_results, $client, &$results, $sort_kind, $sort_priority)
    {
    	foreach ($url_results as $a_url_result) {
    		$u_query = "PREFIX type: <".$this->_privateConfig->p_type_base.">
						PREFIX plugin: <".$this->_privateConfig->plugin_base.">
						PREFIX name: <".$this->_privateConfig->p_name_base.">
						PREFIX description: <".$this->_privateConfig->p_desc_base.">
						PREFIX developer: <".$this->_privateConfig->p_dev_base.">
						PREFIX release: <".$this->_privateConfig->p_release_base.">
						PREFIX url: <".$this->_privateConfig->filerelease_base.">
						PREFIX node: <$a_url_result>
						
						SELECT ?name ?description ?developer ?url
						WHERE {node: name: ?name.
						       node: description: ?description.
						       node: developer: ?developer.
						       node: release: ?version.
						       ?version url: ?url.
						       }";
    		$u_query = $u_query . "ORDER BY " . $sort_priority . "(?" . $sort_kind . ")";
    		$client->setParameterPost('query',$u_query);
	        $client->setHeaders('Accept', 'application/sparql-results+json');
	        $response = $client->request('POST');
	
	        $sparl_results = Zend_Json::decode($response->getBody());
	        //print_r($sparl_results);		
	       
	        foreach ($sparl_results['bindings'] as $a_sparal_result) {
                $plugin_name = $a_sparal_result['name']['value'];
                $plugin_developer = $a_sparal_result['developer']['value'];
                $plugin_developer = str_replace($this->_privateConfig->rdf_base_url, '', $plugin_developer);
                $plugin_desciption = $a_sparal_result['description']['value'];
                $plugin_install_url = $a_sparal_result['url']['value'];
                $results[] = array(
	                'name'			=> $plugin_name,
	                'developer'		=> $plugin_developer,
	                'description'	=> $plugin_desciption,
	                'install_url'	=> $plugin_install_url
                );   
            }
    	}
    }
    
}

?>