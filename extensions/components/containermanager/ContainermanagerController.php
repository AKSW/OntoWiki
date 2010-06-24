<?php
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_containermanager
 */
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Module/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_containermanager
 */
class ContainermanagerController extends OntoWiki_Controller_Component
{   
	private $container;

	public function init()
    {
		parent::init();
		
		if  ( (!isset($_SESSION['container'])) || ($_SESSION['container'] == null) ) {
            $_SESSION['container'] = array();
        }
		//if (!isset($_SESSION['container'])) {
		if (!isset($this->container)) {
			if($_SESSION['container']) {
				$this->container = $_SESSION['container'];
				$_SESSION['message'] = null; 
			}
			else {
				$this->container = array();
				$_SESSION['container'] = $this->container;
				$_SESSION['message'] = null;	
			}
		}
		//$this->_response->setBody($this->view->render('containermanager/box.phtml'));
		
	}
	
	public function __call($method, $args)
	{
		$this->_forward('view');
	}
	
	/**
	* Adds a new container 
	*/
	public function addcontainerAction() {
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
		$url = $_SERVER['REQUEST_URI'];
		$name = strrchr ( $url, '=' );
		$name = substr($name, 1);
		$this->container = $_SESSION['container'];
		if(array_key_exists($name, $this->container)) {
			$this->_abort('The container you entered already exists.', OntoWiki_Message::ERROR);	
			exit;
		}
		$this->container[$name] = array();
		ksort($this->container);
		$_SESSION['container'] = $this->container;
       	$this->_response->setBody($this->view->render('containermanager/box.phtml'));
	}
	
	/**
	*Deletes the selected container.
	*/
	public function deletecontainerAction() {
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
		$this->container = $_SESSION['container'];
		$url = $_SERVER['REQUEST_URI'];
		$name = strrchr ( $url, '=' );
		$name = substr($name, 1);
		if(array_key_exists($name, $this->container)) {
		unset($this->container[$name]);
		$_SESSION['container'] = $this->container;
		if ($_SESSION['list'][$name])  unset($_SESSION['list'][$name]); 
		$this->_response->setBody($this->view->render('containermanager/box.phtml'));
		} else {
			$this->_abort('The container you entered does not exists.', OntoWiki_Message::ERROR);
			exit;
		}
	
	}
	
	/**
	* Add a Ressource to the selected Container
	*/
	public function addressourceAction() {
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
		$this->container = $_SESSION['container'];
		$url = $_SERVER['REQUEST_URI'];
		$name = strstr ( $url, '=' );
		$name = substr($name, 1);
		$modeluri = $name;
		$name = substr_replace($name, '', strpos ( $name, '?' ));
		
		$modeluri = strstr($modeluri, '=');
		$modeluri = substr($modeluri, 1);
		$modeluri = substr_replace($modeluri, '', strpos ( $modeluri, '?' ));
		
		$r = strrchr($url, '=');
		$r = substr($r, 1);
		
		
		$title = 'hallo';
		$page = 2;
		if(!array_key_exists($name, $this->container)) {
			$this->_abort('The container you entered does not exists.', OntoWiki_Message::ERROR);
			exit;
		}
		if(strlen($name) == 0) {
			$this->_abort('No Container name was declared.', OntoWiki_Message::ERROR);
			exit;
		}
		
		if(strlen($modeluri) == 0) {
			$this->_abort('No Modeluri was declared.', OntoWiki_Message::ERROR);
			exit;
		}
		
		if(strlen($r) == 0) {
			$this->_abort('No Ressourceuri was declared.', OntoWiki_Message::ERROR);
			exit;
		}
		if($_SESSION['container'][$name] != null || isset($_SESSION['container'][$name])) {
			$this->add($modeluri, $r, $name, $this->container, $title);
			$this->pager($name, $page);
			//$this->view->render('list.php');
		}
		$this->_response->setBody($this->view->render('containermanager/list.phtml'));
	
	}
	
	/**
	* Removes a selected Ressource from the container
	*/
	public function deleteressourceAction() {
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
		$this->container = $_SESSION['container'];
		$url = $_SERVER['REQUEST_URI'];
		$name = strstr ( $url, '=' );
		$name = substr($name, 1);
		$modeluri = $name;
		$name = substr_replace($name, '', strpos ( $name, '?' ));
		
		$modeluri = strstr($modeluri, '=');
		$modeluri = substr($modeluri, 1);
		$modeluri = substr_replace($modeluri, '', strpos ( $modeluri, '?' ));
		
		$r = strrchr($url, '=');
		$r = substr($r, 1);
		
		$title = 'hallo';
		$page = 2;
		
		if(!array_key_exists($name, $this->container)) {
			$this->_abort('The container you entered does not exists.', OntoWiki_Message::ERROR);
			exit;
		}
		if(strlen($name) == 0) {
			$this->_abort('No Container name was declared.', OntoWiki_Message::ERROR);
			exit;
		}
		
		if(strlen($modeluri) == 0) {
			$this->_abort('No Modeluri was declared.', OntoWiki_Message::ERROR);
			exit;
		}
		
		if(strlen($r) == 0) {
			$this->_abort('No Ressourceuri was declared.', OntoWiki_Message::ERROR);
			exit;
		}
		if($modeluri == null || $r == null ){
			//$_SESSION['message'] = '<p class="message error">' . $this->_strings->cm->error->mod_res . '</p>';
			//$this->view->message = '<p class="message error">' . $this->_strings->cm->error->mod_res . '</p>';
			//header('HTTP/1.0 500 Internal Server Error');
			//echo $this -> view -> render('message.php');
			//$this->pager($name, $page);
			//$this->view->render('list.php');
			//exit();
		}
		for($i=0;$i<sizeof($this->container[$name]);$i++){
			if($this->container[$name][$i][0]==$r && $this->container[$name][$i][1]==$modeluri){
				unset($this->container[$name][$i]);
				break;
			}
		}
		$this->pager($name, $page);
		$_SESSION['container'] = $this->container;
		$this->_response->setBody($this->view->render('containermanager/list.phtml'));
	
	}
	
	
	public function listcontainerAction() {
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
		$this->container = $_SESSION['container'];
		$url = $_SERVER['REQUEST_URI'];
		$name = strrchr ( $url, '=' );
		$name = substr($name, 1);				
		// prepares the specified container in a separate array
		$_SESSION['list'] = array();
		$_SESSION['list'][$name] = $this->container[$name];
		$title = 'hallo';
		$page = 2;
		if(!array_key_exists($name, $this->container)) {
			$this->_abort('The container you entered does not exists.', OntoWiki_Message::ERROR);
			exit;
		}								
		if($_SESSION['list'][$name] == null && ! array_key_exists( $name , $this->container ) ){
					
			//$_SESSION['message'] = '<p class="messagebox">' . $this->_strings->cm->error->entries . '</p>';
			//$this->view->message = '<p class="message error">' . $this->_strings->cm->error->entries . '</p>';
			//header('HTTP/1.0 400 Bad Request');
			//echo $this -> view -> render('message.php');
			//$this->pager($name, $page);	
			//echo $this -> view -> render('list.php');
			//exit();
		}
					
		$this->pager($name, $page);		
		$this->_response->setBody($this->view->render('containermanager/list.phtml'));
	}
	
	
	/**
	* Removes all ressources from the selected container.
	*/
	public function clearcontainerAction() {
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
		$this->container = $_SESSION['container'];
		$url = $_SERVER['REQUEST_URI'];
		$name = strrchr ( $url, '=' );
		$name = substr($name, 1);
		if(!array_key_exists($name, $this->container)) {
			$this->_abort('The container you entered does not exists.', OntoWiki_Message::ERROR);
			exit;
		}
		$this->container[$name] = array();
		$_SESSION['container'] = $this->container;
		if ($_SESSION['list'][$name])  $_SESSION['list'][$name] = array();
		$this->_response->setBody($this->view->render('containermanager/box.phtml'));
	}
	
	// generalizes the add actions
	private function add($modelUri, $r, $name, $container, $title){
					
		if ($modelUri == null || $r == null || $modelUri == 'undefined'|| $r == 'undefined' || $title == 'undefined' || $title == null){
			$this->_abort('No model or resource existing.', OntoWiki_Message::ERROR);
			//$_SESSION['message'] = '<p class="massage error">' . $this->_strings->cm->error->mod_res . '</p>';
			//$this->view->message = '<p class="massage error">' . $this->_strings->cm->error->mod_res . '</p>';
			//header('HTTP/1.0 500 Internal Server Error');
			//echo $this -> view -> render('message.php');
			return;
		}
		if (!array_key_exists($name, $container)) {
			$this->_abort('The container you entered ist not existing yet.', OntoWiki_Message::ERROR);
			//$_SESSION['message'] = '<p class="message error">' . $this->_strings->cm->error->nContainer . '</p>';
			//$this->view->message = '<p class="message error">' . $this->_strings->cm->error->nContainer . '</p>';
			//header('HTTP/1.0 400 Bad Request');
			//echo $this -> view -> render('message.php');
			return;
		}
		for($i=0;$i<sizeof($container[$name]);$i++){
			if($container[$name][$i][0]==$r && $container[$name][$i][1]==$modelUri){
				$this->_abort('This entry already exists in the choosen container.', OntoWiki_Message::ERROR);
				//$_SESSION['message'] = '<p class="message error">' . $this->_strings->cm->error->entry . '</p>';
				//$this->view->message = '<p class="message error">' . $this->_strings->cm->error->entry . '</p>';
				//header('HTTP/1.0 400 Bad Request');
				//echo $this -> view -> render('message.php');
			return;
			}
		}		

		$container[$name][] = array($r, $modelUri, $title);
		asort($container[$name]);
		$_SESSION['container'] = $container;
								
		$_SESSION['list'] = array();
		$_SESSION['list'][$name] = $container[$name];
									
		if($_SESSION['list'][$name] == null && !(array_search(null, $container) == $name )){
			$this->_abort('Please check your entries.', OntoWiki_Message::ERROR);
			//$_SESSION['message'] = '<p class="messagebox">' . $this->_strings->cm->error->entries . '</p>';
			//$this->view->message = '<p class="messagebox">' . $this->_strings->cm->error->entries . '</p>';
			//header('HTTP/1.0 400 Bad Request');
			//echo $this -> view -> render('message.php');
			return;
		}		
	}
	
	// pager
	function pager($name, $page){
		// page navigation
					
		// number of entries per page
		$max = 10; 
		// number of pages
		$count_pages = ((count($_SESSION['container'][$name]))/$max);
				
		if ( isset($this->view->pagerLinks ) ) unset( $this->view->pagerLinks);
		// prepare an array for pager variables
		$this->view->pagerLinks = array();
		$this->view->pagerLinks['max'] = $max;
		$maxButtons = 10;
		$counter = 0;
					
		if (!isset($page) OR $page == 'undefined') { 
			$page = 1;
		}
		// save the page number from the request objekt to pagerLinks array
		$this->view->pagerLinks['page'] = $page;
		
		// back links for side navi
		if ($page != 1) { 
			$back = $page-1; 
			//$this->view->pagerLinks['first'] = "<a class='pager' name='".$name."' id='1'>&lsaquo;&nbsp;" . $this->_strings->cm->page->first . "</a>";
			//$this->view->pagerLinks['back'] = "<a class='pager' name='".$name."' id='". $back ."'>&laquo;&nbsp;" . $this->_strings->cm->page->prev . "</a>";
			$counter += 2;
		}
					
		// forward links for side navi
		if ($page < $count_pages) { 
			$forward = $page+1; 
			//$this->view->pagerLinks['forward'] = "<a class='pager' name='".$name."' id='". $forward . "'>". $this->_strings->cm->page->next . "&nbsp;&raquo;</a>";
			//$this->view->pagerLinks['last'] = "<a class='pager' name='".$name."' id='". $count_pages ."'>". $this->_strings->cm->page->last . "&nbsp;&rsaquo;</a>";
			$counter += 2;
		}
				
		// all other displayed pages
		if($count_pages > 1) {
			$buffer = $maxButtons - $counter;
			for ($i = 1; $i < $count_pages+1; $i++) { 
				if($i > $page-($buffer/2) && $i <= $page+($buffer/2)){
					//if($i == $page) $this->view->pagerLinks['pages'][$i] = "<a class='pager selected' name='".$name."' id='". $i ."'>" . $i . "</a>";
					//else {
						//$this->view->pagerLinks['pages'][$i] = "<a class='pager' name='".$name."' id='". $i ."'>" . $i . "</a>";	
					//}
				} 
			}
		}
	}
	
	function modelSort( $array ){
		
		$modelList = array();
		foreach($array as $key => $value){
			$modelURI = $value[1];
			$resourceURI = $value[0];
			if( array_key_exists($modelURI, $modelList)){
				array_push($modelList[$modelURI], $resourceURI);
			}
			else {
				$modelList[$modelURI] = array($resourceURI);	
			}
		}
		return $modelList;
	}
	
	    /**
     * Shortcut for adding messages
     */
    private function _abort($msg, $type = null, $redirect = null)
    {
        if (empty($type)) {
            $type = OntoWiki_Message::INFO;
        }

        $this->_owApp->appendMessage(
            new OntoWiki_Message(
                $msg ,
                $type
            )
        );

        if (empty($redirect)) {
            if ($redirect !== false) {
                $this->_redirect($this->_config->urlBase);
            }
        } else {
            $this->_redirect((string)$redirect);
        }

        return true;

    }

}