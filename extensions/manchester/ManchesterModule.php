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
     * Returns the content for the model list.
     */
    function getContents()
    {
        $from = $this->_owApp->selectedModel;
        $classname = OntoWiki::getInstance()->selectedResource;

        $structured = Erfurt_Owl_Structured_Util_Owl2Structured::mapOWL2Structured(
            array( (string) $from), (string) $classname
        );

        $manchesterString = ((string)$structured);
        return $manchesterString;

        //$structuredFromString = Erfurt_Owl_Structured_Util_ManchesterHelper::initFromString($manchesterString);
        //var_dump($structuredFromString->toTriples());


        //$newString = "Class: ns0:Allokation SubClassOf: ns0:Transportnetzbetreiber";
        //$newStructuredFromString = Erfurt_Owl_Structured_Util_ManchesterHelper::initFromString($newString);
        //var_dump($newStructuredFromString->toTriples());
        //return "ttt";
    }
}
