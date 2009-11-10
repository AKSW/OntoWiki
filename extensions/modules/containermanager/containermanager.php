<?php
/**
 * ContainerManager plugin main class.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_containermanager
 * @author Maria Moritz
 * @version $Id: ContainerManager.php 2263 2008-05-12 mariamoritz $
 */
require_once 'OntoWiki/Url.php';
require_once 'OntoWiki/Module.php';

class ContainermanagerModule extends OntoWiki_Module {

	private $_uri = null;
	private $container;
	        
    public function init()
    { 
		if  ( (!isset($_SESSION['container'])) || ($_SESSION['container'] == null) ) {
            $_SESSION['container'] = array();
        }
    }

  	//protected function _initPlugin($erfurtobject) {
  	//	
	//    parent::_initPlugin($erfurtobject);
	//    return true;	    
  	//}

	public function getTitle()
	{	
		return 'Container Manager';
		//return 
	}
	
   /**
     * grabs the container action and the service controller name
     * given by the request object and
     * forwards it to the controller directory
     */
	public function grabContainer() {
		
		$frontController = Zend_Controller_Front::getInstance();
		$request = $frontController->getRequest();
		$controllerName = strtolower($request->getControllerName());
		$actionName = strtolower($request->getActionName());

		if ($controllerName == 'service' && $actionName == 'container') {
			
			// The one and only thing needed for redirecting to your own
			// controller (no setting of action or controller or sth. needed)
			$frontController->setControllerDirectory(dirname(__FILE__));			
		}
	}

	public function getContextMenu() {
		/*$contextMenu = new Ontowiki_Menu();
		$contextMenu->setEntry('Add new Container', '#')
					->setEntry('Delete Container', '#')
					->setEntry(OntoWiki_Menu::SEPARATOR)
					->setEntry('Show Container', '#');
		
		
		return $contextMenu;*/
	}
    
    /**
     * after login a session variable is set to true
     *
     * @param string $redirectUrl
     */
	public function redirectOnLogin (&$redirectUrl) {
		
		/*$newWindow = array(
			'cssId'      => 'ContainerManager',  
			'title'      => 'Container Manager', 
			'content'    => 'hi'			
		);*/
		
		$_SESSION['bool'] = true;
    }

    /**
     * bevor the containerManager window will be displayd
     * it will be checked whether you are logged in and a model is active
     */
	/*public function addWindowToView(&$newWindow) {	
    # for event 'template_plugin_example_data', triggered in view (_includes/footer.php)
    	
    	if(!$_SESSION['bool'])return;
    	if(!Zend_Registry::get('config')->activeModel)return;
    	
    	$content = array(
			'cssId'      => 'ContainerManager',  
			'title'      => 'Container Manager', 			
		);
		$newWindow .= $this->_view->partial('micro/window.php', $content);
		return $content;	
		
	}	*/
	public function getContents() {
	$data = array(
            'actionUrl'      => $this->_config->urlBase . 'containermanager/addcontainer', 
            #'user'           => $this->_owApp->user['username'], 
            'modelSelected'  => isset($this->_owApp->selectedModel), 
            'add'              => $this->_request->getParam('addContainer'),
            'uri'             =>$this->_config->urlBase . 'containermanager/listcontainer/?name='        );
		// if  ( (!isset($_SESSION['container'])) || ($_SESSION['container'] == null) ) {
        //    echo count($_SESSION['container']);
        //}

		$content = $this->render('container', $data); 
        return $content;
	}	
    /**
     * adds ContainerManager.js for javascript usage
     *
     */
    public function addIncludes(&$includes){

		$includes.='<!-- ContainerManager scripts -->'. PHP_EOL;
		$includes.='<script type="text/javascript" src="'.$this->_getPluginBaseUri().'/ContainerManager.js"></script>'.PHP_EOL;
		$includes.='<!-- End of ContainerManager scripts -->'.PHP_EOL;
		return true;
	}	

	public function getStateId() {
	    $session = OntoWiki::getInstance()->session;
	    
        $id = $this->_owApp->selectedModel->getModelIri()
            . $this->_owApp->selectedClass
            . print_r($session->hierarchyOpen, true);
        echo $id;
        return $id;
    }
	
	/**
	*
	*
	*/
	public function shouldShow() {
		if($this->_owApp->selectedModel) {
			return true;
		}
		return false;
	}
}
?>