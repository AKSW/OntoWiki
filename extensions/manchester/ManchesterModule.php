<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – Manchester Editor Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_manchester
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ManchesterModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Manchester Syntax';
    }

    public function init()
    {
        if (!isset(OntoWiki::getInstance()->selectedResource)) {
            return;
        }
    }

    /**
     * Returns the content 
     */
    function getContents()
    {
        $from = $this->_owApp->selectedModel;
        $classname = OntoWiki::getInstance()->selectedResource;

        $structured = Erfurt_Owl_Structured_Util_Owl2Structured::mapOWL2Structured(
            array( (string) $from), (string) $classname
        );

        $data = new StdClass();
        $formUrl = new OntoWiki_Url( array('controller' => 'manchester', 'action' => 'post'), array() );

        $data->formUrl = (string) $formUrl;
        $data->manchesterString = (string) $structured;

        $content = $this->render('modules/manchester', $data, 'data');
        return $content;
    }

}
