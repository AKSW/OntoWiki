<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
require_once 'OntoWiki/Menu.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 */
class GeocoderHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki::getInstance();
        // if a model has been selected
        if ($owApp->selectedModel != null) {
            // register with extras menu
            $this->translate  = $owApp->translate;
            $appMenu        = OntoWiki_Menu_Registry::getInstance()->getMenu('application');
            $extrasMenu     = $appMenu->getSubMenu('Extras');
            $geoCoderLabel  = $this->translate->_('Geo Coder', $owApp->config->languages->locale);
            $geoCoderUrl    = $owApp->urlBase . 'geocoder/init';
            $extrasMenu->setEntry($geoCoderLabel, $geoCoderUrl);
        }
    }
}
