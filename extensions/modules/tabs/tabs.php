<?php 

require_once 'OntoWiki/Module.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_tabs
 */
class TabsModule extends OntoWiki_Module
{
    public function getContents()
    {
        $content = array(
            'page1' => $this->render('templates/page1'), 
            'page2' => $this->render('templates/page2')
        );
        
        return $content;
    }
}

