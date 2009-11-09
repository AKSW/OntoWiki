<?php 


require_once 'OntoWiki/Module.php';
require_once 'extensions/components/easyinference/InfRuleContainer.php';


/**
 * OntoWiki module – easyinference
 *
 * shows Options to manage inferences
 *
 * @package    easyinference
 * @author     swp-09-7
 */

class EasyinferenceModule extends OntoWiki_Module
{
	private $has_ask = true;
	private $has_star_select = true;
	private $prologue;
	private $star_limit;

    public function init()
    {
	    $this->prologue = ($this->has_ask ? 'ASK' : ($this->has_star_select ? 'SELECT *' : die('db not supported')));
        $this->star_limit = (!$this->has_ask ? ' LIMIT 1' : '');
        $this->view->headScript()->captureStart(); // Start JavaScript Entry
		?>
        var eiRequestUrl = '<?php echo new OntoWiki_Url(array('controller' => 'easyinference', 'action' => '__action__')) ?>';
        <?php 
		$this->view->headScript()->captureEnd(); // End JavaScript Entry
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'easyinference.js');
        //$this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'easyinference.css', 'screen');
        
	  $this->_owApp->translate->addTranslation(_OWROOT . $this->_config->extensions->modules .
                                               $this->_name . DIRECTORY_SEPARATOR . 'languages/', null,
                                               array('scan' => Zend_Translate::LOCALE_FILENAME));
    }
	
	public function shouldShow()
	{
        $_ac =  $this->_erfurt->getAc(); 
	  // dont show for objects and users who aren't almighty at model edit
	  return !$this->_erfurt->getStore()->sparqlQuery
		(Erfurt_Sparql_SimpleQuery::initWithString
		 (' '.$this->prologue.' FROM <'.$this->_owApp->selectedModel.'> WHERE { <'.$this->_owApp->selectedResource.'>'.
		  ' a ?z . ?z a <'.EF_RDFS_CLASS.'> }'.$this->star_limit)) && $_ac->isActionAllowed ('userAnyModelEditAllowed');
                                                                    //$_ac->isModelAllowed('edit', $this->_owApp->selectedModel) ;
	}
    
    /* add a menu to the module */
    public function getMenu()
    {
		require_once 'easyInferenceMenu.php';
    	$easyInferenceMenu = new EasyInferenceMenu();
    	return $easyInferenceMenu->getMenu('easyInfernceMenu')->toArray();
    }
    
    /**
     * return the content of the modul
     */
    public function getContents()
    {        
	  $content = $this->render('easyinference');
	  return $content;
    }
    
    
}
