<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Exhibit view helper
 *
 * prints exhibit header script
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_HeadScriptExhibit extends Zend_View_Helper_Abstract
{
    // current view, injected with setView from Zend
    public $view;

    public function headScriptExhibit($dataProperty = 'http://lod2.eu/schema/exhibitData')
    {
        if ($dataProperty == null) {
            return;
        } else {
            $description = $this->view->description;
            $resourceUri = $this->view->resourceUri;
            // check for exhibit data URI and integrate this as well as exhibit
            if (isset($description[$resourceUri][$dataProperty])) {
                echo '    <script src="http://static.simile.mit.edu/exhibit/api-2.0/exhibit-api.js" type="text/javascript"></script>' . PHP_EOL;
                foreach ($description[$resourceUri][$dataProperty] as $property) {
                    if (isset($property['value'])) {
                        echo '    <link href="'.$property['value'].'" type="application/jsonp" rel="exhibit/data" ex:jsonp-callback="cb" />' .PHP_EOL;
                    }
                }
            }
        }
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

}
