<?php
/**
 * DL-Learner plugin class
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_dllearner
 * @author Maria Moritz & Vu Duc Minh
 * @version $Id$
 */
 require_once 'OntoWiki/Module.php';
 
class DllearnerModule extends OntoWiki_Module {
  	
	protected static $_instance  = null;
	
    /**
     * adds Dllearner.js for javascript usage
     *
     */
	public function addIncludes(&$includes){

		$includes.='<!-- Dllearner scripts -->'. PHP_EOL;
		$includes.='<script type="text/javascript" src="'.$this->_getPluginBaseUri().'/Dllearner.js"></script>'.PHP_EOL;
		$includes.='<!-- End of Dllearner scripts -->'.PHP_EOL;
		return true;
	}
	
	public function init() {
	}
	
	public function getTitle()
	{	
		return 'DL-Learner';
		//return 
	}
	
	public function shouldShow() {
		return false;
	}
}
?>