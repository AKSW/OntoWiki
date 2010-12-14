<?php
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dllearner
 */
require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dllearner
 */
class DllearnerHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
		$urlEqu = new OntoWiki_Url(array('controller' => 'dllearner', 'action' => 'learnclass'), true);
		$urlEqu->setParam('equivalence', true);
		$urlSup = new OntoWiki_Url(array('controller' => 'dllearner', 'action' => 'learnclass'), false);
		$urlSup->setParam('equivalence', false);
		$rmenu = OntoWiki_Menu_Registry::getInstance()->getMenu(EF_RDFS_RESOURCE);
		$rmenu->appendEntry(OntoWiki_Menu::SEPARATOR);
		$rmenu->appendEntry('Learn Equivalent Class Expression', (string) $urlEqu);
		$rmenu->appendEntry('Learn Super Class Expression', (string) $urlSup);
	}
}